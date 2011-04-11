<?php

/**
 * FWSystem
 *
 * This class provides system related methods.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * FWSystem
 *
 * This class provides system related methods.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */
class FWSystem
{
	/**
	* Returns the maximum file size in bytes that is allowed to upload
	*
	* @return string filesize
	*/
	function getMaxUploadFileSize()
	{
		$upload_max_filesize = FWSystem::_getBytes(@ini_get('upload_max_filesize'));
		$post_max_size = FWSystem::_getBytes(@ini_get('post_max_size'));

		if ($upload_max_filesize < $post_max_size) {
			$maxUploadFilesize = $upload_max_filesize;
		} else {
			$maxUploadFilesize = $post_max_size;
		}
		return $maxUploadFilesize;
	}

	/**
	* Returns the value $size in bytes
	*
	* @access private
	* @param mixed $size
	* @return integer $size in bytes
	*/
	function _getBytes($size)
	{
		$size = trim($size);
		$lastChar = strtolower(substr($size,strlen($size)-1));

		switch ($lastChar) {
			case 'g':
				$size *= 1024;
			case 'm':
				$size *= 1024;
			case 'k':
				$size *= 1024;
		}
		return $size;
	}

    /**
     * Returns the relative URL $absolute_local_path as absolute URL
     * @param string The relative URL that shall be made absolute.
     * @param boolean Wether the absolute URL shall be prefixed by the requested protocol and the domain name.
     */
    static public function mkurl($absolute_local_path, $withProtocolAndDomain = false)
    {
        global $_CONFIG;

        $url = '';
        if ($withProtocolAndDomain) {
            $url .= ASCMS_PROTOCOL."://".$_CONFIG['domainUrl']
                .($_SERVER['SERVER_PORT'] == 80 ? "" : ":".intval($_SERVER['SERVER_PORT']));
        }

        return $url.ASCMS_PATH_OFFSET.stripslashes($absolute_local_path);
    }
}
?>