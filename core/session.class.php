<?php
/**
 * Module Session
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Leandro Nery <nery@astalavista.com>
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @version	   $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Session
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Leandro Nery <nery@astalavista.com>
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>
 * @version	   $Id:    Exp $
 * @package     contrexx
 * @subpackage  core
 */
class cmsSession
{
	var $sessionid;
	var $status;
	var $username;
	var $lifetime;
	var $_objDb;

	function cmsSession($status="")
	{
		global $_CONFIG;

		if (intval($_CONFIG['sessionLifeTime'])==0 || empty($_CONFIG['sessionLifeTime'])){
			$this->lifetime=3600;
		}  else {
			$this->lifetime=intval($_CONFIG['sessionLifeTime']);
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
	       $query = "SELECT datavalue, username, status FROM ".DBPREFIX."sessions WHERE sessionid='".$aKey."'";
	       $objResult = $this->_objDb->Execute($query);

	       if ($objResult !== false) {
		       if ($objResult->RecordCount() == 1) {
		      	     $this->username=$objResult->fields['username'];
		      	     $this->status=$objResult->fields['status'];
		             return $objResult->fields['datavalue'];
		       } else {
		             $query = "INSERT INTO ".DBPREFIX."sessions (sessionid, startdate, lastupdated, status, username, datavalue)
		                       VALUES ('".$aKey."', ROUND(NOW()+0), ROUND(NOW()+0), '".($this->status)."', '".($this->username)."', '')";
		             $this->_objDb->Execute($query);
		             return "";
		       }
	       }
	}

	function cmsSessionWrite( $aKey, $aVal )
	{
	       $aVal = addslashes( $aVal );
	       $query = "UPDATE ".DBPREFIX."sessions SET datavalue = '".$aVal."', lastupdated = ROUND(NOW()+0)  WHERE sessionid = '".$aKey."'";
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
	       $query = "DELETE FROM ".DBPREFIX."sessions WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(lastupdated) > ".$aMaxLifeTime;
	       $this->_objDb->Execute($query);
	       return true;
	}

	function cmsSessionUserUpdate($username="") {
		   $this->username=$username;
	       $query = "UPDATE ".DBPREFIX."sessions SET username ='".$username."' WHERE sessionid = '".$this->sessionid."'";
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