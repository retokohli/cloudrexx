<?PHP
/**
* Newsletter Modul
*
* frontend newsletter class
*
* @copyright CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author Comvation Development Team <info@comvation.com>  
* @module guestbook
* @modulegroup modules
* @access public
* @version 1.0.0
*/


class newsletter
{	
    var $langId;
    var $_objTpl;
    var $statusMessage;
    var $arrSettings = array();
    
    		
	/**
    * Constructor
    *
    * @param  string  
    * @access public
    */   
    function newsletter($pageContent)
    { 
    	$this->__construct($pageContent);    
    }
	

    
    /**
     * PHP5 constructor
     * @param  string  $pageContent
     * @global string  $_LANGID      
     * @access public     
     */
    function __construct($pageContent)
    {  
	    $this->pageContent = $pageContent;
	    $this->langId = $_LANGID;
	    
	    $this->_objTpl = &new HTML_Template_Sigma('.');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE); 

	}

	
	
	
	function getPage()
	{
		global $_LANGID, $_ARRAYLANG;
    	if(!isset($_REQUEST['cmd'])){
    		$_REQUEST['cmd'] = '';
    	}
    	
    	switch($_REQUEST['cmd']){
    		case 'profile':
		    	$this->_profile();
		    	break;
		    case 'unsubscribe':
		    	$this->_unsubscribe();
		    	break;
		     case 'subscribe':
		    	$this->_subscribe();
		    	break;
		    case 'confirm':
		    	$this->_confirm();
		    	break;
		    default:
		        $this->_subscribe();
		        break;
    	}
    	return $this->_objTpl->get(); 
    }   
    
    function _confirm(){
    	global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent, true, true);
		$objResult 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_user SET status=1 where email='".$_REQUEST['email']."'");
    	$this->_objTpl->setVariable("NEWSLETTER", $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION_SUCCESSFUL']);					
		
    } 
	
	function _profile(){
		global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent, true, true);
		
		$PageSource = '';
		$UserCode = $_REQUEST['code'];
		
		// Profile Update
		// --------------------------------------
		if($_REQUEST['profileupdate']=="exe"){
			
			$cat_selected = 0;
			$query 		= "SELECT id, status, name FROM ".DBPREFIX."module_newsletter_category where status=1 order by name";
			$objResult 	= $objDatabase->Execute($query);
			$cat_code = '';
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					if($_REQUEST['category_'.$objResult->fields['id']]=="1"){
						$cat_selected = 1;
					}
					$objResult->MoveNext();
				}
			}
			if($cat_selected==0){
				$Message = '<font color="red">'.$_ARRAYLANG['TXT_CATEGORY_ERROR'].'</font><br/><br/>';
			}elseif (!$this->CheckEmail($_REQUEST['email'])){
				$Message = '<font color="red">'.$_ARRAYLANG['TXT_NOT_VALID_EMAIL'].'</font><br/><br/>';
			}else{
				$objResult 	= $objDatabase->Execute("UPDATE ".DBPREFIX."module_newsletter_user
												SET email='".$_REQUEST["email"]."', lastname='".$_REQUEST["lastname"]."', firstname='".$_REQUEST["firstname"]."', street='".$_REQUEST["street"]."', zip='".$_REQUEST["zip"]."', city='".$_REQUEST["city"]."', country='".$_REQUEST["country"]."', phone='".$_REQUEST["phone"]."', birthday='".$_REQUEST["birthday"]."' where code='".$_REQUEST['code']."'");
				
				$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat where user=".$this->GetUserID($UserCode)."");
				
				$query 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category order by name";
				$objResult 	= $objDatabase->Execute($query);
				if ($objResult !== false) {
					while (!$objResult->EOF) {
						if($_REQUEST['category_'.$objResult->fields['id']]=="1"){
							$objResult_in 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
												(user, category)
												VALUES (".$this->GetUserID($UserCode).", ".$objResult->fields['id'].")");
						}
						$objResult->MoveNext();
					}
				}
				
			}
		}
		$query = "SELECT * FROM ".DBPREFIX."module_newsletter_user
				     WHERE code='".$UserCode."'";
		$objResult = $objDatabase->Execute($query);
		$count_users 		= $objResult->RecordCount();

		if ($count_users > 0 && $UserCode!='') {
			
			$query_c 		= "SELECT id, status, name FROM ".DBPREFIX."module_newsletter_category where status=1 order by name";
			$objResult_c 	= $objDatabase->Execute($query_c);
			$count 		= $objResult_c->RecordCount();
			
			$cat_code = '';
			if ($objResult_c !== false) {
				while (!$objResult_c->EOF) {
					if($count<2){
						$cat_code .= '<input type="hidden" name="category_'.$objResult_c->fields['id'].'" value="1" />';
					}else{
						$cat_code .= '<input type="checkbox" name="category_'.$objResult_c->fields['id'].'" value="1" '.$this->CatChecked($objResult_c->fields['id'], $objResult->fields['id']).' /> '.$objResult_c->fields['name'].'<br/>';
					}
					$objResult_c->MoveNext();
				} 
			}
	
			
			$PageSource = $Message.'
				<form name="profile" action="?section=newsletter&cmd=profile&code='.$UserCode.'" method="post">
				<input type="hidden" name="profileupdate" value="exe" />
				<input type="hidden" name="code" value="'.$UserCode.'" />
				<table width="100%" border="0" cellpadding="3" cellspacing="0" class="adminlist">
					<tr class="row1">
						<td width="12%"><b>'.$_ARRAYLANG['TXT_EMAIL_ADDRESS'].'</b></td>
						<td width="88%"><input type="text" name="email" size="40" maxlength="200" value="'.$objResult->fields["email"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_LASTNAME'].'</td>
						<td width="88%"><input type="text" name="lastname" size="40" maxlength="200" value="'.$objResult->fields["lastname"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_FIRSTNAME'].'</td>
						<td width="88%"><input type="text" name="firstname" size="40" maxlength="200" value="'.$objResult->fields["firstname"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_STREET'].'</td>
						<td width="88%"><input type="text" name="street" size="40" maxlength="200" value="'.$objResult->fields["street"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_ZIP'].'</td>
						<td width="88%"><input type="text" name="zip" size="40" maxlength="200" value="'.$objResult->fields["zip"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_CITY'].'</td>
						<td width="88%"><input type="text" name="city" size="40" maxlength="200" value="'.$objResult->fields["city"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_COUNTRY'].'</td>
						<td width="88%"><input type="text" name="country" size="40" maxlength="200" value="'.$objResult->fields["country"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_PHONE'].'</td>
						<td width="88%"><input type="text" name="phone" size="40" maxlength="200" value="'.$objResult->fields["phone"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_BIRTHDAY'].'</td>
						<td width="88%"><input type="text" name="birthday" size="40" maxlength="200" value="'.$objResult->fields["birthday"].'" /></td>
					</tr>
					<tr>
						<td width="12%" valign="top"></td>
						<td width="88%">
							'.$cat_code.'
						</td>
					</tr>
					<tr>
						<td width="12%"></td>
						<td width="88%"><input type="submit" value="'.$_ARRAYLANG['TXT_SAVE'].'" /></td>
					</tr>
				</table>
				</form>
				';
		}else{
			$PageSource = $_ARRAYLANG['TXT_AUTHENTICATION_FAILED'];
		}
		
		$this->_objTpl->setVariable("NEWSLETTER", $PageSource);
	}
	
	function _unsubscribe(){
		global $objDatabase, $_CONFIG, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent, true, true);
		$PageSource = '';
		
		
			$query_g = "SELECT code FROM ".DBPREFIX."module_newsletter_user
				     WHERE code='".$_REQUEST['code']."'";
			$objResult_g = $objDatabase->Execute($query_g);
			$count_cc 		= $objResult_g->RecordCount();
			if ($count_cc > 0) {
				$UserID = $this->GetUserID($_REQUEST['code']);
				$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_user where code=".$_REQUEST['code']."");
				$objResult 	= $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_newsletter_rel_user_cat where user=".$UserID."");
				$PageSource = $_ARRAYLANG['TXT_EMAIL_SUCCESSFULLY_DELETED'];
			}else{
				$PageSource = $_ARRAYLANG['TXT_AUTHENTICATION_FAILED'];
			}	
		
		
		$this->_objTpl->setVariable("NEWSLETTER", $PageSource);
	}
	
	function _subscribe(){
		global $objDatabase, $_CONFIG, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent, true, true);
		$Message = '';
		$PageSource = '';
		
		// Insert
		// -----------------------
		if($_REQUEST['subscribe']=="exe"){
			
			$cat_selected = 0;
			$query 		= "SELECT id, name FROM ".DBPREFIX."module_newsletter_category order by name";
			$objResult 	= $objDatabase->Execute($query);
			$cat_code = '';
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					if($_REQUEST['category_'.$objResult->fields['id']]=="1"){
						$cat_selected = 1;
					}
					$objResult->MoveNext();
				}
			}
			if($cat_selected==0){
				$Message = '<font color="red">'.$_ARRAYLANG['TXT_CATEGORY_ERROR'].'</font><br/><br/>';
			}elseif (!$this->CheckEmail($_REQUEST['email'])){
				$Message = '<font color="red">'.$_ARRAYLANG['TXT_NOT_VALID_EMAIL'].'</font><br/><br/>';
			}elseif($this->ExistEmail($_REQUEST['email'])){
				$Message = '<font color="red">'.$_ARRAYLANG['TXT_NEWSLETTER_SUBSCRIBER_ALREADY_INSERTED'].'</font><br/><br/>';
			}else{
				
				$objResult 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_user 
													(code, email, lastname, firstname, street, zip, city, country, phone, birthday, status, emaildate)
													VALUES ('".$this->EmailCode()."', '".$_REQUEST["email"]."', '".$_REQUEST["lastname"]."', '".$_REQUEST["firstname"]."', '".$_REQUEST["street"]."', '".$_REQUEST["zip"]."', '".$_REQUEST["city"]."', '".$_REQUEST["country"]."', '".$_REQUEST["phone"]."', '".$_REQUEST["birthday"]."', 0, '".$this->DateForDB()."')");
				
				$queryPS = "select LAST_INSERT_ID() as lastid from ".DBPREFIX."module_newsletter_user";
				$objResultPS = $objDatabase->Execute($queryPS);
				if ($objResultPS !== false) {
					$CreatedID = $objResultPS->fields['lastid'];
				}
				
				$query 		= "SELECT id, status, name FROM ".DBPREFIX."module_newsletter_category where status=1 order by name";
				$objResult 	= $objDatabase->Execute($query);
				if ($objResult !== false) {
					while (!$objResult->EOF) {
						if($_REQUEST['category_'.$objResult->fields['id']]=="1"){
							$objResult_in 	= $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_newsletter_rel_user_cat 
												(user, category)
												VALUES (".$CreatedID.", ".$objResult->fields['id'].")");
						}
						$objResult->MoveNext();
					}
				}
				
				// Authorize Email
				// ---------------
				$query = "SELECT id, email, code FROM ".DBPREFIX."module_newsletter_user
						     WHERE id=".$CreatedID."";
				$objResult = $objDatabase->Execute($query);
		
				if ($objResult !== false) {
					
					$query_conf 		= "SELECT * FROM ".DBPREFIX."module_newsletter_config WHERE 1";
					$objResult_conf 	= $objDatabase->Execute($query_conf);
					if ($objResult !== false) {
						$value_sender_emailDEF = $objResult_conf->fields['sender_email'];
						$value_sender_nameDEF = $objResult_conf->fields['sender_name'];
						$value_return_pathDEF = $objResult_conf->fields['return_path'];
					}
					
					require_once ASCMS_LIBRARY_PATH . '/phpmailer/class.phpmailer.php';
					$mail = new phpmailer();
					$mail->From 	= $value_sender_emailDEF;
					$mail->FromName = $value_sender_nameDEF;	
					$mail->Subject 	= $_ARRAYLANG['TXT_NEWSLETTER_CONFIRMATION'];
					$mail->Priority = 3;
					$mail->IsHTML(false); 
$mail->Body = '
'.$_ARRAYLANG['TXT_NEWSLETTER_MUST_CONFIRM'].'
'.ASCMS_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].'/index.php?section=newsletter&cmd=confirm&email='.$_REQUEST["email"].'
';
					$mail->AddAddress($_REQUEST["email"]);
					if($mail->Send()){
						$Message = $_ARRAYLANG['TXT_NEWSLETTER_SUBSCRIBE_OK']."<br/><br/>";
					}
					 
				}
			}
			
		}
		
		$query_c 		= "SELECT id, status, name FROM ".DBPREFIX."module_newsletter_category where status=1 order by name";
			$objResult_c 	= $objDatabase->Execute($query_c);
			$count 		= $objResult_c->RecordCount();
			$cat_code = '';
			if ($objResult_c !== false) {
				while (!$objResult_c->EOF) {
					if($count<2){
						$cat_code .= '<input type="hidden" name="category_'.$objResult_c->fields['id'].'" value="1" />';
					}else{
						$cat_code .= '<input type="checkbox" name="category_'.$objResult_c->fields['id'].'" value="1" /> '.$objResult_c->fields['name'].'<br/>';
					}
					$objResult_c->MoveNext();
				} 
			}
		
		$PageSource = $Message.'
				<form name="newsletter" action="?section=newsletter&cmd=subscribe" method="post">
				<input type="hidden" name="subscribe" value="exe" />
				<table width="100%" border="0" cellpadding="3" cellspacing="0" class="adminlist">
					<tr class="row1">
						<td width="12%"><b>'.$_ARRAYLANG['TXT_EMAIL_ADDRESS'].'</b></td>
						<td width="88%"><input type="text" name="email" size="40" maxlength="200" value="'.$objResult->fields["email"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_LASTNAME'].'</td>
						<td width="88%"><input type="text" name="lastname" size="40" maxlength="200" value="'.$objResult->fields["lastname"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_FIRSTNAME'].'</td>
						<td width="88%"><input type="text" name="firstname" size="40" maxlength="200" value="'.$objResult->fields["firstname"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_STREET'].'</td>
						<td width="88%"><input type="text" name="street" size="40" maxlength="200" value="'.$objResult->fields["street"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_ZIP'].'</td>
						<td width="88%"><input type="text" name="zip" size="40" maxlength="200" value="'.$objResult->fields["zip"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_CITY'].'</td>
						<td width="88%"><input type="text" name="city" size="40" maxlength="200" value="'.$objResult->fields["city"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_COUNTRY'].'</td>
						<td width="88%"><input type="text" name="country" size="40" maxlength="200" value="'.$objResult->fields["country"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_PHONE'].'</td>
						<td width="88%"><input type="text" name="phone" size="40" maxlength="200" value="'.$objResult->fields["phone"].'" /></td>
					</tr>
					<tr class="row1">
						<td width="12%">'.$_ARRAYLANG['TXT_BIRTHDAY'].'</td>
						<td width="88%"><input type="text" name="birthday" size="40" maxlength="200" value="'.$objResult->fields["birthday"].'" /></td>
					</tr>
					<tr>
						<td width="12%" valign="top"></td>
						<td width="88%">
							'.$cat_code.'
						</td>
					</tr>
					<tr>
						<td width="12%"></td>
						<td width="88%"><input type="submit" value="'.$_ARRAYLANG['TXT_SAVE'].'" /></td>
					</tr>
				</table>
				</form>
				';
		
		$this->_objTpl->setVariable("NEWSLETTER", $PageSource);
	}
    
    function CheckEmail($EmailAdress){
		if(preg_match('/^[a-zA-Z0-9.][a-zA-Z0-9-_\s]+@[a-zA-Z0-9-\s].+\.[a-zA-Z]{2,5}$/',$EmailAdress)){
			$ReturnVar = true;
		}else{
			$ReturnVar = false;
		}
		return $ReturnVar;
	}
	
	function CatChecked($CatID, $UserID){
		global $objDatabase;
		
		$query_cc = "SELECT user FROM ".DBPREFIX."module_newsletter_rel_user_cat
				     WHERE user='".$UserID."' and category='".$CatID."'";
		$objResult_cc = $objDatabase->Execute($query_cc);
		$count_cc 		= $objResult_cc->RecordCount();
		if ($count_cc > 0) {
			return 'checked';
		}else{
			return '2';
		}
	} 
	
	function GetUserID($UserCode){
		global $objDatabase;
		$query_g = "SELECT id FROM ".DBPREFIX."module_newsletter_user
				     WHERE code='".$UserCode."'";
		$objResult_g = $objDatabase->Execute($query_g);
		if ($objResult_g !== false) {
			return $objResult_g->fields["id"];
		}else{
			return '';
		}
	}
	
	function ExistEmail($email){
		global $objDatabase;
		$query_g = "SELECT email FROM ".DBPREFIX."module_newsletter_user
				     WHERE email='".$email."'";
		$objResult_g = $objDatabase->Execute($query_g);
		$count_cc 		= $objResult_g->RecordCount();
		if ($count_cc > 0) {
			return true;
		}else{
			return false;
		}
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
	
	function DateForDB(){
		return date("Y")."-".date("m")."-".date("d");
	}
}
?>