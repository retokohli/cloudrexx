<?php
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
 */
class Env {
    protected static $props = array();
    protected static $em;

    public static function set($prop, &$val) {
        self::$props[$prop] = $val;
    }

    public static function get($prop) {
        if(isset(self::$props[$prop])) {
            return self::$props[$prop];
        }
        return null;
    }

    public static function setEm($em) {
        self::set('em', $em);
    }
    /**
     * Retrieves the Doctrine EntityManager
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public static function em()
    {
        return self::get('em');
    }
}