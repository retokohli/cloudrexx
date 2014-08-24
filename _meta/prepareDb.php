<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/core/Core/init.php';
$cx = init('minimal');

$structureDump = 'installer/data/contrexx_dump_structure.sql';
$dataDump = 'installer/data/contrexx_dump_data.sql';

try {
    $file = new \Cx\Lib\FileSystem\File($structureDump);
    $dump = $file->getData();
    removeConnectionSettings($dump);
    filterModules($dump);
    filterCoreModules($dump);
    $file->write($dump);

    $file = new \Cx\Lib\FileSystem\File($dataDump);
    $dump = $file->getData();
    removeConnectionSettings($dump);
    filterModules($dump);
    filterCoreModules($dump);
    $file->write($dump);
} catch (\Exception $e) {
    print $e->getMessage();
    exit;
}

echo 'done';
exit;

/*
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS=0;
SET AUTOCOMMIT=0;

COMMIT;
SET UNIQUE_CHECKS=1;
SET FOREIGN_KEY_CHECKS = 1;
*/





// remove obsolete connection settings
function removeConnectionSettings(&$dump) {
    $dump = preg_replace('/SET\s+(character_set_client|@saved_cs_client)[^;]+;\n?/m', '', $dump);
}

// remove excluded modules
function filterModules(&$dump) {
    $dump = preg_replace_callback('/(?:CREATE\s+TABLE|INSERT\s+INTO)\s+`([^`]*)`.+?;\n/sm', function($matches) {
        $includedModules = array(
            'block',
            'newsletter',
            'contact',
            'filesharing',
            'media_',
            'news',
            'repository',
        );
        // check if table is related to a module
        if (preg_match('/^contrexx_module_/', $matches[1])) {
            // if table is related to a module,
            // do check if it is an included module
            if (!preg_match('/^contrexx_module_('.join('|', $includedModules).').*/', $matches[1])) { 
                return '';
            }
        }
        return $matches[0];
    } , $dump);
}

// remove excluded core_modules
function filterCoreModules(&$dump) {
    $dump = preg_replace_callback('/(?:CREATE\s+TABLE|INSERT\s+INTO)\s+`([^`]*)`.+?;\n/sm', function($matches) {
        $excludedModules = array(
            'voting',
            'core_module_multisite',
        );
        // check if it is an excluded module
        if (preg_match('/^contrexx_('.join('|', $excludedModules).').*/', $matches[1])) { 
            return '';
        }
        return $matches[0];
    } , $dump);
}
