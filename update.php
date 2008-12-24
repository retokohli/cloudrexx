<?php

if (!@include_once('config/configuration.php'))
    die('Couldn\'t load configuration file');
if (!@include_once('config/version.php'))
    die('Couldn\'t load version file');
if (!@include_once(ASCMS_CORE_PATH.'/API.php'))
    die('Couldn\'t load contrexx API file');
$_SYSCONFIG = false;
if (!@include_once('config/settings.php'))
    die('Couldn\'t load settings file');
if (is_array($_SYSCONFIG)) {
    foreach ($_SYSCONFIG as $sysconfigKey => $sysconfValue) {
        $_CONFIG[$sysconfigKey] = $sysconfValue;
    }
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$objDatabase = ADONewConnection($_DBCONFIG['dbType']);
@$objDatabase->Connect($_DBCONFIG['host'], $_DBCONFIG['user'], $_DBCONFIG['password'], $_DBCONFIG['database']);

$errorNo = $objDatabase->ErrorNo();
if ($errorNo != 0) {
    die("Database error #$errorNo: ".$objDatabase->ErrorMsg());
}
if (!empty($_DBCONFIG['charset']) && !$objDatabase->Execute('SET CHARACTER SET '.$_DBCONFIG['charset'])) {
    die("Unable to set database character set ".$_DBCONFIG['charset']);
}

require_once('update/updates/2.1.0/components/module/shop.php');
$result = _shopUpdate();
if ($result !== true) die($result);
die("Update finished successfully");


function _databaseError($query, $errorMsg)
{
    die("Database error in query:<hr />$query<hr />Error message: ".htmlspecialchars($errorMsg)."<br />");
}

function setUpdateMsg($msg)
{
    echo("Update error message: $msg<br />");
}

?>
