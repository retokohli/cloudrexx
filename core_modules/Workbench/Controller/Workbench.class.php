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
            '/core/Core/Model/Entity/ReflectionComponent.class.php',
            '/core_modules/Workbench',
            '/workbench.config',
            '/workbench',
            '/workbench.sh',
            '/workbench.bat',
            '/testing',
        );
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
    
    public function setConfigEntry($identifier, $value) {
        if (!$this->config) {
            $this->loadConfig();
        }
        $this->config[$identifier] = $value;
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
