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
    
    /**
     * Creates a "posted by $strUsername on $strDate" string.
     *
     * @global  array
     * @param   string      $strUsername
     * @param   string		$strDate
     * @return  string
     */
    function getPostedByString($strUsername, $strDate)
	{
        global $_ARRAYLANG;

        $strPostedString = str_replace('[USER]',$strUsername, $_ARRAYLANG['TXT_NEWS_LIB_POSTED_BY']);
        $strPostedString = str_replace('[DATE]',$strDate, $strPostedString);

        return $strPostedString;
    }
    
    /**
     * Get language data (title, text, teaser_text) from database
     * 
     * @global ADONewConnection
     * @param  Integer $newsId
     * @return Array
     */
    function getLangData($newsId) {
        global $objDatabase;

    	$arrLangData = array();

    	$objResult = $objDatabase->Execute("SELECT lang_id,
			title,
			text,
			teaser_text
			FROM ".DBPREFIX."module_news_locale
			WHERE news_id = " . intval($newsId));

    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
    			$arrLangData[$objResult->fields['lang_id']] = array(
    				'title'	        => $objResult->fields['title'],
    				'text'	        => $objResult->fields['text'],
    				'teaser_text'	=> $objResult->fields['teaser_text']
    			);   			
    			$objResult->MoveNext();
    		}
    	}

    	return $arrLangData;
    }
    
    /**
     * Saving locales after edit news
     *
     * @global ADONewConnection
     * @param Integer $newsId
     * @param Array $newLangData
     * @return Boolean
     */
    protected function storeLocales($newsId, $newLangData) {
        global $objDatabase;

    	$oldLangData = $this->getLangData($newsId);
    	if(count($oldLangData) == 0) {
    	    return false;
    	}
    	
    	$status = true;
    	
    	$arrNewLocales = array_diff(array_keys($newLangData['title']), array_keys($oldLangData));
        $arrRemovedLocales = array_diff(array_keys($oldLangData), array_keys($newLangData['title']));
        $arrUpdatedLocales = array_intersect(array_keys($newLangData['title']), array_keys($oldLangData));

        foreach ($arrNewLocales as $langId) {
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_locale` (`lang_id`, `news_id`, `title`, `text`, `teaser_text`) 
                    VALUES ("   . $langId . ", " 
                                . $newsId . ", '"
                                . contrexx_addslashes(htmlentities($newLangData['title'][$langId], ENT_QUOTES, CONTREXX_CHARSET)) . "', '" 
                                . $this->filterBodyTag(contrexx_addslashes($newLangData['text'][$langId])) . "', '"
                                . contrexx_addslashes($newLangData['teaser_text'][$langId]) . "')") === false) {
                $status = false;
            }
        }

        foreach ($arrRemovedLocales as $langId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_locale` WHERE `news_id` = " . $newsId . " AND `lang_id` = " . $langId) === false) {
                $status = false;
            }
        }

        foreach ($arrUpdatedLocales as $langId) {
            if ($newLangData['title'][$langId] != $oldLangData[$langId]['title'] 
            || $newLangData['text'][$langId] != $oldLangData[$langId]['text'] 
            || $newLangData['teaser_text'][$langId] != $oldLangData[$langId]['teaser_text'] ) {
                if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_locale` SET 
                        `title` = '" . contrexx_addslashes(htmlentities($newLangData['title'][$langId], ENT_QUOTES, CONTREXX_CHARSET)) . "', 
                        `text` = '" . $this->filterBodyTag(contrexx_addslashes($newLangData['text'][$langId])) . "',
                        `teaser_text` = '" . contrexx_addslashes($newLangData['teaser_text'][$langId]) . "'  
                        WHERE `news_id` = " . $newsId . " AND `lang_id` = " . $langId) === false) {
                    $status = false;
                }
            }
        }
    	
    	return $status;
    }
    
    /**
     * Insert new locales after create news from backend
     *
     * @global ADONewConnection
     * @param Integer $newsId
     * @param Array $newLangData
     * @return Boolean
     */
    function insertLocales($newsId, $newLangData) {
        global $objDatabase;
        
        if(!isset($newsId)) {
            return false;
        }
        
        $status = true;
        
        $arrLanguages = FWLanguage::getLanguageArray();
        foreach ($arrLanguages as $langId => $arrLanguage) {
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_locale` (`lang_id`, `news_id`, `title`, `text`, `teaser_text`) 
                    VALUES ("   . $langId . ", " 
                                . $newsId . ", '"
                                . contrexx_addslashes(htmlentities((isset($newLangData['title'][$langId]) ? $newLangData['title'][$langId] : ""), ENT_QUOTES, CONTREXX_CHARSET)) . "', '" 
                                . $this->filterBodyTag(contrexx_addslashes((isset($newLangData['text'][$langId]) ? $newLangData['text'][$langId] : ""))) . "', '"
                                . contrexx_addslashes((isset($newLangData['teaser_text'][$langId]) ? $newLangData['teaser_text'][$langId] : "")) . "')") === false) {
                $status = false;
            }
        }
        
        return $status;
    }
    
    /**
     * Insert new locales after submit news from frontend
     * One copy for all languages
     *
     * @global ADONewConnection
     * @param Integer   $newsId
     * @param String    $title
     * @param String    $text
     * @param String    $teaser_text
     * @return Boolean
     */
    function submitLocales($newsId, $title, $text, $teaser_text) {
        global $objDatabase;
        
        if(!isset($newsId)) {
            return false;
        }
        
        $status = true;
        
        $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."languages");

    	if ($objResult !== false) {
    		while (!$objResult->EOF) {
    		    if($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news_locale (`lang_id`, `news_id`, `title`, `text`, `teaser_text`) 
        		    VALUES ("
            		    . intval($objResult->fields['id']) . ", "
            		    . intval($newsId) . ", '"
            		    . contrexx_addslashes($title) . "', '"
            		    . contrexx_addslashes($text) . "', '"
            		    . contrexx_addslashes($teaser_text) . "')")){
    		        $status = false;
    		    }
    			$objResult->MoveNext();
    		}
    	}
    	
    	return $status;
    }
}
?>
