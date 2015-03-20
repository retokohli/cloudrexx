<?php

/**
 * LinkSanitizer
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */

/**
 * This class replaces any links from Contrexx < 3.0 on the fly.
 * Handles the [[NODE_<ID>_<LANGID>]] placeholders.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */
class LinkSanitizer {
    const ATTRIBUTE_AND_OPEN_QUOTE = 1;
    const FILE_PATH                = 3;
    const CLOSE_QUOTE              = 4;
    
    protected $offset;
    protected $content;

    /**
     * @param string $offset the path offset to prepend, e.g. '/' or '/cms/'
     */
    public function __construct($offset, &$content) {
        $this->content = &$content;
        $this->offset  = $offset;
    }

    /**
     * Calculates and returns the content with all replacements done.
     */
    public function replace() {
        $content = preg_replace_callback("/
            (
                # match all SRC and HREF attributes 
                \s+(src|href|action)\s*=\s*['\"]
                |
                # or match all CSS @import statements
                @import\s+url\s*\(
            )

            # but only those who's values don't start with a slash..
            (?=[^\/])
            
            # ..and neither start with ..\/ (references to files outside the Contrexx directory are ignored)
            (?!\.\.\/)

            # ..and neither start with a protocol (http:, ftp:, javascript:, mailto:, etc)
            (?![a-zA-Z]+:)

            # ..and neither start with an ampersand followed by a sharp and end with a semicolon (which would indicate that the url contains html codes for ascii characters)
            (?!&\#\d+;)

            # ..and neither start with a sharp
            (?!\#)

            # ..and neither start with a backslash which would indicate that the url lies within some javascript code
            (?!\\\)
            
            # match file path and closing quote
            ([^'\"]*)(['\"])
        /x", array($this, 'getPath'), $this->content);

        if (!empty($_GET['preview']) || (isset($_GET['appview']) && ($_GET['appview'] == 1))) {
            $content = preg_replace_callback("/
                (\<(?:a|form)[^>]*?\s+(?:href|action)\s*=\s*)
                (['\"])
                ((?![a-zA-Z]+?:|\\\\).+?)
                \\2
                ([^>]*\>)
            /x", array($this, 'appendParameters'), $content);
        }

        return $content;
    }

    /**
     * Returns the created path by the given array.
     *
     * @param   array   $matches
     * @return  string  created path
     */
    private function getPath($matches) {
        // The Shop JS Cart escapes pathes because he loads it via JavaScript.
        // For this reason, we replace escaped slashes by slashes.
        $matches[\LinkSanitizer::FILE_PATH] = str_replace('\\/', '/', $matches[\LinkSanitizer::FILE_PATH]);

        if ($this->fileExists(ASCMS_DOCUMENT_ROOT . '/' . $matches[\LinkSanitizer::FILE_PATH])) {
            // this is an existing file, do not add virtual language dir
            return $matches[\LinkSanitizer::ATTRIBUTE_AND_OPEN_QUOTE] .
            ASCMS_INSTANCE_OFFSET .
            '/' . $matches[\LinkSanitizer::FILE_PATH] .
            $matches[\LinkSanitizer::CLOSE_QUOTE];
        } else {
            // this is a link to a page, add virtual language dir
            return $matches[\LinkSanitizer::ATTRIBUTE_AND_OPEN_QUOTE] .
            $this->offset .
            $matches[\LinkSanitizer::FILE_PATH] .
            $matches[\LinkSanitizer::CLOSE_QUOTE];
        }
    }

    /**
     * Checks if a file, whose name contains parameters, exists.
     * Exception for PHP files.
     *
     * @access  private
     * @param   string   $filePath
     * @return  bool     true if the file exists, otherwise false
     */
    private function fileExists($filePath) {
        if (file_exists($filePath)) {
            return true;
        }

        $arrUrl = parse_url($filePath);
        if (!empty($arrUrl['path'])
            && substr($arrUrl['path'], -4) !== '.php'
            && file_exists($arrUrl['path'])) {
            return true;
        }

        return false;
    }

    /**
     * Callback method for appending preview and appview parameter to href and action attributes.
     *
     * @access  private
     * @param   array       $matches    regex matches
     * @return  string                  replacement string
     */
    private function appendParameters($matches) {
        $before = $matches[1];
        $quote  = $matches[2];
        $value  = $matches[3];
        $after  = $matches[4];

        if (strpos($value, '?') !== false) {
            list($path, $query) = explode('?', $value);
            $query = \Cx\Core\Routing\Url::params2array($query);
        } else {
            $path = $value;
            $query = array();
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (!empty($extension) && ($extension != 'php')) {
            return $matches[0];
        }

        if (!empty($_GET['preview']) && !isset($query['preview'])) {
            $query['preview'] = $_GET['preview'];
        }
        if (!empty($_GET['templateEditor']) && !isset($query['templateEditor'])) {
            $query['templateEditor'] = $_GET['templateEditor'];
        }
        if ((isset($_GET['appview']) && ($_GET['appview'] == 1)) && !isset($query['appview'])) {
            $query['appview'] = $_GET['appview'];
        }

        $query = \Cx\Core\Routing\Url::array2params($query);

        // replace & with &amp; but only & (not followed by amp;)
        $query = preg_replace('/&(?!amp;)/', '&amp;', $query);
        return $before.$quote.$path.'?'.$query.$quote.$after;
    }

}
