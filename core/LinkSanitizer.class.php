<?php
/**
 * This class replaces any links from Contrexx < 3.0 on the fly.
 * @author srz
 */
/**
 * Handles the [[NODE_<ID>_<LANGID>]] placeholders.
 */
class LinkSanitizer {
    protected $offset;
    protected $content;
    /**
     * @param string $offset the path offset to prepend, e.g. '/' or '/cms/'
     */
    function __construct($offset, &$content) {
        $this->content = &$content;
        $this->offset = $offset;      
    }

    /**
     * Calculates and returns the content with all replacements done.
     */
    function replace() {
        return preg_replace("/
                (     # match all SRC and HREF attributes 
                      \s(src|href|action)\s*=\s*['\"]

                   |  # or match all CSS @import statements
                      @import\s+url\s*\(                             )

                # but only those who's values don't start with a slash..
                (?=[^\/])

                # ..and neither start with a protocol (http:, ftp:, javascript:, mailto:, etc)
                (?!([a-z]+):)
                # ..and neither start with a backslash which would indicate that the url lies within some javascript code
                (?!\\\)
            /x", '\1'.$this->offset, $this->content);
    }
}
