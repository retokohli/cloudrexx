<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
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
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * JSON Adapter for Jobs module
 * 
 * @copyright   Comvation AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  core_jobs
 */

namespace Cx\Modules\jobs\lib\controllers;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Jobs module
 * 
 * @copyright   Comvation AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  core_jobs
 */
class JsonJobs implements JsonAdapter {
    /**
     * List of messages
     * @var Array 
     */
    private $messages = array();
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'jobs';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('updateJobsHotOffer');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }

    /**
     * Update the job's hot offer status
     * 
     * @return array status of job update
     */
    public function updateJobsHotOffer($params)
    {
        global $_ARRAYLANG;

        $objDatabase = \Env::get('cx')->getDb()->getAdoDb();
        $langData    = \Env::get('init')->loadLanguageData($this->getName());
        $_ARRAYLANG  = array_merge($_ARRAYLANG, $langData);
        
        //get the input values
        $id       = isset($params['post']['id']) ? contrexx_input2int($params['post']['id']) : 0;
        $hotOffer = isset($params['post']['hotOffer']) ? contrexx_input2int($params['post']['hotOffer']) : 0;

        if (empty($id)) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_JOBS_RECORD_UPDATE_FAILED']);
        }

        $query = 'UPDATE `' . DBPREFIX . 'module_jobs` 
                    SET `hot` = ' . $hotOffer . ' WHERE `id` = ' . $id;
        if (!$objDatabase->Execute($query)) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_JOBS_RECORD_UPDATE_FAILED']);
        }

        return array('status' => 'success', 'message' => $_ARRAYLANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL']);
    }
}