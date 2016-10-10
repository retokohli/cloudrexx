<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2016
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
 * Main controller for Cache
 *
 * @copyright   Cloudrexx AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_cache
 */

namespace Cx\Core_Modules\Cache\Controller;

/**
 * Main controller for Cache
 *
 * @copyright   Cloudrexx AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_cache
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    /**
     * cache instance
     * @var \Cx\Core_Modules\Cache\Controller\Cache
     */
    protected $cache;

    /**
     * doctrine cache driver instance
     * @var mixed
     */
    protected $cacheDriver;
    
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Start caching with op cache, user cache and cloudrexx caching
     */
    public function preInit(\Cx\Core\Core\Controller\Cx $cx) {
        if ($this->cx->getMode() == \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            $this->cache = new \Cx\Core_Modules\Cache\Controller\Cache();
        } else { // load CacheLib for other modes than frontend
            //- ATTENTION: never load CacheManager here, because it uses not yet defined constants which will cause a fatal error
            $this->cache = new \Cx\Core_Modules\Cache\Controller\CacheLib();
        }
        $this->cacheDriver = $this->cache->getDoctrineCacheDriver();
        if ($this->cx->getMode() == $cx::MODE_FRONTEND) {
            $this->cache->deactivateNotUsedOpCaches();
        } elseif (!isset($_GET['cmd']) || $_GET['cmd'] != 'settings') {
            $this->cache->deactivateNotUsedOpCaches();
        }
        if (
            $this->cache->getUserCacheEngine() == \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_APC ||
            $this->cache->getOpCacheEngine() == \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_APC
        ) { // when using apc the memory limit can be reduced to 32M to save RAM
            $this->cx->setMemoryLimit(32);
        }
        // start cloudrexx caching
        if ($this->cx->getMode() != \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }
        $this->cache->startContrexxCaching();
    }

    public function postFinalize(&$endcode) {
        if ($this->cx->getMode() != \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }
        $endcode = $this->cache->endContrexxCaching($this->cx->getPage(), $endcode);
    }

    /**
     * Wrapper to fetch an item from cache using the doctrine usercache cachedriver
     * @param string $id Id of the item
     * @return mixed     The item
     */
    public function fetch($id) {
        return $this->cacheDriver->fetch($id);
    }

    /**
     * Wrapper to save an item to cache using the doctrine user usercache cachedriver
     * @param string $id Id of the item
     * @param mixed $data data of the item
     * @param int $lifeTime Expiraton time of the item (if it equals zero, the item never expires)
     */
    public function save($id, $data, $lifeTime = 0) {
        $this->cacheDriver->save($id, $data, $lifeTime);
    }

    /**
     * Wrapper to delete an item from cache using the doctrine usercache cache driver
     * @param string $id Id of the item
     */
    public function delete($id) {
        $this->cacheDriver->delete($id);
    }

    /**
     * Wrapper to flush all cache instances
     */
    public function clearCache($cacheEngine = null) {
        $this->cache->clearCache($cacheEngine);
    }

    /**
     * Wrapper to drop a cache page on reverse proxy cache
     * @param string $urlPatter URL pattern to drop on reverse cache proxy
     */
    public function clearReverseProxyCache($urlPattern) {
        $this->cache->clearReverseProxyCache($urlPattern);
    }

    /**
     * Wrapper to drop all cached ESI/SSI elements
     */
    public function clearSsiCache() {
        $this->cache->clearSsiCache();
    }

    /**
     * Wrapper to drop the ESI cache for a specific call
     * @param string $adapterName (Json)Data adapter name
     * @param string $adapterMethod (Json)Data method name
     * @param array $params (optional) params for (Json)Data method call
     */
    public function clearSsiCachePage($adapterName, $adapterMethod, $params = array()) {
        $this->cache->clearSsiCachePage($adapterName, $adapterMethod, $params);
    }

    /**
     * Wrapper to get randomizedEsiContent
     */
    public function getRandomizedEsiContent($esiContentInfos) {
        return $this->cache->getRandomizedEsiContent($esiContentInfos);
    }

    /**
     * Wrapper to return the ESI/SSI content for a (json)data call
     * @param string $adapterName (Json)Data adapter name
     * @param string $adapterMethod (Json)Data method name
     * @param array $params (optional) params for (Json)Data method call
     * @return string ESI/SSI directives to put into HTML code
     */
    public function getEsiContent($adapterName, $adapterMethod, $params = array()) {
        return $this->cache->getEsiContent($adapterName, $adapterMethod, $params);
    }

    /**
     * Delete all cached file's of the cache system
     * @param object $cacheEngine The user cache engine
     */
    function deleteAllFiles($cacheEngine = null) {
        $this->cache->_deleteAllFiles($cacheEngine);
    }

    /**
     * @return object The doctrine cache driver object
     */
    public function getCacheDriver()
    {
        return $this->cacheDriver;
    }
}
