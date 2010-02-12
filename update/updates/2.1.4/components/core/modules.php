<?php
function _updateModules()
{
	global $objDatabase;

	$arrModules = array(
		array(
			'id'					=> 0,
			'name'					=> '',
			'description_variable'	=> '',
			'status'				=> 'n',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 1,
			'name'					=> 'core',
			'description_variable'	=> 'TXT_CORE_MODULE_DESCRIPTION',
			'status'				=> 'n',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 2,
			'name'					=> 'stats',
			'description_variable'	=> 'TXT_STATS_MODULE_DESCRIPTION',
			'status'				=> 'n',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 3,
			'name'					=> 'gallery',
			'description_variable'	=> 'TXT_GALLERY_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 4,
			'name'					=> 'newsletter',
			'description_variable'	=> 'TXT_NEWSLETTER_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 5,
			'name'					=> 'search',
			'description_variable'	=> 'TXT_SEARCH_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 6,
			'name'					=> 'contact',
			'description_variable'	=> 'TXT_CONTACT_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 7,
			'name'					=> 'block',
			'description_variable'	=> 'TXT_BLOCK_MODULE_DESCRIPTION',
			'status'				=> 'n',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 8,
			'name'					=> 'news',
			'description_variable'	=> 'TXT_NEWS_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 9,
			'name'					=> 'media1',
			'description_variable'	=> 'TXT_MEDIA_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 10,
			'name'					=> 'guestbook',
			'description_variable'	=> 'TXT_GUESTBOOK_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 11,
			'name'					=> 'sitemap',
			'description_variable'	=> 'TXT_SITEMAP_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 12,
			'name'					=> 'directory',
			'description_variable'	=> 'TXT_LINKS_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 13,
			'name'					=> 'ids',
			'description_variable'	=> 'TXT_IDS_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 14,
			'name'					=> 'error',
			'description_variable'	=> 'TXT_ERROR_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 15,
			'name'					=> 'home',
			'description_variable'	=> 'TXT_HOME_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 16,
			'name'					=> 'shop',
			'description_variable'	=> 'TXT_SHOP_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 17,
			'name'					=> 'voting',
			'description_variable'	=> 'TXT_VOTING_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 18,
			'name'					=> 'login',
			'description_variable'	=> 'TXT_LOGIN_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 19,
			'name'					=> 'docsys',
			'description_variable'	=> 'TXT_DOC_SYS_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 20,
			'name'					=> 'forum',
			'description_variable'	=> 'TXT_FORUM_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 21,
			'name'					=> 'calendar',
			'description_variable'	=> 'TXT_CALENDAR_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 22,
			'name'					=> 'feed',
			'description_variable'	=> 'TXT_FEED_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 23,
			'name'					=> 'community',
			'description_variable'	=> 'TXT_COMMUNITY_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 24,
			'name'					=> 'media2',
			'description_variable'	=> 'TXT_MEDIA_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 25,
			'name'					=> 'media3',
			'description_variable'	=> 'TXT_MEDIA_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 26,
			'name'					=> 'fileBrowser',
			'description_variable'	=> 'TXT_FILEBROWSER_DESCRIPTION',
			'status'				=> 'n',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 27,
			'name'					=> 'recommend',
			'description_variable'	=> 'TXT_RECOMMEND_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 30,
			'name'					=> 'livecam',
			'description_variable'	=> 'TXT_LIVECAM_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 31,
			'name'					=> 'memberdir',
			'description_variable'	=> 'TXT_MEMBERDIR_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 32,
			'name'					=> 'nettools',
			'description_variable'	=> 'TXT_NETTOOLS_MODULE_DESCRIPTION',
			'status'				=> 'n',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 33,
			'name'					=> 'market',
			'description_variable'	=> 'TXT_MARKET_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 35,
			'name'					=> 'podcast',
			'description_variable'	=> 'TXT_PODCAST_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 38,
			'name'					=> 'egov',
			'description_variable'	=> 'TXT_EGOVERNMENT_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 0
		),
		array(
			'id'					=> 39,
			'name'					=> 'media4',
			'description_variable'	=> 'TXT_MEDIA_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 41,
			'name'					=> 'alias',
			'description_variable'	=> 'TXT_ALIAS_MODULE_DESCRIPTION',
			'status'				=> 'n',
			'is_required'			=> 0,
			'is_core'				=> 1
		),
		array(
			'id'					=> 44,
			'name'					=> 'imprint',
			'description_variable'	=> 'TXT_IMPRINT_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 45,
			'name'					=> 'agb',
			'description_variable'	=> 'TXT_AGB_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		),
		array(
			'id'					=> 46,
			'name'					=> 'privacy',
			'description_variable'	=> 'TXT_PRIVACY_MODULE_DESCRIPTION',
			'status'				=> 'y',
			'is_required'			=> 1,
			'is_core'				=> 1
		)
	);

	$query = "TRUNCATE TABLE ".DBPREFIX."modules";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	// add modules
	foreach ($arrModules as $arrModule) {
		$query = "INSERT INTO ".DBPREFIX."modules ( `id` , `name` , `description_variable` , `status` , `is_required` , `is_core` ) VALUES ( ".$arrModule['id']." , '".$arrModule['name']."', '".$arrModule['description_variable']."', '".$arrModule['status']."', '".$arrModule['is_required']."', '".$arrModule['is_core']."')";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

    $query = "
    ALTER TABLE ".DBPREFIX."modules
    CHANGE COLUMN id id integer(2) UNSIGNED default NULL;
    ";
    if (!$objDatabase->Execute($query)) {
    	return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}
?>