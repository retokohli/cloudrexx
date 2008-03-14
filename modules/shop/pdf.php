<?PHP
/**
 * PDF Creator
 *
 * This file creates a PDF-File with the information from the Database
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Thomas Kaelin <gwanun@astalavista.com>
 * @version       $Id:     Exp $
 * @package     contrexx
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */


require_once realpath(dirname(__FILE__)."/../../").'/config/configuration.php';
require_once ASCMS_CORE_PATH .'/API.php';
require_once ASCMS_LIBRARY_PATH.'/ezpdf/class.pdf.php';
require_once ASCMS_LIBRARY_PATH.'/ezpdf/class.ezpdf.php';

$errorMsg = '';
$objDatabase = getDatabaseObject($errorMsg);
if ($objDatabase === false) {
    die('Database error: '.$errorMsg);
}


class pdfCreator
{
    var $objDatabase;
    var $objResult;
    var $pdf;
    var $pdfSettingsFONT;
    var $pdfRowColor1;
    var $pdfRowColor2;
    var $pdfFontHeightHeader;
    var $pdfFontHeightFooter;
    var $pdfFontHeight;

    var $pdfID;
    var $pdfNAME;
    var $pdfLANG_ID;
    var $pdfBORDER_ON;
    var $pdfHEADER_ON;
    var $pdfHEADER_LEFT;
    var $pdfHEADER_RIGHT;
    var $pdfFOOTER_ON;
    var $pdfFOOTER_LEFT;
    var $pdfFOOTER_RIGHT;
    var $pdfCATEGORIES;

    var $langProductName;
    var $langCategoryName;
    var $langProductId;
    var $langPrice;

    var $currencySymbol;

    var $arrProducts;
    var $arrProductCat;

     /*
    * Constructor
    *
    * @version  1.0      initial version
    */
    function pdfCreator($plid)
    {
        global $objInit, $objDatabase, $_CONFIG;

        $this->_objDatabase = $objDatabase;

        $this->pdfID = $plid;
        $this->_objResult = $this->_objDatabase->Execute("SELECT * FROM ".DBPREFIX."module_shop".MODULE_INDEX."_pricelists WHERE id=".$plid);
        $this->pdfNAME = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['name']) : $this->_objResult->fields['name'];
        $this->pdfLANG_ID = $this->_objResult->fields['lang_id'];
        $this->pdfBORDER_ON = $this->_objResult->fields['border_on'];
        $this->pdfHEADER_ON = $this->_objResult->fields['header_on'];
        $this->pdfHEADER_LEFT = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['header_left']) : $this->_objResult->fields['header_left'];
        $this->pdfHEADER_RIGHT = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['header_right']) : $this->_objResult->fields['header_right'];
        $this->pdfFOOTER_ON = $this->_objResult->fields['footer_on'];
        $this->pdfFOOTER_LEFT = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['footer_left']) : $this->_objResult->fields['footer_left'];
        $this->pdfFOOTER_RIGHT = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['footer_right']) : $this->_objResult->fields['footer_right'];
        $this->pdfCATEGORIES = $this->_objResult->fields['categories'];

        $objInit->backendLangId = $this->pdfLANG_ID;
        $_ARRAYLANG = $objInit->loadLanguageData('shop');

        $this->langProductName = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($_ARRAYLANG['TXT_PRODUCT_NAME']) : $_ARRAYLANG['TXT_PRODUCT_NAME'];
        $this->langProductCustomId = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID']) : $_ARRAYLANG['TXT_SHOP_PRODUCT_CUSTOM_ID'];
        $this->langProductId = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($_ARRAYLANG['TXT_ID']) : $_ARRAYLANG['TXT_ID'];
        $this->langPrice = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($_ARRAYLANG['TXT_UNIT_PRICE']) : $_ARRAYLANG['TXT_UNIT_PRICE'];
        $this->langCategoryName = strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($_ARRAYLANG['TXT_CATEGORY']) : $_ARRAYLANG['TXT_CATEGORY'];

        // set currency symbol
        $this->_objResult = $this->_objDatabase->Execute("SELECT symbol FROM ".DBPREFIX."module_shop".MODULE_INDEX."_currencies WHERE is_default=1");
        if(!$this->_objResult->EOF){
            $this->currencySymbol = $this->_objResult->fields['symbol'];
        }

        $this->_objResult = $this->_objDatabase->Execute("SELECT pro.id,pro.product_id,pro.title,pro.catid,pro.normalprice,pro.status,pro.is_special_offer,cat.catname,cat.catid
                          FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products as pro,
                                ".DBPREFIX."module_shop".MODULE_INDEX."_categories as cat
                          WHERE pro.catid = cat.catid AND pro.status=1
                          ORDER BY pro.id DESC");
        while (!$this->_objResult->EOF) {
            $this->arrProducts[$this->_objResult->fields['id']] = array    (    'title' => (strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['title']) : $this->_objResult->fields['title']),
                                                                'catname' => (strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['catname']) : $this->_objResult->fields['catname']),
                                                                'product_id' => $this->_objResult->fields['product_id'],
                                                                'id' => $this->_objResult->fields['id'],
                                                                'normalprice' => $this->_objResult->fields['normalprice']." ".$this->currencySymbol
                                                            );
            $this->arrProductCat[$this->_objResult->fields['id']] = $this->_objResult->fields['catid'];
            $this->_objResult->MoveNext();
        }

        //mark special offers
        $this->_objResult = $this->_objDatabase->Execute("SELECT pro.id,pro.product_id,pro.title,pro.catid,pro.discountprice,pro.status,cat.catname,cat.catid
                          FROM ".DBPREFIX."module_shop".MODULE_INDEX."_products as pro,
                                ".DBPREFIX."module_shop".MODULE_INDEX."_categories as cat
                          WHERE pro.catid = cat.catid AND pro.status=1 AND pro.is_special_offer=1
                          ORDER BY pro.id DESC");

        while (!$this->_objResult->EOF) {
            $this->arrProducts[$this->_objResult->fields['id']] = array(    'title' => (strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['title']) : $this->_objResult->fields['title']),
                                                            'catname' => (strtolower($_CONFIG['coreCharacterEncoding']) == 'utf-8' ? utf8_decode($this->_objResult->fields['catname']) : $this->_objResult->fields['catname']),
                                                            'product_id' => $this->_objResult->fields['product_id'],
                                                            'id' => $this->_objResult->fields['id'],
                                                            'normalprice' => "S ".$this->_objResult->fields['discountprice']." ".$this->currencySymbol
                                                          );
            $this->_objResult->MoveNext();
        }
        $this->pdf = new Cezpdf('A4');
        $this->pdf->setEncryption('','',array('print'));
    }

    function createOutput()
    {
        $this->pdf->selectFont(ASCMS_LIBRARY_PATH.'/ezpdf/fonts/'.$this->pdfSettingsFONT);
        $this->pdf->ezSetMargins(0,0,0,0); // disable the margins temporary
        $this->pdf->setLineStyle(0.5);
//define the TOP-MARGIN
        if ($this->pdfHEADER_ON == 1) { // header should be shown
                $arrayHeaderLeft = explode("\n",$this->pdfHEADER_LEFT); // split the string into an array
                $arrayHeaderRight = explode("\n",$this->pdfHEADER_RIGHT);

                $countLeft = count($arrayHeaderLeft);
                $countRight = count($arrayHeaderRight);

                if ($countLeft > $countRight) {
                    $biggerCountTop = $countLeft;
                } else {
                    $biggerCountTop = $countRight;
                }
            $marginTop = ($biggerCountTop * 14)+36; // set the margins from top
        } else {
            $marginTop = 30;
        }
//define the BOTTOM-MARGIN

        if ($this->pdfFOOTER_ON == 1) { // footer should be shown

        // Change the Content of the FOOTER-VARS
                $this->pdfFOOTER_LEFT = str_replace('<--DATE-->',date('d.m.Y',time()),$this->pdfFOOTER_LEFT);
                $this->pdfFOOTER_RIGHT = str_replace('<--DATE-->',date('d.m.Y',time()),$this->pdfFOOTER_RIGHT);

                $arrayFooterLeft = explode("\n",$this->pdfFOOTER_LEFT); // split the string into an array
                $arrayFooterRight = explode("\n",$this->pdfFOOTER_RIGHT);

                $countLeft = count($arrayFooterLeft);
                $countRight = count($arrayFooterRight);

                if ($countLeft > $countRight) {
                    $biggerCountBottom = $countLeft;
                } else {
                    $biggerCountBottom = $countRight;
                }
            $marginBottom = ($biggerCountBottom * 20)+20; // set the bottom-margins
        } else {
            $marginBottom = 20;
        }
//If selected, create borders around the content
        if ($this->pdfBORDER_ON == 1) {
            $linesForAllPages = $this->pdf->openObject();
            $this->pdf->saveState();
                $this->pdf->setStrokeColor(0,0,0,1);
                $this->pdf->rectangle(10,10,575.28,821.89);
            $this->pdf->restoreState();
            $this->pdf->closeObject();
            $this->pdf->addObject($linesForAllPages,'all');
        }
//Create the header.. or not
        if ($this->pdfHEADER_ON == 1)
        { // header should be shown
            $this->pdf->ezSetY(830);
            $headerForAllPages = $this->pdf->openObject();
            $this->pdf->saveState();

                for ($i = 0; $i < $biggerCountTop;$i++)
                {
                        $temp[$i] = array('left' => $arrayHeaderLeft[$i],'right' => $arrayHeaderRight[$i]);
                }

                foreach ($temp as $key => $value)
                {
                        $headerArray[$key] = $value;
                }

                $tempY=$this->pdf->ezTable($headerArray,'','',array('showHeadings' => 0,
                                                        'fontSize' => $this->pdfFontHeightHeader,
                                                        'shaded' => 0,
                                                        'width' => 540,
                                                        'showLines' => 0,
                                                        'xPos' => 'center',
                                                        'xOrientation' => 'center',
                                                        'cols' => array('right' => array('justification' => 'right'))
                                                        )
                                );
                $tempY = $tempY - 5;
                if ($this->pdfBORDER_ON == 1)
                {
                    $this->pdf->setStrokeColor(0,0,0);
                    $this->pdf->line(10,$tempY,585.28,$tempY);
                }
                $startpointY = $tempY - 5;    // the startpoint is needed for the output
            $this->pdf->restoreState();
            $this->pdf->closeObject();
            $this->pdf->addObject($headerForAllPages,'all');
        }
        // Create the footer.. or not
        if ($this->pdfFOOTER_ON == 1) { // footer should be shown
            $footerForAllPages = $this->pdf->openObject();
            $this->pdf->saveState();
            $tempY = $marginBottom-5;
            if ($this->pdfBORDER_ON == 1) {
                $this->pdf->setStrokeColor(0,0,0);
                $this->pdf->line(10,$tempY,585.28,$tempY);
            }
            // I need the length of the longest word
            $longestWord = '';
            for ($i = $biggerCountBottom; $i >= 0;$i--) {
                if ($longestWord < strlen($arrayFooterRight[$i])) {
                    $longestWord = strlen($arrayFooterRight[$i]);
                }
            }

            for ($i = $biggerCountBottom-1;$i >= 0; $i--) {
                if ($arrayFooterLeft[$i] == '<--PAGENUMBER-->') {
                    $pageNumbersX = 65;
                    $pageNumbersY = $tempY-18-($i*$this->pdfFontHeightFooter);
                    $pageNumbersFont = $this->pdfFontHeight;
                } else {
                    $this->pdf->addText(25,$tempY-18-($i*$this->pdfFontHeightFooter),$this->pdfFontHeightFooter,$arrayFooterLeft[$i]);
                }

                if ($arrayFooterRight[$i] == '<--PAGENUMBER-->') {
                    $pageNumbersX = 595.28-25;
                    $pageNumbersY = $tempY-18-($i*$this->pdfFontHeightFooter);
                    $pageNumbersFont = $this->pdfFontHeight;
                } else {
                    $this->pdf->addText(595.28-($longestWord*7)-20,$tempY-18-($i*$this->pdfFontHeightFooter),$this->pdfFontHeightFooter,$arrayFooterRight[$i]);
                }
            }

            $this->pdf->restoreState();
            $this->pdf->closeObject();
            $this->pdf->addObject($footerForAllPages,'all');
        }
// Create the Numbersystem
    if (isset($pageNumbersX)) {
        $this->pdf->ezStartPageNumbers($pageNumbersX,$pageNumbersY,$pageNumbersFont,'','',1);
    }
// Sete here the Margins
         $this->pdf->ezSetMargins($marginTop,$marginBottom,30,30); // reset the margins
//Create the products-table
        if (isset($startpointY)) { // there's a header, so the products must be moved down
            $this->pdf->ezSetY($startpointY);
        }

        if ($this->pdfCATEGORIES == '*') { //all products
            $this->_objResult = $this->_objDatabase->Execute("SELECT catid FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories ORDER BY catid");
            while (!$this->_objResult->EOF) {
                $i++;
                $catArray[$i] = $this->_objResult->fields['catid'];
                $this->_objResult->MoveNext();
            }
        } else {
            $catArray = explode(",",$this->pdfCATEGORIES);
        }
        foreach ($catArray as $catValue) {
            foreach ($this->arrProductCat as $ProductCatKey => $ProductCatValue) {
                if ($ProductCatValue == $catValue) {
                    $arrOutput[$ProductCatKey] = $this->arrProducts[$ProductCatKey];
                }
            }
        }

        $this->pdf->ezTable($arrOutput,array(    'title' => '<b>'.$this->langProductName.'</b>',
                                                'catname' => '<b>'.$this->langCategoryName.'</b>',
                                                'product_id' => '<b>'.$this->langProductCustomId.'</b>',
                                                'id' => '<b>'.$this->langProductId.'</b>',
                                                'normalprice' => '<b>'.$this->langPrice.'</b>'),'',
                        array('showHeadings' => 1,
                        'fontSize' => $this->pdfFontHeight,
                        'width' => 530,
                        'innerLineThickness' => 0.5,
                        'outerLineThickness' => 0.5,
                        'shaded' => 2,
                        'shadeCol' => array(hexdec(substr($this->pdfRowColor1,0,2))/255,
                                            hexdec(substr($this->pdfRowColor1,2,2))/255,
                                            hexdec(substr($this->pdfRowColor1,4,2))/255
                                            ),
                        'shadeCol2' => array(hexdec(substr($this->pdfRowColor2,0,2))/255,
                                            hexdec(substr($this->pdfRowColor2,2,2))/255,
                                            hexdec(substr($this->pdfRowColor2,4,2))/255
                                            ),
                        // Total 530
                        'cols' => array('id' => array('width' => 40,'justification' => 'right'),
                                        'product_id' => array('width' => 50,'justification' => 'right'),
                                        'title' => array('width' => 255),
                                        'catname' => array('width' => 135),
                                        'normalprice' => array('width' => 50,'justification' => 'right'))
                             )
                    );
    $this->pdf->ezStream();
    }
}

$objInit = new InitCMS($mode="backend");

$pdfCreator = new pdfCreator(intval($_GET['plid']));
$pdfCreator->pdfSettingsFONT = 'Helvetica';
$pdfCreator->pdfRowColor1 = 'DDDDDD';
$pdfCreator->pdfRowColor2 = 'FFFFFF';
$pdfCreator->pdfFontHeightHeader = 8;
$pdfCreator->pdfFontHeightFooter = 7;
$pdfCreator->pdfFontHeight = 7;
$pdfCreator->createOutput();

?>
