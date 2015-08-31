<?php
/**
 * This file is used for the ComponentController of the core html
 *
 * @copyright CONTREXX CMS - COMVATION AG
 * @author Adrian Berger <ab@comvation.com>
 * @package contrexx
 * @subpackage core_html
 * @version 1.0.0
 */
namespace Cx\Core\Html\Controller;

/**
 * This class is used as controller for core html. It is also a SystemComponentController
 * Its used to handle json request to ViewGenerator and FormGenerator
 *
 * @copyright CONTREXX CMS - COMVATION AG
 * @author Adrian Berger <ab@comvation.com>
 * @package contrexx
 * @subpackage core_html
 * @version 1.0.0
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * This functions returns all classes which are accessable over ajax
     *
     * @access public
     * @return array Accessable Classes
     */
    public function getControllersAccessableByJson()
    {
        return array('ViewGeneratorJsonController');
    }

    /**
     * Returns all Controller class names for this component (except this)
     *
     * Be sure to return all your controller classes if you add your own
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses() {
        return array('ViewGeneratorJson');
    }
}
