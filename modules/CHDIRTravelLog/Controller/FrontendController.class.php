<?php declare(strict_types=1);
/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.1 - 7.2
 *
 * @category  CloudrexxApp
 * @package   CHDIRTravelLog
 * @author    Comvation AG <info@comvation.com>
 * @copyright 2018 ch-direct
 * @link      https://www.comvation.com/
 *
 * Unauthorized copying, changing or deleting
 * of any file from this app is strictly prohibited
 *
 * Authorized copying, changing or deleting
 * can only be allowed by a separate contract
 */

namespace Cx\Modules\CHDIRTravelLog\Controller;

/**
 * Travel Log (Nemesis)
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{
    /**
     * Set up the frontend view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string              $cmd
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        \Cx\Core\Setting\Controller\Setting::init(
            $this->getName(), 'config', 'FileSystem'
        );
        $this->importCsv();
        $this->viewSearch($template);
    }

    /**
     * Return the project name from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getProjectName(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('project_name');
    }

    /**
     * Return the project names from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getProjectNames(): array
    {
        return \Cx\Core\Setting\Controller\Setting::splitValues(
            \Cx\Core\Setting\Controller\Setting::getValue('project_names')
        );
    }

    /**
     * Return the data folder from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getDataFolder(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('data_folder');
    }

    /**
     * Return the PDF folder from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getPdfFolder(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('pdf_folder');
    }

    /**
     * Return the CSV delimiter character from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getCsvDelimiter(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('csv_delimiter');
    }

    /**
     * Return the CSV enclosure character from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getCsvEnclosure(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('csv_enclosure');
    }

    /**
     * Return the CSV excape character from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getCsvEscape(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('csv_escape');
    }

    /**
     * Return the last synchronize time from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  int
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getLastSyncTime(): int
    {
        return intval(
            \Cx\Core\Setting\Controller\Setting::getValue('last_sync_time')
        );
    }

    /**
     * Update the last synchronize time in the Settings
     *
     * Mind that the Settings must have been initialized.
     * @param   integer $lastSyncTime
     * @return  bool|null
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function setLastSyncTime(int $lastSyncTime)
    {
        \Cx\Core\Setting\Controller\Setting::set(
            'last_sync_time', $lastSyncTime
        );
        return \Cx\Core\Setting\Controller\Setting::updateAll();
    }

    /**
     * Set up the search view
     * @global  array               $_CORELANG
     * @param   \Cx\Core\Html\Sigma $template
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function viewSearch(\Cx\Core\Html\Sigma $template)
    {
        $paramsGet = $this->cx->getRequest()->getParams();
        $projectName = $paramsGet['project'] ?? '';
        $searchTerm = $paramsGet['number'] ?? '';
        $selectJourney = '';
        $selectConnection = \Html::ATTRIBUTE_SELECTED;
        $type = $paramsGet['type'] ?? '';
        if ($type === 'journey') {
            $selectConnection = '';
            $selectJourney = \Html::ATTRIBUTE_SELECTED;
        }
        $projectNames = static::getProjectNames();
        $template->setGlobalVariable([
            'CHDIRTRAVELLOG_PROJECT_OPTIONS' => \Html::getOptions(
                array_combine($projectNames, $projectNames), $projectName
            ),
            'CHDIRTRAVELLOG_URI' => $_SERVER['REQUEST_URI'],
            'CHDIRTRAVELLOG_ICON_FOLDER_PATH' => $this->getIconFolderPath(),
            'CHDIRTRAVELLOG_NUMBER' => urldecode($searchTerm),
            'CHDIRTRAVELLOG_SELECTED_CONNECTION' => $selectConnection,
            'CHDIRTRAVELLOG_SELECTED_JOURNEY' => $selectJourney,
        ]);
        if (empty($paramsGet['search'])) {
            return;
        }
        $pos = intval($paramsGet['pos'] ?? 0);
        $exportCsv = (bool)($_GET['csv'] ?? false);
        $count = 0;
        switch ($type) {
            case 'connection':
                $count = $this->showConnections(
                    $template, $projectName, $searchTerm, $pos, $exportCsv
                );
                break;
            case 'journey':
                $count = $this->showJourneys(
                    $template, $projectName, $searchTerm, $pos, $exportCsv
                );
                break;
        }
        if ($count === 0) {
            $template->touchBlock('chdirtravellog_no_result');
        }
    }

    /**
     * List the Connections in the search results view
     * @global  array   $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string  $projectName
     * @param   string  $searchTerm
     * @param   int     $pos
     * @param   bool    $exportCsv
     * @return  int                 The total result count
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function showConnections(
        \Cx\Core\Html\Sigma $template,
        string $projectName, string $searchTerm, int $pos, bool $exportCsv
    ): int {
        global $_ARRAYLANG;
        $arrConnection = explode('.', $searchTerm);
        $connectionRepo = $this->cx->getDb()->getEntityManager()->getRepository(
            'Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Connection'
        );
        $connection = $connectionRepo->find($arrConnection[0]);
        $connectionName = '';
        if ($connection) {
            $connectionName = $connection->getVerbindungsstring();
        }
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('j')
            ->from('Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Journey', 'j')
            ->orderBy('j.rbn, j.reisedat')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('j.project', ':project'),
                    $qb->expr()->neq('j.att', ':att'),
                    $qb->expr()->neq('j.d', ':d')
                )
            )
            ->setParameters([
                'project' => $projectName,
                'att' => '111',
                'd' => 'X',
            ]);
        if (empty($arrConnection[1])) {
            $qb->andWhere(
                $qb->expr()->like('j.verbnr', ':term')
            )
            ->setParameter('term', $arrConnection[0].'.%');
        } else {
            $qb->andWhere(
                $qb->expr()->eq('j.verbnr', ':term')
            )
            ->setParameter('term', $searchTerm);
        }
        if ($exportCsv) {
            $journeys = $qb->getQuery()->getResult();
            // exit()s
            $this->exportCsv(
                $projectName, 'connection_nr_' . $searchTerm, $journeys
            );
        }
        $journeys = new \Doctrine\ORM\Tools\Pagination\Paginator(
            $qb->getQuery(), false
        );
        $count = count($journeys);
        $limit = \Cx\Core\Setting\Controller\Setting::getValue(
            'corePagingLimit', 'Config'
        );
        $qb->setFirstResult($pos)->setMaxResults($limit);
        $paramsGet = $this->cx->getRequest()->getParams();
        $paging = \Paging::get(
            $paramsGet,
            $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ENTRIES'],
            $count, $limit, false, $pos, 'pos'
        );
        $template->setGlobalVariable([
            'CHDIRTRAVELLOG_NUMBER' => $searchTerm,
            'CHDIRTRAVELLOG_CONNECTION_NAME' => $connectionName,
            'CHDIRTRAVELLOG_CONNECTION_COUNT' => $count,
            'CHDIRTRAVELLOG_PAGING' => $paging,
        ]);
        $template->touchBlock('chdirtravellog_connection_info');
        $this->parseJourneys($template, $projectName, $journeys);
        return $count;
    }

    /**
     * List the Journeys in the search results view
     * @global  array   $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string  $projectName
     * @param   string  $searchTerm
     * @param   int     $pos
     * @param   bool    $exportCsv
     * @return  int                 The total result count
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function showJourneys(
        \Cx\Core\Html\Sigma $template,
        string $projectName, string $searchTerm, int $pos, bool $exportCsv
    ): int {
        global $_ARRAYLANG;
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('j')
            ->from('Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Journey', 'j')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('j.project', ':project'),
                    $qb->expr()->neq('j.att', ':att'),
                    $qb->expr()->neq('j.d', ':d'),
                    $qb->expr()->eq('j.rbn', ':term')
                )
            )
            ->setParameters([
                'project' => $projectName,
                'att' => '111',
                'd' => 'X',
                'term' => $searchTerm,
            ])
            ->orderBy('j.reisedat');
        if ($exportCsv) {
            $journeys = $qb->getQuery()->getResult();
            // exit()s
            $this->exportCsv(
                $projectName, 'datasheet_nr_' . $searchTerm, $journeys
            );
        }
        $limit = \Cx\Core\Setting\Controller\Setting::getValue(
            'corePagingLimit', 'Config'
        );
        $qb->setFirstResult($pos)->setMaxResults($limit);
        $journeys = new \Doctrine\ORM\Tools\Pagination\Paginator(
            $qb->getQuery(), false
        );
        $count = count($journeys);
        $paramsGet = $this->cx->getRequest()->getParams();
        $paging = \Paging::get(
            $paramsGet,
            $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ENTRIES'],
            $count, $limit, false, $pos, 'pos'
        );
        $template->setGlobalVariable([
            'CHDIRTRAVELLOG_PDF_FOLDER_PATH' => static::getPdfFolder(),
            'CHDIRTRAVELLOG_PROJECT_NAME' => static::getProjectName(),
            'CHDIRTRAVELLOG_JOURNEY_COUNT' => $count,
            'CHDIRTRAVELLOG_PAGING' => $paging,
        ]);
        $template->touchBlock('chdirtravellog_journey_info');
        $this->parseJourneys($template, $projectName, $journeys);
        return $count;
    }

    /**
     * Parse the journeys for the given project name
     * @staticvar   \Doctrine\ORM\EntityRepository  $connectionRepo
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string  $projectName
     * @param   array   $journeys
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseJourneys(
        \Cx\Core\Html\Sigma $template, string $projectName, iterable $journeys
    ) {
        $connectionRepo =
            $this->cx->getDb()->getEntityManager()->getRepository(
                'Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Connection'
            );
        foreach ($journeys as $journey) {
            $connectionNr = intval($journey->getVerbnr());
            $connection = $connectionRepo->findOneBy([
                'verbindungsnummer' => $connectionNr,
                'project' => $projectName,
            ]);
            $connectionName = '';
            if ($connection) {
                $connectionName = $connection->getVerbindungsstring();
            }
            $url = $this->getFileUrl($projectName, $journey->getRbn());
            if ($url) {
                $template->setVariable([
                'CHDIRTRAVELLOG_JOURNEY_RBN_URL' => $url,
                ]);
            } else {
                $template->touchBlock('chdirtravellog_journey_no_download');
            }
            $template->setVariable([
                'CHDIRTRAVELLOG_JOURNEY_RBN' => $journey->getRbn(),
                'CHDIRTRAVELLOG_JOURNEY_DATE' =>
                    $journey->getReisedat()->format(ASCMS_DATE_FORMAT_DATE),
                'CHDIRTRAVELLOG_JOURNEY_CONNECTION_COUNT' =>
                    $journey->getReisen(),
                'CHDIRTRAVELLOG_JOURNEY_CONNECTION_NR' =>
                    $journey->getVerbnr(),
                'CHDIRTRAVELLOG_JOURNEY_CONNECTION_NAME' => $connectionName,
            ]);
            $template->parse('chdirtravellog_journey');
        }
    }

    /**
     * Update the database tables from the CSV files
     *
     * Skips updating iff neither of the files has been modified.
     * Updates the last sync timestamp iff all updates were successful.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function importCsv()
    {
        global $_ARRAYLANG;
        $dataRoot = static::getDataFolder();
        $projectNames = static::getProjectNames();
        $lastSyncTime = static::getLastSyncTime();
        // Avoid race condition: Mark the time *before* starting the import
        // (works even if the files are modified while the import is running).
        $thisSyncTime = time();
        $failed = [];
        foreach ($projectNames as $projectName) {
            $connectionsFile = new \Cx\Lib\FileSystem\FileSystemFile(
                $dataRoot . $projectName . '_Verbindungen.csv'
            );
            $journeysFile = new \Cx\Lib\FileSystem\FileSystemFile(
                $dataRoot . $projectName . '_FAHRT.csv'
            );
            if (filemtime($connectionsFile->getAbsoluteFilePath())
                    < $lastSyncTime
                && filemtime($journeysFile->getAbsoluteFilePath())
                    < $lastSyncTime
            ) {
                continue;
            }
            if (!$this->csv2db('connection', $projectName, $connectionsFile)) {
                $failed[$connectionsFile] = $projectName;
            }
            if (!$this->csv2db('journey', $projectName, $journeysFile)) {
                $failed[$journeysFile] = $projectName;
            }
        }
        if ($failed) {
            \Message::warning($_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ERROR_FILE_IMPORT']);
            foreach ($failed as $path => $projectName) {
                \Message::warning(sprintf(
                    $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ERROR_FILE_IMPORT_FORMAT'],
                    $path, $projectName
                ));
            }
        } else {
            static::setLastSyncTime($thisSyncTime);
        }
    }

    /**
     * Truncate and repopulate the given table
     *
     * Inserts all records from the given CSV file.
     * Skips the first row (headers).
     * @param   string  $tablename
     * @param   string  $projectName
     * @param   \Cx\Lib\FileSystem\FileSystemFile   $file
     * @return  bool                    True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function csv2db(
        string $tablename, string $projectName,
        \Cx\Lib\FileSystem\FileSystemFile $file
    ): bool {
        global $_ARRAYLANG;
        $filePath = $file->getAbsoluteFilePath();
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            \Message::warning(sprintf(
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ERROR_FILE_READ_FORMAT'],
                $file
            ));
            return false;
        }
        $em = $this->cx->getDb()->getEntityManager();
        $className = 'Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\'
            . ucfirst($tablename);
        $qb = $em->createQueryBuilder();
        $qb->delete($className, 'e')
            ->where($qb->expr()->eq('e.project', ':project'))
            ->setParameter('project', $projectName);
        $qb->getQuery()->execute();
        $delimiter = static::getCsvDelimiter();
        $enclosure = static::getCsvEnclosure();
        $escape = static::getCsvEscape();
        // Skip headers in first row
        $row = fgetcsv($handle, 10500, $delimiter, $enclosure, $escape);
        // NOTE: Trying to create and persist entities causes either
        // a timeout, or an out of memory error.
        // Fallback to the Connection in order to get the job done.
        // Without bulk inserts, the import would still take 700+ seconds.
        // With bulk inserts of 100 or more records, it takes less than
        // 10 seconds.
        $db = $this->cx->getDb()->getEntityManager()->getConnection();
        $queries = [];
        $i = 0;
        while (true) {
            // "verbindungsstring" property values may be
            // up to 10,000 characters (according to the YAML).
            // Mind that these CSV files use ISO-8859-1!
            $row = fgetcsv($handle, 10500, $delimiter, $enclosure, $escape);
            if ($row === false) {
                break;
            }
            if ($tablename === 'connection') {
                // project, verbindungsnummer, sequenznummer, verbindungsstring
                $queries[] = sprintf('
                    (\'%1$s\', %2$u, \'%3$s\', \'%4$s\')',
                    $projectName, $row[0], addslashes($row[1]),
                    addslashes($row[2])
                );
            }
            if ($tablename === 'journey') {
                $reisedat = new \DateTime($row[1]);
                $row[1] = $reisedat->format('Y-m-d');
                // project, att, reisedat, verbnr, rbn,
                // reisen, d, at_start, at_recs
                $queries[] = sprintf('
                    (\'%1$s\', %2$u, \'%3$s\', \'%4$s\', %5$u,
                    %6$u, \'%7$s\', \'%8$s\', \'%9$s\')',
                    $projectName, $row[0], addslashes($row[1]),
                    addslashes($row[2]), $row[3],
                    $row[4], addslashes($row[5]),
                    addslashes($row[6]), addslashes($row[7])
                );
            }
            // Note: With 1000 records,
            // the longest queries are below 120K characters.
            if (++$i % 1000 === 0) {
                static::bulkInsert($db, $tablename, $queries);
                $queries = [];
            }
        }
        if ($queries) {
            static::bulkInsert($db, $tablename, $queries);
            $queries = [];
        }
        fclose($handle);
        return true;
    }

    /**
     * Insert a bunch of prepared records
     *
     * Mind that the query is converted from ISO-8859-1 to UTF-8.
     * @param   \Doctrine\DBAL\Connection   $connection
     * @param   string                      $tablename
     * @param   array                       $queries
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function bulkInsert(
        \Doctrine\DBAL\Connection $connection,
        string $tablename, array $queries
    ) {
        $query = '';
        if ($tablename === 'connection') {
            $query = '
                INSERT INTO `' . DBPREFIX . 'module_chdirtravellog_connection`(
                    `project`, `verbindungsnummer`,
                    `sequenznummer`, `verbindungsstring`
                )';
        }
        if ($tablename === 'journey') {
            $query = '
                INSERT INTO `' . DBPREFIX . 'module_chdirtravellog_journey`(
                    `project`, `att`, `reisedat`, `verbnr`, `rbn`,
                    `reisen`, `d`, `at_start`, `at_recs`
                )';
        }
        $query .= ' VALUES ' . mb_convert_encoding(
            join(',', $queries), 'UTF-8', 'ISO-8859-1'
        );
        $connection->exec($query);
    }

    /**
     * Return the URL of the PDF document
     *
     * If the file does not exist, returns the empty string
     * @param   string  $projectName
     * @param   int     $journeyNr
     * @return  string
     */
    protected function getFileUrl(
        string $projectName, int $journeyNr
    ): string {
        $journeyPath = static::getFilePath($projectName, $journeyNr);
        if (!$journeyPath) {
            return '';
        }
        $url = $this->cx->getRequest()->getUrl();
        $url->removeAllParams();
        $url->setLangDir(null);
        $url->setPort(null);
        $url->setPath($journeyPath);
        return $url->toString();
    }

    /**
     * Return the path to the PDF document
     *
     * If the file does not exist, returns the empty string
     * @param   string  $projectName
     * @param   int     $journeyNr
     * @return  string
     */
    protected static function getFilePath(
        string $projectName, int $journeyNr
    ): string
    {
        $pdfRoot = static::getPdfFolder();
        $projectName = static::getProjectName();
        $journeyPath = $pdfRoot . $projectName . '_' . $journeyNr . '.pdf';
        if (\Cx\Lib\FileSystem\FileSystem::exists($journeyPath)) {
            return $journeyPath;
        }
        $journeyPath = $pdfRoot . $projectName . '_' . $journeyNr . '_F.pdf';
        if (\Cx\Lib\FileSystem\FileSystem::exists($journeyPath)) {
            return $journeyPath;
        }
        return '';
    }

    /**
     * Export the given Journeys as CSV
     *
     * Does not return.
     * @global  array       $_ARRAYLANG
     * @param   string      $projectName
     * @param   string      $filename
     * @param   iterable    $journeys
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function exportCsv(
        string $projectName, string $filename, iterable $journeys
    ) {
        global $_ARRAYLANG;
        $filename = $filename . '.csv';
        $delimiter = static::getCsvDelimiter();
        $enclosure = static::getCsvEnclosure();
        $escape = static::getCsvEscape();
        header('Content-Type: text/comma-separated-values; charset=' . CONTREXX_CHARSET);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $handle = fopen('php://output', 'w');
        fputcsv($handle,
            [
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_DATE'],
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_CONNECTION'],
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_NAME'],
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_COUNT'],
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_JOURNEY'],
            ],
            $delimiter, $enclosure, $escape
        );
        $connectionRepo = $this->cx->getDb()->getEntityManager()->getRepository(
            'Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Connection'
        );
        foreach ($journeys as $journey) {
            $connectionNr = intval($journey->getVerbnr());
            $connection = $connectionRepo->find($connectionNr);
            $connectionName = '';
            if ($connection) {
                $connectionName = $connection->getVerbindungsstring();
            }
            fputcsv($handle,
                [
                    $journey->getReisedat()
                        ->format(ASCMS_DATE_FORMAT_DATE),
                    $journey->getVerbnr(),
                    $connectionName,
                    $journey->getReisen(),
                    $this->getFileUrl($projectName, $journey->getRbn()),
                ],
                $delimiter, $enclosure, $escape
            );
        }
        fclose($handle);
        throw new \Cx\Core\Core\Controller\InstanceException();
    }

    /**
     * Return the icons folder path, relative to the document root
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function getIconFolderPath(): string
    {
        return $this->getDirectory(true, true) . '/View/Icons/';
    }

}
