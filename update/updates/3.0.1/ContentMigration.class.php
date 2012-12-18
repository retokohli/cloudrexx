<?php
namespace Cx\Update;

set_time_limit(0);

// TODO: clean DB
//delete tc, tcn from contrexx_content_navigation as tcn inner join contrexx_content as tc on tc.id=tcn.catid where tcn.lang not in (LANG_IDS_TO_KEEP);
//delete tc, tcn, tl from contrexx_content_navigation_history as tcn inner join contrexx_content_history as tc on tc.id=tcn.id inner join contrexx_content_logfile as tl on tl.history_id = tcn.id where tcn.lang not in (LANG_IDS_TO_KEEP);

//$cl->loadFile(ASCMS_CORE_PATH . '/settings.class.php');
//$cl->loadFile(ASCMS_CORE_PATH . '/Tree.class.php');

class ContentMigration
{
    protected static $em;
    protected $db;
    protected $nodeArr = array();
    private $moduleNames = array();
    private $availableFrontendLanguages = array();
// TODO: fetch defaultLang from contrexx_languages;
    private $defaultLang = 1;
    
    
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

        $this->initModuleNames();
    }

    private function initModuleNames()
    {
        $this->moduleNames = array();

        $objModules = $this->db->Execute('SELECT `id`, `name`
                                             FROM `'.DBPREFIX.'modules`');
        while (!$objModules->EOF) {
            $this->moduleNames[$objModules->fields['id']] = $objModules->fields['name'];
            $objModules->MoveNext();
        }
    }

    protected function createNodesFromResults($resultSet, &$visiblePageIDs) {
        \DBG::msg("PRE-LOAD NODES");
        while (!$resultSet->EOF) {
            //skip ghosts
            if (!in_array($resultSet->fields['catid'], $visiblePageIDs)) {
                \DBG::msg("Page with ID {$resultSet->fields['catid']} is a ghost! Won't migrate...");
                $resultSet->MoveNext();
                continue;
            }

            $catId = $resultSet->fields['catid'];

            //skip existing nodes
            if(isset($this->nodeArr[$catId])) {
                \DBG::msg("A node for page ID {$resultSet->fields['catid']} is already present! Skip...");

                $resultSet->MoveNext();
                continue;
            }

            $this->nodeArr[$catId] = new \Cx\Model\ContentManager\Node();             
            
            self::$em->persist($this->nodeArr[$catId]);

            if (\DBG::getMode()) {
                self::$em->flush();
                \DBG::msg("Create new Node (ID:{$this->nodeArr[$catId]->getId()}) for page ID {$resultSet->fields['catid']}");
            }
            
            $resultSet->MoveNext();
        }
    }
   
    public function migrate()
    {
        if (isset($_POST['doGroup'])) {
            return;
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

        // Fetch a list of all pages that have ever existed or still do.
        // (sql-note: join content tables to prevent to migrate body-less pages)
        $objNodeResult = $this->db->Execute('SELECT `catid`, `lang`
                                                  FROM `'.DBPREFIX.'content_navigation_history` AS tnh
                                                INNER JOIN `'.DBPREFIX.'content_history` AS tch
                                                    ON tch.`id` = tnh.`id`
                                        UNION DISTINCT
                                                SELECT `catid`, `lang`
                                                  FROM `'.DBPREFIX.'content_navigation` AS tn
                                                INNER JOIN `'.DBPREFIX.'content` AS tc
                                                 ON tc.`id` = tn.`catid`
                                              ORDER BY `catid` ASC');

        // Create a node for each page that ever existed or still does
        // and put them in the array $this->nodeArr
        $this->createNodesFromResults($objNodeResult, $visiblePageIDs);

        // flush nodes to db
        self::$em->flush();




        // 1ST: MIGRATE PAGES FROM HISTORY
        \DBG::msg(str_repeat('#', 80));
        \DBG::msg("MIGRATE HISTORY");
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

        $p = array();
       
        // Migrate history
        while (!$objResult->EOF) {
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
                    die("Parent missing: {$objResult->fields['parcat']}");
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

            self::$em->flush();

            \DBG::msg("History ({$objResult->fields['action']}) [Page ID:{$objResult->fields['catid']} - {$objResult->fields['catname']} (m: {$this->moduleNames[$objResult->fields['module']]}, c: {$objResult->fields['cmd']})] Migrated to Page ID:{$page->getId()} on Node ID:{$page->getNode()->getId()}");

            $objResult->MoveNext();
        }




        // 2ND: MIGRATE CURRENT CONTENT 
        \DBG::msg(str_repeat('#', 80));
        \DBG::msg('MIGRATE CURRENT STRUCTURE');

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
        
        if (!$objRecords) {
            return;
        }

        while (!$objRecords->EOF) {
            $catid = $objRecords->fields['catid'];
            //skip ghosts
            if(!in_array($catid, $visiblePageIDs)) {
                $objRecords->MoveNext();
                continue;
            }

            $debugMsg = "Migrate page ID $catid - {$objRecords->fields['catname']} (m: {$this->moduleNames[$objRecords->fields['module']]}, c: {$objRecords->fields['cmd']})";

            if (!isset($this->nodeArr[$catid])) {
                die("Trying to migrate non-existing Node {$catid}");
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

// TODO: do we really have to persist and flush the nodes here?
            //self::$em->persist($node);
            //self::$em->flush();

            // set page data
            if (!isset($p[$catid])) {
                $p[$catid] = new \Cx\Model\ContentManager\Page();
            }
            $page = $p[$catid];

            $this->_setPageRecords($objRecords, $this->nodeArr[$catid], $page);
            $page->setAlias('legacy_page_' . $catid);

            self::$em->persist($page);
            self::$em->flush();

            \DBG::msg("$debugMsg - attached as PAGE:{$page->getId()} in LANG:{$page->getLang()} to NODE:{$this->nodeArr[$catid]->getId()}");
            
            $objRecords->MoveNext();
        }

    }
    
    function _setPageRecords($objResult, $node, $page)
    {
        $page->setNode($node);
        $page->setNodeIdShadowed($node->getId());
        $page->setLang($objResult->fields['lang']);
        $page->setCaching($objResult->fields['cachingstatus']);
        $page->setUpdatedAt($objResult->fields['changelog']);
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
        
        $dateTimeStart = new DateTime($objResult->fields['startdate']);
        $start = $dateTimeStart->getTimestamp();
        if ($start) {
            $page->setStart($start);
        }
        $dateTimeEnd = new DateTime($objResult->fields['enddate']);
        $end = $dateTimeEnd->getTimestamp();
        if ($end) {
            $page->setEnd($end);
        }

        $linkTarget = $objResult->fields['target'];
        if(!$linkTarget) {
            $linkTarget = null;
        }
        $page->setLinkTarget($linkTarget);
        
        if($objResult->fields['module'] && isset($this->moduleNames[$objResult->fields['module']])) {
            $page->setModule($this->moduleNames[$objResult->fields['module']]);
        }
        $page->setCmd($objResult->fields['cmd']);

        //set the type the way the type is supposed to be set. 
        if($page->getModule()) {
            $page->setType(\Cx\Model\ContentManager\Page::TYPE_APPLICATION);
        }
        
        if($page->getTarget()) {
            $page->setType(\Cx\Model\ContentManager\Page::TYPE_REDIRECT);
        }
    }
    
    public function pageGrouping()
    {
        // fetch all pages
        if (!isset($_POST['doGroup'])) {
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

        $arrSimilarPages = json_decode($_POST['similarPages']);//$this->findSimilarPages();
        $arrRemovePages = json_decode($_POST['removePages']);//$this->findSimilarPages();

        foreach ($arrRemovePages as $pageId) {
            $page = $pageRepo->find($pageId);
            $pageToRemove[] = $pageId;
            $nodeToRemove[] = $page->getNode()->getId();
        }

        foreach ($arrSimilarPages as $nodeId => $arrPageIds) {
            foreach ($arrPageIds as $pageId) {
                $page = $pageRepo->find($pageId);

                if ($page->getNode()->getId() != $nodeId) {
                    $formerNodeId = $page->getNode()->getId();
                    $nodeToRemove[] = $page->getNode()->getId();
                    $node = $nodeRepo->find($nodeId);
                    $page->setNode($node);
                    \DBG::msg("Page {$page->getId()} (t: {$page->getType()}, m:{$page->getModule()}, c: {$page->getCmd()}) of node {$formerNodeId} and lang {$page->getLang()} attached to node {$nodeId}");
                    self::$em->persist($node);
                }
            }
        }

        self::$em->flush();

        // prevent the system from trying to remove the same node more than once
        $pageToRemove = array_unique($pageToRemove);
\DBG::dump($pageToRemove);
        foreach ($pageToRemove as $pageId) {
            \DBG::msg("Removing page $pageId");
            $page = self::$em->getRepository('Cx\Model\ContentManager\Page')->find($pageId);
            self::$em->remove($page);
        }
// TODO: is this required? 
        self::$em->flush();
        self::$em->clear();

        $nodeToRemove = array_unique($nodeToRemove);
\DBG::dump($nodeToRemove);
        foreach ($nodeToRemove as $nodeId) {
            \DBG::msg("Removing node $nodeId");
            $node = self::$em->getRepository('Cx\Model\ContentManager\Node')->find($nodeId);
            self::$em->getRepository('Cx\Model\ContentManager\Node')->removeFromTree($node);

            // reset node cache - this is required for the tree to reload its new structure after a node had been removed
            self::$em->clear();
        }
    }

    private function findSimilarPages()
    {
        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');                
        $pages = $pageRepo->findAll();
        $group = array();
        $similarPages = array();

        // create base groups for defaultLang
        foreach ($pages as $page) {
            if ($page->getLang() != $this->defaultLang) {
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
            if ($page->getLang() == $this->defaultLang) {
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

//print_r($similarPages);
        $return = array();
        foreach ($similarPages as $nodeId => $pages) {
            if (count($pages) == 3) {
                $return[$nodeId] = $pages; 
            }
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
        
        $objCx = \ContrexxJavascript::getInstance();
        
        $langs = json_encode($this->availableFrontendLanguages);
        $objCx->setVariable('langs', $langs, 'update/contentMigration');

        $arrSimilarPages = $this->findSimilarPages();
        $arrSimilarPagesJs = json_encode($arrSimilarPages);
        $objCx->setVariable('arrSimilarPagesJs', $arrSimilarPagesJS, 'update/contentMigration');
        $objCx->setVariable('defaultLang', '{' . $this->defaultLang . '}', 'update/contentMigration');
        
        $html  = '';
        $htmlOption = '<option value="%1$s_%2$s" class="%5$s" onclick="javascript:selectPage(this,%6$s)">%4$s</option>';
        $htmlMenu = '<div style="float:left;"><select id="page_tree_%1$s" size="30" onclick="javascript:choose(this,%1$s)" onkeypress="javascript:handleEvent(event,this,%1$s)" style="height:700px;">%2$s</select><br /><input type="text" id="page_group_%1$s" /></div>';
        
        foreach ($this->availableFrontendLanguages as $lang) {
            $objContentTree = new \ContentTree($lang);
            $options = '';
            
            foreach ($objContentTree->getTree() as $arrPage) {
                $pageId = $nodes[$arrPage['node_id']][$arrPage['lang']];
                $grouped = $this->isGrouppedPage($arrSimilarPages, $pageId) ? ' grouped' : '';
                $options .= sprintf($htmlOption, $arrPage['node_id'], $pageId, $arrPage['lang'], contrexx_raw2xml($arrPage['catname']), 'level'.$arrPage['level'].$grouped, $lang);
            }
            
            $menu = sprintf($htmlMenu, $lang, $options);
            $html .= $menu;
        }


        $html .= '<div style="clear:left;">';
        $html .= '<input type="button" value="group" onclick="javascript:groupPages()" />';
        $html .= 'Press DEL to remove a page';
        $html .= '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        $html .= '<input type="hidden" name="similarPages" id="similarPages" value="" />';
        $html .= '<input type="hidden" name="removePages" id="removePages" value="" />';
        $html .= '<input type="submit" name="doGroup" value="do group..." onclick="javascript:executeGrouping();return true;" />';
        $html .= '</form>';
        $html .= '</div>';
        
        return $html;
    }

    private function isGrouppedPage($arrSimilarPages, $pageId)
    {
        foreach ($arrSimilarPages as $pages) {
            // count($pages) must be greater than 1, otherwise its a single page
            if (   count($pages) > 2
                && in_array($pageId, $pages)
            ) {
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
