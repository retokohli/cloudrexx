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
	public static function checkAccess($accessId, $type, $return = false)
	{
		$objFWUser = FWUser::getFWUserObject();
		if ($objFWUser->objUser->login() &&
			(
				$objFWUser->objUser->getAdminStatus() ||
				$type == 'static' && in_array($accessId, $objFWUser->objUser->getStaticPermissionIds()) ||
				$type == 'dynamic' && in_array($accessId, $objFWUser->objUser->getDynamicPermissionIds())
			)
		) {
			return true;
		} elseif ($return) {
			return false;
		} else {
			Permission::noAccess();
		}
	}

	public static function hasAllAccess()
	{
		$objFWUser = FWUser::getFWUserObject();
		if ($objFWUser->objUser->login() && $objFWUser->objUser->getAdminStatus()) {
			return true;
		} else {
			return false;
		}
	}

	public static function noAccess()
	{
		header("Location: index.php?cmd=noaccess");
		exit;
	}
}
?>
