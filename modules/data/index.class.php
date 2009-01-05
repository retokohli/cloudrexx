<?php

/**
 * Data
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version	    $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_data
 */

$_ARRAYLANG['TXT_DATA_DOWNLOAD_ATTACHMENT'] = "Anhang herunterladen";


/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/data/lib/dataLib.class.php';

/**
 * DataAdmin
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version	    $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_data
 */
class Data extends DataLibrary  {

	public $_objTpl;
	public $_strStatusMessage = '';
	public $_strErrorMessage = '';
	public $curCmd;


	/**
	* Constructor	-> Call parent-construct, set language id and create local template-object
    *
    * @global	integer
    */
	function __construct($strPageContent)
	{
		$this->_intCurrentUserId = (isset($_SESSION['auth']['userid'])) ? intval($_SESSION['auth']['userid']) : 0;
	    $this->_objTpl = new HTML_Template_Sigma('.');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->_objTpl->setTemplate($strPageContent);
	}


	/**
	* Reads $_GET['cmd'] and selects (depending on the value) an action
	*
    */
	function getPage()
	{
	    if (isset($_GET['act'])) {
	        if ($_GET['act'] == "thickbox") {
	            $this->thickbox();
	        }
	    }

		if(!isset($_GET['cmd'])) {
    		$_GET['cmd'] = '';
    	} else {
    	    $this->curCmd = $_GET['cmd'];
    	}

    	if (isset($_GET['cid'])) {
    	    $this->showCategory($_GET['cid']);
    	} elseif (isset($_GET['id'])) {
    	    $this->showDetails($_GET['id']);
    	} else {
    	   $this->showCategoryOverview();
    	}

    	return $this->_objTpl->get();
	}

	/**
	 * Show the list of categories
	 *
	 */
	function showCategoryOverview()
	{
	    $arrCategories = $this->createCategoryArray();

	    $catTree = $this->buildCatTree($arrCategories);

	    $catList = $this->parseCategoryView($catTree, $arrCategories);
	    $this->_objTpl->setVariable("CATEGORIES", $catList);

	    $this->_objTpl->parse("showDataCategories");
	}

	/**
	 * Generate the category tree recursively
	 *
	 * @param array $catTree The categories sorted as tree
	 * @param array $arrCategories
	 * @param int $level
	 * @return string
	 */
	function parseCategoryView($catTree, $arrCategories, $level=0)
	{
	    $parsed = false;
	    $catList = str_repeat("\t", $level)."<ul>\n";
	    foreach ($catTree as $key => $value) {
	        if ($arrCategories[$key]['active']) {
	            $catName = $arrCategories[$key][FRONTEND_LANG_ID]['name'];
	            $indent = $level * 10;

    	        $catList .= str_repeat("\t", $level+1)."<li style=\"padding-left: ".$indent."px\">\n";
    	        $catList .= str_repeat("\t", $level+1)."<a href=\"index.php?section=data&amp;cmd=".$this->curCmd."&amp;cid=".$key."\">".$catName."</a>\n";
    	        if (count($value) > 0) {
    	            $catList .= $this->parseCategoryView($value, $arrCategories, $level+1);
    	        }
    	        $catList .= str_repeat("\t", $level+1)."</li>\n";
    	        $parsed = true;
	        }
	    }
	    $catList .= str_repeat("\t", $level)."</ul>\n";
	    if ($parsed) {
	       return $catList;
	    } else {
	        return "";
	    }
	}

	/**
	 * Show one category
	 *
	 * @param unknown_type $id
	 */
	function showCategory($id)
	{
	    global $_ARRAYLANG;

	    $arrEntries = $this->createEntryArray(FRONTEND_LANG_ID);
//	    $settings = $this->createSettingsArray();

	    foreach ($arrEntries as $key => $value) {
	        if ($value['active']) {
	            // check date
	            if ($value['release_time'] != 0) {
	               if ($value['release_time'] > time()) {
	                   // too old
	                   continue;
	               }

	               // if it is not endless (0), check if 'now' is past the given date
	               if ($value['release_time_end'] !=0 && time() > $value['release_time_end']) {
	                   continue;
	               }
	            }
    	        if ($this->categoryMatches($id, $value['categories'][FRONTEND_LANG_ID])) {
    	            $this->_objTpl->setVariable(array(
    	               "ENTRY_TITLE"       => $value['translation'][FRONTEND_LANG_ID]['subject'],
    	               "ENTRY_CONTENT"     => $this->getIntroductionText($value['translation'][FRONTEND_LANG_ID]['content']),
    	               "ENTRY_ID"          => $key,
    	               "TXT_MORE"          => $_ARRAYLANG['TXT_DATA_MORE'],
    	               "CMD"               => $this->curCmd,
    	            ));
    	            $this->_objTpl->parse("entry");
    	        }
	        }
	    }
	    $this->_objTpl->parse("showDataCategory");
	}

	/**
	 * Shows all existing entries of the data in descending order.
	 *
	 * @global 	array
	 */
	function showEntries() {
		global $_ARRAYLANG;

		$arrEntries = $this->createEntryArray(FRONTEND_LANG_ID);

		foreach ($arrEntries as $intEntryId => $arrEntryValues) {

			$this->_objTpl->setVariable(array(
				'TXT_DATA_CATEGORIES'	=>	$_ARRAYLANG['TXT_DATA_FRONTEND_SEARCH_RESULTS_CATEGORIES'],
				'TXT_DATA_TAGS'			=>	$_ARRAYLANG['TXT_DATA_FRONTEND_SEARCH_RESULTS_KEYWORDS'],
				'TXT_DATA_VOTING'		=>	$_ARRAYLANG['TXT_DATA_FRONTEND_OVERVIEW_VOTING'],
				'TXT_DATA_VOTING_DO'	=>	$_ARRAYLANG['TXT_DATA_FRONTEND_OVERVIEW_VOTING_DO'],
				'TXT_DATA_COMMENTS'		=>	$_ARRAYLANG['TXT_DATA_FRONTEND_OVERVIEW_COMMENTS'],
			));

			$this->_objTpl->setVariable(array(
				'DATA_ENTRIES_ID'			=>	$intEntryId,
				'DATA_ENTRIES_TITLE'		=>	$arrEntryValues['subject'],
				//'DATA_ENTRIES_POSTED'		=>	$this->getPostedByString($arrEntryValues['user_name'],$arrEntryValues['time_created']),
				'DATA_ENTRIES_CONTENT'		=>	$arrEntryValues['translation'][FRONTEND_LANG_ID]['content'],
				'DATA_ENTRIES_INTRODUCTION'	=>	$this->getIntroductionText($arrEntryValues['translation'][FRONTEND_LANG_ID]['content']),
				'DATA_ENTRIES_IMAGE'		=>	($arrEntryValues['translation'][FRONTEND_LANG_ID]['image'] != '') ? '<img src="'.$arrEntryValues['translation'][FRONTEND_LANG_ID]['image'].'" title="'.$arrEntryValues['subject'].'" alt="'.$arrEntryValues['subject'].'" />' : '',
				'DATA_ENTRIES_VOTING'		=>	'&#216;&nbsp;'.$arrEntryValues['votes_avg'],
//				'DATA_ENTRIES_VOTING_STARS'	=>	$this->getRatingBar($intEntryId),
				'DATA_ENTRIES_COMMENTS'		=>	$arrEntryValues['comments_active'].' '.$_ARRAYLANG['TXT_DATA_FRONTEND_OVERVIEW_COMMENTS'].'&nbsp;',
				'DATA_ENTRIES_CATEGORIES'	=>	$this->getCategoryString($arrEntryValues['categories'][FRONTEND_LANG_ID], true),
				'DATA_ENTRIES_TAGS'			=>	$this->getLinkedTags($arrEntryValues['translation'][FRONTEND_LANG_ID]['tags']),
				'DATA_ENTRIES_SPACER'		=>	($this->_arrSettings['data_voting_activated'] && $this->_arrSettings['data_comments_activated']) ? '&nbsp;&nbsp;|&nbsp;&nbsp;' : ''
			));

			if (!$this->_arrSettings['data_voting_activated']) {
				$this->_objTpl->hideBlock('showVotingPart');
			}

			if (!$this->_arrSettings['data_comments_activated']) {
				$this->_objTpl->hideBlock('showCommentPart');
			}

			$this->_objTpl->parse('showDataEntries');
		}
	}

	/**
	 * Show a single entry
	 *
	 * @param unknown_type $intMessageId
	 */
	function showDetails($intMessageId)
	{
	    global $_ARRAYLANG;

	    $arrEntries = $this->createEntryArray();
	    $entry = $arrEntries[$intMessageId];

	    if ($entry['translation'][FRONTEND_LANG_ID]['image']) {
                $image = "<img src=\"".$entry['translation'][FRONTEND_LANG_ID]['image']."\" alt=\"\" style=\"float: left; margin-right: 5px;\"/>";
        } else {
            $image = "";
        }

        if ($entry['translation'][FRONTEND_LANG_ID]['attachment']) {
            $this->_objTpl->setVariable(array(
                "HREF"          => $entry['translation'][FRONTEND_LANG_ID]['attachment'],
                "TXT_DOWNLOAD"  => $_ARRAYLANG['TXT_DATA_DOWNLOAD_ATTACHMENT']
            ));
            $this->_objTpl->parse("attachment");
        }

	    $this->_objTpl->setVariable(array(
	       "ENTRY_SUBJECT"         => $entry['translation'][FRONTEND_LANG_ID]['subject'],
	       "ENTRY_CONTENT"         => $entry['translation'][FRONTEND_LANG_ID]['content'],
	       "IMAGE"                 => $image
	    ));

	    $this->_objTpl->parse("showDataDetails");
	}

	/**
	 * Show the thickbox
	 *
	 */
	function thickbox()
	{
	    global $objDatabase, $_ARRAYLANG, $objInit;

	   // var_dump($themesPages['buildin_style']);

	    $id = intval($_GET['id']);
        $lang = intval($_GET['lang']);

        $entries = $this->createEntryArray();
        $entry  = $entries[$id];
        $settings = $this->createSettingsArray();

        $title = $entry['translation'][$lang]['subject'];
        $content = $entry['translation'][$lang]['content'];
        $picture = (!empty($entry['translation'][$lang]['image'])) ? $entry['translation'][$lang]['image'] : "none";

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_THEMES_PATH);
        $this->_objTpl->setCurrentBlock("thickbox");

        $objResult = $objDatabase->SelectLimit("
            SELECT foldername
              FROM ".DBPREFIX."skins
             WHERE id=".$objInit->getThemeId(), 1);
        if ($objResult !== false) {
            $themesPath = $objResult->fields['foldername'];
        }

        $template = preg_replace('/\[\[([A-Z_]+)\]\]/', '{$1}', $settings['data_template_thickbox']);
        $this->_objTpl->setTemplate($template);

        if ($entry['translation'][$lang]['attachment']) {
            $this->_objTpl->setVariable(array(
                "HREF"          => $entry['translation'][$lang]['attachment'],
                "TXT_DOWNLOAD"  => $_ARRAYLANG['TXT_DATA_DOWNLOAD_ATTACHMENT']
            ));
            $this->_objTpl->parse("attachment");
        }

        $this->_objTpl->setVariable(array(
            "TITLE"         => $title,
            "CONTENT"       => $content,
            "PICTURE"       => $picture,
            "THEMES_PATH"   => $themesPath
        ));
        if ($picture != "none") {
            $this->_objTpl->parse("image");
        } else {
            $this->_objTpl->hideBlock("image");
        }
        $this->_objTpl->parse("thickbox");
        $this->_objTpl->show();
	    die();
	}
}

?>
