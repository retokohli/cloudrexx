<?php

/**
 * This is the superclass for all main Controllers for a Component
 * Every component needs a ComponentController for initialization
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
namespace Cx\Core\Component\Model\Entity;

/**
 * This is the superclass for all main Controllers for a Component
 * Every component needs a ComponentController for initialization
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class SystemComponentController extends Controller {
    
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
     * @param \Cx\Core\Html\Sigma                       $template   The main template
     */
    abstract function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html\Sigma $template);
    
    /**
     * Do something after main template got parsed
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     */
    abstract function postFinalize(\Cx\Core\Cx $cx);
}
