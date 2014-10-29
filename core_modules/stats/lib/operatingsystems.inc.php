<?php

/**
 * Defines operating systems
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0
 * @package     contrexx
 * @subpackage  coremodule_stats
 * @todo        Edit PHP DocBlocks!
 */

$arrOperatingSystems = array(
    // mobile operating systems
    array(
        'regExp' => '=Android=i',
        'name' => 'Android'
    ),
    array(
        'regExp' => '=windows\sphone=i',
        'name' => 'Windows Phone'
    ),
    array(
        'regExp' => '=BlackBerry=',
        'name' => 'BlackBerry'
    ),
    array(
        'regExp' => '=symbian=i',
        'name' => 'Symbian'
    ),
    array(
        'regExp' => '=i(Phone|Pad|Pod)=i',
        'name' => 'iOS'
    ),

    // desktop operating systems
    array(
        'regExp' => '=Windows NT 5\.0|Windows 2000=',
        'name' => 'Windows 2000'
    ),
    array(
        'regExp' => '=Windows NT 4\.0=',
        'name' => 'Windows NT'
    ),
    array(
        'regExp' => '=Windows NT 5\.1|Windows XP=',
        'name' => 'Windows XP'
    ),
    array(
        'regExp' => '=Windows NT 6\.0=',
        'name' => 'Windows Vista'
    ),
    array(
        'regExp' => '=Windows 98=',
        'name' => 'Windows 98'
    ),
    array(
        'regExp' => '=Windows 95=',
        'name' => 'Windows 95'
    ),
    array(
        'regExp' => '=Mac_PowerPC|Macintosh=',
        'name' => 'Macintosh'
    ),
    array(
        'regExp' => '=Linux=',
        'name' => 'Linux'
    ),
    array(
        'regExp' => '=SunOS=',
        'name' => 'SunOS'
    ),
    array(
        'regExp' => '=AIX=',
        'name' => 'AIX'
    ),
    array(
        'regExp' => '=FreeBSD=',
        'name' => 'FreeBSD'
    ),
    array(
        'regExp' => '=BeOS=',
        'name' => 'BeOS'
    ),
    array(
        'regExp' => '=IRIX=',
        'name' => 'IRIX'
    ),
    array(
        'regExp' => '=Windows NT 6\.1=', # o'rly? not 7? 't least the RC shows 6.1
        'name' => 'Windows 7'
    ),
    array(
        'regExp' => '=Windows NT 6\.2=',
        'name' => 'Windows 8'
    ),
    // attention: iPhone/iPad/iPod can be missinterpreted as OS/2
    //            therefore, OS/2 must be the last option in this list
    array(
        'regExp' => '=OS/2=',
        'name' => 'OS/2'
    ),
);

?>
