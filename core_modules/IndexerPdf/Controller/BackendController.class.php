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

namespace Cx\Core_Modules\IndexerPdf\Controller;

/**
 * BackendController
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_indexerdocx
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    /**
     * Set up the backend view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   array               $cmd
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd,
        &$isSingle = false)
    {
// TODO: Should this view be linked in the backend navigation?  Where?
        \Cx\Core\Setting\Controller\Setting::init($this->getName(), 'config');
        \Cx\Core\Setting\Controller\Setting::storeFromPost();
        \Cx\Core\Setting\Controller\Setting::show($template,
            $this->cx->getBackendFolderName() . '/' . $this->getName(), '', '',
            'TXT_CORE_MODULE_INDEXERPDF_');
    }

}
