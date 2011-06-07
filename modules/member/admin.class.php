<?php
/**
 * Member manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_member
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Member manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_member
 */
class MemberManager
{
	var $_objTpl;

	/**
	 * PHP 5 Constructor
	 */
	function __construct()
	{
		$this->_objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/member/template');
        CSRF::add_placeholder($this->_objTpl);
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

		$objTemplate->setVariable("CONTENT_NAVIGATION", "	<a href='index.php?cmd=contact' title=".$_ARRAYLANG['TXT_CONTACT_CONTACT_FORMS'].">".$_ARRAYLANG['TXT_CONTACT_CONTACT_FORMS']."</a>
    														<a href='index.php?cmd=contact&amp;act=settings' title=".$_ARRAYLANG['TXT_CONTACT_SETTINGS'].">".$_ARRAYLANG['TXT_CONTACT_SETTINGS']."</a>");
	}

	function MemberManager()
	{
		$this->__construct();
	}


}


?>
