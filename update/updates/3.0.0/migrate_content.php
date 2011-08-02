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
                
        $objResult = $objDatabase->Execute('SELECT content.*, nav.* FROM `'.DBPREFIX.'content` as content LEFT JOIN `'.DBPREFIX.'content_navigation` as nav ON content.id = nav.catid');
        
        while (!$objResult->EOF) {
            $root = new \Cx\Model\ContentManager\Node();
            $n    = new \Cx\Model\ContentManager\Node();        
            $n->setParent($root);

            $p = new \Cx\Model\ContentManager\Page();            
            
            $p->setLang($objResult->fields['lang']);
            $p->setTitle($objResult->fields['title']);
            $p->setContent($objResult->fields['content']);
            $p->setNode($n);
            $p->setUsername($objResult->fields['username']);

            self::$em->persist($root);
            self::$em->persist($n);

            self::$em->persist($p);

            self::$em->flush();

            $objResult->MoveNext();        
        }       
    }    
}

?>
