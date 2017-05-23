<?php

namespace Cx\Core\ContentManager\Model\Entity;

/**
 * Cx\Core\ContentManager\Model\Entity\Translation
 */
class Translation extends \Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * All required columns are mapped through inherited superclass
     */
}
