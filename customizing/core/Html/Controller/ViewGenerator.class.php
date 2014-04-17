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
            
            /**
             * TODO:
             * - trigger pre- and postSave event
             * - execute save if entry is a doctrine entity (or execute callback if specified in configuration)
             * 
             * - trigger pre- and postRemove event
             * - execute remove if entry is a doctrine entity (or execute callback if specified in configuration)
             * 
             * - trigger pre- and postCreate event
             * - execute create if entity class is a doctrine entity (or execute callback if specified in configuration)
             */
            
            //echo '<pre>';var_dump($dataSet->toArray());die();
            // save form data
            // CUSTOMIZING FOR PAYCLOUD.CH
            $instanceUrl = 'https://' . contrexx_input2raw($_POST['editid']) . '.' . substr($_SERVER['SERVER_NAME'], 0);
            $data = $dataSet->toArray();
            
            // modify data for checkout json adapter of instance
            $data['firstName'] = $data['firstname'];
            $data['name'] = $data['lastname'];
            $data['plz'] = $data['zip'];
            $data['ort'] = $data['location'];
            $data['emailId'] = $data['email'];
            $data['phoneNumber'] = $data['phone'];
            $data['poBox'] = $data['pOBox'];
            $data['gender'] = $data['salutation'] === 'Mrs.' ? 1 : 2;
            $data['newsletterStatus'] = $data['newsletterStatus'] === 'yes' ? 1 : 0;
            
            $jd = new \Cx\Core\Json\JsonData();
            $jd->getJson(
                $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=user&act=loginUser',
                array(
                    'USERNAME' => 'O5vie5gOnIMY3Xbi@payrexx.com',
                    'PASSWORD' => 'NwIoOKjujfMlLcMnZCoSxFyZ6hmKnRxa',
                )
            );
            // get new username and password
            $passwordHasChanged = false;
            $username = $data['email'];
            $password = contrexx_input2raw($_POST['password1']);
            if (!empty($_POST['password1']) || !empty($_POST['password2'])) {
                if (empty($_POST['password2']) || $_POST['password1'] != $_POST['password2']) {
                    \Message::add('blabla', \Message::CLASS_ERROR);
                    return;
                }
                $passwordHasChanged = true;
            }
            // set password using access json adapter
            if ($username != '' || $passwordHasChanged) {
                $jd->getJson(
                    $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=user&act=setPassword',
                    array(
                        'userId' => $username,
                        'password' => $password,
                        'repeatPassword' => $password,
                    )
                );
            }
            
            if (isset($_POST['activeStatus'])){
                $jd->getJson(
                    $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=checkout&act=activateInstance'
                );
                $jd->getJson(
                    $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=checkout&act=setMessage',
                    array(
                        'message' => contrexx_input2raw($_POST['instanceMessage']),
                    )
                );
            } else {
                $jd->getJson(
                    $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=checkout&act=disableInstance'
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
            $jd->getJson(
                $instanceUrl.'/cadmin/index.php?cmd=jsondata&object=user&act=logoutUser'
            );
            //
            \CSRF::redirect(\Env::get('cx')->getRequest());
            // END CUSTOMIZING FOR PAYCLOUD.CH
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
                $tpl = new \Cx\Core\Html\Sigma(ASCMS_CORE_PATH.'/Html/View/Template');
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
