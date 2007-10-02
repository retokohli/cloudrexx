<?php
/**
 * User authentification
 * @copyright	CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @version		1.0.0
 */

/**
 * Authentification class
 *
 * The Authentification class checks whether the username/password combination
 * matches the database user table, and validates user rights.
 * @access		public
 * @package     contrexx
 * @subpackage  core
 * @version		1.0.0
 * @todo        Think about moving this stuff to a proper User class,
 *              which would be of much more use in many places.
 *              In fact, it could be linked with Shop::Customer
 *              and any other frontend User group.
 *              Plenty of redundancy here!
 */
class Auth
{
    /**
     * Authentification type
     *
     * Valid values are 'frontend' or 'backend'.
     */
    var $type = '';

    /**
     * Array of error messages
     */
    var $errorMessage = array();

    /**
     * Status
     * @todo    What is this good for?  It's set to the value of $type once,
     *          but never read.
     */
    var $status;


    /**
     * Authentification constructor
     *
     * @param string $authType Authentification type. Defaults to 'frontend',
     *                         see {@link $type}.
     */
    function Auth($authType='frontend')
    {
        $this->type=$authType;
    }

    /**
     * Check the authentification provided by the user.
     * @global mixed   Session object
     * @global array   Core language
     * @return boolean    True if the authentification was successful.
     */
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
                    $sessionObj->cmsSessionUserUpdate($_SESSION['auth']['username']);
                    return true;
                }
            } else {
                ///////////////////////////////////
                // Frontend Authentification
                ///////////////////////////////////
                if ($this->checkUserData() && $this->checkType()) {
                    $sessionObj->cmsSessionUserUpdate($_SESSION['auth']['username']);
                    return true;
                }
            }
        }
        $sessionObj->cmsSessionUserUpdate('unknown');
        $sessionObj->cmsSessionStatusUpdate($this->type);
        return false;
    }


    /**
     * Assign data from login form to internal values.
     *
     * This function takes the values for username and password
     * from $_POST and assigns them to the $_SESSION variable.
     * @access private
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
     * Checks the code from the security image.
     *
     * This function compares the security image code with the
     * code present in the current session.
     * @access private
     * @return boolean true if the images are identical
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
     * Check user data from the database.
     *
     * If the data is valid, calls {@link _initUserRights()} in order to
     * initialize the users' rights.
     * @access private
     * @global mixed  Database
     * @global array  Core language
     * @see    _initUserRights()
     * @return boolean true if the user authenticated successfully
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

    /**
     * Initialize user rights.
     *
     * Sets up sorted arrays containing static and dynamic access IDs
     * and stores them in the users' $_SESSION array.
     * @access private
     * @return void
     */
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

		$_SESSION['auth']['static_access_ids'] = array();
		$_SESSION['auth']['dynamic_access_ids'] = array();

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

    /**
     * Check type
     *
     * @global  array   Database
     * @return  boolean    True if valid
     * @todo    Understand this and document:
     *          What exactly is compared here?  What are "group_id"s used for?
     *          Where do "User Groups" apply? Is the function name meaningful?
     * @todo    Is this method used *anywhere*? - I couldn't find a single
     *          reference!
     */
    function checkType()
    {
        global $objDatabase;

        if ($_SESSION['auth']['is_admin']==1)
            return true;
        $arrUserGroups = $_SESSION['auth']['groups'];
        if (is_array($arrUserGroups)) {
            $objResult = $objDatabase->Execute("SELECT group_id FROM ".DBPREFIX."access_user_groups WHERE type='$this->type' OR type='backend'");
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


    /**
     * Returns the full name for the given User ID.
     *
     * @static
     * @param   integer $id             The User ID
     * @global  mixed   $objDatabase    Database object
     * @return  string                  The users' full name, or false on
     *                                  failure, or if the user doesn't exist.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getFullName($id)
    {
        global $objDatabase;

        $query = "
            SELECT firstname, lastname
              FROM ".DBPREFIX."access_users
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return false;
        }
        return
            $objResult->fields['firstname'].' '.
            $objResult->fields['lastname'];
    }


    /**
     * Returns the e-mail address for the given User ID.
     *
     * @static
     * @param   integer $id             The User ID
     * @global  mixed   $objDatabase    Database object
     * @return  string                  The users' e-mail, or false on
     *                                  failure, or if the user doesn't exist.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    //static
    function getEmail($id)
    {
        global $objDatabase;

        $query = "
            SELECT email
              FROM ".DBPREFIX."access_users
             WHERE id=$id
        ";
        $objResult = $objDatabase->Execute($query);
        if (!$objResult || $objResult->EOF) {
            return false;
        }
        return $objResult->fields['email'];
    }


    /**
     * Returns an array of all user IDs.
     *
     * @static
     * @return  array                   The array of all matching user IDs,
     *                                  or false on failure.
     * @global  mixed   $objDatabase    Database object
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Extend this in order to get IDs of users fulfilling
     *          criteria specified in the parameters, like access
     *          to a certain module.
     */
    //static
    function getUserIdArray() // ($criteria='')
    {
        global $objDatabase;

        $query = "
            SELECT id
              FROM ".DBPREFIX."access_users
        ";// WHERE [criteria]
        $objResult = $objDatabase->Execute($query);
        if (!$objResult) {
echo("Auth::getUserIdArray(): query failed: $query<br />");
            return false;
        }
        $arrUserId = array();
        while (!$objResult->EOF) {
            $arrUserId[] = $objResult->fields['id'];
echo("Auth::getUserIdArray(): ID ".$objResult->fields['id']."<br />");
            $objResult->MoveNext();
        }
        return $arrUserId;
    }


    /**
     * Verfify that the given ID belongs to the current User
     * @static
     * @return  boolean             True if the IDs are equal, false otherwise
     */
    //static
    function isCurrentUserId($id) {
        if (!$_SESSION['auth']['userid'] > 0) {
echo("Auth::isCurrentUserId(id=$id): ERROR: No or invalid current User ID '".$_SESSION['auth']['userid']."'!<br />");
            return false;
        }
        if (!$id > 0) {
echo("Auth::isCurrentUserId(id=$id): ERROR: No or invalid ID given!<br />");
            return false;
        }
        if ($id == $_SESSION['auth']['userid']) {
            return true;
        }
        return false;
    }


    /**
     * Return the current User ID
     * @return  mixed               The User ID, or false if the ID is invalid
     */
    function getUserId() {
        if (!$_SESSION['auth']['userid'] > 0) {
echo("Auth::getUserId(): ERROR: No or invalid current User ID '".$_SESSION['auth']['userid']."'!<br />");
            return false;
        }
        return $_SESSION['auth']['userid'];
    }


    /**
     * Log the user session.
     *
     * Create a log entry in the database containing the users' details.
     * @global mixed  Database
     */
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
                                            http_x_forwarded_for = '".(isset($_SESSION['HTTP_X_FORWARDED_FOR']) ? strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']) : '')."',
                                            http_via = '".(isset($_SERVER['HTTP_VIA']) ? strip_tags($_SERVER['HTTP_VIA']) : '')."',
                                            http_client_ip = '".(isset($_SERVER['HTTP_CLIENT_IP']) ? strip_tags($_SERVER['HTTP_CLIENT_IP']) : '')."',
                                            referer ='".$referer."'");
            $_SESSION['auth']['log']=true;
        }
    }

    /**
     * Log out the user.
     *
     * Clears the authentication and redirects the browser to the home page.
     */
    function logout()
    {
        if (isset($_SESSION['auth'])) {
            unset($_SESSION['auth']);
            session_destroy();
        }

        if ($this->type=='public') {
            header('Location: index.php?section=login');
        } else {
            header('Location: ../index.php');
        }
        exit;
    }

    /**
    * Returns the users' login name and a logout link, if applicable,
    * or an empty string.
    * @global  mixed   Session object
    * @global  array   Core language
    * @return  string  The username followed by a "logout" link,
    *                  or "" (empty string)
    */
    function status()
    {
        global $sessionObj, $_CORELANG;

        if (!isset($_SESSION['auth']['username'])) {
            return "";
        } else {
            return $_CORELANG['TXT_LOGGED_IN_AS']." ".
                $_SESSION['auth']['username'].
                " (<a href='index.php?section=logout' title='logout'>logout</a>)";
        }
    }

    /**
    * Sends an email with instructions on how to reset the password of a
    * user account.
    * @access public
    * @param  mixed  $objTemplate Template
    * @global mixed  Database
    * @global array  Core language
    * @global array  Configuration
    */
    function lostPassword(&$objTemplate)
    {
        global $objDatabase, $_CORELANG, $_CONFIG;

        // set language variables
        $objTemplate->setVariable(array(
            'TXT_LOST_PASSWORD_TEXT'    => $_CORELANG['TXT_LOST_PASSWORD_TEXT'],
            'TXT_EMAIL'                    => $_CORELANG['TXT_EMAIL'],
            'TXT_RESET_PASSWORD'        => $_CORELANG['TXT_RESET_PASSWORD']
        ));

        if (isset($_POST['restore_pw'])) {
            $email = contrexx_addslashes($_POST['email']);

            // check if the user account exists
            $objResult = $objDatabase->Execute("SELECT username, password FROM ".DBPREFIX."access_users WHERE active=1 AND email='".$email."'");
            if ($objResult !== false) {
                if ($objResult->RecordCount() == 1) {
                    $restoreKey = md5($objResult->fields['username'].$objResult->fields['password'].time());

                    if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET restore_key='".$restoreKey."', restore_key_time='".(time()+3600)."' WHERE email='".$email."'") !== false) {
                        $sendto = $email;
                        $subject = $_SERVER['SERVER_NAME'].": ".$_CORELANG['TXT_RESET_PASSWORD'];

                        if ($this->type == "frontend") {
                            $restorLink = strtolower(ASCMS_PROTOCOL)."://".$_SERVER['SERVER_NAME'].ASCMS_PATH_OFFSET."/index.php?section=login&cmd=resetpw&username=".$objResult->fields['username']."&restoreKey=".$restoreKey;
                        } else {
                            $restorLink = strtolower(ASCMS_PROTOCOL)."://".$_SERVER['SERVER_NAME'].ASCMS_PATH_OFFSET.ASCMS_BACKEND_PATH."/index.php?cmd=resetpw&username=".$objResult->fields['username']."&restoreKey=".$restoreKey;
                        }

                        $message = str_replace(array("%USERNAME%", "%URL%", "%SENDER%"), array($objResult->fields['username'], $restorLink, $_CONFIG['coreAdminName']), $_CORELANG['TXT_RESTORE_PASSWORD_MAIL']);

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
                            $statusMessage = str_replace("%EMAIL%", $email, $_CORELANG['TXT_LOST_PASSWORD_MAIL_SENT']);
                            if ($objTemplate->blockExists('login_lost_password')) {
                                $objTemplate->hideBlock('login_lost_password');
                            }
                        } else {
                            $statusMessage = str_replace("%EMAIL%", $email, $_CORELANG['TXT_EMAIL_NOT_SENT']);
                        }
                    } else {
                        $statusMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
                    }
                } else {
                    $statusMessage = $_CORELANG['TXT_ACCOUNT_WITH_EMAIL_DOES_NOT_EXIST']."<br />";
                }
            } else {
                $statusMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }

            $objTemplate->setVariable(array(
                'LOGIN_STATUS_MESSAGE'        => $statusMessage
            ));
        }
    }

    /**
    * Reset the password of the user using a reset form.
    * @access  public
    * @param   mixed  $objTemplate Template
    * @global  mixed  Database
    * @global  array  Core language
    */
    function resetPassword(&$objTemplate)
    {
        global $objDatabase, $_CORELANG;

        $_POST['username'] = isset($_POST['username']) ? contrexx_strip_tags($_POST['username']) : (isset($_GET['username']) ? contrexx_strip_tags($_GET['username']) : "");
        $_POST['restore_key'] = isset($_POST['restore_key']) ? contrexx_strip_tags($_POST['restore_key']) : (isset($_GET['restoreKey']) ? contrexx_strip_tags($_GET['restoreKey']) : "");
        $_POST['password'] = isset($_POST['password']) ? contrexx_strip_tags($_POST['password']) : "";
        $_POST['password2'] = isset($_POST['password2']) ? contrexx_strip_tags($_POST['password2']) : "";

        $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."access_users WHERE username='".$_POST['username']."' AND restore_key!='' AND restore_key='".$_POST['restore_key']."' AND restore_key_time>='".time()."' AND active=1");
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 1) {
                if (isset($_POST['reset_password'])) {
                    if (strlen($_POST['password'])<6) {
                        $statusMessage .= $_CORELANG['TXT_INVALID_PASSWORD']."<br />";
                    } elseif ($_POST['username'] == $_POST['password']) {
                        $statusMessage .= $_CORELANG['TXT_PASSWORD_NOT_USERNAME_TEXT']."<br />";
                    } elseif ($_POST['password'] != $_POST['password2']) {
                        $statusMessage .= $_CORELANG['TXT_PW_DONOT_MATCH']."<br />";
                    } else {
                        $password = md5($_POST['password']);
                        if ($objDatabase->Execute("UPDATE ".DBPREFIX."access_users SET password='".$password."', restore_key='', restore_key_time='' WHERE username='".$_POST['username']."' AND restore_key='".$_POST['restore_key']."' AND active=1") !== false) {
                            $statusMessage = $_CORELANG['TXT_PASSWORD_CHANGED_SUCCESSFULLY'];
                            if ($objTemplate->blockExists('login_reset_password')) {
                                $objTemplate->hideBlock('login_reset_password');
                            }
                        } else {
                            $statusMessage = $_CORELANG['TXT_DATEBASE_QUERY_ERROR'];
                        }
                    }
                }
            } else {
                if ($objTemplate->blockExists('login_reset_password')) {
                    $objTemplate->hideBlock('login_reset_password');
                }

                $statusMessage = $_CORELANG['TXT_INVALID_USER_ACCOUNT'];
            }

            $objTemplate->setVariable(array(
                'TXT_USERNAME'                => $_CORELANG['TXT_USERNAME'],
                'TXT_PASSWORD'                => $_CORELANG['TXT_PASSWORD'],
                'TXT_VERIFY_PASSWORD'        => $_CORELANG['TXT_VERIFY_PASSWORD'],
                'TXT_PASSWORD_MINIMAL_CHARACTERS'    => $_CORELANG['TXT_PASSWORD_MINIMAL_CHARACTERS'],
                'TXT_SET_PASSWORD_TEXT'        => $_CORELANG['TXT_SET_PASSWORD_TEXT'],
                'TXT_SET_NEW_PASSWORD'        => $_CORELANG['TXT_SET_NEW_PASSWORD'],
            ));

        } else {
            $statusMessage = $_CORELANG['TXT_DATEBASE_QUERY_ERROR'];
        }
        $objTemplate->setVariable(array(
            'LOGIN_STATUS_MESSAGE'    => $statusMessage,
            'LOGIN_USERNAME'        => $_POST['username'],
            'LOGIN_RESTORE_KEY'        => $_POST['restore_key']
        ));
    }

    /**
    * Returns a textual error message.
    *
    * Concatenates all messages stored in the {@link errorMessage} array,
    * with an additional line break tag appended after each message.
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
