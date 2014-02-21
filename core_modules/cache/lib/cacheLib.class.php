<?php
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

    function _deleteAllFiles()
    {
        $handleDir = opendir($this->strCachePath);
        if ($handleDir) {
            while ($strFile = readdir($handleDir)) {
                if ($strFile != '.' && $strFile != '..') {
                    unlink($this->strCachePath . $strFile);
                }
            }
            closedir($handleDir);
        }
    }

    /**
     * Delete cache file of page by page id
     *
     * @param int $pageId the page id of cached page
     */
    static public function deleteCacheFileByPageId($pageId)
    {
        foreach (glob(ASCMS_CACHE_PATH . "/*" . $pageId) as $filename) {
            $File = new \Cx\Lib\FileSystem\File($filename);
            $File->delete();
        }
    }
    
    protected function initOPCaching() {
        // APC
        if ($this->isInstalled(self::CACHE_ENGINE_APC)) {
            ini_set('apc.enabled', 1);
            if ($this->isActive(self::CACHE_ENGINE_APC)) {
                $this->opCacheEngines[] = self::CACHE_ENGINE_APC;
            }
        }

        // Disable eAccelerator if active
        if (extension_loaded('eaccelerator')) {
            ini_set('eaccelerator.enable', 0);
            ini_set('eaccelerator.optimizer', 0);
        }

        // Disable zend opcache if it is enabled
        // If save_comments is set to TRUE, doctrine2 will not work properly.
        // It is not possible to set a new value for this directive with php.
        if ($this->isInstalled(self::CACHE_ENGINE_ZEND_OPCACHE)) {
            ini_set('opcache.save_comments', 1);
            ini_set('opcache.load_comments', 1);
            ini_set('opcache.enable', 1);
            
            if (
                !$this->isActive(self::CACHE_ENGINE_ZEND_OPCACHE) ||
                !$this->isConfigured(self::CACHE_ENGINE_ZEND_OPCACHE)
            ) {
                ini_set('opcache.enable', 0);
            } else {
                $this->opCacheEngines[] = self::CACHE_ENGINE_ZEND_OPCACHE;
            }
        }

        // XCache
        if (
            $this->isInstalled(self::CACHE_ENGINE_XCACHE) &&
            $this->isActive(self::CACHE_ENGINE_XCACHE) &&
            $this->isConfigured(self::CACHE_ENGINE_XCACHE)
        ) {
            $this->opCacheEngines[] = self::CACHE_ENGINE_XCACHE;
        }
    }
    
    protected function initUserCaching() {
        // APC
        if ($this->isInstalled(self::CACHE_ENGINE_APC)) {
            // have to use serializer "php", not "default" due to doctrine2 gedmo tree repository
            ini_set('apc.serializer', 'php');
            if (
                $this->isActive(self::CACHE_ENGINE_APC) &&
                $this->isConfigured(self::CACHE_ENGINE_APC, true)
            ) {
                $this->userCacheEngines[] = self::CACHE_ENGINE_APC;
            }
        }
        
        // Memcache
        if ($this->isInstalled(self::CACHE_ENGINE_MEMCACHE)) {
            $memcache = new \Memcache();
            if (@$memcache->connect('localhost', 11211)) {
                $this->memcache = $memcache;
            }
            if ($this->isConfigured(self::CACHE_ENGINE_MEMCACHE)) {
                $this->userCacheEngines[] = self::CACHE_ENGINE_MEMCACHE;
            }
        }

        // XCache
        if (
            $this->isInstalled(self::CACHE_ENGINE_XCACHE) &&
            $this->isActive(self::CACHE_ENGINE_XCACHE) &&
            $this->isConfigured(self::CACHE_ENGINE_XCACHE, true)
        ) {
            $this->userCacheEngines[] = self::CACHE_ENGINE_XCACHE;
        }
    }
    
    protected function getActivatedCacheEngines() {
        global $_CONFIG;
        
        if (in_array($_CONFIG['cacheUserCache'], $this->userCacheEngines)) {
            $this->userCacheEngine = $_CONFIG['cacheUserCache'];
        } else {
            $this->userCacheEngine = current($this->userCacheEngines);
        }
        if (in_array($_CONFIG['cacheOPCache'], $this->opCacheEngines)) {
            $this->opCacheEngine = $_CONFIG['cacheOPCache'];
        } else {
            $this->opCacheEngine = current($this->opCacheEngines);
        }
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
        switch ($cacheEngine) {
            case self::CACHE_ENGINE_APC:
                return extension_loaded('apc');
            case self::CACHE_ENGINE_ZEND_OPCACHE:
                return extension_loaded('opcache') || extension_loaded('Zend OPcache');
            case self::CACHE_ENGINE_MEMCACHE:
                return extension_loaded('memcache') || extension_loaded('memcached');
            case self::CACHE_ENGINE_XCACHE:
                return extension_loaded('xcache');
        }
    }
    
    protected function isActive($cacheEngine) {
        if (!$this->isInstalled($cacheEngine)) {
            return false;
        }
        switch ($cacheEngine) {
            case self::CACHE_ENGINE_APC:
                $setting = 'apc.enabled';
                break;
            case self::CACHE_ENGINE_ZEND_OPCACHE:
                $setting = 'opcache.enable';
                break;
            case self::CACHE_ENGINE_MEMCACHE:
                return $this->memcache ? true : false;
            case self::CACHE_ENGINE_XCACHE:
                $setting = 'xcache.cacher';
                break;
        }
        if (!empty($setting)) {
            $configurations = ini_get_all();
            return $configurations[$setting]['global_value'];
        }
    }
    
    protected function isConfigured($cacheEngine, $user = false) {
        if (!$this->isActive($cacheEngine)) {
            return false;
        }
        switch ($cacheEngine) {
            case self::CACHE_ENGINE_APC:
                if ($user) {
                    return ini_get('apc.serializer') == 'php';
                }
                return true;
            case self::CACHE_ENGINE_ZEND_OPCACHE:
                return ini_get('opcache.save_comments') && ini_get('opcache.load_comments');
            case self::CACHE_ENGINE_MEMCACHE:
                return $this->memcache ? true : false;
            case self::CACHE_ENGINE_XCACHE:
                if ($user) {
                    return ini_get('xcache.var_size') > 0;
                }
                return ini_get('xcache.size') > 0;
        }
    }
    
    /**
     * Flush all user cache instances
     * @see \Cx\Core\ContentManager\Model\Event\PageEventListener on update of page objects
     */
    public function clearUserCache() {
        switch ($this->userCacheEngine) {
            case self::CACHE_ENGINE_APC:
                \apc_clear_cache();
                break;
            case self::CACHE_ENGINE_MEMCACHE:
                $this->memcache->flush();
                break;
            case self::CACHE_ENGINE_XCACHE:
                \xcache_clear_cache();
                break;
            default:
                break;
        }
    }
}
