<?php
/**
 * Command to access doctrine command line tools
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to access doctrine command line tools
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class DbCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'db';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Allows access to doctrine command line tools and adds some handy shortcuts';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) db [doctrine {doctrine syntax}|{command}]';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Gives access to doctrine command line tools and other db management commands. Use "help" to see what commands are available.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {
        $arguments = array_slice($arguments, 1);
        
        switch ($arguments[1]) {
            // empty /tmp/workbench
            case 'cleanup':
                $this->cleanup();
                break;
            // update database for component
            case 'update':
                // doctrine orm:validate-schema
                if ($this->executeDoctrine(array('', 'doctrine', 'orm:validate-schema')) != 0) {
                    return;
                }
                
                // empty /tmp/workbench
                $this->cleanup();
                
                // prepare component filter
                $componentFilter = '';
                if (isset($arguments[2])) {
                    switch (strtolower($arguments[2])) {
                        case 'core':
                            $componentFilter .= 'Cx\\Core\\';
                            break;
                        case 'core_module':
                        case 'core_modules':
                            $componentFilter .= 'Cx\\Core_Modules\\';
                            break;
                        case 'module':
                        case 'modules':
                            $componentFilter .= 'Cx\\Modules\\';
                            break;
                    }
                    if (isset($arguments[3])) {
                        $componentFilter .= $arguments[3] . '\\';
                    }
                }
                if (!empty($componentFilter)) {
                    $componentFilter = '--filter=' . $componentFilter;
                }
                
                // doctrine orm:generate-entities --filter="{component filter}" entities
                $doctrineArgs = array('', 'doctrine', 'orm:generate-entities');
                if (!empty($componentFilter)) {
                    $doctrineArgs[] = $componentFilter;
                }
                $doctrineArgs[] = $this->cx->getWebsiteTempPath().'/workbench';
                if ($this->executeDoctrine($doctrineArgs) != 0) {
                    return;
                }
                
                // move entities to component directory and add .class extension
                $this->moveModel($this->cx->getWebsiteTempPath().'/workbench/Cx', $this->cx->getWebsiteDocumentRootPath());
                
                // doctrine orm:generate-repositories --filter="{component filter}" repositories
                $doctrineArgs = array('', 'doctrine', 'orm:generate-repositories');
                if (!empty($componentFilter)) {
                    $doctrineArgs[] = $componentFilter;
                }
                $doctrineArgs[] = $this->cx->getWebsiteTempPath().'/workbench';
                if ($this->executeDoctrine($doctrineArgs) != 0) {
                    return;
                }
                
                // move repositories to component directory and add .class extension
                $this->moveModel($this->cx->getWebsiteTempPath().'/workbench/Cx', $this->cx->getWebsiteDocumentRootPath());
                
                // doctrine orm:schema-tool:create --dump-sql
                // remove component tables
                // execute sql statements from db dump
                // empty /model/entities and /model/repositories folders
                //$this->cleanup();
                break;
            case 'doctrine':
                $this->executeDoctrine($arguments);
                break;
            case 'help':
            default:
                echo 'Command `' . $this->getName() . "`\r\n" .
                        $this->getDescription() . "\r\n\r\n" .
                        $this->getSynopsis() . "\r\n\r\n" .
                        $this->getHelp() . "\r\n";
                break;
        }
        echo "Done\r\n";
    }
    
    public function executeDoctrine(array $arguments) {
        $_SERVER['argv'] = array_slice($arguments, 1);
        
        $cli = new \Symfony\Component\Console\Application('Doctrine Command Line Interface', \Doctrine\Common\Version::VERSION);
        $cli->setCatchExceptions(true);
        $helperSet = $cli->getHelperSet();
        $helpers = array(
            'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(\Env::get('cx')->getDb()->getEntityManager()),
        );
        foreach ($helpers as $name => $helper) {
            $helperSet->set($helper, $name);
        }
        $cli->addCommands(array(
            // DBAL Commands
            new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
            new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

            // ORM Commands
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
            new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
            new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
            new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand(),

        ));
        $cli->setAutoExit(false);
        return $cli->run();
    }
    
    protected function cleanup() {
        \Cx\Lib\FileSystem\FileSystem::delete_folder($this->cx->getWebsiteTempPath().'/workbench', true);
        \Cx\Lib\FileSystem\FileSystem::make_folder($this->cx->getWebsiteTempPath().'/workbench');
    }
    
    protected function moveModel($sourceFolder, $destinationFolder) {
        $sourceDirectory = new \RecursiveDirectoryIterator($sourceFolder);
        $sourceDirectoryIterator = new \RecursiveIteratorIterator($sourceDirectory);
        $sourceDirectoryRegexIterator = new \RegexIterator($sourceDirectoryIterator, '/^.+\.php$/i', \RegexIterator::GET_MATCH);
        
        // foreach model class
        foreach ($sourceDirectoryRegexIterator as $sourceFile) {
            // move to correct location and add .class ending if necessary
            $sourceFile = current($sourceFile);
            $parts = explode('/Cx/', $sourceFile);
            $destinationFile = $destinationFolder . '/' . end($parts);
            $destinationFile = preg_replace_callback('#(' . $destinationFolder . '/)(Core(?:_Modules)?|Modules)#', function($matches) {
                return $matches[1] . strtolower($matches[2]);
            },
            $destinationFile);
            $destinationFile = preg_replace('/(?!\.class)\.php$/', '.class.php', $destinationFile);
            try {
                $objFile = new \Cx\Lib\FileSystem\File($sourceFile);
                $objFile->move($destinationFile);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                throw $e;
            }
        }
    }
}

