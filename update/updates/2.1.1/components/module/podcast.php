<?php
function _podcastUpdate()
{
	global $objDatabase, $_ARRAYLANG;

	require_once ASCMS_FRAMEWORK_PATH.'/File.class.php';

	//move podcast images directory
	$path = ASCMS_DOCUMENT_ROOT.'/images';
	$webPath = ASCMS_PATH_OFFSET.'/images';
	$oldImagesPath = '/content/podcast';
	$newImagesPath = '/podcast';

	$objFile = new File();

	$objFile->setChmod($path, $webPath, '/content/podcast');
	if(file_exists($path.$oldImagesPath)){
		if('error' == $objFile->copyDir($path, $webPath, $oldImagesPath, $path, $webPath, $newImagesPath, true)){
			setUpdateMsg(sprintf($_ARRAYLANG['TXT_UNABLE_TO_MOVE_DIRECTORY'], $path.$oldImagesPath, $path.$newImagesPath));
		}
	}
	$objFile->setChmod($path, $webPath, '/podcast');
	$objFile->setChmod($path, $webPath, '/podcast/youtube_thumbnails');

	//change thumbnail paths
	$query = "UPDATE `".DBPREFIX."module_podcast_medium` SET `thumbnail` = REPLACE(`thumbnail`, '/images/content/podcast/', '/images/podcast/')";
    if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

	//set new default settings
	$query = "UPDATE `".DBPREFIX."module_podcast_settings` SET `setvalue` = '50' WHERE `setname` = 'thumb_max_size' AND `setvalue` = ''";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}
	$query = "UPDATE `".DBPREFIX."module_podcast_settings` SET `setvalue` = '85' WHERE `setname` = 'thumb_max_size_homecontent' AND `setvalue` = ''";
	if ($objDatabase->Execute($query) === false) {
		return _databaseError($query, $objDatabase->ErrorMsg());
	}

    return true;
}
?>