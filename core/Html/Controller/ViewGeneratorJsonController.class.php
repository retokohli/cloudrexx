<?php
/**
 * This file is used for the ViewGeneratorJsonController of the core html
 *
 * @copyright CONTREXX CMS - COMVATION AG
 * @author Adrian Berger <ab@comvation.com>
 * @package contrexx
 * @subpackage core_html
 * @version 1.0.0
 */
namespace Cx\Core\Html\Controller;

/**
 * This class handles all requests to ViewGenerator, which are submitted over ajax
 * This class is also an entity controller and implements JsonApadter
 *
 * @copyright CONTREXX CMS - COMVATION AG
 * @author Adrian Berger <ab@comvation.com>
 * @package contrexx
 * @subpackage core_html
 * @version 1.0.0
 */
class ViewGeneratorJsonController extends \Cx\Core\Core\Model\Entity\Controller implements \Cx\Core\Json\JsonAdapter {


    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        // at the moment we only allow backend users to edit ViewGenerator over json/ajax.
        // As soon as we have permissions on entity level we can change this, so getViewOverJson can also be used from frontend
        return array(
            'getViewOverJson' => new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, true, null, null,
                function () {
                    $objUser = \FWUser::getFWUserObject()->objUser->getUser($_SESSION->userId);
                    $objBackendGroups = \FWUser::getFWUserObject()->objGroup->getGroups(
                        array('is_active' => true, 'type' => 'backend'),
                        null,
                        array('group_id')
                    );
                    while (!$objBackendGroups->EOF) {
                        if(in_array($objBackendGroups->getId(), $objUser->getAssociatedGroupIds())){
                            return true;
                        }
                        $objBackendGroups->next();
                    }
                    return false;
                }
            ),
        );
    }

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return parent::getName();
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return '';
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return null;
    }

    /**
     * Always returns the add view of an entity
     * This is mostly used for oneToMany associations to load the associated entity (in a model)
     *
     * @access public
     * @param array $params data from ajax request
     * @return json rendered form
     */
    public function getViewOverJson($params)
    {
        $entityClass = $params['get']['entityClass'];
        $entityClassObject = new $entityClass();
        $mappedBy = $params['get']['mappedBy'];
        $options = $_SESSION['vgOptions'][$params['get']['sessionKey']];

        // add must always be true, otherwise we have no change to open the view, because it is not allowed
        $options->recursiveOffsetSet(true, $entityClass.'/functions/add');

        // formButtons should not be set over ViewGenerator, because they are set over modal (js) and we do not want to
        // load them twice. Furthermore there should be no save button, because the entry should be saved if the main
        // for gets stored and not in the modal.
        $options->recursiveOffsetSet(false, $entityClass.'/functions/formButtons');

        // We never show the mapped-attribute, because it should not be possible to change this.
        // The value of this field must always be the id of the main form entry.
        // This will automatically be done by ViewGenerator while saving the main entry
        $options->recursiveOffsetSet(false, $entityClass.'/fields/'.$mappedBy.'/showDetail');


        $entityClassObjectView = new \Cx\Core\Html\Controller\ViewGenerator(
            $entityClassObject,
            $options->toArray() // must be array and not recursiveArrayAccess object
        );
        return $entityClassObjectView->render();
    }
}
