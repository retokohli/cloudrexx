<?php

namespace Cx\Core_Modules\TemplateEditor\Model;

interface YamlSerializable {

    /**
     * Serialize a class to use in a .yml file.
     * This should return a array which will be serialized by the caller.
     *
     * @return array
     */
    public function yamlSerialize();

}