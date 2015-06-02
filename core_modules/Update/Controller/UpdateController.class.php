<?php

/**
 * Class UpdateController
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_update
 */

namespace Cx\Core_Modules\Update\Controller;

/**
 * Class UpdateController
 *
 * The main Update component
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_update
 */
class UpdateController extends \Cx\Core\Core\Model\Entity\Controller {

    /**
     * Constructor
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
    }

    /**
     * Calculate database delta
     *
     * It will calculate which codeBase database update scripts need to be executed.
     * If the original version number is smaller than new version number,
     * Add each version between those two versions to the delta as non-rollback updates otherwise delta as rollback updates
     * 
     * @param integer $oldVersion
     * @param string  $codeBasePath
     */
    public function calculateDbDelta($oldVersion, $codeBasePath) {
        \Cx\Core\Setting\Controller\Setting::init('Config', '', 'Yaml');

        $latestVersion = str_replace('.', '', \Cx\Core\Setting\Controller\Setting::getValue('coreCmsVersion', 'Config'));
        $olderVersion = str_replace('.', '', $oldVersion);

        $versionClassPath = $codeBasePath . '/core_modules/Update/Data/Migrations';
        $versionFiles = array_diff(scandir($versionClassPath), array('..', '.'));


        $versions = array();
        foreach ($versionFiles as $versionFile) {
            $versions[] = substr(str_replace('.php', '', $versionFile), 7);
        }

        $i = 1;
        $isHigherVersion = $olderVersion < $latestVersion;
        foreach ($versions as $version) {
            if ($version > $olderVersion && $version <= $latestVersion) {
                $delta = new \Cx\Core_Modules\Update\Model\Entity\Delta();
                $rollBack = !$isHigherVersion ? 1 : 0;
                $delta->addCodeBase($version, $rollBack, $i);
                $this->registerDbUpdateHooks($delta);
                $i++;
            }
        }
    }

    /**
     * Register DB Update hooks
     * 
     * This saves the calculated delta to /tmp/Update/PendingDbUpdates.yml. 
     * It contains a serialized Delta.
     * 
     * @staticvar object $deltaRepo
     * @param \Cx\Core_Modules\Update\Model\Entity\Delta $delta
     */
    public function registerDbUpdateHooks(\Cx\Core_Modules\Update\Model\Entity\Delta $delta) {
        static $deltaRepo = null;
        if (!isset($deltaRepo)) {
            $deltaRepo = new \Cx\Core_Modules\Update\Model\Repository\DeltaRepository();
        }

        $deltaRepo->add($delta);
        $deltaRepo->flush();
    }

    /**
     * Get the serialized Delta
     * 
     * This loads the serialized Delta and calls applyNext() on it 
     * until returns false.
     */
    public function applyDelta() {

        //Check if any of the update process is interrupt state, then rollback to old state
        $deltaRepository = new \Cx\Core_Modules\Update\Model\Repository\DeltaRepository();
        $deltas = $deltaRepository->findAll();
        if (empty($deltas)) {
            return;
        }
        asort($deltas);
        //set the website as Offline mode
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
        \Cx\Core\Setting\Controller\Setting::set('websiteState', \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);
        \Cx\Core\Setting\Controller\Setting::update('websiteState');

        $status = true;
        foreach ($deltas as $delta) {
            $status = $delta->applyNext();
            $delta->setRollback(1);
            $deltaRepository->flush();
            if (!$status) {
                //Rollback to old state
                $this->rollBackDelta();
                //$this->updateCodeBase(); --TODO--
                break;
            }
        }

        //Destroy all the records in the file PendingDbUpdates.yml, If complete rollback or Non-rollback process
        foreach ($deltaRepository->findAll() as $delta) {
            $deltaRepository->remove($delta);
        }
        $deltaRepository->flush();

        //set the website back to Online mode
        \Cx\Core\Setting\Controller\Setting::set('websiteState', \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_ONLINE);
        \Cx\Core\Setting\Controller\Setting::update('websiteState');
    }

    /**
     * Rollback the delta to make the website as old state
     */
    protected function rollBackDelta() {
        $deltaRepository = new \Cx\Core_Modules\Update\Model\Repository\DeltaRepository();
        $rollBackDeltas = $deltaRepository->findBy(array('rollback' => 1));
        usort($rollBackDeltas, 'compareOffset');
        foreach ($rollBackDeltas as $rollBackDelta) {
            if (!$rollBackDelta->applyNext()) {
                $websiteName = \Cx\Core\Setting\Controller\Setting::getValue('websiteName', 'MultiSite');
                $params = array('websiteName' => $websiteName, 'emailTemplateKey' => 'notification_update_error_email');
                \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnMyServiceServer('sendUpdateNotification', $params);
                break;
            }
        }
    }

    /**
     * Compare array values based on offset
     * 
     * @param object $obj1
     * @param object $obj2
     * 
     * @return integer
     */
    public function compareOffset($obj1, $obj2) {
        if ($obj1->offset == $obj2->offset) {
            return 0;
        }
        return ($obj1->offset < $obj2->offset) ? -1 : 1;
    }

    /**
     * Update CodeBase
     * 
     * @param string $codeBase
     * @param string $installationRootPath
     */
    public function updateCodeBase($codeBase, $installationRootPath) {
        
        //set website to offline mode
        \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
        \Cx\Core\Setting\Controller\Setting::set('websiteState', \Cx\Core_Modules\MultiSite\Model\Entity\Website::STATE_OFFLINE);
        \Cx\Core\Setting\Controller\Setting::update('websiteState');

        //change code base
        \Cx\Core\Setting\Controller\Setting::init('Config', '', 'Yaml');
        \Cx\Core\Setting\Controller\Setting::set('coreCmsVersion', $codeBase);
        \Cx\Core\Setting\Controller\Setting::update('coreCmsVersion');

        //change installation root
        $newConf = new \Cx\Lib\FileSystem\File(\Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteConfigPath() . '/configuration.php');
        $newConfData = $newConf->getData();
            
        $newConfData = preg_replace('/\\$_PATHCONFIG\\[\'ascms_installation_root\'\\] = \'.*?\';/', '$_PATHCONFIG[\'ascms_installation_root\'] = \'' . $installationRootPath . '\';', $newConfData);

        $newConf->write($newConfData);
    }

    /**
     * 
     * @staticvar type $cli
     * 
     * @return \Symfony\Component\Console\Application
     */
    public static function getDoctrineMigrationCli()
    {
        static $cli = null;
        
        if ($cli) {
            return $cli;
        }
        
        $em = \Env::get('em');
        $conn = $em->getConnection();

        $cli = new \Symfony\Component\Console\Application('Doctrine Command Line Interface', \Doctrine\Common\Version::VERSION);
        $cli->setCatchExceptions(true);
        $helperSet = $cli->getHelperSet();
        $helpers = array(
            'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($conn),
            'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(\Env::get('cx')->getDb()->getEntityManager()),
        );
        foreach ($helpers as $name => $helper) {
            $helperSet->set($helper, $name);
        }

        //custom configuration
        $configuration = new \Doctrine\DBAL\Migrations\Configuration\Configuration($conn);
        $configuration->setName('Doctrine Migration');
        $configuration->setMigrationsNamespace('Cx\Core_Modules\Update\Data\Migrations');
        $configuration->setMigrationsTableName(DBPREFIX . 'migration_versions');
        $configuration->setMigrationsDirectory(\Cx\Core\Core\Controller\Cx::instanciate()->getCodeBaseCoreModulePath() . '/Update/Data/Migrations');
        $configuration->registerMigrationsFromDirectory(\Cx\Core\Core\Controller\Cx::instanciate()->getCodeBaseCoreModulePath() . '/Update/Data/Migrations');

        $cli->addCommands(array(
            // Migrations Commands
            self::getCommandObj('\Cx\Core_Modules\Update\Model\Entity\MigrationsDiffDoctrineCommand', $configuration),
            self::getCommandObj('\Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand', $configuration),
            self::getCommandObj('\Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand', $configuration),
            self::getCommandObj('\Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand', $configuration),
            self::getCommandObj('\Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand', $configuration),
            self::getCommandObj('\Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand', $configuration),
        ));
        $cli->setAutoExit(false);
        
        return $cli;
    }
    
    
    /**
     * Get the doctrine migrations command as object
     * 
     * @param string $nameSpace
     * @param object $configuration
     * @return object
     */
    private static function getCommandObj($nameSpace, $configuration) {
        $commandObj = new $nameSpace();
        $commandObj->setMigrationConfiguration($configuration);
        return $commandObj;
    }
}
