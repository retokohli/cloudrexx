<?php
/**
 * Forum home content
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_forum
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/forum/lib/forumLib.class.php';

/**
 * Forum home content
 *
 * Show Forum Block Content
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_forum
 */
class ForumHomeContent extends ForumLibrary {
	
	var $_pageContent;
	var $_objTpl;
	
	/**
	 * Constructor php5
	 */
	function __construct($pageContent) {
		global $_LANGID;
	    $this->_pageContent = $pageContent;
	    $this->_objTpl = &new HTML_Template_Sigma('.');
	    $this->_intLangId = $_LANGID; 
		$this->_arrSettings = $this->createSettingsArray();
	}

	/**
	 * Constructor php4
	 */
    function ForumHomeContent($pageContent) {
    	$this->__construct($pageContent);    	
	}
	
	/**
	 * Fetch latest entries and parse forumtemplate
	 *
	 * @return string parsed latest entries
	 */
	function getContent()
	{
		global $_CONFIG, $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->_pageContent,true,true);
		$this->_showLatestEntries($this->_getLastestEntries());			
		return $this->_objTpl->get();
	}
}
