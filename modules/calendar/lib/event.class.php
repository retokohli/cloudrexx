<?php
/**
 * Calendar Class Event
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */


/**
 * Calendar Class Event
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */    
class CalendarEvent extends CalendarLibrary
{
    /**
     * Event id
     * 
     * @access public
     * @var integer 
     */
    public $id;
    
    /**
     * Event Type
     * 
     * @access public
     * @var integer 
     */
    public $type;
    
    /**
     * Event title
     *
     * @var string 
     * @access public
     */
    public $title;
    
    /**
     * Event Picture
     * 
     * @access public
     * @var string 
     */
    public $pic;
    
    /**
     * Event attachment file name
     *  
     * @access public
     * @var string 
     */
    public $attach;
    
    /** 
     * Event Start date timestamp
     * 
     * @access public
     * @var integer
     */
    public $startDate;
    
    /**
     * Event enddate timestamp 
     * 
     * @access public
     * @var integer
     */
    public $endDate;
    
    /**
     * Event show start date on list view
     * 
     * @access public
     * @var boolean
     */
    public $showStartDateList;
    
    /**
     * Event show End date on list view
     * 
     * @access public
     * @var boolean
     */
    public $showEndDateList;
    
    /**
     * Event show start time on list view
     * 
     * @access public
     * @var boolean
     */
    public $showStartTimeList;
    
    /**
     * Event show End time on list view
     * 
     * @access public
     * @var boolean
     */
    public $showEndTimeList;
    
    /**
     * Event time type on list view
     * 
     * @access public
     * @var integer
     */
    public $showTimeTypeList;
    
    /**
     * Event show start date on detail view
     * 
     * @access public
     * @var boolean
     */
    public $showStartDateDetail;
    
    /**
     * Event show end date on detail view
     * 
     * @access public
     * @var boolean
     */
    public $showEndDateDetail;
    
    /**
     * Event show start time on detail view
     * 
     * @access public
     * @var boolean
     */
    public $showStartTimeDetail;
    
    /**
     * Event show end time on detail view
     * 
     * @access public
     * @var boolean
     */
    public $showEndTimeDetail;
    
    /**
     * Event time type on detail view
     * 
     * @access public
     * @var integer
     */
    public $showTimeTypeDetail;
    
    /**
     * Event price
     * 
     * @access public
     * @var integer
     */
    public $price;
    
    /**
     * Event link
     * 
     * @access public
     * @var string
     */
    public $link;
    
    /**
     * Event priority
     * 
     * @access public
     * @var integer
     */
    public $priority;
    
    /**
     * Event show end date on detail view
     * 
     * @access public
     * @var boolean
     */
    public $access;
    
    /**
     * Event description
     * 
     * @access public
     * @var string
     */
    public $description;
    
    /**
     * Event place
     * 
     * @access public
     * @var string
     */
    public $place;
    
    /**
     * Event status
     * 
     * @access public
     * @var integer
     */
    public $status;
    
    /**
     * Event confirmed
     * 
     * @access public
     * @var boolean
     */
    public $confirmed;
    
    /**
     * Event author
     * 
     * @access public
     * @var string
     */
    public $author;
    
    /**
     * Event category id
     *
     * @access public
     * @var integer 
     */
    public $catId;
    
    /**
     * Event series status
     *
     * @access public
     * @var integer 
     */
    public $seriesStatus;
    
    /**
     * Event series data
     *
     * @access public
     * @var array
     */
    public $seriesData = array();
    
    /**
     * Event languages to show
     *
     * @access public
     * @var array 
     */
    public $showIn;
    
    /**
     * Avaliable languages
     *
     * @access public
     * @var array
     */
    public $availableLang;
    
    /**
     * Event map status
     *
     * @access public
     * @var integer 
     */
    public $map;
    
    /**
     * Event invited group
     *
     * @access public
     * @var array
     */
    public $invitedGroups = array();
    
    /**
     * Event invited mail
     *
     * @access public
     * @var array
     */
    public $invitedMails = array();
    
    /**
     * is Event invitation sent
     *
     * @access public
     * @var boolean
     */
    public $invitationSent;
    
    /**
     * Event status of registration
     *
     * @access public
     * @var boolean
     */
    public $registration;
    
    /**
     * Event registration form
     *
     * @access public
     * @var integer
     */
    public $registrationForm;
    
    /**
     * Event number of subscriber
     *
     * @access public
     * @var integer
     */
    public $numSubscriber;
    
    /**
     * Event notification the event
     *
     * @access public
     * @var string
     */
    public $notificationTo;
    
    /**
     * Event E-mail template
     *
     * @access public
     * @var integer
     */
    public $emailTemplate;
    
    /**
     * Event ticket sales
     *
     * @access public
     * @var integer
     */
    public $ticketSales;
    
    /**
     * Event available seating
     *
     * @access public
     * @var integer
     */
    public $numSeating;
    
    /**
     * Event free palces
     *
     * @access public
     * @var integer
     */
    public $freePlaces;
    
    /**
     * Event related websites
     *
     * @access public
     * @var array
     */
    public $relatedHosts = array();
    
    /**
     * Event data
     *
     * @access public
     * @var array
     */
    public $arrData = array();
    
    /**
     * External
     *
     * @access public
     * @var boolean
     */
    public $external = false;
    
    /**
     * Event host id
     *
     * @access public
     * @var string
     */
    public $hostId = "local";
    
    /**
     * module image upload physical path
     *
     * @access public
     * @var string 
     */
    public $uploadImgPath = '';
    
    /**
     * module uploaded image web path
     *
     * @access public
     * @var string 
     */
    public $uploadImgWebPath = '';
    
    /**
     * Constructor
     * 
     * Loads the event object of given id
     * Call the parent constructor to initialize the settings values
     * 
     * @param integer $id Event id     
     */
    function __construct($id=null){
        if($id != null) {
            self::get($id);
        }
        
        $this->uploadImgPath    = ASCMS_PATH.ASCMS_IMAGE_PATH.'/'.$this->moduleName.'/';
        $this->uploadImgWebPath = ASCMS_IMAGE_PATH.'/'.$this->moduleName.'/';
        
        parent::getSettings();
    }
        
    /**
     * Load the requested event by id
     * 
     * @param integer $eventId        Event Id
     * @param integer $eventStartDate Event start date
     * @param integer $langId         Language id
     * 
     * @return null 
     */
    function get($eventId, $eventStartDate=null, $langId=null) {
        global $objDatabase, $_ARRAYLANG, $_LANGID, $objInit;
        
        parent::getSettings();
        
        if($objInit->mode == 'backend') {
            $lang_where = "";  
        } else {
            if($langId == null) {  
                $lang_where = "AND field.lang_id = '".intval($_LANGID)."' ";   
            } else {
                $lang_where = "AND field.lang_id = '".intval($langId)."' ";   
            }                                 
        }                                                                  
        

        $query = "SELECT event.id AS id,
                         event.type AS type,
                         event.startdate AS startdate,
                         event.enddate AS enddate,
                         event.use_custom_date_display AS useCustomDateDisplay,
                         event.showStartDateList AS showStartDateList,
                         event.showEndDateList AS showEndDateList,
                         event.showStartTimeList AS showStartTimeList,
                         event.showEndTimeList AS showEndTimeList,
                         event.showTimeTypeList AS showTimeTypeList,
                         event.showStartDateDetail AS showStartDateDetail,
                         event.showEndDateDetail AS showEndDateDetail,
                         event.showStartTimeDetail AS showStartTimeDetail,
                         event.showEndTimeDetail AS showEndTimeDetail,
                         event.showTimeTypeDetail AS showTimeTypeDetail,
                         event.access AS access,
                         event.price AS price,
                         event.link AS link,
                         event.pic AS pic,
                         event.attach AS attach,
                         event.place_mediadir_id AS place_mediadir_id,
                         event.priority AS priority,
                         event.catid AS catid,
                         event.status AS status,
                         event.author AS author,
                         event.confirmed AS confirmed,
                         event.show_in AS show_in,
                         event.google AS google,
                         event.invited_groups AS invited_groups,
                         event.invited_mails AS invited_mails,
                         event.invitation_sent AS invitation_sent,
                         event.registration AS registration,
                         event.registration_form AS registration_form,
                         event.registration_num AS registration_num,
                         event.registration_notification AS registration_notification,
                         event.email_template AS email_template,
                         event.ticket_sales AS ticket_sales,
                         event.num_seating AS num_seating,
                         event.series_status AS series_status,
                         event.series_type AS series_type,
                         event.series_pattern_count AS series_pattern_count,
                         event.series_pattern_weekday AS series_pattern_weekday,
                         event.series_pattern_day AS series_pattern_day,
                         event.series_pattern_week AS series_pattern_week,
                         event.series_pattern_month AS series_pattern_month,
                         event.series_pattern_type AS series_pattern_type,
                         event.series_pattern_dourance_type AS series_pattern_dourance_type,
                         event.series_pattern_end AS series_pattern_end,
                         event.series_pattern_begin AS series_pattern_begin,
                         event.series_pattern_exceptions AS series_pattern_exceptions,
                         field.title AS title,
                         field.description AS description,
                         field.place AS place
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_event AS event,
                         ".DBPREFIX."module_".$this->moduleTablePrefix."_event_field AS field
                   WHERE event.id = '".intval($eventId)."'  
                     AND (event.id = field.event_id ".$lang_where.")                                           
                   LIMIT 1";
                                            
        
        $objResult = $objDatabase->Execute($query);  
        
        if($this->arrSettings['showEventsOnlyInActiveLanguage'] == 2) {
            if($objResult->RecordCount() == 0) {
                
                if($langId == null) {
                    $langId = 1;   
                } else {
                    $langId++;
                }
                
                if($langId <= 99) {
                    self::get($eventId,$eventStartDate,$langId); 
                }
            } else {
                if($langId == null) {
                    $langId = $_LANGID;   
                }
            }
        } else {
           $langId = $_LANGID;
        }
        
        if ($objResult !== false) {
            if(!empty($objResult->fields['title'])) {
                $this->id = intval($eventId);   
                $this->type = intval($objResult->fields['type']); 
                $this->title = htmlentities(stripslashes($objResult->fields['title']), ENT_QUOTES, CONTREXX_CHARSET);            
                $this->pic = htmlentities($objResult->fields['pic'], ENT_QUOTES, CONTREXX_CHARSET);
                $this->attach = htmlentities($objResult->fields['attach'], ENT_QUOTES, CONTREXX_CHARSET);
                $this->author = htmlentities($objResult->fields['author'], ENT_QUOTES, CONTREXX_CHARSET);
                $this->startDate = intval($objResult->fields['startdate']);
                $this->endDate = intval($objResult->fields['enddate']);
                $this->useCustomDateDisplay = intval($objResult->fields['useCustomDateDisplay']);
                $this->showStartDateList = intval($objResult->fields['showStartDateList']);
                $this->showEndDateList = intval($objResult->fields['showEndDateList']);
                $this->showStartTimeList = intval($objResult->fields['showStartTimeList']);
                $this->showEndTimeList = intval($objResult->fields['showEndTimeList']);
                $this->showTimeTypeList = intval($objResult->fields['showTimeTypeList']);
                $this->showStartDateDetail = intval($objResult->fields['showStartDateDetail']);
                $this->showEndDateDetail = intval($objResult->fields['showEndDateDetail']);
                $this->showStartTimeDetail = intval($objResult->fields['showStartTimeDetail']);
                $this->showEndTimeDetail = intval($objResult->fields['showEndTimeDetail']);
                $this->showTimeTypeDetail = intval($objResult->fields['showTimeTypeDetail']);
                $this->confirmed = intval($objResult->fields['confirmed']);
                $this->invitationSent = intval($objResult->fields['invitation_sent']);
                $this->access = intval($objResult->fields['access']);
                $this->price = intval($objResult->fields['price']);
                $this->link = htmlentities(stripslashes($objResult->fields['link']), ENT_QUOTES, CONTREXX_CHARSET);
                $this->priority = intval($objResult->fields['priority']);
                $this->description = $objResult->fields['description'];
                
                if($this->arrSettings['placeData'] == 1) {
                    $objMediadirEntry = new mediaDirectoryEntry();
                    $objMediadirEntry->getEntries(intval($objResult->fields['place_mediadir_id'])); 
                    $this->place = '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section=mediadir&amp;cmd=detail&amp;eid='.intval($objResult->fields['place_mediadir_id']).'">'.$objMediadirEntry->arrEntries[$objResult->fields['place_mediadir_id']]['entryFields'][0].'</a>';   
                } else {
                    $this->place = htmlentities(stripslashes($objResult->fields['place']), ENT_QUOTES, CONTREXX_CHARSET);     
                }    
                
                $this->showIn = htmlentities($objResult->fields['show_in'], ENT_QUOTES, CONTREXX_CHARSET);
                $this->availableLang = intval($langId);
                $this->status = intval($objResult->fields['status']);
                $this->catId = intval($objResult->fields['catid']);
                $this->map = intval($objResult->fields['google']);
                $this->seriesStatus = intval($objResult->fields['series_status']);   
                     
                if($this->seriesStatus == 1) {
                    $this->seriesData['seriesPatternCount'] = intval($objResult->fields['series_pattern_count']); 
                    $this->seriesData['seriesType'] = intval($objResult->fields['series_type']); 
                    $this->seriesData['seriesPatternCount'] = intval($objResult->fields['series_pattern_count']); 
                    $this->seriesData['seriesPatternWeekday'] = htmlentities($objResult->fields['series_pattern_weekday'], ENT_QUOTES, CONTREXX_CHARSET);     
                    $this->seriesData['seriesPatternDay'] = intval($objResult->fields['series_pattern_day']); 
                    $this->seriesData['seriesPatternWeek'] = intval($objResult->fields['series_pattern_week']); 
                    $this->seriesData['seriesPatternMonth'] = intval($objResult->fields['series_pattern_month']); 
                    $this->seriesData['seriesPatternType'] = intval($objResult->fields['series_pattern_type']); 
                    $this->seriesData['seriesPatternDouranceType'] = intval($objResult->fields['series_pattern_dourance_type']); 
                    $this->seriesData['seriesPatternEnd'] = intval($objResult->fields['series_pattern_end']); 
                    $this->seriesData['seriesPatternBegin'] = intval($objResult->fields['series_pattern_begin']); 
                    $this->seriesData['seriesPatternExceptions'] = explode(",", $objResult->fields['series_pattern_exceptions']);
                }    
                  
                $this->invitedGroups = explode(',', $objResult->fields['invited_groups']);     
                $this->invitedMails =  htmlentities($objResult->fields['invited_mails'], ENT_QUOTES, CONTREXX_CHARSET);  
                $this->registration = intval($objResult->fields['registration']);  
                $this->registrationForm = intval($objResult->fields['registration_form']);  
                $this->numSubscriber = intval($objResult->fields['registration_num']); 
                $this->notificationTo = htmlentities($objResult->fields['registration_notification'], ENT_QUOTES, CONTREXX_CHARSET);
                $this->emailTemplate = intval($objResult->fields['email_template']);
                $this->ticketSales = intval($objResult->fields['ticket_sales']);
                $this->arrNumSeating = json_decode($objResult->fields['num_seating']);
                $this->numSeating = implode(',', $this->arrNumSeating);
                
                $queryRegistrations = '
                    SELECT `v`.`value` AS `reserved_seating`
                    FROM `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_value` AS `v`
                    INNER JOIN `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration` AS `r`
                    ON `v`.`reg_id` = `r`.`id`
                    INNER JOIN `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field` AS `f`
                    ON `v`.`field_id` = `f`.`id`
                    WHERE `r`.`event_id` = '.intval($eventId).'
                    AND `r`.`type` = 1
                    AND `f`.`type` = "seating"
                ';
                $objResultRegistrations = $objDatabase->Execute($queryRegistrations);
                
                $reservedSeating = 0;
                if ($objResultRegistrations !== false) {
                    while (!$objResultRegistrations->EOF) {
                        $reservedSeating += intval($objResultRegistrations->fields['reserved_seating']);
                        $objResultRegistrations->MoveNext();
                    }
                }
                
                $freePlaces = intval($this->numSubscriber - $reservedSeating);
                $this->freePlaces = $freePlaces < 0 ? 0 : $freePlaces;
                
                $queryHosts = '
                    SELECT host_id                            
                    FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_rel_event_host
                    WHERE event_id = '.intval($eventId)
                ;
                                
                $objResultHosts = $objDatabase->Execute($queryHosts); 
                
                if ($objResultHosts !== false) {      
                    while (!$objResultHosts->EOF) {                                             
                        $this->relatedHosts[] = intval($objResultHosts->fields['host_id']);
                        $objResultHosts->MoveNext();
                    }
                }
                
                self::getData(); 
            }
        }
    }
    
    /**
     * gets the data for the event
     * 
     * @return null
     */
    function getData() {
        global $objDatabase, $_ARRAYLANG, $_LANGID;
        
        $activeLangs = explode(",", $this->showIn);
        $this->arrData = array();
        
        foreach ($activeLangs as $key => $langId) {
            $query = "SELECT field.title AS title, 
                             field.place AS place, 
                             field.place_street AS place_street, 
                             field.place_zip AS place_zip, 
                             field.place_city AS place_city, 
                             field.place_country AS place_country, 
                                 field.description AS description,
                             field.redirect AS redirect                                 
                        FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_event_field AS field
                       WHERE field.event_id = '".intval($this->id)."'
                         AND field.lang_id = '".intval($langId)."'
                       LIMIT 1";
            
            $objResult = $objDatabase->Execute($query);
            
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                        $this->arrData['title'][$langId] = htmlentities(stripslashes($objResult->fields['title']), ENT_QUOTES, CONTREXX_CHARSET);
                        $this->arrData['place'][$langId] = htmlentities(stripslashes($objResult->fields['place']), ENT_QUOTES, CONTREXX_CHARSET);
                        $this->arrData['place_street'][$langId] = htmlentities(stripslashes($objResult->fields['place_street']), ENT_QUOTES, CONTREXX_CHARSET);
                        $this->arrData['place_zip'][$langId] = htmlentities(stripslashes($objResult->fields['place_zip']), ENT_QUOTES, CONTREXX_CHARSET);
                        $this->arrData['place_city'][$langId] = htmlentities(stripslashes($objResult->fields['place_city']), ENT_QUOTES, CONTREXX_CHARSET);
                        $this->arrData['place_country'][$langId] = htmlentities(stripslashes($objResult->fields['place_country']), ENT_QUOTES, CONTREXX_CHARSET);
                        $this->arrData['description'][$langId] = stripslashes($objResult->fields['description']);
                        $this->arrData['redirect'][$langId] = htmlentities(stripslashes($objResult->fields['redirect']), ENT_QUOTES, CONTREXX_CHARSET);                         
                        $objResult->MoveNext();
                }
            }
        }                        
    }     
    
    /**
     * Save the event to the database
     *      
     * @param array $data
     * 
     * @return boolean true if saved successfully, false otherwise
     */
    function save($data){
        global $objDatabase, $_LANGID, $_CONFIG, $objInit;
        
        parent::getSettings();

        if(empty($data['startDate']) || empty($data['endDate']) || empty($data['category']) || ($data['seriesStatus'] == 1 && $data['seriesType'] == 2 && empty($data['seriesWeeklyDays']))) {
            return false;
        }
        
        foreach ($_POST['showIn'] as $key => $langId) {
            if(empty($_POST['title'][$langId]) && empty($_POST['title'][$_LANGID])) {
                return false;
            }
        }
        
        list($startDate, $strStartTime) = explode(' ', $data['startDate']);
        list($startHour, $startMin)     = explode(':', $strStartTime);
        
        list($endDate, $strEndTime)     = explode(' ', $data['endDate']);
        list($endHour, $endMin)         = explode(':', $strEndTime);
        //event data
        $id = intval($data['id']);                                                        
        $type = intval($data['type']);
        $startDate = parent::getDateTimestamp($startDate, intval($startHour), intval($startMin));
        $endDate = parent::getDateTimestamp($endDate, intval($endHour), intval($endMin));
        $google = intval($data['map'][$_LANGID]);        
        
        $useCustomDateDisplay = isset($data['showDateSettings']) ? 1 : 0;
        if($objInit->mode == 'backend') {
            $showStartDateList = $data['showStartDateList'];
            $showEndDateList = $data['showEndDateList'];   
            
            // reset time values if "no time" is selected
            if($data['showTimeTypeList'] == 0 ) {
                $showStartTimeList = 0;
                $showEndTimeList = 0;
            } else {
                $showStartTimeList = $data['showStartTimeList'];
                $showEndTimeList = $data['showEndTimeList'];
            }
            
            $showTimeTypeList = $data['showTimeTypeList'];
            
            
            $showStartDateDetail = $data['showStartDateDetail'] ;
            $showEndDateDetail = $data['showEndDateDetail'];

            // reset time values if "no time" is selected
            if( $data['showTimeTypeDetail'] == 0){
                $showStartTimeDetail = 0;
                $showEndTimeDetail = 0;
            } else {
                $showStartTimeDetail = $data['showStartTimeDetail'];
                $showEndTimeDetail = $data['showEndTimeDetail'];
            }
            $showTimeTypeDetail = $data['showTimeTypeDetail'] ; 
        } else {
            $showStartDateList =  ($this->arrSettings['showStartDateList'] == 1);
            $showEndDateList =  ($this->arrSettings['showEndDateList'] == 1);  
            
            $showStartTimeList = ($this->arrSettings['showStartTimeList'] == 1);
            $showEndTimeList = ($this->arrSettings['showEndTimeList'] == 1);       
            
            // reset time values if "no time" is selected
            if($showStartTimeList == 1 || $showEndTimeList == 1) {   
                $showTimeTypeList = 1;       
            } else {        
                $showStartTimeList = 0;
                $showEndTimeList = 0;       
                $showTimeTypeList = 0;        
            }    
            
            $showStartDateDetail = ($this->arrSettings['showStartDateDetail'] == 1);
            $showEndDateDetail =  ($this->arrSettings['showEndDateDetail'] == 1);
            
            $showStartTimeDetail = ($this->arrSettings['showStartTimeDetail'] == 1);
            $showEndTimeDetail = ($this->arrSettings['showEndTimeDetail'] == 1);
            
            // reset time values if "no time" is selected
            if($showStartTimeDetail == 1 || $showEndTimeDetail == 1) {   
                $showTimeTypeDetail = 1;       
            } else {        
                $showStartTimeDetail = 0;
                $showEndTimeDetail = 0;       
                $showTimeTypeDetail = 0;        
            }
        }           
        
        
        $google = intval($data['map'][$_LANGID]);
        $access = intval($data['access']);  
        $priority = intval($data['priority']);          
        $placeMediadir = intval($data['placeMediadir'][$_LANGID]);
        $price = contrexx_addslashes(contrexx_strip_tags($data['price']));
        $link = contrexx_addslashes(contrexx_strip_tags($data['link']));
        $pic = contrexx_addslashes(contrexx_strip_tags($data['picture']));  
        $attach = contrexx_addslashes(contrexx_strip_tags($data['attachment']));     
        $catId = intval($data['category']);   
        $showIn = contrexx_addslashes(contrexx_strip_tags(join(",",$data['showIn'])));
        $invited_groups = join(',', $data['selectedGroups']); 
        $invited_mails = contrexx_addslashes(contrexx_strip_tags($data['invitedMails']));   
        $send_invitation = intval($data['sendInvitation']);               
        $update_invitation_sent =  $send_invitation == 1 ?  "`invitation_sent` = '1'," : "";     
        $registration = intval($data['registration']);      
        $registration_form = intval($data['registrationForm']);      
        $registration_num = intval($data['numSubscriber']);      
        $registration_notification = contrexx_addslashes(contrexx_strip_tags($data['notificationTo']));
        $email_template = intval($data['emailTemplate']);
        $ticket_sales = intval($data['ticketSales']);
        $num_seating = json_encode(explode(',', $data['numSeating']));
        $related_hosts = $data['selectedHosts'];

        
        //frontend picture upload & thumbnail creation
        if($objInit->mode == 'frontend') {
            $unique_id = intval($_REQUEST['unique_id']);
            
            if (!empty($unique_id)) {
                $picture = $this->_handleUpload($unique_id);

                if (!empty($picture)) {
                    $objFile = new File();
                    //delete thumb
                    if (file_exists("{$this->uploadImgPath}$pic.thumb")) {
                        $objFile->delFile($this->uploadImgPath, $this->uploadImgWebPath, "/$pic.thumb");
                    }

                    //delete image
                    if (file_exists("{$this->uploadImgPath}$pic")) {
                        $objFile->delFile($this->uploadImgPath, $this->uploadImgWebPath, "/$pic");
                    }

                    $pic = $picture;
                }
            }
        }
        
        $seriesStatus = intval($data['seriesStatus']); 
        
        
        //series pattern
        $seriesStatus = intval($data['seriesStatus']); 
        
        if($seriesStatus == 1) {
            if(!empty($data['seriesExeptions'])) {
                $exeptions = array();
                
                foreach($data['seriesExeptions'] as $key => $exeptionDate)  {
                    $exeptions[] = parent::getDateTimestamp($exeptionDate, 0, 0) ;  
                }  
                
                sort($exeptions);
                
                $seriesExeptions = join(",", $exeptions);
            }
        
            $seriesType                     = intval($data['seriesType']);
            $seriesPatternCount             = 0;
            $seriesPatternWeekday           = 0;
            $seriesPatternDay               = 0;
            $seriesPatternWeek              = 0;
            $seriesPatternMonth             = 0;
            $seriesPatternType              = 0;
            $seriesPatternDouranceType      = 0;
            $seriesPatternEnd               = 0;

            switch($seriesType) {
                case 1;
                    if ($seriesStatus == 1) {
                        $seriesPatternType          = intval($data['seriesDaily']);
                        if($seriesPatternType == 1) {
                            $seriesPatternWeekday   = 0;
                            $seriesPatternDay       = intval($data['seriesDailyDays']);
                        } else {
                            $seriesPatternWeekday   = "1111100";
                            $seriesPatternDay       = 0;
                        }

                        $seriesPatternWeek          = 0;
                        $seriesPatternMonth         = 0;
                        $seriesPatternCount         = 0;
                    }
                break;
                case 2;
                    if ($seriesStatus == 1) {
                        $seriesPatternWeek          = intval($data['seriesWeeklyWeeks']);

                        for($i=1; $i <= 7; $i++) {
                            if (isset($data['seriesWeeklyDays'][$i])) {
                                $weekdayPattern .= "1";
                            } else {
                                $weekdayPattern .= "0";
                            }
                        }

                        $seriesPatternWeekday       = $weekdayPattern;

                        $seriesPatternCount         = 0;
                        $seriesPatternDay           = 0;
                        $seriesPatternMonth         = 0;
                        $seriesPatternType          = 0;
                    }
                break;
                case 3;
                    if ($seriesStatus == 1) {
                        $seriesPatternType          = intval($data['seriesMonthly']);
                        if($seriesPatternType == 1) {
                            $seriesPatternMonth     = intval($data['seriesMonthlyMonth_1']);
                            $seriesPatternDay       = intval($data['seriesMonthlyDay']);
                            $seriesPatternWeekday   = 0;
                        } else {
                            $seriesPatternCount     = intval($data['seriesMonthlyDayCount']);
                            $seriesPatternMonth     = intval($data['seriesMonthlyMonth_2']);
                            
                            if ($seriesPatternMonth < 1) {
                                // the increment must be at least once a month, otherwise we will end up in a endless loop in the presence
                                $seriesPatternMonth = 1;
                            }
                            $seriesPatternWeekday   = $data['seriesMonthlyWeekday'];
                            $seriesPatternDay       = 0;
                        }

                        $seriesPatternWeek           = 0;
                    }
                break;
            }
                
            $seriesPatternDouranceType  = intval($data['seriesDouranceType']);
            $dateparts                  = explode("-", $startDate);
            
            switch($seriesPatternDouranceType) {
                case 1:
                    $seriesPatternEnd   = 0;
                break;
                case 2:
                    $seriesPatternEnd   = intval($data['seriesDouranceEvents']);
                break;
                case 3:
                    $seriesPatternEnd   = parent::getDateTimestamp($data['seriesDouranceDate'], 0, 0) ;    
                break;
            }
        }                                                                          

        if($id != 0) {
            $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_event
                         SET `type` = '".$type."',
                             `startdate` = '".$startDate."',
                             `enddate` = '".$endDate."',
                             `use_custom_date_display` = '".$useCustomDateDisplay."',
                             `showStartDateList` = '".$showStartDateList."',
                             `showEndDateList` = '".$showEndDateList."',
                             `showStartTimeList` = '".$showStartTimeList."',
                             `showEndTimeList` = '".$showEndTimeList."',
                             `showTimeTypeList` = '".$showTimeTypeList."',
                             `showStartDateDetail` = '".$showStartDateDetail."',
                             `showEndDateDetail` = '".$showEndDateDetail."',
                             `showStartTimeDetail` = '".$showStartTimeDetail."',
                             `showEndTimeDetail` = '".$showEndTimeDetail."',
                             `showTimeTypeDetail` = '".$showTimeTypeDetail."',
                             `google` = '".$google."',
                             `access` = '".$access."',
                             `priority` = '".$priority."',
                             `price` = '".$price."',
                             `link` = '".$link."',
                             `pic` = '".$pic."',
                             `catid` = '".$catId."',
                             `attach` = '".$attach."',
                             `place_mediadir_id` = '".$placeMediadir."',
                             `show_in` = '".$showIn."',
                             `invited_groups` = '".$invited_groups."', 
                             ".$update_invitation_sent."
                             `invited_mails`  = '".$invited_mails."',      
                             `registration` = '".$registration."', 
                             `registration_form` = '".$registration_form."', 
                             `registration_num` = '".$registration_num."', 
                             `registration_notification` = '".$registration_notification."',
                             `email_template` = '".$email_template."',
                             `ticket_sales` = '".$ticket_sales."',
                             `num_seating` = '".$num_seating."',
                             `num_seating` = '".$num_seating."',
                             `num_seating` = '".$num_seating."',
                             `series_status` = '".$seriesStatus."',
                             `series_type` = '".$seriesType."',
                             `series_pattern_count` = '".$seriesPatternCount."',
                             `series_pattern_weekday` = '".$seriesPatternWeekday."',
                             `series_pattern_day` = '".$seriesPatternDay."',
                             `series_pattern_week` = '".$seriesPatternWeek."',
                             `series_pattern_month` = '".$seriesPatternMonth."',
                             `series_pattern_type` = '".$seriesPatternType."',
                             `series_pattern_dourance_type` = '".$seriesPatternDouranceType."',
                             `series_pattern_end` = '".$seriesPatternEnd."',
                             `series_pattern_exceptions` = '".$seriesExeptions."'
                       WHERE id = '".$id."'";
        
            $objResult = $objDatabase->Execute($query);
            
            if ($objResult !== false) {                
                $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_event_field
                                WHERE event_id = '".$id."'";    
                                
                $objResult = $objDatabase->Execute($query);   
                
                $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_event_host
                                WHERE event_id = '".$id."'";    
                                
                $objResult = $objDatabase->Execute($query); 
            } else {
                return false;     
            }                                    
        } else {    
            $objFWUser  = FWUser::getFWUserObject(); 
            $objUser    = $objFWUser->objUser;
                 
            if($objInit->mode == 'frontend') { 
                $status = 1; 
                
                if($this->arrSettings['confirmFrontendEvents'] == 1) {
                    $confirmed = 0;    
                } else {
                    $confirmed = 1;  
                }  
                                                        
                if($objUser->login()) {
                    $author = intval($objUser->getId());
                } else {
                    $author = 0;    
                }                                   
            } else {
                $status = 0; 
                $confirmed = 1;  
                
                $author = intval($objUser->getId());    
            }
                        
            $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_event
                                  (`type`,`startdate`,`enddate`, `use_custom_date_display`, `showStartDateList`,`showEndDateList`,`showStartTimeList`,`showEndTimeList`,`showTimeTypeList`, `showStartDateDetail`,`showEndDateDetail`,`showStartTimeDetail`,`showEndTimeDetail`,`showTimeTypeDetail`,`google`,`access`,`priority`,`price`,`link`,`pic`,`catid`,`attach`, `place_mediadir_id`, `show_in`,`status`,`confirmed`,`invited_groups`,`invited_mails`,`invitation_sent`,`registration`,`registration_form`,`registration_num`,`registration_notification`,`email_template`,`ticket_sales`,`num_seating`,`author`, `series_status`, `series_type`, `series_pattern_count`, `series_pattern_weekday`, `series_pattern_day`, `series_pattern_week`, `series_pattern_month`, `series_pattern_type`, `series_pattern_dourance_type`, `series_pattern_end`, `series_pattern_exceptions`)
                           VALUES ('".$type."','".$startDate."','".$endDate."', '".$useCustomDateDisplay."', '".$showStartDateList."', '".$showEndDateList."', '".$showStartTimeList."', '".$showEndTimeList."', '".$showTimeTypeList."', '".$showStartDateDetail."', '".$showEndDateDetail."', '".$showStartTimeDetail."', '".$showEndTimeDetail."', '".$showTimeTypeDetail."', '".$google."','".$access."','".$priority."','".$price."','".$link."','".$pic."','".$catId."','".$attach."','".$placeMediadir."','".$showIn."','".$status."','".$confirmed."','".$invited_groups."','".$invited_mails."', '".$send_invitation."', '".$registration."', '".$registration_form."','".$registration_num."','".$registration_notification."','".$email_template."','".$ticket_sales."','".$num_seating."','".$author."','".$seriesStatus."','".$seriesType."','".$seriesPatternCount."','".$seriesPatternWeekday."','".$seriesPatternDay."','".$seriesPatternWeek."','".$seriesPatternMonth."','".$seriesPatternType."','".$seriesPatternDouranceType."','".$seriesPatternEnd."','".$seriesExeptions."')";
                           
            
            
            $objResult = $objDatabase->Execute($query); 
            
            if ($objResult !== false) {           
                $id = intval($objDatabase->Insert_ID());
                $this->id = $id;
            } else {
                return false; 
            }
        }
        
        if($id != 0) {
            foreach ($data['showIn'] as $key => $langId) {   
                $title = contrexx_addslashes(contrexx_strip_tags($data['title'][$langId]));
                $place = contrexx_addslashes(contrexx_strip_tags($data['place'][$langId]));
                $street = contrexx_addslashes(contrexx_strip_tags($data['street'][$langId]));
                $zip = contrexx_addslashes(contrexx_strip_tags($data['zip'][$langId]));
                $city = contrexx_addslashes(contrexx_strip_tags($data['city'][$langId]));
                $country = contrexx_addslashes(contrexx_strip_tags($data['country'][$langId]));                
                $description = contrexx_addslashes($data['description'][$langId]);
                $redirect = contrexx_addslashes($data['redirect'][$langId]);  
        
                if($type == 0) {
                    $redirect = '';        
                } else {
                    $description = '';
                } 
                
                $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_event_field
                                      (`event_id`,`lang_id`,`title`,`place`,`place_street`,`place_zip`,`place_city`,`place_country`,`description`,`redirect`) 
                               VALUES ('".intval($id)."','".intval($langId)."','".$title."','".$place."','".$street."','".$zip."','".$city."','".$country."','".$description."','".$redirect."')";
                               
                $objResult = $objDatabase->Execute($query); 
                
                if ($objResult === false) {
                    return false;    
                }
            } 
            
            if(!empty($related_hosts)) {   
                foreach ($related_hosts as $key => $hostId) {  
                    $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_event_host
                                      (`host_id`,`event_id`) 
                               VALUES ('".intval($hostId)."','".intval($id)."')";
                               
                    $objResult = $objDatabase->Execute($query); 
                }
            }
        }   
            
        if($send_invitation == 1) {    
             $objMailManager = new CalendarMailManager();           
             $objMailManager->sendMail(intval($id), 1);
        }  
        
        return true;
    }
    
    /**
     * Delete the event
     *      
     * @return boolean true if deleted successfully, false otherwise
     */
    function delete(){
        global $objDatabase;
        
        $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_event
                   WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_event_field
                            WHERE event_id = '".intval($this->id)."'";
        
            $objResult = $objDatabase->Execute($query);
            if ($objResult !== false) {
                $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_event_host
                                WHERE event_id = '".intval($this->id)."'";    
                                
                $objResult = $objDatabase->Execute($query); 
                if ($objResult !== false) {   
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Export the Event with calendar and stop excuting script
     *      
     * @return null
     */
    function export(){
        global $_CONFIG;
                                     
        //create new calendar                                                 
        $objVCalendar = new vcalendar();
        $objVCalendar->setConfig('unique_id', $_CONFIG['coreGlobalPageTitle']);
        $objVCalendar->setConfig('filename', urlencode($this->title).'.ics'); // set Your unique id     
        //$v->setProperty('X-WR-CALNAME', 'Calendar Sample');  
        //$v->setProperty('X-WR-CALDESC', 'Calendar Description');
        //$v->setProperty('X-WR-TIMEZONE', 'America/Los_Angeles');
        $objVCalendar->setProperty('X-MS-OLK-FORCEINSPECTOROPEN', 'TRUE');
        $objVCalendar->setProperty('METHOD','PUBLISH');
             
        // create an event calendar component                                                                     
        $objVEvent = new vevent(); 
        
        // start  
        $startYear = date("Y", $this->startDate);
        $startMonth = date("m", $this->startDate); 
        $startDay = date("d", $this->startDate);
        $startHour = date("H", $this->startDate);
        $startMinute = date("i", $this->startDate);
        
        $objVEvent->setProperty( 'dtstart', array( 'year'=>$startYear, 'month'=>$startMonth, 'day'=>$startDay, 'hour'=>$startHour, 'min'=>$startMinute, 'sec'=>0 ));
         
        // end  
        $endYear = date("Y", $this->endDate);
        $endMonth = date("m", $this->endDate); 
        $endDay = date("d", $this->endDate);
        $endHour = date("H", $this->endDate);
        $endMinute = date("i", $this->endDate);
          
        $objVEvent->setProperty( 'dtend', array( 'year'=>$endYear, 'month'=>$endMonth, 'day'=>$endDay, 'hour'=>$endHour, 'min'=>$endMinute, 'sec'=>0 )); 
        
        // place   
        if(!empty($this->place)) {  
            $objVEvent->setProperty( 'location', html_entity_decode($this->place, ENT_QUOTES, CONTREXX_CHARSET));
        }
        
        // title
        $objVEvent->setProperty( 'summary', html_entity_decode($this->title, ENT_QUOTES, CONTREXX_CHARSET)); 
        
        // description
        $objVEvent->setProperty( 'description', html_entity_decode(strip_tags($this->description), ENT_QUOTES, CONTREXX_CHARSET)); 
        
        // organizer                         
        $objVEvent->setProperty( 'organizer' , $_CONFIG['coreGlobalPageTitle'].' <'.$_CONFIG['coreAdminEmail'].'>');    
        
        // comment
        //$objVEvent->setProperty( 'comment', 'This is a comment' ); 
        
        // attendee 
        //$objVEvent->setProperty( 'attendee', 'attendee1@icaldomain.net' );
         
        // ressourcen
        //$objVEvent->setProperty( 'resources', 'COMPUTER PROJECTOR' );  
         
        // series type
        //$objVEvent->setProperty( 'rrule', array( 'FREQ' => 'WEEKLY', 'count' => 4));// weekly, four occasions  
        
        // add event to calendar
        $objVCalendar->setComponent ($objVEvent);                        
         
        $objVCalendar->returnCalendar();     
        exit;          
    }
    
    /**
     * set the event start date
     * 
     * @param integer $value start date
     */
    function setStartDate($value){
        $this->startDate = intval($value);
    }
    
    /**
     * set the event end date
     * 
     * @param integer $value End date
     * 
     * @return null
     */
    function setEndDate($value){
        $this->endDate = intval($value);
    }
    
    /**
     * switch status of the event
     *      
     * @return boolean true if status updated, false otherwise
     */
    function switchStatus(){
        global $objDatabase;
        
        if($this->status == 1) {
            $status = 0;
        } else {
            $status = 1;
        }
             
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_event AS event
                     SET event.status = '".intval($status)."'
                   WHERE event.id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * confirm event
     *      
     * @return boolean true if event confirmed, false otherwise
     */
    function confirm(){
        global $objDatabase;    
             
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_event AS event
                     SET event.confirmed = '1'
                   WHERE event.id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }

   
    /**
     * Handle the calendar image upload
     * 
     * @param string $id unique form id
     * 
     * @return string image path
     */
    function _handleUpload($id)
    {
        $tup              = self::getTemporaryUploadPath($id);
        $tmpUploadDir     = ASCMS_PATH.$tup[1].'/'.$tup[2].'/'; //all the files uploaded are in here                       
        $depositionTarget = $this->uploadImgPath; //target folder
        $pic              = '';

        //move all files
        if(!\Cx\Lib\FileSystem\FileSystem::exists($tmpUploadDir))
            throw new Exception("could not find temporary upload directory '$tmpUploadDir'");

        $h = opendir($tmpUploadDir);
        if ($h) {
            while(false !== ($f = readdir($h))) {
                if($f != '..' && $f != '.') {
                    //do not overwrite existing files.
                    $prefix = '';
                    while (file_exists($depositionTarget.$prefix.$f)) {
                        if (empty($prefix)) {
                            $prefix = 0;
                        }
                        $prefix ++;
                    }

                    // move file
                    try {
                        $objFile = new \Cx\Lib\FileSystem\File($tmpUploadDir.$f);
                        $objFile->move($depositionTarget.$prefix.$f, false);
                    } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                        \DBG::msg($e->getMessage());
                    }
                    
                    $imageName = $prefix.$f;
                    $objImage = new ImageManager();
                    $objImage->_createThumb($this->uploadImgPath, $this->uploadImgWebPath, $imageName, 180);

                    $pic = contrexx_input2raw($this->uploadImgWebPath.$imageName);
                }
            }    
        }
                
        return $pic;
    }

}