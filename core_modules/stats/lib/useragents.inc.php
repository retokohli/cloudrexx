<?php

/**
 * Defines browser identification regular expressions and browser names
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0
 * @package     contrexx
 * @subpackage  coremodule_stats
 */

$arrBrowserRegExps = array(
    "=(Opera) ?/?([0-9]{1,2}).[0-9]{1,2}=",
    "=(MSIE) ?([0-9]{1,2}.[0-9])=",
    "=(Firefox)/([0-9]{1,2}.[0-9]{1,2})=",
    "=(Firebird)/([0-9].[0-9])=",
    "=(Netscape)[0-9]?/([0-9]{1,2})=",
    "=(Chrome)/([0-9]{1,2}.[0-9]{1,2})=",
    "=(Safari)/([0-9]{1,3}).[0-9]{1,2}=",
    "=(Konqueror)/([0-9]{1,2}).[0-9]{1,2}=",
    "=(Lynx)/([0-9]{1,2}.[0-9]{1,2})=",
    "=(Mozilla)/([0-9]{1,2})="
);

$arrBrowserNames = array(
    'MSIE'  => 'Internet Explorer',
    'Netscape'  => 'Netscape Navigator',
);

?>
