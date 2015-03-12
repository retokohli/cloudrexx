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
     * Test function to validate website name
     */
    function testValidateWebsiteName()
    {
        $websiteName = 'mytestsite';

        // verify that name is not a blocked word
        $unavailablePrefixesValue = explode(',',\Cx\Core\Setting\Controller\Setting::getValue('unavailablePrefixes'));
        $this->assertNotContains($websiteName, $unavailablePrefixesValue);
        
        // verify that name complies with naming scheme
        $this->assertNotRegExp('/[^a-z0-9]/', $websiteName);
        
        // verify that website name length
        $this->assertLessThan(strlen($websiteName), \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMinLength'));
        
        $this->assertGreaterThan(strlen($websiteName), \Cx\Core\Setting\Controller\Setting::getValue('websiteNameMaxLength'));

        // Check existing website
        $website = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('name' => $websiteName));
        $this->assertNotInstanceOf('\Cx\Core_Modules\MultiSite\Model\Entity\Website', $website);
        
        return $websiteName;
    }
    
    /**
     * @depends testValidateWebsiteName
     */
    function testAddWebsite($websiteName)
    {
        $this->createWebsite($websiteName);
        
        // Check the website is present or not
        $website = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->findOneBy(array('name' => $websiteName));
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
