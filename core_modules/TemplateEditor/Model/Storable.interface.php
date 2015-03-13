<?php

namespace Cx\Core_Modules\TemplateEditor\Model;

/**
 * 
 */
interface Storable {


    /**
     * @param String $name
     */
    public function retrieve($name);

    /**
     * @param                  $name
     * @param YamlSerializable $data
     *
     * @return bool
     */
    public function persist($name,YamlSerializable $data);

}