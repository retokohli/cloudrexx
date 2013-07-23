<?php
/**
 * Calendar 
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */


/**
 * Calendar
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */
class Calendar extends CalendarLibrary
{
    /**
     * Event manager object
     *
     * @var object
     */
    private $objEventManager;
    
    /**
     * Start date
     * 
     * Unix timestamp
     *
     * @var integer
     */
    private $startDate;
    
    /**
     * End date 
     * Unix timestamp
     *
     * @var integer
     */
    private $endDate;
    
    /**
     * Category id
     *
     * @var integer
     */
    private $categoryId;
    
    /**
     * Search term
     *
     * @var string
     */
    private $searchTerm;
    
    /**
     * Need authorization
     *
     * @var boolean 
     */    
    private $needAuth;
    
    /**
     * Start position
     *
     * @var integer
     */
    private $startPos;
    
    /**
     * Number of events per  page
     *
     * @var integer
     */
    private $numEvents;
    
    /**
     * Author name
     *
     * @var string
     */
    private $author;
    
    /**
     * Sort direction
     *
     * @var string
     */
    private $sortDirection = 'ASC';

    /**
     * APge Title
     *
     * @var string
     */
    public $pageTitle;
    
    /**
     * meta title
     *
     * @var string
     */
    public $metaTitle;

    /**
     * An id unique per form submission and user.
     * This means an user can submit the same form twice at the same time,
     * and the form gets a different submission id for each submit.
     * @var integer
     */
    protected $submissionId = 0;
    
    /**
     * File upload factory folder widget object
     *
     * @var object
     */
    public $folderWidget;
    
    /**
     * File uploader object
     *
     * @var object
     */
    public $uploader;

    /**
     * Constructor
     * 
     * @global array $_ARRAYLANG
     * @global object $objTemplate
     * @param string $pageContent
     */
    function __construct($pageContent)
    {
        global $_ARRAYLANG, $objTemplate;

        parent::__construct('.');
        parent::getSettings();
        
        $this->pageContent = $pageContent;
    }

    /**
     * Performs the calendar page
     * 
     * @return null
     */
    function getCalendarPage()
    {
        self::loadEventManager();
        

        if(isset($_GET['export'])) {
            $objEvent = new CalendarEvent(intval($_GET['export']));
            $objEvent->export();
        }

        switch ($_REQUEST['cmd']) {
            case 'detail':
                if($_GET['id'] != null && $_GET['date'] != null) {
                    self::showEvent();
                } else {
                    CSRF::header("Location: index.php?section=".$this->moduleName);
                    exit();
                }
                break;
            case 'register':
            case 'sign':
                self::showRegistrationForm();
                break;
            case 'boxes':
                echo "boxes";
                break;
            case 'category':
                self::showCategoryView();
                break;
            case 'add':                
                parent::checkAccess('add_event');
                self::modifyEvent();
                break;
            case 'edit':
                parent::checkAccess('edit_event');
                self::modifyEvent(intval($_GET['id']));
                break;
            case 'my_events':
                parent::checkAccess('my_events');
                self::myEvents();
                break;
            case 'success':
                self::showSuccessPage();
                break;
            case 'list':
            case 'eventlist':
            case 'archive':
            default:
                self::overview();
                break;
        }

        return $this->_objTpl->get();
    }

    /**
     * Loads the event manager
     * 
     * @return null
     */
    function loadEventManager()
    {
        // get startdate
        if (!empty($_GET['from'])) {
            $this->startDate = parent::getDateTimestamp($_GET['from']);
        } else if ($_GET['cmd'] == 'archive') {
            $this->startDate = null;
            $this->sortDirection = 'DESC';
        } else {
            $startDay   = isset($_GET['day']) ? $_GET['day'] : date("d", mktime());
            $startMonth = isset($_GET['month']) ? $_GET['month'] : date("m", mktime());
            $startYear  = isset($_GET['year']) ? $_GET['year'] : date("Y", mktime());

            $startDay = $_GET['cmd'] == 'boxes' ? 1 : $startDay;

            $this->startDate = mktime(0, 0, 0, $startMonth, $startDay, $startYear);

        }

        // get enddate
        if (!empty($_GET['till'])) {
            $this->endDate = parent::getDateTimestamp($_GET['till']);
        } else if ($_GET['cmd'] == 'archive') {
            $this->endDate = mktime();
        } else {
            $endDay   = isset($_GET['endDay']) ? $_GET['endDay'] : date("d", mktime());
            $endMonth = isset($_GET['endMonth']) ? $_GET['endMonth'] : date("m", mktime());
            $endYear  = isset($_GET['endYear']) ? $_GET['endYear'] : date("Y", mktime());

            $endYear = empty($_GET['endYear']) && empty($_GET['endMonth']) ? $endYear+10: $endYear;

            $this->endDate = mktime(23, 59, 59, $endMonth, $endDay, $endYear);
        }


        // get datepicker-time
        if($_REQUEST["yearID"] || $_REQUEST["monthID"] || $_REQUEST["dayID"]) {
            $year = $_REQUEST["yearID"] ? $_REQUEST["yearID"] : date('Y', mktime());
            $month = $_REQUEST["monthID"] ? $_REQUEST["monthID"] : date('m', mktime());
            $day = $_REQUEST["dayID"] ? $_REQUEST["dayID"] : date('d', mktime());
            $this->startDate = mktime(0, 0, 0, $month, $day, $year);
            $this->endDate = mktime(23, 59, 59, $month, $day, $year);
        }

        $this->searchTerm = !empty($_GET['term']) ? contrexx_addslashes($_GET['term']) : null;
        $this->categoryId = !empty($_GET['catid']) ? intval($_GET['catid']) : null;




        if ($_GET['cmd'] == 'boxes' || $_GET['cmd'] == 'category') {
            $this->startPos = 0;
            $this->numEvents = 'n';
        } else if(!isset($_GET['search']) && ($_GET['cmd'] != 'list' && $_GET['cmd'] != 'eventlist' && $_GET['cmd'] != 'archive')) {
            $this->startPos = 0;
            $this->numEvents = $this->arrSettings['numEntrance'];
        } else {
            $this->startPos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
            $this->numEvents = $this->arrSettings['numPaging'];
        }

        if ($_GET['cmd'] == 'detail') {
            $this->numEvents = 'n';
        }

        if ($_GET['cmd'] == 'my_events') {
            $objFWUser = FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser;
            $this->author = intval($objUser->getId());
        } else {
            $this->author = null;
        }
        
        $this->objEventManager = new CalendarEventManager($this->startDate,$this->endDate,$this->categoryId,$this->searchTerm,true,$this->needAuth,true,$this->startPos,$this->numEvents,$this->sortDirection,true,$this->author);
        
        if($_GET['cmd'] != 'detail') {
            $this->objEventManager->getEventList();  
        } else { 
            if($_GET['external'] == 1 && $this->arrSettings['publicationStatus'] == 1) {
                $this->objEventManager->getExternalEvent(intval($_GET['id']), intval($_GET['date'])); 
            } else {
                $this->objEventManager->getEvent(intval($_GET['id']), intval($_GET['date'])); 
            }
        }
    }

    /**
     * performs the overview page
     * 
     * @return null
     */    
    function overview()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        parent::getSettings();

        //check datepicker plugin
        if($this->arrSettings['useDatepicker'] == 1) {
            $startDateInputId = "DPC_edit1_".parent::getDateFormat(1);
            $endDateInputId = "DPC_edit2_".parent::getDateFormat(1);

            parent::loadDatePicker($datePicker, $this->categoryId);
        } else {
            $startDateInputId = "startDate";
            $endDateInputId = "endDate";
            $datePicker = "datePicker";
        }

        $objCategoryManager = new CalendarCategoryManager(true);
        $objCategoryManager->getCategoryList();

        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_SEARCH_TERM' =>  $_ARRAYLANG['TXT_CALENDAR_KEYWORD'],
            'TXT_'.$this->moduleLangVar.'_FROM' =>  $_ARRAYLANG['TXT_CALENDAR_FROM'],
            'TXT_'.$this->moduleLangVar.'_TILL' =>  $_ARRAYLANG['TXT_CALENDAR_TILL'],
            'TXT_'.$this->moduleLangVar.'_CATEGORY' =>  $_ARRAYLANG['TXT_CALENDAR_CAT'],
            'TXT_'.$this->moduleLangVar.'_SEARCH' =>  $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
            'TXT_'.$this->moduleLangVar.'_OCLOCK' =>  $_ARRAYLANG['TXT_CALENDAR_OCLOCK'],
            'TXT_'.$this->moduleLangVar.'_DATE' =>  $_CORELANG['TXT_DATE'],
            $this->moduleLangVar.'_SEARCH_TERM' =>  $_GET['term'],
            $this->moduleLangVar.'_SEARCH_FROM' =>  $_GET['from'],
            $this->moduleLangVar.'_SEARCH_TILL' =>  $_GET['till'],
            $this->moduleLangVar.'_SEARCH_CATEGORIES' =>  $objCategoryManager->getCategoryDropdown(intval($_GET['catid']), 1)  ,
            $this->moduleLangVar.'_SEARCH_START_DATE_INPUT_ID'               => $startDateInputId,
            $this->moduleLangVar.'_SEARCH_END_DATE_INPUT_ID'                 => $endDateInputId,
            $this->moduleLangVar.'_DATEPICKER' => $datePicker
        ));
        
        if($this->objEventManager->countEvents > $this->arrSettings['numPaging'] && (isset($_GET['search']) || $_GET['cmd'] == 'list' || $_GET['cmd'] == 'eventlist' || $_GET['cmd'] == 'archive')) {
            $pagingCmd = !empty($_GET['cmd']) ? '&amp;cmd='.$_GET['cmd'] : '';
            $pagingCategory = !empty($_GET['catid']) ? '&amp;catid='.intval($_GET['catid']) : '';
            $pagingTerm = !empty($_GET['term']) ? '&amp;term='.$_GET['term'] : '';
            $pagingSearch = !empty($_GET['search']) ? '&amp;search='.$_GET['search'] : '';
            $pagingFrom = !empty($_GET['from']) ? '&amp;from='.$_GET['from'] : '';
            $pagingTill = !empty($_GET['till']) ? '&amp;till='.$_GET['till'] : '';


            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_PAGING' =>  getPaging($this->objEventManager->countEvents, $this->startPos, "&amp;section=".$this->moduleName.$pagingCmd.$pagingCategory.$pagingTerm.$pagingSearch.$pagingFrom.$pagingTill, "<b>".$_ARRAYLANG['TXT_CALENDAR_EVENTS']."</b>", true, $this->arrSettings['numPaging']),
            ));
        }

        $this->objEventManager->showEventList($this->_objTpl);
    }

    /**
     * performs the my events page
     * 
     * @return null
     */    
    function myEvents()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        $objCategoryManager = new CalendarCategoryManager(true);
        $objCategoryManager->getCategoryList();

        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_EDIT' =>  $_ARRAYLANG['TXT_CALENDAR_EDIT'],
        ));

        if($this->objEventManager->countEvents > $this->arrSettings['numPaging']) {
            $pagingCmd = !empty($_GET['cmd']) ? '&amp;cmd='.$_GET['cmd'] : '';

            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_PAGING' =>  getPaging($this->objEventManager->countEvents, $this->startPos, "&amp;section=".$this->moduleName.$pagingCmd, "<b>".$_ARRAYLANG['TXT_CALENDAR_EVENTS']."</b>", true, $this->arrSettings['numPaging']),
            ));
        }

        $this->objEventManager->showEventList($this->_objTpl);
    }

    /**
     * Add / Edit Event
     * 
     * @param integer $eventId Event id
     * 
     * @return null
     */    
    function modifyEvent($eventId = null)
    {
        global $_ARRAYLANG, $_CORELANG, $_LANGID;
        JS::activate('cx');
        JS::registerJS('modules/calendar/View/Script/Frontend.js');
        
        $this->_objTpl->setTemplate($this->pageContent, true, true);
               
        $showFrom = true;
        if(isset($_POST['submitFormModifyEvent'])) {
            $objEvent = new CalendarEvent();

            $arrData = array();
            $arrData = $_POST;

            $arrData['type'] = 0;
            $arrData['access'] = 0;
            $arrData['priority'] = 3;
            $arrData['showIn'][0] = $_LANGID;

            if($objEvent->save($arrData)) {
                $showFrom = false;
                $this->_objTpl->hideBlock('calendarEventModifyForm');
                $this->_objTpl->touchBlock('calendarEventOkMessage');
                
                $objMailManager = new CalendarMailManager();
                $objMailManager->sendMail($objEvent->id, 4);
            } else {
                $this->_objTpl->touchBlock('calendarEventErrMessage');
            }
        }
        
        if($eventId != null) {
            $objEvent = new CalendarEvent($eventId);
            $objEvent->getData();
        }

        $dateFormat = parent::getDateFormat(1);

        //check datepicker plugin
        if($this->arrSettings['useDatepicker'] == 1) {
            $startDateInputId = "DPC_edit1_".parent::getDateFormat(1);
            $endDateInputId = "DPC_edit2_".parent::getDateFormat(1);
        } else {
            $startDateInputId = "startDate";
            $endDateInputId = "endDate";
        }

        $javascript = <<< EOF
<script language="JavaScript" type="text/javascript">

function ExpandMinimize(toggle, opener){
    elm1 = document.getElementById(toggle);
    elm1.style.display = (elm1.style.display=='none') ? 'block' : 'none';

    if(opener != '') {
        elm2 = document.getElementById(opener);
        elm2.style.display = (elm2.style.display=='none') ? 'inline' : 'none';
    }
}
cx.ready(function() {
    var options = {
        dateFormat: '$dateFormat',        
        timeFormat: 'hh:mm',
        onSelect: function(dateText, inst) {
            startDateTime = jQuery(".startDate").datetimepicker("getDate").getTime() / 1000;
            endDateTime   = jQuery(".endDate").datetimepicker("getDate").getTime() / 1000;                

            if (startDateTime > endDateTime) {
                jQuery(".endDate").datetimepicker('setDate', jQuery(".startDate").val());
            }
        },
        showSecond: false
    };
    jQuery('input[name=startDate]').datetimepicker(options);
    jQuery('input[name=endDate]').datetimepicker(options);
});

</script>
EOF;
        
        if ($showFrom) {
            try {
                $this->handleUniqueId();
                $this->initUploader();
            
                $javascript .= <<< UPLOADER
{$this->uploader->getXHtml()}
{$this->folderWidget->getXHtml('#calendarForm_uploadWidget', 'uploadWidget')}
<script type="text/javascript">    
        cx.include(
        ["core_modules/contact/js/extendedFileInput.js"],
        function() {
        var ef = new ExtendedFileInput({
        field: \$J("#pictureUpload")
        });
        }
        )    
</script>
UPLOADER;
            } catch(Exception $e) {
                \DBG::msg("Error in initializing uploader");
            } 
        }

        $this->_objTpl->setGlobalVariable(array(
            $this->moduleLangVar.'_EVENT_LANG_ID'               => $_LANGID,
            $this->moduleLangVar.'_JAVASCRIPT'                  => $javascript,
            $this->moduleLangVar.'_EVENT_START_DATE_INPUT_ID'   => $startDateInputId,
            $this->moduleLangVar.'_EVENT_END_DATE_INPUT_ID'     => $endDateInputId,
        ));

        $objCategoryManager = new CalendarCategoryManager(true);
        $objCategoryManager->getCategoryList();
        
$this->_objTpl->setVariable(array(
            'TXT_'.$this->moduleLangVar.'_EVENT'                    =>  $_ARRAYLANG['TXT_CALENDAR_EVENT'],
            'TXT_'.$this->moduleLangVar.'_EVENT_DETAILS'            =>  $_ARRAYLANG['TXT_CALENDAR_EVENT_DETAILS'],
            'TXT_'.$this->moduleLangVar.'_SAVE'                     => $_ARRAYLANG['TXT_CALENDAR_SAVE'],
            'TXT_'.$this->moduleLangVar.'_EVENT_START'              => $_ARRAYLANG['TXT_CALENDAR_START'],
            'TXT_'.$this->moduleLangVar.'_EVENT_END'                => $_ARRAYLANG['TXT_CALENDAR_END'],
            'TXT_'.$this->moduleLangVar.'_EVENT_TITLE'              => $_ARRAYLANG['TXT_CALENDAR_TITLE'],
            'TXT_'.$this->moduleLangVar.'_EXPAND'                   => $_ARRAYLANG['TXT_CALENDAR_EXPAND'],
            'TXT_'.$this->moduleLangVar.'_MINIMIZE'                 => $_ARRAYLANG['TXT_CALENDAR_MINIMIZE'],
            'TXT_'.$this->moduleLangVar.'_EVENT_PLACE'              => $_ARRAYLANG['TXT_CALENDAR_EVENT_PLACE'],
            'TXT_'.$this->moduleLangVar.'_EVENT_STREET'             => $_ARRAYLANG['TXT_CALENDAR_EVENT_STREET'],
            'TXT_'.$this->moduleLangVar.'_EVENT_ZIP'                => $_ARRAYLANG['TXT_CALENDAR_EVENT_ZIP'],
            'TXT_'.$this->moduleLangVar.'_EVENT_CITY'               => $_ARRAYLANG['TXT_CALENDAR_EVENT_CITY'],
            'TXT_'.$this->moduleLangVar.'_EVENT_COUNTRY'            => $_ARRAYLANG['TXT_CALENDAR_EVENT_COUNTRY'],
            'TXT_'.$this->moduleLangVar.'_EVENT_MAP'                => $_ARRAYLANG['TXT_CALENDAR_EVENT_MAP'],
            'TXT_'.$this->moduleLangVar.'_EVENT_USE_GOOGLEMAPS'     => $_ARRAYLANG['TXT_CALENDAR_EVENT_USE_GOOGLEMAPS'],
            'TXT_'.$this->moduleLangVar.'_EVENT_LINK'               => $_ARRAYLANG['TXT_CALENDAR_EVENT_LINK'],
            'TXT_'.$this->moduleLangVar.'_EVENT_PICTURE'            => $_ARRAYLANG['TXT_CALENDAR_EVENT_PICTURE'],
            'TXT_'.$this->moduleLangVar.'_EVENT_CATEGORY'           => $_ARRAYLANG['TXT_CALENDAR_CAT'] ,
            'TXT_'.$this->moduleLangVar.'_EVENT_DESCRIPTION'        => $_ARRAYLANG['TXT_CALENDAR_EVENT_DESCRIPTION'],
            'TXT_'.$this->moduleLangVar.'_PLEASE_CHECK_INPUT'       => $_ARRAYLANG['TXT_CALENDAR_PLEASE_CHECK_INPUT'],

            $this->moduleLangVar.'_EVENT_START_DATE'                => $eventId != 0 ? date(parent::getDateFormat()." H:i", $objEvent->startDate) : date(parent::getDateFormat()." H:i"),
            $this->moduleLangVar.'_EVENT_END_DATE'                  => $eventId != 0 ? date(parent::getDateFormat()." H:i", $objEvent->endDate) : date(parent::getDateFormat()." H:i"),
            $this->moduleLangVar.'_EVENT_PICTURE'                   => $objEvent->pic,
            $this->moduleLangVar.'_EVENT_PICTURE_THUMB'             => $objEvent->pic != '' ? '<img src="'.$objEvent->pic.'.thumb" alt="'.$objEvent->title.'" title="'.$objEvent->title.'" />' : '',
            $this->moduleLangVar.'_EVENT_CATEGORIES'                => $objCategoryManager->getCategoryDropdown(intval($objEvent->catId), 2),
            $this->moduleLangVar.'_EVENT_PICTURE'                   => $objEvent->pic,
            $this->moduleLangVar.'_EVENT_LINK'                      => $objEvent->link,
            $this->moduleLangVar.'_EVENT_TITLE'                     => $objEvent->title,
            $this->moduleLangVar.'_EVENT_PLACE'                     => $objEvent->place,
            $this->moduleLangVar.'_EVENT_STREET'                    => $objEvent->arrData['place_street'][$_LANGID],
            $this->moduleLangVar.'_EVENT_ZIP'                       => $objEvent->arrData['place_zip'][$_LANGID],
            $this->moduleLangVar.'_EVENT_CITY'                      => $objEvent->arrData['place_city'][$_LANGID],
            $this->moduleLangVar.'_EVENT_COUNTRY'                   => $objEvent->arrData['place_country'][$_LANGID],
            $this->moduleLangVar.'_EVENT_MAP'                       => $objEvent->map == 1 ? 'checked="checked"' : '',
            $this->moduleLangVar.'_EVENT_DESCRIPTION'               => $objEvent->description,
            $this->moduleLangVar.'_EVENT_ID'                        => $eventId,
        ));
        
        //parse placeSelect
        if ($this->arrSettings['placeData'] != 0) {
            $objMediadirEntries = new mediaDirectoryEntry();
            $objMediadirEntries->getEntries(null,null,null,null,null,null,true,0,'n',null,null,intval($this->arrSettings['placeData']));

            $placeOptions = '<option value="">'.$_ARRAYLANG['TXT_CALENDAR_PLEASE_CHOOSE'].'</option>';

            foreach($objMediadirEntries->arrEntries as $key => $arrEntry) {
                $selectedPlace = ($arrEntry['entryId'] == $objEvent->place) ? 'selected="selected"' : '';
                $placeOptions .= '<option '.$selectedPlace.' value="'.$arrEntry['entryId'].'">'.$arrEntry['entryFields'][0].'</option>';
            }

            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_EVENT_PLACE_OPTIONS'    => $placeOptions,
            ));

            $this->_objTpl->hideBlock('eventPlaceInput');
            $this->_objTpl->parse('eventPlaceSelect');
        } else {
            $this->_objTpl->touchBlock('eventPlaceInput');
            $this->_objTpl->hideBlock('eventPlaceSelect');
        }

    }

    /**
     * Performs the Event details page
     * 
     * @return null
     */    
    function showEvent()
    {
        global $_ARRAYLANG, $_CORELANG, $_LANGID;

        
        $this->_objTpl->setTemplate($this->pageContent, true, true);
        
        $this->pageTitle = $this->objEventManager->eventList[0]->title;
        
        $this->_objTpl->setVariable(array(
            'TXT_'.$this->moduleLangVar.'_ATTACHMENT'        =>  $_ARRAYLANG['TXT_CALENDAR_ATTACHMENT'],
            'TXT_'.$this->moduleLangVar.'_THUMBNAIL'         =>  $_ARRAYLANG['TXT_CALENDAR_THUMBNAIL'],
            'TXT_'.$this->moduleLangVar.'_OPTIONS'           =>  $_ARRAYLANG['TXT_CALENDAR_OPTIONS'],
            'TXT_'.$this->moduleLangVar.'_CATEGORY'          =>  $_ARRAYLANG['TXT_CALENDAR_CAT'],
            'TXT_'.$this->moduleLangVar.'_PLACE'             =>  $_ARRAYLANG['TXT_CALENDAR_PLACE'],
            'TXT_'.$this->moduleLangVar.'_PRIORITY'          =>  $_ARRAYLANG['TXT_CALENDAR_PRIORITY'],
            'TXT_'.$this->moduleLangVar.'_START'             =>  $_ARRAYLANG['TXT_CALENDAR_START'],
            'TXT_'.$this->moduleLangVar.'_END'               =>  $_ARRAYLANG['TXT_CALENDAR_END'],
            'TXT_'.$this->moduleLangVar.'_COMMENT'           =>  $_ARRAYLANG['TXT_CALENDAR_COMMENT'],
            'TXT_'.$this->moduleLangVar.'_OCLOCK'            =>  $_ARRAYLANG['TXT_CALENDAR_OCLOCK'],
            'TXT_'.$this->moduleLangVar.'_EXPORT'            =>  $_ARRAYLANG['TXT_CALENDAR_EXPORT'],
            'TXT_'.$this->moduleLangVar.'_EVENT_PRICE'       =>  $_ARRAYLANG['TXT_CALENDAR_EVENT_PRICE'],
            'TXT_'.$this->moduleLangVar.'_EVENT_FREE_PLACES' =>  $_ARRAYLANG['TXT_CALENDAR_EVENT_FREE_PLACES'],
            'TXT_'.$this->moduleLangVar.'_DATE'              =>  $_CORELANG['TXT_DATE'],
        ));
         
        $this->objEventManager->showEvent($this->_objTpl, intval($_GET['id']), intval($_GET['date']));
    }

    /**
     * performs the registratio page
     * 
     * @return null
     */    
    function showRegistrationForm()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        $objFWUser      = FWUser::getFWUserObject();
        $objUser        = $objFWUser->objUser;
        $userId         = intval($objUser->getId());
        $userLogin      = $objUser->login();
        $captchaCheck   = true;

        if(!$userLogin && isset($_POST['submitRegistration'])) {
            $captchaCheck =  \FWCaptcha::getInstance()->check();
            if (!$captchaCheck) {
                $this->_objTpl->setVariable(array(
                    'TXT_'.$this->moduleLangVar.'_ERROR' => '<br /><font color="#ff0000">'.$_ARRAYLANG['TXT_CALENDAR_INVALID_CAPTCHA_CODE'].'</font>',
                ));
            }
        }
        
        $objEvent = new CalendarEvent(intval($_REQUEST['id']));
        $objRegistrationManager = new CalendarRegistrationManager($objEvent->id,true,false);
        $objRegistrationManager->getRegistrationList();
        $numRegistrations = intval(count($objRegistrationManager->registrationList));
        
        $this->pageTitle = date("d.m.Y", $objEvent->startDate).": ".$objEvent->title;

        if(mktime() <= intval($_REQUEST['date'])) {
            if($numRegistrations < $objEvent->numSubscriber) {
                $this->_objTpl->setVariable(array(
                    $this->moduleLangVar.'_EVENT_ID'                   =>  intval($_REQUEST['id']),
                    $this->moduleLangVar.'_FORM_ID'                    =>  intval($objEvent->registrationForm),
                    $this->moduleLangVar.'_EVENT_DATE'                 =>  intval($_REQUEST['date']),
                    $this->moduleLangVar.'_USER_ID'                    =>  $userId,
                    'TXT_'.$this->moduleLangVar.'_REGISTRATION_SUBMIT' =>  $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_SUBMIT'],
                ));

                $objFormManager = new CalendarFormManager();
                $objFormManager->getFormList();
                $arrNumSeating = $objEvent->ticketSales ? $objEvent->arrNumSeating : 0;
                $objFormManager->showForm($this->_objTpl,intval($objEvent->registrationForm), 2, $arrNumSeating);
                

                if ($this->arrSettings['paymentStatus'] == '1' && $objEvent->ticketSales && ($this->arrSettings['paymentBillStatus'] == '1' || $this->arrSettings['paymentYellowpayStatus'] == '1')) {
                    $paymentMethods  = '<select class="calendarSelect" name="paymentMethod">';
                    $paymentMethods .= $this->arrSettings['paymentBillStatus'] == '1' || $objEvent->price == 0 ? '<option value="1">'.$_ARRAYLANG['TXT_CALENDAR_PAYMENT_BILL'].'</option>'  : '';
                    $paymentMethods .= $this->arrSettings['paymentYellowpayStatus'] == '1' && $objEvent->price > 0 ? '<option value="2">'.$_ARRAYLANG['TXT_CALENDAR_PAYMENT_YELLOWPAY'].'</option>' : '';
                    $paymentMethods .= '</select>';

                    $this->_objTpl->setVariable(array(
                        'TXT_'.$this->moduleLangVar.'_PAYMENT_METHOD' => $_ARRAYLANG['TXT_CALENDAR_PAYMENT_METHOD'],
                        $this->moduleLangVar.'_PAYMENT_METHODS'       => $paymentMethods,
                    ));
                    $this->_objTpl->parse('calendarRegistrationPayment');
                } else {
                    $this->_objTpl->hideBlock('calendarRegistrationPayment');
                }

                if(!$userLogin) {
                    
                    $this->_objTpl->setVariable(array(
                        'TXT_'.$this->moduleLangVar.'_CAPTCHA' => $_CORELANG['TXT_CAPTCHA'],
                        $this->moduleLangVar.'_CAPTCHA_CODE'   => \FWCaptcha::getInstance()->getCode(),
                    ));
                    $this->_objTpl->parse('calendarRegistrationCaptcha');
                } else {
                    $this->_objTpl->hideBlock('calendarRegistrationCaptcha');
                }

                if(isset($_POST['submitRegistration']) && $captchaCheck) {
                    $objRegistration = new CalendarRegistration(intval($_POST['form']));

                    if($objRegistration->save($_POST)) {
                        if ($objRegistration->saveIn == 2) {
                            $status = $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_SUCCESSFULLY_ADDED_WAITLIST'];
                        } else if ($objRegistration->saveIn == 0) {
                            $status =$_ARRAYLANG['TXT_CALENDAR_REGISTRATION_SUCCESSFULLY_ADDED_SIGNOFF'];
                        } else {
                            $status = $_ARRAYLANG['TXT_CALENDAR_REGISTRATION_SUCCESSFULLY_ADDED'];
                            if($_POST["paymentMethod"] == 2) {
                                $objRegistration->get($objRegistration->id);
                                $objEvent = new CalendarEvent($objRegistration->eventId);
                                parent::getSettings();
                                $amount = $objEvent->price * $objRegistration->fields[7]["value"];
                                $amount = (int)$amount*100;
                                $status .= CalendarPayment::_yellowpay(array("orderID" => $objRegistration->id, "amount" => $amount, "currency" => $this->arrSettings["paymentCurrency"], "language" => "DE"));
                            }
                        }
                        $this->_objTpl->setVariable(array(
                            $this->moduleLangVar.'_LINK_BACK' =>  '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section='.$this->moduleName.'">'.$_ARRAYLANG['TXT_CALENDAR_BACK'].'</a>',
                            $this->moduleLangVar.'_REGISTRATION_STATUS' =>  $status,
                        ));

                        $this->_objTpl->touchBlock('calendarRegistrationStatus');
                        $this->_objTpl->hideBlock('calendarRegistrationForm');
                    } else {                        
                        $this->_objTpl->setVariable(array(
                            'TXT_'.$this->moduleLangVar.'_ERROR' => '<br /><font color="#ff0000">'.$_ARRAYLANG['TXT_CALENDAR_CHECK_REQUIRED'].'</font>',
                        ));

                        $this->_objTpl->parse('calendarRegistrationForm');
                        $this->_objTpl->hideBlock('calendarRegistrationStatus');
                    }
                } else {
                    $this->_objTpl->parse('calendarRegistrationForm');
                    $this->_objTpl->hideBlock('calendarRegistrationStatus');
                }
            } else {
                $this->_objTpl->setVariable(array(
                    $this->moduleLangVar.'_LINK_BACK' =>  '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section='.$this->moduleName.'">'.$_ARRAYLANG['TXT_CALENDAR_BACK'].'</a>',
                    $this->moduleLangVar.'_REGISTRATION_STATUS' =>  $_ARRAYLANG['TXT_CALENDAR_EVENT_FULLY_BLOCKED'],
                ));

                $this->_objTpl->touchBlock('calendarRegistrationStatus');
                $this->_objTpl->hideBlock('calendarRegistrationForm');
            }
        } else {
            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_LINK_BACK' =>  '<a href="'.CONTREXX_DIRECTORY_INDEX.'?section='.$this->moduleName.'">'.$_ARRAYLANG['TXT_CALENDAR_BACK'].'</a>',
                $this->moduleLangVar.'_REGISTRATION_STATUS' =>  $_ARRAYLANG['TXT_CALENDAR_EVENT_IN_PAST'],
            ));

            $this->_objTpl->touchBlock('calendarRegistrationStatus');
            $this->_objTpl->hideBlock('calendarRegistrationForm');
        }
    }

    /**
     * set the placeholders for the category view
     * 
     * @return null
     */    
    function showCategoryView()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        $objCategoryManager = new CalendarCategoryManager(true);
        $objCategoryManager->getCategoryList();

        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_SEARCH_TERM' =>  $_ARRAYLANG['TXT_CALENDAR_KEYWORD'],
            'TXT_'.$this->moduleLangVar.'_FROM' =>  $_ARRAYLANG['TXT_CALENDAR_FROM'],
            'TXT_'.$this->moduleLangVar.'_TILL' =>  $_ARRAYLANG['TXT_CALENDAR_TILL'],
            'TXT_'.$this->moduleLangVar.'_CATEGORY' =>  $_ARRAYLANG['TXT_CALENDAR_CAT'],
            'TXT_'.$this->moduleLangVar.'_SEARCH' =>  $_ARRAYLANG['TXT_CALENDAR_SEARCH'],
            'TXT_'.$this->moduleLangVar.'_OCLOCK' =>  $_ARRAYLANG['TXT_CALENDAR_OCLOCK'],
            $this->moduleLangVar.'_SEARCH_TERM' =>  $_GET['term'],
            $this->moduleLangVar.'_SEARCH_FROM' =>  $_GET['from'],
            $this->moduleLangVar.'_SEARCH_TILL' =>  $_GET['till'],
            $this->moduleLangVar.'_SEARCH_CATEGORIES' =>  $objCategoryManager->getCategoryDropdown(intval($_GET['catid']), 1)
        ));

        if(isset($this->categoryId)) {
            $objCategory = new CalendarCategory($this->categoryId);
            $this->_objTpl->setGlobalVariable(array(
                $this->moduleLangVar.'_CATEGORY_NAME' =>  $objCategory->name,
            ));

            $this->objEventManager->showEventList($this->_objTpl);

            $this->_objTpl->parse('categoryList');
        } else {
            foreach ($objCategoryManager->categoryList as $key => $objCategory) {
                $objEventManager = new CalendarEventManager($this->startDate,$this->endDate,$objCategory->id,$this->searchTerm,true,$this->needAuth,true,$this->startPos,$this->numEvents);
                $objEventManager->getEventList();

                $objEventManager->showEventList($this->_objTpl);

                $this->_objTpl->setGlobalVariable(array(
                    $this->moduleLangVar.'_CATEGORY_NAME' =>  $objCategory->name,
                ));

                $this->_objTpl->parse('categoryList');
            }
        }
    }

    /**
     * Display the success page
     * 
     * @return null
     */    
    function showSuccessPage() {
        $this->_objTpl->setTemplate($this->pageContent, true, true);
        if($_REQUEST["handler"] == "yellowpay") {
            switch($_REQUEST["result"]) {
                case 2:
                    // fehler aufgetreten
                    $objRegistration = new CalendarRegistration(null);
                    $objRegistration->delete($_REQUEST["orderID"]);
                    $this->_objTpl->touchBlock("cancelMessage");
                    break;
                case 1:
                    // erfolgreich
                    $objRegistration = new CalendarRegistration(null);
                    $objRegistration->get($_REQUEST["orderID"]);
                    $objRegistration->setPayed(1);
                    $this->_objTpl->touchBlock("successMessage");
                    break;
                case 0:
                    // abgebrochen
                    $objRegistration = new CalendarRegistration(null);
                    $objRegistration->delete($_REQUEST["orderID"]);
                    $this->_objTpl->touchBlock("cancelMessage");
                    break;
                default:
                    CSRF::header("Location: index.php?section=".$this->moduleName);
                    break;
            }
        } else {
            CSRF::header("Location: index.php?section=".$this->moduleName);
            return;
        }
    }
        
    /**
     * Inits the uploader when displaying a form.
     * 
     * @throws Exception
     * 
     * @return null
     */
    protected function initUploader() {
        try {
            //init the uploader
            JS::activate('cx'); //the uploader needs the framework
            $f = UploadFactory::getInstance();
            
            /**
            * Name of the upload instance
            */
            $uploaderInstanceName = 'exposed_combo_uploader';

            //retrieve temporary location for uploaded files
            $tup = self::getTemporaryUploadPath($this->submissionId);

            //create the folder
            if (!\Cx\Lib\FileSystem\FileSystem::make_folder($tup[1].'/'.$tup[2])) {
                throw new Exception("Could not create temporary upload directory '".$tup[0].'/'.$tup[2]."'");
            }

            if (!\Cx\Lib\FileSystem\FileSystem::makeWritable($tup[1].'/'.$tup[2])) {
                //some hosters have problems with ftp and file system sync.
                //this is a workaround that seems to somehow show php that
                //the directory was created. clearstatcache() sadly doesn't
                //work in those cases.
                @closedir(@opendir($tup[0]));

                if (!\Cx\Lib\FileSystem\FileSystem::makeWritable($tup[1].'/'.$tup[2])) {
                    throw new Exception("Could not chmod temporary upload directory '".$tup[0].'/'.$tup[2]."'");
                }
            }
            
            //initialize the widget displaying the folder contents
            $this->folderWidget = $f->newFolderWidget($tup[0].'/'.$tup[2]);
         
            $this->uploader = $f->newUploader('exposedCombo');
            $this->uploader->setJsInstanceName($uploaderInstanceName);
            $this->uploader->setFinishedCallback(array(ASCMS_MODULE_PATH.'/calendar/index.class.php','Calendar','uploadFinished'));
            $this->uploader->setData($this->submissionId);
            
        } catch (Exception $e) {
            \DBG::msg('<!-- failed initializing uploader -->');
            throw new Exception("failed initializing uploader");
        }
    }

    /**
     * Uploader callback function
     * 
     * @param string  $tempPath    Temp path
     * @param string  $tempWebPath Temp webpath
     * @param string  $data        post data
     * @param integer $uploadId    upload id
     * @param array   $fileInfos   file infos
     * @param object  $response    Upload api response object
     * 
     * @return array path and webpath
     */
    public static function uploadFinished($tempPath, $tempWebPath, $data, $uploadId, $fileInfos, $response) {
        global $objDatabase, $_ARRAYLANG, $_CONFIG, $objInit;
        
        $lang     = $objInit->loadLanguageData('calendar');
        $tup      = self::getTemporaryUploadPath($uploadId);
        $path     = $tup[0].'/'.$tup[2];
        $webPath  = $tup[1].'/'.$tup[2];
        $arrFiles = array();
        
        //get allowed file types
        $arrAllowedFileTypes = array();
        if (imagetypes() & IMG_GIF) { $arrAllowedFileTypes[] = 'gif'; }
        if (imagetypes() & IMG_JPG) { $arrAllowedFileTypes[] = 'jpg'; $arrAllowedFileTypes[] = 'jpeg'; }
        if (imagetypes() & IMG_PNG) { $arrAllowedFileTypes[] = 'png'; }

        $h = opendir($tempPath);
        if ($h) {            
            
            while(false != ($file = readdir($h))) {

                $info = pathinfo($file);                

                //skip . and ..
                if($file == '.' || $file == '..') { continue; }
                
                //delete unwanted files
                if(!in_array(strtolower($info['extension']), $arrAllowedFileTypes)) {                                     
                    $response->addMessage(
                        UploadResponse::STATUS_ERROR,
                        $lang["TXT_{$this->moduleLangVar}_IMAGE_UPLOAD_ERROR"],
                        $file
                    );
                    \Cx\Lib\FileSystem\FileSystem::delete_file($tempPath.'/'.$file);
                    continue;
                }   
                
                $arrFiles[] = $file;
            }
            closedir($h);
            
        }
        
        // Delete existing files because we need only one file to upload
        if (!empty($arrFiles)) {
            $h = opendir($path);
            if ($h) {
                while(false != ($file = readdir($h))) {
                    //skip . and ..
                    if($file == '.' || $file == '..') { continue; }
                    \Cx\Lib\FileSystem\FileSystem::delete_file($path.'/'.$file);
                }
            }
        }
        
        return array($path, $webPath);
    }
 
}
