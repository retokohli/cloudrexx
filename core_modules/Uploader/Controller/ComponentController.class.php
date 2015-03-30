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
use Cx\Core\Core\Controller\Cx;
use Cx\Core\Core\Model\Entity\SystemComponent;
use Cx\Core\Core\Model\Entity\SystemComponentController;
use Cx\Core_Modules\Uploader\Model\Uploader;

class ComponentController extends SystemComponentController
{

    protected $uploaderInstances = array();

    public function __construct(SystemComponent $systemComponent, Cx $cx)
    {
        parent::__construct($systemComponent, $cx);
    }

    public function addUploader(Uploader $uploader)
    {
        $this->uploaderInstances[] = $uploader;
    }

    public function getControllersAccessableByJson()
    {
        return array(
            'JsonUploader',
        );
    }

    public function preFinalize(\Cx\Core\Html\Sigma $template)
    {
        if (count($this->uploaderInstances) > 0) {
            global $_ARRAYLANG;

            \Env::get('init')->loadLanguageData('Uploader');
            foreach ($_ARRAYLANG as $key => $value) {
                if (preg_match("/UPLOADER(_[A-Za-z0-9]+)?/", $key)) {
                    \ContrexxJavascript::getInstance()->setVariable($key, $value, 'mediabrowser');
                }
            }

            \JS::activate('mediabrowser');

            $template->setGlobalVariable(
                'MEDIABROWSER_ANGULAR_APP', 'ng-app="contrexxApp"'
            );

        }
    }

}