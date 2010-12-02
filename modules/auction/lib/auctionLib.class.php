<?php

/**
 * Auction library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_auction
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_LIBRARY_PATH . '/FRAMEWORK/File.class.php';

/**
 * Auction library
 *
 * External functions for the auction
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_auction
 * @todo        Edit PHP DocBlocks!
 */
class auctionLibrary
{
    function getCategories()
    {
        global $objDatabase;

        $this->settings = $this->getSettings();

        if  ($this->settings['indexview']['value'] == 1) {
            $order = "name";
        } else {
            $order = "displayorder";
        }

        $objResultCategories = $objDatabase->Execute('SELECT * FROM '.DBPREFIX.'module_auction_categories ORDER BY '.$order.'');
           if ($objResultCategories !== false){
               while (!$objResultCategories->EOF) {
                   $this->categories[$objResultCategories->fields['id']]['id']                 = $objResultCategories->fields['id'];
                   $this->categories[$objResultCategories->fields['id']]['name']                 = $objResultCategories->fields['name'];
                   $this->categories[$objResultCategories->fields['id']]['description']         = $objResultCategories->fields['description'];
                   $this->categories[$objResultCategories->fields['id']]['order']                 = $objResultCategories->fields['displayorder'];
                   $this->categories[$objResultCategories->fields['id']]['status']             = $objResultCategories->fields['status'];
                   $objResultCategories->MoveNext();
               }
           }
    }



    function getEntries($orderBy, $where, $like) {

        global $objDatabase;

        if($orderBy != ''){
            $orderBy = 'ORDER BY '.contrexx_addslashes($orderBy);
        }

        if($where != '' && $like != ''){
            $where = "WHERE ".contrexx_addslashes($where)." LIKE ".contrexx_addslashes($like);
        }

        $objResultEntries = $objDatabase->Execute('SELECT * FROM '.DBPREFIX.'module_auction '.$where.' '.$orderBy);
           if ($objResultEntries !== false){
               while (!$objResultEntries->EOF) {
                   $this->entries[$objResultEntries->fields['id']]['id']                 = $objResultEntries->fields['id'];
                   $this->entries[$objResultEntries->fields['id']]['type']             = $objResultEntries->fields['type'];
                   $this->entries[$objResultEntries->fields['id']]['title']             = $objResultEntries->fields['title'];
                   $this->entries[$objResultEntries->fields['id']]['description']         = $objResultEntries->fields['description'];
                   $this->entries[$objResultEntries->fields['id']]['premium']             = $objResultEntries->fields['premium'];
                   $this->entries[$objResultEntries->fields['id']]['picture_1']             = $objResultEntries->fields['picture_1'];
                   $this->entries[$objResultEntries->fields['id']]['picture_2']             = $objResultEntries->fields['picture_2'];
                   $this->entries[$objResultEntries->fields['id']]['picture_3']             = $objResultEntries->fields['picture_3'];
                   $this->entries[$objResultEntries->fields['id']]['picture_4']             = $objResultEntries->fields['picture_4'];
                   $this->entries[$objResultEntries->fields['id']]['picture_5']             = $objResultEntries->fields['picture_5'];
                   $this->entries[$objResultEntries->fields['id']]['catid']             = $objResultEntries->fields['catid'];
                   $this->entries[$objResultEntries->fields['id']]['price']             = $objResultEntries->fields['price'];
                   $this->entries[$objResultEntries->fields['id']]['startprice']             = $objResultEntries->fields['startprice'];
                   $this->entries[$objResultEntries->fields['id']]['incr_step']             = $objResultEntries->fields['incr_step'];
                   $this->entries[$objResultEntries->fields['id']]['regdate']             = $objResultEntries->fields['regdate'];
                   $this->entries[$objResultEntries->fields['id']]['enddate']             = $objResultEntries->fields['enddate'];
                   $this->entries[$objResultEntries->fields['id']]['userid']             = $objResultEntries->fields['userid'];
                   $this->entries[$objResultEntries->fields['id']]['name']             = $objResultEntries->fields['name'];
                   $this->entries[$objResultEntries->fields['id']]['email']             = $objResultEntries->fields['email'];
                   $this->entries[$objResultEntries->fields['id']]['userdetails']         = $objResultEntries->fields['userdetails'];
                   $this->entries[$objResultEntries->fields['id']]['status']             = $objResultEntries->fields['status'];
                   $this->entries[$objResultEntries->fields['id']]['regkey']             = $objResultEntries->fields['regkey'];
                   $this->entries[$objResultEntries->fields['id']]['spez_field_1']     = $objResultEntries->fields['spez_field_1'];
                   $this->entries[$objResultEntries->fields['id']]['spez_field_2']     = $objResultEntries->fields['spez_field_2'];
                   $this->entries[$objResultEntries->fields['id']]['spez_field_3']     = $objResultEntries->fields['spez_field_3'];
                   $this->entries[$objResultEntries->fields['id']]['spez_field_4']     = $objResultEntries->fields['spez_field_4'];
                   $this->entries[$objResultEntries->fields['id']]['spez_field_5']     = $objResultEntries->fields['spez_field_5'];
                   $this->entries[$objResultEntries->fields['id']]['shipping']             = $objResultEntries->fields['shipping'];
                   $this->entries[$objResultEntries->fields['id']]['payment']             = $objResultEntries->fields['payment'];
                   $objResultEntries->MoveNext();
               }
           }
    }



    function countEntries($catId) {

        global $objDatabase;

        $today = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));

        $objResultCount = $objDatabase->Execute('SELECT id FROM '.DBPREFIX.'module_auction WHERE catid = '.contrexx_addslashes($catId).' AND status =1 AND enddate >= "'.$today.'"');
        if($objResultCount !== false){
            $count = $objResultCount->RecordCount();
        }

        return $count;
    }



    function getSettings(){
        global $objDatabase;

        //get settings
        $objResult = $objDatabase->Execute("SELECT name, value, type FROM ".DBPREFIX."module_auction_settings");
        if($objResult !== false){
            while(!$objResult->EOF){
                $settings[$objResult->fields['name']] = $objResult->fields['value'];
                $objResult->MoveNext();
            }
        }

        return $settings;
    }

    function insertAuction($backend=1){
        global $objDatabase, $_ARRAYLANG, $_CORELANG;

        $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_auction SET title='".contrexx_addslashes($_POST['title'])."'");
        $_REQUEST['id'] = $objDatabase->Insert_ID();

        $this->settings = $this->getSettings();
        $tmb_width = 80;
        if($this->settings['thumbnails_width']>0){
            $tmb_width = $this->settings['thumbnails_width'];
        }


         for($x=1; $x<6; $x++){
            if($_FILES['pic_'.$x]['name']!=''){


                $HashCode = $this->GetHash();

                $objDatabase->Execute('UPDATE '.DBPREFIX.'module_auction SET picture_'.$x.'="'.$HashCode.'_'.$_FILES['pic_'.$x]['name'].'" WHERE id='.intval($_REQUEST['id']).'');

                if(@move_uploaded_file($_FILES['pic_'.$x]['tmp_name'], ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])) {

                    chmod(ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'], 0777);

                    // thumb
                    // ------------------------------------------
                    $LegalType = false;
                    if($_FILES['pic_'.$x]['type']=='image/jpeg'){
                        $image         = @imagecreatefromjpeg (ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])
                                    or die ("GD-Image-Stream error");
                        $LegalType     = true;
                    }
                    if($_FILES['pic_'.$x]['type']=='image/gif'){
                        $image         = @imagecreatefromgif (ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])
                                    or die ("GD-Image-Stream error");
                        $LegalType     = true;
                    }
                    if($_FILES['pic_'.$x]['type']=='image/png'){
                        $image         = @imagecreatefrompng (ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'])
                                    or die ("GD-Image-Stream error");
                        $LegalType     = true;
                    }

                    $sourceFile = ASCMS_AUCTION_UPLOAD_PATH.'/'.$HashCode.'_'.$_FILES['pic_'.$x]['name'];

                    if($LegalType){
                        $imageInfos     = @getimagesize($sourceFile);
                        $width             = $imageInfos[0];
                        $height         = $imageInfos[1];
                        $prozent        = ($tmb_width * 100) / $width;
                        $new_width        = $tmb_width;
                        $new_height        = ($height * $prozent) / 100;

                        $thumb             = imagecreatetruecolor($new_width, $new_height);

                        @imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                        if($imageInfos['mime']=='image/jpeg'){
                            imagejpeg($thumb, ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name']);
                        }
                        if($imageInfos['mime']=='image/gif'){
                            imagegif($thumb, ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name']);
                        }
                        if($imageInfos['mime']=='image/png'){
                            imagepng($thumb, ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name']);
                        }

                        chmod(ASCMS_AUCTION_UPLOAD_PATH.'/tmb_'.$HashCode.'_'.$_FILES['pic_'.$x]['name'], 0755);

                    }
                }

            }
        }

        $auctionsEnd = mktime($_REQUEST["end_hour"], $_REQUEST["end_minutes"], 0, $_REQUEST["end_month"], $_REQUEST["end_day"], $_REQUEST["end_year"]);

        $objFWUser             = FWUser::getFWUserObject();
        if ($objFWUser->objUser->login()) {
            $FromUser = $objFWUser->objUser->getId();
        }

        $objResult = $objDatabase->Execute("UPDATE ".DBPREFIX."module_auction SET
                                    type='".contrexx_addslashes($_POST['type'])."',
                                      title='".contrexx_addslashes($_POST['title'])."',
                                      description='".contrexx_addslashes($_POST['description'])."',
                                    premium='".contrexx_addslashes($_POST['premium'])."',
                                      catid='".contrexx_addslashes($_POST['cat'])."',
                                      price='".$price."',
                                      startprice='".$_REQUEST["startprice"]."',
                                      incr_step='".$_REQUEST["incr_steps"]."',
                                      enddate='".$auctionsEnd."',
                                      userid='".$FromUser."',
                                      name='".contrexx_addslashes($_POST['name'])."',
                                      email='".contrexx_addslashes($_POST['email'])."',
                                      shipping='".contrexx_addslashes($_POST['shipping'])."',
                                      payment='".contrexx_addslashes($_POST['payment'])."',
                                      spez_field_1='".contrexx_addslashes($_POST['spez_1'])."',
                                      spez_field_2='".contrexx_addslashes($_POST['spez_2'])."',
                                      spez_field_3='".contrexx_addslashes($_POST['spez_3'])."',
                                      spez_field_4='".contrexx_addslashes($_POST['spez_4'])."',
                                      spez_field_5='".contrexx_addslashes($_POST['spez_5'])."',
                                      userdetails='".contrexx_addslashes($_POST['userdetails'])."'
                                      WHERE id='".intval($_REQUEST['id'])."'");

        if($backend==1){
            if ($objResult !== false) {
                $this->strOkMessage = $_ARRAYLANG['TXT_AUCTION_ADD_SUCCESS'];
                $this->entries();
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }


    }

    function insertEntry($backend){
        global $objDatabase, $_ARRAYLANG, $_CORELANG;

        if($_FILES['pic']['name'] != ""){
            $picture = $this->uploadPicture();
        }else{
            $picture = "";
        }

        if($picture != "error"){

            if($_POST['forfree'] == 1){
                $price = "forfree";
            }elseif($_POST['agreement'] == 1){
                $price = "agreement";
            }else{
                $price = contrexx_addslashes($_POST['price']);
            }

            $today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
            $tempDays     = date("d");
            $tempMonth     = date("m");
            $tempYear     = date("Y");
            $enddate  = mktime(0, 0, 0, $tempMonth, $tempDays+$_POST['days'],  $tempYear);

            if($backend == 1){
                $status     = '1';
                $regdate    = $today;
                $key        = '';
            }else{
                $status     = '0';
                $regdate    = '';
                $rand          = rand(10, 99);
                $key        = md5($rand.$today);
                $key        = substr($key,0 ,6);
            }

            $objFWUser = FWUser::getFWUserObject();

            $objResult = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_auction SET
                                type='".contrexx_addslashes($_POST['type'])."',
                                  title='".contrexx_addslashes($_POST['title'])."',
                                  description='".contrexx_addslashes($_POST['description'])."',
                                premium='".contrexx_addslashes($_POST['premium'])."',
                                  picture='".contrexx_addslashes($picture)."',
                                  catid='".contrexx_addslashes($_POST['cat'])."',
                                  price='".$price."',
                                  regdate='".$regdate."',
                                  enddate='".$enddate."',
                                  userid='".($objFWUser->objUser->login() ? $objFWUser->objUser->getId() : 0)."',
                                  name='".contrexx_addslashes($_POST['name'])."',
                                  email='".contrexx_addslashes($_POST['email'])."',
                                  userdetails='".contrexx_addslashes($_POST['userdetails'])."',
                                  spez_field_1='".contrexx_addslashes($_POST['spez_1'])."',
                                  spez_field_2='".contrexx_addslashes($_POST['spez_2'])."',
                                  spez_field_3='".contrexx_addslashes($_POST['spez_3'])."',
                                  spez_field_4='".contrexx_addslashes($_POST['spez_4'])."',
                                  spez_field_5='".contrexx_addslashes($_POST['spez_5'])."',
                                  regkey='".$key."',
                                  status='".$status."'");

            if($objResult !== false){
                $this->strOkMessage = $_ARRAYLANG['TXT_MARKET_ADD_SUCCESS'];
                if($backend == 0){
                    $this->sendCodeMail($objDatabase->Insert_ID());
                }
            }else{
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }else{
            $this->strErrMessage = $_ARRAYLANG['TXT_MARKET_IMAGE_UPLOAD_ERROR'];
        }
    }



    function sendCodeMail($entryId){

        global $objDatabase, $_ARRAYLANG, $_CONFIG;

        //entrydata
        $objResult = $objDatabase->Execute("SELECT id, title, name, userid, email, regkey FROM ".DBPREFIX."module_auction WHERE id='".contrexx_addslashes($entryId)."' LIMIT 1");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $entryMail            = $objResult->fields['email'];
                $entryName            = $objResult->fields['name'];
                $entryTitle            = $objResult->fields['title'];
                $entryUserid        = $objResult->fields['userid'];
                $entryKey            = $objResult->fields['regkey'];
                $objResult->MoveNext();
            };
        }

        //assesuserdata
        $objResult = $objDatabase->Execute("SELECT email, username FROM ".DBPREFIX."access_users WHERE id='".$entryUserid."' LIMIT 1");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $userUsername        = $objResult->fields['username'];
                $objResult->MoveNext();
            };
        }

        //get mail content n title
        $objResult = $objDatabase->Execute("SELECT title, content, active, mailcc, mailto FROM ".DBPREFIX."module_auction_mail WHERE id='2'");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $mailTitle            = $objResult->fields['title'];
                $mailContent        = $objResult->fields['content'];
                $mailCC                = $objResult->fields['mailcc'];
                $mailTo                = $objResult->fields['mailcc'];
                $mailOn                = $objResult->fields['active'];
                $objResult->MoveNext();
            };
        }


        $array = explode('; ',$mailCC);
        $url    = $_SERVER['SERVER_NAME'].ASCMS_PATH_OFFSET;
        $now     = date(ASCMS_DATE_FORMAT);

        //replase placeholder
        $array_1 = array('[[EMAIL]]', '[[NAME]]', '[[TITLE]]', '[[ID]]', '[[CODE]]', '[[URL]]', '[[DATE]]', '[[USERNAME]]');
        $array_2 = array($entryMail, $entryName, $entryTitle, $entryId, $entryKey, $url, $now, $userUsername);


        for($x = 0; $x < 8; $x++){
          $mailTitle = str_replace($array_1[$x], $array_2[$x], $mailTitle);
        }

        for($x = 0; $x < 8; $x++){
          $mailContent = str_replace($array_1[$x], $array_2[$x], $mailContent);
        }

        //create mail
        $fromName    = $_CONFIG['coreAdminName']." - ".$url;
        $fromMail    = $_CONFIG['coreAdminEmail'];
        $subject     = $mailTitle;
        $message     = $mailContent;

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

            if($mailTo == 'admin'){
                $addressee = $fromMail;
            } else {
                $addressee = $entryMail;
            }

            if($mailOn == 1){
                $objMail->AddAddress($addressee);
                $objMail->Send();
                $objMail->ClearAddresses();
            }

            // Email message
            foreach($array as $toCC) {
                // Email message
                if (!empty($toCC)) {
                    $objMail->AddAddress($toCC);
                    $objMail->Send();
                    $objMail->ClearAddresses();
                }
            }
        }
    }


    function sendMail($entryId){

        global $objDatabase, $_ARRAYLANG, $_CORELANG, $_CONFIG;

        //entrydata
        $objResult = $objDatabase->Execute("SELECT id, title, name, userid, email FROM ".DBPREFIX."module_auction WHERE id='".contrexx_addslashes($entryId)."' LIMIT 1");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $entryMail            = $objResult->fields['email'];
                $entryName            = $objResult->fields['name'];
                $entryTitle            = $objResult->fields['title'];
                $entryUserid        = $objResult->fields['userid'];
                $objResult->MoveNext();
            };
        }

        //assesuserdata
        $objResult = $objDatabase->Execute("SELECT email, username FROM ".DBPREFIX."access_users WHERE id='".$entryUserid."' LIMIT 1");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $userUsername        = $objResult->fields['username'];
                $objResult->MoveNext();
            };
        }

        //get mail content n title
        $objResult = $objDatabase->Execute("SELECT title, content, active, mailcc FROM ".DBPREFIX."module_auction_mail WHERE id='1'");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $mailTitle        = $objResult->fields['title'];
                $mailContent    = $objResult->fields['content'];
                $mailCC            = $objResult->fields['mailcc'];
                $mailOn            = $objResult->fields['active'];
                $objResult->MoveNext();
            };
        }


        if($mailOn == 1){
            $array = explode('; ',$mailCC);
            $url    = $_SERVER['SERVER_NAME'].ASCMS_PATH_OFFSET;
            $link    = "http://".$url."/index.php?section=auction&cmd=detail&id=".$entryId;
            $now     = date(ASCMS_DATE_FORMAT);

            //replase placeholder
            $array_1 = array('[[EMAIL]]', '[[NAME]]', '[[TITLE]]', '[[ID]]', '[[LINK]]', '[[URL]]', '[[DATE]]', '[[USERNAME]]');
            $array_2 = array($entryMail, $entryName, $entryTitle, $entryId, $link, $url, $now, $userUsername);


            for($x = 0; $x < 8; $x++){
              $mailTitle = str_replace($array_1[$x], $array_2[$x], $mailTitle);
            }

            for($x = 0; $x < 8; $x++){
              $mailContent = str_replace($array_1[$x], $array_2[$x], $mailContent);
            }

            //create mail
            $to         = $entryMail;
            $fromName    = $_CONFIG['coreAdminName']." - ".$url;
            $fromMail    = $_CONFIG['coreAdminEmail'];
            $subject     = $mailTitle;
            $message     = $mailContent;


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
                $objMail->AddAddress($to);
                $objMail->Send();
                $objMail->ClearAddresses();

                foreach($array as $toCC) {
                    // Email message
                    if (!empty($toCC)) {
                        $objMail->AddAddress($toCC);
                        $objMail->Send();
                        $objMail->ClearAddresses();
                    }
                }
            }
        }
    }



    function uploadPicture(){

        $status            = "";
        $path            = "pictures/";

        //check file array
        if(isset($_FILES) && !empty($_FILES))
        {
            //get file info
            $tmpFile          = $_FILES['pic']['tmp_name'];
               $fileName         = $_FILES['pic']['name'];

               if($fileName != ""){
                //check extension
                   $info     = pathinfo($fileName);
                $exte     = $info['extension'];
                $exte     = (!empty($exte)) ? '.' . $exte : '';
                $part1    = substr($fileName, 0, strlen($fileName) - strlen($exte));
                $rand      = rand(10, 99);
                $fileName = md5($rand.$fileName).$exte;

                //check file
                if(file_exists($this->mediaPath.$path.$fileName)){
                    $fileName = $rand.$part1 . '_' . (time()) . $exte;
                    $fileName = md5($fileName).$exte;
                }

                //upload file
                if(@move_uploaded_file($tmpFile, $this->mediaPath.$path.$fileName)) {
                    $objFile = new File();
                    $objFile->setChmod($this->mediaPath, $this->mediaWebPath, $path.$fileName);
                    $status = $fileName;
                }else{
                    $status = "error";
                }
            }else {
                $status = "error";
            }
        }

        return $status;
    }


    function removeEntry($array){

        global $objDatabase, $_ARRAYLANG, $_CORELANG;

        foreach($array as $entryId) {
               $status = "";
               $objResult = $objDatabase->Execute('SELECT picture FROM '.DBPREFIX.'module_auction WHERE id = '.$entryId.' LIMIT 1');
            if($objResult !== false){
                $picture = $objResult->fields['picture'];
            }

            if($picture != ''){
                $objFile = new File();
                $status = $objFile->delFile($this->mediaPath, $this->mediaWebPath, "pictures/".$picture);
            }

            if($status != "error"){
                $objResultDel = $objDatabase->Execute('DELETE FROM '.DBPREFIX.'module_auction WHERE id = '.$entryId.'');
                if($objResultDel !== false){
                    $this->strOkMessage = $_ARRAYLANG['TXT_MARKET_DELETE_SUCCESS'];
                }else{
                    $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
                }
            }else{
                $this->strErrMessage = $_ARRAYLANG['TXT_MARKET_IMAGE_DELETE_ERROR'];
            }
        }
    }

    function GetAuctionBids($auction_id){
        global $objDatabase;
        $returnArray = array();
        $counter = 0;
        $objResult = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."module_auction_bids WHERE bid_auction='".$auction_id."' ORDER BY bid_id");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $returnArray[$counter]['bid_id']            = $objResult->fields['bid_id'];
                $returnArray[$counter]['bid_auction']       = $objResult->fields['bid_auction'];
                $returnArray[$counter]['bid_user']          = $objResult->fields['bid_user'];
                $returnArray[$counter]['bid_price']         = $objResult->fields['bid_price'];
                $returnArray[$counter]['bid_time']          = $objResult->fields['bid_time'];
                $returnArray[$counter]['bid_ip']            = $objResult->fields['bid_ip'];
                $counter++;
                $objResult->MoveNext();
            };
        }
        return $returnArray;
    }

    function GetHash($length=10){
        $act_id = '';
        $pool = "qwertzupasdfghkyxcvbnm";
        $pool .= "23456789";
        $pool .= "WERTZUPLKJHGFDSAYXCVBNM";
        srand ((double)microtime()*1000000);
        for($index = 0; $index < $length; $index++){
            $act_id .= substr($pool,(rand()%(strlen ($pool))), 1);
        }
        return $act_id;
    }
}

?>
