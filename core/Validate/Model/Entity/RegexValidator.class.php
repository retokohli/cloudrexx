<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Validate\Model\Entity;
/**
 * Description of RegexValidator
 *
 * @author ritt0r
 */
class RegexValidator extends Validator {
    protected $pattern;
    
    public function __construct($pattern) {
        $this->pattern = $pattern;
    }
    
    public function isValid($data) {
        return (boolean) preg_match($this->pattern, $data);
    }
    
    public function getValidatedData($data) {
        if (!$this->isValid($data)) {
            throw new ValidationException('Validation for data failed (' . get_class($this) . ')');
        }
        return $data;
    }
    
    public function getJavaScriptCode() {
        return '
            if (' . $this->pattern . '.test(jQuery(this).val())) {
                jQuery(this).removeClass(\'error\');
            } else {
                jQuery(this).addClass(\'error\');
            }
        ';
    }
}
