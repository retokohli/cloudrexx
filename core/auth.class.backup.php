<?php
/**
 * Authentification (backup?)
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Authentification (backup?)
 *
 * The Authentification class checks if the username/password combination matches
 * the database user table.
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class Auth
{
    var $type = ''; // values are frontend or backend
    var $errorMessage = array();
    var $status;

    /**
     * Constructor
     *
     */
    function Auth($authType='frontend')
    {
        $this->type=$authType;
    }

    function checkAuth()
    {
        global $sessionObj, $_CORELANG;

        $this->assignData();

        if (isset($_SESSION['auth']['username']) && isset($_SESSION['auth']['password'])) {
            ///////////////////////////////////
            // Backend Authentification
            ///////////////////////////////////
            if ($this->type=='backend') {
                if (!$this->checkCode()) {
                    $this->errorMessage[] = $_CORELANG['TXT_SECURITY_CODE_IS_INCORRECT'];
                    return false;
                }
                if ($this->checkUserData() && $this->checkType()) {
                    // sets cookie for 30 days
                    setcookie("username", $_SESSION['auth']['username'], time()+3600*24*30);
                    $this->log();
                    $sessionObj->cmsSessionUserUpdate($username=$_SESSION['auth']['username']);
                    return true;
                }
            }else {
                ///////////////////////////////////
                // Frontend Authentification
                ///////////////////////////////////
                if($this->checkUserData() && $this->checkType()) {
                    $sessionObj->cmsSessionUserUpdate($username=$_SESSION['auth']['username']);
                    return true;
                }
            }
        }
        $sessionObj->cmsSessionUserUpdate($username="unknown");
        $sessionObj->cmsSessionStatusUpdate($status=$this->type);
        return false;
    }

    /**
     * Assign data from login form to internal values
     *
     * This function takes the values for username and password
     * from $_POST and assigns them to internal variables.
     *
     * @access private
     * @see    Auth
     * @return void
     */
    function assignData()
    {
        if (isset($_POST['USERNAME']) && $_POST['USERNAME'] != '') {
            $_SESSION['auth']['username'] = (get_magic_quotes_gpc() == 1 ? stripslashes($_POST['USERNAME']) : $_POST['USERNAME']);
        }

        if (isset($_POST['PASSWORD']) && $_POST['PASSWORD'] != '') {
            $_SESSION['auth']['password'] = md5($_POST['PASSWORD']);
        }

        if (isset($_POST['secid2']) && $_POST['secid2'] != '') {
            $_SESSION['auth']['secid2'] = (get_magic_quotes_gpc() == 1 ? stripslashes($_POST['secid2']) : $_POST['secid2']);
        }
    }

    /**
     * checks the code from the security image
     *
     * This function compares the security image code with the
     * Code in the session
     *
     * @access private
     * @return void
     */
    function checkCode()
    {
        if ($_SESSION['auth']['secid2'] != $_SESSION['auth']['secid']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * check user data from the database
     *
     * check user data from the database.
     *
     * @access private
     * @global mixed Database
     * @global array Core language
     * @return void
     */
    function checkUserData()
    {
        global $objDatabase, $_CORELANG;

        $arrGroups = array();
        $arrUserGroups = array();

        if (isset($_SESSION['auth']['username']) && isset($_SESSION['auth']['password'])) {
            $user = $objDatabase->qstr($_SESSION['auth']['username']);

            $query = "SELECT password,
                                   username,
                                   id,
                                   groups,
                                   is_admin,
                                   langId,
                                   firstname,
                                   lastname,
                                   active
                              FROM ".DBPREFIX."access_users
                             WHERE username = ".$user." AND active=1";

            $objResult = $objDatabase->SelectLimit($query, 1);

            if ($objResult !== false) {
                if ($_SESSION['auth']['password'] == $objResult->fields['password']) {
                    $_SESSION['auth']['userid'] = $objResult->fields['id'];
                    $_SESSION['auth']['username'] = $objResult->fields['username'];
                    $_SESSION['auth']['name'] = $objResult->fields['firstname']." ".$objResult->fields['lastname'];
                    $_SESSION['auth']['lang'] = $objResult->fields['langId'];
                    $_SESSION['auth']['is_admin'] = $objResult->fields['is_admin'];

                    $arrGroups = explode(",", $objResult->fields['groups']);

                    $objResult = $objDatabase->Execute("SELECT group_id FROM ".DBPREFIX."access_user_groups WHERE is_active=1");
                    if ($objResult !== false) {
                        while (!$objResult->EOF) {
                            if (in_array($objResult->fields['group_id'], $arrGroups)) {
                                array_push($arrUserGroups, $objResult->fields['group_id']);
                            }
                            $objResult->MoveNext();
                        }
                    }
                    $_SESSION['auth']['groups'] = $arrUserGroups;

                    $this->_initUserRights();
                    return true;
                } else {
                    $this->errorMessage[] = $_CORELANG['TXT_PASSWORD_OR_USERNAME_IS_INCORRECT'];
                    return false;
                }
            } else {
                $this->errorMessage[] = $_CORELANG['TXT_PASSWORD_OR_USERNAME_IS_INCORRECT'];
                return false;
            }
        }
        return false;
    }

    function _initUserRights()
    {
        global $objDatabase;

        $sqlWhereString = "";
        $arrStaticRightIds = array();
        $arrDynamicRightIds = array();

        $arrUserGroups = $_SESSION['auth']['groups'];
        foreach ($arrUserGroups as $groupId) {
            $sqlWhereString .= "group_id=".intval($groupId)." OR ";
        }
        $sqlWhereString = substr($sqlWhereString, 0, strlen($sqlWhereString)-4);

        if (count($arrUserGroups)>0) {
            // get static right ids
            $objResult = $objDatabase->Execute("SELECT access_id FROM ".DBPREFIX."access_group_static_ids WHERE ".$sqlWhereString);

            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    array_push($arrStaticRightIds, $objResult->fields['access_id']);
                    $objResult->MoveNext();
                }
            }

            // get dynamic right ids
            $objResult = $objDatabase->Execute("SELECT access_id FROM ".DBPREFIX."access_group_dynamic_ids WHERE ".$sqlWhereString);

            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    array_push($arrDynamicRightIds, $objResult->fields['access_id']);
                    $objResult->MoveNext();
                }
            }

            $_SESSION['auth']['static_access_ids'] = array_unique($arrStaticRightIds);
            $_SESSION['auth']['dynamic_access_ids'] = array_unique($arrDynamicRightIds);

            sort($_SESSION['auth']['static_access_ids']);
            sort($_SESSION['auth']['dynamic_access_ids']);
        }
    }

    function checkType()
    {
        global $objDatabase;

        if ($_SESSION['auth']['is_admin']==1)
            return true;
        $arrUserGroups = $_SESSION['auth']['groups'];
        if (is_array($arrUserGroups)) {
            $objResult = $objDatabase->Execute("SELECT group_id FROM ".DBPREFIX."access_user_groups WHERE type='$this->type'");
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    if (in_array($objResult->fields['group_id'],$arrUserGroups))
                        return true;
                    $objResult->MoveNext();
                }
            }
        }
        return false;
    }

    function log()
    {
        global $objDatabase;

        if (!isset($_SESSION['auth']['log'])) {
            $remote_host = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
            $referer = get_magic_quotes_gpc() ? strip_tags((strtolower($_SERVER['HTTP_REFERER']))) : addslashes(strip_tags((strtolower($_SERVER['HTTP_REFERER']))));
            $httpUserAgent = get_magic_quotes_gpc() ? strip_tags($_SERVER['HTTP_USER_AGENT']) : addslashes(strip_tags($_SERVER['HTTP_USER_AGENT']));
            $httpAcceptLanguage = get_magic_quotes_gpc() ? strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']) : addslashes(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']));

            $objDatabase->Execute("INSERT INTO ".DBPREFIX."log
                                        SET userid=".intval($_SESSION['auth']['userid']).",
                                            datetime = ".$objDatabase->DBTimeStamp(time()).",
                                            useragent = '".$httpUserAgent."',
                                            userlanguage = '".$httpAcceptLanguage."',
                                            remote_addr = '".strip_tags($_SERVER['REMOTE_ADDR'])."',
                                            remote_host = '".$remote_host."',
                                            http_x_forwarded_for = '".strip_tags($_SERVER['HTTP_X_FORWARDED_FOR'])."',
                                            http_via = '".strip_tags($_SERVER['HTTP_VIA'])."',
                                            http_client_ip = '".strip_tags($_SERVER['HTTP_CLIENT_IP'])."',
                                            referer ='".$referer."'");
            $_SESSION['auth']['log']=true;
        }
    }

    /**
    * Function logout
    *
    * kills all sessions and redirects to the frontend page
    */
    function logout()
    {
        if (isset($_SESSION['auth'])) {
            unset($_SESSION['auth']);
            session_destroy();
        }

        if ($this->type=='public') {
            header('Location: ?section=login');
        } else {
            header('Location: ../index.php');
        }
        exit;
    }

    /**
     * Function status
     *
     * @global mixed Session
     * @global array Core language
     */
    function status()
    {
        global $sessionObj, $_CORELANG;

        if (!isset($_SESSION['auth']['username'])) {
            return "";
        } else {
            return $_CORELANG['TXT_LOGGED_IN_AS']." ".$_SESSION['auth']['username']." (<a href='?section=logout' title='logout'>logout</a>)";
        }
    }


    /**
     * Function lost password
     *
     * @global mixed Database
     * @global array Core language
     */
    function lostpw()
    {
        global $objDatabase, $_CORELANG;

        $dbErrorMsg = '';

        //check email
        if (isset($_POST['EMAIL'])) {
            $user_mail = $objDatabase->qstr($_POST['EMAIL'], get_magic_quotes_gpc());

            $objResult = $objDatabase->Execute("SELECT password,
                                   username,
                                   id,
                                   email
                              FROM ".DBPREFIX."access_users
                             WHERE email = ".$user_mail." AND active=1");
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $userid = $objResult->fields['id'];
                    $username = $objResult->fields['username'];
                    $usermail = $objResult->fields['email'];

                    //generate restore_key
                    $rand=rand(0, 255);
                    $restore_key = md5($rand);

                    $objDb = getDatabaseObject($dbErrorMsg, true);
                    $objDb->Execute("UPDATE ".DBPREFIX."access_users
                                        SET restore_key='$restore_key'
                                      WHERE id = '$userid'");

                    //send email
                    $link = strtolower(ASCMS_PROTOCOL)."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/index.php?cmd=restorepw&key=".$restore_key;

                    if(getenv("HTTP_X_FORWARDED_FOR"))    {$ipaddress = @getenv("HTTP_X_FORWARDED_FOR");}
                    else                                {$ipaddress = @getenv("REMOTE_ADDR");}

                    $host         = @gethostbyaddr($ipaddress);
                    $lang         = @getenv("HTTP_ACCEPT_LANGUAGE");
                    $browser     = @getenv("HTTP_USER_AGENT");
                    $to         = $usermail;
                    $sendTo        = $usermail;

                    $subject     = $_CORELANG['TXT_RESTORE_MAIL_TITLE'];
                    $now         = date(ASCMS_DATE_FORMAT);

                    // Email message
                    $message  = $_CORELANG['TXT_RESTORE_MAIL_PART1'].$_SERVER['SERVER_NAME'].$_CORELANG['TXT_RESTORE_MAIL_PART2'].$username;
                    $message .= $_CORELANG['TXT_RESTORE_MAIL_PART3'];
                    $message .= $_CORELANG['TXT_RESTORE_MAIL_PART4'];
                    $message .= "\n";
                    $message .= "$link\n";
                    $message .= "\n";
                    $message .= $_CORELANG['TXT_RESTORE_MAIL_PART5']."\n";
                    $message .= "\n";
                    $message .= "Host: $host\n";
                    $message .= "Language: $lang\n";
                    $message .= "Browser: $browser\n";
                    $headers  = "From: $subject <$to>\n";

                    $sendmail = @mail($to,
                            $subject,
                            $message,
                            "From: ".$sendTo."\r\n" .
                            "Reply-To: ".$sendTo."\r\n" .
                            "X-Mailer: PHP/" . phpversion());

                    $objResult->MoveNext();
                }
            }

            if (!isset($userid)) {
                $this->errorMessage[] = $_CORELANG['TXT_WRONG_EMAIL'];
            } else {
                $this->status = "pwok";
                $this->errorMessage[] = $_CORELANG['TXT_PW_RESTORE_SUCCESS'];
            }
        }
    }

    /**
     * Function restore password
     *
     * @global mixed Database
     * @global array Core language
     */
    function restorepw()
    {
        global $objDatabase, $_CORELANG;

        if (isset($_GET['key'])) {
            //get restore_key
            $restore_key = $_GET['key'];

            $objResult = $objDatabase->SelectLimit("SELECT id,
                               username,
                               restore_key
                          FROM ".DBPREFIX."access_users
                         WHERE restore_key = ".$objDatabase->qstr($_GET['key'], get_magic_quotes_gpc())." AND active=1", 1);

            if ($objResult !== false) {
                $user_name = $objResult->fields['username'];
                $user_id = $objResult->fields['id'];
                $user_key = $objResult->fields['restore_key'];
            } else {
                $this->errorMessage[] = $_CORELANG['TXT_NO_DATA_FOUND'];
            }

            //check both keys
            if ($restore_key == $user_key) {
                $this->errorMessage[] = $_CORELANG['TXT_ENTER_NEW_PW'];
                return $user_name;
            } else {
                $this->errorMessage[] = $_CORELANG['TXT_WRONG_KEY'];
            }
        } else {
            $this->errorMessage[] = $_CORELANG['TXT_NO_KEY'];
        }
    }

    /**
     * Function set new password
     *
     * @global mixed Database
     * @global array Core language
     */
    function newpw()
    {
        global $objDatabase, $_CORELANG;

        if (isset($_POST['NEWPASSWORD']) && isset($_POST['CONFIRM_NEWPASSWORD'])) {

            $newpw = $_POST['NEWPASSWORD'];
            $con_newpw= $_POST['CONFIRM_NEWPASSWORD'];
            $restore_key = $_POST['RESTOREKEY'];

            $objResult = $objDatabase->SelectLimit("SELECT restore_key,
                               username,
                               email
                          FROM ".DBPREFIX."access_users
                         WHERE restore_key = ".$objDatabase->qstr($restore_key, get_magic_quotes_gpc())." AND active=1", 1);

            if ($objResult !== false) {
                $user_key = $objResult->fields['restore_key'];
                $usermail = $objResult->fields['email'];
                $username = $objResult->fields['username'];
            }

            //check both keys
            if ($restore_key == $user_key) {
                if ($newpw == $con_newpw) {
                    $newpw = md5($newpw);

                    $objDatabase->Execute("UPDATE ".DBPREFIX."access_users
                                    SET restore_key='',
                                        password= ".$objDatabase->qstr($newpw, get_magic_quotes_gpc())."
                                  WHERE restore_key = ".$objDatabase->qstr($restore_key, get_magic_quotes_gpc()));

                    $this->errorMessage[] = $_CORELANG['TXT_NEW_PW_SUCCESS'];

                    //send mail
                    $link = strtolower(ASCMS_PROTOCOL)."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/";

                    if (getenv("HTTP_X_FORWARDED_FOR"))    {
                        $ipaddress = @getenv("HTTP_X_FORWARDED_FOR");
                    } else {
                        $ipaddress = @getenv("REMOTE_ADDR");
                    }

                    $host         = @gethostbyaddr($ipaddress);
                    $lang         = @getenv("HTTP_ACCEPT_LANGUAGE");
                    $browser     = @getenv("HTTP_USER_AGENT");
                    $to         = $usermail;
                    $sendTo        = $usermail;

                    $subject     = $_CORELANG['TXT_NEWPW_MAIL_TITLE'];
                    $now         = date(ASCMS_DATE_FORMAT);

                   // Email message
                    $message  = $_CORELANG['TXT_NEWPW_MAIL_PART1'].$_SERVER['SERVER_NAME'].$_CORELANG['TXT_NEWPW_MAIL_PART2'].$username;
                    $message .= $_CORELANG['TXT_NEWPW_MAIL_PART3']."\n";
                    $message .= "\n";
                    $message .= $_CORELANG['TXT_NEWPW_MAIL_USER'].$username."\n";
                    $message .= "\n";
                    $message .= $_CORELANG['TXT_NEWPW_MAIL_PART4']."\n";
                    $message .= $link."\n";
                    $headers  = "From: $subject <$to>\n";

                    $sendmail = @mail($to,
                                $subject,
                                $message,
                                "From: ".$sendTo."\r\n" .
                                "Reply-To: ".$sendTo."\r\n" .
                                "X-Mailer: PHP/" . phpversion());
                } else {
                    $this->errorMessage[] = $_CORELANG['TXT_NEW_PW_ERROR'];
                }
            } else {
                $this->errorMessage[] = $_CORELANG['TXT_WRONG_KEY'];
            }
        }
    }

    /**
    * Returns a textual error message
    *
    * @access public
    * @return string   error message
    */
    function errorMessage()
    {
        $_errorMessage ='';
        foreach ($this->errorMessage as $value) {
            $_errorMessage .= $value."<br />";
        }
        return $_errorMessage;
    }
}
?>