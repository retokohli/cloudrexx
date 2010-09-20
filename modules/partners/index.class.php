<?php

/**
 * Partners Module - Frontend code
 * @copyright   COMVATION AG
 * @author      David Vogt
 * @subpackage  module_partners
 */

require_once ASCMS_CORE_PATH . '/wysiwyg.class.php';
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
 * Partners Module - Frontend code
 * @copyright   COMVATION AG
 * @author      David Vogt
 * @subpackage  module_partners
 */
class PartnersFrontend extends PartnersBase  {

    public $_objTpl;

    // gets set in detail action, so the partner's name
    // can be retreived after getPage() using getTitle().
    private $current_partner = null;

    private $save_errors = array();


    /**
    * Constructor   -> Create the module-menu and an internal template-object
    * @global   object      $objInit
    * @global   object      $objTemplate
    * @global   array       $_ARRAYLANG
    */
    function __construct($content)
    {
        global $objInit, $objTemplate, $_ARRAYLANG;

        $this->settings = new PartnerSettings;
        JS::registerJS('modules/partners/js/dropdown.js');
        JS::registerJS('modules/partners/js/labels.js');
        JS::registerJS('modules/partners/js/labelbrowser.js');
        JS::registerJS('modules/partners/js/labelfrontend.js');
        JS::activate('prototype');
        JS::activate('scriptaculous');
        $this->_objTpl = new NGView('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($content);
        $this->_intLanguageId = $objInit->userFrontendLangId;
        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->getId();
    }


    /**
     * Perform the right operation depending on the $_GET-params
     * @global   object      $objTemplate
     */
    function getPage()
    {
        if(!isset($_GET['act'])) {
            $_GET['act']='default';
        }
        $act = $_GET['cmd'] ? $_GET['cmd'] : 'default';
        if (isset($_GET['ajax'])) {
            $act = $_GET['ajax'];
        }
        // get the NGMessaging data and put it where it belongs
        $this->get_messages();
        $this->add_default_vars();
        $func = $act . '_action';
        // TODO: error handling, access control
        $this->$func();
        return $this->_objTpl->get();
    }


    function getTitle($orig_title)
    {
        if ($this->current_partner) {
            return $this->current_partner->name;
        }
        return $orig_title;
    }


    function signup_action()
    {
        $partner = new Partner();
        $to_assign = array();
        foreach (AssignableLabel::all($this->langid())->rs() as $label) {
            if (!$label->active) continue;
            DBG::msg("checking for label browser block " . 'PARTNER_'.$label->label_placeholder);
            if (!$this->_objTpl->blockExists('PARTNER_'.$label->label_placeholder)) continue;
            DBG::msg("...ok, found");
            $dropdown = new LabelBrowserView(
                ASCMS_MODULE_PATH.'/partners/template',
                $label,
                $this->langid()
            );
            if ($this->settings->hide_empty_labels) {
                $dropdown->hide_empty();
            }
            $this->_objTpl->PARTNERS_LABEL_NAME            = $label->name($this->langid());
            $this->_objTpl->TXT_PARTNERS_LABEL_NAME        = $dropdown->dropdown_name();
            $this->_objTpl->LABEL_ID                       = $label->id;
            $this->_objTpl->PARTNER_ID                     = $partner ? $partner->id : 0;
            $this->_objTpl->setVariable('TXT_PARTNERS_LABEL_SEARCHFIELD', $dropdown);
            $this->_objTpl->parse('PARTNER_'.$label->label_placeholder);
            if(Request::is_post()) {
                #$assigned = Request::POST($dropdown->dropdown_name() . '_list');
                #if ($assigned) {
                #    $to_assign[] = intval($assigned);
                #}
                foreach ($_SESSION['newpartner_labels'][$label->id] as $entry) {
                    $to_assign[] = $entry;
                }
            }
        }
        DBG::dump($to_assign);

        if (Request::is_post()) {
            $partner->active              = 0;
            $partner->name                = Request::POST('name');
            $partner->first_contact_name  = Request::POST('contact');
            $partner->first_contact_email = Request::POST('email');
            $partner->web_url             = Request::POST('web_url');
            $partner->address             = Request::POST('address');
            $partner->city                = Request::POST('city');
            $partner->zip_code            = Request::POST('zipcode');
            $partner->phone_nr            = Request::POST('phone_nr');
            $partner->fax_nr              = Request::POST('fax_nr');
            $partner->customer_quote      = '';
            $partner->creation_date       = date('Y-m-d');
            $partner->num_installations   = 0;
            $partner->description         = Request::POST('description');
            $this->_objTpl->setVariable(
                'PARTNER_CUSTOMER_DESCRIPTION',
                get_wysiwyg_editor('description', $partner->description, 'news')
            );
            if ($this->_intCurrentUserId) {
                $partner->user_id = $this->_intCurrentUserId;
            }
            if ($partner->validate(array($this, 'add_errormessage'))) {
                $partner->save();
                foreach ($to_assign as $entry_id) {
                    $entry = LabelEntry::get($entry_id);
                    $partner->assign_entry($entry);
                }
                $this->_objTpl->hideBlock('partner_signup_form');
                $this->_objTpl->touchBlock('partner_signup_success');
            } else {
                $this->_objTpl->PARTNER_NAME     = Request::POST('name');
                $this->_objTpl->PARTNER_CONTACT  = Request::POST('contact');
                $this->_objTpl->PARTNER_EMAIL    = Request::POST('email');
                $this->_objTpl->PARTNER_WEB_URL  = Request::POST('web_url');
                $this->_objTpl->PARTNER_ADDRESS  = Request::POST('address');
                $this->_objTpl->PARTNER_CITY     = Request::POST('city');
                $this->_objTpl->PARTNER_ZIPCODE  = Request::POST('zipcode');
                $this->_objTpl->PARTNER_PHONE_NR = Request::POST('phone_nr');
                $this->_objTpl->PARTNER_FAX_NR   = Request::POST('fax_nr');
                $this->_objTpl->hideBlock('partner_signup_success');
                $this->_objTpl->touchBlock('partner_signup_error');
                $this->_objTpl->ERROR_MESSAGE = join('<p/>', $this->save_errors);
            }
        } else {
            $this->_objTpl->setVariable(
                'PARTNER_CUSTOMER_DESCRIPTION',
                get_wysiwyg_editor('description', $partner->description, 'news')
            );
        }
    }


    function assignentry_action()
    {
        $this->force_ajax();
        // TODO: check if user is entitled to change this partner
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
        // TODO: check if user is entitled to change this partner
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


    function add_errormessage($msg)
    {
        $this->save_errors[] = $msg;
    }


    function default_action()
    {
        global $_CORELANG;

        $fulltext     = Request::GET('search');
        $label_search = array();
        $this->_objTpl->add_tr('TXT_SEARCH');
        // Show AssignableLabel in template and also add search values to
        // $label_search so we can filter partners.
        $dd_searchstring = '';
        foreach (AssignableLabel::all($this->langid())->rs() as $label) {
            if (!$label->active) continue;
            $dropdown = new LabelBrowserView(
                ASCMS_MODULE_PATH.'/partners/template',
                $label,
                $this->langid()
            );
            if ($this->settings->hide_empty_labels) {
                $dropdown->hide_empty();
            }
            $this->_objTpl->PARTNERS_LABEL_NAME            = $label->name($this->langid());
            $this->_objTpl->TXT_PARTNERS_LABEL_NAME        = $dropdown->dropdown_name();
            $this->_objTpl->LABEL_ID                       = $label->id;
            $this->_objTpl->PARTNER_ID                     = $partner ? $partner->id : 0;
            $this->_objTpl->setVariable('TXT_PARTNERS_LABEL_SEARCHFIELD', $dropdown);
            $this->_objTpl->parse('label_searchbox');
            $searched = Request::GET($dropdown->dropdown_name());
            if ($searched) {
                $dd_searchstring .= '&' . $dropdown->dropdown_name() . '=' . $searched;
                $label_search[] = intval($searched);
            }
        }
        $this->_objTpl->add_tr('TXT_SEARCH');
        $this->_objTpl->global_TXT_SEARCH_VALUE = $fulltext;
        // Search for label and fulltext data
        $data = Partner::search($fulltext, $label_search, 'frontend');
        if ($data->count() == 0) {
            if ($this->_objTpl->blockExists('no_results')) {
                $this->_objTpl->touchBlock("no_results");
            }
            return;
        }
        $url_template = "index.php?section=partners"
            ."&search=" . htmlspecialchars($fulltext)
            .$dd_searchstring
            .'&p=%p';
        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));
        $pager = new NGView_Pager($data, Request::GET('p', 0));
        if ($this->_objTpl->blockExists('no_results')) {
            $this->_objTpl->hideBlock("no_results");
        }
        foreach ($pager->current()->rs() as $partner) {
            $this->show_partner($partner);
            $this->_objTpl->parse('partner_entry');
        }
        $this->_objTpl->PARTNERS_ROW_COUNT = $data->count();
        $pager->put_placeholders($this->_objTpl, $url_template);
        $this->_objTpl->global_TXT_SEARCH = $_CORELANG['TXT_SEARCH'];
    }


    private function put_partner_vars($partner)
    {
        $this->_objTpl->PARTNER_ID            = $partner->id;
        $this->_objTpl->PARTNER_NAME          = $partner->name;
        $this->_objTpl->PARTNER_CONTACT_NAME  = $partner->first_contact_name;
        $this->_objTpl->PARTNER_CONTACT_EMAIL = $partner->first_contact_email;
        $this->_objTpl->PARTNER_WEB_URL       = $partner->web_url;
        $this->_objTpl->PARTNER_CITY          = $partner->city;
        $this->_objTpl->PARTNER_ADDRESS       = $partner->address;
        $this->_objTpl->PARTNER_ZIP_CODE      = $partner->zip_code;
        $this->_objTpl->PARTNER_PHONE_NR      = $partner->phone_nr;
        $this->_objTpl->PARTNER_FAX_NR        = $partner->fax_nr;
        $this->_objTpl->PARTNER_LOGO_URL      = ($partner->logo_url == "" ? "http://open.thumbshots.org/image.aspx?url=".$partner->web_url : ASCMS_IMAGE_PATH . $partner->logo_url);
        $this->_objTpl->PARTNER_QUOTE         = $partner->customer_quote;
        $this->_objTpl->setVariable('PARTNER_DESCRIPTION', $partner->description);
    }


    function show_partner($partner)
    {
//DBG::msg("showing partner... {$partner->name}");
        // parse labels separately (using the block name from the label AssignableLabel)
        foreach (AssignableLabel::all($this->langid())->rs() as $label) {
            $block = 'PARTNER_'.$label->label_placeholder;
            if ($this->_objTpl->blockExists($block)) {
                DBG::msg("parsing block '$block' START");
                $this->_objTpl->setCurrentBlock($block);
                $entries = $partner->assigned_entries($this->langid(), $label->id);
                foreach ($entries->rs() as $entry) {
                    $this->_objTpl->setCurrentBlock($block.'_ENTRY');
                    DBG::msg("parsing block '{$block}_ENTRY' START");
                    DBG::msg("parsing block '{$block}_ENTRY': e_id = " . $entry->id);
                    DBG::msg("parsing block '{$block}_ENTRY': text = " . $entry->hierarchic_name($this->langid()));
                    $this->_objTpl->ENTRY_ID   = $entry->id;
                    $this->_objTpl->LABEL_ID   = $entry->label_id;
                    $this->_objTpl->ENTRY_TEXT = $entry->hierarchic_name($this->langid());
                    $this->_objTpl->PARTNER_ID = $partner->id;
                    $this->_objTpl->parse($block.'_ENTRY');
                    $this->_objTpl->_variables = array();
                    DBG::msg("parsing block '{$block}_ENTRY' END");
                }
                $this->_objTpl->LABEL_ID   = $entry->label_id;
                $this->_objTpl->LABEL_NAME = $label->name($this->langid());
                $this->_objTpl->parse($block);
                $this->_objTpl->_variables = array();
                DBG::msg("parsing block '$block' END");
            }
        }
        $this->put_partner_vars($partner);
        // parse label list, if present
        if ($this->_objTpl->blockExists('label_list')) {
            foreach (AssignableLabel::all($this->langid())->rs() as $label) {
                $new_entries = $partner->assigned_entries($this->langid(), $label->id);
                foreach ($new_entries->rs() as $entry) {
                    $this->_objTpl->ENTRY_ID   = $entry->id;
                    $this->_objTpl->LABEL_ID   = $entry->label_id;
                    $this->_objTpl->ENTRY_TEXT = $entry->hierarchic_name($this->langid());
                    $this->_objTpl->PARTNER_ID = $partner->id;
                    $this->_objTpl->parse('label_entry');
                    $this->_objTpl->_variables = array();
                    $this->put_partner_vars($partner);
                }
                $this->_objTpl->LABEL_NAME = $label->name($this->langid());
                $this->_objTpl->parse('label_list');
                $this->_objTpl->_variables = array();
                $this->put_partner_vars($partner);
            }
        }
        // label dependent blocks....
        $blocks = array();
        // find all blocks that there are...
        foreach ($this->labels_with_blocks()->rs() as $b_label) {
            $blocks[$b_label->parse_custom_block] = "hideBlock";
        }
        // then figure out which of them to show.
        foreach ($partner->all_labels()->rs() as $p_label) {
            if ($p_label->parse_custom_block) {
                $blocks[$p_label->parse_custom_block] = "touchBlock";
            }
        }
        // now go about all the blocks and touch/hide them.
        foreach ($blocks as $b => $action) {
            $dbg_msg = "Looking for block '$b'...";
            if (!$this->_objTpl->blockExists($b)) {
                DBG::msg("$dbg_msg n/a");
                continue;
            }
            DBG::msg("$dbg_msg $action");
            $this->put_partner_vars($partner);
            $this->_objTpl->$action($b);
        }
        $this->put_partner_vars($partner);
    }


    function detail_action()
    {
        $partner = Partner::get(Request::GET('id'));
        $this->current_partner = $partner;
        $this->show_partner($partner);
    }


    function labels_with_blocks()
    {
        $q = LabelEntry::all($this->langid());
        $tbl = LabelEntry::typeinfo('table');
        return new NGDb_Query("
            SELECT * FROM ($q) AS _q
            WHERE _q.parse_custom_block IS NOT NULL
              AND _q.parse_custom_block <> ''
              ",
              'LabelEntry'
        );
    }

}

?>
