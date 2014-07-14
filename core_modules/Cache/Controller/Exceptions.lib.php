<?php
/**
 * Cache Module - Exceptions: A list of all pages which shouldn't be cached
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 *
 * @package     contrexx
 * @subpackage  coremodule_cache
 * @todo        Edit PHP DocBlocks!
 */
$_EXCEPTIONS = array( 	// Filter specific Pages in alphabetical order
	array('section'	=> 'Access','cmd' => '/settings.*/'),		// User Profile
	array('section'	=> 'contact'),								// Contact
    array('section'	=> 'GuestBook'	),							// GuestBook
	//array('section'	=> 'Login','redirect' => '/.*/'),			// Login
	//array('section'	=> 'Login', 'restore_pw' => '/.*/'),		// Login
	array('section' => 'Login'),
	//array('section'	=> 'News', 'cmd' => '=submit='),			// News
	array('section' => 'News'),
	array('section'	=> 'Search', 'term' =>	'/.*/'),			// Search
	array('section'	=> 'Gallery'),								// Gallery
	//array('section' => 'memberdir', 'search' => '=search='),	// Memberdir
	array('section' => 'MemberDir'),
	array('section'	=> 'Directory'),							// Directory
	array('section'	=> 'Forum'),								// Forum
	array('section' => 'Shop'),									// Shop
	array('section' => 'Calendar'),								// Calendar
	array('section'	=> 'Market'),								// Market
	array('section' => 'Feed'),
	array('section' => 'DocSys'),
	array('section' => 'Media1'),
	array('section' => 'Media2'),
	array('section' => 'Media3'),
	array('section' => 'Livecam'),
	array('section' => 'Voting'),
	array('section' => 'egov')
);
?>