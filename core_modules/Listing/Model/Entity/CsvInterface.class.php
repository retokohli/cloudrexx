<?php

/**
 * Csv interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

namespace Cx\Core_Modules\Listing\Model;

/**
 * Csv interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

class CsvInterface implements Exportable, Importable {
    protected $lineEnding;
    protected $separator;
    
    public function __construct($separator = ',', $lineEnding = "\n") {
        $this->lineEnding = $lineEnding;
        $this->separator = $separator;
    }

    /**
     * @todo Add "" around values containing separator
     * @todo Add support for different charsets, since CSV does not declare which one to use
     * Please note that Excel uses tab as delimiter if charset is unicode and semikolon for ANSI charsets
     * @param type $twoDimensionalArray
     * @return type 
     */
    public function export($twoDimensionalArray) {
        $content = '';
        foreach ($twoDimensionalArray as $array) {
            $content .= implode($this->separator, $array);
            $content .= $this->lineEnding;
        }
        return $content;
    }

    /**
     * @todo Add "" around values containing separator
     * @todo Add support for different charsets, since CSV does not declare which one to use
     * Please note that Excel uses tab as delimiter if charset is unicode and semikolon for ANSI charsets
     * @param type $dataAsString
     * @return type 
     */
    public function import($dataAsString) {
        $array = array();
        $array = explode($this->lineEnding, $dataAsString);
        foreach ($array as &$line) {
            $line = explode($this->separator, $line);
        }
        return $array;
    }
}
