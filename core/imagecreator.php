<?php
/**
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Check availability of GD library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 */
function checkGDExtension()
{
	if (extension_loaded('gd')) {
        return true;
	}
	if (@ini_get( "enable_dl" )) {
		$edir = ini_get('extension_dir');
		if (file_exists("$edir/php_gd.dll")) {
		   if (@dl('php_gd2.dll')) return true;
		   if (@dl('php_gd.dll')) return true; // unix: 'gd.so'
		}
	}
	return false;
}

/**
 * Create a security image
 * @return void
 */
function getSecurityImage($id="")
{
	header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
	header ("Pragma: no-cache");                          // HTTP/1.0
	header ("Content-type: image/png");
	$font   = 4;
	$width  = imagefontwidth($font) * strlen($id);
	$height = imagefontheight($font);
	// This function was added in PHP 4.0.6 and requires GD
	$im = @imagecreate($width,$height)
     or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate ($im, 255, 255, 255); //white background
	$text_color = imagecolorallocate ($im, 0, 0,0);//black text
	imagestring ($im, $font, 0, 0,  $id, $text_color);
	imagepng ($im);
	ImageDestroy ($im);
}
?>