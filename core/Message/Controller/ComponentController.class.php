<?php

/**
 * Main controller for Message
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_message
 */

namespace Cx\Core\Message\Controller;

/**
 * Main controller for Message
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_message
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
          
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $objTemplate =  $this->cx->getTemplate();

                // TODO: This would better be handled by the Message class
                if (!empty($objTemplate->_variables['CONTENT_STATUS_MESSAGE'])) {
                    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] =
                            '<div id="alertbox">' .
                            $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] . '</div>';
                }
                if (!empty($objTemplate->_variables['CONTENT_OK_MESSAGE'])) {
                    if (!isset($objTemplate->_variables['CONTENT_STATUS_MESSAGE'])) {
                        $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] = '';
                    }
                    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] .=
                            '<div id="okbox">' .
                            $objTemplate->_variables['CONTENT_OK_MESSAGE'] . '</div>';
                }
                if (!empty($objTemplate->_variables['CONTENT_WARNING_MESSAGE'])) {
                    $objTemplate->_variables['CONTENT_STATUS_MESSAGE'] .=
                            '<div class="warningbox">' .
                            $objTemplate->_variables['CONTENT_WARNING_MESSAGE'] . '</div>';
                }
                break;

            default:
                break;
        }
    }

}
