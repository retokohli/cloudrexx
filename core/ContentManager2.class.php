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
require ASCMS_CORE_PATH.'/Module.class.php';

class ContentManagerException extends ModuleException {}

class ContentManager extends Module {
	var $em = null;
    protected $act = '';
    protected $template = null;
    protected $pageRepository = null;

	public function __construct($act, $template) {
        parent::__construct($act, $template);
		$this->em = Env::em();
        $this->pageRepository = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->defaultAct = 'actRenderCM';
	}

	protected function actRenderCM() {
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

    protected function actAjaxGetHistoryTable() {       
        if(!isset($_GET['pageId']))
            throw new ContentManagerException('please provide a pageId');

        $id = $_GET['pageId'];
        $page = $this->pageRepository->findOneById($id);

        if(!$page) {
            throw new ContentManagerException("could not find page with id $id");
        }

        $table = new BackendTable(array('border' => '1'));
        $table->setAutoGrow(true);

        $table->setHeaderContents(0,0,'Version');
        $table->setHeaderContents(0,1,'Date');
        $table->setHeaderContents(0,2,'Title');
        $table->setHeaderContents(0,3,'Author');
        
        $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $logRepo->getLogEntries($page);
        
        $this->addHistoryEntries($page, $table, 1, 'current');

        for($i = 0 ; $i < count($logs); $i++) {
            $logRepo->revert($page, $i+1);
            $this->addHistoryEntries($page, $table, $i+2, $i+1);
        }
       
        die($table->toHtml());
    }

    protected function addHistoryEntries($page, $table, $row, $version) {
        $table->setCellContents($row, 0, $version);
        $table->setCellContents($row, 1, $page->getUpdatedAt()->format(ASCMS_DATE_FORMAT));
        $table->setCellContents($row, 2, $page->getTitle());
        $table->setCellContents($row, 3, $page->getUsername());
        $table->setCellContents($row, 4, '<a href="javascript:revert('.$page->getId().','.$version.')">revert to this version</a>');
    }

    protected function actAjaxRevert() {       
        if(!isset($_POST['pageId']))
            throw new ContentManagerException('please provide a pageId');
        if(!isset($_POST['version']))
            throw new ContentManagerException('please provide a version you want to revert to');

        $id = $_POST['pageId'];
        $version = $_POST['version'];
        $page = $this->pageRepository->findOneById($id);

        if(!$page) {
            throw new ContentManagerException("could not find page with id $id");
        }
       
        $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        
        $logRepo->revert($page, $version);

        $this->em->persist($page);
        $this->em->flush();
    }
}
?>