<?php

/**
 * Class Form
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_contact
 */

namespace Cx\Core_Modules\Contact\Model\Entity;

/**
 * Class Form
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  coremodule_contact
 */
class Form {

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $mails;

    /**
     * @var bool
     */
    protected $showForm;

    /**
     * @var bool
     */
    protected $useCaptcha;

    /**
     * @var bool
     */
    protected $useCustomStyle;

    /**
     * @var bool
     */
    protected $saveDataInCrm;

    /**
     * @var bool
     */
    protected $sendCopy;
    
    /**
     * @var bool
     */
    protected $useEmailOfSender;

    /**
     * @var bool
     */
    protected $htmlMail;
    
    /**
     * @var bool
     */
    protected $sendAttachment;
     
    /*
     * Constructor
     * 
     */
    public function __construct() {
        
        $this->id = 0;
        $this->mails = '';
        $this->showForm = false;
        $this->useCaptcha = true;
        $this->useCustomStyle = false;
        $this->saveDataInCrm = false;
        $this->sendCopy = false;
        $this->useEmailOfSender = false;
        $this->htmlMail = true;
        $this->sendAttachment = false;
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Set mails
     *
     * @param text $mails
     */
    public function setMails($mails) {
        $this->mails = $mails;
    }

    /**
     * Set showForm
     *
     * @param boolean $showForm
     */
    public function setShowForm($showForm) {
        $this->showForm = $showForm;
    }
    
    /**
     * Set useCaptch
     *
     * @param boolean $useCaptcha
     */
    public function setUseCaptcha($useCaptcha) {
        $this->useCaptcha = $useCaptcha;
    }
    
    /**
     * Set useCustomStyle
     *
     * @param boolean $useCustomStyle
     */
    public function setUseCustomStyle($useCustomStyle) {
        $this->useCustomStyle = $useCustomStyle;
    }
    
    /**
     * Set saveDataInCrm
     *
     * @param boolean $saveDataInCrm
     */
    public function setSaveDataInCrm($saveDataInCrm) {
        $this->saveDataInCrm = $saveDataInCrm;
    }
    
    /**
     * Set sendCopy
     *
     * @param boolean $sendCopy
     */
    public function setSendCopy($sendCopy) {
        $this->sendCopy = $sendCopy;
    }
    
    /**
     * Set useEmailOfSender
     *
     * @param boolean $useEmailOfSender
     */
    public function setUseEmailOfSender($useEmailOfSender) {
        $this->useEmailOfSender = $useEmailOfSender;
    }
    
    /**
     * Set htmlMail
     *
     * @param boolean $htmlMail
     */
    public function setHtmlMail($htmlMail) {
        $this->htmlMail = $htmlMail;
    }
    
    /**
     * Set sendAttachment
     *
     * @param boolean $sendAttachment
     */
    public function setSendAttachment($sendAttachment) {
        $this->sendAttachment = $sendAttachment;
    }
    
    /**
     * Get id
     *
     * return integer $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get mails
     *
     * return text $mails
     */
    public function getMails() {
        return $this->mails;
    }

    /**
     * Get showForm
     *
     * return boolean $showForm
     */
    public function getShowForm() {
        return $this->showForm;
    }
    
    /**
     * Get useCaptch
     *
     * return boolean $useCaptch
     */
    public function getUseCaptcha() {
        return $this->useCaptch;
    }
    
    /**
     * Get useCustomStyle
     *
     * return boolean $useCustomStyle
     */
    public function getUseCustomStyle() {
        return $this->useCustomStyle;
    }
    
    /**
     * Get saveDataInCrm
     *
     * return boolean $saveDataInCrm
     */
    public function getSaveDataInCrm() {
        return $this->saveDataInCrm;
    }
    
    /**
     * Get sendCopy
     *
     * return boolean $sendCopy
     */
    public function getSendCopy() {
        return $this->sendCopy;
    }
    
    /**
     * Get useEmailOfSender
     *
     * return boolean $useEmailOfSender
     */
    public function getUseEmailOfSender() {
        return $this->useEmailOfSender;
    }
    
    /**
     * Get htmlMail
     *
     * return boolean $htmlMail
     */
    public function getHtmlMail() {
        return $this->htmlMail;
    }
    
    /**
     * Get sendAttachment
     *
     * return boolean $sendAttachment
     */
    public function getSendAttachment() {
        return $this->sendAttachment;
    }
}    

    