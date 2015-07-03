<?php

/**
 * Class RegularExpression
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_helpers
 */

namespace Cx\Lib\Helpers;

/**
 * Class RegularExpression
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_helpers
 */
class RegularExpression
{
    /**
     * Regex
     * 
     * @var string 
     */
    protected $regex;
    
    /**
     * Replacement string
     * 
     * @var string
     */
    protected $replacement;
    
    /**
     * Getter for $regex
     * 
     * @return string
     */
    function getRegex()
    {
        return $this->regex;
    }

    /**
     * Getter for $replacement
     * 
     * @return string
     */
    function getReplacement()
    {
        return $this->replacement;
    }

    /**
     * Set the regular expression
     * 
     * @param string $regex
     */
    function setRegex($regex)
    {
        $this->regex = $regex;
    }

    /**
     * Set the replacement string
     * 
     * @param string $replacement
     */
    function setReplacement($replacement)
    {
        $this->replacement = $replacement;
    }

    /**
     * Match the input string with regular expression
     * 
     * @param string $input Input string
     * 
     * @return boolean True|False True on regular expression matches the string
     */
    function match($input)
    {
        return preg_match($this->regex, $input, $matches);
    }
    
    /**
     * Search and replace in the Input string
     * 
     * @param string $input Input string
     * 
     * @return string Replaced string
     */
    function replace($input)
    {
        return preg_replace($this->regex, $this->replacement, $input);
    }
}
