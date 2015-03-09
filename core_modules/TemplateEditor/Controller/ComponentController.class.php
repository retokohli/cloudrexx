<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\TemplateEditor\Controller;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Core\Model\Entity\SystemComponentController;
use Cx\Core\View\Model\Repository\ThemeRepository;

class ComponentController extends SystemComponentController
{


    public function preFinalize(\Cx\Core\Html\Sigma $template)
    {
        if ($this->cx->getMode() == Cx::MODE_FRONTEND) {
            $themeRepository = new ThemeRepository();
            if (isset($_GET['preview'])) {

            } else {

            }
        }
    }

}