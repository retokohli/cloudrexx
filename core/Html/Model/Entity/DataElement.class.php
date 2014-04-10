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
    protected $validator;
    protected $type;
    
    
    public function __construct($name, $value = '', $type = self::TYPE_INPUT, $validator = null) {
        parent::__construct($type);
        $this->validator = $validator;
        $this->type = $type;
        $this->setAttributes(array(
            'name' => $name,
            'value' => $value,
        ));
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
    
    public function getIdentifier() {
        switch ($this->type) {
            case self::TYPE_INPUT:
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
}
