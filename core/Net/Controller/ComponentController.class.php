<?php
/**
 * Main controller for Net
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */

namespace Cx\Core\Net\Controller;

/**
 * Main controller for Net
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_net
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    public function preInit(\Cx\Core\Core\Controller\Cx $cx) {
        global $_CONFIG;
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $_CONFIG['domainUrl'] = $domainRepo->getMainDomain()->getName();
        \Env::set('config', $_CONFIG);
    }

    /**
     * Convert idn to ascii Format
     * 
     * @param string $name
     * 
     * @return string
     */
    public static function convertIdnToAsciiFormat($name) {
        if (empty($name)) {
            return;
        }
        
        if (!function_exists('idn_to_ascii')) {
            \DBG::msg('Idn is not supported in this system.');           
        } else {
            $name = idn_to_ascii($name);
        }
        
        return $name;
    }
    
    /**
     * Convert idn to utf8 format
     * 
     * @param string $name
     * 
     * @return string
     */
    public static function convertIdnToUtf8Format($name) {
        if (empty($name)) {
            return;
        }
        
        if (!function_exists('idn_to_utf8')) {
            \DBG::msg('Idn is not supported in this system.');
        } else {
            $name = idn_to_utf8($name);
        }
        
        return $name;
    }
}
