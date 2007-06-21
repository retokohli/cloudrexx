<?php
/**
 * User Management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_PATH.'/Tree.class.php';

/**
 * User Management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @version     $Id:  Exp $
 * @package     contrexx
 * @subpackage  core
 */
class userManagement
{
	var $pageTitle="";
	var $strErrMessage = '';
	var $strOkMessage = '';
 	var $todayEmailAlreadySent = 0;
 	var $months = array();

	/**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function userManagement() {
    	global  $objTemplate, $_CORELANG, $objDatabase;

    	$objTemplate->setVariable(array(
			'CONTENT_NAVIGATION'	=> "<a href='?cmd=user'>".$_CORELANG['TXT_USER_ADMINISTRATION']."</a>
                            <a href='?cmd=user&amp;act=newuser'>".$_CORELANG['TXT_ADD_USER']."</a>
                            <a href='?cmd=user&amp;act=groups'>".$_CORELANG['TXT_GROUPS']."</a>
                            <a href='?cmd=user&amp;act=modgroup'>".$_CORELANG['TXT_CREATE_BACKEND_GROUP']."</a>
                            <a href='?cmd=user&amp;act=modpubgroup'>".$_CORELANG['TXT_CREATE_FRONTEND_GROUP']."</a>"
                            //<a href='?cmd=user&amp;act=settings'>".$_CORELANG['TXT_SETTINGS']."</a>"
		));

	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."access_users");
	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."access_user_groups");
	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."access_group_dynamic_ids");
	    $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."access_group_static_ids");

	    $months = explode(',', $_CORELANG['TXT_MONTH_ARRAY']);
		$i=0;
		foreach ($months as $month) {
			$this->months[++$i] = $month;
		}

    }



    /**
    * Gets the requested methods
    *
    * @global	 array     $_CORELANG
    * @return    string    parsed content
    */
    function getPage()
    {
    	global $_CORELANG, $objTemplate, $objPerm;

    	if(!isset($_GET['act'])) {
    	    $_GET['act']="";
    	}

        switch($_GET['act'])
		{
			case "groups":
			    $this->showGroupsOverview();
			break;
			case "settings":
				$this->showSettings();
			break;
			case "updateuser":
			    $objPerm->checkAccess(31, 'static');
			    if ($_REQUEST['userId'] == $_SESSION['auth']['userid'] || $_SESSION['auth']['is_admin'] == 1) {
			    	$this->updateUser();
			    }
			    $this->userOverview();
			break;
			case "edituser":
			    $objPerm->checkAccess(31, 'static');
			    if ($_REQUEST['userId'] == $_SESSION['auth']['userid'] || $_SESSION['auth']['is_admin'] == 1) {
			    	$this->showEditUser();
			    }
			break;
			case "adduser":
			    $objPerm->checkAccess(29, 'static');
			    $this->addUser();
			    $this->showNewUser();
			break;
			case "newuser":
			    $objPerm->checkAccess(29, 'static');
			    $this->showNewUser();
			break;
			case "modgroup":
			    $this->showModGroup();
			break;
			case "addgroup":
			    $objPerm->checkAccess(33, 'static');
			    $this->addGroup();
			    $this->showGroupsOverview();
			break;
			case "modpubgroup":
			    $this->showModPubGroup();
			break;
			case "addpubgroup":
			    $objPerm->checkAccess(33, 'static');
			    $this->addPubGroup();
			    $this->showGroupsOverview();
			break;
			case "updatepubgroup":
			    $objPerm->checkAccess(33, 'static');
			    $this->updatePubGroup();
			    $this->showGroupsOverview();
			break;
			case "updategroup":
			    $objPerm->checkAccess(34, 'static');
			    $this->updateGroup();
			    $this->showGroupsOverview();
			break;
			case "deletegroup":
			    $objPerm->checkAccess(30, 'static');
			    $this->deleteGroup();
			    $this->showGroupsOverview();
			break;
			case "deactivate":
			    $objPerm->checkAccess(28, 'static');
			    $this->deactivateUser();
			    $this->userOverview();
			break;

			case "activate":
				$objPerm->checkAccess(28, 'static');
			    $this->activateUser();
			    $this->userOverview();
			break;
			default:
			    $objPerm->checkAccess(18, 'static');
			    $this->userOverview();
		}

		$objTemplate->setVariable(array(
			'CONTENT_TITLE'	=> $this->pageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
		));
    }


	function getCurrentBirthdays(){
    	global $objDatabase, $_CONFIG;
    	$birthdayMailAlreadySent = 0;
    	$today = date("d-m-");
		$objRS = $objDatabase->Execute("SELECT
										 email,
										 firstname,
										 lastname,
										 birthday
										FROM ".DBPREFIX."access_users
			    	   					WHERE 	LEFT(birthday, 6) = '".$today."'
			    	   					 AND 	show_birthday = 1
			    	   					 AND active = 1
			    	   					UNION
			    	   					SELECT
			    	   					 email,
			    	   					 firstname,
										 lastname,
										 birthday
			    	   					FROM ".DBPREFIX."module_newsletter_user
			    	   					WHERE 	LEFT(birthday, 6) = '".$today."'
			    	   					 AND 	showbirthday = 1 ");
		if($objRS){
			if($objRS->RecordCount() > 0){
	    		while(!$objRS->EOF){
	    			$users[] = $objRS->fields;
	    			$objRS->MoveNext();
	    		}
				$objTpl = &new HTML_Template_Sigma();
				$objTpl->setErrorHandling(PEAR_ERROR_DIE);
				$template = ereg_replace('\[\[','{',$_CONFIG['birthdayMessage']);
				$template = ereg_replace('\]\]','}',$template);
				$objTpl->setTemplate($template);
		    	foreach ($users as $user) {
		    		if($this->todayEmailAlreadySent == 0){
		    			$this->sendBirthdayMail($user);
		    		}
		    		$bd = explode('-',$user['birthday']);
					$age = date("Y") - $bd[2];
		   			$objTpl->setVariable(array(
		   				'FIRSTNAME'	=> $user['firstname'],
		   				'LASTNAME'	=> $user['lastname'],
		   				'AGE'		=> $age
		   			));
		   			$objTpl->parse('birthdaypeople');
		    	}
	     	}else{
	     		//nobody has birthday
	    		return '';
	    	}
    	}else{
    		// no result object
    		return "Datenbankfehler.". __FILE__ . __LINE__;
    	}
    	return $objTpl->get();
    }

    function sendBirthdayMail($arrUser){
    	global $objDatabase, $_CONFIG;
    	$today = date("d-m-");
    	$objRS = $objDatabase->Execute("SELECT setvalue
    									FROM ".DBPREFIX."module_newsletter_settings
    									WHERE setname='today'
    									 AND setvalue='$today'");
    	if($objRS){
    		if($objRS->RecordCount() < 1){
    			$objRS = $objDatabase->Execute("SELECT
    											 email,
    											 firstname,
    											 lastname,
    											 birthday
    											FROM ".DBPREFIX."module_newsletter_user
    											WHERE 	LEFT(birthday, 6) = '".$today."'
					    	   					 AND 	showbirthday = 1
			   			 	   					GROUP BY email");
    			if($objRS){
    				while(!$objRS->EOF){
		    			$users[] = $objRS->fields;
		    			$objRS->MoveNext();
	    			}

					foreach ($users as $user) {
						$objTpl = &new HTML_Template_Sigma();
						$objTpl->setErrorHandling(PEAR_ERROR_DIE);
						$template = ereg_replace('\[\[','{',$_CONFIG['birthdayMessage_newsletter']);
						$template = ereg_replace('\]\]','}',$template);
						$objTpl->setTemplate($template);
			    		$bd = explode('-',$user['birthday']);
						$age = date("Y") - $bd[2];
			   			$objTpl->setVariable(array(
			   				'FIRSTNAME'	=> $user['firstname'],
			   				'LASTNAME'	=> $user['lastname'],
			   				'AGE'		=> $age
			   			));
			   			$targetEmail = $user['email'];
			   			require_once ASCMS_LIBRARY_PATH . '/phpmailer/class.phpmailer.php';

			   			$objRS = $objDatabase->Execute("SELECT setvalue from ".DBPREFIX."module_newsletter_settings WHERE setname LIKE 'birthday%' ORDER BY setid");
						if($objRS){
							$sender_email = trim(htmlentities($objRS->fields['setvalue']), ENT_QUOTES, CONTREXX_CHARSET);
							$sender_replyto = ($objRS->MoveNext()) ? trim(htmlentities($objRS->fields['setvalue'], ENT_QUOTES, CONTREXX_CHARSET)) : '';
							$sender_name = ($objRS->MoveNext()) ? trim(htmlentities($objRS->fields['setvalue'], ENT_QUOTES, CONTREXX_CHARSET)) : '';
							$sender_subject = ($objRS->MoveNext()) ? trim(htmlentities($objRS->fields['setvalue'], ENT_QUOTES, CONTREXX_CHARSET)) : '';
						}else{
							echo "DB error.";
						}

						$mail =& new PHPMailer();

						if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
							$objSmtpSettings = new SmtpSettings();
							if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
								$mail->IsSMTP();
								$mail->Host = $arrSmtp['hostname'];
								$mail->Port = $arrSmtp['port'];
								$mail->SMTPAuth = true;
								$mail->Username = $arrSmtp['username'];
								$mail->Password = $arrSmtp['password'];
							}
						}

						$mail->CharSet = CONTREXX_CHARSET;
			   			$mail->From 	= $sender_email;
						$mail->FromName = $sender_name;
						$mail->AddReplyTo($sender_replyto);
						$mail->Subject 	= $sender_subject;
						$mail->Priority = 3;
						$mail->Body = $objTpl->get();
						$mail->AddAddress($targetEmail);
						$mail->Send();
						$mail->ClearAddresses();
			    	}

    			}

    			if($objDatabase->Execute("	UPDATE ".DBPREFIX."module_newsletter_settings
    										SET setvalue='$today'
    										WHERE setname='today'") === false)
    			{
    				echo "DB error.";
    			}
    			$this->todayEmailAlreadySent = 1;
    		}else{
    			$this->todayEmailAlreadySent = 1;
    		}
    	}

    }


	function getGroupInfo($groupId) {
		global $objDatabase;

		$groupInfo=array();

		$objResult = $objDatabase->Execute("SELECT group_name,group_description,is_active,type
		               FROM ".DBPREFIX."access_user_groups WHERE group_id=".$groupId);
		if ($objResult !== false && !$objResult->EOF) {
            $groupInfo= array(
			'group_name'          => $objResult->fields['group_name'],
			'group_description'   => $objResult->fields['group_description'],
			'is_active'           => $objResult->fields['is_active'],
			'type'                => $objResult->fields['type']);
		}
		return $groupInfo;
	}





//	function getUserInfo($userId) {
//		$groupInfo=array();
//		$_db = &new astalavistaDB;
//		$_db->query("SELECT * FROM ".DBPREFIX."users WHERE id=".$userId);
//		while($_db->next_record()) {
//            $userInfo= array(
//			'is_admin'   => $_db->f('is_admin'),
//			'username'   => $_db->f('username'),
//			'password'   => $_db->f('password'),
//			'regdate'    => $_db->f('regdate'),
//			'email'      => $_db->f('email'),
//			'firstname'  => $_db->f('firstname'),
//			'lastname'   => $_db->f('lastname'),
//			'lang'       => $_db->f('langId'),
//			'active'     => $_db->f('active'),
//			'groups'     => $_db->f('groups'));
//		}
//		return $userInfo;
//	}




	function getPermissionInfo($groupId, $type) {
		global $objDatabase;

		$arrRightIds=array();

		switch ($type) {
			case 'static':
				$table = "access_group_static_ids";
				break;

			case 'dynamic':
				$table = "access_group_dynamic_ids";
				break;

			default:
				return $arrRightIds;
		}

		$objResult = $objDatabase->Execute("SELECT access_id
		               FROM ".DBPREFIX.$table."
		              WHERE group_id=".$groupId);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				array_push($arrRightIds, $objResult->fields['access_id']);
				$objResult->MoveNext();
			}
		}
		return $arrRightIds;
	}


	function showSettings(){
		global $objDatabase, $objTemplate, $_CORELANG;
		$this->pageTitle = $_CORELANG['TXT_SETTINGS'];
		if(!empty($_REQUEST['save'])){
			if($objDatabase->Execute("UPDATE " .DBPREFIX."settings set setvalue='".contrexx_addslashes($_REQUEST['bdmessage'])."' WHERE setname='birthdayMessage' ") !== false){
				$this->statusMessage = "Meldung erfolgreich aktualisiert.";
			}else{
				$this->statusMessage = "Es ist ein Datenbankfehler aufgetreten.";
			}
		}

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_settings', 'user_settings.html');

		$objRS = $objDatabase->Execute("SELECT setvalue from ".DBPREFIX."settings WHERE setname='birthdayMessage'");
		$objTemplate->setVariable(array(
			'TXT_BIRTDAY_MESSAGE' => htmlentities($objRS->fields['setvalue'], ENT_QUOTES, CONTREXX_CHARSET),
		));
	}


	/**
	* Add a new user into the db
	*
	* @global    array      $_CORELANG
	* @global    object     $objDatabase
	* @return    bollean    result
	*/
	function addUser()
	{
		global $objDatabase, $_CORELANG;

        if (is_array($_POST)) {
			foreach ($_POST as $key => $val) {
	            $_SESSION['users'][$key]=$val;
	        }
        }

		if (
		    (strlen($_POST['password'])>=6) AND
		    ($_POST['password']==$_POST['password2']) AND
			(!empty($_POST['username'])) AND
			(!empty($_POST['email']))
		   )
		{
		    $_POST['password'] = md5($_POST['password']);
		    $groups = !empty($_POST['dest']) ? implode(",",$_POST['dest']) : 2; // add standard public group
            $is_admin = (isset($_POST['is_admin']) AND $_POST['is_admin']==1) ? 1 : 0;

		    $query = "INSERT INTO ".DBPREFIX."access_users
				                    SET username='".addslashes(strip_tags($_POST['username']))."',
		                                is_admin='".$is_admin."',
				                        password='".$_POST['password']."',
				                        email='".addslashes(strip_tags($_POST['email']))."',
				                        firstname='".addslashes(strip_tags($_POST['firstname']))."',
				                        lastname='".addslashes(strip_tags($_POST['lastname']))."',
				                        residence='".contrexx_strip_tags($_POST['residence'])."',
									   profession='".contrexx_strip_tags($_POST['profession'])."',
									   interests='".contrexx_strip_tags($_POST['interests'])."',
									   webpage='".contrexx_strip_tags($_POST['webpage'])."',
									    company='".contrexx_strip_tags($_POST['company'])."',
				                        langId=".intval($_POST['adminlang']).",
				                        regdate=CURDATE(),
		    							groups='".$groups."',
		                                active = 1";
			if ($objDatabase->Execute($query) !== false) {
			    unset($_SESSION['users']);
			    $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
			} else {
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		} else {
		    $this->strErrMessage = $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
		}
	}







	/**
	* Shows the group
	*
	* @global    object     $objDatabase
	* @global    array      $_GET['userId']
	* @return    string     parsed content
	*/

	function showGroupsOverview() {
		global $objDatabase, $objTemplate, $_CORELANG;

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_group_overview', 'user_group_overview.html');
		$this->pageTitle = $_CORELANG['TXT_GROUPS'];

		// init variables
		$fromusers="";
		$i=1;

	    $objResult = $objDatabase->Execute("SELECT group_id,
	                       group_name,
	                       group_description,
	                       is_active,
	                       type
	                  FROM ".DBPREFIX."access_user_groups
	              ORDER BY group_id");
	    if ($objResult !== false) {
		    while (!$objResult->EOF) {
				($i % 2) ? $class  = 'row1' : $class  = 'row2';
				($objResult->fields['is_active'] == 1) ? $status = 'green' : $status = 'red';

				$objTemplate->setVariable(array(
					'USERS_ROWCLASS'	=> $class,
					'GROUP_NAME'		=> $objResult->fields['group_name'],
					'GROUP_ID'			=> $objResult->fields['group_id'],
					'GROUP_DESCRIPTION'	=> $objResult->fields['group_description'],
					'GROUP_STATUS'		=> $status,
					'GROUP_TYPE'		=> $objResult->fields['type']
				));

				$groupusers= $this->getUsersInGroup($objResult->fields['group_id']);
				$grouppages = "-";
				$groupEditLink ="index.php?cmd=user&amp;act=modgroup&amp;groupId=".$objResult->fields['group_id'];

				if($objResult->fields['type']=="frontend")
				{
		            $grouppages= count($this->getPagesInGroup($objResult->fields['group_id']));
		            $groupEditLink ="index.php?cmd=user&amp;act=modpubgroup&amp;groupId=".$objResult->fields['group_id'];
				}
				$users = array();
		        foreach($groupusers AS $username) {
		        	$users[] = $username;
		        }
		        $groupusers=implode("<br />",$users);

				$objTemplate->setVariable(array(
					'GROUP_EDIT_LINK'	=> $groupEditLink,
					'GROUP_USERS'		=> $groupusers,
					'GROUP_PAGES'		=> $grouppages
				));
				$objTemplate->parse('groupRow');
				$i++;
				$objResult->MoveNext();
			}
	    }

		$objTemplate->setVariable(array(
			'TXT_GROUP_NAME'               => $_CORELANG['TXT_GROUP_NAME'],
			'TXT_TYPE'                     => $_CORELANG['TXT_TYPE'],
			'TXT_GROUP_ID'                 => $_CORELANG['TXT_GROUP_ID'],
			'TXT_GROUPS'                   => $_CORELANG['TXT_GROUPS'],
			'TXT_USERS'                    => $_CORELANG['TXT_USER'],
			'TXT_PAGE'                     => $_CORELANG['TXT_PAGE'],
			'TXT_STATUS'                   => $_CORELANG['TXT_STATUS'],
			'TXT_STORE'                    => $_CORELANG['TXT_SAVE'],
			'TXT_RESET'                    => $_CORELANG['TXT_RESET'],
			'TXT_DESCRIPTION'              => $_CORELANG['TXT_DESCRIPTION'],
			'TXT_ADD_GROUP'                => $_CORELANG['TXT_ADD_GROUP'],
			'TXT_NAME'                     => $_CORELANG['TXT_NAME'],
			'TXT_ACTION'                   => $_CORELANG['TXT_ACTION'],
			'TXT_PERM_OPEN'                => $_CORELANG['TXT_EXTERNAL'],
			'TXT_ALL_FIELDS_REQUIRED'      => $_CORELANG['TXT_ALL_FIELDS_REQUIRED'],
			'TXT_USERS_DEST'               => $_CORELANG['TXT_ADDED_USERS'],
			'TXT_SELECT_ALL'               => $_CORELANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'             => $_CORELANG['TXT_DESELECT_ALL'],
			'TXT_CONFIRM_DELETE_DATA'      => $_CORELANG['TXT_CONFIRM_DELETE_DATA'],
			'TXT_ACTION_IS_IRREVERSIBLE'   => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
		));
		$objTemplate->setVariable('GROUPS_EDIT_FROMUSER',$fromusers);
	}






	function showNewUser()
	{
		global $objDatabase, $_CORELANG, $objTemplate;

		$options="";
		$is_admin_checkbox="";
		$i=0;

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_add', 'user_add.html');
		$this->pageTitle = $_CORELANG['TXT_ADD_USER'];

		$objResult = $objDatabase->Execute("SELECT group_id,
		                   group_name,
		                   type
		              FROM ".DBPREFIX."access_user_groups
		          ORDER BY group_id");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$options.="<option value=\"".$objResult->fields['group_id']."\"";
				if (!$i) $options.=" selected";
				$options.=">".$objResult->fields['group_name']." [".$objResult->fields['type']."]</option>\n";
				$i++;
				$objResult->MoveNext();
			}
		}

		if($_SESSION['auth']['is_admin']!=1)
        {
        	$is_admin_checkbox="disabled";
	    }

		if(isset($_SESSION['users']))
		{
			$objTemplate->setVariable(array(
				'USERS_LOGINNAME'	=> $_SESSION['users']['username'],
				'USERS_FIRSTNAME'	=> $_SESSION['users']['firstname'],
				'USERS_LASTNAME'	=> $_SESSION['users']['lastname'],
				'USERS_EMAIL'		=> $_SESSION['users']['email'],
				'USERS_PASSWORD'	=> $_SESSION['users']['password'],
				'USERS_PASSWORD2'	=> $_SESSION['users']['password2']
			));
		}

		$objTemplate->setVariable(array(
			'USERS_ADMIN_CHECKBOX'             => $is_admin_checkbox,
			'TXT_FIRST_NAME'                   => $_CORELANG['TXT_FIRST_NAME'],
			'TXT_LAST_NAME'                    => $_CORELANG['TXT_LAST_NAME'],
			'TXT_RESIDENCE'						=> $_CORELANG['TXT_RESIDENCE'],
			'TXT_PROFESSION'					=> $_CORELANG['TXT_PROFESSION'],
			'TXT_INTERESTS'						=> $_CORELANG['TXT_INTERESTS'],
			'TXT_WEBPAGE'						=> $_CORELANG['TXT_WEBPAGE'],
			'TXT_EMAIL'                        => $_CORELANG['TXT_EMAIL'],
			'TXT_STATUS'                       => $_CORELANG['TXT_STATUS'],
			'TXT_LOGIN_NAME'                   => $_CORELANG['TXT_LOGIN_NAME'],
			'TXT_LOGIN_PASSWORD'               => $_CORELANG['TXT_LOGIN_PASSWORD'],
			'TXT_VERIFY_PASSWORD'              => $_CORELANG['TXT_VERIFY_PASSWORD'],
			'TXT_LANGUAGE'                     => $_CORELANG['TXT_LANGUAGE'],
			'TXT_PASSWORD_MINIMAL_CHARACTERS'  => $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
			'TXT_ALL_FIELDS_REQUIRED'          => $_CORELANG['TXT_ALL_FIELDS_REQUIRED'],
			'TXT_PASSWORD_NOT_USERNAME_TEXT'   => $_CORELANG['TXT_PASSWORD_NOT_USERNAME_TEXT'],
			'TXT_ADDUSER'                      => $_CORELANG['TXT_ADD_USER'],
			'TXT_GROUPS'                       => $_CORELANG['TXT_GROUPS'],
			'TXT_GROUPS_DEST'                  => $_CORELANG['TXT_GROUPS_DEST'],
			'TXT_SELECT_ALL'                   => $_CORELANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'                 => $_CORELANG['TXT_DESELECT_ALL'],
			'TXT_ADMIN_STATUS'                 => $_CORELANG['TXT_ADMIN_STATUS'],
			'TXT_RELATEDNESS'                  => $_CORELANG['TXT_BACKEND_RELATEDNESS'],

			'TXT_COMPANY'                  		=> "Firma",
			'TXT_USER_ACCOUNT'					=> $_CORELANG['TXT_USER_ACCOUNT'],
			'TXT_USER_GROUP_S'					=> $_CORELANG['TXT_USER_GROUP_S'],
			'TXT_PROFILE'						=> $_CORELANG['TXT_PROFILE']
		));

		$objTemplate->setVariable(array(
			'USERS_NEW_EDIT_GROUPS'	=> $options,
			'USERS_NEW_EDIT_LANGUAGE_DROPDOWNMENU'	=> $this->languageMenu()
		));
	}



    function _createDatesDropdown($birthday = ''){
		global $objTemplate;

		$day = !empty($birthday) ? $birthday[0] : '01';
		$month = !empty($birthday) ? $birthday[1] : '01';
		$year = !empty($birthday) ? $birthday[2] : date("Y");

		for($i=1;$i<=31;$i++){
			$selected = ($day == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
			$objTemplate->setVariable(array(
				'USERS_BIRTHDAY_DAY'		=> str_pad($i,2,'0', STR_PAD_LEFT),
				'USERS_BIRTHDAY_DAY_NAME'	=> $i,
				'SELECTED_DAY'				=> $selected
			));
			$objTemplate->parse('birthday_day');
		}

		for($i=1;$i<=12;$i++){
			$selected = ($month == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
			$objTemplate->setVariable(array(
				'USERS_BIRTHDAY_MONTH'		=> str_pad($i, 2, '0', STR_PAD_LEFT),
				'USERS_BIRTHDAY_MONTH_NAME'	=> $this->months[$i],
				'SELECTED_MONTH'			=> $selected
			));
			$objTemplate->parse('birthday_month');
		}

		for($i=date("Y");$i>=1900;$i--){
			$selected = ($year == str_pad($i,2,'0',STR_PAD_LEFT)) ? 'selected="selected"' : '' ;
			$objTemplate->setVariable(array(
				'USERS_BIRTHDAY_YEAR' 		=> $i,
				'SELECTED_YEAR'				=> $selected
			));
			$objTemplate->parse('birthday_year');
		}
	}


	/**
	* Add a new group
	*
	* @version   1.0        initial version
	* @global    array      -
	* @global    object     $objDatabase
	* @return    boolean    result
	*/
	function addGroup()
	{
	  	global $objDatabase, $_CORELANG;

		if (!empty($_POST['groupName']))
		{
		    $status=intval($_POST['groupStatus']);

		    // add into access_user_groups table
		    $query = "INSERT INTO ".DBPREFIX."access_user_groups
		                      SET group_name='".addslashes(strip_tags($_POST['groupName']))."',
		                          group_description='".addslashes(strip_tags($_POST['groupDescription']))."',
		                          is_active=".$status.",
		                          type='backend'";

		    if ($objDatabase->Execute($query) !== false) {
				$groupId=$objDatabase->Insert_ID();
				if(is_array($_POST['areaId']))
				{
                    $areaIds = implode(",", $_POST['areaId']);
				}

				// add into user_permissions table
				//////////////////////////////////
				foreach ($_POST['areaId'] as $areaId) {
			    	$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_static_ids (`access_id`, `group_id`) VALUES (".intval($areaId).",".$groupId.")");
			    }

				// add into user table
				//////////////////////////////////
			    $objResult = $objDatabase->Execute("SELECT id, groups FROM ".DBPREFIX."access_users");
			    if ($objResult !== false) {
	                while (!$objResult->EOF) {
	                    $arrUserGroups[$objResult->fields['id']]=$objResult->fields['groups'];
	                    $objResult->MoveNext();
	                }
			    }

                if(isset($_POST['selectedUsers']) AND is_array($_POST['selectedUsers']))
                {
	                foreach($_POST['selectedUsers'] AS $selectedUserId)
	                {
	                    $newgroup = $arrUserGroups[$selectedUserId].",".$groupId;
	                    $objDatabase->Execute("UPDATE ".DBPREFIX."access_users set groups='".$newgroup."' WHERE id=".intval($selectedUserId));
	                }
                }

			    $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
			    return true;
			}
			else
			{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
				return false;
			}
		}
		else
		{
			$this->strErrMessage = $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
			return false;
		}
	}





	/**
	* gets the new user group page
	*
	* @global    object     $objDatabase
	* @global    string     $_CORELANG
	*/
	function showModPubGroup()
	{
		global $objDatabase, $_CORELANG, $_LANGID, $objTemplate, $objInit;

		// init variables
		$unselectedUsers="";
		$selectedUsers="";
		$unselectedPages='';
		$selectedPages = "";
		$groupId=0;
		$formAction ="index.php?cmd=user&amp;act=addpubgroup";
		$arrModules = array();
		$arrModuleFunctions = array();
		$arrLang = array();

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_modpubgroup', 'user_modpubgroup.html');
		$this->pageTitle = $_CORELANG['TXT_GROUPS'];

		// create new ContentTree instance
		$contentTree = &new ContentTree();

		$objResult = $objDatabase->Execute("SELECT areas.access_id AS id, areas.area_name AS title, modules.name AS name FROM ".DBPREFIX."modules AS modules, ".DBPREFIX."backend_areas AS areas WHERE modules.id=areas.module_id");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$arrModules[$objResult->fields['id']] = array(
					'name'	=> stripslashes($objResult->fields['name']),
					'title'	=> stripslashes($objResult->fields['title'])
				);
				$objResult->MoveNext();
			}
		}

		$arrTables = $objDatabase->MetaTables('TABLES');
		if ($arrTables !== false) {
			foreach ($arrModules as $id => $module) {
				if (in_array(DBPREFIX."module_".$module['name']."_access", $arrTables)) {
					$objResult = $objDatabase->Execute("SELECT access_id, description FROM ".DBPREFIX."module_".$module['name']."_access WHERE `type`='global' OR `type`='frontend'");
					if ($objResult !== false && $objResult->RecordCount()>0) {
						while (!$objResult->EOF) {
							$arrModuleFunctions[$id][$objResult->fields['access_id']] = stripslashes($objResult->fields['description']);
							$objResult->MoveNext();
						}
						$arrLang[$id] = $objInit->loadLanguageData($module['name']);
					}
				}
			}
		}

		/////////////////////////////////////////
		// Modify modus
		/////////////////////////////////////////
		if(isset($_GET['groupId']) AND intval($_GET['groupId'])!=0)
		{
		    $groupId = intval($_GET['groupId']);
		    $this->pageTitle = $_CORELANG['TXT_EDIT_GROUP'];
		    $formAction ="index.php?cmd=user&amp;act=updatepubgroup";
		    $groupInfo = $this->getGroupInfo($groupId);
		    $groupPermissionIds = $this->getPermissionInfo($groupId, 'dynamic');

		    ($groupInfo['is_active']==1) ? $is_active="checked" : $is_active="";

			$objTemplate->setVariable(array(
				'USER_GROUP_NAME'              => $groupInfo['group_name'],
				'USER_GROUP_DESCRIPTION'       => $groupInfo['group_description'],
				'USER_GROUP_STATUS'            => $is_active
			));

			// check for users
			$objResult = $objDatabase->Execute("SELECT id,username,groups FROM ".DBPREFIX."access_users WHERE active=1 ORDER BY id");
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$userList[$objResult->fields['id']] = stripslashes($objResult->fields['username']);
					$userGroups[$objResult->fields['id']]=explode(",",$objResult->fields['groups']);
					$objResult->MoveNext();
				}
			}
			foreach($userList AS $id => $username){
			    if(in_array($groupId,$userGroups[$id]))
			        $selectedUsers.="<option value=\"".$id."\">".$username."</option>\n";
			    else
			        $unselectedUsers.="<option value=\"".$id."\">".$username."</option>\n";
			}

			// check for pages (content)
			$spacer="->";
			foreach ($contentTree->getTree() as $data) {
                $spacer="";
                /*
                echo "<pre>";
                print_r($data);
                echo "</pre>";
                */
                $level=intval($data['level']);

                for ($i = 0; $i < $level; $i++) {$spacer .="&nbsp;&nbsp;";}
				if(in_array($data['frontend_access_id'], $groupPermissionIds)){
				    $selectedPages.="<option value=\"".$data['catid']."\">".$spacer.$data['catname']." (".$data['catid'].") </option>\n";
				}
				else {
                    $unselectedPages.="<option value=\"".$data['catid']."\">".$spacer.$data['catname']." (".$data['catid'].") </option>\n";
				}
			}

			$groupPermissionStaticIds = $this->getPermissionInfo($groupId, 'static');
			if (count($arrModuleFunctions)>0) {
				foreach ($arrModuleFunctions as $moduleId => $arrFunction) {
					foreach ($arrFunction as $accessId => $function) {
						$objTemplate->setVariable(array(
							'USER_MANAGEMENT_MODULE_FUNCTION'	=> isset($arrLang[$moduleId][$function]) ? $arrLang[$moduleId][$function] : $function,
							'USER_MANAGEMENT_ACCESS_ID'			=> $accessId,
							'USER_MANAGEMENT_ACCESS_CHECKED'	=> in_array($accessId, $groupPermissionStaticIds) ? "checked=\"checked\"" : ""
						));
						$objTemplate->parse('module_function_list');
					}
					$objTemplate->setVariable('USER_MANAGEMENT_MODULE', $_CORELANG[$arrModules[$moduleId]['title']]);
					$objTemplate->parse('module_list');
				}
			}
		}
		/////////////////////////////////////////
		// Add modus
		/////////////////////////////////////////
		else {
			$objResult = $objDatabase->Execute("SELECT id,username,groups FROM ".DBPREFIX."access_users WHERE active=1 ORDER BY id");
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$unselectedUsers.="<option value=\"".$objResult->fields['id']."\">".$objResult->fields['username']."</option>\n";
					$objResult->MoveNext();
				}
			}

			$spacer="->";
			foreach ($contentTree->getTree() as $data) {
                  $spacer="";
                  $level=intval($data['level']);
                  for ($i = 0; $i < $level; $i++) {$spacer .="&nbsp;&nbsp;";}
				  $unselectedPages.="<option value=\"".$data['catid']."\">".$spacer.$data['catname']." (".$data['catid'].") </option>\n";
			}

			if (count($arrModuleFunctions)>0) {
				foreach ($arrModuleFunctions as $moduleId => $arrFunction) {
					foreach ($arrFunction as $accessId => $function) {
						$objTemplate->setVariable(array(
							'USER_MANAGEMENT_MODULE_FUNCTION'	=> isset($arrLang[$moduleId][$function]) ? $arrLang[$moduleId][$function] : $function,
							'USER_MANAGEMENT_ACCESS_ID'			=> $accessId
						));
						$objTemplate->parse('module_function_list');
					}
					$objTemplate->setVariable('USER_MANAGEMENT_MODULE', $_CORELANG[$arrModules[$moduleId]['title']]);
					$objTemplate->parse('module_list');
				}
			}

		}

		/////////////////////////////////////////
		// Add and modify modus
		/////////////////////////////////////////

		$objTemplate->setVariable(array(
		    'USER_GROUP_ID'                => $groupId,
			'USER_FORM_ACTION'             => $formAction,
			'USER_UNSELECTED'              => $unselectedUsers,
			'USER_SELECTED'                => $selectedUsers,
			'PAGES_UNSELECTED'             => $unselectedPages,
			'PAGES_SELECTED'               => $selectedPages,
			'TXT_ADD_GROUP'                => $_CORELANG['TXT_ADD_GROUP'],
			'TXT_NAME'                     => $_CORELANG['TXT_NAME'],
			'TXT_DESCRIPTION'              => $_CORELANG['TXT_DESCRIPTION'],
			'TXT_STORE'                    => $_CORELANG['TXT_SAVE'],
			'TXT_RESET'                    => $_CORELANG['TXT_RESET'],
			'TXT_USERS_DEST'               => $_CORELANG['TXT_ADDED_USERS'],
			'TXT_SELECT_ALL'               => $_CORELANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'             => $_CORELANG['TXT_DESELECT_ALL'],
			'TXT_USERS'                    => $_CORELANG['TXT_USER'],
			'TXT_ACTIVATED'                => $_CORELANG['TXT_ACTIVATED'],
			'TXT_ASSIGNED_USERS'           => $_CORELANG['TXT_ADDED_USERS'],
			'TXT_EXISTING_USERS'           => $_CORELANG['TXT_EXISTING_USERS'],
			'TXT_PROTECT_RANGES'           => $_CORELANG['TXT_PROTECT_EXISTING_RANGES'],
			'TXT_EXISTING_RANGES'          => $_CORELANG['TXT_EXISTING_RANGES'],
			'TXT_ASSIGNED_RANGES'          => $_CORELANG['TXT_ASSIGNED_RANGES'],
			'TXT_PERMISSIONS'			   => $_CORELANG['TXT_PERMISSIONS'],
			'TXT_MODULE'				   => $_CORELANG['TXT_MODULE'],
			'TXT_ALLOW'					   => $_CORELANG['TXT_ALLOW']
		));
	}




	/**
	* Add a new group
	*
	* @global    array      $_GET['delete']
	* @global    object     $objDatabase
	* @return    boolean    result
	*/
	function addPubGroup()
	{
	  	global $objDatabase, $_CORELANG, $_CONFIG;

	  	$selectedPages='';

		if (!empty($_POST['groupName'])){
		    $status=intval($_POST['groupStatus']);

		    // add into access_user_groups table
		    $query = "INSERT INTO ".DBPREFIX."access_user_groups
		                      SET group_name='".addslashes(strip_tags($_POST['groupName']))."',
		                          group_description='".addslashes(strip_tags($_POST['groupDescription']))."',
		                          is_active='".$status."',
		                          type='frontend'";

		    if($objDatabase->Execute($query) !== false) {
				$groupId=$objDatabase->Insert_ID();
				//////////////////////////////////
				// update navigation table
				//////////////////////////////////
				if(is_array($_POST['selectedPages'])){
                    // create new ContentTree instance
                    $contentTree = &new ContentTree();
                    //print_r($contentTree->getTree());
                    foreach($_POST['selectedPages'] AS $pageId){
                    	$nodeData=$contentTree->getThisNode($pageId);
						//print_r($nodeData);
                    	if (!empty($nodeData['frontend_access_id'])) {
							$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids (`access_id`, `group_id`) VALUES (".$nodeData['frontend_access_id'].", ".$groupId.")");
						} else {
							$_CONFIG['lastAccessId']++;
							if ($objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET frontend_access_id=".$_CONFIG['lastAccessId'].", protected=1 WHERE catid=".intval($pageId)) !== false) {
								$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids (`access_id`, `group_id`) VALUES (".$_CONFIG['lastAccessId'].", ".$groupId.")");
							} else {
								$_CONFIG['lastAccessId']--;
							}
						}
                    }
                    $objDatabase->Execute("UPDATE ".DBPREFIX."settings SET setvalue=".$_CONFIG['lastAccessId']." WHERE setname='lastAccessId'");
				}
				//////////////////////////////////
				// update user table
				//////////////////////////////////
			    $objResult = $objDatabase->Execute("SELECT id, groups FROM ".DBPREFIX."access_users");
			    if ($objResult !== false) {
	                while (!$objResult->EOF) {
	                    $arrUserGroups[$objResult->fields['id']]=$objResult->fields['groups'];
	                    $objResult->MoveNext();
	                }
			    }

                if(isset($_POST['selectedUsers']) AND is_array($_POST['selectedUsers'])){
	                foreach($_POST['selectedUsers'] AS $selectedUserId){
	                    $newgroup = $arrUserGroups[$selectedUserId].",".$groupId;
	                    $objDatabase->Execute("UPDATE ".DBPREFIX."access_users set groups='".$newgroup."' WHERE id=".intval($selectedUserId));
	                }
                }

                //////////////////////////////////
				// update access ids
				//////////////////////////////////
				if (is_array($_POST['accessId'])) {
					foreach ($_POST['accessId'] as $accessId) {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_static_ids (`access_id`, `group_id`) VALUES (".intval($accessId).",".$groupId.")");
					}
				}

                $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
			    return true;
			}
			else {
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
				return false;
			}
		} else {
			$this->strErrMessage = $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
			return false;
		}
	}





	/**
	* updates public groups
	*
	* @global    array      $_CORELANG
	* @global    object     $objDatabase
	* @return    boolean    true/false
	*/
	function updatePubGroup()
	{
	  	global $objDatabase, $_CORELANG, $_CONFIG;

	  	$selectedPages='';
	  	$groupId=intval($_POST['groupId']);

		if (!empty($_POST['groupName'])){
		    $status=intval($_POST['groupStatus']);
			$query = "UPDATE ".DBPREFIX."access_user_groups
						   SET group_name='".addslashes(strip_tags($_POST['groupName']))."',
							   group_description='".addslashes(strip_tags($_POST['groupDescription']))."',
							   is_active='".$status."',
							   type='frontend'
						 WHERE group_id=".$groupId;
		    if ($objDatabase->Execute($query) !== false) {
				//////////////////////////////////
				// update user table where groupId is index of groups
				//////////////////////////////////
			    $objResult = $objDatabase->Execute("SELECT id, groups FROM ".DBPREFIX."access_users");
			    if ($objResult !== false) {
	                while (!$objResult->EOF) {
	                    $arrUserGroups[$objResult->fields['id']]=$objResult->fields['groups'];
	                    $objResult->MoveNext();
	                }
			    }
                if(isset($_POST['selectedUsers']) AND is_array($_POST['selectedUsers'])) {
	                foreach($_POST['selectedUsers'] AS $userId){
                	    $testArray=explode(",",$arrUserGroups[$userId]);
	                	if (!in_array($groupId, $testArray))
						{
	                        array_push ($testArray, $groupId);
	                        $testGroups = implode(",",$testArray);
	                        $objDatabase->Execute("UPDATE ".DBPREFIX."access_users set groups='".$testGroups."' WHERE id=".intval($userId));
	                    }
	                }
                }
                if(isset($_POST['notSelectedUsers']) AND is_array($_POST['notSelectedUsers'])) {
	                foreach($_POST['notSelectedUsers'] AS $userId) {
		                	// create array
		                	$testArray=explode(",",$arrUserGroups[$userId]);
		                	if (in_array($groupId, $testArray)){
								unset($testArray[array_search($groupId, $testArray)]);
		                        $testGroups=implode(",",$testArray);
		                        $objDatabase->Execute("UPDATE ".DBPREFIX."access_users set groups='".$testGroups."' WHERE id=".intval($userId));
		                    }
	                }
                }

                $arrRightIds = $this->getPermissionInfo($groupId, 'dynamic');
				//////////////////////////////////
				// update navigation table
				if(is_array($_POST['selectedPages'])){
                    $contentTree = &new ContentTree();
                    foreach($_POST['selectedPages'] AS $pageId) {
						$nodeData=$contentTree->getThisNode($pageId);
                    	if (!empty($nodeData['frontend_access_id'])) {
                    		if (!in_array($nodeData['frontend_access_id'], $arrRightIds)) {
                    			$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids (`access_id`, `group_id`) VALUES (".$nodeData['frontend_access_id'].", ".$groupId.")");
                    		}
						} else {
							$_CONFIG['lastAccessId']++;
							if ($objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET frontend_access_id=".$_CONFIG['lastAccessId'].", protected=1 WHERE catid=".intval($pageId)) !== false) {
                    			$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_dynamic_ids (`access_id`, `group_id`) VALUES (".$_CONFIG['lastAccessId'].", ".$groupId.")");
							} else {
								$_CONFIG['lastAccessId']--;
							}
						}
                    }
                    $objDatabase->Execute("UPDATE ".DBPREFIX."settings SET setvalue=".$_CONFIG['lastAccessId']." WHERE setname='lastAccessId'");
				}

				// delete unselected pages who where selected before
				if(is_array($_POST['notSelectedPages'])){
                    $contentTree = &new ContentTree();
					foreach($_POST['notSelectedPages'] AS $pageId) {
						$nodeData=$contentTree->getThisNode($pageId);
                    	if (!empty($nodeData['frontend_access_id'])) {
                    		if (in_array($nodeData['frontend_access_id'], $arrRightIds)) {
                    			$objDatabase->Execute("DELETE FROM ".DBPREFIX."access_group_dynamic_ids WHERE access_id=".$nodeData['frontend_access_id']." AND group_id=".$groupId);

                    			$objResult = $objDatabase->SelectLimit("SELECT access_id FROM ".DBPREFIX."access_group_dynamic_ids WHERE access_id=".$nodeData['frontend_access_id'], 1);
                    			if ($objResult !== false) {
                    				if ($objResult->RecordCount() == 0) {
                    					$objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET protected=0, frontend_access_id=0 WHERE catid=".intval($pageId));
                    				}
                    			}
                    		}
						}
                    }
				}

				// update access ids
				$objDatabase->Execute("DELETE FROM ".DBPREFIX."access_group_static_ids WHERE group_id=".$groupId);
				if (is_array($_POST['accessId'])) {
					foreach ($_POST['accessId'] as $accessId) {
						$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_static_ids (`access_id`, `group_id`) VALUES (".intval($accessId).", ".$groupId.")");
					}
				}

			    $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_ADDED_SUCCESSFUL'];
			    return true;
			}
			else
			{
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
				return false;
			}
		}
		else
		{
			$this->strErrMessage = $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
			return false;
		}
	}



	/**
	* gets the new user group page
	*
	* @global    object     $objDatabase
	* @global    string     $_CORELANG
	*/
	function showModGroup()
	{
		global $objDatabase, $_CORELANG, $objTemplate;

		// init variables
		$unselectedUsers="";
		$selectedUsers="";
		$groupId="";
		$is_backend="";
		$formAction ="index.php?cmd=user&amp;act=addgroup";
		$groupPermissionIds = "";
		$arrModules = array();

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_modgroup', 'user_modgroup.html');
		$this->pageTitle = $_CORELANG['TXT_GROUPS'];

		/////////////////////////////////////////
		// Modify modus
		/////////////////////////////////////////
		if(isset($_GET['groupId']) AND intval($_GET['groupId'])!=0){
		    $groupId = intval($_GET['groupId']);
		    $this->pageTitle = $_CORELANG['TXT_EDIT_GROUP'];
		    $formAction ="index.php?cmd=user&amp;act=updategroup";
		    $groupInfo = $this->getGroupInfo($groupId);
		    $groupPermissionIds = $this->getPermissionInfo($groupId, 'static');

		    ($groupInfo['is_active']==1) ? $is_active="checked" : $is_active="";

			$objTemplate->setVariable(array(
				'USER_GROUP_NAME'              => $groupInfo['group_name'],
				'USER_GROUP_DESCRIPTION'       => $groupInfo['group_description'],
				'USER_GROUP_STATUS'            => $is_active
			));

			$objResult = $objDatabase->Execute("SELECT id,username,groups FROM ".DBPREFIX."access_users WHERE active=1 ORDER BY id");
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$userList[$objResult->fields['id']]=stripslashes($objResult->fields['username']);
					$userGroups[$objResult->fields['id']]=explode(",",$objResult->fields['groups']);
					$objResult->MoveNext();
				}
			}
			foreach($userList AS $id => $username){
			    if(in_array($groupId,$userGroups[$id]))
			        $selectedUsers.="<option value=\"".$id."\">".$username."</option>\n";
			    else
			        $unselectedUsers.="<option value=\"".$id."\">".$username."</option>\n";
			}
		}
		/////////////////////////////////////////
		// Add modus
		/////////////////////////////////////////
		else {
			$objResult = $objDatabase->Execute("SELECT id,username,groups FROM ".DBPREFIX."access_users WHERE active=1 ORDER BY id");
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					$unselectedUsers.="<option value=\"".$objResult->fields['id']."\">".$objResult->fields['username']."</option>\n";
					$objResult->MoveNext();
				}
			}
		}

		/////////////////////////////////////////
		// Add and modify modus
		/////////////////////////////////////////
		$objResult = $objDatabase->Execute("SELECT * FROM ".DBPREFIX."backend_areas WHERE is_active=1 ORDER BY parent_area_id, order_id");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
		        $checked="checked";
		        if (is_array($groupPermissionIds)) {
		        	$checked = in_array($objResult->fields['access_id'], $groupPermissionIds) ? "checked" : "";
		        }
				$arr[$objResult->fields['area_id']] = array( 'name' => $objResult->fields['area_name'],
												'access_id'	=> $objResult->fields['access_id'],
		        	                            'status' => $objResult->fields['is_active'],
		        	                            'type' => $objResult->fields['type'],
		        	                            'group_id' => $objResult->fields['parent_area_id'],
		        	                            'checked' => $checked);
				$objResult->MoveNext();
			}
		}

		$objResult = $objDatabase->Execute("SELECT areas.access_id AS id, modules.name AS name FROM ".DBPREFIX."modules AS modules, ".DBPREFIX."backend_areas AS areas WHERE modules.id=areas.module_id");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$arrModules[$objResult->fields['id']] = $objResult->fields['name'];
				$objResult->MoveNext();
			}
		}

		$arrTables = $objDatabase->MetaTables('TABLES');
		if ($arrTables !== false) {
			foreach ($arrModules as $id => $module) {
				if (in_array(DBPREFIX."module_".$module."_access", $arrTables)) {
					$objResult = $objDatabase->Execute("SELECT access_id, description FROM ".DBPREFIX."module_".$module."_access WHERE `type`='global' OR `type`='backend'");
					if ($objResult !== false) {
						while (!$objResult->EOF) {
							if (is_array($groupPermissionIds)) {
								$checked = in_array($objResult->fields['access_id'], $groupPermissionIds) ? "checked" : "";
							}

							$arr[$objResult->fields['access_id']] = array(
								'access_id'	=> $objResult->fields['access_id'],
								'name'	=> $objResult->fields['description'],
								'status'	=> 1,
								'type'	=> 'function',
								'group_id'	=> $id,
								'checked'	=> $checked
							);
							$objResult->MoveNext();
						}
					}
				}
			}
		}

		foreach ($arr AS $group_id => $group_data ){
			if($group_data['type']=="group"){
				$objTemplate->setVariable(array(
					'USER_AREA_GROUP_ID'            => $group_data['access_id'],
					'USER_AREA_GROUP_NAME'          => isset($_CORELANG[$group_data['name']]) ? $_CORELANG[$group_data['name']] : $group_data['name'],
					'USER_AREA_GROUP_STATUS'        => "",
					'USER_AREA_GROUP_PERMISSION'    => $group_data['checked'],
					'USER_AREA_GROUP_ROWCLASS'     => "row3"
				));
				foreach ($arr AS $nav_id => $nav_data) {
					if($group_id==$nav_data['group_id'] AND $nav_data['type']=="navigation"){
						$objTemplate->setVariable(array(
							'USER_AREA_ID'             => $nav_data['access_id'],
							'USER_AREA_NAME'           => isset($_CORELANG[$nav_data['name']]) ? $_CORELANG[$nav_data['name']] : $nav_data['name'],
							'USER_AREA_STATUS'         => "",
							'USER_AREA_PERMISSION'     => $nav_data['checked'],
					        'USER_AREA_ROWCLASS'      => "row1"
						));
						foreach ($arr AS $function_id => $function_data) {
							if($nav_id==$function_data['group_id'] AND $function_data['type']=="function"){
								$objTemplate->setVariable(array(
									'USER_AREA_FUNCTION_ID'             => $function_data['access_id'],
									'USER_AREA_FUNCTION_NAME'           => isset($_CORELANG[$function_data['name']]) ? $_CORELANG[$function_data['name']] : $function_data['name'],
									'USER_AREA_FUNCTION_STATUS'         => "",
									'USER_AREA_FUNCTION_PERMISSION'     => $function_data['checked'],
							        'USER_AREA_FUNCTION_ROWCLASS'      => "row2"
								));
								$objTemplate->parse('functionPermissions');
							}
						}
						$objTemplate->parse('areaPermissions');
					}
				}
			}
			$objTemplate->parse('groupPermissions');
		}

		$objTemplate->setVariable(array(
		    'USER_GROUP_ID'                => $groupId,
			'USER_FORM_ACTION'             => $formAction,
			'USER_UNSELECTED'              => $unselectedUsers,
			'USER_SELECTED'                => $selectedUsers,
			'TXT_ADD_GROUP'                => $_CORELANG['TXT_ADD_GROUP'],
			'TXT_NAME'                     => $_CORELANG['TXT_NAME'],
			'TXT_DESCRIPTION'              => $_CORELANG['TXT_DESCRIPTION'],
			'TXT_STORE'                    => $_CORELANG['TXT_SAVE'],
			'TXT_RESET'                    => $_CORELANG['TXT_RESET'],
			'TXT_USERS_DEST'               => $_CORELANG['TXT_ADDED_USERS'],
			'TXT_SELECT_ALL'               => $_CORELANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'             => $_CORELANG['TXT_DESELECT_ALL'],
			'TXT_USERS'                    => $_CORELANG['TXT_USER'],
			'TXT_PERMISSIONS'              => $_CORELANG['TXT_PERMISSIONS'],
			'TXT_ALLOW'                    => $_CORELANG['TXT_ALLOW'],
			'TXT_AREAS'                    => $_CORELANG['TXT_AREAS']
		));
	}








	/**
	* Updates users into the db
	*
	* @global    object     $objDatabase
	* @return    boolean    result
	*/
	function updateUser()
	{
		global $objDatabase, $_CORELANG;

		$groups="";
		$is_admin=0;
		$redirectCode = "\n<script language='JavaScript' type='text/javascript'>\n<!-- \nwindow.setTimeout(\"location.href=('?cmd=user')\",1500); \n//-->\n</script>\n";


		if ((!empty($_POST['userId'])) AND
		    (!empty($_POST['username'])) AND
		    (!empty($_POST['email'])) AND
		    (!empty($_POST['adminlang'])))
		{
			$userId=intval($_POST['userId']);

			if (!empty($_POST['destuser'])) {
				$groups=implode(",",$_POST['destuser']);
			}
            // just admins can change the is_admin selection
			if (isset($_POST['is_admin']) AND $_POST['is_admin']==1 AND $_SESSION['auth']['is_admin']==1) {
				$is_admin=1;
			}

			// set new password
			if(strlen($_POST['password'])>=6){
			    $_POST['password']= md5($_POST['password']);
				$query = "UPDATE ".DBPREFIX."access_users
							   SET username='".addslashes(strip_tags($_POST['username']))."',
								   password='".$_POST['password']."',
								   email='".addslashes(strip_tags($_POST['email']))."',
								   firstname='".addslashes(strip_tags($_POST['firstname']))."',
								   lastname='".addslashes(strip_tags($_POST['lastname']))."',
								   residence='".contrexx_strip_tags($_POST['residence'])."',
								   profession='".contrexx_strip_tags($_POST['profession'])."',
								   interests='".contrexx_strip_tags($_POST['interests'])."',
								   webpage='".contrexx_strip_tags($_POST['webpage'])."',
								   company='".contrexx_strip_tags($_POST['company'])."',
								   langId=".intval($_POST['adminlang']).",
				                   is_admin='".$is_admin."',
				                   groups='".addslashes(strip_tags($groups))."'
							 WHERE id='".$userId."'";

				if($objDatabase->Execute($query) !== false) {
					if($_SESSION['auth']['userid']==$userId) {
				        $_SESSION['auth']['username']=addslashes(strip_tags($_POST['username']));
					    $_SESSION['auth']['password']=$_POST['password'];
					    $_SESSION['auth']['lang']= intval($_POST['adminlang']);
					}
                    $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'].$redirectCode;
			    	return true;
			    }

			}
			// no new password is given
			else{
				$query= "UPDATE ".DBPREFIX."access_users
				           SET username='".addslashes(strip_tags($_POST['username']))."',
								email='".addslashes(strip_tags($_POST['email']))."',
								firstname='".addslashes(strip_tags($_POST['firstname']))."',
								lastname='".addslashes(strip_tags($_POST['lastname']))."',
								residence='".contrexx_strip_tags($_POST['residence'])."',
								profession='".contrexx_strip_tags($_POST['profession'])."',
								interests='".contrexx_strip_tags($_POST['interests'])."',
								webpage='".contrexx_strip_tags($_POST['webpage'])."',
								company='".contrexx_strip_tags($_POST['company'])."',
								langId=".intval($_POST['adminlang']).",
								is_admin='".$is_admin."',
								groups='".addslashes(strip_tags($groups))."'
						 WHERE id='".intval($_POST['userId'])."'";

				if($objDatabase->Execute($query) !== false) {
					if($_SESSION['auth']['userid']==$userId){
						$_SESSION['auth']['username']=addslashes(strip_tags($_POST['username']));
						$_SESSION['auth']['lang']=intval($_POST['adminlang']);
					}
		        	$this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'].$redirectCode;
		            return true;
		        }
			}
		}
		$this->strErrMessage = $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
		return false;
	}



	/**
	* Shows the user edit page
	*
	* @global    object     $objDatabase
	* @global    array      $_GET['userId']
	* @return    string     parsed content
	*/

	function showEditUser()
	{
		global $objDatabase, $_CORELANG, $objTemplate;

		$is_admin="";
		$notSelectedGroups ="";
		$selectedGroups ="";
		$is_admin_checkbox="";

		$userId = intval($_GET['userId']);

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_edit', 'user_edit.html');
		$this->pageTitle = $_CORELANG['TXT_EDIT_USER_ACCOUNT'];

		$objTemplate->setVariable(array(
			'TXT_USER_NAME'                    => $_CORELANG['TXT_USERNAME'],
			'TXT_FIRST_NAME'                   => $_CORELANG['TXT_FIRST_NAME'],
			'TXT_LAST_NAME'                    => $_CORELANG['TXT_LAST_NAME'],
			'TXT_RESIDENCE'						=> $_CORELANG['TXT_RESIDENCE'],
			'TXT_PROFESSION'					=> $_CORELANG['TXT_PROFESSION'],
			'TXT_INTERESTS'						=> $_CORELANG['TXT_INTERESTS'],
			'TXT_WEBPAGE'						=> $_CORELANG['TXT_WEBPAGE'],
			'TXT_COMPANY'						=> "Firma",
			'TXT_EMAIL'                        => $_CORELANG['TXT_EMAIL'],
			'TXT_ADMIN_STATUS'                 => $_CORELANG['TXT_ADMIN_STATUS'],
			'TXT_PASSWORD'                     => $_CORELANG['TXT_PASSWORD'],
			'TXT_LANGUAGE'                     => $_CORELANG['TXT_LANGUAGE'],
			'TXT_PASSWORD_MINIMAL_CHARACTERS'  => $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
			'TXT_PASSWORD_FIELD_EMPTY'         => $_CORELANG['TXT_PASSWORD_FIELD_EMPTY'],
			'TXT_PASSWORD_MD5_ENCRYPTED'       => $_CORELANG['TXT_PASSWORD_MD5_ENCRYPTED'],
			'TXT_ACCEPT_CHANGES'               => $_CORELANG['TXT_ACCEPT_CHANGES'],
			'TXT_SELECT_ALL'                   => $_CORELANG['TXT_SELECT_ALL'],
			'TXT_DESELECT_ALL'                 => $_CORELANG['TXT_DESELECT_ALL'],
			'TXT_GROUPS'                       => $_CORELANG['TXT_GROUPS'],
			'TXT_GROUPS_DEST'                  => $_CORELANG['TXT_GROUPS_DEST'],
			'TXT_USER_ADMIN_RIGHTS'            => $_CORELANG['TXT_USER_ADMIN_RIGHTS'],
			'TXT_USER_ACCOUNT'					=> $_CORELANG['TXT_USER_ACCOUNT'],
			'TXT_USER_GROUP_S'					=> $_CORELANG['TXT_USER_GROUP_S'],
			'TXT_PROFILE'					=> $_CORELANG['TXT_PROFILE']
		));

		$objResult =$objDatabase->Execute("SELECT langId,
		                   id,
		                   username,
		                   email,
		                   firstname,
		                   lastname,
		                   residence,
		                   profession,
		                   interests,
		                   webpage,
		                   company,
		                   groups,
		                   is_admin
		             FROM ".DBPREFIX."access_users
		            WHERE id=".$userId);

		if ($objResult !== false && !$objResult->EOF) {
			$arrUserGroups=explode(",", $objResult->fields["groups"]);
			$objResult2 = $objDatabase->Execute("SELECT group_id,group_name,type FROM ".DBPREFIX."access_user_groups");
			if ($objResult2 !== false) {
		    	while (!$objResult2->EOF) {
					$key = $objResult2->fields['group_id'];
					if (in_array($key, $arrUserGroups)) {
					   $selectedGroups.="<option value=\"".$key."\">".$objResult2->fields['group_name']." [".$objResult2->fields['type']."]</option>\n";
					} else {
					   $notSelectedGroups.="<option value=\"".$key."\">".$objResult2->fields['group_name']." [".$objResult2->fields['type']."]</option>\n";
					}
					$objResult2->MoveNext();
		    	}
			}

	        if($objResult->fields['is_admin']==1){
	        	$is_admin="checked";
		    }
		    elseif($_SESSION['auth']['is_admin']==1){
		    	$is_admin='';
		    }
		    else {
	        	$is_admin="disabled";
	        }

	        $objTemplate->setVariable(array(
	        	'USERS_ADMIN_STATUS'	=> $is_admin,
				'TR_STATUS'				=> $visible_tr,
				'GROUPS_EDIT_FROMUSER'	=> $notSelectedGroups,
				'GROUPS_EDIT_DESTUSER'	=> $selectedGroups,
				'USERS_ID'				=> $objResult->fields['id'],
				'USERS_USERNAME'		=> stripslashes($objResult->fields['username']),
				'USERS_PASSWORD'		=> '',
				'USERS_EMAIL'			=> stripslashes($objResult->fields['email']),
				'USERS_FIRSTNAME'		=> stripslashes($objResult->fields['firstname']),
				'USERS_LASTNAME'		=> stripslashes($objResult->fields['lastname']),
				'USERS_LASTNAME'		=> stripslashes($objResult->fields['lastname']),
				'USERS_RESIDENCE'		=> stripslashes($objResult->fields['residence']),
				'USERS_PROFESSION'		=> stripslashes($objResult->fields['profession']),
				'USERS_INTERESTS'		=> stripslashes($objResult->fields['interests']),
				'USERS_WEBPAGE'			=> stripslashes($objResult->fields['webpage']),
				'USERS_COMPANY'			=> stripslashes($objResult->fields['company'])
			));
			$status= $objResult->fields['status'];
			$lang = $objResult->fields['langId'];
		}
		$objTemplate->setVariable('USERS_LANGUAGE_MENU', $this->languageMenu($lang));
	}




	/**
	* Edit a group
	*
	* @global    object     $objDatabase

	* @return    boolean    result
	*/
	function updateGroup()
	{
	  	global $objDatabase, $_CORELANG;

	  	// init variables
	  	$newGroups="";
	  	$selectedUsers = array();
	  	$arrOldRights = array();
	  	$arrAddRights = array();
	  	$arrNewRights = array();


		if (!empty($_POST['groupName']) AND !empty($_POST['groupId'])){
	        /////////////////////////////////
	        // update user_group table
	        /////////////////////////////////
			(intval($_POST['groupStatus'])==1)? $is_active=1 : $is_active=0;

			$type='backend';
			$groupId=intval($_POST['groupId']);

			// standard backend group is write protected
			if(groupId==1){
				$type="backend";
				$is_active=1;
			}

			// standard public group is write protected
			if(groupId==2){
				$type="frontend";
				$is_active=1;
			}

	 		$objDatabase->Execute("UPDATE ".DBPREFIX."access_user_groups
			             SET group_name='".addslashes(strip_tags($_POST['groupName']))."',
		                     group_description='".addslashes(strip_tags($_POST['groupDescription']))."',
		                     is_active='".$is_active."',
		                     type='".$type."'
		               WHERE group_id=".$groupId);


		    /////////////////////////////////
		    // update group_static_rights
		    /////////////////////////////////
			if (is_array($_POST['areaId'])) {
				$arrNewRights = $_POST['areaId'];
			}

			$objResult = $objDatabase->Execute("SELECT access_id FROM ".DBPREFIX."access_group_static_ids WHERE group_id=".$groupId);
			if ($objResult !== false) {
				while (!$objResult->EOF) {
					array_push($arrOldRights, $objResult->fields['access_id']);
					$objResult->MoveNext();
				}
			}

			$arrRemoveRights = array_diff($arrOldRights, $arrNewRights);
			$arrAddRights = array_diff($arrNewRights, $arrOldRights);

			// remove unused right ids by the group
			foreach ($arrRemoveRights as $rightId) {
				$objDatabase->Execute("DELETE FROM ".DBPREFIX."access_group_static_ids WHERE access_id=".intval($rightId)." AND group_id=".$groupId);
			}

			// add new right ids for the group
			foreach ($arrAddRights as $rightId) {
				$objDatabase->Execute("INSERT INTO ".DBPREFIX."access_group_static_ids (`access_id` , `group_id`) VALUES (".intval($rightId).",".$groupId.")");
			}


			/////////////////////////////////
			// update users table
			/////////////////////////////////
			if (isset($_POST['selectedUsers']) AND !empty($_POST['selectedUsers'])){
				$selectedUsers=$_POST['selectedUsers'];
			}

			$objResult = $objDatabase->Execute("SELECT id, groups, is_admin FROM ".DBPREFIX."access_users");
			if ($objResult !== false) {
				while (!$objResult->EOF) {
				    $users[$objResult->fields['id']]=$objResult->fields['groups'];
				    $admins[$objResult->fields['id']]=$objResult->fields['is_admin'];
				    $objResult->MoveNext();
				}
			}
			foreach($users AS $id => $groupData){
				$arrUserGroups=explode(",",$groupData);
				if(in_array($id, $selectedUsers)){
					// add new group
					if(!in_array($groupId, $arrUserGroups)){
						array_push($arrUserGroups, $groupId);
						$newGroups=implode(",",$arrUserGroups);
						$query="UPDATE ".DBPREFIX."access_users set groups='".$newGroups."' WHERE id=".$id;
						if ($objDatabase->Execute($query) === false) {
			        		$this->strErrMessage .= $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			        		return false;
						}
					}
				} else {
					// delete group
					if(in_array($groupId, $arrUserGroups)){
                        unset($arrUserGroups[array_search($groupId, $arrUserGroups)]);
                        if(is_array($arrUserGroups)){
						    $newGroups=implode(",",$arrUserGroups);
                        }
                        // do not delete group no.1 if the user is admin
                        if($groupId==1 AND $admins[$id]==1){
			        		$this->strErrMessage .= $_CORELANG['TXT_CANNOT_DELETE_ADMINS'];
                        } else {
							$query="UPDATE ".DBPREFIX."access_users set groups='".$newGroups."' WHERE id=".$id;
							if($objDatabase->Execute($query) === false) {
				        		$this->strErrMessage .= $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
				        		return false;
							}
                        }
					}
				}
			}
		    $this->strOkMessage .= $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
		    return true;
		}
		$this->strErrMessage .= $_CORELANG['TXT_FILL_OUT_ALL_REQUIRED_FIELDS'];
		return false;
	}



	/**
	* Delete a group
	*
	* @global    object     $objDatabase
	* @return    boolean    result
	*/
	function deleteGroup()
	{
	    global $objDatabase, $_CORELANG, $objDatabase;

		$id = intval($_GET['groupId']);

		// standard groups 1,2 are write protected!
		if ($id > 2){
			// Delete access_user_groups entries
			$query="DELETE FROM ".DBPREFIX."access_user_groups WHERE group_id=".$id;
		   	if($objDatabase->Execute($query) !== false) {
		   		// Delete users entries
		   		$arrUsers=$this->getUsersInGroup($id);
		   		foreach ($arrUsers AS $key => $value){
		        	$objResult = $objDatabase->Execute("SELECT groups FROM ".DBPREFIX."access_users WHERE id=".$key);
		        	if ($objResult !== false && !$objResult->EOF) {
		        		$arrGroupId =explode(",",$objResult->fields['groups']);
		        		$newgrouparray= array();
		        		foreach ($arrGroupId AS $value){
		        			if ($value!=$id){
		        				$newgrouparray[]=$value;
		        			}
		        		}
		        		$newgroup=implode(",",$newgrouparray);
		        		$query="UPDATE ".DBPREFIX."access_users set groups='".$newgroup."' WHERE id=".$key;
		        		$objDatabase->Execute($query);
		        	}
		        }

                $arrRightIds = $this->getPermissionInfo($id, 'dynamic');

                // Delete right entries
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."access_group_dynamic_ids WHERE group_id=".$id);
                $objDatabase->Execute("DELETE FROM ".DBPREFIX."access_group_static_ids WHERE group_id=".$id);

		        $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_DELETED_SUCCESSFUL'];

		        foreach ($arrRightIds as $rightId) {
		        	$objResult = $objDatabase->SelectLimit("SELECT access_id FROM ".DBPREFIX."access_group_dynamic_ids WHERE access_id=".$rightId,1);
		        	if ($objResult !== false) {
		        		if ($objResult->RecordCount() == 0) {
		        			$objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET protected=0, frontend_access_id=0 WHERE frontend_access_id=".$rightId);
		        			$objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET backend_access_id=0 WHERE backend_access_id=".$rightId);
		        		}
		        	}
		        }
		        return true;
		   	}
		    else {
	      		$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
	      		return false;
	        }
		}
	    else {
      		$this->strErrMessage = $_CORELANG['TXT_CANNOT_DELETE_STANDARD_GROUPS'];
      		return true;
        }
	}


	/**
	* Returns the pages in a group
	*
	* @return    string     parsed content
	*/

	function getPagesInGroup($group) {
		global $objDatabase;

		$objResult = $objDatabase->Execute("SELECT content.catid AS catid FROM ".DBPREFIX."content_navigation AS content, ".DBPREFIX."access_group_dynamic_ids AS rights WHERE rights.group_id=".$group." AND content.frontend_access_id=rights.access_id");

		$pages=array();
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				array_push ($pages,$objResult->fields["catid"]);
				$objResult->MoveNext();
			}
		}
		return $pages;
	}


	/**
	* Returns the users in a group
	*
	* @return    string     parsed content
	*/

	function getUsersInGroup($groupId) {
		global $objDatabase;

		$arrUsers=array();

		$objResult = $objDatabase->Execute("SELECT id,username,groups FROM ".DBPREFIX."access_users");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$groupscurr=explode(",", $objResult->fields['groups']);
				if (in_array($groupId, $groupscurr)) {
				   $arrUsers[$objResult->fields['id']]= $objResult->fields['username'];
				}
				$objResult->MoveNext();
			}
		}
		return $arrUsers;
	}




	function languageMenu($selectedOption="")
	{
		global $objDatabase;

		$strMenu = "";
		$objResult = $objDatabase->Execute("SELECT id,name FROM ".DBPREFIX."languages WHERE id<>0");
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$selected = ($selectedOption==$objResult->fields['id']) ? "selected" : "";
				$strMenu .="<option value=\"".$objResult->fields['id']."\" $selected>".$objResult->fields['name']."</option>\n";
				$objResult->MoveNext();
			}
		}
		return $strMenu;
	}




	/**
	* Deletes the selected user from the db
	*
	* @global    object     $objDatabase
	* @return    boolean    result
	*/
	function activateUser()
	{
	    global $objDatabase, $_CORELANG, $_CONFIG;

		$id = intval($_GET['userId']);

		if ($id>0){
			if($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET active=1 WHERE id=$id") !== false){
				$this->strOkMessage = $_CORELANG['TXT_USER_ACTIVATED'];

				if (isset($_GET['sendmail'])) {
					$_GET['sendmail'] = intval($_GET['sendmail']);
					if ($_GET['sendmail'] == 1) {
						$objResult = $objDatabase->Execute("SELECT email, username FROM ".DBPREFIX."access_users WHERE id=".$id);
						if ($objResult !== false && $objResult->RecordCount() == 1) {
							$sendto = $objResult->fields['email'];
							$subject = str_replace("%HOST%", $_CONFIG['domainUrl'], $_CORELANG['TXT_ACCOUNT_ACTIVATED']);
							$message = str_replace(array("%HOST%","%USERNAME%"), array("http://".$_CONFIG['domainUrl'].'/', $objResult->fields['username']), $_CORELANG['TXT_ACCOUNT_ACTIVATION_MAIL']);

							if (@include_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php') {
								$objMail = new phpmailer();

								if ($_CONFIG['coreSmtpServer'] > 0 && @include_once ASCMS_CORE_PATH.'/SmtpSettings.class.php') {
									$objSmtpSettings = new SmtpSettings();
									if (($arrSmtp = $objSmtpSettings->getSmtpAccount($_CONFIG['coreSmtpServer'])) !== false) {
										$objMail->IsSMTP();
										$objMail->Host = $arrSmtp['hostname'];
										$objMail->Port = $arrSmtp['port'];
										$objMail->SMTPAuth = true;
										$objMail->Username = $arrSmtp['username'];
										$objMail->Password = $arrSmtp['password'];
									}
								}

								$objMail->CharSet = CONTREXX_CHARSET;
								$objMail->From = $_CONFIG['coreAdminEmail'];
								$objMail->FromName = $_CONFIG['coreAdminName'];
								$objMail->AddReplyTo($_CONFIG['coreAdminEmail']);
								$objMail->Subject = $subject;
								$objMail->IsHTML(false);
								$objMail->Body = $message;
								$objMail->AddAddress($sendto);
							}

							if ($objMail && $objMail->Send()) {
								$this->strOkMessage .= "<br />".str_replace("%EMAIL%", $sendto, $_CORELANG['TXT_EMAIL_SEND_SUCCESSFULLY']);
							} else {
								$this->strErrMessage .= "<br />".str_replace("%EMAIL%", $sendto, $_CORELANG['TXT_EMAIL_NOT_SENT']);
							}
						} else {
							$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
						}
					}
				}
			}else {
				$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
			}
		}
	}

	/**
	* Deletes the selected user from the db
	*
	* @global    object     $objDatabase
	* @return    boolean    result
	*/
	function deactivateUser()
	{
	    global $objDatabase, $_CORELANG;

		$id = intval($_GET['userId']);

		if (($id>0) && ($id != $_SESSION['auth']['userid'])) {
		   $query="SELECT id FROM ".DBPREFIX."access_users WHERE is_admin=1 AND active=1 AND id!=".$id;
		   $objResult = $objDatabase->SelectLimit($query, 1);
		   if ($objResult !== false) {
		   	   //Is there any admin in the table?
		   	   if ($objResult->RecordCount() == 1) {
					if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET active=0 WHERE id=".$id) !== false) {
						$this->strOkMessage = $_CORELANG['TXT_USER_DEACTIVATED'];
					} else {
						$this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
					}
		   	   } else { //no more admin in table, user cannot be deleted
					$this->strErrMessage = $_CORELANG['TXT_USER_NOT_DELETED'];
		   	   }
		   } else {
		   	    $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
		   }
		} else {
			$this->strErrMessage = $_CORELANG['TXT_USER_NOT_DELETED'];
   	    }
	}



    function userOverview()
    {
		global $_CONFIG, $objDatabase, $_CORELANG, $objTemplate;

	    // initialize variables
	    $paging="";
		$status=1;
		$pos=0;
		$i=0;

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'user_overview', 'user_overview.html');
		$this->pageTitle = $_CORELANG['TXT_OVERVIEW'];

		if(isset($_REQUEST['useract']) AND $_REQUEST['useract']=="inactive") {
			$status=0;
			$useract = "&useract=inactive";
		}

		$objTemplate->setVariable(array(
            'TXT_USER_NAME'        		=> $_CORELANG['TXT_USERNAME'],
            'TXT_USER_LIST'        		=> $_CORELANG['TXT_USER_LIST'],
            'TXT_FIRST_NAME'       		=> $_CORELANG['TXT_FIRST_NAME'],
            'TXT_LAST_NAME'        		=> $_CORELANG['TXT_LAST_NAME'],
            'TXT_EMAIL'            		=> $_CORELANG['TXT_EMAIL'],
            'TXT_LANGUAGE'         		=> $_CORELANG['TXT_LANGUAGE'],
            'TXT_ADMINISTRATOR'    		=> $_CORELANG['TXT_ADMIN_STATUS'],
            'TXT_ACTION'           	 	=> $_CORELANG['TXT_ACTION'],
            'TXT_USER_LINK_INACTIVE' 	=> $_CORELANG['TXT_USER_LINK_INACTIVE'],
            'TXT_SEND_ACTIVATION_USER_EMAIL'	=> $_CORELANG['TXT_SEND_ACTIVATION_USER_EMAIL']
        ));

        $objTemplate->setGlobalVariable(array(
            'TXT_EDIT_USER_ACCOUNT'		=> $_CORELANG['TXT_EDIT_USER_ACCOUNT'],
            'TXT_ACTIVATE_USER_ACCOUNT'	=> $_CORELANG['TXT_ACTIVATE_USER_ACCOUNT'],
            'TXT_DEACTIVATE_USER_ACCOUNT'	=> $_CORELANG['TXT_DEACTIVATE_USER_ACCOUNT']
		));

	    /** start paging **/
		$query="SELECT u.id AS id,
		                   u.is_admin AS is_admin,
		                   u.username As username,
		                   u.email AS email,
		                   u.firstname AS firstname,
		                   u.lastname AS lastname,
		                   u.groups AS groups,
		                   l.name AS language
		              FROM ".DBPREFIX."access_users AS u,
		                   ".DBPREFIX."languages AS l
		             WHERE u.langId=l.id
		               AND u.active=".$status."
		         ORDER BY is_admin";

		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			$count = $objResult->RecordCount();
		}
		if(isset($_GET['pos'])){
		    $pos = intval($_GET['pos']);
		}
		if ($count>intval($_CONFIG['corePagingLimit'])){
			$paging = getPaging($count, $pos, "&cmd=user".$useract, "<b>".$_CORELANG['TXT_USER']."</b>", true);
		}
		/** end paging **/

		$objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
	            ($i % 2) ? $class  = 'row1' : $class  = 'row2';
				$arrGroups=explode(",", $objResult->fields["groups"]);
				$id=$objResult->fields['id'];
				if($objResult->fields['is_admin']==1) {
				   // change this with an icons
				   $is_admin= "<img src='images/icons/admin.gif' border='0' alt='Administrator' />";
				} else {
				   // change this with an icons
				   $is_admin= "<img src='images/icons/no_admin.gif' border='0' alt='User' />";
				}

				$objTemplate->setVariable(array(
					'USERS_ROWCLASS'		=> $class,
					'USERS_EDIT_ID'			=> $id,
					'USERS_ID'				=> $id,
					'USERS_USERNAME'		=> stripslashes($objResult->fields['username']),
					'USERS_EMAIL'			=> stripslashes($objResult->fields['email']),
					'USERS_FIRSTNAME'		=> empty($objResult->fields['firstname']) ? "&nbsp;" : stripslashes($objResult->fields['firstname']),
					'USERS_LASTNAME'		=> empty($objResult->fields['lastname']) ? "&nbsp;" : stripslashes($objResult->fields['lastname']),
					'USERS_LANGUAGE'		=> stripslashes($objResult->fields['language']),
					'USERS_ADMIN_STATUS'	=> $is_admin
				));
				if($status==1) {
				    $objTemplate->parse('user_active');
				} else {
	                $objTemplate->parse('user_inactive');
				}
				$objTemplate->parse('user_row');
				$i++;
				$objResult->MoveNext();
			}
		}
		$objTemplate->setVariable('USERS_PAGING', $paging);
    }
}
?>