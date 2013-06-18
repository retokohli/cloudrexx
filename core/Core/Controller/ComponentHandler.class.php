<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cx\Core\Core\Controller;

class ComponentException extends \Exception {}

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
    /**
     * @var \Cx\Core\Core\Model\Repository\SystemComponentRepository
     */
    protected $systemComponentRepo;
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
    
    public function __construct($frontend, $em) {
        $this->legacyComponentHandler = new LegacyComponentHandler();
        $this->frontend = $frontend;
        $this->systemComponentRepo = $em->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');
        $this->systemComponentRepo->findAll();
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
    
    public function callPreResolveHooks($mode = 'all') {
        if ($mode == 'all' || $mode == 'legacy') {
            foreach ($this->components as $componentName) {
                if ($this->checkLegacy('preResolve', $componentName)) {
                    continue;
                }
            }
        }
        if ($mode == 'all' || $mode == 'proper') {
            $this->systemComponentRepo->callPreResolveHooks();
        }
    }
    
    public function callPostResolveHooks($mode = 'all') {
        if ($mode == 'all' || $mode == 'legacy') {
            foreach ($this->components as $componentName) {
                if ($this->checkLegacy('postResolve', $componentName)) {
                    continue;
                }
            }
        }
        if ($mode == 'all' || $mode == 'proper') {
            $this->systemComponentRepo->callPostResolveHooks();
        }
    }
    
    public function callPreContentLoadHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('preContentLoad', $componentName)) {
                continue;
            }
        }
        $this->systemComponentRepo->callPreContentLoadHooks();
    }
    
    public function callPreContentParseHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('preContentParse', $componentName)) {
                continue;
            }
        }
        $this->systemComponentRepo->callPreContentParseHooks();
    }
    
    public function callPostContentParseHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('postContentParse', $componentName)) {
                continue;
            }
        }
        $this->systemComponentRepo->callPostContentParseHooks();
    }
    
    public function callPostContentLoadHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('postContentLoad', $componentName)) {
                continue;
            }
        }
        $this->systemComponentRepo->callPostContentLoadHooks();
    }
    
    public function callPreFinalizeHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('preFinalize', $componentName)) {
                continue;
            }
        }
        $this->systemComponentRepo->callPreFinalizeHooks();
    }
    
    public function callPostFinalizeHooks() {
        foreach ($this->components as $componentName) {
            if ($this->checkLegacy('postFinalize', $componentName)) {
                continue;
            }
        }
        $this->systemComponentRepo->callPostFinalizeHooks();
    }
    
    public function loadComponent(\Cx\Core\Core\Controller\Cx $cx, $componentName, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {
        if ($this->checkLegacy('load', $componentName)) {
            \DBG::msg('This is a legacy component (' . $componentName . '), load via LegacyComponentHandler');
            return;
        }
        $component = $this->systemComponentRepo->findOneBy(array('name'=>$componentName));
        if (!$component) {
            \DBG::msg('This is an ugly legacy component (' . $componentName . '), load via LegacyComponentHandler');
            \DBG::msg('Add an exception for this component in LegacyComponentHandler!');
            throw new ComponentException('This is an ugly legacy component(' . $componentName . '), load via LegacyComponentHandler!');
        }
        $component->load($cx, $page);
        \DBG::msg('<b>WELL, THIS IS ONE NICE COMPONENT!</b>');
    }
    
    public function initComponents() {
        // nothing to do yet
    }
}
