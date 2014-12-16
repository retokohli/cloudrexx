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

$cx = init(\Cx\Core\Core\Controller\Cx::MODE_MINIMAL);
//\DBG::activate(DBG_ADODB_ERROR | DBG_PHP | DBG_LOG_FILE);

define('PROCESS_TIME', time());
$em = $cx->getDb()->getEntityManager();
$componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
$linkManager = $componentRepo->findOneBy(array('name'=>'LinkManager'));
foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
    $linkCrawlerController = new Cx\Core_Modules\LinkManager\Controller\LinkCrawlerController($linkManager, $cx);
    $linkCrawlerController->loadCrawler($lang['id'], $lang['lang']);
}

