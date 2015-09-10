<?php

namespace Cx\Core_Modules\TemplateEditor\Model\Repository;

use Cx\Core\View\Model\Entity\Theme;
use Cx\Core_Modules\TemplateEditor\Model\Entity\OptionSet;
use Cx\Core_Modules\TemplateEditor\Model\Storable;

/**
 * Class ThemeOptionsRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionSetRepository
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
     * @param Theme $theme
     *
     * @return OptionSet
     */
    public function get(Theme $theme)
    {
        $componentData = $this->storage->retrieve($theme->getFoldername());
        return new OptionSet($theme, $componentData);
    }

    /**
     * Save a ThemeOptions entity to the component.yml file.
     *
     * @param OptionSet $entity
     *
     * @return bool
     */
    public function save($entity)
    {
        return $this->storage->persist($entity->getName(), $entity);
    }


}