<?php
/**
* Import Export Class
*
* Parent class for the import and export classes
*
* @copyright     CONTREXX CMS - 2005 COMVATION AG
* @author        Comvation Development Team <info@comvation.com>
* @version       v1.0.0
*/
class ImportExport
{
	/**
	 * Type of the data which shall be exported or imported
	 */
	var $type = null;

	/**
	 * Variable with the data class
	 */
	var $dataClass = null;

	/**
	 * Variable with the types
	 */
	var $types = array();

	/**
	 * PHP 5 constructor
	 *
	 */
	function __construct()
	{
		$this->initTypes();

	}

	/**
	 * PHP 4 constructor
	 *
	 * Runs __construct if PHP 4 is used
	 */
	function ImportExport()
	{
		$this->__construct();
	}

	/**
	 * Initialises the typesy
	 *
	 */
	function initTypes()
	{

		$this->types['csv'] = array(
			"name" => "CSV (Comma Separated Values)"
		);

		/*$this->types['xls'] = array(
			"name" => "Excel"
		);*/
	}

	/**
	 * Passes the options to the data lib
	 *
	 * @param array $options Array with all options which shall be passed
	 * @access public
	 */
	function setOptions($options)
	{
		$this->dataClass->setOptions($options);
	}

	/**
	 * Sets a new type
	 *
	 * @param unknown_type $type
	 * @access public
	 */
	function setType($type)
	{
		$this->type = $type;

		switch($this->type) {
			case "csv":
				require_once ASCMS_LIBRARY_PATH."/importexport/lib/csv.class.php";
				$this->dataClass = new CsvLib();
		}
	}

	/**
	 * Returns select entries for the type selection list
	 *
	 * @param unknown_type $standard
	 * @return string List with all option entries
	 */
	function getTypeSelectList($standard=null)
	{
		$retval = "";

		foreach ($this->types as $key => $value) {
			$selected = ($key == $standard) ? "selected=\"selected\"" : "";
			$retval .= "<option value=\"".$key."\" $selected>".$value['name']."</option>";
		}

		return $retval;
	}
}

?>