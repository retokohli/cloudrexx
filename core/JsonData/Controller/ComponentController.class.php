<?php
/**
 * Main controller for JsonData
 * 
 * @copyright   Comvation AG
 * @author Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_jsondata
 */

namespace Cx\Core\JsonData\Controller;

/**
 * Main controller for JsonData
 * 
 * @copyright   Comvation AG
 * @author Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage core_jsondata
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $json = new \Cx\Core\Json\JsonData();
                // TODO: Verify that the arguments are actually present!
                $adapter = contrexx_input2raw($_GET['object']);
                $method = contrexx_input2raw($_GET['act']);
                // TODO: Replace arguments by something reasonable
                $arguments = array('get' => $_GET, 'post' => $_POST);
                echo $json->jsondata($adapter, $method, $arguments);
                die();
                break;
        }
    }

    /**
     * Do something before content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $section;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if ($section == 'JsonData') {
                    // TODO: move this code to /core/Json/...
                    // TODO: handle expired sessions in any xhr callers.
                    $json = new \Cx\Core\Json\JsonData();
                    // TODO: Verify that the arguments are actually present!
                    $adapter = contrexx_input2raw($_GET['object']);
                    $method = contrexx_input2raw($_GET['act']);
                    // TODO: Replace arguments by something reasonable
                    $arguments = array('get' => $_GET, 'post' => $_POST);
                    echo $json->jsondata($adapter, $method, $arguments);
                    die();
                }
                break;
        }
    }

}