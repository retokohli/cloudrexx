<?php

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  modules_uploader
 */

namespace Cx\Core_Modules\Uploader\Controller;


class UploaderConfiguration
{

    protected static $thumbnails;

    /**
     * @var self reference to singleton instance
     */
    protected static $instance;

    /**
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;

    /**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * is not allowed to call from outside: private!
     *
     */
    protected function __construct()
    {
        $this->cx = \Env::get('cx');
        $this->loadThumbnails();
    }

    public function loadThumbnails()
    {
        /**
         * @var $cx \Cx\Core\Core\Controller\Cx
         */
        $pdo              = $this->cx->getDb()->getPdoConnection();
        $sth              = $pdo->query(
                'SELECT id,name, size,  100 as quality, CONCAT(".thumb_",name) as value FROM  `' . DBPREFIX
                . 'settings_thumbnail`'
        );
        \DBG::log($sth->errorCode());
        self::$thumbnails = $sth->fetchAll();
    }


    /**
     * @return array
     */
    public static function getThumbnails()
    {
        return self::$thumbnails;
    }

    /**
     * prevent the instance from being cloned
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * prevent from being unserialized
     *
     * @return void
     */
    protected function __wakeup()
    {
    }

}
