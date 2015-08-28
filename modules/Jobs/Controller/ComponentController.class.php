<?php

/**
 * Main controller for Jobs
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_jobs
 */

namespace Cx\Modules\Jobs\Controller;

/**
 * Main controller for Jobs
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_jobs
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
        global $_CORELANG, $subMenuTitle, $objTemplate;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $objJobs = new Jobs(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($objJobs->getJobsPage());
                if ($page->getCmd() === 'details') {
                    $objJobs->getPageTitle(\Env::get('cx')->getPage()->getTitle());
                    \Env::get('cx')->getPage()->setTitle($objJobs->jobsTitle);
                    \Env::get('cx')->getPage()->setContentTitle($objJobs->jobsTitle);
                    \Env::get('cx')->getPage()->setMetaTitle($objJobs->jobsTitle);
                }
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:

                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'LegacyContentMaster.html');
                $objTemplate = $this->cx->getTemplate();

                \Permission::checkAccess(148, 'static');

                $subMenuTitle = $_CORELANG['TXT_JOBS_MANAGER'];
                $objJobsManager = new JobsManager();
                $objJobsManager->getJobsPage();
                break;

            default:
                break;
        }
    }

}