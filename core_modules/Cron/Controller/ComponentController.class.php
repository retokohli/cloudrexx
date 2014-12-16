<?php
/**
 * Class ComponentController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_cron
 */

namespace Cx\Core_Modules\Cron\Controller;

/**
 * Class ComponentController
 *
 * The main controller for Cron
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_cron
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
   /*
     * Constructor
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
    }
    
    public function getCommandsForCommandMode() {
        return array('Cron');
    }
    
    /**
     * This component only has a backend
     */
    public function getControllerClasses() {
        return array('Backend', 'Default', 'Settings');
    }
    
    /**
     * To describe the Cron command
     * 
     * @param type $command
     * @param type $short
     * @return string
     */
    public function getCommandDescription($command, $short = false) {
        switch ($command) {
            case 'Cron':
                return 'Fetching all Jobs and execute if they need to execute';
        }
    }
    /**
     * Fetch all Job entities and check each of them if they need to be executed
     * 
     * @param string $command
     */
    public function executeCommand($command) {
        $em = $this->cx->getDb()->getEntityManager();
        
        $executedJobs = 0;
        $starttime = microtime(true);
        try {
            switch ($command) {
                case 'Cron':
                    $cronJobs = $em->getRepository('Cx\Core_Modules\Cron\Model\Entity\Job')->findBy(array('active'=>1));
                    if (!$cronJobs) {
                        break;
                    }
                    foreach ($cronJobs as $cron) {
                        if ($cron->execute()) {
                            $executedJobs++;
                        }
                        $em->flush();
                    }
                    break;
            }
            $duration = microtime(true) - $starttime;
            $severity = 'INFO';
            $data = 'Successfully executed ' . $executedJobs . ' job(s). This took ' . $duration . 's';
        } catch (\Exception $e) {
            $severity = 'FATAL';
            $data = 'Exception of type "' . get_class($e) . '" with message "' . $e->getMessage() . '" caught in ' . $e->getFile() . ' on line ' . $e->getLine();
        }
        
        $this->cx->getEvents()->triggerEvent('SysLog/Add', array(
            'severity'  => $severity, 
            'message'   => 'Cron Executed',
            'data'      => $data,
        ));
    }
}
