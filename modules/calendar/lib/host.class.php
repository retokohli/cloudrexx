<?php
/**
 * Calendar Class Host
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

class CalendarHost extends CalendarLibrary
{
    public $id;
    public $title;
    public $uri;
    public $catId;
    public $key;
    public $status;
    public $confirmed;
    
    function __construct($id=null){
        if($id != null) {
            self::get($id);
        }
    }
    
    function get($hostId) {
        global $objDatabase, $_LANGID;
        
        $query = "SELECT  id,title,uri,cat_id,`key`,confirmed,status 
                    FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_host
                   WHERE id = '".intval($hostId)."'
                   LIMIT 1";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            $this->id = intval($hostId);
            $this->title = htmlentities($objResult->fields['title'], ENT_QUOTES, CONTREXX_CHARSET);
            $this->uri = htmlentities($objResult->fields['uri'], ENT_QUOTES, CONTREXX_CHARSET);
            $this->catId = intval($objResult->fields['cat_id']);
            $this->key = htmlentities($objResult->fields['key'], ENT_QUOTES, CONTREXX_CHARSET);
            $this->confirmed = intval($objResult->fields['confirmed']);
            $this->status = intval($objResult->fields['status']);
        }
    }
    
    function save($data) {
        global $objDatabase;
        
        $title      = contrexx_addslashes(contrexx_strip_tags($data['title']));
        $uri        = contrexx_addslashes(contrexx_strip_tags($data['uri']));
        
        if(substr($uri,-1) != '/') {   
            $uri = $uri."/";  
        }
                
        $category   = intval($data['category']);
        $key        = contrexx_addslashes(contrexx_strip_tags($data['key']));
        $status     = intval($data['status']);
        $confirmed  = intval(1);
        
        if(empty($key)) { 
            $key = parent::generateKey();  
        }
        
        if(intval($this->id) == 0) {
            $query = "INSERT INTO ".DBPREFIX."module_".$this->moduleTablePrefix."_host
                                  (`title`,`uri`,`cat_id`,`key`,`confirmed`,`status`) 
                           VALUES ('".$title."','".$uri."','".$category."','".$key."','".$confirmed."','".$status."')";
        } else {
            $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_host
                         SET `title` = '".$title."',
                             `uri` = '".$uri."',
                             `cat_id` = '".$category."',
                             `key` = '".$key."',
                             `status` = '".$status."'
                       WHERE `id` = '".intval($this->id)."'";
        }
        
        $objResult = $objDatabase->Execute($query);
            
        if($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function switchStatus(){
        global $objDatabase;
        
        if($this->status == 1) {
            $hostStatus = 0;
        } else {
            $hostStatus = 1;
        }
        
        $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_host
                     SET status = '".intval($hostStatus)."'
                   WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    function delete(){
        global $objDatabase;
        
        $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_host
                        WHERE id = '".intval($this->id)."'";
        
        $objResult = $objDatabase->Execute($query);
        
        if ($objResult !== false) {
            $query = "DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_note_host
                            WHERE host_id = '".intval($this->id)."'";
            
            $objResult = $objDatabase->Execute($query);
            
            if ($objResult !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}