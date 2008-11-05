<?PHP
error_reporting(0);
ini_set('display_errors', 0);

require_once("../config/version.php");
if ($_SERVER['HTTP_X_PROTOTYPE_VERSION']) {
    // Check if there's a new version
    $v = preg_split('#\.#', $_CONFIG['coreCmsVersion']);
    $version = $v[0]  * 10000 + $v[1]  * 100 + $v[2];
    $link = fsockopen('www.contrexx.com',80);
    if (!$link) {
        exit;
    }
    $r = $_SERVER['PHP_SELF'];
    fwrite($link, 
        "GET /updatecenter/check.php?v=$version HTTP/1.1\n".
        "Host: www.contrexx.com\n".
        "Referer: $r\n".
        "Connection: close\n\n"
    );

    $r = array();

    while ($l = fgets($link)) {
        if (preg_match('#NVERSION=(\d+\.\d+\.\d+)#', $l, $r)) {
            $newversion = $r[1];
            echo '(New Version available: <a style="color:black;" href="http://www.contrexx.com" target="_new">'.$newversion.'</a>)';
            fclose($link);
            exit;
        }
    }
    fclose($link);
}
else {
    echo $_CONFIG['coreCmsVersion']." ".$_CONFIG['coreCmsEdition'];
}

