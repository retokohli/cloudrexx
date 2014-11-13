<?php

/**
 * JSON Adapter for View Manager
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_viewmanager
 */

namespace Cx\Core\ViewManager\Controller;

/**
 * JSON Adapter for View Manager
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_viewmanager
 */
class JsonViewManager implements \Cx\Core\Json\JsonAdapter {
    /**
     * List of messages
     * @var Array 
     */
    private $messages = array();
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'ViewManager';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array(
            'activateTheme'             => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkActivatePermission')),
            'activateLanguages'         => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkActivatePermission')),
            'checkThemeExistsByThemeId' => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkDeletePermission')),
            'deleteThemeById'           => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkDeletePermission')),
            'delete'                    => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkManipulationPermission')),            
            'rename'                    => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkManipulationPermission')),
            'newWithin'                 => new \Cx\Core\Access\Model\Entity\Permission(array('http', 'https'), array('post'), true, array($this, 'checkManipulationPermission')),            
            );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions() {
        return null;
    }
    /**
     * activate the selected theme as standard theme
     * 
     * @global type $objDatabase
     */
    public function activateTheme() {
        global $objDatabase;
        
        // array contains the database column name for each theme type
        $themeTypes = array(
            'themesid',
            'mobile_themes_id',
            'print_themes_id',
            'pdf_themes_id',
            'app_themes_id'
        );
        
        $themeId   = isset($_POST['themeId']) ? $_POST['themeId'] : '';
        $themeType = isset($_POST['themeType']) && array_key_exists($_POST['themeType'], $themeTypes) ? intval($_POST['themeType']) : 0;
        
        if (!empty($themeId)) {
            if (count(\FWLanguage::getActiveFrontendLanguages()) > 1) {
                if (isset($_POST['themesLangId'])) {
                    foreach ($_POST['themesLangId'] as $langId) {
                        $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET `". $themeTypes[$themeType] ."` = '".intval($themeId)."' WHERE id=".intval($langId));
                    }
                }
            } else {
               $objDatabase->Execute("UPDATE ".DBPREFIX."languages SET `". $themeTypes[$themeType] ."` ='".intval($themeId)."' WHERE `frontend` = 1"); 
            }
        }
        
    }
    
    /**
     * activate selected languages for the corresponding theme
     * 
     * @return array result
     */
    public function activateLanguages() {
        
        $themeTypes = array(
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_STANDARD, 
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_MOBILE,  
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PRINT,  
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_PDF,  
          \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_APP  
        );
        
        $themeId   = isset($_POST['themeId']) ? $_POST['themeId'] : 0;
        $themeType = isset($_POST['themeType']) && array_key_exists($_POST['themeType'], $themeTypes) ? intval($_POST['themeType']) : 0;
        
        $selectThemeNotInLanguages = array();
        
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        if (!empty($themeId)) {
            $theme = $themeRepository->findById($themeId);
            
            $selectThemeNotInLanguages = array_diff_key(
                \FWLanguage::getActiveFrontendLanguages(),
                array_flip($theme->getLanguagesByType($themeTypes[$themeType]))
            );
        }
        

        foreach ($selectThemeNotInLanguages as $selectThemeNotInLanguage){
            $result[] = array(
                "lang_id"     => $selectThemeNotInLanguage['id'],
                "lang_name"   => $selectThemeNotInLanguage['name']
            );
        }
        
        return $result;
    }
    
    /**
     * Check whether the theme is selected for any of the active languages/custom theme for active languages/other theme
     * 
     * @global \Cx\Core\ViewManager\Controller\type $_ARRAYLANG
     * @return array result
     */
    public function checkThemeExistsByThemeId() {
        global $_ARRAYLANG;
        
        $_ARRAYLANG = \Env::get('init')->loadLanguageData('ViewManager');
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $themeId         = isset($_GET['themeId']) ? $_GET['themeId'] : 0;
        
        $pageRepo = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $pages = $pageRepo->findBy(array(
            'skin' => intval($themeId)
        ));
        
        if (isset($themeId)) {
            $theme = $themeRepository->findById($themeId);
            //Check whether the theme is selected for any of the active languages
            if (!empty($theme->getLanguages())) {
                $result = array(
                    "isTrue"  => true,
                    "Content" => $_ARRAYLANG['TXT_THEME_DELETE_CURRENT_ACTIVE_ALERT']
                );
            } else {
                if (empty($pages)) {
                    $result = array(
                            "isTrue"  => false,
                            "Content" => $_ARRAYLANG['TXT_THEME_DELETE_ALERT']
                        );
                } else {
                    foreach ($pages as $page) {
                        //Check whether the selected theme is selected for custom theme for any of the content pages of the active frontend languages
                        if ($page->getSkin() == $themeId) {
                           $result = array(
                               "isTrue"  => false,
                               "Content" => $_ARRAYLANG['TXT_THEME_DELETE_CUSTOM_ACTIVE_ALERT']
                            ); 
                        }
                    }
                }
                
            }
            
        }

        return $result;
    }
    /**
     * Delete selected theme and its theme folder 
     * 
     * @global type $_ARRAYLANG
     * @return array status
     */
    function deleteThemeById() {
        global $_ARRAYLANG;
        
        $_ARRAYLANG = \Env::get('init')->loadLanguageData('ViewManager');
        $delThemeId         = isset($_GET['delThemeId']) ? $_GET['delThemeId'] : 0;
        $themeRepository    = new \Cx\Core\View\Model\Repository\ThemeRepository();
        if (!empty($delThemeId)) {
            $theme = $themeRepository->findById($delThemeId);

            if (!$theme) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_STATUS_CANNOT_DELETE']);
            }
            $themeFolderPath = (\Env::get('cx')->getWebsiteThemesPath() . '/' . $theme->getFoldername());
            
            //Check whether the selected theme is selected for any of the active languages
            
            if(!empty($theme->getLanguages()) && file_exists($themeFolderPath)) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_STATUS_CANNOT_DELETE']);
            }
            
            // delete whole folder with subfolders in case it exists
            if (file_exists($themeFolderPath) && !\Cx\Lib\FileSystem\FileSystem::delete_folder($themeFolderPath, true)
            ) {
                //error
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_STATUS_CANNOT_DELETE']);
            } 
            
            //setting 0 for the custom theme for any of the content pages of the active frontend languages
            $pageRepo = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
            $pages = $pageRepo->findBy(array(
                'skin' => intval($theme->getId()),
            ));
            foreach ($pages as $page) {
                $page->setSkin(0);
                \Env::get('em')->persist($page);
            }
            \Env::get('em')->flush();
            
            //Remove theme details from the database.
            if ($themeRepository->remove($theme)) {
                return array('status' => 'success', 'message' => $theme.": ".$_ARRAYLANG['TXT_STATUS_SUCCESSFULLY_DELETE']);
            }
        }
        
        
    }
    
    /**
     * Callback authentication for checking the user's access permission
     * 
     * @return boolean
     */
    public function checkActivatePermission() 
    {
        if (\Permission::checkAccess(46, 'static', true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Callback authentication for checking the user's access permission
     * 
     * @return boolean
     */
    public function checkDeletePermission() 
    {
        if (\Permission::checkAccess(47, 'static', true)) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Callback authentication for checking the user's access permission
     * 
     * @return boolean
     */
    public function checkManipulationPermission() 
    {
        if (\Permission::checkAccess(47, 'static', true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Name of the file or folder
     * 
     * @global array $_CORELANG
     * 
     * @param string $themeFilePath   themeFilePath
     * @param string $currentFileName currentFilePath
     * 
     * @return string filename
     */
    public  function copyTitle($themeFilePath, $currentFileName)
    {
        global $_CORELANG;
        
        //check the input is file or not
        $currentFileExtn = '';
        $fileInfo = pathinfo($currentFileName);
        if (!empty($fileInfo['extension'])) {
            $currentFileName = $fileInfo['filename'];
            $currentFileExtn = $fileInfo['extension'];
        }
        
        if (preg_match('#\(copy\)#i', $currentFileName) || preg_match('#\(copy \d\)#i', $currentFileName)) {
            $oldFileName = $currentFileName;
            $newFileName = $currentFileName . ' (' . $_CORELANG['TXT_CORE_CM_COPY_OF_PAGE'] . ')';
        } else {
            $oldFileName = preg_replace('@\([^\)]*\)$@','',$currentFileName);
            $newFileName = $oldFileName . ' (' . $_CORELANG['TXT_CORE_CM_COPY_OF_PAGE'] . ')';
        }
        
        $i = 1;
        while (\Cx\Lib\FileSystem\FileSystem::exists($themeFilePath.$newFileName)) {
            $i++;
            $newFileName = $oldFileName. ' (' . sprintf($_CORELANG['TXT_CORE_CM_COPY_N_OF_PAGE'], $i) . ')';
        }
        return !empty($currentFileExtn) ? $newFileName.'.'.$currentFileExtn : $newFileName;
    }
    
    
    /**
     * delete the file or directory
     * 
     * @param array $params supplied arguments from JsonData-request
     * @return string
     */
    public function delete($params)
    {
        global $_ARRAYLANG, $objInit;
        
        $_ARRAYLANG = $objInit->loadLanguageData('ViewManager');
        if (empty($params['post']['themes']) || empty($params['post']['themesPage'])) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_EMPTY_PARAMS']);
        }

        $filePath           = $params['post']['themesPage'];
        $currentThemeFolder = \Env::get('cx')->getWebsiteThemesPath() . '/'.$params['post']['themes'].'/';
        
        $pathStripped = ltrim($params['post']['themesPage'], '/');
        if (empty($pathStripped)) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_EMPTY_PARAMS']);
        }
        
        if (
               !\Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolder . $filePath) 
            && \Cx\Core\ViewManager\Controller\ViewManager::isFileTypeComponent($filePath)
           ) { // resolve the component file
            $componentFilePath = \Cx\Core\ViewManager\Controller\ViewManager::getComponentFilePath($filePath, false);
            if ($componentFilePath && \Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolder . $componentFilePath)) { // file exists 
                $filePath = \Cx\Core\ViewManager\Controller\ViewManager::getComponentFilePath($filePath, false);
            } else { // file not exists may be a folder
                $filePath = \Cx\Core\ViewManager\Controller\ViewManager::replaceComponentFolderByItsType($filePath);
            }
        }
        
        if (\Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolder . $filePath)) {
            if (is_dir($currentThemeFolder . $filePath)) {
                $status = \Cx\Lib\FileSystem\FileSystem::delete_folder($currentThemeFolder . $filePath,true);
            } else {
                $status = \Cx\Lib\FileSystem\FileSystem::delete_file($currentThemeFolder . $filePath);
            }
            
            if (!$status) {
                return array('status' => 'error', 'reload' => false, 'message' => $_ARRAYLANG['TXT_THEME_DELETE_FAILED']);
            }
            return array('status' => 'success', 'reload' => true, 'message' =>  $_ARRAYLANG['TXT_THEME_DELETE_SUCCESS']);
        }
        
        return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_FILE_NOT_EXITS'], contrexx_input2xhtml($filePath)));
    }
    
    /**
     * rename the file or directory
     * 
     * @param array $params supplied arguments from JsonData-request
     * @return string
     */
    public function rename($params)
    {
        global $_ARRAYLANG, $objInit;
        
        $_ARRAYLANG = $objInit->loadLanguageData('ViewManager');
        if (empty($params['post']['theme']) || empty($params['post']['oldName']) || empty($params['post']['newName'])) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_EMPTY_PARAMS']);
        }
        if ($params['post']['isFolder'] && preg_match('/^\./', trim($params['post']['newName']))) { // folder name should not start with dot(.)
            return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_FOLDER_NAME_NOT_ALLOWED'], contrexx_input2xhtml($params['post']['newName'])));
        }
        
        $matches = null;
        preg_match('@{([0-9A-Za-z._-]+)(:([_a-zA-Z][A-Za-z_0-9]*))?}@sm', $params['post']['newName'], $matches);
        if (!empty($matches)) {
            return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_NAME_NOT_ALLOWED'], contrexx_input2xhtml($params['post']['newName'])));
        }
        
        $currentThemeFolder = \Env::get('cx')->getWebsiteThemesPath() . '/'.$params['post']['theme'];
        $oldFilePath        = $params['post']['oldName'];
        $newFileName        = $params['post']['newName'];
        $newFilePath        = \Cx\Lib\FileSystem\FileSystem::replaceCharacters($newFileName);
                
        // Cannot rename the virtual directory
        $virtualDirs = array(
            '/'. \Cx\Core\Core\Model\Entity\SystemComponent::TYPE_CORE_MODULE,
            '/'. \Cx\Core\Core\Model\Entity\SystemComponent::TYPE_MODULE,
            '/'. \Cx\Core\Core\Model\Entity\SystemComponent::TYPE_CORE
        );
        if (in_array($oldFilePath, $virtualDirs) || in_array('/'.$newFilePath, $virtualDirs)) {
            return array('status' => 'error', 'reload' => false, 'message' => $_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_RENAME_VIRTUAL_FOLDER']);
        }
        
        if (
               !\Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolder . $oldFilePath) 
            && \Cx\Core\ViewManager\Controller\ViewManager::isFileTypeComponent($oldFilePath)
           ) { // resolve the component file
            $componentFilePath = \Cx\Core\ViewManager\Controller\ViewManager::getComponentFilePath($oldFilePath, false);
            if ($componentFilePath && \Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolder . $componentFilePath)) { // file exists                 
                $oldFilePath = \Cx\Core\ViewManager\Controller\ViewManager::getComponentFilePath($oldFilePath, false);
            } else { // file not exists may be a folder
                $oldFilePath = \Cx\Core\ViewManager\Controller\ViewManager::replaceComponentFolderByItsType($oldFilePath);
            }
        }        
        
        if (\Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolder .'/'. $oldFilePath)) {
            $dirName = dirname($currentThemeFolder . $oldFilePath);
            
            if (!\FWValidator::is_file_ending_harmless($newFilePath)) {
                return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_FILE_EXTENSION_NOT_ALLOWED'], contrexx_input2xhtml($newFilePath)));
            }
            
            if (\Cx\Lib\FileSystem\FileSystem::exists($dirName . '/'. $newFilePath)) {
                return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_FILE_ALREADY_EXITS'], contrexx_input2xhtml($newFileName)));
            }
            
            \Cx\Lib\FileSystem\FileSystem::move($currentThemeFolder . $oldFilePath, $dirName . '/'. $newFilePath, true);
            
            if (!\Cx\Lib\FileSystem\FileSystem::exists($dirName . '/'. $newFilePath)) {
                return array('status' => 'error', 'reload' => false, 'message' => $_ARRAYLANG['TXT_THEME_RENAME_FAILED']);
            }
            
            $path = preg_replace('#' . $currentThemeFolder . '#', '', $dirName . '/'. $newFilePath);
            
            return array('status' => 'success', 'reload' => true, 'path' => \Cx\Core\ViewManager\Controller\ViewManager::getThemeRelativePath($path), 'message' =>  $_ARRAYLANG['TXT_THEME_RENAME_SUCCESS']);
        }
        
        return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_FILE_NOT_EXITS'], contrexx_input2xhtml($newFileName)));
    }

    /**
     * create new file or folder
     * 
     * @param array $params supplied arguments from JsonData-request
     * @return string
     */
    public function newWithin($params)
    {
        global $_ARRAYLANG, $objInit;
        
        $_ARRAYLANG = $objInit->loadLanguageData('ViewManager');
        if (empty($params['post']['theme']) || empty($params['post']['name'])) {
            return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_EMPTY_PARAMS']);
        }
        
        if ($params['post']['isFolder'] && preg_match('/^\./', trim($params['post']['name']))) { // folder name should not start with dot(.)
            return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_FOLDER_NAME_NOT_ALLOWED'], contrexx_input2xhtml($params['post']['name'])));
        }
        
        $matches = null;
        preg_match('@{([0-9A-Za-z._-]+)(:([_a-zA-Z][A-Za-z_0-9]*))?}@sm', $params['post']['name'], $matches);
        if (!empty($matches)) {
            return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_NAME_NOT_ALLOWED'], contrexx_input2xhtml($params['post']['newName'])));
        }
        
        // Cannot rename the virtual directory
        $virtualDirs = array(
            '/'. \Cx\Core\Core\Model\Entity\SystemComponent::TYPE_CORE_MODULE,
            '/'. \Cx\Core\Core\Model\Entity\SystemComponent::TYPE_MODULE,
            '/'. \Cx\Core\Core\Model\Entity\SystemComponent::TYPE_CORE
        );

        $currentThemeFolderDirPath = \Env::get('cx')->getWebsiteThemesPath() . '/'.$params['post']['theme'].'/';
        $newFileName               = \Cx\Lib\FileSystem\FileSystem::replaceCharacters($params['post']['name']);        

        if (!\FWValidator::is_file_ending_harmless($newFileName)) {
            return array('status' => 'error', 'reload' => false, 'message' => sprintf($_ARRAYLANG['TXT_THEME_FILE_EXTENSION_NOT_ALLOWED'], contrexx_input2xhtml($newFileName)));
        }
        
        if (in_array('/'.$newFileName, $virtualDirs)) {
            return array('status' => 'error', 'reload' => false, 'message' => $_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_VIRTUAL_FOLDER']);
        }
        
        if (!\Cx\Lib\FileSystem\FileSystem::exists($currentThemeFolderDirPath.$newFileName)) {
            if ($params['post']['isFolder']) {
                $status = \Cx\Lib\FileSystem\FileSystem::make_folder($currentThemeFolderDirPath.$newFileName);
            } else {
                $status = \Cx\Lib\FileSystem\FileSystem::touch($currentThemeFolderDirPath.$newFileName);
            }
            
            if (!$status) {
                return array('status' => 'error', 'message' => $_ARRAYLANG['TXT_THEME_NEWFILE_FAILED']);
            }
            return array('status' => 'success', 'reload' => true, 'message' => $_ARRAYLANG['TXT_THEME_NEWFILE_SUCCESS'], 'path' => '/' .$newFileName);
        }
        return array('status' => 'error', 'message' => sprintf($_ARRAYLANG['TXT_THEME_OPERATION_FAILED_FOR_FILE_ALREADY_EXITS'], contrexx_input2xhtml($newFileName)));
    }    
    
}
