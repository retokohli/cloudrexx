<?php
/**
 * Validator
 *
 * Global request validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
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
    if (CONTREXX_ESCAPE_GPC) {
    return strip_tags($string);
    }
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
    if (CONTREXX_ESCAPE_GPC) {
    return $string;
    }
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
	}
    return $string;
}

/**
 * Checks if the request comes from a spider
 *
 * @return boolean
 */
function checkForSpider()
{
    $arrRobots = array();
    require_once ASCMS_CORE_MODULE_PATH.'/stats/lib/spiders.inc.php';
    $useragent =  htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, CONTREXX_CHARSET);
    foreach ($arrRobots as $spider) {
        $spiderName = trim($spider);
        if (preg_match("=".$spiderName."=",$useragent)) {
            return true;
            break;
        }
    }
    return false;
}

?>
