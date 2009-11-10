<?php

class Manufacturer
{
    private static $arrManufacturer = false;

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
     *    'text_url_id' => Manufacturer URI Text ID,
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

        $arrSqlName = Text::getSqlSnippets('`manufacturer`.text_name_id', FRONTEND_LANG_ID);
        $arrSqlUrl = Text::getSqlSnippets('`manufacturer`.text_url_id', FRONTEND_LANG_ID);
        $query = "
            SELECT `manufacturer`.`id`".
                   $arrSqlName['field'].$arrSqlUrl['field']."
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer` as `manufacturer`".
                   $arrSqlName['join'].$arrSqlUrl['join'];
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_name_id = $objResult->fields[$arrSqlName['name']];
            $strName = $objResult->fields[$arrSqlName['text']];
            // Replace Text in a missing language by another, if available
            if ($text_name_id && $strName === null) {
                $objText = Text::getById($text_name_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strName = $objText->getText();
            }
            $text_url_id = $objResult->fields[$arrSqlUrl['name']];
            $strUrl = $objResult->fields[$arrSqlUrl['text']];
            // Replace Text in a missing language by another, if available
            if ($text_url_id && $strUrl === null) {
                $objText = Text::getById($text_url_id, 0);
                if ($objText)
                    $objText->markDifferentLanguage(FRONTEND_LANG_ID);
                    $strUrl = $objText->getText();
            }
            self::$arrManufacturer[$id] = array(
                'id' => $id,
                'name' => $strName,
                'text_name_id' => $text_name_id,
                'url' => $strUrl,
                'text_url_id' => $text_url_id,
            );
            $objResult->MoveNext();
        }
        return true;
    }


    static function insert($name, $url)
    {
        global $objDatabase;

        $objTextName = new Text($name, FRONTEND_LANG_ID, MODULE_ID, TEXT_SHOP_MANUFACTURER_NAME);
        if ($objTextName->store()) {
            $objTextUrl = new Text($url, FRONTEND_LANG_ID, MODULE_ID, TEXT_SHOP_MANUFACTURER_URL);
            if ($objTextUrl->store()) {
                $query = "
                    INSERT INTO ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer (
                        text_name_id, text_url_id
                    ) VALUES (
                        ".$objTextName->getId().",
                        ".$objTextUrl->getId()."
                    )
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) return true;
                Text::deleteById($objTextUrl->getId());
            }
            Text::deleteById($objTextName->getId());
        }
        return false;
    }


    static function update($name, $url, $id)
    {
        global $objDatabase;

        if (empty(self::$arrManufacturer)) self::init();
        // If the ID is not present in the array already, insert it.
        // This especially applies to zero IDs
        if (empty(self::$arrManufacturer[$id]))
            return self::insert($name, $url);
        // Otherwise, update the present record
        $arrManufacturer = self::$arrManufacturer[$id];
        $text_name_id = Text::getById($arrManufacturer['text_name_id'], FRONTEND_LANG_ID);
        if (!$text_name_id) return false;
        $text_url_id = Text::getById($arrManufacturer['text_url_id'], FRONTEND_LANG_ID);
        if (!$text_url_id) return false;
        $text_name_id = Text::replace($text_name_id, FRONTEND_LANG_ID, $name);
        if (!$text_name_id) return false;
        $text_url_id = Text::replace($text_url_id, FRONTEND_LANG_ID, $url);
        if (!$text_url_id) return false;
        $query = "
            UPDATE `".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer`
               SET `text_name_id`=$text_name_id,
                   `text_url_id`=$text_url_id
             WHERE `id`=$id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) return true;
        return false;
    }


    static function delete($id)
    {
        global $objDatabase;

        if (empty($id)) return false;
        if (empty(self::$arrManufacturer)) self::init();
        $arrManufacturer = self::$arrManufacturer[$id];
        if (!Text::deleteById($arrManufacturer['text_name_id'])) return false;
        if (!Text::deleteById($arrManufacturer['text_url_id'])) return false;
        $query = "
            DELETE FROM ".DBPREFIX."module_shop".MODULE_INDEX."_manufacturer
             WHERE id=$id";
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
        if (empty(self::$arrManufacturer)) self::init();
        return self::$arrManufacturer;
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
        if (empty(self::$arrManufacturer)) self::init();
        return Html::getSelect(
            'manufacturerId', self::$arrManufacturer,
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
     * @return  string                  The Manufacturer dropdown menu options
     * @global  ADONewConnection  $objDatabase
     */
    static function getMenuoptions($selected_id=0)
    {
        if (empty(self::$arrManufacturer)) self::init();
        return Html::getOptions(
            self::$arrManufacturer, $selected_id
        );
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
        if (empty(self::$arrManufacturer)) self::init($lang_id);
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
        if (empty(self::$arrManufacturer)) self::init($lang_id);
        if (isset(self::$arrManufacturer[$id]))
            return self::$arrManufacturer[$id]['url'];
        return '';
    }

}

?>
