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
 * Wrapper for strip_tags() that complies with gpc_magic_quotes
 * @param     string     $string
 * @return    string     $string (cleaned)
 */
function contrexx_strip_tags($string)
{
    if (CONTREXX_ESCAPE_GPC) return strip_tags($string);
    return addslashes(strip_tags($string));
}


/**
 * Wrapper for addslashes() that complies with gpc_magic_quotes
 * @param     string     $string
 * @return    string              cleaned
 */
function contrexx_addslashes($string)
{
    // If magic quotes is on the string is already quoted,
    // just return it
    if (CONTREXX_ESCAPE_GPC) return $string;
    return addslashes($string);
}


/**
 * Wrapper for stripslashes() that complies with gpc_magic_quotes
 * @param   string    $string
 * @return  string
 */
function contrexx_stripslashes($string)
{
    if (CONTREXX_ESCAPE_GPC) return stripslashes($string);
    return $string;
}


/**
 * Processes the argument like {@see contrexx_stripslashes()}, but also
 * handles arrays
 *
 * Recurses down into array parameters and applies
 * {@see contrexx_stripslashes()} to any scalar value encountered.
 * @param   mixed   $param      A scalar or array value
 * @return  mixed               The parameter with magic slashes removed
 *                              recursively, if any.
 */
function contrexx_stripslashes_recursive($param)
{
    if (is_array($param)) {
        foreach ($param as &$thing) {
            $thing = contrexx_stripslashes_recursive($thing);
        }
        return $param;
    }
    return contrexx_stripslashes($param);
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
        return $subject;
    }
    return $subject;
}


/**
 * Checks whether the request comes from a known spider
 * @return  boolean
 */
function checkForSpider()
{
    $arrRobots = array();
    require_once ASCMS_CORE_MODULE_PATH.'/stats/lib/spiders.inc.php';
    $useragent =  htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, CONTREXX_CHARSET);
    foreach ($arrRobots as $spider) {
        $spiderName = trim($spider);
        if (preg_match('/'.preg_quote($spiderName, '/').'/', $useragent)) {
            return true;
        }
    }
    return false;
}


/////////////////////////////////////////////////////////////
// Convenience escaping function layer - use these rather than
// contrexx_addslashes() and so on please.

/**
 * Encodes a raw string for use with [X]HTML
 *
 * Apply to raw strings and those taken from the database before writing it
 * to the HTML response stream.
 * @param   string  $raw      The raw string
 * @return  string            The HTML encoded string
 */
function contrexx_raw2xhtml($raw)
{
    return htmlentities($raw, ENT_QUOTES, CONTREXX_CHARSET);
}


/**
 * Unescapes data from any request, and returns a raw string
 *
 * Apply to any string taken from a get or post request, or from a cookie.
 * @param   string  $input    The input string
 * @return  string            The raw string
 */
function contrexx_input2raw($input)
{
  return  contrexx_stripslashes($input);
}


/**
 * Adds slashes to the given string
 *
 * Apply to any raw string before inserting it into the database.
 * @param   string  $raw      The raw string
 * @return  string            The slashed string
 */
function contrexx_raw2db($raw)
{
    return addslashes($raw);
}


/**
 * Encodes a raw string for use with XML
 *
 * Apply to raw strings and those taken from the database before writing
 * to the XML response stream.
 * @param   string  $raw    The raw string
 * @return  string          The XML encoded string
 */
function contrexx_raw2xml($raw)
{
    return htmlspecialchars($raw, ENT_QUOTES, CONTREXX_CHARSET);
}


/**
 * Encodes a raw string for use as a href or src attribute value.
 *
 * Apply to any raw string that is to be used as a link or image address
 * in any tag attribute, such as a.href or img.src.
 * @param   string  $raw          The raw string
 * @param   boolean $encodeDash   Encode dashes ('-') if true.
 *                                Defaults to false
 * @return  string                The URL encoded string
 */
function contrexx_raw2encodedUrl($source, $encodeDash=false)
{
    $cutHttp = false;
    if (!$encodeDash && substr($source, 0, 7) == 'http://') {
        $source = substr($source, 7);
        $cutHttp = true;
    }
    $source = array_map('rawurlencode', explode('/', $source));
    if ($encodeDash) {
        $source = str_replace('-', '%2D', $source);
    }
    $result = implode('/', $source);
    if ($cutHttp) $result = 'http://'.$result;
    return $result;
}


/**
 * Removes script tags and their content from the given string
 * @param   string  $raw    The original string
 * @return  string          The string with script tags removed
 * @todo    Check for event handlers
 */
function contrexx_remove_script_tags($raw)
{
    // Remove closed script tags and content
    $result = preg_replace('/<\s*script[^>]*>.*?<\s*\/script\s*>/is', '', $raw);
    // Remove unclosed script tags
    $result = preg_replace('/<\s*script[^>]*>/is', '', $result);
    return $result;
}

?>
