<?php

/**
 * Specific Setting for this Component. Use this to interact with the Setting.class.php
 *
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com> (parts)
 * @package     contrexx
 * @subpackage  core_setting
 * @todo        Edit PHP DocBlocks!
 */

namespace Cx\Core\Setting\Model\Entity;


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
