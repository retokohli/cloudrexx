<?php
/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';

/**
* Database Manager class
*
* CMS Database Manager
*
* @copyright	CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author		Thomas Kaelin <thomas.kaelin@astalvista.ch>
* @access		public
* @module		DatabaseManager
* @modulegroup	core
* @version		1.0
*/
class DatabaseManager {
	
   /**
    * @var string
    * @desc page title
    */
	var $_strPageTitle;
	
   /**
    * @var string
    * @desc status message (error message)
    */
	var $_strErrMessage = '';
	
	/**
	 * @var string
	 * @desc status message (okay message)
	 */
	var $_strOkMessage;
	
	/**
	 * @var string
	 * @desc path to backup-folder
	 */
	var $_strBackupPath;
	
	/**
	 * @var array
	 * @desc stores file endings for different export-types.
	 */
	var $_arrFileEndings;

	/**
	 * @var array
	 * @desc stores mime types for different export-types.
	 */
	var $_arrMimeTypes;
	
	/**
	 * Constructor Fix for Non-PHP5-Servers.
	 *
	 * @return DatabaseManager
	 */
	function DatabaseManager() {
		$this->__construct();	
	}
	
	/**
	 * Constructor
	 *
 	 * @global	object		template
 	 * @global 	array		core languageuage
	 */
	function __construct() {
		global $objTemplate, $_CORELANG;
		
		$this->_strBackupPath = ASCMS_BACKUP_PATH.'/';
		
		$this->_arrFileEndings = array(	'sql'	=> '.sql',
										'csv'	=> '.csv'
									);
									
		$this->_arrMimeTypes = array(	'sql'	=> 'application/x-unknown',
										'csv'	=> 'text/comma-separated-values'
									);
										
		$objTemplate->setVariable(	'CONTENT_NAVIGATION',
		                   			'
		                   				<a href="index.php?cmd=dbm">'.$_CORELANG['TXT_DBM_MAINTENANCE_TITLE'].'</a>
		                   				<a href="index.php?cmd=dbm&amp;act=sql">'.$_CORELANG['TXT_DBM_SQL_TITLE'].'</a>
		                   				<a href="index.php?cmd=dbm&amp;act=status">'.$_CORELANG['TXT_DBM_STATUS_TITLE'].'</a>
		                    		 	<a href="index.php?cmd=dbm&amp;act=ie">'.$_CORELANG['TXT_DBM_BACKUP_TITLE'].'</a>
		                    		');		
	}
	
	
	/**
	 * Dispatches to the desired function.
	 *
	 * @global	object		template
	 * @global 	object		permissions
	 * @global 	array		core language
	 */
	function getPage() {
		global  $objTemplate, $objPerm, $_CORELANG;
	
    	if(!isset($_GET['act'])){
    	    $_GET['act'] = '';	
    	}
    	
    	//Check general permissions to access this module
    	$objPerm->checkAccess(116, 'static');
    	
    	switch ($_GET['act']) {	
    		    		
    		case 'showTable':
    			$objPerm->checkAccess(121, 'static');
    			$this->showTable($_GET['table']);
    			break;
    			
    		case 'optimize':
    			$objPerm->checkAccess(118, 'static');
    			$this->optimizeDatabase();
    			$this->showMaintenance();
    			break;	
    			
    		case 'repair':
    			$objPerm->checkAccess(118, 'static');
    			$this->repairDatabase();
    			$this->showMaintenance();
    			break;	
    			
    		case 'status':
    			$objPerm->checkAccess(117, 'static');
    			$this->showStatus();
    			break;	    			
    			
    		case 'sql':
    			$objPerm->checkAccess(120, 'static');
    			$this->showQuery();
    			break;
    			
    		case 'doSql':
    			$objPerm->checkAccess(120, 'static');
    			$strExecutedQuery = $this->processQuery();
    			$this->showQuery($strExecutedQuery);
    			break;	
    			
    		case 'ie':
    			$objPerm->checkAccess(119, 'static');
    			$this->showImportExport();
    			break;	
    			
    		case 'createBackup':
    			$objPerm->checkAccess(119, 'static');
    			$this->createBackup();
    			$this->showImportExport();
    			break;	
    			
    		case 'deleteBackup':
    			$objPerm->checkAccess(119, 'static');
       			$this->deleteBackup($_GET['id']);
    			$this->showImportExport();
    			break;	
    			
    		case 'downloadBackup':
    			$objPerm->checkAccess(119, 'static');
    			$this->downloadBackup($_GET['id']);
    			break;	
    			
    		case 'restoreBackup':
    			$objPerm->checkAccess(119, 'static');
    			$this->restoreBackup($_GET['id']);
    			$this->showImportExport();
    			break;	
    			
    		case 'uploadBackup':
    			$objPerm->checkAccess(119, 'static');
    			$this->uploadBackup();
    			$this->showImportExport();
    			break;	
    			
    		case 'details':
    			$objPerm->checkAccess(119, 'static');
    			$this->showDetails($_GET['id']);
    			break;	
    			
    		default:
    			$objPerm->checkAccess(118, 'static');
    			$this->showMaintenance();
    				
    	}	 	
    	
    	$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_strPageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->_strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->_strErrMessage
		));   
	 }
	 
	 
	 /**
	  * Shows useful information about the database.
	  *
	  * @global		object		template object
	  * @global 	object		database object
	  * @global 	array		core language
	  */
	 function showStatus() {
	 	global  $objTemplate, $objDatabase, $_CORELANG, $_DBCONFIG;
	 	
	 	$this->_strPageTitle = $_CORELANG['TXT_DBM_STATUS_TITLE'];
	 	
	 	$objTemplate->addBlockfile('ADMIN_CONTENT', 'status', 'dbm_status.html');
	 	$objTemplate->setVariable(array(
	 		'TXT_STATUS_TITLE'					=>	$_CORELANG['TXT_DBM_STATUS_TITLE'],
	 		'TXT_STATUS_VERSION'				=>	$_CORELANG['TXT_DBM_STATUS_MYSQL_VERSION'],
	 		'TXT_STATUS_TABLES'					=>	$_CORELANG['TXT_DBM_STATUS_USED_TABLES'],
	 		'TXT_STATUS_SIZE'					=>	$_CORELANG['TXT_DBM_STATUS_USED_SPACE'],
	 		'TXT_STATUS_BACKLOG'				=>	$_CORELANG['TXT_DBM_STATUS_BACKOG'],
	 		'TXT_CONNECTION_TITLE'				=>	$_CORELANG['TXT_DBM_CONNECTION_TITLE'],
	 		'TXT_CONNECTION_DBPREFIX'			=>	$_CORELANG['TXT_DBM_CONNECTION_DBPREFIX'],
	 		'TXT_CONNECTION_DATABASE'			=>	$_CORELANG['TXT_DBM_CONNECTION_DATABASE'],
	 		'TXT_CONNECTION_USERNAME'			=>	$_CORELANG['TXT_DBM_CONNECTION_USERNAME'],
	 	));
	 	
	 	//Get Version
	 	$objResult = $objDatabase->Execute('SELECT VERSION()');
	 	$strVersion = $objResult->fields['VERSION()'];
	 	
	 	//Get Table-Status
	 	$objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');
	 	$intTables = $objResult->RecordCount();
	 	
	 	$intSize = 0;
	 	$intBacklog = 0;
	 	
	 	while (!$objResult->EOF) {
	 		$intSize 	+= ($objResult->fields['Data_length'] + $objResult->fields['Index_length']);
	 		$intBacklog += $objResult->fields['Data_free'];
	 		$objResult->MoveNext();
	 	}
	 	
	 	$objTemplate->setVariable(array(
	 		'STATUS_VERSION'		=>	$strVersion,
	 		'STATUS_TABLES'			=>	$intTables,
	 		'STATUS_SIZE'			=>	$this->convertBytesToKBytes($intSize),
	 		'STATUS_BACKLOG'		=>	$this->convertBytesToKBytes($intBacklog),
	 		'CONNECTION_DBPREFIX'	=>	DBPREFIX,
	 		'CONNECTION_DATABASE'	=>	$_DBCONFIG['database'],
	 		'CONNECTION_USERNAME'	=>	$_DBCONFIG['user'],
	 	));	 	
	 	
	 	//Filter mySQL-Info
		ob_start();
		phpinfo();
		$strPhpInfo = ob_get_contents();
		ob_end_clean();
	 	
		//Collect all blocks containing mysql-information
	 	$arrBlocks = array();
	 	preg_match_all('/<h2><a name="module_mysql.*">mysql.*<\/a><\/h2>(.*<\/table><br \/>){2}\n/sU', $strPhpInfo, $arrBlocks);	//Modifier s = Use string as single-row, Modifier U = just be Ungreedy!
	 	
	 	foreach ($arrBlocks[0] as $intBlockKey => $strBlock) {
	 		//Get title of the actual block
	 		$strTitle = preg_replace('/<h2>.*>(.*)<\/a><\/h2>.*/s', '$1', $strBlock);
	
	 		//Get tables of the actual block
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
			 		'TABLE_TITLES_CLASS'	=> ($intTableKey == 0) ? 'row1' : 'row3',
			 		'TABLE_TITLES'			=> $strColumnHeaders
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
		 		'TXT_PHPINFO_TITLE'	=> $_CORELANG['TXT_DBM_STATUS_PHPINFO'],
		 		'BLOCK_TITLE'		=> $strTitle
	 		));
	 		$objTemplate->parse('showPhpBlocks');
	 	}
	 }
	 
	 
	 /**
	  * Shows the database-maintenance page.
	  *
	  * @global		object		template object
	  * @global 	object		database object
	  * @global 	array		core language
	  */
	 function showMaintenance() {
	 	global  $objTemplate, $objDatabase, $_CORELANG;
	 	
	 	$this->_strPageTitle = $_CORELANG['TXT_DBM_MAINTENANCE_TITLE'];
	 	
	 	$objTemplate->addBlockfile('ADMIN_CONTENT', 'maintenance', 'dbm_maintenance.html');
	 	$objTemplate->setVariable(array(
	 		'TXT_MAINTENANCE_OPTIMIZE_TITLE'			=>	$_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DB'],
	 		'TXT_MAINTENANCE_OPTIMIZE_BUTTON'			=>	$_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_START'],
	 		'TXT_MAINTENANCE_OPTIMIZE_DESC'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DESC'],
	 		'TXT_MAINTENANCE_REPAIR_TITLE'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_DB'],
	 		'TXT_MAINTENANCE_REPAIR_BUTTON'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_START'],
	 		'TXT_MAINTENANCE_REPAIR_DESC'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_DESC'],
	 		'TXT_MAINTENANCE_TITLE_TABLES'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLES'],
	 		'TXT_MAINTENANCE_TABLES_NAME'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'],
	 		'TXT_MAINTENANCE_TABLES_ROWS'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_ROWS'],
	 		'TXT_MAINTENANCE_TABLES_DATA'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_DATA_SIZE'],
	 		'TXT_MAINTENANCE_TABLES_INDEXES'			=>	$_CORELANG['TXT_DBM_MAINTENANCE_INDEX_SIZE'],
	 		'TXT_MAINTENANCE_TABLES_BACKLOG' 			=>	$_CORELANG['TXT_DBM_STATUS_BACKOG'],
	 		'TXT_MAINTENANCE_TABLES_SELECT_ALL'			=>	$_CORELANG['TXT_SELECT_ALL'],
	 		'TXT_MAINTENANCE_TABLES_DESELECT_ALL'		=>	$_CORELANG['TXT_DESELECT_ALL'],
	 		'TXT_MAINTENANCE_TABLES_SUBMIT_SELECT'		=>	$_CORELANG['TXT_MULTISELECT_SELECT'],
	 		'TXT_MAINTENANCE_TABLES_SUBMIT_OPTIMIZE'	=>	$_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_START'],
	 		'TXT_MAINTENANCE_TABLES_SUBMIT_REPAIR'		=>	$_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_START'],
	 	));
	 	
	 	//Get tables
	 	$objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');
	 	$intRowCounter = 0;
	 	
	 	//Iterate through tables
	 	while (!$objResult->EOF) {	 		
	 		$objTemplate->setVariable(array(
	 			'TXT_MAINTENANCE_SHOW_TABLE'	=>	$_CORELANG['TXT_DBM_SHOW_TABLE_TITLE'],
	 		));
	 		
	 		
	 		$objTemplate->setVariable(array(
	 			'MAINTENANCE_TABLES_ROW'		=>	($objResult->fields['Data_free'] != 0) ? 'Warn' : (($intRowCounter % 2 == 0) ? 2 : 1),
	 			'MAINTENANCE_TABLES_NAME'		=>	$objResult->fields['Name'],
	 			'MAINTENANCE_TABLES_ROWS'		=>	$objResult->fields['Rows'],
	 			'MAINTENANCE_TABLES_DATA'		=>	$this->convertBytesToKBytes($objResult->fields['Data_length']),
	 			'MAINTENANCE_TABLES_INDEXES'	=>	$this->convertBytesToKBytes($objResult->fields['Index_length']),
	 			'MAINTENANCE_TABLES_BACKLOG'	=>	$this->convertBytesToKBytes($objResult->fields['Data_free']),
	 		));
	 		$objTemplate->parse('showTables');
	 		
	 		++$intRowCounter;
	 		$objResult->MoveNext();
	 	}
	 }
	 
	 
	 /**
	  * Shows content and sql-dump of a single table.
	  * 
	  * @global		object		template object
	  * @global 	object		database object
	  * @global 	array		core language
	  * @param 		string		$strTableName: This table will be shown.
	  */
	 function showTable($strTableName) {
	 	global $objTemplate, $objDatabase, $_CORELANG;
	 	
		$this->_strPageTitle = $_CORELANG['TXT_DBM_SHOW_TABLE_TITLE'];
	 	
	 	$objTemplate->addBlockfile('ADMIN_CONTENT', 'show_table', 'dbm_show_table.html');	 	
	 	$objTemplate->setVariable(array(
	 		'TXT_SHOW_TABLE_HTML_MENU'			=>	$_CORELANG['TXT_DBM_SHOW_TABLE_HTML_TITLE'],
	 		'TXT_SHOW_TABLE_HTML_TITLE'			=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'].':&nbsp;'.$strTableName,
	 		'TXT_SHOW_TABLE_DUMP_MENU'			=>	$_CORELANG['TXT_DBM_SHOW_TABLE_DUMP_TITLE'],
	 		'TXT_SHOW_TABLE_DUMP_TITLE'			=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLENAME'].':&nbsp;'.$strTableName,
	 		'TXT_SHOW_TABLE_DUMP_BUTTON_SELECT'	=>	$_CORELANG['TXT_SELECT_ALL'],
	 		'TXT_SHOW_TABLE_BUTTON_BACK'		=>	ucfirst($_CORELANG['TXT_BACK'])
	 	));
	 	
	 	//Check for contrexx-table
	 	if (DBPREFIX != substr($strTableName, 0, strpos($strTableName,'_') + 1)) {
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_SHOW_TABLE_WRONG_PREFIX'];
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
	 		
	 		foreach($arrColumnNames as $strColumnName => $strUnused) {
	 			$strColumnContent = htmlentities($objTableContent->fields[$strColumnName], ENT_QUOTES);
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
		$strSqlDump .= $objBackup->getEndLine(2);
		$strSqlDump .= $objBackup->getTableContent($strTableName);
	 
	 	$objTemplate->setVariable(array(
	 		'SHOW_TABLE_HTML_HEADERS'	=>	$strColumnNames,
	 		'SHOW_TABLE_HTML_CONTENT'	=>	$strTableContent,
	 		'SHOW_TABLE_SQL_DUMP'		=>	$strSqlDump
	 	));
	 	 	
	 }
	 
	 
	 /**
	  * Optimizes some or all tables (depending on the POST-Array) used by Contrexx.
	  * 
	  * @global 	object		database object
	  * @global 	array		core language
	  */
	 function optimizeDatabase() {
	 	global $objDatabase, $_CORELANG;
	 	
		if (isset($_POST['selectedTablesName'])) {
			//User selected specific tables			
			foreach ($_POST['selectedTablesName'] as $intKey => $strTableName) {
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
	 	
	 	$this->_strOkMessage = $_CORELANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DONE'];
	 }
	 
	 
	 /**
	  * Repairs some or all tables (depending on the POST-Array) used by Contrexx.
	  * 
	  * @global 	object		database object
	  * @global 	array		core language
	  */
	 function repairDatabase() {
	 	global $objDatabase, $_CORELANG;
	 	
		if (isset($_POST['selectedTablesName'])) {
			//User selected specific tables			
			foreach ($_POST['selectedTablesName'] as $intKey => $strTableName) {
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
	 	
	 	$this->_strOkMessage = $_CORELANG['TXT_DBM_MAINTENANCE_REPAIR_DONE'];
	 }
	 
	 
	 /**
	  * Shows the SQL-Query page.
	  * 
	  * @global		object		template object object
	  * @global 	array		core language
	  * @param 		string		this query will be shown in the "executed"-part
	  */
	 function showQuery($strQuery='') {
	 	global  $objTemplate, $_CORELANG;
	 	
	 	$this->_strPageTitle = $_CORELANG['TXT_DBM_SQL_TITLE'];
	 	
	 	$objTemplate->addBlockfile('ADMIN_CONTENT', 'sql', 'dbm_sql.html');
	 	$objTemplate->setVariable(array(
	 		'TXT_SQL_CODE_TITLE'			=>	$_CORELANG['TXT_DBM_SQL_CODE'],
	 		'TXT_SQL_CODE_HINT'				=>	$_CORELANG['TXT_DBM_SQL_HINT'],
	 		'TXT_SQL_FILE_TITLE'			=>	$_CORELANG['TXT_DBM_SQL_FILE'],
	 		'TXT_SQL_FILE_FILE'				=>	$_CORELANG['TXT_SELECT_FILE'],
	 		'TXT_SQL_FILE_ALLOWED_TYPES'	=>	$_CORELANG['TXT_DBM_SQL_FILE_ALLOWED_TYPES'],
	 		'TXT_SQL_FILE_ALLOWED_SIZE'		=>	$_CORELANG['TXT_DBM_SQL_FILE_ALLOWED_SIZE'],
	 		'TXT_SQL_SUBMIT'				=>	$_CORELANG['TXT_EXECUTE']
	 	));
	 	
		$objFWSystem = &new FWSystem(); 	
	 	
	 	$objTemplate->setVariable(array(
	 		'FILE_TYPES'	=>	$this->_arrFileEndings['sql'],
	 		'FILE_SIZE'		=>	$this->convertBytesToKBytes($objFWSystem->getMaxUploadFileSize()),
	 	));
	 	
	 	if (empty($strQuery)) {
	 		$objTemplate->hideBlock('performedQuery');
	 	} else {
	 		$objTemplate->setVariable('TXT_SQL_PERFOMED', $_CORELANG['TXT_DBM_SQL_EXECUTED']);
	 		$objTemplate->setVariable('PERFOMED_QUERY', $this->highlightSqlSyntax($strQuery));
	 		$objTemplate->parse('performedQuery');
	 	}
	 }
	 
	 
	 /**
	  * Highlights all SQL keywords for better viewability.
	  * 
	  * @param 		string		this query will be formatted
	  * @return 	string		highlighted sql query
	  */
	 function highlightSqlSyntax($strQuery) {
 		$strQuery = htmlentities($strQuery, ENT_COMPAT);
 		
 		$strSqlKeyword = '\1<b><font color="#990099">\2</font></b>\3';
 		$strSqlType = '\1<font color="#ff9900">\2</font>\3';
 		$strSqlString = '\1<font color="#008000">\2\3\2</font>';
 		$strSqlNumber = '\1<font color="#008080">\2</font>\3';
 			 		
 		$arrRegEx = array(	'/\n/s',
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
 		
 		$arrReplace = array(	'<br />',
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
	 	
 		//Convert results to <pre>
 		$strQuery = preg_replace('/(\[result\])(.*)(\[\/result\])/Ue',"html_entity_decode('\\2',ENT_QUOTES)",$strQuery);
 		
	 	return $strQuery;
	 }
	 
	 
	 /**
	  * Collects the POST-Information from the Query-Page and performs the contained SQL-Query.
	  * 
	  * @global 	array		core language
	  * @return 	string		executed query
	  */
	 function processQuery() {
	 	global $_CORELANG;
	 	
	 	if (isset($_FILES['frmDatabaseQuery_File']['type']) && !empty($_FILES['frmDatabaseQuery_File']['type'])) { 		
	 		//Check for right file-type
	 		if ($_FILES['frmDatabaseQuery_File']['type'] != $this->_arrMimeTypes['sql']) {
	 			$this->_strErrMessage = str_replace('{TYPE}', $_FILES['frmDatabaseQuery_File']['type'], $_CORELANG['TXT_DBM_SQL_ERROR_TYPE']);
	 			return;
	 		}
	 		
			ob_start();
			readfile($_FILES['frmDatabaseQuery_File']['tmp_name']);
			$strQuery = ob_get_contents();
			ob_end_clean();	
	 	} else {	 		
	 		//Check for empty queries
	 		if (empty($_POST['frmDatabaseQuery_Code'])) {
	 			$this->_strErrMessage = $_CORELANG['TXT_DBM_SQL_ERROR_EMPTY'];
	 			return;
	 		}
	 		
	 		$strQuery = str_replace("\r\n", "\n", $_POST['frmDatabaseQuery_Code']);
	 	}
	 	
	 	$strExecutedQuery = $this->executeQuery($strQuery);
	 	
	 	if (empty($strExecutedQuery)) {
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_SQL_ERROR_EMPTY'];
	 	}
	 	
	 	return $strExecutedQuery;
	 	
	 }
	 
	 /**
	  * Executes all queries contained in the paramater.
	  *
	  * @global 	object		database object object
	  * @param		string		$strQuery: String containing the queries to execute.
	  * @return 	string		Executed Query
	  */
	 function executeQuery($strQuery) {
	 	global $objDatabase;
	 	
	 	$strExecutedQuery = '';
	 	
	 	//$strRegex = '/(DROP|CREATE|SELECT|INSERT|UPDATE|DELETE|SHOW)\s.*;/sU';	//Modifier s = Use string as single-row, Modifier U = just be Ungreedy!	 	
	 	$strRegex = '/((DROP|CREATE|SELECT|UPDATE|DELETE|SHOW)\s.*;)|((INSERT INTO)\s.*\);\n)/siU';
	 	
	 	$arrQueries = array();
	 	preg_match_all($strRegex, $strQuery, $arrQueries);
	 		 		 	
	 	foreach ($arrQueries[0] as $intKey => $strCommand) {
	 		$strExecutedQuery .= $strCommand;
	 		$objResult = $objDatabase->Execute($strCommand);
	 		
	 		//Check for wrong query
	 		if ($objResult === false) {
	 			return '';
	 		}
	 		
	 		//Check for results of the query
	 		if ($objResult->RecordCount() > 0) {
	 			$strExecutedQuery .= '[result]'."\n\n";
	 			$strExecutedQuery .= '<table cellspacing="0" cellpadding="3" style="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;">';
	 			
	 			foreach($objResult->fields as $strColumn => $strKey) {
	 				$strExecutedQuery .= '<td style="border-right: 1px solid #000000;"><b>'.$strColumn.'</b></td>';
	 			}
	 			
	 			while(!$objResult->EOF) {
	 				$strExecutedQuery .= '<tr>';
	 				
	 				foreach ($objResult->fields as $strKey => $strValue) {
	 					$strExecutedQuery .= '<td style="border-top: 1px solid #000000; border-right: 1px solid #000000;">'.(($strValue == '') ? '&nbsp;' : $strValue).'</td>';
	 				}
					$objResult->MoveNext();
					
					$strExecutedQuery .= '</tr>';
	 			}
	 			
	 			$strExecutedQuery .= '</table>'."\n";
	 			$strExecutedQuery .= '[/result]';
	 		} else {
	 			$strExecutedQuery .= "\n\n";
	 		}
	 	}	 	
	 		 		 	
	 	return $strExecutedQuery;
	 }
	 
	 /**
	  * Shows the Import/Export page.
	  *
	  * @global		object		template object
	  * @global 	object		database object object
	  * @global 	array		core language
	  * @global 	array		system configuration
	  */
	 function showImportExport() {
	 	global  $objTemplate, $objDatabase, $_CORELANG, $_CONFIG;
	 	
	 	$this->_strPageTitle = $_CORELANG['TXT_DBM_BACKUP_TITLE'];
	 	
	 	$objTemplate->addBlockfile('ADMIN_CONTENT', 'import_export', 'dbm_import_export.html');
	 	$objTemplate->setVariable(array(
	 		'TXT_EXPORT_TITLE'				=>	$_CORELANG['TXT_DBM_EXPORT_TITLE'],
	 		'TXT_EXPORT_DESCRIPTION'		=>	$_CORELANG['TXT_DBM_EXPORT_DESCRIPTION'],
	 		'TXT_EXPORT_TYPE'				=>	$_CORELANG['TXT_DBM_EXPORT_TYPE'],
	 		'TXT_EXPORT_TABLES'				=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLES'],
	 		'TXT_EXPORT_SELECT_ALL'			=>	$_CORELANG['TXT_SELECT_ALL'],
	 		'TXT_EXPORT_UNSELECT_ALL'		=>	$_CORELANG['TXT_DESELECT_ALL'],
	 		'TXT_EXPORT_SUBMIT'				=>	$_CORELANG['TXT_DBM_EXPORT_TITLE'],
	 		'TXT_EXISTING_BACKUPS_TITLE'	=>	$_CORELANG['TXT_DBM_EXPORT_BACKUPS'],
	 		'TXT_EXISTING_BACKUP_DATE'		=>	$_CORELANG['TXT_DBM_EXPORT_DATE'],
	 		'TXT_EXISTING_BACKUP_TYPE'		=>	$_CORELANG['TXT_DBM_EXPORT_TYPE'],
	 		'TXT_EXISTING_BACKUP_VERSION'	=>	$_CORELANG['TXT_DBM_EXPORT_VERSION'],
	 		'TXT_EXISTING_BACKUP_EDITION'	=>	$_CORELANG['TXT_DBM_EXPORT_EDITION'],
	 		'TXT_EXISTING_BACKUP_DESC'		=>	$_CORELANG['TXT_DBM_EXPORT_DESCRIPTION'],
	 		'TXT_EXISTING_BACKUP_TABLES'	=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLES'],
	 		'TXT_EXISTING_BACKUP_SIZE'		=> 	$_CORELANG['TXT_DBM_EXPORT_SIZE'],
	 		'TXT_EXISTING_BACKUP_DELETE'	=>	$_CORELANG['TXT_CONFIRM_DELETE_DATA'].' '.$_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
	 		'TXT_EXISTING_BACKUP_COMMENTS'	=>	$_CORELANG['TXT_DBM_EXPORT_COMMENTS'],
	 		'TXT_IMPORT_TITLE'				=>	$_CORELANG['TXT_DBM_IMPORT'],
	 		'TXT_IMPORT_DESCRIPTION'		=>	$_CORELANG['TXT_DBM_IMPORT_DESCRIPTION'],
	 		'TXT_IMPORT_FILE'				=>	$_CORELANG['TXT_DBM_IMPORT_FILE'],
	 		'TXT_IMPORT_SUBMIT'				=>	$_CORELANG['TXT_DBM_IMPORT_SUBMIT']
	 	));
	 	
	 	//Show tables
	 	$objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.DBPREFIX.'%"');

	 	$strTables = ''; 	
	 	while(!$objResult->EOF) {
	 		if (stripos($objResult->fields['Name'],'backup') == 0) {
	 			//Don't backup the backup-table..
	 			$objTemplate->setVariable('TABLE_NAME', $objResult->fields['Name']);
	 			$objTemplate->parse('showTables');
	 		}
	 		$objResult->MoveNext();
	 	}	 	
	 	
	 	//Show existing Backups
	 	$objResult = $objDatabase->Execute('SELECT		id,
	 													date,
	 													version,
	 													edition,
	 													type,
	 													description,
	 													usedtables,
	 													size
	 										FROM		'.DBPREFIX.'backups
	 										ORDER BY	date DESC
	 									');

	 	while (!$objResult->EOF) {
	 		
	 		if ($objResult->fields['version'] == $_CONFIG['coreCmsVersion'].' '.$_CONFIG['coreCmsStatus'] && $objResult->fields['edition'] == $_CONFIG['coreCmsEdition']) {
	 			$strClassRow = 'highlightedGreen';
	 		} else {
	 			$strClassRow = 'rowWarn';
	 		}
	 		
	 		$objTemplate->setVariable(array(
	 			'TXT_BACKUP_RESTORE'	=> $_CORELANG['TXT_DBM_EXPORT_RESTORE'],
	 			'TXT_DETAILS'			=> $_CORELANG['TXT_DBM_DETAILS_TITLE'],
	 			'TXT_BACKUP_DOWNLOAD'	=> $_CORELANG['TXT_DOWNLOAD'],
	 			'TXT_BACKUP_DELETE'		=> $_CORELANG['TXT_DELETE']
		 	));	 		
		 
	 		$objTemplate->setVariable(array(
	 			'BACKUP_CLASS'			=> $strClassRow,
	 			'BACKUP_ID'				=> $objResult->fields['id'],
		 		'BACKUP_DATE'			=> date(ASCMS_DATE_FORMAT,$objResult->fields['date']),
		 		'BACKUP_DESC'			=> ($objResult->fields['description'] != '') ? $objResult->fields['description'] : '-',
		 		'BACKUP_TYPE'			=> strtoupper($objResult->fields['type']),
		 		'BACKUP_VERSION'		=> htmlentities($objResult->fields['version'], ENT_QUOTES),
		 		'BACKUP_EDITION'		=> htmlentities($objResult->fields['edition'], ENT_QUOTES),
		 		'BACKUP_TABLES'			=> count(explode(';',$objResult->fields['usedtables'])),
		 		'BACKUP_SIZE'			=> $this->convertBytesToKBytes($objResult->fields['size']),
		 	));
	 		
	 		$objTemplate->parse('showBackups');
	 		$objResult->MoveNext();
	 	}
	 }
	 
	 /**
	  * Creates a backup of all contrexx-tables.
	  * 
	  * @global 	object		database object
	  * @global 	array		core language
	  * @global 	array		system configuration
	  */
	 function createBackup() {
	 	global $objDatabase, $_CORELANG, $_CONFIG;
	 	
	 	$strDescription = addslashes(strip_tags($_POST['frmDatabaseExport_Desc']));
	 	$strBackupType = addslashes(strip_tags($_POST['frmDatabaseExport_Type']));
	 	
	 	//Select desired type of backup
	 	switch ($strBackupType) {
	 		case 'csv':
	 			$objBackup = new CSVBackup();
	 			break;
	 		default: //sql
	 			$objBackup = new SQLBackup();
	 	}
	 	
	 	if (! isset($_POST['frmDatabaseExport_Tables'])) {
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_EXPORT_ERROR_SELECTION'];
	 		return;
	 	}
	 	
	 	//New Backup-String
	 	$strBackup = '';
	 	
	 	//Create file header
	 	if ($objBackup->hasCommentTags()) {
	 		$strBackup .=  	$objBackup->getSeparationLine();
	 		$strBackup .=	$objBackup->getCommentString().' Info:'.$objBackup->getTabulator(2).'Contrexx CMS database backup file'.$objBackup->getEndLine();
	 		$strBackup .=	$objBackup->getCommentString().' Version:'.$objBackup->getTabulator().$_CONFIG['coreCmsVersion'].' '.$_CONFIG['coreCmsStatus'].$objBackup->getEndLine();
	 		$strBackup .=	$objBackup->getCommentString().' Edition:'.$objBackup->getTabulator().$_CONFIG['coreCmsEdition'].$objBackup->getEndLine();
	 		$strBackup .=	$objBackup->getCommentString().' Created:'.$objBackup->getTabulator().date(ASCMS_DATE_FORMAT, mktime()).$objBackup->getEndLine();
	 		$strBackup .=	$objBackup->getSeparationLine().$objBackup->getEndLine(2);
	 	}
	 	
	 	//Create table data
	 	$strTables = ''; 	
	 	foreach ($_POST['frmDatabaseExport_Tables'] as $intKey => $strTableName) { 			
 			$strTables .= $strTableName.';';
 			$strBackup .= $objBackup->getSeparationLine();
 			$strBackup .= $objBackup->getTableHeader($strTableName);
 			$strBackup .= $objBackup->getSeparationLine();
 			$strBackup .= $objBackup->getTableDefinition($strTableName);
			$strBackup .= $objBackup->getEndLine(2);
			$strBackup .= $objBackup->getTableContent($strTableName);
			$strBackup .= $objBackup->getEndLine(2);
	 	}
	 	
	 	$strTables = substr($strTables,0,-1);
	 		
	 	//Write file
	 	if (is_writable($this->_strBackupPath)) {	 		
	 		$intTimeStamp = time();
	 		$strFileName = $intTimeStamp.$this->_arrFileEndings[$strBackupType];
			$handleFile = fopen($this->_strBackupPath.$strFileName, 'w');
			fwrite ($handleFile, $strBackup);
			fclose ($handleFile);
			
			//Try to set access rights
			@chmod($this->_strBackupPath.$strFileName, 0644);
			
			//Add to database
			$objDatabase->Execute('	INSERT INTO	'.DBPREFIX.'backups
									SET	`date`			= '.$intTimeStamp.',
										`version`		= "'.$_CONFIG['coreCmsVersion'].' '.$_CONFIG['coreCmsStatus'].'",
										`edition`		= "'.$_CONFIG['coreCmsEdition'].'",
										`type`			= "'.$strBackupType.'",
										`description`	= "'.$strDescription.'",
										`usedtables`	= "'.$strTables.'",
										`size`			= '.filesize($this->_strBackupPath.$strFileName).'
								');
	
			$this->_strOkMessage = str_replace('{PATH}',$this->_strBackupPath.$strFileName, $_CORELANG['TXT_DBM_EXPORT_SUCCESS']);
	 	} else {
	 		//Directory is not writable, show error
	 		$this->_strErrMessage = str_replace('{PATH}',$this->_strBackupPath, $_CORELANG['TXT_DBM_EXPORT_ERROR']);
	 	}
	 }
	 
	 
	 /**
	  * Removes an existing backup from database and the filesystem.
	  *
 	  * @global 	object		database object
	  * @global 	array		core language
	  * @param 		integer		$intBackupId: The backup with this id will be removed.
	  */
	 function deleteBackup($intBackupId) {
	 	global $objDatabase, $_CORELANG;
	 	
	 	$intBackupId = intval($intBackupId);
	 	
	 	if ($intBackupId == 0) {
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_EXPORT_DELETE_ERROR'];
	 		return;
	 	}
	 	
	 	
	 	$objResult = $objDatabase->Execute('SELECT	`date`,
	 												`type`
	 										FROM	'.DBPREFIX.'backups
	 										WHERE	id='.$intBackupId.'
	 										LIMIT	1
	 									');
	 		
	 	if ($objResult->RecordCount() != 1) {
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_EXPORT_DELETE_ERROR'];
	 		return;
	 	}
	 	
	 	$strFile = $objResult->fields['date'].$this->_arrFileEndings[$objResult->fields['type']];
		@unlink($this->_strBackupPath.$strFile);
		
		$objDatabase->Execute('	DELETE
								FROM	'.DBPREFIX.'backups
								WHERE	id='.$intBackupId.'
								LIMIT	1
							');
	 			
	 	$this->_strOkMessage = $_CORELANG['TXT_DBM_EXPORT_DELETE_SUCCESS'];
	 }
	 
	 
	 /**
	  * Writes a backup to the output stream. Allows to download it.
	  *
 	  * @global 	object		database object
	  * @global 	integer		$intBackupId: The backup with this id should be downloaded
	  */
	 function downloadBackup($intBackupId) {
	 	global $objDatabase;
	 	
	 	//Check id
	 	$intBackupId = intval($intBackupId);
	 	if ($intBackupId <= 0) { return; }
	 	
	 	//Check database
	 	$objResult = $objDatabase->Execute('SELECT 	`date`,
	 												`type`,
	 												`size`
	 										FROM	'.DBPREFIX.'backups
	 										WHERE	id='.$intBackupId.'
	 										LIMIT	1
	 									');
	 	if ($objResult->RecordCount() != 1) { return; }
	 	
	 	//Check file
	 	$strFileName = $objResult->fields['date'].$this->_arrFileEndings[$objResult->fields['type']];
	 	if (! is_file($this->_strBackupPath.$strFileName)) { return; }
	 	
	 	//Write stream
	 	header('Content-type: '.$this->_arrMimeTypes[$objResult->fields['type']]);
	 	header('Content-Length: '.$objResult->fields['size']);
		header('Content-Disposition: attachment; filename="'.$strFileName.'"');
		readfile($this->_strBackupPath.$strFileName);
		exit();
	 }	 
	 
	/**
	 * Manages the Upload of a Backup-File and adds it to the database. Currently it only proccesses SQL-Uploads.
	 * 
 	 * @global 	object		database object
 	 * @global 	array		config
	 * @global 	array		core language	
	 */
	 function uploadBackup() {
	 	global $objDatabase, $_CONFIG, $_CORELANG;
	 	
	 	if ($_FILES['frmDatabaseImport_File']['type'] != $this->_arrMimeTypes['sql']) {
	 		$this->_strErrMessage = str_replace('{TYPE}', $_FILES['frmDatabaseImport_File']['type'], $_CORELANG['TXT_DBM_IMPORT_ERROR_TYPE']);
	 		return;
	 	}
	 		
	 	$intTimeStamp = time();
	 	$strFileName = $intTimeStamp.$this->_arrFileEndings['sql'];
	 	
	 	$arrFileRows = file($_FILES['frmDatabaseImport_File']['tmp_name']);
	 	
	 	//Check for Contrexx-File
	 	if (!preg_match('/# Version:.*/', $arrFileRows[2]) || !preg_match('/# Edition:.*/', $arrFileRows[3])) {
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_IMPORT_ERROR_NO_CONTREXX'];
	 		return;
	 	}

	 	//Collect version & edition information
	 	$strVersion = substr($arrFileRows[2], strrpos($arrFileRows[2],"\t") + 1, -1); //Find begin of version and remove the newline at the end of the string
	 	$strEdition = substr($arrFileRows[3], strrpos($arrFileRows[2],"\t") + 1, -1); //Find begin of edition and remove the newline at the end of the string
	 	
	 	//Collect used tables
	 	$strTables = '';
	 	foreach ($arrFileRows as $intRowIndex => $strRow) {
	 		if (preg_match('/CREATE TABLE `.*` \(\n/', $strRow)) {
	 			$strTables .= substr($strRow, strpos($strRow, '`') + 1, -4).';';
	 		}
	 	}
	 	$strTables = substr($strTables,0,-1);
	 	
	 	
	 	//Move uploaded file
	 	move_uploaded_file($_FILES['frmDatabaseImport_File']['tmp_name'], $this->_strBackupPath.$strFileName);
	 	chmod($this->_strBackupPath.$strFileName, 0644);
	 	
		//Add to database
		$objDatabase->Execute('	INSERT INTO	'.DBPREFIX.'backups
								SET	`date`			= '.$intTimeStamp.',
									`version`		= "'.$strVersion.'",
									`edition`		= "'.$strEdition.'",
									`type`			= "sql",
									`description`	= "",
									`usedtables`	= "'.$strTables.'",
									`size`			= '.filesize($this->_strBackupPath.$strFileName).'
							');
		
		$this->_strOkMessage = $_CORELANG['TXT_DBM_IMPORT_SUCCESS'];
	 }
	 
	 /**
	  * Shows details of an existing backup.
	  *
	  * @global		object		template object
	  * @global 	object		database object
	  * @global 	array		core language
	  * @global 	array		system configuration
	  */
	 function showDetails($intBackupId) {
	 	global  $objTemplate, $objDatabase, $_CORELANG, $_CONFIG; 	
	 	
	 	$this->_strPageTitle = $_CORELANG['TXT_DBM_BACKUP_TITLE'];
	 	
	 	$objTemplate->addBlockfile('ADMIN_CONTENT', 'status', 'dbm_details.html');
	 	$objTemplate->setVariable(array(
	 		'TXT_DETAILS_TITLE'			=>	$_CORELANG['TXT_DBM_EXPORT_TITLE'],
	 		'TXT_DETAILS_DATE'			=>	$_CORELANG['TXT_DBM_EXPORT_DATE'],
	 		'TXT_DETAILS_COMMENT'		=>	$_CORELANG['TXT_DBM_EXPORT_DESCRIPTION'],
	 		'TXT_DETAILS_TYPE'			=>	$_CORELANG['TXT_DBM_EXPORT_TYPE'],
	 		'TXT_DETAILS_VERSION'		=>	$_CORELANG['TXT_DBM_EXPORT_VERSION'],
	 		'TXT_DETAILS_SIZE'			=>	$_CORELANG['TXT_DBM_EXPORT_SIZE'],
	 		'TXT_DETAILS_TABLES'		=>	$_CORELANG['TXT_DBM_MAINTENANCE_TABLES'],
	 		'TXT_DETAILS_CONTENT'		=>	$_CORELANG['TXT_DBM_DETAILS_CONTENT'],
	 		'TXT_DETAILS_BUTTON_SELECT'	=>	$_CORELANG['TXT_SELECT_ALL'],
	 		'TXT_DETAILS_BUTTON_BACK'	=>	ucfirst($_CORELANG['TXT_BACK'])
	 	));
	 		 		 	
	 	$intBackupId = intval($intBackupId);
	 	$objResult = $objDatabase->Execute('SELECT	`date`,
	 												`version`,
	 												`edition`,
	 												`type`,
	 												`description`,
	 												`usedtables`,
	 												`size`
	 										FROM	'.DBPREFIX.'backups
	 										WHERE	id='.$intBackupId.'
	 										LIMIT	1
	 									');
	 		 	
	 	$strFile = $this->_strBackupPath.$objResult->fields['date'].$this->_arrFileEndings[$objResult->fields['type']];
	 	
	 	if ($intBackupId < 1 || $objResult->RecordCount() == 0 || !is_file($strFile)) {
	 		//Wrong ID, show error
	 		$this->_strErrMessage = $_CORELANG['TXT_DBM_DETAILS_ERROR_ID'];
	 	}	 	
	 	
	 	//Read file
	 	$strFileContent = '';
	 	$handleFile = fopen ($strFile, 'r');
		while (!feof($handleFile)) {
		    $strFileContent .= fgets($handleFile, 4096);
		}
		fclose ($handleFile); 
	 	
	 	$objTemplate->setVariable(array(
	 		'DETAILS_DATE'		=>	date(ASCMS_DATE_FORMAT,$objResult->fields['date']),
	 		'DETAILS_COMMENT'	=>	($objResult->fields['description'] != '') ? $objResult->fields['description'] : '-',
	 		'DETAILS_TYPE'		=>	strtoupper($objResult->fields['type']),
	 		'DETAILS_VERSION'	=>	$objResult->fields['version'].' '.$objResult->fields['edition'],
	 		'DETAILS_SIZE'		=>	$this->convertBytesToKBytes($objResult->fields['size']),
	 		'DETAILS_TABLES'	=>	str_replace(';',', ', $objResult->fields['usedtables']),
	 		'DETAILS_CONTENT'	=>	htmlentities($strFileContent, ENT_QUOTES)
	 	));
	 }
	 
	 
	 /**
	  * Converts an number of Bytes into Kilo Bytes.
	  *
	  * @return		float		converted size
	  */
	 private function convertBytesToKBytes($intNumberOfBytes) {
	 	$intNumberOfBytes = intval($intNumberOfBytes);
	 	return round($intNumberOfBytes / 1024, 2);
	 }

}

/**
* BackupBase
*
* This abstract class is the base class for all Backup-Types (SQL, CSV). It delivers standard functionality like "new line" and 
* "tabulators" for its subclasses.
*
* @copyright	CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author		Thomas Kaelin <thomas.kaelin@astalvista.ch>
* @access		public
* @module		DatabaseManager
* @modulegroup	core
* @version		1.0
*/
abstract class BackupBase {
	
	/**
	 * Return an table header for an table.
	 *
	 * @param		string 		$strTable: Name of the table which should be returned
	 * @return 		string		The generated table-header.
	 */
	function getTableHeader($strTable) {
		return ($this->hasCommentTags()) ? ( $this->getCommentString().' Table:'.$this->getTabulator().$strTable.$this->getEndLine() ) : '';
	}
	
	 /**
	  * Prints a separation line to use in sql-files.
	  *
	  * @return 	string 		separation Line
	  */
	 function getSeparationLine() {
	 	return ($this->hasCommentTags()) ? ( $this->getCommentString().'--------------------------------------------------------------'.$this->getEndLine() ) : '';
	 }
	 
	 /**
	  * Prints a specific number of Tabulator delimiter.
	  *
	  * @param 		integer 	$intNumberOfTabs: Number of tabulator-delimiter which should be chained.
	  * @return 	string		Desired number of tabulator-delimiter.
	  */
	 function getTabulator($intNumberOfTabs=1) {
	 	$strReturn = '';
		for ($i = 1; $i <= $intNumberOfTabs; ++$i) {
			$strReturn .= "\t";
		}
		return $strReturn;
	 }
	 
	 /**
	  * Prints a specific number of end of line (EOL) delimiter.
	  *
	  * @param 		integer 	$intNumberOfEndLines: Number of EOL-delimiter which should be chained.
	  * @return 	string		Desired number of EOL-delimiter.
	  */
	 function getEndLine($intNumberOfEndLines=1) {
	 	$strReturn = '';
		for ($i = 1; $i <= $intNumberOfEndLines; ++$i) {
			$strReturn .= "\n";
		}
		return $strReturn;
	 }

	 /**
	  * Defines if the current language supports comments. Some languages (for Example CVS) don't have a comment-tag, so
	  * in this languages some information should not be printed.
	  *
	  * @return 	boolean		true = Actual language supports comments
	  */
	 abstract function hasCommentTags();
	 
	 /**
	  * Returns the comment-delimiter for the backup-type.
	  * 
	  * @return 	string		The comment-delimiter.
	  */
	 abstract function getCommentString();
	  
	 /**
	  * Returns the Definition of a table in a string.
	  *
	  * @param		string 		$strTable: Name of the table which should be returned
	  * @return 	string		The generated table-definition
	  */
	 abstract function getTableDefinition($strTable);
	 
	 /**
	  * Returns the Content of a table in a string.
	  *
	  * @param		string 		$strTable: Name of the table which should be returned
	  * @return 	string		The generated table-content
	  */
	 abstract function getTableContent($strTable);
}

/**
* SQLBackup
*
* This class extends the BackupBase and implements the functionality for the SQL-Export.
*
* @copyright	CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author		Thomas Kaelin <thomas.kaelin@astalvista.ch>
* @access		public
* @module		DatabaseManager
* @modulegroup	core
* @version		1.0
*/
final class SQLBackup extends BackupBase  {	 
	
	 /**
	  * Defines if the current language supports comments. Some languages (for Example CVS) don't have a comment-tag, so
	  * in this languages some information should not be printed.
	  *
	  * @return 	boolean		true = Actual language supports comments
	  */
	 function hasCommentTags() {
	 	return true;
	 }
	
	 /**
	  * Returns a '#'-Char, which is the comment delimiter for SQL.
	  * 
	  * @return 	string		The comment-delimiter '#'
	  */
	function getCommentString() {
		return '#';
	}
	
	/**
	 * Prints the mySQL-definition of a table.
	 *
	 * @global 		object		database
	 * @param		string		$strTable: This table-header will be printed.
	 * @return 		string		The generated table-header.
	 */
	 function getTableDefinition($strTable) {
	 	global $objDatabase;
	 	
	 	$strReturn = '';
	 	
	 	//Drop table definition
	 	$strReturn .= 'DROP TABLE IF EXISTS `'.$strTable.'`;'.$this->getEndLine().$this->getEndLine();
	 	
	 	//Write table definition
	 	$strReturn .= 'CREATE TABLE `'.$strTable.'` ('.$this->getEndLine();
	 	
	 	$objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
	 	while (!$objResult->EOF) {
	 		$strReturn .= '  `'.$objResult->fields['Field'].'` ';
	 		$strReturn .= $objResult->fields['Type'].' ';
	 		$strReturn .= (empty($objResult->fields['Null']) ? 'NOT ' : '').'NULL';
	 		$strReturn .= (isset($objResult->fields['Default'])) ? $this->printTableDefaultTag($objResult->fields['Type'], $objResult->fields['Default']) : '';
	 		$strReturn .= ($objResult->fields['Extra'] == 'auto_increment') ? ' auto_increment' : '';
	 		$strReturn .= ','.$this->getEndLine();
	 		
	 		$objResult->MoveNext();
	 	}
	 
	 	//Get table keys and indices
	 	$arrTableKeys = array();
		$objResult = $objDatabase->Execute('SHOW KEYS FROM '.$strTable);
		while (!$objResult->EOF) {
			$strKeyName 	= $objResult->fields['Key_name'];
			$intSeqIndex 	= $objResult->fields['Seq_in_index'];
			
			$arrUniqueKeys[$strKeyName] = FALSE;
			if ($objResult->fields['Non_unique'] == 0) {
				$arrUniqueKeys[$strKeyName] = TRUE;
			}
			
			$arrFulltextKeys[$strKeyName] = FALSE;
			if ($objResult->fields['Comment'] == 'FULLTEXT' || $objResult->fields['Index_type'] == 'FULLTEXT') {
				$arrFulltextKeys[$strKeyName] = TRUE;
			}
			
			$arrTableKeys[$strKeyName][$intSeqIndex] = '`'.$objResult->fields['Column_name'].'`';
			ksort($arrTableKeys[$strKeyName]);
			$objResult->MoveNext();
		}
	
		//Write table keys and indices
		foreach ($arrTableKeys as $strKeyName => $strKeyFieldNames) {
			
			if ($strKeyName == 'PRIMARY') {
				$strReturn .= '  PRIMARY ';
			} else {
				$strReturn .= ($arrFulltextKeys[$strKeyName] ? '  FULLTEXT ' : '');
				$strReturn .= ($arrUniqueKeys[$strKeyName]   ? '  UNIQUE '   : '');
				$strReturn .= (!$arrFulltextKeys[$strKeyName] && !$arrUniqueKeys[$strKeyName]) ? '  ' : ''; //just for formatting
			}
			$strReturn .= 'KEY'.(($strKeyName == 'PRIMARY') ? '' : ' `'.$strKeyName.'`');
			$strReturn .= ' ('.implode(',', $strKeyFieldNames).')';
			$strReturn .= ','.$this->getEndLine();
		}
	 	
	 	//Close definition
	 	$strReturn = substr($strReturn,0,-2); //Cut last 2 charactes (,\n)
	 	$strReturn .= $this->getEndLine().') ';
	 	
	 	//Additional information
	 	$objResult = $objDatabase->Execute('SHOW TABLE STATUS LIKE "'.$strTable.'"');
	 	$strReturn .= 'ENGINE='.$objResult->fields['Engine'].' ';
	 	$strReturn .= 'DEFAULT CHARSET='.substr($objResult->fields['Collation'],0,strpos($objResult->fields['Collation'],'_')).' ';
	 	$strReturn .= 'COLLATE='.$objResult->fields['Collation'];
	 	
	 	if (intval($objResult->fields['Auto_increment']) > 0) {
	 		$strReturn .= ' AUTO_INCREMENT='.intval($objResult->fields['Auto_increment']);
	 	}
	 	
	 	$strReturn .= ';'; 	
	 	
	 	return $strReturn;
	 }
	 	 
	/**
	 * Prints the mySQL-contents of a table.
	 *
	 * @global 		object		database
	 * @param		string		$strTable: This table-contents will be printed.
	 * @return 		string		The generated table-content.
	 */
	 function getTableContent($strTable) {
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
		 	$strReturn .= ') VALUES '.$this->getEndLine();
		 	
		 	//Add values
		 	while (!$objTableContent->EOF) {
		 		$strReturn .= '(';
		 		foreach($arrColumnNames as $intKey => $strColumnName) {
		 			if (isset($objTableContent->fields[$strColumnName])) {
		 				$strReturn .= '\''.mysql_escape_string($objTableContent->fields[$strColumnName]).'\', ';
		 			} else {
		 				$strReturn .= 'NULL, ';
		 			}
		 		}
		 		$strReturn = substr($strReturn,0,-2); //Cut last 2 characters (, )
		 		$strReturn .= '),'.$this->getEndLine();
		 		$objTableContent->MoveNext();
		 	}
		 	$strReturn = substr($strReturn,0,-2); //Cut last 2 characters (,\n)
		 	
		 	//Write closing tag
		 	$strReturn .= ';';
		 	
	 	}
	 	
	 	return $strReturn;
	 }
	 
	 /**
	  * Prints the "default"-tg for a table-definition.
	  *
	  * @param 		string		$strType: data type of the column
	  * @param 		string	 	$strDefault: default value of the column
	  * @return 	strint		the generated default-tag.
	  */
	 private function printTableDefaultTag($strType, $strDefault) {
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
			default:
				return ' default \''.$strDefault.'\'';
		}
	 }
}


/**
* CSVBackup
*
* This class extends the BackupBase and implements the functionality for the CSV-Export.
*
* @copyright	CONTREXX CMS - Astalavista IT Engineering GmbH Thun
* @author		Thomas Kaelin <thomas.kaelin@astalvista.ch>
* @access		public
* @module		DatabaseManager
* @modulegroup	core
* @version		1.0
*/
final class CSVBackup extends BackupBase  {	 
	
	 /**
	  * Defines if the current language supports comments. Some languages (for Example CVS) don't have a comment-tag, so
	  * in this languages some information should not be printed.
	  *
	  * @return 	boolean		true = Actual language supports comments
	  */
	 function hasCommentTags() {
	 	return false;
	 }
	
	 /**
	  * Returns nothing (= ''), because CSV doesn't support comments.
	  * 
	  * @return 	string		An empty string ('')
	  */
	function getCommentString() {
		return '';
	}

	/**
	 * Prints the CSV-definition of a table.
	 *
	 * @global 		object		database
	 * @param		string		$strTable: This table-header will be printed.
	 * @return 		string		The generated table-header.
	 */
	 function getTableDefinition($strTable) {
	 	global $objDatabase;
	 	
	 	$strReturn = '';
	 	
	 	//Write column-names
	 	$objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
	 	while (!$objResult->EOF) {
	 		$strReturn .= '"'.$objResult->fields['Field'].'";';
	 		$objResult->MoveNext();
	 	}
	 	$strReturn = substr($strReturn,0,-1); //Cut last character (;)
	 	
	 	return $strReturn;
	 }
	
	/**
	 * Prints the CSV-contents of a table.
	 *
	 * @global 		object		database
	 * @param		string		$strTable: This table-contents will be printed.
	 * @return 		string		The generated table-content.
	 */
	 function getTableContent($strTable) {
	 	global $objDatabase;
	 	
	 	$strReturn = '';
	 
	 	//Count lines first
	 	$objTableContent = $objDatabase->Execute('SELECT * FROM '.$strTable);
	 	
	 	if ($objTableContent->RecordCount() > 0) {
		 	
		 	//Get column names
		 	$arrColumnNames = array();
		 	$objResult = $objDatabase->Execute('SHOW FIELDS FROM '.$strTable);
		 	while (!$objResult->EOF) {
		 		$arrColumnNames[count($arrColumnNames)] = $objResult->fields['Field'];
		 		$objResult->MoveNext();
		 	}
		 	
		 	//Add values
		 	while (!$objTableContent->EOF) {
		 		
		 		foreach($arrColumnNames as $intKey => $strColumnName) {
		 			if (isset($objTableContent->fields[$strColumnName])) {
		 				$strReturn .= '"'.addslashes($objTableContent->fields[$strColumnName]).'";';
		 			} else {
		 				$strReturn .= '"NULL";';
		 			}
		 		}
		 		$strReturn = substr($strReturn,0,-1); //Cut last character (;)
		 		$strReturn .= $this->getEndLine();
		 		
		 		$objTableContent->MoveNext();
		 	}		 	
	 	}	 	
	 	
	 	return $strReturn;
	 }
}