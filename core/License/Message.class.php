<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\License;

/**
 * Description of Message
 *
 * @author ritt0r
 */
class Message {
    private $text;
    private $type;
    private $link;
    private $linkTarget;
    
    public function __construct($text, $type, $link, $linkTarget) {
        $this->text = $text;
        $this->type = $type;
        $this->link = $link;
        $this->linkTarget = $linkTarget;
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
}
