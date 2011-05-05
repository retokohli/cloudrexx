<?php

/**
 * Skins
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @package      contrexx
 * @subpackage   core
 * @version        1.1.0
 */

/**
 * @ignore
 */
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/File.class.php';
require_once ASCMS_LIBRARY_PATH.'/FRAMEWORK/Validator.class.php';

/**
 * Skins class
 *
 * Skins and Themes management functions
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @access        public
 * @package      contrexx
 * @subpackage   core
 * @version        1.1.0
 */
class skins
{
    /**
     * Title of the active page
     * @var string
     */

    public $pageTitle;
    /**
     * Error message
     * @var string
     */

    public $strErrMessage = '';
    /**
     * Success message
     * @var unknown_type
     */

    public $strOkMessage = '';
    /**
     * FRAMEWORK File object
     * @var object
     */

    public $_objFile;

    /**
     * Temporary archive location, relative to the document root
     * @var string
     */
    public $_archiveTempWebPath;

    /**
     * Temporary archive location, absolute path
     * @var string
     */

    public $_archiveTempPath;

    /**
     * Name of the theme directory
     * @var string
     */
    public $_themeDir;

    /**
     * Name of the theme
     * @var string
     */
    public $_themeName;

    /**
     * Holds archive files, for later purposes (see skins::_validateArchiveStructure())
     * @var array
     */
    public $_contentFiles = array();

    /**
     * Holds archive directories
     * @var array
     */
    public $_contentDirs = array();

    /**
     * Character encoding used by the XML parser
     * @access private
     * @var string
     */
    public $_xmlParserCharacterEncoding;

    /**
     * Defines the current XML element which is being parsed
     * @access private
     * @var array
     */
    public $_currentXmlElement;

    /**
     * Contains the referencies to the parent XML elements
     * of each XML element
     * @access private
     * @var array
     */
    public $_arrParentXmlElement = array();

    /**
     * Structure with data of the XML document
     * @access private
     * @var array
     */
    public $_xmlDocument;

    /**
     * Defines the relevant XML element that
     * will occurs multiple times in the XML document
     * @access private
     * @var string
     */
    public $_xmlElementName = '';

    /**
     * Required files
     * @var array
     */
    public $filenames = array("index.html","style.css","content.html","home.html","navbar.html","subnavbar.html","sidebar.html","shopnavbar.html","headlines.html","events.html","javascript.js","buildin_style.css","directory.html","info.xml","forum.html","podcast.html","blog.html","immo.html");

    /**
     * Required directories
     * @var array
     */
    public $directories = array("images/",);

    /**
     * File extenstions to display in filelist
     * @var array
     */
    public $fileextensions = array("htm", "shtml", "html", "txt", "css", "js", "php", "java", "tpl", "xml",);

    /**
     * Subdirectores and contents of selected theme
     * @var array
     */
    public $subDirs = array();

    /**
     * Path to the parent directory of the theme
     * @var string
     */
    public $_parentPath = '';


    public $arrWebPaths;                      // array web paths
    public $getAct;                           // $_GET['act']
    public $getPath;                          // $_GET['path']
    public $path;                             // current path
    public $dirLog;                           // Dir Log
    public $webPath;                          // current web path
    public $tableExists;                      // Table exists
    public $oldTable;                         // old Theme-Table name


    function __construct()
    {
        global  $_CORELANG, $objTemplate, $objDatabase;

        $this->_xmlParserCharacterEncoding = CONTREXX_CHARSET;
        //add preview.gif to required files
        $this->filenames[] = "images".DIRECTORY_SEPARATOR."preview.gif";
        //get path variables
        $this->path = ASCMS_THEMES_PATH.'/';
        $this->arrWebPaths  = array(ASCMS_THEMES_WEB_PATH.'/');
        $this->themeZipPath = '/themezips/';
        $this->_archiveTempWebPath = ASCMS_TEMP_WEB_PATH.$this->themeZipPath;
        $this->_archiveTempPath = ASCMS_PATH.$this->_archiveTempWebPath;
        $this->_objFile = new File();
        //create /tmp/zip path if it doesnt exists
        if (!file_exists($this->_archiveTempPath)){
            if ($this->_objFile->mkDir(ASCMS_TEMP_PATH, ASCMS_TEMP_WEB_PATH, $this->themeZipPath ) == 'error'){
                $this->strErrMessage=ASCMS_TEMP_PATH.$this->themeZipPath .":".$_CORELANG['TXT_THEME_UNABLE_TO_CREATE'];
            }
        }
        $this->webPath = $this->arrWebPaths[0];
        if (substr($this->webPath, -1) != '/'){
            $this->webPath = $this->webPath . '/';
        }

        $objTemplate->setVariable("CONTENT_NAVIGATION",
                          "<a href='index.php?cmd=skins'>".$_CORELANG['TXT_DESIGN_OVERVIEW']."</a>
                           <a href='index.php?cmd=skins&amp;act=newDir'>".$_CORELANG['TXT_NEW_DESIGN']."</a>
                           <a href='index.php?cmd=skins&amp;act=activate'>".$_CORELANG['TXT_ACTIVATE_DESIGN']."</a>
                           <a href='index.php?cmd=media&amp;archive=themes'>".$_CORELANG['TXT_DESIGN_FILES_ADMINISTRATION']."</a>
                           <a href='index.php?cmd=skins&amp;act=examples'>".$_CORELANG['TXT_DESIGN_REPLACEMENTS_DIR']."</a>
                           <a href='index.php?cmd=skins&amp;act=manage'>".$_CORELANG['TXT_THEME_IMPORT_EXPORT']."</a>");
        $objDatabase->Execute("OPTIMIZE TABLE ".DBPREFIX."skins");
        $this->oldTable = DBPREFIX."themes";
        $this->_objFile->setChmod($this->path, $this->webPath, "");
    }

    /**
     * Gets the requested page
     * @global   HTML_Template_Sigma
     * @return    string    parsed content
     */
    function getPage()
    {
        global $objTemplate;

        if (!isset($_GET['act'])) {
            $_GET['act']="";
        }
        switch($_GET['act']){
            case "activate":
                $this->_activate();
            break;
            case "examples":
                $this->examples();
            break;
            case "manage":
                $this->_manage();
            break;
            case "upload":
                $this->upload();
                $this->overview();
            break;
            case "update":
                $this->update();
                $this->overview();
            break;
            case "newDir":
                $this->newdir();

            break;
            case "createDir":
                $this->createdir();
                //$this->overview();
            break;
            case "newFile":
                $this->newfile();
                $this->overview();
            break;
            default:
                $this->newfile();
                $this->delfile();
                $this->deldir();
                $this->overview();
        }
        $objTemplate->setVariable(array(
            'CONTENT_TITLE'             => $this->pageTitle,
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
        ));
    }

    /**
     * show the overview page
     * @access   public
     */
    function overview()
    {
        global $_CORELANG, $objTemplate;

        Permission::checkAccess(47, 'static');

        // initialize variables
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'skins_content', 'skins_content.html');
        $this->pageTitle = $_CORELANG['TXT_OVERVIEW'];
        $objTemplate->setVariable(array(
            'TXT_CHOOSE_TEMPLATE_GROUP'       =>     $_CORELANG['TXT_CHOOSE_TEMPLATE_GROUP'],
            'TXT_SELECT_FILE'                 =>     $_CORELANG['TXT_SELECT_FILE'],
            'TXT_DESIGN_FOLDER'               =>     $_CORELANG['TXT_DESIGN_FOLDER'],
            'TXT_TEMPLATE_FILES'              =>     $_CORELANG['TXT_TEMPLATE_FILES'],
            'TXT_FILE_EDITOR'                 =>     $_CORELANG['TXT_FILE_EDITOR'],
            'TXT_UPLOAD_FILES'                =>     $_CORELANG['TXT_UPLOAD_FILES'],
            'TXT_CREATE_FILE'                 =>     $_CORELANG['TXT_CREATE_FILE'],
            'TXT_DELETE'                      =>     $_CORELANG['TXT_DELETE'],
            'TXT_STORE'                       =>     $_CORELANG['TXT_SAVE'],
            'TXT_RESET'                       =>     $_CORELANG['TXT_RESET'],
            'TXT_SELECT_ALL'                  =>     $_CORELANG['TXT_SELECT_ALL'],
            'TXT_MANAGE_FILES'                =>     $_CORELANG['TXT_MANAGE_FILES'],
            'TXT_SELECT_THEME'                =>     $_CORELANG['TXT_SELECT_THEME'],
            'TXT_THEME_NAME'                  =>     $_CORELANG['TXT_THEME_NAME'],
            'TXT_DESIGN_OVERVIEW'             =>     $_CORELANG['TXT_DESIGN_OVERVIEW'],
            'TXT_MODE'                        =>     $_CORELANG['TXT_MODE']
        ));
        $this->getDropdownContent();
    }

    /**
     * set up Import/Export page
     * call specific function depending on $_GET
     * @access private
     */
    function _manage()
    {
        global $_CORELANG, $objTemplate, $objDatabase;

        Permission::checkAccess(102, 'static');

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'skins_manage', 'skins_manage.html');
        $this->pageTitle = $_CORELANG['TXT_THEME_IMPORT_EXPORT'];
        //check GETs for action
        if (!empty($_GET['preview'])){
            CSRF::header("Location: ../?preview=".$_GET['preview']);
            exit;
        }
        if (!empty($_GET['export'])){
            $archiveURL=$this->_exportFile();
            if (is_string($archiveURL)){
                CSRF::header("Location: ".$archiveURL);
                exit;
            }
        }
        if (!empty($_GET['import'])){
            $this->_importFile();

        }
        if (!empty($_GET['activate'])){
            $this->_activateDefault(intval($_GET['activate']));
        }
        if (!empty($_GET['delete'])){
            $this->deldir(true);
            $objTemplate->setVariable('THEMES_MENU', $this->getThemesDropdown());
        }
        //set template variables
        $objTemplate->setGlobalVariable(array(  'TXT_THEME_THEMES'              => $_CORELANG['TXT_THEME_THEMES'],
                                            'TXT_THEME_PREVIEW'             => $_CORELANG['TXT_THEME_PREVIEW'],
                                            'TXT_THEME_IMPORT_EXPORT'       => $_CORELANG['TXT_THEME_IMPORT_EXPORT'],
                                            'TXT_THEME_NAME'                => $_CORELANG['TXT_THEME_NAME'],
                                            'TXT_THEME_IMPORT'              => $_CORELANG['TXT_THEME_IMPORT'],
                                            'TXT_THEME_EXPORT'              => $_CORELANG['TXT_THEME_EXPORT'],
                                            'TXT_THEME_PREVIEW_NEW_WINDOW'  => $_CORELANG['TXT_THEME_PREVIEW_NEW_WINDOW'],
                                            'TXT_THEME_DIRECTORY_NAME'      => $_CORELANG['TXT_THEME_DIRECTORY_NAME'],
                                            'TXT_THEME_SEND_ARCHIVE'        => $_CORELANG['TXT_THEME_SEND_ARCHIVE'],
                                            'TXT_THEME_IMPORT_ARCHIVE'      => $_CORELANG['TXT_THEME_IMPORT_ARCHIVE'],
                                            'TXT_THEME_LOCAL_FILE'          => $_CORELANG['TXT_THEME_LOCAL_FILE'],
                                            'TXT_THEME_SPECIFY_URL'         => $_CORELANG['TXT_THEME_SPECIFY_URL'],
                                            'TXT_THEME_CONFIRM_DELETE'      => $_CORELANG['TXT_THEME_CONFIRM_DELETE'],
                                            'TXT_FUNCTIONS'                 => $_CORELANG['TXT_FUNCTIONS'],
                                            'TXT_NAME'                      => $_CORELANG['TXT_NAME'],
                                            'TXT_THEME_SHOW_PRINT_THEME'    => $_CORELANG['TXT_THEME_SHOW_PRINT_THEME'],
                                            'TXT_THEME_NO_URL_SPECIFIED'    => $_CORELANG['TXT_THEME_NO_URL_SPECIFIED'],
                                            'TXT_THEME_NO_FILE_SPECIFIED'   => $_CORELANG['TXT_THEME_NO_FILE_SPECIFIED'],
                                            'TXT_THEME_DETAILS'             => $_CORELANG['TXT_THEME_DETAILS'],
                                            'TXT_THEME_IMPORT_INFO'         => $_CORELANG['TXT_THEME_IMPORT_INFO'],
                                            'TXT_THEME_IMPORT_INFO_BODY'    => $_CORELANG['TXT_THEME_IMPORT_INFO_BODY'],
                                            'TXT_SKINS_PREVIEW'             => $_CORELANG['TXT_SKINS_PREVIEW'],
                                            'THEMES_MENU'                   => $this->getThemesDropdown()
        ));
        //create themelist
        $themes = $this->_getThemes();
        if ($themes !== false){
            $rowclass = 0;
            foreach ($this->_getThemes() as $theme) {
                $extra = (!empty($theme['extra'])) ? $theme['extra'] : '';

                $this->_getXML($theme['foldername']);

                $htmlDeleteLink = '<a onclick="showInfo(this.parentNode.parentNode); return confirmDelete(\''.htmlspecialchars($theme['themesname'], ENT_QUOTES, CONTREXX_CHARSET).'\');" href="?cmd=skins&amp;act=manage&amp;delete='.urlencode($theme['themesname']).'" title="'.$_CORELANG['TXT_DELETE'].'"> <img border="0" src="images/icons/delete.gif" alt="" /> </a>';
                $htmlActivateLink = '<a onclick="showInfo(this.parentNode.parentNode);" href="?cmd=skins&amp;act=manage&amp;activate='.$theme['id'].'" title="'.$_CORELANG['TXT_ACTIVATE_DESIGN'].'"> <img border="0" src="images/icons/check.gif" alt="" /> </a>';

                $objTemplate->setVariable(array('THEME_NAME'            =>  $theme['themesname'],
                                                'THEME_NAME_EXTRA'      =>  $theme['themesname'].' '.$extra,
                                                'THEME_PREVIEW'         =>  $this->_getPreview($theme['themesname']),
                                                'TXT_THEME_EXPORT'      =>  $_CORELANG['TXT_THEME_EXPORT'],
                                                'TXT_DELETE'            =>  $_CORELANG['TXT_DELETE'],
                                                'THEME_DELETE_LINK'     =>  (empty($extra)) ? $htmlDeleteLink : '',
                                                'THEME_ACTIVATE_LINK'   =>  (empty($extra)) ? $htmlActivateLink : '',
                                                'THEME_ID'              =>  $theme['id'],
                                                'TXT_ACTIVATE_DESIGN'   =>  $_CORELANG['TXT_ACTIVATE_DESIGN'],
                                                'ROW_CLASS'             =>  (!empty($extra) ? 'rowWarn' : (($rowclass++ % 2) ? 'row1' : 'row2')),
                                                'THEME_XML_AUTHOR'      =>  $this->_xmlDocument['THEME']['AUTHORS']['AUTHOR']['USER']['cdata'],
                                                'THEME_XML_VERSION'     =>  $this->_xmlDocument['THEME']['VERSION']['cdata'],
                                                'THEME_XML_DESCRIPTION' =>  $this->_xmlDocument['THEME']['DESCRIPTION']['cdata'],


                ));
                $objTemplate->parse('themeRow');
            }
        }
    }

    /**
     * check if images/preview.gif exists and return webpath
     * return the default preview if it doesnt exists
     * @access private
     * @param string $themedir
     * @return string
     */
    function _getPreview($themedir){
        if (file_exists($this->path.$themedir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'preview.gif')){
            return ASCMS_THEMES_WEB_PATH . '/'.$themedir.'/images/preview.gif';
        } else {
            return ASCMS_ADMIN_TEMPLATE_WEB_PATH.'/images/preview.gif';
        }
    }


    /**
     * clean tmp folder
     * @access private
     * @return void
     */
    function _cleantmp()
    {
        if (is_dir($this->_archiveTempPath)) {
            $dh = opendir($this->_archiveTempPath);
            if ($dh) {
                $file = readdir($dh);
                while ($file !== false) {
                    if ($file != '..' && $file != '.' && is_file($this->_archiveTempPath.$file)){
                        unlink($this->_archiveTempPath.$file);
                    }
                    $file = readdir($dh);
                }
                closedir($dh);
            }
        }
    }

    /**
     * check fileupload and move uploaded file if it's a valid archive
     * @return unknown
     */

    function _checkUpload()
    {
        global $_CORELANG;

        if (!isset($_FILES['importlocal'])){
            $this->strErrMessage="POST Request Error. 'importlocal' is empty";
            return false;
        }
        // Check for MIME type and whether it's a zip file
        // (not all browsers provide the type)
        if (   is_uploaded_file($_FILES['importlocal']['tmp_name'])
            && is_file($_FILES['importlocal']['tmp_name'])) {
            if (isset($_FILES['importlocal']['type'])) {
                if (   !preg_match('/zip$/i', $_FILES['importlocal']['type'])
                   && !(   preg_match('/binary$/', $_FILES['importlocal']['type'])
                        || preg_match('/application\/octet\-?stream/', $_FILES['importlocal']['type'])
                        || preg_match('/application\/x-zip-compressed/', $_FILES['importlocal']['type']))) {
                    $this->strErrMessage =
                        $_FILES['importlocal']['name'].': '.
                        $_CORELANG['TXT_THEME_IMPORT_WRONG_MIMETYPE'].': '.
                        $_FILES['importlocal']['type'];
                    return false;
                }
            }
        } else {
            $this->strErrMessage = $_CORELANG['TXT_COULD_NOT_UPLOAD_FILE'];
            return false;
        }
        // Move the uploaded file to the themezip location
        if (!move_uploaded_file($_FILES['importlocal']['tmp_name'], $this->_archiveTempPath.basename($_FILES['importlocal']['name']))) {
            $this->strErrMessage = $this->_archiveTempPath.basename($_FILES['importlocal']['name']).': '.$_CORELANG['TXT_COULD_NOT_UPLOAD_FILE'];
            return false;
        }
        return true;
    }


    /**
     * check for valid archive structure, put directories and files into _contentDirs and _contentFiles array
     * set errormessage if structure not valid
     * @access private
     * @param array $content file and directory list
     * @return boolean
     */
    function _validateArchiveStructure($content)
    {
        global $_CORELANG;

        //check if archive is empty
        if (sizeof($content) == 0){
            $this->strErrMessage = $_FILES['importlocal']['name'].': '.$_CORELANG['TXT_THEME_ARCHIVE_WRONG_STRUCTURE'];
            return false;
        }

        $first_item = $content[0];
        $this->_themeDir  = substr($first_item['stored_filename'], 0, strpos($first_item['stored_filename'], '/'));
        $this->_themeName = (!empty($_POST['theme_dbname'])) ? contrexx_addslashes($_POST['theme_dbname']) : $this->_themeDir ;

        $this->_contentDirs[] = $this->_themeDir;

        foreach ($content as $item){
            //check if current file/directory contains the base directory and abort when not true
            if (strpos($item['stored_filename'], $this->_themeDir) !== 0){
                $this->strErrMessage = $_FILES['importlocal']['name'].': '.$_CORELANG['TXT_THEME_ARCHIVE_WRONG_STRUCTURE'];
                return false;
            }

            //check if current archive item is a directory
            if ($item['folder'] == 1){
                //check if its the base directory
                if (basename($item['stored_filename']) == $this->_themeDir){
                    //take the whole string, this is the archive base dircotry
                    $this->_contentDirs[] = $item['stored_filename'];
                } else {
                    //only take the most top directory
                    $this->_contentDirs[] = substr($item['stored_filename'], strlen($this->_contentDirs[0]));
                }
            } else {
                //its a file, only take the part relative to the base directory
                $this->_contentFiles[] = substr($item['stored_filename'], strlen($this->_contentDirs[0]));
            }
        }
        //add images directory if it wasn't in the archive
        foreach ($this->directories as $dir) {
            if (!in_array($dir, $this->_contentDirs)){
                $this->_contentDirs[] = $dir;
            }
        }
        return true;
    }

    /**
     * Create the directory structure of the archive contents and set permissions
     * @return boolean
     */

    function _createDirStructure(){
        global $_CORELANG;
        //create archive structure and set permissions
        //this is an important step on hostings where the FTP user ID differs from the PHP user ID
        foreach ($this->_contentDirs as $index => $directory){
            switch($index){
                //check if theme directory already exists
                case 0:
                    if (file_exists($this->path.$directory)){
                        $this->strErrMessage = $this->_themeDir.': '.$_CORELANG['TXT_THEME_FOLDER_ALREADY_EXISTS'].'! '.$_CORELANG['TXT_THEME_FOLDER_DELETE_FIRST'].'.';
                        return false;
                    }

                    $this->_objFile->mkDir($this->path, ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR, $directory);
                    $this->_objFile->setChmod($this->path, ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR, $directory);
                    break;
                default:
                    $this->_objFile->mkDir($this->path, ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR, $this->_contentDirs[0].DIRECTORY_SEPARATOR.$directory);
                    $this->_objFile->setChmod($this->path, ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR, $this->_contentDirs[0].DIRECTORY_SEPARATOR.$directory);
            }
        }
        return true;
    }


    /**
     * Extracts the archive to the themes path
     * @param pclZip Object $archive
     * @return boolean
     */
    function _extractArchive($archive)
    {
        global $_CORELANG;
        $valid_exts = array(
            "txt","doc","xls","pdf","ppt","gif","jpg","png","xml",
            "odt","ott","sxw","stw","dot","rtf","sdw","wpd","jtd",
            "jtt","hwp","wps","ods","ots","sxc","stc","dif","dbf",
            "xlw","xlt","sdc","vor","sdc","cvs","slk","wk1","wks",
            "123","odp","otp","sxi","sti","pps","pot","sxd","sda",
            "sdd","sdp","cgm","odg","otg","sxd","std","dxf","emf",
            "eps","met","pct","sgf","sgv","svm","wmf","bmp","jpeg",
            "jfif","jif","jpe","pbm","pcx","pgm","ppm","psd","ras",
            "tga","tif","tiff","xbm","xpm","pcd","oth","odm","sxg",
            "sgl","odb","odf","sxm","smf","mml","zip","rar","htm",
            "html","shtml","css","js","tpl","thumb","ico"
        );
        if (($files = $archive->extract(PCLZIP_OPT_PATH, $this->path, PCLZIP_OPT_BY_PREG, '/('.implode('|', $valid_exts).')$/')) != 0){
            //required files array
            $reqFiles = $this->filenames;
            foreach ($files as $file) {
                //check status for errors while extracting the archive
                if (!in_array($file['status'],array('ok','filtered','already_a_directory'))){
                    $this->strErrMessage = $_CORELANG['TXT_THEME_ARCHIVE_ERROR'].': '.$archive->errorInfo(true);
                    return false;
                } else {
                    //if no errors, set permission
                    $this->_objFile->setChmod($this->path, ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR, $file['stored_filename']);
                    //if file is in required files array, remove it
					// use '/' instead of DIRECTORY_SEPARATOR; PclZip always returns posix-style paths --fs
                    if (($reqFileIndex = array_search(substr(strstr($file['stored_filename'],'/'), 1), $reqFiles)) !== false){
                        unset($reqFiles[$reqFileIndex]);
                    }
                }
            }
            //all files left in $reqFiles haven't been found, create empty files
            foreach ($reqFiles as $reqFile){
                switch($reqFile){
                    //if no preview thumbnail in archive then copy default
                    case 'images'.DIRECTORY_SEPARATOR.'preview.gif':
                        $this->_objFile->copyFile(ASCMS_ADMIN_TEMPLATE_PATH.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR, 'preview.gif', $this->path.$this->_themeDir.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR, 'preview.gif');
                        break;
                    default:
                        $fh=fopen($this->path.$this->_themeDir.DIRECTORY_SEPARATOR.$reqFile,'w+');
                        fputs($fh,'');
                        fclose($fh);
                        $this->_objFile->setChmod($this->path.$this->_themeDir.DIRECTORY_SEPARATOR, ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR.$this->_themeDir.DIRECTORY_SEPARATOR, $reqFile);
                }
            }
        } else {
            $this->strErrMessage = $_CORELANG['TXT_THEME_ARCHIVE_ERROR'].': '.$archive->errorInfo(true);
            return false;
        }
        return true;
    }


    /**
     * import themes from archive
     * @access   private
     * @param    string   $themes
     */
    function _importFile()
    {
        global $_CORELANG;
        require_once(ASCMS_LIBRARY_PATH.'/pclzip/pclzip.lib.php');

        $this->_cleantmp();
        switch($_GET['import']){
            case 'remote':
                $archiveFile = $this->_fetchRemoteFile($_POST['importremote']);
                if ($archiveFile === false){
                    return false;
                }
                $archive = new PclZip($archiveFile);
                //no break
            case 'local':
                if (empty($archive)){
                    if ($this->_checkUpload() === false){
                        return false;
                    }
                    $archive = new PclZip($this->_archiveTempPath.basename($_FILES['importlocal']['name']));
                }
                $content = $archive->listContent();
                if ($this->_validateArchiveStructure($content) === false){
                    return false;
                }
                if ($this->_createDirStructure() ===  false){
                    return false;
                }
                //extract archive files
                $this->_extractArchive($archive);
                //create database entry
                if (substr($this->_themeName, -1) == '/'){
                    $this->_themeName = substr($this->_themeName, 0, -1);
                }
                $this->insertIntoDb(contrexx_addslashes($this->_themeName), $this->_themeDir);
                $this->strOkMessage = $this->_themeName.' ('.$this->_themeDir.') '.$_CORELANG['TXT_THEME_SUCCESSFULLY_IMPORTED'];
                break;
            //everything else should never be the case
            default:
                $this->strErrMessage="GET Request Error. 'import' should be either 'local' or 'remote'";
                return false;
        }
        return true;
    }


    function _fetchRemoteFile($URL)
    {
        global $_CORELANG;

        $URL = parse_url($URL);
        $errno = $errstr = null;
        $http = @fsockopen($URL['host'], !empty($URL['port']) ? intval($URL['port']) : 80 , $errno, $errstr, 10);
        if ($http){
            $archive = '';
            fputs($http,'GET /'.$URL['path']." HTTP/1.1\r\n");
            fputs($http,'Host: '.$URL['host']."\r\n\r\n");
            while(!feof($http)){
                $archive .= fgets($http, 1024);
            }
            fclose($http);
            //cut off HTTP headers, PKZIP header = "\x50\x4B\x03\x04"
            $archive=strstr($archive,"\x50\x4B\x03\x04");
            if ($archive == ''){
                $this->strErrMessage = $_CORELANG['TXT_THEME_IMPORT_WRONG_MIMETYPE'];
                return false;
            }
            $tmpfilename = basename($URL['path']);
            if (strlen($tmpfilename) < 3) $tmpfilename = '_unknown_upload_';
            $tempFile = ASCMS_TEMP_PATH.DIRECTORY_SEPARATOR.$tmpfilename.microtime().'.zip';
            $fh = fopen($tempFile,'w');
            fputs($fh, $archive);
            return $tempFile;
        } else {
            $this->strErrMessage = $_CORELANG['TXT_THEME_HTTP_CONNECTION_FAILED'];
            return false;
        }
    }

    /**
     * export theme as archive
     * @access   private
     * @param    string   theme folder name
     * @return   string   path to the created archive
     */
    function _exportFile()
    {
        require_once(ASCMS_LIBRARY_PATH.'/pclzip/pclzip.lib.php');
        global $_CORELANG;
        //clean up tmp folder
        $this->_cleantmp();
        //path traversal security check
        $theme=str_replace(array('..','/'), '', trim(html_entity_decode(urldecode($_GET['export']))));
        if (!$theme){
            $this->strErrMessage = $_CORELANG['TXT_THEME_FOLDER_DOES_NOT_EXISTS'];
            return false;
        }
        if (file_exists($this->path.$theme)){
            $archive = new PclZip($this->_archiveTempPath.$theme.'.zip');
            $this->_objFile->setChmod($this->_archiveTempPath, $this->_archiveTempWebPath,'');
            $files = $archive->create($this->path.$theme, PCLZIP_OPT_REMOVE_PATH, $this->path);
            if (!is_array($files)){
                $this->strErrMessage = $this->_archiveTempPath.$theme.'.zip'.': '.$_CORELANG['TXT_THEME_UNABLE_TO_CREATE'];
                return false;
            }
            foreach($files as $file){
                //status check
                if (!in_array($file['status'],array('ok','filtered'))){
                    $this->strErrMessage = $_CORELANG['TXT_THEME_ARCHIVE_ERROR'].': '.$archive->errorInfo(true);
                    return false;
                }
                $this->_objFile->setChmod($this->_archiveTempPath, $this->_archiveTempWebPath , $theme.'.zip');
                return $this->_archiveTempWebPath.$theme.'.zip';
            }
// TODO: Seems that the else block is useless; should set the error message
// anyway and return false!
        } else {
            $this->strErrMessage = $_CORELANG['TXT_THEME_FOLDER_DOES_NOT_EXISTS'];
        }
    }


    /**
     * Activates the Theme for the current default language
     * @access private
     * @return boolean
     */
    function _activateDefault(){
        global $objDatabase, $_CORELANG;
        $themeID = intval($_GET['activate']);
        if ($themeID == 0){
            $this->strErrMessage = "GET value error. Must be numeric ID.";
        }
        $objRS = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."languages WHERE is_default = 'true' LIMIT 1");
        if ($objRS->RecordCount() != 0){
            $langID = $objRS->fields['id'];
            $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET themesid='".intval($themeID)."' WHERE id=".intval($langID));
            $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
        } else {
            $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            return false;
        }
        return true;
    }


    /**
     * Gets the themes assigning page
     * @access   private
     * @global   ADONewConnection
     * @global   array
     * @global   HTML_Template_Sigma
     * @return   string   parsed content
     */
    function _activate()
    {
        global $objDatabase, $_CORELANG, $objTemplate;

        Permission::checkAccess(46, 'static');

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'skins_active', 'skins_activate.html');
        $this->pageTitle = $_CORELANG['TXT_ACTIVATE_DESIGN'];
        $objTemplate->setVariable(array(
            'TXT_ACTIVATE_DESIGN'      => $_CORELANG['TXT_ACTIVATE_DESIGN'],
            'TXT_ID'                   => $_CORELANG['TXT_ID'],
            'TXT_LANGUAGE'             => $_CORELANG['TXT_LANGUAGE'],
            'TXT_ACTIVE_TEMPLATE'      => $_CORELANG['TXT_ACTIVE_TEMPLATE'],
            'TXT_ACTIVE_PDF_TEMPLATE'      => $_CORELANG['TXT_ACTIVE_PDF_TEMPLATE'],
            'TXT_ACTIVE_PRINT_TEMPLATE' => $_CORELANG['TXT_ACTIVE_PRINT_TEMPLATE'],
            'TXT_SAVE'    => $_CORELANG['TXT_SAVE'],
            'TXT_THEME_ACTIVATE_INFO'    => $_CORELANG['TXT_THEME_ACTIVATE_INFO'],
            'TXT_THEME_ACTIVATE_INFO_BODY'    => $_CORELANG['TXT_THEME_ACTIVATE_INFO_BODY'],
            'TXT_ACTIVE_MOBILE_TEMPLATE' => $_CORELANG['TXT_ACTIVE_MOBILE_TEMPLATE']
        ));
        $i=0;

        if (isset($_POST['themesId'])) {
            foreach ($_POST['themesId'] as $langid => $themesId) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET themesid='".intval($themesId)."' WHERE id=".intval($langid));
            }
            foreach ($_POST['printThemesId'] as $langid => $printThemesId) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET print_themes_id='".intval($printThemesId)."' WHERE id=".intval($langid));
            }
            foreach ($_POST['pdfThemesId'] as $langid => $pdfThemesId) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET pdf_themes_id='".intval($pdfThemesId)."' WHERE id=".intval($langid));
            }
            foreach ($_POST['mobileThemesId'] as $langid => $mobileThemesId) {
                $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET mobile_themes_id='".intval($mobileThemesId)."' WHERE id=".intval($langid));
            }
            $this->strOkMessage = $_CORELANG['TXT_DATA_RECORD_UPDATED_SUCCESSFUL'];
        }
        $objResult = $objDatabase->Execute("
           SELECT   id,lang,name,frontend,
                    themesid,mobile_themes_id,print_themes_id,pdf_themes_id
           FROM     ".DBPREFIX."languages
           ORDER BY id
        ");

        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if (($i % 2) == 0) {
                    $class="row1";
                } else {
                    $class="row2";
                }

                if ( $objResult->fields['frontend']){
                    $class="rowWarn";
                }
                $objTemplate->setVariable(array(
                    'THEMES_ROWCLASS'           => $class,
                    'THEMES_LANG_ID'            => $objResult->fields['id'],
                    'THEMES_LANG_SHORTNAME'     => $objResult->fields['lang'],
                    'THEMES_LANG_NAME'          => $objResult->fields['name'],
                    'THEMES_TEMPLATE_MENU'      => $this->_getDropdownActivated($objResult->fields['themesid']),
                    'THEMES_PRINT_TEMPLATE_MENU' => $this->_getDropdownActivated($objResult->fields['print_themes_id']),
                    'THEMES_MOBILE_TEMPLATE_MENU' => $this->_getDropdownActivated($objResult->fields['mobile_themes_id']),
                    'THEMES_PDF_TEMPLATE_MENU' => $this->_getDropdownActivated($objResult->fields['pdf_themes_id']),
                ));
                $objTemplate->parse('themesLangRow');
                $i++;
                $objResult->MoveNext();
            }
        }
    }

    /**
     * Gets the themes example page
     * @access   public
     */
    function examples()
    {
        global $_CORELANG, $objTemplate;

        Permission::checkAccess(47, 'static');

        // initialize variables
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'skins_examples', 'skins_examples.html');
        $this->pageTitle = $_CORELANG['TXT_DESIGN_VARIABLES_LIST'];
        $objTemplate->setVariable(array(
            'TXT_STANDARD_TEMPLATE_STRUCTURE' => $_CORELANG['TXT_STANDARD_TEMPLATE_STRUCTURE'],
            'TXT_FRONTEND_EDITING_LOGIN_FRONTEND' => $_CORELANG['TXT_FRONTEND_EDITING_LOGIN_FRONTEND'],
            'TXT_STARTPAGE'                   => $_CORELANG['TXT_STARTPAGE'],
            'TXT_STANDARD_PAGES'              => $_CORELANG['TXT_STANDARD_PAGES'],
            'TXT_REPLACEMENT_LIST'            => $_CORELANG['TXT_REPLACEMENT_LIST'],
            'TXT_FILES'                       => $_CORELANG['TXT_FILES'],
            'TXT_CONTENTS'                    => $_CORELANG['TXT_CONTENTS']
        ));
    }

    /**
     * create skin folder page
     * @access   public
     */
    function newdir()
    {
        global $_CORELANG, $objTemplate;

        Permission::checkAccess(47, 'static');

        $this->webPath = $this->arrWebPaths[0];
        // initialize variables
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'skins_create', 'skins_create.html');
        $this->pageTitle = $_CORELANG['TXT_NEW_DIRECTORY'];
        $test=$this->_objFile->checkConnection();
        $objTemplate->setVariable(array(
            'TXT_NEW_DIRECTORY'       => $_CORELANG['TXT_NEW_DIRECTORY'],
            'TXT_EXISTING_DIR_NAME'   => $_CORELANG['TXT_EXISTING_DIR_NAME'],
            'CREATE_DIR_ACTION'       => '?cmd=skins&amp;act=createDir&amp;path=' . $this->webPath,
            'TXT_DIR_NAME'            => $_CORELANG['TXT_DIR_NAME'],
            'TXT_DB_NAME'             => $_CORELANG['TXT_DB_NAME'],
            'TXT_DESCRIPTION'         => $this->webPath,
            'TXT_SELECT_DIR'          => $_CORELANG['TXT_SELECT_DIR'],
            'TXT_CREATE'              => $_CORELANG['TXT_CREATE'],
            'TXT_FROM_TEMPLATE'       => $_CORELANG['TXT_FROM_TEMPLATE'],
            'THEMES_TEMPLATE_MENU'    => $this->getThemesDropdown(""),
            'TXT_FTP_STATUS'          => $test
        ));
        $objTemplate->setVariable('THEMES_MENU', $this->getDropdownNotInDb());
        $this->checkTable($this->oldTable);
//      $this->newdir();
    }

    /**
     * create skin folder
     * @access   public
     */
    function createdir()
    {
        global $_CORELANG, $objTemplate;

        Permission::checkAccess(47, 'static');

        if ($_POST['dbName'] != "") {
            if (($_POST['dirName']!= "") && ($_POST['existingdirName'] == "")) {
                $dirName = $this->replaceCharacters($_POST['dirName']);
                if ($_POST['fromTheme'] == "" && $_POST['fromDB'] == "") {
                    $this->dirLog=$this->_objFile->mkDir($this->path, $this->webPath, $dirName);
                    if ($this->dirLog != "error") {
                        $this->_objFile->setChmod($this->path, $this->webPath, $dirName);
                        $this->insertIntoDb($_POST['dbName'], $this->dirLog, $_POST['fromDB']);
                        $this->_createDefaultFiles($this->dirLog) ? $this->overview() : $this->newdir();
                    }
                } elseif ($_POST['fromTheme'] != "" && $_POST['fromDB'] == "") {
                    $this->dirLog=$this->_objFile->copyDir($this->path, $this->webPath, $_POST['fromTheme'], $this->path, $this->webPath, $dirName);
                    if ($this->dirLog != "error") {
                        $this->_replaceThemeName($this->replaceCharacters($_POST['fromTheme']), $dirName, $this->path.$dirName);
                        $this->insertIntoDb($_POST['dbName'], $this->dirLog, $_POST['fromDB']);
                    }
                    $this->strOkMessage  = $_POST['dbName']." ". $_CORELANG['TXT_STATUS_SUCCESSFULLY_CREATE'];

                    # people, why are you doing such ugly hacks?
                    #$_POST['themes'] = $_POST['dbName'];
                    $_POST['themes'] = $this->dirLog;
                    $this->overview();
                } elseif ($_POST['fromTheme'] == "" && $_POST['fromDB'] != "") {
                    $this->dirLog=$this->_objFile->mkDir($this->path, $this->webPath, $dirName);
                    if ($this->dirLog != "error") {
                        $this->insertIntoDb($_POST['dbName'], $this->dirLog, $_POST['fromDB']);
                        $this->createFilesFromDB($this->dirLog, intval($_POST['fromDB']));
                    }
                    $this->newdir();
                }
            } elseif (($_POST['dirName'] == "") && ($_POST['existingdirName'] != "")) {
                $this->insertIntoDb($_POST['dbName'], $_POST['existingdirName']);
                $this->setChmodDir($_POST['existingdirName']);
                $this->strOkMessage  = $_POST['existingdirName']." ". $_CORELANG['TXT_STATUS_SUCCESSFULLY_CREATE'];
                $_POST['themes'] = $_POST['existingdirName'];
                $this->overview();
            } else {
                $this->strErrMessage = $_CORELANG['TXT_STATUS_CHECK_INPUTS'];
                $this->newdir();
            }
        } else {
            $this->strErrMessage = $_CORELANG['TXT_STATUS_CHECK_INPUTS'];
            $this->newdir();
        }
    }

    /**
     * Replaces remainders of the original theme in a freshly copied theme.
     * (links to stylesheets, js, images etc.)
     * CAUTION: as this is intended for subroutine-use, no validation or
     * escaping of given parameters is done.
     * @param string $org original theme name
     * @param string $copy copied theme name
     * @param string $copyPath full path to copied theme's directory
     * @return string "error" on error, else empty string
     * @see skins::createDir()
     */
    function _replaceThemeName($org, $copy, $path)
    {
        //extensions of files that could contain links still pointing to the old template
        $regexValidExtensions = '\.css|\.htm|\.html';

        $dir = opendir($path);
        $file = readdir($dir);
        while($file)
        {
            if($file!='.' && $file != '..')
            {
                $ourFile = $path.'/'.$file;
                if(!is_dir($ourFile))
                {
                    //has the file one of our extensions defined above?
                    if(preg_match('/['.$regexValidExtensions.']$/',$ourFile))
                    {
                        //replace name of old template with new template's name
                        $fileContents = file_get_contents($ourFile);
                        $fileContents = str_replace($org,$copy,$fileContents);
                        file_put_contents($ourFile, $fileContents);
                    }
                }
                else //directory, call this function again to process it
                {
                    $this->_replaceThemeName($org,$copy,$ourFile);
                }
            }
            $file=readdir($dir);
        }
    }


    /**
     * sets all CHMOD for Files and folder ind this directory
     * @param    string   $dir
     */
    function setChmodDir($dir)
    {
        $this->_objFile->setChmod($this->path, $this->webPath, $dir);
        $openDir=@opendir($this->path.$dir);
        $file = @readdir($openDir);
        while ($file) {
            if ($file!="." && $file!="..") {
                if (!is_dir($this->path.$dir.DIRECTORY_SEPARATOR.$file)) {
                    $this->_objFile->setChmod($this->path, $this->webPath, $dir.DIRECTORY_SEPARATOR.$file);
                } else {
                    $this->setChmodDir($dir.DIRECTORY_SEPARATOR.$file);
                }
            }
            $file = @readdir($openDir);
        }
        closedir($openDir);
    }

    /**
     * Gets the dropdown menu of filesystem dirs which are not in the DB
     * @access   public
     * @global   ADONewConnection
     * @return   string   $nadm
     */
    function getDropdownNotInDb()
    {
        global $objDatabase;
        $activatedThemes = array();
        $objResult = $objDatabase->Execute("SELECT id,foldername FROM ".DBPREFIX."skins ORDER BY id");
        $i=0;
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $activatedThemes[$i] = $objResult->fields['foldername'];
                $i++;
                $objResult->MoveNext();
            }
        }
        $dir = ($this->path);
        $dh=opendir($dir);
        $file = readdir($dh);
        $nadm = '';
        while ($file) {
            if ($file!="." && $file!=".." && $file != "zip") {
                if (!in_array($file, $activatedThemes)) {
                    $selected="";
                    $nadm .="<option value='".$file."' $selected>".$file."</option>\n";
                }
            }
            $file = readdir($dh);
        }
        closedir($dh);
        return $nadm;
    }

    /**
     * update skin file
     * @access   public
     */
    function update()
    {
        Permission::checkAccess(47, 'static');

        $themes = $_POST['themes'];
        $themesPage = $_POST['themesPage'];
        $pageContent = contrexx_stripslashes($_POST['content']);
        // Change the replacement variables from [[TITLE]] into {TITLE}
        $pageContent = preg_replace('/\[\[([A-Z0-9_]*?)\]\]/', '{\\1}' ,$pageContent);
        if (!$fp = @fopen($this->path.$themes.DIRECTORY_SEPARATOR.$themesPage ,"w")) {
            $this->setChmodDir($themes);
        }
        $fp = fopen ($this->path.$themes.DIRECTORY_SEPARATOR.$themesPage ,"w");
        fwrite($fp, $pageContent);
        fclose($fp);
    }

    /**
     * insert Skin into DB
     * @global   ADONewConnection
     * @param    string   $path
     * @param    string   $folder
     * @param    string   $themesname
     * @access   public
     */
    function insertIntoDb($themesName, $themesFolder, $oldId="")
    {
        global  $objDatabase;
        if (($themesName != "") && ($themesFolder != "")) {
            $objDatabase->Execute("INSERT INTO ".DBPREFIX."skins (themesname, foldername, expert) VALUES ('".addslashes(strip_tags($themesName))."','".addslashes(strip_tags($themesFolder))."', '1')");
            $newId = $objDatabase->Insert_ID();
            $objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation
                                      SET themes_id='".intval($newId)."'
                                    WHERE themes_id='".intval($oldId)."'
                                      AND themes_id!='0';");
        }
    }

    /**
     * add new skin file
     * @access   public
     */
    function newfile()
    {
        global $_CORELANG;

        Permission::checkAccess(47, 'static');

        $themes = isset($_POST['themes']) ? $_POST['themes'] : '';
        $themesFile = isset($_POST['themesNewFileName']) ? $this->replaceCharacters($_POST['themesNewFileName']) : '';
        if (($themesFile!="") AND ($themes!="") && (FWValidator::is_file_ending_harmless($themesFile))) {
            $fp = fopen ($this->path.$themes.DIRECTORY_SEPARATOR.$themesFile ,"w");
            fwrite($fp,"");
            fclose($fp);
            $this->strOkMessage = $themesFile." ".$_CORELANG['TXT_STATUS_SUCCESSFULLY_CREATE'];
        }
    }

    /**
     * del skin file
     * @access   public
     */
    function delfile()
    {
        global $_CORELANG;

        Permission::checkAccess(47, 'static');

        $themesFile = isset($_POST['themesDelFileName']) ? $_POST['themesDelFileName'] : '';
        $themes = isset($_POST['themes']) ? $_POST['themes'] : '';
        //path traversal security check
        if (strpos(realpath($this->path.'/'.$themes.'/'.$themesFile), realpath($this->path.'/'.$themes)) !== 0) {
            $themesFile = '';
        }
        $themes = str_replace(array('..','/'), '', $themes);

        if (($themesFile!="") AND ($themes!="")){
            if ($_POST['themesPage'] != $themesFile) {
                $this->dirLog = $this->_objFile->delFile($this->path, $this->webPath, $themes.DIRECTORY_SEPARATOR.$themesFile);
                if ($this->dirLog != "error") {
                    $this->strOkMessage = $themesFile.": ".$_CORELANG['TXT_STATUS_SUCCESSFULLY_DELETE'];
                }
             } else {
                $this->strErrMessage = $_CORELANG['TXT_STATUS_FILE_CURRENTLY_OPEN'];
             }
        }
    }

    /**
     * del skin folder and all files in it
     * @param    bool    $nockeck
     * @access   public
     * @global   ADONewConnection
     * @global   array
     */
    function deldir($nocheck = false)
    {
        global $objDatabase, $_CORELANG;

        Permission::checkAccess(47, 'static');

        $themes = isset($_POST['themesDelName']) ? $_POST['themesDelName'] : '';
        if ($themes == '' && !empty($_GET['delete'])){
            $themes = addslashes($_GET['delete']);
        }
// TODO: Unused
//        $theme = str_replace(array('..','/'), '', $themes);
        if ($themes!="") {
            $_POST['themes'] = (!empty($_POST['themes'])) ? contrexx_addslashes($_POST['themes']) : '';
            if ($_POST['themes'] != $themes || $nocheck) {
                $dir = ($this->path.$themes);
                if (file_exists($dir)) {
                    //delete whole folder with subfolders
                    $this->dirLog = $this->_objFile->delDir($this->path, $this->webPath, $themes);
                    if ($this->dirLog != "error") {
                        $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."skins WHERE foldername = '".$themes."'");
                        if ($objResult !== false) {
                            while (!$objResult->EOF) {
                                $themesId = $objResult->fields['id'];
                                $objResult->MoveNext();
                            }
                        }
                        $objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET themes_id ='0' WHERE themes_id = '".$themesId."'");
                        $objDatabase->Execute("DELETE FROM ".DBPREFIX."skins WHERE foldername = '".$themes."'");
                        $this->strOkMessage = $themes.": ".$_CORELANG['TXT_STATUS_SUCCESSFULLY_DELETE'];
                    } else {
                        $this->strErrMessage = $themes.": ".$_CORELANG['TXT_STATUS_CANNOT_DELETE'];
                    }
                } else {
                    $objResult = $objDatabase->Execute("SELECT id FROM ".DBPREFIX."skins WHERE foldername = '".$themes."'");
                    if ($objResult !== false) {
                        while (!$objResult->EOF) {
                            $themesId = $objResult->fields['id'];
                            $objResult->MoveNext();
                        }
                    }
                    $objDatabase->Execute("UPDATE ".DBPREFIX."content_navigation SET themes_id ='0' WHERE themes_id = '".$themesId."'");
                    $objDatabase->Execute("DELETE FROM ".DBPREFIX."skins WHERE foldername = '".$themes."'");
                    $this->strOkMessage = $themes.": ".$_CORELANG['TXT_STATUS_SUCCESSFULLY_DELETE'];
                }
             } else {
                $this->strErrMessage = $_CORELANG['TXT_STATUS_FILE_CURRENTLY_OPEN'];
             }
        }
    }

    /**
     * Gets the dropdown menus content
     * @access public
     */
    function getDropdownContent()
    {
        global $objTemplate;
        $themes = isset($_POST['themes']) ? contrexx_addslashes($_POST['themes']) : '';
        $themesPage = isset($_POST['themesPage']) ? contrexx_addslashes($_POST['themesPage']) : '';
        $objTemplate->setVariable(array(
            'THEMES_PAGES_MENU'     => $this->getFilesDropdown($themes, $themesPage),
            'THEMES_MENU'           => $this->getThemesDropdown($themes),
            'THEMES_PAGE_VALUE'     => $this->getFilesContent($themes, $themesPage),
            'THEMES_PAGE_DEL_VALUE' => $this->getFilesDropdownDel($themes),
            'THEMES_MENU_DEL'       => $this->_getThemesDropdownDelete(),
        ));
    }

    /**
     * Gets the activated themes dropdown menu
     * @access   public
     * @param    string   $themes (optional)
     * @return   string   $atdm
     */
    function _getDropdownActivated($themesId)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("SELECT id,themesname FROM ".DBPREFIX."skins ORDER BY id");
        $atdm = '';
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $selected="";
                if ($objResult->fields['id'] == intval($themesId))  $selected = "selected";
                $atdm .="<option value='".$objResult->fields['id']."' $selected>".$objResult->fields['themesname']."</option>\n";
                $objResult->MoveNext();
            }
        }
        return $atdm;
    }

    /**
     * Gets the themes dropdown menu
     * @access   public
     * @global   ADONewConnection
     * @global   array
     * @param    string   $themes (optional)
     * @return   string   $tdm
     */
    function getThemesDropdown($themes = NULL)
    {
        global $objDatabase, $_CORELANG;
        $themelist=array();
        if (!isset($themes)) {
            $themes = $this->selectTheme();
        }
        $defaultTheme = $this->selectTheme();
        $defaultPrintTheme = $this->getDefaultPrintTheme();
        $defaultPDFTheme = $this->getDefaultPDFTheme();
        $objResult = $objDatabase->Execute("SELECT id,themesname,foldername FROM ".DBPREFIX."skins ORDER BY themesname,id");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if ($objResult->fields['foldername'] == $defaultTheme){
                    array_unshift($themelist, $objResult->fields);
                } elseif ($objResult->fields['foldername'] == $defaultPrintTheme){
                    array_unshift($themelist, $objResult->fields);
                } elseif ($objResult->fields['foldername'] == $defaultPDFTheme){
                    array_unshift($themelist, $objResult->fields);
                } else {
                    $themelist[] = $objResult->fields;
                }
                $objResult->MoveNext();
            }
        }

        $tdm = '';
        foreach ($themelist as $item) {
            $selected = "";
            $default = "";
            $printstyle = "";
            $pdfstyle = "";
            if ($item['foldername'] == $defaultTheme){
                $default = "(".$_CORELANG['TXT_DEFAULT'].")";
            }
            if ($item['foldername'] == $defaultPrintTheme){
                $printstyle = "(".$_CORELANG['TXT_THEME_PRINT'].")";
            }
            if ($item['foldername'] == $defaultPDFTheme){
                $pdfstyle = "(".$_CORELANG['TXT_THEME_PDF'].")";
            }
            if ($themes == $item['foldername']) $selected = "selected";
            $tdm .='<option id="'.$item['id']."\" value='".$item['foldername']."' $selected>".contrexx_stripslashes($item['themesname'])." ".$default.$printstyle.$pdfstyle."</option>\n";
        }
        return $tdm;
    }


    /**
     * Gets the themes dropdown menu
     * @access   private
     * @global   ADONewConnection
     * @param    string   $themes (optional)
     * @return   string   $tdm
     */
    function _getThemesDropdownDelete()
    {
        global $objDatabase;

        $activatedThemes = array();
        $objResult = $objDatabase->Execute("SELECT id,themesid,print_themes_id,pdf_themes_id,is_default FROM ".DBPREFIX."languages ORDER BY id");
        $i=0;
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $activatedThemes[] = $objResult->fields['themesid'];
                $activatedThemes[] = $objResult->fields['print_themes_id'];
                $activatedThemes[] = $objResult->fields['pdf_themes_id'];
                $i++;
                $objResult->MoveNext();
            }
        }
        $objResult = $objDatabase->Execute("SELECT id,themesname,foldername FROM ".DBPREFIX."skins ORDER BY id");
        $tdm = '';
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                if (!in_array($objResult->fields['id'], $activatedThemes)) {
                    $tdm .="<option value='".$objResult->fields['foldername']."'>".$objResult->fields['themesname']."</option>\n";
                }
                $objResult->MoveNext();
            }
        }
        return $tdm;
    }

    /**
     * Get subdirectory contents
     * @param string $strDir
     * @param bool $boolRecursive
     * @param bool $boolIncludeDirs
     * @return array $this->subDirs
     */
    function _getDirListing($strDir, $arrAllowedFileExtensions, $level = 0, $boolRecursive = true, $boolIncludeDirs = true) {
        $dirs = array();
        $hDir = opendir($strDir);
        if ($hDir) {
            $level++;
            $strFile = readdir($hDir);
            while ($strFile !== false) {
                // don't need ., .., .svn
                if (in_array($strFile, array('.', '..', '.svn'))) {
                    $strFile = readdir($hDir);
                    continue;
                }

                if (is_dir($strDir. "/" . $strFile)) {
                    if ($boolRecursive)
                        $dirs = array_merge($dirs, $this->_getDirListing($strDir .'/'. $strFile, $arrAllowedFileExtensions, $level, $boolRecursive, $boolIncludeDirs));
                    if ($boolIncludeDirs){
                        $strPath = $strDir .'/'. $strFile;
                        $strFile = str_replace($this->_parentPath.'/', '', $strDir .'/'. $strFile);
                        $dirs[$strPath] = array(
                            'rel'  => preg_replace('/\/\//si', '/', $strFile),
                            'file'  => basename($strFile),
                            'level' => $level,
                        );
                    }
                }
                elseif (!in_array(substr($strFile, strrchr($strFile, '.')), $arrAllowedFileExtensions)) {
                    $strPath = $strDir .'/'. $strFile;
                    $strFile = str_replace($this->_parentPath.'/', '', $strDir .'/'. $strFile);
                    $dirs[$strPath] = array(
                        'rel'  => preg_replace('/\/\//si', '/', $strFile),
                        'file'  => basename($strFile),
                        'level' => $level,
                    );
                }
                $strFile = readdir($hDir);
            }
            closedir($hDir);
        }
        return $dirs;
    }


    /**
     * Gets the themes pages dropdown menu
     * @access   public
     * @param    string   $themes
     * @param    string   $themesPage (optional)
     * @return   string   $fdm
     */
    function getFilesDropdown($themes="", $themesPage="")
    {
        global $_CORELANG;

        if (!isset($themes)) {
            $themes = $this->selectTheme();
        }
        if (!isset($themesPage)) {
            $themesPage = "index.html";
        }
        $fdm = '';
        $special = '';
        $default = '';
        $defaultFiles = array();
        $selected = "";

        if ($themes != "") {
            $file = $this->path.$themes;
            if (file_exists($file)) {
                $this->_parentPath = $file;
                $skin_folder_page = opendir($file);
                $strPage = readdir($skin_folder_page);
                while (false !== $strPage) {
                    if (!in_array($strPage, array('.', '..', '.svn')) && is_dir($file.'/'.$strPage)){
                        $this->subDirs = array_merge($this->subDirs, $this->_getDirListing($file.'/'.$strPage, $this->fileextensions));
                    }
                    $extension = split("[.]",$strPage);
                    $x = count($extension)-1;
                    if (in_array($extension[$x], $this->fileextensions)) {
                        if (in_array($strPage, $this->filenames)) {
                            if ($strPage != "." && $strPage != "..") {
                                $defaultFiles[] = $strPage;
                            }
                        } else {
                            if ($strPage != "." && $strPage != "..") {
                                $selected="";
                                if ($themesPage==$strPage) $selected = 'selected="selected"';
                                $special .="<option value='".$strPage."' $selected>".$strPage."</option>\n";
                            }
                        }
                    }
                    $strPage = readdir($skin_folder_page);
                }
                closedir($skin_folder_page);
                ksort($this->subDirs, SORT_STRING);
                $subdirContent = '';
                foreach ($this->subDirs as $absolutePath => $arrFile) {
                    $disabled = $selected = '';
                    $strName = str_repeat('&hellip;', $arrFile['level']).$arrFile['file'];
                    if ($themesPage == $arrFile['rel']){ $selected = 'selected="selected"'; }
                    if (is_dir($absolutePath)){ $disabled = 'disabled="disabled"'; $strName .= '/'; }
                    $subdirContent .= "<option value='".($disabled != '' ? '' : $arrFile['rel'])."' $disabled $selected>".$strName."</option>\n";
                }
                //sort files
                sort($defaultFiles);
                foreach ($defaultFiles as $strPage){
                    $selected="";
                    if ($themesPage==$strPage) $selected = "selected";
                    $default .="<option value='".$strPage."' $selected>".$strPage."</option>\n";
                }
                //create dropdown
                $seperator = "<option value=''>----------------------------------------</option>\n";
                $fdm = $default.$seperator.$special.$seperator.$subdirContent;
            } else {
                $this->strErrMessage = $_CORELANG['TXT_STATUS_CANNOT_OPEN'];
            }
        }
        else {
            $fdm .="<option value='1' $selected>".$_CORELANG['TXT_CHOOSE_DESIGN']."</option>\n";
        }
        return $fdm;
    }

    /**
     * Gets the themes pages dropdown menu del
     * @access   public
     * @return   string   $fdmd
     */
    function getFilesDropdownDel($themes="")
    {
        global $_CORELANG;
        if (!isset($themes)) {
            $themes = $this->selectTheme();
        }
        $fdmd = "";

        if ($themes != "") {
            $file = $this->path.$themes;
            if (file_exists($file)) {
                $themesPage = opendir ($file);
                $page = readdir($themesPage);
                while ($page) {
                    $extension = split("[.]",$page);
                    $x = count($extension)-1;
                    if (in_array($extension[$x], $this->fileextensions)) {
                        if (($page != ".") && ($page != "..") && (!in_array($page, $this->filenames))) {
                            $fdmd .="<option value='".$page."'>".$page."</option>\n";
                        }
                    }
                    $page = readdir($themesPage);
                }
                closedir($themesPage);
            }
        } else {
            $fdmd .="<option value='1'>".$_CORELANG['TXT_CHOOSE_DESIGN']."</option>\n";
        }
        return $fdmd;
    }

    /**
     * Gets the themes pages file content
     * @access   public
     * @param    string   $themes
     * @param    string   $themesPage
     * @return   string   $fileContent
     */
    function getFilesContent($themes="", $themesPage="")
    {
        global $objDatabase, $objTemplate;
        if (!isset($themes)) {
            $themes = $this->selectTheme();
        }
        if (!isset($themesPage)) {
            $themesPage = "index.html";
        }
        if ($themes != "" && $themesPage != ""){
            $file = $this->path.$themes.DIRECTORY_SEPARATOR.$themesPage;
            if (file_exists($file)) {
                $contenthtml = file_get_contents($file);
                $contenthtml = preg_replace('/\{([A-Z0-9_]*?)\}/', '[[\\1]]', $contenthtml);
                $contenthtml = htmlspecialchars($contenthtml);
// TODO: Pointless!
//                $objResult = $objDatabase->Execute("SELECT id,expert FROM ".DBPREFIX."skins WHERE foldername = '".$themes."'");
//                if ($objResult !== false) {
//                    while (!$objResult->EOF) {
//                        $expert = $objResult->fields['expert'];
//                        $objResult->MoveNext();
//                    }
//                }
                $objTemplate->setVariable(array(
                    'THEMES_SELECTED_THEME'    => $themes,
                    'THEMES_SELECTED_PAGENAME' => $themesPage,
                    'THEMES_FULL_PATH'         => $this->webPath.$themes.DIRECTORY_SEPARATOR.$themesPage,
                    'CONTENT_HTML'             => $contenthtml,
                ));
                //return $fileContent;
            }
        }
    }


    /**
     * replaces some characters
     * @access   public
     * @param    string   $themes
     */
    function replaceCharacters($string)
    {
        // replace $change with ''
        $change = array('+', '¦', '"', '@', '*', '#', '°', '%', '§', '&', '¬', '/', '|', '(', '¢', ')', '=', '?', '\'', '´', '`', '^', '~', '!', '¨', '[', ']', '{', '}', '£', '$', '-', '<', '>', '\\', ';', ',', ':');
        // replace $signs1 with $signs
        $signs1 = array(' ', 'ä', 'ö', 'ü', 'ç');
        $signs2 = array('_', 'ae', 'oe', 'ue', 'c');
        $string = strtolower($string);
        foreach($change as $str) {
            $string = str_replace($str, '', $string);
        }
        for($x = 0; $x < count($signs1); $x++) {
            $string = str_replace($signs1[$x], $signs2[$x], $string);
        }
        $string = str_replace('__', '_', $string);
        if (strlen($string) > 40) {
            $info       = pathinfo($string);
            $stringExt  = $info['extension'];
            $stringName = substr($string, 0, strlen($string) - (strlen($stringExt) + 1));
            $stringName = substr($stringName, 0, 40 - (strlen($stringExt) + 1));
            $string     = $stringName . '.' . $stringExt;
        }
        return $string;
    }

    /**
     * if not isset themes
     * @access   public
     * @return   string   $themes
     */
    function selectTheme()
    {
        global $objDatabase;

        $themeId = 0;
        $objResult = $objDatabase->Execute("
            SELECT id, themesid
              FROM ".DBPREFIX."languages
             WHERE is_default='true'
             ORDER BY id"); // , is_default, print_themes_id
        if ($objResult && !$objResult->EOF) {
            $themeId = $objResult->fields['themesid'];
//            $printThemeId = $objResult->fields['print_themes_id'];
        }
        $objResult = $objDatabase->Execute("
            SELECT id, foldername
              FROM ".DBPREFIX."skins
             WHERE id=$themeId
             ORDER BY id");
        $themes = null;
        if ($objResult && !$objResult->EOF) {
            $themes = $objResult->fields['foldername'];
        }
        return $themes;
    }


    /**
     * return the foldername of the default print_theme
     * @return string $default_print_theme_foldername
     */
    function getDefaultPrintTheme()
    {
        global $objDatabase, $_CORELANG;
        $objResultID = $objDatabase->SelectLimit("SELECT `print_themes_id` FROM ".DBPREFIX."languages WHERE is_default='true'", 1);
        if ($objResultID !== false && $objResultID->RecordCount() > 0) {
            $objResult = $objDatabase->SelectLimit("SELECT `foldername` FROM ".DBPREFIX."skins WHERE id=".$objResultID->fields['print_themes_id']." ORDER BY id", 1);
            if ($objResult !== false && $objResult->RecordCount() > 0) {
                return $objResult->fields['foldername'];
            }
        }
        $this->strErrMessage = $_CORELANG['TXT_NO_DEFAULT_THEME'];
        return false;
    }

    /**
     * return the foldername of the default pdf_theme
     * @return string $default_pdf_theme_foldername
     */
    function getDefaultPDFTheme()
    {
        global $objDatabase, $_CORELANG;
        $objResultID = $objDatabase->SelectLimit("SELECT `pdf_themes_id` FROM ".DBPREFIX."languages WHERE is_default='true'", 1);
        if ($objResultID !== false && $objResultID->RecordCount() > 0) {
            $objResult = $objDatabase->SelectLimit("SELECT `foldername` FROM ".DBPREFIX."skins WHERE id=".$objResultID->fields['pdf_themes_id']." ORDER BY id", 1);
            if ($objResult !== false && $objResult->RecordCount() > 0) {
                return $objResult->fields['foldername'];
            }
        }
        $this->strErrMessage = $_CORELANG['TXT_NO_DEFAULT_THEME'];
        return false;
    }


    /**
     * selectDefaultTheme
     * @access   public
     * @return   string   $themes
     */
    function selectDefaultTheme()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("SELECT id,themesid,is_default FROM ".DBPREFIX."languages WHERE is_default='true' ORDER BY id");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $themeId = $objResult->fields['themesid'];
                $objResult->MoveNext();
            }
        }
        return $themeId;
    }

    /**
     * create default themepages
     * @access   public
     * @param    string   $themes
     */
    function _createDefaultFiles($themes, $filesOnly = false)
    {
        global $_CORELANG, $_FTPCONFIG;
        $status='';
        foreach ($this->directories as $dir) {
            $this->_objFile->mkDir($this->path.$themes.DIRECTORY_SEPARATOR,ASCMS_THEMES_WEB_PATH.DIRECTORY_SEPARATOR.$themes.DIRECTORY_SEPARATOR,$dir);
        }
        //copy "not available" preview.gif as default preview image
        $status = $this->_objFile->copyFile(ASCMS_ADMIN_TEMPLATE_PATH.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR,'preview.gif', $this->path.$themes.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR,'preview.gif');
        for($x = 0; $x < count($this->filenames); $x++) {
            if (!file_exists($this->path.$themes.DIRECTORY_SEPARATOR.$this->filenames[$x])){
                $fp = fopen ($this->path.$themes.DIRECTORY_SEPARATOR.$this->filenames[$x] ,"w");
                @fwrite($fp,"");
                @fclose($fp);
                @chown($this->path.$themes.DIRECTORY_SEPARATOR.$this->filenames[$x], $_FTPCONFIG['username']);
                @chmod($this->path.$themes.DIRECTORY_SEPARATOR.$this->filenames[$x], 0777);
            }
        }
        if ($filesOnly){
            return true;
        }
        if ($status == 'error'){
            $this->strErrMessage = __FUNCTION__.'(): '.$_CORELANG['TXT_ERRORS_WHILE_READING_THE_FILE'];
            return false;
        } else {
            $this->strOkMessage  = $themes ."  ".$_CORELANG['TXT_STATUS_SUCCESSFULLY_CREATE'];
            $_POST['themes'] = $themes;
            return true;
        }
    }

    /**
     * check iftable exists
     * @access   public
     * @param    string   $table
     */
    function checkTable($table)
    {
        global $objDatabase;
        $arrTables = array();
        // get tables in database
        $arrTables = $objDatabase->MetaTables('TABLES');
        if ($arrTables !== false) {
            if (in_array($table, $arrTables)) {
                $this->tableExists = "tblexists";
                $this->getDbDropdown($table);
                $this->dropTable($this->oldTable);
            }
        }
    }

    /**
     * create db themes dropdownmenu
     * @access   public
     */
    function getDbDropdown()
    {
        global $objDatabase, $_CORELANG, $objTemplate;
        if ($this->tableExists == "tblexists") {
            $tdm = "<select name='fromDB' onchange='existingdirNameValue2()' size='1' style='WIDTH: 150px'>";
            $tdm .="<option value=''>--- Aus Datenbank ----------</option>";
            $objResult = $objDatabase->query("SELECT id,themesname FROM ".$this->oldTable." ORDER BY id");
            $defaultThemeId = $this->selectDefaultTheme();
            $default = '';
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    if ($objResult->fields['id'] == $defaultThemeId){
                        $default = "(".$_CORELANG['TXT_DEFAULT'].")";
                    }
                    $tdm .="<option value='".$objResult->fields['id']."'>".$objResult->fields['themesname']." ".$default."</option>\n";
                    $default='';
                    $objResult->MoveNext();
                }
            }
            $tdm .="</select>";
            $objTemplate->setVariable('TXT_FROM_DB',$tdm);
        }
    }

    /**
     * create files from db
     * @param string $themes
     * @param string $fromDB
     */
    function createFilesFromDB($themes, $fromDB)
    {
        global $objDatabase, $_CORELANG;

        $themePages = array();

        $objResult = $objDatabase->Execute("SELECT * FROM ".$this->oldTable." WHERE id='".$fromDB."'");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $themePages['index.html'] = stripslashes($objResult->fields['indexpage']);
                $themePages['content.html'] =  stripslashes($objResult->fields['content']);
                $themePages['home.html'] =  stripslashes($objResult->fields['home']);
                $themePages['navbar.html'] =  stripslashes($objResult->fields['navbar']);
                $themePages['subnavbar.html'] =  stripslashes($objResult->fields['subnavbar']);
                $themePages['sidebar.html'] =  stripslashes($objResult->fields['sidebar']);
                $themePages['shopnavbar.html'] =  stripslashes($objResult->fields['shopnavbar']);
                $themePages['headlines.html'] =  stripslashes($objResult->fields['headlines']);
                $themePages['javascript.js'] =  stripslashes($objResult->fields['javascript']);
                $themePages['style.css'] =  stripslashes($objResult->fields['style']);
                $objResult->MoveNext();
            }
        }

        for($x = 0; $x < count($this->filenames); $x++) {
            $fp = fopen ($this->path.$themes.DIRECTORY_SEPARATOR.$this->filenames[$x] ,"w");
            fwrite($fp,"");
            fclose($fp);

            $filename = $this->path.$themes.DIRECTORY_SEPARATOR.$this->filenames[$x];

            //check, if file exists and is writable
            if (is_writable($filename)) {
                //open file
                if (!$handle = fopen($filename, "a")) {
                     $this->strErrMessage = $_CORELANG['TXT_STATUS_CANNOT_OPEN'];
                }
                //write file
                if (!fwrite($handle, $themePages[$this->filenames[$x]])) {
                    $this->strErrMessage =  $_CORELANG['TXT_STATUS_CANNOT_WRITE'];
                }
                fclose($handle);
                $this->strOkMessage = $_CORELANG['TXT_STATUS_SUCCESSFULLY_CREATE'];
                $this->overview();
            } else {
                $this->strErrMessage = $_CORELANG['TXT_STATUS_CANNOT_WRITE'];
            }
        }
    }


    /**
     * get all theme rows from the skins table
     * @return array
     */
    function _getThemes()
    {
        global $objDatabase, $_CORELANG;

        $query = "SELECT id, themesname, foldername from ".DBPREFIX."skins ORDER BY themesname";
        $objRS = $objDatabase->Execute($query);
        if ($objRS){
            $themes = array();
            $defaultTheme = $this->selectTheme();
            $defaultPrintTheme = $this->getDefaultPrintTheme();
            while(!$objRS->EOF){
                $languagesWithThisTheme = '';
                $query = '  SELECT `name`
                            FROM '.DBPREFIX.'languages
                            WHERE 1=1
                            AND `frontend` = 1
                            AND (`themesid` = '.$objRS->fields['id'].'
                            OR `print_themes_id` = '.$objRS->fields['id'].')';
                $objRSLang = $objDatabase->Execute($query);
                if ($objRSLang) {
                    while(!$objRSLang->EOF){
                        $languagesWithThisTheme .= $objRSLang->fields['name'].', ';
                        $objRSLang->MoveNext();
                    }
                }
                $languagesWithThisTheme = substr($languagesWithThisTheme, 0, -2);
                if ($objRS->fields['foldername'] == $defaultTheme){
                    $objRS->fields['extra'] = ' ('.$_CORELANG['TXT_DEFAULT']. (!empty($languagesWithThisTheme) ? ') - '.$languagesWithThisTheme : ')');
                    array_unshift($themes, $objRS->fields);
                } elseif ($objRS->fields['foldername'] == $defaultPrintTheme){
                    $objRS->fields['extra'] = ' ('.$_CORELANG['TXT_THEME_PRINT']. (!empty($languagesWithThisTheme) ? ') - '.$languagesWithThisTheme : ')');
                    array_unshift($themes, $objRS->fields);
                } else {
                    $objRS->fields['extra'] = !empty($languagesWithThisTheme) ? ' - '.$languagesWithThisTheme : '';
                    $themes[] = $objRS->fields;
                }
                $objRS->MoveNext();
            }
            //switch first two elements if print is first (we want default theme to be the first in the list)
            if ($themes[0]['foldername'] == 'print'){
                $tmp        = $themes[1];
                $themes[1]  = $themes[0];
                $themes[0]  = $tmp;
            }
            return $themes;
        } else {
            $this->strErrMessage = "DB error.";
            return false;
        }
    }


    /**
     * if now rows in table -> drop $this->oldTable
     * @access   public
     */
    function dropTable()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute("SELECT id FROM ".$this->oldTable." ORDER BY id");
        if ($objResult) {
            if ($objResult->RecordCount() == 0) {
                $objDatabase->Execute("DROP TABLE ".$this->oldTable);
            }
        }
    }


    /**
     * get XML info of specified modulefolder
     * @param string $themes
     * @access private
     */
    function _getXML($themes)
    {
        $xmlFilePath = ASCMS_THEMES_PATH.DIRECTORY_SEPARATOR.$themes.DIRECTORY_SEPARATOR.'info.xml';
        $xml_parser = xml_parser_create($this->_xmlParserCharacterEncoding);
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser,"_xmlStartTag","_xmlEndTag");
        xml_set_character_data_handler($xml_parser, "_xmlCharacterDataTag");
        $documentContent = @file_get_contents($xmlFilePath);
        xml_parse($xml_parser, $documentContent);
        xml_parser_free($xml_parser);
    }

    /**
     * XML parser start tag
     * @access private
     * @param resource $parser
     * @param string $name
     * @param array $attrs
     */
    function _xmlStartTag($parser, $name, $attrs)
    {
        if (isset($this->_currentXmlElement)) {
            if (!isset($this->_currentXmlElement[$name])) {
                $this->_currentXmlElement[$name] = array();
                $this->_arrParentXmlElement[$name] = &$this->_currentXmlElement;
                $this->_currentXmlElement = &$this->_currentXmlElement[$name];
            } else {
                if (!isset($this->_currentXmlElement[$name][0])) {
                    $arrTmp = $this->_currentXmlElement[$name];
                    unset($this->_currentXmlElement[$name]);// = array();
                    $this->_currentXmlElement[$name][0] = $arrTmp;
                }

                array_push($this->_currentXmlElement[$name], array());
                $this->_arrParentXmlElement[$name] = &$this->_currentXmlElement;
                $this->_currentXmlElement = &$this->_currentXmlElement[$name][count($this->_currentXmlElement[$name])-1];
            }

        } else {
            $this->_xmlDocument[$name] = array();
            $this->_currentXmlElement = &$this->_xmlDocument[$name];
        }

        if (count($attrs)>0) {
            foreach ($attrs as $key => $value) {
                $this->_currentXmlElement['attrs'][$key] = $value;
            }
        }
    }

    /**
     * XML parser character data tag
     * @access private
     * @param resource $parser
     * @param string $cData
     */
    function _xmlCharacterDataTag($parser, $cData)
    {
        $cData = trim($cData);
        if (!empty($cData)) {
            if (!isset($this->_currentXmlElement['cdata'])) {
                $this->_currentXmlElement['cdata'] = $cData;
            } else {
                $this->_currentXmlElement['cdata'] .= $cData;
            }
        }
    }

    /**
     * XML parser end tag
     * @access private
     * @param resource $parser
     * @param string $name
     */
    function _xmlEndTag($parser, $name)
    {
        $this->_currentXmlElement = &$this->_arrParentXmlElement[$name];
        unset($this->_arrParentXmlElement[$name]);
    }

}

?>
