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
     * @return
     */
    public function persist($name,$data);

}