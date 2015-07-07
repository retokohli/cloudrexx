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
     * Delimiter
     * 
     * @var string 
     */
    protected $delimiter;

    /**
     * Contructor for RegularExpression
     * 
     * @param string $regex Regular expression
     */
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

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
     * Getter for Delimiter
     * 
     * @return string
     */
    function getDelimiter()
    {
        return $this->delimiter;
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
     * Set the delimiter
     * 
     * @param string $delimiter
     */
    function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
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
    
    /**
     * Return the regular expression string($this->regex)
     * 
     * @return string Return the regular expression string($this->regex)
     */
    function __toString()
    {
        return $this->regex;
    }
}
