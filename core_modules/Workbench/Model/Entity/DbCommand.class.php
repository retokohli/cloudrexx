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
    protected $description = 'Allows access to doctrine command line tools';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) db {doctrine syntax}';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Gives access to doctrine command line tools. Use "help" to see what commands doctrine provides.';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {
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
}
