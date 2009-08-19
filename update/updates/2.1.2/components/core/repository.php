<?php

function _updateModuleRepository_%MODULE_ID%()
{
    global $objDatabase;

    $arrModuleRepositoryPages = array(/*REPOSITORY_ARRAY*/);

    $query = "DELETE FROM ".DBPREFIX."module_repository WHERE `moduleid`=%MODULE_ID%";
    if ($objDatabase->Execute($query) === false) {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    $arrPageId = array();
    foreach ($arrModuleRepositoryPages as $arrPage) {
        $arrPage['query'] = str_replace(
            '[[PKG_MODULE_REPOSITORY_PAGE_PARID]]',
            (empty($arrPageId[$arrPage['parid']])
              ? 0 : $arrPageId[$arrPage['parid']]
            ),
            $arrPage['query']
        );

        if ($objDatabase->Execute($arrPage['query']) === false) {
            return _databaseError($arrPage['query'], $objDatabase->ErrorMsg());
        }
        $arrPageId[$arrPage['id']] = $objDatabase->Insert_ID();
    }
    return true;
}

?>