<?PHP
/**
 * Partners Module - Frontend code
 * @copyright   COMVATION AG
 * @author      David Vogt
 * @subpackage  module_partners
 */

// {{{ Requires
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


// }}}


class PartnersFrontend extends PartnersBase  {

    // {{{ Variables

    var $_objTpl;

    // gets set in detail action, so the partner's name
    // can be retreived after getPage() using getTitle().
    private $current_partner = null;

    //  }}}

    // {{{ Startup & Initialisation

    /**
    * Constructor   -> Create the module-menu and an internal template-object
    * @global   object      $objInit
    * @global   object      $objTemplate
    * @global   array       $_ARRAYLANG
    */
    function __construct($content)
    {
        global $objInit, $objTemplate, $_ARRAYLANG;

        $this->settings = new Settings;

        JS::registerJS('modules/partners/js/dropdown.js');
        JS::activate('prototype');
        $this->_objTpl = new NGView('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($content);

        $this->_intLanguageId = $objInit->userFrontendLangId;

        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->getId();

    }

    /**
    * Perform the right operation depending on the $_GET-params
    *
    * @global   object      $objTemplate
    */
    function getPage() {

        if(!isset($_GET['act'])) {
            $_GET['act']='default';
        }
        $act = $_GET['cmd'] ? $_GET['cmd'] : 'default';

        // get the NGMessaging data and put it where it belongs
        $this->get_messages();

        $this->add_default_vars();

        $func = $act . '_action';
        // TODO: error handling, access control
        $this->$func();

        return $this->_objTpl->get();
    }

    function getTitle($orig_title) {
        if ($this->current_partner) {
            return $this->current_partner->name;
        }
        return $orig_title;
    }

    // }}}

    // {{{ Overview

    function default_action() {
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
            if ($searched) {
                $label_search[] = intval($searched);
            }
        }

        $this->_objTpl->TXT_SEARCH_VALUE = $fulltext;

        // Search for label and fulltext data
        $data = Partner::search($fulltext, $label_search, 'frontend');
        if ($data->count() == 0) {
            if ($this->_objTpl->blockExists('no_results')) {
                $this->_objTpl->touchBlock("no_results");
            }
            return;
        }

        $url_template = "index.php?cmd=partners&search=" . htmlspecialchars($fulltext) . '&p=%p';

        $this->_objTpl->PARTNERS_ROW_COUNT = $data->count();

        $this->_objTpl->setGlobalVariable('ROW_CLASS', new NGView_Cycle('row2', 'row1'));

        $pager = new NGView_Pager($data, Request::GET('p', 0));
        $pager->put_placeholders($this->_objTpl, $url_template);

        if ($this->_objTpl->blockExists('no_results')) {
            $this->_objTpl->hideBlock("no_results");
        }
        foreach ($pager->current()->rs() as $partner) {
            $this->show_partner($partner);
            $this->_objTpl->parse('partner_entry');
        }

    }

    private function put_partner_vars($partner) {
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

    function show_partner($partner) {
        DBG::msg("showing partner... {$partner->name}");
        $this->put_partner_vars($partner);

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
                /*unset($this->_objTpl->_variables["ENTRY_ID"]);
                unset($this->_objTpl->_variables["LABEL_ID"]);
                unset($this->_objTpl->_variables["ENTRY_TEXT"]);
                unset($this->_objTpl->_variables["PARTNER_ID"]);*/

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
            // WHY THE FUCK, SIGMA, WHY THE FUCK?
            $this->put_partner_vars($partner);
            $this->_objTpl->$action($b);
        }
    }

    function detail_action() {
        $partner = Partner::get(Request::GET('id'));
        $this->current_partner = $partner;

        $this->show_partner($partner);

    }


    function labels_with_blocks() {
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

    // }}}

}

# vim:foldmethod=marker:et:sw=4:ts=4

