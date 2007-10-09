<?PHP
/**
 * Guestbook
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_guestbook
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/guestbook/Lib.class.php';

/**
 * Guestbook
 *
 * Guestbook frontend
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_guestbook
 */
class Guestbook extends GuestbookLibrary
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
    function Guestbook($pageContent)
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
	    global $_LANGID;
	    $this->pageContent = $pageContent;
	    $this->langId = $_LANGID;

	    $this->_objTpl = &new HTML_Template_Sigma('.');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

		// get the guestbook settings
    	$this->getSettings();
	}

	/**
	 * Gets the page
	 */
	function getPage()
	{
    	if(!isset($_GET['cmd'])){
    		$_GET['cmd'] = '';
    	}

    	switch($_GET['cmd']){
    		case 'post':
		    	$this->_newEntry();
		    	break;
		    default:
		        $this->_showList();
		        break;
    	}
    	return $this->_objTpl->get();
    }

	/**
	 * Gets the guestbook status
	 *
	 * @global  array    $_CONFIG
	 * @global  array    $_ARRAYLANG
	 * @global  object   $objDatabase
	 * @access private
	 */

	function _showList()
	{
		global $objDatabase, $_CONFIG, $_ARRAYLANG;

		$this->_objTpl->setTemplate($this->pageContent, true, true);

		// initialize variables
	    $i = 1;
	    $paging = "";
		$pos = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

		/** start paging **/
		$query = "SELECT *
			FROM ".DBPREFIX."module_guestbook
			WHERE "
			.($this->arrSettings['guestbook_only_lang_entries'] ? "lang_id='$this->langId' AND " : '')
			."status = 1
			ORDER BY id DESC";
		$objResult = $objDatabase->Execute($query);
		$count = $objResult->RecordCount();
		$paging = getPaging($count, $pos, "&amp;section=guestbook", "<b>".$_ARRAYLANG['TXT_GUESTBOOK_ENTRIES']."</b>", false);
		/** end paging **/

		$this->_objTpl->setVariable("GUESTBOOK_PAGING", $paging);
        $this->_objTpl->setVariable("GUESTBOOK_TOTAL_ENTRIES", $count);

		$query = "SELECT *
			FROM ".DBPREFIX."module_guestbook
			WHERE "
			.($this->arrSettings['guestbook_only_lang_entries'] ? "lang_id='$this->langId' AND " : '')
			."status = 1
			ORDER BY id DESC";
		$objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);

		while (!$objResult->EOF) {
			$class = ($i % 2) ? "row1" : "row2";
			$gender = ($objResult->fields["gender"]=="M") ? $_ARRAYLANG['guestbookGenderMale'] : $_ARRAYLANG['guestbookGenderFemale']; // N/A

			if ($objResult->fields['url']!=""){
				$this->_objTpl->setVariable("GUESTBOOK_URL", "<a href='".$objResult->fields['url']."'><img alt='".$objResult->fields['url']."' src='".ASCMS_MODULE_IMAGE_WEB_PATH."/guestbook/www.gif' align='baseline' border='0' /></a>");
			}
			if ($objResult->fields['email']!=""){
				if ($this->arrSettings['guestbook_replace_at']) {
					$email = $this->changeMail($objResult->fields['email']);
				} else {
					$email = $objResult->fields['email'];
				}

				$this->_objTpl->setVariable("GUESTBOOK_EMAIL", "<a href='mailto:".$email."'><img alt='".$objResult->fields["email"]."' src='".ASCMS_MODULE_IMAGE_WEB_PATH."/guestbook/email.gif' align='baseline' border='0' /></a>");

			}

			$this->_objTpl->setVariable(array(
					   'GUESTBOOK_ROWCLASS'   => $class,
					   'GUESTBOOK_NICK'	      => htmlentities($objResult->fields["nickname"], ENT_QUOTES, CONTREXX_CHARSET),
					   'GUESTBOOK_GENDER'	  => $gender,
					   'GUESTBOOK_LOCATION'	  => htmlentities($objResult->fields["location"], ENT_QUOTES, CONTREXX_CHARSET),
					   'GUESTBOOK_DATE'		  => $objResult->fields["datetime"],
					   'GUESTBOOK_COMMENT'	  => nl2br($objResult->fields["comment"]),
					   'GUESTBOOK_ID'		  => $objResult->fields["id"],
					   'GUESTBOOK_IP'		  => $objResult->fields["ip"]
			));
			$this->_objTpl->parse('guestbook_row');
			$i++;
			$objResult->MoveNext();
		}
		$this->_objTpl->setVariable("GUESTBOOK_STATUS", $this->statusMessage);
	}


	/**
	 * New entry
	 *
	 * Decides what to do, preview, safe or output the errors
	 */
	function _newEntry()
	{
		if (isset($_POST['save'])) {
			if ($this->checkInput()) {
				$this->saveEntry();
			} else {
				$this->_showForm();
			}
		} else {
			$this->_showForm();
		}
	}

	/**
	* shows the submit form
	*
	* @access private
	*/

	function _showForm()
	{
		global $_ARRAYLANG;

		$this->_objTpl->setTemplate($this->pageContent);

		$checked = "checked=\"checked\"";

		if (!empty($this->error)) {
			$errors = "<span style=\"color: red\">";
			foreach ($this->error as $error) {
				$errors .= $error . "<br />";
			}
			$errors .= "</span>";

			if ($_POST['malefemale'] == "F") {
				$female_checked = $checked;
				$male_checked = "";
			} else {
				$female_checked = "";
				$male_checked = $checked;
			}

			$this->_objTpl->setVariable(array(
				"NICKNAME"			=> htmlentities($_POST['nickname'], ENT_QUOTES, CONTREXX_CHARSET),
				"COMMENT"			=> $_POST['comment'],
				"FEMALE_CHECKED"	=> $female_checked,
				"MALE_CHECKED"		=> $male_checked,
				"LOCATION"			=> htmlentities($_POST['location'], ENT_QUOTES, CONTREXX_CHARSET),
				"HOMEPAGE"			=> htmlentities($_POST['url'], ENT_QUOTES, CONTREXX_CHARSET),
				"EMAIL"				=> htmlentities($_POST['email'], ENT_QUOTES, CONTREXX_CHARSET)
			));
		}

		require_once ASCMS_LIBRARY_PATH . "/spamprotection/captcha.class.php";
		$captcha = new Captcha();

		$offset = $captcha->getOffset();
		$alt = $captcha->getAlt();
		$url = $captcha->getUrl();

		$this->_objTpl->setVariable(array(
			"ERROR"					=> $errors,
			"TXT_CAPTCHA"			=> $_ARRAYLANG['txt_captcha'],
			"CAPTCHA_OFFSET"		=> $offset,
			"IMAGE_URL"				=> $url,
			"IMAGE_ALT"				=> $alt,
			"FEMALE_CHECKED"		=> $checked
		));
	}

	/**
	 * Saves an entry
	 */
	function saveEntry()
	{
		global $objDatabase, $_ARRAYLANG;

		$objValidator = new FWValidator();

		$nick 	= $_POST['nickname'];
		$gender = $_POST['malefemale'];
		$mail 	= isset($_POST['email']) ? $_POST['email'] : '';
		$comment = $this->addHyperlinking($_POST['comment']);
		$location = $_POST['location'];

		if (strlen($_POST['url']) > 7) {
			$url = $_POST['url'];

			if (!preg_match("%^http://%", $url)) {
				$url = "http://" . $url;
			}
		}

		$status = $this->arrSettings['guestbook_activate_submitted_entries'];

		$query = "INSERT INTO ".DBPREFIX."module_guestbook
	                    (status,
	                     nickname,
					     gender,
					     url,
					     datetime,
					     email,
					     comment,
					     ip,
	                     location,
	                     lang_id)
	             VALUES ($status,
						'".addslashes($nick)."',
						'".addslashes($gender)."',
						'".addslashes($url)."',
						NOW(),
						'".addslashes($mail)."',
						'".addslashes($comment)."',
						'".addslashes($_SERVER['REMOTE_ADDR'])."',
						'".addslashes($location)."',
						".$this->langId.")";
		$objDatabase->Execute($query);

		if ($this->arrSettings['guestbook_send_notification_email']==1) {
	    	$this->sendNotificationEmail($nick, $comment);
	    }
	    $this->statusMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL']."<br />";
	    if ($this->arrSettings['guestbook_activate_submitted_entries'] == 0) {
	    	$this->statusMessage .= '<b>'.$_ARRAYLANG['TXT_DATA_RECORD_STORED_ACTIVATE'].'</b>';
	    }

	    header("Location: index.php?section=guestbook");
	    exit;
	}

	/**
	 * checks input
	 */
	function checkInput()
	{
		global $_ARRAYLANG;

		require_once ASCMS_LIBRARY_PATH . "/spamprotection/captcha.class.php";

		$captcha = new Captcha();
		$objValidator = new FWValidator();

		$_POST['nickname'] = strip_tags(contrexx_stripslashes($_POST['nickname']));
		$_POST['comment'] = htmlentities(strip_tags(contrexx_stripslashes($_POST['comment'])), ENT_QUOTES, CONTREXX_CHARSET);
		$_POST['location'] = strip_tags(contrexx_stripslashes($_POST['location']));
		$_POST['email'] = strip_tags(contrexx_stripslashes($_POST['email']));
		$_POST['url'] = strip_tags(contrexx_stripslashes($_POST['url']));

		if (!$captcha->compare($_POST['captcha'], $_POST['offset'])) {
			$this->error[] = $_ARRAYLANG['TXT_CAPTCHA_ERROR'];
		}

		if (empty($_POST['nickname'])) {
			$this->makeError($_ARRAYLANG['TXT_NAME']);
		}

		if (empty($_POST['comment'])) {
			$this->makeError($_ARRAYLANG['TXT_COMMENT']);
		}

		if (empty($_POST['malefemale'])) {
			$this->makeError($_ARRAYLANG['TXT_SEX']);
		}

		if (empty($_POST['location'])) {
			$this->makeError($_ARRAYLANG['TXT_LOCATION']);
		}

		if (!$objValidator->isEmail($_POST['email'])) {
			$this->makeError($_ARRAYLANG['TXT_EMAIL']);
		}

		if (empty($this->error)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Makes an error
	 */
	function makeError($term)
	{
		global $_ARRAYLANG;

		$this->error[] = $term . " " . $_ARRAYLANG['TXT_IS_INVALID'];
	}

    /**
    * @return void
    * @desc Sends a notification email to the administrator
    */
	function sendNotificationEmail($nick,$comment)
	{
		global $_ARRAYLANG, $_CONFIG;

	    $message = $_ARRAYLANG['TXT_CHECK_GUESTBOOK_ENTRY']."\n\n";
	    $message .= $_ARRAYLANG['TXT_ENTRY_READS']."\n".$nick."\n".$comment;
	    $mailto = $_CONFIG['coreAdminEmail'];
	    $subject = $_ARRAYLANG['TXT_NEW_GUESTBOOK_ENTRY']." ".$_SERVER['HTTP_HOST'];

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
			$objMail->From = $mailto;
			$objMail->AddReplyTo($mailto);
			$objMail->Subject = $subject;
			$objMail->IsHTML(false);
			$objMail->Body = $message;
			$objMail->AddAddress($mailto);
			if ($objMail->Send()) {
				return true;
			}
		}

		return false;
	}
}
?>
