<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Modules\Skeleton\Controller;

class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController {
    
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $bla = new \Cx\Modules\Skeleton\Controller\FrontendController();
        
        $this->cx->getEvents()->addModelListener(
            \Doctrine\ORM\Events::postPersist,
            '\\Cx\\Modules\\Skeleton\\Model\\Entity\\Test',
            $this
        );
    }
}
