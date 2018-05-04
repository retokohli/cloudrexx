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
 * Main controller for Net
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_net
 */

namespace Cx\Core\Net\Controller;

/**
 * Main controller for Net
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_net
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    public function preInit(\Cx\Core\Core\Controller\Cx $cx) {
        global $_CONFIG;
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $_CONFIG['domainUrl'] = $domainRepo->getMainDomain()->getName();
        \Env::set('config', $_CONFIG);
    }

    /**
     * Convert idn to ascii Format
     *
     * @param string $name
     *
     * @return string
     */
    public static function convertIdnToAsciiFormat($name) {
        if (empty($name)) {
            return;
        }

        if (!function_exists('idn_to_ascii')) {
            \DBG::msg('Idn is not supported in this system.');
        } else {
            $name = idn_to_ascii($name);
        }

        return $name;
    }

    /**
     * Convert idn to utf8 format
     *
     * @param string $name
     *
     * @return string
     */
    public static function convertIdnToUtf8Format($name) {
        if (empty($name)) {
            return;
        }

        if (!function_exists('idn_to_utf8')) {
            \DBG::msg('Idn is not supported in this system.');
        } else {
            $name = idn_to_utf8($name);
        }

        return $name;
    }

    /**
     * Get Host by IP address
     *
     * @param string $ip IP address
     *
     * @return string
     */
    public function getHostByAddr($ip)
    {
        $dnsHostnameLookup = \Cx\Core\Setting\Controller\Setting::getValue(
            'dnsHostnameLookup',
            'Config'
        );
        if ($dnsHostnameLookup != 'on') {
            return $ip;
        }

        return gethostbyaddr($ip);
    }
}
