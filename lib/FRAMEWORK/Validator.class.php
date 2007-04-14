<?php
/**
 * Framework Validator
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.1
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */
class FWValidator
{
	/**
	* Validate an E-mail address
	*
	* @param  string $string
	* @return boolean
	* @access public
	*/
	function isEmail($string)
	{
		if (eregi("^"."[a-z0-9]+([_\\.-][a-z0-9]+)*"	//user
			."@"
			."("
				."([a-z0-9]+([\.-][a-z0-9]+)*)+"		//domain
				."\\.[a-z]{2,4}"  						//sld, tld
			."|localhost)"								// or localhost
			."$", $string)
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Find E-mail addresses in strings
	*
	* @param  string  text with possible email addresses
	* @return array matches email addresses
	* @access public
	*/
	function getEmailAsArray($string)
	{
		preg_match_all("/\s+([_a-zA-Z0-9-]+(?:\\.?[_a-zA-Z0-9-])*@((?:[a-zA-Z0-9-]+\\.)+(?:[a-zA-Z]{2,4})|localhost))\s+/", $string, $matches);
		return $matches[0]; // include spaces
		// return $matches[1]; // exclude spaces
	}

	/**
	* Check if the given url has the leading HTTP protocol prefix.
	* If not then the prefix will be added.
	*
	* @access public
	* @param string url
	* @return string url
	*/
	function getUrl($string)
	{
		if (preg_match("/^[a-z]+:\/\//i", $string) || empty($string)) {
			return $string;
		} else {
			return "http://".$string;
		}
	}
}
?>