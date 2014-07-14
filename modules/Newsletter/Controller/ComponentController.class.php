<?php
/**
 * Main controller for Newsletter
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  modules_newsletter
 */

namespace Cx\Modules\Newsletter\Controller;

/**
 * Main controller for Newsletter
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  modules_newsletter
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

     /**
     * Load your component.
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_CORELANG, $objTemplate, $subMenuTitle;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $newsletter = new Newsletter(\Env::get('cx')->getPage()->getContent());
                \Env::get('cx')->getPage()->setContent($newsletter->getPage());
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'content_master.html');
                $objTemplate = $this->cx->getTemplate();
                
                $subMenuTitle = $_CORELANG['TXT_CORE_EMAIL_MARKETING'];
                $objNewsletter = new NewsletterManager();
                $objNewsletter->getPage();
                break;
        }
    }

    /**
     * Do something after resolving is done
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postResolve(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $command;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                if (Newsletter::isTrackLink()) {
                    //handle link tracker from newsletter, since user should be redirected to the link url
                    /*
                    * Newsletter Module
                    *
                    * Generates no output, requests are answered by a redirect to foreign site
                    *
                    */
                    Newsletter::trackLink();
                    //execution should never reach this point, but let's be safe and call exit anyway
                    exit;
                } elseif ($command == 'displayInBrowser') {
                    Newsletter::displayInBrowser();
                    //execution should never reach this point, but let's be safe and call exit anyway
                    exit;
                }
                // regular newsletter request (like subscribing, profile management, etc).
                // must not abort by an exit call here!
                break;
        }
    }

    /**
     * Do something before content is loaded from DB
     * 
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function preContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $newsletter, $_ARRAYLANG, $page_template, $themesPages, $objInit;
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // get Newsletter
                $_ARRAYLANG = array_merge($_ARRAYLANG, $objInit->loadLanguageData('Newsletter'));
                $newsletter = new Newsletter('');
                $content = \Env::get('cx')->getPage()->getContent();
                if (preg_match('/{NEWSLETTER_BLOCK}/', $content)) {
                    $newsletter->setBlock($content);
                }
                if (preg_match('/{NEWSLETTER_BLOCK}/', $page_template)) {
                    $newsletter->setBlock($page_template);
                }
                if (preg_match('/{NEWSLETTER_BLOCK}/', $themesPages['index'])) {
                    $newsletter->setBlock($themesPages['index']);
                }
                break;
        }
    }
}