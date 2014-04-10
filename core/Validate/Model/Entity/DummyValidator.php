<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Validate\Model\Entity;
/**
 * Description of DummyValidator
 *
 * @author ritt0r
 */
class DummyValidator extends Validator {
    
    public function isValid($data) {
        return true;
    }
    
    public function getValidatedData($data) {
        return $data;
    }
    
    public function getJavaScriptCode() {
        return '
            function(data) {
                return true;
            }
        ';
    }
}
