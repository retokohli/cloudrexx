<?php
/**
 * Specific JsonHtmlController for this Component. Use this to easily handle the ajax request
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */

namespace Cx\Core\Html\Controller;

/**
 * Specific JsonHtmlController for this Component. Use this to easily handle the ajax request
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_html
 */
class JsonHtmlController extends    \Cx\Core\Core\Model\Entity\Controller 
                         implements \Cx\Core\Json\JsonAdapter {
    /**
     * Returns the internal name used as identifier for this adapter
     * 
     * @return String Name of this adapter
     */
    public function getName() {
        return 'Html';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * 
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array(
            'updateOrder' => new \Cx\Core_Modules\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true)
        );
    }
    
    /**
     * Returns all messages as string
     * 
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
        
        $post = is_array($params['post']) ? $params['post'] : array();
        if (    empty($post)
            ||  empty($post['sortOrder'])
            ||  empty($post['sortField'])
            ||  empty($post['component'])
            ||  empty($post['entity'])
        ) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_HTML_UPDATE_SORT_ORDER_FAILED']);
        }
        
        //Get all the 'POST' values
        $sortOrders = is_array($post['sortOrder']) 
                      ? array_map('contrexx_input2int', $post['sortOrder'])
                      : array();
        $sortField  = !empty($post['sortField'])
                      ? contrexx_input2raw($post['sortField'])
                      : '';
        $component  = !empty($post['component'])
                      ? contrexx_input2raw($post['component'])
                      : '';
        $entity     = !empty($post['entity'])
                      ? contrexx_input2raw($post['entity'])
                      : '';
        
        $em = $this->cx->getDb()->getEntityManager();
        $componentRepo   = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $objComponent    = $componentRepo->findOneBy(array('name' => $component));
        $entityNameSpace = $objComponent->getNamespace() . '\\Model\\Entity\\' . $entity;
        //check whether the entity namespace is a valid one or not
        if (!in_array($entityNameSpace, $objComponent->getEntityClasses())) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_HTML_UPDATE_SORT_ORDER_FAILED']);
        }
        
        //Get the Repository object of the corresponding Entity
        $entityRepo     = $em->getRepository($entityNameSpace);
        //Get the 'Primary key' name
        $entityObject   = $em->getClassMetadata($entityNameSpace);
        $primaryKeyName = $entityObject->getSingleIdentifierFieldName();
        $classMethods   = get_class_methods($entityObject->newInstance());
        //check whether the updating entity set method is a valid one or not
        if (!in_array('set'.ucfirst($sortField), $classMethods)) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_HTML_UPDATE_SORT_ORDER_FAILED']);
        }
        
        //update the order field
        $orderId = 1;
        foreach ($sortOrders as $sortOrder) {
            $entity = $entityRepo->findOneBy(array($primaryKeyName => $sortOrder));
            if ($entity) {
                $entity->{'set'.ucfirst($sortField)}($orderId);
                $orderId++;
            }
        }
        $em->flush();
        
        return array('status' => 'success');
    }
}
