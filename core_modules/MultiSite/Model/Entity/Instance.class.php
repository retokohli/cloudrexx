<?php
namespace Cx\Core_Modules\MultiSite\Model\Entity;

class InstanceException extends \Exception {}

class Instance extends \Cx\Core\Component\Model\Entity\EntityBase {
    protected $basepath = null;
    public $name = null;
    public $licenseState = null;
    public $licenseEdition = null;
    public $licenseLastUpdate = null;
    public $domain = null;
    public $aliases = array();
    
    public function __construct($basepath, $name) {
        $this->basepath = $basepath;
        $this->name = $name;
        if (!file_exists($this->basepath . '/' . $this->name . '/config/settings.php')) {
            throw new InstanceException('No instance found on path ' . $this->basepath . '/' . $this->name);
        }
        $settings = file_get_contents($this->basepath . '/' . $this->name . '/config/settings.php');
        if (!file_exists($this->basepath . '/' . $this->name . '/config/configuration.php')) {
            throw new InstanceException('No instance found on path ' . $this->basepath . '/' . $this->name);
        }
        $config = file_get_contents($this->basepath . '/' . $this->name . '/config/configuration.php');
        $matches = array();
        preg_match('/\$_CONFIG\\[\'coreCmsEdition\'\\][\s]*=[\s]*"([a-zA-Z]*)";/', $settings, $matches);
        if (isset($matches[1])) {
            $this->licenseEdition = $matches[1];
        }
        preg_match('/\$_CONFIG\\[\'licenseState\'\\][\s]*=[\s]*"([a-zA-Z]*)";/', $settings, $matches);
        if (isset($matches[1])) {
            $this->licenseState = $matches[1];
        }
        preg_match('/\$_CONFIG\\[\'domainUrl\'\\][\s]*=[\s]*"([a-zA-Z-_\.]*)";/', $settings, $matches);
        if (isset($matches[1])) {
            $this->domain = $matches[1];
        }
        preg_match('/\$_CONFIG\\[\'licenseSuccessfulUpdate\'\\][\s]*=[\s]*([0-9]*);/', $settings, $matches);
        $supd = 0;
        if (isset($matches[1])) {
            $supd = $matches[1];
        }
        preg_match('/\$_CONFIG\\[\'licenseFailedUpdate\'\\][\s]*=[\s]*([0-9]*);/', $settings, $matches);
        $fupd = 0;
        if (isset($matches[1])) {
            $fupd = $matches[1];
        }
        $this->licenseLastUpdate = ($supd > $fupd ? $supd : $fupd);
        preg_match('/\$_PATHCONFIG\\[\'ascms_root_offset\'\\][\s]*=[\s]*["\']([a-z\\/A-Z-_\.]*)["\'];/', $config, $matches);
        $rootOffset = '';
        if (isset($matches[1])) {
            $rootOffset = $matches[1];
        }
        preg_match('/\$_PATHCONFIG\\[\'ascms_installation_root\'\\][\s]*=[\s]*["\']([0-9a-z\\/A-Z-_\.]*)["\'];/', $config, $matches);
        $instRoot = '';
        if (isset($matches[1])) {
            $instRoot = $matches[1];
        }
        preg_match('/\$_PATHCONFIG\\[\'ascms_installation_offset\'\\][\s]*=[\s]*["\']([0-9a-z\\/A-Z-_\.]*)["\'];/', $config, $matches);
        $instOffset = '';
        if (isset($matches[1])) {
            $instOffset = $matches[1];
        }
        if ($instRoot != ASCMS_PATH || $instOffset != ASCMS_PATH_OFFSET) {
            throw new InstanceException('Instance could not be loaded from path ' . $this->basepath . '/' . $this->name);
        }
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        // rename
    }
    
    public function getLicenseState() {
        return $this->licenseState;
    }
    
    public function getLicenseEdition() {
        return $this->licenseEdition;
    }
    
    public function getAliases() {
        return $this->aliases;
    }
    
    public function addAlias($alias) {
        
    }
    
    public function removeAlias($alias) {
        
    }
}
