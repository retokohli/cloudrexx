<?php

/**
 * Class XmlSitemapPageTree
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core
 */

namespace Cx\Core\PageTree;

/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH . '/File.class.php';
require_once ASCMS_CORE_PATH . '/pagetree/PageTree.class.php';

/**
 * Class XmlSitemapPageTree
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @access      public
 * @version     3.0.0
 * @package     contrexx
 * @subpackage  core
 */
class XmlSitemapPageTree extends \PageTree {

    private static $strFilePath = '';
    private static $strFileName = 'sitemap.xml';
    private static $strFileNameWithLang = 'sitemap_%s.xml';

    /**
     * Writes the XML-Sitemap in all langs (if activated in config)
     * @global type $_CONFIG
     * @global type $_CORELANG
     * @return boolean True on success (including deactivated), false otherwise
     */
    public static function write() {
        global $_CONFIG, $_CORELANG;

        if ($_CONFIG['xmlSitemapStatus'] == 'on') {
            $arrActiveLanguages = array();
            foreach (\FWLanguage::getLanguageArray() as $arrLanguage) {
                if ($arrLanguage['frontend'] == 1) {
                    $arrActiveLanguages[$arrLanguage['id']] = $arrLanguage['lang'];
                }
            }

            $arrFailed = array();
            foreach ($arrActiveLanguages as $langId => $langCode) {
                $langSitemap = new static(\Env::get('em'), 0, null, $langId);
                if (!$langSitemap->writeXML()) {
                    $arrFailed[] = sprintf($_CORELANG['TXT_CORE_XML_SITEMAP_NOT_WRITABLE'], sprintf(self::$strFileNameWithLang, $langCode));
                }
            }

            if (count($arrFailed)) {
                return implode('<br />', $arrFailed);
            }
        }

        return true;
    }
    
    /**
     * Renders the PageTree element
     * @param type $title
     * @param type $level
     * @param type $hasChilds
     * @param type $lang
     * @param type $path
     * @param type $current
     * @param type $page
     * @return type 
     */
    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page) {
        global $_CONFIG;
        
        $location = ASCMS_PROTOCOL . '://'
                . $_CONFIG['domainUrl']
                . ($_SERVER['SERVER_PORT'] == 80 ? null : ':' . intval($_SERVER['SERVER_PORT']))
                . ASCMS_PATH_OFFSET
                . '/' . \FWLanguage::getLanguageCodeById($this->lang)
                . $page->getPath();
        return "\t" . '<url>' . 
                "\n\t\t" . '<loc>' . $location . '</loc>' . 
                "\n\t\t" . '<lastmod>' . $this->getLastModificationDate($page) . '</lastmod>' . 
                "\n\t\t" . '<changefreq>' . $this->getChangingFrequency($page) . '</changefreq>' .
                "\n\t\t" . '<priority>0.5</priority>' .
                "\n\t" . '</url>' . "\n";
    }

    /**
     * PageTree override (unused)
     * @param type $level
     * @param type $hasChilds
     * @param type $lang
     * @param type $page
     * @return string 
     */
    protected function postRenderElement($level, $hasChilds, $lang, $page) {
        return '';
    }

    /**
     * Renders the head of the PageTree
     * @param type $lang
     * @return type 
     */
    protected function renderHeader($lang) {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }
    
    /**
     * Renders the foot of the PageTree
     * @param type $lang
     * @return string 
     */
    protected function renderFooter($lang) {
        return '</urlset>';
    }
    
    /**
     * PageTree override (unused)
     * @param type $lang 
     */
    protected function preRender($lang) {
    }
    
    /**
     * PageTree override (unused)
     * @param type $lang 
     */
    protected function postRender($lang) {
    }


    /**
     * Creates a file (if it does not already exist) and sets write permission
     * @param String $filename Name of the file
     * @return boolean True on success, false otherwise
     */
    protected function prepareFileAccess($filename) {
        $objFile = new \File();

        return (
                file_exists(ASCMS_DOCUMENT_ROOT . self::$strFilePath . '/' . $filename)
                || touch(ASCMS_DOCUMENT_ROOT . self::$strFilePath . '/' . $filename)
                || $objFile->touchFile(self::$strFilePath . '/' . $filename)
                ) && (
                is_writable(ASCMS_DOCUMENT_ROOT . self::$strFilePath . '/' . $filename)
                || $objFile->setChmod(ASCMS_DOCUMENT_ROOT . self::$strFilePath, ASCMS_PATH_OFFSET . self::$strFilePath, '/' . $filename)
                );
    }

    /**
     * Write sitemap-file
     *
     * @global     object
     * @global     array
     * @param   array An array containing the language ID's of which languages should be included in the sitemap.
     * @param   string  The two letter language code of the selected language used as the virtual language path
     */
    protected function writeXML() {
        $filename = \FWLanguage::getLanguageCodeById($this->lang) ? sprintf(self::$strFileNameWithLang, \FWLanguage::getLanguageCodeById($this->lang)) : self::$strFileName;

        if (!$this->prepareFileAccess($filename)) {
            return false;
        }
        
        $xml = $this->render();
        
        $handleFile = fopen(ASCMS_DOCUMENT_ROOT . self::$strFilePath . '/' . $filename, 'w');
        if ($handleFile) {
            //Write values
            flock($handleFile, LOCK_EX); //set semaphore

            fwrite($handleFile, $xml);

            flock($handleFile, LOCK_UN); //release semaphore
            fclose($handleFile);
        }

        return true;
    }

    /**
     * Creates the modification-date of a page as a string which can be processed by google. The method uses
     * for module-pages the current date, for normale pages the date of last modification.
     *
     * @param		integer		$intModule: value of the module-field in the database
     * @param		string		$strCmd: value of the cmd-field in the database
     * @param		integer		$intTimestamp: last update of the page as a timestamp
     * @return		string		A date string which can be understood by google
     */
    protected function getLastModificationDate($page) {
        $date = $page->getLastModificationDateTime();
        return $date->format('Y-m-d');
    }

    /**
     * Returns the changing-frequency of the page depending on the database values. If the page is a module
     * page, the frequency is set to 'hourly', for normal pages to 'weekly'.
     *
     * @param		integer		$intModule: value of the module-field in the database
     * @param		string		$strCmd: value of the cmd-field in the database
     * @return		string		true, if the page is a module page. Otherwise false.
     */
    protected function getChangingFrequency($page) {
        return $page->getChangeFrequency();
    }
}
