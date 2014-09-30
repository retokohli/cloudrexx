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
            case 'em':
                \DBG::msg(__METHOD__.": Setting '$prop' is deprecated. Env::get($prop) always returns the active/preferred instance of $prop.");
                break;

            default:
                self::$props[$prop] = $val;
                break;
        }
    }

    public static function get($prop) {
        switch ($prop) {
            case 'cx':
                return \Cx\Core\Core\Controller\Cx::instanciate();
                break;

            case 'em':
                return \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
                break;

            default:
		        if(isset(self::$props[$prop])) {
                    return self::$props[$prop];
	            }
                break;
        }
        return null;
    }

    /**
     * @deprecated \Env::em() always returns the instance of EntityManager of the active/preferred Cx\Core\Core\Controller\Cx instance
     */
    public static function setEm($em) {
        \DBG::msg(__METHOD__." is deprecated. Env::get('em') always returns the active/preferred instance of EntityManager");
        //self::set('em', $em);
    }

    /**
     * Retrieves the Doctrine EntityManager
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public static function em() {
        return \Cx\Core\Core\Controller\Cx::instanciate()->getDb()->getEntityManager();
    }
}
