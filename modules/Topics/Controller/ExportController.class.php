<?php
/**
 * Export Topics in all known languages as CSV
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */

namespace Cx\Modules\Topics\Controller;

/**
 * Topics ExportController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class ExportController extends \Cx\Core\Core\Model\Entity\Controller
{
    /**
     * Column separator, usually semicolon (;)
     *
     * See {@see fputcsv} for details.
     * @var string
     */
    protected static $delimiter = ';';
    /**
     * Column enclosure, usually double quotes (")
     *
     * See {@see fputcsv} for details.
     * @var string
     */
    protected static $enclosure = '"';
    /**
     * Escape character for the column enclosure, usually backslash (\)
     *
     * See {@see fputcsv} for details.
     * Mind that backslashes must be escaped in PHP strings.
     * @var string
     */
    protected static $escape_char = '\\';
    /**
     * BOM (Byte Order Mark)
     *
     * Defaults to UTF-8.
     * Set it to the empty string if not required.
     * Mind that this must be interpolated; use double quotes.
     * @var string
     */
    protected static $bom = "\xEF\xBB\xBF";

    /**
     * Handle the request and send the result
     *
     * if $_POST['exportEntriesCsv'] is set, the export is run and the
     * generated file is sent as a download.
     * Catches and handles errors that occur during the export.
     * Otherwise, the page is parsed.
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string              $cmd
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        global $_ARRAYLANG;
        if (isset($_POST['exportEntriesCsv'])) {
            try {
                $this->export(); // Exits on success
            } catch (\Error $e) {
                \Message::error(
                    $_ARRAYLANG['TXT_MODULE_TOPICS_ACT_EXPORT_ERROR_FAILED']);
                return;
            }
            // Did not exit, thus there was nothing to be downloaded
            \Message::error(
                $_ARRAYLANG['TXT_MODULE_TOPICS_ACT_EXPORT_ERROR_NO_DATA']);
            return;
        }
        // Intentionally unused
        $template->getCurrentBlock();
        $cmd = null;
    }

    /**
     * Download the Entry and Category Repositories as CSV
     * @global  type    $_ARRAYLANG
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function export()
    {
        $data = $this->getData();
        $download = new \HTTP_Download();
        $download->setData($data);
        $download->setContentDisposition(
            HTTP_DOWNLOAD_ATTACHMENT, static::getFileName());
        $download->setContentType();
        $download->send('application/force-download');
        exit;
    }

    /**
     * Return the export file name
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getFileName()
    {
        return 'Topics-' . date('Y-m-d-H-i-s') . '.csv';
    }

    /**
     * Return the export data in CSV format
     *
     * Returns the empty string if no Entries are found.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @throws  \Error  on failure to open the temporary file for writing
     */
    protected function getData()
    {
        $em = $this->cx->getDb()->getEntityManager();
        $entryRepo = $em->getRepository(
            $this->getNamespace() . '\\Model\\Entity\\Entry');
        $entries = $entryRepo->findAll();
        if (count($entries) === 0) {
            return '';
        }
        $pathTemp = tempnam(sys_get_temp_dir(), 'csv');
        $handle = fopen($pathTemp, 'w');
        if (!$handle) {
            throw new \Error('Failed to open temp file for output');
        }
        fwrite($handle, static::$bom);
        fputcsv($handle, [
            'Id',
            'Locale',
            'Name',
            'Description',
            'Categories',
            'Active',
            'Created',
            'Updated',
            'Href',
            'Slug',
        ], static::$delimiter, static::$enclosure, static::$escape_char);
        $translationRepo = $em->getRepository(
            '\\Cx\\Core\\Locale\\Model\\Entity\\Translation');
        foreach ($entries as $entry) { //$entry = new \Cx\Modules\Topics\Model\Entity\Entry();
            $translationsEntry = $translationRepo->findTranslations($entry);
            foreach ($translationsEntry as $locale => $translation) {
                $categoryNames = [];
                foreach ($entry->getCategories()->toArray() as $category) {
                    $translationsCategory =
                        $translationRepo->findTranslations($category);
                    $categoryNames[] = $translationsCategory[$locale]['name'];
                }
                $fields = [
                    $entry->getId(),
                    $locale,
                    $translation['name'],
                    $translation['description'],
                    join(', ', $categoryNames),
                    $entry->getActive(),
                    $entry->getCreated()->format(ASCMS_DATE_FORMAT_DATETIME),
                    $entry->getUpdated()->format(ASCMS_DATE_FORMAT_DATETIME),
                    $translation['href'],
                    $translation['slug'],
                ];
                fputcsv($handle, $fields, static::$delimiter,
                    static::$enclosure, static::$escape_char);
            }
        }
        fclose($handle);
        return file_get_contents($pathTemp);
    }

}
