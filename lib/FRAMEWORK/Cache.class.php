<?php
/**
 * Cache
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Ivan Schmid <ivan.schmid@comvation.com>
 * @version       $Id:  Exp $
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

//Security-Check
if (eregi("cache.class.php",$_SERVER['PHP_SELF']))
{
    CSRF::header("Location: index.php");
    die();
}

/**
 * Class to cache the output
 *
 * Class to create and show the cached output
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Ivan Schmid <ivan.schmid@comvation.com>
 * @version       $Id:  Exp $
 * @package     contrexx
 * @subpackage  lib_framework
 */
class cache
{
	var $cacheFilename;
	var $pageId;
	var $pageUri;
	var $cacheDir;
	var $maxAge; // time in hours
	var $cacheIsEnabled;


	function __construct($maxAge=24)
	{
        $this->pageUri = $_SERVER['REQUEST_URI'];
        /*
		$this->pageId = array( "uri"    => $this->pageUri,
                               "post"   => $_POST,
                               "cookie" => $_COOKIE);

        */


		$this->pageId = array( "uri"    => $this->pageUri,
                               "post"   => $_POST);



		// $this->pageId = array( "uri",$this->pageUri);
        // $this->pageId = $this->pageUri;

		$this->_checkPermission();
		if($this->cacheIsEnabled)
		{
			$this->maxAge = $maxAge;
		    $this->cacheDir = 'cached_files/';
		    $this->cacheFilename = $this->cacheDir.md5(serialize($this->pageId));
		    //$this->cacheFilename = $this->cacheDir.serialize($this->page);
		}
	}



	function _checkPermission()
	{
		global $_CONFIG;

		$must = array('section','page');
		$exceptions = array('newsletter','u=','engine','cmd=submit','dnd','voting');
		$this->cacheIsEnabled = TRUE;

		if(!isset($_CONFIG['enableCaching']) OR !$_CONFIG['enableCaching'])
		{
            $this->cacheIsEnabled = FALSE;
            return "";
		}
		foreach ($must as $val)
		{
			if(ereg($val, $this->pageUri) OR $this->pageUri == '/')
			{
				foreach ($exceptions as $val)
				{
					if(ereg($val, $this->pageUri))// || in_array($val,$_REQUEST))
					//if(preg_grep("/$val/", $_REQUEST))
					{
						//print "cache false";
						$this->cacheIsEnabled = FALSE;
					}
				}

			}
		}
	}





	function startCache()
	{
		if($this->cacheIsEnabled)
		{
			if(is_file($this->cacheFilename))
			{
				if( filemtime($this->cacheFilename) > (time() - ($this->maxAge * 60)) )
				{
					readfile($this->cacheFilename);
					exit;
				}
			}
			else
			{
			    ob_start();
			}
		}
	}



	function endCache()
	{
		if($this->cacheIsEnabled)
		{
			$cacheContents = ob_get_contents();
			ob_end_flush();

			if($fp = fopen($this->cacheFilename, 'w'))
			{
				if(flock($fp, LOCK_EX))
				{
				    fwrite($fp, $cacheContents);
				    flock($fp, LOCK_UN);
				}
				fclose($fp);
			}
			unset($cacheContents);

			// check for valid file
			// the file should be bigger than 2 bytes
			if($fp = fopen($this->cacheFilename, 'r'))
			{
				$testBytes = fread($fp, 3);
				fclose($fp);
				if(strlen($testBytes) < 2)
				{
				    @unlink($this->cacheFilename);
				}
			}
		}
		return "";
	}
}
?>
