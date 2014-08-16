<?php

/**
 * 
 */

namespace Cx\Core\Html\Controller;

/**
 * 
 */
class FormGenerator {
    protected $form = null;
    
    public function __construct($entity, $actionUrl = null, $entityClass = '', $options = array()) {
        // Remove the virtual element from array
        unset($entity['virtual']);
        if (empty($entityClass) && is_object($entity)) {
            $entityClass = get_class($entity);
        }
        \JS::registerCSS(\Env::get('cx')->getCoreFolderName() . '/Html/View/Style/Backend.css');
        $this->form = new \Cx\Core\Html\Model\Entity\FormElement($actionUrl);
        $this->form->setAttribute('id', 'form-X');
        $this->form->setAttribute('class', 'cx-ui');
        $em = \Env::get('em');
        $title = new \Cx\Core\Html\Model\Entity\HtmlElement('legend');
        $title->addChild(new \Cx\Core\Html\Model\Entity\TextElement($entityClass));
        $this->form->addChild($title);
        // @todo replace this by auto-find editid
        if (isset($_REQUEST['editid'])) {
            $editIdField = new \Cx\Core\Html\Model\Entity\DataElement('editid', contrexx_input2raw($_REQUEST['editid']), 'input');
            $editIdField->setAttribute('type', 'hidden');
            $this->form->addChild($editIdField);   
        }
        // foreach entity field
        /*$metadata = $em->getClassMetadata(get_class($entity));
        foreach ($metadata->getColumnNames() as $field) {
            $type = $metadata->fieldMappings[$field]['type'];//*/
        foreach ($entity as $field=>$value) {
            $type = gettype($value);
            if (is_object($value)) {
                if ($value instanceof \Cx\Model\Base\EntityBase) {
                    $type = 'Cx\Model\Base\EntityBase';
                } elseif ($value instanceof \Doctrine\Common\Collections\Collection) {
                    continue;
                } else {
                    $type = get_class($value);
                }
            }//*/
            $length = 0;
            /*if (isset($metadata->fieldMappings[$field]['length'])) {
                $length = $metadata->fieldMappings[$field]['length'];
            }*/
            //if (is_array($entity) && isset($entity[$field])) {
                $value = $entity[$field];
            /*} else {
                $value = $metadata->getFieldValue($entity, $field);
            }*/
            //$this->addFieldsForMetadata($metadata->fieldMappings[$field], $value);
            /*$label = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
            $label->setAttribute('for', 'formX_' . $field);
            $label->addChild(new \Cx\Core\Html\Model\Entity\TextElement($field));
            $this->form->addChild($label);*/
            $fieldOptions = array();
            if (isset($options['fields']) && isset($options['fields'][$field])) {
                $fieldOptions = $options['fields'][$field];
            }
            /*$element = $this->getDataElement($field, $type, $length, $value, $fieldOptions);
            $element->setAttribute('id', 'form-X-' . $field);*/
            $dataElement = $this->getDataElement($field, $type, $length, $value, $fieldOptions);
            if (empty($dataElement)) {
                continue;
            }
            $dataElement->setAttribute('id', 'form-X-' . $field);
            $this->form->addChild(static::getDataElementGroup($field, $dataElement, $fieldOptions));
        }
        if (isset($options['cancelUrl'])) {
            $this->form->cancelUrl =$options['cancelUrl'];
        }
    }
    
    public static function getDataElementGroup($field, $dataElement, $fieldOptions = array()) {
        global $_ARRAYLANG;

        $group = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
        $group->setAttribute('class', 'group');
        $label = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
        $label->setAttribute('for', 'form-X-' . $field);
        $fieldHeader = $field;
        if (isset($fieldOptions['header'])) {
            if (isset($_ARRAYLANG[$fieldOptions['header']])) {
                $fieldHeader = $_ARRAYLANG[$fieldOptions['header']];
            } else {
                $fieldHeader = $fieldOptions['header'];
            }
        }
        $label->addChild(new \Cx\Core\Html\Model\Entity\TextElement($fieldHeader));
        $group->addChild($label);
        $controls = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
        $controls->setAttribute('class', 'controls');
        $controls->addChild($dataElement);
        $group->addChild($controls);
        return $group;
    }
    
    public function getDataElement($name, $type, $length, $value, $options) {
        if (!empty($options['type'])) {
            $type = $options['type'];
        }
        switch ($type) {
            case 'bool':
            case 'boolean':
                // yes/no checkboxes
                $fieldset = new \Cx\Core\Html\Model\Entity\HtmlElement('div');
                $inputYes = new \Cx\Core\Html\Model\Entity\DataElement($name, 'yes');
                $inputYes->setAttribute('type', 'radio');
                $inputYes->setAttribute('value', 'yes');
                $inputYes->setAttribute('id', 'form-X-' . $name . '_yes');
                $fieldset->addChild($inputYes);
                $labelYes = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
                $labelYes->setAttribute('for', 'form-X-' . $name . '_yes');
                $labelYes->addChild(new \Cx\Core\Html\Model\Entity\TextElement('Yes'));
                $fieldset->addChild($labelYes);
                $inputNo = new \Cx\Core\Html\Model\Entity\DataElement($name, 'no');
                $inputNo->setAttribute('id', 'form-X-' . $name . '_no');
                $inputNo->setAttribute('type', 'radio');
                $inputNo->setAttribute('value', 'no');
                $fieldset->addChild($inputNo);
                $labelNo = new \Cx\Core\Html\Model\Entity\HtmlElement('label');
                $labelNo->setAttribute('for', 'form-X-' . $name . '_no');
                $labelNo->addChild(new \Cx\Core\Html\Model\Entity\TextElement('No'));
                $fieldset->addChild($labelNo);
                if ($value) {
                    $inputYes->setAttribute('checked');
                } else {
                    $inputNo->setAttribute('checked');
                }
                return $fieldset;
                break;
            case 'int':
            case 'integer':
                // input field with type number
                $inputNumber = new \Cx\Core\Html\Model\Entity\DataElement(
                    $name,
                    $value,
                    \Cx\Core\Html\Model\Entity\DataElement::TYPE_INPUT,
                    new \Cx\Core\Validate\Model\Entity\RegexValidator(
                        '/-?[0-9]*/'
                    )
                );
                $inputNumber->setAttribute('type', 'number');
                return $inputNumber;
                break;
            case 'Cx\Model\Base\EntityBase':
                $entityClass = get_class($value);
                $entities = \Env::get('em')->getRepository($entityClass)->findAll();
                $primaryKeyName = \Env::get('em')->getClassMetadata($entityClass)->getSingleIdentifierFieldName();
                $selected = \Env::get('em')->getClassMetadata($entityClass)->getFieldValue($value, $primaryKeyName);
                foreach ($entities as $entity) {
                    $arrEntities[\Env::get('em')->getClassMetadata($entityClass)->getFieldValue($entity, $primaryKeyName)] = $entity;
                }
                $select = new \Cx\Core\Html\Model\Entity\DataElement(
                    $name,
                    \Html::getOptions($arrEntities, $selected),
                    \Cx\Core\Html\Model\Entity\DataElement::TYPE_SELECT
                );
                return $select;
                break;
            case 'Country':
                // this is for customizing only:
                $data = \Cx\Core\Country\Controller\Country::getNameById($value);
                if (empty($data)) {
                    $value = 204;
                }
                $options = \Cx\Core\Country\Controller\Country::getMenuoptions($value, false);
                $select = new \Cx\Core\Html\Model\Entity\DataElement(
                    $name,
                    $options,
                    \Cx\Core\Html\Model\Entity\DataElement::TYPE_SELECT
                );
                return $select;
                break;
            case 'DateTime':
            case 'datetime':
                // input field with type text and class datepicker
                if ($value instanceof \DateTime) {
                    $value = $value->format(ASCMS_DATE_FORMAT);
                }
                $input = new \Cx\Core\Html\Model\Entity\DataElement($name, $value);
                $input->setAttribute('type', 'text');
                $input->setAttribute('class', 'datepicker');
                if (isset($options['readonly']) && $options['readonly']) {
                    $input->setAttribute('disabled');
                }
                return $input;
                break;
            case 'text':
                // textarea
                $textarea = new \Cx\Core\Html\Model\Entity\HtmlElement('textarea');
                $textarea->setAttribute('name', $name);
                $textarea->addChild(new \Cx\Core\Html\Model\Entity\TextElement($value));
                return $textarea;
                break;
            case 'phone':
                // input field with type phone
                $input = new \Cx\Core\Html\Model\Entity\DataElement($name, $value);
                $input->setAttribute('type', 'phone');
                if (isset($options['readonly']) && $options['readonly']) {
                    $input->setAttribute('disabled');
                }
                return $input;
                break;
            case 'mail':
                // input field with type mail
                $emailValidator = new \Cx\Core\Validate\Model\Entity\EmailValidator();
                $input = new \Cx\Core\Html\Model\Entity\DataElement($name, $value, 'input', $emailValidator);
                $input->setAttribute('onkeyup', $emailValidator->getJavaScriptCode());
                $input->setAttribute('type', 'mail');
                if (isset($options['readonly']) && $options['readonly']) {
                    $input->setAttribute('disabled');
                }
                return $input;
                break;
            case 'string':
            default:
                // input field with type text
                $input = new \Cx\Core\Html\Model\Entity\DataElement($name, $value);
                $input->setAttribute('type', 'text');
                if (isset($options['readonly']) && $options['readonly']) {
                    $input->setAttribute('disabled');
                }
                return $input;
                break;
        }
    }
    
    public function getForm() {
        return $this->form;
    }
    
    public function isValid() {
        return $this->form->isValid();
    }
    
    public function getData() {
        return $this->form->getData();
    }
    
    public function render() {
        return $this->form->render();
    }
    
    public function __toString() {
        return $this->render();
    }
}
