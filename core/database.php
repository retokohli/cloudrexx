<?php
/**
 * Database access function(s)
 * @copyright	CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @package     contrexx
 * @subpackage  core
 * @version	    1.0.0
 */

/**
 * Returns the database object.
 *
 * If none was created before, or if {link $newInstance} is true,
 * creates a new database object first.
 * In case of an error, the reference argument $errorMsg is set
 * to the error message.
 * @author  Astalavista Development Team <thun@astalvista.ch>
 * @access	public
 * @version	1.0.0
 * @param   string	$errorMsg     Error message
 * @param   boolean	$newInstance  Force new instance
 * @global  array	              Language array
 * @global  array	              Database configuration
 * @global  ???                   ADODB fetch mode
 * @return  boolean               True on success, false on failure
 * @todo    What datatype is $ADODB_FETCH_MODE?
 */
function getDatabaseObject(&$errorMsg, $newInstance = false)
{
	global $_ARRLANG, $_DBCONFIG, $ADODB_FETCH_MODE;

	static $objDatabase;

	if (is_object($objDatabase) && !$newInstance) {
		return $objDatabase;
	} else {
		// open db connection
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

		$objDb = ADONewConnection($_DBCONFIG['dbType']);
		@$objDb->Connect($_DBCONFIG['host'], $_DBCONFIG['user'], $_DBCONFIG['password'], $_DBCONFIG['database']);

		$errorNo = $objDb->ErrorNo();
		if ($errorNo != 0) {
			if ($errorNo == 1049) {
				$errorMsg .= str_replace("[DATABASE]", $_DBCONFIG['database'], $_ARRLANG['TXT_DATABASE_DOES_NOT_EXISTS']."<br />");
			} else {
				$errorMsg .=  $objDb->ErrorMsg()."<br />";
			}
			unset($objDb);
			return false;
		}

		if ($objDb) {
			$objDb->Execute('SET CHARSET latin1');
			if ($newInstance) {
				return $objDb;
			} else {
				$objDatabase = $objDb;
				return $objDb;
			}
		} else {
			$errorMsg .= $_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER']."<i>&nbsp;(".$objDb->ErrorMsg().")</i><br />";
			unset($objDb);
		}
		return false;
	}
}
?>
