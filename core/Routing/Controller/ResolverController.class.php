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
 * Resolving controller
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Controller;

/**
 * Resolving controller
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 */
class ResolverController extends \Cx\Core\Core\Model\Entity\Controller
{
    
    /**
     * Resolves an Url
     *
     * In frontend mode this does the page resolving (alias, fallback, redirect, symlink, permissions, ...)
     * In backend and command mode this forces BackendUrl to find component name and arguments
     * @return \Cx\Core\ContentManager\Model\Entity\Page resolved page (empty for non-frontend)
     */
    public function resolve($url) {
        $page = new \Cx\Core\ContentManager\Model\Entity\Page();
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $page = $url->getPage(); // could be any type of page including alias
                
                // redirect to virtual language dir
                if (
                    $page->getType() != \Cx\Core\ContentManager\Model\Entity\Page::TYPE_ALIAS &&
                    $url->getLanguageCode(false) === null
                ) {
                    $redirectUrl = \Cx\Core\Routing\Model\Entity\Url::fromDocumentRoot(array(), \FWLanguage::getDefaultLangId());
                    \Cx\Core\Csrf\Controller\CSRF::redirect($redirectUrl);
                }
                
                $this->getPreviewPage();
                $page = $this->adjust($page);
                
                // get canonical URL
                $canonicalUrl = $this->getCanonicalUrl($page, $url);
                
                // sub-resolving
                $pageBeforeSubResolve = $page;
                $this->subResolve($page, $url, $canonicalUrl);
                
                // re-resolve if necessary
                if ($pageBeforeSubResolve != $page) {
                    $page = $this->adjust($page);
                }
                
                // set canonical header
                header('Link: <' . $canonicalUrl . '>; rel="canonical"');
                
                // this is legacy:
                define('FRONTEND_LANG_ID', $page->getLang());
                break;
            case \Cx\Core\Core\Controller\Cx::MODE_COMMAND:
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $url->getComponent();
                $url->getArguments();
                break;
        }

        return $page;
    }
    
    /**
     * Returns the preview page built from the session page array.
     *
     * Also replaces the page object in entity manager with the previewed one
     * @return Cx\Core\ContentManager\Model\Entity\Page $page
     */
    protected function getPreviewPage() {
        $url = $this->cx->getRequest()->getUrl();
        if (
            $url->getParam('pagePreview') != '1' &&
            \Permission::checkAccess(6, 'static', true)
        ) {
            return;
        }
        $historyId = $url->getParam('history');
        if (!$historyId) {
            $historyId = 0;
        }
        $session = \cmsSession::getInstance();
        if (!isset($session['page'])) {
            return;
        }
        $data = $session['page']->toArray();

        $em = $this->cx->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $page = $pageRepo->findOneById($data['pageId']);
        if (!$page) {
            $page = new \Cx\Core\ContentManager\Model\Entity\Page();
            $node = new \Cx\Core\ContentManager\Model\Entity\Node();
            $node->setParent($this->nodeRepo->getRoot());
            $node->setLvl(1);
            $this->nodeRepo->getRoot()->addChildren($node);
            $node->addPage($page);
            $page->setNode($node);

            $pageRepo->addVirtualPage($page);
        }
        
        $page->setActive(true);
        $page->setDisplay(true);
        if (in_array($page->getEditingStatus(), array('hasDraft', 'hasDraftWaiting'))) {
            $logRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\LogEntry');
            $logEntries = $logRepo->getLogEntries($page);
            $logRepo->revert($page, $logEntries[1]->getVersion());
        }

        unset($data['pageId']);
        $page->setLang(\FWLanguage::getLanguageIdByCode($data['lang']));
        unset($data['lang']);
        $page->updateFromArray($data);
        $page->setUpdatedAtToNow();
        $page->setActive(true);
        $page->setVirtual(true);
        $page->validate();

        return $page;
    }
    
    /**
     * Alias, fallback, redirect and symlink resolving while respecting permissions
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page to adjust
     * @return \Cx\Core\ContentManager\Model\Entity\Page Adjusted page
     */
    protected function adjust($page) {
        $isAdjusting = true;
        while ($isAdjusting) {
            if (!$page->hasReadAccess()) {
                $link = base64_encode($this->cx->getRequest()->getUrl()->toString());
                \Cx\Core\Csrf\Controller\Csrf::header('Location: '.\Cx\Core\Routing\Model\Entity\Url::fromModuleAndCmd('Login', '', '', array('redirect' => $link)));
            }
            if ($page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK) {
                $em = $this->cx->getDb()->getEntityManager();
                $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
                $page->getFallbackContentFrom($pageRepo->getFallbackPage($page));
                continue;
            }
            $isRedirect = in_array($page->getType(), array(
                \Cx\Core\ContentManager\Model\Entity\Page::TYPE_ALIAS,
                \Cx\Core\ContentManager\Model\Entity\Page::TYPE_REDIRECT,
                \Cx\Core\ContentManager\Model\Entity\Page::TYPE_SYMLINK,
            ));
            if (!$isRedirect) {
                $isAdjusting = false;
                continue;
            }
            // external redirect
            if (
                $page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_REDIRECT ||
                !$page->isTargetInternal()
            ) {
                $isAdjusting = false;
                $target = $page->getTarget();
                if ($page->isTargetInternal()) {
                    $em = $this->cx->getDb()->getEntityManager();
                    $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
                    $target = \Cx\Core\Routing\Model\Entity\Url::fromPage($pageRepo->getTargetPage($page));
                }
                \Cx\Core\Csrf\Controller\CSRF::redirect($target);
                die();
                
            // internal redirect
            } else { // type symlink or alias
                $em = $this->cx->getDb()->getEntityManager();
                $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
                $page = $pageRepo->getTargetPage($page);
            }
        }
        return $page;
    }
    
    /**
     * Returns the canonical URL for this page
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page
     * @param \Cx\Core\Routing\Model\Entity\Url $url Request URL
     * @return \Cx\Core\Routing\Model\Entity\Url Canonical URL
     */
    protected function getCanonicalUrl($page, $url) {
        // do not set a sensful canonical header for application pages yet
        if ($page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION) {
            return $url;
        }
        $canonicalPage = $page;
        if (
            $url->getPage()->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_SYMLINK
        ) {
            $em = $this->cx->getDb()->getEntityManager();
            $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
            $canonicalPage = $this->pageRepo->getTargetPage($url->getPage());
        }
        return \Cx\Core\Routing\Model\Entity\Url::fromPage($canonicalPage);
    }
    
    /**
     * Let's current component resolve additional path parts of the request
     *
     * The component might do something with additional path parts (component intern resolving).
     * Redirects can be done by changing $page. In this case, the page will be re-resolved.
     * The canonical URL can be changed by the component by changing $canonicalUrl.
     * @todo Does this work for fallbacks and aliases?
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page by reference
     * @param \Cx\Core\Routing\Model\Entity\Url $url Request URL
     * @param \Cx\Core\Routing\Model\Entity\Url $canonicalUrl Canonical URL by reference
     */
    protected function subResolve(&$page, $url, &$canonicalUrl) {
        if (
            $page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION &&
            $page->getPath() != $url->getPathWithoutOffsetAndLangDir()
        ) {
            $additionalPath = substr('/' . $url->getPathWithoutOffsetAndLangDir(), strlen($page->getPath()));
            $componentRepo = $this->em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
            $componentController = $componentRepo->findOneBy(
                array(
                    'name' => $page->getModule()
                )
            );
            if ($componentController) {
                $parts = explode('/', substr($additionalPath, 1));
                $componentController->resolve($parts, $page, $canonicalUrl);
            }
        }
    }
    
    /**
     * Returns the resolved Url
     * @deprecated Use $cx->getRequest()->getUrl() instead
     * @return \Cx\Core\Routing\Model\Entity\Url Resolved URL
     */
    public function getUrl() {
        return $this->cx->getRequest()->getUrl();
    }
}
