<?php
/**
 * Main controller for Routing
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing\Controller;

/** 
 * Main controller for Routing
 * 
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array('Backend');
    }

    public function preResolve(\Cx\Core\Routing\Url $url) {
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
        if ($originalUrl->toString() != $url->toString()) {
            \Cx\Core\Csrf\Controller\Csrf::header('Location: ' . $url->toString(), true, $rewriteRule->getRewriteStatusCode());
            die();
        }
    }    
}
