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
        $em                = $this->cx->getDb()->getEntityManager();
        
        $this->cx->getEvents()->triggerEvent(
            'SysLog/Add', array(
            'severity'  => 'INFO', 
            'message'   => 'Cron Execution Started',
            'data'      => ' ',
        ));
        switch ($command) {
            case 'Cron':
              $cronJobs = $em->getRepository('Cx\Core_Modules\Cron\Model\Entity\Job')->findAll();
              if ($cronJobs) {
                  foreach ($cronJobs as $cron) {
                      $cron->execute();
                      $em->flush();
                  }
              }
              break;
        }
        
    }
}
