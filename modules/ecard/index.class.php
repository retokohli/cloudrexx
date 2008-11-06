<?php

require_once ASCMS_LIBRARY_PATH.'/phpmailer/class.phpmailer.php';

class ecard {
	/**
		* Template object
		*
		* @access private
		* @var object
		*/
	var $_objTpl;
	var $pageContent;

	/**
		 * Constructor
		 * @global object $objTemplate
		 * @global array $_ARRAYLANG
		 */
	function __construct($pageContent) {
		$this->pageContent = $pageContent;
		$this->_objTpl = new HTML_Template_Sigma('.');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
	}

	/**
		* Get content page
		*
		* @access public
		*/
	function getPage() {
		$_GET['cmd'] = (isset($_GET['cmd'])) ? $_GET['cmd'] : '';

		switch ($_GET['cmd']) {
			case '':
				$this->showEcards();
				break;
			case 'preview':
				$this->preview();
				break;
			case 'send':
				$this->send();
				break;
			case 'show':
				$this->showEcard();
				break;
		}
		return $this->_objTpl->get();
	}

	function showEcards() {
		global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent);

		$i = 1;

		/*****************************/
		/* Initialize POST variables */
		/*****************************/
		$id = !empty($_POST['selectedEcard']) ? $_POST['selectedEcard'] : "";
		$message = !empty($_POST['ecardMessage']) ? strip_tags($_POST['ecardMessage']) : "";
		$receiverSalutaion = !empty($_POST['ecardReceiverSalutation']) ? $_POST['ecardReceiverSalutation'] : "";
		$senderName = !empty($_POST['ecardSenderName']) ? $_POST['ecardSenderName'] : "";
		$senderEmail = !empty($_POST['ecardSenderEmail']) ? $_POST['ecardSenderEmail'] : "";
		$receiverName = !empty($_POST['ecardReceiverName']) ? $_POST['ecardReceiverName'] : "";
		$receiverEmail = !empty($_POST['ecardReceiverEmail']) ? $_POST['ecardReceiverEmail'] : "";


		/************************************/
		/* Initialize POST DATA placeholder */
		/************************************/
		$this->_objTpl->setVariable(array(
			'POST_MESSAGE'   		=> $message,
			'POST_SENDERNAME'   	=> $senderName,
			'POST_SENDEREMAIL'		=> $senderEmail,
			'POST_RECEIVERNAME'		=> $receiverName,
			'POST_RECEIVEREMAIL'	=> $receiverEmail
		));


		/*******************************************************/
		/* Get max. number of characters and lines pro message */
		/*******************************************************/
		$query = "	SELECT *
						FROM
							".DBPREFIX."module_ecard_settings";

		$objResult = $objDatabase->Execute($query);

		while (!$objResult->EOF) {
			switch ($objResult->fields['setting_name']) {
				case "maxCharacters":
					$maxCharacters = $objResult->fields['setting_value'];
					break;
				case "maxLines":
					$maxLines = $objResult->fields['setting_value'];
					break;
			}
			$objResult->MoveNext();
		}

		$this->_objTpl->setVariable(array(
			'MAX_CHARACTERS'	=> $maxCharacters,
			'MAX_LINES'			=> $maxLines
		));


		/******************************************/
		/* JavaScript code for input verification */
		/******************************************/
		$JScode = '
					<script type="text/javascript">
					/* <![CDATA[ */
					
					var Selection = function(input){
						this.isTA = (this.input = input).nodeName.toLowerCase() == "textarea";
					};
					
					with({o: Selection.prototype}){
					    o.setCaret = function(start, end){
					        var o = this.input;
					        if(Selection.isStandard)
					            o.setSelectionRange(start, end);
					        else if(Selection.isSupported){
					            var t = this.input.createTextRange();
					            end -= start + o.value.slice(start + 1, end).split("\n").length - 1;
					            start -= o.value.slice(0, start).split("\n").length - 1;
					            t.move("character", start), t.moveEnd("character", end), t.select();
					        }
					    };
					    
					    o.getCaret = function(){
					        var o = this.input, d = document;
					        if(Selection.isStandard)
					            return {start: o.selectionStart, end: o.selectionEnd};
					        else if(Selection.isSupported){
					            var s = (this.input.focus(), d.selection.createRange()), r, start, end, value;
					            if(s.parentElement() != o)
					                return {start: 0, end: 0};
					            if(this.isTA ? (r = s.duplicate()).moveToElementText(o) : r = o.createTextRange(), !this.isTA)
					                return r.setEndPoint("EndToStart", s), {start: r.text.length, end: r.text.length + s.text.length};
					            for(var $ = "[###]"; (value = o.value).indexOf($) + 1; $ += $);
					            r.setEndPoint("StartToEnd", s), r.text = $ + r.text, end = o.value.indexOf($);
					            s.text = $, start = o.value.indexOf($);
					            if(d.execCommand && d.queryCommandSupported("Undo"))
					                for(r = 3; --r; d.execCommand("Undo"));
					            return o.value = value, this.setCaret(start, end), {start: start, end: end};
					        }
					        return {start: 0, end: 0};
					    };
					    
					    o.getText = function(){
					        var o = this.getCaret();
					        return this.input.value.slice(o.start, o.end);
					    };
					    
					    o.setText = function(text){
					        var o = this.getCaret(), i = this.input, s = i.value;
					        i.value = s.slice(0, o.start) + text + s.slice(o.end);
					        this.setCaret(o.start += text.length, o.start);
					    };
					    
					    new function(){
					        var d = document, o = d.createElement("input"), s = Selection;
					        s.isStandard = "selectionStart" in o;
					        s.isSupported = s.isStandard || (o = d.selection) && !!o.createRange();
					    };
					}
					
					var textarea = document.getElementById("ecardMessage");
					
					var getLineNr = function(){
					    var s = selection.getCaret();
					    len = 0;
					    if(textarea.value.substr(0, s.start).match(/\n/g)){
					        len = textarea.value.substr(0, s.start).match(/\n/g).length
					    }
					    return len
					}
					
					var maxChars = ' . $maxCharacters . ';
					var maxLines = ' . $maxLines . ';
										
					var selection = new Selection(document.getElementById("ecardMessage"));
					
					function checkAllFields() {
						value = document.getElementById("ecardMessage").value;
					    lines = value.split("\n");
						newValue = "";
						regex = new RegExp("^([^\n]{0,"+maxChars+"})?[^\n]*(\n?)\n*", "");
					
						for (i = 1; i <= maxLines && value != ""; ++i) {
							value = value.replace(regex, "");
							if (RegExp.$1) {
								newValue += RegExp.$1+RegExp.$2;
								RegExp.$1 = "";
								//USED CHAR COUNTER
								currentChars = lines[getLineNr()].length;
								leftChars = (maxChars - currentChars) > -1 ? (maxChars - currentChars) : 0;
								document.getElementById("charCounter").value = leftChars;
					
								//USED LINE COUNTER
								currentLines = i;
								leftLines = maxLines - currentLines;
								document.getElementById("lineCounter").value = leftLines;
							}
						}
						document.getElementById("ecardMessage").value = newValue;
					}

					
					
					function checkInput() {
						var ecardCount = 0;
						var wrongFieldsArray = new Array();
						var fieldsArray = new Array("motiveFieldset", "fieldDescription_salutation", "ecardMessage", "ecardSenderName", "ecardReceiverName", "ecardSenderName", "ecardSenderEmail", "ecardReceiverEmail");
						
						for(var i = 0; i < document.getElementsByName("selectedEcard").length; i++) {
							if(document.getElementsByName("selectedEcard")[i].checked == true) {
								ecardCount++
							}
						}
						
						if (ecardCount == 0) {wrongFieldsArray.push("motiveFieldset");}
						if ((document.getElementsByName("ecardReceiverSalutation")[0].checked == false) && (document.getElementsByName("ecardReceiverSalutation")[1].checked == false)) {wrongFieldsArray.push("fieldDescription_salutation");}
						if(document.getElementsByName("ecardMessage")[0].value    == "") {wrongFieldsArray.push("ecardMessage");}
						if(document.getElementsByName("ecardSenderName")[0].value    == "") {wrongFieldsArray.push("ecardSenderName");}
						if(document.getElementsByName("ecardReceiverName")[0].value    == "") {wrongFieldsArray.push("ecardReceiverName");}
						if(document.getElementsByName("ecardSenderName")[0].value    == "") {wrongFieldsArray.push("ecardSenderName");}
						if(checkEmail(document.getElementsByName("ecardSenderEmail")[0].value) == false) {wrongFieldsArray.push("ecardSenderEmail");} 
						if(checkEmail(document.getElementsByName("ecardReceiverEmail")[0].value) == false) {wrongFieldsArray.push("ecardReceiverEmail");}
						
						for (var i=0; i < fieldsArray.length; i++) {
							if (wrongFieldsArray.toString().indexOf(fieldsArray[i]) == -1) {
								if (fieldsArray[i] == "fieldDescription_salutation") {
									document.getElementById(fieldsArray[i]).style.color = "";
								} else {
									document.getElementById(fieldsArray[i]).style.border = "";
								}
							}
						}
						
						if (wrongFieldsArray.length == 0) {
							return true;
						} else {
							for (var i=0; i < wrongFieldsArray.length; i++) {
								if (wrongFieldsArray[i] == "fieldDescription_salutation") {
									document.getElementById(wrongFieldsArray[i]).style.color= "red";
								} else {
									document.getElementById(wrongFieldsArray[i]).style.border = "1px solid red";
								}
							}
							alert("' . $_ARRAYLANG['TXT_FIELD_INPUT_INCORRECT'] . '");
							return false;
						}
					}
					
					function checkEmail(s) {
						var a = false;
						var res = false;
						if(typeof(RegExp) == "function") {
							var b = new RegExp("abc");
							if(b.test("abc") == true){a = true;}
						}
						
						if(a == true) {
							reg = new RegExp("^([a-zA-Z0-9\\-\\.\\_]+)"+
							                 "(\\@)([a-zA-Z0-9\\-\\.]+)"+
							                 "(\\.)([a-zA-Z]{2,4})$");
							res = (reg.test(s));
						} else {
							res = (s.search("@") >= 1 &&
							       s.lastIndexOf('.') > s.search("@") &&
							       s.lastIndexOf('.') >= s.length-5)
						}
						return(res);
					}
				
				/* ]]> */
				</script>';


		/**************************/
		/* Select motives from DB */
		/**************************/
		$query = "	SELECT *
						FROM
							".DBPREFIX."module_ecard_settings
						WHERE
							setting_name
						LIKE
							'motive_%'";
		$i = 0;
		$ii = 1;

		$objResult = $objDatabase->Execute($query);


		/*******************************/
		/* Initialize DATA placeholder */
		/*******************************/
		while (!$objResult->EOF) {
			$motive = $objResult->fields['setting_value'];
			$motive = str_replace(ASCMS_PATH_OFFSET, "", $motive);
			$motiveID = $objResult->fields['id'];
			$motiveFilename = strrchr($motive, "/");

			if ($motive != "") {
				$this->_objTpl->setVariable(array(
					'JS_CODE'   			=> $JScode,
					'MOTIVE_OPTIMIZED_PATH'	=> ASCMS_ECARD_OPTIMIZED_WEB_PATH . $motiveFilename,
					'MOTIVE_ID'   			=> $motiveID,
					'THUMBNAIL_PATH'    	=> ASCMS_ECARD_THUMBNAIL_WEB_PATH . $motiveFilename,
					'CSSNUMBER'    			=> $ii
				));
				$this->_objTpl->parse('motiveBlock');

				if ($i == 2) {
					$ii = 1;
				} elseif ($i == 5) {
					$ii = 1;
				} else {
					$ii++;
				}

				if ($i%3 == 0) {
					$this->_objTpl->parse('motiveRow');
				}
			}
			$i++;
			$objResult->MoveNext();
		}
	}

	function preview() {
		global $objDatabase, $_ARRAYLANG;

		$this->_objTpl->setTemplate($this->pageContent);

		/*****************************/
		/* Initialize POST variables */
		/*****************************/
		$id = $_POST['selectedEcard'];
		$message = nl2br($_POST['ecardMessage']);
		$receiverSalutaion = $_POST['ecardReceiverSalutation'];
		$senderName = $_POST['ecardSenderName'];
		$senderEmail = $_POST['ecardSenderEmail'];
		$receiverName = $_POST['ecardReceiverName'];
		$receiverEmail = $_POST['ecardReceiverEmail'];


		/********************************/
		/* Get path from choosen motive */
		/********************************/
		$query = "	SELECT
							setting_value
						FROM
							".DBPREFIX."module_ecard_settings
						WHERE
							id = '" . $id . "'
						LIMIT 1";

		$objResult = $objDatabase->Execute($query);
		$selectedMotive = str_replace("/", "", strrchr($objResult->fields['setting_value'], "/"));


		/*******************************/
		/* Initialize DATA placeholder */
		/*******************************/
		$this->_objTpl->setVariable(array(
			'ECARD_DATA'   				=> '<strong>' . $senderName . '</strong> (<a href="mailto:' . $senderEmail . '">' . $senderEmail . '</a>) ' . $_ARRAYLANG['TXT_HAS_SEND_YOU_ECARD'],
			'MOTIVE'   					=> '<img src="' . ASCMS_ECARD_OPTIMIZED_WEB_PATH . $selectedMotive  . '" alt="' . $selectedMotive . '" />',
			'MOTIVE_ID'   				=> $id,
			'ECARD_MESSAGE'   			=> $message,
			'ECARD_SENDER_NAME'   		=> $senderName,
			'ECARD_SENDER_EMAIL'   		=> $senderEmail,
			'ECARD_RECEIVER_NAME'   	=> $receiverName,
			'ECARD_RECEIVER_EMAIL'   	=> $receiverEmail,
			'ECARD_RECEIVER_SALUTATION'	=> $receiverSalutaion
		));
	}

	function send() {
		global $objDatabase, $_ARRAYLANG, $_CONFIG;
		$this->_objTpl->setTemplate($this->pageContent);

		/************************/
		/* Initialize variables */
		/************************/
		$code = substr(md5(rand(0,99999)), 1, 10);
		$url = 'http://www.' . $_CONFIG['domainUrl'] . '/index.php?section=ecard&cmd=show&code=' . $code;


		/*****************************/
		/* Initialize POST variables */
		/*****************************/
		$id = $_POST['selectedEcard'];
		$message = $_POST['ecardMessage'];
		$receiverSalutation = $_POST['ecardReceiverSalutation'];
		$senderName = $_POST['ecardSenderName'];
		$senderEmail = $_POST['ecardSenderEmail'];
		$receiverName = $_POST['ecardReceiverName'];
		$receiverEmail = $_POST['ecardReceiverEmail'];


		$query = "	SELECT *
						FROM 
							".DBPREFIX."module_ecard_settings";

		$objResult = $objDatabase->Execute($query);

		while (!$objResult->EOF) {
			switch ($objResult->fields['setting_name']) {
				case 'validdays':
					$validdays = $objResult->fields['setting_value'];
					break;
				case 'greetings':
					$greetings = $objResult->fields['setting_value'];
					break;
				case 'subject':
					$subject = $objResult->fields['setting_value'];
					break;
				case 'emailText':
					$emailText = strip_tags($objResult->fields['setting_value']);
					break;
			}
			$objResult->MoveNext();
		}

		$timeToLife = $validdays * 86400;


		/**********************************************************************/
		/* Replace placeholders with used in notification mail with user data */
		/**********************************************************************/
		$emailText = str_replace('[[ECARD_RECEIVER_SALUTATION]]', $receiverSalutation, $emailText);
		$emailText = str_replace('[[ECARD_RECEIVER_NAME]]', $receiverName, $emailText);
		$emailText = str_replace('[[ECARD_RECEIVER_EMAIL]]', $receiverEmail, $emailText);
		$emailText = str_replace('[[ECARD_SENDER_NAME]]', $senderName, $emailText);
		$emailText = str_replace('[[ECARD_SENDER_EMAIL]]', $senderEmail, $emailText);
		$emailText = str_replace('[[ECARD_VALID_DAYS]]', $validdays, $emailText);
		$emailText = str_replace('[[ECARD_URL]]', $url, $emailText);

		$body = $emailText;


		/**********************/
		/* Insert ecard to DB */
		/**********************/
		$query = "	INSERT INTO
		    					`".DBPREFIX."module_ecard_ecards` 
		    				VALUES (
		    					'" . mktime() . "',
		    					'" . $timeToLife . "',
		    					'" . $code . "',
		    					'" . $receiverSalutation . "',
		    					'" . $senderName . "',
		    					'" . $senderEmail . "',
		    					'" . $receiverName . "',
		    					'" . $receiverEmail . "',
		    					'" . $message . "');";

		if ($objDatabase->Execute($query)) {
			$query = "SELECT setting_value FROM ".DBPREFIX."module_ecard_settings WHERE id = '" . $id . "'";
			$objResult = $objDatabase->Execute($query);


			/**************************************************/
			/* Copy motive to new file with $code as filename */
			/**************************************************/
			$fileExtension = substr($objResult->fields['setting_value'], -4);
			$fileNameWithExtension = str_replace("/", "", strrchr($objResult->fields['setting_value'], "/"));

			if (copy(ASCMS_ECARD_OPTIMIZED_PATH . $fileNameWithExtension, ASCMS_ECARD_SEND_ECARDS_PATH . $code . $fileExtension)) {


				/*************************/
				/* Check e-mail settings */
				/*************************/
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


				/********************************************/
				/* Send notification mail to ecard-receiver */
				/********************************************/
				$objMail = new phpmailer();
				$objMail->CharSet = CONTREXX_CHARSET;
				$objMail->From = $senderEmail;
				$objMail->FromName = $senderName;
				$objMail->AddReplyTo($senderEmail);
				$objMail->Subject = $subject;
				$objMail->IsHTML(false);
				$objMail->Body = $body;
				$objMail->AddAddress($receiverEmail);

				if ($objMail->Send()) {
					$this->_objTpl->setVariable(array(
						'STATUS_MESSAGE'   => $_ARRAYLANG['TXT_ECARD_HAS_BEEN_SENT']
					));
				} else {
					$this->_objTpl->setVariable(array(
						'STATUS_MESSAGE'   => $_ARRAYLANG['TXT_ECARD_MAIL_SENDING_ERROR']
					));
				}
			}
		} else {
			$this->_objTpl->setVariable(array(
				'STATUS_MESSAGE'   => $_ARRAYLANG['TXT_ECARD_SENDING_ERROR']
			));
		}
	}

	function showEcard() {
		global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent);

		/************************/
		/* Initialize variables */
		/************************/
		$code = $_GET['code'];


		/********************/
		/* Get data from DB */
		/********************/
		$query = "	SELECT *
		                FROM
		                	".DBPREFIX."module_ecard_ecards
		                WHERE
		                	code = '" . $code . "'
		                LIMIT 1";

		$objResult = $objDatabase->Execute($query);


		/*********************************************/
		/* If entered code does match a record in db */
		/*********************************************/
		if (!$objResult->EOF) {
			$message = $objResult->fields['message'];
			$senderName = $objResult->fields['senderName'];
			$senderEmail = $objResult->fields['senderEmail'];
			$receiverName = $objResult->fields['receiverName'];
			$receiverEmail = $objResult->fields['receiverEmail'];
			$receiversalutation = $objResult->fields['salutation'];


			/****************************/
			/* Get right file extension */
			/****************************/
			$globArray = glob(ASCMS_ECARD_SEND_ECARDS_PATH . $code . ".*");
			$fileextension = substr($globArray[0], -4);

			$selectedMotive = $code . $fileextension;


			/*******************************/
			/* Initialize DATA placeholder */
			/*******************************/
			$this->_objTpl->setVariable(array(
				'ECARD_DATA'   				=> '<strong>' . $senderName . '</strong> (<a href="mailto:' . $senderEmail . '">' . $senderEmail . '</a>) ' . $_ARRAYLANG['TXT_HAS_SEND_YOU_ECARD'],
				'MOTIVE'   					=> '<img src="' . ASCMS_ECARD_SEND_ECARDS_WEB_PATH . $selectedMotive  . '" alt="' . $selectedMotive . '" />',
				'ECARD_FROM'   				=> 'E-Card von ' . $senderName,
				'ECARD_MESSAGE'   			=> $message,
				'ECARD_SENDER_NAME'   		=> $senderName,
				'ECARD_SENDER_EMAIL'   		=> $senderEmail,
				'ECARD_RECEIVER_SALUTATION'	=> $receiversalutation,
				'ECARD_RECEIVER_NAME'		=> $receiverName,
				'ECARD_RECEIVER_EMAIL'		=> $receiverEmail
			));


			/******************************/
			/* else display error message */
			/******************************/
		} else {
			$this->_objTpl->setVariable(array(
				'ECARD_MESSAGE'	=> $_ARRAYLANG['TXT_ECARD_WRONG_CODE'],
				'ECARD_FROM'   	=> $_ARRAYLANG['TXT_ECARD_CAN_NOT_BE_DISPLAYED']
			));
		}
	}
}
?>