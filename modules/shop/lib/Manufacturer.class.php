<?php

class Manufacturer
{
    /**
     * Text keys
     */
    const TEXT_NAME = 'shop_manufacturer_name';
    const TEXT_URI  = 'shop_manufacturer_uri';

    /**
     * Static class data with the manufacturers
     * @var   array
     */
    private static $arrManufacturer = null;


    /**
     * Initialise the Manufacturer array
     *
     * Uses the FRONTEND_LANG_ID constant to determine the language.
     * The array has the form
     *  array(
     *    'id' => Manufacturer ID,
     *    'name' => Manufacturer name,
     *    'text_name_id' => Manufacturer name Text ID,
     *    'url' => Manufacturer URI,
     *    'text_uri_id' => Manufacturer URI Text ID,
     *  )
     * @static
     * @return  boolean                       True on success, false otherwise
     * @global  ADONewConnection  $objDatabase
     * @global  array             $_ARRAYLANG
     * @todo    Order the Manufacturers by their name
     */
    static function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`manufacturer`.`text_name_id`', FRONTEND_LANG_ID);
        $arrSqlUrl = Text::getSqlSnippets(
            '`manufacturer`.`text_uri_id`', FRONTEND_LANG_ID);
        $query = "
            SELECT `manufacturer`.`id`".
                   $arrSqlName['field'].$arrSqlUrl['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer` as `manufacturer`".
                   $arrSqlName['join'].$arrSqlUrl['join'];
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrManufacturer = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($text_name_id && $strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText) {
// Mark missing language entries
//                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
                }
            }
            $text_uri_id = $objResult->fields[$arrSqlUrl['name']];
            $strUrl = $objResult->fields[$arrSqlUrl['text']];
            // Replace Text in a missing language by another, if available
            if ($text_uri_id && $strUrl === null) {
                $objText = Text::getById($text_uri_id, 0);
                if ($objText) {
// Mark missing language entries
//                  $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strUrl = $objText->getText();
                }
            }
            self::$arrManufacturer[$id] = array(
                'id' => $id,
                'name' => $strName,
                'text_name_id' => $text_name_id,
                'url' => $strUrl,
                'text_uri_id' => $text_uri_id,
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Clears the static data in the class
     *
     * Call this whenever the database has been modified, before you
     * access the manufacturer array for reading
     * @return  void
     * @static
     */
    static function reset()
    {
        self::$arrManufacturer = null;
    }


    /**
     * Inserts a new manufacturer
     * @param   string    $name     The manufacturer name
     * @param   string    $url      The manufacturer URL
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function insert($name, $url)
    {
        global $objDatabase;

        $text_name_id = Text::replace(
            0, FRONTEND_LANG_ID, $name, MODULE_ID, self::TEXT_NAME);
        if (!$text_name_id) return false;
        $text_uri_id = Text::replace(
            0, FRONTEND_LANG_ID, $url, MODULE_ID, self::TEXT_URI);
        if ($text_uri_id) {
            $query = "
                INSERT INTO `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer` (
                    `text_name_id`, `text_uri_id`
                ) VALUES (
                    $text_name_id, $text_uri_id
                )";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) return true;
            Text::deleteById($text_uri_id);
        }
        Text::deleteById($text_name_id);
        return false;
    }


    /**
     * Updates an existing manufacturer
     * @param   string    $name     The manufacturer name
     * @param   string    $url      The manufacturer URL
     * @param   integer   $id       The manufacturer ID
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function update($name, $url, $id)
    {
        global $objDatabase;

        if (is_null(self::$arrManufacturer)) self::init();
        // If the ID is not present in the array already, insert it.
        // This especially applies to zero IDs
        if (empty(self::$arrManufacturer[$id]))
            return self::insert($name, $url);
        // Otherwise, update the present record
        $arrManufacturer = self::$arrManufacturer[$id];
        $text_name_id = Text::replace(
            $arrManufacturer['text_name_id'], FRONTEND_LANG_ID,
            $name, MODULE_ID, self::TEXT_NAME);
        if (!$text_name_id) return false;
        $text_uri_id = Text::replace(
            $arrManufacturer['text_uri_id'], FRONTEND_LANG_ID,
            $url, MODULE_ID, self::TEXT_URI);
        if ($text_uri_id) {
            $query = "
                UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer`
                   SET `text_name_id`=$text_name_id,
                       `text_uri_id`=$text_uri_id
                 WHERE `id`=$id";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) return true;
            Text::deleteById($text_uri_id);
        }
        Text::deleteById($text_name_id);
        return false;
    }


    /**
     * Deletes a manufacturer
     * @param   integer   $id       The manufacturer ID
     * @return  boolean             True on success, false otherwise
     * @static
     */
    static function delete($id)
    {
        global $objDatabase;

        if (empty($id)) return false;
        if (is_null(self::$arrManufacturer)) self::init();
        $arrManufacturer = self::$arrManufacturer[$id];
        if (!Text::deleteById($arrManufacturer['text_name_id'])) return false;
        if (!Text::deleteById($arrManufacturer['text_uri_id'])) return false;
        $query = "
            DELETE FROM `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer`
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        return (bool)$objResult;
    }


    /**
     * Returns the current array of Manufacturers
     *
     * Note that you *SHOULD* re-init() the array after changing the
     * database table.
     * See {@link init()} for details on the array.
     * @return  array               The Manufacturer array
     */
    static function getArray()
    {
        if (is_null(self::$arrManufacturer)) self::init();
        return self::$arrManufacturer;
    }


    /**
     * Returns the array of Manufacturer names
     *
     * Note that you *SHOULD* re-init() the array after changing the
     * database table.
     * @return  array               The Manufacturer name array
     */
    static function getNameArray()
    {
        $arrManufacturerName = array();
        foreach (self::getArray() as $id => $arrManufacturer) {
            $arrManufacturerName[$id] = $arrManufacturer['name'];
        }
        return $arrManufacturerName;
    }


    /**
     * Get the Manufacturer dropdown menu HTML code string.
     *
     * Used in the Product search form, see {@link products()}.
     * @static
     * @param   integer $selectedId     The optional preselected Manufacturer ID
     * @return  string                  The Manufacturer dropdown menu HTML code
     * @global  ADONewConnection
     * @global  array
     */
    static function getMenu($selected_id=0)
    {
DBG::log("Manufacturer::getMenu($selected_id): Manufacturers: ".var_export(self::$arrManufacturer, true));
        return Html::getSelect(
            'manufacturerId', self::getNameArray(),
            $selected_id, false, '',
            'style="width: 180px;"'
        );
    }


    /**
     * Returns the Manufacturer HTML dropdown menu options code
     *
     * Used in the Product search form, see {@link products()}.
     * @static
     * @param   integer $selectedId     The optional preselected Manufacturer ID
     * @param   boolean $include_none   If true, a dummy option for "none" is
     *                                  included
     * @return  string                  The Manufacturer dropdown menu options
     * @global  ADONewConnection  $objDatabase
     */
    static function getMenuoptions($selected_id=0, $include_none=false)
    {
        global $_ARRAYLANG;

        return
            ($include_none
              ? '<option value="0">'.
                $_ARRAYLANG['TXT_SHOP_MANUFACTURER_ALL'].
                '</option>'
              : '').
            Html::getOptions(self::getNameArray(), $selected_id);
    }


    /**
     * Returns the name of the Manufacturer with the given ID
     * @static
     * @param   integer $id             The Manufacturer ID
     * @param   integer $lang_id        The language ID
     * @return  string                  The Manufacturer name on success,
     *                                  or the empty string on failure
     * @global  ADONewConnection
     * @todo    Move this to the Manufacturer class!
     */
    static function getNameById($id, $lang_id)
    {
        if (is_null(self::$arrManufacturer)) self::init();
        if (isset(self::$arrManufacturer[$id]))
            return self::$arrManufacturer[$id]['name'];
        return '';
    }


    /**
     * Returns the URL of the Manufacturers for the given ID
     * @static
     * @param   integer $id             The Manufacturer ID
     * @param   integer $lang_id        The language ID
     * @return  string                  The Manufacturer URL on success,
     *                                  or the empty string on failure
     * @global  ADONewConnection
     * @todo    Move this to the Manufacturer class!
     */
    static function getUrlById($id, $lang_id)
    {
        if (is_null(self::$arrManufacturer)) self::init();
        if (isset(self::$arrManufacturer[$id]))
            return self::$arrManufacturer[$id]['url'];
        return '';
    }


    static function errorHandler()
    {
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');

DBG::activate(DBG_DB_FIREPHP);

        // Fix the Text table first
        Text::errorHandler();

        $table_name = DBPREFIX.'module_shop'.MODULE_INDEX.'_manufacturer';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'auto_increment' => true, 'primary' => true),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'default' => '0', 'renamefrom' => 'name'),
            'text_uri_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => false, 'default' => null, 'renamefrom' => 'url'),
        );
        $table_index =  array();

        if (DbTool::table_exists($table_name)) {
            if (DbTool::column_exists($table_name, 'name')) {
                // Migrate all Manufacturer names to the Text table first
                $objResult = DbTool::sql("
                    SELECT `id`, `name`, `url`
                      FROM `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer`");
                if (!$objResult) {
die("Manufacturer::errorHandler(): Error: failed to query manufacturers, code herjherj4uj");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['id'];
                    $name = $objResult->fields['name'];
                    $uri = $objResult->fields['url'];
                    $text_name_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $name, MODULE_ID, self::TEXT_NAME);
                    if (!$text_name_id) {
die("Manufacturer::errorHandler(): Error: failed to migrate Manufacturer name '$name', code jrjaw36hdds");
                    }
                    $text_uri_id = Text::replace(
                        null, FRONTEND_LANG_ID,
                        $uri, MODULE_ID, self::TEXT_URI);
                    if (!$text_uri_id) {
die("Manufacturer::errorHandler(): Error: failed to migrate Manufacturer URI '$uri', code haerje23hstk");
                    }
                    $objResult2 = DbTool::sql("
                        UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer`
                           SET `name`='$text_name_id',
                               `url`='$text_uri_id'
                         WHERE `id`=$id");
                    if (!$objResult2) {
die("Manufacturer::errorHandler(): Error: failed to update Manufacturer ID $id, code aj47jsseg");
                    }
                    $objResult->MoveNext();
                }
            }
        }

        if (!DbTool::table($table_name, $table_structure, $table_index)) {
die("Manufacturer::errorHandler(): Error: failed to migrate Manufacturer table, code hnaer73hyseew");
        }

        // Always
        return false;
    }

}

?>
