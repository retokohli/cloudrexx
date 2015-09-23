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
    const ERROR_REASON_PAGE_NOT_FOUND = 'page not found';

    /**
     * @var string
     */
    const ERROR_REASON_NOT_LICENSED = 'not licensed';

    /**
     * @var \Cx\Core\Routing\Resolver Resolver which triggered the event
     */
    protected $resolver;

    /**
     * @var string The section of the page if it is a component-page
     */
    protected $section = '';

    /**
     * @var string reason why the event was triggered
     */
    protected $reason = '';

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
     * Handles the event of a missing, deactivated or not licensed component or page respectively.
     *
     * Loads the page-not-found-page and sets it as resolvedPage (@see \Cx\Core\Core\Controller\Cx::setPage($page))
     * Swaps the missing, deativated or not licensed component with the error-component
     * Sets the HTTP/1.0 404-header
     *
     * @TODO: Check what happens when this is called in load or preContentLoad $eventArgs might need a stage-value to abort this method if it is already too late
     * @param array $eventArgs The arguments supplied while triggering the event
     * @throws \Cx\Core_Modules\Error\Controller\SkipResolverException to stop resolving the faulty page or component
     */
    private function pageNotFound(array $eventArgs) {
        global $page, $plainSection;

        // display a sexy message in the stacktrace
        \DBG::stack();

        // get the information about missing or deactivated component / page
        $history = $eventArgs['history'];
        $this->section = $eventArgs['section'];
        $this->resolver = $eventArgs['resolver'];
        $this->reason = $eventArgs['reason'];

        // Load the content of the error-page
        $pageRepo = $this->cx->getDb()->getEntityManager()->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $errorPage = $pageRepo->findOneByModuleCmdLang('Error', '', FRONTEND_LANG_ID);

        // @TODO: Remove this as soon as global $page is no longer in use! Update global $page to the error-page
        $page = $errorPage;
        // @TODO: Remove this as soon as global $plainSection is no longer in use! Update global $plainSectino to Error
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
        if ($this->reason == $this::ERROR_REASON_PAGE_NOT_FOUND || $this->reason == $this::ERROR_REASON_NOT_LICENSED) {
            // throw exception so that the resolver stops resolving gets caught in \Cx\Core\Core\Controller\Cx.class.php
            throw new \Cx\Core_Modules\Error\Controller\SkipResolverException();
        }
    }

    /**
     * Parses the page-not-found-page.
     *
     * If the faulty page is a normal page, no component-information are shown.
     * If the user has not sufficient permissions to install a component, he won't see the instructions to do so.
     * A description of a component is only shown when the description could be found.
     *
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     * @param string $cmd The cmd of the resolved page
     * @return null Return only when we have a normal page and no information shall be displayed
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        global $_ARRAYLANG;

        // Default content
        $template->touchBlock('error_default_explanation');
        $template->setVariable(array(
            'ERROR_DEFAULT_TITLE'       => $_ARRAYLANG['TXT_ERROR_DEFAULT_TITLE'],
            'ERROR_SEARCH_NAME'         => $_ARRAYLANG['TXT_ERROR_SEARCH_NAME'],
            'ERROR_HOME_PAGE_NAME'      => $_ARRAYLANG['TXT_ERROR_HOME_PAGE_NAME'],
            'ERROR_EXPLANATION_GERMAN'  => $_ARRAYLANG['TXT_ERROR_EXPLANATION_GERMAN'],
            'ERROR_EXPLANATION_ENGLISH' => $_ARRAYLANG['TXT_ERROR_EXPLANATION_ENGLISH']
        ));

        // is a component-page
        $systemComponentRepo = $this->cx->getDb()->getEntityManager()->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');
        if (empty($this->section) || empty($systemComponentRepo->findOneBy(array('name' => ucfirst($this->section))))) {
            $template->hideBlock('error_module_information');
            $template->hideBlock('error_module_description');
            $template->hideBlock('error_module_name');
            $template->hideBlock('error_module_installation_instructions');
            return;
        }

        // load language data of the core to display Component information
        $coreLang = \Env::get('init')->getComponentSpecificLanguageData('Core', false, FRONTEND_LANG_ID);

        // check if a description is available
        $noDescription = !isset($coreLang['TXT_' . strtoupper($this->section) . '_MODULE_DESCRIPTION']) ? true : false;
        // check if user has permission to install a module
        $noAccess = !(\FWUser::getFWUserObject()->objUser->login(true) && \Permission::checkAccess(52, 'static', true));

        // prepare the template
        $template->touchBlock('error_module_information');
        $template->touchBlock('error_module_name');

        // only parse the description-block if a description is available
        if ($noDescription) {
            $template->hideBlock('error_module_description');
        } else {
            $template->touchBlock('error_module_description');
        }
        if ($noAccess || $this->reason == $this::ERROR_REASON_PAGE_NOT_FOUND) {
            $template->hideBlock('error_module_installation_instructions');
        } else {
            $template->touchBlock('error_module_installation_instructions');
        }

        // only replace the description if one exists
        if(!$noDescription) {
            $template->setVariable(array(
                'ERROR_MODULE_DESCRIPTION' => $coreLang['TXT_' . strtoupper($this->section) . '_MODULE_DESCRIPTION']
            ));
        }

        // only show installation instructions when the user has the permissions to and the component was not found
        if(!$noAccess) {
            $template->setVariable(array(
                'ERROR_MODULE_INSTALLATION_GUIDE_TITLE' => $_ARRAYLANG['TXT_ERROR_MODULE_INSTALLATION_GUIDE_TITLE'],
                'ERROR_BACKEND_URL' => $this->getBackendUrl(),
                'ERROR_BACKEND_NAME' => $_ARRAYLANG['TXT_ERROR_BACKEND_NAME'],
                'ERROR_COMPONENT_MANAGER_URL' => $this->getBackendUrl('ComponentManager', 'edit'),
                'ERROR_COMPONENT_CONTROLLER_NAME' => $_ARRAYLANG['TXT_ERROR_COMPONENT_MANAGER_NAME']
            ));

            for ($i = 1; $i <= 3; $i++) {
                // replace the placeholder (%s) with the component-name
                $template->setVariable('ERROR_MODULE_INSTALLATION_GUIDE_PART_' . $i,
                    nl2br(sprintf($_ARRAYLANG['TXT_ERROR_MODULE_INSTALLATION_GUIDE_PART_' . $i], $this->section))
                );
            }
        }

        // replace the variables
        $template->setVariable(array(
            'ERROR_MODULE_NAME' => $this->section
        ));

    }

    /**
     * Get the backend URL
     *
     * Creates a Url using {@link \Cx\Core\Routing\Url::fromDocumentRoot()} and sets the url-params for component, cmd
     * and csrf.
     *
     * @param string $component Name of the component needs to be the formatted value (CamelCase)
     * @param string $cmd
     * @return string The absolute URL
     */
    private function getBackendUrl($component = '', $cmd = '') {
        $backendUrl = \Cx\Core\Routing\Url::fromDocumentRoot();
        $backendUrl->setMode('backend');
        $backendUrl->setPath($this->cx->getBackendFolderName() . '/');
        $backendUrl->setParams(array(
            'module' => isset($component) ? $component : '',
            'cmd' => isset($cmd) ? $cmd : '',
            \Cx\Core\Csrf\Controller\Csrf::key() => \Cx\Core\Csrf\Controller\Csrf::code(),
        ));

        return $backendUrl->toString();
    }
}