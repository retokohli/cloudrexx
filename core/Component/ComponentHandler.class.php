<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core\Component;

/**
 * Description of ComponentHandler
 *
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ComponentHandler {
    /**
     * @var LegacyContentHandler
     */
    private $legacyComponentHandler;
    private $frontend;
    private $components = array(
        'License',
        'Resolver',
        'Security',
        'Cache',
        'Session',
        'FwUser',
        
        'Uploader',
        'Captcha',
        'FrontendEditing',
        'JsonData',
        'Newsletter',
        'Downloads',
        'Feed',
        'Immo',
        'Stats',
        'Block',
        'Data',
        'Teasers',
        'Popup',
        'Knowledge',
        'Calendar',
        'News',
        'MediaDir',
        'Blog',
        'Voting',
        'Podcast',
        'Gallery',
        'Directory',
        'Forum',
        'Banner',
        'Market',
        'Shop',
        
        // backend only
        'Js',
        'ComponentHandler',
        'Csrf',
        'Language',
        'Message',
    );
    
    public function __construct($frontend) {
        $this->legacyComponentHandler = new LegacyComponentHandler();
        $this->frontend = $frontend;
    }
    
    /**
     * Checks for existance of legacy exception and executes it if available
     * @param String $action The action to be executed
     * @param String $componentName Name of the component to execute the action
     * @return boolean True if legacy has an exception for this action and component
     */
    private function checkLegacy($action, $componentName) {
        if ($this->legacyComponentHandler->hasExceptionFor(
            $this->frontend,
            $action,
            $componentName
        )) {
            $this->legacyComponentHandler->executeException(
                $this->frontend,
                $action,
                $componentName
            );
            return true;
        }
        return false;
    }
    
    public function callPreResolveHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('preResolve', $componentName)) {
                continue;
            }
            // @todo: add non legacy code
        }
    }
    
    public function callPostResolveHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('postResolve', $componentName)) {
                continue;
            }
            // @todo: add non legacy code
        }
    }
    
    public function callPreContentLoadHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('preContentLoad', $componentName)) {
                continue;
            }
            // @todo: add non legacy code
        }
    }
    
    public function callPostContentLoadHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('postContentLoad', $componentName)) {
                continue;
            }
            // @todo: add non legacy code
        }
    }
    
    public function loadComponent($componentName) {
        if ($this->checkLegacy('load', $componentName)) {
            continue;
        }
        // @todo: add non legacy code
    }
    
    public function initComponents() {
        // nothing to do yet
    }
}
