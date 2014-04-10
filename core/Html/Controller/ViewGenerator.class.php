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
     * @throws ViewGeneratorException 
     */
    public function __construct($object, $options = array()) {
        $this->options = $options;
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
        
        if (isset($_POST['editid'])) {
            \DBG::activate(DBG_PHP);
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
            // get form data
            $dataSet = $form->getData();
            echo '<pre>';var_dump($dataSet->toArray());die();
            // save form data
            // CUSTOMIZING FOR PAYCLOUD.CH
            $instanceUrl = 'http://' . contrexx_input2raw($_GET['editid']) . '.' . $_SERVER['SERVER_NAME'];
            $data = $dataSet->toArray();
            
            $jd = new \Cx\Core\Json\JsonData();
            $jd->getJson(
                $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=access&act=login',
                array(
                    'username' => '',
                    'password' => '',
                )
            );
            // get new username and password
            $passwordHasChanged = false;
            $username = $data['email'];
            $password = '';
            // set password using access json adapter
            if ($username != $username || $passwordHasChanged) {
                $jd->getJson(
                    $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=access&act=chUser',
                    array(
                        'username' => $username,
                        'password' => $password,
                    )
                );
            }
            // unset password in rest of data
            //unset($data['email']);
            //unset($data['password']);
            // submit rest of data
            $jd->getJson(
                $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=checkout&act=saveMasterData',
                $data
            );
            // END CUSTOMIZING FOR PAYCLOUD.CH
        }
    }
    
    public function render() {
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
            return new \BackendTable($renderObject, $this->options);
        } else {
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
