<?php
function _blogUpdate() {
	global $objDatabase, $_ARRAYLANG;

	/*
	* Check for missing setting "blog_comments_editor" in database. In the update-package for 1.2 this value somehow
	* got lost.
	*/
	$query = '	SELECT 	name
				FROM	`'.DBPREFIX.'module_blog_settings`
				WHERE	name="blog_comments_editor"
				LIMIT	1';

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

	/************************************************
	* BUGFIX:	Set write access to the upload dir  *
	************************************************/
	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';
	$objFile = new File();
	if (is_writeable(ASCMS_BLOG_IMAGES_PATH) || $objFile->setChmod(ASCMS_BLOG_IMAGES_PATH, ASCMS_BLOG_IMAGES_WEB_PATH, '')) {
    	if ($mediaDir = @opendir(ASCMS_BLOG_IMAGES_PATH)) {
    		while($file = readdir($mediaDir)) {
    			if ($file != '.' && $file != '..') {
    				if (!is_writeable(ASCMS_BLOG_IMAGES_PATH.'/'.$file) && !$objFile->setChmod(ASCMS_BLOG_IMAGES_PATH.'/', ASCMS_BLOG_IMAGES_WEB_PATH.'/', $file)) {
    					setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_FILE'], ASCMS_BLOG_IMAGES_PATH.'/'.$file, $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    					return false;
    				}
    			}
			}
    	} else {
    		setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_BLOG_IMAGES_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    		return false;
		}
    } else {
    	setUpdateMsg(sprintf($_ARRAYLANG['TXT_SET_WRITE_PERMISSON_TO_DIR_AND_CONTENT'], ASCMS_BLOG_IMAGES_PATH.'/', $_CORELANG['TXT_UPDATE_TRY_AGAIN']), 'msg');
    	return false;
    }

	/**
	 * Everything went fine. Return without any errors.
	 */
    return true;
}
?>
