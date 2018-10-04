-- Used queries (these determine the indexes added below):
-- --
-- SELECT `Verbindungsstring`
-- FROM contrexx_module_travellog_connection
-- WHERE `Verbindungsnummer`=$connectionNr;
-- --
-- SELECT STR_TO_DATE(j.`REISEDAT`,'%d.%m.%Y') AS resedat, j.`REISEDAT`, j.`VERBNR`, j.`RBN`, j.`REISEN`
-- FROM contrexx_module_travellog_journey
-- WHERE `ATT` NOT LIKE '111'
-- AND `D` NOT LIKE 'X'
-- AND `RBN`=$searchTerm
-- ORDER BY `resedat` ASC";
-- --
-- SELECT STR_TO_DATE(`REISEDAT`,'%d.%m.%Y') AS resedat, `REISEDAT`, `VERBNR`, `RBN`, `REISEN`
-- FROM contrexx_module_travellog_journey
-- WHERE `ATT` NOT LIKE '111'
-- AND `D` NOT LIKE 'X'
-- AND `VERBNR` $regex
-- ORDER BY `RBN` ASC, `resedat` ASC";
-- --

DROP TABLE IF EXISTS `contrexx_module_chdirtravellog_connection`;
CREATE TABLE `contrexx_module_chdirtravellog_connection` (
  `verbindungsnummer` int(11) unsigned NOT NULL PRIMARY KEY,
  `project` varchar(255) NOT NULL,
  `sequenznummer` varchar(255) NOT NULL, -- never used
  `verbindungsstring` text NOT NULL
);
ALTER TABLE `contrexx_module_chdirtravellog_connection`
ADD INDEX `project` (`project`);
INSERT INTO `contrexx_module_chdirtravellog_connection` (
  `verbindungsnummer`, `project`, `sequenznummer`, `verbindungsstring`
)
(
  SELECT `Verbindungsnummer`, 'GAN16', `Sequenznummer`, `Verbindungsstring`
  FROM `contrexx_module_travellog_connection`
);

-- TODO: Is there any combination of keys to form a primary?
DROP TABLE IF EXISTS `contrexx_module_chdirtravellog_journey`;
CREATE TABLE `contrexx_module_chdirtravellog_journey` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `project` varchar(255) NOT NULL,
  `att` int(11) unsigned NOT NULL,
  `reisedat` date NOT NULL,
  `verbnr` varchar(255) NOT NULL,
  `rbn` int(11) unsigned NOT NULL,
  `reisen` int(11) unsigned NOT NULL,
  `d` varchar(1) NOT NULL,
  `at_start` varchar(255) NOT NULL, -- never used
  `at_recs` varchar(255) NOT NULL -- never used
);
-- INDEX
ALTER TABLE `contrexx_module_chdirtravellog_journey`
ADD INDEX `project` (`project`),
ADD INDEX `att` (`att`),
ADD INDEX `reisedat` (`reisedat`),
ADD INDEX `verbnr` (`verbnr`),
ADD INDEX `rbn` (`rbn`),
ADD INDEX `d` (`d`);

INSERT INTO `contrexx_module_chdirtravellog_journey` (
  `att`, `project`, `reisedat`,
  `verbnr`, `rbn`, `reisen`, `d`, `at_start`, `at_recs`
)
(
  SELECT `ATT`, 'GAN16', STR_TO_DATE(`REISEDAT`, '%d.%m.%Y'),
      `VERBNR`, `RBN`, `REISEN`, `D`, `AT_START`, `AT_RECS`
  FROM `contrexx_module_travellog_journey`
);

