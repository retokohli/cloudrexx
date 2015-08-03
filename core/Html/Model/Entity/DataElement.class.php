<?php

/**
 * 
 */

namespace Cx\Core\Html\Model\Entity;

/**
 * 
 */
class DataElement extends HtmlElement {
    const TYPE_INPUT = 'input';
    const TYPE_SELECT = 'select';
    protected $validator;
    protected $type;
    
    
    public function __construct($name, $value = '', $type = self::TYPE_INPUT, $validator = null) {
        parent::__construct($type);
        $this->validator = $validator;
        $this->type = $type;
        $this->setAttribute('name', $name);
        switch ($type) {
            case self::TYPE_INPUT:
                $this->setAttribute('value', $value);
            break;
            case self::TYPE_SELECT:
                if (is_string($value)) {
                    // this is for customizing only:
                    $this->addChild(new \Cx\Core\Html\Model\Entity\TextElement($value));
                }
            break;
        }
    }
    
    public function isValid() {
        return $this->getValidator()->isValid($this->getData());
    }
    
    public function getValidator() {
        if (!$this->validator) {
            return new \Cx\Core\Validate\Model\Entity\DummyValidator();
        }
        return $this->validator;
    }
    
    public function setValidator($validator) {
        $this->validator = $validator;
    }
    
    public function getIdentifier() {
        switch ($this->type) {
            case self::TYPE_INPUT:
            case self::TYPE_SELECT:
                return $this->getAttribute('name');
                break;
            default:
                return null;
                break;
        }
    }
    
    public function getData() {
        switch ($this->type) {
            case self::TYPE_INPUT:
                return $this->getAttribute('value');
                break;
            default:
                return null;
                break;
        }
    }
    
    public function setData($data) {
        switch ($this->type) {
            case self::TYPE_INPUT:
                $this->setAttribute('value', $data);
                break;
            default:
                // error handling
                break;
        }
    }
    
    public function render() {
        $this->setAttribute('onkeyup', $this->getValidator()->getJavaScriptCode());
        return parent::render();
    }
}
