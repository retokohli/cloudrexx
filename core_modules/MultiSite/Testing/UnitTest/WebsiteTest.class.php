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
        $websiteName = 'mytestsite';
        
        $this->createWebsite($websiteName);
        
        // Check the website is present or not
        $websiteRepo = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website');
        $website = $websiteRepo->findOneBy(array('name' => $websiteName));        
        $this->assertInstanceOf('\Cx\Core_Modules\MultiSite\Model\Entity\Website', $website);

        return $website;
    }
    
    /**
     * @depends testAddWebsite
     */
    function testDestroyWebsite(\Cx\Core_Modules\MultiSite\Model\Entity\Website $objWebsite)
    {   
        $websiteName = $objWebsite->getName();
        
        $objWebsite->destroy();
        
        /**
         * remove the website object and flush into database
         */
        self::$cx->getDb()->getEntityManager()->remove($objWebsite);
        self::$cx->getDb()->getEntityManager()->flush();
        
        // check the website is removed or not
        $website = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('name' => $websiteName));
        $this->assertEmpty($website); 
    }
}
