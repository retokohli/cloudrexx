<?php
/**
 * Main controller for Stats
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage coremodule_stats
 */

namespace Cx\Core_Modules\Stats\Controller;

/**
 * Main controller for Stats
 * 
 * @copyright   comvation
 * @author      Project Team SS4U <info@comvation.com>
 * @package contrexx
 * @subpackage coremodule_stats
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * getControllerClasses
     * 
     * @return type
     */
    public function getControllerClasses() {
        return array();
    }

     /**
     * Load the component Stats.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $subMenuTitle, $objTemplate, $_CORELANG;
        
        \Permission::checkAccess(163, 'static');
        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
        $objTemplate = $this->cx->getTemplate();

        $subMenuTitle = $_CORELANG['TXT_STATISTIC'];
        $statistic= new \Cx\Core_Modules\Stats\Controller\Stats();
        $statistic->getContent();
    }
    
     /**
     * Do something before content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $objCounter;
        // Initialize counter and track search engine robot
        $objCounter = new \Cx\Core_Modules\Stats\Controller\StatsLibrary();
        $objCounter->checkForSpider();
        
    }

}
