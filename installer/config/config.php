<?php
/**
 * Installer config
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author        Astalavista Development Team <thun@astalvista.ch>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

$requiredPHPVersion = "4.3";
$requiredMySQLVersion = "3.23";
$requiredGDVersion = "1.6";
$dbType = "mysql";
$defaultLanguage = "de";
$licenseFileCommerce = "data".DIRECTORY_SEPARATOR."contrexx_lizenz_de.txt";
$licenseFileOpenSource = "data".DIRECTORY_SEPARATOR."contrexx_lizenz_opensource_de.txt";
$configFile = "/config/configuration.php";
$configTemplateFile = "data".DIRECTORY_SEPARATOR."configuration.tpl";
$versionFile = "/config/version.php";
$versionTemplateFile = "data".DIRECTORY_SEPARATOR."version.tpl";
$sqlDumpFile = DIRECTORY_SEPARATOR."installer".DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."contrexx_dump";
$dbPrefix = "contrexx_";
$templatePath = "template/contrexx/";
$supportEmail = "support@contrexx.com";
$supportURI = "http://www.contrexx.com/index.php?page=754";
$forumURI = "http://www.contrexx.com/forum/";
$contrexxURI = "http://www.contrexx.com/";


$arrDefaultConfig = array(
	'dbHostname'	=> 'localhost',
	'dbUsername'	=> '',
	'dbPassword'	=> '',
	'dbDatabaseName'	=> '',
	'dbTablePrefix'	=> 'contrexx_',
	'ftpHostname'	=> 'localhost',
	'ftpUsername'	=> '',
	'ftpPassword'	=> ''
);

$arrLanguages = array(
	1	=> array(
		'id'	=> 1,
		'lang'	=> 'de',
		'name'	=> 'Deutsch',
		'is_default'	=> true
	),
	2	=> array(
		'id'	=> 2,
		'lang'	=> 'en',
		'name'	=> 'English',
		'is_default'	=> false
	)/*,
	3	=> array(
		'id'	=> 3,
		'lang'	=> 'fr',
		'name'	=> 'French',
		'is_default'	=> false
	),
	4	=> array(
		'id'	=> 4,
		'lang'	=> 'it',
		'name'	=> 'Italian',
		'is_default'	=> false
	),
	5	=> array(
		'id'	=> 5,
		'lang'	=> 'dk',
		'name'	=> 'Danish',
		'is_default'	=> false
	)*/
);

$arrFiles = array(
	'/admin/backup' => array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777
	),
	'/feed'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/media'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/images'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/themes'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	'/tmp'		=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777,
		'sub_dirs'	=> true
	),
	$configFile => array(
		'mode'		=> '0706',
		'mode_oct'	=> 0706
	),
	$versionFile => array(
		'mode'		=> '0706',
		'mode_oct'	=> 0706
	),
	'/config/settings.php'	=> array(
		'mode'		=> '0666',
		'mode_oct'	=> 0706
	),
	'/cache'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777
	),
	'/sitemap.xml'	=> array(
		'mode'		=> '0777',
		'mode_oct'	=> 0777
	)
);

$arrDatabaseTables = array(
	'access_group_dynamic_ids',
	'access_group_static_ids',
	'access_users',
	'access_user_groups',
	'backend_areas',
	'backups',
	'content',
	'content_history',
	'content_logfile',
	'content_navigation',
	'content_navigation_history',
	'ids',
	'languages',
	'log',
	'modules',
	'module_news',
	'module_news_access',
	'module_news_categories',
	'module_news_settings',
	'module_news_teaser_frame',
	'module_news_teaser_frame_templates',
	'module_repository',
	'sessions',
	'settings',
	'skins'
);

$arrDatabaseTablesModulesStandard = array(
	/*'community_config',
	'module_banner_groups',
	'module_banner_relations',
	'module_banner_settings',
	'module_banner_system',
	'module_block_blocks',
	'module_calendar',
	'module_calendar_categories',
	'module_calendar_style',
	'module_contact_form',
	'module_contact_form_data',
	'module_contact_form_field',
	'module_contact_settings',
	'module_docsys',
	'module_docsys_categories',
	'module_feed_category',
	'module_feed_news',
	'module_feed_newsml_association',
	'module_feed_newsml_categories',
	'module_feed_newsml_documents',
	'module_feed_newsml_providers',
	'module_gallery_categories',
	'module_gallery_comments',
	'module_gallery_language',
	'module_gallery_language_pics',
	'module_gallery_pictures',
	'module_gallery_settings',
	'module_gallery_votes',
	'module_guestbook',
	'module_guestbook_settings',
	'module_newsletter',
	'module_newsletter_attachment',
	'module_newsletter_category',
	'module_newsletter_confirm_mail',
	'module_newsletter_rel_cat_news',
	'module_newsletter_rel_user_cat',
	'module_newsletter_settings',
	'module_newsletter_template',
	'module_newsletter_tmp_sending',
	'module_newsletter_user',
	'module_podcast_category',
	'module_podcast_medium',
	'module_podcast_rel_category_lang',
	'module_podcast_rel_medium_category',
	'module_podcast_settings',
	'module_podcast_template',
	'module_recommend',
	'stats_browser',
	'stats_colourdepth',
	'stats_config',
	'stats_country',
	'stats_hostname',
	'stats_javascript',
	'stats_operatingsystem',
	'stats_referer',
	'stats_requests',
	'stats_requests_summary',
	'stats_screenresolution',
	'stats_search',
	'stats_spiders',
	'stats_spiders_summary',
	'stats_visitors',
	'stats_visitors_summary',
	'voting_results',
	'voting_system'*/
);

$arrDatabaseTablesModulesECommerce = array(
	'module_shop_categories',
	'module_shop_config',
	'module_shop_countries',
	'module_shop_currencies',
	'module_shop_customers',
	'module_shop_mail',
	'module_shop_mail_content',
	'module_shop_orders',
	'module_shop_order_items',
	'module_shop_order_items_attributes',
	'module_shop_payment',
	'module_shop_payment_processors',
	'module_shop_pricelists',
	'module_shop_products',
	'module_shop_products_attributes',
	'module_shop_products_attributes_name',
	'module_shop_products_attributes_value',
	'module_shop_products_downloads',
	'module_shop_rel_countries',
	'module_shop_rel_payment',
	'module_shop_rel_shipment',
	'module_shop_shipment',
	'module_shop_zones'
);
?>
