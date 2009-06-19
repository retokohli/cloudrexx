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
}
?>
