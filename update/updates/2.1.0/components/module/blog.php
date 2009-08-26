<?php
function _blogUpdate() {
    global $objDatabase, $_ARRAYLANG;

    /*
    * Check for missing setting "blog_comments_editor" in database. In the update-package for 1.2 this value somehow
    * got lost.
    */
    $query = '    SELECT     name
                FROM    `'.DBPREFIX.'module_blog_settings`
                WHERE    name="blog_comments_editor"
                LIMIT    1';

    $objResult = $objDatabase->Execute($query);

    if ($objResult !== false) {
        if ($objResult->RecordCount() == 0) {
            $query = "INSERT INTO `".DBPREFIX."module_blog_settings` ( `name` , `value` ) VALUES ('blog_comments_editor', 'wysiwyg')";

            if ($objDatabase->Execute($query) === false) {
                return _databaseError($query, $objDatabase->ErrorMsg());
            }
        }
    } else {
        return _databaseError($query, $objDatabase->ErrorMsg());
    }

    /**
     * Everything went fine. Return without any errors.
     */
    return true;
}
?>
