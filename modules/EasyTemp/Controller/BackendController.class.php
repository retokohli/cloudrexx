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

namespace Cx\Modules\EasyTemp\Controller;

/**
 * Main controller for EasyTemp
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     cloudrexx
 * @subpackage  module_easytemp
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands()
    {
        $commands = array();
        if (\Permission::checkAccess(
                \Cx\Modules\EasyTemp\Controller\ComponentController::PERMISSION_BACKEND,
                'static', true)
        ) {
            $commands[] = 'Setting';
        }
        if (\Permission::checkAccess(
                \Cx\Modules\EasyTemp\Controller\ComponentController::PERMISSION_IMPORT,
                'static', true)
        ) {
            $commands[] = 'Import';
        }
    }

    /**
     * Parse the view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   array               $cmd        The cmd parameter values
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        global $_ARRAYLANG, $subMenuTitle;

        switch ($cmd[0]) {
            case 'Import':
                $import = $this->getController('Import');
                $import->parsePage($template, $cmd);
                break;
            default:
                $subMenuTitle = $_ARRAYLANG['TXT_MODULE_EASYTEMP'];
                $url = \Cx\Core\Routing\Url::fromRequest();
                \Cx\Core\Setting\Controller\Setting::init('EasyTemp', 'setting');
                \Cx\Core\Setting\Controller\Setting::storeFromPost();
                \Cx\Core\Setting\Controller\Setting::show($template, $url,
                    'EasyTemp', $_ARRAYLANG['TXT_MODULE_EASYTEMP'],
                    'TXT_MODULE_EASYTEMP_');
                break;
        }
        //\Message::show();
    }

}
