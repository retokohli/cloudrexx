/*!40101 SET NAMES utf8 */;
INSERT INTO `contrexx_core_setting`
  (`section`, `name`, `group`, `type`, `value`, `values`, `ord`)
VALUES
-- Default to empty URL (exec(pdftotext) in shell).
-- This won't work without pdftotext installed.
  ('IndexerPdf', 'url_pdftotext', 'config', 'text', '', '', 1);
-- TEST ONLY (endpoint dummy, development)
--  ('IndexerPdf', 'url_pdftotext', 'config', 'text', 'http://localhost/pdftotext.php', '', 1);
