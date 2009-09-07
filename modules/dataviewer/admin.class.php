<?php
/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <ben@comvation.com>
 * @version		1.0.1
 * @package     contrexx
 * @subpackage  module_dataviewer
 */

class Dataviewer {
    public $_objTpl;
    public $_pageTitle;
    public $strErrMessage = '';
    public $strOkMessage = '';
    
    /**
     * PHP5 constructor
     * @global HTML_Template_Sigma
     * @global array $_ARRAYLANG
     */
    function __construct()
    {
       global $_ARRAYLANG, $_CORELANG, $objTemplate;
       
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/dataviewer/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $objTemplate->setVariable("CONTENT_NAVIGATION",
            							"<a href='index.php?cmd=dataviewer'>".$_ARRAYLANG['TXT_DATAVIEWER_OVERVIEW']."</a>
            							<a href='index.php?cmd=dataviewer&amp;act=new'>".$_ARRAYLANG['TXT_DATAVIEWER_NEW_PROJECT']."</a>
            							<a href='index.php?cmd=dataviewer&amp;act=settings'>".$_ARRAYLANG['TXT_DATAVIEWER_SETTINGS']."</a>");
    }

    
    /**
    * Set the backend page
    *
    * @access public
    * @global HTML_Template_Sigma
    * @global array $_ARRAYLANG
    */
    function getPage() {
		global $objTemplate, $_ARRAYLANG;
		$_GET['act'] = !empty($_GET['act']) ? $_GET['act'] : "";
		$_GET['id']  = !empty($_GET['id']) ? $_GET['id'] : "";
		
		switch ($_GET['act']) {
			case "edit":
				$this->editProject($_GET['id']);
				break;
			case "new":
				$this->newProject();
				break;
			case "filter":
				$this->filter($_GET['id']);
				break;
			case "placeholder":
				$this->placeholder($_GET['id']);
				break;
			case "import":
				$this->import($_GET['id']);
				break;
			case "settings":
				$this->settings();
				break;
			default:
				$this->overview();
				break;
		}
		
		$objTemplate->setVariable(array(
			'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
			'ADMIN_CONTENT'             => $this->_objTpl->get(),
			'CONTENT_TITLE'             => $this->_pageTitle,
		));
		
		return $this->_objTpl->get();
	}
    
	   
	/**
	 * handles the overview view and actions
	 *
	 */
    function overview() {   	
        global $_ARRAYLANG, $objDatabase;
        $this->_objTpl->loadTemplateFile('module_dataviewer_overview.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_ADMINISTRATION'];
        
        $_GET['setstatus'] = !empty($_GET['setstatus']) ? $_GET['setstatus'] : "";
        $_GET['delete']    = !empty($_GET['delete']) ? $_GET['delete'] : "";
        
        //set status
        if ($_GET['setstatus']) {
        	$_GET['setstatus'] = $_GET['setstatus'] == "y" ? 1 : 0;
        	$this->changeStatus($_GET['id'], $_GET['setstatus']);
        }
        
        //delete project
        if ($_GET['delete']) {
        	if($this->deleteProject($_GET['delete'])) {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strOkMessage = $_ARRAYLANG['TXT_PROJECT_DELETED']
				));	
        	} else {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strErrMessage = $_ARRAYLANG['TXT_PROJECT_COULDN_BE_DELETED']
				));	
        	}
        }
        
        
        //select all projects
        $queryProjects     = "SELECT * FROM ".DBPREFIX."module_dataviewer_projects";
		$objResultProjects = $objDatabase->Execute($queryProjects);
		
		$i = 0;	
		while (!$objResultProjects->EOF) {
			//display choosen filter nicer
			$filtersString = "";
			foreach (explode(";", $objResultProjects->fields['filters']) as $filter) {
				if ($filter !== "") {
					$filtersString .= $filter . ", ";	
				}
			}
			$filtersString = substr($filtersString, 0, strlen($filtersString)-2);
			
			//format language
			$langExplode = explode(";", $objResultProjects->fields['language']);
			$languages = "";
			foreach ($langExplode as $lang) {
				$languages .= $this->getLanguageName($lang) . ", ";
			}
			$languages = substr($languages,0,strlen($languages)-2);
						
			$this->_objTpl->setVariable(array(
				'ID'				=> $objResultProjects->fields['id'],
				'STATUS'			=> $objResultProjects->fields['status'] == 1 ? '<a href="index.php?cmd=dataviewer&amp;id=' . $objResultProjects->fields['id']. '&amp;setstatus=n"><img border="0" src="images/icons/led_green.gif" /></a>' : '<a href="index.php?cmd=dataviewer&amp;id=' . $objResultProjects->fields['id']. '&amp;setstatus=y"><img border="0" src="images/icons/led_red.gif" /></a>',
				'NAME'				=> $objResultProjects->fields['name'],
				'DESCRIPTION' 		=> $objResultProjects->fields['description'],
				'LANGUAGE' 			=> $languages,
				'COUNTRYBASED' 		=> $objResultProjects->fields['countrybased'] == 1 ? $_ARRAYLANG['TXT_YES'] : $_ARRAYLANG['TXT_NO'],
				'FILTER' 			=> $filtersString,
				'ROWCLASS' 			=> ($i % 2 == 0) ? 'row1' : 'row2',
				'TXT_PLACEHOLDER'        	=> $_ARRAYLANG['TXT_PLACEHOLDER'],
            	'TXT_EDIT'        			=> $_ARRAYLANG['TXT_EDIT'],
            	'TXT_DELETE'        		=> $_ARRAYLANG['TXT_DELETE'],
            	'TXT_IMPORT'        		=> $_ARRAYLANG['TXT_IMPORT'],
            	'TXT_EDIT_FILTER'			=> $_ARRAYLANG['TXT_EDIT_FILTER'],
			));
			
			$i++;
			$this->_objTpl->parse('projectRow');
			$objResultProjects->MoveNext();
		}
		
		
		if (substr($_SERVER['REQUEST_URI'],-5) == "added") {
    		$this->_objTpl->setVariable(array(
				'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_PROJECT_CREATED']
			));
    	}

        $this->_objTpl->setVariable(array(
            'TXT_PROJECTNAME'        	=> $_ARRAYLANG['TXT_PROJECTNAME'],         
            'TXT_PROJECTDESCRIPTION' 	=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
            'TXT_FUNCTIONS'        		=> $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_PROJECTSTATUS'        	=> $_ARRAYLANG['TXT_PROJECTSTATUS'],
            'TXT_ADMINISTRATE_PROJECTS' => $_ARRAYLANG['TXT_ADMINISTRATE_PROJECTS'],
            'TXT_LANGUAGE'        		=> $_ARRAYLANG['TXT_LANGUAGE'],
            'TXT_COUNTRY_BASED'        	=> $_ARRAYLANG['TXT_COUNTRY_BASED'],
            'TXT_FILTER'        		=> $_ARRAYLANG['TXT_FILTER'],
            'TXT_PREVIEW'        		=> $_ARRAYLANG['TXT_PREVIEW'],
            'TXT_SAVE'        			=> $_ARRAYLANG['TXT_SAVE'],
            'TXT_DELETE_CONFIRMATION'   => $_ARRAYLANG['TXT_DELETE_CONFIRMATION'],
        ));
	}
		
	
	/**
	 * gets language name of activated languages
	 *
	 * @param  int $id;
	 * @return string name;
	 */
	function getLanguageName($id) {
		global $objDatabase;
		$query     = "SELECT name FROM ".DBPREFIX."languages WHERE id = '".$id."'";
		$objResult = $objDatabase->Execute($query);
		return $objResult->fields['name'];
	}

	
	

	/**
	* handles the view and actions for a new project
	* @param no param
	* @return no return
	*/
	function newProject() {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_new_project.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_NEW_PROJECT'];
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
		$arrSetting = $this->getSettings();
		
		//SAVE
		if ($_POST['save']) {
			$name 			= !empty($_POST['name']) ? $_POST['name'] : "";
			
			$language 		= !empty($_POST['language']) ? $_POST['language'] : "";
			$languageString = implode(";", $language);
			
			$description 	= !empty($_POST['description']) ? $_POST['description'] : "";
			
			$countryBased 	= !empty($_POST['countryBased']) ? $_POST['countryBased'] : "";
			$countryBased   = $countryBased == "y" ? 1 : 0;
			
			$status 		= !empty($_POST['status']) ? $_POST['status'] : "";
			$status 		= $status == "y" ? 1 : 0;
			
			$columns 		= !empty($_POST['column']) ? $_POST['column'] : "";
			array_pop($columns); //deletes last element, this one is empty because of the JS used to create dynamics input fields
			
			//add field country to columns
			if ($countryBased == 1) {
				$columns[] = "country";
			}
			$columnsString = implode(";", $columns);
						
			
			//create query for dataviewer_projects
			$insertProjectQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_projects 
										(name, 
										description, 
										status, 
										language,
										countrybased,
										filters)
									VALUES
										('" . $this->makeInputDBvalid($name) . "',
										'" . $description . "',
										'" . $status . "',
										'" . $languageString . "',
										'" . $countryBased  . "',
										'" . $columnsString  . "');";
			
			
			//insert record in dataviewer_projects
			if($objDatabase->Execute($insertProjectQuery)) {
				$insertProjectQueryOK = true;
			}
						
			//create query for dataviewer_projects_placeholders
			$insertPlaceholderQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_placeholders 
											(`id`, 
											`projectid`, 
											`column`)
										VALUES ";
			
			$valuesString = "";
			foreach ($columns as $key => $column) {
				$valuesString .= 	"('$key', '" . ($objDatabase->Insert_ID(DBPREFIX."module_dataviewer_projects", "id")) . "', '$column'), ";
			}
							
			//delete last ","
			$valuesString       = substr($valuesString, 0, strlen($valuesString)-2);
			$insertPlaceholderQuery = $insertPlaceholderQuery . $valuesString . ";";
			
			
			//create query for dataviewer_projects_$name
			$createProjectQuery = "CREATE TABLE ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($name) ." (";
			
			$columnsString = "";
			foreach ($columns as $column) {
				$columnsString .= $column . " VARCHAR(250), ";
			}
			
			//delete last ","
			$columnsString       = substr($columnsString, 0, strlen($columnsString)-2);
			$createProjectQuery .= $columnsString . ");";
			
			
			//execute queries
			if($insertProjectQueryOK &&
			   $objDatabase->Execute($insertPlaceholderQuery) &&
			   $objDatabase->Execute($createProjectQuery) &&
			   $this->insertPages($name) == true) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_PROJECT_CREATED']
				));	
				CSRF::header("location:index.php?cmd=dataviewer&act=added");
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_PROJECT_COULDNT_BE_CREATED']."<br />".$insertProjectQuery . "<br />" . $createProjectQuery . "<br />" . $insertPlaceholderQuery
				));	
			}
		}
		
		
		$htmlCountrybased = "";
		if ($arrSetting['use_countrybased'] == 1) {
			$htmlCountrybased = '
				<tr class="row2">
					<td width="12%" nowrap="nowrap" onmouseout="htm()" onmouseover="stm(Text[3],Style[0])">'.$_ARRAYLANG['TXT_COUNTRY_BASED'].'</td>
					<td width="88%">
						<input type="radio" name="countryBased" value="y" />'.$_ARRAYLANG['TXT_YES'].'
						<input type="radio" name="countryBased" value="n" />'.$_ARRAYLANG['TXT_NO'].'
					</td>
				</tr>
				';	
		}
			
		
		//projectstring for JS check
		$query     = "SELECT * FROM " . DBPREFIX . "module_dataviewer_projects";
		$objResult = $objDatabase->Execute($query);
		$projectsString = "";
		while (!$objResult->EOF) {
			if ($objResult->fields['id'] !== $_GET['id']) {
				$projectsString .= $objResult->fields['name']." ";	
			}
			$objResult->MoveNext();
		}
		$projectsString = substr($projectsString, 0, strlen($projectsString)-1);
			
		$this->_objTpl->setVariable(array(
			'AVAILABLE_FRONTENT_LANGUAGES'	=> $this->getFrontentLangCheckboxes(""),
			'TXT_FRONTEND_LANGUAGE' 		=> $_ARRAYLANG['TXT_FRONTEND_LANGUAGE'],
			'TXT_ACTIVE' 					=> $_ARRAYLANG['TXT_ACTIVE'],
			'TXT_INACTIVE' 					=> $_ARRAYLANG['TXT_INACTIVE'],
			'TXT_ADD_COLUMN' 				=> $_ARRAYLANG['TXT_ADD_COLUMN'],
			'TXT_PROJECT_COLUMNS' 			=> $_ARRAYLANG['TXT_PROJECT_COLUMNS'],
			'TXT_PROJECTNAME' 				=> $_ARRAYLANG['TXT_PROJECTNAME'],
			'TXT_PROJECTDESCRIPTION'		=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
			'TXT_PROJECTSTATUS' 			=> $_ARRAYLANG['TXT_PROJECTSTATUS'],
			'TXT_PREVIEW' 					=> $_ARRAYLANG['TXT_PREVIEW'],
			'TXT_SAVE' 						=> $_ARRAYLANG['TXT_SAVE'],	
			'TXT_ONE_COLUMN_OR_MORE' 		=> $_ARRAYLANG['TXT_ONE_COLUMN_OR_MORE'],
			'TXT_COLUMNS_MUSTNT_BE_EMPTY' 	=> $_ARRAYLANG['TXT_COLUMNS_MUSTNT_BE_EMPTY'],
			'TXT_ENTER_A_NAME' 				=> $_ARRAYLANG['TXT_ENTER_A_NAME'],
			'TXT_CHOOSE_FRONTENT_LANG' 		=> $_ARRAYLANG['TXT_CHOOSE_FRONTENT_LANG'],
			'TXT_ENTER_DESCRIPTION' 		=> $_ARRAYLANG['TXT_ENTER_DESCRIPTION'],
			'TXT_CHOOSE_COUNTRYBASED' 		=> $_ARRAYLANG['TXT_CHOOSE_COUNTRYBASED'],
			'TXT_CHOOSE_STATUS' 			=> $_ARRAYLANG['TXT_CHOOSE_STATUS'],
			'TXT_COLUMNS_DOUBLED_NAMED' 	=> $_ARRAYLANG['TXT_COLUMNS_DOUBLED_NAMED'],
			'TABLE_ROW_COUNTRYBASED' 		=> $htmlCountrybased,
			'PROJECTSSTRING' 				=> $projectsString,
			'ID'			 				=> $_GET['id'],
			'TIP_PROJECTNAME'				=> $_ARRAYLANG['TIP_PROJECTNAME'],
			'TIP_FRONTEND_LANG' 			=> $_ARRAYLANG['TIP_FRONTEND_LANG'],
			'TIP_COUNTRYBASED' 				=> $_ARRAYLANG['TIP_COUNTRYBASED'],
			'TIP_DESCRIPTION' 				=> $_ARRAYLANG['TIP_DESCRIPTION'],
			'TIP_STATUS' 					=> $_ARRAYLANG['TIP_STATUS'],
			'TIP_COLUMN_NAME' 				=> $_ARRAYLANG['TIP_COLUMN_NAME']
		));	
	}
	
	
	
	/**
	* handles the view and actions for editing a project
	* @param no param
	* @return no return
	*/
	function editProject() {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_edit_project.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_EDIT_PROJECT'];
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
		$id            = !empty($_GET['id']) ? $_GET['id'] : "";
		
		//SAVE
		if ($_POST['save']) {
			$id 			= !empty($_POST['id']) ? $_POST['id'] : "";
			$name 			= !empty($_POST['name']) ? $_POST['name'] : "";
			$orderBy 		= !empty($_POST['orderby']) ? $_POST['orderby'] : "";
			
			$language 		= !empty($_POST['language']) ? $_POST['language'] : "";
			$languageString = implode(";", $language);
			
			$description 	= !empty($_POST['description']) ? $_POST['description'] : "";
			
			$columns 		= !empty($_POST['column']) ? $_POST['column'] : "";
			array_pop($columns); //deletes last element, this one is empty because of the JS used to create dynamics input fields
			
			
			$columnsString = implode(";", $columns);
						
			$projectnameOld = $this->makeInputDBvalid($this->getProjectName($id));
			
			//create query for dataviewer_projects
			$updateProjectQuery = "	UPDATE ".DBPREFIX."module_dataviewer_projects SET
										name = '" . $name . "', 
										description = '" . $description . "', 
										language = '" . $languageString . "',
										filters = '" . $columnsString . "',
										order_by = '" . $orderBy . "'
									WHERE
										id = '".$id."';";
			
			//rename table
			$renameTableQuery = "";
			if ($this->makeInputDBvalid($this->getProjectName($id)) <> $this->makeInputDBvalid($name)) {
				$renameTableQuery = "RENAME TABLE ".DBPREFIX."module_dataviewer_".$projectnameOld."  TO ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($name).";";	
			}
			
			$columnsDB = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$projectnameOld);
			unset($columnsDB['COUNTRY']);

					
			//ALTER
			$alterQuery = "";
			
			$i = 0;
			foreach ($columnsDB as $column) {
				//RENAME
				if($column !== $columns[$i] && key_exists($i, $columns)) {
					$alterQuery[] = "ALTER TABLE ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id))." CHANGE ".$column." ".$columns[$i]." VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL; ";
				}
				
				//DELETE
				if (!key_exists($i, $columns)) {
					$alterQuery[] = "ALTER TABLE ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id))." DROP ".$column."; ";
				}	
						
				unset($columns[$i]);	
				$i++;
			}
			
			//ADD
			if (count($columns) > 0) {
				foreach ($columns as $column) {
					$alterQuery[] = "ALTER TABLE ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id))." ADD ".$column." VARCHAR(250) NOT NULL; ";
				}
			}
						
			
			$columns 		= !empty($_POST['column']) ? $_POST['column'] : "";
			array_pop($columns); //deletes last element, this one is empty because of the JS used to create dynamics input fields


			//create query for dataviewer_projects_placeholders
			$deletePlaceholderQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_placeholders WHERE projectid = '".$id."';";
											
			$insertPlaceholderQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_placeholders 
											(`id`, 
											`projectid`, 
											`column`)
										VALUES ";
			
			$valuesString = "";
			
			foreach ($columns as $key => $column) {
				$valuesString .= 	"('$key', '".$id."', '$column'), ";
			}
							
//			delete last ","
			$valuesString = substr($valuesString, 0, strlen($valuesString)-2);
			$insertPlaceholderQuery = $insertPlaceholderQuery . $valuesString;
			
						
			$error = false;
			if ($alterQuery !== "") {
				foreach ($alterQuery as $singelQuery) {
					if(!$objDatabase->Execute($singelQuery)) {
						$error = true;
					}	
				}
			}			
			
			if(!$objDatabase->Execute($updateProjectQuery)) {$error = true;}
			if(!$objDatabase->Execute($deletePlaceholderQuery)) {$error = true;}
			if(!$objDatabase->Execute($insertPlaceholderQuery)) {$error = true;}
			if ($renameTableQuery !== "") {
				if(!$objDatabase->Execute($renameTableQuery)) {$error = true;}	
			}			
			
			//check queries
			if($this->updateContentPage($this->getProjectName($id), $projectnameOld) && !$error) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_PROJECT_UPDATED']
				));	
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_PROJECT_COULDNT_BE_UPDATED']."<br />".$updateProjectQuery . "<br />" . implode("<br />", $alterQuery) . "<br />" . $renameTableQuery . "<br />" . $deletePlaceholderQuery . "<br />" . $insertPlaceholderQuery
				));	
			}
		}
		
		//projectstring for JS check
		$query     = "SELECT * FROM " . DBPREFIX . "module_dataviewer_projects";
		$objResult = $objDatabase->Execute($query);
		$projectsString = "";
		while (!$objResult->EOF) {
			if ($objResult->fields['id'] !== $_GET['id']) {
				$projectsString .= $objResult->fields['name']." ";	
			}
			$objResult->MoveNext();
		}
		$projectsString = substr($projectsString, 0, strlen($projectsString)-1);
		
		
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
		$i = 0;
		foreach ($columns as $column) {
			if ($column !== "country") {
				$this->_objTpl->setVariable(array(
					'COLUMN_NAME'	=> $column,
					'i'				=> $i,
				));		
				$this->_objTpl->parse('columnsRow');	
				$i++;	
			}
		}

		
		$query     = "SELECT * FROM " . DBPREFIX . "module_dataviewer_projects WHERE id = '".$id."';";
		$objResult = $objDatabase->Execute($query);
		$columnsDB = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
		
		$orderByOptions = "";
		foreach ($columnsDB as $column) {
			$selected = "";
			if($column == $objResult->fields['order_by']) {
				$selected = "selected";
			}
			$orderByOptions .= '<option value="' . $column . '" ' .$selected. ' /> ' . $column;		
		}
			
		$this->_objTpl->setVariable(array(
			'POST_NAME'						=> $objResult->fields['name'],
			'POST_DESCRIPTION'				=> $objResult->fields['description'],
			'AVAILABLE_FRONTENT_LANGUAGES'	=> $this->getFrontentLangCheckboxes($id),
			'ID'							=> $id,
			'ORDER_BY_OPTIONS'				=> $orderByOptions,
			'TXT_ORDER_BY'					=> $_ARRAYLANG['TXT_ORDER_BY'],
			'TXT_FRONTEND_LANGUAGE' 		=> $_ARRAYLANG['TXT_FRONTEND_LANGUAGE'],
			'TXT_YES' 						=> $_ARRAYLANG['TXT_YES'],
			'TXT_NO' 						=> $_ARRAYLANG['TXT_NO'],
			'TXT_ACTIVE' 					=> $_ARRAYLANG['TXT_ACTIVE'],
			'TXT_INACTIVE' 					=> $_ARRAYLANG['TXT_INACTIVE'],
			'TXT_ADD_COLUMN' 				=> $_ARRAYLANG['TXT_ADD_COLUMN'],
			'TXT_PROJECT_COLUMNS' 			=> $_ARRAYLANG['TXT_PROJECT_COLUMNS'],
			'TXT_COUNTRY_BASED' 			=> $_ARRAYLANG['TXT_COUNTRY_BASED'],
			'TXT_PROJECTNAME' 				=> $_ARRAYLANG['TXT_PROJECTNAME'],
			'TXT_PROJECTDESCRIPTION'		=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
			'TXT_PROJECTSTATUS' 			=> $_ARRAYLANG['TXT_PROJECTSTATUS'],
			'TXT_PREVIEW' 					=> $_ARRAYLANG['TXT_PREVIEW'],
			'TXT_SAVE' 						=> $_ARRAYLANG['TXT_SAVE'],	
			'TXT_ONE_COLUMN_OR_MORE' 		=> $_ARRAYLANG['TXT_ONE_COLUMN_OR_MORE'],
			'TXT_COLUMNS_MUSTNT_BE_EMPTY' 	=> $_ARRAYLANG['TXT_COLUMNS_MUSTNT_BE_EMPTY'],
			'TXT_ENTER_A_NAME' 				=> $_ARRAYLANG['TXT_ENTER_A_NAME'],
			'TXT_CHOOSE_FRONTENT_LANG' 		=> $_ARRAYLANG['TXT_CHOOSE_FRONTENT_LANG'],
			'TXT_ENTER_DESCRIPTION' 		=> $_ARRAYLANG['TXT_ENTER_DESCRIPTION'],
			'TXT_CHOOSE_COUNTRYBASED' 		=> $_ARRAYLANG['TXT_CHOOSE_COUNTRYBASED'],
			'TXT_CHOOSE_STATUS' 			=> $_ARRAYLANG['TXT_CHOOSE_STATUS'],
			'PROJECTSSTRING' 				=> $projectsString,
			'TIP_PROJECTNAME'				=> $_ARRAYLANG['TIP_PROJECTNAME'],
			'TIP_FRONTEND_LANG' 			=> $_ARRAYLANG['TIP_FRONTEND_LANG'],
			'TIP_COUNTRYBASED' 				=> $_ARRAYLANG['TIP_COUNTRYBASED'],
			'TIP_DESCRIPTION' 				=> $_ARRAYLANG['TIP_DESCRIPTION'],
			'TIP_STATUS' 					=> $_ARRAYLANG['TIP_STATUS'],
			'TIP_COLUMN_NAME' 				=> $_ARRAYLANG['TIP_COLUMN_NAME'],
			'TIP_ORDER_BY'					=> $_ARRAYLANG['TIP_ORDER_BY'],
		));	
	}
	
	
	
	/**
	 * inserts content and navigation page for a new project
	 * 
	 * @param  string $projectname
	 * @return boolean
	 */
	function insertPages($projectname) {
		global $objDatabase;
		
		//get module id
		$query     = "SELECT id from " .DBPREFIX. "modules WHERE name = 'dataviewer'";
		$objResult = $objDatabase->Execute($query);
		$moduleID  = $objResult->fields['id']; 
		
		//get parent id
		$query     = "SELECT catid from " .DBPREFIX. "content_navigation WHERE module = '".$moduleID."' AND parcat = '0'";
		$objResult = $objDatabase->Execute($query);
		$parentID  = $objResult->fields['catid']; 
		
		//get last id 
		$query     = "SELECT catid from " .DBPREFIX. "content_navigation ORDER BY catid DESC LIMIT 1";
		$objResult = $objDatabase->Execute($query);
		$lastID    = $objResult->fields['catid']; 
		
		
		//insert navigation page
		$queryNavi = "	INSERT INTO ".DBPREFIX."content_navigation (
							catid,
							is_validated,
							parcat,
							catname,
							target,
							displayorder,
							displaystatus,
							activestatus,
							cachingstatus,
							username,
							changelog,
							cmd,
							lang,
							module,
							startdate,
							enddate,
							protected,
							frontend_access_id,
							backend_access_id,
							themes_id,
							css_name)
						VALUES (
							'" . ($lastID+1) . "',
							'1',
							'" . $parentID . "',
							'" . $projectname . "',
							'_self',
							'1',
							'on',
							'1',
							'1',
							'system',
							'" . mktime() . "',
							'" . $this->makeInputDBvalid($projectname) . "',
							'1',
							'" . $moduleID . "',
							'0000-00-00',
							'0000-00-00',
							'0',
							'0',
							'0',
							'0',
							'')";
		
		
		//insert content page
		$queryContent = "	INSERT INTO ".DBPREFIX."content (
							id,
							content,
							title,
							metatitle,
							metadesc,
							metakeys,
							metarobots,
							css_name,
							redirect,
							expertmode)
						VALUES (
							'" . ($lastID+1) . "',
							'" . $this->createContentpage($projectname) . "',
							'" . $projectname . "',
							'" . $projectname . "',
							'" . $projectname . "',
							'" . $projectname . "',
							'" . $projectname . "',
							'',
							'',
							'n')";
		
		if ($objDatabase->Execute($queryContent) && $objDatabase->Execute($queryNavi)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	
	/**
	 * inserts content and navigation page for a new project
	 * 
	 * @param  string $projectname
	 * @return boolean
	 */
	function updateContentPage($projectname, $projectnameOld) {
		global $objDatabase;
		
		//get page id
		$query     = "SELECT catid from " .DBPREFIX. "content_navigation WHERE cmd = '".$this->makeInputDBvalid($projectnameOld)."';";
		$objResult = $objDatabase->Execute($query);
		$pageID  = $objResult->fields['catid']; 
		
		//insert content page
		$queryContent     = "UPDATE ".DBPREFIX."content SET content = '".$this->createContentpage($projectname)."' WHERE id = '".$pageID."';";
		$queryContentNavi = "UPDATE ".DBPREFIX."content_navigation SET cmd = '".$this->makeInputDBvalid($projectname)."' WHERE catid = '".$pageID."';";
		
		if ($objDatabase->Execute($queryContent) && $objDatabase->Execute($queryContentNavi)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * creates the current activated frontend languages as checkboxes
	 * 
	 * @return string $xhtml
	 */
	function getFrontentLangCheckboxes($id) {
		global $objDatabase;
		//select current frontend languages
		$query     = "SELECT * FROM " . DBPREFIX . "languages WHERE frontend = '1'";
		$objResult = $objDatabase->Execute($query);
		
		//select frontend languages from project
		if ($id !== "") {
			$queryLang     = "SELECT * FROM " . DBPREFIX . "module_dataviewer_projects WHERE id = '".$id."'";
			$objResultLang = $objDatabase->Execute($queryLang);
			$langString = $objResultLang->fields['language'];
		}
		
		
		//create xhtml
		$xhtml = "";
		while (!$objResult->EOF) {
			$checked = "";
			
			if ($id !== "") {
				if(strchr($langString, $objResult->fields['id'])) {
					$checked = "checked";
				}	
			}
			
			
			$xhtml .= '<input type="checkbox" name="language[]" id="language" value="' . $objResult->fields['id'] . '" ' .$checked. ' /> ' . $objResult->fields['name'];
			$objResult->MoveNext();
		}
		
		return $xhtml;
	}
	
	
	/**
	 * replaces whitspaces, umlaute and formats to lowercase
	 *
	 * @param  string $input
	 * @return string $input
	 */
	function makeInputDBvalid($input) {
//		$input = str_replace(" ", "_",  $input);
//		$input = str_replace("Ä", "ae", $input);
//		$input = str_replace("ä", "ae", $input);
//		$input = str_replace("Ö", "oe", $input);
//		$input = str_replace("ö", "oe", $input);
//		$input = str_replace("Ü", "ue", $input);
//		$input = str_replace("ü", "ue", $input);
		$arrPattern["/[\+\/\(\)=,;%&]+/"] = "_"; // interpunction etc.
		$arrPattern['/[\'<>\\\~$!\"]+/']  =  "'_'";  		 // quotes and other special characters
		$arrPattern['/Ä/'] 				  = "ae";  
		$arrPattern['/Ö/'] 				  = "oe";  
		$arrPattern['/Ü/'] 				  = "ue";  
		$arrPattern['/ä/'] 				  = "ae";  
		$arrPattern['/ö/'] 				  = "oe";  
		$arrPattern['/ü/'] 				  = "ue";  
		$arrPattern['/à/'] 				  = "a";  
		$arrPattern['/ç/'] 				  = "c";  
		$arrPattern['/\s/'] 			  = "_";  
		$arrPattern['/[èé]/'] 			  = "e";  
		$arrPattern['/-/'] 				  = "_";  
		
		// Fallback for everything we didn't catch by now
		$arrPattern['/[^\sa-z_-]+/i'] 	  = "_";
		$arrPattern['/[_-]{2,}/']   	  = "_";  
		$arrPattern['/^[_\.\/\-]+/'] 	  = "_";  
		
		foreach ($arrPattern as $pattern => $replacement) {
			$input = preg_replace($pattern, $replacement, $input);
		}
		
		return strtolower($input);	
	}
	
			
	/**
	 * sets the status (visible or hidden)
	 * 
	 * @param  int $id
	 * @param  int $status
	 * @return no return
	 */
	function changeStatus($id, $status) {
		global $objDatabase, $_ARRAYLANG;
		$query = "UPDATE ".DBPREFIX."module_dataviewer_projects SET status = '" . $status . "' WHERE id = '" . $id . "'";
		if ($objDatabase->Execute($query)) {
			$this->_objTpl->setVariable(array(
				'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_STATUS_UPDATED']
			));
		} else {
			$this->_objTpl->setVariable(array(
				'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_STATUS_COULDNT_BE_UPDATED']
			));
		}
	}
	
	
	/**
	 * gets the projectname by projectid
	 * 
	 * @param  int $id
	 * @return string $projectname
	 */
	function getProjectName($id) {
		global $objDatabase;
		$query     = "SELECT name FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "';";
		$objResult = $objDatabase->Execute($query);
		
		return $objResult->fields['name'];
	}
	
	
	/**
	 * handles the filter view and filter actions
	 * 
	 * @param  int $id
	 * @return no return
	 */
	function filter($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_filters.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_FILTER'];
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
				
		//UPDATE
		if ($_POST['save']) {
			$filters = !empty($_POST['filters']) ? $_POST['filters'] : "";
			$id = !empty($_GET['id']) ? $_GET['id'] : "";
			
			
			$orderChanged = false;
			foreach ($filters as $key => $filter) {
				if ($filter == "noColumn") {
					unset($filters[$key]);
					$orderChanged = true;
				}
			}
			
			
			//create filter string
			$filtersString = implode(";", $filters);
			$filtersString = str_replace("noColumn", "", $filtersString);
			
			if(strlen($filtersString) <= count($filters)) {
				$filtersString = "";
			}
						
			$updateFiltersQuery = "UPDATE ".DBPREFIX."module_dataviewer_projects SET filters = '" . $filtersString . "' WHERE id = '" . $id . "'";				
			
			if($objDatabase->Execute($updateFiltersQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_FILTER_UPDATED'] . ($orderChanged == true ? $_ARRAYLANG['TXT_ORDER_HAS_BEEN_CHANGED'] = "<br />Die Reihenfolge wurde angepasst." : "")
				));
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_FILTER_COULDNT_BE_UPDATED'] . $updateFiltersQuery
				));
			}	
		}		
		
		//get all columns
		$columns         = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
		$numberOfColumns = count($columns);
		
		//select current filters
		$currentFilterQuery = "SELECT filters FROM " . DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult = $objDatabase->Execute($currentFilterQuery);
		$filters   = explode(";", $objResult->fields['filters']);
		
		//fill up that there are as many filters as columns
		$filters = array_pad($filters, $numberOfColumns, "");
		
		//set template variables
		for($i = 0; $i < $numberOfColumns; $i++) {
			foreach ($columns as $column) {
				$this->_objTpl->setVariable(array(
					'COLUMN' 		=> $column,
					'SELECTED' 		=> $column == $filters[$i] ? 'selected="selected"' : "",
					'TXT_NO_FILTER'	=> $_ARRAYLANG['TXT_NO_FILTER']
				));		
				$this->_objTpl->parse('columnsRow');
			}
		
			$this->_objTpl->setVariable(array(
				'FILTER'	=> "Filter " . ($i == 0 ? "1" : $i+1),
				'ROWCLASS' 	=> ($i % 2 == 0) ? 'row1' : 'row2'
			));		
			$this->_objTpl->parse('filterRow');
		}	
		
		$this->_objTpl->setVariable(array(
			'ID'						=> $id,
			'TXT_FILTER'				=> $_ARRAYLANG['TXT_FILTER'],
			'TXT_SAVE'					=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_NO_DOUBLE_ASSIGNMENTS'	=> $_ARRAYLANG['TXT_NO_DOUBLE_ASSIGNMENTS'],
			'TIP_EVERY_FILTER'			=> $_ARRAYLANG['TIP_EVERY_FILTER']
		));		
	}
		
	
	/**
	 * handles the placeholder view and placeholder actions
	 * 
	 * @param int $id
	 * @return no return
	 */
	function placeholder($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_placeholders.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_PLACEHOLDER'];
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
				
		//UPDATE
		if ($_POST['save']) {
			$placeholders = !empty($_POST['placeholders']) ? $_POST['placeholders'] : "";
			$id = !empty($_GET['id']) ? $_GET['id'] : "";
			
			//create querys
			foreach ($placeholders as $key => $placeholder) {
				$updatePlaceholdersQuery[] = "UPDATE ".DBPREFIX."module_dataviewer_placeholders SET `column` = '" . $placeholder . "' WHERE `id` = " . $key . " AND `projectid` = " . $id . "";	
			}
			
			$error = false;
			$queryForErrorReport = "";
			foreach ($updatePlaceholdersQuery as $query) {
				if (!$objDatabase->Execute($query)) {
					$error = true;
				}
				$queryForErrorReport .= $query . "<br />";
			}
			
			if ($error) {
				$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_PLACEHOLDER_COULDNT_BE_UPDATED']  . $queryForErrorReport
					));	
			} else {
				$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_PLACEHOLDER_UPDATED']
					));	
			}
		}
			
			
		//get all columns
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
				
		//select all placeholder from project
		$getPlaceholdersQuery  = "SELECT * FROM " . DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" . $id . "' ORDER BY id ASC";
		$objPlaceholdersResult = $objDatabase->Execute($getPlaceholdersQuery);
		
		//set template variables
		$i = 0;
		while (!$objPlaceholdersResult->EOF) {
			foreach ($columns as $column) {
				$this->_objTpl->setVariable(array(
					'COLUMN' 	=> $column,
					'SELECTED' 	=>  $column == $objPlaceholdersResult->fields['column'] ? 'selected="selected"' : ""
				));
				$this->_objTpl->parse('columnsRow');
			}
			
			$objPlaceholdersResult->MoveNext();
			
			$this->_objTpl->setVariable(array(
				'PLACEHOLDERID'		=> $i == 0 ? "1" : $i+1,
				'ROWCLASS' 			=> ($i % 2 == 0) ? 'row1' : 'row2',
				'TXT_NO_ASSIGNMENT'	=> $_ARRAYLANG['TXT_NO_ASSIGNMENT']
			));		
			$this->_objTpl->parse('placeholderRow');
			$i++;
		}	
			
		$this->_objTpl->setVariable(array(
			'ID'							=> $id,
			'TXT_PLACEHOLDER'				=> $_ARRAYLANG['TXT_PLACEHOLDER'],
			'TXT_WHERE_TO_PUT_PLACEHOLDER'	=> $_ARRAYLANG['TXT_WHERE_TO_PUT_PLACEHOLDER'],
			'TIP_PLACEHOLDER'				=> $_ARRAYLANG['TIP_PLACEHOLDER'],
			'TXT_SAVE'						=> $_ARRAYLANG['TXT_SAVE']
		));		
	}
			
		
	/**
	* deletes the record in dataviewer_projects, the project table,
	* content & navigation pages and the placeholder
	* 
	* @param int $id
	* @return boolean
	*/
	function deleteProject($id) {
		global $objDatabase;
		
		//get catid from navigation page
		$query 						   = "SELECT catid FROM " .DBPREFIX. "content_navigation WHERE cmd = '" . $this->makeInputDBvalid($this->getProjectName($id)) . "';";
		$objResult 					   = $objDatabase->Execute($query);
		$catID 						   = $objResult->fields['catid'];
		
		$deleteProjectTableQuery       = "DROP TABLE ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($this->getProjectName($id)).";";
		$deleteProjectRecordQuery      = "DELETE FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "';";
		$deletePlaceholdersRecordQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" . $id . "';";
		
		if ($this->makeInputDBvalid($this->getProjectName($id) !== "")) {
			$deleteNavigationPageQuery     = "DELETE FROM ".DBPREFIX."content_navigation WHERE cmd = '" . $this->makeInputDBvalid($this->getProjectName($id)) . "';";	
		}
		
		if ($catID !== "") {
			$deleteContentPageQuery        = "DELETE FROM ".DBPREFIX."content WHERE id = '" . $catID . "';";
		}		

//		echo $query . "<br>";
//		echo $catID . "<br>";
//		echo $deleteProjectTableQuery . "<br>";
//		echo $deleteProjectRecordQuery . "<br>";
//		echo $deletePlaceholdersRecordQuery . "<br>";
//		echo $deleteNavigationPageQuery . "<br>";
//		echo $deleteContentPageQuery . "<br>";
		
		$error = false;
		if(!$objDatabase->Execute($deleteProjectTableQuery)) {$error = true;}
		if(!$objDatabase->Execute($deleteProjectRecordQuery)) {$error = true;}
		if(!$objDatabase->Execute($deletePlaceholdersRecordQuery)) {$error = true;}
		if(!$objDatabase->Execute($deleteNavigationPageQuery)) {$error = true;}
		if(!$objDatabase->Execute($deleteContentPageQuery)) {$error = true;}
		
		if (!$error){
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * checks if project is countrybased
	 *
	 * @param  int $id
	 * @return boolean
	 */
	function isCountryBased($id) {
		global $objDatabase;			
		$query 		  = "SELECT countrybased FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult    = $objDatabase->Execute($query);
		
		return $objResult->fields['countrybased'] == 1 ? true : false;
	}
	
	
	/**
	* handles the view and actions for importing data to a project
	* @param int $id
	*/
	function import($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_import.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_IMPORT_DATA'];
		require_once("lib/CSVHandler.class.php");
		
		$_POST['continue'] = !empty($_POST['continue']) ? $_POST['continue'] : "";
		$_POST['import']   = !empty($_POST['import']) ? $_POST['import'] : "";

		if($this->isCountryBased($id)) {
			//generate dropdown for country list
			$query     = "SELECT * FROM " . DBPREFIX . "lib_country";
			$objResult = $objDatabase->Execute($query);
			
			while (!$objResult->EOF) {
				$this->_objTpl->setVariable(array(
					'COUNTRY' 	  => $objResult->fields['name'],
					'COUNTRYCODE' => $objResult->fields['iso_code_2'],
				));
				$this->_objTpl->parse('countryRow');
				$objResult->MoveNext();
			}
		}		
		
		$this->_objTpl->setVariable(array(
			'DISPLAY' 							=> "none",
		));	
		
		
		//CONTINUE button
		if ($_POST['continue']) {
			$file     		 = $_FILES['csvFile'];
			$selectedCountry = $_POST['country'];
			$error = false;
			
			if ($selectedCountry == "null" && $this->isCountryBased($id)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_CHOOSE_COUNTRY_WHICH_DATA_FOR']
				));	
				$error = true;
			}
			
			
			//show warning, if country allready exists in db
			if ($this->isCountryBased($id)) {
				//if records for country allready exists,
				//delete it
				if($this->countryExistsInDB($id, $selectedCountry)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_RECORDS_FOR_COUNTRY_EXISTS']
					));	
				}	
			}
			
			$this->_objTpl->setVariable(array(
				'FILENAME' 				 	=> $file['name']
			));	
			
			//generate dropdown for country list
			//SELECTED == selected
			$query     = "SELECT * FROM " . DBPREFIX . "lib_country";
			$objResult = $objDatabase->Execute($query);
			$selected  = "";
			
			while (!$objResult->EOF) {
				$selected = $objResult->fields['iso_code_2'] == $selectedCountry ? "selected" : "";
				$this->_objTpl->setVariable(array(
					'COUNTRY' 	  => $objResult->fields['name'],
					'COUNTRYCODE' => $objResult->fields['iso_code_2'],
					'SELECTED'    => $selected
				));
				$this->_objTpl->parse('countryRow');
				$objResult->MoveNext();
			}
			
			if(!$error){
			//generate selection stuff
			//filetype check		
			if ($file['type'] == "text/comma-separated-values") {
				//upload file
				if($this->uploadFile($file)) {
					$file 	= ASCMS_DATAVIEWER_TEMP_PATH . $file['name'];
					$objCSV = new CSVHandler($file, ";", "");
			
					//get all columns
					$columnsDB  = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
					$columnsCSV = $objCSV->ListAllHeader();
					
					//dont display country column
					if ($this->isCountryBased($id)) {
						unset($columnsDB["COUNTRY"]); //in db field "country" no imports are alowed
					}
					
					//create drop down menu with columns from csv file
					$xhtml = '<option value="0">'.$_ARRAYLANG['TXT_NO_ASSIGNMENT'].'</option>';
					foreach ($columnsCSV as $column) {
						$xhtml .= '<option value="' . $column . '">' . $column . '</option>';
					}
					
					//ouput	
					$i = 0;
					foreach ($columnsDB as $column) {
						$this->_objTpl->setVariable(array(
							'COLUMNS_CSV_DROPDOWN' => $xhtml,
							'COLUMN_NAME_DB' 	   => $column,
							'ROWCLASS' 			   => ($i % 2 == 0) ? 'row1' : 'row2'
						));
						
						$i++;
						$this->_objTpl->parse('columnRow');
					}
					
					$this->_objTpl->setVariable(array(
						'DISPLAY' => $error ? "none" : "block"
					));
			
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_FILE_COULDNT_BE_UPLOADED']
					));	
				}
			} else if($file['type'] == "") {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_NO_FILE_SELECTED']
				));	
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_WRONG_FILETYPE']
				));	
			}
		}}
		
		
		//IMPORT
		if ($_POST['import']) {
			$columnsDB  	= $_POST['dbColumn'];
			$columnsCSV 	= $_POST['csvColumn'];
			$hiddenFilename = $_POST['fileName'];	
			$country 		= $_POST['country'];	
			
			//check if at least one column from CSV is selected
			$csvSelected = false;
			foreach ($columnsCSV as $column) {
				if ($column !== "0") {
					$csvSelected = true;
				}
			}
			
			
			if ($csvSelected) {
				if ($this->isCountryBased($id)) {
					//if records for country allready exists,
					//delete it
					if($this->countryExistsInDB($id, $country)) {
						$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($this->getProjectName($id)) . " WHERE country = '" . $country . "'");
					}	
				}
				
				$objCSV = new CSVHandler(ASCMS_DATAVIEWER_TEMP_PATH . $hiddenFilename, ";", "");
	
				//create query for insert
				$insertDataQuery = "INSERT INTO ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($this->getProjectName($id)) . " (";
			
				//add columns manually, if project is countrybased
				if ($this->isCountryBased($id)) {
					$columnsDB[] = "country";
					$columnsCSV[] = "country";
				}
				
				//fields
				$columnsNameString = "";
				foreach ($columnsDB as $column) {
					$columnsNameString .= "`" . $column . "`,";
				}
				
				//delete last ","
				$columnsNameString  = substr($columnsNameString, 0, strlen($columnsNameString)-1);
				$columnsNameString .= ") VALUES ";
	
				//values
				$csvData 			 = $objCSV->ReadCSV();
				$columnsValuesString = "";
				
				//write all values to query string
				foreach ($csvData as $row) {
					if ($this->isCountryBased($id)) {
						$row['country'] = $country;
					}	
					
					$columnsValuesString .= "(";
					foreach ($columnsCSV as $column) {
						$columnsValuesString .= "'" . utf8_encode(mysql_escape_string($row[$column])) . "',";
					}
					$columnsValuesString  = substr($columnsValuesString, 0, strlen($columnsValuesString)-1);
					$columnsValuesString .= "), ";
				}
				
				$columnsValuesString  = substr($columnsValuesString, 0, strlen($columnsValuesString)-3);			
				$columnsValuesString .= ")";
				$insertDataQuery = $insertDataQuery . $columnsNameString . $columnsValuesString;
				
				if ($objDatabase->Execute($insertDataQuery)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_DATA_IMPORTED']
					));	
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_ERROR_AT_IMPORT'] . $insertDataQuery
					));	
				}
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_NO_DATA_IMPORTED_NO_SELECTION']
				));	
			}
			//delete file from temp folder
			unlink(ASCMS_DATAVIEWER_TEMP_PATH . $hiddenFilename);
		}
		
		$this->_objTpl->setVariable(array(
			'ID' 								=> $id,
			'COUNTRY_STATEMENT' 				=> $this->isCountryBased($id) ? $_ARRAYLANG['TXT_CHOOSE_COUNTRY'] : $_ARRAYLANG['TXT_PROJECT_IS_NOT_COUNTRYBASED'],
			'TXT_PROJECT_COLUMNS' 				=> $_ARRAYLANG['TXT_PROJECT_COLUMNS'],
			'TXT_IMPORT_FROM_CSV' 				=> $_ARRAYLANG['TXT_IMPORT_FROM_CSV'],
			'TXT_COUNTRY' 						=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_CSV_FILE' 						=> $_ARRAYLANG['TXT_CSV_FILE'],
			'TXT_CONTINUE' 						=> $_ARRAYLANG['TXT_CONTINUE'],
			'TXT_AVAILABLE_IN_DATABASE' 		=> $_ARRAYLANG['TXT_AVAILABLE_IN_DATABASE'],
			'TXT_AVAILABLE_IN_CSV' 				=> $_ARRAYLANG['TXT_AVAILABLE_IN_CSV'],
			'TXT_START_IMPORT' 					=> $_ARRAYLANG['TXT_START_IMPORT'],
			'TIP_CHOOSE_FILE' 					=> $_ARRAYLANG['TIP_CHOOSE_FILE'],
			'TIP_AVAILABLE_FIELDS_IN_DATABASE' 	=> $_ARRAYLANG['TIP_AVAILABLE_FIELDS_IN_DATABASE'],
			'TIP_AVAILABLE_FIELDS_IN_CSV' 		=> $_ARRAYLANG['TIP_AVAILABLE_FIELDS_IN_CSV']
		));	
	}
	
	
	/**
	 * uploads the csv file
	 *
	 */
	function uploadFile($file) {
		$tempFilePath = ASCMS_DATAVIEWER_TEMP_PATH;
		
		if ($file['type'] == "text/comma-separated-values") {
				if (move_uploaded_file($file['tmp_name'], $tempFilePath . $file['name'])) {
					return true;
				} else {
					return false;
				}
			}
	}
		
	
	/**
	 * checks if country allready exists in table
	 *
	 * @param  int $id
	 * @param  string $country
	 * @return boolean
	 */
	function countryExistsInDB($id, $country) {
		global $objDatabase;			
		$query 		  = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($this->getProjectName($id)) . " WHERE country = '" . $country . "'";
		$objResult    = $objDatabase->Execute($query);
		
		if($objResult->_numOfRows > 0) {
			return true;
		} else {
			return false;
		}
	}
		
	
	
	
	/**
	 * creates content page for project
	 * 
	 * @param  string $projectname
	 * @return string $xhtml;
	 */
	function createContentpage($projectname) {
		global $objDatabase, $_ARRAYLANG;
		
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($projectname));
		unset($columns['COUNTRY']);
		
		$tableHeadlines = "";
		$tableContent   = "";
		$i = 1;
		
		foreach ($columns as $column) {
			$tableHeadlines .= '<td><strong>' . $column  . '</strong></td>';
			$tableContent   .= '<td>{DATAVIEWER_PLACEHOLDER_' . $i . '}</td>';
			$i++;
		}
		
		$tableHeadlines = "<tr>" . $tableHeadlines . "</tr>" ;
		$tableContent   = "<!-- BEGIN dataviewer_row --><tr>" . $tableContent . "</tr><!-- END dataviewer_row -->" ;
		$tableStart     = '<table border="0" width="100%" id="dataviewer_Table">';
		$tableEnd       = '</table>';
		
		$xhtml .= '<!-- BEGIN dataviewer_filter_row --><div class="thisFilter">[[DATAVIEWER_FILTER]]</div><!-- END dataviewer_filter_row -->'.$tableStart . $tableHeadlines . $tableContent . $tableEnd;
		
		return html_entity_decode($xhtml);
	}
	
	
	function settings() {
		global $_ARRAYLANG, $objDatabase;
        $this->_objTpl->loadTemplateFile('module_dataviewer_settings.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_SETTINGS'];
        $useCountrybased  = !empty($_POST['useCountrybased']) ? "1" : "0";
                		        
        if ($_POST['save']) {
        	if($objDatabase->Execute("UPDATE ".DBPREFIX."module_dataviewer_settings SET setting_value = '".$useCountrybased."' WHERE setting_name = 'use_countrybased';")) {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strOkMessage = $_ARRAYLANG['TXT_SETTINGS_SAVED']
				));	
        	} else {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strErrMessage = $_ARRAYLANG['TXT_SETTINGS_NOT_SAVED']
				));	
        	}
        }
        
        $arrSetting = $this->getSettings();
        
		$this->_objTpl->setVariable(array(			
			'TXT_DATAVIEWER_SETTINGS' 			=> $_ARRAYLANG['TXT_DATAVIEWER_SETTINGS'],
			'TXT_PLACEHOLDER' 					=> $_ARRAYLANG['TXT_PLACEHOLDER'],
			'TXT_WHERE_TO_PUT_PLACEHOLDER' 		=> $_ARRAYLANG['TXT_WHERE_TO_PUT_PLACEHOLDER'],
			'TXT_HOW_TO_PLACEHOLDER_SETTINGS' 		=> $_ARRAYLANG['TXT_HOW_TO_PLACEHOLDER_SETTINGS'],
			'TXT_SAVE' 			   				=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_USE_COUNTRYBASED' 				=> $_ARRAYLANG['TXT_USE_COUNTRYBASED'],
			'TIP_USE_COUNTRYBASED' 				=> $_ARRAYLANG['TIP_USE_COUNTRYBASED'],
			'CHECKED' 			   				=> ($arrSetting['use_countrybased'] == 1) ? "checked" : ""
			
		));			
	}
	
	
	function getSettings() {
		global $objDatabase;
		$querySettings 	   = "SELECT * FROM ".DBPREFIX."module_dataviewer_settings";
		$objResultSettings = $objDatabase->Execute($querySettings);
		
		while (!$objResultSettings->EOF) {
			$arrSetting[$objResultSettings->fields['setting_name']] = $objResultSettings->fields['setting_value'];
			$objResultSettings->MoveNext();
		}
		
		return $arrSetting;
	}
}
?>
