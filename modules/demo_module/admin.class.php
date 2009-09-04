<?php
/**
 * Demo module
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_demo
 * @todo        Edit PHP DocBlocks!
 */
class demoModule
{
	/**
	* Template object
	*
	* @access private
	* @var object
	*/
	var $_objTpl;

	/**
	* Page title
	*
	* @access private
	* @var string
	*/
	var $_pageTitle;

	/**
	* Status message
	*
	* @access private
	* @var string
	*/
	var $_statusMessage = '';

	/**
	* Constructor
	*/
	function demoModule()
	{
		$this->__construct();
	}

	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct()
	{
		global $objTemplate, $_ARRAYLANG;

		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/demo_module/template');
        CSRF::add_placeholder($this->_objTpl);
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

    	$objTemplate->setVariable("CONTENT_NAVIGATION", "	<a href='index.php?cmd=gallery'>".$_ARRAYLANG['TXT_OVERVIEW']."</a>");
	}

	/**
	* Set the backend page
	*
	* @access public
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function getPage()
	{
		global $objTemplate, $_ARRAYLANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];

		$this->_objTpl->loadTemplateFile('module_demoModule_overview.html');
		$this->_objTpl->setVariable('TXT_WELCOME_MSG', $_ARRAYLANG['TXT_WELCOME_MSG']);

		$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_pageTitle,
			'CONTENT_STATUS_MESSAGE'	=> $this->_statusMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
	}
}
?>
