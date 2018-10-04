<?php declare(strict_types=1);

/**
 * PHP Version 7.1 - 7.2 (uses iterable type declaration)
 *
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Cx\Modules\CHDIRTravelLog\Controller;

/**
 * Travel Log (Nemesis)
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
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
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
\DBG::activate(DBG_PHP);
        \Cx\Core\Setting\Controller\Setting::init(
            $this->getName(), 'config', 'FileSystem'
        );
// TODO: Is jQuery required?
        \JS::activate('jquery');
        $this->importCsv();
        $this->viewSearch($template);
// TODO: Probby not required
//        return parent::parsePage($template, $cmd);
    }

    /**
     * Return the project name from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     */
    protected static function getProjectName(): string
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('project_name');
    }

    /**
     * Return the data folder from the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
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
     */
    protected static function getLastSyncTime()
    {
        return \Cx\Core\Setting\Controller\Setting::getValue('last_sync_time');
    }

    /**
     * Update the last synchronize time in the Settings
     *
     * Mind that the Settings must have been initialized.
     * @return  string
     */
    protected static function setLastSyncTime($lastSyncTime)
    {
        return \Cx\Core\Setting\Controller\Setting::update(
            'last_sync_time', $lastSyncTime
        );
    }


// TODO from index (class TravelLog)
    /**
     * Set up the search view
     * @global  array               $_CORELANG
     * @param   \Cx\Core\Html\Sigma $template
     */
    protected function viewSearch(\Cx\Core\Html\Sigma $template)
    {
        global $_CORELANG;
        $paramsGet = $this->cx->getRequest()->getParams();
        $searchTerm = contrexx_input2raw($paramsGet['number'] ?? '');
        $selectJourney = '';
        $selectConnection = \Html::ATTRIBUTE_SELECTED;
        $type = $paramsGet['type'] ?? '';
        if ($type === 'journey') {
            $selectConnection = '';
            $selectJourney = \Html::ATTRIBUTE_SELECTED;
        }
        $template->setGlobalVariable([
            'TXT_SEARCH' => $_CORELANG['TXT_SEARCH'],
            'TRAVELLOG_NUMBER' => $searchTerm,
            'TRAVELLOG_SELECTED_CONNECTION' => $selectConnection,
            'TRAVELLOG_SELECTED_JOURNEY' => $selectJourney,
        ]);
        if (empty($paramsGet['search'])) {
//            $template->hideBlock('travellog_journeys');
//            $template->hideBlock('travellog_connections');
            return;
        }
        $pos = intval($paramsGet['pos'] ?? 0);
        $exportCsv = (bool)($_GET['csv'] ?? false);
        $count = 0;
        switch ($type) {
            case 'connection':
                $count = $this->parseConnections(
                    $template, $searchTerm, $pos, $exportCsv
                );
                break;
            case 'journey':
                $count = $this->parseJourneys(
                    $template, $searchTerm, $pos, $exportCsv
                );
                break;
        }
        if ($count === 0) {
            $template->touchBlock('travellog_no_results');
//            $template->hideBlock('travellog_journeys');
//            $template->hideBlock('travellog_connections');
        }
    }

    /**
     * List the Connections in the search results view
     * @global  array   $_ARRAYLANG
     * @global  array   $_CONFIG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string  $searchTerm
     * @param   int     $pos
     * @param   bool    $exportCsv
     * @return  int                 The total result count
     */
    protected function parseConnections(
        \Cx\Core\Html\Sigma $template,
        string $searchTerm, int $pos, bool $exportCsv
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
                    $qb->expr()->neq('j.att', ':att'),
                    $qb->expr()->neq('j.d', ':d')
                )
            )
            ->setParameters([
                'att' => '111',
                'd' => 'X',
            ])
        ;
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
            $this->exportCsv('connection_nr_' . $searchTerm, $journeys);
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
        $template->setGlobalVariable([
            'TRAVELLOG_QUERIED_CONNECTION' => $searchTerm . ' ' . $connectionName,
            'TRAVELLOG_PAGING' => $paging,
            'TRAVELLOG_NUM_JOURNEYS' => $count,
            'TRAVELLOG_EXPORT_RESULTS' =>
            '<a href="' . $_SERVER['REQUEST_URI'] . '&csv=1"'
            .' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '">'
            .'<img src="' . $this->getIconFolderPath() . 'xls.png"'
            .' style="height: 20px; width: auto; margin: 0 0 15px 0 !important; float: right;"'
            .' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '" />'
            .'</a>',
        ]);
//        $template->parse('travellog_journeys');
//        $template->hideBlock('travellog_connections');
        foreach ($journeys as $journey) {
            $template->setVariable([
                'TRAVELLOG_JOURNEY_RNB_LINK' =>
                $this->getFileLink($journey->getRbn()),
                'TRAVELLOG_JOURNEY_RNB' => $journey->getRbn(),
                'TRAVELLOG_JOURNEY_DATE' => $journey->getReisedat()
                    ->format(static::date_format_ymd),
                'TRAVELLOG_JOURNEY_NUMOF_CONNECTION' =>
                $journey->getReisen(),
                'TRAVELLOG_JOURNEY_CONNECTION_NR' =>
                $journey->getVerbnr(),
                'TRAVELLOG_JOURNEY_CONNECTION_NAME' => $connectionName,
            ]);
            $template->parse('travellog_journey_results');
        }
        return $count;
    }


    /**
     * List the Journeys in the search results view
     * @global  array   $_ARRAYLANG
     * @global  array   $_CONFIG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string  $searchTerm
     * @param   int     $pos
     * @param   bool    $exportCsv
     * @return  int                 The total result count
     */
    protected function parseJourneys(
        \Cx\Core\Html\Sigma $template,
        string $searchTerm, int $pos, bool $exportCsv
    ): int
    {
        global $_ARRAYLANG, $_CONFIG;
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('j')
            ->from('Cx\\Modules\\CHDIRTravelLog\\Model\\Entity\\Journey', 'j')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->neq('j.att', ':att'),
                    $qb->expr()->neq('j.d', ':d'),
                    $qb->expr()->eq('j.rbn', ':term')
                )
            )
            ->setParameters([
                'att' => '111',
                'd' => 'X',
                'term' => $searchTerm,
                ])
            ->orderBy('j.reisedat')
        ;
        if ($exportCsv) {
            $journeys = $qb->getQuery()->getResult();
            // exit()s
            $this->exportCsv('datasheet_nr_' . $searchTerm, $journeys);
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
        $template->setGlobalVariable([
            'TRAVELLOG_QUERIED_JOURNEY' =>
            '<a target="_blank" href="' . static::getPdfFolder()
            . static::getProjectName() . '_' . $searchTerm . '.pdf">'
            . $searchTerm . '</a>',
            'TRAVELLOG_NUM_CONNECTIONS' => $count,
            'TRAVELLOG_PAGING' => $paging,
            'TRAVELLOG_EXPORT_RESULTS' =>
            '<a href="' . $_SERVER['REQUEST_URI'] . '&csv=1"'
            .' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '">'
            .'<img src="' . $this->getIconFolderPath() . 'xls.png"'
            .' style="height: 20px; width: auto; margin: 0 0 15px 0 !important; float: right;"'
            .' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_TITLE'] . '" />'
            .'</a>',
        ]);
//        $template->parse('travellog_connections');
//        $template->hideBlock('travellog_journeys');
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
            $template->setVariable(array(
                'TRAVELLOG_CONNECTION_RBN_LINK' => $this->getFileLink($searchTerm),
                'TRAVELLOG_CONNECTION_RBN' => $journey->getRbn(),
                'TRAVELLOG_CONNECTION_DATE' => $journey->getReisedat()
                    ->format(static::date_format_ymd),
                'TRAVELLOG_CONNECTION_NUMOF_CONNECTION' => $journey->getReisen(),
                'TRAVELLOG_CONNECTION_NR' => $journey->getVerbnr(),
                'TRAVELLOG_CONNECTION_NAME' => $connectionName,
            ));
            $template->parse('travellog_connection_results');
        }
        return $count;
    }

    /**
     * Update the database tables from the CSV files
     *
     * Skips updating iff neither of the files has been modified.
     */
    function importCsv()
    {
        $dataRoot = static::getDataFolder();
        $projectName = static::getProjectName();
        $lastSyncTime = static::getLastSyncTime();
        $connectionsFile = new \Cx\Lib\FileSystem\FileSystemFile(
            $dataRoot . $projectName . '_Verbindungen.csv'
        );
        $journeysFile = new \Cx\Lib\FileSystem\FileSystemFile(
            $dataRoot . $projectName . '_FAHRT.csv'
        );
\DBG::log("dataroot $dataRoot");
\DBG::log("connectionsFile {$connectionsFile->getAbsoluteFilePath()}");
\DBG::log("journeysFile {$journeysFile->getAbsoluteFilePath()}");
        if (filemtime($connectionsFile->getAbsoluteFilePath()) < $lastSyncTime
            && filemtime($journeysFile->getAbsoluteFilePath()) < $lastSyncTime
        ) {
            return;
        }
        // Avoid race condition: Mark the time *before* starting the import
        $lastSyncTime = time();
        if ($this->csv2db('connection', $connectionsFile)
            && $this->csv2db('journey', $journeysFile)
        ) {
            // Update on success only
            static::setLastSyncTime($lastSyncTime);
        }
// TODO: Message, at least on error
    }

    /**
     * Truncate and repopulate the given table
     *
     * Inserts all records from the given CSV file.
     * Skips the first row (headers).
     * @param   string  $tablename
     * @param   \Cx\Lib\FileSystem\FileSystemFile   $file
     * @return  bool                    True on success, false otherwise
     */
    function csv2db(
        string $tablename, \Cx\Lib\FileSystem\FileSystemFile $file
    ): bool
    {
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
// TODO: See whether that works
        // https://stackoverflow.com/a/17068337/3396113
        $cmd = $em->getClassMetadata($className);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
        $delimiter = static::getCsvDelimiter();
        $enclosure = static::getCsvEnclosure();
        $escape = static::getCsvEscape();
        $header = null;
        while (true) {
            // "verbindungsstring" property values may be
            // up to 10,000 characters (according to the YAML).
            $row = fgetcsv($handle, 10500, $delimiter, $enclosure, $escape);
            if ($row === false) {
                break;
            }
// TODO: Why were these fields cleared?
//            if ($tablename === 'connection') {
//                unset($row[1]);
//            }
//            if ($tablename === 'journey') {
//                array_pop($row);
//            }
            if (!$header) {
                $header = true;
                continue;
            }
            $entity = new $className();
            if ($tablename === 'connection') {
                $entity->setVerbindungsnummer($row[0]);
                $entity->setSequenznummer($row[1]);
                $entity->setVerbindungsstring($row[2]);
            }
            if ($tablename === 'journey') {
                $entity->setAtt($row[0]);
                $entity->setReisedat($row[1]);
                $entity->setVerbnr($row[2]);
                $entity->setRbn($row[3]);
                $entity->setReisen($row[4]);
                $entity->setD($row[5]);
                $entity->setAtStart($row[6]);
                $entity->setAtRecs($row[7]);
            }
            $em->persist($entity);
        }
        fclose($handle);
        return true;
    }

    function getFileLink($journeyNr, $urlOnly = null)
    {
        global $_ARRAYLANG;
        $dataRoot = static::getDataFolder();
        $projectName = static::getProjectName();
        $journeyIcon = '<img src="' . $this->getIconFolderPath() . 'pdf.png"'
            . ' style="height: 20px; width: auto; margin: 0 !important;"'
            . ' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_DOWNLOAD_ICON_TITLE'] . '"'
            . ' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_DOWNLOAD_ICON_TITLE'] . '"'
            . ' />';
        $journeyPath = $dataRoot
            . 'pdf/' . $projectName . '_' . $journeyNr . '.pdf';
\DBG::log($journeyPath);
        $domain = $this->cx->getRequest()->getUrl()->getDomain();
        if (\Cx\Lib\FileSystem\FileSystem::exists($journeyPath)) {
\DBG::log('exists');
            if ($urlOnly) {
                return '//' . $domain . $journeyPath;
            }
\DBG::log('does not exist');
            return '<a target="_blank" href="' . $journeyPath . '" >'
                . $journeyIcon . '</a>';
        }
        $journeyPath = $dataRoot
            . 'pdf/' . $projectName . '_' . $journeyNr . '_F.pdf';
        if (\Cx\Lib\FileSystem\FileSystem::exists($journeyPath)) {
            if ($urlOnly) {
                return '//' . $domain . $journeyPath;
            }
            return '<a target="_blank" href="' . $journeyPath . '" >'
                . $journeyIcon . '</a>';
        }
        if ($urlOnly) {
            return null;
        }
        return '<img src="' . $this->getIconFolderPath() . 'blank.gif"'
            . ' style="margin: 0 !important;"'
            . ' title="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_NO_DOWNLOAD_FOUND'] . '"'
            . ' alt="' . $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_NO_DOWNLOAD_FOUND'] . '"'
            . ' />';
    }

    /**
     * Export the given Journeys as CSV
     *
     * Does not return.
     * @global  array       $_ARRAYLANG
     * @param   iterable    $journeys
     * @param   string      $filename
     */
    function exportCsv(iterable $journeys, string $filename)
    {
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
                $_ARRAYLANG['TXT_MODULE_CHDIRTRAVELLOG_EXPORT_NUM'],
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
                    $journey->getReisedat(),
                    $journey->getVerbnr(),
                    $connectionName,
                    $journey->getReisen(),
                    $this->getFileLink($journey->getRbn(), true)
                ],
                $delimiter, $enclosure, $escape);
        }
        fclose($handle);
        exit();
    }

    /**
     * Return the icons folder path, relative to the document root
     * @return  string
     */
    protected function getIconFolderPath(): string
    {
        return $this->cx->getWebsiteOffsetPath()
            . '/modules/' . $this->getName()
            . '/View/Icons/';
    }

}
