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

	function __construct() {
		$this->em = Env::em();
	}

	function renderCM() {
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

        // TODO: move including of add'l JS dependencies to cx obj from /cadmin/index.html
        $objTemplate->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());
	}

}

?>
