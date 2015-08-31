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
use Cx\Core_Modules\Uploader\Model\Entity\Uploader;

class ComponentController extends SystemComponentController
{

    protected $uploaderInstances = array();

    public function addUploader(Uploader $uploader) {
        $this->uploaderInstances[] = $uploader;
    }

    public function getControllerClasses() {
        return array();
    }

    public function getControllersAccessableByJson() {
        return array(
            'JsonUploader',
        );
    }

    public function preFinalize(\Cx\Core\Html\Sigma $template) {
        if (count($this->uploaderInstances) == 0) {
            return;
        }
        global $_ARRAYLANG;

        \Env::get('init')->loadLanguageData('Uploader');
        foreach ($_ARRAYLANG as $key => $value) {
            if (preg_match("/UPLOADER(_[A-Za-z0-9]+)?/", $key)) {
                \ContrexxJavascript::getInstance()->setVariable(
                    $key, $value, 'mediabrowser'
                );
            }
        }
        \ContrexxJavascript::getInstance()->setVariable(
            'chunk_size', floor((\FWSystem::getMaxUploadFileSize()-1000000)/1000000).'mb', 'uploader'
        );

        \JS::activate('mediabrowser');
        \JS::registerJS('core_modules/Uploader/View/Script/Uploader.js');
    }

}