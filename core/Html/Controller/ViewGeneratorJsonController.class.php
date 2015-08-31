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
        return array(
            'getViewOverJson',
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
     * Returns default permission as object
     * @param array $params data from ajax request
     * @return json rendered form
     */
    public function getViewOverJson($params)
    {
        $entityClassObject = new $params['get']['entityClass']();
        $entityClassObjectView = new \Cx\Core\Html\Controller\ViewGenerator($entityClassObject,
            array(
                'functions' => array(
                    'add' => true,
                    'formButtons' => false,
                ),
                'fields' => array(
                    $params['get']['mappedBy'] => array(
                        'showDetail' => false,
                    )
                )
            )
        );
        return $entityClassObjectView->render();
    }
}
