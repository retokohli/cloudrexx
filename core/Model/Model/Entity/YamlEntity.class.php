<?php
/**
 * YAML Entity
 *
 * A entity that is handled by a YAML repository.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */

namespace Cx\Core\Model\Model\Entity;

/**
 * YAML Entity Exception
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */
class YamlEntityException extends \Exception {};

/**
 * YAML Entity
 *
 * A entity that is handled by a YAML repository.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @subpackage  core_model
 */
class YamlEntity extends \Cx\Model\Base\EntityBase {
    /**
     * Defines if an entity is virtual and therefore not persistable.
     * Defaults to FALSE - not virtual.
     * @var boolean
     */
    protected $virtual = false;

    /**
     * Set the virtuality of the entity
     * @param   boolean $virtual    TRUE to set the entity as virtual or otherwise to FALSE 
     */
    public function setVirtual($virtual) {
        $this->virtual = $virtual;
    }

    /**
     * Returns the virtuality of the entity
     * @return  boolean TRUE if the entity is virtual, otherwise FALSE
     */
    public function isVirtual() {
        return $this->virtual;
    }
}

