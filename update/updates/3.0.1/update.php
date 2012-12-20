<?php

require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileInterface.interface.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/File.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystem.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystemFile.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FTPFile.class.php';

function executeContrexxUpdate($updateRepository = true, $updateBackendAreas = true, $updateModules = true) {
    global $_ARRAYLANG, $_CORELANG, $_CONFIG, $objDatabase, $objUpdate;

    $_SESSION['copyFilesFinished'] = !empty($_SESSION['copyFilesFinished']) ? $_SESSION['copyFilesFinished'] : false;
    
    // Copy cx files to the root directory
    if (!$_SESSION['copyFilesFinished']) {
        if (!copyCxFilesToRoot(dirname(__FILE__) . '/cx_files', ASCMS_PATH . ASCMS_PATH_OFFSET)) {
            return false;
        }
        $_SESSION['copyFilesFinished'] = true;
    }
    unset($_SESSION['copiedCxFilesIndex']);
    
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
    
    $objFWUser = FWUser::getFWUserObject();
    $userData = array(
        'id'   => $objFWUser->objUser->getId(),
        'name' => $objFWUser->objUser->getUsername(),
    );
    $loggableListener = \Env::get('loggableListener');
    $loggableListener->setUsername(json_encode($userData));
    
    // Reinitialize FWLanguage. Now with fallback (doctrine).
    FWLanguage::init();
    
    if ($_CONFIG['coreCmsVersion'] < 3) {
        Env::get('ClassLoader')->loadFile(dirname(__FILE__) . '/ContentMigration.class.php');
        $contentMigration = new \Cx\Update\Cx_3_0_1\ContentMigration();
        
        // Migrate content
        if (empty($_SESSION['contrexx_update']['content_migrated']) && $contentMigration->migrate()) {
            $_SESSION['contrexx_update']['content_migrated'] = true;
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                return false;
            }
        }
        
        // Migrate aliases
        if (empty($_SESSION['contrexx_update']['aliases_migrated']) && $contentMigration->migrateAliases()) {
            $_SESSION['contrexx_update']['aliases_migrated'] = true;
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                return false;
            }
        }
        
        // Group pages if more than one language
        if (empty($_SESSION['contrexx_update']['pages_grouped'])) {
            $pageGrouping = $contentMigration->pageGrouping();
            
            if ($pageGrouping === true) {
                $_SESSION['contrexx_update']['pages_grouped'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            } else if (!empty($pageGrouping)) {
                $arrDialogData = array(
                    'langs'        => $contentMigration->langs,
                    'similarPages' => $contentMigration->similarPages,
                    'defaultLang'  => $contentMigration::$defaultLang,
                );
                
                setUpdateMsg('Inhaltsseiten gruppieren', 'title');
                setUpdateMsg($pageGrouping, 'msg');
                setUpdateMsg('<input type="submit" value="' . $_CORELANG['TXT_UPDATE_NEXT'] . '" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
                setUpdateMsg($arrDialogData, 'dialog');
                return false;
            }
        }
    }
    
    $arrDirs = array('core_module', 'module');
    $updateStatus = true;

    if (!@include_once(dirname(__FILE__) . '/components/core/core.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/core.php'));
        return false;
    } elseif (UPDATE_UTF8 && !@include_once(dirname(__FILE__) . '/components/core/utf8.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/utf8.php'));
        return false;
    } elseif (!@include_once(dirname(__FILE__) . '/components/core/backendAreas.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/backendAreas.php'));
        return false;
    } elseif (!@include_once(dirname(__FILE__) . '/components/core/modules.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/modules.php'));
        return false;
    } elseif (!@include_once(dirname(__FILE__) . '/components/core/settings.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/settings.php'));
        return false;
    } elseif (!@include_once(dirname(__FILE__) . '/components/core/License.class.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/license.class.php'));
        return false;
    }
    
    if (!isset($_SESSION['contrexx_update']['update']['done'])) {
        $_SESSION['contrexx_update']['update']['done'] = array();
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

                        if (!@include_once(dirname(__FILE__).'/components/'.$dir.'/'.$file)) {
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
            $_SESSION['contrexx_update']['update']['done'][] = 'coreSettings';
        }
    }

    if (!in_array('coreModules', $_SESSION['contrexx_update']['update']['done'])) {
        if ($updateModules) {
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
    }

    if (!in_array('coreBackendAreas', $_SESSION['contrexx_update']['update']['done'])) {
        if ($updateBackendAreas) {
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
    }

    if (!in_array('coreModuleRepository', $_SESSION['contrexx_update']['update']['done'])) {
        if ($updateRepository) {
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
    }
    
    if (!in_array('coreLicense', $_SESSION['contrexx_update']['update']['done'])) {
        $lupd = new \Cx\Update\Cx_3_0_1\Core\License();
        $result = $lupd->update();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_LICENSE_DATA']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreLicense';
        }
    }
    
    _response();

    return true;
}

function _response() {
    global $_ARRAYLANG;
    setUpdateMsg($_ARRAYLANG['TXT_README_MSG'], 'msg');
}

function getMissedModules() {
    $installedModules = array();
    $result = \Cx\Lib\UpdateUtil::sql('SELECT `name`, `description_variable` FROM `'.DBPREFIX.'modules` WHERE `status` = "y" ORDER BY `name` ASC');
    if ($result && ($result > 0)) {
        while (!$result->EOF) {
            $installedModules[] = $result->fields['name'];
            $result->MoveNext();
        }
    }

    $missedModules = array();
    $potentialMissedModules = array('blog', 'calendar', 'directory', 'docsys', 'egov', 'feed', 'forum', 'gallery', 'guestbook', 'livecam', 'market', 'memberdir', 'newsletter', 'podcast', 'shop', 'voting');
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
    );

    $conflictedModules = array();
    foreach ($missedModules as $module) {
        foreach ($potentialMissedTables[$module] as $table) {
            if (\Cx\Lib\UpdateUtil::table_exist($table)) {
                $conflictedModules[$module][] = $table;
            }
        }
    }

    return $conflictedModules;
}

function _updateModuleRepository() {
    global $_CORELANG, $objUpdate;

    $count = 0;

    $dh = opendir(dirname(__FILE__) . '/components/core');
    if ($dh) {
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

                    if (!@include_once(dirname(__FILE__) . '/components/core/' . $file)) {
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

function copyCxFilesToRoot($src, $dst) {
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

    foreach ($arrCurrentFolderStructure as $file) {
        if (!checkMemoryLimit() || !checkTimeoutLimit()) {
            $_SESSION['copiedCxFilesIndex'] = $copiedCxFilesIndex;
            return false;
        }

        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;

        if (is_dir($srcPath)) {
            if (!copyCxFilesToRoot($srcPath, $dstPath)) {
                return false;
            }
        } else {
            $copiedCxFilesIndex++;

            if (isset($_SESSION['copiedCxFilesIndex']) && $copiedCxFilesIndex <= $_SESSION['copiedCxFilesIndex']) {
                continue;
            }

            try {
                $objFile = new \Cx\Lib\FileSystem\File($srcPath);
                $objFile->copy($dstPath, true);
            } catch (\Exception $e) {
                $_SESSION['copiedCxFilesIndex'] = $copiedCxFilesIndex;
                \DBG::msg('Copy cx files to root: ' . $e->getMessage());
                return false;
            }
        }
    }

    closedir($dir);
    return true;
}
