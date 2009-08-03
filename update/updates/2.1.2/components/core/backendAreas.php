<?php
function _updateBackendAreas()
{
	global $objDatabase;

	$arrBackendAreas = array(
		array(
			'area_id'			=> 1,
			'parent_area_id'	=> 0,
			'type'				=> 'group',
			'area_name'			=> 'TXT_CONTENT_MANAGEMENT',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 0,
			'access_id'			=> 1
		),
		array(
			'area_id'			=> 55,
			'parent_area_id'	=> 0,
			'type'				=> 'group',
			'area_name'			=> 'TXT_HELP_SUPPORT',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 5,
			'access_id'			=> 55
		),
		array(
			'area_id'			=> 4,
			'parent_area_id'	=> 0,
			'type'				=> 'group',
			'area_name'			=> 'TXT_SYSTEM_INFO',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_new',
			'module_id'			=> 1,
			'order_id'			=> 4,
			'access_id'			=> 4
		),
		array(
			'area_id'			=> 3,
			'parent_area_id'	=> 0,
			'type'				=> 'group',
			'area_name'			=> 'TXT_ADMINISTRATION',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 3,
			'access_id'			=> 3
		),
		array(
			'area_id'			=> 2,
			'parent_area_id'	=> 0,
			'type'				=> 'group',
			'area_name'			=> 'TXT_MODULE',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 2,
			'access_id'			=> 2
		),
		array(
			'area_id'			=> 76,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_BLOCK_SYSTEM',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=block',
			'target'			=> '_self',
			'module_id'			=> 7,
			'order_id'			=> 9,
			'access_id'			=> 76
		),
		array(
			'area_id'			=> 75,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_CONTENT_HISTORY',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=workflow',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 3,
			'access_id'			=> 75
		),
		array(
			'area_id'			=> 32,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_IMAGE_ADMINISTRATION',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=media&amp;archive=content',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 5,
			'access_id'			=> 32
		),
		array(
			'area_id'			=> 90,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_CONTACTS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=contact',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 7,
			'access_id'			=> 84
		),
		array(
			'area_id'			=> 10,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_NEWS_MANAGER',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=news',
			'target'			=> '_self',
			'module_id'			=> 8,
			'order_id'			=> 6,
			'access_id'			=> 10
		),
		array(
			'area_id'			=> 8,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SITE_PREVIEW',
			'is_active'			=> 1,
			'uri'				=> '../index.php',
			'target'			=> '_blank',
			'module_id'			=> 1,
			'order_id'			=> 10,
			'access_id'			=> 8
		),
		array(
			'area_id'			=> 5,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_NEW_PAGE',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=content&amp;act=new',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 1,
			'access_id'			=> 5
		),
		array(
			'area_id'			=> 6,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_CONTENT_MANAGER',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=content',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 2,
			'access_id'			=> 6
		),
		array(
			'area_id'			=> 7,
			'parent_area_id'	=> 1,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_MEDIA_MANAGER',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=media&amp;archive=archive1',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 4,
			'access_id'			=> 7
		),
		array(
			'area_id'			=> 59,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_LINKS_MODULE_DESCRIPTION',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=directory',
			'target'			=> '_self',
			'module_id'			=> 12,
			'order_id'			=> 9,
			'access_id'			=> 59
		),
		array(
			'area_id'			=> 82,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_LIVECAM',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=livecam',
			'target'			=> '_self',
			'module_id'			=> 30,
			'order_id'			=> 15,
			'access_id'			=> 82
		),
		array(
			'area_id'			=> 89,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_MEMBERDIR',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=memberdir',
			'target'			=> '_self',
			'module_id'			=> 31,
			'order_id'			=> 13,
			'access_id'			=> 83
		),
		array(
			'area_id'			=> 61,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_COMMUNITY',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=community',
			'target'			=> '_self',
			'module_id'			=> 23,
			'order_id'			=> 11,
			'access_id'			=> 60
		),
		array(
			'area_id'			=> 109,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_EGOVERNMENT',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=egov',
			'target'			=> '_self',
			'module_id'			=> 38,
			'order_id'			=> 20,
			'access_id'			=> 109
		),
		array(
			'area_id'			=> 64,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_RECOMMEND',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=recommend',
			'target'			=> '_self',
			'module_id'			=> 27,
			'order_id'			=> 10,
			'access_id'			=> 64
		),
		array(
			'area_id'			=> 93,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_PODCAST',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=podcast',
			'target'			=> '_self',
			'module_id'			=> 35,
			'order_id'			=> 17,
			'access_id'			=> 87
		),
		array(
			'area_id'			=> 9,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_GUESTBOOK',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=guestbook',
			'target'			=> '_self',
			'module_id'			=> 10,
			'order_id'			=> 0,
			'access_id'			=> 9
		),
		array(
			'area_id'			=> 11,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_DOC_SYS_MANAGER',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=docsys',
			'target'			=> '_self',
			'module_id'			=> 19,
			'order_id'			=> 0,
			'access_id'			=> 11
		),
		array(
			'area_id'			=> 12,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_THUMBNAIL_GALLERY',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=gallery',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 0,
			'access_id'			=> 12
		),
		array(
			'area_id'			=> 13,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SHOP',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=shop',
			'target'			=> '_self',
			'module_id'			=> 16,
			'order_id'			=> 0,
			'access_id'			=> 13
		),
		array(
			'area_id'			=> 14,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_VOTING',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=voting',
			'target'			=> '_self',
			'module_id'			=> 17,
			'order_id'			=> 0,
			'access_id'			=> 14
		),
		array(
			'area_id'			=> 16,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_CALENDAR',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=calendar',
			'target'			=> '_self',
			'module_id'			=> 21,
			'order_id'			=> 0,
			'access_id'			=> 16
		),
		array(
			'area_id'			=> 25,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_NEWSLETTER',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=newsletter',
			'target'			=> '_self',
			'module_id'			=> 4,
			'order_id'			=> 0,
			'access_id'			=> 25
		),
		array(
			'area_id'			=> 106,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_FORUM',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=forum',
			'target'			=> '_self',
			'module_id'			=> 20,
			'order_id'			=> 19,
			'access_id'			=> 106
		),
		array(
			'area_id'			=> 98,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_MARKET_MODULE_DESCRIPTION',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=market',
			'target'			=> '_self',
			'module_id'			=> 33,
			'order_id'			=> 14,
			'access_id'			=> 98
		),
		array(
			'area_id'			=> 27,
			'parent_area_id'	=> 2,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_NEWS_SYNDICATION',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=feed',
			'target'			=> '_self',
			'module_id'			=> 22,
			'order_id'			=> 0,
			'access_id'			=> 27
		),
		array(
			'area_id'			=> 110,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_ALIAS_ADMINISTRATION',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=alias',
			'target'			=> '_self',
			'module_id'			=> 41,
			'order_id'			=> 8,
			'access_id'			=> 115
		),
		array(
			'area_id'			=> 19,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_STATS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=stats',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 6,
			'access_id'			=> 19
		),
		array(
			'area_id'			=> 17,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SYSTEM_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=settings',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 7,
			'access_id'			=> 17
		),
		array(
			'area_id'			=> 18,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_USER_ADMINISTRATION',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=user',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 1,
			'access_id'			=> 18
		),
		array(
			'area_id'			=> 20,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_BACKUP',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=backup',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 2,
			'access_id'			=> 20
		),
		array(
			'area_id'			=> 21,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_DESIGN_MANAGEMENT',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=skins',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 3,
			'access_id'			=> 21
		),
		array(
			'area_id'			=> 23,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_MODULE_MANAGER',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=modulemanager',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 5,
			'access_id'			=> 23
		),
		array(
			'area_id'			=> 22,
			'parent_area_id'	=> 3,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_LANGUAGE_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=language',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 4,
			'access_id'			=> 22
		),
		array(
			'area_id'			=> 58,
			'parent_area_id'	=> 4,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SYSTEM_UPDATE',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=systemUpdate',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 3,
			'access_id'			=> 58
		),
		array(
			'area_id'			=> 24,
			'parent_area_id'	=> 4,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SERVER_INFO',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=server',
			'target'			=> '_self',
			'module_id'			=> 1,
			'order_id'			=> 1,
			'access_id'			=> 24
		),
		array(
			'area_id'			=> 54,
			'parent_area_id'	=> 4,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_NETWORK_TOOLS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=nettools',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 2,
			'access_id'			=> 54
		),
		array(
			'area_id'			=> 53,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_COPY_DELETE_SITES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 53
		),
		array(
			'area_id'			=> 35,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_EDIT_PAGES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 35
		),
		array(
			'area_id'			=> 36,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ACCESS_CONTROL',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 36
		),
		array(
			'area_id'			=> 26,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_DELETE_PAGES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 26
		),
		array(
			'area_id'			=> 80,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_HISTORY_DELETE_ENTRY',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 7,
			'access_id'			=> 80
		),
		array(
			'area_id'			=> 79,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ACTIVATE_HISTORY',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 6,
			'access_id'			=> 79
		),
		array(
			'area_id'			=> 37,
			'parent_area_id'	=> 6,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ADD_REPOSITORY',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 37
		),
		array(
			'area_id'			=> 38,
			'parent_area_id'	=> 7,
			'type'				=> 'function',
			'area_name'			=> 'TXT_MODIFY_MEDIA_FILES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 38
		),
		array(
			'area_id'			=> 39,
			'parent_area_id'	=> 7,
			'type'				=> 'function',
			'area_name'			=> 'TXT_UPLOAD_MEDIA_FILES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 39
		),
		array(
			'area_id'			=> 69,
			'parent_area_id'	=> 12,
			'type'				=> 'function',
			'area_name'			=> 'TXT_GALLERY_MENU_VALIDATE',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 5,
			'access_id'			=> 69
		),
		array(
			'area_id'			=> 67,
			'parent_area_id'	=> 12,
			'type'				=> 'function',
			'area_name'			=> 'TXT_GALLERY_MENU_UPLOAD',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 3,
			'access_id'			=> 67
		),
		array(
			'area_id'			=> 66,
			'parent_area_id'	=> 12,
			'type'				=> 'function',
			'area_name'			=> 'TXT_GALLERY_MENU_NEW_CATEGORY',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 2,
			'access_id'			=> 66
		),
		array(
			'area_id'			=> 70,
			'parent_area_id'	=> 12,
			'type'				=> 'function',
			'area_name'			=> 'TXT_GALLERY_MENU_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 6,
			'access_id'			=> 70
		),
		array(
			'area_id'			=> 68,
			'parent_area_id'	=> 12,
			'type'				=> 'function',
			'area_name'			=> 'TXT_GALLERY_MENU_IMPORT',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 4,
			'access_id'			=> 68
		),
		array(
			'area_id'			=> 65,
			'parent_area_id'	=> 12,
			'type'				=> 'function',
			'area_name'			=> 'TXT_GALLERY_MENU_OVERVIEW',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 3,
			'order_id'			=> 1,
			'access_id'			=> 65
		),
		array(
			'area_id'			=> 28,
			'parent_area_id'	=> 18,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ACTIVATE_DEACTIVATE_USERS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 28
		),
		array(
			'area_id'			=> 34,
			'parent_area_id'	=> 18,
			'type'				=> 'function',
			'area_name'			=> 'TXT_EDIT_GROUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 34
		),
		array(
			'area_id'			=> 29,
			'parent_area_id'	=> 18,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ADD_USERS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 29
		),
		array(
			'area_id'			=> 33,
			'parent_area_id'	=> 18,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ADD_GROUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 33
		),
		array(
			'area_id'			=> 30,
			'parent_area_id'	=> 18,
			'type'				=> 'function',
			'area_name'			=> 'TXT_DELETE_GROUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 30
		),
		array(
			'area_id'			=> 31,
			'parent_area_id'	=> 18,
			'type'				=> 'function',
			'area_name'			=> 'TXT_EDIT_USERINFOS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 31
		),
		array(
			'area_id'			=> 40,
			'parent_area_id'	=> 19,
			'type'				=> 'function',
			'area_name'			=> 'TXT_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 40
		),
		array(
			'area_id'			=> 41,
			'parent_area_id'	=> 20,
			'type'				=> 'function',
			'area_name'			=> 'TXT_CREATE_BACKUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 41
		),
		array(
			'area_id'			=> 42,
			'parent_area_id'	=> 20,
			'type'				=> 'function',
			'area_name'			=> 'TXT_RESTORE_BACKUP',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 42
		),
		array(
			'area_id'			=> 43,
			'parent_area_id'	=> 20,
			'type'				=> 'function',
			'area_name'			=> 'TXT_DELETE_BACKUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 43
		),
		array(
			'area_id'			=> 44,
			'parent_area_id'	=> 20,
			'type'				=> 'function',
			'area_name'			=> 'TXT_DOWNLOAD_BACKUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 44
		),
		array(
			'area_id'			=> 45,
			'parent_area_id'	=> 20,
			'type'				=> 'function',
			'area_name'			=> 'TXT_VIEW_BACKUPS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 45
		),
		array(
			'area_id'			=> 92,
			'parent_area_id'	=> 21,
			'type'				=> 'function',
			'area_name'			=> 'TXT_THEME_IMPORT_EXPORT',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 102
		),
		array(
			'area_id'			=> 47,
			'parent_area_id'	=> 21,
			'type'				=> 'function',
			'area_name'			=> 'TXT_EDIT_SKINS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 47
		),
		array(
			'area_id'			=> 46,
			'parent_area_id'	=> 21,
			'type'				=> 'function',
			'area_name'			=> 'TXT_ACTIVATE_SKINS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 46
		),
		array(
			'area_id'			=> 48,
			'parent_area_id'	=> 22,
			'type'				=> 'function',
			'area_name'			=> 'TXT_EDIT_LANGUAGE_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 48
		),
		array(
			'area_id'			=> 49,
			'parent_area_id'	=> 22,
			'type'				=> 'function',
			'area_name'			=> 'TXT_DELETE_LANGUAGES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 49
		),
		array(
			'area_id'			=> 50,
			'parent_area_id'	=> 22,
			'type'				=> 'function',
			'area_name'			=> 'TXT_LANGUAGE_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 50
		),
		array(
			'area_id'			=> 51,
			'parent_area_id'	=> 23,
			'type'				=> 'function',
			'area_name'			=> 'TXT_REGISTER_MODULES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 51
		),
		array(
			'area_id'			=> 52,
			'parent_area_id'	=> 23,
			'type'				=> 'function',
			'area_name'			=> 'TXT_INST_REMO_MODULES',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 0,
			'access_id'			=> 52
		),
		array(
			'area_id'			=> 99,
			'parent_area_id'	=> 55,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SUPPORT_WIKI',
			'is_active'			=> 1,
			'uri'				=> 'http://www.contrexx.com/docs/',
			'target'			=> '_blank',
			'module_id'			=> 1,
			'order_id'			=> 2,
			'access_id'			=> 110
		),
		array(
			'area_id'			=> 56,
			'parent_area_id'	=> 55,
			'type'				=> 'navigation',
			'area_name'			=> 'TXT_SUPPORT_FORUM',
			'is_active'			=> 1,
			'uri'				=> 'http://www.contrexx.com/forum/',
			'target'			=> '_blank',
			'module_id'			=> 1,
			'order_id'			=> 1,
			'access_id'			=> 56
		),
		array(
			'area_id'			=> 78,
			'parent_area_id'	=> 75,
			'type'				=> 'function',
			'area_name'			=> 'TXT_WORKFLOW_VALIDATE',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 1,
			'access_id'			=> 78
		),
		array(
			'area_id'			=> 77,
			'parent_area_id'	=> 75,
			'type'				=> 'function',
			'area_name'			=> 'TXT_DELETED_RESTORE',
			'is_active'			=> 1,
			'uri'				=> '',
			'target'			=> '_self',
			'module_id'			=> 0,
			'order_id'			=> 1,
			'access_id'			=> 77
		),
		array(
			'area_id'			=> 91,
			'parent_area_id'	=> 90,
			'type'				=> 'function',
			'area_name'			=> 'TXT_CONTACT_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=contact&amp;act=settings',
			'target'			=> '_self',
			'module_id'			=> 6,
			'order_id'			=> 0,
			'access_id'			=> 85
		),
		array(
			'area_id'			=> 107,
			'parent_area_id'	=> 106,
			'type'				=> 'function',
			'area_name'			=> 'TXT_FORUM_MENU_CATEGORIES',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=forum',
			'target'			=> '_self',
			'module_id'			=> 20,
			'order_id'			=> 1,
			'access_id'			=> 107
		),
		array(
			'area_id'			=> 108,
			'parent_area_id'	=> 106,
			'type'				=> 'function',
			'area_name'			=> 'TXT_FORUM_MENU_SETTINGS',
			'is_active'			=> 1,
			'uri'				=> 'index.php?cmd=forum&amp;act=settings',
			'target'			=> '_self',
			'module_id'			=> 20,
			'order_id'			=> 2,
			'access_id'			=> 108
		)
	);

	$objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."backend_areas");

	// add backend areas
	foreach ($arrBackendAreas as $arrBackendArea) {
		$query = "INSERT INTO ".DBPREFIX."backend_areas (`area_id`, `parent_area_id` , `type` , `area_name` , `is_active` , `uri` , `target` , `module_id` , `order_id` , `access_id`
			) VALUES (
			".$arrBackendArea['area_id'].", '".$arrBackendArea['parent_area_id']."', '".$arrBackendArea['type']."', '".$arrBackendArea['area_name']."', '".$arrBackendArea['is_active']."', '".$arrBackendArea['uri']."', '".$arrBackendArea['target']."', '".$arrBackendArea['module_id']."', '".$arrBackendArea['order_id']."', '".$arrBackendArea['access_id']."')";
		if ($objDatabase->Execute($query) === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}






	/***************************************************
	* BUGFIX:	Clean up duplicate usage of access ids *
	***************************************************/
    $arrAccessIds = array(
        116 => 145,
        122 => 146,
        123 => 147,
        140 => 148,
        141 => 149
    );
    $query = 'SELECT `group_id`, `access_id` FROM `'.DBPREFIX.'access_group_static_ids` WHERE `access_id` IN ('.implode(',', array_keys($arrAccessIds)).')';
    $objResult = $objDatabase->Execute($query);
    if ($objResult) {
        while (!$objResult->EOF) {
            $query = 'INSERT INTO `'.DBPREFIX.'access_group_static_ids` (`access_id`, `group_id`) VALUES ('.$arrAccessIds[$objResult->fields['access_id']].', '.$objResult->fields['group_id'].')';
            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
            $objResult->MoveNext();
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    return true;
}
?>
