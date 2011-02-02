<?php
function _newsUpdate() {
	global $objDatabase, $_CONFIG, $objUpdate, $_ARRAYLANG;


	/************************************************
	* EXTENSION:	Placeholder NEWS_LINK replaced	*
	*				by NEWS_LINK_TITLE				*
	* ADDED:		Contrexx v2.1.0					*
	************************************************/
	if ($objUpdate->_isNewerVersion($_CONFIG['coreCmsVersion'], '2.1.0')) {
		$query = "
    		SELECT
	    		c.`id`,
	    		c.`content`,
	    		c.`title`,
	    		c.`metatitle`,
	    		c.`metadesc`,
	    		c.`metakeys`,
	    		c.`metarobots`,
	    		c.`css_name`,
	    		c.`redirect`,
	    		c.`expertmode`,
	    		n.`catid`,
                n.`is_validated`,
	    		n.`parcat`,
	    		n.`catname`,
	    		n.`target`,
	    		n.`displayorder`,
	    		n.`displaystatus`,
                n.`activestatus`,
	    		n.`cachingstatus`,
                n.`username`,
	    		n.`cmd`,
	    		n.`lang`,
	    		n.`startdate`,
	    		n.`enddate`,
	    		n.`protected`,
	    		n.`frontend_access_id`,
	    		n.`backend_access_id`,
	    		n.`themes_id`,
                n.`css_name`
    		FROM `".DBPREFIX."content` AS c
    		INNER JOIN `".DBPREFIX."content_navigation` AS n ON n.`catid` = c.`id`
    		WHERE n.`module` = 8 AND c.`content` LIKE '%\{NEWS_LINK\}%' AND n.`username` != 'contrexx_update_2_1_0'";
    	$objContent = $objDatabase->Execute($query);
    	if ($objContent !== false) {
    		$arrFailedPages = array();
    		while (!$objContent->EOF) {
    			$newContent = str_replace(
    				'{NEWS_LINK}',
    				'{NEWS_LINK_TITLE}',
    				$objContent->fields['content']
    			);
    			$query = "UPDATE `".DBPREFIX."content` AS c INNER JOIN `".DBPREFIX."content_navigation` AS n on n.`catid` = c.`id` SET `content` = '".addslashes($newContent)."', `username` = 'contrexx_update_2_1_0' WHERE c.`id` = ".$objContent->fields['id'];
    			if ($objDatabase->Execute($query) === false) {
					$link = CONTREXX_SCRIPT_PATH."?section=news".(empty($objContent->fields['cmd']) ? '' : "&amp;cmd=".$objContent->fields['cmd'])."&amp;langId=".$objContent->fields['lang'];
    				$arrFailedPages[$objContent->fields['id']] = array('title' => $objContent->fields['catname'], 'link' => $link);
    			} else {
	    			$objDatabase->Execute("UPDATE `".DBPREFIX."content_navigation_history` SET `is_active` = '0' WHERE `catid` = ".$objContent->fields['id']);
	    			$objDatabase->Execute("
	    				INSERT INTO `".DBPREFIX."content_navigation_history`
						SET
							`is_active` = '1',
							`catid` = ".$objContent->fields['id'].",
							`parcat` = ".$objContent->fields['parcat'].",
							`catname` = '".addslashes($objContent->fields['catname'])."',
							`target` = '".$objContent->fields['target']."',
							`displayorder` = ".$objContent->fields['displayorder'].",
							`displaystatus` = '".$objContent->fields['displaystatus']."',
							`activestatus` = '".$objContent->fields['activestatus']."',
							`cachingstatus` = '".$objContent->fields['cachingstatus']."',
							`username` = 'contrexx_update_2_1_0',
							`changelog` = ".time().",
							`cmd` = '".$objContent->fields['cmd']."',
							`lang` = ".$objContent->fields['lang'].",
							`module` = 8,
							`startdate` = '".$objContent->fields['startdate']."',
							`enddate` = '".$objContent->fields['enddate']."',
							`protected` = ".$objContent->fields['protected'].",
							`frontend_access_id` = ".$objContent->fields['frontend_access_id'].",
							`backend_access_id` = ".$objContent->fields['backend_access_id'].",
							`themes_id` = ".$objContent->fields['themes_id'].",
                            `css_name` = '".$objContent->fields['css_name']."'"
					);

					$historyId = $objDatabase->Insert_ID();

					$objDatabase->Execute("
						INSERT INTO `".DBPREFIX."content_history`
						SET
							`id` = ".$historyId.",
							`page_id` = ".$objContent->fields['id'].",
							`content` = '".addslashes($newContent)."',
							`title` = '".addslashes($objContent->fields['title'])."',
							`metatitle` = '".addslashes($objContent->fields['metatitle'])."',
							`metadesc` = '".addslashes($objContent->fields['metadesc'])."',
							`metakeys` = '".addslashes($objContent->fields['metakeys'])."',
							`metarobots` = '".addslashes($objContent->fields['metarobots'])."',
							`css_name` = '".addslashes($objContent->fields['css_name'])."',
							`redirect` = '".addslashes($objContent->fields['redirect'])."',
							`expertmode` = '".$objContent->fields['expertmode']."'
					");

					$objDatabase->Execute("
						INSERT INTO	`".DBPREFIX."content_logfile`
						SET
							`action` = 'update',
							`history_id` = ".$historyId.",
							`is_validated` = '1'
					");
    			}

    			$objContent->MoveNext();
    		}

    		if (count($arrFailedPages)) {
    			setUpdateMsg($_ARRAYLANG['TXT_UNABLE_APPLY_NEW_NEWS_LAYOUT'], 'msg');

                $pages = '<ul>';
                foreach ($arrFailedPages as $arrPage) {
                    $pages .= "<li><a href='".$arrPage['link']."' target='_blank'>".htmlentities($arrPage['title'], ENT_QUOTES, CONTREXX_CHARSET)." (".$arrPage['link'].")</a></li>";
                }
                $pages .= '</ul>';
                setUpdateMsg($pages, 'msg');
    		}
    	} else {
    		return _databaseError($query, $objDatabase->ErrorMsg());
    	}
	}



	/************************************************
	* EXTENSION:	Front- and backend permissions  *
	* ADDED:		Contrexx v2.1.0					*
	************************************************/
	$query = "SELECT 1 FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_message_protection'";
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `".DBPREFIX."module_news_settings` (`name`, `value`) VALUES ('news_message_protection', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $query = "SELECT 1 FROM `".DBPREFIX."module_news_settings` WHERE `name` = 'news_message_protection_restricted'";
    $objResult = $objDatabase->SelectLimit($query, 1);
    if ($objResult) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `".DBPREFIX."module_news_settings` (`name`, `value`) VALUES ('news_message_protection_restricted', '1')";
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'module_news');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_news'));
        return false;
    }

    if (!in_array('frontend_access_id', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_news` ADD `frontend_access_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `validated`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }
    if (!in_array('backend_access_id', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_news` ADD `backend_access_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `frontend_access_id`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



	/************************************************
	* EXTENSION:	Thunbmail Image                 *
	* ADDED:		Contrexx v2.1.0					*
	************************************************/
    $arrColumns = $objDatabase->MetaColumnNames(DBPREFIX.'module_news');
    if ($arrColumns === false) {
        setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_GETTING_DATABASE_TABLE_STRUCTURE'], DBPREFIX.'module_news'));
        return false;
    }

    if (!in_array('teaser_image_thumbnail_path', $arrColumns)) {
        $query = "ALTER TABLE `".DBPREFIX."module_news` ADD `teaser_image_thumbnail_path` TEXT NOT NULL AFTER `teaser_image_path`";
        if ($objDatabase->Execute($query) === false) {
            return _databaseError($query, $objDatabase->ErrorMsg());
        }
    }



    try{
        // delete obsolete table  contrexx_module_news_access
        UpdateUtil::drop_table(DBPREFIX.'module_news_access');
        # fix some ugly NOT NULL without defaults
        UpdateUtil::table(
            DBPREFIX . 'module_news',
            array(
                'id'                         => array('type'=>'INT(6) UNSIGNED','notnull'=>true,  'primary'     =>true,   'auto_increment' => true),
                'date'                       => array('type'=>'INT(14)',            'notnull'=>false, 'default_expr'=>'NULL'),
                'title'                      => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'text'                       => array('type'=>'MEDIUMTEXT',         'notnull'=>true),
                'redirect'                   => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'source'                     => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'url1'                       => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'url2'                       => array('type'=>'VARCHAR(250)',       'notnull'=>true,  'default'     =>''),
                'catid'                      => array('type'=>'INT(2) UNSIGNED',    'notnull'=>true,  'default'     =>0),
                'lang'                       => array('type'=>'INT(2) UNSIGNED',    'notnull'=>true,  'default'     =>0),
                'userid'                     => array('type'=>'INT(6) UNSIGNED',    'notnull'=>true,  'default'     =>0),
                'startdate'                  => array('type'=>'DATETIME',           'notnull'=>true,  'default'     =>'0000-00-00 00:00:00'),
                'enddate'                    => array('type'=>'DATETIME',           'notnull'=>true,  'default'     =>'0000-00-00 00:00:00'),
                'status'                     => array('type'=>'TINYINT(4)',         'notnull'=>true,  'default'     =>1),
                'validated'                  => array('type'=>"ENUM('0','1')",      'notnull'=>true,  'default'     =>0),
                'frontend_access_id'         => array('type'=>'INT(10) UNSIGNED',   'notnull'=>true,  'default'     =>0),
                'backend_access_id'          => array('type'=>'INT(10) UNSIGNED',   'notnull'=>true,  'default'     =>0),
                'teaser_only'                => array('type'=>"ENUM('0','1')",      'notnull'=>true,  'default'     =>0),
                'teaser_frames'              => array('type'=>'TEXT',               'notnull'=>true),
                'teaser_text'                => array('type'=>'TEXT',               'notnull'=>true),
                'teaser_show_link'           => array('type'=>'TINYINT(1) UNSIGNED','notnull'=>true,  'default'     =>1),
                'teaser_image_path'          => array('type'=>'TEXT',               'notnull'=>true),
                'teaser_image_thumbnail_path'=> array('type'=>'TEXT',               'notnull'=>true),
                'changelog'                  => array('type'=>'INT(14)',            'notnull'=>true,  'default'     =>0),
            ),
            array(#indexes
                'newsindex' =>array ('type' => 'FULLTEXT', 'fields' => array('text','title','teaser_text'))
            )
        );

    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        DBG::trace();
        return UpdateUtil::DefaultActionHandler($e);
    }

	return true;
}

?>
