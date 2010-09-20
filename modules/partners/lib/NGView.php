<?php

/**
 * This is a wrapper class for HTML_Template_Sigma, extending
 * our basic template system with some useful methods.
 */

require_once ASCMS_MODULE_PATH.'/partners/lib/translation.php';

/**
 * This is a wrapper class for HTML_Template_Sigma, extending
 * our basic template system with some useful methods.
 *
 * Note that the following ways of adding variables will
 * always htmlspecialchars() the data. If you don't want this,
 * use the setVariable() and setGlobalVariable() methods:
 * - $tpl->VARIABLE = $data;
 * - $tpl->global_VARIABLE = $data;
 * - $tpl->add_tr()
 * - $tpl->add_tr_parsed()
 * - $tpl->add_tr_global()
 * - $tpl->add_tr_parsed_global()
 */
class NGView extends HTML_Template_Sigma
{
    /**
     * Creates a new NGView object. Note that it already calls CSRF::add_placeholder(),
     * so you don't have to do this again.
     */
    function __construct($path) {
        HTML_Template_Sigma::__construct($path);
        CSRF::add_placeholder($this);
    }


    /**
     * Setter for variables. You can use $tpl->foo = 'bar'
     * instead of $tpl->setVariable('foo', 'bar').
     *
     * There's some unholy magic going on if you prefix your variable with
     * the string "global_" (lowercase): In this case, this part is stripped,
     * and setGlobalVariable() is used instead.
     */
    function __set($key, $value) {
        if (substr($key, 0, 7) == 'global_') {
            $key = substr($key, 7);
            $this->setGlobalVariable($key, htmlspecialchars($value));
            return;
        }
        $this->setVariable($key, htmlspecialchars($value));
    }


    /**
     * When you need to parse a language variable before adding,
     * use this method instead of add_tr(). You can pass additional
     * arguments that will be parsed using sprintf().
     * @param string name - The name of the language variable.
     * @param var    args - one or more parameters to be parsed into the variable
     */
    function add_tr_parsed($name, $args) {
        $this->setVariable(
            $name,
            htmlspecialchars(call_user_func_array('tr_parse', func_get_args()))
        );
    }


    /**
     * setGlobalVariable() variant of add_tr_parsed().
     * See the documentation there for more info.
     */
    function add_tr_parsed_global($name, $args) {
        $this->setGlobalVariable(
            $name,
            htmlspecialchars(call_user_func_array('tr_parse', func_get_args()))
        );
    }


    /**
     * setGlobalVariable() variant of add_tr().
     * See the documentation there for more info.
     */
    function add_tr_global($name) {
        $this->setGlobalVariable($name, htmlspecialchars(tr($name)));
    }


    /**
     * Adds the language variable named $name to the template.
     *
     * @param string $name - The Name of the language variable to add.
     */
    function add_tr($name) {
        $this->setVariable($name, htmlspecialchars(tr($name)));
    }


    /**
     * This function assumes that there's a block
     * in the template called 'successmessage', which
     * contains a placeholder SUCCESS_MESSAGE.
     * It then activates this block and fills in the message.
     */
    function show_success($msg) {
        $this->SUCCESS_MESSAGE = $msg;
        $this->parse('successmessage');
    }


    /**
     * This function assumes that there's a block
     * in the template called 'errormessage', which
     * contains a placeholder ERROR_MESSAGE.
     * It then activates this block and fills in the message.
     */
    function show_error($msg) {
        $this->ERROR_MESSAGE = $msg;
        $this->parse('errormessage');
    }

}


/**
 * Helper class for creating cyclic row classes or other cyclic data.
 * Each time an NGView_Cycle object is converted to a string, it gives
 * the next value given to it's constructor, then looping again from
 * the beginning.
 */
class NGView_Cycle
{
    private $cycles = array();


    /**
     * Creates a new NGView_Cycle object. Pass as many strings as needed
     * for your cycle creation.
     */
    function __construct() {
        $this->cycles = func_get_args();
    }


    function __toString() {
        $curr = array_shift($this->cycles);
        array_push($this->cycles, $curr);
        return $curr;
    }

}


/**
 * This is a helper class for creating a pager. Note that the page
 * numbering starts with 1 for usability reasons. A page number 0 is
 * accepted, too, but will be converted to 1 internally.
 */
class NGView_Pager {
    private $query;
    private $page;
    private $items_per_page;
    private $pages;


    /**
     * Creates a new NGView_Pager object.
     *
     * @param NGDb_Query $q              the query object.
     * @param int        $page           the current page.
     * @param int        $items_per_page the amount of items to show per page. default 30
     */
    function __construct($q, $page, $items_per_page = 30) {
        $this->items_per_page = $items_per_page;
        $this->page           = max($page, 1);
        $this->query          = $q;
    }


    private function _parseurl($template, $text) {
        return str_replace('%p', $text, $template);
    }


    private function _pages() {
        if ($this->pages) {
            return $this->pages;
        }
        $this->pages = $this->query->num_pages();
        return $this->pages;
    }


    /**
     * Returns a NGDb_Query object for the current page.
     */
    function current() {
        return $this->query->page($this->page, $this->items_per_page);
    }


    /**
     * Returns the URL for the previous page.
     *
     * @param string $template - URL that has a placeholder %p
     * where the page number will be inserted.
     */
    function prev_url($template) {
        return $this->_parseurl($template, max(1, $this->page-1));
    }


    /**
     * Returns the URL for the next page.
     *
     * @param string $template - URL that has a placeholder %p
     * where the page number will be inserted.
     */
    function next_url($template) {
        return $this->_parseurl($template, min($this->_pages(), $this->page+1));
    }


    /**
     * Puts the following placeholders in the given Sigma or NGView template:
     * - PAGER_NEXT_URL
     * - PAGER_PREV_URL
     * - PAGER_NUM_PAGES
     * - PAGER_CURRENT_PAGE
     *
     * @param object $tpl     - Sigma or NGView template object
     * @param string $url_tpl - URL template.
     */
    function put_placeholders($tpl, $url_tpl) {
        $tpl->add_tr('TXT_PARTNERS_PAGE');
        $tpl->add_tr('TXT_PARTNERS_ROW_COUNT');
        $tpl->setVariable('PAGER_NEXT_URL',     $this->next_url($url_tpl));
        $tpl->setVariable('PAGER_PREV_URL',     $this->prev_url($url_tpl));
        $tpl->setVariable('PAGER_NUM_PAGES',    $this->_pages());
        $tpl->setVariable('PAGER_CURRENT_PAGE', $this->page);
    }

}

?>
