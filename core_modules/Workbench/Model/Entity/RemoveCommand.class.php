<?php
/**
 * Command to remove workbench from installation
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to remove workbench from installation
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class RemoveCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'remove';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Removes the workbench from this installation';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) remove';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'Removes all workbench stuff from this installation in order to switch to production mode';
    
    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {
        if (!$this->interface->yesNo('Removing workbench requires re-installing workbench to use it again. Are you sure?')) {
            return;
        }
        
        // Remove component from Db and FileSystem
        $component = new \Cx\Core\Core\Model\Entity\ReflectionComponent('Workbench', 'core_module');
        $component->remove();
        
        // Remove additional files (config, command line script)
        foreach ($this->interface->getWorkbench()->getFileList() as $file) {
            if (is_dir($file)) {
                \Cx\Lib\FileSystem\FileSystem::delete_folder(ASCMS_DOCUMENT_ROOT . $file, true);
            } else {
                \Cx\Lib\FileSystem\FileSystem::delete_file(ASCMS_DOCUMENT_ROOT . $file);
            }
        }
        
        $this->interface->show('Done');
    }
}
