<?php
/**
 * Main controller for Captcha
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_captcha
 */

namespace Cx\Core_Modules\Captcha\Controller;

/**
 * Main controller for Captcha
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_captcha
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }
      /**
     * Do something before resolving is done
     * 
     * @param \Cx\Core\Routing\Url                      $request    The URL object for this request
     */
    public function preResolve(\Cx\Core\Routing\Url $request) {
        global $url;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $params = $url->getParamArray();
                if (isset($params['section']) && $params['section'] == 'Captcha') {
                    /*
                    * Captcha Module
                    *
                    * Generates no output, requests are answered by a die()
                    * @since   2.1.5
                    */
                    Captcha::getInstance()->getPage();
                }
                break;

            default:
                break;
        }
    }

}
