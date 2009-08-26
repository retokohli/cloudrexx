<?php

define('CATALOG_MAX_THUMBNAIL_WIDTH',     60);
define('CATALOG_MAX_THUMBNAIL_HEIGHT',  9999);
define('CATALOG_MAX_THUMBNAIL_QUALITY',  100);

/**
 * Creates a thumbnail for a catalog image
 * @param   string  $filename   The file name
 * @return  boolean             True on success, false otherwise
 */
function createThumb($filename)
{
//echo("createThumb<br />");
    require_once("../lib/FRAMEWORK/Image.class.php");
    $objImage = new ImageManager();
    $path_parts = pathinfo($filename);
    return $objImage->_createThumbWhq(
        ASCMS_PATH.$path_parts['dirname'],
        $path_parts['dirname'],
        '/'.basename($filename),
        CATALOG_MAX_THUMBNAIL_WIDTH,
        CATALOG_MAX_THUMBNAIL_HEIGHT,
        CATALOG_MAX_THUMBNAIL_QUALITY
    );
}

?>
