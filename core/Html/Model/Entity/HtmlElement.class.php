<?php

/**
 * 
 */

namespace Cx\Core\Html\Model\Entity;

/**
 * 
 */
class HtmlElement {
    private $name;
    private $attributes = array();
    private $children = array();
    private $output = null;
    
    public function __construct($elementName) {
        $this->setName($elementName);
    }
    
    public function getName() {
        return $this->name;
    }
    
    protected function setName($elementName) {
        $this->output = null;
        $this->name = $elementName;
    }
    
    public function setAttribute($name, $value = null) {
        if ($value === null) {
            $value = $name;
        }
        $this->output = null;
        $this->attributes[$name] = $value;
    }
    
    public function setAttributes($attributes) {
        $this->output = null;
        $this->attributes += $attributes;
    }
    
    public function getAttribute($name) {
        if (!isset($this->attributes[$name])) {
            return null;
        }
        return $this->attributes[$name];
    }
    
    public function getAttributes() {
        return $this->attributes;
    }
    
    public function getChildren() {
        return $this->children;
    }
    
    public function addChild(HtmlElement $element, HtmlElement $reference = null, $before = false) {
        $this->output = null;
        if (!$reference) {
            $this->children[] = $element;
            return true;
        }
        
        $key = array_search($reference, $this->children);
        if ($key === false) {
            return false;
        }
        
        if (!$before) {
            $key++;
        }
        array_splice($this->children, $key, 0, array($element));
        return true;
    }
    
    public function addChildren(array $elements, HtmlElement $reference = null, $before = false) {
        $this->output = null;
        if (!$reference) {
            $this->children += $elements;
            return true;
        }
        foreach ($elements as $element) {
            if (!$this->addChild($element, $reference, $before)) {
                return false;
            }
            $before = false;
            $reference = $element;
        }
        return true;
    }
    
    /* addChildAfter, removeChild, getNthChild */
    
    public function render() {
        if ($this->output) {
            return $this->output;
        }
        $template = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseCorePath() . '/Html/View/Template/Generic');
        $template->loadTemplateFile('HtmlElement.html');
        $parsedChildren = null;
        foreach ($this->getChildren() as $child) {
            $parsedChildren .= $child->render();
        }
        $template->setVariable(array(
            'ELEMENT_NAME' => $this->name,
        ));
        if ($parsedChildren === null) {
            $template->hideBlock('children');
            $template->touchBlock('nochildren');
        } else {
            $template->hideBlock('nochildren');
            $template->setVariable(array(
                'CHILDREN' => $parsedChildren,
            ));
        }
        foreach ($this->getAttributes() as $name=>$value) {
            $template->setVariable(array(
                'ATTRIBUTE_NAME' => $name,
                'ATTRIBUTE_VALUE' =>  preg_replace(array("/{/","/}/"), array("&#123;","&#125;"), contrexx_raw2xhtml($value), -1), //replaces curly brackets, so they get not parsed with the sigma engine
            ));
            $template->parse('attribute');
        }
        $this->output = $template->get();
        return $this->output;
    }

    public function __toString() {
        return $this->render();
    }
}
