<?php
include_once(ASCMS_TEST_PATH.'/testCases/DoctrineTestCase.php');

/**
 * Test cases for the class Cx\Core_Modules\MultiSite\Model\Entity\Website
 *
 * @author ss4u <ss4u.comvation@gmail.com>
 */
class WebsiteTest1 extends DoctrineTestCase {
    
    /**
     * Test function to adding a website
     */
    function testAddWebsite1() {
        return true;
        $objFWUser   = \FWUser::getFWUserObject();
        
        /**
         * Creating website requires 2 parameters
         * 1. User object 
         * 2. Website name
         */
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw(1));        // get the user object
        $websiteName = 'mytestwebsite'. \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain'); // website name + multisite domain name
        
        // check website is manager
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        
        // When current server is a website manager then it will request the service server to create the website.
        // So we need to fetch the default website website service server.
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = self::$em->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('isDefault' => 1));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        
        /**
         * create new website object and flush into database
         */
        $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
        self::$em->persist($objWebsite);        
        self::$em->flush();
        
        // configure the website
        return $objWebsite->setup(array('subscription' => 'Trail'));        
    }
}