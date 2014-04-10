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
class EmailValidator extends RegexValidator {
    
    public function __construct() {
        parent::__construct('/^[a-zäàáâöôüûñéè0-9!\#\$\%\&\'\*\+\/\=\?\^_\`\{\|\}\~-]+(?:\.[a-zäàáâöôüûñéè0-9!\#\$\%\&\'\*\+\/\=\?\^_\`\{\|\}\~-]+)*@(?:[a-zäàáâöôüûñéè0-9](?:[a-zäàáâöôüûñéè0-9-]*[a-zäàáâöôüûñéè0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/');
    }
}
