<?php
/**
 * Version code
 *
 * Version informations
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team
 * @version     2.1.3
 * @package     contrexx
 * @subpackage  config
 * @todo        Edit PHP DocBlocks!
 */

// status levels:
//	Planning
//	Pre-Alpha
//	Alpha
//	Beta
//	Production/Stable
//	Mature
//	Inactive

$_CONFIG['coreCmsName']        = "Contrexx® Web Content Management System";
$_CONFIG['coreCmsVersion']	    = "2.1.3";
$_CONFIG['coreCmsStatus']	    = "Stable";
$_CONFIG['coreCmsEdition']	    = "Premium";
$_CONFIG['coreCmsCodeName']    = "Oscar";
$_CONFIG['coreCmsReleaseDate']	= "23.01.2010";

if (strstr(str_replace('\\', '/',__FILE__), $_SERVER['PHP_SELF'])) {
    header('Content-type: text/html; charset="utf-8"',true);
    echo $_CONFIG['coreCmsName']
        . ' ' . $_CONFIG['coreCmsVersion']
        . ' ' . $_CONFIG['coreCmsEdition']
        . ' ' . $_CONFIG['coreCmsStatus']
        ;
}

?>