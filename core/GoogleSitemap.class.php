<?php
/**
 * Class GoogleSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.1.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class GoogleSitemap
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.1.0
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
     * @return boolean    true if file can be written, false if not
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
     * @global     object        $objDatabase
     * @global     array        $_CONFIG
     */
    function writeFile() {
        global $objDatabase, $_CONFIG;

        if ($this->boolActivated) {
            $handleFile = fopen($this->strFilePath.$this->strFileName,'w+');
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
                
                $strActiveLanguages = '';
                foreach ($this->getLanguages() as $intKey => $intLanguageId) {
                	$strActiveLanguages .= $intLanguageId.',';
                }
                $strActiveLanguages = substr($strActiveLanguages,0,-1);

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
                        	$strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/'.$objResult->fields['aliasName'];
                        } else {
                        	//No alias existing
                       		if ($objResult->fields['module'] == 0) {
	                            if (!empty($objResult->fields['cmd'])) {
	                                $strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?cmd='.$objResult->fields['cmd'];
	                                $strContent .= (count($this->getLanguages()) > 1) ? '&amp;langId='.$objResult->fields['langid'] : '';
	                            } else {
	                                $strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?page='.$objResult->fields['catid'];
	                                //No addition of language-id needed because a pageId is always unique!
	                            }
	                        } else {
	                            $strContent .= ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?section='.$arrModules[$objResult->fields['module']];
	                            if (!empty($objResult->fields['cmd'])) {
	                                $strContent .= '&amp;cmd='.$objResult->fields['cmd'];
	                            }
	                            $strContent .= (count($this->getLanguages()) > 1) ? '&amp;langId='.$objResult->fields['langid'] : '';
	                        }
                        }

                        $strContent .= "</loc>\n";
                        $strContent .= "\t\t<lastmod>".$this->getLastModificationDate($objResult->fields['module'], $objResult->fields['cmd'], $objResult->fields['changelog'])."</lastmod>\n";
                        $strContent .= "\t\t<changefreq>".$this->getChangingFrequency($objResult->fields['module'], $objResult->fields['cmd'])."</changefreq>\n";
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
        }
    }
    
     /**
     * Returns an array containing all active languages.
     *
     * @global     object       $objDatabase
     * @return     array        Array of all active languages
     */
    function getLanguages() {
    	global $objDatabase;
    	
    	$arrLanguages = array();
    	
    	$objResult = $objDatabase->Execute('SELECT      id
                                            FROM        '.DBPREFIX.'languages
                                            WHERE       frontend=1
                                            ORDER BY    id
                                        ');
    	
    	if ($objResult->RecordCount() > 0) {
    		while (!$objResult->EOF) {
    			$arrLanguages[count($arrLanguages)] = $objResult->fields['id'];
    			$objResult->MoveNext();
    		}
    	}
    	
    	return $arrLanguages;
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
    function getLastModificationDate($intModule, $strCmd, $intTimestamp) {
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
    function getChangingFrequency($intModule, $strCmd) {
    	if (intval($intModule) > 0 || !empty($strCmd)) {
    		return 'hourly';
    	} else {
    		return 'weekly';
    	}
    }
}
?>