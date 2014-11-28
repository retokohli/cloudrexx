<?php
/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_netmanager
 */

namespace Cx\Core\NetManager\Controller;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_netmanager
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
    
    /**
     * Template object
     */
    protected $template;
    
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands() {
        return array();
    }
    
    /**
     * Use this to parse your backend page
     * 
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array $cmd CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        // this class inherits from Controller, therefore you can get access to
        // Cx like this:
        $this->cx;
        $this->template = $template;
        
        // instantiate the default View Controller
        new \Cx\Core\NetManager\Controller\DefaultController($this->getSystemComponentController(), $this->cx, $this->template);
        
        \Message::show();
    }
    
}
