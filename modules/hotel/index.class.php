<?php

/**
* Hotel management
*
* @copyright    CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author       Astalavista Development Team <thun@astalvista.ch>
* @module       hotel
* @modulegroup  modules
* @access       public
* @version      1.0.0
*/

require_once ASCMS_MODULE_PATH."/hotel/HotelLib.class.php";

class HotelManager extends HotelLib
{

    /**
     * Factor for prices in euro (factor for the default currency defined under settings in the backend)
     *
     * @var integer currency exchange rate
     */
    public $_currencyEuroFactor = 1.65;

    /**
    * Template object
    *
    * @access private
    * @var object
    */
    public $_objTpl;

    /**
     * Fields used in basic data (i.e. field which should not be displayed in any text-, img-, or link-rows)
     *
     * @var array
     */
    public $_usedFields =      array(  'Kopfzeile',    'Adresse',      'Ort',          'Preis',            'Beschreibung',         'Headline',
                                    'Aufz�hlung1',  'Aufz�hlung2',  'Aufz�hlung3',  'Link auf Homepage','Anzahl Zimmer',        'Destination',
                                    'Besonderes',   'Lage',         'Aktivit�ten',  'hotel_id',         'CityCode',             'Kategorie',
                                    'Hotel',        'Kulinarisches','Sport',        'Schnorcheln',      'Kinderfreundl.',       'Wellness',
                                    'Tauchen',      'Familien',     'Badeferien',   'Highlight');

    /**
     * Fields used in categories (with stars)
     *
     * @var array
     */
    public $_categoryFields =  array(  'Essen', 'Kategorie', 'Kinderfreundl.', 'Tauchen', 'Sport', 'Kulinarisches', 'Badeferien', 'Strand',
                                    'Hausriff', 'Schnorcheln', 'Wellness', 'Familien', 'Tauchkreuzfahrten', 'Hochzeitsreisen', 'Rundreisen', 'Nilkreuzfahrten', 'Golf' );


    /**
     * Fields used for interest navigation section
     *
     * @var array
     */
    public $_interestFields = array(   'badeferien', 'wellness', 'tauchkreuzfahrten', 'familien', 'hochzeitsreisen', 'rundreisen', 'nilkreuzfahrten', 'tauchen', 'golf', );


    /**
     * Fields used in the general tab
     *
     * @var array
     */
    public $_generalFields = array('allgemein', 'highlight');


    /**
     * Fields used in the activity tab
     *
     * @var array
     */
    public $_activityFields = array('aktivit�ten', 'kinder');


    /**
     * Fields used in the location tab
     *
     * @var array
     */
    public $_locationFields = array('lage');


    /**
     * Fields used in the special tab
     *
     * @var array
     */
    public $_specialFields = array('besonderes');




    /**
     * variable holding the frontend langId for the hotel module
     *
     * @var integer frontend language ID
     */
    public $frontLang;
    /**
     * Currency Suffix for currency defined under settings in the backend
     *
     * @var string currency suffix
     */
    public $_currencySuffix = '.-';

    /**
     * Currency prefix for prices in �
     *
     * @var string currency prefix (euro)
     */
    public $_currencyEuroPrefix = "&euro;";

    /**
     * Currency suffix for prices in �
     *
     * @var string currency suffix (euro)
     */
    public $_currencyEuroSuffix = ".-";

    /**
     * count of the listing for dedicated listing fields
     *
     * @var integer count of listing entries
     */
    public $_listingCount = 3;

    /**
     * array holding the priceFields
     *
     * @var array price fields
     */
    public $_priceFields = array();

    /**
     * Coordinates for the frontend google map startpoints
     *
     * @var array google lat-lon cooridantes for the map startpoints
     */
    public $_googleMapCoordinates = array(
        'egypt' => array(
            'lat'   => 26.352497858154024,
            'lon'   => 31.376953125,
            'zoom'  => 5
        ),
        'jordan' => array(
            'lat'   => 26.352497858154024,
            'lon'   => 31.376953125,
            'zoom'  => 5
        ),
        'dubai' => array(
            'lat'   => 26.352497858154024,
            'lon'   => 31.376953125,
            'zoom'  => 5
        ),
        'turkey' => array(
            'lat'   => 26.352497858154024,
            'lon'   => 31.376953125,
            'zoom'  => 5
        ),
    );

    /**
     * array holding the weekday language strings
     *
     * @var array
     */
    public $_weekdays  = array();


    /**
     * Margin for the category stars
     *
     * @var integer pixel
     */
    public $_categoryMargin        = 68;

    /**
     * Width of one category star
     *
     * @var integer pixel
     */
    public $_categoryStarsWidth    = 13;

    /**
     * number of categories in the overview per hotel
     *
     * @var integer number of categories
     */
    public $_categoryCountOverview  = 6;

    /**
     * Boolcheck for setting the correct pagetitle at cmd=hotellist if no object were found
     *
     * @var bool
     */
    public $_emptyHotelList = false;

    /**
    * PHP5 constructor
    * @global object $objDatabase
    * @global array $_CORELANG
    * @global integer $_LANGID
    */
    function __construct($pageContent)
    {
        global $objDatabase, $_CORELANG, $_LANGID;

        $this->frontLang = $_LANGID;

        $objRS=$objDatabase->Execute("  SELECT count(1) as cnt FROM ".DBPREFIX."module_hotel_fieldname WHERE
                                        lang_id = 1 AND lower(name) LIKE '%hlung%'"); // aufzählung
        $this->_listingCount = $objRS->fields['cnt'];
        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($pageContent);
        $this->_weekdays = explode(',', $_CORELANG['TXT_DAY_ARRAY']);
        parent::__construct();
        $this->_usedFields = array_merge(
                                            $this->_usedFields,
                                            $this->_categoryFields,
                                            $this->_interestFields,
                                            $this->_generalFields,
                                            $this->_activityFields,
                                            $this->_locationFields,
                                            $this->_specialFields
        );

        array_walk($this->_usedFields, array(&$this, '_strtolower'));
        $this->_usedFields = array_unique($this->_usedFields);
    }

    /**
    * Get content page
    *
    * @access public
    */
    function getPage()
    {
        if (!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }

        if (isset($_GET['standalone']) && !empty($_GET['standalone'])) {
            $this->_showMap();
        } elseif (isset($_GET['img'])) {
            $this->_showImageViewer();
        } else {
            $cmdParts = explode('_', $_GET['cmd']);

            switch ($cmdParts[0]) {
                case 'map':
                    $this->_doNothing();
                    break;
                case 'traveldata':
                    $this->_showTravelData();
                    break;
                case 'showmapoverview':
                    $this->_showMap();
                    break;
                case 'showObj':
                    $this->_showObject();
                    break;
                case 'interest':
                    $this->_showInterestForm();
                    break;
                case 'submitContact':
                    $this->_addContact();
                    break;
                case 'getPDF':
                    $this->_getPDF();
                    break;
                case 'hotellist':
                    $this->_showHotelList();
                    break;
                 case 'hotellistdynamic':
                    $this->_showHotelList();
                    break;
                default:
                    $this->_showOverview();
                    break;
            }
        }
        return $this->_objTpl->get();
    }

    function _strtolower(&$s) {
        $s = strtolower($s);
    }

    /**
     * return hotelname for pagetitle
     *
     * @param string $pagetitle original pagetitle
     * @return string new pagetitle
    function getPageTitle($pagetitle)
    {
        return $this->_getFieldFromText('destination').' - '.$this->_getFieldFromText('ort').' - '.$this->_getFieldFromText('hotel');
    }
     */

    /**
     * return traveldata pagetitle
     *
     * @param string $pagetitle original pagetitle
     * @return string new pagetitle
    function getPageTitleTravel($pagetitle) {
        $this->_getFieldNames(intval($_GET['id']));
        return $pagetitle.' '.$this->_getFieldFromText('hotel').', '.$this->_getFieldFromText('ort');
    }
     */

    /**
     * return interest pagetitle
     *
     * @param string $pagetitle original pagetitle
     * @return string new pagetitle
    function getPageTitleInterest($interest, $pagetitle) {
        global $_ARRAYLANG;
        if ($this->_emptyHotelList) {
            return $_ARRAYLANG['TXT_HOTEL_NO_RESULTS_FOUND'];
        }
        return $pagetitle.': '.$this->_getFieldFromText($interest, 'names');
    }
     */


    function _showImageViewer()
    {
        $this->_objTpl->loadTemplateFile("modules/hotel/template/frontend_images_viewer.html");
        $hotelID = intval($_GET['id']);
        $images = $this->_getImagesFromObject($hotelID);
        $index = intval($_GET['index']);
        foreach ($images as $index => $image) {
            $this->_objTpl->setVariable(array(
               'HOTEL_IMAGE_INDEX'      => $index,
               'HOTEL_IMAGE_SRC'        => $image['imgsrc'],
               'HOTEL_IMAGE_WIDTH'      => $image['width'],
               'HOTEL_IMAGE_HEIGHT'    => $image['height'],
               'HOTEL_IMAGE_NAME'      => $image['name'],
               'HOTEL_IMAGE_CONTENT'       => $image['content'],
               'HOTEL_CURRENT_INDEX'    => $index,
               'HOTEL_IMAGE_FIELD_ID'   => !empty($image['field_id']) ? $image['field_id'] : $index,
            ));
            $this->_objTpl->parse('imagesArray');
            $this->_objTpl->parse('indexArray');
        }
        $this->_objTpl->show();
        die();
    }


    /**
     * shows the interest form and saves the submit data
     * @return void
     */
    function _showInterestForm()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        require_once(ASCMS_LIBRARY_PATH.DIRECTORY_SEPARATOR.'phpmailer'.DIRECTORY_SEPARATOR."class.phpmailer.php");
        $hotelid = intval($_REQUEST['id']);
        $this->_getFieldNames($hotelid, $this->frontLang);
        $hotelID = $this->_getFieldFromText('hotel_id');

        if (!empty($hotelid)) {
            $this->_objTpl->setVariable(array(
                'HOTEL_ID' => $hotelid,
                'HOTEL_HOTEL_NAME' => $this->_getFieldFromText('hotel'),
            ));
        }

        $arrDepartureDates = $this->_getTravelDepartureDates($hotelID);
        foreach($arrDepartureDates as $departureDate) {
            $departure =  $this->_weekdays[date('w', $departureDate['from'])];
            $departure .= ', '.date('d.m.Y', $departureDate['from']);
            $this->_objTpl->setVariable(array(
                'HOTEL_TRAVEL_ID'               => $departureDate['id'],
                'HOTEL_TRAVEL_DEPARTURE_DATE'   => $departure,
            ));
            $this->_objTpl->parse('travelDates');
        }

        $this->_showTravelData($hotelID);

        if (!empty($_REQUEST['submitContactForm'])) {

            $hotelid            = intval($_REQUEST['contactFormField_hotel_id']);//hidden field: hotelid
// Unused
//            $travelID           = intval($_REQUEST['contactFormField_travel_id']);
            $name               = !empty($_REQUEST['contactFormField_13']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_13'])) : '';
            $firstname          = !empty($_REQUEST['contactFormField_14']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_14'])) : '';
            $street             = !empty($_REQUEST['contactFormField_15']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_15'])) : '';
            $zip                = !empty($_REQUEST['contactFormField_16']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_16'])) : '';
            $location           = !empty($_REQUEST['contactFormField_17']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_17'])) : '';
            $email              = !empty($_REQUEST['contactFormField_18']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_18'])) : '';
            $phone_home         = !empty($_REQUEST['contactFormField_19']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_19'])) : '';
            $phone_office       = !empty($_REQUEST['contactFormField_20']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_20'])) : '';
            $phone_mobile       = !empty($_REQUEST['contactFormField_21']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_21'])) : '';

            $num_adults         = !empty($_REQUEST['contactFormField_23']) ? intval($_REQUEST['contactFormField_23']) : '1';
            $num_children       = !empty($_REQUEST['contactFormField_24']) ? intval($_REQUEST['contactFormField_24']) : '0';
            $num_weeks          = !empty($_REQUEST['contactFormField_22']) ? intval($_REQUEST['contactFormField_22']) : '1';
            $favDepartureDateID = !empty($_REQUEST['favDepartureDate'])    ? intval($_REQUEST['favDepartureDate'])    : '';
            $flightOnly         = !empty($_REQUEST['contactFormField_28']) ? intval($_REQUEST['contactFormField_28']) : '0';
            $comment            = !empty($_REQUEST['contactFormField_27']) ? contrexx_addslashes(contrexx_strip_tags($_REQUEST['contactFormField_27'])) : '';


            //get departure and arrival dates
            $query = "  SELECT `isis_code`, `from`, `to` FROM `".DBPREFIX."module_hotel_travel`
                        WHERE `id` = ".$favDepartureDateID;
            $objRS = $objDatabase->SelectLimit($query, 1);
            $from = $objRS->fields['from'];
            $to = $objRS->fields['to'];
            $isisCode = $objRS->fields['isis_code'];


            $query = "INSERT INTO ".DBPREFIX."module_hotel_interest VALUES (    NULL, $hotelid, '$isisCode', '$name', '$firstname',
                                                                            '$street', '$zip', '$location', '$email',
                                                                            '$phone_home', '$phone_office', '$phone_mobile',
                                                                            '$num_weeks', '$num_adults', '$num_children',
                                                                            '$from', '$to', $flightOnly ,'$favDepartureDateID',
                                                                            '$comment', ".mktime().")";
            if (!$objDatabase->Execute($query)) {
                $this->_objTpl->setVariable('CONTACT_FEEDBACK_TEXT', $_ARRAYLANG['TXT_HOTEL_DATABASE_ERROR']);
                return false;
            }

            $query = "   SELECT reference, ref_nr_note
                         FROM ".DBPREFIX."module_hotel
                         WHERE id = ".$hotelid;
//            if (($objRS = $objDatabase->SelectLimit($query, 1)) !== false) {
// Unused
//                $reference   = $objRS->fields['reference'];
//                $ref_note    = $objRS->fields['ref_nr_note'];
//            }

            $favDepartureDate = date('d.m.Y', $from);
// Unused
//            $favArrivalDate   = date('d.m.Y', $to);



            //set hotel ID for _getFieldFromText function
            $this->_getFieldNames($hotelid);
            $this->_currFieldID = $hotelid;

// Unused
//          $address        = $this->_getFieldFromText('adresse');
            $hotellocation  = $this->_getFieldFromText('ort');
            $hotel          = $this->_getFieldFromText('hotel');



            $mailer = new PHPMailer();
            $objRS = $objDatabase->SelectLimit('SELECT setvalue FROM '.DBPREFIX.'module_hotel_settings
                                                WHERE setname="contact_receiver"');

            //set recipients
            $emails = explode(',', $objRS->fields['setvalue']);
            foreach ($emails as $email) {
                $mailer->AddAddress($email);
            }

            $emailBody  = $_ARRAYLANG['TXT_HOTEL_E_MAIL'].":".str_repeat("\t", 4).contrexx_addslashes($_REQUEST['contactFormField_18'])."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_NAME'].":".str_repeat("\t", 5).$name."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'].":".str_repeat("\t", 4).$firstname."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_STREET'].":".str_repeat("\t", 4).$street."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_LOCATION'].":".str_repeat("\t", 5).$location."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_TELEPHONE'].":".str_repeat("\t", 4).$phone_home."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'].":".str_repeat("\t", 2).$phone_office."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'].":".str_repeat("\t", 3).$phone_mobile."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_AMOUNT_ADULTS'].":".str_repeat("\t", 2).$num_adults."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_AMOUNT_CHILDREN'].":".str_repeat("\t", 3).$num_children."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_AMOUNT_WEEKS'].":".str_repeat("\t", 3).$num_weeks."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_DESIRED_DEPARTURE_DATE'].":".str_repeat("\t", 1).$favDepartureDate."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_FLIGHT_ONLY'].":".str_repeat("\t", 4).(( $flightOnly == 1 ) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO']) ."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_ISIS_CODE'].":".str_repeat("\t", 4).$isisCode."\n";
            $emailBody .= $_ARRAYLANG['TXT_HOTEL_COMMENTS'].': '."\n".$comment."\n";

            $mailer->CharSet = CONTREXX_CHARSET;
            $mailer->From = contrexx_addslashes($_REQUEST['contactFormField_18']);
            $mailer->FromName = 'Interessent';
            $mailer->Subject = 'Neue Anfrage auf '.$_CONFIG['domainUrl'];
            $mailer->IsHTML(false);
            $mailer->Body = 'Neue Anfrage f�r '.$hotel." - ".$hotellocation.", von: \n \n";
            $mailer->Body .= $emailBody;
            $mailer->Send();

            //mail for interested customer
            $mailer->ClearAddresses();
            $mailer->From = $this->arrSettings['sender_email'];
            $mailer->FromName = $this->arrSettings['sender_name'];
            $mailer->AddAddress($_REQUEST['contactFormField_18']);
            $mailer->Subject = $this->arrSettings['interest_confirm_subject'];
            $message = str_replace('[[HOTEL_TRAVEL_BOOKING_DETAILS]]', $emailBody, $this->arrSettings['interest_confirm_message']);
            $mailer->Body = $message;
            $mailer->Send();
            $this->_objTpl->setVariable('CONTACT_FEEDBACK_TEXT', $_ARRAYLANG['TXT_HOTEL_CONTACT_SUCCESSFUL']);
        }
        return true;
    }


    /**
     * return array of departure dates for one hotel
     *
     * @param int $hotelID
     * @return array $arrDepartureDates
     */
    function _getTravelDepartureDates($hotelID) {
        global $objDatabase;
        $query = "  SELECT `id`, `from` FROM `".DBPREFIX."module_hotel_travel`
                    WHERE hotel_id = ".$hotelID."
                    AND `from` > ".mktime();
        $objRS = $objDatabase->Execute($query);
        $arrDepartureDates = array();
        while(!$objRS->EOF) {
            $arrDepartureDates[] = $objRS->fields;
            $objRS->MoveNext();
        }
        return $arrDepartureDates;
    }


    /**
     * return the traveldates for one hotel
     *
     * @param int $hotelID
     * @return array $arrNextTravelDates
     */
    function _getTravelDates($hotelID, $limit = 0) {
        global $objDatabase;
        $query = "  SELECT `id`, `from_day`, `from`, `to_day`, `to` FROM `".DBPREFIX."module_hotel_travel`
                    WHERE hotel_id = ".$hotelID."
                    AND `from` > ".mktime();
        $objRS = $objDatabase->SelectLimit($query, $limit);
        $arrNextTravelDates = array();
        while(!$objRS->EOF) {
            $arrNextTravelDates[] = $objRS->fields;
            $objRS->MoveNext();
        }
        return $arrNextTravelDates;
    }




    /**
     * return array of images for corresponding hotelid
     *
     * @param int hotel ID
     * @return array images
     */
    function _getImagesFromObject($id)
    {
        global $objDatabase;

        $query = "  SELECT img.field_id as field_id, content.fieldvalue AS content, name.name, img.uri AS imgsrc
                    FROM ".DBPREFIX."module_hotel_content AS content
                    INNER JOIN ".DBPREFIX."module_hotel_fieldname AS name
                        ON content.field_id = name.field_id
                    INNER JOIN ".DBPREFIX."module_hotel_image AS img
                        ON content.hotel_id = img.hotel_id
                    AND content.field_id = img.field_id
                    WHERE content.field_id
                    IN (
                        SELECT id
                        FROM `".DBPREFIX."module_hotel_field`
                        WHERE TYPE = 'img'
                        OR TYPE = 'panorama'
                    )
                    AND content.hotel_id = ".$id."
                    AND content.lang_id = ".$this->frontLang."
                    AND content.active  = 1
                    AND name.lang_id = ".$this->frontLang;
        $index = 0;
        $images = array();
        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $images[$index] = $objRS->fields;
                $dim = $this->_getImageDim($images[$index]['imgsrc']);
                $images[$index]['width']    = $dim[1];
                $images[$index]['height']   = $dim[2];
                $index++;
                $objRS->MoveNext();
            }
            return $images;
        }
        return false;
    }


    /**
     * This function shows the main page which contains:
     * - form for searching objects
     * - special offers
     *
     * @return void
     */
    function _showOverview() {
        global $_ARRAYLANG, $objDatabase;
        $this->_objTpl->setVariable(array(
            'TXT_HOTEL_RESET'                   =>  $_ARRAYLANG['TXT_HOTEL_RESET'],
            'TXT_HOTEL_SEARCH'                  =>  $_ARRAYLANG['TXT_HOTEL_SEARCH'],
            'TXT_HOTEL_REF_NR'                  =>  $_ARRAYLANG['TXT_HOTEL_REF_NR'],
            'TXT_HOTEL_LOCATIONS'               =>  $_ARRAYLANG['TXT_HOTEL_LOCATIONS'],
            'TXT_HOTEL_LOCATION'                =>  $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_OBJECTTYPE_FLAT'         =>  $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_FLAT'],
            'TXT_HOTEL_OBJECTTYPE_HOUSE'        =>  $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_HOUSE'],
            'TXT_HOTEL_OBJECTTYPE_MULTIFAMILY'  =>  $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_MULTIFAMILY'],
            'TXT_HOTEL_OBJECTTYPE_ESTATE'       =>  $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_ESTATE'],
            'TXT_HOTEL_OBJECTTYPE_INDUSTRY'     =>  $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_INDUSTRY'],
            'TXT_HOTEL_OBJECTTYPE_PARKING'      =>  $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_PARKING'],
            'TXT_HOTEL_OBJECT_TYPE'             =>  $_ARRAYLANG['TXT_HOTEL_OBJECT_TYPE'],
            'TXT_HOTEL_PROPERTYTYPE_PURCHASE'   =>  $_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE_PURCHASE'],
            'TXT_HOTEL_PROPERTYTYPE_RENT'       =>  $_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE_RENT'],
            'TXT_HOTEL_PROPERTY_TYPE'           =>  $_ARRAYLANG['TXT_HOTEL_PROPERTY_TYPE'],
            'TXT_HOTEL_SPECIAL_OFFERS'          =>  $_ARRAYLANG['TXT_HOTEL_SPECIAL_OFFERS'],
            'TXT_HOTEL_SEARCH_STYLE'            =>  $_ARRAYLANG['TXT_HOTEL_SEARCH_STYLE'],
            'TXT_HOTEL_FULLTEXT_SEARCH'         =>  $_ARRAYLANG['TXT_HOTEL_FULLTEXT_SEARCH'],
            'TXT_HOTEL_PRICE'                   =>  $_ARRAYLANG['TXT_HOTEL_PRICE'],
            'TXT_HOTEL_ROOMS'                   =>  $_ARRAYLANG['TXT_HOTEL_ROOMS'],
            'TXT_HOTEL_FROM'                    =>  $_ARRAYLANG['TXT_HOTEL_FROM'],
            'TXT_HOTEL_TO'                      =>  $_ARRAYLANG['TXT_HOTEL_TO'],
            'TXT_HOTEL_NEW_BUILDING'            =>  $_ARRAYLANG['TXT_HOTEL_NEW_BUILDING'],
            'TXT_HOTEL_YES'                     =>  $_ARRAYLANG['TXT_HOTEL_YES'],
            'TXT_HOTEL_NO'                      =>  $_ARRAYLANG['TXT_HOTEL_NO'],
            'TXT_HOTEL_ORDER_BY'                =>  $_ARRAYLANG['TXT_HOTEL_ORDER_BY'],
            'TXT_HOTEL_FOREIGNER_AUTHORIZATION' =>  $_ARRAYLANG['TXT_HOTEL_FOREIGNER_AUTHORIZATION'],
            'TXT_HOTEL_LOGO'                    =>  $_ARRAYLANG['TXT_HOTEL_LOGO'],
            'HOTEL_HOTEL_JAVASCRIPT'            =>  $this->_getHotelJS(),
        ));

        $query = " SELECT DISTINCT `from`, `to` FROM `".DBPREFIX."module_hotel_travel` where `from` > ".mktime()." ORDER BY `from`";
        $objRS = $objDatabase->Execute($query);
        while(!$objRS->EOF) {
            if (empty($objRS->fields['from_day'])) {
                $objRS->fields['from_day'] = $this->_weekdaysShort[$this->frontLang][date('w', $objRS->fields['from'])];
            }
            if (empty($objRS->fields['to_day'])) {
                $objRS->fields['to_day'] =  $this->_weekdaysShort[$this->frontLang][date('w', $objRS->fields['to'])];
            }
            if (empty($objRS->fields['from'])) {
                $objRS->MoveNext();
                continue;
            }
            $this->_objTpl->setVariable(array(
                'HOTEL_DEPARTURE_DATE'      => $objRS->fields['from_day'].' '.date('d.m.Y', $objRS->fields['from']),
                'HOTEL_DEPARTURE_DATE_TS'   => $objRS->fields['from'],
                'HOTEL_RETURN_DATE'         => $objRS->fields['to_day'].' '.date('d.m.Y', $objRS->fields['to']),
                'HOTEL_RETURN_DATE_TS'      => $objRS->fields['to'],
            ));
            $this->_objTpl->parse('departure_date');
            $this->_objTpl->parse('return_date');
            $objRS->MoveNext();
        }

        $query = " SELECT DISTINCT `fl_from` FROM `".DBPREFIX."module_hotel_travel` where `from` > ".mktime().' ORDER BY `fl_from`';
        $objRS = $objDatabase->Execute($query);
        while(!$objRS->EOF) {
            if (empty($objRS->fields['fl_from'])) {
                $objRS->MoveNext();
                continue;
            }
            $this->_objTpl->setVariable(array(
                'HOTEL_FL_FROM'     => htmlentities($objRS->fields['fl_from']),
            ));
            $this->_objTpl->parse('fl_from');
            $objRS->MoveNext();
        }

        //$locations = $this->_getLocations();
        //foreach ($locations as $location) {
        //    $this->_objTpl->setVariable(array('HOTEL_LOCATION_CONTENT'    =>  $location));
        //    $this->_objTpl->parse("locations");
        //}

        $this->_showSpecialOffers();
    }

    /**
     * return array of locations from database
     *
     * @return array locations
     */
    function _getLocations() {
        global $objDatabase;
        $query = "  SELECT TRIM(a.fieldvalue) as location FROM ".DBPREFIX.'module_hotel_content AS a
                                                                WHERE a.field_id = (
                                                                    SELECT field_id
                                                                    FROM '.DBPREFIX.'module_hotel_fieldname
                                                                    WHERE lower( name ) = "ort"
                                                                    AND lang_id = 1 )
                                                                AND a.lang_id = '.$this->frontLang.'
                                                                GROUP BY location ';
        $objRS = $objDatabase->Execute($query);
        if ($objRS) {
            while(!$objRS->EOF) {
                $locations[] = $objRS->fields['location'];
                $objRS->MoveNext();
            }
        }
        return $locations;
    }

    /**
     * display the specialoffers
     *
     * @return void
     */
    function _showSpecialOffers() {
        $showSpecialOffersOnly = true;
        $this->_showHotelList($showSpecialOffersOnly);
    }


    /**
     * function handling protected link requests
     *
     * @return void
     */
    function _getPDF()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        require_once(ASCMS_LIBRARY_PATH.DS.'/FRAMEWORK'.DS."Validator.class.php");
        $objValidator = new FWValidator();

        $ids=explode('_',$_GET['id']);
        $hotelID=intval($ids[0]);
        $fieldID=intval($ids[1]);
        if (isset($_POST['hotel_id'])) {  //form was sent
            $name             = !empty($_POST['name']) ? contrexx_addslashes(strip_tags($_POST['name'])) : '';
            $firstname        = !empty($_POST['firstname']) ? contrexx_addslashes(strip_tags($_POST['firstname'])) : '';
            $company          = !empty($_POST['company']) ? contrexx_addslashes(strip_tags($_POST['company'])) : '';
            $street           = !empty($_POST['street']) ? contrexx_addslashes(strip_tags($_POST['street'])) : '';
            $zip              = !empty($_POST['zip']) ? intval($_POST['zip']) : '';
            $location         = !empty($_POST['location']) ? contrexx_addslashes(strip_tags($_POST['location'])) : '';
            $telephone        = !empty($_POST['telephone']) ? contrexx_addslashes(strip_tags($_POST['telephone'])) : '';
            $telephone_office = !empty($_POST['telephone_office']) ? contrexx_addslashes(strip_tags($_POST['telephone_office'])) : '';
            $telephone_mobile = !empty($_POST['telephone_mobile']) ? contrexx_addslashes(strip_tags($_POST['telephone_mobile'])) : '';
            $purchase         = isset($_POST['purchase']) ? 1 : 0;
            $funding          = isset($_POST['funding']) ? 1 : 0;
            $email            = !empty($_POST['email']) ? contrexx_addslashes(strip_tags($_POST['email'])) : '';
            $comment          = !empty($_POST['comment']) ? contrexx_addslashes(strip_tags($_POST['comment'])) : '';
            $hotelID           = !empty($_POST['hotel_id']) ? intval($_POST['hotel_id']) : '';
            $fieldID          = !empty($_POST['field_id']) ? intval($_POST['field_id']) : '';

            $error=0;
            if ($objValidator->isEmail($email)) {
                if (!empty($name) && !empty($telephone) && !empty($email) && $hotelID > 0 && $fieldID > 0) {
                    require_once(ASCMS_LIBRARY_PATH.DS.'/phpmailer'.DS."class.phpmailer.php");
                    $objRS = $objDatabase->SelectLimit("SELECT email
                                                FROM ".DBPREFIX."module_hotel_contact
                                                WHERE hotel_id = '$hotelID'
                                                AND email = '$email'
                                                AND timestamp > ".(mktime() - 600), 1);
                    if ($objRS->RecordCount() > 0) {
                        $this->_objTpl->setVariable('TXT_HOTEL_STATUS', '<span class="errmsg">'.$_ARRAYLANG['TXT_HOTEL_ALREADY_SENT_RECENTLY'].'</span>');
                        $this->_showContactForm($hotelID, $fieldID);
                        return false;
                    }

                    $objRS = $objDatabase->SelectLimit("SELECT fieldvalue
                                                FROM ".DBPREFIX."module_hotel_content
                                                WHERE hotel_id = '$hotelID'
                                                AND field_id = '$fieldID'
                                                AND lang_id = '".$this->frontLang."'", 1);
                    if ($objRS) {
                        $link = 'http://'.$_CONFIG['domainUrl'].str_replace(" ", "%20", $objRS->fields['fieldvalue']);
                        $mailer = new PHPMailer();
                        $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_hotel_contact
                                                VALUES
                                                (NULL, '$email', '$name', '$firstname', '$street', '$zip', '$location', '$company', '$telephone', '$telephone_office', '$telephone_mobile', '$purchase', '$funding', '$comment', '$hotelID', '$fieldID', ".mktime()." )");

                        $mailer->CharSet = CONTREXX_CHARSET;
                        $mailer->IsHTML(false);
                        $mailer->From       = $this->arrSettings['sender_email'];
                        $mailer->FromName   = $this->arrSettings['sender_name'];
                        $mailer->Subject    = $this->arrSettings['prot_link_message_subject'];
                        $mailer->Body       = str_replace('[[HOTEL_PROTECTED_LINK]]', $link, $this->arrSettings['prot_link_message_body'])."\n\n";
                        $mailer->AddAddress($email);
                        $mailer->Send();
                    } else {
                        $this->_objTpl->setVariable('TXT_HOTEL_STATUS', '<span class="errmsg">DB error.</span>');
                    }

                } else {
                    $error=1;
                }

            } else {
                $error=1;
            }


            if ($error==1) {
                $this->_objTpl->setVariable('TXT_HOTEL_STATUS', '<span class="errmsg">'.$_ARRAYLANG['TXT_HOTEL_MISSIONG_OR_INVALID_FIELDS'].'</span>');
            } else {
                $this->_objTpl->setVariable('TXT_HOTEL_STATUS', '<span class="okmsg">'.$_ARRAYLANG['TXT_HOTEL_CONTACT_SUCCESSFUL'].'</span>');
            }
//        } else { //form was not sent
        }
        $this->_showContactForm($hotelID, $fieldID);
        return true;
    }



    /**
     * show the contact form for the according hotel- and field-ID
     *
     * @param int $hotelID
     * @param int $fieldID
     * @return void
     */
    function _showContactForm($hotelID, $fieldID) {
        global $_ARRAYLANG;
        $this->_objTpl->setVariable(array(
            'HOTEL_ID' => $hotelID,
            'TXT_HOTEL_FIELDS_REQUIRED' => $_ARRAYLANG['TXT_HOTEL_FIELDS_REQUIRED'],
            'TXT_HOTEL_NAME'                => $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_FIRSTNAME'       => $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'],
            'TXT_HOTEL_STREET'          => $_ARRAYLANG['TXT_HOTEL_STREET'],
            'TXT_HOTEL_ZIP'             => $_ARRAYLANG['TXT_HOTEL_ZIP'],
            'TXT_HOTEL_COMPANY'         => $_ARRAYLANG['TXT_HOTEL_COMPANY'],
            'TXT_HOTEL_PURCHASE'            => $_ARRAYLANG['TXT_HOTEL_PURCHASE'],
            'TXT_HOTEL_FUNDING'         => $_ARRAYLANG['TXT_HOTEL_FUNDING'],
            'TXT_HOTEL_TELEPHONE'       => $_ARRAYLANG['TXT_HOTEL_TELEPHONE'],
            'TXT_HOTEL_TELEPHONE_OFFICE'    => $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'],
            'TXT_HOTEL_TELEPHONE_MOBILE'    => $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'],
            'TXT_HOTEL_LOCATION'            => $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_COMMENTS'            => $_ARRAYLANG['TXT_HOTEL_COMMENTS'],
            'TXT_HOTEL_DELETE'          => $_ARRAYLANG['TXT_HOTEL_DELETE'],
            'TXT_HOTEL_SEND'                => $_ARRAYLANG['TXT_HOTEL_SEND'],
            'HOTEL_FIELD_ID'                => $fieldID,
        ));
    }

    /**
     * get the attribute list for the showObject page
     *
     * @return void
     */
    function _getListing() {
        for($i=1; $i<=$this->_listingCount;$i++) {
            $list = $this->_getFieldFromText('Aufz�hlung'.$i);
            if (!empty($list)) {
                $this->_objTpl->setVariable('HOTEL_LISTING', $this->_getFieldFromText('Aufz�hlung'.$i));
                $this->_objTpl->parse("listing");
            }
        }
    }


    /**
     * get the meal for the hotel (doppelzimmer)
     *
     * @param unknown_type $hotelID
     * @return unknown
     */
    function _getMeal($hotelID) {
        global $objDatabase;
        $query = sprintf("SELECT `meal` FROM `".DBPREFIX."module_hotel_travel` WHERE `hotel_id` = %d AND `from` > ".mktime()." ORDER BY `from` ASC", $hotelID);
        $objRS = $objDatabase->SelectLimit($query, 1);
        return $objRS->fields['meal'];
    }

    /**
     * show the details of an object
     *
     * @return void
     */
    function _showObject()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!empty($_GET['id'])) {
            $hotelID = intval($_GET['id']);
            if (empty($hotelID)) {
                CSRF::header('Location: ?section=hotel&cmd=hotellist');
                die();
            }
        }
        $this->_getFieldNames($hotelID, $this->frontLang);
        if (($objRS = $objDatabase->SelectLimit('SELECT reference FROM '.DBPREFIX.'module_hotel WHERE id='.$hotelID, 1)) !== false) {
              $reference = $objRS->fields['reference'];
        }

        $ID = $this->_getFieldFromText('hotel_id');

        $query = sprintf('SELECT `pdt` as minprice FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id = %d AND `from` > '.mktime(), $ID);

        if (($lowestPriceDBResult = $objDatabase->Execute($query)) !== false) {
            $lowestPrice = $lowestPriceDBResult->fields['minprice'];
        }

        if (empty($lowestPrice)) {
            $query = sprintf('SELECT `pdt` as minprice FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id = %d AND `from` > '.mktime(), $ID);
            if (($lowestPriceDBResult = $objDatabase->Execute($query)) !== false) {
                $lowestPrice = $lowestPriceDBResult->fields['minprice'];
            }
        }




        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_PRICE_PREFIX'            => $this->arrSettings['currency_lang_'.$this->frontLang],
            'TXT_HOTEL_PRICE_SUFFIX'            => $this->_currencySuffix,
            'TXT_HOTEL_PRICE_PREFIX_EURO'       => $this->_currencyEuroPrefix,
            'TXT_HOTEL_PRICE_SUFFIX_EURO'       => $this->_currencyEuroSuffix,
            'TXT_HOTEL_SHOWMAP'                 => $_ARRAYLANG['TXT_HOTEL_SHOWMAP'],
            'TXT_HOTEL_PRINT_PAGE'              => $_ARRAYLANG['TXT_HOTEL_PRINT_PAGE'],
            'TXT_HOTEL_BACK'                    => $_ARRAYLANG['TXT_HOTEL_BACK'],
            'TXT_HOTEL_CONTACT_FORM'            => $_ARRAYLANG['TXT_HOTEL_CONTACT_FORM'],
            'TXT_HOTEL_HOMEPAGE_LINK'           => $_ARRAYLANG['TXT_HOTEL_HOMEPAGE_LINK'],
            'TXT_HOTEL_SERVICE_LINKS'           => $_ARRAYLANG['TXT_HOTEL_SERVICE_LINKS'],
            'TXT_HOTEL_GOTO_TOP'                => $_ARRAYLANG['TXT_HOTEL_GOTO_TOP'],
            'TXT_HOTEL_TO_PICTURES'             => $_ARRAYLANG['TXT_HOTEL_TO_PICTURES'],
            'TXT_HOTEL_TO_OBJECTDATA'           => $_ARRAYLANG['TXT_HOTEL_TO_OBJECTDATA'],
            'TXT_HOTEL_TO_LINKS'                => $_ARRAYLANG['TXT_HOTEL_TO_LINKS'],
            'TXT_HOTEL_PICTURES'                => $_ARRAYLANG['TXT_HOTEL_PICTURES'],
            'TXT_HOTEL_OBJECTDATA'              => $_ARRAYLANG['TXT_HOTEL_OBJECTDATA'],
            'TXT_HOTEL_LINKS'                   => $_ARRAYLANG['TXT_HOTEL_LINKS'],
            'TXT_HOTEL_TO_PLANS'                => $_ARRAYLANG['TXT_HOTEL_TO_PLANS'],
            'TXT_HOTEL_TO_MAP'                  => $_ARRAYLANG['TXT_HOTEL_TO_MAP'],
            'TXT_HOTEL_INTERESTED_IN_OBJECT'    => $_ARRAYLANG['TXT_HOTEL_INTERESTED_IN_OBJECT'],
            'HOTEL_ID'                          => $hotelID,
        ));

      $img = $this->_getFieldFromText('Übersichtsbild', 'img');
      $imgOverviewKey = $this->_currFieldID;
      $imgdim = $this->_getImageDim($img, 540);
//      $homepageLink = trim($this->_getFieldFromText('Link auf Homepage'));
//      $homepageLink_active = $this->_getFieldFromText('Link auf Homepage', 'active');
//      $this->_getListing();
//      $lowestPrice = $this->_getLowestPrice();


        $meal = $this->_getMeal($ID);

        !empty($_ARRAYLANG['TXT_HOTEL_MEAL_'.strtoupper($meal)])
            ?   ($meal_txt = $_ARRAYLANG['TXT_HOTEL_MEAL_'.strtoupper($meal)])
            :   ($meal_txt = $meal);

        $this->_objTpl->setVariable(array(
            'HOTEL_HEADER'          => $this->_getFieldFromText('Kopfzeile'),
            'HOTEL_ADDRESS'         => $this->_getFieldFromText('Adresse'),
            'HOTEL_REF_NR'          => $reference,
            'HOTEL_LOCATION'        => $this->_getFieldFromText('Ort'),
            'HOTEL_PRICE'           => $lowestPrice,
            'HOTEL_PRICE_EURO'      => number_format($lowestPrice / $this->_currencyEuroFactor, 0),
            'HOTEL_DESCRIPTION'     => $this->_getFieldFromText('Beschreibung'),
            'HOTEL_HEADLINE'        => $this->_getFieldFromText('Headline'),
            'HOTEL_INFO_SPECIAL'    => $this->_getFieldFromText('besonderes'),
            'HOTEL_INFO_LOCATION'   => $this->_getFieldFromText('lage'),
            'HOTEL_INFO_ACTIVITIES' => $this->_getFieldFromText('aktivit�ten'),
            'HOTEL_HIGHLIGHT'       => $this->_getFieldFromText('highlight'),
// Undefined
//            'HOTEL_HOMEPAGE_LINK'   => $homepageLink,
            'HOTEL_IMG_DIM'         => $imgdim[0],
            'HOTEL_IMG_WIDTH'       => $imgdim[1],
            'HOTEL_IMG_HEIGHT'      => $imgdim[2],
            'HOTEL_MEAL'            => $meal_txt,
            'HOTEL_IMG_SRC'         => $img,
            'HOTEL_ID'              => $hotelID,
            'HOTEL_IMAGES_INDEX'    => $imgOverviewKey,
        ));

        foreach ($this->_generalFields as $field) {
            $title = $this->_getFieldFromText($field, 'names');
            $content = $this->_getFieldFromText($field);

            if (!empty($title) && !empty($content) && $this->_getFieldFromText($field, 'active')) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_INFO_GENERAL_TITLE'  => $title,
                    'HOTEL_INFO_GENERAL_CONTENT' => $content,
                ));
                $this->_objTpl->parse('generalTab');
            }
        }

        foreach ($this->_activityFields as $field) {
            $title = $this->_getFieldFromText($field, 'names');
            $content = $this->_getFieldFromText($field);

            if (!empty($title) && !empty($content) && $this->_getFieldFromText($field, 'active')) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_INFO_ACTIVITY_TITLE' => $title,
                    'HOTEL_INFO_ACTIVITY_CONTENT' => $content,
                ));
                $this->_objTpl->parse('activityTab');
            }
        }

        foreach ($this->_locationFields as $field) {
            $title = $this->_getFieldFromText($field, 'names');
            $content = $this->_getFieldFromText($field);

            if (!empty($title) && !empty($content) && $this->_getFieldFromText($field, 'active')) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_INFO_LOCATION_TITLE' => $title,
                    'HOTEL_INFO_LOCATION_CONTENT' => $content,
                ));
                $this->_objTpl->parse('locationTab');
            }
        }

        foreach ($this->_specialFields as $field) {
            $title = $this->_getFieldFromText($field, 'names');
            $content = $this->_getFieldFromText($field);

            if (!empty($title) && !empty($content) && $this->_getFieldFromText($field, 'active')) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_INFO_SPECIAL_TITLE'  => $title,
                    'HOTEL_INFO_SPECIAL_CONTENT' => $content,
                ));
                $this->_objTpl->parse('specialTab');
            }
        }

//      $this->_objTpl->parse("basicData");
        $this->_objTpl->touchBlock("basicData");

// Unused
//        $imgRow = 1;
        $lnkRow = 1;
        $textcount = 0;
        $imagecount = 0;
        $linkcount = 0;
        $i = 0;
        $firstpic = true;
        foreach ($this->fieldNames as $fieldKey => $field) {
            if ( $field['content']['active']  == 1
            && !in_array(strtolower($field['names'][1]), $this->_usedFields)) {
                switch($field['type']) {
                    case 'text':
                    case 'textarea':
                    case 'digits_only':
                    case 'price':
                        $textcount++;
                        $this->_objTpl->setVariable(array(
                            'HOTEL_FIELD_NAME'          =>  htmlentities($field['names'][$this->frontLang], ENT_QUOTES),
                            'HOTEL_FIELD_CONTENT'       =>  htmlentities($field['type'] == 'price' ? number_format($field['content'][$this->frontLang],0,".","'") : $field['content'][$this->frontLang], ENT_QUOTES),
                            'TXT_HOTEL_CURRENCY_PREFIX' =>  $field['type'] == 'price' ? htmlentities($this->arrSettings['currency_lang_'.$this->frontLang], ENT_QUOTES) : '',
                            'TXT_HOTEL_CURRENCY_SUFFIX' =>  $field['type'] == 'price' ? $this->_currencySuffix : '',
                        ));
                        if (trim($field['content'][$this->frontLang]) != '') {
                            if ($textcount < $this->_fieldCount['text']) {
                                $this->_objTpl->touchBlock('textListHR');
                            }
                            $this->_objTpl->parse('textList');
                        }
                    break;

                    case 'img':
                        $imagecount++;
                        $img = trim($field['img']);

                        if (!empty($img) ) {
                            //special case for panorama
                            if ($field['names'][1] == 'Panorama') {
                                $imgdim = $this->_getImageDim($img, 500);
                                $this->_objTpl->setVariable(array(
                                    'HOTEL_PANO_SRC' => $img,
                                    'HOTEL_PANO_DIM' => $imgdim[0],
                                ));
                                $this->_objTpl->parse('panorama');
                                break;
                            }

                            $imgdim = $this->_getImageDim($img, 78);
                            $this->_objTpl->setVariable(array(
//                              'HOTEL_FIELD_NAME'      =>  htmlentities($field['names'][$this->frontLang], ENT_QUOTES),
//                              'HOTEL_FIELD_CONTENT'   =>  htmlentities($field['content'][$this->frontLang], ENT_QUOTES),
                                'HOTEL_IMG_SRC'         =>  $img,
                                'HOTEL_IMG_WIDTH'       =>  $imgdim[1],
                                'HOTEL_IMG_HEIGHT'      =>  $imgdim[2],
                                'HOTEL_IMG_INDEX'       =>  $i++,
                            ));

                            if ($firstpic) {
                                $imgdim = $this->_getImageDim($img, 200);
                                $firstpic = false;
                                $this->_objTpl->setVariable(array(
                                    'HOTEL_IMG_PB_SRC'  => $img,
                                    'HOTEL_FIELD_NAME'  => $field['content'][$this->frontLang],
                                ));
//                              $this->_objTpl->parse('pictureBox');
                            } else {

                                $this->_objTpl->setVariable(array(
                                    'HOTEL_THUMB_NR'    => $imagecount-2, //minus first and minus 1 (array starts at 0)
                                ));
                                $this->_objTpl->parse('imageList');
                            }
                        }
                    break;

                    case 'panorama':
                        $img = trim($field['img']);
                        if (!empty($img)) {
                            $imgdim = $this->_getImageDim($img, 530);
                            $this->_objTpl->setVariable(array(
                                'HOTEL_FIELD_NAME'      =>  htmlentities($field['names'][$this->frontLang], ENT_QUOTES),
                                'HOTEL_FIELD_CONTENT'   =>  htmlentities($field['content'][$this->frontLang], ENT_QUOTES),
                                'HOTEL_IMG_SRC'         =>  $img,
                                'HOTEL_IMG_WIDTH'       =>  $imgdim[1],
                                'HOTEL_IMG_HEIGHT'      =>  $imgdim[2],
                                'HOTEL_IMG_DIM'         =>  $imgdim[0],
                                'HOTEL_IMAGES_INDEX'        =>  $fieldKey,
                            ));
//                          $this->_objTpl->parse('panorama');
                        }
                    break;

                    case 'link':
                    case 'protected_link':
                        $linkcount++;
                        $splitName = explode(" - ", $field['names'][$this->frontLang]);
                        $iconType = strtolower(trim($splitName[count($splitName)-1]));
                        $this->_objTpl->setVariable(array(
                            'HOTEL_LINK_ICON_SRC'       =>  $this->_getIcon($iconType),
                            'HOTEL_FIELD_NAME'          =>  htmlentities($field['names'][$this->frontLang], ENT_QUOTES),
                            'HOTEL_FIELD_CONTENT'       =>  $field['type']=='protected_link' ? '?section=hotel&amp;cmd=getPDF&amp;id='.$hotelID.'_'.$fieldKey : $field['content'][$this->frontLang],
                        ));


                    if (trim($field['content'][$this->frontLang]) != '') {
                        if ($lnkRow++ % 2 == 0) {
                            $this->_objTpl->parse('linkList');
                            $this->_objTpl->parse('linkListRow');
                        } else {
                            $this->_objTpl->parse('linkList');
                        }
                    }
                    break;
                }
            }
        }

         $this->_objTpl->addBlock('HOTEL_SHOWOBJ_JAVASCRIPT', 'hotel_showobj_js', '
<script type="text/javascript">
//<![CDATA[

    {HOTEL_DETAILS_JAVASCRIPT}
  {HOTEL_PICSWAP_JAVASCRIPT}

//]]>
</script>');

        $this->_objTpl->setVariable(array(
            'HOTEL_DETAILS_JAVASCRIPT'  => $this->_getDetailsJS(),
            'HOTEL_PICSWAP_JAVASCRIPT'  => $this->_getPictureSwapJS($hotelID, $fieldKey, $imagecount - 1), // minus large picture box
        ));
        $this->_objTpl->parse('hotel_showobj_js');
        $this->_showCategoryData();
    }


    function _showCategoryData()
    {
// Unused
//        $hotelId = intval($_REQUEST['id']);
        foreach ($this->_categoryFields as $categoryField) {
            if (!$this->_getFieldFromText($categoryField, 'active') || $this->_getFieldFromText($categoryField) == '') {
                continue;
            }
            $stars = str_replace(',', '.', $this->_getFieldFromText($categoryField));
            $value[$stars][] = $this->_getFieldFromText($categoryField, 'names');
        }

        $i=0;
        while(!empty($value)) {
            $i++;
            $keys=array_keys($value);
            $biggest = max($keys);
            if (empty($value[$biggest])) {
                unset($value[$biggest]);
                continue;
            }
            $name  = array_shift($value[$biggest]);
            $stars = $biggest;
            $this->_objTpl->setVariable(array(
                'HOTEL_TRAVEL_CATEGORY_NAME'        => $name,
                'HOTEL_STARS'   => $stars * $this->_categoryStarsWidth,
                'HOTEL_STARS_MARGIN'    => $this->_categoryMargin - $stars*$this->_categoryStarsWidth-3,
            ));
            $this->_objTpl->parse('categories');
        }
    }





    function _showTravelData($hotelId = 0, $showall = true) {
        global $_ARRAYLANG, $objDatabase;

        $hotelID = intval($_GET['id']);
        $this->_getFieldNames($hotelID);
        $hotelId = $hotelId > 0 ? intval($hotelId) : $this->_getFieldFromText('hotel_id');
        $this->_objTpl->setVariable('HOTEL_ID', $hotelID);


        $query = "  SELECT `isis_code`, `from`, `from_day`, `to`, `to_day`, `meal`, `pst`, `pdt`, `ptt`, `ps2wt`, `pd2wt`, `pt2wt`,
                            `spez1`, `spez2`, `spez3`, `spez4`, `spez5`, `spez6`, `spez7`, `pdt` as minprice
                    FROM `".DBPREFIX."module_hotel_travel`
                    WHERE hotel_id = ".$hotelId."  AND `from` > ".mktime();
        if (($objRS = $objDatabase->Execute($query)) === false) {
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
            return false;
        }

        if ($objRS->RecordCount() == 0) {
            $query = "  SELECT `isis_code`, `from`, `from_day`, `to`, `to_day`,, `meal`, `pst`, `pdt`, `ptt`, `ps2wt`, `pd2wt`, `pt2wt`,
                                `spez1`, `spez2`, `spez3`, `spez4`, `spez5`, `spez6`, `spez7`, `pdt` as minprice,
                        FROM `".DBPREFIX."module_hotel_travel`
                        WHERE hotel_id = ".$hotelId;
            if (($objRS = $objDatabase->Execute($query)) === false) {
                $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
                return false;
            }
        }

        $tmp = 0;
        for($i=1; $i<=7; $i++) {
            if (!empty($objRS->fields['spez'.$i])) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_SPEZ_CLASS' => ($tmp++ % 2) + 1,
                    'HOTEL_SPEZ_VALUE' => $objRS->fields['spez'.$i],
                ));
            }
            $this->_objTpl->parse('spez');
        }

        if ($showall) {
            $row = 0;
            while(!$objRS->EOF) {
                if (empty($objRS->fields['from_day'])) {
                    $objRS->fields['from_day'] = $this->_weekdaysShort[$this->frontLang][date('w', $objRS->fields['from'])];
                }
                if (empty($objRS->fields['to_day'])) {
                    $objRS->fields['to_day'] =  $this->_weekdaysShort[$this->frontLang][date('w', $objRS->fields['to'])];
                }
                $this->_objTpl->setVariable(array(
                    'HOTEL_TRAVEL_ROW_CLASS' => $row++ % 2 + 1,
                    'HOTEL_TRAVEL_ISISCODE' => $objRS->fields['isis_code'],
                    'HOTEL_TRAVEL_FL_AIR'   => $objRS->fields['fl_air'],
                    'HOTEL_TRAVEL_FL_FROM'  => $objRS->fields['fl_from'],
                    'HOTEL_TRAVEL_FROM'     => $objRS->fields['from_day'].', '.date('d.m', $objRS->fields['from']),
                    'HOTEL_TRAVEL_TO'       => $objRS->fields['to_day'].', '.date('d.m', $objRS->fields['to']),
                    'HOTEL_TRAVEL_MEAL'     => $objRS->fields['meal'],
                    'HOTEL_TRAVEL_PST'      => $objRS->fields['pst'],
                    'HOTEL_TRAVEL_PDT'      => $objRS->fields['pdt'],
                    'HOTEL_TRAVEL_PTT'      => $objRS->fields['ptt'],
                    'HOTEL_TRAVEL_PS2WT'    => $objRS->fields['ps2wt'],
                    'HOTEL_TRAVEL_PD2WT'    => $objRS->fields['pd2wt'],
                    'HOTEL_TRAVEL_PT2WT'    => $objRS->fields['pt2wt'],
                ));
                $this->_objTpl->parse('travelData');
                $objRS->MoveNext();
            }
        } else {
            //only get the lowest price
            //$this->_getLowestPrice($hotelId, true);
            return $objRS->fields['minprice'];
        }
//      $this->_objTpl->parse('module_hotel_travel_overview');
        return true;
    }


    /**
     * return image source to the specified icon
     *
     * @param string icontype
     * @return string path to icon
     */
    function _getIcon($icon) {
        return 'images/content/hotel/'.$icon.'.gif';
    }



    function _doNothing() {
    }


    /**
     * returns an array containing all hotel_IDs which match the price criteria
     * @param integer $price
     * @return array
     */
    function _getHotelIdsByPrice($price) {
        global $objDatabase;
        $price = intval($price);

        $hotelIds = array();

        $query = "
            SELECT hotel_id
              FROM `DBPREFIX.'module_hotel_travel`
             WHERE `pst` < '$price'
                OR `pdt` < '$price'
                OR `ptt` < '$price'
                OR `ps2wt` < '$price'
                OR `pd2wt` < '$price'
                OR `pt2wt` < '$price'
             GROUP BY hotel_id";

        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $hotelIds[] = $objRS->fields['hotel_id'];
                $objRS->MoveNext();
            }
        }

        return $hotelIds;
    }

    /**
     * returns an array containing all hotel_IDs which match the meal criteria
     * @param integer $meal
     * @return array
     */
    function _getHotelIdsByMeal($meal)
    {
        global $objDatabase;

        $meal = contrexx_addslashes($meal);
// Unused
//        $hotelIds = array();
        $query = sprintf("SELECT hotel_id FROM `%s` WHERE `meal` = '%s' GROUP BY hotel_id;",  DBPREFIX.'module_hotel_travel', $meal);
        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $this->getHotel_tableID($objRS->fields['hotel_id']);
                $objRS->MoveNext();
            }
        }
        return $this->_hotelIDs;
    }

    /**
     * returns an array containing all hotel_IDs which match the searchterm criteria
     * @param string $searchterm
     * @return array
     */
    function _getHotelIdsBySearchTerm($searchterm) {
        global $objDatabase;
        $searchterm = contrexx_addslashes($searchterm);

        $hotelIds = array();

        $query = "SELECT hotel_id FROM `".DBPREFIX."module_hotel_content` WHERE `fieldvalue` LIKE '%$searchterm%' GROUP BY hotel_id";


        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $hotelIds[] = $objRS->fields['hotel_id'];

                $objRS->MoveNext();
            }
        }

        return $hotelIds;
    }

    function _getHotelIdsByFlightFrom($fl_from) {
        global $objDatabase;

        $hotelIds = array();

        $query = sprintf("SELECT hotel_id FROM `%s` WHERE `fl_from` = '".contrexx_addslashes($fl_from)."' GROUP BY hotel_id;", DBPREFIX.'module_hotel_travel');

        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $hotelIds[] = $objRS->fields['hotel_id'];
                $objRS->MoveNext();
            }
        }

        return $hotelIds;
    }

    function _getHotelIdsByDepartureDate($departureDate) {
        global $objDatabase;

        $hotelIds = array();

        $query = sprintf("SELECT hotel_id FROM `%s` WHERE `from` = ".contrexx_addslashes($departureDate)." GROUP BY hotel_id;", DBPREFIX.'module_hotel_travel');

        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $hotelIds[] = $objRS->fields['hotel_id'];
                $objRS->MoveNext();
            }
        }

        return $hotelIds;
    }

    function _getHotelIdsByReturnDate($returnDate) {
        global $objDatabase;

        $hotelIds = array();

        $query = sprintf("SELECT hotel_id FROM `%s` WHERE `to` = ".contrexx_addslashes($returnDate)." GROUP BY hotel_id;", DBPREFIX.'module_hotel_travel');

        if (($objRS = $objDatabase->Execute($query)) !== false) {
            while(!$objRS->EOF) {
                $hotelIds[] = $objRS->fields['hotel_id'];
                $objRS->MoveNext();
            }
        }

        return $hotelIds;
    }


    /**
     * This function builds a query which will return a list of hotels matching the criterias...
     *
     * @param boolean $showSpecialOffersOnly
     * @return string query
     */
    function _buildQuery($showSpecialOffersOnly=false)
    {
//        global $_ARRAYLANG;

        // We get the parameters from the search form ($_POST)
        if ($_GET['cmd'] == "hotellistdynamic") {

// Adfinis comment: not yet sure, what this does.. ToDo
//            if (!empty($_REQUEST['foreigner_auth'])) {
//                $foreigner_auth = intval($_REQUEST['foreigner_auth']) > 0 ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'];
//            }

            $flightFrom = contrexx_addslashes(strip_tags(($_REQUEST['fl_from'])));
            $departureDate = contrexx_addslashes(strip_tags(($_REQUEST['departure_date'])));
            $returnDate = contrexx_addslashes(strip_tags(($_REQUEST['return_date'])));
            $citycode = contrexx_addslashes(strip_tags(($_REQUEST['city'])));
            $category = intval($_REQUEST['hotelcategory']);
            $tprice = contrexx_addslashes(strip_tags(($_REQUEST['tprice'])));
            $meal = contrexx_addslashes(strip_tags(($_REQUEST['meal'])));
            $searchterm = contrexx_addslashes($_REQUEST['search']);

            $orderBy = !empty($_REQUEST['order_by']) ? contrexx_addslashes($_REQUEST['order_by']) : 'minprice';

            /**
             * Build a custom WHERE clause using the passed search parameter.
             */
            $WHERE = array();

            if (!empty($flightFrom)) {
                unset($hotelIDs);
                $hotelIDs = $this->_getHotelIdsByFlightFrom($flightFrom);
                if (count($hotelIDs) > 0) {
                    array_push($WHERE, sprintf("l.fieldvalue IN (%s)",  implode(',', $hotelIDs)));
                }
            }

            if (!empty($departureDate)) {
                unset($hotelIDs);
                $hotelIDs = $this->_getHotelIdsByDepartureDate($departureDate);
                if (count($hotelIDs) > 0) {
                    array_push($WHERE, sprintf("/*foo*/l.fieldvalue IN (%s)",  implode(',', $hotelIDs)));
                }
            }

            if (!empty($returnDate)) {
                unset($hotelIDs);
                $hotelIDs = $this->_getHotelIdsByReturnDate($returnDate);
                if (count($hotelIDs) > 0) {
                    array_push($WHERE, sprintf("/*bar*/l.fieldvalue IN (%s)",  implode(',', $hotelIDs)));
                }
            }

            // city (aka region)
            if ($citycode != "") {
                array_push($WHERE, sprintf("`g`.`fieldvalue` = '%s'", $citycode));
            }
            // hotelcategory
            if ($category != "") {
                array_push($WHERE, sprintf("`h`.`fieldvalue` = '%s'", $category));
            }
            // price
            if ($tprice != "") {
                unset($hotelIDs);
                $hotelIDs = $this->_getHotelIdsByPrice($tprice);
                if (count($hotelIDs) > 0) {
                    array_push($WHERE, sprintf("`hotel`.`id` IN (%s)",  implode(',', $hotelIDs)));
                }
            }
            // meal
            if ($meal != "") {
                unset($hotelIDs);
                $hotelIDs = $this->_getHotelIdsByMeal($meal);
                if (count($hotelIDs) > 0) {
                    array_push($WHERE, sprintf("`hotel`.`id` IN (%s)",  implode(',', $hotelIDs)));
                } else {
                    array_push($WHERE, sprintf("false"));
                }
            }

            // search term
            if ($searchterm != "") {
                unset($hotelIDs);
                $hotelIDs = $this->_getHotelIdsBySearchTerm($searchterm);
                if (count($hotelIDs) > 0) {
                    array_push($WHERE, sprintf("`hotel`.`id` IN (%s)",  implode(',', $hotelIDs)));
                } else {
                    array_push($WHERE, "false");
                }
            }

            /**
             * Build a custom ORDER BY clause using the passed cmd parameter.
             */
            if ($orderBy != "") {
                $dynamicOrderBy = sprintf(' ORDER BY %s ASC', $orderBy);
            } else {
//              nur by price oder auch prio?!
//              $dynamicOrderBy = ' ORDER BY `prio`,`minprice` ASC';
                $dynamicOrderBy = ' ORDER BY `minprice` ASC';
            }

        // No parameters but show special offers
        } elseif ($showSpecialOffersOnly == true) {

            /*
            WHERE special_offer = 1
                AND visibility != "disabled"
                ORDER BY rand()
                LIMIT 4';
            */

            $WHERE = array();
            array_push($WHERE, "`special_offer` = '1'");
            array_push($WHERE, "`visibility` != 'disabled'");

            $dynamicOrderBy = ' ORDER BY `prio`,`minprice` ASC';

        // We get the parameters from the url ($_GET)
        } else {
            /**
             * This section will be called when cmd parameter = hotellist_aegypten_HRG_tauchen_preis
             * The parameter's syntax is as follows:
             *  - Field 0: Keyword         ('hotellistdynamic')
             *  - Field 1: Country         ('Ägypten')
             *  - Field 2: Region          ('Saqala')
             *  - Field 3: Citycode        ('SSH')
             *  - Field 4: Interest        ('Kinderfreundl.')
             *  - Field 5: Order By        ('minprice')
             *  - Field 6: Sort            ('ASC/DESC')
             *  - Field 7: Limit Parameter ('5')
             * Delimiter is underline "_"
             *
             */
            $hotelListSection = urldecode($_GET['cmd']);

            $hotelListSectionParts = explode('_', $hotelListSection);

            // the first value ist 'hotellistdynamic' throw this out of the array
            array_shift($hotelListSectionParts);

            // check the 7 specifyed values. Set empty values to the reserved keyword "EMPTY"
            for($i=0;$i<=6;$i++) {
                if ($hotelListSectionParts[$i] == '') {
                    $hotelListSectionParts[$i] = 'EMPTY';
                }
            }

            /**
             * Build a custom WHERE clause using the passed cmd parameter.
             */
            $WHERE = array();

            // country
            if ($hotelListSectionParts[0] != "EMPTY") {
                array_push($WHERE, sprintf("`i`.`fieldvalue` = '%s'", $hotelListSectionParts[0]));
            }
            // region
            if ($hotelListSectionParts[1] != "EMPTY") {
                array_push($WHERE, sprintf("`k`.`fieldvalue` = '%s'", $hotelListSectionParts[1]));
            }
            // city
            if ($hotelListSectionParts[2] != "EMPTY") {
                array_push($WHERE, sprintf("`g`.`fieldvalue` = '%s'", $hotelListSectionParts[2]));
            }
            //  interests
            if ($hotelListSectionParts[3] != "EMPTY") {
                $interestField = 'j.fieldvalue AS interest,';
                $interestJOIN = sprintf(" JOIN %s AS j ON ( hotel.id = j.hotel_id AND j.field_id = (SELECT field_id FROM %s WHERE name = '%s' AND lang_id = 1 AND j.fieldvalue > 0 ) AND j.lang_id = '%d')",
                                        DBPREFIX.'module_hotel_content', DBPREFIX.'module_hotel_fieldname',  $hotelListSectionParts[3], $this->frontLang);
            }

            /**
             * Build a custom ORDER BY clause using the passed cmd parameter.
             */
            if ($hotelListSectionParts[4] != "EMPTY") {
                $dynamicOrderBy = ' ORDER BY '. $hotelListSectionParts[4];

                if ($hotelListSectionParts[5] != "EMPTY") {
                    $dynamicOrderBy .= ' '.$hotelListSectionParts[5];
                }
            } else {
                $dynamicOrderBy = ' ORDER BY `prio`,`minprice` ASC';
            }
        }

        //  (SELECT `pdt` as minprice FROM `contrexx_module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as minprice,

        $query = ' SELECT SQL_CALC_FOUND_ROWS hotel.id AS hotel_id, visibility,
                    a.fieldvalue AS location,
                    /* CAST(b.fieldvalue AS UNSIGNED) AS price,
                    c.fieldvalue AS header,
                    d.fieldvalue AS headline,
                    e.fieldvalue AS rooms,
                    f.fieldvalue AS address, */
                    g.fieldvalue AS citycode,
                    h.fieldvalue AS category,
                    i.fieldvalue AS country,
                    '.$interestField.'
                    k.fieldvalue AS region,
                    l.fieldvalue AS hotel_id_link,
                    img.uri      AS imgsrc,
                    trv.pdt AS minprice,
                    trv.fl_from as fl_from,
                    trv.from as ts_from,
                    trv.to as ts_to,trv.prio as prio,trv.fl_id as fl_id,trv.fl_air as fl_air'.
//  '(SELECT `pdt` as minprice FROM `'.DBPREFIX.'module_hotel_travel` as trv WHERE trv.pdt IS NOT NULL AND hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as minprice,'
''
//  '(SELECT LEAST(min(`pdt`), min(`pd2wt`)) as minprice FROM `contrexx_module_hotel_travel` WHERE hotel_id_link = hotel_id LIMIT 1 ) as minprice,'
//                  (SELECT DISTINCT fl_from FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as fl_from,
//                  (SELECT DISTINCT ts_from FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as ts_from,
//                  (SELECT DISTINCT ts_to FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as ts_to

.
//                  '(SELECT DISTINCT prio FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as prio,
//                  (SELECT DISTINCT fl_id FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as fl_id,
//                  (SELECT DISTINCT fl_air FROM `'.DBPREFIX.'module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > '.mktime().' LIMIT 1 ) as fl_air
                    '
            FROM '.DBPREFIX.'module_hotel AS hotel
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS a ON ( hotel.id = a.hotel_id
                                                        AND a.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "ort"
                                                            AND lang_id = 1 )
                                                        AND a.lang_id = '.$this->frontLang.' )
         /* LEFT JOIN '.DBPREFIX.'module_hotel_content AS b ON ( hotel.id = b.hotel_id
                                                        AND b.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "preis"
                                                            AND lang_id = 1 )
                                                        AND b.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS c ON ( hotel.id = c.hotel_id
                                                        AND c.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "kopfzeile"
                                                            AND lang_id = 1 )
                                                        AND c.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS d ON ( hotel.id = d.hotel_id
                                                        AND d.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "headline"
                                                            AND lang_id = 1 )
                                                        AND d.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS e ON ( hotel.id = e.hotel_id
                                                        AND e.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "anzahl zimmer"
                                                            AND lang_id = 1 )
                                                        AND e.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS f ON ( hotel.id = f.hotel_id
                                                        AND f.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "adresse"
                                                            AND lang_id = 1 )
                                                        AND f.lang_id = '.$this->frontLang.' )
        */  LEFT JOIN '.DBPREFIX.'module_hotel_content AS g ON ( hotel.id = g.hotel_id
                                                        AND g.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "citycode"
                                                            AND lang_id = 1 )
                                                        AND g.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS h ON ( hotel.id = h.hotel_id
                                                        AND h.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "kategorie"
                                                            AND lang_id = 1 )
                                                        AND h.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS i ON ( hotel.id = i.hotel_id
                                                        AND i.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "destination"
                                                            AND lang_id = 1 )
                                                        AND i.lang_id = '.$this->frontLang.' )'
            // add the generated JOIN statement
            .$interestJOIN.

            'LEFT JOIN '.DBPREFIX.'module_hotel_content AS k ON ( hotel.id = k.hotel_id
                                                        AND k.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "region"
                                                            AND lang_id = 1 )
                                                        AND k.lang_id = '.$this->frontLang.' )
            LEFT JOIN '.DBPREFIX.'module_hotel_content AS l ON ( hotel.id = l.hotel_id
                                                        AND l.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name = "hotel_id"
                                                            AND lang_id = 1 )
                                                        AND l.lang_id = 1 )
            LEFT JOIN `'.DBPREFIX.'module_hotel_travel` AS trv on (trv.hotel_id = l.fieldvalue)
            LEFT JOIN '.DBPREFIX.'module_hotel_image AS img ON ( hotel.id = img.hotel_id
                                                        AND img.field_id = (
                                                            SELECT field_id
                                                            FROM '.DBPREFIX.'module_hotel_fieldname
                                                            WHERE name LIKE "%bersichtsbild"
                                                            AND lang_id = 1 )
                                                        )';
        //WHERE g.fieldvalue = "HRG"
        //ORDER BY `minprice` DESC ';

        // merge the WHERE parts
        if (count($WHERE) != 0) {
            $query .= ' WHERE 1=1 AND ' . implode(' AND ', $WHERE);
            $query .= " AND     `visibility` <> 'disabled' ";
        } else {
            $query .= " WHERE   `visibility` <> 'disabled' ";
        }

        $query .= '  AND trv.pdt IS NOT NULL AND trv.`from` > '.mktime();

        // add the ORDER BY part
        if ($dynamicOrderBy != "") {
            $query .= ' GROUP BY hotel.id '.$dynamicOrderBy;
        }

        return $query;
    }


    /**
     * shows the list of objects, also handles search requests
     *
     * @return void
     */
    function _showHotelList($showSpecialOffersOnly) {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        /**
         * Paging stuff
         * Normaly $limit is filled with the value from the config. But it is also possible, to add the limit parameter to the url.
         */
        $pos = intval($_GET['pos']);

        $hotelListSection = urldecode($_GET['cmd']);
        $hotelListSectionParts = explode('_', $hotelListSection);
        // the first value ist 'hotellistdynamic' throw this out of the array
        array_shift($hotelListSectionParts);
        (intval($hotelListSectionParts[6]) > 0) ? ($limit = intval($hotelListSectionParts[6])) : ($limit = $_CONFIG['corePagingLimit']);


        /**
         * Set some template stuff
         */
        $this->_objTpl->addBlockfile('HOTEL_LIST_CONTENT', 'hotel_list_content', 'modules/hotel/template/frontend_hotellist.html');

        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_BACK'                    => $_ARRAYLANG['TXT_HOTEL_BACK'],
            'TXT_HOTEL_CURRENCY_PREFIX'         => $this->arrSettings['currency_lang_'.$this->frontLang],
            'TXT_HOTEL_CURRENCY_SUFFIX'         => $this->_currencySuffix,
            'TXT_HOTEL_CURRENCY_PREFIX_EURO'    => $this->_currencyEuroPrefix,
            'TXT_HOTEL_CURRENCY_SUFFIX_EURO'    => $this->_currencyEuroSuffix,
            'TXT_HOTEL_MORE_INFOS'              => $_ARRAYLANG['TXT_HOTEL_MORE_INFOS'],
            'TXT_HOTEL_PRICE_ADDITION'          => $_ARRAYLANG['TXT_HOTEL_PRICE_ADDITION'],
            'TXT_HOTEL_PRICE_FROM'              => $_ARRAYLANG['TXT_HOTEL_PRICE_FROM'],
            'TXT_HOTEL_NEXT_DEPARTURE_DATE'     => $_ARRAYLANG['TXT_HOTEL_NEXT_DEPARTURE_DATE'],
        ));


        $this->_getFieldNames();

        $searchterm = contrexx_addslashes($_REQUEST['search']);
        if (!empty($searchterm) && strlen($searchterm) <= 3) {
            $this->_objTpl->setVariable("TXT_HOTEL_SEARCHTERM_TOO_SHORT", $_ARRAYLANG['TXT_HOTEL_SEARCHTERM_TOO_SHORT']);
              return false;
        } else {
            if ($this->_objTpl->blockExists('errorMsg')) {
                $this->_objTpl->hideBlock('errorMsg');
            }
        }


        /**
         * build the query
         */
        $query = $this->_buildQuery($showSpecialOffersOnly);


        /**
         * run the query
         */
        $objRS = $objDatabase->SelectLimit($query, $limit, $pos);


        /**
         * If no hotels matche the selected criterias we show a message
         */
        if ($objRS->RecordCount() < 1) {
            $this->_emptyHotelList = true;
            $this->_objTpl->setVariable(array('TXT_NO_MATCHES' => $_ARRAYLANG['TXT_NO_MATCHES']));
            $this->_objTpl->parse('nomatches');
            return false;
        }

        $objRSCount = $objDatabase->Execute('SELECT FOUND_ROWS() AS total');
        $count = $objRSCount->fields['total'];

        if ($objRS) {
            //DB result iteration loop
            while(!$objRS->EOF) {
                $this->_getFieldNames($objRS->fields['hotel_id']);
                //fetch meal info
                $meal = $this->_getMeal($this->_getFieldFromText('hotel_id'));
                !empty($_ARRAYLANG['TXT_HOTEL_MEAL_'.strtoupper($meal)])
                    ?   ($meal_txt = $_ARRAYLANG['TXT_HOTEL_MEAL_'.strtoupper($meal)])
                    :   ($meal_txt = $meal);


                $nextDepartureDate = $this->_getTravelDates($this->_getFieldFromText('hotel_id'), 1);

                if (empty($nextDepartureDate[0]['from_day'])) {
                    $nextDepartureDate[0]['from_day'] = $this->_weekdaysShort[$this->frontLang][date('w', $nextDepartureDate[0]['from'])];
                }
                if (empty($nextDepartureDate[0]['to_day'])) {
                    $nextDepartureDate[0]['to_day'] =  $this->_weekdaysShort[$this->frontLang][date('w', $nextDepartureDate[0]['to'])];
                }


                $nextDepartureDateFrom =  $nextDepartureDate[0]['from_day'].'. '.date('d.m.y', $nextDepartureDate[0]['from']);
                $nextDepartureDateTo =  $nextDepartureDate[0]['to_day'].'. '.date('d.m.y', $nextDepartureDate[0]['to']);


                $nextDepartureDate = "$nextDepartureDateFrom - $nextDepartureDateTo";
                //$lowestPrice = $this->_getLowestPrice();
                $lowestPrice = $objRS->fields['minprice'];
//              if (intval($lowestPrice) == 0) {
//                  $objRS->MoveNext();
//                  $count--;
//                  continue;
//              }
                $imgdim = '';
                $img = $objRS->fields['imgsrc'];
                $imgdim = $this->_getImageDim($img, 120);
                // Mr. Mostafa can define the airline of a flight
                $flightID = $objRS->fields['fl_id'];
                switch($flightID) {
                    case 0:
                        $airline = 'swiss';
                        break;
                    case 1:
                        $airline = 'hello';
                        break;
                    case 2:
                        $airline = 'egyptair';
                        break;
                    case 3:
                        $airline = 'edelweissair';
                        break;
                    case 4:
                        $airline = 'belair';
                        break;
                    case 5:
                        $airline = 'Air_m�diterrann�e';
                        break;
                    case 6:
                        $airline = 'Air_Memphis';
                        break;
                    case 7:
                        $airline = 'Air_Berlin';
                        break;
                    default:
                        $airline = 'swiss';
                        break;
                }
                $linkToFlightInfos = 'http://isis-swisstravel.ch/index.php?page=941#'.strtoupper($airline);
                $airlineLogo = $airline.'_logo.jpg';

                $this->_objTpl->setVariable(array(
                    'TXT_HOTEL_FLIGHT_WITH'         => !empty($objRS->fields['fl_from']) ? sprintf($_ARRAYLANG['TXT_HOTEL_FLIGHT_WITH'], $objRS->fields['fl_from']) : $_ARRAYLANG['TXT_HOTEL_FLIGHT_WITH_ONLY'],
                    'HOTEL_HEADER'                  => $objRS->fields['header'],
                    'HOTEL_LOCATION'                => $objRS->fields['location'] ,
                    'HOTEL_PRICE'                   => $lowestPrice,
                    'HOTEL_PRICE_EURO'              => number_format($lowestPrice / $this->_currencyEuroFactor, 0),
                    'HOTEL_HEADER_NOBR'             => $this->_getFieldFromText('destination')." - ".$this->_getFieldFromText('ort')." - ".$this->_getFieldFromText('hotel'),
                    'HOTEL_HEADER_BR'               => $this->_getFieldFromText('destination')." - ".$this->_getFieldFromText('ort')."<br /> ".$this->_getFieldFromText('hotel'),
                    'HOTEL_HIGHLIGHT'               => $this->_shortenString($this->_getFieldFromText('highlight'), 100),
                    'HOTEL_GENERAL'                 => $this->_getFieldFromText('allgemein'),
                    'HOTEL_STARS'                   => str_replace(',', '.', $this->_getFieldFromText('kategorie'))*$this->_categoryStarsWidth,
                    'HOTEL_REF_NR'                  => $objRS->fields['reference'],
                    'HOTEL_HEADLINE'                => $objRS->fields['headline'],
                    'HOTEL_IMG_PREVIEW_DIM'         => $imgdim[0],
                    'HOTEL_IMG_PREVIEW_SRC'         => $img,
                    'HOTEL_LOWEST_PRICE'            => $lowestPrice,
                    'HOTEL_LOWEST_PRICE_EURO'       => number_format($lowestPrice / $this->_currencyEuroFactor, 0),
                    'HOTEL_LINK_FLIGHT_INFOS'       => $linkToFlightInfos,
                    'HOTEL_LINK_FLIGHT_INFOS_LOGO'  => $airlineLogo,
                    'HOTEL_MEAL'                    => $meal_txt,
                    'HOTEL_NEXT_DEPARTURE_DATE'     => $nextDepartureDate,
                ));

                $this->_objTpl->setGlobalVariable(array(
                    'HOTEL_ID'                  => $objRS->fields['hotel_id'],
                ));

                $interestCat = contrexx_addslashes($_REQUEST['interest']);
                if (!empty($interestCat)) {
                    $hotels['cat'][0]['name']  = $this->_getFieldFromText($interestCat, 'names');
                    $hotels['cat'][0]['stars'] = $this->_getFieldFromText($interestCat);
                }

                // this loop sets the "interests" fields.

                /**
                 * If there was a "interest" passed by URL, we need to get sure, that this
                 * interest will be listet. Find the interests ID first..
                 */
                if ($hotelListSectionParts[3] != 'EMPTY') {
                    foreach(array_keys($this->_categoryFields) as $i) {
                        if (strtolower($this->_categoryFields[$i]) == strtolower($hotelListSectionParts[3])) {
                            $requestedInteresID = ($i);
                        }
                    }
                }
                /**
                 * .. now pick some interests and put them in the $hotels['cat'] array..
                 * (IMHO one should take those interests with the most starts... but this was not implemented by astalavista so we don't change it)
                 */

                $stars = "";
                $starvalues = array();
                $value = array();
                for($i = 0; $i < count($this->_categoryFields); $i++) {
                    if ($i == $requestedInteresID) {
                        continue;
                    } else {
                        if (!$this->_getFieldFromText($this->_categoryFields[$i], 'active') || $this->_getFieldFromText($this->_categoryFields[$i]) == '') {
                            continue;
                        }
                        $stars = str_replace(',', '.', $this->_getFieldFromText($this->_categoryFields[$i]));
                        if ($i <= $this->_categoryCountOverview) {
                            $starvalues[] = $stars;
                        }
                        $value[$stars][] = $this->_getFieldFromText($this->_categoryFields[$i], 'names');
                    }
                }

                /*
                 * unset the last array with the lowest stars and replace it with the interest category, if there was an interest
                 */
                if (isset($requestedInteresID)) {
                    unset($value[min($starvalues)]);
                    $stars = str_replace(',', '.', $this->_getFieldFromText($this->_categoryFields[$requestedInteresID]));
                    $value[$stars][] = $this->_getFieldFromText($this->_categoryFields[$requestedInteresID], 'names');
                }


            /*
                echo "<pre>";
                print_r($hotels['cat']);
                echo "</pre>";
                die('wait');
            */

                $catcount = 0;
                while(!empty($value) && $catcount !== $this->_categoryCountOverview) {
                    $keys=array_keys($value);
                    $biggest = max($keys);
                    if (empty($value[$biggest])) {
                        unset($value[$biggest]);
                        continue;
                    }
                    $name  = array_shift($value[$biggest]);
                    $stars = $biggest;
                    $this->_objTpl->setVariable(array(
                        'HOTEL_CATEGORY'        => $name,
                        'HOTEL_STARS_WIDTH'     => $stars * $this->_categoryStarsWidth,
                        'HOTEL_STARS_MARGIN'    => $this->_categoryMargin - $stars*$this->_categoryStarsWidth-3,
                    ));
                    $this->_objTpl->parse('categories');
                    $catcount++;
                }

                //fill up empty star fields, thanks IE6
                for($j=$catcount;$j<6;$j++) {
                    $this->_objTpl->setVariable(array(
                        'HOTEL_CATEGORY'        => '&nbsp;',
                        'HOTEL_STARS_WIDTH'     => 0,
                        'HOTEL_STARS_MARGIN'    => '-9999',
                    ));
                    $this->_objTpl->parse('categories');
                }


                if (!empty($objRS->fields['imgsrc'])) {
                    $this->_objTpl->parse("previewImage");
                } else {
                    $this->_objTpl->hideBlock("previewImage");
                }
                $this->_objTpl->setVariable('HOTEL_HEADER', $objRS->fields['header']);
                $this->_objTpl->parse("objectRow");
                $objRS->MoveNext();
            }


            // split pages
            if ($count > $limit) {
                $querystring = preg_replace('#pos=[0-9]+&#', '', $_SERVER['QUERY_STRING']);

                $flightFrom = contrexx_addslashes(strip_tags(($_REQUEST['fl_from'])));
                $departureDate = contrexx_addslashes(strip_tags(($_REQUEST['departure_date'])));
                $returnDate = contrexx_addslashes(strip_tags(($_REQUEST['return_date'])));
                $citycode = contrexx_addslashes(strip_tags(($_REQUEST['city'])));
                $category = intval($_REQUEST['hotelcategory']);
                $tprice = contrexx_addslashes(strip_tags(($_REQUEST['tprice'])));
                $meal = contrexx_addslashes(strip_tags(($_REQUEST['meal'])));
                $searchterm = contrexx_addslashes($_REQUEST['search']);

                $hotelListQuery =       '&amp;fl_from='.$flightFrom.'&amp;departure_date='.$departureDate.'&amp;return_date='.$returnDate.'&amp;city='.$citycode.
                                        '&amp;hotelcategory='.$category.'&amp;tprice='.$tprice.
                                        '&amp;meal='.$meal.'&amp;search='.$searchterm;
                $GLOBALS['hotelListQuery'] = $hotelListQuery;

                $querystring .= $hotelListQuery;
                $querystring = str_replace('&cmd', '&amp;cmd', $querystring);
                $this->_objTpl->setVariable('HOTEL_PAGING', getPaging($count, $pos, '&amp;'.$querystring, '', true, $limit));
            }
            //$this->_objTpl->parse('hotel_list_content');
        } else {
            echo "DB error. file: ".__FILE__." line: ".__LINE__;
        }
        return true;
    }


    /**
     * return the dimensions of an image to fit the content (resized if neccessary)
     *
     * @param string $img path to the image
     * @param int $max maximum acceptable size of the image (height or width, whichever is bigger)
     * @return array containing the style string, the width and the height
     */
    function _getImageDim($img, $max = 60)
    {
        if ($img != '') {
            $size       = getimagesize(ASCMS_DOCUMENT_ROOT.$img);
            $height     = '';
            $width      = '';
            $height = $size[1];
            $width = $size[0];
            if ($height > $max && $height > $width)
            {
                $height = $max;
                $percent = ($size[1] / $height);
                $width = ($size[0] / $percent);
            }
            else if ($width > $max)
            {
                $width = $max;
                $percent = ($size[0] / $width);
                $height = ($size[1] / $percent);
            }
            if ($width > 0 && $height > 0) {
                $imgdim[0] = 'style="height: '.$height.'px; width:'.$width.'px;"';
                $imgdim[1] = $size[0]+20;
                $imgdim[2] = $size[1]+20;
            }
            return $imgdim;
        }
        return '';
    }


    /**
     * Shows the map
     *
     * @param int $highlight Id of the Object to be highlighted
     * @return void
     */
    function _showMap()
    {
        global $objDatabase, $_ARRAYLANG;

        error_reporting(0);
        $this->_objTpl->loadTemplateFile("modules/hotel/template/frontend_map_template.html");

        // Check if something has to be highlighted
        $highlight = (isset($_GET['highlight'])) ? intval($_GET['highlight']) : 0;

        // Extract all Placeholders out of the message
        $subQueryPart = "";
        $first = true;
        $matches = array();
        //preg_match_all("/%([A-Z0-9���_]+[^%])%/", $this->arrSettings['message'], $matches);
        preg_match_all('/%([^%]+)%/', $this->arrSettings['message'], $matches);
        setlocale(LC_ALL, "de_CH");
        foreach ($matches[1] as $match) {
            if ($first) {
                $first = false;
            } else {
                $subQueryPart .= " OR ";
            }
            $subQueryPart .= "lower(name) = '".strtolower($match)."'";
        }

        // Get All the hotel objects
        $query = " SELECT hotel.id as `id`,
                        hotel.reference AS `ref` ,
                        hotel.visibility,
                        hotel.object_type AS otype,
                        hotel.new_building AS `new` ,
                        hotel.property_type AS ptype,
                        hotel.longitude as `long`,
                        hotel.latitude as `lat`,
                        hotel.zoom as `zoom`,
                        l.fieldvalue AS hotel_id_link,
                        (SELECT `pdt` as minprice FROM `".DBPREFIX."module_hotel_travel` WHERE hotel_id_link = hotel_id AND `from` > ".mktime()." LIMIT 1) as minprice,
                        hotel.logo as `logo`
                    FROM ".DBPREFIX."module_hotel AS hotel
                    LEFT JOIN ".DBPREFIX."module_hotel_content AS l ON ( hotel.id = l.hotel_id
                                                        AND l.field_id = (
                                                            SELECT field_id
                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                            WHERE name = 'hotel_id'
                                                            AND lang_id = 1 )
                                                        AND l.lang_id = 1 )
                    WHERE hotel.visibility != 'disabled'";
        if ($highlight > 0) {
            $query .= " AND hotel.id = $highlight";
        }
        //die($query);
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            $keyCounter = 0;
            while (!$objResult->EOF) {
                unset($data);
                /*
                    This is the one we want to highlight. So we scroll to it
                */
                if ($objResult->fields['id'] == $highlight) {
                    $startX = $objResult->fields['long'];
                    $startY = $objResult->fields['lat'];
                    $startZoom = $objResult->fields['zoom'];
                }

                $query = "SELECT `hotel_id`, `fieldvalue` FROM `".DBPREFIX."module_hotel_content` WHERE `field_id` = (SELECT `field_id` FROM `".DBPREFIX."module_hotel_fieldname` WHERE `name` = 'hotel_id' LIMIT 1)";
                $objRS = $objDatabase->Execute($query);
                while(!$objRS->EOF) {
                    $arrHotelIDs[$objRS->fields['hotel_id']] = $objRS->fields['fieldvalue'];
                    $objRS->MoveNext();
                }

//              $this->_getFieldNames($objResult->fields['id'], 0, true);

                $data = array(
                    'reference'     => $objResult->fields['ref'],
                    'object_type'   => $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE'.strtoupper($objResult->fields['object_type'])],
                    'new_building'  => ($objResult->fields['new_building']) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                    'property_type' => $_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE'.strtoupper($objResult->fields['property_type'])],
                    'longitude'     => $objResult->fields['longitude'],
                    'latitude'      => $objResult->fields['latitude']
                );

                $query = "  SELECT content.field_id AS `field_id` ,
                                fieldnames.name AS `field_name` ,
                                fieldvalue AS `value` ,
                                image.uri AS `uri` ,
                                field.type AS `type`
                            FROM ".DBPREFIX."module_hotel_content AS `content`
                            INNER JOIN ".DBPREFIX."module_hotel_fieldname AS `fieldnames` ON fieldnames.field_id = content.field_id
                            AND fieldnames.lang_id = '".$this->frontLang."'
                            AND content.lang_id = '".$this->frontLang."'
                            AND fieldnames.field_id
                            IN (
                                SELECT field_id
                                FROM `".DBPREFIX."module_hotel_fieldname` AS fieldn
                                WHERE
                                ".$subQueryPart."
                            )
                            AND content.hotel_id ='".$objResult->fields['id']."'
                            LEFT OUTER JOIN ".DBPREFIX."module_hotel_image AS `image` ON image.field_id = content.field_id
                            AND image.hotel_id = '".$objResult->fields['id']."'
                            LEFT OUTER JOIN ".DBPREFIX."module_hotel_field AS `field` ON content.field_id = field.id";
                //die($query);
                $objResult2 = $objDatabase->Execute($query);
                while (!$objResult2->EOF) {
                    $data[strtolower($objResult2->fields['field_name'])] = ($objResult2->fields['type'] == "img" || $objResult2->fields['type'] == "panorama") ? ((!empty($objResult2->fields['uri'])) ? $objResult2->fields['uri'] : "admin/images/icons/pixel.gif") : $objResult2->fields['value'];
                    $objResult2->MoveNext();
                }

                /*
                 * Now replace the placeholder in the message with the date (if provided)
                 */
                $message = $this->arrSettings['message'];
                foreach ($matches[1] as $match) {
                    $minprice = $objResult->fields['minprice'];
                    if ($match == 'PRICE') {
                        //$toReplace = $this->_getLowestPrice($this->_getFieldFromText('hotel_id'));
                        $toReplace = $minprice;
                    } elseif ($match == 'PRICE_EURO') {
                        $toReplace = number_format($minprice / $this->_currencyEuroFactor, 0);
                    } elseif ($match == 'HOTEL_ID') {
                        $toReplace = $objResult->fields['id'];
                    } else { //no more special cases, check for available fieldname and return the fieldcontent, empty string otherwise.
                        $toReplace = (isset($data[strtolower($match)])) ? $data[strtolower($match)] : "";
                    }
                    $message = str_replace('%'.$match.'%', $this->_shortenString( htmlspecialchars($toReplace, ENT_QUOTES, CONTREXX_CHARSET) , 200), $message);

                    // Line breaks are evil
                    $message = str_replace("\r", "<br />", $message);
                    $message = str_replace("\n", "<br />", $message);
                }

                $meal = $this->_getMeal($arrHotelIDs[$objResult->fields['id']]);
                !empty($_ARRAYLANG['TXT_HOTEL_MEAL_'.strtoupper($meal)])
                    ?   ($meal_txt = $_ARRAYLANG['TXT_HOTEL_MEAL_'.strtoupper($meal)])
                    :   ($meal_txt = $meal);
                $message = str_replace( array(  '[[HOTEL_MEAL]]',
                                                '[[TXT_HOTEL_PRICE_WEEK]]',
                                                '[[TXT_HOTEL_MEAL]]',
                                                '[[HOTEL_ID]]'),
                                        array(  $meal_txt,
                                                $_ARRAYLANG['TXT_HOTEL_PRICE_WEEK'],
                                                $_ARRAYLANG['TXT_HOTEL_MEAL'],
                                                $objResult->fields['id']),
                                        $message);

                $this->_objTpl->setVariable(array(
                    'HOTEL_KEY_NUMBER'          => $keyCounter,
                    'HOTEL_MARKER_LAT'          => $objResult->fields['lat'],
                    'HOTEL_MARKER_LONG'         => $objResult->fields['long'],
                    'HOTEL_MARKER_MSG'          => $message,
                    'HOTEL_MARKER_ID'           => $objResult->fields['id'],
                    'HOTEL_MARKER_HIGHLIGHT'    => ($objResult->fields['id'] == $highlight) ? 1 : 0,
                    'HOTEL_MARKER_LOGO'         => $objResult->fields['logo']
                ));

                $this->_objTpl->parse("setmarker");
                $keyCounter++;
                $objResult->MoveNext();
            }
        }

        /*
            Nothing is highlighted. set startpoint of the specified country
        */
        if (!$highlight || !isset($startX)) {
            $startX = $this->_googleMapCoordinates[$_REQUEST['country']]['lat'];
            $startY = $this->_googleMapCoordinates[$_REQUEST['country']]['lon'];
            $startZoom = $this->_googleMapCoordinates[$_REQUEST['country']]['zoom'];
        }

        $startX = empty($startX) ? $this->arrSettings['lat_frontend'] : $startX;
        $startY = empty($startY) ? $this->arrSettings['lon_frontend'] : $startY;
        $startZoom = empty($startZoom) ? $this->arrSettings['zoom_frontend'] : $startZoom;

        $this->_objTpl->setVariable(array(
            'HOTEL_GOOGLE_API_KEY'       => $this->arrSettings['GOOGLE_API_KEY_'.$_SERVER['SERVER_NAME']],
            'HOTEL_START_X'              => $startX,
            'HOTEL_START_Y'              => $startY,
            'HOTEL_START_ZOOM'           => $startZoom,
            'HOTEL_LANG'                 => $this->frontLang,
            'HOTEL_TXT_LOOK'             => $_ARRAYLANG['TXT_HOTEL_LOOK']
        ));

        $this->_objTpl->show();
        die;
    }

    /**
     * return javascript for search form
     *
     * @return string javascript
     */
    function _getHotelJS() {
        return <<< EOF
    var swapSearch = function() {
        document.getElementById("advanced_search").style.display = document.getElementById("advanced_search").style.display == 'none' ? 'block'  : 'none';
        document.getElementById("simple_search").style.display   = document.getElementById("simple_search").style.display == 'none' ? 'block'  : 'none';
    }
EOF;
    }

    /**
     * Picture swap javascript
     *
     * @return string javascript
     */
    function _getPictureSwapJS($hotelID, $fieldID, $count) {
        return <<< EOF
    var imgPic;
    var imageurl;
    var wintitle;
    var winPic;
    var count = $count;
    var divHeight   = 200;
    var divWidth    = 250;
    var thumbWidth  = 78;
    var thumbHeight = 62;
    var top;
    var busy = false;
    var imgs;
    var loaded = false;
    var link;

    var hotelID = $hotelID;
    var fieldID = $fieldID;


    function setFuncs() {
        base            = /(http:\\/\\/[^\\/]+\\/)/.exec(location);
        base            = base[0].substring(0,base[0].length-1);
        link            = document.getElementById('mainPicLink');
        imgs            = document.getElementsByName('imgthumb');
        var MainImgEl   = document.getElementById("picturebox_main");
        for(i=0 ; i<imgs.count ; i++) {
            imgs[i].onclick = function() { swapImage(this, MainImgEl, i) };
        }
        loaded = true;
    }

    function swapImage(clickedImg, mainImg, i, width, height) {
        if (busy || !loaded)
        {
            return false;
        }
        busy        = true; //lock this function while working

        tmpsrc = mainImg.src;
        tmpalt = mainImg.alt;
        tmptitle = mainImg.title;

        link.href = clickedImg.src;
        link.title = clickedImg.alt;

        mainImg.src = clickedImg.src;
        mainImg.alt = clickedImg.alt;
        mainImg.title = clickedImg.title;
        clickedImg.src = tmpsrc;
        clickedImg.alt = tmpalt;
        clickedImg.title = tmptitle;



        busy=false;
        return true;

        mainImg.src     = clickedImg.src;
        mainImg.alt     = clickedImg.alt;


    }

    function selectTab(tabName)
    {
     if (document.getElementById(tabName).style.display != "block")
     {
         document.getElementById(tabName).style.display = "block";
         strClass = document.getElementById(tabName).className;
         document.getElementById(strClass+"_"+tabName).className = "active";

         arrTags = document.getElementsByTagName("*");
         for (i=0;i<arrTags.length;i++)
             {
             if (arrTags[i].className == strClass && arrTags[i] != document.getElementById(tabName))
             {
                 arrTags[i].style.display = "none";
                 if (document.getElementById(strClass+"_"+arrTags[i].getAttribute("id"))) {
                    document.getElementById(strClass+"_"+arrTags[i].getAttribute("id")).className = "";
                 }
             }
         }
     }
     try{
     foo = document.createElement('input');
     foo.type='hidden';
     document.body.appendChild(foo);
     foo.focus();
     foo.parentNode.removeChild(foo);
     }catch(e) {/*IE hassle goes here*/}
    }

    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
        window.onload = setFuncs;
    }
    else {
        window.onload = function() {
            oldonload();
            setFuncs();
        }
    }

EOF;
    }


    /**
     * return javascript for the details page
     *
     * @return string javascript
     */
    function _getDetailsJS() {
        return <<< EOF
    var mapHeight = 510;
    var mapWidth = 540;
    var imgPrevHeight = 1000;
    var imgPrevWidth = 1000;


    var openMap = function(id) {
        try{
            if (! popUp.closed) {
                return popUp.focus();
            }
        }catch(e) {}

        url='http://isis-swisstravel.ch/?section=hotel&standalone=1&highlight='+id;
        if (!window.focus) {
            return true;
        }
        try{
            popUp = window.open(url, 'Map', 'width='+mapWidth+',height='+mapHeight+',scrollbars=no');
            popUp.focus();
            return false;
        }catch(e) {return false;}
    }

    var openPreview = function() {
        try{
            if (! imgPopUp.closed) {
                imgPopUp.index = parseInt(imageIndex);
                imgPopUp.showImage();
                imgPopUp.focus();
                return;
            }
        }catch(e) {}
        if (!window.focus) {
            return true;
        }
        imgPopUp = window.open('?section=hotel&img=1&id='+hotelID+'&index='+fieldID, '', 'width='+imgPrevWidth+',height='+imgPrevHeight+',scrollbars=yes');

        imgPopUp.focus();
        imgPopUp.moveTo(0,0);
        return false;
    }
EOF;
    }

    /**
     * shortens a string to a maximum length
     *
     * @param string $str original string
     * @param integer $maxLength maximum length
     * @return string $str shortened string
     */

    function _shortenString($str, $maxLength) {
        if (strlen($str) > $maxLength) {
            return substr($str, 0, $maxLength-3).'[...]';
        }
        return $str;
    }
}

?>
