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
    public $messages;

    function __construct() {
        $this->em = Env::em();
        $this->messages = array();
        $this->tz = new DateTimeZone('Europe/Berlin');

        $fallback_lang_codes = FWLanguage::getFallbackLanguageArray();
        $active_langs = FWLanguage::getActiveFrontendLanguages();

        // get all active languages and their fallbacks
        foreach ($active_langs as $lang) {
            $this->fallbacks[FWLanguage::getLanguageCodeById($lang['id'])] = ((array_key_exists($lang['id'], $fallback_lang_codes)) ? FWLanguage::getLanguageCodeById($fallback_lang_codes[$lang['id']]) : null);
        }
    }

    public function getMessagesAsString() {
        return implode("<br />", $this->messages);
    }

    public function get($params) {
        $pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');

        // pages can be requested in two ways:
        // by page id               - default for existing pages
        // by node id + lang        - to translate an existing page into a new language, assigned to the same node
        if (isset($_GET['page']) && $_GET['page'] != 0) {
            $page = $pageRepo->find($_GET['page']);
        }

        if (isset($page)) {
            // All is well, continue
        }
        elseif (isset($_GET['node']) && isset($_GET['lang'])) {
            $node = $nodeRepo->find($_GET['node']);
            $pageArray = $this->getFallbackPageArray($node, $_GET['lang']);
        }
        else {
            throw new Exception('cannot find that page');
        }

        // load an older revision if asked to do so:
        if (isset($params['get']) && isset($params['get']['history'])) {
            $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');

            $logRepo->revert($page, $params['get']['history']);
        }
        // load the draft revision if one is available and we're not loading historic data:
        elseif ($page->getEditingStatus() == 'hasDraft' || $page->getEditingStatus() == 'hasDraftWaiting') {
            $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');

            $availableRevisions = $logRepo->getLogEntries($page);
            $logRepo->revert($page, $availableRevisions[1]->getVersion());
        }
        
        $pageArray = $this->getPageArray($page);

        return $pageArray;
    }

    public function set($params) {
        global $objFWUser, $_CORELANG;
        $newpage = false;
        $pg = Env::get('pageguard');

        $nodeRepo = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $pageRepo = $this->em->getRepository('Cx\Model\ContentManager\Page');

        $page = $params['post']['page'];

        $fields = array(
                        'type'                     => array('type' => 'String'),
                        'name'                     => array('type' => 'String', 'map_to' => 'title'),
                        'title'                    => array('type' => 'String', 'map_to' => 'contentTitle'),
                        // the model can take advantage of proper NULLing, so this needn't be set
                        //                  'scheduled_publishing' => array('type' => 'boolean'),
                        'start'                    => array('type' => 'DateTime', 'require' => 'scheduled_publishing'),
                        'end'                      => array('type' => 'DateTime', 'require' => 'scheduled_publishing'),
                        'metatitle'                => array('type' => 'String'),
                        'metakeys'                 => array('type' => 'String'),
                        'metadesc'                 => array('type' => 'String'),
                        'metarobots'               => array('type' => 'boolean'),
                        'content'                  => array('type' => 'String'),
                        'sourceMode'               => array('type' => 'boolean'),
                        'protection_frontend'      => array('type' => 'boolean', 'map_to' => 'frontendProtection'),
                        'protection_backend'       => array('type' => 'boolean', 'map_to' => 'backendProtection'),
                        'application'              => array('type' => 'String', 'map_to' => 'module'),
                        'area'                     => array('type' => 'String', 'map_to' => 'cmd'),
                        'target'                   => array('type' => 'String'),
                        'link_target'              => array('type' => 'String', 'map_to' => 'linkTarget'),
                        'slug'                     => array('type' => 'String'),
                        'caching'                  => array('type' => 'boolean'),
                        'skin'                     => array('type' => 'integer'),
                        'customContent'            => array('type' => 'String'),
                        'cssName'                  => array('type' => 'String'),
                        'cssNavName'               => array('type' => 'String'),
                        );

        $output = array();

        foreach($fields as $field => $meta) {
            $target = isset($meta['map_to']) ? $meta['map_to'] : $field;

            if ($meta['type'] == 'boolean') {
                // checkboxes and radiobuttons by default aren't submitted unless checked or
                // selected. in cm.html they are prefixed with an input type=hidden value=off, so 
                // we always get a value
                // this is required for Page#updateFromArray to work.
                if ($page[$field] == "on")  $value = true;
                if ($page[$field] == "off") $value = false;
            }

            if ($meta['type'] == 'DateTime') {
                try {
                    $value = new DateTime($page[$field], $this->tz);
                }
                catch (Exception $e) {
                    $value = new DateTime('0000-00-00 00:00', $this->tz);
                }
            }

            if ($meta['type'] == 'integer') {
                $value = intval($page[$field]);
            }

            if ($meta['type'] == 'String') {
                $value = $page[$field];
            }

            if (isset($meta['require']) && !$page[$meta['require']]) {
                $value = null;
            }

            $output[$target] = $value;
        }

        //TODO: should we allow filter/callback fns in field processing above?
        $output['content'] = preg_replace('/\\[\\[([A-Z0-9_-]+)\\]\\]/', '{\\1}', $output['content']);

        if (intval($page['id']) > 0) {
            // if we got a page id, the page already exists and can be updated
            $page = $pageRepo->find($params['post']['page']['id']);
            $node = $page->getNode();

        }
        elseif ($page['id'] == 0 && $page['node'] && $page['lang']) {
            // we are translating another page (to $page['lang'])
            $node = $nodeRepo->find($page['node']);

            foreach ($node->getPages() as $pageCandidate) {
                $source_page = $pageCandidate;
                if ($source_page->getLang() == FWLanguage::getLanguageIdByCode($params['post']['page']['lang'])) break;
            }
            $page = $pageRepo->translate($source_page, FWLanguage::getLanguageIdByCode($page['lang']), true, true, true);

            $reload = true;
        } 
        else {
            // create a new node/page combination
            $node = new \Cx\Model\ContentManager\Node();
            $node->setParent($nodeRepo->getRoot());

            $this->em->persist($node);
            $this->em->flush();

            $page = new \Cx\Model\ContentManager\Page();
            $page->setNode($node);
            $page->setNodeIdShadowed($node->getId());
            $page->setLang(FWLanguage::getLanguageIdByCode($params['post']['page']['lang']));

            $newpage = true;
            $reload = true;
        }

        $page->updateFromArray($output);
        $page->setUsername($objFWUser->objUser->getUsername());
        $page->setUpdatedAtToNow();

        if (isset($params['get']['publish']) && $params['get']['publish']) {
            $page->setActive(true);
        }

        $page->validate();

        if ($page->isFrontendProtected() && isset($params['post']['frontendGroups'])) {
            $pg->setAssignedGroupIds($page, $params['post']['frontendGroups'], true);
        }
        if ($page->isBackendProtected() && isset($params['post']['backendGroups'])) {
            $pg->setAssignedGroupIds($page, $params['post']['backendGroups'], false);
        }

        if ((isset($params['get']['publish']) && $params['get']['publish']) 
            && \Permission::checkAccess(78, 'static', true)) {
            // user w/permission clicked save&publish. we should either publish the page or submit the draft for approval
            if ($page->getEditingStatus() == 'hasDraftWaiting') {
                $reload = true;
            }
            $page->setEditingStatus('');
            $this->messages[] = $_CORELANG['TXT_CORE_SAVED'];
            // TODO: define what log data we want to keep in a case like this.
            //       make adjustments, if necessary.
        }
        else {
            // user clicked save [as draft], so let's do that
            if ($newpage) {
                $this->em->persist($page);
                $this->em->flush();
            }
            $updatingDraft = $page->getEditingStatus() != '' ? true : false;

            if (isset($params['get']['publish']) && $params['get']['publish']) {
                // user w/o publish permission clicked save&publish. submit it as a draft
                $page->setEditingStatus('hasDraftWaiting');
                $this->messages[] = $_CORELANG['TXT_CORE_DRAFT_SUBMITTED'];
            }
            else {
                if ($page->getEditingStatus() == 'hasDraftWaiting' && \Permission::checkAccess(78, 'static', true)) {
                    $reload = true;
                }
                $page->setEditingStatus('hasDraft');
                $this->messages[] = $_CORELANG['TXT_CORE_SAVED_AS_DRAFT'];
            }

            $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
            // gedmo-loggable generates a LogEntry (i.e. revision) on persist, so we'll have to 
            // store the draft first, then revert the current version to what it previously was.
            // in the end, we'll have the current [published] version properly stored as a page
            // and the draft version stored as a gedmo LogEntry

            $this->em->persist($page);
            // gedmo hooks in on persist/flush, so we unfortunately need to flush our em in
            // order to get a clean set of logEntries
            $this->em->flush();
            $logEntries = $logRepo->getLogEntries($page);
            // $logEntries holds an array of Gedmo LogEntries, the most recent one listed first

            // we need the editing status of the page
            $logData = $logEntries[1]->getData();
            $logData['editingStatus'] = $page->getEditingStatus();
            $logEntries[1]->setData($logData);
            
            // revert to the published version
            $logRepo->revert($page, $logEntries[1]->getVersion());
            $this->em->persist($page);

            // gedmo auto-logs slightly too much data. clean up unnecessary revisions:
            if ($updatingDraft) {
                $this->em->flush();

                $logEntries = $logRepo->getLogEntries($page);
                $this->em->remove($logEntries[2]);
                $this->em->remove($logEntries[3]);
            }
        }

        $this->em->persist($page);
        $this->em->flush();
        
        // only users with publish rights can create aliases
        if (\Permission::checkAccess(78, 'static', true)) {
            // aliases are updated after persist!
            $data['alias'] = $params['post']['page']['alias'];
            $aliasses = $page->getAliasses();
            $page->updateFromArray($data);
            if ($aliasses != $page->getAliasses()) {
                $reload = true;
            }
        } else {
            $this->messages[] = $_CORELANG['TXT_CORE_ALIAS_CREATION_DENIED'];
        }

        if (isset($reload) && $reload) {
            return array(
                'reload' => 'true', 
                'id'     => $page->getId()
            );
        }
    }

    function getAccessData($page = null) {
        // TODO: add functionality for $page!=null (see below), DRY up #getFallbackPageArray

        $pg = Env::get('pageguard');

        $accessData = array();

        $accessData['frontend'] = array('groups' => $pg->getGroups(true), 'assignedGroups' => array());
        $accessData['backend'] = array('groups' => $pg->getGroups(false), 'assignedGroups' => array());

        return $accessData;
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
                           'sourceMode'            => $page->getSourceMode(),
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
                           'aliasses'              => $this->getAliasArray($page),
                           'editingStatus'         => $page->getEditingStatus(),
            
                           /*'display'       =>  $page->getDisplay(),
                             'active'        =>  $page->getActive(),
                             'user'          =>  $page->getUser(),
                             'username'      =>  $page->getUsername(),
                             'updatedAt'     =>  $page->getUpdatedAt(),*/
                           );

        return $pageArray;
    }

    /**
     * Returns an array of alias slugs
     * @param Cx\Model\ContentManager\Page $page Page to get the aliasses of
     * @return Array<String>
     */
    private function getAliasArray($page)
    {
        $pages = $page->getAliasses();
        $aliasses = array();
        foreach ($pages as $alias) {
            $aliasses[] = $alias->getSlug();
        }
        return $aliasses;
    }
}
?>
