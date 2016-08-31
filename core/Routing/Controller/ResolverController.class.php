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
class ResolverController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    
    public function resolve() {
        $page = new \Cx\Core\ContentManager\Model\Entity\Page();
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $page = $this->cx->getRequest()->getUrl()->getPage(); // could be any type of page including alias
                
                // redirect to virtual language dir
                if (
                    $page->getType() != \Cx\Core\ContentManager\Model\Entity\Page::TYPE_ALIAS &&
                    $this->cx->getRequest()->getUrl()->getLanguageCode(false) === null
                ) {
                    $redirectUrl = \Cx\Core\Routing\Model\Entity\Url::fromDocumentRoot(null, 1);
                    \Cx\Core\Csrf\Controller\CSRF::redirect($redirectUrl);
                }
                
                $page = $this->adjust($page);
                $this->getPreviewPage();
                
                // this is legacy:
                define('FRONTEND_LANG_ID', $page->getLang());
                break;
            case \Cx\Core\Core\Controller\Cx::MODE_COMMAND:
            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                $url = $this->cx->getRequest()->getUrl();
                $url->getComponent();
                $url->getArguments();
                break;
        }
        // sub-resolving
        // re-resolve if necessary
        //$page = $this->adjust($page);
        // canonical header

        return $page;
    }
    
    /**
     * Returns the preview page built from the session page array.
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
    
    protected function adjust($page) {
        // alias, redirect and symlink resolving:
        
        // TODO:
        // - Redirect to error if page not found (seems to work already somehow)
        // - command mode / api
        $isAdjusting = true;
        while ($isAdjusting) {
            if (!$page->hasAccess()) {
                $link = base64_encode($this->cx->getRequest()->getUrl()->toString());
                \Cx\Core\Csrf\Controller\Csrf::header('Location: '.\Cx\Core\Routing\Model\Entity\Url::fromModuleAndCmd('Login', '', '', array('redirect' => $link)));
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
            if (!$page || !$page->isTargetInternal()) { // type redirect (internal or external)
                $isAdjusting = false;
                \Cx\Core\Csrf\Controller\CSRF::redirect($page->getTarget());
                
            // internal redirect
            } else { // types symlink and fallback
                $em = $this->cx->getDb()->getEntityManager();
                $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
                $page = $pageRepo->getTargetPage($page);
            }
        }
        return $page;
    }
    
    public function getUrl() {
        return $this->cx->getRequest()->getUrl();
    }
}

