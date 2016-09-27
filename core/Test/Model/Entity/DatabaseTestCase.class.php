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
 
/**
 * DatabaseTestCase
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

class DatabaseTestCaseException extends \Exception {}

/**
 * DatabaseTestCase
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */
abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * Instance of PDO connetction
     *
     * @var \PDO
     */
    static protected $pdo  = null;

    /**
     * Database connection instance
     *
     * @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection 
     */
    protected $conn = null;
    
    /**
     * Entitymanager
     */
    protected static $em;

    /**
     * Default dataset type
     *
     * @var string
     */
    protected $dataSetType = 'yaml';

    /**
     * Default dataset file name
     *
     * @var string 
     */
    protected $dataSetFile = '/DataSet.yml';

    /**
     * Location of dataset files
     *
     * @var string
     */
    protected $dataSetFolder = '';

    /**
     * Instance of cloudrexx
     *
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;

    /**
     * Default constructor
     *
     * @param string    $name
     * @param array     $data
     * @param string    $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->cx = \Cx\Core\Core\Controller\Cx::instanciate();

        $this->backupGlobals          = true;
        /**
         * Globals has to backup to run repeated tests. Otherwise test might conflict with
         * each other.
         *
         * BackupGlobalsBlacklist
         * List the global key's that could not take backup
         * Ex: PDO can not be serialize and unserialize
         */
        $this->backupGlobalsBlacklist = array(
          'objDatabase',
          'objInit',
          'objCache',
          'cx',
        );
        $this->backupStaticAttributes = false;
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass() {
        if (self::$pdo == null) {
            self::$pdo = \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getPdoConnection();
        }
        self::$em = \Env::get('em');
    }

    /**
     * Sets up the database transaction
     */
    public function setUp() {
        self::$pdo->beginTransaction();
        parent::setUp();
    }

    /**
     * Rollback the database transaction
     */
    public function tearDown() {
        parent::tearDown();
        self::$pdo->rollBack();
    }

    /**
     * Returns the test database connection.
     *
     * @return \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected function getConnection()
    {
        global $_DBCONFIG;
        if ($this->conn == null) {
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $_DBCONFIG['database']);
        }
        return $this->conn;
    }

    /**
     * Returns the database operation executed in test setup 
     *
     * @return \PHPUnit_Extensions_Database_Operation_Composite
     */
    public function getSetUpOperation()
    {
        return new \PHPUnit_Extensions_Database_Operation_Composite(array(
            new TruncateOperation(),
            \PHPUnit_Extensions_Database_Operation_Factory::INSERT()
        ));
    }

    /**
     * Returns the dataset instance
     * TO-DO: Extend this method to support the xml and other dataset types
     *
     * @return \PHPUnit_Extensions_Database_DataSet_YamlDataSet
     */
    public function getDataSet()
    {
        $dataSetFile = $this->dataSetFolder .  $this->dataSetFile;
        if (   !\Cx\Lib\FileSystem\FileSystem::exists($dataSetFile)
            || !is_file($dataSetFile)
        ) {
            throw new DatabaseTestCaseException('Invaild dataset file provided');
        }
        $dataSet = null;
        switch ($this->dataSetType) {
            case 'yaml':
            default:
                $ymlParser = new SymfonyYamlParser();
                $dataSet   = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
                    $this->dataSetFolder . $this->dataSetFile,
                    $ymlParser
                );
                break;
        }
        return $dataSet;
    }
}
