<?php
/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  coremodule_cache
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  coremodule_cache
 */
class CacheManager extends cacheLib
{
    var $objTpl;
    var $arrSettings = array();

    private $objSettings;

    /**
     * Constructor
     *
     */
    function Cache()
    {
        $this->__construct();
    }

    /**
     * PHP5 constructor
     *
     * @global     object    $objTemplate
     * @global    array    $_CORELANG
     */
    function __construct()
    {
        global $objTemplate, $_CORELANG;

        $this->objTpl = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/cache/template');
        CSRF::add_placeholder($this->objTpl);
        $this->objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->arrSettings = $this->getSettings();
        $this->objSettings = new settingsManager();

        if (is_dir(ASCMS_CACHE_PATH)) {
            if (is_writable(ASCMS_CACHE_PATH)) {
                $this->strCachePath = ASCMS_CACHE_PATH . '/';
            } else {
                $objTemplate->SetVariable('CONTENT_STATUS_MESSAGE', $_CORELANG['TXT_CACHE_ERR_NOTWRITABLE'] . ASCMS_CACHE_PATH);
            }
        } else {
            $objTemplate->SetVariable('CONTENT_STATUS_MESSAGE', $_CORELANG['TXT_CACHE_ERR_NOTEXIST'] . ASCMS_CACHE_PATH);
        }
    }

    /**
     * Creates an array containing all important cache-settings
     *
     * @global     object    $objDatabase
     * @return    array    $arrSettings
     */
    function getSettings()
    {
        global $objDatabase;

        $arrSettings = array();

        $objResult = $objDatabase->Execute('SELECT	setname,
													setvalue
											FROM	' . DBPREFIX . 'settings
											WHERE	setname LIKE "cache%"
										');
        while (!$objResult->EOF) {
            $arrSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
            $objResult->MoveNext();
        }

        return $arrSettings;
    }

    /**
     * Show settings of the module
     *
     * @global     object    $objTemplate
     * @global     array    $_CORELANG
     */
    function showSettings()
    {
        global $objTemplate, $_CORELANG;

        $this->objTpl->loadTemplateFile('settings.html');
        $this->objTpl->setVariable(array(
            'TXT_CACHE_GENERAL' => $_CORELANG['TXT_SETTINGS_MENU_CACHE'],
            'TXT_CACHE_STATS' => $_CORELANG['TXT_CACHE_STATS'],
            'TXT_CACHE_CONTREXX_CACHING' => $_CORELANG['TXT_CACHE_CONTREXX_CACHING'],
            'TXT_CACHE_USERCACHE' => $_CORELANG['TXT_CACHE_USERCACHE'],
            'TXT_CACHE_OPCACHE' => $_CORELANG['TXT_CACHE_OPCACHE'],
            'TXT_CACHE_EMPTY' => $_CORELANG['TXT_CACHE_EMPTY'],
            'TXT_CACHE_STATS' => $_CORELANG['TXT_CACHE_STATS'],
            'TXT_CACHE_ENGINE' => $_CORELANG['TXT_CACHE_ENGINE'],
            'TXT_CACHE_INSTALLATION_STATE' => $_CORELANG['TXT_CACHE_INSTALLATION_STATE'],
            'TXT_CACHE_ACTIVE_STATE' => $_CORELANG['TXT_CACHE_ACTIVE_STATE'],
            'TXT_CACHE_CONFIGURATION_STATE' => $_CORELANG['TXT_CACHE_CONFIGURATION_STATE'],
            'TXT_CACHING' => $_CORELANG['TXT_CACHING'],
            'TXT_SETTINGS_SAVE' => $_CORELANG['TXT_SAVE'],
            'TXT_SETTINGS_ON' => $_CORELANG['TXT_ACTIVATED'],
            'TXT_SETTINGS_OFF' => $_CORELANG['TXT_DEACTIVATED'],
            'TXT_SETTINGS_STATUS' => $_CORELANG['TXT_CACHE_SETTINGS_STATUS'],
            'TXT_SETTINGS_STATUS_HELP' => $_CORELANG['TXT_CACHE_SETTINGS_STATUS_HELP'],
            'TXT_SETTINGS_EXPIRATION' => $_CORELANG['TXT_CACHE_SETTINGS_EXPIRATION'],
            'TXT_SETTINGS_EXPIRATION_HELP' => $_CORELANG['TXT_CACHE_SETTINGS_EXPIRATION_HELP'],
            'TXT_EMPTY_BUTTON' => $_CORELANG['TXT_CACHE_EMPTY'],
            'TXT_EMPTY_DESC' => $_CORELANG['TXT_CACHE_EMPTY_DESC'],
            'TXT_STATS_FILES' => $_CORELANG['TXT_CACHE_STATS_FILES'],
            'TXT_STATS_FOLDERSIZE' => $_CORELANG['TXT_CACHE_STATS_FOLDERSIZE']
        ));

        if ($this->objSettings->isWritable()) {
            $this->objTpl->parse('cache_submit_button');
        } else {
            $this->objTpl->hideBlock('cache_submit_button');
            $objTemplate->SetVariable('CONTENT_STATUS_MESSAGE', implode("<br />\n", $this->objSettings->strErrMessage));
        }
        
        $this->initOPCaching();
        $this->initUserCaching();
        $this->getActivatedCacheEngines();
        
        // parse op cache engines
        $this->parseOPCacheEngines();
        // parse user cache engines
        $this->parseUserCacheEngines();

        $intFoldersize = 0;
        $intFiles = 0;

        $handleFolder = opendir($this->strCachePath);
        if ($handleFolder) {
            while ($strFile = readdir($handleFolder)) {
                if ($strFile != '.' && $strFile != '..') {

                    $intFoldersize += filesize($this->strCachePath . $strFile);
                    ++$intFiles;
                }
            }
            closedir($handleFolder);
        }

        $this->objTpl->setVariable(array(
            'SETTINGS_STATUS_ON' => ($this->arrSettings['cacheEnabled'] == 'on') ? 'checked' : '',
            'SETTINGS_STATUS_OFF' => ($this->arrSettings['cacheEnabled'] == 'off') ? 'checked' : '',
            'SETTINGS_EXPIRATION' => intval($this->arrSettings['cacheExpiration']),
            'STATS_FILES' => $intFiles,
            'STATS_FOLDERSIZE' => number_format($intFoldersize / 1024, 2, '.', '\''),
        ));

        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => $_CORELANG['TXT_SETTINGS_MENU_CACHE'],
            'ADMIN_CONTENT' => $this->objTpl->get()
        ));
    }

    /**
     * Update settings and write them to the database
     *
     * @global     object    $objDatabase
     * @global     object    $objTemplate
     * @global     array    $_CORELANG
     */
    function updateSettings()
    {
        global $objDatabase, $objTemplate, $_CORELANG, $_CONFIG;

        if (!isset($_POST['frmSettings_Submit'])) {
            return;
        }

        $strStatus = ($_POST['cachingStatus'] == 'on') ? 'on' : 'off';
        $intExpiration = intval($_POST['cachingExpiration']);

        $objDatabase->Execute('	UPDATE	' . DBPREFIX . 'settings
								SET		setvalue="' . $strStatus . '"
								WHERE	setname="cacheEnabled"
								LIMIT	1
							');

        $objDatabase->Execute('	UPDATE	' . DBPREFIX . 'settings
								SET		setvalue="' . $intExpiration . '"
								WHERE	setname="cacheExpiration"
								LIMIT	1
							');
        
        $objDatabase->Execute('UPDATE '.DBPREFIX.'settings SET setvalue="' . contrexx_input2db($_POST['usercache']) . '" WHERE setname="cacheUserCache"');
        $objDatabase->Execute('UPDATE '.DBPREFIX.'settings SET setvalue="' . contrexx_input2db($_POST['opcache']) . '" WHERE setname="cacheOPCache"');

        $_CONFIG['cacheUserCache'] = contrexx_input2raw($_POST['usercache']);
        $_CONFIG['cacheOPCache'] = contrexx_input2raw($_POST['opcache']);
        
        $this->arrSettings = $this->getSettings();

        $this->objSettings->writeSettingsFile();

        if (!count($this->objSettings->strErrMessage)) {
            $objTemplate->SetVariable('CONTENT_OK_MESSAGE', $_CORELANG['TXT_SETTINGS_UPDATED']);
        } else {
            $objTemplate->SetVariable('CONTENT_STATUS_MESSAGE', implode("<br />\n", $this->objSettings->strErrMessage));
        }
    }
    
    private function parseOPCacheEngines() {
        $cachingEngines = array(
            self::CACHE_ENGINE_APC => array(),
            self::CACHE_ENGINE_ZEND_OPCACHE => array(),
            self::CACHE_ENGINE_XCACHE => array(),
        );
        $this->objTpl->setVariable('CHECKED_OPCACHE_' . strtoupper($this->getOpCacheEngine()), 'checked="checked"');
        if ($this->isInstalled(self::CACHE_ENGINE_APC)) {
            $cachingEngines[self::CACHE_ENGINE_APC]['installed'] = true;
        }
        if ($this->isActive(self::CACHE_ENGINE_APC)) {
            $cachingEngines[self::CACHE_ENGINE_APC]['active'] = true;
        }
        if ($this->isConfigured(self::CACHE_ENGINE_APC)) {
            $cachingEngines[self::CACHE_ENGINE_APC]['configured'] = true;
        }
        
        if ($this->isInstalled(self::CACHE_ENGINE_ZEND_OPCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_ZEND_OPCACHE]['installed'] = true;
        }
        if ($this->isActive(self::CACHE_ENGINE_ZEND_OPCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_ZEND_OPCACHE]['active'] = true;
        }
        if ($this->isConfigured(self::CACHE_ENGINE_ZEND_OPCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_ZEND_OPCACHE]['configured'] = true;
        }
        
        if ($this->isInstalled(self::CACHE_ENGINE_XCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_XCACHE]['installed'] = true;
        }
        if ($this->isActive(self::CACHE_ENGINE_XCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_XCACHE]['active'] = true;
        }
        if ($this->isConfigured(self::CACHE_ENGINE_XCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_XCACHE]['configured'] = true;
        }
        
        foreach ($cachingEngines as $engine => $data) {
            $installationIcon = $activeIcon = $configurationIcon = 'led_red.gif';
            if (isset($data['installed']) && isset($data['active']) && isset($data['configured'])) {
                if ($this->objTpl->blockExists('cache_opcache_' . $engine)) {
                    $this->objTpl->touchBlock('cache_opcache_' . $engine);
                }
            }
            if (isset($data['installed'])) {
                $installationIcon = 'led_green.gif';
            }
            if (isset($data['active'])) {
                $activeIcon = 'led_green.gif';
            }
            if (isset($data['configured'])) {
                $configurationIcon = 'led_green.gif';
            }
            $engine = strtoupper($engine);
            $this->objTpl->setVariable($engine . '_OPCACHE_INSTALLATION_ICON', $installationIcon);
            $this->objTpl->setVariable($engine . '_OPCACHE_ACTIVE_ICON', $activeIcon);
            $this->objTpl->setVariable($engine . '_OPCACHE_CONFIGURATION_ICON', $configurationIcon);
        }
    }
    
    private function parseUserCacheEngines() {
        $cachingEngines = array(
            self::CACHE_ENGINE_APC => array(),
            self::CACHE_ENGINE_MEMCACHE => array(),
            self::CACHE_ENGINE_XCACHE => array()
        );
        $this->objTpl->setVariable('CHECKED_USERCACHE_' . strtoupper($this->getUserCacheEngine()), 'checked="checked"');
        if ($this->isInstalled(self::CACHE_ENGINE_APC, true)) {
            $cachingEngines[self::CACHE_ENGINE_APC]['installed'] = true;
        }
        if ($this->isActive(self::CACHE_ENGINE_APC)) {
            $cachingEngines[self::CACHE_ENGINE_APC]['active'] = true;
        }
        if ($this->isConfigured(self::CACHE_ENGINE_APC, true)) {
            $cachingEngines[self::CACHE_ENGINE_APC]['configured'] = true;
        }
        
        if ($this->isInstalled(self::CACHE_ENGINE_MEMCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_MEMCACHE]['installed'] = true;
        }
        if ($this->isActive(self::CACHE_ENGINE_MEMCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_MEMCACHE]['active'] = true;
        }
        if ($this->isConfigured(self::CACHE_ENGINE_MEMCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_MEMCACHE]['configured'] = true;
        }
        
        if ($this->isInstalled(self::CACHE_ENGINE_XCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_XCACHE]['installed'] = true;
        }
        if ($this->isActive(self::CACHE_ENGINE_XCACHE)) {
            $cachingEngines[self::CACHE_ENGINE_XCACHE]['active'] = true;
        }
        if ($this->isConfigured(self::CACHE_ENGINE_XCACHE, true)) {
            $cachingEngines[self::CACHE_ENGINE_XCACHE]['configured'] = true;
        }
        
        foreach ($cachingEngines as $engine => $data) {
            $installationIcon = $activeIcon = $configurationIcon = 'led_red.gif';
            if (isset($data['installed']) && isset($data['active']) && isset($data['configured'])) {
                if ($this->objTpl->blockExists('cache_usercache_' . $engine)) {
                    $this->objTpl->touchBlock('cache_usercache_' . $engine);
                }
            }
            if (isset($data['installed'])) {
                $installationIcon = 'led_green.gif';
            }
            if (isset($data['active'])) {
                $activeIcon = 'led_green.gif';
            }
            if (isset($data['configured'])) {
                $configurationIcon = 'led_green.gif';
            }
            $engine = strtoupper($engine);
            $this->objTpl->setVariable($engine . '_USERCACHE_INSTALLATION_ICON', $installationIcon);
            $this->objTpl->setVariable($engine . '_USERCACHE_ACTIVE_ICON', $activeIcon);
            $this->objTpl->setVariable($engine . '_USERCACHE_CONFIGURATION_ICON', $configurationIcon);
        }
    }

    /**
     * Delete all files in cache-folder
     *
     * @global     object    $objTemplate
     * @global     array    $_CORELANG
     */
    function deleteAllFiles()
    {
        global $_CORELANG, $objTemplate;

        $this->_deleteAllFiles();

        $objTemplate->SetVariable('CONTENT_OK_MESSAGE', $_CORELANG['TXT_CACHE_FOLDER_EMPTY']);
    }


    /**
     * Delete all specific file from cache-folder
     *
     * @global     object    $objDatabase
     */
    function deleteSingleFile($intPageId)
    {
        global $objDatabase;

        $intPageId = intval($intPageId);
        if ($intPageId > 0) {
            $arrPageContent = array('url' => '/index.php?page=' . $intPageId,
                'request' => array('page' => strval($intPageId))
            );
            $arrFileNames[0] = md5(serialize($arrPageContent));

            $objResult = $objDatabase->Execute('SELECT		id
												FROM		' . DBPREFIX . 'languages
												ORDER BY	id ASC
											');
            while (!$objResult->EOF) {
                $arrLanguages[$objResult->fields['id']] = $objResult->fields['id'];
                $objResult->MoveNext();
            }


            $i = 2;
            foreach ($arrLanguages as $intKey1 => $intLangId1) {
                foreach ($arrLanguages as $intKey2 => $intLangId2) {
                    unset($arrPageContent);
                    $arrPageContent = array('url' => '/index.php?page=' . $intPageId,
                        'request' => array('backendLangId' => $intLangId1,
                            'langId' => $intLangId2,
                            'page' => strval($intPageId)

                        )
                    );
                    $arrFileNames[$i] = md5(serialize($arrPageContent));
                    $i++;
                }
            }

            foreach ($arrFileNames as $intKey => $strFileName) {
                if (is_file($this->strCachePath . $strFileName)) {
                    @unlink($this->strCachePath . $strFileName);
                }
            }
        }
    }
}

?>
