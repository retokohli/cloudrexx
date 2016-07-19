<?php

function _searchUpdate() {

    /*********************************************************
     * EXTENSION:   Change name of HTML_Template_Sigma block *
     * ADDED:       Contrexx v3.0.0                          *
     *********************************************************/
    try {
        \Cx\Lib\UpdateUtil::migrateContentPage('search', null, 'searchrow', 'search_result', '3.0.0');
    } catch (\Cx\Lib\UpdateException $e) {
        return \Cx\Lib\UpdateUtil::DefaultActionHandler($e);
    }

}
