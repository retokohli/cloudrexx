<?php
/**
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_uploader
 */


namespace Cx\Core_Modules\Uploader\Model;


class DefaultUploadCallback implements UploadCallbackInterface
{

    /**
     * @var \Cx\Core\Core\Controller\Cx
     */
    protected $cx;

    /**
     * @param $cx \Cx\Core\Core\Controller\Cx
     */
    public function __construct($cx)
    {
        $this->cx = $cx;
    }

    /**
     * @param $tempPath    String Path to the temporary directory containing the files at this moment.
     * @param $tempWebPath String Points to the same folder as tempPath, but relative to the webroot.
     * @param $data        String Data given to setData() when creating the uploader.
     * @param $uploadId    integer Per-session unique id for the current upload.
     * @param $fileInfos   array('originalFileNames' => array( 'theCurrentAndCleanedFilename.txt' => 'raw!Source#Filename.txt' ) )
     *
     *
     * @return mixed The return value can be an array as shown in the example or null.
     *               When returning an array, all files left in the temporary directory are moved accordingly.
     *               When returning null, all left files are deleted.
     */
    function uploadFinished(
        $tempPath, $tempWebPath, $data, $uploadId, $fileInfos
    )
    {
        return array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath()
        );
    }
}