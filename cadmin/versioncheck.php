<?php

error_reporting(0);
ini_set('display_errors', 0);

require_once("../config/version.php");
if ($_SERVER['HTTP_X_PROTOTYPE_VERSION']) {
    // Check if there's a new version
    $v = preg_split('#\.#', $_CONFIG['coreCmsVersion']);
    $e = $_CONFIG['coreCmsEdition'];

    $version = $v[0]  * 10000 + $v[1]  * 100 + $v[2];
    $link = @fsockopen('www.contrexx.com',80);
    if (!$link) {
        exit;
    }
    # Please don't change this, we'd like to know who installs our software.
    # The data won't be given away to third parties; there will not be any
    # negative consequences for you. This data is collected entirely for 
    # internal statistics (installation count etc).
    #
    # If you decide to change this code, you will not receive any notification
    # about new versions anymore.
    $r = $_SERVER['HTTP_REFERER'];
    $a = $_SERVER['REMOTE_ADDR'];
    fwrite($link,
        "GET /updatecenter/check.php?v=$version HTTP/1.1\n".
        "Host: www.contrexx.com\n".
        "Referer: $r\n".
        "X-Edition: $e\n".
        "X-Remote-Addr: $a\n".
        "Connection: close\n\n"
    );

    $r = array();
    $l = fgets($link);
    while ($l) {
        if (preg_match('#NVERSION=(\d+\.\d+\.\d+)#', $l, $r)) {
            $newversion = $r[1];
            echo '(Update: <a href="http://www.contrexx.com" target="_new">'.$newversion.'</a>)';
            fclose($link);
            exit;
        }
        $l = fgets($link);
    }
    fclose($link);
} else {
    echo $_CONFIG['coreCmsVersion']." ".$_CONFIG['coreCmsEdition'];
}

?>
