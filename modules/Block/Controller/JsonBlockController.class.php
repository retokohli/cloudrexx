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
 * JSON Adapter for Block
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_block
 */

namespace Cx\Modules\Block\Controller;
 
/**
 * JSON Adapter for Block
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_block
 */
class JsonBlockController extends \Cx\Core\Core\Model\Entity\Controller implements \Cx\Core\Json\JsonAdapter
{
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() 
    {
        return 'Block';
    }
    
    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return null;
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array(
            'getCountries' => new \Cx\Core_Modules\Access\Model\Entity\Permission(null, array('get'), true),
        );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }

    /**
     * Get countries from given name
     * 
     * @param array $params Get parameters, 
     * 
     * @return array Array of countries
     */
    public function getCountries($params)
    {
        $countries = array();
        $term = !empty($params['get']['term']) ? contrexx_input2raw($params['get']['term']) : '';
        if (empty($term)) {
            return array(
                'countries' => $countries
            );
        }
        $arrCountries = \Cx\Core\Country\Controller\Country::searchByName($term);
        foreach ($arrCountries as $country) {
            $countries[] = array(
                'id'    => $country['id'],
                'label' => $country['name'],
                'val'   => $country['name'],
            );
        }
        return array(
            'countries' => $countries
        );
    }
}
