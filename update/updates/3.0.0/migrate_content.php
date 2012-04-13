<?php
use Doctrine\Common\Util\Debug as DoctrineDebug;
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
require_once ASCMS_CORE_PATH.'/Tree.class.php';
DBG::activate(DBG_ADODB_ERROR | DBG_PHP | DBG_LOG_FILE);

$m = new Contrexx_Content_migration;
$m->migrate();
$m->pageGrouping();
print 'DONE';

class Contrexx_Content_migration
{

    protected static $em;
    protected $nodeArr = array();
    private $moduleNames = array();
    private $availableFrontendLanguages = array();
// TODO: fetch defaultLang from contrexx_languages;
    private $defaultLang = 1;
    
    
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

        $this->initModuleNames();
    }

    private function initModuleNames()
    {
        global $objDatabase;

        $this->moduleNames = array();

        $objModules = $objDatabase->Execute('SELECT `id`, `name`
                                             FROM `'.DBPREFIX.'modules`');
        while (!$objModules->EOF) {
            $this->moduleNames[$objModules->fields['id']] = $objModules->fields['name'];
            $objModules->MoveNext();
        }
    }

    protected function createNodesFromResults($resultSet, &$visiblePageIDs) {
        DBG::msg("PRE-LOAD NODES");
        while (!$resultSet->EOF) {
            //skip ghosts
            if (!in_array($resultSet->fields['catid'], $visiblePageIDs)) {
                DBG::msg("Page with ID {$resultSet->fields['catid']} is a ghost! Won't migrate...");
                $resultSet->MoveNext();
                continue;
            }

            $catId = $resultSet->fields['catid'];

            //skip existing nodes
            if(isset($this->nodeArr[$catId])) {
                DBG::msg("A node for page ID {$resultSet->fields['catid']} is already present! Skip...");

                $resultSet->MoveNext();
                continue;
            }

            $this->nodeArr[$catId] = new \Cx\Model\ContentManager\Node();             
            
            self::$em->persist($this->nodeArr[$catId]);

            if (DBG::getMode()) {
                self::$em->flush();
                DBG::msg("Create new Node (ID:{$this->nodeArr[$catId]->getId()}) for page ID {$resultSet->fields['catid']}");
            }
            
            $resultSet->MoveNext();
        }
    }
   
    public function migrate()
    {
        global $objDatabase;

        if (isset($_POST['doGroup'])) {
            return;
        }

        $objDatabase->Execute('TRUNCATE TABLE `contrexx_pages`');
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_nodes`');
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_ext_log_entries`');

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
        $objNodeResult = $objDatabase->Execute('SELECT `catid`, `lang`
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
        DBG::msg(str_repeat('#', 80));
        DBG::msg("MIGRATE HISTORY");
        $objResult = $objDatabase->Execute('SELECT cn.*,
               nav.*,
               cnlog.*
               FROM `'.DBPREFIX.'content_history` AS cn
               INNER JOIN `'.DBPREFIX.'content_navigation_history` AS nav
               ON cn.id=nav.id
               INNER JOIN `'.DBPREFIX.'content_logfile` AS cnlog
               ON cn.id = cnlog.history_id ORDER BY cnlog.id ASC');

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

            DBG::msg("History ({$objResult->fields['action']}) [Page ID:{$objResult->fields['catid']} - {$objResult->fields['catname']} (m: {$this->moduleNames[$objResult->fields['module']]}, c: {$objResult->fields['cmd']})] Migrated to Page ID:{$page->getId()} on Node ID:{$page->getNode()->getId()}");

            $objResult->MoveNext();
        }




        // 2ND: MIGRATE CURRENT CONTENT 
        DBG::msg(str_repeat('#', 80));
        DBG::msg('MIGRATE CURRENT STRUCTURE');

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

            self::$em->persist($page);
            self::$em->flush();

            DBG::msg("$debugMsg - attached as PAGE:{$page->getId()} in LANG:{$page->getLang()} to NODE:{$this->nodeArr[$catid]->getId()}");
            
            $objRecords->MoveNext();
        }

    }
    
    function _setPageRecords($objResult, $node, $page)
    {
        // Convert the changelog value from Unix time stamp to date for UpdatedAt function
        $updatedDate = Date('Y-m-d H:i:s',$objResult->fields['changelog']);
        
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
        if($objResult->fields['module'] && isset($this->moduleNames[$objResult->fields['module']]))
            $page->setModule($this->moduleNames[$objResult->fields['module']]);
        //if($objResult->fields['cmd'])
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

        if (!isset($_POST['doGroup'])) {
            return $this->parseTree();
        }



        DBG::msg(str_repeat('#', 80));
        DBG::msg('START GROUPING...');

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
                    DBG::msg("Page {$page->getId()} (t: {$page->getType()}, m:{$page->getModule()}, c: {$page->getCmd()}) of node {$formerNodeId} and lang {$page->getLang()} attached to node {$nodeId}");
                    self::$em->persist($node);
                }
            }
        }

        self::$em->flush();

        // prevent the system from trying to remove the same node more than once
        $pageToRemove = array_unique($pageToRemove);
DBG::dump($pageToRemove);
        foreach ($pageToRemove as $pageId) {
            DBG::msg("Removing page $pageId");
            $page = self::$em->getRepository('Cx\Model\ContentManager\Page')->find($pageId);
            self::$em->remove($page);
        }
// TODO: is this required? 
        self::$em->flush();
        self::$em->clear();

        $nodeToRemove = array_unique($nodeToRemove);
DBG::dump($nodeToRemove);
        foreach ($nodeToRemove as $nodeId) {
            DBG::msg("Removing node $nodeId");
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
    
    function parseTree()
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
        $langs = json_encode($this->availableFrontendLanguages);

        $arrSimilarPages = $this->findSimilarPages();
        $arrSimilarPagesJS = json_encode($arrSimilarPages);


        $htmlOption = '<option value="%1$s_%2$s" class="%5$s" onclick="javascript:selectPage(this,%6$s)">%4$s</option>';
        $htmlMenu = '<div style="float:left;"><select id="page_tree_%1$s" size="30" onclick="javascript:choose(this,%1$s)" onkeypress="javascript:handleEvent(event,this,%1$s)" style="height:700px;">%2$s</select><br /><input type="text" id="page_group_%1$s" /></div>';

        $js = <<<JS_
<script type="text/javascript" src="../../../lib/javascript/jquery/jquery-1.6.1.min.js" ></script>
<script type="text/javascript">
var langs = $langs;
var similarPages = $arrSimilarPagesJS;
var defaultLang = {$this->defaultLang};
var nodePageRegexp = /(\d+)_(\d+)/;
var removePages = new Array();

function handleEvent(event,select,lang)
{
    // 46 = DELETE key
    if (event.keyCode == 46) {
        page = jQuery(select).find(':selected');
        removePages.push(nodePageRegexp.exec(page.val())[2]);
        page.addClass('removed');
        move2NextUngroupedPage(page,lang);
    }
}

function choose(select,selectLang)
{
    var selectedPageId = nodePageRegexp.exec(jQuery(select).find(':selected').val())[2];

    associatedNode = null;
    for (node in similarPages) {
        for (page in similarPages[node]) {
            if (similarPages[node][page] == selectedPageId) {
                associatedNode = node;
                break;
            }
        }
    }

    if (associatedNode == null) {
        console.log('nope..');
        return;
    }

    for (page in similarPages[associatedNode]) {
        for (lIdx in langs) {
            lang = langs[lIdx];
            if (lang != selectLang) {
                jQuery(jQuery('#page_tree_'+lang).find('[value$=_'+similarPages[associatedNode][page]+']')).attr('selected', true);
            }
        }
    }
}

function selectPage(option,lang) {
    jQuery('#page_group_'+lang).val(jQuery(option).val());
}

function groupPages()
{
    nodeId = null;
    pages = new Array();
    options = new Array();

    for (lIdx in langs) {
        lang = langs[lIdx];
        pageInfo = jQuery('#page_group_'+lang).val();
        if (!pageInfo) {
            continue;
        }

        pageId = parseInt(nodePageRegexp.exec(pageInfo)[2],10);
        pages.push(pageId);

        selected = jQuery(jQuery('#page_tree_'+lang).find(':selected'));
        options[lang] = selected;

        if (lang == defaultLang) {
            nodeId = nodePageRegexp.exec(pageInfo)[1]
        }

    }

    if (nodeId) {
        similarPages[nodeId] = pages;

        for (lang in options) {
            if (lang) {
                options[lang].addClass('grouped');
                move2NextUngroupedPage(options[lang],lang);
            }
        }
    }
}

function move2NextUngroupedPage(page,lang)
{
    while (page = page.next()) {
        if (page.hasClass('grouped')) {
            continue;
        }

        page.attr('selected', true);
        break;
    }
    selectPage(page,lang);
}
function executeGrouping()
{
    jQuery('#similarPages').val(JSON.stringify(similarPages));
    jQuery('#removePages').val(JSON.stringify(removePages));
}
</script>
JS_;

        echo $js;

        $css = <<<CSS
<style type="text/css">
.level1 {
    text-indent: 15px;
}
.level2 {
    text-indent: 30px;
}
.level3 {
    text-indent: 45px;
}
.grouped {
    background-color: #0f0;
}
.removed {
    background-color: #f00;
}

</style>
CSS;

        echo $css;

        foreach ($this->availableFrontendLanguages as $lang) {
            $objContentTree = new ContentTree($lang);
            $options = '';
            foreach ($objContentTree->getTree() as $arrPage) {
//print_r($arrPage);
                $pageId = $nodes[$arrPage['node_id']][$arrPage['lang']];
                $grouped = $this->isGrouppedPage($arrSimilarPages, $pageId) ? ' grouped' : '';
                $options .= sprintf($htmlOption, $arrPage['node_id'], $pageId, $arrPage['lang'], contrexx_raw2xhtml($arrPage['catname']), 'level'.$arrPage['level'].$grouped, $lang);
            }
            $menu = sprintf($htmlMenu, $lang, $options);

        print $menu;
        }


        print '<div style="clear:left;">';
        print '<input type="button" value="group" onclick="javascript:groupPages()" />';
        print 'Press DEL to remove a page';
        print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        print '<input type="hidden" name="similarPages" id="similarPages" value="" />';
        print '<input type="hidden" name="removePages" id="removePages" value="" />';
        print '<input type="submit" name="doGroup" value="do group..." onclick="javascript:executeGrouping();return true;" />';
        print '</form>';
        print '</div>';
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
