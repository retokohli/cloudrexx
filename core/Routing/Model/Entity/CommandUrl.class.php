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
 * An Cloudrexx internal URL pointing to command mode
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */

namespace Cx\Core\Routing\Model\Entity;

/**
 * An Cloudrexx internal URL pointing to command mode
 *
 * Extracts command name and arguments from the URL
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 * @link        http://www.cloudrexx.com/ cloudrexx homepage
 * @since       v5.0.0
 */
class CommandUrl extends \Cx\Core\Routing\Model\Entity\Url {
    
    /**
     * Extracts command name from this URL
     *
     * @todo There's bit of a hacky solution for CLI mode
     * @return string Command name
     */
    public function getCommandName() {
        switch ($this->getScheme()) {
            case 'file':
                global $argv;
                $resolvePathParts = $argv;
                array_shift($resolvePathParts);
                break;
            default:
                $resolvePathParts = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
                array_shift($resolvePathParts);
                array_shift($resolvePathParts);
                break;
        }
        return (string) current($resolvePathParts);
    }
    
    /**
     * Extracts command arguments from this URL
     *
     * @todo There's bit of a hacky solution for CLI mode
     * @return array Command arguments
     */
    public function getCommandArguments() {
        switch ($this->getScheme()) {
            case 'file':
                global $argv;
                array_shift($argv);
                array_shift($argv);
                return $argv;
                break;
            default:
                $arguments = explode($this->getPathDelimiter(), $this->getPathWithoutOffset());
                array_shift($arguments);
                array_shift($arguments);
                array_shift($arguments);
                $arguments += $_GET;
                return $arguments;
                break;
        }
    }
}
