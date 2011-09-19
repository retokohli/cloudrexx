<?php

/**
 * Content Manager 2 (Doctrine-based version)
 *
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

class ContentManager {
	
	var $em = null;

	public function __construct() {
		$this->em = Env::em();
	}

	public function renderCM() {
        JS::activate('cx');
        JS::activate('ckeditor');
        JS::activate('jqueryui');
        JS::activate('jstree');
        JS::activate('chosen');

        // Render the Content Manager within our old backend template.
		global $objTemplate;

		$objTemplate->addBlockfile('ADMIN_CONTENT', 'content_manager', 'content_manager.html');
		$objTemplate->touchBlock('content_manager');
		$objTemplate->addBlockfile('CONTENT_MANAGER_MEAT', 'content_manager_meat', 'cm.html');
		$objTemplate->touchBlock('content_manager_meat');

        $this->setLanguageVars(array(
            //langs
            'TXT_GERMAN', 'TXT_FRENCH', 'TXT_ENGLISH',
            //categories
            'TXT_SITE_TYPE', 'TXT_SITE_CONTENT', 'TXT_SITE_ACCESS', 'TXT_SITE_SETTINGS', 'TXT_SITE_HISTORY',
            //type tab
            'TXT_TYPE_EXPLANATION', 'TXT_TYPE_CONTENT', 'TXT_TYPE_CONTENT_DESCRIPTION', 'TXT_TYPE_REDIRECTION', 'TXT_TYPE_REDIRECTION_DESCRIPTION', 'TXT_TYPE_APP', 'TXT_TYPE_APP_DESCRIPTION', 
            //content tab
            'TXT_ACTIVE_FROM_TO', 'TXT_ACTIVE_FROM', 'TXT_ACTIVE_TO', 'TXT_META_INFORMATION', 'TXT_META_TITLE',  'TXT_META_KWORDS', 'TXT_META_DESC', 'TXT_META_ROBOTS',
            //settings tab
            'TXT_MODULE_CMD', 'TXT_MODULE', 'TXT_CMD', 'TXT_OPTICS_STYLE', 'TXT_SKIN', 'TXT_SPECIAL_CONTENT_PAGE', 'TXT_CUSTOMCONTENT', 'TXT_REDIRECTION', 'TXT_TARGET', 'TXT_PERFORMANCE_OPTIMIZATION', 'TXT_CACHING', 'TXT_LINK', 'TXT_SLUG',
            //bottom buttons
            'TXT_PREVIEW', 'TXT_SAVE_PUBLISH', 'TXT_SAVE'
        ));

        // TODO: move including of add'l JS dependencies to cx obj from /cadmin/index.html
        $objTemplate->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());
	}

    protected function setLanguageVars($ids) {
        global $_CORELANG;
        global $objTemplate;
        foreach($ids as $id) {
            $objTemplate->setVariable($id, $_CORELANG[$id]);
        }
    }

}

?>
