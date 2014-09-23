<?php

/**
 * EntityBase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_base
 */

namespace Cx\Model\Base;

/**
 * Thrown by @link EntityBase::validate() if validation errors occur.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_base
 */
class ValidationException extends \Exception {
    protected $errors;

    public function __construct(array $errors) {
        parent::__construct();
        $this->errors = $errors;
        $this->assignMessage();
    }

    private function assignMessage() {
        $str = '';
        foreach($this->errors as $field => $details) {
            $str .= $field.":\n";
            foreach($details as $id => $message) {
                $str .= "    $id: $message\n";
            }
        }
        $this->message = $str;
    }

    public function getErrors() {
        return $this->errors;
    }
}

/**
 * This class provides the magic of being validatable.
 * See EntityBase::$validators if you want to subclass it.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  model_base
 */
class EntityBase {
    /**
     * Initialize this array as follows:
     * array(
     *     'columName' => Zend_Validate
     * )
     * @var array
     */
    protected $validators = null;

    /**
     * Defines if an entity is virtual and therefore not persistable.
     * Defaults to FALSE - not virtual.
     * @var boolean
     */
    protected $virtual = false;
    
    /**
     * This is an ugly solution to allow $this->cx to be available in all entity classes
     * Since the entity's constructor is not called when an entity is loaded from DB this
     * cannot be assigned there.
     */
    public function __get($name) {
        if ($name == 'cx') {
            return \Env::get('cx');
        }
    }
    
    /**
     * Returns the component controller for this component
     * @return \Cx\Core\Core\Model\Entity\SystemComponent
     */
    public function getComponentController() {
        $matches = array();
        preg_match('/Cx\\\\(?:Core|Core_Modules|Modules)\\\\([^\\\\]*)\\\\/', get_class($this), $matches);
        if (empty($matches[1])) {
            throw new \Exception('Could not find component name');
        }
        $em = $this->cx->getDb()->getEntityManager();
        $componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
        $myComponent = $componentRepo->findOneBy(array(
            'name' => $matches[1],
        ));
        if (!$myComponent) {
            throw new \Cx\Core\Core\Model\Entity\SystemComponentException('Component not found: "' . $matches[1] . '"');
        }
        return $myComponent;
    }
    
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
    
    /**
     * @throws ValidationException
     * @prePersist
     */
    public function validate() {
        if(!$this->validators)
            return;

        $errors = array();
        foreach($this->validators as $field => $validator) {
            $methodName = 'get'.ucfirst($field);
            $val = $this->$methodName();
            if($val) {
                if(!$validator->isValid($val)) {
                     $errors[$field] = $validator->getMessages();
                }
            }
        }
        if(count($errors) > 0)
            throw new ValidationException($errors);
    }

    public function __toString() {
        return strval($this->getId());
    }
}

