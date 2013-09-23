<?php

/**
 * Yaml interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

namespace Cx\Core_Modules\Listing\Model;

/**
 * Yaml interface
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_listing
 */

class YamlInterface implements Exportable, Importable {
    protected $yaml;
    
    public function __construct() {
        $this->yaml = new \Symfony\Component\Yaml\Yaml();
    }
    
    public function export($twoDimensionalArray) {
        return $this->yaml->dump($twoDimensionalArray, 1000);
    }
    
    public function import($dataAsString) {
        return $this->yaml->load($dataAsString);
    }
}
