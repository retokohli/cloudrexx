INSERT INTO `contrexx_module_podcast_settings` ( `setid` , `setname` , `setvalue` , `status` )
VALUES (
NULL , 'feed_title', 'Contrexx.com Neuste Videos', '1'
), (
NULL , 'feed_description', 'Neuste Videos', '1'
);

INSERT INTO `contrexx_module_podcast_settings` ( `setid` , `setname` , `setvalue` , `status` )
VALUES (
NULL , 'feed_image', '', '1'
);


ALTER TABLE `contrexx_module_podcast_medium` ADD FULLTEXT (
`title` ,
`description`
);