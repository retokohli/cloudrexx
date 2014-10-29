<?php
/**
 * Index Class CRM
 *
 * @category   Crm
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */

/**
 * Index Class CRM
 *
 * @category   Crm
 * @package    contrexx
 * @subpackage module_crm
 * @author     SoftSolutions4U Development Team <info@softsolutions4u.com>
 * @copyright  2012 and CONTREXX CMS - COMVATION AG
 * @license    trial license
 * @link       www.contrexx.com
 */
class Crm
{
	/**
	* Template object
	*
	* @access private
	* @var object
	*/
	var $_objTpl;

	/**
	* Module Name
	*
	* @access private
	* @var object
	*/
        var $moduleName = 'crm';
	
	/**
	* Constructor
         *
         * @param string $pageContent page content
	*/
	function Crm($pageContent)
	{

		$this->__construct($pageContent);
	}
	
	/**
	* PHP5 constructor
	*
        * @param string $pageContent page content
        *
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct($pageContent)
	{
        //$this->_intLanguageId = intval($_LANGID);
        $this->_objTpl = new HTML_Template_Sigma('.');
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
