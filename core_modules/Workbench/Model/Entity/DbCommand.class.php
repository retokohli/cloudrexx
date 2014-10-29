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
            // empty /model/entities and /model/repositories folders
            case 'cleanup':
                $this->cleanup();
                break;
            // update database for component
            case 'update':
                // empty /model/entities and /model/repositories folders
                $this->cleanup();
                // doctrine orm:validate-schema
                $this->executeDoctrine(array('', 'doctrine', 'orm:validate-schema'));
                // doctrine orm:generate-entities --filter="{component filter}" entities
                $this->executeDoctrine(array('', 'doctrine', 'orm:generate-entities', '--filter="{component filter}"', 'entities'));
                // move entities to component directory and add .class extension
                // todo
                // doctrine orm:generate-repositories --filter="{component filter}" repositories
                $this->executeDoctrine(array('', 'doctrine', 'orm:generate-repositories', '--filter="{component filter}"', 'repositories'));
                // move repositories to component directory and add .class extension
                // todo
                // doctrine orm:schema-tool:create --dump-sql
                // remove component tables
                // execute sql statements from db dump
                // empty /model/entities and /model/repositories folders
                $this->cleanup();
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
        $cli->run();
    }
    
    protected function cleanup() {
        // leave /model/entities/Cx/Model/Base/EntityBase.class.php where it is!
    }
}
