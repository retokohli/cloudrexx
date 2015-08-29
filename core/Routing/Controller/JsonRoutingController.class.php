<?php

/**
 * Specific JsonRoutingController for this Component. Use this to easily handle the ajax request
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Controller;

/**
 * Specific JsonRoutingController for this Component. Use this to easily handle the ajax request
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */
class JsonRoutingController extends    \Cx\Core\Core\Model\Entity\Controller 
                            implements \Cx\Core\Json\JsonAdapter {
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'Routing';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array(
            'updateOrder' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true)
        );
    }
    
    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }
    
    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return null;
    }
    
    /**
     * Update the sort order in DB
     * 
     * @param array $params
     * 
     * @return array
     * @throws \Exception
     */
    public function updateOrder($params) {
        global $_ARRAYLANG;
        
        if (empty($params['post']['sortOrder'])) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_ROUTING_UPDATE_SORT_ORDER_FAILED']);
        }
        
        //Get the 'POST' values
        $sortOrders = !empty($params['post']['sortOrder']) 
                      ? $params['post']['sortOrder']
                      : '';
        //Get the Repository object of RewriteRule Entity
        $em = $this->cx->getDb()->getEntityManager();
        $rewriteRuleRepo = $em->getRepository('Cx\Core\Routing\Model\Entity\RewriteRule');
        //Get the 'Primary key' name
        $entityObject    = $em->getClassMetadata('Cx\Core\Routing\Model\Entity\RewriteRule');
        $primaryKeyName  = $entityObject->getSingleIdentifierFieldName();
        
        //update the field 'orderNo'
        $i = 1;
        foreach ($sortOrders as $sortOrder) {
            $rewriteRule = $rewriteRuleRepo->findOneBy(array($primaryKeyName => $sortOrder));
            if ($rewriteRule) {
                $rewriteRule->setOrderNo($i);
                $i++;
            }
        }
        $em->flush();
        
        return array('status' => 'success');
    }
}
