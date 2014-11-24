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
use Cx\Core\Html\Sigma;
use Cx\Core_Modules\MediaBrowser\Model\MediaBrowser;
use Cx\Core_Modules\Uploader\Controller\UploaderConfiguration;

class ComponentController extends
    \Cx\Core\Core\Model\Entity\SystemComponentController
{

    protected $mediaBrowserInstances = array();


    public function __construct(
        \Cx\Core\Core\Model\Entity\SystemComponent $systemComponent,
        \Cx\Core\Core\Controller\Cx $cx
    )
    {
        parent::__construct($systemComponent, $cx);
    }

    public function addMediaBrowser(MediaBrowser $mediaBrowser)
    {
        $this->mediaBrowserInstances[] = $mediaBrowser;
    }


    public function getControllersAccessableByJson()
    {
        return array(
            'JsonMediaBrowser',
        );
    }

    public function preFinalize(\Cx\Core\Html\Sigma $template)
    {

        if (count($this->mediaBrowserInstances) > 0) {
            global $_ARRAYLANG;
            \Env::get('init')->loadLanguageData('MediaBrowser');
            foreach ($_ARRAYLANG as $key => $value) {
                if (preg_match("/TXT_FILEBROWSER_[A-Za-z0-9]+/", $key)) {
                    \ContrexxJavascript::getInstance()->setVariable(
                        $key, $value, 'mediabrowser'
                    );
                }
            }

            $thumbnailsTemplate = new Sigma();
            $thumbnailsTemplate->loadTemplateFile(
                'core_modules/MediaBrowser/View/Template/Thumbnails.html'
            );
            $thumbnailsTemplate->setVariable(
                'TXT_FILEBROWSER_THUMBNAIL_ORIGINAL_SIZE', sprintf(
                    $_ARRAYLANG['TXT_FILEBROWSER_THUMBNAIL_ORIGINAL_SIZE']
                )
            );
            foreach (
                UploaderConfiguration::getInstance()->getThumbnails() as
                $thumbnail
            ) {
                $thumbnailsTemplate->setVariable(
                    array(
                        'THUMBNAIL_NAME' => sprintf(
                            $_ARRAYLANG[
                            'TXT_FILEBROWSER_THUMBNAIL_' . strtoupper(
                                $thumbnail['name']
                            ) . '_SIZE'], $thumbnail['size']
                        ),
                        'THUMBNAIL_ID' => $thumbnail['id'],
                        'THUMBNAIL_SIZE' => $thumbnail['size']
                    )
                );
                $thumbnailsTemplate->parse('thumbnails');
            }

            \ContrexxJavascript::getInstance()->setVariable(
                'thumbnails_template', $thumbnailsTemplate->get(),
                'mediabrowser'
            );

            try {
                // add ng-app="contrexxApp" as Attribute to <html>
                $template->_blocks['__global__'] = str_replace(
                    '<html', '<html ng-app="contrexxApp"',
                    $template->_blocks['__global__']
                );

                $code = "<script>var oldjQuery = jQuery</script>";
                $code .= $this->createScriptTag(
                    '../lib/javascript/jquery/1.9.1/js/jquery.min.js'
                );
                $code .= "<script> window.MediaBrowserjQuery = jQuery</script>";
                $code .= $this->createScriptTag(
                    '../lib/plupload/js/moxie.min.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/plupload/js/plupload.full.min.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/javascript/angularjs/angular.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/javascript/angularjs/angular-route.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/javascript/angularjs/angular-animate.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/javascript/angularjs/ui-bootstrap-tpls-0.11.2.min.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/javascript/bootbox.min.js'
                );
                $code .= $this->createScriptTag(
                    '../lib/javascript/twitter-bootstrap/3.1.0/js/bootstrap.min.js'
                );

                $code .= $this->createScriptTag(
                    '../' . $this->cx->getCoreModuleFolderName()
                    . '/MediaBrowser/View/Script/mediabrowser.js?v=' . md5(
                        date('HH:SS:Y:D:M')
                    )
                );
                $code .= $this->createScriptTag(
                    '../' . substr(
                        $this->cx->getCoreModuleFolderName()
                        . '/MediaBrowser/View/Script/standalone-directives.js',
                        1
                    )
                );
                $template->_blocks['__global__'] = str_replace(
                    '</head>', $code . '</head>',
                    $template->_blocks['__global__']
                );

            } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
                echo($e->getMessage());
            }
            \JS::registerCSS(
                substr(
                    $this->cx->getCoreModuleFolderName()
                    . '/MediaBrowser/View/Style/mediabrowser.css', 1
                )
            );

        }
    }

    function createScriptTag($link)
    {
        return "<script type=\"text/javascript\"  src=\"" . $link
        . "\"></script>\n";
    }

}
