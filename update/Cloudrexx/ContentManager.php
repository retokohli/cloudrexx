<?php

require_once dirname(dirname(dirname(__FILE__))) . '/core/Core/init.php';
init('minimal');
echo contentManagerUpdates();

function contentManagerUpdates() {
    //Database migration
    try {
        //update module name 
        \Cx\Lib\UpdateUtil::sql("INSERT INTO `".DBPREFIX."modules` (`id`, `name`, `distributor`, `description_variable`, `status`, `is_required`, `is_core`, `is_active`, `is_licensed`) VALUES ('72', 'ContentManager', 'DEV', 'TXT_CONTENTMANAGER_MODULE_DESCRIPTION', 'n', '0', '1', '1', '1')");
        //update navigation url
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=ContentManager&act=new' WHERE `area_id` = 5");
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=ContentManager' WHERE `area_id` = 6");
        \Cx\Lib\UpdateUtil::sql("UPDATE `".DBPREFIX."backend_areas` SET `uri` = 'index.php?cmd=ContentManager' WHERE `area_id` = 161");
    } catch (\Cx\Lib\UpdateException $e) {
        return "Error: $e->sql";
    }
    
    //migrating custom application template
    $pageRepo   = \Env::get('em')->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
    $themeRepo  = new \Cx\Core\View\Model\Repository\ThemeRepository();
    
    $pages      = $pageRepo->findBy(array('type' => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION));
    foreach ($pages As $page) {
        try {
            $designTemplateName  = $page->getSkin() ? $themeRepo->findById($page->getSkin())->getFoldername() : $themeRepo->getDefaultTheme()->getFoldername();
            $cmd                 = !$page->getCmd() ? 'Default' : ucfirst($page->getCmd());
            $moduleFolderName    = contrexx_isCoreModule($page->getModule()) ? 'core_modules' : 'modules';
        
            $themesPath = ASCMS_THEMES_PATH . '/' . $designTemplateName;
        
            //check common module or core_module folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName)) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName);
            }
        
            //check module's folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName . '/' . $page->getModule())) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName . '/' . $page->getModule());
            }
            
            //check module's template folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template')) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template');
            }
        
            //check module's Frontend folder exists
            if (!file_exists($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template/Frontend')) {
                \Cx\Lib\FileSystem\FileSystem::make_folder($themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template/Frontend');
            }
        
            $targetPath = $themesPath . '/' . $moduleFolderName . '/' . $page->getModule() . '/Template/Frontend';
            $applicationTemplateName = getFilename($targetPath, $cmd . '_custom_' . FWLanguage::getLanguageCodeById($page->getLang()));
        
            if (file_exists($targetPath)) {
                //create a application template file
                $file = new \Cx\Lib\FileSystem\File($targetPath . '/' . $applicationTemplateName);
                $file->write($page->getContent());
            }
            
            //update application template 
            $page->setContent('{APPLICATION_DATA}');
            $page->setApplicationTemplate($applicationTemplateName);
            $page->setUseCustomApplicationTemplateForAllChannels(1);
            \Env::get('em')->persist($page);
            \Env::get('em')->flush();
            
        } catch (\Exception $e) {
            throw new \Exception('Error :' . $e);
        }
    }
    
    return 'Application template migrated successfully.';
}

function getFilename($path, $name) {
    if (!file_exists($path . '/' . $name . '.html')) {
        return $name . '.html';
    }
    
    $suffix = 1;
    while (file_exists($path . '/' . $name . $suffix . '.html')) {
        $suffix++;
    }
    
    return $name . $suffix . '.html';
}