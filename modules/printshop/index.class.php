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
    var $_lineColor = 'rgba(153, 204, 0, 0.5)';

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
        $lineColor = $this->_lineColor;
        $type = !empty($_REQUEST['type']) ? contrexx_addslashes($_REQUEST['type']) : '';
        JS::activate('excanvas');
        $JS =<<< EOJ
(function(){
    \$J(document).ready(function(){
        var drawDelay = 10; //delay in ms for each rectangle draw step
        var \$last = [];
        \$J.each(['Format', 'Front', 'Back', 'Weight', 'Paper'], function(i, j){
            setAttributeFilter(\$J('#psAttribute'+j+' li:first'), \$last);
            \$last = \$J('#psAttribute'+j+' li:first');
        });

        if(!window.console){
            window.log = '';
            window.console = {log: function(msg){window.log+=msg.toString();}}
        }

        \$J('#printshopCanvas').click(function(e){
            clickLiAtPos(e);
        });

        var clickLiAtPos = function(e){
            for(var i=0; i < arrLis.length; i++){
                if(e.clientX > arrLis[i].left && e.clientX < arrLis[i].left + arrLis[i].width){
                    if(e.clientY > arrLis[i].top && e.clientY < arrLis[i].top + arrLis[i].height){
                        \$J(arrLis[i].li).click();
                        return false;
                    }
                }
            }
        }

        //all clickable attribtues
        \$lis = \$J('.psAttributeContainer li');
        //get position and dimension of each li
        var arrLis = [];
        \$lis.each(function(){
            var pos = \$J(this).position();
            arrLis[arrLis.length] = {
                left: pos.left,
                top: pos.top,
                width: \$J(this).outerWidth(),
                height: \$J(this).outerHeight(),
                li: this
            };
        });

        \$lis.click(function(){
            var step        = 0;
            var \$li        = \$J(this);
            var \$liSel     = \$li.parent().find('.selected');
            if(\$liSel.attr('id') == \$li.attr('id')){
                return false;
            }

            var filters = getFilters();

            \$J.ajax({
                type: 'POST',
                url: 'index.php?standalone=1&cmd=printshop&type=$type',
                data: filters,
                dataType: 'json',
                success: function(data){updatePrice(data.price)},
                error: function(data){updatePrice('N/A');}
            });

            var \$ul        = \$li.parent();
            var \$canvas    = \$J('#printshopCanvas');
            var posLi       = \$li.position();
            var heightLi    = \$li.outerHeight();
            var widthLi     = \$li.outerWidth();
            var posUl       = \$ul.position();
            var widthUl     = \$ul.outerWidth();
            var heightUl    = \$ul.outerHeight();
            var posLiSel    = \$liSel.position();
            var posCanvas   = \$canvas.position();

            if(posLi.top > posLiSel.top){
                var gap       = \$lis.index(\$li) - \$lis.index(\$liSel);
                var targetPos = posLi.top - posLiSel.top;
                var toTop     = false;
            }else{
                var gap       = \$lis.index(\$liSel) - \$lis.index(\$li);
                var targetPos = posLiSel.top - posLi.top;
                var toTop     = true;
            }

            //relative distance of li on canvas
            var left        = posLi.left - posCanvas.left;
            var top         = posLi.top - posCanvas.top;
            \$li.parent().find('li').removeClass('selected');
            \$liSel.addClass('oldSelected');
            (function(){ //scroll down smoothly
                ctx.clearRect(left , posUl.top - posCanvas.top, widthUl, heightUl+100);
                updateLines(step, \$liSel, toTop);
                ctx.fillStyle = "$lineColor";
                if(toTop){
                    ctx.fillRect (left, top + heightLi*gap - step, widthLi, heightLi);
                }else{
                    ctx.fillRect (left, top - heightLi*gap + step, widthLi, heightLi);
                }
                step += 2;
                if(step < targetPos){
                    x = setTimeout(arguments.callee, drawDelay);
                    return;
                }
                \$li.addClass('selected');
                \$liSel.removeClass('oldSelected');
                setAttributeFilter(\$li, \$liSel);
            })();
        });
        var x = setTimeout(initCanvas, 100); //give poor IE some time
    });

    var getFilters = function(){
        var filter = {};
        \$J('.selected').each(function(){
            var filterMatch = \$J(this).attr('id').match(/(.*?)_(\d+)$/);
            var attribute   = filterMatch[1];
            var attributeId = filterMatch[2];
            filter[attribute] = attributeId;
        });
        return \$J.param(filter);
    }

    var setAttributeFilter = function(\$li, \$last){
        \$li.addClass('selected');
        \$li.parent().find('li').each(function(){
            \$J('this').removeAttr('rel');
        });
        if(\$last.length > 0){
            \$prevLi = \$li.parent().parent().prev().find('li.selected'); //get selected li in prev div
            \$nextLi = \$li.parent().parent().next().find('li.selected'); //get selected li in next div
            if(\$nextLi.length > 0){
                \$li.attr('rel', \$nextLi.attr('id'));
            }
            if(\$prevLi.length > 0){
                \$prevLi.parent().find('li').each(function(){
                    \$J('this').removeAttr('rel');
                });
                \$prevLi.attr('rel', \$li.attr('id'));
            }
        }
        if(window.ctx){
            updateLines();
        }
    }

    var initCanvas = function(){
        window.ctx = \$J("#printshopCanvas").get(0).getContext("2d");
        updateLines();
    }

    var updatePrice = function(price){
        \$J('#pricePerOne').text(price);
    }

    var updateLines = function(_offsetTop, \$affectedLi, toTop){
        var offsetTop     = _offsetTop || 0;
        var \$elements    = \$J(".selected,.oldSelected");
        var \$canvas      = \$J("#printshopCanvas");
        var canvasEl      = \$canvas.get(0);
        canvasEl.width    = \$canvas.width();
        canvasEl.height   = \$canvas.height();
        var cOffset       = \$canvas.offset();
        ctx.lineWidth     = 2;
        ctx.strokeStyle   = "$lineColor";
        ctx.beginPath();

        \$elements.each(function(){
            var \$li=\$J(this);
            if(\$li.attr("rel")){
                var srcOffset      = \$li.offset();
                var srcMidHeight   = \$li.outerHeight()/2;
                var \$targetLi     = \$J("#"+\$li.attr("rel"));
                var liWidth        = \$targetLi.outerWidth();
                if(\$targetLi.length > 0){
                    var trgOffset     = \$targetLi.offset();
                    var trgMidHeight  = \$li.outerHeight()/2;
                    ctx.moveTo(srcOffset.left - cOffset.left + liWidth - 4, srcOffset.top - cOffset.top + srcMidHeight);
                    if(\$affectedLi){
                        var \$prevLi =  \$affectedLi.parent().parent().prev().find('li.selected');
                        if(\$prevLi.length > 0 && \$li.index(\$prevLi) > -1){
                            ctx.moveTo(srcOffset.left - cOffset.left + liWidth - 4, srcOffset.top - cOffset.top + srcMidHeight);
                            if(toTop){
                                return ctx.lineTo(trgOffset.left - cOffset.left, trgOffset.top - cOffset.top + trgMidHeight - offsetTop);
                            }else{
                                return ctx.lineTo(trgOffset.left - cOffset.left, trgOffset.top - cOffset.top + trgMidHeight + offsetTop);
                            }
                        }else if(\$affectedLi.length > 0 && \$affectedLi.index(\$li) > -1){
                            if(toTop){
                                ctx.moveTo(srcOffset.left - cOffset.left + liWidth - 4, srcOffset.top - cOffset.top + srcMidHeight - offsetTop);
                            }else{
                                ctx.moveTo(srcOffset.left - cOffset.left + liWidth - 4, srcOffset.top - cOffset.top + srcMidHeight + offsetTop);
                            }
                        }
                    }
                    return ctx.lineTo(trgOffset.left - cOffset.left, trgOffset.top - cOffset.top + trgMidHeight);
                }
            }
        });
        ctx.stroke();
        ctx.closePath();
    }
})();
EOJ;
        JS::registerCode($JS);

        if(!empty($_REQUEST['standalone'])){ //JSON request
            foreach ($this->_arrAvailableAttributes as $attribute) {
                $arrFilter[$attribute] = !empty($_POST[$attribute]) ? intval($_POST[$attribute]) : 0;
            }
            $arrEntry = current($this->_getEntries($arrFilter, false));
            die(json_encode(array(
                'price' => $arrEntry['price'],
            )));
        }
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
