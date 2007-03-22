<?php
function _podcastUpdate()
{
	global $objDatabase;

	/**
	 * *******************************************************************
	 * Create tables
	 *
	 */
	$arrTables = $objDatabase->MetaTables('TABLES');
	if ($arrTables !== false) {
		if (!in_array(DBPREFIX."module_podcast_medium", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_podcast_medium (
				`id` int(10) unsigned NOT NULL auto_increment,
				`title` varchar(255) NOT NULL default '',
				`author` varchar(255) NOT NULL default '',
				`description` text NOT NULL,
				`source` text NOT NULL,
				`template_id` int(11) unsigned NOT NULL default '0',
				`width` int(10) unsigned NOT NULL default '0',
				`height` int(10) unsigned NOT NULL default '0',
				`playlenght` int(10) unsigned NOT NULL default '0',
				`size` int(10) unsigned NOT NULL default '0',
				`status` tinyint(1) NOT NULL default '0',
				`date_added` int(14) unsigned NOT NULL default '0',
				PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_podcast_category", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_podcast_category (
				`id` int(10) unsigned NOT NULL auto_increment,
				`title` varchar(255) NOT NULL default '',
				`description` varchar(255) NOT NULL default '',
				`status` tinyint(1) NOT NULL default '0',
				PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_podcast_rel_category_lang", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_podcast_rel_category_lang (
				`category_id` int(10) unsigned NOT NULL default '0',
				`lang_id` int(10) unsigned NOT NULL default '0'
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_podcast_rel_medium_category", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_podcast_rel_medium_category (
				`medium_id` int(10) unsigned NOT NULL default '0',
				`category_id` int(10) unsigned NOT NULL default '0'
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_podcast_template", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_podcast_template (
				`id` int(10) unsigned NOT NULL auto_increment,
				`description` varchar(255) NOT NULL default '',
				`template` text NOT NULL,
				`extensions` varchar(255) NOT NULL default '',
				PRIMARY KEY  (`id`),
				UNIQUE KEY `description` (`description`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

		if (!in_array(DBPREFIX."module_podcast_settings", $arrTables)) {
			$query = "CREATE TABLE ".DBPREFIX."module_podcast_settings (
				`setid` smallint(6) NOT NULL auto_increment,
				`setname` varchar(250) NOT NULL default '',
				`setvalue` text NOT NULL,
				`status` tinyint(1) NOT NULL default '0',
				PRIMARY KEY  (`setid`)
				) TYPE=MyISAM";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	} else {
		print "Die Struktur der Datenkbank konnte nicht ermittelt werden!";
		return false;
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_podcast_medium");
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['podcastindex'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_podcast_medium` ADD FULLTEXT `podcastindex` (`title` ,`description`)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_podcast_medium konnte nicht ermittelt werden!";
		return false;
	}

	$arrIndexes = $objDatabase->MetaIndexes(DBPREFIX."module_podcast_category");
	if ($arrIndexes !== false) {
		if (!isset($arrIndexes['podcastindex'])) {
			$query = "ALTER TABLE `".DBPREFIX."module_podcast_category` ADD FULLTEXT `podcastindex` (`title` ,`description`)";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}

	} else {
		print "Die Struktur der Datenbanktabelle ".DBPREFIX."module_podcast_category konnte nicht ermittelt werden!";
		return false;
	}

	/**
	 * *******************************************************************
	 * Insert settings
	 *
	 */
	$arrPodcastSettings = array(
		array(
			'setname'	=> 'default_width',
			'setvalue'	=> '320',
			'status'	=> 1
		),
		array(
			'setname'	=> 'default_height',
			'setvalue'	=> '240',
			'status'	=> 1
		),
		array(
			'setname'	=> 'feed_title',
			'setvalue'	=> 'Contrexx.com Neuste Videos',
			'status'	=> 1
		),
		array(
			'setname'	=> 'feed_description',
			'setvalue'	=> 'Neuste Videos',
			'status'	=> 1
		),
		array(
			'setname'	=> 'feed_image',
			'setvalue'	=> '',
			'status'	=> 1
		)
	);

	foreach ($arrPodcastSettings as $arrSetting) {
		$query = "SELECT setid FROM ".DBPREFIX."module_podcast_settings WHERE setname='".$arrSetting['setname']."'";
		$objSettings = $objDatabase->SelectLimit($query, 1);
		if ($objSettings === false) {
			return _databaseError($query, $objDatabase->ErrorMsg());
		} elseif ($objSettings->RecordCount() == 0) {
			$query = "INSERT INTO ".DBPREFIX."module_podcast_settings (`setname` , `setvalue` , `status` ) VALUES ( '".$arrSetting['setname']."', '".$arrSetting['setvalue']."', ".$arrSetting['status'].")";
			if ($objDatabase->Execute($query) === false) {
				return _databaseError($query, $objDatabase->ErrorMsg());
			}
		}
	}

	/**
	 * *******************************************************************
	 * Insert Templates
	 *
	 */
	$arrTemplates = array(
		array(
			'description'	=> 'Video für Windows (Windows Media Player Plug-in)',
			'template'		=> '<object id="podcastPlayer" classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" standby="Loading Windows Media Player components..." type="application/x-oleobject" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<embed type="application/x-mplayer2" name="podcastPlayer" showstatusbar="1" src="[[MEDIUM_URL]]" autostart="1" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]+70" />\r\n<param name="URL" value="[[MEDIUM_URL]]" />\r\n<param name="BufferingTime" value="60" />\r\n<param name="AllowChangeDisplaySize" value="true" />\r\n<param name="AutoStart" value="true" />\r\n<param name="EnableContextMenu" value="true" />\r\n<param name="stretchToFit" value="true" />\r\n<param name="ShowControls" value="true" />\r\n<param name="ShowTracker" value="true" />\r\n<param name="uiMode" value="full" />\r\n</object>',
			'extensions'	=> 'avi, wmv'
		),
		array(
			'description'	=> 'RealMedia (RealMedia Player Plug-in)',
			'template'		=> '<object id="podcastPlayer1" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]">\r\n<param name="controls" value="all">\r\n<param name="autostart" value="true">\r\n<embed src="[[MEDIUM_URL]]" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" autostart="true" type="video/x-pn-realvideo" console="video1" controls="All" nojava="true"></embed>\r\n</object>',
			'extensions'	=> 'ram, rpm'
		),
		array(
			'description'	=> 'QuickTime Film (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/quicktime" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'mov, qt, mqv',
		),
		array(
			'description'	=> 'CAF-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-caf" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-caf" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'caf'
		),
		array(
			'description'	=> 'AAC-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-aac" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-aac" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'aac, adts'
		),
		array(
			'description'	=> 'AMR-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/AMR" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/AMR" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'amr'
		),
		array(
			'description'	=> 'GSM-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-gsm" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-gsm" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'gsm'
		),
		array(
			'description'	=> 'QUALCOMM PureVoice Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/vnd.qcelp" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/vnd.qcelp" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'qcp'
		),
		array(
			'description'	=> 'MIDI (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-midi" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-midi" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'mid, midi, smf, kar'
		),
		array(
			'description'	=> 'uLaw/AU-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/basic" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/basic" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'au, snd, ulw'
		),
		array(
			'description'	=> 'AIFF-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-aiff" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-aiff" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'aiff, aif, aifc, cdda'
		),
		array(
			'description'	=> 'WAVE-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-wav" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-wav" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'wav, bwf'
		),
		array(
			'description'	=> 'Video für Windows (AVI) (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/x-msvideo" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/x-msvideo" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'avi, vfw'
		),
		array(
			'description'	=> 'AutoDesk Animator (FLC) (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/flc" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/flc" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'flc, fli, cel'
		),
		array(
			'description'	=> 'Digitales Video (DV) (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/x-dv" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/x-dv" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'dv, dif'
		),
		array(
			'description'	=> 'SDP-Stream-Beschreibung (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="application/x-sdp" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="application/x-sdp" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'sdp'
		),
		array(
			'description'	=> 'RTSP-Stream-Beschreibung (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="application/x-rtsp" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="application/x-rtsp" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'rtsp, rts'
		),
		array(
			'description'	=> 'MP3-Wiedergabeliste (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-mpegurl" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-mpegurl" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'm3u, m3url'
		),
		array(
			'description'	=> 'MPEG-Medien (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/x-mpeg" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/x-mpeg" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'mpeg, mpg, m1s, m1v, m1a, m75, m15, mp2'
		),
		array(
			'description'	=> '3GPP-Medien (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/3gpp" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/3gpp" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> '3gp, 3gpp'
		),
		array(
			'description'	=> '3GPP2-Medien (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/3gpp2" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/3gpp2" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> '3g2, 3gp2'
		),
		array(
			'description'	=> 'SD-Video (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/sd-video" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/sd-video" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'sdv'
		),
		array(
			'description'	=> 'AMC-Medien (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="application/x-mpeg" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="application/x-mpeg" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'amc'
		),
		array(
			'description'	=> 'MPEG-4-Medien (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/mp4" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/mp4" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'mp4'
		),
		array(
			'description'	=> 'AAC-Audiodatei (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-m4a" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-m4a" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'm4a'
		),
		array(
			'description'	=> 'AAC-Audio (geschützt) (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-m4p" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-m4p" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'm4p'
		),
		array(
			'description'	=> 'ACC-Audiobuch (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-m4b" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-m4b" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'm4b'
		),
		array(
			'description'	=> 'Video (geschützt) (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="video/x-m4v" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="video/x-m4v" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'm4v'
		),
		array(
			'description'	=> 'MP3-Audio (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-mpeg" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-mpeg" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'mp3, swa'
		),
		array(
			'description'	=> 'Sound Designer II (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="audio/x-sd2" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="audio/x-sd2" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'sd2'
		),
		array(
			'description'	=> 'BMP-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-bmp" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-bmp" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'bmp, dib'
		),
		array(
			'description'	=> 'MacPaint Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-macpaint" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-macpaint" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'pntg, pnt, mac'
		),
		array(
			'description'	=> 'PICT-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-pict" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-pict" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'pict, pic, pct'
		),
		array(
			'description'	=> 'PNG-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-png" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-png" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'png'
		),
		array(
			'description'	=> 'QuickTime Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-quicktime" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-quicktime" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'qtif, qti'
		),
		array(
			'description'	=> 'SGI-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-sgi" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-sgi" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'sgi, rgb'
		),
		array(
			'description'	=> 'TGA-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-targa" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-targa" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'targa, tga'
		),
		array(
			'description'	=> 'TIFF-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-tiff" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-tiff" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'tif, tiff'
		),
		array(
			'description'	=> 'Photoshop-Bild (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/x-photoshop" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/x-photoshop" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'psd'
		),
		array(
			'description'	=> 'JPEG2000 image (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="image/jp2" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="image/jp2" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'jp2'
		),
		array(
			'description'	=> 'SMIL 1.0 (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="application/smil" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="application/smil" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'smi, sml, smil'
		),
		array(
			'description'	=> 'Flash-Medien (QuckTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="application/x-shockwave-flash" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="application/x-shockwave-flash" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'swf'
		),
		array(
			'description'	=> 'QuickTime HTML (QHTML) (QuickTime Plug-in)',
			'template'		=> '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="autoplay" value="true" />\r\n<param name="controller" value="true" />\r\n<param name="target" value="myself" />\r\n<param name="type" value="text/x-html-insertion" />\r\n<embed src="[[MEDIUM_URL]]" width=[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" type="text/x-html-insertion" pluginspage="http://www.apple.com/quicktime/download/" autoplay="true" controller="true" target="myself" />\r\n</object>',
			'extensions'	=> 'qht, qhtm'
		),
		array(
			'description'	=> 'MP3-Audio (RealPlayer Player)',
			'template'		=> '<object id="videoplayer1" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="controls" value="all" />\r\n<param name="autostart" value="true" />\r\n<param name="type" value="audio/x-mpeg" />\r\n<embed src="[[MEDIUM_URL]]" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" autostart="true" type="audio/x-mpeg" console="video1" controls="All" nojava="true"></embed>\r\n</object>',
			'extensions'	=> 'mp3'
		),
		array(
			'description'	=> 'MP3-Wiedergabeliste (RealPlayer Plug-in)',
			'template'		=> '<object id="videoplayer1" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="controls" value="all" />\r\n<param name="autostart" value="true" />\r\n<param name="type" value="audio/x-mpegurl" />\r\n<embed src="[[MEDIUM_URL]]" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" autostart="true" type="audio/x-mpegurl" console="video1" controls="All" nojava="true"></embed>\r\n</object>',
			'extensions'	=> 'm3u, m3url'
		),
		array(
			'description'	=> 'WAVE-Audio (RealPlayer Plug-in)',
			'template'		=> '<object id="videoplayer1" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]">\r\n<param name="src" value="[[MEDIUM_URL]]" />\r\n<param name="controls" value="all" />\r\n<param name="autostart" value="true" />\r\n<param name="type" value="audio/x-wav" />\r\n<embed src="[[MEDIUM_URL]]" width="[[MEDIUM_WIDTH]]" height="[[MEDIUM_HEIGHT]]" autostart="true" type="audio/x-wav" console="video1" controls="All" nojava="true"></embed>\r\n</object>',
			'extensions'	=> 'wav'
		)
	);

	foreach ($arrTemplates as $arrTemplate) {
		$query = "SELECT id FROM ".DBPREFIX."module_podcast_template WHERE description='".$arrTemplate['description']."'";
		$objTemplate = $objDatabase->SelectLimit($query, 1);
		if ($objTemplate !== false) {
			if ($objTemplate->RecordCount() == 0) {
				$query = 'INSERT INTO '.DBPREFIX.'module_podcast_template (`description`, `template`, `extensions`) VALUES (\''.$arrTemplate['description'].'\', \''.$arrTemplate['template'].'\', \''.$arrTemplate['extensions'].'\')';
				if ($objDatabase->Execute($query) === false) {
					return _databaseError($query, $objDatabase->ErrorMsg());
				}
			}
		} else {
			return _databaseError($query, $objDatabase->ErrorMsg());
		}
	}

	return true;
}
?>