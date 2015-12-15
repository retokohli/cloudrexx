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
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_geoip
 */

namespace Cx\Core_Modules\GeoIp\Controller;

/**
 * Class GeoIpException
 */
class GeoIpException extends \Exception {}

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_geoip
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {

    /**
     * Returns a list of available commands (?act=XY)
     * 
     * @return array list of acts
     */
    public function getCommands() {
        return array();
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
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        $this->template = $template;
        $act = $cmd[0];

        //GeoIp configuration setting
        self::errorHandler();
        $this->connectToController($act);

        \Message::show();
    }

    /**
     * Trigger a controller according to the act param from the url
     * 
     * @param string $act page action parameter
     */
    public function connectToController($act)
    {
        $act = empty($act) ? 'Default' : ucfirst($act);
        $controllerName = __NAMESPACE__ . '\\' . $act . 'Controller';
        if (!$controllerName && !in_array($controllerName, $this->getEntityClasses())) {
            return;
        }
        $objController = $this->getSystemComponentController()->getController($act);
        $objController->parsePage($this->template);
    }

    /**
     * Fixes database errors.   
     * 
     * @return boolean
     * @throws GeoIpException
     */
    static function errorHandler() {

        try {
            \Cx\Core\Setting\Controller\Setting::init('GeoIp', '', 'Yaml');

            //setup config
            \Cx\Core\Setting\Controller\Setting::init('GeoIp', 'config', 'Yaml');
            if (!\Cx\Core\Setting\Controller\Setting::isDefined('serviceStatus')
                && !\Cx\Core\Setting\Controller\Setting::add('serviceStatus',1, 1,
               \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'config')
            ) {
                    throw new GeoIpException("Failed to add Setting entry for GeoIp Service Status");
            }
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
        }

        // Always!
        return false;
    }
}