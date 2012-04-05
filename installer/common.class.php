<?php
/**
 * Install Wizard Controller
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       $Id:     Exp $
 * @package     contrexx
 * @subpackage  installer
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Install Wizard Controller
 *
 * The Install Wizard
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       $Id:     Exp $
 * @package     contrexx
 * @subpackage  installer
 */
class CommonFunctions
{
	var $defaultLanguage;
	var $detectedLanguage;
	var $adoDbPath;
	var $ftpTimeout;
	var $ftpPort;
	var $newestVersion;
	var $_ftpFileWinPCRE = '#^()(?:[0-9\-]+)\s+(?:[0-9]{2}:[0-9]{2}(?:AM|PM|))\s+(?:\<DIR\>|[0-9]+|)\s+(.*)$#';
	var $_ftpFileUnixPCRE = '#^(?:([bcdlsp-])[rwxtTsS-]{9})\s+(?:[0-9]+)\s+(?:\S+)\s+(?:\S+)\s+(?:[0-9]+)\s+(?:[A-Z][a-z]+\s+[0-9]{1,2}\s+(?:[0-9]{4}|[0-9]{2}:[0-9]{2}|))\s+(.*)$#';

	function CommonFunctions()
	{
	    $this->__construct();
	}

	function __construct() {
		global $defaultLanguage, $arrLanguages;

	    $this->defaultLanguage = $defaultLanguage;
	    $this->adoDbPath = '..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'adodb'.DIRECTORY_SEPARATOR.'adodb.inc.php';
	    $this->ftpTimeout = 5;
	    $this->ftpPort = 21;

	    // set language
	    if (isset($_GET['langId']) && array_key_exists($_GET['langId'], $arrLanguages)) {
			$_SESSION['installer']['langId'] = $_GET['langId'];
		}
	}

	/**
	* init language
	*
	* initialize language array
	*
	* @access	public
	* @global	array	$_ARRLANG
	* @global	string	$basePath
	* @global	string	$language
	*/
	function initLanguage() {
		global $_ARRLANG, $basePath, $language;

		$language = $this->_getLanguage();

		require_once($basePath.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$language.'.lang.php');
	}

	/**
	* check language existence
	*
	* check if the language file of the langauge $language exists
	*
	* @access	private
	* @param	string	$language
	* @global	string	$basePath
	* @return	boolean	true if exists, false if not
	*/
	function _checkLanguageExistence($language) {
		global $basePath;

		if (file_exists($basePath.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$language.'.lang.php')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* get language
	*
	* get the language to use
	*
	* @access	private
	* @global	string	$basePath
	* @global	array	$arrLanguages
	* @return	string	language to use
	*/
	function _getLanguage() {
		global $basePath, $arrLanguages;

		$language = $this->defaultLanguage;

		if (isset($_SESSION['installer']['langId'])) {
			$language = $arrLanguages[$_SESSION['installer']['langId']]['lang'];
		} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		    $language = substr(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']),0,2);
		}

		if ($this->_checkLanguageExistence($language)) {
			return $language;
		} else {
			return $this->defaultLanguage;
		}
	}

	/**
	* get database object
	*
	* return an database connection object. if not already a connection has established, then it will do it
	*
	* @access	private
	* @param	string	$statusMsgError message
	* @global	object	$objDb
	* @global	array	$_ARRLANG
	* @global	string	$dbType
	* @return	mixed	object $objDb on success, false on failure
	*/
	function _getDbObject(&$statusMsg, $useDb = true) {
		global $objDb, $_ARRLANG, $dbType, $useUtf8;

		if (isset($objDb)) {
			return $objDb;
		} else {
			// open db connection
			require_once $this->adoDbPath;

			$objDb = ADONewConnection($dbType);
			@$objDb->Connect($_SESSION['installer']['config']['dbHostname'], $_SESSION['installer']['config']['dbUsername'], $_SESSION['installer']['config']['dbPassword'], ($useDb ? $_SESSION['installer']['config']['dbDatabaseName'] : null));

			$errorNo = $objDb->ErrorNo();
			if ($errorNo != 0) {
				if ($errorNo == 1049) {
					$statusMsg = str_replace("[DATABASE]", $_SESSION['installer']['config']['dbDatabaseName'], $_ARRLANG['TXT_DATABASE_DOES_NOT_EXISTS']."<br />");
				} else {
					$statusMsg =  $objDb->ErrorMsg();
				}
				unset($objDb);
				return false;
			}

			if ($objDb) {
				if (($mysqlServerVersion = $this->getMySQLServerVersion()) !== false && !$this->_isNewerVersion($mysqlServerVersion, '4.1')) {
					if ($objDb->Execute('SET CHARACTER SET '.($useUtf8 ? 'utf8' : 'latin1')) !== false) {
						return $objDb;
					}
				} else {
					return $objDb;
				}

				$statusMsg = $_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER']."<i>&nbsp;(".$objDb->ErrorMsg().")</i><br />";
				unset($objDb);
			} else {
				$statusMsg = $_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER']."<i>&nbsp;(".$objDb->ErrorMsg().")</i><br />";
				unset($objDb);
			}
			return false;
		}
	}

	/**
	* close Db connection
	*
	* close database connection
	*
	* @access	public
	* @global	object	$objDb
	*/
	function closeDbConnection() {
		global $objDb;

		if (isset($objDb)) {
			@$objDb->Close();
		}
	}

	function getMySQLServerVersion()
	{
		$statusMsg = '';

		$objDb = $this->_getDbObject($statusMsg, false);
		if ($objDb === false) {
			return $statusMsg;
		} else {
			$objVersion = $objDb->SelectLimit('SELECT VERSION() AS mysqlversion', 1);
			if ($objVersion !== false && $objVersion->RecordCount() == 1 && preg_match('#^([0-9.]+)#', $objVersion->fields['mysqlversion'], $version)) {
				return $version[1];
			} else {
				return false;
			}
		}
	}

	function _isNewerVersion($installedVersion, $newVersion)
	{
		$arrInstalledVersion = explode('.', $installedVersion);
		$arrNewVersion = explode('.', $newVersion);

		$maxSubVersion = count($arrInstalledVersion) > count($arrNewVersion) ? count($arrInstalledVersion) : count($arrNewVersion);
		for ($nr = 0; $nr < $maxSubVersion; $nr++) {
			if (!isset($arrInstalledVersion[$nr])) {
				return true;
			} elseif (!isset($arrNewVersion[$nr])) {
				return false;
			} elseif ($arrNewVersion[$nr] > $arrInstalledVersion[$nr]) {
				return true;
			} elseif ($arrNewVersion[$nr] < $arrInstalledVersion[$nr]) {
				return false;
			}
		}

		return false;
	}

	function _getUtf8Collations()
	{
		$arrCollate = array();

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb === false) {
			return $statusMsg;
		} else {
			$objCollation = $objDb->Execute('SHOW COLLATION');
			if ($objCollation !== false) {
				while (!$objCollation->EOF) {
					if ($objCollation->fields['Charset'] == 'utf8') {
						$arrCollate[] = $objCollation->fields['Collation'];
					}
					$objCollation->MoveNext();
				}

				return $arrCollate;
			} else {
				return false;
			}
		}
	}

	/**
	* get FTP object
	*
	* return an FTP connection object. if not already a connection has established, then it will do it
	*
	* @access	private
	* @param	string	$statusMsgError message
	* @global	object	$objFtp
	* @global	array	$_ARRLANG
	* @return	mixed	object $objFtp on success, false on failure
	*/
	function _getFtpObject(&$statusMsg) {
		global $objFtp, $_ARRLANG;

		if (isset($objFtp)) {
			return $objFtp;
		} else {
			if (isset($_SESSION['installer']['config']['ftpPort'])) {
				$this->ftpPort = $_SESSION['installer']['config']['ftpPort'];
			}

			// open ftp conneciton
			$objFtp = @ftp_connect($_SESSION['installer']['config']['ftpHostname'], $this->ftpPort, $this->ftpTimeout);
			if ($objFtp) {
				// login to ftp server
				if (@ftp_login($objFtp, $_SESSION['installer']['config']['ftpUsername'], $_SESSION['installer']['config']['ftpPassword'])) {
					if ($_SESSION['installer']['config']['ftpPasv']) {
						if (@ftp_pasv($objFtp, true)) {
							return $objFtp;
						} else {
							@ftp_close($objFtp);
							$statusMsg = $_ARRLANG['TXT_FTP_PASSIVE_MODE_FAILED'];
						}
					} else {
						return $objFtp;
					}
				} else {
					@ftp_close($objFtp);
					unset($objFtp);
					$statusMsg = $_ARRLANG['TXT_FTP_AUTH_FAILED']."<br />";
				}
			} else {
				unset($objFtp);
				$statusMsg = $_ARRLANG['TXT_CANNOT_CONNECT_TO_FTP_HOST']."<br />";
			}
			return false;
		}
	}

	/**
	* Close FTP connection
	*
	* Close the FTP connection of the object $objFtp
	*
	* @access	public
	* @global	object	$objFtp
	*/
	function closeFtpConnection() {
		global $objFtp;

		if (isset($objFtp)) {
			@ftp_close($objFtp);
		}
	}



	function getPHPVersion() {
		return phpversion();
	}

	function checkMysqlVersion($installedMySQLVersion, $requiredVersion = null) {
		global $requiredMySQLVersion;

		$arrInstalledVersion = explode('.', $installedMySQLVersion);
		$arrRequiredVersion = explode('.', empty($requiredVersion) ? $requiredMySQLVersion : $requiredVersion);

		$maxSubVersion = count($arrInstalledVersion) > count($arrRequiredVersion) ? count($arrInstalledVersion) : count($arrRequiredVersion);
		for ($nr = 0; $nr < $maxSubVersion; $nr++) {
			if (!isset($arrRequiredVersion[$nr])) {
				return true;
			} elseif (!isset($arrInstalledVersion[$nr])) {
				return false;
			} elseif ($arrInstalledVersion[$nr] > $arrRequiredVersion[$nr]) {
				return true;
			} elseif ($arrInstalledVersion[$nr] < $arrRequiredVersion[$nr]) {
				return false;
			}
		}

		return true;
	}

	function checkMySQLSupport() {
		if (extension_loaded('mysql')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* check FTP support
	*
	* check if the ftp extension is loaded
	*
	* @access	public
	* @return	boolean
	*/
	function checkFTPSupport() {
		if (extension_loaded("ftp")) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* check installation status
	*
	* check if the system is already installed
	*
	* @access	public
	* @global	string	$configFile
	* @return	boolean
	*/
	function checkInstallationStatus() {
		global $configFile;

		$result = @include_once'..'.$configFile;
		if ($result === false) {
			return false;
		} else {
			return (defined('CONTEXX_INSTALLED') && CONTEXX_INSTALLED);
		}
	}

	/**
	* check gd support
	*
	* check for the gd(graphics draw) extension.
	* if it is supported check also if the version is equal or higher to version 2
	*
	* @access	public
	* @return	mixed
	*/
	function checkGDSupport() {
		if (extension_loaded("gd")) {
			$arrGdInfo = gd_info();

			preg_match("/[\d\.]+/", $arrGdInfo['GD Version'], $matches);
			if (!empty($matches[0])) {
				return $matches[0];
			}
		}
		return false;
	}

	/**
	* check rss support
	*
	* check if the rss function can be used
	*
	* @access	public
	* @return	boolean
	*/
	function checkRSSSupport() {
		if (ini_get('allow_url_fopen') != "1") {
			@ini_set('allow_url_fopen', "1");
			if (ini_get('allow_url_fopen') != "1") {
				return false;
			}
		}
		return true;
	}

	/**
	* check CMS path
	*
	* check if the cms could be found in the specified path
	*
	* @access	public
	* @global	array	$_ARRLANG
	* @global	array	$arrFiles
	* @return	mixed	true on success, $statusMsg on failure
	*/
	function checkCMSPath() {
		global $_ARRLANG, $arrFiles;

		$statusMsg = "";

		if (!ini_get('safe_mode')) {
			if (!file_exists($_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].'/index.php')) {
				return str_replace("[PATH]", $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'], $_ARRLANG['TXT_PATH_DOES_NOT_EXIST']);
			} else {
				foreach (array_keys($arrFiles) as $file) {
					if (!file_exists($_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$file)) {
						$statusMsg .= str_replace("[FILE]", $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$file, $_ARRLANG['TXT_CANNOT_FIND_FIlE']);
					}
				}
				if (empty($statusMsg)) {
					return true;
				} else {
					return $statusMsg;
				}
			}
		} else {
			return true;
		}
	}

	function checkDbConnection($host, $user, $password) {
		global $_ARRLANG, $dbType, $requiredMySQLVersion;

		require_once $this->adoDbPath;

		$db = ADONewConnection($dbType);
		@$db->NConnect($host, $user, $password);

		$errorNr = $db->ErrorNo();
		$arrServerInfo = $db->ServerInfo();
		$db->Close();

		if ($errorNr == 0) {
			if ($this->checkMysqlVersion($arrServerInfo['version'])) {
				return true;
			} else {
				return str_replace("[VERSION]", $requiredMySQLVersion, $_ARRLANG['TXT_MYSQL_VERSION_REQUIRED']."<br />")
					.sprintf($_ARRLANG['TXT_MYSQL_SERVER_VERSION'], $arrServerInfo['version']);
			}
		} else {
			return $_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER']."<i>&nbsp;(".$db->ErrorMsg().")</i><br />";
		}
	}

	function existDatabase($host, $user, $password, $database) {
		global $_ARRLANG, $dbType;

		require_once $this->adoDbPath;

		$db = ADONewConnection($dbType);
		@$db->Connect($host, $user, $password, $database);

		$errorNr = $db->ErrorNo();

		if ($db->IsConnected()) {
			$db->Close();
			return true;
		} else {
			return false;
		}
	}

	function checkFTPConnection() {
		global $_ARRLANG;

		$statusMsg = "";

		$objFtp = $this->_getFtpObject($statusMsg);
		if ($objFtp === false) {
			return $statusMsg;
		} else {
			return true;
		}
	}

	function checkFtpPath() {
		global $_ARRLANG, $arrFiles;

		$statusMsg = "";

		$objFtp = $this->_getFtpObject($statusMsg);
		if ($objFtp === false) {
			return $statusMsg;
		} else {
			if (empty($_SESSION['installer']['config']['ftpPath'])) {
				$_SESSION['installer']['config']['ftpPath'] = @ftp_pwd($objFtp);
			}

			if (!@ftp_chdir($objFtp, $_SESSION['installer']['config']['ftpPath'].$_SESSION['installer']['config']['offsetPath'])) {
				return $_ARRLANG['TXT_FTP_PATH_DOES_NOT_EXISTS']."<br />";
			} else {
				foreach (array_keys($arrFiles) as $file) {
					if (!@ftp_chdir($objFtp, dirname($_SESSION['installer']['config']['ftpPath'].$_SESSION['installer']['config']['offsetPath'].$file))) {
						$statusMsg .= str_replace("[DIRECTORY]", dirname($_SESSION['installer']['config']['ftpPath'].$_SESSION['installer']['config']['offsetPath'].$file), $_ARRLANG['TXT_DIRECTORY_ON_FTP_DOES_NOT_EXIST']."<br />");
					} else {
						$arrFTPFiles = $this->_getFilesOfFtpDirectory($objFtp);
						if (!is_array($arrFTPFiles)) {
							return $arrFTPFiles;
						}
						preg_match("/.*\/([\d\D]+)$/", $file, $arrMatches);
						$checkFile = $arrMatches[1];
						if (!is_array($arrFTPFiles) || !in_array($checkFile, $arrFTPFiles)) {
							$statusMsg .= str_replace("[FILE]", $_SESSION['installer']['config']['ftpPath'].$_SESSION['installer']['config']['offsetPath'].$file, $_ARRLANG['TXT_FILE_ON_FTP_DOES_NOT_EXIST']."<br />");
						}
					}
				}
				if (empty($statusMsg)) {
					return true;
				} else {
					return $statusMsg;
				}
			}
		}
	}

	function _getFilesOfFtpDirectory(&$objFtp)
	{
		$arrDirectories = array();
		if (($fileList = ftp_rawlist($objFtp, ".")) !== false) {
			if (count($fileList) > 0) {
				if ($this->isWindowsFtp($objFtp)) {
					$pcre = $this->_ftpFileWinPCRE;
				} else {
					$pcre = $this->_ftpFileUnixPCRE;
				}

				foreach ($fileList as $fileDescription) {
					if (preg_match($pcre, $fileDescription, $arrFile)) {
                        if ($arrFile[1] == 'l') {
                            $file = substr($arrFile[2], strpos($arrFile[2], '-> ') + 3);
                        } else {
                            $file = $arrFile[2];
                        }
                        if ($file != '.' && $file != '..') {
                            array_push($arrDirectories, $file);
                        }
					}
				}
			}
		} else {
			return false;
		}
		return $arrDirectories;
	}

	/**
	* is windows ftp
	*
	* checks if the system of the ftp server is windows or unix
	*
	* @access public
	* @param object &$objFtp
	* @return boolean true if is windows, false if not
	*/
	function isWindowsFtp(&$objFtp)
	{
		$hostType = @ftp_systype($objFtp);
		if ($hostType !== false) {
			if (eregi('win', $hostType)) {
				return true;
			} else {
				return false;
			}
		} else {
			return $this->isWindows();
		}
	}

	/**
	* is windows
	*
	* check if the system on which php is runnis is a windows system
	*
	* @access	public
	* @return	boolean
	*/
	function isWindows() {
		if (substr(PHP_OS,0,3) == "WIN") {
			return true;
		} else {
			return false;
		}
	}

	function _isNewestVersion($thisVersion, $newestVersion)
	{
		$arrInstalledVersion = explode('.', $thisVersion);
		$arrNewVersion = explode('.', $newestVersion);

		$maxSubVersion = count($arrInstalledVersion) > count($arrNewVersion) ? count($arrInstalledVersion) : count($arrNewVersion);
		for ($nr = 0; $nr < $maxSubVersion; $nr++) {
			if (!isset($arrInstalledVersion[$nr])) {
				return false;
			} elseif (!isset($arrNewVersion[$nr])) {
				return true;
			} elseif ($arrNewVersion[$nr] > $arrInstalledVersion[$nr]) {
				return false;
			} elseif ($arrNewVersion[$nr] < $arrInstalledVersion[$nr]) {
				return true;
			}
		}

		return true;
	}

	/**
	* Check permisssions
	*
	* Check if the files of the array $arrFiles are writable
	*
	* @access public
	* @param array	$arrFiles
	* @return mixed	true on success, string with error message on faiure
	*/
	function checkPermissions($arrFiles) {
		global $_ARRLANG;

		$statusMessage = "";
		$path = $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'];

		foreach ($arrFiles as $file => $arrAttributes) {
			$arrAllFiles = array();
			$arrSubDirs = array();

			if (isset($arrAttributes['sub_dirs']) && $arrAttributes['sub_dirs']) {
				$arrSubDirs = $this->_getSubDirs($file);
				$arrAllFiles = array_merge(array($file), $arrSubDirs);
			} else {
				$arrAllFiles = array($file);
			}

			foreach ($arrAllFiles as $checkFile) {
				if (!is_writable($path.$checkFile)) {
					if ($this->isWindows()) {
						if (empty($statusMessage)) {
							$statusMessage = $_ARRLANG['TXT_SET_WRITE_PERMISSION_TO_FILES']."<br />";
						}
						$statusMessage .= $path.$checkFile."<br />";
					} else {
						$result = $this->_chmod($checkFile, $arrAttributes);
						if ($result !== true) {
							$statusMessage .= $result;
						}
					}
				}
			}
		}
		if (empty($statusMessage)) {
			return true;
		} else {
			return $statusMessage;
		}
	}

	/**
	* chmod
	*
	* Change file access permissions
	*
	* @access priavte
	* @param string	$file	File to change permissions
	* @param integer	$mode	Mode to set on file
	* @return	boolean	true on success, false on failure
	*/
	function _chmod($file, $arrAttributes) {
		global $_ARRLANG;

		if (!$this->isWindows() && $_SESSION['installer']['config']['useFtp']) {
			$objFtp = $this->_getFtpObject($statusMsg);
			if ($objFtp === false) {
				return $statusMsg;
			} else {
				if (phpversion()>=5) {
					// if system supports php5
					if (@ftp_chmod($objFtp, $arrAttributes['mode_oct'], $_SESSION['installer']['config']['ftpPath'].$_SESSION['installer']['config']['offsetPath'].$file)) {
						return true;
					} else {
						return $_ARRLANG['TXT_COULD_NOT_CHANGE_PERMISSIONS'].' '.$file."<br />";
					}
				} else {
					// if system don't support php5 yet
					$chmodCmd = "CHMOD ".$arrAttributes['mode']." ".$_SESSION['installer']['config']['ftpPath'].$_SESSION['installer']['config']['offsetPath'].$file;
					if (@ftp_site($objFtp, $chmodCmd)) {
						return true;
					} else {
						return $_ARRLANG['TXT_COULD_NOT_CHANGE_PERMISSIONS'].' '.$file."<br />";
					}
				}
			}
		} else {
			if (@chmod($_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$file, $arrAttributes['mode_oct'])) {
				return true;
			} else {
				return $_ARRLANG['TXT_COULD_NOT_CHANGE_PERMISSIONS'].' '.$file."<br />";;
			}
		}
	}

	/**
	* get sub directories
	*
	* get sub directories of the directory $directory
	*
	* @access private
	* @param string	$directoryDirectory to scan
	* @return array	$arrDirectories Subdirectories
	*/
	function _getSubDirs($directory) {
		$arrDirectories = array();

		$directoryPath = $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$directory;

		$fp = @opendir($directoryPath);
		if ($fp) {
			while ($file = readdir($fp)) {
				$path = $directoryPath.DIRECTORY_SEPARATOR.$file;

				if ($file != "." && $file != ".." && $file != ".svn") {
					array_push($arrDirectories, $directory.DIRECTORY_SEPARATOR.$file);

					if (is_dir(realpath($path))) {
						$arrDirectoriesRec = $this->_getSubDirs($directory.DIRECTORY_SEPARATOR.$file);
						if (count($arrDirectoriesRec)>0) {
							$arrDirectories = array_merge($arrDirectories, $arrDirectoriesRec);
						}
					}
				}
			}
			closedir($fp);
		}
		return $arrDirectories;
	}

	function _getConfigFileTemplate(&$statusMsg) {
		global $configTemplateFile, $_ARRLANG, $useUtf8;

		$str = "";

	    $str = @file_get_contents($configTemplateFile);

	    if (empty($str)) {
	    	$statusMsg = str_replace("[FILENAME]", $configTemplateFile, $_ARRLANG['TXT_CANNOT_OPEN_FILE']."<br />");
	    }

	    //PATHS
	 	$str = str_replace(
	 		array("%PATH_ROOT%", "%PATH_ROOT_OFFSET%"),
	 		array($_SESSION['installer']['config']['documentRoot'], $_SESSION['installer']['config']['offsetPath']),
	 		$str
	 	);

	    //MySQL
	    $str = str_replace(
	    	array("%DB_HOST%", "%DB_NAME%", "%DB_USER%", "%DB_PASSWORD%", "%DB_TABLE_PREFIX%", "%DB_CHARSET%"),
	    	array($_SESSION['installer']['config']['dbHostname'], $_SESSION['installer']['config']['dbDatabaseName'], $_SESSION['installer']['config']['dbUsername'], $_SESSION['installer']['config']['dbPassword'], $_SESSION['installer']['config']['dbTablePrefix'], !empty($_SESSION['installer']['config']['dbCollation']) ? 'utf8' : ''),
	    	$str
	    );

	    // CHARSET
	    $str = str_replace("%CHARSET%", $useUtf8 ? 'UTF-8' : 'ISO-8859-1', $str);

	    //FTP
	    if ($_SESSION['installer']['config']['useFtp']) {
	    	$str = str_replace(
	    		array("%FTP_STATUS%", "%FTP_PASSIVE%", "%FTP_HOST%", "%FTP_PORT%", "%FTP_USER%", "%FTP_PASSWORD%", "%FTP_PATH%"),
	    		array("true", ($_SESSION['installer']['config']['ftpPasv'] ? "true" : "false"), $_SESSION['installer']['config']['ftpHostname'], (isset($_SESSION['installer']['config']['ftpPort']) ? $_SESSION['installer']['config']['ftpPort'] : $this->ftpPort), $_SESSION['installer']['config']['ftpUsername'], $_SESSION['installer']['config']['ftpPassword'], $_SESSION['installer']['config']['ftpPath']),
	    		$str
	    	);
	    } else {
	    	$str = str_replace(
	    		array("%FTP_STATUS%", "%FTP_PASSIVE%", "%FTP_HOST%", "%FTP_PORT%", "%FTP_USER%", "%FTP_PASSWORD%", "%FTP_PATH%"),
	    		array("false", "false", "", $this->ftpPort, "", "", ""),
	    		$str
	    	);
	    }
		return $str;
	}
        
        function _getHtaccessFileTemplate() {
                global $htaccessTemplateFile, $_CONFIG;
                
                return str_replace(
	 		array("%PATH_ROOT_OFFSET%"),
	 		array($_SESSION['installer']['config']['offsetPath']),
	 		@file_get_contents($htaccessTemplateFile)
	 	);
        }

	/**
	* get version template file
	*
	* get the version template file, set the values and return it
	*
	* @access private
	* @return string parsed version template file
	*/
	function _getVersionTemplateFile() {
		global $versionTemplateFile, $_CONFIG;

		return str_replace(
			array("%CMS_NAME%","%CMS_VERSION%", "%CMS_STATUS%", "%CMS_EDITION%", "%CMS_CODE_NAME%", "%CMS_RELEASE_DATE%"),
			array($_CONFIG['coreCmsName'], $_CONFIG['coreCmsVersion'], $_CONFIG['coreCmsStatus'], $_CONFIG['coreCmsEdition'], $_CONFIG['coreCmsCodeName'], $_CONFIG['coreCmsReleaseDate']),
			@file_get_contents($versionTemplateFile)
		);
	}

	function createDatabase() {
		global $_ARRLANG, $dbType, $useUtf8;

		require_once $this->adoDbPath;

		$result = "";

		$db = ADONewConnection($dbType);
		@$db->Connect($_SESSION['installer']['config']['dbHostname'], $_SESSION['installer']['config']['dbUsername'], $_SESSION['installer']['config']['dbPassword']);

		$arrServerInfo = $db->ServerInfo();

		$result = @$db->Execute("CREATE DATABASE `".$_SESSION['installer']['config']['dbDatabaseName']."`".($this->checkMysqlVersion($arrServerInfo['version'], '4.1.1') ? " DEFAULT CHARACTER SET ".($useUtf8 ? "utf8 COLLATE ".$_SESSION['installer']['config']['dbCollation'] : "latin1") : null));

		if ($result === false) {
			return $_ARRLANG['TXT_COULD_NOT_CREATE_DATABASE']."<br />";;
		} else {
			@$db->Close();
			return true;
		}
	}

	function setDatabaseCharset()
	{
		global $_ARRLANG, $dbType, $useUtf8;

		require_once $this->adoDbPath;

		$result = "";

		$db = ADONewConnection($dbType);
		@$db->Connect($_SESSION['installer']['config']['dbHostname'], $_SESSION['installer']['config']['dbUsername'], $_SESSION['installer']['config']['dbPassword']);

		$result = @$db->Execute("ALTER DATABASE `".$_SESSION['installer']['config']['dbDatabaseName']."` DEFAULT CHARACTER SET utf8 COLLATE ".$_SESSION['installer']['config']['dbCollation']);

		if ($result === false) {
			return $_ARRLANG['TXT_COULD_NOT_SET_DATABASE_CHARSET']."<br />";;
		} else {
			@$db->Close();
			return true;
		}
	}

	function executeSQLQueries($type)
	{
		global $_ARRLANG, $sqlDumpFile, $dbType, $dbPrefix, $arrDatabaseTables, $useUtf8;

		$sqlQuery = "";
		$buffer = "";
		$result = "";
		$statusMsg = "";
		$dbPrefixRegexp = '#`'.$dbPrefix.'('.implode('|', $arrDatabaseTables).')`#';

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb === false) {
			return $statusMsg;
		} else {
			// insert sql dump file
			$sqlDump = $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$sqlDumpFile.'_'.$type.'.sql';

			$fp = @fopen ($sqlDump, "r");
			if ($fp !== false) {
				while (!feof($fp)) {
					$buffer = fgets($fp);
					if ((substr($buffer,0,1) != "#") && (substr($buffer,0,2) != "--")) {
						$sqlQuery .= $buffer;
						if (preg_match("/;[ \t\r\n]*$/", $buffer)) {
							$sqlQuery = preg_replace($dbPrefixRegexp, '`'.$_SESSION['installer']['config']['dbTablePrefix'].'$1`', $sqlQuery);
							$result = @$objDb->Execute($sqlQuery);
							if ($result === false) {
								$statusMsg .= "<br />".htmlentities($sqlQuery, ENT_QUOTES, ($useUtf8 ? 'UTF-8' : 'ISO-8859-1'))."<br /> (".$objDb->ErrorMsg().")<br />";
							}
							$sqlQuery = "";
						}
					}
				}
			} else {
				return str_replace("[FILENAME]", $sqlDump, $_ARRLANG['TXT_COULD_NOT_READ_SQL_DUMP_FILE']."<br />");
			}
			if (empty($statusMsg)) {
				return true;
			} else {
				return $_ARRLANG['TXT_SQL_QUERY_ERROR'].$statusMsg;
			}
		}
	}

	function createDatabaseTables()
	{
		return $this->executeSQLQueries('structure');
	}

	function insertDatabaseData()
	{
		return $this->executeSQLQueries('data');
	}

	function checkDatabaseTables() {
		global $arrDatabaseTables, $_ARRLANG, $sqlDumpFile, $dbType, $_CONFIG;

		$statusMsg = "";
		$arrTables = array();

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb === false) {
			return $statusMsg;
		} else {
			$result = $objDb->Execute($objDb->metaTablesSQL);
			if ($result) {
				while ($arrResult = $result->FetchRow()) {
					array_push($arrTables, $arrResult[0]);
				}
			}
		}

		foreach ($arrDatabaseTables as $table) {
			if (!in_array($_SESSION['installer']['config']['dbTablePrefix'].$table, $arrTables)) {
				$statusMsg .= str_replace("[TABLE]", $table, $_ARRLANG['TXT_TABLE_NOT_AVAILABLE'])."<br />";
			}
		}

		if (empty($statusMsg)) {
			return true;
		} else {
			$statusMsg .= str_replace("[FILEPATH]", $_SESSION['installer']['config']['offsetPath'].str_replace(DIRECTORY_SEPARATOR, '/', $sqlDumpFile).'_structure.sql', $_ARRLANG['TXT_CREATE_DATABAES_TABLE_MANUALLY'])."<br />";
			$statusMsg .= $_ARRLANG['TXT_PRESS_REFRESH_TO_CONTINUE_INSTALLATION'];
			return $statusMsg;
		}
	}
        
        function createHtaccessFile() {
		global $basePath, $offsetPath, $htaccessFile, $_ARRLANG, $_CORELANG;

		$htaccessFileContent = $this->_getHtaccessFileTemplate();
                
                if (!@include_once(ASCMS_LIBRARY_PATH.'/FRAMEWORK/FWHtAccess.class.php')) {
                    die('Unable to load file '.ASCMS_LIBRARY_PATH.'/FRAMEWORK/FWHtAccess.class.php');
                }
                $_CORELANG = $_ARRLANG;
                $htaccess = new FWHtAccess(dirname($basePath), $offsetPath);
                $result = $htaccess->loadHtAccessFile($htaccessFile);
                if ($result !== true) {
                    return $result;
                }
                
                $htaccess->setSection("core_routing", explode("\n", $htaccessFileContent));
                $result = $htaccess->write();
                if ($result !== true) {
                    return $result;
                }
                return true;
        }

	function createConfigFile() {
		global $configFile, $_ARRLANG;

		$statusMsg = "";

		$configFileContent = $this->_getConfigFileTemplate($statusMsg);
		if (!empty($statusMsg)) {
			return $statusMsg;
		}

		$configFilePath = $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$configFile;

		if (!file_exists($configFilePath) && (touch($configFilePath) === false || !@chmod($configFilePath, 0755))) {
			return sprintf($_ARRLANG['TXT_CANNOT_CREATE_FILE']."<br />", $configFilePath);
		}

		if (!$fp = @fopen($configFilePath, "w")) {
			return str_replace("[FILENAME]", $configFilePath, $_ARRLANG['TXT_CANNOT_OPEN_FILE']."<br />");
		} else {
			if (!@fwrite($fp, $configFileContent)) {
				@fclose($fp);
				return sprintf($_ARRLANG['TXT_CANNOT_CREATE_FILE']."<br />", $configFilePath);
			}
			@fclose($fp);
			return true;
		}
	}

	function createVersionFile() {
		global $versionFile, $_ARRLANG;

		$versionFileContent = $this->_getVersionTemplateFile();
		$versionFilePath = $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].$versionFile;

		if (!file_exists($versionFilePath) && (touch($versionFilePath) === false || !@chmod($versionFilePath, 0755))) {
			return sprintf($_ARRLANG['TXT_CANNOT_CREATE_FILE']."<br />", $versionFilePath);
		}

		if (!$fp = @fopen($versionFilePath, "w")) {
			return str_replace("[FILENAME]", $versionFilePath, $_ARRLANG['TXT_CANNOT_OPEN_FILE']."<br />");
		} else {
			if (!@fwrite($fp, $versionFileContent)) {
				@fclose($fp);
				return sprintf($_ARRLANG['TXT_CANNOT_CREATE_FILE']."<br />", $versionFilePath);
			}
			@fclose($fp);
			return true;
		}
	}

	function getSystemLanguages() {
		global $dbType;

		$statusMsg = "";
		$arrLanguages = array();

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb !== false) {
			$query = "SELECT `id`, `lang`, `name`, `is_default` FROM `".$_SESSION['installer']['config']['dbTablePrefix']."languages` ORDER BY `name` DESC";
			$result = $objDb->Execute($query);
		}

		if ($result) {
			while ($arrLanguage = $result->FetchRow()) {
				array_push($arrLanguages, $arrLanguage);
			}
		}
		return $arrLanguages;
	}

	function createAdminAccount() {
		global $dbType, $arrLanguages, $language, $_ARRLANG;

		$statusMsg = "";
		$userLangId = "";

		foreach ($arrLanguages as $langId => $arrLanguage) {
			if ($language == $arrLanguage['lang']) {
				$userLangId = $langId;
				break;
			}
		}

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb !== false) {
			#$objDb->debug = true;
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."access_users`
						 SET `username` = '".$_SESSION['installer']['account']['username']."',
							 `password` = '".md5($_SESSION['installer']['account']['password'])."',
							 `regdate` = '".time()."',
							 `email` = '".$_SESSION['installer']['account']['email']."',
							 `frontend_lang_id` = '".$userLangId."',
							 `backend_lang_id` = '".$userLangId."',
							 `active` = 1
					   WHERE `id` = 1";
			if ($objDb->Execute($query) !== false) {
                $query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."access_user_profile`
                             SET `firstname` = '".$_SESSION['installer']['sysConfig']['adminName']."',
                                 `lastname` = ''
                           WHERE `user_id` = 1";
                if ($objDb->Execute($query) !== false) {
                    return true;
                }
			}
		}
		return $_ARRLANG['TXT_COULD_NOT_CREATE_ADMIN_ACCOUNT'];
	}

	/**
	* Validate an E-mail address
	*
	* @param  string  unvalidated email string
	* @return boolean
	* @access public
	*/
	function isEmail($email)
    {
        require_once ASCMS_FRAMEWORK_PATH.'/Validator.class.php';
		return FWValidator::isEmail($email);
	}

	function isValidDbPrefix($prefix)
	{
		return preg_match('#^[a-z0-9_]+$#i', $prefix);
	}

	/**
	* set system configuration
	*
	* configure the system
	*
	* @access	public
	*/
	function setSystemConfig() {
		global $_ARRLANG, $_CONFIG, $arrLanguages, $language;

		$userLangId = "";
		foreach ($arrLanguages as $langId => $arrLanguage) {
			if ($language == $arrLanguage['lang']) {
				$userLangId = $langId;
				break;
			}
		}

		$statusMsg = "";

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb === false) {
			return $statusMsg;
		} else {
            // deactivate all languages
            $query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."languages`
                         SET `frontend` = '0', `backend` = '0', `is_default` = 'false'";
            if (!@$objDb->Execute($query)) {
                $statusMsg .= $_ARRLANG['TXT_COULD_NOT_DEACTIVATE_UNUSED_LANGUAGES']."<br />";
            }

            // set active language
            $query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."languages`
                         SET `frontend` = '1', `backend` = '1', `is_default` = 'true' WHERE `id` = ".$userLangId;
            if (!@$objDb->Execute($query)) {
                $statusMsg .= $_ARRLANG['TXT_COULD_NOT_ACTIVATE_DEFAULT_LANGUAGE']."<br />";
            }

			// set admin email
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."settings`
						 SET `setvalue` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'
					   WHERE `setname` = 'coreAdminEmail'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_ADMIN_EMAIL']."<br />";
			}

			// set admin name
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."settings`
						 SET `setvalue` = '".$_SESSION['installer']['sysConfig']['adminName']."'
					   WHERE `setname` = 'coreAdminName'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_ADMIN_NAME']."<br />";
			}

			if (($arrTables = $objDb->MetaTables('TABLES')) === false) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_GATHER_ALL_DATABASE_TABLES']."<br />";
				return $statusMsg;
			}

            // set access emails
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."access_settings`
						 SET `value` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'
					   WHERE `key` = 'notification_address'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_ADMIN_EMAIL']."<br />";
			}
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."access_user_mail`
						 SET `sender_mail` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_ADMIN_EMAIL']."<br />";
			}

			// set newsletter emails
			if (in_array($_SESSION['installer']['config']['dbTablePrefix']."module_newsletter_settings", $arrTables)) {
				$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_newsletter_settings`
							 SET `setvalue` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'
						   WHERE `setname` = 'sender_mail' OR `setname` = 'reply_mail' OR `setname` = 'test_mail'";
				if (!@$objDb->Execute($query)) {
					$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_NEWSLETTER_EMAILS']."<br />";
				}

				// set newsletter name
				$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_newsletter_settings`
							 SET `setvalue` = '".$_SESSION['installer']['sysConfig']['adminName']."'
						   WHERE `setname` = 'sender_name'";
				if (!@$objDb->Execute($query)) {
					$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_NEWSLETTER_SENDER']."<br />";
				}
			}

			// set contact email
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."settings`
						 SET `setvalue` = '".$_SESSION['installer']['sysConfig']['contactEmail']."'
					   WHERE `setname` = 'contactFormEmail'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL']."<br />";
			}

			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_contact_form`
						 SET `mails` = '".$_SESSION['installer']['sysConfig']['contactEmail']."'
					   WHERE `id` = 1";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL']."<br />";
			}

			// set domain url
			if (preg_match('#^https?://#', $_SESSION['installer']['sysConfig']['domainURL'])) {
                $statusMsg .= $_ARRLANG['TXT_SET_VALID_DOMAIN_URL'];
			} else {
				if (substr($_SESSION['installer']['sysConfig']['domainURL'], -1) == '/') {
					$_SESSION['installer']['sysConfig']['domainURL'] = substr($_SESSION['installer']['sysConfig']['domainURL'], 0, -1);
				}

				$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."settings`
							SET `setvalue` = '".$_SESSION['installer']['sysConfig']['domainURL']."'
							WHERE `setname` = 'domainUrl'";
				if (!@$objDb->Execute($query)) {
					$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_DOMAIN_URL']."<br />";
				}
			}

			if (in_array($_SESSION['installer']['config']['dbTablePrefix']."module_shop_config", $arrTables)) {
				// set shop email
				$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_shop_config`
							 SET `value` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'
						   WHERE `name` = 'email' OR `name` = 'confirmation_emails' OR `name` = 'paypal_account_email'";
				if (!@$objDb->Execute($query)) {
					$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL']."<br />";
				}
			}

			if (in_array($_SESSION['installer']['config']['dbTablePrefix']."module_shop_mail_content", $arrTables)) {
				// set shop email
				$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_shop_mail_content`
							 SET `from_mail` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'";
				if (!@$objDb->Execute($query)) {
					$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL']."<br />";
				}
			}

			if (in_array($_SESSION['installer']['config']['dbTablePrefix'].'module_egov_products', $arrTables)) {
				$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_egov_products`
							 SET `product_target_email` = '".$_SESSION['installer']['sysConfig']['adminEmail']."'";
				if (!@$objDb->Execute($query)) {
					$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL']."<br />";
				}
			}

            $_SESSION['installer']['sysConfig']['iid'] = $this->updateCheck();

			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."settings`
						 SET `setvalue` = '".$_SESSION['installer']['sysConfig']['iid']."'
					   WHERE `setname` = 'installationId'";
			if (!@$objDb->Execute($query) || empty($_SESSION['installer']['sysConfig']['iid'])) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_INSTALLATIONID']."<br />";
			}

			/*
			// set rss title
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_news_settings`
						 SET `value` = '".$_SESSION['installer']['sysConfig']['rssTitle']."'
					   WHERE `name` = 'news_feed_title'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_RSS_TITLE']."<br />";
			}

			// set rss description
			$query = "UPDATE `".$_SESSION['installer']['config']['dbTablePrefix']."module_news_settings`
						 SET `value` = '".$_SESSION['installer']['sysConfig']['rssDescription']."'
					   WHERE `name` = 'news_feed_description'";
			if (!@$objDb->Execute($query)) {
				$statusMsg .= $_ARRLANG['TXT_COULD_NOT_SET_RSS_DESCRIPTION']."<br />";
			}
			*/
		}

		if (empty($statusMsg)) {
			return $this->_createSettingsFile();
		} else {
			return $statusMsg;
		}
	}

	/**
	 * Write all settings into the config-file
	 *
	 */
	function _createSettingsFile()
	{
		global $_ARRLANG;

		$objDb = $this->_getDbObject($statusMsg);
		if ($objDb === false) {
			return $statusMsg;
		} else {
			$strSettingsFile = $_SESSION['installer']['config']['documentRoot'].$_SESSION['installer']['config']['offsetPath'].'/config/settings.php';

			if (!file_exists($strSettingsFile) && (touch($strSettingsFile) === false || !@chmod($strSettingsFile, 0755))) {
				return sprintf($_ARRLANG['TXT_SETTINGS_ERROR_WRITABLE'], $strSettingsFile);
			}

			if (!$handleFile = @fopen($strSettingsFile, "w")) {
				return str_replace("[FILENAME]", $strSettingsFile, $_ARRLANG['TXT_CANNOT_OPEN_FILE']."<br />");
			} else {
			//Header & Footer
				$strHeader	= "<?php\n";
				$strHeader .= "/**\n";
				$strHeader .= "* This file is generated by the \"settings\"-menu in your CMS.\n";
				$strHeader .= "* Do not try to edit it manually!\n";
				$strHeader .= "*/\n\n";

				$strFooter = "?>";

			//Get module-names
				$objResult = $objDb->Execute("SELECT id, name FROM `".$_SESSION['installer']['config']['dbTablePrefix']."modules`");
				if ($objResult->RecordCount() > 0) {
					while (!$objResult->EOF) {
						$arrModules[$objResult->fields['id']] = $objResult->fields['name'];
						$objResult->MoveNext();
					}
				}

			//Get values
				$objResult = $objDb->Execute("SELECT setname, setmodule, setvalue FROM `".$_SESSION['installer']['config']['dbTablePrefix']."settings` ORDER BY	setmodule ASC, setname ASC");
				$intMaxLen = 0;
				if ($objResult->RecordCount() > 0) {
					while (!$objResult->EOF) {
						$intMaxLen = (strlen($objResult->fields['setname']) > $intMaxLen) ? strlen($objResult->fields['setname']) : $intMaxLen;
						$arrValues[$objResult->fields['setmodule']][$objResult->fields['setname']] = $objResult->fields['setvalue'];
						$objResult->MoveNext();
					}
				}
				$intMaxLen += strlen('$_CONFIG[\'\']') + 1; //needed for formatted output

			//Write values
				flock($handleFile, LOCK_EX); //set semaphore
				@fwrite($handleFile,$strHeader);

				foreach ($arrValues as $intModule => $arrInner) {
					@fwrite($handleFile,"/**\n");
					@fwrite($handleFile,"* -------------------------------------------------------------------------\n");
					@fwrite($handleFile,"* ".ucfirst($arrModules[$intModule])."\n");
					@fwrite($handleFile,"* -------------------------------------------------------------------------\n");
					@fwrite($handleFile,"*/\n");

					foreach($arrInner as $strName => $strValue) {
						@fwrite($handleFile,sprintf("%-".$intMaxLen."s",'$_CONFIG[\''.$strName.'\']'));
						@fwrite($handleFile,"= ");
						@fwrite($handleFile,(is_numeric($strValue) ? $strValue : '"'.$strValue.'"').";\n");
					}
					@fwrite($handleFile,"\n");
				}

				@fwrite($handleFile,$strFooter);
				flock($handleFile, LOCK_UN);

				fclose($handleFile);

				return true;
			}
		}
	}

	function _checkOpenbaseDirConfig()
	{
		global $_ARRLANG;

		$openbasedir = @ini_get('open_basedir');
		if (!empty($openbasedir)) {
			if ($this->isWindows()) {
				return true;
			} else {
				if (count(preg_grep('#^/tmp$#', array_map('trim', explode(':', $openbasedir))))) {
					return true;
				} else {
					return $_ARRLANG['TXT_OPEN_BASEDIR_TMP_MISSING'];
				}
			}
		} else {
			return true;
		}
	}

	function getFtpDirectoryTree($path) {
		$statusMsg = "";
		$arrDirectories = array();
		$directoryPath = "";

		$arrPaths = explode("/", $path);

		$objFtp = $this->_getFtpObject($statusMsg);
		if ($objFtp === false) {
			return $statusMsg;
		} else {
			for ($directoryId = 0; $directoryId < count($arrPaths); $directoryId++) {
				$arrDirectories[$directoryId] = array();
				$arrDirectoryTree = array();

				if ($directoryId != 0) {
					@ftp_chdir($objFtp, $arrPaths[$directoryId]);
				}
				$directoryPath .= $arrPaths[$directoryId].'/';

				$arrDirectoryTree = $this->_getFilesOfFtpDirectory($objFtp);
				if (!is_array($arrDirectoryTree)) {
					return $arrDirectoryTree;
				}

				foreach ($arrDirectoryTree as $file) {
					if (@ftp_chdir($objFtp, $file)) {
						$arrDirectory = array(
							'name'	=> $file,
							'path'	=> $directoryPath,
							'style'	=> "padding-left: ".($directoryId*15)."px;"
						);
						array_push($arrDirectories[$directoryId], $arrDirectory);
						@ftp_chdir($objFtp, '..');
					}
				}
			}
		}
		return $arrDirectories;
	}

	function updateCheck() {
		global $_CONFIG, $objDb;

        $version = "";
        $ip = "";
        $serverName = "";
        $iid = "";

        if (!isset($_SESSION['installer']['updateCheck']) || !$_SESSION['installer']['updateCheck']) {
	        if (isset($_SESSION['installer']['sysConfig']['domainURL'])) {
                $serverName = $_SESSION['installer']['sysConfig']['domainURL'];
            }
            else if (isset($_SERVER['SERVER_NAME'])) {
	        	$serverName = $_SERVER['SERVER_NAME'];
	        }
	        if (isset($_SERVER['SERVER_ADDR'])) {
	        	$ip = $_SERVER['SERVER_ADDR'];
	        }

			$v = $_CONFIG['coreCmsVersion'] . $_CONFIG['coreCmsStatus'];
	        $url = base64_decode('d3d3LmNvbnRyZXh4LmNvbQ==');
            $file = base64_decode("L3VwZGF0ZWNlbnRlci9pbmRleC5waHA=").'?host='.$serverName.$_SESSION['installer']['config']['offsetPath'].'&ip='.$ip.'&version='.$v.'&edition='.$_CONFIG['coreCmsEdition'];
            $fp = fsockopen($url, 80, $errno, $errstr, 3);
            if ($fp)
            {
                $out = "GET $file HTTP/1.1\r\n";
                $out .= "Host: $url\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                $ret = '';
                while (!feof($fp)) {
                    $ret .= fgets($fp);
                }
                fclose($fp);
                $iid = substr($ret, strpos($ret, "\r\n\r\n") + 4);
                $_SESSION['installer']['updateCheckImage']="";
            } else {
                $_SESSION['installer']['updateCheckImage']="<img src='".$url."' width='1' height='1' />";
            }
	        $_SESSION['installer']['updateCheck'] = true;
            return $iid;
        }
	}

	function getNewestVersion() {
		$xml_parser = @xml_parser_create();
		@xml_set_object($xml_parser,$this);
		@xml_set_element_handler($xml_parser,"_xmlVersionStartTag","_xmlVersionEndTag");
		@xml_set_character_data_handler($xml_parser, "_xmlVersionCharacterData");
		@xml_parse($xml_parser,file_get_contents(base64_decode('aHR0cDovL3d3dy5jb250cmV4eC5jb20vdXBkYXRlY2VudGVyL3ZlcnNpb24ueG1s')));
		return $this->newestVersion;
	}

	function _xmlVersionStartTag($parser,$name,$attrs) {
		global $xmlVersionTag;

		$xmlVersionTag = $name;
	}

	function _xmlVersionCharacterData($parser, $data) {
		global $xmlVersionTag;

		if (empty($this->newestVersion) && $xmlVersionTag == "VERSION") {
			$this->newestVersion = $data;
		}
	}

	function _xmlVersionEndTag($parser,$name) {
	}
}
?>
