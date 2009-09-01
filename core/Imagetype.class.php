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
     * Get an array with image type data.
     *
     * This array lets you resolve Imagetype text IDs to Imagetype names
     * The array returned looks like
     *  array(
     *    text_id => Imagetype name,
     *    ... more ...
     *  )
     * The array elements are ordered by key, ascending.
     * Uses the MODULE_ID constant.
     * @static
     * @param       string      $key            The optional type key
     * @return      array                       The type data array on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArray($key=false)
    {
        global $objDatabase;

//echo("Imagetype::getArray($key): Entered<br />");
        $arrSqlName = Text::getSqlSnippets(
            '`imagetype`.`text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_IMAGETYPE
        );
        // `imagetype`.`key`
        $query = "
            SELECT 1 ".$arrSqlName['field']."
              FROM `".DBPREFIX."core_imagetype` AS `imagetype`".
                   $arrSqlName['join']."
             WHERE `imagetype`.`module_id`=".MODULE_ID.
              ($key ? " AND `imagetype`.`key`='".addslashes($key)."'" : '')."
             ORDER BY `imagetype`.`key` ASC";
//echo("Imagetype::getArray($key): query $query<br />");
        $objResult = $objDatabase->Execute($query);
//echo("Imagetype::getArray($key): query ran, result ".var_export($objResult, true)."<br />");
        if (!$objResult) return self::errorHandler();
//die("Imagetype::getArray($key): No error<br />");
        $arrImagetype = array();
        while (!$objResult->EOF) {
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objResult->MoveNext();
                continue;
            }
//            $key = $objResult->fields['key'];
            $text_id = $objResult->fields['text_id'];
            $arrImagetype[$text_id] = $strName;
//die("Imagetype::getArray($key): got ID $text_id => imagetype $strName<br />");
            $objResult->MoveNext();
        }
        return $arrImagetype;
    }


    /**
     * Delete matching image types from the database.
     *
     * Also deletes associated Text records.
     * @param       string      $key            The type key
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
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
     * @param       string      $key            The type key
     * @return      boolean                     True if the record exists,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
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
     * @param       string      $key                The type key
     * @param       string      $imagetype          The type description
     * @param       integer     $thumbnail_width    The thumbnail width
     * @param       integer     $thumbnail_height   The thumbnail height
     * @param       integer     $thumbnail_quality  The thumbnail quality
     * @return      boolean                         True on success,
     *                                              false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store(
        $key, $imagetype,
        $thumbnail_width='NULL', $thumbnail_height='NULL', $thumbnail_quality='NULL'
    ) {
        if (self::recordExists($key))
            return self::update(
                $key, $imagetype,
                $thumbnail_width, $thumbnail_height, $thumbnail_quality
            );
        return self::insert(
            $key, $imagetype,
            $thumbnail_width, $thumbnail_height, $thumbnail_quality
        );
    }


    /**
     * Update this image type in the database.
     *
     * Note that associations to module ID and key can *NOT* be modified.
     * If you need to change an image type this way, you have to delete()
     * and re-insert() it.
     * @param       string      $key                The type key
     * @param       string      $imagetype          The type description
     * @param       integer     $thumbnail_width    The thumbnail width
     * @param       integer     $thumbnail_height   The thumbnail height
     * @param       integer     $thumbnail_quality  The thumbnail quality
     * @return      boolean                         True on success,
     *                                              false otherwise
     * @global      mixed       $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update(
        $key, $strImagetype,
        $thumbnail_width='NULL', $thumbnail_height='NULL', $thumbnail_quality='NULL'
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
                   `thumbnail_width`=$thumbnail_width,
                   `thumbnail_height`=$thumbnail_height,
                   `thumbnail_quality`=$thumbnail_quality
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Insert this image type into the database.
     *
     * Uses the current language ID found in the FRONTEND_LANG_ID constant.
     * @param       string      $key                The type key
     * @param       string      $imagetype          The type description
     * @param       integer     $thumbnail_width    The thumbnail width
     * @param       integer     $thumbnail_height   The thumbnail height
     * @param       integer     $thumbnail_quality  The thumbnail quality
     * @return      boolean                         True on success,
     *                                              false otherwise
     * @global      mixed       $objDatabase        Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert(
        $key, $strImagetype,
        $thumbnail_width='NULL', $thumbnail_height='NULL', $thumbnail_quality='NULL'
    ) {
        global $objDatabase;

        $objText = new Text(
            $strImagetype, FRONTEND_LANG_ID, MODULE_ID, self::TEXT_IMAGETYPE
        );
        if (!$objText->store()) return false;

        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."core_imagetype (
                `module_id`,
                `key`,
                `text_id`,
                `thumbnail_width`,
                `thumbnail_height`,
                `thumbnail_quality`
            ) VALUES (
                ".MODULE_ID.",
                '".addslashes($key)."',
                ".$objText->getId().",
                $thumbnail_width,
                $thumbnail_height,
                $thumbnail_quality
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
              `thumbnail_width` BINARY NULL ,
              `thumbnail_height` INT UNSIGNED NULL ,
              `thumbnail_quality` INT UNSIGNED NULL ,
              PRIMARY KEY (`module_id`, `key`),
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
              'module_id'         => 10013,
              'key'               => 'hotelcard_hotel_title',
              'text'              => 'Title',
              'thumbnail_width'   => 120,
              'thumbnail_height'  => 80,
              'thumbnail_quality' => 95,
            ),
            array(
              'module_id'         => 10013,
              'key'               => 'hotelcard_hotel_room',
              'text'              => 'Room',
              'thumbnail_width'   => 120,
              'thumbnail_height'  => 80,
              'thumbnail_quality' => 95,
            ),
            array(
              'module_id'         => 10013,
              'key'               => 'hotelcard_hotel_vicinity',
              'text'              => 'Vicinity',
              'thumbnail_width'   => 120,
              'thumbnail_height'  => 80,
              'thumbnail_quality' => 95,
            ),
            array(
              'module_id'         => 10013,
              'key'               => 'hotelcard_hotel_lobby',
              'text'              => 'Lobby',
              'thumbnail_width'   => 120,
              'thumbnail_height'  => 80,
              'thumbnail_quality' => 95,
            ),
        );

        foreach ($arrImagetypes as $arrImagetype) {
            $text_id = Text::add($arrImagetype['text'], self::TEXT_IMAGETYPE);
            if (!$text_id) die("Imagetype::errorHandler(): Error adding Text");
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_imagetype` (
                  `module_id`, `key`, `text_id`,
                  `thumbnail_width`, `thumbnail_height`, `thumbnail_quality`
                ) VALUES (
                  ".$arrImagetype['module_id'].",
                  '".addslashes($arrImagetype['key'])."',
                  $text_id,
                  ".$arrImagetype['thumbnail_width'].",
                  ".$arrImagetype['thumbnail_height'].",
                  ".$arrImagetype['thumbnail_quality']."
                )");
            if (!$objResult) die("Imagetype::errorHandler(): Error adding Imagetype");
echo("Imagetype::errorHandler(): Inserted image type ".$arrImagetype['key']."<br />");
        }

        // More to come...

        return false;
    }

}

?>
