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
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Cloudrexx AG
 * @author      Thomas Wirz <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_jobs
 */

namespace Cx\Modules\Jobs\Controller;


/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Cloudrexx AG
 * @author      Thomas Wirz <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_jobs
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands()
    {
        return array(
            'add',
            'cat',
            'loc',
            'settings',
        );
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     *
     * @param \Cx\Core\Html\Sigma $template template for current CMD
     * @param array               $cmd      CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd, &$isSingle = false) {
        \Permission::checkAccess(148, 'static');
        $objJobsManager = new JobsManager($template);
        $objJobsManager->getJobsPage();
    }
}
