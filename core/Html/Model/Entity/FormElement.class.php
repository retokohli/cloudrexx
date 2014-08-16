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
    public $cancelUrl = null;
    
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
    public function addChild(HtmlElement $element, HtmlElement $reference = null, $before = false) {
        if ($element instanceof FieldsetElement) {
            parent::addChild($element, $reference, $before);
            return;
        }
        if (!count($this->getChildren())) {
            $this->addChild(new FieldsetElement());
        }
        current($this->getChildren())->addChild($element, $reference, $before);
    }
    
    public function addChildren(array $elements, HtmlElement $reference = null, $before = false) {
        foreach ($elements as $child) {
            $this->addChild($child, $reference, $before);
            $before = false;
            $reference = $child;
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
    
    public function getData($element = null, &$data = array()) {
        $internalRequest = true;
        if (!$element) {
            $element = $this;
            $internalRequest = false;
        }
        
        foreach ($element->getChildren() as $subElement) {
            if ($subElement instanceof \Cx\Core\Html\Model\Entity\DataElement) {
                $data[$subElement->getIdentifier()] = $subElement->getData();
                if (isset($_POST[$subElement->getIdentifier()])) {
                    if ($subElement->getValidator()->isValid(contrexx_input2raw($_POST[$subElement->getIdentifier()]))) {
                        $data[$subElement->getIdentifier()] = contrexx_input2raw($_POST[$subElement->getIdentifier()]);
                        $subElement->setData(contrexx_input2raw($_POST[$subElement->getIdentifier()]));
                    }
                }
            } else {
                $this->getData($subElement, $data);
            }
        }
        if ($internalRequest) {
            return $data;
        }
        return new \Cx\Core_Modules\Listing\Model\Entity\DataSet(array($data));
    }
    
    public function render() {
        global $_CORELANG;

        // if no child with name input and type submit is present, add one
        $hasSubmit = false;
        foreach ($this->getChildren() as $child) {
            if ($child->getName() == 'input' && $child->getAttribute('type') == 'submit') {
                $hasSubmit = true;
                break;
            }
        }
        if (!$hasSubmit) {
            $submitDiv = new HtmlElement('div');
            $submitDiv->setAttribute('class', 'actions');
            $submit = new HtmlElement('input');
            $submit->setAttribute('type', 'submit');
            $submit->setAttribute('value', $_CORELANG['TXT_SAVE']);
            $submitDiv->addChild($submit);
            if(!empty($this->cancelUrl)){
                $cancel = new HtmlElement('input');
                $cancel->setAttribute('type', 'button');
                $cancel->setAttribute('value', $_CORELANG['TXT_CANCEL']);
                $cancel->setAttribute('onclick', "location.href='".$this->cancelUrl."&csrf=".\Cx\Core\Csrf\Controller\Csrf::code()."'");
                $submitDiv->addChild($cancel);
            }
            $this->addChild($submitDiv);
        }
        return parent::render();
    }
}

