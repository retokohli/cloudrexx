<?php
/**
 * Cache Module - Exceptions: A list of all pages which shouldn't be cached
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 *
 * @package     contrexx
 * @subpackage  core_module_cache
 * @todo        Edit PHP DocBlocks!
 */
$_EXCEPTIONS = array( 	// Filter specific Pages in alphabetical order
	array('section'	=> 'access','cmd' => '/settings.*/'),		// User Profile
	array('section'	=> 'contact'),								// Contact
    array('section'	=> 'guestbook'	),							// Guestbook
	//array('section'	=> 'login','redirect' => '/.*/'),			// Login
	//array('section'	=> 'login', 'restore_pw' => '/.*/'),		// Login
	array('section' => 'login'),
	//array('section'	=> 'news', 'cmd' => '=submit='),			// News
	array('section' => 'news'),
	array('section'	=> 'search', 'term' =>	'/.*/'),			// Search
	array('section'	=> 'gallery'),								// Gallery
	//array('section' => 'memberdir', 'search' => '=search='),	// Memberdir
	array('section' => 'memberdir'),
	array('section'	=> 'directory'),							// Directory
	array('section'	=> 'forum'),								// Forum
	array('section' => 'shop'),									// Shop
	array('section' => 'calendar'),								// Calendar
	array('section'	=> 'market'),								// Market
	array('section' => 'feed'),
	array('section' => 'docsys'),
	array('section' => 'media1'),
	array('section' => 'media2'),
	array('section' => 'media3'),
	array('section' => 'livecam'),
	array('section' => 'voting'),
	array('section' => 'egov')
);
?>
