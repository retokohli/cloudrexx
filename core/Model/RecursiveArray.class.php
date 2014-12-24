<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core\Model;
/**
 * Description of RecursiveArray
 *
 * @author sebastian.brand
 */
class RecursiveArray extends RecursiveArrayAccess {
    
    public function __construct($data) {
        parent::__construct($data);
    }
}
