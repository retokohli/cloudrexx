<?php

/**
 * Core Country and Region class
 * @version     3.0.0
 * @since       3.0.0
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
 * Database Tools
 * @ignore
 */
require_once ASCMS_CORE_PATH.'/DbTool.class.php';

/**
 * Country helper methods
 * @version     3.0.0
 * @since       3.0.0
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
    private static $arrCountries = null;

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
     * Initialises the class array of Countries
     *
     * Calls {@see getArray()} to accomplish this.
     */
    static function init()
    {
        $count = 0;
        self::$arrCountries = self::getArray($count);
    }


    /**
     * Returns an array of Country arrays
     *
     * The array created is of the form
     *  array(
     *    country ID => array(
     *      'id'           => country ID,
     *      'text_name_id' => Text name ID,
     *      'name'         => country name,
     *      'alpha2'       => alpha-2 (two letter) code,
     *      'alpha3'       => alpha-3 (three letter) code,
     *      'active'       => boolean,
     *      'ord'          => ordinal value,
     *    ),
     *    ... more ...
     *  )
     * Notes:
     *  - The Countries are returned in the current frontend language
     *    as set in FRONTEND_LANG_ID, except if the optional $lang_id
     *    argument is not empty.
     *  - Empty arguments are set to their default values, which are:
     *    - $lang_id: The current value of the FRONTEND_LANG_ID constant
     *    - $limit:   -1, meaning no limit
     *    - $offset:  0, meaning no offset
     *    - $order:   `name` ASC, meaning ordered by country name, ascending
     * @global  ADONewConnection  $objDatabase
     * @param   integer   $count            The record count, by reference
     * @param   integer   $lang_id          The optional language ID
     * @param   integer   $limit            The optional record limit
     * @param   integer   $offset           The optional record offset
     * @param   string    $order            The optional order direction
     * @return  array                       The Country array on success,
     *                                      false otherwise
     */
    static function getArray(
        &$count, $lang_id=null, $limit=-1, $offset=0, $order=null
    ) {
        global $objDatabase;

        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        $arrSqlName = Text::getSqlSnippets(
            '`country`.`text_name_id`', $lang_id,
            0, self::TEXT_NAME, 'name'
        );
        if (empty($limit))  $limit  = -1;
        if (empty($offset)) $offset =  0;
        if (empty($order))  $order  = $arrSqlName['text'].' ASC';
        $count = 0;
        $query = "
            SELECT `country`.`id`,
                   `country`.`text_name_id`,
                   `country`.`alpha2`, `country`.`alpha3`,
                   `country`.`ord`,
                   `country`.`active`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_country AS `country`".
                   $arrSqlName['join']."
             ORDER BY $order";
        $objResult = $objDatabase->SelectLimit($query, $limit, $offset);
        if (!$objResult) return self::errorHandler();
        $arrCountries = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['id'];
            $text_id = $objResult->fields['text_name_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($text_id, 0);
                if ($objText) $strName = $objText->getText();
            }
            $arrCountries[$id] = array(
                'id'           => $id,
                'text_name_id' => $text_id,
                'name'         => $strName,
                'ord'          => $objResult->fields['ord'],
                'alpha2'       => $objResult->fields['alpha2'],
                'alpha3'       => $objResult->fields['alpha3'],
                'active'       => $objResult->fields['active'],
            );
            $objResult->MoveNext();
        }
        $count = DbTool::record_count(DBPREFIX.'core_country');
        if (is_null($count)) return false;
//DBG::log("Country::getArray(): Count: $count, made: ".var_export($arrCountries, true));
        return $arrCountries;
    }


    /**
     * Returns an array of Country data for the given ID
     *
     * The array created is of the form
     *  array(
     *    'id'           => country ID,
     *    'text_name_id' => Text name ID,
     *    'name'         => country name,
     *    'alpha2'       => alpha-2 (two letter) code,
     *    'alpha3'       => alpha-3 (three letter) code,
     *    'active'       => boolean,
     *    'ord'          => ordinal value,
     *  ),
     * The Country is returned in the current frontend language
     * as set in FRONTEND_LANG_ID, except if the optional $lang_id
     * argument is not empty.
     * @global  ADONewConnection  $objDatabase
     * @param   integer   $country_id       The Country ID
     * @param   integer   $lang_id          The optional language ID
     * @return  array                       The Country array on success,
     *                                      false otherwise
     */
    static function getById($country_id, $lang_id=null)
    {
        global $objDatabase;

        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        $arrSqlName = Text::getSqlSnippets(
            '`country`.`text_name_id`', $lang_id,
            0, self::TEXT_NAME, 'name'
        );
        $query = "
            SELECT `country`.`text_name_id`,
                   `country`.`alpha2`, `country`.`alpha3`,
                   `country`.`ord`,
                   `country`.`active`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_country AS `country`".
                   $arrSqlName['join']."
             WHERE `country`.`id`=$country_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        $text_id = $objResult->fields['text_name_id'];
        $strName = $objResult->fields[$arrSqlName['text']];
        if ($strName === null) {
            $objText = Text::getById($text_id, 0);
            if ($objText) $strName = $objText->getText();
        }
        return array(
            'id'           => $country_id,
            'text_name_id' => $text_id,
            'name'         => $strName,
            'ord'          => $objResult->fields['ord'],
            'alpha2'       => $objResult->fields['alpha2'],
            'alpha3'       => $objResult->fields['alpha3'],
            'active'       => $objResult->fields['active'],
        );
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
     * Returns the array of all active country names, indexed by their ID
     *
     * If the optional $lang_id parameter is empty, the FRONTEND_LANG_ID
     * constant's value is used instead.
     * @param   integer   $lang_id    The optional language ID
     * @param   boolean   $full       If true, all Countries are included,
     *                                only active ones otherwise.
     *                                Defaults to false
     * @return  array                 The country names array on success,
     *                                false otherwise
     */
    static function getNameArray($active=true)
    {
        if (is_null(self::$arrCountries)) self::init();
        $arrName = array();
        foreach (self::$arrCountries as $id => $arrCountry) {
            if ($active && empty($arrCountry['active'])) continue;
            $arrName[$id] = $arrCountry['name'];
        }
//die("Names: ".var_export($arrName, true));
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
        if (is_null(self::$arrCountries)) self::init();
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
        if (is_null(self::$arrCountries)) self::init();
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
    static function getAlpha3ById($country_id)
    {
        if (is_null(self::$arrCountries)) self::init();
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
        if (is_null(self::$arrCountries)) self::init();
        if (isset(self::$arrCountries[$country_id]))
            return self::$arrCountries[$country_id]['active'];
        return '';
    }


    /**
     * Resets the state of the class
     * @return  void
     * @static
     */
    static function reset()
    {
        self::$arrCountries = null;
    }


    /**
     * Returns SQL query snippets for the Country name for including
     * in any full query
     *
     * Simply calls {@see Text::getSqlSnippets()}.
     * @param   integer   $lang_id    The optional Language ID.
     *                                Defaults to the FRONTEND_LANG_ID
     *                                constant value
     * @return  array                 The SQL snippet array
     */
    static function getSqlSnippets($lang_id=0)
    {
        $lang_id = intval($lang_id);
        if (empty($lang_id)) $lang_id = FRONTEND_LANG_ID;
        return Text::getSqlSnippets(
            '`country`.`text_name_id`', $lang_id,
            0, self::TEXT_NAME
        );
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
     * Decides whether to call {@see insert()} or {@see update()}
     * by means of calling {@see getById()} if $country_id is valid.
     * Optional values equal to null are ignored and not updated,
     * or inserted with default values.
     * Note, however, that $country_name, $alpha2 and $alpha3 are mandatory
     * and must be non-empty when a new record is to be {@see insert()}ed!
     * @param   string    $alpha2         The ISO 2-character code, or null
     * @param   string    $alpha3         The ISO 3-character code, or null
     * @param   integer   $country_name   The name of the Country, or null
     * @param   integer   $ord            The ordinal value, or null
     * @param   boolean   $active         The active status, or null
     * @param   integer   $country_id     The Country ID, or null
     * @return  boolean                   True on success, false otherwise
     */
    static function store(
        $alpha2=null, $alpha3=null, $lang_id=null, $country_name=null,
        $ord=null, $active=null, $country_id=null
    ) {
        $arrCountry = false;
        if ($country_id) $arrCountry = self::getById($country_id);
        // Store the Country name only if it's set
        $text_id = null;
        if ($country_name != '') {
            if ($arrCountry) {
                $text_id = $arrCountry['text_name_id'];
            }
            $text_id = Text::replace(
                $text_id, $lang_id, $country_name, 0, self::TEXT_NAME);
            if (!$text_id) {
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $active, $country_id): Failed to store Text ID $text_id");
                return false;
            }
        }
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $active, $country_id): Stored Text ID $text_id");
        if ($arrCountry) {
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $active, $country_id): Updating Country ID $country_id, $country_name");
            return self::update(
                $country_id, $ord, $active, $alpha2, $alpha3);
        }
        if (self::insert($alpha2, $alpha3, $text_id,
                $ord, $active, $country_id)) {
//DBG::log("Country::store($alpha2, $alpha3, $lang_id, $country_name, $ord, $active, $country_id): Inserted Country ID $country_id, $country_name");
            return true;
        }
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
     * @param   boolean   $active     The active status, or null
     * @param   integer   $country_id The Country ID, or null
     * @return  boolean               True on success, false otherwise
     */
    static function insert(
        $alpha2, $alpha3, $text_id=null, $ord=null,
        $active=null, $country_id=null
    ) {
        global $objDatabase;

        if (empty($text_id) || empty($alpha2) || empty($alpha3)) {
//DBG::log("Country::insert(): Error: Trying to store Country with empty name or alpha code");
            return false;
        }
        $objResult = $objDatabase->Execute("
            INSERT INTO `".DBPREFIX."core_country` (
              `id`,
              `text_name_id`,
              `alpha2`,
              `alpha3`,
              `ord`,
              `active`
            ) VALUES (
              ".($country_id ? $country_id : 'NULL').",
              ".($text_id    ? $text_id    : 'NULL').",
              '".addslashes($alpha2)."',
              '".addslashes($alpha3)."',
              ".intval($ord).",
              ".intval($active)."
            )");
        if (!$objResult) return false; //self::errorHandler();
        return ($country_id ? $country_id : $objDatabase->Insert_ID());
    }


    /**
     * Updates the Country in the database
     *
     * Note that it should never be necessary to update the Text ID,
     * so that parameter is not present here.  Call {@see store()}
     * to update the Country name as well.
     * @param   integer   $country_id The Country ID
     * @param   integer   $ord        The ordinal value, or null
     * @param   boolean   $active     The active status, or null
     * @param   string    $alpha2     The ISO 2-character code, or null
     * @param   string    $alpha3     The ISO 3-character code, or null
     * @return  boolean               True on success, false otherwise
     */
    static function update(
        $country_id, $ord=null, $active=null, $alpha2=null, $alpha3=null
    ) {
        global $objDatabase;

        if (empty($country_id)) {
//die("Country::update($country_id, $text_id, $ord, $active, $alpha2, $alpha3): Error: Cannot update without a valid Country ID");
            return false;
        }
        $query = array();
// TODO: If I'm right, then it shouldn't be necessary to update the Text ID ever.
//        if (!empty($text_id)) $query[]  = "`text_name_id`=$text_id";
        if (isset($ord))     $query[] = "`ord`=$ord";
        if (isset($active))  $query[] = "`active`=".intval($active);
        if (!empty($alpha2)) $query[] = "`alpha2`='".addslashes($alpha2)."'";
        if (!empty($alpha3)) $query[] = "`alpha3`='".addslashes($alpha3)."'";
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

        if (is_null(self::$arrCountries)) self::init();
        if (empty(self::$arrCountries[$country_id])) {
//            self::$errors[] = $_CORELANG['TXT_CORE_COUNTRY_ERROR_DELETING_NOT_FOUND'];
            return false;
        }
        $text_id = self::$arrCountries[$country_id]['text_name_id'];
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
     * @param   string    $selected   Optional selected country ID
     * @param   boolean   $active     Include inactive countries if false.
     *                                Defaults to true
     * @param   string    $onchange   Optional onchange callback function
     * @return  string                The HTML dropdown menu code
     * @static
     */
    static function getMenu(
        $menuName='countryId', $selected='', $active=true, $onchange=''
    ) {
        if (is_null(self::$arrCountries)) self::init();
        if (count(self::$arrCountries) == 1) {
            $arrCountry = current(self::$arrCountries);
            return
                Html::getHidden($menuName, $arrCountry['id']).
                $arrCountry['name'];
        }
        return Html::getSelect(
            $menuName, self::getNameArray($active), $selected,
            false, $onchange);
    }


    /**
     * Returns the HTML code for the countries dropdown menu options
     * @param   string  $selected     The optional selected Country ID
     * @param   boolean $active       If true, only active countries
     *                                are added to the options, all otherwise.
     * @return  string                The HTML dropdown menu options code
     * @static
     */
    static function getMenuoptions($selected=0, $active=true)
    {
        return Html::getOptions(self::getNameArray($active), $selected);
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

        if (is_null(self::$arrCountries)) self::init();
        // Query relations between zones and countries:
        // Get all country IDs and names
        // associated with that zone ID
        $arrSqlName = Text::getSqlSnippets(
            '`country`.`text_name_id`', FRONTEND_LANG_ID,
            0, self::TEXT_NAME
        );
        $query = "
            SELECT `country`.`id`".$arrSqlName['field']."
              FROM `".DBPREFIX."core_country` AS `country`
             INNER JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `relation`
                ON `country`.`id`=`relation`.`country_id`
                   ".$arrSqlName['join']."
             WHERE `country`.`active`=1
               AND `relation`.`zone_id`=$zone_id
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
                $text_id = $objResult->fields['text_name_id'];
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
                && $arrCountry['active']) {
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
     *                                      to-be-active Country IDs
     * @return  boolean                     True on success, false otherwise
     */
    static function activate($strCountryIds)
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."core_country
               SET active=0
             WHERE id NOT IN ($strCountryIds)";
        if (!$objDatabase->Execute($query)) return false;
        self::reset();
        $query = "
            UPDATE ".DBPREFIX."core_country
               SET active=1
             WHERE id IN ($strCountryIds)";
        return (boolean)$objDatabase->Execute($query);
    }


    /**
     * Sets up the Country settings page
     * @return  string          The page content
     */
    static function settings()
    {
        global $_CORELANG;
        require_once ASCMS_CORE_PATH.'/Sorting.class.php';
        require_once ASCMS_CORE_PATH.'/SettingDb.class.php';

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
                'id'     => $_CORELANG['TXT_CORE_COUNTRY_ID'],
                'active' => $_CORELANG['TXT_CORE_COUNTRY_ACTIVE'],
                'ord'    => $_CORELANG['TXT_CORE_COUNTRY_ORD'],
                'name'   => $_CORELANG['TXT_CORE_COUNTRY_NAME'],
                'alpha2' => $_CORELANG['TXT_CORE_COUNTRY_ISO2'],
                'alpha3' => $_CORELANG['TXT_CORE_COUNTRY_ISO3'],
            ),
            true,
            'order_country'
        );
        SettingDb::init('country');
        $limit = SettingDb::getValue('core_country_per_page_backend');
// TODO: Should be obsoleted ASAP:
        if (empty($limit)) {
            $limit = 30;
            $limit = SettingDb::add(
                'core_country_per_page_backend', $limit, 101,
                SettingDb::TYPE_TEXT, '', 'country'
            );
        }

        $count = 0;
        $arrCountries = self::getArray(
            $count, null, $limit, Paging::getPosition(),
            $objSorting->getOrder());
        if ($arrCountries === false) {
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
            'TXT_CORE_COUNTRY_ACTIVE' => $_CORELANG['TXT_CORE_COUNTRY_ACTIVE'],
            'TXT_CORE_COUNTRY_ORD' => $_CORELANG['TXT_CORE_COUNTRY_ORD'],
            'TXT_CORE_COUNTRY_NAME' => $_CORELANG['TXT_CORE_COUNTRY_NAME'],
            'TXT_CORE_COUNTRY_ISO2' => $_CORELANG['TXT_CORE_COUNTRY_ISO2'],
            'TXT_CORE_COUNTRY_ISO3' => $_CORELANG['TXT_CORE_COUNTRY_ISO3'],
            'HEAD_SETTINGS_COUNTRY_ID' => $objSorting->getHeaderForField('id'),
            'HEAD_SETTINGS_COUNTRY_ACTIVE' => $objSorting->getHeaderForField('active'),
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
        foreach ($arrCountries as $country_id => $arrCountry) {
            $objTemplateCountry->setVariable(array(
                'SETTINGS_COUNTRY_ROWCLASS' => (++$i % 2 + 1),
                'SETTINGS_COUNTRY_ID' => $country_id,
                'SETTINGS_COUNTRY_ACTIVE' =>
                    ($arrCountry['active'] ? HTML_ATTRIBUTE_CHECKED : ''),
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

        self::init();
        if (!empty($_REQUEST['delete_country_id'])) {
            if (Country::deleteById($_REQUEST['delete_country_id'])) {
                self::$messages[] = $_CORELANG['TXT_CORE_COUNTRY_DELETED_SUCCESSULLY'];
            } else {
                self::$errors[] = $_CORELANG['TXT_CORE_COUNTRY_DELETING_FAILED'];
            }
            return;
        }
        if (empty($_POST['country_name'])) return;
// TODO
//        Permission::checkAccess(PERMISSION_COUNTRY_EDIT, 'static');
        foreach ($_POST['country_name'] as $country_id => $country_name) {
            $active = !empty($_POST['country_active'][$country_id]);
            $ord = (isset($_POST['country_ord'][$country_id])
                ? intval($_POST['country_ord'][$country_id]) : null);
            $alpha2 = empty($_POST['country_alpha2'][$country_id])
                ? null : strtoupper($_POST['country_alpha2'][$country_id]);
            $alpha3 = empty($_POST['country_alpha3'][$country_id])
                ? null : strtoupper($_POST['country_alpha3'][$country_id]);
//DBG::log("Country::storeFromPost(): Storing Country ID $country_id, name $country_name, ord $ord, status $active, alpha2 $alpha2, alpha3 $alpha3, language ID ".FRONTEND_LANG_ID);
            if (   isset($alpha2) && empty($alpha2)
                || isset($alpha3) && empty($alpha3)
                || !self::store(
                      $alpha2, $alpha3, FRONTEND_LANG_ID, $country_name,
                      $ord, $active, $country_id)
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
        require_once(ASCMS_CORE_PATH.'/DbTool.class.php');
        static $break = false;

        if ($break) {
            die("
                Country::errorHandler(): Recursion detected while handling an error.<br /><br />
                This should not happen.  We are very sorry for the inconvenience.<br />
                Please contact customer support: support@comvation.com");
        }
        $break = true;

//die("Country::errorHandler(): Disabled!<br />");

//        $objResult = $objDatabase->Execute("
//            ALTER TABLE `".DBPREFIX."core_country`
//            CHANGE `name_text_id` `text_name_id` INT(10) UNSIGNED NOT NULL DEFAULT 0");
//        if (!$objResult) return false;
//die("Country::errorHandler(): Fixed Country table");

        $table_name = DBPREFIX.'core_country';
        $table_structure = array(
            'id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
            'text_name_id' => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
            'alpha2' => array('type' => 'CHAR(2)', 'notnull' => true, 'default' => ''),
            'alpha3' => array('type' => 'CHAR(3)', 'notnull' => true, 'default' => ''),
            'ord' => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'renamefrom' => 'sort_order'),
            'active' => array('type' => 'TINYINT(1)', 'unsigned' => true, 'notnull' => true, 'default' => '1', 'renamefrom' => 'is_active'),
        );

        if (!DbTool::table_exists($table_name)) {
            // There is no previous version, so don't use DbTools::table()
            if (!DbTool::create_table($table_name, $table_structure)) {
die("Country::errorHandler(): Error: failed to create table, code hoe6snwkj");
            }

            // Copy the Countries from the Shop module
            if (DbTool::table_exists(DBPREFIX."module_shop".MODULE_INDEX."_countries")) {
                $objResult = DbTool::sql("
                    SELECT `countries_id`, `countries_name`,
                           `countries_iso_code_2`, `countries_iso_code_3`,
                           `activation_status`
                      FROM ".DBPREFIX."module_shop".MODULE_INDEX."_countries");
                if (!$objResult) {
die("Country::errorHandler(): Error: failed to query existing Country records, code sj3bfi8sn");
                }
                while (!$objResult->EOF) {
                    $id = $objResult->fields['countries_id'];
                    $name = $objResult->fields['countries_name'];
                    $alpha2 = $objResult->fields['countries_iso_code_2'];
                    $alpha3 = $objResult->fields['countries_iso_code_3'];
                    $active = $objResult->fields['activation_status'];
                    $ord = 0;
                    if (!self::store($alpha2, $alpha3, FRONTEND_LANG_ID,
                        $name, $ord, $active, $id)
                    ) {
die("Country::errorHandler(): Error: failed to migrate Country $name, code vnso6lsjbs");
                    }
                    $objResult->MoveNext();
                }
                DbTool::drop_table(DBPREFIX.'modules_shop_countries');
                return false;
            }
        }


        if (DbTool::table_empty($table_name)) {
// TODO:
            // Add new Country records
            $arrCountries = null;
            if (!@include_once(ASCMS_CORE_PATH.'/countries_iso3166-2.php'))
die("Country::errorHandler(): Failed to load required file ".ASCMS_CORE_PATH.'/countries_iso3166-2.php');

            $ord = 0;
            foreach ($arrCountries as $country_id => $arrCountry) {
                $name = $arrCountry[0];
                $alpha2 = $arrCountry[1];
                $alpha3 = $arrCountry[2];
// Not currently in use:
//                $numeric = $arrCountry[3];
//                $iso_full = $arrCountry[4];
                // English (language ID 2) only!
                if (!self::store(
                    $alpha2, $alpha3, 2, $name, ++$ord, true, $country_id)
                ) {
die("Country::errorHandler(): Error: failed to add Country $name, code dgkjhk3ol");
                    continue;
                }
            }

            // Add more languages from the countries_languages.php file,
            // if present
            $arrCountries = array();
            // $arrCountries is redefined in the file
            if (@include_once ASCMS_CORE_PATH.'/countries_languages.php') {
                foreach ($arrCountries as $alpha2 => $arrLanguage) {
//DBG::log("errorHandler: Looking for Alpha-2 $alpha2");
                    $country_id = self::getByAlpha2($alpha2);
                    if ($country_id === false) {
die("Country::errorHandler(): Error: failed to retrieve Country with Alpha-2 $alpha2");
//                        continue;
                    }

                    foreach ($arrLanguage as $lang_id => $country_name) {
//DBG::log("errorHandler: Storing Country ID $country_id, language ID $lang_id, name $country_name");
                        if (!self::store(
                            null, null, $lang_id,
                            $country_name, null, null, $country_id)
                        ) {
die("Country::errorHandler(): Failed to update Country ID $country_id name $country_name");
                        }
                    }
                }
            }
        }
        SettingDb::init('country');
        SettingDb::add(
            'core_country_per_page_backend', 30, 101,
            SettingDb::TYPE_TEXT, '', 'country');

        // More to come...

        // Always!
        return false;
    }

}

?>
