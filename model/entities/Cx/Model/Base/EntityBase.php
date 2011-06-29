<?php
namespace Cx\Model\Base;

/**
 * Thrown by @link EntityBase::validate() if validation errors occur.
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
 *
 * See EntityBase::$validators if you want to subclass it.
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
     * @throws ValidationException
     * @return null | array( 'field' => array( 'errorid' => 'errormessage') )
     * @prePersis
     */
    public function validate() {
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
}
