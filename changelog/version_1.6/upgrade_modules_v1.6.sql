// gallery module
// probably already there in version 1.6!

ALTER TABLE `astalavista_gallery_pictures` ADD `show_size` SET( '0', '1' ) DEFAULT '1' NOT NULL AFTER `status`;
UPDATE `astalavista_gallery_pictures` SET `show_size` = '1';
ALTER TABLE `astalavista_gallery_pictures` RENAME `astalavista_module_gallery_pictures`;
ALTER TABLE `astalavista_gallery_categories` RENAME `astalavista_module_gallery_categories` ;
ALTER TABLE `astalavista_gallery_settings` RENAME `astalavista_module_gallery_settings` ;
INSERT INTO `astalavista_module_gallery_settings` ( `id` ,`name`,`value`,`description`) VALUES ('','show_names','off','txtGallerySettingsShowName');


// docsys module
//////////////////////////////

DROP TABLE IF EXISTS astalavista_module_docsys;
CREATE TABLE astalavista_module_docsys (
  id smallint(6) unsigned NOT NULL auto_increment,
  date int(14) default NULL,
  title varchar(250) NOT NULL default '',
  text mediumtext NOT NULL,
  source varchar(250) NOT NULL default '',
  url1 varchar(250) NOT NULL default '',
  url2 varchar(250) NOT NULL default '',
  catid tinyint(2) NOT NULL default '0',
  lang tinyint(2) NOT NULL default '0',
  userid smallint(6) NOT NULL default '0',
  startdate date NOT NULL default '0000-00-00',
  enddate date NOT NULL default '0000-00-00',
  status tinyint(4) NOT NULL default '1',
  changelog int(14) NOT NULL default '0',
  KEY ID (id),
  FULLTEXT KEY newsindex (text,title)
) TYPE=MyISAM;



DROP TABLE IF EXISTS astalavista_module_docsys_categories;
CREATE TABLE astalavista_module_docsys_categories (
  catid tinyint(2) unsigned NOT NULL auto_increment,
  name varchar(100) NOT NULL default '',
  lang tinyint(2) unsigned NOT NULL default '1',
  PRIMARY KEY  (catid)
) TYPE=MyISAM;



// news module
//////////////////////////////
// probably already there in version 1.6!

ALTER TABLE `astalavista_news` RENAME `astalavista_module_news` ;
ALTER TABLE `astalavista_module_news` ADD `startdate` DATE DEFAULT '0000-00-00' NOT NULL AFTER `userid` ,ADD `enddate` DATE DEFAULT '0000-00-00' NOT NULL AFTER `startdate` ,ADD `status` TINYINT DEFAULT '1' NOT NULL AFTER `enddate`;
ALTER TABLE `astalavista_module_news` CHANGE `newsid` `id` SMALLINT( 6 ) UNSIGNED NOT NULL AUTO_INCREMENT ; 
ALTER TABLE `astalavista_module_news` CHANGE `newsdate` `date` INT( 14 ) DEFAULT NULL;  
ALTER TABLE `astalavista_module_news` CHANGE `newstitle` `title` VARCHAR( 250 ) NOT NULL ; 
ALTER TABLE `astalavista_module_news` CHANGE `newstext` `text` MEDIUMTEXT NOT NULL; 
ALTER TABLE `astalavista_module_news` CHANGE `newssource` `source` VARCHAR( 250 ) NOT NULL;  
ALTER TABLE `astalavista_module_news` CHANGE `newsurl1` `url1` VARCHAR( 250 ) NOT NULL ;
ALTER TABLE `astalavista_module_news` CHANGE `newsurl2` `url2` VARCHAR( 250 ) NOT NULL;
ALTER TABLE `astalavista_module_news` CHANGE `newscat` `catid` TINYINT( 2 ) DEFAULT '0' NOT NULL ;

ALTER TABLE `astalavista_news_categories` RENAME `astalavista_module_news_categories` ;
ALTER TABLE `astalavista_module_news_categories` CHANGE `id` `catid` TINYINT( 2 ) UNSIGNED NOT NULL AUTO_INCREMENT;



// calendar module
//////////////////////////////
// probably already there in version 1.6!


DROP TABLE IF EXISTS astalavista_module_calendar;
CREATE TABLE astalavista_module_calendar (
  id int(11) NOT NULL auto_increment,
  date varchar(10) NOT NULL default '',
  time varchar(4) NOT NULL default '',
  end_date varchar(10) NOT NULL default '',
  end_time varchar(4) NOT NULL default '',
  priority int(1) NOT NULL default '3',
  name varchar(25) NOT NULL default '',
  comment text NOT NULL,
  lang int(1) NOT NULL default '1',
  sort varchar(10) NOT NULL default '',
  place varchar(25) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS astalavista_module_calendar_style;
CREATE TABLE astalavista_module_calendar_style (
  id int(11) NOT NULL auto_increment,
  tableWidth varchar(4) NOT NULL default '141',
  tableHeight varchar(4) NOT NULL default '92',
  tableColor varchar(7) NOT NULL default '',
  tableBorder int(11) NOT NULL default '0',
  tableBorderColor varchar(7) NOT NULL default '',
  tableSpacing int(11) NOT NULL default '0',
  fontSize int(11) NOT NULL default '10',
  fontColor varchar(7) NOT NULL default '',
  numColor varchar(7) NOT NULL default '',
  normalDayColor varchar(7) NOT NULL default '',
  normalDayRollOverColor varchar(7) NOT NULL default '',
  curDayColor varchar(7) NOT NULL default '',
  curDayRollOverColor varchar(7) NOT NULL default '',
  eventDayColor varchar(7) NOT NULL default '',
  eventDayRollOverColor varchar(7) NOT NULL default '',
  shownEvents int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;


INSERT INTO astalavista_module_calendar_style VALUES (1, '141', '92', '#ffffff', 1, '#cccccc', 0, 10, '#000000', '#0000ff', '#ffffff', '#eeeeee', '#00ccff', '#0066ff', '#00cc00', '#009900', 10);
INSERT INTO astalavista_module_calendar_style VALUES (2, '141', '92', '#ffffff', 1, '#cccccc', 0, 10, '#000000', '#0000ff', '#ffffff', '#eeeeee', '#00ccff', '#0066ff', '#00cc00', '#009900', 10);



DROP TABLE IF EXISTS astalavista_module_feed_category;
CREATE TABLE astalavista_module_feed_category (
  id int(11) NOT NULL auto_increment,
  name varchar(150) NOT NULL default '',
  status int(1) NOT NULL default '1',
  time int(100) NOT NULL default '0',
  lang int(1) NOT NULL default '0',
  pos int(3) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;


DROP TABLE IF EXISTS astalavista_module_feed_news;
CREATE TABLE astalavista_module_feed_news (
  id int(11) NOT NULL auto_increment,
  subid int(11) NOT NULL default '0',
  name varchar(150) NOT NULL default '',
  link varchar(150) NOT NULL default '',
  filename varchar(150) NOT NULL default '',
  articles int(2) NOT NULL default '0',
  cache int(4) NOT NULL default '3600',
  time int(100) NOT NULL default '0',
  image int(1) NOT NULL default '1',
  status int(1) NOT NULL default '1',
  pos int(3) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;









