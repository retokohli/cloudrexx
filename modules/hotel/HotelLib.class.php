<?php

/**
* Hotel management module
*
* backend class for the hotel module
*
* @copyright    CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author        Astalavista Development Team <thun@astalvista.ch>
* @module        hotel
* @modulegroup    modules
* @access        public
* @version        1.0.0
*/

class HotelLib{

    public $_objTpl;

    /**
     * weekdays abbreviations (index = langid)
     *
     * @var array
     */
    public $_weekdaysShort = array(    1 => array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'),
                                2 => array('Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'),
                                3 => array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa')
                            );

    public $fieldNames = array();

    /**
     * array of available languages
     * key         -> id
     * value     -> _ARRAYLANG language variablename
     * @var array
     */
    public $languages = array();

    /**
     * framework file object
     *
     * @var object
     */
    public $_objFile;

    /**
     * number of languages
     *
     * @var int
     */
    public $langCount = 0;

    /**
     * holds the field ID of the last field found by HotelLib::_getFieldFromText()
     *
     * @var int
     */
    public $_currFieldID;

    /**
     * relative path to standard image in image-fields
     *
     * @var str
     */
    public $noImage = 'images/icons/.gif';

    /**
     * Array with the settings values
     */
    public $arrSettings;


    /**
     * standard line break (windows)
     *
     * @var string (escaped)
     */
    public $_lineBreak = "\r\n";

    /**
     * Array holding number of field per type
     * $_fieldCount[$type]['count']
     *
     * @var array
     */
    public $_fieldCount;



    /**
     * temporary array holding the hotel IDs fetched by buildquery() callees, used for building the hotellist query
     *
     * @var array
     */
    public $_hotelIDs = array();


    /**
    * PHP5 constructor
    */
    function __construct()
    {
        define('DS', DIRECTORY_SEPARATOR);
        $this->_getLanguages();
        $this->_getSettings();
    }

    /**
     * Get Field Names
     *
     * Generates an array with field names
     *
     * @param $hotelID ID of the object (only fetches rows of that object, all if omitted)
     * @param $count whether fieldtypes shall be count, if bigger than 0 (also specifies the frontend language)
     * @param $frontend if this value is true, then the contents of one language {@see $this->frontLang} will be fetched only
     */
    function _getFieldNames($hotelID = 0, $count = 0, $frontend = false){
        global $objDatabase;
        $objRS = $objDatabase->Execute("    SELECT id, field_id, lang_id, name
                                            FROM ".DBPREFIX."module_hotel_fieldname");
        $allNames = array();
        if ($objRS != false) {
            while (!$objRS->EOF) {
                $allNames[] = array(
                    "id"        => $objRS->fields['id'],
                    "field_id"    => $objRS->fields['field_id'],
                    "lang_id"    => $objRS->fields['lang_id'],
                    "name"        => $objRS->fields['name']
                );
                $objRS->MoveNext();
            }
        }
        unset($objRS);
        $objRS = $objDatabase->Execute("SELECT id, type, `order`, `mandatory`
                                        FROM ".DBPREFIX."module_hotel_field
                                        ORDER BY `order`");
        if($objRS !== false){
            while(!$objRS->EOF){
                $names = array();
                foreach($allNames as $name) {
                    if ($name['field_id'] == $objRS->fields['id']) {
                        $names[$name['lang_id']] = $name['name'];
                    }
                }

                if(!$frontend){
                    foreach (array_keys($this->languages) as $langID) {
                        $query = "    SELECT  id, hotel_id, lang_id, field_id, fieldvalue, active
                                    FROM ".DBPREFIX."module_hotel_content
                                    WHERE field_id = ".$objRS->fields['id']."
                                    AND lang_id = $langID";
                        $query .= ($hotelID > 0) ? " AND hotel_id = $hotelID LIMIT 1" : ' LIMIT 1';
                        $objRSContent = $objDatabase->Execute($query);
                        if($objRSContent !== false){
                            $content[$langID] = $objRSContent->fields['fieldvalue'];
                        }
                    }
                }else{
                    $langID = $this->frontLang;
                    $query = "    SELECT  id, hotel_id, lang_id, field_id, fieldvalue, active
                                FROM ".DBPREFIX."module_hotel_content
                                WHERE field_id = ".$objRS->fields['id']."
                                AND lang_id = ".$langID;
                    $query .= ($hotelID > 0) ? " AND hotel_id = $hotelID " : '';
                    $objRSContent = $objDatabase->SelectLimit($query, 1);
                    if($objRSContent !== false){
                        $content[$langID] = $objRSContent->fields['fieldvalue'];
                    }
                }

                $content['active'] = $objRSContent->fields['active'];

                $img = ($hotelID > 0) ? $this->_getImageInfo($objRS->fields['id'], $hotelID) : array('uri' => '') ;
                if($count > 0 && $content['active'] == 1 && trim($names[$count]) != '' && !in_array($names[$count] ,$this->_usedFields)){
                    switch($objRS->fields['type']){
                           case 'text':
                        case 'textarea':
                        case 'digits_only':
                        case 'price':
                            $this->_fieldCount['text']++;
                        break;

                        case 'img':
                            $this->_fieldCount['img']++;
                        break;

                        default:
                        break;
                    }
                }
                $this->fieldNames[$objRS->fields['id']] = array(
                    "type"        => $objRS->fields['type'],
                    "order"        => $objRS->fields['order'],
                    "names"        => $names,
                    'content'    => $content,
                    'img'        => $img['uri'],
                    'mandatory' => $objRS->fields['mandatory']
                );
                $objRS->MoveNext();
            }
        }
    }

    function _getImageInfo($fieldID, $hotelID)
    {
        global $objDatabase;

        $query = "    SELECT id, field_id, uri
                    FROM ".DBPREFIX."module_hotel_image
                    WHERE field_id = $fieldID
                    AND hotel_id = $hotelID
                    LIMIT 1";
        $objRS = $objDatabase->Execute($query);
        if($objRS !== false){
            return $objRS->fields;
        }
        return false;
    }


    /**
     * build array with languages
     * key         -> id
     * value    -> _ARRAYLANG language variablename
     *
     * @return unknown
     */

    function _getLanguages()
    {
        global $objDatabase;
        $query = "    SELECT id, language
                        FROM ".DBPREFIX."module_hotel_languages";
        $objRS = $objDatabase->Execute($query);
        if($objRS !== false){
            while(!$objRS->EOF){
                $this->languages[$objRS->fields['id']] = $objRS->fields['language'];
                ++$this->langCount;
                $objRS->MoveNext();
            }
            return true;
        }
        return false;
    }

    /**
     * return field attributes by text
     *
     * @param string $str name of the field to fetch
     * @param string $type type of return: content, names, img, active, key
     * @return false on failure, requested valued on success
     */

    function _getFieldFromText($str, $type = 'content'){
        array_walk($this->fieldNames, array($this, '_searchField'), $str);
        if($type == 'content'){
            return $this->fieldNames[$this->_currFieldID]['content'][$this->frontLang];
        }else if ($type == 'names'){
            return $this->fieldNames[$this->_currFieldID]['names'][$this->frontLang];
        }else if ($type == 'img'){
            return $this->fieldNames[$this->_currFieldID]['img'];
        }else if ($type == 'active'){
            return $this->fieldNames[$this->_currFieldID]['content']['active'];
        }else if ($type == 'key'){
            return $this->_currFieldID;
        }
        return false;
    }

    /**
     * search field and set key on match
     *
     * @param array $field
     * @param int $key
     * @param string $fieldName
     */
    function _searchField($field, $key, $fieldName){
        if(trim(strtolower($field['names'][1])) == trim(strtolower($fieldName))){
            $this->_currFieldID = $key;
        }
    }


    /**
     * fetch the settings from the module_hotel_settings table
     *
     */
    function _getSettings() {
        global $objDatabase;
        $this->arrSettings = array();
        $query = "  SELECT `setname`, `setvalue`
                    FROM ".DBPREFIX."module_hotel_settings
                    WHERE `status` = '1'";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrSettings[$objResult->fields['setname']] = $objResult->fields['setvalue'];
                $objResult->MoveNext();
            }
        }
    }

    /**
     * check if $_CONFIG['domainUrl'] has a subdomain
     * return domainUrl plus subdomain, if domainUrl has no subdomain
     * otherwise return domainUrl minus subdomain
     *
     * @param  string domain
     * @return string domain2
     */
    function _getDomain($domain)
    {
        $dparts = explode(".", $domain);
        switch (count($dparts)) {
            case 2:
                return 'www.'.$domain;
                break;
            case 3:
                if($dparts[0] == 'www'){
                    return substr($domain, 4);
                }
                break;
        }
        return false;
    }


    /**
     * helper callback
     * @param string     $item
     */
    function arrStrToLower(&$item)
    {
        $item = strtolower($item);
    }

    /**
     * helper callback
     *
     * @param string $var
     * @return bool $found
     */
    function filterHotelType($var) {
        if (substr($var, 0, 20) == "TXT_HOTEL_OBJECTTYPE_") {
            return true;
        }
        return false;
    }

    /**
     * return the hotel_id field
     *
     */
    function getHotel_id($ID){
        global $objDatabase;
        $query = "  SELECT `field_id`
                    FROM `".DBPREFIX."module_hotel_fieldname`
                    WHERE `name` = 'hotel_id'";

        $objResult = $objDatabase->Execute($query);

        $query = "    SELECT `fieldvalue`
                    FROM `".DBPREFIX."module_hotel_content`
                    WHERE `hotel_id` = ".$ID."
                    AND `field_id` = ".$objResult->fields['field_id'];

        $objResult = $objDatabase->Execute($query);
        return $objResult->fields['fieldvalue'];

    }

    function getHotel_tableID($ID)
    {
        global $objDatabase;

        $query = "  SELECT `field_id`
                    FROM `".DBPREFIX."module_hotel_fieldname`
                    WHERE `name` = 'hotel_id'";

        $objResult = $objDatabase->Execute($query);

        $query = "    SELECT DISTINCT `hotel_id`
                    FROM `".DBPREFIX."module_hotel_content`
                    WHERE `fieldvalue` = ".$ID."
                    AND `field_id` = ".$objResult->fields['field_id'];
        $objResult = $objDatabase->Execute($query);
        while(!$objResult->EOF){
            $this->_hotelIDs[] = $objResult->fields['hotel_id'];
            $objResult->MoveNext();
        }
        $this->_hotelIDs = array_unique($this->_hotelIDs);
    }



    /**
     * wrap a string $str in a TD
     *
     * @param string $str
     * @return string
     */
    function makeTD($str){
        return '<td>'.$str.'</td>';
    }
}

?>
