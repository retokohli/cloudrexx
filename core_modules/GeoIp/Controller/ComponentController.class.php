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
 * This is the controllers for GeoIp
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_geoip
 * @version     1.0.0
 */

namespace Cx\Core_Modules\GeoIp\Controller;

/**
 * This is the main controller for GeoIp
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_geoip
 * @version     1.0.0
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * Returns all Controller class names for this component (except this)
     * 
     * @return array List of Controller class names (without namespace)
     */
    public function getControllerClasses() {
        return array('Backend', 'Default');
    }
    
    /**
     * Do GeoIp processing after content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page the resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page)
    {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                \Cx\Core\Setting\Controller\Setting::init('GeoIp', 'config', 'Yaml');
                $serviceStatus = \Cx\Core\Setting\Controller\Setting::getValue('serviceStatus', 'GeoIp');
                
                //If GeoIp processing is deactivated, skip the process
                if (empty($serviceStatus)) {
                    return;
                }
                
                //Get the country name and code by using the ipaddress through GeoIp2 library
                spl_autoload_register(array($this, 'loadGeoIpClass'));
                $countryDb          = $this->getDirectory().'/Data/GeoLite2-Country.mmdb';
                $availableLocale    = array('de', 'en', 'fr', 'ru', 'es', 'ja', 'pt-BR', 'zh-CN');
                $frontendLangLocale = \FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID);
                $locale             = in_array($frontendLangLocale, $availableLocale) ? $frontendLangLocale : 'en';
                try {
                    $reader = new \GeoIp2\Database\Reader($countryDb, array($locale));
                    $record = $reader->country($_SERVER['REMOTE_ADDR']);
                    $countryName = $record->country->name;
                    $countryCode = $record->country->isoCode;
                } catch (\Exception $e) {
                    \DBG::log($e->getMessage());
                    return;
                }

                //Parse the country name and code
                $objTemplate = $this->cx->getTemplate();
                $objTemplate->setVariable(array(
                    'GEOIP_COUNTRY_NAME' => $countryName,
                    'GEOIP_COUNTRY_CODE' => $countryCode
                ));

                //Set the country name and code as cx.variables
                $objJS = \ContrexxJavascript::getInstance();
                $objJS->setVariable(array(
                    'countryName'   => $countryName,
                    'countryCode'   => $countryCode
                ), 'geoIp');
            break;
        }
    }
    
    /**
     * Load the GeoIp library classes
     * 
     * @param type $className namespace of the class
     * 
     * @return null
     */
    public function loadGeoIpClass($className) 
    {
        $parts = explode('\\', $className);
        if (!in_array($parts[0], array('GeoIp2', 'MaxMind'))) {
            return;
        }
        if ($parts[0] == 'MaxMind') {
            array_unshift($parts, 'MaxMind'); // add virtual name MaxMind in the begining
        }
        $filePath = $this->cx->getCodeBaseLibraryPath() . '/' . implode('/', $parts) . '.php';
        $this->cx->getClassLoader()->loadFile($filePath);
    }
}