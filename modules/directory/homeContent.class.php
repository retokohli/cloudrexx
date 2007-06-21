<?PHP
/**
 * Home content
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_directory
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/directory/lib/directoryLib.class.php';

/**
 * Home content
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_directory
 */
class dirHomeContent extends directoryLibrary
{
	var $_pageContent;
	var $_objTemplate;
	var $rssPath;
	var $rssWebPath;
	var $settings = array();

	//local settings
    var $rows 				= 2;
	var $subLimit 			= 5;
	var $rowWidth 			= "50%";
	var $arrRows			= array();
	var $arrRowsIndex	    = array();

	var $categories = array();
	var $levels = array();

	var $count = array();
    var $numLevels = array();
    var $numCategories = array();

    var $navtree;
	var $navtreeLevels = array();
	var $navtreeCategories = array();

	/**
	 * Constructor php5
	 */
	function __construct($pageContent) {
		$this->dirHomeContent($pageContent);
	}

	/**
	 * Constructor php4
	 */
    function dirHomeContent($pageContent) {
	    $this->_pageContent = $pageContent;
	    $this->_objTemplate = &new HTML_Template_Sigma('.');
	    $this->rssPath = ASCMS_DIRECTORY_FEED_PATH . '/';
	    $this->rssWebPath = ASCMS_DIRECTORY_FEED_WEB_PATH. '/';
		$this->settings = $this->getSettings();
	}

	function getContent()
	{
		global $_CONFIG, $objDatabase, $_ARRAYLANG;

		$this->_objTemplate->setTemplate($this->_pageContent,true,true);

		$this->count = '';
	    $this->numLevels ='';
	    $this->numCategories = '';

		if(isset($_GET['lid'])){
			$lId = intval($_GET['lid']);
		}else{
			$lId = 0;
		}

		if(isset($_GET['cid'])){
			$cId = intval($_GET['cid']);
		}else{
			$cId = 0;
		}

		//xml link
		$xmlLink = $this->rssWebPath."directory_latest.xml";

		//get search
		$this->getSearch();


		if($this->settings['levels']['value'] == 1){
			$arrAttributes = $this->showLevels($lId);
		}else{
			$arrAttributes = $this->showCategories($cId);
		}

		$objResult = $objDatabase->Execute("SELECT COUNT(1) AS dir_count FROM ".DBPREFIX."module_directory_dir WHERE status = 1");
		$insertFeeds = str_replace('%COUNT%', '<b>'.$objResult->fields['dir_count'].'</b>', $_ARRAYLANG['TXT_INSERT_FEEDS']);
		$this->_objTemplate->parse('showInsertFeeds');

		if($this->settings['description']['value'] == 0){
			 $arrAttributes['description'] = "";
		}

		//select View
		if  ($this->settings['indexview']['value'] == 1) {
			$this->arrRows ='';
			sort($this->arrRowsIndex);

			$i = 0;
			foreach($this->arrRowsIndex as $rowKey => $rowName){
				if ($index != substr($rowName, 0, 1)) {
					$index = substr($rowName, 0, 1);
					if($i%$this->rows==0){
						$i=1;
					}else{
						$i++;
					}

					$this->arrRows[$i] .= "<br /><b>".$index."</b><br />".substr($rowName,1);
				} else {
					$this->arrRows[$i] .= substr($rowName,1);
				}
			}
		}




		// set variables
		$this->_objTemplate->setVariable(array(
			'TYPE_SELECTION'    				=> $this->typeSelection,
			'DIRECTORY_ROW_WIDTH'				=> $this->rowWidth,
			'DIRECTORY_ROW1'					=> $this->arrRows[1]."<br />",
			'DIRECTORY_ROW2'					=> $this->arrRows[2]."<br />",
			'DIRECTORY_TITLE'  					=> $arrAttributes['title'],
			'DIRECTORY_XML_LINK'				=> $xmlLink,
			'DIRECTORY_INSERT_FEEDS'			=> $insertFeeds,
		));

		return $this->_objTemplate->get();
	}

	function showLevels($parentId)
	{
		global $objDatabase, $_ARRAYLANG;

		if(!isset($showlevels)){
			$arrLevel['showlevels'] = 1;
		}

		//get levels
		$objResult = $objDatabase->Execute("SELECT id, parentid, name FROM ".DBPREFIX."module_directory_levels WHERE status = '1' AND parentid ='".contrexx_addslashes($parentId)."' ORDER BY displayorder");

		if($objResult !== false){
			while(!$objResult->EOF){
				$this->levels['name'][$objResult->fields['id']] = $objResult->fields['name'];
				$this->levels['parentid'][$objResult->fields['id']] = $objResult->fields['parentid'];
				$objResult->MoveNext();
			}
		}

		//get level attributes
		$objResult = $objDatabase->Execute("SELECT id, name, description, showcategories, showlevels FROM ".DBPREFIX."module_directory_levels WHERE status = '1' AND id =".contrexx_addslashes($parentId)." LIMIT 1");
		if($objResult !== false){
			while(!$objResult->EOF){
				$arrLevel['title'] 			= $objResult->fields['name'];
				$arrLevel['description']  	= $objResult->fields['description'];
				$arrLevel['showentries']  	= $objResult->fields['showentries'];
				$arrLevel['showcategories'] = $objResult->fields['showcategories'];
				$arrLevel['showlevels'] 	= $objResult->fields['showlevels'];
				$objResult->MoveNext();
			}
		}

		//show level
		$i = 1;
		if(!empty($this->levels) && $arrLevel['showlevels'] == 1 && !isset($_GET['cid'])){
			foreach($this->levels['name'] as $levelKey => $levelName){
				//count entries
				$count = $this->count($levelKey, '');

				$class= $parentId==0 ? "catLink" : "subcatLink";
				$this->arrRows[$i] .= "<a class='catLink' href='?section=directory&amp;lid=".$levelKey."''>".htmlentities($levelName, ENT_QUOTES, CONTREXX_CHARSET)."</a>&nbsp;(".$count.")<br />";
				array_push($this->arrRowsIndex, substr(htmlentities($levelName, ENT_QUOTES, CONTREXX_CHARSET), 0, 1)."<a class='catLink' href='?section=directory&amp;lid=".$levelKey."''>".htmlentities($levelName, ENT_QUOTES, CONTREXX_CHARSET)."</a>&nbsp;(".$count.")<br />");


				//get level
				if($this->levels['parentid'][$levelKey] == 0){
					$objResult = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."module_directory_levels WHERE status = '1' AND parentid =".contrexx_addslashes($levelKey)." ORDER BY displayorder LIMIT ".contrexx_addslashes($this->subLimit)."");
					if($objResult !== false){
						while(!$objResult->EOF){
							$this->arrRows[$i] .= "<a class='subcatLink' href='?section=directory&amp;lid=".$objResult->fields['id']."''>".htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)."</a>, ";
							$objResult->MoveNext();
						}
					}

					if($objResult->RecordCount() != 0){
						$this->arrRows[$i] .= "<br />";
					}
				}

				if($i%$this->rows==0){
					$i=1;
				}else{
					$i++;
				}
			}
		}


		if($arrLevel['showcategories'] == 1){
			if(isset($_GET['cid'])){
				$arrCategories 				= $this->showCategories(intval($_GET['cid']));
				$arrLevel['title'] 			= $arrCategories['title'];
				$arrLevel['description']  	= $arrCategories['description'];
				$arrLevel['showentries']  	= $arrCategories['showentries'];
			}else{
				$this->showCategories(0);
			}

		}

		return $arrLevel;
	}


	function showCategories($parentId)
	{
		global $objDatabase, $_ARRAYLANG;

		if(!empty($_GET['lid'])){
			$levelLink = "&amp;lid=".intval($_GET['lid']);
		}else{
			$levelLink = "";
		}

		//get categories
		$objResult = $objDatabase->Execute("SELECT id, parentid, name, showentries FROM ".DBPREFIX."module_directory_categories WHERE status = '1' AND parentid =".contrexx_addslashes($parentId)." ORDER BY displayorder");

		if($objResult !== false){
			while(!$objResult->EOF){
				$this->categories['name'][$objResult->fields['id']] = $objResult->fields['name'];
				$this->categories['parentid'][$objResult->fields['id']] = $objResult->fields['parentid'];
				$objResult->MoveNext();
			}
		}

		//get categorie attributes
		$objResult = $objDatabase->Execute("SELECT id, name, description, showentries FROM ".DBPREFIX."module_directory_categories WHERE status = '1' AND id =".contrexx_addslashes($parentId)." LIMIT 1");
			if($objResult !== false){
				while(!$objResult->EOF){
					$arrCategories['title'] 		= $objResult->fields['name'];
					$arrCategories['description'] 	= $objResult->fields['description'];
					$arrCategories['showentries'] 	= $objResult->fields['showentries'];
					$objResult->MoveNext();
				}
			}

		//show categories
		$i = 1;
		if(!empty($this->categories)){
			foreach($this->categories['name'] as $catKey => $catName){
				//count entries
				$count = $this->count($_GET['lid'], $catKey);

				$class= $parentId==0 ? "catLink" : "subcatLink";
				$this->arrRows[$i] .= "<a class='catLink' href='?section=directory".$levelLink."&amp;cid=".$catKey."''>".htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET)."</a>&nbsp;(".$count.")<br />";
				array_push($this->arrRowsIndex, substr(htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET), 0, 1)."<a class='catLink' href='?section=directory".$levelLink."&amp;cid=".$catKey."''>".htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET)."</a>&nbsp;(".$count.")<br />");


				//get subcategories
				if($this->categories['parentid'][$catKey] == 0){
					$objResult = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."module_directory_categories WHERE status = '1' AND parentid =".contrexx_addslashes($catKey)." ORDER BY displayorder LIMIT ".contrexx_addslashes($this->subLimit)."");
					if($objResult !== false){
						while(!$objResult->EOF){
							$this->arrRows[$i] .= "<a class='subcatLink' href='?section=directory".$levelLink."&amp;cid=".$objResult->fields['id']."''>".htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)."</a>, ";
							$objResult->MoveNext();
						}
					}

					if($objResult->RecordCount() != 0){
						$this->arrRows[$i] .= "<br />";
					}
				}

				if($i%$this->rows==0){
					$i=1;
				}else{
					$i++;
				}
			}
		}else{
			$this->_objTemplate->hideBlock('showCategories');
		}

		return $arrCategories;
	}



	/**
    * get search
    *
    * get search
    *
    * @access   public
    * @param    string  $id
    */
	function getSearch()
	{
		global $objDatabase, $_ARRAYLANG, $template;

		$arrDropdown['language'] 	= $this->getLanguages('');
		$arrDropdown['platform'] 	= $this->getPlatforms('');
		$arrDropdown['canton'] 		= $this->getCantons('');



		$javascript	= 	'<script language="JavaScript">
						<!--
						function toggle(target){
						    obj = document.getElementById(target);
						    obj.style.display = (obj.style.display==\'none\') ? \'inline\' : \'none\';
						    if (obj.style.display==\'none\' && target == \'hiddenSearch\'){
						         document.getElementById(\'searchCheck\').value = \'norm\';
						    }else if(obj.style.display==\'inline\' && target == \'hiddenSearch\'){
						         document.getElementById(\'searchCheck\').value = \'exp\';
						    }
						}
						-->
						</script>';

		//get levels
		if ($this->settings['levels']['value'] == 1) {
			$options 	= $this->getSearchLevels('');
			$name		= "Ebene";
			$field 		= '<select name="lid" style="width:194px;"><option value="">Alle Ebenen</option>'.$options.'</select>';

			// set variables
			$expSearch	.= '<tr>
	                            <td width="100" height="20">'.$name.'</td>
	                            <td>'.$field.'</td>
	                        </tr>';
		}

		//get categories
		$options 	= $this->getSearchCategories('');
		$name		= $_ARRAYLANG['TXT_DIR_F_CATEGORIE'];
		$field	 	= '<select name="cid" style="width:194px;"><option value="">Alle Kategorien</option>'.$options.'</select>';

		// set variables
		$expSearch	.= '<tr>
                            <td width="100" height="20">'.$name.'</td>
                            <td>'.$field.'</td>
                        </tr>';

		//get exp search fields
		$objResult = $objDatabase->Execute("SELECT id, name, title, typ FROM ".DBPREFIX."module_directory_inputfields WHERE exp_search='1' AND is_search='1' ORDER BY sort");
		if($objResult !== false){
			while(!$objResult->EOF){
				$name = $_ARRAYLANG[$objResult->fields['title']];
				if($objResult->fields['typ'] == 1){
					$field 		= '<input maxlength="100" size="30" name="'.$objResult->fields['name'].'" />';
				}else{
					$field 		= '<select name="'.$objResult->fields['name'].'" style="width:194px;">'.$arrDropdown[$objResult->fields['name']].'</select>';
				}

				// set variables
				$expSearch	.= '<tr>
		                            <td width="100" height="20">'.$name.'</td>
		                            <td>'.$field.'</td>
		                        </tr>';

				$objResult->MoveNext();
			}
		}


		$html 		=	'<table width="100%" cellspacing="5" cellpadding="0" border="0" id="directory">
					    <tbody>
					        <tr>
					            <td class="description">
					                <form action="index.php?" method="get" name="directorySearch" id="directorySearch">
					                    <input name="term" value="{TXT_DIRECTORY_SEARCHTERM}" size="25" maxlength="100" />
					                    <input id="searchCheck" type="hidden" name="check" value="norm" size="10" />
					                    <input type="hidden" name="section" value="directory" size="10" />
					                    <input type="hidden" name="cmd" value="search" size="10" />
					                    <input type="submit" value="'.$_ARRAYLANG['TXT_DIR_F_SEARCH'].'" name="search" /> » <a onclick="javascript:toggle(\'hiddenSearch\')" href="javascript:{}">'.$_ARRAYLANG['TXT_DIRECTORY_EXP_SEARCH'].'</a><br />
					                    <div style="display: none;" id="hiddenSearch">
					                    <br />
					                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
					                    '.$expSearch.'
					                    </table>
					                    </div>
					                </form>
					            </td>
					        </tr>
					    </tbody>
					</table>';

		// set variables
		$this->_objTemplate->setVariable(array(
			'DIRECTORY_SEARCH' 			=>  $javascript.$html,
		));
	}
}
