<?php
function _forumUpdate()
{
	global $objDatabase;

	/********************************************************************
	 * Create tables
	 *
	 */
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_forum_access", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_access` (
						`category_id` SMALLINT UNSIGNED NOT NULL ,
						`group_id` SMALLINT UNSIGNED NOT NULL ,
						`read` SET( '0', '1' ) NOT NULL DEFAULT '0',
						`write` SET( '0', '1' ) NOT NULL DEFAULT '0',
						`edit` SET( '0', '1' ) NOT NULL DEFAULT '0',
						`delete` SET( '0', '1' ) NOT NULL DEFAULT '0',
						`move` SET( '0', '1' ) NOT NULL DEFAULT '0',
						`close` SET( '0', '1' ) NOT NULL DEFAULT '0',
						`sticky` SET( '0', '1' ) NOT NULL DEFAULT '0',
						PRIMARY KEY ( `category_id` , `group_id` )
					) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_forum_categories", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_categories` (
						`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						`parent_id` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
						`order_id` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
						`status` SET( '0', '1' ) NOT NULL DEFAULT '0',
						INDEX ( `parent_id` )
					) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_forum_categories_lang", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_categories_lang` (
						`category_id` SMALLINT UNSIGNED NOT NULL ,
						`lang_id` SMALLINT UNSIGNED NOT NULL ,
						`name` VARCHAR( 100 ) NOT NULL ,
						`description` TEXT NOT NULL ,
						PRIMARY KEY ( `category_id` , `lang_id` )
					) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_forum_notification", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_notification` (
						`thread_id` INTEGER UNSIGNED NOT NULL,
						`user_id` SMALLINT UNSIGNED NOT NULL,
						`is_notified` SET('0','1') NOT NULL DEFAULT '0',
						PRIMARY KEY (`thread_id`, `user_id`)
					) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_forum_postings", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_postings` (
						`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
						`category_id` SMALLINT UNSIGNED NOT NULL,
						`thread_id` INTEGER UNSIGNED NOT NULL DEFAULT '0',
						`prev_post_id` INTEGER UNSIGNED NOT NULL DEFAULT '0',
						`user_id` SMALLINT UNSIGNED NOT NULL,
						`time_created` INTEGER(14) UNSIGNED NOT NULL,
						`time_edited` INTEGER(14) UNSIGNED NOT NULL,
						`is_locked` SET('0','1') NOT NULL DEFAULT '0',
						`is_sticky` SET('0','1') NOT NULL DEFAULT '0',
						`views` INTEGER UNSIGNED NOT NULL DEFAULT '0',
						`icon` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
						`subject` VARCHAR(250) NOT NULL,
						`content` TEXT NOT NULL,
						INDEX(`category_id`,`thread_id`,`prev_post_id`,`user_id`),
						FULLTEXT KEY `fulltext` (`subject`, `content`)
					) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_forum_settings", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_settings` (
						`id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
						`name` VARCHAR(50) NOT NULL,
						`value` TEXT NOT NULL
					) TYPE = MYISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_forum_statistics", $arrTables)) {
			$query = "CREATE TABLE `".DBPREFIX."module_forum_statistics` (
						`category_id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY ,
						`thread_count` INTEGER UNSIGNED NOT NULL,
						`post_count` INTEGER UNSIGNED NOT NULL ,
						`last_post_id` INTEGER UNSIGNED NOT NULL
					) TYPE = MYISAM ";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenkbank konnte nicht ermittelt werden!";
		return false;
	}

	/********************************************************************
	 * Insert data
	 *
	 */
	//modules table forum entry
	$query = "	SELECT 1 FROM `".DBPREFIX."modules`
				WHERE `name` = 'forum' AND `id` = 20";
	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
		if($objRS->RecordCount() < 1){
			$query = "	INSERT INTO `".DBPREFIX."modules`
						VALUES (20, 'forum', 'TXT_FORUM_MODULE_DESCRIPTION', 'y', 0, 0)";
			if($objDatabase->Execute($query) === false){
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}else{
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//


	//frontend groups:
	$arrGroupIds = array();
	$arrBoolNewGroups = array(false, false);
	//make sure that at least the following groups exist
	$arrForumUserGroupNames =	array(
									'Forum: Administratoren',
									'Forum: Benutzer'
								);

	$arrForumUserGroupDescs =	array(
									'Gruppe der Forenadministratoren',
									'Gruppe der Forenbenutzer'
								);

	foreach($arrForumUserGroupNames as $index => $strUserGroup){
		$query = "	SELECT `group_id` FROM `".DBPREFIX."access_user_groups`
					WHERE `type` = 'frontend'
					AND `group_name` = '".$strUserGroup."'";
		if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){
			if($objRS->RecordCount() < 1){
				$arrBoolNewGroups[$index] = true;
				$query = "	INSERT INTO `".DBPREFIX."access_user_groups`
						 	VALUES (NULL, '".$strUserGroup."', '".$arrForumUserGroupDescs[$index]."', 1, 'frontend')";
				if($objDatabase->Execute($query) !== false){
					$arrGroupIds[$index] = $objDatabase->Insert_ID();
				}else{
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}else{
				$arrGroupIds[$index] = $objRS->fields['group_id'];
			}
		}else{
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	if($arrBoolNewGroups[0]){
		//insert access rows for admin-group
		$arrAccessSQL = array(
							"INSERT INTO `".DBPREFIX."module_forum_access` VALUES(1, ".$arrGroupIds[0].", '1', '1', '1', '1', '1', '1', '1')",
							"INSERT INTO `".DBPREFIX."module_forum_access` VALUES(2, ".$arrGroupIds[0].", '1', '1', '1', '1', '1', '1', '1')"
						);
		foreach($arrAccessSQL as $query) {
			if($objDatabase->Execute($query) === false){
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	if($arrBoolNewGroups[1]){
		//insert access rows for user-group
		$arrAccessSQL = array(	"INSERT INTO `".DBPREFIX."module_forum_access` VALUES(1, ".$arrGroupIds[1].", '1', '1', '0', '0', '0', '0', '0')",
								"INSERT INTO `".DBPREFIX."module_forum_access` VALUES(2, ".$arrGroupIds[1].", '1', '1', '0', '0', '0', '0', '0')"
							);
		foreach($arrAccessSQL as $query) {
			if($objDatabase->Execute($query) === false){
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	//insert categories
	$query = "	SELECT 1 FROM `".DBPREFIX."module_forum_categories`";
	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){//if no categories: insert example categories
		if($objRS->RecordCount() < 1){
			$arrCategorySQL = 	array(
									"INSERT INTO `".DBPREFIX."module_forum_categories` VALUES (1, 0, 1, '1')",
									"INSERT INTO `".DBPREFIX."module_forum_categories` VALUES (2, 1, 1, '1')"
								);
			foreach ($arrCategorySQL as $query) {
				if($objDatabase->Execute($query) === false){
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}
	}

	//insert categorie language specific data
	$query = "	SELECT 1 FROM `".DBPREFIX."module_forum_categories_lang`";
	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){//if no lang data: insert data for example categories
		if($objRS->RecordCount() < 1){
			$arrCategorySQL = 	array(
									"INSERT INTO `".DBPREFIX."module_forum_categories_lang` VALUES (1, 1, 'Beispielskategorie', 'Diese Kategorie soll als Beispiel dienen.')",
									"INSERT INTO `".DBPREFIX."module_forum_categories_lang` VALUES (1, 2, 'Example: category', 'This category is just an example.')",
									"INSERT INTO `".DBPREFIX."module_forum_categories_lang` VALUES (2, 1, 'Beispielsforum', 'Dieses Forum soll Ihnen die Fähigkeiten des neuen Forenmoduls demonstrieren.')",
									"INSERT INTO `".DBPREFIX."module_forum_categories_lang` VALUES (2, 2, 'Example: forum', 'This forum should demonstrate you the possibilities of the new forum-module.')"
								);
			foreach ($arrCategorySQL as $query) {
				if($objDatabase->Execute($query) === false){
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		}
	}

	//insert statistics data
	$query = "	SELECT 1 FROM `".DBPREFIX."module_forum_statistics`";
	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){//no data for stats yet, insert default example
		if($objRS->RecordCount() < 1){
			$query =	"INSERT INTO `".DBPREFIX."module_forum_statistics` VALUES (2,1,1,1)";
			if($objDatabase->Execute($query) === false){
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	//insert postings data
	$query = "	SELECT 1 FROM `".DBPREFIX."module_forum_postings`";
	if(($objRS = $objDatabase->SelectLimit($query, 1)) !== false){//no data for stats yet, insert default example
		if($objRS->RecordCount() < 1){
			$query =	"INSERT INTO `".DBPREFIX."module_forum_postings` VALUES ( 1, 2, 0, 0, 1, 1155045563, 0, '0', '0', 0, 2, 'Das neue Forenmodul', 'Sehr geehrter Contrexx-User, Wir freuen uns, Ihnen das neue Foren-Modul präsentieren zu dürfen. Mit freundlichen Gr&uuml;ssen Ihr Contrexx-Team')";
			if($objDatabase->Execute($query) === false){
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	/**
	 * *******************************************************************
	 * Insert settings
	 *
	 */
	$arrForumSettings = array(
		array(
			'name'	=> 'thread_paging',
			'value'	=> '20',
		),
		array(
			'name'	=> 'posting_paging',
			'value'	=> '10',
		),
		array(
			'name'	=> 'latest_entries_count',
			'value'	=> '5',
		),
		array(
			'name'	=> 'block_template',
			'value'	=> '<div id="forum">
    <div class="div_board">
        <div class="div_title">[[TXT_FORUM_LATEST_ENTRIES]]</div>
	    <table cellspacing="0" cellpadding="0">
	    	<tr class="row3">
				<th width="65%" style="text-align: left;">[[TXT_FORUM_THREAD]]</th>
				<th width="15%" style="text-align: left;">[[TXT_FORUM_OVERVIEW_FORUM]]</th>
			<!--<th width="15%" style="text-align: left;">[[TXT_FORUM_THREAD_STRATER]]</th>-->
				<th width="1%" style="text-align: left;">[[TXT_FORUM_POST_COUNT]]</th>
				<th width="4%" style="text-align: left;">[[TXT_FORUM_THREAD_CREATE_DATE]]</th>
			</tr>
			<!-- BEGIN latestPosts -->
			<tr class="row_[[FORUM_ROWCLASS]]">
				<td>[[FORUM_THREAD]]</td>
				<td>[[FORUM_FORUM_NAME]]</td>
			<!--<td>[[FORUM_THREAD_STARTER]]</td>-->
				<td>[[FORUM_POST_COUNT]]</td>
				<td>[[FORUM_THREAD_CREATE_DATE]]</td>
			</tr>
			<!-- END latestPosts -->
		</table>
	</div>
</div>'

		),
		array(
			'name'	=> 'notification_template',
			'value'	=> '[[FORUM_USERNAME]],

Es wurde ein neuer Beitrag im Thema \"[[FORUM_THREAD_SUBJECT]]\", gestartet
von \"[[FORUM_THREAD_STARTER]]\", geschrieben.

Der neue Beitrag umfasst folgenden Inhalt:

-----------------NACHRICHT START-----------------
-----Betreff-----
[[FORUM_LATEST_SUBJECT]]

----Nachricht----
[[FORUM_LATEST_MESSAGE]]
-----------------NACHRICHT ENDE------------------

Um den ganzen Diskussionsverlauf zu sehen oder zur Abmeldung dieser
Benachrichtigung, besuchen Sie folgenden Link:
[[FORUM_THREAD_URL]]'
		),
		array(
			'name'	=> 'notification_subject',
			'value'	=> 'Neuer Beitrag in \"[[FORUM_THREAD_SUBJECT]]\"',
		),
		array(
			'name'	=> 'notification_from_email',
			'value'	=> 'noreply@example.com',
		),
		array(
			'name'	=> 'notification_from_name',
			'value'	=> 'nobody',
		)
	);

	foreach($arrForumSettings as $id => $arrSetting){
		$query = "SELECT `id` FROM ".DBPREFIX."module_forum_settings WHERE `name`='".$arrSetting['name']."'";
		$objRS = $objDatabase->SelectLimit($query, 1);
		if ($objRS === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		} elseif ($objRS->RecordCount() == 0) {
			$query = "INSERT INTO ".DBPREFIX."module_forum_settings (`id`, `name` , `value`) VALUES ( ".($id+1).", '".$arrSetting['name']."', '".$arrSetting['value']."')";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	$query = "	SELECT 1 FROM `".DBPREFIX."settings`
				WHERE `setname` = 'forumHomeContent'";
	$objRS = $objDatabase->SelectLimit($query, 1);
	if ($objRS === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	} elseif ($objRS->RecordCount() == 0) {
		$query = "INSERT INTO `".DBPREFIX."settings` VALUES (60, 'forumHomeContent', '1', 20);";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrBackendAreas = array(
		array(
			'id' 		=> 106,
			'parent_id' => 2,
			'type' 		=> 'navigation',
			'name'		=> 'TXT_FORUM',
			'active'	=> 1,
			'uri'		=> 'index.php?cmd=forum',
			'target'	=> '_self',
			'module_id'	=> 20,
			'order_id'	=> 19,
			'access_id'	=> 106,
		),
		array(
			'id' 		=> 107,
			'parent_id' => 106,
			'type' 		=> 'function',
			'name'		=> 'TXT_FORUM_MENU_CATEGORIES',
			'active'	=> 1,
			'uri'		=> 'index.php?cmd=forum',
			'target'	=> '_self',
			'module_id'	=> 20,
			'order_id'	=> 1,
			'access_id'	=> 107,
		),
		array(
			'id' 		=> 108,
			'parent_id' => 106,
			'type' 		=> 'function',
			'name'		=> 'TXT_FORUM_MENU_SETTINGS',
			'active'	=> 1,
			'uri'		=> 'index.php?cmd=forum&amp;act=settings',
			'target'	=> '_self',
			'module_id'	=> 20,
			'order_id'	=> 2,
			'access_id'	=> 108,
		),
	);

	foreach($arrBackendAreas as $arrBackendArea){
		$query = "SELECT `area_id` FROM ".DBPREFIX."backend_areas WHERE `area_id` = ".$arrBackendArea['id'];
		$objRS = $objDatabase->SelectLimit($query, 1);
		if ($objRS === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		} elseif ($objRS->RecordCount() == 0) {
			$query = "INSERT INTO ".DBPREFIX."backend_areas (	`area_id`, `parent_area_id`, `type`,
																`area_name`, `is_active`, `uri`,
																`target`, `module_id`, `order_id`,
																`access_id`)
													VALUES ( 	".$arrBackendArea['id'].", ".$arrBackendArea['parent_id'].", '".$arrBackendArea['type']."',
																'".$arrBackendArea['name']."', ".$arrBackendArea['active'].", '".$arrBackendArea['uri']."',
																'".$arrBackendArea['target']."', ".$arrBackendArea['module_id'].", ".$arrBackendArea['order_id'].",
																".$arrBackendArea['access_id'].")";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	/**
	 * post 1.0.9.10.1
	 */
	$arrColumns = $objDatabase->MetaColumnNames(DBPREFIX."module_forum_notification");
	if (!is_array($arrColumns)) {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_forum_notification konnte nicht ermittelt werden!";
		return false;
	}

	if (!in_array('category_id', $arrColumns)) {
		$query = "ALTER TABLE `".DBPREFIX."module_forum_notification` ADD `category_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' FIRST";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	$arrPrimaryKeys = $objDatabase->MetaPrimaryKeys(DBPREFIX."module_forum_notification");
	if (!is_array($arrPrimaryKeys)) {
		print "Die Primärschlüssel der Datenbanktabelle ".DBPREFIX."module_forum_notification konnten nicht ermittelt werden!";
		return false;
	}

	if(count($arrPrimaryKeys) < 3){
		$query = "ALTER TABLE `".DBPREFIX."module_forum_notification` DROP PRIMARY KEY";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}


		$query = "ALTER TABLE `".DBPREFIX."module_forum_notification` ADD PRIMARY KEY ( `category_id` , `thread_id` , `user_id` )";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}
	return true;
}
?>