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
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function postPersist($eventArgs) {
        \DBG::msg('MultiSite (WebsiteTemplateEventListener): postPersist');
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    $this->manageWebsiteTemplatesOnServiceServer($eventArgs);
                    break;
            }
        } catch (\Exception $e) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
        }
    }

    /**
     * postUpdate
     *  
     * @param object $eventArgs
     * @throws \Cx\Core\Error\Model\Entity\ShinyException
     */
    public function postUpdate($eventArgs) {
        \DBG::msg('MultiSite (WebsiteTemplateEventListener): postUpdate');
        try {
            switch (\Cx\Core\Setting\Controller\Setting::getValue('mode')) {
                case \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER:
                    $this->manageWebsiteTemplatesOnServiceServer($eventArgs);
                    break;
            }
        } catch (\Exception $e) {
            throw new \Cx\Core\Error\Model\Entity\ShinyException($e->getMessage());
        }
    }

    /**
     * website template changes on service servers
     * 
     * @param type $eventArgs
     */
    public function manageWebsiteTemplatesOnServiceServer($eventArgs) {
        $websiteTemplate = $eventArgs->getEntity();
        if(!$websiteTemplate){
            return;
        }
        $websiteServiceServers = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')->findAll();
        $dataSetObj = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($websiteTemplate);
        $param = array();
        $param['data'] = $dataSetObj->toArray();
        $param['dataType'] = $dataSetObj->getDataType();
        foreach ($websiteServiceServers as $websiteServiceServer) {
           $resp = \Cx\Core_Modules\MultiSite\Controller\JsonMultiSite::executeCommandOnServiceServer('push', $param, $websiteServiceServer);
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
