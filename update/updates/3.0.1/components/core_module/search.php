<?php

function _searchUpdate() {

    /*********************************************************
     * EXTENSION:   Change name of HTML_Template_Sigma block *
     * ADDED:       Contrexx v3.0.0                          *
     *********************************************************/
    \Cx\Lib\UpdateUtil::migrateContentPage('search', null, 'searchrow', 'search_result', '3.0.0');

}
