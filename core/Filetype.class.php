<?php

/**
 * File type
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @version     2.2.0
 */

/**
 * All kind of file type stuff, including MIME types
 * @internal    Used to be Mime.class.php
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @version     2.2.0
 */
class Filetype
{
    /**
     * Text key for the file type name
     */
    const TEXT_NAME = 'core_filetype';

    /**
     * The default MIME type used if nothing is known about the data
     * @access  private
     * @var     string
     */
    const MIMETYPE_DEFAULT = 'application/octet-stream';

    /**
     * Size limit in bytes for files being uploaded or stored
     *
     * This is set to 2^20, or 1048576 -- aka one megabyte
     */
    const MAXIMUM_UPLOAD_FILE_SIZE = 1048576;


    /**
     * Map known extensions to MIME types
     * @access  private
     * @var     array
     */
    private static $arrExtensions2Mimetypes = false;

    /**
     * Map MIME types to known extensions
     * @access  private
     * @var     array
     */
    private static $arrMimetypes2Extensions = false;


    /**
     * Initialize the arrays of extensions and mime types on request
     *
     * The arrays look like this:
     *  $arrExtensions2Mimetypes = array(
     *    Extension => array(
     *      'id'        => ID,
     *      'text_id'   => Text ID,
     *      'name'      => Name,
     *      'extension' => Extension,
     *      'mimetype' => array(
     *        MIME Type,
     *        ... more ...
     *      ),
     *    ),
     *    ... more ...
     *  );
     *
     *  $arrMimetypes2Extensions = array(
     *    MIME Type => array(
     *      'id'        => ID,
     *      'text_id'   => Text ID,
     *      'name'      => Name,
     *      'mimetype' => MIME Type,
     *      'extension' => array(
     *        Extension,
     *        ... more ...
     *      ),
     *    ),
     *    ... more ...
     *  );
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`filetype`.`name_text_id`', FRONTEND_LANG_ID,
            0, self::TEXT_NAME
        );
        $query = "
            SELECT `filetype`.`id`,
                   `filetype`.`extension`, `filetype`.`mimetype`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_filetype AS `filetype`".
                   $arrSqlName['join']."
             ORDER BY `filetype`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrExtensions2Mimetypes = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields[$arrSqlName['id']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
            if (empty(self::$arrExtensions2Mimetypes[$objResult->fields['extension']]))
                self::$arrExtensions2Mimetypes[$objResult->fields['extension']] =
                    array(
                        'id' => $id,
                        'text_id' => $text_id,
                        'name' => $strName,
                        'extension' => $objResult->fields['extension'],
                    );
                self::$arrExtensions2Mimetypes[$objResult->fields['extension']]['mimetype'][] =
                    $objResult->fields['mimetype'];
            if (empty(self::$arrMimetypes2Extensions[$objResult->fields['mimetype']]))
                self::$arrMimetypes2Extensions[$objResult->fields['mimetype']] =
                    array(
                        'id' => $id,
                        'text_id' => $text_id,
                        'name' => $strName,
                        'mimetype' => $objResult->fields['mimetype'],
                    );
                self::$arrMimetypes2Extensions[$objResult->fields['mimetype']]['extension'][] =
                    $objResult->fields['extension'];
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns boolean true if the string argument is a known ending,
     * false otherwise.
     * @static
     * @param   string     $strExtension    The file extension
     * @return  boolean                     True if the extension is known,
     *                                      false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function isKnownExtension($strExtension)
    {
        if (empty(self::$arrExtensions2Mimetypes)) self::init();
        return isset(self::$arrExtensions2Mimetypes[$strExtension]);
    }


    /**
     * Return the MIME type for the extension provided.
     *
     * Takes a full file name, or a file extension with or without
     * the dot as an argument, i.e. 'contrexx.zip', '.gif, or 'txt'.
     * Returns the string 'application/octet-stream' for any unknown ending.
     * Use {@link isKnownExtension()} to test exactly that.
     * @static
     * @param   string     $strExtension    The file extension
     * @return  string                      The corresponding MIME type
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getMimetypeForExtension($strExtension)
    {
        if (empty(self::$arrExtensions2Mimetypes)) self::init();
        // Make sure only the extension is present.
        // Chop the file name up to and including  the last dot
        $strChoppedExtension = preg_replace('/^.*\./', '', $strExtension);
        if (self::isKnownExtension($strChoppedExtension))
            return self::$arrExtensions2Mimetypes[$strChoppedExtension]['mimetype'];
        return self::MIMETYPE_DEFAULT;
    }


    /**
     * Returns an array of file name extensions that are valid for images
     *
     * These include jpg, jpeg, gif, and png.
     */
    static function getImageExtensions()
    {
        return array('jpg', 'jpeg', 'gif', 'png', );
    }


    /**
     * Returns an array of MIME types for accepted images
     *
     * These include those with the extensions returned by
     * {@see getImageExtensions()}
     */
    static function getImageMimetypes()
    {
        $arrMimetypes = array();
        foreach (self::getImageExtensions() as $extension) {
        	 $arrMimetypes[] = self::$arrExtensions2Mimetypes[$extension]['mimetype'];
        }
        return $arrMimetypes;
    }


    /**
     * Returns the HTML code for the MIME type dropdown menu
     * @param   string    $selected     The optional selected MIME tpye
     * @return  string                  The menu options HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTypeMenuoptions($selected='')
    {
        if (empty(self::$arrExtensions2Mimetypes)) self::init();
        $strMenuoptions = '';
        foreach (self::$arrExtensions2Mimetypes as $extension => $arrType) {
            $mimetype = $arrType['mimetype'];
            $strMenuoptions .=
                '<option value="'.$mimetype.'"'.
                ($selected == $mimetype ? ' selected="selected"' : '').
                ">$mimetype ($extension)</option>\n";
        }
        return $strMenuoptions;
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database table.
     * If the table exists, it is dropped.
     * After that, the table is created anew.
     * Finally, the mime types known are inserted.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

die("Filetype::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_file_type", $arrTables)) {
            // The table does exist, but causes errors!  So...
            $objResult = $objDatabase->Execute("
                DROP TABLE `".DBPREFIX."core_file_type`");
            if (!$objResult) return false;
        }

        $objResult = $objDatabase->Execute("
            CREATE TABLE `".DBPREFIX."core_file_type` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `extension` VARCHAR(16) NULL COMMENT 'Extension without the leading dot',
              `mimetype` VARCHAR(32) NULL COMMENT 'Mime type',
              PRIMARY KEY (`id`),
              UNIQUE INDEX `type` USING BTREE (`extension`(16) ASC, `mimetype`(32) ASC)
            ENGINE = InnoDB");
        if (!$objResult) return false;

        /**
         * Known extensions and corresponding MIME types.
         *
         * Note that these associations are arbitrary!
         * @var     array
         */
        $arrExtensions2Mimetypes = array(
            '3dm' => 'x-world/x-3dmf',
            '3dmf' => 'x-world/x-3dmf',
            'ai' => 'application/postscript',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'au' => 'audio/basic',
            'avi' => 'video/x-msvideo',
            'bin' => 'application/octet-stream',
            'cab' => 'application/x-shockwave-flash',
            'chm' => 'application/mshelp',
            'class' => 'application/octet-stream',
            'com' => 'application/octet-stream',
            'csh' => 'application/x-csh',
            'css' => 'text/css',
            'csv' => 'text/comma-separated-values',
            'dll' => 'application/octet-stream',
            'doc' => 'application/msword',
            'dot' => 'application/msword',
            'eps' => 'application/postscript',
            'exe' => 'application/octet-stream',
            'fh4' => 'image/x-freehand',
            'fh5' => 'image/x-freehand',
            'fhc' => 'image/x-freehand',
            'fif' => 'image/fif',
            'gif' => 'image/gif',
            'gtar' => 'application/x-gtar',
            'gz ' => 'application/gzip',
            'hlp' => 'application/mshelp',
            'hqx' => 'application/mac-binhex40',
            'htm' => 'text/html',
            'html' => 'text/html',
            'ico' => 'image/x-icon',
            'ief' => 'image/ief',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'application/x-javascript',
            'js' => 'text/javascript',
            'latex' => 'application/x-latex',
            'mcf' => 'image/vasa',
            'mid' => 'audio/x-midi',
            'midi' => 'audio/x-midi',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2' => 'audio/x-mpeg',
            'mpe' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'pbm' => 'image/x-portable-bitmap',
            'pdf' => 'application/pdf',
            'pgm' => 'image/x-portable-graymap',
            'php' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'png' => 'image/png',
            'pnm' => 'image/x-portable-anymap',
            'pot' => 'application/mspowerpoint',
            'ppm' => 'image/x-portable-pixmap',
            'pps' => 'application/mspowerpoint',
            'ppt' => 'application/mspowerpoint',
            'ppz' => 'application/mspowerpoint',
            'ps' => 'application/postscript',
            'qd3' => 'x-world/x-3dmf',
            'qd3d' => 'x-world/x-3dmf',
            'qt' => 'video/quicktime',
            'ra' => 'audio/x-pn-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'rgb' => 'image/x-rgb',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'rtf' => 'text/rtf',
            'rtx' => 'text/richtext',
            'sgm' => 'text/x-sgml',
            'sgml' => 'text/x-sgml',
            'sh' => 'application/x-sh',
            'shtml' => 'text/html',
            'sit' => 'application/x-stuffit',
            'snd' => 'audio/basic',
            'stream' => 'audio/x-qt-stream',
            'swf' => 'application/x-shockwave-flash',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texi' => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'tsv' => 'text/tab-separated-values',
            'txt' => 'text/plain',
            'viv' => 'video/vnd.vivo',
            'vivo' => 'video/vnd.vivo',
            'wav' => 'audio/x-wav',
            'wbmp' => 'image/vnd.wap.wbmp',
            'wml' => 'text/vnd.wap.wml',
            'wrl' => 'model/vrml',
            'xbm' => 'image/x-xbitmap',
            'xhtml' => 'application/xhtml+xml',
            'xla' => 'application/msexcel',
            'xls' => 'application/msexcel',
            'xml' => 'text/xml',
            'xpm' => 'image/x-xpixmap',
            'xwd' => 'image/x-windowdump',
            'z' => 'application/x-compress',
            'zip' => 'application/zip',
        );

        Text::deleteByKey(self::TEXT_NAME);

        foreach ($arrExtensions2Mimetypes as $extension => $mimetype) {
// TODO:  Add proper names for the file types
            $objText = new Text($mimetype, FRONTEND_LANG_ID, 0, self::TEXT_NAME);
            if (!$objText->store()) {
echo("Filetype::errorHandler(): Failed to store Text for type $mimetype<br />");
                continue;
            }
            $text_id = $objText->getId();
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_file_type` (
                    `name_text_id`, `extension`, `mimetype`
                ) VALUES (
                    $text_id, ".addslashes($extension).", ".addslashes($mimetype)."
                )");
            if (!$objResult) {
echo("Filetype::errorHandler(): Failed to store file type $mimetype<br />");
                continue;
            }
        }

        // More to come...

        return false;
    }

}

?>
