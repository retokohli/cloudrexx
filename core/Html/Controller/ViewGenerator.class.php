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
        $this->options = $options;$entityNS=null;
        if (is_array($object)) {
            $object = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($object);
        }
        \JS::registerCSS(ASCMS_CORE_FOLDER.'/Html/View/Style/Backend.css');
        if ($object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            // render table if no parameter is set
            $this->object = $object;
        } else {
            if (!is_object($object)) {
                throw new ViewGeneratorException('Cannot generate view for variable type ' . gettype($object));
            }
            // render form
            $this->object = $object;
        }
        // get entity name space
        $entityNS = $this->object->getDataType();
        /** 
         *  postSave event
         *  execute save if entry is a doctrine entity (or execute callback if specified in configuration)
         */
        if (isset($_POST['editid']) && !empty($entityNS)) {
            // render form for editid
            $entityId = contrexx_input2raw($_POST['editid']);
            $form = $this->renderFormForEntry($entityId);
            // form->isValid()?
            if ($form === false) {
                // cannot save, no such entry
                \Message::add('Cannot save, no such entry', \Message::CLASS_ERROR);
                return;
            }
            if (!$form->isValid()) {
                // data validation failed, stay in edit view
                \Message::add('Cannot save, validation failed', \Message::CLASS_ERROR);
                $_GET['editid'] = $_POST['editid'];
                return;
            }
            
            $entityObject = $this->object->getEntry($entityId);
            if (empty($entityObject)) {
                \Message::add('Cannot save, Invalid entry', \Message::CLASS_ERROR);
                return;
            }
            $isUpdate=false; $id=0; $updateArray=array();
            $classMethods = get_class_methods(new $entityNS());
            foreach ($entityObject as $name=>$value) {
                if (isset ($_POST[$name])) { 
                    if ($_POST[$name] != $value) {
                        $isUpdate=true;
                        if (in_array('set'.ucfirst($name), $classMethods)) {
                            $updateArray['set'.ucfirst($name)]=$_POST[$name];
                        }
                    } 
                } elseif (in_array('get'.ucfirst($name), $classMethods) && !in_array('set'.ucfirst($name), $classMethods)) {
                    $id=$entityObject[$name];
                }
            }  
            if (!empty($updateArray) && !empty($id) 
                && !empty($isUpdate)) {
                $entityObj=\Env::get('em')->getRepository($entityNS)->find($id);
                if (!empty($entityObj)) {
                    foreach($updateArray as $key=>$value) {
                        $entityObj->$key($value);
                    }
                    \Env::get('em')->flush();    
                    \Message::add('Entity have been updated sucessfully!');   
                } else {
                    \Message::add('Cannot save, Invalid argument!', \Message::CLASS_ERROR);
                }
            } 
            \CSRF::redirect(\Env::get('cx')->getRequest());
        }
        /**
         * TODO:
         * - trigger pre- and postRemove event
         * - execute remove if entry is a doctrine entity (or execute callback if specified in configuration)
         */
        if (isset($_POST['deleteid']) && !empty($entityNS)) {
        }
    }
    
    public function render(&$isSingle = false) {
        $renderObject = $this->object;
        $entityClass = get_class($this->object);
        if (
            $this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet
            && isset($_GET['editid'])
        ) {
            $entityClass = $this->object->getDataType();
            $entityId = contrexx_input2raw($_GET['editid']);
            if ($this->object->entryExists($entityId)) {
                $renderObject = $this->object->getEntry($entityId);
            }
        }
        
        if ($renderObject instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            if (!count($renderObject) || !count(current($renderObject))) {
                // make this configurable
                $tpl = new \Cx\Core\Html\Sigma(ASCMS_CORE_PATH.'/Html/View/Template/Generic');
                $tpl->loadTemplateFile('NoEntries.html');
                return $tpl->get();
            }
            $listingController = new \Cx\Core_Modules\Listing\Controller\ListingController($renderObject);
            $renderObject = $listingController->getData();
            return new \BackendTable($renderObject, $this->options) . '<br />' . $listingController;
        } else {
            $isSingle = true;
            return $this->renderFormForEntry($entityId);
        }
    }
    
    protected function renderFormForEntry($entityId) {
        $renderObject = $this->object;
        $entityClass = get_class($this->object);
        if ($this->object instanceof \Cx\Core_Modules\Listing\Model\Entity\DataSet) {
            $entityClass = $this->object->getDataType();
            if (!$this->object->entryExists($entityId)) {
                // no such entry
                return false;
            }
            $renderObject = $this->object->getEntry($entityId);
        }
        $actionUrl = clone \Env::get('cx')->getRequest();
        $actionUrl->setParam('editid', null);
        return new FormGenerator($renderObject, $actionUrl, $entityClass, $this->options);
    }
    
    public function __toString() {
        try {
            return (string) $this->render();
        } catch (\Exception $e) {
            echo $e->getMessage();die();
        }
    }
}
