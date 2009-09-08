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
     * Map known extensions to MIME types
     *
     * Note:  Some extensions may be used twice, so this mapping is flawed!
     * @access  private
     * @var     array
     */
    private static $arrExtensions2MimeTypes = false;

    /**
     * Map MIME types to known extensions
     *
     * Note:  Some MIME types may be used twice, so this mapping is flawed!
     * @access  private
     * @var     array
     */
    private static $MimeTypes2arrExtensions = false;

    /**
     * The default MIME type used if nothing is known about the data
     * @access  private
     * @var     string
     */
    private static $strDefaultType = 'application/octet-stream';


    /**
     * Initialize the array of extensions and mime types on request
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @return  boolean             True on success, false otherwise
     */
    function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`filetype`.`name_text_id`', FRONTEND_LANG_ID,
            0, self::TEXT_NAME
        );
        $query = "
            SELECT `filetype`.`id`,
                   `filetype`.`extension`, `filetype`.`mime_type`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_filetype AS `filetype`".
                   $arrSqlName['join']."
             ORDER BY `filetype`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrExtensions2MimeTypes = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields[$arrSqlName['id']];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrExtensions2MimeTypes[$objResult->fields['extension']] =
                array(
                    'id' => $id,
                    'text_id' => $text_id,
                    'name' => $strName,
                    'mime_type' => $objResult->fields['mime_type'],
                    'extension' => $objResult->fields['extension'],
                );
            self::$arrMimeTypes2Extensions[$objResult->fields['mime_type']] =
                array(
                    'id' => $id,
                    'text_id' => $text_id,
                    'name' => $strName,
                    'mime_type' => $objResult->fields['mime_type'],
                    'extension' => $objResult->fields['extension'],
                );
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
        if (empty(self::$arrExtensions2MimeTypes)) self::init();
        return isset(self::$arrExtensions2MimeTypes[$strExtension]);
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
    static function getMimeTypeForExtension($strExtension)
    {
        if (empty(self::$arrExtensions2MimeTypes)) self::init();
        // Make sure only the extension is present.
        // Chop the file name up to and including  the last dot
        $strChoppedExtension = preg_replace('/^.*\./', '', $strExtension);
        if (self::isKnownExtension($strChoppedExtension))
            return self::$arrExtensions2MimeTypes[$strChoppedExtension]['mime_type'];
        return self::$strDefaultType;
    }


    /**
     * Return the default MIME type
     *
     * The value as stored in {@link $strDefaultType}.
     * @static
     * @return  string                      The default MIME type
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @static
     */
    static function getDefaultType()
    {
        return self::$strDefaultType;
    }


    /**
     * Returns the HTML code for the MIME type dropdown menu
     * @param   string    $selected     The optional selected MIME tpye
     * @return  string                  The menu options HTML code
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getTypeMenuoptions($selected='')
    {
        if (empty(self::$arrExtensions2MimeTypes)) self::init();
        $strMenuoptions = '';
        foreach (self::$arrExtensions2MimeTypes as $extension => $arrType) {
            $mimetype = $arrType['mime_type'];
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

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_file_type", $arrTables)) {
            // The table does exist, but causes errors!  So...
            $objResult = $objDatabase->Execute("
                DROP TABLE `".DBPREFIX."core_file_type`");
            if (!$objResult) return false;
        }

        $objResult = $objDatabase->Execute("
            CREATE TABLE `".DBPREFIX."core_file_type` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT DEFAULT 0,
              `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
              `extension` VARCHAR(16) NULL COMMENT 'Extension without the leading dot',
              `mime_type` VARCHAR(32) NULL COMMENT 'Mime type',
              PRIMARY KEY (`id`),
              UNIQUE INDEX `type` USING BTREE (`extension`(16) ASC, `mime_type`(32) ASC)
            ENGINE = InnoDB");
        if (!$objResult) return false;

        /**
         * Known extensions and corresponding MIME types.
         *
         * Note that these associations are arbitrary!
         * @var     array
         */
        $arrExtensions2MimeTypes = array(
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

        foreach ($arrExtensions2MimeTypes as $extension => $mime_type) {
// TODO:  Add proper names for the file types
            $objText = new Text($mime_type, FRONTEND_LANG_ID, 0, self::TEXT_NAME);
            if (!$objText->store()) {
echo("Filetype::errorHandler(): Failed to store Text for type $mime_type<br />");
                continue;
            }
            $text_id = $objText->getId();
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_file_type` (
                    `name_text_id`, `extension`, `mime_type`
                ) VALUES (
                    $text_id, ".addslashes($extension).", ".addslashes($mime_type)."
                )");
            if (!$objResult) {
echo("Filetype::errorHandler(): Failed to store file type $mime_type<br />");
                continue;
            }
        }

        // More to come...

        return false;
    }

}

?>
