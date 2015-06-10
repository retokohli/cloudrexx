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
        if (!$isHigherVersion) {
            rsort($versions);
        }
        foreach ($versions as $version) {
            if (    (   $isHigherVersion 
                    &&  (   $version > $olderVersion 
                        &&  $version <= $latestVersion
                        )
                    )
                ||
                    (   !$isHigherVersion 
                    &&  (   $version <= $olderVersion 
                        &&  $version > $latestVersion
                        )
                    )
            ) {
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

        $status     = true;
        $yamlFile   = null;
        foreach ($deltas as $delta) {
            $status = $delta->applyNext();
            $delta->setRollback($delta->getRollback() ? 0 : 1);
            $deltaRepository->flush();
            if (!$status) {
                //Rollback to old state
                $this->rollBackDelta();
                //Rollback the codebase changes(settings.php, configuration.php and website codebase in manager and service)
                $yamlFile = \Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteTempPath() . '/Update/PendingCodeBaseChanges.yml';
                if (file_exists($yamlFile)) {
                    $pendingCodeBaseChanges = $this->getUpdateWebsiteDetailsFromYml($yamlFile);
                    $oldCodeBase            = $pendingCodeBaseChanges['PendingCodeBaseChanges']['oldCodeBaseId'];
                    $latestCodeBase         = $pendingCodeBaseChanges['PendingCodeBaseChanges']['latestCodeBaseId'];
                    //Register YamlSettingEventListener
                    \Cx\Core\Config\Controller\ComponentController::registerYamlSettingEventListener();
                    //Update codeBase in website
                    $this->updateCodeBase($latestCodeBase, null, $oldCodeBase);
                    //Update website codebase in manager and service
                    \Cx\Core\Setting\Controller\Setting::init('MultiSite', '', 'FileSystem');
                    $websiteName = \Cx\Core\Setting\Controller\Setting::getValue('websiteName', 'MultiSite');
                    $params = array('websiteName' => $websiteName, 'codeBase' => $oldCodeBase);
                    \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnMyServiceServer('updateWebsiteCodeBase', $params);
                }
                break;
            }
        }

        //Remove the folder '/tmp/Update', After the completion of rollback or Non-rollback process
        $tmpUpdateFolderPath = \Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteTempPath() . '/Update';
        if (file_exists($tmpUpdateFolderPath)) {
            \Cx\Lib\FileSystem\FileSystem::delete_folder($tmpUpdateFolderPath, true);
        }
        
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
        rsort($rollBackDeltas);
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
     * Update CodeBase
     * 
     * @param string $newCodeBaseVersion   latest codeBase version
     * @param string $installationRootPath installationRoot path
     * @param string $oldCodeBaseVersion   old codeBase version
     */
    public function updateCodeBase($newCodeBaseVersion, $installationRootPath, $oldCodeBaseVersion = '') 
    {
        //change installation root
        $objConfigData = new \Cx\Lib\FileSystem\File(\Cx\Core\Core\Controller\Cx::instanciate()->getWebsiteConfigPath() . '/configuration.php');
        $configData    = $objConfigData->getData();
        if (!\FWValidator::isEmpty($oldCodeBaseVersion)) {
            $matches = array();
            preg_match('/\\$_PATHCONFIG\\[\'ascms_installation_root\'\\] = \'(.*?)\';/', $configData , $matches);
            $installationRootPath = str_replace($newCodeBaseVersion, $oldCodeBaseVersion, $matches[1] );
            $newCodeBaseVersion   = $oldCodeBaseVersion;
        }
        
        $newConfigData = preg_replace('/\\$_PATHCONFIG\\[\'ascms_installation_root\'\\] = \'.*?\';/', '$_PATHCONFIG[\'ascms_installation_root\'] = \'' . $installationRootPath . '\';', $configData);

        $objConfigData->write($newConfigData);
        
        //change code base
        \Cx\Core\Setting\Controller\Setting::init('Config', '', 'Yaml');
        \Cx\Core\Setting\Controller\Setting::set('coreCmsVersion', $newCodeBaseVersion);
        \Cx\Core\Setting\Controller\Setting::update('coreCmsVersion');
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
    
    /**
     * Store the website details into the YML file
     * 
     * @param string $folderPath
     * @param string $filePath
     * @param array  $ymlContent
     */
    public function storeUpdateWebsiteDetailsToYml($folderPath, $filePath, $ymlContent)
    {
        if (empty($folderPath) || empty($filePath)) {
            return;
        }

        try {
            if (!file_exists($folderPath)) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($folderPath);
            }

            $file = new \Cx\Lib\FileSystem\File($filePath);
            $file->touch();

            $yaml = new \Symfony\Component\Yaml\Yaml();
            $file->write(
                $yaml->dump(
                        array('PendingCodeBaseChanges' => $ymlContent )
                )
            );
        } catch (\Exception $e) {
            \DBG::log($e->getMessage());
        }
    }
    
    /**
     * Get update websiteDetailsFromYml
     * 
     * @param string $file yml file name
     * @return array
     */
    public function getUpdateWebsiteDetailsFromYml($file) {
        if (!file_exists($file)) {
            return;
        }
        $objFile = new \Cx\Lib\FileSystem\File($file);
        $yaml = new \Symfony\Component\Yaml\Yaml();
        return $yaml->load($objFile->getData());
    }
    
    /**
     * getAllCodeBaseVersions
     * 
     * @param string $codeBasePath codeBase path
     * @return array
     */
    public function getAllCodeBaseVersions($codeBasePath) {
        //codebase
        $codeBaseVersions   = array();
        $codebaseScannedDir = array_values(array_diff(scandir($codeBasePath), array('..', '.')));
        foreach ($codebaseScannedDir as $value) {
            $configFile = $codeBasePath . '/' . $value . '/installer/config/config.php';
            if (file_exists($configFile)) {
                $configContents = file_get_contents($configFile);
                if (preg_match_all('/\\$_CONFIG\\[\'(.*?)\'\\]\s+\=\s+\'(.*?)\';/s', $configContents, $matches)) {
                    $configValues       = array_combine($matches[1], $matches[2]);
                    $codeBaseVersions[] = $configValues['coreCmsVersion'];
                }
            }
        }
        return $codeBaseVersions;
    }

}
