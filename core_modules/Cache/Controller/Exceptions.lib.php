<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
 
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