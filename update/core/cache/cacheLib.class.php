<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
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
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Class podcast library
 *
 * podcast library class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  coremodule_cache
 * @todo        Edit PHP DocBlocks!
 * @todo        Descriptions are wrong. What is it really?
 */

/**
 * Class podcast library
 *
 * podcast library class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  coremodule_cache
 * @todo        Descriptions are wrong. What is it really?
 */
class cacheLib
{
    var $strCachePath;
    
    /**
     * Alternative PHP Cache extension
     */
    const CACHE_ENGINE_APC = 'apc';
    
    /**
     * memcache(d) extension
     */
    const CACHE_ENGINE_MEMCACHE = 'memcache';
    
    /**
     * xcache extension
     */
    const CACHE_ENGINE_XCACHE = 'xcache';
    
    /**
     * zend opcache extension
     */
    const CACHE_ENGINE_ZEND_OPCACHE = 'zendopcache';
    
    /**
     * file system user cache extension
     */
    const CACHE_ENGINE_FILESYSTEM = 'filesystem';
    
    /**
     * cache off
     */
    const CACHE_ENGINE_OFF = 'off';
    
    /**
     * Used op cache engines
     * @var array Cache engine names, empty for none
     */
    protected $opCacheEngines = array();
    
    /**
     * Used user cache engines
     * @var type array Cache engine names, empty for none
     */
    protected $userCacheEngines = array();
    
    protected $opCacheEngine = null;
    protected $userCacheEngine = null;
    protected $memcache = null;

    /**
     * Delete all cached file's of the cache system   
     */
    function _deleteAllFiles()
    {
        \Env::get('cache')->flushAll();
    }
    
    protected function initOPCaching() {
        return;
    }

    protected function initUserCaching() {
        return;
    }
    
    protected function getActivatedCacheEngines() {
        return;
    }
    
    public function getOpCacheEngine() {
        return $this->opCacheEngine;
    }
    
    public function getUserCacheEngine() {
        return $this->userCacheEngine;
    }
    
    public function getMemcache() {
        return $this->memcache;
    }
    
    public function getAllUserCacheEngines() {
        return array(self::CACHE_ENGINE_APC, self::CACHE_ENGINE_MEMCACHE, self::CACHE_ENGINE_XCACHE);
    }
    
    public function getAllOpCacheEngines() {
        return array(self::CACHE_ENGINE_APC, self::CACHE_ENGINE_ZEND_OPCACHE);
    }
    
    protected function isInstalled($cacheEngine) {
        return false;
    }
    
    protected function isActive($cacheEngine) {
        return false;
    }
    
    protected function isConfigured($cacheEngine, $user = false) {
        return false;
    }
    
    protected function getMemcacheConfiguration() {
        global $_CONFIG;
        $ip = '127.0.0.1';
        $port = '11211';
        return array();
    }
    
    protected function getVarnishConfiguration(){
        global $_CONFIG;
        $ip = '127.0.0.1';
        $port = '8080';
        return array();
    }
    
    /**
     * Flush all cache instances
     * @see \Cx\Core\ContentManager\Model\Event\PageEventListener on update of page objects
     */
    public function clearCache($cacheEngine = null) {
        return;
    }
    
    
    /**
     * Retunrns the CachePrefix related to this Domain
     * @global string $_DBCONFIG
     * @return string CachePrefix
     */
    protected function getCachePrefix(){
        return '';
    }
}
