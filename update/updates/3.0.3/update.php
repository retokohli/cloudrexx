<?php

require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileInterface.interface.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/File.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystem.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystemFile.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FTPFile.class.php';

function executeContrexxUpdate() {
    global $_CORELANG, $_CONFIG, $objDatabase, $objUpdate;

    $_SESSION['contrexx_update']['copyFilesFinished'] = !empty($_SESSION['contrexx_update']['copyFilesFinished']) ? $_SESSION['contrexx_update']['copyFilesFinished'] : false;

    // Copy cx files to the root directory
    if (!$_SESSION['contrexx_update']['copyFilesFinished']) {
        if (!loadMd5SumOfOriginalCxFiles()) {
            return false;
        }

        $copyFilesStatus = copyCxFilesToRoot(dirname(__FILE__) . '/cx_files', ASCMS_PATH . ASCMS_PATH_OFFSET);
        if ($copyFilesStatus !== true) {
            if ($copyFilesStatus === 'timeout') {
                setUpdateMsg(1, 'timeout');
            }
            return false;
        }
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_clear_cache();
        }
        $_SESSION['contrexx_update']['copyFilesFinished'] = true;

        // we need to stop the script here to force a reinitialization of the update system
        // this is required so that the new constants from config/set_constants.php are loaded
        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_TIME_MSG'].'<br />', 'msg');
        setUpdateMsg('Installation der neuen Dateien abgeschlossen.<br /><br />', 'msg');
        setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
        return false;
    }
    unset($_SESSION['contrexx_update']['copiedCxFilesIndex']);

    /**
     * This needs to be initialized before loading config/doctrine.php
     * Because we overwrite the Gedmo model (so we need to load our model
     * before doctrine loads the Gedmo one)
     */
    require_once(ASCMS_CORE_PATH . '/ClassLoader/ClassLoader.class.php');
    $cl = new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT, true);
    Env::set('ClassLoader', $cl);

    // Doctrine configuration
    $incDoctrineStatus = require_once(UPDATE_PATH . '/config/doctrine.php');
    Env::set('incDoctrineStatus', $incDoctrineStatus);

    $userData = array(
        'id'   => $_SESSION['contrexx_update']['user_id'],
        'name' => $_SESSION['contrexx_update']['username'],
    );
    $loggableListener = \Env::get('loggableListener');
    $loggableListener->setUsername(json_encode($userData));

    // Reinitialize FWLanguage. Now with fallback (doctrine).
    FWLanguage::init();

    if (!isset($_SESSION['contrexx_update']['update']['done'])) {
        $_SESSION['contrexx_update']['update']['done'] = array();
    }

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        DBG::msg('Installed version: '.$_CONFIG['coreCmsVersion']);
        Env::get('ClassLoader')->loadFile(dirname(__FILE__) . '/ContentMigration.class.php');
        $contentMigration = new \Cx\Update\Cx_3_0_3\ContentMigration();

        // Migrate statistics - this must be done before migrating to the new content architecture
        if (empty($_SESSION['contrexx_update']['content_stats'])) {
            DBG::msg('Migrate stats');
            if ($contentMigration->migrateStatistics()) {
                $_SESSION['contrexx_update']['content_stats'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Check if there are content of inactive languages.
        // If true then ask if the update system can remove them.
        if (empty($_SESSION['contrexx_update']['inactive_content_languages_checked'])) {
            DBG::msg('Check inactive content languages');
            $arrMigrateLangIds = $contentMigration->getActiveContentLanguageIds();

            if (!isset($_POST['skipMigrateLangIds'])) {
                $result = $contentMigration->getInactiveContentLanguageCheckboxes();

                if (!empty($result)) {
                    setUpdateMsg('Inhaltsseiten von inaktiven Sprache(n) gefunden', 'title');
                    setUpdateMsg('
                        Folgende Sprache(n) sind inaktiv, aber enthalten Inhaltsseiten:<br />
                        ' . $result . '<br />
                        Wählen Sie die inaktiven Sprachen, dessen Inhaltseiten Sie migrieren möchten.<br />
                        Klicken Sie anschliessend auf <b>Update fortsetzen...</b>.<br /><br />
                        <div class="message-alert">
                        <b>Achtung:</b><br />
                        Die Inhaltsseiten der inaktive Sprache(n), welche Sie nicht ausgewählt haben, werden gelöscht.
                        </div>
                    ', 'msg');
                    setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" /><input type="hidden" name="skipMigrateLangIds" id="skipMigrateLangIds" />', 'button');
                    return false;
                }
            } else {
                if (!empty($_POST['migrateLangIds'])) {
                    if (is_array($_POST['migrateLangIds'])) {
                        $_POST['migrateLangIds'] = array_filter($_POST['migrateLangIds'], 'intval');
                        if (!empty($_POST['migrateLangIds'])) {
                            $arrMigrateLangIds = array_merge($arrMigrateLangIds, $_POST['migrateLangIds']);
                        }
                    } else {
                        if (intval($_POST['migrateLangIds'])) {
                            $arrMigrateLangIds[] = intval($_POST['migrateLangIds']);
                        }
                    }
                }
            }

            $_SESSION['contrexx_update']['migrate_lang_ids'] = $arrMigrateLangIds;
            $_SESSION['contrexx_update']['inactive_content_languages_checked'] = true;
        }

        if (empty($_SESSION['contrexx_update']['migrate_lang_ids'])) {
            $_SESSION['contrexx_update']['migrate_lang_ids'] = $contentMigration->getActiveContentLanguageIds();
        }
        $contentMigration->arrMigrateLangIds = $_SESSION['contrexx_update']['migrate_lang_ids'];
        $contentMigration->migrateLangIds    = implode(',', $_SESSION['contrexx_update']['migrate_lang_ids']);

        // Migrate content
        if (empty($_SESSION['contrexx_update']['content_migrated'])) {
            DBG::msg('Migrate content');
            $status = $contentMigration->migrate();

            if ($status === true) {
                $_SESSION['contrexx_update']['content_migrated'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else if ($status === 'timeout') {
                setUpdateMsg(1, 'timeout');
                return false;
            } else {
                return false;
            }
        }

        // Page grouping
        if (empty($_SESSION['contrexx_update']['pages_grouped'])) {
            DBG::msg('Group pages');
            $pageGrouping = $contentMigration->pageGrouping();

            if ($pageGrouping === true) {
                $_SESSION['contrexx_update']['pages_grouped'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else if ($pageGrouping === 'timeout') {
                setUpdateMsg(1, 'timeout');
                return false;
            } else if ($pageGrouping === false) {
                return false;
            } else if (!empty($pageGrouping)) {
                $arrDialogData = array(
                    'similarPages' => $contentMigration->similarPages,
                );

                setUpdateMsg('Inhaltsseiten gruppieren', 'title');
                setUpdateMsg($pageGrouping, 'msg');
                setUpdateMsg('<input type="submit" value="' . $_CORELANG['TXT_UPDATE_NEXT'] . '" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
                setUpdateMsg($arrDialogData, 'dialog');
                return false;
            }
        }

        // Migrate aliases
        if (empty($_SESSION['contrexx_update']['aliases_migrated'])) {
            DBG::msg('Migrate aliases');
            if ($contentMigration->migrateAliases()) {
                $_SESSION['contrexx_update']['aliases_migrated'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Migrate blocks
        if (empty($_SESSION['contrexx_update']['blocks_migrated'])) {
            DBG::msg('Migrate blocks');
            if ($contentMigration->migrateBlocks()) {
                $_SESSION['contrexx_update']['blocks_migrated'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Drop old tables
        if (empty($_SESSION['contrexx_update']['old_tables_dropped'])) {
            DBG::msg('Drop old tables');
            if ($contentMigration->dropOldTables()) {
                $_SESSION['contrexx_update']['old_tables_dropped'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            }
        }
    } else {
        // we are updating from 3.0.0 rc1, rc2, stable or 3.0.0.1
        if (!include_once(dirname(__FILE__) . '/update3.php')) {
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/update3.php'));
            return false;
        }
        
        if (!createHtAccess()) {
            $webServerSoftware = !empty($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'apache') ? 'apache' : (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') ? 'iis' : '');
            $file = $webServerSoftware == 'iis' ? 'web.config' : '.htaccess';

            setUpdateMsg('Die Datei \'' . $file . '\' konnte nicht erstellt/aktualisiert werden.');
            return false;
        }

        $arrUpdate = $objUpdate->getLoadedVersionInfo();
        $_CONFIG['coreCmsVersion'] = $arrUpdate['cmsVersion'];

        try {
            $request = new \HTTP_Request2(ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/core_modules/License/versioncheck.php?force=true&silent=true', \HTTP_Request2::METHOD_POST);
            $response = $request->send();
        } catch (\HTTP_Request2_Exception $e) {
            \DBG::log($e->getMessage());
        }

        return true;
    }

    $arrDirs = array('core_module', 'module');
    $updateStatus = true;

    if (!include_once(dirname(__FILE__) . '/components/core/core.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/core.php'));
        return false;
    } elseif (UPDATE_UTF8 && !include_once(dirname(__FILE__) . '/components/core/utf8.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/utf8.php'));
        return false;
    } elseif (!include_once(dirname(__FILE__) . '/components/core/backendAreas.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/backendAreas.php'));
        return false;
    } elseif (!include_once(dirname(__FILE__) . '/components/core/modules.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/modules.php'));
        return false;
    } elseif (!include_once(dirname(__FILE__) . '/components/core/settings.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/settings.php'));
        return false;
    }

    if (!in_array('configSettings', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _writeNewConfigurationFile();
        if ($result === false) {
            return false;
        }

        if (UPDATE_UTF8) {
            $result = _utf8Update();
            if ($result === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
                }
                return false;
            }
        }

        $result = _writeNewConfigurationFile(true);
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'configSettings';
        }
    }

    if (UPDATE_UTF8) {
        $query = 'SET CHARACTER SET utf8';
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }

    if (!in_array('coreUpdate', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _coreUpdate();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_CORE_SYSTEM']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreUpdate';
        }
    }


    $missedModules = array();
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        $missedModules = getMissedModules();
        $conflictedModules = getConflictedModules($missedModules);
        if (!empty($conflictedModules)) {
            $conflictedModulesList = '';
            foreach ($conflictedModules as $moduleName => $moduleTables) {
                $conflictedModulesList = '<li><strong>'.$moduleName.':</strong> '.implode(', ', $moduleTables).'</li>';
            }
            setUpdateMsg($_CORELANG['TXT_CONFLICTED_MODULES_TITLE'], 'title');
            setUpdateMsg($_CORELANG['TXT_CONFLICTED_MODULES_DESCRIPTION'].'<ul>'.$conflictedModulesList.'</ul>', 'msg');
            setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_UPDATE_TRY_AGAIN'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
            return false;
        }
    }
    foreach ($arrDirs as $dir) {
        $dh = opendir(dirname(__FILE__).'/components/'.$dir);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if (!in_array($file, $_SESSION['contrexx_update']['update']['done'])) {
                    $fileInfo = pathinfo(dirname(__FILE__).'/components/'.$dir.'/'.$file);

                    if ($fileInfo['extension'] == 'php') {
                        DBG::msg("--------- updating $file ------");

                        if (!include_once(dirname(__FILE__).'/components/'.$dir.'/'.$file)) {
                            setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/'.$dir.'/'.$file));
                            return false;
                        }

                        if (!in_array($fileInfo['filename'], $missedModules)) {
                            $function = '_'.$fileInfo['filename'].'Update';
                            if (function_exists($function)) {
                                $result = $function();
                                if ($result === false) {
                                    if (empty($objUpdate->arrStatusMsg['title'])) {
                                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                                    }
                                    return false;
                                }
                            } else {
                                setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], '.'.$fileInfo['filename'], $file));
                                return false;
                            }
                        } else {
                            $function = '_'.$fileInfo['filename'].'Install';
                            if (function_exists($function)) {
                                $result = $function();
                                if ($result === false) {
                                    if (empty($objUpdate->arrStatusMsg['title'])) {
                                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                                    }
                                    return false;
                                } else {
                                    // fetch module info from components/core/module.php
                                    $arrModule = getModuleInfo($fileInfo['filename']);
                                    if ($arrModule) {
                                        try {
                                            \Cx\Lib\UpdateUtil::sql("INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` , `distributor` ) VALUES ( ".$arrModule['id']." , '".$arrModule['name']."', '".$arrModule['description_variable']."', '".$arrModule['status']."', '".$arrModule['is_required']."', '".$arrModule['is_core']."', 'Comvation AG') ON DUPLICATE KEY UPDATE `id` = `id`");
                                        } catch (\Cx\Lib\UpdateException $e) {
                                            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
                                        }
                                    } else {
                                        DBG::msg('unable to register module '.$fileInfo['filename']);
                                    }
                                }
                            } else {
                                setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], '.'.$fileInfo['filename'], $file));
                                return false;
                            }
                        }
                    }

                    $_SESSION['contrexx_update']['update']['done'][] = $file;
                }
            }
        } else {
            setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_DIR_COMPONENTS'], dirname(__FILE__).'/components/'.$dir));
            return false;
        }

        closedir($dh);
    }

    if (!in_array('coreSettings', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _updateSettings();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_BASIC_CONFIGURATION']), 'title');
            }
            return false;
        } else {
            $result = _writeNewConfigurationFile();
            if ($result === false) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'coreSettings';
        }
    }

    if (!in_array('coreModules', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _updateModules();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULES']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreModules';
        }
    }

    if (!in_array('coreBackendAreas', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _updateBackendAreas();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_SECURITY_SYSTEM']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreBackendAreas';
        }
    }

    if (!in_array('coreModuleRepository', $_SESSION['contrexx_update']['update']['done'])) {
        $result = _updateModuleRepository();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreModuleRepository';
        }
    }

    if (!in_array('moduleTemplates', $_SESSION['contrexx_update']['update']['done'])) {
        if (_updateModulePages() === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_TEMPLATES']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'moduleTemplates';
        }
    }
    
    if (!createHtAccess()) {
        $webServerSoftware = !empty($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'apache') ? 'apache' : (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') ? 'iis' : '');
        $file = $webServerSoftware == 'iis' ? 'web.config' : '.htaccess';

        setUpdateMsg('Die Datei \'' . $file . '\' konnte nicht erstellt/aktualisiert werden.');
        return false;
    }

    $arrUpdate = $objUpdate->getLoadedVersionInfo();
    $_CONFIG['coreCmsVersion'] = $arrUpdate['cmsVersion'];

    if (!in_array('coreLicense', $_SESSION['contrexx_update']['update']['done'])) {
        $lupd = new License();
        $result = $lupd->update();
        // ignore error to allow offline installations
        /*if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_LICENSE_DATA']), 'title');
            }
            return false;
        } else {*/
        $_SESSION['contrexx_update']['update']['done'][] = 'coreLicense';
        //}
    }

    return true;
}

function getMissedModules() {
    $installedModules = array();
    $result = \Cx\Lib\UpdateUtil::sql('SELECT `name`, `description_variable` FROM `'.DBPREFIX.'modules` WHERE `status` = "y" ORDER BY `name` ASC');
    if ($result) {
        while (!$result->EOF) {
            $installedModules[] = $result->fields['name'];
            $result->MoveNext();
        }
    }

    $missedModules = array();
    $potentialMissedModules = array('blog', 'calendar', 'directory', 'docsys', 'egov', 'feed', 'forum', 'gallery', 'guestbook', 'livecam', 'market', 'memberdir', 'newsletter', 'podcast', 'shop', 'voting', 'downloads', 'ecard', 'jobs', 'knowledge', 'mediadir');
    foreach ($potentialMissedModules as $module) {
        if (!in_array($module, $installedModules)) {
            $missedModules[] = $module;
        }
    }

    return $missedModules;
}


function getConflictedModules($missedModules) {
    $potentialMissedTables = array(
        'blog' => array(
            DBPREFIX.'module_blog_comments',
            DBPREFIX.'module_blog_categories',
            DBPREFIX.'module_blog_messages',
            DBPREFIX.'module_blog_messages_lang',
            DBPREFIX.'module_blog_message_to_category',
            DBPREFIX.'module_blog_networks',
            DBPREFIX.'module_blog_networks_lang',
            DBPREFIX.'module_blog_settings',
            DBPREFIX.'module_blog_votes',
        ),
        'calendar' => array(
            DBPREFIX.'module_calendar',
            DBPREFIX.'module_calendar_categories',
            DBPREFIX.'module_calendar_form_data',
            DBPREFIX.'module_calendar_form_fields',
            DBPREFIX.'module_calendar_registrations',
            DBPREFIX.'module_calendar_settings',
            DBPREFIX.'module_calendar_style',
        ),
        'directory' => array(
            DBPREFIX.'module_directory_categories',
            DBPREFIX.'module_directory_dir',
            DBPREFIX.'module_directory_inputfields',
            DBPREFIX.'module_directory_levels',
            DBPREFIX.'module_directory_mail',
            DBPREFIX.'module_directory_rel_dir_cat',
            DBPREFIX.'module_directory_rel_dir_level',
            DBPREFIX.'module_directory_settings',
            DBPREFIX.'module_directory_settings_google',
            DBPREFIX.'module_directory_vote',
        ),
        'docsys' => array(
            DBPREFIX.'module_docsys',
            DBPREFIX.'module_docsys_categories',
            DBPREFIX.'module_docsys_entry_category',
        ),
        'egov' => array(
            DBPREFIX.'module_egov_configuration',
            DBPREFIX.'module_egov_orders',
            DBPREFIX.'module_egov_products',
            DBPREFIX.'module_egov_product_calendar',
            DBPREFIX.'module_egov_product_fields',
            DBPREFIX.'module_egov_settings',
        ),
        'feed' => array(
            DBPREFIX.'module_feed_category',
            DBPREFIX.'module_feed_news',
            DBPREFIX.'module_feed_newsml_association',
            DBPREFIX.'module_feed_newsml_categories',
            DBPREFIX.'module_feed_newsml_documents',
            DBPREFIX.'module_feed_newsml_providers',
        ),
        'forum' => array(
            DBPREFIX.'module_forum_access',
            DBPREFIX.'module_forum_categories',
            DBPREFIX.'module_forum_categories_lang',
            DBPREFIX.'module_forum_notification',
            DBPREFIX.'module_forum_postings',
            DBPREFIX.'module_forum_rating',
            DBPREFIX.'module_forum_settings',
            DBPREFIX.'module_forum_statistics',
        ),
        'gallery' => array(
            DBPREFIX.'module_gallery_categories',
            DBPREFIX.'module_gallery_comments',
            DBPREFIX.'module_gallery_language',
            DBPREFIX.'module_gallery_language_pics',
            DBPREFIX.'module_gallery_pictures',
            DBPREFIX.'module_gallery_settings',
            DBPREFIX.'module_gallery_votes',
        ),
        'guestbook' => array(
            DBPREFIX.'module_guestbook',
            DBPREFIX.'module_guestbook_settings',
        ),
        'livecam' => array(
            DBPREFIX.'module_livecam',
            DBPREFIX.'module_livecam_settings',
        ),
        'market' => array(
            DBPREFIX.'module_market',
            DBPREFIX.'module_market_categories',
            DBPREFIX.'module_market_mail',
            DBPREFIX.'module_market_paypal',
            DBPREFIX.'module_market_settings',
            DBPREFIX.'module_market_spez_fields'
        ),
        'memberdir' => array(
            DBPREFIX.'module_memberdir_directories',
            DBPREFIX.'module_memberdir_name',
            DBPREFIX.'module_memberdir_settings',
            DBPREFIX.'module_memberdir_values'
        ),
        'newsletter' => array(
            DBPREFIX.'module_newsletter',
            DBPREFIX.'module_newsletter_access_user',
            DBPREFIX.'module_newsletter_attachment',
            DBPREFIX.'module_newsletter_category',
            DBPREFIX.'module_newsletter_confirm_mail',
            DBPREFIX.'module_newsletter_email_link',
            DBPREFIX.'module_newsletter_email_link_feedback',
            DBPREFIX.'module_newsletter_rel_cat_news',
            DBPREFIX.'module_newsletter_rel_usergroup_newsletter',
            DBPREFIX.'module_newsletter_rel_user_cat',
            DBPREFIX.'module_newsletter_settings',
            DBPREFIX.'module_newsletter_template',
            DBPREFIX.'module_newsletter_tmp_sending',
            DBPREFIX.'module_newsletter_user',
            DBPREFIX.'module_newsletter_user_title',
        ),
        'podcast' => array(
            DBPREFIX.'module_podcast_category',
            DBPREFIX.'module_podcast_medium',
            DBPREFIX.'module_podcast_rel_category_lang',
            DBPREFIX.'module_podcast_rel_medium_category',
            DBPREFIX.'module_podcast_settings',
            DBPREFIX.'module_podcast_template',
        ),
        'shop' => array(
            DBPREFIX.'core_mail_template',
            DBPREFIX.'core_country',
            DBPREFIX.'module_shop_article_group',
            DBPREFIX.'module_shop_attribute',
            DBPREFIX.'module_shop_categories',
            DBPREFIX.'module_shop_countries',
            DBPREFIX.'module_shop_currencies',
            DBPREFIX.'module_shop_customer_group',
            DBPREFIX.'module_shop_discountgroup_count_name',
            DBPREFIX.'module_shop_discountgroup_count_rate',
            DBPREFIX.'module_shop_discount_coupon',
            DBPREFIX.'module_shop_importimg',
            DBPREFIX.'module_shop_lsv',
            DBPREFIX.'module_shop_mail',
            DBPREFIX.'module_shop_mail_content',
            DBPREFIX.'module_shop_manufacturer',
            DBPREFIX.'module_shop_option',
            DBPREFIX.'module_shop_orders',
            DBPREFIX.'module_shop_order_attributes',
            DBPREFIX.'module_shop_order_items',
            DBPREFIX.'module_shop_payment',
            DBPREFIX.'module_shop_payment_processors',
            DBPREFIX.'module_shop_pricelists',
            DBPREFIX.'module_shop_products',
            DBPREFIX.'module_shop_products_downloads',
            DBPREFIX.'module_shop_rel_countries',
            DBPREFIX.'module_shop_rel_customer_coupon',
            DBPREFIX.'module_shop_rel_discount_group',
            DBPREFIX.'module_shop_rel_payment',
            DBPREFIX.'module_shop_rel_product_attribute',
            DBPREFIX.'module_shop_rel_shipper',
            DBPREFIX.'module_shop_shipment_cost',
            DBPREFIX.'module_shop_shipper',
            DBPREFIX.'module_shop_vat',
            DBPREFIX.'module_shop_zones',
        ),
        'voting' => array(
            DBPREFIX.'voting_additionaldata',
            DBPREFIX.'voting_email',
            DBPREFIX.'voting_rel_email_system',
            DBPREFIX.'voting_results',
            DBPREFIX.'voting_system',
        ),
        'downloads' => array(
            DBPREFIX.'module_downloads_category',
            DBPREFIX.'module_downloads_category_locale',
            DBPREFIX.'module_downloads_download',
            DBPREFIX.'module_downloads_download_locale',
            DBPREFIX.'module_downloads_group',
            DBPREFIX.'module_downloads_group_locale',
            DBPREFIX.'module_downloads_rel_download_category',
            DBPREFIX.'module_downloads_rel_download_download',
            DBPREFIX.'module_downloads_rel_group_category',
            DBPREFIX.'module_downloads_settings',
        ),
        'ecard' => array(
            DBPREFIX.'module_ecard_ecards',
            DBPREFIX.'module_ecard_settings',
        ),
        'jobs' => array(
            DBPREFIX.'module_jobs',
            DBPREFIX.'module_jobs_categories',
            DBPREFIX.'module_jobs_location',
            DBPREFIX.'module_jobs_rel_loc_jobs',
            DBPREFIX.'module_jobs_settings',
        ),
        'knowledge' => array(
            DBPREFIX.'module_knowledge_articles',
            DBPREFIX.'module_knowledge_article_content',
            DBPREFIX.'module_knowledge_categories',
            DBPREFIX.'module_knowledge_categories_content',
            DBPREFIX.'module_knowledge_settings',
            DBPREFIX.'module_knowledge_tags',
            DBPREFIX.'module_knowledge_tags_articles',
        ),
        'mediadir' => array(
            DBPREFIX.'module_mediadir_categories',
            DBPREFIX.'module_mediadir_categories_names',
            DBPREFIX.'module_mediadir_comments',
            DBPREFIX.'module_mediadir_entries',
            DBPREFIX.'module_mediadir_forms',
            DBPREFIX.'module_mediadir_form_names',
            DBPREFIX.'module_mediadir_inputfields',
            DBPREFIX.'module_mediadir_inputfield_names',
            DBPREFIX.'module_mediadir_inputfield_types',
            DBPREFIX.'module_mediadir_inputfield_verifications',
            DBPREFIX.'module_mediadir_levels',
            DBPREFIX.'module_mediadir_level_names',
            DBPREFIX.'module_mediadir_mails',
            DBPREFIX.'module_mediadir_mail_actions',
            DBPREFIX.'module_mediadir_masks',
            DBPREFIX.'module_mediadir_order_rel_forms_selectors',
            DBPREFIX.'module_mediadir_rel_entry_categories',
            DBPREFIX.'module_mediadir_rel_entry_inputfields',
            DBPREFIX.'module_mediadir_rel_entry_inputfields_clean1',
            DBPREFIX.'module_mediadir_rel_entry_levels',
            DBPREFIX.'module_mediadir_settings',
            DBPREFIX.'module_mediadir_settings_num_categories',
            DBPREFIX.'module_mediadir_settings_num_entries',
            DBPREFIX.'module_mediadir_settings_num_levels',
            DBPREFIX.'module_mediadir_settings_perm_group_forms',
            DBPREFIX.'module_mediadir_votes'
        ),
    );

    $conflictedModules = array();
    foreach ($missedModules as $module) {
        foreach ($potentialMissedTables[$module] as $table) {
            if (\Cx\Lib\UpdateUtil::table_exist($table)) {
                $result = \Cx\Lib\UpdateUtil::sql('SHOW TABLE STATUS WHERE `Name` = "'.$table.'"');
                if ($result && ($result->RecordCount() > 0) && (strpos($result->fields['Comment'], 'cx3upgrade') === false)) {
                    $conflictedModules[$module][] = $table;
                }
            }
        }
    }

    return $conflictedModules;
}

function _updateModuleRepository() {
    global $_CORELANG, $objUpdate, $objDatabase;

    $count = 0;

    $dh = opendir(dirname(__FILE__) . '/components/core');
    if ($dh) {

        $query = "TRUNCATE TABLE ".DBPREFIX."module_repository";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }

        try {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_repository',
                array(
                    'id'                 => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true),
                    'moduleid'           => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'id'),
                    'content'            => array('type' => 'mediumtext', 'after' => 'moduleid'),
                    'title'              => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'content'),
                    'cmd'                => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'title'),
                    'expertmode'         => array('type' => 'SET(\'y\',\'n\')', 'notnull' => true, 'default' => 'n', 'after' => 'cmd'),
                    'parid'              => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'expertmode'),
                    'displaystatus'      => array('type' => 'SET(\'on\',\'off\')', 'notnull' => true, 'default' => 'on', 'after' => 'parid'),
                    'username'           => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => '', 'after' => 'displaystatus'),
                    'displayorder'       => array('type' => 'SMALLINT(6)', 'notnull' => true, 'default' => '100', 'after' => 'username')
                ),
                array(
                    'contentid'          => array('fields' => array('id'), 'type' => 'UNIQUE'),
                    'fulltextindex'      => array('fields' => array('title','content'), 'type' => 'FULLTEXT')
                )
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }

        while (($file = readdir($dh)) !== false) {
            if (preg_match('#^repository_([0-9]+)\.php$#', $file, $arrFunction)) {
                if (!in_array($file, $_SESSION['contrexx_update']['update']['done'])) {
                    if (function_exists('memory_get_usage')) {
                        if (!checkMemoryLimit()) {
                            return false;
                        }
                    } else {
                        $count++;
                    }

                    if (!include_once(dirname(__FILE__) . '/components/core/' . $file)) {
                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/' . $file));
                        return false;
                    }
                    $function = '_updateModuleRepository_' . $arrFunction[1];
                    if (function_exists($function)) {
                        DBG::msg("---------------------- update: calling $function() ---------");
                        $result = $function();
                        if ($result === false) {
                            if (empty($objUpdate->arrStatusMsg['title'])) {
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                            }
                            return false;
                        }
                    } else {
                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY'], $arrFunction[1]));
                        return false;
                    }

                    $_SESSION['contrexx_update']['update']['done'][] = $file;

                    if ($count == 10) {
                        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
                        setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_RAM_MSG'] . '<br /><br />', 'msg');
                        setUpdateMsg('<input type="submit" value="' . $_CORELANG['TXT_CONTINUE_UPDATE'] . '" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
                        return false;
                    }
                }
            }
        }
    } else {
        setUpdateMsg($_CORELANG['TXT_UPDATE_UNABLE_LOAD_REPOSITORY_PARTS']);
        return false;
    }

    closedir($dh);

    return true;
}

function _updateModulePages() {
    global $objUpdate, $_CONFIG, $objDatabase;
    
    $updateTable = array(
        'newsletter'    => '3.0.1.0', // E-Mail Marketing
        'shop'          => '3.0.0.0', // Online Shop
        'voting'        => '2.1.0.0', // Umfragen
        'access'        => '2.0.0.0', // Benutzerverwaltung
        'podcast'       => '2.0.0.0', // Podcast
        'login'         => '3.0.2.0', // Login
        'gallery'       => '3.0.2.0', // Bildgalerie
    );
    
    foreach ($updateTable as $module=>$version) {
        // only update templates if the installed version is older than $version
        if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], $version)) {
            continue;
        }
        $em = \Env::get('em');
        $pageRepo = $em->getRepository('\Cx\Model\ContentManager\Page');
        $pages = $pageRepo->findBy(array(
            'module' => $module,
            'type'   => \Cx\Model\ContentManager\Page::TYPE_APPLICATION,
        ));
        $objResult = $objDatabase->Execute('
            SELECT
                `id`
            FROM
                ' . DBPREFIX . 'modules
            WHERE
                `name` LIKE \'' . $module . '\'
        ');
        if ($objResult) {
            if (!$objResult->EOF) {
                $moduleId = $objResult->fields['id'];
            }
        } else {
            return false;
        }
        foreach ($pages as $page) {
            $query = '
                SELECT
                    `content`
                FROM
                    ' . DBPREFIX . 'module_repository
                WHERE
                    `moduleid` = ' . $moduleId . ' AND
                    `cmd` LIKE \'' . $page->getCmd() . '\'
            ';
            $objResult = $objDatabase->Execute($query);
            if (!$objResult || $objResult->EOF) {
                return false;
            }
            $page->setContent($objResult->fields['content']);
            $em->persist($page);
            $em->flush();
        }
    }
    return true;
}

function loadMd5SumOfOriginalCxFiles()
{
    global $_CORELANG, $_CONFIG, $arrMd5SumsOfCxFiles, $objUpdate;

    if (!isset($_SESSION['contrexx_update']['skipIntegrityCheck'])) {
        $_SESSION['contrexx_update']['skipIntegrityCheck'] = false;
    }

    // skip integrity check for version 3 or newer
    if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        $_SESSION['contrexx_update']['skipIntegrityCheck'] = true;
        return true;
    }

    $md5File = UPDATE_PATH . '/md5sums/'.$_CONFIG['coreCmsVersion'].'.md5';
    if (!file_exists($md5File)) {
        if (!empty($_POST['skipIntegrityCheck'])) {
            $_SESSION['contrexx_update']['skipIntegrityCheck'] = true;
        }
        if ($_SESSION['contrexx_update']['skipIntegrityCheck']) {
            return true;
        }

        setUpdateMsg('Integritätsprüfung fehlgeschlagen', 'title');
        setUpdateMsg('Die Integritätsprüfung konnte nicht durchgeführt werden, da die installierte Version ('.$_CONFIG['coreCmsVersion'].') unbekannt ist.', 'error');
        setUpdateMsg('Ohne Integritätsprüfung kann das Update System nicht feststellen, ob Ihre Website Erweiterungen zum Standardfunktionsumfang enthält (modifizierte Dateien).', 'msg');
        setUpdateMsg('Es wird empfohlen sich an den <a href="http://www.contrexx.com/support" title="Herstellersupport" target="_blank">Herstellersupport</a> oder Ihrem <i>Contrexx Solution Partner</i> zu wenden, um die Integritätsprüfung von einem Spezialisten vornehmen zu lassen.<br />', 'msg');
        setUpdateMsg('Der Updatevorgang kann ohne Integritätsprüfung fortgesetzt werden, dadurch gehen aber allfällige Erweiterungen zum Standardfunktionsumfang unwiderruflich verloren!', 'msg');
        setUpdateMsg('<strong>Es wird nicht empfohlen den Updatevorgang ohne Integritätsprüfung fortzufahren!</strong><br />', 'msg');
        setUpdateMsg('<input type="checkbox" name="skipIntegrityCheck" id="skipIntegrityCheck" value="1" style="float:left;margin-top:3px;" /><label for="skipIntegrityCheck" style="float:left;width:490px;">Ich bin mir den Konsequenzen bewusst und möchte das Update trotzdem ohne Integritätsprüfung fortfahren.</label><br />', 'msg');

        setUpdateMsg('<input type="submit" value="' . $_CORELANG['TXT_CONTINUE_UPDATE'] . '" name="updateNext" />', 'button');

        return false;
    }

    $arrMd5SumsOfCxFiles = array();
    $list = file($md5File);
    foreach ($list as $entry) {
        list($file, $md5sum) = explode('|', trim($entry));
        if (!isset($arrMd5SumsOfCxFiles[$file])) {
            $arrMd5SumsOfCxFiles[$file] = array();
        }
        $arrMd5SumsOfCxFiles[$file][] = $md5sum;
    }

    return true;
}

function backupModifiedFile($file)
{
    $cxFilePath = dirname(substr($file, strlen(ASCMS_DOCUMENT_ROOT)));
    $customizingPath = ASCMS_DOCUMENT_ROOT.'/customizing'.$cxFilePath;
    \Cx\Lib\FileSystem\FileSystem::make_folder($customizingPath);
    $customizingFile = $customizingPath . '/'. basename($file);

    if (file_exists($customizingFile)) {
        $customizingFile .= '_backup_'.date('d.m.Y');
        $suffix = '';
        $idx = 0;
        while (file_exists($customizingFile.$suffix)) {
            $idx++;
            $suffix = '_'.$idx;
        }
    
        $customizingFile .= $suffix;
    }

    try {
        $objFile = new \Cx\Lib\FileSystem\File($file);
        $objFile->copy($customizingFile);
        $_SESSION['contrexx_update']['modified_files'][] = array(
            'src'   => $cxFilePath . '/' . basename($file),
            'dst'   => substr($customizingFile, strlen(ASCMS_DOCUMENT_ROOT)),
        );
    } catch (\Exception $e) {
        setUpdateMsg('Folgende Datei konnte nicht installiert werden:<br />' . $dstPath);
        setUpdateMsg('Fehler: ' . $e->getMessage());
        setUpdateMsg('<br />Häufigste Ursache dieses Problems ist, dass zur Ausführung dieses Vorgangs die benötigten Schreibrechte nicht vorhanden sind. Prüfen Sie daher, ob die FTP-Konfiguration in der Datei <strong>config/configuration.php</strong> korrekt eingerichtet ist.');
        return false;
    }

    return true;
}


function verifyMd5SumOfFile($file, $newFile)
{
    global $arrMd5SumsOfCxFiles;

    // user wants to skip integrity check
    if ($_SESSION['contrexx_update']['skipIntegrityCheck']) {
        return true;
    }

    if (!file_exists($file)) {
        return true;
    }

    $md5 = md5_file($file);
    $cxFilePath = substr($file, strlen(ASCMS_DOCUMENT_ROOT.'/'));

    // file did not exist in old version,
    // therefore, a check would be non-sense
    if (!isset($arrMd5SumsOfCxFiles[$cxFilePath])) {
        return true;
    }

    foreach ($arrMd5SumsOfCxFiles[$cxFilePath] as $validMd5Sum) {
        if ($md5 == $validMd5Sum) {
            return true;
        }
    }

    $md5SumOfNewFile = md5_file($newFile);
    if ($md5 == $md5SumOfNewFile) {
        return true;
    }

    return false;
}

function copyCxFilesToRoot($src, $dst)
{
    static $copiedCxFilesIndex = 0;

    $src = str_replace('\\', '/', $src);
    $dst = str_replace('\\', '/', $dst);
    $dir = opendir($src);

    $arrCurrentFolderStructure = array();
    while ($file = readdir($dir)) {
        if (!in_array($file, array('.', '..'))) {
            $arrCurrentFolderStructure[] = $file;
        }
    }
    sort($arrCurrentFolderStructure);

    if (!isset($_SESSION['contrexx_update']['copiedCxFilesTotal'])) {
        $_SESSION['contrexx_update']['copiedCxFilesTotal'] = 0;
    }

    foreach ($arrCurrentFolderStructure as $file) {
        if (!checkMemoryLimit() || !checkTimeoutLimit()) {
            $_SESSION['contrexx_update']['copiedCxFilesIndex'] = $copiedCxFilesIndex;
            return 'timeout';
        }

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        if (is_dir($srcPath)) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($dstPath);
            if (!copyCxFilesToRoot($srcPath, $dstPath)) {
                return false;
            }
        } else {
            $copiedCxFilesIndex++;

            if (isset($_SESSION['contrexx_update']['copiedCxFilesIndex']) && $copiedCxFilesIndex <= $_SESSION['contrexx_update']['copiedCxFilesIndex']) {
                continue;
            }

            $_SESSION['contrexx_update']['copiedCxFilesTotal']++;

            try {
                if (!verifyMd5SumOfFile($dstPath, $srcPath)) {
                    if (!backupModifiedFile($dstPath)) {
                        return false;
                    }
                }

                $objFile = new \Cx\Lib\FileSystem\File($srcPath);
                $objFile->copy($dstPath, true);
            } catch (\Exception $e) {
                $copiedCxFilesIndex--;
                $_SESSION['contrexx_update']['copiedCxFilesIndex'] = $copiedCxFilesIndex;
                $_SESSION['contrexx_update']['copiedCxFilesTotal']--;
                setUpdateMsg('Folgende Datei konnte nicht installiert werden:<br />' . $dstPath);
                setUpdateMsg('Fehler: ' . $e->getMessage());
                setUpdateMsg('<br />Häufigste Ursache dieses Problems ist, dass zur Ausführung dieses Vorgangs die benötigten Schreibrechte nicht vorhanden sind. Prüfen Sie daher, ob die FTP-Konfiguration in der Datei <strong>config/configuration.php</strong> korrekt eingerichtet ist.');
                return false;
            }
        }
    }

    closedir($dir);
    return true;
}

function createHtAccess()
{
    if (empty($_SESSION['contrexx_update']['htaccess_file_created'])) {
        $webServerSoftware = !empty($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'apache') ? 'apache' : (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') ? 'iis' : '');
        $cl = Env::get('ClassLoader');

        if ($webServerSoftware == 'iis') {
            $cl->loadFile(UPDATE_LIB . '/PEAR/File/HtAccess.php');
            $objHtAccess = new File_HtAccess(ASCMS_DOCUMENT_ROOT . '/web.config');
            $objHtAccess->setAdditional(explode("\n", @file_get_contents(dirname(__FILE__) . '/data/iis_htaccess.tpl')));
            $result = $objHtAccess->save();
            if ($result !== true) {
                return false;
            }
        } else {
            $cl->loadFile(UPDATE_LIB . '/FRAMEWORK/FWHtAccess.class.php');
            $objFWHtAccess = new FWHtAccess(ASCMS_DOCUMENT_ROOT, ASCMS_PATH_OFFSET);
            $result = $objFWHtAccess->loadHtAccessFile('/.htaccess');
            if ($result !== true) {
                return false;
            }
            $htAccessTemplate = getHtAccessTemplate();
            $pathOffset = ASCMS_PATH_OFFSET;
            if (empty($pathOffset)) $pathOffset = '/';
            $htAccessTemplate = str_replace('%PATH_ROOT_OFFSET%', $pathOffset, $htAccessTemplate);
            $objFWHtAccess->setSection('core_routing', explode("\n", $htAccessTemplate));
            $objFWHtAccess->removeSection('core_modules__alias');
            $objFWHtAccess->removeSection('core__language');
            $result = $objFWHtAccess->write();
            if ($result !== true) {
                return false;
            }
        }

        $_SESSION['contrexx_update']['htaccess_file_created'] = true;
    }

    return true;
}

function getHtAccessTemplate()
{
    $htAccessTemplate = @file_get_contents(dirname(__FILE__) . '/data/apache_htaccess.tpl');
    $htAccessPath     = ASCMS_DOCUMENT_ROOT . '/.htaccess';

    if (file_exists($htAccessPath)) {
        $htAccess = @file_get_contents($htAccessPath);
        if (preg_match('/^(\s*)#?RewriteRule\s+\^\(\\\\w\\\\w\\\\\/\)\?\([^\)]*\)\\\\\\/\$\s+\$2\s+\[[^\]]+\]$/m', $htAccess, $matches)) {
            $search  = '#RewriteRule  ^(\w\w\/)?(_meta|admin|cache|cadmin|changelog|config|core|core_modules|customizing|feed|images|installer|lang|lib|media|model|modules|testing|themes|tmp|update|webcam|favicon.ico)\/$ $2 [L,QSA]';
            $replace = str_replace($matches[1], '', $matches[0]);
            $htAccessTemplate = str_replace($search, $replace, $htAccessTemplate);
        }
        if (preg_match('/^(\s*)RewriteRule\s+\^\(\\\\w\\\\w\\\\\/\)\?\([^\)]*\)\(\\\\\/\|\$\)\(\.\*\)\s+\$2\$3\$4\s+\[[^\]]+\]$/m', $htAccess, $matches)) {
            $search  = 'RewriteRule  ^(\w\w\/)?(_meta|admin|cache|cadmin|changelog|config|core|core_modules|customizing|feed|images|installer|lang|lib|media|model|modules|testing|themes|tmp|update|webcam|favicon.ico)(\/|$)(.*) $2$3$4 [L,QSA]';
            $replace = str_replace($matches[1], '', $matches[0]);
            $htAccessTemplate = str_replace($search, $replace, $htAccessTemplate);
        }
    }

    return $htAccessTemplate;
}

class License {

    public function __construct() {

    }

    public function update() {
        global $documentRoot, $sessionObj, $_CONFIG;

        if (@include_once(ASCMS_DOCUMENT_ROOT.'/lib/PEAR/HTTP/Request2.php')) {
            $_GET['force'] = 'true';
            $_GET['silent'] = 'true';
            $documentRoot = ASCMS_DOCUMENT_ROOT;

            $userId = 1;
            $sessionObj->cmsSessionUserUpdate($userId);

            $_CONFIG['licenseUpdateInterval'] = 0;
            $_CONFIG['licenseSuccessfulUpdate'] = 0;
            $_CONFIG['installationId'] = '';
            $_CONFIG['licenseKey'] = '';
            $_CONFIG['licenseState'] = '';

            $return = @include_once(ASCMS_DOCUMENT_ROOT.'/core_modules/License/versioncheck.php');
        }

        // we force a version number update. if the license update failed
        // version number will not be upgraded yet:
        \Cx\Lib\UpdateUtil::sql('UPDATE `' . DBPREFIX . 'settings` SET `setvalue` = \'' . $_CONFIG['coreCmsVersion'] . '\' WHERE `setid` = 97');
        $settingsManager = new \settingsManager();
        $settingsManager->writeSettingsFile();

        return ($return === true);
    }
}
