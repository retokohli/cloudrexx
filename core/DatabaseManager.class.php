<?php

/**
 * Error reporting level
 * @ignore
 */
define('_DBM_DEBUG', 0);

/**
 * Database Manager class
 *
 * CMS Database Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  core
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch> (Pre 2.1.0)
 * @author      Reto Kohli <reto.kohli@comvation.com> (Version 2.1.0)
 * @version     2.1.0
 */

/**
 * @ignore
 */
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';

/**
 * Database Manager class
 *
 * CMS Database Manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  core
 * @author      Thomas Kaelin <thomas.kaelin@astalvista.ch> (Pre 2.1.0)
 * @author      Reto Kohli <reto.kohli@comvation.com> (Version 2.1.0)
 * @version     2.1.0
 * @internal    Use the core Filetype class to handle MIME types
 */
class DatabaseManager
{
    /**
     * @var string
     * @desc page title
     */
    public $_strPageTitle;

    /**
     * @var string
     * @desc status message (error message)
     */
    private static $strErrMessage = '';

    /**
     * @var string
     * @desc status message (okay message)
     */
    private static $strOkMessage = '';

    /**
     * @var string
     * @desc path to backup-folder
     */
    public $_strBackupPath;

    /**
     * @var array
     * @desc stores file endings for different export-types.
     */
    public $_arrFileEndings;

    /**
     * @var array
     * @desc stores mime types for different export-types.
     */
    public $_arrMimeTypes;

    /**
     * Constructor
     * @global  HTML_Template_Sigma
     * @global  array
     */
    function __construct()
    {
        global $objTemplate, $_CORELANG;

        $this->_strBackupPath = ASCMS_BACKUP_PATH.'/';
        $this->_arrFileEndings = array(
            'sql' => '.sql',
            'csv' => '.csv',
        );
        $this->_arrMimeTypes = array(
            'sql' => 'application/x-unknown',
            'csv' => 'text/comma-separated-values',
        );
        $objTemplate->setVariable(
            'CONTENT_NAVIGATION',
            '<a href="index.php?cmd=dbm">'.$_CORELANG['TXT_DBM_MAINTENANCE_TITLE'].'</a>'.
            (Permission::hasAllAccess()
              ? '<a href="index.php?cmd=dbm&amp;act=sql">'.$_CORELANG['TXT_DBM_SQL_TITLE'].'</a>'.
                '<a href="index.php?cmd=dbm&amp;act=status">'.$_CORELANG['TXT_DBM_STATUS_TITLE'].'</a>'
              : ''
            )
        );
    }


    /**
     * Dispatches to the desired function.
     * @global  HTML_Template_Sigma $objTemplate
     * @global  array               $_CORELANG
     */
    function getPage()
    {
        global $objTemplate;

        if (!isset($_GET['act'])) $_GET['act'] = '';

        // Check permission to access this module
        Permission::checkAccess(20, 'static');

        switch ($_GET['act']) { 
            case 'showTable':
                if (Permission::hasAllAccess()) {
                    $this->showTable($_GET['table']);
                } else {
                    Permission::noAccess();
                }
                break;
            case 'optimize':
                Permission::checkAccess(41, 'static');
                $this->optimizeDatabase();
                $this->showMaintenance();
                break;
            case 'repair':
                Permission::checkAccess(41, 'static');
                $this->repairDatabase();
                $this->showMaintenance();
                break;
            case 'status':
                if (Permission::hasAllAccess()) {
                    $this->showStatus();
                } else {
                    Permission::noAccess();
                }
                break;
            case 'sql':
                if (Permission::hasAllAccess()) {
                    $this->showQuery();
                } else {
                    Permission::noAccess();
                }
                break;
// Added 2.1.0
            case 'csv':
                // Kick unauthorised users out
                if (!Permission::hasAllAccess()) Permission::noAccess();
                $this->showCsv();
                break;

            default:
                Permission::checkAccess(41, 'static');
                $this->showMaintenance();
                break;
        }
        $objTemplate->setVariable(array(
            'CONTENT_TITLE' => $this->_strPageTitle,
            'CONTENT_OK_MESSAGE' => self::$strOkMessage,
            'CONTENT_STATUS_MESSAGE' => self::$strErrMessage,
        ));
    }


    /**
     * Shows useful information about the database.
     *
     * @global  HTML_Template_Sigma
     * @global  ADONewConnection
     * @global  array
     * @global  array
     */
    function showStatus()
    {
        global  $objTemplate, $objDatabase, $_CORELANG, $_DBCONFIG;

        $this->_strPageTitle = $_CORELANG['TXT_DBM_STATUS_TITLE'];
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'status', 'dbm_status.html');
        $objTemplate->setVariable(array(
            'TXT_STATUS_TITLE' => $_CORELANG['TXT_DBM_STATUS_TITLE'],
            'TXT_STATUS_VERSION' => $_CORELANG['TXT_DBM_STATUS_MYSQL_VERSION'],
            'TXT_STATUS_TABLES' => $_CORELANG['TXT_DBM_STATUS_USED_TABLES'],
            'TXT_STATUS_SIZE' => $_CORELANG['TXT_DBM_STATUS_USED_SPACE'],
            'TXT_STATUS_BACKLOG' => $_CORELANG['TXT_DBM_STATUS_BACKOG'],
            'TXT_CONNECTION_TITLE' => $_CORELANG['TXT_DBM_CONNECTION_TITLE'],
            'TXT_CONNECTION_DBPREFIX' => $_CORELANG['TXT_DBM_CONNECTION_DBPREFIX'],
            'TXT_CONNECTION_DATABASE' => $_CORELANG['TXT_DBM_CONNECTION_DATABASE'],
            'TXT_CONNECTION_USERNAME' => $_CORELANG['TXT_DBM_CONNECTION_USERNAME'],
        ));

        // Get version
        $objResult = $objDatabase->Execute('SELECT VERSION()');
        $strVersion = $objResult->fields['VERSION()'];

        // Get table status
        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');
        $intTables = $objResult->RecordCount();

        $intSize = 0;
        $intBacklog = 0;
        while (!$objResult->EOF) {
            $intSize    += $objResult->fields['Data_length'] + $objResult->fields['Index_length'];
            $intBacklog += $objResult->fields['Data_free'];
            $objResult->MoveNext();
        }

        $objTemplate->setVariable(array(
            'STATUS_VERSION' => $strVersion,
            'STATUS_TABLES' => $intTables,
            'STATUS_SIZE' => $this->convertBytesToKBytes($intSize),
            'STATUS_BACKLOG' => $this->convertBytesToKBytes($intBacklog),
            'CONNECTION_DBPREFIX' => DBPREFIX,
            'CONNECTION_DATABASE' => $_DBCONFIG['database'],
            'CONNECTION_USERNAME' => $_DBCONFIG['user'],
        ));

        //Filter mySQL-Info
        ob_start();
        phpinfo();
        $strPhpInfo = ob_get_contents();
        ob_end_clean();

        $arrBlocks = array();
        $arrTables = array();
        $arrColumns = array();
        $arrRows = array();

        //Collect all blocks containing mysql-information
        preg_match_all('/<h2><a name="module_mysql.*">mysql.*<\/a><\/h2>(.*<\/table><br \/>) {2}\n/sU', $strPhpInfo, $arrBlocks);    //Modifier s = Use string as single-row, Modifier U = just be Ungreedy!
        foreach ($arrBlocks[0] as $strBlock) {
            // Get title of the block
            $strTitle = preg_replace('/<h2>.*>(.*)<\/a><\/h2>.*/s', '$1', $strBlock);
            // Get tables of the block
            preg_match_all('/<table.*<\/table>/sU', $strBlock, $arrTables);
            foreach ($arrTables[0] as $intTableKey => $strTable) {
                //Get column-headers of this table
                $strColumnHeaders = '';
                preg_match_all('/<th>.*<\/th>/U', $strTable, $arrColumns);

                $intColumWidthLast = 100; //Used to calculate the column-width.
                foreach ($arrColumns[0] as $intColumnKey => $strColumn) {
                    $strColumnHeaders .= preg_replace('/<th>(.*)<\/th>/', '<td width="'.(($intColumnKey + 1 == count($arrColumns[0])) ? $intColumWidthLast : 15).'%" nowrap="nowrap">$1</td>', $strColumn);
                    $intColumWidthLast -= 15;
                }

                $objTemplate->setVariable(array(
                    'TABLE_TITLES_CLASS' => ($intTableKey == 0) ? 'row1' : 'row3',
                    'TABLE_TITLES' => $strColumnHeaders
                ));

                //Get content of this table
                $strRowContent = '';
                preg_match_all('/<tr><td class="e">.*<\/td><\/tr>/sU', $strTable, $arrRows);

                foreach($arrRows[0] as $intRowKey => $strRow) {
                    $strRow = preg_replace('/<tr>/U', '<tr class="row'.($intRowKey % 2).'">', $strRow);
                    $strRowContent .= preg_replace('/<td class=".*">(.*)<\/td>/U', '<td>$1</td>', $strRow);
                }
                $objTemplate->setVariable('TABLE_CONTENT', $strRowContent);
                $objTemplate->parse('showPhpTables');
            }
            $objTemplate->setVariable(array(
                'TXT_PHPINFO_TITLE' => $_CORELANG['TXT_DBM_STATUS_PHPINFO'],
                'BLOCK_TITLE' => $strTitle
            ));
            $objTemplate->parse('showPhpBlocks');
        }
    }


    /**
     * Shows the database-maintenance page.
     *
     * @global     HTML_Template_Sigma
     * @global     ADONewConnection
     * @global     array
     */
    function showMaintenance()
    {
        global  $objTemplate, $objDatabase, $_CORELANG;

        $this->_strPageTitle = $_CORELANG['TXT_DBM_MAINTENANCE_TITLE'];

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'maintenance', 'dbm_maintenance.html');
        $objTemplate->setVariable(array(
            'TXT_MAINTENANCE_OPTIMIZE_TITLE' => $_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DB'],
            'TXT_MAINTENANCE_OPTIMIZE_BUTTON' => $_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_START'],
            'TXT_MAINTENANCE_OPTIMIZE_DESC' => $_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DESC'],
            'TXT_MAINTENANCE_REPAIR_TITLE' => $_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_DB'],
            'TXT_MAINTENANCE_REPAIR_BUTTON' => $_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_START'],
            'TXT_MAINTENANCE_REPAIR_DESC' => $_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_DESC'],
            'TXT_MAINTENANCE_TITLE_TABLES' => $_CORELANG['TXT_DBM_MAINTENANCE_TABLES'],
            'TXT_MAINTENANCE_TABLES_NAME' => $_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'],
            'TXT_MAINTENANCE_TABLES_ROWS' => $_CORELANG['TXT_DBM_MAINTENANCE_ROWS'],
            'TXT_MAINTENANCE_TABLES_DATA' => $_CORELANG['TXT_DBM_MAINTENANCE_DATA_SIZE'],
            'TXT_MAINTENANCE_TABLES_INDEXES' => $_CORELANG['TXT_DBM_MAINTENANCE_INDEX_SIZE'],
            'TXT_MAINTENANCE_TABLES_BACKLOG' => $_CORELANG['TXT_DBM_STATUS_BACKOG'],
            'TXT_MAINTENANCE_TABLES_SELECT_ALL' => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_MAINTENANCE_TABLES_DESELECT_ALL' => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_MAINTENANCE_TABLES_SUBMIT_SELECT' => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_MAINTENANCE_TABLES_SUBMIT_OPTIMIZE' => $_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_START'],
            'TXT_MAINTENANCE_TABLES_SUBMIT_REPAIR' => $_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_START'],
        ));

        //Get tables
        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');
        $intRowCounter = 0;

        //Iterate through tables
        while (!$objResult->EOF) {
            $isInnoDbEngine = $objResult->fields['Engine'] == 'InnoDB';
            $objTemplate->setGlobalVariable(array(
                'TXT_MAINTENANCE_SHOW_TABLE' => $_CORELANG['TXT_DBM_SHOW_TABLE_TITLE'],
                'MAINTENANCE_TABLES_NAME' => $objResult->fields['Name']
            ));

            $objTemplate->setVariable(array(
                'MAINTENANCE_TABLES_ROW' => (!$isInnoDbEngine && $objResult->fields['Data_free'] != 0) ? 'Warn' : (($intRowCounter % 2 == 0) ? 2 : 1),
                'MAINTENANCE_TABLES_ROWS' => $objResult->fields['Rows'],
                'MAINTENANCE_TABLES_DATA' => $this->convertBytesToKBytes($objResult->fields['Data_length']),
                'MAINTENANCE_TABLES_INDEXES' => $this->convertBytesToKBytes($objResult->fields['Index_length']),
                'MAINTENANCE_TABLES_BACKLOG' => $isInnoDbEngine ? '0' : $this->convertBytesToKBytes($objResult->fields['Data_free']),
            ));

            if (Permission::hasAllAccess()) {
                $objTemplate->touchblock('showTableContentLink');
                $objTemplate->hideBlock('showTableContentNoLink');
            } else {
                $objTemplate->touchblock('showTableContentNoLink');
                $objTemplate->hideBlock('showTableContentLink');
            }

            $objTemplate->parse('showTables');

            ++$intRowCounter;
            $objResult->MoveNext();
        }
    }


    /**
     * Shows content and sql-dump of a single table.
     *
     * @global     HTML_Template_Sigma
     * @global     ADONewConnection
     * @global     array
     * @param         string        $strTableName: This table will be shown.
     */
    function showTable($strTableName)
    {
        global $objTemplate, $objDatabase, $_CORELANG;

        $this->_strPageTitle = $_CORELANG['TXT_DBM_SHOW_TABLE_TITLE'];

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'show_table', 'dbm_show_table.html');
        $objTemplate->setVariable(array(
            'TXT_SHOW_TABLE_HTML_MENU' => $_CORELANG['TXT_DBM_SHOW_TABLE_HTML_TITLE'],
            'TXT_SHOW_TABLE_HTML_TITLE' => $_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'].':&nbsp;'.$strTableName,
            'TXT_SHOW_TABLE_DUMP_MENU' => $_CORELANG['TXT_DBM_SHOW_TABLE_DUMP_TITLE'],
            'TXT_SHOW_TABLE_DUMP_TITLE' => $_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'].':&nbsp;'.$strTableName,
            'TXT_SHOW_TABLE_DUMP_BUTTON_SELECT' => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_SHOW_TABLE_BUTTON_BACK' => ucfirst($_CORELANG['TXT_BACK'])
        ));

        //Check for contrexx-table
        if (strpos($strTableName, DBPREFIX) !== 0) {
            self::addError($_CORELANG['TXT_DBM_SHOW_TABLE_WRONG_PREFIX']);
            $objTemplate->hideBlock('showTable');
            return;
        }

        //Get column names
        $arrColumnNames = array();
        $arrColumnWidths = array();

        $strColumnNames = '';

        $objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTableName);
        while (!$objResult->EOF) {
            $arrColumnNames[$objResult->fields['Field']] = '';
            $arrColumnWidths[$objResult->fields['Field']] = strlen($objResult->fields['Field']);

            $strColumnNames .= '<td><b>'.$objResult->fields['Field'].'</b></td>';

            $objResult->MoveNext();
        }

        //Get content
        $strTableContent = '';

        $intRowClass = 0;

        $objTableContent = $objDatabase->Execute('SELECT * FROM '.$strTableName);
        while (!$objTableContent->EOF) {
            $strTableContent .= '<tr class="row'.(($intRowClass++) % 2).'">';

            foreach(array_keys($arrColumnNames) as $strColumnName) {
                $strColumnContent = $this->_prepareOutput($objTableContent->fields[$strColumnName]);
                $strTableContent .= '<td style="vertical-align: top;">'.$strColumnContent.'</td>';

                //Measure sizes for plain-text view
                if (strlen($strColumnContent) > $arrColumnWidths[$strColumnName]) {
                    $arrColumnWidths[$strColumnName] = mb_strlen($strColumnContent);
                }
            }

            $strTableContent .= '</tr>';

            $objTableContent->MoveNext();
        }

        //Get SQL Dump
        $objBackup = new SQLBackup();

        $strSqlDump = '';
        $strSqlDump .= $objBackup->getTableDefinition($strTableName);
        $strSqlDump .= $objBackup->getTableContent($strTableName);

        $objTemplate->setVariable(array(
            'SHOW_TABLE_HTML_HEADERS' => $strColumnNames,
            'SHOW_TABLE_HTML_CONTENT' => $strTableContent,
            'SHOW_TABLE_SQL_DUMP' => str_replace(array('{', '}'), array('&#123;', '&#125;'), htmlentities($strSqlDump, ENT_QUOTES, CONTREXX_CHARSET))
        ));

    }

    /**
     * Optimizes some or all tables (depending on the POST-Array) used by Contrexx.
     *
     * @global     ADONewConnection
     * @global     array
     */
    function optimizeDatabase()
    {
        global $objDatabase, $_CORELANG;

        if (isset($_POST['selectedTablesName'])) {
            //User selected specific tables
            foreach ($_POST['selectedTablesName'] as $strTableName) {
                $objDatabase->Execute('OPTIMIZE TABLE '.$strTableName);
            }
        } else {
            //No tables selected, just optimize everything.
            $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');

            while(!$objResult->EOF) {
                $objDatabase->Execute('OPTIMIZE TABLE '.$objResult->fields['Name']);
                $objResult->MoveNext();
            }
        }

        self::addMessage($_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DONE']);
    }

    /**
     * Repairs some or all tables (depending on the POST-Array) used by Contrexx.
     *
     * @global     ADONewConnection
     * @global     array
     */
    function repairDatabase()
    {
        global $objDatabase, $_CORELANG;

        if (isset($_POST['selectedTablesName'])) {
            //User selected specific tables
            foreach ($_POST['selectedTablesName'] as $strTableName) {
                $objDatabase->Execute('REPAIR TABLE '.$strTableName);
            }
        } else {
            //No tables selected, just repair everything.
            $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');

            while(!$objResult->EOF) {
                $objDatabase->Execute('REPAIR TABLE '.$objResult->fields['Name']);
                $objResult->MoveNext();
            }
        }

        self::addMessage($_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_DONE']);
    }


    /**
     * Shows the SQL-Query page.
     *
     * @global     HTML_Template_Sigma
     * @global     array
     * @param         string        this query will be shown in the "executed"-part
     */
    function showQuery()
    {
        global  $objTemplate, $_CORELANG;

        $this->_strPageTitle = $_CORELANG['TXT_DBM_SQL_TITLE'];

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'sql', 'dbm_sql.html');
        $objTemplate->setVariable(array(
            'TXT_SQL_CODE_TITLE' => $_CORELANG['TXT_DBM_SQL_CODE'],
            'TXT_SQL_CODE_HINT' => $_CORELANG['TXT_DBM_SQL_HINT'],
            'TXT_SQL_FILE_TITLE' => $_CORELANG['TXT_DBM_SQL_FILE'],
            'TXT_SQL_FILE_FILE' => $_CORELANG['TXT_SELECT_FILE'],
            'TXT_SQL_FILE_ALLOWED_TYPES' => $_CORELANG['TXT_DBM_SQL_FILE_ALLOWED_TYPES'],
            'TXT_SQL_FILE_ALLOWED_SIZE' => $_CORELANG['TXT_DBM_SQL_FILE_ALLOWED_SIZE'],
            'TXT_SQL_SUBMIT' => $_CORELANG['TXT_EXECUTE']
        ));

        $objFWSystem = new FWSystem();

        $objTemplate->setVariable(array(
            'FILE_TYPES' => $this->_arrFileEndings['sql'],
            'FILE_SIZE' => $this->convertBytesToKBytes($objFWSystem->getMaxUploadFileSize()),
        ));

        if (isset($_POST['frmDatabaseQuery_Submited']) && count($arrSqlQueries = $this->parseInput())) {
            $output = array();
            foreach ($arrSqlQueries as $sqlQuery) {
                $output[] = $this->highlightSqlSyntax($sqlQuery).'<br />'.$this->executeQuery($sqlQuery);
            }

            $objTemplate->setVariable(array(
                'TXT_SQL_PERFOMED' => $_CORELANG['TXT_DBM_SQL_EXECUTED'],
                'PERFOMED_QUERY' => implode('<hr />', $output)
            ));
            $objTemplate->parse('performedQuery');
        } else {
            $objTemplate->hideBlock('performedQuery');
        }
    }

    /**
     * Highlights all SQL keywords for better viewability.
     *
     * @param       string      this query will be formatted
     * @return  string      highlighted sql query
     */
    function highlightSqlSyntax($strQuery)
    {
        $strQuery = htmlentities($strQuery, ENT_COMPAT, CONTREXX_CHARSET);
        $strSqlKeyword = '\1<b><font color="#990099">\2</font></b>\3';
        $strSqlType = '\1<font color="#ff9900">\2</font>\3';
        $strSqlString = '\1<font color="#008000">\2\3\2</font>';
        $strSqlNumber = '\1<font color="#008080">\2</font>\3';
        $arrRegEx = array(
            '/\n/s',
                            '/(\s)?(SHOW)()/i',
                            '/(\s)?(SELECT)()/i',
                            '/(\s)(FROM)()/i',
                            '/(\s)(ORDER)()/i',
                            '/(\s)(BY)()/i',
                            '/(\s)(ASC)()/i',
                            '/(\s)(DESC)()/i',
                            '/(\s)?(CREATE)()/i',
                            '/(\s)(TABLE)(\s)/i',
                            '/(\s)(TABLES)(\s|;)/i',
                            '/(\s)?(DROP)(\s)/i',
                            '/(\s)?(DELETE)(\s)/i',
                            '/(\s)?(INSERT)()/i',
                            '/(\s)(INTO)()/i',
                            '/(\s)(VALUES)(\s)/i',
                            '/(\s)?(UPDATE)()/i',
                            '/(\s)(SET)(\s)/i',
                            '/(\s)(WHERE)()/i',
                            '/(\s)(LIMIT)()/i',
                            '/(\s)(KEY)(\s)/i',
                            '/(\s)(UNIQUE)()/i',
                            '/(\s)(FULLTEXT)()/i',
                            '/(\s)(PRIMARY)()/i',
                            '/(\s)(NOT)(\s)/i',
                            '/(\s)(NULL)()/i',
                            '/(\s)(IF)(\s)/i',
                            '/(\s)(EXISTS)()/i',
                            '/(\s)(ENGINE)()/i',
                            '/(\s)(DEFAULT)(\s)/i',
                            '/(\s)(CHARSET)(=)/i',
                            '/(\s)(COLLATE)()/i',
                            '/(\s)(AUTO_INCREMENT)()/i',
                            '/(\s)(SMALLINT)(\()/i',
                            '/(\s)(TINYINT)(\()/i',
                            '/(\s)(INT)(\()/i',
                            '/(\s)(VARCHAR)(\()/i',
                            '/(\s)(ENUM)(\()/i',
                            '/(\s)(SET)(\()/i',
                            '/(\s)(MEDIUMTEXT)(\s)/i',
                            '/(\s)(TEXT)(\s)/i',
                            '/(\s)(DATE)(\s)/i',
                            '/(\s)(UNSIGNED)(\s)/i',
                            '/(\*)(\s)/i',
                            '/([ (,])?(`)(.*)`\s?/U',
                            '/([ (,])?(\')(.*)\'/U',
                            '/(\()(\d*)(\))/U',
                            '/([=\s])(\d*)([\s;])/U',
                        );
        $arrReplace = array(
            '<br />',
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlKeyword,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlType,
                                $strSqlString,
                                $strSqlString,
                                $strSqlNumber,
                                $strSqlNumber
                        );
        $strQuery = preg_replace($arrRegEx, $arrReplace, $strQuery);
        return $strQuery;
    }


    /**
     * Collects the information from the POST request by the query page
     * and returns the contained SQL query.
     * @global  array
     * @global  HTML_Template_Sigma
     * @return  string                            The query, if valid, or the
     *                                            empty string otherwise
     */
    function parseInput()
    {
        global $_CORELANG, $objTemplate;

        $input = '';
        $extension = array();
        if (isset($_FILES['frmDatabaseQuery_File']) && $_FILES['frmDatabaseQuery_File']['error'] == 0) {
            //Check for right file-type
            if (!preg_match('/(\.[^.]+)$/', $_FILES['frmDatabaseQuery_File']['name'], $extension) || !in_array($extension[1], $this->_arrFileEndings)) {
                self::addError(sprintf($_CORELANG['TXT_DBM_SQL_ERROR_TYPE'], $_FILES['frmDatabaseQuery_File']['type']));
                return '';
            }

            $input = @file_get_contents($_FILES['frmDatabaseQuery_File']['tmp_name']);
            if (!$input) {
                self::addError($_CORELANG['TXT_ERRORS_WHILE_READING_THE_FILE']);
                return '';
            }
        } else {
            //Check for empty queries
            if (empty($_POST['frmDatabaseQuery_Code'])) {
                self::addError($_CORELANG['TXT_DBM_SQL_ERROR_EMPTY']);
                return '';
            } elseif (!strpos($_POST['frmDatabaseQuery_Code'], ';')) {
                self::addError($_CORELANG['TXT_DBM_SQL_ERROR_EMPTY']);
            }
            $input = get_magic_quotes_gpc() ? stripslashes($_POST['frmDatabaseQuery_Code']) : $_POST['frmDatabaseQuery_Code'];
            $objTemplate->setVariable('PARSED_QUERY', htmlentities($input, ENT_QUOTES, CONTREXX_CHARSET));
        }
        return $this->extractSQLQueries($input);
    }


    /**
    * split sql string
    *
    * split the sql string in sql queries
    *
    * @access private
    * @param string $input
    */
    function extractSQLQueries($input)
    {
        $input = trim($input);
        $queryStartPos = 0;
        $stringDelimiter = '';
        $isString = false;
        $isComment = false;
        $query = '';
        $arrSqlQueries = array();

        for ($charNr = 0; $charNr < strlen($input); $charNr++) {
            if ($isComment) { // check if the loop is in a comment
                if ($input[$charNr] == "\r" || $input[$charNr] == "\n") {
                    $isComment = false;
                    $queryStartPos = $charNr+1;
                }
            } elseif ($isString) { // check if the loop is in a string
                if ($input[$charNr] == $stringDelimiter && ($input[$charNr-1] != "\\" || $input[$charNr-2] == "\\")) {
                    $isString = false;
                }
            } elseif ($input[$charNr] == "#" || (!empty($input[$charNr+1]) && $input[$charNr].$input[$charNr+1] == "--")) {
                $isComment = true;

            } elseif ($input[$charNr] == '"' || $input[$charNr] == "'" || $input[$charNr] == "`") { // check if this is a string delimiter
                $isString = true;
                $stringDelimiter = $input[$charNr];
            } elseif ($input[$charNr] == ";") { // end of query reached
                $charNr++;
                $query = ltrim(substr($input, $queryStartPos, $charNr-$queryStartPos));
                array_push($arrSqlQueries, $query);
                $queryStartPos = $charNr;
            }
        }
        return $arrSqlQueries;
    }


    /**
     * Executes all queries contained in the paramater.
     *
     * @global     ADONewConnection
     * @param        string        $strQuery: String containing the queries to execute.
     * @return     string        Executed Query
     */
    function executeQuery($strQuery) {
        global $objDatabase;

        $strExecutedQuery = '';
        $objResult = $objDatabase->Execute($strQuery);

        //Check for wrong query
        if ($objResult && $objResult->RecordCount() > 0) {
            $strExecutedQuery .= '<table cellspacing="0" cellpadding="3" style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;">';
            $strExecutedQuery .= '<tr><td style="border-right: 1px solid #000000;"><b>'.implode('</b></td><td style="border-right: 1px solid #000000;"><b>', array_keys($objResult->fields)).'</b></td></tr>';

            while(!$objResult->EOF) {
                $strExecutedQuery .= '<tr><td style="border-top: 1px solid #000000; border-right: 1px solid #000000;">'.implode('</td><td style="border-top: 1px solid #000000; border-right: 1px solid #000000;">', array_map(array($this, '_prepareOutput'), $objResult->fields)).'</td></tr>';
                $objResult->MoveNext();
            }

             $strExecutedQuery .= "</table>\n";
        } else {
            $strExecutedQuery = $objDatabase->ErrorMsg();
        }

        return $strExecutedQuery;
    }

    /**
     * Prepare data from SQL response to display it
     *
     * Makes htmlentities, sets BR-tags for new lines and sets unicode-tags for braces in the given string.
     *
     * @param string $string
     * @return string
     */
    function _prepareOutput($string) {
        return '<pre style="margin:5px; line-height:60%;">'.str_replace(array('{', '}'), array('&#123;', '&#125;'), nl2br(htmlentities($string, ENT_QUOTES, CONTREXX_CHARSET))).'</pre>';
    }

    /**
     * Converts an number of Bytes into Kilo Bytes.
     *
     * @return      float       converted size
     */
    private function convertBytesToKBytes($intNumberOfBytes) {
        $intNumberOfBytes = intval($intNumberOfBytes);
        return round($intNumberOfBytes / 1024, 2);
    }


    /**
     * Show the CSV import/export view
     * @return      boolean       True on success, false otherwise
     * @global      ADOConnection       $objDatabase
     * @global      HTML_Template_Sigma $objTemplate
     * @global      array               $_CORELANG
     * @author      Reto Kohli <reto.kohli@comvation.com>
     * @since       2.1.0
     * @internal    Make the import/export path configurable
     */
    function showCsv()
    {
        global $objTemplate, $objDatabase, $_CORELANG;

        $arrSuccess = array();
        $arrFail = array();
        if (   isset($_POST['multiaction'])
            && $_POST['multiaction'] == 'import') {
            if (   empty($_POST['source'])
                || !is_array($_POST['source'])) {
                self::addError($_CORELANG['TXT_DBM_ERROR_NO_SOURCE_FILES']);
            } else {
                $flagTruncate = !empty($_POST['truncate']);
                foreach ($_POST['source'] as $strTablename) {
                    $result = CSVBackup::import_csv($strTablename, $flagTruncate);
                    if ($result) {
                        $arrSuccess[] = $strTablename;
                    } else {
                        $arrFail[] = $strTablename;
                    }
                }
                if ($arrSuccess)
                    self::addMessage(sprintf(
                        $_CORELANG['TXT_DBM_SUCCEEDED_IMPORTING_CSV_FILES'],
                        join(', ', $arrSuccess)
                    ));
                    self::addMessage(sprintf(
                        $_CORELANG['TXT_DBM_CSV_FOLDER'], CSVBackup::getPath()
                    ));
                if ($arrFail)
                    self::addError(sprintf(
                        $_CORELANG['TXT_DBM_FAILED_IMPORTING_CSV_FILES'],
                        join(', ', $arrFail)
                    ));
            }
        }
        if (   isset($_POST['multiaction'])
            && $_POST['multiaction'] == 'export') {
/*
            // Accept a custom destination folder
            if (empty($_POST['target'])) {
                self::addError($_CORELANG['TXT_DBM_ERROR_NO_TARGET_FOLDER']);
*/
            if (   empty($_POST['source'])
                || !is_array($_POST['source'])) {
                self::addError($_CORELANG['TXT_DBM_ERROR_NO_SOURCE_TABLES']);
            } else {
                foreach ($_POST['source'] as $strTablename) {
                    $result = CSVBackup::export_csv($strTablename);
                    if ($result) {
                        $arrSuccess[] = $strTablename;
                    } else {
                        $arrFail[] = $strTablename;
                    }
                }
                if ($arrSuccess)
                    self::addMessage(sprintf(
                        $_CORELANG['TXT_DBM_SUCCEEDED_EXPORTING_TABLES'],
                        join(', ', $arrSuccess)
                    ));
                    self::addMessage(sprintf(
                        $_CORELANG['TXT_DBM_CSV_FOLDER'], CSVBackup::getPath()
                    ));
                if ($arrFail)
                    self::addMessage(sprintf(
                        $_CORELANG['TXT_DBM_FAILED_EXPORTING_TABLES'],
                        join(', ', $arrFail)
                    ));
            }
        }

        // Set up the view
        $this->_strPageTitle = $_CORELANG['TXT_DBM_CSV'];
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'csv', 'dbm_csv.html');
        $objTemplate->setGlobalVariable(array(
            'TXT_DBM_CSV_TITLE_TABLES'            => $_CORELANG['TXT_DBM_MAINTENANCE_TABLES'],
            'TXT_DBM_CSV_TABLES_NAME'             => $_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'],
            'TXT_DBM_CSV_TABLES_ROWS'             => $_CORELANG['TXT_DBM_MAINTENANCE_ROWS'],
            'TXT_DBM_CSV_TABLES_DATA'             => $_CORELANG['TXT_DBM_MAINTENANCE_DATA_SIZE'],
            'TXT_DBM_CSV_TABLES_INDEXES'          => $_CORELANG['TXT_DBM_MAINTENANCE_INDEX_SIZE'],
            'TXT_DBM_CSV_TABLES_BACKLOG'          => $_CORELANG['TXT_DBM_STATUS_BACKOG'],
            'TXT_DBM_CSV_TABLES_SELECT_ALL'       => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DBM_CSV_TABLES_DESELECT_ALL'     => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_DBM_CSV_TABLES_SUBMIT_SELECT'    => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_DBM_CSV_EXPORT'                  => $_CORELANG['TXT_DBM_CSV_EXPORT'],
            'TXT_DBM_CSV_IMPORT'                  => $_CORELANG['TXT_DBM_CSV_IMPORT'],
            'TXT_DBM_CSV_SHOW_TABLE'              => $_CORELANG['TXT_DBM_SHOW_TABLE_TITLE'],
            'TXT_DBM_CSV_IMPORT_TRUNCATE_TABLE'   => $_CORELANG['TXT_DBM_CSV_IMPORT_TRUNCATE_TABLE'],
            'TXT_DBM_CSV' => $_CORELANG['TXT_DBM_CSV'],
        ));
        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');
        $i = 0;
        while (!$objResult->EOF) {
            $isInnoDbEngine = $objResult->fields['Engine'] == 'InnoDB';
            $objTemplate->setVariable(array(
                'DBM_CSV_TABLES_NAME' => $objResult->fields['Name'],
                'DBM_CSV_TABLES_ROW' =>
                    (!$isInnoDbEngine && $objResult->fields['Data_free']
                      ? 'Warn' : (++$i % 2 ? 2 : 1)
                    ),
                'DBM_CSV_TABLES_ROWS' => $objResult->fields['Rows'],
                'DBM_CSV_TABLES_DATA' => sprintf(
                    $_CORELANG['TXT_CORE_KILOBYTE_ABBREV'],
                    $this->convertBytesToKBytes($objResult->fields['Data_length'])
                ),
                'DBM_CSV_TABLES_INDEXES' => sprintf(
                    $_CORELANG['TXT_CORE_KILOBYTE_ABBREV'],
                    $this->convertBytesToKBytes($objResult->fields['Index_length'])
                ),
                'DBM_CSV_TABLES_BACKLOG' => sprintf(
                    $_CORELANG['TXT_CORE_KILOBYTE_ABBREV'],
                    $this->convertBytesToKBytes($objResult->fields['Data_free'])
                ),
            ));
            if (Permission::hasAllAccess()) {
                $objTemplate->touchblock('showTableContentLink');
                $objTemplate->hideBlock('showTableContentNoLink');
            } else {
                $objTemplate->touchblock('showTableContentNoLink');
                $objTemplate->hideBlock('showTableContentLink');
            }
            $objTemplate->parse('showTables');
            $objResult->MoveNext();
        }
        return true;
    }


    /**
     * Adds the string $strErrorMessage to the error messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * error messages.
     * @static
     * @param   string  $strErrorMessage    The error message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addError($strErrMessage)
    {
        self::$strErrMessage .=
            (self::$strErrMessage != '' && $strErrMessage != ''
                ? '<br />' : ''
            ).$strErrMessage;
    }


    /**
     * Adds the string $strOkMessage to the success messages.
     *
     * If necessary, inserts a line break tag (<br />) between
     * messages.
     * @static
     * @param   string  $strOkMessage       The message to add
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    static function addMessage($strOkMessage)
    {
        self::$strOkMessage .=
            (self::$strOkMessage != '' && $strOkMessage != ''
                ? '<br />' : ''
            ).$strOkMessage;
    }

}


/**
 * BackupBase
 *
 * This abstract class is the base class for all Backup-Types (SQL, CSV). It delivers standard functionality like "new line" and
 * "tabulators" for its subclasses.
 *
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author        Thomas Kaelin <thomas.kaelin@astalvista.ch>
 * @access        public
 * @package      contrexx
 * @subpackage   core
 * @version        1.0
 */
abstract class BackupBase
{
    /**
     * Return an table header for an table.
     *
     * @param       string      $strTable: Name of the table which should be returned
     * @return      string      The generated table-header.
     */
    function getTableHeader($strTable) {
        return
            ($this->hasCommentTags()
                ? $this->getCommentString().
                  " Table:\t$strTable\n"
                : ''
            );
    }


    /**
     * Prints a separation line to use in sql-files.
     *
     * @return  string      separation Line
     */
    function getSeparationLine()
    {
        return
            ($this->hasCommentTags()
                ? $this->getCommentString().
                  "--------------------------------------------------------------\n"
                : ''
            );
    }


    /**
     * Defines if the current language supports comments. Some languages (for Example CVS) don't have a comment-tag, so
     * in this languages some information should not be printed.
     *
     * @return  boolean     true = Actual language supports comments
     */
    abstract function hasCommentTags();


    /**
     * Returns the comment-delimiter for the backup-type.
     *
     * @return  string      The comment-delimiter.
     */
    abstract function getCommentString();


    /**
     * Returns the Definition of a table in a string.
     *
     * @param       string      $strTable: Name of the table which should be returned
     * @return  string      The generated table-definition
     */
    abstract function getTableDefinition($strTable);


    /**
     * Returns the Content of a table in a string.
     *
     * @param       string      $strTable: Name of the table which should be returned
     * @return  string      The generated table-content
     */
    abstract function getTableContent($strTable);

}


/**
 * SQLBackup
 *
 * This class extends the BackupBase and implements the functionality for the SQL-Export.
 *
 * @copyright    CONTREXX CMS - COMVATION AG
 * @author        Thomas Kaelin <thomas.kaelin@astalvista.ch>
 * @access        public
 * @package      contrexx
 * @subpackage   core
 * @version        1.0
 */
final class SQLBackup extends BackupBase
{
    /**
     * Defines if the current language supports comments. Some languages (for Example CVS) don't have a comment-tag, so
     * in this languages some information should not be printed.
     *
     * @return  boolean     true = Actual language supports comments
     */
    function hasCommentTags()
    {
        return true;
    }

    /**
     * Returns a '#'-Char, which is the comment delimiter for SQL.
     *
     * @return  string      The comment-delimiter '#'
     */
    function getCommentString()
    {
        return '#';
    }

    /**
     * Prints the mySQL-definition of a table.
     *
     * @global       ADONewConnection
     * @param        string        $strTable: This table-header will be printed.
     * @return         string        The generated table-header.
     */
    function getTableDefinition($strTable)
    {
        global $objDatabase;

        $strReturn =
            'DROP TABLE IF EXISTS `'.$strTable."`;\n\n".
            'CREATE TABLE `'.$strTable."` (\n";

        $objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
        while (!$objResult->EOF) {
            $strReturn .=
                ' `'.$objResult->fields['Field'].'` '.
                $objResult->fields['Type'].' '.
                ($objResult->fields['Null'] == 'NO' ? 'NOT ' : '').'NULL'.
                (isset($objResult->fields['Default'])
                    ? $this->printTableDefaultTag($objResult->fields['Type'], $objResult->fields['Default'])
                    : ''
                ).
                ($objResult->fields['Extra'] == 'auto_increment' ? ' auto_increment' : '').
                ",\n";
            $objResult->MoveNext();
        }

        //Get table keys and indices
        $arrTableKeys = array();
        $objResult = $objDatabase->Execute('SHOW KEYS FROM '.$strTable);
        while (!$objResult->EOF) {
            $strKeyName  = $objResult->fields['Key_name'];
            $intSeqIndex = $objResult->fields['Seq_in_index'];
            $intSubPart  = intval($objResult->fields['Sub_part']);

            $arrUniqueKeys[$strKeyName] = false;
            if ($objResult->fields['Non_unique'] == 0) {
                $arrUniqueKeys[$strKeyName] = true;
            }

            $arrFulltextKeys[$strKeyName] = false;
            if ($objResult->fields['Comment'] == 'FULLTEXT' || $objResult->fields['Index_type'] == 'FULLTEXT') {
                $arrFulltextKeys[$strKeyName] = true;
            }

            $arrTableKeys[$strKeyName][$intSeqIndex] =
                '`'.$objResult->fields['Column_name'].'`'.
                ($intSubPart ? "($intSubPart)" : '');
            ksort($arrTableKeys[$strKeyName]);
            $objResult->MoveNext();
        }

        //Write table keys and indices
        foreach ($arrTableKeys as $strKeyName => $arrKeyFieldNames) {
            if ($strKeyName == 'PRIMARY') {
                $strReturn .= '  PRIMARY ';
            } else {
                $strReturn .=
                    ($arrFulltextKeys[$strKeyName] ? '  FULLTEXT ' : '').
                    ($arrUniqueKeys[$strKeyName] ? '  UNIQUE ' : '').
                    (!$arrFulltextKeys[$strKeyName] && !$arrUniqueKeys[$strKeyName]
                        ? '  ' : ''
                    );
            }
            $strReturn .=
                'KEY'.(($strKeyName == 'PRIMARY') ? '' : ' `'.$strKeyName.'`').
                ' ('.implode(',', $arrKeyFieldNames)."),\n";
        }

        //Close definition
        $strReturn = substr($strReturn,0,-2); //Cut last 2 charactes (,\n)
        $strReturn .= "\n) ";

        //Additional information
        $objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.$strTable.'"');
        $strReturn .= 'ENGINE='.$objResult->fields['Engine'].' ';
        $strReturn .= 'DEFAULT CHARSET='.substr($objResult->fields['Collation'],0,strpos($objResult->fields['Collation'],'_')).' ';
        $strReturn .= 'COLLATE='.$objResult->fields['Collation'];

        if (intval($objResult->fields['Auto_increment']) > 0) {
            $strReturn .= ' AUTO_INCREMENT='.intval($objResult->fields['Auto_increment']);
        }
        $strReturn .= ";\n\n";
        return $strReturn;
    }


    /**
     * Prints the mySQL-contents of a table.
     *
     * @global       ADONewConnection
     * @param        string        $strTable: This table-contents will be printed.
     * @return         string        The generated table-content.
     */
    function getTableContent($strTable)
    {
        global $objDatabase;

        $strReturn = '';

        //Count lines first
        $objTableContent = $objDatabase->Execute('SELECT * FROM '.$strTable);

        if ($objTableContent->RecordCount() > 0) {
            //Start INSERT line
            $strReturn .= 'INSERT INTO `'.$strTable.'` (';

            //Get column names
            $arrColumnNames = array();
            $objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
            while (!$objResult->EOF) {
                $arrColumnNames[count($arrColumnNames)] = $objResult->fields['Field'];
                $strReturn .= '`'.$objResult->fields['Field'].'`, ';
                $objResult->MoveNext();
            }
            $strReturn = substr($strReturn,0,-2); //Cut last 2 charactres (, )
            $strReturn .= ") VALUES \n";

            //Add values
            while (!$objTableContent->EOF) {
                $strReturn .= '(';
                foreach($arrColumnNames as $strColumnName) {
                    if (isset($objTableContent->fields[$strColumnName])) {
                        $strReturn .= '\''.addslashes($objTableContent->fields[$strColumnName]).'\', ';
                    } else {
                        $strReturn .= 'NULL, ';
                    }
                }
                $strReturn = substr($strReturn,0,-2); //Cut last 2 characters (, )
                $strReturn .= "),\n";
                $objTableContent->MoveNext();
            }
            $strReturn = substr($strReturn,0,-2); //Cut last 2 characters (,\n)

            //Write closing tag
            $strReturn .= ';';
        }
        return $strReturn;
    }


    /**
     * Returns the "default" value string for a table definition.
     *
     * Note that this method assumes the $strDefault argument to be non-empty!
     * @param   string      $strType      Data type of the column
     * @param   string      $strDefault   Default value of the column
     * @return  strint                    The default value string
     * @version 2.0.2
     * @author  Reto Kohli <reto.kohli@comvation.com> (version 2.0.2)
     */
    private function printTableDefaultTag($strType, $strDefault)
    {
        switch ($strType) {
            case 'tinytext':
            case 'tinyblob':
            case 'text':
            case 'blob':
            case 'mediumtext':
            case 'mediumblob':
            case 'longtext':
            case 'longblob':
                return ''; //No default values
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return " default '".intval($strDefault)."'";
            case 'time':
            case 'date':
            case 'datetime':
            case 'year':
                return " default '$strDefault'";
            case 'timestamp':
                // Note that there are *NO* quotes!
                if ($strDefault == 'CURRENT_TIMESTAMP') return " default ".$strDefault;
                // For any other default value
                return " default '$strDefault'";
                // Note that "ON UPDATE CURRENT_TIMESTAMP" is not handled here!
            case 'enum':
            case 'set':
                if ($strDefault != '') return " default '".$strDefault."'";
                // Pick the first possible value if an empty default is set
                // (although it may in fact be the empty string,
                // we wanna make sure it is valid)
                $arrMatch = array();
                $size = '';
                if (preg_match('/^\w+\(?([^)]*)\)?.*$/', $strType, $arrMatch)) {
                    $size = $arrMatch[1];
                }
                $arrValues = preg_split('/\s*,\s*/', $size, 1, PREG_SPLIT_NO_EMPTY);
                return " default '".$arrValues[0]."'";
            default:
                return " default '$strDefault'";
        }
    }
}


/**
 * CSVBackup
 *
 * This class extends the BackupBase and implements functionality for CSV
 * import and export.
 * There are some flaws in the CSV format that are not easy to
 * circumvent.  Please note that there is no trivial way to represent
 * the NULL value; it is exported as an empty string.
 * Thus, when reimporting tables from CSV files, this will result in a
 * value of 0 (zero) for integer, and the empty string for any kind of
 * character type fields.  As a consequence, there *MUST* be no difference
 * when handling empty (zero or '') and NULL results obtained from the
 * database throughout the system!
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @access      public
 * @package     contrexx
 * @subpackage  core
 * @since       2.1.0
 * @version     2.1.0
 */
final class CSVBackup extends BackupBase
{
    const default_delimiter = ';';
    const default_quote = '"';
    // Folder with trailing slash
    const default_path = '';
    // OBSOLETE:  const csv_escape = '\\';

    private static $delimiter = '';
    private static $quote = '';
    // Folder with trailing slash
    private static $path = '';

    function init($path='', $delimiter='', $quote='')
    {
        self::$path =
            (empty($path) ? ASCMS_BACKUP_PATH.'/'.self::default_path : $path);
        self::$delimiter =
            (empty($delimiter) ? self::default_delimiter : $delimiter);
        self::$quote =
            (empty($quote) ? self::default_quote : $quote);
    }


    static function getPath()
    {
          return self::$path;
    }


    /**
     * CSV does not support comments.
     * @return  boolean     False.  Always.
     */
    function hasCommentTags()
    {
        return false;
    }


    /**
     * CSV does not support comments.
     * @throws  Exception
     */
    function getCommentString()
    {
        die('getCommentString():  Not implemented!');
        //throw new Exception('Error: '.__CLASS__.'::'.__FUNCTION__.'() is not supported', 0);
    }


    /**
     * Returns the table definition as a string
     */
    function getTableDefinition($strTable)
    {
        global $objDatabase;

        $strReturn = '';
        // Write column names
        $objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
        while (!$objResult->EOF) {
            $strReturn .=
                ($strReturn ? ';' : '').
                '"'.$objResult->fields['Field'].'"';
            $objResult->MoveNext();
        }
        return $strReturn."\n";
        //return "TRUNCATE TABLE `$strTable`\n";
    }


    /**
     * Returns the table contents as a string
     */
    function getTableContent($strTable)
    {
        global $objDatabase;

        // Get the column names
        $arrColumnNames = array();
        $objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
        if (!$objResult || $objResult->EOF) return false;
        while (!$objResult->EOF) {
            $arrColumnNames[] = $objResult->fields['Field'];
            $objResult->MoveNext();
        }

//        // Get the count of all rows
//        $objResult = $objDatabase->Execute('SELECT COUNT(*) as numof_rows FROM '.$strTable);
//        if (!$objResult || $objResult->EOF) return false;
//        $numof_rows = $objResult->fields['numof_rows'];

        // Get the contents and add them to the string
        $strReturn = '';
        $objResult = $objDatabase->Execute('SELECT * FROM '.$strTable);
        if (!$objResult || $objResult->EOF) return false;
        while (!$objResult->EOF) {
            $arrRow = array();
            foreach($arrColumnNames as $strColumnName) {
                if (isset($objResult->fields[$strColumnName])) {
                    $strValue = $objResult->fields[$strColumnName];
                    $strValue = '"'.preg_replace('/"/', '""', $strValue).'"';
                    $arrRow[] = $strValue;
                } else {
                    $arrRow[] = 'NULL';
                }
            }
            $strReturn .= join(';', $arrRow)."\n";
            $objResult->MoveNext();
        }
        return $strReturn;
    }


    /**
     * Export the contents of the Shop tables to CSV
     *
     * Note that the table name *MUST* include the table prefix
     * (i.e. "contrexx_").
     * @param   string    $strTablename   Name of the table to be exported
     * @return  boolean                   True on success, false otherwise
     * @static
     * @global  ADOConnection $objDatabase
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function export_csv($strTablename)
    {
        global $_CORELANG;

        if (empty(self::$delimiter)) self::init();
        $strPath =
            self::$path.$strTablename.
            //'_'.date('YmdHis').
            '.csv';
        $fh = @fopen($strPath, 'w');
        if (!$fh) {
            DatabaseManager::addError(sprintf(
                $_CORELANG['TXT_DBM_ERROR_OPENING_FILE_FOR_WRITING'],
                $strPath
            ));
            return false;
        }
        $strTableDefinition = self::getTableDefinition($strTablename);
//echo("Def: $strTableDefinition<br />");
        fwrite($fh, $strTableDefinition);
        $strTableContent = self::getTableContent($strTablename);
//echo("Con: $strTableContent<br />");
        fwrite($fh, $strTableContent);
        fclose($fh);
        return true;
    }


    /**
     * Import the contents of the CSV file into the table with the same name
     *
     * Note that the table prefix, i.e. "contrexx_", *MUST* be included
     * in the table name.
     * @param   string    $strTablename   The source table name
     * @param   boolean   $flagTruncate   If true, truncates the destination
     *                                    table before importing
     * @return  boolean                   True on success, false otherwise
     * @static
     * @global  ADOConnection $objDatabase
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @since   2.1.0
     */
    static function import_csv($strTablename, $flagTruncate=false)
    {
        global $objDatabase, $_CORELANG;

        if (empty(self::$delimiter)) self::init();
        $arrTables = $objDatabase->MetaTables('TABLES');
        if (empty($arrTables)) {
            DatabaseManager::addError($_CORELANG['TXT_DBM_ERROR_GETTING_TABLES_INFO']);
            return false;
        }
        //$strTablename = preg_replace('/^(.+)_\d+\.csv$/', '$1', $strTablename);
        // Table exists?
        if (!in_array($strTablename, $arrTables)) {
            DatabaseManager::addError(sprintf(
                $_CORELANG['TXT_DBM_ERROR_TABLE_DOES_NOT_EXIST'],
                $strTablename
            ));
            return false;
        }
        $arrColumnsTable = $objDatabase->MetaColumns($strTablename);
        if (!$arrColumnsTable) {
            DatabaseManager::addError(sprintf(
                $_CORELANG['TXT_DBM_ERROR_GETTING_TABLE_INFO'],
                $strTablename
            ));
            return false;
        }
        foreach ($arrColumnsTable as $field) {
            $arrFieldnameTable[] = strtolower($field->name);
        }
        $strPath =
            self::$path.$strTablename.
            //'_'.date('YmdHis').
            '.csv';
        $fh = @fopen($strPath, 'r');
        if (!$fh) {
            DatabaseManager::addError(sprintf(
                $_CORELANG['TXT_DBM_ERROR_OPENING_FILE_FOR_READING'],
                $strPath
            ));
            return false;
        }
        $arrFieldnameCsv = fgetcsv($fh, null, self::$delimiter, self::$quote);
        // Verify that both the database and the CSV contain
        // the same number of fields with the same names
        $flagEqual = true;
        if (count($arrFieldnameTable) != count($arrFieldnameCsv)) {
            $flagEqual = false;
        }
        if ($flagEqual) {
            for ($i = 0; $i < count($arrFieldnameCsv); ++$i) {
                if ($arrFieldnameTable[$i] != $arrFieldnameCsv[$i]) {
                    $flagEqual = false;
                    break;
                }
            }
        }
        if (!$flagEqual) {
            DatabaseManager::addError(sprintf(
                $_CORELANG['TXT_DBM_ERROR_IMPORT_DIFFERING_TABLE'],
                $strTablename, $strPath
            ));
            return false;
        }
        // Truncate the table if so desired
        if ($flagTruncate) {
            $query = "TRUNCATE TABLE `$strTablename`";
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                DatabaseManager::addError(sprintf(
                    $_CORELANG['TXT_DBM_ERROR_TRUNCATING_TABLE'],
                    $strTablename
                ));
                return false;
            }
        }
        // Do the import
        // Join the database field names.  Part of the query below
        $strFieldnames = '`'.join('`, `', $arrFieldnameCsv).'`';
        // Values or EOF
        $arrValue = fgetcsv($fh, null, self::$delimiter, self::$quote);
        while ($arrValue) {
            foreach ($arrValue as &$value) {
                if ($value !== 'NULL')
                      $value = "'".mysql_escape_string($value)."'";
            }
            $query = "
                INSERT INTO `$strTablename` (
                    $strFieldnames
                ) VALUES (
                    ".join(', ', $arrValue)."
                )
            ";
//echo("Query: $query<br />");
            $objResult = $objDatabase->Execute($query);
            if (!$objResult) {
                DatabaseManager::addError(sprintf(
                    $_CORELANG['TXT_DBM_ERROR_IMPORTING_TABLE_FROM_CSV_FILE'],
                    $strTablename, $strPath
                ));
                return false;
            }
            $arrValue = fgetcsv($fh, null, self::$delimiter, self::$quote);
        }
        fclose($fh);
        return true;
    }

}

?>
