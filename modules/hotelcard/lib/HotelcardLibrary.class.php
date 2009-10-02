<?php

/**
 * Hotelcard Library
 *
 * Common front- and backend functionality
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */

/** @ignore */
//require_once 'Hotel.class.php';

/**
 * Hotelcard Library
 *
 * Common front- and backend functionality
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @version     2.2.0
 * @package     contrexx
 * @subpackage  module_hotelcard
 */
class HotelcardLibrary
{
    /**
     * Format with HTML code for mandatory field marks
     */
    const MANDATORY_FIELD_HTML = '<span style="color: red;">&nbsp;*</span>';


    /**
     * Set up the view of any single hotel
     * @param   HTML_Template_Sigma   $objTemplate    The Template object,
     *                                                by reference
     * @param   integer   $hotel_id       The Hotel ID
     * @return  boolean                   True on success, false otherwise
     */
    static function hotel_view(&$objTemplate, $hotel_id)
    {
        global $_ARRAYLANG;

        $objHotel = Hotel::getById($hotel_id);
        if (empty($objHotel)) {
            Hotelcard::addMessage(sprintf(
                $_ARRAYLANG['TXT_HOTELCARD_HOTEL_ID_NOT_FOUND'], $hotel_id
            ));
            return Hotelcard::hotel_overview();
        }
        $arrFields = array();
        $arrFieldnames = Hotel::getFieldnames();
        foreach ($arrFieldnames as $name) {
            $value = $objHotel->getFieldvalue($name);

            if (preg_match('/(?:^id$|^recommended$'.
                '|^reservation_|^accountant_|^billing_'.
                '|^checkin_|^checkout_'.
                // currently unused
                '|^policy_text_id$'.
                ')/', $name)) {
                continue; // $value = Country::getNameById($value);
            }
            // Fix values that are IDs, special, or arrays of anything
            if (preg_match('/(?:group_id|description_text_id)$/', $name)) {
                $value = Text::getById($value, FRONTEND_LANG_ID)->getText();
            } elseif (preg_match('/region$/', $name)) {
                $value = State::getFullname($value);
            } elseif (preg_match('/location$/', $name)) {
                // 204 is the country ID of switzerland
                $value = $value.' '.Location::getCityByZip($value, 204);
            } elseif (preg_match('/^accomodation_type_id$/', $name)) {
                $value = HotelAccomodationType::getNameById($value);
            } elseif (preg_match('/^lang_id$/', $name)) {
                $value = FWLanguage::getLanguageParameter($value, 'name');
            } elseif (preg_match('/contact_gender$/', $name)) {
                $value = (preg_match('/[wf]/i', $value)
                  ? $_ARRAYLANG['TXT_HOTELCARD_GENDER_FEMALE']
                  : $_ARRAYLANG['TXT_HOTELCARD_GENDER_MALE']);
            } elseif (preg_match('/^rating$/', $name)) {
                $value = HotelRating::getString($value);
            } elseif (preg_match('/^image_id$/', $name)) {
                $ord = (isset($_SESSION['hotelcard']['image_ord'])
                    ? $_SESSION['hotelcard']['image_ord'] : 0);
//echo("Image ID $value, ord $ord<br />");
                $objImage = Image::getById($value, $ord);
                if (!$objImage) {
//echo("Image is invalid<br />");
                    continue;
                }
                if (defined('BACKEND')) {
                    $objImage->setPath(ASCMS_PATH_OFFSET.'/'.$objImage->getPath());
                }
                $value = Html::getImage($objImage);
//echo("Image HTML: ".htmlentities($value)."<br />");
                //preg_replace('/^[0-9a-f]+_/', '', $objImage->getPath());
            } elseif (preg_match('/^registration_time$/', $name)) {
                $value = date(ASCMS_DATE_FILE_FORMAT, $value);
            } elseif (preg_match('/^status$/', $name)) {
                $value =
                    Html::getLed(
                      $value == Hotel::STATUS_VERIFIED
                        ? 'green'
                        :
                      (   $value == Hotel::STATUS_ACCOUNT
                       || $value == Hotel::STATUS_CONFIRMED
                        ? 'yellow'
                        : 'red'
                      ),
                    $_ARRAYLANG['TXT_HOTELCARD_HOTEL_STATUS_'.$value]);

            } elseif (preg_match('/(?:|^numof_rooms$'.
                '|^hotel_name$|^hotel_address$|^hotel_uri$'.
                '|^contact_|^found_how$|^comment$|^country_id$)/', $name)) {
                // Do nothing, use as-is
                //$value = $value;
            } else {
//echo("Unhandled field name $name, value ".var_export($value, true)."<br />");
            }
            $name = 'TXT_HOTELCARD_'.strtoupper($name);
            if (empty($value)) {
//echo("Note: Empty value for $name<br />");
                $value = $_ARRAYLANG['TXT_HOTELCARD_NO_VALUE'];
            }
            if (empty($_ARRAYLANG[$name])) {
//echo("Note: No language entry for $name (value '$value')<br />");
                continue;
            }
//echo("Language variable $name => ".$_ARRAYLANG[$name]."<br />");
            $arrFields[$name] = array(
                'label' => $_ARRAYLANG[$name],
                'input' => $value,
            );
        }

        $arrHotelFacilities = HotelFacility::getFacilityNameArray();
        $arrHotelFacilitiesRelation = HotelFacility::getRelationArray($hotel_id); // self::$arrRelations[$hotel_id][$facility_id] = $facility_id;
        $strHotelFacilities = '';
        foreach ($arrHotelFacilitiesRelation[$hotel_id] as $facility_id) {
            $strHotelFacilities .=
                (empty($strHotelFacilities) ? '' : ', ').
                $arrHotelFacilities[$facility_id];
        }
        $arrFields['hotel_facility_id'] = array(
            'label' => $_ARRAYLANG['TXT_HOTELCARD_HOTEL_FACILITY_ID'],
            'input' => $strHotelFacilities,
        );

        $arrRoomtypes = HotelRoom::getTypeArray($hotel_id, 0, 0, 0);
//echo("room types: ".var_export($arrRoomtypes, true)."<br />");
        $strRoomtypes = '';
        $i = 0;
        foreach ($arrRoomtypes as $arrRoomtype) {
//echo("room type index $i + 1<br />");
            $strRoomFacilities = '';
            foreach ($arrRoomtype['facilities'] as $facility_id => $strFacilityName) {
//echo("room facility $strFacilityName<br />");
                $strRoomFacilities .=
                    (empty($strRoomFacilities) ? '' : ', ').
                    $strFacilityName;
            }
            $strRoomtypes .=
                ($strRoomtypes ? '<br />' : '').
                '<b>'.
                sprintf($_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE_NUMBER_COLON'], ++$i).
                '</b><br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPE'].': '.
                $arrRoomtype['name'].'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_AVAILABLE'].': '.
                $arrRoomtype['number_default'].'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_PRICE'].': '.
                $arrRoomtype['price_default'].'<br />'.
                ($arrRoomtype['breakfast_included']
                  ? $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_INCLUDED']
                  : $_ARRAYLANG['TXT_HOTELCARD_BREAKFAST_NOT_INCLUDED']).'<br />'.
                $_ARRAYLANG['TXT_HOTELCARD_ROOM_FACILITY_ID'].': '.
                $strRoomFacilities.'<br />';
        }
        $arrFields['room_types'] = array(
            'label' => $_ARRAYLANG['TXT_HOTELCARD_ROOMTYPES'],
            'input' => $strRoomtypes,
        );
// Not implemented
//            } elseif (preg_match('/^creditcard_id$/', $name)) {
//                $value = join(', ', $value);
        return self::parseDataTable($objTemplate, $arrFields);
    }


    /**
     * Parse and display the form data as provided
     *
     * The $arrFields argument must be an array of rows, with single rows
     * containing fields like this:
     *  array(
     *    'label' => 'Label text',
     *    'input' => 'Input form element or text',
     *    'mandatory' => boolean,
     *    'class' => 'optional_class_name',
     *    'error' => 'Optional error message to be added on top of the input',
     *    'special' => Custom line, shown as-is.  If this index is present,
     *                  any of the other are ignored.
     *  )
     * All fields are optional.
     * If both label and input are missing, the array is recursively scanned
     * for valid subrows with at least a label or an input.
     * If only the input is empty, the label is displayes as a header (<h2>).
     * If the mandatory flag evaluates to true, the label is marked with a
     * trailing non-breakable space, and an asterisk.
     * If the class value is not empty, it is inserted into the otherwise
     * empty class attribute of the label tag.
     * If the error value is not empty, it is added on top of the input
     * element or text.
     * @param   HTML_Template_Sigma   $objTemplate    The Template object,
     *                                                by reference
     * @param   array   $arrFields    The array of rows to be displayed
     * @return  boolean               True on success, false otherwise
     */
    static function parseDataTable(&$objTemplate, $arrFields)
    {
        global $_ARRAYLANG;
//echo("Hotelcard::parseDataTable(): Template:<br />".nl2br(htmlentities(var_export($objTemplate, true), ENT_QUOTES, CONTREXX_CHARSET))."<hr />");

        $i = 0;
        foreach ($arrFields as $row_data) {
//echo("Hotelcard::parseDataTable(): row_data is ".(htmlentities(var_export($row_data, true), ENT_QUOTES, CONTREXX_CHARSET))."<br />");

            if (!is_array($row_data)) return false;

            // Some rows contain the 'special' index, which contains
            // HTML content that is inserted as-is
            if (isset($row_data['special'])) {
                $objTemplate->setVariable(
                    'HOTELCARD_DATA_SPECIAL', $row_data['special']);
                $objTemplate->parse('hotelcard_data');
                continue;
            }

            // Some other "rows" contain arrays of rows (i.e.,
            // hotel facilities).  Recurse into them
            if (   empty($row_data['label'])
                && empty($row_data['input'])) {
//echo("Hotelcard::parseDataTable(): &gt;&gt;&gt;<br />");
                self::parseDataTable($objTemplate, $row_data);
//echo("Hotelcard::parseDataTable(): &lt;&lt;&lt;<br />");
                continue;
            }

//echo("Language variable $label => ".$_ARRAYLANG[$label]."<br />");

            $mandatory = (empty($row_data['mandatory']) ? false : true);
            $label = (isset($row_data['label']) ? $row_data['label'] : '');
            $input = (isset($row_data['input']) ? $row_data['input'] : '');
            $class = (isset($row_data['class']) ? $row_data['class'] : '');
            $error = (isset($row_data['error']) ? $row_data['error'].'<br />' : '');

//echo("Hotelcard::parseDataTable(): class: $class<br />");
            ++$i;
            if (empty($input)) {
//echo("Header: $label<hr />");
                // Parse header
                $objTemplate->setVariable(array(
                    'HOTELCARD_ROWCLASS' => $i % 2 + 1,
                    'HOTELCARD_DATA_HEADER' =>
                        (preg_match('/^TXT_/', $label)
                            ? $_ARRAYLANG[$label] : $label)
                ));
            } else {
//echo("Label: $label, input: $input<hr />");
                $objTemplate->setVariable(array(
                    'HOTELCARD_ROWCLASS' => $i % 2 + 1,
                    'HOTELCARD_DATA_LABEL' =>
                        (preg_match('/^TXT_/', $label)
                            ? (empty($_ARRAYLANG[$label.'_TOOLTIP'])
                                ? $_ARRAYLANG[$label]
                                : '<span id="tooltip-'.$i.'"'.
                                  ' onmouseover=\'Tip("'.
                                  $_ARRAYLANG[$label.'_TOOLTIP'].'",'.
//                                  'FADEIN,50,'.
//                                  'FADEOUT,1000,'.
                                  'LEFT,true,'.
                                  'WIDTH,200,'.
                                  'FIX,["tooltip-'.$i.'",-230,-21],'.
                                  'STICKY,true,'.
                                  'BGCOLOR,"#f0f8ff",'.
                                  'BORDERCOLOR,"#d0e0f0",'.
                                  'FONTCOLOR,"#000000");\''.
                                  ' onmouseout="UnTip();">'.
                                  Html::getImageByPath(
                                      'images/modules/hotelcard/question_mark.png',
                                      'alt=""').
                                  '&nbsp;'.
                                  $_ARRAYLANG[$label].'</span>')
                            : $label).
                        ($mandatory
                            ? self::MANDATORY_FIELD_HTML : ''),
                    'HOTELCARD_DATA_LABEL_CLASS' => $class,
                    'HOTELCARD_DATA_INPUT' => $error.$input,
                ));
            }
            $objTemplate->parse('hotelcard_data');
        }
//echo("Hotelcard::parseDataTable(): Template:<br />".nl2br(htmlentities(var_export($objTemplate, true), ENT_QUOTES, CONTREXX_CHARSET))."<hr />");
      return true;
    }

}

?>
