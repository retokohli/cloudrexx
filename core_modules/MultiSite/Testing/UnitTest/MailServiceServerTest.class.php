<?php

/**
 * MailServiceServerTest
 * Test cases for the class Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer
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
 * MailServiceServerTest
 * Test cases for the class Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class MailServiceServerTest extends \Cx\Core\Test\Model\Entity\MultiSiteTestCase
{

    /**
     * Test function to create mail account for website
     */
    function testCreateAccount()
    {
        /**
         * Website to create mail account
         */
        $website = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')
                                  ->findOneBy(array('name' => 'mytestsite'));
        
        $accountId = 0; // Initially the mail account id is null
        
        /**
         * The mail account created from the manager mode so check the mode and website object
         */
        if ($website && 
            \Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER)
        {
            // get default mail service server
            $defaultMailServiceServer = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\MailServiceServer')
                                                       ->findOneBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultMailServiceServer')));
            
            // create a mail service account in controller and flush the data into db
            $accountId = $defaultMailServiceServer->createAccount($website);
            $website->setMailAccountId($accountId);
            self::$cx->getDb()->getEntityManager()->flush();
            
            // Check the created mail account is presnt or not
            $this->assertNotEmpty($accountId);
            $this->assertEquals($accountId, $website->getMailAccountId());
            
            return $website;
        } else {
            $this->setExpectedException('Failed to create mail service account.');
        }
        
    }
    
    /**
     * @depends testCreateAccount
     */
    function testEnableService(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        if ($website && 
            \Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER)
        {
            $mailServiceServer = $website->getMailServiceServer();
            $mailServiceServer->enableService($website);
            
            $this->assertInstanceOf('\Cx\Core_Modules\MultiSite\Model\Entity\Domain', $website->getMailDn());
        } else {
            $this->setExpectedException('Failed to enable mail service account.');
        }
    }
    
    /**
     * @depends testCreateAccount
     */
    function testDisableService(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {        
        if ($website && 
            \Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER)
        {
            $mailServiceServer = $website->getMailServiceServer();
            $mailServiceServer->disableService($website);
            
            $this->assertEmpty($website->getMailDn());
        } else {
            $this->setExpectedException('Failed to disable mail service account.');
        }
    }
    
    /**
     * @depends testCreateAccount
     */
    function testDeleteAccount(\Cx\Core_Modules\MultiSite\Model\Entity\Website $website)
    {
        if ($website && 
            \Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER)
        {
            $website->getMailServiceServer()->deleteAccount($website->getMailAccountId());
            $website->getMailServiceServer()->removeWebsite($website);
            $website->setMailAccountId(null);
            $website->setMailServiceServer(null);
            self::$cx->getDb()->getEntityManager()->flush();
            
            $this->assertEmpty($website->getMailAccountId());
        } else {
            $this->setExpectedException('Failed to delete mail service account.');
        } 
    }
}