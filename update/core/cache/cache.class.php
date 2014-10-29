<?php
/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     3.1.2
 * @package     contrexx
 * @subpackage  coremodule_cache
 */

require_once(UPDATE_CORE . '/cache/cacheLib.class.php');

/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     3.1.2
 * @package     contrexx
 * @subpackage  coremodule_cache
 */
class Cache extends cacheLib
{
    var $boolIsEnabled = false; //Caching enabled?
    var $intCachingTime; //Expiration time for cached file

    var $strCachePath; //Path to cache-directory
    var $strCacheFilename; //Name of the current cache-file

    var $arrPageContent = array(); //array containing $_SERVER['REQUEST_URI'] and $_REQUEST

    var $arrCacheablePages = array(); //array of all pages with activated caching

    /**
     * Constructor
     *
     * @global array $_CONFIG
     */
    public function __construct()
    {
        return;
    }
    
    protected function initContrexxCaching()
    {
        return;
    }


    /**
     * Start caching functions. If this page is already cached, load it, otherwise create new file
     */
    public function startContrexxCaching()
    {
        return;
    }


    /**
     * End caching functions. Check for a sessionId: if not set, write pagecontent to a file.
     */
    public function endContrexxCaching($page)
    {
        return;
    }


    /**
     * Check the exception-list for this site
     *
     * @global     array        $_EXCEPTIONS
     * @return     boolean        true: Site has been found in exception list
     * @todo    Reimplement! Use for restricting caching-option in CM - see #1205
     */
    public function isException()
    {
        
        return false; //if we are coming to this line, no exception has been found
    }

    /**
     * Delete all cache files from tmp directory
     */
    public function cleanContrexxCaching()
    {
        return;
    }
}
