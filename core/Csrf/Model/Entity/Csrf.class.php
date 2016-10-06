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
 * Class Csrf
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_csrf
 */

namespace Cx\Core\Csrf\Model\Entity;

/**
 * Class Csrf
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_csrf
 */
class Csrf extends \Cx\Model\Base\EntityBase {

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $sessionId
     */
    private $sessionId;

    /**
     * @var string $token
     */
    private $token;
    
    /**
     * @var decimal $count
     */
    private $count;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set sessionId
     *
     * @param string $sessionId
     */
    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    /**
     * Get sessionId
     *
     * @return string $sessionId
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * Get token
     *
     * @return string $token
     */
    public function getToken() {
        return $this->token;
    }
    
    /**
     * Set count
     *
     * @param decimal $count
     */
    public function setCount($count) {
        $this->count = $count;
    }

    /**
     * Get count
     *
     * @return decimal $count
     */
    public function getCount() {
        return $this->count;
    }

}
