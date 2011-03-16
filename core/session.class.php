<?php
/**
 * Module Session
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Leandro Nery <nery@astalavista.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version	   $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';

/**
 * Session
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Leandro Nery <nery@astalavista.com>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version	   $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 */
class cmsSession
{
	var $sessionid;
	var $status;
    var $sessionPath;
    var $sessionPathPrefix = 'session_';
	var $userId;
	var $lifetime;
	var $_objDb;
	var $compatibelitiyMode;

	function __construct($status='')
	{
		global $_CONFIG;

		if (intval($_CONFIG['sessionLifeTime'])==0 || empty($_CONFIG['sessionLifeTime'])){
			$this->lifetime=3600;
		}  else {
			$this->lifetime=intval($_CONFIG['sessionLifeTime']);
		}

		if (ini_get("session.auto_start")) {
            session_destroy();
		}

		if (session_set_save_handler(
        	array(& $this, 'cmsSessionOpen'),
        	array(& $this, 'cmsSessionClose'),
        	array(& $this, 'cmsSessionRead'),
        	array(& $this, 'cmsSessionWrite'),
        	array(& $this, 'cmsSessionDestroy'),
        	array(& $this, 'cmsSessionGc')))
        {
	       	$this->status=$status;
	       	$errorMsg = '';
	        $this->_objDb = getDatabaseObject($errorMsg, true);
            if (DBG::getMode() & DBG_ADODB_TRACE) {
                $this->_objDb->debug=99;
            } elseif (DBG::getMode() & DBG_ADODB || DBG::getMode() & DBG_ADODB_ERROR) {
                $this->_objDb->debug=1;
            } else {
                $this->_objDb->debug=0;
            }
	        $this->compatibelitiyMode = ($arrColumns = $this->_objDb->MetaColumnNames(DBPREFIX.'sessions')) && in_array('username', $arrColumns);

// TODO: there should be an option to limit the session to the browser's session
            @ini_set('session.gc_maxlifetime', $this->lifetime);
	        session_start();
            $this->cmsSessionExpand();
	    }
        else {
        	$this->cmsSessionError();
        }
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
            setcookie($ses, $_COOKIE[$ses], $expirationTime, "/");
        }
    }

	function cmsSessionOpen($aSavaPath, $aSessionName)
	{
	       $this->cmsSessionGc($this->lifetime);
	       return true;
	}

	function cmsSessionClose()
	{
	       return true;
	}

	function cmsSessionRead( $aKey )
	{
		   $this->sessionid=$aKey;
           $this->sessionPath = ASCMS_TEMP_PATH.'/'.$this->sessionPathPrefix.$this->sessionid;
	       $query = "SELECT datavalue, user_id, status FROM ".DBPREFIX."sessions WHERE sessionid='".$aKey."'";
	       if ($this->compatibelitiyMode) {
			$query = "SELECT datavalue, username as user_id, status FROM ".DBPREFIX."sessions WHERE sessionid='".$aKey."'";
	       }
	       $objResult = $this->_objDb->Execute($query);

	       if ($objResult !== false) {
		       if ($objResult->RecordCount() == 1) {
		      	     $this->userId=$objResult->fields['user_id'];
		      	     $this->status=$objResult->fields['status'];
		             return $objResult->fields['datavalue'];
		       } else {
		             $query = "INSERT INTO ".DBPREFIX."sessions (sessionid, startdate, lastupdated, status, user_id, datavalue)
                               VALUES ('".$aKey."', '".time()."', '".time()."', '".($this->status)."', '".intval($this->userId)."', '')";
		             if ($this->compatibelitiyMode) {
		             	 $query = "INSERT INTO ".DBPREFIX."sessions (sessionid, startdate, lastupdated, status, username, datavalue)
                               VALUES ('".$aKey."', '".time()."', '".time()."', '".($this->status)."', '".intval($this->userId)."', '')";
		             }
		             $this->_objDb->Execute($query);
		             return "";
		       }
	       }
	}

	function cmsSessionWrite( $aKey, $aVal )
	{
		$aVal = addslashes( $aVal );
		$query = "UPDATE ".DBPREFIX."sessions SET datavalue = '".$aVal."', lastupdated = '".time()."' WHERE sessionid = '".$aKey."'";
		$this->_objDb->Execute($query);
	   return true;
	}

	function cmsSessionDestroy( $aKey )
	{
	       $query = "DELETE FROM ".DBPREFIX."sessions WHERE sessionid = '".$aKey."'";
	       $this->_objDb->Execute($query);

           if (file_exists($this->sessionPath)) {
                $objFile = new File();
                $objFile->delDir(ASCMS_TEMP_PATH, ASCMS_TEMP_WEB_PATH, '/'.$this->sessionPathPrefix.$this->sessionid);
           }

	       return true;
	}

	function cmsSessionGc( $aMaxLifeTime )
	{
		   if (empty($aMaxLifeTime)) return true;
	       $query = "DELETE FROM ".DBPREFIX."sessions WHERE lastupdated < ".(time() - $aMaxLifeTime);
	       $this->_objDb->Execute($query);
	       return true;
	}

	function cmsSessionUserUpdate($userId=0)
	{
		   $this->userId=$userId;
	       $query = "UPDATE ".DBPREFIX."sessions SET user_id ='".$userId."' WHERE sessionid = '".$this->sessionid."'";
	       if ($this->compatibelitiyMode) {
	       	$query = "UPDATE ".DBPREFIX."sessions SET username ='".$userId."' WHERE sessionid = '".$this->sessionid."'";
	       }
	       $this->_objDb->Execute($query);
	       return true;
	}

	function cmsSessionStatusUpdate($status="")
	{
		   $this->status=$status;
	       $query = "UPDATE ".DBPREFIX."sessions SET status ='".$status."' WHERE sessionid = '".$this->sessionid."'";
	       $this->_objDb->Execute($query);
	       return true;
	}

	function cmsSessionError() {
        die ("Session Handler Error");
    }

    function getTempPath()
    {
        $this->cleanTempPaths();

        $objFile = new File();

        if (!is_dir($this->sessionPath) && !$objFile->mkdir(ASCMS_TEMP_PATH, ASCMS_TEMP_WEB_PATH, '/'.$this->sessionPathPrefix.$this->sessionid)) {
            return false;
        }

        if (!is_writable($this->sessionPath) && !$objFile->setChmod(ASCMS_TEMP_PATH, ASCMS_TEMP_WEB_PATH, '/'.$this->sessionPathPrefix.$this->sessionid)) {
            return false;
        }

        return $this->sessionPath;
    }

    public function cleanTempPaths()
    {
        $dirs = array();
        if ($dh = opendir(ASCMS_TEMP_PATH)) {
            while (($file = readdir($dh)) !== false) {
                if (is_dir(ASCMS_TEMP_PATH.'/'.$file)) {
                    $dirs[] = $file;
                }
            }
            closedir($dh);
        }

        $sessionPaths = preg_grep('#^'.$this->sessionPathPrefix.'[0-9A-F]{32}$#i', $dirs);
        $sessions = array();
        $query = 'SELECT `sessionid` FROM `'.DBPREFIX.'sessions`';
        $objResult = $this->_objDb->Execute($query);
        while (!$objResult->EOF) {
            $sessions[] = $objResult->fields['sessionid'];
            $objResult->MoveNext();
        }

        foreach ($sessionPaths as $sessionPath) {
            if (!in_array(substr($sessionPath, strlen($this->sessionPathPrefix)), $sessions)) {
                if (!isset($objFile)) {
                    $objFile = new File();
                }

                $objFile->delDir(ASCMS_TEMP_PATH, ASCMS_TEMP_WEB_PATH, '/'.$sessionPath);
            }
        }
    }
}
?>
