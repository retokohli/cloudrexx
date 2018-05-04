<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Main controller for Jobs
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_jobs
 */

namespace Cx\Modules\Jobs\Controller;

/**
 * Main controller for Jobs
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_jobs
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function getControllerClasses() {
// Return an empty array here to let the component handler know that there
// does not exist a backend, nor a frontend controller of this component.
        return array('JsonJobs');
    }

    /**
     * Returns a list of JsonAdapter class names
     * 
     * @return array List of ComponentController classes
     */
    public function getControllersAccessableByJson() {
        return array('JsonJobsController');
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

    /**
     * Do something after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        if ($this->cx->getMode() !== \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }

        //Parse the Hot / Latest jobs
        $jobLib = new JobsLibrary();
        $jobLib->parseHotOrLatestJobs($this->cx->getTemplate());
    }
}
