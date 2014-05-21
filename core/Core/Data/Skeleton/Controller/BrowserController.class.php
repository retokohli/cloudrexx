<?php

/**
 * DefaultController
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Controller;

/**
 * DefaultController Description
 *
 * @copyright   Comvation AG
 * @author      Tobias Schmoker <tobias.schmoker@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediabrowser
 */
class BrowserController extends \Cx\Core\Core\Model\Entity\Controller {

    private $_arrDirectories = array();   

    /**
     * DefaultController for the DefaultView
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController
     * @param \Cx\Core\Core\Controller\Cx $cx
     * @param \Cx\Core\Html\Sigma $template
     * @param type $submenu
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx, \Cx\Core\Html\Sigma $template, $submenu = null) {
        parent::__construct($systemComponentController, $cx);
        $this->template = $template;
        $this->cx = $cx;

        $this->showView1();
    }

    public function showView1() {
        $this->_showFileBrowser();
    }
    
    
   /**
     * Show the file browser
     * @access private
     * @global array
     */
    function _showFileBrowser() {
        global $_ARRAYLANG;


        switch($this->_mediaType) {
            case 'webpages':
                $strWebPath = 'Webpages (DB)';
                break;
            default:
                if (array_key_exists($this->_mediaType, $this->mediaTypePaths)) {
                    $strWebPath = $this->mediaTypePaths[$this->_mediaType][1].$this->_path;
                } else {
                    $strWebPath = ASCMS_CONTENT_IMAGE_WEB_PATH.$this->_path;
                }
        }

        $this->template->setVariable(array(
            'CONTREXX_CHARSET'      => CONTREXX_CHARSET,
            'FILEBROWSER_WEB_PATH'  => $strWebPath,
            'TXT_CLOSE'             => $_ARRAYLANG['TXT_CLOSE']
        ));

        $this->_getNavigation();
        /*$this->_setUploadForm();
        $this->_setContent();
        $this->_showStatus();
        $this->template->show();*/
    }
    
    

    /**
     * Set the navigation with the media type drop-down menu in the file browser
     * @access private
     * @see FileBrowser::_getMediaTypeMenu, template, _mediaType, _arrDirectories
     */
    private function _getNavigation() {
        global $_ARRAYLANG;

        $ckEditorFuncNum = isset($_GET['CKEditorFuncNum']) ? '&amp;CKEditorFuncNum=' . contrexx_raw2xhtml($_GET['CKEditorFuncNum']) : '';

        $this->template->addBlockfile('FILEBROWSER_NAVIGATION', 'fileBrowser_navigation', 'BrowserNavigation.html');
        $this->template->setVariable(array(
            'FILEBROWSER_MEDIA_TYPE_MENU' => $this->_getMediaTypeMenu('fileBrowserType', $this->_mediaType, 'onchange="window.location.replace(\'' . \CSRF::enhanceURI('index.php?cmd=fileBrowser') . '&amp;standalone=true&amp;langId=' . $this->_frontendLanguageId . '&amp;type=\'+this.value+\'' . $ckEditorFuncNum . '\')"'),
            'TXT_FILEBROWSER_PREVIEW' => $_ARRAYLANG['TXT_FILEBROWSER_PREVIEW']
        ));
        
        /*

        if ($this->_mediaType != 'webpages') {
            // only show directories if the files should be displayed
            if (count($this->_arrDirectories) > 0) {
                foreach ($this->_arrDirectories as $arrDirectory) {
                    $this->template->setVariable(array(
                        'FILEBROWSER_FILE_PATH' => "index.php?cmd=fileBrowser&amp;standalone=true&amp;langId={$this->_frontendLanguageId}&amp;type={$this->_mediaType}&amp;path={$arrDirectory['path']}&amp;CKEditor=" . contrexx_raw2xhtml($_GET['CKEditor']) . $ckEditorFuncNum,
                        'FILEBROWSER_FILE_NAME' => $arrDirectory['name'],
                        'FILEBROWSER_FILE_ICON' => $arrDirectory['icon']
                    ));
                    $this->template->parse('navigation_directories');
                }
            }
        }
        $this->template->parse('fileBrowser_navigation');*/
    }

    /**
     * checks whether a module is available and active
     *
     * @return bool
     */
    private function _checkForModule($strModuleName) {
        global $objDatabase;
        if (($objRS = $objDatabase->SelectLimit("SELECT `status` FROM " . DBPREFIX . "modules WHERE name = '" . $strModuleName . "' AND `is_active` = '1' AND `is_licensed` = '1'", 1)) != false) {
            if ($objRS->RecordCount() > 0) {
                if ($objRS->fields['status'] == 'n') {
                    return false;
                }
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Create html-source of a complete <select>-navigation
     * @param string $name: name of the <select>-tag
     * @param string $selectedType: which <option> will be "selected"?
     * @param string $attrs: further attributes of the <select>-tag
     * @return string html-source
     */
    private function _getMediaTypeMenu($name, $selectedType, $attrs) {
        global $_ARRAYLANG, $_CORELANG;
        
        $menu = "<select name=\"" . $name . "\" " . $attrs . ">";
        foreach ($this->_arrMediaTypes as $type => $text) {
            if (!$this->_checkForModule($type)) {
                continue;
            }
            $text = $_ARRAYLANG[$text];
            if (empty($text)) {
                $text = $_CORELANG[$text];
            }
            $menu .= "<option value=\"" . $type . "\"" . ($selectedType == $type ? " selected=\"selected\"" : "") . ">" . $text . "</option>\n";
        }
        $menu .= "</select>";
        return $menu;
    }

}
