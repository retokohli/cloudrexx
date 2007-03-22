<?php
/**
 * Validator
 *
 * Global request validator
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 * @todo        Isn't this supposed to be a class?
 */

//Security-Check
if (eregi("validator.inc.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}


/**
 * strip_tags wrapper
 *
 * @param     string     $string
 * @return    string     $string (cleaned)
 */
function contrexx_strip_tags($string)
{
  if(CONTREXX_ESCAPE_GPC)
    return strip_tags($string);
  else
    return addslashes(strip_tags($string));
}



/**
* addslashes wrapper to check for gpc_magic_quotes - gz
*
* @param     string     $string
* @return    string     $string (cleaned)
*/
function contrexx_addslashes($string)
{
  // if magic quotes is on the string is already quoted,
  // just return it
  if(CONTREXX_ESCAPE_GPC)
    return $string;
  else
    return addslashes($string);
}

/**
* stripslashes wrapper to check for gpc_magic_quotes
*
* @param string	$string
* @return string $string
*/
function contrexx_stripslashes($string)
{
	if (CONTREXX_ESCAPE_GPC) {
		return stripslashes($string);
	} else {
		return $string;
	}
}
?>