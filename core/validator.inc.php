<?php

/**
 * Validator
 *
 * Global request validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @version        1.0.0
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
 * @param     string     $string
 * @return    string              cleaned
 */
function contrexx_addslashes($string)
{
    // if magic quotes is on, the string is already quoted
    if (CONTREXX_ESCAPE_GPC) {
        return $string;
    }
    return addslashes($string);
}


/**
 * stripslashes wrapper to check for gpc_magic_quotes
 * @param   string    $string
 * @return  string
 */
function contrexx_stripslashes($string)
{
    if (CONTREXX_ESCAPE_GPC) {
        return stripslashes($string);
    }
    return $string;
}


/**
 * Convenient match-and-replace-in-one function
 *
 * Parameters are those of preg_match() and preg_replace() combined.
 * @param   string  $pattern      The regex pattern to match
 * @param   string  $replace      The replacement string for matches
 * @param   string  $subject      The string to be matched/replaced on
 * @param   array   $subpatterns  The optional array for the matches found
 * @param   integer $limit        The optional limit for replacements
 * @param   integer $count        The optional counter for the replacements done
 * @return  string                The resulting string
 */
function preg_match_replace(
    $pattern, $replace, $subject, &$subpatterns=null, $limit=-1, &$count=null
) {
    if (preg_match($pattern, $subject, $subpatterns)) {
        $subject = preg_replace($pattern, $replace, $subject, $limit, $count);
//echo("preg_match_replace(<br />pattern $pattern,<br />replace $replace,<br />subject $subject,<br />subpatterns ".var_export($subpatterns, true).",<br />limit $limit,<br />count $count<br />): Match, made ".htmlentities($subject)."<br />");
        return $subject;
    }
//echo("NO match<br />");
    return $subject;
}


/**
 * Checks if the request comes from a spider
 *
 * @return  boolean
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
