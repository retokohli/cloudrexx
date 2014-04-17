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
}
