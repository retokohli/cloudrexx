<?php

/**
 * Image type handling
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

require_once ASCMS_CORE_PATH.'/Text.class.php';

/**
 * Image
 *
 * Includes access methods and data layer.
 * Do not, I repeat, do not access private fields, or even try
 * to access the database directly!
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Imagetype
{
    /**
     * Text key for the image type
     */
    const TEXT_IMAGETYPE = "core_imagetype";

    /**
     * Default thumbnail height used when the type is unknown
     */
    const DEFAULT_THUMBNAIL_WIDTH  = 160;

    /**
     * Default thumbnail height used when the type is unknown
     */
    const DEFAULT_THUMBNAIL_HEIGHT = 120;

    /**
     * Default thumbnail quality used when the type is unknown
     */
    const DEFAULT_THUMBNAIL_QUALITY = 90;


    /**
     * Get an array with image type data.
     *
     * The $key argument may be empty (defaults to false), a single string,
     * or an array of strings.  Only matching keys are returned in the array.
     * The array returned looks like
     *  array(
     *    key => array(
     *      'key' => Image type key,
     *      'text_id' => Image type Text ID,
     *      'name' => Image type name,
     *      'width' => thumbnail width,
     *      'height' => thumbnail height,
     *      'quality' => thumbnail quality,
     *    ),
     *    ... more ...
     *  )
     * The array elements are ordered by key, ascending.
     * Uses the MODULE_ID constant.
     * @static
     * @param   string      $key            The optional type key or key array
     * @return  array                       The type data array on success,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArray($key=false)
    {
        global $objDatabase;

//echo("Imagetype::getArray($key): Entered<br />");
        $arrSqlName = Text::getSqlSnippets(
            '`imagetype`.`text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_IMAGETYPE
        );
        $query = "
            SELECT `imagetype`.`key`,
                   `imagetype`.`width`,
                   `imagetype`.`height`,
                   `imagetype`.`quality`
                   ".$arrSqlName['field']."
              FROM `".DBPREFIX."core_imagetype` AS `imagetype`".
                   $arrSqlName['join']."
             WHERE `imagetype`.`module_id`=".MODULE_ID.
              ($key
                ? ' AND `imagetype`.`key`'.
                  (is_array($key)
                    ? " IN ('".join("','", addslashes($key)).')'
                    : "='".addslashes($key)."'")
                : '')."
             ORDER BY `imagetype`.`key` ASC";
//echo("Imagetype::getArray($key): query $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("Imagetype::getArray($key): query ran, result ".var_export($objResult, true)."<br />");
        if (!$objResult) return self::errorHandler();
//die("Imagetype::getArray($key): No error<br />");
        $arrImagetypes = array();
        while (!$objResult->EOF) {
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objResult->MoveNext();
                continue;
            }
            $arrImagetypes[$key] = array(
                'key' => $objResult->fields['key'],
                'text_id' => $objResult->fields[$arrSqlName['id']],
                'name' => $objResult->fields[$arrSqlName['text']],
                'width' => $objResult->fields['width'],
                'height' => $objResult->fields['height'],
                'quality' => $objResult->fields['quality'],
            );
            $objResult->MoveNext();
        }
//die("Imagetype::getArray($key): got ".var_export($arrImagetypes, true)."<hr />");
        return $arrImagetypes;
    }


    /**
     * Get an array with image type names.
     *
     * See {@see getArray()} for details.
     * The array returned looks like
     *  array(
     *    key => Imagetype name,
     *    ... more ...
     *  )
     * The array elements are ordered by key, ascending.
     * Uses the MODULE_ID constant.
     * @static
     * @param   string      $key            The optional type key or key array
     * @return  array                       The type name array on success,
     *                                      false otherwise
     * @global  mixed       $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function getNameArray($key=false)
    {
        global $objDatabase;

//echo("Imagetype::getNameArray(): Entered<br />");
        $arrImagetypes = self::getArray($key);
        if ($arrImagetypes === false) return false;
        $arrImagetype = array();
        foreach ($arrImagetypes as $arrImagetype) {
            $arrImagetype[$key] = $arrImagetype['name'];
        }
//die("Imagetype::getNameArray($key): got ".var_export($arrImagetype, true)."<hr />");
        return $arrImagetype;
    }


    /**
     * Returns an array with the thumbnail width and height for the
     * Imagetype key
     *
     * If the key is not found, the default sizes are returned in the array.
     * The returned array looks like
     *  array(
     *    0 => width,
     *    1 => height,
     *  )
     * @param   string    $key        The Imagetype key
     * @return  array                 The thumbnail size array
     */
    static function getThumbnailOptions($key)
    {
        $arrImagetype = self::getArray($key);
        if ($arrImagetype === false) return array(
            0 => self::DEFAULT_THUMBNAIL_WIDTH,
            1 => self::DEFAULT_THUMBNAIL_HEIGHT,
        );
        return array(
            0 => $arrImagetype[$key]['width'],
            1 => $arrImagetype[$key]['height'],
        );
    }


    /**
     * Delete matching image types from the database.
     *
     * Also deletes associated Text records.
     * @param       string      $key          The type key
     * @return      boolean                   True on success, false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteByKey($key)
    {
        global $objDatabase;

        if (empty($key)) return false;

        $objResult = $objDatabase->Execute("
            SELECT `text_id`
              FROM `".DBPREFIX."core_imagetype`
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        if (!$objResult->RecordCount()) return true;
        if (!Text::deleteById($objResult->fields['text_id']))
            return false;
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_imagetype`
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Test whether a record with the given module ID and key is already
     * present in the database.
     * @param       string      $key          The type key
     * @return      boolean                   True if the record exists,
     *                                        false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists($key)
    {
        global $objDatabase;

        if (empty($key)) return false;

        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM `".DBPREFIX."core_imagetype`
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        if ($objResult->RecordCount() == 1) return true;
        return false;
    }


    /**
     * Adds or updates the given image type.
     *
     * If a record with the given module ID and key already exists, it is
     * updated, otherwise it is inserted.
     * Also adds or updates the Text entry.  Only the language selected in
     * FRONTEND_LANG_ID is affected.
     * @param       string      $key          The type key
     * @param       string      $imagetype    The type description
     * @param       integer     $width        The thumbnail width
     * @param       integer     $height       The thumbnail height
     * @param       integer     $quality      The thumbnail quality
     * @return      boolean                   True on success,
     *                                        false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store(
        $key, $imagetype,
        $width='NULL', $height='NULL', $quality='NULL'
    ) {
        if (self::recordExists($key))
            return self::update(
                $key, $imagetype,
                $width, $height, $quality
            );
        return self::insert(
            $key, $imagetype,
            $width, $height, $quality
        );
    }


    /**
     * Update this image type in the database.
     *
     * Note that associations to module ID and key can *NOT* be modified.
     * If you need to change an image type this way, you have to delete()
     * and re-insert() it.
     * @param       string      $key          The type key
     * @param       string      $imagetype    The type description
     * @param       integer     $width        The thumbnail width
     * @param       integer     $height       The thumbnail height
     * @param       integer     $quality      The thumbnail quality
     * @return      boolean                   True on success,
     *                                        false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update(
        $key, $strImagetype,
        $width='NULL', $height='NULL', $quality='NULL'
    ) {
        global $objDatabase;

        $arrImagetype = self::getArray($key);
        $imagetype_id = key($arrImagetype);
        $objText = Text::getById($imagetype_id, FRONTEND_LANG_ID);
        if (!$objText)
            $objText = new Text(
                '', FRONTEND_LANG_ID, MODULE_ID, self::TEXT_IMAGETYPE
            );
        $objText->setText($strImagetype);
        if (!$objText->store()) return false;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_imagetype`
               SET `text_id`=".$objText->getId().",
                   `width`=$width,
                   `height`=$height,
                   `quality`=$quality
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Insert this image type into the database.
     *
     * Uses the current language ID found in the FRONTEND_LANG_ID constant.
     * @param       string      $key          The type key
     * @param       string      $imagetype    The type description
     * @param       integer     $width        The thumbnail width
     * @param       integer     $height       The thumbnail height
     * @param       integer     $quality      The thumbnail quality
     * @return      boolean                   True on success,
     *                                        false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert(
        $key, $strImagetype,
        $width='NULL', $height='NULL', $quality='NULL'
    ) {
        global $objDatabase;

        $objText = new Text(
            $strImagetype, FRONTEND_LANG_ID, MODULE_ID, self::TEXT_IMAGETYPE
        );
        if (!$objText->store()) return false;

        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."core_imagetype (
                `module_id`, `key`, `text_id`,
                `width`, `height`, `quality`
            ) VALUES (
                ".MODULE_ID.", '".addslashes($key)."', ".$objText->getId().",
                $width, $height, $quality
            )");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Handle any error occurring in this class.
     *
     * Tries to fix known problems with the database table.
     * @global  mixed     $objDatabase    Database object
     * @return  boolean                   False.  Always.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function errorHandler()
    {
        global $objDatabase;

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_imagetype", $arrTables)) {
            // The table does exist...
            $objResult = $objDatabase->Execute("
                DROP TABLE IF EXISTS `".DBPREFIX."core_imagetype`");
            if (!$objResult) return false;
echo("Imagetype::errorHandler(): Dropped table core_imagetype<br />");
        }
        $objResult = $objDatabase->Execute("
            CREATE TABLE IF NOT EXISTS `".DBPREFIX."core_imagetype` (
              `module_id` INT UNSIGNED NOT NULL COMMENT 'The ID of the module this image type occurs in' ,
              `key` VARCHAR(255) NOT NULL COMMENT 'The key unique for each module ID that identifies the image type' ,
              `text_id` INT UNSIGNED NOT NULL COMMENT 'Relates to core_text.id' ,
              `width` BINARY NULL ,
              `height` INT UNSIGNED NULL ,
              `quality` INT UNSIGNED NULL ,
              PRIMARY KEY (`module_id`, `key`),
              UNIQUE (`text_id`),
              CONSTRAINT `imagetype_text_id`
                FOREIGN KEY (`text_id`)
                REFERENCES `".DBPREFIX."core_text` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE=MYISAM");
        if (!$objResult) return false;
echo("Imagetype::errorHandler(): Created table core_imagetype<br />");

        $arrImagetypes = array(
            // hotelcard image type entries, english
            array(
              'module_id' => 10013,
              'key'       => 'hotelcard_hotel_title',
              'text'      => array(
                  1 => 'Titelbild',
                  2 => 'Title',
                  3 => 'Title',
                  4 => 'Title',
              ),
              'width'     => 120,
              'height'    => 80,
              'quality'   => 95,
            ),
            array(
              'module_id' => 10013,
              'key'       => 'hotelcard_hotel_room',
              'text'      => array(
                  1 => 'Zimmer',
                  2 => 'Room',
                  3 => 'Room',
                  4 => 'Room',
              ),
              'width'     => 120,
              'height'    => 80,
              'quality'   => 95,
            ),
            array(
              'module_id' => 10013,
              'key'       => 'hotelcard_hotel_vicinity',
              'text'      => array(
                  1 => 'Umbgebung',
                  2 => 'Vicinity',
                  3 => 'Vicinity',
                  4 => 'Vicinity',
              ),
              'width'     => 120,
              'height'    => 80,
              'quality'   => 95,
            ),
            array(
              'module_id' => 10013,
              'key'       => 'hotelcard_hotel_lobby',
              'text'      => array(
                  1 => 'Lobby',
                  2 => 'Lobby',
                  3 => 'Lobby',
                  4 => 'Lobby',
              ),
              'width'     => 120,
              'height'    => 80,
              'quality'   => 95,
            ),
        );

        Text::deleteByKey(self::TEXT_IMAGETYPE);

        foreach ($arrImagetypes as $arrImagetype) {
            $text_id = false;
            foreach ($arrImagetype['text'] as $lang_id => $text) {
                $objText = new Text(
                    $text, $lang_id, MODULE_ID, self::TEXT_IMAGETYPE, $text_id);
                if (!$objText->store())
die("Imagetype::errorHandler(): Error storing Text");
                $text_id = $objText->getId();
            }
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_imagetype` (
                  `module_id`, `key`, `text_id`,
                  `width`, `height`, `quality`
                ) VALUES (
                  ".$arrImagetype['module_id'].",
                  '".addslashes($arrImagetype['key'])."',
                  $text_id,
                  ".$arrImagetype['width'].",
                  ".$arrImagetype['height'].",
                  ".$arrImagetype['quality']."
                )");
            if (!$objResult)
die("Imagetype::errorHandler(): Error adding Imagetype");

echo("Imagetype::errorHandler(): Inserted image type ".$arrImagetype['key']."<br />");
        }

        // More to come...

        return false;
    }

}

?>
