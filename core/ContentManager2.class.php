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

require ASCMS_CORE_PATH.'/BackendTable.class.php';

class ContentManager {
	
	var $em = null;
    protected $act = '';
    protected $template = null;

	public function __construct($act, $template) {
		$this->em = Env::em();
        $this->act = $act;
        $this->template = $template;
	}

    public function getPage() {
        if($this->act == '') {
            $this->renderCM();
            return;
        }

        //prevent execution of non-act methods.
        if(substr($this->act, 0, 3) != 'act') {
            die('acts start with "act", "' . $this->act . '" given');
        }

        //call the right act.
        $act = $this->act;
        if(method_exists($this, $act))
            $this->$act();
        else
            die('unknown act: "' . $this->act . '"');
    }

	protected function renderCM() {
        JS::activate('cx');
        JS::activate('ckeditor');
        JS::activate('jqueryui');
        JS::activate('jstree');
        JS::activate('chosen');

		$this->template->addBlockfile('ADMIN_CONTENT', 'content_manager', 'content_manager.html');
		$this->template->touchBlock('content_manager');
		$this->template->addBlockfile('CONTENT_MANAGER_MEAT', 'content_manager_meat', 'cm.html');
		$this->template->touchBlock('content_manager_meat');

        $this->setLanguageVars(array(
            //langs
            'TXT_CORE_GERMAN', 'TXT_CORE_FRENCH', 'TXT_CORE_ENGLISH',
            //categories
            'TXT_CORE_SITE_TYPE', 'TXT_CORE_SITE_CONTENT', 'TXT_CORE_SITE_ACCESS', 'TXT_CORE_SITE_SETTINGS', 'TXT_CORE_SITE_HISTORY',
            //type tab
            'TXT_CORE_TYPE_EXPLANATION', 'TXT_CORE_TYPE_CONTENT', 'TXT_CORE_TYPE_CONTENT_DESCRIPTION', 'TXT_CORE_TYPE_REDIRECTION', 'TXT_CORE_TYPE_REDIRECTION_DESCRIPTION', 'TXT_CORE_TYPE_APP', 'TXT_CORE_TYPE_APP_DESCRIPTION', 
            //content tab
            'TXT_CORE_ACTIVE_FROM_TO', 'TXT_CORE_ACTIVE_FROM', 'TXT_CORE_ACTIVE_TO', 'TXT_CORE_META_INFORMATION', 'TXT_CORE_META_TITLE',  'TXT_CORE_META_KWORDS', 'TXT_CORE_META_DESC', 'TXT_CORE_META_ROBOTS',
            //settings tab
            'TXT_CORE_APPLICATION_AREA', 'TXT_CORE_APPLICATION', 'TXT_CORE_AREA', 'TXT_CORE_OPTICS_STYLE', 'TXT_CORE_SKIN', 'TXT_CORE_SPECIAL_CONTENT_PAGE', 'TXT_CORE_CUSTOMCONTENT', 'TXT_CORE_REDIRECTION', 'TXT_CORE_TARGET', 'TXT_CORE_PERFORMANCE_OPTIMIZATION', 'TXT_CORE_CACHING', 'TXT_CORE_LINK', 'TXT_CORE_SLUG',
            //bottom buttons
            'TXT_CORE_PREVIEW', 'TXT_CORE_SAVE_PUBLISH', 'TXT_CORE_SAVE'
        ));

        // TODO: move including of add'l JS dependencies to cx obj from /cadmin/index.html
        $this->template->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());
	}

    protected function setLanguageVars($ids) {
        global $_CORELANG;
        foreach($ids as $id) {
            $this->template->setVariable($id, $_CORELANG[$id]);
        }
    }

    protected function actAjaxGetHistoryTable($template) {
        $table = new BackendTable();       
    }

}

?>
