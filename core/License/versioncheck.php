<?php

/**
 * request: {
 *      installationId, // optional?
 *      licenseKey,
 *      domainName,
 *      sendTemplate,   // optional
 * }
 * 
 * response: {
 *      licenseState:       "OK",               // One of [OK,NOK,DEMO,ERROR]
 *      validThru:          123456123456,       // License ends at that date
 *      currentVersion:     "3.0.0",            // Current release no.
 *      currentVersionName: "Nikola Tesla",     // Current release name
 *      grayzoneTime:       14,                 // On ERROR Cx will be available for x days
 *      legacy:             "NVERSION=3.0.0",   // Current version for Cx prior 3.0
 *      newTemplate:        "<html></html>",    // optional, template
 *      newInstallationId:  "123456"            // optional, new id
 * }
 * 
 * $validTo = getData()->validThru;
 * $lastUpdate = getLastUpdatedTimestamp();
 * if ($lastUpdate < today() || ($user->isLoggedIn() && !file_exists($template)) {
 *      try {
 *          $data = request();
 *          saveDataExceptTemplate($data);
 *          if (md5($data->template) != md5($template)) {
 *              if ($user->isLoggedIn()) {
 *                  saveTemplate($data->template);
 *              } else {
 *                  rm($template);
 *              }
 *          }
 *          updateLastUpdatedTimestamp();
 *          setLastUnsuccesfulLicenseCheck(null);
 *          $validTo = getData()->validThru;
 *      } catch (request failed) {
 *          if (getLastUnsuccesfulLicenseCheck() == null) {
 *              setLastUnsuccesfulLicenseCheck(now());
 *          }
 *          $validTo = getLastUnsuccesfulLicenseCheck() + getData()->grayzoneTime;
 *      }
 * }
 */

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
