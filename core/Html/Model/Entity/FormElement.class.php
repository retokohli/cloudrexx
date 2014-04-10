<?php

/**
 * 
 */

namespace Cx\Core\Html\Model\Entity;

/**
 * 
 */
class FormElement extends HtmlElement {
    const ENCTYPE_MULTIPART_FORMDATA = 'multipart/formdata';
    
    public function __construct($action, $method = 'post', $enctype = self::ENCTYPE_MULTIPART_FORMDATA) {
        parent::__construct('form');
        $this->setAttributes(array(
            'action' => $action,
            'method' => $method,
            'enctype' => $enctype,
        ));
    }
    
    /**
     * Add children to first fieldset
     */
    public function addChild(HtmlElement $element) {
        if ($element instanceof FieldsetElement) {
            parent::addChild($element);
            return;
        }
        if (!count($this->getChildren())) {
            $this->addChild(new FieldsetElement());
        }
        current($this->getChildren())->addChild($element);
    }
    
    public function addChildren(array $elements) {
        foreach ($elements as $child) {
            $this->addChild($child);
        }
    }
    
    public function isValid() {
        // foreach data field
        foreach ($this->getChildren() as $fieldset) {
            foreach ($fieldset->getChildren() as $child) {
                if ($child instanceof \Cx\Core\Html\Model\Entity\DataElement) {
                    // datafield->isValid()?
                    if (!$child->isValid()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    public function getData() {
        $data = array();
        // foreach data field
        foreach ($this->getChildren() as $fieldset) {
            foreach ($fieldset->getChildren() as $child) {
                if ($child instanceof \Cx\Core\Html\Model\Entity\DataElement) {
                    $data[$child->getIdentifier()] = $child->getData();
                }
            }
        }
        return new \Cx\Core_Modules\Listing\Model\Entity\DataSet(array($data));
    }
    
    public function render() {
        // if no child with name input and type submit is present, add one
        $hasSubmit = false;
        foreach ($this->getChildren() as $child) {
            if ($child->getName() == 'input' && $child->getAttribute('type') == 'submit') {
                $hasSubmit = true;
                break;
            }
        }
        if (!$hasSubmit) {
            $submit = new HtmlElement('input');
            $submit->setAttribute('type', 'submit');
            $this->addChild($submit);
        }
        return parent::render();
    }
}

