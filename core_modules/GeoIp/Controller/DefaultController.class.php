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
 * DefaultController
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_geoip
 */

namespace Cx\Core_Modules\GeoIp\Controller;

/**
 * 
 * DefaultController for GeoIp.
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_geoip
 */
class DefaultController extends \Cx\Core\Core\Model\Entity\Controller {
    
    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma  $template
     */
    protected $template;
    
    /**
     * module name
     * @var string $moduleName
     */
    public $moduleName = 'GeoIp';
    
    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    public $moduleNameLang = 'GEOIP';

    /**
     * Controller for the Backend Orders views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
    }

    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template)
    {
        $this->template = $template;
        $this->showOverview();
    }

    /**
     * Display GeoIp settings
     */
    public function showOverview()
    {
        global $_ARRAYLANG;
        
        //save the setting values
        if (isset($_POST['bsubmit'])) {
            \Cx\Core\Setting\Controller\Setting::storeFromPost();
        }
        
        //display the setting options
        \Cx\Core\Setting\Controller\Setting::init('GeoIp', null,'Yaml');
        \Cx\Core\Setting\Controller\Setting::setEngineType('GeoIp', 'Yaml', 'config');    
        \Cx\Core\Setting\Controller\Setting::show(
            $this->template,
            'index.php?cmd=GeoIp',
            $_ARRAYLANG['TXT_CORE_MODULE_GEOIP'],
            $_ARRAYLANG['TXT_CORE_MODULE_GEOIP_SETTINGS'],
            'TXT_CORE_MODULE_GEOIP_'
        );
    }
    
}