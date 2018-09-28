/*!40101 SET NAMES utf8 */;

-- NOTE: This is obsolete.
--
-- Use file based settings, as provided for setup in settings-add.sh.

INSERT INTO `contrexx_core_setting`
  (`section`, `name`, `group`, `type`, `value`, `values`, `ord`)
VALUES
-- Default to empty URL (exec(pdftotext) in shell).
-- This won't work without pdftotext installed.
  ('C7NIndexerPdf', 'url_pdftotext', 'config', 'text', '', '', 1);
-- TEST ONLY (endpoint dummy, development)
--  ('C7NIndexerPdf', 'url_pdftotext', 'config', 'text', 'http://localhost/pdftotext.php', '', 1);
