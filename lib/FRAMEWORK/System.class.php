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
    static public function getMaxUploadFileSize()
    {
        $upload_max_filesize = self::getBytesOfLiteralSizeFormat(ini_get('upload_max_filesize'));
        $post_max_size = self::getBytesOfLiteralSizeFormat(ini_get('post_max_size'));

        if ($upload_max_filesize < $post_max_size) {
            $maxUploadFilesize = $upload_max_filesize;
        } else {
            $maxUploadFilesize = $post_max_size;
        }
        return $maxUploadFilesize;
    }

    /**
     * Return the literal size of $bytes with the appropriate suffix (bytes, KB, MB, GB)
     *
     * @param integer $bytes
     * @return string
     */
    static public function getLiteralSizeFormat($bytes)
    {
        $exp = floor(log($bytes, 1024));

        switch ($exp) {
            case 0: // bytes
                $suffix = ' bytes';
                break;

            case 1: // KB
                $suffix = ' KB';
                break;

            case 2: // MB
                $suffix = ' MB';
                break;

            case 3: // GB
                $suffix = ' GB';
                break;
        }

        return round(($bytes / pow(1024, $exp)), 1).$suffix;
    }

    /**
     * Return the literal size $literalSize in bytes.
     *
     * @param string $literalSize
     * @return integer
     */
    static public function getBytesOfLiteralSizeFormat($literalSize)
    {
        $subpatterns = array();
        if (preg_match('#^([0-9.]+)\s*(gb?|mb?|kb?|bytes?|)?$#i', $literalSize, $subpatterns)) {
            $bytes = $subpatterns[1];
            switch (strtolower($subpatterns[2][0])) {
                case 'g':
                    $bytes *= 1024;

                case 'm':
                    $bytes *= 1024;

                case 'k':
                    $bytes *= 1024;
            }

            return $bytes;
        } else {
            return 0;
        }
    }

}

?>
