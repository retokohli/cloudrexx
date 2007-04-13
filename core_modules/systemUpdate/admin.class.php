<?php
/**
 * System Update
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author Astalavista Development Team <thun@astalvista.ch>
 * @version       1.0
 * @package     contrexx
 * @subpackage  core_module_systemupdate
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_FRAMEWORK_PATH.'/System.class.php';

/**
 * System Update
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author Astalavista Development Team <thun@astalvista.ch>
 * @version       1.0
 * @access        public
 * @package     contrexx
 * @subpackage  core_module_systemupdate
 */
class systemUpdate
{
	var $pageTitle;
	var $strErrMessage = '';
	var $strOkMessage = '';
	var $_objTpl;
	var $databasePrefix = "astalavista_";


	/**
	 * constructor
	 *
	 * @return systemUpdate
	 */
	function systemUpdate()
	{
		$this->__construct();
	}

	/**
	 * constructor
	 *
	 * @access private
	 * @global object $objTemplate
	 * @global array $_ARRAYLANG
	 */
	function __construct()
	{
    	global $objTemplate, $_ARRAYLANG;

		if (!$_SESSION['auth']['is_admin']) {
			header("Location: index.php?cmd=noaccess");
			exit;
		}

    	$this->_objTpl = &new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/systemUpdate/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

		$objTemplate->setVariable("CONTENT_NAVIGATION","<a href='?cmd=systemUpdate&amp;act=database'>".$_ARRAYLANG['TXT_DATABASE']."</a>");
	}


	/**
	 * get content
	 *
	 * get the content of the system update module
	 *
	 * @access public
	 * @global object $objTemplate
	 * @return string
	 */
	function getContent()
	{
		global $objTemplate;

		if(!isset($_REQUEST['act'])){
    		$_REQUEST['act'] = "";
    	}

		switch ($_REQUEST['act']){
    		case 'database':
    			$this->_getDatabasePage();
    			break;

    		default:
    			$this->_getDatabasePage();
    			break;
    	}

    	$objTemplate->setVariable(array(
    		'CONTENT_TITLE'				=> $this->pageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
    		'ADMIN_CONTENT'				=> $this->_objTpl->get()
    	));
	}


	/**
	 * get database page
	 *
	 * get the database page
	 *
	 * @access private
	 * @global array $_ARRAYLANG
	 * @see _getSqlString()
	 */
	function _getDatabasePage()
	{
		global $_ARRAYLANG;

		$sqlString = '';
		$arrSqlQueries = array();

		$objFWSystem = &new FWSystem();
		$maxUploadFilesize = number_format($objFWSystem->getMaxUploadFileSize()/1024)." KB";

    	$this->_objTpl->loadTemplateFile('module_systemUpdate_database.html',true,true);
    	$this->pageTitle = $_ARRAYLANG['TXT_DATABASE'];

    	// set language variables
    	$this->_objTpl->setVariable(array(
    		'TXT_EXECUTE_SQL_QUERY'	=> $_ARRAYLANG['TXT_EXECUTE_SQL_QUERY'],
    		'TXT_OR_FILE'			=> $_ARRAYLANG['TXT_OR_FILE'],
    		'TXT_EXECUTE'			=> $_ARRAYLANG['TXT_EXECUTE'],
    		'TXT_MAX_FILESIZE'		=> $_ARRAYLANG['TXT_MAX_FILESIZE']
    	));

    	$this->_objTpl->setVariable('SYSEM_UPDATE_MAX_FILESIZE', $maxUploadFilesize);

		if (isset($_POST['executeQuery'])) {
			// get sql string
			$sqlString = $this->_getSqlString();

			if (!empty($sqlString)) {
				// get sql queries
				$arrSqlQueries = $this->_splitSqlString($sqlString);

				// execute queries
				if (count($arrSqlQueries)>0) {
					foreach ($arrSqlQueries as $sqlQuery) {
						$this->_executeQuery($sqlQuery);
					}
				} else {
					$this->strErrMessage .= $_ARRAYLANG['TXT_NO_VALID_SQL_QUERY'];
				}
			}
		}
	}


	/**
	* Get the maximum file size that is allowed to upload
	*
	* @access private
	* @return string filesize
	*/
	function _getMaxUploadFileSize()
	{
		$upload_max_filesize = $this->_getBytes(@ini_get('upload_max_filesize'));
		$post_max_size = $this->_getBytes(@ini_get('post_max_size'));

		if ($upload_max_filesize < $post_max_size) {
			$maxUploadFilesize = $upload_max_filesize;
		} else {
			$maxUploadFilesize = $post_max_size;
		}
		return number_format($maxUploadFilesize/1024)." KB";
	}


	/**
	* Returns the value $size in bytes
	*
	* @access private
	* @param $size
	* @return integer
	*/
	function _getBytes($size)
	{
		$size = trim($size);
		$lastChar = strtolower(substr($size,strlen($size)-1));

		switch ($lastChar) {
			case 'g':
				$size *= 1024;
			case 'm':
				$size *= 1024;
			case 'k':
				$size *= 1024;
		}
		return $size;
	}

	/**
	 * get sql string
	 *
	 * get the sql string typed in to the form or from the defined file
	 *
	 * @access private
	 * @global array $_ARRAYLANG
	 * @see _splitSqlString()
	 */
	function _getSqlString()
	{
		global $_ARRAYLANG;

		$buffer = "";
		$sqlDumpFilePath = ASCMS_TEMP_PATH.'/sql_dump_file.sql';
		$sqlQuery;

		if (isset($_POST['sql_query']) && !empty($_POST['sql_query'])) {
			$sqlQuery = get_magic_quotes_gpc() ? stripslashes($_POST['sql_query']) : $_POST['sql_query'];

			$this->_objTpl->setVariable(array(
				'SQL_QUERY'	=> htmlspecialchars($sqlQuery, ENT_QUOTES, CONTREXX_CHARSET)
			));

			return $sqlQuery;
		} elseif (isset($_FILES['sql_file']['error']) && $_FILES['sql_file']['error'] == 0) {
			if (@move_uploaded_file($_FILES['sql_file']['tmp_name'], $sqlDumpFilePath)) {
				$sqlQuery = @file_get_contents($sqlDumpFilePath);
				if ($sqlQuery) {
					@unlink($sqlDumpFilePath);
					return $sqlQuery;
				} else {
					$this->strErrMessage .= $_ARRAYLANG['TXT_ERRORS_WHILE_READING_THE_FILE'];
				}
			} else {
				$this->strErrMessage .= $_ARRAYLANG['TXT_COULD_NOT_UPLOAD_FILE'];
			}
		}
	}

	/**
	 * split sql string
	 *
	 * split the sql string in sql queries
	 *
	 * @access private
	 * @param string $sqlQuery
	 * @see _executeQuery()
	 */
	function _splitSqlString($sqlQuery)
	{
    	$sqlQuery = trim($sqlQuery);
	    $char = '';
	    $queryStartPos = 0;
	    $stringDelimiter = '';
	    $isString = false;
	    $isComment = false;
	    $query = '';
	    $arrSqlQueries = array();

	    for ($charNr = 0; $charNr < strlen($sqlQuery); $charNr++) {
	    	if ($isComment) { // check if the loop is in a commentary
	    		if ($sqlQuery[$charNr] == "\r" || $sqlQuery[$charNr] == "\n") {
	    			$isComment = false;
	    			$queryStartPos = $charNr+1;
	    		}
			} elseif ($isString) { // check if the loop is in a string
	    		if ($sqlQuery[$charNr] == $stringDelimiter && ($sqlQuery[$charNr-1] != "\\" || $sqlQuery[$charNr-2] == "\\")) {
	    			$isString = false;
	    		}
	    	} elseif ($sqlQuery[$charNr] == "#" || (!empty($sqlQuery[$charNr+1]) && $sqlQuery[$charNr].$sqlQuery[$charNr+1] == "--")) {
	    		$isComment = true;

	    	} elseif ($sqlQuery[$charNr] == '"' || $sqlQuery[$charNr] == "'" || $sqlQuery[$charNr] == "`") { // check if this is a string delimiter
	    		$isString = true;
	    		$stringDelimiter = $sqlQuery[$charNr];
	    	} elseif ($sqlQuery[$charNr] == ";") { // end of query reached
	    		$charNr++;
	    		$query = ltrim(substr($sqlQuery, $queryStartPos, $charNr-$queryStartPos));
	    		array_push($arrSqlQueries, $query);
	    		$queryStartPos = $charNr;
	    	}
	    }
	    return $arrSqlQueries;
	}

	/**
	 * execute query
	 *
	 * execute the sql query
	 *
	 * @access private
	 * @param string $query
	 * @global object $objDatabase
	 * @global array $_DBCONFIG
	 */
	function _executeQuery($query)
	{
		global $objDatabase, $_DBCONFIG;

		$query = str_replace($this->databasePrefix, $_DBCONFIG['tablePrefix'], $query);
		$objResult = $objDatabase->Execute($query);
		if ($objResult) {
			$this->strOkMessage .= htmlentities($query, ENT_QUOTES, CONTREXX_CHARSET)."<br />";
			if ($objResult->RecordCount() > 0) {
				$header = "<tr>\n<th style='border-bottom: 1px solid #000000; border-right: 1px solid #000000;'>".implode("</th>\n<th style='border-bottom: 1px solid #000000; border-right: 1px solid #000000;'>", array_keys($objResult->fields))."</th>\n</tr>\n";
				$body = '';
				while (!$objResult->EOF) {
					$body .= "<tr>\n<td style='border-bottom: 1px solid #000000; border-right: 1px solid #000000;'>".implode("</td>\n<td style='border-bottom: 1px solid #000000; border-right: 1px solid #000000;'>", array_map(array($this, '_prepareOutput'), $objResult->fields))."</td>\n</tr>\n";
					$objResult->MoveNext();
				}
				$this->strOkMessage .= "<table style='border-left: 1px solid #000000; border-top: 1px solid #000000;' cellpadding='3' cellspacing='0'>\n".$header.$body."</table>";
			}
		} else {
			$this->strErrMessage .= htmlentities($query, ENT_QUOTES, CONTREXX_CHARSET).":<br />";
			$this->strErrMessage .= $objDatabase->ErrorMsg()."<br />";
		}
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
}
?>
