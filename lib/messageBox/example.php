<?php

/**
 * messageBox Example
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_framework
 */

/**
 * @ignore
 */
require_once("StatusMessage.class.php") ;

# Create new status message Object
$msg = new StatusMessage();

# Set Box
# $message string
# $mode 'error','warning','confirmation'
$msg->setBox($message="Achtung: Schweinegefahr", $mode="error");

# Generate box
$htmlBox = $msg->generateBox();
?>

<html>
<body bgcolor='white'>
<h2>StatusMessage Example</h2>
<? echo $htmlBox ?>
</body>
</html>
