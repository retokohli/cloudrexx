<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../../config/configuration.php';
require_once '../../../core/API.php';
require_once '../../../config/doctrine.php';

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
        
        $objDatabase->Execute('TRUNCATE TABLE `pages`');
        $objDatabase->Execute('TRUNCATE TABLE `nodes`');  

        $objNodeResult = $objDatabase->Execute('SELECT catid FROM `'.DBPREFIX.'content_navigation` ORDER BY catid ASC');

        while (!$objNodeResult->EOF) {
            
            $nodeArr[$objNodeResult->fields['catid']] = new \Cx\Model\ContentManager\Node();             
            
            self::$em->persist($nodeArr[$objNodeResult->fields['catid']]);
            
            $objNodeResult->MoveNext();
        }

        $objResult = $objDatabase->Execute('SELECT content.content,
                                            content.title,
                                            content.metatitle,
                                            content.metadesc,
                                            content.metakeys,
                                            content.metarobots,
                                            content.css_name,
                                            nav.catid,
                                            nav.parcat,
                                            nav.lang,
                                            nav.cachingstatus,
                                            nav.changelog,
                                            nav.custom_content,
                                            nav.startdate,
                                            nav.enddate,
                                            nav.username,
                                            nav.displaystatus,
                                            nav.activestatus,
                                            nav.target,
                                            nav.module,
                                            nav.cmd
                                            FROM `'.DBPREFIX.'content` AS content 
                                            INNER JOIN `'.DBPREFIX.'content_navigation` AS nav
                                            ON content.id = nav.catid
                                            ORDER BY nav.parcat ASC, nav.displayorder ASC');
        
        while (!$objResult->EOF) {

            if ($objResult->fields['parcat'] == 0) {
                $nodeArr[$objResult->fields['catid']]->setParent($root);                
            } else {
                $nodeArr[$objResult->fields['catid']]->setParent($nodeArr[$objResult->fields['parcat']]);
            }

            $p = new \Cx\Model\ContentManager\Page();
            
            // Convert the changelog value from Unix time stamp to date for UpdatedAt function
            $updatedDate = Date('Y-m-d H:i:s',$objResult->fields['changelog']);

            $p->setNode($nodeArr[$objResult->fields['catid']]); 
            $p->setLang($objResult->fields['lang']);
            $p->setCaching($objResult->fields['cachingstatus']);
            $p->setUpdatedAt(new DateTime($updatedDate));
            $p->setTitle($objResult->fields['title']);
            $p->setContent($objResult->fields['content']);            
            $p->setCustomContent($objResult->fields['custom_content']);
            $p->setCssName($objResult->fields['css_name']);
            $p->setMetatitle($objResult->fields['metatitle']);
            $p->setMetadesc($objResult->fields['metadesc']);
            $p->setMetakeys($objResult->fields['metakeys']);
            $p->setMetarobots($objResult->fields['metarobots']);
            $p->setStart(new DateTime($objResult->fields['startdate']));
            $p->setEnd(new DateTime($objResult->fields['enddate']));
            $p->setUsername($objResult->fields['username']);
            $p->setDisplay(($objResult->fields['displaystatus'] === 'on' ? 1 : 0));
            $p->setActive($objResult->fields['activestatus']);
            $p->setTarget($objResult->fields['target']);
            $p->setModule($objResult->fields['module']);

            self::$em->persist($p);

            self::$em->flush();

            $objResult->MoveNext();
        }
        
        $objDatabase->Execute('TRUNCATE TABLE `ext_log_entries`');     

        $actionArr = array(
                      'new'    => 'create',
                      'update' => 'update',
                      'delete' => 'remove',
                     );
          
        $objLog = $objDatabase->Execute('SELECT cnlog.id AS logId,
                                         cnlog.action AS action,
                                         cnlog.history_id AS historyId,
                                         navHis.username,
                                         navHis.changelog,
                                         cnHis.page_id
                                         FROM `'.DBPREFIX.'content_logfile` AS cnlog
                                         LEFT JOIN `'.DBPREFIX.'content_navigation_history` AS navHis ON navHis.id = cnlog.history_id
                                         LEFT JOIN `'.DBPREFIX.'content_history` AS cnHis ON navHis.id = cnHis.id');
        while (!$objLog->EOF) {
            $objDatabase->Execute('INSERT INTO `ext_log_entries` SET
                                     `id`        = '.$objLog->fields['logid'].',
                                     `action`    = "'.$actionArr[$objLog->fields['action']].'",
                                     `logged_at` = '.date('Y m d H:i:s',$objLog->fields['changelog']).',
                                     `version`   = '.$objLog->fields['historyId'].',
                                     `username`  = "'.$objLog->fields['username'].'"');
            $objLog->MoveNext();
        }                
    }
}
?>
