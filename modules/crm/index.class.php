<?php
/**
* Class CRM
*
* Crm class
*
* @copyright	CONTREXX CMS
* @author		SoftSolutions4U Development Team <info@softsolutions4u.com>
* @module		CRM
* @modulegroup	modules
* @access		public
* @version		1.0.0
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
    var $moduleName = 'crm';
	
	/**
	* Constructor
	*/
	function Crm($pageContent)
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
        //$this->_intLanguageId = intval($_LANGID);
	    $this->_objTpl = &new HTML_Template_Sigma('.');
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
	    if(isset($_GET['cmd'])) {
            $action=$_GET['cmd'];
        } elseif(isset($_GET['act'])) {
            $action=$_GET['act'];
        } else {
            $action='';
        }
         switch ($action) {
            default:
                $this->userAuthendicate();
                $this->showOverview();
                break;
        }
 		return $this->_objTpl->get();
	}
}
?>
