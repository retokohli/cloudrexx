<?php

/**
 * Media Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0
 * @package     contrexx
 * @subpackage  core_module_media
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_MODULE_PATH.'/media/mediaLib.class.php';

/**
 * Media Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0
 * @access        public
 * @package     contrexx
 * @subpackage  core_module_media
 */
class MediaManager extends MediaLibrary
{
    public $_objTpl;                       // var for the template object
    public $pageTitle;                     // var for the title of the active page
    public $statusMessage;                 // var for the status message

    public $iconPath;                      // icon path constant
    public $iconWebPath;                   // icon web path constant
    public $arrPaths;                      // array paths
    public $arrWebPaths;                   // array web paths

    public $getCmd;                        // $_GET['cmd']
    public $getAct;                        // $_GET['act']
    public $getPath;                       // $_GET['path']
    public $getFile;                       // $_GET['file']

    public $path;                          // current path
    public $webPath;                       // current web path
    public $docRoot;                       // document root
    public $archive;


    /**
     * PHP5 constructor
     * @param  string  $template
     * @param  array   $_ARRAYLANG
     * @access public
     */
    function __construct($pageContent, $archive)
    {
        $this->archive = (intval(substr($archive, -1, 1)) == 0) ? 'media1' : $archive;

        // directory variables
        $this->iconPath     = ASCMS_MODULE_IMAGE_PATH.'/media/';
        $this->iconWebPath  = ASCMS_MODULE_IMAGE_WEB_PATH.'/media/';
        $this->arrPaths = array(
            ASCMS_MEDIA1_PATH.'/',
            ASCMS_MEDIA2_PATH.'/',
            ASCMS_MEDIA3_PATH.'/',
            ASCMS_MEDIA4_PATH.'/',
        );
        $this->arrWebPaths = array(
            'media1' => ASCMS_MEDIA1_WEB_PATH.'/',
            'media2' => ASCMS_MEDIA2_WEB_PATH.'/',
            'media3' => ASCMS_MEDIA3_WEB_PATH.'/',
            'media4' => ASCMS_MEDIA4_WEB_PATH.'/',
        );
        $this->docRoot = ASCMS_PATH;

        // sigma template
        $this->pageContent = $pageContent;
        $this->_objTpl     = new HTML_Template_Sigma('.');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_objTpl->setTemplate($this->pageContent, true, true);

        // get variables
        $this->getAct  = (isset($_GET['act']) and !empty($_GET['act']))   ? trim($_GET['act'])  : '';
        $this->getFile = (isset($_GET['file']) and !empty($_GET['file'])) ? trim($_GET['file']) : '';
        $this->sortBy = !empty($_GET['sort']) ? trim($_GET['sort']) : 'name';
        $this->sortDesc = !empty($_GET['sort_desc']);
    }


    /**
     * checks and cleans the web path
     * @param  string default web path
     * @return string  cleaned web path
     */
    function getWebPath($defaultWebPath)
    {
        if (isset($_GET['path']) AND !empty($_GET['path']) AND !stristr($_GET['path'],'..')) {
            $webPath = trim($_GET['path']);
        } else {
            $webPath = $defaultWebPath;
        }
        if (substr($webPath, 0, strlen($defaultWebPath)) != $defaultWebPath || !file_exists($this->docRoot.$webPath)) {
            $webPath = $defaultWebPath;
        }
        return $webPath;
    }


    /**
     * Gets the requested page
     * @global     array     $_ARRAYLANG,$_CONFIG
     * @return    string    parsed content
     */
    function getMediaPage()
    {
        $this->webPath = $this->getWebPath($this->arrWebPaths[$this->archive]);
        $this->path = ASCMS_PATH.$this->webPath;
        $this->getCmd = !empty($_GET['cmd']) ? '&amp;cmd='.htmlentities($_GET['cmd'], ENT_QUOTES, CONTREXX_CHARSET) : '';

        $this->_overviewMedia();
        return $this->_objTpl->get();
    }


    /**
     * Overview Media Data
     * @global     array     $_ARRAYLANG
     * @return    string    parsed content
     */
    function _overviewMedia()
    {
        global $_ARRAYLANG;

        switch($this->getAct) {
            case 'download':
                $this->_downloadMedia();
                break;
            default:
        }

        // tree navigation
        $tmp = $this->arrWebPaths[$this->archive];
        if (substr($this->webPath, 0, strlen($tmp)) == $tmp) {
            $this->_objTpl->setVariable(array(  // navigation #1
                'MEDIA_TREE_NAV_MAIN'      => "Home /", //$this->arrWebPaths[$x],
                'MEDIA_TREE_NAV_MAIN_HREF' => CONTREXX_SCRIPT_PATH.'?section='.$this->archive.$this->getCmd.'&amp;path=' . rawurlencode($this->arrWebPaths[$this->archive])
            ));

            if (strlen($this->webPath) != strlen($tmp)) {
                $tmpPath = substr($this->webPath, -(strlen($this->webPath) - strlen($tmp)));
                $tmpPath = explode('/', $tmpPath);
                $tmpLink = '';
                foreach ($tmpPath as $path) {
                    if (!empty($path)) {
                        $tmpLink .= $path.'/';
                        $this->_objTpl->setVariable(array(  // navigation #2
                            'MEDIA_TREE_NAV_DIR'      => $path,
                            'MEDIA_TREE_NAV_DIR_HREF' => CONTREXX_SCRIPT_PATH.'?section=' . $this->archive . $this->getCmd . '&amp;path=' . rawurlencode($this->arrWebPaths[$this->archive] . $tmpLink)
                        ));
                        $this->_objTpl->parse('mediaTreeNavigation');
                    }
                }
            }
        }

        // media directory tree
        $i = 0;
        $dirTree = $this->_dirTree($this->path);
        $dirTree = $this->_sortDirTree($dirTree);
        foreach (array_keys($dirTree) as $key) {
            if (is_array($dirTree[$key]['icon'])) {
                for ($x = 0; $x < count($dirTree[$key]['icon']); $x++) {
                    if ($key == 'dir') {
                        $tmpHref= CONTREXX_SCRIPT_PATH.'?section='.$this->archive.$this->getCmd.'&amp;path='.rawurlencode($this->webPath.$dirTree[$key]['name'][$x].'/');
                    } elseif ($key == 'file') {
                        if ($this->_isImage($this->path.$dirTree[$key]['name'][$x])) {
                            $tmpSize = getimagesize($this->path.$dirTree[$key]['name'][$x]);
                            $tmpHref = 'javascript:preview(\''.$this->webPath.$dirTree[$key]['name'][$x].'\','.$tmpSize[0].','.$tmpSize[1].');';
                        } else {
                            $tmpHref = CONTREXX_SCRIPT_PATH.'?section='.$this->archive.'&amp;act=download&amp;path='.rawurlencode($this->webPath).'&amp;file='.rawurlencode($dirTree[$key]['name'][$x]);
                        }
                    }
                    $this->_objTpl->setVariable(array(
                        'MEDIA_FILE_NAME_HREF' => $tmpHref,
                        'MEDIA_DIR_TREE_ROW'   => 'row'.(++$i % 2 + 1),
                        'MEDIA_FILE_ICON'      => $this->iconWebPath.$dirTree[$key]['icon'][$x].'.gif',
                        'MEDIA_FILE_NAME'      => $dirTree[$key]['name'][$x],
                        'MEDIA_FILE_SIZE'      => $this->_formatSize($dirTree[$key]['size'][$x]),
                        'MEDIA_FILE_TYPE'      => $this->_formatType($dirTree[$key]['type'][$x]),
                        'MEDIA_FILE_DATE'      => $this->_formatDate($dirTree[$key]['date'][$x]),
                    ));
                    $this->_objTpl->parse('mediaDirectoryTree');
                }
            }
        }

        // empty dir or php safe mode restriction
        if ($i == 0 && !@opendir($this->rootPath)) {
            $tmpMessage = (!@opendir($this->path)) ? 'PHP Safe Mode Restriction or wrong path' : $_ARRAYLANG['TXT_MEDIA_DIR_EMPTY'];

            $this->_objTpl->setVariable(array(
                'TXT_MEDIA_DIR_EMPTY' => $tmpMessage,
                'MEDIA_SELECT_STATUS' => ' disabled',
            ));
            $this->_objTpl->parse('mediaEmptyDirectory');
        }

        // parse variables
        $tmpHref  = CONTREXX_SCRIPT_PATH.'?section='.$this->archive.$this->getCmd.'&amp;path='.rawurlencode($this->webPath);
        $tmpIcon  = $this->_sortingIcons();
        $this->_objTpl->setVariable(array(  // parse dir content
            'MEDIA_NAME_HREF'     => $tmpHref.'&amp;sort=name&amp;sort_desc='.($this->sortBy == 'name' && !$this->sortDesc),
            'MEDIA_SIZE_HREF'     => $tmpHref.'&amp;sort=size&amp;sort_desc='.($this->sortBy == 'size' && !$this->sortDesc),
            'MEDIA_TYPE_HREF'     => $tmpHref.'&amp;sort=type&amp;sort_desc='.($this->sortBy == 'type' && !$this->sortDesc),
            'MEDIA_DATE_HREF'     => $tmpHref.'&amp;sort=date&amp;sort_desc='.($this->sortBy == 'date' && !$this->sortDesc),
            'MEDIA_PERM_HREF'     => $tmpHref.'&amp;sort=perm&amp;sort_desc='.($this->sortBy == 'perm' && !$this->sortDesc),
            'TXT_MEDIA_FILE_NAME' => $_ARRAYLANG['TXT_MEDIA_FILE_NAME'],
            'TXT_MEDIA_FILE_SIZE' => $_ARRAYLANG['TXT_MEDIA_FILE_SIZE'],
            'TXT_MEDIA_FILE_TYPE' => $_ARRAYLANG['TXT_MEDIA_FILE_TYPE'],
            'TXT_MEDIA_FILE_DATE' => $_ARRAYLANG['TXT_MEDIA_FILE_DATE'],
            'TXT_MEDIA_FILE_PERM' => $_ARRAYLANG['TXT_MEDIA_FILE_PERM'],
            'MEDIA_NAME_ICON'     => $tmpIcon['name'],
            'MEDIA_SIZE_ICON'     => $tmpIcon['size'],
            'MEDIA_TYPE_ICON'     => $tmpIcon['type'],
            'MEDIA_DATE_ICON'     => $tmpIcon['date'],
            'MEDIA_PERM_ICON'     => $tmpIcon['perm'],
            'MEDIA_JAVASCRIPT'    => $this->_getJavaScriptCodePreview(),
        ));
    }

}

?>
