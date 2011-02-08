<?php

/**
* Hotel module
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

require_once(ASCMS_FRAMEWORK_PATH."/File.class.php");
include(dirname(__FILE__).'/HotelLib.class.php');

class HotelManager extends HotelLib {
    /**
    * Template object
    *
    * @access private
    * @var object
    */
    public $_objTpl;

    /**
    * Page title
    *
    * @access private
    * @var string
    */
    public $_pageTitle;

    /**
    * Status messages
    *
    * @access private
    * @var string
    */
    public $_strOkMessage = '';
    public $_strErrMessage = '';


    public $_defaultImage = 'images/icons/images.gif';

    /**
     * field adjustment list for import from CSV
     *
     * @access private
     * @var array CSV fields
     */
    public $_arrTravelImportFieldsTranslationTable = array(
        'id'         =>         0, //id uses auto_increment, not needed
        'hotel_id'    =>        0,
        'prio'        =>         1,
        'from_day'    =>      2,
        'from'        =>      3,
        'to_day'     =>         4,
        'to'         =>         5,
        'p_id'         =>        6,
        'fl_id'        =>          7,
        'fl_air'    =>          8,
        'fl_from'    =>          9,
        'l_code'    =>          10,
        'meal'        =>         11,
        'pst'        =>         12,
        'pdt'        =>         13,
        'ptt'        =>         14,
        'ps2wt'        =>         15,
        'pd2wt'        =>         16,
        'pt2wt'        =>         17,
        'spez1'        =>         18,
        'spez2'        =>         19,
        'spez3'        =>         20,
        'spez4'        =>         21,
        'spez5'        =>         22,
        'spez6'        =>         23,
        'spez7'        =>         24,
    );

    /**
    * PHP5 constructor
    *
    * @global object $objTemplate
    * @global array $_ARRAYLANG
    */
    function __construct()
    {
        global $objTemplate, $_ARRAYLANG;
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotel/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable("CONTENT_NAVIGATION", "
            <a href='?cmd=hotel'>".$_ARRAYLANG['TXT_HOTEL_OVERVIEW']."</a>
            <a href='?cmd=hotel&amp;act=add'>".$_ARRAYLANG['TXT_HOTEL_ADD']."</a>
            <a href='?cmd=hotel&amp;act=interests'>Buchungen</a>
            <a href='?cmd=hotel&amp;act=travel'>".$_ARRAYLANG['TXT_HOTEL_TRAVEL']."</a>
            <a href='?cmd=hotel&amp;act=settings'>".$_ARRAYLANG['TXT_HOTEL_SETTINGS']."</a>"

        );

        $this->_objFile = new File();
        parent::__construct();
    }

    /**
    * Set the backend page
    *
    * @access public
    * @global object $objTemplate
    * @global array $_ARRAYLANG
    */
    function getPage()
    {
        global $objTemplate;

        if (!isset($_GET['act'])) {
            $_GET['act']="";
        }

        switch($_GET['act']) {
            case 'ziptest':
                $this->_zipTest();
                break;
            case 'debug':
                print_r($_SESSION['hotel']);
                unset($_SESSION['hotel']);
                print_r($_SESSION['hotel']);
                break;
            case 'travel':
                $this->_travelMain();
                break;
            case 'add':
                $this->_add();
                $this->_showHotelForm();
                break;
            case 'del':
                $this->_del();
                $this->_showOverview();
                break;
            case 'delimages':
                $this->_deleteUnusedImages();
                $this->_showSettings();
                break;
            case 'copy':
                $this->_copy();
                $this->_showOverview();
                break;
            case 'interests':
                $this->_interests();
                break;
            case 'contactdetails':
                $this->_showContactDetails();
                break;
            case 'interestdetails':
                $this->_showInterestDetails();
                break;
            case 'rpcov':
                $this->_RPCOverview();
                break;
            case 'rpc':
                $this->_RPCGetSuggest();
                break;
            case 'rpcr':
                $this->_RPCSort();
                break;
            case 'rpcs':
                $this->_RPCSearch();
                break;
            case 'rpcsisort':
                $this->_RPCSortInterests();
                break;
            case 'rpcsi':
                $this->_RPCSearchInterests();
                break;
            case 'rpcdl':
                $this->_RPCDownloadStats();
                break;
            case 'stats':
                $this->_showStats();
                    break;
                case 'downloads':
                $this->_showDownloads();
                    break;
            case 'export':
                $this->_exportContacts();
                    break;
            case 'modhotel':
                $this->_modhotel();
                break;
            case 'saveSettings':
                $this->_saveSettings();
                $this->_getSettings();
            case 'settings':
                $this->_showSettings();
                    break;
            case 'map':
                $this->_showMapPopup();
                exit;
                    break;
            case 'addfields':
                $this->_addfields();
                break;
            case 'modfields':
                $this->_modfields();
                break;
            case 'addlanguage':
                $this->_addlanguage();
                break;
            case 'delfield':
                $this->_delfield();
                break;
            default:
                $this->_showOverview();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                => $this->_pageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'                => $this->_objTpl->get()
        ));
    }

    function _zipTest(){
        require_once(ASCMS_LIBRARY_PATH.'/pclzip/pclzip.lib.php');
        error_reporting(E_ALL);ini_set('display_errors',1);
        $pcl = new PclZip(ASCMS_PATH.'/1July2007.zip');
        print_r($pcl->listContent());
    }



    /**
     * travel main page
     *
     * @return void
     */
    function _travelMain()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('module_hotel_travel.html');
        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_TRAVEL_OVERVIEW' =>    $_ARRAYLANG['TXT_HOTEL_TRAVEL_OVERVIEW'],
            'TXT_HOTEL_TRAVEL_IMPORT'     =>    $_ARRAYLANG['TXT_HOTEL_TRAVEL_IMPORT'],
            'TXT_HOTEL_CSV_EXAMPLE'     =>    $_ARRAYLANG['TXT_HOTEL_CSV_EXAMPLE'],
        ));

        $_REQUEST['tpl'] = !empty($_REQUEST['tpl']) ? $_REQUEST['tpl'] : '';

        switch ($_REQUEST['tpl']){
            case 'import':
                $this->_travelImportData();
                $this->_showTravelImport();
                break;
            default:
                $this->_showTravelData();
        }
    }

    /**
     * shows the travel data
     *
     * @return bool
     */
    function _showTravelData(){
        global $_ARRAYLANG, $objDatabase;

        $pos = !empty($_GET['pos']) ? intval($_GET['pos']) : 0;
        $limit = !empty($_GET['limit']) ? intval($_GET['limit']) : 100;

        $this->_objTpl->addBlockfile('HOTEL_TRAVEL_FILE', 'module_hotel_travel_overview', 'module_hotel_travel_overview.html');
        $this->_objTpl->setVariable(array(
            'TXT_HOTEL_TRAVEL_OVERVIEW'     =>    $_ARRAYLANG['TXT_HOTEL_TRAVEL_OVERVIEW'],
        ));

        $arrColumns = array_map('strtolower', array_keys($objDatabase->MetaColumns(DBPREFIX."module_hotel_travel")));
        $colspan = 1;
        foreach ($arrColumns as $col) {
            $colspan++;
            $this->_objTpl->setVariable('HOTEL_TRAVEL_FIELDS', $col);
            $this->_objTpl->parse('travelFields');
        }

        $this->_objTpl->setVariable('HOTEL_TRAVEL_OVERVIEW_COLSPAN', $colspan);

        $strSQLColumns = '';
        foreach ($arrColumns as $col) {
            $strSQLColumns .= ', `'.strtolower($col).'`';
        }
        $strSQLColumns = substr($strSQLColumns, 1);


        $query = "    SELECT SQL_CALC_FOUND_ROWS ".$strSQLColumns." FROM `".DBPREFIX."module_hotel_travel` ORDER BY `from`";
        if(($objRS = $objDatabase->SelectLimit($query, $limit, $pos)) === false){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
            return false;
        }
        $row = 0;
        while(!$objRS->EOF){
            $strRow = '';
            foreach ($objRS->fields as $key => $field) {
                if($key == 'from' || $key == 'to'){
                    $strRow .= '<td>'.date('d.m.Y', $field).'</td>';
                }else{
                    $strRow .= '<td>'.$field.'</td>';
                }
            }
            $this->_objTpl->setVariable(array(
                'HOTEL_TRAVEL_ROWCLASS'     => $row++ % 2 + 1,
                'HOTEL_TRAVEL_DATA'        => $strRow."\n",
            ));
            $this->_objTpl->parse('travelData');
            $objRS->MoveNext();
        }

        $objRSCount = $objDatabase->Execute("SELECT FOUND_ROWS() AS `count`");
        $count = $objRSCount->fields['count'];

        $this->_objTpl->setVariable('HOTEL_TRAVEL_PAGING', getPaging($count, $pos, '&amp;cmd=hotel&amp;act=travel&amp;limit='.$limit, '', true, $limit));

        $this->_objTpl->parse('module_hotel_travel_overview');
        return true;
    }

    /**
     * show the traveldata import page
     *
     * @return void
     */
    function _showTravelImport(){
        global $_ARRAYLANG;
        $this->_objTpl->addBlockfile('HOTEL_TRAVEL_FILE', 'module_hotel_travel_import', 'module_hotel_travel_import.html');
        $this->_objTpl->setVariable(array(
            'TXT_HOTEL_TRAVEL_IMPORT'     =>    $_ARRAYLANG['TXT_HOTEL_TRAVEL_IMPORT'],
        ));

        $this->_objTpl->parse('module_hotel_travel_import');

    }


    /**
     * strip quotes callback function
     *
     * @param string $a
     * @return string
     */
    function stripquotes($a){
        return str_replace('"', '', $a);
    }



    /**
     * extract data from CSV file and import into the travel table
     *
     * @return bool
     */
    function _travelImportData()
    {
        global $objDatabase, $_ARRAYLANG;

        if(!isset($_REQUEST['hotel_travel_import_submit'])){
            return false;
        }

        $query = "    DELETE FROM `".DBPREFIX."module_hotel_travel_backup`";
        if($objDatabase->Execute($query) === false){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
            return false;
        }

        $query = "    INSERT INTO `".DBPREFIX."module_hotel_travel_backup` (SELECT * FROM `".DBPREFIX."module_hotel_travel`)";
        if($objDatabase->Execute($query) === false){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
            return false;
        }

        $query = "    DELETE FROM `".DBPREFIX."module_hotel_travel`";
        if($objDatabase->Execute($query) === false){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
            return false;
        }

        $strSeparator = !empty($_GET['sep']) ? $_GET['sep'][0] : ';';

        require_once(ASCMS_LIBRARY_PATH.'/pclzip/pclzip.lib.php');
        $pcl = new PclZip($_FILES['hotel_travel_import_file']['tmp_name']);
        $arrContent = $pcl->listContent();
        $compressedFilename = $arrContent[0]['stored_filename'];
        if(file_exists(ASCMS_TEMP_PATH.'/'.$compressedFilename)){
            unlink(ASCMS_TEMP_PATH.'/'.$compressedFilename);
        }

        if($pcl->extract(    PCLZIP_OPT_PATH, ASCMS_TEMP_PATH.'/hotel_cvs_import/',
                            PCLZIP_OPT_BY_NAME, $compressedFilename) == 0){
// Undefined
//            $this->_strErrMessage = "Error : ".$archive->errorInfo(true);
            return false;
        }

        $arrTravelDataRows = file(ASCMS_TEMP_PATH.'/hotel_cvs_import/'.$compressedFilename);
        foreach(glob(ASCMS_TEMP_PATH.'/hotel_cvs_import/*') as $CSVFile){
            unlink($CSVFile);
        }

        if(!empty($arrTravelDataRows)){
            array_shift($arrTravelDataRows); //drop the first line (field names)

            foreach($arrTravelDataRows as $strCVSLine){
                $t = $this->_arrTravelImportFieldsTranslationTable;
                $d = explode($strSeparator, $strCVSLine);
                $d = array_map(array(&$this,'stripquotes'), $d);

                $arrDateFrom         = explode('.', $d[$t['from']]);
                $d[$t['from']]        = mktime(0, 0, 0, $arrDateFrom[1], $arrDateFrom[0], $arrDateFrom[2]);

                $arrDateTo             = explode('.', $d[$t['to']]);
                $d[$t['to']]        = mktime(0, 0, 0, $arrDateTo[1],   $arrDateTo[0],     $arrDateTo[2]);

                // $isisCode is a special construct using country- hotel and PID
                $isisCode = $d[$t['l_code']]."_".$d[$t['hotel_id']]."_".$d[$t['p_id']];

                $d[$t['prio']] = !empty($d[$t['prio']]) ? $d[$t['prio']] : 0;

                foreach ($d as $k => $v) {
                    if(empty($v)){
                        $d[$k] = '0';
                    }
                }

//                $d[$t['visible']]    = $d[$t['visible']] == 'WAHR' ? 1 : 0;
                $query = "    INSERT INTO `".DBPREFIX."module_hotel_travel` VALUES (NULL, '".$isisCode."',         ".$d[$t['hotel_id']].",".$d[$t['prio']].",  '".$d[$t['from_day']]."',".$d[$t['from']].",
                                                                                        '".$d[$t['to_day']]."', ".$d[$t['to']].",        ".$d[$t['p_id']].",     ".$d[$t['fl_id']].",   '".$d[$t['fl_air']]."',
                                                                                        '".$d[$t['fl_from']]."','".$d[$t['l_code']]."','".$d[$t['meal']]."', ".$d[$t['pst']].",         ".$d[$t['pdt']].",
                                                                                         ".$d[$t['ptt']].",     ".$d[$t['ps2wt']].",   ".$d[$t['pd2wt']].",  ".$d[$t['pt2wt']].",   '".$d[$t['spez1']]."',
                                                                                         '".$d[$t['spez2']]."', '".$d[$t['spez3']]."', '".$d[$t['spez4']]."','".$d[$t['spez5']]."',  '".$d[$t['spez6']]."',
                                                                                         '".$d[$t['spez7']]."')";
                if($objDatabase->Execute($query) === false){
                    $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
                    $query = "    INSERT INTO `".DBPREFIX."module_hotel_travel` (SELECT * FROM `".DBPREFIX."module_hotel_travel_backup`)";
                    if($objDatabase->Execute($query) === false){
                        $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
                        return false;
                    }
                    return false;
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_TRAVEL_IMPORTED_SUCCESSFULLY'];
        }else{
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_TRAVEL_IMPORT_NO_OR_INVALID_FILE'];
        }
        return true;
    }

    /**
     * remote scripting interest list sort
     *
     * @return JSON object
     */
    function _RPCSortInterests(){
        global $_CONFIG, $objDatabase;
        $fieldValues = array('hotel_id', 'name', 'firstname', 'street', 'zip', 'location', 'telephone', 'comment', 'time');
// Unused
//        $hotelID = !empty($_REQUEST['hotelid']) ? intval($_REQUEST['hotelid']) : 0;
        $field = (!empty($_GET['field'])) ? contrexx_addslashes($_GET['field']) : 'time';
        $order = (!empty($_GET['order'])) ? contrexx_addslashes($_GET['order']) : 'asc';
        $limit = (!empty($_GET['limit'])) ? intval($_GET['limit']) : $_CONFIG['corePagingLimit'];
        if(!in_array($field, $fieldValues) && ( $order != 'asc' || $order != 'desc' )){
            die();
        }

        $searchTerm = (!empty($_REQUEST['search']) && !empty($_REQUEST['searchField'])) ? " LIKE '%".contrexx_addslashes($_REQUEST['search'])."%'" : ' TRUE';
        $searchField = (!empty($_REQUEST['searchField']) && !empty($_REQUEST['search'])) ? ' WHERE '.contrexx_addslashes($_REQUEST['searchField']) : ' WHERE ';

        $query = "    SELECT     `interest`.`id` as contact_id , `email` , `name` , `firstname` , `street` , `zip` , `location` ,
                            `phone_home`, `comment` , `interest`.`hotel_id` , `time`, content1.fieldvalue AS hotel_header, content2.fieldvalue AS hotel_address, content3.fieldvalue AS hotel_location
                    FROM `".DBPREFIX."module_hotel_interest` AS interest
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = interest.hotel_id
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'headline'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = interest.hotel_id
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'adresse'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content3 ON content3.hotel_id = interest.hotel_id
                        AND content3.lang_id =1
                        AND content3.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )"
                    .$searchField." ".$searchTerm;
//                    if($_REQUEST['ignore_timespan'] !== 'on' && !empty($_SESSION['hotel']['startDate'])){
//                        $query .= " AND `time` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
//                    }
//                    if($hotelID > 0){
//                        $query .= " AND interest.hotel_id = $hotelID";
//                    }
                    $query .= " ORDER BY ".$field." ".$order;

        $objRS = $objDatabase->SelectLimit($query, $limit);
        $limit = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        $contacts = '';
        for($i=0; $i<$limit; $i++){
            $contacts .= 'contacts['.$i.'] = { ';
            //escape string and replace space escape
            $contacts .= 'hotel_id:"'.str_replace('+',' ', urlencode($objRS->fields['hotel_id']))."\",";
            $contacts .= 'contact_id:"'.str_replace('+',' ', urlencode($objRS->fields['contact_id']))."\",";
            $contacts .= 'email:"'.str_replace('+',' ', urlencode($objRS->fields['email']))."\",";
            $contacts .= 'name:"'.str_replace('+',' ', urlencode($objRS->fields['name']))."\",";
            $contacts .= 'firstname:"'.str_replace('+',' ', urlencode($objRS->fields['firstname']))."\",";
            $contacts .= 'street:"'.str_replace('+',' ', urlencode($objRS->fields['street']))."\",";
            $contacts .= 'zip:"'.str_replace('+',' ', urlencode($objRS->fields['zip']))."\",";
            $contacts .= 'location:"'.str_replace('+',' ', urlencode($objRS->fields['location']))."\",";
            $contacts .= 'telephone:"'.str_replace('+',' ', urlencode($objRS->fields['phone_home']))."\",";
            $contacts .= 'comment:"'.str_replace('+',' ', urlencode($objRS->fields['comment']))."\",";
            $contacts .= 'timestamp:"'.str_replace('+',' ', urlencode(date(ASCMS_DATE_FORMAT, $objRS->fields['time'])))."\"};\n\n";
            $objRS->MoveNext();
        }
        die($contacts);
    }

    /**
     * show interests
     *
     * @return void
     */
    function _interests()
    {
        global $objDatabase, $_ARRAYLANG;

        $interestID = intval($_GET['del']);
        if(!empty($interestID)){
            if($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_hotel_interest WHERE id = $interestID") !== false){
                $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_DELETED'];
                header('Location: /admin/?cmd=hotel&act=interests');
                return true;
            }
        }

        $this->_pageTitle = 'Buchungen';
        $this->_objTpl->loadTemplateFile('module_hotel_interests.html');

        $hotelID = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $_SESSION['hotel']['startDate']    = !empty($_REQUEST['inputStartDate']) ? contrexx_addslashes($_REQUEST['inputStartDate'])  : $_SESSION['hotel']['startDate'];
        $_SESSION['hotel']['endDate']    = !empty($_REQUEST['inputEndDate']) ? contrexx_addslashes($_REQUEST['inputEndDate'])  : $_SESSION['hotel']['endDate'];
        $limit = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 20;
        $pos = !empty($_REQUEST['pos']) ? intval($_REQUEST['pos']) : 0;
// Unused
//        $field = (!empty($_REQUEST['field'])) ? contrexx_addslashes($_REQUEST['field']) : 'visits';
//        $order = (!empty($_REQUEST['order'])) ? contrexx_addslashes($_REQUEST['order']) : 'asc';
//        $hsearchField = !empty($_REQUEST['searchField']) ? $_REQUEST['searchField'] : '' ;
//        $hsearch = !empty($_REQUEST['search']) ? contrexx_addslashes($_REQUEST['search']) : '' ;

        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_HOTEL_ID'                     => $_ARRAYLANG['TXT_HOTEL_HOTEL_ID'],
            'TXT_HOTEL_INTERESTS'                 => $_ARRAYLANG['TXT_HOTEL_INTERESTS'],
            'TXT_HOTEL_INTEREST_SEARCH'             => $_ARRAYLANG['TXT_HOTEL_INTEREST_SEARCH'],
            'TXT_HOTEL_EXPORT'                      =>    $_ARRAYLANG['TXT_HOTEL_EXPORT'],
            'TXT_HOTEL_TIMESPAN'                      =>    $_ARRAYLANG['TXT_HOTEL_TIMESPAN'],
            'TXT_HOTEL_FROM'                          =>    $_ARRAYLANG['TXT_HOTEL_FROM'],
            'TXT_HOTEL_TO'                          =>    $_ARRAYLANG['TXT_HOTEL_TO'],
            'TXT_HOTEL_INTERESTS'                 =>    $_ARRAYLANG['TXT_HOTEL_INTERESTS'],
            'TXT_HOTEL_DOWNLOAD_LIST'              =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOAD_LIST'],
            'TXT_HOTEL_INTEREST_SEARCH'              =>    $_ARRAYLANG['TXT_HOTEL_INTEREST_SEARCH'],
            'TXT_HOTEL_SHOW_TIMESPAN_DETAILS'     =>    $_ARRAYLANG['TXT_HOTEL_SHOW_TIMESPAN_DETAILS'],
            'TXT_HOTEL_IGNORE_TIMESPAN'             =>    $_ARRAYLANG['TXT_HOTEL_IGNORE_TIMESPAN'],
            'TXT_HOTEL_REFRESH'                     =>    $_ARRAYLANG['TXT_HOTEL_REFRESH'],
            'TXT_HOTEL_SEARCH'                     =>    $_ARRAYLANG['TXT_HOTEL_SEARCH'],
            'TXT_HOTEL_EMAIL'                     =>    $_ARRAYLANG['TXT_HOTEL_EMAIL'],
            'TXT_HOTEL_NAME'                         =>    $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_FIRSTNAME'                 =>    $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'],
            'TXT_HOTEL_COMPANY'                     =>    $_ARRAYLANG['TXT_HOTEL_COMPANY'],
            'TXT_HOTEL_STREET'                     =>    $_ARRAYLANG['TXT_HOTEL_STREET'],
            'TXT_HOTEL_ZIP'                         =>    $_ARRAYLANG['TXT_HOTEL_ZIP'],
            'TXT_HOTEL_LOCATION'                     =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_TELEPHONE'                 =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE'],
            'TXT_HOTEL_TELEPHONE_OFFICE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'],
            'TXT_HOTEL_TELEPHONE_MOBILE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'],
            'TXT_HOTEL_PURCHASE'                     =>    $_ARRAYLANG['TXT_HOTEL_PURCHASE'],
            'TXT_HOTEL_FUNDING'                     =>    $_ARRAYLANG['TXT_HOTEL_FUNDING'],
            'TXT_HOTEL_COMMENT'                     =>    $_ARRAYLANG['TXT_HOTEL_COMMENT'],
            'TXT_HOTEL_TIMESTAMP'                 =>    $_ARRAYLANG['TXT_HOTEL_TIMESTAMP'],
            'TXT_HOTEL_EXPORT'                           =>    $_ARRAYLANG['TXT_HOTEL_EXPORT'],
            'TXT_HOTEL_FUNCTIONS'                    =>    $_ARRAYLANG['TXT_HOTEL_FUNCTIONS'],
            'TXT_HOTEL_CONFIRM_DELETE_CONTACT'    =>    $_ARRAYLANG['TXT_HOTEL_CONFIRM_DELETE_CONTACT'],
            'TXT_HOTEL_CANNOT_UNDO_OPERATION'    =>    $_ARRAYLANG['TXT_HOTEL_CANNOT_UNDO_OPERATION'],
            'CALENDAR_TODAY'                     => !empty($_SESSION['hotel']['startDate']) ? $_SESSION['hotel']['startDate'] : date('Y-m-d', strtotime('-1 month')),
            'CALENDAR_NEXT_MONTH'                 => !empty($_SESSION['hotel']['endDate']) ? $_SESSION['hotel']['endDate'] : date('Y-m-d'),
            'HOTEL_FORM_ACTION_ID'                 => $hotelID,
            'HOTEL_ID'                             => $hotelID,
        ));




        $searchTerm = (!empty($_REQUEST['search'])) ? " LIKE '%".contrexx_addslashes($_REQUEST['search'])."%'" : ' TRUE';
        $searchField = (!empty($_REQUEST['searchField']) && !empty($_REQUEST['search'])) ? ' WHERE '.contrexx_addslashes($_REQUEST['searchField']) : ' WHERE ';
        $query = "    SELECT     `interest`.`id` , `email` , `name` , `firstname` , `street` , `zip` , `location` ,
                            `phone_home` , `phone_office` , `phone_mobile` , `comment` , `interest`.`hotel_id` , `time` as `timestamp`,
                            content1.fieldvalue AS hotel_header, content2.fieldvalue AS hotel_address, content3.fieldvalue AS hotel_location
                    FROM `".DBPREFIX."module_hotel_interest` AS interest
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = interest.id
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'hotel'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = interest.id
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'citycode'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content3 ON content3.hotel_id = interest.id
                        AND content3.lang_id =1
                        AND content3.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'hotel_id'
                            AND fname.lang_id =1
                        )"
                    .$searchField." ".$searchTerm;

                    if($hotelID > 0){
                        $query .= " AND interest.hotel_id = $hotelID ";
                    }


//                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
//                        $query .= " AND `time` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
//                    }
                    $query .= ' ORDER BY `time` DESC';
        if(($objRS = $objDatabase->Execute($query)) !== false){
            $count = $objRS->RecordCount();
            $objRS = $objDatabase->SelectLimit($query, $limit, $pos);
            $rowclass = 0;
            while(!$objRS->EOF){
                $this->_objTpl->setVariable(array(
                      'HOTEL_CONTACT_ID'        =>    intval($objRS->fields['id']),
                       'HOTEL_HOTEL_ID'            =>    htmlspecialchars($objRS->fields['hotel_id']),
                       'HOTEL_OBJECT_HEADER'    =>    htmlspecialchars($objRS->fields['hotel_header']),
                    'HOTEL_OBJECT_ADDRESS'    =>    htmlspecialchars($objRS->fields['hotel_address']),
                    'HOTEL_OBJECT_LOCATION'    =>    htmlspecialchars($objRS->fields['hotel_location']),
                    'HOTEL_EMAIL'            =>    htmlspecialchars($objRS->fields['email']),
                    'HOTEL_NAME'                =>    htmlspecialchars($objRS->fields['name']),
                    'HOTEL_FIRSTNAME'        =>    htmlspecialchars($objRS->fields['firstname']),
                    'HOTEL_STREET'            =>    htmlspecialchars($objRS->fields['street']),
                    'HOTEL_ZIP'                =>    htmlspecialchars($objRS->fields['zip']),
                    'HOTEL_LOCATION'            =>    htmlspecialchars($objRS->fields['location']),
                    'HOTEL_TELEPHONE'        =>    htmlspecialchars($objRS->fields['phone_home']),
                    'HOTEL_TELEPHONE_OFFICE'    =>    htmlspecialchars($objRS->fields['phone_office']),
                    'HOTEL_TELEPHONE_MOBILE'    =>    htmlspecialchars($objRS->fields['phone_mobile']),
                    'HOTEL_COMMENT'            =>    str_replace(array("\r\n", "\n"), '<br />', htmlspecialchars($objRS->fields['comment'])),
                    'HOTEL_COMMENT_TEXT'        =>    str_replace(array("\r\n", "\n"), '<br />', htmlspecialchars($objRS->fields['comment'])),
                    'HOTEL_COMMENT_INDEX'    =>    $rowclass,
                    'HOTEL_COMMENT_INDEX2'    =>    $rowclass,
                    'HOTEL_TIMESTAMP'        =>    date(ASCMS_DATE_FORMAT, $objRS->fields['timestamp']),
                    'ROW_CLASS'                =>    (++$rowclass % 2 ? 'row1' : 'row2'),
                ));
                $this->_objTpl->parse('commentsArray');
                $this->_objTpl->parse('downloads');
                $objRS->MoveNext();
            }
        }

        $this->_objTpl->setVariable(array(
            'HOTEL_STATS_INTERESTS_PAGING'    => getPaging($count, $pos, '&amp;cmd=hotel&amp;act=interests&amp;limit='.$limit, '', true),
        ));
        return true;
    }


    /**
     * export the requested contacts to a CSV file and send it to the browser
     *
     * @return void
     */
    function _exportContacts()
    {
        global $objDatabase, $_ARRAYLANG;
        $separator = ';';
        switch($_REQUEST['type']){
            case 'downloads':
                $query = "  SELECT  `email`, `name`, `firstname`, `company`, `street`, `zip`, `location`,
                                    `telephone`, `telephone_office`, `telephone_mobile`, `purchase`, `funding`,
                                    `comment`, `timestamp`
                            FROM `".DBPREFIX."module_hotel_contact`";
                if(!empty($_SESSION['hotel']['startDate'])){
                    $query .= " WHERE `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                }
                $hotelid = !empty($_REQUEST['hotel_id']) ? intval($_REQUEST['hotel_id']) : 0;
                if(!empty($hotelid)){
                    $query .= " AND hotel_id = $hotelid";
                }

                $query .=  " ORDER BY `timestamp`";
                $CSVfields = '';
                $cols = array(        'email', 'name', 'firstname', 'company', 'street', 'zip', 'location',
                                    'telephone', 'telephone_office', 'telephone_mobile', 'purchase', 'funding',
                                    'comment','timestamp');

                break;
            case 'interests':
                $query = "  SELECT     `hotel`.`reference`, `name`, `firstname`, `street`, `zip`, `location` ,
                                    `email`, `phone_office`, `phone_home`, `phone_mobile`, `doc_via_mail`,
                                    `funding_advice`, `inspection`, `contact_via_phone`, `comment` ,`time`
                            FROM `".DBPREFIX."module_hotel_interest` AS `interest`
                            LEFT JOIN `".DBPREFIX."module_hotel` AS `hotel` ON `interest`.`hotel_id` = `hotel`.`id`";
                if(!empty($_SESSION['hotel']['startDate'])){
                    $query .= " WHERE `time` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                }
                $hotelid = !empty($_REQUEST['hotel_id']) ? intval($_REQUEST['hotel_id']) : 0;

                if(!empty($hotelid)){
                    $query .= " AND hotel_id = $hotelid";
                }

                $query .= " ORDER BY `time`";
                $CSVfields = '';
                $cols = array(        'reference', 'name', 'firstname', 'street', 'zip', 'location',
                                    'email', 'phone_office', 'phone_home', 'phone_mobile', 'doc_via_mail',
                                    'funding_advice', 'inspection', 'contact_via_phone', 'comment', 'time' );
                break;
            default:
                $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_WRONG_TYPE_FOR_EXPORT'];
                $this->_showStats();
                return false;
                break;
        }

        foreach ($cols as $field) {
            $CSVfields .= $this->_escapeCsvValue($_ARRAYLANG['TXT_HOTEL_'.strtoupper($field)]).$separator;
        }
        $CSVfields = substr($CSVfields, 0, -1).$this->_lineBreak;

        if(($objRS = $objDatabase->Execute($query)) !== false){
            while(!$objRS->EOF){
                foreach ($objRS->fields as $fieldName => $fieldContent) {
                    $CSVdata .= $this->_escapeCsvValue(($fieldName != 'timestamp' && $fieldName != 'time') ? $fieldContent : date(ASCMS_DATE_FORMAT, $fieldContent)).$separator;
                }
                $CSVdata .= $this->_lineBreak;
                $objRS->moveNext();
            }
        }

        header("Content-Type: text/comma-separated-values", true);
        header("Content-Length: ".strlen($CSVdata));
        header("Content-MD5: ".md5($CSVdata));
        header("Content-Disposition: inline; filename=\"".'hotel_stats_contact_'.date('Y-M-D H_m_s', mktime()).".csv\"");
        die($CSVfields.$CSVdata);
    }

    /**
     * show interest details
     *
     * @return void
     */
    function _showInterestDetails(){
        global $_ARRAYLANG, $objDatabase;
        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_DOWNLOAD_DETAILS'];
        $this->_objTpl->loadTemplateFile('module_hotel_interest_details.html');
        $interestID = intval($_GET['id']);
        $this->_objTpl->setVariable(array(
            'TXT_HOTEL_CONTACT_DETAILS'             =>    $_ARRAYLANG['TXT_HOTEL_CONTACT_DETAILS'],
            'TXT_HOTEL_EMAIL'                     =>    $_ARRAYLANG['TXT_HOTEL_EMAIL'],
            'TXT_HOTEL_NAME'                         =>    $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_FIRSTNAME'                 =>    $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'],
            'TXT_HOTEL_COMPANY'                     =>    $_ARRAYLANG['TXT_HOTEL_COMPANY'],
            'TXT_HOTEL_STREET'                     =>    $_ARRAYLANG['TXT_HOTEL_STREET'],
            'TXT_HOTEL_ZIP'                         =>    $_ARRAYLANG['TXT_HOTEL_ZIP'],
            'TXT_HOTEL_LOCATION'                     =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_TELEPHONE'                 =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE'],
            'TXT_HOTEL_TELEPHONE_OFFICE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'],
            'TXT_HOTEL_TELEPHONE_MOBILE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'],
            'TXT_HOTEL_DOC_VIA_MAIL'                 =>    $_ARRAYLANG['TXT_HOTEL_DOC_VIA_MAIL'],
            'TXT_HOTEL_FUNDING_ADVICE'             =>    $_ARRAYLANG['TXT_HOTEL_FUNDING_ADVICE'],
            'TXT_HOTEL_INSPECTION'                 =>    $_ARRAYLANG['TXT_HOTEL_INSPECTION'],
            'TXT_HOTEL_CONTACT_VIA_PHONE'         =>    $_ARRAYLANG['TXT_HOTEL_CONTACT_VIA_PHONE'],
            'TXT_HOTEL_COMMENT'                     =>    $_ARRAYLANG['TXT_HOTEL_COMMENT'],
            'TXT_HOTEL_TIMESTAMP'                 =>    $_ARRAYLANG['TXT_HOTEL_TIMESTAMP'],
            'TXT_HOTEL_BACK'                         =>    $_ARRAYLANG['TXT_HOTEL_BACK'],
            'TXT_HOTEL_OBJECT_DETAILS'             =>    $_ARRAYLANG['TXT_HOTEL_OBJECT_DETAILS'],
        ));

        $query = "    SELECT     `interest`.`id`, `interest`.`hotel_id`, `name`, `firstname`, `street`, `zip`, `location`, `email`,
                            `phone_office`, `phone_home`, `phone_mobile`, `weeks`, `adults`, `children`, `from`, `to`, `flight_only`, `code`,
                            `comment`, `time`, content1.fieldvalue AS hotel_header,
                            content2.fieldvalue AS hotel_address, content3.fieldvalue AS hotel_location
                    FROM `".DBPREFIX."module_hotel_interest` AS interest
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = interest.hotel_id
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'hotel'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = interest.hotel_id
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'adresse'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content3 ON content3.hotel_id = interest.hotel_id
                        AND content3.lang_id =1
                        AND content3.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )
                    WHERE interest.id = $interestID";
        $objRS = $objDatabase->SelectLimit($query, 1);
        if ($objRS) {
            $this->_objTpl->setVariable(array(
                'HOTEL_OBJECT_DETAILS'        =>  htmlspecialchars($objRS->fields['hotel_header']
                                            .', '.htmlspecialchars($objRS->fields['hotel_location'])),
                'HOTEL_EMAIL'                =>    htmlspecialchars($objRS->fields['email']),
                'HOTEL_NAME'                    =>    htmlspecialchars($objRS->fields['name']),
                'HOTEL_FIRSTNAME'            =>    htmlspecialchars($objRS->fields['firstname']),
                'HOTEL_STREET'                =>    htmlspecialchars($objRS->fields['street']),
                'HOTEL_ZIP'                    =>    htmlspecialchars($objRS->fields['zip']),
                'HOTEL_LOCATION'                =>    htmlspecialchars($objRS->fields['location']),
                'HOTEL_TELEPHONE'            =>    htmlspecialchars($objRS->fields['phone_home']),
                'HOTEL_TELEPHONE_OFFICE'        =>    htmlspecialchars($objRS->fields['phone_office']),
                'HOTEL_TELEPHONE_MOBILE'        =>    htmlspecialchars($objRS->fields['phone_mobile']),
                'HOTEL_TRAVEL_WEEKS'            =>    $objRS->fields['weeks'],
                'HOTEL_TRAVEL_CHILDREN'        =>    $objRS->fields['children'],
                'HOTEL_TRAVEL_ADULTS'        =>    $objRS->fields['adults'],
                'HOTEL_TRAVEL_DEPARTUREDATE'    =>    date('d.m.Y', $objRS->fields['from']),
                'HOTEL_TRAVEL_ARRIVALDATE'    =>    date('d.m.Y', $objRS->fields['to']),
                'HOTEL_TRAVEL_FLIGHTONLY'    =>    ($objRS->fields['flight_only'] == 1) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                'HOTEL_TRAVEL_ISISCODE'        =>    $objRS->fields['code'],
                'HOTEL_TRAVEL_COMMENT'        =>    nl2br(htmlspecialchars($objRS->fields['comment'])),
                'HOTEL_TRAVEL_TIMESTAMP'    =>    date(ASCMS_DATE_FORMAT ,$objRS->fields['time']),
                'ROW_CLASS'                    =>    'row1',
            ));
        }
    }


    /**
     * show contact details
     *
     * @return void
     */
    function _showContactDetails(){
        global $_ARRAYLANG, $objDatabase;
        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_CONTACT_DETAILS'];
        $this->_objTpl->loadTemplateFile('module_hotel_contact_details.html');
        $contactID = intval($_GET['id']);

        $this->_objTpl->setVariable(array(

            'TXT_HOTEL_OBJECT_DETAILS'             =>    $_ARRAYLANG['TXT_HOTEL_OBJECT_DETAILS'],
            'TXT_HOTEL_CONTACT_DETAILS'             =>    $_ARRAYLANG['TXT_HOTEL_CONTACT_DETAILS'],
            'TXT_HOTEL_EMAIL'                     =>    $_ARRAYLANG['TXT_HOTEL_EMAIL'],
            'TXT_HOTEL_NAME'                         =>    $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_FIRSTNAME'                 =>    $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'],
            'TXT_HOTEL_COMPANY'                     =>    $_ARRAYLANG['TXT_HOTEL_COMPANY'],
            'TXT_HOTEL_STREET'                     =>    $_ARRAYLANG['TXT_HOTEL_STREET'],
            'TXT_HOTEL_ZIP'                         =>    $_ARRAYLANG['TXT_HOTEL_ZIP'],
            'TXT_HOTEL_LOCATION'                     =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_TELEPHONE'                 =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE'],
            'TXT_HOTEL_TELEPHONE_OFFICE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'],
            'TXT_HOTEL_TELEPHONE_MOBILE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'],
            'TXT_HOTEL_PURCHASE'                     =>    $_ARRAYLANG['TXT_HOTEL_PURCHASE'],
            'TXT_HOTEL_FUNDING'                     =>    $_ARRAYLANG['TXT_HOTEL_FUNDING'],
            'TXT_HOTEL_COMMENT'                     =>    $_ARRAYLANG['TXT_HOTEL_COMMENT'],
            'TXT_HOTEL_TIMESTAMP'                 =>    $_ARRAYLANG['TXT_HOTEL_TIMESTAMP'],
            'TXT_HOTEL_BACK'                         =>    $_ARRAYLANG['TXT_HOTEL_BACK'],
        ));


        $query = "    SELECT     `contact`.`id` , `email` , `name` , `firstname` , `street` , `zip` , `location` ,
                            `company` , `telephone` , `telephone_office` , `telephone_mobile` , `purchase` ,
                            `funding` ,  `comment` , `contact`.`hotel_id` , `timestamp`, content1.fieldvalue AS hotel_header,
                            content2.fieldvalue AS hotel_address, content3.fieldvalue AS hotel_location
                    FROM `".DBPREFIX."module_hotel_contact` AS contact
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = contact.hotel_id
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'headline'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = contact.hotel_id
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'adresse'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content3 ON content3.hotel_id = contact.hotel_id
                        AND content3.lang_id =1
                        AND content3.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )
                    WHERE contact.id = $contactID";
        $objRS = $objDatabase->SelectLimit($query, 1);
        if ($objRS) {
            $this->_objTpl->setVariable(array(
                'HOTEL_OBJECT_DETAILS'    =>  htmlspecialchars($objRS->fields['hotel_header']
                                            .', '.htmlspecialchars($objRS->fields['hotel_address'])
                                            .', '.htmlspecialchars($objRS->fields['hotel_location'])),
                'HOTEL_EMAIL'            =>    htmlspecialchars($objRS->fields['email']),
                'HOTEL_NAME'                =>    htmlspecialchars($objRS->fields['name']),
                'HOTEL_FIRSTNAME'        =>    htmlspecialchars($objRS->fields['firstname']),
                'HOTEL_COMPANY'            =>    htmlspecialchars($objRS->fields['company']),
                'HOTEL_STREET'            =>    htmlspecialchars($objRS->fields['street']),
                'HOTEL_ZIP'                =>    htmlspecialchars($objRS->fields['zip']),
                'HOTEL_LOCATION'            =>    htmlspecialchars($objRS->fields['location']),
                'HOTEL_TELEPHONE'        =>    htmlspecialchars($objRS->fields['telephone']),
                'HOTEL_TELEPHONE_OFFICE'    =>    htmlspecialchars($objRS->fields['telephone_office']),
                'HOTEL_TELEPHONE_MOBILE'    =>    htmlspecialchars($objRS->fields['telephone_mobile']),
                'HOTEL_PURCHASE'            =>    $objRS->fields['purchase'] == 1 ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                'HOTEL_FUNDING'            =>    $objRS->fields['funding'] == 1 ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                'HOTEL_COMMENT'            =>    htmlspecialchars($objRS->fields['comment']),
                'HOTEL_TIMESTAMP'        =>    date(ASCMS_DATE_FORMAT ,$objRS->fields['timestamp']),
                'ROW_CLASS'                =>    'row1',
            ));
        }
    }


    /**
     * Prepare value for insertion into a csv file.
     *
     * @param string $value
     * @return string
     */
    function _escapeCsvValue($value)
    {
        $value = preg_replace('/\r\n/', "\n", $value);
        $valueModified = str_replace('"', '""', $value);

        if ($valueModified != $value || preg_match('/[;\n]+/', $value)) {
            $value = '"'.$valueModified.'"';
        }
        return $value;
    }


    /**
     * show statistics
     *
     * @return void
     */
    function _showStats()
    {
        global $_ARRAYLANG, $objDatabase, $_CONFIG;

        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_STATS'];
        $this->_objTpl->loadTemplateFile('module_hotel_stats.html');
        //paging data
        $limit = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : $_CONFIG['corePagingLimit'];
        $pos = !empty($_REQUEST['pos']) ? intval($_REQUEST['pos']) : 0;
        $field = (!empty($_REQUEST['field'])) ? contrexx_addslashes($_REQUEST['field']) : 'visits';
// Unused
//        $order = (!empty($_REQUEST['order'])) ? contrexx_addslashes($_REQUEST['order']) : 'asc';
        $hsearchField = !empty($_REQUEST['searchField']) ? $_REQUEST['searchField'] : '' ;
        $hsearch = !empty($_REQUEST['search']) ? contrexx_addslashes($_REQUEST['search']) : '' ;

        if(empty($_REQUEST['tab'])){
            $_REQUEST['tab'] = 'hotel_downloads';
        }

        $_SESSION['hotel']['startDate']    = !empty($_REQUEST['inputStartDate']) ? contrexx_addslashes($_REQUEST['inputStartDate'])  : $_SESSION['hotel']['startDate'];
        $_SESSION['hotel']['endDate']    = !empty($_REQUEST['inputEndDate']) ? contrexx_addslashes($_REQUEST['inputEndDate'])  : $_SESSION['hotel']['endDate'];

        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_PAGE_VIEWS'               =>    $_ARRAYLANG['TXT_HOTEL_PAGE_VIEWS'],
            'TXT_HOTEL_DOWNLOADS'                =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOADS'],
            'TXT_HOTEL_OBJECT'                     =>    $_ARRAYLANG['TXT_HOTEL_OBJECT'],
            'TXT_HOTEL_VISITS'                     =>    $_ARRAYLANG['TXT_HOTEL_VISITS'],
            'TXT_HOTEL_HEADER'                     =>    $_ARRAYLANG['TXT_HOTEL_HEADER'],
            'TXT_HOTEL_LOCATION'                 =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_SEARCH'                     =>    $_ARRAYLANG['TXT_HOTEL_SEARCH'],
            'TXT_HOTEL_DOWNLOAD_SEARCH'             =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOAD_SEARCH'],
            'TXT_HOTEL_SORT'                     =>    $_ARRAYLANG['TXT_HOTEL_SORT'],
            'TXT_HOTEL_HOTEL_ID'                 =>    $_ARRAYLANG['TXT_HOTEL_HOTEL_ID'],
            'TXT_HOTEL_EMAIL'                     =>    $_ARRAYLANG['TXT_HOTEL_EMAIL'],
            'TXT_HOTEL_NAME'                     =>    $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_FIRSTNAME'                 =>    $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'],
            'TXT_HOTEL_COMPANY'                     =>    $_ARRAYLANG['TXT_HOTEL_COMPANY'],
            'TXT_HOTEL_STREET'                     =>    $_ARRAYLANG['TXT_HOTEL_STREET'],
            'TXT_HOTEL_ZIP'                         =>    $_ARRAYLANG['TXT_HOTEL_ZIP'],
            'TXT_HOTEL_LOCATION'                 =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_TELEPHONE'                 =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE'],
            'TXT_HOTEL_TELEPHONE_OFFICE'         =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'],
            'TXT_HOTEL_TELEPHONE_MOBILE'         =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'],
            'TXT_HOTEL_PURCHASE'                 =>    $_ARRAYLANG['TXT_HOTEL_PURCHASE'],
            'TXT_HOTEL_FUNDING'                     =>    $_ARRAYLANG['TXT_HOTEL_FUNDING'],
            'TXT_HOTEL_COMMENT'                     =>    $_ARRAYLANG['TXT_HOTEL_COMMENT'],
            'TXT_HOTEL_TIMESTAMP'                 =>    $_ARRAYLANG['TXT_HOTEL_TIMESTAMP'],
            'TXT_HOTEL_EXPORT'                           =>    $_ARRAYLANG['TXT_HOTEL_EXPORT'],
            'TXT_HOTEL_FUNCTIONS'                    =>    $_ARRAYLANG['TXT_HOTEL_FUNCTIONS'],
            'TXT_HOTEL_SEPARATOR'                    =>    $_ARRAYLANG['TXT_HOTEL_SEPARATOR'],
            'TXT_HOTEL_EDIT'                        =>    $_ARRAYLANG['TXT_HOTEL_EDIT'],
            'TXT_HOTEL_DELETE'                        =>    $_ARRAYLANG['TXT_HOTEL_DELETE'],
            'TXT_HOTEL_SHOW_OBJECT_IN_NEW_WINDOW'=>    $_ARRAYLANG['TXT_HOTEL_SHOW_OBJECT_IN_NEW_WINDOW'],
            'TXT_HOTEL_CONFIRM_DELETE_CONTACT'   =>    $_ARRAYLANG['TXT_HOTEL_CONFIRM_DELETE_CONTACT'],
            'TXT_HOTEL_CANNOT_UNDO_OPERATION'    =>    $_ARRAYLANG['TXT_HOTEL_CANNOT_UNDO_OPERATION'],
            'TXT_HOTEL_COUNT'                      =>    $_ARRAYLANG['TXT_HOTEL_COUNT'],
            'TXT_HOTEL_REF_NOTE'                  =>    $_ARRAYLANG['TXT_HOTEL_REF_NOTE'],
            'TXT_HOTEL_REFERENCE_NUMBER'         =>    $_ARRAYLANG['TXT_HOTEL_REFERENCE_NUMBER'],
            'TXT_HOTEL_HEADER'                      =>    $_ARRAYLANG['TXT_HOTEL_HEADER'],
            'TXT_HOTEL_LINKNAME'                  =>    $_ARRAYLANG['TXT_HOTEL_LINKNAME'],
            'TXT_HOTEL_TIMESPAN'                  =>    $_ARRAYLANG['TXT_HOTEL_TIMESPAN'],
            'TXT_HOTEL_FROM'                          =>    $_ARRAYLANG['TXT_HOTEL_FROM'],
            'TXT_HOTEL_TO'                          =>    $_ARRAYLANG['TXT_HOTEL_TO'],
            'TXT_HOTEL_INTERESTS'                 =>    $_ARRAYLANG['TXT_HOTEL_INTERESTS'],
            'TXT_HOTEL_DOWNLOAD_LIST'              =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOAD_LIST'],
            'TXT_HOTEL_INTEREST_SEARCH'          =>    $_ARRAYLANG['TXT_HOTEL_INTEREST_SEARCH'],
            'TXT_HOTEL_SHOW_TIMESPAN_DETAILS'    =>    $_ARRAYLANG['TXT_HOTEL_SHOW_TIMESPAN_DETAILS'],
            'TXT_HOTEL_IGNORE_TIMESPAN'             =>    $_ARRAYLANG['TXT_HOTEL_IGNORE_TIMESPAN'],
            'TXT_HOTEL_REFRESH'                     =>    $_ARRAYLANG['TXT_HOTEL_REFRESH'],
            'CALENDAR_TODAY'                     => !empty($_SESSION['hotel']['startDate']) ? $_SESSION['hotel']['startDate'] : date('Y-m-d', strtotime('-1 month')),
            'CALENDAR_NEXT_MONTH'                 => !empty($_SESSION['hotel']['endDate']) ? $_SESSION['hotel']['endDate'] : date('Y-m-d'),
            'HOTEL_IGNORE_TIMESPAN_CHECKED'         => empty($_REQUEST['ignore_timespan']) ? '' : 'checked="checked"',
            'PATH_OFFSET'                         => ASCMS_PATH_OFFSET,
            'HOTEL_PAGING_LIMIT'                 =>    $limit,
            'HOTEL_PAGING_POS'                     =>    $pos,
            'HOTEL_PAGING_FIELD'                 =>    $field,
            'HOTEL_HSEARCH_FIELD'                 =>    $hsearchField,
            'HOTEL_HSEARCH'                         =>    $hsearch,
            'HOTEL_VISIBLE_TAB'                     =>    $_REQUEST['tab'],
            'HOTEL_DOWNLOADS_VISIBLE'             =>    ($_REQUEST['tab'] == 'hotel_downloads') ? 'style="display: block;"' : 'style="display: none;"',
            'HOTEL_INTERESTS_VISIBLE'             =>    ($_REQUEST['tab'] == 'hotel_interests') ? 'style="display: block;"' : 'style="display: none;"',
            'HOTEL_PAGEVIEWS_VISIBLE'             =>    ($_REQUEST['tab'] == 'hotel_pageviews') ? 'style="display: block;"' : 'style="display: none;"',
            'HOTEL_DOWNLOADS_TAB_ACTIVE'         => ($_REQUEST['tab'] == 'hotel_downloads') ? 'class="active"' : '',
            'HOTEL_INTERESTS_TAB_ACTIVE'         => ($_REQUEST['tab'] == 'hotel_interests') ? 'class="active"' : '',
            'HOTEL_PAGEVIEWS_TAB_ACTIVE'         => ($_REQUEST['tab'] == 'hotel_pageviews') ? 'class="active"' : '',


        ));


        $rowclass = 2;
        //get object request stats
        $query = "    SELECT page, visits, content1.fieldvalue AS header, content2.fieldvalue AS location, hotel.reference as reference, hotel.ref_nr_note as ref_note
                    FROM `".DBPREFIX."stats_requests`
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = CAST(MID(`page`, 40, 8 ) AS UNSIGNED)
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'headline'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = CAST(MID(`page`, 40, 8) AS UNSIGNED)
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel` AS hotel ON hotel.id = CAST(MID(`page`, 40, 8) AS UNSIGNED)
                    WHERE page LIKE '/index.php?section=hotel&cmd=showObj&id=%'";
                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    $query .= " GROUP BY reference
                    ORDER BY ".$field." DESC";
        if(($objRS = $objDatabase->Execute($query)) !== false){
            $count = $objRS->RecordCount();
            $objRS = $objDatabase->SelectLimit($query, $limit, $pos);
            $i = 0;
            while(!$objRS->EOF){
// Unused
//                $split = explode('&', $objRS->fields['page']);
// Unused
//                $hotelID = intval($split[0]);

                $this->_objTpl->setVariable(array(
                    'HOTEL_OBJECT_NAME'        =>    htmlspecialchars($objRS->fields['page']),
                    'HOTEL_VISITS'            =>    $objRS->fields['visits'],
                    'HOTEL_OBJECT_HEADER'    =>    htmlspecialchars($objRS->fields['header']),
                    'HOTEL_OBJECT_LOCATION'    =>    htmlspecialchars($objRS->fields['location']),
                    'HOTEL_OBJECT_REFERENCE'    =>    !empty($objRS->fields['reference']) ? htmlspecialchars($objRS->fields['reference']) : 'N/A',
                    'HOTEL_OBJECT_REF_NOTE'    =>    !empty($objRS->fields['ref_note']) ? htmlspecialchars($objRS->fields['ref_note']) : 'N/A',
                    'ROW_CLASS'                =>    ($rowclass++ % 2 == 0) ? 'row1' : 'row2',
                ));
                $this->_objTpl->parse('pageVisits');
                $objRS->MoveNext();
            }
        }else{
            die("db error.".$objDatabase->ErrorMsg());
        }
        $this->_objTpl->setVariable(array(
            'HOTEL_STATS_PAGEVIEW_PAGING'    => getPaging($count, $pos, '&amp;cmd=hotel&amp;act=stats&amp;tab=1&amp;limit='.$limit, '', true),
        ));

        $rowclass = 2;
        //get protected link donload stats
        $query = "    SELECT count( 1 ) AS cnt, hotel.id as hotel_id, hotel.reference, hotel.ref_nr_note, a.fieldvalue AS header, b.name AS linkname
                    FROM `".DBPREFIX."module_hotel_contact`
                        AS contact
                    LEFT JOIN ".DBPREFIX."module_hotel
                        AS hotel
                        ON ( contact.hotel_id = hotel.id )
                    LEFT JOIN ".DBPREFIX."module_hotel_content
                        AS a
                        ON ( a.hotel_id = contact.hotel_id )
                    LEFT JOIN ".DBPREFIX."module_hotel_fieldname
                        AS fn
                        ON ( a.field_id = fn.field_id )
                    LEFT JOIN ".DBPREFIX."module_hotel_fieldname
                        AS b
                        ON ( b.field_id = contact.field_id )
                    WHERE fn.name = 'headline'
                    AND fn.lang_id = 1
                    AND a.lang_id = 1
                    AND b.lang_id = 1";
                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    $query .= " GROUP BY contact.hotel_id
                    ORDER BY cnt DESC";
        if(($objRS = $objDatabase->Execute($query)) !== false){
            $count = $objRS->RecordCount();
            $objRS = $objDatabase->SelectLimit($query, $limit, $pos);
            while(!$objRS->EOF){
                $this->_objTpl->setVariable(array(
                      'HOTEL_DL_COUNT'            =>    intval($objRS->fields['cnt']),
                       'HOTEL_DL_REF_NOTE'        =>    htmlspecialchars($objRS->fields['ref_nr_note']),
                       'HOTEL_DL_REFERENCE'        =>    htmlspecialchars($objRS->fields['reference']),
                       'HOTEL_DL_HEADER'        =>    htmlspecialchars($objRS->fields['header']),
                       'HOTEL_DL_LINKNAME'        =>    htmlspecialchars($objRS->fields['linkname']),
                       'HOTEL_DL_HOTEL_ID'        =>    intval($objRS->fields['hotel_id']),
                    'ROW_CLASS'                =>    ($rowclass++ % 2 == 0) ? 'row1' : 'row2',
                ));
                $this->_objTpl->parse('downloads');
                $i++;
                $objRS->MoveNext();
            }
        }
        $this->_objTpl->setVariable(array(
            'HOTEL_STATS_DOWNLOADS_PAGING'    => getPaging($count, $pos, '&amp;cmd=hotel&amp;act=stats&amp;limit='.$limit, '', true),
        ));


        $query = "    SELECT count( `interest`.`hotel_id` ) AS cnt, hotel.id AS hotel_id, hotel.reference, hotel.ref_nr_note, a.fieldvalue AS header
                    FROM `".DBPREFIX."module_hotel_interest` AS interest
                    LEFT JOIN ".DBPREFIX."module_hotel AS hotel ON ( interest.hotel_id = hotel.id )
                    LEFT JOIN ".DBPREFIX."module_hotel_content AS a ON ( a.hotel_id = interest.hotel_id )
                    LEFT JOIN ".DBPREFIX."module_hotel_fieldname AS fn ON ( a.field_id = fn.field_id )
                    WHERE fn.name = 'hotel'
                    AND fn.lang_id =1
                    AND a.lang_id =1";
                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `time` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    $query .= " GROUP BY `interest`.`hotel_id`
                    ORDER BY cnt DESC ";
        if(($objRS = $objDatabase->Execute($query)) !== false){
            $count = $objRS->RecordCount();
            $objRS = $objDatabase->SelectLimit($query, $limit, $pos);
            while(!$objRS->EOF){
                $this->_objTpl->setVariable(array(
                      'HOTEL_INTEREST_COUNT'        =>    intval($objRS->fields['cnt']),
                       'HOTEL_INTEREST_REF_NOTE'    =>    htmlspecialchars($objRS->fields['ref_nr_note']),
                       'HOTEL_INTEREST_REFERENCE'    =>    htmlspecialchars($objRS->fields['reference']),
                       'HOTEL_INTEREST_HEADER'        =>    htmlspecialchars($objRS->fields['header']),
                       'HOTEL_INTEREST_HOTEL_ID'        =>    intval($objRS->fields['hotel_id']),
                    'ROW_CLASS'                    =>    ($rowclass++ % 2 == 0) ? 'row1' : 'row2',
                ));
                $this->_objTpl->parse('interests');
                $i++;
                $objRS->MoveNext();
            }
        }
        $this->_objTpl->setVariable(array(
            'HOTEL_STATS_INTERESTS_PAGING'    => getPaging($count, $pos, '&amp;cmd=hotel&amp;act=stats&amp;limit='.$limit, '', true),
        ));
    }


    /**
     * show protected link downloads
     *
     * @return void
     */
    function _showDownloads()
    {
        global $_ARRAYLANG, $objDatabase, $_CONFIG;
        //delete if $_GET['del'] is set
        $contactID = intval($_GET['del']);
        if(!empty($contactID)){
            if($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_hotel_contact WHERE id = $contactID") !== false){
                $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_DELETED'];
                $this->_showStats();
                return true;
            }
        }
        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_DOWNLOADS'];
        $this->_objTpl->loadTemplateFile('module_hotel_downloads.html');

        $hotelID = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        $_SESSION['hotel']['startDate']    = !empty($_REQUEST['inputStartDate']) ? contrexx_addslashes($_REQUEST['inputStartDate'])  : $_SESSION['hotel']['startDate'];
        $_SESSION['hotel']['endDate']    = !empty($_REQUEST['inputEndDate']) ? contrexx_addslashes($_REQUEST['inputEndDate'])  : $_SESSION['hotel']['endDate'];
        //paging data
        $limit = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : $_CONFIG['corePagingLimit'];
        $pos = !empty($_REQUEST['pos']) ? intval($_REQUEST['pos']) : 0;
        $field = (!empty($_REQUEST['field'])) ? contrexx_addslashes($_REQUEST['field']) : 'visits';
// Unused
//        $order = (!empty($_REQUEST['order'])) ? contrexx_addslashes($_REQUEST['order']) : 'asc';
        $hsearchField = !empty($_REQUEST['searchField']) ? $_REQUEST['searchField'] : '' ;
        $hsearch = !empty($_REQUEST['search']) ? contrexx_addslashes($_REQUEST['search']) : '' ;

        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_EXPORT'                      =>    $_ARRAYLANG['TXT_HOTEL_EXPORT'],
            'TXT_HOTEL_DOWNLOADS'                 =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOADS'],
            'TXT_HOTEL_OBJECT'                     =>    $_ARRAYLANG['TXT_HOTEL_OBJECT'],
            'TXT_HOTEL_VISITS'                     =>    $_ARRAYLANG['TXT_HOTEL_VISITS'],
            'TXT_HOTEL_HEADER'                     =>    $_ARRAYLANG['TXT_HOTEL_HEADER'],
            'TXT_HOTEL_LOCATION'                     =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_SEARCH'                     =>    $_ARRAYLANG['TXT_HOTEL_SEARCH'],
            'TXT_HOTEL_DOWNLOAD_SEARCH'             =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOAD_SEARCH'],
            'TXT_HOTEL_SORT'                         =>    $_ARRAYLANG['TXT_HOTEL_SORT'],
            'TXT_HOTEL_HOTEL_ID'                     =>    $_ARRAYLANG['TXT_HOTEL_HOTEL_ID'],
            'TXT_HOTEL_EMAIL'                     =>    $_ARRAYLANG['TXT_HOTEL_EMAIL'],
            'TXT_HOTEL_NAME'                         =>    $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_FIRSTNAME'                 =>    $_ARRAYLANG['TXT_HOTEL_FIRSTNAME'],
            'TXT_HOTEL_COMPANY'                     =>    $_ARRAYLANG['TXT_HOTEL_COMPANY'],
            'TXT_HOTEL_STREET'                     =>    $_ARRAYLANG['TXT_HOTEL_STREET'],
            'TXT_HOTEL_ZIP'                         =>    $_ARRAYLANG['TXT_HOTEL_ZIP'],
            'TXT_HOTEL_LOCATION'                     =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
            'TXT_HOTEL_TELEPHONE'                 =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE'],
            'TXT_HOTEL_TELEPHONE_OFFICE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_OFFICE'],
            'TXT_HOTEL_TELEPHONE_MOBILE'             =>    $_ARRAYLANG['TXT_HOTEL_TELEPHONE_MOBILE'],
            'TXT_HOTEL_PURCHASE'                     =>    $_ARRAYLANG['TXT_HOTEL_PURCHASE'],
            'TXT_HOTEL_FUNDING'                     =>    $_ARRAYLANG['TXT_HOTEL_FUNDING'],
            'TXT_HOTEL_COMMENT'                     =>    $_ARRAYLANG['TXT_HOTEL_COMMENT'],
            'TXT_HOTEL_TIMESTAMP'                 =>    $_ARRAYLANG['TXT_HOTEL_TIMESTAMP'],
            'TXT_HOTEL_EXPORT'                           =>    $_ARRAYLANG['TXT_HOTEL_EXPORT'],
            'TXT_HOTEL_FUNCTIONS'                    =>    $_ARRAYLANG['TXT_HOTEL_FUNCTIONS'],
            'TXT_HOTEL_SEPARATOR'                    =>    $_ARRAYLANG['TXT_HOTEL_SEPARATOR'],
            'TXT_HOTEL_EDIT'                            =>    $_ARRAYLANG['TXT_HOTEL_EDIT'],
            'TXT_HOTEL_DELETE'                        =>    $_ARRAYLANG['TXT_HOTEL_DELETE'],
            'TXT_HOTEL_CONFIRM_DELETE_CONTACT'    =>    $_ARRAYLANG['TXT_HOTEL_CONFIRM_DELETE_CONTACT'],
            'TXT_HOTEL_CANNOT_UNDO_OPERATION'     =>    $_ARRAYLANG['TXT_HOTEL_CANNOT_UNDO_OPERATION'],
            'TXT_HOTEL_DETAILS'                      =>    $_ARRAYLANG['TXT_HOTEL_DETAILS'],
            'TXT_HOTEL_TIMESPAN'                      =>    $_ARRAYLANG['TXT_HOTEL_TIMESPAN'],
            'TXT_HOTEL_FROM'                          =>    $_ARRAYLANG['TXT_HOTEL_FROM'],
            'TXT_HOTEL_TO'                          =>    $_ARRAYLANG['TXT_HOTEL_TO'],
            'TXT_HOTEL_INTERESTS'                 =>    $_ARRAYLANG['TXT_HOTEL_INTERESTS'],
            'TXT_HOTEL_DOWNLOAD_LIST'              =>    $_ARRAYLANG['TXT_HOTEL_DOWNLOAD_LIST'],
            'TXT_HOTEL_INTEREST_SEARCH'              =>    $_ARRAYLANG['TXT_HOTEL_INTEREST_SEARCH'],
            'TXT_HOTEL_SHOW_TIMESPAN_DETAILS'     =>    $_ARRAYLANG['TXT_HOTEL_SHOW_TIMESPAN_DETAILS'],
            'TXT_HOTEL_IGNORE_TIMESPAN'             =>    $_ARRAYLANG['TXT_HOTEL_IGNORE_TIMESPAN'],
            'TXT_HOTEL_REFRESH'                     =>    $_ARRAYLANG['TXT_HOTEL_REFRESH'],
            'CALENDAR_TODAY'                     => !empty($_SESSION['hotel']['startDate']) ? $_SESSION['hotel']['startDate'] : date('Y-m-d', strtotime('-1 month')),
            'CALENDAR_NEXT_MONTH'                 => !empty($_SESSION['hotel']['endDate']) ? $_SESSION['hotel']['endDate'] : date('Y-m-d'),
            'PATH_OFFSET'                         => ASCMS_PATH_OFFSET,
            'HOTEL_FORM_ACTION_ID'                 => $hotelID,
            'HOTEL_ID'                             => $hotelID,
            'HOTEL_PAGING_LIMIT'                     =>    $limit,
            'HOTEL_PAGING_POS'                     =>    $pos,
            'HOTEL_PAGING_FIELD'                     =>    $field,
            'HOTEL_HSEARCH_FIELD'                 =>    $hsearchField,
            'HOTEL_HSEARCH'                         =>    $hsearch,

        ));
        $rowclass = 2;

        //get contact and download stats
        $searchTerm = (!empty($_REQUEST['search'])) ? " LIKE '%".contrexx_addslashes($_REQUEST['search'])."%'" : ' TRUE';
        $searchField = (!empty($_REQUEST['searchField']) && !empty($_REQUEST['search'])) ? ' WHERE '.contrexx_addslashes($_REQUEST['searchField']) : ' WHERE ';
        $query = "    SELECT     `contact`.`id` , `email` , `name` , `firstname` , `street` , `zip` , `location` ,
                            `company` , `telephone` , `telephone_office` , `telephone_mobile` , `purchase` ,
                            `funding` ,  `comment` , `contact`.`hotel_id` , `timestamp`, content1.fieldvalue AS hotel_header, content2.fieldvalue AS hotel_address, content3.fieldvalue AS hotel_location
                    FROM `".DBPREFIX."module_hotel_contact` AS contact
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = contact.id
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'headline'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = contact.id
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'adresse'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content3 ON content3.hotel_id = contact.id
                        AND content3.lang_id =1
                        AND content3.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )"
                    .$searchField." ".$searchTerm;

                    if($hotelID > 0){
                        $query .= " AND contact.hotel_id = $hotelID ";
                    }


                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate'])." ORDER BY timestamp DESC";
                    }
        if(($objRS = $objDatabase->Execute($query)) !== false){
            $count = $objRS->RecordCount();
            $objRS = $objDatabase->SelectLimit($query, $limit, $pos);
            $i=0;
            while(!$objRS->EOF){
                $this->_objTpl->setVariable(array(
                      'HOTEL_CONTACT_ID'        =>    intval($objRS->fields['id']),
                       'HOTEL_HOTEL_ID'            =>    htmlspecialchars($objRS->fields['hotel_id']),
                       'HOTEL_OBJECT_HEADER'    =>    htmlspecialchars($objRS->fields['hotel_header']),
                    'HOTEL_OBJECT_ADDRESS'    =>    htmlspecialchars($objRS->fields['hotel_address']),
                    'HOTEL_OBJECT_LOCATION'    =>    htmlspecialchars($objRS->fields['hotel_location']),
                    'HOTEL_EMAIL'            =>    htmlspecialchars($objRS->fields['email']),
                    'HOTEL_NAME'                =>    htmlspecialchars($objRS->fields['name']),
                    'HOTEL_FIRSTNAME'        =>    htmlspecialchars($objRS->fields['firstname']),
                    'HOTEL_COMPANY'            =>    htmlspecialchars($objRS->fields['company']),
                    'HOTEL_STREET'            =>    htmlspecialchars($objRS->fields['street']),
                    'HOTEL_ZIP'                =>    htmlspecialchars($objRS->fields['zip']),
                    'HOTEL_LOCATION'            =>    htmlspecialchars($objRS->fields['location']),
                    'HOTEL_TELEPHONE'        =>    htmlspecialchars($objRS->fields['telephone']),
                    'HOTEL_TELEPHONE_OFFICE'    =>    htmlspecialchars($objRS->fields['telephone_office']),
                    'HOTEL_TELEPHONE_MOBILE'    =>    htmlspecialchars($objRS->fields['telephone_mobile']),
                    'HOTEL_PURCHASE'            =>    htmlspecialchars($objRS->fields['purchase']),
                    'HOTEL_FUNDING'            =>    htmlspecialchars($objRS->fields['funding']),
                    'HOTEL_COMMENT'            =>    str_replace(array("\r\n", "\n"), '<br />', htmlspecialchars($objRS->fields['comment'])),
                    'HOTEL_COMMENT_TEXT'        =>    str_replace(array("\r\n", "\n"), '<br />', htmlspecialchars($objRS->fields['comment'])),
                    'HOTEL_COMMENT_INDEX'    =>    $i,
                    'HOTEL_COMMENT_INDEX2'    =>    $i,
                    'HOTEL_TIMESTAMP'        =>    date(ASCMS_DATE_FORMAT ,$objRS->fields['timestamp']),
                    'ROW_CLASS'                =>    ($rowclass++ % 2 == 0) ? 'row1' : 'row2',
                ));
                $this->_objTpl->parse('commentsArray');
                $this->_objTpl->parse('downloads');
                $i++;
                $objRS->MoveNext();
            }
        }
        $this->_objTpl->setVariable(array(
            'HOTEL_STATS_DOWNLOADS_PAGING'    => getPaging($count, $pos, '&amp;cmd=hotel&amp;act=downloads&amp;limit='.$limit, '', true),
        ));
        return true;
    }

    /**
     * remote scripting search for interests
     *
     * @return JSON object
     */
    function _RPCSearchInterests()
    {
        global $_CONFIG, $objDatabase;

        $fieldValues = array('int_count', 'int_reference', 'int_ref_note', 'int_header');
        $field = (!empty($_GET['field'])) ? contrexx_addslashes($_GET['field']) : 'int_count';
        $order = (!empty($_GET['order'])) ? contrexx_addslashes($_GET['order']) : 'asc';
        $limit = (!empty($_GET['limit'])) ? intval($_GET['limit']) : $_CONFIG['corePagingLimit'];
        $pos = (!empty($_GET['pos'])) ? intval($_GET['pos']) : 0;
        if(!in_array($field, $fieldValues) && ( $order != 'asc' || $order != 'desc' )){
            die();
        }
        $query = "    SELECT count( 1 ) AS int_count, hotel.reference as int_reference, hotel.ref_nr_note as int_ref_note, a.fieldvalue AS int_header, hotel.id as int_hotelid
                    FROM `".DBPREFIX."module_hotel_interest`
                        AS interest
                    LEFT JOIN ".DBPREFIX."module_hotel
                        AS hotel
                        ON ( interest.hotel_id = hotel.id )
                    LEFT JOIN ".DBPREFIX."module_hotel_content
                        AS a
                        ON ( a.hotel_id = interest.hotel_id )
                    LEFT JOIN ".DBPREFIX."module_hotel_fieldname
                        AS fn
                        ON ( a.field_id = fn.field_id )
                    WHERE fn.name = 'headline'";


                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `time` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    $query .= " AND fn.lang_id =1
                    AND a.lang_id = 1
                    GROUP BY interest.hotel_id
                    ORDER BY ".$field." ".$order;
        $objRS        = $objDatabase->SelectLimit($query, $limit, $pos);
        $limit         = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        $interests    = '';
        for($i=0; $i<$limit; $i++){
            $interests .= 'interests['.$i.'] = { ';
            //escape string and replace space escape
            $interests .= '"int_count":"'.str_replace('+',' ', urlencode($objRS->fields['int_count']))."\",";
            $interests .= '"int_reference":"'.str_replace('+',' ', urlencode($objRS->fields['int_reference']))."\",";
            $interests .= '"int_ref_note":"'.str_replace('+',' ', urlencode($objRS->fields['int_ref_note']))."\",";
            $interests .= '"int_header":"'.str_replace('+',' ', urlencode($objRS->fields['int_header']))."\",";
            $interests .= '"int_hotelid":"'.str_replace('+',' ', urlencode($objRS->fields['int_hotelid']))."\"};\n\n";
            $objRS->MoveNext();
        }
        die($interests);
    }


    /**
     * remote scripting search for downloads
     *
     * @return JSON object
     */

    function _RPCSearch()
    {
        global $_CONFIG, $objDatabase;

        $fieldValues = array('hotel_id', 'name', 'firstname', 'street', 'zip', 'location', 'telephone', 'comment', 'timestamp');
        $hotelID = !empty($_REQUEST['hotelid']) ? intval($_REQUEST['hotelid']) : 0;
        $field = (!empty($_GET['field'])) ? contrexx_addslashes($_GET['field']) : 'timestamp';
        $order = (!empty($_GET['order'])) ? contrexx_addslashes($_GET['order']) : 'asc';
        $limit = (!empty($_GET['limit'])) ? intval($_GET['limit']) : $_CONFIG['corePagingLimit'];
        if(!in_array($field, $fieldValues) && ( $order != 'asc' || $order != 'desc' )){
            die();
        }

        $searchTerm = (!empty($_REQUEST['search']) && !empty($_REQUEST['searchField'])) ? " LIKE '%".contrexx_addslashes($_REQUEST['search'])."%'" : ' TRUE';
        $searchField = (!empty($_REQUEST['searchField']) && !empty($_REQUEST['search'])) ? ' WHERE '.contrexx_addslashes($_REQUEST['searchField']) : ' WHERE ';

        $query = "    SELECT     `contact`.`id` as contact_id , `email` , `name` , `firstname` , `street` , `zip` , `location` ,
                            `company` , `telephone` , `telephone_office` , `telephone_mobile` , `purchase` ,
                            `funding` ,  `comment` , `contact`.`hotel_id` , `timestamp`, content1.fieldvalue AS hotel_header, content2.fieldvalue AS hotel_address, content3.fieldvalue AS hotel_location
                    FROM `".DBPREFIX."module_hotel_contact` AS contact
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = contact.hotel_id
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'headline'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = contact.hotel_id
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'adresse'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content3 ON content3.hotel_id = contact.hotel_id
                        AND content3.lang_id =1
                        AND content3.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )"
                    .$searchField." ".$searchTerm;
                    if($_REQUEST['ignore_timespan'] !== 'on' && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    if($hotelID > 0){
                        $query .= " AND contact.hotel_id = $hotelID";
                    }
                    $query .= " ORDER BY ".$field." ".$order;

        $objRS = $objDatabase->SelectLimit($query, $limit);
        $limit = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        $contacts = '';
        for($i=0; $i<$limit; $i++){
            $contacts .= 'contacts['.$i.'] = { ';
            //escape string and replace space escape
            $contacts .= 'hotel_id:"'.str_replace('+',' ', urlencode($objRS->fields['hotel_id']))."\",";
            $contacts .= 'contact_id:"'.str_replace('+',' ', urlencode($objRS->fields['contact_id']))."\",";
            $contacts .= 'email:"'.str_replace('+',' ', urlencode($objRS->fields['email']))."\",";
            $contacts .= 'name:"'.str_replace('+',' ', urlencode($objRS->fields['name']))."\",";
            $contacts .= 'firstname:"'.str_replace('+',' ', urlencode($objRS->fields['firstname']))."\",";
            $contacts .= 'company:"'.str_replace('+',' ', urlencode($objRS->fields['company']))."\",";
            $contacts .= 'street:"'.str_replace('+',' ', urlencode($objRS->fields['street']))."\",";
            $contacts .= 'zip:"'.str_replace('+',' ', urlencode($objRS->fields['zip']))."\",";
            $contacts .= 'location:"'.str_replace('+',' ', urlencode($objRS->fields['location']))."\",";
            $contacts .= 'telephone:"'.str_replace('+',' ', urlencode($objRS->fields['telephone']))."\",";
            $contacts .= 'telephone_office:"'.str_replace('+',' ', urlencode($objRS->fields['telephone_office']))."\",";
            $contacts .= 'telephone_mobile:"'.str_replace('+',' ', urlencode($objRS->fields['telephone_mobile']))."\",";
            $contacts .= 'purchase:"'.str_replace('+',' ', urlencode($objRS->fields['purchase']))."\",";
            $contacts .= 'funding:"'.str_replace('+',' ', urlencode($objRS->fields['funding']))."\",";
            $contacts .= 'comment:"'.str_replace('+',' ', urlencode($objRS->fields['comment']))."\",";
            $contacts .= 'timestamp:"'.str_replace('+',' ', urlencode(date(ASCMS_DATE_FORMAT, $objRS->fields['timestamp'])))."\"};\n\n";
            $objRS->MoveNext();
        }
        die($contacts);
    }

    /**
     * remote scripting overview listing
     *
     * @return JSON object
     */
    function _RPCOverview()
    {
        global $_CONFIG, $objDatabase, $_ARRAYLANG;

        $fieldValues = array('hotel_id', 'reference', 'ref_nr_note', 'visibility', 'address', 'location', 'object_type', 'new_building', 'property_type', 'foreigner_authorization', 'special_offer');
        $field = (!empty($_REQUEST['field'])) ? contrexx_addslashes($_REQUEST['field']) : 'timestamp';
        $searchterm = (!empty($_REQUEST['searchTerm'])) ? contrexx_addslashes($_REQUEST['searchTerm']) : '';
        $logo = (!empty($_REQUEST['logo'])) ? contrexx_addslashes($_REQUEST['logo']) : '';
        $order = (!empty($_REQUEST['order'])) ? contrexx_addslashes($_REQUEST['order']) : 'asc';
        $limit = (!empty($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : $_CONFIG['corePagingLimit'];

        $order_fields = explode(",", $field);
        $order_order = explode(",", $order);

        //create order by query
        $orderby = '';
        foreach (array_keys($order_fields) as $key){
            if(!in_array($order_fields[$key], $fieldValues) && ( $order_order[$key] != 'asc' || $order_order[$key] != 'desc' )){
                die();
            }
            if(count($order_fields)-1 != $key){
                $orderby .= $order_fields[$key]." ".$order_order[$key].",";
            } else {
                $orderby .= $order_fields[$key]." ".$order_order[$key];
            }
        }

        $query = "  SELECT hotel.id as `hotel_id`,
                        hotel.reference AS `reference`,
                        hotel.ref_nr_note,
                        hotel.object_type AS object_type,
                        hotel.new_building AS `new_building`,
                        hotel.property_type AS `property_type`,
                        hotel.visibility,
                        hotel.special_offer,
                        a.fieldvalue AS foreigner_authorization,
                        b.fieldvalue AS location,
                        c.fieldvalue AS address
                    FROM ".DBPREFIX."module_hotel AS hotel
                    LEFT JOIN ".DBPREFIX."module_hotel_content AS a ON ( hotel.id = a.hotel_id
                                                                    AND a.field_id = (
                                                                        SELECT field_id
                                                                        FROM ".DBPREFIX."module_hotel_fieldname
                                                                        WHERE name = 'ausl�nder-bewilligung'
                                                                        AND lang_id = 1 )
                                                                    AND a.lang_id = 1 )
                    LEFT JOIN ".DBPREFIX."module_hotel_content AS b ON ( hotel.id = b.hotel_id
                                                                    AND b.field_id = (
                                                                        SELECT field_id
                                                                        FROM ".DBPREFIX."module_hotel_fieldname
                                                                        WHERE name = 'ort'
                                                                        AND lang_id = 1 )
                                                                    AND b.lang_id = 1 )
                       LEFT JOIN ".DBPREFIX."module_hotel_content AS c ON ( hotel.id = c.hotel_id
                                                                    AND c.field_id = (
                                                                        SELECT field_id
                                                                        FROM ".DBPREFIX."module_hotel_fieldname
                                                                        WHERE name = 'adresse'
                                                                        AND lang_id = 1 )
                                                                    AND c.lang_id = 1 )
                    ORDER BY $orderby";

        $keys1 = array_filter(array_keys($_ARRAYLANG), array(&$this,"filterHotelType"));
        foreach ($keys1 as $key) {
            $keys[$key] = $_ARRAYLANG[$key];
        }
        array_walk($keys, array(&$this, 'arrStrToLower'));
        if (!empty($searchterm)) {
            $query = "  SELECT hotel.id AS `hotel_id` , hotel.reference AS `reference`, hotel.ref_nr_note, hotel.object_type AS object_type, hotel.new_building AS `new_building` , hotel.property_type AS property_type, hotel.special_offer, hotel.visibility, c.fieldvalue AS address, a.fieldvalue AS foreigner_authorization, b.fieldvalue AS location
                        FROM ".DBPREFIX."module_hotel AS hotel, ".DBPREFIX."module_hotel_content AS content
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS a ON ( hotel.id = a.hotel_id
                                                                        AND a.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'ausl�nder-bewilligung'
                                                                            AND lang_id = 1 )
                                                                        AND a.lang_id = 1 )
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS b ON ( hotel.id = b.hotel_id
                                                                        AND b.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'ort'
                                                                            AND lang_id = 1 )
                                                                        AND b.lang_id = 1 )
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS c ON ( hotel.id = c.hotel_id
                                                                        AND c.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'adresse'
                                                                            AND lang_id = 1 )
                                                                        AND c.lang_id = 1 )
                        WHERE content.hotel_id = hotel.id";

                        if(!empty($searchterm) && intval($searchterm) == 0){
                            $query .= " AND content.fieldvalue LIKE '%".$searchterm."%'";
                        }else if (!empty($searchterm)){
                            $query .= " AND hotel.reference LIKE '%".$searchterm."%'";
                        }

                        if(!empty($logo)){
                            $query .= " AND hotel.logo = '$logo'";
                        }

                        $query .= " AND content.lang_id =1
                        GROUP BY hotel.id
                        ORDER BY $orderby";

        }
        $objRS = $objDatabase->SelectLimit($query, $limit);
        $limit = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        $objects = '';
        for($i=0; $i<$limit; $i++){
            $objects .= 'objects['.$i.'] = { ';
            //escape string and replace space escape
            $objects .= 'hotel_id:"'.str_replace('+',' ', urlencode($objRS->fields['hotel_id']))."\",";
            $objects .= 'reference:"'.str_replace('+',' ', urlencode($objRS->fields['reference']))."\",";
            $objects .= 'ref_nr_note:"'.str_replace('+',' ', urlencode($objRS->fields['ref_nr_note']))."\",";
            $objects .= 'address:"'.str_replace('+',' ', urlencode($objRS->fields['address']))."\",";
            $objects .= 'visibility:"'.str_replace('+',' ', urlencode($_ARRAYLANG['TXT_HOTEL_'.strtoupper($objRS->fields['visibility'])]))."\",";
            $objects .= 'otype:"'.str_replace('+',' ', urlencode($_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_'.strtoupper($objRS->fields['object_type'])]))."\",";
            $objects .= 'ptype:"'.str_replace('+',' ', urlencode($_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE_'.strtoupper($objRS->fields['property_type'])]))."\",";
            $objects .= 'newobj:"'.(($objRS->fields['new_building']) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'])."\",";
            $objects .= 'so:"'.(($objRS->fields['special_offer']) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'])."\",";
            $objects .= 'fa:"'.str_replace('+',' ', urlencode($objRS->fields['foreigner_authorization']))."\",";
            $objects .= 'location:"'.str_replace('+',' ', urlencode($objRS->fields['location']))."\"};\n\n";
            $objRS->MoveNext();
        }
        die($objects);
    }

    /**
     * remote scripting pageview stats listing
     *
     * @return JSON object
     */
    function _RPCSort()
    {
        global $_CONFIG, $objDatabase;

        $fieldValues = array('visits', 'reference', 'ref_note', 'header', 'location');
        $field = (!empty($_GET['field'])) ? contrexx_addslashes($_GET['field']) : 'visits';
        $order = (!empty($_GET['order'])) ? contrexx_addslashes($_GET['order']) : 'asc';
        $limit = (!empty($_GET['limit'])) ? intval($_GET['limit']) : $_CONFIG['corePagingLimit'];
        $pos = (!empty($_GET['pos'])) ? intval($_GET['pos']) : 0;
        if(!in_array($field, $fieldValues) && ( $order != 'asc' || $order != 'desc' )){
            die();
        }

        $query = "    SELECT page, visits, content1.fieldvalue AS header, content2.fieldvalue AS location, hotel.reference as reference, hotel.ref_nr_note as ref_note
                    FROM `".DBPREFIX."stats_requests`
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content1 ON content1.hotel_id = CAST(MID(`page`, 40, 8 ) AS UNSIGNED)
                        AND content1.lang_id =1
                        AND content1.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'headline'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel_content` AS content2 ON content2.hotel_id = CAST(MID(`page`, 40, 8) AS UNSIGNED)
                        AND content2.lang_id =1
                        AND content2.field_id = (
                            SELECT field_id
                            FROM `".DBPREFIX."module_hotel_fieldname` AS fname
                            WHERE lower( name ) = 'ort'
                            AND fname.lang_id =1
                        )
                    LEFT JOIN `".DBPREFIX."module_hotel` AS hotel ON hotel.id = CAST(MID(`page`, 40, 8) AS UNSIGNED)
                    WHERE page LIKE '/index.php?section=hotel&cmd=showObj&id=%'";
                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    $query .= "
                    GROUP BY reference
                    ORDER BY ".$field." ".$order;

        $objRS        = $objDatabase->SelectLimit($query, $limit, $pos);
        $limit         = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        $requests    = '';
        for($i=0; $i<$limit; $i++){
            $requests .= 'requests['.$i.'] = { ';
            //escape string and replace space escape
            $requests .= '"visits":"'.str_replace('+',' ', urlencode($objRS->fields['visits']))."\",";
            $requests .= '"reference":"'.str_replace('+',' ', urlencode($objRS->fields['reference']))."\",";
            $requests .= '"ref_note":"'.str_replace('+',' ', urlencode($objRS->fields['ref_note']))."\",";
            $requests .= '"header":"'.str_replace('+',' ', urlencode($objRS->fields['header']))."\",";
            $requests .= '"page":"'.str_replace('+',' ', urlencode($objRS->fields['page']))."\",";
            $requests .= '"location":"'.str_replace('+',' ', urlencode($objRS->fields['location']))."\"};\n\n";
            $objRS->MoveNext();
        }
        die($requests);
    }

    /**
     * remote scripting download stats listing
     *
     * @return JSON object
     */
    function _RPCDownloadStats()
    {
        global $_CONFIG, $objDatabase;

        $fieldValues = array('dl_count', 'dl_reference', 'dl_ref_note', 'dl_header', 'dl_linkname');
        $field = (!empty($_GET['field'])) ? contrexx_addslashes($_GET['field']) : 'dl_count';
        $order = (!empty($_GET['order'])) ? contrexx_addslashes($_GET['order']) : 'asc';
        $limit = (!empty($_GET['limit'])) ? intval($_GET['limit']) : $_CONFIG['corePagingLimit'];
        $pos = (!empty($_GET['pos'])) ? intval($_GET['pos']) : 0;
        if(!in_array($field, $fieldValues) && ( $order != 'asc' || $order != 'desc' )){
            die();
        }
        $query = "    SELECT count( 1 ) AS dl_count, hotel.reference as dl_reference, hotel.ref_nr_note as dl_ref_note, a.fieldvalue AS dl_header, b.name AS dl_linkname, hotel.id as dl_hotelid
                    FROM `".DBPREFIX."module_hotel_contact`
                        AS contact
                    LEFT JOIN ".DBPREFIX."module_hotel
                        AS hotel
                        ON ( contact.hotel_id = hotel.id )
                    LEFT JOIN ".DBPREFIX."module_hotel_content
                        AS a
                        ON ( a.hotel_id = contact.hotel_id )
                    LEFT JOIN ".DBPREFIX."module_hotel_fieldname
                        AS fn
                        ON ( a.field_id = fn.field_id )
                    LEFT JOIN ".DBPREFIX."module_hotel_fieldname
                        AS b
                        ON ( b.field_id = contact.field_id )
                    WHERE fn.name = 'headline'";


                    if(empty($_REQUEST['ignore_timespan']) && !empty($_SESSION['hotel']['startDate'])){
                        $query .= " AND `timestamp` BETWEEN ".strtotime($_SESSION['hotel']['startDate'])." AND ".strtotime($_SESSION['hotel']['endDate']);
                    }
                    $query .= " AND fn.lang_id =1
                    AND a.lang_id = 1
                    AND b.lang_id = 1
                    GROUP BY contact.hotel_id
                    ORDER BY ".$field." ".$order;
        $objRS        = $objDatabase->SelectLimit($query, $limit, $pos);
        $limit         = ($limit > $objRS->RecordCount()) ? $objRS->RecordCount() : $limit;
        $requests    = '';
        for($i=0; $i<$limit; $i++){
            $requests .= 'requests['.$i.'] = { ';
            //escape string and replace space escape
            $requests .= '"dl_count":"'.str_replace('+',' ', urlencode($objRS->fields['dl_count']))."\",";
            $requests .= '"dl_reference":"'.str_replace('+',' ', urlencode($objRS->fields['dl_reference']))."\",";
            $requests .= '"dl_ref_note":"'.str_replace('+',' ', urlencode($objRS->fields['dl_ref_note']))."\",";
            $requests .= '"dl_header":"'.str_replace('+',' ', urlencode($objRS->fields['dl_header']))."\",";
            $requests .= '"dl_hotelid":"'.str_replace('+',' ', urlencode($objRS->fields['dl_hotelid']))."\",";
            $requests .= '"dl_linkname":"'.str_replace('+',' ', urlencode($objRS->fields['dl_linkname']))."\"};\n\n";
            $objRS->MoveNext();
        }
        die($requests);
    }

    /**
     * shows the object overview
     *
     * @return void
     */
    function _showOverview()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_OVERVIEW'];
        $this->_objTpl->loadTemplateFile('module_hotel_overview.html');

        $limit = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : $this->arrSettings['latest_entries_count'];
        $pos = !empty($_REQUEST['pos']) ? intval($_REQUEST['pos']) : 0;
        $field = (!empty($_REQUEST['field'])) ? contrexx_addslashes($_REQUEST['field']) : 'hotel_id';
// Unused
//        $order = (!empty($_REQUEST['order'])) ? contrexx_addslashes($_REQUEST['order']) : 'asc';
        $hsearchField = !empty($_REQUEST['searchField']) ? $_REQUEST['searchField'] : '' ;
// Unused
//        $hsearch = !empty($_REQUEST['search']) ? contrexx_addslashes($_REQUEST['search']) : '' ;

        $rowclass = 2;
        $queryAll = "  SELECT hotel.id as `hotel_id`,
                            hotel.reference AS `ref`,
                            hotel.ref_nr_note,
                            hotel.object_type AS otype,
                            hotel.new_building AS `new`,
                            hotel.property_type AS ptype,
                            hotel.visibility,
                            hotel.special_offer,
                            a.fieldvalue AS foreigner_authorization,
                            b.fieldvalue AS location,
                            c.fieldvalue AS address
                        FROM ".DBPREFIX."module_hotel AS hotel
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS a ON ( hotel.id = a.hotel_id
                                                                        AND a.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'ausl�nder-bewilligung'
                                                                            AND lang_id = 1 )
                                                                        AND a.lang_id = 1 )
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS b ON ( hotel.id = b.hotel_id
                                                                        AND b.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'ort'
                                                                            AND lang_id = 1 )
                                                                        AND b.lang_id = 1 )
                           LEFT JOIN ".DBPREFIX."module_hotel_content AS c ON ( hotel.id = c.hotel_id
                                                                        AND c.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'adresse'
                                                                            AND lang_id = 1 )
                                                                        AND c.lang_id = 1 )

                        ORDER BY hotel.id DESC";

        $keys1 = array_filter(array_keys($_ARRAYLANG), array(&$this,"filterHotelType"));
        foreach ($keys1 as $key) {
            $keys[$key] = $_ARRAYLANG[$key];
        }
        array_walk($keys, array(&$this, 'arrStrToLower'));
        if (!empty($_REQUEST['search'])) {
            $searchterm = contrexx_addslashes(strip_tags($_POST['searchterm'], ENT_QUOTES));
            $logo = contrexx_addslashes(strip_tags($_POST['logo'], ENT_QUOTES));
            $query = "  SELECT hotel.id AS `hotel_id`, hotel.reference AS `ref`, hotel.ref_nr_note, hotel.object_type AS otype, hotel.new_building AS `new` , hotel.property_type AS ptype, hotel.special_offer, hotel.visibility, c.fieldvalue AS address, a.fieldvalue AS foreigner_authorization, b.fieldvalue AS location
                        FROM ".DBPREFIX."module_hotel AS hotel, ".DBPREFIX."module_hotel_content AS content
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS a ON ( hotel.id = a.hotel_id
                                                                        AND a.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'ausl�nder-bewilligung'
                                                                            AND lang_id = 1 )
                                                                        AND a.lang_id = 1 )
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS b ON ( hotel.id = b.hotel_id
                                                                        AND b.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'ort'
                                                                            AND lang_id = 1 )
                                                                        AND b.lang_id = 1 )
                        LEFT JOIN ".DBPREFIX."module_hotel_content AS c ON ( hotel.id = c.hotel_id
                                                                        AND c.field_id = (
                                                                            SELECT field_id
                                                                            FROM ".DBPREFIX."module_hotel_fieldname
                                                                            WHERE name = 'adresse'
                                                                            AND lang_id = 1 )
                                                                        AND c.lang_id = 1 )
                        WHERE content.hotel_id = hotel.id";

                        if(intval($searchterm) == 0){
                            $query .= " AND content.fieldvalue LIKE '%".$searchterm."%'";
                        }else{
                            $query .= " AND  hotel.reference LIKE '%".$searchterm."%'";
                        }

                        $query .= " AND hotel.logo = '$logo'
                        AND content.lang_id =1
                        GROUP BY hotel.id
                        ORDER BY hotel.id DESC";

            $objResult = $objDatabase->Execute($query);



            if ($objResult->RecordCount() == 0) {
                $objResult = $objDatabase->SelectLimit($queryAll, $this->arrSettings['latest_entries_count']);
                $listTitle = $_ARRAYLANG['TXT_HOTEL_LATEST_ENTRIES'];
                $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_NO_RESULTS'];
                $searchterm = $logo = "";
            } else {
                $listTitle = $_ARRAYLANG['TXT_HOTEL_SEARCH_RESULTS'];
            }
        }else {
            $objResult = $objDatabase->SelectLimit($queryAll, $this->arrSettings['latest_entries_count']);
            $listTitle = $_ARRAYLANG['TXT_HOTEL_LATEST_ENTRIES'];
        }

        $this->_objTpl->setVariable(array(
            'TXT_HOTEL_SEARCH'        => $_ARRAYLANG['TXT_HOTEL_SEARCH'],
            'TXT_HOTEL_LIST_TITLE'    => $listTitle,
        ));

        // If entries should be shown
        if ($objResult->RecordCount() > 0) {
            $this->_objTpl->setVariable(array(
                'TXT_HOTEL_REF_ID'                       =>    $_ARRAYLANG['TXT_HOTEL_REFERENCE_NUMBER_SHORT'],
                'TXT_HOTEL_OBJECT_TYPE'                  =>    $_ARRAYLANG['TXT_HOTEL_OBJECT_TYPE'],
                'TXT_HOTEL_NEW_BUILDING'                 =>    $_ARRAYLANG['TXT_HOTEL_NEW_BUILDING_SHORT'],
                'TXT_HOTEL_PROPERTY_TYPE'               =>    $_ARRAYLANG['TXT_HOTEL_PROPERTY_TYPE_SHORT'],
                'TXT_HOTEL_REF_NOTE'                   =>    $_ARRAYLANG['TXT_HOTEL_REF_NOTE'],
                'TXT_HOTEL_ADDRESS'                    =>    $_ARRAYLANG['TXT_HOTEL_ADDRESS'],
                'TXT_HOTEL_FUNCTIONS'                  =>    $_ARRAYLANG['TXT_HOTEL_FUNCTIONS'],
                'TXT_HOTEL_DELETE'                    =>    $_ARRAYLANG['TXT_HOTEL_DELETE'],
                'TXT_HOTEL_EDIT'                        =>    $_ARRAYLANG['TXT_HOTEL_EDIT'],
                'TXT_HOTEL_COPY'                        =>    $_ARRAYLANG['TXT_HOTEL_COPY'],
                'TXT_HOTEL_CONFIRM_DELETE_OBJECT'    =>    $_ARRAYLANG['TXT_HOTEL_CONFIRM_DELETE_OBJECT'],
                'TXT_HOTEL_CANNOT_UNDO_OPERATION'    =>    $_ARRAYLANG['TXT_HOTEL_CANNOT_UNDO_OPERATION'],
                'TXT_HOTEL_LOCATION'                    =>    $_ARRAYLANG['TXT_HOTEL_LOCATION'],
                'TXT_HOTEL_VISIBLE'                    =>    $_ARRAYLANG['TXT_HOTEL_VISIBLE'],
                'TXT_HOTEL_FOREIGNER_AUTHORIZATION'    =>    $_ARRAYLANG['TXT_HOTEL_FOREIGNER_AUTHORIZATION'],
                'TXT_HOTEL_SPECIAL_OFFER'            =>    $_ARRAYLANG['TXT_HOTEL_SPECIAL_OFFER'],
                'HOTEL_LOGO_'.strtoupper($logo).'_SELECTED'    =>    'selected="selected"',
                'HOTEL_PAGING_LIMIT'                     =>    $limit,
                'HOTEL_PAGING_POS'                     =>    $pos,
                'HOTEL_PAGING_FIELD'                     =>    $field,
                'HOTEL_HSEARCH_FIELD'                 =>    $hsearchField,
                'HOTEL_HSEARCH'                         =>    $searchterm,
                'HOTEL_HLOGO'                         =>    $logo,
            ));

            while (!$objResult->EOF) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_ID'                       => !empty($objResult->fields['hotel_id']) ? $objResult->fields['hotel_id'] : '&nbsp;',
                    'HOTEL_REF_ID'                   => !empty($objResult->fields['ref']) ? $objResult->fields['ref'] : '&nbsp;',
                    'HOTEL_REF_NR_NOTE'              => !empty($objResult->fields['ref_nr_note']) ? $objResult->fields['ref_nr_note'] : '&nbsp;',
                    'HOTEL_OBJECT_TYPE'              => $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_'.strtoupper($objResult->fields['otype'])],
                    'HOTEL_NEW_BUILDING'             => ($objResult->fields['new']) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                    'HOTEL_PROPERTY_TYPE'            => $_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE_'.strtoupper($objResult->fields['ptype'])],
                    'HOTEL_ADDRESS'                  => $objResult->fields['address'],
                    'HOTEL_LOCATION'                    => $objResult->fields['location'],
                    'HOTEL_VISIBILITY'                => $_ARRAYLANG['TXT_HOTEL_'.strtoupper($objResult->fields['visibility'])],
                    'HOTEL_LOCATION'                    => !empty($objResult->fields['location']) ? $objResult->fields['location'] : '&nbsp;',
                    'HOTEL_SPECIAL_OFFER'            => $objResult->fields['special_offer'] == 1 ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                    'HOTEL_FOREIGNER_AUTHORIZATION'    => !empty($objResult->fields['foreigner_authorization']) ? $objResult->fields['foreigner_authorization'] : '&nbsp;',
                    'ROW_CLASS'                        => 'row'.(($rowclass++ % 2 == 0) ? 1 : 2),
                ));

                $this->_objTpl->parse("row");
                $objResult->MoveNext();
//                $objRSLoc->MoveNext();
            }

            $this->_objTpl->parse("entriesList");
        }


    }

    /**
     * copy an object
     *
     * @return void
     */
    function _copy()
    {
        global $objDatabase, $_ARRAYLANG;
        $hotelID = intval($_GET['id']);
        if(empty($hotelID)){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_NO_ID_SPECIFIED'];
        }else{
            $query = "    INSERT INTO ".DBPREFIX."module_hotel(
SELECT '', reference, ref_nr_note, logo, special_offer, visibility, object_type, new_building, property_type, longitude, latitude, zoom
FROM ".DBPREFIX."module_hotel
WHERE id = $hotelID )";
            if($objDatabase->Execute($query)){
                $lastInsertedID = $objDatabase->Insert_ID();
                $objDatabase->Execute("    UPDATE ".DBPREFIX."module_hotel_settings set setvalue = $lastInsertedID
                                            WHERE setname = 'last_inserted_hotel_id'");
                $query = "    INSERT INTO ".DBPREFIX."module_hotel_content    (
                                SELECT '', '$lastInsertedID', lang_id, field_id, fieldvalue, `active`
                                FROM ".DBPREFIX."module_hotel_content WHERE hotel_id = $hotelID
                            )";
                $objDatabase->Execute($query);

                $query = "    INSERT INTO ".DBPREFIX."module_hotel_image (
                                SELECT '', '$lastInsertedID', field_id, uri
                                FROM ".DBPREFIX."module_hotel_image
                                WHERE hotel_id = $hotelID )";
                if($objDatabase->Execute($query)){
                    if(!file_exists(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images'.DS.($lastInsertedID+1))){
                        $this->_objFile->mkDir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images'.DS, ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'images'.DS, ($lastInsertedID+1));
                        $this->_objFile->mkDir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'pdfs'.DS, ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'pdfs'.DS, ($lastInsertedID+1));
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_COPIED'];
                }
            }else{
                $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR']." ". $objDatabase->ErrorMsg();
            }
        }
    }

    /**
     * deletes an object
     *
     * @return void
     */
    function _del(){
        global $objDatabase, $_ARRAYLANG;
        $hotelID = intval($_GET['id']);
        if(empty($hotelID)){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_NO_ID_SPECIFIED'];
        }else{
            if($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_hotel WHERE id = $hotelID")){
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_hotel_content WHERE hotel_id = $hotelID");
                if($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_hotel_image WHERE hotel_id = $hotelID")){
                    $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_DELETED'];
                }
            }else{
                $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'];
            }
        }
    }

    /**
     * adds a new language (not yet implemented)
     * @return void
     */
    function _addlanguage()
    {
        print_r($_REQUEST);
        $this->_showSettings();
    }

    /**
     * deletes a custom field
     *
     * @return bool
     */
    function _delfield()
    {
        global $objDatabase, $_ARRAYLANG;
        if(!empty($_GET['id'])){
            $fieldID = intval($_GET['id']);
        }

        $query = "    SELECT field_id from ".DBPREFIX."module_hotel_fieldname
                    WHERE `name` = 'Adresse' LIMIT 1";
        $objRS = $objDatabase->Execute($query);
        if(!$objRS){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
            $this->_showSettings();
            return false;
        }else{
            if($objRS->RecordCount() != 1){
                $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_ADDRESSFIELD_MISSING'];
                $this->_showSettings();
                return false;
            }
        }

        $fieldIdAddress = $objRS->fields['field_id'];
        if($fieldID == $fieldIdAddress){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_THIS_FIELD_CANNOT_BE_DELETED'];
            $this->_showSettings();
            return false;
        }
        if(
            $objDatabase->Execute("    DELETE FROM ".DBPREFIX."module_hotel_field
                                    WHERE id=$fieldID")
            !== false
        &&    $objDatabase->Execute("    DELETE FROM ".DBPREFIX."module_hotel_fieldname
                                    WHERE field_id=$fieldID")
            !== false
        &&    $objDatabase->Execute("    DELETE FROM ".DBPREFIX."module_hotel_image
                                    WHERE field_id=$fieldID")
            !== false
        &&    $objDatabase->Execute("    DELETE FROM ".DBPREFIX."module_hotel_content
                                    WHERE field_id=$fieldID")
            !== false)
        {
            $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_DELETED'];
        }else{
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
        }
        $this->_showSettings();
        return true;
    }


    /**
     * remote scripting field content suggestion
     *
     * @return JSON object
     */
    function _RPCGetSuggest(){
        global $objDatabase;
        $sugg = '';
        $fieldID = intval($_GET['fieldid']);
        $langID = intval($_GET['langid']);
        $value = contrexx_addslashes($_GET['value']);
// Unused
//        $ID = contrexx_addslashes($_GET['ID']);
        $objRS = $objDatabase->SelectLimit("SELECT fieldvalue AS suggestion
                                        FROM ".DBPREFIX."module_hotel_content
                                        WHERE field_id = $fieldID
                                        AND lang_id = $langID
                                        AND fieldvalue LIKE '%$value%'
                                        GROUP BY fieldvalue
                                        ORDER BY fieldvalue", 30, 0);
        if($objRS){
            $i = 0;
            //build JSON object with suggestions and needed IDs
            $sugg = 'sugg = { ';
            while(!$objRS->EOF){
                //escape string and replace space escape
                $sugg .= $i.':"'.str_replace('+',' ', urlencode($objRS->fields['suggestion'])).'",';
                $objRS->MoveNext();
                $i++;
            }
            $sugg = substr($sugg,0,-1); //remove trailing comma
            $sugg .= "};\n";
            die($sugg);
        }

    }




    /**
     * Add or edit an object
     *
     * @return void
     */
    function _add() {
        global $objDatabase, $_ARRAYLANG;
        $hotelID = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

        $error = false;
        $okStatus = "";
        if (isset($_POST['sent'])) {
            $reference = (!empty($_POST['ref_nr'])) ? intval($_POST['ref_nr']) : "";
            $reference_note = (!empty($_POST['ref_nr_note'])) ? contrexx_addslashes(strip_tags($_POST['ref_nr_note'], ENT_QUOTES)) : "";
            $logo = (!empty($_POST['logo'])) ? contrexx_addslashes(strip_tags($_POST['logo'], ENT_QUOTES)) : "";
            $special_offer = (!empty($_POST['special_offer'])) ? contrexx_addslashes(strip_tags($_POST['special_offer'], ENT_QUOTES)) : "";
            $visibility = (!empty($_POST['visibility'])) ? contrexx_addslashes(strip_tags($_POST['visibility'], ENT_QUOTES)) : "";
            $object_type = (!empty($_POST['obj_type'])) ? contrexx_addslashes(strip_tags($_POST['obj_type'], ENT_QUOTES)) : "";
            $new_building = (!empty($_POST['new_building'])) ? contrexx_addslashes(strip_tags($_POST['new_building'], ENT_QUOTES)) : "";
            $property_type = (!empty($_POST['property_type'])) ? contrexx_addslashes(strip_tags($_POST['property_type'], ENT_QUOTES)) : "";
            $zoom = (!empty($_POST['zoom'])) ? contrexx_addslashes(strip_tags($_POST['zoom'])) : "";

            if (!empty($_POST['longitude'])) {
                $longitude = contrexx_addslashes(strip_tags($_POST['longitude'])).".";
                if (empty($_POST['longitude_fraction'])) {
                    $longitude .= "0";
                } else {
                    $longitude .= contrexx_addslashes(strip_tags($_POST['longitude_fraction']));
                }
            } else {
                $longitude = "0";
            }

            if (!empty($_POST['latitude'])) {
                $latitude = contrexx_addslashes(strip_tags($_POST['latitude'])).".";
                if (empty($_POST['latitude_fraction'])) {
                    $latitude .= "0";
                } else {
                    $latitude .= contrexx_addslashes(strip_tags($_POST['latitude_fraction']));
                }
            } else {
                $latitude = "";
            }

            if($hotelID > 0){
                $query = "  UPDATE ".DBPREFIX."module_hotel
                            SET
                                `reference` =  '".$reference."',
                                `ref_nr_note` =  '".$reference_note."',
                                `logo` =  '".$logo."',
                                `special_offer` =  '".$special_offer."',
                                `visibility` =  '".$visibility."',
                                `object_type` =  '".$object_type."',
                                `new_building` = '".$new_building."',
                                `property_type` = '".$property_type."',
                                `longitude` =  '".$longitude."',
                                `latitude` = '".$latitude."',
                                `zoom` = '".$zoom."'
                            WHERE `id` = '".$hotelID."'";
                if ($objDatabase->Execute($query)) {
                    $this->_getFieldNames($hotelID);
                    foreach ($this->fieldNames as $fieldkey => $field) {
                            $_POST['active'][$fieldkey] = ((isset($_POST['active'][$fieldkey]) || $field['mandatory']) ? 1 : 0);
                            $field['content']['active'] = ((!empty($field['content']['active']) || $field['mandatory']) ? 1 : 0);
                            foreach ($this->languages as $langId => $lang) {
                                $value = $_POST['field_'.$fieldkey.'_'.$langId];
                                if ( ((CONTREXX_ESCAPE_GPC) ? stripslashes($value) : $value) != ((!empty($field['content'][$langId])) ? $field['content'][$langId] : '') ||
                                        $_POST['active'][$fieldkey] != $field['content']['active']) {
                                    $value = contrexx_addslashes(strip_tags($value, ENT_QUOTES));
                                    $query = "  UPDATE ".DBPREFIX."module_hotel_content
                                                SET `fieldvalue`= '".$value."',
                                                `active` = '".(($_POST['active'][$fieldkey] || $field['mandatory']) ? 1 : 0)."'
                                                WHERE `field_id` = '".$fieldkey."'
                                                AND `lang_id` = '".$langId."'
                                                AND `hotel_id` = '".$hotelID."'
                                                ";
                                                //hotelid?
                                    if (!$objDatabase->Execute($query)) {
                                        $error = true;
                                    }
                                    if($objDatabase->Affected_Rows() == 0){ // field doesnt exists yet
                                        $query = "  INSERT INTO ".DBPREFIX."module_hotel_content
                                            (
                                                `hotel_id`,
                                                `lang_id`,
                                                `field_id`,
                                                `fieldvalue`,
                                                `active`
                                            ) VALUES (
                                                '".$hotelID."',
                                                '".$langId."',
                                                '".$fieldkey."',
                                                '".$value."',
                                                '".(($_POST['active'][$fieldkey] || $field['mandatory']) ? 1 : 0)."'
                                            )";
                                                //hotelid?
                                        if (!$objDatabase->Execute($query)) {
                                            $error = true;
                                        }
                                    }
                                }
                            }

                                 /***
                                    type: LINKS
                                ***/



                            if ($field['type'] == "img" || $field['type'] == "panorama") {
                                $value = (isset($_POST['hi_'.$fieldkey])) ? contrexx_addslashes(strip_tags($_POST['hi_'.$fieldkey], ENT_QUOTES)) : "";
                                if(empty($value)){continue;}  //ignore empty
                                if($field['img'] == $_POST['hi_'.$fieldkey]){continue;} //ignore if no changes were made
                                $query = "  UPDATE ".DBPREFIX."module_hotel_image
                                            SET `uri` = '".$value."'
                                            WHERE `hotel_id` = '".$hotelID."'
                                            AND `field_id` = '".$fieldkey."'
                                            ";

                                if (!$objDatabase->Execute($query)) {
                                    $error = true;
                                }
                                if($objDatabase->Affected_Rows() == 0){ // field doesnt exists yet
                                        $query = "  INSERT INTO ".DBPREFIX."module_hotel_image
                                                (   `hotel_id`,
                                                    `field_id`,
                                                    `uri`
                                                ) VALUES (
                                                    '".$hotelID."',
                                                    '".$fieldkey."',
                                                    '".$value."'
                                                )";
                                   //hotelid?
                                    if (!$objDatabase->Execute($query)) {
                                        $error = true;
                                    }
                                }
                            }
                        }

                } else {
                    $error = true;
                }
                $okStatus = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_UPDATED'];
            } else {
                $this->_getSettings();
                //new object code goes here
                $query = "  INSERT INTO ".DBPREFIX."module_hotel
                            (
                                `reference`,
                                `ref_nr_note`,
                                `logo`,
                                `special_offer`,
                                `visibility`,
                                `object_type`,
                                `new_building`,
                                `property_type`,
                                `longitude`,
                                `latitude`,
                                `zoom`
                            ) VALUES (
                                '".$reference."',
                                '".$reference_note."',
                                '".$logo."',
                                '".$special_offer."',
                                '".$visibility."',
                                '".$object_type."',
                                '".$new_building."',
                                '".$property_type."',
                                '".$longitude."',
                                '".$latitude."',
                                '".$zoom."'
                            )";


                if ($objDatabase->Execute($query)) {
                    $insertId = $objDatabase->Insert_ID();
                    $objDatabase->Execute("    UPDATE ".DBPREFIX."module_hotel_settings set setvalue = $insertId
                                            WHERE setname = 'last_inserted_hotel_id'");
                    $this->_getFieldNames();

                    foreach ($this->fieldNames as $fieldkey => $field) {
//                        if (isset($_POST['active'][$fieldkey])) {
                            foreach ($this->languages as $langId => $lang) {
                                $value = contrexx_addslashes(strip_tags($_POST['field_'.$fieldkey.'_'.$langId], ENT_QUOTES));
                                $value = ( !empty($value) ) ? $value : '';
                                $query = "  INSERT INTO ".DBPREFIX."module_hotel_content
                                            (
                                                `hotel_id`,
                                                `lang_id`,
                                                `field_id`,
                                                `fieldvalue`,
                                                `active`
                                            ) VALUES (
                                                '".$insertId."',
                                                '".$langId."',
                                                '".$fieldkey."',
                                                '".$value."',
                                                '".((isset($_POST['active'][$fieldkey]) || $field['mandatory']) ? 1 : 0)."'
                                            )";
                                if (!$objDatabase->Execute($query)) {
                                    $error = true;
                                }
//                            }
                        }

                        if ($field['type'] == "img" || $field['type'] == 'panorama') {

                            $value = (isset($_POST['hi_'.$fieldkey])) ? contrexx_addslashes(strip_tags($_POST['hi_'.$fieldkey], ENT_QUOTES)) : "";
                            $query = "  INSERT INTO ".DBPREFIX."module_hotel_image
                                        (   `hotel_id`,
                                            `field_id`,
                                            `uri`
                                        ) VALUES (
                                            '".$insertId."',
                                            '".$fieldkey."',
                                            '".$value."'
                                        )";
                            if (!$objDatabase->Execute($query)) {
                                $error = true;
                            }
                        }
                    }
                } else {
                    $error = true;
                }
                $okStatus = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_INSERTED'];
            }
        }
        if ($error) {
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
        } else {
            // OKMessage
            $this->_strOkMessage = $okStatus;
        }
    }

    /**
     * deletes all images that are no longer used by any objects
     *
     * @return void
     */
    function _deleteUnusedImages(){
        global $objDatabase, $_ARRAYLANG;
        $deleted = '';
        $images = opendir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images');
        while (($file = readdir($images)) !== false) {
            if($file == '.' || $file == '..'){continue;}
            if(is_dir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images'.DS.intval($file))){
                $objRS = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_hotel
                                                WHERE id = $file");
                if($objRS->RecordCount() == 0){
                    $this->_objFile->delDir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images'.DS, ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'images'.DS, $file);
                    $this->_objFile->delDir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'pdfs'.DS, ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'pdfs'.DS, $file);
                    $deleted .= ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'images'.DS.$file."<br />\n";
                }
            }
        }
        closedir($images);
        $this->_strOkMessage = sprintf($_ARRAYLANG['TXT_HOTEL_IMAGES_DELETED'], $deleted);
    }

    /**
     * show the hotel form for adding or editing objects
     *
     * @param int $hotelID ID of the object to edit, 0 for new objects
     */
    function _showHotelForm($hotelID=0){
        global $objDatabase, $_ARRAYLANG;
        $this->_getSettings();
        if(!empty($_GET['id']) && intval($_GET['id']) > 0){
            $hotelID = intval($_GET['id']);
            $this->_getFieldNames($hotelID);
        }else{
            $hotelID = 0;
            $this->_getFieldNames($hotelID);
        }

        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_ADD'];
        $this->_objTpl->loadTemplateFile('module_hotel_add.html');
        $this->_objTpl->setGlobalVariable(array(
            'TXT_HOTEL_CREATE_OBJECT'            =>    $_ARRAYLANG['TXT_HOTEL_CREATE_OBJECT'],
            'TXT_HOTEL_NAME'                     =>    $_ARRAYLANG['TXT_HOTEL_NAME'],
            'TXT_HOTEL_IMAGE'                     =>    $_ARRAYLANG['TXT_HOTEL_IMAGE'],
            'TXT_HOTEL_CLICK_HERE'                =>     $_ARRAYLANG['TXT_HOTEL_CLICK_HERE'],
            'TXT_HOTEL_OBJECTTYPE_FLAT'            =>     $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_FLAT'],
            'TXT_HOTEL_OBJECTTYPE_HOUSE'            =>     $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_HOUSE'],
            'TXT_HOTEL_OBJECTTYPE_MULTIFAMILY'    =>     $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_MULTIFAMILY'],
            'TXT_HOTEL_OBJECTTYPE_ESTATE'        =>     $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_ESTATE'],
            'TXT_HOTEL_OBJECTTYPE_INDUSTRY'        =>     $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_INDUSTRY'],
            'TXT_HOTEL_OBJECTTYPE_PARKING'        =>     $_ARRAYLANG['TXT_HOTEL_OBJECTTYPE_PARKING'],
            'TXT_HOTEL_OBJECT_TYPE'                =>    $_ARRAYLANG['TXT_HOTEL_OBJECT_TYPE'],
            'TXT_HOTEL_REFERENCE_NUMBER'            =>    $_ARRAYLANG['TXT_HOTEL_REFERENCE_NUMBER'],
            'TXT_HOTEL_PROPERTYTYPE_PURCHASE'    =>    $_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE_PURCHASE'],
            'TXT_HOTEL_PROPERTYTYPE_RENT'        =>    $_ARRAYLANG['TXT_HOTEL_PROPERTYTYPE_RENT'],
            'TXT_HOTEL_PROPERTY_TYPE'            =>    $_ARRAYLANG['TXT_HOTEL_PROPERTY_TYPE'],
            'TXT_HOTEL_NEW_BUILDING'                =>    $_ARRAYLANG['TXT_HOTEL_NEW_BUILDING'],
            'TXT_HOTEL_YES'                        =>    $_ARRAYLANG['TXT_HOTEL_YES'],
            'TXT_HOTEL_NO'                        =>    $_ARRAYLANG['TXT_HOTEL_NO'],
            'TXT_HOTEL_PROPERTY_TYPE'            =>    $_ARRAYLANG['TXT_HOTEL_PROPERTY_TYPE'],
            'TXT_HOTEL_DEFINE_TEXT'                =>    $_ARRAYLANG['TXT_HOTEL_DEFINE_TEXT'],
            'TXT_HOTEL_DEFINE_IMAGE'             =>  $_ARRAYLANG['TXT_HOTEL_DEFINE_IMAGE'],
            'TXT_HOTEL_LONGITUDE'                =>    $_ARRAYLANG['TXT_HOTEL_LONGITUDE'],
            'TXT_HOTEL_LATITUDE'                    =>    $_ARRAYLANG['TXT_HOTEL_LATITUDE'],
            'TXT_HOTEL_ZOOM'                        =>    $_ARRAYLANG['TXT_HOTEL_ZOOM'],
            'TXT_HOTEL_ENTER_ADDRESS'            =>    $_ARRAYLANG['TXT_HOTEL_ENTER_ADDRESS'],
            'TXT_HOTEL_SEARCH_ADDRESS'            =>    $_ARRAYLANG['TXT_HOTEL_SEARCH_ADDRESS'],
            'TXT_HOTEL_BROWSER_NOT_SUPPORTED'    =>    $_ARRAYLANG['TXT_HOTEL_BROWSER_NOT_SUPPORTED'],
            'TXT_HOTEL_DELETE'                    =>  $_ARRAYLANG['TXT_HOTEL_DELETE'],
            'HOTEL_COLUMN_NUMBER'               =>  $this->langCount+2,
            'HOTEL_COLUMN_NUMBER2'                =>    $this->langCount+1,
            'HOTEL_COLUMN_NUMBER3'                =>    $this->langCount,
            'TXT_HOTEL_SUBMIT'                    =>    ($hotelID > 0) ? $_ARRAYLANG['TXT_HOTEL_SAVE'] : $_ARRAYLANG['TXT_HOTEL_ADD'],
            'HOTEL_ID'                           =>  ($hotelID > 0) ? $hotelID : "",
            'LAST_HOTEL_ID'                      =>  ($hotelID > 0) ? $hotelID : ($this->arrSettings['last_inserted_hotel_id']+1),
            'TXT_HOTEL_TAB_IMAGES'               =>  $_ARRAYLANG['TXT_HOTEL_TAB_IMAGES'],
            'TXT_HOTEL_TAB_LINK'                   =>  $_ARRAYLANG['TXT_HOTEL_TAB_LINK'],
            'TXT_HOTEL_TAB_TEXT'                 =>  $_ARRAYLANG['TXT_HOTEL_TAB_TEXT'],
            'TXT_HOTEL_LOGO'                        =>    $_ARRAYLANG['TXT_HOTEL_LOGO'],
            'TXT_HOTEL_SPECIAL_OFFER'            =>    $_ARRAYLANG['TXT_HOTEL_SPECIAL_OFFER'],
            'TXT_HOTEL_VISIBLE'                    =>    $_ARRAYLANG['TXT_HOTEL_VISIBLE'],
            'TXT_HOTEL_DISABLED'                    =>    $_ARRAYLANG['TXT_HOTEL_DISABLED'],
            'TXT_HOTEL_REFERENCE'                =>    $_ARRAYLANG['TXT_HOTEL_REFERENCE'],
            'TXT_HOTEL_LISTING'                    =>    $_ARRAYLANG['TXT_HOTEL_LISTING'],
            'TXT_HOTEL_DISABLED'                    =>    $_ARRAYLANG['TXT_HOTEL_DISABLED'],
            'TXT_HOTEL_DEFINE_LINK'                =>    $_ARRAYLANG['TXT_HOTEL_DEFINE_LINK'],
            'TXT_HOTEL_BROWSE'                    =>    $_ARRAYLANG['TXT_HOTEL_BROWSE'],
            'TXT_HOTEL_NO_RESULTS'                =>    $_ARRAYLANG['TXT_HOTEL_NO_RESULTS'],
            'TXT_HOTEL_GET_PROPOSAL_LIST'        =>    $_ARRAYLANG['TXT_HOTEL_GET_PROPOSAL_LIST'],
            'TXT_HOTEL_MANDATORY_FIELDS_ARE_EMPTY'     =>    $_ARRAYLANG['TXT_HOTEL_MANDATORY_FIELDS_ARE_EMPTY'],
            'TXT_HOTEL_EDIT_OR_ADD_IMAGE'         =>    $_ARRAYLANG['TXT_HOTEL_EDIT_OR_ADD_IMAGE'],

        ));
        if(!file_exists(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images'.DS.($this->arrSettings['last_inserted_hotel_id']+1))){
            $this->_objFile->mkDir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'images'.DS, ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'images'.DS, ($this->arrSettings['last_inserted_hotel_id']+1));
            $this->_objFile->mkDir(ASCMS_CONTENT_IMAGE_PATH.DS.'hotel'.DS.'pdfs'.DS, ASCMS_CONTENT_IMAGE_WEB_PATH.DS.'hotel'.DS.'pdfs'.DS, ($this->arrSettings['last_inserted_hotel_id']+1));
        }

        foreach ($this->languages as $langID => $language) {
            $this->_objTpl->setVariable('TXT_HOTEL_LANGUAGE', $_ARRAYLANG[$language]);
            $this->_objTpl->parse("languages");
            $this->_objTpl->setVariable('TXT_HOTEL_LANGUAGE', $_ARRAYLANG[$language]);
            $this->_objTpl->parse("languages2");
            $this->_objTpl->setVariable('TXT_HOTEL_LANGUAGE', $_ARRAYLANG[$language]);
            $this->_objTpl->parse("languages3");
            $this->_objTpl->setVariable('HOTEL_LANGUAGE_ID', $langID);
            $this->_objTpl->parse("languageIds");
        }

        if ($hotelID > 0) {
            $query = "    SELECT * FROM ".DBPREFIX."module_hotel
                          WHERE `id` = '".$hotelID."'";
            $objResult = $objDatabase->Execute($query);

            if ($objResult) {
                $longitudeTmp = explode(".", $objResult->fields['longitude']);
                $latitudeTmp = explode(".", $objResult->fields['latitude']);

                $strSelected = "selected=\"selected\"";

                $this->_objTpl->setVariable(array(
                    'HOTEL_REF_NR'              => $objResult->fields['reference'],
                    'HOTEL_REF_NR_NOTE'          => $objResult->fields['ref_nr_note'],
                    'HOTEL_LONGITUDE'            => $longitudeTmp[0],
                    'HOTEL_LONGITUDE_FRACTION'  => $longitudeTmp[1],
                    'HOTEL_LATTITUDE'            => $latitudeTmp[0],
                    'HOTEL_LATTITUDE_FRACTION'  => $latitudeTmp[1],
                    'HOTEL_ZOOM'                 => $objResult->fields['zoom'],
                    'HOTEL_TYPE_SELECT_'.strtoupper($objResult->fields['object_type']) => $strSelected,
                    'HOTEL_NEW_SELECT_'.(($objResult->fields['new_building']) ? "YES" : "NO")  => $strSelected,
                    'HOTEL_PROPERTY_SELECT_'.strtoupper($objResult->fields['property_type']) => $strSelected,
                    'HOTEL_SPECIAL_OFFER_SELECT_'.(($objResult->fields['special_offer']) ? "YES" : "NO")  => $strSelected,
                    'HOTEL_LOGO_SELECTED_'.strtoupper($objResult->fields['logo']) => $strSelected,
                    'HOTEL_VISIBLE_SELECT_'.strtoupper($objResult->fields['visibility']) => $strSelected,
                ));
            }
        }

        $strDisabled = 'disabled="disabled"';
        $rowClassText = 2; //text & textbox
        $rowClassImg = 2;  //img
        $rowClassLnk = 2;  //lnk
        $hotelData = "";
        foreach ($this->fieldNames as $fieldkey => $field) {
            if($field['mandatory']){
                $this->_objTpl->setVariable('HOTEL_MANDATORY_ID', $fieldkey);
                $this->_objTpl->parse("mandatoryArray");
            }

            switch ($field['type']) {
                case "text":
                case "digits_only":
                case "price":
                       $rowClassText = ($rowClassText == 2) ? 1 : 2;
                    foreach ($this->languages as $langid => $language){
                        $this->_objTpl->setVariable(array(
                            "HOTEL_FIELD_TEXT_NAME"      => "field_".$fieldkey."_".$langid,
                            "HOTEL_FIELD_TEXT_VALUE"     => ($hotelID > 0) /*If we're editing*/ ? $field['content'][$langid] : "",
                            "HOTEL_DECIMAL_ONLY"            => ($field['type'] == 'digits_only') ? 'onchange="this.value=decimalOnly(this.value)"' : '',
                            'HOTEL_FIELD_ID'                =>    $fieldkey,
                            'HOTEL_FIELD_LANG_ID'        =>    $langid,
                        ));
                        $this->_objTpl->parse("text-column");
                    }

                    $this->_objTpl->setVariable(array(
                        "HOTEL_FIELD_TEXT_CAPTION"   => $field['names'][1],
                        "HOTEL_ROW"                  => $rowClassText,
                        'HOTEL_FIELD_TEXT_ID'        => $fieldkey,
                        'HOTEL_CHECKED'                => ($hotelID > 0) ? (($field['content']['active']) ? 'checked="checked"' : '' ) : ($field['mandatory'] == 1) ? 'checked="checked"' : '',
                        'HOTEL_DISABLED'                => ($field['mandatory'] == 1) ? $strDisabled : '',
                    ));
                    $this->_objTpl->parse("fieldRowText");

                    $hotelData .= $this->_objTpl->get("fieldRowText", true);
                break;

                case "textarea":
                    $rowClassText = ($rowClassText == 2) ? 1 : 2;
                    foreach ($this->languages as $langid => $language){
                        $this->_objTpl->setVariable(array(
                            "HOTEL_FIELD_TEXTAREA_NAME"      => "field_".$fieldkey."_".$langid,
                            "HOTEL_FIELD_TEXTAREA_VALUE"     => ($hotelID > 0) ? $field['content'][$langid] : "", // If we're editing
                            'HOTEL_FIELD_ID'                =>    $fieldkey,
                            'HOTEL_FIELD_LANG_ID'        =>    $langid,
                        ));
                        $this->_objTpl->parse("textarea-column");
                    }
                    $this->_objTpl->setVariable(array(
                        "HOTEL_FIELD_TEXTAREA_CAPTION"   => $field['names'][1],
                        "HOTEL_ROW"                      => $rowClassText,
                        'HOTEL_FIELD_TEXTAREA_ID'        => $fieldkey,
                        'HOTEL_CHECKED'                    => ($hotelID > 0) ? (($field['content']['active']) ? 'checked="checked"' : '' ) : ($field['mandatory'] == 1) ? 'checked="checked"' : '',
                        'HOTEL_DISABLED'                    => ($field['mandatory'] == 1) ? $strDisabled : '',
                    ));

                    $this->_objTpl->parse("fieldRowTextarea");
                    $hotelData .= $this->_objTpl->get("fieldRowTextarea", true);
                break;

                case "img":
                    $rowClassImg = ($rowClassImg == 2) ? 1 : 2;
                    foreach ($this->languages as $langid => $language){
                        $this->_objTpl->setVariable(array(
                            "HOTEL_FIELD_IMG_NAME"    => "field_".$fieldkey."_".$langid,
                            'HOTEL_FIELD_IMG_VALUE'    => ($hotelID > 0) ? $field['content'][$langid] : "", // If we're editings
                        ));
                        $this->_objTpl->parse("img-column");
                    }

                    $this->_objTpl->setVariable(array(
                        "HOTEL_FIELD_IMG_CAPTION"       => $field['names'][1],
                        "HOTEL_ROW"                  => $rowClassImg,
                        'HOTEL_FIELD_IMG_ID'            => $fieldkey,
                        'HOTEL_CHECKED'                => ($hotelID > 0) ? (($field['content']['active']) ? 'checked="checked"' : '' ) : ($field['mandatory'] == 1) ? 'checked="checked"' : '',
                        'HOTEL_DISABLED'                    => ($field['mandatory'] == 1) ? $strDisabled : '',
                        'HOTEL_FIELD_IMG_SRC'        => (!empty($field['img']) && is_file(ASCMS_PATH.$field['img'])) ? $field['img'] : $this->_defaultImage,
                        'TXT_HOTEL_EDIT_OR_ADD_IMAGE' => $_ARRAYLANG['TXT_HOTEL_EDIT_OR_ADD_IMAGE'],
                    ));
                    $this->_objTpl->parse("fieldRowImg");

                    $this->_objTpl->setVariable(array(
                           'HOTEL_IMG_ID'    =>    $fieldkey,
                           'HOTEL_IMG_URL'    =>     $field['img'],
                    ));
                    $this->_objTpl->parse("hiddenFields");

                break;
                case 'panorama':
                    $rowClassImg = ($rowClassImg == 2) ? 1 : 2;
                    foreach ($this->languages as $langid => $language){
                        $this->_objTpl->setVariable(array(
                            "HOTEL_FIELD_PANO_NAME"    => "field_".$fieldkey."_".$langid,
                            'HOTEL_FIELD_PANO_VALUE'    => ($hotelID > 0) ? $field['content'][$langid] : "", // If we're editings
                        ));
                        $this->_objTpl->parse("panorama-column");
                    }

                    $this->_objTpl->setVariable(array(
                        "HOTEL_FIELD_PANO_CAPTION"       => $field['names'][1],
                        "HOTEL_ROW"                  => $rowClassImg,
                        'HOTEL_FIELD_PANO_ID'            => $fieldkey,
                        'HOTEL_CHECKED'                => ($hotelID > 0) ? (($field['content']['active']) ? 'checked="checked"' : '' ) : ($field['mandatory'] == 1) ? 'checked="checked"' : '',
                        'HOTEL_DISABLED'                    => ($field['mandatory'] == 1) ? $strDisabled : '',
                        'HOTEL_FIELD_PANO_SRC'        => (!empty($field['img']) && is_file(ASCMS_PATH.$field['img'])) ? $field['img'] : $this->_defaultImage,
                    ));

                    $this->_objTpl->parse("fieldRowPanorama");

                    $this->_objTpl->setVariable(array(
                           'HOTEL_IMG_ID'    =>    $fieldkey,
                           'HOTEL_IMG_URL'    =>     $field['img'],
                    ));
                    $this->_objTpl->parse("hiddenFields");

                break;

                case "protected_link":
                case "link":
                    $rowClassLnk = ($rowClassLnk == 2) ? 1 : 2;
                    foreach ($this->languages as $langid => $language){
                        $this->_objTpl->setVariable(array(
                            "HOTEL_FIELD_LNK_NAME"    => "field_".$fieldkey."_".$langid,
                            'HOTEL_FIELD_LNK_VALUE'    => ($hotelID > 0) ? $field['content'][$langid] : "", // we're editing?
                        ));
                        $this->_objTpl->parse("lnk-column");
                    }

                    $this->_objTpl->setVariable(array(
                        "HOTEL_FIELD_LNK_CAPTION"       => $field['names'][1],
                        "HOTEL_ROW"                  => $rowClassLnk,
                        'HOTEL_FIELD_LNK_ID'            => $fieldkey,
                        'HOTEL_CHECKED'                => ($hotelID > 0) ? (($field['content']['active']) ? 'checked="checked"' : '' ) : ($field['mandatory'] == 1) ? 'checked="checked"' : '',
                        'HOTEL_DISABLED'                => ($field['mandatory'] == 1) ? $strDisabled : '',
                          'HOTEL_PROTECTED_ICON'        => ($field['type'] == 'protected_link') ? 'images/icons/lock_closed.gif' : 'images/icons/lock_open.gif',
                        'TXT_HOTEL_PROTECTED'        => ($field['type'] == 'protected_link') ? $_ARRAYLANG['TXT_HOTEL_PROTECTED'] : $_ARRAYLANG['TXT_HOTEL_NOT_PROTECTED'],

                    ));

                    $this->_objTpl->parse("fieldRowLnk");
                break;


            }



        }

        $this->_objTpl->replaceBlock("rows", $hotelData, true);
        $this->_objTpl->touchBlock("rows");
        $this->_objTpl->parse("rows");
    }

    /**
     * pops up the google map window for choosing coordinates for an object
     *
     * @return void
     */
    function _showMapPopup(){
        global $_ARRAYLANG;
        $objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/hotel/template');
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $objTpl->loadTemplateFile('module_hotel_map_popup.html');
        $googlekey = (!empty($this->arrSettings['GOOGLE_API_KEY_'.$_SERVER['SERVER_NAME']])) ? $this->arrSettings['GOOGLE_API_KEY_'.$_SERVER['SERVER_NAME']] : '';
        $objTpl->setVariable(array(
            'TXT_HOTEL_BROWSER_NOT_SUPPORTED'    => $_ARRAYLANG['TXT_HOTEL_BROWSER_NOT_SUPPORTED'],
            'TXT_HOTEL_CLOSE'                    => $_ARRAYLANG['TXT_HOTEL_CLOSE'],
            'TXT_HOTEL_DBLCLICK_TO_SET_POINT'    => $_ARRAYLANG['TXT_HOTEL_DBLCLICK_TO_SET_POINT'],
            'TXT_HOTEL_ACCEPT'                   => $_ARRAYLANG['TXT_HOTEL_ACCEPT'],
            'HOTEL_MAP_LAT_BACKEND'              => $this->arrSettings['lat_backend'],
            'HOTEL_MAP_LON_BACKEND'              => $this->arrSettings['lon_backend'],
            'HOTEL_MAP_ZOOM_BACKEND'             => $this->arrSettings['zoom_backend'],
            'HOTEL_GOOGLE_API_KEY'               => $googlekey,

        ));
        $objTpl->show();

    }

    /**
     * shows The settings page
     *
     * @global $_ARRAYLANG, $objDatabase
     * @access private
     *
     */
    function _showSettings()
    {
        global $_ARRAYLANG, $_CONFIG;

        $this->_getFieldNames();
        $domain1 = $_CONFIG['domainUrl'];
        $domain2 = $this->_getDomain($_CONFIG['domainUrl']);
        $this->_pageTitle = $_ARRAYLANG['TXT_HOTEL_SETTINGS'];
        $this->_objTpl->loadTemplateFile('module_hotel_settings.html');
        $this->_objTpl->setGlobalVariable(array(
                'TXT_HOTEL_TYPE'                    => $_ARRAYLANG['TXT_HOTEL_TYPE'],
                'TXT_HOTEL_ORDER'                => $_ARRAYLANG['TXT_HOTEL_ORDER'],
                'TXT_HOTEL_TYPE_TEXT'            => $_ARRAYLANG['TXT_HOTEL_TYPE_TEXT'],
                'TXT_HOTEL_TYPE_TEXTAREA'        => $_ARRAYLANG['TXT_HOTEL_TYPE_TEXTAREA'],
                'TXT_HOTEL_TYPE_IMG'                => $_ARRAYLANG['TXT_HOTEL_TYPE_IMG'],
                'TXT_HOTEL_CLONEROW'                => $_ARRAYLANG['TXT_HOTEL_CLONEROW'],
                'TXT_HOTEL_DELETE'                => $_ARRAYLANG['TXT_HOTEL_DELETE'],
                'TXT_HOTEL_DELETEROW'            => $_ARRAYLANG['TXT_HOTEL_DELETEROW'],
                'TXT_HOTEL_DEFINE_FIELDS'        => $_ARRAYLANG['TXT_HOTEL_DEFINE_FIELDS'],
                'TXT_HOTEL_LANGUAGES'            => $_ARRAYLANG['TXT_HOTEL_LANGUAGES'],
                'TXT_HOTEL_IMAGES'                => $_ARRAYLANG['TXT_HOTEL_IMAGES'],
                'TXT_HOTEL_DELETE_UNUSED_IMAGES'    => $_ARRAYLANG['TXT_HOTEL_DELETE_UNUSED_IMAGES'],
                'TXT_HOTEL_DELETE_IMAGES'        => $_ARRAYLANG['TXT_HOTEL_DELETE_IMAGES'],
                'TXT_HOTEL_SAVE'                    => $_ARRAYLANG['TXT_HOTEL_SAVE'],
                'TXT_HOTEL_AVAILABLE_LANGUAGES'    => $_ARRAYLANG['TXT_HOTEL_AVAILABLE_LANGUAGES'],
                'TXT_HOTEL_NEW_LANGUAGE'            => $_ARRAYLANG['TXT_HOTEL_NEW_LANGUAGE'],
                'TXT_HOTEL_ADD'                    => $_ARRAYLANG['TXT_HOTEL_ADD'],
                'TXT_HOTEL_AVAILABLE_FIELDS'        => $_ARRAYLANG['TXT_HOTEL_AVAILABLE_FIELDS'],
                'TXT_HOTEL_ID'                    => $_ARRAYLANG['TXT_HOTEL_ID'],
                'TXT_HOTEL_TYPE'                    => $_ARRAYLANG['TXT_HOTEL_TYPE'],
                'TXT_HOTEL_FUNCTIONS'            => $_ARRAYLANG['TXT_HOTEL_FUNCTIONS'],
                'TXT_CONFIRM_DELETE'            => $_ARRAYLANG['TXT_CONFIRM_DELETE'],
                'TXT_HOTEL_SHOW_FILE'            => $_ARRAYLANG['TXT_HOTEL_SHOW_FILE'],
                'TXT_HOTEL_EDIT'                 => $_ARRAYLANG['TXT_HOTEL_EDIT'],
                'TXT_SETTINGS'                  => $_ARRAYLANG['TXT_SETTINGS'],
                'TXT_HOTEL_LATEST_ENTRIES_COUNT' => $_ARRAYLANG['TXT_HOTEL_LATEST_ENTRIES_COUNT'],
                'TXT_HOTEL_GOOGLE_KEY'           => $_ARRAYLANG['TXT_HOTEL_GOOGLE_KEY'],
                'TXT_HOTEL_SAVE'                 => $_ARRAYLANG['TXT_HOTEL_SAVE'],
                'TXT_HOTEL_ICON_MESSAGE'         => $_ARRAYLANG['TXT_HOTEL_ICON_MESSAGE'],
                'TXT_HOTEL_MAP_STARTPOINT_FRONTEND'  => $_ARRAYLANG['TXT_HOTEL_MAP_STARTPOINT_FRONTEND'],
                'TXT_HOTEL_MAP_STARTPOINT_BACKEND'  => $_ARRAYLANG['TXT_HOTEL_MAP_STARTPOINT_BACKEND'],
                'TXT_HOTEL_SETTINGS_ICON_MESSAGE_DESC'   => $_ARRAYLANG['TXT_HOTEL_SETTINGS_ICON_MESSAGE_DESC'],
                'HOTEL_SETTINGS_DOMAIN1'         => $domain1,
                'HOTEL_SETTINGS_DOMAIN2'         => $domain2,
                'TXT_HOTEL_TYPE_LINK'            =>    $_ARRAYLANG['TXT_HOTEL_TYPE_LINK'],
                'TXT_HOTEL_TYPE_PROTECTED_LINK'    =>    $_ARRAYLANG['TXT_HOTEL_TYPE_PROTECTED_LINK'],
                'TXT_HOTEL_TYPE_PANORAMA'    =>    $_ARRAYLANG['TXT_HOTEL_TYPE_PANORAMA'],
                'TXT_HOTEL_TYPE_DIGITS_ONLY'    =>    $_ARRAYLANG['TXT_HOTEL_TYPE_DIGITS_ONLY'],
                'TXT_HOTEL_TYPE_PRICE'        =>    $_ARRAYLANG['TXT_HOTEL_TYPE_PRICE'],
                'TXT_HOTEL_MANDATORY'            => $_ARRAYLANG['TXT_HOTEL_MANDATORY'],
                'TXT_HOTEL_NO'                    => $_ARRAYLANG['TXT_HOTEL_NO'],
                'TXT_HOTEL_YES'                    => $_ARRAYLANG['TXT_HOTEL_YES'],
                'TXT_HOTEL_CURRENCY'                => $_ARRAYLANG['TXT_HOTEL_CURRENCY'],
                'TXT_HOTEL_PROTECTED_LINK_EMAIL_MESSAGE_BODY'        => $_ARRAYLANG['TXT_HOTEL_PROTECTED_LINK_EMAIL_MESSAGE_BODY'],
                'TXT_HOTEL_PROTECTED_LINK_EMAIL_MESSAGE_SUBJECT'        => $_ARRAYLANG['TXT_HOTEL_PROTECTED_LINK_EMAIL_MESSAGE_SUBJECT'],
                'TXT_HOTEL_PROTECTED_LINK_SENDER_NAME'        => $_ARRAYLANG['TXT_HOTEL_PROTECTED_LINK_SENDER_NAME'],
                'TXT_HOTEL_PROTECTED_LINK_SENDER_EMAIL'        => $_ARRAYLANG['TXT_HOTEL_PROTECTED_LINK_SENDER_EMAIL'],
                'TXT_HOTEL_PROTECTED_LINK_EMAIL_MESSAGE_BODY_INFO'        => $_ARRAYLANG['TXT_HOTEL_PROTECTED_LINK_EMAIL_MESSAGE_BODY_INFO'],
                'TXT_HOTEL_CONTACT_RECEIVERS'        => $_ARRAYLANG['TXT_HOTEL_CONTACT_RECEIVERS'],
                'TXT_HOTEL_CONTACT_RECEIVERS_INFO'        => $_ARRAYLANG['TXT_HOTEL_CONTACT_RECEIVERS_INFO'],
                'TXT_HOTEL_INTEREST_CONFIRM_SUBJECT'        => $_ARRAYLANG['TXT_HOTEL_INTEREST_CONFIRM_SUBJECT'],
                'TXT_HOTEL_INTEREST_CONFIRM_MESSAGE'        => $_ARRAYLANG['TXT_HOTEL_INTEREST_CONFIRM_MESSAGE'],
                'TXT_HOTEL_INTEREST_INFO'            => $_ARRAYLANG['TXT_HOTEL_INTEREST_INFO'],

                // Settings
                'HOTEL_SETTINGS_LATEST_ENTRIES_COUNT' => $this->arrSettings['latest_entries_count'],
                'HOTEL_SETTINGS_GOOGLE_KEY_DOMAIN1' => $this->arrSettings['GOOGLE_API_KEY_'.$domain1],
                'HOTEL_SETTINGS_GOOGLE_KEY_DOMAIN2' => $this->arrSettings['GOOGLE_API_KEY_'.$domain2],
                'HOTEL_SETTINGS_ICON_MESSAGE'       => htmlspecialchars($this->arrSettings['message']),
                'TXT_HOTEL_GOOGLE_KEY_INFO'        => $_ARRAYLANG['TXT_HOTEL_GOOGLE_KEY_INFO'],
                'HOTEL_LON_FRONTEND'                => $this->arrSettings['lon_frontend'],
                'HOTEL_LAT_FRONTEND'                => $this->arrSettings['lat_frontend'],
                'HOTEL_ZOOM_FRONTEND'            => $this->arrSettings['zoom_frontend'],
                'HOTEL_LON_BACKEND'                => $this->arrSettings['lon_backend'],
                'HOTEL_LAT_BACKEND'                => $this->arrSettings['lat_backend'],
                'HOTEL_ZOOM_BACKEND'                => $this->arrSettings['zoom_backend'],
                'HOTEL_PROT_LINK_MESSAGE_SUBJECT'    => $this->arrSettings['prot_link_message_subject'],
                'HOTEL_PROT_LINK_MESSAGE_BODY' => $this->arrSettings['prot_link_message_body'],
                'HOTEL_PROT_LINK_SENDER_EMAIL' => $this->arrSettings['sender_email'],
                'HOTEL_PROT_LINK_SENDER_NAME'  => $this->arrSettings['sender_name'],
                'HOTEL_CONTACT_RECEIVERS'      => $this->arrSettings['contact_receiver'],
                'HOTEL_INTEREST_CONFIRM_SUBJECT'      => $this->arrSettings['interest_confirm_subject'],
                'HOTEL_INTEREST_CONFIRM_MESSAGE'      => $this->arrSettings['interest_confirm_message'],

                'HOTEL_DEFINE_FIELDS_COLSPAN'     => $this->langCount+4,
                'HOTEL_LANG_COUNT'                => $this->langCount,
                'HOTEL_LANG_COUNT_PLUS1'            => $this->langCount+1,
        ));

        $rowid = 2;
        foreach ($this->fieldNames as $fieldID => $field) {
            foreach(array_keys($this->languages) as $langID) {
                $this->_objTpl->setVariable(array(
                    'HOTEL_FIELD_CONTENT'=> (!empty($field['names'][$langID])) ? $field['names'][$langID] : '',
                    'HOTEL_FIELD_LANGID' => $langID
                ));
                $this->_objTpl->touchBlock('langRow4');
                $this->_objTpl->parse('langRow4');
            }
            $this->_objTpl->setVariable(array(
                'HOTEL_FIELD_ID'        =>    $fieldID,
                'HOTEL_FIELD_TYPE'    =>    $_ARRAYLANG['TXT_HOTEL_TYPE_'.strtoupper($field['type'])],
                'HOTEL_FIELD_TYPE_LIST' => $this->_getFieldTypeList($fieldID, $field['type']),
                'TXT_MANDATORY'        => ($field['mandatory']) ? $_ARRAYLANG['TXT_HOTEL_YES'] : $_ARRAYLANG['TXT_HOTEL_NO'],
                'HOTEL_ORDER'         =>  $field['order'],
                'HOTEL_ROW'          => ($rowid == 2) ? $rowid-- : $rowid++,
                'HOTEL_SELECTED_'.(($field['mandatory']) ? "YES" : "NO") => "selected=\"selected\""
            ));
            $this->_objTpl->parse('fieldLangList');
        }

        foreach ($this->languages as $id => $lang) {
            $this->_objTpl->setVariable(array(
                'HOTEL_LANGUAGE_ID'         =>    $id,
                'HOTEL_LANGUAGE_NAME'    =>    $_ARRAYLANG[$lang],
                'HOTEL_LANGUAGE_ID2'     =>    $id,
                'HOTEL_LANGUAGE_NAME2'    =>    $_ARRAYLANG[$lang],
                'HOTEL_LANGUAGE_NAME3'    =>  $_ARRAYLANG[$lang],
                'HOTEL_LANGUAGE_NAME5'    =>  $_ARRAYLANG[$lang],
                'HOTEL_LANGUAGE_ID5'        =>  $id,
                'HOTEL_CURRENCY'            =>    $this->arrSettings['currency_lang_'.$id]
            ));
            $this->_objTpl->parse('langRow');
            $this->_objTpl->parse('langRow2');
            $this->_objTpl->parse('langRow3');
            $this->_objTpl->parse('langRow5');
            $this->_objTpl->parse('langRowContent');
        }
    }

    /**
     * write the settings to the database
     *
     * @return void
     */
    function _saveSettings()
    {
        global $_ARRAYLANG, $_CONFIG;

        $error = false;
        $domain1 =  $_CONFIG['domainUrl'];
        $domain2 =  $this->_getDomain($_CONFIG['domainUrl']);
        // with underlines for dots
        $domain1Un = str_replace(".", "_", $domain1);
        $domain2Un = str_replace(".", "_", $domain2);


        if (isset($_POST['latest_entries_count'])) {
            if (!$this->_updateSetting('latest_entries_count', $_POST['latest_entries_count'])) {
                $error = true;
            }
        }

        if (isset($_POST['GOOGLE_API_KEY_'.$domain1Un])) {
            if (!$this->_updateSetting('GOOGLE_API_KEY_'.$domain1, $_POST['GOOGLE_API_KEY_'.$domain1Un])) {
                $error = true;
            }
        }

        if (isset($_POST['GOOGLE_API_KEY_'.$domain2Un])) {
            if (!$this->_updateSetting('GOOGLE_API_KEY_'.$domain2, $_POST['GOOGLE_API_KEY_'.$domain2Un])) {
                $error = true;
            }
        }

        if (isset($_POST['message'])) {
            if (!$this->_updateSetting('message', $_POST['message'])) {
                $error = true;
            }
        }

        if (isset($_POST['lat_frontend'])) {
            if (!$this->_updateSetting('lat_frontend', $_POST['lat_frontend'])) {
                $error = true;
            }
        }

        if (isset($_POST['lon_frontend'])) {
            if (!$this->_updateSetting('lon_frontend', $_POST['lon_frontend'])) {
                $error = true;
            }
        }

        if (isset($_POST['zoom_frontend'])) {
            if (!$this->_updateSetting('zoom_frontend', $_POST['zoom_frontend'])) {
                $error = true;
            }
        }



        if (isset($_POST['lat_backend'])) {
            if (!$this->_updateSetting('lat_backend', $_POST['lat_backend'])) {
                $error = true;
            }
        }

        if (isset($_POST['lon_backend'])) {
            if (!$this->_updateSetting('lon_backend', $_POST['lon_backend'])) {
                $error = true;
            }
        }



        if (isset($_POST['zoom_backend'])) {
            if (!$this->_updateSetting('zoom_backend', $_POST['zoom_backend'])) {
                $error = true;
            }
        }

        if (isset($_POST['prot_link_message_body'])) {
            if (!$this->_updateSetting('prot_link_message_body', $_POST['prot_link_message_body'])) {
                $error = true;
            }
        }

        if (isset($_POST['prot_link_message_subject'])) {
            if (!$this->_updateSetting('prot_link_message_subject', $_POST['prot_link_message_subject'])) {
                $error = true;
            }
        }


        if (isset($_POST['sender_email'])) {
            if (!$this->_updateSetting('sender_email', $_POST['sender_email'])) {
                $error = true;
            }
        }

        if (isset($_POST['sender_name'])) {
            if (!$this->_updateSetting('sender_name', $_POST['sender_name'])) {
                $error = true;
            }
        }

        if (isset($_POST['contact_receiver'])) {
            if (!$this->_updateSetting('contact_receiver', $_POST['contact_receiver'])) {
                $error = true;
            }
        }

        if (isset($_POST['interest_confirm_subject'])) {
            if (!$this->_updateSetting('interest_confirm_subject', $_POST['interest_confirm_subject'])) {
                $error = true;
            }
        }

        if (isset($_POST['interest_confirm_message'])) {
            if (!$this->_updateSetting('interest_confirm_message', $_POST['interest_confirm_message'])) {
                $error = true;
            }
        }

        foreach (array_keys($this->languages) as $langID) {
            if (isset($_POST['currency_lang_'.$langID])) {
                  if (!$this->_updateSetting('currency_lang_'.$langID, $_POST['currency_lang_'.$langID])) {
                       $error = true;
                }
            }
        }

        if ($error) {
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'];
        } else {
            $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_UPDATED'];
        }
    }

    /**
     * Update Settings
     *
     * Updates a Settings row
     * @param $key Name of the setting
     * @param $val Value of the setting
     */
    function _updateSetting($key, $val) {
        global $objDatabase;
        $val = contrexx_addslashes($val);
        if ($this->arrSettings[$key] != $val) {
            $query = "  UPDATE ".DBPREFIX."module_hotel_settings
                        SET `setvalue` = '".$val."'
                        WHERE `setname` = '".$key."'";
            if (!$objDatabase->Execute($query)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Saves added fields
     *
     * @access private
     * @global $objDatabase
     * @global $_ARRAYLANG
     */
    function _addfields(){
        global $objDatabase, $_ARRAYLANG;
        $dberror=false;

        for($i=0; $i<count($_REQUEST['type']) && !$dberror; $i++){
            $query = "    INSERT INTO ".DBPREFIX."module_hotel_field
                            (id,
                            type,
                            `order`,
                            `mandatory`
                            )
                        VALUES
                            (NULL,
                            '".addslashes(strip_tags($_REQUEST['type'][$i], ENT_QUOTES))."',
                            '".((isset($_REQUEST['order'][$i])) ? intval($_REQUEST['order'][$i]) : 9999 )."',
                            '".((isset($_REQUEST['mandatory'][$i])) ? intval($_REQUEST['mandatory'][$i]) : 0)."'
                            )";
            if($objDatabase->Execute($query) === false){
                $objDatabase->ErrorMsg();
                $dberror=true;
            }
            $lastFieldID = $objDatabase->Insert_ID();

            foreach (array_keys($this->languages) as $langID){
                if(!$dberror){
                    $query="    INSERT INTO ".DBPREFIX."module_hotel_fieldname
                                                (id,
                                                field_id,
                                                lang_id,
                                                name)
                                            VALUES
                                                (NULL,
                                                '".$lastFieldID."',
                                                '".$langID."',
                                                '".$_REQUEST['lang_'.$langID][$i]."')";
                    if($objDatabase->Execute($query) === false){
                        $dberror=true;
                    }
                }
            }

        }
        if($dberror){
            $this->_strErrMessage = $_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg();
        }else{
            $this->_strOkMessage = $_ARRAYLANG['TXT_HOTEL_SUCCESSFULLY_INSERTED'];
        }
        $this->_showSettings();
    }


    /**
     * Modify fields
     *
     * Saves the modified fields
     * @global $objDatabase
     * @global $_ARRAYLANG
     * @access  private
     */
    function _modfields() {
        global $objDatabase, $_ARRAYLANG;
        $this->_getFieldNames();
        $checked = array();

        foreach ($_POST as $key => $value) {
            if (preg_match('/^value_[0-9]+_[0-9]+$/', $key)) {
                $singleVals = explode("_", $key);
                $id = $singleVals[1];
                $langId = $singleVals[2];
                $newType = $_POST['select_list_'.$id];
                $newOrder = $_POST['order_'.$id];
                $newMandatory = $_POST['field_mandatory_'.$id];
                $value=trim($value);

                $query = "    SELECT 1 FROM ".DBPREFIX."module_hotel_fieldname
                            WHERE `field_id` = '".$id."'
                            AND    `lang_id` = '".$langId."'";
                if(($objRS = $objDatabase->SelectLimit($query, 1)) === false){
                    die($_ARRAYLANG['TXT_HOTEL_DB_ERROR'] ." ".$objDatabase->ErrorMsg());
                }

                if($objRS->RecordCount() > 0){
                    if ($this->fieldNames[$id]['names'][$langId] != $value) {
                        $query = "  UPDATE ".DBPREFIX."module_hotel_fieldname
                                    SET `name` = '".$value."'
                                    WHERE `field_id` = '".$id."'
                                    AND `lang_id` = '".$langId."'";
                        $objDatabase->Execute($query);
                    }
                }else{
                    $query = "  INSERT INTO ".DBPREFIX."module_hotel_fieldname
                                SET `name` = '".$value."',
                                `field_id` = '".$id."',
                                `lang_id` = '".$langId."'";
                    $objDatabase->Execute($query);
                }


                if (!isset($checked[$id])) {
                    $checked[$id] = true;
                    if ($this->fieldNames[$id]['type'] != $newType) {
                        $query = "  UPDATE ".DBPREFIX."module_hotel_field
                                    SET `type` = '".$newType."'
                                    WHERE `id` = '".$id."'";
                        $objDatabase->Execute($query);
                    }

                    if ($this->fieldNames[$id]['order'] != $newOrder) {
                        $query = "  UPDATE ".DBPREFIX."module_hotel_field
                                    SET `order` = '".$newOrder."'
                                    WHERE `id` = '".$id."'";
                        $objDatabase->Execute($query);
                    }

                    if ($this->fieldNames[$id]['mandatory'] != $newMandatory) {
                        $query = "  UPDATE ".DBPREFIX."module_hotel_field
                                    SET `mandatory` = '".$newMandatory."'
                                    WHERE `id` = '".$id."'";
                        $objDatabase->Execute($query);
                    }
                }
            }
        }

        header("Location: ?cmd=hotel&act=settings");
        exit;
    }

    /**
     * Get Field Type List
     *
     * List with the types. For the settings page
     * @param int $id Needed for naming the name
     * @param text $select The one entry which should be selected
     * @global $_ARRAYLANG
     * @return string
     */
    function _getFieldTypeList($id, $select) {
        global $_ARRAYLANG;

        $selected = "selected=\"selected\"";
        $textselected = ($select == "text") ? $selected : "";
        $textareaselected  = ($select == "textarea") ? $selected : "";
        $imgselected = ($select == "img") ? $selected : "";
        $linkselected = ($select == "link") ? $selected : "";
        $plinkselected = ($select == "protected_link") ? $selected : "";
        $panoselected = ($select == "panorama") ? $selected : "";
        $digitsselected = ($select == "digits_only") ? $selected : "";
        $priceselected = ($select == "price") ? $selected : "";

        $retval = "<select name=\"select_list_".$id."\" id=\"select_list_".$id."\" style=\"display: none;\" >";
        $retval .= "<option $textselected value=\"text\">".$_ARRAYLANG['TXT_HOTEL_TYPE_TEXT']."</option>";
        $retval .= "<option $textareaselected value=\"textarea\">".$_ARRAYLANG['TXT_HOTEL_TYPE_TEXTAREA']."</option>";
        $retval .= "<option $imgselected value=\"img\">".$_ARRAYLANG['TXT_HOTEL_TYPE_IMG']."</option>" ;
        $retval .= "<option $linkselected value=\"link\">".$_ARRAYLANG['TXT_HOTEL_TYPE_LINK']."</option>" ;
        $retval .= "<option $plinkselected value=\"protected_link\">".$_ARRAYLANG['TXT_HOTEL_TYPE_PROTECTED_LINK']."</option>" ;
        $retval .= "<option $panoselected value=\"panorama\">".$_ARRAYLANG['TXT_HOTEL_TYPE_PANORAMA']."</option>" ;
        $retval .= "<option $digitsselected value=\"digits_only\">".$_ARRAYLANG['TXT_HOTEL_TYPE_DIGITS_ONLY']."</option>" ;
        $retval .= "<option $priceselected value=\"price\">".$_ARRAYLANG['TXT_HOTEL_TYPE_PRICE']."</option>" ;
        $retval .= "</select>";

        return $retval;
    }

}

?>
