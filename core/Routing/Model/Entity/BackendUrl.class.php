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
 * An Cloudrexx internal URL pointing to backend mode
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */

namespace Cx\Core\Routing\Model\Entity;

/**
 * An Cloudrexx internal URL pointing to backend mode
 *
 * Extracts component name and arguments from the URL
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */
class BackendUrl extends \Cx\Core\Routing\Model\Entity\Url {

    /**
     * Extracts component name from this URL
     *
     * @return string Component name
     */
    public function getComponent() {
        $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
        array_shift($resolvePathParts);
        array_shift($resolvePathParts);
        if (in_array($resolvePathParts[0], array('', 'index.php'))) {
            return '';
        }
        if (!$this->hasParam('cmd')) {
            $this->setParam('cmd', $resolvePathParts[0]);
            $_REQUEST['cmd'] = $resolvePathParts[0];
            $_GET['cmd'] = $_REQUEST['cmd'];
        }
        return $resolvePathParts[0];
    }
    
    /**
     * Extracts component arguments from this URL
     *
     * @return array Component arguments
     */
    public function getArguments() {
        $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
        array_shift($resolvePathParts);
        array_shift($resolvePathParts);
        if (in_array($resolvePathParts[0], array('', 'index.php'))) {
            return '';
        }
        if (isset($resolvePathParts[1])) {
            if (substr($resolvePathParts[1], -1, 1) == '/') {
                $resolvePathParts[1] = substr($resolvePathParts[1], 0, -1);
            }
            if (!$this->hasParam('act')) {
                $this->setParam('act', $resolvePathParts[1]);
                $_REQUEST['act'] = $resolvePathParts[1];
                $_GET['act'] = $_REQUEST['act'];
            }
            return $resolvePathParts[1];
        }
        return '';
    }
}
