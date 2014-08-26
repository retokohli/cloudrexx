<?php
/**
 * CronLinkManager.php
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_linkmanager
 */

// Note! The path below will work if the cron file is moved 
// to "customizing/cron" folder. If you intend to trigger the LinkManager cron 
// form a other directory, please adjust the path below.
require_once dirname(dirname(dirname(__FILE__))).'/core/Core/init.php';

init(\Cx\Core\Core\Controller\Cx::MODE_MINIMAL);
//\DBG::activate(DBG_ADODB_ERROR | DBG_PHP | DBG_LOG_FILE);

define('PROCESS_TIME', time());
foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) { 
    new Cx\Modules\LinkManager\Controller\LinkCrawlerController($lang['id'], $lang['lang']);
}

