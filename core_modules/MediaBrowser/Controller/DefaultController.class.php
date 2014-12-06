<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

/**
 * DefaultController Description
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class DefaultController extends \Cx\Core\Core\Model\Entity\Controller
{

    /**
     * DefaultController for the DefaultView
     *
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController
     * @param \Cx\Core\Core\Controller\Cx                          $cx
     * @param \Cx\Core\Html\Sigma                                  $template
     */
    public function __construct(
        \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController,
        \Cx\Core\Core\Controller\Cx $cx
    )
    {
        parent::__construct($systemComponentController, $cx);
        $this->cx = $cx;
    }
    
    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
    }

}
