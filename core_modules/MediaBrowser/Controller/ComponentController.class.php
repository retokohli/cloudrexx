<?php

/**
 * Class ComponentController
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 * @version     1.0.0
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

// don't load Frontend and BackendController for this core_module
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponent $systemComponent, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponent, $cx);
    }

    public function getControllersAccessableByJson() {
        return array(
            'JsonMediaBrowser',
        );
    }

    public function preFinalize(\Cx\Core\Html\Sigma $template) {
        if (is_object(\Env::get('MediaBrowser'))) {


            //$modalTemplate = new \Cx\Core\Html\Sigma((ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Template/MediaBrowserModel.html'));

            try {
                $path = $this->getDirectory(false) . '/View/Template/MediaBrowserModal.html';
                $objFile = new \Cx\Lib\FileSystem\File($path);
                $data = $objFile->getData();

                $template->_blocks['__global__'] = str_replace('</body>', $data.'</body>', $template->_blocks['__global__']);
                
                // add ng-app="contrexxApp" as Attribute to <html>
                $template->_blocks['__global__'] = str_replace('<html', '<html ng-app="contrexxApp"', $template->_blocks['__global__']);
            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                echo ($e->getMessage());
            }


            \JS::registerCSS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Style/mediabrowser.css', 1));

            \JS::registerJS('lib/javascript/jquery/1.9.1/js/jquery.min.js');
            \JS::registerJS('lib/plupload/js/moxie.min.js');
            \JS::registerJS('lib/plupload/js/plupload.dev.js'); /* todo change to min */
            \JS::registerJS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Script/angular.min.js', 1));
            \JS::registerJS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Script/angular-route.min.js', 1));
            \JS::registerJS('lib/javascript/twitter-bootstrap/3.1.0/js/bootstrap.min.js');
            \JS::registerJS('lib/javascript/bootbox.min.js');
            \JS::registerJS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Script/mediabrowser.js', 1));
            \JS::registerJS(substr(ASCMS_CORE_MODULE_FOLDER . '/MediaBrowser/View/Script/standalone-directives.js', 1));
        }
    }

}
