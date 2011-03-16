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
 * Country helper methods
 * @version     2.2.0
 * @since       2.2.0
 * @package     contrexx
 * @subpackage  core
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @todo        Test!
 */
class Country
{
    /**
     * Database key
     */
    const TEXT_CORE_COUNTRY_NAME = 'core_country_name';

    /**
     * Array of all countries
     * @var     array
     * @access  private
     * @see     init()
     */
    private static $arrCountries = false;

    /*
     * Array of all country-zone relations
     * @var     array
     * @access  private
     * @see     initCountryRelations()
    private static $arrCountryRelations = false;
     */


    /**
     * Initialise the static $arrCountries array with all countries
     * found in the database
     *
     * The array created is of the form
     *  array(
     *    country ID => array(
     *      'id'         => country ID,
     *      'name'       => country name,
     *      'iso_code_2' => ISO 2 digit code,
     *      'iso_code_3' => ISO 3 digit code,
     *      'is_active'  => boolean,
     *    ),
     *    ... more ...
     *  )
     * Notes:
     *  - The Countries are always shown in the current frontend language
     *    as set in FRONTEND_LANG_ID.
     *  - The country ID (field name_text_id) equals the corresponding Text ID.
     * @global  ADONewConnection  $objDatabase
     * @return  boolean                     True on success, false otherwise
     */
    static function init()
    {
        global $objDatabase;

        $arrSqlName = Text::getSqlSnippets(
            '`country`.`name_text_id`', FRONTEND_LANG_ID,
            MODULE_ID, self::TEXT_CORE_COUNTRY_NAME
        );
        $query = "
            SELECT `country`.`name_text_id`,
                   `country`.`iso_code_2`, `country`.`iso_code_3`,
                   `country`.`is_active`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_country AS `country`".
                   $arrSqlName['join']."
             ORDER BY `country`.`ord` ASC
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        self::$arrCountries = array();
        while (!$objResult->EOF) {
            $id = $objResult->fields['name_text_id'];
            $strName = $objResult->fields[$arrSqlName['text']];
            if ($strName === null) {
                $objText = Text::getById($id, 0);
                if ($objText) $strName = $objText->getText();
            }
            self::$arrCountries[$id] = array(
                'id' => $id,
                'name' => $strName,
                'iso_code_2' => $objResult->fields['iso_code_2'],
                'iso_code_3' => $objResult->fields['iso_code_3'],
                'is_active' => $objResult->fields['is_active'],
            );
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Returns the array of all countries
     * @return  array               The country array on success,
     *                              false otherwise
     * @static
     */
    static function getArray()
    {
        if (empty(self::$arrCountries) && self::init())
            return self::$arrCountries;
        return false;
    }


    /**
     * Returns the array of all country names, indexed by their ID
     * @return  array               The country names array on success,
     *                              false otherwise
     */
    static function getNameArray()
    {
        static $arrName = false;

        if (empty(self::$arrCountries)) {
            $arrName = false;
            self::init();
        }
        if (empty($arrName)) {
            foreach (self::$arrCountries as $id => $arrCountry) {
                $arrName[$id] = $arrCountry['name'];
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
    static function getIso2ById($country_id)
    {
        if (empty(self::$arrCountries)) self::init();
        if (isset(self::$arrCountries[$country_id]))
            return self::$arrCountries[$country_id]['iso_code_2'];
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
            return self::$arrCountries[$country_id]['iso_code_3'];
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
    static function flush()
    {
        self::$arrCountries = false;
    }


    /**
     * @todo    Rewrite this for proper use with the modules affected
     *          (i.e. shop)
     * Returns an array of two arrays; one with countries in the given zone,
     * the other with the remaining countries.
     *
     * The array looks like this:
     *  array(
     *    'in' => array(    // Countries in the zone
     *      country ID => array(
     *        'id' => country ID,
     *        'name' => country name,
     *        'name_text_id' => country name Text ID,
     *      ),
     *      ... more ...
     *    ),
     *    'out' => array(   // Countries not in the zone
     *      country ID => array(
     *        'id' => country ID,
     *        'name' => country name,
     *        'name_text_id' => country name Text ID,
     *      ),
     *      ... more ...
     *    ),
     *  );
     * @param   integer     $zone_id        The zone ID
     * @return  array                       Countries array, as described above
    static function getArraysByZoneId($zone_id)
    {
        global $objDatabase;

        if (empty(self::$arrCountries)) self::init();

        // Query relations between zones and countries:
        // Get all country IDs and names
        // associated with that zone ID
//        $arrSqlName = Text::getSqlSnippets(
//            '`country`.`name_text_id`', FRONTEND_LANG_ID,
//            MODULE_ID, TEXT_SHOP_COUNTRY_NAME
//        );
// TEST!
//        $query = "
//            SELECT `country`.`id`, `relation`.`country_id`".
//                   $arrSqlName['field']."
//              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_countries` AS `country`".
//                   $arrSqlName['join']."
//              LEFT JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `relation`
//                ON `country`.`id`=`relation`.`country_id`
//             WHERE `country`.`is_active`=1
//               AND `relation`.`zone_id`=$zone_id
//             ORDER BY ".$arrSqlName['text']." ASC
//        ";
        $query = "
            SELECT `relation`.`countries_id`
              FROM `".DBPREFIX."module_shop".MODULE_INDEX."_rel_countries` AS `relation`
              JOIN `".DBPREFIX."module_shop".MODULE_INDEX."_countries` AS `country`
                ON `country`.`countries_id`=`relation`.`countries_id`
             WHERE `relation`.`zones_id`=$zone_id
             ORDER BY `country`.`countries_name`
        ";
//             WHERE `country`.`activation_is_active`=1
//             ORDER BY ".//$arrSqlName['text']."`country`.`countries_name` ASC
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
        // Initialize the array to avoid notices when one or the other is empty
        $arrZoneCountries = array('in' => array(), 'out' => array());
        while (!$objResult->EOF) {
            $id = $objResult->fields['countries_id'];
            // Country may only be in the Zone if it exists and is active
            if (   empty(self::$arrCountries[$id])
                || empty(self::$arrCountries[$id]['is_active']))
                continue;
            $arrZoneCountries['in'][$id] = array(
                'id' => $id,
                'name' => self::$arrCountries[$id]['name'],
// Probably not needed:
//                'name_text_id' => $name_text_id,
            );
            $objResult->MoveNext();
        }
        foreach (self::$arrCountries as $id => $arrCountry) {
            // Country may only be available for the Zone if it is active
            if (empty($arrZoneCountries['in'][$id])
                && $arrCountry['is_active'])
                $arrZoneCountries['out'][$id] = array(
                    'id' => $id,
                    'name' => $arrCountry['name'],
                );
        }
        return $arrZoneCountries;
    }
     */


    /**
     * Tries to fix or recreate the database table(s) for the class
     *
     * Should be called whenever there's a problem with the database table.
     * @return  boolean             False.  Always.
     */
    function errorHandler()
    {
        global $objDatabase;

die("Country::errorHandler(): Disabled!<br />");

        $arrTables = $objDatabase->MetaTables('TABLES');
        if (!in_array(DBPREFIX."core_country", $arrTables)) {
            $query = "
                CREATE TABLE IF NOT EXISTS `".DBPREFIX."core_country` (
                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
                  `iso_code_2` CHAR(2) ASCII NOT NULL DEFAULT '',
                  `iso_code_3` CHAR(3) ASCII NOT NULL DEFAULT '',
                  `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                  `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                  PRIMARY KEY (`id`),
                  INDEX `country_name_text_id` (`name_text_id` ASC)
                ) ENGINE=MYISAM";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("Country::errorHandler(): Created table ".DBPREFIX."core_country<br />");
        }

        // Remove old countries
        $objResult = $objDatabase->Execute("
            TRUNCATE TABLE `".DBPREFIX."core_country`");
        if (!$objResult)
die("Country::errorHandler(): Failed to truncate table ".DBPREFIX."core_country");

        // Remove old country names
        Text::deleteByKey(self::TEXT_CORE_COUNTRY_NAME);
// Obsolete
//        Text::deleteByKey('CORE_COUNTRY');

/*
        // Insert all country names into the multilanguage text table
        // German
        $query = "
            INSERT INTO `".DBPREFIX."core_text` (
              `id`, `lang_id`, `module_id`, `key`, `text`
            )
            SELECT NULL, 1, 0, '".self::TEXT_CORE_COUNTRY_NAME."', `name`
              FROM `".DBPREFIX."lib_country`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        // Insert all country name text IDs into the country table
        $query = "
            INSERT INTO `".DBPREFIX."core_country` (
              `name_text_id`, `iso_code_2`, `iso_code_3`
            )
            SELECT DISTINCT `t`.`id`, `c`.`iso_code_2`, `c`.`iso_code_3`
              FROM `".DBPREFIX."lib_country` AS `c`
             INNER JOIN `".DBPREFIX."core_text` AS `t`
                ON `c`.`name`=`t`.`text`
             WHERE `t`.`key`='".self::TEXT_CORE_COUNTRY_NAME."'
               AND `t`.`lang_id`=2";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;
*/

        // Re-insert country records from scratch
        $arrCountries = array(
            // (ID (Obsolete), Name, ISO2, ISO3)
            array(1, 'Afghanistan', 'AF', 'AFG'),
            array(2, 'Albania', 'AL', 'ALB'),
            array(3, 'Algeria', 'DZ', 'DZA'),
            array(4, 'American Samoa', 'AS', 'ASM'),
            array(5, 'Andorra', 'AD', 'AND'),
            array(6, 'Angola', 'AO', 'AGO'),
            array(7, 'Anguilla', 'AI', 'AIA'),
            array(8, 'Antarctica', 'AQ', 'ATA'),
            array(9, 'Antigua and Barbuda', 'AG', 'ATG'),
            array(10, 'Argentina', 'AR', 'ARG'),
            array(11, 'Armenia', 'AM', 'ARM'),
            array(12, 'Aruba', 'AW', 'ABW'),
            array(13, 'Australia', 'AU', 'AUS'),
            array(14, 'Österreich', 'AT', 'AUT'),
            array(15, 'Azerbaijan', 'AZ', 'AZE'),
            array(16, 'Bahamas', 'BS', 'BHS'),
            array(17, 'Bahrain', 'BH', 'BHR'),
            array(18, 'Bangladesh', 'BD', 'BGD'),
            array(19, 'Barbados', 'BB', 'BRB'),
            array(20, 'Belarus', 'BY', 'BLR'),
            array(21, 'Belgium', 'BE', 'BEL'),
            array(22, 'Belize', 'BZ', 'BLZ'),
            array(23, 'Benin', 'BJ', 'BEN'),
            array(24, 'Bermuda', 'BM', 'BMU'),
            array(25, 'Bhutan', 'BT', 'BTN'),
            array(26, 'Bolivia', 'BO', 'BOL'),
            array(27, 'Bosnia and Herzegowina', 'BA', 'BIH'),
            array(28, 'Botswana', 'BW', 'BWA'),
            array(29, 'Bouvet Island', 'BV', 'BVT'),
            array(30, 'Brazil', 'BR', 'BRA'),
            array(31, 'British Indian Ocean Territory', 'IO', 'IOT'),
            array(32, 'Brunei Darussalam', 'BN', 'BRN'),
            array(33, 'Bulgaria', 'BG', 'BGR'),
            array(34, 'Burkina Faso', 'BF', 'BFA'),
            array(35, 'Burundi', 'BI', 'BDI'),
            array(36, 'Cambodia', 'KH', 'KHM'),
            array(37, 'Cameroon', 'CM', 'CMR'),
            array(38, 'Canada', 'CA', 'CAN'),
            array(39, 'Cape Verde', 'CV', 'CPV'),
            array(40, 'Cayman Islands', 'KY', 'CYM'),
            array(41, 'Central African Republic', 'CF', 'CAF'),
            array(42, 'Chad', 'TD', 'TCD'),
            array(43, 'Chile', 'CL', 'CHL'),
            array(44, 'China', 'CN', 'CHN'),
            array(45, 'Christmas Island', 'CX', 'CXR'),
            array(46, 'Cocos (Keeling) Islands', 'CC', 'CCK'),
            array(47, 'Colombia', 'CO', 'COL'),
            array(48, 'Comoros', 'KM', 'COM'),
            array(49, 'Congo', 'CG', 'COG'),
            array(50, 'Cook Islands', 'CK', 'COK'),
            array(51, 'Costa Rica', 'CR', 'CRI'),
            array(52, 'Cote D\'Ivoire', 'CI', 'CIV'),
            array(53, 'Croatia', 'HR', 'HRV'),
            array(54, 'Cuba', 'CU', 'CUB'),
            array(55, 'Cyprus', 'CY', 'CYP'),
            array(56, 'Czech Republic', 'CZ', 'CZE'),
            array(57, 'Denmark', 'DK', 'DNK'),
            array(58, 'Djibouti', 'DJ', 'DJI'),
            array(59, 'Dominica', 'DM', 'DMA'),
            array(60, 'Dominican Republic', 'DO', 'DOM'),
            array(61, 'East Timor', 'TP', 'TMP'),
            array(62, 'Ecuador', 'EC', 'ECU'),
            array(63, 'Egypt', 'EG', 'EGY'),
            array(64, 'El Salvador', 'SV', 'SLV'),
            array(65, 'Equatorial Guinea', 'GQ', 'GNQ'),
            array(66, 'Eritrea', 'ER', 'ERI'),
            array(67, 'Estonia', 'EE', 'EST'),
            array(68, 'Ethiopia', 'ET', 'ETH'),
            array(69, 'Falkland Islands (Malvinas)', 'FK', 'FLK'),
            array(70, 'Faroe Islands', 'FO', 'FRO'),
            array(71, 'Fiji', 'FJ', 'FJI'),
            array(72, 'Finland', 'FI', 'FIN'),
            array(73, 'France', 'FR', 'FRA'),
            array(74, 'France, Metropolitan', 'FX', 'FXX'),
            array(75, 'French Guiana', 'GF', 'GUF'),
            array(76, 'French Polynesia', 'PF', 'PYF'),
            array(77, 'French Southern Territories', 'TF', 'ATF'),
            array(78, 'Gabon', 'GA', 'GAB'),
            array(79, 'Gambia', 'GM', 'GMB'),
            array(80, 'Georgia', 'GE', 'GEO'),
            array(81, 'Deutschland', 'DE', 'DEU'),
            array(82, 'Ghana', 'GH', 'GHA'),
            array(83, 'Gibraltar', 'GI', 'GIB'),
            array(84, 'Greece', 'GR', 'GRC'),
            array(85, 'Greenland', 'GL', 'GRL'),
            array(86, 'Grenada', 'GD', 'GRD'),
            array(87, 'Guadeloupe', 'GP', 'GLP'),
            array(88, 'Guam', 'GU', 'GUM'),
            array(89, 'Guatemala', 'GT', 'GTM'),
            array(90, 'Guinea', 'GN', 'GIN'),
            array(91, 'Guinea-bissau', 'GW', 'GNB'),
            array(92, 'Guyana', 'GY', 'GUY'),
            array(93, 'Haiti', 'HT', 'HTI'),
            array(94, 'Heard and Mc Donald Islands', 'HM', 'HMD'),
            array(95, 'Honduras', 'HN', 'HND'),
            array(96, 'Hong Kong', 'HK', 'HKG'),
            array(97, 'Hungary', 'HU', 'HUN'),
            array(98, 'Iceland', 'IS', 'ISL'),
            array(99, 'India', 'IN', 'IND'),
            array(100, 'Indonesia', 'ID', 'IDN'),
            array(101, 'Iran (Islamic Republic of)', 'IR', 'IRN'),
            array(102, 'Iraq', 'IQ', 'IRQ'),
            array(103, 'Ireland', 'IE', 'IRL'),
            array(104, 'Israel', 'IL', 'ISR'),
            array(105, 'Italy', 'IT', 'ITA'),
            array(106, 'Jamaica', 'JM', 'JAM'),
            array(107, 'Japan', 'JP', 'JPN'),
            array(108, 'Jordan', 'JO', 'JOR'),
            array(109, 'Kazakhstan', 'KZ', 'KAZ'),
            array(110, 'Kenya', 'KE', 'KEN'),
            array(111, 'Kiribati', 'KI', 'KIR'),
            array(112, 'Korea, Democratic People\'s Republic of', 'KP', 'PRK'),
            array(113, 'Korea, Republic of', 'KR', 'KOR'),
            array(114, 'Kuwait', 'KW', 'KWT'),
            array(115, 'Kyrgyzstan', 'KG', 'KGZ'),
            array(116, 'Lao People\'s Democratic Republic', 'LA', 'LAO'),
            array(117, 'Latvia', 'LV', 'LVA'),
            array(118, 'Lebanon', 'LB', 'LBN'),
            array(119, 'Lesotho', 'LS', 'LSO'),
            array(120, 'Liberia', 'LR', 'LBR'),
            array(121, 'Libyan Arab Jamahiriya', 'LY', 'LBY'),
            array(122, 'Liechtenstein', 'LI', 'LIE'),
            array(123, 'Lithuania', 'LT', 'LTU'),
            array(124, 'Luxembourg', 'LU', 'LUX'),
            array(125, 'Macau', 'MO', 'MAC'),
            array(126, 'Macedonia, The Former Yugoslav Republic of', 'MK', 'MKD'),
            array(127, 'Madagascar', 'MG', 'MDG'),
            array(128, 'Malawi', 'MW', 'MWI'),
            array(129, 'Malaysia', 'MY', 'MYS'),
            array(130, 'Maldives', 'MV', 'MDV'),
            array(131, 'Mali', 'ML', 'MLI'),
            array(132, 'Malta', 'MT', 'MLT'),
            array(133, 'Marshall Islands', 'MH', 'MHL'),
            array(134, 'Martinique', 'MQ', 'MTQ'),
            array(135, 'Mauritania', 'MR', 'MRT'),
            array(136, 'Mauritius', 'MU', 'MUS'),
            array(137, 'Mayotte', 'YT', 'MYT'),
            array(138, 'Mexico', 'MX', 'MEX'),
            array(139, 'Micronesia, Federated States of', 'FM', 'FSM'),
            array(140, 'Moldova, Republic of', 'MD', 'MDA'),
            array(141, 'Monaco', 'MC', 'MCO'),
            array(142, 'Mongolia', 'MN', 'MNG'),
            array(143, 'Montserrat', 'MS', 'MSR'),
            array(144, 'Morocco', 'MA', 'MAR'),
            array(145, 'Mozambique', 'MZ', 'MOZ'),
            array(146, 'Myanmar', 'MM', 'MMR'),
            array(147, 'Namibia', 'NA', 'NAM'),
            array(148, 'Nauru', 'NR', 'NRU'),
            array(149, 'Nepal', 'NP', 'NPL'),
            array(150, 'Netherlands', 'NL', 'NLD'),
            array(151, 'Netherlands Antilles', 'AN', 'ANT'),
            array(152, 'New Caledonia', 'NC', 'NCL'),
            array(153, 'New Zealand', 'NZ', 'NZL'),
            array(154, 'Nicaragua', 'NI', 'NIC'),
            array(155, 'Niger', 'NE', 'NER'),
            array(156, 'Nigeria', 'NG', 'NGA'),
            array(157, 'Niue', 'NU', 'NIU'),
            array(158, 'Norfolk Island', 'NF', 'NFK'),
            array(159, 'Northern Mariana Islands', 'MP', 'MNP'),
            array(160, 'Norway', 'NO', 'NOR'),
            array(161, 'Oman', 'OM', 'OMN'),
            array(162, 'Pakistan', 'PK', 'PAK'),
            array(163, 'Palau', 'PW', 'PLW'),
            array(164, 'Panama', 'PA', 'PAN'),
            array(165, 'Papua New Guinea', 'PG', 'PNG'),
            array(166, 'Paraguay', 'PY', 'PRY'),
            array(167, 'Peru', 'PE', 'PER'),
            array(168, 'Philippines', 'PH', 'PHL'),
            array(169, 'Pitcairn', 'PN', 'PCN'),
            array(170, 'Poland', 'PL', 'POL'),
            array(171, 'Portugal', 'PT', 'PRT'),
            array(172, 'Puerto Rico', 'PR', 'PRI'),
            array(173, 'Qatar', 'QA', 'QAT'),
            array(174, 'Reunion', 'RE', 'REU'),
            array(175, 'Romania', 'RO', 'ROM'),
            array(176, 'Russian Federation', 'RU', 'RUS'),
            array(177, 'Rwanda', 'RW', 'RWA'),
            array(178, 'Saint Kitts and Nevis', 'KN', 'KNA'),
            array(179, 'Saint Lucia', 'LC', 'LCA'),
            array(180, 'Saint Vincent and the Grenadines', 'VC', 'VCT'),
            array(181, 'Samoa', 'WS', 'WSM'),
            array(182, 'San Marino', 'SM', 'SMR'),
            array(183, 'Sao Tome and Principe', 'ST', 'STP'),
            array(184, 'Saudi Arabia', 'SA', 'SAU'),
            array(185, 'Senegal', 'SN', 'SEN'),
            array(186, 'Seychelles', 'SC', 'SYC'),
            array(187, 'Sierra Leone', 'SL', 'SLE'),
            array(188, 'Singapore', 'SG', 'SGP'),
            array(189, 'Slovakia (Slovak Republic)', 'SK', 'SVK'),
            array(190, 'Slovenia', 'SI', 'SVN'),
            array(191, 'Solomon Islands', 'SB', 'SLB'),
            array(192, 'Somalia', 'SO', 'SOM'),
            array(193, 'South Africa', 'ZA', 'ZAF'),
            array(194, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS'),
            array(195, 'Spain', 'ES', 'ESP'),
            array(196, 'Sri Lanka', 'LK', 'LKA'),
            array(197, 'St. Helena', 'SH', 'SHN'),
            array(198, 'St. Pierre and Miquelon', 'PM', 'SPM'),
            array(199, 'Sudan', 'SD', 'SDN'),
            array(200, 'Suriname', 'SR', 'SUR'),
            array(201, 'Svalbard and Jan Mayen Islands', 'SJ', 'SJM'),
            array(202, 'Swaziland', 'SZ', 'SWZ'),
            array(203, 'Sweden', 'SE', 'SWE'),
            array(204, 'Schweiz', 'CH', 'CHE'),
            array(205, 'Syrian Arab Republic', 'SY', 'SYR'),
            array(206, 'Taiwan', 'TW', 'TWN'),
            array(207, 'Tajikistan', 'TJ', 'TJK'),
            array(208, 'Tanzania, United Republic of', 'TZ', 'TZA'),
            array(209, 'Thailand', 'TH', 'THA'),
            array(210, 'Togo', 'TG', 'TGO'),
            array(211, 'Tokelau', 'TK', 'TKL'),
            array(212, 'Tonga', 'TO', 'TON'),
            array(213, 'Trinidad and Tobago', 'TT', 'TTO'),
            array(214, 'Tunisia', 'TN', 'TUN'),
            array(215, 'Turkey', 'TR', 'TUR'),
            array(216, 'Turkmenistan', 'TM', 'TKM'),
            array(217, 'Turks and Caicos Islands', 'TC', 'TCA'),
            array(218, 'Tuvalu', 'TV', 'TUV'),
            array(219, 'Uganda', 'UG', 'UGA'),
            array(220, 'Ukraine', 'UA', 'UKR'),
            array(221, 'United Arab Emirates', 'AE', 'ARE'),
            array(222, 'United Kingdom', 'GB', 'GBR'),
            array(223, 'United States', 'US', 'USA'),
            array(224, 'United States Minor Outlying Islands', 'UM', 'UMI'),
            array(225, 'Uruguay', 'UY', 'URY'),
            array(226, 'Uzbekistan', 'UZ', 'UZB'),
            array(227, 'Vanuatu', 'VU', 'VUT'),
            array(228, 'Vatican City State (Holy See)', 'VA', 'VAT'),
            array(229, 'Venezuela', 'VE', 'VEN'),
            array(230, 'Viet Nam', 'VN', 'VNM'),
            array(231, 'Virgin Islands (British)', 'VG', 'VGB'),
            array(232, 'Virgin Islands (U.S.)', 'VI', 'VIR'),
            array(233, 'Wallis and Futuna Islands', 'WF', 'WLF'),
            array(234, 'Western Sahara', 'EH', 'ESH'),
            array(235, 'Yemen', 'YE', 'YEM'),
            array(236, 'Yugoslavia', 'YU', 'YUG'),
            array(237, 'Zaire', 'ZR', 'ZAR'),
            array(238, 'Zambia', 'ZM', 'ZMB'),
            array(239, 'Zimbabwe', 'ZW', 'ZWE'),
        );
        $ord = 0;
        foreach ($arrCountries as $arrCountry) {
            // German only!
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_text` (
                  `id`, `lang_id`, `module_id`, `key`, `text`
                ) VALUES (
                  NULL, 1, 0, '".self::TEXT_CORE_COUNTRY_NAME."', '".addslashes($arrCountry[1])."'
                )");
            if (!$objResult) {
                Text::errorHandler();
                return self::errorHandler();
//echo("Country::errorHandler(): Failed to insert Text for Country ".var_export($arrCountry, true)."<br />");
//                continue;
            }
            $text_id = $objDatabase->Insert_ID();
            // The active field defaults to 1
            $objResult = $objDatabase->Execute("
                INSERT INTO `".DBPREFIX."core_country` (
                  `id`, `name_text_id`, `iso_code_2`, `iso_code_3`, `ord`
                ) VALUES (
                  ".$arrCountry['0'].",
                  $text_id,
                  '".addslashes($arrCountry['2'])."',
                  '".addslashes($arrCountry['3'])."',
                  ".++$ord."
                )");
            if (!$objResult) {
die("Country::errorHandler(): Failed to insert Country ".var_export($arrCountry, true)."<br />");
//                continue;
            }
        }

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
                  FROM `".DBPREFIX."core_zip` AS `zip`
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
              FROM ".DBPREFIX."core_zip
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
              FROM ".DBPREFIX."core_zip
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


    static function errorHandler()
    {

        global $objDatabase;

die("State::errorHandler(): Disabled!<br />");

        $query = "DROP TABLE IF EXISTS `".DBPREFIX."core_zip`";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return false;

        $query = "
            CREATE TABLE `".DBPREFIX."core_zip` (
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
                  FROM `".DBPREFIX."core_zip`
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


    static function getCityByZip($zip, $country_id)
    {
        global $objDatabase;

        $query = "
            SELECT `city`
              FROM `".DBPREFIX."core_zip`
             WHERE `zip`='".addslashes($zip)."'
               AND `country_id`=$country_id";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) return self::errorHandler();
        if ($objResult->EOF) return false;
        return $objResult->fields['city'];
    }


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


    static function getMatching($location, $state)
    {
        global $objDatabase;

        $query = "
            SELECT DISTINCT `city`
              FROM `".DBPREFIX."core_zip`
             WHERE 1
             ".($location ? " AND `city` LIKE '".addslashes($location)."%'" : '')."
             ".($state ? " AND `state`='".addslashes($state)."'" : '')."
             ORDER BY `city` ASC";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) die();
        $arrLocations = array();
        while (!$objResult->EOF) {
            $arrLocations[] =
                $objResult->fields['city'];
            $objResult->MoveNext();
        }
        die(join(',', $arrLocations));
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
    const TEXT_CORE_REGION_NAME = 'core_region_name';

    /**
     * Array of all regions
     * @var     array
     * @access  private
     * @see     init()
     */
    private static $arrRegions = false;

    /**
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
            MODULE_ID, self::TEXT_CORE_REGION_NAME
        );
        $query = "
            SELECT `region`.`id`,
                   `region`.`parent_id`, `region`.`country_name_id`,
                   `region`.`ord`, `region`.`is_active`".
                   $arrSqlName['field']."
              FROM ".DBPREFIX."core_region AS `region`".
                   $arrSqlName['join']."
             ORDER BY `region`.`ord` ASC
        ";
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
            ? self::$arrRegions[$region_id]['parent_id']
            : 0
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
            ? self::$arrRegions[$region_id]['country_id']
            : false
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
    static function flush()
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
        if (!in_array(DBPREFIX."core_region", $arrTables)) {
            $query = "
              CREATE TABLE IF NOT EXISTS `".DBPREFIX."core_region` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name_text_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `parent_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `country_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `ord` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
              ) ENGINE=MYISAM";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) return false;
echo("Region::errorHandler(): Created table ".DBPREFIX."core_region<br />");
        }

        // More to come...

        // Always
        return false;
   }

}

?>
