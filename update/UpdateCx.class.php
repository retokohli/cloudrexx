<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

class UpdateCx extends \Cx\Core\Core\Controller\Cx {
    /**
     * System mode
     * @var string Mode as string
     * @access protected
     */
    protected $mode = 'minimal';

    /**
     * Initializes the UpdateCx class
     * This includes all class variables which are needed for the update system.
     * @global  array   $_PATHCONFIG
     */
    public function __construct() {
        global $_PATHCONFIG;

        $this->setCodeBaseRepository($_PATHCONFIG['ascms_installation_root'], $_PATHCONFIG['ascms_installation_offset']);
        $this->setWebsiteRepository($_PATHCONFIG['ascms_root'], $_PATHCONFIG['ascms_root_offset']);
    }

    /**
     * Loading EventManager, DB, License
     * @global PDOConnection    $connection
     * @global array            $_DBCONFIG
     * @global array            $_CONFIG
     */
    public function minimalInit() {
        global $connection, $_DBCONFIG, $_CONFIG;

        // Set database connection details
        $objDb = new \Cx\Core\Model\Model\Entity\Db();
        $objDb->setHost($_DBCONFIG['host']);
        $objDb->setName($_DBCONFIG['database']);
        $objDb->setTablePrefix($_DBCONFIG['tablePrefix']);
        $objDb->setDbType($_DBCONFIG['dbType']);
        $objDb->setCharset($_DBCONFIG['charset']);
        $objDb->setCollation($_DBCONFIG['collation']);
        $objDb->setTimezone((empty($_CONFIG['timezone'])?$_DBCONFIG['timezone']:$_CONFIG['timezone']));

        // Set database user details
        $objDbUser = new \Cx\Core\Model\Model\Entity\DbUser();
        $objDbUser->setName($_DBCONFIG['user']);
        $objDbUser->setPassword($_DBCONFIG['password']);

        // Initialize database connection
        $this->db = new \Cx\Core\Model\Db($objDb, $objDbUser);
        $this->db->setPdoConnection($connection);
        $this->db->setAdoDb(\Env::get('db'));
        $this->db->setEntityManager(\Env::get('em'));

        // initialize event manager
        $this->eventManager = new \Cx\Core\Event\Controller\EventManager();
        new \Cx\Core\Event\Controller\ModelEventWrapper($this);

        // initialize license
        $this->license = \Cx\Core_Modules\License\License::getCached($_CONFIG, $this->getDb()->getAdoDb());
    }
}
