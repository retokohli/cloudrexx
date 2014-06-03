<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core_Modules\License;

/**
 * Description of Message
 *
 * @author ritt0r
 */
class Message {
    private $langCode;
    private $text;
    private $type;
    private $link;
    private $linkTarget;
    private $showInDashboard = true;
    
    public function __construct($langCode = null, $text = '', $type = 'alertbox', $link = '', $linkTarget = '_blank', $showInDashboard = true) {
        $this->langCode = $langCode ? $langCode : \FWLanguage::getLanguageCodeById(LANG_ID);
        $this->text = $text;
        $this->type = $type;
        $this->link = $link;
        $this->linkTarget = $linkTarget;
        $this->showInDashboard = $showInDashboard;
    }
    
    public function getLangCode() {
        return $this->langCode;
    }
    
    public function getText() {
        return $this->text;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function getLink() {
        return $this->link;
    }
    
    public function getLinkTarget() {
        return $this->linkTarget;
    }
    
    public function showInDashboard() {
        return $this->showInDashboard;
    }
}
