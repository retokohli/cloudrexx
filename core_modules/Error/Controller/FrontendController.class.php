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
 * FrontendController of Error-Component and Event-listener for Routing/PageNotFound-event.
 *
 * The Event-listener provides the functionality for pages or components which could not be resolved for either:
 * * Component isn't licensed (@see \Cx\Core_Modules\License\ComponentController::postResolve())
 * * Page or Component is inactive (@see \Cx\Core\Routing\Resolver::resolve())
 * * Page or Compoent couldn't be found (@see \Cx\Core\Routing\Resolver:.resolve())
 *
 * The FrontendController parses the page-not-found-page
 *
 * @copyright CLOUDREXX *MS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @package cloudrexx
 * @subpackage coremodule_error
 * @version 1.0.0
 */

namespace Cx\Core_Modules\Error\Controller;

/**
 * Exception to skip the resolving of the current page/component.
 *
 * The Event-listener changes the resolved page and component to be the error-component and the page-not-found-page.
 * Therefore the resolver must stop resolving after triggering the event
 *
 * @copyright CLOUDREXX *MS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @package cloudrexx
 * @subpackage coremodule_error
 * @version 1.0.0
 */
class SkipResolverException extends \Exception {}

/**
 * FrontendController of Error-Component and Event-listener for Routing/PageNotFound-event.
 *
 * The Event-listener provides the functionality for pages or components which could not be resolved for either:
 * * Component isn't licensed (@see \Cx\Core_Modules\License\ComponentController::postResolve())
 * * Page or Component is inactive (@see \Cx\Core\Routing\Resolver::resolve())
 * * Page or Compoent couldn't be found (@see \Cx\Core\Routing\Resolver:.resolve())
 *
 * The FrontendController parses the page-not-found-page
 *
 * @copyright CLOUDREXX *MS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @package cloudrexx
 * @subpackage coremodule_error
 * @version 1.0.0
 */

class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController implements \Cx\Core\Event\Model\Entity\EventListener
{
    /**
     * @var string
     */
    const ERROR_STATE_RESOLVE = 'resolve';

    /**
     * @var string
     */
    const ERROR_STATE_POST_RESOLVE = 'postResolve';

    /**
     * @var \Cx\Core\Routing\Resolver Resolver which triggered the event
     */
    protected $resolver;

    /**
     * @var string The section of the page if it is a component-page
     */
    protected $section = '';

    /**
     * @var string state in which the event was triggered
     */
    protected $state = '';

    /**
     * Resolves all events which the error-component has been registered as handler.
     *
     * @param string $eventName The name of the Event
     * @param array $eventArgs The arguments supplied
     */
    public function onEvent($eventName, array $eventArgs) {
        // Check if the event-name contains a forward-slash
        if (preg_match("#/#", $eventName)) {
            // and only use the second part of it since that is the actual event-name
            $eventName = explode("/", $eventName)[1];
        }

        // make sure that the event-name is CCL
        $eventName = lcfirst($eventName);

        // check if the event-handler for the event exists
        if (!method_exists($this, $eventName)) {
            return;
        }

        $this->$eventName($eventArgs);
    }

    /**
     * Handles the event of a missing, deactivated or not licensed component or
     * page respectively.
     *
     * Loads the page-not-found-page and sets it as resolvedPage(see \Cx\Core\Core\Controller\Cx::setPage($page))
     * Swaps the missing, deactivated or not licensed component with the error-component
     * Sets the HTTP/1.0 404-header
     *
     * @TODO: Check what happens when this is called in load or preContentLoad $eventArgs might need a stage-value to abort this method if it is already too late
     * @param array $eventArgs The arguments supplied while triggering the event
     * @throws \Cx\Core_Modules\Error\Controller\SkipResolverException to stop resolving the faulty page or component
     */
    private function pageNotFound(array $eventArgs) {
        global $page, $plainSection;

        // display a message in the stacktrace
        \DBG::stack();

        // get the information about missing or deactivated component / page
        $history = $eventArgs['history'];
        $this->section = $eventArgs['section'];
        $this->resolver = $eventArgs['resolver'];
        $this->state = $eventArgs['state'];

        // Load the content of the error-page
        $pageRepo = $this->cx->getDb()->getEntityManager()->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $errorPage = $pageRepo->findOneByModuleCmdLang('Error', '', FRONTEND_LANG_ID);

        // @TODO: Remove this as soon as global $page is no longer in use! Update global $page to the error-page
        $page = $errorPage;
        // @TODO: Remove this as soon as global $plainSection is no longer in use! Update global $plainSection to Error
        $plainSection = $errorPage->getModule();

        // setup the corresponding template
        $this->resolver->setTemplateBasedOnPage($errorPage);
        // check if the error-page has restricted access
        $this->resolver->checkFrontendAccess($errorPage, $history);

        // update the page-object
        $this->cx->setPage($errorPage);

        // set 404 page not found http-header
        header("HTTP/1.0 404 Not Found");

        // only throw the SkipResolverException when whe are resolving or postResolving
        if ($this->state == $this::ERROR_STATE_RESOLVE ||
            $this->state == $this::ERROR_STATE_POST_RESOLVE
        ) {
            // throw exception so that the resolver stops resolving! Gets caught
            // in \Cx\Core\Core\Controller\Cx.class.php
            throw new \Cx\Core_Modules\Error\Controller\SkipResolverException();
        }
    }

    /**
     * Parses the page-not-found-page.
     *
     * If the faulty page is a normal page, no component-information are shown.
     * If the user has not sufficient permissions to install a component, he won't see the instructions to do so.
     * A description of a component is only shown when the description could be found and the user is logged in.
     *
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     * @param string $cmd The cmd of the resolved page
     * @return null Return only when we have a normal page and no information shall be displayed
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        global $_ARRAYLANG;

        // LinkParser replaced a component-node with the page-not-found-url and
        // added the initial component as GET param
        if (!empty($_REQUEST['initialComponent'])) {
            $this->section = $_REQUEST['initialComponent'];
        }

        // Default content
        $template->touchBlock('error_default_explanation');
        $template->setVariable(array(
            'ERROR_DEFAULT_TITLE'       => $_ARRAYLANG['TXT_ERROR_DEFAULT_TITLE'],
            'ERROR_SEARCH_NAME'         => $_ARRAYLANG['TXT_ERROR_SEARCH_NAME'],
            'ERROR_HOME_PAGE_NAME'      => $_ARRAYLANG['TXT_ERROR_HOME_PAGE_NAME'],
            'ERROR_EXPLANATION_GERMAN'  => $_ARRAYLANG['TXT_ERROR_EXPLANATION_GERMAN'],
            'ERROR_EXPLANATION_ENGLISH' => $_ARRAYLANG['TXT_ERROR_EXPLANATION_ENGLISH']
        ));

        // Check if the user is logged in to the frontend
        $loggedIn = \FWUser::getFWUserObject()->objUser->login();

        // display only the default component if no section is given, the given
        // section is not a valid component or the user is not logged in
        // (at least frontend) to be able to see the component information
        $systemComponentRepo = $this->cx->getDb()->getEntityManager()->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');
        if (empty($this->section)
            || empty($systemComponentRepo->findOneBy(array('name' => $this->section)))
            || !$loggedIn
        ) {
            $template->hideBlock('error_module_information');
            $template->hideBlock('error_module_name');
            $template->hideBlock('error_module_description');
            $template->hideBlock('error_module_installation_instructions');
            return;
        }

        // is a component-page and the user is logged in

        // load language data of the core to display Component information
        $coreLang = \Env::get('init')->getComponentSpecificLanguageData(
            'Core',
            false,
            FRONTEND_LANG_ID
        );

        // check if a description is available
        $noDescription = !isset($coreLang['TXT_' . strtoupper($this->section) . '_MODULE_DESCRIPTION']) ? true : false;
        // check if user has permission to install a module
        $noAccess = !(\FWUser::getFWUserObject()->objUser->login(true) && \Permission::checkAccess(52, 'static', true));

        // prepare the template
        $blocks = array(
            'error_module_information'                  => 'set',
            'error_module_description'                  => 'set',
            'error_module_installation_instructions'    => 'set',
        );

        // We wouldn't be here if there wasn't a component name
        $template->touchBlock('error_module_name');

        // get the actual name of the component
        $component = $systemComponentRepo->findOneBy(array('name' => $this->section))->getSystemComponent();
        $this->section = $component->getName();

        // replace the variable to list the name
        $template->setVariable(array(
            'ERROR_MODULE_NAME' => $this->section
        ));

        // hide the description block if no description is available
        if ($noDescription) {
            $blocks['error_module_description'] = 'unset';
        }

        // hide the installation instruction if the component isn't licensed or
        // the user hasn't enough rights to add new components
        if ($noAccess || !$component->isActive()) {
            $blocks['error_module_installation_instructions'] = 'unset';
        }

        // hide or touch the blocks
        foreach ($blocks as $name => $set) {
            if ($set === 'unset') {
                $template->hideBlock($name);
                continue;
            }

            $template->touchBlock($name);
        }

        // only replace the description if one exists and the user is logged in
        if(!$noDescription && $loggedIn) {
            $template->setVariable(array(
                'ERROR_MODULE_DESCRIPTION' => $coreLang['TXT_' . strtoupper($this->section) . '_MODULE_DESCRIPTION']
            ));
        }

        // only show installation instructions when the user has the permissions to
        if(!$noAccess) {
            $template->setVariable(array(
                'ERROR_MODULE_INSTALLATION_GUIDE_TITLE' => $_ARRAYLANG['TXT_ERROR_MODULE_INSTALLATION_GUIDE_TITLE'],
                'ERROR_BACKEND_URL' => $this->getBackendUrl(false),
                'ERROR_BACKEND_NAME' => $_ARRAYLANG['TXT_ERROR_BACKEND_NAME'],
                'ERROR_COMPONENT_MANAGER_URL' => $this->getBackendUrl(true, 'ComponentManager', 'edit'),
                'ERROR_COMPONENT_CONTROLLER_NAME' => $_ARRAYLANG['TXT_ERROR_COMPONENT_MANAGER_NAME']
            ));

            for ($i = 1; $i <= 3; $i++) {
                // replace the placeholder (%s) with the component-name
                $template->setVariable('ERROR_MODULE_INSTALLATION_GUIDE_PART_' . $i,
                    nl2br(sprintf($_ARRAYLANG['TXT_ERROR_MODULE_INSTALLATION_GUIDE_PART_' . $i], $this->section))
                );
            }
        }
    }

    /**
     * Get the backend URL with the
     *
     * Creates an url using {@link \Cx\Core\Routing\Url::fromDocumentRoot()} and
     * sets the url-params for cmd and act
     *
     * @param boolean $setParams If the cmd and act params shall be added
     * @param string $component Name of the component needs to be the formatted value (CamelCase)
     * @param string $act The act-param value
     * @return string The absolute URL
     */
    private function getBackendUrl($setParams = true, $component = '', $act = '' ) {
        // create new url from documentroot
        $backendUrl = \Cx\Core\Routing\Url::fromDocumentRoot();
        // set mode to backend
        $backendUrl->setMode('backend');
        // get the backend folder name and remove the leading slash (if there is one)
        $backendFolderName = ltrim($this->cx->getBackendFolderName(), '/');
        // set the path to the backend folder
        $backendUrl->setPath($backendFolderName . '/');

        // check if params shall be added (if empty cmd is set for loading the dashboard, it crashes)
        if ($setParams) {
            // add the params
            $backendUrl->setParams(array(
                'cmd' => isset($component) ? $component : '',
                'act' => isset($act) ? $act : '',
            ));
        }

        // return the absolute url to the backend folder
        return $backendUrl->toString();
    }
}