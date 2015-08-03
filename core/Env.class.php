<?php

/**
 * Env
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */

/**
 * A global environment repository.
 *
 * In old code, use this instead of global variables - allows central tracking
 * of dependencies.
 * Do *NOT* use this in new code, inject dependencies instead.
 * Example: 
 * WRONG:
 * public function __construct() {
 *     $this->entityManager = Env::get('em');
 * }
 * RIGHT:
 * public function __construct($em) {
 *     $this->entityManager = $em;
 * }
 * Reason: Global state is untestable and leads to inflexible code.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */
class Env {
    protected static $props = array();
    protected static $em;

    public static function set($prop, &$val) {
        switch ($prop) {
            case 'cx':
                // set is only used for installerCx. Normal cx class will load with \Env::get('cx')
                self::$props[$prop] = $val;
                \DBG::msg(__METHOD__.": Setting '$prop' is deprecated. Use only for installer, otherwise use \\Env::('$prop')");
                \DBG::stack();
                break;
            case 'em':
                self::$props[$prop] = $val;
                \DBG::msg(__METHOD__.": Setting '$prop' is deprecated. Env::get($prop) always returns the active/preferred instance of $prop.");
                \DBG::stack();
                break;

            default:
                self::$props[$prop] = $val;
                break;
        }
    }

    public static function get($prop) {
        switch ($prop) {
            case 'em':
                return \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
                break;
            case 'cx':
                if (!isset(self::$props[$prop]) && class_exists('\Cx\Core\Core\Controller\Cx')) {
                    return \Cx\Core\Core\Controller\Cx::instanciate();
                }
            default:
		        if(isset(self::$props[$prop])) {
                    return self::$props[$prop];
	            }
                break;
        }
        return null;
    }

    /**
     * Clear the value of a prop
     *
     * @access public
     * @param $prop indexname we want to unset
     * @return void
     */
    public static function clear($prop) {
        if (isset(self::$props[$prop])) {
            unset(self::$props[$prop]);
        }
    }
}
