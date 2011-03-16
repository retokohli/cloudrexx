<?php
/**
 * Auction
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_auction
 * @todo        Edit PHP DocBlocks!
 */
//ini_set('display_errors', 1);
//error_reporting (E_ALL);


/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/auction/lib/auctionLib.class.php';
require_once ASCMS_CORE_PATH.'/modulemanager.class.php';
require_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php';

/**
 * Auction
 *
 * Demo auction class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
 * @package     contrexx
 * @subpackage  module_auction
 */
class Auction extends auctionLibrary
{
    /**
    * Template object
    *
    * @access private
    * @var object
    */
    var $_objTpl;
    var $pageContent;
    var $communityModul;
    var $mediaPath;
    var $mediaWebPath;
    var $settings;
    var $categories;
    var $entries;

    /**
     * Constructor
     * @global object $objTemplate
     * @global array $_ARRAYLANG
     */
    function __construct($pageContent)
    {
        $this->pageContent = $pageContent;

        $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->mediaPath = ASCMS_AUCTION_UPLOAD_PATH . '/';
        $this->mediaWebPath = ASCMS_AUCTION_UPLOAD_WEB_PATH . '/';

        //get settings
        $this->settings = $this->getSettings();

        //check community modul
        $objModulManager = new modulemanager();
        $arrInstalledModules = $objModulManager->getModules();
        if (in_array(23, $arrInstalledModules)) {
            $this->communityModul = true;
        } else {
            $this->communityModul = false;
        }

        //ipn Check
        if (isset($_GET['act'])) {
            switch($_GET['act']) {
                case "paypalIpnCheck";
                    $objPaypal = new PayPal;
                    $objPaypal->ipnCheck();
                    exit;
                    break;
                default:
                    //nothging
                    break;
            }
        }
    }

    /**
    * Get content page
    *
    * @access public
    */
    function getPage()
    {
        CSRF::add_code();
        if (!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        }

        switch ($_GET['cmd']) {
            case 'detail':
                $this->entryDetails($_GET['id']);
            break;
            case 'send':
                CSRF::check_code();
                $this->sendMessage($_GET['id']);
            break;
            case 'add':
                CSRF::check_code();
                $this->addEntry();
            break;
            case 'confirm':
                CSRF::check_code();
                $this->confirmEntry();
            break;
            case 'edit':
                CSRF::check_code();
                $this->editEntry();
            break;
            case 'del':
                CSRF::check_code();
                $this->delEntry();
            break;
            case 'search':
                $this->searchEntry();
            break;
            break;
            default:
                $this->showCategories();
            break;
        }
        return $this->_objTpl->get();
    }



    function showCategories()
    {
        global $objDatabase, $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        $catRows                 = 2;
        $categorieRowWidth         = substr(100/$catRows, 0, 2)."%";
        $categorieRows            = array();
        $arrRowsIndex            = array();

        for($x = 1; $x <= $catRows; $x++) {
            $categorieRows[$x] = "";
        }

        //get categories
        $objResult = $objDatabase->Execute("SELECT id, name, description FROM ".DBPREFIX."module_auction_categories WHERE status = '1' ORDER BY displayorder");

        if ($objResult !== false) {
            while(!$objResult->EOF) {
                $this->categories['name'][$objResult->fields['id']] = $objResult->fields['name'];
                $this->categories['description'][$objResult->fields['id']] = $objResult->fields['description'];
                $objResult->MoveNext();
            }
        }

        //get search
        $this->getSearch();

        //get navigation
        $this->getNavigation($_GET['id']);

        //show categories
        $i = 1;
        if (!empty($this->categories) && !isset($_GET['id'])) {
            foreach($this->categories['name'] as $catKey => $catName) {
                $count = $this->countEntries($catKey);
                if ($count == "") {
                    $count = "0";
                }

                $categorieRows[$i] .= "<a class='catLink' href='?section=auction&amp;id=".$catKey."'>".htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET)."</a>&nbsp;(".$count.")<br />";
                array_push($arrRowsIndex, substr(htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET), 0, 1)."<a class='catLink' href='?section=auction&amp;id=".$catKey."'>".htmlentities($catName, ENT_QUOTES, CONTREXX_CHARSET)."</a>&nbsp;(".$count.")<br />");

                if ($i%$catRows==0) {
                    $i=1;
                }else{
                    $i++;
                }
            }
			$today = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
            $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."module_auction WHERE status = '1' AND enddate >= '".$today."'");
            $allFeeds = $objResult->RecordCount();
            $insertFeeds = $allFeeds." ".$_ARRAYLANG['TXT_AUCTION_ADD_ADVERTISEMENT'];

            $this->_objTpl->hideBlock('showCategoriesTitle');
            $this->_objTpl->parse('showInsertEntries');
            $this->_objTpl->hideBlock('showEntriesHeader');

            $this->showLatestEntries();
        }else{
            $title             = $this->categories['name'][$_GET['id']];
            $description     = $this->getDescription($_GET['id']);
            $selectionOffer        = "";
            $selectionSearch    = "";
            $selectionAll        = "";

            if (!isset($_GET['type'])) {
                $_GET['type'] = '';
            }

            switch ($_GET['type']) {
                case 'offer':
                    $selectionOffer        = "checked";
                break;
                case 'search':
                    $selectionSearch    = "checked";
                break;
                default:
                    $selectionAll        = "checked";
                break;
            }
            //typselector
            $selector = '<input type="radio" name="type" onclick="location.replace(\'index.php?section=auction&id='.$_GET['id'].'\')" '.$selectionAll.' />'.$_ARRAYLANG['TXT_AUCTION_ALL'].'&nbsp;<input type="radio" name="type" onclick="location.replace(\'index.php?section=auction&id='.$_GET['id'].'&type=offer\')" '.$selectionOffer.' />'.$_ARRAYLANG['TXT_AUCTION_OFFERS'].'&nbsp;<input type="radio" name="type" onclick="location.replace(\'index.php?section=auction&id='.$_GET['id'].'&type=search\')" '.$selectionSearch.' />'.$_ARRAYLANG['TXT_AUCTION_REQUEST'];
            //get entries
            $this->showEntries($_GET['id']);

            $this->_objTpl->parse('showCategoriesTitle');
            $this->_objTpl->hideBlock('showInsertEntries');
            $this->_objTpl->hideBlock('showCategories');
        }

        //select View
        if  ($this->settings['indexview']['value'] == 1) {
            $categorieRows ='';
            sort($arrRowsIndex);

            $i = 0;
            $firstCol = true;
            $index = '';
            foreach($arrRowsIndex as $rowName) {
                if ($index != substr($rowName, 0, 1)) {
                    $index = substr($rowName, 0, 1);
                    if ($i%$catRows==0) {
                        $i=1;
                    }else{
                        $i++;
                    }

                    $categorieRows[$i] .= (!$firstCol ? "<br />" : "")."<b>".$index."</b><br />".substr($rowName,1);
                    if ($i == $catRows && $firstCol) {
                        $firstCol = false;
                    }
                } else {
                    $categorieRows[$i] .= substr($rowName,1);
                }
            }
        }

        //spez fields
        $objResult = $objDatabase->Execute("SELECT id, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1'");
        if ($objResult !== false) {
            while(!$objResult->EOF) {
                $spezFields[$objResult->fields['id']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }

        // set variables
        $this->_objTpl->setVariable(array(
// TODO: $paging is never set!
//            'AUCTION_SEARCH_PAGING'            => $paging,
            'AUCTION_CATEGORY_ROW_WIDTH'        => $categorieRowWidth,
            'AUCTION_CATEGORY_ROW1'            => $categorieRows[1]."<br />",
            'AUCTION_CATEGORY_ROW2'            => $categorieRows[2]."<br />",
            'AUCTION_CATEGORY_TITLE'            => $title,
            'AUCTION_CATEGORY_DESCRIPTION'    => $description,
            'DIRECTORY_INSERT_ENTRIES'        => $insertFeeds,
            'TXT_AUCTION_ENDDATE'            => $_CORELANG['TXT_END_DATE'],
            'TXT_AUCTION_TITLE'                => $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_AUCTION_PRICE'                => $_ARRAYLANG['TXT_AUCTION_PRICE'],
            'TXT_AUCTION_CITY'                => $_ARRAYLANG['TXT_AUCTION_CITY'],
            'AUCTION_TYPE_SECECTION'            => $selector,
            'TXT_AUCTION_SPEZ_FIELD_1'        => $spezFields[1],
            'TXT_AUCTION_SPEZ_FIELD_2'        => $spezFields[2],
            'TXT_AUCTION_SPEZ_FIELD_3'        => $spezFields[3],
            'TXT_AUCTION_SPEZ_FIELD_4'        => $spezFields[4],
            'TXT_AUCTION_SPEZ_FIELD_5'        => $spezFields[5],
            'TXT_AUCTION_CATEGORIES'              => $_ARRAYLANG['TXT_AUCTION_CATEGORIES'],
            'TXT_AUCTION_AUCTIONS'                => $_ARRAYLANG['TXT_AUCTION_AUCTIONS'],
            'TXT_AUCTION_SEARCH_TITLE'            => $_ARRAYLANG['TXT_AUCTION_SEARCH_TITLE'],
        ));

    }


    function showEntries($catId)
    {
        global $objDatabase, $_ARRAYLANG;

        $today = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
        $type                 = "";
        $typePaging            = "";

        if ($this->settings['maxdayStatus'] != 0) {
            $this->checkEnddate();
        }

        if (!isset($_GET['type'])) {
            $_GET['type'] = '';
        }

        switch ($_GET['type']) {
            case 'offer':
                $type                = "AND type='offer'";
                $typePaging            = "&type=offer";
            break;
            case 'search':
                $type                 = "AND type ='search'";
                $typePaging            = "&type=search";
            break;
            default:
                $type                 = "";
                $typePaging            = "";
            break;
        }

        switch ($_GET['sort']) {
            case 'title':
                $sort                = "title";
                $sortPaging            = "&sort=title";
            break;
            case 'enddate':
                $sort                = "enddate";
                $sortPaging            = "&sort=enddate";
            break;
            case 'price':
                $sort                = "price";
                $sortPaging            = "&sort=price";
            break;
            case 'residence':
                $sort                = "residence";
                $sortPaging            = "&sort=residence";
            break;
            default:
                $sort                = "enddate";
                $sortPaging            = "";
            break;
        }

        if (isset($_GET['way'])) {
            $way         = $_GET['way']=='ASC' ? 'DESC' : 'ASC';
            $wayPaging     = '&way='.$_GET['way'];
        }else{
            $way         = 'ASC';
            $wayPaging     = '';
        }

        $this->_objTpl->setVariable(array(
            'AUCTION_ENDDATE_SORT'            => "?section=auction&id=".$catId."&type=".$_GET['type']."&sort=enddate&way=".$way,
            'AUCTION_TITLE_SORT'                => "?section=auction&id=".$catId."&type=".$_GET['type']."&sort=title&way=".$way,
            'AUCTION_PRICE_SORT'                => "?section=auction&id=".$catId."&type=".$_GET['type']."&sort=price&way=".$way,
            'AUCTION_CITY_SORT'                => "?section=auction&id=".$catId."&type=".$_GET['type']."&sort=residence&way=".$way,
            'TXT_AUCTION_BIDS'                => $_ARRAYLANG["TXT_AUCTION_BIDS"],
            'TXT_AUCTION_OFFER_END'                => $_ARRAYLANG["TXT_AUCTION_OFFER_END"],
            'TXT_AUCTION_DURATION'                => $_ARRAYLANG["TXT_AUCTION_DURATION"],
        ));

//        if ($this->settings['maxdayStatus'] == 0) {
//            $where = '';
//        } else {
            $where = 'AND enddate >= "'.$today.'"';
        //}


        /////// START PAGING ///////
        $pos= intval($_GET['pos']);

        if ($sort == 'price') {
            $query='SELECT *, `id`,`name`,`email`,`type`,`title`,`description`,`premium`,`picture_1`,`picture_2`,`picture_3`,`picture_4`,`picture_5`,`catid`, CAST(`price` AS UNSIGNED) as `price`,`regdate`,`enddate`,`userid`,`userdetails`,`status`,`regkey`,`paypal`,`spez_field_1`,`spez_field_2`,`spez_field_3`,`spez_field_4`,`spez_field_5` FROM '.DBPREFIX.'module_auction WHERE catid = "'.contrexx_addslashes($catId).'" AND status="1" '.$where.' '.$type.' ORDER BY '.$sort.' '.$way;
        }else{
            $query='SELECT * FROM '.DBPREFIX.'module_auction WHERE catid = "'.contrexx_addslashes($catId).'" AND status="1" '.$where.' '.$type.' ORDER BY '.$sort.' '.$way;
        }

        $objResult = $objDatabase->Execute($query);
        $count = $objResult->RecordCount();
        if ($count > $this->settings['paging']) {
            $paging = getPaging($count, $pos, "&amp;section=auction&amp;id=".$catId.$typePaging.$sortPaging.$wayPaging, "<b>Auktionen</b>", true, $this->settings['paging']);
        }

        $this->_objTpl->setVariable('SEARCH_PAGING', $paging);
        $objResult = $objDatabase->SelectLimit($query, $this->settings['paging'], $pos);
        /////// END PAGING ///////

        $i=0;
        if ($objResult !== false) {
               while (!$objResult->EOF) {
                   if (empty($objResult->fields['picture_1'])) {
                    $objResult->fields['picture'] = 'no_picture.gif';
                }

                   $info         = getimagesize($this->mediaWebPath.'pictures/tmb_'.$objResult->fields['picture']);
                   $height     = '';
                   $width         = '';

                   if ($info[0] <= $info[1]) {
                    if ($info[1] > 50) {
                        $faktor = $info[1]/50;
                        $height = 50;
                        $width    = $info[0]/$faktor;
                    } else {
                        $height = $info[1];
                        $width = $info[0];
                    }
                }else{
                    $faktor = $info[0]/80;
                    $result = $info[1]/$faktor;
                    if ($result > 50) {
                        if ($info[1] > 50) {
                            $faktor = $info[1]/50;
                            $height = 50;
                            $width    = $info[0]/$faktor;
                        }else{
                            $height = $info[1];
                            $width = $info[0];
                        }
                    }else{
                        if ($info[0] > 80) {
                            $width = 80;
                            $height = $info[1]/$faktor;
                        }else{
                            $width = $info[0];
                            $height = $info[1];
                        }
                    }
                }
                $width != '' ? $width = 'width="'.round($width,0).'"' : $width = '';
                $height != '' ? $height = 'height="'.round($height,0).'"' : $height = '';
                
                $image = '<img src="'.$this->mediaWebPath.'pictures/tmb_'.$objResult->fields['picture'].'" '.$width.' '.$height.' border="0" alt="'.$objResult->fields['title'].'" />';
                
                
                $objFWUser = FWUser::getFWUserObject();
                $objUser = $objFWUser->objUser->getUser($objResult->fields['userid']);
                if ($objUser) {
                    $city = $objUser->getProfileAttribute('city');
                }

                   if ($objResult->fields['premium'] == 1) {
                       $row = "auctionRow1";
                   }else{
                       $row = $i % 2 == 0 ? 'auctionRow2' : 'auctionRow3';
                   }

                   $enddate = date("d.m.Y H:i", $objResult->fields['enddate']);

                   if ($objResult->fields['price'] == 'forfree') {
                       $price = $_ARRAYLANG['TXT_AUCTION_FREE'];
                   }elseif ($objResult->fields['price'] == 'agreement') {
                       $price = $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'];
                   }else{
                       $price = $objResult->fields['price'].' '.$this->settings['currency'];
                   }
                   
                   $Bids = $this->GetAuctionBids($objResult->fields['id']);
            
		            if(count($Bids)>0){
		            	$LastPrice = $Bids[count($Bids)-1]["bid_price"];
		            }else{
		            	$LastPrice = $objResult->fields['startprice'];
		            }
		            
		            
		             $price = $LastPrice.' '.$this->settings['currency'];
                   
                   // auction pictures tmb
                   // ---------------------------------
                   $auction_tmb 	= ASCMS_AUCTION_UPLOAD_WEB_PATH.'/no_img.gif';
                   $auction_tmbx	= 0;
                   for($z=1; $z<6; $z++){
	                	if($objResult->fields['picture_'.$z]!='' && $auction_tmbx==0){
	                		$auction_tmb = ASCMS_AUCTION_UPLOAD_WEB_PATH.'/tmb_'.$objResult->fields['picture_'.$z];
	                		$auction_tmbx = 1;
	                	}
	                }
                   

                   $this->_objTpl->setVariable(array(
                    'AUCTION_ENDDATE'            => $enddate,
                    'AUCTION_TITLE'                => $objResult->fields['title'],
                    'AUCTION_DESCRIPTION'        => substr($objResult->fields['description'], 0, 110)."<a href='index.php?section=auction&cmd=detail&id=".$objResult->fields['id']."' target='_self'>[...]</a>",
                    'AUCTION_PRICE'                => number_format($price, 2,',', '.'),
                    'AUCTION_PICTURE'            => $image,
                    'AUCTION_ROW'                => $row,
                    'AUCTION_DETAIL'                => "index.php?section=auction&cmd=detail&id=".$objResult->fields['id'],
                    'AUCTION_ID'                    => $objResult->fields['id'],
                    'AUCTION_CITY'                => $city,
                    'AUCTION_SPEZ_FIELD_1'        => $objResult->fields['spez_field_1'],
                    'AUCTION_SPEZ_FIELD_2'        => $objResult->fields['spez_field_2'],
                    'AUCTION_SPEZ_FIELD_3'        => $objResult->fields['spez_field_3'],
                    'AUCTION_SPEZ_FIELD_4'        => $objResult->fields['spez_field_4'],
                    'AUCTION_SPEZ_FIELD_5'        => $objResult->fields['spez_field_5'],
                    'AUCTION_TMB'				=> $auction_tmb,
                    'AUCTION_BIDS'				=> count($this->GetAuctionBids($objResult->fields['id'])),
                ));

                $this->_objTpl->parse('showEntries');

                $i++;
                $objResult->MoveNext();
               }

           }



           if ($count <= 0) {
            $this->_objTpl->setVariable(array(
                'AUCTION_NO_ENTRIES_FOUND'            => $_ARRAYLANG['TXT_AUCTION_NO_ENTRIES_FOUND'],
            ));

            $this->_objTpl->parse('noEntries');
        }


    }

    function showLatestEntries()
    {
        global $objDatabase, $_ARRAYLANG;

        if ($this->_objTpl->blockExists('showLatestEntries')) {
            $objEntries = $objDatabase->SelectLimit('SELECT id, title, picture FROM '.DBPREFIX.'module_auction WHERE status !=0 ORDER BY id DESC', 4);
            $colCount = 2;
            $entryNr = 1;
            $rowNr = 1;

            if ($objEntries && $objEntries->RecordCount() > 0) {
                   while (!$objEntries->EOF) {
                       if ($objEntries->fields['picture'] == '') {
                           $pic = 'no_picture.gif';
                       } else {
                           $pic = $objEntries->fields['picture'];
                       }
                       $info         = getimagesize($this->mediaPath.'pictures/'.$pic);
                       $height     = '';
                       $width         = '';

                       if ($info[0] <= $info[1]) {
                           $height = 50;
                       } else {
                           $faktor = $info[0]/80;
                           $result = $info[1]/$faktor;
                           if ($result > 50) {
                               $height = 50;
                           } else {
                               $width = 80;
                           }
                       }

                    $width != '' ? $width = 'width="'.$width.'"' : $width = '';
                    $height != '' ? $height = 'height="'.$height.'"' : $height = '';

                       $image = '<img src="'.$this->mediaWebPath.'pictures/'.$pic.'" '.$width.' '.$height.' border="0" alt="'.$objEntries->fields['title'].'" />';

                       $this->_objTpl->setVariable(array(
                        'AUCTION_TITLE'                => htmlentities($objEntries->fields['title'], ENT_QUOTES, CONTREXX_CHARSET),
                        'AUCTION_PICTURE'            => $image,
                        'AUCTION_ROW'                => ($entryNr % 2 == ($rowNr % 2) ? 'description' : 'description'),
                        'AUCTION_DETAIL'                => "index.php?section=auction&cmd=detail&id=".$objEntries->fields['id']
                    ));
                    $this->_objTpl->parse('showLatestEntryCols');
                    if ($entryNr % $colCount == 0) {
                        $rowNr++;
                        $this->_objTpl->parse('showLatestEntryRows');
                    }

                    $entryNr++;
                    $objEntries->MoveNext();
                   }

                   $this->_objTpl->parse('showLatestEntries');
               } else {
                   $this->_objTpl->hideBlock('showLatestEntries');
               }
        }
    }


    function getSearch() {

         global $objDatabase, $_ARRAYLANG, $_CORELANG;

         $options = '';

         if  ($this->settings['indexview']['value'] == 1) {
            $order = "name";
        } else {
            $order = "displayorder";
        }

        $objResultSearch = $objDatabase->Execute("SELECT id, name, description FROM ".DBPREFIX."module_auction_categories WHERE status = '1' ORDER BY ".$order."");

        if ($objResultSearch !== false) {
            while(!$objResultSearch->EOF) {
                $options .= '<option value="'.$objResultSearch->fields['id'].'">'.$objResultSearch->fields['name'].'</option>';
                $objResultSearch->MoveNext();
            }
        }

         $inputs     = '<tr><td width="100" height="20">'.$_ARRAYLANG['TXT_AUCTION_CATEGORY'].'</td><td><select name="catid" style="width:194px;"><option value="">'.$_ARRAYLANG['TXT_AUCTION_ALL_CATEGORIES'].'</option>'.$options.'</select></td></tr>';
        $inputs     .= '<tr><td width="100" height="20">'.$_CORELANG['TXT_TYPE'].'</td><td><select name="type" style="width:194px;"><option value="">'.$_ARRAYLANG['TXT_AUCTION_ALL_TYPES'].'</option><option value="offer">'.$_ARRAYLANG['TXT_AUCTION_OFFER'].'</option><option value="search">'.$_ARRAYLANG['TXT_AUCTION_SEARCH'].'</option></select></td></tr>';

        $options = '';

//        $arrPrices = explode(",", $this->settings['searchPrice']);
//
//        foreach ($arrPrices as $priceValue) {
//            $options .= '<option value="'.$priceValue.'">'.$priceValue.' '.$this->settings['currency'].'</option>';
//        }

        $inputs     .= '<tr><td width="100" height="20">'.$_ARRAYLANG['TXT_AUCTION_PRICE_MAX'].'</td><td><select name="price" style="width:194px;"><option value="">'.$_ARRAYLANG['TXT_AUCTION_ALL_PRICES'].'</option>'.$options.'</select></td></tr>';


        // set variables
        $this->_objTpl->setVariable(array(
            'TXT_AUCTION_SEARCH'                    => $_CORELANG['TXT_SEARCH'],
            'TXT_AUCTION_SEARCH_EXP'                => $_CORELANG['TXT_EXP_SEARCH'],
            'AUCTION_EXP_SEARCH_FIELD'             => $inputs,
        ));
    }



    function getDescription($id)
    {
        global $objDatabase, $_ARRAYLANG;

        //get categorie
        if ($this->settings['description'] == 1) {
            $objResult = $objDatabase->Execute("SELECT description FROM ".DBPREFIX."module_auction_categories WHERE status = '1' AND id = '".contrexx_addslashes($id)."' ORDER BY id DESC");
            if ($objResult !== false) {
                while(!$objResult->EOF) {
                    $description = "<br/>".$objResult->fields['description'];
                    $objResult->MoveNext();
                }
            }
        }else{
            $description = "";
        }

        return $description;
    }



    function getNavigation($catId) {

        global $objDatabase, $_ARRAYLANG;

        //get categorie
         $objResult = $objDatabase->Execute("SELECT  id, name FROM ".DBPREFIX."module_auction_categories WHERE status = '1' AND id = '".contrexx_addslashes($catId)."'");
        if ($objResult !== false)    {
            if ($objResult->fields['name'] != '') {
                $verlauf = "&nbsp;&raquo;&nbsp;<a href='?section=auction&amp;id=".$catId."'>".$objResult->fields['name']."</a>";
            }else{
                $verlauf = "";
            }
         }

         // set variables
        $this->_objTpl->setVariable(array(
            'TXT_AUCTION'                    => $_ARRAYLANG['TXT_ENTRIES'],
            'AUCTION_CATEGORY_NAVI'            => $verlauf,
        ));
    }


    function _makeOffer($id){
    	global $objDatabase, $_ARRAYLANG, $_CORELANG;
    	
    	$id = $_REQUEST["id"];
    	
    	$Bids = $this->GetAuctionBids($id);
            
        if(count($Bids)>0){
        	$LastPrice = $Bids[count($Bids)-1]["bid_price"];
        }else{
        	$LastPrice = $this->entries[$id]['startprice'];
        }
        
        $offer 	= $_REQUEST["offer_1"].'.'.$_REQUEST["offer_2"];
        $offer 	= floatval($offer);
        $step 	= (count($this->GetAuctionBids($id))==0) ? 0 : floatval($this->entries[$id]['incr_step']);
        $dif 	= $offer - $LastPrice;
        
        
        $objFWUser = FWUser::getFWUserObject();
		if ($objFWUser->objUser->login()) {
			$UserID = $objFWUser->objUser->getId();
		}

        $teil_1	= $offer/$step;
        $teil_2 = $teil_1;
        settype($teil_2, 'int');
        
    	//if(($offer % $step) != 0 || $offer<$LastPrice+$step){
    	if($teil_1>$teil_2 || $offer<$LastPrice+$step){
    		$_SESSION["ADD_JS"] = 'alert("'.$_ARRAYLANG['TXT_AUCTION_LOOK_INCREASE_STEP'].'");';
    	}else{
    		if($objFWUser->objUser->login()){
    			
    			$objReslut = $objDatabase->Execute("select enddate from ".DBPREFIX."module_auction where id='".$id."'");
		        if ($objReslut !== false) {
					$Differenz		= $objReslut->fields['enddate']-time();
		        }
		        
		        if($Differenz<0){
					$_SESSION["ADD_JS"] = 'alert("'.$_ARRAYLANG['TXT_AUCTION_AUCTION_IS_CLOSED'].'");';
				}else{
    			
		    		$objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_auction_bids SET
		                                bid_auction='".$id."',
		                                bid_user='".$UserID."',
		                                bid_price='".$offer."',
		                                bid_time='".time()."',
		                                bid_ip='".$_SERVER['REMOTE_ADDR']."'");
		
		            if($objResult !== false){
		            	$_SESSION["ADD_JS"] = 'alert("'.$_ARRAYLANG['TXT_AUCTION_SAVE_OFFER_SUCCS'].'");';
		            }else{
		            	$_SESSION["ADD_JS"] = 'alert("'.$_ARRAYLANG['TXT_AUCTION_SAVE_OFFER_ERROR'].'");';
		            }
		            
				}
    		}else{
    			$_SESSION["ADD_JS"] = 'alert("'.$_ARRAYLANG['TXT_AUCTION_NOT_LOGED_ERROR'].'");';
    		}
    	}
    }
    
    function sendQuestion(){
    	global $objDatabase, $_ARRAYLANG, $_CORELANG;
    	
    	$id = $_REQUEST["id"];
    	$this->getEntries('', 'id', $id);
    	
    	$mail = new phpmailer();
    	
    	$objFWUser = FWUser::getFWUserObject();

    	$objOwner 			= FWUser::getFWUserObject();
        $objOwnerUser 		= $objOwner->objUser->getUser($this->entries[$id]['userid']);
        if ($objOwnerUser) {
            $TargetEmail = $objOwnerUser->getEmail();
        }
        
        $objFWUser 			= FWUser::getFWUserObject();
		if ($objFWUser->objUser->login()) {
			$FromEmail = $objFWUser->objUser->getEmail();
		}
		if($_REQUEST["Email"]!=''){
			$FromEmail = $_REQUEST["Email"];
		}
		$FromName 			= $_REQUEST["Name"].' (User: '.$objFWUser->objUser->getUsername().')';
		$mailHTML			= '';
		$Mailbody[1]		= str_replace('[USER]', $objOwnerUser->getUsername(), $_ARRAYLANG['TXT_AUCTION_QUESTION_BODY_1']);
		$Mailbody[2]		= str_replace('[AUCTION_NR]', $id, $_ARRAYLANG['TXT_AUCTION_QUESTION_BODY_2']);
		$Mailbody[2]		= str_replace('[AUCTION_TITLE]', $this->entries[$id]['title'], $Mailbody[2]);
		
		$mailHTML .= $Mailbody[1]."\n";
		$mailHTML .= $Mailbody[2]."\n\n";
		$mailHTML .= $_ARRAYLANG["TXT_AUCTION_QUESTION"].":\n";
		$mailHTML .= $_REQUEST["Frage"]."\n\n";
		$mailHTML .= $_ARRAYLANG["TXT_AUCTION_QUESTION_BODY_3"]."\n";
		$mailHTML .= "Name: ".$FromName."\n";
		$mailHTML .= "E-Mail: ".$FromEmail."\n\n";
		$mailHTML .= "-------------------------------\n";
		$mailHTML .= "-------------------------------\n";
		$mailHTML .= "Created: ".date("d.m.Y H:i")."\n";
        
		$mail->From 	= $FromEmail;
		$mail->FromName = $FromName;	
		$mail->Subject 	= $_ARRAYLANG["TXT_AUCTION_AUCTION_QUESTION"];
		$mail->Priority = 3;
		$mail->IsHTML(false); 
		$mail->Body 	= $mailHTML;
		$mail->AltBody 	= $mailHTML;
		$mail->AddAddress($TargetEmail);
		if($mail->Send()){
			$_SESSION["ADD_JS"] = 'alert("'.$_ARRAYLANG['TXT_AUCTION_QUESTION_ALERT'].'");';
		}
		unset($mail);
		
    }

    function entryDetails($id) {

        global $objDatabase, $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get erntry
        $this->getEntries('', 'id', $id);
        
        
        // EXE
        // --------------------------
        if(isset($_REQUEST["exe"])){
        	switch ($_REQUEST["exe"]) {
        		case "makeOffer":
        			$this->_makeOffer($id);
        			break;
        		case "sendQuestion":
        			$this->sendQuestion($id);
        			break;
        	
        	}
        }
        
        
        
        if (isset($id) && count($this->entries) != 0) {

            //get search
            $this->getSearch();

            //get navigatin
            $this->getNavigation($this->entries[$id]['catid']);

            $enddate = date("d.m.Y", $this->entries[$id]['enddate']);
            $info         = getimagesize($this->mediaPath.'pictures/'.$this->entries[$id]['picture']);
            $height     = '';
            $width         = '';

            if ($info[0] <= $info[1]) {
                if ($info[1] > 200) {
                    $faktor = $info[1]/200;
                    $height = 200;
                    $width    = $info[0]/$faktor;
                } else {
                    $height = $info[1];
                    $width = $info[0];
                }
            }else{
                $faktor = $info[0]/300;
                $result = $info[1]/$faktor;
                if ($result > 200) {
                    if ($info[1] > 200) {
                        $faktor = $info[1]/200;
                        $height = 200;
                        $width    = $info[0]/$faktor;
                    }else{
                        $height = $info[1];
                        $width = $info[0];
                    }
                }else{
                    if ($info[0] > 300) {
                        $width = 300;
                        $height = $info[1]/$faktor;
                    }else{
                        $width = $info[0];
                        $height = $info[1];
                    }
                }
            }

            $width != '' ? $width = 'width="'.round($width,0).'"' : $width = '';
            $height != '' ? $height = 'height="'.round($height,0).'"' : $height = '';

            $image = '<img src="'.$this->mediaWebPath.'pictures/'.$this->entries[$id]['picture'].'" '.$width.' '.$height.' border="0" alt="'.$this->entries[$id]['title'].'" />';

            $user         = $this->entries[$id]['name'].'<br /><br />';
            $userMail    = '<a href="mailto:'.$this->entries[$id]['email'].'">'.$this->entries[$id]['email'].'</a><br />';

            //user details
            $objFWUser = FWUser::getFWUserObject();
            $objUser = $objFWUser->objUser->getUser($this->entries[$id]['userid']);
            if ($objUser) {
                    $objUser->getProfileAttribute('address') != '' ? $street = $objUser->getProfileAttribute('address').'<br />' : $street = '';
                    $objUser->getProfileAttribute('phone_office') != '' ? $phone = $objUser->getProfileAttribute('phone_office').'<br />' : $phone = '';
                    $objUser->getProfileAttribute('phone_mobile') != '' ? $mobile = $objUser->getProfileAttribute('phone_mobile').'<br /><br />' : $mobile = '';
                    if ($objUser->getProfileAttribute('zip') != '' || $objUser->getProfileAttribute('zip') != 0) {
                        $zip = $objUser->getProfileAttribute('zip').' ';
                    } else {
                        $zip = '';
                    }
                    $objUser->getProfileAttribute('city') != '' ? $city = $objUser->getProfileAttribute('city').'<br />' : $city = '';
                    $objUser->getProfileAttribute('website') != '' ? $webpage = '<a href="http://:'.$objUser->getProfileAttribute('website').'" target="_blank">'.$objUser->getProfileAttribute('website').'</a><br />' : $webpage = '';

                $TXTuserDetails = $_ARRAYLANG['TXT_AUCTION_CONTACT'];
                $userDetails =     $user.$street.$zip.$city.$phone.$mobile.$userMail.$webpage;

                $residence = $objUser->getProfileAttribute('zip').' '.$objUser->getProfileAttribute('city');
            } else {
            	$TXTuserDetails 	= $_ARRAYLANG['TXT_AUCTION_CONTACT'];
                $userDetails 		= $user.$userMail;
            }

            if ($this->entries[$id]['userdetails'] != 1) {
                $userDetails = '';
                $TXTuserDetails = '';
            }

            //type
            if ($this->entries[$id]['type'] == "offer") {
                $type         = $_ARRAYLANG['TXT_AUCTION_OFFER'];
                $txtplace     = $_ARRAYLANG['TXT_AUCTION_PLACE'];
                $place         = $residence;
            }else{
                $type         = $_ARRAYLANG['TXT_AUCTION_SEARCH'];
                $txtplace     = '';
                $place         = '';
            }

            //spez fields
            $objResult = $objDatabase->Execute("SELECT id, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1'");
              if ($objResult !== false) {
                while(!$objResult->EOF) {
                    $spezFields[$objResult->fields['id']] = $objResult->fields['value'];
                    $objResult->MoveNext();
                }
              }

            //price
            if ($this->entries[$id]['price'] == 'forfree') {
                   $price = $_ARRAYLANG['TXT_AUCTION_FREE'];
               }elseif ($this->entries[$id]['price'] == 'agreement') {
                   $price = $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'];
               }else{
                   $price = $this->entries[$id]['price'].' '.$this->settings['currency'];
               }

               if ($this->settings['maxdayStatus'] == 1) {
                $enddate = $_ARRAYLANG['TXT_AUCTION_ADVERTISEMENT_ONLINE'].' '.$enddate;
            } else {
                $enddate = "";
            }
            
            // Auction TMBS
            // --------------------------------------------
            $auction_tmbs = '';
            for($x=1;$x<6;$x++){
	            if($this->entries[$id]['picture_'.$x] != ''){
	            	if (file_exists(ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$this->entries[$id]['picture_'.$x])) {
	            		$auction_tmbs .= '<div class="auction_tmb"><a href="'.ASCMS_AUCTION_UPLOAD_WEB_PATH.'/'.$this->entries[$id]['picture_'.$x].'" class="lightview" rel="gallery[auctionimages_'.$id.']"><img src="'.ASCMS_AUCTION_UPLOAD_WEB_PATH.'/tmb_'.$this->entries[$id]['picture_'.$x].'" border="0" alt="" /></a></div>';
	            	}else{
	            		$auction_tmbs .= '<div class="auction_tmb"><a href="'.ASCMS_AUCTION_UPLOAD_WEB_PATH.'/'.$this->entries[$id]['picture_'.$x].'" class="lightview" rel="gallery[auctionimages_'.$id.']"><img src="'.ASCMS_AUCTION_UPLOAD_WEB_PATH.'/no_img.gif" border="0" alt="" /></a></div>';
	            	}
	            }
            }
            
            $Bids = $this->GetAuctionBids($id);
            
            if(count($Bids)>0){
            	$LastPrice = $Bids[count($Bids)-1]["bid_price"];
            }else{
            	$LastPrice = $this->entries[$id]['startprice'];
            }
            
            // Offers
            // ---------------------------------------------
            $this->_objTpl->setCurrentBlock('AllOffers');
            for($x=count($Bids)-1;$x>-1; $x--){
            	
            	$icon = ($x==count($Bids)-1) ? 'icon_ok.gif' : 'icon_no.gif';
            	
            	$objFWUserAuction = FWUser::getFWUserObject();
				$objUserAuction = $objFWUserAuction->objUser->getUser($Bids[$x]['bid_user']);
				
            	$this->_objTpl->setVariable(array(
                	'AUCTION_OFFERS_ICON'		=> ASCMS_AUCTION_WEB_PATH.'/'.$icon,
                	'AUCTION_BID'				=> number_format($Bids[$x]['bid_price'], 2,',', '.'),
                	'AUCTION_BID_MAKED'			=> date('d.m.Y H:i', $Bids[$x]['bid_time']),
                	'AUCTION_USENAME'			=> $objUserAuction->getUsername(),
                ));
                $this->_objTpl->parse('AllOffers');
            }
            
            
            $display_auction_login 	= ($objFWUser->objUser->login()) ? 'none' : 'block';
            $loged					= ($objFWUser->objUser->login()) ? 1 : 0;
            
            $_SESSION["ADD_JS"]		= (isset($_SESSION["ADD_JS"])) ? $_SESSION["ADD_JS"]: '';

            $nextBid				= number_format($LastPrice+$this->entries[$id]['incr_step'],2, ',', '.').' '.$this->settings['currency'];
            if(count($this->GetAuctionBids($id))==0){
            	$nextBid = number_format($LastPrice,2, ',', '.').' '.$this->settings['currency'];
            }
            
            $yourOffer_1			= number_format($LastPrice+$this->entries[$id]['incr_step'],0,'.','');
            $yourOffer_2			= substr(strrchr (number_format($LastPrice+$this->entries[$id]['incr_step'],2), "."), 1);
            
            if(count($this->GetAuctionBids($id))==0){
            	$yourOffer_1 = number_format($LastPrice,0,'.','');
            	$yourOffer_2 = substr(strrchr (number_format($LastPrice,2), "."), 1);
            }
            
            // set variables
            $this->_objTpl->setVariable(array(
                'AUCTION_TITLE'                    => $this->entries[$id]['title'],
                'AUCTION_ID'                        => $id,
                'AUCTION_EDIT'                    => '<a href="?section=auction&amp;cmd=edit&amp;id='.$id.'">'.$_ARRAYLANG['TXT_EDIT_ADVERTISEMENT'].'</a>',
                'AUCTION_DEL'                    => '<a href="?section=auction&amp;cmd=del&amp;id='.$id.'">'.$_ARRAYLANG['TXT_AUCTION_DELETE_ADVERTISEMENT'].'</a>',
                'AUCTION_TYPE'                    => $type,
                'AUCTION_PICTURE'                => $image,
                'AUCTION_USER_DETAILS'             => $userDetails,
                'TXT_AUCTION_USER_DETAILS'         => $TXTuserDetails,
                'AUCTION_DESCRIPTION'             => $this->entries[$id]['description'],
                'TXT_AUCTION_PLACE'                 => $txtplace,
                'AUCTION_PLACE'                     => $place,
                'TXT_AUCTION_PRICE'                 => $_ARRAYLANG['TXT_AUCTION_PRICE'],
                'AUCTION_PRICE'                     => $price,
                'TXT_AUCTION_MESSAGE'             => $_ARRAYLANG['TXT_AUCTION_SEND_MESSAGE'],
                'TXT_AUCTION_TITLE'                 => $_ARRAYLANG['TXT_AUCTION_TITLE'],
                'TXT_AUCTION_MSG_TITLE'             => $_ARRAYLANG['TXT_MARKTE_MESSAGE_ABOUT'].' ',
                'TXT_AUCTION_MSG'                 => $_ARRAYLANG['TXT_AUCTION_MESSAGE'],
                'TXT_AUCTION_SEND'                 => $_ARRAYLANG['TXT_AUCTION_SEND'],
                'AUCTION_ENDDATE'                 => $enddate,
                'TXT_FIELDS_REQUIRED'            => $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS'],
                'TXT_THOSE_FIELDS_ARE_EMPTY'    => $_ARRAYLANG['TXT_AUCTION_FIELDS_NOT_CORRECT'],
                'TXT_AUCTION_NAME'                 => $_CORELANG['TXT_NAME'],
                'TXT_AUCTION_EMAIL'                 => $_CORELANG['TXT_EMAIL'],
                'TXT_AUCTION_PRICE_MSG'             => $_ARRAYLANG['TXT_AUCTION_PRICE_IS'],
                'TXT_AUCTION_NEW_PRICE'             => $_ARRAYLANG['TXT_PRICE_EXPECTATION'],
                'TXT_AUCTION_SPEZ_FIELD_1'        => $spezFields[1],
                'TXT_AUCTION_SPEZ_FIELD_2'        => $spezFields[2],
                'TXT_AUCTION_SPEZ_FIELD_3'        => $spezFields[3],
                'TXT_AUCTION_SPEZ_FIELD_4'        => $spezFields[4],
                'TXT_AUCTION_SPEZ_FIELD_5'        => $spezFields[5],
                'AUCTION_SPEZ_FIELD_1'            => $this->entries[$id]['spez_field_1'],
                'AUCTION_SPEZ_FIELD_2'            => $this->entries[$id]['spez_field_2'],
                'AUCTION_SPEZ_FIELD_3'            => $this->entries[$id]['spez_field_3'],
                'AUCTION_SPEZ_FIELD_4'            => $this->entries[$id]['spez_field_4'],
                'AUCTION_SPEZ_FIELD_5'            => $this->entries[$id]['spez_field_5'],
                'TXT_AUCTION_ID' 					=> $_ARRAYLANG['TXT_AUCTION_ID'],
                'TXT_AUCTION_ENDTIME' 				=> $_ARRAYLANG['TXT_AUCTION_ENDTIME'],
                'TXT_AUCTION_NR_BIDS' 				=> $_ARRAYLANG['TXT_AUCTION_NR_BIDS'],
                'TXT_AUCTION_SEARCH_TITLE' 			=> $_ARRAYLANG['TXT_AUCTION_SEARCH_TITLE'],
                'AUCTION_ENDTIME'					=> date("d.m.Y H:i", $this->entries[$id]['enddate']),
                'AUCTION_NR_BIDS'					=> count($this->GetAuctionBids($id)),
                'AUCTION_TMBS'						=> $auction_tmbs,
                'TXT_AUCTION_STARTPRICE' 			=> $_ARRAYLANG['TXT_AUCTION_STARTPRICE'],
                'TXT_AUCTION_INCREASE_STEP' 		=> $_ARRAYLANG['TXT_AUCTION_INCREASE_STEP'],
                'TXT_AUCTION_NEXT_BID' 				=> $_ARRAYLANG['TXT_AUCTION_NEXT_BID'],
                'AUCTION_STARTPRICE' 				=> number_format($this->entries[$id]['startprice'], 2,',', '.').' '.$this->settings['currency'],
                'AUCTION_INCREASE_STEP' 			=> number_format($this->entries[$id]['incr_step'], 2,',', '.').' '.$this->settings['currency'],
                'AUCTION_NEXT_BID' 					=> $nextBid,
                'TXT_AUCTION_YOUR_OFFER' 			=> $_ARRAYLANG['TXT_AUCTION_YOUR_OFFER'],
                'CURRENCY' 							=> $this->settings['currency'],
                'YOUR_OFFER_1' 						=> $yourOffer_1,
                'YOUR_OFFER_2'						=> $yourOffer_2,
                'TXT_AUCTION_ADD_OFFER' 			=> $_ARRAYLANG['TXT_AUCTION_ADD_OFFER'],
                'TXT_AUCTION_DESCRIPTION' 			=> $_ARRAYLANG['TXT_AUCTION_DESCRIPTION'],
                'AUCTION_DESCRIPTION' 				=> $this->entries[$id]['description'],
                'TXT_AUCTION_INFORMATIONEN' 		=> $_ARRAYLANG['TXT_AUCTION_INFORMATIONEN'],
                'TXT_AUCTION_SHIPPING' 				=> $_ARRAYLANG['TXT_AUCTION_SHIPPING'],
                'TXT_AUCTION_PAYMENT' 				=> $_ARRAYLANG['TXT_AUCTION_PAYMENT'],
                'AUCTION_SHIPPING' 					=> $this->entries[$id]['shipping'],
                'AUCTION_PAYMENT' 					=> $this->entries[$id]['payment'],
                'TXT_AUCTION_OFFERS' 				=> $_ARRAYLANG['TXT_AUCTION_OFFERS'],
                'AUCTION_OFFERS' 					=> 'auction offers - contrexx_module_auction:contrexx_module_auction_bids id:'.$id,
                'TXT_AUCTION_QUESTIONS'				=> $_ARRAYLANG['TXT_AUCTION_QUESTIONS'],
                'AUCTION_QUESTIONS'					=> 'auction quest - form output id:'.$id,
                'TXT_AUCTION_DURATION'                => $_ARRAYLANG["TXT_AUCTION_DURATION"],
                'TXT_AUCTION_USENAME'                => $_ARRAYLANG["TXT_AUCTION_USENAME"],
                'TXT_AUCTION_BID'                => $_ARRAYLANG["TXT_AUCTION_BID"],
                'TXT_AUCTION_BID_MAKED'                => $_ARRAYLANG["TXT_AUCTION_BID_MAKED"],
                'TXT_AUCTION_NAME'                => $_ARRAYLANG["TXT_AUCTION_NAME"],
                'TXT_AUCTION_EMAIL'                => $_ARRAYLANG["TXT_AUCTION_EMAIL"],
                'TXT_AUCTION_QUESTION'                => $_ARRAYLANG["TXT_AUCTION_QUESTION"],
                'TXT_AUCTION_PASSWORD'                => $_ARRAYLANG["TXT_AUCTION_PASSWORD"],
                'TXT_LOGIN'                => $_ARRAYLANG["TXT_AUCTION_LOGIN"],
                'DISPLAY_LOGIN'                => $display_auction_login,
                'REDIRECT_URL'					=> base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']),
                'LOGED'							=> $loged,
                'TXT_AUCTION_NOT_LOGED_ERROR'	=> $_ARRAYLANG['TXT_AUCTION_NOT_LOGED_ERROR'],
                'TXT_AUCTION_URL'				=> $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'],
                'TXT_AUCTION_ADD_ALL_FIELDS'				=> $_ARRAYLANG['TXT_AUCTION_ADD_ALL_FIELDS'],
                'TXT_AUCTION_SEND_QUESTION'				=> $_ARRAYLANG['TXT_AUCTION_SEND_QUESTION'],
                'ADD_JS'						=> $_SESSION["ADD_JS"],
                
            ));
            
            $_SESSION["ADD_JS"] = '';
            
        }else{
            CSRF::header('Location: ?section=auction');
        }
    }
    
    function sendMessage($id) {

        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get erntry
        $this->getEntries('', 'id', $id);

        if (isset($id) && count($this->entries) != 0) {
            //get search
            $this->getSearch();

            //get navigatin
            $this->getNavigation($this->entries[$id]['catid']);

            if ($_POST['title'] != '' && $_POST['message'] != '') {
                //create mail
                $sendTo        = $this->entries[$id]['email'];
                $fromName    = $_POST['name'];
                $fromMail    = $_POST['email'];
                $subject     = $_POST['title'];
                $newPrice     = $_POST['newprice']!='' ? "\n\n".$_ARRAYLANG['TXT_PRICE_EXPECTATION']."\n".$_POST['newprice'] : '';
                $oldPrice     = $_POST['price']!='' ? "\n\n".$_ARRAYLANG['TXT_AUCTION_MESSAGE_PRICE']."\n".$_POST['price'] : '';
                $message     = $_POST['message'].$oldPrice.$newPrice;

                if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
                    $objMail = new phpmailer();

                    if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
                        if (($arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
                            $objMail->IsSMTP();
                            $objMail->Host = $arrSmtp['hostname'];
                            $objMail->Port = $arrSmtp['port'];
                            $objMail->SMTPAuth = true;
                            $objMail->Username = $arrSmtp['username'];
                            $objMail->Password = $arrSmtp['password'];
                        }
                    }

                    $objMail->CharSet = CONTREXX_CHARSET;
                    $objMail->From = $fromMail;
                    $objMail->FromName = $fromName;
                    $objMail->AddReplyTo($fromMail);
                    $objMail->Subject = $subject;
                    $objMail->IsHTML(false);
                    $objMail->Body = $message;
                    $objMail->AddAddress($sendTo);
                    $objMail->Send();
                }

                // set variables
                $this->_objTpl->setVariable(array(
                    'AUCTION_TITLE'                    => $_ARRAYLANG['TXT_AUCTION_MESSAGE_SUCCESS_TITLE'],
                    'AUCTION_MSG_SEND'                => $_ARRAYLANG['TXT_AUCTION_MESSAGE_SUCCESS_BODY'],
                    'TXT_AUCTION_BACK'                => $_CORELANG['TXT_BACK'],
                ));
            }
        }else{
            CSRF::header('Location: ?section=auction');
        }
    }


    function checkEnddate()
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        $objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction SET status = 0 WHERE enddate < '.$today.'');
    }


    function addEntry()
    {
        global $objDatabase, $_CORELANG, $_ARRAYLANG, $_CONFIG;


        if (!$this->settings['addEntry'] == '1' || (!$this->communityModul && $this->settings['addEntry_only_community'] == '1')) {
            CSRF::header('Location: index.php?section=auction');
            exit;
        }elseif ($this->settings['addEntry_only_community'] == '1') {
            $objFWUser = FWUser::getFWUserObject();
            if (!$objFWUser->objUser->login()) {
                $link = base64_encode(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
                CSRF::header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=login&redirect=".$link);
                exit;
            }
        } else {
            $objFWUser = FWUser::getFWUserObject();
        }

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get search
        $this->getSearch();

        //get navigatin
        $this->getNavigation('');

        $this->getCategories();
        $categories = '';
        foreach(array_keys($this->categories) as $catId) {
            $categories .= '<option value="'.$catId.'">'.$this->categories[$catId]['name'].'</option>';
        }

        $daysOnline = '';
        for($x = $this->settings['maxday']; $x >= 1; $x--) {
            $daysOnline .= '<option value="'.$x.'">'.$x.'</option>';
        }

        //get premium
        $objReslut = $objDatabase->Execute("SELECT price_premium FROM ".DBPREFIX."module_auction_paypal WHERE id = '1'");
          if ($objReslut !== false) {
            while(!$objReslut->EOF) {
                $premium     = $objReslut->fields['price_premium'];
                $objReslut->MoveNext();
            }
          }

          if ($premium == '' || $premium == '0.00' || $premium == '0') {
              $premium = '';
          }else{
              $premium = $_ARRAYLANG['TXT_AUCTION_ADDITIONAL_FEE'].$premium.' '.$_ARRAYLANG['TXT_AUCTION_CURRENCY'];
          }

          if ($this->settings['maxdayStatus'] == 1) {
            $daysOnline = '';
            for($x = $this->settings['maxday']; $x >= 1; $x--) {
                $daysOnline .= '<option value="'.$x.'">'.$x.'</option>';
            }

            $daysJS = 'if (days.value == "") {
                            errorMsg = errorMsg + "- '.$_ARRAYLANG['TXT_AUCTION_DURATION'].'\n";
                       }
                       ';
        }
        
        // Auktionsende
        // --------------------------------------------
        $Options 				= array();
        $AuctionEnd				= array();
        $AuctionEndTimestamp	= $objResult->fields['enddate'];
        $AuctionEnd['year']		= date('Y', $AuctionEndTimestamp);
        $AuctionEnd['month']	= date('m', $AuctionEndTimestamp);
        $AuctionEnd['day']		= date('d', $AuctionEndTimestamp);
        $AuctionEnd['hour']		= date('H', $AuctionEndTimestamp);
        $AuctionEnd['minutes']	= date('i', $AuctionEndTimestamp);
        
        for($x=date("Y"); $x<=date("Y")+5; $x++){
        	$SelectedText = ($x==date("Y")) ? 'selected' : '';
        	$Options['auctionend']['year'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }
        
        for($x=1; $x<=12; $x++){
        	$SelectedText = ($x==intval(date("m"))) ? 'selected' : '';
        	$Options['auctionend']['month'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }
        
        for($x=1; $x<=31; $x++){
        	$SelectedText = ($x==intval(date("d"))) ? 'selected' : '';
        	$Options['auctionend']['day'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }
        
        for($x=1; $x<=23; $x++){
        	$SelectedText = ($x==intval(date("H"))) ? 'selected' : '';
        	$Options['auctionend']['hour'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }
        
        for($x=0; $x<=59; $x++){
        	$SelectedText = ($x==intval(date("i"))) ? 'selected' : '';
        	$Options['auctionend']['minutes'] .= '<option value="'.$x.'" '.$SelectedText.'>'.$x.'</option>';
        }

        $this->_objTpl->setVariable(array(
            'TXT_AUCTION_NAME'                        =>    $_CORELANG['TXT_NAME'],
            'TXT_AUCTION_EMAIL'                        =>    $_CORELANG['TXT_EMAIL'],
            'TXT_AUCTION_TITLE_ENTRY'                =>    $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_AUCTION_DESCRIPTION'                =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_AUCTION_SAVE'                        =>    $_CORELANG['TXT_ADD'],
            'TXT_AUCTION_FIELDS_REQUIRED'            =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS'],
            'TXT_AUCTION_THOSE_FIELDS_ARE_EMPTY'        =>    $_ARRAYLANG['TXT_AUCTION_FIELDS_NOT_CORRECT'],
            'TXT_AUCTION_PICTURE'                    =>    $_ARRAYLANG['TXT_AUCTION_IMAGE'],
            'TXT_AUCTION_CATEGORIE'                    =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY'],
            'TXT_AUCTION_PRICE'                        =>    $_ARRAYLANG['TXT_AUCTION_PRICE'].' '.$this->settings['currency'],
            'TXT_AUCTION_TYPE'                        =>    $_CORELANG['TXT_TYPE'],
            'TXT_AUCTION_OFFER'                        =>    $_ARRAYLANG['TXT_AUCTION_OFFER'],
            'TXT_AUCTION_SEARCH'                        =>    $_ARRAYLANG['TXT_AUCTION_SEARCH'],
            'TXT_AUCTION_FOR_FREE'                    =>    $_ARRAYLANG['TXT_AUCTION_FREE'],
            'TXT_AUCTION_AGREEMENT'                    =>    $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'],
            'TXT_AUCTION_END_DATE'                    =>    $_ARRAYLANG['TXT_AUCTION_DURATION'],
            'END_DATE_JS'                            =>    $daysJS,
            'TXT_AUCTION_ADDED_BY'                    =>    $_ARRAYLANG['TXT_AUCTION_ADDEDBY'],
            'TXT_AUCTION_USER_DETAIL'                =>    $_ARRAYLANG['TXT_AUCTION_USERDETAILS'],
            'TXT_AUCTION_DETAIL_SHOW'                =>    $_ARRAYLANG['TXT_AUCTION_SHOW_IN_ADVERTISEMENT'],
            'TXT_AUCTION_DETAIL_HIDE'                =>    $_ARRAYLANG['TXT_AUCTION_NO_SHOW_IN_ADVERTISEMENT'],
            'TXT_AUCTION_PREMIUM'                    =>    $_ARRAYLANG['TXT_AUCTION_MARK_ADVERTISEMENT'],
            'TXT_AUCTION_DAYS'                        =>    $_ARRAYLANG['TXT_AUCTION_DAYS'],
            'ENTRY_END_YEAR'				=> $Options['auctionend']['year'],
            'ENTRY_END_MONTH'				=> $Options['auctionend']['month'],
            'ENTRY_END_DAY'					=> $Options['auctionend']['day'],
            'ENTRY_END_HOUR'				=> $Options['auctionend']['hour'],
            'ENTRY_END_MINUTES'				=> $Options['auctionend']['minutes'],
        ));

//        if ($this->settings['maxdayStatus'] != 1) {
//            $this->_objTpl->hideBlock('end_date_dropdown');
//        }

        $objReslut = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1' AND active='1' ORDER BY id DESC");
        if ($objReslut !== false) {
            $i = 0;
            while(!$objReslut->EOF) {
                $this->_objTpl->setCurrentBlock('spez_fields');

// TODO: Never used
//                ($i % 2)? $class = "row2" : $class = "row1";
                $input = '<input type="text" name="spez_'.$objReslut->fields['id'].'" style="width: 300px;" maxlength="100">';

                // initialize variables
                $this->_objTpl->setVariable(array(
                    'TXT_AUCTION_SPEZ_FIELD_NAME'    => $objReslut->fields['value'],
                    'AUCTION_SPEZ_FIELD_INPUT'          => $input,
                ));

                $this->_objTpl->parse('spez_fields');
                $i++;
                $objReslut->MoveNext();
            }
          }

        $this->_objTpl->setVariable(array(
            'TXT_AUCTION_PREMIUM_CONDITIONS'            =>    $premium,
            'AUCTION_CATEGORIES'                        =>    $categories,
            'AUCTION_ENTRY_ADDEDBY'                    =>    htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
            'AUCTION_ENTRY_USERDETAILS_ON'            =>    "checked",
            'AUCTION_ENTRY_TYPE_OFFER'                =>    "checked",
            'AUCTION_DAYS_ONLINE'                    =>    $daysOnline,
        ));
    }



    function confirmEntry() {

        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get search
        $this->getSearch();

        //get navigatin
        $this->getNavigation($this->entries[$id]['catid']);

        if (isset($_POST['submitEntry']) || isset($_POST['submit'])) {

            if (isset($_POST['submitEntry'])) {
                //$this->insertEntry('0');
                $this->insertAuction(0);
                $id = $objDatabase->Insert_ID();
            }
            
            $this->getNavigation($this->entries[$id]['catid']);

            if (isset($_POST['submit'])) {
                if(!isset($id)){
                	$id = contrexx_addslashes($_POST['id']);
                }
                $regkey     = contrexx_addslashes($_POST['regkey']);

                $objResult = $objDatabase->Execute("SELECT id, regkey, userid FROM ".DBPREFIX."module_auction WHERE id = '".$id."' AND regkey='".$regkey."'");
                if ($objResult !== false) {
                    $count = $objResult->RecordCount();
                    while(!$objResult->EOF) {
                        $today = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
                        $objResultUpdate = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET status='1', regkey='', regdate='".$today."' WHERE id='".$objResult->fields['id']."'");

                        $this->sendMail($id);

                        if ($objResultUpdate !== false) {
                            CSRF::header('Location: ?section=auction&cmd=detail&id='.$objResult->fields['id'].'');
                        }

                        $objResult->MoveNext();
                    }
                 }

                 if ($count == 0) {
                     $error = $_ARRAYLANG['TXT_AUCTION_CLEARING_CODE_NOT_EXISTING'];
                 }
            }

            //get paypal
//            $objResult = $objDatabase->Execute("SELECT value FROM ".DBPREFIX."module_auction_settings WHERE id = '11'");
//              if ($objResult !== false) {
//                while(!$objResult->EOF) {
//                    $codeMode         = $objResult->fields['value'];
//                    $objResult->MoveNext();
//                }
//              }
              
              $codeMode = '0';

              if ($codeMode == '0') {
                  $this->_objTpl->touchBlock('infoText');
                  $this->_objTpl->hideBlock('codeForm');
              }else{
                  $confirmForm    = '<form action="index.php?section=auction&cmd=confirm" method="post" name="auctionSearch" id="auctionAGB">
                                   <input type="hidden" name="id" value="'.$id.'" >
                                   <input id="regkey" name="regkey" value="" size="25" maxlength="100" />&nbsp;<input id="submit" type="submit" value="Freischalten" name="submit" />
                                   </form>';

                  $this->_objTpl->parse('codeForm');
                  $this->_objTpl->hideBlock('infoText');
              }

            // set variables
            $this->_objTpl->setVariable(array(
                'TXT_AUCTION_TITLE'            => $_ARRAYLANG['TXT_AUCTION_REQUIREMENTS'],
                'TXT_AUCTION_AGB'            => $_ARRAYLANG['TXT_AUCTION_AGB'],
                'TXT_AUCTION_CONFIRM'        => $_ARRAYLANG['TXT_AUCTION_AGB_ACCEPT'],
                'AUCTION_ERROR_CONFIRM'        => $error,
                'AUCTION_FORM'                => $confirmForm,
            ));

        }else{
            CSRF::header('Location: ?section=auction&cmd=add');
        }
    }



    function searchEntry() {

        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        //get search
        $this->getSearch();

        //get navigatin
        $this->getNavigation('');

        //spez fields
        $objResult = $objDatabase->Execute("SELECT id, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1'");
          if ($objResult !== false) {
            while(!$objResult->EOF) {
                $spezFields[$objResult->fields['id']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
          }

        // set variables
        $this->_objTpl->setVariable(array(
// TODO: $paging is never set!
//            'AUCTION_SEARCH_PAGING'            => $paging,
            'TXT_AUCTION_ENDDATE'            => $_CORELANG['TXT_END_DATE'],
            'TXT_AUCTION_TITLE'                => $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_AUCTION_PRICE'                => $_ARRAYLANG['TXT_AUCTION_PRICE'],
            'TXT_AUCTION_CITY'                => $_ARRAYLANG['TXT_AUCTION_CITY'],
            'TXT_AUCTION_SPEZ_FIELD_1'        => $spezFields[1],
            'TXT_AUCTION_SPEZ_FIELD_2'        => $spezFields[2],
            'TXT_AUCTION_SPEZ_FIELD_3'        => $spezFields[3],
            'TXT_AUCTION_SPEZ_FIELD_4'        => $spezFields[4],
            'TXT_AUCTION_SPEZ_FIELD_5'        => $spezFields[5],
            'TXT_AUCTION_BIDS'                => $_ARRAYLANG["TXT_AUCTION_BIDS"],
            'TXT_AUCTION_OFFER_END'                => $_ARRAYLANG["TXT_AUCTION_OFFER_END"],
            'TXT_AUCTION_DURATION'                => $_ARRAYLANG["TXT_AUCTION_DURATION"],
        ));

// TODO: Never used
//        $today                 = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        $searchTermOrg         = contrexx_addslashes($_GET['term']);
        $searchTerm         = contrexx_addslashes($_GET['term']);
        $array = explode(' ', $searchTerm);
        for($x = 0; $x < count($array); $x++) {
            $tmpTerm .= $array[$x].'%';
        }

        $searchTerm    = substr($tmpTerm, 0, -1);
        $searchTermExp = "&amp;check=norm&amp;term=".$searchTermOrg;

        if ($_GET['check'] == 'exp') {

            $searchTermExp = "&amp;check=exp&amp;term=".$searchTermOrg;

            if ($_GET['catid'] != '') {
                $query_search         .="AND catid LIKE ('%".$_GET['catid']."%') ";
                $searchTermExp        .= "&amp;catid=".$_GET['catid'];
            }
            if ($_GET['type'] != '') {
                $query_search         .="AND type LIKE ('%".$_GET['type']."%') ";
                $searchTermExp        .= "&amp;type=".$_GET['type'];
            }

            if ($_GET['price'] != '') {
                $query_search         .="AND price <= ".$_GET['price']." ";
                $searchTermExp        .= "&amp;price=".$_GET['price'];
            }
        }

        if ($_GET['term'] != '') {
            $query="SELECT  *, id,
                            title,
                            description,
                            price,
                            picture_1,
                            userid,
                            enddate,
                            spez_field_1,
                            spez_field_2,
                            spez_field_3,
                            spez_field_4,
                            spez_field_5,
                      MATCH (title,description) AGAINST ('%$searchTerm%') AS score
                       FROM ".DBPREFIX."module_auction
                      WHERE (title LIKE ('%$searchTerm%')
                              OR description LIKE ('%$searchTerm%')
                              OR spez_field_1 LIKE ('%$searchTerm%')
                              OR spez_field_2 LIKE ('%$searchTerm%')
                              OR spez_field_3 LIKE ('%$searchTerm%')
                              OR spez_field_4 LIKE ('%$searchTerm%')
                              OR spez_field_5 LIKE ('%$searchTerm%'))
                         ".$query_search."
                        AND status = '1'
                   ORDER BY score DESC, enddate DESC";

            /////// START PAGING ///////
            $pos= intval($_GET['pos']);
            $objResult = $objDatabase->Execute($query);
            $count = $objResult->RecordCount();
            if ($count > $this->settings['paging']) {
                $paging = getPaging($count, $pos, "&amp;section=auction&amp;cmd=search".$searchTermExp, "<b>Auktionen</b>", true, $this->settings['paging']);
            }
            $this->_objTpl->setVariable('SEARCH_PAGING', $paging);
            $objResult = $objDatabase->SelectLimit($query, $this->settings['paging'], $pos);
            /////// END PAGING ///////

            if ($objResult !== false) {
                $i = 0;
                while (!$objResult->EOF) {
                    if (empty($objResult->fields['picture'])) {
                        $objResult->fields['picture'] = 'no_picture.gif';
                    }

                    $info         = getimagesize($this->mediaPath.'pictures/'.$objResult->fields['picture']);
                    $height     = '';
                    $width         = '';

                       if ($info[0] <= $info[1]) {
                        if ($info[1] > 50) {
                            $faktor = $info[1]/50;
                            $height = 50;
                            $width    = $info[0]/$faktor;
                        } else {
                            $height = $info[1];
                            $width = $info[0];
                        }
                    }else{
                        $faktor = $info[0]/80;
                        $result = $info[1]/$faktor;
                        if ($result > 50) {
                            if ($info[1] > 50) {
                                $faktor = $info[1]/50;
                                $height = 50;
                                $width    = $info[0]/$faktor;
                            }else{
                                $height = $info[1];
                                $width = $info[0];
                            }
                        }else{
                            if ($info[0] > 80) {
                                $width = 80;
                                $height = $info[1]/$faktor;
                            }else{
                                $width = $info[0];
                                $height = $info[1];
                            }
                        }
                    }

                    $width != '' ? $width = 'width="'.round($width,0).'"' : $width = '';
                    $height != '' ? $height = 'height="'.round($height,0).'"' : $height = '';

                    $image = '<img src="'.$this->mediaWebPath.'pictures/'.$objResult->fields['picture'].'" '.$width.'" '.$height.'" border="0" alt="'.$objResult->fields['title'].'" />';


                    $objFWUser = FWUser::getFWUserObject();
                    $objUser = $objFWUser->objUser->getUser($objResult->fields['userid']);
                    if ($objUser) {
                        $city = $objUser->getProfileAttribute('city');
                    }

                       if ($objResult->fields['premium'] == 1) {
                           $row = "auctionRow1";
                       }else{
                           $row = $i%2==0 ? "auctionRow2" : "auctionRow3";
                       }

                       $enddate = date("d.m.Y", $objResult->fields['enddate']);

                       if ($objResult->fields['price'] == 'forfree') {
                           $price = $_ARRAYLANG['TXT_AUCTION_FREE'];
                       }elseif ($objResult->fields['price'] == 'agreement') {
                           $price = $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'];
                       }else{
                           $price = $objResult->fields['price'].' '.$this->settings['currency'];
                       }
                       
                       $Bids = $this->GetAuctionBids($objResult->fields['id']);
            
			            if(count($Bids)>0){
			            	$LastPrice = $Bids[count($Bids)-1]["bid_price"];
			            }else{
			            	$LastPrice = $objResult->fields['startprice'];
			            }
		            
		             $price = $LastPrice.' '.$this->settings['currency'];
		             
		             // auction pictures tmb
	                   // ---------------------------------
	                   $auction_tmb 	= ASCMS_AUCTION_WEB_PATH.'/no_img.gif';
	                   $auction_tmbx	= 0;
	                   for($z=1; $z<6; $z++){
		                	if($objResult->fields['picture_'.$z]!='' && $auction_tmbx==0){
		                		$auction_tmb = ASCMS_AUCTION_WEB_PATH.'/tmb_'.$objResult->fields['picture_'.$z];
		                		$auction_tmbx = 1;
		                	}
		                }

                       $this->_objTpl->setVariable(array(
                        'AUCTION_ENDDATE'                => $enddate,
                        'AUCTION_TITLE'                    => $objResult->fields['title'],
                        'AUCTION_DESCRIPTION'            => substr($objResult->fields['description'], 0, 110)."<a href='index.php?section=auction&cmd=detail&id=".$objResult->fields['id']."' target='_self'>[...]</a>",
                        'AUCTION_PRICE'                    => $price,
                        'AUCTION_PICTURE'                => $image,
                        'AUCTION_ROW'                    => $row,
                        'AUCTION_DETAIL'                    => "index.php?section=auction&cmd=detail&id=".$objResult->fields['id'],
                        'AUCTION_ID'                        => $objResult->fields['id'],
                        'AUCTION_CITY'                    => $city,
                        'AUCTION_SPEZ_FIELD_1'            => $objResult->fields['spez_field_1'],
                        'AUCTION_SPEZ_FIELD_2'            => $objResult->fields['spez_field_2'],
                        'AUCTION_SPEZ_FIELD_3'            => $objResult->fields['spez_field_3'],
                        'AUCTION_SPEZ_FIELD_4'            => $objResult->fields['spez_field_4'],
                        'AUCTION_SPEZ_FIELD_5'            => $objResult->fields['spez_field_5'],
                        'AUCTION_TMB'            => $auction_tmb,
                    ));

                    $this->_objTpl->parse('showEntries');
                    $objResult->MoveNext();
                    $i++;
                   }

               }

               if ($count <= 0) {
                $this->_objTpl->setVariable(array(
                    'AUCTION_NO_ENTRIES_FOUND'            => $_ARRAYLANG['TXT_AUCTION_NO_ENTRIES_FOUND'],
                ));

                $this->_objTpl->parse('noEntries');
                $this->_objTpl->hideBlock('showEntries');
            }

        }else{
            $this->_objTpl->setVariable(array(
                'AUCTION_NO_ENTRIES_FOUND'            => $_ARRAYLANG['TXT_AUCTION_SEARCH_INSERT'],
            ));

            $this->_objTpl->parse('noEntries');
            $this->_objTpl->hideBlock('showEntries');
            $this->_objTpl->hideBlock('showEntriesHeader');
        }

           $this->_objTpl->setVariable(array(
            'TXT_AUCTION_SEARCHTERM'            => $searchTermOrg,
        ));

        $this->_objTpl->parse('showEntriesHeader');
    }


    function editEntry() {

        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        if (!$this->settings['editEntry'] == '1' || (!$this->communityModul && $this->settings['addEntry_only_community'] == '1')) {
            CSRF::header('Location: index.php?section=auction&cmd=detail&id='.$_POST['id']);
            exit;
        }elseif ($this->settings['addEntry_only_community'] == '1') {
            $objFWUser = FWUser::getFWUserObject();
            if (!$objFWUser->objUser->login()) {
                $link = base64_encode(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
                CSRF::header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=login&redirect=".$link);
                exit;
            }
        } else {
            $objFWUser = FWUser::getFWUserObject();
        }

        //get search
        $this->getSearch();

        $this->_objTpl->setVariable(array(
            'TXT_AUCTION_TITLE'                        =>    $_ARRAYLANG['TXT_EDIT_ADVERTISEMENT'],
            'TXT_AUCTION_TITLE_ENTRY'                =>    $_ARRAYLANG['TXT_AUCTION_TITLE'],
            'TXT_AUCTION_NAME'                        =>    $_CORELANG['TXT_NAME'],
            'TXT_AUCTION_EMAIL'                        =>    $_CORELANG['TXT_EMAIL'],
            'TXT_AUCTION_DESCRIPTION'                =>    $_CORELANG['TXT_DESCRIPTION'],
            'TXT_AUCTION_SAVE'                        =>    $_CORELANG['TXT_SAVE'],
            'TXT_AUCTION_FIELDS_REQUIRED'            =>    $_ARRAYLANG['TXT_AUCTION_CATEGORY_ADD_FILL_FIELDS'],
            'TXT_AUCTION_THOSE_FIELDS_ARE_EMPTY'        =>    $_ARRAYLANG['TXT_AUCTION_FIELDS_NOT_CORRECT'],
            'TXT_AUCTION_PICTURE'                    =>    $_CORELANG['TXT_IMAGE'],
            'TXT_AUCTION_CATEGORIE'                    =>    $_CORELANG['TXT_CATEGORY'],
            'TXT_AUCTION_PRICE'                        =>    $_ARRAYLANG['TXT_AUCTION_PRICE'].' '.$this->settings['currency'],
            'TXT_AUCTION_TYPE'                        =>    $_CORELANG['TXT_TYPE'],
            'TXT_AUCTION_OFFER'                        =>    $_ARRAYLANG['TXT_AUCTION_OFFER'],
            'TXT_AUCTION_SEARCH'                        =>    $_ARRAYLANG['TXT_AUCTION_SEARCH'],
            'TXT_AUCTION_FOR_FREE'                    =>    $_ARRAYLANG['TXT_AUCTION_FREE'],
            'TXT_AUCTION_AGREEMENT'                    =>    $_ARRAYLANG['TXT_AUCTION_ARRANGEMENT'],
            'TXT_AUCTION_ADDED_BY'                    =>    $_ARRAYLANG['TXT_AUCTION_ADDEDBY'],
            'TXT_AUCTION_USER_DETAIL'                =>    $_ARRAYLANG['TXT_AUCTION_USERDETAILS'],
            'TXT_AUCTION_DETAIL_SHOW'                =>    $_ARRAYLANG['TXT_AUCTION_SHOW_IN_ADVERTISEMENT'],
            'TXT_AUCTION_DETAIL_HIDE'                =>    $_ARRAYLANG['TXT_AUCTION_NO_SHOW_IN_ADVERTISEMENT'],
        ));

        if (isset($_GET['id'])) {
            $entryId = contrexx_addslashes($_GET['id']);
            $objResult = $objDatabase->Execute('SELECT type, title, description, premium, picture, catid, price, regdate, enddate, userid, name, email, userdetails, spez_field_1, spez_field_2, spez_field_3, spez_field_4, spez_field_5 FROM '.DBPREFIX.'module_auction WHERE id = '.$entryId.' LIMIT 1');
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    if ($objFWUser->objUser->login() && $objFWUser->objUser->getId()==$objResult->fields['userid']) {
                        //entry type
                        if ($objResult->fields['type'] == 'offer') {
                            $offer     = 'checked';
                            $search    = '';
                        }else{
                            $offer     = '';
                            $search    = 'checked';
                        }

                        //entry price
                        if ($objResult->fields['price'] == 'forfree') {
                            $forfree     = 'checked';
                            $price         = '';
                            $agreement     = '';
                        }elseif ($objResult->fields['price'] == 'agreement') {
                            $agreement    = 'checked';
                            $price         = '';
                            $forfree     = '';
                        }else{
                            $price         = $objResult->fields['price'];
                            $forfree     = '';
                            $agreement     = '';
                        }

                        //entry user
                        $objResultUser = $objDatabase->Execute('SELECT username FROM '.DBPREFIX.'access_users WHERE id = '.$objResult->fields['userid'].' LIMIT 1');
                        if ($objResultUser !== false) {
                            $addedby = $objResultUser->fields('username');
                        }

                        //entry userdetails
                        if ($objResult->fields['userdetails'] == '1') {
                            $userdetailsOn         = 'checked';
                            $userdetailsOff     = '';
                        }else{
                            $userdetailsOn         = '';
                            $userdetailsOff     = 'checked';
                        }

                        //entry picture
                        if ($objResult->fields['picture'] != '') {
                            $picture         = '<img src="'.$this->mediaWebPath.'pictures/'.$objResult->fields['picture'].'" border="0" alt="" /><br /><br />';
                        }else{
                            $picture         = '<img src="'.$this->mediaWebPath.'pictures/no_picture.gif" border="0" alt="" /><br /><br />';
                        }

                        //entry category
                        $this->getCategories();
                        $categories     = '';
                        $checked         = '';
// TODO: Never used
//                        $catID            = $objResult->fields['catid'];
                        foreach(array_keys($this->categories) as $catId) {
                            $catId == $objResult->fields['catid'] ? $checked = 'selected' : $checked = '';
                            $categories .= '<option value="'.$catId.'" '.$checked.'>'.$this->categories[$catId]['name'].'</option>';
                        }

                        //spez fields
                        $objSpezFields = $objDatabase->Execute("SELECT id, name, value FROM ".DBPREFIX."module_auction_spez_fields WHERE lang_id = '1' AND active='1' ORDER BY id DESC");
                          if ($objSpezFields !== false) {
                            while(!$objSpezFields->EOF) {

// TODO: Never used
//                                ($i % 2)? $class = "row2" : $class = "row1";
                                $input = '<input type="text" name="spez_'.$objSpezFields->fields['id'].'" value="'.$objResult->fields[$objSpezFields->fields['name']].'" style="width: 300px;" maxlength="100">';

                                // initialize variables
                                $this->_objTpl->setVariable(array(
                                    'TXT_AUCTION_SPEZ_FIELD_NAME'        => $objSpezFields->fields['value'],
                                    'AUCTION_SPEZ_FIELD_INPUT'              => $input,
                                ));

                                $this->_objTpl->parse('spez_fields');
// TODO: $class is never used
//                                $i++;
                                $objSpezFields->MoveNext();
                            }
                          }


                        $this->_objTpl->setVariable(array(
                            'AUCTION_ENTRY_ID'                    =>    $entryId,
                            'AUCTION_ENTRY_TYPE_OFFER'            =>    $offer,
                            'AUCTION_ENTRY_TYPE_SEARCH'            =>    $search,
                            'AUCTION_ENTRY_TITLE'                =>    $objResult->fields['title'],
                            'AUCTION_ENTRY_DESCRIPTION'            =>    $objResult->fields['description'],
                            'AUCTION_ENTRY_PICTURE'                =>    $picture,
                            'AUCTION_ENTRY_PICTURE_OLD'            =>    $objResult->fields['picture'],
                            'AUCTION_CATEGORIES'                    =>    $categories,
                            'AUCTION_ENTRY_PRICE'                =>    $price,
                            'AUCTION_ENTRY_FORFREE'                =>    $forfree,
                            'AUCTION_ENTRY_AGREEMENT'            =>    $agreement,
                            'AUCTION_ENTRY_ADDEDBY'                =>    $addedby,
                            'AUCTION_ENTRY_ADDEDBY_ID'            =>    $objResult->fields['userid'],
                            'AUCTION_ENTRY_USERDETAILS_ON'        =>    $userdetailsOn,
                            'AUCTION_ENTRY_USERDETAILS_OFF'        =>    $userdetailsOff,
                            'AUCTION_ENTRY_NAME'                    =>    $objResult->fields['name'],
                            'AUCTION_ENTRY_EMAIL'                =>    $objResult->fields['email'],
                        ));
                           $objResult->MoveNext();
                       }else{
                        CSRF::header('Location: index.php?section=auction&cmd=detail&id='.$_GET['id']);
                        exit;
                    }
                }

                //get navigatin
                $this->getNavigation($catID);
            }
        }else{
            if (isset($_POST['submitEntry'])) {
                if ($_FILES['pic']['name'] != "") {
                    $picture = $this->uploadPicture();
                    if ($picture != "error") {
                        $objFile = new File();
                        $objFile->delFile($this->mediaPath, $this->mediaWebPath, "pictures/".$_POST['picOld']);
                    }
                }else{
                    $picture = $_POST['picOld'];
                }

                if ($picture != "error") {
                    if ($_POST['forfree'] == 1) {
                        $price = "forfree";
                    }elseif ($_POST['agreement'] == 1) {
                        $price = "agreement";
                    }else{
                        $price = contrexx_addslashes($_POST['price']);
                    }

                    $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET
                                        type='".contrexx_addslashes($_POST['type'])."',
                                          title='".contrexx_addslashes($_POST['title'])."',
                                          description='".contrexx_addslashes($_POST['description'])."',
                                          picture='".contrexx_addslashes($picture)."',
                                          catid='".contrexx_addslashes($_POST['cat'])."',
                                          price='".$price."',
                                          name='".contrexx_addslashes($_POST['name'])."',
                                          email='".contrexx_addslashes($_POST['email'])."',
                                          spez_field_1='".contrexx_addslashes($_POST['spez_1'])."',
                                          spez_field_2='".contrexx_addslashes($_POST['spez_2'])."',
                                          spez_field_3='".contrexx_addslashes($_POST['spez_3'])."',
                                          spez_field_4='".contrexx_addslashes($_POST['spez_4'])."',
                                          spez_field_5='".contrexx_addslashes($_POST['spez_5'])."',
                                          userdetails='".contrexx_addslashes($_POST['userdetails'])."'
                                          WHERE id='".contrexx_addslashes($_POST['id'])."'");

                    if ($objResult !== false) {
                        CSRF::header('Location: index.php?section=auction&cmd=detail&id='.$_POST['id']);
                        exit;
                    }else{
// TODO: Never used
//                        $error = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
                        CSRF::header('Location: index.php?section=auction&cmd=edit&id='.$_POST['id']);
                        exit;
                    }
                }else{
// TODO: Never used
//                    $error = $_CORELANG['TXT_AUCTION_IMAGE_UPLOAD_ERROR'];
                    CSRF::header('Location: index.php?section=auction&cmd=edit&id='.$_POST['id']);
                    exit;
                }
            }else{
                CSRF::header('Location: index.php?section=auction');
                exit;
            }
        }
    }



    function delEntry() {

        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        if (!$this->settings['editEntry'] == '1' || (!$this->communityModul && $this->settings['addEntry_only_community'] == '1')) {
            CSRF::header('Location: index.php?section=auction&cmd=detail&id='.$_POST['id']);
            exit;
        }elseif ($this->settings['addEntry_only_community'] == '1') {
            $objFWUser = FWUser::getFWUserObject();
            if (!$objFWUser->objUser->login()) {
                $link = base64_encode(CONTREXX_DIRECTORY_INDEX.'?'.$_SERVER['QUERY_STRING']);
                CSRF::header("Location: ".CONTREXX_DIRECTORY_INDEX."?section=login&redirect=".$link);
                exit;
            }
        } else {
            $objFWUser = FWUser::getFWUserObject();
        }

        //get search
        $this->getSearch();

        if (isset($_GET['id'])) {
            $entryId =contrexx_addslashes($_GET['id']);
            $objResult = $objDatabase->Execute('SELECT id, userid, catid FROM '.DBPREFIX.'module_auction WHERE id = '.$entryId.' LIMIT 1');
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    if ($objFWUser->objUser->login() && $objFWUser->objUser->getId()==$objResult->fields['userid']) {
                        $this->_objTpl->setVariable(array(
                            'AUCTION_ENTRY_ID'                    =>    $entryId,
                            'TXT_AUCTION_DEL'                    =>    $_ARRAYLANG['TXT_AUCTION_DELETE_ADVERTISEMENT'],
                            'TXT_AUCTION_ABORT'                    =>    $_CORELANG['TXT_CANCEL'],
                            'TXT_AUCTION_CONFIRM_DEL'            =>    $_ARRAYLANG['TXT_AUCTION_ADVERTISEMENT_DELETE'],
                        ));

                        //get navigatin
                        $this->getNavigation($objResult->fields['catid']);

                        $objResult->MoveNext();
                    }else{
                        CSRF::header('Location: index.php?section=auction&cmd=detail&id='.$_GET['id']);
                        exit;
                    }
                }
            }
        }else{
            if (isset($_POST['submitEntry'])) {

                $arrDelete = array();
                $arrDelete[0] = $_POST['id'];
                $this->removeEntry($arrDelete);

                CSRF::header('Location: index.php?section=auction');
                exit;
            }else{
                CSRF::header('Location: index.php?section=auction');
                exit;
            }
        }
    }


    /**
    * Get Auction Latest Entrees
    *
    * getContentLatest
    *
    * @access    public
    * @param    string $pageContent
    * @param     string
    */
    function getBlockLatest()
    {
        global $objDatabase, $objTemplate;

        //get latest
        $query = "SELECT id, title, enddate, catid
                    FROM ".DBPREFIX."module_auction
                   WHERE status = '1'
                ORDER BY id DESC
                   LIMIT 5";

        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                // set variables
                $objTemplate->setVariable('AUCTION_DATE', date("d.m.Y", $objResult->fields['enddate']));
                $objTemplate->setVariable('AUCTION_TITLE', $objResult->fields['title']);
                $objTemplate->setVariable('AUCTION_ID', $objResult->fields['id']);
                $objTemplate->setVariable('AUCTION_CATID', $objResult->fields['catid']);

                $objTemplate->parse('auctionLatest');


                $objResult->MoveNext();
            }
        }
    }
    
    
}
?>
