<?php

/**
 * FeedBack
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_support
 */

namespace Cx\Modules\Support\Model\Entity;

class FeedBackException extends \Exception {};

/**
 * Feedback
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_support
 */
class FeedBack extends \Cx\Model\Base\EntityBase {

    /**
     * 
     * @var integer $id 
     */
    protected $id;
    
    /**
     *
     * @var integer $feedBackType
     */
    protected $feedBackType;
    
    /**
     *
     * @var string $subject
     */
    protected $subject;
    
    /**
     *
     * @var string $comment
     */
    protected $comment;

    /**
     *
     * @var string $name
     */
    protected $name;

    /**
     *
     * @var string $email
     */
    protected $email;

    /**
     *
     * @var string $url
     */
    protected $url;

    public function __construct() {}
    
    /**
     * Get the id
     * 
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the id
     * 
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Get the feedback type
     * 
     * @return integer
     */
    public function getFeedBackType() {
        return $this->feedBackType;
    }

    /**
     * Set the feedback type
     * 
     * @param integer $feedBackType
     */
    public function setFeedBackType($feedBackType) {
        $this->feedBackType = $feedBackType;
    }
    
    /**
     * Get the subject
     * 
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Set the subject
     * 
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    /**
     * Get the comment
     * 
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set the comment
     * 
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }
    
    /**
     * Get the name
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name
     * 
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * Get the email
     * 
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the email
     * 
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }
    
    /**
     * Get the url
     * 
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set the url
     * 
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }
    
}