<?php
/**
 * Class User
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
 
namespace Cx\Core_Modules\MultiSite\Model\Entity;

/**
 * Class User
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class User extends \User {
    /**
     * Used to define a custom user-ID instead of assigning a new one determined by auto-increment
     * @var integer
     */
    protected $multiSiteId = null;

    /**
     * Set a custom ID which shall be used for a newly created user instead of the next auto-increment ID.
     * @param   integer $id The user-ID to use for the newly created user account
     */
    public function setMultiSiteId($id){
        $this->multiSiteId = $id;
    }    

    /**
     * Overwritten \User::createUser() method
     * Used to create a new user by manually defining its user-ID
     */
    protected function createUser() {
        global $_CORELANG;

        // if no custom user ID has been set, then the regular user creation process shall be followed
        if (!$this->multiSiteId) {
            return parent::createUser();
        }

        $db = \Env::get('db');
        if (!$db->Execute('INSERT INTO `'.DBPREFIX.'access_users` SET `id` = '.$this->multiSiteId.', `regdate` = '.time())) {
            $this->error_msg[] = $_CORELANG['TXT_ACCESS_FAILED_TO_ADD_USER_ACCOUNT'];
            return false;
        }
        $this->id = $db->Insert_ID();
        if (!$this->createProfile()) {
            $this->delete();
            $this->error_msg[] = $_CORELANG['TXT_ACCESS_FAILED_TO_ADD_USER_ACCOUNT'];
            return false;
        }
        return parent::updateUser();
    }
}
