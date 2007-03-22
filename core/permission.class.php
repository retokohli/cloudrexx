<?php
/**
 * Permission
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Permission
 *
 * Checks the permission of the public and backend cms
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
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
}
?>