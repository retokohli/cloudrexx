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

class CalendarException extends \Exception { }

/**
 * Calendar
 *
 * LibClass to manage cms calendar
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */  
class CalendarLibrary
{
    /**
     * Template object
     * 
     * @access public
     * @var object 
     */
    public $_objTpl;  
    
    /**
     * template content
     *
     * @access public
     * @var string 
     */
    public $pageContent; 
    
    /**
     * module name
     *
     * @access public
     * @var string 
     */
    public $moduleName = "calendar";
    
    /**
     * module table prefix
     *
     * @access public
     * @var string 
     */
    public $moduleTablePrefix = "calendar";
    
    /**
     * module language variable prefix
     *
     * @access public
     * @var string 
     */
    public $moduleLangVar  = "CALENDAR";
        
    /**
     * Error message
     *
     * @access public
     * @var string 
     */
    public $errMessage = '';
    
    /**
     * Success message
     *
     * @access public
     * @var type 
     */
    public $okMessage = '';
    
    /**
     * CSV separator
     *
     * @access public
     * @var string
     */
    public $csvSeparator = ';';
    
    /**
     * active frontend languages
     *
     * @access public
     * @var array 
     */
    public $arrFrontendLanguages = array();
    
    /**
     * Settings array
     *
     * @access public
     * @var array 
     */
    public $arrSettings = array();
    
    /**
     * Community group array
     *
     * @access public
     * @var array 
     */
    public $arrCommunityGroups = array();    
        
    /**
     * map field key
     *
     * @var string
     */
    const MAP_FIELD_KEY     = 'map_id';
    
    /**
     * Picture field key
     *
     * @var string
     */
    const PICTURE_FIELD_KEY = 'picture_id';
    
    /**
     * Assign the template path
     * Sets the Global variable for the calendar module
     * 
     * @param string $tplPath Template path
     */
    function __construct($tplPath){                                                                      
        $this->_objTpl = new \Cx\Core\Html\Sigma($tplPath);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);    
        
        $this->_objTpl->setGlobalVariable(array(
            $this->moduleLangVar.'_MODULE_NAME'  => $this->moduleName,
            $this->moduleLangVar.'_CSRF'         => 'csrf='.CSRF::code(),     
            $this->moduleLangVar.'_DATE_FORMAT'  => self::getDateFormat(1),
            $this->moduleLangVar.'_JAVASCRIPT'   => self::getJavascript(),
        ));        
    }         
    
    /**
     * Checks the access level for the given action     
     *      
     * It checks the access level for the given action
     * and return's null if access is granted otherwise it redirect the action
     * to the respective fallback pages.
     *  
     * @param string $strAction possible values are add_event, 
     *                          edit_event, my_events
     * 
     * @return null
     */
    function checkAccess($strAction)
    {
        global $objInit;

        if($objInit->mode == 'backend') {
            //backend access
        } else {
            //frontend access

            $strStatus = '';
            $objFWUser  = FWUser::getFWUserObject();

            //get user attributes
            $objUser         = $objFWUser->objUser;
            $intUserId      = intval($objUser->getId());
            $intUserName    = $objUser->getUsername();
            $bolUserLogin   = $objUser->login();
            $intUserIsAdmin = $objUser->getAdminStatus();                                                                                 

            $accessId = 0; //used to remember which access id the user needs to have. this is passed to Permission::checkAccess() later.
            
            $intUserIsAdmin = false;

            if(!$intUserIsAdmin) {
                self::getSettings();

                switch($strAction) {
                    case 'add_event':  
                       if($this->arrSettings['addEventsFrontend'] == 1 || $this->arrSettings['addEventsFrontend'] == 2) {
                            if($this->arrSettings['addEventsFrontend'] == 2) {
                                if($bolUserLogin) {
                                    $bolAdd = true;
                                } else {
                                    $bolAdd = false;
                                }
                            } else {
                                $bolAdd = true;
                            } 

                            if($bolAdd) {
                                //get groups attributes
                                $arrUserGroups  = array();
                                $objGroup = $objFWUser->objGroup->getGroups($filter = array('is_active' => true, 'type' => 'frontend'));

                                while (!$objGroup->EOF) {
                                    if(in_array($objGroup->getId(), $objUser->getAssociatedGroupIds())) {
                                        $arrUserGroups[] = $objGroup->getId();
                                    }
                                    $objGroup->next();
                                }                  
                            } else {
                                $strStatus = 'login';
                            }
                        } else {
                            $strStatus = 'redirect';
                        }
                        
                        break;
                    case 'edit_event':                
                        if($this->arrSettings['addEventsFrontend'] == 1 || $this->arrSettings['addEventsFrontend'] == 2) {
                            if($bolUserLogin) {         
                                if(isset($_POST['submitFormModifyEvent'])) {
                                    $eventId = intval($_POST['id']);
                                } else {
                                    $eventId = intval($_GET['id']);
                                }                       
                                
                                $objEvent = new CalendarEvent($eventId);
                                
                                if($objEvent->author != $intUserId) {
                                    $strStatus = 'no_access';
                                }
                            } else {
                                $strStatus = 'login';
                            }   
                        } else {  
                            $strStatus = 'redirect';
                        }
                        break;
                    
                    case 'my_events':
                        if($this->arrSettings['addEventsFrontend'] == 1 || $this->arrSettings['addEventsFrontend'] == 2) {
                            if(!$bolUserLogin) {
                                $strStatus = 'login';
                            }
                        } else {  
                            $strStatus = 'redirect';
                        }
                        break;
                }

                switch($strStatus) {
                    case 'no_access':
                        CSRF::header('Location: '.CONTREXX_SCRIPT_PATH.'?section=login&cmd=noaccess');
                        exit();
                        break;
                    case 'login':
                        $link = base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
                        CSRF::header("Location: ".CONTREXX_SCRIPT_PATH."?section=login&redirect=".$link);
                        exit();
                        break;
                    case 'redirect':
                        CSRF::header('Location: '.CONTREXX_SCRIPT_PATH.'?section='.$this->moduleName);   
                        exit();
                        break;
                }
            }
        }
    }
    
    /**
     * Prepares the settings from database to array format
     * 
     * Loads the settings values from the database and assign those values into
     * $this->arrSettings
     * 
     * @return null
     */    
    function getSettings()
    {
        global $objDatabase, $_ARRAYLANG, $objInit;
        
        // only initialize once
        if ($this->arrSettings) {
            return;
        }
        
    	$arrSettings = array();
        $arrDateSettings =  array(
                            'separatorDateList','separatorDateTimeList', 'separatorSeveralDaysList', 'separatorTimeList',
                            'separatorDateDetail','separatorDateTimeDetail', 'separatorSeveralDaysDetail', 'separatorTimeDetail',
                            );

        $objSettings = $objDatabase->Execute("SELECT name,value,options, type FROM  ".DBPREFIX."module_".$this->moduleTablePrefix."_settings ORDER BY name ASC");
        if ($objSettings !== false) {
            while (!$objSettings->EOF) {
                //return date settings
                if($objSettings->fields['type'] == 5 && in_array($objSettings->fields['name'], $arrDateSettings) )
                {
                    $strOptions = $objSettings->fields['options'];
                    $arrOptions = explode(',', $strOptions );
                    $value = $arrOptions[$objSettings->fields['value']];
                    
                    if($objInit->mode == 'backend') {
                        // This is for the preview in settings > Date
                        $arrSettings["{$objSettings->fields['name']}_value"] = htmlspecialchars($_ARRAYLANG["{$value}_VALUE"], ENT_QUOTES, CONTREXX_CHARSET);
                    }
                    $value = $_ARRAYLANG[$value];                    
                    $arrSettings[$objSettings->fields['name']] = htmlspecialchars($value, ENT_QUOTES, CONTREXX_CHARSET);
                } else {
                    //return all exept date settings
                    $arrSettings[$objSettings->fields['name']] = htmlspecialchars($objSettings->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
                }

                $objSettings->MoveNext();
            }
        }
        
        $this->arrSettings = $arrSettings;
    }
    
    /**
     * Used to bulid the option menu from the array
     * 
     * @param type    $arrOptions  options value for the select menu
     * @param integer $intSelected selected option in the select menu
     * 
     * @return string drop down options
     */
    function buildDropdownmenu($arrOptions, $intSelected=null)
    {
        foreach ($arrOptions as $intValue => $strName) {
            $checked = $intValue==$intSelected ? 'selected="selected"' : '';
            $strOptions .= "<option value='".$intValue."' ".$checked.">".htmlspecialchars($strName, ENT_QUOTES, CONTREXX_CHARSET)."</option>";
        }

        return $strOptions;
    }
    
    /**
     * Initialize the active frontend languages array
     * 
     * Fetch the active frontend languages from the database and assign those
     * values into $this->arrFrontendLanguages
     * 
     * @return null
     */
    function getFrontendLanguages()
    {        
        // return $arrLanguages;
        $this->arrFrontendLanguages = \FWLanguage::getActiveFrontendLanguages();
    }
    
    /**
     * Return's the dataformat based on the type
     *
     * Return's the dateformat by the given type
     * 1 => frontend (javascript format alone) else backend
     *
     * @param integer $type type 1 => frontend (javascript format alone) else backend
     *
     * @return string Date format
     */
    function getDateFormat($type=null)
    {
        global $objDatabase;
        
        $objDateFormat = $objDatabase->Execute("SELECT value FROM  ".DBPREFIX."module_".$this->moduleTablePrefix."_settings WHERE name = 'dateFormat' LIMIT 1");
        if ($objDateFormat !== false) {        
            $dateFormat = $objDateFormat->fields['value'];      
        }
        
        if($type == 1) {
            switch ($dateFormat) {
                 case 0:  
                    $dateFormat = 'dd.mm.yy';
                    break;
                 case 1:
                    $dateFormat = 'dd/mm/yy';
                    break;
                 case 2:
                    $dateFormat = 'yy.mm.dd';
                    break;
                 case 3:
                    $dateFormat = 'mm/dd/yy';
                    break;
                 case 4:
                    $dateFormat = 'yy-mm-dd';
                    break;
            }                                                                
        } else {   
            switch ($dateFormat) {
                 case 0:  
                    $dateFormat = 'd.m.Y';
                    break;
                 case 1:
                    $dateFormat = 'd/m/Y';
                    break;
                 case 2:
                    $dateFormat = 'Y.m.d';
                    break;
                 case 3:
                    $dateFormat = 'm/d/Y';
                    break;
                 case 4:
                    $dateFormat = 'Y-m-d';
                    break;
            } 
        }
        
        return $dateFormat;
    }
    
    /**
     * Returns a \DateTime object from a calendar date/time string.
     * The format of a calendar date/time string can be configured
     * in the settings section of the calendar component.
     *
     * Note: In constrast to this method, the method getUserDateTimeFromUser()
     * expects a PHP date/time string.
     *
     * The SUPPLIED calendar date/time string must be in USER timezone.
     * The RETURNED \DateTime object will be in INTERNAL timezone.
     *
     * @param string $date A calendar date/time string in user timezone
     * @param integer $hour Hour value
     * @param integer $minute Minute value
     * @return \DateTime \DateTime object in internal timezone
     */
    function getDateTime($date, $hour = 0, $minute = 0)
    {
        self::getSettings();
        
        switch($this->arrSettings['dateFormat']) {
            case 0:
                $date = str_replace(".", "", $date);                 
                $posYear = 4;
                $posMonth = 2;  
                $posDay = 0;       
                break;
            case 1:                                                
                $date = str_replace("/", "", $date); 
                $posYear = 4;
                $posMonth = 2;  
                $posDay = 0;   
                break;
            case 2:                                               
                $date = str_replace(".", "", $date); 
                $posYear = 0;
                $posMonth = 4;  
                $posDay = 6;
                break;
            case 3:                                           
                
                $date = str_replace("/", "", $date);   
                $posYear = 4;
                $posMonth = 0;  
                $posDay = 2;
                break;
            case 4:   
                $date = str_replace("-", "", $date);  
                $posYear = 0;
                $posMonth = 4;  
                $posDay = 6;
                break;
        }
                                                                   
        $year = substr($date, $posYear,4);
        $month = str_pad(substr($date, $posMonth,2), 2, '0', STR_PAD_LEFT);
        $day = str_pad(substr($date, $posDay,2), 2, '0', STR_PAD_LEFT);
        $hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $minute = str_pad($minute, 2, '0', STR_PAD_LEFT);

        return $this->getInternDateTimeFromUser($year . '-' . $month . '-' . $day . ' ' .$hour . ':' . $minute . ':00');
    }
    
    /**
     * Initilize the available group
     * 
     * Fetch the available group from the database and assign those values into
     * $this->arrCommunityGroups
     * 
     * @return null
     */
    function getCommunityGroups()
    {
        global $objDatabase;

        $arrCommunityGroups = array();

        $objCommunityGroups = $objDatabase->Execute("SELECT
                                                        `group`.`group_id` AS group_id,
                                                        `group`.`group_name` AS group_name,
                                                        `group`.`is_active` AS is_active,
                                                        `group`.`type` AS `type`
                                                      FROM  ".DBPREFIX."access_user_groups AS `group`");
        if ($objCommunityGroups !== false) {
            while (!$objCommunityGroups->EOF) {
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['id'] = intval($objCommunityGroups->fields['group_id']);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['name'] = htmlspecialchars($objCommunityGroups->fields['group_name'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['active'] = intval($objCommunityGroups->fields['is_active']);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['type'] = htmlspecialchars($objCommunityGroups->fields['type'], ENT_QUOTES, CONTREXX_CHARSET);  

                $objCommunityGroups->MoveNext();
            }
        }
                                           
        $this->arrCommunityGroups = $arrCommunityGroups;
    }
    
    /**
     * Return's the billing address javascript
     * 
     * @return string Billing HereDoc phpscript
     */
    function getJavascript()
    {
        $javascript = <<< EOF
<script type="text/javascript" src="lib/datepickercontrol/datepickercontrol.js"></script>
EOF;
        if($_GET['cmd'] == 'register') {
             $javascript .= <<< EOF
             
<script type="text/javascript">
/* <![CDATA[ */
if(\$J('#calendarSelectBillingAddress').length > 0) {
    \$J(document).ready(function() {
        checkSelectBillingAddress();        
    });
    
    \$J('#calendarSelectBillingAddress').change(function() {
        checkSelectBillingAddress();
    });
}

function checkSelectBillingAddress() {
    var displayValue;
    var selectValue =  \$J('#calendarSelectBillingAddress').val();
        
    if(selectValue == 'deviatesFromContact') {
        displayValue = 'block'; 
    } else {
        displayValue = 'none'; 
    }
    
    \$J(".affiliationBilling").each(function() { 
       \$J(this).css('display', displayValue); 
    });  
}

/* ]]> */
</script>
             
EOF;
        }
        
        
        return $javascript;
    }
    
    /**
     * generates the random key
     * 
     * @return string combination of alphabet and number in random order
     */
    function generateKey()
    {
        $arrRandom = array();
        $arrChars = array ('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'); 
        $arrNumerics =  array (0,1,2,3,4,5,6,7,8,9); 
        
        for ($i = 0; $i <= rand(15,40); $i++) {
            $charOrNum = rand(0,1);
            if($charOrNum == 1) {
                $posChar = rand(0,25);
                $upOrLow = rand(0,1);

                if($upOrLow == 0) {
                    $arrRandom[$i] = strtoupper($arrChars[$posChar]);
                } else {
                    $arrRandom[$i] = strtolower($arrChars[$posChar]);
                }
            } else {
                $posNum = rand(0,9);
                $arrRandom[$i] = $arrNumerics[$posNum];
            }
        }
        
        $key = join($arrRandom);
            
        return $key;
    }
    
    /**
     * Returns the escaped value for processing csv
     * 
     * @param string &$value string to be send to the csv
     * 
     * @return string escaped value for csv
     */
    function escapeCsvValue(&$value)
    {
        $value = preg_replace('/\r\n/', "\n", $value);
        $valueModified = str_replace('"', '""', $value);

        if ($valueModified != $value || preg_match('/['.$this->csvSeparator.'\n]+/', $value)) {
            $value = '"'.$valueModified.'"';
        }
        
        return strtolower(CONTREXX_CHARSET) == 'utf-8' ? utf8_decode($value) : $value;
    }

    /**
     * Loads datepicker
     *      
     * @param object  &$datePicker
     * @param integer $cat
     * 
     * @return null
     */
    function loadDatePicker(&$datePicker, $cat = null) {
        global $_CORELANG;
        if($this->_objTpl->placeholderExists($this->moduleLangVar.'_DATEPICKER')) {
            $timestamp = time();
            $datePickerYear = $_REQUEST["yearID"] ? $_REQUEST["yearID"] : date('Y', $timestamp);
            $datePickerMonth = $_REQUEST["monthID"] ? $_REQUEST["monthID"] : date('m', $timestamp);
            $datePickerDay = $_REQUEST["dayID"] ? $_REQUEST["dayID"] : date('d', $timestamp);
            $datePicker = new activeCalendar($datePickerYear, $datePickerMonth, $datePickerDay);
            $datePicker->enableMonthNav("?section=calendar");
            $datePicker->enableDayLinks("?section=calendar");
            $datePicker->setDayNames(explode(',', $_CORELANG['TXT_DAY_ARRAY']));
            $datePicker->setMonthNames(explode(',', $_CORELANG['TXT_MONTH_ARRAY']));

            $eventManagerAllEvents = new CalendarEventManager(null, null, $cat, null, true, false, true);
            $eventManagerAllEvents->getEventList();
            $events = $eventManagerAllEvents->getEventsWithDate();
            foreach($events as $event) {
                $datePicker->setEvent($event["year"], $event["month"], $event["day"], " withEvent");
            }

            $datePicker = $datePicker->showMonth();
        }
    }
    
    /**
     * generates an unique id for each form and user.
     * 
     * @see Calendar::$submissionId
     * 
     * @return $id  integer
     */
    protected function handleUniqueId($key) {
        global $sessionObj;
        if (!isset($sessionObj)) $sessionObj = \cmsSession::getInstance();
        
        $id = 0;
        if (isset($_REQUEST[$key])) { //an id is specified - we're handling a page reload
            $id = intval($_REQUEST[$key]);
        } else { //generate a new id
            if (!isset($_SESSION['calendar_last_id'])) {
                $_SESSION['calendar_last_id'] = 1;
            } else {
                $_SESSION['calendar_last_id'] += 1;
            }
                
            $id = $_SESSION['calendar_last_id'];
        }
        
        $this->_objTpl->setVariable("{$this->moduleLangVar}_".  strtoupper($key), $id);   
        
        return $id;
    }
    
    /**
     * Gets the temporary upload location for files.
     * 
     * @param string  $fieldName    Uploader field name and id
     * @param integer $submissionId     
     * 
     * @throws Exeception
     * 
     * @return array('path','webpath', 'dirname')
     */
    public static function getTemporaryUploadPath($fieldName, $submissionId) {
        global $sessionObj;

        if (!isset($sessionObj)) $sessionObj = \cmsSession::getInstance();
        
        $tempPath = $_SESSION->getTempPath();
        $tempWebPath = $_SESSION->getWebTempPath();
        if($tempPath === false || $tempWebPath === false)
            throw new Exception('could not get temporary session folder');

        $dirname = "event_files_{$fieldName}_{$submissionId}";
        $result = array(
            $tempPath,
            $tempWebPath,
            $dirname
        );
        return $result;
    }
        
    /**
     * Returns all series dates based on the given post data
     *       
     * @return array Array of dates
     */    
    function getExeceptionDates()
    {
        global $_CORELANG;
        
        $exceptionDates = array();
        
        $objEvent = new CalendarEvent();
        $objEvent->loadEventFromPost($_POST);

        $objEventManager = new CalendarEventManager($objEvent->startDate);
        $objEventManager->_setNextSeriesElement($objEvent);
        
        $dayArray = explode(',', $_CORELANG['TXT_CORE_DAY_ABBREV2_ARRAY']);
        foreach ($objEventManager->eventList as $event) {
            $startDate = $event->startDate;
            $endDate   = $event->endDate;
            $exceptionDates[$this->format2userDate($startDate)] = $this->format2userDate($startDate) != $this->format2userDate($endDate)
                                                                    ? $dayArray[$this->formatDateTime2user($startDate, "w")] .", " . $this->format2userDate($startDate) .' - ' . $dayArray[$this->formatDateTime2user($endDate, "w")] .", ". $this->format2userDate($endDate)
                                                                    : $dayArray[$this->formatDateTime2user($startDate, "w")] .", " . $this->format2userDate($startDate);
        }

        return $exceptionDates;
    }

    /**
     * Get component controller object
     *
     * @param string $name  component name  
     *
     * @return \Cx\Core\Core\Model\Entity\SystemComponentController
     * The requested component controller or null if no such component exists
     */
    public function getComponent($name)
    {
        if (empty($name)) {
            return null;
        }

        $componentRepo = \Env::get('cx')
                            ->getDb()
                            ->getEntityManager()
                            ->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');

        $component     = $componentRepo->findOneBy(array('name' => $name));
        if (!$component) {
            throw new CalendarException('The component => '. $name .' not available');
        }
        return $component;
    }

    /**
     * Returns the date/time string (according to the calendar's
     * configuration) from a \DateTime object.
     *
     * The SUPPLIED \DateTime object must be in INTERNAL timezone.
     * The RETURNED date/time string will be in USER timezone.
     *
     * @param \DateTime $dateTime DateTime object in internal timezone
     * @return string A date/time string
     */
    public function format2userDateTime(\DateTime $dateTime)
    {
        return $this->formatDateTime2user($dateTime, $this->getDateFormat() .' H:i');
    }

    /**
     * Returns the date string (according to the calendar's
     * configuration) from a \DateTime object.
     *
     * The SUPPLIED \DateTime object must be in INTERNAL timezone.
     * The RETURNED date string will be in USER timezone.
     *
     * @param \DateTime $dateTime DateTime object in internal timezone
     * @return string A date string
     */
    public function format2userDate(\DateTime $dateTime)
    {
        return $this->formatDateTime2user($dateTime, $this->getDateFormat());
    }

    /**
     * Returns the time string 'H:i' from a \DateTime object
     *
     * The SUPPLIED \DateTime object must be in INTERNAL timezone.
     * The RETURNED time string will be in USER timezone.
     *
     * @param \DateTime $dateTime DateTime object in internal timezone
     * @return string A time string
     */
    public function format2userTime(\DateTime $dateTime)
    {
        return $this->formatDateTime2user($dateTime, 'H:i');
    }

    /**
     * Returns a date/time string from a \DateTime object.
     *
     * The SUPPLIED \DateTime object must be in INTERNAL timezone.
     * The RETURNED date/time string will be in USER timezone.
     *
     * @param \DateTime $dateTime DateTime object in internal timezone
     * @param string $format Format string
     * @return string A date/time string formatted according to $format
     */
    public function formatDateTime2user(\DateTime $dateTime, $format)
    {
        return $this->getUserDateTimeFromIntern($dateTime)
                    ->format($format);
    }

    /**
     * Returns a \DateTime object in user timezone
     *
     * The SUPPLIED \DateTime object must be in INTERNAL timezone.
     * The RETURNED \DateTime object will be in USER timezone.
     *
     * @param \DateTime $dateTime \DateTime object in internal timezone
     * @return \DateTime \DateTime in user timezone
     */
    public function getUserDateTimeFromIntern(\DateTime $dateTime)
    {
        $dateTimeInUserTimezone = clone($dateTime);
        return $this->getComponent('DateTime')->intern2user($dateTimeInUserTimezone);
    }

    /**
     * Returns a \DateTime object from a date/time string.
     *
     * The SUPPLIED date/time string must be in USER timezone.
     * The RETURNED \DateTime object will be in INTERNAL timezone.
     * 
     * @param string $time A date/time string in user timezone
     * @return \DateTime \DateTime object in internal timezone
     */
    public function getInternDateTimeFromUser($time = 'now')
    {
        $dateTime = $this->getComponent('DateTime')->createDateTimeForUser($time);
        return $this->getComponent('DateTime')->user2intern($dateTime);
    }

    /**
     * Returns a \DateTime object from a date/time string.
     *
     * The SUPPLIED date/time string must be in DB timezone.
     * The RETURNED \DateTime object will be in INTERNAL timezone.
     * 
     * @param string $time A date/time string in db timezone
     * @return \DateTime \DateTime object in internal timezone
     */
    public function getInternDateTimeFromDb($time = 'now')
    {
        $dateTime = $this->getComponent('DateTime')->createDateTimeForDb($time);
        return $this->getComponent('DateTime')->db2intern($dateTime);
    }

    /**
     * Returns a \DateTime object in db timezone
     *
     * The SUPPLIED \DateTime object must be in INTERNAL timezone.
     * The RETURNED \DateTime object will be in DB timezone.
     *
     * @param \DateTime $dateTime \DateTime object in internal timezone
     * @return \DateTime \DateTime in db timezone
     */
    public function getDbDateTimeFromIntern(\DateTime $dateTime)
    {
        $dateTimeInDbTimezone = clone($dateTime);
        return $this->getComponent('DateTime')
                    ->intern2db($dateTimeInDbTimezone);
    }
}
