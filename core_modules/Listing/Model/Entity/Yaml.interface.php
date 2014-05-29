<?php

namespace  Cx\Core_Modules\Listing\Model\Entity;

class Yaml implements Exportable, Importable {
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
