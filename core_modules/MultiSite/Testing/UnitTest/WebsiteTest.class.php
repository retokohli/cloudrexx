<?php

/**
 * WebsiteTest
 * Test cases for the class Cx\Core_Modules\MultiSite\Model\Entity\Website
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Testing\UnitTest;

/**
 * WebsiteTest
 * Test cases for the class Cx\Core_Modules\MultiSite\Model\Entity\Website
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteTest extends \Cx\Core\Test\Model\Entity\MultiSiteTestCase
{
    
    /**
     * Test function to adding a website
     */
    function testAddWebsite()
    {
        $objFWUser   = \FWUser::getFWUserObject();
        
        /**
         * Creating website requires 2 parameters
         * 1. User object 
         * 2. Website name
         */
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw(1));        // get the user object
        $websiteName = 'mytestwebsite'; // website name
        
        // check website is manager
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        
        // When current server is a website manager then it will request the service server to create the website.
        // So we need to fetch the default website website service server.
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        
        /**
         * create new website object and flush into database
         */
        $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
        self::$cx->getDb()->getEntityManager()->persist($objWebsite);        
        self::$cx->getDb()->getEntityManager()->flush();
        
        // configure the website
        $objWebsite->setup(array('subscription' => 'Trail'));
        
        // Check the website is present or not
        $websiteRepo = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website = $websiteRepo->findOneBy(array('name' => $websiteName));
        
        $this->assertInstanceOf('\Cx\Core_Modules\MultiSite\Model\Entity\Website', $website);
    }
}
