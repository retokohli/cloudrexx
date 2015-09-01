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
 * SettingsYellowpay
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_checkout
 */

namespace Cx\Modules\Checkout\Controller;

/**
 * SettingsYellowpay
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_checkout
 */
class SettingsYellowpay {

    /**
     * Database object.
     *
     * @access      private
     * @var         ADONewConnection
     */
    private $objDatabase;

    /**
     * Initialize the database object.
     *
     * @access      public
     * @param       ADONewConnection    $objDatabase
     */
    public function __construct($objDatabase)
    {
        $this->objDatabase = $objDatabase;
    }

    /**
     * Get settings.
     *
     * @access      public
     */
    public function get()
    {
        $objResult = $this->objDatabase->Execute('SELECT `name`, `value` FROM `'.DBPREFIX.'module_checkout_settings_yellowpay`');

        $arrYellowpay = array();
        if ($objResult) {
            while (!$objResult->EOF) {
                $arrYellowpay[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
            return $arrYellowpay;
        } else {
            return false;
        }
    }

    /**
     * Update settings.
     *
     * @access      public
     * @param       array       $arrYellowpay
     */
    public function update($arrYellowpay)
    {
        foreach ($arrYellowpay as $name => $value) {
            $objResult = $this->objDatabase->Execute('
                UPDATE `'.DBPREFIX.'module_checkout_settings_yellowpay`
                SET `value`="'.contrexx_raw2db($value).'"
                WHERE `name`="'.$name.'"
            ');

            if (!$objResult) {
                return false;
            }
        }

        return true;
    }

}
