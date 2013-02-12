<?php

class membership {

    private $moduleName = 'crm';
    private $table_name;

    function findAllByLang($data = array()) {
        global $objDatabase, $_LANGID;

        $condition = '';
        if (!empty($data)) {
            $condition = "AND ".implode("AND ",$data);
        }
        $objResult = $objDatabase->Execute("SELECT membership.*,
                                                   memberLoc.value
                                             FROM `".DBPREFIX."module_{$this->moduleName}_memberships` AS membership
                                             LEFT JOIN `".DBPREFIX."module_{$this->moduleName}_membership_local` AS memberLoc
                                                ON membership.id = memberLoc.entry_id
                                             WHERE memberLoc.lang_id = ".$_LANGID." $condition ORDER BY sorting ASC");

        return $objResult;
    }
}
?>
