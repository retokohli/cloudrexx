<?php
/**
 * Event-listener for Routing/PageNotFound-event
 *
 * @copyright CLOUDREXX *MS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @version 5
 * @package cloudrexx
 * @subpackage core_modules_error
 */

namespace Cx\Core\Error\Controller;

/**
 * Exception to skip the resolving of the current page/component
 *
 * @copyright CLOUDREXX *MS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @version 5
 * @package cloudrexx
 * @subpackage core_modules_error
 */
class SkipResolverException extends \Exception {}

/**
 * Event-listener for Routing/PageNotFound-event
 *
 * @copyright CLOUDREXX *MS - CLOUDREXX AG
 * @author Cloudrexx Development Team <info@cloudrexx.com>
 * @access public
 * @version 5
 * @package cloudrexx
 * @subpackage core_modules_error
 */

class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController implements \Cx\Core\Event\Model\Entity\EventListener
{
    /**
     * @var \Cx\Core\Routing\Resolver Resolver which triggered the event
     */
    protected $resolver;

    /**
     * @var string The section of the page if it is a component-page
     */
    protected $section = '';

    /**
     * @var string The cmd if one is set
     */
    private $cmd = '';

    /**
     * @var \Cx\Core\ContentManager\Model\Entity\Page The page which might only be inactive
     */
    private $inactivePage = null;

    /**
     * @param $eventName String The name of the Event
     * @param array $eventArgs
     * @throws \Exception
     */
    public function onEvent($eventName, array $eventArgs) {
        if (preg_match("#/#", $eventName)) {
            $eventName = explode("/", $eventName)[1];
        }
        $eventName = lcfirst($eventName);
        if (!method_exists($this, $eventName)) {
            return;
        }

        $this->$eventName($eventArgs);
    }

    /**
     * @TODO: Check what happens when this is called in load or preContentLoad $eventArgs might need a stage-value
     * @param array $eventArgs
     * @throws \Cx\Core\Error\Controller\SkipResolverException
     */
    private function pageNotFound(array $eventArgs) {
        global $page, $plainSection;

        // display a sexy message in the stacktrace
        \DBG::stack();

        // might not be used if we don't change anything in the $eventArgs-array
        $this->section = $eventArgs['section'];
        $this->cmd = $eventArgs['cmd'];
        $this->inactivePage = $eventArgs['page'];
        $history = $eventArgs['history'];
        $this->resolver = $eventArgs['resolver'];

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
        // throw exception so that the resolver stops resolving gets caught in \Cx\Core\Core\Controller\Cx.class.php
        throw new \Cx\Core\Error\Controller\SkipResolverException();
    }

    /**
     * Use this to parse your frontend page
     *
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     * @return null
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        // is a component-page
        if (!isset($this->section)) {
            $template->hideBlock('error_module_information');
            $template->hideBlock('error_module_description');
            $template->hideBlock('error_module_name');
            return;
        }

        // load language data of given component
        $componentLang = \Env::get('init')->getComponentSpecificLanguageData($this->section, false, FRONTEND_LANG_ID);

        $noDescription = !isset($componentLang['TXT_' . strtoupper($this->section) . 'MODULE_DESCRIPTION']) ? true : false;

        // prepare the template
        $template->touchBlock('error_module_information');
        $template->touchBlock('error_module_name');
        // only parse the description-block if a description is available
        if ($noDescription) {
            $template->hideBlock('error_module_description');
        }
        $template->touchBlock('error_module_description');
        // only replace the description if one exists
        if($noDescription) {
            $template->setVariable(array(
                'ERROR_MODULE_DESCRIPTION' => $componentLang['TXT_'
                . strtoupper($this->section) . 'MODULE_DESCRIPTION']
            ));
        }
        // replace the variables
        $template->setVariable(array(
            'ERROR_MODULE_NAME' => $this->section
        ));

    }
}