/* Updatscript geschrieben */
INSERT INTO `contrexx_module_news_settings` (`name`, `value`) VALUES ('news_ticker_filename', 'newsticker.txt');
ALTER TABLE `contrexx_module_news` ADD `redirect` VARCHAR( 250 ) NOT NULL AFTER `text`