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
     * @var     integer         $module_id       The module ID, PRIMARY
     * @access  private
     */
    private $module_id = false;

    /**
     * @var     integer         $text_id         The Text ID, PRIMARY
     * @access  private
     */
    private $text_id = false;

    /**
     * The image type key
     * @var     string          $key             The image type key
     * @access  private
     */
    private $key = '';


    /**
     * No constructor here.  This class is purely static.
    function Image($ord, $imageId=0)
    {
        $this->ord = $ord;
        $this->id = $imageId;
    }
     */


    /**
     * Get an array with image type data.
     *
     * The array returned looks like
     *  array(
     *    'module_id' => module_id,
     *    'key' => key,
     *    'text_id' => text_id,
     *  )
     * @static
     * @param       integer     $module_id      The module ID
     * @param       string      $key            The type key
     * @return      array                       The type data on success,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function getArrayById($module_id, $key)
    {
        global $objDatabase;

        if (empty($module_id) || empty($key)) return false;

        $objResult = $objDatabase->Execute("
            SELECT `type_text_id`
              FROM `".DBPREFIX."core_image_type`
             WHERE `module_id`=$module_id
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return array(
            'module_id' => $objResult->fields['module_id'],
            'key'       => $objResult->fields['key'],
            'text_id'   => $objResult->fields['text_id'],
        );
    }


    /**
     * Delete matching image types from the database.
     *
     * Also deletes associated Text records.
     * @param       integer     $module_id      The module ID
     * @param       string      $key            The type key
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    static function deleteById($module_id, $key)
    {
        global $objDatabase;

        if (empty($module_id) || empty($key)) return false;

        $arrImagetypes = self::getById($module_id, $key);
        if (!is_array($arrImagetypes)) return false;

        foreach ($arrImagetypes as $arrImagetype) {
            if ($this->text_id && !Text::deleteById($arrImagetype['text_id'])) {
                return false;
            }
        }

        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_image_type`
             WHERE `module_id`=$module_id
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Test whether a record with the given module ID and key is already
     * present in the database.
     * @param       integer     $module_id      The module ID
     * @param       string      $key            The type key
     * @return      boolean                     True if the record exists,
     *                                          false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function recordExists($module_id, $key)
    {
        global $objDatabase;

        if (empty($module_id) || empty($key)) return false;

        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM `".DBPREFIX."core_image_type`
             WHERE `module_id`=$module_id
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
     * @param       integer     $module_id      The module ID
     * @param       string      $key            The type key
     * @param       string      $imagetype      The type description
     * @return      boolean     True on success, false otherwise
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function store($module_id, $key, $imagetype)
    {
        if (self::recordExists($module_id, $key))
            return self::update($module_id, $key, $imagetype);
        return self::insert($module_id, $key, $imagetype);
    }


    /**
     * Update this image type in the database.
     *
     * Note that associations to module ID and key can *NOT* be modified.
     * If you need to change an image type this way, you have to delete()
     * and re-insert() it.
     * @param       string      $strImagetype   The type description
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update($strImagetype)
    {
        global $objDatabase;

        $arrImagetype = self::getArrayById($module_id, $key);
        $objText = Text::getById($arrImagetype['text_id'], FRONTEND_LANG_ID);
        if (!$objText)
            // Add missing language entry
            $objText = new Text(
                '', FRONTEND_LANG_ID,
                $module_id, $key, $arrImagetype['text_id']);
        $objText->setText($imagetype);
        if (!$objText->store()) return false;

// This should never be necessary
/*
        $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_image_type`
               SET `type_text_id`=$this->text_id,
             WHERE `module_id`=$module_id
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
*/
        return true;
    }


    /**
     * Insert this image type into the database.
     *
     * Uses the current language ID found in the FRONTEND_LANG_ID constant.
     * @param       string      $imagetype      The type description
     * @param       integer     $module_id      The module ID
     * @param       string      $key            The type key
     * @return      boolean                     True on success, false otherwise
     * @global      mixed       $objDatabase    Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert(
        $strImagetype, $module_id, $key,
        $thumbnail_width='NULL', $thumbnail_height='NULL', $thumbnail_quality='NULL'
    ) {
        global $objDatabase;

        $objText = new Text(FRONTEND_LANG_ID);
        $objText->setName($strImagetype);
        if (!$objText->store()) return false;

        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."core_image_type (
                `module_id`,
                `key`,
                `thumbnail_width`,
                `thumbnail_height`,
                `thumbnail_quality`
            ) VALUES (
                $module_id,
                '".addslashes($key)."',
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
        if (in_array(DBPREFIX."core_image_type", $arrTables)) {
            // The table does exist...
            $objResult = $objDatabase->Execute("
                DROP TABLE IF EXISTS `".DBPREFIX."core_image_type`");
            if (!$objResult) return false;
        }
        $objResult = $objDatabase->Execute("
            CREATE TABLE IF NOT EXISTS `".DBPREFIX."core_image_type` (
              `id` INT UNSIGNED NOT NULL COMMENT 'Relates to core_text.id' ,
              `module_id` INT UNSIGNED NOT NULL COMMENT 'The ID of the module this image type occurs in' ,
              `key` VARCHAR(255) NOT NULL COMMENT 'The key unique for each module ID that identifies the image type' ,
              `thumbnail_width` BINARY NULL ,
              `thumbnail_height` INT UNSIGNED NULL ,
              `thumbnail_quality` INT UNSIGNED NULL ,
              PRIMARY KEY (`id`) ,
              INDEX `image_type_text_id` (`id` ASC) ,
              UNIQUE INDEX `module_key` (`module_id` ASC, `key` ASC) ,
              CONSTRAINT `image_type_text_id`
                FOREIGN KEY (`id` )
                REFERENCES `".DBPREFIX."core_text` (`id` )
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB");
        if (!$objResult) return false;

        // More to come...

        return false;
    }

}

?>
