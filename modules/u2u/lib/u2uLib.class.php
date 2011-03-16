<?php

/**
 * U2U library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Raveendran.L
 * @version     v 1.00
 * @package     contrexx
 * @subpackage  U2U_U2U
 */

/**
 * Includes
 */

class u2uLibrary {

    var $_boolInnoDb = false;
    var $_intLanguageId;
    var $_intCurrentUserId;
    var $_arrSettings           = array();
    var $_arrLanguages          = array();
   	var $_arrlistLevel = null;
   	var $PaginactionCount;
   	var $orderedResults,$orderofResult;
    var $paginationCount,$counter;

    /**
    * Constructor
    *
    */
    function __construct()  {
        $this->setDatabaseEngine();
        $this->_arrSettings     = $this->createSettingsArray();
        $this->_arrLanguages    = $this->createLanguageArray();
    }


    /**
     * Reads out the used database engine and sets the local variable.
     * @global      array       $objDatabase
     */
    function setDatabaseEngine() {
        global $objDatabase;

        $objMetaResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'module_u2u_settings"');
        if (preg_match('/.*innodb.*/i', $objMetaResult->fields['Engine'])) {
            $this->_boolInnoDb = true;
        }
    }

    /**
     *
     * Selects the username with the id of the user who has loggged on.
     * @global      $objDatabase
     */
    function getUserID($userName) {
        global $objDatabase;

        $userName = contrexx_addslashes($userName);
        $selUserID  = 'SELECT id FROM '.DBPREFIX.'access_users
                       WHERE username="'.$userName.'" AND
                       active=1';
        $objResult = $objDatabase->Execute($selUserID);
        while (!$objResult->EOF) {
          $ID=$objResult->fields['id'];
          $objResult->MoveNext();
        }
        return $ID;
    }

    /**
     *
     * creates the Array of messages for the users..
     * @global      $objDatabase
     */
    function createEntryDetails($userID, $pos) {

        global $objDatabase,$_CONFIG,$_ARRAYLANG;

        $userID = intval($userID);
        /**
          *Checks the Messages is the notification or the Messages..
          *if it is a notification we have make the condition as the Messages should show (not opened only)
          */

        if($_REQUEST["cmd"]=="inbox") {
             $whereCondition ="";
             $pagingText="<b>".$_ARRAYLANG['TXT_INBOX_PAGING']."</b>";
        }
        if($_REQUEST["cmd"]=="notification") {
            $whereCondition =' AND sentMsg.mesage_open_status="0"';
            $pagingText="<b>".$_ARRAYLANG['TXT_NOTIFICATION_PAGING']."</b>";
        }

        $selMessage ='SELECT
                        Log.message_text,
                        Log.message_title,
                        Log.message_id,
                        User.username,
                        sentMsg.userid,
                        sentMsg.date_time
                        FROM
                        '.DBPREFIX.'module_u2u_sent_messages sentMsg,
                        '.DBPREFIX.'module_u2u_message_log Log,
                        '.DBPREFIX.'access_users User

                        WHERE
                        sentMsg.receiver_id='.$userID.' AND
                        User.id=sentMsg.userid AND
                        User.active!=0 AND
                        Log.message_id=sentMsg.message_id'.$whereCondition.'
                        ORDER BY sentMsg.date_time DESC';
      $objResult = $objDatabase->Execute($selMessage);

      $count = $objResult->RecordCount();
      $this->counter=$count;

	  $paging = getPaging($count, $pos, "&amp;section=u2u&amp;cmd=".$_REQUEST['cmd'],$pagingText, true);

      $selMessage ='SELECT
                        Log.message_text,
                        Log.message_title,
                        Log.message_id,
                        User.username,
                        sentMsg.userid,
                        sentMsg.date_time
                        FROM
                        '.DBPREFIX.'module_u2u_sent_messages sentMsg,
                        '.DBPREFIX.'module_u2u_message_log Log,
                        '.DBPREFIX.'access_users User

                        WHERE
                        sentMsg.receiver_id='.$userID.' AND
                        User.id=sentMsg.userid AND
                        User.active!=0 AND
                        Log.message_id=sentMsg.message_id'.$whereCondition.'
                        ORDER BY sentMsg.date_time DESC';

       $objResult = $objDatabase->SelectLimit($selMessage, $_CONFIG['corePagingLimit'], $pos);
       $this->paginationCount=$paging;

       while (!$objResult->EOF) {
          $messageID=$objResult->fields['message_id'];
          $arrMessage[$messageID]["message"]        =   $objResult->fields['message_text'];
          $arrMessage[$messageID]["message_title"]  =   $objResult->fields['message_title'];
          $arrMessage[$messageID]["username"]       =   $objResult->fields['username'];
          $arrMessage[$messageID]["date_time"]      =   $objResult->fields['date_time'];
          $objResult->MoveNext();
        }

       return $arrMessage;
    }

    /**
     *
     * creates the Array of messages for the users whom they sent...
     * @global      $objDatabase
     */
    function createEntryDetailsOutbox($userID, $pos) {
        global $objDatabase,$_CONFIG,$_ARRAYLANG;

        $userID = intval($userID);
        $selMessage ='SELECT
                        Log.message_text,
                        Log.message_title,
                        Log.message_id,
                        User.username,
                        sentMsg.userid,
                        sentMsg.date_time
                        FROM
                        '.DBPREFIX.'module_u2u_sent_messages sentMsg,
                        '.DBPREFIX.'module_u2u_message_log Log,
                        '.DBPREFIX.'access_users User

                        WHERE
                        sentMsg.userid='.$userID.' AND
                        User.id=sentMsg.receiver_id AND
                        User.active!=0 AND
                        Log.message_id=sentMsg.message_id AND
                        sentMsg.mesage_open_status="0"
                        ORDER BY sentMsg.date_time DESC';
      //$pos = intval($_GET['pos']);
      $objResult = $objDatabase->Execute($selMessage);
      $count = $objResult->RecordCount();
      $this->counter=$count;
	  $paging = getPaging($count, $pos, "&amp;section=u2u&amp;cmd=outbox", "<b>".$_ARRAYLANG['TXT_OUTBOX_PAGING']."</b>", true);

      $selMessage ='SELECT
                        Log.message_text,
                        Log.message_title,
                        Log.message_id,
                        User.username,
                        sentMsg.userid,
                        sentMsg.date_time
                        FROM
                        '.DBPREFIX.'module_u2u_sent_messages sentMsg,
                        '.DBPREFIX.'module_u2u_message_log Log,
                        '.DBPREFIX.'access_users User

                        WHERE
                        sentMsg.userid='.$userID.' AND
                        User.id=sentMsg.receiver_id AND
                        User.active!=0 AND
                        Log.message_id=sentMsg.message_id AND
                        sentMsg.mesage_open_status="0"
                        ORDER BY sentMsg.date_time DESC';


      $objResult = $objDatabase->SelectLimit($selMessage, $_CONFIG['corePagingLimit'], $pos);
      $this->paginationCount=$paging;
      //$objResult = $objDatabase->Execute($selMessage);

       while (!$objResult->EOF) {
          $messageID=$objResult->fields['message_id'];
          $arrMessage[$messageID]["message"]        =   $objResult->fields['message_text'];
          $arrMessage[$messageID]["message_title"]  =   $objResult->fields['message_title'];
          $arrMessage[$messageID]["username"]       =   $objResult->fields['username'];
          $arrMessage[$messageID]["date_time"]      =   $objResult->fields['date_time'];
          $objResult->MoveNext();
       }
       return $arrMessage;
    }

    /**
     *
     * Calculating the Total number of new messages for the Private notifications..
     * @global      $objDatabase
     */
    function notificationEntryMessage($userID) {

        global $objDatabase;

        $userID = intval($userID);
        $selMessageCount='SELECT
                        COUNT(Log.message_id) AS numberofEntries
                        FROM
                        '.DBPREFIX.'module_u2u_sent_messages sentMsg,
                        '.DBPREFIX.'module_u2u_message_log Log,
                        '.DBPREFIX.'access_users User

                        WHERE
                        sentMsg.receiver_id='.$userID.' AND
                        User.id=sentMsg.userid AND
                        Log.message_id=sentMsg.message_id
                        AND sentMsg.mesage_open_status="0"
                        AND User.active!=0
                        ORDER BY sentMsg.date_time';
        $objResult = $objDatabase->Execute($selMessageCount);
        $newMessages=$objResult->fields['numberofEntries'];
        return $newMessages;
    }

    /**
     *
     * Returns the Messages with the array and updating the message open status
     * @global      $objDatabase
     */
    function createEntryShowMessage($messageID) {
        global $objDatabase;

        $messageID = intval($messageID);
        if($_REQUEST["status"]=="outboxmsg" || !empty($_REQUEST['send'])) {
            $whereCondition=' AND User.id=sentMsg.receiver_id';
        } else {
            $whereCondition=' AND User.id=sentMsg.userid';
        }

        /**Select the message from the Database... */
        $selShowMessage ='SELECT
                        Log.message_text,
                        Log.message_title,
                        Log.message_id,
                        User.username,
                        User.regdate,
                        sentMsg.userid,
                        sentMsg.date_time
                        FROM
                        '.DBPREFIX.'module_u2u_sent_messages sentMsg,
                        '.DBPREFIX.'module_u2u_message_log Log,
                        '.DBPREFIX.'access_users User
                        WHERE
                        Log.message_id='.$messageID.' AND
                        sentMsg.message_id=Log.message_id
                        AND User.active!=0'.$whereCondition.' ORDER BY sentMsg.date_time DESC';
        $objResult = $objDatabase->Execute($selShowMessage);

        /**Updating the Message Open status.. */
        if(empty($_REQUEST["status"]) && empty($_REQUEST['send'])) {
            $updateStatus ='UPDATE
                          '.DBPREFIX.'module_u2u_sent_messages
                           SET  mesage_open_status="1"
                           WHERE message_id="'.$messageID.'"
                          ';
            $objUpdate = $objDatabase->Execute($updateStatus);
        }

        while (!$objResult->EOF) {
            $userID=$objResult->fields['userid'];
            $arrShowMessage["message"]           =   $objResult->fields['message_text'];
            $arrShowMessage["message_title"]     =   $objResult->fields['message_title'];
            $arrShowMessage["username"]          =   $objResult->fields['username'];
            $arrShowMessage["registerd_date"]    =  date('Y-m-d',$objResult->fields['regdate']);
            $arrShowMessage["date_time"]         =   $objResult->fields['date_time'];
            $objResult->MoveNext();
        }
        return $arrShowMessage;
    }

    /**
     *
     * Deletes the Messages from the table from the message log
     * @global      $objDatabase
     */
    function deleteMsg($id) {
        global $objDatabase;
        $id = intval($id);
        $delMessage = 'DELETE  from '.DBPREFIX.'module_u2u_message_log WHERE message_id='.$id.'';
        $objUpdate = $objDatabase->Execute($delMessage);
        return $objUpdate;
    }

    /**
     *
     * Gets the Maximum posting size
     * @global      $objDatabase
     */
    function _getMaxPostingDetails()  {
        global $objDatabase;

        $settingQuery='SELECT value from '.DBPREFIX.'module_u2u_settings WHERE name="max_posting_size"';
        $objResult= $objDatabase->Execute($settingQuery);
        $arrShowSettings["max_posting_size"] = $objResult->fields['value'];
        return $arrShowSettings;
    }

    /**
     *
     * Gets the Maximum Posting Chars on the Messages..
     * @global      $objDatabase
     */
    function _getMaxCharDetails() {
        global $objDatabase;

        $settingQuery='SELECT value from '.DBPREFIX.'module_u2u_settings WHERE name="max_posting_chars"';
        $objResult=$objDatabase->Execute($settingQuery);
        $arrShowSettings['max_posting_chars'] = $objResult->fields['value'];
        return $arrShowSettings;
    }

    /**
     *
     * Gets the Maximum posting Entries of the Users..
     * @global      $objDatabase
     */
    function _getMaxpostings($id)  {
        global $objDatabase;

        $id = intval($id);
        $settingQuery        = 'SELECT count(*) AS numberOfEntries from '.DBPREFIX.'module_u2u_sent_messages WHERE userid='.$id.'';
        $objResult           = $objDatabase->Execute($settingQuery);
        $arrShowSettings     = $objResult->fields['numberOfEntries'];
        return $arrShowSettings;
    }

    /**
     *
     * Gets the Email subject Specified by the admin..
     * @global      $objDatabase
     */
    function _getEmailSubjectDetails() {
        global $objDatabase;

        $settingQuery='SELECT value from '.DBPREFIX.'module_u2u_settings WHERE name="subject"';
        $objResult=$objDatabase->Execute($settingQuery);
        $arrShowSettings['subject'] = $objResult->fields['value'];
        return $arrShowSettings;
    }

    /**
     *
     * Gets the Email From address details Specified by the admin..
     * @global      $objDatabase
     */
    function _getEmailFromDetails() {
        global $objDatabase;

        $settingQuery='SELECT value from '.DBPREFIX.'module_u2u_settings WHERE name="from"';
        $objResult=$objDatabase->Execute($settingQuery);
        $arrShowSettings['from'] = $objResult->fields['value'];
        return $arrShowSettings;
    }

    /**
     *
     * Gets the Email Message content details Specified by the admin..
     * @global      $objDatabase
     */
    function _getEmailMessageDetails(){
        global $objDatabase;

        $settingQuery='SELECT value from '.DBPREFIX.'module_u2u_settings WHERE name="email_message"';
        $objResult=$objDatabase->Execute($settingQuery);
        $arrShowSettings['email_message']        =$objResult->fields['value'];
        return $arrShowSettings;
    }

    /**
     *
     * Gets the Status of the Users..
     * @global      $objDatabase
     */
    function _getStatus($userID) {

        global $objDatabase;
        $userID = intval($userID);
        $selStatusUser      = 'select u2u_active from '.DBPREFIX.'access_users
                              where id="'.$userID.'" and active="1"';
        $objResult          = $objDatabase->Execute($selStatusUser);
        $countStatus        = $objResult->RecordCount();
        return $countStatus;

    }

    /**
     *
     * Gets the Email of the Users..
     * @global      $objDatabase
     */
    function _getEmail($id) {
        global $objDatabase;

        $id = intval($id);
        $emailQuery='SELECT email from '.DBPREFIX.'access_users WHERE id='.$id.'';
        $objResult=$objDatabase->Execute($emailQuery);
        $arrShowEmail['email']        =$objResult->fields['email'];
        return $arrShowEmail;
    }

     /**
     *
     * Gets the Name of the Users..
     * @global      $objDatabase
     */
    function _getName($id) {
        global $objDatabase;

        $id = intval($id);
        $nameQuery='SELECT username from '.DBPREFIX.'access_users WHERE id='.$id.'';
        $objResult=$objDatabase->Execute($nameQuery);
        $arrShowname['username']        =$objResult->fields['username'];
        return $arrShowname;
    }

     /**
     *
     * Gets the City of the Users..
     * @global      $objDatabase
     */
    function _getCity($id) {
        global $objDatabase;

        $id = intval($id);
        $cityQuery='SELECT city from '.DBPREFIX.'access_user_profile WHERE user_id='.$id.'';
        $objResult=$objDatabase->Execute($cityQuery);
        $arrShowcity['city']        =$objResult->fields['city'];
        return $arrShowcity;
    }

     /**
     *
     * Gets the Website Address of the Users..
     * @global      $objDatabase
     */
    function _getSite($id) {
        global $objDatabase;

        $id = intval($id);
        $siteQuery='SELECT website from '.DBPREFIX.'access_user_profile WHERE user_id='.$id.'';
        $objResult=$objDatabase->Execute($siteQuery);
        $arrShowsite['website'] = $objResult->fields['website'];
        return $arrShowsite;
    }

     /**
     *
     * Gets the Address list members of the Users..
     * @global      $objDatabase
     */
    function _getBuddyNames($id) {
        global $objDatabase;

        $id = intval($id);
        $buddiesQuery='SELECT buddies_id from '.DBPREFIX.'module_u2u_address_list WHERE user_id='.$id.'';
        $objResult=$objDatabase->Execute($buddiesQuery);
        while (!$objResult->EOF) {
              $arrShowbuddies[] = $objResult->fields['buddies_id'];
              $objResult->MoveNext();
        }
       //print_r($arrShowbuddies);
        return $arrShowbuddies;
    }

    /**
     * Get the User IDs of the currently logged-in user's buddies
     *
     * @global ADONewConnection
     */
    static function getIdsOfBuddies()
    {
        global $objDatabase;
        static $arrIds;

        if (!isset($arrIds)) {
            $arrIds = array();

            $objFWUser = FWUser::getFWUserObject();
            if ($objFWUser->objUser->login()) {
                $objResult = $objDatabase->Execute('SELECT `buddies_id` FROM `'.DBPREFIX.'module_u2u_address_list` WHERE `user_id` = '.$objFWUser->objUser->getId());
                if ($objResult !== false) {
                    while (!$objResult->EOF) {
                        $arrIds[] = $objResult->fields['buddies_id'];
                        $objResult->MoveNext();
                    }
                }
            }
        }

        return $arrIds;
    }
}
?>
