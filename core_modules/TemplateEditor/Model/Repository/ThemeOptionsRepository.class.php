<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Repository;
use Cx\Core_Modules\TemplateEditor\Model\Entity\ThemeOptions;
use Cx\Core_Modules\TemplateEditor\Model\Storable;

/**
 * 
 */
class ThemeOptionsRepository {


    /**
     * @var Storable
     */
    protected $storage;

    public function __construct(Storable $storage) {
        $this->storage = $storage;
    }

    /**
     * @param String $name
     * @return ThemeOptions
     */
    public function getByName($name) {
        $componentData = $this->storage->retrieve($name);
        return new ThemeOptions($componentData);
    }

    /**
     * @param ThemeOptions $entity
     */
    public function save($entity) {
        $this->storage->persist($entity->getName(),$entity);
    }

}