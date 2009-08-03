<?php
function _forumUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	$arrSettings = array(
						'9' => array(
							'name' 	=> 'banned_words',
							'value' => 'penis enlargement,free porn,(?i:buy\\\\s*?(?:cheap\\\\s*?)?viagra)'),
						'10' => array(
							'name' 	=> 'wysiwyg_editor',
							'value' => '1'),
						'11' => array(
							'name' 	=> 'tag_count',
							'value' => '10'),
						'12' => array(
							'name' 	=> 'latest_post_per_thread',
							'value' => '1'),
						'13' => array(
							'name' 	=> 'allowed_extensions',
							'value' => '7z,aiff,asf,avi,bmp,csv,doc,fla,flv,gif,gz,gzip, jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf, png,ppt,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf, sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xml ,zip')
						);

	$arrTables = $objDatabase->MetaTables();
	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_forum_postings");
	if(!in_array('rating', $arrColumns)){
		$query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD `rating` INT NOT NULL DEFAULT '0' AFTER `is_sticky`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}


	if(!in_array('keywords', $arrColumns)){
		$query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD `keywords` TEXT NOT NULL AFTER `icon`" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}
	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_forum_postings");

	if(is_array($arrIndexes['fulltext'])){
		$query = "ALTER TABLE `".DBPREFIX."module_forum_postings` DROP INDEX `fulltext`";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}
	$query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD FULLTEXT `fulltext` (
				`keywords`,
				`subject`,
				`content`
				);" ;
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}


	if(!in_array('attachment', $arrColumns)){
		$query = "ALTER TABLE `".DBPREFIX."module_forum_postings` ADD `attachment` VARCHAR(250) NOT NULL DEFAULT '' AFTER `content`" ;
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	foreach ($arrSettings as $id => $arrSetting) {
		$query = "SELECT 1 FROM `".DBPREFIX."module_forum_settings` WHERE `name`= '".$arrSetting['name']."'" ;
		if (($objRS = $objDatabase->Execute($query)) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
		if($objRS->RecordCount() == 0){
			$query = "INSERT INTO `".DBPREFIX."module_forum_settings`
							 (`id`, `name`, `value`)
					  VALUES (".$id.", '".$arrSetting['name']."', '".addslashes($arrSetting['value'])."')" ;
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}


    try{
        UpdateUtil::table(
            DBPREFIX.'module_forum_rating',
            array(
                'id'         => array('type' => 'INT(11)', 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id'    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'post_id'    => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0'),
                'time'       => array('type' => 'INT(11)', 'notnull' => true, 'default' => '0')
            ),
            array(
                'user_id'    => array('fields' => array('user_id','post_id'))
            )
        );
    }
    catch (UpdateException $e) {
        return UpdateUtil::DefaultActionHandler($e);
    }


    return true;
}
?>
