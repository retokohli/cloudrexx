<?php

namespace Cx\Core_Modules\TemplateEditor\Model;

/**
 * Interface Storable
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
interface Storable
{


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
    public function persist($name, YamlSerializable $data);

}