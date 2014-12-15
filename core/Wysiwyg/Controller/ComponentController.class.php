<?php
/**
 * @copyright   Comvation AG
 * @author      Sebastian Brand <sebastian.brand@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 */

namespace Cx\Core\Wysiwyg\Controller;

class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {
    
    public function getControllerClasses() {
        return array('Backend');
    }
    
    public function getWysiwygTempaltes() {
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $em->getRepository('Cx\Core\Wysiwyg\Model\Entity\Wysiwyg');
        $allWysiwyg = $repo->findBy(array('inactive'=>'0'));
        $containerArr = array();
        foreach ($allWysiwyg as $wysiwyg) {
            $containerArr[] = array(
                'title' => $wysiwyg->getTitle(),
                'image' => $wysiwyg->getImagePath(),
                'description' => $wysiwyg->getDescription(),
                'html' => $wysiwyg->getHtmlContent(),
            );
        }
        
        return json_encode($containerArr);
    }
    
}
