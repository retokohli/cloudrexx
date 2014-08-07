<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Html\Controller;
/**
 * Description of ViewGenerator
 *
 * @author ritt0r
 */
class ViewGenerator {
    protected $object;
    protected $options;
    
    /**
     *
     * @param mixed $object Array, instance of DataSet, instance of EntityBase, object
     * @param $options is functions array 
     * @throws ViewGeneratorException 
     */
    public function __construct($object, $options = array()) {
        try {
            $this->options = $options;
            $entityNS=null;
            if (is_array($object)) {
                $object = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($object);
            }
            \JS::registerCSS(\Env::get('cx')->getCoreFolderName() . '/Html/View/Style/Backend.css');
            if ($object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
                // render table if no parameter is set
                $this->object = $object;
                $entityNS = $this->object->getDataType();
            } else {
                if (!is_object($object)) {
                    throw new ViewGeneratorException('Cannot generate view for variable type ' . gettype($object));
                }
                // render form
                $this->object = $object;
                $entityNS = get_class($this->object);
            }

            /** 
             *  postSave event
             *  execute save if entry is a doctrine entity (or execute callback if specified in configuration)
             */
            $add=(!empty($_GET['add'])? contrexx_input2raw($_GET['add']):null);
            if (!empty($add) && !empty($this->options['functions']['add'])) {
                $form = $this->renderFormForEntry(null);
                if ($form === false) {
                    // cannot save, no such entry
                    \Message::add('Cannot save, no such entry', \Message::CLASS_ERROR);
                    return;
                }
                if (!$form->isValid()) {
                    // data validation failed, stay in add view
                    \Message::add('Cannot save, validation failed', \Message::CLASS_ERROR);
                    return;
                }
                if (!empty($_POST)) {
                    $post=$_POST;
                    unset($post['csrf']);
                    $blankPost=true;
                    if (!empty($post)) {
                        foreach($post as $value) {
                            if ($value) $blankPost=false;
                        }
                    }
                    if ($blankPost) {
                        \Message::add('Cannot save, You should fill any one field!', \Message::CLASS_ERROR);
                        return;
                    }               
                    $entityObject = \Env::get('em')->getClassMetadata($entityNS);  
                    $primaryKeyName =$entityObject->getSingleIdentifierFieldName(); //get primary key name  
                    $getAllField = $entityObject->getColumnNames(); //get all field names                 

                    // create new entity without calling the constructor
    // TODO: this might break certain entities!
                    $entityObj = $entityObject->newInstance();
                    
                    foreach($getAllField as $entity) {
                        if (isset($_POST[$entity]) && $entity!=$primaryKeyName) {
                            $name='set'.$entity;
                            $entityObj->$name(contrexx_input2raw($_POST[$entity]));
                        }
                    }
                    if ($entityObj instanceof \Cx\Core\Model\Model\Entity\YamlEntity) {
                        $entityRepository = \Env::get('em')->getRepository($entityNS);
                        $entityRepository->add($entityObj);
                        $entityRepository->flush();
                    } else {
                        if (!($entityObj instanceof \Cx\Model\Base\EntityBase)) {
                            \DBG::msg('Unkown entity model '.get_class($entityObj).'! Trying to persist using entity manager...');
                        }
                        \Env::get('em')->persist($entityObj);
                        \Env::get('em')->flush();
                    }
                    \Message::add('Entity have been added sucessfully!');   
                    $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
                    $actionUrl->setParam('add', null);
                    \Cx\Core\Csrf\Controller\Csrf::redirect($actionUrl);
                }
            }
            /** 
             *  postEdit event
             *  execute edit if entry is a doctrine entity (or execute callback if specified in configuration)
             */
           // $entityId = (isset($_POST['editid'])? contrexx_input2raw($_POST['editid']):null);
            if (isset($_POST['editid'])) {
                $entityId = contrexx_input2raw($_POST['editid']);
                // render form for editid
                $form = $this->renderFormForEntry($entityId);
                if ($form === false) {
                    // cannot save, no such entry
                    \Message::add('Cannot save, no such entry', \Message::CLASS_ERROR);
                    return;
                }
                if (!$form->isValid()) {
                    // data validation failed, stay in edit view
                    \Message::add('Cannot save, validation failed', \Message::CLASS_ERROR);
                    return;
                }
                $entityObject=array();
                if ($this->object->entryExists($entityId)) {
                    $entityObject = $this->object->getEntry($entityId);
                }
                if (empty($entityObject)) {
                    \Message::add('Cannot save, Invalid entry', \Message::CLASS_ERROR);
                    return;
                }
                $isUpdate=false; 
                $updateArray=array();
                $entityObj = \Env::get('em')->getClassMetadata($entityNS);
                $primaryKeyName =$entityObj->getSingleIdentifierFieldName(); //get primary key name  
                //$getAllField = $entityObj->getColumnNames(); //get all field names 
                $id=$entityObject[$primaryKeyName]; //get primary key value  
                $classMethods = get_class_methods($entityObj->newInstance());
                foreach ($entityObject as $name=>$value) {
                    if (isset ($_POST[$name])) { 
                        if ($_POST[$name] != $value) {
                            $isUpdate=true;
                            if (in_array('set'.ucfirst($name), $classMethods)) {
                                $updateArray['set'.ucfirst($name)]=contrexx_input2raw($_POST[$name]);
                            }
                        } 
                    }
                }
                if (!empty($updateArray) && !empty($id) 
                    && !empty($isUpdate)) {
                    $entityObj = \Env::get('em')->getRepository($entityNS)->find($id);
                    if (!empty($entityObj)) {
                        foreach($updateArray as $key=>$value) {
                            $entityObj->$key($value);
                        }
                        if ($entityObj instanceof \Cx\Core\Model\Model\Entity\YamlEntity) {
                            \Env::get('em')->getRepository($entityNS)->flush();
                        } else {
                            \Env::get('em')->flush();    
                        }
                        \Message::add('Entity have been updated sucessfully!');   
                    } else {
                        \Message::add('Cannot save, Invalid argument!', \Message::CLASS_ERROR);
                    }
                } 
                $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
                $actionUrl->setParam('editid', null);
                \Cx\Core\Csrf\Controller\Csrf::redirect($actionUrl);
            }
            /**
             * 
             * trigger pre- and postRemove event
             * execute remove if entry is a doctrine entity (or execute callback if specified in configuration)
             */
            $deleteId = contrexx_input2raw($_GET['deleteid']);
            if ($deleteId!='') {
                $entityObject = $this->object->getEntry($deleteId);
                if (empty($entityObject)) {
                    \Message::add('Cannot save, Invalid entry', \Message::CLASS_ERROR);
                    return;
                }
                $entityObj = \Env::get('em')->getClassMetadata($entityNS);  
                $primaryKeyName =$entityObj->getSingleIdentifierFieldName(); //get primary key name  
                $id=$entityObject[$primaryKeyName]; //get primary key value  
                if (!empty($id)) {
                    $entityObj=\Env::get('em')->getRepository($entityNS)->find($id);
                    if (!empty($entityObj)) {
                        if ($entityObj instanceof \Cx\Core\Model\Model\Entity\YamlEntity) {
                            $ymlRepo = \Env::get('em')->getRepository($entityNS);
                            $ymlRepo->remove($entityObj);;
                            $ymlRepo->flush();
                        } else {
                            \Env::get('em')->remove($entityObj);
                            \Env::get('em')->flush();
                        }
                        \Message::add('Entity have been deleted sucessfully!');   
                    }
                }
                $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
                $actionUrl->setParam('deleteid', null);
                \Cx\Core\Csrf\Controller\Csrf::redirect($actionUrl);
            }
        } catch (\Exception $e) {
            \Message::add($e->getMessage());   
            return;
        }
    }
    
    public function render(&$isSingle = false) {
        if (!empty($_GET['add']) 
            && !empty($this->options['functions']['add'])) {
            $isSingle = true;
            return $this->renderFormForEntry(null);
        }
        $renderObject = $this->object;
        $entityClass = get_class($this->object);
        $entityId = '';
        if ($this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet
            && isset($_GET['editid'])) {
            $entityClass = $this->object->getDataType();
            $entityId = contrexx_input2raw($_GET['editid']);
            if ($this->object->entryExists($entityId)) {
                $renderObject = $this->object->getEntry($entityId);
            }
        }
        if ($renderObject instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            $addBtn = '';
            $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
            if (!empty($this->options['functions']['add'])) {
                $actionUrl->setParam('add', 1);
                $addBtn = '<br /><br /><input type="button" name="addEtity" value="Add" onclick="location.href='."'".$actionUrl."&csrf=".\Cx\Core\Csrf\Controller\Csrf::code()."'".'" />'; 
            }
            if (!count($renderObject) || !count(current($renderObject))) {
                // make this configurable
                $tpl = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseCorePath().'/Html/View/Template/Generic');
                $tpl->loadTemplateFile('NoEntries.html');
                return $tpl->get().$addBtn;
            }
            $listingController = new \Cx\Core_Modules\Listing\Controller\ListingController($renderObject, array(), $this->options['functions']);
            $renderObject = $listingController->getData();
            $backendTable = new \BackendTable($renderObject, $this->options) . '<br />' . $listingController;

            return $backendTable.$addBtn;
        } else {
            $isSingle = true;
            return $this->renderFormForEntry($entityId);
        }
    }
    
    protected function renderFormForEntry($entityId) {
        $renderArray=array();
        if ($this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            $entityClass = $this->object->getDataType();
            $entityObject = \Env::get('em')->getClassMetaData($entityClass);
            $primaryKeyName = $entityObject->getSingleIdentifierFieldName(); //get primary key name
            if (!empty($_GET['add']) && !empty($this->options['functions']['add'])) {
                $cancelUrl = clone \Env::get('cx')->getRequest()->getUrl();
                $cancelUrl->setParam('add', null);
                $this->options['cancelUrl'] = $cancelUrl;
                $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
                $title='Add Entity';
                $actionUrl->setParam('add', 1);
                $getAllField = $entityObject->getColumnNames(); //get all field names  
                if (empty($getAllField)) return false;
                foreach($getAllField as $name) {
                    if ($name!=$primaryKeyName) {
                        $renderArray[$name]="";    
                    }
                }
            } else {
                if (!$this->object->entryExists($entityId)) return false;
                $cancelUrl = clone \Env::get('cx')->getRequest()->getUrl();
                $cancelUrl->setParam('editid', null);
                $this->options['cancelUrl'] = $cancelUrl;
                $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
                $title='Edit Entity';
                $actionUrl->setParam('editid', null);
                $renderObject = $this->object->getEntry($entityId);
                if (empty($renderObject)) return false;
                foreach($renderObject as $name=>$value) {
                    if ($name!=$primaryKeyName) {
                        $renderArray[$name]=$value;
                    }
                }
            }
        } else {
            $entityClass = get_class($this->object);
            $entityObject = \Env::get('em')->getClassMetadata($entityClass);  
            $primaryKeyName =$entityObject->getSingleIdentifierFieldName(); //get primary key name
            $cancelUrl = clone \Env::get('cx')->getRequest()->getUrl();
            $cancelUrl->setParam('add', null);
            $this->options['cancelUrl'] = $cancelUrl;
            $actionUrl = clone \Env::get('cx')->getRequest()->getUrl();
            $title='Add Entity';
            $actionUrl->setParam('add', 1);
            $getAllField = $entityObject->getColumnNames(); //get all field names  
            if (empty($getAllField)) return false;
            foreach($getAllField as $name) {
                if ($name!=$primaryKeyName) {
                    $renderArray[$name]="";    
                }
            }
        }        
        return new FormGenerator($renderArray, $actionUrl,$title, $this->options);
    }
    
    public function __toString() {
        try {
            return (string) $this->render();
        } catch (\Exception $e) {
            echo $e->getMessage();die();
        }
    }
}
