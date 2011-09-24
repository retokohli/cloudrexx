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
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH .'/Image.class.php';

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

        // get multilanguage settings (for now only news_feed_title and news_feed_description)
        $query = "SELECT lang_id, name, value FROM ".DBPREFIX."module_news_settings_locale";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $this->arrSettings[$objResult->fields['name']][$objResult->fields['lang_id']] = $objResult->fields['value'];
            $objResult->MoveNext();
        }
    }


    function getCategoryMenu($selectedOption='')
    {
        global $objDatabase;

        $strMenu = "";
        $query = "SELECT category_id, name FROM ".DBPREFIX."module_news_categories_locale WHERE lang_id=".FRONTEND_LANG_ID." ORDER BY name";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $selected = $objResult->fields['category_id'] == $selectedOption ? "selected" : "";
            $strMenu .="<option value=\"".$objResult->fields['category_id']."\" $selected>".contrexx_raw2xhtml($objResult->fields['name'])."</option>\n";
            $objResult->MoveNext();
        }

        return $strMenu;
    }


    function getTypeMenu($selectedOption='')
    {
        global $objDatabase;
        global $_ARRAYLANG;

        $strMenu = "";
        $query = "SELECT type_id, name FROM ".DBPREFIX."module_news_types_locale WHERE lang_id=".FRONTEND_LANG_ID." ORDER BY name";
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $selected = $objResult->fields['type_id'] == $selectedOption ? "selected" : "";
            $strMenu .="<option value=\"".$objResult->fields['type_id']."\" $selected>".contrexx_raw2xhtml($objResult->fields['name'])."</option>\n";
            $objResult->MoveNext();
        }

        return $strMenu;
    }
    
    protected function getPublisherMenu($selectedOption = '', $categoryId = 0)
    {
        global $objDatabase, $objInit;

        $arrNewsPublisher = array();
        $arrPublisher = array();

        $query = "SELECT DISTINCT n.publisher_id
                    FROM ".DBPREFIX."module_news AS n 
                    INNER JOIN ".DBPREFIX."module_news_locale AS nl
                    ON nl.news_id = n.id
                    WHERE  nl.lang_id=".FRONTEND_LANG_ID."
                    AND n.status = 1
                    AND n.publisher_id != 0
                    ".($categoryId ? " AND n.catid=".$categoryId : '');
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $arrNewsPublisher[] = $objResult->fields['publisher_id'];
            $objResult->MoveNext();
        }

        $objUser = FWUser::getFWUserObject()->objUser->getUsers(array('id' => $arrNewsPublisher), null, null, array('company', 'lastname', 'firstname'));
        if ($objUser) {
            $showUsername = ($objInit->mode == 'backend');

            while(!$objUser->EOF) {
                $arrPublisher[$objUser->getId()] = FWUser::getParsedUserTitle($objUser, '', $showUsername);
                $objUser->next();
            }

            asort($arrPublisher);
        }

        $menu = '';
        foreach ($arrPublisher as $publisherId => $publisherTitle) {
            $selected = $publisherId == $selectedOption ? 'selected="selected"' : '';
            $menu .="<option value=\"$publisherId\" $selected>".contrexx_raw2xhtml($publisherTitle)."</option>\n";
        }

        return $menu;
    }

    protected function getAuthorMenu($selectedOption = '', $categoryId = 0)
    {
        global $objDatabase, $objInit;

        $arrNewsAuthor = array();
        $arrAuthor = array();

        $query = "SELECT DISTINCT n.author_id
                    FROM ".DBPREFIX."module_news AS n 
                    INNER JOIN ".DBPREFIX."module_news_locale AS nl
                    ON nl.news_id = n.id
                    WHERE  nl.lang_id=".FRONTEND_LANG_ID."
                    AND n.status = 1
                    AND n.author_id != 0
                    ".($categoryId ? " AND n.catid=".$categoryId : '');
        $objResult = $objDatabase->Execute($query);
        while (!$objResult->EOF) {
            $arrNewsAuthor[] = $objResult->fields['author_id'];
            $objResult->MoveNext();
        }

        $objUser = FWUser::getFWUserObject()->objUser->getUsers(array('id' => $arrNewsAuthor), null, null, array('company', 'lastname', 'firstname'));
        if ($objUser) {
            $showUsername = ($objInit->mode == 'backend');

            while(!$objUser->EOF) {
                $arrAuthor[$objUser->getId()] = FWUser::getParsedUserTitle($objUser, '', $showUsername);
                $objUser->next();
            }

            asort($arrAuthor);
        }

        $menu = '';
        foreach ($arrAuthor as $authorId => $authorTitle) {
            $selected = $authorId == $selectedOption ? 'selected="selected"' : '';
            $menu .="<option value=\"$authorId\" $selected>".contrexx_raw2xhtml($authorTitle)."</option>\n";
        }

        return $menu;
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
        if ($res==true) {
            $bodyEndTag=$arrayMatches[0][0];
            // Position des End-Tags holen
            $posEndTag = strpos($fullContent, $bodyEndTag, 0);
            // Content innerhalb der Body-Tags auslesen
         }
         $content = substr($fullContent, $posStartBodyContent, $posEndTag  - $posStartBodyContent);
         return $content;
    }


    function hasCategories()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("SELECT 1 FROM ".DBPREFIX."module_news_categories_locale");
        return $objResult !== false && $objResult->RecordCount();
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
            is_active,
            title,
            text,
            teaser_text
            FROM ".DBPREFIX."module_news_locale
            WHERE news_id = " . intval($id));

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrLangData[$objResult->fields['lang_id']] = array(
                    'active'      => $objResult->fields['is_active'],
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
     * Get types language data
     * @global ADONewConnection
     * @return Array
     */
    function getTypesLangData()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("SELECT lang_id,
            type_id,
            name
            FROM ".DBPREFIX."module_news_types_locale");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if (!isset($arrLangData[$objResult->fields['type_id']])) {
                    $arrLangData[$objResult->fields['type_id']] = array();
                }
                $arrLangData[$objResult->fields['type_id']][$objResult->fields['lang_id']] = $objResult->fields['name'];
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
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_locale` (`lang_id`, `news_id`, `is_active`,  `title`, `text`, `teaser_text`)
                    VALUES ("   . intval($langId) . ", "
                                . $newsId . ", '"
                                . contrexx_input2db($newLangData['active'][$langId]) . "', '"
                                . contrexx_input2db($newLangData['title'][$langId]) . "', '"
                                . $this->filterBodyTag(contrexx_input2db($newLangData['text'][$langId])) . "', '"
                                . contrexx_input2db($newLangData['teaser_text'][$langId]) . "')") === false) {
                $status = false;
            }
        }
        foreach ($arrRemovedLocales as $langId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_locale` WHERE `news_id` = " . $newsId . " AND `lang_id` = " . $langId) === false) {
                $status = false;
            }
        }
        foreach ($arrUpdatedLocales as $langId) {
            $newLangData['active'][$langId] = isset($newLangData['active'][$langId]) ? $newLangData['active'][$langId] : '0';
            if ($newLangData['active'][$langId] != $oldLangData[$langId]['active']
            || $newLangData['title'][$langId] != $oldLangData[$langId]['title']
            || $newLangData['text'][$langId] != $oldLangData[$langId]['text']
            || $newLangData['teaser_text'][$langId] != $oldLangData[$langId]['teaser_text'] ) {
                if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_locale` SET
                        `is_active` = '" . contrexx_input2db($newLangData['active'][$langId]) . "',
                        `title` = '" . contrexx_input2db($newLangData['title'][$langId]) . "',
                        `text` = '" . $this->filterBodyTag(contrexx_input2db($newLangData['text'][$langId])) . "',
                        `teaser_text` = '" . contrexx_input2db($newLangData['teaser_text'][$langId]) . "'
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
                        VALUES ("   . intval($langId) . ", "
                                    . $catId . ", '"
                                    . contrexx_input2db($newLangData[$catId][$langId]) . "')")
                                    === false) {
                    $status = false;
                }
            }
            foreach ($arrUpdatedLocales as $langId) {
                if ($newLangData[$catId][$langId] != $oldLangData[$catId][$langId] ) {
                    if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_categories_locale` SET
                            `name` = '" . contrexx_input2db($newLangData[$catId][$langId]). "'
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
     * Saving types locales
     * @global ADONewConnection
     * @param Array $newLangData
     * @return Boolean
     */
    protected function storeTypesLocales($newLangData)
    {
        global $objDatabase;

        $oldLangData = $this->getTypesLangData();
        if (count($oldLangData) == 0) {
            return false;
        }
        $status = true;
        $arrNewLocales = array_diff(array_keys($newLangData[key($newLangData)]), array_keys($oldLangData[key($oldLangData)]));
        $arrRemovedLocales = array_diff(array_keys($oldLangData[key($oldLangData)]), array_keys($newLangData[key($newLangData)]));
        $arrUpdatedLocales = array_intersect(array_keys($newLangData[key($newLangData)]), array_keys($oldLangData[key($oldLangData)]));
        foreach (array_keys($newLangData) as $typeId) {
            foreach ($arrNewLocales as $langId) {
                if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_types_locale` (`lang_id`, `type_id`, `name`)
                        VALUES ("   . intval($langId) . ", "
                                    . $typeId . ", '"
                                    . contrexx_input2db($newLangData[$typeId][$langId]) . "')")
                                    === false) {
                    $status = false;
                }
            }
            foreach ($arrUpdatedLocales as $langId) {
                if ($newLangData[$typeId][$langId] != $oldLangData[$typeId][$langId] ) {
                    if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_types_locale` SET
                            `name` = '" . contrexx_input2db($newLangData[$typeId][$langId]). "'
                            WHERE `type_id` = " . $typeId . " AND `lang_id` = " . $langId) === false) {
                        $status = false;
                    }
                }
            }
        }
        foreach ($arrRemovedLocales as $langId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_news_types_locale` WHERE `lang_id` = " . $langId) === false) {
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
                    VALUES ("   . intval($langId) . ", '"
                                . $settingsName . "', '"
                                . contrexx_input2db($newLangData[$langId]) . "')")
                                === false) {
                $status = false;
            }
        }
        foreach ($arrUpdatedLocales as $langId) {
            if ($newLangData[$langId] != $oldLangData[$langId] ) {
                if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_news_settings_locale` SET
                        `value` = '" . contrexx_input2db($newLangData[$langId]). "'
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
            if ($arrLanguage['frontend'] == 1 && isset($newLangData['active'][$langId])) {
                if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_news_locale` (`lang_id`, `news_id`, `is_active`, `title`, `text`, `teaser_text`)
                        VALUES ("   . intval($langId) . ", "
                                    . $newsId . ", '"
                                    . (isset($newLangData['active'][$langId]) ? contrexx_input2db($newLangData['active'][$langId]) : "0") . "', '"
                                    . (isset($newLangData['title'][$langId]) ? contrexx_input2db($newLangData['title'][$langId]) : "") . "', '"
                                    . (isset($newLangData['text'][$langId]) ? $this->filterBodyTag(contrexx_input2db($newLangData['text'][$langId])) : "") . "', '"
                                    . (isset($newLangData['teaser_text'][$langId]) ? contrexx_input2db($newLangData['teaser_text'][$langId]) : "") . "')") === false) {
                    $status = false;
                }
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
                        . contrexx_input2db($title) . "', '"
                        . $this->filterBodyTag(contrexx_input2db($text)) . "', '"
                        . contrexx_input2db($teaser_text) . "')")){
                    $status = false;
                }
                $objResult->MoveNext();
            }
        }
        return $status;
    }

    protected function getHtmlImageTag($src, $alt)
    {
        static $htmlImgTag = '<img src="%1$s" alt="%2$s" />';

        return sprintf($htmlImgTag, contrexx_raw2xhtml($src), $alt);
    }

    protected function parseImageThumbnail($imageSource, $thumbnailSource, $altText, $newsUrl)
    {
        $image = '';
        $imageLink = '';
        $source = '';
        if (!empty($thumbnailSource)) {
            $source = $thumbnailSource;
        } elseif (!empty($imageSource) && file_exists(ASCMS_PATH.ImageManager::getThumbnailFilename($imageSource))) {
            $source = ImageManager::getThumbnailFilename($imageSource);
        } elseif (!empty($imageSource)) {
            $source = $imageSource;
        }

        if (!empty($source)) {
            $image     = self::getHtmlImageTag($source, $altText);
            $imageLink = self::parseLink($newsUrl, $altText, $image);
        }

        return array($image, $imageLink, $source);
    }

    protected static function parseLink($href, $title, $innerHtml, $class=null)
    {
        static $htmlLinkTag = '<a href="%1$s" title="%2$s">%3$s</a>';

        if (empty($href)) return '';

        return sprintf($htmlLinkTag, contrexx_raw2xhtml($href), contrexx_raw2xhtml($title), $innerHtml);
    }
}
?>
