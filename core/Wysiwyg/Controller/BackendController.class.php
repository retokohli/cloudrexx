<?php
/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Sebastian Brand <sebastian.brand@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 */

namespace Cx\Core\Wysiwyg\Controller;

/**
 * Specific BackendController for this Component. Use this to easily create a backend view
 *
 * @copyright   Comvation AG
 * @author      Sebastian Brand <sebastian.brand@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController {
    /**
    * Returns a list of available commands (?act=XY)
    * @return array List of acts
    */
    public function getCommands() {
        $cmds = array();
        foreach ($this->getEntityClasses() as $class) {
            $cmds[] = preg_replace('#' . preg_quote($this->getNamespace() . '\\Model\\Entity\\') . '#', '', $class);
        }
        return $cmds;
    }
    
    /**
    * Use this to parse your backend page
    * 
    * You will get the template located in /View/Template/{CMD}.html
    * You can access Cx class using $this->cx
    * To show messages, use \Message class
    * @param \Cx\Core\Html\Sigma $template Template for current CMD
    * @param array $cmd CMD separated by slashes
    */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        global $_ARRAYLANG;
        
        // Parse entity view generation pages
        $entityClassName = $this->getNamespace() . '\\Model\\Entity\\' . current($cmd);
        if (in_array($entityClassName, $this->getEntityClasses())) {
            $this->parseEntityClassPage($template, $entityClassName, current($cmd));
            return;
        }
        
        // Not an entity, parse overview or settings
        switch (current($cmd)) {
            case 'Settings':
                if(isset($_POST)) {
                    \Cx\Core\Setting\Controller\Setting::set('specificStylesheet', isset($_POST["specificStylesheet"])?1:0);
                    
                    \Cx\Core\Setting\Controller\Setting::storeFromPost();
                }
                
                $i = 0;
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('specificStylesheet')
                    && !\Cx\Core\Setting\Controller\Setting::add('specificStylesheet', '0', ++$i, \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX, '1', 'wysiwyg')
                ){
                    throw new \Exception("Failed to add new configuration option");
                }
                
                \Cx\Core\Setting\Controller\Setting::init('Config', 'wysiwyg', 'Yaml');
                
                $tmpl = new \Cx\Core\Html\Sigma();
                \Cx\Core\Setting\Controller\Setting::show(
                    $tmpl,
                    'index.php?cmd=Config&act=Wysiwyg&tpl=Settings',
                    $_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_SETTINGS'],
                    $_ARRAYLANG['TXT_CORE_WYSIWYG_ACT_SETTINGS'],
                    'TXT_CORE_WYSIWYG_'
                );
                
                $template->setVariable('WYSIWYG_CONFIG_TEMPLATE', $tmpl->get());
                break;
            case '':
            default:
                if ($template->blockExists('overview')) {
                    $template->touchBlock('overview');
                }
                break;
        }
    }
    
    /**
     * This is called by the default ComponentController and does all the repeating work
     * 
     * This loads a template named after current $act and calls parsePage($actTemplate)
     * @todo $this->cx->getTemplate()->setVariable() should not be called here but in Cx class
     * @global array $_ARRAYLANG Language data
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved page
     */
    public function getPage(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        global $_ARRAYLANG, $subMenuTitle;
        $subMenuTitle = $_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName())];
        
        $cmd = array('');
        if (isset($_GET['act'])) {
            $cmd = explode('/', contrexx_input2raw($_GET['act']));
        } else {
            $cmd[0] = 'Wysiwyg';
        }
        
        $actTemplate = new \Cx\Core\Html\Sigma($this->getDirectory(true) . '/View/Template/Backend');
        $filename = $cmd[0] . '.html';
        $testFilename = $cmd[0];
        if (!\Env::get('ClassLoader')->getFilePath($actTemplate->getRoot() . '/' . $filename)) {
            $filename = 'Default.html';
            $testFilename = 'Default';
        }
        foreach ($cmd as $index=>$name) {
            if ($index == 0) {
                continue;
            }
            
            $testFilename .= $name;
            if (\Env::get('ClassLoader')->getFilePath($actTemplate->getRoot() . '/' . $testFilename . '.html')) {
                $filename = $testFilename . '.html';
            } else {
                break;
            }
        }
        $actTemplate->loadTemplateFile($filename);
        
        // todo: Messages
        $this->parsePage($actTemplate, $cmd);
        
        // set tabs
        $navigation = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseCorePath() . '/Core/View/Template/Backend');
        $navigation->loadTemplateFile('Navigation.html');
        $commands = array_merge($this->getCommands());
        foreach ($commands as $key=>$command) {
            $subnav = array();
            if (is_array($command)) {
                $subnav = array_merge(array(''), $command);
                $command = $key;
            }
            
            if ($key !== '') {
                if ($cmd[0] == $command) {
                    $navigation->touchBlock('tab_active');
                } else {
                    $navigation->hideBlock('tab_active');
                }
                $act = '&amp;act=' . $command;
                $txt = $command;
                if (empty($command)) {
                    $act = '';
                    $txt = 'DEFAULT';
                }
                $actTxtKey = 'TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName() . '_ACT_' . $txt);
                $actTitle = isset($_ARRAYLANG[$actTxtKey]) ? $_ARRAYLANG[$actTxtKey] : $actTxtKey;
                $navigation->setVariable(array(
                    'HREF' => 'index.php?cmd=' . $this->getName() . $act,
                    'TITLE' => $actTitle,
                ));
                $navigation->parse('tab_entry');
            }
            
            // subnav
            if ($cmd[0] == $command && count($subnav)) {
                $first = true;
                foreach ($subnav as $subcommand) {
                    if ((!isset($cmd[1]) && $first) || ((isset($cmd[1]) ? $cmd[1] : '') == $subcommand)) {
                        $navigation->touchBlock('subnav_active');
                    } else {
                        $navigation->hideBlock('subnav_active');
                    }
                    $act = '&amp;act=' . $cmd[0] . '/' . $subcommand;
                    $txt = (empty($cmd[0]) ? 'DEFAULT' : $cmd[0]) . '_';
                    if (empty($subcommand)) {
                        $act = '&amp;act=' . $cmd[0] . '/';
                        $txt .= 'DEFAULT';
                    } else {
                        $txt .= strtoupper($subcommand);
                    }
                    $actTxtKey = 'TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName() . '_ACT_' . $txt);
                    $actTitle = isset($_ARRAYLANG[$actTxtKey]) ? $_ARRAYLANG[$actTxtKey] : $actTxtKey;
                    $navigation->setVariable(array(
                        'HREF' => 'index.php?cmd=' . $this->getName() . $act,
                        'TITLE' => $actTitle,
                    ));
                    $navigation->parse('subnav_entry');
                    $first = false;
                }
            }
        }
        $txt = $cmd[0];
        if (empty($txt)) {
            $txt = 'DEFAULT';
        }
        
        // default css and js
        if (file_exists($this->cx->getClassLoader()->getFilePath($this->getDirectory(false) . '/View/Style/Backend.css'))) {
            \JS::registerCSS(substr($this->getDirectory(false, true) . '/View/Style/Backend.css', 1));
        }
        if (file_exists($this->cx->getClassLoader()->getFilePath($this->getDirectory(false) . '/View/Script/Backend.js'))) {
            \JS::registerJS(substr($this->getDirectory(false, true) . '/View/Script/Backend.js', 1));
        }
        
        // finish
        $actTemplate->setGlobalVariable($_ARRAYLANG);
        \Cx\Core\Csrf\Controller\Csrf::add_placeholder($actTemplate);
        $page->setContent($actTemplate->get());
        $cachedRoot = $this->cx->getTemplate()->getRoot();
        $this->cx->getTemplate()->setRoot(\Env::get('cx')->getCodeBaseCorePath() . '/Core/View/Template/Backend');
        $this->cx->getTemplate()->addBlockfile('CONTENT_OUTPUT', 'content_master', 'ContentMaster.html');
        $this->cx->getTemplate()->setRoot($cachedRoot);
        $this->cx->getTemplate()->setVariable(array(
            'CONTENT_NAVIGATION' => $navigation->get(),
            'ADMIN_CONTENT' => $page->getContent(),
            'CONTENT_TITLE' => $_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName() . '_ACT_' . $txt)],
        ));
    }
    
    
    protected function getViewGeneratorOptions($entityClassName, $classIdentifier) {
        global $_ARRAYLANG;
        
        return array(
            'header' => $_ARRAYLANG['TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier)],
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
            'fields' => array(
                'title' => array(
                    'header' => $_ARRAYLANG['TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier) . '_TITLE'],
                ),
                'description' => array(
                    'header' => $_ARRAYLANG['TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier) . '_DESCRIPTION'],
                ),
                'inactive' => array(
                    'header' => $_ARRAYLANG['TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier) . '_INACTIVE'],
                ),
                'imagePath' => array(
                    'header' => $_ARRAYLANG['TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier) . '_IMAGE_PATH'],
                    'type' => 'uploader',
                    'showOverview' => false,
                ),
                'htmlContent' => array(
                    'header' => $_ARRAYLANG['TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier) . '_HTML_CONTENT'],
                    'showOverview' => false,
                ),
            ),
        );
    }
    
}
