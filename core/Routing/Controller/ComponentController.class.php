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
 * Main controller for Routing
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Controller;

/** 
 * Main controller for Routing
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_routing
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array('Backend', 'Resolver');
    }

    /**
     * Do something before resolving is done
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Routing\Model\Entity $url The URL object for this request
     */
    public function preResolve(\Cx\Core\Routing\Model\Entity\Url $url) {
        if ($this->cx->getMode() != \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }
        
        // rewrite rules:
        $em = $this->cx->getDb()->getEntityManager();
        $rewriteRuleRepo = $em->getRepository($this->getNamespace() . '\\Model\\Entity\\RewriteRule');
        $rewriteRules = $rewriteRuleRepo->findAll(array(), array('order'=>'asc'));
        $last = false;
        $originalUrl = clone $url;
        foreach ($rewriteRules as $rewriteRule) {
            try {
                $url = $rewriteRule->resolve($url, $last);
            } catch (\Exception $e) {
                // This is thrown if the regex of the rule is not valid
            }
            if ($last) {
                break;
            }
        }
        if ((string) $originalUrl != (string) $url) {
            \Cx\Core\Csrf\Controller\Csrf::header('Location: ' . $url->toString(), true, $rewriteRule->getRewriteStatusCode());
            die();
        }
        
        // legacy resolving:
        if (!$url->hasParam('section')) {
            return;
        }
        if ($url->getParam('section') == 'logout') {
            global $sessionObj, $objFWUser;
            if (empty($sessionObj)) {
                $sessionObj = \cmsSession::getInstance();
            }
            if ($objFWUser->objUser->login()) {
                $objFWUser->logout();
            }
        }

        $em = $this->cx->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        // If the database uses a case insensitive collation, $section needn't be the exact component name to find a page
        $cmd = $url->getParam('cmd');
        if (!$cmd) {
            $cmd = '';
        }
        $page = $pageRepo->findOneByModuleCmdLang($url->getParam('section'), $cmd, /*FRONTEND_LANG_ID*/1);
        if (!$page) {
            return;
        }
        // TODO: this should be an internal redirect!
        $redirectUrl = \Cx\Core\Routing\Url::fromPage($page);
        \Cx\Core\Csrf\Controller\CSRF::redirect($redirectUrl);
    }    
}
