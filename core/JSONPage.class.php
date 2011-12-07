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

    public function set() {
        $page = $_POST['page'];

        if (intval($page['id']) > 0) {
            // store the updated page
$updated_page = array_map('contrexx_input2raw', $_POST['page']);

$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

$page = $pageRepo->find($updated_page['id']);

if ($updated_page['type']) {
    $page->setType($updated_page['type']);
    $page->setUpdatedAtToNow();
    $page->setTitle($updated_page['name']);
    $page->setContentTitle($updated_page['title']);
    try {
        $start = new DateTime($updated_page['start'], $this->tz);
        $end = new DateTime($updated_page['end'], $this->tz);
    } catch (Exception $e) {
        $start = new DateTime('0000-00-00 00:00', $this->tz);
        $end = new DateTime('0000-00-00 00:00', $this->tz);
    }
    $page->setStart($start);
    $page->setEnd($end);
    $page->setMetatitle($updated_page['metatitle']);
    $page->setMetakeys($updated_page['metakeys']);
    $page->setMetadesc($updated_page['metadesc']);
    $page->setMetarobots($updated_page['metarobots']);
    $page->setContent(preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $updated_page['content']));
    $page->setSourceMode($updated_page['sourceMode'] == 'on' ? true : false);
    $page->setModule($updated_page['application']);
    if ($updated_page['area'] == '') { 
        $updated_page['area'] = null;
    }
    $page->setCmd($updated_page['area']);
    $page->setTarget($updated_page['target']);
    $page->setSlug($updated_page['slug']);
    $page->setCaching((bool) $updated_page['caching']);

    $skin = $updated_page['skin'];
    if(!$skin)
        $skin = null;
    $page->setSkin($skin);
    $page->setCustomContent($updated_page['customContent']);
    $page->setCssName($updated_page['cssName']);
    $page->setCssNavName($updated_page['cssNavName']);
}
elseif ($updated_page['status']) {
    $page->setStatus($updated_page['status']);
}

//$page->updateFromArray($_POST['page']);

$this->em->persist($page);
$this->em->flush();

DoctrineDebug::dump($page);
die();

        }
        elseif ($page['id'] == 0 && $page['node'] && $page['lang']) {
            // translate another page
        }
        else {
            // create a new node/page combination
        }
    }

/*
elseif ($_POST['page']['id'] == 'new' || $_POST['page']['id'] == 0) {
    		$nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
      		$pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

            if ($_POST['page']['node']) {
                $node = $nodeRepo->find($_POST['page']['node']);                
            }

            $updated_page = array_map('contrexx_input2raw', $_POST['page']);

            if (!$node) {
                $node = new \Cx\Model\ContentManager\Node();
                $node->setParent($nodeRepo->getRoot());

                $this->em->persist($node);

                $page = new \Cx\Model\ContentManager\Page();
                $page->setNode($node);
            }
            else {
                foreach ($node->getPages() as $pageCandidate) {
                    $source_page = $pageCandidate;
                    if ($source_page->getLang() == FWLanguage::getLanguageIdByCode($_GET['page']['lang'])) break;
                }
                $page = $pageRepo->translate($source_page, FWLanguage::getLanguageIdByCode($updated_page['lang']), true, true, true);
            }

            $page->setType($updated_page['type'] ? $updated_page['type'] : 'fallback');

            $page->setUpdatedAtToNow();
            $page->setLang(FWLanguage::getLanguageIdByCode($updated_page['lang']));
            $page->setUsername(
            $page->setStart(null);
            $page->setEnd(null);
            $page->setContentTitle($updated_page['title']);
            $page->setTitle($updated_page['name']);
            $page->setMetatitle($updated_page['metatitle']);
            $page->setMetakeys($updated_page['metakeys']);
            $page->setMetadesc($updated_page['metadesc']);
            $page->setMetarobots($updated_page['metarobots']);
            $page->setContent(preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $updated_page['content']));
            $page->setSourceMode($updated_page['sourceMode'] == 'on' ? true : false);
            $page->setModule($updated_page['application']);
            $page->setCmd($updated_page['area']);
            $page->setTarget($updated_page['target']);

            $page->setCaching((bool) $updated_page['caching']);

            $skin = $updated_page['skin'];
            if(!$skin)
                $skin = null;
            $page->setSkin($skin);
            $page->setCustomContent($updated_page['customContent']);
            $page->setCssName($updated_page['cssName']);
            $page->setCssNavName($updated_page['cssNavName']);

            if (strlen($updated_page['slug']) > 0) {
                $page->setSlug($updated_page['slug']);
            }

            $this->em->persist($page);
            $this->em->flush();

            die('new');
        }
*/

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
            'id'                    => 0,
            'lang'                  => $lang,
            'node'                  => $node->getId(),
            'type'                  => ($this->fallbacks[$lang] ? 'fallback' : 'content'),
            // Page Tab
            'name'                  => $page->getTitle(),
            'title'                 => $page->getContentTitle(),
            // Metadata
            'metatitle'             => $page->getMetatitle(),
            'metadesc'              => $page->getMetadesc(),
            'metakeys'              => $page->getMetakeys(),
            'metarobots'            => $page->getMetarobots(),
            // Access Permissions
            'frontend_protection'   => $page->isFrontendProtected(),
            'backend_protection'    => $page->isBackendProtected(),
            'accessData'            => $accessData,
            // Advanced Settings
            'slug'                  => $page->getSlug(),
            'sourceMode'            => false,
            'sourceMode'            =>  $page->getSourceMode(),
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
            'id'                    => $page->getId(),
            'lang'                  => FWLanguage::getLanguageCodeById($page->getLang()),
            'node'                  => $page->getNode()->getId(),
            // Page Tab
            'name'                  => $page->getTitle(),
            'title'                 => $page->getContentTitle(),
            'type'                  => $page->getType(),
            'target'                => $page->getTarget(),
            'module'                => $page->getModule(),
            'area'                  => $page->getCmd(),
            'scheduled_publishing'  => $scheduled_publishing,
            'start'                 => $start,
            'end'                   => $end,
            'content'               => preg_replace('/{([A-Z0-9_-]+)}/', '[[\\1]]', $page->getContent()),
            'sourceMode'            => $page->getSourceMode(),
            // Metadata
            'metatitle'             => $page->getMetatitle(),
            'metadesc'              => $page->getMetadesc(),
            'metakeys'              => $page->getMetakeys(),
            'metarobots'            => $page->getMetarobots(),
            // Access Permissions
            'frontend_protection'   => $page->isFrontendProtected(),
            'backend_protection'    => $page->isBackendProtected(),
            'accessData'            => $accessData,
            // Advanced Settings
            'skin'                  => $page->getSkin(),
            'customContent'         => $page->getCustomContent(),
            'cssName'               => $page->getCssName(),
            'cssNavName'            => $page->getCssNavName(),
            'caching'               => $page->getCaching(),
            'linkTarget'            => $page->getLinkTarget(),
            'slug'                  => $page->getSlug(),
            
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
