DROP TABLE IF EXISTS `contrexx_module_travellog_connection`;
CREATE TABLE `contrexx_module_travellog_connection` (
  `Verbindungsnummer` int(11) NOT NULL PRIMARY KEY,
  `Sequenznummer` varchar(255) NOT NULL, -- never used
  `Verbindungsstring` varchar(10000) NOT NULL
);

DROP TABLE IF EXISTS `contrexx_module_travellog_journey`;
CReate table `contrexx_module_travellog_journey` (
  `att` int(11) unsigned NOT NULL,
  `reisedat` date NOT NULL,
  `verbnr` varchar(255) NOT NULL,
  `rbn` int(11) NOT NULL,
  `reisen` int(11) NOT NULL,
  `d` varchar(1) NOT NULL,
  `at_start` varchar(255) NOT NULL, -- never used
  `at_recs` varchar(255) NOT NULL -- never used
);
-- INDEX
ALTER TABLE `contrexx_module_travellog_journey`
ADD INDEX `att`,
ADD INDEX `reisedat`,
ADD INDEX `verbnr`,
ADD INDEX `rbn`,
ADD INDEX `d`;

-- Used queries
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
