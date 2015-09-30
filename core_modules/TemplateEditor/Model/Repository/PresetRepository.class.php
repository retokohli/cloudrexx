<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Repository;

use Cx\Core_Modules\TemplateEditor\Model\Entity\Preset;
use Cx\Core_Modules\TemplateEditor\Model\Storable;

/**
 * Class ThemeOptionsRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class PresetRepository
{

    /**
     * @var Storable
     */
    protected $storage;

    /**
     * @param Storable $storage
     */
    public function __construct(Storable $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param       $name
     *
     * @return Preset
     */
    public function getByName($name)
    {
        return Preset::createFromArray($name,$this->storage->retrieve($name));
    }

    /**
     * Find all presets
     *
     * @return array
     */
    public function findAll()
    {
        return $this->storage->getList();
    }

    /**
     * Save a ThemeOptions entity to the component.yml file.
     *
     * @param Preset $entity
     *
     * @return bool
     */
    public function save($entity)
    {
        return $this->storage->persist($entity->getName(), $entity);
    }

    /**
     * @param $entity
     */
    public function remove($entity){
        return $this->storage->remove($entity->getName());
    }


}