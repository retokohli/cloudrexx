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
	* Constructor
	*/
	function demoModule($pageContent)
	{
		$this->__construct($pageContent);
	}

	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct($pageContent)
	{
	    $this->_objTpl = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
		$this->_objTpl->setTemplate($pageContent);
	}

	/**
	* Get content page
	*
	* @access public
	*/
	function getPage()
	{
		return $this->_objTpl->get();
	}
}
?>
