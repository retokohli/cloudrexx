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
 * Class Cache Library
 *
 * Cache Library class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  coremodule_cache
 * @todo        Edit PHP DocBlocks!
 * @todo        Descriptions are wrong. What is it really?
 */
namespace Cx\Core_Modules\Cache\Controller;
/**
 * Class Cache Library
 *
 * Cache Library class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @access      public
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  coremodule_cache
 * @todo        Descriptions are wrong. What is it really?
 */
class CacheLib
{
    var $strCachePath;

    /**
     * Alternative PHP Cache extension
     */
    const CACHE_ENGINE_APC = 'apc';

    /**
     * memcache extension
     */
    const CACHE_ENGINE_MEMCACHE = 'memcache';

    /**
     * memcache(d) extension
     */
    const CACHE_ENGINE_MEMCACHED = 'memcached';

    /**
     * xcache extension
     */
    const CACHE_ENGINE_XCACHE = 'xcache';

    /**
     * zend opcache extension
     */
    const CACHE_ENGINE_ZEND_OPCACHE = 'zendopcache';

    /**
     * cache off
     */
    const CACHE_ENGINE_OFF = 'off';

    /**
     * Page cache directory offset
     */
    const CACHE_DIRECTORY_OFFSET_PAGE = 'page/';

    /**
     * ESI cache directory offset
     */
    const CACHE_DIRECTORY_OFFSET_ESI = 'esi/';

    /**
     * File name for page cache locale data
     */
    const LOCALE_CACHE_FILE_NAME = 'Locale.dat';

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
    protected $memcached = null;

    /**
     * @var \Doctrine\Common\Cache\AbstractCache doctrine cache engine for the active user cache engine
     */
    protected $doctrineCacheEngine = null;

    /**
     * @var \Cx\Lib\ReverseProxy\Model\Entity\ReverseProxyProxy SSI proxy
     */
    protected $ssiProxy;

    /**
     * @var \Cx\Core\Json\JsonData
     */
    protected $jsonData = null;

    /**
     * Prefix to be used to identify cache entries in shared caching
     * environments.
     *
     * @var string
     */
    protected $cachePrefix;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setCachePath();
        $this->initOPCaching();
        $this->initUserCaching();
        $this->getActivatedCacheEngines();
    }

    /**
     * Delete all cached file's of the cache system
     */
    function _deleteAllFiles($cacheEngine = null)
    {
        if (!in_array($cacheEngine, array('cxPages', 'cxEntries'))) {
            $this->getDoctrineCacheDriver()->deleteAll();
            return;
        }
        $handleDir = opendir(
            $this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE
        );
        if ($handleDir) {
            while ($strFile = readdir($handleDir)) {
                if ($strFile != '.' && $strFile != '..') {
                    switch ($cacheEngine) {
                        case 'cxPages':
                            if (is_file(
                                $this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE . $strFile
                            )) {
                                unlink(
                                    $this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE . $strFile
                                );
                            }
                            break;
                        case 'cxEntries':
                            $this->getDoctrineCacheDriver()->deleteAll();
                            break;
                        default:
                            unlink(
                                $this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE . $strFile
                            );
                            break;
                    }
                }
            }
            closedir($handleDir);

            if ($cacheEngine == 'cxPages') {
                $cx = \Cx\Core\Core\Controller\Cx::instanciate(); 
                $this->setCachedLocaleData($cx);
            } 
        }
    }

    /**
     * Sets the cache path
     */
    protected function setCachePath() {
        // check the cache directory
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $this->checkCacheDir($cx);
        $this->strCachePath = $cx->getWebsiteCachePath() . '/';
    }

    /**
     * Makes sure that the cache directory exists and is writable
     * @param \Cx\Core\Core\Controller\Cx $cx The contrexx instance
     */
    protected function checkCacheDir($cx) {
        if (!is_dir($cx->getWebsiteCachePath())) {
            \Cx\Lib\FileSystem\FileSystem::make_folder($cx->getWebsiteCachePath());
        }
        if (!is_writable($cx->getWebsiteCachePath())) {
            \Cx\Lib\FileSystem\FileSystem::makeWritable($cx->getWebsiteCachePath());
        }
    }

    protected function initOPCaching()
    {
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
            if (!ini_get('opcache.enable')) {
                @ini_set('opcache.enable', 1);
            }

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

    protected function initUserCaching()
    {
        global $_CONFIG;

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
        if (   $this->isInstalled(self::CACHE_ENGINE_MEMCACHE)
            && (\Env::get('cx')->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND
            || $_CONFIG['cacheUserCache'] == self::CACHE_ENGINE_MEMCACHE)
        ) {
            $memcacheConfiguration = $this->getMemcacheConfiguration();
            unset($this->memcache); // needed for reinitialization
            if (class_exists('\Memcache')) {
                $memcache = new \Memcache();
                if (@$memcache->addServer($memcacheConfiguration['ip'], $memcacheConfiguration['port'])) {
                    $this->memcache = $memcache;
                }
            }
            if ($this->isConfigured(self::CACHE_ENGINE_MEMCACHE)) {
                $this->userCacheEngines[] = self::CACHE_ENGINE_MEMCACHE;
            }
        }

        // Memcached
        if (   $this->isInstalled(self::CACHE_ENGINE_MEMCACHED)
            && (\Env::get('cx')->getMode() == \Cx\Core\Core\Controller\Cx::MODE_BACKEND
            || $_CONFIG['cacheUserCache'] == self::CACHE_ENGINE_MEMCACHED)
        ) {
            $memcachedConfiguration = $this->getMemcachedConfiguration();
            unset($this->memcached); // needed for reinitialization
            if (class_exists('\Memcached')) {
                $memcached = new \Memcached();
                $memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, false);
                if (@$memcached->addServer($memcachedConfiguration['ip'], $memcachedConfiguration['port'])) {
                    $servers = $memcached->getStats();
                    if (!empty($servers) &&
                        isset($servers[$memcachedConfiguration['ip'] . ':' . $memcachedConfiguration['port']])
                    ) {
                        $this->memcached = $memcached;
                    }
                }
            }
            if ($this->isConfigured(self::CACHE_ENGINE_MEMCACHED)) {
                $this->userCacheEngines[] = self::CACHE_ENGINE_MEMCACHED;
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

    protected function getActivatedCacheEngines()
    {
        global $_CONFIG;

        $this->userCacheEngine = self::CACHE_ENGINE_OFF;
        if (   isset($_CONFIG['cacheUserCache'])
            && in_array($_CONFIG['cacheUserCache'], $this->userCacheEngines)
        ) {
            $this->userCacheEngine = $_CONFIG['cacheUserCache'];
        }

        $this->opCacheEngine = self::CACHE_ENGINE_OFF;
        if (   isset($_CONFIG['cacheOPCache'])
            && in_array($_CONFIG['cacheOPCache'], $this->opCacheEngines)
        ) {
            $this->opCacheEngine = $_CONFIG['cacheOPCache'];
        }

        // if system is configured for "intern" or not correctly configured
        $proxySettings = $this->getSsiProcessorConfiguration();
        if (
            !isset($_CONFIG['cacheSsiOutput']) ||
            $_CONFIG['cacheSsiOutput'] == 'intern' ||
            !in_array(
                $_CONFIG['cacheSsiOutput'],
                array(
                    'intern',
                    'ssi',
                    'esi',
                )
            ) ||
            !in_array(
                $_CONFIG['cacheSsiType'],
                array(
                    'varnish',
                    'nginx',
                )
            )
        ) {
            $this->ssiProxy = new \Cx\Core_Modules\Cache\Model\Entity\ReverseProxyCloudrexx(
                $proxySettings['ip'],
                $proxySettings['port']
            );
            return;
        }
        $className = '\\Cx\\Lib\\ReverseProxy\\Model\\Entity\\SsiProcessor' . ucfirst($_CONFIG['cacheSsiOutput']);
        $ssiProcessor = new $className();
        $className = '\\Cx\\Lib\\ReverseProxy\\Model\\Entity\\ReverseProxy' . ucfirst($_CONFIG['cacheSsiType']);
        $this->ssiProxy = new $className(
            $proxySettings['ip'],
            $proxySettings['port'],
            $ssiProcessor
        );
    }

    public function deactivateNotUsedOpCaches()
    {
        if (empty($this->opCacheEngine)) {
            $this->getActivatedCacheEngines();
        }
        $opCacheEngine = $this->opCacheEngine;
        if (!$this->getOpCacheActive()) {
            $opCacheEngine = self::CACHE_ENGINE_OFF;
        }

        // deactivate other op cache engines
        foreach ($this->opCacheEngines as $engine) {
            if ($engine != $opCacheEngine) {
                switch ($engine) {
                    case self::CACHE_ENGINE_APC:
                        ini_set('apc.cache_by_default', 0);
                        break;
                    case self::CACHE_ENGINE_ZEND_OPCACHE:
                        ini_set('opcache.enable', 0);
                        break;
                    case self::CACHE_ENGINE_XCACHE:
                        ini_set('xcache.cacher', 0);
                        break;
                }
            }
        }
    }

    public function getUserCacheActive()
    {
        global $_CONFIG;
        return
            isset($_CONFIG['cacheDbStatus'])
            && $_CONFIG['cacheDbStatus'] == 'on';
    }

    public function getOpCacheActive() {
        global $_CONFIG;
        return
            isset($_CONFIG['cacheOpStatus'])
            && $_CONFIG['cacheOpStatus'] == 'on';
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

    /**
     * @return \Memcache The memcached object
     */
    public function getMemcached() {
        return $this->memcached;
    }

    public function getAllUserCacheEngines() {
        return array(self::CACHE_ENGINE_APC, self::CACHE_ENGINE_MEMCACHE, self::CACHE_ENGINE_MEMCACHED, self::CACHE_ENGINE_XCACHE);
    }

    public function getAllOpCacheEngines() {
        return array(self::CACHE_ENGINE_APC, self::CACHE_ENGINE_ZEND_OPCACHE);
    }

    /**
     * Returns the current SSI proxy
     * @return \Cx\Lib\ReverseProxy\Model\Entity\ReverseProxy SSI proxy
     */
    public function getSsiProxy() {
        return $this->ssiProxy;
    }

    /**
     * Returns the ESI/SSI content for a (json)data call
     * @param string $adapterName (Json)Data adapter name
     * @param string $adapterMethod (Json)Data method name
     * @param array $params (optional) params for (Json)Data method call
     * @return string ESI/SSI directives to put into HTML code
     */
    public function getEsiContent($adapterName, $adapterMethod, $params = array()) {
        $url = $this->getUrlFromApi($adapterName, $adapterMethod, $params);
        $settings = $this->getSettings();
        if (
            is_a($this->getSsiProxy(), '\\Cx\\Core_Modules\\Cache\\Model\\Entity\\ReverseProxyCloudrexx') &&
            (
                !isset($settings['internalSsiCache']) ||
                $settings['internalSsiCache'] != 'on'
            )
        ) {
            try {
                return $this->getApiResponseForUrl($url);
            } catch (\Exception $e) {
                return '';
            }
        }
        return $this->getSsiProxy()->getSsiProcessor()->getIncludeCode($url->toString());
    }

    /**
     * Each entry of $esiContentInfos consists of an array like:
     * array(
     *     <adapterName>,
     *     <adapterMethod>,
     *     <params>,
     * )
     * @param array $esiContentInfos List of ESI content info arrays
     * @param int $count (optional) Number of unique random entries to parse
     * @return string ESI randomized include code
     */
    public function getRandomizedEsiContent($esiContentInfos, $count = 1) {
        $urls = array();
        foreach ($esiContentInfos as $i=>$esiContentInfo) {
            $urls[] = $this->getUrlFromApi(
                $esiContentInfo[0],
                $esiContentInfo[1],
                $esiContentInfo[2]
            )->toString();
        }
        $settings = $this->getSettings();
        if (
            is_a(
                $this->getSsiProxy(),
                '\\Cx\\Core_Modules\\Cache\\Model\\Entity\\ReverseProxyCloudrexx'
            ) &&
            (
                !isset($settings['internalSsiCache']) ||
                $settings['internalSsiCache'] != 'on'
            )
        ) {
            try {
                return $this->getApiResponseForUrl(
                    $urls[rand(0, (count($urls) - 1))]
                );
            } catch (\Exception $e) {
                return '';
            }
        }
        return $this->getSsiProxy()->getSsiProcessor()->getRandomizedIncludeCode(
            $urls,
            $count
        );
    }

    /**
     * Returns the content of the API response for an API URL
     * This gets data internally and does not do a HTTP request!
     * @param string $url API URL
     * @throws \Exception If JsonAdapter request did not succeed
     * @return string API content or empty string
     */
    protected function getApiResponseForUrl($url) {
        // Initialize only when needed, we need DB for this!
        if (empty($this->apiUrlString)) {
            $this->apiUrlString = substr(\Cx\Core\Routing\Url::fromApi('', array(), array()), 0, -1);
        }
        
        $query = parse_url($url, PHP_URL_QUERY);
        $path = parse_url($url, PHP_URL_PATH);
        $params = array();
        parse_str($query, $params);
        
        $pathParts = explode('/', str_replace($this->apiUrlString, '', $path));
        if (
            count($pathParts) != 4 ||
            $pathParts[0] != 'Data' ||
            $pathParts[1] != 'Plain'
        ) {
            return '';
        }
        $adapter = contrexx_input2raw($pathParts[2]);
        $method = contrexx_input2raw($pathParts[3]);
        unset($params['cmd']);
        unset($params['object']);
        unset($params['act']);
        $arguments = array('get' => contrexx_input2raw($params));
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $arguments['response'] = new \Cx\Core\Routing\Model\Entity\Response(
            null,
            200,
            new \Cx\Core\Routing\Model\Entity\Request(
                'get',
                new \Cx\Core\Routing\Url($url),
                array(
                    'Referer' => $cx->getRequest()->getUrl()->toString(),
                )
            )
        );
        $arguments['response']->setPage($cx->getPage());

        if (!$this->jsonData) {
            $this->jsonData = new \Cx\Core\Json\JsonData();
        }
        $response = $this->jsonData->data($adapter, $method, $arguments);
        if (
            !isset($response['status']) ||
            $response['status'] != 'success' ||
            !isset($response['data']) ||
            !isset($response['data']['content'])
        ) {
            throw new \Exception('JsonAdapter returned with an error: "' . $response['message'] . '"');
        }
        return $response['data']['content'];
    }

    /**
     * Drops the ESI cache for a specific call
     * @param string $adapterName (Json)Data adapter name
     * @param string $adapterMethod (Json)Data method name
     * @param array $params (optional) params for (Json)Data method call
     * @todo Only drop this specific content instead of complete cache
     */
    public function clearSsiCachePage($adapterName, $adapterMethod, $params = array()) {
        $url = $this->getUrlFromApi($adapterName, $adapterMethod, $params);
        $this->getSsiProxy()->clearCachePage($url->toString(), $this->getDomainsAndPorts());
    }
    
    /**
     * Wrapper for \Cx\Core\Routing\Url::fromApi()
     * This ensures correct param order
     * @param string $adapterName (Json)Data adapter name
     * @param string $adapterMethod (Json)Data method name
     * @param array $params (optional) params for (Json)Data method call
     * @return \Cx\Core\Routing\Url URL for (Json)Data call
     */
    protected function getUrlFromApi($adapterName, $adapterMethod, $params) {
        if (isset($_GET['preview'])) {
            $params['theme'] = intval($_GET['preview']);
        }
        $url = \Cx\Core\Routing\Url::fromApi('Data', array('Plain', $adapterName, $adapterMethod), $params);
        // make sure params are in correct order:
        $correctIndexOrder = array(
            'page',
            'locale',
            'user',
            'theme',
            'channel',
            'country',
            'currency',
            'query',
            'path',
            'ref',
            'targetComponent',
            'targetEntity',
            'targetId',
        );
        $params = $url->getParamArray();
        uksort($params, function($a, $b) use ($correctIndexOrder) {
            return array_search($a, $correctIndexOrder) - array_search($b, $correctIndexOrder);
        });
        $url->setParams($params);
        $url->setParam('EOU', '');
        return $url;
    }

    /**
     * Drops all cached ESI/SSI elements
     */
    public function clearSsiCache($urlPattern = '') {
        if (!empty($urlPattern)) {
            $this->getSsiProxy()->clearCachePage($urlPattern, $this->getDomainsAndPorts());
        }
        $this->getSsiProxy()->clearCache($this->getDomainsAndPorts());
    }

    protected function isInstalled($cacheEngine)
    {
        switch ($cacheEngine) {
            case self::CACHE_ENGINE_APC:
                return extension_loaded('apc');
            case self::CACHE_ENGINE_ZEND_OPCACHE:
                return extension_loaded('opcache') || extension_loaded('Zend OPcache');
            case self::CACHE_ENGINE_MEMCACHE:
                return extension_loaded('memcache');
            case self::CACHE_ENGINE_MEMCACHED:
                return extension_loaded('memcached');
            case self::CACHE_ENGINE_XCACHE:
                return extension_loaded('xcache');
        }
    }

    protected function isActive($cacheEngine)
    {
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
                return !empty($this->memcache) ? true : false;
            case self::CACHE_ENGINE_MEMCACHED:
                return !empty($this->memcached) ? true : false;
            case self::CACHE_ENGINE_XCACHE:
                $setting = 'xcache.cacher';
                break;
        }
        if (!empty($setting)) {
            $configurations = ini_get_all();
            return $configurations[$setting]['global_value'];
        }
    }

    protected function isConfigured($cacheEngine, $user = false)
    {
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
                // opcache.load_comments no longer exists since PHP7
                // therefore, ini_get() will return FALSE in case the
                // php directive does not exist
                return ini_get('opcache.save_comments') && (ini_get('opcache.load_comments') === false || ini_get('opcache.load_comments'));
            case self::CACHE_ENGINE_MEMCACHE:
                return $this->memcache ? true : false;
            case self::CACHE_ENGINE_MEMCACHED:
                return $this->memcached ? true : false;
            case self::CACHE_ENGINE_XCACHE:
                if ($user) {
                    return (
                        ini_get('xcache.var_size') > 0 &&
                        ini_get('xcache.admin.user') &&
                        ini_get('xcache.admin.pass')
                    );
                }
                return ini_get('xcache.size') > 0;
        }
    }

    protected function getMemcacheConfiguration()
    {
        global $_CONFIG;
        $ip = '127.0.0.1';
        $port = '11211';

        if(!empty($_CONFIG['cacheUserCacheMemcacheConfig'])){
            $settings = json_decode($_CONFIG['cacheUserCacheMemcacheConfig'], true);
            $ip = $settings['ip'];
            $port = $settings['port'];
        }

        return array('ip' => $ip, 'port' => $port);
    }

    protected function getMemcachedConfiguration()
    {
        global $_CONFIG;
        $ip = '127.0.0.1';
        $port = '11211';

        if(!empty($_CONFIG['cacheUserCacheMemcachedConfig'])){
            $settings = json_decode($_CONFIG['cacheUserCacheMemcachedConfig'], true);
            $ip = $settings['ip'];
            $port = $settings['port'];
        }

        return array('ip' => $ip, 'port' => $port);
    }

    /**
     * Gets the configuration value for reverse proxy
     * @return array 'ip' and 'port' of reverse proxy
     */
    protected function getReverseProxyConfiguration()
    {
        global $_CONFIG;
        $ip = '127.0.0.1';
        $port = '8080';

        if (!empty($_CONFIG['cacheProxyCacheConfig'])){
            $settings = json_decode($_CONFIG['cacheProxyCacheConfig'], true);
            $ip = $settings['ip'];
            $port = $settings['port'];
        }

        return array('ip' => $ip, 'port' => $port);
    }

    /**
     * Gets the configuration value for external ESI/SSI processor
     * @return array 'ip' and 'port' of external ESI/SSI processor
     */
    protected function getSsiProcessorConfiguration()
    {
        global $_CONFIG;
        $ip = '127.0.0.1';
        $port = '8080';

        if (!empty($_CONFIG['cacheSsiProcessorConfig'])){
            $settings = json_decode($_CONFIG['cacheSsiProcessorConfig'], true);
            $ip = $settings['ip'];
            $port = $settings['port'];
        }

        return array('ip' => $ip, 'port' => $port);
    }

    /**
     * Flush all cache instances
     * @see \Cx\Core\ContentManager\Model\Event\PageEventListener on update of page objects
     */
    public function clearCache($cacheEngine = null)
    {
        if (!$this->strCachePath) {
            $cx = \Cx\Core\Core\Controller\Cx::instanciate();
            if (is_dir($cx->getWebsiteCachePath())) {
                if (is_writable($cx->getWebsiteCachePath())) {
                    $this->strCachePath = $cx->getWebsiteCachePath() . '/';
                }
            }
        }
        if ($cacheEngine === null) {
            // remove cached files
            $this->_deleteAllFiles('cxPages');
        }

        if ($cacheEngine == null) {
            if ($this->userCacheEngine == self::CACHE_ENGINE_MEMCACHED) {
                // do not automatically flush memcached as fallback
                // this will break Gedmo etc
            } else {
                $cacheEngine = $this->userCacheEngine;
            }
        }
        switch ($cacheEngine) {
            case self::CACHE_ENGINE_APC:
                $this->clearApc();
                break;
            case self::CACHE_ENGINE_MEMCACHE:
                $this->clearMemcache();
                break;
            case self::CACHE_ENGINE_MEMCACHED:
                $this->clearMemcached();
                break;
            case self::CACHE_ENGINE_XCACHE:
                $this->clearXcache();
                break;
            case self::CACHE_ENGINE_ZEND_OPCACHE:
                $this->clearZendOpCache();
                break;
            default:
                break;
        }

        $this->clearReverseProxyCache('*');
        $this->clearSsiCache();
    }

    /**
     * Drops a cache page on reverse proxy cache
     * @param string $urlPatter URL pattern to drop on reverse cache proxy
     */
    public function clearReverseProxyCache($urlPattern) {
        global $_CONFIG;

        // find rproxy driver
        if (!isset($_CONFIG['cacheReverseProxy']) || $_CONFIG['cacheReverseProxy'] == 'none') {
            return;
        }
        $reverseProxyType = $_CONFIG['cacheReverseProxy'];

        $className = '\\Cx\\Lib\\ReverseProxy\\Model\\Entity\\ReverseProxy' . ucfirst($reverseProxyType);
        $reverseProxyConfiguration = $this->getReverseProxyConfiguration();
        $reverseProxy = new $className(
            $reverseProxyConfiguration['ip'],
            $reverseProxyConfiguration['port']
        );

        // advise driver to drop page for HTTP and HTTPS ports on all domain aliases
        $reverseProxy->clearCachePage($urlPattern, $this->getDomainsAndPorts());
    }

    /**
     * Returns all domains and ports this instance of cloudrexx can be reached at
     * @return array List of domains and ports (array(array(0=>{domain}, 1=>{port})))
     */
    protected function getDomainsAndPorts() {
        $domainsAndPorts = array();
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        $forceDomainUrl = \Cx\Core\Setting\Controller\Setting::getValue(
            'forceDomainUrl',
            'Config'
        );
        if (isset($forceDomainUrl) && $forceDomainUrl == 'on') {
            $domains = array($domainRepo->getMainDomain());
        } else {
            $domains = $domainRepo->findAll();
        }
        $forceProtocolFrontend = \Cx\Core\Setting\Controller\Setting::getValue(
            'forceProtocolFrontend',
            'Config'
        );
        if (isset($forceProtocolFrontend) && $forceProtocolFrontend != 'none') {
            $protocols = array($forceProtocolFrontend);
        } else {
            $protocols = array('http', 'https');
        }
        foreach ($protocols as $protocol) {
            foreach ($domains as $domain) {
                $domainsAndPorts[] = array(
                    $domain->getName(),
                    \Cx\Core\Setting\Controller\Setting::getValue(
                        'portFrontend' . strtoupper($protocol),
                        'Config'
                    )
                );
            }
        }
        return $domainsAndPorts;
    }

    /**
     * Clears APC cache if APC is installed
     */
    private function clearApc()
    {
        if($this->isInstalled(self::CACHE_ENGINE_APC)){
            $apcInfo = \apc_cache_info();
            foreach($apcInfo['entry_list'] as $entry) {
                if(false !== strpos($entry['key'], $this->getCachePrefix()))
                \apc_delete($entry['key']);
            }
            \apc_clear_cache(); // this only deletes the cached files
        }
    }

    /**
     * Clears all Memcachedata related to this Domain if Memcache is installed
     */
    private function clearMemcache()
    {
        if(!$this->isInstalled(self::CACHE_ENGINE_MEMCACHE)){
            return;
        }
        //$this->memcache->flush(); //<- not like this!!!
        $keys = array();
        $allSlabs = $this->memcache->getExtendedStats('slabs');

        foreach ($allSlabs as $server => $slabs) {
            if (is_array($slabs)) {
                foreach (array_keys($slabs) as $slabId) {
                    $dump = $this->memcache->getExtendedStats('cachedump', (int) $slabId);
                    if ($dump) {
                        foreach ($dump as $entries) {
                            if ($entries) {
                                $keys = array_merge($keys, array_keys($entries));
                            }
                        }
                    }
                }
            }
        }
        foreach($keys as $key){
            if(strpos($key, $this->getCachePrefix()) !== false){
                $this->memcache->delete($key);
            }
        }
    }

    /**
     * Clears all Memcacheddata related to this Domain if Memcache is installed
     * @param   string  $pattern    Optional pattern to restrict the
     *                              invalidation of the cache by.
     * @return  integer Returns the number of invalidated keys
     */
    public function clearMemcached($pattern = '')
    {
        if(!$this->isInstalled(self::CACHE_ENGINE_MEMCACHED)){
            return;
        }
        //$this->memcache->flush(); //<- not like this!!!
        $keys = $this->memcached->getAllKeys();
        $n = 0;
        foreach($keys as $key){
            if(strpos($key, $this->getCachePrefix()) !== false){
                if (
                    !empty($pattern) &&
                    !preg_match('/' . $pattern . '/', $key)
                ) {
                    continue;
                }
                $this->memcached->delete($key);
                $n++;
            }
        }

        return $n;
    }

    /**
     * Clears XCache if configured. Configuration is needed to clear.
     */
    private function clearXcache()
    {
        if($this->isConfigured(self::CACHE_ENGINE_XCACHE, true)){
            \xcache_clear_cache();
        }
    }

    /**
     * Clears Zend OPCache if installed
     */
    private function clearZendOpCache()
    {
        if($this->isInstalled(self::CACHE_ENGINE_ZEND_OPCACHE)){
            \opcache_reset();
        }
    }

    /**
     * Retunrns the CachePrefix related to this Domain
     * @global string $_DBCONFIG
     * @return string CachePrefix
     */
    protected function getCachePrefix()
    {
        global $_DBCONFIG;

        // TODO: check if the initialization of the prefix could be moved into
        //       the constructor
        if (empty($this->cachePrefix)) {
            $this->cachePrefix = $_DBCONFIG['database'].'.'.$_DBCONFIG['tablePrefix'];
        }

        return $this->cachePrefix;
    }

    /**
     * Overwrite the automatically set CachePrefix
     * @param   $prefix String  The new CachePrefix to be used.
     *                          Setting an empty string will reset
     *                          the CachePrefix to its initial value.
     */
    public function setCachePrefix($prefix = '') {
        $this->cachePrefix = $prefix;
    }

    /**
     * Detects the correct doctrine cache driver for the user caching engine in use
     * @return \Doctrine\Common\Cache\AbstractCache The doctrine cache driver object
     */
    public function getDoctrineCacheDriver() {
        if($this->doctrineCacheEngine) { // return cache engine if already set
            return $this->doctrineCacheEngine;
        }
        $userCacheEngine = $this->getUserCacheEngine();
        // check if user caching is active
        if (!$this->getUserCacheActive()) {
            $userCacheEngine = \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_OFF;
        }
        switch ($userCacheEngine) {
            case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_APC:
                $cache = new \Doctrine\Common\Cache\ApcCache();
                $cache->setNamespace($this->getCachePrefix());
                break;
            case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_MEMCACHE:
                $memcache = $this->getMemcache();
                $cache = new \Doctrine\Common\Cache\MemcacheCache();
                $cache->setMemcache($memcache);
                $cache->setNamespace($this->getCachePrefix());
                break;
            case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_MEMCACHED:
                $memcached = $this->getMemcached();
                $cache = new \Doctrine\Common\Cache\MemcachedCache();
                $cache->setMemcached($memcached);
                $cache->setNamespace($this->getCachePrefix());
                break;
            case \Cx\Core_Modules\Cache\Controller\Cache::CACHE_ENGINE_XCACHE:
                $cache = new \Doctrine\Common\Cache\XcacheCache();
                $cache->setNamespace($this->getCachePrefix());
                break;
            default:
                $cache = new \Doctrine\Common\Cache\ArrayCache();
                break;
        }
        // set the doctrine cache engine to avoid getting it a second time
        $this->doctrineCacheEngine = $cache;
        return $cache;
    }

    /**
     * Creates an array containing all important cache-settings
     *
     * @global     object    $objDatabase
     * @return    array    $arrSettings
     */
    function getSettings() {
        $arrSettings = array();
        \Cx\Core\Setting\Controller\Setting::init('Config', NULL,'Yaml');
        $ymlArray = \Cx\Core\Setting\Controller\Setting::getArray('Config', null);

        foreach ($ymlArray as $key => $ymlValue){
            $arrSettings[$key] = $ymlValue['value'];
        }

        return $arrSettings;
    }

    /**
     * Returns the validated file search parts of the URL
     * @param string $url URL to parse
     * @param string $originalUrl URL of the page that ESI is parsed for
     * @return array <fileNamePrefix>=><parsedValue> type array
     */
    public function getCacheFileNameSearchPartsFromUrl($url, $originalUrl) {
        try {
            $url = new \Cx\Lib\Net\Model\Entity\Url($url);
            $params = $url->getParsedQuery();
        } catch (\Cx\Lib\Net\Model\Entity\UrlException $e) {
            parse_str(substr($url, 1), $params);
        }
        $searchParams = array(
            'p' => 'page',
            'l' => 'locale',
            'u' => 'user',
            't' => 'theme',
            'ch' => 'channel',
            'g' => 'country',
            'c' => 'currency',
            'q' => 'query',
            'pa' => 'path',
            'r' => 'ref',
            'tc' => 'targetComponent',
            'te' => 'targetEntity',
            'ti' => 'targetId',
        );
        $fileNameSearchParts = array();
        foreach ($searchParams as $short=>$long) {
            if (!isset($params[$long])) {
                continue;
            }
            // security: abort if any mysterious characters are found
            if (!preg_match('/^[a-zA-Z0-9-=\.]+$/', $params[$long])) {
                return array();
            }
            if ($long == 'ref') {
                $params[$long] = str_replace(
                    '$(HTTP_REFERER)',
                    $originalUrl,
                    $params[$long]
                );
                $params[$long] = md5($params[$long]);
            }
            $fileNameSearchParts[$short] = '_' . $short . $params[$long];
        }
        return $fileNameSearchParts;
    }

    /**
     * Gets the local cache file name for an URL
     * @param string $url URL to get file name for
     * @param string $originalUrl URL of the page that ESI is parsed for
     * @param boolean $withCacheInfoPart (optional) Adds info part (default true)
     * @return string File name (without path)
     */
    public function getCacheFileNameFromUrl($url, $originalUrl, $withCacheInfoPart = true) {
        $cacheInfoParts = $this->getCacheFileNameSearchPartsFromUrl($url, $originalUrl);
        try {
            $url = new \Cx\Lib\Net\Model\Entity\Url($url);
            $params = $url->getParsedQuery();
        } catch (\Cx\Lib\Net\Model\Entity\UrlException $e) {
            parse_str(substr($url, 1), $params);
        }
        $correctIndexOrder = array(
            'page',
            'locale',
            'user',
            'theme',
            'channel',
            'country',
            'currency',
            'query',
            'path',
            'ref',
            'targetComponent',
            'targetEntity',
            'targetId',
        );
        foreach ($correctIndexOrder as $paramName) {
            unset($params[$paramName]);
        }
        // Make sure placeholders are replaced before generating filename.
        // Otherwise the filename will be non-unique.
        if (isset($params['ref'])) {
            $params['ref'] = str_replace(
                '$(HTTP_REFERER)',
                $originalUrl,
                $params['ref']
            );
        }
        $fileName = '';
        if (is_object($url)) {
            $url->setParsedQuery($params);
            $url = $url->toString();
            $fileName = md5($url);
        } else {
            $url = http_build_query($params);
        }
        if ($withCacheInfoPart) {
            $fileName .= implode('', $cacheInfoParts);
        }
        return $fileName;
    }

    /**
     * Delete all specific file from cache-folder
     */
    function deleteSingleFile($intPageId) {
        $intPageId = intval($intPageId);
        if (!$intPageId) {
            return;
        }

        $files = glob($this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE . '*_{,h}' . $intPageId . '*', GLOB_BRACE);

        if (!is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * Delete all cached files for a component from cache-folder
     */
    function deleteComponentFiles($componentName)
    {
        $pages = array();
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $em = $cx->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        // get all application pages
        $applicationPages = $pageRepo->findBy(array(
            'type' => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION,
            'module' => $componentName,
        ));
        foreach ($applicationPages as $page) {
            $pages[$page->getId()] = $page;
            // get all fallbacks to them
            // get all symlinks to them
            $pages += $pageRepo->getPagesPointingTo($page);
        }
        // foreach of the above
        foreach ($pages as $pageId=>$page) {
            $this->deleteSingleFile($pageId);
        }
        return array_keys($pages);
    } 

    /**
     * Drops all page cache files that do not belong to a page
     * Those are cached header redirects
     */
    public function deleteNonPagePageCache() {
        $files = glob($this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE . '*_{,h}', GLOB_BRACE);

        if (!is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * Clear user based page cache of a specific user identified by its
     * session ID.
     *
     * @param   string  $sessionId  The session ID of the user of whom
     *                              to clear the page cache from.
     */
    public function clearUserBasedPageCache($sessionId) {
        // abort if no valid session id is supplied
        if (empty($sessionId)) {
            return;
        }

        // fetch complete page cache of specific user
        $files = glob(
            $this->strCachePath .
                static::CACHE_DIRECTORY_OFFSET_PAGE . '*_u' .
                $sessionId . '{,_h}',
            GLOB_BRACE
        );

        if (!is_array($files)) {
            return;
        }

        // drop identified page cache of specific user
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * Clear user based ESI cache of a specific user identified by its
     * session ID.
     *
     * @param   string  $sessionId  The session ID of the user of whom
     *                              to clear the esi cache from.
     */
    public function clearUserBasedEsiCache($sessionId) {
        // abort if no valid session id is supplied
        if (empty($sessionId)) {
            return;
        }

        // fetch complete esi cache of specific user
        $files = glob(
            $this->strCachePath . static::CACHE_DIRECTORY_OFFSET_ESI . '*_u' . $sessionId . '*'
        );

        if (!is_array($files)) {
            return;
        }

        // drop identified esi cache of specific user
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    /**
     * Sets the cached locale data
     *
     * Default locale and the following hashtables are cached:
     * <localeCode> to <localeId>
     * <localeCountryCode> to <localeCodes>
     * @param \Cx\Core\Core\Controller\Cx $cx Cx instance
     */
    public function setCachedLocaleData($cx) {
        $filename = $this->strCachePath . static::CACHE_DIRECTORY_OFFSET_PAGE .
            static::LOCALE_CACHE_FILE_NAME;
        if (file_exists($filename)) {
            return;
        }
        $locale = $cx->getComponent('Locale');
        if (!$locale) {
            return;
        }
        $localeData = $locale->getLocaleData();
        if (empty($localeData)) {
            return;
        }
        $file = new \Cx\Lib\FileSystem\File($filename);
        $file->write(serialize($localeData));
    }
}
