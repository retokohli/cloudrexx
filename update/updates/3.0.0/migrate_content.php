<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require('../../../config/configuration.php');
require('../../../core/API.php');
require('../../../config/doctrine.php');

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
        
        $objDatabase->Execute('TRUNCATE TABLE `pages`');
        $objDatabase->Execute('TRUNCATE TABLE `nodes`');        
        $objResult = $objDatabase->Execute('SELECT content.*, nav.* 
                                            FROM `'.DBPREFIX.'content` AS content 
                                            INNER JOIN `'.DBPREFIX.'content_navigation` AS nav
                                            ON content.id = nav.catid
                                            ORDER BY nav.parcat ASC, nav.displayorder ASC');        
        
        while (!$objResult->EOF) {
            $n    = new \Cx\Model\ContentManager\Node(); 
            
            $nodeArr[$objResult->fields['id']] = $n;
            
            if ($objResult->fields['parcat'] == 0) {          
                $n->setParent($root);
                self::$em->persist($root);
            } else {
                $n->setParent($nodeArr[$objResult->fields['parcat']]);
            }          

            $p = new \Cx\Model\ContentManager\Page();
            
            // Convert the changelog value from Unix time stamp to date for UpdatedAt function
            $updatedDate = Date('Y-m-d H:i:s',$objResult->fields['changelog']);

            $p->setNode($n); 
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
            
            self::$em->persist($n);            
            self::$em->persist($p);

            self::$em->flush();

            $objResult->MoveNext();
        }       
    }    
}
?>
