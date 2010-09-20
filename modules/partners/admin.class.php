<?php

/**
 * Partners
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      David Vogt
 * @version     v 1.0
 * @package     contrexx
 * @subpackage  partners
 */

require_once ASCMS_CORE_PATH . '/wysiwyg.class.php';
require_once ASCMS_FRAMEWORK_PATH . '/Image.class.php';
require_once ASCMS_MODULE_PATH.'/partners/lib/translation.php';
require_once ASCMS_MODULE_PATH.'/partners/lib/NGView.php';
require_once ASCMS_MODULE_PATH.'/partners/lib/Request.php';
require_once ASCMS_MODULE_PATH.'/partners/lib/NGMessaging.php';
require_once ASCMS_MODULE_PATH.'/partners/PartnersBase.php';
require_once ASCMS_MODULE_PATH.'/partners/model/Partner.php';
require_once ASCMS_MODULE_PATH.'/partners/model/AssignableLabel.php';
require_once ASCMS_MODULE_PATH.'/partners/model/Settings.php';
require_once ASCMS_MODULE_PATH.'/partners/views/LabelDropdownView.php';
require_once ASCMS_MODULE_PATH.'/partners/views/LabelBrowserView.php';

/**
 * Partners
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      David Vogt
 * @version     v 1.0
 * @package     contrexx
 * @subpackage  partners
 */
class PartnersAdmin extends PartnersBase
{
    public $_objTpl;
    public $settings;

    /**
     * Constructor   -> Create the module-menu and an internal template-object
     * @global   object      $objInit
     * @global   object      $objTemplate
     * @global   array       $_CORELANG
     */
    function __construct()
    {
        global $objInit, $objTemplate, $_ARRAYLANG;

        $this->settings = new PartnerSettings();
        $this->_objTpl = new NGView(ASCMS_MODULE_PATH.'/partners/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_intLanguageId = $objInit->userFrontendLangId;
        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->getId();
        $objTemplate->setVariable('CONTENT_NAVIGATION', '
            <a href="index.php?cmd=partners">'                   .tr('TXT_PARTNERS').'</a>
            <a href="index.php?cmd=partners&amp;act=listlabels">'.tr('TXT_PARTNERS_LABELS')   .'</a>
            <a href="index.php?cmd=partners&amp;act=settings">'  .tr('TXT_PARTNERS_SETTINGS_TITLE').'</a>
        ');
    }


    /**
    * Perform the right operation depending on the $_GET-params
    * @global   object      $objTemplate
    */
    function getPage() {
        global $objTemplate;

        if(!isset($_GET['act'])) {
            $_GET['act']='default';
        }
        $act = $_GET['act'];

        // get the NGMessaging data and put it where it belongs
        $this->get_messages();

        $func = $act . '_action';
        // TODO: error handling, access control
        $this->$func();

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->_strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));
    }


    /**
     * Handles the ?act=settings request.
     */
    function settings_action()
    {
        if (Request::is_post()) {
            switch(Request::GET('do')) {
            case 'new_parse_entry':
                $blockname = Request::POST('new_parseblock');
                $type      = Request::POST('label_type');
                $entry_id  = Request::POST($type);
                $entry     = LabelEntry::get($entry_id);

                $entry->parse_custom_block = $blockname;
                $entry->save();
                NGMessaging::save(tr('TXT_PARTNERS_BLOCK_SAVED'), 'partners_success');
                break;
            case 'drop_parse_entry':
                $entry = LabelEntry::get(Request::POST('entry_id'));
                $entry->parse_custom_block = '';
                $entry->save();
                NGMessaging::save(tr('TXT_PARTNERS_BLOCK_CLEARED'), 'partners_success');
                break;
            case 'misc':

                if (is_numeric(Request::POST('imagewidth'))) {
                    $this->settings->image_size = Request::POST('imagewidth');
                    $this->settings->hide_empty_labels = Request::POST('hide_empty_labels');
                    NGMessaging::save(tr('TXT_PARTNERS_SETTINGS_SAVED'), 'partners_success');
                }
                else {
                    NGMessaging::save(tr('TXT_PARTNERS_IMAGESIZE_INVALID_VALUE'), 'partners_error');
                }
                break;
            }
            // We stop processing here and do a safe redirect.
            CSRF::header("Location: index.php?cmd=partners&act=settings");
            exit();
        }

        $this->_strPageTitle = tr('TXT_SETTINGS');
        $this->_objTpl->loadTemplateFile('settings.html',true,true);
        $this->add_default_vars();
        $this->_objTpl->add_tr('TXT_PARTNERS_SETTINGS_PARSE_BLOCKS');
        $this->_objTpl->PARTNERS_IMAGE_SCALE_WIDTH = $this->settings->image_size;
        $this->_objTpl->PARTNERS_HIDE_EMPTY_CHECKED = $this->settings->hide_empty_labels ? 'checked="checked"' : '';


        // figure out all the labels that have a block assigned
        // and group them by AssignableLabel
        foreach (AssignableLabel::all($this->langid())->rs() as $label) {
            if (!$label->active) continue;
            $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
            $this->_objTpl->add_tr('TXT_PARTNERS_LABEL');
            $this->_objTpl->add_tr('TXT_PARTNERS_BLOCK_TO_PARSE');
            $this->_objTpl->add_tr('TXT_PARTNERS_NEW_ENTRY');
            $this->_objTpl->PARTNERS_ASSIGNABLELABEL = $label->name($this->langid());

            $dropdown = new LabelBrowserView(
                ASCMS_MODULE_PATH.'/partners/template',
                $label,
                $this->langid()
            );
            $this->_objTpl->TXT_PARTNERS_DD_NAME     = $dropdown->dropdown_name();
            $this->_objTpl->setVariable('TXT_PARTNERS_LABEL_BROWSER', $dropdown);

            // now loop over all LabelEntry objects of this AssignableLabel
            // and put them in the template.
            foreach ($label->labels($this->langid())->rs() as $entry) {
                if ($entry->parse_custom_block == '') continue;
                $this->_objTpl->ENTRY_ID   = $entry->id;
                $this->_objTpl->ENTRY_BLOCK= $entry->parse_custom_block;
                $this->_objTpl->setVariable('ENTRY_NAME',
                    $entry->hierarchic_name($this->langid(), '&nbsp;&gt;&nbsp;')
                );
                $this->_objTpl->parse('settings_parsing_label_entry');
            }
            $this->_objTpl->parse('settings_parsing_labels');
        }
    }


    function statustoggle_action()
    {
        $partner = Partner::get(Request::GET('id'));
        $partner->active = ! $partner->active;
        $partner->save();
        $str = $partner->active
            ? 'TXT_PARTNERS_PARTNER_ACTIVATED'
            : 'TXT_PARTNERS_PARTNER_DEACTIVATED'
        ;
        NGMessaging::save(tr($str), 'partners_success');
        if(strlen($_SERVER["HTTP_REFERER"]) > 5) {
            CSRF::header('Location: '.$_SERVER["HTTP_REFERER"]);
        } else {
            CSRF::header('Location: index.php?cmd=partners"');
        }
        exit();
    }


    function assignentry_action()
    {
        $this->force_ajax();

        $entry   = LabelEntry::get(Request::POST('entry_id'));
        $label   = $entry->label();
        $partner = null;
        if(Request::POST('partner_id')) {
            $partner = Partner::get(Request::POST('partner_id'));
            $partner->assign_entry($entry);
        } else {
            if (!$label->multiple_assignable || !isset($_SESSION['newpartner_labels'][$label->id])) {
                $_SESSION['newpartner_labels'][$label->id] = array(Request::POST('entry_id'));
            } else {
                $a = $_SESSION['newpartner_labels'][$label->id];
                $a[] = Request::POST('entry_id');
                $_SESSION['newpartner_labels'][$label->id] = $a;
            }
        }
        die($this->list_labels($partner, $label));
    }


    function dropentry_action()
    {
        $this->force_ajax();

        $entry   = LabelEntry::get(Request::POST('entry_id'));
        $label   = $entry->label();
        $partner = null;
        if(Request::POST('partner_id')) {
            $partner = Partner::get(Request::POST('partner_id'));
            $partner->drop_entry($entry);
        } else {
            if (!isset($_SESSION['newpartner_labels'][$label->id])) {
                $_SESSION['newpartner_labels'][$label->id] = array();
            } elseif(($idx = array_search(Request::POST('entry_id'), $_SESSION['newpartner_labels'][$label->id])) !== false) {
                $a = array();
                foreach($_SESSION['newpartner_labels'][$label->id] as $e) {
                    if ($e != Request::POST('entry_id'))
                        $a[] = $e;
                }
                $_SESSION['newpartner_labels'][$label->id] = $a;
            }
        }
        die($this->list_labels($partner, $label));
    }


    /**
     * Provides the HTML needed for a popup for
     * assign Label entries to a partner.
     */
    function assignentry_show_action() {
        $this->force_ajax();
        $this->_objTpl->loadTemplateFile('add_label.html',true,true);

        $label   = AssignableLabel::get(Request::POST('label_id'));
        $partner = Request::POST('partner_id') ? Partner::get(Request::POST('partner_id')) : null;
        $dropdown = new LabelBrowserView(
            ASCMS_MODULE_PATH.'/partners/template',
            $label,
            $this->langid()
        );
        $this->_objTpl->PARTNERS_LABEL_NAME            = $label->name($this->langid());
        $this->_objTpl->TXT_PARTNERS_LABEL_NAME        = $dropdown->dropdown_name();
        $this->_objTpl->LABEL_ID                       = $label->id;
        $this->_objTpl->PARTNER_ID                     = $partner ? $partner->id : 0;
        $this->_objTpl->add_tr('TXT_PARTNERS_ADD');
        $this->_objTpl->add_tr('TXT_PARTNERS_CLOSE');
        $this->_objTpl->setVariable('TXT_PARTNERS_LABEL_SEARCHFIELD', $dropdown);
        die($this->_objTpl->get());
    }


    /**
     * Handler for ?act=default or for a call without ?act parameter.
     *
     * Lists all the partners alphabetically.
     */
    function default_action()
    {
        $this->_strPageTitle = tr('TXT_PARTNERS_OVERVIEW_TITLE');
        if (Request::GET('tab') == 'new') {
            $this->_objTpl->loadTemplateFile('new_partner.html',true,true);
            $this->add_default_vars();

            $orig_tpl = $this->_objTpl;
            $this->_objTpl = new NGView(ASCMS_MODULE_PATH.'/partners/template');
            $this->editpartner_action(true, false);
            $orig_tpl->setVariable('NEWPARTNER_TAB_CONTENT', $this->_objTpl->get());
            $this->_objTpl = $orig_tpl;

        } else {
            $this->_objTpl->loadTemplateFile('list_partners.html',true,true);
            $this->add_default_vars();
            $this->_objTpl->add_tr('TXT_PARTNERS_LOOK_FOR_LABELFREE');
            $this->_objTpl->add_tr('TXT_PARTNERS_SPECIAL_SEARCH');
            $fulltext     = Request::cached_GET('search');
            $label_search = array();
            // Show AssignableLabel in template and also add search values to
            // $label_search so we can filter partners.
            foreach (AssignableLabel::all($this->langid())->rs() as $label) {
                if (!$label->active) continue;
                $dropdown = new LabelDropdownView(
                    ASCMS_MODULE_PATH.'/partners/template',
                    $label,
                    $this->langid()
                );
                if ($this->settings->hide_empty_labels) {
                    $dropdown->hide_empty();
                }
                $this->_objTpl->PARTNERS_LABEL_NAME            = $label->name($this->langid());
                $this->_objTpl->TXT_PARTNERS_LABEL_NAME        = $dropdown->dropdown_name();
                $this->_objTpl->setVariable('TXT_PARTNERS_LABEL_SEARCHFIELD', $dropdown);
                $this->_objTpl->parse('label_searchbox');
                $searched = Request::cached_GET($dropdown->dropdown_name());
                if (Request::GET('reset_search') == 1) {
                    Request::reset_cached_GET($dropdown->dropdown_name());
                } elseif ($searched) {
                    $label_search[] = intval($searched);
                }
            }
            if (Request::GET('reset_search') == 1) {
                Request::reset_cached_GET('search');
                $fulltext = '';
                $label_search = array();
            }
            // Search for label and fulltext data
            if (Request::GET('labelfree')) {
                $sql = Partner::all();
                $ptl = Partner2Label::typeinfo('table');
                $sql = "
                    SELECT p.* FROM ($sql) AS p
                    LEFT OUTER JOIN $ptl   AS l ON l.partner_id = p.id
                    WHERE l.label_id IS NULL
                    ORDER BY p.name";
                $data = new NGDb_Query($sql, 'Partner');
            }
            else {
                $data = Partner::search($fulltext, $label_search);
            }
            $this->_objTpl->PARTNERS_TOTAL = Partner::all()->count();
            $url_template = "index.php?cmd=partners&search=" . htmlspecialchars($fulltext) . '&p=%p';
            $this->_objTpl->TXT_SEARCH_VALUE = $fulltext;
            $this->_objTpl->add_tr('TXT_PARTNERS_TOTAL');
            $this->_objTpl->PARTNERS_ROW_COUNT = $data->count();
            $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
            $pager = new NGView_Pager($data, Request::GET('p', 0));
            $pager->put_placeholders($this->_objTpl, $url_template);
            foreach ($pager->current()->rs() as $partner) {
                $this->_objTpl->PARTNER_ID            = $partner->id;
                $this->_objTpl->PARTNER_NAME          = $partner->name;
                $this->_objTpl->PARTNER_NUM_INSTALLATIONS= $partner->num_installations;
                $this->_objTpl->PARTNER_CONTACT_NAME = $partner->first_contact_name ? $partner->first_contact_name : $partner->first_contact_email;
                $this->_objTpl->PARTNER_CONTACT_EMAIL = $partner->first_contact_email;
                $this->_objTpl->PARTNER_WEB_URL       = $partner->web_url;
                $this->_objTpl->PARTNER_CITY          = $partner->city;
                $this->_objTpl->PARTNER_ADDRESS       = $partner->address;
                $this->_objTpl->PARTNER_ZIP_CODE      = $partner->zip_code;
                $this->_objTpl->PARTNER_PHONE_NR      = $partner->phone_nr;
                $this->_objTpl->PARTNER_FAX_NR        = $partner->fax_nr;
                $this->_objTpl->LED_COLOR             = $partner->active ? 'green' : 'red';
                $this->_objTpl->parse('partner_entry');
            }
        }
    }



    /**
     * Handle the deletepartner ajax request.
     */
    function deletepartner_action() {
        // This is an AJAX handler. anything else we don't accept!
        $this->force_ajax();
        $partner = Partner::get(Request::POST('id'));
        $partner->delete();
        die("OK");
    }


    /**
     * Redirects ?cmd=newpartner to editpartner_action and tell them what to do.
     */
    function newpartner_action()
    {
        $ret = $this->editpartner_action(true, false);
        $this->_strPageTitle = tr('TXT_PARTNERS_NEW_PARTNER');
        return $ret;
    }


    /**
     * Handler for ?cmd=editpartner
     *
     * Edit a partner object
     */
    function editpartner_action($new_action=false, $standalone=true)
    {
        if($standalone) {
            $this->_strPageTitle = tr('TXT_PARTNERS_EDIT_PARTNER');
        }
        $this->_objTpl->loadTemplateFile('edit_partner.html',true,true);
        if ($new_action) {
            #print "------------ new! ------------";
            $partner = new Partner();
            $partner->creation_date = date("Y-m-d");
            $partner->web_url       = 'http://';
            $partner->id = 0;
            $this->_objTpl->TXT_PARTNERS_EDIT_PARTNER = tr('TXT_PARTNERS_NEW_PARTNER');
            $this->_objTpl->EDITPARTNER_FORM_TARGET   = 'index.php&cmd=partners&act=newpartner';
            if (!Request::is_post()) {
                unset($_SESSION['newpartner_labels']);
            }
        } else {
            #print "------------ existing! ------------";
            $partner = Partner::get(Request::GET('id'));
        }
        $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_PARTNER');
        $this->add_default_vars();
        $this->_objTpl->PARTNERS_IMAGE_PREFIX = ASCMS_IMAGE_PATH;
        $langid = $this->langid();
        $this->_objTpl->setVariable('PARTNERS_LABELENTRY_DROP_URL',   'index.php?cmd=partners&act=dropentry');
        $this->_objTpl->setVariable('PARTNERS_LABELENTRY_ASSIGN_URL', 'index.php?cmd=partners&act=assignentry');
        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
        foreach (AssignableLabel::all($langid)->rs() as $label) {
            if (!$label->active) continue;
            $html = $this->list_labels($new_action ? null : $partner, $label);
            $this->_objTpl->LABEL_NAME = $label->multiple_assignable ? $label->name($langid) : $label->name($langid);
            $this->_objTpl->LABEL_ID   = $label->id;
            $this->_objTpl->PARTNER_ID = $partner->id;
            $this->_objTpl->add_tr('TXT_PARTNERS_ADD_LABEL');
            $this->_objTpl->setVariable('LABEL_LIST', $html);
            $this->_objTpl->parse('add_labels');
        }
        if (Request::is_post() && Request::POST('savepartner')) {
            $partner->active =              Request::POST('active') == 'on' ? 1 : 0;
            $partner->name =                Request::POST('name');
            $partner->first_contact_name =  Request::POST('contact');
            $partner->first_contact_email = Request::POST('email');
            $partner->web_url =             Request::POST('web_url');
            $partner->address =             Request::POST('address');
            $partner->city =                Request::POST('city');
            $partner->zip_code =            Request::POST('zipcode');
            $partner->phone_nr =            Request::POST('phone_nr');
            $partner->fax_nr =              Request::POST('fax_nr');
            $partner->customer_quote =      Request::POST('customer_quote');
            $partner->logo_url =            Request::POST('logo_url');
            $partner->creation_date =       Request::POST('created');
            $partner->num_installations =   Request::POST('num_installations');
            $partner->user_id =             Request::POST('user_id');
            $partner->description =         Request::POST('description');
            if ($partner->validate(array($this, 'add_errormessage'))) {
                if ($new_action) {
                    // HACK HACK HACK: $partner->id was set to 0 before to make
                    // list_labels() etc work. but save() assumes an entry in db
                    // if the id is not null
                    $partner->id = null;
                }
                $partner->save();
                $logo_file = ASCMS_DOCUMENT_ROOT . '/images' . $partner->logo_url;
                $logo_url  = ASCMS_IMAGE_PATH                . $partner->logo_url;
                if ($partner->logo_url && file_exists($logo_file)) {
                    if (!file_exists($logo_file.".thumb")) {
                        $path    = dirname($logo_file)."/";
                        $webpath = dirname($logo_url)."/";
                        $file    = basename($logo_file);
                        $im = new ImageManager;
                        $width  = $this->settings->image_size;
                        $im->_createThumb($path, $webpath, $file, $width, $width*.8, $quality=90);
                    }
                }
                foreach ($_SESSION['newpartner_labels'] as $label_id => $entry_list) {
                    foreach ($entry_list as $entry_id) {
                        $entry = LabelEntry::get($entry_id);
                        $partner->assign_entry($entry);
                    }
                }
                NGMessaging::save(tr('TXT_PARTNERS_SAVED'), 'partners_success');
                // redirect to edit form
                CSRF::header("Location: index.php?cmd=partners&act=editpartner&id=" . $partner->id);
                exit();
            }
        }
        $this->_objTpl->PARTNER_ACTIVE =       $partner->active ? 'checked="checked"' : '';
        $this->_objTpl->PARTNER_NAME =         $partner->name;
        $this->_objTpl->PARTNER_CONTACT_NAME  = $partner->first_contact_name;
        $this->_objTpl->PARTNER_CONTACT_EMAIL =$partner->first_contact_email;
        $this->_objTpl->PARTNER_WEB_URL =      $partner->web_url;
        $this->_objTpl->PARTNER_ZIP_CODE =     $partner->zip_code;
        $this->_objTpl->PARTNER_ADDRESS =      $partner->address;
        $this->_objTpl->PARTNER_PHONE_NR =     $partner->phone_nr;
        $this->_objTpl->PARTNER_FAX_NR =       $partner->fax_nr;
        $this->_objTpl->PARTNER_LOGO_URL =     ASCMS_IMAGE_PATH . $partner->logo_url;
        $this->_objTpl->PARTNER_LOGO_URL_INPUT=$partner->logo_url;
        $this->_objTpl->PARTNER_CITY =         $partner->city;
        $this->_objTpl->PARTNER_CUSTOMER_QUOTE=$partner->customer_quote;
        $this->_objTpl->PARTNER_CREATED       =$partner->creation_date;
        $this->_objTpl->PARTNER_USER_ID =      $partner->user_id;
        $this->_objTpl->PARTNER_NUM_INSTALLATIONS = $partner->num_installations ? $partner->num_installations : 0;
        $this->_objTpl->setVariable('PARTNER_CUSTOMER_DESCRIPTION', get_wysiwyg_editor('description', $partner->description));
    }


    function _multiaction_sort()
    {
        NGMessaging::save(tr('TXT_PARTNERS_SORTING_UPDATED'), 'partners_success');
        $tbl = Partner::typeinfo('table');
        $sql = "UPDATE `$tbl` SET `num_installations` = %0 WHERE `id` = %1";
        foreach ($_POST['partner_inst'] as $p_id => $inst) {
            NGDb::parse_execute($sql, $inst, $p_id);
        }
    }

    function partnermultiaction_action()
    {
        // confirmation was done by javascript, so we just do what we're told!
        $tbl = Partner::typeinfo('table');
        $sql_fragment = false;
        switch (Request::POST('multiaction')) {
            case "delete":
                NGMessaging::save(tr('TXT_PARTNERS_PARTNER_DELETED'), 'partners_success');
                $sql_fragment = "DELETE FROM `$tbl` WHERE id= %0";
                break;
            case "activate":
                NGMessaging::save(tr('TXT_PARTNERS_PARTNER_ACTIVATED'), 'partners_success');
                $sql_fragment = "UPDATE `$tbl` SET active=1 WHERE id= %0";
                break;
            case "deactivate":
                NGMessaging::save(tr('TXT_PARTNERS_PARTNER_DEACTIVATED'), 'partners_success');
                $sql_fragment = "UPDATE `$tbl` SET active=0 WHERE id= %0";
                break;
            default:
                $this->_multiaction_sort();
        }
        if ($sql_fragment) {
            foreach (Request::POST('checked_partner') as $e_id) {
                NGDb::parse_execute($sql_fragment, $e_id);
            }
        }
        CSRF::header("Location: index.php?cmd=partners");
        exit();
    }


    /**
     * Handler for ?cmd=listlabels
     *
     * Lists all AssignableLabel objects.
     */
    function listlabels_action()
    {
        $this->_strPageTitle = tr('TXT_PARTNERS_LABELS');
        $this->_objTpl->loadTemplateFile('list_labels.html',true,true);
        $this->add_default_vars();
        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
        $lang_id = $this->langid();
        foreach (AssignableLabel::all($lang_id)->rs() as $label) {
            $this->_objTpl->LABEL_NAME = $label->name($lang_id);
            $this->_objTpl->LABEL_ID   = $label->id;
            $this->_objTpl->LABEL_COUNT_ENTRIES = $label->num_labels();
            $this->_objTpl->LED_COLOR  = $label->active ? 'green' : 'red';
            $this->_objTpl->add_tr('TXT_LABEL_COUNT_ENTRIES');
            $this->_objTpl->parse('label_entry');
        }

        // hack to get newlabel action's result
        $old_tpl = $this->_objTpl;
        $this->_objTpl = new NGView(ASCMS_MODULE_PATH.'/partners/template');
        $this->editlabel_action(true);
        $newform = $this->_objTpl->get();
        $this->_objTpl = $old_tpl;
        $this->_objTpl->setVariable('NEWLABEL_FORM', $newform);
    }


    function editlabel_action($new_action=false)
    {
        $this->_strPageTitle = tr('TXT_PARTNERS_LABELS');
        $this->_objTpl->loadTemplateFile('edit_label.html',true,true);
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_PLACEHOLDER');
        $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_LABEL_TEXTS');
        $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_LABEL_TEXTS_SINGULAR');
        $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_LABEL_TEXTS_PLURAL');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_MULTI_ASSIGNABLE');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_ENTRIES');
        $this->_objTpl->add_tr('TXT_PARTNERS_CUSTOM_BLOCK');
        $this->_objTpl->add_tr('TXT_PARTNERS_DELETE_ENTRY_QUESTION');
        $this->_objTpl->add_tr_global('TXT_PARTNERS_NEW_ENTRY');
        if ($new_action) {
            #print "------------ new! ------------";
            $label = new AssignableLabel();
            $this->_objTpl->TXT_PARTNERS_EDIT_LABEL = tr('TXT_PARTNERS_NEW_LABEL');
            $this->_objTpl->hideBlock('entries_block');
            $this->_objTpl->hideBlock('datasource_refresh');
            $this->_objTpl->add_tr('TXT_PARTNERS_SAVE_BEFORE_EDITING_ENTRIES');
        } else {
            #print "------------ existing! ------------";
            $label = AssignableLabel::get(Request::GET('id'));
            $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_LABEL');
            $this->_objTpl->hideBlock('save_first_please');
        }
        $this->show_datasources($label->datasource);
        $all_language_ids = array();
        foreach ($this->_languages() as $lang) {
            $all_language_ids[] = $lang->id;
        }
        $this->add_default_vars();
        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_ENTRY_DEFAULT_PARTNER');
        if (!$new_action) {
            $this->_loop_entries($label);
        }
        if (Request::is_post()) {
            $label->label_placeholder   = Request::POST('placeholder');
            $label->datasource          = Request::POST('datasource');
            $label->multiple_assignable = Request::POST('multi_assignable') == 'on' ? 1 : 0;
            $name   = Request::POST('name');
            foreach ($all_language_ids as $lang_id) {
                $label->name  ($lang_id, $name  [$lang_id]);
            }
            $label->save();
            if ($new_action || Request::POST('enable_autoimport') == 'on') {
                $ds = $this->datasource_importer($label);
                $ds->import();
            }
            NGMessaging::save(tr('TXT_PARTNERS_LABEL_SAVED'), 'partners_success');
            // redirect to edit form
            CSRF::header("Location: index.php?cmd=partners&act=editlabel&id=" . $label->id);
        }
        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
        foreach ($this->_languages() as $lang) {
            $all_language_ids[] = $lang->id;
            $this->_objTpl->PARTNERS_LANGID            = $lang->id;
            $this->_objTpl->PARTNERS_LANGUAGE          = $lang->name;
            $this->_objTpl->PARTNERS_LANGUAGE_SINGULAR = $label->name($lang->id);
            $this->_objTpl->parse('translation_entry');
        }
        $this->_objTpl->LABEL_PLACEHOLDER          = $label->label_placeholder;
        $this->_objTpl->LABEL_MULTI_ASSIGNABLE_CHK = $label->multiple_assignable ? 'checked="checked"' : '';
    }


    private function show_datasources($current_source = '')
    {
        // <option value="{PARTNERS_DATASOURCE_ID}">{PARTNERS_DATASOURCE_NAME}</option>
        $sel = 'selected="selected"';
        // Default: NO data source
        $this->_objTpl->PARTNERS_DATASOURCE_SEL  = '';
        $this->_objTpl->PARTNERS_DATASOURCE_ID   = '';
        $this->_objTpl->PARTNERS_DATASOURCE_NAME = tr('TXT_PARTNERS_DATASOURCE_NONE');
        $this->_objTpl->parse('datasource_option');
        // Datasource "shop countries"
        $this->_objTpl->PARTNERS_DATASOURCE_SEL  = $current_source == 'shop_countries' ? $sel : '';
        $this->_objTpl->PARTNERS_DATASOURCE_ID   = 'shop_countries';
        $this->_objTpl->PARTNERS_DATASOURCE_NAME = tr('TXT_PARTNERS_DATASOURCE_SHOP_COUNTRIES');
        $this->_objTpl->parse('datasource_option');
    }


    function datasource_importer($label)
    {
        $path = ASCMS_MODULE_PATH.'/partners/model/';
        switch ($label->datasource) {
            case 'shop_countries':
                require_once($path.'DataSource_ShopCountries.php');
                return new DataSource_ShopCountries($label, $this->langid(), $this->_languages());
            default:
                require_once($path.'DataSource.php');
                return new DataSource($label, $this->langid(), $this->_languages());
        }
    }


    function labelmultiaction_action()
    {
        // confirmation was done by javascript, so we just do what we're told!
        $tbl = AssignableLabel::typeinfo('table');
        $sql_fragment = false;
        switch (Request::POST('multiaction')) {
            case "delete":
                NGMessaging::save(tr('TXT_PARTNERS_LABEL_DELETED'), 'partners_success');
                $sql_fragment = "DELETE FROM `$tbl` WHERE id= %0";
                break;
            case "activate":
                NGMessaging::save(tr('TXT_PARTNERS_LABEL_ACTIVATED'), 'partners_success');
                $sql_fragment = "UPDATE `$tbl` SET active=1 WHERE id= %0";
                break;
            case "deactivate":
                NGMessaging::save(tr('TXT_PARTNERS_LABEL_DEACTIVATED'), 'partners_success');
                $sql_fragment = "UPDATE `$tbl` SET active=0 WHERE id= %0";
                break;
        }
        if ($sql_fragment) {
            foreach (Request::POST('checked_label') as $e_id) {
                NGDb::parse_execute($sql_fragment, $e_id);
            }
        }
        CSRF::header("Location: index.php?cmd=partners&act=listlabels");
        exit();
    }


    /**
     * Handle the deletelabel ajax request.
     */
    function deletelabel_action()
    {
        // This is an AJAX handler. anything else we don't accept!
        $this->force_ajax();
        $label = AssignableLabel::get(Request::POST('id'));
        $label->delete();
        die("OK");
    }


    /**
     * Handle the deleteentry ajax request.
     */
    function deleteentry_action()
    {
        // This is an AJAX handler. anything else we don't accept!
        $this->force_ajax();
        $entry = LabelEntry::get(Request::POST('id'));
        $entry->delete();
        die("OK");
    }


    function newentry_popup_action($die=true)
    {
        $this->editentry_popup_action(false);
    }


    function editentry_popup_action($editing=true)
    {
        $this->_objTpl->loadTemplateFile('edit_entry.html',true,true);
        $this->_objTpl->LABEL_ID                 = Request::POST('id');
        $this->_objTpl->PARENT_ID                = Request::POST('parent_id');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_ENTRY_DEFAULT_PARTNER');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_ENTRY_PARSE_CUSTOM_BLOCK');
        $this->_objTpl->add_tr('TXT_PARTNERS_EDIT_LABEL_TEXTS');
        $this->_objTpl->add_tr('TXT_SAVE');
        $this->_objTpl->add_tr('TXT_CANCEL');
        if ($editing) {
            // we need to fill in all the label's data
            $entry = LabelEntry::get(Request::POST('entry_id'));
            $this->_objTpl->ENTRY_DEFAULTPARTNER_CHK = $entry->default_partner ? 'checked="checked"' : '';
            $this->_objTpl->ENTRY_PARSE_CUSTOM_BLOCK = $entry->parse_custom_block;
            $this->_objTpl->TXT_TITLE                = tr('TXT_PARTNERS_EDIT_ENTRY');
            $this->_objTpl->global_ENTRY_ID                 = $entry->id;
            $this->_objTpl->global_LABEL_ID                 = $entry->label_id;
            $this->_objTpl->global_PARENT_ID                = $entry->parent_entry_id;
            foreach ($this->_languages() as $lang) {
                $this->_objTpl->PARTNERS_LANGID            = $lang->id;
                $this->_objTpl->PARTNERS_LANGUAGE          = $lang->name;
                $this->_objTpl->PARTNERS_ENTRY_TRANSLATION = $entry->name($lang->id);
                $this->_objTpl->parse('translation_entry');
            }
        } else {
            $this->_objTpl->TXT_TITLE                = tr('TXT_PARTNERS_NEW_ENTRY');
            // newentry_popup_action() called us
            foreach ($this->_languages() as $lang) {
                $this->_objTpl->PARTNERS_LANGID            = $lang->id;
                $this->_objTpl->PARTNERS_LANGUAGE          = $lang->name;
                $this->_objTpl->PARTNERS_ENTRY_TRANSLATION = ''; # we're creating a new one here
                $this->_objTpl->parse('translation_entry');
            }
        }
        die($this->_objTpl->get());
    }


    function entry_list()
    {
        $template = file_get_contents(dirname(__FILE__)."/template/edit_label.html");
        $template = preg_replace("#.*<!--\s+BEGIN\s+entries_block\s+-->#msi", '', $template);
        $template = preg_replace("#<!--\s+END\s+entries_block\s+-->.*#msi",   '', $template);
        $this->_objTpl->setTemplate($template);
        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_ENTRIES');
        $this->_objTpl->add_tr('TXT_PARTNERS_CUSTOM_BLOCK');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL_ENTRY_DEFAULT_PARTNER');
        $this->_loop_entries(AssignableLabel::get(Request::POST('label_id')));
    }


    /**
     * Save a LabelEntry object (read information from POST data)
     */
    private function _save_entry_obj($entry)
    {
        $this->_set_if_nonempty($entry, 'parent_entry_id', Request::POST('parent_id'));
        $this->_set_if_nonempty($entry, 'label_id',        Request::POST('label_id'));
        $entry->parse_custom_block = Request::POST('parse_custom_block');
        $entry->default_partner    = Request::POST('default_partner') == 'on';
        $entry->save();
        $trans_data = Request::POST('name');
        foreach ($this->_languages() as $lang) {
            $entry->name($lang->id, $trans_data[$lang->id]);
        }
    }


    function editentry_save_action()
    {
        $this->force_ajax();
        $entry = LabelEntry::get(Request::POST('entry_id'));
        $this->_save_entry_obj($entry);
        $this->entry_list();
        $this->_objTpl->JS_CREATED_ENTRY_ID = $entry->id;
        print $this->_objTpl->get();
        exit();
    }


    function newentry_save_action()
    {
        $this->force_ajax();
        $entry = new LabelEntry();
        $this->_save_entry_obj($entry);
        $this->add_default_vars();
        // Create entries list from edit_entry.html and fill in all
        // the template variables
        $this->entry_list();
        $this->_objTpl->JS_CREATED_ENTRY_ID = $entry->id;
        $this->_objTpl->add_tr('TXT_PARTNERS_NEW_ENTRY');
        $this->_objTpl->add_tr('TXT_PARTNERS_LABEL');
        print $this->_objTpl->get();
        exit();
    }


    /**
     * Helper for listing all th entries of an AssignableLabel.
     * @param AssignableLabel $label
     */
    private function _loop_entries($label) {
        $entries = $label->labels_with_indent($this->langid());
        $this->_objTpl->global_LABEL_ID   = $label->id;
        foreach ($entries as $entry_combo) {
            extract($entry_combo);
            $this->_objTpl->ENTRY_DEFAULT_PARTNER = $entry->default_partner ? tr('TXT_YES') : tr('TXT_NO');
            $this->_objTpl->ENTRY_ID     = $entry->id;
            $this->_objTpl->ENTRY_NAME   = $entry->name($this->langid());
            // FIXME: remove that * 2 as soon as we can do it better in the template
            $this->_objTpl->ENTRY_INDENT = $indent * 2;
            $this->_objTpl->ENTRY_ADDITIONAL_BLOCK = $entry->parse_custom_block ? $entry->parse_custom_block : '-';
            $this->_objTpl->ENTRY_PARTNER_COUNT = $entry->partner_count();
            $this->_objTpl->parse('entry_line');
        }
    }

}

?>
