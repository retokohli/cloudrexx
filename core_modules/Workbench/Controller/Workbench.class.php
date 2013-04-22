<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core_Modules\Workbench\Controller;
/**
 * Description of Workbench
 *
 * @author ritt0r
 */
class WorkbenchException extends \Exception {}

class Workbench {
    private $config = null;
    
    /**
     * Returns a list of files (directories include all contained files and folders)
     * @return array 
     */
    public function getFileList() {
        return array(
            '/core_modules/Workbench',
            '/workbench.config',
            '/workbench.sh',
            '/workbench.bat',
            '/testing',
            '/model/doctrine.php',
            '/model/doctrine',
        );
    }
    
    /**
     *
     * @param type $objTemplate
     * @param type $post
     * @return type
     * @throws \Exception 
     * @todo YAML assistant
     * @todo Cx/Module sandbox
     * @todo Language var checker (/translation helper)
     * @todo Component analysis (/testing)
     */
    public function getPage($objTemplate, $post, &$_ARRAYLANG) {
        \DBG::activate(DBG_PHP);
        
        // Initialize
        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }
        $cmd = explode('/', $_GET['act']);
        if (!isset($cmd[0])) {
            $cmd[0] = 'development';
        }
        $controller = $cmd[0];
        if (!isset($cmd[1])) {
            $cmd[1] = '';
        }
        $act = $cmd[1];
        
        // Load controller specific things
        switch ($controller) {
            case 'sandbox':
                // The following code is for sandbox only:
                if ($act == '') {
                    $act = 'dql';
                }
                $navEntries = array(
                    'index.php?cmd=Workbench&amp;act=sandbox/dql' => 'DQL',
                    'index.php?cmd=Workbench&amp;act=sandbox/php' => 'PHP',
                );
                $objTemplate->setVariable('ADMIN_CONTENT', new Sandbox($_ARRAYLANG, $act, $_POST));
                break;
            case 'development':
            default:
                $navEntries = array(
                    'index.php?cmd=Workbench&amp;act=development' => '',
                );
                $objTemplate->setVariable('ADMIN_CONTENT', '');
                break;
        }
        
        // set tabs
        $navigation = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/Workbench/View/Template');
        $navigation->loadTemplateFile('Navigation.html');
        foreach ($navEntries as $href=>$title) {
            $navigation->setVariable(array(
                'HREF' => $href,
                'TITLE' => $title,
            ));
            if ($title == strtoupper($act)) {
                $navigation->touchBlock('active');
            }
            $navigation->parse('tab_entry');
        }
        $objTemplate->setVariable('CONTENT_NAVIGATION', $navigation->get());
    }
    
    public function getConfigEntry($identifier) {
        if (!$this->config) {
            $this->loadConfig();
        }
        if (!isset($this->config[$identifier])) {
            return '';
        }
        return $this->config[$identifier];
    }
    
    public function __destruct() {
        $this->writeConfig();
    }
    
    private function loadConfig() {
        $content = file_get_contents(ASCMS_DOCUMENT_ROOT.'/workbench.config');
        $content = explode("\n", $content);
        $this->config = array();
        foreach ($content as $line) {
            $line = explode('=', $line, 2);
            if (count($line) != 2) {
                continue;
            }
            $this->config[trim($line[0])] = trim($line[1]);
        }
    }
    
    private function writeConfig() {
        $content = '';
        if (!$this->config) {
            return;
        }
        foreach ($this->config as $key=>$value) {
            $content .= $key.'='.$value."\r\n";
        }
        file_put_contents(ASCMS_DOCUMENT_ROOT.'/workbench.config', $content);
    }
}
