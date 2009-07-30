<?php
/**
 * Framework Validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Regular Expression for an e-mail address
 *
 * TKaelin @ 2.0.2: wrote new regex based on http://en.wikipedia.org/wiki/E-mail_address
 * Dave V, @ 2.1.2: re-wrote regex according to http://www.regular-expressions.info/email.html 
*/
define('VALIDATOR_REGEX_EMAIL',
    "[a-z0-9!#\$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#\$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?"
);

/**
 * Framework Validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
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
        return preg_match('"^'.VALIDATOR_REGEX_EMAIL.'$"i', stripslashes($string)) ? true : false;
    }

    /**
     * Find all e-mail addresses in a string
     * @param   string  $string     String potentially containing email addresses
     * @return  array               Array with all e-mail addresses found
     * @access  public
     */
    function getEmailAsArray($string)
    {
        preg_match_all(
//          '/\s([_a-zA-Z0-9-]+(?:\.?[_a-zA-Z0-9-])*@((?:[a-zA-Z0-9-]+\.)+(?:[a-zA-Z]{2,4})|localhost))\s+/", $string, $matches);
            '/\s('.VALIDATOR_REGEX_EMAIL.')\.?\s/',
            $string, $matches);
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


    /**
     * Returns true if the ending of the given file name
     * is harmless. We consider all executable as well as
     * all scripts (server and client side) as harmful.
     * You should NOT allow to upload these.
     *
     * This function returns true if the given filename
     * is safe to upload.
     *
     * @param string file
     */
    static function is_file_ending_harmless($file) {
        $evil = array(
            # windows executables:
            'exe','bat','pif', 'com',
            # client scripts:
            'vs', 'vbs','js',
            # client script containers:
            'html','xhtml','xml','svg','shtml','htm',
            # server scripts:
            'php','cgi','pl','jsp','jspx','asp','aspx', 
            'jsp','jspx','jhtml','phtml','cfm',
        );
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
        if (in_array($ext, $evil)) {
            return false;
        }
        return true;
    }

    private static function __fix_flash($html) {
        // we come from a preg_replace_callback
        $html = $html[0];

        // already done
        if (strstr($html, 'wmode=')) return $html;

        // INJECT <param> for IE
        $new_param = '<param name="wmode" value="transparent" />'."\n";
        $html = preg_replace('/<param/i', "$new_param<param", $html, 1);

        // INJECT <embed> attribute for FF and the rest of the gang
        $embed_attr = 'wmode="transparent"';
        return preg_replace('/<embed/i', "<embed $embed_attr", $html, 1);
    }

    /**
     * This function fixes the given HTML so that any embedded flash
     * objects will get the "wmode=transprent" set. This is neccessary
     * for the frontend login box for example, when a flash object is
     * on the page.
     *
     * Takes un-escaped HTML code as parameter, returns the fixed HTML.
     */
    static function fix_flash_transparency($html_code) {
        $result = preg_replace_callback(
            '!<object.*?.*?<param.*?</object>!ims',
            'FWValidator::__fix_flash',
            $html_code
        );
        return $result;
    }
}

