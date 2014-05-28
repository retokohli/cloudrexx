<?php
/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Uploader\Controller;

// don't load Frontend and BackendController for this core_module
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController
{
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
    }

    public function getControllersAccessableByJson() {
        return array(
            'JsonUploader',
        );
    }

}