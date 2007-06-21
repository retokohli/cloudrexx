<?php
/**
* Class newsletter
*
* Newsletter module class
*
* @copyright	CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author		Comvation Development Team <info@comvation.com>            
* @module		Newsletter
* @modulegroup	modules
* @access		public
* @version		1.0.0
*/
class newsletter
{
	/**
	* Template object
	*
	* @access private
	* @var object
	*/
	var $_objTpl;
	
	/**
	* Page title
	*
	* @access private
	* @var string
	*/
	var $_pageTitle;
	
	/**
	* Status message
	*
	* @access private
	* @var string
	*/
	var $_statusMessage = '';
	
	/**
	* Constructor
	*/
	function newsletter()
	{
		$this->__construct();
	}
	
	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct()
	{
		global $objTemplate, $_ARRAYLANG; 
		
		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/newsletter/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		
    	if(!isset($_REQUEST['standalone'])){
    		
    		$objTemplate->setVariable("CONTENT_NAVIGATION", "	
    										<a href='index.php?cmd=newsletter'>".$_ARRAYLANG['TXT_OVERVIEW']."</a> 
    										<a href='index.php?cmd=newsletter&act=newsletterlist'>".$_ARRAYLANG['TXT_NEWSLETTER']."</a> 
    										<a href='index.php?cmd=newsletter&act=edituser'>".$_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION']."</a>
    										<a href='index.php?cmd=newsletter&act=categorys'>".$_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS']."</a>
    										<a href='index.php?cmd=newsletter&act=dispatch'>".$_ARRAYLANG['TXT_SETTINGS']."</a>");
		}
	}
	
	/**
	* Set the backend page
	*
	* @access public
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	
	function getPage() {
		global $objTemplate, $_ARRAYLANG;
		
		switch ($_GET['act']){
			case "newsletter":
				$this->newsletterOverview();
			break;
			case "user":
				$this->userOverview();
			break;
			case "categorys":
				$this->categoryOverview();
			break;
			case "config":
				$this->configOverview();
			break;
			case "adduser":
				$this->adduser();
			break;
			case "edituser":
				$this->edituser();
			break;
			case "detailuser":
				$this->detailuser();
			break;
			case "updateuser":
				$this->updateuser();
			break;
			case "importuser":
				$this->importuser();
			break;
			case "exportuser":
				$this->exportuser();
			break;
			case "newsletterinsert":
				$this->newsletterinsert();
			break;
			case "newsletterupdate":
				$this->newsletterinsert();
			break;
			case "newsletterlist":
				$this->newsletterlist();
				break;
			case "newslettersend":
				$this->newslettersend();
				break;
			case "dispatch":
				$this->ConfigDispatch();
				break;
			case "confightml":
				$this->ConfigHTML();
				break;
			default:
				$this->overview();
			break;
		}
		
		if(!isset($_REQUEST['standalone'])){
		
			$objTemplate->setVariable(array(
				'CONTENT_TITLE'				=> $this->_pageTitle,
				'CONTENT_STATUS_MESSAGE'	=> $this->_statusMessage,
				'ADMIN_CONTENT'				=> $this->_objTpl->get()
			));
			
		}else{
			$this->_objTpl->show();
		}	
			
	}
	
	function ConfigHTML(){
		global $objDatabase, $_ARRAYLANG;
		$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
		$this->_objTpl->loadTemplateFile('newsletter_config_html.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_GENERATE_HTML']);
	
		$query 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category order by name";
		$objResult 	= $objDatabase->Execute($query);
		$count 		= $objResult->RecordCount();
		if($count<1){
			$this->_objTpl->setVariable('TXT_NO_CATEGORIES', $_ARRAYLANG['TXT_NO_CATEGORIES']);
		}else{
			$NoCats = "";
		}
		if ($objResult !== false) {
			$html_code = '<form name="newsletter" action="?section=newsletter&act=subscribe" method="post">'.chr(13).chr(10);
			while (!$objResult->EOF) {
				
				if($count==1){
					$html_code .= '<input type="hidden" name="category_'.$objResult->fields['id'].'" value="1" />'.chr(13).chr(10);
				}else{
					$html_code .= '<input type="checkbox" name="category_'.$objResult->fields['id'].'" /> '.$objResult->fields['name'].'<br/>'.chr(13).chr(10);
				}
	            
				$objResult->MoveNext();
			}
			$html_code .= '<input type="text" size="40" name="email" value="'.$_ARRAYLANG['TXT_YOUR_EMAIL'].'" /><br/>'.chr(13).chr(10).'<input type="submit" value="'.$_ARRAYLANG['TXT_ENTRY'].'" />'.chr(13).chr(10).'</form>';
		}
		
		
		$this->_objTpl->setVariable('HTML_CODE', $html_code);
		$this->_objTpl->setVariable('TXT_SELECT_ALL', $_ARRAYLANG['TXT_SELECT_ALL']);
		$this->_objTpl->setVariable('TXT_DISPATCH_SETINGS', $_ARRAYLANG['TXT_DISPATCH_SETINGS']);
		$this->_objTpl->setVariable('TXT_GENERATE_HTML', $_ARRAYLANG['TXT_GENERATE_HTML']);
		
	
	
	}
	
	function ConfigDispatch(){
		global $objDatabase, $_ARRAYLANG;
		$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
		$this->_objTpl->loadTemplateFile('newsletter_config_dispatch.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_DISPATCH_SETINGS']);
		
		if($_POST["update"]=="exe"){
			$objResultUPDATE 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_config
														SET sender_email='".$_POST["sender_email"]."',
														sender_name='".$_POST["sender_name"]."',
														return_path='".$_POST["return_path"]."',
														mails_per_run='".$_POST["mails_per_run"]."',
														profile_setup_html='".$_POST["profile_setup_html"]."',
														profile_setup_text='".$_POST["profile_setup_text"]."',
														unsubscribe_html='".$_POST["unsubscribe_html"]."',
														unsubscribe_text='".$_POST["unsubscribe_text"]."'
														WHERE id=1");
			$objResultUPDATE 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_template
														SET  html='".$_POST["template_html"]."',
														text='".$_POST["template_text"]."'
														WHERE id=1");
		}
		 
		// Load Values
		// -------------
		$query 		= "SELECT sender_email, sender_name, return_path, profile_setup_html, profile_setup_text, unsubscribe_html, unsubscribe_text, mails_per_run FROM ".DBPREFIX."module_newsletter_config WHERE 1";
					$objResult 	= $objDatabase->Execute($query);
					if ($objResult !== false) {
						$sendermail_value = $objResult->fields['sender_email'];
						$sendername_value = $objResult->fields['sender_name'];
						$returnpath_value = $objResult->fields['return_path'];
						$profilehtml_value = $objResult->fields['profile_setup_html'];
						$profiletext_value = $objResult->fields['profile_setup_text'];
						$unsubscribehtml_value = $objResult->fields['unsubscribe_html'];
						$unsubscribetext_value = $objResult->fields['unsubscribe_text'];
						$mailsperrun_value = $objResult->fields['mails_per_run'];
					}
		$query 		= "SELECT id, html, text FROM ".DBPREFIX."module_newsletter_template WHERE id=1";
					$objResult 	= $objDatabase->Execute($query);
					if ($objResult !== false) {
						$templatehtml_value = $objResult->fields['html'];
						$templatetext_value = $objResult->fields['text'];
					}
					
		$html_code = '';
		
		
		
		$this->_objTpl->setVariable(array(
			'TXT_SETTINGS'				=> $_ARRAYLANG['TXT_SETTINGS'],
			'TXT_SENDER' 				=> $_ARRAYLANG['TXT_SENDER'],
			'TXT_LASTNAME' 				=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_RETURN_PATH' 			=> $_ARRAYLANG['TXT_RETURN_PATH'],
			'TXT_SEND_LIMIT' 			=> $_ARRAYLANG['TXT_SEND_LIMIT'],
			'TXT_SAVE'					=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_DISPATCH_SETINGS'		=> $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
			'TXT_PROFILE_SETUP'			=> $_ARRAYLANG['TXT_PROFILE_SETUP'],
			'TXT_UNSUBSCRIBE'			=> $_ARRAYLANG['TXT_UNSUBSCRIBE'],
			'TXT_FILL_OUT_ALL_REQUIRED_FIELDS' => $_ARRAYLANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'],
			'SENDERMAIL_VALUE'			=> $sendermail_value,
			'SENDERNAME_VALUE'			=> $sendername_value,
			'RETURNPATH_VALUE'			=> $returnpath_value,
			'MAILSPERRUN_VALUE'			=> $mailsperrun_value,
			'PROFILEHTML_VALUE'			=> $profilehtml_value,
			'PROFILETEXT_VALUE'			=> $profiletext_value,
			'UNSUBSCRIBEHTML_VALUE'		=> $unsubscribehtml_value,
			'UNSUBSCRIBETEXT_VALUE'		=> $unsubscribetext_value,
			'TEMPLATEHTML_VALUE'		=> $templatehtml_value,
			'TEMPLATETEXT_VALUE'		=> $templatetext_value,
			'TXT_GENERATE_HTML'		=> $_ARRAYLANG['TXT_GENERATE_HTML'],
			'TXT_WILDCART_INFOS'		=> $_ARRAYLANG['TXT_WILDCART_INFOS'],
			'TXT_USER_DATA'				=> $_ARRAYLANG["TXT_USER_DATA"],
			'TXT_EMAIL_ADDRESS'			=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_LASTNAME'				=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_FIRSTNAME'				=> $_ARRAYLANG['TXT_FIRSTNAME'],
			'TXT_STREET'				=> $_ARRAYLANG['TXT_STREET'],
			'TXT_ZIP'					=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_CITY'					=> $_ARRAYLANG['TXT_CITY'],
			'TXT_COUNTRY'				=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_PHONE'					=> $_ARRAYLANG['TXT_PHONE'],
			'TXT_BIRTHDAY'				=> $_ARRAYLANG['TXT_BIRTHDAY'],
			'TXT_GENERALLY'				=> $_ARRAYLANG['TXT_GENERALLY'],
			'TXT_PROFILE_SETUP'			=> $_ARRAYLANG['TXT_PROFILE_SETUP'],
			'TXT_UNSUBSCRIBE'			=> $_ARRAYLANG['TXT_UNSUBSCRIBE'],
			'TXT_DATE'					=> $_ARRAYLANG['TXT_DATE'],
			'TXT_NEWSLETTER_CONTENT'	=> $_ARRAYLANG['TXT_NEWSLETTER_CONTENT']
			));
		
	}
	
	function newslettersend(){
		global $objDatabase, $_ARRAYLANG;
		
		$NewsletterID = $_GET["id"];
		if($NewsletterID==""){
			$NewsletterID = $_POST["id"];
		}
		
		// Send Testmail
		// -------------------------------
		if($_POST["sendtestmail"]=="exe"){
			if($this->CheckEmail($_POST["testemail"])){
				if($this->SendEmail(0, $NewsletterID, $_POST["testemail"], 0)){
					$this->_statusMessage = str_replace("%s", $_POST["testemail"], $_ARRAYLANG['TXT_TESTMAIL_SEND_SUCCESSFUL']);
				}else{
					$this->_statusMessage = $_ARRAYLANG['TXT_SENDING_MESSAGE_ERROR'];
				}
				
			}else{
				$this->_statusMessage = $_ARRAYLANG['TXT_INVALID_EMAIL_ADDRESS'];
			}
		}
		// -------------------------------
		
		$this->_pageTitle = $_ARRAYLANG['TXT_SEND_NEWSLETTER'];
		
		if($_GET["standalone"]=="true"){
			$this->_objTpl->loadTemplateFile('newsletter_newsletter_sending.html');
		}else{
			$this->_objTpl->loadTemplateFile('newsletter_newsletter_send.html');
		}
		
		$queryNU 		= "SELECT * FROM ".DBPREFIX."module_newsletter_rel_cat_news right join ".DBPREFIX."module_newsletter_rel_user_cat on ".DBPREFIX."module_newsletter_rel_cat_news.category=".DBPREFIX."module_newsletter_rel_user_cat.category right join contrexx_module_newsletter_user on contrexx_module_newsletter_rel_user_cat.user=contrexx_module_newsletter_user.id and contrexx_module_newsletter_user.status = 1 where newsletter=".$NewsletterID." group by ".DBPREFIX."module_newsletter_rel_user_cat.user";
		$objResultNU 	= $objDatabase->Execute($queryNU);
		$countNU 		= $objResultNU->RecordCount();
		
		
		$queryNewsletterValues = "select id, subject, template, content, content_text, attachment, format, priority, sender_email, sender_name, return_path, status, count, date_create, date_sent, tmp_copy from ".DBPREFIX."module_newsletter where id=".$NewsletterID."";
		$objResultNewsletterValues = $objDatabase->Execute($queryNewsletterValues);
		if ($objResultNewsletterValues !== false) {
			$subject 		= $objResultNewsletterValues->fields['subject'];
			$template 		= $objResultNewsletterValues->fields['template'];
			$content 		= $objResultNewsletterValues->fields['content'];
			$content_text 	= $objResultNewsletterValues->fields['content_text'];
			$attachment 	= $objResultNewsletterValues->fields['attachment'];
			$format 		= $objResultNewsletterValues->fields['format'];
			$priority 		= $objResultNewsletterValues->fields['priority'];
			$sender_email 	= $objResultNewsletterValues->fields['sender_email'];
			$sender_name 	= $objResultNewsletterValues->fields['sender_name'];
			$return_path 	= $objResultNewsletterValues->fields['return_path'];
			$status 		= $objResultNewsletterValues->fields['status'];
			$count 			= $objResultNewsletterValues->fields['count'];
			$date_create 	= $objResultNewsletterValues->fields['date_create'];
			$date_sent 		= $objResultNewsletterValues->fields['date_sent'];
			$tmp_copy 		= $objResultNewsletterValues->fields['tmp_copy'];
		}
		
		if($countNU==0){
			$countNU = 1;
		}
		$StatusPixelWidth = 200/$countNU;
		$StatusBarWidth = round($StatusPixelWidth * $count, 0);
		
		$this->_objTpl->setVariable('TXT_SEND_TEST_EMAIL', $_ARRAYLANG['TXT_SEND_TEST_EMAIL']);
		$this->_objTpl->setVariable(array(
			'TXT_EMAIL_ADDRESS'	=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_SUBMIT'		=> $_ARRAYLANG['TXT_SUBMIT'],
			'TXT_SEND_NEWSLETTER'=> $_ARRAYLANG['TXT_SEND_NEWSLETTER'],
			'TXT_NEWSLETTER_SENT_NEWSLETTERS'=> $_ARRAYLANG['TXT_NEWSLETTER_SENT_NEWSLETTERS'],
			'NEWSLETTER_ID'		=> $NewsletterID,
			'NEWSLETTER_USERES'	=> $countNU,
			'NEWSLETTER_SENDT'	=> $count,
			'TXT_NEWSLETTER_SUBJECT'=> $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
			'VALUE_SUBJECT'		=> $subject,
			'STATUSBAR_WIDTH'	=> $StatusBarWidth,
			'SENDING_BUTTON'	=> '<input type="button" value="'.$_ARRAYLANG['TXT_SEND_NEWSLETTER'].'" onclick="Sending();" />'
			));
		if($status==0){
			if($_GET["sending"]=="exe"){
				if($tmp_copy==0){
					$queryNU 		= "SELECT * FROM ".DBPREFIX."module_newsletter_rel_cat_news right join ".DBPREFIX."module_newsletter_rel_user_cat on ".DBPREFIX."module_newsletter_rel_cat_news.category=".DBPREFIX."module_newsletter_rel_user_cat.category right join ".DBPREFIX."module_newsletter_user on ".DBPREFIX."module_newsletter_rel_user_cat.user=".DBPREFIX."module_newsletter_user.id and ".DBPREFIX."module_newsletter_user.status = 1 where newsletter=".$NewsletterID." group by ".DBPREFIX."module_newsletter_rel_user_cat.user";
					$objResultNU 	= $objDatabase->Execute($queryNU);
					if ($objResultNU !== false) {
						while (!$objResultNU->EOF) { 
							$queryCheck 	= "SELECT newsletter, email FROM ".DBPREFIX."module_newsletter_tmp_sending where email='".$objResultNU->fields['email']."' and newsletter=".$NewsletterID."";
							$objResultCheck = $objDatabase->Execute($queryCheck);
							$countCheck 	= $objResultCheck->RecordCount();
							if ($countCheck == 0) {
								$objResultIM = $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_tmp_sending 
													(newsletter, email)
													VALUES (".$NewsletterID.", '".$objResultNU->fields['email']."')");
							}
							$objResultNU->MoveNext();
						}
						$objResultUPDATE 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter
															SET tmp_copy=1
															WHERE id=".$NewsletterID."");
						$this->_objTpl->setVariable('RELOAD_SENDING_FRAME', '<script language="javascript">setTimeout("Sending()",1000);</script>');
			
					}
				}else{
					// Einzel-Versand
					/*
					$queryNU 		= "SELECT * FROM ".DBPREFIX."module_newsletter_tmp_sending right join ".DBPREFIX."module_newsletter_user on ".DBPREFIX."module_newsletter_user.email=".DBPREFIX."module_newsletter_tmp_sending.email where sendt=0";
					$objResultNU 	= $objDatabase->Execute($queryNU);
					if ($objResultNU !== false) {
						$this->SendEmail($objResultNU->fields['id'], $NewsletterID, ''.$objResultNU->fields['email'].'', 1);
					}
					*/
					// --------------
					
					$query 		= "SELECT mails_per_run FROM ".DBPREFIX."module_newsletter_config WHERE 1";
					$objResult 	= $objDatabase->Execute($query);
					if ($objResult !== false) {
						$mails_per_run = $objResult->fields['mails_per_run'];
					}
					
					$queryNU 		= "SELECT * FROM ".DBPREFIX."module_newsletter_tmp_sending right join ".DBPREFIX."module_newsletter_user on ".DBPREFIX."module_newsletter_user.email=".DBPREFIX."module_newsletter_tmp_sending.email where sendt=0 LIMIT 0, ".$mails_per_run."";
					$objResultNU 	= $objDatabase->Execute($queryNU);
					if ($objResultNU !== false) {
						while (!$objResultNU->EOF) {
							$this->SendEmail($objResultNU->fields['id'], $NewsletterID, ''.$objResultNU->fields['email'].'', 1);
							$objResultNU->MoveNext();
						}
					}
					// --------------
				}
				$this->_objTpl->setVariable('TXT_SENDING', $_ARRAYLANG['TXT_SENDING']);
			}	
		}
			
	}
	
	function SendEmail($UserID, $NewsletterID, $TargetEmail, $TmpEntry){
		global $objDatabase, $_ARRAYLANG;
		
		require_once ASCMS_LIBRARY_PATH . '/phpmailer/class.phpmailer.php';
		
		// Load Newsletter-Values
		// ---------------------------------------------
		$queryNewsletterValues = "select id, subject, template, content, content_text, attachment, format, priority, sender_email, sender_name, return_path, status, count, date_create, date_sent from ".DBPREFIX."module_newsletter where id=".$NewsletterID."";
		$objResultNewsletterValues = $objDatabase->Execute($queryNewsletterValues);
		if ($objResultNewsletterValues !== false) {
			$subject 		= $objResultNewsletterValues->fields['subject'];
			$template 		= $objResultNewsletterValues->fields['template'];
			$content 		= $objResultNewsletterValues->fields['content'];
			$content_text 	= $objResultNewsletterValues->fields['content_text'];
			$attachment 	= $objResultNewsletterValues->fields['attachment'];
			$format 		= $objResultNewsletterValues->fields['format'];
			$priority 		= $objResultNewsletterValues->fields['priority'];
			$sender_email 	= $objResultNewsletterValues->fields['sender_email'];
			$sender_name 	= $objResultNewsletterValues->fields['sender_name'];
			$return_path 	= $objResultNewsletterValues->fields['return_path'];
			$status 		= $objResultNewsletterValues->fields['status'];
			$count 			= $objResultNewsletterValues->fields['count'];
			$date_create 	= $objResultNewsletterValues->fields['date_create'];
			$date_sent 		= $objResultNewsletterValues->fields['date_sent'];
		}
		
		$HTML_TemplateSource = $this->GetTemplateSource($template, 'html');
		$TEXT_TemplateSource = $this->GetTemplateSource($template, 'text');
		
		$NewsletterBody_HTML = $this->ParseNewsletter($UserID, $subject, $content, $HTML_TemplateSource, "html", $TargetEmail);
		$NewsletterBody_TEXT = $this->ParseNewsletter($UserID, $subject, $content_text, $TEXT_TemplateSource, "text", $TargetEmail);
		
		$mail = new phpmailer();
		$mail->From 	= $sender_email;
		$mail->FromName = $sender_name;	
		$mail->Subject 	= $subject;
		$mail->Priority = $priority;
		switch ($format){
			case "text/html":
				$mail->Body 	= $NewsletterBody_HTML;
				$mail->AltBody 	= $NewsletterBody_TEXT;
			break;
			case "html":
				$mail->IsHTML(true);
				$mail->Body 	= $NewsletterBody_HTML;
			break;
			case "text":
				$mail->IsHTML(false); 
				$mail->Body 	= $NewsletterBody_TEXT;
			break;
			default:
				$mail->Body 	= $NewsletterBody_HTML;
				$mail->AltBody 	= $NewsletterBody_TEXT;
			break;
		}
		$queryATT 		= "SELECT newsletter, file_name FROM ".DBPREFIX."module_newsletter_attachment where newsletter=".$NewsletterID."";
		$objResultATT 	= $objDatabase->Execute($queryATT);
		if ($objResultATT !== false) {
			while (!$objResultATT->EOF) {
				$mail->AddAttachment(ASCMS_NEWSLETTER_ATTACHMENT."/".$objResultATT->fields['file_name'], $objResultATT->fields['file_name'], "8bit");
				$objResultATT->MoveNext();
			}
		}
		
		$mail->AddAddress($TargetEmail);
		
		if($mail->Send()){
			$ReturnVar = true;
			if($TmpEntry==1){
				// Insert TMP-ENTRY Sended Email & Count++
				// ---------------------------------------
				$objResultIM = $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_tmp_sending 
												SET sendt=1 where email='".$TargetEmail."'");
				$count ++;
				$objResultUPDATE 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter
														SET count='".$count."'
														WHERE id=".$NewsletterID."");
				$queryCheck 	= "SELECT newsletter, email, sendt FROM ".DBPREFIX."module_newsletter_tmp_sending where newsletter='".$NewsletterID."' and sendt=0";
				$objResultCheck = $objDatabase->Execute($queryCheck);
				$countCheck 	= $objResultCheck->RecordCount();
				if ($countCheck == 0) {
					$objResultUPDATE 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter
														SET status=1
														WHERE id=".$NewsletterID."");
				}
			}
			$this->_objTpl->setVariable('RELOAD_SENDING_FRAME', '<script language="javascript">setTimeout("Sending()",1000);</script>');
																
		}else{
			$ReturnVar = false;
		}
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		
		return $ReturnVar;
	}
	
	function GetTemplateSource($TemplateID, $format){
		global $objDatabase;
		$TemplateSource = '';
		$queryPN = "select id, name, description, ".$format." from ".DBPREFIX."module_newsletter_template where id=".$TemplateID."";
		$objResultPN = $objDatabase->Execute($queryPN);
		if ($objResultPN !== false) {
			$TemplateSource = $objResultPN->fields[$format];
		}
		return $TemplateSource;
	}
	
	function CheckEmail($EmailAdress){
		if(preg_match('/^[a-zA-Z0-9.][a-zA-Z0-9-_\s]+@[a-zA-Z0-9-\s].+\.[a-zA-Z]{2,5}$/',$EmailAdress)){
			$ReturnVar = true;
		}else{
			$ReturnVar = false;
		}
		return $ReturnVar;
	}
	
	function ParseNewsletter($UserID, $subject, $content_text, $TemplateSource, $format, $TargetEmail){
		global $objDatabase, $_ARRAYLANG;
		$NewsletterBody = '';
		
		if($UserID!=0){
			$queryPN = "select id, code, email, lastname, firstname, street, zip, city, country, phone, birthday, status, emaildate from ".DBPREFIX."module_newsletter_user where id=".$UserID."";
			$objResultPN = $objDatabase->Execute($queryPN);
			
			if ($objResultPN !== false) {
				$code 		= $objResultPN->fields["code"];
				$lastname 	= $objResultPN->fields["lastname"];
				$firstname 	= $objResultPN->fields["firstname"];
				$street 	= $objResultPN->fields["street"];
				$zip 		= $objResultPN->fields["zip"];
				$city 		= $objResultPN->fields["city"];
				$country 	= $objResultPN->fields["country"];
				$birthday 	= $objResultPN->fields["birthday"];
				$status 	= $objResultPN->fields["status"];
				$emaildate 	= $objResultPN->fields["emaildate"];
			
				if($format=="text"){
				
					$content_text = str_replace("<-- code -->", $code, $content_text);
					$content_text = str_replace("<-- lastname -->", $lastname, $content_text);
					$content_text = str_replace("<-- firstname -->", $firstname, $content_text);
					$content_text = str_replace("<-- street -->", $street, $content_text);
					$content_text = str_replace("<-- zip -->", $zip, $content_text);
					$content_text = str_replace("<-- city -->", $city, $content_text);
					$content_text = str_replace("<-- country -->", $country, $content_text);
					$content_text = str_replace("<-- birthday -->", $birthday, $content_text);
					$content_text = str_replace("<-- emaildate --".$GRZeichen, $emaildate, $content_text);
					$content_text = str_replace("<--code-->", $code, $content_text);
					$content_text = str_replace("<--lastname-->", $lastname, $content_text);
					$content_text = str_replace("<--firstname-->", $firstname, $content_text);
					$content_text = str_replace("<--street-->", $street, $content_text);
					$content_text = str_replace("<--zip-->", $zip, $content_text);
					$content_text = str_replace("<--city-->", $city, $content_text);
					$content_text = str_replace("<--country-->", $country, $content_text);
					$content_text = str_replace("<--birthday-->", $birthday, $content_text);
					$content_text = str_replace("<--emaildate--".$GRZeichen, $emaildate, $content_text);
				
				}else{
				
					$content_text = str_replace("&lt;-- code --&gt;", $code, $content_text);
					$content_text = str_replace("&lt;-- lastname --&gt;", $lastname, $content_text);
					$content_text = str_replace("&lt;-- firstname --&gt;", $firstname, $content_text);
					$content_text = str_replace("&lt;-- street --&gt;", $street, $content_text);
					$content_text = str_replace("&lt;-- zip --&gt;", $zip, $content_text);
					$content_text = str_replace("&lt;-- city --&gt;", $city, $content_text);
					$content_text = str_replace("&lt;-- country --&gt;", $country, $content_text);
					$content_text = str_replace("&lt;-- birthday --&gt;", $birthday, $content_text);
					$content_text = str_replace("&lt;-- emaildate --&gt;", $emaildate, $content_text);
					$content_text = str_replace("&lt;--code--&gt;", $code, $content_text);
					$content_text = str_replace("&lt;--lastname--&gt;", $lastname, $content_text);
					$content_text = str_replace("&lt;--firstname--&gt;", $firstname, $content_text);
					$content_text = str_replace("&lt;--street--&gt;", $street, $content_text);
					$content_text = str_replace("&lt;--zip--&gt;", $zip, $content_text);
					$content_text = str_replace("&lt;--city--&gt;", $city, $content_text);
					$content_text = str_replace("&lt;--country--&gt;", $country, $content_text);
					$content_text = str_replace("&lt;--birthday--&gt;", $birthday, $content_text);
					$content_text = str_replace("&lt;--emaildate--&gt;", $emaildate, $content_text);
					
				}
					
				$TemplateSource = str_replace("<-- code -->", $code, $TemplateSource);
				$TemplateSource = str_replace("<-- lastname -->", $lastname, $TemplateSource);
				$TemplateSource = str_replace("<-- firstname -->", $firstname, $TemplateSource);
				$TemplateSource = str_replace("<-- street -->", $street, $TemplateSource);
				$TemplateSource = str_replace("<-- zip -->", $zip, $TemplateSource);
				$TemplateSource = str_replace("<-- city -->", $city, $TemplateSource);
				$TemplateSource = str_replace("<-- country -->", $country, $TemplateSource);
				$TemplateSource = str_replace("<-- birthday -->", $birthday, $TemplateSource);
				$TemplateSource = str_replace("<-- emaildate -->", $emaildate, $TemplateSource);
				$TemplateSource = str_replace("<--code-->", $code, $TemplateSource);
				$TemplateSource = str_replace("<--lastname-->", $lastname, $TemplateSource);
				$TemplateSource = str_replace("<--firstname-->", $firstname, $TemplateSource);
				$TemplateSource = str_replace("<--street-->", $street, $TemplateSource);
				$TemplateSource = str_replace("<--zip-->", $zip, $TemplateSource);
				$TemplateSource = str_replace("<--city-->", $city, $TemplateSource);
				$TemplateSource = str_replace("<--country-->", $country, $TemplateSource);
				$TemplateSource = str_replace("<--birthday-->", $birthday, $TemplateSource);
				$TemplateSource = str_replace("<--emaildate-->", $emaildate, $TemplateSource);
			}
		}
		
		if($format=="text"){
			$content_text 	= str_replace("<-- date -->", date(d).".".date(m).".".date(Y), $content_text);
			$content_text 	= str_replace("<-- profile_setup -->", $this->GetProfileSource($code, $UserID, "text"), $content_text);
			$content_text 	= str_replace("<-- unsubscribe -->", $this->GetUnsubscribeSource($code, $UserID, "text"), $content_text);
			$content_text 	= str_replace("<--date-->", date(d).".".date(m).".".date(Y), $content_text);
			$content_text 	= str_replace("<--profile_setup-->", $this->GetProfileSource($code, $UserID, "text"), $content_text);
			$content_text 	= str_replace("<--unsubscribe-->", $this->GetUnsubscribeSource($code, $UserID, "text"), $content_text);
		}else{
			$content_text 	= str_replace("&lt;-- date --&gt;", date(d).".".date(m).".".date(Y), $content_text);
			$content_text 	= str_replace("&lt;-- profile_setup --&gt;", $this->GetProfileSource($code, $UserID, "html"), $content_text);
			$content_text 	= str_replace("&lt;-- unsubscribe --&gt;", $this->GetUnsubscribeSource($code, $UserID, "html"), $content_text);
			$content_text 	= str_replace("&lt;--date--&gt;", date(d).".".date(m).".".date(Y), $content_text);
			$content_text 	= str_replace("&lt;--profile_setup--&gt;", $this->GetProfileSource($code, $UserID, "html"), $content_text);
			$content_text 	= str_replace("&lt;--unsubscribe--&gt;", $this->GetUnsubscribeSource($code, $UserID, "html"), $content_text);
			
			preg_match_all("|src=\"(.*)\"|U",$content_text,$allImg, PREG_PATTERN_ORDER);
			$size = sizeof($allImg[1]);
			$i = 0;
			while ($i < $size) {
				$URLforReplace = $allImg[1][$i];
				$ReplaceWith = '"http://contrexx.itsicherheit.ch'.$URLforReplace.'"';
				$content_text = str_replace('"'.$URLforReplace.'"', $ReplaceWith, $content_text);
				$i++;
			}
			
		} 
		
		$TemplateSource = str_replace("<-- date -->", date(d).".".date(m).".".date(Y), $TemplateSource);
		$TemplateSource	= str_replace("<-- profile_setup -->", $this->GetProfileSource($code, $UserID, $format), $TemplateSource);
		$TemplateSource	= str_replace("<-- unsubscribe -->", $this->GetUnsubscribeSource($code, $UserID, $format), $TemplateSource);
		
		$NewsletterBody = str_replace("<-- subject -->", $subject, $TemplateSource);
		$NewsletterBody = str_replace("<-- content -->", $content_text, $TemplateSource);
		
		return $NewsletterBody;
	}
	
	function GetUnsubscribeSource($code, $UserID, $format){
		global $objDatabase;
		$queryPN = "select  unsubscribe_".$format." as TxTValue from ".DBPREFIX."module_newsletter_config where id=1";
		$objResultPN = $objDatabase->Execute($queryPN);
		if ($objResultPN !== false) {
			if($format=="html"){
				$UnsubscribeSource = '<a href="'.ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'/index.php?section=newsletter&code='.$code.'&cmd=unsubscribe">'.$objResultPN->fields["TxTValue"].'</a>';
			}else{
$UnsubscribeSource = ''.$objResultPN->fields["TxTValue"].'
'.ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'/index.php?section=newsletter&code='.$code.'&cmd=unsubscribe';
			}
		}
		
		return $UnsubscribeSource;
	}
	
	function GetProfileSource($code, $UserID, $format){
		global $objDatabase;
		
		$queryPN = "select   profile_setup_".$format." as TxTValue from ".DBPREFIX."module_newsletter_config where id=1";
		$objResultPN = $objDatabase->Execute($queryPN);
		if ($objResultPN !== false) {
			if($format=="html"){
				$ProfileSource = '<a href="'.ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'/index.php?section=newsletter&code='.$code.'&cmd=profile">'.$objResultPN->fields["TxTValue"].'</a>';
			}else{
$ProfileSource = $objResultPN->fields["TxTValue"].'
'.ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'/index.php?section=newsletter&code='.$code.'&cmd=profile';
			}
		}
		
		return $ProfileSource;
	}
	
	function newsletterlist(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_OVERVIEW'];
		$this->_objTpl->loadTemplateFile('newsletter_newsletter_list.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_NEWSLETTER_OVERVIEW']);
		
		$this->_objTpl->setVariable(array(
			'TXT_NEWSLETTER_SUBJECT'	=> $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
			'TXT_FORMAT'				=> $_ARRAYLANG['TXT_FORMAT'],
			'TXT_FROM'					=> $_ARRAYLANG['TXT_FROM'],
			'TXT_DATE'					=> $_ARRAYLANG['TXT_DATE'],
			'TXT_ACTION'				=> $_ARRAYLANG['TXT_ACTION'],
			'TXT_CONFIRM_DELETE_DATA'	=> $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
			'TXT_NEWSLETTER_EDIT_SEND'	=> $_ARRAYLANG["TXT_NEWSLETTER_EDIT_SEND"],
			'TXT_NEWSLETTER_NEW'		=> $_ARRAYLANG["TXT_NEWSLETTER_NEW"]
			));
		
		
		// Delete
		// --------------------------
		if($_POST["NewsletterDelete"]=="exe"){
			$NewsletterID = $_POST["NewsletterID"];
			$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_attachment where newsletter=".$NewsletterID."");
			$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_tmp_sending where newsletter=".$NewsletterID."");
			$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter where id=".$NewsletterID."");
			$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
		}	
		
		// Create
		// --------------------------
		if($_POST["NewsletterCreate"]=="exe"){
			
			$NewsletterID = $_POST["NewsletterID"];
			
			$queryValues = "select * from ".DBPREFIX."module_newsletter where id=".$NewsletterID."";
			$objResultValues = $objDatabase->Execute($queryValues);
			if ($objResultValues !== false) {
				
				$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter 
												(subject, template, content, attachment, format, sender_email, sender_name, return_path, status, date_create, priority, content_text)
												VALUES ('".$objResultValues->fields['subject']." (copy)', '".$objResultValues->fields['template']."', '".$objResultValues->fields['content']."', '".$objResultValues->fields['attachment']."', '".$objResultValues->fields['format']."', '".$objResultValues->fields['sender_email']."', '".$objResultValues->fields["sender_name"]."', '".$objResultValues->fields["return_path"]."', 0, '".$this->DateForDB()."', '".$objResultValues->fields["priority"]."', '".$objResultValues->fields["content_text"]."')");
				
			}
			
			$queryPS = "select LAST_INSERT_ID() as lastid from ".DBPREFIX."module_newsletter";
			$objResultPS = $objDatabase->Execute($queryPS);
			if ($objResultPS !== false) {
				$CreatedID = $objResultPS->fields['lastid'];
			}
			
			$queryCS 		= "SELECT * FROM ".DBPREFIX."module_newsletter_rel_cat_news where newsletter=".$NewsletterID."";
			$objResultCS 	= $objDatabase->Execute($queryCS);
			if ($objResultCS !== false) {
				while (!$objResultCS->EOF) {
					$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_cat_news 
												(newsletter, category)VALUES ('".$CreatedID."', '".$objResultCS->fields['category']."')");
					$objResultCS->MoveNext();
				}
			}
			
			$queryCS 		= "SELECT * FROM ".DBPREFIX."module_newsletter_attachment where newsletter=".$NewsletterID."";
			$objResultCS 	= $objDatabase->Execute($queryCS);
			if ($objResultCS !== false) {
				while (!$objResultCS->EOF) {
					$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_attachment 
												(newsletter, file_name, file_nr)VALUES ('".$CreatedID."', '".$objResultCS->fields['file_name']."', '".$objResultCS->fields['file_nr']."')");
					$objResultCS->MoveNext();
				}
			}
			$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];
			
		}
		
		$query 		= "SELECT id, subject, format, date_create, sender_email, sender_name, template, status, count FROM ".DBPREFIX."module_newsletter where 1=1 order by status, id desc";
		$objResult 	= $objDatabase->Execute($query);
		$count 		= $objResult->RecordCount();
		
		$this->_objTpl->setCurrentBlock('newsletter_list');
		$RowClass = "row1";
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				
				if($objResult->fields['status']==1){
					$StatusImg = '<img src="/admin/images/icons/led_green.gif" width="13" height="13" border="0" alt="'.$_ARRAYLANG['TXT_ACTIVE'].'">';
				}else{
					$StatusImg = '<img src="/admin/images/icons/led_red.gif" width="13" height="13" border="0" alt="'.$_ARRAYLANG['TXT_OPEN_ISSUE'].'">';
				} 
				
				/*
				$queryCS 		= "SELECT user FROM ".DBPREFIX."module_newsletter_rel_cat_news right join ".DBPREFIX."module_newsletter_rel_user_cat on ".DBPREFIX."module_newsletter_rel_cat_news.category=".DBPREFIX."module_newsletter_rel_user_cat.category right join contrexx_module_newsletter_user on contrexx_module_newsletter_rel_user_cat.user=contrexx_module_newsletter_user.id and contrexx_module_newsletter_user.status = 1 where newsletter=".$objResult->fields['id']." group by user";
				$objResultCS 	= $objDatabase->Execute($queryCS);
				$countGUC 		= $objResultCS->RecordCount();
				*/
				/*
				$queryCS 		= "SELECT COUNT(*) as anzahl FROM ".DBPREFIX."module_newsletter_rel_cat_news right join ".DBPREFIX."module_newsletter_rel_user_cat on ".DBPREFIX."module_newsletter_rel_cat_news.category=".DBPREFIX."module_newsletter_rel_user_cat.category right join contrexx_module_newsletter_user on contrexx_module_newsletter_rel_user_cat.user=contrexx_module_newsletter_user.id and contrexx_module_newsletter_user.status = 1 where newsletter=".$objResult->fields['id']." group by ".DBPREFIX."module_newsletter_rel_user_cat.user";
				$objResultCS 	= $objDatabase->Execute($queryCS);
				if ($objResultCS !== false) {
					$countGUC = $objResultCS->fields['anzahl'];
				}
				*/
				$CatWhere = '';
				$queryCS 		= "SELECT category FROM ".DBPREFIX."module_newsletter_rel_cat_news where newsletter=".$objResult->fields['id']."";
				$objResultCS 	= $objDatabase->Execute($queryCS);
				if ($objResultCS !== false) {
					while (!$objResultCS->EOF) {
						$CatWhere .= ' and category='.$objResultCS->fields['category'].' ';
						$objResultCS->MoveNext();
					}
				}
				$queryCS 		= "SELECT user FROM ".DBPREFIX."module_newsletter_rel_user_cat where 1=1 ".$CatWhere." group by user";
				$objResultCS 	= $objDatabase->Execute($queryCS);
				$countGUC 		= $objResultCS->RecordCount();
				
				if($objResult->fields['count']<$countGUC){
					$editLink = '<a href="index.php?cmd=newsletter&act=newsletterupdate&id='.$objResult->fields['id'].'"><img src="/admin/images/icons/edit.gif" border="0" alt="'.$_ARRAYLANG['TXT_EDIT'].'" title="'.$_ARRAYLANG['TXT_EDIT'].'"></a>';
					$versendenLink = '<a href="index.php?cmd=newsletter&act=newslettersend&id='.$objResult->fields['id'].'"><img src="/admin/images/icons/email.gif" border="0" alt="'.$_ARRAYLANG['TXT_SEND_NEWSLETTER'].'" title="'.$_ARRAYLANG['TXT_SEND_NEWSLETTER'].'"></a>';
				}else{
					$editLink = '';
					$versendenLink = '';
				}
				
				$this->_objTpl->setVariable(array(
					'NEWSLETTER_ID'					=>$objResult->fields['id'],
	                'NEWSLETTER_SUBJECT'			=>$objResult->fields['subject'],
	                'NEWSLETTER_SENDER_NAME'		=>$objResult->fields['sender_name'],
	                'NEWSLETTER_SENDER_EMAIL'		=>$objResult->fields['sender_email'],
	                'NEWSLETTER_FORMAT'				=>$objResult->fields['format'],
	                'NEWSLETTER_TEMPLATE'			=>$this->GetTemplateName($objResult->fields['template']),
	                'NEWSLETTER_DATE'				=>$objResult->fields['date_create'],
	                'NEWSLETTER_COUNT'				=>$objResult->fields['count']."",
	                'ROW_CLASS'						=>$RowClass,
	                'STATUS_IMG'					=>$StatusImg,
	                'TXT_EDIT'						=> $_ARRAYLANG['TXT_EDIT'],
	                'TXT_DELETE'					=> $_ARRAYLANG['TXT_DELETE'],
	                'TXT_NEWSLETTER_SENT_NEWSLETTERS'=> $_ARRAYLANG['TXT_NEWSLETTER_SENT_NEWSLETTERS'],
	                'NEWSLETTER_USERS'				=> $countGUC,
	                'EDIT_LINK'						=> $editLink,
	                'TXT_STORE_AS_NEW_TEMPLATE'		=> $_ARRAYLANG['TXT_STORE_AS_NEW_TEMPLATE'],
	                'VERSENDEN_LINK'				=> $versendenLink
				));
				if($RowClass=="row1"){
					$RowClass = "row2";
				}else{
					$RowClass = "row1";
				}
				$this->_objTpl->parse("newsletter_list");
				$objResult->MoveNext();
			}
		}
	}
	
	function newsletterinsert(){
		global $objDatabase, $_ARRAYLANG;
		
		if($_POST["ActMode"]=="insert"){
			if($_FILES['datei1']['name']!="" || $_FILES['datei1']['name']!="" || $_FILES['datei1']['name']!=""){
				$attachment = 1;
			}else{
				$attachment = 0;
			}
			$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter 
												(subject, template, content, attachment, format, sender_email, sender_name, return_path, status, date_create, priority, content_text)
												VALUES ('".$_POST["subject"]."', '".$_POST["template"]."', '".$_POST["content"]."', '".$attachment."', '".$_POST["format"]."', '".$_POST["sender_email"]."', '".$_POST["sender_name"]."', '".$_POST["sender_replay"]."', 0, '".$this->DateForDB()."', '".$_POST["priority"]."', '".$_POST["content_text"]."')");
	
			$queryPS = "select LAST_INSERT_ID() as lastid from ".DBPREFIX."module_newsletter";
			$objResultPS = $objDatabase->Execute($queryPS);
			if ($objResultPS !== false) {
				$LastID = $objResultPS->fields['lastid'];
			}
			
			// InsertCategorys
			// ----------------------------
			$queryCS 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
			$objResultCS 	= $objDatabase->Execute($queryCS);
			if ($objResultCS !== false) {
				$CategorysFounded = 1;
				while (!$objResultCS->EOF) {
					if($_POST["cat_".$objResultCS->fields['id']]=="yes"){
						$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_cat_news 
									(newsletter, category) VALUES ('".$LastID."', '".$objResultCS->fields['id']."')");
		
					}
					$objResultCS->MoveNext();
				}
			}
			
			for($i=1; $i<=3; $i++){
				if($_FILES['datei'.$i.'']['name']!=""){
					$NewFileName = ASCMS_DOCUMENT_ROOT.'/modules/newsletter/upload/'.$_FILES['datei'.$i.'']['name'];
					move_uploaded_file($_FILES['datei'.$i.'']['tmp_name'], $NewFileName);
					$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_attachment 
												(newsletter, file_name, file_nr) VALUES (".$LastID.", '".$_FILES['datei'.$i.'']['name']."', ".$i.")");
				}
			}
			
			$this->_statusMessage = $_ARRAYLANG['TXT_NEWSLETTER_SUCCESSFULLY_MADE'];
			
			
			
			$_POST["NewsletterID"] = $LastID;
			$_GET['act']=="newsletterupdate";
			
		} 
		if($_POST["ActMode"]=="update"){
			if($_POST["AttachChange"]=="yes"){
				$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_attachment where id=".$_POST["FileID"]."");
			}
			$LastID = $_POST["NewsletterID"];
			$NewsletterID = $_POST["NewsletterID"];
			for($i=1; $i<=3; $i++){
				
				if($_FILES['datei'.$i.'']['name']!=""){
					$NewFileName = ASCMS_DOCUMENT_ROOT.'/modules/newsletter/upload/'.$_FILES['datei'.$i.'']['name'];
					move_uploaded_file($_FILES['datei'.$i.'']['tmp_name'], $NewFileName);
					$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_attachment 
												(newsletter, file_name, file_nr) VALUES (".$LastID.", '".$_FILES['datei'.$i.'']['name']."', ".$i.")");
				}
			}
			
			$objResult 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter
												SET subject='".$_POST["subject"]."', content='".$_POST["content"]."', template='".$_POST["template"]."', format='".$_POST["format"]."', sender_email='".$_POST["sender_email"]."', sender_name='".$_POST["sender_name"]."', return_path='".$_POST["sender_replay"]."', priority='".$_POST["priority"]."', content_text='".$_POST["content_text"]."' where id='".$LastID."'");
			
			// UpdateCategorys
			// ----------------------------
			$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_cat_news where newsletter=".$LastID."");
			$queryCS 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
			$objResultCS 	= $objDatabase->Execute($queryCS);
			if ($objResultCS !== false) {
				$CategorysFounded = 1;
				while (!$objResultCS->EOF) {
					if($_POST["cat_".$objResultCS->fields['id']]=="yes"){
						$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_cat_news 
									(newsletter, category) VALUES ('".$LastID."', '".$objResultCS->fields['id']."')");
			
					}
					$objResultCS->MoveNext();
				}
			}
			
			$this->_statusMessage = $_ARRAYLANG['TXT_NEWSLETTER_SUCCESSFULLY_UPDATE'];
		
		}
		
		if($_GET['act']=="newsletterupdate" || $_POST["ActMode"]=="update"){
			$Title_txt = $_ARRAYLANG['TXT_EDIT'];
		}else{
			$Title_txt = $_ARRAYLANG['TXT_NEWSLETTER_NEW'];
		}
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_NEW'];
		$this->_objTpl->loadTemplateFile('newsletter_newsletter_insert.html');
		$this->_objTpl->setVariable('TXT_TITLE', $Title_txt);
		
		if($_GET['act']=="newsletterinsert"){
			$ActMode = "insert";
		}
		if($_GET['act']=="newsletterupdate"){
			$ActMode = "update";
			$NewsletterID = $_POST["NewsletterID"];
			if($NewsletterID==""){
				$NewsletterID = $_GET["id"];
			}
		
			
			$query 		= "SELECT * FROM ".DBPREFIX."module_newsletter where id='".$NewsletterID."'";
			$objResult 	= $objDatabase->Execute($query);
			if ($objResult !== false) {
				
				$value_subject = $objResult->fields['subject'];
				$value_content = $objResult->fields['content'];
				$value_content_text = $objResult->fields['content_text'];
				$value_priority = $objResult->fields['priority'];
				$EmailFormatDB 	= $objResult->fields['format'];
				
				if($objResult->fields['format']=="html/text"){
					$value_html_text = "selected";
				}
				if($objResult->fields['format']=="html"){
					$value_html = "selected";
				}
				if($objResult->fields['format']=="text"){
					$value_text = "selected";
				}
				
			}
			
			$EmailFormat = $_POST['format'];
			if($EmailFormat==""){
				$EmailFormat = $EmailFormatDB;
			}
			
			switch ($EmailFormat){
				case "html/text":
					$value_display_html = "";
					$value_display_txt = "";
				break;
				case "html":
					$value_display_html = "";
					$value_display_txt = "none";
				break;
				case "text":
					$value_display_html = "none";
					$value_display_txt = "";
				break;
				default:
					$value_display_html = "";
					$value_display_txt = "";
				break;
			}
		}
		
		// Load Values 4 Insert
		$query 		= "SELECT * FROM ".DBPREFIX."module_newsletter where id='".$NewsletterID."'";
		$objResult 	= $objDatabase->Execute($query);
		if ($objResult !== false) {
			$value_sender_email = $objResult->fields['sender_email'];
			$value_sender_name = $objResult->fields['sender_name'];
			$value_return_path = $objResult->fields['return_path'];
		}
		// DefaultValue
		$query 		= "SELECT * FROM ".DBPREFIX."module_newsletter_config WHERE 1";
		$objResult 	= $objDatabase->Execute($query);
		if ($objResult !== false) {
			$value_sender_emailDEF = $objResult->fields['sender_email'];
			$value_sender_nameDEF = $objResult->fields['sender_name'];
			$value_return_pathDEF = $objResult->fields['return_path'];
		}
		if($value_sender_email==""){
			$value_sender_email = $value_sender_emailDEF;
		}
		if($value_sender_name==""){
			$value_sender_name = $value_sender_nameDEF;
		}
		if($value_return_path==""){
			$value_return_path = $value_return_pathDEF;
		}
		
		switch ($value_priority){
				case 1:
					$value_priority_1 = "selected";
					$value_priority_2 = "";
					$value_priority_3 = "";
					$value_priority_4 = "";
					$value_priority_5 = "";
				break;
				case 2:
					$value_priority_1 = "";
					$value_priority_2 = "selected";
					$value_priority_3 = "";
					$value_priority_4 = "";
					$value_priority_5 = "";
				break;
				case 3:
					$value_priority_1 = "";
					$value_priority_2 = "";
					$value_priority_3 = "selected";
					$value_priority_4 = "";
					$value_priority_5 = "";
				break;
				case 4:
					$value_priority_1 = "";
					$value_priority_2 = "";
					$value_priority_3 = "";
					$value_priority_4 = "selected";
					$value_priority_5 = "";
				break;
				case 5:
					$value_priority_1 = "";
					$value_priority_2 = "";
					$value_priority_3 = "";
					$value_priority_4 = "";
					$value_priority_5 = "selected";
				break;
				default:
					$value_priority_1 = "";
					$value_priority_2 = "";
					$value_priority_3 = "selected";
					$value_priority_4 = "";
					$value_priority_5 = "";
				break;
			}
			
			// New Newsletter
			if($_POST["ActMode"]=="" && $_REQUEST["act"]=="newsletterinsert"){
				$value_display_html = "block";
				$value_display_txt = "block";
				$value_html_text = "selected";
				$value_content = "<-- profile_setup --><br/><-- unsubscribe -->";
				$value_content_text = "<-- profile_setup -->".chr(13).chr(10)."<-- unsubscribe -->";
			}
		
		
		$this->_objTpl->setVariable(array(
			'TXT_NEWSLETTER_SUBJECT'	=> $_ARRAYLANG['TXT_NEWSLETTER_SUBJECT'],
			'TXT_FORMAT'				=> $_ARRAYLANG['TXT_FORMAT'],
			'VALUE_SUBJECT'				=> $value_subject,
			'VALUE_HTML_TEXT'			=> $value_html_text,
			'VALUE_HTML'				=> $value_html,
			'VALUE_TEXT'				=> $value_text,
			'VALUE_NEWSLETTER_ID'		=> $NewsletterID,
			'CONTENTAREA'				=> get_wysiwyg_editor('content', $value_content, 'newsletter'),
			'VALUE_CONTENT_TEXT'		=> $value_content_text,
			'TEMPLATE_SELECT'			=> $this->GetTemplateSelect(),
			'TXT_NEWSLETTER_CONTENT' 	=> $_ARRAYLANG['TXT_NEWSLETTER_CONTENT'],
			'TXT_ATTACHMENT'	 		=> $_ARRAYLANG['TXT_ATTACHMENT'],
			'ATTACHMENT_VALUES'  		=> $this->GetAttachmentValues(),
			'TXT_RESET'	 				=> $_ARRAYLANG['TXT_RESET'],
			'TXT_SAVE'		 			=> $_ARRAYLANG['TXT_SAVE'],
			'ACT_MODE'					=> $ActMode,
			'TXT_CONFIRM_DELETE_DATA' 	=> $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
			'TXT_SENDER' 				=> $_ARRAYLANG['TXT_SENDER'],
			'TXT_LASTNAME' 				=> $_ARRAYLANG['TXT_LASTNAME'],
			'VALUE_SENDER_MAIL'			=> $value_sender_email,
			'VALUE_SENDER_NAME'			=> $value_sender_name,
			'VALUE_SENDER_REPLAY'		=> $value_return_path,
			'CATEGORY_SELECT'			=> $this->CategorySelect($NewsletterID),
			'TXT_PRIORITY' 				=> $_ARRAYLANG['TXT_PRIORITY'],
			'TXT_HIGHEST' 				=> $_ARRAYLANG['TXT_HIGHEST'],
			'TXT_HIGH' 					=> $_ARRAYLANG['TXT_HIGH'],
			'TXT_NORMAL' 				=> $_ARRAYLANG['TXT_NORMAL'],
			'TXT_LOW' 					=> $_ARRAYLANG['TXT_LOW'],
			'TXT_LOWEST' 				=> $_ARRAYLANG['TXT_LOWEST'],
			'DISPLAY_HTML'				=> $value_display_html,
			'DISPLAY_TXT'				=> $value_display_txt,
			'VALUE_PRIORITY_1'			=> $value_priority_1,
			'VALUE_PRIORITY_2'			=> $value_priority_2,
			'VALUE_PRIORITY_3'			=> $value_priority_3,
			'VALUE_PRIORITY_4'			=> $value_priority_4,
			'VALUE_PRIORITY_5'			=> $value_priority_5,
			'TXT_NEWSLETTER_EDIT_SEND'	=> $_ARRAYLANG["TXT_NEWSLETTER_EDIT_SEND"],
			'TXT_NEWSLETTER_NEW'		=> $_ARRAYLANG["TXT_NEWSLETTER_NEW"],
			'TXT_WILDCART_INFOS'		=> $_ARRAYLANG['TXT_WILDCART_INFOS'],
			'TXT_USER_DATA'				=> $_ARRAYLANG["TXT_USER_DATA"],
			'TXT_EMAIL_ADDRESS'			=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_LASTNAME'				=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_FIRSTNAME'				=> $_ARRAYLANG['TXT_FIRSTNAME'],
			'TXT_STREET'				=> $_ARRAYLANG['TXT_STREET'],
			'TXT_ZIP'					=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_CITY'					=> $_ARRAYLANG['TXT_CITY'],
			'TXT_COUNTRY'				=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_PHONE'					=> $_ARRAYLANG['TXT_PHONE'],
			'TXT_BIRTHDAY'				=> $_ARRAYLANG['TXT_BIRTHDAY'],
			'TXT_GENERALLY'				=> $_ARRAYLANG['TXT_GENERALLY'],
			'TXT_PROFILE_SETUP'			=> $_ARRAYLANG['TXT_PROFILE_SETUP'],
			'TXT_UNSUBSCRIBE'			=> $_ARRAYLANG['TXT_UNSUBSCRIBE'],
			'TXT_DATE'					=> $_ARRAYLANG['TXT_DATE'],
			'TXT_NEWSLETTER_CONTENT'	=> $_ARRAYLANG['TXT_NEWSLETTER_CONTENT']
			));
	}
	
	function exportuser(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_EXPORT'];
		$this->_objTpl->loadTemplateFile('newsletter_user_export.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_SETTINGS']);
		
		if($_POST["export"]=="exe"){
			
			$this->_pageTitle = $_ARRAYLANG['TXT_EXPORT'];
			$this->_objTpl->loadTemplateFile('newsletter_user_export_liste.html');
			$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_EXPORT']);
			
			
			// Export nach kategorien
			$WhereStatement = " and (";
			if (isset($_POST['selectedCat'])) {
				foreach ($_POST['selectedCat'] as $intKey => $intCatId) {
					if($WhereStatement==" and ("){
						$NextCar = "";
					}else{
						$NextCar = " or ";
					}
					$WhereStatement .= $NextCar." category=".$intCatId." ";
					//$WhereStatement .= " left join ".DBPREFIX."module_newsletter_rel_user_cat on ".DBPREFIX."module_newsletter_rel_user_cat.id=".$intCatId;
				}
			}
			$WhereStatement .= ")";
			//$query		= "SELECT * FROM ".DBPREFIX."module_newsletter_user ".$WhereStatement." where 1=1 GROUP BY ".DBPREFIX."module_newsletter_rel_user_cat.user";
			
			$query		= "SELECT * FROM ".DBPREFIX."module_newsletter_rel_user_cat right join ".DBPREFIX."module_newsletter_user on ".DBPREFIX."module_newsletter_rel_user_cat.user=".DBPREFIX."module_newsletter_user.id where 1=1 ".$WhereStatement." GROUP BY user";
			$objResult 	= $objDatabase->Execute($query);
			$StringForFile = '';
			$separetor = $_POST["separetor"];
			if ($objResult !== false) {
				while (!$objResult->EOF) {
	
					if($_POST["email"]=="1"){
						$StringForFile .= $objResult->fields['email'].$separetor;
					}
					if($_POST["lastname"]=="1"){
						$StringForFile .= $objResult->fields['lastname'].$separetor;
					}
					if($_POST["firstname"]=="1"){
						$StringForFile .= $objResult->fields['firstname'].$separetor;
					}
					if($_POST["street"]=="1"){
						$StringForFile .= $objResult->fields['street'].$separetor;
					}
					if($_POST["zip"]=="1"){
						$StringForFile .= $objResult->fields['zip'].$separetor;
					}
					if($_POST["city"]=="1"){
						$StringForFile .= $objResult->fields['city'].$separetor;
					}
					if($_POST["country"]=="1"){
						$StringForFile .= $objResult->fields['country'].$separetor;
					}
					if($_POST["phone"]=="1"){
						$StringForFile .= $objResult->fields['phone'].$separetor;
					}
					if($_POST["birthday"]=="1"){
						$StringForFile .= $objResult->fields['birthday'].$separetor;
					}
					$StringForFile .= chr(13).chr(10);
					$objResult->MoveNext();
				}
			}
			
			$this->_objTpl->setVariable('USER_LIST', $StringForFile);
			
			
			/* -----------------------------------------
			Vorbereitet für schreiben ins Textfile
			
			$TemplateFile = ASCMS_MODULE_PATH.'/newsletter/download/users.export';
			$SourceFile = ASCMS_MODULE_PATH.'/newsletter/download/'.date("d").date("m").date("Y")."_".date("h").date("i").date("s").".txt";
			copy($TemplateFile, $SourceFile);
			
			$fp = fopen($SourceFile,"w");
			
			if($_POST["active"]!="1"){
				if($_POST["inactive"]!="1"){
					$WhereStatement = " and status='' ";
				}else{
					$WhereStatement = " and status=0 ";
				}
			}else{
				if($_POST["inactive"]=="1"){
					$WhereStatement = '';
				}else{
					$WhereStatement = ' and status=1 ';
				}
			}
			
			$query		= "SELECT id, email, lastname, firstname, street, zip, city, country, status, phone, birthday FROM ".DBPREFIX."module_newsletter_user where 1=1 ".$WhereStatement." ";
			$objResult 	= $objDatabase->Execute($query);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					
					$StringForFile = '';
					if($_POST["email"]=="1"){
						$StringForFile .= $objResult->fields['email'].',';
					}
					if($_POST["lastname"]=="1"){
						$StringForFile .= $objResult->fields['lastname'].',';
					}
					if($_POST["firstname"]=="1"){
						$StringForFile .= $objResult->fields['firstname'].',';
					}
					if($_POST["street"]=="1"){
						$StringForFile .= $objResult->fields['street'].',';
					}
					if($_POST["zip"]=="1"){
						$StringForFile .= $objResult->fields['zip'].',';
					}
					if($_POST["city"]=="1"){
						$StringForFile .= $objResult->fields['city'].',';
					}
					if($_POST["country"]=="1"){
						$StringForFile .= $objResult->fields['country'].',';
					}
					if($_POST["phone"]=="1"){
						$StringForFile .= $objResult->fields['phone'].',';
					}
					if($_POST["birthday"]=="1"){
						$StringForFile .= $objResult->fields['birthday'].',';
					}
					//$StringForFile .= '';
					fwrite($fp, $StringForFile, strlen($StringForFile));
					
					$objResult->MoveNext();
					
				}
			}
			fclose($fp);
			
			----------------------------------------- */
			
		}
		$category_values = '';
		$query 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category order by name";
		$objResult 	= $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$category_values .= '<input type="checkbox" checked name="selectedCat[]" value="'.$objResult->fields['id'].'"> '.$objResult->fields['name']."<br/>";
				$objResult->MoveNext();
			}
		}
		
		$this->_objTpl->setVariable(array(
			'TXT_EMAIL_ADDRESS'		=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_LASTNAME'			=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_FIRSTNAME'			=> $_ARRAYLANG['TXT_FIRSTNAME'],
			'TXT_STREET'			=> $_ARRAYLANG['TXT_STREET'],
			'TXT_ZIP'				=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_CITY'				=> $_ARRAYLANG['TXT_CITY'],
			'TXT_COUNTRY'			=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_PHONE'				=> $_ARRAYLANG['TXT_PHONE'],
			'TXT_BIRTHDAY'			=> $_ARRAYLANG['TXT_BIRTHDAY'],
			'TXT_OPEN_ISSUE'		=> $_ARRAYLANG['TXT_OPEN_ISSUE'],
			'TXT_ACTIVE'			=> $_ARRAYLANG['TXT_ACTIVE'],
			'TXT_EXPORT'			=> $_ARRAYLANG['TXT_EXPORT'],
			'TXT_CHOOSE_SEPERATOR' 	=> $_ARRAYLANG['TXT_CHOOSE_SEPERATOR'],
			'TXT_SELECT_ALL'		=> $_ARRAYLANG['TXT_SELECT_ALL'],
			'TXT_EDIT'				=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'				=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'			=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'			=> $_ARRAYLANG['TXT_EXPORT'],
			'TXT_NEWSLETTER_CATEGORYS'=> $_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'],
			'CATEGORY_VALUES'		=> $category_values
			));
		
	}
	
	function importuser(){
		global $objDatabase, $_ARRAYLANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_IMPORT'];
		$this->_objTpl->loadTemplateFile('newsletter_user_import.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_UPLOAD_FILE']);
		
		if($_POST["import"]=="exe"){
			$GSdatei_name = $_FILES['GSdatei']['name'];
			
			if($GSdatei_name!=""){
				$_FILES['GSdatei']['name'] = str_replace("%","",$_FILES['GSdatei']['name']); 
				move_uploaded_file($_FILES['GSdatei']['tmp_name'], ASCMS_DOCUMENT_ROOT."/tmp/".$_FILES['GSdatei']['name']);
				
				$LineArray = file(ASCMS_DOCUMENT_ROOT."/tmp/".$_FILES['GSdatei']['name']);
				$LinesCount = count($LineArray);
				$EmailCount = 0;
				$BadEmails = 0;
				$ExistEmails = 0;
				$NewEmails = 0;
				
				foreach ($LineArray as $LineValue) {
					list ($email, $lastname, $firstname, $street, $plz, $city, $country, $phone, $birthday) = split('[,;]', $LineValue);
					if($this->check_email($email)!=1){
						$BadEmails ++;
					}else{
						$EmailCount ++;
						$queryCHECK 		= "SELECT id FROM ".DBPREFIX."module_newsletter_user where email='".$email."'";
						$objResultCHECK 	= $objDatabase->Execute($queryCHECK);
						$count 		= $objResultCHECK->RecordCount();
						if ($count > 0) {
							$ExistEmails ++;
						}else{
							
							$NewEmails ++;
							$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_user 
													(code, email, lastname, firstname, street, zip, city, country, phone, birthday, status, emaildate)
													VALUES ('".$this->EmailCode()."', '".$email."', '".htmlspecialchars(strip_tags($lastname))."', '".htmlspecialchars(strip_tags($firstname))."', '".htmlspecialchars(strip_tags($street))."', '".htmlspecialchars(strip_tags($zip))."', '".htmlspecialchars(strip_tags($city))."', '".htmlspecialchars(strip_tags($country))."', '".htmlspecialchars(strip_tags($phone))."', '".htmlspecialchars(strip_tags($birthday))."', 1, '".$this->DateForDB()."')");
							
							$queryPS = "select LAST_INSERT_ID() as lastid from ".DBPREFIX."module_newsletter_user";
							$objResultPS = $objDatabase->Execute($queryPS);
							if ($objResultPS !== false) {
								$CreatedID = $objResultPS->fields['lastid'];
							}
							
							if($_REQUEST['category']==""){
								$queryIC 		= "SELECT id FROM ".DBPREFIX."module_newsletter_category";
								$objResultIC 	= $objDatabase->Execute($queryIC);
								if ($objResultIC !== false) {
									while (!$objResultIC->EOF) {
										$objResultICR 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
														(user, category)
														VALUES (".$CreatedID.", ".$objResultIC->fields['id'].")");
										$objResultIC->MoveNext();
									}
								}
							}else{
								$objResultICR 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
												(user, category)
												VALUES (".$CreatedID.", ".$_REQUEST['category'].")");
							}
						
						}
					}
				}
				$this->_statusMessage = $_ARRAYLANG['TXT_DATA_IMPORT_SUCCESSFUL']."<br/>".$_ARRAYLANG['TXT_LINES_NUMBER'].": ".$LinesCount."<br/>".$_ARRAYLANG['TXT_CORRECT_EMAILS'].": ".$EmailCount."<br/>".$_ARRAYLANG['TXT_NOT_VALID_EMAILS'].": ".$BadEmails."<br/>".$_ARRAYLANG['TXT_EXISTING_EMAILS'].": ".$ExistEmails."<br/>".$_ARRAYLANG['TXT_NEW_ADDED_EMAILS'].": ".$NewEmails;
			}elseif($_REQUEST["Emails"]!=""){
				$NLine = chr(13).chr(10);
				$EmailArray = split('[,;'.$NLine.']', $_REQUEST["Emails"]);
				
				$LinesCount = count($EmailArray);
				$EmailCount = 0;
				$BadEmails = 0;
				$ExistEmails = 0;
				$NewEmails = 0;
				
				foreach ($EmailArray as $email) {
					if($this->check_email($email)!=1){
						$BadEmails ++;
					}else{
						$EmailCount ++;
						$queryCHECK 		= "SELECT id FROM ".DBPREFIX."module_newsletter_user where email='".$email."'";
						$objResultCHECK 	= $objDatabase->Execute($queryCHECK);
						$count 		= $objResultCHECK->RecordCount();
						if ($count > 0) {
							$ExistEmails ++;
						}else{
							$NewEmails ++;
							$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_user 
														(code, email, status, emaildate)
														VALUES ('".$this->EmailCode()."', '".$email."', 1, '".$this->DateForDB()."')");
							$queryPS = "select LAST_INSERT_ID() as lastid from ".DBPREFIX."module_newsletter_user";
							$objResultPS = $objDatabase->Execute($queryPS);
							if ($objResultPS !== false) {
								$CreatedID = $objResultPS->fields['lastid'];
							}
							if($_REQUEST['category']==""){
								$queryIC 		= "SELECT id FROM ".DBPREFIX."module_newsletter_category";
								$objResultIC 	= $objDatabase->Execute($queryIC);
								if ($objResultIC !== false) {
									while (!$objResultIC->EOF) {
										$objResultICR 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
														(user, category)
														VALUES (".$CreatedID.", ".$objResultIC->fields['id'].")");
										$objResultIC->MoveNext();
									}
								}
							}else{
								$objResultICR 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
												(user, category)
												VALUES (".$CreatedID.", ".$_REQUEST['category'].")");
							}
							
						}
					}
				}
				$this->_statusMessage = $_ARRAYLANG['TXT_DATA_IMPORT_SUCCESSFUL']."<br/>".$_ARRAYLANG['TXT_LINES_NUMBER'].": ".$LinesCount."<br/>".$_ARRAYLANG['TXT_CORRECT_EMAILS'].": ".$EmailCount."<br/>".$_ARRAYLANG['TXT_NOT_VALID_EMAILS'].": ".$BadEmails."<br/>".$_ARRAYLANG['TXT_EXISTING_EMAILS'].": ".$ExistEmails."<br/>".$_ARRAYLANG['TXT_NEW_ADDED_EMAILS'].": ".$NewEmails;
			}
			
		}
		
		/*for($x=39; $x<=188; $x++){
			//echo("test".$x."@helpgui.ch,lastname".$x.",firstname".$x."<br/>");
			echo("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat (user, category) VALUES (".$x.", 4);<br>");
		}*/
		
		$this->_objTpl->setVariable(array(
			'JAVASCRIPTCODE'		=> $this->JSimportuser(),
			'TXT_EDIT'		=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'		=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'	=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'	=> $_ARRAYLANG['TXT_EXPORT'],
			'TXT_HELP'		=> $_ARRAYLANG['TXT_HELP'],
			'TXT_IMPORT_HELP'=> $_ARRAYLANG['TXT_IMPORT_HELP'],
			'CATEGORY_DROPDOWN' => $this->CategoryDropDown(),
			'TXT_IMPORT_IN_CATEGORY' => $_ARRAYLANG['TXT_IMPORT_IN_CATEGORY'],
			'TXT_ENTER_EMAIL_ADDRESS' => $_ARRAYLANG['TXT_ENTER_EMAIL_ADDRESS']
		));
	}
	
	function check_email($email) {
		return eregi("^[a-z0-9]+([-_\.]?[a-z0-9])+@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}", $email);
	} 
	
	function overview(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];
		$this->_objTpl->loadTemplateFile('newsletter_overview.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_OVERVIEW']);
		
		$this->_objTpl->setVariable(array(
			'TXT_NEWSLETTER_NOT_SENT_NEWSLETTERS'		=> $_ARRAYLANG['TXT_NEWSLETTER_NOT_SENT_NEWSLETTERS'],
			'TXT_NEWSLETTER_SENT_NEWSLETTERS'			=> $_ARRAYLANG['TXT_NEWSLETTER_SENT_NEWSLETTERS'],
			'TXT_NEWSLETTER_ANNOUNCED_USERS'			=> $_ARRAYLANG['TXT_NEWSLETTER_ANNOUNCED_USERS'],
			'TXT_NEWSLETTER_EDIT'		=> $_ARRAYLANG['TXT_NEWSLETTER_EDIT'],
			'TXT_NEWSLETTER_NEW'		=> $_ARRAYLANG['TXT_NEWSLETTER_NEW'],
			'TXT_SEND_NEWSLETTER'		=> $_ARRAYLANG['TXT_SEND_NEWSLETTER'],
			'TXT_EDIT'		=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'		=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'	=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'	=> $_ARRAYLANG['TXT_EXPORT'],
			'TXT_TITLE_NEWSLETTER'	=> $_ARRAYLANG['TXT_NEWSLETTER'],
			'TXT_TITLE_SETTINGS'	=> $_ARRAYLANG['TXT_SETTINGS'],
			'TXT_TITLE_USER'		=> $_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION'],
			'TXT_TITLE_CATEGORY'	=> $_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'],
			'USER_COUNT'			=> $this->UserCount(),
			'VALUE_SENT_NEWSLETTERS'=> $this->NewsletterSendCount(),
			'VALUE_NOT_SENT_NEWSLETTERS'=> $this->NewsletterNotSendCount(),
			'TXT_NEWSLETTER_EDIT_SEND' => $_ARRAYLANG["TXT_NEWSLETTER_EDIT_SEND"],
			'TXT_DISPATCH_SETINGS'	=> $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
			'TXT_GENERATE_HTML'		=> $_ARRAYLANG['TXT_GENERATE_HTML'],
			'TXT_STATISTICS'		=> $_ARRAYLANG['TXT_STATISTICS'],
			'CAT_STAT'				=> $this->CategoryStat()
			));
			
	}
	
	function newsletterOverview(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER'];
		$this->_objTpl->loadTemplateFile('newsletter_newsletter.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_ACTION']);
		
		$this->_objTpl->setVariable(array(
			'TXT_NEWSLETTER_EDIT'		=> $_ARRAYLANG['TXT_NEWSLETTER_EDIT'],
			'TXT_NEWSLETTER_NEW'		=> $_ARRAYLANG['TXT_NEWSLETTER_NEW'],
			'TXT_SEND_NEWSLETTER'		=> $_ARRAYLANG['TXT_SEND_NEWSLETTER'],
			'TXT_NEWSLETTER_EDIT_SEND' => $_ARRAYLANG["TXT_NEWSLETTER_EDIT_SEND"]
			));
	}

	function userOverview(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION'];
		$this->_objTpl->loadTemplateFile('newsletter_user.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_ACTION']);
		
		
		$this->_objTpl->setVariable(array(
			'TXT_EDIT'		=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'		=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'	=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'	=> $_ARRAYLANG['TXT_EXPORT']
			));
	}
	
	function categoryOverview(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'];
		$this->_objTpl->loadTemplateFile('newsletter_categorys.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_ADD']);
		
		if($_POST["insert"]=="exe"){
			$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_category (name, status) VALUES ('".$_POST["name"]."', 1)");
			$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
		}
		
		if($_POST["updateaction"]=="update"){
			if (isset($_POST['selectedId'])) {
				foreach ($_POST['selectedId'] as $intKey => $intCatId) {
					if($_REQUEST['Stat_'.$intCatId]=="1"){
						$Status = 1;
					}else{
						$Status = 0;
					}
					$objResult 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_category
													SET name='".$_POST["cat_".$intCatId]."', status=".$Status."
													WHERE id=".$intCatId."");
					$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];
				}
			}
		}
		
		if($_POST["updateaction"]=="delete"){
			if (isset($_POST['selectedId'])) {
				foreach ($_POST['selectedId'] as $intKey => $intCatId) {
					$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_category
													WHERE id=".$intCatId."");
					$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];
				}
			}
		}
		
		$this->_objTpl->setVariable(array(
			'TXT_EDIT'				=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_CATEGORY_NAME'		=> $_ARRAYLANG['TXT_CATEGORY_NAME'],
			'TXT_SAVE'				=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_SELECT_ALL'		=> $_ARRAYLANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'		=> $_ARRAYLANG['TXT_DESELECT_ALL'],
			'TXT_SUBMIT_SELECT'		=> $_ARRAYLANG['TXT_SUBMIT_SELECT'],
			'TXT_SUBMIT_DELETE'		=> $_ARRAYLANG['TXT_SUBMIT_DELETE'],
			'TXT_SUBMIT_UPDATE'		=> $_ARRAYLANG['TXT_SUBMIT_UPDATE'],
			'TXT_DELETE_MARKED'		=> $_ARRAYLANG['TXT_DELETE_MARKED'],
			'TXT_NEWSLETTER_VISIBLE' => $_ARRAYLANG['TXT_NEWSLETTER_VISIBLE']
			));
			
		$query 		= "SELECT id, status, name FROM ".DBPREFIX."module_newsletter_category order by name";
		$objResult 	= $objDatabase->Execute($query);
		$count 		= $objResult->RecordCount();
		if($count<1){
			$NoCats = $_ARRAYLANG['TXT_NO_CATEGORIES'];
		}else{
			$NoCats = "";
		}
		$this->_objTpl->setVariable('TXT_NO_CATEGORIES', $_ARRAYLANG['$NoCats']);
		
		$this->_objTpl->setCurrentBlock('categorys_row');
		$RowClass = "row1";
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				
				if($objResult->fields['status']==1){
					$StatusChecked = "checked";
				}else{
					$StatusChecked = '';
				}
				
				$this->_objTpl->setVariable(array(
	                'CATEGORY_ID'			=>$objResult->fields['id'],
	                'CATEGORY_NAME'			=>$objResult->fields['name'],
	                'STATUS_CHECKED'		=>$StatusChecked,
	                'ROW_CLASS'			=>$RowClass
				));
				if($RowClass=="row1"){
					$RowClass = "row2";
				}else{
					$RowClass = "row1";
				}
				$this->_objTpl->parse("categorys_row");
				$objResult->MoveNext();
			}
		}
	}
	
	
	function configOverview(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
		$this->_objTpl->loadTemplateFile('newsletter_configuration.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_SETTINGS']);
		
		
		$this->_objTpl->setVariable(array(
			'TXT_DISPATCH_SETINGS'	=> $_ARRAYLANG['TXT_DISPATCH_SETINGS'],
			'TXT_GENERATE_HTML'		=> $_ARRAYLANG['TXT_GENERATE_HTML']
			));
		
	}
	
	function UserCount(){
		global $objDatabase;
		$objResult_value = $objDatabase->SelectLimit("SELECT 
													count(*) as counter
													FROM ".DBPREFIX."module_newsletter_user 
													", 1);
		if ($objResult_value !== false && !$objResult_value->EOF) {
			return $objResult_value->fields["counter"];
		}else{
			return 0;
		}
	}
	
	function NewsletterSendCount(){
		global $objDatabase;
		$objResult_value = $objDatabase->SelectLimit("SELECT 
													count(*) as counter
													FROM ".DBPREFIX."module_newsletter 
													WHERE status=1", 1);
		if ($objResult_value !== false && !$objResult_value->EOF) {
			return $objResult_value->fields["counter"];
		}else{
			return 0;
		}
	}
	
	function NewsletterNotSendCount(){
		global $objDatabase;
		$objResult_value = $objDatabase->SelectLimit("SELECT 
													count(*) as counter
													FROM ".DBPREFIX."module_newsletter 
													WHERE status=0", 1);
		if ($objResult_value !== false && !$objResult_value->EOF) {
			return $objResult_value->fields["counter"];
		}else{
			return 0;
		}
	}
	
	function detailuser(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION'];
		$this->_objTpl->loadTemplateFile('newsletter_user_detail.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_UPDATE']);
		
		$objResult_value = $objDatabase->SelectLimit("SELECT * FROM ".DBPREFIX."module_newsletter_user where id=".$_GET["id"]."", 1);
		if ($objResult_value !== false && !$objResult_value->EOF) {
			$value_id 			= $objResult_value->fields["id"];
			$value_email 		= $objResult_value->fields["email"];
			$value_lastname 	= $objResult_value->fields["lastname"];
			$value_firstname 	= $objResult_value->fields["firstname"];
			$value_street 		= $objResult_value->fields["street"];
			$value_zip 			= $objResult_value->fields["zip"];
			$value_city 		= $objResult_value->fields["city"];
			$value_country 		= $objResult_value->fields["country"];
			$value_phone 		= $objResult_value->fields["phone"];
			$value_birthday 	= $objResult_value->fields["birthday"];
			$value_status 		= $objResult_value->fields["status"];
		}
		
		if($value_status==1){
			$value_status = "checked";
		}else{
			$value_status = "";
		}
		
		$this->_objTpl->setVariable(array(
			'TXT_EMAIL_ADDRESS'		=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_LASTNAME'			=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_FIRSTNAME'			=> $_ARRAYLANG['TXT_FIRSTNAME'],
			'TXT_STREET'			=> $_ARRAYLANG['TXT_STREET'],
			'TXT_ZIP'				=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_CITY'				=> $_ARRAYLANG['TXT_CITY'],
			'TXT_COUNTRY'			=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_PHONE'				=> $_ARRAYLANG['TXT_PHONE'],
			'TXT_BIRTHDAY'			=> $_ARRAYLANG['TXT_BIRTHDAY'],
			'TXT_STATUS'			=> $_ARRAYLANG['TXT_STATUS'],
			'TXT_SAVE'				=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_MAILERROR'			=> $_ARRAYLANG['TXT_MAILERROR'],
			'VALUE_ID'				=> $value_id,
			'VALUE_EMAIL'			=> $value_email,
			'VALUE_LASTNAME'		=> $value_lastname,
			'VALUE_FIRSTNAME'		=> $value_firstname,
			'VALUE_STREET'			=> $value_street,
			'VALUE_ZIP'				=> $value_zip,
			'VALUE_CITY'			=> $value_city,
			'VALUE_COUNTRY'			=> $value_country,
			'VALUE_PHONE'			=> $value_phone,
			'VALUE_BIRTHDAY'		=> $value_birthday,
			'VALUE_STATUS'			=> $value_status,
			'JAVASCRIPTCODE'		=> $this->JSdetailuser(),
			'TXT_EDIT'		=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'		=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'	=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'	=> $_ARRAYLANG['TXT_EXPORT']
			));
	}
	
	function updateuser(){
		global $objDatabase, $_ARRAYLANG;
		
		if($_POST["update"]=="exe"){
			if($_POST["status"]!="1"){
				$status = 0;
			}else{
				$status = 1;
			}
			$objResult 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_user
													SET email='".$_POST["email"]."', 
													lastname='".$_POST["lastname"]."',
													firstname='".$_POST["firstname"]."',
													street='".$_POST["street"]."',
													zip='".$_POST["zip"]."',
													city='".$_POST["city"]."',
													country='".$_POST["country"]."',
													phone='".$_POST["phone"]."',
													birthday='".$_POST["birthday"]."',
													status='".$_POST["status"]."'
													WHERE id=".$_POST["id"]."");
		}
		$this->edituser();
		$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];
	}
	
	function adduser(){
		global $objDatabase, $_ARRAYLANG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION'];
		$this->_objTpl->loadTemplateFile('newsletter_user_add.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_ADD']);
		
		// User Insert
		// -----------------
		
		// ->>noch check ob email schon vorhanden einbauen
		$queryCHECK 		= "SELECT id FROM ".DBPREFIX."module_newsletter_user where email='".$_POST["email"]."'";
		$objResultCHECK 	= $objDatabase->Execute($queryCHECK);
		$count 		= $objResultCHECK->RecordCount();
		if ($count > 0) {
			$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_ALREADY_EXISTS'];
		}else{
			if($_POST["insert"]=="exe"){
				$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_user 
													(code, email, lastname, firstname, street, zip, city, country, phone, birthday, status, emaildate)
													VALUES ('".$this->EmailCode()."', '".$_POST["email"]."', '".$_POST["lastname"]."', '".$_POST["firstname"]."', '".$_POST["street"]."', '".$_POST["zip"]."', '".$_POST["city"]."', '".$_POST["country"]."', '".$_POST["phone"]."', '".$_POST["birthday"]."', 1, '".$this->DateForDB()."')");
				$queryPS = "select LAST_INSERT_ID() as lastid from ".DBPREFIX."module_newsletter_user";
				$objResultPS = $objDatabase->Execute($queryPS);

				if ($objResultPS !== false) {
					$CreatedID = $objResultPS->fields['lastid'];
				}
				
				$queryIC 		= "SELECT id FROM ".DBPREFIX."module_newsletter_category";
				$objResultIC 	= $objDatabase->Execute($queryIC);
				if ($objResultIC !== false) {
					while (!$objResultIC->EOF) {
						$objResultICR 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
										(user, category)
										VALUES (".$CreatedID.", ".$objResultIC->fields['id'].")");
						$objResultIC->MoveNext();
					}
				}
				$this->_statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
			}
		}
		
		$this->_objTpl->setVariable(array(
			'TXT_EMAIL_ADDRESS'		=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_LASTNAME'			=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_FIRSTNAME'			=> $_ARRAYLANG['TXT_FIRSTNAME'],
			'TXT_STREET'			=> $_ARRAYLANG['TXT_STREET'],
			'TXT_ZIP'				=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_CITY'				=> $_ARRAYLANG['TXT_CITY'],
			'TXT_COUNTRY'			=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_PHONE'				=> $_ARRAYLANG['TXT_PHONE'],
			'TXT_BIRTHDAY'			=> $_ARRAYLANG['TXT_BIRTHDAY'],
			'TXT_SAVE'				=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_MAILERROR'			=> $_ARRAYLANG['TXT_MAILERROR'],
			'JAVASCRIPTCODE'		=> $this->JSadduser(),
			'TXT_EDIT'		=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'		=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'	=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'	=> $_ARRAYLANG['TXT_EXPORT']
			));
	}
	
	function edituser(){
		global $objDatabase, $_ARRAYLANG, $_CONFIG;
		
		$this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_USER_ADMINISTRATION'];
		$this->_objTpl->loadTemplateFile('newsletter_user_edit.html');
		$this->_objTpl->setVariable('TXT_TITLE', $_ARRAYLANG['TXT_SEARCH']);

		
		if($_GET["addmailcode"]=="exe"){
			
			$query		= "SELECT id, code FROM ".DBPREFIX."module_newsletter_user where code=''";
			$objResult 	= $objDatabase->Execute($query);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$objResultUPDATE 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_user
														SET code='".$this->EmailCode()."'
														WHERE id=".$objResult->fields['id']."");
					$objResult->MoveNext();
				}
			}	
				
		}
		
		if($_GET["delete"]=="exe"){
			if($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_user WHERE id=".$_GET["id"]) !== false){
				$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat WHERE user=".$_GET["id"]);
				$this->_statusMessage=$_ARRAYLANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];	
			}else{
				$this->_statusMessage=$_ARRAYLANG['TXT_DATA_RECORD_DELETE_ERROR'];
			}
		}
		
		$queryCHECK 		= "SELECT id, code FROM ".DBPREFIX."module_newsletter_user where code=''";
		$objResultCHECK 	= $objDatabase->Execute($queryCHECK);
		$count 		= $objResultCHECK->RecordCount();
		if ($count > 0) {
			$email_code_check = '<div style="color: red;">'.$_ARRAYLANG['TXT_EMAIL_WITHOUT_CODE_MESSAGE'].'!<br/><a href="index.php?cmd=newsletter&act=edituser&addmailcode=exe">'.$_ARRAYLANG['TXT_ADD_EMAIL_CODE_LINK'].' ></a></div/><br/>';
			$this->_objTpl->setVariable('EMAIL_CODE_CHECK', $email_code_check);
		}
		
		$this->_objTpl->setVariable(array(
			'TXT_SEARCH'			=> $_ARRAYLANG['TXT_SEARCH'],
			'TXT_EMAIL_ADDRESS'		=> $_ARRAYLANG['TXT_EMAIL_ADDRESS'],
			'TXT_LASTNAME'			=> $_ARRAYLANG['TXT_LASTNAME'],
			'TXT_FIRSTNAME'			=> $_ARRAYLANG['TXT_FIRSTNAME'],
			'TXT_STREET'			=> $_ARRAYLANG['TXT_STREET'],
			'TXT_ZIP'				=> $_ARRAYLANG['TXT_ZIP'],
			'TXT_CITY'				=> $_ARRAYLANG['TXT_CITY'],
			'TXT_COUNTRY'			=> $_ARRAYLANG['TXT_COUNTRY'],
			'TXT_PHONE'				=> $_ARRAYLANG['TXT_PHONE'],
			'TXT_BIRTHDAY'			=> $_ARRAYLANG['TXT_BIRTHDAY'],
			'TXT_USER_DATA'			=> $_ARRAYLANG['TXT_USER_DATA'],
			'TXT_NEWSLETTER_CATEGORYS'	=> $_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'],
			'TXT_STATUS'			=> $_ARRAYLANG['TXT_STATUS'],
			'SELECTLIST_FIELDS'		=> $this->SelectListFields(),
			'SELECTLIST_CATEGORY'	=> $this->SelectListCategory(),
			'SELECTLIST_STATUS'		=> $this->SelectListStatus(),
			'JAVASCRIPTCODE'		=> $this->JSedituser(),
			'TXT_EDIT'				=> $_ARRAYLANG['TXT_EDIT'],
			'TXT_ADD'				=> $_ARRAYLANG['TXT_ADD'],
			'TXT_IMPORT'			=> $_ARRAYLANG['TXT_IMPORT'],
			'TXT_EXPORT'			=> $_ARRAYLANG['TXT_EXPORT'],
			));
		
			
		$where_statement = '';
		if($_POST["keyword"]!=""){
			if($_POST["SearchFields"]!=""){
				$where_statement .= ' and '.$_POST["SearchFields"].' LIKE "%'.$_POST["keyword"].'%" ';
			}else{
				$where_statement .= ' and (email LIKE "%'.$_POST["keyword"].'%" or lastname LIKE "%'.$_POST["keyword"].'%" or firstname LIKE "%'.$_POST["keyword"].'%" or street LIKE "%'.$_POST["keyword"].'%" or zip LIKE "%'.$_POST["keyword"].'%" or city LIKE "%'.$_POST["keyword"].'%" or country LIKE "%'.$_POST["keyword"].'%" or phone LIKE "%'.$_POST["keyword"].'%" or birthday LIKE "%'.$_POST["keyword"].'%")';
			}
		}
		// kategoriesuche noch einbauen
		if($_POST["SearchCategory"]!=""){
			$where_statement .= ' ';
		}
		
		if($_POST["SearchStatus"]!=""){
			$where_statement .= ' and status='.$_POST["SearchStatus"].' ';
		}
		
		$pos = intval($_GET['pos']);
		
			
		$query 		= "SELECT id, email, lastname, firstname, street, zip, city, country, status FROM ".DBPREFIX."module_newsletter_user where 1=1 ".$where_statement." order by email";
		$objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
		
		$query_2 		= "SELECT id, email, lastname, firstname, street, zip, city, country, status FROM ".DBPREFIX."module_newsletter_user where 1=1 ".$where_statement." order by email";
		$objResult_2 	= $objDatabase->Execute($query_2);
		$count 		= $objResult_2->RecordCount();
		if($count<1){
			$NoUser = $_ARRAYLANG['TXT_NO_USER'];
		}else{
			$NoUser = "";
		}
		$this->_objTpl->setCurrentBlock('newsletter_user');
		$RowClass = "row1";
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				
				if($objResult->fields['status']==1){
					$StatusImg = '<img src="/admin/images/icons/led_green.gif" width="13" height="13" border="0" alt="'.$_ARRAYLANG['TXT_ACTIVE'].'">';
				}else{
					$StatusImg = '<img src="/admin/images/icons/led_red.gif" width="13" height="13" border="0" alt="'.$_ARRAYLANG['TXT_OPEN_ISSUE'].'">';
				} 
				 
				$this->_objTpl->setVariable(array(
	                'USER_ID'			=>$objResult->fields['id'],
	                'USER_EMAIL'		=>$objResult->fields['email'],
	                'USER_LASTNAME'		=>$objResult->fields['lastname'],
	                'USER_FIRSTNAME'	=>$objResult->fields['firstname'],
	                'USER_STREET'		=>$objResult->fields['street'],
	                'USER_ZIP'			=>$objResult->fields['zip'],
	                'USER_CITY'			=>$objResult->fields['city'],
	                'USER_COUNTRY'		=>$objResult->fields['country'],
	                'STATUS_IMG'		=>$StatusImg,
	                'ROW_CLASS'			=>$RowClass
				));
				if($RowClass=="row1"){
					$RowClass = "row2";
				}else{
					$RowClass = "row1";
				}
				
				$this->_objTpl->parse("newsletter_user");
				$objResult->MoveNext();
			}
		}
		$paging = getPaging($count, $pos, "&amp;cmd=newsletter&act=edituser", "", true);
		$this->_objTpl->setVariable("USER_PAGING", $paging);
		
		$this->_objTpl->setVariable('TXT_EDIT', $_ARRAYLANG['TXT_EDIT']);
	}
	
	function SelectListStatus(){
		global $objDatabase, $_ARRAYLANG;
		$ReturnVar = '
			<select name="SearchStatus">
				<option value="">-- '.$_ARRAYLANG['TXT_STATUS'].' --</option>
				<option value="0">'.$_ARRAYLANG['TXT_OPEN_ISSUE'].'</option>
				<option value="1">'.$_ARRAYLANG['TXT_ACTIVE'].'</option>
			</select>		
		';
		return $ReturnVar;
	}
	
	function SelectListCategory(){
		global $objDatabase, $_ARRAYLANG;
		$ReturnVar = '<select name="SearchCategory">';
		$ReturnVar .= '<option value="">-- '.$_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'].' --</option>';
		$queryPS 		= "SELECT * FROM ".DBPREFIX."module_newsletter_category order by name";
		$objResultPS 	= $objDatabase->Execute($queryPS);
		if ($objResultPS !== false) {
			while (!$objResultPS->EOF) {
				$ReturnVar .= '<option value="'.$objResultPS->fields['id'].'" >'.$objResultPS->fields['name'].'</option>';
				$objResultPS->MoveNext();
			}
		}
		$ReturnVar .= '</select>';
		return $ReturnVar;
	}
	
	function SelectListFields(){
		global $objDatabase, $_ARRAYLANG;
		$ReturnVar = '
			<select name="SearchFields">
				<option value="">-- '.$_ARRAYLANG['TXT_SEARCH_ON'].' --</option>
				<option value="email">'.$_ARRAYLANG['TXT_EMAIL_ADDRESS'].'</option>
				<option value="lastname">'.$_ARRAYLANG['TXT_LASTNAME'].'</option>
				<option value="firstname">'.$_ARRAYLANG['TXT_FIRSTNAME'].'</option>
				<option value="street">'.$_ARRAYLANG['TXT_STREET'].'</option>
				<option value="zip">'.$_ARRAYLANG['TXT_ZIP'].'</option>
				<option value="city">'.$_ARRAYLANG['TXT_CITY'].'</option>
				<option value="country">'.$_ARRAYLANG['TXT_COUNTRY'].'</option>
				<option value="phone">'.$_ARRAYLANG['TXT_PHONE'].'</option>
				<option value="birthday">'.$_ARRAYLANG['TXT_BIRTHDAY'].'</option>
			</select>		
		';
		return $ReturnVar;
	}
	
	function JSadduser(){
		global $objDatabase, $_ARRAYLANG;
		Return '
			<script language="javascript">
				function SubmitAddForm(){
					if(CheckMail(document.adduser.email.value)==true){
						document.adduser.submit();
					}else{
						alert("'.$_ARRAYLANG['TXT_MAILERROR'].'");
						document.adduser.email.focus();
					}
				}
				function CheckMail(s){
	 				var a = false;
	 				var res = false;
 					if(typeof(RegExp) == "function"){
  						var b = new RegExp("abc");
  						if(b.test("abc") == true){a = true;}
  					}
					if(a == true){
	  					reg = new RegExp("^([a-zA-Z0-9\\-\\.\\_]+)"+
						                   "(\\@)([a-zA-Z0-9\\-\\.]+)"+
						                   "(\\.)([a-zA-Z]{2,4})$");
						res = (reg.test(s));
					}else{
						res = (s.search("@") >= 1 &&
						s.lastIndexOf(".") > s.search("@") &&
						s.lastIndexOf(".") >= s.length-5)
					}
					return(res);
				}
			</script>
		';
	}
	
	function JSdetailuser(){
		global $_ARRAYLANG;
		Return '
			<script language="javascript">
				function SubmitUpdateForm(){
					if(CheckMail(document.updateuser.email.value)==true){
						document.updateuser.submit();
					}else{
						alert("'.$_ARRAYLANG['TXT_MAILERROR'].'");
						document.updateuser.email.focus();
					}
				}
				function CheckMail(s){
	 				var a = false;
	 				var res = false;
 					if(typeof(RegExp) == "function"){
  						var b = new RegExp("abc");
  						if(b.test("abc") == true){a = true;}
  					}
					if(a == true){
	  					reg = new RegExp("^([a-zA-Z0-9\\-\\.\\_]+)"+
						                   "(\\@)([a-zA-Z0-9\\-\\.]+)"+
						                   "(\\.)([a-zA-Z]{2,4})$");
						res = (reg.test(s));
					}else{
						res = (s.search("@") >= 1 &&
						s.lastIndexOf(".") > s.search("@") &&
						s.lastIndexOf(".") >= s.length-5)
					}
					return(res);
				}
			</script>
		';
	}
	
	function JSimportuser(){
		global $_ARRAYLANG;
		Return '
			<script language="javascript">
				function SubmitImportForm(){
					if(document.importuser.GSdatei.value!="" || document.importuser.Emails.value!=""){
						document.importuser.submit();
					}
				}
			</script>
		';
	}
	
	function JSedituser(){
		global $_ARRAYLANG;
		Return '
			<script language="javascript">
				function DeleteUser(UserID){
					if(confirm("'.$_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'].'")){
						document.location.href = "index.php?cmd=newsletter&act=edituser&delete=exe&id="+UserID;
					}
				}
			</script>
		';
	}

	function DateForDB(){
		return date("Y")."-".date("m")."-".date("d");
	}

	function EmailCode(){
		$ReturnVar = '';
		$pool = "qwertzupasdfghkyxcvbnm";
		$pool .= "23456789";
		$pool .= "WERTZUPLKJHGFDSAYXCVBNM";
		srand ((double)microtime()*1000000);
		for($index = 0; $index < 10; $index++){
			$ReturnVar .= substr($pool,(rand()%(strlen ($pool))), 1);
		}
		return $ReturnVar;
	}
	
	function GetTemplateSelect(){
		global $objDatabase;
		$ReturnVar = '<select name="template" style="width: 220px;">';
		$queryPS 		= "SELECT * FROM ".DBPREFIX."module_newsletter_template order by name";
		$objResultPS 	= $objDatabase->Execute($queryPS);
		if ($objResultPS !== false) {
			while (!$objResultPS->EOF) {
				$ReturnVar .= '<option value="'.$objResultPS->fields['id'].'" >'.$objResultPS->fields['name'].'</option>';
				$objResultPS->MoveNext();
			}
		}
		$ReturnVar .= '</select>';
		return $ReturnVar;
	}
	
	function GetAttachmentValues(){
		global $objDatabase, $_ARRAYLANG;
		$ReturnVar ='';
		$NewsletterID = $_POST["NewsletterID"];
		if($NewsletterID==""){
			$NewsletterID = $_GET["id"];
		}
		$queryPS 		= "SELECT * FROM ".DBPREFIX."module_newsletter_attachment where newsletter=".$NewsletterID."";
		$objResultPS = $objDatabase->Execute($queryPS);
		$FileCounter = 0;
		if ($objResultPS !== false) {
			while (!$objResultPS->EOF) {
				$ReturnVar .= '<table cellspacing="0" cellapdding="2" border="0"><tr><td><a href="javascript:DeleteFile('.$objResultPS->fields['id'].');"><img src="/admin/images/icons/delete.gif" border="0" alt="'.$_ARRAYLANG['TXT_DELETE'].'" title="'.$_ARRAYLANG['TXT_DELETE'].'"></a></td><td>'.$objResultPS->fields['file_name'].'</td></tr></table>';
				$FileCounter ++;
				$objResultPS->MoveNext();
			}
		}
		for($x=($FileCounter+1); $x<=3; $x++){
			$ReturnVar .= '<input type="file" name="datei'.$x.'" size="40" /><br/>';
		}
		return $ReturnVar;
	}
	
	function CategorySelect($NewsletterID){
		global $objDatabase, $_ARRAYLANG;
		$ReturnVar = '<tr class="row1"><td valign="top">'.$_ARRAYLANG['TXT_NEWSLETTER_CATEGORYS'].'</td><td valign="top">';
		$queryCS 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
		$objResultCS 	= $objDatabase->Execute($queryCS);
		if ($objResultCS !== false) {
			$CategorysFounded = 1;
			while (!$objResultCS->EOF) {
				
				// Check
				// ---------------------------------
				$queryCheck = "SELECT id, newsletter, category FROM ".DBPREFIX."module_newsletter_rel_cat_news where newsletter='".$NewsletterID."' and category='".$objResultCS->fields['id']."'";
				$objResultCheck 	= $objDatabase->Execute($queryCheck);
				if ($objResultCheck !== false) {
					if($objResultCheck->fields['id']!=""){
						$CheckedText = "checked";
					}else{
						$CheckedText = "";
					}
				}
				$ReturnVar .= '<input type="checkbox" name="cat_'.$objResultCS->fields['id'].'" '.$CheckedText.' value="yes"> '.$objResultCS->fields['name'].' ('.$this->GetUsersInCategory($objResultCS->fields['id']).')<br/>';
				$objResultCS->MoveNext();
			}
		}
		$ReturnVar .= '</td></tr>';
		if($CategorysFounded!=1){
			$ReturnVar = '';
		}
		return $ReturnVar;
	}
	
	function CategoryDropDown(){
		global $objDatabase, $_ARRAYLANG;
		$ReturnVar = '<select name="category"><option value="">'.$_ARRAYLANG['TXT_NEWSLETTER_ALL'].'</option>';
		$queryCS 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
		$objResultCS 	= $objDatabase->Execute($queryCS);
		if ($objResultCS !== false) {
			$CategorysFounded = 1;
			while (!$objResultCS->EOF) {
				$ReturnVar .= '<option value="'.$objResultCS->fields['id'].'">'.$objResultCS->fields['name'].'</option>';
				$objResultCS->MoveNext();
			}
		}
		$ReturnVar .= '</select>';
		if($CategorysFounded!=1){
			$ReturnVar = '';
		}
		return $ReturnVar;
	}
	
	function GetUsersInCategory($CatID){
		global $objDatabase;
		$queryGUC 		= "SELECT id, user, category FROM ".DBPREFIX."module_newsletter_rel_user_cat where category='".$CatID."'";
		$objResultGUC 	= $objDatabase->Execute($queryGUC);
		$countGUC 		= $objResultGUC->RecordCount();
		return $countGUC;
	}
	
	function GetTemplateName($TemplateID){
		global $objDatabase;
		$objResult_value = $objDatabase->SelectLimit("SELECT 
													id, name 
													FROM ".DBPREFIX."module_newsletter_template 
													WHERE id=".$TemplateID."", 1);
		if ($objResult_value !== false && !$objResult_value->EOF) {
			return $objResult_value->fields["name"];
		}else{
			return "-";
		}
	}
	
	function SendNewsletter($UserID){
		global $objDatabase, $_ARRAYLANG;
		
	}
	
	function CategoryStat(){
		global $objDatabase, $_ARRAYLANG;
		$queryCS 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
		$objResultCS 	= $objDatabase->Execute($queryCS);
		if ($objResultCS !== false) {
			$CategorysFounded = 1;
			while (!$objResultCS->EOF) {
				
				$ReturnVar .= '- '.$objResultCS->fields['name'].' ('.$this->GetUsersInCategory($objResultCS->fields['id']).')<br/>';
				$objResultCS->MoveNext();
			}
		}
		$ReturnVar .= '';
		if($CategorysFounded!=1){
			$ReturnVar = '';
		}
		return $ReturnVar;
	}
	
}
?>
