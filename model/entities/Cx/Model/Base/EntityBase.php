<?php
namespace Cx\Model\Base;

/**
 * This class provides the magic of being validatable.
 *
 * See EntityBase::$validators if you want to subclass it.
 */
class EntityBase {
    /**
     * Initialize this array as 
     * array(
     *     'columName' => Zend_Validate
     * )
     * @var array
     */
    protected $validators = null;

    /**
     * @return null | array( 'field' => array( 'errorid' => 'errormessage') )
     */
    public function validate() {
        $errors = array();
        foreach($this->$validators as $field => $validator) {
            if(!$validator->isValid($this->$field)) {
                $errors[$field] = $validator->getMessages();
            }
        }
        if(count($errors) == 0)
            return null;
        return $errors;
    }
}
?>
