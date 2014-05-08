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

    public static $instance;
    
    public $sessionid;
    public $status;
    public $userId;
    
    private $sessionPath;    
    private $sessionPathPrefix = 'session_';    
    private $lifetime;
    private $defaultLifetime;
    private $defaultLifetimeRememberMe;
    private $rememberMe = false;
    private $discardChanges = false;
    
    private $locks = array();
    private $sessionLockTime = 10;
    
    /*
     * Get instance of the class from the out side world
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {            
            self::$instance = new static(null, null, true);
            $_SESSION = self::$instance;
            
            // read the session data
             $_SESSION->readData();
            
            //earliest possible point to set debugging according to session.
            $_SESSION->restoreDebuggingParams();

            $_SESSION->cmsSessionExpand();
        }
        
        return self::$instance;
    }

    /**
     * Default object constructor.
     *
     * @param array   $data         array of data to set into the session array
     * @param string  $path         Current position of the array(path) string
     * @param boolean $initSession  intialize the session or not 
     */    
    protected function __construct($data = array(), $path = '', $initSession = false)
    {        

        // initialize the session on start up
        if ($initSession) {
            if (ini_get('session.auto_start')) {
                session_destroy();
            }
            
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
        } else {
            // BEGIN Array implementations 
            $this->arrayPath = $path;
            
            if (is_array($data)) {
                foreach ($data as $key => $value) {                    
                    $this[$key] = $value;
                }
            }            
            // END Array implementations 
        }
    }
    
    function readData() {        
        
        $query = "SELECT 
                    `variable_key`,
                    `variable_value`,
                    `lastused`
                  FROM 
                    `". DBPREFIX ."session_variable` 
                  WHERE 
                    `sessionid` = '{$this->sessionid}' 
                  ";
        $objResult = \Env::get('db')->Execute($query);
                
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
            \Env::get('db')->debug = 99;
        } elseif (DBG::getMode() & DBG_ADODB || DBG::getMode() & DBG_ADODB_ERROR) {
            \Env::get('db')->debug = 1;
        } else {
            \Env::get('db')->debug = 0;
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
                $objResult = \Env::get('db')->Execute('UPDATE `' . DBPREFIX . 'sessions` SET `remember_me` = 1 WHERE `sessionid` = "' . contrexx_input2db($sessionId) . '"');
            }
        } else {
            $objResult = \Env::get('db')->Execute('SELECT `remember_me` FROM `' . DBPREFIX . 'sessions` WHERE `sessionid` = "' . contrexx_input2db($sessionId) . '"');
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
        $objResult = \Env::get('db')->Execute('SELECT 1 FROM `' . DBPREFIX . 'sessions` WHERE `sessionid` = "' . contrexx_input2db($sessionId) . '"');
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
        // release all locks
        if (!empty($_SESSION->locks)) {
            foreach ($_SESSION->locks as $key => $value) {
                $this->releaseLock($key);
            }
        }
        
        return true;
    }

    function cmsSessionRead( $aKey )
    {        
        
        $this->sessionid = $aKey;        
        $this->sessionPath = ASCMS_TEMP_WEB_PATH . '/' . $this->sessionPathPrefix . $this->sessionid;
        
        $objResult = \Env::get('db')->Execute('SELECT `user_id`, `status` FROM `' . DBPREFIX . 'sessions` WHERE `sessionid` = "' . $aKey . '"');
        if ($objResult !== false) {
            if ($objResult->RecordCount() == 1) {
                $this->userId = $objResult->fields['user_id'];
                $this->status = $objResult->fields['status'];
            } else {
                \Env::get('db')->Execute('
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
        \Env::get('db')->debug = 0;

        \Env::get('db')->Execute($query);
        return true;
    }

    function cmsSessionDestroy($aKey, $destroyCookie = true) {          
        $query = "DELETE FROM " . DBPREFIX . "sessions WHERE sessionid = '" . $aKey . "'";
        \Env::get('db')->Execute($query);

        $query = "DELETE FROM " . DBPREFIX . "session_variable WHERE sessionid = '" . $aKey . "'";
        \Env::get('db')->Execute($query);

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
        $objResult = \Env::get('db')->Execute('SELECT `sessionid` FROM `' . DBPREFIX . 'sessions` WHERE `user_id` = ' . intval($userId));
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
        \Env::get('db')->Execute('DELETE FROM `' . DBPREFIX . 'sessions` WHERE ((`remember_me` = 0) AND (`lastupdated` < ' . (time() - $this->defaultLifetime) . '))');
        \Env::get('db')->Execute('DELETE FROM `' . DBPREFIX . 'sessions` WHERE ((`remember_me` = 1) AND (`lastupdated` < ' . (time() - $this->defaultLifetimeRememberMe) . '))');
        return true;
    }

    function cmsSessionUserUpdate($userId=0)
    {
        $this->userId = $userId;
        \Env::get('db')->Execute('UPDATE `' . DBPREFIX . 'sessions` SET `user_id` = ' . $userId . ' WHERE `sessionid` = "' . $this->sessionid . '"');
        return true;
    }

    function cmsSessionStatusUpdate($status = "") {
        $this->status = $status;
        $query = "UPDATE " . DBPREFIX . "sessions SET status ='" . $status . "' WHERE sessionid = '" . $this->sessionid . "'";
        \Env::get('db')->Execute($query);
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
        $objResult = \Env::get('db')->Execute($query);
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
    
    static function getLockName($key)
    {
        global $_DBCONFIG;
        
        return $_DBCONFIG['database'].DBPREFIX."sessions_".$_SESSION->sessionid.'_'.$key;
    }

    public function getLock($lockName, $lifeTime = 60)
    {
        $objLock = \Env::get('db')->Execute('SELECT GET_LOCK("' . $lockName . '", ' . $lifeTime . ')');

        if (!$objLock || $objLock->fields['GET_LOCK("' . $lockName . '", ' . $lifeTime . ')'] != 1) {
            die('Could not obtain session lock!');
        }     
    }
    
    public function releaseLock($key)
    {
        unset($_SESSION->locks[$key]);
        \Env::get('db')->Execute('SELECT RELEASE_LOCK("' . self::getLockName($key) . '")');
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
    
    public function offsetGet($offset) {
        
        if (isset($this->data[$offset])) {
            
            if ($this->arrayPath && !empty($this->arrayPath)) {
                list($lockKey) = explode('/', $this->arrayPath);
            } else {
                $lockKey = $offset;
            }
            if (!isset($_SESSION->locks[$lockKey])) {

                $_SESSION->locks[$lockKey] = 1;
                $this->getLock(self::getLockName($lockKey), $this->sessionLockTime);

                $query = "SELECT
                            `sessionid`,
                            `lastused`,
                            `variable_value`
                          FROM
                            `" . DBPREFIX . "session_variable`
                          WHERE
                            `variable_key` = '$offset'
                          AND
                            `sessionid`   = '{$_SESSION->sessionid}'";
                $objResult = \Env::get('db')->SelectLimit($query, 1);

                if ($objResult && $objResult->RecordCount() > 0) {
                    return unserialize($objResult->fields['variable_value']);
                }
            }
            
            return $this->data[$offset];
        } else {
            return null;
        }
        
    }
    
    public function offsetSet($offset, $data) {              
        
        parent::offsetSet($offset, $data);
        
        // Don't write session data to databse.
        // This is used to prevent an unwanted session overwrite by a continuous
        // script request (javascript) that only checks for a certain event to happen.
        if ($_SESSION->discardChanges) return true;
        
        if (!empty($this->arrayPath)) {
            list($offset) = explode('/', $this->arrayPath);
        }
                
        $sessionVar = contrexx_raw2db($offset);
        $sessionVal = contrexx_raw2db(serialize($_SESSION[$offset]));

        if (!isset($_SESSION->locks[$offset])) {
            $_SESSION->locks[$offset] = 1;
            $this->getLock(self::getLockName($offset), $this->sessionLockTime);
        }
        
        $query = "REPLACE INTO 
                     `" . DBPREFIX . "session_variable` 
                   SET
                    `sessionid`   = '{$_SESSION->sessionid}',
                    `variable_key` = '$sessionVar',
                    `variable_value` = '$sessionVal'";
        
        \Env::get('db')->Execute($query);
    }
}
