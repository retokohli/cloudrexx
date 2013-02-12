<?php
/**
 * This is the settings class file for handling the all functionalities under settings menu.
 *
 * PHP version 5.3 or >
 *
 * @category Settings
 * @package  PM_CRM_Tool
 * @author   ss4ugroup <ss4ugroup@softsolutions4u.com>
 * @license  BSD Licence
 * @version  1.0.0
 * @link     http://mycomvation.com/po/cadmin
 */

/**
 * This is the settings class file for handling the all functionalities under settings menu.
 *
 * @category Settings
 * @package  PM_CRM_Tool
 * @author   ss4ugroup <ss4ugroup@softsolutions4u.com>
 * @license  BSD Licence
 * @version  1.0.0
 * @link     http://mycomvation.com/po/cadmin
 */

class Settings extends CrmLibrary
{

    /**
     * Template object
     *     
     * @param object
     */
    public $_objTpl;

    /**
     * php 5.3 contructor
     *
     * @param object $objTpl
     */
    function  __construct($objTpl)
    {
        $this->_objTpl = $objTpl;
        parent::__construct();
    }

    /**
     * customer settings overview
     *
     * @global array $_CORELANG
     * @global array $_ARRAYLANG
     * @global object $objDatabase
     * @global object $objJs
     * @return true
     */
    public function showCustomerSettings()
    {
        global $_CORELANG, $_ARRAYLANG, $objDatabase ,$objJs;
        $this->_objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_customers.html');
        $this->_objTpl->setGlobalVariable('MODULE_NAME', $this->moduleName);

        if (isset($_POST['customer_type_submit']))
            $this->saveCustomerTypes();

        $customerLabel   = isset($_POST['label']) ? contrexx_input2raw($_POST['label']) : '';
        $customerSorting = isset($_POST['sortingNumber']) ? intval($_POST['sortingNumber']) : '';
        $customerStatus  = isset($_POST['activeStatus']) || !isset ($_POST['customer_type_submit']) ? 1 : 0;
        $hrlyRate        = array();

        if (!empty($_POST['currencyName'])) {
            foreach ($_POST['currencyName'] as $currencyId) {
                $hrlyRate[$currencyId] = (isset($_POST['rateValue_'.$currencyId])) ? intval($_POST['rateValue_'.$currencyId]) : 0;
            }
        }
        $this->_objTpl->setVariable(array(
                'TXT_DISPLAY_ADD'            =>    isset($_POST['customer_type_submit']) ? "block" : 'none',
                'TXT_DISPLAY_ENTRIES'        =>    isset($_POST['customer_type_submit']) ? "none"  : 'block',
                'TXT_DISPLAY_ADD_ACTIVE'     =>    isset($_POST['customer_type_submit']) ? "active" : 'inactive',
                'TXT_DISPLAY_ENTRIES_ACTIVE' =>    isset($_POST['customer_type_submit']) ? "inactive"  : 'active',
        ));

        if (isset($_POST['customer_submit'])) {
            $count = count($_POST['form_id']);
            for ($x = 0; $x < $count; $x++) {
                $query = "UPDATE ".DBPREFIX."module_{$this->moduleName}_customer_types
                      SET      pos = '".intval($_POST['form_pos'][$x])."'
                      WHERE    id = '".intval($_POST['form_id'][$x])."'";
                $objDatabase->Execute($query);
            }

            $defaultTypeId = intval($_POST['default']);            
            $statusArr     = array();
            $x             = 0;
            foreach ($_POST['form_id'] as $id) {
                $statusArr[$id]['id']     = intval($id);
                $statusArr[$id]['status'] = ($defaultTypeId == $id) ? 1 : 0;
                $statusArr[$id]['pos']    = intval($_POST['form_pos'][$x]);                
                $x++;
            }

            // New update Query.
            $idArr = array_map('intval', $_POST['form_id']);
            $ids   = implode(',', $idArr);
            $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_customer_types SET `default` = CASE id ";

            foreach ($statusArr as $id => $val) {
                $query .= sprintf(" WHEN %d THEN %d", $id, $val['status']);
            }

            $query .= " END WHERE id IN ($ids)";

            $objDatabase->Execute($query);

            $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CHANGES_UPDATED_SUCCESSFULLY'];
        }

        if (isset($_GET['chg']) && $_GET['chg'] == 1) {
            for ($x = 0; $x < count($_POST['form_id']); $x++) {
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_customer_types
                          SET      pos = '".intval($_POST['form_pos'][$x])."'
                          WHERE    id = '".intval($_POST['form_id'][$x])."'";
                $objDatabase->Execute($query);
            }
            $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_PROJECTSTATUS_SORTING_COMPLETE'];
        }

        $sortField            = isset($_GET['sortf']) ? intval($_GET['sortf']) : 0;
        $sortOrder            = isset($_GET['sorto']) ? intval($_GET['sorto']) : 1;
        $customerFields       = array('pos', 'label','customerTypeId');
        $customerTypeOverview = array();
        $numeric              = array('pos');

        $objResult = $objDatabase->Execute('SELECT `id`,
                                                     `label`,
                                                     `pos`,
                                                     `active`,
                                                     `default`
                                                FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_types` ');

        if ($objResult->fields['id'] === null) {
            $this->_objTpl->setVariable(array(
                    'TXT_CONTAINS_NO_RECORDS'    =>    $_ARRAYLANG['TXT_CONTAINS_NO_RECORDS'],
                    'TXT_DISPLAY_SELECT_ACTION'  =>    "none"
            ));
            $this->_objTpl->parse('showEntries');
        } else {
            $row = 0;
            while (!$objResult->EOF) {
                $activeImage = ($objResult->fields['active']) ? "images/icons/led_green.gif" : "images/icons/led_red.gif";
                $activeTitle = ($objResult->fields['active']) ? $_ARRAYLANG['TXT_ACTIVE'] : $_ARRAYLANG['TXT_INACTIVE'];
                
                $customerTypeOverview[$row] = array(
                        'pos'            => $objResult->fields['pos'],
                        'active'         => $objResult->fields['active'],
                        'label'          => $objResult->fields['label'],
                        'activeTitle'    => $activeTitle,
                        'activeImage'    => $activeImage,
                        'customerTypeId' => $objResult->fields['id'],
                        'default'        => $objResult->fields['default'],
                );
                $row++;
                $objResult->MoveNext();
            }

            $sorting              = new Sorter();
            $sorting->backwards   = empty($sortOrder);
            $sorting->numeric     = (in_array($customerFields[$sortField], $numeric));
            $customerTypeOverview = $sorting->sort($customerTypeOverview, $customerFields[$sortField]);

            $row = 0;
            foreach ($customerTypeOverview as $customerTypeValues) {

                $this->_objTpl->setVariable(array(
                        'TXT_CUSTOMER_TYPE_ID'		=>  $customerTypeValues['customerTypeId'],
                        'TXT_CUSTOMER_TYPE_LABEL'	=>  contrexx_raw2xhtml($customerTypeValues['label'], ENT_QUOTES),
                        'TXT_PROJECT_ACTIVE_IMAGE'      =>  $customerTypeValues['activeImage'],
                        'TXT_PROJECT_ACTIVE_TITLE'      =>  $customerTypeValues['activeTitle'],
                        'TXT_CUSTOMER_ACTIVE'		=>  $customerTypeValues['active'],
                        'TXT_CUSTOMER_POS_SORT'		=>  $customerTypeValues['pos'],
                        'ENTRY_ROWCLASS'                =>  ($row % 2 == 0) ? 'row1' : 'row2',
                        'TXT_IMAGE_DELETE'              =>  $_ARRAYLANG['TXT_IMAGE_DELETE'],
                        'TXT_IMAGE_EDIT'                =>  $_ARRAYLANG['TXT_IMAGE_EDIT'],
                        'TXT_CUSTOMER_TYPE'             =>  $_ARRAYLANG['TXT_CUSTOMER_TYPE'],
                        'TXT_DEFAULT_STATUS_CHECKED'    =>  ($customerTypeValues['default'] == 1) ? 'checked="checked"' : '',
                ));
                $row++;
                $this->_objTpl->parse('showEntries');
            }
        }

        $this->_objTpl->setVariable('CUSTOMER_TYPES_JAVASCRIPT', $objJs->getCustomerTypeJavascript());
        $this->_objTpl->setVariable(array(
                'PM_CUSTOMER_ORDER_SORT'         =>'&sortf=0&sorto='.($sortOrder?0:1),
                'PM_CUSTOMER_LABEL_SORT'         =>'&sortf=1&sorto='.($sortOrder?0:1),
                'TXT_CUSTOMERGIVENLABEL'         => contrexx_raw2xhtml($customerLabel),
                'CRM_SORTING_NUMBER'             => (int) $customerSorting,
                'CRM_CUSTOMER_TYPE_CHECKED'      => $customerStatus ? 'checked' : '',

                'TXT_CUSTOMERS'                  => $_ARRAYLANG['TXT_CUSTOMERS'],
                'TXT_CUSTOMER_TYPES'             => $_ARRAYLANG['TXT_CUSTOMER_TYPES'],
                'TXT_LABEL'                      => $_ARRAYLANG['TXT_LABEL'],
                'TXT_TITLE_STATUS'               => $_ARRAYLANG['TXT_TITLE_STATUS'],
                'TXT_FUNCTIONS'                  => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SORTING'                    => $_ARRAYLANG['TXT_SORTING'],
                'TXT_TITLEACTIVE'                => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_PROJECTSTATUS_SAVE_SORTING' => $_ARRAYLANG['TXT_PROJECTSTATUS_SAVE_SORTING'],
                'TXT_SORTING_NUMBER'             => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_ACTIVATESELECTED'           => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED'         => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_SELECT_ACTION'              => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_DELETE_SELECTED'            => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_ADD_CUSTOMER_TYPES'         => $_ARRAYLANG['TXT_ADD_CUSTOMER_TYPES'],
                'TXT_ENTER_LABEL_FIELD'          => $_ARRAYLANG['TXT_ENTER_LABEL_FIELD'],
                'TXT_ENTER_LABEL_FIELD_WITHOUT_SPECIAL_CHARACTERS' => $_ARRAYLANG['TXT_ENTER_LABEL_FIELD_WITHOUT_SPECIAL_CHARACTERS'],
                'TXT_SELECT_ALL'                 => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'               => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_CURRENCY_RATES'             => $_ARRAYLANG['TXT_CURRENCY_RATES'],
                'TXT_DEFAULT'                    => $_ARRAYLANG['TXT_DEFAULT'],
                'TXT_GENERAL'                    => $_ARRAYLANG['TXT_GENERAL'],
                'TXT_NOTES'                      => $_ARRAYLANG['TXT_NOTES'],
                'TXT_SAVE'                       => $_ARRAYLANG['TXT_SAVE']
        ));
    }

    /**
     * store the customer type
     *
     * @global array $_CORELANG
     * @global array $_ARRAYLANG
     * @global object $objDatabase
     * @return true
     */
    public function saveCustomerTypes()
    {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];

        $success = true;

        $customerLabel      = isset($_POST['label']) ? contrexx_input2raw($_POST['label']) : '';
        $customerSorting    = isset($_POST['sortingNumber']) ? intval($_POST['sortingNumber']) : '';
        $customerStatus     = isset($_POST['activeStatus']) || !isset ($_POST['customer_type_submit']) ? 1 : 0;
        
        $searchingQuery = "SELECT label FROM `".DBPREFIX."module_{$this->moduleName}_customer_types`
                               WHERE  label = '".contrexx_raw2db($customerLabel)."'";
        $objResult = $objDatabase->Execute($searchingQuery);

        if (!$objResult->EOF) {
            $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPE_ALREADY_EXIST'];
            $success = false;
        } else {
            $insertCustomerTypes = "INSERT INTO `".DBPREFIX."module_".$this->moduleName."_customer_types`
                                        SET    `label`             = '".contrexx_input2db($customerLabel)."',
                                               `pos`               = '".intval($customerSorting)."',
                                               `active`            = '".intval($customerStatus)."'
                                               ";
            $db = $objDatabase->Execute($insertCustomerTypes);
            if ($db)
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPES_ADDED_SUCCESSFULLY'];
            else
                $success = false;
        }

        if ($success) {
            CSRF::header("location:./index.php?cmd={$this->moduleName}&act=settings&tpl=customertypes");
            exit();
        }

    }

    /**
     * Currency overview
     *
     * @global array $_CORELANG
     * @global array $_ARRAYLANG
     * @global object $objDatabase
     * @global object $objJs
     * @return true
     */
    public function currencyoverview()
    {
        global $_CORELANG, $_ARRAYLANG, $objDatabase, $objJs;
        $this->_objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_currency.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];

        if (isset ($_POST['currency_submit'])) {
            $this->addCurrency();
        }

        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));
        if (!isset($_GET['mes'])) {
            $_GET['mes']='';
        }
        switch ($_GET['mes']) {
        case 'updatecurrency' :
            $this->_strOkMessage = $_ARRAYLANG['TXT_CURRENCY_UPDATED_SUCCESSFULLY'];
            break;
        case 'changesupdate' :
            $this->_strOkMessage = $_ARRAYLANG['TXT_CHANGES_UPDATED_SUCCESSFULLY'];
            break;
        }

        if (!empty($this->_strErrMessage)) {
            $this->_objTpl->setVariable(array(
                    'TXT_DISPLAY_ADD'      =>    "block",
                    'TXT_DISPLAY_ENTRIES'  =>    "none",
                    'TXT_DESCRIPTION_VALUE' => $_SESSION['description']
            ));
            unset($_SESSION['description']);
        } else {
            $this->_objTpl->setVariable(array(
                    'TXT_DISPLAY_ADD'      =>    "none",
                    'TXT_DISPLAY_ENTRIES'  =>    "block"
            ));
        }
        //sort
        if (isset($_GET['chg']) and $_GET['chg'] == 1 ) {
            for ($x = 0; $x < count($_POST['form_id']); $x++) {
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_currency
                               SET pos = '".intval($_POST['form_pos'][$x])."'
                             WHERE id = '".intval($_POST['form_id'][$x])."'";
                $objDatabase->Execute($query);
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_PROJECTSTATUS_SORTING_COMPLETE'];
        }

        if (isset($_POST['currencyfield_submit'])) {

            for ($x = 0; $x < count($_POST['form_id']); $x++) {
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_currency
                              SET pos = '".intval($_POST['form_pos'][$x])."'
                             WHERE id = '".intval($_POST['form_id'][$x])."'";
                $objDatabase->Execute($query);
                $this->_strOkMessage = $_ARRAYLANG['TXT_PROJECTSTATUS_SORTING_COMPLETE'];
            }
        }

        $sortField = isset ($_GET['sortf']) ? intval($_GET['sortf']) : 0;
        $sortOrder = isset ($_GET['sorto']) ? intval($_GET['sorto']) : 1;
        $customerFields      = array('pos','name','id','active');
        $currencyeOverview   = array();
        $numeric             = array('pos');
        $key                 = 0;

        $objData = $objDatabase->Execute('SELECT id, name, active, pos FROM '.DBPREFIX.'module_'.$this->moduleName.'_currency');

        $row = "row2";
        if ($objData->fields['id'] == null) {
            $this->_objTpl->setVariable(array(
                    'TXT_CONTAINS_NO_RECORDS'    =>    $_ARRAYLANG['TXT_CONTAINS_NO_RECORDS'],
                    'TXT_DISPLAY_SELECT_ACTION'  =>    "none"
            ));
            $this->_objTpl->parse('showNoEntries');
        } else {

            while (!$objData->EOF) {

                $currencyeOverview[$key] = array(
                        'pos'                      => $objData->fields['pos'],
                        'name'                     => trim($objData->fields['name']),
                        'id'                       => $objData->fields['id'],
                        'active'                   => $objData->fields['active']);

                $key++;
                $objData->MoveNext();
            }
            $sorting               = new Sorter();
            $sorting->backwards    = empty($sortOrder);
            $sorting->numeric      = (in_array($customerFields[$sortField], $numeric));
            $currencyeOverview     = $sorting->sort($currencyeOverview, $customerFields[$sortField], $customerFields[2]);

            foreach ($currencyeOverview as $key => $currency) {
                $activeImage = $currencyeOverview[$key]['active'] ? "images/icons/led_green.gif" : "images/icons/led_red.gif";
                $activeTitle = $currencyeOverview[$key]['active'] ? $_ARRAYLANG['TXT_ACTIVE']    : $_ARRAYLANG['TXT_INACTIVE'];
                
                $this->_objTpl->setVariable(array(
                        'TXT_CURRENCY_NAME'            => contrexx_raw2xhtml($currency['name']),
                        'TXT_CURRENCY_ID'              => $currency['id'],
                        'TXT_CURRENCY_ACTIVE_IMAGE'    => $activeImage,
                        'TXT_CURRENCY_ACTIVE_TITLE'    => $activeTitle,
                        'TXT_CURRENCY_POS'             => $currency['pos'],
                        'TXT_CURRENCY_ACTIVE'          => $currency['active'],
                        'TXT_IMAGE_EDIT'               => $_ARRAYLANG['TXT_EDIT'],
                        'TXT_IMAGE_DELETE'             => $_ARRAYLANG['TXT_DELETE'],
                        'ENTRY_ROWCLASS'               => $row = ($row == "row1") ? "row2" : "row1"

                ));
                $this->_objTpl->parse('currency_entries');
                $objData->MoveNext();
            }
        }

        // Hourly rate
        $hrlyRate  = array();
        $objResult = $objDatabase->Execute('SELECT id,label FROM  '.DBPREFIX.'module_'.$this->moduleName.'_customer_types WHERE  active!="0" ORDER BY pos,label');
        while(!$objResult->EOF) {
            $this->_objTpl->setVariable(array(
                'CRM_CUSTOMER_TYPE'     => contrexx_raw2xhtml($objResult->fields['label']),
                'CRM_CUSTOMERTYPE_ID'   => (int) $objResult->fields['id'],
                'PM_CURRENCY_HOURLY_RATE'   => !empty($hrlyRate[$objResult->fields['id']]) ? intval($hrlyRate[$objResult->fields['id']]) : 0,
            ));
            $this->_objTpl->parse("hourlyRate");
            $objResult->MoveNext();
        }
        
        $this->_objTpl->setVariable(array(
                'PM_CURRENCY_ORDER_SORT'         => '&sortf=0&sorto='.($sortOrder?0:1),
                'PM_CURRENCY_NAME_SORT'          => '&sortf=1&sorto='.($sortOrder?0:1),
                'TXT_CURRENCY'                   => $_ARRAYLANG['TXT_CURRENCY'],
                'TXT_ADD_CURRENCY'               => $_ARRAYLANG['TXT_ADD_CURRENCY'],
                'TXT_TITLE_STATUS'               => $_ARRAYLANG['TXT_TITLE_STATUS'],
                'TXT_NAME'                       => $_ARRAYLANG['TXT_NAME'],
                'TXT_SAVE'                       => $_ARRAYLANG['TXT_SAVE'],
                'TXT_NOTES'                     => $_ARRAYLANG['TXT_NOTES'],
                'TXT_SORTING'                    => $_ARRAYLANG['TXT_SORTING'],
                'TXT_SORTING_NUMBER'             => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_ACTIVATESELECTED'           => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED'         => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_TITLEACTIVE'                => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_FUNCTIONS'                  => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ALL'                 => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_PROJECTSTATUS_SAVE_SORTING' => $_ARRAYLANG['TXT_PROJECTSTATUS_SAVE_SORTING'],
                'TXT_DESELECT_ALL'               => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_SELECT_ACTION'              => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_DELETE_SELECTED'            => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_CUSTOMER_TYPES'            => $_ARRAYLANG['TXT_CUSTOMER_TYPES'],
                'TXT_GENERAL'                    => $_ARRAYLANG['TXT_GENERAL'],
                'TXT_CURRENCY_RATES'              => $_ARRAYLANG['TXT_CURRENCY_RATES'],
                'TXT_HOURLY_RATE'                 => $_ARRAYLANG['TXT_HOURLY_RATE'],
                'PM_SETTINGS_CURRENCY_JAVASCRIPT' => $objJs->getAddCurrencyJavascript(),
        ));
    }

    /**
     * store currency
     *
     * @global array $_CORELANG
     * @global array $_ARRAYLANG
     * @global object $objDatabase
     * @global object $objJs
     * @return true
     */
    public function addCurrency()
    {
        global $_CORELANG, $_ARRAYLANG, $objDatabase,$objJs;

        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];

        if ($_POST['currency_submit']) {

            $settings = array('name' => $_POST['name'],'sortingNumber' => $_POST['sortingNumber']);

            $nameValue = $settings['name'];
            $SortingNum = $settings['sortingNumber'];
            $searchingQuery = 'SELECT name from `'.DBPREFIX.'module_'.$this->moduleName.'_currency` WHERE name = '."'$nameValue'";
            $objResult = $objDatabase->Execute($searchingQuery);

            $hrlyRate           = array();
            if (!empty($_POST['customerType'])) {
                foreach ($_POST['customerType'] as $customerTypeId) {
                    $hrlyRate[$customerTypeId] = (isset($_POST['rateValue_'.$customerTypeId])) ? intval($_POST['rateValue_'.$customerTypeId]) : 0;
                }
            }
        
            if (!$objResult->EOF) {
                $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_CURRENCY_ALREADY_EXISTS'];
                return;
            } else {
                $activeValue = isset($_POST['activeStatus']) ? 1 : 0;

                $insertQuery = "INSERT INTO ".DBPREFIX."module_{$this->moduleName}_currency
                                    SET name    = '{$settings['name']}',
                                        pos     = '{$settings['sortingNumber']}',
                                        active  = '$activeValue',
                                        `hourly_rate`       = '".json_encode($hrlyRate)."'";

                $objDatabase->Execute($insertQuery);
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CURRENCY_ADDED_SUCCESSFULLY'];
            }
        }
        $this->_objTpl->setVariable(array(
                'TXT_CURRENCY'                    => $_ARRAYLANG['TXT_CURRENCY'],
                'TXT_ADD_CURRENCY'                => $_ARRAYLANG['TXT_ADD_CURRENCY'],
                'TXT_NAME'                        => $_ARRAYLANG['TXT_NAME'],
                'TXT_SORTING_NUMBER'              => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_FUNCTIONS'                   => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ALL'                  => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'                => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_SELECT_ACTION'               => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_DELETE_SELECTED'             => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'PM_SETTINGS_CURRENCY_JAVASCRIPT' => $objJs->getAddCurrencyJavascript(),
        ));
        CSRF::header('location:./index.php?cmd=crm&act=settings&tpl=currency');
        exit();
    }

    /**
     * task type overview
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     * @return true
     */
    public function taskTypesoverview()
    {
        global $objDatabase,$_ARRAYLANG;

        $objTpl = $this->_objTpl;
        $objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_task_types.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName,
                'TXT_IMAGE_EDIT' => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                'TXT_IMAGE_DELETE' => $_ARRAYLANG['TXT_IMAGE_DELETE'],
        ));
        JS::activate("jquery");

        // Activate validation scripts
        JS::registerCSS("lib/javascript/validationEngine/css/validationEngine.jquery.css");
        JS::registerJS("lib/javascript/validationEngine/js/languages/jquery.validationEngine-en.js");
        JS::registerJS("lib/javascript/validationEngine/js/jquery.validationEngine.js");
        JS::registerCSS("lib/javascript/chosen/chosen.css");
        JS::registerJS("lib/javascript/chosen/chosen.jquery.js");

        $msg = base64_decode($_REQUEST['msg']);
        switch ($msg) {
        case 'taskUpdated':
            $_SESSION['strOkMessage'] = $_ARRAYLANG['CRM_TASK_TYPE_UPDATED_SUCCESSFULLY'];
            break;
        default:
            break;
        }

        $action          = (isset ($_REQUEST['actionType'])) ? $_REQUEST['actionType'] : '';
        $tasktypeIds     = (isset($_REQUEST['taskTypeId'])) ? array_map('intval', $_REQUEST['taskTypeId']) : 0;
        $tasktypeSorting = (isset($_REQUEST['sorting'])) ? array_map('intval', $_REQUEST['sorting']) : 0;
        $ajax            = isset($_REQUEST['ajax']);

        switch ($action) {
        case 'changestatus':
            $this->activateTaskType((int) $_GET['taskTypeId']);
            if ($ajax) exit();
        case 'activate':
            $this->activateTaskTypes($tasktypeIds);
            break;
        case 'deactivate':
            $this->activateTaskTypes($tasktypeIds, true);
            break;
        case 'delete':
            $this->deleteTaskTypes($tasktypeIds);
            break;
        case 'deletecatalog':
            $this->deleteTaskType((int) $_GET['taskTypeId']);
            if ($ajax) exit();
            break;
        default:
            break;
        }
        if (!empty ($action)) {
            $this->saveSortingTaskType($tasktypeSorting);
            if ($action == 'savesorting' || $action == 'Save')
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_PROJECTSTATUS_SORTING_COMPLETE'];
        }

        if ($_POST['saveTaskType']) {
            $this->saveTaskTypes();
            $_SESSION['strOkMessage'] = $_ARRAYLANG['CRM_TASK_TYPE_ADDED_SUCCESSFULLY'];
        }

        $this->getModifyTaskTypes();

        $this->showTaskTypes();

        $objTpl->setVariable(array(
                'TXT_CRM_TASK_TYPES'        => $_ARRAYLANG['TXT_CRM_TASK_TYPES'],
                'TXT_CRM_ADD_TASK_TYPE'     => $_ARRAYLANG['TXT_CRM_ADD_TASK_TYPE'],
                'TXT_CRM_TASK_TYPE_STATUS'  => $_ARRAYLANG['TXT_CRM_TASK_TYPE_STATUS'],
                'TXT_CRM_FUNCTIONS'         => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_CRM_NO_TASKTYPES'      => $_ARRAYLANG['TXT_CRM_NO_TASKTYPES'],
                'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
                'TXT_SELECT_ALL'           => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'         => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_SELECT_ACTION'        => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_DELETE_SELECTED'      => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_ACTIVATE_SELECTED'    => $_ARRAYLANG['TXT_ACTIVATE_SELECTED'],
                'TXT_DEACTIVATE_SELECTED'  => $_ARRAYLANG['TXT_DEACTIVATE_SELECTED'],
                'TXT_SAVE_SORTING'         => $_ARRAYLANG['TXT_SAVE_SORTING'],
                'TXT_SELECT_ENTRIES'       => $_ARRAYLANG['TXT_NO_OPERATION'],
                'TXT_STATUS_SUCCESSFULLY_CHANGED' => $_ARRAYLANG['TXT_TASK_TYPE_STATUS_CHANGED_SUCCESSFULLY'],
        ));
    }

    /**
     * change status of task type
     *
     * @param array $tasktypeIds asd
     * @param bool  $deactivate  dedd
     * 
     * @global  object   $objDatabase
     * @global  array    $_ARRAYLANG
     * @return true
     */
    public function activateTaskTypes($tasktypeIds, $deactivate = false)
    {
        global $objDatabase,$_ARRAYLANG;

        if (!empty($tasktypeIds) && is_array($tasktypeIds)) {

            $ids = implode(',', $tasktypeIds);
            $setValue = $deactivate ? 0 : 1;
            $query = "UPDATE `".DBPREFIX."module_".$this->moduleName."_task_types` SET `status` = CASE id ";
            foreach ($tasktypeIds as $count => $idValue) {
                $query .= sprintf("WHEN %d THEN $setValue ", $idValue);
            }
            $query .= "END WHERE id IN ($ids)";
            $objResult = $objDatabase->Execute($query);

            $this->_strOkMessage = $_ARRAYLANG['TXT_TASK_TYPE_STATUS_CHANGED_SUCCESSFULLY'];

        }
    }

    /**
     * activate the task type
     *
     * @param integer $tasktypeId id
     * 
     * @global object $objDatabase
     * @return true
     */
    public function activateTaskType($tasktypeId)
    {
        global $objDatabase;

        if (!$tasktypeId)
            return;

        $query = $objDatabase->Execute("UPDATE `".DBPREFIX."module_".$this->moduleName."_task_types` SET `status` = IF(status = 1, 0, 1) WHERE id = $tasktypeId");

    }

    /**
     * save sorting
     *
     * @param array $tasktypeSorting id array
     *
     * @global <type> $objDatabase
     * @global <type> $_ARRAYLANG
     * @return true
     */
    public function saveSortingTaskType($tasktypeSorting)
    {
        global $objDatabase,$_ARRAYLANG;

        if (!empty($tasktypeSorting) && is_array($tasktypeSorting)) {

            $ids = implode(',', array_keys($tasktypeSorting));

            $query = "UPDATE `".DBPREFIX."module_".$this->moduleName."_task_types` SET `sorting` = CASE id ";
            foreach ($tasktypeSorting as $idValue => $value ) {
                $query .= sprintf("WHEN %d THEN %d ", $idValue, $value);
            }
            $query .= "END WHERE id IN ($ids)";
            $objResult = $objDatabase->Execute($query);
        }
    }

    /**
     * delete task type
     *
     * @param array $tasktypeIds id
     * 
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     * @return true
     */
    public function deleteTaskTypes($tasktypeIds)
    {
        global $objDatabase,$_ARRAYLANG;

        if (!empty($tasktypeIds) && is_array($tasktypeIds)) {

            $ids = implode(',', $tasktypeIds);

            $query = "DELETE FROM `".DBPREFIX."module_".$this->moduleName."_task_types` WHERE id IN ($ids)";
            $objResult = $objDatabase->Execute($query);

            $this->_strOkMessage = $_ARRAYLANG['TXT_TASK_TYPE_DELETED_SUCCESSFULLY'];
        }
    }

    /**    
     * delete task type
     * 
     * @param integer $tasktypeId Id of tasktype
     *
     * @global object $objDatabase
     * @global array $_ARRAYLANG     
     * @return true
     */

    public function deleteTaskType($tasktypeId)
    {
        global $objDatabase,$_ARRAYLANG;

        $query = "DELETE FROM `".DBPREFIX."module_".$this->moduleName."_task_types` WHERE id = $tasktypeId";
        $objResult = $objDatabase->Execute($query);
        echo $_ARRAYLANG['TXT_TASK_TYPE_DELETED_SUCCESSFULLY'];
    }

    /**
     * settings general
     * 
     * @global <type> $objDatabase
     * @global <type> $_ARRAYLANG
     * @return true
     */
    public function showGeneralSettings()
    {
        global $objDatabase,$_ARRAYLANG;
        $this->_objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_general.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $objTpl = $this->_objTpl;
        
        if (isset($_POST['save'])) {
                                    
            $settings = array(
                    'allow_pm'                           => (!$this->isPmInstalled ? 0 : (isset($_POST['allowPm']) ? 1 : 0)),
                    'create_user_account'                => isset($_POST['create_user_account']) ? 1 : 0,
                    'customer_default_language_backend'  => isset($_POST['default_language_backend']) ? (int) $_POST['default_language_backend'] : 0,
                    'customer_default_language_frontend' => isset($_POST['default_language_frontend']) ? (int) $_POST['default_language_frontend'] : 0,
                    'default_user_group'                 => isset($_POST['default_user_group']) ? (int) $_POST['default_user_group'] : 0,
                    'emp_default_user_group'             => isset($_POST['emp_default_user_group']) ? (int) $_POST['emp_default_user_group'] : 0,
            );

            foreach ($settings as $settings_var => $settings_val) {
                $updateAllowPm = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_settings
                                          SET `setvalue` = "'.contrexx_input2db($settings_val).'"
                                    WHERE setname = "'.$settings_var.'"';
                $objDatabase->Execute($updateAllowPm);
            }

            $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CHANGES_UPDATED_SUCCESSFULLY'];
        }

        $settings = $this->getSettings();

        $objLanguages = $objDatabase->Execute("SELECT `id`, `name`, `frontend`, `backend` FROM ".DBPREFIX."languages WHERE frontend = 1 OR backend =1");

        if ($objLanguages) {
            $objTpl->setVariable(array(
                    'CRM_LANG_NAME'     => $_ARRAYLANG['TXT_CRM_STANDARD'],
                    'CRM_LANG_VALUE'    => 0,
                    'CRM_LANG_SELECTED' => $settings['customer_default_language_frontend'] == 0 ? "selected='selected'" : ''
            ));
            $objTpl->parse("langFrontend");
            $objTpl->setVariable(array(
                    'CRM_LANG_NAME'  => $_ARRAYLANG['TXT_CRM_STANDARD'],
                    'CRM_LANG_VALUE' => 0,
                    'CRM_LANG_SELECTED' => $settings['customer_default_language_backend'] == 0 ? "selected='selected'" : ''
            ));
            $objTpl->parse("langBackend");
            while (!$objLanguages->EOF) {

                if ($objLanguages->fields['frontend']) {
                    $objTpl->setVariable(array(
                            'CRM_LANG_NAME'     => contrexx_raw2xhtml($objLanguages->fields['name']),
                            'CRM_LANG_VALUE'    => (int) $objLanguages->fields['id'],
                            'CRM_LANG_SELECTED' => $settings['customer_default_language_frontend'] == $objLanguages->fields['id'] ? "selected='selected'" : ''
                    ));
                    $objTpl->parse("langFrontend");
                }

                if ($objLanguages->fields['backend']) {
                    $objTpl->setVariable(array(
                            'CRM_LANG_NAME'     => contrexx_raw2xhtml($objLanguages->fields['name']),
                            'CRM_LANG_VALUE'    => (int) $objLanguages->fields['id'],
                            'CRM_LANG_SELECTED' => $settings['customer_default_language_backend'] == $objLanguages->fields['id'] ? "selected='selected'" : ''
                    ));
                    $objTpl->parse("langBackend");
                }

                $objLanguages->MoveNext();
            }
        }

        $objUserGroup = $objDatabase->Execute("SELECT `group_id`, `group_name` FROM ".DBPREFIX."access_user_groups WHERE is_active = 1");
        if ($objUserGroup) {
            while (!$objUserGroup->EOF) {
                $objTpl->setVariable(array(
                        'CRM_GROUP_NAME'            => contrexx_raw2xhtml($objUserGroup->fields['group_name']),
                        'CRM_GROUP_VALUE'           => (int) $objUserGroup->fields['group_id'],
                        'CRM_USER_GROUP_SELECTED'   => $settings['default_user_group'] == $objUserGroup->fields['group_id'] ? "selected='selected'" : ''
                ));
                $objTpl->parse("userGroup");
                $objTpl->setVariable(array(
                        'CRM_GROUP_NAME'            => contrexx_raw2xhtml($objUserGroup->fields['group_name']),
                        'CRM_GROUP_VALUE'           => (int) $objUserGroup->fields['group_id'],
                        'CRM_USER_GROUP_SELECTED'   => $settings['emp_default_user_group'] == $objUserGroup->fields['group_id'] ? "selected='selected'" : ''
                ));
                $objTpl->parse("empUserGroup");
                $objUserGroup->MoveNext();
            }
        }

        $objTpl->setVariable(array(
            'CRM_ALLOW_PM'                   => ($settings['allow_pm']) ? "checked='checked'" : '',
            'CRM_CREATE_ACCOUNT_USER'        => ($settings['create_user_account']) ? "checked='checked'" : '',
        ));
        
        $objTpl->setVariable(array(                
                'TXT_CRM_ALLOW_PM'               => $_ARRAYLANG["TXT_CRM_ALLOW_PM"],
                'TXT_CRM_CUSTOMERS'              => $_ARRAYLANG['TXT_CUSTOMERS'],
                'TXT_CRM_LANGUAGE'               => $_ARRAYLANG['TXT_TITLE_LANGUAGE'],
                'TXT_CRM_BACKEND'                => $_ARRAYLANG['TXT_CRM_BACKEND'],
                'TXT_CRM_FRONTEND'               => $_ARRAYLANG['TXT_CRM_FRONTEND'],
                'TXT_CRM_ALLOW_PM_EXPLANATION'   => $_ARRAYLANG["TXT_CRM_ALLOW_PM_EXPLANATION"],
                'TXT_SAVE'                       => $_ARRAYLANG['TXT_SAVE'],
                'TXT_CRM_DEFAULT_LANGUAGE'       => $_ARRAYLANG['TXT_CRM_DEFAULT_LANGUAGE'],
                'TXT_CRM_DEFAULT_USER_GROUP'     => $_ARRAYLANG['TXT_CRM_DEFAULT_USER_GROUP'],
                'TXT_CRM_CREATE_ACCOUNT_USER'    => $_ARRAYLANG['TXT_CRM_CREATE_ACCOUNT_USER'],
                'TXT_CRM_CREATE_ACCOUNT_USER_TIP'=> $_ARRAYLANG['TXT_CRM_CREATE_ACCOUNT_USER_TIP'],

                'MODULE_NAME'                    => $this->moduleName,
                'TXT_NOTES'                      => $_ARRAYLANG['TXT_NOTES'],
                'TXT_GENERAL'                    => $_ARRAYLANG['TXT_GENERAL'],
                'TXT_CURRENCY'                   => $_ARRAYLANG['TXT_CURRENCY'],
                'TXT_CUSTOMER_TYPES'             => $_ARRAYLANG['TXT_CUSTOMER_TYPES'],
                'TXT_CRM_EMPLOYEE'               => $_ARRAYLANG['TXT_CRM_EMPLOYEE'],
                'TXT_CRM_EMP_DEFAULT_USER_GROUP' => $_ARRAYLANG['TXT_CRM_EMP_DEFAULT_USER_GROUP']
        ));
        
        if (!$this->isPmInstalled)
            $objTpl->hideBlock('allowPmModule');
    }
}
