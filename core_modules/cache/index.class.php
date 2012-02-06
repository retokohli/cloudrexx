<?php
/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  core_module_cache
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Include
 */
require_once ASCMS_CORE_MODULE_PATH.'/cache/lib/Exceptions.lib.php';
require_once ASCMS_CORE_MODULE_PATH.'/cache/lib/cacheLib.class.php';

/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  core_module_cache
 */
class Cache extends cacheLib {
	var $boolIsEnabled = false;			//Caching enabled?
	var $intCachingTime;				//Expiration time for cached file

	var $strCachePath;					//Path to cache-directory
	var $strCacheFilename;				//Name of the current cache-file

	var $arrPageContent = array();		//array containing $_SERVER['REQUEST_URI'] and $_REQUEST

	var $arrCacheablePages = array();	//array of all pages with activated caching

	/**
    * Constructor
    *
    * @global 	array		$_CONFIG
    */
	function __construct()
	{
		global $_CONFIG;

        // in case the request's origin is from a mobile devie
        // and this is the first request (the InitCMS object wasn't yet
        // able to determine of the mobile device wishes to be served
        // with the system's mobile view), we shall deactivate the caching system
        if (InitCMS::_is_mobile_phone()
            && !isset($_REQUEST['smallscreen'])
        ) {
            $this->boolIsEnabled = false;
            return;
        }

		if($_CONFIG['cacheEnabled'] == 'on' && (!isset($_REQUEST['caching']) || $_REQUEST['caching'] != '0')) {
			if (is_dir(ASCMS_CACHE_PATH)) {
				if (is_writable(ASCMS_CACHE_PATH)) {
					$this->strCachePath = ASCMS_CACHE_PATH.'/';
				} else {
					die('Directory for cache-system is not writeable. Please set chmod 777 on: '.ASCMS_CACHE_PATH);
				}
			} else {
				die('Directory for cache-system does not exist. Please check path: '.ASCMS_CACHE_PATH);
			}

			$this->intCachingTime = intval($_CONFIG['cacheExpiration']);
			ksort($_REQUEST);
			$this->arrPageContent = array(	'url'		=>	$_SERVER['REQUEST_URI'],
											'request'	=>	$_REQUEST
										);
			$this->strCacheFilename = md5(serialize($this->arrPageContent));

			if (is_file($this->strCachePath.'index.php')) {
				require_once($this->strCachePath.'index.php'); //Read in the "CachablePages-File"
				if (is_array($_CACHEPAGES)) {
					foreach($_CACHEPAGES as $intKey => $intPage) {
						$this->arrCacheablePages[$intPage] = true;
					}
				}
			}

			if (!$this->isException()) {
				if (isset($_GET['page']) && intval($_GET['page']) > 0) {
					if (array_key_exists(intval($_GET['page']),$this->arrCacheablePages)) {
						$this->boolIsEnabled = true;
					} else {
						$this->boolIsEnabled = false;
					}
				} else {
					$this->boolIsEnabled = true;
				}
			}
		}
	}


	/**
    * Start caching functions. If this page is already cached, load it, otherwise create new file
    */
	function startCache() {
		if ($this->boolIsEnabled) {
			if (is_file($this->strCachePath.$this->strCacheFilename)	&&
				filemtime($this->strCachePath.$this->strCacheFilename) > (time() - $this->intCachingTime)) {
				//file was cached before, load it
				readfile($this->strCachePath.$this->strCacheFilename);
				exit;
			} else {
				//file does not exist, start recording
				ob_start();
			}
		}
	}


	/**
    * End caching functions. Check for a sessionId: if not set, write pagecontent to a file.
    */
	function endCache() {
		if ($this->boolIsEnabled) {
			$strCacheContents = ob_get_contents();
			ob_end_flush();

			if (session_id() == '') {
				$handleFile = fopen($this->strCachePath.$this->strCacheFilename,'w+');
				if ($handleFile) {
					//Set a semaphore
					flock($handleFile, LOCK_EX);
					@fwrite($handleFile, $strCacheContents);
					flock($handleFile, LOCK_UN);
					fclose($handleFile);
				}
			}
		}
	}


	/**
    * Check the exception-list for this site
    *
    * @global 	array		$_EXCEPTIONS
    * @return 	boolean		true: Site has been found in exception list
    */
	function isException() {
		global $_EXCEPTIONS;

		$boolReturn = true;

		if (is_array($_EXCEPTIONS)) {
			foreach ($_EXCEPTIONS as $intKey => $arrInner) {
				if (count($arrInner) == 1) {
					//filter a complete module
					if ($_REQUEST['section'] == $arrInner['section']) {
						return true;
					}
				} else {
					//filter a specific part of a module
					$intArrLength = count($arrInner);
					$intHits = 0;

					foreach ($arrInner as $strKey => $strValue) {
						if ($strKey == 'section') {
							if ($_REQUEST['section'] == $strValue) {
								++$intHits;
							}
						} else {
							if (isset($_REQUEST[$strKey]) && preg_match($strValue, $_REQUEST[$strKey])) {
								++$intHits;
							}
						}
					}

					if ($intHits == $intArrLength) {
						//all fields have been found, don't cache
						return true;
					}
				}
			}
		}

		return false; //if we are coming to this line, no exception has been found
	}

	function deleteAllFiles()
	{
		$this->_deleteAllFiles();
	}
}
?>
