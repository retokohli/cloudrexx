<?php

/**
 * Main controller for Country
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_country
 */

namespace Cx\Core\Country\Controller;

/**
 * Main controller for Country
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_country
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

        global $_CORELANG, $subMenuTitle;

        $subMenuTitle = $_CORELANG['TXT_CORE_COUNTRY'];
        // TODO: Move this define() somewhere else, allocate the IDs properly
        define('PERMISSION_COUNTRY_VIEW', 145);
        define('PERMISSION_COUNTRY_EDIT', 146);

        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $cachedRoot = $this->cx->getTemplate()->getRoot();

        \Permission::checkAccess(PERMISSION_COUNTRY_VIEW, 'static');

        $objCountry = new Country();
        $objCountry->getPage();

        $this->cx->getTemplate()->setRoot($cachedRoot);
    }

}
