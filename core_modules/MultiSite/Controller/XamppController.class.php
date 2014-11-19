<?php
/**
 * Class XampController
 *
 * This is the XampController class.
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Sudhir Parmar 
 * @package     contrexx
 * @subpackage  coremodule_MultiSite
 * @version     1.0.0
 */

namespace Cx\Core_Modules\MultiSite\Controller;
/**
 * Class XampController
 *
 * Controller clask used to create database, websites and
 * database user creation etc.
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Sudhir Parmar
 * @package     contrexx
 * @subpackage  coremodule_MultiSite
 * @version     1.0.0
 */
class XamppController implements \Cx\Core_Modules\MultiSite\Controller\DbController,
                                 \Cx\Core_Modules\MultiSite\Controller\SubscriptionController,
                                 \Cx\Core_Modules\MultiSite\Controller\FtpController,
                                 \Cx\Core_Modules\MultiSite\Controller\DnsController {
    /*
     * Protected object for db queries
     * */
    protected $db;
    
     /*
     * Constructor
     */
    public function __construct(\Cx\Core\Model\Model\Entity\Db $db, \Cx\Core\Model\Model\Entity\DbUser $dbUser) {
        $dbClass = new \Cx\Core\Model\Db($db, $dbUser);
        // init new db
        $this->db = $dbClass->getAdoDb(); 
    }
     /**
     * Creates a DB user
     * @param string $name (optional) Name for the new user
     * @return \Cx\Core\Model\Model\Entity\DbUser representation of the created user
     */
    public function createDbUser(\Cx\Core\Model\Model\Entity\DbUser $user){
        $objResult = $this->db->Execute('CREATE USER \'' . $user->getName() . '\'@\'localhost\' IDENTIFIED BY \'' . $user->getPassword() . '\'');
        if ($objResult === false) {
            throw new \Exception("Could not create database user (2/" . 'CREATE USER \'' . $user->getName() . '\'@\'localhost\' IDENTIFIED BY \'' . '******' . '\'' . "/" . $this->db->ErrorMsg() . ")!");
        }    
    }
    
    /**
     * Creates a DB
     * @param string $name Name for the new database
     * @param \Cx\Core\Model\Model\Entity\DbUser $user (optional) Database user to grant rights for this DB, if null is given a new User is created
     * @return \Cx\Core\Model\Model\Entity\Db Abstract representation of the created database
     */
    public function createDb(\Cx\Core\Model\Model\Entity\Db $db, \Cx\Core\Model\Model\Entity\DbUser $user = null){
        $objResult = $this->db->Execute("CREATE DATABASE `" . $db->getName() . "` DEFAULT CHARACTER SET ".$db->getCharset()." COLLATE ".$db->getCollation());   
        if (!($objResult)) {
            throw new \Exception("Could not create database (1/" . $this->db->ErrorMsg() . ")!");
        }
        if($user != null){
            if($user !== null){
                $this->createDbUser($user);
                $this->grantRightsToDb($user, $db);// create a db user if $user is not null   
            }
        }
    }
    
    /**
     * Grants user $user usage rights on database $database
     * @param \Cx\Core\Model\Model\Entity\DbUser $user Database user to grant rights for
     * @param \Cx\Core\Model\Model\Entity\Db $db Database to work on
     * @throws MultiSiteDbException On error
     */
    public function grantRightsToDb(\Cx\Core\Model\Model\Entity\DbUser $user, \Cx\Core\Model\Model\Entity\Db $database){
        $objResult = $this->db->Execute('GRANT ALL PRIVILEGES ON `' . $database->getName() . '` . * TO \'' . $user->getName() . '\'@\'localhost\'');
        if ($objResult === false) {
            throw new \Exception("Could not grant database permission to user (3/" . $this->db->ErrorMsg() . ")!");
        }    
    }
    
    /**
     * Revokes user $user all rights on database $database
     * @param \Cx\Core\Model\Model\Entity\DbUser $user Database user to revoke rights of
     * @param \Cx\Core\Model\Model\Entity\Db $db Database to work on
     * @throws MultiSiteDbException On error
     */
    public function revokeRightsToDb(\Cx\Core\Model\Model\Entity\DbUser $user, \Cx\Core\Model\Model\Entity\Db $database){
        $isRevoked = $this->db->execute("REVOKE ALL PRIVILEGES FROM '".$user->getName."'@'localhost'");   
        if(!$isRevoked){
            throw new \Exception("Query failed: \REVOKE ALL PRIVILEGES FROM ".$user->getName."'@'localhost'" . $this->db->ErrorMsg());
        }
    }
    
    /**
     * Removes a db user
     * @param \Cx\Core\Model\Model\Entity\DbUser $dbUser User to remove
     * @throws MultiSiteDbException On error
     */
    public function removeDbUser(\Cx\Core\Model\Model\Entity\DbUser $dbUser, \Cx\Core\Model\Model\Entity\Db $db ){
        $isUserExist = $this->db->execute("SELECT User FROM mysql.user WHERE user = '".$dbUser->getName()."'");
        if ($isUserExist->RecordCount() == 1) {
           $isUserDeleted = $this->db->execute("DROP USER '".$dbUser->getName()."'@'localhost'");
            if (!$isUserDeleted) {
                throw new \Exception("Query failed: \ DROP USER '".$dbUser->getName()."'@'localhost'" . $this->db->ErrorMsg());
            }
        }
    }
    
    /**
     * Removes a db
     * @param \Cx\Core\Model\Model\Entity\Db $db Database to remove
     * @throws MultiSiteDbException On error
     */
    public function removeDb(\Cx\Core\Model\Model\Entity\Db $db){
        $isDbExist = $this->db->execute("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$db->getName()."'");
        if ($isDbExist->RecordCount() == 1) {
            $isDbCreated = $this->db->execute("DROP DATABASE `".$db->getName()."`");
            if (!$isDbCreated) {
                throw new \Exception('Query failed: \'DROP DATABASE `'.$db->getName().'`\', ' . $this->db->ErrorMsg());
            }  
        }
    }
    
     /**
     * Creates a Subscription
     * @param \Cx\Core\Model\Model\Entity\Subscription
     * @return 
     */
    public function createSubscription(\Cx\Core_Modules\MultiSite\Model\Entity\Customer $customer,\Cx\Core_Modules\MultiSite\Model\Entity\SubscriptionInfo $subscription){
        //createSubscription code will be here
    }
    
    /**
     * Removes a Subscription
     * @param \Cx\Core\Model\Model\Entity\Subscription
     * @throws MultiSiteDbException On error
     */
    public function removeSubscription(\Cx\Core_Modules\MultiSite\Model\Entity\SubscriptionInfo $subscription){
        //removeSubscription code will be here  
        return 0;  
    }
    
    /**
     * Creaye a Customer
     * @param \Cx\Core\Model\Model\Entity\Subscription
     * @throws MultiSiteDbException On error
     */
    public function createCustomer(\Cx\Core_Modules\MultiSite\Model\Entity\Customer $customer){
        //createCustomer code will be here     
        return 0;   
    }

    /**
     * @todo    Implement interface to BIND or similar
     */
    public function addDnsRecord($type = 'A', $host, $value, $zone = null, $zoneId = null){
        \DBG::msg("MultiSite (XamppController): add DNS-record: $type / $host / $value / $zone / $zoneId");
        return null;
    }

    public function removeDnsRecord($type, $host, $id) {
        \DBG::msg("MultiSite (XamppController): remove DNS-record: $type / $host / $id");
        return true;
    }

    public function updateDnsRecord($type, $host, $value, $zone = null, $zoneId = null, $id = null){
        \DBG::msg("MultiSite (XamppController): update DNS-record: $type / $host / $value / $zone / $zoneId / $id");
        return null;
    }
    
    public function addFtpAccount($userName, $password, $homePath, $subscriptionId) {
        \DBG::msg("MultiSite (XamppController): add Ftp-Account: $userName / $password / $homePath / $subscriptionId");
        return null;
    }
    
    public function removeFtpAccount($userName) {
        \DBG::msg("MultiSite (XamppController): remove Ftp-Account: $userName");
        return true;
    }
    
    public function changeFtpAccountPassword($userName, $password) {
        \DBG::msg("MultiSite (XamppController): update Ftp-Account Password: $userName / $password");
        return null;
    }
    
    public function getDnsRecords() {
        \DBG::msg("MultiSite (XamppController): get Dns Records");
        return null;
    }
    
    /**
     * Get Ftp Accounts
     */
    public function getFtpAccounts() {
        \DBG::msg("MultiSite (XamppController): get Ftp Accounts");
        return null;
    }
    
    /**
     * Create new domain alias
     * 
     * @param string $aliasName alias name
     * 
     * @return null
     */
    public function createDomainAlias($aliasName)
    {
        \DBG::msg("MultiSite (XamppController): create domain alias");
        return null;
    }
    
    /**
     * Rename the domain alias
     * 
     * @param string $oldAliasName old alias name
     * @param string $newAliasName new alias name
     * 
     * @return null
     */
    public function renameDomainAlias($oldAliasName, $newAliasName)
    {
        \DBG::msg("MultiSite (XamppController): rename domain alias");
        return null;
    }
    
    /**
     * Remove the domain alias by name
     * 
     * @param string $aliasName alias name to delete
     * 
     * @return null
     */
    public function deleteDomainAlias($aliasName)
    {
        \DBG::msg("MultiSite (XamppController): delete domain alias");
        return null;
    }
}
