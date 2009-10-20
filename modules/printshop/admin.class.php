<?php
/**
 * Printshop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
error_reporting(E_ALL);ini_set('display_errors',1);

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/printshop/lib/printshopLib.class.php';

/**
 * PrintshopAdmin
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
class PrintshopAdmin extends PrintshopLibrary {

    var $_objTpl;
    var $_strPageTitle  = '';
    var $_strErrMessage = '';
    var $_strOkMessage  = '';

    /**
    * Constructor   -> Create the module-menu and an internal template-object
    * @global   InitCMS
    * @global   HTML_Template_Sigma
    * @global   array
    */
    function __construct()
    {
        global $objInit, $objTemplate, $_ARRAYLANG;
        parent::__construct();
        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/printshop/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_intLanguageId = $objInit->userFrontendLangId;

        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->getId();
        $objTemplate->setVariable('CONTENT_NAVIGATION','    <a href="?cmd=printshop">'.$_ARRAYLANG['TXT_PRINTSHOP_ORDERS_TITLE'].'</a>
                                                            <a href="?cmd=printshop&amp;act=products">'.$_ARRAYLANG['TXT_PRINTSHOP_PRODUCTS'].'</a>
                                                            <a href="?cmd=printshop&amp;act=attributes">'.$_ARRAYLANG['TXT_PRINTSHOP_ATTRIBUTES'].'</a>
                                                            <a href="?cmd=printshop&amp;act=settings">'.$_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'].'</a>
                                                    ');
    }


   /**
    * Perform the right operation depending on the $_GET-params
    *
    * @global   HTML_Template_Sigma
    */
    function getPage() {
        global $objTemplate, $_ARRAYLANG;

        if(!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch($_GET['act']){
            case 'addProduct':
                $this->_addProduct();
            break;

            case 'updateProduct':
                $this->_updateProduct();
            break;

            case 'delProduct':
                $this->_delProduct();
            break;

            case 'delOrder':
                $this->_delOrder();
                $this->showOrders();
            break;

            case 'getProducts':
                $this->_getProducts();
            break;

            case 'attributes':
                $_GET['act'] = 'type'; //default attribute
            case 'type':
            case 'format':
            case 'front':
            case 'back':
            case 'weight':
            case 'paper':
                Permission::checkAccess(150, 'static');
                $attribute = contrexx_addslashes($_GET['act']);
                if(!empty($_POST['json'])){
                    switch($_POST['json']){
                        case 'save':
                            $arrEntry = $this->saveAttribute($attribute, intval($_POST['id']));
                            die(json_encode(array(
                                'id' => $arrEntry['id'],
                                'name' => $arrEntry['name'],
                                'exists' => $arrEntry['exists']
                            )));
                        break;
                        case 'delete':
                            $message = $this->deleteAttribute($attribute, intval($_POST['id']));
                            die(json_encode(array('message' => $message)));
                        break;
                        default:
                    }
                }
                $this->showAttributes($attribute);
            break;

            case 'settings':
                $this->showSettings();
            break;

            case 'saveSettings':
                Permission::checkAccess(150, 'static');
                $this->saveSettings();
                $this->showSettings();
            break;

            case 'products':
                $this->showProductOverview();
            break;

            case 'changeStatus':
                if($this->_changeOrderStatus(intval($_GET['orderId']))){
                    $this->_strOkMessage  = $_ARRAYLANG['TXT_PRINTSHOP_MOVE_OK'];
                }else{
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PRINTSHOP_MOVE_ERR'];
                }
                $this->showOrders();
            break;

            case 'detail':
                $this->_showOrderDetails($_GET['orderId']);
            break;

            default:
                Permission::checkAccess(150, 'static');
                $this->showOrders();
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->_strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }

    /**
     * deletes an order
     *
     */
    function _delOrder(){
        global $objDatabase, $_ARRAYLANG;
        if($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'module_printshop_order` WHERE `orderId`='.intval($_GET['orderId']))){
            $this->_strOkMessage = $_ARRAYLANG['TXT_PRINTSHOP_ENTRY_DELETED'];
        }
    }


    /**
     * change the status of the specified order
     *
     * @param integer $orderId
     * @return bool
     */
    function _changeOrderStatus($orderId){
        global $objDatabase;
        $orderId = intval($orderId);
        if($orderId > 0){
            $query = "UPDATE `".DBPREFIX."module_printshop_order`
                     SET `status` = IF(`status`, 0, 1)
                     WHERE `orderId` = ".$orderId;
            return $objDatabase->Execute($query);
        }
        return false;
    }


    /**
     * Shows the print item overview
     *
     * @global  array
     * @global  array
     */
    function showProductOverview(){
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_PRODCUTS'];
        $this->_objTpl->loadTemplateFile('module_printshop_products.html', true, true);

        foreach ($this->_priceThresholds as $index => $price) {
            $disabled = '';
            if(!isset($price['threshold'])){
                $price['threshold'] = '-';
                $disabled = 'disabled="disabled"';
            }
            $this->_objTpl->setVariable(array(
                'PRINTSHOP_PRICE_'.$index             => $price['threshold'],
                'PRINTSHOP_PRICE_'.$index.'_DISABLED' => $disabled,
                'TXT_PRINTSHOP_PRICE_'.$index.'_HELP' => sprintf($_ARRAYLANG['TXT_PRINTSHOP_PRICE_HELP'], $price['threshold']),
            ));
        }

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_NO_ENTRY'                => $_ARRAYLANG['TXT_PRINTSHOP_NO_ENTRY'],
            'TXT_PRINTSHOP_ADD'                     => $_ARRAYLANG['TXT_PRINTSHOP_ADD'],
            'TXT_PRINTSHOP_ADD_PRODUCT'             => $_ARRAYLANG['TXT_PRINTSHOP_ADD_PRODUCT'],
            'TXT_PRINTSHOP_PRODUCTS'                => $_ARRAYLANG['TXT_PRINTSHOP_PRODUCTS'],
            'TXT_PRINTSHOP_PRICE_TITLE'             => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_TITLE'],
            'TXT_PRINTSHOP_FACTOR_TITLE'            => $_ARRAYLANG['TXT_PRINTSHOP_FACTOR_TITLE'],
            'TXT_PRINTSHOP_SUBTITLE_ACTIONS'        => $_ARRAYLANG['TXT_PRINTSHOP_SUBTITLE_ACTIONS'],
            'TXT_PRINTSHOP_FIELDS_REQUIRED'         => $_ARRAYLANG['TXT_PRINTSHOP_FIELDS_REQUIRED'],
            'TXT_PRINTSHOP_DELETE_ENTRY'            => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_ENTRY'],
            'TXT_PRINTSHOP_DELETE_SELECTED'         => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_SELECTED'],
            'TXT_PRINTSHOP_FADE_OUT'                => $_ARRAYLANG['TXT_PRINTSHOP_FADE_OUT'],
            'TXT_PRINTSHOP_SAVE'                    => $_ARRAYLANG['TXT_PRINTSHOP_SAVE'],
            'TXT_PRINTSHOP_COPY'                    => $_ARRAYLANG['TXT_PRINTSHOP_COPY'],
            'TXT_PRINTSHOP_PRODUCT_EXISTED_UPDATED' => $_ARRAYLANG['TXT_PRINTSHOP_PRODUCT_EXISTED_UPDATED'],
            'TXT_PRINTSHOP_PRODUCT_UPDATED'         => $_ARRAYLANG['TXT_PRINTSHOP_PRODUCT_UPDATED'],
            'TXT_ADD_SUBMIT'                        => $_CORELANG['TXT_SAVE'],
            'TXT_SELECT_ALL'                        => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'                      => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_MULTISELECT_SELECT'                => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_MULTISELECT_DELETE'                => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'PRINTSHOP_PAGING_LIMIT'                => $this->_arrSettings['entriesPerPage'],
        ));
        $arrFilter = array();
        foreach ($this->_arrAvailableAttributes as $attribute) {
            $attr = strtoupper($attribute);
            $arrFilter[$attribute] = !empty($_POST['psFilter'.$attribute]) ? intval($_POST['psFilter'.$attribute]) : 0;
            $this->_objTpl->setVariable(array(
                'ENTRY_'.$attr.'_DD'            => $this->createAttributeDropDown($attribute, 'ps', $_ARRAYLANG['TXT_PRINTSHOP_CHOOSE']),
                'ENTRY_'.$attr.'_FILTER_DD'     => $this->createAttributeDropDown($attribute, 'psFilter', $_CORELANG['TXT_USER_ALL'], $arrFilter[$attribute]),
                'TXT_PRINTSHOP_'.$attr.'_TITLE' => $_ARRAYLANG['TXT_PRINTSHOP_'.$attr.'_TITLE'],
            ));
        }

        $arrEntries = $this->_getEntries($arrFilter, true);

        if($arrEntries['count'] > 0){
            foreach ($arrEntries['entries'] as $index => $arrEntry) {
                $this->_objTpl->setVariable(array(
                    'ENTRY_TYPE'        =>  $arrEntry['type'],
                    'ENTRY_FORMAT'      =>  $arrEntry['format'],
                    'ENTRY_FRONT'       =>  $arrEntry['front'],
                    'ENTRY_BACK'        =>  $arrEntry['back'],
                    'ENTRY_WEIGHT'      =>  $arrEntry['weight'],
                    'ENTRY_PAPER'       =>  $arrEntry['paper'],
                    'ENTRY_PRICE_0'     =>  $arrEntry['price_0'],
                    'ENTRY_PRICE_1'     =>  $arrEntry['price_1'],
                    'ENTRY_PRICE_2'     =>  $arrEntry['price_2'],
                    'ENTRY_PRICE_3'     =>  $arrEntry['price_3'],
                    'ENTRY_PRICE_4'     =>  $arrEntry['price_4'],
                    'ENTRY_PRICE_5'     =>  $arrEntry['price_5'],
                    'ENTRY_PRICE_6'     =>  $arrEntry['price_6'],
                    'ENTRY_PRICE_7'     =>  $arrEntry['price_7'],
                    'ENTRY_PRICE_8'     =>  $arrEntry['price_8'],
                    'ENTRY_PRICE_9'     =>  $arrEntry['price_9'],
                    'ENTRY_PRICE_10'    =>  $arrEntry['price_10'],
                    'ENTRY_PRICE_11'    =>  $arrEntry['price_11'],
                    'ENTRY_PRICE_12'    =>  $arrEntry['price_12'],
                    'ENTRY_PRICE_13'    =>  $arrEntry['price_13'],
                    'ENTRY_PRICE_14'    =>  $arrEntry['price_14'],
                    'ENTRY_PRICE_15'    =>  $arrEntry['price_15'],
                    'ENTRY_ROWCLASS'    =>  $index % 2 ? 'row2' : 'row1',

                ));

                $this->_objTpl->parse('showEntry');
            }
            $this->_objTpl->setVariable(array(
                'PRINTSHOP_PAGING'  => getPaging($arrEntries['count'], $this->_pos, '&cmd=printshop&act=products', $_ARRAYLANG['TXT_PRINTSHOP_ENTRY'], true, $this->_limit),
            ));
        }else{
            $this->_objTpl->touchBlock('noEntry');
        }
        $this->_objTpl->setVariable(array(
            'PRINTSHOP_TOTAL_ENTRIES'   => $arrEntries['count'],
        ));
    }

    /**
     * Shows the details of an order
     *
     * @param integer $orderId
     */
    function _showOrderDetails($orderId){
        global $_ARRAYLANG;

        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_ORDER_DETAILS'];
        $this->_objTpl->loadTemplateFile('module_printshop_order_detail.html', true, true);

        $arrOrder = $this->_getOrder($orderId);

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_ORDER_DETAILS'           => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_DETAILS'],
            'TXT_PRINTSHOP_PRODUCT_DETAILS'         => $_ARRAYLANG['TXT_PRINTSHOP_PRODUCT_DETAILS'],
            'TXT_PRINTSHOP_ADDITIONAL_DETAILS'      => $_ARRAYLANG['TXT_PRINTSHOP_ADDITIONAL_DETAILS'],
            'TXT_PRINTSHOP_SHIPMENT_ADDRESS'        => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_ADDRESS'],
            'TXT_PRINTSHOP_INVOICE_ADDRESS'         => $_ARRAYLANG['TXT_PRINTSHOP_INVOICE_ADDRESS'],
            'TXT_PRINTSHOP_TYPE'                    => $_ARRAYLANG['TXT_PRINTSHOP_TYPE_TITLE'],
            'TXT_PRINTSHOP_FORMAT'                  => $_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'],
            'TXT_PRINTSHOP_FRONT'                   => $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'],
            'TXT_PRINTSHOP_BACK'                    => $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'],
            'TXT_PRINTSHOP_WEIGHT'                  => $_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'],
            'TXT_PRINTSHOP_PAPER'                   => $_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'],
            'TXT_PRINTSHOP_SUBJECT'                 => $_ARRAYLANG['TXT_PRINTSHOP_SUBJECT'],
            'TXT_PRINTSHOP_EMAIL'                   => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL'],
            'TXT_PRINTSHOP_TELEPHONE'               => $_ARRAYLANG['TXT_PRINTSHOP_TELEPHONE'],
            'TXT_PRINTSHOP_COMMENT'                 => $_ARRAYLANG['TXT_PRINTSHOP_COMMENT'],
            'TXT_PRINTSHOP_SHIPMENT_TYPE'           => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_TYPE'],
            'TXT_PRINTSHOP_UPLOADED_IMAGES'         => $_ARRAYLANG['TXT_PRINTSHOP_UPLOADED_IMAGES'],
            'TXT_PRINTSHOP_PRICE'                   => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_TITLE'],
            'TXT_PRINTSHOP_COMPANY'                 => $_ARRAYLANG['TXT_PRINTSHOP_COMPANY'],
            'TXT_PRINTSHOP_CONTACT'                 => $_ARRAYLANG['TXT_PRINTSHOP_CONTACT'],
            'TXT_PRINTSHOP_ADDRESS'                 => $_ARRAYLANG['TXT_PRINTSHOP_ADDRESS'],
            'TXT_PRINTSHOP_ZIP'                     => $_ARRAYLANG['TXT_PRINTSHOP_ZIP'],
            'TXT_PRINTSHOP_CITY'                    => $_ARRAYLANG['TXT_PRINTSHOP_CITY'],
            'TXT_PRINTSHOP_AMOUNT'                  => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT'],
            'TXT_PRINTSHOP_FUNCTIONS'               => $_ARRAYLANG['TXT_PRINTSHOP_SUBTITLE_ACTIONS'],
            'TXT_PRINTSHOP_VIEW_IMAGE'              => $_ARRAYLANG['TXT_PRINTSHOP_VIEW_IMAGE'],
            'TXT_PRINTSHOP_DOWNLOAD_FILE'           => $_ARRAYLANG['TXT_PRINTSHOP_DOWNLOAD_FILE'],
            'TXT_PRINTSHOP_ORDER_ID'                => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_ID'],
            'PRINTSHOP_ORDER_ID'                    => $orderId,
            'PRINTSHOP_TYPE'                        => $this->_getAttributeName('type', $arrOrder['type']),
            'PRINTSHOP_FORMAT'                      => $this->_getAttributeName('format', $arrOrder['format']),
            'PRINTSHOP_FRONT'                       => $this->_getAttributeName('front', $arrOrder['front']),
            'PRINTSHOP_BACK'                        => $this->_getAttributeName('back', $arrOrder['back']),
            'PRINTSHOP_WEIGHT'                      => $this->_getAttributeName('weight', $arrOrder['weight']),
            'PRINTSHOP_PAPER'                       => $this->_getAttributeName('paper', $arrOrder['paper']),
            'PRINTSHOP_AMOUNT'                      => $arrOrder['amount'],
            'PRINTSHOP_PRICE'                       => $this->_arrSettings['currency'].' '.number_format($arrOrder['price'], 2, null, "'"),
            'PRINTSHOP_SUBJECT'                     => $arrOrder['subject'],
            'PRINTSHOP_EMAIL'                       => $arrOrder['email'],
            'PRINTSHOP_TELEPHONE'                   => $arrOrder['telephone'],
            'PRINTSHOP_COMMENT'                     => nl2br($arrOrder['comment']),
            'PRINTSHOP_SHIPMENT_COMPANY'            => $arrOrder['shipmentCompany'],
            'PRINTSHOP_SHIPMENT_CONTACT'            => $arrOrder['shipmentContact'],
            'PRINTSHOP_SHIPMENT_ADDRESS1'           => $arrOrder['shipmentAddress1'],
            'PRINTSHOP_SHIPMENT_ADDRESS2'           => $arrOrder['shipmentAddress2'],
            'PRINTSHOP_SHIPMENT_ZIP'                => $arrOrder['shipmentZip'],
            'PRINTSHOP_SHIPMENT_CITY'               => $arrOrder['shipmentCity'],
            'PRINTSHOP_INVOICE_COMPANY'             => $arrOrder['invoiceCompany'],
            'PRINTSHOP_INVOICE_CONTACT'             => $arrOrder['invoiceContact'],
            'PRINTSHOP_INVOICE_ADDRESS1'            => $arrOrder['invoiceAddress1'],
            'PRINTSHOP_INVOICE_ADDRESS2'            => $arrOrder['invoiceAddress2'],
            'PRINTSHOP_INVOICE_ZIP'                 => $arrOrder['invoiceZip'],
            'PRINTSHOP_INVOICE_CITY'                => $arrOrder['invoiceCity'],
            'PRINTSHOP_SHIPMENT'                    => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_'.strtoupper($arrOrder['shipment'])],
        ));

        foreach ($this->_arrImageFields as $index => $imgFieldName) {
            $imgSrc = $arrOrder['file'.$index];
            if(!empty($imgSrc)){
                switch($index){
                    case 1:
                        $caption = $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'];
                    break;
                    case 2:
                        $caption = $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'];
                    break;
                    default:
                        $caption = $_ARRAYLANG['TXT_PRINTSHOP_ADDITIONAL'];
                }
                $this->_objTpl->setVariable(array(
                    'PRINTSHOP_IMAGE_CAPTION'   => $caption,
                    'PRINTSHOP_IMAGE_SRC'       => $imgSrc,
                ));
                $this->_objTpl->parse('image');
            }
        }
    }


    /**
     * Shows the orders
     *
     */
    function showOrders(){
        global $_ARRAYLANG;

        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_ORDERS_TITLE'];
        $this->_objTpl->loadTemplateFile('module_printshop_orders.html', true, true);

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_OPEN_ORDERS'     => $this->_arrSettings['orderStatusEnabled'] > 0 ? $_ARRAYLANG['TXT_PRINTSHOP_OPEN_ORDERS'] : $_ARRAYLANG['TXT_PRINTSHOP_ORDERS_TITLE'],
            'TXT_PRINTSHOP_CLOSED_ORDERS'   => $_ARRAYLANG['TXT_PRINTSHOP_CLOSED_ORDERS'],
            'TXT_PRINTSHOP_PRICE'           => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_TITLE'],
            'TXT_PRINTSHOP_SUBJECT'         => $_ARRAYLANG['TXT_PRINTSHOP_SUBJECT'],
            'TXT_PRINTSHOP_COMPANY'         => $_ARRAYLANG['TXT_PRINTSHOP_COMPANY'],
            'TXT_PRINTSHOP_CONTACT'         => $_ARRAYLANG['TXT_PRINTSHOP_CONTACT'],
            'TXT_PRINTSHOP_ADDRESS'         => $_ARRAYLANG['TXT_PRINTSHOP_ADDRESS'],
            'TXT_PRINTSHOP_ZIP'             => $_ARRAYLANG['TXT_PRINTSHOP_ZIP'],
            'TXT_PRINTSHOP_CITY'            => $_ARRAYLANG['TXT_PRINTSHOP_CITY'],
            'TXT_PRINTSHOP_AMOUNT'          => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT'],
            'TXT_PRINTSHOP_VIEW_DETAILS'    => $_ARRAYLANG['TXT_PRINTSHOP_VIEW_DETAILS'],
            'TXT_PRINTSHOP_MOVE_TO_CLOSED'  => $_ARRAYLANG['TXT_PRINTSHOP_MOVE_TO_CLOSED'],
            'TXT_PRINTSHOP_FUNCTIONS'       => $_ARRAYLANG['TXT_PRINTSHOP_SUBTITLE_ACTIONS'],
            'TXT_PRINTSHOP_DELETE'          => $_ARRAYLANG['TXT_PRINTSHOP_DELETE'],
            'TXT_PRINTSHOP_DELETE_ENTRY'    => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_ENTRY'],
            'PRINTSHOP_CURRENCY'            => $this->_arrSettings['currency'],
        ));

        $arrOrdersOpen = $this->_getOrders(1);
        $arrOrdersClosed = $this->_getOrders(0);

        if($this->_arrSettings['orderStatusEnabled'] > 0){
            if($arrOrdersOpen['count'] > 0){
                foreach ($arrOrdersOpen['entries'] as $index => $arrOrder) {
                    $this->_setOrderTemplateVars($index, $arrOrder);
                    $this->_objTpl->parse('orderMove');
                    $this->_objTpl->parse('orderOpen');
                }
            }

            if($arrOrdersClosed['count'] > 0){
                foreach ($arrOrdersClosed['entries'] as $index => $arrOrder) {
                    $this->_setOrderTemplateVars($index, $arrOrder);
                    $this->_objTpl->parse('orderClosed');
                    $this->_objTpl->parse('orderMove');
                }
            }
            $this->_objTpl->setVariable(array(
                'PRINTSHOP_PAGING_OPEN'   => getPaging($arrOrdersOpen['count'], $this->_pos, '&cmd=printshop', $_ARRAYLANG['TXT_PRINTSHOP_ENTRY'], true, $this->_limit),
                'PRINTSHOP_PAGING_CLOSED' => getPaging($arrOrdersClosed['count'], $this->_pos, '&cmd=printshop', $_ARRAYLANG['TXT_PRINTSHOP_ENTRY'], true, $this->_limit),
            ));
            $this->_objTpl->touchBlock('orderTabmenu');
        }else{
            $arrOrders = array_merge($arrOrdersOpen['entries'], $arrOrdersClosed['entries']);
            if(count($arrOrders) > 0){
                foreach ($arrOrders as $index => $arrOrder) {
                    $this->_setOrderTemplateVars($index, $arrOrder);
                    $this->_objTpl->parse('orderOpen');
                }
            }
            $this->_objTpl->setVariable(array(
                'PRINTSHOP_PAGING_OPEN'   => getPaging(count($arrOrders), $this->_pos, '&cmd=printshop', $_ARRAYLANG['TXT_PRINTSHOP_ENTRY'], true, $this->_limit),
            ));
            $this->_objTpl->hideBlock('orderClosed');
            $this->_objTpl->hideBlock('orderMove');
            $this->_objTpl->hideBlock('orderTabmenu');
        }
    }


    /**
     * set the template vars
     *
     * @param integer $index row index
     * @param array $arrOrder data of the order to be used for replacement
     */
    function _setOrderTemplateVars($index, $arrOrder){
        $this->_objTpl->setGlobalVariable(array(
            'PRINTSHOP_ORDER_ID'    => !empty($arrOrder['orderId'])             ? $arrOrder['orderId']          : '&nbsp;',
        ));
        $this->_objTpl->setVariable(array(
            'PRINTSHOP_ROW_CLASS'   => $index % 2 == 0 ? 'row1' : 'row2',
            'PRINTSHOP_ORDER_ID'    => !empty($arrOrder['orderId'])             ? $arrOrder['orderId']          : '&nbsp;',
            'PRINTSHOP_SUBJECT'     => !empty($arrOrder['subject'])             ? $arrOrder['subject']          : '&nbsp;',
            'PRINTSHOP_COMPANY'     => !empty($arrOrder['shipmentCompany'])     ? $arrOrder['shipmentCompany']  : '&nbsp;',
            'PRINTSHOP_CONTACT'     => !empty($arrOrder['shipmentContact'])     ? $arrOrder['shipmentContact']  : '&nbsp;',
            'PRINTSHOP_ADDRESS1'    => !empty($arrOrder['shipmentAddress2'])    ? $arrOrder['shipmentAddress2'] : '&nbsp;',
            'PRINTSHOP_ADDRESS2'    => !empty($arrOrder['shipmentAddress1'])    ? $arrOrder['shipmentAddress1'] : '&nbsp;',
            'PRINTSHOP_ZIP'         => !empty($arrOrder['shipmentZip'])         ? $arrOrder['shipmentZip']      : '&nbsp;',
            'PRINTSHOP_CITY'        => !empty($arrOrder['shipmentCity'])        ? $arrOrder['shipmentCity']     : '&nbsp;',
            'PRINTSHOP_AMOUNT'      => !empty($arrOrder['amount'])              ? $arrOrder['amount']           : '&nbsp;',
            'PRINTSHOP_PRICE'       => !empty($arrOrder['price'])               ? number_format($arrOrder['price'], 2, null, "'") : '0.00',
        ));
    }


    /**
     * return filtered products as JSON string
     *
     */
    function _getProducts(){
        global $_ARRAYLANG;

        $arrFilter['type']   = !empty($_POST['psFilterType'])   ? contrexx_addslashes($_POST['psFilterType']) : '';
        $arrFilter['format'] = !empty($_POST['psFilterFormat']) ? contrexx_addslashes($_POST['psFilterFormat']) : '';
        $arrFilter['front']  = !empty($_POST['psFilterFront'])  ? contrexx_addslashes($_POST['psFilterFront']) : '';
        $arrFilter['back']   = !empty($_POST['psFilterBack'])   ? contrexx_addslashes($_POST['psFilterBack']) : '';
        $arrFilter['weight'] = !empty($_POST['psFilterWeight']) ? contrexx_addslashes($_POST['psFilterWeight']) : '';
        $arrFilter['paper']  = !empty($_POST['psFilterPaper'])  ? contrexx_addslashes($_POST['psFilterPaper']) : '';

        $arrEntriesData = array();
        $arrEntries = $this->_getEntries($arrFilter);
        if($arrEntries['count'] == 0){
            die(json_encode(array('error' => $_ARRAYLANG['TXT_PRINTSHOP_NO_ENTRY'])));
        }
        foreach ($arrEntries['entries'] as $index => $arrEntry) {
            $arrPrice = array();
            for($index = 0; $index < $this->_priceThresholdCount; $index++) {
                $arrPrice[$index] = $arrEntry['price_'.$index];
            }
                $arrEntriesData[] = array(
                'type'          => $arrEntry['type'],
                'format'        => $arrEntry['format'],
                'front'         => $arrEntry['front'],
                'back'          => $arrEntry['back'],
                'weight'        => $arrEntry['weight'],
                'paper'         => $arrEntry['paper'],
                'price'         => $arrPrice,
            );
        }
        die(json_encode(array(
            'paging' => array(
                'pos'   => $this->_pos,
                'limit' => $this->_limit,
                'count' => intval($arrEntries['count'])
             ),
            'entries' => $arrEntriesData)));
    }


    /**
     * updates a product
     *
     */
    function _updateProduct(){
        global $_ARRAYLANG;

        $type   = !empty($_POST['psType'])   ? contrexx_addslashes($_POST['psType']) : '';
        $format = !empty($_POST['psFormat']) ? contrexx_addslashes($_POST['psFormat']) : '';
        $front  = !empty($_POST['psFront'])  ? contrexx_addslashes($_POST['psFront']) : '';
        $back   = !empty($_POST['psBack'])   ? contrexx_addslashes($_POST['psBack']) : '';
        $weight = !empty($_POST['psWeight']) ? contrexx_addslashes($_POST['psWeight']) : '';
        $paper  = !empty($_POST['psPaper'])  ? contrexx_addslashes($_POST['psPaper']) : '';
        $oldtype   = !empty($_POST['psOldType'])   ? contrexx_addslashes($_POST['psOldType']) : '';
        $oldformat = !empty($_POST['psOldFormat']) ? contrexx_addslashes($_POST['psOldFormat']) : '';
        $oldfront  = !empty($_POST['psOldFront'])  ? contrexx_addslashes($_POST['psOldFront']) : '';
        $oldback   = !empty($_POST['psOldBack'])   ? contrexx_addslashes($_POST['psOldBack']) : '';
        $oldweight = !empty($_POST['psOldWeight']) ? contrexx_addslashes($_POST['psOldWeight']) : '';
        $oldpaper  = !empty($_POST['psOldPaper'])  ? contrexx_addslashes($_POST['psOldPaper']) : '';
        $oldprice  = array();

        $newProductExsisted = false;
        if(array($oldtype, $oldformat, $oldfront, $oldback, $oldweight, $oldpaper)
        != array($type,    $format,    $front,    $back,    $weight,    $paper)
        && $this->productExists($type, $format, $front, $back, $weight, $paper))
        {//new product exists, so this one will be update, hence delete the old one.
            $this->delProduct($oldtype, $oldformat, $oldfront, $oldback, $oldweight, $oldpaper);
            $newProductExsisted = true;
        }

        foreach ($this->_priceThresholds as $index => $threshold){
            if(!isset($_POST['psPrice_'.$index])){
                $_POST['psPrice_'.$index] = 0;
            }
            $price[$index] = contrexx_addslashes($_POST['psPrice_'.$index]) ? contrexx_addslashes($_POST['psPrice_'.$index]) : 0;
        }

        if(empty($price[0])){ //need at least first price
            die(json_encode(array('error' => $_ARRAYLANG['TXT_PRINTSHOP_FIELDS_REQUIRED'])));
        }

        if($this->addProduct($type, $format, $front, $back, $weight, $paper, $price) !== false){
            die(json_encode(array(
                'error'             => 'null',
                'ok'                => $_ARRAYLANG['TXT_PRINTSHOP_ENTRY_UPDATED'],
                'price'             => $price,
                'type'              => $this->_getAttributeName('type', $type),
                'format'            => $this->_getAttributeName('format', $format),
                'front'             => $this->_getAttributeName('front', $front),
                'back'              => $this->_getAttributeName('back', $back),
                'weight'            => $this->_getAttributeName('weight', $weight),
                'paper'             => $this->_getAttributeName('paper', $paper),
                'newProductExisted' => $newProductExsisted,
            )));
        }else{
            die(json_encode(array('error' => 'DB error.'.__FILE__.':'.__LINE__)));
        }

    }


    /**
     * deletes a product
     *
     */
    function _delProduct(){
        global $_ARRAYLANG;
        $type   = !empty($_POST['psType'])   ? contrexx_addslashes($_POST['psType']) : '';
        $format = !empty($_POST['psFormat']) ? contrexx_addslashes($_POST['psFormat']) : '';
        $front  = !empty($_POST['psFront'])  ? contrexx_addslashes($_POST['psFront']) : '';
        $back   = !empty($_POST['psBack'])   ? contrexx_addslashes($_POST['psBack']) : '';
        $weight = !empty($_POST['psWeight']) ? contrexx_addslashes($_POST['psWeight']) : '';
        $paper  = !empty($_POST['psPaper'])  ? contrexx_addslashes($_POST['psPaper']) : '';


        if(empty($type) || empty($format) || empty($front) || empty($back) || empty($weight) || empty($paper)){
            die(json_encode(array('error' => $_ARRAYLANG['TXT_PRINTSHOP_FIELDS_REQUIRED'])));
        }

        if($this->delProduct($type, $format, $front, $back, $weight, $paper) !== false){
            die(json_encode(array(
                'error'     => 'null',
                'ok'        => $_ARRAYLANG['TXT_PRINTSHOP_ENTRY_DELETED'],
            )));
        }else{
            die(json_encode(array('error' => 'DB error.'.__FILE__.':'.__LINE__)));
        }
    }

    /**
     * adds a product
     *
     */
    function _addProduct(){
        global $_ARRAYLANG;
        $type   = !empty($_POST['psType'])   ? contrexx_addslashes($_POST['psType']) : '';
        $format = !empty($_POST['psFormat']) ? contrexx_addslashes($_POST['psFormat']) : '';
        $front  = !empty($_POST['psFront'])  ? contrexx_addslashes($_POST['psFront']) : '';
        $back   = !empty($_POST['psBack'])   ? contrexx_addslashes($_POST['psBack']) : '';
        $weight = !empty($_POST['psWeight']) ? contrexx_addslashes($_POST['psWeight']) : '';
        $paper  = !empty($_POST['psPaper'])  ? contrexx_addslashes($_POST['psPaper']) : '';

        $arrPrice  = array();
        foreach ($this->_priceThresholds as $index => $price){
            if(!isset($_POST['psPrice_'.$index])){
                $_POST['psPrice_'.$index] = 0;
            }
            $arrPrice[$index] = contrexx_addslashes($_POST['psPrice_'.$index]) ? contrexx_addslashes($_POST['psPrice_'.$index]) : 0;
        }

        if(empty($type) || empty($format) || empty($front) || empty($back) || empty($weight) || empty($paper) || empty($arrPrice[0])/* need at least first price*/){
            die(json_encode(array('error' => $_ARRAYLANG['TXT_PRINTSHOP_FIELDS_REQUIRED'])));
        }

        if($this->productExists($type, $format, $front, $back, $weight, $paper)){
             die(json_encode(array('error' => $_ARRAYLANG['TXT_PRINTSHOP_ALREADY_EXISTS'])));
        }

        $this->_getAttributeTranslation();

        if($this->addProduct($type, $format, $front, $back, $weight, $paper, $arrPrice) !== false){
            die(json_encode(array(
                'error'     => 'null',
                'ok'        => $_ARRAYLANG['TXT_PRINTSHOP_ENTRY_ADDED'],
                'type'      => $this->_arrAttributeTranslation['type'  ][$type  ]['name'],
                'format'    => $this->_arrAttributeTranslation['format'][$format]['name'],
                'back'      => $this->_arrAttributeTranslation['back'  ][$back  ]['name'],
                'front'     => $this->_arrAttributeTranslation['front' ][$front ]['name'],
                'weight'    => $this->_arrAttributeTranslation['weight'][$weight]['name'],
                'paper'     => $this->_arrAttributeTranslation['paper' ][$paper ]['name'],
                'price'     => $arrPrice,
            )));
        }else{
            die(json_encode(array('error' => 'DB error.'.__FILE__.':'.__LINE__)));
        }
    }


    /**
     * removes attribute if not used by any products anymore
     *
     * @param string $attribute
     * @param int $id
     * @return string
     */
    function deleteAttribute($attribute, $id){
        global $objDatabase, $_ARRAYLANG;

        if(!$this->_isValidAtrribute($attribute)){
            return false;
        }
        $message = 'ok';
        $query = 'SELECT 1 FROM `'.DBPREFIX.'module_printshop_product`
                  WHERE  `'.$attribute.'`='.$id;
        $objRS = $objDatabase->SelectLimit($query, 1);
        $attributeName = $this->_getAttributeName($attribute, $id);
        if($objRS->RecordCount() > 0){
            $message = sprintf($_ARRAYLANG['TXT_PRINTSHOP_ATTRIBUTE_STILL_USED'], $attributeName);
        }else{
            $query = 'DELETE FROM `'.DBPREFIX.'module_printshop_'.$attribute.'`
                      WHERE `id` = '.$id;
            $objDatabase->Execute($query);
        }
        return $message;
    }


    /**
     * edit the print attributes
     *
     * @param string $attribute
     * @param int $id
     * @return array
     */
    function saveAttribute($attribute, $id){
        global $objDatabase;

        if(!$this->_isValidAtrribute($attribute)){
            return false;
        }
        $attributeValue = contrexx_addslashes(trim($_POST['psAttributeName']));

        $query = 'SELECT `id` FROM `'.DBPREFIX.'module_printshop_'.$attribute.'`
                  WHERE `'.$attribute."`='$attributeValue'";
        $objRS = $objDatabase->SelectLimit($query, 1);

        if($objRS->RecordCount() > 0){
            $alreadyExists = true;
            if($id == 0){
                $id = $objRS->fields['id'];
            }else{
                return array('id' => $id, 'name' => $attributeValue, 'exists' => $alreadyExists);
            }
        }else{
            $alreadyExists = false;
            if($id == 0){
                $id = 'NULL';
            }
        }

        $query = 'INSERT INTO `'.DBPREFIX.'module_printshop_'.$attribute.'` (`id`, `'.$attribute."`)
                    VALUES ($id, '$attributeValue')
                  ON DUPLICATE KEY
                    UPDATE  `id`         = LAST_INSERT_ID(`id`),
                            `$attribute` = '".$attributeValue."'";
        $objDatabase->Execute($query);
        $insertID = $objDatabase->Insert_ID();
        if($insertID > 0 ){
            $id = $insertID;
        }
        return array('id' => $id, 'name' => $attributeValue, 'exists' => $alreadyExists);
    }

    /**
     * Show the print attributes
     *
     * @param string $attribute
     */
    function showAttributes($attribute){
        global $_ARRAYLANG, $_CORELANG;
        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_ATTRIBUTES'];
        $this->_objTpl->loadTemplateFile('module_printshop_attributes.html', true, true);

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_ATTRIBUTE'               => $_ARRAYLANG['TXT_PRINTSHOP_'.strtoupper($attribute).'_TITLE'],
            'PRINTSHOP_ATTRIBUTE'                   => $attribute,
            'TXT_PRINTSHOP_NO_ENTRY'                => $_ARRAYLANG['TXT_PRINTSHOP_NO_ENTRY'],
            'TXT_PRINTSHOP_ADD_ATTRIBUTE'           => $_ARRAYLANG['TXT_PRINTSHOP_ADD_ATTRIBUTE'],
            'TXT_PRINTSHOP_UPDATE_ATTRIBUTE'        => $_ARRAYLANG['TXT_PRINTSHOP_UPDATE_ATTRIBUTE'],
            'TXT_PRINTSHOP_ENTER_ATTRIBUTE'         => $_ARRAYLANG['TXT_PRINTSHOP_ENTER_ATTRIBUTE'],
            'TXT_PRINTSHOP_ATTRIBUTE_NAME'          => $_ARRAYLANG['TXT_PRINTSHOP_ATTRIBUTE_NAME'],
            'TXT_PRINTSHOP_SUBTITLE_ACTIONS'        => $_ARRAYLANG['TXT_PRINTSHOP_SUBTITLE_ACTIONS'],
            'TXT_PRINTSHOP_DELETE_ENTRY'            => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_ENTRY'],
            'TXT_PRINTSHOP_DELETE_SELECTED'         => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_SELECTED'],
            'TXT_PRINTSHOP_ALREADY_EXISTS'          => $_ARRAYLANG['TXT_PRINTSHOP_ALREADY_EXISTS'],
            'TXT_PRINTSHOP_FADE_OUT'                => $_ARRAYLANG['TXT_PRINTSHOP_FADE_OUT'],
            'TXT_PRINTSHOP_TYPE_TITLE'              => $_ARRAYLANG['TXT_PRINTSHOP_TYPE_TITLE'],
            'TXT_PRINTSHOP_FORMAT_TITLE'            => $_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'],
            'TXT_PRINTSHOP_FRONT_TITLE'             => $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'],
            'TXT_PRINTSHOP_BACK_TITLE'              => $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'],
            'TXT_PRINTSHOP_PAPER_TITLE'             => $_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'],
            'TXT_PRINTSHOP_WEIGHT_TITLE'            => $_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'],
            'TXT_PRINTSHOP_EDIT'                    => $_ARRAYLANG['TXT_PRINTSHOP_EDIT'],
            'TXT_PRINTSHOP_DELETE'                  => $_ARRAYLANG['TXT_PRINTSHOP_DELETE'],
            'TXT_SELECT_ALL'                        => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'                      => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_MULTISELECT_SELECT'                => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_MULTISELECT_DELETE'                => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'DIRECTORY_INDEX'                       => CONTREXX_DIRECTORY_INDEX,
        ));

        $arrAttributes = $this->_getAttributes($attribute);

        if($arrAttributes){
            $index = 0;
            foreach ($arrAttributes as $id => $arrAttribute) {
                $this->_objTpl->setVariable(array(
                    'ENTRY_ROWCLASS'            => $index++ % 2 ? 'row2' : 'row1',
                    'PRINTSHOP_ATTRIBUTE_ID'    => $arrAttribute['id'],
                    'PRINTSHOP_ATTRIBUTE_NAME'  => $arrAttribute['name'],
                ));
                $this->_objTpl->parse('showEntry');
            }
        }else{
            $this->_objTpl->touchBlock('noEntry');
        }
    }


    /**
     * Shows the settings-page of the printshop-module.
     *
     * @global  array
     * @global  array
     */
    function showSettings() {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'];
        $this->_objTpl->loadTemplateFile('module_printshop_settings.html', true, true);

        $checked = 'checked="checked"';

        $this->_objTpl->setVariable(array(
            'TXT_PRINTSHOP_SETTINGS_TITLE'                          => $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'],
            'TXT_PRINTSHOP_EMAIL_HELP'                              => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_HELP'],
            'TXT_PRINTSHOP_ORDER_EMAIL'                             => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_EMAIL'],
            'TXT_PRINTSHOP_ENTRIES_PER_PAGE'                        => $_ARRAYLANG['TXT_PRINTSHOP_ENTRIES_PER_PAGE'],
            'TXT_PRINTSHOP_PRICE_THRESHOLDS'                        => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_THRESHOLDS'],
            'TXT_PRINTSHOP_PRICE_THRESHOLDS_HELP'                   => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_THRESHOLDS_HELP'],
            'TXT_PRINTSHOP_DATA_PREPARATION_PRICE'                  => $_ARRAYLANG['TXT_PRINTSHOP_DATA_PREPARATION_PRICE'],
            'TXT_PRINTSHOP_CURRENCY'                                => $_ARRAYLANG['TXT_PRINTSHOP_CURRENCY'],
            'TXT_PRINTSHOP_EMAIL_TEMPLATE_CUSTOMER'                 => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_TEMPLATE_CUSTOMER'],
            'TXT_PRINTSHOP_EMAIL_TEMPLATE_CUSTOMER_HELP'            => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_TEMPLATE_CUSTOMER_HELP'],
            'TXT_PRINTSHOP_EMAIL_TEMPLATE_VENDOR'                   => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_TEMPLATE_VENDOR'],
            'TXT_PRINTSHOP_EMAIL_TEMPLATE_VENDOR_HELP'              => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_TEMPLATE_VENDOR_HELP'],
            'TXT_PRINTSHOP_EMAIL_SUBJECT_CUSTOMER'                  => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_SUBJECT_CUSTOMER'],
            'TXT_PRINTSHOP_EMAIL_SUBJECT_VENDOR'                    => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_SUBJECT_VENDOR'],
            'TXT_PRINTSHOP_SHIPMENT_PRICE_MAIL'                     => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_PRICE_MAIL'],
            'TXT_PRINTSHOP_SHIPMENT_PRICE_MESSENGER'                => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_PRICE_MESSENGER'],
            'TXT_PRINTSHOP_SETTINGS_GENERAL'                        => $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_GENERAL'],
            'TXT_PRINTSHOP_SETTINGS_EMAIL'                          => $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_EMAIL'],
            'TXT_PRINTSHOP_SENDER_EMAIL'                            => $_ARRAYLANG['TXT_PRINTSHOP_SENDER_EMAIL'],
            'TXT_PRINTSHOP_SENDER_EMAIL_HELP'                       => $_ARRAYLANG['TXT_PRINTSHOP_SENDER_EMAIL_HELP'],
            'TXT_PRINTSHOP_SENDER_EMAIL_NAME'                       => $_ARRAYLANG['TXT_PRINTSHOP_SENDER_EMAIL_NAME'],
            'TXT_PRINTSHOP_SENDER_EMAIL_NAME_HELP'                  => $_ARRAYLANG['TXT_PRINTSHOP_SENDER_EMAIL_NAME_HELP'],
            'TXT_PRINTSHOP_ORDER_STATUS_ENABLED'                    => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_STATUS_ENABLED'],
            'TXT_PRINTSHOP_ORDER_STATUS_ENABLED_HELP'               => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_STATUS_ENABLED_HELP'],
            'TXT_PRINTSHOP_UPLOADED_IMAGES'                         => $_ARRAYLANG['TXT_PRINTSHOP_UPLOADED_IMAGES'],
            'TXT_PRINTSHOP_UPLOAD_LOCATION'                         => $_ARRAYLANG['TXT_PRINTSHOP_UPLOAD_LOCATION'],
            'TXT_PRINTSHOP_UPLOAD_LOCATION_HELP'                    => $_ARRAYLANG['TXT_PRINTSHOP_UPLOAD_LOCATION_HELP'],
            'TXT_PRINTSHOP_MANDATORY_IMAGE_UPLOAD_ENABLED'          => $_ARRAYLANG['TXT_PRINTSHOP_MANDATORY_IMAGE_UPLOAD_ENABLED'],
            'TXT_PRINTSHOP_MANDATORY_IMAGE_UPLOAD_ENABLED_HELP'     => $_ARRAYLANG['TXT_PRINTSHOP_MANDATORY_IMAGE_UPLOAD_ENABLED_HELP'],
            'TXT_SAVE'                                              => $_CORELANG['TXT_SAVE'],
            'PRINTSHOP_ORDER_EMAIL'                                 => $this->_arrSettings['orderEmail'],
            'PRINTSHOP_SENDER_EMAIL'                                => $this->_arrSettings['senderEmail'],
            'PRINTSHOP_SENDER_EMAIL_NAME'                           => $this->_arrSettings['senderEmailName'],
            'PRINTSHOP_ENTRIES_PER_PAGE'                            => $this->_arrSettings['entriesPerPage'],
            'PRINTSHOP_PRICE_THRESHOLDS'                            => $this->_arrSettings['priceThresholds'],
            'PRINTSHOP_DATA_PREPARATION_PRICE'                      => $this->_arrSettings['dataPreparationPrice'],
            'PRINTSHOP_SHIPMENT_PRICE_MAIL'                         => $this->_arrSettings['shipmentPriceMail'],
            'PRINTSHOP_SHIPMENT_PRICE_MESSENGER'                    => $this->_arrSettings['shipmentPriceMessenger'],
            'PRINTSHOP_CURRENCY'                                    => $this->_arrSettings['currency'],
            'PRINTSHOP_EMAIL_TEMPLATE_CUSTOMER'                     => $this->_arrSettings['emailTemplateCustomer'],
            'PRINTSHOP_EMAIL_TEMPLATE_VENDOR'                       => $this->_arrSettings['emailTemplateVendor'],
            'PRINTSHOP_EMAIL_SUBJECT_CUSTOMER'                      => $this->_arrSettings['emailSubjectCustomer'],
            'PRINTSHOP_EMAIL_SUBJECT_VENDOR'                        => $this->_arrSettings['emailSubjectVendor'],
            'PRINTSHOP_ORDER_STATUS_ENABLED'                        => $this->_arrSettings['orderStatusEnabled'] > 0 ? $checked : '',
            'PRINTSHOP_MANDATORY_IMAGE_UPLOAD_ENABLED'              => $this->_arrSettings['mandatoryImageUploadEnabled'] > 0 ? $checked : '',
        ));
    }




    /**
     * Validate and save the settings from $_POST into the database.
     *
     * @global  ADONewConnection
     * @global  array
     */
    function saveSettings() {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        if(!empty($_POST['save_settings'])) {
            foreach ($this->_settingNames as $setting) {
                switch($setting){
                    case 'entriesPerPage':
                        $_POST[$setting] = !empty($_POST[$setting]) && intval($_POST[$setting]) > 0 ? $_POST[$setting] : $_CONFIG['corePagingLimit'];
                    break;
                    case 'orderStatusEnabled':
                    case 'mandatoryImageUploadEnable':
                        $_POST[$setting] = !empty($_POST[$setting]) && intval($_POST[$setting]) > 0 ? 1 : 0;
                    break;
                    default:
                        $_POST[$setting] = !empty($_POST[$setting]) ? $_POST[$setting] : '';
                }

                $objDatabase->Execute('
                    UPDATE '.DBPREFIX.'module_printshop_settings
                    SET `value` = "'.contrexx_addslashes($_POST[$setting]).'"
                    WHERE `name` = "'.$setting.'"
                ');
            }
        }
        $this->_arrSettings = $this->createSettingsArray();
        $this->_strOkMessage = $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_SAVE_SUCCESSFUL'];
    }
}
