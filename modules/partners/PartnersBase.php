<?php

//error_reporting(E_ALL);

/**
 * Partners library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      David Vogt
 * @version     v 1.00
 * @package     contrexx
 * @subpackage  partners
 */
class PartnersBase {
    var $_strPageTitle  = '';
    var $_strErrMessage = '';
    var $_strOkMessage  = '';
    /**
     * Returns the language id of the current language. Works in frontend and backend.
     */
    function langid() {
        global $objInit;
        if ($objInit->mode == 'backend') {
            return $objInit->backendLangId;
        }
        return $objInit->frontendLangId;
    }

    /**
     * Generic helper that only sets a property on an object
     * if the value to set is not empty().
     * @param object $object The object where the property should be set
     * @param string $field  The field (or property) that should be set
     * @param variant $value The value to be stored.
     */
    function _set_if_nonempty($object, $field, $value) {
        if (!empty($value)) {
            $object->$field = $value;
        }
    }


    /**
     * Reads messages tagged partners_success and partners_error
     * from NGMessaging and saves them in $this->_strOkMessage
     * and $this->_strErrMessage respectively.
     * No parameters, no returns.
     */
    function get_messages() {
        foreach (NGMessaging::fetch('partners_success') as $msg) {
            $this->add_successmessage($msg);
        }
        foreach(NGMessaging::fetch('partners_error') as $msg) {
            $this->add_errormessage($msg);
        }
    }

    function add_successmessage($message) {
        if($this->_strOkMessage) {
            $this->_strOkMessage .= '<p/>'.$message;
        }
        else {
            $this->_strOkMessage = $message;
        }
    }
    function add_errormessage($message) {
        if($this->_strErrMessage) {
            $this->_strErrMessage .= '<p/>'.$message;
        }
        else {
            $this->_strErrMessage = $message;
        }
    }

    /**
     * Returns a NGRecordSet object containing
     * all currently active languages.
     * The selected fields are: id, name.
     */
    function _languages() {
        static $lang_cache = Null;
        if (is_null($lang_cache)) {
            $lang_table = DBPREFIX . "languages";
            $lang_cache = NGDb::query(
                "SELECT id, name FROM $lang_table WHERE backend = 1 OR frontend = 1 ORDER BY name"
            );
        }
        return $lang_cache;
    }

    // {{{ Utilities
    protected function add_default_vars() {
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_CITY');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_CONTACT');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_EMAIL');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_NAME');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_URL');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_ZIP');
        $this->_objTpl->add_tr('TXT_PARTNERS_ROW_COUNT');
        $this->_objTpl->add_tr('TXT_SAVE');
        $this->_objTpl->add_tr('TXT_PARTNERS_PAGE');
        $this->_objTpl->add_tr('TXT_SEARCH');
        $this->_objTpl->add_tr('TXT_PARTNERS_DELETE_QUESTION');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_QUOTE');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_DESCRIPTION');
        $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_LABELS');
        $this->_objTpl->add_tr('TXT_PARTNERS_DELETE_LABEL_QUESTION');
        $this->_objTpl->add_tr('TXT_PARTNERS_OVERVIEW_TITLE');
        $this->_objTpl->add_tr('TXT_PARTNERS_NEW_PARTNER');
        $this->_objTpl->add_tr('TXT_ACTION');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABELS');
        $this->_objTpl->add_tr('TXT_PARTNERS_NEW_LABEL');
        $this->_objTpl->add_tr('TXT_PARTNERS_ARE_YOU_SURE_TO_DELETE_LABELS');
        $this->_objTpl->add_tr('TXT_PARTNERS_ARE_YOU_SURE_TO_DELETE_PARTNERS');
        $this->_objTpl->add_tr('TXT_MULTISELECT_SELECT');
        $this->_objTpl->add_tr('TXT_SETTINGS_DELETE');
        $this->_objTpl->add_tr('TXT_ACTIVATE_DESIGN');
        $this->_objTpl->add_tr('TXT_PARTNERS_DEACTIVATE');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_ACTIVE');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_PHONE_NR');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_FAX_NR');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_ADDRESS');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_LOGO');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_LOGO_EDIT');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_LOGO_REMOVE');
        $this->_objTpl->add_tr('TXT_PARTNERS_ADD_LABEL');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL');
        $this->_objTpl->add_tr('TXT_PARTNERS_COMPANY_CREATED');
        $this->_objTpl->add_tr('TXT_PARTNERS_IMPORT_DATASOURCE');
        $this->_objTpl->add_tr('TXT_PARTNERS_DATASOURCE_NONE');
        $this->_objTpl->add_tr('TXT_PARTNERS_DATASOURCE_REFRESH');
        $this->_objTpl->add_tr('TXT_PARTNERS_SETTINGS_IMAGES');
        $this->_objTpl->add_tr('TXT_PARTNERS_SETTINGS_MISC');
        $this->_objTpl->add_tr('TXT_PARTNERS_IMAGE_SCALE_WIDTH');
        $this->_objTpl->add_tr('TXT_PARTNERS_RESET_SEARCH');
        $this->_objTpl->add_tr('TXT_PARTNERS_HIDE_EMPTY_LABELS');
        $this->_objTpl->PARTNERS_DROPDOWN_JS_LOCATION     = ASCMS_MODULE_WEB_PATH . '/partners/js/dropdown.js';
        $this->_objTpl->PARTNERS_LABELBROWSER_JS_LOCATION = ASCMS_MODULE_WEB_PATH . '/partners/js/labelbrowser.js';
        $this->_objTpl->PARTNERS_LABEL_JS_LOCATION        = ASCMS_MODULE_WEB_PATH . '/partners/js/labels.js';
        $this->_objTpl->PARTNERS_POPUP_JS_LOCATION        = ASCMS_MODULE_WEB_PATH . '/partners/js/popup.js';
    }
    //  }}}


}

# vim:foldmethod=marker:et:sw=4:ts=4

