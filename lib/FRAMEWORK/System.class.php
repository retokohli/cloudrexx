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
}
?>