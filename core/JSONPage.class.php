<?php

/**
 * JSON Export for the node structure
 *
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

class JSONPage {
	
	var $em = null;
    var $fallbacks;

	function __construct() {
		$this->em = Env::em();
        $this->tz = new DateTimeZone('Europe/Berlin');

        $fallback_lang_codes = FWLanguage::getFallbackLanguageArray();
        $active_langs = FWLanguage::getActiveFrontendLanguages();

        // get all active languages and their fallbacks
        foreach ($active_langs as $lang) {
            $this->fallbacks[FWLanguage::getLanguageCodeById($lang['id'])] = ((array_key_exists($lang['id'], $fallback_lang_codes)) ? FWLanguage::getLanguageCodeById($fallback_lang_codes[$lang['id']]) : null);
        }
	}

    public function get() {
        $pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

        // pages can be requested in two ways:
        // by page id               - default for existing pages
        // by node id + lang        - to translate an existing page into a new language, assigned to the same node
        if (isset($_GET['page']) && $_GET['page'] != 0) {
            $page = $pageRepo->find($_GET['page']);
        }

        if (isset($page)) {
            $pageArray = $this->getPageArray($page);
        }
        elseif (isset($_GET['node']) && isset($_GET['lang'])) {
            $node = $nodeRepo->find($_GET['node']);
            $pageArray = $this->getFallbackPageArray($node, $_GET['lang']);
        }
        else {
            echo 'cannot find that page';
        }

        return json_encode($pageArray);
    }

    public function store($args) {

    }

    private function getFallbackPageArray($node, $lang) {
        foreach ($node->getPages() as $pageCandidate) {
            $page = $pageCandidate;
            if ($page->getLang() == FWLanguage::getLanguageIdByCode($this->fallbacks[$lang])) break;
        }

        // Access Permissions
        $pg = Env::get('pageguard');
        $accessData = array();
        $accessData['frontend'] = array('groups' => $pg->getGroups(true), 'assignedGroups' => $pg->getAssignedGroupIds($page, true));
        $accessData['backend'] = array('groups' => $pg->getGroups(false), 'assignedGroups' => $pg->getAssignedGroupIds($page, false));

        $pageArray = array(
            // Editor Meta
            'id'            => 0,
            'lang'          => $lang,
            'node'          => $node->getId(),
            'type'          => ($this->fallbacks[$lang] ? 'fallback' : 'content'),
            // Page Tab
            'name'          => $page->getTitle(),
            'title'         => $page->getContentTitle(),
            // Metadata
            'metatitle'     =>  $page->getMetatitle(),
            'metadesc'      =>  $page->getMetadesc(),
            'metakeys'      =>  $page->getMetakeys(),
            'metarobots'    =>  $page->getMetarobots(),
            // Access Permissions
            'frontend_protection'    => $page->isFrontendProtected(),
            'backend_protection'     => $page->isBackendProtected(),
            'accessData'    =>  $accessData,
            // Advanced Settings
            'slug'          =>  $page->getSlug(),
        );

        return $pageArray;
    }

    private function getPageArray($page) {
        // Scheduled Publishing
        $n = new DateTime(null, $this->tz);
        if ($page->getStart() && $page->getEnd()) {
            $scheduled_publishing = true;
            $start = $page->getStart()->format('d.m.Y H:i');
            $end = $page->getEnd()->format('d.m.Y H:i');
        }
        else {
            $scheduled_publishing = false;
            $start = $n->format('d.m.Y H:i');
            $end = $n->format('d.m.Y H:i');
        }

        // Access Permissions
        $pg = Env::get('pageguard');
        $accessData = array();
        $accessData['frontend'] = array('groups' => $pg->getGroups(true), 'assignedGroups' => $pg->getAssignedGroupIds($page, true));
        $accessData['backend'] = array('groups' => $pg->getGroups(false), 'assignedGroups' => $pg->getAssignedGroupIds($page, false));

        $pageArray = array(
            // Editor Meta
            'id'            =>  $page->getId(),
            'lang'          =>  FWLanguage::getLanguageCodeById($page->getLang()),
            'node'          =>  $page->getNode()->getId(),
            // Page Tab
            'name'          =>  $page->getTitle(),
            'title'         =>  $page->getContentTitle(),
            'type'          =>  $page->getType(),
            'target'        =>  $page->getTarget(),
            'module'        =>  $page->getModule(),
            'area'          =>  $page->getCmd(),
            'scheduled_publishing'  =>  $scheduled_publishing,
            'start'         =>  $start,
            'end'           =>  $end,
            'content'       =>  preg_replace('/{([A-Z0-9_-]+)}/', '[[\\1]]', $page->getContent()),
            'sourceMode'    =>  $page->getSourceMode(),
            // Metadata
            'metatitle'     =>  $page->getMetatitle(),
            'metadesc'      =>  $page->getMetadesc(),
            'metakeys'      =>  $page->getMetakeys(),
            'metarobots'    =>  $page->getMetarobots(),
            // Access Permissions
            'frontend_protection'    => $page->isFrontendProtected(),
            'backend_protection'     => $page->isBackendProtected(),
            'accessData'    =>  $accessData,
            // Advanced Settings
            'skin'          =>  $page->getSkin(),
            'customContent' =>  $page->getCustomContent(),
            'cssName'       =>  $page->getCssName(),
            'caching'       =>  $page->getCaching(),
            'linkTarget'    => $page->getLinkTarget(),
            'slug'          =>  $page->getSlug(),
            
            /*'editingStatus' =>  $page->getEditingStatus(),
            'display'       =>  $page->getDisplay(),
            'active'        =>  $page->getActive(),
            'user'          =>  $page->getUser(),
            'username'      =>  $page->getUsername(),
            'updatedAt'     =>  $page->getUpdatedAt(),*/
        );

        return $pageArray;
    }

}
?>
