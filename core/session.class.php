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
	var $userId;
	var $lifetime;
	var $_objDb;
	var $compatibelitiyMode;

	function cmsSession($status="")
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
	        $this->compatibelitiyMode = ($arrColumns = $this->_objDb->MetaColumnNames(DBPREFIX.'sessions')) && in_array('username', $arrColumns);

	        session_start();
        } else {
        	$this->cmsSessionError();
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
		                       VALUES ('".$aKey."', '".time()."', '".time()."', '".($this->status)."', '".($this->userId)."', '')";
		             if ($this->compatibelitiyMode) {
		             	 $query = "INSERT INTO ".DBPREFIX."sessions (sessionid, startdate, lastupdated, status, username, datavalue)
		                       VALUES ('".$aKey."', '".time()."', '".time()."', '".($this->status)."', '".($this->userId)."', '')";
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

}
?>