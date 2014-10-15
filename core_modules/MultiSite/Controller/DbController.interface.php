<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core_Modules\MultiSite\Controller;

class DbControllerException extends \Exception {}

/**
 * Description of newPHPClass
 *
 * @author ritt0r
 */
interface DbController {

    /**
     * Creates a DB user
     * @param string $name (optional) Name for the new user
     * @return \Cx\Core\Model\Model\Entity\DbUser representation of the created user
     */
    public function createDbUser(\Cx\Core\Model\Model\Entity\DbUser $user);
    
    /**
     * Creates a DB
     * @param string $name Name for the new database
     * @param \Cx\Core\Model\Model\Entity\DbUser $user (optional) Database user to grant rights for this DB, if null is given a new User is created
     * @return \Cx\Core\Model\Model\Entity\Db Abstract representation of the created database
     */
    public function createDb(\Cx\Core\Model\Model\Entity\Db $db, \Cx\Core\Model\Model\Entity\DbUser $user = null);
    
    /**
     * Grants user $user usage rights on database $database
     * @param \Cx\Core\Model\Model\Entity\DbUser $user Database user to grant rights for
     * @param \Cx\Core\Model\Model\Entity\Db $db Database to work on
     * @throws MultiSiteDbException On error
     */
    public function grantRightsToDb(\Cx\Core\Model\Model\Entity\DbUser $user, \Cx\Core\Model\Model\Entity\Db $database);
    
    /**
     * Revokes user $user all rights on database $database
     * @param \Cx\Core\Model\Model\Entity\DbUser $user Database user to revoke rights of
     * @param \Cx\Core\Model\Model\Entity\Db $db Database to work on
     * @throws MultiSiteDbException On error
     */
    public function revokeRightsToDb(\Cx\Core\Model\Model\Entity\DbUser $user, \Cx\Core\Model\Model\Entity\Db $database);
    
    /**
     * Removes a db user
     * @param \Cx\Core\Model\Model\Entity\DbUser $dbUser User to remove
     * @throws MultiSiteDbException On error
     */
    public function removeDbUser(\Cx\Core\Model\Model\Entity\DbUser $dbUser, \Cx\Core\Model\Model\Entity\Db $db);
    
    /**
     * Removes a db
     * @param \Cx\Core\Model\Model\Entity\Db $db Database to remove
     * @throws MultiSiteDbException On error
     */
    public function removeDb(\Cx\Core\Model\Model\Entity\Db $db);
}
