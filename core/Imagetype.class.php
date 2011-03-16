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
     * The key last used when {@see getArray()} was called, or false
     * @var   string
     */
    private static $last_key = false;
    /**
     * The array of Imagetypes as initialized by {@see getArray()}, or false
     * @var   array
     */
    private static $arrImagetypes = false;


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
    static function getArray($key='')
    {
        global $objDatabase;

        if (   self::$last_key !== ''
            && self::$last_key !== $key)
            self::reset();
        if (!is_array(self::$arrImagetypes)) {
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
            self::$arrImagetypes = array();
            while (!$objResult->EOF) {
                $strName = $objResult->fields[$arrSqlName['text']];
                if ($strName === null) {
//echo("No text<br />");
                    $strName = '';
                }
                $key = $objResult->fields['key'];
                self::$arrImagetypes[$key] = array(
                    'key' => $key,
                    'text_id' => $objResult->fields[$arrSqlName['id']],
                    'name' => $strName,
                    'width' => $objResult->fields['width'],
                    'height' => $objResult->fields['height'],
                    'quality' => $objResult->fields['quality'],
                );
                $objResult->MoveNext();
            }
            self::$last_key = $key;
//die("Imagetype::getArray($key): got ".var_export(self::$arrImagetypes, true)."<hr />");
        }
        return self::$arrImagetypes;
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


    static function reset()
    {
        self::$last_key = false;
        self::$arrImagetypes = false;
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
        if ($objResult->RecordCount()) {
            if (!Text::deleteById($objResult->fields['text_id']))
                return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_imagetype`
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Returns the Text ID of the Imagetype record with the given key.
     *
     * This works almost the same as {@see recordExists()} does,
     * except that you may have to check the result for null,
     * as the Text entry may be missing for any existing key.
     * @param       string      $key          The type key
     * @return      boolean                   The Text ID or null if the
     *                                        key exists, false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function getTextIdByKey($key)
    {
        global $objDatabase;

        if (empty($key)) return false;
        $objResult = $objDatabase->Execute("
            SELECT `text_id`
              FROM `".DBPREFIX."core_imagetype`
             WHERE `module_id`=".MODULE_ID."
               AND `key`='".addslashes($key)."'");
        if (!$objResult) return self::errorHandler();
        if ($objResult->RecordCount())
            return $objResult->fields['text_id'];
        return false;
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
        $text_id_old = self::getTextIdByKey($key);
        $text_id = Text::replace(
            ($text_id_old ? $text_id_old : null),
            FRONTEND_LANG_ID, $imagetype,
            MODULE_ID, self::TEXT_IMAGETYPE);
        if ($text_id_old === false)
            return self::insert(
                $key, $text_id, $width, $height, $quality
            );
        return self::update(
            $key, $text_id, $width, $height, $quality
        );
    }


    /**
     * Update this image type in the database.
     *
     * Note that associations to module ID and key can *NOT* be modified.
     * If you need to change an image type this way, you have to delete()
     * and re-insert() it.
     * @param       string      $key          The type key
     * @param       integer     $text_id      The type description Text ID
     * @param       integer     $width        The thumbnail width
     * @param       integer     $height       The thumbnail height
     * @param       integer     $quality      The thumbnail quality
     * @return      boolean                   True on success,
     *                                        false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function update(
        $key, $text_id,
        $width='NULL', $height='NULL', $quality='NULL'
    ) {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            UPDATE `".DBPREFIX."core_imagetype`
               SET `text_id`=$text_id,
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
     * @param       integer     $text_id      The type description Text ID
     * @param       integer     $width        The thumbnail width
     * @param       integer     $height       The thumbnail height
     * @param       integer     $quality      The thumbnail quality
     * @return      boolean                   True on success,
     *                                        false otherwise
     * @global      mixed       $objDatabase  Database object
     * @author      Reto Kohli <reto.kohli@comvation.com>
     */
    function insert(
        $key, $text_id,
        $width='NULL', $height='NULL', $quality='NULL'
    ) {
        global $objDatabase;

        $objResult = $objDatabase->Execute("
            INSERT INTO ".DBPREFIX."core_imagetype (
                `module_id`, `key`, `text_id`,
                `width`, `height`, `quality`
            ) VALUES (
                ".MODULE_ID.", '".addslashes($key)."', $text_id,
                $width, $height, $quality
            )");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Display the imagetypes for editing
     *
     * Placeholders:
     * The imagetypes' name is written to IMAGETYPE_NAME, and the key
     * to IMAGETYPE_KEY.  Other fields are IMAGETYPE_WIDTH,
     * IMAGETYPE_HEIGHT, and IMAGETYPE_QUALITY for width, height, and
     * quality, respectively.
     * Some entries from $_CORELANG are set up. Their indices are used as
     * placeholder name as well.
     * If you want your imagetypes to be stored, you *MUST* handle the parameter
     * 'act=imagetypes_edit' in your modules' getPage(), and call this method
     * again.
     * @return  HTML_Template_Sigma   The Template object
     */
    static function edit()
    {
        global $objTemplate, $_CORELANG;

        $result = self::storeFromPost();
        if ($result === true) {
            $objTemplate->setVariable('CONTENT_OK_MESSAGE',
                $_CORELANG['TXT_CORE_IMAGETYPE_STORED_SUCCESSFULLY']);
        } elseif ($result === false) {
            $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',
                $_CORELANG['TXT_CORE_IMAGETYPE_ERROR_STORING']);
        }

        if (!empty($_REQUEST['imagetype_delete_key'])) {
            $result = self::deleteByKey($_REQUEST['imagetype_delete_key']);
            if ($result === true) {
                $objTemplate->setVariable('CONTENT_OK_MESSAGE',
                    $_CORELANG['TXT_CORE_IMAGETYPE_DELETED_SUCCESSFULLY']);
            } elseif ($result === false) {
                $objTemplate->setVariable('CONTENT_STATUS_MESSAGE',
                    $_CORELANG['TXT_CORE_IMAGETYPE_ERROR_DELETING']);
            }
        }
        self::reset();

//$objTemplate->setCurrentBlock();
//echo(nl2br(htmlentities(var_export($objTemplate->getPlaceholderList()))));

        $objTemplateLocal = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        CSRF::add_placeholder($objTemplateLocal);
        $objTemplateLocal->setErrorHandling(PEAR_ERROR_DIE);
        if (!$objTemplateLocal->loadTemplateFile('imagetypes.html'))
            die("Failed to load template imagetypes.html");
        $uri = htmlentities(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
        Html::replaceUriParameter($uri, 'act=imagetypes_edit');
        $objTemplateLocal->setGlobalVariable(
// TODO: Add sorting
            $_CORELANG
          + array(
                'URI_BASE' => $uri,
        ));

        $arrImagetypes = self::getArray();
//echo("Imagetype::edit(): got Array: ".var_export($arrImagetypes, true)."<br />");
        if (!is_array($arrImagetypes)) {
            $objTemplateLocal->setVariable(
                'CONTENT_STATUS_MESSAGE',
                $_CORELANG['TXT_CORE_IMAGETYPE_ERROR_RETRIEVING']
            );
            return $objTemplateLocal;
        }
        if (empty($arrImagetypes)) {
            $objTemplateLocal->setVariable(
                'CONTENT_STATUS_MESSAGE',
                sprintf(
                    $_CORELANG['TXT_CORE_IMAGETYPE_WARNING_NONE_FOUND_FOR_MODULE'],
                    MODULE_ID
                )
            );
            return $objTemplateLocal;
        }

        $i = 0;
        foreach ($arrImagetypes as $key => $arrImagetype) {
            $key     = $arrImagetype['key'];
            $name    = $arrImagetype['name'];
            $width   = $arrImagetype['width'];
            $height  = $arrImagetype['height'];
            $quality = $arrImagetype['quality'];
            $objTemplateLocal->setVariable(array(
                'CORE_IMAGETYPE_ROWCLASS'  => ++$i % 2 + 1,
                'CORE_IMAGETYPE_KEY'       =>
                    $key.
                    Html::getHidden(
                        'imagetype_key['.$key.']', $key, 'imagetype_key-'.$key),
                'CORE_IMAGETYPE_NAME'      =>
                    Html::getInputText(
                        'imagetype_name['.$key.']', $name, 'imagetype_name-'.$key,
                        'style="width: 220px;"'),
                'CORE_IMAGETYPE_WIDTH'     =>
                    Html::getInputText(
                        'imagetype_width['.$key.']', $width, false,
                        'style="width: 120px; text-align: right;"'),
                'CORE_IMAGETYPE_HEIGHT'    =>
                    Html::getInputText(
                        'imagetype_height['.$key.']', $height, false,
                        'style="width: 120px; text-align: right;"'),
                'CORE_IMAGETYPE_QUALITY'   =>
                    Html::getInputText(
                        'imagetype_quality['.$key.']', $quality, false,
                        'style="width: 120px; text-align: right;"'),
                'CORE_IMAGETYPE_FUNCTIONS' =>
                    Html::getBackendFunctions(array(
                        'delete' =>
                            'javascript:delete_imagetype(\''.$key.'\');',
                    )),
            ));
            $objTemplateLocal->parse('core_imagetype_data');
        }
        $objTemplateLocal->touchBlock('core_imagetype_section');
        $objTemplateLocal->parse('core_imagetype_section');
        $objTemplateLocal->setVariable(array(
            'CORE_IMAGETYPE_ROWCLASS'  => 1,
            'CORE_IMAGETYPE_KEY'       =>
                Html::getInputText(
                    'imagetype_key[new]', '', false,
                    'style="width: 220px;"'),
            'CORE_IMAGETYPE_NAME'      =>
                Html::getInputText(
                    'imagetype_name[new]', '', false,
                    'style="width: 220px;"'),
            'CORE_IMAGETYPE_WIDTH'     =>
                Html::getInputText(
                    'imagetype_width[new]', self::DEFAULT_THUMBNAIL_WIDTH, false,
                    'style="width: 120px; text-align: right;"'),
            'CORE_IMAGETYPE_HEIGHT'    =>
                Html::getInputText(
                    'imagetype_height[new]', self::DEFAULT_THUMBNAIL_HEIGHT, false,
                    'style="width: 120px; text-align: right;"'),
            'CORE_IMAGETYPE_QUALITY'   =>
                Html::getInputText(
                    'imagetype_quality[new]', self::DEFAULT_THUMBNAIL_QUALITY, false,
                    'style="width: 120px; text-align: right;"'),
            'CORE_IMAGETYPE_FUNCTIONS' => '',
        ));
        $objTemplateLocal->parse('core_imagetype_data');
        JS::registerCode(self::getJavascript());
        return $objTemplateLocal;
    }


    /**
     * Update and store all imagetypes found in the $_POST array
     * @return  boolean                 True on success,
     *                                  the empty string if none was changed,
     *                                  or false on failure
     */
    static function storeFromPost()
    {
//echo("Imagetype::storeFromPost(): Entered<br />");
        if (empty($_POST['bsubmit'])) return '';
        // Compare POST with current imagetypes.
        // Only store what was changed.
        $arrImagetypes = self::getArray();
        $result = '';
        // The keys don't really change, but we can recognize added
        // entries easily like this
        foreach ($_POST['imagetype_key'] as $key_old => $key_new) {
            // No new Imagetype is to be added if the new key is empty
            if (empty($key_new)) {
                continue;
            }
//echo("TEST: Old key $key_old, new: '$key_new'<br />");
            $key_old = contrexx_stripslashes($key_old);
            $key_new = contrexx_stripslashes($key_new);
            $name    = contrexx_stripslashes($_POST['imagetype_name'][$key_old]);
            $width   = contrexx_stripslashes($_POST['imagetype_width'][$key_old]);
            $height  = contrexx_stripslashes($_POST['imagetype_height'][$key_old]);
            $quality = contrexx_stripslashes($_POST['imagetype_quality'][$key_old]);
            if (   empty($arrImagetypes[$key_old])
                || $name != $arrImagetypes[$key_old]['name']
                || $width != $arrImagetypes[$key_old]['width']
                || $height != $arrImagetypes[$key_old]['height']
                || $quality != $arrImagetypes[$key_old]['quality']
            ) {
//echo("Changed or new<br />");
                if ($result === '') $result = true;
                if (!self::store($key_new, $name, $width, $height, $quality))
                    $result = false;
            }
        }
        return $result;
    }


    static function getJavascript()
    {
        global $_CORELANG;

        return '
function delete_imagetype(key)
{
  name = document.getElementById("imagetype_name-"+key).value;
  if (confirm(name+" ("+key+")\\n\\n'.
      $_CORELANG['TXT_CORE_IMAGETYPE_CONFIRM_DELETE_IMAGETYPE'].'\\n\\n'.
      $_CORELANG['TXT_CORE_IMAGETYPE_ACTION_IS_IRREVERSIBLE'].'"))
    window.location.href = "index.php?'.CSRF::param().
        '&cmd=hotelcard&act=imagetypes_edit&imagetype_delete_key="+key;
}
';
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

die("Imagetype::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_imagetype", $arrTables)) {
            $objResult = $objDatabase->Execute("
                DROP TABLE `".DBPREFIX."core_imagetype`");
            if (!$objResult) return false;
echo("Imagetype::errorHandler(): Created table core_imagetype<br />");
        }
        if (!in_array(DBPREFIX."core_imagetype", $arrTables)) {
            $objResult = $objDatabase->Execute("
                CREATE TABLE IF NOT EXISTS `".DBPREFIX."core_imagetype` (
                  `module_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The ID of the module this image type occurs in',
                  `key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'The key unique for each module ID that identifies the image type',
                  `text_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Relates to core_text.id',
                  `width` INT UNSIGNED NULL DEFAULT NULL,
                  `height` INT UNSIGNED NULL DEFAULT NULL,
                  `quality` INT UNSIGNED NULL DEFAULT NULL,
                  PRIMARY KEY (`module_id`, `key`),
                  UNIQUE (`text_id`)
                ) ENGINE=MYISAM");
            if (!$objResult) return false;
echo("Imagetype::errorHandler(): Created table core_imagetype<br />");
        }

        $arrImagetypes = array(
            // hotelcard image type entries
            array(
              'module_id' => 10013,
              'key'       => 'hotelcard_hotel_title',
              'text'      => array(
                  1 => 'Titelbild', // de
                  2 => 'Title',     // en
                  3 => 'Title',     // fr
                  4 => 'Title',     // it
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
            $text_id = 0;
            foreach ($arrImagetype['text'] as $lang_id => $text) {
                $text_id = Text::replace(
                    $text_id, $lang_id, $text,
                    $arrImagetype['module_id'], self::TEXT_IMAGETYPE);
                if (!$text_id)
die("Imagetype::errorHandler(): Error storing Text");
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

echo("Imagetype::errorHandler(): Inserted image type ".var_export($arrImagetype, true)."<br />");
        }

        // More to come...

        return false;
    }

}

?>
