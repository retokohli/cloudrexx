<?php
/**
 * Permission
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Permission
 *
 * Checks the permission of the public and backend cms
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class Permission
{
	var $type;
	var $allAccess = 0;



	function Permission($permType='frontend') {
		$this->type=$permType;

		if(isset($_SESSION['auth']['is_admin']) && $_SESSION['auth']['is_admin']==1){
			$this->allAccess=1;
		}
	}

	/**
	 * Check access
	 *
	 * Check if the user has the required access id
	 *
	 * @access public
	 * @param integer $accessId
	 * @param string $type
	 * @return boolean
	 */
	function checkAccess($accessId, $type) {
		if ($this->allAccess) {
			return true;
		} elseif (($type == 'static' || $type == 'dynamic') && isset($_SESSION['auth'][$type.'_access_ids']) && !empty($_SESSION['auth'][$type.'_access_ids'])) {
			if (in_array($accessId, $_SESSION['auth'][$type.'_access_ids'])) {
				return true;
			}
		}
		if ($this->type == 'backend') {
			header("Location: index.php?cmd=noaccess");
			exit;
		}
		return false;
	}

	/**
	 * Get static access ids
	 *
	 * Returns an array containing the static access ID's of the initialized user.
	 *
	 * @return array
	 */
	function getStaticAccessIds()
	{
		return $_SESSION['auth']['static_access_ids'];
	}

	/**
	 * Get dynamic access ids
	 *
	 * Returns an array containing the dynamic access ID's of the initialized user.
	 *
	 * @return array
	 */
	function getDynamicAccessIds()
	{
		return $_SESSION['auth']['dynamic_access_ids'];
	}
}
?>