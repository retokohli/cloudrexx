<?php

/**
 * File browser
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_filebrowser
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/Validator.class.php';
require_once ASCMS_CORE_PATH.'/Tree.class.php';
require_once(ASCMS_FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Image.class.php');

/**
 * File browser
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_filebrowser
 */
class FileBrowser {

    public $_objTpl;
    public $_pageTitle;
    public $_okMessage = array();
    public $_errMessage = array();
    public $_arrFiles = array();
    public $_arrDirectories = array();
    public $_path = '';
    public $_iconWebPath = '';
    public $_frontendLanguageId = null;
    public $_absoluteURIs = false;
    public $_mediaType = '';
    public $_arrWebpages = array();
    public $_arrMediaTypes = array(
        'files'     => 'TXT_FILEBROWSER_FILES',
        'webpages'  => 'TXT_FILEBROWSER_WEBPAGES',
        'media1'    => 'TXT_FILEBROWSER_MEDIA_1',
        'media2'    => 'TXT_FILEBROWSER_MEDIA_2',
        'media3'    => 'TXT_FILEBROWSER_MEDIA_3',
        'media4'    => 'TXT_FILEBROWSER_MEDIA_4',
        'shop'      => 'TXT_FILEBROWSER_SHOP',
        'blog'      => 'TXT_FILEBROWSER_BLOG',
        'podcast'   => 'TXT_FILEBROWSER_PODCAST',
        'downloads' => 'TXT_FILEBROWSER_DOWNLOADS'
    );
    public $_shopEnabled;
    public $_blogEnabled;
    public $_podcastEnabled;
    public $_downloadsEnabled;


    /**
    * PHP5 constructor
    *
    * @global array
    */
    function __construct()
    {
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/fileBrowser/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_iconPath = ASCMS_MODULE_IMAGE_WEB_PATH.'/fileBrowser/';
        $this->_path = $this->_getPath();
        $this->_setFrontendLanguageId();
        $this->_checkURIReturnType();
        $this->_mediaType = $this->_getMediaType();

        $this->_shopEnabled = $this->_checkForModule('shop');
        $this->_blogEnabled = $this->_checkForModule('blog');
        $this->_podcastEnabled = $this->_checkForModule('podcast');
        $this->_downloadsEnabled = $this->_checkForModule('downloads');

        $this->_checkUpload();
        $this->checkMakeDir();
        $this->_initFiles();
    }


    /**
     * checks whether the a module is available and active
     *
     * @return bool
     */
    function _checkForModule($strModuleName) {
        global $objDatabase;
        if ( ($objRS = $objDatabase->SelectLimit("SELECT `id` FROM ".DBPREFIX."modules WHERE name = '".$strModuleName."' AND status = 'y'", 1)) != false) {
            if ($objRS->RecordCount() > 0) {
                return true;
            }
        }
        return false;
    }


    /**
     * Get the type of which media content should be displayed in the file browser
     * @access private
     * @see FileBrowser::_arrMediaTypes
     */
    function _getMediaType()
    {
        if (isset($_REQUEST['type']) && isset($this->_arrMediaTypes[$_REQUEST['type']])) {
            return $_REQUEST['type'];
        } else {
            return 'files';
        }
    }


    /**
     * Get the path
     * @return string    current browsing path
     */
    function _getPath()
    {
        $path = "";
        if (isset($_REQUEST['path']) && !stristr($_REQUEST['path'], '..')) {
            $path = $_REQUEST['path'];
        }
        $pos = strrpos($path, '/');
        if ($pos === false || $pos != (strlen($path)-1)) {
            $path .= "/";
        }

        return $path;
    }


    function _setFrontendLanguageId()
    {
        global $_FRONTEND_LANGID;

        if (!empty($_GET['langId']) || !empty($_POST['langId'])) {
            $this->_frontendLanguageId = intval(!empty($_GET['langId']) ? $_GET['langId'] : $_POST['langId']);
        } else {
            $this->_frontendLanguageId = $_FRONTEND_LANGID;
        }
    }


    function _checkURIReturnType()
    {
        if (!empty($_REQUEST['absoluteURIs'])) {
            $this->_absoluteURIs = (bool) $_REQUEST['absoluteURIs'];
        }
    }


    /**
     * Set the backend page
     */
    function getPage()
    {
        if (!isset($_REQUEST['act'])) {
            $_REQUEST['act'] = '';
        }
        switch ($_REQUEST['act']) {
        case 'FCKEditorUpload':
            $this->_FCKEditorUpload();
            break;

        default:
            $this->_showFileBrowser();
            break;
        }
    }


    function SendResults( $errorNumber, $fileUrl = '', $fileName = '', $customMsg = '' )
    {
        echo '<script type="text/javascript">' ;
        echo 'window.parent.OnUploadCompleted(' . $errorNumber . ',"' . str_replace( '"', '\\"', $fileUrl ) . '","' . str_replace( '"', '\\"', $fileName ) . '", "' . str_replace( '"', '\\"', $customMsg ) . '") ;' ;
        echo '</script>' ;
        exit ;
    }


    function _FCKEditorUpload()
    {
        if (!isset($_FILES['NewFile']) || is_null($_FILES['NewFile']['tmp_name']) || $_FILES['NewFile']['name'] == '') {
            $this->SendResults('202');
        } else {
            $uploadFileName = '';
            $this->_uploadFile($_FILES['NewFile']['name'], $_FILES['NewFile']['tmp_name'], $uploadFileName);
            $this->SendResults( 0, ASCMS_CONTENT_IMAGE_WEB_PATH.$this->_path.$uploadFileName, $uploadFileName);
        }
    }


    /**
     * Show the file browser
     * @access private
     * @global array
     */
    function _showFileBrowser()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('module_fileBrowser_frame.html');

        switch($this->_mediaType) {
            case 'media1':
                $strWebPath = ASCMS_MEDIA1_WEB_PATH.$this->_path;
                break;
            case 'media2':
                $strWebPath = ASCMS_MEDIA2_WEB_PATH.$this->_path;
                break;
            case 'media3':
                $strWebPath = ASCMS_MEDIA3_WEB_PATH.$this->_path;
                break;
            case 'media4':
                $strWebPath = ASCMS_MEDIA4_WEB_PATH.$this->_path;
                break;
            case 'webpages':
                $strWebPath = 'Webpages (DB)';
                break;
            case 'shop':
                $strWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.$this->_path;
                break;
            case 'blog':
                $strWebPath = ASCMS_BLOG_IMAGES_WEB_PATH.$this->_path;
                break;
            case 'podcast':
                $strWebPath = ASCMS_PODCAST_IMAGES_WEB_PATH.$this->_path;
                break;
            case 'downloads':
                $strWebPath = ASCMS_DOWNLOADS_IMAGES_WEB_PATH.$this->_path;
                break;
            default:
                $strWebPath = ASCMS_CONTENT_IMAGE_WEB_PATH.$this->_path;
        }

        $this->_objTpl->setVariable(array(
            'CONTREXX_CHARSET'      => CONTREXX_CHARSET,
            'FILEBROWSER_WEB_PATH'  => $strWebPath,
            'TXT_CLOSE'             => $_ARRAYLANG['TXT_CLOSE']
        ));

        $this->_setNavigation();
        $this->_setContent(!empty($_GET['noAliases']) ? $_GET['noAliases'] : false);
        $this->_setUploadForm();
        $this->_showStatus();
        $this->_objTpl->show();
    }


    /**
     * set the error/ok messages in the template
     * @return void
     */
    function _showStatus()
    {
        $okMessage  = implode('<br />', $this->_okMessage);
        $errMessage = implode('<br />', $this->_errMessage);

        if (!empty($errMessage)) {
           $this->_objTpl->setVariable('FILEBROWSER_ERROR_MESSAGE', $errMessage);
        } else {
           $this->_objTpl->hideBlock('errormsg');
        }

        if (!empty($okMessage)) {
            $this->_objTpl->setVariable('FILEBROWSER_OK_MESSAGE', $okMessage);
        } else {
           $this->_objTpl->hideBlock('okmsg');
        }
    }


    /**
     * put $message in the array specified by type
     * for later use of $this->_showStatus();
     * @param string $message
     * @param string $type ('ok' or 'error')
     * @return void
     * @see $this->_showStatus();
     */
    function _pushStatusMessage($message, $type = 'ok')
    {
       switch ($type) {
           case 'ok':
               array_push($this->_okMessage, $message);
               break;
           case 'error':
               array_push($this->_errMessage, $message);
               break;
           default:
               $this->_pushStatusMessage('invalid errortype, check admin.class.php.', 'error');
               break;
       }
    }


    /**
     * Check if there is a file-upload in the current reqest
     *
     */
    function _checkUpload()
    {
        if (isset($_FILES['fileBrowserUploadFile']) && !empty($_FILES['fileBrowserUploadFile'])) {
            $tmp = '';
            $this->_uploadFile(
                $_FILES['fileBrowserUploadFile']['name'],
                $_FILES['fileBrowserUploadFile']['tmp_name'],
                $tmp
            );
        }
    }


    private function checkMakeDir()
    {
        if (isset($_POST['createDir']) && !empty($_POST['newDir'])) {
            $this->makeDir($_POST['newDir']);
        }
    }


    private function makeDir($dir)
    {
        global $_ARRAYLANG;

        switch($this->_mediaType) {
            case 'media1':
                $strPath    = ASCMS_MEDIA1_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA1_WEB_PATH.$this->_path;
            break;
            case 'media2':
                $strPath    = ASCMS_MEDIA2_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA2_WEB_PATH.$this->_path;
            break;
            case 'media3':
                $strPath    = ASCMS_MEDIA3_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA3_WEB_PATH.$this->_path;
            break;
            case 'media4':
                $strPath    = ASCMS_MEDIA4_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA4_WEB_PATH.$this->_path;
            break;
            case 'shop':
                $strPath    = ASCMS_SHOP_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.$this->_path;
            break;
            case 'blog':
                $strPath    = ASCMS_BLOG_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_BLOG_IMAGES_WEB_PATH.$this->_path;
            break;
            case 'podcast':
                $strPath    = ASCMS_PODCAST_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_PODCAST_IMAGES_WEB_PATH.$this->_path;
            break;
            case 'downloads':
                $strPath    = ASCMS_DOWNLOADS_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_DOWNLOADS_IMAGES_WEB_PATH.$this->_path;
            break;
            default:
                $strPath    = ASCMS_CONTENT_IMAGE_PATH.$this->_path;
                $strWebPath = ASCMS_CONTENT_IMAGE_WEB_PATH.$this->_path;
        }

        if (preg_match('#^[0-9a-zA-Z_\-]+$#', $dir)) {
            CSRF::check_code();
            $objFile = new File();
            if (!$objFile->mkDir($strPath, $strWebPath, $dir)) {
                $this->_pushStatusMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_UNABLE_TO_CREATE_FOLDER'], $dir), 'error');
            } else {
                $this->_pushStatusMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_DIRECTORY_SUCCESSFULLY_CREATED'], $dir));
            }
        } else if (!empty($dir)) {
            $this->_pushStatusMessage($_ARRAYLANG['TXT_FILEBROWSER_INVALID_CHARACTERS'], 'error');
        }
    }


    /**
     * Upload a file
     * @param string $uploadFileName: the name of the file
     * @param string $tmpFileName: temporary name of th efile
     * @param string $uploadedFileName: reference to the file name after upload
     */
    function _uploadFile($uploadFileName, $tmpFileName, &$uploadedFileName)
    {
        global $_ARRAYLANG;

        $file = $uploadFileName;
        $fileExtension = '';

        switch($this->_mediaType) {
            case 'media1':
                $strPath    = ASCMS_MEDIA1_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA1_WEB_PATH.$this->_path;
            break;
            case 'media2':
                $strPath    = ASCMS_MEDIA2_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA2_WEB_PATH.$this->_path;
            break;
            case 'media3':
                $strPath    = ASCMS_MEDIA3_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA3_WEB_PATH.$this->_path;
            break;
            case 'media4':
                $strPath    = ASCMS_MEDIA4_PATH.$this->_path;
                $strWebPath = ASCMS_MEDIA4_WEB_PATH.$this->_path;
            break;
            case 'shop':
                $strPath    = ASCMS_SHOP_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_SHOP_IMAGES_WEB_PATH.$this->_path;
            break;
            case 'blog':
                $strPath    = ASCMS_BLOG_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_BLOG_IMAGES_WEB_PATH.$this->_path;
            break;
            case 'podcast':
                $strPath    = ASCMS_PODCAST_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_PODCAST_IMAGES_WEB_PATH.$this->_path;
            break;
            case 'downloads':
                $strPath    = ASCMS_DOWNLOADS_IMAGES_PATH.$this->_path;
                $strWebPath = ASCMS_DOWNLOADS_IMAGES_WEB_PATH.$this->_path;
            break;
            default:
                $strPath    = ASCMS_CONTENT_IMAGE_PATH.$this->_path;
                $strWebPath = ASCMS_CONTENT_IMAGE_WEB_PATH.$this->_path;
        }

        $nr = 1;

        if (!FWValidator::is_file_ending_harmless($uploadFileName)) {
            return false;
        }

        if (@file_exists($strPath.$uploadFileName)) {
            $arrSubPatterns = array();
            if (preg_match('/.*\.(.*)$/', $uploadFileName, $arrSubPatterns)) {
                $fileName = substr($uploadFileName, 0, strrpos($uploadFileName, '.'));
                $fileExtension = $arrSubPatterns[1];
                $file = $fileName.'-'.$nr.'.'.$fileExtension;
                while (@file_exists($strPath.$file)) {
                    $file = substr($uploadFileName, 0, strrpos($uploadFileName, '.')).'-'.$nr.'.'.$fileExtension;
                    $nr++;
                }
            } else {
                return false;
            }
        }
        $uploadedFileName = $file;

        if (move_uploaded_file($tmpFileName, $strPath.$file)) {
            $objFile = new File();
            $objFile->setChmod($strPath, $strWebPath, $file);
        }
        $fileType = pathinfo($strPath.$file);
        if ($fileType['extension'] == 'jpg' || $fileType['extension'] == 'jpeg' || $fileType['extension'] == 'png' || $fileType['extension'] == 'gif') {
            if ($this->_createThumb($strPath, $strWebPath, $file)) {
              $this->_pushStatusMessage(sprintf($_ARRAYLANG['TXT_FILEBROWSER_THUMBNAIL_SUCCESSFULLY_CREATED'], $strWebPath.$file));
            }
        }
        return true;
    }


    /**
     * @todo    OBSOLETE.  Use core/Image.class.php instead.
     */
    function _createThumb($strPath, $strWebPath, $file, $height=80, $quality=90)
    {
        global $_ARRAYLANG;

        $objFile = new File();
        $_objImage = new ImageManager();
        $_objImage->_createThumbWhq(
            $strPath, $strWebPath, $file, 1e10, $height, $quality
        );
//        $tmpSize = getimagesize($strPath.$file);
//        $thumbWidth = $height / $tmpSize[1] * $tmpSize[0];
//        $_objImage->loadImage($strPath.$file);
//        $_objImage->resizeImage($thumbWidth, $height, $quality);
//        $_objImage->saveNewImage($strPath.$file . '.thumb');
        if ($objFile->setChmod($strPath, $strWebPath,
            ImageManager::getThumbnailFilename($file)))
            return true;
        return false;
    }


    /**
     * Set the navigation with the media type drop-down menu in the file browser
     * @access private
     * @see FileBrowser::_getMediaTypeMenu, _objTpl, _mediaType, _arrDirectories
     */
    function _setNavigation()
    {
        global $_ARRAYLANG;

        $this->_objTpl->addBlockfile('FILEBROWSER_NAVIGATION', 'fileBrowser_navigation', 'module_fileBrowser_navigation.html');
        $this->_objTpl->setVariable(array(
            'FILEBROWSER_MEDIA_TYPE_MENU'   => $this->_getMediaTypeMenu('fileBrowserType', $this->_mediaType, 'onchange="window.location.replace(\''.CSRF::enhanceURI('index.php?cmd=fileBrowser').'&amp;standalone=true&amp;langId='.$this->_frontendLanguageId.'&amp;absoluteURIs='.$this->_absoluteURIs.'&amp;type=\'+this.value)" style="width:180px;"'),
            'TXT_FILEBROWSER_PREVIEW'       => $_ARRAYLANG['TXT_FILEBROWSER_PREVIEW']
        ));

        if ($this->_mediaType != 'webpages') {
            // only show directories if the files should be displayed
            if (count($this->_arrDirectories) > 0) {
                foreach ($this->_arrDirectories as $arrDirectory) {
                    $this->_objTpl->setVariable(array(
                        'FILEBROWSER_FILE_PATH' => "index.php?cmd=fileBrowser&amp;standalone=true&amp;langId={$this->_frontendLanguageId}&amp;absoluteURIs={$this->_absoluteURIs}&amp;type={$this->_mediaType}&amp;path={$arrDirectory['path']}",
                        'FILEBROWSER_FILE_NAME' => $arrDirectory['name'],
                        'FILEBROWSER_FILE_ICON' => $arrDirectory['icon']
                    ));
                    $this->_objTpl->parse('navigation_directories');
                }
            }
        }
        $this->_objTpl->parse('fileBrowser_navigation');
    }


    /**
     * Shows all files / pages in filebrowser
     */
    function _setContent($noAliases=false)
    {
        global $objDatabase, $_CONFIG;

        $this->_objTpl->addBlockfile('FILEBROWSER_CONTENT', 'fileBrowser_content', 'module_fileBrowser_content.html');
        $this->_objTpl->setVariable('FILEBROWSER_NOT_ABSOLUTE_URI', !$this->_absoluteURIs ? 'true' : 'false');

        $rowNr = 0;

        switch ($this->_mediaType) {
        case 'webpages':
            $arrModules = array();
            $objResult = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."modules");
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    $arrModules[$objResult->fields['id']] = $objResult->fields['name'];
                    $objResult->MoveNext();
                }
            }
            $getPageId = (isset($_REQUEST['getPageId']) && $_REQUEST['getPageId'] == 'true') ? true : false;

            $objContentTree = new ContentTree($this->_frontendLanguageId);

            $scriptPath = ($this->_absoluteURIs ?
                $_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/'.($_CONFIG['useVirtualLanguagePath'] == 'on' ?
                    FWLanguage::getLanguageParameter($this->_frontendLanguageId, 'lang').'/'
                :   null)
            :   null);
            foreach ($objContentTree->getTree() as $arrPage) {
                $s = isset($arrModules[$arrPage['moduleid']]) ? $arrModules[$arrPage['moduleid']] : '';
                $c = $arrPage['cmd'];
                $section = ($s=="") ? "" : "&amp;section=$s";
                $cmd = ($c=="") ? "" : "&amp;cmd=$c";
                $link = $scriptPath.CONTREXX_DIRECTORY_INDEX.((!empty($s)) ? "?section=".$s.$cmd : "?page=".$arrPage['catid'].$section.$cmd);

                $url = "'".$link."'".($getPageId ? ','.$arrPage['catid'] : '')."";

                if($arrPage['alias'] && !$noAliases) {
                    $url = "'" . $scriptPath . $arrPage['alias'] . "'";
                }

                $this->_objTpl->setVariable(array(
                    'FILEBROWSER_ROW_CLASS'         => $rowNr%2 == 0 ? "row1" : "row2",
                    'FILEBROWSER_FILE_PATH_CLICK'   => "javascript:{setUrl($url)}",
                    'FILEBROWSER_FILE_NAME'         => $arrPage['catname'],
                    'FILEBROWSER_FILESIZE'          => '&nbsp;',
                    'FILEBROWSER_FILE_ICON'         => $this->_iconPath.'htm.gif',
                    'FILEBROWSER_FILE_DIMENSION'    => '&nbsp;',
                    'FILEBROWSER_SPACER'            => '<img src="images/icons/pixel.gif" width="'.($arrPage['level']*16).'" height="1" />'
                ));
                $this->_objTpl->parse('content_files');

                $rowNr++;
            }
            break;
        case 'media1':
        case 'media2':
        case 'media3':
        case 'media4':
            Permission::checkAccess(7, 'static');       //Access Media-Archive
            Permission::checkAccess(38, 'static');  //Edit Media-Files
            Permission::checkAccess(39, 'static');  //Upload Media-Files

        //Hier soll wirklich kein break stehen! Beabsichtig!


        default:
            if (count($this->_arrDirectories) > 0) {
                foreach ($this->_arrDirectories as $arrDirectory) {
                    $this->_objTpl->setVariable(array(
                        'FILEBROWSER_ROW_CLASS'         => $rowNr%2 == 0 ? "row1" : "row2",
                        'FILEBROWSER_FILE_PATH_CLICK'   => "index.php?cmd=fileBrowser&amp;standalone=true&amp;langId={$this->_frontendLanguageId}&amp;absoluteURIs={$this->_absoluteURIs}&amp;type={$this->_mediaType}&amp;path={$arrDirectory['path']}",
                        'FILEBROWSER_FILE_NAME'         => $arrDirectory['name'],
                        'FILEBROWSER_FILESIZE'          => '&nbsp;',
                        'FILEBROWSER_FILE_ICON'         => $arrDirectory['icon'],
                        'FILEBROWSER_FILE_DIMENSION'    => '&nbsp;'
                    ));
                    $this->_objTpl->parse('content_files');

                    $rowNr++;
                }
            }

            if (count($this->_arrFiles) > 0) {
                $arrEscapedPaths = array();
                foreach ($this->_arrFiles as $arrFile) {
                    $arrEscapedPaths[] = contrexx_raw2encodedUrl($arrFile['path']);
                    $this->_objTpl->setVariable(array(
                        'FILEBROWSER_ROW_CLASS'             => $rowNr%2 == 0 ? "row1" : "row2",
                        'FILEBROWSER_FILE_PATH_DBLCLICK'    => "setUrl('".contrexx_raw2encodedUrl($arrFile['path'])."',".$arrFile['width'].",".$arrFile['height'].",'')",
                        'FILEBROWSER_FILE_PATH_CLICK'       => "javascript:{showPreview(".(count($arrEscapedPaths)-1).",".$arrFile['width'].",".$arrFile['height'].")}",
                        'FILEBROWSER_FILE_NAME'             => contrexx_stripslashes($arrFile['name']),
                        'FILEBROWSER_FILESIZE'              => $arrFile['size'].' KB',
                        'FILEBROWSER_FILE_ICON'             => $arrFile['icon'],
                        'FILEBROWSER_FILE_DIMENSION'        => (empty($arrFile['width']) && empty($arrFile['height'])) ? '' : intval($arrFile['width']).'x'.intval($arrFile['height'])
                    ));
                    $this->_objTpl->parse('content_files');
                    $rowNr++;
                }

                $this->_objTpl->setVariable('FILEBROWSER_FILES_JS', "'".implode("','",$arrEscapedPaths)."'");
            }

            switch ($this->_mediaType) {
                case 'media1':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_MEDIA1_WEB_PATH);
                    break;
                case 'media2':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_MEDIA2_WEB_PATH);
                    break;
                case 'media3':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_MEDIA3_WEB_PATH);
                    break;
                case 'media4':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_MEDIA4_WEB_PATH);
                    break;
                case 'shop':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_SHOP_IMAGES_WEB_PATH);
                    break;
                case 'blog':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_BLOG_IMAGES_WEB_PATH);
                    break;
                case 'podcast':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_PODCAST_IMAGES_WEB_PATH);
                    break;
                case 'downloads':
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_DOWNLOADS_IMAGES_WEB_PATH);
                    break;
                default:
                    $this->_objTpl->setVariable('FILEBROWSER_IMAGE_PATH', ASCMS_CONTENT_IMAGE_WEB_PATH);
            }
        }
        $this->_objTpl->parse('fileBrowser_content');
    }


    /**
     * Shows the upload-form in the filebrowser
     */
    function _setUploadForm()
    {
        global $_ARRAYLANG, $_CONFIG;

        $objFWSystem = new FWSystem();
        $this->_objTpl->addBlockfile('FILEBROWSER_UPLOAD', 'fileBrowser_upload', 'module_fileBrowser_upload.html');
        $this->_objTpl->setVariable(array(
            'FILEBROWSER_UPLOAD_TYPE'   => $this->_mediaType,
            'FILEBROWSER_UPLOAD_PATH'   => $this->_path,
            'FILEBROWSER_MAX_FILE_SIZE' => $objFWSystem->getMaxUploadFileSize(),
            'TXT_CREATE_DIRECTORY'      => $_ARRAYLANG['TXT_FILEBROWSER_CREATE_DIRECTORY'],
            'TXT_UPLOAD_FILE'           => $_ARRAYLANG['TXT_FILEBROWSER_UPLOAD_FILE']
        ));

        $objModulChecker = new ModuleChecker();
        if ($objModulChecker->getModuleStatusById(52) && $_CONFIG['fileUploaderStatus'] == 'on') {
            $this->_objTpl->setVariable(array(
                'TXT_UPLOAD_FILE_MULTI'                => $_ARRAYLANG['TXT_FILEBROWSER_UPLOAD_FILE_MULTI'],
                'TXT_FILEBROWSER_START_FILE_UPLOADER'   => $_ARRAYLANG['TXT_FILEBROWSER_START_FILE_UPLOADER'],
                'FILEBROWSER_ADVANCED_UPLOAD_PATH'    => 'index.php?cmd=fileUploader&amp;standalone=true&amp;type='.$this->_mediaType.'&amp;path='.urlencode($this->_path)
            ));
            $this->_objTpl->parse('fileBrowser_advanced_upload');
        } else {
            $this->_objTpl->hideBlock('fileBrowser_advanced_upload');
        }

        $this->_objTpl->parse('fileBrowser_upload');
    }


    /**
     * Read all files / directories of the current folder
     */
    function _initFiles()
    {
        switch($this->_mediaType) {
            case 'media1':
                $strPath = ASCMS_MEDIA1_PATH.$this->_path;
            break;
            case 'media2':
                $strPath = ASCMS_MEDIA2_PATH.$this->_path;
            break;
            case 'media3':
                $strPath = ASCMS_MEDIA3_PATH.$this->_path;
            break;
            case 'media4':
                $strPath = ASCMS_MEDIA4_PATH.$this->_path;
            break;
            case 'shop':
                $strPath = ASCMS_SHOP_IMAGES_PATH.$this->_path;
            break;
            case 'blog':
                $strPath = ASCMS_BLOG_IMAGES_PATH.$this->_path;
            break;
            case 'podcast':
                $strPath = ASCMS_PODCAST_IMAGES_PATH.$this->_path;
            break;
            case 'downloads':
                $strPath = ASCMS_DOWNLOADS_IMAGES_PATH.$this->_path;
            break;
            default:
                $strPath = ASCMS_CONTENT_IMAGE_PATH.$this->_path;
        }

        $objDir = @opendir($strPath);

        $arrFiles = array();

        if ($objDir) {
            $path = array();
            if (   $this->_path !== "/"
                && preg_match('#(.*/).+[/]?$#', $this->_path, $path)) {
                array_push($this->_arrDirectories, array('name' => '..', 'path' => $path[1], 'icon' => $this->_iconPath.'_folder.gif'));
            }

            $file = readdir($objDir);
            while ($file !== false) {
// TODO: This match won't work for arbitrary thumbnail file names as they
// may be created by the Image class!
                if ($file == '.' || $file == '..' || preg_match('/\.thumb$/', $file) || $file == 'index.php') {
                    $file = readdir($objDir);
                    continue;
                }
                array_push($arrFiles, $file);
                $file = readdir($objDir);
            }
            closedir($objDir);

            sort($arrFiles);

            foreach ($arrFiles as $file) {
                if (is_dir($strPath.$file)) {
                    array_push($this->_arrDirectories, array('name' => $file, 'path' => $this->_path.$file, 'icon' => $this->_getIcon($strPath.$file)));
                } else {
                    $filesize = @filesize($strPath.$file);
                    if ($filesize > 0) {
                        $filesize = round($filesize/1024);
                    } else {
                        $filesize = 0;
                    }
                    $arrDimensions = @getimagesize($strPath.$file);
                    array_push($this->_arrFiles, array('name' => $file, 'path' => $this->_path.$file, 'size' => $filesize, 'icon' => $this->_getIcon($strPath.$file), 'width' => intval($arrDimensions[0]), 'height' => intval($arrDimensions[1])));
                }
            }
        }
    }


    /**
     * Search the icon for a file
     * @param  string $file: The icon of this file will be searched
     */
    function _getIcon($file)
    {
        if (is_file($file)) {
            $info = pathinfo($file);
            $icon = strtolower($info['extension']);
        }

        if (is_dir($file)) {
            $icon = '_folder';
        }

        if (!file_exists(ASCMS_MODULE_IMAGE_PATH.'/fileBrowser/'.$icon.'.gif') or !isset($icon)) {
            $icon = '_blank';
        }
        return $this->_iconPath.$icon.'.gif';
    }


    /**
     * Create html-source of a complete <select>-navigation
     * @param string $name: name of the <select>-tag
     * @param string $selectedType: which <option> will be "selected"?
     * @param string $attrs: further attributes of the <select>-tag
     * @return string html-source
     */
    function _getMediaTypeMenu($name, $selectedType, $attrs)
    {
        global $_ARRAYLANG;

        $menu = "<select name=\"".$name."\" ".$attrs.">";
        foreach ($this->_arrMediaTypes as $type => $text) {
            if ($type == 'shop' && !$this->_shopEnabled) { continue; }
            if ($type == 'blog' && !$this->_blogEnabled) { continue; }
            if ($type == 'podcast' && !$this->_podcastEnabled) { continue; }
            if ($type == 'downloads' && !$this->_downloadsEnabled) { continue; }
            $menu .= "<option value=\"".$type."\"".($selectedType == $type ? " selected=\"selected\"" : "").">".$_ARRAYLANG[$text]."</option>\n";
        }
        $menu .= "</select>";
        return $menu;
    }

}

?>
