<?php
namespace Cx\Update\Cx_3_0_1;

set_time_limit(0);

class ContentMigration
{
    public $langs;
    public $similarPages;
    public static $defaultLang;
    protected $db;
    protected $nodeArr = array();
    protected $moduleNames = array();
    protected $availableFrontendLanguages = array();
    protected static $em;
    
    public function __construct()
    {
        $this->db = \Env::get('db');
        
        if (\DBG::getMode() & DBG_ADODB_TRACE) {
            \DBG::enable_adodb_debug(true);
        } elseif (\DBG::getMode() & DBG_ADODB || \DBG::getMode() & DBG_ADODB_ERROR) {
            \DBG::enable_adodb_debug();
        } else {
            \DBG::disable_adodb_debug();
        }
        
        self::$em = \Env::em();
        self::$defaultLang = \FWLanguage::getDefaultLangId();
        
        $this->initModuleNames();
    }

    private function initModuleNames()
    {
        $this->moduleNames = array();

        $objModules = $this->db->Execute('SELECT `id`, `name` FROM `'.DBPREFIX.'modules`');
        while (!$objModules->EOF) {
            $this->moduleNames[$objModules->fields['id']] = $objModules->fields['name'];
            $objModules->MoveNext();
        }
    }

    protected function getSortedNodes($objResult, &$visiblePageIDs) {
        $arrSortedNodes = array();
        
        while (!$objResult->EOF) {
            $catId = $objResult->fields['catid'];
            
            // Skip ghosts
            if (!in_array($catId, $visiblePageIDs)) {
                $objResult->MoveNext();
                continue;
            }

            //skip existing nodes
            if(isset($this->nodeArr[$catId])) {
                $arrSortedNodes[] = $this->nodeArr[$catId];
            } else {
                $this->nodeArr[$catId] = new \Cx\Model\ContentManager\Node();
                $arrSortedNodes[] = $this->nodeArr[$catId];
            }

            $objResult->MoveNext();
        }
        
        return array_reverse($arrSortedNodes);
    }
   
    public function migrate()
    {
        if (isset($_POST['doGroup']) && $_POST['doGroup']) {
            return false;
        }
        
        $this->db->Execute('
            CREATE TABLE IF NOT EXISTS `contrexx_content_node` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `parent_id` int(11) DEFAULT NULL,
              `lft` int(11) NOT NULL,
              `rgt` int(11) NOT NULL,
              `lvl` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_E5A18FDD727ACA70` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');
        $this->db->Execute('
            CREATE TABLE IF NOT EXISTS `contrexx_content_page` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `node_id` int(11) DEFAULT NULL,
              `nodeIdShadowed` int(11) DEFAULT NULL,
              `lang` int(11) NOT NULL,
              `type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
              `caching` tinyint(1) NOT NULL,
              `updatedAt` timestamp NULL DEFAULT NULL,
              `updatedBy` char(40) COLLATE utf8_unicode_ci NOT NULL,
              `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `linkTarget` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
              `contentTitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `content` longtext COLLATE utf8_unicode_ci NOT NULL,
              `sourceMode` tinyint(1) NOT NULL DEFAULT \'0\',
              `customContent` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
              `cssName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `cssNavName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `skin` int(11) DEFAULT NULL,
              `metatitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `metadesc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `metakeys` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `metarobots` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
              `start` timestamp NULL DEFAULT NULL,
              `end` timestamp NULL DEFAULT NULL,
              `editingStatus` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
              `protection` int(11) NOT NULL,
              `frontendAccessId` int(11) NOT NULL,
              `backendAccessId` int(11) NOT NULL,
              `display` tinyint(1) NOT NULL,
              `active` tinyint(1) NOT NULL,
              `target` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `module` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `cmd` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',
              PRIMARY KEY (`id`),
              UNIQUE KEY `node_id` (`node_id`,`lang`),
              KEY `IDX_D8E86F54460D9FD7` (`node_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');
        $this->db->Execute('
            CREATE TABLE IF NOT EXISTS `contrexx_log_entry` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `action` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
              `logged_at` timestamp NULL DEFAULT NULL,
              `version` int(11) NOT NULL,
              `object_id` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
              `object_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `data` longtext COLLATE utf8_unicode_ci,
              `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `log_class_unique_version_idx` (`version`,`object_id`,`object_class`),
              KEY `log_class_lookup_idx` (`object_class`),
              KEY `log_date_lookup_idx` (`logged_at`),
              KEY `log_user_lookup_idx` (`username`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');
        
        if (empty($_SESSION['contrexx_update']['contraints_added'])) {
            $nodeConstraintAdded = $this->db->Execute('
                ALTER TABLE `contrexx_content_node`
                ADD CONSTRAINT FOREIGN KEY (`parent_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
            ');
            
            $pageConstraintAdded = $this->db->Execute('
                ALTER TABLE `contrexx_content_page`
                ADD CONSTRAINT FOREIGN KEY (`node_id`) REFERENCES `contrexx_content_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
            ');
            
            if ($nodeConstraintAdded && $pageConstraintAdded) {
                $_SESSION['contrexx_update']['contraints_added'] = true;
                if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                    return false;
                }
            }
        }
        
        $this->db->Execute('TRUNCATE TABLE `'.DBPREFIX.'content_pages`');
        $this->db->Execute('TRUNCATE TABLE `'.DBPREFIX.'content_nodes`');
        $this->db->Execute('TRUNCATE TABLE `'.DBPREFIX.'log_entry`');

        $this->nodeArr = array ();
        
        // this will be the root of the site-tree
        $root = new \Cx\Model\ContentManager\Node();
        self::$em->persist($root);
        
        // Due to a bug in the old content manager, there happened to exist ghost-pages
        // that were never visible, neither in the frontend, nor in the backend.
        // Therefore, we'll need a list of these ghost-pages so that we won't migrate them.
        // (because those ghost-pages would probably break the new site-tree)
        $visiblePageIDs = $this->getVisiblePageIDs();
        
        if (empty($_SESSION['contrexx_update']['nodes_added'])) {
            // Fetch a list of all pages that have ever existed or still do.
            // (sql-note: join content tables to prevent to migrate body-less pages)
            $objNodeResult = $this->db->Execute('
                    SELECT `catid`, `parcat`, `lang`, `displayorder`
                      FROM `'.DBPREFIX.'content_navigation_history` AS tnh
                INNER JOIN `'.DBPREFIX.'content_history` AS tch
                        ON tch.`id` = tnh.`id`
                UNION DISTINCT
                    SELECT `catid`, `parcat`, `lang`, `displayorder`
                      FROM `'.DBPREFIX.'content_navigation` AS tn
                INNER JOIN `'.DBPREFIX.'content` AS tc
                        ON tc.`id` = tn.`catid`
                  ORDER BY `parcat`, `displayorder`, `lang` ASC'
            );
            
            // Create a node for each page that ever existed or still does
            // and put them in the array $this->nodeArr
            $arrSortedNodes = $this->getSortedNodes($objNodeResult, $visiblePageIDs);
            foreach ($arrSortedNodes as $node) {
                self::$em->persist($node);
            }
            
            self::$em->flush();
            $_SESSION['contrexx_update']['nodes_added'] = true;
            
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                return false;
            }
        }
        
        $p = array();
        
        if (empty($_SESSION['contrexx_update']['history_pages_added'])) {
            // 1ST: MIGRATE PAGES FROM HISTORY
            $objResult = $this->db->Execute('
                SELECT
                    cn.page_id, cn.content, cn.title, cn.metatitle, cn.metadesc, cn.metakeys, cn.metarobots, cn.css_name, cn.redirect, cn.expertmode,
                    nav.catid, nav.parcat, nav.catname, nav.target, nav.displayorder, nav.displaystatus, nav.activestatus,
                    nav.cachingstatus, nav.username, nav.changelog, nav.cmd, nav.lang, nav.module, nav.startdate, nav.enddate, nav.protected,
                    nav.frontend_access_id, nav.backend_access_id, nav.themes_id, nav.css_name AS css_nav_name, nav.custom_content,
                    cnlog.action, cnlog.history_id, cnlog.is_validated
                FROM       `' . DBPREFIX . 'content_history` AS cn
                INNER JOIN `' . DBPREFIX . 'content_navigation_history` AS nav
                ON         cn.id = nav.id
                INNER JOIN `' . DBPREFIX . 'content_logfile` AS cnlog
                ON         cn.id = cnlog.history_id ORDER BY cnlog.id ASC
            ');
           
            // Migrate history
            if ($objResult !== false) {
                while (!$objResult->EOF) {
                    if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                        return false;
                    }
                    
                    //skip ghosts
                    if (!in_array($objResult->fields['catid'], $visiblePageIDs)) {
                        $objResult->MoveNext();
                        continue;
                    }
        
                    // TODO: create a LOST&FOUND node in case a certain parent node doesn't exist
                    // SET PARENT NODE
                    if ($objResult->fields['parcat'] == 0) {
                        // Page was located on the first level in the site-tree.
                        // Therefore, directly attach it to the ROOT-node
                        $this->nodeArr[$objResult->fields['catid']]->setParent($root);
                    } else {
                        if (!isset($this->nodeArr[$objResult->fields['parcat']])) {
                            // Parent node of page can't be found.
                            // Attach node to LOST&FOUND node
                            setUpdateMsg('Parent missing: id ' . $objResult->fields['parcat']);
                            return false;
                        }
        
                        // Attach page to associated parent node
                        $this->nodeArr[$objResult->fields['catid']]->setParent($this->nodeArr[$objResult->fields['parcat']]);
                    }
        
                    $deleted = false;
                    $page = null;
        
                    // CREATE PAGE
                    switch ($objResult->fields['action']) {
                        case 'new':
                        case 'update':
                            if (!isset($p[$objResult->fields['page_id']])) {
                                $p[$objResult->fields['page_id']] = new \Cx\Model\ContentManager\Page();
                            }
                            $page = $p[$objResult->fields['page_id']];
                            break;
                        case 'delete':
                            self::$em->remove($p[$objResult->fields['page_id']]);
                            $deleted = true;
                            break;
                    }                      
        
                    if(!$deleted) {
                        $this->_setPageRecords($objResult, $this->nodeArr[$objResult->fields['catid']], $page);
                        self::$em->persist($page);
                    }
                    
                    $objResult->MoveNext();
                }
                
                self::$em->flush();
                $_SESSION['contrexx_update']['history_pages_added'] = true;
            }
        }
        
        if (empty($_SESSION['contrexx_update']['pages_added'])) {
            // 2ND: MIGRATE CURRENT CONTENT
            $objRecords = $this->db->Execute('
                SELECT cn.id, cn.content, cn.title, cn.metatitle, cn.metadesc, cn.metakeys, cn.metarobots, cn.css_name, cn.redirect, cn.expertmode,
                       nav.catid, nav.parcat, nav.catname, nav.target, nav.displayorder, nav.displaystatus, nav.activestatus,
                       nav.cachingstatus, nav.username, nav.changelog, nav.cmd, nav.lang, nav.module, nav.startdate, nav.enddate, nav.protected,
                       nav.frontend_access_id, nav.backend_access_id, nav.themes_id, nav.css_name AS css_nav_name, nav.custom_content
                FROM       `'.DBPREFIX.'content` AS cn
                INNER JOIN `'.DBPREFIX.'content_navigation` AS nav
                ON         cn.id = nav.catid
                WHERE      cn.id
                ORDER BY   nav.parcat ASC, nav.displayorder DESC
            ');
            
            if ($objRecords !== false) {
                while (!$objRecords->EOF) {
                    if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                        return false;
                    }
                    
                    $catid = $objRecords->fields['catid'];
                    //skip ghosts
                    if(!in_array($catid, $visiblePageIDs)) {
                        $objRecords->MoveNext();
                        continue;
                    }
        
                    $debugMsg = "Migrate page ID $catid - {$objRecords->fields['catname']} (m: {$this->moduleNames[$objRecords->fields['module']]}, c: {$objRecords->fields['cmd']})";
        
                    if (!isset($this->nodeArr[$catid])) {
                        setUpdateMsg('Trying to migrate non-existing node: id ' . $catid);
                        return false;
                    }
        
                    $node = $this->nodeArr[$catid];
        
                    if ($objRecords->fields['parcat'] == 0) {
                        // Page was located on the first level in the site-tree.
                        // Therefore, directly attach it to the ROOT-node
                        //$node->setParent($root);
                        $this->nodeArr[$catid]->setParent($root);
                    } else {
                        // Attach page to associated parent node
                        //$node->setParent($this->nodeArr[$objRecords->fields['parcat']]);
                        $this->nodeArr[$catid]->setParent($this->nodeArr[$objRecords->fields['parcat']]);
                    }
        
                    // set page data
                    if (!isset($p[$catid])) {
                        $p[$catid] = new \Cx\Model\ContentManager\Page();
                    }
                    $page = $p[$catid];
        
                    $this->_setPageRecords($objRecords, $this->nodeArr[$catid], $page);
                    $page->setAlias(array('legacy_page_' . $catid));
                    
                    self::$em->persist($page);
                    $objRecords->MoveNext();
                }
                
                self::$em->flush();
                $_SESSION['contrexx_update']['pages_added'] = true;
            }
        }
        
        $nodeRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
        //$nodeRepo->recover();
        
        // Drop old tables
        /*if (empty($_SESSION['contrexx_udpate']['old_tables_dropped'])) {
            $oldTablesDropped = $this->db->Execute('
                DROP TABLE `contrexx_content`;
                DROP TABLE `contrexx_content_history`;
                DROP TABLE `contrexx_content_logfile`;
                DROP TABLE `contrexx_content_navigation`;
                DROP TABLE `contrexx_content_navigation_history`;
            ');
            
            if ($oldTablesDropped) {
                $_SESSION['contrexx_udpate']['old_tables_dropped'] = true;
            }
        }*/
        
        return true;
    }
    
    function _setPageRecords($objResult, $node, $page)
    {
        $page->setNode($node);
        $page->setNodeIdShadowed($node->getId());
        $page->setLang($objResult->fields['lang']);
        $page->setCaching($objResult->fields['cachingstatus']);
        $page->setTitle($objResult->fields['catname']);
        $page->setContentTitle($objResult->fields['title']);
        $page->setSlug($objResult->fields['catname']);
        $page->setContent($objResult->fields['content']);            
        $page->setCustomContent($objResult->fields['custom_content']);
        $page->setCssName($objResult->fields['css_name']);
        $page->setMetatitle($objResult->fields['metatitle']);
        $page->setMetadesc($objResult->fields['metadesc']);
        $page->setMetakeys($objResult->fields['metakeys']);
        $page->setMetarobots($objResult->fields['metarobots']);
        $page->setDisplay($objResult->fields['displaystatus'] === 'on' ? 1 : 0);
        $page->setActive($objResult->fields['activestatus']);
        $page->setSourceMode($objResult->fields['expertmode'] == 'y');
        $page->setUpdatedBy($objResult->fields['username']);
        $page->setCssNavName($objResult->fields['css_nav_name']);
        $page->setSkin($objResult->fields['themes_id']);
        $page->setProtection($objResult->fields['protected']);
        $page->setFrontendAccessId($objResult->fields['frontend_access_id']);
        $page->setBackendAccessId($objResult->fields['backend_access_id']);
        $page->setTarget($objResult->fields['redirect']);
        
        $updatedAt = new \DateTime();
        $updatedAt->setTimestamp($objResult->fields['changelog']);
        $page->setUpdatedAt($updatedAt);
        
        $start = new \DateTime($objResult->fields['startdate']);
        if ($start) {
            $page->setStart($start);
        }
        $end = new \DateTime($objResult->fields['enddate']);
        if ($end) {
            $page->setEnd($end);
        }

        $linkTarget = $objResult->fields['target'];
        if (!$linkTarget) {
            $linkTarget = null;
        }
        $page->setLinkTarget($linkTarget);
        
        if ($objResult->fields['module'] && isset($this->moduleNames[$objResult->fields['module']])) {
            $page->setType(\Cx\Model\ContentManager\Page::TYPE_APPLICATION);
            $page->setModule($this->moduleNames[$objResult->fields['module']]);
        }
        $page->setCmd($objResult->fields['cmd']);
        
        if ($page->getTarget()) {
            $page->setType(\Cx\Model\ContentManager\Page::TYPE_REDIRECT);
        }
    }
    
    public function migrateAliases()
    {
        // Migrate aliases
        $query = '
            SELECT `s`.`url` AS `slug`, `t`.`type`, `t`.`url` AS `target`
            FROM       `' . DBPREFIX . 'module_alias_source` AS `s`
            INNER JOIN `' . DBPREFIX . 'module_alias_target` AS `t`
            ON `s`.`target_id` = `t`.`id`
        ';
        $objResult = $this->db->Execute($query);
        
        $arrAliases = array();
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $arrAliases[$objResult->fields['target']][$objResult->fields['slug']] = $objResult->fields['type'];
                $objResult->MoveNext();
            }
        }
        
        foreach ($arrAliases as $target => $arrSlugs) {
            foreach ($arrSlugs as $slug => $type) {
                $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');
                $nodeRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
                
                if ($type === 'local') {
                    $aliasPage = $pageRepo->findOneBy(array(
                        'type' => 'alias',
                        'slug' => 'legacy_page_' . $target,
                    ));
                    
                    if ($aliasPage) {
                        $targetPage = $pageRepo->getTargetPage($aliasPage);
                        if ($targetPage) {
                            $objAliasLib = \aliasLib($targetPage->getLang());
                            $objAliasLib->_saveAlias($slug, $aliasPage->getTarget(), true);
                        }
                    }
                } else {
                    $objAliasLib = new \aliasLib(\FWLanguage::getDefaultLangId());
                    $objAliasLib->_saveAlias($slug, $target, false);
                }
            }
        }
        
        return true;
    }
    
    public function pageGrouping()
    {
        // fetch all pages
        if (!isset($_POST['doGroup']) || (isset($_POST['doGroup']) && !$_POST['doGroup'])) {
            return $this->getTreeCode();
        }
        
        \DBG::msg(str_repeat('#', 80));
        \DBG::msg('START GROUPING...');

        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');
        $nodeRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
        $pages = $pageRepo->findAll();
        $group = array();
        $nodeToRemove = array();
        $pageToRemove = array();

        $arrSimilarPages = $_POST['similarPages'];
        $arrRemovePages  = $_POST['removePages'];
        $delInAcLangs    = $_POST['delInAcLangs'];
        $i = 0;
        if ($delInAcLangs) {
            $arrLanguages = \FWLanguage::getLanguageArray();
            foreach ($arrLanguages as $arrLanguage) {
                if (empty($arrLanguage['frontend'])) {
                    $pagesOfInAcLang = $pageRepo->findBy(array('lang' => $arrLanguage['id']), true);
                    foreach ($pagesOfInAcLang as $page) {
                        $node = $page->getNode();
                        $countPagesOfThisNode = count($node->getPages(true));
                        
                        if ($countPagesOfThisNode === 1) {
                            self::$em->remove($node);
                        } else {
                            self::$em->remove($page);
                        }
                    }
                }
            }
        }
        
        foreach ($arrRemovePages as $pageId) {
            $page = $pageRepo->find($pageId);
            if ($page) {
                $pageToRemove[] = $pageId;
                $nodeToRemove[] = $page->getNode()->getId();
            }
        }

        foreach ($arrSimilarPages as $nodeId => $arrPageIds) {
            foreach ($arrPageIds as $pageId) {
                $page = $pageRepo->find($pageId);

                if ($page->getNode()->getId() != $nodeId) {
                    $formerNodeId = $page->getNode()->getId();
                    $nodeToRemove[] = $page->getNode()->getId();
                    $node = $nodeRepo->find($nodeId);
                    $page->setNode($node);
                    self::$em->persist($node);
                }
            }
        }

        self::$em->flush();

        // prevent the system from trying to remove the same node more than once
        $pageToRemove = array_unique($pageToRemove);
        foreach ($pageToRemove as $pageId) {
            $page = self::$em->getRepository('Cx\Model\ContentManager\Page')->find($pageId);
            self::$em->remove($page);
        }
// TODO: is this required? 
        self::$em->flush();
        self::$em->clear();

        $nodeToRemove = array_unique($nodeToRemove);
        foreach ($nodeToRemove as $nodeId) {
            $node = self::$em->getRepository('Cx\Model\ContentManager\Node')->find($nodeId);
            self::$em->getRepository('Cx\Model\ContentManager\Node')->removeFromTree($node);

            // reset node cache - this is required for the tree to reload its new structure after a node had been removed
            self::$em->clear();
        }
        
        return true;
    }

    private function findSimilarPages()
    {
        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');                
        $pages = $pageRepo->findAll();
        $group = array();
        $similarPages = array();

        // create base groups for defaultLang
        foreach ($pages as $page) {
            if ($page->getLang() != self::$defaultLang) {
                continue;
            }

            // don't group regular pages
            if (!$page->getModule()) continue;

            // group module pages
            if (!isset ($group[$page->getModule()][$page->getCmd()])) {
                $nodeId = $page->getNode()->getId();
                $group[$page->getModule()][$page->getCmd()] = $nodeId;
            } else {
                $nodeId = $group[$page->getModule()][$page->getCmd()];
            }

            $similarPages[$nodeId][] = $page->getId();
        }

        // group pages of non-default languages 
        foreach ($pages as $page) {
            if ($page->getLang() == self::$defaultLang) {
                continue;
            }

            // don't group regular pages
            if (!$page->getModule()) continue;

            // group module pages
            if (!isset ($group[$page->getModule()][$page->getCmd()])) {
                $nodeId = $page->getNode()->getId();
                $group[$page->getModule()][$page->getCmd()] = $nodeId;
            } else {
                $nodeId = $group[$page->getModule()][$page->getCmd()];
            }

            $similarPages[$nodeId][] = $page->getId();
        }

        $return = array();
        foreach ($similarPages as $nodeId => $pages) {
            $return[$nodeId] = $pages; 
        }
        
        return $return;
    }
    
    private function getTreeCode()
    {
        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');                
        $pages = $pageRepo->findAll();
        $nodes = array();
        
        foreach ($pages as $page) {
            if (!$page->getLang()) {
                continue;
            }
            
            if (!in_array($page->getLang(), $this->availableFrontendLanguages)) {
                $this->availableFrontendLanguages[] = $page->getLang();
            }

            $nodes[$page->getNode()->getId()][$page->getLang()] = $page->getId();
        }
        sort($this->availableFrontendLanguages);
        
        if (count($this->availableFrontendLanguages) === 1) {
            return true;
        }
        
        $objCx = \ContrexxJavascript::getInstance();
        
        $this->langs = $this->availableFrontendLanguages;
        $objCx->setVariable('langs', json_encode($this->langs), 'update/contentMigration');
        
        $arrSimilarPages = $this->findSimilarPages();
        $this->similarPages = $arrSimilarPages;
        $objCx->setVariable('similarPages', json_encode($arrSimilarPages), 'update/contentMigration');
        $objCx->setVariable('defaultLang', self::$defaultLang, 'update/contentMigration');
        
        $html  = '<div class="content-migration-info">';
        $html .=     'Die weiss hinterlegten Inhaltsseiten müssen gruppiert werden. ';
        $html .=     'Dabei müssen Sie zuerst aus jeder Sprache die gleiche Inhaltsseite markieren und anschliessend auf "Gruppieren" klicken. ';
        $html .=     'Falls Sie eine Gruppierung rückgängig machen wollen, wählen Sie die entsprechenden Inhaltsseiten und klicken Sie auf "Gruppierung aufheben".<br />';
        $html .=     'Über die Schaltfläche "Entfernen" können einzelne Inhaltsseiten entfernt werden. ';
        $html .=     'Klicken Sie auf die Schaltfläche "Abschliessen", wenn Sie mit der Gruppierung fertig sind.';
        $html .= '</div>';
        
        $htmlMenu   = '<div class="content-migration-select-wrapper %4$s">';
        $htmlMenu  .=     '<h3 class="content-migration-h3">%3$s</h3>';
        $htmlMenu  .=     '<select id="page_tree_%1$s" size="30" onclick="javascript:choose(this,%1$s)">%2$s</select>';
        $htmlMenu  .=     '<input type="text" id="page_group_%1$s" class="hide" />';
        $htmlMenu  .= '</div>';
        
        $htmlOption = '<option value="%1$s_%2$s" class="%5$s" onclick="javascript:selectPage(this,%6$s)">%4$s</option>';
        
        foreach ($this->availableFrontendLanguages as $lang) {
            $objContentTree = new \ContentTree($lang);
            $langName = \FWLanguage::getLanguageParameter($lang, 'name');
            $arrActiveLanguages = \FWLanguage::getIdArray();
            $classInactiveLanguage = !in_array($lang, $arrActiveLanguages) ? 'inactive-language' : '';
            $options = '';
            
            foreach ($objContentTree->getTree() as $arrPage) {
                $pageId = $nodes[$arrPage['node_id']][$arrPage['lang']];
                $grouped = $this->isGrouppedPage($arrSimilarPages, $pageId) ? ' grouped' : '';
                $options .= sprintf($htmlOption, $arrPage['node_id'], $pageId, $arrPage['lang'], contrexx_raw2xml($arrPage['catname']), 'level'.$arrPage['level'].$grouped, $lang);
            }
            
            $menu = sprintf($htmlMenu, $lang, $options, $langName, $classInactiveLanguage);
            $html .= $menu;
        }
        
        $html .= '<div class="content-migration-buttons">';
        $html .=     '<input type="button" value="Gruppieren" onclick="javascript:groupPages()" />&nbsp;&nbsp;';
        $html .=     '<input type="button" value="Gruppierung aufheben" onclick="javascript:ungroupPages()" />&nbsp;&nbsp;';
        $html .=     '<input type="button" value="Entfernen" onclick="javascript:delPage()" />&nbsp;&nbsp;';
        $html .=     '<input type="button" value="Entfernung aufheben" onclick="javascript:undelPage()" />';
        $html .=     '<input type="hidden" name="doGroup" id="doGroup" value="0" />';
        $html .=     '<input type="hidden" name="similarPages" id="similarPages" value="" />';
        $html .=     '<input type="hidden" name="removePages" id="removePages" value="" />';
        $html .= '</div>';
        $html .= '<div class="content-migration-legend">';
        $html .=     '<span style="color: #08D415;">Grün:</span> Gruppiert, <span style="color: #F00F00">Rot:</span> Entfernt&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $html .=     '<input type="checkbox" name="delInAcLangs" id="delInAcLangs" value="1" onclick="javascript:delInAcLangs()" checked="checked" /> <label for="delInAcLangs">Inaktive Sprachen löschen</label>';
        $html .= '</div>';
        
        return $html;
    }

    private function isGrouppedPage($arrSimilarPages, $pageId)
    {
        foreach ($arrSimilarPages as $pages) {
            if (count($pages) > 1 && in_array($pageId, $pages)) {
                return true;
            }
        }

        return false;
    }

    /*
      there are 'lost' ghost pages in the old content due to bugs, so we need to identify
      those pages.
      this simulates a sitemap as the old contentmanager saw it to achieve this.
      the ids of all 'non-lost' pages are then returned.
     */
    function getVisiblePageIDs() {
        $query = "SELECT lang FROM ".DBPREFIX."content_navigation GROUP BY lang";
        $result = $this->db->Execute($query);


        $pageIds = array();
        while(!$result->EOF) {
            $pageIds = array_merge($pageIds, $this->getVisiblePageIDsForLang($result->fields['lang']));
            $result->MoveNext();
        }


        return $pageIds;        
    }

    protected $treeArray = array();
    protected $navTable = array();

    function getVisiblePageIDsForLang($lang)
    {
        $query = "SELECT
                         n.lang,
                         n.catid AS catid,
                         n.displayorder AS displayorder,
                         n.parcat AS parcat
                    FROM ".DBPREFIX."content_navigation AS n
                   WHERE n.lang = $lang 
                ORDER BY n.parcat ASC, n.displayorder ASC";

        $objResult = $this->db->Execute($query);
        if ($objResult === false) {
            //problem.
        }

        $this->navtable = array();
        $this->treeArray = array();

        while (!$objResult->EOF) {
            $lang = $objResult->fields['lang'];
            $parcat = $objResult->fields['parcat'];
            $catid = $objResult->fields['catid'];

            $this->navtable[$parcat][$catid]='title';
            $objResult->MoveNext();
        }

        return $this->doAdminTreeArray();
    }

    //based on old ContentSitemap. magically removes the ghosts.
    function doAdminTreeArray($parcat=0, $level=0, $maxlevel=0)
    {
        $list = $this->navtable[$parcat];
        if (is_array($list)) {
            foreach (array_keys($list) as $pageId) {
                $this->treeArray[$pageId] = $level;
                if (isset($this->navtable[$pageId]) && ($maxlevel > $level+1 || $maxlevel == '0')) {
                    $this->doAdminTreeArray($pageId, $level+1, $maxlevel);
                }
            }
        }
        return array_keys($this->treeArray);
    }

}
