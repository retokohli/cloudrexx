<?php
/**
 * Admin Class CRM
 *
 * Crm class
 *
 * @copyright	CONTREXX CMS
 * @author	SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @module	CRM
 * @modulegroup	modules
 * @access	public
 * @version	1.0.0
 */
//DBG::activate();
require_once ASCMS_MODULE_PATH   . '/crm/lib/crmLib.class.php';

require_once CRM_MODULE_LIB_PATH . '/javascript.class.php';
require_once CRM_MODULE_LIB_PATH . '/sort.class.php';
require_once CRM_MODULE_LIB_PATH . '/CSVimport.class.php';
require_once CRM_MODULE_LIB_PATH . '/Csv_bv.class.php';

class CRM extends CrmLibrary {
    /**
     * Template object
     *
     * @access private
     * @var object
     */
    var $_objTpl;

    /**
     * Page title
     *
     * @access private
     * @var string
     */
    var $_pageTitle;

    /**
     * CSV Import class
     * @var CSVimport
     */
    private $objCSVimport;

    /**
     *  class Javascript;
     * @var js
     */
    var $objJs = '';

    private $act = '';

    /**
     * Constructor
     */
    function Crm() {
        $this->__construct();
    }

    /**
     * PHP5 constructor
     *
     * @global object $objTemplate
     * @global array $_ARRAYLANG
     */
    function __construct() {

        global $objTemplate, $_ARRAYLANG, $objJs;
        parent::__construct();
        $objJs = new Javascript();       
        $this->objCSVimport = new CSVimport();        
        // $objJs = new Javascript();
        date_default_timezone_set('Europe/Zurich');
        $this->_objTpl = new \Cx\Core\Html\Sigma(ASCMS_MODULE_PATH.'/'.$this->moduleName.'/template');
        CSRF::add_placeholder($this->_objTpl);
        
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->act = $_REQUEST['act'];

        $contentNavigation = '';

        if (Permission::checkAccess($this->customerAccessId, 'static', true)) {
            $contentNavigation .= "<a href='index.php?cmd={$this->moduleName}&act=customers' class='".($this->act == 'customers' ? 'active' : '')."'  title='".$_ARRAYLANG['TXT_CUSTOMERS']."'>{$_ARRAYLANG
                ['TXT_CUSTOMERS']}</a>";
        }

        if (Permission::checkAccess($this->staffAccessId, 'static', true)) {
            $contentNavigation .= "<a href='index.php?cmd={$this->moduleName}&act=membership' class='".($this->act == 'membership' ? 'active' : '')."' title='{$_ARRAYLANG['TXT_CRM_CUSTOMER_MEMBERSHIP']}'>{$_ARRAYLANG
                ['TXT_CRM_CUSTOMER_MEMBERSHIP']}</a>
             <a href='index.php?cmd={$this->moduleName}&act=task' class='".($this->act == 'task' ? 'active' : '')."' title='{$_ARRAYLANG['TXT_TASKS']}'>{$_ARRAYLANG
                ['TXT_TASKS']}</a>
             <a href='index.php?cmd={$this->moduleName}&act=deals' class='".($this->act == 'deals' ? 'active' : '')."' title='{$_ARRAYLANG['TXT_OPPORTUNITY']}'>{$_ARRAYLANG
                ['TXT_OPPORTUNITY']}</a>
             <a href='index.php?cmd={$this->moduleName}&act=interface' class='".($this->act == 'interface' ? 'active' : '')."' title='{$_ARRAYLANG['TXT_INTERFACE']}'>
             {$_ARRAYLANG['TXT_INTERFACE']}</a>";
        }

        if (Permission::checkAccess($this->adminAccessId, 'static', true)) {
            $contentNavigation .= "<a href='index.php?cmd=".$this->moduleName."&act=settings' class='".($this->act == 'settings' ? 'active' : '')."' title='".$_ARRAYLANG['TXT_SETTINGS']."'>".
                $_ARRAYLANG['TXT_SETTINGS']."</a>";
        }

        $objTemplate->setVariable("CONTENT_NAVIGATION", $contentNavigation);

        $dispatcher = EventDispatcher::getInstance();
        $default_handler = new DefaultEventHandler();
        $dispatcher->addHandler(CRM_EVENT_ON_USER_ACCOUNT_CREATED, $default_handler);
    }

    /**
     * Set the backend page
     *
     * @access public
     * @global object $objTemplate
     * @global array $_ARRAYLANG
     */
    function getPage() {

        global $objTemplate, $_ARRAYLANG;

        if (!isset($_GET['act'])) {
            $_GET['act']='';
        }
        
        switch ($_GET['act']) {
            case 'customersearch':
                $this->getCustomerSearch();
                break;
            case 'checkuseravailablity':
                $this->checkUserAvailablity();
                break;
            case 'uploadProfilePhoto':
                $this->uploadProfilePhoto();
                break;
            case 'updateProfileImage':
                $this->updateProfilePhoto();
                break;
            case 'addcontact':
                $this->addContact();
                break;
            case 'getcustomers':
                $this->getCustomers();
                break;
            case 'autosuggest':
                $this->autoSuggest();
                break;
            case 'getdomains':
                $this->getCustomerDomains();
                break;
            case 'deals':
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->dealsOverview();
                break;
            case 'getcontacttasks':
                $this->getContactTasks();
                break;
            case 'getcontactprojects':
                $this->getcontactprojects();
                break;
            case 'getcontactdeals':
                $this->getContactDeals();
                break;
            case 'deleteContacts':                
                $this->deleteContacts();
                break;
            case 'getlinkcontacts':
                $this->getLinkContacts();
                break;
            case 'customertooltipdetail':
                $this->customerTooltipDetail();
                break;
            case 'notesdetail':
                $this->notesDetail();
                break;
            case 'changecontactstatus':
                $this->changeCustomerContactStatus();
                break;            
            case 'exportvcf':
                $this->exportVcf();
                break;
            case 'changecustomerstatus':
                $this->changeCustomerStatus();
                break;
            case 'showcustdetail' :
                $this->checkCustomerIdentity();
                $this->showCustomerDetail();
                break;
            case 'deleteCustomers':
                $this->deleteCustomers();
                break;
            case 'customersChangeStatus':
                $this->customersChangeStatus();
                break;
            case 'customerTypeChangeStatus':
                $this->customerTypeChangeStatus();
                break;
            case 'settings':
                Permission::checkAccess($this->adminAccessId, 'static');
                $this->settingsSubmenu();                
                break;
            case 'managecontact':
                $this->_modifyContact();
                break;
            case 'editTaskType':
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->editTaskType();
                break;
            case 'membership':
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->showMembership();
                break;
            case 'editnotestype':
                $this->editnotes();
                break;
            case 'deleteCurrency':
                $this->deleteCurrency();
                break;
            case 'editcurrency':
                $this->editCurrency();
                break;
            case 'currencyChangeStatus':
                $this->currencyChangeStatus();
                break;
            case 'noteschangestatus':
                $this->notesChangeStatus();
                break;
            case 'deleteCustomerTypes':
                $this->deleteCustomerTypes();
                break;
            case 'editCustomerTypes':
                $this->editCustomerTypes();
                break;            
            case 'deleteCustomerServiceplan':
                $this->deleteCustomerServiceplan();
                break;
            case  'deleteCustomerSupportcase':
                $this->deleteCustomerSupportcase();
                break;
            case 'deleteCustomerHosting':
                $this->deleteCustomerHosting();
                break;
            case 'interface' :
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->showInterface();
                break;
            case 'export':
                $this->csvExport();
                break;
            case 'importcsv' :
                $this->ImportCSV();
                break;
            case 'finalImport' :
                $this->finalImport();
                break;
            case 'InsertCSV' :
                $this->InsertCSV();
                break;
            case 'task':
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->showTasks();
                break;
            case 'addTask':
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->_modifyTask();
                break;
            case 'deleteTask':
                Permission::checkAccess($this->staffAccessId, 'static');
                $this->deleteTask();
                break;            
            case 'customers':
            default:
                if (Permission::checkAccess($this->staffAccessId, 'static', true)) {
                    $this->showCustomers();
                } else {
                    $this->checkCustomerIdentity();
                    Permission::noAccess();
                }
                break;
        }
        
        $objTemplate->setVariable(array(
                'CONTENT_TITLE'             => isset($_SESSION['pageTitle']) ? $_SESSION['pageTitle'] : $this->_pageTitle,
                'CONTENT_OK_MESSAGE'        => isset($_SESSION['strOkMessage']) ? $_SESSION['strOkMessage'] : $this->_strOkMessage,
                'CONTENT_STATUS_MESSAGE'    => isset($_SESSION['strErrMessage']) ? $_SESSION['strErrMessage'] : $this->_strErrMessage,
                'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
        unset($_SESSION['pageTitle']);
        unset($_SESSION['strOkMessage']);
        unset($_SESSION['strErrMessage']);
    }

    public function checkCustomerIdentity($return = false) {

        $customer_id = $this->getCustomerId();
        if ($customer_id) {
            if ((isset($_GET['id']) && ($_GET['id'] == $customer_id)) || (isset($_GET['id']) && in_array($_GET['id'], $this->getCustomerContacts($customer_id)))) {
                return true;
            } else {
                if ($return) {
                    return false;
                }
                CSRF::header("Location:./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$customer_id}");
                exit();
            }
        }

        return true;
    }

    public function getCustomerId() {
        global $objDatabase;

        $objFWUser  = FWUser::getFWUserObject();
        $userid     = $objFWUser->objUser->getId();

        $objResult = $objDatabase->selectLimit("SELECT `id` FROM `".DBPREFIX."module_{$this->moduleName}_contacts` WHERE `user_account` = {$userid}", 1);
        if ($objResult && $objResult->RecordCount()) {
            return (int) $objResult->fields['id'];
        }

        return false;
    }

    public function getCustomerContacts($customerId)
    {
        global $objDatabase;

        $contacts = array();
        if ($customerId) {
            $query = "SELECT `id` FROM `".DBPREFIX."module_{$this->moduleName}_contacts` WHERE `contact_customer` = {$customerId}";
            $objResult = $objDatabase->Execute($query);

            if ($objResult) {
                while (!$objResult->EOF) {
                    array_push($contacts, $objResult->fields['id']);
                    $objResult->MoveNext();
                }
            }
        }

        return $contacts;        
    }
    
    function notesDetail() {
        global $_ARRAYLANG, $objDatabase, $wysiwygEditor, $FCKeditorBasePath ,$objJs;
        
        JS::activate("cx");
        
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_customer_notes_history.html');
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));

        $custId = (isset($_REQUEST['id']))? (int) trim($_REQUEST['id']):0;
        $design = (isset($_REQUEST['design']))? contrexx_input2raw($_REQUEST['design']):'';
        $noteId = (isset($_GET['nid']))? (int) $_GET['nid'] : 0;  //Requset from pm module

        $intPerpage = 50;
        $intPage    = (isset($_GET['page']) ? (int) $_GET['page'] : 0) * $intPerpage;

        $functionHead = "<td nowrap='nowrap' width='10%' style='text-align: right;font-weight: bold;padding-right: 5px;font-weight:bold;'>";
        $functionEnd  = "</td>";
        $this->_objTpl->setVariable(array(
                'TXT_FUNCTION_HEAD'        => ($design != 'pm') ? $functionHead : '',
                'TXT_FUNCTIONS_END'        => ($design != 'pm') ? $functionEnd : '',
        ));

        if (!empty($noteId)) {
            $filter_note_id = " AND comment.notes_type_id = ".$noteId;
        }

        if(!empty($custId)) {
            $objComment = $objDatabase->Execute("SELECT comment.id,
                                                        customer_id,
                                                        notes_type_id,
                                                        comment,
                                                        added_date,
                                                        date,
                                                        notes.name AS notes
                                                    FROM ".DBPREFIX."module_".$this->moduleName."_customer_comment AS comment
                                                    LEFT JOIN ".DBPREFIX."module_".$this->moduleName."_notes AS notes
                                                       ON comment.notes_type_id = notes.id
                                                    WHERE customer_id = '$custId' $filter_note_id LIMIT $intPage, $intPerpage");
            if ($objComment->RecordCount() == 0 && $_GET['ajax'] == true) {
                echo '0';
                exit();
            }

            if ($objComment->RecordCount() == 0) {
                $this->_objTpl->hideBlock('showComment');
                $this->_objTpl->touchBlock('noNotesEntries');
            } else {
                $this->_objTpl->touchBlock('showComment');
                $this->_objTpl->hideBlock('noNotesEntries');
            }

            $row = 'row2';
            while(!$objComment->EOF) {
                $this->_objTpl->setVariable(array(
                        'TXT_COMMENT_ID'            => (int) $objComment->fields['id'],
                        'TXT_COMMENT_CUSTOMER_ID'   => (int) $objComment->fields['customer_id'],
                        'TXT_COMMENT_ADDEDDATE'     => date('Y-m-d', strtotime($objComment->fields['added_date'])),
                        'CRM_COMMENT_DATE'          => contrexx_raw2xhtml($objComment->fields['date']),
                        'CRM_NOTES_TYPE'            => contrexx_raw2xhtml($objComment->fields['notes']),
                        'CRM_NOTES_TYPE_ID'         => intval($objComment->fields['notes_type_id']),
                        'TXT_COMMENT_DESCRIPTION'   => html_entity_decode($objComment->fields['comment'], ENT_QUOTES, CONTREXX_CHARSET),
                        'TXT_IMAGE_EDIT'            => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                        'TXT_IMAGE_DELETE'          => $_ARRAYLANG['TXT_IMAGE_DELETE'],
                        'ENTRY_ROWCLASS'            => $row = ($row == 'row1') ? 'row2' : 'row1',
                        'TXT_CUST_ID'               => $custId,
                        'TXT_DISPLAY'               => ($design != 'pm') ? 'display: block' : 'display: none',                        
                ));
                $this->_objTpl->parse('showComment');
                $objComment->MoveNext();
            }
        }
        $this->_objTpl->setGlobalVariable(array(
                'CRM_CUST_ID'              => $custId,
                'CSRF_PARAM'               => CSRF::param(),
                'CRM_NO_RECORDS_FOUND'     => $_ARRAYLANG['CRM_NO_RECORDS_FOUND'],
                'TXT_CRM_NOTES_TYPE'       => $_ARRAYLANG['TXT_NOTE_TYPE'],
                'TXT_SHOW_COMMENT_HISTORY' => $_ARRAYLANG['TXT_SHOW_COMMENT_HISTORY'],
                'TXT_COMMENT_TITLE'        => $_ARRAYLANG['TXT_COMMENT_TITLE'],
                'TXT_COMMENT_DATE_TIME'    => $_ARRAYLANG['TXT_COMMENT_DATE_TIME'],
                'TXT_TASK_FUNCTIONS'       => ($design != 'pm') ? $_ARRAYLANG['TXT_TASK_FUNCTIONS'] : '',
                'TXT_CRM_DUE_DATE'         => $_ARRAYLANG['TXT_DUE_DATE'],
                'TXT_CRM_ADD_NOTE'         => $_ARRAYLANG['TXT_NOTES_ADD'],
        ));

        if (isset($_GET['ajax'])) {
            $this->_objTpl->hideBlock("skipAjaxBlock");
            $this->_objTpl->hideBlock("skipAjaxBlock1");
        } else {
            $this->_objTpl->touchBlock("skipAjaxBlock");
            $this->_objTpl->touchBlock("skipAjaxBlock1");
        }
        echo $this->_objTpl->get();
        exit();
    }

    /**
     * Shows the Customer overview page
     *
     * @access Authenticated
     */
    function showCustomers() {
        global $_ARRAYLANG, $objDatabase, $objJs, $_LANGID;

        $tpl = isset ($_GET['tpl']) ? $_GET['tpl'] : '';
        if (!empty($tpl)) {
            switch($tpl) {
                case 'managecontact':
                    $this->_modifyContact();
                    break;
                case 'addnote':
                    $this->_modifyNotes();
                    break;
            }
            return;
        }        

        JS::activate("cx");
        JS::activate("jqueryui");
        JS::registerJS("lib/javascript/crm/main.js");
        JS::registerJS("lib/javascript/crm/customerTooltip.js");
        JS::registerCSS("lib/javascript/crm/css/customerTooltip.css");
        JS::registerCSS("lib/javascript/crm/css/main.css");

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_customer_overview.html');

        $settings = $this->getSettings();

        $delValue         = isset($_GET['delId']) ? intval($_GET['delId']) : 0;
        $activeId         = isset($_GET['activeId']) ? intval($_GET['activeId']) : 0;
        $activeValue      = isset($_GET['active']) ? intval($_GET['active']) : 0;

        $this->_pageTitle = $_ARRAYLANG['TXT_CUSTOMER_OVERVIEW'];
        $this->_objTpl->setGlobalVariable('MODULE_NAME',$this->moduleName);

        if(!empty($delValue)) {
            $this->deleteCustomers();
        }
        if(!empty($activeId)) {
            $this->changeActive($activeId, $activeValue);
        }

        $mes = isset($_REQUEST['mes']) ? base64_decode($_REQUEST['mes']) : '';
        if(!empty($mes)) {
            switch($mes) {
                case "customerupdated":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_DETAILS_UPDATED_SUCCESSFULLY'];
                    break;
                case "customeradded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_ADDED_SUCCESSFULLY'];
                    break;
                case "contactdeleted":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_CONTACT_DELETED_SUCCESSFULLY'];
                    break;
                case "contactadded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_CONTACT_ADDED_SUCCESSFULLY'];
                    break;
                case "contactupdated":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_CONTACT_UPDATED_SUCCESSFULLY'];
                    break;
                case "deleted":
                    $this->_strOkMessage  = $_ARRAYLANG['TXT_CUSTOMER_DETAILS_DELETED_SUCCESSFULLY'];
                    break;
            }
        }

        $searchLink = '';
        $where = array();

        // This is the function to show the A-Z letters
        $alphaFilter = isset($_REQUEST['companyname_filter']) ? contrexx_input2raw($_REQUEST['companyname_filter']) : '';
        $this->parseLetterIndexList('index.php?cmd='.$this->moduleName.'&act=customers', 'companyname_filter', $alphaFilter);
        $searchLink .= (!empty($alphaFilter)) ? "&companyname_filter=$alphaFilter" : '';

        if (!empty($alphaFilter)) {
            $where[] = " (c.customer_name LIKE '".contrexx_input2raw($alphaFilter)."%')";
        }

        $searchContactTypeFilter = isset($_GET['contactSearch']) ? (array) $_GET['contactSearch'] : array(1,2);
        $searchContactTypeFilter = array_map('intval', $searchContactTypeFilter);
        $where[] = " c.contact_type IN (".implode(',', $searchContactTypeFilter).")";
        foreach ($searchContactTypeFilter as $value) {
            $searchLink .= "&contactSearch[]=$value";
        }

        if (isset($_GET['advanced-search'])) {
            if (isset($_GET['s_name']) && !empty($_GET['s_name'])) {
                $where[] = " (c.customer_name LIKE '".contrexx_input2db($_GET['s_name'])."%' OR c.contact_familyname LIKE '".contrexx_input2db($_GET['s_name'])."%')";
            }
            if (isset($_GET['s_email']) && !empty($_GET['s_email'])) {
                $where[] = " (email.email LIKE '".contrexx_input2db($_GET['s_email'])."%')";
            }
            if (isset($_GET['s_address']) && !empty($_GET['s_address'])) {
                $where[] = " (addr.address LIKE '".contrexx_input2db($_GET['s_address'])."%')";
            }
            if (isset($_GET['s_city']) && !empty($_GET['s_city'])) {
                $where[] = " (addr.city LIKE '".contrexx_input2db($_GET['s_city'])."%')";
            }
            if (isset($_GET['s_postal_code']) && !empty($_GET['s_postal_code'])) {
                $where[] = " (addr.zip LIKE '".contrexx_input2db($_GET['s_postal_code'])."%')";
            }
            if (isset($_GET['s_notes']) && !empty($_GET['s_notes'])) {
                $where[] = " (c.notes LIKE '".contrexx_input2db($_GET['s_notes'])."%')";
            }
            $searchLink .= "&s_name={$_GET['s_name']}&s_email={$_GET['s_email']}&s_address={$_GET['s_address']}&s_city={$_GET['s_city']}&s_postal_code={$_GET['s_postal_code']}&s_notes={$_GET['s_notes']}";
        }
        if (isset($_GET['customer_type']) && !empty($_GET['customer_type'])) {
            $where[] = " (c.customer_type = '".intval($_GET['customer_type'])."')";
        }
        if (isset($_GET['filter_membership']) && !empty($_GET['filter_membership'])) {
            $where[] = " mem.membership_id = '".intval($_GET['filter_membership'])."'";
        }

        if (isset($_GET['term']) && !empty($_GET['term'])) {
            $fullTextContact = array();
            if (in_array(2, $searchContactTypeFilter))
                $fullTextContact[]  =  'c.customer_name, c.contact_familyname';
            if (in_array(1, $searchContactTypeFilter))
                $fullTextContact[]  = 'c.customer_name';
            if (empty($fullTextContact)) {
                $fullTextContact[]  =  'c.customer_name, c.contact_familyname';
            }
            $where[] = " MATCH (".implode(',', $fullTextContact).") AGAINST ('".contrexx_input2db($_GET['term'])."' IN BOOLEAN MODE)";
        }

        //  Join where conditions
        $filter = '';
        if (!empty ($where))
            $filter = " WHERE ".implode(' AND ', $where);

        $searchLink .= "&customer_type={$_GET['customer_type']}&term={$_GET['term']}&filter_membership={$_GET['filter_membership']}";

        $sortingFields = array("c.customer_name" ,  "activities", "c.added_date");
        $sorto = (isset ($_GET['sorto'])) ? (((int) $_GET['sorto'] == 0) ? 'DESC' : 'ASC') : 'DESC';
        $sortf = (isset ($_GET['sortf']) && in_array($sortingFields[$_GET['sortf']], $sortingFields)) ? $sortingFields[$_GET['sortf']] : 'c.id';
        $sortLink = "&sorto={$_GET['sorto']}&sortf={$_GET['sortf']}";

        $query = "SELECT
                           DISTINCT c.id,                           
                           c.customer_id,
                           c.customer_type,
                           c.customer_name,
                           c.contact_familyname,
                           c.contact_type,
                           c.contact_customer AS contactCustomerId,
                           c.status,
                           c.added_date,
                           c.profile_picture,
                           con.customer_name AS contactCustomer,
                           email.email,
                           phone.phone,
                           t.label AS cType,
                           Inloc.value AS industryType,                           
                           ((SELECT count(1) AS notesCount FROM `".DBPREFIX."module_{$this->moduleName}_customer_comment` AS com WHERE com.customer_id = c.id ) +
                           (SELECT count(1) AS tasksCount FROM `".DBPREFIX."module_{$this->moduleName}_task` AS task WHERE task.customer_id = c.id ) +
                           (SELECT count(1) AS dealsCount FROM `".DBPREFIX."module_{$this->moduleName}_deals` AS deal WHERE deal.customer = c.id )) AS activities
                       FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS con
                         ON c.contact_customer =con.id
                       LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_customer_types AS t
                         ON c.customer_type = t.id
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` as email
                         ON (c.id = email.contact_id AND email.is_primary = '1')
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` as phone
                         ON (c.id = phone.contact_id AND phone.is_primary = '1')
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` as addr
                         ON (c.id = addr.contact_id AND addr.is_primary = '1')
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_membership` as mem
                         ON (c.id = mem.contact_id)
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_industry_types` AS Intype
                         ON c.industry_type = Intype.id
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_industry_type_local` AS Inloc
                         ON Intype.id = Inloc.entry_id AND Inloc.lang_id = ".$_LANGID."
                $filter
                       ORDER BY $sortf $sorto";

        /* Start Paging ------------------------------------ */
        $intPos             = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        $intPerPage         = $this->getPagingLimit();
        $intPerPage         = 20;
        $this->_objTpl->setVariable('ENTRIES_PAGING', getPaging($this->countRecordEntries($query), $intPos, "./index.php?cmd={$this->moduleName}&act=customers$searchLink$sortLink", false,true, $intPerPage));

        $pageLink           = "&pos=$intPos";
        /* End Paging -------------------------------------- */

        $selectLimit = " LIMIT $intPos, $intPerPage";

        $query = $query. $selectLimit;

        $objResult = $objDatabase->Execute($query);

        if($objResult && $objResult->RecordCount() == 0) {
            $errMsg = "<div width='100%'>No Records Found ..</div>";
            $this->_objTpl->setVariable('TXT_NORECORDFOUND_ERROR', $errMsg);
        }

        $row       = 'row2';

        $sortOrder = ($_GET['sorto'] == 0) ? 1 : 0;
        //Apply standard values.
        $this->_objTpl->setGlobalVariable(array(
                'TXT_CRM_CUSTOMER_OVERVIEW'     => $_ARRAYLANG['TXT_CUSTOMER_OVERVIEW'],
                'TXT_DISPLAY_ENTRIES'           => 'none',
                'TXT_CRM_FILTERS'               =>  $_ARRAYLANG['TXT_CRM_FILTERS'],
                'TXT_DELETE_ENTRIES'            =>  $_ARRAYLANG['TXT_ARE_YOU_SURE_DELETE_ENTRIES'],
                'TXT_DELETE_SELECTED_ENTRIES'   =>  $_ARRAYLANG['TXT_ARE_YOU_SURE_DELETE_SELECTED_ENTRIES'],
                'TXT_TITLE_COMPANY_NAME'        =>  $_ARRAYLANG['TXT_TITLE_COMPANY_NAME'],
                'TXT_TITLE_NAME'                =>  $_ARRAYLANG['TXT_TITLE_NAME'],
                'TXT_DISCOUNT'                  =>  $_ARRAYLANG['TXT_DISCOUNT'],
                'TXT_TITLE_ROLE'                =>  $_ARRAYLANG['TXT_TITLE_ROLE']  ,
                'TXT_TITLE_CUSTOMERTYPE'        =>  $_ARRAYLANG['TXT_TITLE_CUSTOMERTYPE']  ,
                'TXT_TITLE_POSTAL_CODE'         =>  $_ARRAYLANG['TXT_TITLE_POSTAL_CODE']  ,
                'TXT_TITLE_NOTES'               =>  $_ARRAYLANG['TXT_TITLE_NOTES']  ,
                'TXT_CUSTOMER_ID'               =>  $_ARRAYLANG['TXT_TITLE_CUSTOMERID'],
                'TXT_TITLE_TYPE'                =>  $_ARRAYLANG['TXT_TITLE_TYPE'],
                'TXT_TITLE_CITY'                =>  $_ARRAYLANG['TXT_TITLE_CITY']  ,
                'TXT_TITLE_ADDRESS'             =>  $_ARRAYLANG['TXT_TITLE_ADDRESS']  ,
                'TXT_TITLE_PRIMARY_EMAIL'       =>  $_ARRAYLANG['TXT_TITLE_PRIMARY_EMAIL']  ,
                'TXT_TITLE_PRIMARY_NAME'        =>  $_ARRAYLANG['TXT_TITLE_PRIMARY_NAME']  ,
                'TXT_TITLE_TELEPHONE'           =>  $_ARRAYLANG['TXT_TITLE_TELEPHONE']  ,
                'TXT_TITLE_ADDEDDATE'           =>  $_ARRAYLANG['TXT_TITLE_ADDEDDATE']  ,
                'TXT_TITLE_ACTIVE'              =>  $_ARRAYLANG['TXT_TITLE_ACTIVE']  ,
                'TXT_TITLE_FUNCTIONS'           =>  $_ARRAYLANG['TXT_TITLE_FUNCTIONS']  ,
                'TXT_SELECT_ACTION'             =>  $_ARRAYLANG['TXT_SELECT_ACTION']  ,
                'TXT_DELETE_SELECTED_ITEMS'     =>  $_ARRAYLANG['TXT_DELETE_SELECTED_ITEMS']  ,
                'TXT_SELECT_ALL'                =>  $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_REMOVE_SELECTION'          =>  $_ARRAYLANG['TXT_REMOVE_SELECTION'],
                'TXT_SELECT_ACTION'             =>  $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_ACTIVATESELECTED'          =>  $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED'        =>  $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_DELETE_SELECTED'           =>  $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_SERVICE_PLAN'              =>  $_ARRAYLANG['TXT_SERVICE_PLAN'],
                'TXT_TICKET'                    =>  $_ARRAYLANG['TXT_TICKET'],
                'TXT_SUPPORT_CASES'             =>  $_ARRAYLANG['TXT_SUPPORT_CASES'],
                'TXT_SUPPORT_TICKET'            =>  $_ARRAYLANG['TXT_SUPPORT_TICKET'],
                'TXT_DATE'                      =>  $_ARRAYLANG['TXT_DATE'],
                'TXT_TITLE'                     =>  $_ARRAYLANG['TXT_TITLE'],
                'TXT_DESCRIPTION'               =>  $_ARRAYLANG['TXT_DESCRIPTION'],
                'TXT_SUPPORTPLAN_COVERAGE'      =>  $_ARRAYLANG['TXT_SUPPORTPLAN_COVERAGE'],
                'TXT_TITLE_STATUS'              =>  $_ARRAYLANG['TXT_TITLE_STATUS'],
                'TXT_HOSTING'                   =>  $_ARRAYLANG['TXT_HOSTING'],
                'TXT_START_DATE'                =>  $_ARRAYLANG['TXT_START_DATE'],
                'TXT_DOMAIN'                    =>  $_ARRAYLANG['TXT_DOMAIN'],
                'TXT_INVOICESENT'               =>  $_ARRAYLANG['TXT_INVOICESENT'],
                'TXT_NEXT_INVOICE'              =>  $_ARRAYLANG['TXT_NEXT_INVOICE'],
                'TXT_ISSUE_DATE'                =>  $_ARRAYLANG['TXT_ISSUE_DATE'],
                'TXT_VADIL_UNTIL'               =>  $_ARRAYLANG['TXT_VADIL_UNTIL'],
                'TXT_CASES_USED'                =>  $_ARRAYLANG['TXT_CASES_USED'],
                'TXT_SERVICE_TYPE'              => $_ARRAYLANG['TXT_SERVICE_TYPE'],
                'TXT_CUSTOMER_ADDSUPPORT_PLAN'  => $_ARRAYLANG['TXT_CUSTOMER_ADDSUPPORT_PLAN'],
                'TXT_CUSTOMER'                  => $_ARRAYLANG['TXT_CUSTOMER'],
                'TXT_CUSTOMER_ADDSUPPORT_CASE'  => $_ARRAYLANG['TXT_CUSTOMER_ADDSUPPORT_CASE'],
                'TXT_HOSTING_TYPE'              => $_ARRAYLANG['TXT_HOSTING_TYPE'],
                'TXT_DOMAIN'                    => $_ARRAYLANG['TXT_DOMAIN'],
                'TXT_PASSWORD'                  => $_ARRAYLANG['TXT_PASSWORD'],
                'TXT_REGISTRATION_DATE'         => $_ARRAYLANG['TXT_REGISTRATION_DATE'],
                'TXT_INVOICE_PERIOD'            => $_ARRAYLANG['TXT_INVOICE_PERIOD'],
                'TXT_MINIMAL_DURATION'          => $_ARRAYLANG['TXT_MINIMAL_DURATION'],
                'TXT_INVOICESENT'               => $_ARRAYLANG['TXT_INVOICESENT'],
                'TXT_NEXTINVOICE'               => $_ARRAYLANG['TXT_NEXTINVOICE'],
                'TXT_PRICE'                     => $_ARRAYLANG['TXT_PRICE'],
                'TXT_ADDITIONAL_INFORMATION'    => $_ARRAYLANG['TXT_ADDITIONAL_INFORMATION'],
                'TXT_CUSTOMER_ADDHOSTING'       => $_ARRAYLANG['TXT_CUSTOMER_ADDHOSTING'],
                'TXT_CUSTOMER_SUPPORT_PLAN'     => $_ARRAYLANG['TXT_CUSTOMER_SUPPORT_PLAN'],
                'TXT_STATUS'                    => $_ARRAYLANG['TXT_STATUS'],
                'TXT_SUPPORT_VADIL_UNTIL'       => $_ARRAYLANG['TXT_SUPPORT_VADIL_UNTIL'],
                'TXT_SUPPORT_CASES_USED'        => $_ARRAYLANG['TXT_SUPPORT_CASES_USED'],
                'TXT_TITLE_EMAIL'               => $_ARRAYLANG['TXT_TITLE_EMAIL'],
                'TXT_CRM_CUSTOMER_EXPORT'       => $_ARRAYLANG['TXT_EXPORT_NAME'],
                'TXT_CRM_ADD_NEW_CUSTOMER'      => $_ARRAYLANG['TXT_CRM_ADD_NEW_CUSTOMER'],
                'TXT_CRM_ADD_NEW_CONTACT'       => $_ARRAYLANG['TXT_CRM_ADD_NEW_CONTACT'],
                'TXT_CRM_CONTACTS'              => $_ARRAYLANG['TXT_CRM_PERSONS'],
                'TXT_CRM_CUSTOMERS'             => $_ARRAYLANG['TXT_TITLE_COMPANY_NAME'],
                'TXT_CRM_CUSTOMER_SEARCH_HINT'  => $_ARRAYLANG['TXT_CRM_CUSTOMER_SEARCH_HINT'],
                'TXT_CRM_ADVANCED_SEARCH'       => $_ARRAYLANG['TXT_CRM_ADVANCED_SEARCH'],
                'TXT_CRM_COMPANY_NAME'          => $_ARRAYLANG['TXT_COMPANY_NAME'],
                'TXT_CRM_CONTACT_NAME'          => $_ARRAYLANG['TXT_CRM_CUSTOMER_CONTACT_NAME'],
                'TXT_CRM_PRIMARY_EMAIL'         => $_ARRAYLANG['TXT_TITLE_EMAIL'],
                'TXT_CRM_ADDRESS'               => $_ARRAYLANG['TXT_TITLE_ADDRESS'],
                'TXT_CRM_CITY'                  => $_ARRAYLANG['TXT_TITLE_CITY'],
                'TXT_CRM_CUSTOMER_TYPE'         => $_ARRAYLANG['TXT_CUSTOMER_TYPE'],
                'TXT_CRM_POSTAL_CODE'           => $_ARRAYLANG['TXT_TITLE_POSTAL_CODE'],
                'TXT_CRM_DESCRIPTION'           => $_ARRAYLANG['TXT_DESCRIPTION'],
                'TXT_FILTER_CUSTOMER_TYPE'      => $_ARRAYLANG['TXT_FILTER_CUSTOMER_TYPE'],
                'TXT_CRM_SEARCH'                => $_ARRAYLANG['TXT_CRM_SEARCH'],
                'TXT_FILTER_MEMBERSHIP'         => $_ARRAYLANG['TXT_FILTER_MEMBERSHIP'],
                'TXT_CRM_CUSTOMERID'            => $_ARRAYLANG['TXT_TITLE_CUSTOMERID'],
                'TXT_CRM_CUSTOMERTYPE'          => $_ARRAYLANG['TXT_TITLE_CUSTOMERTYPE'],
                'TXT_CRM_NOTES'                 => $_ARRAYLANG['TXT_COMMENT_TITLE'],
                'TXT_CRM_TASKS'                 => $_ARRAYLANG['TXT_TASKS'],
                'TXT_CRM_OPPURTUNITIES'         => $_ARRAYLANG['TXT_OPPORTUNITY'],
                'TXT_CRM_CONTACT'               => $_ARRAYLANG['TXT_CRM_CONTACT'],
                'TXT_CRM_ACTIVITIES'            => $_ARRAYLANG['TXT_CRM_ACTIVITIES'],
                'CRM_ADVANCED_SEARCH_STYLE'     => (isset($_GET['advanced-search'])) ? "" : "display:none;",
                'CRM_ADVANCED_SEARCH_CLASS'     => (isset($_GET['advanced-search'])) ? "arrow-up" : "arrow-down",
                'CRM_SEARCH_LINK'               => $searchLink,
                'CRM_NAME_SORT'                 => "&sortf=0&sorto=$sortOrder",
                'CRM_ACTIVITIES_SORT'           => "&sortf=1&sorto=$sortOrder",                
                'CRM_DATE_SORT'                 => "&sortf=4&sorto=$sortOrder",

                'CRM_CUSTOMER_CHECKED'          => in_array(1, $searchContactTypeFilter) ? "checked" : '',
                'CRM_CONTACT_CHECKED'           => in_array(2, $searchContactTypeFilter) ? "checked" : '',
                'CRM_SEARCH_TERM'               => contrexx_input2xhtml($_GET['term']),
                'CRM_SEARCH_NAME'               => contrexx_input2xhtml($_GET['s_name']),
                'CRM_SEARCH_EMAIL'              => contrexx_input2xhtml($_GET['s_email']),
                'CRM_SEARCH_ADDRESS'            => contrexx_input2xhtml($_GET['s_address']),
                'CRM_SEARCH_CITY'               => contrexx_input2xhtml($_GET['s_city']),
                'CRM_SEARCH_ZIP'                => contrexx_input2xhtml($_GET['s_postal_code']),
                'CRM_SEARCH_NOTES'               => contrexx_input2xhtml($_GET['s_notes']),
                'CRM_ACCESS_PROFILE_IMG_WEB_PATH'=> CRM_ACCESS_PROFILE_IMG_WEB_PATH,
                'TXT_CRM_ENTER_SEARCH_TERM'     => $_ARRAYLANG['TXT_CRM_ENTER_SEARCH_TERM'],
        ));

        $this->getCustomerTypeDropDown($this->_objTpl, isset($_GET['customer_type']) ? $_GET['customer_type'] : 0);

        $this->membership = $this->load->model("membership", __CLASS__);
        $this->getOverviewMembershipDropdown($this->_objTpl, $this->membership, isset($_GET['filter_membership']) ? $_GET['filter_membership'] : 0) ;

        $this->_objTpl->setGlobalVariable('TXT_DOWNLOAD_VCARD' , $_ARRAYLANG['TXT_DOWNLOAD_VCARD']);

        $row = "row2";
        $today = date('Y-m-d');
        if ($objResult) {
            while(!$objResult->EOF) {
                $notesCount = $objDatabase->getOne("SELECT count(1) AS notesCount FROM `".DBPREFIX."module_{$this->moduleName}_customer_comment` AS com WHERE com.customer_id ={$objResult->fields['id']}");
                $tasksCount = $objDatabase->getOne("SELECT count(1) AS tasksCount FROM `".DBPREFIX."module_{$this->moduleName}_task` AS task WHERE task.customer_id = {$objResult->fields['id']}");
                $dealsCount = $objDatabase->getOne("SELECT count(1) AS dealsCount FROM `".DBPREFIX."module_{$this->moduleName}_deals` AS deal WHERE deal.customer = {$objResult->fields['id']}");
                if ($objResult->fields['contact_type'] == 1) {
                    if(($objResult->fields['status'] == "1")) {
                        $activeImage = 'images/icons/led_green.gif';
                        $activeValue = 1;
                        $imageTitle  = $_ARRAYLANG['TXT_ACTIVE'];
                    } else {
                        $activeValue = 0;
                        $activeImage = 'images/icons/led_red.gif';
                        $imageTitle  = $_ARRAYLANG['TXT_INACTIVE'];
                    }                    
                    $this->_objTpl->setVariable(array(
                            'ENTRY_ID'                  => (int) $objResult->fields['id'],
                            'CRM_COMPANY_NAME'          => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}' title='details'>".contrexx_raw2xhtml($objResult->fields['customer_name'])."</a>",
                            'TXT_ACTIVE_IMAGE'          => $activeImage,
                            'TXT_ACTIVE_VALUE'          => $activeValue,
                            'CRM_CUSTOMER_ID'           => contrexx_raw2xhtml($objResult->fields['customer_id']),
                            'CRM_CONTACT_PHONE'         => contrexx_raw2xhtml($objResult->fields['phone']),
                            'CRM_CONTACT_EMAIL'         => contrexx_raw2xhtml($objResult->fields['email']),
                            'CRM_ADDED_DATE'            => contrexx_raw2xhtml($objResult->fields['added_date']),
                            'CRM_ACTIVITIES_COUNT'      => $objResult->fields['activities'],
                            'CRM_CONTACT_NOTES_COUNT'   => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}#ui-tabs-1' title=''>{$_ARRAYLANG['TXT_COMMENT_TITLE']} ({$notesCount})</a>",
                            'CRM_CONTACT_TASK_COUNT'    => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}#ui-tabs-2' title=''>{$_ARRAYLANG['TXT_TASKS']} ({$tasksCount})</a>",
                            'CRM_CONTACT_DEALS_COUNT'   => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}#ui-tabs-3' title=''>{$_ARRAYLANG['TXT_OPPORTUNITY']} ({$dealsCount})</a>",
                            'CRM_CONTACT_ADDED_NEW'     => strtotime($today) == strtotime($objResult->fields['added_date']) ? '<img src="../images/crm/icons/new.png" alt="new" />' : '',
                            'CRM_ROW_CLASS'             => $row = ($row == "row2") ? "row1" : "row2",
                            'CRM_CONTACT_PROFILE_IMAGE' => !empty($objResult->fields['profile_picture']) ? contrexx_raw2xhtml($objResult->fields['profile_picture'])."_40X40.thumb" : '0_no_company_picture.gif',
                            'CRM_REDIRECT_LINK'         => '&redirect='.base64_encode("&act=customers{$searchLink}{$sortLink}{$pageLink}"),
                    ));
                    $this->_objTpl->parse("showCustomers");
                    $this->_objTpl->hideBlock("showContacts");
                }

                if ($objResult->fields['contact_type'] == 2) {
                    if(($objResult->fields['status'] == "1")) {
                        $activeImage = 'images/icons/led_green.gif';
                        $activeValue = 1;
                        $imageTitle  = $_ARRAYLANG['TXT_ACTIVE'];
                    } else {
                        $activeValue = 0;
                        $activeImage = 'images/icons/led_red.gif';
                        $imageTitle  = $_ARRAYLANG['TXT_INACTIVE'];
                    }
                    $this->_objTpl->setVariable(array(
                            'ENTRY_ID'                  => (int) $objResult->fields['id'],
                            'CRM_CONTACT_NAME'          => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}' title='details'>".contrexx_raw2xhtml($objResult->fields['customer_name']." ".$objResult->fields['contact_familyname']).'</a>',
                            'CRM_COMPNAY_NAME'          => (!empty($objResult->fields['contactCustomer'])) ? "Company : <a class='crm-companyInfoCardLink personPopupTrigger' href='./index.php?cmd=crm&act=showcustdetail&id={$objResult->fields['contactCustomerId']}' rel='{$objResult->fields['contactCustomerId']}' > ". contrexx_raw2xhtml($objResult->fields['contactCustomer'])."</a>" : '',
                            'TXT_ACTIVE_IMAGE'          => $activeImage,
                            'TXT_ACTIVE_VALUE'          => $activeValue,
                            'CRM_CONTACT_PHONE'         => contrexx_raw2xhtml($objResult->fields['phone']),
                            'CRM_CONTACT_EMAIL'         => contrexx_raw2xhtml($objResult->fields['email']),
                            'CRM_ADDED_DATE'            => contrexx_raw2xhtml($objResult->fields['added_date']),
                            'CRM_ACTIVITIES_COUNT'      => $objResult->fields['activities'],
                            'CRM_CONTACT_NOTES_COUNT'   => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}#ui-tabs-1' title=''>{$_ARRAYLANG['TXT_COMMENT_TITLE']} ({$notesCount})</a>",
                            'CRM_CONTACT_TASK_COUNT'    => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}#ui-tabs-2' title=''>{$_ARRAYLANG['TXT_TASKS']} ({$tasksCount})</a>",
                            'CRM_CONTACT_DEALS_COUNT'   => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['id']}#ui-tabs-3' title=''>{$_ARRAYLANG['TXT_OPPORTUNITY']} ({$dealsCount})</a>",
                            'CRM_CONTACT_ADDED_NEW'     => strtotime($today) == strtotime($objResult->fields['added_date']) ? '<img src="../images/crm/icons/new.png" alt="new" />' : '',
                            'CRM_ROW_CLASS'             => $row = ($row == "row2") ? "row1" : "row2",
                            'CRM_CONTACT_PROFILE_IMAGE' => !empty($objResult->fields['profile_picture']) ? contrexx_raw2xhtml($objResult->fields['profile_picture'])."_40X40.thumb" : '0_noavatar.gif',
                            'CRM_REDIRECT_LINK'         => '&redirect='.base64_encode("&act=customers{$searchLink}{$sortLink}{$pageLink}"),
                    ));                
                    $this->_objTpl->parse("showContacts");
                    $this->_objTpl->hideBlock("showCustomers");
                }
                $this->_objTpl->parse("showEntries");
                $objResult->MoveNext();
            }
        }
        
    }

    function showCustomerDetail() {
        global $_ARRAYLANG, $objDatabase,$objJs, $_LANGID;

        JS::activate("cx");
        JS::activate("jqueryui");
        JS::registerJS("lib/javascript/crm/main.js");
        JS::registerJS("lib/javascript/jquery.ui.tabs.js");
        JS::registerJS("lib/javascript/crm/customerTooltip.js");
        JS::registerJS("lib/javascript/jquery.form.js");
        JS::registerCSS("lib/javascript/crm/css/main.css");        
        JS::registerCSS("lib/javascript/crm/css/customerTooltip.css");
        
        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile('module_'.$this->moduleName.'_customer_details.html');

        $contactId = (int) $_GET['id'];
        $settings  = $this->getSettings();
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME'    => $this->moduleName,
                'PM_MODULE_NAME' => $this->pm_moduleName,
                'TXT_FUNCTIONS'  => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_DOWNLOAD_VCARD' => $_ARRAYLANG['TXT_DOWNLOAD_VCARD'],
                'ENTRY_ID'          => (int) $contactId,
        ));

        $mes = isset($_REQUEST['mes']) ? base64_decode($_REQUEST['mes']) : '';
        if(!empty($mes)) {
            switch($mes) {
                case "customerupdated":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_DETAILS_UPDATED_SUCCESSFULLY'];
                    break;
                case "customeradded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_ADDED_SUCCESSFULLY'];
                    break;
                case "contactdeleted":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_CONTACT_DELETED_SUCCESSFULLY'];
                    break;
                case "contactadded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_CONTACT_ADDED_SUCCESSFULLY'];
                    break;
                case "contactupdated":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_CUSTOMER_CONTACT_UPDATED_SUCCESSFULLY'];
                    break;
                case "deleted":
                    $this->_strOkMessage  = $_ARRAYLANG['TXT_CUSTOMER_DETAILS_DELETED_SUCCESSFULLY'];
                    break;
                case "projectUpdated":
                    $this->_strOkMessage = $_ARRAYLANG['CRM_PROJECT_UPDATED_STATUS_MESSAGE'];
                    break;
                case "projectAdded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PROJECTS_SUCESSMESSAGE'];
                    break;
                case "projectDelete":
                    $this->_strOkMessage = $_ARRAYLANG['CRM_PROJECT_DELETED_STATUS_MESSAGE'];
                    break;
                case "commentAdded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_COMMENT_SUCESSMESSAGE'];
                    break;
                case "commentEdited":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_COMMENT_UPDATESUCESSMESSAGE'];
                    break;
                case "dealsAdded":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_DEALS_ADDED_SUCCESSFULLY'];
                    break;
                case "dealsUpdated":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_DEALS_UPDATED_SUCCESSFULLY'];
                    break;
                case "CommentDelete":
                    $this->_strOkMessage = $_ARRAYLANG['TXT_COMMENT_DELETESUCESSMESSAGE'];
                    break;
            }
        }

        if (isset($_REQUEST['deleteComment'])) {
            $this->deleteCustomerComment();
        }

        if ($contactId) {
            $this->contact = $this->load->model('crmContact', __CLASS__);
            $this->contact->load($contactId);
            $custDetails = $this->contact->getCustomerDetails();
            
            $objMails  = $objDatabase->SelectLimit("SELECT email, email_type FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` WHERE `is_primary` = '1' AND contact_id = $contactId", 1);
            if ($objMails) {
                if ($objMails->RecordCount() && !empty($objMails->fields['email'])) {
                    $objTpl->setVariable("CRM_CONTACT_EMAIL", $this->formattedWebsite($objMails->fields['email'], 8)." <span class='description'>(".$_ARRAYLANG[$this->emailOptions[$objMails->fields['email_type']]].")</span>");                    
                } else {
                    $objTpl->hideBlock("contactEmails");
                }                
            }

            $ObjPhone = $objDatabase->SelectLimit("SELECT phone, phone_type FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` WHERE `is_primary` = '1' AND contact_id = $contactId", 1);
            if ($ObjPhone) {
                if ($ObjPhone->RecordCount() && !empty($ObjPhone->fields['phone'])) {
                    $objTpl->setVariable("CRM_CONTACT_PHONE", (!empty($ObjPhone->fields['phone'])) ? contrexx_input2xhtml($ObjPhone->fields['phone'])." <span class='description'>(".$_ARRAYLANG[$this->phoneOptions[$ObjPhone->fields['phone_type']]].")<span>" : '');
                } else {
                    $objTpl->hideBlock("contactPhones");
                }                
            }
            
            $objWeb   = $objDatabase->SelectLimit("SELECT url, url_profile FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` WHERE `is_primary` = '1' AND contact_id = $contactId", 1);
            if ($objWeb) {                
                if ($objWeb->RecordCount() && !empty($objWeb->fields['url'])) {
                    $objTpl->setVariable(array(
                                'CRM_WEBSITE_TYPE'    => !empty($objWeb->fields['url']) ? '<span class="description">('.$_ARRAYLANG[$this->websiteProfileOptions[$objWeb->fields['url_profile']]].')</span>' : '',
                                'CRM_WEBSITE_URL'     => !empty($objWeb->fields['url']) ? $this->formattedWebsite($objWeb->fields['url']) : '',
                        ));
                } else {
                    $objTpl->hideBlock("contactWebsite");
                }                
            }
            
            $objWeb   = $objDatabase->SelectLimit("SELECT url, url_profile FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_social_network` WHERE `is_primary` = '1' AND contact_id = $contactId", 1);
            if ($objWeb) {
                if ($objWeb->RecordCount() && !empty($objWeb->fields['url'])) {                    
                    $objTpl->setVariable(array(
                                'CRM_SOCIAL_TYPE'    => !empty($objWeb->fields['url']) ? '<span class="description">('.$_ARRAYLANG[$this->socialProfileOptions[$objWeb->fields['url_profile']]].')</span>' : '',
                                'CRM_SOCIAL_URL'     => !empty($objWeb->fields['url']) ? $this->formattedWebsite($objWeb->fields['url'], $objWeb->fields['url_profile']) : '',
                        ));
                } else {
                    $objTpl->hideBlock("contactSocial");
                }
            }

            $objAddr  = $objDatabase->SelectLimit("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` WHERE `is_primary` = '1' AND contact_id = $contactId", 1);
            if ($objAddr) {
                if ($objAddr->RecordCount() && !empty($objAddr->fields['address'])) {
                    $addrLine  = contrexx_input2xhtml($objAddr->fields['address']);
                    $addrArr   = array();
                    !empty($objAddr->fields['city']) ? $addrArr[] = contrexx_input2xhtml($objAddr->fields['city'])  : '';
                    !empty($objAddr->fields['state']) ? $addrArr[] = contrexx_input2xhtml($objAddr->fields['state'])  : '';
                    !empty($objAddr->fields['zip']) ? $addrArr[] = contrexx_input2xhtml($objAddr->fields['zip'])  : '';
                    $country   = contrexx_input2xhtml($objAddr->fields['country']);

                    $address  = '';
                    $address .= !empty($objAddr->fields['address']) ? "$addrLine,<br> "  : '';
                    $address .= !empty($addrArr) ? implode(',', $addrArr).",<br> "  : '';
                    $address .= !empty($objAddr->fields['country']) ? "$country."  : '';
                    $addressFull .= $address."<span class='description'>(".$_ARRAYLANG[$this->addressTypes[$objAddr->fields['Address_Type']]].")</span><br>";
                    $addressFull .= "<a target='_blank' href='http://maps.google.com/maps?q={$addrLine},". implode(',', $addrArr) .",{$country}'>{$_ARRAYLANG['TXT_CRM_SHOW_ON_MAP']}</a>";

                    $objTpl->setVariable("CRM_CONTACT_ADDRESS", (!empty($addressFull)) ? ($addressFull) : '');
                } else {
                    $objTpl->hideBlock("contactAddresses");
                }                
            }

            if ($custDetails['contact_type'] == 1) {

                
                $objTpl->setVariable(array(
                        'CRM_COMPANY_NAME'      => contrexx_raw2xhtml($custDetails['customer_name']),                        
                        'CRM_CUSTOMERID'        => contrexx_raw2xhtml($custDetails['customer_id']),                        
                        'CRM_CUSTOMER_TYPE'     => "<a title='filter' href='./index.php?cmd={$this->moduleName}&act=customers&customer_type={$custDetails['customer_type']}'>".contrexx_raw2xhtml($custDetails['cType']).'</a>',
                        'CRM_CUSTOMER_CURRENCY' => contrexx_raw2xhtml($custDetails['currency']),
                        'CRM_INDUSTRY_TYPE'     => contrexx_raw2xhtml($custDetails['industry_name']),
                        'CRM_CONTACT_PROFILE_IMAGE' => !empty($custDetails['profile_picture']) ? contrexx_raw2xhtml($custDetails['profile_picture']).".thumb" : '0_no_company_picture.gif',

                        'TXT_CRM_NAME'                => $_ARRAYLANG['TXT_CRM_CONTACT_NAME'],
                        'TXT_CRM_WEBSITE'             => $_ARRAYLANG['TXT_CRM_WEBSITE'],                        
                        'TXT_CRM_CUSTOMERTYPE'        => $_ARRAYLANG['TXT_TITLE_CUSTOMERTYPE'],
                        'TXT_CRM_CUSTOMERID'          => $_ARRAYLANG['TXT_TITLE_CUSTOMERID'],
                        'TXT_CRM_CUSTOMER_CURRENCY'   => $_ARRAYLANG['TXT_TITLE_CURRENCY'],
                        'TXT_TITLE_CUSTOMER_ADDEDBY'  => $_ARRAYLANG['TXT_TITLE_CUSTOMER_ADDEDBY'],
                ));
                $objTpl->parse("customerGeneral");
                $objTpl->hideBlock("contactGeneral");

                // Contacts Display
                $objContacts = $objDatabase->Execute("SELECT con.id,
                                                     con.contact_familyname,
                                                     con.customer_name,
                                                     con.customer_type,
                                                     con.contact_customer,
                                                     con.contact_type,
                                                     con.status,
                                                     con.added_date,
                                                     e.email,
                                                     p.phone,
                                                     l.label,
                                                     con.profile_picture
                                                     FROM `".DBPREFIX."module_{$this->moduleName}_contacts` as con
                                                    LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` as e
                                                    ON (e.contact_id=con.id AND e.is_primary = '1')
                                                    LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` as p
                                                    ON (p.contact_id=con.id AND p.is_primary = '1')
                                                    LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_types` as l
                                                    ON (l.id=con.customer_type)
                                                    WHERE contact_customer=$contactId ORDER BY con.id DESC");
                if($objContacts) {
                    $row = 'row2';
                    while(!$objContacts->EOF) {
                        $activeImage = $objContacts->fields['status'] ? 'images/icons/led_green.gif' : 'images/icons/led_red.gif';
                        $this->_objTpl->setVariable(array(
                                'CRM_CONTACT_ID'     => (int) $objContacts->fields['id'],
                                'CUSTOMER_CONTACT_ID'=> contrexx_raw2xhtml($objContacts->fields['contact_customer']),
                                'CRM_CONTACT_CUSTOMER' => (!empty($custDetails['customer_name'])) ? "Company : <a class='crm-companyInfoCardLink personPopupTrigger' href='javascript:void(0)' rel='{$objContacts->fields['contact_customer']}' > ". contrexx_raw2xhtml($custDetails['customer_name'])."</a>" : '',
                                'CRM_CONTACT_NAME'   => "<a href='./index.php?cmd=crm&act=showcustdetail&id={$objContacts->fields['id']}'> ".contrexx_raw2xhtml($objContacts->fields['customer_name'] .' '.$objContacts->fields['contact_familyname'])."</a>",
                                'CRM_CONTACT_EMAIL'  => contrexx_raw2xhtml($objContacts->fields['email']),
                                'CRM_CONTACT_PHONE'  => contrexx_raw2xhtml($objContacts->fields['phone']),
                                'CRM_CONTACT_STATUS' => $activeImage,
                                'CRM_CONTACT_ADDED'  => $objContacts->fields['added_date'],
                                'CRM_CONTACT_TYPE'   => $objContacts->fields['label'],                                
                                'ROW_CLASS'          => $row = ($row == 'row2') ? 'row1': 'row2',
                                'CRM_CONTACTS_PROFILE_IMAGE'     => !empty($objContacts->fields['profile_picture']) ? contrexx_raw2xhtml($objContacts->fields['profile_picture'])."_40X40.thumb" : '0_noavatar.gif',
                                'CONTACT_REDIRECT_LINK' => '&redirect='.base64_encode("&act=showcustdetail&id=$contactId"),
                        ));
                        $this->_objTpl->parse('customerContacts');
                        $objContacts->MoveNext();
                    }
                }
                if($custDetails['contact_type'] != 1) {
                    $this->_objTpl->hideBlock("displayContacts");
                } else {
                    $objTpl->setVariable(array(
                            'TXT_CRM_CONTACT_NAME'  => $_ARRAYLANG['TXT_CRM_CONTACT_NAME'],
                            'TXT_CRM_CUSTOMERTYPE'  => $_ARRAYLANG['TXT_CRM_CUSTOMERTYPE'],
                            'TXT_TITLE_TELEPHONE'   => $_ARRAYLANG['TXT_TITLE_TELEPHONE'],
                            'TXT_CUSTOMER_ADDEDDATE' => $_ARRAYLANG['TXT_TITLE_ADDEDDATE'],
                            'TXT_CRM_CONTACT_STATUS' => $_ARRAYLANG['TXT_CRM_CONTACT_STATUS'],
                            'TXT_ADD_CONTACT'        => $_ARRAYLANG['TXT_ADD_CONTACT']
                    ));
                    $this->_objTpl->touchBlock("displayContacts");
                }
            }
            if ($custDetails['contact_type'] == 2) {
                $objTpl->setVariable(array(
                        'CRM_CONTACT_NAME'          => contrexx_raw2xhtml($custDetails['customer_name']),
                        'CRM_CONTACT_FAMILY_NAME'   => contrexx_raw2xhtml($custDetails['contact_familyname']),
                        'CRM_CONTACT_ROLE'          => contrexx_raw2xhtml($custDetails['contact_role']),
                        'CRM_COMPNAY_NAME'          => (!empty($custDetails['contactCustomerId'])) ? "<a class='crm-companyInfoCardLink personPopupTrigger' href='./index.php?cmd=crm&act=showcustdetail&id={$custDetails['contactCustomerId']}' rel='{$custDetails['contactCustomerId']}' > ". contrexx_raw2xhtml($custDetails['contactCustomer'])."</a>" : '',
                        'CRM_CONTACT_LANGUAGE'      => contrexx_raw2xhtml($custDetails['language']),
                        'CRM_CUSTOMER_CURRENCY'     => contrexx_raw2xhtml($custDetails['currency']),
                        'CRM_CONTACT_PROFILE_IMAGE' => !empty($custDetails['profile_picture']) ? contrexx_raw2xhtml($custDetails['profile_picture']).".thumb" : '0_noavatar.gif',
                        'CRM_CUSTOMERTYPE'          => "<a title='filter' href='./index.php?cmd={$this->moduleName}&act=customers&customer_type={$custDetails['customer_type']}'>".contrexx_raw2xhtml($custDetails['cType']).'</a>',

                        'TXT_CRM_NAME'              => $_ARRAYLANG['TXT_CRM_CONTACT_NAME'],
                        'TXT_CRM_FAMILY_NAME'       => $_ARRAYLANG['TXT_CRM_FAMILY_NAME'],
                        'TXT_CRM_CONTACT_ROLE'      => $_ARRAYLANG['TXT_ROLE'],
                        'TXT_CRM_COMPNAY_NAME'      => $_ARRAYLANG['TXT_COMPANY_NAME'],
                        'TXT_CRM_CUSTOMERTYPE'      => $_ARRAYLANG['TXT_TITLE_CUSTOMERTYPE'],
                        'TXT_CRM_CUSTOMER_CURRENCY' => $_ARRAYLANG['TXT_TITLE_CURRENCY'],
                        'TXT_CRM_CONTACT_LANGUAGE'  => $_ARRAYLANG['TXT_TITLE_LANGUAGE'],
                ));
                if (empty($custDetails['contactCustomerId'])) {
                    $objTpl->parse("contactCustomerType");
                    $objTpl->parse("contactCurrency");
                    $objTpl->hideBlock("contactCustomer");
                } else {
                    $objTpl->parse("contactCustomer");
                    $objTpl->hideBlock("contactCustomerType");
                    $objTpl->hideBlock("contactCurrency");
                    $objTpl->touchBlock("emptyContactCurrency");
                }
                $objTpl->parse("contactGeneral");
                $objTpl->hideBlock("customerGeneral");
            }
            $objMembership = $objDatabase->Execute("SELECT
                                                            `membership_id`, msl.value AS membership
                                                         FROM `".DBPREFIX."module_{$this->moduleName}_customer_membership` AS cm
                                                          LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_memberships` AS ms
                                                            ON cm.membership_id = ms.id
                                                          LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_membership_local` AS msl
                                                            ON ms.id = msl.entry_id AND lang_id = {$_LANGID}
                                                         WHERE `contact_id` = {$contactId}");                                                         
            $membershipLink = array();
            if ($objMembership) {
                while (!$objMembership->EOF) {
                    $membershipLink[] = "<a href='./index.php?cmd={$this->moduleName}&act=customers&filter_membership={$objMembership->fields['membership_id']}'>". contrexx_raw2xhtml($objMembership->fields['membership']) ."</a>";
                    $objMembership->MoveNext();
                }
            }

            $objTpl->setVariable(array(
                    'CRM_CONTACT_NAME'        => ($custDetails['contact_type'] == 1) ? contrexx_raw2xhtml($custDetails['customer_name']) : contrexx_raw2xhtml($custDetails['customer_name']." ".$custDetails['contact_familyname']),
                    'CRM_CONTACT_DESCRIPTION' => html_entity_decode($custDetails['notes'], ENT_QUOTES, CONTREXX_CHARSET),
                    'EDIT_LINK'               => ($custDetails['contact_type'] != 1) ? "index.php?cmd={MODULE_NAME}&redirect=showcustdetail&act=customers&tpl=managecontact&amp;type=contact&amp;id=$contactId&redirect=".base64_encode("&act=showcustdetail&id=$contactId") : "index.php?cmd={MODULE_NAME}&amp;act=customers&tpl=managecontact&amp;id=$contactId&redirect=".base64_encode("&act=showcustdetail&id=$contactId"),
            ));
        }

        $objTpl->setGlobalVariable(array(
                'TXT_CUSTOMER_OVERVIEW'     => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_NOTES_ADD'             => $_ARRAYLANG['TXT_NOTES_ADD'],
                'TXT_CRM_HISTROY'           => $_ARRAYLANG['TXT_CRM_HISTROY'],
                'TXT_CRM_PROFILE'           => $_ARRAYLANG['TXT_PROFILE'],
                'TXT_CRM_CONTACTS'          => $_ARRAYLANG['TXT_CRM_CONTACTS'],
                'TXT_CRM_PROFILE_INFO'      => $_ARRAYLANG['TXT_CRM_PROFILE_INFORMATION'],
                'TXT_CRM_GENERAL_INFO'      => $_ARRAYLANG['TXT_CRM_GENERAL_INFORMATION'],
                'TXT_CRM_CONTACT_EMAIL'     => $_ARRAYLANG['TXT_TITLE_EMAIL'],
                'TXT_CRM_CONTACT_PHONE'     => $_ARRAYLANG['TXT_PHONE'],
                'TXT_CRM_CONTACT_WEBSITE'   => $_ARRAYLANG['TXT_CRM_WEBSITE'],
                'TXT_CRM_SOCIAL_NETWORK'      =>    $_ARRAYLANG['TXT_CRM_SOCIAL_NETWORK'],
                'TXT_CRM_CONTACT_ADDRESSES' => $_ARRAYLANG['TXT_TITLE_ADDRESS'],
                'TXT_CRM_CONTACT_DESCRIPTION' => $_ARRAYLANG['TXT_DESCRIPTION'],
                'TXT_IMAGE_DELETE'            =>  $_ARRAYLANG['TXT_IMAGE_DELETE'],
                'TXT_IMAGE_EDIT'              =>  $_ARRAYLANG['TXT_IMAGE_EDIT'],
                'TXT_CRM_TASKS'               => $_ARRAYLANG['TXT_TASKS'],
                'TXT_CRM_PROJECTS'            => $_ARRAYLANG['TXT_CRM_PROJECTS'],
                'TXT_CRM_DEALS'               => $_ARRAYLANG['TXT_OPPORTUNITY'],
                'TXT_CUSTOMER_CONTACT'        => $_ARRAYLANG['TXT_CUSTOMER_CONTACT'],
                'TXT_CRM_INDUSTRY_TYPE'       => $_ARRAYLANG['TXT_CRM_INDUSTRY_TYPE'],
                'TXT_CRM_MEMBERSHIP'          => $_ARRAYLANG['TXT_CRM_MEMBERSHIP'],
                'TXT_CRM_WEBSITE'             => $_ARRAYLANG['TXT_CRM_WEBSITE'],
                'TXT_CRM_PROFILE_PHTO_TITLE'  => $_ARRAYLANG['TXT_CRM_PROFILE_PHTO_TITLE'],
                'TXT_CRM_PROFILE_PHOTO_TITLE1'=> $_ARRAYLANG['TXT_CRM_PROFILE_PHOTO_TITLE1'],
                'TXT_CRM_PROFILE_PHOTO_TITLE2'=> $_ARRAYLANG['TXT_CRM_PROFILE_PHOTO_TITLE2'],
                'TXT_CRM_PROFILE_PHOTO_DES'   => $_ARRAYLANG['TXT_CRM_PROFILE_PHOTO_DES'],
                'TXT_CRM_CHANGE_PHOTO'        => $_ARRAYLANG['TXT_CRM_CHANGE_PHOTO'],
                'CRM_ACCESS_PROFILE_IMG_WEB_PATH'   => CRM_ACCESS_PROFILE_IMG_WEB_PATH,
                'CRM_CUSTOMER_MEMBERSHIP'     => implode(' , ', $membershipLink),
                'TXT_CRM_CUSTOMER_DETAILS'    =>  ($custDetails['contact_type'] == 1) ? $_ARRAYLANG['TXT_CUSTOMER_DETAILS'] : $_ARRAYLANG['TXT_CONTACT_DETAILS'],
                'CRM_ADD_CONTACT_REDIRECT'    => "&redirect=".base64_encode("&act=showcustdetail&id=$contactId"),
        ));

        ($this->isPmInstalled && !empty($settings['allow_pm'])) ? $objTpl->touchBlock("contactsProjectsTab") : $objTpl->hideBlock("contactsProjectsTab");
        ($custDetails['contact_type'] == 1) ? $objTpl->touchBlock("contactSwitchTab") : $objTpl->hideBlock("contactSwitchTab");
        if ($custDetails['contact_type'] == 1) {
            $objTpl->touchBlock("company_default_image");
        } else {
            $objTpl->touchBlock("person_default_image");
        }
        
        $this->_pageTitle = $custDetails['contact_type'] == 1 ? $_ARRAYLANG['TXT_CUSTOMER_DETAILS'] : $_ARRAYLANG['TXT_CONTACT_DETAILS'];
    }

    function pmRemoveStylesAddcustomer() {

        $style = <<<END
       <style type="text/css">
       #contrexx_header,#navigation, #footer_top, #footer, #nav_tree, .subnavbar_level1, #subnavbar_level2,       
       #bottom_border {
       display: none;
       }
      </style>
END;
        return $style;
    }

    function pmRemoveStylesShowcustomers() {

        $style = <<<END
    <style type="text/css">
    #contrexx_header,#navigation, #footer_top, #footer, #nav_tree, .subnavbar_level1,  #bottom_border {
   display: none;
   }
   </style>
END;
        return $style;
    }

    function deleteCustomers() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $id = intval($_GET['id']);
        if(!empty($id)) {
            $deleteQuery    = 'DELETE       contact.*, email.*, phone.*, website.*, addr.*
                               FROM  `'.DBPREFIX.'module_'.$this->moduleName.'_contacts` AS contact
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_emails` AS email
                                 ON contact.id = email.contact_id
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_phone` AS phone
                                 ON contact.id = phone.contact_id
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_websites` AS website
                                 ON contact.id = website.contact_id
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_address` AS addr
                                 ON contact.id = addr.contact_id                               
                               WHERE contact.id ='.$id;
            $objDatabase->Execute($deleteQuery);
            $deleteComQuery = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_comment`
                               WHERE       customer_id = '.$id;
            $objDatabase->Execute($deleteComQuery);
            $deleteMembership = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_membership`
                                     WHERE contact_id = '.$id;
            $objDatabase->Execute($deleteMembership);
            $this->_strOkMessage = $_ARRAYLANG['TXT_DELETED_SUCCESSFULLY'];
        } else {
            $deleteIds = $_POST['selectedEntriesId'];
            foreach($deleteIds as $id) {
                $deleteQuery    = 'DELETE       contact.*, email.*, phone.*, website.*, addr.*
                               FROM  `'.DBPREFIX.'module_'.$this->moduleName.'_contacts` AS contact
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_emails` AS email
                                 ON contact.id = email.contact_id
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_phone` AS phone
                                 ON contact.id = phone.contact_id
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_websites` AS website
                                 ON contact.id = website.contact_id
                               LEFT JOIN    `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_address` AS addr
                                 ON contact.id = addr.contact_id                               
                               WHERE contact.id ='.$id;
                $objDatabase->Execute($deleteQuery);
                $deleteComQuery = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_comment`
                                   WHERE        customer_id = '.$id;
                $objDatabase->Execute($deleteComQuery);
                $deleteMembership = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_membership`
                                     WHERE contact_id = '.$id;
                $objDatabase->Execute($deleteMembership);
                $this->_strOkMessage = $_ARRAYLANG['TXT_DELETED_SUCCESSFULLY'];
            }
        }
        if (isset($_GET['ajax']))
            exit();
        $message = base64_encode("deleted");
        csrf::header("location:".ASCMS_ADMIN_WEB_PATH."/index.php?cmd=".$this->moduleName."&act=customers&mes=$message");
    }

    function customersChangeStatus() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;

        $status = ($_GET['status'] == 0) ? 1 : 0;
        $id     = $_GET['id'];
        $query = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_contacts SET status='.$status.' WHERE id = '.$id;
        $objDatabase->Execute($query);
        if($_REQUEST['type'] == "activate") {
            $arrStatusNote = $_POST['selectedEntriesId'];
            if($arrStatusNote != null) {
                foreach ($arrStatusNote as $noteId) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_contacts SET status='1' WHERE id=$noteId";
                    $objDatabase->Execute($query);
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'];
        }
        if($_REQUEST['type'] == "deactivate") {
            $arrStatusNote = $_POST['selectedEntriesId'];
            if($arrStatusNote != null) {
                foreach ($arrStatusNote as $noteId) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_contacts SET status='0' WHERE id=$noteId";
                    $objDatabase->Execute($query);
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
        }
        $this->showCustomers();
    }

    function customerTypeChangeStatus() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $status = ($_GET['status'] == 0) ? 1 : 0;
        $id     = $_GET['id'];
        $query  = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_customer_types SET active='.$status.' WHERE id = '.$id;
        $objDatabase->Execute($query);
        ($status) ?  $this->_strOkMessage = $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'] : $this->_strOkMessage = $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
        if($_REQUEST['type'] == "activate") {
            $arrStatusNote = $_POST['selectedEntriesId'];
            if($arrStatusNote != null) {
                foreach ($arrStatusNote as $noteId) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_customer_types SET active='1' WHERE id=$noteId";
                    $objDatabase->Execute($query);
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'];
        }
        if($_REQUEST['type'] == "deactivate") {
            $arrStatusNote = $_POST['selectedEntriesId'];
            if($arrStatusNote != null) {
                foreach ($arrStatusNote as $noteId) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_customer_types SET active='0' WHERE id=$noteId";
                    $objDatabase->Execute($query);
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
        }
        $_GET['tpl'] = 'customertypes';
        $this->settingsSubmenu();
        
    }

    function settingsSubmenu() {
        global $_ARRAYLANG;
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_settings_submenu.html',true,true);
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];

        $tpl = isset($_GET['tpl']) ? $_GET['tpl'] : '';
        $this->settingsController = $this->load->controller('settings', __CLASS__, & $this->_objTpl);

        switch ($tpl) {
            case 'customertypes':
                $this->settingsController->showCustomerSettings();
                break;
//            case 'successrate':
//                $this->showSuccessRate();
//                break;
            case 'opstages':                
                $this->showOpportunityStages();
                break;
            case 'currency':
                $this->settingsController->currencyoverview();
                break;
            case 'notes':
                $this->Notesoverview();
                break;
            case 'tasktypes':
                $this->settingsController->taskTypesoverview();
                break;
            case 'industry':
                $this->showIndustry();
                break;
            default:
                $tpl = "overview";
                $this->settingsController->showGeneralSettings();
                break;
        }

        $this->_objTpl->setVariable(array(
                'MODULE_NAME'                    => $this->moduleName,
                'TXT_NOTES'                      => $_ARRAYLANG['TXT_NOTES'],
                'TXT_GENERAL'                    => $_ARRAYLANG['TXT_GENERAL'],
                'TXT_CURRENCY'                   => $_ARRAYLANG['TXT_CURRENCY'],
                'TXT_CRM_TASK_TYPES'             => $_ARRAYLANG['TXT_CRM_TASK_TYPES'],
                'TXT_CUSTOMER_TYPES'             => $_ARRAYLANG['TXT_CUSTOMER_TYPES'],
                'TXT_SUCCESS_RATE'               => $_ARRAYLANG['TXT_SUCCESS_RATE'],
                'TXT_CRM_DEALS_STAGES'           => $_ARRAYLANG['TXT_CRM_DEALS_STAGES'],
                'TXT_CRM_CUSTOMER_INDUSTRY'      => $_ARRAYLANG['TXT_CRM_CUSTOMER_INDUSTRY'],
                strtoupper($tpl)."_ACTIVE"       => 'active'
        ));
    }

    function showSuccessRate() {
        global $_ARRAYLANG, $objDatabase ,$objJs;

        JS::activate('jquery');
        $objTpl = $this->_objTpl;
        $objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_success_rate.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $objTpl->setGlobalVariable("MODULE_NAME", $this->moduleName);

        $fn = isset($_GET['fn']) ? $_GET['fn'] : '';
        switch ($fn) {
            case 'modify':
                $this->saveSuccessRate();
                if (isset($_GET['ajax']))
                    exit();
                break;
            default:
                break;
        }

        $action = (isset ($_REQUEST['actionType'])) ? $_REQUEST['actionType'] : '';
        $successEntries = (isset($_REQUEST['successEntry'])) ? array_map('intval', $_REQUEST['successEntry']) : 0;
        $successEntriesorting = (isset($_REQUEST['sorting'])) ? array_map('intval', $_REQUEST['sorting']) : 0;

        switch ($action) {
            case 'changestatus':
                $this->activateSuccessRate((array) $_GET['id']);
                if (isset($_GET['ajax']))
                    exit();
            case 'activate':
                $this->activateSuccessRate($successEntries);
                break;
            case 'deactivate':
                $this->activateSuccessRate($successEntries, true);
                break;
            case 'delete':
                $this->deleteSuccessRates($successEntries);
                break;
            case 'deletesuccessrate':
                $this->deleteSuccessRate();
                if (isset($_GET['ajax']))
                    exit();
                break;
            default:
                break;
        }
        if (!empty ($action) || isset($_POST['save_entries'])) {
            $this->saveSuccessRate($successEntrySorting);
        }

        $label  = isset ($_POST['label']) ? contrexx_input2raw(trim($_POST['label'])) : '';
        $rate   = isset ($_POST['rate']) ? contrexx_input2raw(trim($_POST['rate'])) : '';
        $status = isset ($_POST['status']) ? 1 : (isset($_POST['add_rate']) ? 0 : 1);
        if (isset($_POST['add_rate'])) {
            if (!empty($label) && !empty($rate)) {
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_success_rate`
                                    SET label   = '".contrexx_raw2db($label)."',
                                        rate    = '".contrexx_raw2db($rate)."',
                                        status  =  $status,
                                        sorting = 0";
                $db = $objDatabase->Execute($query);
                $label = '';
                $rate = '';
                $status = 0;
                if ($db)
                    $this->_strOkMessage = "Success probability added successfully";
                else
                    $this->_strErrMessage = "Error in saving Record";
            } else {
                $this->_strErrMessage = "All values must be filled out";
            }
        }

        $objResult = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_success_rate` ORDER BY sorting ASC");

        $row = "row2";
        if ($objResult) {
            while (!$objResult->EOF) {
                $objTpl->setVariable(array(
                        'ENTRY_ID'          => (int) $objResult->fields['id'],
                        'CRM_LABEL'         => contrexx_raw2xhtml($objResult->fields['label']),
                        'CRM_SORTING'       => contrexx_raw2xhtml($objResult->fields['sorting']),
                        'CRM_SUCCESS_RATE'  => contrexx_raw2xhtml($objResult->fields['rate']),
                        'CRM_SUCCESS_STATUS'=> $objResult->fields['status'] ? 'images/icons/led_green.gif' : 'images/icons/led_red.gif',
                        'ROW_CLASS'         => $row = ($row == "row2" ? "row1" : "row2"),
                ));
                $objTpl->parse("successRateEntries");
                $objResult->MoveNext();
            }
        }

        $objTpl->setVariable(array(
                'TXT_STATUS'        => $_ARRAYLANG['TXT_STATUS'],
                'TXT_CRM_LABEL'     => $_ARRAYLANG['TXT_LABEL'],
                'TXT_CRM_ADD_RATE'  => $_ARRAYLANG['TXT_CRM_ADD_RATE'],
                'TXT_CRM_VALUE'     => $_ARRAYLANG['TXT_CRM_VALUE'],
                'TXT_SAVE'          => $_ARRAYLANG['TXT_SAVE'],
                'TXT_SUCCESS_RATES' => $_ARRAYLANG['TXT_SUCCESS_RATES'],
                'TXT_SORTING'       => $_ARRAYLANG['TXT_SORTING'],
                'TXT_FUNCTIONS'     => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ALL'    => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_REMOVE_SELECTION'   => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
                'TXT_SELECT_ACTION'      => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_ACTIVATESELECTED'   => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED' => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_DELETE_SELECTED'    => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'CRM_RATE_LABEL'         => contrexx_raw2xhtml($label),
                'CRM_RATE_VALUE'         => contrexx_raw2xhtml($rate),
                'CRM_RATE_CHECKED'       => ($status) ? 'checked' : ''
        ));

    }

    function saveSuccessRate() {
        global $objDatabase;

        // New update Query
        $idArr = array_map(intval, array_keys($_POST['sorting']));
        $ids = implode(',', $idArr);

        $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_success_rate SET `sorting` = CASE id ";
        foreach ($_POST['sorting'] as $id => $val) {
            $query .= sprintf(" WHEN %d THEN %d", (int) $id, $val);
        }

        $query .= " END,
                            `label` = CASE id ";
        foreach ($_POST['label'] as $id => $val) {
            $query .= sprintf(" WHEN %d THEN '%s'",(int) $id, contrexx_input2db($val));
        }
        $query .= " END,
                            `rate` = CASE id ";
        foreach ($_POST['rate'] as $id => $val) {
            $query .= sprintf(" WHEN %d THEN '%s'", (int) $id, contrexx_input2db($val));
        }
        $query .= " END WHERE id IN ($ids)";
        $db = $objDatabase->Execute($query);

    }

    function showOpportunityStages() {
        global $_ARRAYLANG, $objDatabase ,$objJs;

        JS::activate('jquery');
        $objTpl = $this->_objTpl;
        $objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_stages.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $objTpl->setGlobalVariable("MODULE_NAME", $this->moduleName);

        $fn = isset($_GET['fn']) ? $_GET['fn'] : '';
        switch ($fn) {
            case 'modify':
                $this->saveStage();
                if (isset($_GET['ajax']))
                    exit();
                break;
            default:
                break;
        }

        $action = (isset ($_REQUEST['actionType'])) ? $_REQUEST['actionType'] : '';
        $stageEntries = (isset($_REQUEST['stageEntry'])) ? array_map('intval', $_REQUEST['stageEntry']) : 0;
        $stageEntriesorting = (isset($_REQUEST['sorting'])) ? array_map('intval', $_REQUEST['sorting']) : 0;

        switch ($action) {
            case 'changestatus':
                $this->activateStage((array) $_GET['id']);
                if (isset($_GET['ajax']))
                    exit();
            case 'activate':
                $this->activateStage($stageEntries);
                break;
            case 'deactivate':
                $this->activateStage($stageEntries, true);
                break;
            case 'delete':
                $this->deleteStages($stageEntries);
                break;
            case 'deletestage':
                $this->deleteStage();
                if (isset($_GET['ajax']))
                    exit();
                break;
            default:
                break;
        }
        if (!empty ($action) || isset($_POST['save_entries'])) {
            $this->saveStageSorting($stageEntriesorting);
        }

        $label  = isset ($_POST['label']) ? contrexx_input2raw(trim($_POST['label'])) : '';
        $stage   = isset ($_POST['stage']) ? contrexx_input2raw(trim($_POST['stage'])) : '';
        $status = isset ($_POST['status']) ? 1 : (isset($_POST['add_stage']) ? 0 : 1);
        if (isset($_POST['add_stage'])) {
            if (!empty($label) && !empty($stage)) {
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_stages`
                                    SET label   = '".contrexx_raw2db($label)."',
                                        stage    = '".contrexx_raw2db($stage)."',
                                        status  =  $status,
                                        sorting = 0";
                $db = $objDatabase->Execute($query);
                $label = '';
                $stage = '';
                $status = 1;
                if ($db)
                    $this->_strOkMessage = "Opportunity stage added successfully";
                else
                    $this->_strErrMessage = "Error in saving Record";
            } else {
                $this->_strErrMessage = "All values must be filled out";
            }
        }

        $objResult = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_stages` ORDER BY sorting ASC");

        $row = "row2";
        if ($objResult) {
            while (!$objResult->EOF) {
                $objTpl->setVariable(array(
                        'ENTRY_ID'          => (int) $objResult->fields['id'],
                        'CRM_LABEL'         => contrexx_raw2xhtml($objResult->fields['label']),
                        'CRM_SORTING'       => contrexx_raw2xhtml($objResult->fields['sorting']),
                        'CRM_STAGE'         => contrexx_raw2xhtml($objResult->fields['stage']),
                        'CRM_STAGE_STATUS'  => $objResult->fields['status'] ? 'images/icons/led_green.gif' : 'images/icons/led_red.gif',
                        'ROW_CLASS'         => $row = ($row == "row2" ? "row1" : "row2"),
                ));
                $objTpl->parse("stageEntries");
                $objResult->MoveNext();
            }
        }

        $objTpl->setVariable(array(
                'TXT_STATUS'             => $_ARRAYLANG['TXT_STATUS'],
                'TXT_CRM_LABEL'          => $_ARRAYLANG['TXT_LABEL'],
                'TXT_CRM_ADD_STAGE'      => $_ARRAYLANG['TXT_CRM_ADD_STAGE'],
                'TXT_CRM_VALUE'          => $_ARRAYLANG['TXT_CRM_VALUE'],
                'TXT_SAVE'               => $_ARRAYLANG['TXT_SAVE'],
                'TXT_CRM_DEALS_STAGES'   => $_ARRAYLANG['TXT_CRM_DEALS_STAGES'],
                'TXT_CRM_DEALS_STAGE'    => $_ARRAYLANG['TXT_CRM_DEALS_STAGE'],
                'TXT_SORTING'            => $_ARRAYLANG['TXT_SORTING'],
                'TXT_FUNCTIONS'          => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ALL'         => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_REMOVE_SELECTION'   => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
                'TXT_SELECT_ACTION'      => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_ACTIVATESELECTED'   => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED' => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_DELETE_SELECTED'    => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'CRM_STAGE_LABEL'        => contrexx_raw2xhtml($label),
                'CRM_STAGE_VALUE'        => contrexx_raw2xhtml($stage),
                'CRM_STAGE_CHECKED'      => ($status) ? 'checked' : '',
                'TXT_PRODUCTS_SELECT_ENTRIES' => $_ARRAYLANG['TXT_NOTHING_SELECTED']
        ));

    }

    function saveStage() {
        global $objDatabase;

        // New update Query
        $idArr = array_map(intval, array_keys($_POST['sorting']));
        $ids = implode(',', $idArr);

        $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_stages SET `sorting` = CASE id ";
        foreach ($_POST['sorting'] as $id => $val) {
            $query .= sprintf(" WHEN %d THEN %d", (int) $id, $val);
        }

        $query .= " END,
                            `label` = CASE id ";
        foreach ($_POST['label'] as $id => $val) {
            $query .= sprintf(" WHEN %d THEN '%s'",(int) $id, contrexx_input2db($val));
        }
        $query .= " END,
                            `stage` = CASE id ";
        foreach ($_POST['rate'] as $id => $val) {
            $query .= sprintf(" WHEN %d THEN '%s'", (int) $id, contrexx_input2db($val));
        }
        $query .= " END WHERE id IN ($ids)";
        $db = $objDatabase->Execute($query);

    }

    function editCustomerTypes($labelValue="") {
        global $_CORELANG, $_ARRAYLANG, $objDatabase ,$objJs;
        
        $this->_pageTitle = $_ARRAYLANG['TXT_EDIT_CUSTOMER_TYPE'];
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_setting_editcustomer.html',true,true);
        $this->_objTpl->setGlobalVariable('MODULE_NAME', $this->moduleName);
        $id                 = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (empty($id)) {
            CSRF::header("location:./index.php?cmd={$this->moduleName}&act=settings&tpl=customertypes");
            exit();
        }

        $customerLabel      = isset($_POST['label']) ? contrexx_input2raw($_POST['label']) : '';
        $customerSorting    = isset($_POST['sortingNumber']) ? intval($_POST['sortingNumber']) : '';
        $customerStatus     = isset($_POST['activeStatus']) || !isset ($_POST['customer_type_submit']) ? 1 : 0;
        $hrlyRate           = array();
            
        if($_POST['customer_type_submit']) {
            $success = true;          

            $searchingQuery = "SELECT label FROM `".DBPREFIX."module_{$this->moduleName}_customer_types`
                                   WHERE  label = '".contrexx_input2db($customerLabel)."' AND id != $id ";
            $objResult = $objDatabase->Execute($searchingQuery);

            if(!$objResult->EOF) {
                $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPE_ALREADY_EXIST'];
                $success = false;
            } else {
                $insertCustomerTypes = "UPDATE `".DBPREFIX."module_".$this->moduleName."_customer_types`
                                            SET    `label`             = '".contrexx_input2db($customerLabel)."',
                                                   `pos`               = '".intval($customerSorting)."',
                                                   `active`            = '".intval($customerStatus)."'
                                            WHERE id =$id";

                $db = $objDatabase->Execute($insertCustomerTypes);
                if ($db)
                    $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPES_UPDATED_SUCCESSFULLY'];
                else
                    $success = false;
            }

            if ($success) {
                CSRF::header("location:./index.php?cmd={$this->moduleName}&act=settings&tpl=customertypes");
                exit();
            }
        } else {
            $objResult =   $objDatabase->Execute('SELECT  id,label, pos, active, hourly_rate
                                                  FROM '.DBPREFIX.'module_'.$this->moduleName.'_customer_types WHERE id = '.$id);

            $customerLabel      = $objResult->fields['label'];
            $customerSorting    = $objResult->fields['pos'];
            $customerStatus     = $objResult->fields['active'];            
        }

        $this->_objTpl->setVariable(array(
                'CUSTOMER_TYPES_JAVASCRIPT'         => $objJs->editCustomerTypeJavascript(),
                'TXT_CUSTOMER_TYPE_ID'              => (int) $id,
                'TXT_LABEL_VALUE'		    => contrexx_raw2xhtml($customerLabel),
                'CRM_CUSTOMER_TYPE_SORTING_NUMBER'  => (int) $customerSorting,
                'TXT_ACTIVATED_VALUE'               => $customerStatus ? 'checked' : '',
            
                'TXT_CUSTOMERS'                     => $_ARRAYLANG['TXT_CUSTOMERS'],
                'TXT_LABEL'                         => $_ARRAYLANG['TXT_LABEL'],
                'TXT_SAVE'                          => $_ARRAYLANG['TXT_SAVE'],
                'TXT_BACK'                          => $_ARRAYLANG['TXT_BACK'],                
                'TXT_TITLEACTIVE'                   => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_FUNCTIONS'                     => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ACTION'                 => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_DELETE_SELECTED'               => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_ADD_CUSTOMER_TYPES'            => $_ARRAYLANG['TXT_ADD_CUSTOMER_TYPES'],
                'TXT_EDIT_CUSTOMER_TYPE'            => $_ARRAYLANG['TXT_EDIT_CUSTOMER_TYPE'],
                'TXT_ENTER_LABEL_FIELD'             => $_ARRAYLANG['TXT_ENTER_LABEL_FIELD'],
                'TXT_CUSTOMER_TYPE_SORTING_NUMBER'  => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_ENTER_LABEL_FIELD_WITHOUT_SPECIAL_CHARACTERS' => $_ARRAYLANG['TXT_ENTER_LABEL_FIELD_WITHOUT_SPECIAL_CHARACTERS'],
                'TXT_CURRENCY_RATES'                => $_ARRAYLANG['TXT_CURRENCY_RATES'],
                'CSRF_PARAM'                        => CSRF::param(),
        ));
    }

    function deleteCustomerTypes() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        echo $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $query = 'SELECT `id` FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_types` WHERE `default` = 1';
        $chkdefaultType = $objDatabase->Execute($query);
        $defaultId = $chkdefaultType->fields['id'];

        if(!empty($id)) {
            $query = 'SELECT 1 FROM `'.DBPREFIX.'module_'.$this->moduleName.'_contacts` WHERE `customer_type` = '.$id;
            $chkCustomerTypes = $objDatabase->Execute($query);
            if ($chkCustomerTypes->RecordCount() == 0) {
                if ($defaultId != $id) {
                    $deleteQuery = 'DELETE FROM   `'.DBPREFIX.'module_'.$this->moduleName.'_customer_types`
                                    WHERE          id = '.$id;
                    $objDatabase->Execute($deleteQuery);
                    $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPES_DELETED_SUCCESSFULLY'];
                } else {

                    $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_DEFAULT_CUSTOMER_TYPES_CANNOT_BE_DELETED'];
                }
            } else {
                $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPES_CANNOT_BE_DELETED'];
            }
        } else {
            $deleteIds = array_map(intval,$_POST['selectedEntriesId']);
            $deleteIdstring = implode(',', $deleteIds);

            $query = 'SELECT customer_type FROM `'.DBPREFIX.'module_'.$this->moduleName.'_contacts` WHERE `customer_type` IN ('.$deleteIdstring.')';
            $chkCustomerTypes = $objDatabase->Execute($query);

            $idContainsCustomer = array();
            while (!$chkCustomerTypes->EOF) {
                $idContainsCustomer[] = $chkCustomerTypes->fields['customer_type'];
                $chkCustomerTypes->MoveNext();
            }

            foreach($deleteIds as $id) {
                if (in_array($id, $idContainsCustomer)) {
                    $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPES_CANNOT_BE_DELETED'];
                    continue;
                }
                if ($defaultId == $id) {
                    $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_DEFAULT_CUSTOMER_TYPES_CANNOT_BE_DELETED'];
                    continue;
                }
                $deleteQuery = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_types`
                                WHERE        id = '.$id;
                $objDatabase->Execute($deleteQuery);
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CUSTOMER_TYPES_DELETED_SUCCESSFULLY'];
            }
        }        
        CSRF::header('location:./index.php?cmd=crm&act=settings&tpl=customertypes');
        exit();
    }

    function deleteCustomerServiceplan() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;

        $serviceId       = isset($_GET['seriveId']) ? intval($_GET['seriveId']) : 0;
        $customerId        = isset($_GET['customerId']) ? intval($_GET['customerId']) : 0;



        if($serviceId!==0) {
            $deleteQuery    = 'DELETE  FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_serviceplan`
                               WHERE         id ='.$serviceId;
            $objDatabase->Execute($deleteQuery);

        }
        csrf::header('location:./index.php?cmd=crm&act=showcustdetail&action=addServicePlan&mes=serviceplanmessage&id='.$customerId);

    }

    function _modifyContact() {
        global $_ARRAYLANG, $objDatabase ,$objJs, $objResult, $_LANGID;

        /** verify the user redirects to details page **/
        $this->checkCustomerIdentity();

        JS::activate("jquery");
        JS::activate("jqueryui");
        JS::registerJS("lib/javascript/crm/main.js");
        JS::registerJS("lib/javascript/crm/contact.js");
        JS::registerCSS("lib/javascript/crm/css/main.css");
        JS::registerCSS("lib/javascript/crm/css/contact.css");
        JS::registerCSS("lib/javascript/chosen/chosen.css");
        JS::registerJS("lib/javascript/chosen/chosen.jquery.js");

        $settings  = $this->getSettings();
        
        $objFWUser = FWUser::getFWUserObject();
        $_GET['type'] = isset($_GET['type']) ? $_GET['type'] : 'customer';
        $redirect     = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : base64_decode('&act=customers');

        $this->_pageTitle = (isset($_REQUEST['id'])) ? $_ARRAYLANG["TXT_EDIT_".strtoupper($_GET['type'])] : $_ARRAYLANG["TXT_ADD_".strtoupper($_GET['type'])] ;
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_customer_modify.html');
        $this->_objTpl->setGlobalVariable("MODULE_NAME", $this->moduleName);

        $id               = (isset($_REQUEST['id'])) ? intval($_REQUEST['id']) : 0;

        $this->contact = $this->load->model('crmContact', __CLASS__);
        !empty($id) ? $this->contact->id = $id : '';
        $contactType      = (isset($_GET['type']) && $_GET['type'] == 'contact') ? 2 : 1;

        //person
        $this->contact->family_name      = (isset($_POST['family_name'])) ? contrexx_input2raw($_POST['family_name']) : '';
        $this->contact->contact_role     = (isset($_POST['contact_role'])) ? contrexx_input2raw($_POST['contact_role']) : '';
        $this->contact->contact_language = (isset($_POST['contact_language'])) ? (int) $_POST['contact_language'] : (empty($id) ? $_LANGID : 0);
        $this->contact->contact_customer = isset($_POST['company']) ? (int) $_POST['company'] : (isset($_GET['custId']) ? (int) $_GET['custId'] : 0);        
        $this->contact->contactType      = $contactType;
        $this->contact->contact_gender   = isset($_POST['contact_gender']) ? (int) $_POST['contact_gender'] : 0;
        
        $accountUserEmail                = (isset($_POST['contact_email'])) ? contrexx_input2raw($_POST['contact_email']) : '';
        $accountUserPassword             = (isset($_POST['contact_password'])) ? contrexx_input2raw($_POST['contact_password']) : '';
        $sendLoginDetails                = isset($_POST['send_account_notification']);
        
        $this->contact->account_id       = 0;
        
        // customer
        $tpl = isset($_REQUEST['tpl']) ? contrexx_input2db($_REQUEST['tpl']) : '';
        if (isset($_GET['design']) && $_GET['design'] == 'custom') {
            $this->_objTpl->setVariable(array(
                    'PM_REMOVE_BACKGROUND_STYLE'             => $this->pmRemoveStylesAddcustomer(),
                    'PM_AJAX_SAVE_FROM_SHADOWBOX_JAVASCRIPT' => $objJs->pmAjaxformSubmitForShadowbox($tpl),
            ));
        }
        
        $defaultTypeId  = $objDatabase->getOne('SELECT `id` FROM '.DBPREFIX.'module_'.$this->moduleName.'_customer_types WHERE `default` = 1');
        
        $this->contact->customerId          = isset($_POST['customerId']) ? contrexx_input2raw($_POST['customerId']) : '';
        $this->contact->customerType        = isset($_POST['customer_type']) ? (int) $_POST['customer_type'] : (empty($id) ? $defaultTypeId : '');
        $this->contact->customerName        = isset($_POST['companyName']) ? contrexx_input2raw($_POST['companyName']) : '';
        $this->contact->addedUser           = $objFWUser->objUser->getId();
        $this->contact->currency            = isset($_POST['currency']) ? (int) $_POST['currency'] : '';        
        $this->contact->datasource          = 1;


        $customerContacts    = isset($_POST['companyContacts']) ? array_map('intval', (array) $_POST['companyContacts']) : array();        
        $assignedMembersShip = isset($_POST['assigned_memberships']) ? array_map('intval', (array) $_POST['assigned_memberships']) : array();

        $this->contact->notes  = isset($_POST['notes']) ? html_entity_decode($_POST['notes'], ENT_QUOTES, CONTREXX_CHARSET) : '';
        $this->contact->industryType  = isset($_POST['industryType']) ? (int) $_POST['industryType'] : 0;
        $this->contact->user_name   = isset($_POST['contact_username']) ? contrexx_input2raw($_POST['contact_username']) : '';

        if(isset($_POST['save_contact']) || isset($_POST['save_add_new_contact'])) {
            $description            = $this->strip_only_tags($this->contact->notes, '<script><iframe>', $stripContent=false);
            $this->contact->notes   = $description;
            $msg = '';
            switch(true) {
                case ($contactType == 1 && !empty($id)):
                    $msg = "customerupdated";
                    break;
                case ($contactType == 2 && !empty($id)):
                    $msg = "contactupdated";
                    break;
                case ($contactType == 1):
                    $msg = "customeradded";
                    break;
                case ($contactType == 2):
                    $msg = "contactadded";
                    break;
                default:
                    break;
            }
            $result = $this->parseContacts($_POST);

            // unset customer type, customerId the contact have customer
            if (($this->contact->contactType == 2) && $this->contact->contact_customer != 0) {
                $this->contact->customerType = 0;
                $this->contact->currency     = 0;
                $this->contact->customerId   = '';                
            }

            if (!$settings['create_user_account'] || ($contactType == 1) || $this->addUser($accountUserEmail, $accountUserPassword, $sendLoginDetails)) {

                $this->contact->save();

                $this->updateCustomerMemberships((array) $assignedMembersShip, $this->contact->id);
                if ($contactType == 2) { // For contact
                    //$this->save
                } else {
                    $this->updateCustomerContacts((array) $customerContacts, $this->contact->id);
                }

                // insert Emails
                $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` WHERE `contact_id` = {$this->contact->id}");
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` (email, email_type, is_primary, contact_id) VALUES ";

                $values = array();
                foreach ($result['contactemail'] as $value) {
                    $values[] = "('".contrexx_input2db($value['value'])."', '".(int) $value['type']."', '".(int) $value['primary']."', '".$this->contact->id."')";
                }

                $query .= implode(",", $values);
                $objDatabase->Execute($query);

                // insert Phone
                $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` WHERE `contact_id` = {$this->contact->id}");
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` (phone, phone_type, is_primary, contact_id) VALUES ";

                $values = array();
                foreach ($result['contactphone'] as $value) {
                    $values[] = "('".contrexx_input2db($value['value'])."', '".(int) $value['type']."', '".(int) $value['primary']."', '".$this->contact->id."')";
                }

                $query .= implode(",", $values);
                $objDatabase->Execute($query);

                // insert Website
                $assignedWebsites = array();
                foreach ($result['contactwebsite'] as $value) {
                    $fields = array(
                                'id'            => array('val' => !empty($value['id']) ? (int) $value['id'] : NULL, 'omitEmpty' => true) ,
                                'url'           => $value['value'],                                
                                'url_profile'   => (int) $value['profile'],
                                'is_primary'    => $value['primary'],
                                'contact_id'    => $this->contact->id
                              );
                    if (!empty($value['id'])) {
                        array_push($assignedWebsites, $value['id']);
                        $query  = SQL::update("module_{$this->moduleName}_customer_contact_websites", $fields)." WHERE `id` = {$value['id']} AND `contact_id` = {$this->contact->id}";
                        $objDatabase->Execute($query);
                    } else {
                        $query  = SQL::insert("module_{$this->moduleName}_customer_contact_websites", $fields);
                        $db = $objDatabase->Execute($query);
                        if ($db)
                            array_push($assignedWebsites, $objDatabase->INSERT_ID());
                    }                    
                }

                $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` WHERE `id` NOT IN (".implode(',', $assignedWebsites).") AND `contact_id` = {$this->contact->id}");

                // insert social networks
                $assignedSocialNetwork = array();
                foreach ($result['contactsocial'] as $value) {
                    $fields = array(
                                'id'            => array('val' => !empty($value['id']) ? (int) $value['id'] : NULL, 'omitEmpty' => true) ,
                                'url'           => $value['value'],                                
                                'url_profile'   => (int) $value['profile'],
                                'is_primary'    => $value['primary'],
                                'contact_id'    => $this->contact->id
                              );
                    if (!empty($value['id'])) {
                        array_push($assignedSocialNetwork, $value['id']);
                        $query  = SQL::update("module_{$this->moduleName}_customer_contact_social_network", $fields)." WHERE `id` = {$value['id']} AND `contact_id` = {$this->contact->id}";
                        $objDatabase->Execute($query);
                    } else {
                        $query  = SQL::insert("module_{$this->moduleName}_customer_contact_social_network", $fields);
                        $db = $objDatabase->Execute($query);
                        if ($db)
                            array_push($assignedSocialNetwork, $objDatabase->INSERT_ID());
                    }
                }

                $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_social_network` WHERE `id` NOT IN (".implode(',', $assignedSocialNetwork).") AND `contact_id` = {$this->contact->id}");


                // insert address
                $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` WHERE `contact_id` = {$this->contact->id}");
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` (address, city, state, zip, country, Address_Type, is_primary, contact_id) VALUES ";

                $values = array();
                foreach ($result['contactAddress'] as $value) {
                    $values[] = "('".contrexx_input2db($value['address'])."', '".contrexx_input2db($value['city'])."', '".contrexx_input2db($value['state'])."', '".contrexx_input2db($value['zip'])."', '".contrexx_input2db($value['country'])."', '".intval($value['type'])."', '".intval($value['primary'])."', '".$this->contact->id."')";
                }

                $query .= implode(",", $values);

                $objDatabase->Execute($query);
                $ChckCount = 0;
                if(!empty($id)) {
                    $contactId = $this->contact->contact_customer;
                }
                if($this->contact->contactType == 2) {
                    $contactId = $this->contact->contact_customer;
                }

                $customerId = $this->contact->id;
                $customerName = $this->contact->customerName;
                // ajax request
                if ($_GET['design'] == 'custom') {
                    $returnString = array(
                            'errChk'       => $ChckCount,
                            'customerId'   => $customerId,
                            'customerName' => $customerName,
                            'contactId'    => $contactId,
                            'msg'          => $msg
                    );
                    echo json_encode($returnString);
                    exit();
                }

                if (isset($_POST['save_add_new_contact'])) {
                    $contactTypeUrl = $contactType == 2 ? '&type=contact' : '';
                    CSRF::header("Location:./index.php?cmd={$this->moduleName}&act=customers&tpl=managecontact$contactTypeUrl");
                    exit();
                }                
                //print base64_decode($redirect);
                CSRF::header("Location:./index.php?cmd={$this->moduleName}&act=overview&mes=".base64_encode($msg).base64_decode($redirect));
                exit();
            }            
        } elseif($this->contact->load($id)) {

            if ($contactType == 1) {
                $objContact = $objDatabase->Execute("SELECT `id` FROM `".DBPREFIX."module_{$this->moduleName}_contacts` WHERE `contact_customer` = {$this->contact->id}");
                if ($objContact) {
                    while(!$objContact->EOF) {
                        $customerContacts[] = (int) $objContact->fields['id'];
                        $objContact->MoveNext();
                    }
                }
            }

            $objMemberShips = $objDatabase->Execute("SELECT `membership_id` FROM `".DBPREFIX."module_{$this->moduleName}_customer_membership` WHERE `contact_id` = {$this->contact->id}");
            if ($objMemberShips) {
                while (!$objMemberShips->EOF) {
                    $assignedMembersShip[] = (int) $objMemberShips->fields['membership_id'];
                    $objMemberShips->Movenext();
                }
            }

            // Get emails and phones
            $objEmails = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` WHERE contact_id = {$this->contact->id} ORDER BY id ASC");
            if ($objEmails) {
                while(!$objEmails->EOF) {
                    $result['contactemail'][] = array("type" => $objEmails->fields['email_type'], "primary" => $objEmails->fields['is_primary'], "value" => $objEmails->fields['email']);
                    $objEmails->MoveNext();
                }
            }
            $objPhone = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` WHERE contact_id = {$this->contact->id} ORDER BY id ASC");
            if ($objPhone) {
                while(!$objPhone->EOF) {
                    $result['contactphone'][] = array("type" => $objPhone->fields['phone_type'], "primary" => $objPhone->fields['is_primary'], "value" => $objPhone->fields['phone']);
                    $objPhone->MoveNext();
                }
            }
            $objWebsite = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` WHERE contact_id = {$this->contact->id} ORDER BY id ASC");
            if ($objWebsite) {
                while(!$objWebsite->EOF) {
                    $result['contactwebsite'][] = array("id" => $objWebsite->fields['id'], "profile" => $objWebsite->fields['url_profile'], "primary" => $objWebsite->fields['is_primary'], "value" => $objWebsite->fields['url']);
                    $objWebsite->MoveNext();
                }
            }
            $objSocial = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_social_network` WHERE contact_id = {$this->contact->id} ORDER BY id ASC");
            if ($objSocial) {
                while(!$objSocial->EOF) {
                    $result['contactsocial'][] = array("id" => $objSocial->fields['id'], "profile" => $objSocial->fields['url_profile'], "primary" => $objSocial->fields['is_primary'], "value" => $objSocial->fields['url']);
                    $objSocial->MoveNext();
                }
            }
            $objAddress = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` WHERE contact_id = {$this->contact->id} ORDER BY id ASC");
            if ($objAddress) {
                while(!$objAddress->EOF) {
                    $result['contactAddress'][] = array("address" => $objAddress->fields['address'], "city" => $objAddress->fields['city'], "state" => $objAddress->fields['state'], "zip" => $objAddress->fields['zip'], "country" => $objAddress->fields['country'], "type" => $objAddress->fields['Address_Type'], "primary" => $objAddress->fields['is_primary']);
                    $objAddress->MoveNext();
                }
            }
        }

        // reset the email and phone fields
        if (empty($result['contactemail'])) $result['contactemail'][] = array("type" => ($contactType == 1) ? 1 : 0, "primary" => 1, "value" => "");
        if (empty($result['contactphone'])) $result['contactphone'][] = array("type" => 1, "primary" => 1, "value" => "");
        if (empty($result['contactwebsite'])) $result['contactwebsite'][] = array("id" => 0, "profile" => ($contactType == 1) ? 3 : 1, "primary" => 1, "value" => "");
        if (empty($result['contactsocial'])) $result['contactsocial'][] = array("id" => 0, "profile" => 4, "primary" => 1, "value" => "");
        if (empty($result['contactAddress'])) $result['contactAddress'][] = array("address" => '', "city" => '', "state" => '', "zip" => "", "country" => "Schweiz", "type" => 2, "primary" => 1);

        if (!empty($result['contactemail'])) {
            $Count = 1;
            //$showEmail = false;
            $showEmail = true;
            foreach($result['contactemail'] as $email) {
                if (!empty($email['value']) && !$showEmail)
                    $showEmail = true;

                $this->_objTpl->setVariable(array(
                        'CRM_CONTACT_EMAIL_NAME'    => "contactemail_{$Count}_{$email['type']}_{$email['primary']}",
                        'CRM_CONTACT_EMAIL'         => contrexx_raw2xhtml($email['value']),
                        'CRM_EMAIL_OPTION'          => $_ARRAYLANG[$this->emailOptions[$email['type']]],
                        'CRM_CONTACT_EMAIL_PRIMARY' => ($email['primary']) ? "primary_field" : "not_primary_field",
                ));
                $block = $contactType == 1 ? "customerEmailContainer" : "contactEmailContainer";
                $this->_objTpl->parse($block);
                $Count++;
            }
        }
        if (!empty($result['contactphone'])) {
            foreach($result['contactphone'] as $phone) {
                $this->_objTpl->setVariable(array(
                        'CRM_CONTACT_PHONE_NAME'    => "contactphone_{$Count}_{$phone['type']}_{$phone['primary']}",
                        'CRM_CONTACT_PHONE'         => contrexx_raw2xhtml($phone['value']),
                        'CRM_PHONE_OPTION'          => $_ARRAYLANG[$this->phoneOptions[$phone['type']]],
                        'CRM_CONTACT_PHONE_PRIMARY' => ($phone['primary']) ? "primary_field" : "not_primary_field",
                ));
                $block = $contactType == 1 ? "customerPhoneContainer" : "contactPhoneContainer";
                $this->_objTpl->parse($block);
                $Count++;
            }
        }
        if (!empty($result['contactwebsite'])) {            
            foreach($result['contactwebsite'] as $website) {
                $this->_objTpl->setVariable(array(
                        'CRM_CONTACT_WEBSITE_NAME'    => "contactwebsite_{$Count}_{$website['profile']}_{$website['primary']}",
                        'CRM_CONTACT_WEBSITE'         => contrexx_raw2xhtml($website['value']),
                        'CRM_WEBSITE_PROFILE'         => $_ARRAYLANG[$this->websiteProfileOptions[$website['profile']]],
                        'CRM_WEBSITE_OPTION'          => $_ARRAYLANG[$this->websiteOptions[$website['type']]],
                        'CRM_CONTACT_WEB_ID_NAME'     => "website_{$Count}",
                        'CRM_CONTACT_WEB_ID'          => (int) $website['id'],
                        'CRM_CONTACT_WEBSITE_PRIMARY' => ($website['primary']) ? "primary_field" : "not_primary_field",
                ));
                $block = $contactType == 1 ? "customerwebsiteContainer" : "contactwebsiteContainer";
                $this->_objTpl->parse($block);
                $Count++;
            }
        }
        
        if (!empty($result['contactsocial'])) {
            foreach($result['contactsocial'] as $social) {                
                $this->_objTpl->setVariable(array(
                        'CRM_CONTACT_SOCIAL_NAME'     => "contactsocial_{$Count}_{$social['profile']}_{$social['primary']}",
                        'CRM_CONTACT_SOCIAL'          => contrexx_raw2xhtml($social['value']),
                        'CRM_SOCIAL_PROFILE'          => $_ARRAYLANG[$this->socialProfileOptions[$social['profile']]],
                        'CRM_CONTACT_SOCIAL_ID_NAME'  => "social_{$Count}",
                        'CRM_CONTACT_SOCIAL_ID'       => (int) $social['id'],
                        'CRM_CONTACT_SOCIAL_PRIMARY'  => ($social['primary']) ? "primary_field" : "not_primary_field",
                ));
                $block = $contactType == 1 ? "customerSocialLinkContainer" : "contactSocialLinkContainer";
                $this->_objTpl->parse($block);
                $Count++;
            }
        }

        if (!empty($result['contactAddress'])) {
            $showAddress = false;
            
            foreach($result['contactAddress'] as $address) {
                if (!empty($address['address']) && !$showAddress)
                    $showAddress = true;
                
                $primary = ($address['primary']) ? 1 : 0;
                $this->_objTpl->setVariable(array(
                        'CRM_CONTACT_ADDRESS_NAME'  => "contactAddress_{$Count}_1_{$primary}",
                        'CRM_CONTACT_ADDRESS_VALUE' => contrexx_raw2xhtml($address['address']),
                        'CRM_CONTACT_CITY_NAME'     => "contactAddress_{$Count}_2_{$primary}",
                        'CRM_CONTACT_CITY_VALUE'    => contrexx_raw2xhtml($address['city']),
                        'CRM_CONTACT_STATE_NAME'    => "contactAddress_{$Count}_3_{$primary}",
                        'CRM_CONTACT_STATE_VALUE'   => contrexx_raw2xhtml($address['state']),
                        'CRM_CONTACT_ZIP_NAME'      => "contactAddress_{$Count}_4_{$primary}",
                        'CRM_CONTACT_ZIP_VALUE'     => contrexx_raw2xhtml($address['zip']),
                        'CRM_CONTACT_COUNTRY_NAME'  => "contactAddress_{$Count}_5_{$primary}",
                        'CRM_CONTACT_COUNTRY_VALUE' => $this->getContactAddressCountry($this->_objTpl, $address['country'], 'crmCountry'),
                        'CRM_CONTACT_ADDR_TYPE_NAME'  => "contactAddress_{$Count}_6_{$primary}",
                        'CRM_CONTACT_ADDR_TYPE_VALUE' => $this->getContactAddrTypeCountry($this->_objTpl, $address['type'], 'addressType'),
                        'CRM_CONTACT_ADDRESS_PRIMARY' => ($primary) ? "primary_field_address" : "not_primary_field_address",
                ));
                $this->_objTpl->parse("contactAddressContainer");
                $Count++;
            }
        }
        $this->getContactAddressCountry($this->_objTpl, 'Schweiz', 'additionalcrmCountry');
        $this->getContactAddrTypeCountry($this->_objTpl, 2, 'additionaladdressType');

        // special fields for contacts
        $objResult =   $objDatabase->Execute('SELECT  id,name,lang FROM    '.DBPREFIX.'languages');
        while(!$objResult->EOF) {
            $this->_objTpl->setVariable(array(
                    'TXT_LANG_ID'	=>  (int) $objResult->fields['id'],
                    'TXT_LANG_NAME'     =>  contrexx_raw2xhtml($objResult->fields['name']),
                    'TXT_LANG_SELECT'   =>  ($objResult->fields['id'] == $this->contact->contact_language) ? "selected=selected" : "",
            ));
            $langBlock = ($contactType == 2) ? "showAddtionalContactLanguages" : "ContactLanguages";
            $this->_objTpl->parse($langBlock);
            $objResult->MoveNext();
        }
        
        // special fields for customer
        if ($contactType == 1) {
            $this->getCustomerTypeDropDown($this->_objTpl, $this->contact->customerType); // Customer Types

            // Parse the contacts
            if (!empty($customerContacts)) {
                $objContacts = $objDatabase->Execute("SELECT `id`, `customer_name`, `contact_familyname` FROM `".DBPREFIX."module_{$this->moduleName}_contacts` WHERE `id` IN (".implode(',', $customerContacts).")");
                if ($objContacts) {
                    $row = "row2";
                    while(!$objContacts->EOF) {
                        $this->_objTpl->setVariable(array(
                                'CRM_CONTACT_ID'     => $objContacts->fields['id'],
                                'CRM_CONTACT_NAME'   => contrexx_raw2xhtml($objContacts->fields['contact_familyname']." ".$objContacts->fields['customer_name']),
                                'ROW_CLASS'               => $row = ($row == 'row2') ? "row1" : "row2",
                        ));
                        $this->_objTpl->parse("customerContacts");
                        $objContacts->MoveNext();
                    }
                }
            }            
            $this->_objTpl->setVariable('CRM_CONTACTS_HEADER_CLASS', (!empty ($customerContacts)) ? 'header-collapse' : 'header-expand');
            
            // parse currency
            $this->getCustomerCurrencyDropDown($this->_objTpl, $this->contact->currency, "currency");                        
        } else {
            $this->getCustomerTypeDropDown($this->_objTpl, $this->contact->customerType, "contactCustomerTypes");     // Customer Types
            $this->getCustomerCurrencyDropDown($this->_objTpl, $this->contact->currency, "contactCurrency");  // currency            
        }

        $memberships          = array_keys($this->getMemberships());
        $membershipBlock      = $contactType == 1 ? "assignedGroup" : "contactMembership";
        $this->getMembershipDropdown($this->_objTpl, $memberships, $membershipBlock, $assignedMembersShip);

        if (!empty($this->contact->account_id)) {
            $objUser = $objFWUser->objUser->getUser($this->contact->account_id);
        } else {
            $objUser = false;
        }

        $this->_objTpl->setVariable(array(            
            'CRM_ADDRESS_HEADER_CLASS'      => $showAddress ? 'header-collapse' : 'header-expand',
            'CRM_ADDRESS_BLOCK_DISPLAY'     => $showAddress ? 'table-row-group' : 'none',
            'CRM_DESCRIPTION_HEADER_CLASS'  => !empty($this->contact->notes) ? 'header-collapse' : 'header-expand',
            'CRM_DESCRIPTION_BLOCK_DISPLAY' => !empty($this->contact->notes) ? 'table-row-group' : 'none',
            
            'CRM_MEMBERSHIP_HEADER_CLASS'   => !empty($assignedMembersShip) ? 'header-collapse' : 'header-expand',
            'CRM_MEMBERSHIP_BLOCK_DISPLAY'  => !empty($assignedMembersShip) ? 'table-row-group' : 'none',
        ));
        

        $this->_objTpl->setGlobalVariable(array(
                'TXT_CON_FAMILY'            => contrexx_raw2xhtml($this->contact->family_name),
                'TXT_CON_ROLE'              => contrexx_raw2xhtml($this->contact->contact_role),
                'CRM_INPUT_COUNT'           => $Count,
                'CRM_CONTACT_COMPANY_ID'    => (int) $this->contact->contact_customer,
                'CRM_CONTACT_COMPANY'       => ($this->contact->contact_customer!=null) ? contrexx_raw2xhtml($objDatabase->getOne("SELECT `customer_name` FROM `".DBPREFIX."module_{$this->moduleName}_contacts` WHERE id = {$this->contact->contact_customer} ")) : '',
                'CRM_CONTACT_NOTES'         => contrexx_raw2xhtml($this->contact->notes),
                'CRM_INDUSTRY_DROPDOWN'     => $this->listIndustryTypes($this->_objTpl, 2, $this->contact->industryType),

                'CRM_CUSTOMERID'            => contrexx_input2xhtml($this->contact->customerId),
                'CRM_COMPANY_NAME'          => contrexx_input2xhtml($this->contact->customerName),                
                'CRM_CONTACT_ID'            => $this->contact->id != null ? $this->contact->id : 0,
                'CRM_CONTACT_USERNAME'      => $objUser ? contrexx_raw2xhtml($objUser->getEmail()) : '',
                'CRM_GENDER_FEMALE_SELECTED'=> $this->contact->contact_gender == 1 ? 'selected' : '',
                'CRM_GENDER_MALE_SELECTED'  => $this->contact->contact_gender == 2 ? 'selected' : '',
                'CRM_CONTACT_TYPE'          => ($contactType == 1) ? 'company' : 'contact',

                'TXT_CRM_CITY'              => $_ARRAYLANG['TXT_TITLE_CITY'],
                'TXT_CRM_STATE'             => $_ARRAYLANG['TXT_CRM_STATE'],
                'TXT_CRM_ZIP_CODE'          => $_ARRAYLANG['TXT_CRM_ZIP_CODE'],                
                'TXT_EDITCUSTOMERCONTACT_TITLE' => (isset($_REQUEST['id'])) ? $_ARRAYLANG["TXT_EDIT_".strtoupper($_GET['type'])] : $_ARRAYLANG["TXT_ADD_".strtoupper($_GET['type'])],
                'TXT_CRM_INDUSTRY_TYPE'     => $_ARRAYLANG['TXT_CRM_INDUSTRY_TYPE'],
                'TXT_CRM_DATASOURCE'        => $_ARRAYLANG['TXT_CRM_DATASOURCE'],
                'TXT_CRM_OPTION'            => $_ARRAYLANG['TXT_CRM_WORK'],
                'TXT_CRM_EMAIL_DEFAULT_OPTION'=>($contactType == 1) ? $_ARRAYLANG['TXT_CRM_HOME'] : $_ARRAYLANG['TXT_CRM_WORK'],
                'TXT_CRM_PROFILE_OPTION'    => ($contactType == 1) ? $_ARRAYLANG['TXT_CRM_BUSINESS1'] : $_ARRAYLANG['TXT_CRM_WORK'],
                'TXT_CRM_SOCIAL_PROFILE_OPTION' => $_ARRAYLANG['TXT_CRM_FACEBOOK'],
                'TXT_NAME'                  => $_ARRAYLANG['TXT_NAME'],
                'TXT_EMAIL'                 => $_ARRAYLANG['TXT_EMAIL'],
                'TXT_PHONE'                 => $_ARRAYLANG['TXT_PHONE'],
                'TXT_TITLE_LANGUAGE'        => $_ARRAYLANG['TXT_TITLE_LANGUAGE'],
                'TXT_ROLE'                  => $_ARRAYLANG['TXT_ROLE'],
                'TXT_FAMILY_NAME'           => $_ARRAYLANG['TXT_FAMILY_NAME'],
                'TXT_TITLE_SELECT_LANGUAGE' => $_ARRAYLANG['TXT_TITLE_SELECT_LANGUAGE'],
                'TXT_TITLE_MAIN_CONTACT'    => $_ARRAYLANG['TXT_TITLE_MAIN_CONTACT'],
                'TXT_CRM_HOME'              => $_ARRAYLANG['TXT_CRM_HOME'],
                'TXT_CRM_WORK'              => $_ARRAYLANG['TXT_CRM_WORK'],
                'TXT_CRM_BUSINESS1'         => $_ARRAYLANG['TXT_CRM_BUSINESS1'],
                'TXT_CRM_BUSINESS2'         => $_ARRAYLANG['TXT_CRM_BUSINESS2'],
                'TXT_CRM_BUSINESS3'         => $_ARRAYLANG['TXT_CRM_BUSINESS3'],
                'TXT_CRM_PRIVATE'           => $_ARRAYLANG['TXT_CRM_PRIVATE'],
                'TXT_CRM_OTHERS'            => $_ARRAYLANG['TXT_CRM_OTHERS'],
                'TXT_CRM_MOBILE'            => $_ARRAYLANG['TXT_CRM_MOBILE'],
                'TXT_CRM_FAX'               => $_ARRAYLANG['TXT_CRM_FAX'],
                'TXT_CRM_DIRECT'            => $_ARRAYLANG['TXT_CRM_DIRECT'],
                'TXT_CRM_DESCRIPTION'       => $_ARRAYLANG['TXT_DESCRIPTION'],
                'TXT_COMPANY_NAME'          => $_ARRAYLANG['TXT_TITLE_COMPANY_NAME'],
                'TXT_WEBSITE_SOCIAL_NETWORK' => $_ARRAYLANG['TXT_WEBSITE_SOCIAL_NETWORK'],
                'TXT_CRM_WEBSITE'           => $_ARRAYLANG['TXT_CRM_WEBSITE'],
                'TXT_CRM_SKYPE'             => $_ARRAYLANG['TXT_CRM_SKYPE'],
                'TXT_CRM_TWITTER'           => $_ARRAYLANG['TXT_CRM_TWITTER'],
                'TXT_CRM_LINKEDIN'          => $_ARRAYLANG['TXT_CRM_LINKEDIN'],
                'TXT_CRM_FACEBOOK'          => $_ARRAYLANG['TXT_CRM_FACEBOOK'],
                'TXT_CRM_LIVEJOURNAL'       => $_ARRAYLANG['TXT_CRM_LIVEJOURNAL'],
                'TXT_CRM_MYSPACE'           => $_ARRAYLANG['TXT_CRM_MYSPACE'],
                'TXT_CRM_GMAIL'             => $_ARRAYLANG['TXT_CRM_GMAIL'],
                'TXT_CRM_BLOGGER'           => $_ARRAYLANG['TXT_CRM_BLOGGER'],
                'TXT_CRM_YAHOO'             => $_ARRAYLANG['TXT_CRM_YAHOO'],
                'TXT_CRM_MSN'               => $_ARRAYLANG['TXT_CRM_MSN'],
                'TXT_CRM_ICQ'               => $_ARRAYLANG['TXT_CRM_ICQ'],
                'TXT_CRM_JABBER'            => $_ARRAYLANG['TXT_CRM_JABBER'],
                'TXT_CRM_AIM'               => $_ARRAYLANG['TXT_CRM_AIM'],
                'TXT_CRM_ADDRESS'           => $_ARRAYLANG['TXT_TITLE_ADDRESS'],
                'TXT_CRM_SELECT_COUNTRY'    => $_ARRAYLANG['TXT_CRM_SELECT_COUNTRY'],
                'TXT_OVERVIEW'              => $_ARRAYLANG['TXT_OVERVIEW'],                
                'TXT_ARE_YOU_SURE_DELETE_ENTRIES' => $_ARRAYLANG['TXT_ARE_YOU_SURE_DELETE_ENTRIES'],
                'TXT_ARE_YOU_SURE_DELETE_SELECTED_ENTRIES' => $_ARRAYLANG['TXT_ARE_YOU_SURE_DELETE_SELECTED_ENTRIES'],                
                'TXT_CRM_ACCOUNT_EMAIL'       => $_ARRAYLANG['TXT_CRM_ACCOUNT_EMAIL'],
                'TXT_CRM_ACCOUNT_PASSWORD'    => $_ARRAYLANG['TXT_CRM_ACCOUNT_PASSWORD'],
                'TXT_CRM_SEND_LOGIN_DETAILS'  => $_ARRAYLANG['TXT_CRM_SEND_LOGIN_DETAILS'],
                'TXT_CRM_CHOOSE_MEMBERSHIPS'  => $_ARRAYLANG['TXT_CRM_CHOOSE_MEMBERSHIPS'],
                
                'TXT_CRM_COMPANY_NAME'        =>    $_ARRAYLANG['TXT_TITLE_COMPANY_NAME'],
                'TXT_CRM_CUSTOMERTYPE'        =>    $_ARRAYLANG['TXT_TITLE_CUSTOMERTYPE'],
                'TXT_CRM_SOCIAL_NETWORK'      =>    $_ARRAYLANG['TXT_CRM_SOCIAL_NETWORK'],
                'TXT_CRM_GENDER'              =>    $_ARRAYLANG['TXT_CRM_GENDER'],
                'TXT_CRM_NOT_SPECIFIED'       =>    $_ARRAYLANG['TXT_CRM_NOT_SPECIFIED'],
                'TXT_CRM_GENDER_MALE'         =>    $_ARRAYLANG['TXT_CRM_GENDER_MALE'],
                'TXT_CRM_GENDER_FEMALE'       =>    $_ARRAYLANG['TXT_CRM_GENDER_FEMALE'],
                'TXT_CRM_CUSTOMERID'          =>    $_ARRAYLANG['TXT_TITLE_CUSTOMERID'],                
                'TXT_CRM_CURRENCY'            =>    $_ARRAYLANG['TXT_TITLE_CURRENCY'],
                'TXT_CRM_PLEASE_SELECT'       =>    $_ARRAYLANG['TXT_CRM_PLEASE_SELECT'],                
                'TXT_CRM_GENERAL_INFORMATION' =>    $_ARRAYLANG['TXT_CRM_GENERAL_INFORMATION'],
                'TXT_CRM_PROFILE_INFORMATION' =>    $_ARRAYLANG['TXT_CRM_PROFILE_INFORMATION'],
                'TXT_CRM_ALL_PERSONS'         =>    $_ARRAYLANG['TXT_CRM_ALL_PERSONS'],
                'TXT_CRM_ADD_CONTACT'         =>    $_ARRAYLANG['TXT_CRM_ADD_OR_LINK_CONTACT'],
                'TXT_CRM_ENTER_WEBSITE'       =>    $_ARRAYLANG['TXT_CRM_ENTER_WEBSITE'],
                'TXT_WEBSITE_NAME'            =>    $_ARRAYLANG['TXT_WEBSITE_NAME'],
                'TXT_FUNCTIONS'               =>    $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_CRM_SELECT_FROM_CONTACTS'=>    $_ARRAYLANG['TXT_CRM_SELECT_FROM_CONTACTS'],
                'TXT_CRM_NO_MATCHES'          =>    $_ARRAYLANG['TXT_CRM_NO_MATCHES'],
                'TXT_CRM_ADD_NEW'             =>    $_ARRAYLANG['TXT_CRM_ADD_NEW'],
                'TXT_CANCEL'                  =>    $_ARRAYLANG['TXT_CANCEL'],
                'TXT_CRM_WEBSITE'             =>    $_ARRAYLANG['TXT_CRM_WEBSITE'],
                'TXT_CRM_ADD_WEBSITE'         =>    $_ARRAYLANG['TXT_CRM_ADD_WEBSITE'],
                'TXT_CRM_PLEASE_SELECT'       =>    $_ARRAYLANG['TXT_CRM_PLEASE_SELECT'],
                'TXT_CRM_WEBSITES'            =>    $_ARRAYLANG['TXT_CRM_WEBSITES'],
                'BTN_SAVE'                    =>    $_ARRAYLANG['TXT_SAVE'],
                'TXT_CRM_ADD_NEW_CUSTOMER'    =>    $_ARRAYLANG['TXT_CRM_ADD_NEW_CUSTOMER'],
                'TXT_CRM_ADD_NEW_CONTACT'     =>    $_ARRAYLANG['TXT_CRM_ADD_NEW_CONTACT'],
                'TXT_CRM_PROFILE'             =>    $_ARRAYLANG['TXT_CRM_PROFILE'],
                'TXT_ADVANCED_OPTIONS'        =>    $_ARRAYLANG['TXT_ADVANCED_OPTIONS'],
                'TXT_CRM_MEMBERSHIP'          =>    $_ARRAYLANG['TXT_CRM_MEMBERSHIP'],
                'TXT_CRM_ADD_NEW_ACCOUNT'     =>    $_ARRAYLANG['TXT_CRM_ADD_NEW_ACCOUNT'],
                'TXT_CRM_FIND_CONTACT_BY_NAME'=>    $_ARRAYLANG['TXT_CRM_FIND_CONTACT_BY_NAME'],
                'TXT_CRM_FIND_COMPANY_BY_NAME'=>    $_ARRAYLANG['TXT_CRM_FIND_COMPANY_BY_NAME'],
                'TXT_CRM_SAVE_CONTACT'        =>    ($contactType == 2) ? $_ARRAYLANG['TXT_CRM_SAVE_PERSON'] : $_ARRAYLANG['TXT_CRM_SAVE_COMPANY'],
                'TXT_CRM_SAVE_AND_ADD_NEW_CONTACT'  => ($contactType == 2) ? $_ARRAYLANG['TXT_CRM_SAVE_AND_ADD_NEW_PERSON'] : $_ARRAYLANG['TXT_CRM_SAVE_AND_ADD_NEW_COMPANY'],
                'TXT_CRM_SELECT_CUSTOMER_WATERMARK' => $this->contact->customerName == null ? 'crm-watermark' : '',
                'COMPANY_MENU_ACTIVE'         => ($contactType == 1) ? 'active' : '',
                'CONTACT_MENU_ACTIVE'         => ($contactType == 2) ? 'active' : '',
                'CRM_REDIRECT_LINK'           => $redirect,
        ));
        if ($contactType == 2) {    // If contact type eq to `contact`            
            if ($settings['create_user_account']) {                
                $this->_objTpl->touchBlock("contactUserName");
                $this->_objTpl->touchBlock("contactPassword");
                if ($this->contact->id) {
                    $this->_objTpl->hideBlock("contactSendNotification");
                } else {
                    $this->_objTpl->touchBlock("contactSendNotification");
                }
            } else {
                $this->_objTpl->hideBlock("contactUserName");
                $this->_objTpl->hideBlock("contactPassword");
                $this->_objTpl->touchBlock("emptyContactUserName");
                $this->_objTpl->touchBlock("emptyContactPassword");
            }

            $this->_objTpl->parse("contactBlock");
            $this->_objTpl->hideBlock("customerBlock");
            $this->_objTpl->hideBlock("customerAdditionalBlock");
            $this->_objTpl->touchBlock("contactWebsiteOptions");
            $this->_objTpl->hideBlock("companyWebsiteOptions");
        } else {
            $this->_objTpl->parse("customerBlock");
            $this->_objTpl->parse("customerAdditionalBlock");            
            $this->_objTpl->hideBlock("contactBlock");
            $this->_objTpl->touchBlock("companyWebsiteOptions");
            $this->_objTpl->hideBlock("contactWebsiteOptions");
        }
    }

    /**
     * Returns the allowed maximum element per page. Can be used for paging.
     *
     * @global  array
     * @return  integer     allowed maximum of elements per page.
     */
    function getPagingLimit() {
        global $_CONFIG;
        return intval($_CONFIG['corePagingLimit']);
    }

    /**
     * Counts all existing entries in the database.
     *
     * @global  ADONewConnection
     * @return  integer     number of entries in the database
     */
    function countEntries($table, $where=null) {

        global $objDatabase;
        $objEntryResult = $objDatabase->Execute('SELECT  COUNT(*) AS numberOfEntries FROM '.DBPREFIX.'module_'.
                $table.$where);

        return intval($objEntryResult->fields['numberOfEntries']);
    }

    /**
     * Counts all existing entries in the database.
     *
     * @global  ADONewConnection
     * @return  integer     number of entries in the database
     */
    function countEntriesOfJoin($table) {
        global $objDatabase;

        $objEntryResult = $objDatabase->Execute('SELECT  COUNT(*) AS numberOfEntries
                                                    FROM    ('.$table.') AS num');

        return intval($objEntryResult->fields['numberOfEntries']);
    }

    /**
     * Default PM Calendar Month Page
     *
     * @access Authenticated
     */
    function parseLetterIndexList($URI, $paramName, $selectedLetter) {
        global $_CORELANG;

        if ($this->_objTpl->blockExists('module_'.$this->moduleName.'_letter_index_list')) {
            $arrLetters[]   = 48;
            $arrLetters     = array_merge($arrLetters, range(65, 90)); // ascii codes of characters "A" to "Z"
            $arrLetters[]   = '';

            foreach ($arrLetters as $letter) {
                switch ($letter) {
                    case 48:
                        $parsedLetter = '#';
                        break;
                    case '':
                        $parsedLetter = $_CORELANG['TXT_ACCESS_ALL'];
                        break;
                    default:
                        $parsedLetter = chr($letter);
                        break;
                }

                if ($letter == '' && $selectedLetter == '' || chr($letter) == $selectedLetter) {
                    $parsedLetter = '<strong>'.$parsedLetter.'</strong>';
                }

                $this->_objTpl->setVariable(array(
                        'ACCESS_USER_LETTER_INDEX_URI'      => $URI.(!empty($letter) ? '&amp;'.$paramName.'='.chr($letter) : null),
                        'ACCESS_USER_LETTER_INDEX_LETTER'   => $parsedLetter
                ));

                $this->_objTpl->parse('module_'.$this->moduleName.'_letter_index_list');
            }
        }
    }

    function changeActive($id, $value) {
        global $_ARRAYLANG,$objDatabase;

        $value = ($value == 1) ? 0 : 1;
        $updateQuery = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_contacts SET status = '.$value.'
                        WHERE    id='.$id;
        $objDatabase->Execute($updateQuery);
        $_SESSION['strOkMessage'] = ($value == 1) ? $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
    }

    function _modifyNotes() {
        global $objDatabase, $_ARRAYLANG;

        JS::activate('jqueryui');
        JS::registerJS('lib/javascript/crm/main.js');
        JS::registerCSS('lib/javascript/crm/css/main.css');
        $objFWUser  = FWUser::getFWUserObject();
        
        $id             = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $customerId     = isset($_GET['cust_id']) ? (int) $_GET['cust_id'] : 0;
        $noteTypeId     = isset($_POST['notes_type']) ? (int) $_POST['notes_type'] : 0;
        $noteDate       = isset($_POST['date']) ? contrexx_input2raw($_POST['date']) : date('Y-m-d');
        
        $description    = html_entity_decode($_POST['customer_comment'], ENT_QUOTES, CONTREXX_CHARSET);
        $description    = $this->strip_only_tags($description, '<script><iframe>', $stripContent=false);
        $description    = htmlspecialchars($description);
        
        $userid     = $objFWUser->objUser->getId();

        $this->_pageTitle       = ($id) ? $_ARRAYLANG['TXT_CRM_NOTES_EDIT'] : $_ARRAYLANG['TXT_CRM_NOTES_ADD'];
        
        $objTpl     = $this->_objTpl;        
        $objTpl->loadTemplateFile("module_{$this->moduleName}_comments_modify.html");
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName ,
        ));
        
        if ($customerId) {

            if (isset($_POST['comment_submit'])) {
                if (!empty($noteTypeId) && !empty($customerId)) {
                    $fields = array(
                        'customer_id'   => (int) $customerId,
                        'notes_type_id' => (int) $noteTypeId,
                        'comment'       => $description,
                        'user_id'       => (int) $userid,
                        'date'          => $noteDate
                    );
                    if ($id) {
                        $sql = SQL::update("module_{$this->moduleName}_customer_comment", $fields, array('escape' => false))." WHERE id = $id";
                    } else {
                        $fields['added_date'] = date('Y-m-d H:i:s');
                        $sql = SQL::insert("module_{$this->moduleName}_customer_comment", $fields, array('escape' => false));
                    }

                    $db = $objDatabase->Execute($sql);
                    if ($db) {
                        $msg = ($id) ? base64_encode("commentEdited") : base64_encode("commentAdded");
                        CSRF::header("Location:".ASCMS_ADMIN_WEB_PATH."/index.php?cmd={$this->moduleName}&act=showcustdetail&mes=$msg&id=$customerId");
                    } else {
                        $this->_strErrMessage = "Some thing went wrong";
                    }
                } else {
                    $this->_strErrMessage = "Some thing went wrong";
                }
            } elseif ($id) {                
                $objResult = $objDatabase->Execute("SELECT notes_type_id,
                                                           comment,
                                                           c.date,
                                                           n.name as notes_type
                                                     FROM `".DBPREFIX."module_{$this->moduleName}_customer_comment` AS c
                                                       LEFT JOIN ".DBPREFIX."module_".$this->moduleName."_notes AS n
                                                         ON c.notes_type_id = n.id
                                                     WHERE c.id = $id AND customer_id = $customerId");
                if ($objResult) {
                    $noteTypeId  = $objResult->fields['notes_type_id'];
                    $description = $objResult->fields['comment'];
                    $noteType    = $objResult->fields['notes_type'];
                    $noteDate    = $objResult->fields['date'];
                }
            }
            
            $objResult = $objDatabase->Execute("SELECT id,name FROM ".DBPREFIX."module_".$this->moduleName."_notes WHERE status=1 ORDER BY pos");
            if ($objResult) {
                while(!$objResult->EOF) {
                    $this->_objTpl->setVariable(array(
                            'CRM_NOTE_TYPE_ID'  => (int) $objResult->fields['id'],
                            'CRM_NOTE_TYPE'     => contrexx_raw2xhtml($objResult->fields['name'])
                    ));
                    $this->_objTpl->parse('NoteType');
                    $objResult->MoveNext();
                }
            }
            
            $objTpl->setVariable(array(
                'CRM_NOTES_TYPE_ID'         => (int) $noteTypeId,
                'CRM_NOTES_TYPE'            => ($noteTypeId) ? contrexx_raw2xhtml($noteType) : $_ARRAYLANG['TXT_TASK_SELECTNOTES'],
                'CRM_NOTES_DATE'            => contrexx_raw2xhtml($noteDate),
                'CRM_CUSTOMER_ID'           => $customerId,
                'CRM_COMMENT_DESCRIPTION'   =>  new \Cx\Core\Wysiwyg\Wysiwyg('customer_comment', html_entity_decode($description, ENT_QUOTES, CONTREXX_CHARSET),'pm_fullpage')
            ));
        } else {
            $this->_strErrMessage = "Customer should not be empty";
        }
        
        $objTpl->setVariable(array(
            'TXT_CRM_NOTES_TITLE'       => ($id) ? $_ARRAYLANG['TXT_CRM_NOTES_EDIT'] : $_ARRAYLANG['TXT_CRM_NOTES_ADD'],
            'TXT_COMMENT_DESCRIPTION'   => $_ARRAYLANG['TXT_COMMENT_DESCRIPTION'],
            'TXT_OVERVIEW_NOTESTYPE'    => $_ARRAYLANG['TXT_OVERVIEW_NOTESTYPE'],
            'TXT_CRM_CUSTOMER_OVERVIEW' => $_ARRAYLANG['TXT_CUSTOMER_OVERVIEW'],
            'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
            'TXT_CRM_DUE_DATE'          => $_ARRAYLANG['TXT_DUE_DATE'],
        ));
    }

    /**
     * Delete the Comment single
     *
     * @access Authenticated
     */
    function deleteCustomerComment() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $id = $_REQUEST['commentid'];
        $customerId = $_REQUEST['id'];

        if (!empty($id)) {

            $deleteQuery = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_customer_comment` WHERE id = '.$id;
            $objDatabase->Execute($deleteQuery);
            $msg = base64_encode("CommentDelete");
            csrf::header("Location:".ASCMS_ADMIN_WEB_PATH."/index.php?cmd=".$this->moduleName."&act=showcustdetail&mes=$msg&id=$customerId");
        }
        die();
    }

    function showInterface() {
        global $_ARRAYLANG;
        
        $tpl = isset($_GET['tpl']) ? $_GET['tpl'] : '';        
        $_SESSION['pageTitle'] = $_ARRAYLANG['TXT_INTERFACE'];
        
        $this->crmInterfaceController = $this->load->controller('crmInterface', __CLASS__, & $this->_objTpl);

        switch ($tpl) {
            case 'export':
                $this->crmInterfaceController->showExport();
                break;
            case 'exportcsv':
                $this->crmInterfaceController->csvExport();
                break;
            case 'importCsv';
                $this->crmInterfaceController->csvImport();
                break;
            case 'importoptions':
                $this->crmInterfaceController->getImportOptions();
                break;
            case 'import':
            default:
                $this->crmInterfaceController->showImport();
                break;
        }

        return ;
                
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_interface_entries.html');
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));
        // Passing javascript functions to template.
        $this->_objTpl->setVariable(array(
                //  'INTERFACE_JAVASCRIPT'    =>    $this->getInterfaceJavascript(),
        ));
        // Passing the place holders to the template page
        $this->_objTpl->setVariable(array(
                'TXT_EXPORT_NAME'                   => $_ARRAYLANG['TXT_EXPORT_NAME'],
                'TXT_IMPORT_NAME'                   => $_ARRAYLANG['TXT_IMPORT_NAME'],
                'TXT_EXPORT_INFO'                 => $_ARRAYLANG['TXT_EXPORT_INFO'],
                'TXT_FUNCTIONS'               => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_EXPORT_CUSTOMER_CSV'     => $_ARRAYLANG['TXT_EXPORT_CUSTOMER_CSV'],
                'TXT_IMPORT_PAGE_HEADING'     => $_ARRAYLANG['TXT_IMPORT_PAGE_HEADING'],
                'TXT_IMPORT_FILE_TYPE'     => $_ARRAYLANG['TXT_IMPORT_FILE_TYPE'],
                'TXT_IMPORT_CSV_TXT'     => $_ARRAYLANG['TXT_IMPORT_CSV_TXT'],
                'TXT_CHOOSE_FILE'     => $_ARRAYLANG['TXT_CHOOSE_FILE'],
                'TXT_CSV_SEPARATOR'     => $_ARRAYLANG['TXT_CSV_SEPARATOR'],
                'TXT_CSV_ENCLOSURE'     => $_ARRAYLANG['TXT_CSV_ENCLOSURE'],
                'TXT_CSV_TABLE'     => $_ARRAYLANG['TXT_CSV_TABLE'],
                'TXT_CSV_CHOOSE_TABLE'     => $_ARRAYLANG['TXT_CSV_CHOOSE_TABLE'],
                'TXT_CUSTOMERS'     => $_ARRAYLANG['TXT_CUSTOMERS'],
                'INTERFACE_JAVASCRIPT'    =>    $objJs->getInterfaceJavascript(),
        ));
    }


    function customerImport() {

        global $_CORELANG, $_ARRAYLANG, $objDatabase,$objJs;
        $this->_pageTitle = $_ARRAYLANG['TXT_INTERFACE'];
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_interface_import_analyse.html');
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));
        $separator = $_REQUEST['import_options_csv_separator'];
        $enclosure = $_REQUEST['import_options_csv_enclosure'];
        // Passing javascript functions to template.
        $this->_objTpl->setVariable(array(
                //  'IMPORT_ANALYSIS_JAVASCRIPT'    =>    $this->getImportAnalysisJavascript(),
        ));
        // Here uploading the CSV file
        $target_path = ASCMS_DOCUMENT_ROOT."/csvupload/";

        $target_path = $target_path . basename( $_FILES['importfile']['name']);
        $_SESSION['fileName'] = $target_path;
        $_SESSION['separator'] = $separator;
        $_SESSION['enclosure'] = $enclosure;
        if(!move_uploaded_file($_FILES['importfile']['tmp_name'], $target_path)) {

            $this->_strErrMessage = $_ARRAYLANG['TXT_CSV_UPLOAD_ERR'];
            // Calling the Interface Page with Upload error Message
            $this->showInterface();
        }
        else {
            // DB Field Name for the Cutomer Tablel
            $DBFiledName=$this->objCSVimport->CustomerDBField();
            // Geting the Filed Names from the File
            $FileFields = $this->objCSVimport->getFilefieldMenuOptions($target_path,$separator,$enclosure);

            // Passing the place holders to the template page
            $this->_objTpl->setVariable(array(
                    'TXT_EXPORT_NAME'                   => $_ARRAYLANG['TXT_EXPORT_NAME'],
                    'TXT_IMPORT_NAME'                   => $_ARRAYLANG['TXT_IMPORT_NAME'],
                    'TXT_EXPORT_INFO'                 => $_ARRAYLANG['TXT_EXPORT_INFO'],
                    'TXT_FUNCTIONS'               => $_ARRAYLANG['TXT_FUNCTIONS'],
                    'TXT_EXPORT_CUSTOMER_CSV'     => $_ARRAYLANG['TXT_EXPORT_CUSTOMER_CSV'],
                    'TXT_IMPORT_PAGE_HEADING'     => $_ARRAYLANG['TXT_IMPORT_PAGE_HEADING'],
                    'TXT_IMPORT_FILE_TYPE'     => $_ARRAYLANG['TXT_IMPORT_FILE_TYPE'],
                    'TXT_IMPORT_CSV_TXT'     => $_ARRAYLANG['TXT_IMPORT_CSV_TXT'],
                    'TXT_CHOOSE_FILE'     => $_ARRAYLANG['TXT_CHOOSE_FILE'],
                    'TXT_CSV_SEPARATOR'     => $_ARRAYLANG['TXT_CSV_SEPARATOR'],
                    'TXT_CSV_ENCLOSURE'     => $_ARRAYLANG['TXT_CSV_ENCLOSURE'],
                    'TXT_CSV_TABLE'     => $_ARRAYLANG['TXT_CSV_TABLE'],
                    'TXT_CSV_CHOOSE_TABLE'     => $_ARRAYLANG['TXT_CSV_CHOOSE_TABLE'],
                    'TXT_CUSTOMERS'     => $_ARRAYLANG['TXT_CUSTOMERS'],
                    'TXT_IMPORT_ANALYSIS'     => $_ARRAYLANG['TXT_IMPORT_ANALYSIS'],
                    'TXT_CUSTOMER_TYPE'     => $_ARRAYLANG['TXT_CUSTOMER_TYPE'],
                    'TXT_CSV_TXTFILE'     => $_ARRAYLANG['TXT_CSV_TXTFILE'],
                    'TXT_CSV_DATABASE'     => $_ARRAYLANG['TXT_CSV_DATABASE'],
                    'TXT_CSV_NOTE'     => $_ARRAYLANG['TXT_CSV_NOTE'],
                    'TXT_CSV_NOTE_TXT'     => $_ARRAYLANG['TXT_CSV_NOTE_TXT'],
                    'TXT_CSV_REMOVE_PAIR'     => $_ARRAYLANG['TXT_CSV_REMOVE_PAIR'],
                    'TXT_CSV_PARENTID'     => $_ARRAYLANG['TXT_CSV_PARENTID'],
                    'TXT_CSV_LANGUAGE'     => $_ARRAYLANG['TXT_CSV_LANGUAGE'],
                    'TXT_CSV_CURRENCY'     => $_ARRAYLANG['TXT_CSV_CURRENCY'],
                    'TXT_CSV_COUNTRY'     =>$_ARRAYLANG['TXT_CSV_COUNTRY'],
                    'TXT_SAVE'     => $_ARRAYLANG['TXT_SAVE'],
                    'TXT_CSV_NEXT'     => $_ARRAYLANG['TXT_CSV_NEXT'],
                    'TXT_CSV_ADDPAIR'     => $_ARRAYLANG['TXT_CSV_ADDPAIR'],
                    'TXT_CSV_REMOVEPAIR'     => $_ARRAYLANG['TXT_CSV_REMOVEPAIR'],
                    'TXT_DBFIELD_NAME' => $DBFiledName,
                    'TXT_FILEFIELD_NAME' => $FileFields,
                    'IMPORT_ANALYSIS_JAVASCRIPT'    =>    $objJs->getImportAnalysisJavascript(),
            ));
        }// END of File upload else Condition
    }

    function finalImport() {

        global $_CORELANG, $_ARRAYLANG, $objDatabase,$objJs;
        $this->_pageTitle = $_ARRAYLANG['TXT_INTERFACE'];
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_interface_import_custfinal.html');
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));
        // Passing javascript functions to template.
        $this->_objTpl->setVariable(array(
                //'IMPORT_FINAL_JAVASCRIPT'    =>    $this->getFinalImportJavascript(),
        ));
        // File Information getting from session variables
        $target_path = $_SESSION['fileName'];
        $separator = $_SESSION['separator'];
        $enclosure = $_SESSION['enclosure'];
        // Paired Field values
        $PairLeft = $_REQUEST['pairs_left_keys'];
        $PairRight = $_REQUEST['pairs_right_keys'];
        $_SESSION['PairLeft'] = $PairLeft;
        $_SESSION['PairRight'] = $PairRight;
        // Getting the Customer type, Parent ID, Currency, Language
        $CustType = $_REQUEST['CustType'];
        $Country = $_REQUEST['Country'];
        $Lang = $_REQUEST['Lang'];
        $Currency = $_REQUEST['Currency'];
        $_SESSION['CustType'] = $CustType;
        $_SESSION['Country'] = $Country;
        $_SESSION['Lang'] = $Lang;
        $_SESSION['Currency'] = $Currency;

        // Selecting the Customer Type label from the Database
        $objCustTypeResult =   $objDatabase->Execute('SELECT  id,label FROM '.DBPREFIX.'module_'.$this->moduleName.'_customer_types ORDER BY pos' );
        while(!$objCustTypeResult->EOF) {

            $DBCustType.='<option value="'.$objCustTypeResult->fields['id'].'">'.$objCustTypeResult->fields['label'].'</option>';
            $objCustTypeResult->MoveNext();
        }

        // Selecting the Country Name from the Database
        $objCustNameResult =   $objDatabase->Execute('SELECT  iso_code_2,id,name FROM '.DBPREFIX.'lib_country ORDER BY id' );

        while(!$objCustNameResult->EOF) {

            $DBCustName.='<option value="'.$objCustNameResult->fields['iso_code_2'].'">'.$objCustNameResult->fields['name'].'</option>';
            $objCustNameResult->MoveNext();
        }

        // Selecting the Language from the Database
        $objLangResult =   $objDatabase->Execute('SELECT  id,name FROM '.DBPREFIX.'languages ORDER BY id' );
        while(!$objLangResult->EOF) {

            $DBLang.='<option value="'.$objLangResult->fields['id'].'">'.$objLangResult->fields['name'].'</option>';
            $objLangResult->MoveNext();
        }

        // Selecting the Currency from the Database
        $objCurrencyResult =   $objDatabase->Execute('SELECT  id,name FROM '.DBPREFIX.'module_'.$this->moduleName.'_currency ORDER BY id' );
        while(!$objCurrencyResult->EOF) {

            $DBCurrency.='<option value="'.$objCurrencyResult->fields['id'].'">'.$objCurrencyResult->fields['name'].'</option>';
            $objCurrencyResult->MoveNext();
        }

        // Reading the file content
        $arrFileContent = $this->objCSVimport->GetFileContent($target_path,$separator,$enclosure);


        // Getting the Customer Type from the CSV file
        for($i=1;$i<count($arrFileContent);$i++) {

            $ArrTypeField[]= $arrFileContent[$i][$CustType];

        }
        $CustomerType = array_unique($ArrTypeField);
        foreach($CustomerType as $value) {
            $FileCustType.='<option value="'.$value.'">'.$value.'</option>';
        }

        // Getting the Parent Name from the CSV file
        for($i=1;$i<count($arrFileContent);$i++) {
            $ArrParentField[]= $arrFileContent[$i][$Country];
        }
        $Country= array_unique($ArrParentField);
        //var_dump($Country);
        foreach($Country as $value) {
            $FileParent.='<option value="'.$value.'">'.$value.'</option>';
        }
        // var_dump($FileParent);
        // Getting the Language from the CSV file
        for($i=1;$i<count($arrFileContent);$i++) {
            $ArrLangField[]= $arrFileContent[$i][$Lang];
        }
        $LangField = array_unique($ArrLangField);
        foreach($LangField as $value) {
            $FileLang.='<option value="'.$value.'">'.$value.'</option>';
        }

        // Getting the Currency from the CSV file
        for($i=1;$i<count($arrFileContent);$i++) {
            $ArrCurrField[]= $arrFileContent[$i][$Currency];
        }
        $CurrField = array_unique($ArrCurrField);
        foreach($CurrField as $value) {
            $FileCurr.='<option value="'.$value.'">'.$value.'</option>';
        }

        // Passing the place holders to the template page
        $this->_objTpl->setVariable(array(
                'TXT_EXPORT_NAME'                   => $_ARRAYLANG['TXT_EXPORT_NAME'],
                'TXT_IMPORT_NAME'                   => $_ARRAYLANG['TXT_IMPORT_NAME'],
                'TXT_EXPORT_INFO'                 => $_ARRAYLANG['TXT_EXPORT_INFO'],
                'TXT_FUNCTIONS'               => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_EXPORT_CUSTOMER_CSV'     => $_ARRAYLANG['TXT_EXPORT_CUSTOMER_CSV'],
                'TXT_IMPORT_PAGE_HEADING'     => $_ARRAYLANG['TXT_IMPORT_PAGE_HEADING'],
                'TXT_IMPORT_FILE_TYPE'     => $_ARRAYLANG['TXT_IMPORT_FILE_TYPE'],
                'TXT_IMPORT_CSV_TXT'     => $_ARRAYLANG['TXT_IMPORT_CSV_TXT'],
                'TXT_CHOOSE_FILE'     => $_ARRAYLANG['TXT_CHOOSE_FILE'],
                'TXT_CSV_SEPARATOR'     => $_ARRAYLANG['TXT_CSV_SEPARATOR'],
                'TXT_CSV_ENCLOSURE'     => $_ARRAYLANG['TXT_CSV_ENCLOSURE'],
                'TXT_CSV_TABLE'     => $_ARRAYLANG['TXT_CSV_TABLE'],
                'TXT_CSV_CHOOSE_TABLE'     => $_ARRAYLANG['TXT_CSV_CHOOSE_TABLE'],
                'TXT_CUSTOMERS'     => $_ARRAYLANG['TXT_CUSTOMERS'],
                'TXT_IMPORT_ANALYSIS'     => $_ARRAYLANG['TXT_IMPORT_ANALYSIS'],
                'TXT_CUSTOMER_TYPE'     => $_ARRAYLANG['TXT_CUSTOMER_TYPE'],
                'TXT_CSV_TXTFILE'     => $_ARRAYLANG['TXT_CSV_TXTFILE'],
                'TXT_CSV_DATABASE'     => $_ARRAYLANG['TXT_CSV_DATABASE'],
                'TXT_CSV_NOTE'     => $_ARRAYLANG['TXT_CSV_NOTE'],
                'TXT_CSV_NOTE_TXT'     => $_ARRAYLANG['TXT_CSV_NOTE_TXT'],
                'TXT_CSV_REMOVE_PAIR'     => $_ARRAYLANG['TXT_CSV_REMOVE_PAIR'],
                'TXT_CSV_PARENTID'     => $_ARRAYLANG['TXT_CSV_PARENTID'],
                'TXT_CSV_LANGUAGE'     => $_ARRAYLANG['TXT_CSV_LANGUAGE'],
                'TXT_CSV_CURRENCY'     => $_ARRAYLANG['TXT_CSV_CURRENCY'],
                'TXT_SAVE'     => $_ARRAYLANG['TXT_SAVE'],
                'TXT_CSV_ADDPAIR'     => $_ARRAYLANG['TXT_CSV_ADDPAIR'],
                'TXT_CSV_REMOVEPAIR'     => $_ARRAYLANG['TXT_CSV_REMOVEPAIR'],
                'TXT_DBCUSTOMERS_TYPE'     => $DBCustType,
                'TXT_DBCUSTOMERS_NAME'     => $DBCustName,
                'TXT_DBLANG_NAME'     => $DBLang,
                'TXT_DBCURRENCY_NAME'     => $DBCurrency,
                'TXT_FILECUSTOMER_TYPE'     => $FileCustType,
                'TXT_FILEPARENT_ID'     => $FileParent,
                'TXT_FILELANG_NAME'     => $FileLang,
                'TXT_FILECURRENCY_NAME'     => $FileCurr,
                'IMPORT_FINAL_JAVASCRIPT'    =>    $objJs->getFinalImportJavascript(),
        ));
    }

    function InsertCSV() {

        global $_CORELANG, $_ARRAYLANG, $objDatabase;

        // File Information getting from session variables
        if(!empty($_SESSION['fileName'])) {
            $target_path = $_SESSION['fileName'];
            $separator = $_SESSION['separator'];
            $enclosure = $_SESSION['enclosure'];

            // Paired Field values
            $PairLeft = $_SESSION['PairLeft'];
            $PairRight = $_SESSION['PairRight'];
            $Arr_pair_file = explode(';',$PairLeft);
            $Arr_pair_db = explode(';',$PairRight);

            $InsertField = "";
            foreach($Arr_pair_db as $value) {
                if(!empty($InsertField)) {
                    $InsertField .=",";
                }
                $InsertField .= $value;
            }

            // Getting the Customer type, Parent ID, Currency, Language
            $CustType = $_SESSION['CustType'];
            $Country = $_SESSION['Country'];
            $Lang = $_SESSION['Lang'];
            $Currency = $_SESSION['Currency'];

            $Cust_Type_File = $_REQUEST['pairs_left_keys'];
            $Cust_Type_DB = $_REQUEST['pairs_right_keys'];
            $Arr_custtype_file = explode(';',$Cust_Type_File);
            $Arr_custtype_db = explode(';',$Cust_Type_DB);

            $Parent_Id_File = $_REQUEST['pairs_left_keys2'];
            $Parent_Id_DB = $_REQUEST['pairs_right_keys2'];
            $Arr_parentid_file = explode(';',$Parent_Id_File);
            $Arr_parentid_db = explode(';',$Parent_Id_DB);

            $Lang_File = $_REQUEST['pairs_left_keys3'];
            $Lang_DB = $_REQUEST['pairs_right_keys3'];
            $Arr_lang_file = explode(';',$Lang_File);
            $Arr_lang_db = explode(';',$Lang_DB);

            $Currency_File = $_REQUEST['pairs_left_keys4'];
            $Currency_DB = $_REQUEST['pairs_right_keys4'];
            $Arr_currency_file = explode(';',$Currency_File);
            $Arr_currency_db = explode(';',$Currency_DB);

            // Reading the file content
            $arrFileContent = $this->objCSVimport->GetFileContent($target_path,$separator,$enclosure);

            // Getting the Customer Type from the CSV file
            for($i=1;$i<count($arrFileContent);$i++) {
                for($j=0;$j<count($Arr_custtype_file);$j++) {
                    if($arrFileContent[$i][$CustType] == $Arr_custtype_file[$j]) {
                        $arrFileContent[$i][$CustType] = $Arr_custtype_db[$j];
                    }
                }
            }

            // Getting the country from the CSV file
            for($i=1;$i<count($arrFileContent);$i++) {
                for($j=0;$j<count($Arr_parentid_file);$j++) {
                    if($arrFileContent[$i][$Country] == $Arr_parentid_file[$j]) {
                        $arrFileContent[$i][$Country] = $Arr_parentid_db[$j];
                    }
                }
            }

            // Getting the Lang Id from the CSV file
            for($i=1;$i<count($arrFileContent);$i++) {
                for($j=0;$j<count($Arr_lang_file);$j++) {
                    if($arrFileContent[$i][$Lang] == contrexx_stripslashes($Arr_lang_file[$j])) {
                        $arrFileContent[$i][$Lang] = $Arr_lang_db[$j];
                    }
                }
            }
            // Getting the Currency Id from the CSV file
            for($i=1;$i<count($arrFileContent);$i++) {
                for($j=0;$j<count($Arr_currency_file);$j++) {
                    if($arrFileContent[$i][$Currency] == contrexx_stripslashes($Arr_currency_file[$j])) {
                        $arrFileContent[$i][$Currency] = $Arr_currency_db[$j];
                    }
                }
            }
            for($i=1;$i<count($arrFileContent);$i++) {
                $ResultRow = "";
                foreach($Arr_pair_file as $value) {
                    if(!empty($ResultRow)) {
                        $ResultRow .=",";
                    }
                    $ResultRow .='"'.$arrFileContent[$i][$value].'"';
                }
                $InsertRowValue[] = $ResultRow;
            }

            for($i=0;$i<count($InsertRowValue);$i++) {
                $Result = explode(',',$InsertRowValue[$i]);
                $FinalResultcontact = array();
                $FinalResultCustomer = array();
                for($j=0;$j<count($Arr_pair_file);$j++) {

                    $contact=array('e_mail','Name','language_id');

                    if(in_array($Arr_pair_db[$j],$contact)) {
                        $FinalResultcontact[]='`'.$Arr_pair_db[$j].'` = '.$Result[$j];

                    } else {
                        $FinalResultCustomer[]= '`'.$Arr_pair_db[$j].'` = '.$Result[$j];
                    }
                }

                $FinalInsertStr[$i]['customer'] = $FinalResultCustomer;
                $FinalInsertStr[$i]['contact'] = $FinalResultcontact;

            }
            foreach ($FinalInsertStr as $value) {
                $customer =implode(',',$value['customer']);
                $contact =implode(',',$value['contact']);
                $objFWUser = FWUser::getFWUserObject();
                $userid = $objFWUser->objUser->getId();
                $query    = 'INSERT INTO `'.DBPREFIX.'module_'.$this->moduleName.'_customers` SET `addedby`='.$userid.',`is_active`=1 ,'.$customer;

                $objResult  =   $objDatabase->Execute($query);
                $customerid = $objDatabase->INSERT_ID();
                if (!empty($customerid)) {
                    $query    = 'INSERT INTO `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contacts` SET `customer_id`='.$customerid.',`main_contact`=1 , '.$contact;
                    $objResult  =   $objDatabase->Execute($query);


                }
                $this->_strOkMessage = "Records are inserted Successfully";
            }
        }//IF $_POST['frmSelectFields']
        $_SESSION['fileName'] = '';
        $_SESSION['separator'] = '';
        $_SESSION['enclosure'] = '';
        $_SESSION['PairLeft'] = '';
        $_SESSION['PairRight'] = '';
        $_SESSION['CustType'] = '';
        $_SESSION['ParentId'] = '';
        $_SESSION['Lang'] = '';
        $_SESSION['Currency'] = '';
        $this->showInterface();
    }

    function Notesoverview() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase, $objJs;

        $this->_objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_notes.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));

        if (!isset($_GET['message'])) {
            $_GET['message']='';
        }
        switch ($_GET['message']) {
            case 'updatenotes' :
                $this->_strOkMessage = $_ARRAYLANG['TXT_NOTES_UPDATED'];
                break;
        }

        $name       = isset($_POST['name'])? contrexx_input2db($_POST['name']):'';
        $status     = isset($_POST['status'])? intval($_POST['status']):'';
        $position   = isset($_POST['sorting'])? intval($_POST['sorting']):'';
        $id         = isset($_GET['idr'])? intval($_GET['idr']):'';

        if(isset($_GET['idr'])) {
            $objComment = $objDatabase->Execute("SELECT notes_type_id FROM ".DBPREFIX."module_".$this->moduleName."_customer_comment WHERE notes_type_id = '$id'");
            if($objComment->fields['notes_type_id'] != $id) {
                $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleName."_notes WHERE id = '$id'");
                $this->_strOkMessage = $_ARRAYLANG['TXT_NOTES_DELETED'];
            }else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_NOTES_ERROR'];
            }
        }

        if (isset($_GET['chg']) and $_GET['chg'] == 1 and isset($_POST['selected']) and is_array($_POST['selected'])) {
            if ($_POST['form_activate'] != '' or $_POST['form_deactivate'] != '') {
                $ids = $_POST['selected'];
                $to  = $_POST['form_activate'] ? 1 : 0;
                foreach($ids as $id) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_notes
                                                                   SET   status  = '".$to."'
                                                                   WHERE system_defined != 1 AND  id      = '".intval($id)."'";
                    $objDatabase->SelectLimit($query, 1);
                }
                $this->_strOkMessage = ($to == 1) ? $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
            }
            if ($_POST['form_delete'] != '') {
                $ids = $_POST['selected'];
                $x   = 0;
                foreach($ids as $id) {
                    $objComment = $objDatabase->Execute("SELECT notes_type_id FROM ".DBPREFIX."module_".$this->moduleName."_customer_comment WHERE notes_type_id = '$id'");
                    if($objComment->fields['notes_type_id'] != $id) {
                        $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleName."_notes
                                                                       WHERE system_defined != 1 AND id = '".intval($id)."'";
                        $objDatabase->SelectLimit($query, 1);
                        $this->_strOkMessage = $_ARRAYLANG['TXT_NOTES_DELETED'];
                    }else {
                        $this->_strErrMessage = $_ARRAYLANG['TXT_NOTES_ERROR'];
                    }
                }
//            $this->_strOkMessage = ($_POST['form_delete'] == 1) ? $_ARRAYLANG['TXT_NOTES_DELETED'] : '';
            }
        }
        if (isset($_GET['chg']) and $_GET['chg'] == 1 and $_POST['form_sort'] == 1) {
            for ($x = 0; $x < count($_POST['form_id']); $x++) {
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_notes
                                                  SET   pos   = '".intval($_POST['form_pos'][$x])."'
                                                  WHERE id    = '".intval($_POST['form_id'][$x])."'";
                $objDatabase->Execute($query);
            }
            $this->_strOkMessage = ($_POST['form_sort'] == 1) ? $_ARRAYLANG['TXT_SORTING_COMPLETE'] : '';
        }
        if(isset($_POST['save'])) {
            $validate = $this->validation($name);
            if($validate) {
                $objResult = $objDatabase->Execute("INSERT ".DBPREFIX."module_".$this->moduleName."_notes SET name   ='$name',
                                                                                                                  status = '$status',
                                                                                                                  pos    = '$position'");
                $this->_strOkMessage = $_ARRAYLANG['TXT_NOTES_INSERTED'];
            }else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_ERROR'];
            }
        }
        if(isset($_POST['notes_save'])) {
            for ($x = 0; $x < count($_POST['form_id']); $x++) {
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_notes
                                           SET   pos   = '".intval($_POST['form_pos'][$x])."'
                                           WHERE id    = '".intval($_POST['form_id'][$x])."'";
                $objDatabase->Execute($query);
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_SORTING_COMPLETE'];
        }

        $sortf = isset($_GET['sortf']) && isset($_GET['sorto']) ? (($_GET['sortf'] == 1)? 'pos':'name') : 'pos';
        $sorto = isset($_GET['sortf']) && isset($_GET['sorto']) ? (($_GET['sorto'] == 'ASC') ? 'DESC' : 'ASC') : 'ASC';
        $objResult = $objDatabase->Execute("SELECT id,name,status,pos, system_defined FROM ".DBPREFIX."module_".$this->moduleName."_notes ORDER BY $sortf $sorto");

        $row = 'row2';
        while(!$objResult->EOF) {
            $stat = $objResult->fields['status'];
            
            if ($objResult->fields['system_defined']) {
                $this->_objTpl->hideBlock('noteDeleteIcon');
            } else {
                $this->_objTpl->touchBlock('noteDeleteIcon');
            }
            
            $this->_objTpl->setVariable(array(
                    'TXT_NOTES_ID'      => (int) $objResult->fields['id'],
                    'TXT_NOTES_NAME'    => contrexx_raw2xhtml($objResult->fields['name']),
                    'TXT_NOTES_STATVAL' => $stat,
                    'TXT_NOTES_STATUS'  => ($stat == 1)? 'green':'red',
                    'TXT_NOTES_SORTING' => (int) $objResult->fields['pos'],
                    'TXT_ROW'           => $row = ($row == 'row2') ? 'row1' : 'row2',
                    'TXT_ORDER'         => $sorto
            ));
            
            $this->_objTpl->parse('users');
            $objResult->MoveNext();
        }
        $this->_objTpl->setVariable(array(
                'TXT_GENERAL'                   => $_ARRAYLANG['TXT_GENERAL'],
                'TXT_CUSTOMER_TYPES'            => $_ARRAYLANG['TXT_CUSTOMER_TYPES'],
                'TXT_CURRENCY'                  => $_ARRAYLANG['TXT_CURRENCY'],
                'TXT_NOTES'                     => $_ARRAYLANG['TXT_NOTES'],
                'TXT_NAME'                      => $_ARRAYLANG['TXT_NAME'],
                'TXT_SAVE'                      => $_ARRAYLANG['TXT_SAVE'],
                'TXT_TITLEACTIVE'               => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_SORTING_NUMBER'            => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_ADD_NOTES_TYPES'           => $_ARRAYLANG['TXT_ADD_NOTES_TYPES'],
                'TXT_TITLE_STATUS'              => $_ARRAYLANG['TXT_TITLE_STATUS'],
                'TXT_SORTING'                   => $_ARRAYLANG['TXT_SORTING'],
                'TXT_FUNCTIONS'                 => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_ENTRIES_MARKED'            => $_ARRAYLANG['TXT_ENTRIES_MARKED'],
                'TXT_SELECT_ALL'                => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'              => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_NO_OPERATION'              => $_ARRAYLANG['TXT_NO_OPERATION'],
                'TXT_ACTIVATESELECTED'          => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED'        => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_PROJECTSTATUS_SAVE_SORTING'=> $_ARRAYLANG['TXT_PROJECTSTATUS_SAVE_SORTING'],
                'TXT_NOTES_DELETED'             => $_ARRAYLANG['TXT_NOTES_DELETED'],
                'TXT_DELETE_CONFIRM'            => $_ARRAYLANG['TXT_DELETE_CONFIRM'],
                'TXT_CHANGE_STATUS'             => $_ARRAYLANG['TXT_CHANGE_STATUS'],
                'TXT_DELETE_SELECTED'           => $_ARRAYLANG['TXT_DELETE_SELECTED'],                
                'PM_SETTINGS_CURRENCY_JAVASCRIPT' => $objJs->getAddNotesJavascript(),
        ));
    }

    function editnotes() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase, $objJs;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_settings_editnotes.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_EDIT_SETTINGS'];
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));

        $id       = isset($_GET['id'])? intval($_GET['id']):'';
        $name     = isset($_POST['name'])? contrexx_input2db($_POST['name']):'';
        $status   = isset($_POST['status'])? intval($_POST['status']):'';
        $position = isset($_POST['sorting'])? intval($_POST['sorting']):'';

        if(isset($_POST['currency_submit'])) {
            if(!empty($id)) {
                $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_".$this->moduleName."_notes
                                                                                            SET   name    = '$name',
                                                                                                  status  = '$status',
                                                                                                  pos     = '$position'
                                                                                            WHERE id      = '$id'");
                csrf::header("Location:".ASCMS_ADMIN_WEB_PATH."/index.php?cmd=".$this->moduleName."&act=settings&tpl=notes&message=updatenotes");
            }
        }
        $objResult = $objDatabase->Execute("SELECT id,name,status,pos FROM ".DBPREFIX."module_".$this->moduleName."_notes WHERE id = '$id'");
        $this->_objTpl->setVariable(array(
                'TXT_NOTESNAME'   =>  contrexx_raw2xhtml($objResult->fields['name']),
                'TXT_NOTESSTATUS' =>  ($objResult->fields['status'] == 1)? 'checked':'',
                'TXT_NOTESPOS'    =>  (int) $objResult->fields['pos'],
        ));
        $this->_objTpl->setVariable(array(
                'TXT_NOTES'                     => $_ARRAYLANG['TXT_NOTES'],
                'TXT_NAME'                      => $_ARRAYLANG['TXT_NAME'],
                'TXT_TITLEACTIVE'               => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_SORTING_NUMBER'            => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_ADD_NOTES_TYPES'           => $_ARRAYLANG['TXT_ADD_NOTES_TYPES'],
                'TXT_TITLE_STATUS'              => $_ARRAYLANG['TXT_TITLE_STATUS'],
                'TXT_SORTING'                   => $_ARRAYLANG['TXT_SORTING'],
                'TXT_NAME'                      => $_ARRAYLANG['TXT_NAME'],
                'TXT_FUNCTIONS'                 => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SAVE'                      => $_ARRAYLANG['TXT_SAVE'],
                'TXT_EDIT_NOTES'                => $_ARRAYLANG['TXT_EDIT_NOTES'],
                'TXT_BACK'                      => $_ARRAYLANG['TXT_BACK'],
                'PM_SETTINGS_CURRENCY_JAVASCRIPT' => $objJs->getAddCurrencyJavascript(),
                'CSRF_PARAM'                    => CSRF::param(),
        ));

    }

    function validation($name) {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $objResult = $objDatabase->Execute("SELECT name FROM ".DBPREFIX."module_".$this->moduleName."_notes WHERE name='$name'");
        if($objResult->fields['name'] == $name) {
            return false;
        }else {
            return true;
        }
    }

    function deleteCurrency() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $id = $_GET['id'];

        if(!empty($id)) {
            $deleteQuery = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_currency` WHERE id = '.$id;
            $objDatabase->Execute($deleteQuery);
            $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CURRENCY_DELETED_SUCCESSFULLY'];
        } else {
            $deleteIds = $_POST['selectedEntriesId'];
            foreach($deleteIds as $id) {
                $deleteQuery = 'DELETE FROM `'.DBPREFIX.'module_'.$this->moduleName.'_currency` WHERE id = '.$id;
                $objDatabase->Execute($deleteQuery);
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CURRENCY_DELETED_SUCCESSFULLY'];
            }
        }
        CSRF::header('location:./index.php?cmd=crm&act=settings&tpl=currency');
        exit();
    }

    function currencyChangeStatus() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $status = ($_GET['status'] == 0) ? 1 : 0;
        $id     = intval($_GET['id']);
        if (!empty($id)) {
            $query = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_currency SET active='.$status.' WHERE id = '.$id;
            $objDatabase->Execute($query);
            $this->_strOkMessage = ($status == 1) ? $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
        }

        if($_REQUEST['type'] == "activate") {
            $arrStatusNote = $_POST['selectedEntriesId'];
            if($arrStatusNote != null) {
                foreach ($arrStatusNote as $noteId) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_currency SET active='1' WHERE id=$noteId";
                    $objDatabase->Execute($query);
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'];
        }
        if($_REQUEST['type'] == "deactivate") {
            $arrStatusNote = $_POST['selectedEntriesId'];
            if($arrStatusNote != null) {
                foreach ($arrStatusNote as $noteId) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_currency SET active='0' WHERE id=$noteId";
                    $objDatabase->Execute($query);
                }
            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
        }
        
        $_GET['tpl'] = 'currency';
        $this->settingsSubmenu();        
    }

    function notesChangeStatus() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;

        $status = ($_GET['stat'] == 0) ? 1 : 0;
        $id     = intval($_GET['ids']);

        if (!empty($id)) {
            $query = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_notes SET status='.$status.' WHERE id = '.$id;
            $objDatabase->Execute($query);
            $this->_strOkMessage = ($status == 1) ? $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
        }
        $_GET['tpl'] = 'notes';
        $this->settingsSubmenu();        
    }

    function editCurrency($labelValue="") {
        global $_CORELANG, $_ARRAYLANG, $objDatabase, $objJs;

        $this->_pageTitle = $_ARRAYLANG['TXT_EDIT_CURRENCY'];
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_setting_editcurrency.html');
        $this->_objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName
        ));

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if (empty($id)) {
            CSRF::header(ASCMS_ADMIN_WEB_PATH."/index.php?cmd={$this->moduleName}&act=settings&tpl=currency");
            exit();
        }

        $label      = isset($_POST['name']) ? contrexx_input2raw($_POST['name']) : '';
        $sorting    = isset($_POST['sortingNumber']) ? (int) $_POST['sortingNumber'] : '';
        $status     = isset($_POST['activeStatus']) ? 1 : 0;
        $hrlyRate           = array();
        if (!empty($_POST['customerType'])) {
            foreach ($_POST['customerType'] as $customerTypeId) {
                $hrlyRate[$customerTypeId] = (isset($_POST['rateValue_'.$customerTypeId])) ? intval($_POST['rateValue_'.$customerTypeId]) : 0;
            }
        }
        
        if (isset($_POST['currency_submit'])) {
            $success = true;

            $searchDuplicateQuery = "SELECT name
                                        FROM `".DBPREFIX."module_{$this->moduleName}_currency`
                                        WHERE name='$label' AND id != $id";
            $objData = $objDatabase->Execute($searchDuplicateQuery);

            if ($objData->RecordCount()) {
                $_SESSION['strErrMessage'] = $_ARRAYLANG['TXT_CURRENCY_ALREADY_EXISTS'];                
            } else {
                $updateProjectTypes = "UPDATE `".DBPREFIX."module_{$this->moduleName}_currency`
    							     SET  `name`  = '$label',
                                          `pos`   = '$sorting',
                                          `active`= '$status',
                                          `hourly_rate` = '".json_encode($hrlyRate)."'
                                     WHERE id = $id";
                $objDatabase->Execute($updateProjectTypes);
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_CURRENCY_UPDATED_SUCCESSFULLY'];

                CSRF::header("location:./index.php?cmd={$this->moduleName}&act=settings&tpl=currency");
                exit();
            }
        } else {
            $objResult =   $objDatabase->Execute("SELECT  id, name,pos, hourly_rate, active FROM ".DBPREFIX."module_{$this->moduleName}_currency WHERE id = $id");

            $label      = $objResult->fields['name'];
            $sorting    = $objResult->fields['pos'];
            $status     = $objResult->fields['active'];
            $hrlyRate   = json_decode($objResult->fields['hourly_rate'] ,true);
        }

        // Hourly rate
        
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
            'TXT_SORTINGNUMBER'       => (int) $sorting,
            'TXT_CURRENCY_ID'	      => (int) $id,
            'TXT_NAME_VALUE'	      => contrexx_raw2xhtml($label),
            'TXT_ACTIVATED_VALUE'     => $status ? 'checked' : '',

            'TXT_EDIT_CURRENCY'       => $_ARRAYLANG['TXT_EDIT_CURRENCY'],
            'TXT_NAME'                => $_ARRAYLANG['TXT_NAME'],
            'TXT_FUNCTIONS'           => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_SAVE'                => $_ARRAYLANG['TXT_SAVE'],
            'TXT_SORTING_NUMBER'      => $_ARRAYLANG['TXT_SORTING_NUMBER'],
            'TXT_TITLEACTIVE'         => $_ARRAYLANG['TXT_TITLEACTIVE'],
            'TXT_BACK'                => $_ARRAYLANG['TXT_BACK'],
            'TXT_SELECT_ACTION'       => $_ARRAYLANG['TXT_SELECT_ACTION'],
            'TXT_DELETE_SELECTED'     => $_ARRAYLANG['TXT_DELETE_SELECTED'],
            'TXT_CURRENCY_RATES'              => $_ARRAYLANG['TXT_CURRENCY_RATES'],
            'TXT_HOURLY_RATE'                 => $_ARRAYLANG['TXT_HOURLY_RATE'],
            'CSRF_PARAM'              => CSRF::param(),
            'CURRENCY_JAVASCRIPT'     => $objJs->getAddCurrencyJavascript()
        ));        
    }

    function changeCustomerStatus() {
        global $objDatabase, $_ARRAYLANG;

        $customerId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($customerId) {
            $objDatabase->Execute("UPDATE `".DBPREFIX."module_{$this->moduleName}_customers`
                                        SET is_active = 0
                                    WHERE id=$customerId");
            echo $_ARRAYLANG['TXT_CUSTOMER_DEACTIVATE_STATUS'];
        }
        exit();
    }

    /**
     *
     * @param String $str
     * @param tags which needs to be strip $tags
     * @param boolean $stripContent
     * @return string
     */
    function strip_only_tags($str, $tags, $stripContent=false) {
        $content = '';
        if(!is_array($tags)) {
            $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
            if(end($tags) == '') array_pop($tags);
        }
        foreach($tags as $tag) {
            if ($stripContent)
                $content = '(.+</'.$tag.'(>|\s[^>]*>)|)';
            $str = preg_replace('#</?'.$tag.'(>|\s[^>]*>)'.$content.'#is', '', $str);
        }
        return $str;
    }

    function exportVcf() {
        global $objDatabase;

        $id   = (int) $_GET['id'];

        $query    = "SELECT c.`customer_name`,
                            c.`contact_familyname`,
                            c.`contact_type`,
                            con.`customer_name` AS company,
                            con.`id` AS companyId
                        FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                        LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS con
                         ON c.`contact_customer` =con.`id`
                        WHERE c.`id` = $id";

        $mailQry  = "SELECT email, email_type FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` WHERE contact_id = $id AND email_type IN (0,1) ORDER BY id DESC";
        $phoneQry = "SELECT phone, phone_type FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` WHERE contact_id = $id AND phone_type IN (0,1,2, 3) ORDER BY id DESC";
        $webQry   = "SELECT url, url_type     FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` WHERE contact_id = $id AND url_profile = 1 AND url_type IN (0,1) ORDER BY id DESC";
        $addrQry  = "SELECT *                 FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` WHERE contact_id = $id AND Address_Type IN (0,5) ORDER BY id DESC";

        if (false != ($objRS = $objDatabase->Execute($query))  && false != ($objMail = $objDatabase->Execute($mailQry)) && false != ($objPhone = $objDatabase->Execute($phoneQry)) && false != ($objWeb = $objDatabase->Execute($webQry)) && false != ($objAddr = $objDatabase->Execute($addrQry))) {

            $isWorkEmail = false;
            while (!$objMail->EOF) {
                switch (true) {
                    case ($objMail->fields['email_type'] == 0):
                        $homeEmail      = utf8_decode($objMail->fields['email']);
                        break;
                    case ($objMail->fields['email_type'] == 1):
                        $isWorkEmail    = true;
                        $workEmail      = utf8_decode($objMail->fields['email']);
                        break;
                    default:
                        break;
                }
                $objMail->MoveNext();
            }
            $isWorkPhone = false;
            while (!$objPhone->EOF) {                
                switch (true) {
                    case ($objPhone->fields['phone_type'] == 0):
                        $homeTelephone  = utf8_decode($objPhone->fields['phone']);
                        break;
                    case ($objPhone->fields['phone_type'] == 1):
                        $isWorkPhone    = true;
                        $wrkTelephone   = utf8_decode($objPhone->fields['phone']);
                        break;
                    case ($objPhone->fields['phone_type'] == 2):
                        $celephone      = utf8_decode($objPhone->fields['phone']);
                        break;
                    case ($objPhone->fields['phone_type'] == 3):
                        $fax            = utf8_decode($objPhone->fields['phone']);
                        break;
                    default:
                        break;
                }
                $objPhone->MoveNext();
            }
            while (!$objWeb->EOF) {
                switch (true) {
                    case ($objWeb->fields['url_type'] == 0):
                        $homeWebsite      = utf8_decode($objWeb->fields['url']);
                        break;
                    case ($objWeb->fields['url_type'] == 1):
                        $workWebsite      = utf8_decode($objWeb->fields['url']);
                        break;
                    default:
                        break;
                }
                $objWeb->MoveNext();
            }
            $workAddr = false;
            while (!$objAddr->EOF) {
                switch (true) {
                    case ($objAddr->fields['Address_Type'] == 0):
                        $homeAddress    = utf8_decode($objAddr->fields['address']);
                        $homeCity       = utf8_decode($objAddr->fields['city']);
                        $homeState      = utf8_decode($objAddr->fields['state']);
                        $homePostalcode = utf8_decode($objAddr->fields['zip']);
                        $homeCountry    = utf8_decode($objAddr->fields['country']);
                        break;
                    case ($objAddr->fields['Address_Type'] == 5):
                        $workAddr       = true;
                        $workAddress    = utf8_decode($objAddr->fields['address']);
                        $workCity       = utf8_decode($objAddr->fields['city']);
                        $workState      = utf8_decode($objAddr->fields['state']);
                        $workPostalcode = utf8_decode($objAddr->fields['zip']);
                        $workCountry    = utf8_decode($objAddr->fields['country']);
                        break;
                    default:
                        break;
                }
                $objAddr->MoveNext();
            }

            if ($objRS->fields['contact_type'] == 1) {
                $firstName = utf8_decode($objRS->fields['customer_name']);
            } elseif ($objRS->fields['contact_type'] == 2) {
                $firstName   = utf8_decode($objRS->fields['customer_name']);
                $lastName    = utf8_decode($objRS->fields['contact_familyname']);
                $role        = utf8_decode($objRS->fields['contact_role']);
                $companyName = utf8_decode($objRS->fields['company']);

                if (!$workAddr || !$isWorkEmail || !$isWorkPhone) {
                    $objContactCompany = $objDatabase->Execute("SELECT email.email,
                                                                     phone.phone,
                                                                     addr.*
                                                                  FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                                                                  LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` as email
                                                                   ON (c.id = email.contact_id AND email.is_primary = '1')
                                                                  LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` as phone
                                                                   ON (c.id = phone.contact_id AND phone.is_primary = '1')
                                                                  LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` as addr
                                                                   ON (c.id = addr.contact_id AND addr.is_primary = '1')
                                                                  WHERE c.`id` = {$objRS->fields['companyId']}");
                    if (!$isWorkEmail)
                        $workEmail      = utf8_decode($objContactCompany->fields['email']);
                    if (!$isWorkPhone)
                        $wrkTelephone   = utf8_decode($objContactCompany->fields['phone']);

                    if (!$workAddr) {
                        $workAddress    = utf8_decode($objContactCompany->fields['address']);
                        $workCity       = utf8_decode($objContactCompany->fields['city']);
                        $workState      = utf8_decode($objContactCompany->fields['state']);
                        $workPostalcode = utf8_decode($objContactCompany->fields['zip']);
                        $workCountry    = utf8_decode($objContactCompany->fields['country']);
                    }
                }
            }

            require_once("lib/class_vcard.php");

            $vc        = new vcard();
            $vc->data['customer_name']      = $firstName." ".$lastName;
            $vc->data['company']            = $companyName;
            $vc->data['email1']             = $workEmail;
            $vc->data['email2']             = $homeEmail;
            $vc->data['title']              = $role;
            $vc->data['first_name']         = $firstName;
            $vc->data['last_name']          = $lastName;
            $vc->data['work_address']       = $workAddress;
            $vc->data['work_city']          = $workCity;
            $vc->data['work_tele']          = $wrkTelephone;
            $vc->data['work_postal_code']   = $workPostalcode;
            $vc->data['work_country']       = $workCountry;
            $vc->data['home_address']       = $homeAddress;
            $vc->data['home_city']          = $homeCity;
            $vc->data['home_country']       = $homeCountry;
            $vc->data['home_postal_code']   = $homePostalcode;
            $vc->data['home_tel']           = $homeTelephone;
            $vc->data['cell_tel']           = $celephone;
            $vc->data['office_tel']         = $wrkTelephone;
            $vc->data['fax_tel']            = $fax;
            $vc->data['fax_home']           = $fax;            
            $vc->data['work_url']           = $workWebsite;
            $vc->data['home_url']           = $homeWebsite;
            $vc->download();
        }
        exit();
    }

    function changeCustomerContactStatus() {
        global $objDatabase;

        $objTemplate = $this->_objTpl;

        $id = (int) $_GET['id'];

        if($id) {
            $result = $objDatabase->Execute("UPDATE `".DBPREFIX."module_{$this->moduleName}_contacts` SET `status` = IF(status = 1, 0, 1) WHERE id = $id");
        }

        exit();
    }

    function submenu() {
        global $_ARRAYLANG;
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_tasks.html',true,true);

        $this->_objTpl->setVariable(array(
                'MODULE_NAME'                    => $this->moduleName,
                "TXT_OVERVIEW"   => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_CRM_ADD_TASK'  => $_ARRAYLANG['TXT_CRM_ADD_TASK'],
                'TXT_CRM_ADD_IMPORT'  => $_ARRAYLANG['TXT_CRM_ADD_IMPORT'],
                'TXT_CRM_ADD_EXPORT'  => $_ARRAYLANG['TXT_CRM_ADD_EXPORT'],
        ));
    }

    function editTaskType() {
        global $objDatabase, $_ARRAYLANG;

        JS::activate("jquery");
        // Activate validation scripts
        JS::registerCSS("lib/javascript/validationEngine/css/validationEngine.jquery.css");
        JS::registerJS("lib/javascript/validationEngine/js/languages/jquery.validationEngine-en.js");
        JS::registerJS("lib/javascript/validationEngine/js/jquery.validationEngine.js");
        JS::registerCSS("lib/javascript/chosen/chosen.css");
        JS::registerJS("lib/javascript/chosen/chosen.jquery.js");

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($_POST['saveTaskType']) {
            $this->saveTaskTypes($id);
            $msg = "taskUpdated";
            CSRF::header("Location:./index.php?cmd={$this->moduleName}&act=settings&tpl=tasktypes&msg=".base64_encode($msg));
            exit();
        }

        $objTpl = $this->_objTpl;
        $this->_pageTitle = $_ARRAYLANG['TXT_CRM_EDIT_TASK_TYPE'];
        $objTpl->loadTemplateFile("module_{$this->moduleName}_settings_edit_task_types.html");

        $this->getModifyTaskTypes($id);

        $objTpl->setVariable(array(
                'TXT_CRM_ADD_TASK_TYPE'     => $_ARRAYLANG['TXT_CRM_EDIT_TASK_TYPE'],
                'TXT_BACK1'                 => $_ARRAYLANG['TXT_BACK1'],
                'CSRF_PARAM'                => CSRF::param(),
        ));

    }

    function showTasks() {
        global $_ARRAYLANG, $objDatabase;

        JS::activate("jquery");
        JS::activate("jquery-ui");

        $objtpl = $this->_objTpl;
        $this->_pageTitle = $_ARRAYLANG['TXT_TASK_OVERVIEW'];
        $objtpl->loadTemplateFile("module_{$this->moduleName}_tasks_overview.html");
        $objtpl->setGlobalVariable("MODULE_NAME", $this->moduleName);
        $taskId = isset($_REQUEST['searchType'])? intval($_REQUEST['searchType']) : 0 ;
        $taskTitle = isset($_REQUEST['searchTitle'])? contrexx_input2raw($_REQUEST['searchTitle']) : '';

        $mes = base64_decode($_REQUEST['mes']);
        switch($mes) {
            case 'Inserted':
                $this->_strOkMessage = $_ARRAYLANG['TXT_TASK_OK_MESSAGE'];
                break;
            case 'Updated':
                $this->_strOkMessage = $_ARRAYLANG['TXT_TASK_UPDATE_MESSAGE'];
                break;
            case 'Deleted':
                $this->_strOkMessage = $_ARRAYLANG['TXT_TASK_DELETE_MESSAGE'];
                break;
            case 'Changed':
                $this->_strOkMessage = $_ARRAYLANG['TXT_TASK_STATUS_MESSAGE'];
                break;
        }
        $objType = $objDatabase->Execute("SELECT id,name FROM ".DBPREFIX."module_{$this->moduleName}_task_types ORDER BY sorting");
        if ($objType) {
            while(!$objType->EOF) {
                $selected = ($objType->fields['id'] == $taskId) ? 'selected="selected"' : '';
                $objtpl->setVariable(array(
                        'TXT_TASK_ID'      => $objType->fields['id'],
                        'TXT_TASK_NAME'    => $objType->fields['name'],
                        'TXT_TASK_SELECTED'=> $selected,
                ));
                $objtpl->parse('tastType');
                $objType->MoveNext();
            }
        }

        if (isset($_GET['chg']) and $_GET['chg'] == 1 and isset($_POST['selected']) and is_array($_POST['selected'])) {
            if ($_POST['form_activate'] != '' or $_POST['form_deactivate'] != '') {
                $ids = $_POST['selected'];
                $to  = $_POST['form_activate'] ? 1 : 0;
                foreach($ids as $id) {
                    $query = "UPDATE ".DBPREFIX."module_".$this->moduleName."_task
                                                                   SET   task_status  = '".$to."'
                                                                   WHERE id      = '".intval($id)."'";
                    $objDatabase->SelectLimit($query, 1);
                }
                $this->_strOkMessage = ($to == 1) ? $_ARRAYLANG['TXT_ACTIVATED_SUCCESSFULLY'] : $_ARRAYLANG['TXT_DEACTIVATED_SUCCESSFULLY'];
            }
            if ($_POST['form_delete'] != '') {
                $ids = $_POST['selected'];
                $x   = 0;
                foreach($ids as $id) {
                    $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleName."_task
                                                                       WHERE id = '".intval($id)."'";
                    $objDatabase->SelectLimit($query, 1);
                }
                $this->_strOkMessage = ($_POST['form_delete'] == 1) ? $_ARRAYLANG['TXT_TASK_DELETED'] : '';
            }
        }

        $message = base64_encode('Changed');
        $status = ($_GET['status'] == 0) ? 1 : 0;
        $id     = intval($_GET['status_id']);

        $typefilter = !empty($taskId)? "&searchType=$taskId":'';
        $titlefilter= !empty($taskTitle)? "&searchTitle=$taskTitle&searchCustomer=$taskTitle":'';

        if (!empty($id)) {
            $query = 'UPDATE '.DBPREFIX.'module_'.$this->moduleName.'_task SET task_status='.$status.' WHERE id = '.$id;
            $objDatabase->Execute($query);
            CSRF::header("location:index.php?cmd=crm&act=task$typefilter$titlefilter&mes='$message'");
            exit();
        }


        if(!empty($taskTitle) || !empty($taskId)) {
            $totalfilter = $typefilter.$titlefilter;
        }

        if ($taskId != 0) {
            $filterAdd   = ($filterTyp == 0) ? " WHERE" : " AND";
            $filterType .= $filterAdd."  t.task_type_id = '$taskId'";
            $filterTyp   = 1;
        }

        if ($taskTitle != '') {
            $filterAdd   = ($filterTyp == 0) ? " WHERE" : " AND";
            $filterType .= $filterAdd."  t.task_title LIKE '%$taskTitle%' OR c.customer_name LIKE '%$taskTitle%'";
            $filterTyp   = 1;
        }

        $query = "SELECT tt.name,
                               t.task_status,
                               t.id,
                               t.task_id,
                               t.task_title,
                               t.task_type_id,
                               t.customer_id,
                               c.customer_name,
                               c.contact_familyname,
                               t.due_date,
                               contrexxuser.username,
                               t.description
                            FROM ".DBPREFIX."module_{$this->moduleName}_task as t
                            LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_task_types as tt
                            ON (t.task_type_id=tt.id)
                            LEFT JOIN ".DBPREFIX."access_users AS contrexxuser
                            ON contrexxuser.id = t.assigned_to
                            LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_contacts as c
                            ON (t.customer_id = c.id) $filterType";
        /* Start Paging ------------------------------------ */
        $intPos             = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        // TODO: Never used
        $intPerPage         = $this->getPagingLimit();
        $strPagingSource    = getPaging($this->countRecordEntries($query), $intPos, "&amp;cmd=$this->moduleName&amp;act=task$totalfilter", false,'', $intPerPage);
        $this->_objTpl->setVariable('ENTRIES_PAGING', $strPagingSource);
        /* End Paging -------------------------------------- */
        $start      = $intPos ?: 0;
        $sorting = array('task_title','task_type_id','customer_name','due_date');

        if(isset($_GET['sortf']) && isset($_GET['sorto'])) {
            $sortf = in_array($sorting[$_GET['sortf']],$sorting)? contrexx_input2raw($sorting[$_GET['sortf']]):'';
            $sorto = ($_GET['sorto'] == 'ASC')? 'DESC' : 'ASC';
            $order = " ORDER BY";
            $order .= " $sortf $sorto LIMIT $start, $intPerPage";
        }

        $idorder    = " ORDER BY";
        $idorder   .= " t.`id` DESC LIMIT $start, $intPerPage";
        if(isset($_GET['sortf']) && isset($_GET['sorto'])) {
            $query .= $order;
        }else {
            $query     .= $idorder;
        }
        $objResult  = $objDatabase->Execute($query);

        $objtpl->setVariable(array( 'TXT_SEARCH_VALUE' => contrexx_input2raw($_POST['search'])));
        $row = 'row2';

        if(empty($objResult->fields['id'])) {
            $objtpl->setVariable(array(
                    'TXT_NO_RECORDS_FOUND'  => 'No records found ...'
            ));
            $objtpl->parse('noRecords');
            $objtpl->setVariable(array(
                    'TXT_NO_RECORDS_FOUND'  => 'No Records Found'
            ));
            $objtpl->parse('noRecords1');
        }else {
            if ($objResult) {
                while(!$objResult->EOF) {
                    $objtpl->setVariable(array(
                            'CRM_TASK_ID'          => (int) $objResult->fields['id'],
                            'TXT_CRM_TASKID'       => contrexx_raw2xhtml($objResult->fields['task_id']),
                            'TXT_CRM_TASKTITLE'    => contrexx_raw2xhtml($objResult->fields['task_title']),
                            'TXT_CRM_TASKTYPE'     => contrexx_raw2xhtml($objResult->fields['name']),
                            'TXT_CRM_CUSTOMERNAME' => contrexx_raw2xhtml($objResult->fields['customer_name']." ".$objResult->fields['contact_familyname']),
                            'TXT_DUEDATE'          => contrexx_raw2xhtml($objResult->fields['due_date']),
                            'TXT_POSEDITLINK'      => $position,
                            'TXT_STATUS'           => (int) $objResult->fields['task_status'],
                            'TXT_DESCRIPTION'      => html_entity_decode($objResult->fields['description'], ENT_QUOTES, CONTREXX_CHARSET),
                            'CRM_TASK_TYPE_ACTIVE' => $objResult->fields['task_status'] == 1 ? 'led_green.gif':'led_red.gif',
                            'TXT_ROW'              => $row = ($row == 'row2')? 'row1':'row2',
                            'TXT_ADDEDBY'          => contrexx_raw2xhtml($objResult->fields['username']),
                            'TXT_IMAGE_EDIT'       => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                            'TXT_IMAGE_DELETE'     => $_ARRAYLANG['TXT_IMAGE_DELETE'],
                            'TXT_LINK'             => $totalfilter,
                            'TXT_ORDER'            => $sorto,
                            'CRM_REDIRECT_LINK'    => '&redirect='.base64_encode("&act=task$typefilter$titlefilter$position"),
                    ));
                    $objtpl->parse('showTask');
                    $objResult->MoveNext();
                }
            }
        }

        $objtpl->setVariable(array(
                'TXT_OVERVIEW'                  => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_CRM_ADD_TASK'              => $_ARRAYLANG['TXT_CRM_ADD_TASK'],
                'TXT_CRM_ADD_IMPORT'            => $_ARRAYLANG['TXT_CRM_ADD_IMPORT'],
                'TXT_CRM_ADD_EXPORT'            => $_ARRAYLANG['TXT_CRM_ADD_EXPORT'],
                "TXT_CRM_FUNCTIONS"             => $_ARRAYLANG['TXT_CRM_FUNCTIONS'],
                'TXT_CRM_TASK_TYPE_DESCRIPTION' => $_ARRAYLANG['TXT_CRM_TASK_TYPE_DESCRIPTION'],
                'TXT_ASSIGNEDTO'                => $_ARRAYLANG['TXT_ASSIGNEDTO'],
                'TXT_TASK_DUE_DATE'             => $_ARRAYLANG['TXT_TASK_DUE_DATE'],
                'TXT_CRM_CUSTOMER_NAME'         => $_ARRAYLANG['TXT_CRM_CUSTOMER_NAME'],
                'TXT_CRM_TASK_TYPE'             => $_ARRAYLANG['TXT_CRM_TASK_TYPE'],
                'TXT_CRM_TASK_TITLE'            => $_ARRAYLANG['TXT_CRM_TASK_TITLE'],
                'TXT_CRM_TASK_ID'               => $_ARRAYLANG['TXT_CRM_TASK_ID'],
                'TXT_CRM_TASK_STATUS'           => $_ARRAYLANG['TXT_CRM_TASK_STATUS'],
                'TXT_TASK'                      => $_ARRAYLANG['TXT_TASK'],
                'TXT_ENTRIES_MARKED'            => $_ARRAYLANG['TXT_ENTRIES_MARKED'],
                'TXT_SELECT_ALL'                => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'              => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_SELECT_ACTION'             => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_NO_OPERATION'              => $_ARRAYLANG['TXT_NO_OPERATION'],
                'TXT_ACTIVATESELECTED'          => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED'        => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_DELETE_SELECTED'           => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_DELETE_CONFIRM'            => $_ARRAYLANG['TXT_DELETE_CONFIRM'],
                'TXT_FILTERS'                   => $_ARRAYLANG['TXT_FILTERS'],
                'TXT_CRM_SEARCH'                => $_ARRAYLANG['TXT_CRM_SEARCH'],
                'TXT_CRM_ENTER_SEARCH_TERM'     => $_ARRAYLANG['TXT_CRM_ENTER_SEARCH_TERM'],
                'TXT_CRM_FILTER_TASK_TYPE'      => $_ARRAYLANG['TXT_CRM_FILTER_TASK_TYPE']
        ));
    }

    function _modifyTask() {
        global $_ARRAYLANG,$objDatabase,$objJs,$objFWUser;
        JS::activate('cx');
        JS::activate("jquery");
        JS::activate("jqueryui");
        JS::activate("shadowbox");
        JS::registerCSS("lib/javascript/crm/css/contact.css");

        $objtpl = $this->_objTpl;
        $this->_pageTitle = empty($_GET['id']) ? $_ARRAYLANG['TXT_ADDTASK'] : $_ARRAYLANG['TXT_EDITTASK'];

        $objtpl->setGlobalVariable("MODULE_NAME", $this->moduleName);
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_addtasks.html');

        $date = date('Y-m-d H:i:s');
        $title      = isset($_POST['taskTitle']) ? contrexx_input2raw($_POST['taskTitle']) : '';
        $type       = isset($_POST['taskType']) ? (int) $_POST['taskType'] : 0;
        $customer   = isset($_REQUEST['customerId']) ? (int) $_REQUEST['customerId'] : '';
        $duedate    = isset($_POST['date']) ? $_POST['date'] : $date;
        $assignedto = isset($_POST['assignedto']) ? intval($_POST['assignedto']) : $objFWUser->objUser->getId();
        $description= html_entity_decode($_POST['description'], ENT_QUOTES, CONTREXX_CHARSET);
        $description= $this->strip_only_tags($description , '<script><iframe>', $stripContent=false);
        $taskAutoId = isset($_POST['taskAutoId']) ? contrexx_input2raw($_POST['taskAutoId']) : "";
        $id         = isset($_REQUEST['id'])? (int) $_REQUEST['id']:'';

        $taskId     = isset($_REQUEST['searchType'])? intval($_REQUEST['searchType']) : 0 ;
        $taskTitle  = isset($_REQUEST['searchTitle'])? contrexx_input2raw($_REQUEST['searchTitle']) : '';

        $redirect     = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : base64_encode('&act=task');
        if (empty($taskAutoId)) {
            $i = 0;
            $codeNumb = "";
            $possible = '1234567890';
            while ($i < 3) {
                $codeNumb .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
                $i++;
            }
            $taskAutoId = "TC".$codeNumb;
        }

        if (isset($_POST['addtask'])) {
            if (!empty($id)) {
                $query = "UPDATE ".DBPREFIX."module_{$this->moduleName}_task
                               SET `task_title`    = '$title',
                                   `task_type_id`  = '$type',
                                   `customer_id` = '$customer',
                                   `due_date`      = '$duedate',
                                   `assigned_to`   = '$assignedto',
                                   `description`   = '".contrexx_input2db(htmlspecialchars($description))."'
                                    WHERE id= '$id'";
                $message = 'Updated';
                $typefilter = !empty($taskId)? "&searchType=$taskId":'';
                $titlefilter= !empty($taskTitle)? "&searchTitle=$taskTitle&searchCustomer=$taskTitle":'';
            } else {
                $query = "INSERT INTO ".DBPREFIX."module_{$this->moduleName}_task
                                   SET `task_id`       = '$taskAutoId',
                                       `task_title`    = '$title',
                                       `task_type_id`  = '$type',
                                       `customer_id` = '$customer',
                                       `due_date`      = '$duedate',
                                       `assigned_to`   = '$assignedto',
                                       `description`   = '".contrexx_input2db(htmlspecialchars($description))."'
                                       ";
                $message = 'Inserted';
            }
            $db = $objDatabase->Execute($query);
            if ($db) {
                CSRF::header("Location:./index.php?cmd={$this->moduleName}&mes=".base64_encode($message).base64_decode($redirect));
                exit();
            }
            return;
        } elseif (!empty($id)) {
            $objValue       = $objDatabase->Execute("SELECT task_id,
                                                            task_title,
                                                            task_type_id,
                                                            due_date,
                                                            assigned_to,
                                                            description,
                                                            t.customer_id,
                                                            c.customer_name,
                                                            c.contact_familyname
                                                       FROM `".DBPREFIX."module_{$this->moduleName}_task` AS t
                                                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                                                            ON t.customer_id = c.id
                                                       WHERE t.id='$id'");

            $title      = $objValue->fields['task_title'];
            $type       = $objValue->fields['task_type_id'];
            $customer   = $objValue->fields['customer_id'];
            $customerName = $objValue->fields['customer_name']." ".$objValue->fields['contact_familyname'];
            $duedate    = $objValue->fields['due_date'];
            $assignedto = $objValue->fields['assigned_to'];
            $description= $objValue->fields['description'];
            $taskAutoId = $objValue->fields['task_id'];
        }

        $objResultMember = $objDatabase->Execute('SELECT contrexxuser.id as userid,
                                                         contrexxuser.is_admin,
                                                         contrexxuser.active,
                                                         contrexxuser.username
                                                   FROM '.DBPREFIX.'access_users AS contrexxuser
                                                      WHERE contrexxuser.active != 0
                                                   ORDER BY contrexxuser.username');
        while(!$objResultMember->EOF) {
            $selectedMember = ($assignedto ==  $objResultMember->fields['userid']) ? "selected" : '';

            $objtpl->setVariable(array(
                    'TXT_ADDPROJECT_MEMBERID'	 => (int) $objResultMember->fields['userid'],
                    'TXT_ADDPROJECT_MEMBERNAME'      => contrexx_raw2xhtml($objResultMember->fields['username']),
                    'TXT_MEMBERSELECTED'             => $selectedMember
            ));
            $objtpl->parse('Members');
            $objResultMember->MoveNext();
        }

        $this->taskTypeDropDown($objtpl, $type);

        $description= html_entity_decode($description, ENT_QUOTES, CONTREXX_CHARSET);

        if (!empty($customer)) {
            // Get customer Name
            $objCustomer = $objDatabase->Execute("SELECT customer_name, contact_familyname  FROM `".DBPREFIX."module_crm_contacts` WHERE id = {$customer}");
            $customerName = $objCustomer->fields['customer_name']." ".$objCustomer->fields['contact_familyname'];
        }

        $objtpl->setVariable(array(
                'CRM_TASK_AUTOID'       => contrexx_raw2xhtml($taskAutoId),
                'CRM_TASK_ID'           => (int) $id,
                'CRM_TASKTITLE'         => contrexx_raw2xhtml($title),
                'CRM_DUE_DATE'          => contrexx_raw2xhtml($duedate),
                'CRM_CUSTOMER_ID'       => intval($customer),
                'CRM_CUSTOMER_NAME'     => contrexx_raw2xhtml($customerName),                
                'CRM_TASK_DESC'         => new \Cx\Core\Wysiwyg\Wysiwyg('description', $description, 'pm_small'),

                'TXT_CRM_ADD_TASK'        => empty($id)? $_ARRAYLANG['TXT_CRM_ADD_TASK'] : $_ARRAYLANG['TXT_EDITTASK'],
                'TXT_TASK_ID'             => $_ARRAYLANG['TXT_TASK_ID'],
                'TXT_TASK_TITLE'          => $_ARRAYLANG['TXT_TASK_TITLE'],
                'TXT_TASK_TYPE'           => $_ARRAYLANG['TXT_TASK_TYPE'],
                'TXT_SELECT_TASK_TYPE'    => $_ARRAYLANG['TXT_SELECT_TASK_TYPE'],
                'TXT_CUSTOMER_NAME'       => $_ARRAYLANG['TXT_CUSTOMER_NAME'],
                'TXT_TASK_DUE_DATE'       => $_ARRAYLANG['TXT_TASK_DUE_DATE'],
                'TXT_ASSIGNEDTO'          => $_ARRAYLANG['TXT_ASSIGNEDTO'],
                'TXT_SELECT_MEMBER_NAME'  => $_ARRAYLANG['TXT_SELECT_MEMBER_NAME'],
                'TXT_OVERVIEW'            => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_TASK_DESCRIPTION'  => $_ARRAYLANG['TXT_TASK_DESCRIPTION'],
                'TXT_CRM_FIND_COMPANY_BY_NAME'=>    $_ARRAYLANG['TXT_CRM_FIND_COMPANY_BY_NAME'],
        ));
    }

    function deleteTask() {
        global $_ARRAYLANG,$objDatabase;

        $objtpl  = $this->_objTpl;
        $objtpl->setGlobalVariable("MODULE_NAME", $this->moduleName);
        $id      = isset($_GET['id'])? (int) $_GET['id']:0;
        $taskId     = isset($_REQUEST['searchType'])? intval($_REQUEST['searchType']) : 0 ;
        $taskTitle  = isset($_REQUEST['searchTitle'])? contrexx_input2raw($_REQUEST['searchTitle']) : '';
        $message = base64_encode('Deleted');

        if(!empty($id)) {
            $objResult = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_{$this->moduleName}_task WHERE id = '$id'");
            $typefilter = !empty($taskId)? "&searchType=$taskId":'';
            $titlefilter= !empty($taskTitle)? "&searchTitle=$taskTitle&searchCustomer=$taskTitle":'';
            csrf::header("location:index.php?cmd=crm&act=task$typefilter$titlefilter&mes='$message'");
        }
    }

    function countRecordEntries($query) {
        global $objDatabase;

        $objEntryResult = $objDatabase->Execute('SELECT  COUNT(*) AS numberOfEntries
                                                    FROM    ('.$query.') AS num');

        return intval($objEntryResult->fields['numberOfEntries']);
    }

    function customerTooltipDetail() {
        global $_ARRAYLANG,$objDatabase,$objJs;

        $objtpl  = $this->_objTpl;
        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_customer_tooltip_detail.html');
        $objtpl->setGlobalVariable("MODULE_NAME", $this->moduleName);
        $contactid = isset($_REQUEST['contactid']) ? (int) $_REQUEST['contactid'] : 0;

        if(!empty($contactid)) {

            $contactCount = $objDatabase->getOne("SELECT count(1) FROM `".DBPREFIX."module_{$this->moduleName}_contacts` WHERE contact_customer = $contactid");

            $query = "SELECT   c.id,
                               c.customer_name,
                               email.email,
                               phone.phone,
                               addr.address, addr.city, addr.state, addr.zip, addr.country
                       FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS con
                         ON c.contact_customer =con.id                       
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` as email
                         ON (c.id = email.contact_id AND email.is_primary = '1')
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` as phone
                         ON (c.id = phone.contact_id AND phone.is_primary = '1')
                       LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` as addr
                         ON (c.id = addr.contact_id AND addr.is_primary = '1')
                       WHERE c.id = $contactid";
            $objResult = $objDatabase->Execute($query);

            $objtpl->setVariable(array(
                    'CUSTOMER_NAME'        => contrexx_raw2xhtml($objResult->fields['customer_name']),
                    'CUSTOMER_PHONE'       => contrexx_raw2xhtml($objResult->fields['phone']),
                    'CUSTOMER_EMAIL'       => contrexx_raw2xhtml($objResult->fields['email']),
                    'CUSTOMER_NOOF_CONTACT'=> (int) $contactCount,
                    'CUSTOMER_ADDRESS'     => contrexx_raw2xhtml($objResult->fields['address']),
                    'CUSTOMER_CITY'        => contrexx_raw2xhtml($objResult->fields['city']),
                    'CUSTOMER_STATE'       => contrexx_raw2xhtml($objResult->fields['state']),
                    'CUSTOMER_POSTCODE'    => contrexx_raw2xhtml($objResult->fields['zip']),
                    'CRM_CONTACT_COUNTRY'  => contrexx_raw2xhtml($objResult->fields['country']),
                    'CRM_CUSTOMER_ID'      => (int) $objResult->fields['id'],
                    'CSRF_PARAM'           => CSRF::param(),
            ));
        }        
        echo $this->_objTpl->get();
        exit();
    }

    function getLinkContacts() {
        global  $objDatabase;

        $searchTerm = (isset($_GET['term'])) ? contrexx_input2raw($_GET['term']) : '';

        $objResult = $objDatabase->Execute("SELECT `id`,
                                                   `customer_name`,
                                                   `contact_familyname`
                                                   FROM `".DBPREFIX."module_{$this->moduleName}_contacts` 
                                            WHERE `contact_type` = 2
                                              AND `contact_customer` = 0
                                              AND (contact_familyname like '%$searchTerm%' OR customer_name like '%$searchTerm%')");

        $contacts = array();
        while (!$objResult->EOF) {
            $contacts[] = array(
                    'id'    => (int) $objResult->fields['id'],
                    'label' => html_entity_decode(stripslashes($objResult->fields['contact_familyname'].' '.$objResult->fields['customer_name']), ENT_QUOTES, CONTREXX_CHARSET),
            );
            $objResult->MoveNext();
        }
        echo json_encode($contacts);
        exit();
    }

    function addContact() {
        global $objDatabase, $_ARRAYLANG;

        $contactId  = (isset ($_GET['id'])) ? (int) $_GET['id'] : 0;
        $customerId = (isset ($_GET['customerid'])) ? (int) $_GET['customerid'] : 0;
        $tpl = isset($_GET['tpl']) ? $_GET['tpl'] : '';

        $this->contact = $this->load->model('crmContact', __CLASS__);
        if ($contactId)
            $this->contact->load($contactId);

        isset($_POST['firstname'])  ? $this->contact->customerName = $_POST['firstname'] : '';
        isset($_POST['familyname']) ? $this->contact->family_name  = $_POST['familyname'] : '';
        isset($_POST['language'])   ? $this->contact_language      = $_POST['language'] : '';

        $this->contact->contact_customer = $customerId;
        $this->contact->contactType = 2;
        $this->contact->save();
        switch ($tpl) {
            case 'delete':
                $this->unlinkContact($contactId);
                exit();
            case 'add':
            // insert email
                $objDatabase->Execute("INSERT INTO `".DBPREFIX."module_".$this->moduleName."_customer_contact_emails`
                                        SET `email` = '".contrexx_input2db($_POST['email'])."',
                                            `email_type` = 1, `is_primary` = '1', contact_id = {$this->contact->id}");
                break;
            default:
                break;
        }
        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile("module_{$this->moduleName}_add_customer_contact.html");

        if (isset($this->contact->id)) {
            $objTpl->setVariable(array(
                    'CRM_CONTACT_ID'     => $this->contact->id,
                    'CRM_CONTACT_NAME'   => contrexx_raw2xhtml($this->contact->family_name." ".$this->contact->customerName)
            ));
        }

        echo $objTpl->get();
        exit();
    }

    function getContactTasks() {
        global $objDatabase, $_ARRAYLANG;

        $objTpl = $this->_objTpl;

        $contactId = (int) $_GET['id'];
        $intPerpage = 50;
        $intPage    = (isset($_GET['page']) ? (int) $_GET['page'] : 0) * $intPerpage;
        $objTpl->loadTemplateFile("module_{$this->moduleName}_contact_tasks.html");

        $query = "SELECT tt.name,
                         t.id AS taskid,
                         t.task_title,                         
                         c.customer_name,
                         c.contact_familyname,
                         t.due_date,
                         contrexxuser.username,
                         t.description,
                         t.task_status
                    FROM ".DBPREFIX."module_{$this->moduleName}_task as t
                    LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_task_types as tt
                        ON (t.task_type_id=tt.id)
                    LEFT JOIN ".DBPREFIX."access_users AS contrexxuser
                            ON contrexxuser.id = t.assigned_to
                    LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_contacts as c
                        ON t.customer_id = c.id WHERE c.id = $contactId ORDER BY t.id DESC LIMIT $intPage, $intPerpage ";

        $objResult = $objDatabase->Execute($query);
        if ($objResult->RecordCount() == 0 && $_GET['ajax'] == true) {
            echo '0';
            exit();
        }
        ($objResult->RecordCount() > 0) ? $objTpl->hideBlock("noRecords") : $objTpl->touchBlock("noRecords");
        if ($objResult) {
            while(!$objResult->EOF) {
                $objTpl->setVariable(array(
                        'CRM_TASK_ID'          => (int) $objResult->fields['id'],
                        'TXT_CRM_TASKID'       => contrexx_raw2xhtml($objResult->fields['task_id']),
                        'TXT_CRM_TASKTITLE'    => contrexx_raw2xhtml($objResult->fields['task_title']),
                        'TXT_CRM_TASKTYPE'     => contrexx_raw2xhtml($objResult->fields['name']),
                        'TXT_CRM_CUSTOMERNAME' => contrexx_raw2xhtml($objResult->fields['customer_name']." ".$objResult->fields['contact_familyname']),
                        'TXT_DUEDATE'          => contrexx_raw2xhtml($objResult->fields['due_date']),
                        'TXT_POSEDITLINK'      => $position,
                        'TXT_STATUS'           => (int) $objResult->fields['task_status'],
                        'TXT_DESCRIPTION'      => html_entity_decode($objResult->fields['description'], ENT_QUOTES, CONTREXX_CHARSET),
                        'CRM_TASK_TYPE_ACTIVE' => $objResult->fields['task_status'] == 1 ? 'led_green.gif':'led_red.gif',
                        'TXT_ROW'              => $row = ($row == 'row2')? 'row1':'row2',
                        'TXT_ADDEDBY'          => contrexx_raw2xhtml($objResult->fields['username']),
                        'TXT_IMAGE_EDIT'       => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                        'TXT_IMAGE_DELETE'     => $_ARRAYLANG['TXT_IMAGE_DELETE'],
                ));
                $objTpl->parse('showTask');
                $objResult->MoveNext();
            }

            $objTpl->setVariable(array(
                    'TXT_CRM_TASK_STATUS'   => $_ARRAYLANG['TXT_CRM_TASK_STATUS'],
                    'TXT_CRM_TASK_TITLE'    => $_ARRAYLANG['TXT_CRM_TASK_TITLE'],
                    'TXT_CRM_TASK_TYPE'     => $_ARRAYLANG['TXT_CRM_TASK_TYPE'],
                    'TXT_CRM_CUSTOMER_NAME' => $_ARRAYLANG['TXT_CRM_CUSTOMER_NAME'],
                    'TXT_TASK_DUE_DATE'     => $_ARRAYLANG['TXT_TASK_DUE_DATE'],
                    'TXT_ASSIGNEDTO'        => $_ARRAYLANG['TXT_ASSIGNEDTO'],
                    'TXT_TASK'              => $_ARRAYLANG['TXT_TASK'],
                    'TXT_NO_RECORDS_FOUND'  => $_ARRAYLANG['CRM_NO_RECORDS_FOUND'],
                    'TXT_CRM_ADD_TASK'      => $_ARRAYLANG['TXT_CRM_ADD_TASK'],
                    'CSRF_PARAM'            => CSRF::param(),
                    'CRM_CUSTOMER_ID'       => $contactId,
            ));
        }
        $this->_objTpl->setGlobalVariable('CRM_REDIRECT_LINK' , '&redirect='.base64_encode("&act=showcustdetail&id={$contactId}"));
        if (isset($_GET['ajax'])) {
            $this->_objTpl->hideBlock("skipAjaxBlock");
            $this->_objTpl->hideBlock("skipAjaxBlock1");
        }
        echo $objTpl->get();
        exit();
    }

    function getContactProjects() {
        global $objDatabase, $_ARRAYLANG;

        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile("module_{$this->moduleName}_contacts_projects.html");
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME'        => $this->moduleName,
                'PM_MODULE_NAME'    => $this->pm_moduleName
        ));
        $custId = (int) $_GET['id'];
        $intPerpage = 50;
        $intPage    = (isset($_GET['page']) ? (int) $_GET['page'] : 0) * $intPerpage;
        $projectsResult = 'SELECT pro.`id`,
                                         pro.`name`,
                                         pro.`domain`,
                                         cusWeb.`url`,
                                         pro.`quoted_price`,
                                         pro.`target_date`,
                                         pstatus.`name` AS proStatus,
                                         users.`username`,
                                         cus.`customer_name`,
                                         cus.`contact_familyname`,
                                         cus.`contact_type`,
                                         curr.`name` AS currency
                                  FROM `'.DBPREFIX.'module_'.$this->pm_moduleName.'_projects` AS pro
                                  LEFT JOIN `'.DBPREFIX.'module_'.$this->pm_moduleName.'_project_status` AS pstatus
                                      ON pro.`status` = pstatus.`projectstatus_id`
                                  LEFT JOIN `'.DBPREFIX.'module_'.$this->moduleName.'_contacts` AS cus
                                      ON pro.`customer_id` = cus.`id`
                                  LEFT JOIN `'.DBPREFIX.'module_'.$this->moduleName.'_currency` AS curr
                                      ON cus.`customer_currency` = curr.`id`
                                  LEFT JOIN `'.DBPREFIX.'access_users` AS users
                                      ON pro.`assigned_to` = users.`id`
                                  LEFT JOIN `'.DBPREFIX.'module_'.$this->moduleName.'_customer_contact_websites` AS cusWeb
                                      ON pro.`domain` = cusWeb.`id`
                                  WHERE pro.`customer_id` = '.(int) $custId .' ORDER BY pro.`id` DESC LIMIT '.$intPage.','. $intPerpage;

        $objProjectResult = $objDatabase->Execute($projectsResult);
        if ($objProjectResult->RecordCount() == 0 && $_GET['ajax'] == true) {
            echo '0';
            exit();
        }

        if ($objProjectResult->RecordCount() == 0) {
            $this->_objTpl->hideBlock('showProjects');
            $this->_objTpl->touchBlock('noProjectEntries');
        } else {
            $this->_objTpl->touchBlock('showProjects');
            $this->_objTpl->hideBlock('noProjectEntries');
        }

        $row = 'row2';
        while (!$objProjectResult->EOF) {
            $contactType = $objProjectResult->fields['contact_type'];
            $company     = contrexx_raw2xhtml($objProjectResult->fields['customer_name']." ".$objProjectResult->fields['contact_familyname']);
            $active = '<img border="0" src="images/icons/led_green.gif" alt="" title="Inactive" style="margin-top:4px;"/>';
            $this->_objTpl->setVariable(array(
                    'CRM_PROJECT_ACTIVE'       => $active,
                    'CRM_PROJECT_ID'           => (int) $objProjectResult->fields['id'],
                    'CRM_PROJECT_NAME'         => "<a href='index.php?cmd={$this->pm_moduleName}&act=projectdetails&".CSRF::param()."&projectid={$objProjectResult->fields['id']}'>".urldecode($objProjectResult->fields['url'])." - ".contrexx_raw2xhtml($objProjectResult->fields['name'])."</a>",
                    'CRM_PROJECT_QUOTED_PRICE' => contrexx_raw2xhtml($objProjectResult->fields['quoted_price']).' '.contrexx_raw2xhtml($objProjectResult->fields['currency']),
                    'CRM_PROJECT_STATUS'       => contrexx_raw2xhtml($objProjectResult->fields['proStatus']),
                    'CRM_PROJECT_RESPONSIBLE'  => contrexx_raw2xhtml($objProjectResult->fields['username']),
                    'CRM_PROJECT_TARGET_DATE'  => $objProjectResult->fields['target_date'],
                    'ENTRY_ROWCLASS'           => $row = ($row == 'row1') ? 'row2' : 'row1',
                    'TXT_COMPANY_NAME'         => $company,
            ));
            $this->_objTpl->parse('showProjects');
            $objProjectResult->MoveNext();
        }

        $this->_objTpl->setGlobalVariable(array (
                'TXT_CRM_ACTIVE'                    => $_ARRAYLANG['TXT_ACTIVE'],
                'TXT_CRM_PROJECT_ID'                => $_ARRAYLANG['CRM_PROJECT_ID'],
                'TXT_CRM_PROJECT_NAME'              => $_ARRAYLANG['CRM_PROJECT_NAME'],
                'TXT_CRM_PROJECT_QUOTED_PRICE'      => $_ARRAYLANG['CRM_PROJECT_QUOTED_PRICE'],
                'TXT_TITLE_COMPANY_NAME'            => $_ARRAYLANG['TXT_COMPANY_NAME'],
                'TXT_CRM_PROJECT_STATUS'            => $_ARRAYLANG['CRM_PROJECT_STATUS'],
                'TXT_CRM_PROJECT_RESPONSIBLE'       => $_ARRAYLANG['CRM_PROJECT_RESPONSIBLE'],
                'TXT_CRM_PROJECT_TARGET_DATE'       => $_ARRAYLANG['CRM_PROJECT_TARGET_DATE'],
                'CRM_NO_RECORDS_FOUND'              => $_ARRAYLANG['CRM_NO_RECORDS_FOUND'],
                'TXT_CRM_PROJECT_CUSTOMER'          => $contactType == 1 ? $_ARRAYLANG['TXT_CRM_PROJECT_CUSTOMER'] : $_ARRAYLANG['TXT_CRM_PROJECT_CONTACT'],
                'TXT_STATUS_SUCCESSFULLY_CHANGED'   => $_ARRAYLANG['TXT_STATUS_SUCCESSFULLY_CHANGED'],
                'TXT_NOTE_TYPE'                     => $_ARRAYLANG['TXT_NOTE_TYPE'],
                'TXT_TITLE_FUNCTIONS'               => $_ARRAYLANG['TXT_TITLE_FUNCTIONS'],
                'TXT_ADD_PROJECT'                   => $_ARRAYLANG['TXT_ADD_PROJECT'],
                'TXT_COMPANY_NAME'                  => $company,
                'CSRF_PARAM'                        => CSRF::param(),
                'CRM_CUSTOMER_ID'                   => $custId,

        ));
        $this->_objTpl->setGlobalVariable('CRM_REDIRECT_LINK' , "&redirect=".base64_encode("&cmd={$this->moduleName}&act=showcustdetail&id={$custId}"));
        if (isset($_GET['ajax'])) {
            $this->_objTpl->hideBlock("skipAjaxBlock");
            $this->_objTpl->hideBlock("skipAjaxBlock1");
        } else {
            $this->_objTpl->touchBlock("skipAjaxBlock");
            $this->_objTpl->touchBlock("skipAjaxBlock1");
        }
        echo $objTpl->get();
        exit();
    }

    function getContactDeals() {
        global $objDatabase, $_ARRAYLANG;

        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile("module_{$this->moduleName}_contacts_deals.html");
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME'        => $this->moduleName,
                'PM_MODULE_NAME'    => $this->pm_moduleName
        ));

        $settings = $this->getSettings();
        $allowPm  = $this->isPmInstalled && $settings['allow_pm'];

        $custId = (int) $_GET['id'];
        $intPerpage = 50;
        $intPage    = (isset($_GET['page']) ? (int) $_GET['page'] : 0) * $intPerpage;

        $dealsResult = "SELECT
                               d.id,
                               d.title,
                               d.quoted_price,
                               d.customer,
                               c.customer_name,
                               c.contact_familyname,
                               d.quote_number,
                               d.assigned_to,
                               d.due_date,
                               d.project_id,
                               u.username
                            FROM ".DBPREFIX."module_{$this->moduleName}_deals AS d
                                LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_contacts AS c
                            ON d.customer = c.id                                
                                LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` AS web
                            ON d.website = web.id
                                LEFT JOIN `".DBPREFIX."access_users` AS u
                            ON u.id = d.assigned_to
                                WHERE d.`customer` = ".(int) $custId ." ORDER BY d.`id` DESC LIMIT $intPage, $intPerpage";

        $objDealsResult = $objDatabase->Execute($dealsResult);
        if ($objDealsResult->RecordCount() == 0 && $_GET['ajax'] == true) {
            echo '0';
            exit();
        }

        if ($objDealsResult->RecordCount() == 0) {
            $this->_objTpl->hideBlock('showDeals');
            $this->_objTpl->touchBlock('noDealsEntries');
        } else {
            $this->_objTpl->touchBlock('showDeals');
            $this->_objTpl->hideBlock('noDealsEntries');
        }

        $row = 'row2';
        while (!$objDealsResult->EOF) {
            $title = $allowPm ? "<a href='./index.php?cmd={$this->pm_moduleName}&act=projectdetails&projectid={$objDealsResult->fields['project_id']}'>".contrexx_raw2xhtml($objDealsResult->fields['title'])."</a>" : contrexx_raw2xhtml($objDealsResult->fields['title']);
            $userName = $allowPm ? "<a href='./index.php?cmd={$this->pm_moduleName}&act=resourcedetails&id={$objDealsResult->fields['assigned_to']}'>".contrexx_raw2xhtml($objDealsResult->fields['username'])."</a>" : contrexx_raw2xhtml($objDealsResult->fields['username']);
            $this->_objTpl->setVariable(array(
                    'ENTRY_ID'              => (int) $objDealsResult->fields['id'],
                    'CRM_DEALS_TITLE'       => $title,
                    'CRM_CONTACT_NAME'      => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objDealsResult->fields['customer']}' title='details'>".contrexx_raw2xhtml($objDealsResult->fields['customer_name']." ".$objDealsResult->fields['contact_familyname']).'</a>',
                    'CRM_DEALS_CONTACT_NAME'=> $userName,
                    'CRM_DEALS_DUE_DATE'    => contrexx_raw2xhtml($objDealsResult->fields['due_date']),
                    'ROW_CLASS'             => $row = ($row == "row2") ? "row1" : 'row2',
            ));
            $this->_objTpl->parse('showDeals');
            $objDealsResult->MoveNext();
        }

        $this->_objTpl->setVariable(array (
                'TXT_CRM_DEALS_OVERVIEW'        => $_ARRAYLANG['TXT_CRM_DEALS_OVERVIEW'],
                'TXT_CRM_DEALS_TITLE'           => $_ARRAYLANG['TXT_CRM_DEALS_TITLE'],
                'TXT_CRM_DEALS_CUSTOMER_NAME'   => $_ARRAYLANG['TXT_CUSTOMER_NAME'],
                'TXT_CRM_DEALS_DUE_DATE'        => $_ARRAYLANG['TXT_DUE_DATE'],
                'TXT_CRM_DEALS_RESPONSIBLE'     => $_ARRAYLANG['CRM_PROJECT_RESPONSIBLE'],
                'TXT_CRM_OF_CONTACTS'           => $_ARRAYLANG['TXT_CRM_OF_CONTACTS'],
                'TXT_FUNCTIONS'                 => $_ARRAYLANG['TXT_FUNCTIONS'],
                'CRM_NO_RECORDS_FOUND'          => $_ARRAYLANG['CRM_NO_RECORDS_FOUND'],
                'CRM_CUSTOMER_ID'               => $custId,
                'TXT_CRM_ADD_OPPURTUNITY'       => $_ARRAYLANG['CRM_ADD_DEAL_TITLE'],
                'CSRF_PARAM'                    => CSRF::param(),
        ));
        $this->_objTpl->setGlobalVariable('CRM_REDIRECT_LINK' , '&redirect='.base64_encode("&act=showcustdetail&id={$custId}"));
        if (isset($_GET['ajax'])) {
            $this->_objTpl->hideBlock("skipAjaxBlock");
            $this->_objTpl->hideBlock("skipAjaxBlock1");
        } else {
            $this->_objTpl->touchBlock("skipAjaxBlock");
            $this->_objTpl->touchBlock("skipAjaxBlock1");
        }
        echo $objTpl->get();
        exit();
    }

    /**
     * Overview of opportunity
     */
    function dealsOverview() {
        global $objDatabase, $_ARRAYLANG;

        JS::activate("jquery");
        $tpl = isset($_GET['tpl']) ? $_GET['tpl'] : '';
        switch ($tpl) {
            case 'manage':
                $this->_modifyDeal();
                return;
                break;
            default:
                break;
        }

        $settings = $this->getSettings();
        $allowPm  = $this->isPmInstalled && $settings['allow_pm'];

        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile("module_{$this->moduleName}_deals_overview.html");
        $this->_pageTitle = $_ARRAYLANG['TXT_OPPORTUNITY'];

        $objTpl->setGlobalVariable(array(
                'MODULE_NAME'       => $this->moduleName,
                'PM_MODULE_NAME'    => $this->pm_moduleName
        ));

        $action = (isset ($_REQUEST['actionType'])) ? $_REQUEST['actionType'] : '';
        $dealsEntries = (isset($_REQUEST['dealsEntry'])) ? array_map('intval', $_REQUEST['dealsEntry']) : 0;

        switch ($action) {
            case 'delete':
                $this->deleteDeals($dealsEntries, $allowPm);
                break;
            case 'deletedeals':
                $this->deleteDeal($allowPm);
                if (isset($_GET['ajax']))
                    exit();
                break;
            default:
                break;
        }

        $mes = isset($_REQUEST['mes']) ? base64_decode($_REQUEST['mes']) : '';
        if(!empty($mes)) {
            switch($mes) {
            case "dealsAdded":
                $this->_strOkMessage = $_ARRAYLANG['TXT_DEALS_ADDED_SUCCESSFULLY'];
                break;
            case "dealsUpdated":
                $this->_strOkMessage = $_ARRAYLANG['TXT_DEALS_UPDATED_SUCCESSFULLY'];
                break;
            case "dealsdeleted":
                $this->_strOkMessage = $_ARRAYLANG['TXT_DEALS_DELETED_SUCCESSFULLY'];
                break;
            }
        }

        $searchLink = '';
        $where      = array();
        if (isset($_GET['term']) && !empty($_GET['term'])) {
            $where[] = " d.title LIKE '%".contrexx_input2raw($_GET['term'])."%' OR c.customer_name LIKE '%".contrexx_input2raw($_GET['term'])."%'";
            $searchLink = "&term={$_GET['term']}";
        }

        //  Join where conditions
        $filter = '';
        if (!empty ($where))
            $filter = " WHERE ".implode(' AND ', $where);

        $sortingFields = array("d.id", "d.title" ,  "c.customer_name", "u.username", "d.due_date");
        $sorto = (isset ($_GET['sorto'])) ? (((int) $_GET['sorto'] == 0) ? 'DESC' : 'ASC') : 'DESC';
        $sortf = (isset ($_GET['sortf']) && in_array($sortingFields[$_GET['sortf']], $sortingFields)) ? $sortingFields[$_GET['sortf']] : 'c.id';
        $sortLink = "&sorto={$_GET['sorto']}&sortf={$_GET['sortf']}";

        $query = "SELECT
                       d.id,
                       d.title,
                       d.quoted_price,
                       d.customer,
                       c.customer_name,
                       c.contact_familyname,
                       d.quote_number,
                       d.assigned_to,
                       d.due_date,
                       u.username
            FROM ".DBPREFIX."module_{$this->moduleName}_deals AS d
                LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_contacts AS c
            ON d.customer = c.id                
                LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` AS web
            ON d.website = web.id
                LEFT JOIN `".DBPREFIX."access_users` AS u
            ON u.id = d.assigned_to
                $filter
            ORDER BY $sortf $sorto";
        $objResult = $objDatabase->Execute($query);

        /* Start Paging ------------------------------------ */
        $intPos             = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        $intPerPage         = $this->getPagingLimit();
        $intPerPage         = 5;  //For Testing
        $this->_objTpl->setVariable('ENTRIES_PAGING', getPaging($this->countRecordEntries($query), $intPos, "./index.php?cmd={$this->moduleName}&act=deals$searchLink$sortLink", false, true, $intPerPage));

        $pageLink           = "&pos=$intPos";
        /* End Paging -------------------------------------- */

        $selectLimit = " LIMIT $intPos, $intPerPage";

        $query = $query. $selectLimit;

        $objResult = $objDatabase->Execute($query);

        if ($objResult) {
            if ($objResult->RecordCount() <= 0)
                $objTpl->touchBlock("dealsNoRecords");
            else
                $objTpl->hideBlock("dealsNoRecords");

            $row = "row2";
            while (!$objResult->EOF) {

                $objTpl->setVariable(array(
                        'ENTRY_ID'              => (int) $objResult->fields['id'],
                        'CRM_DEALS_TITLE'       => contrexx_raw2xhtml($objResult->fields['title']),
                        'CRM_CONTACT_NAME'      => "<a href='./index.php?cmd={$this->moduleName}&act=showcustdetail&id={$objResult->fields['customer']}' title='details'>".contrexx_raw2xhtml($objResult->fields['customer_name']." ".$objResult->fields['contact_familyname']).'</a>',
                        'CRM_DEALS_CONTACT_NAME'=> contrexx_raw2xhtml($objResult->fields['username']),
                        'CRM_DEALS_DUE_DATE'    => contrexx_raw2xhtml($objResult->fields['due_date']),
                        'ROW_CLASS'             => $row = ($row == "row2") ? "row1" : 'row2',
                        'CRM_REDIRECT_LINK'     => '&redirect='.base64_encode("&act=deals{$searchLink}{$sortLink}{$pageLink}"),
                        'TXT_IMAGE_EDIT'        =>  $_ARRAYLANG['TXT_EDIT'],
                        'TXT_IMAGE_DELETE'      =>  $_ARRAYLANG['TXT_DELETE'],
                ));
                $objTpl->parse("dealsEntries");
                $objResult->MoveNext();
            }
        }

        $sortOrder = ($_GET['sorto'] == 0) ? 1 : 0;
        $objTpl->setVariable(array(
                'CRM_NAME_SORT'                 => "&sortf=1&sorto=$sortOrder",
                'CRM_CUSTOMER_SORT'             => "&sortf=2&sorto=$sortOrder",
                'CRM_RESPONSIBLE_SORT'          => "&sortf=3&sorto=$sortOrder",
                'CRM_DUE_DATE_SORT'             => "&sortf=4&sorto=$sortOrder",
                'CRM_SEARCH_LINK'               => $searchLink,
                'TXT_CRM_SEARCH'                => $_ARRAYLANG['TXT_CRM_SEARCH'],
                'CRM_DEALS_CREATE'              => $_ARRAYLANG['CRM_DEALS_CREATE'],
                'CRM_DEALS_OVERVIEW'            => $_ARRAYLANG['CRM_DEALS_OVERVIEW'],
                'TXT_CRM_DEALS_OVERVIEW'        => $_ARRAYLANG['TXT_CRM_DEALS_OVERVIEW'],
                'TXT_CRM_DEALS_TITLE'           => $_ARRAYLANG['TXT_CRM_DEALS_TITLE'],
                'TXT_CRM_DEALS_CUSTOMER_NAME'   => $_ARRAYLANG['TXT_CUSTOMER_NAME'],
                'TXT_FUNCTIONS'                 => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_CRM_DEALS_CONTACT_PERSON'  => $_ARRAYLANG['TXT_CONTACT_PERSON'],
                'TXT_CRM_DEALS_DUE_DATE'        => $_ARRAYLANG['TXT_DUE_DATE'],
                'TXT_SELECT_ACTION'             =>  $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_DELETE_SELECTED'           =>  $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_SELECT_ALL'                =>  $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_REMOVE_SELECTION'          =>  $_ARRAYLANG['TXT_REMOVE_SELECTION'],
                'TXT_NO_RECORDS_FOUND'          =>  $_ARRAYLANG['CRM_NO_RECORDS_FOUND'],
                'TXT_SELECT_ENTRIES'            => $_ARRAYLANG['TXT_NO_OPERATION'],
                'TXT_CRM_FILTERS'               =>  $_ARRAYLANG['TXT_CRM_FILTERS'],
                'TXT_CRM_DEALS_RESPONSIBLE'     =>  $_ARRAYLANG['CRM_PROJECT_RESPONSIBLE'],
                'CRM_DEALS_SEARCH_TERM'         =>  contrexx_input2xhtml($_GET['term']),
                'TXT_CRM_ENTER_SEARCH_TERM'     => $_ARRAYLANG['TXT_CRM_ENTER_SEARCH_TERM'],
        ));
    }

    /**
     *  add /edit of deals
     */
    function _modifyDeal()
    {
        global $objDatabase, $_ARRAYLANG;

        JS::activate('cx');
        JS::activate('jqueryui');
        JS::registerCSS("lib/javascript/crm/css/main.css");
        JS::registerCSS("lib/javascript/crm/css/contact.css");

        $redirect     = $_REQUEST['redirect'] ? $_REQUEST['redirect'] : base64_encode('&act=deals');
        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile("module_{$this->moduleName}_deals_modify.html");
        $settings = $this->getSettings();
        $allowPm  = $this->isPmInstalled && $settings['allow_pm'];

        $objTpl->setGlobalVariable(array(
                'MODULE_NAME'       => $this->moduleName,
                'PM_MODULE_NAME'    => $this->pm_moduleName
        ));

        if ($allowPm) {
            include_once ASCMS_MODULE_PATH . '/pm/lib/pmLib.class.php';
            $objPmLib = new PmLibrary;
        }


        $id             = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

        $fields = array(
                'title'             => isset($_POST['title']) ? contrexx_input2raw($_POST['title']) : '',
                'website'           => isset($_POST['domain']) ? intval($_POST['domain']) : 0,
                'customer'          => isset($_REQUEST['customer']) ? (int) $_REQUEST['customer'] : 0,
                'customer_contact'  => isset($_POST['customer_contact']) ? (int) $_POST['customer_contact'] : 0,
                'quoted_price'      => isset($_POST['quoted_price']) ? contrexx_input2raw($_POST['quoted_price']) : '',
                'assigned_to'       => isset($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : FWUser::getFWUserObject()->objUser->getId(),
                'due_date'          => isset($_POST['due_date']) ? contrexx_input2raw($_POST['due_date']) : date("Y-m-d"),
                'description'       => isset($_POST['description']) ? contrexx_input2raw($_POST['description']) : '',                
                'stage'             => isset($_POST['dealsStage']) ? contrexx_input2raw($_POST['dealsStage']) : '',
        );

        $projectFileds = array();
        if ($allowPm)
            $projectFileds = array(
                    'invoice_type'      => isset($_POST['invoiceType']) ? (int) $_POST['invoiceType'] : 3,
                    'project_type'      => isset($_POST['project_type']) ? (int) $_POST['project_type'] : 0,
                    'project_status'    => isset($_POST['status']) ? (int) $_POST['status'] : 0,
                    'priority'          => isset($_POST['priority']) ? contrexx_input2raw($_POST['priority']) : 0,
                    'send_invoice'      => isset($_POST['send-partial-invoice']) ? 1 : 0,
                    'hrs_offered'       => isset($_POST['projectDuration']) ? (float) $_POST['projectDuration'] : '',
                    'quote_number'      => isset($_POST['quoteNumber']) ? contrexx_input2raw($_POST['quoteNumber']) : '',
                    'bill_info'         => isset($_POST['billing_info']) ? contrexx_input2raw($_POST['billing_info']) : '',
            );

        if (isset ($_POST['save_deal'])) {
            if (true) {
                $fields['website'] = $this->_getDomainNameId($fields['website'], $fields['customer'], contrexx_input2raw($_POST['domainName']));

                if (!empty($id)) {
                    $query = SQL::update("module_{$this->moduleName}_deals", $fields, array('escape' => true))." WHERE `id` = $id";
                } else {
                    $query = SQL::insert("module_{$this->moduleName}_deals", $fields, array('escape' => true));
                }

                //print $query;
                $db = $objDatabase->Execute($query);

                $msg =  empty($id) ? 'dealsAdded' : 'dealsUpdated';

                if (empty($id))
                    $id = $objDatabase->INSERT_ID();

                $projectId = $objDatabase->getOne("SELECT project_id FROM `".DBPREFIX."module_{$this->moduleName}_deals` WHERE id = $id");

                if ($db) {

                    if ($allowPm) {
                        $saveProjects = array(
                                'name'                  => $fields['title'],
                                'quoteNumber'           => $projectFileds['quote_number'],
                                'domain'                => $fields['website'],
                                'customer_id'           => $fields['customer'],
                                'project_type_id'       => $projectFileds['project_type'],
                                'added_by'              => FWUser::getFWUserObject()->objUser->getId(),
                                'assigned_to'           => $fields['assigned_to'],
                                'status'                => $projectFileds['project_status'],
                                'priority'              => $projectFileds['priority'],
                                'contact_id'            => $fields['customer_contact'],
                                'quoted_price'          => $fields['quoted_price'],
                                'projectDuration'       => $projectFileds['hrs_offered'],
                                'billing_info'          => $projectFileds['bill_info'],
                                'description'           => $fields['description'],
                                'send_partial_invoice'  => $projectFileds['send_invoice'],
                                'internal'              => $projectFileds['invoice_type'],
                                'target_date'           => isset($_POST['dueDate']) ? contrexx_input2raw($_POST['dueDate']) : date("Y-m-d"),
                                'billtype'              => 1
                        );
                        $projectId = $objPmLib->saveOppurtunityProject($saveProjects, $projectId);

                        if (isset($_FILES['documentUpload'])) {
                            $inputName         = 'documentUpload';
                            $date              = date('Y-m-d');
                            $docTitle          = '';
                            $uploadedUserId    = 0;
                            $objPmLib->uploadProjectDocument($inputName, $date, $projectId, $docTitle, $uploadedUserId);
                        }
                        // Update project id to the oppurtunity.
                        $objDatabase->Execute("UPDATE `".DBPREFIX."module_{$this->moduleName}_deals` SET project_id = $projectId WHERE id = $id");
                    }

                    //print base64_decode($redirect);
                    csrf::header("Location:./index.php?cmd={$this->moduleName}&mes=".base64_encode($msg).base64_decode($redirect));
                    $this->_strOkMessage = "Saved successfully";
                } else {
                    $this->_strErrMessage = "Err in saving";
                }
            } else {
                $this->_strErrMessage = "All fields must be filled out";
            }
        } elseif (!empty($id)) {

            $objResult = $objDatabase->Execute("SELECT d.title,
                                       d.website,
                                       web.url AS siteName,
                                       d.quoted_price,
                                       d.customer,
                                       c.customer_name AS customerName,
                                       c.contact_familyname AS customerFamilyName,
                                       d.customer_contact,
                                       d.quote_number,
                                       d.assigned_to,
                                       d.due_date,
                                       d.description,                                       
                                       d.stage,
                                       d.project_id
                            FROM ".DBPREFIX."module_{$this->moduleName}_deals AS d
                                LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_contacts AS c
                            ON d.customer = c.id                                
                                LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` AS web
                            ON d.website = web.id                                
                            WHERE d.id = $id");

            $fields = array(
                    'title'             => $objResult->fields['title'],
                    'website'           => $objResult->fields['website'],
                    'siteName'          => $objResult->fields['siteName'],
                    'customer'          => $objResult->fields['customer'],
                    'customer_name'     => $objResult->fields['customerName']." ".$objResult->fields['customerFamilyName'],
                    'customer_contact'  => $objResult->fields['customer_contact'],
                    'quoted_price'      => $objResult->fields['quoted_price'],
                    'assigned_to'       => $objResult->fields['assigned_to'],
                    'due_date'          => $objResult->fields['due_date'],
                    'description'       => $objResult->fields['description'],                    
                    'stage'             => $objResult->fields['stage'],
            );

            $projectId = (int) $objResult->fields['project_id'];
            if ($allowPm && !empty($projectId)) {
                $objProjectResult = $objDatabase->Execute("SELECT p.internal,
                                                            p.project_type_id,
                                                            p.status,
                                                            p.priority,
                                                            p.send_partial_invoice,
                                                            p.projectDuration,
                                                            p.quoteNumber,
                                                            p.billing_info
                                                        FROM `".DBPREFIX."module_{$this->pm_moduleName}_projects` AS p
                                                         WHERE id = $projectId");

                $projectFileds = array(
                        'invoice_type'      => $objProjectResult->fields['internal'],
                        'project_type'      => $objProjectResult->fields['project_type_id'],
                        'project_status'    => $objProjectResult->fields['status'],
                        'priority'          => $objProjectResult->fields['priority'],
                        'send_invoice'      => $objProjectResult->fields['send_partial_invoice'],
                        'hrs_offered'       => $objProjectResult->fields['projectDuration'],
                        'quote_number'      => $objProjectResult->fields['quoteNumber'],
                        'bill_info'         => $objProjectResult->fields['billing_info'],
                );

            }
        }

        if (!empty($fields['customer'])) {
            $contactType = $objDatabase->getOne("SELECT contact_type FROM `".DBPREFIX."module_crm_contacts` WHERE id = {$fields['customer']}");
            $contatWhere = $contactType == 1 ? "c.contact_customer" : "c.id";
            $objContactPerson = $objDatabase->Execute("SELECT c.`id`,
                                                          c.`contact_customer`,
                                                          c.`customer_name`,
                                                          c.`contact_familyname`,
                                                          email.`email`
                                                   FROM `".DBPREFIX."module_crm_contacts` AS c
                                                   LEFT JOIN `".DBPREFIX."module_crm_customer_contact_emails` AS email
                                                       ON c.`id` = email.`contact_id` AND email.`is_primary` = '1'
                                                   WHERE $contatWhere = '{$fields['customer']}' AND (status=1 OR c.id ='{$fields['customer_contact']}')");

            while (!$objContactPerson->EOF) {
                $selected = ($fields['customer_contact'] ==  $objContactPerson->fields['id']) ? "selected" : '';
                $contactName = $objContactPerson->fields['customer_name']." ".$objContactPerson->fields['contact_familyname'];

                $this->_objTpl->setVariable(array(
                        'TXT_CONTACT_ID'   =>	(int) $objContactPerson->fields['id'] ,
                        'TXT_CONTACT_NAME' =>   contrexx_raw2xhtml($contactName),
                        'TXT_SELECTED'     =>   $selected));
                $this->_objTpl->parse('Contacts');
                $objContactPerson->MoveNext();
            }

            // Get customer Name
            $objCustomer = $objDatabase->Execute("SELECT customer_name, contact_familyname  FROM `".DBPREFIX."module_crm_contacts` WHERE id = {$fields['customer']}");
            $fields['customer_name'] = $objCustomer->fields['customer_name']." ".$objCustomer->fields['contact_familyname'];
        }
        
        $this->getDealsStages($fields['stage']);
        $this->_getResourceDropDown('Members', $fields['assigned_to'], $settings['emp_default_user_group']);

        if ($allowPm) {
            $objPmLib->getProjectTypeDropdown($objTpl, $projectFileds['project_type']);
            $objPmLib->getProjectStatusDropdown($objTpl, $projectFileds['project_status']);
            $objPmLib->getProjectPriorityDropdown($objTpl, $projectFileds['priority']);

            $objTpl->setvariable(array(
                    'PROJECT_BILLING_INFO'              => new \Cx\Core\Wysiwyg\Wysiwyg('billing_info', html_entity_decode($projectFileds['bill_info'], ENT_QUOTES, CONTREXX_CHARSET), 'pm_fullpage'),
                    'PROJECT_INVOICETYPE_PROJECT'       => ($projectFileds['invoice_type'] == 3) ? 'checked=checked' : '',
                    'PROJECT_INVOICETYPE_COLLECTIVE'    => ($projectFileds['invoice_type'] == 2) ? 'checked=checked' : '',
                    'PROJECT_INVOICETYPE_INTERNAL'      => ($projectFileds['invoice_type'] == 1) ? 'checked=checked' : '',
                    'PM_SEND_PARTIAL_INVOICE_CHECKED'   => !empty($projectFileds['send_invoice']) ? 'checked' : '',
                    'CRM_QUOTATION_NUMBER'              => contrexx_raw2xhtml($projectFileds['quote_number']),
                    'PROJECT_DURATION'                  => number_format($projectFileds['hrs_offered'], 2),

                    'TXT_ADDITIONAL_INFO'               => $_ARRAYLANG['TXT_ADDITIONAL_INFO'],
                    'TXT_PM_INVOICE_TYPE'               => $_ARRAYLANG['TXT_PM_INVOICE_TYPE'],
                    'TXT_PROJECT_INVOICETYPE_PROJECT'   => $_ARRAYLANG['TXT_PROJECT_INVOICETYPE_PROJECT'],
                    'TXT_PROJECT_INVOICETYPE_COLLECTIVE'=> $_ARRAYLANG['TXT_PROJECT_INVOICETYPE_COLLECTIVE'],
                    'TXT_PROJECT_INVOICETYPE_INTERNAL'  => $_ARRAYLANG['TXT_PROJECT_INVOICETYPE_INTERNAL'],
                    'TXT_PROJECT_TYPE'                  => $_ARRAYLANG['TXT_PROJECT_TYPE'],
                    'TXT_SELECT_PROJECT_TYPE'           => $_ARRAYLANG['TXT_SELECT_PROJECT_TYPE'],
                    'TXT_PROJECT_STATUS'                => $_ARRAYLANG['TXT_PROJECT_STATUS'],
                    'TXT_PRIORITY'                      => $_ARRAYLANG['TXT_PRIORITY'],
                    'TXT_SELECT_PRIORITY'               => $_ARRAYLANG['TXT_SELECT_PRIORITY'],
                    'TXT_LOW'                           => $_ARRAYLANG['TXT_LOW'],
                    'TXT_MEDIUM'                        => $_ARRAYLANG['TXT_MEDIUM'],
                    'TXT_HIGH'                          => $_ARRAYLANG['TXT_HIGH'],
                    'TXT_PM_SEND_PARTIAL_INVOICE'       => $_ARRAYLANG['TXT_PM_SEND_PARTIAL_INVOICE'],
                    'TXT_PM_PROJECT_DURATION'           => $_ARRAYLANG['TXT_PM_PROJECT_DURATION'],
                    'TXT_QUOTE_NUMBER'                  => $_ARRAYLANG['TXT_QUOTE_NUMBER'],
                    'TXT_PM_DOCUMENT_UPLOAD'            => $_ARRAYLANG['TXT_PM_DOCUMENT_UPLOAD'],
                    'TXT_BILLING_INFORMATION'           => $_ARRAYLANG['TXT_BILLING_INFORMATION'],
            ));
            if (!empty($id))
                $objTpl->hideBlock("projectDocUpload");
        }

        if (!$allowPm)
            $objTpl->hideBlock("projectEntryBlock");

        $objTpl->setVariable(array(
                'CRM_DEALS_OVERVIEW'            => $_ARRAYLANG['CRM_DEALS_OVERVIEW'],
                'TXT_CRM_DEALS_CUSTOMER_NAME'   => $_ARRAYLANG['TXT_CUSTOMER_NAME'],
                'TXT_CRM_DEALS_CONTACT_PERSON'  => $_ARRAYLANG['TXT_CONTACT_PERSON'],
                'TXT_CRM_DEALS_QUOTED_PRICE'    => $_ARRAYLANG['CRM_PROJECT_QUOTED_PRICE'],
                'TXT_CRM_DEALS_ESTIMATED_HOURS' => $_ARRAYLANG['TXT_CRM_DEALS_ESTIMATED_HOURS'],
                'TXT_CRM_DEALS_QUOTE_NUMBER'    => $_ARRAYLANG['TXT_CRM_DEALS_QUOTE_NUMBER'],
                'TXT_CRM_DEALS_RESPONSIBLE'     => $_ARRAYLANG['CRM_PROJECT_RESPONSIBLE'],
                'TXT_CRM_DEALS_DUE_DATE'        => $_ARRAYLANG['TXT_DUE_DATE'],
                'TXT_CRM_DEALS_STAGES'          => $_ARRAYLANG['TXT_CRM_DEALS_STAGES'],
                'TXT_CRM_DEALS_SUCC_RATE'       => $_ARRAYLANG['TXT_CRM_DEALS_SUCC_RATE'],
                'TXT_SAVE'                      => $_ARRAYLANG['TXT_SAVE'],
                'CRM_MODIFY_DEAL_TITLE'         => empty($id) ? $_ARRAYLANG['CRM_ADD_DEAL_TITLE'] : $_ARRAYLANG['CRM_EDIT_DEAL_TITLE'],

                'CRM_DEALS_TITLE'               => contrexx_raw2xhtml($fields['title']),
                'PM_PROJECT_DOMAIN_ID'          => (int) $fields['website'],
                'PM_PROJECT_DOMAIN_NAME'        => contrexx_raw2xhtml($fields['siteName']),
                'CRM_DEALS_CUSTOMER'            => (int) $fields['customer'],
                'CRM_DEALS_CUSTOMER_NAME'       => contrexx_raw2xhtml($fields['customer_name']),
                'CRM_DEALS_QUOTED_PRICE'        => contrexx_raw2xhtml($fields['quoted_price']),
                'DEALS_DUE_DATE'                => contrexx_raw2xhtml($fields['due_date']),
                'CRM_REDIRECT_LINK'             => $redirect,
                'CRM_DEALS_DESCRIPTION'         => new \Cx\Core\Wysiwyg\Wysiwyg('description', html_entity_decode($fields['description'], ENT_QUOTES, CONTREXX_CHARSET), 'pm_small'),
                'TXT_CRM_DEALS_TITLE'           => $_ARRAYLANG['TXT_CRM_DEALS_TITLE'],
                'TXT_SELECT_MEMBER_NAME'        => $_ARRAYLANG['TXT_SELECT_MEMBER_NAME'],
                'CRM_MODIFY_DEAL_DESCRIPTION'   => $_ARRAYLANG['TXT_DESCRIPTION'],
        ));

        $this->_pageTitle = empty($id) ? $_ARRAYLANG['CRM_ADD_DEAL_TITLE'] : $_ARRAYLANG['CRM_EDIT_DEAL_TITLE'];
    }

    function showIndustry()
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        JS::activate("jquery");

        $fn = isset($_GET['fn']) ? $_GET['fn'] : '';
        if (!empty ($fn)) {
            switch ($fn) {
            case 'modify':
                $this->_modifyIndustry();
                return;
                break;
            }            
        }

        $action             = (isset($_REQUEST['actionType'])) ? $_REQUEST['actionType'] : '';
        $indusEntries       = (isset($_REQUEST['indusEntry'])) ? array_map('intval', $_REQUEST['indusEntry']) : 0;
        $indusEntriesorting = (isset($_REQUEST['sorting'])) ? array_map('intval', $_REQUEST['sorting']) : 0;

        if (isset($_SESSION['strOkMessage'])) {
            $strMessage = is_array($_SESSION['strOkMessage']) ? implode("<br>", $_SESSION['strOkMessage']) : $_SESSION['strOkMessage'];
            $this->_strOkMessage = $strMessage;
            unset($_SESSION['strOkMessage']);
        }

        switch ($action) {
        case 'changestatus':
            $this->activateIndustryType((int) $_GET['id']);
            if (isset($_GET['ajax']))
                exit();
        case 'activate':
            $this->activateIndustryType($indusEntries);
            break;
        case 'deactivate':
            $this->activateIndustryType($indusEntries, true);
            break;
        case 'delete':
            $this->deleteIndustryTypes($indusEntries);
            break;
        case 'deleteIndustryType':
            $this->deleteIndustryType();
            if (isset($_GET['ajax']))
                exit();
            break;
        default:
            break;
        }
        if (!empty ($action) || isset($_POST['save_entries'])) {
            $this->saveSortingIndustryType($indusEntriesorting);
        }

        $objTpl = $this->_objTpl;
        $objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_industry.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName,
                'TXT_IMAGE_EDIT' => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                'TXT_IMAGE_DELETE' => $_ARRAYLANG['TXT_IMAGE_DELETE'],
        ));
        
        $name     = isset($_POST['name']) ? contrexx_input2raw($_POST['name']) : '';
        $sorting  = isset($_POST['sortingNumber']) ? (int) $_POST['sortingNumber'] : '';
        $status   = isset($_POST['activeStatus']) ? 1 : (empty($_POST) ? 1 : 0);
        $parentId = isset($_POST['parentId']) ? (int) $_POST['parentId'] : 0;

        $industryType = isset($_POST['Inputfield']) ? $_POST['Inputfield'] : array();

        if (isset ($_POST['save_entry'])) {
            $error = false;
            $fields = array(
                    'parent_id'     => $parentId,
                    'sorting'       => $sorting,
                    'status'        => $status
            );

            $field_set = '';
            foreach ($fields as $col => $val) {
                if ($val !== null) {
                    $field_set[] = "`$col` = '".contrexx_input2db($val)."'";
                }
            }
            $field_set = implode(', ', $field_set);

            if (!$error) {
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_industry_types` SET
                            $field_set";
                $db = $objDatabase->Execute($query);
                $entryId = !empty($id) ? $id : $objDatabase->INSERT_ID();

                // Insert the name locale
                if ($db) {
                    $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_industry_type_local` WHERE entry_id = $entryId");
                    foreach ($this->_arrLanguages as $langId => $langValue) {
                        $value = empty($industryType[$langId]) ? contrexx_input2db($industryType[0]) : contrexx_input2db($industryType[$langId]);
                        $objDatabase->Execute("
                            INSERT INTO `".DBPREFIX."module_{$this->moduleName}_industry_type_local` SET
                                `entry_id` = $entryId,
                                `lang_id`   = $langId,
                                `value`    = '$value'
                                ");
                    }
                }

                if ($db) {
                    $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_ENTRY_ADDED_SUCCESS'];
                } else {
                    $this->_strErrMessage = "Error in saving Data";
                }
            }
        }
        $this->listIndustryTypes($objTpl, 1);
        $first = true;
        foreach ($this->_arrLanguages as $langId => $langValue) {

            ($first) ? $objTpl->touchBlock("minimize") : $objTpl->hideBlock("minimize");
            $first = false;

            $objTpl->setVariable(array(
                    'LANG_ID'                 => $langId,
                    'LANG_LONG_NAME'          => $langValue['long'],
                    'LANG_SHORT_NAME'         => $langValue['short'],
                    'CRM_INDUSTRY_NAME_VALUE' => isset($industryType[$langId]) ? contrexx_raw2xhtml($industryType[$langId]) : ''
            ));
            $objTpl->parse("industryTypeNames");
        }

        $objTpl->setGlobalVariable(array(
                'TXT_CRM_MORE'              => $_ARRAYLANG['TXT_CRM_MORE'],
                'TXT_CRM_MINIMIZE'          => $_ARRAYLANG['TXT_CRM_MINIMIZE']
        ));
        $objTpl->setVariable(array(
                'DEFAULT_LANG_ID'           => $_LANGID,
                'LANG_ARRAY'                => implode(',', array_keys($this->_arrLanguages)),
                'CRM_PARENT_INDUSTRY_DROPDOWN'    => $this->listIndustryTypes($this->_objTpl, 2, $parentId),
                'TXT_CRM_CUSTOMER_INDUSTRY' => $_ARRAYLANG['TXT_CRM_CUSTOMER_INDUSTRY'],
                'TXT_OVERVIEW'           => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_ADD_INDUSTRY'       => $_ARRAYLANG['TXT_ADD_INDUSTRY'],
                'TXT_STATUS'             => $_ARRAYLANG['TXT_STATUS'],
                'TXT_CRM_LABEL'          => $_ARRAYLANG['TXT_NAME'],
                'TXT_CRM_ADD_STAGE'      => $_ARRAYLANG['TXT_CRM_ADD_STAGE'],                
                'TXT_SAVE'               => $_ARRAYLANG['TXT_SAVE'],
                'TXT_CRM_DEALS_STAGES'   => $_ARRAYLANG['TXT_CRM_DEALS_STAGES'],
                'TXT_CRM_DEALS_STAGE'    => $_ARRAYLANG['TXT_CRM_DEALS_STAGE'],
                'TXT_SORTING'            => $_ARRAYLANG['TXT_SORTING'],
                'TXT_FUNCTIONS'          => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ALL'         => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_REMOVE_SELECTION'   => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
                'TXT_SELECT_ACTION'      => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_ACTIVATESELECTED'   => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED' => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_DELETE_SELECTED'    => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_CHANGE_STATUS'         => $_ARRAYLANG['TXT_CHANGE_STATUS'],
                'TXT_ENTRY_DELETED_SUCCESS' => $_ARRAYLANG['TXT_ENTRY_DELETED_SUCCESS'],
                'TXT_OVERVIEW'              => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_NAME'                  => $_ARRAYLANG['TXT_NAME'],
                'TXT_TITLEACTIVE'           => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_SORTING_NUMBER'        => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
                'TXT_PARENT_INDUSTRY_TYPE'  => $_ARRAYLANG['TXT_PARENT_INDUSTRY_TYPE'],
                'TXT_CRM_NEW_INDUSTRY_TYPE' => $_ARRAYLANG['TXT_CRM_NEW_INDUSTRY_TYPE'],
                'TXT_TITLE_MODIFY_INDUSTRY' => $_ARRAYLANG['TXT_ADD_INDUSTRY'],
        ));

    }

    function _modifyIndustry()
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        JS::activate("jquery");
        $objTpl = $this->_objTpl;
        $objTpl->addBlockfile('CRM_SETTINGS_FILE', 'settings_block', 'module_'.$this->moduleName.'_settings_industry_modify.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName,
                'TXT_IMAGE_EDIT' => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                'TXT_IMAGE_DELETE' => $_ARRAYLANG['TXT_IMAGE_DELETE'],
        ));

        if (isset($_SESSION['strOkMessage'])) {
            $strMessage = is_array($_SESSION['strOkMessage']) ? implode("<br>", $_SESSION['strOkMessage']) : $_SESSION['strOkMessage'];
            $this->_strOkMessage = $strMessage;
            unset($_SESSION['strOkMessage']);
        }
        
        $id       = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $name     = isset($_POST['name']) ? contrexx_input2raw($_POST['name']) : '';
        $sorting  = isset($_POST['sortingNumber']) ? (int) $_POST['sortingNumber'] : '';
        $status   = isset($_POST['activeStatus']) ? 1 : (empty($_POST) ? 1 : 0);
        $parentId = isset($_POST['parentId']) ? (int) $_POST['parentId'] : 0;
        
        $industryType = isset($_POST['Inputfield']) ? $_POST['Inputfield'] : array();
        if (isset ($_POST['save_entry'])) {
            $error = false;
            $fields = array(
                    'parent_id'     => $parentId,
                    'sorting'       => $sorting,
                    'status'        => $status
            );

            $field_set = '';
            foreach ($fields as $col => $val) {
                if ($val !== null) {
                    $field_set[] = "`$col` = '".contrexx_input2db($val)."'";
                }
            }
            $field_set = implode(', ', $field_set);

            if (!empty($id) && ($id == $parentId)) {
                $this->_strErrMessage = "Choose different parent id";
                $error = true;
            }

            if (!$error) {
                if (!empty($id)) {
                    $query = "UPDATE `".DBPREFIX."module_{$this->moduleName}_industry_types` SET
                            $field_set
                      WHERE `id` = $id";
                    $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_ENTRY_UPDATED_SUCCESS'];
                } else {
                    $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_industry_types` SET
                            $field_set";
                    $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_ENTRY_ADDED_SUCCESS'];
                }
                $db = $objDatabase->Execute($query);
                $entryId = !empty($id) ? $id : $objDatabase->INSERT_ID();

                // Insert the name locale
                if ($db) {
                    $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_industry_type_local` WHERE entry_id = $entryId");
                    foreach ($this->_arrLanguages as $langId => $langValue) {
                        $value = empty($industryType[$langId]) ? contrexx_input2db($industryType[0]) : contrexx_input2db($industryType[$langId]);
                        $objDatabase->Execute("
                            INSERT INTO `".DBPREFIX."module_{$this->moduleName}_industry_type_local` SET
                                `entry_id` = $entryId,
                                `lang_id`   = $langId,
                                `value`    = '$value'
                                ");
                    }
                }

                if ($db) {
                    CSRF::header("Location:./?cmd={$this->moduleName}&act=settings&tpl=industry");
                    exit();
                } else {
                    $this->_strErrMessage = "Error in saving Data";
                }
            }            
        } elseif (!empty($id)) {
            $objResult = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_industry_types` WHERE id = $id");

            $name     = $objResult->fields['industry_type'];
            $sorting  = $objResult->fields['sorting'];
            $status   = $objResult->fields['status'];
            $parentId = $objResult->fields['parent_id'];

            $objInputFields = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_industry_type_local` WHERE entry_id = $id");
            while (!$objInputFields->EOF) {
                $industryType[$objInputFields->fields['lang_id']] = $objInputFields->fields['value'];
                $objInputFields->MoveNext();
            }
        }
        
        $first = true;
        foreach ($this->_arrLanguages as $langId => $langValue) {

            ($first) ? $objTpl->touchBlock("minimize") : $objTpl->hideBlock("minimize");
            $first = false;

            $objTpl->setVariable(array(
                    'LANG_ID'                 => $langId,
                    'LANG_LONG_NAME'          => $langValue['long'],
                    'LANG_SHORT_NAME'         => $langValue['short'],
                    'CRM_INDUSTRY_NAME_VALUE' => isset($industryType[$langId]) ? contrexx_raw2xhtml($industryType[$langId]) : ''
            ));
            $objTpl->parse("industryTypeNames");
        }

        $objTpl->setGlobalVariable(array(
                'MODULE_NAME'               => $this->moduleName,
                'TXT_CRM_MORE'              => $_ARRAYLANG['TXT_CRM_MORE'],
                'TXT_CRM_MINIMIZE'          => $_ARRAYLANG['TXT_CRM_MINIMIZE']
        ));        
        $objTpl->setVariable(array(
                'CRM_INDUSTRY_NAME_DEFAULT_VALUE' => isset($industryType[$_LANGID]) ? contrexx_raw2xhtml($industryType[$_LANGID]) : '',
                'CRM_PARENT_INDUSTRY_DROPDOWN'    => $this->listIndustryTypes($this->_objTpl, 2, $parentId),
                'CRM_ACTIVATED_VALUE'       => $status ? "checked='checked'" : '',                
                'CRM_SORTINGNUMBER'         => $sorting,
                'DEFAULT_LANG_ID'           => $_LANGID,
                'LANG_ARRAY'                => implode(',', array_keys($this->_arrLanguages)),
                'TXT_CRM_CUSTOMER_INDUSTRY' => $_ARRAYLANG['TXT_CRM_CUSTOMER_INDUSTRY'],
                'TXT_OVERVIEW'              => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_NAME'                  => $_ARRAYLANG['TXT_NAME'],
                'TXT_TITLEACTIVE'           => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_SORTING_NUMBER'        => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
                'TXT_PARENT_INDUSTRY_TYPE'  => $_ARRAYLANG['TXT_PARENT_INDUSTRY_TYPE'],
                'TXT_CRM_NEW_INDUSTRY_TYPE' => $_ARRAYLANG['TXT_CRM_NEW_INDUSTRY_TYPE'],
                'TXT_TITLE_MODIFY_INDUSTRY' => (!empty ($id)) ? $_ARRAYLANG['TXT_EDIT_INDUSTRY'] : $_ARRAYLANG['TXT_ADD_INDUSTRY'],
                'CSRF_PARAM'                => CSRF::param(),
        ));
    }

    function showMembership()
    {
        global $objDatabase,$_ARRAYLANG, $_LANGID;

        JS::activate("jquery");

        $tpl = isset($_GET['tpl']) ? $_GET['tpl'] : '';
        if (!empty ($tpl)) {
            switch ($tpl) {
            case 'modify':
                $this->_modifyMembership();
                break;
            }
            return;
        }

        $action              = (isset($_REQUEST['actionType'])) ? $_REQUEST['actionType'] : '';
        $memberEntries       = (isset($_REQUEST['memberEntry'])) ? array_map('intval', $_REQUEST['memberEntry']) : 0;
        $memberEntriesorting = (isset($_REQUEST['sorting'])) ? array_map('intval', $_REQUEST['sorting']) : 0;

        if (isset($_SESSION['strOkMessage'])) {
            $strMessage = is_array($_SESSION['strOkMessage']) ? implode("<br>", $_SESSION['strOkMessage']) : $_SESSION['strOkMessage'];
            $this->_strOkMessage = $strMessage;
            unset($_SESSION['strOkMessage']);
        }

        switch ($action) {
        case 'changestatus':
            $this->activateMembership((int) $_GET['id']);
            if (isset($_GET['ajax']))
                exit();
        case 'activate':
            $this->activateMembership($memberEntries);
            break;
        case 'deactivate':
            $this->activateMembership($memberEntries, true);
            break;
        case 'delete':
            $this->deleteMemberships($memberEntries);
            break;
        case 'deleteMembership':
            $this->deleteMembership();
            if (isset($_GET['ajax']))
                exit();
            break;
        default:
            break;
        }
        if (!empty ($action) || isset($_POST['save_entries'])) {
            $this->saveSortingMembership($memberEntriesorting);
        }

        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile('module_'.$this->moduleName.'_settings_membership.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_CRM_CUSTOMER_MEMBERSHIP'];
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName,
                'TXT_IMAGE_EDIT' => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                'TXT_IMAGE_DELETE' => $_ARRAYLANG['TXT_IMAGE_DELETE'],
        ));

        $query = "SELECT membership.*,
                         memberLoc.value,
                         (SELECT COUNT(1) FROM
                            `".DBPREFIX."module_{$this->moduleName}_customer_membership` as m
                            WHERE m.membership_id = membership.id)
                         as cusCount
                     FROM `".DBPREFIX."module_{$this->moduleName}_memberships` AS membership
                     LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_membership_local` AS memberLoc
                        ON membership.id = memberLoc.entry_id
                     WHERE memberLoc.lang_id = ".$_LANGID." ORDER BY sorting ASC ";
        $objResult = $objDatabase->Execute($query);

        if ($objResult && $objResult->RecordCount() == 0) {
            $objTpl->setVariable(array(
                    'TXT_NO_RECORDS_FOUND'  =>  $_ARRAYLANG['CRM_NO_RECORDS_FOUND']
            ));
        }
        while (!$objResult->EOF) {
            $activeImage = ($objResult->fields['status']) ? 'images/icons/led_green.gif' : 'images/icons/led_red.gif';
            $objTpl->setVariable(array(
                    'ENTRY_ID'          => $objResult->fields['id'],
                    'CRM_SORTING'       => (int) $objResult->fields['sorting'],
                    'CRM_SUCCESS_STATUS' => $activeImage,
                    'CRM_CUSTOMER_COUNT' => (int) $objResult->fields['cusCount'],
                    'CRM_INDUSTRY_NAME' => contrexx_raw2xhtml($objResult->fields['value'])
            ));
            $objTpl->parse("membershipEntries");
            $objResult->MoveNext();
        }

        $objTpl->setVariable(array(
                'TXT_CRM_CUSTOMER_MEMBERSHIP' => $_ARRAYLANG['TXT_CRM_CUSTOMER_MEMBERSHIP'],
                'TXT_ADD_MEMBERSHIP'     => $_ARRAYLANG['TXT_ADD_MEMBERSHIP'],
                'TXT_STATUS'             => $_ARRAYLANG['TXT_STATUS'],
                'TXT_CRM_LABEL'          => $_ARRAYLANG['TXT_NAME'],                
                'TXT_SAVE'               => $_ARRAYLANG['TXT_SAVE'],
                'TXT_SORTING'            => $_ARRAYLANG['TXT_SORTING'],
                'TXT_FUNCTIONS'          => $_ARRAYLANG['TXT_FUNCTIONS'],
                'TXT_SELECT_ALL'         => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_REMOVE_SELECTION'   => $_ARRAYLANG['TXT_REMOVE_SELECTION'],
                'TXT_SELECT_ACTION'      => $_ARRAYLANG['TXT_SELECT_ACTION'],
                'TXT_ACTIVATESELECTED'   => $_ARRAYLANG['TXT_ACTIVATESELECTED'],
                'TXT_DEACTIVATESELECTED' => $_ARRAYLANG['TXT_DEACTIVATESELECTED'],
                'TXT_DELETE_SELECTED'    => $_ARRAYLANG['TXT_DELETE_SELECTED'],
                'TXT_CHANGE_STATUS'         => $_ARRAYLANG['TXT_CHANGE_STATUS'],
                'TXT_ENTRY_DELETED_SUCCESS' => $_ARRAYLANG['TXT_ENTRY_DELETED_SUCCESS'],
        ));

    }

    function _modifyMembership()
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        JS::activate("jquery");
        $objTpl = $this->_objTpl;
        $objTpl->loadTemplateFile('module_'.$this->moduleName.'_settings_membership_modify.html');        
        $objTpl->setGlobalVariable(array(
                'MODULE_NAME' => $this->moduleName,
                'TXT_IMAGE_EDIT' => $_ARRAYLANG['TXT_IMAGE_EDIT'],
                'TXT_IMAGE_DELETE' => $_ARRAYLANG['TXT_IMAGE_DELETE'],
        ));

        $id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $name    = isset($_POST['name']) ? contrexx_input2raw($_POST['name']) : '';
        $sorting = isset($_POST['sortingNumber']) ? (int) $_POST['sortingNumber'] : '';
        $status  = isset($_POST['activeStatus']) ? 1 : (empty($_POST) ? 1 : 0);
        
        $this->_pageTitle = (!empty ($id)) ? $_ARRAYLANG['TXT_EDIT_MEMBERSHIP'] : $_ARRAYLANG['TXT_ADD_MEMBERSHIP'];

        $inputField = isset($_POST['Inputfield']) ? $_POST['Inputfield'] : array();
        if (isset ($_POST['save_entry'])) {
            $fields = array(
                    'sorting'       => $sorting,
                    'status'        => $status
            );

            $field_set = '';
            foreach ($fields as $col => $val) {
                if ($val !== null) {
                    $field_set[] = "`$col` = '".contrexx_input2db($val)."'";
                }
            }
            $field_set = implode(', ', $field_set);

            if (!empty($id)) {
                $query = "UPDATE `".DBPREFIX."module_{$this->moduleName}_memberships` SET
                        $field_set
                  WHERE `id` = $id";
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_ENTRY_UPDATED_SUCCESS'];
            } else {
                $query = "INSERT INTO `".DBPREFIX."module_{$this->moduleName}_memberships` SET
                        $field_set";
                $_SESSION['strOkMessage'] = $_ARRAYLANG['TXT_ENTRY_ADDED_SUCCESS'];
            }
            $db = $objDatabase->Execute($query);
            $entryId = !empty($id) ? $id : $objDatabase->INSERT_ID();

            // Insert the name locale
            if ($db) {
                $objDatabase->Execute("DELETE FROM `".DBPREFIX."module_{$this->moduleName}_membership_local` WHERE entry_id = $entryId");
                foreach ($this->_arrLanguages as $langId => $langValue) {
                    $value = empty($inputField[$langId]) ? contrexx_input2db($inputField[0]) : contrexx_input2db($inputField[$langId]);
                    $objDatabase->Execute("
                        INSERT INTO `".DBPREFIX."module_{$this->moduleName}_membership_local` SET
                            `entry_id` = $entryId,
                            `lang_id`   = $langId,
                            `value`    = '$value'
                            ");
                }
            }

            if ($db) {
                CSRF::header("Location:./?cmd={$this->moduleName}&act=membership");
                exit();
            } else {
                $this->_strErrMessage = "Error in saving Data";
            }
        } elseif (!empty($id)) {
            $objResult = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_memberships` WHERE id = $id");

            $name    = $objResult->fields['industry_type'];
            $sorting = $objResult->fields['sorting'];
            $status  = $objResult->fields['status'];

            $objInputFields = $objDatabase->Execute("SELECT * FROM `".DBPREFIX."module_{$this->moduleName}_membership_local` WHERE entry_id = $id");
            while (!$objInputFields->EOF) {
                $inputField[$objInputFields->fields['lang_id']] = $objInputFields->fields['value'];
                $objInputFields->MoveNext();
            }
        }

        $first = true;
        foreach ($this->_arrLanguages as $langId => $langValue) {

            ($first) ? $objTpl->touchBlock("minimize") : $objTpl->hideBlock("minimize");
            $first = false;

            $objTpl->setVariable(array(
                    'LANG_ID'                 => $langId,
                    'LANG_LONG_NAME'          => $langValue['long'],
                    'LANG_SHORT_NAME'         => $langValue['short'],
                    'CRM_SETTINGS_VALUE'      => isset($inputField[$langId]) ? contrexx_raw2xhtml($inputField[$langId]) : ''
            ));
            $objTpl->parse("settingsNames");
        }

        $objTpl->setGlobalVariable(array(
                'TXT_CRM_MORE'              => $_ARRAYLANG['TXT_CRM_MORE'],
                'TXT_CRM_MINIMIZE'          => $_ARRAYLANG['TXT_CRM_MINIMIZE']
        ));
        $objTpl->setVariable(array(
                'CRM_SETTINGS_NAME_DEFAULT_VALUE' => isset($inputField[$_LANGID]) ? contrexx_raw2xhtml($inputField[$_LANGID]) : '',
                'CRM_ACTIVATED_VALUE'       => $status ? "checked='checked'" : '',
                'CRM_SORTINGNUMBER'         => $sorting,
                'DEFAULT_LANG_ID'           => $_LANGID,
                'LANG_ARRAY'                => implode(',', array_keys($this->_arrLanguages)),
                'TXT_OVERVIEW'              => $_ARRAYLANG['TXT_OVERVIEW'],
                'TXT_NAME'                  => $_ARRAYLANG['TXT_NAME'],
                'TXT_TITLEACTIVE'           => $_ARRAYLANG['TXT_TITLEACTIVE'],
                'TXT_SORTING_NUMBER'        => $_ARRAYLANG['TXT_SORTING_NUMBER'],
                'TXT_SAVE'                  => $_ARRAYLANG['TXT_SAVE'],
                'TXT_TITLE_MODIFY_INDUSTRY' => (!empty ($id)) ? $_ARRAYLANG['TXT_EDIT_MEMBERSHIP'] : $_ARRAYLANG['TXT_ADD_MEMBERSHIP'],
        ));
    }

    function getCustomerSearch()
    {
        global $objDatabase, $_LANGID;
        
        $where = array();

        $searchContactTypeFilter = isset($_REQUEST['contactSearch']) ? (array) $_REQUEST['contactSearch'] : array(1,2);
        $searchContactTypeFilter = array_map('intval', $searchContactTypeFilter);
        $where[] = " c.contact_type IN (".implode(',', $searchContactTypeFilter).")";
        
        if (isset($_REQUEST['advanced-search'])) {
            if (isset($_REQUEST['s_name']) && !empty($_REQUEST['s_name'])) {
                $where[] = " (c.customer_name LIKE '".contrexx_input2db($_REQUEST['s_name'])."%' OR c.contact_familyname LIKE '".contrexx_input2db($_REQUEST['s_name'])."%')";
            }
            if (isset($_REQUEST['s_email']) && !empty($_REQUEST['s_email'])) {
                $where[] = " (email.email LIKE '".contrexx_input2db($_REQUEST['s_email'])."%')";
            }
            if (isset($_REQUEST['s_address']) && !empty($_REQUEST['s_address'])) {
                $where[] = " (addr.address LIKE '".contrexx_input2db($_REQUEST['s_address'])."%')";
            }
            if (isset($_REQUEST['s_city']) && !empty($_REQUEST['s_city'])) {
                $where[] = " (addr.city LIKE '".contrexx_input2db($_REQUEST['s_city'])."%')";
            }
            if (isset($_REQUEST['s_postal_code']) && !empty($_REQUEST['s_postal_code'])) {
                $where[] = " (addr.zip LIKE '".contrexx_input2db($_REQUEST['s_postal_code'])."%')";
            }
            if (isset($_REQUEST['s_notes']) && !empty($_REQUEST['s_notes'])) {
                $where[] = " (c.notes LIKE '".contrexx_input2db($_REQUEST['s_notes'])."%')";
            }            
        }
        if (isset($_REQUEST['customer_type']) && !empty($_REQUEST['customer_type'])) {
            $where[] = " (c.customer_type = '".intval($_REQUEST['customer_type'])."')";
        }
        if (isset($_REQUEST['filter_membership']) && !empty($_REQUEST['filter_membership'])) {
            $where[] = " mem.membership_id = '".intval($_REQUEST['filter_membership'])."'";
        }

        if (isset($_REQUEST['term']) && !empty($_REQUEST['term'])) {
            if (in_array(2, $searchContactTypeFilter))
                $fullTextContact[]  =  'c.customer_name, c.contact_familyname';
            if (in_array(1, $searchContactTypeFilter))
                $fullTextContact[]  = 'c.customer_name';
            $where[] = " MATCH (".implode(',', $fullTextContact).") AGAINST ('".contrexx_input2raw($_REQUEST['term'])."*' IN BOOLEAN MODE)";
        }

        //  Join where conditions
        $filter = '';
        if (!empty ($where))
            $filter = " WHERE ".implode(' AND ', $where);

        $sorto = 'DESC';
        $sortf = 'c.id';        

        $query = "SELECT
                       DISTINCT c.id,                       
                       c.customer_id,
                       c.customer_type,
                       c.customer_name,
                       c.contact_familyname,
                       c.contact_type,
                       c.contact_customer AS contactCustomerId,
                       c.status,
                       c.added_date,
                       con.customer_name AS contactCustomer,
                       email.email,
                       phone.phone,
                       t.label AS cType,
                       Inloc.value AS industryType
                   FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS con
                     ON c.contact_customer =con.id
                   LEFT JOIN ".DBPREFIX."module_{$this->moduleName}_customer_types AS t
                     ON c.customer_type = t.id
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_emails` as email
                     ON (c.id = email.contact_id AND email.is_primary = '1')
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_phone` as phone
                     ON (c.id = phone.contact_id AND phone.is_primary = '1')
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_contact_address` as addr
                     ON (c.id = addr.contact_id AND addr.is_primary = '1')
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_customer_membership` as mem
                     ON (c.id = mem.contact_id)
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_industry_types` AS Intype
                     ON c.industry_type = Intype.id
                   LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_industry_type_local` AS Inloc
                     ON Intype.id = Inloc.entry_id AND Inloc.lang_id = ".$_LANGID."
            $filter
                   ORDER BY $sortf $sorto";
                   
        $objResult = $objDatabase->Execute($query);

        $result = array();
        if ($objResult) {
            while (!$objResult->EOF) {
                if ($objResult->fields['contact_type'] == 1) {
                    $contactName = $objResult->fields['customer_name'];
                } else {
                    $contactName = $objResult->fields['customer_name']." ".$objResult->fields['contact_familyname'];
                }
                $result[] = array(
                    'id'    => (int) $objResult->fields['id'],
                    'label' => html_entity_decode(stripslashes($contactName), ENT_QUOTES, CONTREXX_CHARSET),
                    'value' => html_entity_decode(stripslashes($contactName), ENT_QUOTES, CONTREXX_CHARSET),
                );
                $objResult->MoveNext();
            }
        }
        echo json_encode($result);
        exit();
    }

    public function checkUserAvailablity()
    {
        global $objDatabase, $_ARRAYLANG;

        $json = array();

        $customerId = isset($_GET['id']) ?  intval($_GET['id']) : 0;
        $term       = isset($_GET['term']) ?  contrexx_input2raw($_GET['term']) : '';
        $userId     = 0;
        if (!empty($term)) {
            if ($customerId) {
                $userId = $objDatabase->getOne("SELECT `user_account` FROM `". DBPREFIX ."module_{$this->moduleName}_contacts` WHERE `id` = $customerId");
            }

            $accountId = $this->isUniqueUsername($term, $userId);
            
            if ($accountId) {
                $objCount = $objDatabase->Execute("SELECT `user_account` FROM `". DBPREFIX ."module_{$this->moduleName}_contacts` WHERE `user_account` = $accountId");
                if ($objCount->RecordCount() && $userId != $objCount->fields['user_account']) {
                    $json['error'] = $_ARRAYLANG['CRM_ERROR_EMAIL_USED_BY_OTHER_PERSON'];
                } else {
                    $json['error'] = $_ARRAYLANG['CRM_USER_EMAIL_ALERT'];
                }                
            } else {
                $json['success'] = 'Available';
                
            }
        } else {
            $json['error'] = $_ARRAYLANG['TXT_CRM_EMAIL_EMPTY'];
        }
        
        echo json_encode($json);
        exit();
    }
    function getCustomerDomains() {
        global $objDatabase;

        $term       = contrexx_input2db($_GET['term']);
        $customerId = isset($_GET['customer']) ? intval($_GET['customer']) : 0;

        if (!empty($customerId)) {
            $searchCustomer = " AND cus.`id` = $customerId";
        }
        $query = "SELECT web.`id`,
                         web.`url`,
                         web.`contact_id`,
                         cus.`customer_name`
                     FROM `".DBPREFIX."module_{$this->moduleName}_customer_contact_websites` AS web
                        LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_contacts` AS cus
                       ON web.`contact_id` = cus.`id` AND web.`url_profile` = 1
                     WHERE web.`url` LIKE '%$term%' $searchCustomer ORDER BY web.`url` ASC";
        $objResult = $objDatabase->Execute($query);

        $website = array();

        while (!$objResult->EOF) {
            $website[] = array(
                    'id'         => $objResult->fields['id'],
                    'label'      => urldecode($objResult->fields['url']),
                    'value'      => urldecode($objResult->fields['url']),
                    'company'    => $objResult->fields['customer_name'],
                    'companyId'    => $objResult->fields['contact_id'],
            );
            $objResult->MoveNext();
        }
        echo json_encode($website);
        exit();

    }
    
    /**
     * Default PM Customer Suggetion box functionality
     *
     * @access Authenticated
     */
    function autoSuggest() {
        global $_ARRAYLANG,$objDatabase,$wysiwygEditor, $FCKeditorBasePath;

        $id = intval($_GET['id']);

        $q = "SELECT
                cust.id,
                cust.customer_name,
                cust.contact_familyname,
                cust.customer_id,
                cur.name AS cur_name,
                cust.`contact_type`
             FROM ".DBPREFIX."module_crm_contacts AS cust
             LEFT JOIN ".DBPREFIX."module_crm_currency AS cur
                ON cust.customer_currency = cur.id
             WHERE cust.id ='".$id."' AND status='1'";


        $objResult = $objDatabase->Execute($q);
        $customer = array();

        $contatWhere = $objResult->fields['contact_type'] == 1 ? "c.contact_customer" : "c.id";
        $contactPerson = $objDatabase->Execute("SELECT c.`id`,
                                                       c.`contact_customer`,
                                                       c.`customer_name`,
                                                       c.`contact_familyname`,
                                                       email.`email`
                                                   FROM `".DBPREFIX."module_crm_contacts` AS c
                                                   LEFT JOIN `".DBPREFIX."module_crm_customer_contact_emails` AS email
                                                       ON c.`id` = email.`contact_id` AND email.`is_primary` = '1'
                                                   WHERE $contatWhere = '$id' AND `status` = 1");

        $customer['id']         = intval($objResult->fields['id']);
        $customer['company']    = $objResult->fields['contact_type'] == 1 ? stripslashes($objResult->fields['customer_name']) : stripslashes($objResult->fields['customer_name']." ".$objResult->fields['contact_familyname']);// Reply array list for given query
        $customer['cust_input'] = stripslashes($objResult->fields['customer_id']);
        $customer['cur_name']   = stripslashes($objResult->fields['cur_name']);
        $row = 0;
        while (!$contactPerson->EOF) {
            $customer['customer'][$row]['name'] = stripslashes($contactPerson->fields['customer_name']." ".$contactPerson->fields['contact_familyname']);
            $customer['customer'][$row]['email'] = stripslashes($contactPerson->fields['email']);
            $customer['customer'][$row]['id'] = intval($contactPerson->fields['id']);
            $contactPerson->MoveNext();
            $row++;
        }

        $rcustomer = json_encode($customer);
        header("Content-Type: application/json");
        echo $rcustomer;

        exit();
    }
    
    function getCustomers() {
        global $objDatabase;

        $term = contrexx_input2db($_GET['term']);
        // Customers without contacts

        $q = "SELECT   c.id,
                       c.customer_name,
                       c.contact_familyname,
                       c.contact_type
                   FROM `".DBPREFIX."module_{$this->moduleName}_contacts` AS c
                   WHERE c.customer_name LIKE '$term%' AND contact_type = 1";
        $objResult = $objDatabase->Execute($q);

        $customer = array();
        while (!$objResult->EOF) {
            $customerName   = $objResult->fields['contact_type'] == 1 ? contrexx_addslashes($objResult->fields['customer_name']) : contrexx_addslashes($objResult->fields['customer_name']." ".$objResult->fields['contact_familyname']);// Reply array list for given query
            $customer[] = array(
                    'id'    => (int) $objResult->fields['id'],
                    'label' => html_entity_decode(stripslashes($customerName), ENT_QUOTES, CONTREXX_CHARSET),
                    'value' => html_entity_decode(stripslashes($customerName), ENT_QUOTES, CONTREXX_CHARSET),
            );
            $objResult->MoveNext();
        }
        echo json_encode($customer);
        exit();
    }

    function uploadProfilePhoto()
    {
        global $objDatabase, $_ARRAYLANG;

        $customerId = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
        
        $json = array();
        if ($customerId) {
            require_once CRM_MODULE_LIB_PATH.'/qqFileUploader.php';

            $uploader = new qqFileUploader();
            // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
            //Taken from http://en.wikipedia.org/wiki/Image_file_formats
            $uploader->allowedExtensions = array("jpeg", "png", "gif", "jpg", "bmp");

            // Specify max file size in bytes.
            $uploader->sizeLimit = 1 * 1024 * 1024;

            // Specify the input name set in the javascript.
            $uploader->inputName = 'profile-picture';

            // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
            $result = $uploader->handleUpload(CRM_ACCESS_PROFILE_IMG_PATH, md5(mt_rand()).'_'.$uploader->getName());

            // To save the upload with a specified name, set the second parameter.
            // $result = $uploader->handleUpload('uploads/', md5(mt_rand()).'_'.$uploader->getName());

            // To return a name used for uploaded file you can use the following line.
            $result['uploadName'] = $uploader->getUploadName();
            $result['thumbName']  = $uploader->getThumbName();

            if (empty($result['error'])) {
                //dbg::activate();
                $this->contact = $this->load->model('crmContact', __CLASS__);
                $this->contact->load($customerId);
                $this->contact->profile_picture = $result['uploadName'];
                $this->contact->save();
            }
            
            header("Content-Type: text/plain");
            echo json_encode($result);
            exit();
        } else {
            $json['error'] = "Customer Id empty!";
        }

        echo json_encode($json);
        exit();
    }

    function updateProfilePhoto()
    {
        global $objDatabase, $_ARRAYLANG;

        $json = array();

        $customer_id = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
        $image_name  = isset($_POST['img_name']) ? contrexx_raw2xhtml($_POST['img_name']) : '';

        if ($customer_id && !empty($image_name)) {
            $this->contact = $this->load->model('crmContact', __CLASS__);
            $this->contact->load($customer_id);
            $this->contact->profile_picture = $image_name;
            $this->contact->save();
            $json['success']    = true;
            $json['img_name']   = $image_name;
        } else {
            $json['error'] = "Customer Id empty!";
        }
        
        echo json_encode($json);
        exit();
    }
}
?>