<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Html\Model\Entity;
/**
 * Description of TextElement
 *
 * @author ritt0r
 */
class TextElement extends HtmlElement {
    private $content;
    
    public function __construct($content) {
        parent::__construct('');
        $this->content = $content;
    }
    
    public function render() {
        return $this->content;
    }
}
