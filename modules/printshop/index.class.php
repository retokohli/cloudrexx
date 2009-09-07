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

        $this->_objTpl->setGlobalVariable(array(
            'TXT_PRINTSHOP_FORMAT_TITLE'                => $_ARRAYLANG['TXT_PRINTSHOP_FORMAT_TITLE'],
            'TXT_PRINTSHOP_FRONT_TITLE'                 => $_ARRAYLANG['TXT_PRINTSHOP_FRONT_TITLE'],
            'TXT_PRINTSHOP_BACK_TITLE'                  => $_ARRAYLANG['TXT_PRINTSHOP_BACK_TITLE'],
            'TXT_PRINTSHOP_WEIGHT_TITLE'                => $_ARRAYLANG['TXT_PRINTSHOP_WEIGHT_TITLE'],
            'TXT_PRINTSHOP_PAPER_TITLE'                 => $_ARRAYLANG['TXT_PRINTSHOP_PAPER_TITLE'],
            'TXT_PRINTSHOP_SUMMARY'                     => $_ARRAYLANG['TXT_PRINTSHOP_SUMMARY'],
            'TXT_PRINTSHOP_PRICE_PER_PIECE'             => $_ARRAYLANG['TXT_PRINTSHOP_PRICE_PER_PIECE'],
            'TXT_PRINTSHOP_AMOUNT'                      => $_ARRAYLANG['TXT_PRINTSHOP_AMOUNT'],
            'TXT_PRINTSHOP_TYPE'                        => $_ARRAYLANG['TXT_PRINTSHOP_TYPE'],
            'TXT_PRINTSHOP_DATA_PREPARATION'            => $_ARRAYLANG['TXT_PRINTSHOP_DATA_PREPARATION'],
            'TXT_PRINTSHOP_EXCL_TAX'                    => $_ARRAYLANG['TXT_PRINTSHOP_EXCL_TAX'],
            'TXT_PRINTSHOP_COMMIT_ORDER'                => $_ARRAYLANG['TXT_PRINTSHOP_COMMIT_ORDER'],
            'TXT_PRINTSHOP_PRICES_THRESHOLD_AMOUNT'     => $_ARRAYLANG['TXT_PRINTSHOP_PRICES_THRESHOLD_AMOUNT'],
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

        \$J('#psSubmit').click(function(){
            var format = \$J('#psAttributeFormat li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var back   = \$J('#psAttributeBack li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var front  = \$J('#psAttributeFront li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var weight = \$J('#psAttributeWeight li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var paper  = \$J('#psAttributePaper li.selected').attr('id').replace(/.*_(\d+)$/g, '$1');
            var amount = \$J('#amount').val().replace(/^.*?(\d+)$/g, '$1');
            location.href='$DI?section=printshop&cmd=order&psAmount='+amount+'&psType=$type&psFormat='+format+'&psFront='+front+'&psBack='+back+'&psWeight='+weight+'&psPaper='+paper;
        });

        \$J('.psType_$type').css({color:'black'});

        \$J('#printshopCanvas').click(function(e){
            clickLiAtPos(e);
        });

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
    });

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
        if(data){
            \$J('#psSummaryType').text(data.type);
            \$J('#psSummaryFormat').text(data.format);
            \$J('#psSummaryFront').text(data.front);
            \$J('#psSummaryBack').text(data.back);
            \$J('#psSummaryWeight').text(data.weight);
            \$J('#psSummaryPaper').text(data.paper);
            \$J([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]).each(function(i, j){
                \$J('#price_'+j).text(data['price_'+j]);
            });
            \$J('#psSubmit').removeAttr('disabled');
        }else{
            \$J('#pricePerOne').text(NA);
            \$J('#psSummaryType').text(NA);
            \$J('#psSummaryFormat').text(NA);
            \$J('#psSummaryFront').text(NA);
            \$J('#psSummaryBack').text(NA);
            \$J('#psSummaryWeight').text(NA);
            \$J('#psSummaryPaper').text(NA);
            \$J([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]).each(function(i, j){
                \$J('#price_'+j).text(NA);
            });
            \$J('#psSubmit').attr('disabled', 'disabled');
        }
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

        print_r($_REQUEST);
        print_r($_FILES);


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

        $acceptTermsCheckbox = '<input '.(!empty($acceptTerms) ? 'checked="checked"' : '').'
                                 type="checkbox" id="psAcceptTerms" name="psAcceptTerms" />
                                <label for="psAcceptTerms">'.$_ARRAYLANG['TXT_PRINTSHOP_ACCEPT_TERMS'].'</label>';

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
            'TXT_PRINTSHOP_UPLOAD_DATA_DESC'    => $_ARRAYLANG['TXT_PRINTSHOP_UPLOAD_DATA_DESC'],
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

        $dataPreparationPrice   = $this->_arrSettings['dataPreparationPrice'];
        $shipmentPriceMail      = $this->_arrSettings['shipmentPriceMail'];
        $shipmentPriceMessenger = $this->_arrSettings['shipmentPriceMessenger'];

        $type   = $arrFilter['type'];
        $format = $arrFilter['format'];
        $front  = $arrFilter['front'];
        $back   = $arrFilter['back'];
        $weight = $arrFilter['weight'];
        $paper  = $arrFilter['paper'];

        $DI   = CONTREXX_DIRECTORY_INDEX;
        JS::activate('jquery');
        JS::activate('excanvas');
        $JS =<<< EOJ
(function(){
    var dataPreparationPrice     = parseFloat(($dataPreparationPrice).toFixed(2));
    var shipmentPriceMail        = parseFloat(($shipmentPriceMail).toFixed(2));
    var shipmentPriceMessenger   = parseFloat(($shipmentPriceMessenger).toFixed(2));
    var shipmentPrice            = 0.00;

    \$J(document).ready(function(){
        initPrices();

        \$J('.shipmentSelection input').change(function(){
            updateShipmentPrice.apply(this);
        });

        \$J('#printShop form').submit(function(){
            return checkFilledFields();
        })

        \$J('#amount').keyup(function(){
            \$J(this).val(\$J(this).val().replace(/[^\d]/g, ''));
            if(\$J(this).val() == ''){
                return false;
            }
            updatePrice();
        });

        if(\$J('[name=psShipment]:checked').length !== 0){
            updateShipmentPrice.apply(\$J('[name=psShipment]:checked').get(0));
        }
    });

    var type            = $type;
    var format          = $format;
    var front           = $front;
    var back            = $back;
    var weight          = $weight;
    var paper           = $paper;
    var price           = [];
    var priceThresholds = [];


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
        console.log([index, shipmentPrice, shipmentPriceMessenger, shipmentPriceMail]);
        updatePrice();
    }

    var initPrices = function(){
        var lastPrice;
        \$J.ajax({
            url: '$DI?section=printshop&cmd=order&standalone=1',
            type: 'post',
            data: {type: type, format: format, front: front, back: back, weight: weight, paper: paper},
            dataType: 'json',
            success: function(data){
                if(data.entry.price_0){
                    \$J([0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]).each(function(i, j){
                        if(data.entry['price_'+j] > 0){
                            lastPrice = data.entry['price_'+j]
                            price[j]  = data.entry['price_'+j];
                        }else{
                            price[j]  = lastPrice;
                        }
                        priceThresholds[j]  = data.thresholds[j];
                    });
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
        var calculation = [];
        if(amount > priceThresholds[1] - 1){ //check if print amount is not only in first threshold area, which is 1 to priceThresholds[1]-1
            //process the first range
            printCost += (priceThresholds[1] - 1) * price[0];
            amount    -= priceThresholds[1] - 1;
            calculation.push('a 1-'+(priceThresholds[1] - 1)+' * '+price[0]+' ='+printCost);

            for(i = 1; i <= 15; i++){
                if(amount < priceThresholds[i+1] - 1){
                    printCost += amount * price[i];
                    calculation.push('b '+priceThresholds[i]+'-'+(1*priceThresholds[i]-1+1*amount)+' * '+price[i]+' ='+printCost);
                    break;
                }else{
                    printCost += (priceThresholds[i+1] - 1) * price[i];
                    amount    -= priceThresholds[i] - 1;
                    calculation.push('c '+priceThresholds[i]+'-'+(1*priceThresholds[i+1]-1)+' * '+price[i]+' ='+printCost);
                }
            }
        }else{ //it's only in the first range
            printCost   += amount * price[0];
            calculation.push('d 1-'+amount+' * '+price[0]+' ='+printCost);
        }

        console.log(calculation);

        var roundedPrice = roundPrice(printCost.toFixed(2));
        var subtotal = 1*printCost + 1*dataPreparationPrice;
        var vat = 1*subtotal * 0.071;
        var grossPrice = 1*subtotal + 1*vat;
        var totalPrice = 1*grossPrice + 1*shipmentPrice;


        \$J('#psPrintCost').text(printCost.toFixed(2));
        \$J('#psPriceSubtotal').text(subtotal.toFixed(2));
        \$J('#psPriceVAT').text(vat.toFixed(2));
        \$J('#psPriceGross').text(grossPrice.toFixed(2));
        \$J('#psPriceShipment').text(shipmentPrice.toFixed(2));
        \$J('#psPriceTotal').text(roundPrice(totalPrice).toFixed(2));
        \$J('#psPrice').text(roundPrice(totalPrice).toFixed(2));
    }

    var submitOrder = function(){
        \$J.ajax({
            url: '$DI?section=printshop&cmd=',
            type: 'post',
            data: '',
            dataType: 'json',
            success: ''
        });
    }

    var checkFilledFields = function(){
        var psSubject = \$J('#psSubject').val().replace(/\s+/, '');
        var psImage1 = \$J('#psImage1').val().replace(/\s+/, '');
        var psImage2 = \$J('#psImage2').val().replace(/\s+/, '');
     // var psImage3 = \$J('#psImage3').val().replace(/\s+/, '');

        var error = false;
        var mandatoryFields = [
            'psSubject',
            'psAmount',
            'psShipment',
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
            'psPhone',
        ]

        \$J(mandatoryFields).each(function(i, j){
            \$J('#'+j).val(\$J('#'+j).val().replace(/^\s+(.*?)\s+$/, '$1'));
            if(\$J('#'+j).val() == ''){
                \$J('#'+j[0]).addClass('missing');
                error = true;
            }else{
                \$J('#'+j[0]).removeClass('missing');
            }
        });

        if(\$J('#psShipment :checked').length == 0){
            \$J('#'+j[0]).addClass('missing');
            error = true;
        }else{
            \$J('#'+j[0]).removeClass('missing');
        }

        if(\$J('#psAcceptTerms :checked').length == 0){
            \$J('#'+j[0]).addClass('missing');
            error = true;
        }else{
            \$J('#'+j[0]).removeClass('missing');
        }

        return error;
    }

})();
EOJ;
        JS::registerCode($JS);


        if(!empty($_POST['psSubmitOrder'])){
            $this->_checkOrder();
        }
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
        $price = !empty($_POST['psPrice']) ? doubleval($_POST['pspPrice']) : '';
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

        $acceptTermsCheckbox = '<input '.(!empty($acceptTerms) ? 'checked="checked"' : 'sd="ff"').'
                                 type="checkbox" id="psAcceptTerms" name="psAcceptTerms" />
                                <label for="psAcceptTerms">'.$_ARRAYLANG['TXT_PRINTSHOP_ACCEPT_TERMS'].'</label>';

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

        print_r($_POST);
        print_r($_FILES);

        if(empty($subject)      || empty($amount)  || empty($shipment) || empty($invContact)  || empty($invAddress1)
        || empty($invAddress2)  || empty($invZip)  || empty($invCity)  || empty($shipContact) || empty($shipAddress1)
        || empty($shipAddress2) || empty($shipZip) || empty($shipCity) || empty($email)       || empty($phone)
        || empty($acceptTerms)){
           $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_MISSING_FIELDS']);
           return false;
        }

        if($this->_addOrder(
            $type, $format, $front, $back, $weight, $paper, $price, $amount, $filePath1, $filePath2, $filePath3, $email, $phone, $comment, $shipment,
            $invCompany, $invContact, $invAddress1, $invAddress2, $invZip, $invCity,
            $shipCompany, $shipContact, $shipAddress1, $shipAddress2, $shipZip, $shipCity
        )){
            $this->_sendMails();
        }else{
            $this->_setError($_ARRAYLANG['TXT_PRINTSHOP_ORDER_SAVE_ERROR']);
        }


//  orderId
//	type
//	format
//	front
//	back
//	weight
//	paper
//	status
//	price
//	amount
//	file1
//	file2
//	file3
//	email
//	telephone
//	comment
//	shipment
//	invoiceCompany
//	invoiceContact
//	invoiceAddress1
//	invoiceAddress2
//	invoiceZip
//	invoiceCity
//	shipmentCompany
//	shipmentContact
//	shipmentAddress1
//	shipmentAddress2
//	shipmentZip
//	shipmentCity




/*
psSubject=subject
psType=1
psFormat=2
psFront=1
psBack=1
psWeight=1
psPaper=1
psAmount=44
psCompanyI=rf
psCompanyS=lf
psContactI=rk
psContactS=lk
psAddress1I=ra1
psAddress1S=la1
psAddress2I=ra2
psAddress2S=la2
psZIPI=rp
psZIPS=lp
psCityI=ro
psCityS=lo
psEmail=asdf%40asdf.com
psPhone=12341234
psComment=ASDFQWDASDQWD
psTerms=on
*/
    }


    /**
     * Return the string for the page title
     *
     * @return string
     */
    function getPageTitle(){
        return '';
    }


    function _sendMails(){


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


     /**
     * parse the available messages
     *
     */
    function _parseMsgs(){
        $msg = '';
        foreach ($this->_errMsg as $msg) {
            $msg .= '<div>'.$msg.'</div>';
        	$this->_objTpl->setVariable('PRINTSHOP_ERROR_MSG');
        }
        $msg = '';
        foreach ($this->_okMsg as $msg) {
            $msg .= '<div>'.$msg.'</div>';
        	$this->_objTpl->setVariable('PRINTSHOP_OK_MSG');
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
