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
class Printshop extends PrintshopLibrary {

    var $_objTpl;
    var $_intVotingDaysBeforeExpire = 1;
    var $_strStatusMessage = '';
    var $_strErrorMessage = '';
    var $_type = '';

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

        $this->_type = !empty($_GET['type']) ? intval($_GET['type']) : 1;
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
        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_FORMAT_TITLE'     => $_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'],
            'TXT_PRINTSHOP_FRONT_TITLE'      => $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'],
            'TXT_PRINTSHOP_BACK_TITLE'       => $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'],
            'TXT_PRINTSHOP_WEIGHT_TITLE'     => $_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'],
            'TXT_PRINTSHOP_PAPER_TITLE'      => $_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'],
            'TXT_PRINTSHOP_SUMMARY'          => $_ARRAYLANG['TXT_PRINTSHOP_SUMMARY'],
            'TXT_PRINTSHOP_PRICE_PER_PIECE'  => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_PER_PIECE'],
            'TXT_PRINTSHOP_AMOUNT'           => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT'],
            'TXT_PRINTSHOP_TYPE'             => $_ARRAYLANG['TXT_PRINTSHOP_TYPE'],
            'TXT_PRINTSHOP_DATA_PREPARATION' => $_ARRAYLANG['TXT_PRINTSHOP_DATA_PREPARATION'],
            'TXT_PRINTSHOP_EXCL_TAX'         => $_ARRAYLANG['TXT_PRINTSHOP_EXCL_TAX'],
            'TXT_PRINTSHOPT_COMMIT_ORDER'    => $_ARRAYLANG['TXT_PRINTSHOPT_COMMIT_ORDER'],
        ));

        foreach($this->_arrAvailableAttributes as $attribute){
            if($attribute == 'type'){ continue; }
            $arrAttributes = $this->_getAttributesOfType($attribute, $this->_type);
            foreach($arrAttributes as $id => $attr) {
            	$this->_objTpl->setVariable(array(
                    'ATTRIBUTE_FILTER_ID'           => $attr['id'],
                    'ATTRIBUTE_FILTER_NAME'         => $attr['name'],
                    'ATTRIBUTE_FILTER_ATTRIBUTE'    => $attribute,
            	));
                $this->_objTpl->parse('filter'.ucwords($attribute));
            }
        }

        $JS =<<< EOJ
(function(){
    \$J(document).ready(function(){
        var \$last = [];
        \$J.each(['Format', 'Front', 'Back', 'Weight', 'Paper'], function(i, j){
            setAttributeFilter(\$J('#psAttribute'+j+' li:first'), i, \$last);
            \$last = \$J('#psAttribute'+j+' li:first');
        });
//            \$J('#psAttributeFormat li:first').css({backgroundColor:'#99CC00', padding:"3px"});
//            \$J('#psAttributeFront li:first').css({backgroundColor:'#99CC00', padding:"3px"});
//            \$J('#psAttributeBack li:first').css({backgroundColor:'#99CC00', padding:"3px"});
//            \$J('#psAttributeWeight li:first').css({backgroundColor:'#99CC00', padding:"3px"});
//            \$J('#psAttributePaper li:first').css({backgroundColor:'#99CC00', padding:"3px"});

        updateCanvas(\$J("#printshopCanvas"), \$J(".selected"));
    });

    var setAttributeFilter = function(\$li, index, \$last){
        \$li.addClass('selected');
        console.log(\$last);
        if(\$last.length > 0){
            \$li.attr('rel', \$last.attr('id'));
        }
    }

    var updateCanvas = function(\$canvas, \$elements){
		var canvasEl      = \$canvas.get(0);
		canvasEl.width    = \$canvas.width();
		canvasEl.height   = \$canvas.height();
		var cOffset       = \$canvas.offset();
		ctx               = canvasEl.getContext("2d");
		//var ctx         = canvasEl.getContext("2d");
		ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);
		ctx.beginPath();
		\$elements.each(function(){
			var \$li=\$J(this);
			if(\$li.attr("rel"))
			{
				var srcOffset      = \$li.offset();
				var srcMidHeight   = \$li.height()/2;
				var \$targetLi     = \$J("#"+\$li.attr("rel"));
				if(\$targetLi.length)
				{
					var trgOffset     = \$targetLi.offset();
					var trgMidHeight  = \$li.height()/2;
					ctx.moveTo(srcOffset.left - cOffset.left, srcOffset.top - cOffset.top + srcMidHeight);
					ctx.lineTo(trgOffset.left - cOffset.left + \$targetLi.width(), trgOffset.top - cOffset.top + trgMidHeight);
				}
			}
		});
		ctx.stroke();
		ctx.closePath();
	}

})();
EOJ;
        JS::registerCode($JS);

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
        return '';
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
