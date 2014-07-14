<?php
namespace Cx\Core_Modules\MultiSite\Controller;

class ComponentController extends \Cx\Core\Component\Model\Entity\SystemComponentController {
	
	
	/**
	 * Do something before resolving is done
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param \Cx\Core\Routing\Url                      $request    The URL object for this request
	 */
	public function preResolve(\Cx\Core\Cx $cx, \Cx\Core\Routing\Url $request) {
		
	}
	
	/**
	 * Do something after resolving is done
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
	 */
	public function postResolve(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {
		
	}
	
	/**
	 * Do something before content is loaded from DB
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
	 */
	public function preContentLoad(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {
		
	}
	
	/**
	 * Do something before a module is loaded
	 * This method is called only if any module
	 * gets loaded for content parsing
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
	 */
	public function preContentParse(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {
		
	}
	
	/**
	 * Load your component. It is needed for this request.
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
	 */
	public function load(\Cx\Core\Cx $cx, \Cx\Core\ContentManager\Model\Entity\Page $page = null) {
		$objTemplate = $cx->getTemplate();
		$_ARRAYLANG = \Env::get('init')->loadLanguageData($this->getName());
		
        $navEntries = array(
            'index.php?cmd=MultiSite' => 'Instances',
            'index.php?cmd=MultiSite&amp;act=new' => 'New',
            'index.php?cmd=MultiSite&amp;act=settings' => 'Settings',
        );
        $act = 'instances';
		
	    if (isset($_GET['act'])) {
        	$act = contrexx_input2raw($_GET['act']);
        }
        
        $actTemplate = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/MultiSite/View/Template/Backend');
        
        switch ($act) {
		
        /* EVERYTHING ABOVE HERE SHOULD BE DONE IN SystemComponentController */
        
            case 'instances':
                $actTemplate->loadTemplateFile('Instances.html');
                
                $instRepo = new \Cx\Core_Modules\MultiSite\Model\Repository\InstanceRepository();
                $instances = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($instRepo->findAll('/var/www'));
                $actTemplate->setVariable(array(
                    'TABLE' => (new \BackendTable($instances))->toHtml(),
                ));
                break;
                
            case 'new':
                $actTemplate->loadTemplateFile('New.html');
                break;
                
            case 'settings':
                $actTemplate->loadTemplateFile('Settings.html');
                break;
        
		/* EVERYTHING BELOW HERE SHOULD BE DONE IN SystemComponentController */
		
		}
        
        // set tabs
        $navigation = new \Cx\Core\Html\Sigma(ASCMS_CORE_MODULE_PATH . '/MultiSite/View/Template/Backend');
        $navigation->loadTemplateFile('Navigation.html');
        foreach ($navEntries as $href=>$title) {
            $navigation->setVariable(array(
                'HREF' => $href,
                'TITLE' => $title,
            ));
            if (strtolower($title) == $act) {
                $navigation->touchBlock('active');
            }
            $navigation->parse('tab_entry');
        }
		
		$objTemplate->setVariable('ADMIN_CONTENT', $actTemplate->get());
        $objTemplate->setVariable('CONTENT_NAVIGATION', $navigation->get());
	}
	
	/**
	 * Do something after a module is loaded
	 * This method is called only if any module
	 * gets loaded for content parsing
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param string                                    $content    The parsed content
	 */
	public function postContentParse(\Cx\Core\Cx $cx, &$content) {
		
	}
	
	/**
	 * Do something before content is loaded from DB
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param string                                    $content    The parsed content
	 */
	public function postContentLoad(\Cx\Core\Cx $cx, &$content) {
		
	}
	
	/**
	 * Do something before main template gets parsed
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * CALCULATE YOUR STUFF AS LATE AS POSSIBLE
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 * @param \Cx\Core\Html\Sigma                       $template   The main template
	 */
	public function preFinalize(\Cx\Core\Cx $cx, \Cx\Core\Html\Sigma $template) {
		
	}
	
	/**
	 * Do something after main template got parsed
	 * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
	 * @param \Cx\Core\Cx                               $cx         The Contrexx main class
	 */
	public function postFinalize(\Cx\Core\Cx $cx) {
		
	}
}
