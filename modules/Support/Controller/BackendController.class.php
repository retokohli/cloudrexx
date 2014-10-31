<?php
/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_support
 */

namespace Cx\Modules\Support\Controller;

/**
 * Class SupportException
 */
class SupportException extends \Exception {}

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_support
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
    
    /**
     * Template object
     */
    protected $template;
    
    /**
     * Sub menu name
     */
    protected $submenuName;
    
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
        $act = $cmd[0];
        $this->submenuName = $this->getSubmenuName($cmd);
        //support configuration setting
        self::errorHandler();
        $this->connectToController($act);
        
        \Message::show();
    }
    
    /**
     * Trigger a controller according the act param from the url
     * 
     * @param   string $act
     */
    public function connectToController($act)
    {
        $act = ucfirst($act);
        if (!empty($act)) {
            $controllerName = __NAMESPACE__.'\\'.$act.'Controller';
            if (!$controllerName && !class_exists($controllerName)) {
                return;
            }
            //  instantiate the view specific controller
            new $controllerName($this->getSystemComponentController(), $this->cx, $this->template, $this->submenuName);
        } else { 
            // instantiate the default View Controller
            new DefaultController($this->getSystemComponentController(), $this->cx, $this->template);
        }
    }   
    
    /**
     * get the sub menu name
     * 
     * @param array $cmd
     * 
     * @return null|string
     */
    private function getSubmenuName($cmd)
    {
        if(count($cmd) > 1){
            $submenu = ucfirst($cmd[1]);
            return $submenu;
        }
        return null;
    }
    
    /**
     * Fixes database errors.   
     * 
     * @global array $_CONFIG
     * 
     * @return boolean
     * @throws SupportException
     */
    static function errorHandler() {
        global $_CONFIG;
        
        try {
            \Cx\Core\Setting\Controller\Setting::init('Support', '', 'Yaml');
            
            //setup group
            \Cx\Core\Setting\Controller\Setting::init('Support', 'setup', 'Yaml');
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('faqUrl') && !\Cx\Core\Setting\Controller\Setting::add('faqUrl', 'https://www.cloudrexx.com/FAQ', 1, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')) {
                throw new SupportException("Failed to add Setting entry for faq url");
            }
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('recipientMailAddress') && !\Cx\Core\Setting\Controller\Setting::add('recipientMailAddress', $_CONFIG['coreAdminEmail'], 2, \Cx\Core\Setting\Controller\Setting::TYPE_TEXT, null, 'setup')) {
                throw new SupportException("Failed to add Setting entry for recipient mail address");
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }
        
        // Always!
        return false;
    }

}
