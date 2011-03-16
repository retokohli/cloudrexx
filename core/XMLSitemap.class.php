<?php
/**
 * Class XMLSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        2.1.0
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
class XMLSitemap {

    private static $strFilePath = '';
    private static $strFileName = 'sitemap.xml';
    private static $strFileNameWithLang = 'sitemap_%s.xml';

    public static function write()
    {
        global $_CONFIG, $_CORELANG;

        if ($_CONFIG['xmlSitemapStatus'] == 'on') {
            foreach (FWLanguage::getLanguageArray() as $arrLanguage) {
                if ($arrLanguage['frontend'] == 1) {
                    $arrActiveLanguages[$arrLanguage['id']] = $arrLanguage['lang'];
                }
            }

            if ($_CONFIG['useVirtualLanguagePath'] == 'on') {
                $arrFailed = array();
                foreach ($arrActiveLanguages as $langId => $langCode) {
                    if (!XMLSitemap::writeXML(array($langId), $langCode)) {
                        $arrFailed[] = sprintf($_CORELANG['TXT_CORE_XML_SITEMAP_NOT_WRITABLE'], sprintf(XMLSitemap::$strFileNameWithLang, $langCode));
                    }
                }

                if (count($arrFailed)) {
                    return implode('<br />', $arrFailed);
                }
            } else {
               if (!XMLSitemap::writeXML(array_keys($arrActiveLanguages))) {
                   return sprintf($_CORELANG['TXT_CORE_XML_SITEMAP_NOT_WRITABLE'], XMLSitemap::$strFileName);
               }
            }
        }

        return true;
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
                || $objFile->setChmod(ASCMS_DOCUMENT_ROOT.XMLSitemap::$strFilePath, ASMCS_PATH_OFFSET.XMLSitemap::$strFilePath, '/'.$filename)
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
            $objResult = $objDatabase->Execute('SELECT id, name FROM '.DBPREFIX.'modules');
            while (!$objResult->EOF) {
                $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }

            $strContent = '';

            $strActiveLanguages = implode(',', $arrLang);
            $objFWUser = FWUser::getFWUserObject();

            $objResult = $objDatabase->Execute('SELECT     	cn.catid 		AS catid,
                                                            cn.changelog 	AS changelog,
                                                            cn.cmd			AS cmd,
                                                            cn.module		AS module,
                                                            cn.lang			AS langid,
                                                            cc.redirect     AS redirect,
                                                '.($_CONFIG['aliasStatus'] ? '
                                                            mas.isdefault	AS aliasIsDefault,
                                                            mas.url			AS aliasName'
                                                            : '0            AS aliasIsDefault').'
                                                FROM        '.DBPREFIX.'content_navigation	AS cn
                                                INNER JOIN  '.DBPREFIX.'content             AS cc
                                                ON          cc.id = cn.catid
                                                '.($_CONFIG['aliasStatus'] ?
                                                'LEFT JOIN	'.DBPREFIX.'module_alias_target	AS mat
                                                ON			cn.catid = mat.url
                                                LEFT JOIN	'.DBPREFIX.'module_alias_source AS mas
                                                ON			mat.id = mas.target_id' : '').'
                                                WHERE       cn.is_validated="1" 	AND
                                                            cn.activestatus="1" 	AND
                                                            cn.displaystatus="on" 	AND
                                                            (cn.startdate<=CURDATE() OR cn.startdate=\'0000-00-00\') AND
                                                            (cn.enddate>=CURDATE() OR cn.enddate=\'0000-00-00\') AND
                                                            cn.lang IN ('.$strActiveLanguages.')
                                                            '.($_CONFIG['coreListProtectedPages'] == 'off' ? 'AND cn.protected=0 ' : '').'
                                                ORDER BY    cn.parcat, cn.displayorder
                                            ');

            if ($objResult && $objResult->RecordCount() > 0) {
                $arrPages = array();
                $arrLocations = array();
                $arrRedundancies = array();

                while (!$objResult->EOF) {
                    $isRedirection = false;

                    if (intval($objResult->fields['aliasIsDefault']) == 1) {
                        //Alias existing
                        $isRedirection = true;
                        $location = ASCMS_PROTOCOL.'://'
                            .$_CONFIG['domainUrl']
                            .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                            .ASCMS_PATH_OFFSET
                            .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                            .'/'.stripslashes($objResult->fields['aliasName']);
                    } else {
                        //No alias
                        if (!empty($objResult->fields['redirect'])) {
                            // redirection
                            $isRedirection = true;
                            if (preg_match('#^[a-z]+://#', $objResult->fields['redirect'])) {
                                // the redirection points towards a web ressource
                                if (!preg_match('#^https?://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'#', $objResult->fields['redirect'])) {
                                    // we won't include redirections to foreign ressources
                                    $objResult->MoveNext();
                                    continue;
                                }
                                $location = htmlentities($objResult->fields['redirect'], ENT_QUOTES, CONTREXX_CHARSET);
                            } elseif (strpos($objResult->fields['redirect'], '/') === 0) {
                                if (strpos($objResult->fields['redirect'], ASCMS_PATH_OFFSET) === false) {
                                    // we won't include redirections to foreign ressources
                                    $objResult->MoveNext();
                                    continue;
                                }
                                $location = ASCMS_PROTOCOL.'://'
                                    .$_CONFIG['domainUrl']
                                    .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                    .htmlentities($objResult->fields['redirect'], ENT_QUOTES, CONTREXX_CHARSET);
							} else {
                                $location = ASCMS_PROTOCOL.'://'
                                    .$_CONFIG['domainUrl']
                                    .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                    .ASCMS_PATH_OFFSET
                                    .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                    .'/'.htmlentities($objResult->fields['redirect'], ENT_QUOTES, CONTREXX_CHARSET);
                            }
                        } elseif ($objResult->fields['module'] == 0) {
                            // regular page
                            if (!empty($objResult->fields['cmd'])) {
                                $location = ASCMS_PROTOCOL.'://'
                                    .$_CONFIG['domainUrl']
                                    .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                    .ASCMS_PATH_OFFSET
                                    .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                    .'/'.CONTREXX_DIRECTORY_INDEX
                                    .'?cmd='.$objResult->fields['cmd']
                                    .($_CONFIG['useVirtualLanguagePath'] == 'off' ? '&amp;langId='.$objResult->fields['langid'] : '');
                            } else {
                                $location = ASCMS_PROTOCOL.'://'
                                    .$_CONFIG['domainUrl']
                                    .($_SERVER['SERVER_PORT'] == 80 ? null : ':'.intval($_SERVER['SERVER_PORT']))
                                    .ASCMS_PATH_OFFSET
                                    .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                    .'/'.CONTREXX_DIRECTORY_INDEX
                                    .'?page='.$objResult->fields['catid'];
                                    //No addition of language-id needed because a pageId is always unique!
                            }
                        } else {
                            // module page
                            $location = ASCMS_PROTOCOL.'://'
                                .$_CONFIG['domainUrl']
                                .ASCMS_PATH_OFFSET
                                .($_CONFIG['useVirtualLanguagePath'] == 'on' ? '/'.$code : null)
                                .'/'.CONTREXX_DIRECTORY_INDEX
                                .'?section='.$arrModules[$objResult->fields['module']]
                                .(!empty($objResult->fields['cmd']) ? '&amp;cmd='.contrexx_raw2xml($objResult->fields['cmd']) : '')
                                .($_CONFIG['useVirtualLanguagePath'] == 'off' ? '&amp;langId='.$objResult->fields['langid'] : '');
                        }
                    }

                    $arrPages[] = array(
                        'location'      => $location,
                        'redirection'   => $isRedirection,
                        'module'        => $objResult->fields['module'],
                        'cmd'           => $objResult->fields['cmd'],
                        'changelog'     => $objResult->fields['changelog']
                    );

                    if (!isset($arrLocations[$objResult->fields['langid']])) {
                        $arrLocations[$objResult->fields['langid']] = array();
                    }

                    if (in_array($location, $arrLocations[$objResult->fields['langid']])) {
                        $arrRedundancies[] = $location;
                    }
                    $arrLocations[$objResult->fields['langid']][] = $location;

                    $objResult->MoveNext();
                }

                // solve redundancies
                foreach ($arrLocations as $arrLocationsByLangId) {
                    foreach ($arrRedundancies as $redundancy) {
                        $arrRedundancyLocations = array_keys($arrLocationsByLangId, $redundancy);
                        $arrRemovablePages = array();

                        // find all pages that link to the page that has been listed more than once
                        foreach ($arrRedundancyLocations as $page) {
                            if  ($arrPages[$page]['redirection']) {
                                $arrRemovablePages[] = $page;
                            }
                        }

                        // if the target page itself isn't listed in the sitemap, we will use the one that occured as first
                        if (count($arrRedundancyLocations) == count($arrRemovablePages)) {
                            $arrRemovablePages = array_slice($arrRedundancyLocations, 1, null, true);
                        }

                        // remove redundancies
                        foreach ($arrRemovablePages as $page) {
                            unset($arrPages[$page]);
                        }
                    }
                }

                foreach ($arrPages as $arrPage) {
                    $strContent .= "\t<url>\n";
                    $strContent .= "\t\t<loc>".$arrPage['location']."</loc>\n";
                    $strContent .= "\t\t<lastmod>".XMLSitemap::getLastModificationDate($arrPage['module'], $arrPage['cmd'], $arrPage['changelog'])."</lastmod>\n";
                    $strContent .= "\t\t<changefreq>".XMLSitemap::getChangingFrequency($arrPage['module'], $arrPage['cmd'])."</changefreq>\n";
                    $strContent .= "\t\t<priority>0.5</priority>\n";
                    $strContent .= "\t</url>\n";
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
