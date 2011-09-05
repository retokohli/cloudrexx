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
    
    public function __construct()
    {
        global $objDatabase;
        
        $objDatabase = getDatabaseObject($errorMsg);
        if (!$objDatabase) {
            die($errorMsg);
        }
        self::$em = Env::em();


    }
   
    public function migrate()
    {
        global $objDatabase;
        $nodeArr = array ();
        $root = new \Cx\Model\ContentManager\Node();
        self::$em->persist($root);
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_pages`');
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_nodes`');
        $objDatabase->Execute('TRUNCATE TABLE `contrexx_ext_log_entries`');

        $objNodeResult = $objDatabase->Execute('SELECT DISTINCT catid
                                                FROM `'.DBPREFIX.'content_navigation_history`
                                                ORDER BY catid ASC');

        while (!$objNodeResult->EOF) {
            $nodeArr[$objNodeResult->fields['catid']] = new \Cx\Model\ContentManager\Node();             
            
            self::$em->persist($nodeArr[$objNodeResult->fields['catid']]);
            
            $objNodeResult->MoveNext();
        }

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
        
        while (!$objResult->EOF) {

            if ($objResult->fields['parcat'] == 0) {
                $nodeArr[$objResult->fields['catid']]->setParent($root);                
            } else {
                $nodeArr[$objResult->fields['catid']]->setParent($nodeArr[$objResult->fields['parcat']]);
            }

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
                    break;
            }
                        
            $this->_setPageRecords($objResult, $nodeArr[$objResult->fields['catid']], $page);

            self::$em->persist($page);

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
            $objNodeResult = $objDatabase->Execute('SELECT catid 
                                                    FROM `'.DBPREFIX.'content_navigation`
                                                    ORDER BY parcat ASC, displayorder ASC');

            while (!$objNodeResult->EOF) {
                if (empty ($nodeArr[$objNodeResult->fields['catid']])) {
                    $nodeArr[$objNodeResult->fields['catid']] = new \Cx\Model\ContentManager\Node();             
            
                    self::$em->persist($nodeArr[$objNodeResult->fields['catid']]);
                }
                $objNodeResult->MoveNext();
            }
        }

        while (!$objRecords->EOF) {
            
            if ($objRecords->fields['parcat'] == 0) { 
                $nodeArr[$objRecords->fields['catid']]->setParent($root);                
            } else {
                $nodeArr[$objRecords->fields['catid']]->setParent($nodeArr[$objRecords->fields['parcat']]);
            }
            $page = new \Cx\Model\ContentManager\Page();

            $this->_setPageRecords($objRecords, $nodeArr[$objRecords->fields['catid']], $page);

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
        $page->setStart(new DateTime($objResult->fields['startdate']));
        $page->setEnd(new DateTime($objResult->fields['enddate']));
        $page->setUsername($objResult->fields['username']);
        $page->setDisplay(($objResult->fields['displaystatus'] === 'on' ? 1 : 0));
        $page->setActive($objResult->fields['activestatus']);
        $page->setTarget($objResult->fields['target']);
        $page->setModule($objModules->fields['moduleName']);
        $page->setCmd($objResult->fields['cmd']);
    }
    
    function pageGrouping()
    {
        // fetch all pages
        $pages = self::$em->getRepository('Cx\Model\ContentManager\Page')->findAll();
        $group = array();
        $nodeToRemove = array();
        foreach ($pages as $page) {
            // don't group regular pages
            if (!$page->getModule()) continue;

            // group module pages
            if (!isset ($group[$page->getModule()][$page->getCmd()])) {
                 $group[$page->getModule()][$page->getCmd()] = $page->getNode();
            } else {
                if ($page->getNode()->getId() != $group[$page->getModule()][$page->getCmd()]->getId()) {
                    $nodeToRemove[] = $page->getNode()->getId();
                    $page->setNode($group[$page->getModule()][$page->getCmd()]);
                    self::$em->persist($group[$page->getModule()][$page->getCmd()]);
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
}
?>
