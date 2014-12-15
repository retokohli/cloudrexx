<?php

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;
use Cx\Core_Modules\MediaBrowser\Model\MediaBrowser;
use Cx\Core_Modules\Uploader\Model\Uploader;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class BackendController extends
    \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{

    /**
     * Act param for the URL Reguest;
     *
     * @var string $act
     */
    protected $act = '';

    /**
     * @var \Cx\Core\Html\Sigma
     */
    protected $template;

    /**
     * @var String
     */
    protected $submenuName;

    /**
     * Returns a list of available commands (?act=XY)
     *
     * @return array List of acts
     */
    public function getCommands()
    {
        return array(

        );
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     *
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array               $cmd      CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        $this->template = $template;

        $uploader = new Uploader();
        $uploader->setFinishedCallback(
            '\Cx\Core_Modules\Uploader\Model\DefaultUploadCallback'
        );
        $uploader->setCallback('callback3');
        $template->setVariable(
            'UPLOADER_CODE', $uploader->getXHtml('Open Uploader 1')
        );

        $uploader2 = new Uploader();
        $uploader2->setFinishedCallback(
            '\Cx\Core_Modules\Uploader\Model\DefaultUploadCallback'
        );
        $uploader2->setOptions(array('data-on-file-uploaded' => 'callback2'));
        $template->setVariable(
            'UPLOADER_CODE2', $uploader2->getXHtml('Open Uploader 2')
        );

        $mediaBrowser = new MediaBrowser();
        $mediaBrowser->setCallback('callback');
        $template->setVariable(
            'MEDIABROWSER_CODE1', $mediaBrowser->getXHtml('MediaBrowser')
        );
        $template->setVariable(
            'MEDIABROWSER_CODE1_RAW',
            htmlspecialchars($mediaBrowser->getXHtml('MediaBrowser'))
        );


        // get the act
        $act = $cmd[0];
        // get the submenu of act
        $this->submenuName = $this->getSubmenuName($cmd);
        // initiat the right controller
        // DELETE FOR PRODUCTION
        // Controller routes all calls to undeclared methods to your
        // ComponentController. So you can do things like
        //$this->getName();
        // DELETE FOR PRODUCTION
        // Trigger the specific controller
        $this->routeToController($act);
    }


    /**
     * Trigger a controller according the act param from the url
     *
     * @param   string $act
     */
    public function routeToController($act)
    {
        $act = ucfirst($act);
        if (!empty($act)) {
            $controllerName = __NAMESPACE__ . '\\' . $act . 'Controller';
            if (!$controllerName && !class_exists($controllerName)) {
                return;
            } else {

            }
            //  instantiate the view specific controller
            $objController = new $controllerName(
                $this->getSystemComponentController(), $this->cx
            );
        } else {
            // instantiate the default View Controller
            $objController = new DefaultController(
                $this->getSystemComponentController(), $this->cx
            );
        }
        $objController->parsePage($this->template);

    }

    /**
     * Returns the sub menu page if the url contains any
     *
     * @param   array $cmd
     *
     * @return  string
     */
    protected function getSubmenuName($cmd)
    {
        if (count($cmd) > 1) {
            $submenu = ucfirst($cmd[1]);
            return $submenu;
        }
        return null;
    }
}
