<?php
/**
 * Calendar Class Mail Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation <info@comvation.com>
 * @version     $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_calendar
 */


/**
 * CalendarMailManager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation <info@comvation.com>
 * @version     $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_calendar
 */
class CalendarMailManager extends CalendarLibrary {
    /**
     * Mail list array
     * 
     * @access public
     * @var array 
     */
    public $mailList = array();
    
    /**
     * Constructor
     */
    function __construct()
    {
        parent::getFrontendLanguages();
    }
    
    /**
     * Return's the mailing list
     * 
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     * @global integer $_LANGID
     * @return array Return's the mailing list
     */
    function getMailList() 
    {
        global $objDatabase,$_ARRAYLANG,$_LANGID;   
        
        $query = "SELECT id
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                ORDER BY action_id ASC, title ASC";
        
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $objMail = new CalendarMail(intval($objResult->fields['id']));
                $this->mailList[] = $objMail;
                $objResult->MoveNext();
            }
        }
    }
    
    /**
     * Set the mailing list placeholders to the template
     * 
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     * @param object $objTpl
     */
    function showMailList($objTpl) 
    {
        global $objDatabase, $_ARRAYLANG;
        
        $i=0;
        foreach ($this->mailList as $key => $objMail) {
            foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                if($arrLang['id'] == $objMail->lang_id) {
                    $langName = $arrLang['name'];
                }
            }
            
            $objResult = $objDatabase->Execute("SELECT `name` FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_mail_action WHERE id='".$objMail->action_id."' LIMIT 1 ");
            if ($objResult !== false) {
                $action = $_ARRAYLANG['TXT_CALENDAR_MAIL_ACTION_'.strtoupper($objResult->fields['name'])];
            }
            
            if($objMail->is_default == 1) {
                $isDefault = 'checked="checked"';
            } else {
                $isDefault = '';
            }
            
            $objTpl->setVariable(array(
                $this->moduleLangVar.'_TEMPLATE_ROW'       => $i%2==0 ? 'row1' : 'row2',
                $this->moduleLangVar.'_TEMPLATE_ID'              => $objMail->id,
                $this->moduleLangVar.'_TEMPLATE_STATUS'          => $objMail->status==0 ? 'red' : 'green',
                $this->moduleLangVar.'_TEMPLATE_LANG'            => $langName,
                $this->moduleLangVar.'_TEMPLATE_TITLE'           => $objMail->title,
                $this->moduleLangVar.'_TEMPLATE_ACTION'          => $action,
                $this->moduleLangVar.'_TEMPLATE_DEFAULT'         => $isDefault,
                $this->moduleLangVar.'_TEMPLATE_DEFAULT_NAME'    => "isDefault_".$objMail->action_id,
            ));
            
            $i++;
            $objTpl->parse('templateList');
        }
        
        if(count($this->mailList) == 0) {
            $objTpl->hideBlock('templateList');
        
            $objTpl->setVariable(array(
                'TXT_CALENDAR_NO_TEMPLATES_FOUND' => $_ARRAYLANG['TXT_CALENDAR_NO_TEMPLATES_FOUND'],
            ));
            
            $objTpl->parse('emptyTemplateList');
        }
    }
    
    /**
     * Sets the mail placeholders to the template
     * 
     * @global object $objInit
     * @global array $_ARRAYLANG
     * @param object $objTpl
     * @param integer $mailId
     */
    function showMail($objTpl, $mailId) 
    {
        global $objInit, $_ARRAYLANG;
        
        $objMail = new CalendarMail(intval($mailId));
        $this->mailList[$mailId] = $objMail;
        
        $objTpl->setVariable(array(
            $this->moduleLangVar.'_TEMPLATE_ID'              => $objMail->id,
            $this->moduleLangVar.'_TEMPLATE_ACTION'          => $objMail->action_id,
            $this->moduleLangVar.'_TEMPLATE_LANG'            => $objMail->lang_id,
            $this->moduleLangVar.'_TEMPLATE_RECIPIENTS'      => $objMail->recipients,
            $this->moduleLangVar.'_TEMPLATE_TITLE'           => $objMail->title,
            $this->moduleLangVar.'_TEMPLATE_CONTENT_TEXT'    => stripslashes($objMail->content_text),
            $this->moduleLangVar.'_TEMPLATE_CONTENT_HTML'    => $objMail->content_html,
        ));
    }
    
    /**
     * Initialize the mail functionality to the recipient
     * 
     * @global object $objDatabase
     * @global array $_ARRAYLANG
     * @global integer $_LANGID
     * @global array $_CONFIG
     * @param integer $eventId
     * @param integer $actionId
     * @param integer $regId
     * @param string $mailTemplate
     */
    function sendMail($eventId, $actionId, $regId=null, $mailTemplate = null)
    {  
        global $objDatabase,$_ARRAYLANG,$_LANGID, $_CONFIG ;
        
        $this->mailList = array();  

        if($mailTemplate) {
            $whereId = " AND mail.id = " . intval($mailTemplate);
        } else {
            $whereId = "";
        }

        $query = "SELECT mail.id, action.default_recipient, mail.lang_id, mail.is_default, mail.recipients    
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_mail AS mail, 
                         ".DBPREFIX."module_".$this->moduleTablePrefix."_mail_action AS action
                   WHERE mail.action_id='".intval($actionId)."'  
                     AND status='1'    
                     AND action.id = mail.action_id$whereId
                ORDER BY is_default DESC";   
                
        $objResult = $objDatabase->Execute($query);  
        
        if ($objResult !== false) {
            while (!$objResult->EOF) {       
                $objMail = new CalendarMail(intval($objResult->fields['id']));  
                if($objResult->fields['is_default'] == 1) {   
                    $supRecipients = explode(",", $objResult->fields['recipients']);     
                    foreach ($supRecipients as $key => $mail) {
                        $this->mailList[0]['recipients'][$mail] = intval($objResult->fields['lang_id']);    
                    }
                    $this->mailList[0]['default_recipient'][$objResult->fields['default_recipient']] = intval($objResult->fields['lang_id']);                                                                                    
                    $this->mailList[0]['mail'] = $objMail;    
                }
                 
                $supRecipients = explode(",", $objResult->fields['recipients']);            
                foreach ($supRecipients as $key => $mail) {
                    $this->mailList[intval($objResult->fields['lang_id'])]['recipients'][$mail] = intval($objResult->fields['lang_id']);    
                }                                                                 
                $this->mailList[intval($objResult->fields['lang_id'])]['default_recipient'][$objResult->fields['default_recipient']] = intval($objResult->fields['lang_id']);     
                $this->mailList[intval($objResult->fields['lang_id'])]['mail'] = $objMail;    
                
                $objResult->MoveNext();
            }
        }
        
        if(!empty($this->mailList)) {                            
            
            $objFWUser = FWUser::getFWUserObject();     
            if(!$this->objUser = $objFWUser->objUser->getUser($id = intval($objRSEntryUserId->fields['added_by']))) {
                $this->objUser = false;
            }
            
            $objEvent = new CalendarEvent($eventId);
            
            if(!empty($regId)) {   
                $objRegistration = new CalendarRegistration($objEvent->registrationForm, $regId);    
                if(!empty($objRegistration->userId)) {  
                    $objFWUser = FWUser::getFWUserObject();
                    if($objUser = $objFWUser->objUser->getUser($id = intval($objRegistration->userId))) {   
                        $userNick = $objUser->getUsername();
                        $userFirstname = $objUser->getProfileAttribute('firstname');
                        $userLastname = $objUser->getProfileAttribute('lastname');
                        $userMail = $objUser->getEmail();
                        $userLang = $objUser->getFrontendLanguage();
                    } 
                }
                
                $registrationDataHtml = '<table align="top" border="0" cellpadding="3" cellspacing="0">';
                foreach($objRegistration->fields as $id => $arrField){
                    if($arrField['type'] == 'select' || $arrField['type'] == 'radio' || $arrField['type'] == 'checkbox') {  
                        $options = explode(",", $arrField['default']);  
                        $values = explode(",", $arrField['value']);
                        $output = array();
                            
                        foreach ($values as $key => $value) {  
                            $arrValue = explode('[[', $value);    
                            $value = $arrValue[0];
                            $input = str_replace(']]','', $arrValue[1]); 
                            
                            if(!empty($input)) {
                                $arrOption = explode('[[', $options[$value-1]);      
                                $output[] = $arrOption[0].": ".$input; 
                            } else {   
                                if($options[0] == '' && $value == 1) {
                                    $options[$value-1] = '1';
                                }
                                $output[] = $options[$value-1];        
                            }        
                        } 
                        
                        $value = join(", ", $output);       
                    } else {
                        $value = $arrField['value'];
                    }
                    $registrationDataText .= html_entity_decode($arrField['name']).":\t".html_entity_decode($value)."\n";
                    $registrationDataHtml .= '<tr><td><b>'.$arrField['name'].":</b></td><td>". $value."</td></tr>";
                }
                $registrationDataHtml .= '</table>'; 
            } 
                                                              
            $recipients = array();     
            
            if (array_key_exists('admin', $this->mailList[0]['default_recipient'])) {          
                $recipients[$_CONFIG['coreAdminEmail']] = $_LANGID;  
            } else if (array_key_exists('author', $this->mailList[$_LANGID]['default_recipient'])) {                      
                if(!empty($regId)) {   
                    foreach($objRegistration->fields as $id => $arrField){
                        if($arrField['type'] == 'mail' && !empty($arrField['value'])) {        
                            $recipients[$arrField['value']] = $_LANGID;
                        }   
                    } 
                } else {
                    $recipients[$userMail] = $userLang;
                }
            }        
                    
            switch ($actionId) {
                case 1:
                     $invitedMails = explode(",", $objEvent->invitedMails);
                     foreach ($invitedMails as $key => $mail) {
                        $recipients[$mail] = $_LANGID;    
                     }   
                               
                     $invitedGroups = array();
                     if ($objUser = FWUser::getFWUserObject()->objUser->getUsers()) {               
                        while (!$objUser->EOF) {   
                        foreach($objUser->getAssociatedGroupIds() as $key => $groupId) {
                            if(in_array($groupId, $objEvent->invitedGroups))  {
                             $invitedGroups[$objUser->getEmail()] = $objUser->getFrontendLanguage();   
                            }
                        }
                        $objUser->next();
                        }
                     }           
                     
                     $recipients = array_merge($recipients, $invitedGroups);  
                break;
                case 3:
                     $notificationEmails= explode(",", $objEvent->notificationTo);
                     foreach ($notificationEmails as $key => $mail) {
                        $recipients[$mail] = $_LANGID;    
                     }   
                break;
                default:
            }   

            $date = date(parent::getDateFormat()." - H:i:s");       
            $eventTitle = $objEvent->title; 
            $eventStart = date(parent::getDateFormat()." (H:i:s)", $objEvent->startDate); 
            $eventEnd = date(parent::getDateFormat()." (H:i:s)", $objEvent->endDate);
            $protocol = ASCMS_PROTOCOL;
            $domain = $_CONFIG['domainUrl'].ASCMS_PATH_OFFSET;                                                                                         
            $regType = $objRegistration->type == 1 ? $_ARRAYLANG['TXT_CALENDAR_REG_REGISTRATION'] : $_ARRAYLANG['TXT_CALENDAR_REG_SIGNOFF'];                                                                                         
            $eventLink = urldecode($protocol."://".$domain.'/'.CONTREXX_DIRECTORY_INDEX.'?section='.$this->moduleName.'&cmd=detail&id='.$objEvent->id);     
            $regLink = urldecode($protocol."://".$domain.'/'.CONTREXX_DIRECTORY_INDEX.'?section='.$this->moduleName.'&cmd=register&id='.$objEvent->id."&date=".$objEvent->startDate);  
            $placeholder = array('[[REGISTRATION_TYPE]]', '[[TITLE]]', '[[START_DATE]]', '[[END_DATE]]', '[[LINK_EVENT]]', '[[LINK_REGISTRATION]]', '[[USERNAME]]', '[[FIRSTNAME]]', '[[LASTNAME]]', '[[URL]]', '[[DATE]]');
            
            if (!empty($regId)) {
                $query = 'SELECT `v`.`value`, `n`.`default`, `f`.`type`
                          FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_value AS `v`
                          INNER JOIN '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_name AS `n`
                          ON `v`.`field_id` = `n`.`field_id`
                          INNER JOIN '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field AS `f`
                          ON `v`.`field_id` = `f`.`id`
                          WHERE `v`.`reg_id` = '.$regId.'
                          AND (
                                 `f`.`type` = "salutation"
                              OR `f`.`type` = "firstname"
                              OR `f`.`type` = "lastname"
                              OR `f`.`type` = "mail"
                          )';
                $objResult = $objDatabase->Execute($query);
                
                $arrDefaults = array();
                $arrValues   = array();
                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        if (!empty($objResult->fields['default'])) {
                            $arrDefaults[$objResult->fields['type']] = explode(',', $objResult->fields['default']);
                        }
                        $arrValues[$objResult->fields['type']] = $objResult->fields['value'];
                        $objResult->MoveNext();
                    }
                }
                
                $regSalutation = !empty($arrValues['salutation']) ? $arrDefaults['salutation'][$arrValues['salutation'] - 1] : '';
                $regFirstname  = !empty($arrValues['firstname'])  ? $arrValues['firstname'] : '';
                $regLastname   = !empty($arrValues['lastname'])   ? $arrValues['lastname']  : '';
                $regMail       = !empty($arrValues['mail'])       ? $arrValues['mail']      : '';
                $regSearch     = array('[[REGISTRATION_SALUTATION]]', '[[REGISTRATION_FIRSTNAME]]', '[[REGISTRATION_LASTNAME]]', '[[REGISTRATION_EMAIL]]');
                $regReplace    = array(  $regSalutation,                $regFirstname,                $regLastname,                $regMail);
            }
            
            if($this->mailList[$_LANGID]['mail']->title == '') { 
                $langId = 0;    
            } else {
                $langId = $_LANGID;
            }
                           
            if(is_array($this->mailList[$langId]['recipients'])) {
                $recipients = array_merge($recipients, $this->mailList[$langId]['recipients']);       
            }
            
            if(is_array($this->mailList[$langId]['default_recipient'])) {
                $recipients = array_merge($recipients, $this->mailList[$langId]['default_recipient']);       
            }     
                        
            $objMail = new phpmailer();

            if ($_CONFIG['coreSmtpServer'] > 0) {
                $arrSmtp = SmtpSettings::getSmtpAccount($_CONFIG['coreSmtpServer']);
                if ($arrSmtp !== false) {
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
            $objMail->FromName = $_CONFIG['coreGlobalPageTitle'];
            $objMail->AddReplyTo($_CONFIG['coreAdminEmail']); 

            foreach ($recipients as $mailAdress => $langId) {
                if(!empty($mailAdress)) {        
                    if($this->mailList[$langId]['mail']->title == '') { 
                        $langId = 0;    
                    } 

                    if ($objUser = FWUser::getFWUserObject()->objUser->getUsers($filter = array('email' => $mailAdress, 'is_active' => true))) {
                        $userNick = $objUser->getUsername();
                        $userFirstname = $objUser->getProfileAttribute('firstname');
                        $userLastname = $objUser->getProfileAttribute('lastname');  

                    }

                    $mailTitle = $this->mailList[$langId]['mail']->title;
                    $mailContentText = !empty($this->mailList[$langId]['mail']->content_text) ? $this->mailList[$langId]['mail']->content_text : strip_tags($this->mailList[$langId]['mail']->content_html);
                    $mailContentHtml = !empty($this->mailList[$langId]['mail']->content_html) ? $this->mailList[$langId]['mail']->content_html : $this->mailList[$langId]['mail']->content_text;
                    $replaceContent = array($regType, $eventTitle, $eventStart, $eventEnd, $eventLink, $regLink, $userNick, $userFirstname, $userLastname, $domain, $date);

                    for ($x = 0; $x < 11; $x++) {
                        $mailTitle       = str_replace($placeholder[$x], html_entity_decode($replaceContent[$x], ENT_QUOTES, CONTREXX_CHARSET), $mailTitle);                                                                           
                        $mailContentText = str_replace($placeholder[$x], html_entity_decode($replaceContent[$x], ENT_QUOTES, CONTREXX_CHARSET), $mailContentText);                                                                           
                        $mailContentHtml = str_replace($placeholder[$x], $replaceContent[$x], $mailContentHtml);
                    }    

                    $mailTitle       = str_replace($regSearch, html_entity_decode($regReplace, ENT_QUOTES, CONTREXX_CHARSET), $mailTitle);                                                                           
                    $mailContentText = str_replace($regSearch, html_entity_decode($regReplace, ENT_QUOTES, CONTREXX_CHARSET), $mailContentText);                                                                           
                    $mailContentHtml = str_replace($regSearch, $regReplace, $mailContentHtml);

                    $mailContentText = str_replace('[[REGISTRATION_DATA]]', $registrationDataText, $mailContentText);                                                                           
                    $mailContentHtml = str_replace('[[REGISTRATION_DATA]]', $registrationDataHtml, $mailContentHtml);

                   /* echo "send to: ".$mailAdress."<br />";
                    echo "send title: ".$mailTitle."<br />";*/

                    $objMail->Subject = $mailTitle;
                    $objMail->Body = $mailContentHtml;
                    $objMail->AltBody = $mailContentText; 
                    $objMail->AddAddress($mailAdress);   
                    $objMail->Send();
                    $objMail->ClearAddresses();
                }
            }            
        }
    }
}