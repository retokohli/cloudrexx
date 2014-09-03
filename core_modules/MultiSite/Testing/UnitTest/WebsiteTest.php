<?php
include_once(ASCMS_TEST_PATH.'/testCases/DoctrineTestCase.php');

/**
 * Test cases for the class Cx\Core_Modules\MultiSite\Model\Entity\Website
 *
 * @author ss4u <ss4u.comvation@gmail.com>
 */
class WebsiteTest extends DoctrineTestCase {
    
    function testAddWebsite() {
        
        $objFWUser   = \FWUser::getFWUserObject();
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw(1));        
        $websiteName = 'mytestwebsite'. \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');
        
        // check website is manager
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('isDefault' => 1));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        
        $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
        \Env::get('em')->persist($objWebsite);        
        \Env::get('em')->flush();
        
        return $objWebsite->setup(array('subscription' => 'Trail'));        
    }
}