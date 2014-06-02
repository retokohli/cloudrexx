<?php

/**
 * Engine for setting requests
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  Setting
 */

namespace Cx\Core\Setting\Model\Entity;

/**
 * Engine for setting requests
 * @copyright   Comvation AG
 * @author      Manish Thakur <manishthakur@cdnsol.com>
 * @package     contrexx
 * @subpackage  Setting
 */
interface Engine {
    
    
    public static function add($name, $value, $ord=false, $type='text', $values='', $group=null);
    
    public static function delete($name=null, $group=null);
    
    public static function getValue($name);
    
    public static function deleteModule();
    
    public static function splitValues($strValues);
    
    public static function joinValues($arrValues);
    
    public static function errorHandler();
    
    public static function set($name, $value);
    
    public static function updateAll();
    
    public static function update($name);
    
}
