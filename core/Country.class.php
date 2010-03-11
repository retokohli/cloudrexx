<?php

/**
 * Core Country and Region class
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */

/**
 * Multilanguage text
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Text.class.php';
/**
 * Sorting
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/Sorting.class.php';
/**
 * Setting
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/SettingDb.class.php';
//SettingDb::add('core_country_per_page_backend', 30, 1, SettingDb::TYPE_TEXT, '', 'country');

/**
 * Country helper methods
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class Country
{
    /**
     * Text key
     */
    const TEXT_NAME = 'core_country_name';

    /**
     * Array of all countries
     * @var     array
     * @access  private
     * @see     init()
     */
    private static $arrCountries = false;

    /**
     * The array for success and info messages
     * @var     string
     */
    private static $messages = array();

    /**
     * The array for error messages
     * @var     string
     */
    private static $errors = array();

    /*
     * Array of all country-zone relations
     * @var     array
     * @access  private
     * @see     initCountryRelations()
    private static $arrCountryRelations = false;
     */


    /**
     * Returns the country settings page, always.
     * @return
     */
    static function getPage()
    {
        global $objTemplate, $_CORELANG;

//DBG::activate(DBG_PHP|DBG_ADODB|DBG_LOG_FIREPHP);
        $objTemplate->setVariable(array(
            'CONTENT_NAVIGATION'     =>
                '<a href="'.CONTREXX_DIRECTORY_INDEX.'?cmd=country">'.
                $_CORELANG['TXT_CORE_COUNTRY'].'</a>',
            'CONTENT_TITLE'          => $_CORELANG['TXT_CORE_COUNTRY'],
            'CONTENT_OK_MESSAGE'     => join('<br />', self::$messages),
            'CONTENT_STATUS_MESSAGE' => join('<br />', self::$errors),
            'ADMIN_CONTENT'          => self::settings(),
        ));
//DBG::log("ERROR: ".join('; ', self::$errors));
//DBG::log("MESSAGE: ".join('; ', self::$messages));
//die(self::errorHandler());
    }


    /**
     * Initialise the static $arrCountries array with countries
     * found in the database
     *
     * The array created is of the form
     *  array(
     *    country ID => array(
     *      'id'           => country ID,
     *      'name_text_id' => Text name ID,
     *      'name'         => country name,
     *      'alpha2'   => ISO 2 digit code,
     *      'alpha3'   => ISO 3 digit code,
     *      'is_active'    => boolean,
     *    ),
     *    ... more ...
     *  )
     * Notes:
     *  - The Countries are always shown in the current frontend language
     *    as set in FRONTEND_LANG_ID, except if the optional $lang_id
     *    argument is not empty.
     *  - Empty arguments are set to their default values, which are:
     *    - $lang_id: The current value of the FRONTEND_LANG_ID constant
     *    - $limit:   -1, meaning no limit
     *    - $offset:  0, meaning no offset
     *    - $order:   `name` ASC, meaning ordered by country name, ascending
     * @global  ADONewConnection  $objDatabase
     * @param   integer   $lang_id          The optional language ID
     * @param   integer   $limit            The optional record limit
     * @param   integer   $offset           The optional record offset
     * @param   string    $order            The optional order direction
     * @return  boolean                     True on success, false otherwise
     */
    static function init(
        $lang_id=null, $limit=-1, $offset=0, $order=null
    ) {
        global $objDatabase;

        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        $arrSqlName = Text::getSqlSnippets(
            '`country`.`name_text_id`', $lang_id,
            0, self::TEXT_NAME, 'name'
        );
        if (empty($limit))  $limit  = -1;
        if (empty($offset)) $offset =  0;
        if (empty($order))  $order  = $arrSqlName['text'].' ASC';
        $query = "
            SELECT `country`.`id`,
                   `country`.`name_text_id`,
                   `country`.`alpha2`, `country`.`alpha3`,
                   `country`.`ord`,
                   `country`.`is_active`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_country AS `country`".
                   $arrSqlName['join']."
             ORDER BY $order";
        $objResult = $objDatabase->SelectLimit($query, $limit, $offset);

        if (!$objResult) return self::errorHandler();
        self::$arrCountries = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrCountries[$id] = array(
                'id'           => $id,
                'name_text_id' => $text_id,
                'name'         => $strName,
                'ord'          => $objResult->fields['ord'],
                'alpha2'       => $objResult->fields['alpha2'],
                'alpha3'       => $objResult->fields['alpha3'],
                'is_active'    => $objResult->fields['is_active'],
            );
            $objResult->MoveNext();
        }
//DBG::log("Countries: ".var_export(self::$arrCountries, true));
        return true;
    }


    /**
     * Returns the current number of Country records present in the database
     * @return  integer           The number of records on success,
     *                            false otherwise.
     */
    static function getRecordcount()
    {
        global $objDatabase;

        $query = "
            SELECT COUNT(*) AS `numof_records`
              FROM ".DBPREFIX."core_country";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) return self::errorHandler();
        return $objResult->fields['numof_records'];
    }


    static function getIdByAlpha2($alpha2)
    {
        global $objDatabase;

        $query = "
            SELECT `country`.`id`
              FROM ".DBPREFIX."core_country AS `country`
             WHERE `alpha2`='".addslashes($alpha2)."'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return null;
        return $objResult->fields['id'];
    }


    /**
     * Returns the array of all countries
     * @return  array               The country array on success,
     *                              false otherwise
     * @static
     */
    static function getArray()
    {
        if (empty(self::$arrCountries)) self::init();
        return self::$arrCountries;
    }


    /**
     * Returns the array of all active country names, indexed by their ID
     *
     * If the optional $lang_id parameter is empty, the FRONTEND_LANG_ID
     * constant's value is used instead.
     * @param   integer   $lang_id    The optional language ID
     * @return  array                 The country names array on success,
     *                                false otherwise
     */
    static function getNameArray($lang_id=null)
    {
        static $arrName = false;

        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        if (empty(self::$arrCountries)) {
            $arrName = false;
            self::init($lang_id);
        }
        if (empty($arrName)) {
            foreach (self::$arrCountries as $id => $arrCountry) {
                if ($arrCountry['is_active']) {
                    $arrName[$id] = $arrCountry['name'];
                }
            }
        }
        return $arrName;
    }


    /**
     * Returns the name of the country selected by its ID
     *
     * If a country with the given ID does not exist, returns the empty string.
     * @param   integer   $country_id     The country ID
     * @return  string                    The country name, or the empty string
     * @static
     */
    static function getNameById($country_id)
    {
        if (empty(self::$arrCountries)) self::init();
        if (isset(self::$arrCountries[$country_id]))
            return self::$arrCountries[$country_id]['name'];
        return '';
    }


    /**
     * Returns the ISO 2 code of the country selected by its ID
     *
     * If a country with the given ID does not exist, returns the empty string.
     * @param   integer   $country_id     The country ID
     * @return  string                    The ISO 2 code, or the empty string
     * @static
     */
    static function getAlpha2ById($country_id)
    {
        if (empty(self::$arrCountries)) self::init();
        if (isset(self::$arrCountries[$country_id]))
            return self::$arrCountries[$country_id]['alpha2'];
        return '';
    }


    /**
     * Returns the ISO 3 code of the country selected by its ID
     *
     * If a country with the given ID does not exist, returns the empty string.
     * @param   integer   $country_id     The country ID
     * @return  string                    The ISO 3 code, or the empty string
     * @static
     */
    static function getIso3ById($country_id)
    {
        if (empty(self::$arrCountries)) self::init();
        if (isset(self::$arrCountries[$country_id]))
            return self::$arrCountries[$country_id]['alpha3'];
        return '';
    }


    /**
     * Returns true if the country selected by its ID is active
     *
     * If a country with the given ID does not exist, returns false.
     * @param   integer   $country_id     The country ID
     * @return  boolean                   True if active, false otherwise
     * @static
     */
    static function isActiveById($country_id)
    {
        if (empty(self::$arrCountries)) self::init();
        if (isset(self::$arrCountries[$country_id]))
            return self::$arrCountries[$country_id]['is_active'];
        return '';
    }


    /**
     * Resets the state of the class
     * @return  void
     * @static
     */
    static function reset()
    {
        self::$arrCountries = false;
    }


    /**
     * Returns true if the record for the given ID exists in the database
     *
     * Returns true if the $country_id argument is empty.
     * Returns false both if the ID cannot be found and on failure.
     * @param   integer   $country_id   The Country ID
     * @return  boolean                 True if the Country ID is present,
     *                                  false otherwise.
     */
    static function recordExists($country_id)
    {
        global $objDatabase;

        if (empty($country_id)) {
            return false;
        }
        $objResult = $objDatabase->Execute("
            SELECT 1
              FROM `".DBPREFIX."core_country` AS `country`
             WHERE `country`.`id`=$country_id");
        if (!$objResult) return self::errorHandler();
        return $objResult->EOF;
    }


    /**
     * Stores a Country in the database.
     *
     * Note that you have to {@see init()} the class before calling this
     * method.  Otherwise, updating of existing records will not work!
     * Also decides whether to call {@see insert()} or {@see update()}
     * by means of the class array variable $arrCountries.
     * Optional values equal to null are ignored and not stored.
     * Note, however, that $country_name, $alpha2 and $alpha3 are mandatory
     * and must be non-empty when a new record is to be {@see insert()}ed!
     * @param   string    $alpha2         The ISO 2-character code, or null
     * @param   string    $alpha3         The ISO 3-character code, or null
     * @param   integer   $country_name   The name of the Country, or null
     * @param   integer   $ord            The ordinal value, or null
     * @param   boolean   $is_active      The status, or null
     * @param   integer   $country_id     The Country ID, or null
     * @return  boolean                   True on success, false otherwise
     */
    static function store(
        $alpha2=null, $alpha3=null, $lang_id=null, $country_name=null,
        $ord=null, $is_active=null, $country_id=null
    ) {
        // Store the Country name only if it's set
        $text_id = null;
        if (isset($country_name)) {
            if ($country_id > 0 && isset($country_name)) {
                if (isset(self::$arrCountries[$country_id])) {
                    $text_id = self::$arrCountries[$country_id]['name_text_id'];
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $is_active, $country_id): Found existing Country ID $country_id, Text ID $text_id, language ID $lang_id");
                } else {
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $is_active, $country_id): Country ID $country_id not found");
                }
            }
            $text_id = Text::replace(
                $text_id, $lang_id, $country_name, 0, self::TEXT_NAME);
            if (!$text_id) {
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $is_active, $country_id): Failed to store Text ID $text_id");
                return false;
            }
        }
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $is_active, $country_id): Stored Text ID $text_id");
        if (isset(self::$arrCountries[$country_id]))
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $is_active, $country_id): Updating Country ID $country_id, $country_name");
            return self::update(
                $country_id, $ord, $is_active, $alpha2, $alpha3);
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $is_active, $country_id): Inserting Country ID $country_id, $country_name");
        if (self::insert($alpha2, $alpha3, $text_id,
                $ord, $is_active, $country_id)) return true;
        // If inserting fails, the Text record must be rolled back
        Text::deleteById($text_id);
        return false;
    }


    /**
     * Inserts the Country into the database
     *
     * Note that the Country name is inserted or updated in {@see store()} only.
     * @param   string    $alpha2     The ISO 2-character code
     * @param   string    $alpha3     The ISO 3-character code
     * @param   integer   $text_id    The Text ID of the Country name, or null
     * @param   integer   $ord        The ordinal value, or null
     * @param   boolean   $is_active  The status, or null
     * @param   integer   $country_id The Country ID, or null
     * @return  boolean               True on success, false otherwise
     */
    static function insert(
        $alpha2, $alpha3, $text_id=null, $ord=null,
        $is_active=null, $country_id=null
    ) {
        global $objDatabase;

        if (empty($text_id) || empty($alpha2) || empty($alpha3)) {
//DBG::log("Country::insert(): Error: Trying to store Country with empty name or alpha code");
            return false;
        }
        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."core_country` (
              `id`,
              `name_text_id`,
              `alpha2`,
              `alpha3`,
              `ord`,
              `is_active`
            ) VALUES (
              ".($country_id ? $country_id : 'NULL').",
              ".($text_id    ? $text_id    : 'NULL').",
              '".addslashes($alpha2)."',
              '".addslashes($alpha3)."',
              ".intval($ord).",
              ".intval($is_active)."
            )");
        if (!$objResult) return false; //self::errorHandler();
        return $objDatabase->Insert_ID();
    }


    /**
     * Updates the Country in the database
     *
     * Note that it should never be necessary to update the Text ID,
     * so that parameter is not present here.  Call {@see store()}
     * to update the Country name as well.
     * @param   integer   $country_id The Country ID
     * @param   integer   $ord        The ordinal value, or null
     * @param   boolean   $is_active  The status, or null
     * @param   string    $alpha2     The ISO 2-character code, or null
     * @param   string    $alpha3     The ISO 3-character code, or null
     * @return  boolean               True on success, false otherwise
     */
    static function update(
        $country_id, $ord=null, $is_active=null, $alpha2=null, $alpha3=null
    ) {
        global $objDatabase;

        if (empty($country_id)) {
//die("Country::update($country_id, $text_id, $ord, $is_active, $alpha2, $alpha3): Error: Cannot update without a valid Country ID");
            return false;
        }
        $query = array();
// TODO: If I'm right, then it shouldn't be necessary to update the Text ID ever.
//        if (!empty($text_id)) $query[]  = "`name_text_id`=$text_id";
        if (isset($ord)) $query[]       = "`ord`=$ord";
        if (isset($is_active)) $query[] = "`is_active`=".intval($is_active);
        if (!empty($alpha2)) $query[]   = "`alpha2`='".addslashes($alpha2)."'";
        if (!empty($alpha3)) $query[]   = "`alpha3`='".addslashes($alpha3)."'";
        // Something to do?
        if ($query) {
            $objResult = $objDatabase->Execute("
                UPDATE `".DBPREFIX."core_country`
                   SET ".join(', ', $query)."
                 WHERE `id`=$country_id");
            if (!$objResult) return false; //self::errorHandler();
        }
        return true;
    }


    /**
     * Deletes the Country with the given ID from the database
     * @param   integer   $country_id The Country ID
     * @return  boolean               True on success, false otherwise
     */
    static function deleteById($country_id)
    {
        global $objDatabase, $_CORELANG;

        if (empty(self::$arrCountries)) self::init();
        if (empty(self::$arrCountries[$country_id])) {
//            self::$errors[] = $_CORELANG['TXT_CORE_COUNTRY_ERROR_DELETING_NOT_FOUND'];
            return false;
        }
        $text_id = self::$arrCountries[$country_id]['name_text_id'];
        if (!Text::deleteById($text_id)) {
            return false;
        }
        $query = "
            DELETE FROM `".DBPREFIX."core_country`
             WHERE `id`=".intval($country_id);
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
//            self::$errors[] = $_CORELANG['TXT_CORE_COUNTRY_ERROR_DELETING'];
            return false;
        }
        return true;
    }


    /**
     * Returns the HTML dropdown menu or hidden input field plus name string
     *
     * If there is just one active country, returns a hidden <input> tag with
     * the countries' name appended.  If there are more, returns a dropdown
     * menu with the optional ID preselected and optional onchange method added.
     * @param   string    $menuName   Optional name of the menu,
     *                                defaults to "countryId"
     * @param   string    $selectedId Optional preselected country ID
     * @param   boolean   $activeonly Include inactive countries if false.
     *                                Defaults to true
     * @param   string    $onchange   Optional onchange callback function
     * @return  string                The HTML dropdown menu code
     * @static
     */
    static function getMenu(
        $menuName='countryId', $selectedId='', $activeonly=true, $onchange=''
    ) {
        if (empty(self::$arrCountries)) self::init();
        if (count(self::$arrCountries) == 1) {
            $arrCountry = current(self::$arrCountries);
            return
                '<input name="'.$menuName.'" type="hidden"'.
                ' value="'.$arrCountry['id'].'" />'.
                $arrCountry['name']."\n";
        }
        return
            '<select name="'.$menuName.'" '.
            ($onchange ? ' onchange="'.$onchange.'"' : '').">\n".
            self::getMenuoptions($selectedId, $activeonly).
            "</select>\n";
    }


    /**
     * Returns the HTML code for the countries dropdown menu options
     * @param   string  $selectedId   Optional preselected country ID
     * @param   boolean $activeonly   If true, only active countries
     *                                are added to the options, all otherwise.
     * @return  string                The HTML dropdown menu options code
     * @static
     */
    static function getMenuoptions($selected_id=0, $activeonly=true)
    {
        $strMenuoptions = '';
        if (!is_array(self::$arrCountries)) self::init();
        foreach (self::$arrCountries as $country_id => $arrCountry) {
            if (   $activeonly
                && empty($arrCountry['is_active'])) continue;
            $strMenuoptions .=
                '<option value="'.$country_id.'"'.
                ($selected_id == $country_id ? ' selected="selected"' : '').'>'.
                $arrCountry['name']."</option>\n";
        }
        return $strMenuoptions;
    }


    /**
     * Returns an array of two arrays; one with countries in the given zone,
     * the other with the remaining countries.
     *
     * The array looks like this:
     *  array(
     *    'in' => array(    // Countries in the zone
     *      country ID => array(
     *        'id' => country ID,
     *        'name' => country name,
     *        'text_name_id' => country name Text ID,
     *      ),
     *      ... more ...
     *    ),
     *    'out' => array(   // Countries not in the zone
     *      country ID => array(
     *        'id' => country ID,
     *        'name' => country name,
     *        'text_name_id' => country name Text ID,
     *      ),
     *      ... more ...
     *    ),
     *  );
     * @param   integer     $zone_id        The zone ID
     * @return  array                       Countries array, as described above
     */
    static function getArraysByZoneId($zone_id)
    {
        global $objDatabase;

        if (empty(self::$arrCountries)) self::init();
        // Query relations between zones and countries:
        // Get all country IDs and names
        // associated with that zone ID
        $arrSqlName = Text::getSqlSnippets(
            '`country`.`name_text_id`', FRONTEND_LANG_ID,
            0, self::TEXT_NAME
        );
        $query = "
            SELECT `country`.`id`".$arrSqlName['field']."
              FROM `".DBPREFIX."core_country` AS `country`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `relation`
                ON `country`.`id`=`relation`.`countries_id`
                   ".$arrSqlName['join']."
             WHERE `country`.`is_active`=1
               AND `relation`.`zones_id`=$zone_id

               ORDER BY ".$arrSqlName['text']." ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Initialize the array to avoid notices when one or the other is empty
        $arrZoneCountries = array('in' => array(), 'out' => array());

        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
//DBG::log(("MISSING Name for ID $id"));
                $text_id = $objResult->fields['name_text_id'];
                $objText = Text::getById($text_id, 0);
//DBG::log(("GOT Name for Text ID $text_id: ".$objText->getText()));

                if ($objText) $strName = $objText->getText();
            }
//DBG::log(("IN zone: ID $id - $strName"));
            $arrZoneCountries['in'][$id] = array(
                'id'   => $id,
                'name' => $strName,
            );
            $objResult->MoveNext();
        }
        foreach (self::$arrCountries as $id => $arrCountry) {
            // Country may only be available for the Zone if it's
            // not in yet and it's active
            if (   empty($arrZoneCountries['in'][$id])
                && $arrCountry['is_active']) {
//DBG::log(("OUT zone: ID $id - {$arrCountry['name']}"));
                $arrZoneCountries['out'][$id] = array(
                    'id'   => $id,
                    'name' => $arrCountry['name'],
                );
            }

        }
        return $arrZoneCountries;
    }


    /**
     * Activate the countries whose IDs are listed in the comma separated
     * list of Country IDs
     *
     * Any Country not included in the list is deactivated.
     * @param   string    $strCountryIds    The comma separated list of
     *                                      Country IDs
     * @return  boolean                     True on success, false otherwise
     */
    static function activate($strCountryIds)
    {
        global $objDatabase;

        self::reset();
        $query = "
            UPDATE ".DBPREFIX."core_country
               SET `is_active`=0";
        if (!$objDatabase->Execute($query)) return false;
        $query = "
            UPDATE ".DBPREFIX."core_country
               SET `is_active`=1
             WHERE `id` IN ($strCountryIds)";
        return (boolean)$objDatabase->Execute($query);
    }


    /**
     * Sets up the Country settings page
     * @return  string          The page content
     */
    static function settings()
    {
        global $_CORELANG;

        $objTemplateCountry = new HTML_Template_Sigma(ASCMS_ADMIN_TEMPLATE_PATH);
        $objTemplateCountry->loadTemplateFile('settings_country.html');

        // Appends errors to self::$errors
        self::storeSettings();
        self::storeFromPost();

        $uri = Html::getRelativeUri();
        // Let all links in this tab point here again
        Html::replaceUriParameter($uri, 'active_tab='.SettingDb::getTabIndex());
        // Create a copy of the URI for the Paging, as this is passed by
        // reference and modified
        $uri_paging = $uri;
//DBG::log("URI: $uri");
        $objSorting = new Sorting(
            $uri,
            array(
                'id'        => $_CORELANG['TXT_CORE_COUNTRY_ID'],
                'is_active' => $_CORELANG['TXT_CORE_COUNTRY_IS_ACTIVE'],
                'ord'       => $_CORELANG['TXT_CORE_COUNTRY_ORD'],
                'name'      => $_CORELANG['TXT_CORE_COUNTRY_NAME'],
                'alpha2'    => $_CORELANG['TXT_CORE_COUNTRY_ISO2'],
                'alpha3'    => $_CORELANG['TXT_CORE_COUNTRY_ISO3'],
            ),
            true,
            'order_country'
        );
        SettingDb::init('country');
        $limit = SettingDb::getValue('core_country_per_page_backend');
        $count = self::getRecordcount();
//DBG::log("Order: ".$objSorting->getOrder());
        if (!self::init(
            FRONTEND_LANG_ID,
            $limit,
            Paging::getPosition(),
            $objSorting->getOrder()
        )) {
            self::$errors[] = $_CORELANG['TXT_CORE_COUNTRY_ERROR_INITIALIZING'];
            return false;
        }

        $objTemplateCountry->setGlobalVariable(array(
            'TXT_CORE_FUNCTIONS' => $_CORELANG['TXT_CORE_FUNCTIONS'],
            'TXT_CORE_COUNTRY' =>
                $_CORELANG['TXT_CORE_COUNTRY'].' '.
                sprintf($_CORELANG['TXT_CORE_TOTAL'], $count),
            'TXT_CORE_STORE' => $_CORELANG['TXT_CORE_STORE'],
            'TXT_CORE_COUNTRY_NEW' => $_CORELANG['TXT_CORE_COUNTRY_NEW'],
            'TXT_CORE_COUNTRY_NEW_INFO_HEAD' => $_CORELANG['TXT_CORE_COUNTRY_NEW_INFO_HEAD'],
            'TXT_CORE_COUNTRY_NEW_INFO_BODY' => $_CORELANG['TXT_CORE_COUNTRY_NEW_INFO_BODY'],
            'TXT_CORE_COUNTRY_ID' => $_CORELANG['TXT_CORE_COUNTRY_ID'],
            'TXT_CORE_COUNTRY_IS_ACTIVE' => $_CORELANG['TXT_CORE_COUNTRY_IS_ACTIVE'],
            'TXT_CORE_COUNTRY_ORD' => $_CORELANG['TXT_CORE_COUNTRY_ORD'],
            'TXT_CORE_COUNTRY_NAME' => $_CORELANG['TXT_CORE_COUNTRY_NAME'],
            'TXT_CORE_COUNTRY_ISO2' => $_CORELANG['TXT_CORE_COUNTRY_ISO2'],
            'TXT_CORE_COUNTRY_ISO3' => $_CORELANG['TXT_CORE_COUNTRY_ISO3'],
            'HEAD_SETTINGS_COUNTRY_ID' => $objSorting->getHeaderForField('id'),
            'HEAD_SETTINGS_COUNTRY_IS_ACTIVE' => $objSorting->getHeaderForField('is_active'),
            'HEAD_SETTINGS_COUNTRY_ORD' => $objSorting->getHeaderForField('ord'),
            'HEAD_SETTINGS_COUNTRY_NAME' => $objSorting->getHeaderForField('name'),
            'HEAD_SETTINGS_COUNTRY_ISO2' => $objSorting->getHeaderForField('alpha2'),
            'HEAD_SETTINGS_COUNTRY_ISO3' => $objSorting->getHeaderForField('alpha3'),
            'CORE_SETTINGDB_TAB_INDEX' => SettingDb::getTabIndex(),
            'SETTINGS_COUNTRY_PAGING' =>
                Paging::getPaging($count, null, $uri_paging, '', true, $limit),
        ));
        // Note:  Optionally disable the block 'settings_country_submit'
        // to disable storing changes
        $i = 0;
        foreach (self::$arrCountries as $country_id => $arrCountry) {
            $objTemplateCountry->setVariable(array(
                'SETTINGS_COUNTRY_ROWCLASS' => (++$i % 2 + 1),
                'SETTINGS_COUNTRY_ID' => $country_id,
                'SETTINGS_COUNTRY_IS_ACTIVE' =>
                    ($arrCountry['is_active'] ? HTML_ATTRIBUTE_CHECKED : ''),
// Note that the ordinal value is unused other than in the settings!
                'SETTINGS_COUNTRY_ORD' => $arrCountry['ord'],
                'SETTINGS_COUNTRY_NAME' => $arrCountry['name'],
                'SETTINGS_COUNTRY_ISO2' => $arrCountry['alpha2'],
                'SETTINGS_COUNTRY_ISO3' => $arrCountry['alpha3'],
                'SETTINGS_FUNCTIONS' => Html::getBackendFunctions(
                    array(
                        'delete' => 'delete_country_id='.$country_id,
                    ),
                    array(
                        'delete' =>
                            $_CORELANG['TXT_CORE_COUNTRY_CONFIRM_DELETE']."\\n".
                            $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
                    )
                ),
            ));
            $objTemplateCountry->parse('settings_country_row');
        }
        $objTemplateSetting = null;
        SettingDb::show_external(
            $objTemplateSetting,
            $_CORELANG['TXT_CORE_COUNTRY_EDIT'],
            $objTemplateCountry->get()
        );
        SettingDb::show(
            $objTemplateSetting,
            $uri,
            $_CORELANG['TXT_CORE_COUNTRY_SETTINGS'],
            $_CORELANG['TXT_CORE_COUNTRY_SETTINGS']
        );
        return $objTemplateSetting->get();
    }



    /**
     * Store the Countries posted from the (settings) page
     *
     * Appends any errors encountered to the class array variable $errors.
     * @return  void
     */
    static function storeFromPost()
    {
        global $_CORELANG;

        if (!empty($_REQUEST['delete_country_id'])) {
            if (Country::deleteById($_REQUEST['delete_country_id'])) {
                self::$messages[] = $_CORELANG['TXT_CORE_COUNTRY_DELETED_SUCCESSULLY'];
            } else {
                self::$errors[] = $_CORELANG['TXT_CORE_COUNTRY_DELETING_FAILED'];
            }
            return;
        }
        if (empty($_POST['country_name'])) return;
        Permission::checkAccess(PERMISSION_COUNTRY_EDIT, 'static');
        foreach ($_POST['country_name'] as $country_id => $country_name) {
            $is_active = !empty($_POST['country_is_active'][$country_id]);
            $ord = (isset($_POST['country_ord'][$country_id])
                ? intval($_POST['country_ord'][$country_id]) : null);
            $alpha2 = empty($_POST['country_alpha2'][$country_id])
                ? null : strtoupper($_POST['country_alpha2'][$country_id]);
            $alpha3 = empty($_POST['country_alpha3'][$country_id])
                ? null : strtoupper($_POST['country_alpha3'][$country_id]);
//DBG::log("Country::storeFromPost(): Storing Country ID $country_id, name $country_name, ord $ord, status $is_active, alpha2 $alpha2, alpha3 $alpha3, language ID ".FRONTEND_LANG_ID);
            if (   isset($alpha2) && empty($alpha2)
                || isset($alpha3) && empty($alpha3)
                || !self::store(
                      $alpha2, $alpha3, FRONTEND_LANG_ID, $country_name,
                      $ord, $is_active, $country_id)
            ) {
                self::$errors[] = sprintf(
                    $_CORELANG['TXT_CORE_COUNTRY_ERROR_STORING'],
                    $country_id, $country_name);
            }
        }
        if (empty(self::$errors)) {
            self::$messages[] = $_CORELANG['TXT_CORE_COUNTRY_STORED_SUCCESSULLY'];
        }
    }


    static function storeSettings()
    {
        global $_CORELANG;

        if (!SettingDb::storeFromPost()) {
            self::$errors[] = SettingDb::getErrorString();
        }
    }


    /**
     * Tries to recreate the database table(s) for the class
     *
     * Should be called whenever there's a problem with the database table.
     * @return  boolean             False.  Always.
     */
    function errorHandler()
    {
        global $objDatabase;
        static $break = false;

        if ($break) {
            die("
                Country::errorHandler(): Recursion detected while handling an error.<br /><br />
                This should not happen.  We are very sorry for the inconvenience.<br />
                Please contact customer support: support@comvation.com");
        }
        $break = true;

die("Country::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_country", $arrTables)) {
            $query = "
                DROP TABLE `".DBPREFIX."core_country`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("Country::errorHandler(): Dropped table ".DBPREFIX."core_country<br />");
        }
            $query = "
                CREATE TABLE IF NOT EXISTS `".DBPREFIX."core_country` (
                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
                  `alpha2` CHAR(2) ASCII NOT NULL DEFAULT '',
                  `alpha3` CHAR(3) ASCII NOT NULL DEFAULT '',
                  `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                  `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                  PRIMARY KEY (`id`),
                  INDEX `country_name_text_id` (`name_text_id` ASC)
                ) ENGINE=MYISAM";
            $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
echo("Country::errorHandler(): Failed to create table ".DBPREFIX."core_country<br />");
            return false;
        }
echo("Country::errorHandler(): Created table ".DBPREFIX."core_country<br />");

        // Remove old countries
        $objResult = $objDatabase->Execute("
            TRUNCATE TABLE `".DBPREFIX."core_country`");
        if (!$objResult) {
echo("Country::errorHandler(): Failed to truncate table ".DBPREFIX."core_country");
            return false;
        }

        // Remove old country names
        Text::deleteByKey(self::TEXT_NAME);

//        // Insert all country names into the multilanguage text table.
//        // Takes the data from the old lib_country table.
//        // German
//        $query = "
//            INSERT INTO `".DBPREFIX."core_text` (
//              `id`, `lang_id`, `module_id`, `key`, `text`
//            )
//            SELECT NULL, 1, 0, '".self::TEXT_NAME."', `name`
//              FROM `".DBPREFIX."lib_country`";
//        $objResult = $objDatabase->Execute($query);
//        if (!$objResult) return false;
//
//        // Insert all country name text IDs into the country table
//        $query = "
//            INSERT INTO `".DBPREFIX."core_country` (
//              `name_text_id`, `alpha2`, `alpha3`
//            )
//            SELECT DISTINCT `t`.`id`, `c`.`alpha2`, `c`.`alpha3`
//              FROM `".DBPREFIX."lib_country` AS `c`
//             INNER JOIN `".DBPREFIX."core_text` AS `t`
//                ON `c`.`name`=`t`.`text`
//             WHERE `t`.`key`='".self::TEXT_NAME."'
//               AND `t`.`lang_id`=2";
//        $objResult = $objDatabase->Execute($query);
//        if (!$objResult) return false;

        // Re-insert country records from scratch

        $arrCountries = null;
        if (!@include_once(ASCMS_CORE_PATH.'/countries_iso3166-2.php'))
die("Country::errorHandler(): Failed to load required file ".dirname(__FILE__).'/countries_iso3166-2.php');

        $ord = 0;
        foreach ($arrCountries as $country_id => $arrCountry) {
            $name = $arrCountry[0];
            $alpha2 = $arrCountry[1];
            $alpha3 = $arrCountry[2];
// Not currently in use:
//            $numeric = $arrCountry[3];
//            $iso_full = $arrCountry[4];
            // English (language ID 2) only!
            // The active field defaults to 1.
            if (!self::store($alpha2, $alpha3, 2, $name, $ord, $country_id)) {
echo("Country::errorHandler(): Failed to insert Country ".var_export($arrCountry, true)."<br />");
                continue;
            }
        }

        // Add more languages from the countries.php file.
        $arrCountries = array();
echo("Looking for custom countries file ".ASCMS_CORE_PATH.'/countries.php<br />');
        // Defines $arrCountries array!
        @include_once ASCMS_CORE_PATH.'/countries.php';
//die("Countries: ".var_export($arrCountries, true));

        // Load the current Countries.
        // We don't care about the language, but english exists, and using
        // that is much quicker
        if ($arrCountries) self::init(2);
        foreach ($arrCountries as $alpha2 => $arrLanguage) {
//DBG::log("errorHandler: Looking for Alpha-2 $alpha2");
            $country_id = self::getIdByAlpha2($alpha2);
            if (!$country_id) {
die("Country::errorHandler(): Failed to find Country with Alpha-2 $alpha2");
                continue;
            }

            foreach ($arrLanguage as $lang_id => $country_name) {
//DBG::log("errorHandler: Storing Country ID $country_id, language ID $lang_id, name $country_name");
                if (!self::store(null, null, $lang_id,
                        $country_name, null, null, $country_id)) {
die("Country::errorHandler(): Failed to update Country ID $country_id name $country_name");
                }
            }
        }

        SettingDb::init('country');
        SettingDb::add('core_country_per_page_backend', 30, 1, SettingDb::TYPE_TEXT);

        // More to come...

        // Always!
        return false;
    }

}


/**
 * State helper methods
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class State
{
    /**
     * Text key
     */
    const TEXT_STATE = 'core_country_state';


    /**
     * Initialises and returns the static array with all records
     * from the database
     *
     * Note: The short state names are only present in one language
     * @param   boolean     $fullname   If true, the full state names are
     *                                  looked up and used instead of the
     *                                  short ones
     * @global  ADONewConnection  $objDatabase
     * @return  array                   The state array on success,
     *                                  false otherwise
     */
    function getArray($fullname)
    {
        global $objDatabase;
        static $arrState = false;

        if (empty($arrState)) {
            $query = "
                SELECT DISTINCT `zip`.`state`, `text`.`text`
                  FROM `".DBPREFIX."core_state` AS `zip`
                 INNER JOIN `".DBPREFIX."core_text` AS `text`
                       ON `text`.`key`=CONCAT('".self::TEXT_STATE."_', `zip`.`state`)
                 WHERE `text`.`lang_id`=".FRONTEND_LANG_ID."
                 ORDER BY `state` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            $arrState = array();
            while (!$objResult->EOF) {
                $arrState[$objResult->fields['state']] =
                    ($fullname
                      ? $objResult->fields['text']
                      : $objResult->fields['state']);
                $objResult->MoveNext();
            }
        }
        return $arrState;
    }


    /**
     * Returns the short state name for the given location (city)
     * @param   string    $location     The location (city) name
     * @return  string                  The short state name, if found,
     *                                  false otherwise
     */
    static function getByLocation($location)
    {
        global $objDatabase;

        $query = "
            SELECT `state`
              FROM ".DBPREFIX."core_state
             WHERE `city`='".addslashes($location)."'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return $objResult->fields['state'];
    }


    /**
     * Returns the short state name for the given zip
     * @param   string    $location     The zip
     * @return  string                  The short state name, if found,
     *                                  false otherwise
     */
    static function getByZip($zip)
    {
        global $objDatabase;

        $query = "
            SELECT `state`
              FROM ".DBPREFIX."core_state
             WHERE `zip`='".addslashes($zip)."'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return $objResult->fields['state'];
    }


    /**
     * Returns the full state name in the current language
     *
     * This method uses a little hack to pick Text entries with
     * distinct keys from the Text table.  See {@see Text::getByKey()}
     * for details.
     * Uses the FRONTEND_LANG_ID global constant as the current language ID.
     * If the full name is not present in the Text table, some error
     * message generated by the Text class is returned.
     * @param   string    $state    The short state name
     * @return  string              The full state name on success,
     *                              some error message otherwise
     */
    static function getFullname($state)
    {
        $objText = Text::getByKey(self::TEXT_STATE.'_'.$state, FRONTEND_LANG_ID);
        return $objText->getText();
    }


    /**
     * Handles database errors and tries to fix the tables
     * @return    boolean         False
     */
    static function errorHandler()
    {
        global $objDatabase;

die("State::errorHandler(): Disabled!<br />");

        $query = "DROP TABLE IF EXISTS `".DBPREFIX."core_state`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $query = "
            CREATE TABLE `".DBPREFIX."core_state` (
              `zip`        VARCHAR(8) NOT NULL DEFAULT '',
              `city`       VARCHAR(64) NOT NULL DEFAULT '',
              `state`      VARCHAR(4) NOT NULL DEFAULT '',
              `country_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`zip`, `country_id`),
              INDEX (`city`),
              INDEX (`state`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Data -- to big to load all the time
        $query = file_get_contents(ASCMS_CORE_PATH.'/region_data.sql');
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        $query = "
            DELETE FROM `".DBPREFIX."core_text`
             WHERE `key` LIKE '".self::TEXT_STATE."%'";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Add the full state names to the text table
        // Note: Text::replace() returns the ID
        Text::replace(false, FRONTEND_LANG_ID, 'Appenzell Innerrhoden', 0, self::TEXT_STATE.'_AI');
        Text::replace(false, FRONTEND_LANG_ID, 'Appenzell Ausserrhoden', 0, self::TEXT_STATE.'_AR');
        Text::replace(false, FRONTEND_LANG_ID, 'Bern', 0, self::TEXT_STATE.'_BE');
        Text::replace(false, FRONTEND_LANG_ID, 'Basel Land', 0, self::TEXT_STATE.'_BL');
        Text::replace(false, FRONTEND_LANG_ID, 'Basel Stadt', 0, self::TEXT_STATE.'_BS');
        Text::replace(false, FRONTEND_LANG_ID, 'Deutschland', 0, self::TEXT_STATE.'_DE');
        Text::replace(false, FRONTEND_LANG_ID, 'Fürstentum Liechtenstein', 0, self::TEXT_STATE.'_FL');
        Text::replace(false, FRONTEND_LANG_ID, 'Fribourg', 0, self::TEXT_STATE.'_FR');
        Text::replace(false, FRONTEND_LANG_ID, 'Genève', 0, self::TEXT_STATE.'_GE');
        Text::replace(false, FRONTEND_LANG_ID, 'Glarus', 0, self::TEXT_STATE.'_GL');
        Text::replace(false, FRONTEND_LANG_ID, 'Graubünden', 0, self::TEXT_STATE.'_GR');
        Text::replace(false, FRONTEND_LANG_ID, 'Italien', 0, self::TEXT_STATE.'_IT');
        Text::replace(false, FRONTEND_LANG_ID, 'Jura', 0, self::TEXT_STATE.'_JU');
        Text::replace(false, FRONTEND_LANG_ID, 'Luzern', 0, self::TEXT_STATE.'_LU');
        Text::replace(false, FRONTEND_LANG_ID, 'Neuchâtel', 0, self::TEXT_STATE.'_NE');
        Text::replace(false, FRONTEND_LANG_ID, 'Nidwalden', 0, self::TEXT_STATE.'_NW');
        Text::replace(false, FRONTEND_LANG_ID, 'Obwalden', 0, self::TEXT_STATE.'_OW');
        Text::replace(false, FRONTEND_LANG_ID, 'Sankt Gallen', 0, self::TEXT_STATE.'_SG');
        Text::replace(false, FRONTEND_LANG_ID, 'Schaffhausen', 0, self::TEXT_STATE.'_SH');
        Text::replace(false, FRONTEND_LANG_ID, 'Soloturn', 0, self::TEXT_STATE.'_SO');
        Text::replace(false, FRONTEND_LANG_ID, 'Schwyz', 0, self::TEXT_STATE.'_SZ');
        Text::replace(false, FRONTEND_LANG_ID, 'Thurgau', 0, self::TEXT_STATE.'_TG');
        Text::replace(false, FRONTEND_LANG_ID, 'Ticino', 0, self::TEXT_STATE.'_TI');
        Text::replace(false, FRONTEND_LANG_ID, 'Uri', 0, self::TEXT_STATE.'_UR');
        Text::replace(false, FRONTEND_LANG_ID, 'Vaud', 0, self::TEXT_STATE.'_VD');
        Text::replace(false, FRONTEND_LANG_ID, 'Valais', 0, self::TEXT_STATE.'_VS');
        Text::replace(false, FRONTEND_LANG_ID, 'Zug', 0, self::TEXT_STATE.'_ZG');
        Text::replace(false, FRONTEND_LANG_ID, 'Zürich', 0, self::TEXT_STATE.'_ZH');

        // More to come...

        // Always!
        return false;
    }

}


/**
 * State helper methods
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class Location
{
    private static $arrLocations = false;

    /**
     * Initialises and returns the static array with all records
     * from the database matching the given state, if any
     *
     * The array returned looks like
     *  array(
     *    zip => location,
     *    ... more ...
     *  )
     * Note: The locations are only present in one language.
     * The second optional $format parameter determines the format
     * of the location strings, as processed by sprintf().
     * The parameter %1$s is replaced by the location, and %2$s by the ZIP.
     * The format defaults to '%1$s', the location only.
     * E.g. if you specify the format as '%1$s (%2$s)', you get entries
     * like 'location (zip)'.
     * @global  ADONewConnection  $objDatabase
     * @param   string    $state        The optional state name
     * @param   integer   $zip_pos      The position of the ZIP code
     * @return  boolean                 True on success, false otherwise
     */
    function getArrayByState($state=false, $format='%1$s')
    {
        global $objDatabase;
        static $last_state = null;

        if (empty(self::$arrLocations) || $last_state !== $state) {
            self::$arrLocations = array();
            $query = "
                SELECT DISTINCT `city`, `zip`
                  FROM `".DBPREFIX."core_state`
                 ".($state ? "WHERE `state`='".addslashes($state)."'" : '')."
                 ORDER BY `city` ASC, `zip` ASC";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return self::errorHandler();
            self::$arrLocations = array();
            while (!$objResult->EOF) {
                self::$arrLocations[$objResult->fields['zip']] =
//                self::$arrLocations[$objResult->fields['city']] =
                    sprintf($format,
                        $objResult->fields['city'],
                        $objResult->fields['zip']
                    );
                $objResult->MoveNext();
            }
        }
        $last_state = $state;
//echo("Location::getArrayByState($state): Made array<br />".var_export(self::$arrLocations, true)."<hr />");
        return self::$arrLocations;
    }


    /**
     * Returns the city name for the given zip
     *
     * Single language only.
     * @param   string    $zip          The zip
     * @param   integer   $country_id   The country ID
     * @return  string                  The city name on success,
     *                                  false otherwise
     */
    static function getCityByZip($zip, $country_id)
    {
        global $objDatabase;

        $query = "
            SELECT `city`
              FROM `".DBPREFIX."core_state`
             WHERE `zip`='".addslashes($zip)."'
               AND `country_id`=$country_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return $objResult->fields['city'];
    }


    /**
     * Returns an array with all the parts necessary to build an SQL query
     * joining the state table with any other by the zip code
     *
     * The join used is a "LEFT" one, so both zip and city may be NULL.
     * When you call this method with ('fk_zip', 'alias'), the resulting
     * array looks like
     *  array(
     *    'zip'   => 'location_#_zip',
     *    'city'  => 'alias', // 'location_#_city' if $alias_city is empty
     *    'name'  => 'fk_zip',
     *    'alias' => 'location_#',
     *    'field' => SQL snippet including all field names,
     *    'join'  => SQL snippet with the join statement,
     *  );
     * @param   string    $foreign_zip    The zip foreign key field name
     * @param   string    $alias_city     The optional city field alias
     * @return  array                     The array of SQL snippets
     */
    static function getSqlSnippets($field_foreign_zip, $alias=false)
    {
        static $table_alias_index = 0;

        if (empty($field_foreign_zip)) return false;
        $table_alias = 'location_'.++$table_alias_index;
        $field_zip = $table_alias.'_zip';
        $field_city = ($alias ? $alias : $table_alias.'_city');
        $query_field =
            ', '.$field_foreign_zip.
            ', `'.$table_alias.'`.`zip`  AS `'.$field_zip.'`'.
            ', `'.$table_alias.'`.`city` AS `'.$field_city.'`';
        $query_join =
            ' LEFT JOIN `'.DBPREFIX.'core_zip` as `'.$table_alias.'`'.
            ' ON `'.$table_alias.'`.`zip`='.$field_foreign_zip;
// Unfortunately, we don't have these in multiple lanugages yet
//            ' AND `'.$table_alias.'`.`lang_id`='.$lang_id.
//echo("Text::getSqlSnippets(): got name /$field_id_name/, made ");
            // Remove table name, dot and backticks, if any
            $field_foreign_zip = preg_replace(
                '/`?\w*`?\.?`?(\w+)`?/', '$1', $field_foreign_zip);
//echo("/$field_id_name/<br />");
        return array(
            'zip'   => $field_zip,
            'city'  => $field_city,
            'name'  => $field_foreign_zip,
            'alias' => $table_alias,
            'field' => $query_field,
            'join'  => $query_join,
        );
    }


    /**
     * Returns a comma separated list of city names that begin with
     * the $city string and are in the State $state
     *
     * Either one of the parameters may be empty and will then be ignored.
     * @param   string    $city       The optional beginning of a city name
     * @param   string    $state      The optional State code
     *                                (usually two letters)
     * @return  string                The comma separated list of
     *                                matching city names
     */
    static function getMatching($location='', $state='')
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT `city`
              FROM `".DBPREFIX."core_state`
             WHERE 1
             ".($location ? " AND `city` LIKE '".addslashes($location)."%'" : '')."
             ".($state ? " AND `state`='".addslashes($state)."'" : '')."
             ORDER BY `city` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        $arrLocations = array();
        while (!$objResult->EOF) {
            $arrLocations[] =
                $objResult->fields['city'];
            $objResult->MoveNext();
        }
        return (join(',', $arrLocations));
    }


    /**
     * Returns the HTML code for the locations dropdown menu options
     * @param   string  $state      The optional state
     * @param   string  $selected   Optional preselected region ID
     * @param   string  $format     The optional format, passed on to
     *                              {@see Location::getArrayByState()}
     * @return  string              The HTML dropdown menu options code
     * @static
     */
    static function getMenuoptions($state='', $selected=false, $format=false)
    {
        $strMenuoptions = '';
        foreach (self::getArrayByState($state, $format)
            as $id => $location) {
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($selected == $id ? ' selected="selected"' : '').'>'.
                $location."</option>\n";
        }
        return $strMenuoptions;
    }


    /**
     * Handles Location table query errors
     *
     * Currently, this only calls {@see State::errorHandler()}, as all the
     * tables and data are created there.
     * @return  boolean           False
     */
    static function errorHandler()
    {
        return State::errorHandler();
    }

}


/**
 * NOT CURRENTLY IN USE
 * See {@see State} and {@see Location} classes for some intermediate
 * solution to most of your problems.
 *
 * Region helper methods
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class Region
{
    /**
     * Database key
     */
    const TEXT_NAME = 'core_region_name';

    /**
     * Array of all regions
     * @var     array
     * @access  private
     * @see     init()
     */
    private static $arrRegions = false;

    /**
     * NOT USED
     * Array of all region to parent region relations
     * @var     array
     * @access  private
     * @see     init()
     */
//    private static $arrParentId = false;


    /**
     * Initialise the static array with all regions from the database
     *
     * Notes:
     *  - The regions are always shown in the current frontend language
     *    as set in FRONTEND_LANG_ID.
     *  - The region ID equals the corresponding Text ID.
     * @global  ADONewConnection  $objDatabase
     * @return  boolean                     True on success, false otherwise
     */
    function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`region`.`name_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_NAME
        );
        $query = "
            SELECT `region`.`id`, `region`.`name_text_id`,
                   `region`.`parent_id`, `region`.`country_name_id`,
                   `region`.`ord`, `region`.`is_active`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_region AS `region`".
                   $arrSqlName['join']."
             ORDER BY `region`.`ord` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrRegions = array();
        self::$arrParentId = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['name_text_id'];
            $parent_id = $objResult->fields['parent_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrRegions[$id] = array(
                'id'         => $id,
                'parent_id'  => $parent_id,
                'country_id' => $objResult->fields['country_name_id'],
                'name'       => $strName,
                'ord'        => $objResult->fields['ord'],
                'is_active'  => $objResult->fields['is_active'],
            );
            self::$arrParentId[$id] = $parent_id;
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the array of all regions
     * @return  array               The region array on success,
     *                              false otherwise
     * @static
     */
    static function getArray()
    {
        if (empty(self::$arrRegions) && self::init())
            return self::$arrRegions;
        return false;
    }


    /**
     * Returns the array of all region names, indexed by their ID
     * @return  array               The region names array on success,
     *                              false otherwise
     */
    static function getNameArray()
    {
        static $arrName = false;

        if (empty(self::$arrRegions)) {
            $arrName = false;
            self::init();
        }
        if (empty($arrName)) {
            foreach (self::$arrRegions as $id => $arrRegion) {
                $arrName[$id] = $arrRegion['name'];
            }
        }
        return $arrName;
    }


    /**
     * Returns the name of the region selected by its ID
     *
     * If a region with the given ID does not exist, returns the empty string.
     * @param   integer   $region_id     The region ID
     * @return  string                    The region name, or the empty string
     * @static
     */
    static function getNameById($region_id)
    {
        if (empty(self::$arrRegions)) self::init();
        if (isset(self::$arrRegions[$region_id]))
            return self::$arrRegions[$region_id]['name'];
        return '';
    }


    /**
     * Returns the parent ID of the region selected by its ID
     *
     * If a region with the given ID does not exist, returns zero.
     * @param   integer   $region_id      The region ID
     * @return  string                    The parent ID, or zero
     * @static
     */
    static function getParentIdById($region_id)
    {
        if (empty(self::$arrRegions)) self::init();
        return (isset(self::$arrRegions[$region_id])
            ? self::$arrRegions[$region_id]['parent_id'] : 0
        );
    }


    /**
     * Returns the country ID of the region selected by its ID
     *
     * If a region with the given ID does not exist, returns false.
     * @param   integer   $region_id      The region ID
     * @return  string                    The country ID, or false
     * @static
     */
    static function getCountryIdById($region_id)
    {
        if (empty(self::$arrRegions)) self::init();
        return (isset(self::$arrRegions[$region_id])
            ? self::$arrRegions[$region_id]['country_id'] : false
        );
    }


    /**
     * Returns true if the region selected by its ID is active
     *
     * If a region with the given ID does not exist, returns false.
     * @param   integer   $region_id      The region ID
     * @return  boolean                   True if active, false otherwise
     * @static
     */
    static function isActiveById($region_id)
    {
        if (empty(self::$arrRegions)) self::init();
        return (!empty(self::$arrRegions[$region_id]['is_active']));
    }


    /**
     * Resets the state of the class
     * @return  void
     * @static
     */
    static function reset()
    {
        self::$arrRegions = false;
        self::$arrParentId = false;
    }


    /**
     * Returns the HTML dropdown menu code for the active regions.
     *
     * Frontend use only.
     * @param   string  $selectedId Optional preselected region ID
     * @param   string  $menuName   Optional name of the menu,
     *                              defaults to "regionId"
     * @param   string  $onchange   Optional onchange callback function
     * @return  string              The HTML dropdown menu code
     * @static
     */
    static function getMenu($selectedId='', $menuName='regionId', $onchange='')
    {
        $strMenu =
            '<select name="'.$menuName.'"'.
            ($onchange ? ' onchange="'.$onchange.'"' : '').">\n".
            self::getMenuoptions($selectedId).
            "</select>\n";
        return $strMenu;
    }


    /**
     * Returns the HTML code for the regions dropdown menu options
     *
     * Remembers the last selected ID and the menu options created, so it's
     * very quick to call this again using the same arguments.
     * @param   string  $selectedId   Optional preselected region ID
     * @param   boolean $active_only  If true, only active regions are
     *                                added to the options, all otherwise.
     *                                Defaults to true.
     * @return  string                The HTML dropdown menu options code
     * @static
     */
    static function getMenuoptions($selected_id=0, $active_only=true)
    {
        static $strMenuoptions = '';
        static $last_selected_id = 0;

        if (empty(self::$arrRegions)) {
            $strMenuoptions = '';
            self::init();
        }
        if ($strMenuoptions && $last_selected_id == $selected_id)
            return $strMenuoptions;
        foreach (self::$arrRegions as $id => $arrRegion) {
            if (   $active_only
                && empty($arrRegion['is_active'])) continue;
            $strMenuoptions .=
                '<option value="'.$id.'"'.
                ($selected_id == $id ? ' selected="selected"' : '').'>'.
                $arrRegion['name']."</option>\n";
        }
        $last_selected_id = $selected_id;
        return $strMenuoptions;
    }


    /**
     * Tries to fix or recreate the database table(s) for the class
     *
     * Should be called whenever there's a problem with the database table.
     * @return  boolean             False.  Always.
     */
    function errorHandler()
    {
        global $objDatabase;

die("Region::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (in_array(DBPREFIX."core_region", $arrTables)) {
            $query = "DROP TABLE `".DBPREFIX."core_region`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
        }
        $query = "
            CREATE TABLE `".DBPREFIX."core_region` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name_text_id` INT(10) UNSIGNED NOT NULL,
              `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
              `country_name_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
              `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
              PRIMARY KEY (`id`)
            ) ENGINE=MYISAM";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
echo("Region::errorHandler(): Created table ".DBPREFIX."core_region<br />");

// TODO:  Try to DROP old records

        // Re-insert region records from scratch
// TODO: Define and add regions
        $arrRegion = array(
            // (Name, parent name, country ID)
            array('Bern', '', 204),
            array('Thun', 'Bern', 204),
            array('Thunersee', 'Thun', 204),
        );
        $ord = 0;
        foreach ($arrRegion as $arrRegion) {
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_text` (
                  `id`, `lang_id`, `module_id`, `key`, `text`
                ) VALUES (
                  NULL, 2, 0, '".self::TEXT_NAME."', '".addslashes($arrRegion[0])."'
                )");
            if (!$objResult) {
echo("Region::errorHandler(): Failed to insert Text for Region ".var_export($arrRegion, true)."<br />");
                continue;
            }
            $id = $objDatabase->Insert_ID();
            // The active field defaults to 1
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_region` (
                  `name_text_id`, `parent_id`, `country_name_id`, `ord`
                ) VALUES (
                  $id,
                  (SELECT `id`
                     FROM `".DBPREFIX."core_text`
                    WHERE `lang_id`=2
                      AND `module_id`=".MODULE_ID."
                      AND `key`='".self::TEXT_NAME."'
                      AND `text`='".$arrRegion[1]."'
                  ) OR 0,
                  ".$arrRegion[2].",
                  ".++$ord."
                )");
            if (!$objResult) {
echo("Region::errorHandler(): Failed to insert Region ".var_export($arrRegion, true)."<br />");
                continue;
            }
        }

        // More to come...

        // Always
        return false;
   }

}

?>
