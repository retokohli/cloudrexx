<?php
/**
 * Printshop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
error_reporting(E_ALL);ini_set('display_errors',1);
//$objDatabase->debug=1;

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
        global $objTemplate;

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
     * Shows the print item overview
     *
     * @global  array
     * @global  array
     */
    function showProductOverview() {
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
            'TXT_PRINTSHOP_NO_ENTRY'         => $_ARRAYLANG['TXT_PRINTSHOP_NO_ENTRY'],
            'TXT_PRINTSHOP_ADD'              => $_ARRAYLANG['TXT_PRINTSHOP_ADD'],
            'TXT_PRINTSHOP_ADD_PRODUCT'      => $_ARRAYLANG['TXT_PRINTSHOP_ADD_PRODUCT'],
            'TXT_PRINTSHOP_PRODUCTS'         => $_ARRAYLANG['TXT_PRINTSHOP_PRODUCTS'],
            'TXT_PRINTSHOP_PRICE_TITLE'      => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_TITLE'],
            'TXT_PRINTSHOP_FACTOR_TITLE'     => $_ARRAYLANG['TXT_PRINTSHOP_FACTOR_TITLE'],
            'TXT_PRINTSHOP_SUBTITLE_ACTIONS' => $_ARRAYLANG['TXT_PRINTSHOP_SUBTITLE_ACTIONS'],
            'TXT_PRINTSHOP_FIELDS_REQUIRED'  => $_ARRAYLANG['TXT_PRINTSHOP_FIELDS_REQUIRED'],
            'TXT_PRINTSHOP_DELETE_ENTRY'     => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_ENTRY'],
            'TXT_PRINTSHOP_DELETE_SELECTED'  => $_ARRAYLANG['TXT_PRINTSHOP_DELETE_SELECTED'],
            'TXT_PRINTSHOP_FADE_OUT'         => $_ARRAYLANG['TXT_PRINTSHOP_FADE_OUT'],
            'TXT_PRINTSHOP_SAVE'             => $_ARRAYLANG['TXT_PRINTSHOP_SAVE'],
            'TXT_ADD_SUBMIT'                 => $_CORELANG['TXT_SAVE'],
            'TXT_SELECT_ALL'                 => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'               => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_MULTISELECT_SELECT'         => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_MULTISELECT_DELETE'         => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'PRINTSHOP_PAGING_LIMIT'         => $this->_arrSettings['entriesPerPage'],
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
                'PRINTSHOP_PAGING'  => getPaging($arrEntries['count'], $this->_pos, '&cmd=printshop', $_ARRAYLANG['TXT_PRINTSHOP_ENTRY'], true, $this->_limit),
            ));
        }else{
            $this->_objTpl->touchBlock('noEntry');
        }
        $this->_objTpl->setVariable(array(
            'PRINTSHOP_TOTAL_ENTRIES'   => $arrEntries['count'],
        ));
    }


    function showOrders(){
        global $_ARRAYLANG;

        $this->_strPageTitle = $_ARRAYLANG['TXT_PRINTSHOP_ORDERS_TITLE'];
        $this->_objTpl->loadTemplateFile('module_printshop_orders.html', true, true);


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

        $arrEntries = $this->_getEntries($arrFilter);
        if($arrEntries['count'] == 0){
            die(json_encode(array('error' => $_ARRAYLANG['TXT_PRINTSHOP_NO_ENTRY'])));
        }
        foreach ($arrEntries['entries'] as $index => $arrEntry) {
                $arrEntriesData[] =array(
                'type'          => $arrEntry['type'],
                'format'        => $arrEntry['format'],
                'front'         => $arrEntry['front'],
                'back'          => $arrEntry['back'],
                'weight'        => $arrEntry['weight'],
                'paper'         => $arrEntry['paper'],
                'price'         => $arrEntry['price'],
                'factor'        => $arrEntry['factor'],
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


    function _updateProduct(){
        global $_ARRAYLANG;

        $type   = !empty($_POST['psType'])   ? contrexx_addslashes($_POST['psType']) : '';
        $format = !empty($_POST['psFormat']) ? contrexx_addslashes($_POST['psFormat']) : '';
        $front  = !empty($_POST['psFront'])  ? contrexx_addslashes($_POST['psFront']) : '';
        $back   = !empty($_POST['psBack'])   ? contrexx_addslashes($_POST['psBack']) : '';
        $weight = !empty($_POST['psWeight']) ? contrexx_addslashes($_POST['psWeight']) : '';
        $paper  = !empty($_POST['psPaper'])  ? contrexx_addslashes($_POST['psPaper']) : '';
        $price  = array();
        foreach ($this->_priceThresholds as $index => $price){
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
                'error'     => 'null',
                'ok'        => $_ARRAYLANG['TXT_PRINTSHOP_ENTRY_UPDATED'],
                'price'     => $price,
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

        $this->_objTpl->setVariable(array(
            'TXT_PRINTSHOP_SETTINGS_TITLE'          => $_ARRAYLANG['TXT_PRINTSHOP_SETTINGS_TITLE'],
            'TXT_PRINTSHOP_EMAIL_HELP'              => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL_HELP'],
            'TXT_PRINTSHOP_ORDER_EMAIL'             => $_ARRAYLANG['TXT_PRINTSHOP_ORDER_EMAIL'],
            'TXT_PRINTSHOP_ENTRIES_PER_PAGE'        => $_ARRAYLANG['TXT_PRINTSHOP_ENTRIES_PER_PAGE'],
            'TXT_PRINTSHOP_PRICE_THRESHOLDS_HELP'   => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_THRESHOLDS_HELP'],
            'TXT_PRINTSHOP_PRICE_THRESHOLDS'        => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_THRESHOLDS'],
            'TXT_SAVE'                              => $_CORELANG['TXT_SAVE']
        ));

        $this->_objTpl->setVariable(array(
            'PRINTSHOP_SETTINGS_ORDER_EMAIL'        => $this->_arrSettings['orderEmail'],
            'PRINTSHOP_SETTINGS_ENTRIES_PER_PAGE'   => $this->_arrSettings['entriesPerPage'],
            'PRINTSHOP_SETTINGS_PRICE_THRESHOLDS'   => $this->_arrSettings['priceThresholds'],
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
