<?php
/**
 * Class GoogleSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class GoogleSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class GoogleSitemap {

    var $boolActivated = false;
    var $strFilePath;
    var $strFileName;


    /**
     * Constructor
     *
     */
    function __construct() {
        global $_CONFIG;

        $this->strFileName = 'sitemap.xml';
        $this->strFilePath = ASCMS_DOCUMENT_ROOT.'/';

        if ($_CONFIG['googleSitemapStatus'] == 'on') {
            $this->boolActivated = $this->checkPermissions();
        }
    }


    /**
     * Check permissions of sitemap-file
     *
     * @return boolean  true if file can be written, false if not
     */
    function checkPermissions() {
        if (is_file($this->strFilePath.$this->strFileName)) {
            if (is_writable($this->strFilePath.$this->strFileName)) {
                return true;
            } else {
                return false;
            }
        } else {
            if (is_writable($this->strFilePath)) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Write sitemap-file
     *
     * @global     ADONewConnection
     * @global     array
     */
    function writeFile() {
        global $objDatabase, $_CONFIG;

        if ($this->boolActivated) {
            $handleFile = fopen($this->strFilePath.$this->strFileName,'w+');
            if ($handleFile) {
                //Header
                $strHeader =    "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                $strHeader .=   "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

                //Footer
                $strFooter =    "</urlset>";

                //Url
                $objResult = $objDatabase->Execute('SELECT      id,
                                                                name
                                                    FROM        '.DBPREFIX.'modules
                                                ');
                while (!$objResult->EOF) {
                    $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                    $objResult->MoveNext();
                }

                $objResult = $objDatabase->Execute('SELECT      catid,
                                                                changelog,
                                                                cmd,
                                                                module
                                                    FROM        '.DBPREFIX.'content_navigation
                                                    WHERE       is_validated="1" AND
                                                                activestatus="1" AND
                                                                displaystatus="on" AND
                                                                protected=0
                                                    ORDER BY    catid ASC
                                                ');
                if ($objResult->RecordCount() > 0) {
                    while (!$objResult->EOF) {

                        $strContent .= "<url>\n";
                        $strContent .= "<loc>";
                        if ($objResult->fields['module'] == 0) {
                            if (!empty($objResult->fields['cmd'])) {
                                $strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?cmd='.$objResult->fields['cmd'];
                            } else {
                                $strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?page='.$objResult->fields['catid'];
                            }
                        } else {
                            $strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?section='.$arrModules[$objResult->fields['module']];
                            if (!empty($objResult->fields['cmd'])) {
                                $strContent .= '&amp;cmd='.$objResult->fields['cmd'];
                            }
                        }
                        $strContent .= "</loc>\n";
                        $strContent .= "<lastmod>";
                        $strContent .= date('Y-m-d',$objResult->fields['changelog']);
                        $strContent .= "</lastmod>\n";
                        $strContent .= "<changefreq>always</changefreq>\n";
                        $strContent .= "<priority>0.5</priority>\n";
                        $strContent .= "</url>\n";

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
        }
    }
}
?>
