<?php
function _blogUpdate() {
	global $objDatabase, $_ARRAYLANG, $_CORELANG;

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




    try{
        UpdateUtil::table(
            DBPREFIX.'module_blog_categories',
            array(
                'category_id'    => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'is_active'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'name'           => array('type' => 'VARCHAR(100)', 'notnull' => true, 'default' => '')
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_blog_comments',
            array(
                'comment_id'     => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'is_active'      => array('type' => 'ENUM(\'0\',\'1\')', 'notnull' => true, 'default' => '1'),
                'time_created'   => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'ip_address'     => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '0.0.0.0'),
                'user_id'        => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'user_name'      => array('type' => 'VARCHAR(50)', 'notnull' => false),
                'user_mail'      => array('type' => 'VARCHAR(250)', 'notnull' => false),
                'user_www'       => array('type' => 'VARCHAR(255)', 'notnull' => false),
                'subject'        => array('type' => 'VARCHAR(250)', 'notnull' => true, 'default' => ''),
                'comment'        => array('type' => 'TEXT')
            ),
            array(
                'message_id'     => array('fields' => array('message_id'))
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_blog_message_to_category',
            array(
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'category_id'    => array('type' => 'INT(4)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            ),
            array(
                'category_id'    => array('fields' => array('category_id'))
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_blog_messages',
            array(
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'user_id'        => array('type' => 'INT(5)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'time_created'   => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'time_edited'    => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'hits'           => array('type' => 'INT(7)', 'unsigned' => true, 'notnull' => true, 'default' => '0')
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_blog_networks_lang',
            array(
                'network_id'     => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true),
                'lang_id'        => array('type' => 'INT(2)', 'unsigned' => true, 'notnull' => true, 'default' => '0', 'primary' => true)
            )
        );

        UpdateUtil::table(
            DBPREFIX.'module_blog_votes',
            array(
                'vote_id'        => array('type' => 'INT(8)', 'unsigned' => true, 'notnull' => true, 'auto_increment' => true, 'primary' => true),
                'message_id'     => array('type' => 'INT(6)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'time_voted'     => array('type' => 'INT(14)', 'unsigned' => true, 'notnull' => true, 'default' => '0'),
                'ip_address'     => array('type' => 'VARCHAR(15)', 'notnull' => true, 'default' => '0.0.0.0'),
                'vote'           => array('type' => 'ENUM(\'1\',\'2\',\'3\',\'4\',\'5\',\'6\',\'7\',\'8\',\'9\',\'10\')', 'notnull' => true, 'default' => '1')
            ),
            array(
                'message_id'     => array('fields' => array('message_id'))
            )
        );
    }
    catch (UpdateException $e) {
        // we COULD do something else here..
        return UpdateUtil::DefaultActionHandler($e);
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
