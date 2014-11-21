<?php
/**
 * Class ComponentController
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_cron
 */

namespace Cx\Core\Cron\Controller;

/**
 * Class ComponentController
 *
 * The main controller for Cron
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_cron
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
     * Fetch all Job entities and check each of them if they need to be executed
     * 
     * @param string $command
     */
    public static function executeCommand($command) {
        switch ($command) {
            case 'Cron':
              $cronJobs = \Env::get('em')->getRepository('Cx\Core\Cron\Model\Entity\Job')->findAll();
              if ($cronJobs) {
                  foreach ($cronJobs as $cron) {
                      $cron->execute();
                      \Env::get('em')->persist($cron);
                      \Env::get('em')->flush();
                  }
              }
              break;
        }
        
    }
}
