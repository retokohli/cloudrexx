UPDATE `contrexx_backend_areas` SET `access_id` = '64' WHERE `area_id` =64 LIMIT 1 ;

# Versionierungssystem
# --------------------------------
INSERT INTO `contrexx_content_navigation` VALUES ('', '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1', 'system', 1132500836, 'lost_and_found', 2, 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content` VALUES ('', 'Restored categories will be added here.', 'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y');
INSERT INTO `contrexx_content_navigation` VALUES ('', '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1', 'system', 1132500836, 'lost_and_found', 3, 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content` VALUES ('', 'Restored categories will be added here.', 'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y');
INSERT INTO `contrexx_content_navigation` VALUES ('', '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1', 'system', 1132500836, 'lost_and_found', 4, 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content` VALUES ('', 'Restored categories will be added here.', 'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y');
INSERT INTO `contrexx_content_navigation` VALUES ('', '1', 0, 'Lost & Found', '', 9999, 'off', '0', '1', 'system', 1132500836, 'lost_and_found', 5, 1, '0000-00-00', '0000-00-00', 0, 0, 0, 0);
INSERT INTO `contrexx_content` VALUES ('', 'Restored categories will be added here.', 'Lost &amp; Found', 'Lost &amp; Found', 'Lost & Found', 'Lost & Found', 'index', '', '', 'y');

ALTER TABLE `contrexx_content_navigation` ADD PRIMARY KEY ( `catid` )