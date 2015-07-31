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
        return array('RewriteRule');
    }
    
    protected function getViewGeneratorOptions($entityClassName, $classIdentifier) {
        global $_ARRAYLANG;
        
        $langVarName = 'TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier);
        $header = '';
        if (isset($_ARRAYLANG[$langVarName])) {
            $header = $_ARRAYLANG[$langVarName];
        }
        return array(
            'header' => $header,
            'fields' => array(
                'id' => array(
                    'showOverview' => false,
                ),
                'regularExpression' => array(
                    'header' => $_ARRAYLANG['regularExpression'],
                ),
                'orderNo' => array(
                    'header' => $_ARRAYLANG['orderNo'],
                ),
                'rewriteStatusCode' => array(
                    'header' => $_ARRAYLANG['rewriteStatusCode'],
                ),
                'continueOnMatch' => array(
                    'header' => $_ARRAYLANG['continueOnMatch'],
                ),
            ),
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
        );
    }
}
