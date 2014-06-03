<?php
/**
 * Filesystem Driver Adapter for doctrine
 *
 * @copyright   Comvation AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @package     contrexx
 * @subpackage  coremodules_cache
 */

namespace Cx\Core_Modules\Cache\lib\Doctrine\CacheDriver;

/**
 * Filesystem Driver Adapter for doctrine
 *
 * @copyright   Comvation AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @package     contrexx
 * @subpackage  coremodules_cache
 */
class FileSystemCache extends \Doctrine\Common\Cache\AbstractCache
{
    private $path;
    private $prefix = 'db_';
    private $suffix = '.tmp';
    
    /**
     * @param string $path the path to the cache directory
     */
    public function setPath($path) {
        $this->path = $path;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        if (!is_dir($this->path)) {
            return array();
        }
        $keys = array();
        foreach (new \DirectoryIterator($this->path) as $fileInfo) {
            if (!$fileInfo->isFile()) continue;
            $id = str_replace(array($this->prefix, $this->suffix), '', $fileInfo->getFilename());
            $keys[] = str_replace('-', '/', $id);
        }
        
        return $keys;
    }

    /**
     * {@inheritdoc}
     */
    protected function _doFetch($id)
    {
        $lifetime = -1;
        $filename = $this->getFileName($id);
        if (!file_exists($filename)) {
            return false;
        }
        
        $file = new \Cx\Lib\FileSystem\File($filename);
        $content = $file->getData();
        $lines = explode("\r\n", $content);

        if (!empty($lines) && count($lines) > 1) {
            $lifetime = (integer) $lines[0];
        }
        unset($lines[0]);
        
        if ($lifetime !== 0 && $lifetime < time()) {
            return false;
        }
        $data = implode("\r\n", $lines);
        if (preg_match("/^(O:|a:)/", $data)) { // is serialized?
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function _doContains($id)
    {
        $lifetime = -1;
        $file = $this->getFileName($id);
        if (!file_exists($file)) {
            return false;
        }
        $resource = fopen($file, "r");
        if (false !== ($line = fgets($resource))) {
           $lifetime = (integer) $line;
        }
        if (false !== ($line = fgets($resource))) {
           $lifetime = (integer) $line;
        }
        return $lifetime === 0 || $lifetime > time();
    }

    /**
     * {@inheritdoc}
     */
    protected function _doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }
        $file = new \Cx\Lib\FileSystem\File($this->getFileName($id));
        try {
            $file->write($lifeTime . "\r\n" . serialize($data));
        } catch (\Exception $e) {
           return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function _doDelete($id)
    {
        if (!$this->_doContains($id)) {
            return false;
        }
        $file = new \Cx\Lib\FileSystem\File($this->getFileName($id));
        return $file->delete();
    }
    
    protected function getFileName($id) {
        $id = str_replace(array('/', '\\'), '-', $this->prefix . $id . $this->suffix);
        return $this->path . '/' . $id;
    }
}
