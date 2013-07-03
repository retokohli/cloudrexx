<?php

/**
 * Calendar Class Mail
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */
class CalendarMail extends CalendarLibrary
{
    public $id;
    public $title;
    public $content_text;
    public $content_html;
    public $lang_id;
    public $recipients;
    public $default_recipient;
    public $action_id;
    public $is_default;
    public $status;
    public $templateList;
    
    function __construct($id=null){
        if($id != null) {
            self::get($id);
        }
    }
    
    function get($mailId) {
        global $objDatabase, $_ARRAYLANG, $_LANGID;
        
        $query = "SELECT id,title,recipients,content_text,content_html,lang_id,action_id,is_default,status
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                   WHERE id = '".intval($mailId)."'
                   LIMIT 1";
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $this->id = intval($mailId);
            $this->title = stripslashes($objResult->fields['title']);
            $this->content_text = stripslashes($objResult->fields['content_text']);
            $this->content_html = stripslashes($objResult->fields['content_html']);
            $this->recipients = htmlentities($objResult->fields['recipients'], ENT_QUOTES, CONTREXX_CHARSET);
            $this->default_recipient = htmlentities($objResult->fields['default_recipient'], ENT_QUOTES, CONTREXX_CHARSET);
            $this->action_id = intval($objResult->fields['action_id']);
            $this->lang_id = intval($objResult->fields['lang_id']);
            $this->is_default = intval($objResult->fields['is_default']);
            $this->status = intval($objResult->fields['status']);
        }
    }
    
    function delete(){
        global $objDatabase;
        
        $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                   WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function setAsDefault(){
        global $objDatabase;
        
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                     SET is_default = '0'
                   WHERE action_id = '".intval($this->action_id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                     SET is_default = '1'
                   WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function switchStatus(){
        global $objDatabase;
        
        if($this->status == 1) {
            $mailStatus = 0;
        } else {
            $mailStatus = 1;
        }
        
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                     SET status = '".intval($mailStatus)."'
                   WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function save($data) {
        global $objDatabase;
        
        $title          = contrexx_addslashes(contrexx_strip_tags($data['title']));
        $content_text   = contrexx_addslashes(contrexx_strip_tags($data['content_text']));
        $content_html   = contrexx_addslashes($data['content_html']);
        $lang_id        = intval($data['lang']);
        $action_id      = intval($data['action']);
        $recipients     = contrexx_addslashes(contrexx_strip_tags($data['recipients']));
        
        if(intval($this->id) == 0) {
            $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                                  (`title`,`content_text`,`content_html`,`recipients`,`lang_id`,`action_id`,`status`) 
                           VALUES ('".$title."','".$content_text."','".$content_html."','".$recipients."','".$lang_id."','".$action_id."','0')";
        } else {
            $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_mail
                         SET `title` = '".$title."',
                             `content_text` = '".$content_text."',
                             `content_html` = '".$content_html."',
                             `recipients` = '".$recipients."',
                             `lang_id` = '".$lang_id."',
                             `action_id` = '".$action_id."'
                       WHERE `id` = '".intval($this->id)."'";
        }
        
        $objResult = $objDatabase->Execute($query);
        if($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getTemplateList() {
        global $objDatabase;
        
        $query = 'SELECT `id`
                  FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_mail
                  WHERE `status` = 1';
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $objMail = new CalendarMail(intval($objResult->fields['id']));
                $this->templateList[] = $objMail;   
                $objResult->MoveNext();
            }
        }
    }
    
    function getTemplateDropdown($selectedId=null, $actionId=null) {
        parent::getSettings();
        $arrOptions = array();
        
        foreach ($this->templateList as $key => $objMail) {
            if ($actionId != null && $actionId != $objMail->action_id) continue;
            $arrOptions[$objMail->id] = $objMail->title;
        }      
        
        $options = parent::buildDropdownmenu($arrOptions, $selectedId);
        
        return $options;
    }
}
