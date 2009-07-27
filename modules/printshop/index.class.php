<?php
/**
 * Printshop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */
error_reporting(E_ALL);ini_set('display_errors',1);
$objDatabase->debug=1;
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
class Printshop extends PrintshopLibrary {

    var $_objTpl;
    var $_intVotingDaysBeforeExpire = 1;
    var $_strStatusMessage = '';
    var $_strErrorMessage = '';


    /**
    * Constructor   -> Call parent-constructor, set language id and create local template-object
    *
    * @global   integer
    */
    function __construct($strPageContent)
    {
        global $_LANGID;

        parent::__construct();

        $this->_intLanguageId = intval($_LANGID);
        $this->_intCurrentUserId = 0;

        $this->_objTpl = new HTML_Template_Sigma('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($strPageContent);
    }


    /**
     * Must be called before the user-id is accessed. Tries to load the user-id from the session.
     *
     */
    function initUserId() {
        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->login() ? $objFWUser->objUser->getId() : 0;
    }


    /**
    * Reads $_GET['cmd'] and selects (depending on the value) an action
    *
    */
    function getPage()
    {
        if(!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }

        switch ($_GET['cmd']) {
            case 'order':
                $this->showOrder();
                break;
            default:
                $this->showPrints();
                break;
        }

        return $this->_objTpl->get();
    }



    /**
     * Shows the main page
     *
     * @global  array
     */
    function showPrints() {
        global $_ARRAYLANG;
//        $this->_objTpl->parse('showPrints');
    }


    /**
     * Shows the order form
     *
     * @global  array
     * @global  ADONewConnection
     * @global  array
     */
    function showOrder() {
        global $_ARRAYLANG, $objDatabase, $_CONFIG;

    }


    function getPageTitle(){
        return;
    }


    /**
     * Returns needed javascripts
     *
     * @param   string      $strType: Which Javascript should be returned?
     * @return  string      $strJavaScript
     */
    function getJavascript($strType = '') {
        $strJavaScript = '';

        switch ($strType) {
            case 'order':
                $strJavaScript = '  <script type="text/javascript" language="JavaScript">
                                    //<![CDATA[
                                    //]]>
                                    </script>';
                break;
            default:
                $strJavaScript = '  <script type="text/javascript" language="JavaScript">
                                    //<![CDATA[

                                    //]]>
                                    </script>';
                break;
        }

        return $strJavaScript;
    }
}
