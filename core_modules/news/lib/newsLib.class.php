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

        // get multilanguage settings (now only 'news_feed_title', 'news_feed_description')
        $query = "SELECT lang_id, name, value FROM ".DBPREFIX."module_news_settings_locale";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            if (!is_array($this->arrSettings[$objResult->fields['name']])) {
                $this->arrSettings[$objResult->fields['name']] = array();
            }
            $this->arrSettings[$objResult->fields['name']][$objResult->fields['lang_id']] = $objResult->fields['value'];
            $objResult->MoveNext();
        }
    }


    function getCategoryMenu($langId, $selectedOption="")
    {
        global $objDatabase;

        $strMenu = "";
        $query = "SELECT lang_id, category_id, name FROM ".DBPREFIX."module_news_categories_locale
                        WHERE category_id <> 0 ORDER BY category_id";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $selected = ($selectedOption == $objResult->fields['category_id']
                && $langId == $objResult->fields['lang_id']) ? "selected" : "";
            $hidden = ($langId == $objResult->fields['lang_id']) ? "" : " style='display:none;'";
            $strMenu .="<option class=\"lang" . $objResult->fields['lang_id'] . "\" value=\"".$objResult->fields['category_id']."\" $selected $hidden>".stripslashes($objResult->fields['name'])."</option>\n";
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
    function filterBodyTag($fullContent)
    {
        $res=false;
        $posBody=0;
        $posStartBodyContent=0;
        $res=preg_match_all("/<body[^>]*>/i", $fullContent, $arrayMatches);
        if ($res==true) {
            $bodyStartTag = $arrayMatches[0][0];
            // Position des Start-Tags holen
            $posBody = strpos($fullContent, $bodyStartTag, 0);
            // Beginn des Contents ohne Body-Tag berechnen
            $posStartBodyContent = $posBody + strlen($bodyStartTag);
        }
        $posEndTag=strlen($fullContent);
        $res=preg_match_all("/<\/body>/i",$fullContent, $arrayMatches);
        if ($res) {
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

        $objResult = $objDatabase->Execute("SELECT category_id,
            name,
            lang_id
            FROM ".DBPREFIX."module_news_categories_locale
            WHERE lang_id=".$objInit->userFrontendLangId."
            ORDER BY category_id asc");

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrCatgories[$objResult->fields['category_id']] = array(
                    'id'    => $objResult->fields['category_id'],
                    'name'  => $objResult->fields['name'],
                    'lang'  => $objResult->fields['lang_id']
                );
                $objResult->MoveNext();
            }
        }
        return $arrCatgories;
    }


    /**
     * Creates a "posted by $strUsername on $strDate" string.
     * @global  array
     * @param   string      $strUsername
     * @param   string      $strDate
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
     * @global ADONewConnection
     * @param  Integer $id
     * @return Array
     */
    function getLangData($id) 
    {
        global $objDatabase;

        if (empty($id)) {
            return false;
        }
        $arrLangData = array();
        $objResult = $objDatabase->Execute("SELECT lang_id,
            title,
            text,
            teaser_text
            FROM ".DBPREFIX."module_news_locale
            WHERE news_id = " . intval($id));

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrLangData[$objResult->fields['lang_id']] = array(
                    'title'       => $objResult->fields['title'],
                    'text'        => $objResult->fields['text'],
                    'teaser_text' => $objResult->fields['teaser_text']
                );
                $objResult->MoveNext();
            }
        }
        return $arrLangData;
    }


    /**
     * Get categories language data
     * @global ADONewConnection
     * @return Array
     */
    function getCategoriesLangData() 
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("SELECT lang_id,
            category_id,
            name
            FROM ".DBPREFIX."module_news_categories_locale");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if (!isset($arrLangData[$objResult->fields['category_id']])) {
                    $arrLangData[$objResult->fields['category_id']] = array();
                }
                $arrLangData[$objResult->fields['category_id']][$objResult->fields['lang_id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }
        }
        return $arrLangData;
    }


    /**
     * Saving locales after edit news
     * @global ADONewConnection
     * @param Integer $newsId
     * @param Array $newLangData
     * @return Boolean
     */
    protected function storeLocales($newsId, $newLangData) 
    {
        global $objDatabase;

        $oldLangData = $this->getLangData($newsId);
        if (count($oldLangData) == 0 || !isset($newsId)) {
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
     * Saving categories locales
     * @global ADONewConnection
     * @param Array $newLangData
     * @return Boolean
     */
    protected function storeCategoriesLocales($newLangData) 
    {
        global $objDatabase;

        $oldLangData = $this->getCategoriesLangData();
        if (count($oldLangData) == 0) {
            return false;
        }
        $status = true;
        $arrNewLocales = array_diff(array_keys($newLangData[key($newLangData)]), array_keys($oldLangData[key($oldLangData)]));
        $arrRemovedLocales = array_diff(array_keys($oldLangData[key($oldLangData)]), array_keys($newLangData[key($newLangData)]));
        $arrUpdatedLocales = array_intersect(array_keys($newLangData[key($newLangData)]), array_keys($oldLangData[key($oldLangData)]));
        foreach (array_keys($newLangData) as $catId) {
            foreach ($arrNewLocales as $langId) {
                if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_categories_locale` (`lang_id`, `category_id`, `name`)
                        VALUES ("   . $langId . ", "
                                    . $catId . ", '"
                                    . contrexx_addslashes(htmlentities($newLangData[$catId][$langId], ENT_QUOTES, CONTREXX_CHARSET)) . "')")
                                    === false) {
                    $status = false;
                }
            }
            foreach ($arrUpdatedLocales as $langId) {
                if ($newLangData[$catId][$langId] != $oldLangData[$catId][$langId] ) {
                    if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_categories_locale` SET
                            `name` = '" . contrexx_addslashes(htmlentities($newLangData[$catId][$langId], ENT_QUOTES, CONTREXX_CHARSET)). "'
                            WHERE `category_id` = " . $catId . " AND `lang_id` = " . $langId) === false) {
                        $status = false;
                    }
                }
            }
        }
        foreach ($arrRemovedLocales as $langId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_categories_locale` WHERE `lang_id` = " . $langId) === false) {
                $status = false;
            }
        }
        return $status;
    }


    /**
     * Saving feed settings locales
     * @global ADONewConnection
     * @param String $newsId
     * @param Array $newLangData
     * @return Boolean
     */
    protected function storeFeedLocales($settingsName, $newLangData) 
    {
        global $objDatabase;

        $this->getSettings();
        $oldLangData = $this->arrSettings[$settingsName];
        if (count($oldLangData) == 0) {
            return false;
        }
        $status = true;
        $arrNewLocales = array_diff(array_keys($newLangData), array_keys($oldLangData));
        $arrRemovedLocales = array_diff(array_keys($oldLangData), array_keys($newLangData));
        $arrUpdatedLocales = array_intersect(array_keys($newLangData), array_keys($oldLangData));
        foreach ($arrNewLocales as $langId) {
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_settings_locale` (`lang_id`, `name`, `value`)
                    VALUES ("   . $langId . ", '"
                                . $settingsName . "', '"
                                . contrexx_addslashes(htmlentities($newLangData[$langId], ENT_QUOTES, CONTREXX_CHARSET)) . "')")
                                === false) {
                $status = false;
            }
        }
        foreach ($arrUpdatedLocales as $langId) {
            if ($newLangData[$langId] != $oldLangData[$langId] ) {
                if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_settings_locale` SET
                        `value` = '" . contrexx_addslashes(htmlentities($newLangData[$langId], ENT_QUOTES, CONTREXX_CHARSET)). "'
                        WHERE `name` LIKE '" . $settingsName . "' AND `lang_id` = " . $langId) === false) {
                    $status = false;
                }
            }
        }
        foreach ($arrRemovedLocales as $langId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_settings_locale` WHERE `lang_id` = " . $langId) === false) {
                $status = false;
            }
        }
        return $status;
    }


    /**
     * Insert new locales after create news from backend
     * @global ADONewConnection
     * @param Integer $newsId
     * @param Array $newLangData
     * @return Boolean
     */
    function insertLocales($newsId, $newLangData) 
    {
        global $objDatabase;

        if (empty($newsId)) {
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
     * @global ADONewConnection
     * @param Integer   $newsId
     * @param String    $title
     * @param String    $text
     * @param String    $teaser_text
     * @return Boolean
     */
    function submitLocales($newsId, $title, $text, $teaser_text) 
    {
        global $objDatabase;

        if (empty($newsId)) {
            return false;
        }
        $status = true;
        $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."languages");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_news_locale (`lang_id`, `news_id`, `title`, `text`, `teaser_text`)
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
