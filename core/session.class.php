<?php

/**
 * Module Session
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Leandro Nery <nery@astalavista.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

use \Cx\Core\Model\RecursiveArrayAccess as RecursiveArrayAccess;

/**
 * Session
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Leandro Nery <nery@astalavista.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 */
class cmsSession extends RecursiveArrayAccess {

    var $sessionid;
    var $status;
    private $sessionPath;
    private $sessionLock;
    private $sessionLockTime = 10;
    private $sessionPathPrefix = 'session_';
    var $userId;
    var $_objDb;
    private $lifetime;
    private $defaultLifetime;
    private $defaultLifetimeRememberMe;
    private $rememberMe = false;
    private $discardChanges = false;
    
    public static $instance;
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $class = __CLASS__;
            self::$instance = new $class();
            $_SESSION = self::$instance;
            
            // read the session data
            $_SESSION->readData();
            
            //earliest possible point to set debugging according to session.
            $_SESSION->restoreDebuggingParams();

            $_SESSION->cmsSessionExpand();
        }
        
        return self::$instance;
    }

    protected function __construct($status='')
    {        

        if (ini_get('session.auto_start')) {
            session_destroy();
        }
                
        $this->status = $status;
        $this->initDatabase();
        $this->initRememberMe();
        $this->initSessionLifetime();

        if (session_set_save_handler(
            array(& $this, 'cmsSessionOpen'),
            array(& $this, 'cmsSessionClose'),
            array(& $this, 'cmsSessionRead'),
            array(& $this, 'cmsSessionWrite'),
            array(& $this, 'cmsSessionDestroy'),
            array(& $this, 'cmsSessionGc')))
        {
            session_start();
            
        } else {
            $this->cmsSessionError();
        }
    }
    
    function __destruct() {
        // Don't write session data to databse.
        // This is used to prevent an unwanted session overwrite by a continuous
        // script request (javascript) that only checks for a certain event to happen.
        if ($this->discardChanges) return true;
    
        $rows = array();
        foreach ($this->data as $k => $v) {
            $sessionVar = contrexx_raw2db($k);
            $sessionVal = contrexx_raw2db(serialize($v));
        
            $rows[] = "('$this->sessionid', '$sessionVar', '$sessionVal')";
        }
        
        if (!empty($rows)) {
            $strRows = implode(', ', $rows);
            $query = "INSERT INTO 
                    `" . DBPREFIX . "session_variable` 
                    (`sessionid`, `variable_key`, `variable_value`) 
                  VALUES 
                        $strRows 
                      ON DUPLICATE KEY 
                        UPDATE 
                            `variable_value` = VALUES(variable_value)";

            $this->_objDb->Execute($query);
        }
    }
        
    function readData() {        
        
        $query = "SELECT 
                    `variable_key`,
                    `variable_value` 
                  FROM 
                    `". DBPREFIX ."session_variable` 
                  WHERE 
                    `sessionid` = '{$this->sessionid}' 
                  ";
        $objResult = $this->_objDb->Execute($query);
                
        if ($objResult && $objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $this->data[$objResult->fields['variable_key']] = unserialize($objResult->fields['variable_value']);
                $objResult->MoveNext();
            }            
        }        
    }
    
    /**
     * Initializes the database.
     *
     * @access  private
     */
    private function initDatabase()
    {
        $this->_objDb = \Env::get('db');

        $this->setAdodbDebugMode();
    }

    /**
     * Sets the database debug mode.
     *
     * @access  private
     */
    private function setAdodbDebugMode()
    {
        if (DBG::getMode() & DBG_ADODB_TRACE) {
            $this->_objDb->debug = 99;
        } elseif (DBG::getMode() & DBG_ADODB || DBG::getMode() & DBG_ADODB_ERROR) {
            $this->_objDb->debug = 1;
        } else {
            $this->_objDb->debug = 0;
        }
    }

    /**
     * Expands debugging behaviour with behaviour stored in session if specified and active.
     *
     * @access  private
     */
    private function restoreDebuggingParams()
    {                
        if (isset($_SESSION['debugging']) && $_SESSION['debugging']) {
            DBG::activate(DBG::getMode() | $_SESSION['debugging_flags']);
        }
    }

    /**
     * Initializes the status of remember me.
     *
     * @access  private
     */
    private function initRememberMe()
    {
        $sessionId = !empty($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : null;
        if (isset($_POST['remember_me'])) {
            $this->rememberMe = true;
            if ($this->sessionExists($sessionId)) {//remember me status for new sessions will be stored in cmsSessionRead() (when creating the appropriate db entry)
                $objResult = $this->_objDb->Execute('UPDATE `' . DBPREFIX . 'sessions` SET `remember_me` = 1 WHERE `sessionid` = "' . contrexx_input2db($sessionId) . '"');
            }
        } else {
            $objResult = $this->_objDb->Execute('SELECT `remember_me` FROM `' . DBPREFIX . 'sessions` WHERE `sessionid` = "' . contrexx_input2db($sessionId) . '"');
            if ($objResult && ($objResult->RecordCount() > 0)) {
                if ($objResult->fields['remember_me'] == 1) {
                    $this->rememberMe = true;
                }
            }
        }
    }

    /**
     * Checks if the passed session exists.
     *
     * @access  private
     * @param   string      $session
     * @return  boolean
     */
    private function sessionExists($sessionId) {
        $objResult = $this->_objDb->Execute('SELECT 1 FROM `' . DBPREFIX . 'sessions` WHERE `sessionid` = "' . contrexx_input2db($sessionId) . '"');
        if ($objResult && ($objResult->RecordCount() > 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets the default session lifetimes
     * and lifetime of the current session.
     *
     * @access  private
     */
    private function initSessionLifetime()
    {
        global $_CONFIG;

        $this->defaultLifetime = !empty($_CONFIG['sessionLifeTime']) ? intval($_CONFIG['sessionLifeTime']) : 3600;
        $this->defaultLifetimeRememberMe = !empty($_CONFIG['sessionLifeTimeRememberMe']) ? intval($_CONFIG['sessionLifeTimeRememberMe']) : 1209600;

        if ($this->rememberMe) {
            $this->lifetime = $this->defaultLifetimeRememberMe;
        } else {
            $this->lifetime = $this->defaultLifetime;
        }

        @ini_set('session.gc_maxlifetime', $this->lifetime);
    }

    /**
     * expands a running session by @link Session::lifetime seconds.
     * called on pageload.
     */
    function cmsSessionExpand()
    {
        // Reset the expiration time upon page load
        $ses = session_name();
        if (isset($_COOKIE[$ses])) {
            $expirationTime = ($this->lifetime > 0 ? $this->lifetime + time() : 0);
            setcookie($ses, $_COOKIE[$ses], $expirationTime, '/');
        }
    }

    function cmsSessionOpen($aSavaPath, $aSessionName)
    {
        $this->cmsSessionGc();
        return true;
    }

    function cmsSessionClose()
    {
        // release the lock associated with the current session
        $this->_objDb->Execute('SELECT RELEASE_LOCK("' . $this->sessionLock . '")');

        return true;
    }

    function cmsSessionRead( $aKey )
    {
        global $_DBCONFIG;
        
        $this->sessionid = $aKey;
        $this->sessionPath = ASCMS_TEMP_WEB_PATH . '/' . $this->sessionPathPrefix . $this->sessionid;

        // get the lock name, associated with the current session
        $this->sessionLock = "{$_DBCONFIG['database']}.".DBPREFIX."sessions_{$this->sessionid}";
        $objLock = $this->_objDb->Execute('SELECT GET_LOCK("' . $this->sessionLock . '", ' . $this->sessionLockTime . ')');
        
        if (!$objLock || $objLock->fields['GET_LOCK("' . $this->sessionLock . '", ' . $this->sessionLockTime . ')'] != 1) {
            die('Could not obtain session lock!');
        }
        
        $objResult = $this->_objDb->Execute('SELECT `user_id`, `status` FROM `' . DBPREFIX . 'sessions` WHERE `sessionid` = "' . $aKey . '"');
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 1) {
                $this->userId = $objResult->fields['user_id'];
                $this->status = $objResult->fields['status'];
            } else {
                $this->_objDb->Execute('
                    INSERT INTO `' . DBPREFIX . 'sessions` (`sessionid`, `remember_me`, `startdate`, `lastupdated`, `status`, `user_id`)
                    VALUES ("' . $aKey . '", ' . ($this->rememberMe ? 1 : 0) . ', "' . time() . '", "' . time() . '", "' . $this->status . '", ' . intval($this->userId) . ')
                ');
                return '';
            }
        }

        return '';
    }

    function cmsSessionWrite($aKey, $aVal) {
        // Don't write session data to databse.
        // This is used to prevent an unwanted session overwrite by a continuous
        // script request (javascript) that only checks for a certain event to happen.
        if ($this->discardChanges) return true;
        
        $aVal = addslashes($aVal);
        $query = "UPDATE " . DBPREFIX . "sessions SET lastupdated = '" . time() . "' WHERE sessionid = '" . $aKey . "'";

        // We must deactivate the debugging of the database here,
        // because at this stage the database driver used in DBG
        // or DBG itself has already been deconstructed. So logging
        // an SQL statement at this point will most likely generate
        // a FATAL error.
        $this->_objDb->debug = 0;

        $this->_objDb->Execute($query);
        return true;
    }

    function cmsSessionDestroy($aKey, $destroyCookie = true) {          
        $query = "DELETE FROM " . DBPREFIX . "sessions WHERE sessionid = '" . $aKey . "'";
        $this->_objDb->Execute($query);

        $query = "DELETE FROM " . DBPREFIX . "session_variable WHERE sessionid = '" . $aKey . "'";
        $this->_objDb->Execute($query);

        if (\Cx\Lib\FileSystem\FileSystem::exists($this->sessionPath)) {
            \Cx\Lib\FileSystem\FileSystem::delete_folder($this->sessionPath, true);
        }

        if ($destroyCookie) {
            setcookie("PHPSESSID", '', time() - 3600, '/');
        }
        // do not write the session data
        $this->discardChanges = true;
        
        return true;
    }

    function cmsSessionDestroyByUserId($userId) {
        $objResult = $this->_objDb->Execute('SELECT `sessionid` FROM `' . DBPREFIX . 'sessions` WHERE `user_id` = ' . intval($userId));
        if ($objResult) {
            while (!$objResult->EOF) {
                if ($objResult->fields['sessionid'] != $this->sessionid) {
                    $this->cmsSessionDestroy($objResult->fields['sessionid'], false);
                }
                $objResult->MoveNext();
            }
        }

        return true;
    }

    function cmsSessionGc() {
        $this->_objDb->Execute('DELETE FROM `' . DBPREFIX . 'sessions` WHERE ((`remember_me` = 0) AND (`lastupdated` < ' . (time() - $this->defaultLifetime) . '))');
        $this->_objDb->Execute('DELETE FROM `' . DBPREFIX . 'sessions` WHERE ((`remember_me` = 1) AND (`lastupdated` < ' . (time() - $this->defaultLifetimeRememberMe) . '))');
        return true;
    }

    function cmsSessionUserUpdate($userId=0)
    {
        $this->userId = $userId;
        $this->_objDb->Execute('UPDATE `' . DBPREFIX . 'sessions` SET `user_id` = ' . $userId . ' WHERE `sessionid` = "' . $this->sessionid . '"');
        return true;
    }

    function cmsSessionStatusUpdate($status = "") {
        $this->status = $status;
        $query = "UPDATE " . DBPREFIX . "sessions SET status ='" . $status . "' WHERE sessionid = '" . $this->sessionid . "'";
        $this->_objDb->Execute($query);
        return true;
    }

    function cmsSessionError() {
        die("Session Handler Error");
    }

    public function getTempPath()
    {
        $this->cleanTempPaths();

        if (!\Cx\Lib\FileSystem\FileSystem::make_folder($this->sessionPath)) {
            return false;
        }

        if (!\Cx\Lib\FileSystem\FileSystem::makeWritable($this->sessionPath)) {
            return false;
        }

        return ASCMS_PATH . $this->sessionPath;
    }

    /**
     * Gets a web temp path.
     * This path is needed to work with the File-class from the framework.
     *
     * @return string 
     */
    public function getWebTempPath() {
        $tp = $this->getTempPath();
        if (!$tp)
            return false;
        return $this->sessionPath;
    }

    public function cleanTempPaths() {
        $dirs = array();
        if ($dh = opendir(ASCMS_TEMP_PATH)) {
            while (($file = readdir($dh)) !== false) {
                if (is_dir(ASCMS_TEMP_PATH . '/' . $file)) {
                    $dirs[] = $file;
                }
            }
            closedir($dh);
        }

        // depending on the php setting session.hash_function and session.hash_bits_per_character
        // the length of the session-id varies between 22 and 40 characters.
        $sessionPaths = preg_grep('#^' . $this->sessionPathPrefix . '[0-9A-Z,-]{22,40}$#i', $dirs);
        $sessions = array();
        $query = 'SELECT `sessionid` FROM `' . DBPREFIX . 'sessions`';
        $objResult = $this->_objDb->Execute($query);
        while (!$objResult->EOF) {
            $sessions[] = $objResult->fields['sessionid'];
            $objResult->MoveNext();
        }

        foreach ($sessionPaths as $sessionPath) {
            if (!in_array(substr($sessionPath, strlen($this->sessionPathPrefix)), $sessions)) {
                \Cx\Lib\FileSystem\FileSystem::delete_folder(ASCMS_TEMP_WEB_PATH . '/' . $sessionPath, true);
            }
        }
    }

    /**
     * Discard changes made to the $_SESSION-array.
     *
     * If called, this method causes the session not to store
     * any changes made to the $_SESSION-array to the database.
     * Use this method when doing multiple ajax-requests simultaneously
     * to prevent an unwanted session overwrite.
     */
    public function discardChanges() {
        $this->discardChanges = true;
    }

}
