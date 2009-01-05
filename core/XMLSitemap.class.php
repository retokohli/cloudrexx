<?php

/**
 * Class XMLSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        2.0.2
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */
/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';

/**
 * Class XMLSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        2.0.2
 * @package     contrexx
 * @subpackage  core
 */
class XMLSitemap
{
    private static $strFilePath = '';
    private static $strFileName = 'sitemap.xml';
    private static $strFileNameWithLang = 'sitemap_%s.xml';

    public static function write()
    {
        global $_CONFIG, $objLanguage;

        if ($_CONFIG['xmlSitemapStatus'] == 'on') {
            if (!isset($objLanguage)) {
                $objLanguage = new FWLanguage();
            }

            foreach ($objLanguage->getLanguageArray() as $arrLanguage) {
                if ($arrLanguage['frontend'] == 1) {
                    $arrActiveLanguages[$arrLanguage['id']] = $arrLanguage['lang'];
                }
            }

            if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                foreach ($arrActiveLanguages as $langId => $langCode) {
                    XMLSitemap::writeXML(array($langId), $langCode);
                }
            } else {
               XMLSitemap::writeXML(array_keys($arrActiveLanguages));
            }
        }
    }


    private static function prepareFileAccess($filename)
    {
		$objFile = new File();

        return (
                file_exists(ASCMS_DOCUMENT_ROOT.XMLSitemap::$strFilePath.'/'.$filename)
                || touch(ASCMS_DOCUMENT_ROOT.XMLSitemap::$strFilePath.'/'.$filename)
                || $objFile->touchFile(XMLSitemap::$strFilePath.'/'.$filename)
            ) && (
                is_writable(ASCMS_DOCUMENT_ROOT.XMLSitemap::$strFilePath.'/'.$filename)
                || $objFile->setChmod(ASCMS_DOCUMENT_ROOT.XMLSitemap::$strFilePath, XMLSitemap::$strFilePath, '/'.$filename)
        );
    }


    /**
     * Write sitemap file
     * @global     object
     * @global     array
     * @param   array An array containing the language ID's of which languages should be included in the sitemap.
     * @param   string  The two letter language code of the selected language used as the virtual language path
     */
    private static function writeXML($arrLang, $code = null)
    {
        global $objDatabase, $_CONFIG;

        $filename = $code ? sprintf(XMLSitemap::$strFileNameWithLang, $code) : XMLSitemap::$strFileName;

        if (!XMLSitemap::prepareFileAccess($filename)) {
            return false;
        }

        $handleFile = fopen(ASCMS_DOCUMENT_ROOT.XMLSitemap::$strFilePath.'/'.$filename,'w+');
        if ($handleFile) {
            //Header
            $strHeader =     "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $strHeader .=     "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

            //Footer
            $strFooter =    "</urlset>";

            //Url
            $objResult = $objDatabase->Execute('SELECT        id,
                                                            name
                                                FROM        '.DBPREFIX.'modules
                                            ');
            while (!$objResult->EOF) {
                $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }

            $strActiveLanguages = implode(',', $arrLang);

            $objResult = $objDatabase->Execute('SELECT     	cn.catid 		AS catid,
                                                            cn.changelog 	AS changelog,
                                                            cn.cmd			AS cmd,
                                                            cn.module		AS module,
                                                            cn.lang			AS langid,
                                                            mas.isdefault	AS aliasIsDefault,
                                                            mas.url			AS aliasName
                                                FROM        '.DBPREFIX.'content_navigation	AS cn
                                                LEFT JOIN	'.DBPREFIX.'module_alias_target	AS mat
                                                ON			cn.catid = mat.url
                                                LEFT JOIN	'.DBPREFIX.'module_alias_source AS mas
                                                ON			mat.id = mas.target_id
                                                WHERE       cn.is_validated="1" 	AND
                                                            cn.activestatus="1" 	AND
                                                            cn.displaystatus="on" 	AND
                                                            cn.protected=0			AND
                                                            cn.lang IN ('.$strActiveLanguages.')
                                                ORDER BY    cn.catid ASC
                                            ');

            $strContent = '';
            if ($objResult->RecordCount() > 0) {

                while (!$objResult->EOF) {

                    $strContent .= "\t<url>\n";
                    $strContent .= "\t\t<loc>";

                    if (intval($objResult->fields['aliasIsDefault']) == 1) {
                        //Alias existing
                        $strContent .= ASCMS_PROTOCOL.'://'
                                    .$_CONFIG['domainUrl']
                                    .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                    .ASCMS_PATH_OFFSET
                                    .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                    .'/'.$objResult->fields['aliasName'];
                    } else {
                        //No alias existing
                        if ($objResult->fields['module'] == 0) {
                            if (!empty($objResult->fields['cmd'])) {
                                $strContent .= ASCMS_PROTOCOL.'://'
                                            .$_CONFIG['domainUrl']
                                            .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                            .ASCMS_PATH_OFFSET
                                            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                            .'/'.CONTREXX_DIRECTORY_INDEX
                                            .'?cmd='.$objResult->fields['cmd']
                                            .($_CONFIG['useVirtualLanguagePath'] == 'off' ? '&amp;langId='.$objResult->fields['langid'] : '');
                            } else {
                                $strContent .= ASCMS_PROTOCOL.'://'
                                            .$_CONFIG['domainUrl']
                                            .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                            .ASCMS_PATH_OFFSET
                                            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                            .'/'.CONTREXX_DIRECTORY_INDEX
                                            .'?page='.$objResult->fields['catid'];
                                            //No addition of language-id needed because a pageId is always unique!
                            }
                        } else {
                            $strContent .= ASCMS_PROTOCOL.'://'
                                        .$_CONFIG['domainUrl']
                                        .ASCMS_PATH_OFFSET
                                        .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                        .'/'.CONTREXX_DIRECTORY_INDEX
                                        .'?section='.$arrModules[$objResult->fields['module']]
                                        .(!empty($objResult->fields['cmd']) ? '&amp;cmd='.$objResult->fields['cmd'] : '')
                                        .($_CONFIG['useVirtualLanguagePath'] == 'off' ? '&amp;langId='.$objResult->fields['langid'] : '');
                        }
                    }

                    $strContent .= "</loc>\n";
                    $strContent .= "\t\t<lastmod>".XMLSitemap::getLastModificationDate($objResult->fields['module'], $objResult->fields['cmd'], $objResult->fields['changelog'])."</lastmod>\n";
                    $strContent .= "\t\t<changefreq>".XMLSitemap::getChangingFrequency($objResult->fields['module'], $objResult->fields['cmd'])."</changefreq>\n";
                    $strContent .= "\t\t<priority>0.5</priority>\n";
                    $strContent .= "\t</url>\n";

                    $objResult->MoveNext();
                }
            }

            //Write values
            flock($handleFile, LOCK_EX); //set semaphore

            @fwrite($handleFile,$strHeader);
            @fwrite($handleFile,$strContent);
            @fwrite($handleFile,$strFooter);

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
    private static function getLastModificationDate($intModule, $strCmd, $intTimestamp)
    {
    	if (intval($intModule) > 0 || !empty($strCmd)) {
    		return date('Y-m-d', time());
    	} else {
    		return date('Y-m-d', $intTimestamp);
    	}
    }


    /**
     * Returns the changing-frequency of the page depending on the database values. If the page is a module
     * page, the frequency is set to 'hourly', for normal pages to 'weekly'.
     *
     * @param		integer		$intModule: value of the module-field in the database
     * @param		string		$strCmd: value of the cmd-field in the database
     * @return		string		true, if the page is a module page. Otherwise false.
     */
    private static function getChangingFrequency($intModule, $strCmd)
    {
    	if (intval($intModule) > 0 || !empty($strCmd)) {
    		return 'hourly';
    	} else {
    		return 'weekly';
    	}
    }
}

?>
