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
     * @global array  $_ARRAYLANG
     * @global object $objInit
     * 
     * @return array
     * @throws \Exception
     */
    public function updateOrder($params) {
        global $_ARRAYLANG, $objInit;
        
        //get the language interface text
        $langData   = $objInit->loadLanguageData('Html');
        $_ARRAYLANG = array_merge($_ARRAYLANG, $langData);
        
        $post = is_array($params['post']) ? $params['post'] : array();
        if (    empty($post)
            ||  empty($post['prePosition'])
            ||  empty($post['curPosition'])
            ||  empty($post['sortField'])
            ||  empty($post['component'])
            ||  empty($post['entity'])
        ) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_HTML_UPDATE_SORT_ORDER_FAILED']);
        }
        
        //Get all the 'POST' values
        $sortField       = !empty($post['sortField'])
                           ? contrexx_input2raw($post['sortField'])
                           : '';
        $sortOrder       = !empty($post['sortOrder'])
                           ? contrexx_input2raw($post['sortOrder'])
                           : '';
        $componentName   = !empty($post['component'])
                           ? contrexx_input2raw($post['component'])
                           : '';
        $entityName      = !empty($post['entity'])
                           ? contrexx_input2raw($post['entity'])
                           : '';
        $pagingPosition  = !empty($post['pagingPosition'])
                           ? contrexx_input2int($post['pagingPosition'])
                           : 0;
        $currentPosition = isset($post['curPosition']) 
                           ? contrexx_input2int($post['curPosition'])
                           : 0;
        $prePosition     = isset($post['prePosition']) 
                           ? contrexx_input2int($post['prePosition'])
                           : 0;
        $updatedOrder    = (    isset($post['sortingOrder']) 
                            &&  is_array($post['sortingOrder'])
                           )
                           ? array_map('contrexx_input2int', $post['sortingOrder'])
                           : array();

        $em = $this->cx->getDb()->getEntityManager();
        $componentRepo   = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $objComponent    = $componentRepo->findOneBy(array('name' => $componentName));
        $entityNameSpace = $objComponent->getNamespace() . '\\Model\\Entity\\' . $entityName;
        //check whether the entity namespace is a valid one or not
        if (!in_array($entityNameSpace, $objComponent->getEntityClasses())) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_HTML_UPDATE_SORT_ORDER_FAILED']);
        }
        
        $entityObject = $em->getClassMetadata($entityNameSpace);
        $classMethods = get_class_methods($entityObject->newInstance());
        $primaryKeyName = $entityObject->getSingleIdentifierFieldName();
        //check whether the updating entity set/get method is a valid one or not
        if (    !in_array('set'.ucfirst($sortField), $classMethods)
            ||  !in_array('get'.ucfirst($sortField), $classMethods)
            ||  !in_array('get'.ucfirst($primaryKeyName), $classMethods)
        ) {
            throw new \Exception($_ARRAYLANG['TXT_CORE_HTML_UPDATE_SORT_ORDER_FAILED']);
        }
        
        //update the entities order field in DB
        $oldPosition = $pagingPosition + $prePosition;
        $newPosition = $pagingPosition + $currentPosition;

        $min = min(array($newPosition, $oldPosition));
        $max = max(array($newPosition, $oldPosition));

        $offset = $min - 1;
        $limit  = ($max - $min) + 1;

        $qb = $em->createQueryBuilder();
        $qb ->select('e')
            ->from($entityNameSpace, 'e')
            ->orderBy('e.' . $sortField, $sortOrder);
        if (empty($updatedOrder)) {
            $qb->setFirstResult($offset)
               ->setMaxResults($limit);
        }
        $objResult = $qb->getQuery()->getResult();

        if ($objResult) {
            if (    ($oldPosition > $newPosition && empty($updatedOrder))
                || (!empty($updatedOrder) && $sortOrder == 'DESC')
            ) {
                krsort($objResult);
            }
            $i = 1;
            $recordCount = count($objResult);
            $orderFieldSetMethodName = 'set'.ucfirst($sortField);
            $orderFieldGetMethodName = 'get'.ucfirst($sortField);
            $primaryGetMethodName    = 'get'.ucfirst($primaryKeyName);
            foreach ($objResult as $result) {
                if (!empty($updatedOrder)) {
                    //If the same 'order' field value is repeated,
                    //we need to update all the entries.
                    $id = $result->$primaryGetMethodName();
                    if (in_array($id, $updatedOrder)) {
                        $order   = array_search($id, $updatedOrder);
                        $orderNo = $pagingPosition + $order + 1;
                        if ($sortOrder == 'DESC') {
                            $orderNo = $recordCount - ($pagingPosition + $order);
                        }
                        $result->$orderFieldSetMethodName($orderNo);
                    } else {
                        $result->$orderFieldSetMethodName($i);
                    }
                } else {
                    //If the same 'order' field value is not repeated,
                    //we need to update all the entries between dragged and dropped position
                    $currentOrder = $result->$orderFieldGetMethodName();
                    if ($i == 1) {
                        $firstResult = $result;
                        $sortOrder   = $currentOrder;
                        $i++;
                        continue;
                    } else if ($i == count($objResult)) {
                        $firstResult->$orderFieldSetMethodName($currentOrder);
                        $result->$orderFieldSetMethodName($sortOrder);
                        continue;
                    }
                    $result->$orderFieldSetMethodName($sortOrder);
                    $sortOrder = $currentOrder;
                }
                $i++;
            }
            $em->flush();
        }
        return array('status' => 'success', 'recordCount' => $recordCount);
    }
}
