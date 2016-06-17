<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */


require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileInterface.interface.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/File.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystem.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FileSystemFile.class.php';
require_once UPDATE_PATH . '/lib/FRAMEWORK/FileSystem/FTPFile.class.php';

function executeContrexxUpdate() {
    global $_CORELANG, $_CONFIG, $objDatabase, $objUpdate, $_DBCONFIG;

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** UPDATE SYSTEM INITIALIZATION - PHASE 1 *******************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    
    /**
     * These are the modules which MUST have new template in order for Cloudrexx
     * to work correctly. CSS definitions for these modules will get updated too.
     */
    $viewUpdateTable = array(
        // E-Mail Marketing
        'newsletter'    => array (
            'version'       => '3.1.0.0',
            'dependencies'    => array (
                'forms',
            ),
        ),

        // Veranstaltungskalender
        'calendar'    => array (
            'version'       => '3.1.0.0',
            'dependencies'  => array (),
        ),

        // Online Shop
        'shop'          => array (
            'version'       => '3.0.0.0',
            'dependencies'  => array (
                'forms',
            ),
        ),

        // Umfragen
        'voting'        => array (
            'version'       => '2.1.0.0',
            'dependencies'  => array (),
        ),

        // Benutzerverwaltung
        'access'        => array (
            'version'       => '2.0.0.0',
            'dependencies'  => array (
                'forms',
                'captcha',
                'uploader',
            ),
        ),

        // Podcast
        'podcast'       => array (
            'version'       => '2.0.0.0',
            'dependencies'  => array (),
        ),

        // Login
        'login'         => array (
            'version'       => '3.0.2.0',
            'dependencies'  => array (
                'forms',
                'captcha',
            ),
        ),

        // Media archives
        'media1'        => array(
            'version'       => '3.0.0.0',
            'dependencies'  => array (),
        ),
        'media2'        => array(
            'version'       => '3.0.0.0',
            'dependencies'  => array (),
        ),
        'media3'        => array(
            'version'       => '3.0.0.0',
            'dependencies'  => array (),
        ),
        'media4'        => array(
            'version'       => '3.0.0.0',
            'dependencies'  => array (),
        ),
    );
    
    $_SESSION['contrexx_update']['copyFilesFinished'] = !empty($_SESSION['contrexx_update']['copyFilesFinished']) ? $_SESSION['contrexx_update']['copyFilesFinished'] : false;

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 1 - INSTALL NEW PHP CODE BASE **********************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    // Copy cx files to the root directory
    if (!$_SESSION['contrexx_update']['copyFilesFinished']) {
        if (!loadMd5SumOfOriginalCxFiles()) {
            return false;
        }

        // backup and remove old component directories
        $backupAndRemoved = backupAndRemove(ASCMS_DOCUMENT_ROOT, array('core', 'core_modules', 'modules'));
        if ($backupAndRemoved !== true) {
            if ($backupAndRemoved === 'timeout') {
                setUpdateMsg(1, 'timeout');
            }
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

        // log modified files
        DBG::msg('MODIFIED FILES:');
        if (isset($_SESSION['contrexx_update']['modified_files'])) {
            DBG::dump($_SESSION['contrexx_update']['modified_files']);
        }

        // we need to stop the script here to force a reinitialization of the update system
        // this is required so that the new constants from config/set_constants.php are loaded
        //setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED'], 'title');
        //setUpdateMsg($_CORELANG['TXT_UPDATE_PROCESS_HALTED_TIME_MSG'].'<br />', 'msg');
        //setUpdateMsg('Installation der neuen Dateien abgeschlossen.<br /><br />', 'msg');
        //setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
        setUpdateMsg(1, 'timeout');
        return false;
    }
    unset($_SESSION['contrexx_update']['copiedCxFilesIndex']);


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** UPDATE SYSTEM INITIALIZATION - PHASE 2 *******************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    /**
     * This needs to be initialized before loading config/doctrine.php
     * Because we overwrite the Gedmo model (so we need to load our model
     * before doctrine loads the Gedmo one)
     */
    require_once(ASCMS_CORE_PATH . '/ClassLoader/ClassLoader.class.php');
    require_once(dirname(UPDATE_PATH).'/core/Core/Controller/Cx.class.php');
    require_once(dirname(UPDATE_PATH).'/core/Model/Model/Entity/Db.class.php');
    require_once(UPDATE_LIB . '/UpdateCx.class.php');

    $cx = new \UpdateCx();
    \Cx\Core\Core\Controller\Cx::registerInstance($cx);

    Env::set('cx', $cx);
    $cl = new \Cx\Core\ClassLoader\ClassLoader($cx, true);
    Env::set('ClassLoader', $cl);

    FWLanguage::init();

    if (!isset($_SESSION['contrexx_update']['update'])) {
        $_SESSION['contrexx_update']['update'] = array();
    }
    if (!isset($_SESSION['contrexx_update']['update']['done'])) {
        $_SESSION['contrexx_update']['update']['done'] = array();
    }

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 2 - UTF-8 MIGRATION ********************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!include_once(dirname(__FILE__) . '/components/core/core.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/core.php'));
        return false;
    }
    if (!include_once(dirname(__FILE__) . '/components/core/utf8.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/utf8.php'));
        return false;
    }
    if (!in_array('utf8', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        $result = _utf8Update();
        if ($result === 'timeout') {
            setUpdateMsg(1, 'timeout');
            return false;
        } elseif (!$result) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UTF_CONVERSION']), 'title');
            }
            return false;
        }
        if ($result === 'charset_changed') {
            // write new charset/collation definition to config file
            if (!_writeNewConfigurationFile()) {
                return false;
            }
        }

        $_SESSION['contrexx_update']['update']['done'][] = 'utf8';

        // _utf8Update() might have changed the charset/collation and migrated some tables,
        // therefore, we will force a reinitialization of the update system
        // to ensure that all db-connections are using the proper charset/collation
        \DBG::msg('Changed collation to: '.$_DBCONFIG['collation']);
        \DBG::msg('Force reinitialization of update...');
        setUpdateMsg(1, 'timeout');
        return false;
    }

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 3 CREATE POSSIBLY MISSING TABLES *******************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    $possiblyMissingTables = array(
        array (
            'table' => DBPREFIX . 'core_setting',
            'structure' => array(
                'section' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
                'name' => array('type' => 'VARCHAR(255)', 'default' => '', 'primary' => true),
                'group' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
                'type' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => 'text', 'after' => 'group'),
                'value' => array('type' => 'text', 'notnull' => true, 'after' => 'type'),
                'values' => array('type' => 'text', 'notnull' => true, 'after' => 'value'),
                'ord' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'values'),
            ),
            'comment' => 'cx3upgrade',
            'engine' => 'MyISAM',
        ),
        array (
            'table' => DBPREFIX . 'core_country',
            'structure' => array(
                'id' => array('type' => 'INT(10)', 'primary' => true),
                'alpha2' => array('type' => 'CHAR(2)', 'default' => ''),
                'alpha3' => array('type' => 'CHAR(3)', 'default' => ''),
                'ord' => array('type' => 'INT(5)', 'default' => 0),
                'active' => array('type' => 'TINYINT(1)', 'default' => 1),
            ),
            'comment' => 'cx3upgrade',
            'engine' => 'MyISAM'
        ),
        array (
            'table' => DBPREFIX . 'core_text',
            'structure' => array(
                'id' => array('type' => 'INT(10)', 'default' => 0, 'primary' => true),
                'lang_id' => array('type' => 'INT(10)', 'default' => 1, 'primary' => true),
                'section' => array('type' => 'VARCHAR(32)', 'default' => '', 'primary' => true),
                'key' => array('type' => 'VARCHAR(255)', 'default' => '', 'primary' => 32),
                'text' => array('type' => 'TEXT', 'default' => ''),
            ),
            'keys' => array('text' => array('fields' => array('text'), 'type' => 'FULLTEXT')),
            'comment' => 'cx3upgrade',
            'engine' => 'MyISAM',
        )
    );
    foreach($possiblyMissingTables as $possiblyMissingTable) {
        try {
            $engine = 'MyISAM';
            $comment = 'cx3upgrade';
            $constraints = array();
            $keys = array();
            if (isset($possiblyMissingTable['engine'])) {
                $engine = $possiblyMissingTable['engine'];
            }
            if (isset($possiblyMissingTable['comment'])) {
                $comment = $possiblyMissingTable['comment'];
            }
            if (isset($possiblyMissingTable['constraints'])) {
                $constraints = $possiblyMissingTable['constraints'];
            }
            if (isset($possiblyMissingTable['keys'])) {
                $keys = $possiblyMissingTable['keys'];
            }
            \Cx\Lib\UpdateUtil::table(
                $possiblyMissingTable['table'],
                $possiblyMissingTable['structure'],
                $keys,
                $engine,
                $comment,
                $constraints
            );
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 4 - SESSION MIGRATION ******************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!in_array('session', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        $isSessionVariableTableExists = \Cx\Lib\UpdateUtil::table_exist(DBPREFIX.'session_variable');
        if (!$isSessionVariableTableExists || $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '4.0.0')) {
            \DBG::msg('update: migrate session');
            if (!migrateSessionTable()) {
                setUpdateMsg('Error in updating session table', 'error');
                return false;
            }
            setUpdateMsg(1, 'timeout');
            return false;
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** UPDATE SYSTEM INITIALIZATION - PHASE 3 *******************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    // Load Doctrine (this must be done after the UTF-8 Migration, because we'll need $_DBCONFIG['charset'] to be set)
    $incDoctrineStatus = require_once(UPDATE_PATH . '/config/doctrine.php');
    Env::set('incDoctrineStatus', $incDoctrineStatus);

    $userData = array(
        'id'   => $_SESSION['contrexx_update']['user_id'],
        'name' => $_SESSION['contrexx_update']['username'],
    );
    $loggableListener = \Env::get('loggableListener');
    $loggableListener->setUsername(json_encode($userData));

    // load content manager migration script; execution will be manually called later by updateContentManagerDbStructure()
    if (!include_once(dirname(__FILE__) . '/components/core/contentmanager.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/contentmanager.php'));
        return false;
    }
    
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 5 - CONTENT MIGRATION ******************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        //////////////////////////////
        // BEGIN: CONTENT MIGRATION //
        //////////////////////////////
        DBG::msg('Installed version: '.$_CONFIG['coreCmsVersion']);
        Env::get('ClassLoader')->loadFile(dirname(__FILE__) . '/ContentMigration.class.php');
        $contentMigration = new \Cx\Update\Cx_3_0_4\ContentMigration();

        // Migrate statistics - this must be done before migrating to the new content architecture
        if (empty($_SESSION['contrexx_update']['content_stats'])) {
            DBG::msg('Migrate stats');
            if ($contentMigration->migrateStatistics()) {
                $_SESSION['contrexx_update']['content_stats'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    setUpdateMsg(1, 'timeout');
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
        $contentMigration->arrMigrateLangIds = ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['migrate_lang_ids']);
        $contentMigration->migrateLangIds    = implode(',', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['migrate_lang_ids']));

        // Migrate content
        if (empty($_SESSION['contrexx_update']['content_migrated'])) {
            DBG::msg('Migrate content');
            $status = $contentMigration->migrate();

            if ($status === true) {
                $_SESSION['contrexx_update']['content_migrated'] = true;

                // log migrated nodes
                DBG::msg('NODES: catId -> nodeId');
                DBG::dump(ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['nodes']));
                unset($_SESSION['contrexx_update']['nodes']);

                // log migrated pages
                DBG::msg('PAGES: catId -> pageId');
                DBG::dump(ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['pages']));
                unset($_SESSION['contrexx_update']['pages']);

                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    setUpdateMsg(1, 'timeout');
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
                    setUpdateMsg(1, 'timeout');
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
                    setUpdateMsg(1, 'timeout');
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
                    setUpdateMsg(1, 'timeout');
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
                    setUpdateMsg(1, 'timeout');
                    return false;
                }
            }
        }
        ////////////////////////////
        // END: CONTENT MIGRATION //
        ////////////////////////////
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 6 - VERSION 3 FIXES ********************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        if (!in_array('updateContentManagerDbStructure', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
            $result = ContentManagerUpdate::updateContentManagerDbStructure();
            if ($result === false) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'updateContentManagerDbStructure';
        }

        if (!in_array('fixPageLogs', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
            $result = ContentManagerUpdate::fixPageLogs();
            if ($result === false) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'fixPageLogs';
        }

        if (!in_array('fixFallbackPages', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
            $result = ContentManagerUpdate::fixFallbackPages();
            if ($result === false) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'fixFallbackPages';
        }

        if (!in_array('fixTree', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
            $result = ContentManagerUpdate::fixTree();
            if ($result === false) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'fixTree';
        }

        $cx3Version = detectCx3Version();
        if ($cx3Version === false) {
            return false;
        }

        if ($cx3Version !== true) {
            // we are updating from 3.0.0 rc1, rc2, stable or 3.0.0.1
            if (!in_array('update3', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
                if (!include_once(dirname(__FILE__) . '/update3.php')) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], dirname(__FILE__) . '/update3.php'));
                    return false;
                }
                $_SESSION['contrexx_update']['update']['done'][] = 'update3';
            }
        }
    }
        

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 7 - CORE MIGRATION *********************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    // Update languages, access_groups, modules table and so on
    if (!in_array('coreUpdate', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: process _coreUpdate()');
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

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** UPDATE SYSTEM INITIALIZATION - PHASE 4 *******************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    // load backend areas migration script; execution will be manually called later by _updateBackendAreas()
    if (!include_once(dirname(__FILE__) . '/components/core/backendAreas.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/backendAreas.php'));
        return false;
    }

    // load modules migration script; execution will be manually called later by _updateModules()
    if (!include_once(dirname(__FILE__) . '/components/core/modules.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/modules.php'));
        return false;
    }

    // load settings migration script; execution will be manually called later by _updateSettings()
    if (!include_once(dirname(__FILE__) . '/components/core/settings.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/settings.php'));
        return false;
    }

    // load components migration script; execution will be manually called later by _updateComponent()
    if (!include_once(dirname(__FILE__) . '/components/core/componentmanager.php')) {
        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__) . '/components/core/componentmanager.php'));
        return false;
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 8 - COMPONENTS MIGRATION ***************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/

    if (!in_array('migrateComponents', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        // Execute component migration scripts:
        // check for any missed modules
        $missedModules = array();
        if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')) {
            \DBG::msg('update: check for missed and conflicted modules');
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

        $arrDirs = array('core', 'core_module', 'module');
        // migrate the components
        \DBG::msg('update: migrate components');
        $result = _migrateComponents($arrDirs, $objUpdate, $missedModules);
        if ($result === 'timeout') {
            setUpdateMsg(1, 'timeout');
            return false;
        }
        if (!$result) {
            if (empty($objUpdate->arrStatusMsg['msg'])) {
                setUpdateMsg('Die Komponenten konnten nicht migiert werden.', 'msg');
            }
            return false;
        }

        $_SESSION['contrexx_update']['update']['done'][] = 'migrateComponents';
        unset($_SESSION['contrexx_update']['update']['migrateComponentsDone']);
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 9 - SETTINGS MIGRATION *****************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (
        !in_array('coreSettings', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done'])) &&
        $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')
    ) {
        \DBG::msg('update: update settings');
        $result = _updateSettings();
        if ($result === 'timeout') {
            setUpdateMsg(1, 'timeout');
            return false;
        }
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_BASIC_CONFIGURATION']), 'title');
            }
            return false;
        } else {
            // update configuration.php (migrate to new format)
            if (!_writeNewConfigurationFile()) {
                return false;
            }
            $_SESSION['contrexx_update']['update']['done'][] = 'coreSettings';

            if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
                // till this point the file config/version.php was still loaded upon a request,
                // therefore we must force a new page request here, to ensure that the file config/version.php
                // will not be loaded anylonger. This is essential here, otherwise the old values of config/version.php
                // would screw up the update process
                setUpdateMsg(1, 'timeout');
                return false;
            }
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************* STAGE 10 - LOAD NEW MODULE REPOSITORY *********************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!in_array('coreModuleRepository', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: update module repository');
        $result = _updateModuleRepository();
        if ($result === false) {
            DBG::msg('unable to update module repository');
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY']), 'title');
            }
            return false;
        }
        $_SESSION['contrexx_update']['update']['done'][] = 'coreModuleRepository';
        unset($_SESSION['contrexx_update']['update']['coreModuleRepositoryDone']);
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 11 - THEMES MIGRATION ******************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!in_array('convertTemplates', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: convert themes 2 component');
        $result = _convertThemes2Component();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                DBG::msg('unable to convert themes to component');
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_CONVERT_TEMPLATES']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'convertTemplates';
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 12 - UPDATE CONTENT APPLICATION TEMPLATES  *********/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!in_array('moduleTemplates', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: update module pages');
        if (_updateModulePages($viewUpdateTable) === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                DBG::msg('unable to update module templates');
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_TEMPLATES']), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'moduleTemplates';
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 13 - INSTALL NEW APPLICATION TEMPLATES STYLES ******/
    /******************************** (version < 3 only) ***************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        if (!in_array('moduleStyles', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
            \DBG::msg('update: update css definitions');
            if (_updateCssDefinitions($viewUpdateTable, $objUpdate) === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_MODULE_TEMPLATES']), 'title');
                }
                return false;
            } else {
                $_SESSION['contrexx_update']['update']['done'][] = 'moduleStyles';
            }
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 14 - FIX BROKEN NAVIGATIONS ************************/
    /******************************** (version < 3 only) ***************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        if (!in_array('navigations', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
            \DBG::msg('update: update navigations');
            if (_updateNavigations() === false) {
                if (empty($objUpdate->arrStatusMsg['title'])) {
                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_NAVIGATIONS']), 'title');
                }
                return false;
            } else {
                $_SESSION['contrexx_update']['update']['done'][] = 'navigations';
            }
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 15 - DROP /CADMIN/INDEX.PHP ************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // IMPORTANT: This only works as long as the backend areas have not been reloaded
    if (file_exists(ASCMS_DOCUMENT_ROOT.ASCMS_BACKEND_PATH.'/index.php')) {
        \DBG::msg('update: backup customized index.php file -> /<customizing-path>');
        \DBG::msg('/cadmin/index.php still exists...');
        // move cadmin index.php if its customized
        if (!loadMd5SumOfOriginalCxFiles()) {
            return false;
        }
        if (!verifyMd5SumOfFile(ASCMS_DOCUMENT_ROOT.ASCMS_BACKEND_PATH.'/index.php', '', false)) {
            \DBG::msg('...and it\'s customized, so let\'s move it to customizing directory');
            // changes, backup modified file
            if (!backupModifiedFile(ASCMS_DOCUMENT_ROOT.ASCMS_BACKEND_PATH.'/index.php')) {
                setUpdateMsg('Die Datei \''.ASCMS_DOCUMENT_ROOT.ASCMS_BACKEND_PATH.'/index.php\' konnte nicht kopiert werden.');
                return false;
            }
        } else {
            \DBG::msg('...but it\'s not customized');
        }
        // no non-backupped changes, can delete
        try {
            \DBG::msg('So let\'s remove it...');
            $cadminIndex = new \Cx\Lib\FileSystem\File(ASCMS_DOCUMENT_ROOT.ASCMS_BACKEND_PATH.'/index.php');
            $cadminIndex->delete();
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            setUpdateMsg('Die Datei \''.ASCMS_DOCUMENT_ROOT.ASCMS_BACKEND_PATH.'/index.php\' konnte nicht gelöscht werden.');
            return false;
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 16 - LOAD NEW MODULE DB ****************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Update DBPREFIX_modules-table
    if (!in_array('coreModules', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: update modules');
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


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 17 - LOAD NEW BACKEND AREA DB **********************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Update DBPREFIX_backend_areas-table
    if (!in_array('coreBackendAreas', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: update backend areas');
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


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 18 - LOAD NEW COMPONENT DB *************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Update DBPREFIX_component-table
    if (!in_array('coreComponent', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: update component');
        $result = _updateComponent();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_TABLE'], DBPREFIX), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreComponent';
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 19 - MIGRATE PAGE LOGS TO NEW COMPONENT NAMES ******/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Migrate page logs
    if (!in_array('pageLogs', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: migrate page logs');
        $result = _migratePageLogs();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg($_CORELANG['TXT_UPDATE_PAGE_LOG'], 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'pageLogs';
        }
    }

    if (!in_array('pageApplicationNames', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: migrate page application names');
        $result = migratePageApplicationNames();
        if ($result === 'timeout') {
            setUpdateMsg(1, 'timeout');
            return false;
        }
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg('Beim Aktualisieren der Anwendungsseiten ist ein Fehler aufgetreten', 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'pageApplicationNames';
        }
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 20 - MIGRATE MEDIA PATHS ***************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (!in_array('mediaPaths', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: migrate media paths for content and blocks');
        $mediaPathContentDone = _migrateMediaPaths('page');
        $mediaPathTemplateDone = _migrateTemplateMediaPaths();
        if ($mediaPathContentDone === false || $mediaPathBlockDone === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg(
                    sprintf($_CORELANG['TXT_UNABLE_TO_MIGRATE_MEDIA_PATH'], ''), 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'pageLogs';
        }
    }

    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 21 - INSTALL CONTENT APPLICATION TEMPLATES *********/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Migrate page logs
    if (!in_array('applicationTemplates', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        \DBG::msg('update: install content application templates');
        $result = installContentApplicationTemplates();
        if ($result === 'timeout') {
            setUpdateMsg(1, 'timeout');
            return false;
        }
        if ($result === false) {
            return false;
        }

        unset($_SESSION['contrexx_update']['update']['migratedApplicationContentPages']);
        $_SESSION['contrexx_update']['update']['done'][] = 'applicationTemplates';
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** UPDATE SYSTEM INITIALIZATION - PHASE 5 *******************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    $cx->minimalInit();


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 22 - SETTINGS 2 SETTINGDB MIGRATION ****************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    if (
        !in_array('coreSettings2SettingDb', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done'])) &&
        $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '5.0.0')
    ) {
        \DBG::msg('update: migrate settings to \Cx\Core\Setting');
        $result = migrateSettingsToSettingDb();
        if ($result === false) {
            if (empty($objUpdate->arrStatusMsg['title'])) {
                setUpdateMsg('Bei der Migration der Grundeinstellungen trat ein Fehler auf', 'title');
            }
            return false;
        } else {
            $_SESSION['contrexx_update']['update']['done'][] = 'coreSettings2SettingDb';

            // let's force a reload here to ensure any new settings will be loaded
            setUpdateMsg(1, 'timeout');
            return false;
        }
    }



    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 23 - INSTALL NEW .HTACCESS FILE ********************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Update .htaccess
    \DBG::msg('update: create htaccess file');
    if(!in_array('createHtAccess', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done']))) {
        if (!createHtAccess()) {
            $webServerSoftware = !empty($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'apache') ? 'apache' : (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') ? 'iis' : '');
            $file = $webServerSoftware == 'iis' ? 'web.config' : '.htaccess';

            setUpdateMsg('Die Datei \'' . $file . '\' konnte nicht erstellt/aktualisiert werden.');
            return false;
        }

        $_SESSION['contrexx_update']['update']['done'][] = 'createHtAccess';

        // force final reload
        setUpdateMsg(1, 'timeout');
        return false;
    }


    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /******************** STAGE 24 - INSTALL NEW LICENSE ***************************/
    /*******************************************************************************/
    /*******************************************************************************/
    /*******************************************************************************/
    // Update license
    \DBG::msg('update: update license');
    $arrUpdate = $objUpdate->getLoadedVersionInfo();

    if (
        !in_array('coreLicense', ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['done'])) ||
        $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], $arrUpdate['cmsVersion'])
    ) {
        $lupd = new License();
        try {
            $lupd->update();
        } catch (\Cx\Lib\UpdateException $e) {
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $_CORELANG['TXT_UPDATE_LICENSE_DATA']), 'title');
            return false;
        }
        $_SESSION['contrexx_update']['update']['done'][] = 'coreLicense';
    }

    ////////////////
    // END UPDATE //
    ////////////////
    \DBG::msg('update: end of update reached :)');

    return true;
}

function getMissedModules() {
    global $objUpdate, $_CONFIG;
    $installedModules = array();
    $result = \Cx\Lib\UpdateUtil::sql('SELECT `name`, `description_variable` FROM `'.DBPREFIX.'modules` WHERE `status` = "y" ORDER BY `name` ASC');
    if ($result) {
        while (!$result->EOF) {
            $installedModules[] = $result->fields['name'];
            $result->MoveNext();
        }
    }

    // the egov module is installed but not turned to 'y' in update
    if (   $objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.0.3')
        && !$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.0.2')
        && \Cx\Lib\UpdateUtil::table_exist(DBPREFIX . 'module_egov_configuration')) {
        $installedModules[] = 'egov';
    }

    $missedModules = array();
    $potentialMissedModules = array('blog', 'crm', 'calendar', 'directory', 'docsys', 'egov', 'feed', 'forum', 'gallery', 'guestbook', 'livecam', 'market', 'memberdir', 'newsletter', 'podcast', 'shop', 'voting', 'downloads', 'ecard', 'jobs', 'knowledge', 'mediadir');
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
        if (!isset($potentialMissedTables[$module])) {
            continue;
        }
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

        if (!isset($_SESSION['contrexx_update']['update']['coreModuleRepositoryDone'])) {
            $_SESSION['contrexx_update']['update']['coreModuleRepositoryDone'] = array();
        }

        while (($file = readdir($dh)) !== false) {
            if (preg_match('#^repository_([0-9]+)\.php$#', $file, $arrFunction)) {
                if (!in_array($file, ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['coreModuleRepositoryDone']))) {
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
                            DBG::msg("---------------------- update: calling $function() failed ---------");
                            if (empty($objUpdate->arrStatusMsg['title'])) {
                                setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                            }
                            return false;
                        }
                    } else {
                        DBG::msg("---------------------- update: calling $function() failed, function does not exist ---------");
                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], $_CORELANG['TXT_UPDATE_MODULE_REPOSITORY'], $arrFunction[1]));
                        return false;
                    }

                    $_SESSION['contrexx_update']['update']['coreModuleRepositoryDone'][] = $file;

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


/**
 * Update content pages of modules which MUST have new template in order for Cloudrexx
 * to work correctly. CSS definitions for these modules will get updated too.
 * Content is loaded from module repository.
 */
function _updateModulePages(&$viewUpdateTable) {
    global $objUpdate, $_CONFIG, $objDatabase;
    
    foreach ($viewUpdateTable as $module=>$data) {
        $version = $data['version'];
        // only update templates if the installed version is older than $version
        if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], $version)) {
            continue;
        }
        $em = \Env::get('em');
        $pageRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Entity\Page');
        $pages = $pageRepo->findBy(array(
            'module' => $module,
            'type'   => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION,
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
            if (!$objResult) {
                return false;
            }
            if ($objResult->EOF) {
                DBG::msg('unable to load module repository of page with section ' . $module . ' and cmd ' . $page->getCmd());
                continue;
            }
            $page->setContent($objResult->fields['content']);
            $page->setSourceMode(true);
            $page->setUpdatedAtToNow();
            $em->persist($page);
            $em->flush();
        }
    }
    return true;
}

function _updateCssDefinitions(&$viewUpdateTable, $objUpdate) {
    global $objDatabase, $_CORELANG;
    
    // Find all themes
    $result = $objDatabase->Execute('SELECT `themesname`, `foldername` FROM `' . DBPREFIX . 'skins`');
    if ($result->EOF) {
        \DBG::msg('No themes, really?');
        return false;
    }
    
    // Find type for theme and update its CSS definitions
    $errorMessages = '';
    while (!$result->EOF) {
        if (!is_dir(ASCMS_THEMES_PATH . '/' . $result->fields['foldername'])) {
            \DBG::msg('Skipping theme "' . $result->fields['themesname'] . '"; No such folder!');
            $errorMessages .= sprintf($_CORELANG['TXT_CSS_UPDATE_MISSING_FOLDER'], $result->fields['themesname']);
            $result->moveNext();
            continue;
        }
        if (preg_match('/print/', $result->fields['themesname'])) {
            $type = 'print';
        } else if (preg_match('/pdf/', $result->fields['themesname'])) {
            $type = 'pdf';
        } else if (preg_match('/mobile/', $result->fields['themesname'])) {
            $type = 'mobile';
        } else if (preg_match('/app/', $result->fields['themesname'])) {
            $type = 'app';
        } else {
            $type = 'standard';
        }
        \DBG::msg('Updating CSS definitions for theme "' . $result->fields['themesname'] . '" (' . $type . ')');
        if (!_updateCssDefinitionsForTemplate($result->fields['foldername'], $type, $viewUpdateTable, $objUpdate)) {
            \DBG::msg('CSS update for theme "' . $result->fields['themesname'] . '" failed');
            $errorMessages .= sprintf($_CORELANG['TXT_UPDATE_THEME_FAILED'], $result->fields['themesname']);
        }
        $result->moveNext();
    }
    if (!empty($errorMessages)) {
        setUpdateMsg('<div class="message-warning">' . $errorMessages . '</div>', 'msg');
        setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
        $_SESSION['contrexx_update']['update']['done'][] = 'moduleStyles';
        return false;
    }
    return true;
}

function _updateCssDefinitionsForTemplate($templatePath, $templateType, &$viewUpdateTable, $objUpdate) {
    global $objUpdate;
    
    \DBG::msg('Loading new module style definitions');
    $moduleStyles = _readNewCssDefinitions($templateType, $objUpdate->getLoadedVersionInfo());
    
    if ($moduleStyles === false) {
        return false;
    } else if ($moduleStyles === true) {
        // Skip if no source CSS file was found
        return true;
    }
    
    \DBG::msg('Calculating new module style definitions');
    $additionalCss = _calculateNewCss($viewUpdateTable, $moduleStyles, $objUpdate);
    
    if ($additionalCss === false) {
        return false;
    }
    if (empty($additionalCss)) {
        return true;
    }
    $version = $objUpdate->getLoadedVersionInfo();
    $version = $version['cmsVersion'];
    $additionalCss = '/***************************************************/
/* THESE ARE THE CSS MODULE STYLES FOR ' . $version .  '       */
/***************************************************/' . "\r\n\r\n" . $additionalCss;
    \DBG::msg('Writing new module style definitions');
    return _writeNewCss($templatePath, $additionalCss, $objUpdate->getLoadedVersionInfo());
}

/**
 * This reads /updates/{version}/data/modules.css and parses its contents
 * @return mixed Module styles as array({module_name}=>{css}), true if source file was not found or false on error
 */
function _readNewCssDefinitions($templateType, &$arrUpdate) {
    
    // Read and parse new modules.css
    try {
        $modulesCss = new \Cx\Lib\FileSystem\File(UPDATE_PATH.'/updates/' . $arrUpdate['cmsVersion'] . '/data/' . $templateType . '.css');
        $styleDefinitions = $modulesCss->getData();
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        \DBG::msg($e->getMessage());
        return true;
    }
    // split css by module header comment
    $styleDefinitions = preg_split('#(?:[\s]*)/[\*]*/\n(?:[\s]*)/\*\sCSS (GLOBAL\s)?DEFINITIONS FOR#', $styleDefinitions);
    $moduleStyles = array();
    $matches = array();
    $moduleRegex = '#^ ([A-Z]*)\s?(?:[A-Z ]*)(?:[\s]*)\*/\n(?:[\s]*)/[\*]*/#';
    foreach ($styleDefinitions as $key=>$value) {
        // get module name from header
        if (!preg_match($moduleRegex, $value, $matches)) {
            // not a module
            continue;
        }
        // reconstruct header (this could be done more nicely)
        $moduleStyles[strtolower($matches[1])] = '/***************************************************/
/* CSS DEFINITIONS FOR' . $value;
    }
    \DBG::msg('--- loaded modules css definitions for modules: ---');
    \DBG::dump(array_keys($moduleStyles));
    return $moduleStyles;
}

/**
 * Merges CSS definitions of modules with updated template
 * @return mixed Additional CSS definitions as string or false on error
 */
function _calculateNewCss(&$viewUpdateTable, &$moduleStyles, $objUpdate) {
    global $_CONFIG;
    
    // Calculate new CSS definitions
    $additionalCss = array ();
    foreach ($viewUpdateTable as $module=>$data) {
        $version = $data['version'];
        $dependencies = $data['dependencies'];
        // only add css if the installed version is older than $version
        if (!$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], $version)) {
            continue;
        }
        if (!isset($moduleStyles[$module])) {
            \DBG::msg('No style definitions for module "' . $module . '" in this theme type');
            continue;
        }
        if (!isset($additionalCss[$module])) {
            $additionalCss[$module] = $moduleStyles[$module];
        }
        foreach ($dependencies as $module) {
            if (!isset($additionalCss[$module])) {
                $additionalCss[$module] = $moduleStyles[$module];
            }
        }
    }
    \DBG::msg('--- added modules css definitions for modules: ---');
    \DBG::dump(array_keys($additionalCss));
    $additionalCss = implode("\r\n\r\n", $additionalCss);
    return $additionalCss;
}

/**
 * Writes the new additional CSS definitions to FS and adds style definition
 * to theme
 * @return boolean True on success, false otherwise
 */
function _writeNewCss($templatePath, $newCss, &$arrUpdate) {
    
    // Write the CSS first
    $filename = 'modules_' . preg_replace('/\./', '_', $arrUpdate['cmsVersion']) . '.css';
    try {
        $objFile = new \Cx\Lib\FileSystem\File(ASCMS_THEMES_PATH . '/' . $templatePath . '/' . $filename);
        $objFile->write($newCss);
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        \DBG::msg($e->getMessage());
        return false;
    }
    
    // Generate include tag
    $cssInclusion = '<link rel="stylesheet" type="text/css" href="themes/' . $templatePath . '/' . $filename . '" />'."\r\n";
    
    // Read index.html
    try {
        $objFile = new \Cx\Lib\FileSystem\File(ASCMS_THEMES_PATH . '/' . $templatePath . '/index.html');
        $indexHtml = $objFile->getData();
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        \DBG::msg($e->getMessage());
        return false;
    }
    
    // Search write position. CSS inclusion tag is added before
    // style placeholder. If the placeholder is not present in
    // index.html the CSS inclusion tag is inserted before </head>
    // Search for style placeholder ({STYLE_FILE})
    if (($pos = strpos($indexHtml, '{STYLE_FILE}')) === false) {
        $matches = array();
        
        // Search for head end tag
        if (!preg_match('#</head>#', $indexHtml, $matches, PREG_OFFSET_CAPTURE)) {
            \DBG::msg('No style tag or </head> found, skip template');
            return true;
        }
        $pos = $matches[0][1];
    }
    
    // Finally add the include statement before $pos and write out
    $indexHtml = substr_replace($indexHtml, $cssInclusion, $pos, 0);
    try {
        $objFile = new \Cx\Lib\FileSystem\File(ASCMS_THEMES_PATH . '/' . $templatePath . '/index.html');
        $objFile->write($indexHtml);
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        \DBG::msg($e->getMessage());
        return false;
    }

    // Copy files for frontend login page to theme's directory
    $imagesToCopy = array('facebook_login.png', 'google_login.png', 'twitter_login.png');
    try {
        foreach ($imagesToCopy as $imageToCopy) {
            $src = str_replace('\\', '/', UPDATE_PATH.'/updates/' . $arrUpdate['cmsVersion'] . '/data/images/' . $imageToCopy);
            $dst = str_replace('\\', '/', ASCMS_THEMES_PATH . '/' . $templatePath . '/images/' . $imageToCopy);
            if (file_exists($src)) {
                $File = new \Cx\Lib\FileSystem\File($src);
                $File->copy($dst);
            }
        }
    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
        \DBG::msg($e->getMessage());
        return false;
    }

    return true;
}

/**
 * This should only be executed when updating from version 2.2.6 or older
 * Fix for the following tickets:
 * http://bugs.contrexx.com/contrexx/ticket/1412
 * http://bugs.contrexx.com/contrexx/ticket/1043
 * @see http://helpdesk.comvation.com/131276-Die-Navigation-meiner-Seite-wird-nicht-mehr-korrekt-angezeigt
 * 
 * Adds placeholder {LEVELS_FULL} to all non-empty subnavbars
 * Adds placeholder {LEVELS_BRANCH} to all navbars having a block named 'navigation' but none 'level_1'
 */
function _updateNavigations()
{
    global $objDatabase, $_CORELANG;
    
    $navbars = array('navbar', 'navbar2', 'navbar3');
    $subnavbars = array('subnavbar', 'subnavbar2', 'subnavbar3');
    
    // Find all themes
    $result = $objDatabase->Execute('SELECT `themesname`, `foldername` FROM `' . DBPREFIX . 'skins`');
    if ($result->EOF) {
        \DBG::msg('No themes, really?');
        return false;
    }
    
    // Update navigations for all themes
    $errorMessages = '';
    while (!$result->EOF) {
        if (!is_dir(ASCMS_THEMES_PATH . '/' . $result->fields['foldername'])) {
            \DBG::msg('Skipping theme "' . $result->fields['themesname'] . '"; No such folder!');
            $errorMessages .= '<div class="message-warning">' . sprintf($_CORELANG['TXT_CSS_UPDATE_MISSING_FOLDER'], $result->fields['themesname']) . '</div>';
            $result->moveNext();
            continue;
        }
        
        \DBG::msg('Updating navigations for theme "' . $result->fields['themesname'] . '" (' . $type . ')');
        
        // add {LEVELS_FULL} to all non-empty subnavbars
        foreach ($subnavbars as $subnavbar) {
            try {
                $objFile = new \Cx\Lib\FileSystem\File(ASCMS_THEMES_PATH . '/' . $result->fields['foldername'] . '/' . $subnavbar . '.html');
                $content = $objFile->getData();
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
                continue;
            }
            if (trim($content) == '') {
                continue;
            }
            $content = '{LEVELS_FULL}' . "\r\n" . $content;
            try {
                $objFile->write($content);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
                continue;
            }
            \DBG::msg('Updated file ' . $subnavbar . '.html for theme '  . $result->fields['themesname']);
        }

        // add {LEVELS_BRANCH} to all navbars matching the following criterias:
        // 1. blockExists('navigation')
        // 2. !blockExists('level_1')
        foreach ($navbars as $navbar) {
            try {
                $objFile = new \Cx\Lib\FileSystem\File(ASCMS_THEMES_PATH . '/' . $result->fields['foldername'] . '/' . $navbar . '.html');
                $content = $objFile->getData();
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
                continue;
            }
            if (trim($content) == '') {
                continue;
            }
            $template = new \Cx\Core\Html\Sigma('.');
            $template->setTemplate($content);
            if (!$template->blockExists('navigation')) {
                continue;
            }
            if ($template->blockExists('level_1')) {
                continue;
            }
            $content = '{LEVELS_BRANCH}' . "\r\n" . $content;
            try {
                $objFile->write($content);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                \DBG::msg($e->getMessage());
                continue;
            }
            \DBG::msg('Updated file ' . $navbar . '.html for theme '  . $result->fields['themesname']);
        }
        
        $result->moveNext();
    }
    if (!empty($errorMessages)) {
        setUpdateMsg($errorMessages, 'msg');
        setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
        $_SESSION['contrexx_update']['update']['done'][] = 'navigations';
        return false;
    }
    return true;
}

function loadMd5SumOfOriginalCxFiles()
{
    global $_CORELANG, $_CONFIG, $arrMd5SumsOfCxFiles, $objUpdate;

    if (!isset($_SESSION['contrexx_update']['skipIntegrityCheck'])) {
        $_SESSION['contrexx_update']['skipIntegrityCheck'] = false;
    }

    if ($_CONFIG['coreCmsVersion'] == '3.0.0') {
        try {
            $resultRc1 = \Cx\Lib\UpdateUtil::sql('SELECT `target` FROM `'.DBPREFIX.'backend_areas` WHERE `area_id` = 186');
            $resultRc2 = \Cx\Lib\UpdateUtil::sql('SELECT `order_id` FROM `'.DBPREFIX.'backend_areas` WHERE `area_id` = 2');

            if ($resultRc1->fields['target'] != '_blank') {
                $filename = $_CONFIG['coreCmsVersion'].'_RC1.md5';
            } elseif ($resultRc2->fields['order_id'] != 6) {
                $filename = $_CONFIG['coreCmsVersion'].'_RC2.md5';
            }
        } catch (\Cx\Lib\UpdateException $e) {
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    } else {
        $filename = $_CONFIG['coreCmsVersion'].'.md5';
    }

    $md5File = UPDATE_PATH.'/md5sums/'.$filename;

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
        list($file, $md5sum, $rawMd5Sum) = explode('|', trim($entry));
        if (!isset($arrMd5SumsOfCxFiles[$file])) {
            $arrMd5SumsOfCxFiles[$file] = array();
        }
        $arrMd5SumsOfCxFiles[$file][] = array(
            'md5_sum'     => $md5sum,
            'raw_md5_sum' => $rawMd5Sum
        );
    }

    return true;
}

function backupModifiedFile($file)
{
    global $_CONFIG;
    
    $cxFilePath = dirname(substr($file, strlen(ASCMS_DOCUMENT_ROOT)));
    if ($cxFilePath == '/') {
        $cxFilePath = '';
    }

    $customizingPath = ASCMS_DOCUMENT_ROOT.'/customizing'.$cxFilePath;
    \Cx\Lib\FileSystem\FileSystem::make_folder($customizingPath);
    $customizingFile = $customizingPath . '/'. basename($file)."_".$_CONFIG['coreCmsVersion'];

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

    if (!isset($_SESSION['contrexx_update']['modified_files'])) {
        $_SESSION['contrexx_update']['modified_files'] = array();
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


function verifyMd5SumOfFile($file, $newFile, $allowSkip = true)
{
    global $arrMd5SumsOfCxFiles;

    \DBG::msg('Running MD5 comparision for file ' . $file);
    // user wants to skip integrity check
    if ($_SESSION['contrexx_update']['skipIntegrityCheck'] && $allowSkip) {
        \DBG::msg('MD5 comparision skipped');
        return true;
    }

    if (!file_exists($file)) {
        \DBG::msg('MD5 comparision cancelled, file not found (' . $file . ')');
        return true;
    }

    $md5 = md5_file($file);
    $cxFilePath = substr($file, strlen(ASCMS_DOCUMENT_ROOT.'/'));
    \DBG::msg('Cx file path is ' . $cxFilePath);

    // file did not exist in old version,
    // therefore, a check would be non-sense
    if (!isset($arrMd5SumsOfCxFiles[$cxFilePath])) {
        \DBG::msg('MD5 comparision complete, file did not exist in prior versions');
        return true;
    }

    foreach ($arrMd5SumsOfCxFiles[$cxFilePath] as $validMd5Sum) {
        if ($md5 == $validMd5Sum['md5_sum']) {
            \DBG::msg('MD5 comparision complete, MD5 sum matches');
            return true;
        }
    }

    $rawmd5 = md5(preg_replace('/\s/u', '', file_get_contents($file)));
    foreach ($arrMd5SumsOfCxFiles[$cxFilePath] as $validMd5Sum) {
        if ($rawmd5 == $validMd5Sum['raw_md5_sum']) {
            \DBG::msg('MD5 comparision complete, raw MD5 sum matches');
            return true;
        }
    }
    
    $md5SumOfNewFile = md5_file($newFile);
    if ($md5 == $md5SumOfNewFile) {
        \DBG::msg('MD5 comparision complete, file equals new one');
        return true;
    }

    \DBG::msg('MD5 comparision complete, file has changed');
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
            $status = copyCxFilesToRoot($srcPath, $dstPath);
            if ($status !== true) {
                return $status;
            }
        } else {
            $copiedCxFilesIndex++;

            if (isset($_SESSION['contrexx_update']['copiedCxFilesIndex']) && $copiedCxFilesIndex <= $_SESSION['contrexx_update']['copiedCxFilesIndex']) {
                continue;
            }

            $_SESSION['contrexx_update']['copiedCxFilesTotal'] = $_SESSION['contrexx_update']['copiedCxFilesTotal'] + 1;

            try {
                
                // rename the file if its exists on customizing
                if (!renameCustomizingFile($dstPath)) {
                    return false;
                }
                
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
                $_SESSION['contrexx_update']['copiedCxFilesTotal'] = $_SESSION['contrexx_update']['copiedCxFilesTotal'] - 1;
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

function renameCustomizingFile($file)
{
    global $_CONFIG;
    
    $cxFilePath = dirname(substr($file, strlen(ASCMS_DOCUMENT_ROOT)));
    if ($cxFilePath == '/') {
        $cxFilePath = '';
    }

    $customizingPath = ASCMS_DOCUMENT_ROOT.'/customizing'.$cxFilePath;
    
    $customizingFile = $customizingPath . '/'. basename($file);

    if (!file_exists($file)) {
        \DBG::msg('Rename customizing file cancelled. File not found in original path (' . $file . ')');
        return true;
    }

    if (file_exists($customizingFile)) {
        $customizingFile .= "_".$_CONFIG['coreCmsVersion'];
        
        $suffix = '';
        $idx = 0;
        while (file_exists($customizingFile.$suffix)) {
            $idx++;
            $suffix = '_'.$idx;
        }

        $customizingFile .= $suffix;
    } else {
        return true;
    }

    try {
        $objFile = new \Cx\Lib\FileSystem\File($file);
        $objFile->move($customizingFile);
    } catch (\Exception $e) {
        setUpdateMsg('Error on renaming customizing file:<br />' . $file);
        setUpdateMsg('Error: ' . $e->getMessage());
        setUpdateMsg('<br />Häufigste Ursache dieses Problems ist, dass zur Ausführung dieses Vorgangs die benötigten Schreibrechte nicht vorhanden sind. Prüfen Sie daher, ob die FTP-Konfiguration in der Datei <strong>config/configuration.php</strong> korrekt eingerichtet ist.');
        return false;
    }

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

/**
 * This will migrate the session tables by keeping the current session
 * (but only the current one) alive
 */
function migrateSessionTable()
{
    global $sessionObj;
    
    try {
        // update and empty session_variable table
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'session_variable',
            array(
                'id'        => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' =>true),
                'parent_id' => array('type' => 'INT(11)', 'notnull' => true, 'after' => 'id'),
                'sessionid' => array('type' => 'VARCHAR(32)', 'notnull' => true, 'default' => '', 'after' => 'parent_id'),
                'lastused'  => array('type' => 'TIMESTAMP', 'notnull' => true, 'default_expr' => 'CURRENT_TIMESTAMP', 'on_update' => 'CURRENT_TIMESTAMP', 'after' => 'sessionid'),
                'key'       => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '', 'after' => 'lastused'),
                'value'     => array('type' => 'TEXT', 'notnull' => false, 'default' => '', 'after' => 'key')
            ),
            array(
                'key_index' => array('fields' => array('parent_id', 'key', 'sessionid'), 'type' => 'UNIQUE'),
                'key_parent_id_sessionid' => array('fields' => array('parent_id', 'sessionid')),
            ),
            'InnoDB'
        );
        \Cx\Lib\UpdateUtil::sql('TRUNCATE TABLE `'. DBPREFIX .'session_variable`');

        // update and empty sessions table
        \Cx\Lib\UpdateUtil::table(
            DBPREFIX.'sessions',
            array(
                'sessionid'      => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'primary' => true),
                'remember_me'    => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'sessionid'),
                'startdate'      => array('type' => 'VARCHAR(14)', 'notnull' => true, 'default' => '', 'after' => 'remember_me'),
                'lastupdated'    => array('type' => 'VARCHAR(14)', 'notnull' => true, 'default' => '', 'after' => 'startdate'),
                'status'         => array('type' => 'VARCHAR(20)', 'notnull' => true, 'default' => '', 'after' => 'lastupdated'),
                'user_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'status'),
            ),
            array(
                'LastUpdated'    => array('fields' => array('lastupdated')),
            ),
            'InnoDB'
        );
        \Cx\Lib\UpdateUtil::sql('TRUNCATE TABLE `'. DBPREFIX .'sessions`');
        
        // migrate the current session into database
        $_SESSION['contrexx_update']['update']['done'][] = 'session';
        $sessionArray = $_SESSION;
        insertSessionArray(session_id(), $sessionArray);

    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
    return true;
}

/**
 * This inserts a session into the new session structure
 * Make sure that the session tables are empty before calling this
 */
function insertSessionArray($sessionId, $sessionArr, $parentId = 0)
{
    global $objDatabase;

    if ($parentId == 0) {
        \Cx\Lib\UpdateUtil::sql('
            INSERT INTO
                '. DBPREFIX .'sessions
            SET
                `sessionid` = \''. $sessionId .'\',
                `remember_me` = 0,
                `startdate` = \'' . time() . '\',
                `lastupdated` = \'' . time() . '\',
                `status` = \'backend\',
                `user_id` = \'' . \FWUser::getFWUserObject()->objUser->getId() . '\'
        ');
    }
    foreach ($sessionArr as $key => $value) {
        \Cx\Lib\UpdateUtil::sql('
            INSERT INTO
                '. DBPREFIX .'session_variable
            SET
                `parent_id` = "'. intval($parentId) .'",
                `sessionid` = "'. $sessionId .'",
                `key` = "'. contrexx_input2db($key) .'",
                `value` = "'. (is_array($value) ? '' : contrexx_input2db(serialize($value)))  .'"
            ON DUPLICATE KEY UPDATE
                `value` = "'. (is_array($value) ? '' : contrexx_input2db(serialize($value))) .'"
        ');
        $insertId = $objDatabase->Insert_ID();
        
        if (is_array($value)) {
            insertSessionArray($sessionId, $value, $insertId);
        }
    }
}

function _convertThemes2Component()
{
    global $objDatabase, $_CORELANG;
    
    // Find all themes
    $result = $objDatabase->Execute('SELECT `id`, `themesname`, `foldername`, `expert` FROM `' . DBPREFIX . 'skins`');
    if ($result->EOF) {
        \DBG::msg('No themes found!');
        return false;
    }
    
    $errorMessages = '';
    $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
    while (!$result->EOF) {
        $themePath = ASCMS_THEMES_PATH . '/' . $result->fields['foldername'];
        if (!is_dir($themePath)) {
            \DBG::msg('Skipping theme "' . $result->fields['themesname'] . '"; No such folder!');
            $errorMessages .= '<div class="message-warning">' . sprintf($_CORELANG['TXT_CSS_UPDATE_MISSING_FOLDER'], $result->fields['themesname']) . '</div>';
            $result->MoveNext();
            continue;
        }
        
        // create a new one if no component.yml exists
        if (!file_exists($themePath . '/component.yml')) {
            \DBG::msg('Converting theme "' . $result->fields['themesname'] . ' to component');
            $theme = new \Cx\Core\View\Model\Entity\Theme($result->fields['id'], $result->fields['themesname'], $result->fields['foldername'], $result->fields['expert']);
            $themeRepository->convertThemeToComponent($theme);
        }

        $result->MoveNext();
    }
    
    if (!empty($errorMessages)) {
        setUpdateMsg($errorMessages, 'msg');
        setUpdateMsg('<input type="submit" value="'.$_CORELANG['TXT_CONTINUE_UPDATE'].'" name="updateNext" /><input type="hidden" name="processUpdate" id="processUpdate" />', 'button');
        $_SESSION['contrexx_update']['update']['done'][] = 'convertTemplates';
        return false;
    }
    return true;
}

function _migrateComponents($components, $objUpdate, $missedModules) {
    global $_CORELANG, $_CONFIG;

    if (!is_array($components) || empty($components)) {
        setUpdateMsg('Keine Komponenten angegeben.');
        return false;
    }
    if (!isset($_SESSION['contrexx_update']['update']['migrateComponentsDone'])) {
        $_SESSION['contrexx_update']['update']['migrateComponentsDone'] = array();
    }

    // list of core components who's update script will be executed independently
    $specialComponents2skip = array(
        'backendAreas', 'componentmanager', 'contentmanager', 'core', 'modules', 'repository', 'settings', 'utf8',
    );

    // component update scripts that introduce changes for all versions (pre and post v3)
    $genericMigrationScripts = array(
        // core
        'routing', 'wysiwyg',
        // core module
        'access', 'contact',
        'cron', 'linkmanager', 'news',
        // module
        'blog', 'calendar', 'crm', 'data', 'directory', 'downloads', 'ecard', 'filesharing',
        'forum', 'gallery', 'market', 'mediadir', 'memberdir', 'newsletter', 'podcast', 'shop',
    );

    foreach ($components as $dir) {
        $dh = opendir(dirname(__FILE__).'/components/'.$dir);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if (in_array($file, ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['migrateComponentsDone']))) {
                    continue;
                }

                $fileInfo = pathinfo(dirname(__FILE__).'/components/'.$dir.'/'.$file);

                if ($fileInfo['extension'] == 'php') {
                    // skip special components that are being executed individually
                    if (preg_match('/('.join($specialComponents2skip, '|').')/', $fileInfo['filename'])) {
                        \DBG::msg("skip special component: $file");
                        continue;
                    }

                    // skip all files that don't introduce changes for versions 3.0 and up
                    if (
                        !$objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0') &&
                        !in_array($fileInfo['filename'], $genericMigrationScripts)
                    ) {
                        continue;
                    }
                    DBG::msg("--------- updating $file ------");

                    if (!include_once(dirname(__FILE__).'/components/'.$dir.'/'.$file)) {
                        setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                        setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_UPDATE_COMPONENT'], dirname(__FILE__).'/components/'.$dir.'/'.$file));
                        return false;
                    }

                    if (!in_array($fileInfo['filename'], $missedModules)) {
                        $function = '_'.$fileInfo['filename'].'Update';
                        if (function_exists($function)) {
                            DBG::msg("execute $function");
                            $result = $function();
                            if ($result === false) {
                                if (empty($objUpdate->arrStatusMsg['title'])) {
                                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                                }
                                return false;
                            } elseif ($result === 'timeout') {
                                return $result;
                            }
                        } else {
                            setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
                            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UPDATE_COMPONENT_CORRUPT'], '.'.$fileInfo['filename'], $file));
                            return false;
                        }
                    } else {
                        $function = '_'.$fileInfo['filename'].'Install';
                        if (function_exists($function)) {
                            DBG::msg("execute $function");
                            $result = $function();
                            if ($result === false) {
                                if (empty($objUpdate->arrStatusMsg['title'])) {
                                    setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_COMPONENT_BUG'], $file), 'title');
                                }
                                return false;
                            } elseif ($result === 'timeout') {
                                return $result;
                            } else {
                                // fetch module info from components/core/modules.php
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

                $_SESSION['contrexx_update']['update']['migrateComponentsDone'][] = $file;
                return 'timeout';
            }
        } else {
            setUpdateMsg($_CORELANG['TXT_UPDATE_ERROR'], 'title');
            setUpdateMsg(sprintf($_CORELANG['TXT_UPDATE_UNABLE_LOAD_DIR_COMPONENTS'], dirname(__FILE__).'/components/'.$dir));
            return false;
        }

        closedir($dh);
    }
    return true;
}

/**
 * Backup the components which have been renamed and remove the old ones to ensure compatibility with windows servers
 *
 * @param $src string path to source-files without trailing slash
 * @param $directories array with name(s) of the directories which shall be backed up and removed if not directly under
 *                     source-path, path without source-path is needed
 * @return bool|string
 */
function backupAndRemove($src, $directories) {
    $folderStructure = array();
    foreach($directories as $directory) {
        $folderStructure[$directory] = getFolderStructure($src . '/' . $directory);

        // set last checked file-index to 0
        if (empty($_SESSION['contrexx_update']['validatedComponentFiles'][$directory])) {
            $_SESSION['contrexx_update']['validatedComponentFiles'][$directory] = 0;
        }
    }

    // Backup any changes in the old components
    foreach ($folderStructure as $rootFolder => $files) {
        for ($i = $_SESSION['contrexx_update']['validatedComponentFiles'][$rootFolder]; $i < count($files); $i++) {
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                $_SESSION['contrexx_update']['validatedComponentFiles'][$rootFolder] = $i;
                return 'timeout';
            }
            $newFile = dirname(__FILE__) . '/cx_files/'. substr($files[$i], strlen(ASCMS_DOCUMENT_ROOT.'/'));

            if (!verifyMd5SumOfFile($files[$i], $newFile)) {
                backupModifiedFile($files[$i]);
            }
        }
    }

    // Remove the old component directories
    if (!removeOldComponents($directories)) {
        return false;
    }

    return true;
}

/**
 * Get the structure of a directory with all the subdirectories and files
 *
 * @param $folder string path to the desired folder
 * @param bool $foldersOnly
 * @param bool $subdirectories
 * @return array all the files and directories in the desired folder
 */
function getFolderStructure($folder, $foldersOnly = false, $subdirectories = true) {
    $files = array();
    $dirs = array($folder);
    $folders = array();

    while (($dir = array_pop($dirs)) !== NULL) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    // add current dir if subdirectories shall be listed as well
                    if ($subdirectories) {
                        $dirs[] = $path;
                    }
                    // add current dir to folders-array if folders only is desired
                    if ($foldersOnly) {
                        $folders[] = $path;
                    }
                } else {
                    $files[] = $path;
                }
            }
            closedir($dh);
        }
    }

    if ($foldersOnly) {
        return $folders;
    }
    return $files;
}

/**
 * Removes the old component-folders
 *
 * @param $folders array root folders which shall be checked for new versions and remvoed
 * @return bool true on success false otherwise
 */
function removeOldComponents($folders) {
    \DBG::msg(__METHOD__);

    $newComponents = array();
    foreach ($folders as $componentFolder) {
        $newComponents[$componentFolder] = getFolderStructure(dirname(__FILE__) . '/cx_files/' . $componentFolder, true, false);
        if (empty($_SESSION['contrexx_update']['removedComponents'][$componentFolder])) {
            $_SESSION['contrexx_update']['removedComponents'][$componentFolder] = 0;
        }
    }

    $componentList = array(
        'Config' => 'settings',
        'ComponentManager' => 'modulemanager',
        'ContentWorkflow' => 'workflow',
        'Country' => 'country',
        'Csrf' => 'CSRF',
        'DatabaseManager' => 'dbm',
        'Error' => 'error',
        'FrontendEditing' => 'frontendediting',
        'ImageType' => 'Imagetype',
        'JavaScript' => 'JavaScript',
        'JsonData' => 'jsondata',
        'MailTemplate' => 'MailTemplate',
        'LanguageManager' => 'language',
        'Message' => 'Message',
        'Security' => 'Security',
        'Session' => 'session',
        'SystemInfo' => 'server',
        'SystemLog' => 'log',
        'ViewManager' => 'skins',
        'Access' => 'access',
        'Agb' => 'agb',
        'Alias' => 'alias',
        'Cache' => 'cache',
        'Captcha' => 'captcha',
        'Contact' => 'contact',
        'FileBrowser' => 'fileBrowser',
        'Home' => 'home',
        'Ids' => 'ids',
        'Imprint' => 'imprint',
        'Login' => 'login',
        'Media' => 'media',
        'NetTools' => 'nettools',
        'News' => 'news',
        'Privacy' => 'privacy',
        'Search' => 'search',
        'Sitemap' => 'sitemap',
        'Stats' => 'stats',
        'Block' => 'block',
        'Blog' => 'blog',
        'Calendar' => 'calendar',
        'Checkout' => 'checkout',
        'Crm' => 'crm',
        'Data' => 'data',
        'Directory' => 'directory',
        'DocSys' => 'docsys',
        'Downloads' => 'downloads',
        'Ecard' => 'ecard',
        'Egov' => 'egov',
        'Feed' => 'feed',
        'FileSharing' => 'filesharing',
        'Forum' => 'forum',
        'Gallery' => 'gallery',
        'GuestBook' => 'guestbook',
        'Jobs' => 'jobs',
        'Knowledge' => 'knowledge',
        'Livecam' => 'livecam',
        'Market' => 'market',
        'MediaDir' => 'mediadir',
        'MemberDir' => 'memberdir',
        'Newsletter' => 'newsletter',
        'Podcast' => 'podcast',
        'Recommend' => 'recommend',
        'Shop' => 'shop',
        'U2u' => 'u2u',
        'Voting' => 'voting',
    );

    foreach ($newComponents as $componentFolder => $newComponentNames) {
        // load the removedComponent index stored in the session
        $removedComponents = $_SESSION['contrexx_update']['removedComponents'][$componentFolder];
        \DBG::msg(__METHOD__.': removed components:');
        \DBG::dump($removedComponents);
        for ($i = $removedComponents; $i < count($newComponentNames); $i++) {
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                $_SESSION['contrexx_update']['removedComponents'][$componentFolder] = $i;
                return 'timeout';
            }

            // get the component name out of the path
            $newComponentNamesExploded = explode('/', $newComponentNames[$i]);
            $newComponentName = end($newComponentNamesExploded);

            // check if the component has been renamed
            if (!isset($componentList[$newComponentName])) {
                continue;
            }

            $oldComponentName = $componentList[$newComponentName];

            // check if the component has been renamed, backed up or if the directory exists in cx_files/
            if (
                false
                //   !file_exists(ASCMS_CUSTOMIZING_PATH . '/' . $componentFolder . '/' . $oldComponentName)
                //|| file_exists(dirname(__FILE__) . '/cx_files/' . $componentFolder . '/' . $oldComponentName)
            ) {
                // Componentname didn't change or component hasn't been backed up
                // or component doesn't exist in cx_files => No need to remove it
                \DBG::msg("skip component folder removal of $newComponentName");
                continue;
            }

            // get old component name
            $path = ASCMS_DOCUMENT_ROOT . '/' . $componentFolder . '/' .$oldComponentName;

            // make sure that current path is a directory and it can be removed
            if (!is_dir($path)) {
                \DBG::msg("skip component folder removal of $newComponentName; path ($path) is not recognized as a folder");
                continue;
            }

            \DBG::msg("remove folder $path");
            if (!\Cx\Lib\FileSystem\FileSystem::delete_folder($path, $recursive = true)) {
                // failed to remove folder
                setUpdateMsg('Das Verzeichnis \'' . $path . '\' konnte nicht gelöscht werden.');
                return false;
            }
        }
    }

    return true;
}

function getNewComponentNames() {
    return array (
        'Access', 'Agb', 'Blog', 'Calendar', 'Checkout', 'Contact', 'Cron',
        'Data', 'Directory', 'DocSys', 'Downloads', 'Ecard', 'Egov', 'Error',
        'Feed', 'FileSharing', 'Forum', 'Gallery', 'GuestBook', 'Home', 'Html',
        'Ids', 'Imprint', 'Jobs', 'Knowledge', 'LinkManager', 'Livecam', 'Login',
        'Market', 'Media', 'Media1', 'Media2', 'Media3', 'Media4', 'MediaBrowser',
        'MediaDir', 'MemberDir', 'NetManager', 'News', 'Newsletter', 'Order',
        'Pim', 'Podcast', 'Privacy', 'Recommend', 'Search', 'Shell', 'Shop',
        'Sitemap', 'Survey', 'SysLog', 'U2u', 'Uploader', 'User', 'Voting', 'Wysiwyg',
    );
}

/**
 * Migrate the page logs to the new naming convention (component names CamelCase)
 *
 * @return boolean true on success false on failure
 */
function _migratePageLogs() {
    $componentNames = getNewComponentNames();
    foreach ($componentNames as $componentName) {
        try {
            $nameLength = strlen($componentName);
            $nameLower = strtolower($componentName);

            \Cx\lib\UpdateUtil::sql(
                'UPDATE `' . DBPREFIX . 'log_entry`
                 SET `data` = REPLACE(`data`, \'"module";s:'. $nameLength . ':"' . $nameLower . '"\', \'"module";s:'. $nameLength . ':"' . $componentName . '"\')
                 WHERE `data` LIKE \'%"module";s:' . $nameLength . ':' . $nameLower . '"%\''
            );
        } catch (\Cx\Lib\UpdateException $e) {
            \DBG::log('Update::_migratePageLogs(): Failed to Migrate logs for component ' . $componentName);
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }

    return true;
}

/**
 * Migrate old media and image paths in content and log entries
 * @param string    $where  Either 'page' or 'block' specify in which tables
 *                          the media paths shall be replaced default is 'page'
 * @return bool             true on success false on failure
 */
function _migrateMediaPaths($where = 'page') {
    $mediaPaths = \Cx\Lib\UpdateUtil::getMigrationPaths();
    foreach($mediaPaths as $oldPath => $newPath) {
        try {
            \Cx\Lib\UpdateUtil::migrateContentPageUsingRegexCallback(
                array(),
                '/'.preg_quote($oldPath, '/').'/',
                function() use ($newPath) {
                    return $newPath;
                },
                array('content', 'target'),
                '5.0.0'
            );
        } catch (\Cx\Lib\UpdateException $e) {
            \DBG::log('Update::_migrateMediaPaths(): Failed to migrate to new path ' . $newPath);
            return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
        }
    }
    return true;
}

function _migrateTemplateMediaPaths($themeRepository = null) {
    // check if the themeRepository from theme Migration is still available
    if (!isset($themeRepository)) {
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
    }
    // get all available templates
    $availableTemplates = $themeRepository->findAll();
    $mediaPaths = \Cx\Lib\UpdateUtil::getMigrationPaths();
    foreach ($availableTemplates as $template) {
        // check if current template-folder exists
        if (is_dir(ASCMS_THEMES_PATH . $template->getFoldername())) {
            // get all files inside of the current template
            $files = getFolderStructure(ASCMS_THEMES_PATH . $template->getFoldername());
            foreach ($files as $file) {
                // get file info of the current file
                $fileInfo = pathinfo($file);
                // check if it is either a .css-, .html- or .js-file
                // we do not want to check any pictures, fonts or rather any
                // other file type on that matter
                if (in_array($fileInfo['extension'], array('css', 'html', 'js'))) {
                    try {
                        $file = new \Cx\Lib\FileSystem\File($file);
                        $content = $file->getData();
                        foreach($mediaPaths as $oldPath => $newPath) {
                            $migratedContent = preg_replace(
                                '#' . $oldPath . '#',
                                $newPath,
                                $content
                            );
                            // check if the content did in fact change
                            if ($migratedContent != $content) {
                                // write the new content into the file
                                $file->write($migratedContent);
                            }
                        }
                    } catch (\Exception $e) {
                        \DBG::log($e->getMessage());
                        continue;
                    }
                }
            }
        }
    }

}

function migratePageApplicationNames() {
        $componentNames = getNewComponentNames();
        $em = \Env::get('em');
        $pages = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page')->findBy(array('type' => Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION), true);
        foreach ($pages as $page) {
            if ($page) {
                if (!checkMemoryLimit()) {
                    return 'timeout';
                }
                try {
                    // detect new component name
                    $matchedComponentNames = preg_grep('/^' . $page->getModule() . '$/i', $componentNames);
                    if (count($matchedComponentNames) != 1) {
                        // TODO message
                        return false;
                    }
                    $matchedComponentName = current($matchedComponentNames);
                    if (empty($matchedComponentName)) {
                        continue;
                    }

                    // check if the page has already been migrated
                    if ($page->getModule() == $matchedComponentName) {
                        continue;
                    }

                    // update page with new component name
                    $page->setModule($matchedComponentName);
                    $page->setUpdatedAtToNow();
                    $em->persist($page);
                }
                catch (\Exception $e) {
                    \DBG::log("Migrating page application name failed: ".$e->getMessage());
                    throw new UpdateException('Bei der Migration einer Inhaltsseite trat ein Fehler auf! '.$e->getMessage());
                }
            }
        }
        $em->flush();

}


// move content of application content pages into HTML files in associated themes and replace it by {APPLICATION_DATA} placeholder
function installContentApplicationTemplates() {
    try {
        if (!isset($_SESSION['contrexx_update']['update']['migratedApplicationContentPages'])) {
            $_SESSION['contrexx_update']['update']['migratedApplicationContentPages'] = array();
        }

        $virtualComponents = array('Agb', 'Ids', 'Imprint', 'Privacy');
        //migrating custom application template
        $pageRepo   = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $themeRepo  = new \Cx\Core\View\Model\Repository\ThemeRepository();

        $pages      = $pageRepo->findBy(array('type' => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION));
        foreach ($pages As $page) {
            // skip already migrated pages
            if (in_array($page->getId(), ContrexxUpdate::_getSessionArray($_SESSION['contrexx_update']['update']['migratedApplicationContentPages']))) {
                continue;
            }

            //virtual components do not migrating custom application template
            if (in_array(ucfirst($page->getModule()), $virtualComponents)) {
                continue;
            }

            // skip standard application pages
            if ($page->getContent() == '{APPLICATION_DATA}') {
                continue;
            }

            $designTemplateName  = $page->getSkin() ? $themeRepo->findById($page->getSkin())->getFoldername() : $themeRepo->getDefaultTheme()->getFoldername();
            $cmd                 = !$page->getCmd() ? 'Default' : ucfirst($page->getCmd());
            $moduleFolderName    = \Cx\Core\ModuleChecker::getInstance(\Env::get('em'), \Env::get('db'), \Env::get('ClassLoader'))->isCoreModule($page->getModule()) ? 'core_modules' : 'modules';

            $themesPath = ASCMS_THEMES_PATH . '/' . $designTemplateName;

            //check common module or core_module folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName)) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName);
            }

            //check module's folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName . '/' . $page->getModule())) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName . '/' . $page->getModule());
            }

            //check module's template folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template')) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template');
            }

            //check module's Frontend folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template/Frontend')) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template/Frontend');
            }

            $targetPath = $themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template/Frontend';
            $applicationTemplateName = getApplicationTemplateFilename($targetPath, $cmd . '_custom_' . FWLanguage::getLanguageCodeById($page->getLang()));

            if (file_exists($targetPath)) {
                //create a application template file
                $file = new \Cx\Lib\FileSystem\File($targetPath . '/' . $applicationTemplateName);
                $file->write($page->getContent());
            }

            //update application template
            $page->setContent('{APPLICATION_DATA}');
            $page->setApplicationTemplate($applicationTemplateName);
            $page->setUseCustomApplicationTemplateForAllChannels(1);
            \Env::get('em')->persist($page);
            \Env::get('em')->flush();

            $_SESSION['contrexx_update']['update']['migratedApplicationContentPages'][] = $page->getId();
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                return 'timeout';
            }
        }
    } catch (\Exception $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

    return true;
}

function getApplicationTemplateFilename($path, $name) {
    if (!file_exists($path . '/' . $name . '.html')) {
        return $name . '.html';
    }

    $suffix = 1;
    while (file_exists($path . '/' . $name . $suffix . '.html')) {
        $suffix++;
    }

    return $name . $suffix . '.html';
}

/**
 * Detect the actual version of a contrexx 3.x installation
 *
 * @return  mixed   FALSE in case the detection failed
 *                  NULL in case the installation is not a 3.x installation
 *                  A string indicating the actual version
 */
function detectCx3Version() {
    global $_CONFIG, $objUpdate;

    static $version = null;

    if (isset($version)) {
        return $version;
    }

    if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '3.0.0')) {
        return null;
    }

    try {
        $objResultRc1 = \Cx\Lib\UpdateUtil::sql('SELECT `target` FROM `'.DBPREFIX.'backend_areas` WHERE `area_id` = 186');
        $objResultRc2 = \Cx\Lib\UpdateUtil::sql('SELECT `order_id` FROM `'.DBPREFIX.'backend_areas` WHERE `area_id` = 2');

        if (!$objResultRc1 || !$objResultRc2) {
            return false;
        }

        if ($objResultRc1->fields['target'] != '_blank') {
            $version = 'rc1';
        } elseif ($objResultRc2->fields['order_id'] != 6 && $_CONFIG['coreCmsVersion'] == '3.0.0') {
            $version = 'rc2';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.0.0') {
            $version = 'stable';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.0.0.1') {
            $version = 'hotfix';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.0.1') {
            $version = 'sp1';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.0.2') {
            $version = 'sp2';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.0.3') {
            $version = 'sp3';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.0.4') {
            $version = 'sp4';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.1.0') {
            $version = '310';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.1.1') {
            $version = '311';
        } elseif ($_CONFIG['coreCmsVersion'] == '3.2.0') {
            $version = '320';
        } else {
            // installation is either older than v3 or newer (4+)
            $version = null;
        }

        return $version;
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }
}

class License {
    public function update($getNew = true) {
        global $objUpdate;

// TODO: load license from config/License.lic

        \DBG::msg(__METHOD__.': manually update settings in case license update failed');
        $arrUpdate = $objUpdate->getLoadedVersionInfo();
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::init('Config', 'release','Yaml'));
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::set('coreCmsVersion', $arrUpdate['cmsVersion']));
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::set('coreCmsCodeName', $arrUpdate['cmsCodeName']));
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::set('coreCmsReleaseDate', $arrUpdate['cmsReleaseDate']));
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::set('coreCmsName', $arrUpdate['cmsName']));
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::set('coreCmsStatus', $arrUpdate['cmsStatus']));
        \DBG::dump(\Cx\Core\Setting\Controller\Setting::updateAll());
        \DBG::dump(\Cx\Core\Config\Controller\Config::updatePhpCache());

// TODO: fix license
        return true;
    }
}
