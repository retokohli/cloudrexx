<?php
/**
 * This standalone file adds module and cmd to fallback pages
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

$documentRoot = dirname(__FILE__);

require_once($documentRoot.'/lib/DBG.php');
DBG::activate(DBG_PHP);
require_once($documentRoot.'/core/Env.class.php');                       // needed to get EM
require_once($documentRoot.'/config/settings.php');                      // needed for doctrine.php
require_once($documentRoot.'/config/configuration.php');                 // needed for doctrine.php
require_once($documentRoot.'/core/ClassLoader/ClassLoader.class.php');
new \Cx\Core\ClassLoader\ClassLoader($documentRoot, false);
require_once($documentRoot.'/config/doctrine.php');
require_once($documentRoot.'/lib/FRAMEWORK/Language.class.php');         // needed by page repo
require_once($documentRoot.'/core/API.php');                             // needed for getDatabaseObject()
require_once($documentRoot.'/core/Init.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Setting_Mail.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Setting.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Profile_Attribute.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User_Profile.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/UserGroup.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/User/User.class.php');
require_once($documentRoot.'/lib/FRAMEWORK/FWUser.class.php');
require_once($documentRoot.'/core/session.class.php');

$objDatabase = getDatabaseObject($strErrMessage, true);

$license = \Cx\Core\License\License::getCached($_CONFIG, $objDatabase);

$objInit = new InitCMS('backend', null);

if (!isset($sessionObj) || !is_object($sessionObj)) $sessionObj = new \cmsSession();

$objUser = \FWUser::getFWUserObject()->objUser;
if (!$objUser->login(true) || !$objUser->getAdminStatus()) {
    // do not use die() here, if we use this script as in include it won't work
    echo '<a href="' . ASCMS_ADMIN_WEB_PATH . '/">You must log in as admin before you can update the database. Click here to log in...</a>';
    return;
}

$em = \Env::em();
$pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');

$fallbackPages = $pageRepo->findBy(array(
    'type' => \Cx\Model\ContentManager\Page::TYPE_FALLBACK,
));

foreach ($fallbackPages as $page) {
    $page->setModule($page->getModule());
    $page->setCmd($page->getCmd());
    $em->persist($page);
}
$em->flush();

echo 'All pages converted';
