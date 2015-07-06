<?php

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
     * @var string $tokens
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
     * @param array $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * Get token
     *
     * @return array $token
     */
    public function getToken() {
        return $this->token;
    }
    
    /**
     * Set count
     *
     * @param array $count
     */
    public function setCount($count) {
        $this->count = $count;
    }

    /**
     * Get count
     *
     * @return array $count
     */
    public function getCount() {
        return $this->count;
    }

}
