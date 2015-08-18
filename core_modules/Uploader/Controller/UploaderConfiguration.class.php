<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
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
