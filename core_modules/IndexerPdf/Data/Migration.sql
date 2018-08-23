/*!40101 SET NAMES utf8 */;
INSERT INTO `contrexx_core_setting` (`section`, `name`, `group`, `type`, `value`, `values`, `ord`) VALUES
-- Default to empty URL (exec(pdftotext) in shell)
-- ('IndexerPdf', 'url_pdftotext', 'config', 'text', '', '', 1);
-- TODO: TEST ONLY
('IndexerPdf', 'url_pdftotext', 'config', 'text', 'http://localhost/pdftotext.php', '', 1);
