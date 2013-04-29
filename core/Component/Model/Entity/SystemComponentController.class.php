<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Cx\Core\Component\Model\Entity;
/**
 * Description of SystemComponentController
 *
 * @author ritt0r
 */
abstract class SystemComponentController {
    protected $systemComponent;
    
    public function __construct(\Cx\Core\Component\Model\Entity\SystemComponent $systemComponent) {
        $this->systemComponent = $systemComponent;
    }
    
    public function __call($methodName, $arguments) {
        return call_user_func(array($this->systemComponent, $methodName), $arguments);
    }
    
    /**
     * Do something before resolving is done
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\Routing\Url                      $request    The URL object for this request
     */
    abstract function preResolve(\Cx\Core\Cx $cx, \Cx\Core\Routing\Url $request);
    
    /**
     * Do something after resolving is done
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    abstract function postResolve(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null);
    
    /**
     * Do something before content is loaded from DB
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    abstract function preContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null);
    
    /**
     * Do something before a module is loaded
     * This method is called only if any module
     * gets loaded for content parsing
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    abstract function preContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null);
    
    /**
     * Load your component. It is needed for this request.
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    abstract function load(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null);
    
    /**
     * Do something after a module is loaded
     * This method is called only if any module
     * gets loaded for content parsing
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param string                                    $content    The parsed content
     */
    abstract function postContentParse(\Cx\Core\Cx $cx, &$content);
    
    /**
     * Do something before content is loaded from DB
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param string                                    $content    The parsed content
     */
    abstract function postContentLoad(\Cx\Core\Cx $cx, &$content);
    
    /**
     * Do something before main template gets parsed
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\Html                             $template   The main template
     */
    abstract function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html $template);
    
    /**
     * Do something after main template got parsed
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     */
    abstract function postFinalize(\Cx\Core\Cx $cx);
}
