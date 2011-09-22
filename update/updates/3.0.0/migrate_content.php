<?php
set_time_limit(0);
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../../lib/DBG.php';
require_once '../../../config/configuration.php';
require_once '../../../core/API.php';
require_once '../../../config/doctrine.php';
DBG::activate();

$m = new Contrexx_Content_migration;
$m->migrate();
$m->pageGrouping();
print 'DONE';

class Contrexx_Content_migration
{

    protected static $em;
    protected $nodeArr = array();
    
    public function __construct()
    {
        global $objDatabase;
        
        $objDatabase = getDatabaseObject($errorMsg);
        if (!$objDatabase) {
            die($errorMsg);
        }
        self::$em = Env::em();
    }

    protected function createNodesFromResults($resultSet, &$visiblePageIDs) {
        while (!$resultSet->EOF) {
            //skip ghosts
            if (!in_array($resultSet->fields['catid'], $visiblePageIDs)) {
                $resultSet->MoveNext();
                continue;
            }

            $catId = $resultSet->fields['catid'];

            //skip existing noeds
            if(isset($this->nodeArr[$catId]))
                continue;

            $this->nodeArr[$catId] = new \Cx\Model\ContentManager\Node();             
            
            self::$em->persist($this->nodeArr[$catId]);
            
            $resultSet->MoveNext();
        }
       
    }
   
    public function migrate()
    {
        global $objDatabase;
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_pages`');
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_nodes`');
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_ext_log_entries`');

        $this->nodeArr = array ();
        $root = new \Cx\Model\ContentManager\Node();
        self::$em->persist($root);

        $visiblePageIDs = $this->getVisiblePageIDs();

        $objNodeResult = $objDatabase->Execute('SELECT DISTINCT catid
                                                FROM `'.DBPREFIX.'content_navigation_history`
                                                ORDER BY catid ASC');

        $this->createNodesFromResults($objNodeResult, $visiblePageIDs);

        // flush nodes to db
        self::$em->flush();

        $objResult = $objDatabase->Execute('SELECT cn.*,
               nav.*,
               cnlog.*
               FROM `'.DBPREFIX.'content_history` AS cn
               INNER JOIN `'.DBPREFIX.'content_navigation_history` AS nav
               ON cn.id=nav.id
               INNER JOIN `'.DBPREFIX.'content_logfile` AS cnlog
               ON cn.id = cnlog.history_id ORDER BY cnlog.id ASC');

        $p = array();
       
        while (!$objResult->EOF) {
            //skip ghosts
            if (!in_array($objResult->fields['catid'], $visiblePageIDs)) {
                $objResult->MoveNext();
                continue;
            }

            if ($objResult->fields['parcat'] == 0) {
                $this->nodeArr[$objResult->fields['catid']]->setParent($root);
            } else {
                $this->nodeArr[$objResult->fields['catid']]->setParent($this->nodeArr[$objResult->fields['parcat']]);
            }

            $deleted = false;
            $page = null;

            switch ($objResult->fields['action']) {
                case 'new':
                    $p[$objResult->fields['page_id']] = new \Cx\Model\ContentManager\Page();
                    $page = $p[$objResult->fields['page_id']];
                    break;
                case 'update':
                    $page = isset($p[$objResult->fields['page_id']]) ? $p[$objResult->fields['page_id']] : new \Cx\Model\ContentManager\Page();
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

            $objResult->MoveNext();
        }             


        // Check for unmatched record in contents table and pages table
        $objRecords = $objDatabase->Execute('SELECT * 
                                             FROM `'.DBPREFIX.'content` AS cn
                                             INNER JOIN `'.DBPREFIX.'content_navigation` AS nav
                                             ON cn.id = nav.catid
                                             WHERE cn.id
                                             NOT IN (
                                                SELECT contrexx_pages.id
                                                FROM contrexx_pages
                                                WHERE cn.id = contrexx_pages.id)
                                             ORDER BY nav.parcat ASC, nav.displayorder ASC'
                                            );
        
        if ($objRecords->RecordCount() > 0) {
            $objNodeResult = $objDatabase->Execute('SELECT catid, parcat  
                                                    FROM `'.DBPREFIX.'content_navigation`
                                                    ORDER BY parcat ASC, displayorder ASC');

            $this->createNodesFromResults($objNodeResult, $visiblePageIDs);
            self::$em->flush();

            $objNodeResult->MoveFirst();

            while (!$objNodeResult->EOF) {
                //skip ghosts
                if(!in_array($objNodeResult->fields['catid'], $visiblePageIDs)) {
                    $objNodeResult->MoveNext();
                    continue;
                }

                $node = $this->nodeArr[$objNodeResult->fields['catid']];

                if ($objNodeResult->fields['parcat'] == 0) {
                    $node->setParent($root);                
                } else {
                    $node->setParent($this->nodeArr[$objNodeResult->fields['parcat']]);
                }

                self::$em->persist($node);

                $objNodeResult->MoveNext();
            }
            self::$em->flush();
        }

        while (!$objRecords->EOF) {
            $catid = $objRecords->fields['catid'];
            //skip ghosts
            if(!in_array($catid, $visiblePageIDs)) {
                $objRecords->MoveNext();
                continue;
            }

            $page = new \Cx\Model\ContentManager\Page();

            $this->_setPageRecords($objRecords, $this->nodeArr[$catid], $page);

            self::$em->persist($page);

            self::$em->flush();
            
            $objRecords->MoveNext();
        }

    }
    
    function _setPageRecords($objResult, $node, $page)
    {
        global $objDatabase;
        
        // Convert the changelog value from Unix time stamp to date for UpdatedAt function
        $updatedDate = Date('Y-m-d H:i:s',$objResult->fields['changelog']);
        // Get the corresponding module name
        $objModules = $objDatabase->Execute('SELECT `name` as `moduleName`
                                             FROM `'.DBPREFIX.'modules`
                                             WHERE id = '.$objResult->fields['module']);
        
        $page->setNode($node); 
        $page->setLang($objResult->fields['lang']);
        $page->setCaching($objResult->fields['cachingstatus']);
        $page->setUpdatedAt(new DateTime($updatedDate));
        $page->setTitle($objResult->fields['title']);
        $page->setContent($objResult->fields['content']);            
        $page->setCustomContent($objResult->fields['custom_content']);
        $page->setCssName($objResult->fields['css_name']);
        $page->setMetatitle($objResult->fields['metatitle']);
        $page->setMetadesc($objResult->fields['metadesc']);
        $page->setMetakeys($objResult->fields['metakeys']);
        $page->setMetarobots($objResult->fields['metarobots']);
        //$page->setStart(new DateTime($objResult->fields['startdate']));
        //$page->setEnd(new DateTime($objResult->fields['enddate']));
        $page->setStart(new DateTime());
        $page->setEnd(new DateTime());
        $page->setUsername($objResult->fields['username']);
        $page->setDisplay(($objResult->fields['displaystatus'] === 'on' ? 1 : 0));
        $page->setActive($objResult->fields['activestatus']);
        $page->setTarget($objResult->fields['target']);
        $page->setModule($objModules->fields['moduleName']);
        $page->setCmd($objResult->fields['cmd']);

        //set the type the way the type is supposed to be set. 
        if($page->getModule())
            $page->setType('module');
    }
    
    function pageGrouping()
    {
        // fetch all pages
        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');
        $pages = $pageRepo->findAll();
        $group = array();
        $nodeToRemove = array();
        foreach ($pages as $page) {
            // don't group regular pages
            if (!$page->getModule()) continue;

            // group module pages
            if (!isset ($group[$page->getModule()][$page->getCmd()])) {
                $group[$page->getModule()][$page->getCmd()] = $page;
            } else {
                if ($page->getNode()->getId() != $group[$page->getModule()][$page->getCmd()]->getNode()->getId()) {
                    $nodeToRemove[] = $page->getNode()->getId();
                    $src = $group[$page->getModule()][$page->getCmd()];
                    
                    $t = $pageRepo->translate($src, $page->getLang(), true, false, true);
                    $t->setContent($page->getContent());
                    $t->setTitle($page->getTitle());

                    self::$em->remove($page);
                    self::$em->persist($t);
                }
            }
        }
        self::$em->flush();

        // prevent the system from trying to remove the same node more than once
        $nodeToRemove = array_unique($nodeToRemove);
        foreach ($nodeToRemove as $nodeId) {
            $node = self::$em->getRepository('Cx\Model\ContentManager\Node')->find($nodeId);
            self::$em->getRepository('Cx\Model\ContentManager\Node')->removeFromTree($node);

            // reset node cache - this is required for the tree to reload its new structure after a node had been removed
            self::$em->clear();
        }
    }

    /*
      there are 'lost' ghost pages in the old content due to bugs, so we need to identify
      those pages.
      this simulates a sitemap as the old contentmanager saw it to achieve this.
      the ids of all 'non-lost' pages are then returned.
     */
    function getVisiblePageIDs() {
        global $objDatabase;

        $query = "SELECT lang FROM ".DBPREFIX."content_navigation GROUP BY lang";
        $result = $objDatabase->Execute($query);


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
        global $objDatabase;
        $query = "SELECT
                         n.lang,
                         n.catid AS catid,
                         n.displayorder AS displayorder,
                         n.parcat AS parcat
                    FROM ".DBPREFIX."content_navigation AS n
                   WHERE n.lang = $lang 
                ORDER BY n.parcat ASC, n.displayorder ASC";

        $objResult = $objDatabase->Execute($query);
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
?>
