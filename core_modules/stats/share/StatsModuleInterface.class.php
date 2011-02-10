<?php
require_once(ASCMS_FRAMEWORK_PATH.'/ModuleInterface.class.php');
/**
 * Provides public stats functions
 */
class StatsModuleInterface extends ModuleInterface {
    /**
     * Updates the statistic title table.
     * A copy of the current title is kept in this table. This way we can ensure titles in the statistics
     * overview are still available if the page itself (including history) has already been deleted.
     * @param integer $pageId ID of the page to update
     * @param string $title the new title
     */
    public function updateStatsTitles($pageId, $title) {
        global $objDatabase;
        $objDatabase->Execute('UPDATE '.DBPREFIX.'stats_requests
                                  SET pageTitle="'.$title.'"
                                WHERE pageId='.$pageId);
    }

}
?>