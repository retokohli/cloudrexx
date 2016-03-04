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

namespace Cx\Core\DateTime\Controller;

/**
 * ComponentControlle for DateTime component
 * This component handles timezones and provides methods for conversion
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_datetime
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    /**
     * @var \DateTimeZone Internal timezone
     */
    protected $internalTimezone;
    
    /**
     * @var \DateTimeZone User's timezone
     */
    protected $userTimezone;
    
    /**
     * Returns the controller class names for this component
     * @return array List of controller names
     */
    public function getControllerClasses() {
        return array();
    }
    
    /**
     * Sets the user's and the internal timezone
     * @param \Cx\Core\Routing\Url $request Request URL
     */
    public function preResolve(\Cx\Core\Routing\Url $request) {
        $internalTimezoneString = \Cx\Core\Setting\Controller\Setting::getValue(
            'timezone',
            'Config'
        );
        $this->internalTimezone = new \DateTimeZone($internalTimezoneString);
        
        $this->userTimezone = \FWUser::getFWUserObject()->objUser->getTimezone();
    }
    
    /**
     * Accepts a datetime in internal timezone and converts it to user's timezone
     * @param \DateTime $datetime DateTime in internal timezone
     * @return \DateTime DateTime in user's timezone
     */
    public function db2user(\DateTime $datetime) {
        return $datetime->setTimezone($this->userTimezone);
    }
    
    /**
     * Accepts a datetime in user's timezone and converts it to internal timezone
     * @param \DateTime $datetime DateTime in user's timezone
     * @return \DateTime DateTime in internal timezone
     */
    public function user2db(\DateTime $datetime) {
        if ($datetime->getTimezone()->getName() == $this->internalTimezone->getName()) {
            $dateTimeString = $datetime->format('Y-m-d H:i:s');
            $datetime = new \DateTime($dateTimeString, $this->userTimezone);
        }
        return $datetime->setTimezone($this->internalTimezone);
    }
}

