<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
/**
 * Calendar
 *  
 * @package    cloudrexx
 * @subpackage module_calendar
 * @author     Cloudrexx <info@cloudrexx.com>
 * @copyright  CLOUDREXX CMS - CLOUDREXX AG
 * @version    1.00
 */

namespace Cx\Modules\Calendar\Controller;
/**
 * Calendar
 * 
 * Calendar Class Registration
 * 
 * @package    cloudrexx
 * @subpackage module_calendar
 * @author     Cloudrexx <info@cloudrexx.com>
 * @copyright  CLOUDREXX CMS - CLOUDREXX AG
 * @version    1.00
 */ 
class CalendarRegistration extends CalendarLibrary
{
    /**
     * Registration id
     *
     * @access public
     * @var integer 
     */
    public $id; 
    
    /**
     * Event Id
     *
     * @access public
     * @var integer 
     */
    public $eventId;  
    
    /**
     * Event date
     *
     * @access public
     * @var integer Timestamp of Event date
     */
    public $eventDate; 
    
    /**
     * User id
     *
     * @access public
     * @var interger 
     */
    public $userId;   
    
    /**
     * Language Id
     *
     * @access public
     * @var integer
     */
    public $langId; 
    
    /**
     * Type
     *
     * @access public
     * @var integer
     */
    public $type; 
    
    /**
     * Host name
     *
     * @access public
     * @var string
     */
    public $hostName; 
    
    /**
     * User Ip address
     *
     * @access public
     * @var string
     */
    public $ipAddress;
    
    /**
     * Reg Key
     *
     * @access public
     * @var string 
     */
    public $key;     
    
    /**
     * First Export time
     *
     * @access public
     * @var integer 
     */
    public $firstExport;
    
    /**
     * Paymend method
     *
     * @access public
     * @var integer
     */
    public $paymentMethod;
    
    /**
     * Payment status
     *
     * @access public
     * @var interger
     */
    public $paid;
    
    /**
     * Save In
     *
     * @access public
     * @var integer 
     */
    public $saveIn;
    
    /**
     * Fields
     *
     * @access public
     * @var array 
     */
    public $fields = array(); 
    
    /**
     * Registration form object
     *
     * @access private
     * @var object 
     */
    private $form;
    
    /**
     * Constructor for registration class
     * 
     * Loads the form object from CalendarForm class
     * IF the $id is not null load the register object for the given id
     * 
     * @param integer $formId Registration Form Id
     * @param integer $id     Registration id
     */
    function __construct($formId, $id=null){              
        $objForm = new \Cx\Modules\Calendar\Controller\CalendarForm(intval($formId));
        $this->form = $objForm;     
        
        if ($id != null) {
            self::get($id);
        }
        $this->init();
    }
    
    /**
     * Loads the registration by id
     *      
     * @param integer $regId Registration id
     * 
     * @return null
     */
    function get($regId) {
        global $objDatabase, $_LANGID;    
        
        $query = 'SELECT registration.`id` AS `id`,
                         registration.`event_id` AS `event_id`,
                         registration.`date` AS `date`,
                         registration.`host_name` AS `host_name`,
                         registration.`ip_address` AS `ip_address`,
                         registration.`type` AS `type`,
                         registration.`key` AS `key`,
                         registration.`user_id` AS `user_id`,
                         registration.`lang_id` AS `lang_id`,
                         registration.`export` AS `first_export`,
                         registration.`payment_method` AS `payment_method`,
                         registration.`paid` AS `paid`
                   FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration AS registration
                   WHERE registration.`id` = "'.$regId.'"
                   LIMIT 1';   
        
        $objResult = $objDatabase->Execute($query);  
        
        if($objResult !== false) {
            $this->id = intval($objResult->fields['id']);
            $this->eventId = intval($objResult->fields['event_id']);           
            $this->eventDate = intval($objResult->fields['date']);        
            $this->userId= intval($objResult->fields['user_id']);        
            $this->langId= intval($objResult->fields['lang_id']);        
            $this->type = intval($objResult->fields['type']);        
            $this->hostName = htmlentities($objResult->fields['host_name'], ENT_QUOTES, CONTREXX_CHARSET);      
            $this->ipAddress = htmlentities($objResult->fields['ip_address'], ENT_QUOTES, CONTREXX_CHARSET);        
            $this->key = htmlentities($objResult->fields['key'], ENT_QUOTES, CONTREXX_CHARSET);          
            $this->firstExport = intval($objResult->fields['first_export']);
            $this->paymentMethod = intval($objResult->fields['payment_method']);
            $this->paid = intval($objResult->fields['paid']);
            
            foreach ($this->form->inputfields as $key => $arrInputfield) {         
                $name = $arrInputfield['name'][$_LANGID];
                $default = $arrInputfield['default_value'][$_LANGID];
                
                $queryField = 'SELECT field.`value` AS `value`
                                 FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_value AS field
                                WHERE field.`reg_id` = "'.$regId.'" AND
                                      field.`field_id` = "'.intval($arrInputfield['id']).'"
                                LIMIT 1';
                $objResultField = $objDatabase->Execute($queryField);          
                
                if($objResultField !== false) {
                     $this->fields[$arrInputfield['id']]['name']    =  $name;
                     $this->fields[$arrInputfield['id']]['type']    =  $arrInputfield['type'];
                     $this->fields[$arrInputfield['id']]['value']   =  htmlentities($objResultField->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
                     $this->fields[$arrInputfield['id']]['default'] =  $default;  
                }   
            } 
        }       
    }
    
    /**
     * Save the registration
     *      
     * @param array $data posted data from the form
     * 
     * @return boolean true if the registration saved, false otherwise
     */
    function save($data)
    {
        global $objDatabase, $objInit, $_LANGID;
        
        /* foreach ($this->form->inputfields as $key => $arrInputfield) {
            if($arrInputfield['type'] == 'selectBillingAddress') { 
                $affiliationStatus = $data['registrationField'][$arrInputfield['id']];
            }
        } */
        
        foreach ($this->form->inputfields as $key => $arrInputfield) {
            /* if($affiliationStatus == 'sameAsContact') {
                if($arrInputfield['required'] == 1 && empty($data['registrationField'][$arrInputfield['id']]) && $arrInputfield['affiliation'] != 'billing') {
                    return false;
                } 
            
                if($arrInputfield['required'] == 1 && $arrInputfield['type'] == 'mail' && $arrInputfield['affiliation'] != 'billing') {
                    $objValidator = new FWValidator();
                    
                    if(!$objValidator->isEmail($data['registrationField'][$arrInputfield['id']])) {
                        return false;    
                    }
                }
            } else { */
                if($arrInputfield['required'] == 1 && empty($data['registrationField'][$arrInputfield['id']])) {
                    return false;
                } 
            
                if($arrInputfield['required'] == 1 && $arrInputfield['type'] == 'mail') {
                    $objValidator = new \FWValidator();
                    
                    if(!$objValidator->isEmail($data['registrationField'][$arrInputfield['id']])) {
                        return false;    
                    }
                }
            /* } */
        }
        
        $regId = intval($data['regid']);
        $eventId = intval($data['id']);
        $formId = intval($data['form']);
        $eventDate = intval($data['date']);
        $userId = intval($data['userid']);
        
        $objEvent = new \Cx\Modules\Calendar\Controller\CalendarEvent($eventId);

        if (   $objInit->mode == \Cx\Core\Core\Controller\Cx::MODE_BACKEND
            && $objEvent->seriesStatus
            && $objEvent->independentSeries
        ) {
            $eventDate = isset($data['registrationEventDate']) ? contrexx_input2int($data['registrationEventDate']) : $eventDate;
        }

        $query = '
            SELECT
                `id`
            FROM
                `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field`
            WHERE
                `form` = '. $formId .'
            AND
                `type` = "seating"
            LIMIT 1
        ';
        $objResult = $objDatabase->Execute($query);
        
        $numSeating = intval($data['registrationField'][$objResult->fields['id']]);
        $type       =   empty($regId) && intval($objEvent->getFreePlaces() - $numSeating) < 0
                      ? 2 : (isset($data['registrationType']) ? intval($data['registrationType']) : 1);
        $this->saveIn = intval($type);
        $paymentMethod = intval($data['paymentMethod']);
        $paid = intval($data['paid']);
        $hostName = 0;
        $ipAddress = 0;
        $key = $this->generateKey();

        $formFieldValues = $this->getRegistrationFormFieldValueAsArray($data);
        $formData = array(
            'fields' => array(
                'date'          => $eventDate,
                'hostName'      => $hostName,
                'ipAddress'     => $ipAddress,
                'type'          => $type,
                'key'           => $key,
                'userId'        => $userId,
                'langId'        => $_LANGID,
                'paymentMethod' => $paymentMethod,
                'paid'          => $paid
            ),
            'relation' => array(
                'event'           => $eventId,
                'formFieldValues' => $formFieldValues
            )
        );
        $registration = $this->getRegistrationEntity($regId, $formData);
        if ($regId == 0) {
            $registration->setExport(0);
            //Trigger prePersist event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/prePersist',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
            $query = 'INSERT INTO '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration
                                  (`event_id`,`date`,`host_name`,`ip_address`,`type`,`key`,`user_id`,`lang_id`,`export`,`payment_method`,`paid`)
                           VALUES ("'.$eventId.'","'.$eventDate.'","'.$hostName.'","'.$ipAddress.'","'.$type.'","'.$key.'","'.$userId.'","'.$_LANGID.'",0,"'.$paymentMethod.'","'.$paid.'")';
            
            $objResult = $objDatabase->Execute($query);
            
            if ($objResult !== false) {
                $this->id = $objDatabase->Insert_ID();
                $registration = $this->getRegistrationEntity($this->id);
            } else {
                return false;
            }
        } else {
            //Trigger preUpdate event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/preUpdate',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
            $query = 'UPDATE `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration`
                         SET `event_id` = '.$eventId.',
                             `date` = '.$eventDate.',
                             `host_name` = '.$hostName.',
                             `ip_address` = '.$ipAddress.',
                             `key` = "'.$key.'",
                             `user_id` = '.$userId.',
                             `type`    = '.$type.',
                             `lang_id` = '.$_LANGID.',
                             `payment_method` = '.$paymentMethod.',
                             `paid` = '.$paid.'
                       WHERE `id` = '.$regId;
            
            $objResult = $objDatabase->Execute($query);

            if ($objResult === false) {
                return false;
            }
        }

        if ($regId != 0) {
            $this->id = $regId;
            $formFieldValueEntities = $registration->getRegistrationFormFieldValues();
            foreach ($formFieldValueEntities as $formFieldValueEntity) {
                //Trigger preRemove event for RegistrationFormFieldValue Entity
                $this->cx->getEvents()->triggerEvent(
                    'model/preRemove',
                    array(
                        new \Doctrine\ORM\Event\LifecycleEventArgs(
                            $formFieldValueEntity, $this->em
                        )
                    )
                );
            }
            $deleteQuery = 'DELETE FROM '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_value
                            WHERE `reg_id` = '.$this->id;
            
            $objDeleteResult = $objDatabase->Execute($deleteQuery);
            
            if ($objDeleteResult === false) {
                return false;
            } else {
                foreach ($formFieldValueEntities as $formFieldValueEntity) {
                    //Trigger postRemove event for RegistrationFormFieldValue Entity
                    $this->cx->getEvents()->triggerEvent(
                        'model/postRemove',
                        array(
                            new \Doctrine\ORM\Event\LifecycleEventArgs(
                                $formFieldValueEntity, $this->em
                            )
                        )
                    );
                }
            }
        }

        $formFieldRepo = $this
            ->em
            ->getRepository('Cx\Modules\Calendar\Model\Entity\RegistrationFormField');
        foreach ($formFieldValues  as $formFieldId => $formFieldValue) {
            $formData = array(
                'fieldId'    => $formFieldId,
                'fieldValue' => $formFieldValue
            );
            $formFieldValueEntity = $this->getFormFieldValueEntity(
                $registration, $formFieldRepo, $formData
            );
            //Trigger prePersist event for RegistrationFormFieldValue Entity
            $this->cx->getEvents()->triggerEvent(
                'model/prePersist',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $formFieldValueEntity, $this->em
                    )
                )
            );

            $query = 'INSERT INTO '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_value
                        (`reg_id`, `field_id`, `value`)
                        VALUES (' . $this->id . ', ' . $formFieldId . ', "' . $formFieldValue . '")';
            $objResult = $objDatabase->Execute($query);

            if ($objResult === false) {
                return false;
            } else {
                //Trigger postPersist event for RegistrationFormFieldValue Entity
                $this->cx->getEvents()->triggerEvent(
                    'model/postPersist',
                    array(
                        new \Doctrine\ORM\Event\LifecycleEventArgs(
                            $formFieldValueEntity, $this->em
                        )
                    )
                );
            }
        }

        if ($regId == 0) {
            //Trigger postPersist event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/postPersist',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
        } else {
            //Trigger postUpdate event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/postUpdate',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
        }

        if ($objInit->mode == 'frontend') {
            $objMailManager = new \Cx\Modules\Calendar\Controller\CalendarMailManager();
            
            $templateId     = $objEvent->emailTemplate[FRONTEND_LANG_ID];
            $objMailManager->sendMail(intval($_REQUEST['id']), \Cx\Modules\Calendar\Controller\CalendarMailManager::MAIL_CONFIRM_REG, $this->id, $templateId);
            
            $objMailManager->sendMail(intval($_REQUEST['id']), \Cx\Modules\Calendar\Controller\CalendarMailManager::MAIL_ALERT_REG, $this->id);
        }
        
        return true;
    }

    /**
     * Get registration form field value as array
     *
     * @param array $data post data
     *
     * @return array the array of field values
     */
    public function getRegistrationFormFieldValueAsArray($data)
    {
        if (empty($data)) {
            return null;
        }

        $formFieldValues = array();
        foreach ($this->form->inputfields as $key => $arrInputfield) {
            $value = $data['registrationField'][$arrInputfield['id']];
            $id    = $arrInputfield['id'];

            if (is_array($value)) {
                $subvalue = array();
                foreach ($value as $key => $element) {
                    $additionalField = $data['registrationFieldAdditional'][$id][$element-1];
                    $subvalue[] = !empty($additionalField)
                        ? $element . '[[' . $additionalField . ']]' : $element;
                }
                $value = join(',', $subvalue);
            } else {
                $additionalField = $data['registrationFieldAdditional'][$id][$value-1];
                if (isset($additionalField)) {
                    $value = $value . '[[' . $additionalField . ']]';
                }
            }

            $formFieldValues[$id] = contrexx_input2db($value);
        }

        return $formFieldValues;
    }

    /**
     * Delete the registration
     *      
     * @param integer $regId Registration id
     * 
     * @return boolean true if data deleted, false otherwise
     */
    function delete($regId)
    {
        global $objDatabase; 

        if (!empty($regId)) {
            $registration = $this->getRegistrationEntity($regId);
            //Trigger preRemove event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/preRemove',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );

            $query = '
                DELETE FROM `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration`
                WHERE `id` = '.intval($regId);
            $objResult = $objDatabase->Execute($query);
            
            if ($objResult !== false) {
                $formFieldValueEntities = $registration->getRegistrationFormFieldValues();
                foreach ($formFieldValueEntities as $formFieldValueEntity) {
                    //Trigger preRemove event for RegistrationFormFieldValue Entity
                    $this->cx->getEvents()->triggerEvent(
                        'model/preRemove',
                        array(
                            new \Doctrine\ORM\Event\LifecycleEventArgs(
                                $formFieldValueEntity, $this->em
                            )
                        )
                    );
                }
                $query = '
                    DELETE FROM `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration_form_field_value`
                    WHERE `reg_id` = '.intval($regId)
                ;
                $objResult = $objDatabase->Execute($query);
                
                if ($objResult !== false) {
                    foreach ($formFieldValueEntities as $formFieldValueEntity) {
                        //Trigger postRemove event for RegistrationFormFieldValue Entity
                        $this->cx->getEvents()->triggerEvent(
                            'model/postRemove',
                            array(
                                new \Doctrine\ORM\Event\LifecycleEventArgs(
                                    $formFieldValueEntity, $this->em
                                )
                            )
                        );
                    }
                    //Trigger postRemove event for Registration Entity
                    $this->cx->getEvents()->triggerEvent(
                        'model/postRemove',
                        array(
                            new \Doctrine\ORM\Event\LifecycleEventArgs(
                                $registration, $this->em
                            )
                        )
                    );
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Update the registration to the given type
     *      
     * @param integer $regId  Registration id
     * @param integer $typeId Type Id
     * 
     * @return boolean true if registration updated, false otherwise
     */
    function move($regId, $typeId)
    {
        global $objDatabase, $_LANGID;

        if (!empty($regId)) {
            $registration = $this
                ->em
                ->getRepository('Cx\Modules\Calendar\Model\Entity\Registration')
                ->findOneBy(array('id' => $regId, 'langId' => $_LANGID));
            $registration->setType($typeId);
            $registration->setVirtual(true);
            //Trigger preUpdate event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/preUpdate',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
            $query = '
                UPDATE `'.DBPREFIX.'module_'.$this->moduleTablePrefix.'_registration`
                SET `type` = '.$typeId.'
                WHERE `id` = '.$regId.'
                AND `lang_id` = '.$_LANGID
            ;
            $objResult = $objDatabase->Execute($query);

            if ($objResult !== false) {
                //Trigger postUpdate event for Registration Entity
                $this->cx->getEvents()->triggerEvent(
                    'model/postUpdate',
                    array(
                        new \Doctrine\ORM\Event\LifecycleEventArgs(
                            $registration, $this->em
                        )
                    )
                );
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Update the export date into the registration
     *      
     * @return boolean true if date updated sucessfully, false otherwise
     */
    function tagExport()
    {
        global $objDatabase, $_LANGID;

       $now = time();

        if (intval($this->id) != 0) {
            $registration = $this->getRegistrationEntity(
                $this->id, array('fields' => array('export' => $now))
            );
            //Trigger preUpdate event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/preUpdate',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
            $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_registration SET `export` = '".intval($now)."' WHERE `id` = '".intval($this->id)."'";              
            $objResult = $objDatabase->Execute($query);     
            if($objResult !== false) {
                $this->firstExport = $now;
                //Trigger postUpdate event for Registration Entity
                $this->cx->getEvents()->triggerEvent(
                    'model/postUpdate',
                    array(
                        new \Doctrine\ORM\Event\LifecycleEventArgs(
                            $registration, $this->em
                        )
                    )
                );
                return true;  
            } else {
                return false;
            }  
        }
    }

    /**
     * Updatete the payment status
     *      
     * @param integer $payStatus payment status
     * 
     * @return null
     */
    function setPaid($payStatus = 0)
    {
        global $objDatabase;

        $registration = $this->getRegistrationEntity(
            $this->id, array('fields' => array('paid' => $payStatus))
        );
        //Trigger preUpdate event for Registration Entity
        $this->cx->getEvents()->triggerEvent(
            'model/preUpdate',
            array(
                new \Doctrine\ORM\Event\LifecycleEventArgs(
                    $registration, $this->em
                )
            )
        );
        $query = '
                    UPDATE `'.DBPREFIX.'module_calendar_registration` AS `r`
                    SET `paid` = ? WHERE `id` = ?
                ';
        $objResult = $objDatabase->Execute($query, array($payStatus, $this->id));
        if ($objResult !== false) {
            //Trigger postUpdate event for Registration Entity
            $this->cx->getEvents()->triggerEvent(
                'model/postUpdate',
                array(
                    new \Doctrine\ORM\Event\LifecycleEventArgs(
                        $registration, $this->em
                    )
                )
            );
        }
    }

    /**
     * Get registration entity
     *
     * @param integer $id        registration id
     * @param array   $formDatas registration field values
     *
     * @return \Cx\Modules\Calendar\Model\Entity\Registration
     */
    public function getRegistrationEntity($id, $formDatas)
    {
        if (empty($id)) {
            $registration = new \Cx\Modules\Calendar\Model\Entity\Registration();
        } else {
            $registration = $this
                ->em
                ->getRepository('Cx\Modules\Calendar\Model\Entity\Registration')
                ->findOneById($id);
        }
        $registration->setVirtual(true);

        if (!$registration) {
            return null;
        }

        if (!$formDatas) {
            return $registration;
        }
        //Set registration field values
        foreach ($formDatas['fields'] as $fieldName => $fieldValue) {
            $methodName = 'set'.ucfirst($fieldName);
            if (method_exists($registration, $methodName)) {
                $registration->{$methodName}($fieldValue);
            }
        }

        $relations = $formDatas['relation'];
        if (!$relations) {
            return $registration;
        }

        $formFieldRepo = $this
            ->em
            ->getRepository('Cx\Modules\Calendar\Model\Entity\RegistrationFormField');
        $eventRepo = $this
            ->em->getRepository('Cx\Modules\Calendar\Model\Entity\Event');
        //Set Registration event
        if (    $relations['event']
            &&  (   (   $registration->getEvent()
                    &&  ($registration->getEvent()->getId() != $relations['event'])
                    )
                || (!($registration->getEvent()) && $relations['event'])
                )
        ) {
            $event = $eventRepo->findOneById($relations['event']);
            $event->setVirtual(true);
            if ($event) {
                $registration->setEvent($event);
                $event->addRegistration($registration);
            }
        }

        //Set Registration formfield values
        if ($relations['formFieldValues']) {
            foreach ($relations['formFieldValues'] as $fieldId => $fieldValue) {
                $formData = array('fieldId' => $fieldId, 'fieldValue' => $fieldValue);
                $this->getFormFieldValueEntity($registration, $formFieldRepo, $formData);
            }
        }

        return $registration;
    }

    /**
     * Get form field value entity
     *
     * @param \Cx\Modules\Calendar\Model\Entity\Registration $registration  registration entity
     * @param object                                         $formFieldRepo formfield repository
     * @param array                                          $fieldValues   field values
     *
     * @return \Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue
     */
    public function getFormFieldValueEntity(
        \Cx\Modules\Calendar\Model\Entity\Registration $registration,
        $formFieldRepo,
        $fieldValues
    ){
        $isNewEntity    = false;
        $formFieldValue = $registration->getRegistrationFormFieldValueByFieldId(
            $fieldValues['fieldId']
        );
        if (!$formFieldValue) {
            $isNewEntity    = true;
            $formFieldValue = new \Cx\Modules\Calendar\Model\Entity\RegistrationFormFieldValue();
        }
        $formFieldValue->setVirtual(true);
        $formField = $formFieldValue->getRegistrationFormField();
        if (    (   $formField
                &&  ($formField->getId() != $fieldValues['fieldId'])
                )
            ||  (!$formField && $fieldValues['fieldId'])
        ) {
            $formField = $formFieldRepo->findOneById($fieldValues['fieldId']);
            $formField->setVirtual(true);
            if ($formField) {
                $formFieldValue->setRegistrationFormField($formField);
                $formField->addRegistrationFormFieldValue($formFieldValue);
            }
        }
        $formFieldValue->setValue($fieldValues['fieldValue']);
        if ($isNewEntity) {
            $registration->addRegistrationFormFieldValue($formFieldValue);
            $formFieldValue->setRegistration($registration);
        }

        return $formFieldValue;
    }
}