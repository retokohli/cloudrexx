<?php

/**
 * Language Module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_development
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Language Manager
 *
 * This class provides all the language functions and options for the core CMS system
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_development
 */
class LanguageManager
{
    var $_objTpl;
	var $pageTitle='';
	var $statusMessage='';
	var $arrLang = array();
	var $filePath='';
	var $hideVariables = true;

	/**
	* constructor
	*/
	function __construct()
	{
    	global  $objDatabase, $_CORELANG, $objTemplate;

    	$arrTables = array();

		$this->filePath = ASCMS_LANGUAGE_PATH. '/';

		// get tables in database
		$objResult = $objDatabase->MetaTables('TABLES');
		if ($objResult !== false && !$objResult->EOF) {
			$arrTables = $objResult;
		}
		if (in_array(DBPREFIX."language_variable_names",$arrTables) && in_array(DBPREFIX."language_variable_content",$arrTables)){
			$this->hideVariables = false;
		}

		$objTemplate->setVariable("CONTENT_NAVIGATION","<a href='index.php?cmd=language'>".$_CORELANG['TXT_LANGUAGE_LIST']."</a>"
		                                         .($this->hideVariables == false ? "<a href='index.php?cmd=language&amp;act=vars'>".$_CORELANG['TXT_VARIABLE_LIST']."</a>
		                                         <a href='index.php?cmd=language&amp;act=mod'>".$_CORELANG['TXT_ADD_LANGUAGE_VARIABLES']."</a>
												 <a href='index.php?cmd=language&amp;act=writefiles' title='".$_CORELANG['TXT_WRITE_VARIABLES_TO_FILES']."'>".$_CORELANG['TXT_WRITE_VARIABLES_TO_FILES']."</a>"
		                                         : ""));



	    $objResult = $objDatabase->Execute("SELECT id,name FROM ".DBPREFIX."languages");
	    if ($objResult !== false) {
			while (!$objResult->EOF) {
				$this->arrLang[$objResult->fields['id']]=$objResult->fields['name'];
				$objResult->MoveNext();
			}
	    }

	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."languages");
	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."language_variable_content");
	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."language_variable_names");
	}



    /**
    * Gets the requested methods
    *
    * @global   array     $_CORELANG
    * @global   object    $objTemplate
    * @return   string    parsed content
    */
	function getLanguagePage()
    {
    	global $_CORELANG, $objTemplate;

    	if(!isset($_GET['act'])){
    	    $_GET['act']="";
    	}

        switch($_GET['act']){
			case "dellang":
			    Permission::checkAccess(49, 'static');
			    $this->deleteLanguage();
			    $this->languageOverview();
			break;
			case "vars":
			    $this->listVariables();
			break;
			case "mod":
			    Permission::checkAccess(48, 'static');
			    $this->addUpdateVariable();
			    $this->modifyVariables();
			break;
			case "add":
			    Permission::checkAccess(50, 'static');
			    $this->addLanguage();
			    $this->languageOverview();
			break;
			case "del":
			    Permission::checkAccess(48, 'static');
			    $this->deleteVariable();
			    $this->listVariables();
			break;
			case "writefiles":
				Permission::checkAccess(48, 'static');
				$this->createFiles();
				$this->listVariables();
				break;
			default:
			    Permission::checkAccess(50, 'static');
			    $this->modifyLanguage();
			    $this->languageOverview();
		}
		$objTemplate->setVariable(array(
			'CONTENT_TITLE'			=> $this->pageTitle,
			'CONTENT_STATUS_MESSAGE'	=> $this->statusMessage
		));
    }



	/**
	* deletes the selected language
	*
	* @global    array      $_CORELANG
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    string     error string or empty string
	*/
	function deleteLanguage()
	{
		global $_CORELANG, $objDatabase;
		if (!empty($_REQUEST['id'])){
	        $objResult = $objDatabase->Execute("SELECT lang FROM ".DBPREFIX."content_navigation WHERE lang=".intval($_REQUEST['id']));

	        if ($objResult !== false && $objResult->RecordCount() == 0) {
				if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."languages WHERE id=".intval($_REQUEST['id'])) !== false) {
					$objDatabase->Execute("DELETE FROM ".DBPREFIX."language_variable_content WHERE lang_id=".intval($_REQUEST['id']));
					$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_gallery_language WHERE lang_id=".intval($_REQUEST['id']));
					$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_gallery_language_pics WHERE lang_id=".intval($_REQUEST['id']));
					$this->statusMessage = $_CORELANG['TXT_STATUS_SUCCESSFULLY_DELETE']; //not available
					return true;
				}
			} else {
			    $this->statusMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			    return false;
			}
		}
	}



	/**
	* deletes the selected language variables
	*
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    boolean
	*/
	function deleteVariable()
	{
		global $_CORELANG, $objDatabase;
		if (!empty($_REQUEST['id'])){
	        $objDatabase->Execute("DELETE FROM ".DBPREFIX."language_variable_names WHERE id=".intval($_REQUEST['id']));
			$objDatabase->Execute("DELETE FROM ".DBPREFIX."language_variable_content WHERE varid=".intval($_REQUEST['id']));
			$this->statusMessage = $_CORELANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
			return true;
		}
		return false;
	}



	/**
	* deletes the selected language variables
	*
	* @global    object     $objDatabase
	* @return    boolean
	*/
	function addLanguage()
	{
		global $_CORELANG, $objDatabase;
		if (!empty($_POST['name']) && !empty($_POST['shortName']) && !empty($_POST['charset']))
		{
            $shortName = mysql_escape_string($_POST['shortName']);
            $name = mysql_escape_string($_POST['name']);
            $charset = mysql_escape_string($_POST['charset']);

			$objResult = $objDatabase->Execute("SELECT lang FROM ".DBPREFIX."languages WHERE lang='".$shortName."'");
			if ($objResult !== false) {
				if ($objResult->RecordCount()>=1) {
					$this->statusMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
					return false;
				} else {
					$objDatabase->Execute("INSERT INTO ".DBPREFIX."languages
					                               SET lang='".$shortName."',
										               name='".$name."',
										               charset='".$charset."',
										               is_default='false'");

					$newLanguageId = $objDatabase->Insert_ID();
					$objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."languages WHERE is_default='true'", 1);
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							$defaultLanguageId=$objResult->fields['id'];
							$objResult->MoveNext();
						}
					}
					$objResult = $objDatabase->Execute("SELECT varid,content
					              FROM ".DBPREFIX."language_variable_content
					             WHERE lang_id=".$defaultLanguageId);
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							$arrContent[$objResult->fields['varid']]=stripslashes($objResult->fields['content']);
							$objResult->MoveNext();
						}
					}
					foreach ($arrContent as $key => $content) {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."language_variable_content
						                    	SET varid=".$key.",
						                        content='".addslashes($content)."',
						                        lang_id=".$newLanguageId.",
						                        status=0");
					}

					$objResult = $objDatabase->Execute('	SELECT	gallery_id,
																	name,
																	value
															FROM	'.DBPREFIX.'module_gallery_language
															WHERE	lang_id='.$defaultLanguageId.'
														');
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							$objDatabase->Execute('	INSERT
													INTO	'.DBPREFIX.'module_gallery_language
													SET		gallery_id='.$objResult->fields['gallery_id'].',
															lang_id='.$newLanguageId.',
															name="'.$objResult->fields['name'].'",
															value="'.$objResult->fields['value'].'"
												');
							$objResult->MoveNext();
						}
					}

					$objResult = $objDatabase->Execute('	SELECT	picture_id,
																	name,
																	`desc`
															FROM	'.DBPREFIX.'module_gallery_language_pics
															WHERE	lang_id='.$defaultLanguageId.'
														');
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							$objDatabase->Execute('	INSERT
													INTO	'.DBPREFIX.'module_gallery_language_pics
													SET		picture_id='.$objResult->fields['picture_id'].',
															lang_id='.$newLanguageId.',
															name="'.$objResult->fields['name'].'",
															`desc`="'.$objResult->fields['desc'].'"
												');
							$objResult->MoveNext();
						}
					}

					$this->statusMessage = $_CORELANG['TXT_NEW_LANGUAGE_ADDED_SUCCESSFUL'];
					return true;
				}
			}
        }
	}




	/**
	* Gets the language add variable page
	*
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    string     parsed content
	*/
	function addUpdateVariable()
	{
		global $_CORELANG, $objDatabase;
		if (!empty($_POST['submit']) AND !empty($_POST['name'])){
			$name = mysql_escape_string($_POST['name']);
			$adminzone = intval($_POST['backend']);
			$website = intval($_POST['frontend']);
			$moduleId = intval($_POST['moduleId']);

			// Add new variable
			if (empty($_POST['id'])){
				$objResult = $objDatabase->Execute("SELECT name
				              FROM ".DBPREFIX."language_variable_names
				             WHERE name = '".$name."'
				               AND module_id =".$moduleId);
				if ($objResult !== false) {
					if ($objResult->RecordCount()>=1) {
						$this->statusMessage= $_CORELANG['TXT_LANGUAGE_VARIABLE_ALREADY_EXIST'];
						return false;
					} else {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."language_variable_names
						                   SET name='".$name."',
						                       module_id='".$moduleId."',
						                       backend='".$adminzone."',
						                       frontend='".$website."'");

		                $varId = $objDatabase->Insert_ID();

						foreach ($_POST['content'] as $langId => $content) {
							$status = intval($_POST['status'][$langId]);
							$objDatabase->Execute("INSERT INTO ".DBPREFIX."language_variable_content
							                    SET varid=".$varId.",
							                        content='".addslashes($content)."',
							                        status=".$status.",
							                        lang_id=".$langId);
						}
						$this->statusMessage= $_CORELANG['TXT_LANGUAGE_VARIABLE_ADDED_SUCCESSFUL'];
						if(isset($_POST['writeFiles']) && !empty($_POST['writeFiles'])){
							$this->createFiles();
						}
						return true;
					}
				}
	        }
			// Update variable
			else
			{
				// Edit not add
				$id = intval($_POST['id']);

				$objDatabase->Execute("UPDATE ".DBPREFIX."language_variable_names
				               SET name='".$name."',
				                   module_id='".$moduleId."',
				                   backend='".$adminzone."',
				                   frontend='".$website."'
				             WHERE id=".$id);

				foreach ($_POST['content'] as $langId => $content) {
					$content=addslashes($content);
					$status = intval($_POST['status'][$langId]);

				    $objDatabase->Execute("UPDATE ".DBPREFIX."language_variable_content
				                   SET content='".$content."',
				                       status='".$status."'
				                 WHERE varid=".$id."
				                   AND lang_id=".$langId);
				}
				$this->statusMessage = $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
				if(isset($_POST['writeFiles']) && !empty($_POST['writeFiles'])){
					$this->createFiles();
				}
				return true;
			}
		}
		return false;
	}






	/**
	* Gets the language add/mod variable page
	*
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    string     parsed content
	*/
	function modifyVariables()
	{
		global $objDatabase, $_CORELANG, $objTemplate;

		$variableName = "";
		$variableId = "";
		$variableModule = "";

	    $objTemplate->addBlockfile('ADMIN_CONTENT', 'langauge_mod', 'language_mod.html');
	    $this->pageTitle = $_CORELANG['TXT_ADD_LANGUAGE_VARIABLES'];

	    $objTemplate->setVariable(array(
	        'TXT_NAME'                   => $_CORELANG['TXT_NAME'],
	        'TXT_VALUE_CONTROL_LANGUAGE' => $_CORELANG['TXT_VALUE_CONTROL_LANGUAGE'],
	        'TXT_MODULE'                 => $_CORELANG['TXT_MODULE'],
	        'TXT_SELECT_MODULE'          => $_CORELANG['TXT_SELECT_MODULE'],
	        'TXT_STORE'                  => $_CORELANG['TXT_SAVE'],
	        'TXT_WEB_PAGES'              => $_CORELANG['TXT_WEB_PAGES'],
	        'TXT_ADMINISTRATION_PAGES'   => $_CORELANG['TXT_ADMINISTRATION_PAGES'],
	        'TXT_APPLICATION_RANGE'      => $_CORELANG['TXT_APPLICATION_RANGE'],
	        'TXT_LANGUAGE_NAME_REQUIRED' => $_CORELANG['TXT_LANGUAGE_NAME_REQUIRED'],
	        'TXT_APPLICATION_RANGE_REQUIRED'  => $_CORELANG['TXT_APPLICATION_RANGE_REQUIRED'],
	        'TXT_WRITE_VARIABLES_TO_FILES' => $_CORELANG['TXT_WRITE_VARIABLES_TO_FILES']
	    ));

		$objResult = $objDatabase->Execute("SELECT id,name,lang FROM ".DBPREFIX."languages ORDER BY id");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
		        $arrayLang[$objResult->fields['id']]=$objResult->fields['name']." (".$objResult->fields['lang'].")";
		        $objResult->MoveNext();
			}
		}
		if(isset($_GET['id']))
		//---------------------------
		// mod status
		//---------------------------
		{
		    $objTemplate->setVariable("TXT_LANGUAGE_SETTING", $_CORELANG['TXT_MOD_LANGUAGE_VARIABLES']);
		    $variableId = intval($_GET['id']);
			$objResult = $objDatabase->SelectLimit("SELECT id,
			                   name,
			                   module_id,
			                   backend,
			                   frontend
			              FROM ".DBPREFIX."language_variable_names
			              WHERE id = ".$variableId, 1);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$variableName=$objResult->fields['name'];
					$variableAdminzone=$objResult->fields['backend'];
					$variableWebsite=$objResult->fields['frontend'];
					$variableModule=$objResult->fields['module_id'];
					$objResult->MoveNext();
				}
			}
			$objResult = $objDatabase->Execute("SELECT content,
			                   lang_id,
			                   status
			              FROM ".DBPREFIX."language_variable_content
			             WHERE varid = ".$variableId."
			          ORDER BY varid");
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$variableContent[$objResult->fields['lang_id']]=$objResult->fields['content'];
					$variableStatus[$objResult->fields['lang_id']]=$objResult->fields['status'];
					$objResult->MoveNext();
				}
			}
			foreach ($arrayLang as $k => $v) {
				$checked="";
				if($variableStatus[$k]==1)
				{
				    $checked="checked";
				}
				//echo htmlspecialchars($variableContent[$k]);
				$content=htmlspecialchars(stripslashes($variableContent[$k]));
				$strLangInputFields .="<input type='text' name='content[$k]' size=80 value=\"".$content."\" />&nbsp;\n
									   <input type='checkbox' name='status[$k]' value='1' ".$checked." />&nbsp;$v<br />\n";
			}
		}
		else
		//---------------------------
		// Add status
		//---------------------------
		{
			$objTemplate->setVariable("TXT_LANGUAGE_SETTING", $_CORELANG['TXT_ADD_LANGUAGE_VARIABLES']);
			foreach ($arrayLang as $k => $v)
			{
				$strLangInputFields .="<input type='text' name='content[$k]' size=80 value='' />&nbsp;\n
									   <input type='checkbox' name='status[$k]' value='1' checked />&nbsp;$v<br />\n";
			}
		}

		if($variableAdminzone==1){
			$variableAdminzone="checked";
		}else {
			$variableAdminzone="";
		}
		if($variableWebsite==1){
			$variableWebsite="checked";
		}else {
			$variableWebsite="";
		}

		$objTemplate->setVariable(array(
			'LANGUAGE_VARIABLE_NAME'	=> $variableName,
			'LANGUAGE_INPUT_FIELDS'		=> $strLangInputFields,
			'LANGUAGE_ADMINZONE'		=> $variableAdminzone,
			'LANGUAGE_WEBSITE'			=> $variableWebsite,
			'LANGUAGE_MODULES_MENU'		=> $this->getSearchOptionMenu("modules",$variableModule),
			'LANGUAGE_VARIABLE_ID'		=> $variableId
		));
	}



	/**
	* Get the language variable default page
	*
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    string     parsed content
	*/
	function listVariables()
	{
		global $_CORELANG, $objDatabase, $objTemplate;

		//init variables
		$q_term = "";
		$q_lang = "";
		$q_module = "";
		$q_status = "";
		$q_zone = "";
		$i=0;
		$zoneMenu ="";
	    $selected1="";
	    $selected2="";
	    $selected3="";


	    $objTemplate->addBlockfile('ADMIN_CONTENT', 'language_list', 'language_list.html');
	    $this->pageTitle = $_CORELANG['TXT_VARIABLE_LIST'];

		if(!isset($_SESSION['lang']['term'])) $_SESSION['lang']['term']="";
		if(!isset($_SESSION['lang']['langId'])) $_SESSION['lang']['langId']="";
		if(!isset($_SESSION['lang']['status'])) $_SESSION['lang']['status']="";
		if(!isset($_SESSION['lang']['zone'])) $_SESSION['lang']['zone']="both";
		if(!isset($_SESSION['lang']['moduleId'])) $_SESSION['lang']['moduleId'] = "";

		if(isset($_POST['term'])){
			$_SESSION['lang']['term']= mysql_escape_string($_POST['term']);
		}
		if(isset($_POST['lang'])){
			$_SESSION['lang']['langId']=intval($_POST['lang']);
		}
		if(isset($_POST['status'])){
			$_SESSION['lang']['status']=intval($_POST['status']);
		}
		if(isset($_POST['zone'])){
			$_SESSION['lang']['zone']=intval($_POST['zone']);
		}
		if (isset($_POST['module'])) {
			$_SESSION['lang']['moduleId'] = intval($_POST['module']);
		}

		$term = $_SESSION['lang']['term'];
		$lang = $_SESSION['lang']['langId'];
		$status = $_SESSION['lang']['status'];
		$zone = $_SESSION['lang']['zone'];
		$module = $_SESSION['lang']['moduleId'];

	    if($zone=="frontend"){
			$selected1="selected";
		} elseif($zone == "backend") {
			$selected2="selected";
		}elseif($zone == "both" OR $zone == "") {
			$zone = "both";
			$selected3="selected";
		}
		$zoneMenu .="<option value='both' ".$selected3.">".$_CORELANG['TXT_SECTION']."</option>\n";
		$zoneMenu .="<option value='frontend' ".$selected1.">".$_CORELANG['TXT_WEB_PAGES']."</option>\n";
		$zoneMenu .="<option value='backend' ".$selected2.">".$_CORELANG['TXT_ADMINISTRATION_PAGES']."</option>\n";
		$objTemplate->setVariable("LANGUAGE_ZONE_MENU", $zoneMenu);

	    //Begin language varibales
	    $objTemplate->setVariable(array(
	        'TXT_CONFIRM_DELETE_DATA'                    => $_CORELANG['TXT_CONFIRM_DELETE_DATA'],
	        'TXT_ACTION_IS_IRREVERSIBLE'                 => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
	        'TXT_ATTENTION_SYSTEM_FUNCTIONALITY_AT_RISK' => $_CORELANG['TXT_ATTENTION_SYSTEM_FUNCTIONALITY_AT_RISK'],
	        'TXT_MODULE'                                 => $_CORELANG['TXT_MODULE'],
	        'TXT_LANGUAGE'                               => $_CORELANG['TXT_LANGUAGE'],
	        'TXT_STATUS'                                 => $_CORELANG['TXT_STATUS'],
	        'TXT_CONTROLLED'                             => $_CORELANG['TXT_CONTROLLED'],
	        'TXT_OPEN_ISSUE'                             => $_CORELANG['TXT_OPEN_ISSUE'],
	        'TXT_LANGUAGE_DEPENDANT_SYSTEM_VARIABLES'    => $_CORELANG['TXT_LANGUAGE_DEPENDANT_SYSTEM_VARIABLES'],
	        'TXT_FOUND'                                  => $_CORELANG['TXT_FOUND'],
	        'TXT_NAME'                                   => $_CORELANG['TXT_NAME'],
	        'TXT_VALUE'                                  => $_CORELANG['TXT_VALUE'],
	        'TXT_DISPLAY'                                => $_CORELANG['TXT_DISPLAY'],
	        'TXT_ADMIN'                                  => $_CORELANG['TXT_ADMINISTRATION_PAGES'],
	        'TXT_PUBLIC'                                 => $_CORELANG['TXT_WEB_PAGES'],
	        'TXT_EXPORT_VARIABLES_TO_FILES'				 => $_CORELANG['TXT_EXPORT_VARIABLES_TO_FILES'] //not available
	    ));
	    //End language variables
		if(!empty($term)) {
			if (empty($lang)) {
		        $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."languages WHERE is_default='true'");
		        if ($objResult !== false) {
					while (!$objResult->EOF) {
				        $q_lang = "AND con.lang_id=".intval($objResult->fields['id'])." ";
				        $objResult->MoveNext();
					}
		        }
			} else {
		        $q_lang = "AND con.lang_id=".intval($lang)." ";
			}

			if ($zone <> "both") {
			    $q_zone = "AND nam.$zone=1 ";
			}

			if ($module != 0) {
				$q_module = "AND nam.module_id = ".$module." ";
			}

			if($status=="0" || $status=="1") {
			    $q_status = "AND con.status=".intval($status)." ";
			}

			$q = "SELECT con.content AS content,
			             con.status AS status,
			             con.lang_id AS lang,
			             nam.name AS name,
			             nam.id AS varid,
			             modu.name AS module,
			             nam.backend AS backend,
			             nam.frontend AS frontend
			      FROM ".DBPREFIX."language_variable_content AS con,
					   ".DBPREFIX."language_variable_names AS nam,
					   ".DBPREFIX."modules AS modu
				  WHERE modu.id=nam.module_id
			        AND con.varid=nam.id
			        AND (nam.name LIKE '%".$term."%' OR con.content LIKE '%".$term."%') ".$q_zone.$q_lang.$q_module.$q_status."
				  ORDER BY nam.id";

			$objResult = $objDatabase->Execute($q);
			if ($objResult !== false) {
				$numRows = $objResult->RecordCount();
				while (!$objResult->EOF) {
					if (($i % 2) == 0) {$class="row1";} else {$class="row2";}

					if (intval($objResult->fields['backend'])==1) {
						$objTemplate->setVariable("LANGUAGE_ADMIN","<img alt='' src='images/icons/check.gif' />");
					}
					if (intval($objResult->fields['frontend'])==1) {
						$objTemplate->setVariable("LANGUAGE_WEBSITE","<img alt='' src='images/icons/check.gif' />");
					}
					$objTemplate->setVariable(array(
						'LANGUAGE_ROWCLASS'		=> $class,
						'LANGUAGE_ID'			=> $objResult->fields['varid'],
						'LANGUAGE_VARIABLENAME'	=> $objResult->fields['name'],
						'LANGUAGE_CONTENT'		=> htmlspecialchars(stripslashes($objResult->fields['content'])),
						'LANGUAGE_MODULE'		=> $objResult->fields['module'],
						'LANGUAGE_LANG'			=> $this->arrLang[$objResult->fields['lang']]
					));
					// not carefully checked variable
					if(intval($objResult->fields['status']==1)) {
					    $langStatus ="<img alt='' src=\"images/icons/led_green.gif\" />";
					} else {
					    $langStatus ="<img alt='' src=\"images/icons/led_red.gif\" />";
					}
					$objTemplate->setVariable("LANGUAGE_STATUS",$langStatus);
					$objTemplate->parse('languageRow');
					$i++;
					$objResult->MoveNext();
				}
			}
		} else {
		    $objTemplate->hideBlock('languageSearchTable');
		}
		$objTemplate->setVariable(array(
			'LANGUAGE_STATS'		=> $numRows,
			'LANGUAGE_MODULES_MENU'	=> $this->getSearchOptionMenu("modules",$module),
			'LANGUAGE_LANG_MENU'	=> $this->getSearchOptionMenu("languages",$lang),
			'LANGUAGE_SEARCHTERM'	=> $term
		));
	}







	/**
	* get the language list page
	*
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    string     parsed content
	*/
	function languageOverview()
	{
		global $_CORELANG, $objDatabase, $objTemplate;
	    // init vars
	    $i=0;

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'langauge_langlist', 'language_langlist.html');
		$this->pageTitle = $_CORELANG['TXT_LANGUAGE_LIST'];

	    //begin language variables
	    $objTemplate->setVariable(array(
		    'TXT_ADD_NEW_LANGUAGE'	         => $_CORELANG['TXT_ADD_NEW_LANGUAGE'],
		    'TXT_NAME'	                     => $_CORELANG['TXT_NAME'],
		    'TXT_SHORT_NAME'                 => $_CORELANG['TXT_SHORT_NAME'],
		    'TXT_CHARSET'                    => $_CORELANG['TXT_CHARSET'],
		    'TXT_ADD'  	                     => $_CORELANG['TXT_ADD'],
		    'TXT_LANGUAGE_LIST'              => $_CORELANG['TXT_LANGUAGE_LIST'],
		    'TXT_ID'                         => $_CORELANG['TXT_ID'],
		    'TXT_SHORT_FORM'                 => $_CORELANG['TXT_SHORT_FORM'],
		    'TXT_STANDARD_LANGUAGE'          => $_CORELANG['TXT_STANDARD_LANGUAGE'],
		    'TXT_ACTION'                     => $_CORELANG['TXT_ACTION'],
		    'TXT_ACCEPT_CHANGES'             => $_CORELANG['TXT_ACCEPT_CHANGES'],
		    'TXT_REMARK'                     => $_CORELANG['TXT_REMARK'],
		    'TXT_ADD_DELETE_LANGUAGE_REMARK' => $_CORELANG['TXT_ADD_DELETE_LANGUAGE_REMARK'],
		    'TXT_CONFIRM_DELETE_DATA'        => $_CORELANG['TXT_CONFIRM_DELETE_DATA'],
		    'TXT_ACTION_IS_IRREVERSIBLE'     => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
		    'TXT_VALUE'                      => $_CORELANG['TXT_VALUE'],
		    'TXT_MODULE'                     => $_CORELANG['TXT_MODULE'],
		    'TXT_LANGUAGE'                   => $_CORELANG['TXT_LANGUAGE'],
		    'TXT_STATUS'                     => $_CORELANG['TXT_STATUS'],
		    'TXT_VIEW'                       => $_CORELANG['TXT_VIEW'],
		    'TXT_CONTROLLED'                 => $_CORELANG['TXT_CONTROLLED'],
		    'TXT_OPEN_ISSUE'                 => $_CORELANG['TXT_OPEN_ISSUE'],
		    'TXT_SHORT_NAME'                 => $_CORELANG['TXT_SHORT_NAME'],
		    'TXT_LANGUAGE_DEPENDANT_SYSTEM_VARIABLES'=> $_CORELANG['TXT_LANGUAGE_DEPENDANT_SYSTEM_VARIABLES'],
		    'TXT_ADMINISTRATION_PAGES'       => $_CORELANG['TXT_ADMINISTRATION_PAGES'],
		    'TXT_WEB_PAGES'                  => $_CORELANG['TXT_WEB_PAGES'],
		    'TXT_SECTION'                    => $_CORELANG['TXT_SECTION'],
		    'TXT_DEFAULT_LANGUAGE'           => $_CORELANG['TXT_STANDARD_LANGUAGE']
		));
	    //end language variables

	    if($this->hideVariables == true){
	    	$objTemplate->setGlobalVariable(array('LANGUAGE_ADMIN_STYLE' => 'display: none'));
	    } else {
	    	$objTemplate->setGlobalVariable(array('LANGUAGE_ADMIN_STYLE' => 'display: block'));
	    }

		$objResult = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."languages ORDER BY id");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$checked = "";
				if ($objResult->fields['is_default']=="true") {
				  $checked = "checked";
				}
				$status ="<input type='radio' name='langDefaultStatus' value='".$objResult->fields['id']."' $checked />";

				$checked = "";
				if($objResult->fields['frontend']==1) {
				  $checked = "checked";
				}
				$activeStatus ="<input type='checkbox' name='langActiveStatus[".$objResult->fields['id']."]' value='1' $checked />";
				$checked = "";
				if($objResult->fields['backend']==1) {
				  $checked = "checked";
				}
				$adminStatus ="<input type='checkbox' name='langAdminStatus[".$objResult->fields['id']."]' value='1' $checked />";

				($i % 2) ? $class  = 'row1' : $class  = 'row2';
				$objTemplate->setVariable(array(
					'LANGUAGE_ROWCLASS'		=> $class,
					'LANGUAGE_LANG_ID'		=> $objResult->fields['id'],
					'LANGUAGE_LANG_NAME'	=> $objResult->fields['name'],
					'LANGUAGE_LANG_SHORTNAME'	=> $objResult->fields['lang'],
					'LANGUAGE_LANG_CHARSET'	=> $objResult->fields['charset'],
					'LANGUAGE_LANG_STATUS'	=> $status,
					'LANGUAGE_ACTIVE_STATUS'	=> $activeStatus,
					'LANGUAGE_ADMIN_STATUS'	=> $adminStatus
				));
				$objTemplate->parse('languageRow');
				$i++;
				$objResult->MoveNext();
			}
		}
	}



	/**
	* add and modify language values
	*
	* @global    object     $objDatabase
	* @global    string     DBPREFIX
	* @return    string     parsed content
	*/
	function modifyLanguage()
	{
		global $_CORELANG, $objDatabase;
		if (!empty($_POST['submit']) AND (isset($_POST['addLanguage']) && $_POST['addLanguage']=="true")){
			//-----------------------------------------------
			// Add new language with all variables
			//-----------------------------------------------
			if (!empty($_POST['newLangName']) AND !empty($_POST['newLangShortname'])){
	            $newLangShortname = addslashes(strip_tags($_POST['newLangShortname']));
	            $newLangName = addslashes(strip_tags($_POST['newLangName']));
	            $newLangCharset = addslashes(strip_tags($_POST['newLangCharset']));
				$objResult = $objDatabase->Execute("SELECT lang FROM ".DBPREFIX."languages WHERE lang='".$newLangShortname."'");
				if ($objResult !== false) {
					if ($objResult->RecordCount()>=1) {
						$this->statusMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
						return false;
					} else {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."languages SET lang='".$newLangShortname."',
																		   name='".$newLangName."',
																		   charset='".$newLangCharset."',
																		   is_default='false'");
						$newLanguageId = $objDatabase->Insert_ID();
						if(!empty($newLanguageId))
						{
							$objResult = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."languages WHERE is_default='true'", 1);
							if ($objResult !== false && !$objResult->EOF) {
								$defaultLanguage=$objResult->fields['id'];

								$objResult = $objDatabase->Execute("SELECT varid,content,module FROM ".DBPREFIX."language_variable_content WHERE 1 AND lang=".$defaultLanguage);
								if ($objResult !== false) {
									while (!$objResult->EOF) {
										$arrayLanguageContent[$objResult->fields['varid']]=stripslashes($objResult->fields['content']);
										$arrayLanguageModule[$objResult->fields['varid']]=$objResult->fields['module'];
										$objResult->MoveNext();
									}
									foreach ($arrayLanguageContent as $varid => $content) {
										$LanguageModule = $arrayLanguageModule[$varid];
										$objDatabase->Execute("INSERT INTO ".DBPREFIX."language_variable_content SET varid=".$varid.", content='".addslashes($content)."', module=".$LanguageModule.", lang=".$newLanguageId.", status=0");
									}
									$this->statusMessage = $_CORELANG['TXT_NEW_LANGUAGE_ADDED_SUCCESSFUL'];
							        return true;
								}
							}
					    }else{
							$this->statusMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
							return false;
						}
					}
				}
	        }
		}
	   elseif(!empty($_POST['submit']) AND ( $_POST['modLanguage'] == "true")){
			//-----------------------------------------------
			// Update languages
			//-----------------------------------------------
			foreach ($_POST['langName'] as $id => $name) {
				$active = 0;
				if (isset($_POST['langActiveStatus'][$id]) && $_POST['langActiveStatus'][$id]==1 ){
					$active = 1;
				}
				$status = "false";
				if($_POST['langDefaultStatus']==$id){
				    $status = "true";
				}
				$adminstatus = 0;
				if(isset($_POST['langAdminStatus'][$id]) && $_POST['langAdminStatus'][$id]==1){
					$adminstatus = 1;
				}
				$objDatabase->Execute("UPDATE ".DBPREFIX."languages SET name='".$name."', frontend=".$active." , is_default='".$status."',backend='".$adminstatus."' WHERE id=".$id);
			}
			$this->statusMessage = $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
			return true;
	   }
		return false;
	}




	function getSearchOptionMenu($dbTableName, $selectedOption="")
	{
		global $objDatabase;
		$strMenu = "";
		if($dbTableName=="languages" OR $dbTableName=="modules")
		{
		    $q = "SELECT id, name FROM ".DBPREFIX.$dbTableName." WHERE 1 ORDER BY id";
			$objResult = $objDatabase->Execute($q);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$selected = "";
					if($selectedOption==$objResult->fields['id']){
						$selected = "selected";
					}
					if($objResult->fields['id']!=0){
						$name = $objResult->fields['name'];
						if ($dbTableName == 'modules') {
							switch ($objResult->fields['name']) {
								case 'media1':
									$name = 'media';
									break;

								case 'media2':
								case 'media3':
									$name = '';
									break;
							}
						}
						if (!empty($name)) {
					    	$strMenu .="<option value=\"".$objResult->fields['id']."\" ".$selected.">".$name."</option>\n";
						}
					}
					$objResult->MoveNext();
				}
			}
		}
		return $strMenu;
	}



	/**
    * checks if the permissions on the language directory are correct
    *
	* @return   boolean
    */
	function checkPermissions()
	{
		if(is_writeable($this->filePath) AND
		   is_dir($this->filePath))
		{
			return true;
		}else{
			return false;
		}
	}

	/**
    * createXML: parse out the XML
    *
	* @global	string		$objDatabase
	* @global	array		$_CORELANG
    */
    function createFiles()
    {
    	global $objDatabase, $_CORELANG;

    	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';

    	$arrModules = array();
    	$arrLanguages = array();
    	$arrModulesPath = array();
    	$arrModuleVariables = array();
    	$arrErrorFiles = array();
    	$objFile = new File();

    	$strHeader = "/**\n* Contrexx CMS\n* generated date ".date('r',time())."\n**/\n\n";

    	// generate the arrays $arrModulesPath and $arrModules
		$query = "SELECT id, name, is_core FROM ".DBPREFIX."modules";
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				if(strlen($objResult->fields['name'])>0){
					switch($objResult->fields['name']){
						case 'core':
							$arrModulesPath[$objResult->fields['name']]['sys'] = ASCMS_DOCUMENT_ROOT;
							$arrModulesPath[$objResult->fields['name']]['web'] = ASCMS_PATH_OFFSET;
							break;
						case 'media1':
							$arrModulesPath['media']['sys'] = ASCMS_CORE_MODULE_PATH.'/media';
							$arrModulesPath['media']['web'] = ASCMS_CORE_MODULE_WEB_PATH.'/media';
							$objResult->fields['name'] = 'media';
							break;
						case 'media2':
						case 'media3':
							$objResult->fields['name'] = "";
							break;
						default:
						$arrModulesPath[$objResult->fields['name']]['sys'] = ($objResult->fields['is_core'] == 1 ? ASCMS_CORE_MODULE_PATH : ASCMS_MODULE_PATH).'/'.$objResult->fields['name'];
						$arrModulesPath[$objResult->fields['name']]['web'] = ($objResult->fields['is_core'] == 1 ? ASCMS_CORE_MODULE_WEB_PATH : ASCMS_MODULE_WEB_PATH).'/'.$objResult->fields['name'];
					}
					if (!empty($objResult->fields['name'])) {
						$arrModulesPath[$objResult->fields['name']]['sys'] .= '/lang/';
						$arrModulesPath[$objResult->fields['name']]['web'] .= '/lang/';
					}
				}
				$arrModules[$objResult->fields['id']] = array(
					'id'	=>	$objResult->fields['id'],
					'name'	=>	$objResult->fields['name']
    			);
    			$objResult->MoveNext();
			}
		}

    	// get language array
    	$query = "SELECT id, lang FROM ".DBPREFIX."languages";
    	$objResult = $objDatabase->Execute($query);
    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
    			$arrLanguages[$objResult->fields['id']] = array(
					'id'	=> $objResult->fields['id'],
					'lang'	=> $objResult->fields['lang']
				);
				$objResult->MoveNext();
    		}
    	}

    	// get language variables
    	$query = "SELECT vn.name, vn.module_id, vn.backend, vn.frontend, vc.content, vc.lang_id
    				FROM ".DBPREFIX."language_variable_names AS vn,
    					 ".DBPREFIX."language_variable_content AS vc
				   WHERE vn.id=vc.varid";

    	// generate array $arrModuleVariables including the variables
    	$objResult = $objDatabase->Execute($query);
    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
				if($objResult->fields['module_id'] == 0){
					$moduleId = 1;
				} else {
					$moduleId = $objResult->fields['module_id'];
				}
				if($objResult->fields['backend'] == 1){
					$arrModuleVariables[$moduleId][$objResult->fields['lang_id']]['backend'][$objResult->fields['name']] = $objResult->fields['content'];
				}
				if($objResult->fields['frontend'] == 1){
					$arrModuleVariables[$moduleId][$objResult->fields['lang_id']]['frontend'][$objResult->fields['name']] = $objResult->fields['content'];
				}
				$objResult->MoveNext();
    		}
    	}
    	// generate array $arrOutput with the data to write into files
    	foreach ($arrModuleVariables as $moduleId => $arrLanguageVariables){
    		foreach ($arrLanguageVariables as $langId => $arrModeVariables){
    			$filePath = $arrModulesPath[$arrModules[$moduleId]['name']]['sys'].$arrLanguages[$langId]['lang'].'/';
    			$webFilePath = $arrModulesPath[$arrModules[$moduleId]['name']]['web'].$arrLanguages[$langId]['lang'].'/';
    			foreach ($arrModeVariables as $strMode => $arrVariables){
    				$fileName = $strMode.".php";
    				$arrOutput[$filePath.$fileName]['filename'] = $fileName;
    				$arrOutput[$filePath.$fileName]['path'] = $filePath;
    				$arrOutput[$filePath.$fileName]['webpath'] = $webFilePath;
    				foreach ($arrVariables as $strName => $strContent){
    					$strContent = stripslashes($strContent);
    					$strContent = str_replace("\"", "\\\"", $strContent);
    					$arrOutput[$filePath.$fileName]['content'] .= "$"."_ARRAYLANG['".$strName."'] = \"".$strContent."\";\n";
    				}
    			}
    		}
    	}
    	unset($arrModuleVariables);

    	// write variables to files
    	foreach ($arrOutput as $file => $strOutput){
    		//$objFile->setChmod($strOutput['path'], $strOutput['webpath'], $strOutput['filename']);
    		$fileHandle = @fopen($file,"w");
			if($fileHandle)
			{
				@fwrite($fileHandle,"<?php\n".$strHeader.$strOutput['content']."?>");
				@fclose($fileHandle);
			} else {
				array_push($arrErrorFiles,$file);
			}
    	}

    	unset($arrOutput);
    	if(count($arrErrorFiles)>0){
    		foreach ($arrErrorFiles as $file){
    			$this->statusMessage .= "<br />".$_CORELANG['TXT_COULD_NOT_WRITE_TO_FILE']." (".$file.")";
    		}
    	} else {
    		$this->statusMessage .= "<br />".$_CORELANG['TXT_SUCCESSFULLY_EXPORTED_TO_FILES'];
    	}
    }
}
?>
