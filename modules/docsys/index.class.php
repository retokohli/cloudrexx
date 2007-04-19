<?PHP
/**
 * DocSys
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_docsys
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/docsys/lib/Library.class.php';

/**
 * DocSys
 *
 * This module will get all the docSys pages
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_docsys
 */
class docSys extends docSysLibrary
{
    var $docSysTitle;
    var $langId;
    var $dateFormat = 'd.m.Y';
    var $dateLongFormat = 'H:i:s d.m.Y';
    var $_objTpl;


    function docSys($pageContent)
    {
    	$this->__construct($pageContent);
    }


	// CONSTRUCTOR
    function __construct($pageContent)
    {
	    global $_LANGID;
	    $this->pageContent = $pageContent;
	    $this->_objTpl = &new HTML_Template_Sigma('.');
	    $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
	    $this->langId = $_LANGID;
	}



	// GET PAGE
    function getdocSysPage()
    {
    	if (!isset($_REQUEST['cmd'])) {
    		$_REQUEST['cmd'] = '';
    	}

    	switch( $_REQUEST['cmd']) {
    		case 'details':
		    	return $this->getDetails();
		    	break;
		    default:
		        return $this->getTitles();
		        break;
    	}
    }



	/**
	* Gets the news details
	*
	* @global	 array     $_CONFIG
	* @global	 array     $_ARRAYLANG
	* @global	 object    $objDatabase
	* @return    string    parsed content
	*/
	function getDetails()
	{
		global $_CONFIG, $objDatabase, $_ARRAYLANG;

	    $this->_objTpl->setTemplate($this->pageContent);

	    $id = intval($_GET['id']);

		if ($id > 0) {
			$query = "SELECT id,
							   source,
							   changelog,
							   url1,
							   url2,
							   text,
							   date,
							   changelog,
							   title,
			                   author
			              FROM ".DBPREFIX."module_docsys
			             WHERE status = 1
			               AND id = $id
			               AND lang=".$this->langId."
			               AND (startdate<=CURDATE() OR startdate='0000-00-00')
			               AND (enddate>=CURDATE() OR enddate='0000-00-00')";
			$objResult = $objDatabase->SelectLimit($query, 1);

			while(!$objResult->EOF) {
				$lastUpdate	= stripslashes($objResult->fields['changelog']);
				$date = stripslashes($objResult->fields['date']);
				$source	= stripslashes($objResult->fields['source']);
				$url1 = stripslashes($objResult->fields['url1']);
				$url2 = stripslashes($objResult->fields['url2']);
				$docUrl = "";
				$docSource = "";
				$docLastUpdate = "";

				if (!empty($url1)){
					 $docUrl = $_ARRAYLANG['TXT_IMPORTANT_HYPERLINKS'].'<br /><a target="new" href="'.$url1.'" title="'.$url1.'">'.$url1.'</a><br />';
				}
				if (!empty($url2)){
					 $docUrl .= '<a target="new" href="'.$url2.'">'.$url2.'</a><br />';
				}
				if (!empty($source)){
					 $docSource = $_ARRAYLANG['TXT_SOURCE'].'<br /><a target="new" href="'.$source.'" title="'.$source.'">'.$source.'</a><br />';
				}
				if (!empty($lastUpdate) AND $lastUpdate!=$date ){
					 $docLastUpdate = $_ARRAYLANG['TXT_LAST_UPDATE']."<br />".date(ASCMS_DATE_FORMAT,$lastUpdate);
				}

				$title = $objResult->fields['title'];
				$this->_objTpl->setVariable(array(
					'DOCSYS_DATE' => date(ASCMS_DATE_FORMAT,$date),
					'DOCSYS_TITLE'=> stripslashes($title),
					'DOCSYS_AUTHOR'	=> stripslashes($objResult->fields['author']),
					'DOCSYS_TEXT' => stripslashes($objResult->fields['text']),
					'DOCSYS_LASTUPDATE' => $docLastUpdate,
					'DOCSYS_SOURCE' => $docSource,
					'DOCSYS_URL'=> $docUrl));
				$objResult->MoveNext();
			}
		} else {
			header("Location: ?section=docsys");
			exit;
		}

		$this->docSysTitle = strip_tags(stripslashes($title));
		return $this->_objTpl->get();
	}





	/**
	* Gets the global page title
	*
	* @param     string	   (optional)$pageTitle
	*/
	function getPageTitle($pageTitle="")
	{
	    if(empty($this->docSysTitle)){
	        $this->docSysTitle = strip_tags(stripslashes($pageTitle));
	    }
	}





	/**
	* Gets the list with the headlines
	*
	* @global	 object    $objDatabase
	* @param     integer   $pos
	* @param     string	   $page_content
	* @return    string    parsed content
	*/

	function getTitles()
	{
		global $_CONFIG, $objDatabase, $_ARRAYLANG;

		$selectedId = "";
		$docFilter = "";
		$paging = "";
		$pos = intval($_GET['pos']);
		$i = 1;
		$class  = 'row1';

		$this->_objTpl->setTemplate($this->pageContent);

		if(!empty($_REQUEST['category'])){
			$selectedId= intval($_REQUEST['category']);
			$query = " SELECT `sort_style` FROM `".DBPREFIX."module_docsys_categories`
						WHERE `catid` = ".$selectedId;
			$objRS = $objDatabase->SelectLimit($query, 1);
			if($objRS !== false){
				$sortType = $objRS->fields['sort_style'];
			}else{
				die('database error. '.$objDatabase->ErrorMsg());
			}
			$docFilter =" n.catid='$selectedId' AND ";
		}
		$this->_objTpl->setVariable("DOCSYS_NO_CATEGORY", $_ARRAYLANG['TXT_CATEGORY']);
	    $this->_objTpl->setVariable("DOCSYS_CAT_MENU", $this->getCategoryMenu($this->langId, $selectedId));
	    $this->_objTpl->setVariable("TXT_PERFORM", $_ARRAYLANG['TXT_PERFORM']);

	    $query = "SELECT n.date AS date,
		                 n.id AS docid,
		                 n.title AS title,
		                 n.author AS author,
		                 nc.name AS name
		            FROM ".DBPREFIX."module_docsys AS n,
		                 ".DBPREFIX."module_docsys_categories AS nc
		           WHERE status = 1
		             AND n.lang=".$this->langId."
		             AND $docFilter n.catid=nc.catid
	                 AND (startdate<=CURDATE() OR startdate='0000-00-00')
	                 AND (enddate>=CURDATE() OR enddate='0000-00-00') ";

	   if(!empty($docFilter)){
			switch($sortType){
				case 'alpha':
					$query .= " ORDER BY `title`";
				break;

				case 'date':
					$query .= " ORDER BY `date` DESC";
				break;

				case 'date_alpha':
					$query .= " ORDER BY DATE_FORMAT( FROM_UNIXTIME( `date` ) , '%Y%j' ) DESC, `title`";
				break;

				default:
					$query .= " ORDER BY n.id DESC";
			}
		}else{
			$query .= " ORDER BY n.id DESC";
		}




		/***start paging ****/

		$objResult = $objDatabase->Execute($query);
		$count = $objResult->RecordCount();
		if ($count > intval($_CONFIG['corePagingLimit'])) {
		    $paging = getPaging($count, $pos, "&section=docsys", $_ARRAYLANG['TXT_DOCUMENTS'], true);
		}
		$this->_objTpl->setVariable("DOCSYS_PAGING", $paging);
		$objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos) ;
		/*** end paging ***/

		if($count>=1){
			while (!$objResult->EOF) {
				($i % 2) ? $class  = 'row1' : $class  = 'row2';
				$this->_objTpl->setVariable(array(
					'DOCSYS_STYLE'      => $class,
					'DOCSYS_LONG_DATE'  => date($this->dateLongFormat,$objResult->fields['date']),
					'DOCSYS_DATE'       => date($this->dateFormat,$objResult->fields['date']),
					'DOCSYS_LINK'	   => "<a href=\"?section=docsys&amp;cmd=details&amp;id=".$objResult->fields['docid']."\" title=\"".stripslashes($objResult->fields['title'])."\">".stripslashes($objResult->fields['title'])."</a>",
					'DOCSYS_CATEGORY'   => stripslashes($objResult->fields['name']),
					'DOCSYS_AUTHOR'	   => stripslashes($objResult->fields['author']),
				));

				$this->_objTpl->parse("row");
				$i++;
				$objResult->MoveNext();
			}
		}else{
			$this->_objTpl->setVariable('DOCSYS_STYLE', $class);
			$this->_objTpl->setVariable('DOCSYS_DATE', '');
			$this->_objTpl->setVariable('DOCSYS_LINK', '');
			$this->_objTpl->setVariable('DOCSYS_CATEGORY', $_ARRAYLANG['TXT_NO_DOCUMENTS_FOUND']);
			$this->_objTpl->parse("row");
		}
	    return $this->_objTpl->get();
	}
}
?>
