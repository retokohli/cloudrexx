<?php
/**
 * Redirect
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Redirect class
 *
 * Check Referer and change Location
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 */
class redirect
{
	var $host;
	var $locations = array();

	/**
    * Constructor
    * @param  string
    * @access public
    */
    function redirect()
    {
    	$this->__construct();
    }



    function __construct()
    {
    	global  $_CORELANG, $objDatabase;

    	if (substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.') {
    		$this->host = strtolower(substr($_SERVER['HTTP_HOST'], 4));

    	} else {
    		$this->host = strtolower($_SERVER['HTTP_HOST']);
    	}

    	$this->getLocations();
    	$this->changeLocation();
    }

    function changeLocation()
    {
    	global  $_CORELANG, $objDatabase;

    	if (empty($_SERVER['argv'])) {
    		if (!empty($this->locations[$this->host])) {
	    		if (!empty($this->locations[$this->host]['lid'])) {
	    			$lid = '&lid='.$this->locations[$this->host]['lid'];
	    		}
	    		if (!empty($this->locations[$this->host]['cid'])) {
	    			$cid = '&cid='.$this->locations[$this->host]['cid'];
	    		}

	    		$newLocation = "?section=directory".$lid.$cid;

	    		CSRF::header('Location: '.$newLocation);
				exit;
	    	}
    	}
    }

    function getLocations()
    {
    	$this->locations['marktplatzbeosued.ch'] = array(
		   	 									'lid' 	=> '',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzbern.ch'] = array(
		   	 									'lid' 	=> '33',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzbiel-bienne.ch'] = array(
		   	 									'lid' 	=> '',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzboedeli.ch'] = array(
		   	 									'lid' 	=> '',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzchur.ch'] = array(
		   	 									'lid' 	=> '',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzemmental.ch'] = array(
		   	 									'lid' 	=> '',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzluzern.ch'] = array(
		   	 									'lid' 	=> '38',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzthun.ch'] = array(
		   	 									'lid' 	=> '52',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzwallis.ch'] = array(
		   	 									'lid' 	=> '49',
		   	 									'cid' 	=> '',
		   	 									);
		$this->locations['marktplatzzuerich.ch'] = array(
		   	 									'lid' 	=> '51',
		   	 									'cid' 	=> '',
		   	 									);
    }
}
?>
