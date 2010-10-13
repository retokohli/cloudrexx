<?php

/**
 * News library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 * @todo        Edit PHP DocBlocks!
 */

/**
 * News library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_news
 */
class newsLibrary
{

    /**
    * Gets the categorie option menu string
    *
    * @global    ADONewConnection
    * @param     string     $lang
    * @param     string     $selectedOption
    * @return    string     $modulesMenu
    */
    function getSettings()
    {
    	global $objDatabase;
        $query = "SELECT name, value FROM ".DBPREFIX."module_news_settings";
        $objResult = $objDatabase->Execute($query);
	    while (!$objResult->EOF) {
		    $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
		    $objResult->MoveNext();
	    }
    }


    function getCategoryMenu($langId, $selectedOption=""){
	    global $objDatabase;

	    $strMenu = "";
        $query = "SELECT catid, name FROM ".DBPREFIX."module_news_categories
                        WHERE catid<>0 AND lang=".$langId." ORDER BY catid";
        $objResult = $objDatabase->Execute($query);
	    while (!$objResult->EOF) {
		    $selected = ($selectedOption==$objResult->fields['catid']) ? "selected" : "";
		    $strMenu .="<option value=\"".$objResult->fields['catid']."\" $selected>".stripslashes($objResult->fields['name'])."</option>\n";
		    $objResult->MoveNext();
	    }
	    return $strMenu;
    }

    /**
    * Gets only the body content and deleted all the other tags
    *
    * @param     string     $fullContent      HTML-Content with more than BODY
    * @return    string     $content          HTML-Content between BODY-Tag
    */
    function filterBodyTag($fullContent){
	    $res=false;
	    $posBody=0;
	    $posStartBodyContent=0;
	    $res=preg_match_all("/<body[^>]*>/i", $fullContent, $arrayMatches);
	    if($res==true)
	    {
            $bodyStartTag = $arrayMatches[0][0];
            // Position des Start-Tags holen
            $posBody = strpos($fullContent, $bodyStartTag, 0);
            // Beginn des Contents ohne Body-Tag berechnen
            $posStartBodyContent = $posBody + strlen($bodyStartTag);
	    }
	    $posEndTag=strlen($fullContent);
	    $res=preg_match_all("/<\/body>/i",$fullContent, $arrayMatches);
	    if($res==true)
	    {
            $bodyEndTag=$arrayMatches[0][0];
            // Position des End-Tags holen
            $posEndTag = strpos($fullContent, $bodyEndTag, 0);
            // Content innerhalb der Body-Tags auslesen
	     }
	     $content = substr($fullContent, $posStartBodyContent, $posEndTag  - $posStartBodyContent);
         return $content;
    }

    function getCategories()
    {
    	global $objDatabase, $objInit;

    	$arrCatgories = array();

    	$objResult = $objDatabase->Execute("SELECT catid,
			name,
			lang
			FROM ".DBPREFIX."module_news_categories
			WHERE lang=".$objInit->userFrontendLangId."
			ORDER BY catid asc");

    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
    			$arrCatgories[$objResult->fields['catid']] = array(
    				'id'	=> $objResult->fields['catid'],
    				'name'	=> $objResult->fields['name'],
    				'lang'	=> $objResult->fields['lang']
    			);
    			$objResult->MoveNext();
    		}
    	}

    	return $arrCatgories;
    }
}
?>
