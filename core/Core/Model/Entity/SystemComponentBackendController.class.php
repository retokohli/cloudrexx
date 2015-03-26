<?php
/**
 * Backend controller to create a default backend view.
 *
 * Create a subclass of this in order to create a normal backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */

namespace Cx\Core\Core\Model\Entity;

/**
 * Backend controller to create a default backend view.
 *
 * Create a subclass of this in order to create a normal backend view
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */
class SystemComponentBackendController extends Controller {
    
    /**
     * Default permission
     * 
     * @var Cx\Core_Modules\Access\Model\Entity\Permission
     */
    protected $defaultPermission;
    
    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands() {
        $cmds = array();
        foreach ($this->getEntityClasses() as $class) {
            $cmds[] = preg_replace('#' . preg_quote($this->getNamespace() . '\\Model\\Entity\\') . '#', '', $class);
        }
        $cmds['Settings'] = array('Help');
        return $cmds;
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
        $navigation = $this->parseNavigation($cmd);
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
    
    /**
     * Parse the navigation
     * 
     * @param array $cmd
     * 
     * @return \Cx\Core\Html\Sigma
     */
    public function parseNavigation($cmd = array()) {
        // set tabs
        $navigation = new \Cx\Core\Html\Sigma(\Env::get('cx')->getCodeBaseCorePath() . '/Core/View/Template/Backend');
        $navigation->loadTemplateFile('Navigation.html');
        
        $commands = array_merge(
                        array('' => array('permission' => $this->defaultPermission)), 
                        $this->getCommands()
                    );
        foreach ($commands as $key => $command) {
            $subNav         = array();
            $currentCommand = is_array($command) ? $key : $command;
            
            if (is_array($command) && isset($command['children'])) {
                $subNav = array_merge(array('' => array('permission' => $this->defaultPermission)), $command['children']);                          
            } else {
                if (array_key_exists('permission', $command)) {
                    unset($command['permission']); // navigation might contain only the permission key, unset it
                }
                $subNav = is_array($command) && !empty($command)  ? array_merge(array(''), $command) : array();
            } 
            //check the main navigation permission
            if (!$this->hasAccessToCommand(array($currentCommand))) {
                continue;
            }
            //parse the main navigation
            $this->parseCurrentNavItem($navigation, 'tab', $currentCommand, '', $cmd[0] == $currentCommand, 0);
            
            // subnav
            if ($cmd[0] == $currentCommand && count($subNav)) {
                $first = true;
                foreach ($subNav as $subkey => $subValue) {
                    $subcommand = is_array($subValue) ? $subkey : $subValue;
                    if (!$this->hasAccessToCommand(array($currentCommand, $subcommand))) {
                        continue;
                    }
                    $isActiveSubNav = (!isset($cmd[1]) && $first) || ((isset($cmd[1]) ? $cmd[1] : '') == $subcommand);
                    //parse the subnavigation
                    $this->parseCurrentNavItem($navigation, 'subnav', $subcommand, $currentCommand, $isActiveSubNav, 1);
                    $first = false;
                }
            }
        }
        return $navigation;
    }
    
    /**
     * Parse the current navigation item
     * 
     * @global array $_ARRAYLANG
     * 
     * @param \Cx\Core\Html\Sigma $navigation
     * @param string              $blockName
     * @param string              $currentCmd
     * @param string              $mainCmd
     * @param boolean             $isActiveNav
     * @param boolean             $isSubNav
     */
    public function parseCurrentNavItem(\Cx\Core\Html\Sigma $navigation, $blockName, $currentCmd, $mainCmd, $isActiveNav, $isSubNav) {
        global $_ARRAYLANG;
        
        if (empty($blockName)) {
            return;
        }
        
        $isActiveNav ? $navigation->touchBlock($blockName . '_active') : $navigation->hideBlock($blockName . '_active');
        
        if (empty($isSubNav)) {
            $act = empty($currentCmd) ? '' : '&amp;act=' . $currentCmd;
            $txt = empty($currentCmd) ? 'DEFAULT' : $currentCmd;
        } else {
            $act = '&amp;act=' . $mainCmd . '/' . $currentCmd;
            $txt = (empty($mainCmd) ? 'DEFAULT' : $mainCmd) . '_';
            $txt .= empty($currentCmd) ? 'DEFAULT' : strtoupper($currentCmd);
        }

        $actTxtKey = 'TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName() . '_ACT_' . $txt);
        $actTitle  = isset($_ARRAYLANG[$actTxtKey]) ? $_ARRAYLANG[$actTxtKey] : $actTxtKey;
        $navigation->setVariable(array(
            'HREF' => 'index.php?cmd=' . $this->getName() . $act,
            'TITLE' => $actTitle,
        ));
        $navigation->parse($blockName . '_entry');
    }
    
    /**
     * Check the access permission based on the command
     * 
     * @param array $commands
     * 
     * @return boolean
     */
    public function hasAccessToCommand($commands = array()) {
        $currentCommands = array_merge(array('' => array('permission' => $this->defaultPermission)), $this->getCommands());
        
        foreach ($commands as $command) {
            $cmd = isset($currentCommands[$command]) ? $currentCommands[$command] : array();            
            if (!$this->hasAccess($cmd)) {
                return false;
            }
            unset($cmd['permission']);
            $currentCommands = isset($cmd['children']) ? $cmd['children'] : $cmd;
        }
        return true;
    }
    
    /**
     * Check the access permission
     * 
     * @param array $command
     * 
     * @return boolean
     */
    public function hasAccess($command) {
        $objPermission = is_array($command) && isset($command['permission']) ? $command['permission'] : $this->defaultPermission;
        if ($objPermission instanceof \Cx\Core_Modules\Access\Model\Entity\Permission) {
            if (!$objPermission->hasAccess()) {
                return false;
            }
        }
        return true;
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
                if (!isset($cmd[1])) {
                    $cmd[1] = '';
                }
                switch ($cmd[1]) {
                    case '':
                    default:
                        if (!$template->blockExists('mailing')) {
                            return;
                        }
                        $template->setVariable(
                            'MAILING',
                            \Cx\Core\MailTemplate\Controller\MailTemplate::adminView(
                                $this->getName(),
                                'nonempty',
                                $config['corePagingLimit'],
                                'settings/email'
                            )->get()
                        );
                        break;
                }
                break;
            case '':
            default:
                if ($template->blockExists('overview')) {
                    $template->touchBlock('overview');
                }
                break;
        }
    }
    
    protected function parseEntityClassPage($template, $entityClassName, $classIdentifier) {
        if (!$template->blockExists('entity_view')) {
            return;
        }
        $view = new \Cx\Core\Html\Controller\ViewGenerator(
            $entityClassName,
            $this->getViewGeneratorOptions($entityClassName, $classIdentifier)
        );
        $template->setVariable('ENTITY_VIEW', $view->render());
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
        );
    }
}

