<?php

/**
 * This is the superclass for all main Controllers for a Component
 * 
 * Every component needs a SystemComponentController for initialization
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
namespace Cx\Core\Component\Model\Entity;

/**
 * This is the superclass for all main Controllers for a Component
 * 
 * Every component needs a SystemComponentController for initialization
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
abstract class SystemComponentController extends Controller {
    
    /**
     * Returns a list of JsonAdapter class names
     * 
     * The array values might be a class name without namespace. In that case
     * the namespace \Cx\{component_type}\{component_name}\Controller is used.
     * If the array value starts with a backslash, no namespace is added.
     * 
     * Avoid calculation of anything, just return an array!
     * @return array List of ComponentController classes
     */
    public abstract function getControllersAccessableByJson();
    
    /**
     * Do something before resolving is done
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\Routing\Url                      $request    The URL object for this request
     */
    public abstract function preResolve(\Cx\Core\Cx $cx, \Cx\Core\Routing\Url $request);
    
    /**
     * Do something after resolving is done
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public abstract function postResolve(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page);
    
    /**
     * Do something before content is loaded from DB
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public abstract function preContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page);
    
    /**
     * Do something before a module is loaded
     * 
     * This method is called only if any module
     * gets loaded for content parsing
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public abstract function preContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page);
    
    /**
     * Load your component. It is needed for this request.
     * 
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public abstract function load(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page);
    
    /**
     * Do something after a module is loaded
     * 
     * This method is called only if any module
     * gets loaded for content parsing
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public abstract function postContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page);
    
    /**
     * Do something after content is loaded from DB
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public abstract function postContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page);
    
    /**
     * Do something before main template gets parsed
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     * @param \Cx\Core\Html\Sigma                       $template   The main template
     */
    public abstract function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html\Sigma $template);
    
    /**
     * Do something after main template got parsed
     * 
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * @param \Cx\Core\Cx                               $cx         The Contrexx main class
     */
    public abstract function postFinalize(\Cx\Core\Cx $cx);
}
