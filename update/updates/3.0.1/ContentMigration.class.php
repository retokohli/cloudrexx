<?php
namespace Cx\Update\Cx_3_0_1;

set_time_limit(0);

class ContentMigration
{
    public $langs;
    public $similarPages;
    public static $defaultLang;
    protected $nodeArr = array();
    protected $moduleNames = array();
    protected $availableFrontendLanguages = array();
    protected static $em;
    
    public function __construct()
    {
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
        
        try {
            $objModules = \Cx\Lib\UpdateUtil::sql('SELECT `id`, `name` FROM `'.DBPREFIX.'modules`');
        } catch (\Cx\Lib\UpdateException $e) {
            \DBG::msg(\Cx\Lib\UpdateUtil::DefaultActionHandler($e));
        }
        
        while (!$objModules->EOF) {
            $this->moduleNames[$objModules->fields['id']] = $objModules->fields['name'];
            $objModules->MoveNext();
        }
    }

    protected function getSortedNodes($objResult, $visiblePageIDs) {
        $arrSortedNodes = array();
        
        while (!$objResult->EOF) {
            $catId = $objResult->fields['catid'];
            
            // Skip ghosts
            if (!in_array($catId, $visiblePageIDs)) {
                $objResult->MoveNext();
                continue;
            }
            
            // Skip existing nodes
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
        
        if (empty($_SESSION['contrexx_update']['tables_created'])) {
            try {
                \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content_page');
                \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content_node');
                \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'log_entry');
                
                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX . 'content_node',
                    array(
                        'id'                                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                        'parent_id'                          => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'id'),
                        'lft'                                => array('type' => 'INT(11)', 'after' => 'parent_id'),
                        'rgt'                                => array('type' => 'INT(11)', 'after' => 'lft'),
                        'lvl'                                => array('type' => 'INT(11)', 'after' => 'rgt')
                    ),
                    array(
                        'IDX_E5A18FDD727ACA70'               => array('fields' => array('parent_id'))
                    ),
                    'InnoDB'
                );
                
                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX . 'content_page',
                    array(
                        'id'                                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                        'node_id'                            => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'id'),
                        'nodeIdShadowed'                     => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'node_id'),
                        'lang'                               => array('type' => 'INT(11)', 'after' => 'nodeIdShadowed'),
                        'type'                               => array('type' => 'VARCHAR(16)', 'after' => 'lang'),
                        'caching'                            => array('type' => 'TINYINT(1)', 'after' => 'type'),
                        'updatedAt'                          => array('type' => 'timestamp', 'after' => 'caching'),
                        'updatedBy'                          => array('type' => 'CHAR(40)', 'after' => 'updatedAt'),
                        'title'                              => array('type' => 'VARCHAR(255)', 'after' => 'updatedBy'),
                        'linkTarget'                         => array('type' => 'VARCHAR(16)', 'notnull' => false, 'after' => 'title'),
                        'contentTitle'                       => array('type' => 'VARCHAR(255)', 'after' => 'linkTarget'),
                        'slug'                               => array('type' => 'VARCHAR(255)', 'after' => 'contentTitle'),
                        'content'                            => array('type' => 'longtext', 'after' => 'slug'),
                        'sourceMode'                         => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '0', 'after' => 'content'),
                        'customContent'                      => array('type' => 'VARCHAR(64)', 'notnull' => false, 'after' => 'sourceMode'),
                        'cssName'                            => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'customContent'),
                        'cssNavName'                         => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'cssName'),
                        'skin'                               => array('type' => 'INT(11)', 'notnull' => false, 'after' => 'cssNavName'),
                        'metatitle'                          => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'skin'),
                        'metadesc'                           => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'metatitle'),
                        'metakeys'                           => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'metadesc'),
                        'metarobots'                         => array('type' => 'VARCHAR(7)', 'notnull' => false, 'after' => 'metakeys'),
                        'start'                              => array('type' => 'timestamp', 'after' => 'metarobots'),
                        'end'                                => array('type' => 'timestamp', 'after' => 'start'),
                        'editingStatus'                      => array('type' => 'VARCHAR(16)', 'after' => 'end'),
                        'protection'                         => array('type' => 'INT(11)', 'after' => 'editingStatus'),
                        'frontendAccessId'                   => array('type' => 'INT(11)', 'after' => 'protection'),
                        'backendAccessId'                    => array('type' => 'INT(11)', 'after' => 'frontendAccessId'),
                        'display'                            => array('type' => 'TINYINT(1)', 'after' => 'backendAccessId'),
                        'active'                             => array('type' => 'TINYINT(1)', 'after' => 'display'),
                        'target'                             => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'active'),
                        'module'                             => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'target'),
                        'cmd'                                => array('type' => 'VARCHAR(50)', 'notnull' => true, 'default' => '', 'after' => 'module')
                    ),
                    array(
                        'node_id'                            => array('fields' => array('node_id','lang'), 'type' => 'UNIQUE'),
                        'IDX_D8E86F54460D9FD7'               => array('fields' => array('node_id'))
                    ),
                    'InnoDB'
                );
                
                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX . 'log_entry',
                    array(
                        'id'                 => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                        'action'             => array('type' => 'VARCHAR(8)', 'after' => 'id'),
                        'logged_at'          => array('type' => 'timestamp', 'after' => 'action'),
                        'version'            => array('type' => 'INT(11)', 'after' => 'logged_at'),
                        'object_id'          => array('type' => 'VARCHAR(32)', 'notnull' => false, 'after' => 'version'),
                        'object_class'       => array('type' => 'VARCHAR(255)', 'after' => 'object_id'),
                        'data'               => array('type' => 'longtext', 'after' => 'object_class'),
                        'username'           => array('type' => 'VARCHAR(255)', 'notnull' => false, 'after' => 'data')
                    ),
                    array(
                        'log_class_unique_version_idx' => array('fields' => array('version','object_id','object_class'), 'type' => 'UNIQUE'),
                        'log_class_lookup_idx' => array('fields' => array('object_class')),
                        'log_date_lookup_idx' => array('fields' => array('logged_at')),
                        'log_user_lookup_idx' => array('fields' => array('username'))
                    ),
                    'InnoDB'
                );
                
                $objResult = \Cx\Lib\UpdateUtil::sql('SHOW CREATE TABLE `' . DBPREFIX . 'content_page`');
                if (!empty($objResult->fields['Create Table'])) {
                    $constraintExists = strpos(strtolower($objResult->fields['Create Table']), 'constraint') !== false;
                } else {
                    $constraintExists = false;
                }
                if (!$constraintExists) {
                    \Cx\Lib\UpdateUtil::sql('ALTER TABLE `' . DBPREFIX . 'content_node` ADD CONSTRAINT FOREIGN KEY (`parent_id`) REFERENCES `' . DBPREFIX . 'content_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
                }
                
                $objResult = \Cx\Lib\UpdateUtil::sql('SHOW CREATE TABLE `' . DBPREFIX . 'content_node`');
                if (!empty($objResult->fields['Create Table'])) {
                    $constraintExists = strpos(strtolower($objResult->fields['Create Table']), 'constraint') !== false;
                } else {
                    $constraintExists = false;
                }
                if (!$constraintExists) {
                    \Cx\Lib\UpdateUtil::sql('ALTER TABLE `' . DBPREFIX . 'content_page` ADD CONSTRAINT FOREIGN KEY (`node_id`) REFERENCES `' . DBPREFIX . 'content_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
                }
            } catch (\Cx\Lib\UpdateException $e) {
                \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
                return false;
            }
            
            $_SESSION['contrexx_update']['tables_created'] = true;
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                return false;
            }
        }
        
        $this->nodeArr = array();
        
        if (empty($_SESSION['contrexx_update']['root_node_added'])) {
            // This will be the root of the site-tree
            $root = new \Cx\Model\ContentManager\Node();
            self::$em->persist($root);
            self::$em->flush();
            $_SESSION['contrexx_update']['root_node_added'] = true;
        } else {
            $nodes = self::$em->getRepository('Cx\Model\ContentManager\Node')->findAll();
            $root  = $nodes[0];
        }
        
        // Due to a bug in the old content manager, there happened to exist ghost-pages
        // that were never visible, neither in the frontend, nor in the backend.
        // Therefore, we'll need a list of these ghost-pages so that we won't migrate them.
        // (because those ghost-pages would probably break the new site-tree)
        $visiblePageIDs = $this->getVisiblePageIDs();
        if ($visiblePageIDs === false) {
            return false;
        }
        
        if (empty($_SESSION['contrexx_update']['nodes_added'])) {
            // Fetch a list of all pages that have ever existed or still do.
            // (sql-note: join content tables to prevent to migrate body-less pages)
            try {
                $objNodeResult = \Cx\Lib\UpdateUtil::sql('
                        SELECT `catid`, `parcat`, `lang`, `displayorder`
                          FROM `'.DBPREFIX.'content_navigation_history` AS tnh
                    INNER JOIN `'.DBPREFIX.'content_history` AS tch
                            ON tch.`id` = tnh.`id`
                    UNION DISTINCT
                        SELECT `catid`, `parcat`, `lang`, `displayorder`
                          FROM `'.DBPREFIX.'content_navigation` AS tn
                    INNER JOIN `'.DBPREFIX.'content` AS tc
                            ON tc.`id` = tn.`catid`
                      ORDER BY `parcat`, `displayorder`, `lang` ASC
                ');
            } catch (\Cx\Lib\UpdateException $e) {
                \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
                return false;
            }
            
            // Create a node for each page that ever existed or still does
            // and put them in the array $this->nodeArr
            $arrSortedNodes = $this->getSortedNodes($objNodeResult, $visiblePageIDs);
            
            if (!empty($_SESSION['contrexx_update']['nodes'])) {
                foreach ($_SESSION['contrexx_update']['nodes'] as $catId => $nodeId) {
                    $node = self::$em->getRepository('Cx\Model\ContentManager\Node')->find($nodeId);
                    if ($node) {
                        self::$em->remove($node);
                    }
                }
                self::$em->flush();
                unset($_SESSION['contrexx_update']['nodes']);
            }
            
            foreach ($arrSortedNodes as $node) {
                self::$em->persist($node);
            }
            self::$em->flush();
            
            foreach ($this->nodeArr as $catId => $node) {
                $_SESSION['contrexx_update']['nodes'][$catId] = $node->getId();
            }
            
            $_SESSION['contrexx_update']['nodes_added'] = true;
            if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                return false;
            }
        } else {
            if (!empty($_SESSION['contrexx_update']['nodes'])) {
                foreach ($_SESSION['contrexx_update']['nodes'] as $catId => $nodeId) {
                    $node = self::$em->getRepository('Cx\Model\ContentManager\Node')->find($nodeId);
                    $this->nodeArr[$catId] = $node;
                }
            }
        }
        
        $p = array();
        
        if (empty($_SESSION['contrexx_update']['history_pages_added'])) {
            // 1ST: MIGRATE PAGES FROM HISTORY
            try {
                $hasCustomContent = false;
                if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'content_navigation_history', 'custom_content')) {
                    $hasCustomContent = true;
                }

                $objResult = \Cx\Lib\UpdateUtil::sql('
                    SELECT
                        cn.page_id, cn.content, cn.title, cn.metatitle, cn.metadesc, cn.metakeys, cn.metarobots, cn.css_name, cn.redirect, cn.expertmode,
                        nav.catid, nav.parcat, nav.catname, nav.target, nav.displayorder, nav.displaystatus, nav.activestatus,
                        nav.cachingstatus, nav.username, nav.changelog, nav.cmd, nav.lang, nav.module, nav.startdate, nav.enddate, nav.protected,
                        nav.frontend_access_id, nav.backend_access_id, nav.themes_id, nav.css_name AS css_nav_name, '.($hasCustomContent ? 'nav.custom_content,' : '').'
                        cnlog.action, cnlog.history_id, cnlog.is_validated
                    FROM       `' . DBPREFIX . 'content_history` AS cn
                    INNER JOIN `' . DBPREFIX . 'content_navigation_history` AS nav
                    ON         cn.id = nav.id
                    INNER JOIN `' . DBPREFIX . 'content_logfile` AS cnlog
                    ON         cn.id = cnlog.history_id ORDER BY cnlog.id ASC
                ');
            } catch (\Cx\Lib\UpdateException $e) {
                \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
                return false;
            }
            
            // Migrate history
            if ($objResult !== false) {
                if (!empty($_SESSION['contrexx_update']['pages'])) {
                    foreach ($_SESSION['contrexx_update']['pages'] as $catId => $pageId) {
                        $page = self::$em->getRepository('Cx\Model\ContentManager\Page')->find($pageId);
                        if ($page) {
                            self::$em->remove($page);
                        }
                    }
                    self::$em->flush();
                }
                
                while (!$objResult->EOF) {
                    if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                        return false;
                    }
                    
                    $catId = $objResult->fields['catid'];
                    
                    // Skip ghosts
                    if (!in_array($catId, $visiblePageIDs)) {
                        $objResult->MoveNext();
                        continue;
                    }
        
                    // TODO: create a LOST&FOUND node in case a certain parent node doesn't exist
                    // SET PARENT NODE
                    if ($objResult->fields['parcat'] == 0) {
                        // Page was located on the first level in the site-tree.
                        // Therefore, directly attach it to the ROOT-node
                        $this->nodeArr[$catId]->setParent($root);
                    } else {
                        if (!isset($this->nodeArr[$objResult->fields['parcat']])) {
                            // Parent node of page can't be found.
                            // Attach node to LOST&FOUND node
                            setUpdateMsg('Parent missing: id ' . $objResult->fields['parcat']);
                            return false;
                        }
        
                        // Attach page to associated parent node
                        $this->nodeArr[$catId]->setParent($this->nodeArr[$objResult->fields['parcat']]);
                    }
        
                    $deleted = false;
                    $page = null;
        
                    // CREATE PAGE
                    switch ($objResult->fields['action']) {
                        case 'new':
                        case 'update':
                            if (!isset($p[$catId])) {
                                $p[$catId] = new \Cx\Model\ContentManager\Page();
                            }
                            $page = $p[$catId];
                            break;
                        case 'delete':
                            self::$em->remove($p[$catId]);
                            self::$em->flush();
                            if (isset($_SESSION['contrexx_update']['pages'][$catId])) {
                                unset($_SESSION['contrexx_update']['pages'][$catId]);
                            }
                            $deleted = true;
                            break;
                    }                      
                    
                    if(!$deleted) {
                        $this->_setPageRecords($objResult, $this->nodeArr[$catId], $page);
                        self::$em->persist($page);
                        self::$em->flush();
                        $_SESSION['contrexx_update']['pages'][$catId] = $page->getId();
                    }
                    
                    $objResult->MoveNext();
                }
                
                $_SESSION['contrexx_update']['history_pages_added'] = true;
            }
        } else {
            if (!empty($_SESSION['contrexx_update']['pages'])) {
                foreach ($_SESSION['contrexx_update']['pages'] as $catId => $pageId) {
                    $page = self::$em->getRepository('Cx\Model\ContentManager\Page')->find($pageId);
                    $p[$catId] = $page;
                }
            }
        }
        
        if (empty($_SESSION['contrexx_update']['pages_added'])) {
            // 2ND: MIGRATE CURRENT CONTENT
            try {
                $hasCustomContent = false;
                if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX.'content_navigation', 'custom_content')) {
                    $hasCustomContent = true;
                }

                $objRecords = \Cx\Lib\UpdateUtil::sql('
                    SELECT cn.id, cn.content, cn.title, cn.metatitle, cn.metadesc, cn.metakeys, cn.metarobots, cn.css_name, cn.redirect, cn.expertmode,
                           nav.catid, nav.parcat, nav.catname, nav.target, nav.displayorder, nav.displaystatus, nav.activestatus,
                           nav.cachingstatus, nav.username, nav.changelog, nav.cmd, nav.lang, nav.module, nav.startdate, nav.enddate, nav.protected,
                           nav.frontend_access_id, nav.backend_access_id, nav.themes_id, nav.css_name AS css_nav_name '.($hasCustomContent ? ', nav.custom_content' : '').'
                    FROM       `' . DBPREFIX . 'content` AS cn
                    INNER JOIN `' . DBPREFIX . 'content_navigation` AS nav
                    ON         cn.id = nav.catid
                    WHERE      cn.id
                    ORDER BY   nav.parcat ASC, nav.displayorder DESC
                ');
            } catch (\Cx\Lib\UpdateException $e) {
                \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
                return false;
            }
            
            if ($objRecords !== false) {
                while (!$objRecords->EOF) {
                    if (!checkMemoryLimit() || !checkTimeoutLimit()) {
                        return false;
                    }
                    
                    $catId = $objRecords->fields['catid'];
                    
                    // Skip ghosts
                    if(!in_array($catId, $visiblePageIDs)) {
                        $objRecords->MoveNext();
                        continue;
                    }
        
                    if (!isset($this->nodeArr[$catId])) {
                        setUpdateMsg('Trying to migrate non-existing node: id ' . $catId);
                        return false;
                    }
        
                    $node = $this->nodeArr[$catId];
        
                    if ($objRecords->fields['parcat'] == 0) {
                        // Page was located on the first level in the site-tree.
                        // Therefore, directly attach it to the ROOT-node
                        //$node->setParent($root);
                        $this->nodeArr[$catId]->setParent($root);
                    } else {
                        // Attach page to associated parent node
                        //$node->setParent($this->nodeArr[$objRecords->fields['parcat']]);
                        $this->nodeArr[$catId]->setParent($this->nodeArr[$objRecords->fields['parcat']]);
                    }
        
                    // set page data
                    if (!isset($p[$catId])) {
                        $p[$catId] = new \Cx\Model\ContentManager\Page();
                    }
                    $page = $p[$catId];
        
                    $this->_setPageRecords($objRecords, $this->nodeArr[$catId], $page);
                    $page->setAlias(array('legacy_page_' . $catId));
                    
                    self::$em->persist($page);
                    $objRecords->MoveNext();
                }
                
                self::$em->flush();
                $_SESSION['contrexx_update']['pages_added'] = true;
            }
        }
        
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
        $customContent = isset($objResult->fields['custom_content']) ? $objResult->fields['custom_content'] : '';
        $page->setCustomContent($customContent);
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
        
        if ($objResult->fields['startdate'] != '0000-00-00') {
            $start = new \DateTime($objResult->fields['startdate']);
            $page->setStart($start);
        }
        
        if ($objResult->fields['enddate'] != '0000-00-00') {
            $end = new \DateTime($objResult->fields['enddate']);
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
        try {
            $objResult = \Cx\Lib\UpdateUtil::sql('
                SELECT `s`.`url` AS `slug`, `t`.`type`, `t`.`url` AS `target`
                FROM       `' . DBPREFIX . 'module_alias_source` AS `s`
                INNER JOIN `' . DBPREFIX . 'module_alias_target` AS `t`
                ON `s`.`target_id` = `t`.`id`
            ');
        } catch (\Cx\Lib\UpdateException $e) {
            \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            return false;
        }
        
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
    
    public function migrateBlocks()
    {
        global $_ARRAYLANG;
        
        try {
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX . 'module_block_categories',
                array(
                    'id'         => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'parent'     => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                    'name'       => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'parent'),
                    'order'      => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'name'),
                    'status'     => array('type' => 'TINYINT(1)', 'notnull' => true, 'default' => '1', 'after' => 'order')
                )
            );
            
            // migrate content -> related multi language
            if (\Cx\Lib\UpdateUtil::table_exist(DBPREFIX . 'module_block_rel_lang')) {
// TODO: loop through languages of each block and add copies if required
                /*- loop through blocks
                    - fetch lang_id's from contrexx_module_block_rel_lang
                    - in case that there are more than one lang_id's entry found:
                        - check if allpages option is set to 1:
                            -if not, for each additional lang_id must be added a new separate block
                */
                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX . 'module_block_rel_lang_content',
                    array(
                        'block_id'       => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                        'lang_id'        => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'after' => 'block_id'),
                        'content'        => array('type' => 'mediumtext', 'after' => 'lang_id'),
                        'active'         => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'content')
                    ),
                    array(
                        'id_lang'        => array('fields' => array('block_id','lang_id'), 'type' => 'UNIQUE')
                    )
                );
                
                \Cx\Lib\UpdateUtil::sql('
                    INSERT INTO `' . DBPREFIX . 'module_block_rel_lang_content` (`block_id`, `lang_id`, `content`, `active`) 
                    SELECT DISTINCT tblBlock.`id`, CL.`id`, tblBlock.`content`, 0
                    FROM `' . DBPREFIX . 'languages` AS CL, `' . DBPREFIX . 'module_block_blocks` AS tblBlock
                    WHERE CL.`frontend` = \'1\'
                    ORDER BY tblBlock.`id`
                    ON DUPLICATE KEY UPDATE `block_id` = `block_id`
                ');
                
                # Activate a block in the previous used languages
                \Cx\Lib\UpdateUtil::sql('
                    UPDATE `' . DBPREFIX . 'module_block_rel_lang_content` AS tblContent
                    INNER JOIN `' . DBPREFIX . 'module_block_rel_lang` AS tblLang
                    ON tblLang.`block_id` = tblContent.`block_id`
                    AND tblLang.`lang_id` = tblContent.`lang_id`
                    SET `active` = 1
                ');
                
                # Deactivate all blocks of which none of their content is active
                \Cx\Lib\UpdateUtil::sql('
                    UPDATE `' . DBPREFIX . 'module_block_blocks` AS tblBlock 
                    SET tblBlock.`active` = 0
                    WHERE tblBlock.`id` NOT IN (
                        SELECT `block_id` FROM `' . DBPREFIX . 'module_block_rel_lang_content` WHERE `active` = 1
                    )
                ');
                
                # fetch active blocks
                $arrActiveBlockIds = array();
                $activeBlockList = '';
                $objResult = \Cx\Lib\UpdateUtil::sql('SELECT `block_id` FROM `' . DBPREFIX . 'module_block_rel_lang_content` WHERE `active` = 1');
                if ($objResult && !$objResult->EOF) {
                    while (!$objResult->EOF) {
                        $arrActiveBlockIds[] = $objResult->fields['block_id'];
                        $objResult->MoveNext();
                    }
                    $activeBlockList = join(', ', $arrActiveBlockIds);
                }
                
                # Activate block's content of system's default language for all blocks that have no active content at all
                \Cx\Lib\UpdateUtil::sql('
                    UPDATE `' . DBPREFIX . 'module_block_rel_lang_content` AS tblContent
                    SET tblContent.`active` = 1
                    WHERE tblContent.`lang_id` = ( SELECT CL.`id` FROM `' . DBPREFIX . 'languages` AS CL WHERE CL.`frontend` AND CL.`is_default` = \'true\' )
                    '.($activeBlockList ? 'AND tblContent.`block_id` NOT IN ('.$activeBlockList.')' : '')
                );
                
                $arrLangIds = array_keys(\FWLanguage::getLanguageArray());
                foreach ($arrLangIds as $langId) {
                    \Cx\Lib\UpdateUtil::sql('
                        UPDATE `' . DBPREFIX . 'module_block_blocks` AS tblBlock
                        INNER JOIN `' . DBPREFIX . 'module_block_rel_lang` AS tblLang
                        ON tblLang.`block_id` = tblBlock.`id`
                        SET tblBlock.`global` = If(tblLang.`all_pages` = 0,2,1)
                        WHERE tblLang.`lang_id` = ' . $langId . '
                    ');
                }
            
                // drop obsolete table
                \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'module_block_rel_lang');
            }

            // 1. drop old locale column `content`
            // 2. add several new columns
            \Cx\Lib\UpdateUtil::table(
                DBPREFIX.'module_block_blocks',
                array(
                    'id'                 => array('type' => 'INT(10)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                    'start'              => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'id'),
                    'end'                => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'start'),
                    'name'               => array('type' => 'VARCHAR(255)', 'notnull' => true, 'default' => '', 'after' => 'end'),
                    'random'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'name'),
                    'random_2'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random'),
                    'random_3'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_2'),
                    'random_4'           => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_3'),
                    'global'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'random_4'),
                    'active'             => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'global'),
                    'order'              => array('type' => 'INT(1)', 'notnull' => true, 'default' => '0', 'after' => 'active'),
                    'cat'                => array('type' => 'INT(10)', 'notnull' => true, 'default' => '0', 'after' => 'order'),
                    'wysiwyg_editor'     => array('type' => 'INT(1)', 'notnull' => true, 'default' => '1', 'after' => 'cat')
                )
            );

            if (\Cx\Lib\UpdateUtil::column_exist(DBPREFIX . 'module_block_rel_pages', 'lang_id')) {
// TODO: MIGRATE data of contrexx_module_block_rel_pages for `contrexx_module_block_blocks` WHERE `global` = 2
                \Cx\Lib\UpdateUtil::table(
                    DBPREFIX.'module_block_rel_pages',
                    array(
                        'block_id'       => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true),
                        'page_id'        => array('type' => 'INT(7)', 'notnull' => true, 'default' => '0', 'primary' => true, 'after' => 'block_id')
                    )
                );
            }
        } catch (\Cx\Lib\UpdateException $e) {
            \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            return false;
        }
        
        return true;
    }
    
    public function pageGrouping()
    {
        // Fetch all pages
        if (!isset($_POST['doGroup']) || (isset($_POST['doGroup']) && !$_POST['doGroup'])) {
            return $this->getTreeCode();
        }

        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');
        $nodeRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
        $pages = $pageRepo->findAll();
        $group = array();
        $nodeToRemove = array();
        $pageToRemove = array();

        $arrSimilarPages = $_POST['similarPages'];
        $arrRemovePages  = $_POST['removePages'];
        $delInAcLangs    = $_POST['delInAcLangs'];
        
        if ($delInAcLangs) {
            $arrLanguages = \FWLanguage::getLanguageArray();
            
            foreach ($arrLanguages as $arrLanguage) {
                if (empty($arrLanguage['frontend'])) {
                    $pagesOfInAcLang = $pageRepo->findBy(array('lang' => $arrLanguage['id']), true);
                    foreach ($pagesOfInAcLang as $page) {
                        $aliases = $page->getAliases();
                        foreach ($aliases as $alias) {
                            self::$em->remove($alias);
                        }
                        
                        $node = $page->getNode();
                        $countPagesOfThisNode = count($node->getPages(true));
                        
                        if ($countPagesOfThisNode === 1) {
                            self::$em->remove($node);
                        } else {
                            self::$em->remove($page);
                        }
                    }
                }
                
                self::$em->flush();
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
                    
                    $aliases = $page->getAliases();
                    foreach ($aliases as $alias) {
                        $alias->setTarget('[[NODE_' . $node->getId() . '_' . $page->getLang() . ']]');
                        self::$em->persist($alias);
                    }
                    
                    $page->setNode($node);
                    
                    self::$em->persist($node);
                }
            }
        }

        self::$em->flush();

        // Prevent the system from trying to remove the same node more than once
        $pageToRemove = array_unique($pageToRemove);
        foreach ($pageToRemove as $pageId) {
            $page = self::$em->getRepository('Cx\Model\ContentManager\Page')->find($pageId);
            $aliases = $page->getAliases();
            foreach ($aliases as $alias) {
                self::$em->remove($alias);
            }
            self::$em->remove($page);
        }
        
        self::$em->flush();
        self::$em->clear();

        $nodeToRemove = array_unique($nodeToRemove);
        $nodeRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
        
        foreach ($nodeToRemove as $nodeId) {
            $node = $nodeRepo->find($nodeId);
            $nodeRepo->removeFromTree($node);

            // Reset node cache - this is required for the tree to reload its new structure after a node had been removed
            self::$em->clear();
        }
        
        // Drop old tables
        try {
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content');
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content_history');
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content_logfile');
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content_navigation');
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'content_navigation_history');
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'module_alias_source');
            \Cx\Lib\UpdateUtil::drop_table(DBPREFIX . 'module_alias_target');
        } catch (\Cx\Lib\UpdateException $e) {
            \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            return false;
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
            $cl = \Env::get('ClassLoader');
            $cl->loadFile(ASCMS_CORE_PATH . '/Tree.class.php');
            $cl->loadFile(UPDATE_CORE . '/UpdateTree.class.php');
            $objContentTree = new \UpdateContentTree($lang);
            $langName = \FWLanguage::getLanguageParameter($lang, 'name');
            $arrActiveLanguages = \FWLanguage::getIdArray();
            $classInactiveLanguage = !in_array($lang, $arrActiveLanguages) ? 'inactive-language' : '';
            $options = '';
            
            foreach ($objContentTree->getTree() as $arrPage) {
                $pageId = $nodes[$arrPage['node_id']][$arrPage['lang']];
                $grouped = $this->isGrouppedPage($arrSimilarPages, $pageId) ? ' grouped' : '';
                
                $spaces = '';
                for ($i = 0; $i < $arrPage['level']; $i++) {
                    $spaces .= '&nbsp;&nbsp;';
                }
                
                $options .= sprintf($htmlOption, $arrPage['node_id'], $pageId, $arrPage['lang'], $spaces.$arrPage['catname'], 'level'.$arrPage['level'].$grouped, $lang);
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
        try {
            $result = \Cx\Lib\UpdateUtil::sql('SELECT lang FROM ' . DBPREFIX . 'content_navigation GROUP BY lang');
        } catch (\Cx\Lib\UpdateException $e) {
            \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            return false;
        }
        
        $pageIds = array();
        while(!$result->EOF) {
            $visiblePageIDsForLang = $this->getVisiblePageIDsForLang($result->fields['lang']);
            if ($visiblePageIDsForLang === false) {
                return false;
            }
            $pageIds = array_merge($pageIds, $visiblePageIDsForLang);
            $result->MoveNext();
        }


        return $pageIds;        
    }

    protected $treeArray = array();
    protected $navTable = array();

    function getVisiblePageIDsForLang($lang)
    {
        try {
            $objResult = \Cx\Lib\UpdateUtil::sql('
                SELECT   n.lang,
                         n.catid AS catid,
                         n.displayorder AS displayorder,
                         n.parcat AS parcat
                    FROM ' . DBPREFIX . 'content_navigation AS n
                   WHERE n.lang = ' . $lang . '
                ORDER BY n.parcat ASC, n.displayorder ASC
            ');
        } catch (\Cx\Lib\UpdateException $e) {
            \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
            return false;
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
