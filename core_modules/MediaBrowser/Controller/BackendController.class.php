<?php

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

use Cx\Core\Core\Model\Entity\SystemComponentBackendController;
use Cx\Core_Modules\MediaBrowser\Model\Entity\MediaBrowser;
use Cx\Core_Modules\Uploader\Model\Entity\Uploader;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 *              Robin Glauser <robin.glauser@comvation.com>
 */
class BackendController extends SystemComponentBackendController
{

    /**
     * Act param for the URL Request;
     *
     * @var string $act
     */
    protected $act = '';


    /**
     * Returns a list of available commands (?act=XY)
     *
     * @return array List of acts
     */
    public function getCommands()
    {
        return array();
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     *
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array               $cmd      CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        $uploader = new Uploader();
        $uploader->setFinishedCallback(
            '\Cx\Core_Modules\Uploader\Model\DefaultUploadCallback'
        );
        $uploader->setCallback('gallery.uploader');
        $template->setVariable(
            'UPLOADER_CODE', $uploader->getXHtml('Open Uploader 1')
        );

        $uploader2 = new Uploader();
        $uploader2->setFinishedCallback(
            '\Cx\Core_Modules\Uploader\Model\DefaultUploadCallback'
        );
        $uploader2->setCallback('gallery.uploader2');
        $uploader2->setType(Uploader::UPLOADER_TYPE_INLINE);
        $template->setVariable(
            'UPLOADER_CODE2', $uploader2->getXHtml('Open Uploader 2')
        );

        $configurations = array(
            array(),
            array(
                'startview' => 'sitestructure',
                'views' => 'sitestructure'
            ),
            array(
                'views' => 'uploader'
            ),
            array(
                'views' => 'sitestructure'
            ),
            array(
                'views' => 'filebrowser'
            ),
            array(
                'startmediatype' => 'gallery'
            ),
            array(
                'mediatypes' => 'gallery, files'
            ),
            array(
                'multipleselect' => true
            ),
            array(
                'data-cx-Mb-Cb-Js-modalopened' => 'testfunction'
            )
        );

        foreach ($configurations as $configuration){
            $mediaBrowser = new MediaBrowser();
            $mediaBrowser->setOptions($configuration);
            $mediaBrowser->setCallback('gallery.fancyCallback');
            $template->setVariable(
                'MEDIABROWSER_CODE', $mediaBrowser->getXHtml('MediaBrowser')
            );
            $template->setVariable(
                'MEDIABROWSER_OPTIONS', var_export($configuration,true)
            );
            $template->setVariable(
                'MEDIABROWSER_CODE_RAW',
                htmlspecialchars($mediaBrowser->getXHtml('MediaBrowser'))
            );

            $template->parse('mediabrowser_demo');
        }



        $template->setVariable(
            'MEDIABROWSER_FOLDER_WIDGET',
            new \Cx\Core_Modules\MediaBrowser\Model\Entity\FolderWidget($this->cx->getWebsiteImagesContentPath())
        );
        $template->setVariable(
            'MEDIABROWSER_FOLDER_WIDGET_VIEW_MODE',
            new \Cx\Core_Modules\MediaBrowser\Model\Entity\FolderWidget($this->cx->getWebsiteImagesContentPath(), true)
        );

        
    }
}
