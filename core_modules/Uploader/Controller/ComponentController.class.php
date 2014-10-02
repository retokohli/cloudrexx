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

            try {
                // add ng-app="contrexxApp" as Attribute to <html>
                $template->_blocks['__global__'] = str_replace(
                    '<html', '<html data-ng-app="contrexxApp"', $template->_blocks['__global__']
                );
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                echo($e->getMessage());
            }

//            \JS::registerCSS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Style/mediabrowser.css', 1));
//            \JS::registerJS('lib/javascript/jquery/1.9.1/js/jquery.min.js');
//            \JS::registerJS('lib/plupload/js/moxie.min.js');
//            \JS::registerJS('lib/plupload/js/plupload.full.min.js');
//            \JS::registerJS('lib/javascript/angularjs/angular.js');
//            \JS::registerJS('lib/javascript/angularjs/angular-route.js');
//            \JS::registerJS('lib/javascript/angularjs/angular-animate.js');
//            \JS::registerJS('lib/javascript/twitter-bootstrap/3.1.0/js/bootstrap.min.js');
//            \JS::registerJS('lib/javascript/bootbox.min.js');
//            \JS::registerJS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Script/mediabrowser.js', 1));
//            \JS::registerJS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Script/standalone-directives.js', 1));
        }
    }

}