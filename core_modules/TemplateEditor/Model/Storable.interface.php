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
     * Retrieve a item from the storage
     * 
     * @param String $name
     */
    public function retrieve($name);

    /**
     * Persist a item to the storage
     * 
     * @param                  $name
     * @param YamlSerializable $data
     *
     * @return bool
     */
    public function persist($name, YamlSerializable $data);

    /**
     * Get list with items.
     *
     * @return array
     */
    public function getList();

    /**
     * Remove item.
     *
     * @param $name
     */
    public function remove($name);

}