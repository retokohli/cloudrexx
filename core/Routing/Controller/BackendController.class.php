<?php

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Controller;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    
    /**
    * Returns a list of available commands (?act=XY)
    * @return array List of acts
    */
    public function getCommands() {
        return array('Redirect');
    }
    
    
    /**     
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array $cmd CMD separated by slashes    
    */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        
        switch (current($cmd)) {
            case 'Redirect':
                $entity = 'RewriteRule';
                $entityClassName = $this->getNamespace() . '\\Model\\Entity\\'. $entity;
                $this->parseEntityClassPage($template, $entityClassName, $entity);
                break;
            case '':
            default:
                break;
        }
    }
}
