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
     * Date format for output
     */
    const date_format_ymd = 'd.m.Y';

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
    protected static function getDataFolder()
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
    protected static function getPdfFolder()
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
    protected static function getCsvDelimiter()
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
    protected static function getCsvEnclosure()
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
    protected static function getCsvEscape()
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('csv_escape');
    }

    /**
     * Return the last synchronize time from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getLastSyncTime()
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('last_sync_time');
    }

    /**
     * Update the last synchronize time in the Settings
     *
     * Mind that the Settings must have been initialized.
     * @param   integer $lastSyncTime
     * @return  bool|null
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function setLastSyncTime($lastSyncTime)
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
        $searchTerm = contrexx_input2raw($paramsGet['number'] ?? '');
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
            'CHDIRTRAVELLOG_NUMBER' => $searchTerm,
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
     * @global  array   $_CONFIG
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
    ): int
    {
        global $_ARRAYLANG, $_CONFIG;
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
                $qb->expr()->like(
                'j.verbnr', ':term'
            ))
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
            $qb->getQuery(), false);
        $count = count($journeys);
        $qb->setFirstResult($pos)
            ->setMaxResults($_CONFIG['corePagingLimit']);
        $paramsGet = $this->cx->getRequest()->getParams();
        $paging = \Paging::get(
            $paramsGet,
            '<b>' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ENTRIES'] . '</b>',
            $count, $_CONFIG['corePagingLimit'], false, $pos, 'pos'
        );
        $template->setVariable([
            'CHDIRTRAVELLOG_CONNECTION_QUERIED' => $searchTerm . ' ' . $connectionName,
            'CHDIRTRAVELLOG_PAGING' => $paging,
            'CHDIRTRAVELLOG_CONNECTION_COUNT' => $count,
            'CHDIRTRAVELLOG_EXPORT_RESULTS' =>
            '<a href="' . $_SERVER['REQUEST_URI'] . '&csv=1"'
            .' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '">'
            .'<img src="' . $this->getIconFolderPath() . 'xls.png"'
            .' style="height: 20px; width: auto; margin: 0 0 15px 0 !important; float: right;"'
            .' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '" />'
            .'</a>',
        ]);
        $this->parseJourneys($template, $projectName, $journeys);
        return $count;
    }

    /**
     * List the Journeys in the search results view
     * @global  array   $_ARRAYLANG
     * @global  array   $_CONFIG
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
    ): int
    {
        global $_ARRAYLANG, $_CONFIG;
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
        $qb->setFirstResult($pos)
            ->setMaxResults($_CONFIG['corePagingLimit']);
        $journeys = new \Doctrine\ORM\Tools\Pagination\Paginator(
            $qb->getQuery(), false);
        $count = count($journeys);
        $paramsGet = $this->cx->getRequest()->getParams();
        $paging = \Paging::get(
            $paramsGet,
            '<b>' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_ENTRIES'] . '</b>',
            $count, $_CONFIG['corePagingLimit'], false, $pos, 'pos'
        );
        $template->setVariable([
            'CHDIRTRAVELLOG_JOURNEY_QUERIED' =>
            '<a target="_blank" href="' . static::getPdfFolder()
            . static::getProjectName() . '_' . $searchTerm . '.pdf">'
            . $searchTerm . '</a>',
            'CHDIRTRAVELLOG_JOURNEY_COUNT' => $count,
            'CHDIRTRAVELLOG_PAGING' => $paging,
            'CHDIRTRAVELLOG_EXPORT_RESULTS' =>
            '<a href="' . $_SERVER['REQUEST_URI'] . '&csv=1"'
            .' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '">'
            .'<img src="' . $this->getIconFolderPath() . 'xls.png"'
            .' style="height: 20px; width: auto; margin: 0 0 15px 0 !important; float: right;"'
            .' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '" />'
            .'</a>',
        ]);
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
        static $connectionRepo = null;
        if (!$connectionRepo) {
            $connectionRepo =
                $this->cx->getDb()->getEntityManager()->getRepository(
                    'Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Connection'
                );
        }
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
            $template->setVariable([
                'CHDIRTRAVELLOG_JOURNEY_RBN_LINK' =>
                    $this->getFileLink($projectName, $journey->getRbn()),
                'CHDIRTRAVELLOG_JOURNEY_RBN' => $journey->getRbn(),
                'CHDIRTRAVELLOG_JOURNEY_DATE' => $journey->getReisedat()
                    ->format(static::date_format_ymd),
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
    function importCsv()
    {
        global $_ARRAYLANG;
        $dataRoot = static::getDataFolder();
        $projectNames = static::getProjectNames();
        $lastSyncTime = static::getLastSyncTime();
        // Avoid race condition: Mark the time *before* starting the import
        // (works even if the files are modified while the import is running).
        $thisSyncTime = time();
        $success = true;
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
            if (!$this->csv2db('connection', $projectName, $connectionsFile)
                || !$this->csv2db('journey', $projectName, $journeysFile)
            ) {
                $success = false;
            }
        }
        if ($success) {
            static::setLastSyncTime($thisSyncTime);
        } else {
// TODO: Message, at least on error
            \Message::warning($_ARRAYLANG['']);
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
    function csv2db(
        string $tablename, string $projectName,
        \Cx\Lib\FileSystem\FileSystemFile $file
    ): bool
    {
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
        $className = '\\Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\'
            . ucfirst($tablename);
        // https://stackoverflow.com/a/17068337/3396113
        $cmd = $em->getClassMetadata($className);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $query = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($query);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
        $delimiter = static::getCsvDelimiter();
        $enclosure = static::getCsvEnclosure();
        $escape = static::getCsvEscape();
        // Skip headers in first row
        $row = fgetcsv($handle, 10500, $delimiter, $enclosure, $escape);
        ini_set('max_execution_time', '120');
        // NOTE: Trying to create and persist entities causes either
        // a timeout, or an out of memory error.
        // Fallback to AdoDb in order to get the job done.
        // Without bulk inserts, the import would still take 700+ seconds.
        // With bulk inserts of 100 or more records, it takes less than
        // 10 seconds.
        $db = $this->cx->getDb()->getAdoDb();
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
                // verbindungsnummer, project, sequenznummer, verbindungsstring
                $queries[] = sprintf('
                    (%1$u, \'%2$s\', \'%3$s\', \'%4$s\')',
                    $row[0], $projectName, addslashes($row[1]),
                    addslashes($row[2])
                );
            }
            if ($tablename === 'journey') {
                $reisedat = new \DateTime($row[1]);
                $row[1] = $reisedat->format('Y-m-d');
                // att, project, reisedat, verbnr, rbn,
                // reisen, d, at_start, at_recs
                $queries[] = sprintf('
                    (%1$u, \'%2$s\', \'%3$s\', \'%4$s\', %5$u,
                    %6$u, \'%7$s\', \'%8$s\', \'%9$s\')',
                    $row[0], $projectName, addslashes($row[1]),
                    addslashes($row[2]), $row[3],
                    $row[4], addslashes($row[5]),
                    addslashes($row[6]), addslashes($row[7])
                );
            }
            // Note: With 500 records, the queries are too long.
            // 400 works, 200 is pretty safe.
            if (++$i % 200 === 0) {
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
     * @param   \ADOConnection  $db
     * @param   string          $tablename
     * @param   array           $queries
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function bulkInsert(
        \ADOConnection $db, string $tablename, array $queries)
    {
        $query = '';
        if ($tablename === 'connection') {
            $query = '
                INSERT INTO `contrexx_module_chdirtravellog_connection`(
                    `verbindungsnummer`, `project`, `sequenznummer`, `verbindungsstring`
                )';
        }
        if ($tablename === 'journey') {
            $query = '
                INSERT INTO `contrexx_module_chdirtravellog_journey`(
                    `att`, `project`, `reisedat`, `verbnr`, `rbn`,
                    `reisen`, `d`, `at_start`, `at_recs`
                )';
        }
        $query .= ' VALUES ' . mb_convert_encoding(
            join(',', $queries), 'UTF-8', 'ISO-8859-1'
        );
        $db->Execute($query);
    }

    /**
     * Return the link to the PDF document
     *
     * Includes an icon in the <a> tag.
     * Set $urlOnly to true in order to obtain the URL only; required for CSV.
     * @global  array   $_ARRAYLANG
     * @param   string  $projectName
     * @param   int     $journeyNr
     * @param   bool    $urlOnly       Exclude all HTML if true
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    function getFileLink(
        string $projectName, int $journeyNr, bool $urlOnly = false
    ): string
    {
        global $_ARRAYLANG;
        $pdfRoot = static::getPdfFolder();
        $projectName = static::getProjectName();
        $journeyIcon = '<img src="' . $this->getIconFolderPath() . 'pdf.png"'
            . ' style="height: 20px; width: auto; margin: 0 !important;"'
            . ' title="'
            . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_DOWNLOAD_ICON_TITLE'] . '"'
            . ' alt="'
            . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_DOWNLOAD_ICON_TITLE'] . '"'
            . ' />';
        $journeyPath = $pdfRoot . $projectName . '_' . $journeyNr . '.pdf';
        $protocol = $this->cx->getRequest()->getUrl()->getProtocol();
        $domain = $this->cx->getRequest()->getUrl()->getDomain();
        if (\Cx\Lib\FileSystem\FileSystem::exists($journeyPath)) {
            if ($urlOnly) {
                return $protocol . '://' . $domain . $journeyPath;
            }
            return '<a target="_blank" href="' . $journeyPath . '" >'
                . $journeyIcon . '</a>';
        }
        $journeyPath = $pdfRoot . $projectName . '_' . $journeyNr . '_F.pdf';
        if (\Cx\Lib\FileSystem\FileSystem::exists($journeyPath)) {
            if ($urlOnly) {
                return $protocol . '://' . $domain . $journeyPath;
            }
            return '<a target="_blank" href="' . $journeyPath . '" >'
                . $journeyIcon . '</a>';
        }
        if ($urlOnly) {
            return '';
        }
        return '<img src="' . $this->getIconFolderPath() . 'blank.gif"'
            . ' style="margin: 0 !important;"'
            . ' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_WARNING_NO_DOWNLOAD_FOUND'] . '"'
            . ' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_WARNING_NO_DOWNLOAD_FOUND'] . '"'
            . ' />';
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
    function exportCsv(
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
                    $journey->getReisedat()->format(static::date_format_ymd),
                    $journey->getVerbnr(),
                    $connectionName,
                    $journey->getReisen(),
                    $this->getFileLink($projectName, $journey->getRbn(), true)
                ],
                $delimiter, $enclosure, $escape);
        }
        fclose($handle);
        exit();
    }

    /**
     * Return the icons folder path, relative to the document root
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function getIconFolderPath(): string
    {
        return $this->cx->getWebsiteOffsetPath()
            . '/modules/' . $this->getName()
            . '/View/Icons/';
    }

}
