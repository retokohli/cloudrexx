<?php
/**
 * Printshop
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation AG <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_printshop
 */

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
    var $_okMsg = array();
    var $_errMsg = array();

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
    * @return string parsed HTML template
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
        $this->_parseMsgs();
        return $this->_objTpl->get();
    }



    /**
     * Shows the main page
     *
     * @global  array
     */
    function showPrints() {
        global $_ARRAYLANG;

        if(!empty($_REQUEST['standalone'])){ //JSON request
            $arrFilter = array();
            foreach ($this->_arrAvailableAttributes as $attribute) {
                $arrFilter[$attribute] = !empty($_POST[$attribute]) ? intval($_POST[$attribute]) : 0;
            }
            $arrEntry = $this->_getEntries($arrFilter, true);
            die(json_encode(current($arrEntry['entries'])));
        }

        $dataPreparationPrice = $this->_arrSettings['currency'].' '.number_format($this->_arrSettings['dataPreparationPrice'], 2);

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_FORMAT_TITLE'                => $_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'],
            'TXT_PRINTSHOP_FRONT_TITLE'                 => $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'],
            'TXT_PRINTSHOP_BACK_TITLE'                  => $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'],
            'TXT_PRINTSHOP_WEIGHT_TITLE'                => $_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'],
            'TXT_PRINTSHOP_PAPER_TITLE'                 => $_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'],
            'TXT_PRINTSHOP_SUMMARY'                     => $_ARRAYLANG['TXT_PRINTSHOP_SUMMARY'],
            'TXT_PRINTSHOP_PRICE_PER_PIECE'             => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_PER_PIECE'],
            'TXT_PRINTSHOP_CURRENCY'                    => $this->_arrSettings['currency'],
            'TXT_PRINTSHOP_AMOUNT'                      => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT'],
            'TXT_PRINTSHOP_TYPE'                        => $_ARRAYLANG['TXT_PRINTSHOP_TYPE'],
            'TXT_PRINTSHOP_DATA_PREPARATION'            => $_ARRAYLANG['TXT_PRINTSHOP_DATA_PREPARATION'],
            'TXT_PRINTSHOP_EXCL_TAX'                    => $_ARRAYLANG['TXT_PRINTSHOP_EXCL_TAX'],
            'TXT_PRINTSHOP_COMMIT_ORDER'                => $_ARRAYLANG['TXT_PRINTSHOP_COMMIT_ORDER'],
            'TXT_PRINTSHOP_PRICES_THRESHOLD_AMOUNT'     => $_ARRAYLANG['TXT_PRINTSHOP_PRICES_THRESHOLD_AMOUNT'],
            'TXT_PRINTSHOP_PRINT_COST'                  => $_ARRAYLANG['TXT_PRINTSHOP_PRINT_COST'],
            'TXT_PRINTSHOP_CURRENCY'                    => $this->_arrSettings['currency'],
            'PRINTSHOP_DATA_PREPARATION_PRICE'          => $dataPreparationPrice,
            'PRINTSHOP_TAX'                             => $this->_vatPercent,
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

        $arrPriceThresholds = array();
        foreach ($this->_priceThresholds as $index => $threshold) {
            $arrPriceThresholds['PRINTSHOP_PRICE_THRESHOLD_'.$index] = $threshold['threshold'];
        }
      	$this->_objTpl->setVariable($arrPriceThresholds);

        $lineColor = $this->_lineColor;
        $type = !empty($_REQUEST['type']) ? contrexx_addslashes($_REQUEST['type']) : '';
        $DI   = CONTREXX_DIRECTORY_INDEX;
        JS::activate('jquery');
        JS::activate('excanvas');
        $JS =<<< EOJ
(function(){
    \$J(document).ready(function(){
        var drawDelay = 12; //delay in ms for each rectangle draw step
        var stepSize = 3;
        var \$last = [];
        \$J.each(['Format', 'Front', 'Back', 'Weight', 'Paper'], function(i, j){
            setAttributeFilter(\$J('#psAttribute'+j+' li:first'), \$last);
            \$last = \$J('#psAttribute'+j+' li:first');
        });

        if(!window.console){
            window.log = '';
            window.console = {log: function(msg){window.log+=msg.toString();}}
        }

        //set default value for amount
        if(isNaN(parseInt(\$J('#amount').val()))){
            \$J('#amount').val(1);
        }

        //form submit handler, redirect to order page
        \$J('#psSubmit').click(function(){
            var format = \$J('#psAttributeFormat li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var back   = \$J('#psAttributeBack li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var front  = \$J('#psAttributeFront li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var weight = \$J('#psAttributeWeight li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var paper  = \$J('#psAttributePaper li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var amount = \$J('#amount').val().replace(/^.*?(\d+)$/g, '$1');
            location.href='$DI?section=printshop&cmd=order&psAmount='+amount+'&psType=$type&psFormat='+format+'&psFront='+front+'&psBack='+back+'&psWeight='+weight+'&psPaper='+paper;
        });

        //highlight active navigation (use css_name psType_$type in content manager for this to work)
        \$J('.psType_$type').css({color:'black'});

        //handle price table hover styles and set amount upon click
        \$J('.priceThreshold,.pricePerOne').hover(function(){
            if(\$J(this).is('.pricePerOne')){
                 \$J(this).addClass('highlight');
                 \$J(this).prev().addClass('highlight');
            }else{
                 \$J(this).addClass('highlight');
                 \$J(this).next().addClass('highlight');
            }
        },
        function(){
            if(\$J(this).is('.pricePerOne')){
                 \$J(this).removeClass('highlight');
                 \$J(this).prev().removeClass('highlight');
            }else{
                 \$J(this).removeClass('highlight');
                 \$J(this).next().removeClass('highlight');
            }
        })
        .click(function(){
            if(\$J(this).is('.pricePerOne')){
                var amount = \$J(this).prev().text();
            }else{
                var amount = \$J(this).text();
            }
            \$J('#amount').val(parseInt(amount));
            updatePrice();
        });

        \$J('#amount').bind('keyup change', function(){
            \$J(this).val(\$J(this).val().replace(/[^\d]/g, ''));
            if(\$J(this).val() == ''){
                \$J(this).val('1');
                \$J(this).select();
            }
            updatePrice();
        });



        \$J('#printshopCanvas').click(function(e){
            clickLiAtPos(e);
        });

        \$J('#printshopCanvas').mousemove(function(e){
            mouseOverLi(e, this);
        });

        var mouseOverLi = function(e, that){
            var x = e.clientX + \$J(window).scrollLeft(); //add window scroll pos in case we're not at 0,0
            var y = e.clientY + \$J(window).scrollTop();
            for(var i=0; i < arrLis.length; i++){
                if(x > arrLis[i].left && x < arrLis[i].left + arrLis[i].width){
                    if(y > arrLis[i].top && y < arrLis[i].top + arrLis[i].height){
                        \$J(that).css({cursor: 'pointer'});
                        return false;
                    }
                }
            }
            \$J(that).css({cursor: 'default'});
        }

        var clickLiAtPos = function(e){ //check event location add relay the click event to the according li
            var x = e.clientX + \$J(window).scrollLeft(); //add window scroll pos in case we're not at 0,0
            var y = e.clientY + \$J(window).scrollTop();
            for(var i=0; i < arrLis.length; i++){
                if(x > arrLis[i].left && x < arrLis[i].left + arrLis[i].width){
                    if(y > arrLis[i].top && y < arrLis[i].top + arrLis[i].height){
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
            \$li.addClass('targetLi');
            updateFilter();
            \$li.removeClass('targetLi');

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
                step += stepSize;
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
        updateFilter();
    }); //end document ready

    var priceThresholds = [];
    var price = [];
    var roundUpIndex = 0;

    var updatePrice = function(){
        var amount = parseInt(\$J('#amount').val());
        var printCost = 0;
        var i = -1;
        do{ i++; }
             while( amount > priceThresholds[i] && i < 15 );

        if(roundUpIndex > 0 && i > roundUpIndex && amount != priceThresholds[i] && amount < priceThresholds[priceThresholds.length-1]){
            amount = priceThresholds[i];
            \$J('#amount').val(amount);
        }


        if(priceThresholds[i] > amount){
            printCost = amount * price[i-1];
        } else {
            printCost = amount * price[i];
        }

        var roundedPrice = roundPrice(printCost.toFixed(2));
        \$J('#psPriceForPrints').text(printCost.toFixed(2));
    }

    var roundPrice = function(flt){
        return parseFloat(Math.floor(20*flt)/20);
    }

    var updateFilter = function(){
        var filters = getFilters();
        \$J.ajax({
            type: 'POST',
            url: 'index.php?standalone=1&section=printshop',
            data: 'type=$type&'+filters,
            dataType: 'json',
            success: function(data){updateInfoDisplay(data)},
            error: function(data){updateInfoDisplay();}
        });
    }

    var getFilters = function(\$li){
        var filter = {};
        var \$selected = \$J('.selected,.targetLi');
        \$selected.each(function(){
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

    var updateInfoDisplay = function(data){
        var NA = 'N/A';
        var lastPrice;

        if(data){
            \$J('#psSummaryType').text(data.type);
            \$J('#psSummaryFormat').text(data.format);
            \$J('#psSummaryFront').text(data.front);
            \$J('#psSummaryBack').text(data.back);
            \$J('#psSummaryWeight').text(data.weight);
            \$J('#psSummaryPaper').text(data.paper);
            \$J.each([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15], function(i, j){
                if(data['price_'+j] > 0){
                    \$J('#price_'+j).text(data['price_'+j]);
                    lastPrice = data['price_'+j];
                }else{
                    data['price_'+j] = lastPrice;
                }
                \$J('#price_'+j).text(data['price_'+j]);
            });
            roundUpIndex = data.roundUpIndex || 0;
            \$J('#psSubmit').removeAttr('disabled');
        }else{
            \$J('#pricePerOne').text(NA);
            \$J('#psSummaryType').text(NA);
            \$J('#psSummaryFormat').text(NA);
            \$J('#psSummaryFront').text(NA);
            \$J('#psSummaryBack').text(NA);
            \$J('#psSummaryWeight').text(NA);
            \$J('#psSummaryPaper').text(NA);
            \$J.each([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15], function(i, j){
                \$J('#price_'+j).text(NA);
            });
            \$J('#psSubmit').attr('disabled', 'disabled');
        }

        \$J('.priceThreshold').each(function(i){
            priceThresholds[priceThresholds.length] = parseInt(\$J(this).text().replace(/(\d+)/, '$1'));
            price[i] = parseFloat(\$J('#price_'+i).text());
        });
        updatePrice();
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

        $amount = !empty($_REQUEST['psAmount']) && intval($_REQUEST['psAmount']) > 0 ? intval($_REQUEST['psAmount']) : '1';

        $arrFilter  = array();
        foreach ($this->_arrAvailableAttributes as $attribute) {
            $arrFilter[$attribute] = !empty($_REQUEST['ps'.ucfirst($attribute)]) ? intval($_REQUEST['ps'.ucfirst($attribute)]) : 0;
        }

        if(!empty($_REQUEST['standalone'])){ //JSON request
            $arrEntry = $this->_getEntries($arrFilter, true);
            $arrThresholds = array();
            foreach ($this->_priceThresholds as $index => $threshold) {
                $arrThresholds[$index] = $threshold['threshold'];
            }
            die(json_encode(array(
                'entry'      => current($arrEntry['entries']),
                'thresholds' => $arrThresholds,
            )));
        }

        $shipmentInputHTML = array();
        foreach ($this->_shipmentEnum as $id => $shipmentType) {
            if(!empty($_POST['psShipment']) && $_POST['psShipment'] == $shipmentType){
                $checked = 'checked="checked"';
            }elseif(empty($_POST['psShipment']) && $id == count($this->_shipmentEnum) - 1){
                $checked = 'checked="checked"';
            }else{
                $checked = '';
            }
        	$shipmentInputHTML[] = '<input class="radio" '.$checked.'
        	                         type="radio" id="psShipment'.$id.'" name="psShipment" value="'.$shipmentType.'" />
        	                        <label for="psShipment'.$id.'">'
        	                        .$_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_'.strtoupper($shipmentType)]
        	                        .'</label><br />';
        }

        $acceptTermsCheckbox = $this->_getTermsCheckbox();

        $dataUploadHelpDescription = $_ARRAYLANG['TXT_PRINTSHOP_UPLOAD_DATA_DESC'];
        if($this->_arrSettings['mandatoryImageUploadEnabled'] < 1){
            $emailLink  = '<a   id="psUploadEmailLink" href="'.CONTREXX_DIRECTORY_INDEX.'?section=imprint"
                                title="'.$_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_MAIL'].'">'
                            .$_ARRAYLANG['TXT_PRINTSHOP_EMAIL'].'</a>';
            $mailLink   = '<a   id="psMailLink" href="'.CONTREXX_DIRECTORY_INDEX.'?section=imprint"
                                title="'.$_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_MAIL'].'">'
                            .$_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_MAIL'].'</a>';
            $dataUploadHelpDescription .= sprintf('<br />'.$_ARRAYLANG['TXT_PRINTSHOP_UPLOAD_DATA_DESC2'], $emailLink, $mailLink);
        }

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_FORMAT_TITLE'        => $_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'],
            'TXT_PRINTSHOP_FRONT_TITLE'         => $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'],
            'TXT_PRINTSHOP_BACK_TITLE'          => $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'],
            'TXT_PRINTSHOP_WEIGHT_TITLE'        => $_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'],
            'TXT_PRINTSHOP_PAPER_TITLE'         => $_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'],
            'TXT_PRINTSHOP_SUMMARY'             => $_ARRAYLANG['TXT_PRINTSHOP_SUMMARY'],
            'TXT_PRINTSHOP_PRICE_PER_PIECE'     => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_PER_PIECE'],
            'TXT_PRINTSHOP_AMOUNT'              => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT'],
            'TXT_PRINTSHOP_TYPE_TITLE'          => $_ARRAYLANG['TXT_PRINTSHOP_TYPE_TITLE'],
            'TXT_PRINTSHOP_DATA_PREPARATION'    => $_ARRAYLANG['TXT_PRINTSHOP_DATA_PREPARATION'],
            'TXT_PRINTSHOP_EXCL_TAX'            => $_ARRAYLANG['TXT_PRINTSHOP_EXCL_TAX'],
            'TXT_PRINTSHOP_COMMIT_ORDER'        => $_ARRAYLANG['TXT_PRINTSHOP_COMMIT_ORDER'],
            'TXT_PRINTSHOP_UPLOAD_DATA_TITLE'   => $_ARRAYLANG['TXT_PRINTSHOP_UPLOAD_DATA_TITLE'],
            'TXT_PRINTSHOP_FILE'                => $_ARRAYLANG['TXT_PRINTSHOP_FILE'],
            'TXT_PRINTSHOP_CHOOSE_FILE'         => $_ARRAYLANG['TXT_PRINTSHOP_CHOOSE_FILE'],
            'TXT_PRINTSHOP_UPLOAD_DATA_DESC'    => $dataUploadHelpDescription,
            'TXT_PRINTSHOP_SUBJECT'             => $_ARRAYLANG['TXT_PRINTSHOP_SUBJECT'],
            'TXT_PRINTSHOP_ORDERDETAILS'        => $_ARRAYLANG['TXT_PRINTSHOP_ORDERDETAILS'],
            'TXT_PRINTSHOP_AMOUNT_COST_SHIP'    => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT_COST_SHIP'],
            'TXT_PRINTSHOP_PRINT_COST'          => $_ARRAYLANG['TXT_PRINTSHOP_PRINT_COST'],
            'TXT_PRINTSHOP_DATA_PREPARATION'    => $_ARRAYLANG['TXT_PRINTSHOP_DATA_PREPARATION'],
            'TXT_PRINTSHOP_TOTAL'               => $_ARRAYLANG['TXT_PRINTSHOP_TOTAL'],
            'TXT_PRINTSHOP_SHIPMENT'            => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT'],
            'TXT_PRINTSHOP_GROSS'               => $_ARRAYLANG['TXT_PRINTSHOP_GROSS'],
            'TXT_PRINTSHOP_VAT'                 => $_ARRAYLANG['TXT_PRINTSHOP_VAT'],
            'TXT_PRINTSHOP_PRICE_SUBTOTAL'      => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_SUBTOTAL'],
            'TXT_PRINTSHOP_INVOC_SHIPM_ADDRESS' => $_ARRAYLANG['TXT_PRINTSHOP_INVOC_SHIPM_ADDRESS'],
            'TXT_PRINTSHOP_INVOICE_ADDRESS'     => $_ARRAYLANG['TXT_PRINTSHOP_INVOICE_ADDRESS'],
            'TXT_PRINTSHOP_SHIPMENT_ADDRESS'    => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_ADDRESS'],
            'TXT_PRINTSHOP_CONTACT_PERSON'      => $_ARRAYLANG['TXT_PRINTSHOP_CONTACT_PERSON'],
            'TXT_PRINTSHOP_COMPANY'             => $_ARRAYLANG['TXT_PRINTSHOP_COMPANY'],
            'TXT_PRINTSHOP_ADDRESS'             => $_ARRAYLANG['TXT_PRINTSHOP_ADDRESS'],
            'TXT_PRINTSHOP_ZIP'                 => $_ARRAYLANG['TXT_PRINTSHOP_ZIP'],
            'TXT_PRINTSHOP_CITY'                => $_ARRAYLANG['TXT_PRINTSHOP_CITY'],
            'TXT_PRINTSHOP_CONTACT'             => $_ARRAYLANG['TXT_PRINTSHOP_CONTACT'],
            'TXT_PRINTSHOP_COMMENT'             => $_ARRAYLANG['TXT_PRINTSHOP_COMMENT'],
            'TXT_PRINTSHOP_EMAIL'               => $_ARRAYLANG['TXT_PRINTSHOP_EMAIL'],
            'TXT_PRINTSHOP_TELEPHONE'           => $_ARRAYLANG['TXT_PRINTSHOP_TELEPHONE'],
            'TXT_PRINTSHOP_ACCEPT_TERMS'		=> $_ARRAYLANG['TXT_PRINTSHOP_ACCEPT_TERMS'],
            'TXT_PRINTSHOP_SUBMIT_ORDER'		=> $_ARRAYLANG['TXT_PRINTSHOP_SUBMIT_ORDER'],
            'TXT_PRINTSHOP_SAME_AS_INVOICE_ADDR'=> $_ARRAYLANG['TXT_PRINTSHOP_SAME_AS_INVOICE_ADDR'],
            'TXT_PRINTSHOP_SHIPMENT_TYPE'       => $_ARRAYLANG['TXT_PRINTSHOP_SHIPMENT_TYPE'],
            'PRINTSHOP_CURRENCY'                => $this->_arrSettings['currency'],
            'PRINTSHOP_DATA_PREPARATION_PRICE'  => number_format($this->_arrSettings['dataPreparationPrice'], 2),
            'PRINTSHOP_AMOUNT'                  => $amount,
            'PRINTSHOP_SHIPMENT_RADIOBUTTONS'   => implode("\n", $shipmentInputHTML),
            'PRINTSHOP_TERMS_CHKBOX'            => $acceptTermsCheckbox,
        ));

        $arrEntry = $this->_getEntries($arrFilter, false);
        $entry = current($arrEntry['entries']);
        $arrAttributeTexts = array();
        foreach ($this->_arrAvailableAttributes as $index => $attribute) {
            $attr = strtoupper($attribute);
        	$arrAttributeTexts['PRINTSHOP_'.$attr]       = $this->_getAttributeName($attribute, $arrFilter[$attribute]);
        	$arrAttributeTexts['PRINTSHOP_'.$attr.'_ID'] = $entry[$attribute];
        }
        $this->_objTpl->setVariable($arrAttributeTexts);

        $strBlank = $this->_blank;
        $strErrorMissingFields      = $_ARRAYLANG['TXT_PRINTSHOP_MISSING_FIELDS'];
        $strErrorInvalidPhone       = $_ARRAYLANG['TXT_PRINTSHOP_INVALID_PHONE'];
        $strErrorInvalidEmail       = $_ARRAYLANG['TXT_PRINTSHOP_INVALID_EMAIL'];
        $strErrorInvalidExtension   = $_ARRAYLANG['TXT_PRINTSHOP_INVALID_EXTENSION'];

        $dataPreparationPrice   = $this->_arrSettings['dataPreparationPrice'];
        $shipmentPriceMail      = $this->_arrSettings['shipmentPriceMail'];
        $shipmentPriceMessenger = $this->_arrSettings['shipmentPriceMessenger'];

        $imageNames         = implode("','", $this->_arrImageFields);
        foreach ($this->_acceptedExtension as $extension) {
            $extensionRegexes[] = "new RegExp('\.$extension$')";
        }
        $extensionRegexes   = implode(",", $extensionRegexes);

        $type   = $arrFilter['type'];
        $format = $arrFilter['format'];
        $front  = $arrFilter['front'];
        $back   = $arrFilter['back'];
        $weight = $arrFilter['weight'];
        $paper  = $arrFilter['paper'];

        $vendorEmail = implode("','", str_split($this->_arrSettings['orderEmail']));

        $mandatoryImageUploadEnabled = $this->_arrSettings['mandatoryImageUploadEnabled'] > 0 ? 'true' : 'false';

        $vatFactor = $this->_vatPercent/100;

        $DI   = CONTREXX_DIRECTORY_INDEX;
        JS::activate('jquery');
        JS::activate('excanvas');
        $JS =<<< EOJ
(function(){
    var mandatoryImageUploadEnabled = $mandatoryImageUploadEnabled;

    var mandatoryFields = [
            'amount',
            'psSubject',
            'psImage1',
            'psContactI',
            'psContactS',
            'psAddress1I',
            'psAddress1S',
            'psAddress2I',
            'psAddress2S',
            'psZipI',
            'psZipS',
            'psCityI',
            'psCityS',
            'psEmail',
            'psPhone'
    ];

    var dataPreparationPrice     = parseFloat(($dataPreparationPrice).toFixed(2));
    var shipmentPriceMail        = parseFloat(($shipmentPriceMail).toFixed(2));
    var shipmentPriceMessenger   = parseFloat(($shipmentPriceMessenger).toFixed(2));
    var shipmentPrice            = 0.00;
    var vendorEmail              = ['$vendorEmail'];

    \$J(document).ready(function(){
        //get price informations
        initPrices();

        //unobfuscate email
        \$J('#psUploadEmailLink').click(function(){
            \$J(this).attr('href', 'mailto:' + vendorEmail.join(''));
        });

        //copy invoice address to shipment address
        \$J('#psSameAddress').click(function(){
            \$J('#psCompanyS'   ).val(\$J('#psCompanyI').val());
            \$J('#psContactS'   ).val(\$J('#psContactI').val());
            \$J('#psAddress1S'  ).val(\$J('#psAddress1I').val());
            \$J('#psAddress2S'  ).val(\$J('#psAddress2I').val());
            \$J('#psZipS'       ).val(\$J('#psZipI').val());
            \$J('#psCityS'      ).val(\$J('#psCityI').val());
            return false;
        });

        //highlight active navigation (use css_name psType_$type in content manager for this to work)
        \$J('.psType_$type').css({color:'black'});

        //price recalculation
        \$J('.shipmentSelection input').change(function(){
            updateShipmentPrice.apply(this);
        });

        //terms link popup
        \$J('#psTermsLink').click(function(){
            var popUp = window.open(\$J(this).attr('href'));
            return false;
        })

        //form pre submit check
        \$J('#psSubmitOrder').click(function(){
            if(checkFilledFields()){
                \$J('#psFrmOrder').submit();
            }else{
                return false;
            }
        })

        //autoselect on focus
        \$J('#amount').focus(function(){
            \$J(this).select();
        });

        //only allow numbers for the amount
        \$J('#amount').keyup(function(){
            \$J(this).val(\$J(this).val().replace(/[^\d]/g, ''));
            if(\$J(this).val() == ''){
                \$J(this).val('1');
                \$J(this).select();
            }
            updatePrice();
        });

        //update price if a shipment type has already been selected on initial page load
        if(\$J('[name=psShipment]:checked').length !== 0){
            updateShipmentPrice.apply(\$J('[name=psShipment]:checked').get(0));
        }

        \$J(mandatoryFields).each(function(i, j){
            if(j == 'psImage1'){
                return;
            }
            \$J('#'+j).keyup(function(){
                \$J(this).val(\$J(this).val().replace(/^\s+(.*?)\s+$/, '$1'));
                var value = \$J(this).val();
                if(\$J(this).is('.missing')){
                    if(value != ''){
                        \$J(this).removeClass('missing');
                    }
                }
            });
        });
    });

    var type            = $type;
    var format          = $format;
    var front           = $front;
    var back            = $back;
    var weight          = $weight;
    var paper           = $paper;
    var price           = [];
    var priceThresholds = [];
    var roundUpIndex    = 0;
    var vatFactor       = $vatFactor;

    var updateShipmentPrice = function(){
        var index = parseInt(\$J(this).attr('id').replace(/.*(\d+)$/, '$1'));
        switch(index){
            case 0:
                shipmentPrice = 0;
            break;
            case 1:
                shipmentPrice = shipmentPriceMessenger;
            break;
            case 2:
                shipmentPrice = shipmentPriceMail;
            break;
        }
        updatePrice();
    }

    var initPrices = function(){
        var lastPrice;
        \$J.ajax({
            url: '$DI?section=printshop&cmd=order&standalone=1',
            type: 'post',
            data: {psType: type, psFormat: format, psFront: front, psBack: back, psWeight: weight, psPaper: paper},
            dataType: 'json',
            success: function(data){
                if(data.entry.price_0){
                    \$J.each([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15], function(i, j){
                        if(data.entry['price_'+j] > 0){
                            lastPrice = data.entry['price_'+j];
                            price[j]  = data.entry['price_'+j];
                        }else{
                            price[j]  = lastPrice;
                        }
                        priceThresholds[j]  = data.thresholds[j];
                    });
                    roundUpIndex = data.entry.roundUpIndex || 0;
                }
                updatePrice();
            }
        });
    }

    var roundPrice = function(flt){
        return parseFloat(Math.floor(20*flt)/20);
    }

    var updatePrice = function(){
        var amount = parseInt(\$J('#amount').val());
        var printCost = 0;
        var i = -1;

        do{ i++ }
             while( amount > priceThresholds[i] && i < 15 );

        if(roundUpIndex > 0 && i > roundUpIndex && amount != priceThresholds[i] && amount < priceThresholds[priceThresholds.length-1]){
            amount = priceThresholds[i];
            \$J('#amount').val(amount);
        }

        if(priceThresholds[i] > amount){
            printCost = amount * price[i-1];
        } else {
            printCost = amount * price[i];
        }

        var roundedPrice = roundPrice(printCost.toFixed(2));
        var subtotal = 1*printCost + 1*dataPreparationPrice;
        var vat = 1*subtotal * vatFactor;
        var grossPrice = 1*subtotal + 1*vat;
        var totalPrice = 1*grossPrice + 1*shipmentPrice;


        \$J('#psPrintCost').text(printCost.toFixed(2));
        \$J('#psPriceSubtotal').text(subtotal.toFixed(2));
        \$J('#psPriceVAT').text(vat.toFixed(2));
        \$J('#psPriceGross').text(grossPrice.toFixed(2));
        \$J('#psPriceShipment').text(shipmentPrice.toFixed(2));
        \$J('#psPriceTotal').text(roundPrice(totalPrice).toFixed(2));
        \$J('#psPrice').val(roundPrice(totalPrice).toFixed(2));
    }

    var checkFilledFields = function(){
        var missing = false;
        var error = false;

        \$J(mandatoryFields).each(function(i, j){
            if(!mandatoryImageUploadEnabled && j == 'psImage1'){
                return;
            }
            if(j != 'psImage1'){
                \$J('#'+j).val(\$J('#'+j).val().replace(/^\s+(.*?)\s+$/, '$1'));
            }
            if(\$J('#'+j).val() == ''){
                \$J('#'+j).addClass('missing');
                missing = true;
            }else{
                \$J('#'+j).removeClass('missing');
            }
        });


        if( mandatoryImageUploadEnabled
            && !\$J('#psAttributeBack').text().match(/$strBlank/)
            && \$J('#psImage2').val().replace(/^\s+(.*?)\s+$/, '$1') == '')
        {
            \$J('#psImage2').addClass('missing');
            missing = true;
        }else{
            \$J('#psImage2').removeClass('missing');
        }

        images            = ['$imageNames'];
        extensionRegexes  = [$extensionRegexes];

        var valid = true;
        for(var i=0; i < images.length; i++){
            if(\$J('#'+images[i]).val() == ''){
                continue;
            }
            valid = false;
            for(var j=0;j < extensionRegexes.length; j++){
                if(\$J('#'+images[i]).val().match(extensionRegexes[j])){
                    valid = true;
                }
            }
            if(valid){
                \$J('#'+images[i]).removeClass('missing');
            }else{
                \$J('#'+images[i]).addClass('missing');
            }
        }
        if(!valid){
            setErrorMsg('$strErrorInvalidExtension');
        }


        if(\$J('[name=psShipment]:checked').length == 0){
            \$J('.shipmentSelection').addClass('missing');
            missing = true;
        }else{
            \$J('.shipmentSelection').removeClass('missing');
        }

        if(\$J('#psAcceptTerms:checked').length == 0){
            \$J('#psAcceptTerms').parent().find('label').addClass('missing');
            missing = true;
        }else{
            \$J('#psAcceptTerms').parent().find('label').removeClass('missing');
        }

        if(\$J('#psPhone').val().replace(/[^\d]/g, '').length < 10){
            setErrorMsg('$strErrorInvalidPhone');
            \$J('#psPhone').addClass('missing');
            error = true;
        }else{
            \$J('#psPhone').removeClass('missing');
        }

        //crude email check
        if(!\$J('#psEmail').val().match(/[\w._-]+@[\w._-]+\.[\w_-]{2,}/)){
            setErrorMsg('$strErrorInvalidEmail');
            \$J('#psEmail').addClass('missing');
            error = true;
        }else{
            \$J('#psEmail').removeClass('missing');
        }

        if(missing){
            setErrorMsg('$strErrorMissingFields');
        }

        return valid && !missing && !error;
    }

    var setErrorMsg = function(str){
        \$J('<div>'+str+'</div>').appendTo('#errorMessage')
        .fadeOut(14000, function(){\$J(this).remove()});
        \$J(window).scrollTop(\$J('#header_wrapper').height());
    }

    var setOkMsg = function(str){
        \$J('<div>'+str+'</div>').appendTo('#okMessage')
        .fadeOut(14000, function(){\$J(this).remove()});
        \$J(window).scrollTop(\$J('#header_wrapper').height());
    }

})();
EOJ;
        JS::registerCode($JS);


        if(!empty($_POST['psSubmitOrder'])){
            if($this->_checkOrder()){
                $this->_objTpl->hideBlock('orderForm');
                $this->_objTpl->touchBlock('orderFeedback');
            }else{
                $this->_objTpl->hideBlock('orderFeedback');
            }
        }
    }


    /**
     * return the html for the accept terms checkbox
     *
     * @return string
     */
    function _getTermsCheckbox($acceptTerms = ''){
        global $_ARRAYLANG;

        return '<input '.(!empty($acceptTerms) ? 'checked="checked"' : 'sd="ff"').'
                 type="checkbox" id="psAcceptTerms" name="psAcceptTerms" />
                <label for="psAcceptTerms">'
                .sprintf($_ARRAYLANG['TXT_PRINTSHOP_ACCEPT_TERMS'], '<a id="psTermsLink" href="'.CONTREXX_DIRECTORY_INDEX.'?section=agb">', '</a>')
                .'</label>';
    }


    /**
     * check if the order is complete and call handler if positive
     *
     */
    function _checkOrder(){
        global $_ARRAYLANG;

        $subject = !empty($_POST['psSubject']) ? contrexx_addslashes($_POST['psSubject']) : '';
        $type = !empty($_POST['psType']) ? intval($_POST['psType']) : '';
        $format = !empty($_POST['psFormat']) ? intval($_POST['psFormat']) : '';
        $front = !empty($_POST['psFront']) ? intval($_POST['psFront']) : '';
        $back = !empty($_POST['psBack']) ? intval($_POST['psBack']) : '';
        $weight = !empty($_POST['psWeight']) ? intval($_POST['psWeight']) : '';
        $paper = !empty($_POST['psPaper']) ? intval($_POST['psPaper']) : '';
        $amount = !empty($_POST['psAmount']) ? intval($_POST['psAmount']) : '';
        $price = !empty($_POST['psPrice']) ? doubleval($_POST['psPrice']) : '';
        $shipment = !empty($_POST['psShipment']) ? contrexx_addslashes($_POST['psShipment']) : '';
        $invCompany = !empty($_POST['psCompanyI']) ? contrexx_addslashes($_POST['psCompanyI']) : '';
        $invContact = !empty($_POST['psContactI']) ? contrexx_addslashes($_POST['psContactI']) : '';
        $invAddress1 = !empty($_POST['psAddress1I']) ? contrexx_addslashes($_POST['psAddress1I']) : '';
        $invAddress2 = !empty($_POST['psAddress2I']) ? contrexx_addslashes($_POST['psAddress2I']) : '';
        $invZip = !empty($_POST['psZipI']) ? contrexx_addslashes($_POST['psZipI']) : '';
        $invCity = !empty($_POST['psCityI']) ? contrexx_addslashes($_POST['psCityI']) : '';
        $shipCompany = !empty($_POST['psCompanyS']) ? contrexx_addslashes($_POST['psCompanyS']) : '';
        $shipContact = !empty($_POST['psContactS']) ? contrexx_addslashes($_POST['psContactS']) : '';
        $shipAddress1 = !empty($_POST['psAddress1S']) ? contrexx_addslashes($_POST['psAddress1S']) : '';
        $shipAddress2 = !empty($_POST['psAddress2S']) ? contrexx_addslashes($_POST['psAddress2S']) : '';
        $shipZip = !empty($_POST['psZipS']) ? contrexx_addslashes($_POST['psZipS']) : '';
        $shipCity = !empty($_POST['psCityS']) ? contrexx_addslashes($_POST['psCityS']) : '';
        $email = !empty($_POST['psEmail']) ? contrexx_addslashes($_POST['psEmail']) : '';
        $phone = !empty($_POST['psPhone']) ? contrexx_addslashes($_POST['psPhone']) : '';
        $comment = !empty($_POST['psComment']) ? contrexx_addslashes($_POST['psComment']) : '';
        $acceptTerms = !empty($_POST['psAcceptTerms']) ? contrexx_addslashes($_POST['psAcceptTerms']) : '';

        $acceptTermsCheckbox = $this->_getTermsCheckbox($acceptTerms);

        $this->_objTpl->setVariable(array(
            'PRINTSHOP_SUBJECT'         => $subject,
            'PRINTSHOP_AMOUNT'          => $amount,
            'PRINTSHOP_COMPANYI'        => $invCompany,
            'PRINTSHOP_COMPANYS'        => $shipCompany,
            'PRINTSHOP_CONTACTI'        => $invContact,
            'PRINTSHOP_CONTACTS'        => $shipContact,
            'PRINTSHOP_ADDRESS1I'       => $invAddress1,
            'PRINTSHOP_ADDRESS1S'       => $shipAddress1,
            'PRINTSHOP_ADDRESS2I'       => $invAddress2,
            'PRINTSHOP_ADDRESS2S'       => $shipAddress2,
            'PRINTSHOP_ZIPI'            => $invZip,
            'PRINTSHOP_ZIPS'            => $shipZip,
            'PRINTSHOP_CITYI'           => $invCity,
            'PRINTSHOP_CITYS'           => $shipCity,
            'PRINTSHOP_EMAIL'           => $email,
            'PRINTSHOP_PHONE'           => $phone,
            'PRINTSHOP_COMMENT'         => $comment,
            'PRINTSHOP_AMOUNT'          => $amount,
            'PRINTSHOP_TERMS_CHKBOX'    => $acceptTermsCheckbox,
        ));

        if(!FWValidator::isEmail($email)){
            $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_INVALID_EMAIL']);
            return false;
        }

        if(!in_array($shipment, $this->_shipmentEnum)){
            $shipment = '';
        }

        //someone accessed the order page without selecting all attributes, redirect to selection in this case
        if(empty($type) || empty($format) || empty($front) || empty($back) || empty($weight) || empty($paper)){
            header('Location: '.CONTREXX_DIRECTORY_INDEX.'?section=printshop');
            die();
        }

        $arrImages = array();
        foreach ($this->_arrImageFields as $index => $image) {
            if(empty($_FILES[$image]['name'])){
                $arrImages[$index] = '';
                continue;
            }

            $arrFileParts   = pathinfo($_FILES[$image]['name']);
            $filepath       = $this->_imageUploadPath.'/'.$_FILES[$image]['name'];
            while(file_exists(ASCMS_DOCUMENT_ROOT.'/'.$filepath)){
                $filepath = $this->_imageUploadPath.'/'.$arrFileParts['filename'].'_'.time().'.'.$arrFileParts['extension'];
            }

            if(!empty($_FILES[$image]['type']) && strpos($_FILES[$image]['type'], 'image/')){
                $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_INVALID_EXTENSION']);
                return false;
            }

            if( ($arrImages[$index] = File::uploadFileHttp($image, $filepath, 0, $this->_acceptedExtension)) === false){
                $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_ERROR_UPLOADING_FILE']);
                return false;
            }else{
                $arrImages[$index] = $filepath;
            }
            if(!in_array($_FILES[$image]['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))){
                $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_ERROR_UPLOADING_FILE']);
                return false;
            }

        }

        if($this->_arrSettings['mandatoryImageUploadEnabled'] > 0){
            if($this->_getAttributeName('back', $back) != $this->_blank){
                if(empty( $arrImages[2] )){
                    $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_MISSING_IMAGE_UPLOAD_BACK']);
                    return false;
                }
            }

            if(empty( $arrImages[1] )){
                $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_MISSING_IMAGE_UPLOAD_FRONT']);
                return false;
            }
        }

        if(empty($subject)      || empty($amount)  || empty($shipment) || empty($invContact)  || empty($invAddress1)
        || empty($invAddress2)  || empty($invZip)  || empty($invCity)  || empty($shipContact) || empty($shipAddress1)
        || empty($shipAddress2) || empty($shipZip) || empty($shipCity) || empty($email)       || empty($phone)
        || empty($acceptTerms)){
           $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_MISSING_FIELDS']);
           return false;
        }

        if( ($orderID = $this->_addOrder(
            $type, $format, $front, $back, $weight, $paper, $price, $amount, $arrImages[1], $arrImages[2], $arrImages[3],
            $subject, $email, $phone, $comment, $shipment,
            $invCompany, $invContact, $invAddress1, $invAddress2, $invZip, $invCity,
            $shipCompany, $shipContact, $shipAddress1, $shipAddress2, $shipZip, $shipCity
        )) ){
            $this->_sendMails($orderID);
            return true;
        }else{
            $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_ORDER_SAVE_ERROR']);
            return false;
        }
    }


    /**
     * Return the string for the page title
     *
     * @return string
     */
    function getPageTitle(){
        if(!empty($_GET['type'])){
            $type = intval($_GET['type']);
        }
        if(!empty($_GET['psType'])){
            $type = intval($_GET['psType']);
        }
        return !empty($type) ? $this->_getAttributeName('type', $type) : '';
    }


    function _sendMails($orderId){
        global $_CONFIG;

        require_once(ASCMS_LIBRARY_PATH.DIRECTORY_SEPARATOR.'phpmailer'.DIRECTORY_SEPARATOR."class.phpmailer.php");
        $mailer = new PHPMailer();
        $mailer->CharSet = CONTREXX_CHARSET;
        if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
            if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                $mailer->IsSMTP();
                $mailer->Host = $arrSmtp['hostname'];
                $mailer->Port = $arrSmtp['port'];
                $mailer->SMTPAuth = true;
                $mailer->Username = $arrSmtp['username'];
                $mailer->Password = $arrSmtp['password'];
            }
        }

        $arrOrder           = $this->_getOrder($orderId);
        $type               = $this->_getAttributeName('type', $arrOrder['type']);
        $format             = $this->_getAttributeName('format', $arrOrder['format']);
        $front              = $this->_getAttributeName('front', $arrOrder['front']);
        $back               = $this->_getAttributeName('back', $arrOrder['back']);
        $paper              = $this->_getAttributeName('paper', $arrOrder['paper']);
        $weight             = $this->_getAttributeName('weight', $arrOrder['weight']);
        $subject            = $arrOrder['subject'] ;
        $invoiceCompany     = $arrOrder['invoiceCompany'];
        $invoiceContact     = $arrOrder['invoiceContact'];
        $invoiceAddress1    = $arrOrder['invoiceAddress1'];
        $invoiceAddress2    = $arrOrder['invoiceAddress2'];
        $invoiceZip         = $arrOrder['invoiceZip'];
        $invoiceCity        = $arrOrder['invoiceCity'];
        $shipCompany        = $arrOrder['shipmentCompany'];
        $shipContact        = $arrOrder['shipmentContact'];
        $shipAddress1       = $arrOrder['shipmentAddress1'];
        $shipAddress2       = $arrOrder['shipmentAddress2'];
        $shipZip            = $arrOrder['shipmentZip'];
        $shipCity           = $arrOrder['shipmentCity'];
        $email              = $arrOrder['email'];
        $telephone          = $arrOrder['telephone'];
        $comment            = $arrOrder['comment'];
        $amount             = $arrOrder['amount'];
        $price              = $arrOrder['price'];

        $imageLinks         = (!empty($arrOrder['file1']) ? 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/'.$arrOrder['file1']."\n" : '').
                              (!empty($arrOrder['file2']) ? 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/'.$arrOrder['file2']."\n" : '').
                              (!empty($arrOrder['file3']) ? 'http://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/'.$arrOrder['file3']."\n" : '');

        $customerBody = str_replace(
            array(
                '%SUBJECT%', '%TYPE%', '%FORMAT%', '%FRONT%', '%BACK%', '%PAPER%', '%WEIGHT%',
                '%INVOICE_COMPANY%', '%INVOICE_CONTACT%', '%INVOICE_ADDRESS1%', '%INVOICE_ADDRESS2%', '%INVOICE_ZIP%', '%INVOICE_CITY%',
                '%SHIPMENT_COMPANY%', '%SHIPMENT_CONTACT%', '%SHIPMENT_ADDRESS1%', '%SHIPMENT_ADDRESS2%', '%SHIPMENT_ZIP%', '%SHIPMENT_CITY%',
                '%EMAIL%', '%TELEPHONE%', '%COMMENTS%', '%AMOUNT%', '%PRICE%', '%ORDER_ID%'
            ),
            array(
                $subject, $type, $format, $front, $back, $paper, $weight,
                $invoiceCompany, $invoiceContact, $invoiceAddress1, $invoiceAddress2, $invoiceZip, $invoiceCity,
                $shipCompany, $shipContact, $shipAddress1, $shipAddress2, $shipZip, $shipCity,
                $email, $telephone, $comment, $amount, $price, $orderId
            ),
            $this->_arrSettings['emailTemplateCustomer']
        );

        //mail for customer
        $mailer->AddAddress($email);
        $mailer->From     = !empty($this->_arrSettings['senderEmail'])     ? $this->_arrSettings['senderEmail']     : $_CONFIG['contactFormEmail'];
        $mailer->FromName = !empty($this->_arrSettings['senderEmailName']) ? $this->_arrSettings['senderEmailName'] : $_CONFIG['coreAdminName'];

        $mailer->Subject = $this->_arrSettings['emailSubjectCustomer'];
        $mailer->IsHTML(false);
        $mailer->Body = $customerBody;
        $mailer->Send();

        //mail for vendor
        $mailer->ClearAddresses();
        $mailer->From     = $email;
        $mailer->FromName = $invoiceContact;
        $mailer->AddAddress($this->_arrSettings['orderEmail']);
        $mailer->Subject = $this->_arrSettings['emailSubjectVendor'];
        $mailer->IsHTML(false);
        $vendorBody = str_replace(
            array(
                '%SUBJECT%', '%TYPE%', '%FORMAT%', '%FRONT%', '%BACK%', '%PAPER%', '%WEIGHT%',
                '%INVOICE_COMPANY%', '%INVOICE_CONTACT%', '%INVOICE_ADDRESS1%', '%INVOICE_ADDRESS2%', '%INVOICE_ZIP%', '%INVOICE_CITY%',
                '%SHIPMENT_COMPANY%', '%SHIPMENT_CONTACT%', '%SHIPMENT_ADDRESS1%', '%SHIPMENT_ADDRESS2%', '%SHIPMENT_ZIP%', '%SHIPMENT_CITY%',
                '%EMAIL%', '%TELEPHONE%', '%COMMENTS%', '%AMOUNT%', '%PRICE%', '%IMAGE_LINKS%', '%ORDER_ID%'
            ),
            array(
                $subject, $type, $format, $front, $back, $paper, $weight,
                $invoiceCompany, $invoiceContact, $invoiceAddress1, $invoiceAddress2, $invoiceZip, $invoiceCity,
                $shipCompany, $shipContact, $shipAddress1, $shipAddress2, $shipZip, $shipCity,
                $email, $telephone, $comment, $amount, $price, $imageLinks, $orderId
            ),
            $this->_arrSettings['emailTemplateVendor']
        );

        $mailer->Body = $vendorBody;
        $mailer->Send();
    }


     /**
     * parse the available messages
     *
     */
    function _parseMsgs(){
        $msgs = '';
        foreach ($this->_errMsg as $msg) {
            $msgs .= '<div>'.$msg.'</div>';
        	$this->_objTpl->setVariable('PRINTSHOP_ERROR_MESSAGES', $msgs);
        }
        $msgs = '';
        foreach ($this->_okMsg as $msg) {
            $msgs .= '<div>'.$msg.'</div>';
        	$this->_objTpl->setVariable('PRINTSHOP_OK_MESSAGES', $msgs);
        }
    }


    /**
     * add the specified error message
     *
     * @param string $strMsg
     */
    function _setError($strMsg){
        $this->_errMsg[] = $strMsg;
    }


    /**
     * add the specified ok message
     *
     * @param string $strMsg
     */
    function _setOk($strMsg){
        $this->_okMsg[] = $strMsg;
    }
}
