<?php

namespace Cx\Core\ContentManager\Model\Entity;

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Entity;

/**
 * Cx\Core\ContentManager\Model\Entity\Translation
 */
class Translation extends \Gedmo\Translatable\Entity\AbstractTranslation
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * All required columns are mapped through inherited superclass
     */
}
