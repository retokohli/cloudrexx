<?php

namespace Cx\Update\Cx_3_0_1\Core;

class License {
    
    public function __construct() {
        
    }
    
    public function update() {
        global $documentRoot, $sessionObj;
        
        if (!@include_once(ASCMS_DOCUMENT_ROOT.'/lib/PEAR/HTTP/Request2.php')) {
            return false;
        }

        $_GET['force'] = 'true';
        $_GET['silent'] = 'true';
        $documentRoot = ASCMS_DOCUMENT_ROOT;
        
        $userId = 1;
        $sessionObj->cmsSessionUserUpdate($userId);
        
        $return = @include_once(ASCMS_DOCUMENT_ROOT.'/core_modules/License/versioncheck.php');
        return ($return === true);
    }
}
