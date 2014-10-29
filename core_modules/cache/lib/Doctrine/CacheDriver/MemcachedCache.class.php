<?php
/**
 * Memcached Driver Adapter for doctrine
 *
 * @copyright   Comvation AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @package     contrexx
 * @subpackage  coremodules_cache
 */

namespace Cx\Core_Modules\Cache\lib\Doctrine\CacheDriver;

use \Memcached;

/**
 * Memcached Driver Adapter for doctrine
 *
 * @copyright   Comvation AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @package     contrexx
 * @subpackage  coremodules_cache
 */
class MemcachedCache extends \Doctrine\Common\Cache\MemcacheCache
{
    /**
     * @var Memcache
     */
    private $_memcache;

    /**
     * Sets the memcache instance to use.
     *
     * @param Memcached $memcache
     */
    public function setMemcache(Memcached $memcache)
    {
        $this->_memcache = $memcache;
    }

    /**
     * Gets the memcache instance used by the cache.
     *
     * @return Memcached
     */
    public function getMemcache()
    {
        return $this->_memcache;
    }

    /**
     * {@inheritdoc}
     */
    protected function _doSave($id, $data, $lifeTime = 0)
    {
        return $this->_memcache->set($id, $data, (int) $lifeTime);
    }
}
