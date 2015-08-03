<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Validate\Model\Entity;
/**
 * Description of Validator
 *
 * @author ritt0r
 */
abstract class Validator {
    
    public abstract function isValid($data);
    
    public abstract function getValidatedData($data);
    
    public abstract function getJavaScriptCode();
}
