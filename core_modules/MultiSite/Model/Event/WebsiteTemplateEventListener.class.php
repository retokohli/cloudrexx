<?php

/**
 * This listener manage the websitetemplate
 *  
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Event;

/**
 * Class WebsiteTemplateEventListenerException
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteTemplateEventListenerException extends \Exception {
    
}

/**
 * Class WebsiteTemplateEventListener
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */
class WebsiteTemplateEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * postPersist
     * 
     * @param object $eventArgs
     */
    public function postPersist($eventArgs) {
        \DBG::msg('MultiSite (WebsiteTemplateEventListener): postPersist');
        $this->manageWebsiteTemplatesOnServiceServer($eventArgs);
    }

    /**
     * postUpdate
     *  
     * @param object $eventArgs
     */
    public function postUpdate($eventArgs) {
        \DBG::msg('MultiSite (WebsiteTemplateEventListener): postUpdate');
        $this->manageWebsiteTemplatesOnServiceServer($eventArgs);
    }

    /**
     * manage the website templates on service server
     * 
     * @param object $eventArgs
     * @return type
     * @throws WebsiteTemplateEventListenerException
     */
    public function manageWebsiteTemplatesOnServiceServer($eventArgs) {
        $websiteTemplate = $eventArgs->getEntity();
        if (!$websiteTemplate) {
            return;
        }
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    $websiteServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')->findAll();
                    
                    $objDataSet = new \Cx\Core_Modules\Listing\Model\Entity\DataSet();
                    $objEntityInterface = new \Cx\Core_Modules\Listing\Model\Entity\EntityInterface();
                    
                    $arrayDataSet = $objDataSet->import($objEntityInterface, $websiteTemplate);
                    $param = array();
                    $param['data'] = current($arrayDataSet);
                    $param['dataType'] = get_class($websiteTemplate);
                   
                    foreach ($websiteServiceServers as $websiteServiceServer) {
                        \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('push', $param, $websiteServiceServer);
                    }
                    break;
            }
        } catch (\Exception $e) {
            throw new WebsiteTemplateEventListenerException($e->getMessage());
        }
    }

    /**
     * To call the event
     * 
     * @param string $eventName function name
     * @param array  $eventArgs arguments
     */
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

}
