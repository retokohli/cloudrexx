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
        return preg_replace("#( (src|href)\s*=\s*['\"])(?=[^/])(?!((http|ftp|)://|javascript:))#", '\1'.$this->offset, $this->content);
    }
}