<?php

/**
 * 
 */

namespace Cx\Core\Html\Model\Entity;

/**
 * 
 */
class FieldsetElement extends HtmlElement {
    
    public function __construct($children = null) {
        parent::__construct('fieldset');
        if (is_array($children)) {
            $this->addChildren($children);
        }
    }
}
