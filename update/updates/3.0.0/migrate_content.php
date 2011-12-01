<?php
set_time_limit(0);

// TODO: clean DB
/*
delete tc, tcn from contrexx_content_navigation as tcn inner join contrexx_content as tc on tc.id=tcn.catid where tcn.lang not in (LANG_IDS_TO_KEEP);

delete tc, tcn, tl from contrexx_content_navigation_history as tcn inner join contrexx_content_history as tc on tc.id=tcn.id inner join contrexx_content_logfile as tl on tl.history_id = tcn.id where tcn.lang not in (LANG_IDS_TO_KEEP);
*/


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../../lib/DBG.php';
require_once '../../../config/configuration.php';
require_once '../../../core/API.php';
require_once '../../../config/doctrine.php';
DBG::activate(DBG_ADODB_ERROR | DBG_PHP);

$m = new Contrexx_Content_migration;
$m->migrate();
//$m->pageGrouping();
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

        if (DBG::getMode() & DBG_ADODB_TRACE) {
            DBG::enable_adodb_debug(true);
        } elseif (DBG::getMode() & DBG_ADODB || DBG::getMode() & DBG_ADODB_ERROR) {
            DBG::enable_adodb_debug();
        } else {
            DBG::disable_adodb_debug();
        }

        self::$em = Env::em();
    }

    protected function createNodesFromResults($resultSet, &$visiblePageIDs) {
        DBG::msg("PRE-LOAD NODES");
        while (!$resultSet->EOF) {
            //skip ghosts
            if (!in_array($resultSet->fields['catid'], $visiblePageIDs)) {
                DBG::msg("GHOST: {$resultSet->fields['catid']}");
                $resultSet->MoveNext();
                continue;
            }

            DBG::msg("PASS: {$resultSet->fields['catid']}");

            $catId = $resultSet->fields['catid'];

            //skip existing noeds
            if(isset($this->nodeArr[$catId])) {
                $resultSet->MoveNext();
                continue;
            }

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

        // join content tables to prevent to migrate body-less pages
        $objNodeResult = $objDatabase->Execute('SELECT `catid`
                                                  FROM `'.DBPREFIX.'content_navigation_history` AS tnh
                                                INNER JOIN `'.DBPREFIX.'content_history` AS tch
                                                    ON tch.`id` = tnh.`id`
                                        UNION DISTINCT
                                                SELECT `catid`
                                                  FROM `'.DBPREFIX.'content_navigation` AS tn
                                                INNER JOIN `'.DBPREFIX.'content` AS tc
                                                 ON tc.`id` = tn.`catid`
                                              ORDER BY `catid` ASC');

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
       
        // 1ST: MIGRATE HISTORY
        while (!$objResult->EOF) {
            //skip ghosts
            if (!in_array($objResult->fields['catid'], $visiblePageIDs)) {
                $objResult->MoveNext();
                continue;
            }

// TODO: create a LOST&FOUND node in case a certain parent node doesn't exist
            if ($objResult->fields['parcat'] == 0) {
                $this->nodeArr[$objResult->fields['catid']]->setParent($root);
            } else {
                if (!isset($this->nodeArr[$objResult->fields['parcat']])) {
                    die("Parent missing: {$objResult->fields['parcat']}");
                }
                $this->nodeArr[$objResult->fields['catid']]->setParent($this->nodeArr[$objResult->fields['parcat']]);
            }

            $deleted = false;
            $page = null;

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

            $objResult->MoveNext();
        }             


        // 2ND: MIGRATE CURRENT CONTENT 
        $objRecords = $objDatabase->Execute('SELECT * 
                                             FROM `'.DBPREFIX.'content` AS cn
                                             INNER JOIN `'.DBPREFIX.'content_navigation` AS nav
                                             ON cn.id = nav.catid
                                             WHERE cn.id
                                             ORDER BY nav.parcat ASC, nav.displayorder DESC'
                                            );
        
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

            DBG::msg("POST Migrate: $catid - {$objRecords->fields['catname']}");

            $node = $this->nodeArr[$catid];

            if ($objRecords->fields['parcat'] == 0) {
                $node->setParent($root);                
            } else {
                $node->setParent($this->nodeArr[$objRecords->fields['parcat']]);
            }
            self::$em->persist($node);
            self::$em->flush();

            // set page data
            if (!isset($p[$catid])) {
                $p[$catid] = new \Cx\Model\ContentManager\Page();
            }
            $page = $p[$catid];

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
        $page->setStart($objResult->fields['startdate']);
        $page->setEnd($objResult->fields['enddate']);
        //$page->setStart(new DateTime($objResult->fields['startdate']));
        //$page->setEnd(new DateTime($objResult->fields['enddate']));
        $page->setStart(new DateTime());
        $page->setEnd(new DateTime());
        $page->setUsername($objResult->fields['username']);
        $page->setDisplay($objResult->fields['displaystatus'] === 'on' ? 1 : 0);
        $page->setActive($objResult->fields['activestatus']);
        $page->setSourceMode($objResult->fields['expertmode'] == 'y');

//TODO: migrate targets
        $page->setTarget($objResult->fields['redirect']);

        $linkTarget = $objResult->fields['target'];
        if(!$linkTarget)
            $linkTarget = null;
        
        $page->setLinkTarget($linkTarget);
        if($objModules->fields['moduleName'])
            $page->setModule($objModules->fields['moduleName']);
        if($objResult->fields['cmd'])
            $page->setCmd($objResult->fields['cmd']);

        //set the type the way the type is supposed to be set. 
        if($page->getModule())
            $page->setType('application');
//TODO: migrate targets
        if($page->getTarget())
            $page->setType('redirect');
    }
    
    function pageGrouping()
    {
        // fetch all pages
        $pageRepo = self::$em->getRepository('Cx\Model\ContentManager\Page');        
        $nodeRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
        $pages = $pageRepo->findAll();
        $group = array();
        $nodeToRemove = array();   
             
        foreach ($pages as $page) {            
            $nodeRepo->moveUp($page->getNode()->getId(), true);
                    
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
