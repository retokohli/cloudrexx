<?php
/**
 * Media Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  core_module_media
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH .'/media/mediaLib.class.php';
require_once ASCMS_FRAMEWORK_PATH.'/Image.class.php';
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/File.class.php';

/**
 * Media Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0.0
 * @access        public
 * @package     contrexx
 * @subpackage  core_module_media
 */
class MediaManager extends MediaLibrary
{
    public $_objTpl;                          // var for the template object
    public $pageTitle;                        // var for the title of the active page

    public $iconPath;                         // icon path constant
    public $iconWebPath;                      // icon webPath constant
    public $arrPaths;                         // array paths
    public $arrWebPaths;                      // array web paths

    public $getAct;                           // $_GET['act']
    public $getPath;                          // $_GET['path']
    public $getFile;                          // $_GET['file']
    public $getData;                          // $_GET['data']

    public $chmodFolder       = 0777;         // chmod for folder 0777
    public $chmodFile         = 0644;         // chmod for files  0644
    public $thumbHeight       = 80;           // max height for thumbnail
    public $thumbQuality      = 80;           // max quality for thumbnail

    public $docRoot;                          // ASCMS_DOCUMENT_ROOT
    public $path;                             // current path
    public $webPath;                          // current web path
    public $highlightName     = array();      // highlight added name
    public $highlightColor    = '#d8ffca';    // highlight color for added name [#d8ffca]
    public $highlightCCColor  = '#ffe7e7';    // highlight color for cuted or copied media [#ffe7e7]

    public $tmpPath           = array();      // dir tree path
    public $tmpPathName       = array();      // dir tree path name

    public $_objImage;                        // object from ImageManager class

    public $dirLog;                           // Dir Log
    public $archive;

    public $_shopEnabled;


    /**
     * PHP5 constructor
     * @param  string  $objTemplate
     * @param  array   $_ARRAYLANG
     * @access public
     */
    function __construct()
    {
        global  $_ARRAYLANG, $objTemplate;

        // sigma template
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/media/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        // directory variables
        $this->iconPath     = ASCMS_MODULE_IMAGE_PATH.'/media/';
        $this->iconWebPath  = ASCMS_MODULE_IMAGE_WEB_PATH.'/media/';
        $this->arrPaths     = array(ASCMS_MEDIA1_PATH.DIRECTORY_SEPARATOR,
                                    ASCMS_MEDIA2_PATH.DIRECTORY_SEPARATOR,
                                    ASCMS_MEDIA3_PATH.DIRECTORY_SEPARATOR,
                                    ASCMS_MEDIA4_PATH.DIRECTORY_SEPARATOR,
                                    ASCMS_CONTENT_IMAGE_PATH.DIRECTORY_SEPARATOR,
                                    ASCMS_SHOP_IMAGES_PATH.DIRECTORY_SEPARATOR,
                                    ASCMS_THEMES_PATH.DIRECTORY_SEPARATOR);

        $this->arrWebPaths  = array('archive1'    => ASCMS_MEDIA1_WEB_PATH.'/',
                                    'archive2'    => ASCMS_MEDIA2_WEB_PATH.'/',
                                    'archive3'    => ASCMS_MEDIA3_WEB_PATH.'/',
                                    'archive4'    => ASCMS_MEDIA4_WEB_PATH.'/',
                                    'content'    => ASCMS_CONTENT_IMAGE_WEB_PATH.'/',
                                    'shop'        => ASCMS_SHOP_IMAGES_WEB_PATH.'/',
                                    'themes'       => ASCMS_THEMES_WEB_PATH.'/');

        $_shopEnabled = $this->_checkForShop();

        if (isset($_REQUEST['archive']) && array_key_exists($_REQUEST['archive'], $this->arrWebPaths)) {
            $this->archive = $_REQUEST['archive'];
        } else {
            $this->archive = 'content';
        }

        // get variables
        $this->getAct  = (isset($_GET['act'])  and !empty($_GET['act']))  ? trim($_GET['act'])  : '';
        $this->getPath = (isset($_GET['path']) and !empty($_GET['path']) AND !stristr($_GET['path'],'..')) ? trim($_GET['path']) : $this->arrWebPaths[$this->archive];
        $this->getFile = (isset($_GET['file']) and !empty($_GET['file']) AND !stristr($_GET['file'],'..')) ? trim($_GET['file']) : '';
        $this->getData = (isset($_GET['data']) and !empty($_GET['data'])) ? $_GET['data']       : '';
        $this->sortBy = !empty($_GET['sort']) ? trim($_GET['sort']) : 'name';
        $this->sortDesc = !empty($_GET['sort_desc']);

        if ($this->archive == 'themes') {
            $_SESSION["skins"] = true;
        } else {
            $_SESSION["skins"] = false;
        }

        switch ($this->archive) {
        case 'themes':
            Permission::checkAccess(21, 'static');
            $objTemplate->setVariable("CONTENT_NAVIGATION",
                "<a href='index.php?cmd=skins'>".$_ARRAYLANG['TXT_DESIGN_OVERVIEW']."</a>
                <a href='index.php?cmd=skins&amp;act=newDir'>".$_ARRAYLANG['TXT_NEW_DESIGN']."</a>
                <a href='index.php?cmd=skins&amp;act=activate'>".$_ARRAYLANG['TXT_ACTIVATE_DESIGN']."</a>
                <a href='index.php?cmd=media&amp;archive=themes'>".$_ARRAYLANG['TXT_DESIGN_FILES_ADMINISTRATION']."</a>
                <a href='index.php?cmd=skins&amp;act=examples'>".$_ARRAYLANG['TXT_DESIGN_REPLACEMENTS_DIR']."</a>
                <a href='index.php?cmd=skins&amp;act=manage'>".$_ARRAYLANG['TXT_THEME_IMPORT_EXPORT']."</a>");
            break;

        case 'content':
            Permission::checkAccess(32, 'static');
            $objTemplate->setVariable('CONTENT_NAVIGATION', '
                <a href="index.php?cmd=media&amp;archive=content">'. $_ARRAYLANG['TXT_IMAGE_CONTENT'] .'</a>'
                .(($_shopEnabled) ? '<a href="index.php?cmd=media&amp;archive=shop">'. $_ARRAYLANG['TXT_IMAGE_SHOP'] .'</a>' : '')
            );
            break;
        case 'shop':
            Permission::checkAccess(13, 'static');
            $objTemplate->setVariable('CONTENT_NAVIGATION', '
                <a href="index.php?cmd=media&amp;archive=content">'. $_ARRAYLANG['TXT_IMAGE_CONTENT'] .'</a>'
                .(($_shopEnabled) ? '<a href="index.php?cmd=media&amp;archive=shop">'. $_ARRAYLANG['TXT_IMAGE_SHOP'] .'</a>' : '')
            );
            break;

        default:
            Permission::checkAccess(7, 'static');
            $objTemplate->setVariable('CONTENT_NAVIGATION', '
                <a href="index.php?cmd=media&amp;archive=archive1">'. $_ARRAYLANG['TXT_MEDIA_ARCHIVE'] .' #1</a>
                <a href="index.php?cmd=media&amp;archive=archive2">'. $_ARRAYLANG['TXT_MEDIA_ARCHIVE'] .' #2</a>
                <a href="index.php?cmd=media&amp;archive=archive3">'. $_ARRAYLANG['TXT_MEDIA_ARCHIVE'] .' #3</a>
                <a href="index.php?cmd=media&amp;archive=archive4">'. $_ARRAYLANG['TXT_MEDIA_ARCHIVE'] .' #4</a>
            ');
            break;
        }

        $this->docRoot = ASCMS_DOCUMENT_ROOT; // with path offset
        $this->docRoot = ASCMS_PATH; // without path offset

        //paths
        $this->webPath = $this->_pathCheck($this->getPath);
        $this->path    = $this->docRoot.$this->webPath;

        $this->_objImage = new ImageManager();
    }


    /**
     * checks whether the shop module is available and active
     * @return bool
     * @todo    Move this to a Shop Library static method
     */
    function _checkForShop() {
        global $objDatabase;
        if ( ($objRS = $objDatabase->SelectLimit("SELECT `id` FROM ".DBPREFIX."modules WHERE name = 'shop' AND status = 'y'", 1)) != false) {
            if ($objRS->RecordCount() > 0) {
                return true;
            }
        }
        return false;
    }


    /**
     * Gets the requested page
     * @global     array     $_ARRAYLANG,$_CONFIG
     * @return    string    parsed content
     */
    function getMediaPage()
    {
        global $_ARRAYLANG, $objTemplate;

        switch($this->getAct) {
            case 'newDir':
                $this->_createNewDir($_POST['dirName']);
                $this->_overviewMedia();
                break;
            case 'download':
                $this->_downloadMedia();
                //$this->_overviewMedia();
                break;
            case 'cut':
                $this->_cutMedia();
                $this->_overviewMedia();
                break;
            case 'copy':
                $this->_copyMedia();
                $this->_overviewMedia();
                break;
            case 'paste':
                $this->_pasteMedia();
                $this->_overviewMedia();
                break;
            case 'delete':
                $this->_deleteMedia();
                $this->_overviewMedia();
                break;
            case 'edit':
                $this->_editMedia();
                break;
            case 'preview':
                $this->_previewImage();
                break;
            case 'previewSize':
                $this->_previewImageSize();
                break;
            case 'ren':
                $this->_renMedia();
                $this->_overviewMedia();
                break;
            default:
                $this->_overviewMedia();
        }
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                => $this->pageTitle,
            'ADMIN_CONTENT'                => $this->_objTpl->get(),
        ));
    }


    /**
     * Overview Media Data
     * @global    array     $_ARRAYLANG
     * @global    array     $_CONFIG
     * @return    string    parsed content
     */
    function _overviewMedia()
    {
        global $_ARRAYLANG, $_CONFIG;

        $this->_objTpl->loadTemplateFile('module_media.html', true, true);

        switch ($this->archive) {
        case 'themes':
            $this->pageTitle = $_ARRAYLANG['TXT_DESIGN_FILES_ADMINISTRATION'];
            break;

        case 'content':
            $this->pageTitle = $_ARRAYLANG['TXT_IMAGE_ADMINISTRATION'];
            break;

        default:
            $this->pageTitle = $_ARRAYLANG['TXT_MEDIA_OVERVIEW'];
            break;
        }

        // cut, copy and paste session
        if (isset($_SESSION['mediaCutFile'])) {
            $tmpArray = array();
            foreach ($_SESSION['mediaCutFile'][2] as $tmp) {
                 if (file_exists($_SESSION['mediaCutFile'][0].$tmp)) {
                     $tmpArray[] = $tmp;
                 }
            }

            if (count($tmpArray) > 0) {
                $_SESSION['mediaCutFile'][0] = $_SESSION['mediaCutFile'][0];
                $_SESSION['mediaCutFile'][1] = $_SESSION['mediaCutFile'][1];
                $_SESSION['mediaCutFile'][2] = $tmpArray;
            } else {
                unset($_SESSION['mediaCutFile']);
            }
        }
        if (isset($_SESSION['mediaCopyFile'])) // copy
        {
            $tmpArray = array();
            foreach ($_SESSION['mediaCopyFile'][2] as $tmp) {
                 if (file_exists($_SESSION['mediaCopyFile'][0].$tmp)) {
                     $tmpArray[] = $tmp;
                 }
            }

            if (count($tmpArray) > 0)
            {
                $_SESSION['mediaCopyFile'][0] = $_SESSION['mediaCopyFile'][0];
                $_SESSION['mediaCopyFile'][1] = $_SESSION['mediaCopyFile'][1];
                $_SESSION['mediaCopyFile'][2] = $tmpArray;
            }
            else
            {
                unset($_SESSION['mediaCopyFile']);
            }
        }

        // tree navigation
        $tmp = $this->arrWebPaths[$this->archive];
        if (substr($this->webPath, 0, strlen($tmp)) == $tmp)
        {
            $this->_objTpl->setVariable(array(  // navigation #1
                'MEDIA_TREE_NAV_MAIN'      => 'http://'.$_SERVER['HTTP_HOST'].$this->arrWebPaths[$this->archive],
                'MEDIA_TREE_NAV_MAIN_HREF' => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;path='.$this->arrWebPaths[$this->archive]
            ));

            if (strlen($this->webPath) != strlen($tmp))
            {
                $tmpPath = substr($this->webPath, -(strlen($this->webPath) - strlen($tmp)));
                $tmpPath = explode('/', $tmpPath);
                $tmpLink = '';
                foreach ($tmpPath as $path)
                {
                    if (!empty($path))
                    {
                        $tmpLink .= $path.'/';
                        $this->_objTpl->setVariable(array(  // navigation #2
                            'MEDIA_TREE_NAV_DIR'      => $path,
                            'MEDIA_TREE_NAV_DIR_HREF' => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;path='.$this->arrWebPaths[$this->archive].$tmpLink
                        ));
                        $this->_objTpl->parse('mediaTreeNavigation');
                    }
                }
            }
        }

        /**
         * Uploader button handling
         */
        require_once ASCMS_CORE_MODULE_PATH.'/upload/share/uploadFactory.class.php';
        //data we want to remember for handling the uploaded files
        $data = array(
            'path' => $this->path,
            'webPath' => $this->webPath
        );

        $comboUp = UploadFactory::getInstance()->newUploader('exposedCombo');
        $comboUp->setFinishedCallback(array(ASCMS_CORE_MODULE_PATH.'/media/mediaLib.class.php', 'MediaLibrary', 'uploadFinished'));
        $comboUp->setData($data);
        //set instance name to combo_uploader so we are able to catch the instance with js
        $comboUp->setJsInstanceName('exposed_combo_uploader');
		$redirectUrl = CSRF::enhanceURI('index.php?'.$_SERVER['QUERY_STRING'].'&highlightUploadId='.$comboUp->getUploadId());
		$redirectUrl = str_replace('&act=delete', '', $redirectUrl);
        $comboUp->setRedirectUrl('cadmin/'.$redirectUrl);
        
        $this->_objTpl->setVariable(array(
              'FILEBROWSER_ADVANCED_UPLOAD_PATH'  => 'index.php?cmd=fileUploader&amp;standalone=true&amp;type='.$this->archive.'&amp;path='.urlencode(substr($this->webPath,strlen($this->arrWebPaths[$this->archive])-1)),
			  'REDIRECT_URL'					  => $redirectUrl,
              'TXT_MEDIA_FILE_UPLOADER'           => $_ARRAYLANG['TXT_MEDIA_FILE_UPLOADER'],
              'TXT_MEDIA_START_FILE_UPLOADER'     => $_ARRAYLANG['TXT_MEDIA_START_FILE_UPLOADER'],
              'TXT_MEDIA_FILE_UPLOADER_DESC'      => $_ARRAYLANG['TXT_MEDIA_FILE_UPLOADER_DESC'],
              'COMBO_UPLOADER_CODE'               => $comboUp->getXHtml(true)
        ));
        //end of uploader button handling

        //check if a finished upload caused reloading of the page.
        //if yes, we know the added files and want to highlight them
        if (!empty($_GET['highlightUploadId'])) {
            $key = 'media_upload_files_'.intval($_GET['highlightUploadId']);
            $sessionHighlightCandidates = $_SESSION[$key]; //an array with the filenames, set in mediaLib::uploadFinished
            //clean up session; we do only highlight once
            unset($_SESSION[$key]);

            if(is_array($sessionHighlightCandidates)) //make sure we don't cause any unexpected behaviour if we lost the session data
                $this->highlightName = $sessionHighlightCandidates;
        }

        // media directory tree
        $i       = 1;
        $dirTree = $this->_dirTree($this->path);
        $dirTree = $this->_sortDirTree($dirTree);

        foreach (array_keys($dirTree) as $key) {
            if (isset($dirTree[$key]['icon']) && is_array($dirTree[$key]['icon'])) {
                for ($x = 0; $x < count($dirTree[$key]['icon']); $x++) {
                    $fileName = $dirTree[$key]['name'][$x];
                    // colors
		    	    $class = ($x % 2) ? 'row2' : 'row1';
                    if (in_array($fileName, $this->highlightName)) {
                        $class .= '" style="background-color: '.$this->highlightColor.';';
                    }
                    if (isset($_SESSION['mediaCutFile']) && !empty($_SESSION['mediaCutFile'])) {
                        if (   $this->webPath == $_SESSION['mediaCutFile'][1]
                            && in_array($fileName, $_SESSION['mediaCutFile'][2])) {
                            $class .= '" style="background-color: '.$this->highlightCCColor.';';
                        }
                    }
                    if (isset($_SESSION['mediaCopyFile']) && !empty($_SESSION['mediaCopyFile'])) {
                        if (   $this->webPath == $_SESSION['mediaCopyFile'][1]
                            && in_array($fileName, $_SESSION['mediaCopyFile'][2])) {
                            $class .= '" style="background-color: '.$this->highlightCCColor.';';
                        }
                    }

                    // creates link
                    if ($key == 'dir') {
                        $tmpHref= 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;path='.$this->webPath.$fileName.'/';
                    } elseif ($key == 'file') {
                        if ($this->_isImage($this->path.$fileName)) {
                            $tmpHref = 'javascript:expandcontent(\'preview_'.$fileName.'\');';
                        } else {
                            $tmpHref = 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=download&amp;path='.$this->webPath.'&amp;file='.$fileName;
                        }
                    }

                    // show thumbnail
                    if ($this->_isImage($this->path.$fileName)) {
                        // make thumbnail if it doesn't exist
                        $tmpSize = @getimagesize($this->path.$fileName);
                        $thumb_name = ImageManager::getThumbnailFilename($fileName);
                        if ($tmpSize[1] > $this->thumbHeight) {
                            if (!file_exists($this->path.$thumb_name)) {
                                $this->_createThumbnail($this->path.$fileName);
                                clearstatcache();
                            }
                            if (!file_exists($this->path.$thumb_name)) {
                                // The thumbnail could not be created!
                                $tmpHref = 'javascript:preview(\''.$this->webPath.$fileName.'\','.$tmpSize[0].','.$tmpSize[1].');';
                                $thbSize = array(3 => '');
                                $thumb   = '';
                            } else {
                                $thbSize = @getimagesize($this->path.$thumb_name);
                                $thumb   = $this->webPath.$thumb_name;
                            }
                        } else {
                            $thbSize = $tmpSize;
                            $thumb   = $this->webPath.$fileName;
                        }
                        $this->_objTpl->setVariable(array(  // thumbnail
                            'MEDIA_FILE_NAME_SIZE'     => $tmpSize[0].' x '.$tmpSize[1],
                            'MEDIA_FILE_NAME_PRE'      =>'preview_'.$fileName,
                            'MEDIA_FILE_NAME_IMG_HREF' => $tmpHref,
                            'MEDIA_FILE_NAME_IMG_SRC'  => $thumb,
                            'MEDIA_FILE_NAME_IMG_SIZE' => $thbSize[3],
                        ));
                        $this->_objTpl->parse('mediaShowThumbnail');
                    }

                    $this->_objTpl->setVariable(array(
                        'MEDIA_DIR_TREE_ROW'  => $class,
                        'MEDIA_FILE_ICON'     => $this->iconWebPath.$dirTree[$key]['icon'][$x].'.gif',
                        'MEDIA_FILE_NAME'     => $fileName,
                        'MEDIA_FILE_SIZE'     => $this->_formatSize($dirTree[$key]['size'][$x]),
                        'MEDIA_FILE_TYPE'     => $this->_formatType($dirTree[$key]['type'][$x]),
                        'MEDIA_FILE_DATE'     => $this->_formatDate($dirTree[$key]['date'][$x]),
                        'MEDIA_FILE_PERM'     => $this->_formatPerm($dirTree[$key]['perm'][$x], $key),
                        'MEDIA_FILE_NAME_HREF'   => $tmpHref,
                        'MEDIA_FILE_EDIT_HREF'   => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=edit&amp;path='.$this->webPath.'&amp;file='.$fileName,
                        'MEDIA_EDIT'             => $_ARRAYLANG['TXT_MEDIA_EDIT'].': '.$fileName,
                        'MEDIA_FILE_DELETE_HREF' => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=delete&amp;path='.$this->webPath.'&amp;file='.$fileName,
                        'MEDIA_DELETE'           => $_ARRAYLANG['TXT_MEDIA_DELETE'].': '.$fileName
                    ));
                    $this->_objTpl->parse('mediaDirectoryTree');
                }
            }
        }

        // empty dir or php safe mode restriction
        if ($i == 0 || !@opendir($this->path)) {
            if (!@opendir($this->path)) {
                $tmpMessage = 'PHP Safe Mode Restriction!';
            } else {
                $tmpMessage = $_ARRAYLANG['TXT_MEDIA_DIR_EMPTY'];
            }

            $this->_objTpl->setVariable(array(
                'TXT_MEDIA_DIR_EMPTY'   => $tmpMessage,
                'MEDIA_SELECT_STATUS'   => ' disabled'
            ));
            $this->_objTpl->parse('mediaEmptyDirectory');
        } else {
            // not empty dir (select action)
            $this->_objTpl->setVariable(array(
                'TXT_SELECT_ALL'           => $_ARRAYLANG['TXT_SELECT_ALL'],
                'TXT_DESELECT_ALL'         => $_ARRAYLANG['TXT_DESELECT_ALL'],
                'TXT_MEDIA_SELECT_ACTION'  => $_ARRAYLANG['TXT_MEDIA_SELECT_ACTION'],
                'TXT_MEDIA_CUT'            => $_ARRAYLANG['TXT_MEDIA_CUT'],
                'TXT_MEDIA_COPY'           => $_ARRAYLANG['TXT_MEDIA_COPY'],
                'TXT_MEDIA_DELETE'         => $_ARRAYLANG['TXT_MEDIA_DELETE']
            ));
            $this->_objTpl->parse('mediaSelectAction');
            $this->_objTpl->setVariable('MEDIA_ARCHIVE', $this->archive);
        }
        // paste media
        if (isset($_SESSION['mediaCutFile']) or isset($_SESSION['mediaCopyFile'])) {
            $this->_objTpl->setVariable(array(
                'MEDIDA_PASTE_ACTION'      => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=paste&amp;path='.$this->webPath,
                'TXT_MEDIA_PASTE'          => $_ARRAYLANG['TXT_MEDIA_PASTE']
            ));
            $this->_objTpl->parse('mediaActionPaste');
        }

        // parse variables
        $tmpHref  = 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;path='.$this->webPath;
        $tmpIcon  = $this->_sortingIcons();
        $tmpClass  = $this->_sortingClass();

        $this->_objTpl->setVariable(array(  // java script
            'TXT_MEDIA_CHECK_NAME'      => $_ARRAYLANG['TXT_MEDIA_CHECK_NAME'],
            'TXT_MEDIA_CONFIRM_DELETE_2'  => $_ARRAYLANG['TXT_MEDIA_CONFIRM_DELETE_2'],
            'MEDIA_DO_ACTION_PATH'      => $this->webPath,
            'TXT_MEDIA_MAKE_SELECTION'  => $_ARRAYLANG['TXT_MEDIA_MAKE_SELECTION'],
            'TXT_MEDIA_SELECT_UPLOAD_FILE' => $_ARRAYLANG['TXT_MEDIA_SELECT_UPLOAD_FILE'],
            'MEDIA_JAVA_SCRIPT_PREVIEW' => $this->_getJavaScriptCodePreview(),
            'TXT_MEDIA_NEW_DIRECTORY'   => $_ARRAYLANG['TXT_MEDIA_NEW_DIRECTORY'],
            'MEDIA_CREATE_DIR_ACTION'   => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=newDir&amp;path='.$this->webPath,
            'TXT_MEDIA_NAME'            => $_ARRAYLANG['TXT_MEDIA_NAME'],
            'TXT_MEDIA_CREATE'          => $_ARRAYLANG['TXT_MEDIA_CREATE'],
            'TXT_MEDIA_UPLOAD_FILES'    => $_ARRAYLANG['TXT_MEDIA_UPLOAD_FILES'],
            'MEDIA_UPLOAD_FILES_ACTION' => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=upload&amp;path='.$this->webPath,
            'TXT_MEDIA_UPLOAD'          => $_ARRAYLANG['TXT_MEDIA_UPLOAD'],
            'TXT_MEDIA_FORCE_OVERWRITE' => $_ARRAYLANG['TXT_MEDIA_FORCE_OVERWRITE'],
            'MEDIA_NAME_HREF'           => $tmpHref.'&amp;sort=name&amp;sort_desc='. ($this->sortBy == 'name' && !$this->sortDesc),
            'MEDIA_SIZE_HREF'           => $tmpHref.'&amp;sort=size&amp;sort_desc='. ($this->sortBy == 'size' && !$this->sortDesc),
            'MEDIA_TYPE_HREF'           => $tmpHref.'&amp;sort=type&amp;sort_desc='. ($this->sortBy == 'type' && !$this->sortDesc),
            'MEDIA_DATE_HREF'           => $tmpHref.'&amp;sort=date&amp;sort_desc='. ($this->sortBy == 'date' && !$this->sortDesc),
            'MEDIA_PERM_HREF'           => $tmpHref.'&amp;sort=perm&amp;sort_desc='. ($this->sortBy == 'perm' && !$this->sortDesc),
            'TXT_MEDIA_FILE_NAME'       => $_ARRAYLANG['TXT_MEDIA_FILE_NAME'],
            'TXT_MEDIA_FILE_SIZE'       => $_ARRAYLANG['TXT_MEDIA_FILE_SIZE'],
            'TXT_MEDIA_FILE_TYPE'       => $_ARRAYLANG['TXT_MEDIA_FILE_TYPE'],
            'TXT_MEDIA_FILE_DATE'       => $_ARRAYLANG['TXT_MEDIA_FILE_DATE'],
            'TXT_MEDIA_FILE_PERM'       => $_ARRAYLANG['TXT_MEDIA_FILE_PERM'],
            'MEDIA_NAME_ICON'           => isset($tmpIcon['name']) ? $tmpIcon['name'] : '',
            'MEDIA_SIZE_ICON'           => isset($tmpIcon['size']) ? $tmpIcon['size'] : '',
            'MEDIA_TYPE_ICON'           => isset($tmpIcon['type']) ? $tmpIcon['type'] : '',
            'MEDIA_DATE_ICON'           => isset($tmpIcon['date']) ? $tmpIcon['date'] : '',
            'MEDIA_PERM_ICON'           => isset($tmpIcon['perm']) ? $tmpIcon['perm'] : '',
            'MEDIA_NAME_CLASS'           => isset($tmpClass['name']) ? $tmpIcon['name'] : '',
            'MEDIA_SIZE_CLASS'           => isset($tmpClass['size']) ? $tmpIcon['size'] : '',
            'MEDIA_TYPE_CLASS'           => isset($tmpClass['type']) ? $tmpIcon['type'] : '',
            'MEDIA_DATE_CLASS'           => isset($tmpClass['date']) ? $tmpIcon['date'] : '',
            'MEDIA_PERM_CLASS'           => isset($tmpClass['perm']) ? $tmpIcon['perm'] : '',
        ));
    }


    /**
     * Edit Media Data
     * @global     array     $_ARRAYLANG
     * @return    string    parsed content
     */
    function _editMedia()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('module_media_edit.html', true, true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIA_EDIT_FILE'];

        $check = true;
        (!isset($this->getFile) && empty($this->getFile)) ? $check = false : '';
        (!isset($this->getPath) && empty($this->getPath)) ? $check = false : '';
        (!file_exists($this->path.$this->getFile))      ? $check = false : '';

        if ($check == false) {
            // file doesn't exist
            $this->_objTpl->setVariable(array(  // ERROR
                'TXT_MEDIA_ERROR_OCCURED'    => $_ARRAYLANG['TXT_MEDIA_ERROR_OCCURED'],
                'TXT_MEDIA_FILE_DONT_EXISTS' => $_ARRAYLANG['TXT_MEDIA_FILE_DONT_EXISTS']
            ));
            $this->_objTpl->parse('mediaErrorFile');
        } elseif ($check == true) {
            // file exists
            $this->_objTpl->setVariable(array(  // java script
                'TXT_MEDIA_RENAME_NAME'  => $_ARRAYLANG['TXT_MEDIA_RENAME_NAME'],
                'TXT_MEDIA_RENAME_EXT'   => $_ARRAYLANG['TXT_MEDIA_RENAME_EXT'],
                'MEDIA_EDIT_ACTION'         => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=ren&amp;path='.$this->webPath,
                'MEDIA_EDIT_ACTION_PREVIEW' => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=preview&amp;path='.$this->webPath .'&amp;file='.$this->getFile,
                'MEDIA_EDIT_ACTION_PREVIEW_S' => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;act=previewSize&amp;path='.$this->webPath .'&amp;file='.$this->getFile,
                'TXT_MEDIA_EDIT_FILE'       => $_ARRAYLANG['TXT_MEDIA_EDIT_FILE'],
                'MEDIA_DIR'                 => $this->webPath,
                'MEDIA_FILE'                => $this->getFile,
                'TXT_MEDIA_INSERT_AS_COPY'  => $_ARRAYLANG['TXT_MEDIA_INSERT_AS_COPY'],
                'TXT_MEDIA_SAVE'            => $_ARRAYLANG['TXT_MEDIA_SAVE'],
                'TXT_MEDIA_RESET'           => $_ARRAYLANG['TXT_MEDIA_RESET'],
                'TXT_PREVIEW' => $_ARRAYLANG['TXT_PREVIEW'],
            ));

            $icon     = $this->_getIcon($this->path.$this->getFile);
            $fileName = $this->getFile;

            // extension
            if (is_file($this->path.$this->getFile)) {
                $info     = pathinfo($this->getFile);
                $fileExt  = $info['extension'];
                $ext      = (!empty($fileExt)) ? '.'.$fileExt : '';
                $fileName = substr($this->getFile, 0, strlen($this->getFile) - strlen($ext));

                $this->_objTpl->setVariable(array(
                    'MEDIA_ORGFILE_EXT'   => $fileExt.''
                ));
                $this->_objTpl->parse('mediaFileExt');
            }

            // edit name
            $this->_objTpl->setVariable(array(
                'MEDIA_FILE_ICON'     => $this->iconWebPath.$icon.'.gif',
                'MEDIA_ORGFILE_NAME'  => $fileName
            ));

            // edit image
            if ($this->_isImage($this->path.$this->getFile)) {
                $tmpSize  = @getimagesize($this->path.$this->getFile);
                $fileSize = $this->_formatSize($this->_getSize($this->path.$this->getFile));

                $this->_objTpl->setVariable(array(
                    'TXT_MEDIA_EDIT_IMAGE' => $_ARRAYLANG['TXT_MEDIA_EDIT_IMAGE'],
                    'TXT_MEDIA_NO'         => $_ARRAYLANG['TXT_MEDIA_NO'],
                    'TXT_MEDIA_YES'        => $_ARRAYLANG['TXT_MEDIA_YES'],
                ));
                $this->_objTpl->parse('mediaShowImage');
                $this->_objTpl->setVariable(array(
                    'TXT_MEDIA_EDIT_IMAGE' => $_ARRAYLANG['TXT_MEDIA_EDIT_IMAGE'],
                    'TXT_MEDIA_FILE_SIZE'  => $_ARRAYLANG['TXT_MEDIA_FILE_SIZE'],
                    'TXT_MEDIA_WIDTH'      => $_ARRAYLANG['TXT_MEDIA_WIDTH'],
                    'TXT_MEDIA_HEIGHT'     => $_ARRAYLANG['TXT_MEDIA_HEIGHT'],
                    'TXT_MEDIA_BALANCE'    => $_ARRAYLANG['TXT_MEDIA_BALANCE'],
                    'TXT_MEDIA_QUALITY'    => $_ARRAYLANG['TXT_MEDIA_QUALITY'],
                    'MEDIA_FILE_SIZE'      => $fileSize,
                    'MEDIA_IMG_WIDTH'      => $tmpSize[0],
                    'MEDIA_IMG_HEIGHT'     => $tmpSize[1],
                    'MEDIA_FILE_IMAGE_IMG' => $this->webPath.$this->getFile,
                ));
               $this->_objTpl->parse('mediaFileImage');
            }
            $this->_objTpl->parse('mediaFile');
        }
        // variables
        $this->_objTpl->setVariable(array(
            'TXT_MEDIA_BACK'        => $_ARRAYLANG['TXT_MEDIA_BACK'],
            'MEDIA_BACK_HREF'       => 'index.php?cmd=media&amp;archive='.$this->archive.'&amp;path='.$this->webPath,
        ));
    }

}

?>
