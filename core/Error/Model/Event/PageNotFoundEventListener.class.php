<?php

/**
 * Handles the Routing/PageNotFound Event
 */

namespace Cx\Core\Error\Model\Event;

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
class PageNotFoundEventListener implements \Cx\Core\Event\Model\Entity\EventListener
{
    /**
     * @var \Cx\Core\Core\Controller\Cx Cx-object
     */
    protected $cx;

    /**
     * @var \Cx\Core\Routing\Resolver Resolver-object
     */
    protected $resolver;
    /**
     * @param \Cx\Core\Core\Controller\Cx $cx
     */
    public function __construct(\Cx\Core\Core\Controller\Cx $cx) {
        $this->cx = $cx;
    }

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
            throw new \Exception(get_class($this) . " not prepared for " . $eventName);
        } else {
            $this->$eventName($eventArgs);
        }
    }

    /**
     * @TODO: Check what happens when this is called in load or preContentLoad $eventArgs might need a stage-value
     * @param array $eventArgs
     * @throws SkipResolverException
     */
    private function pageNotFound(array $eventArgs) {
        global $page;
        // might not be used if we don't change anything in the $eventArgs-array
        $section = $eventArgs['section'];
        $cmd = $eventArgs['cmd'];
        $missingPage = $eventArgs['page'];
        $history = $eventArgs['history'];
        $this->resolver = $eventArgs['resolver'];

        // Load the content of the error-page
        $pageRepo = $this->cx->getDb()->getEntityManager()->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $errorPage = $pageRepo->findOneByModuleCmdLang('Error', '', FRONTEND_LANG_ID);

        // @TODO: Remove this as soon as global $page is no longer in use!
        $page = $errorPage;

        // setup the corresponding template
        $this->resolver->setTemplateBasedOnPage($errorPage);
        // check if the error-page has restricted access
        $this->resolver->checkFrontendAccess($errorPage, $history);

        // get the content
        $pageContent = $errorPage->getContent();
        $this->cx->setPage($errorPage);
        header("HTTP/1.0 404 Not Found");
        throw new \Cx\Core\Error\Model\Event\SkipResolverException();
    }
}