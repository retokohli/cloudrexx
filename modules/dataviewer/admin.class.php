<?php
/**
 * Class Dataviewer
 */
class Dataviewer {
    public $_objTpl;
    public $_pageTitle;
    public $strErrMessage = '';
    public $strOkMessage = '';

    
    /**
     * PHP5 constructor
     * @global HTML_Template_Sigma
     * @global array $_ARRAYLANG
     */
    function __construct()
    {
       global $_ARRAYLANG, $_CORELANG, $objTemplate;
       
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/dataviewer/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable("CONTENT_NAVIGATION",
            							"<a href='index.php?cmd=dataviewer'>".$_ARRAYLANG['TXT_DATAVIEWER_ADMINISTRATION']."</a>
            							<a href='index.php?cmd=dataviewer&amp;act=new'>".$_ARRAYLANG['TXT_DATAVIEWER_NEW_PROJECT']."</a>
            							<a href='index.php?cmd=dataviewer&amp;act=import'>".$_ARRAYLANG['TXT_DATAVIEWER_IMPORT_DATA']."</a>");
    }

    /**
    * Set the backend page
    *
    * @access public
    * @global HTML_Template_Sigma
    * @global array $_ARRAYLANG
    */
    function getPage() {
        global $objTemplate, $_ARRAYLANG;

        $_GET['act'] = !empty($_GET['act']) ? $_GET['act'] : "";
        $_GET['setstatus'] = !empty($_GET['setstatus']) ? $_GET['setstatus'] : "";
        $_GET['delete'] = !empty($_GET['delete']) ? $_GET['delete'] : "";
        $_GET['projectid'] = !empty($_GET['projectid']) ? $_GET['projectid'] : "";
        $_POST['createProject'] = !empty($_POST['createProject']) ? $_POST['createProject'] : "";
       
        switch ($_GET['act']) {
            case "setfilter":
            	$this->setFilter($_GET['projectid']);
                break;
			case "import":
                $this->importData();
                break;
			case "new":
                $this->newProject();
                break;
            default:
                $this->overview();
            break;
        }
        
		 $objTemplate->setVariable(array(
	            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
	            'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
	            'ADMIN_CONTENT'             => $this->_objTpl->get(),
	            'CONTENT_TITLE'             => $this->_pageTitle,
	        ));
			
	        return $this->_objTpl->get();
	    }
    
	    
    function overview()
    {
        global $_ARRAYLANG, $objDatabase;

        $this->_objTpl->loadTemplateFile('module_dataviewer_overview.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_ADMINISTRATION'];
        
        if ($_GET['setstatus']) {
        	$this->changeStatus($_GET['projectid'], $_GET['setstatus']);
        }
        
        if ($_GET['delete']) {
        	$this->deleteProject($_GET['delete']);
        }
        
        $queryProjects = "SELECT * FROM ".DBPREFIX."module_dataviewer_projects";
		$objResultProjects = $objDatabase->Execute($queryProjects);
		
		$i = 0;	
		while (!$objResultProjects->EOF) {
			$status = $objResultProjects->fields['projectstatus'] == 1 ? "led_green" : "led_red";
			$statusInt = $objResultProjects->fields['projectstatus'] == 1 ? "I" : "A";
			$this->_objTpl->setVariable(array(
				'PROJECTID'	=> $objResultProjects->fields['projectid'],
				'PROJECTNAME'	=> $objResultProjects->fields['projectname'],
				'PROJECT_DESCRIPTION'	=> $objResultProjects->fields['projectdescription'],
				'PROJECTSTATUS'	=> $status,
				'PROJECTSTATUSINT' => $statusInt,
				'ROWCLASS' => ++$i % 2 ? 'row2' : 'row1'
			));
			$this->_objTpl->parse('projectRow');
			$objResultProjects->MoveNext();
		}

        $this->_objTpl->setVariable(array(
            'TXT_PROJECTNAME'        => $_ARRAYLANG['TXT_PROJECTNAME'],
            'TXT_PROJECT_DESCRIPTION'        => $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
            'TXT_FUNCTIONS'        => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_PROJECTSTATUS'        => $_ARRAYLANG['TXT_PROJECTSTATUS'],
        ));

	}
	
	
	/**
	* set projectstatus to 1 or 0 (visible or hidden)
	* @param int $projectid
	* @param int $status
	* @return no return
	*/
	function changeStatus($projectid, $status) {
		global $objDatabase;
		$status = $status == "A" ? 1 : 0;
		$query = "UPDATE ".DBPREFIX."module_dataviewer_projects SET projectstatus = '" . $status . "' WHERE projectid = '" . $projectid . "'";
		$objDatabase->Execute($query);
	}
	
	
	/**
	* deletes the record in dataviewer_projects, dataviewer_filters and dataviewer_projectname table
	* @param int $projectid
	* @return no return
	*/
	function deleteProject($projectid) {
		global $objDatabase;
		$projectname = $this->getProjectName($projectid);
		
		$queryFailed = false;
		
		$deleteProjectTableQuery = "DROP TABLE ".DBPREFIX."module_dataviewer_" . $projectname . ";";
		if(!$objDatabase->Execute($deleteProjectTableQuery)) {
			$this->_objTpl->setVariable(array(
				'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $deleteProjectTableQuery . " konnte nicht ausgeführt werden.<br />"
			));
			$queryFailed = true;
		}
		
		if ($queryFailed == false) {
			$deleteProjectRecordQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_projects WHERE projectid = '" . $projectid . "'";
			if(!$objDatabase->Execute($deleteProjectRecordQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $deleteProjectRecordQuery . " konnte nicht ausgeführt werden."
				));
				$queryFailed = true;
			}	
		}
		
		if ($queryFailed == false) {
			$deleteProjectFilterQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_filters WHERE projectid = '" . $projectid . "'";
			if(!$objDatabase->Execute($deleteProjectFilterQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $deleteProjectFilterQuery . " konnte nicht ausgeführt werden."
				));
				$queryFailed = true;
			}	
		}
		
		if ($queryFailed == false) {
			$this->_objTpl->setVariable(array(
				'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Projekt wurde erfolgreich gelöscht."
			));
		}
	}
	
	
	/**
	* displays the mask for a new project
	* @param no param
	* @return no return
	*/
	function newProject() {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_new_project.html');
		
		$file = !empty($_FILES['file']) ? $_FILES['file'] : "";
		$tempPath = ASCMS_DATAVIEWER_TEMP_PATH;
		$projectname = !empty($_POST['projectname']) ? $_POST['projectname'] : "";
		$projectdescription = !empty($_POST['projectdescription']) ? $_POST['projectdescription'] : "";
		$_POST['continue'] = !empty($_POST['continue']) ? $_POST['continue'] : "";
		$_POST['createProject'] = !empty($_POST['createProject']) ? $_POST['createProject'] : "";
		$selectedColumns = !empty($_POST['columns']) ? $_POST['columns'] : "";
		
		$this->_objTpl->setVariable(array(
			'TXT_CREATE_PROJECT'    => $_ARRAYLANG['TXT_CREATE_PROJECT'],
			'TXT_CONTINUE'        	=> $_ARRAYLANG['TXT_CONTINUE'],
			'TXT_PROJECTNAME'       => $_ARRAYLANG['TXT_PROJECTNAME'],
			'TXT_PROJECT_DESCRIPTION'	=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
			'TXT_SELECTION_FILE'	=> $_ARRAYLANG['TXT_SELECTION_FILE'],
			'POST_PROJECTNAME'  	=> $projectname,
			'POST_PROJECTDESCRIPTION'  	=> $projectdescription,
			'CSS_DISPLAY'			=> "none"
		));
		
		if ($_POST['continue']) {
			if ($this->uploadFile($file)) {
				if($columnsArray = $this->getColumns($file['name'])) {
					
					foreach ($columnsArray as $column) {
						$this->_objTpl->setVariable(array(
							'COLUMN_NAME'	=> $column
						));
						$this->_objTpl->parse('columnOption');
					}
					
					$this->_objTpl->setVariable(array(
						'CSS_DISPLAY'	=> "block"
					));
				}
			} 
		}
		
		if ($_POST['createProject']) {
			$this->createProject($projectname, $projectdescription, $selectedColumns);
		}
	}

	
	/**
	* insert dataviewer_projects record, dataviewer_filters record and create new project table
	* @param var $projectname
	* @param array $selectedColumns
	* @return no return
	*/
	function createProject($projectname, $projectdescription, $selectedColumns) {
		global $objDatabase;
		$insertProjectQueryOK = false;
		$createProjectTableQueryOK = false;
		
		$inputOK = 0;
		
		if ($selectedColumns == "") {
			$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Bitte wählen Sie die gewünschten Spalten aus!"
				));
			$inputOK++;
		}
		
		if ($projectname == "") {
			$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Bitte geben Sie einen Namen ein!"
				));
			$inputOK++;
		}
		
		if ($inputOK == 0) {
			$projectfields = implode(";", $selectedColumns);
		
			$insertProjectQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_projects (projectname, projectdescription, projectstatus, projectfields) VALUES ('" . $projectname . "', '" . $projectdescription . "', '1', '" . $projectfields . "');";
	
			$createProjectTableQuery = "CREATE TABLE ".DBPREFIX."module_dataviewer_".$projectname ." (";
			$fields = "";
	
			foreach ($selectedColumns as $column) {
				$fields .= $column . " VARCHAR( 500 ), ";
			}
			
			$fields .= "country VARCHAR( 10 ));";
			$createProjectTableQuery .= $fields;
			
			if ($objDatabase->Execute($insertProjectQuery) && $objDatabase->Execute($createProjectTableQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Projekt wurde erfolgreich angelegt. Datenbankeintrag wurde erzeugt, Projekttabelle erstellt."
				));
			} else {
				$deleteProjectTableQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_projects WHERE projectname = '".$projectname . "'";
				$objDatabase->Execute($deleteProjectTableQuery);
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Fehler während dem Erstellen des Projektes. Bitte wiederholen! " . $createProjectTableQuery
				));
			}	
		}			
	}
		
	
	/**
	* get columns for selection which fields you want to import
	* @param file $file
	* @return array $columnsArray
	*/
	function getColumns($file) {
		$rows = 1;
		$handler = fopen(ASCMS_DATAVIEWER_TEMP_PATH . $file, "r");
		
		//get columns and fieldvalues
		while(($csvData = fgetcsv($handler, 1000, ";")) !== FALSE) {
			$numberOfFields = count($csvData);
			
			if ($rows == 1) {
				for($i = 0; $i < $numberOfFields; $i++) {
					//sometimes there a just blank fields inside the csv file
					if ($csvData[$i] !== "") {
						$columnsArray[] = $csvData[$i];	
					}
				}	
			} 
			
			$rows++;
		}
		fclose($handler);
		
		return $columnsArray;
	}
	
		
	/**
	* displays the mask for a import data and do the import
	* @return no return
	*/
	function importData() {
			global $_ARRAYLANG, $objDatabase;			
			$this->_objTpl->loadTemplateFile('module_dataviewer_import_data.html');
			
			
			$_POST['startImport'] = !empty($_POST['startImport']) ? $_POST['startImport'] : "";
			$country = !empty($_POST['country']) ? $_POST['country'] : "";
			$projectid = !empty($_POST['projectid']) ? $_POST['projectid'] : "";
			
			$file = !empty($_FILES['file']) ? $_FILES['file'] : "";
			
			//get countries select options
			$query = "SELECT * FROM ".DBPREFIX."lib_country";
			$objResultCountries = $objDatabase->Execute($query);
			
			while (!$objResultCountries->EOF) {
				$this->_objTpl->setVariable(array(
					'COUNTRY_CODE'	=> $objResultCountries->fields['iso_code_2'],
					'COUNTRY_NAME'	=> $objResultCountries->fields['name']
				));
				$this->_objTpl->parse('selectOption');
				$objResultCountries->MoveNext();
			}
			
			//get projects select options
			$query = "SELECT * FROM ".DBPREFIX."module_dataviewer_projects";
			$objResultProjects = $objDatabase->Execute($query);
			
			while (!$objResultProjects->EOF) {
				$this->_objTpl->setVariable(array(
					'PROJECT_ID'	=> $objResultProjects->fields['projectid'],
					'PROJECT_NAME'	=> $objResultProjects->fields['projectname']
				));
				$this->_objTpl->parse('projectOption');
				$objResultProjects->MoveNext();
			}
			
			
			$this->_objTpl->setVariable(array(
				'TXT_DATAVIEWER_IMPORT_DATA'        => $_ARRAYLANG['TXT_DATAVIEWER_IMPORT_DATA'],
				'TXT_CONTINUE'        => $_ARRAYLANG['TXT_CONTINUE'],
				'TXT_PROJECTNAME'        => $_ARRAYLANG['TXT_PROJECTNAME'],
				'TXT_SELECTION_COUNTRY'        => $_ARRAYLANG['TXT_SELECTION_COUNTRY'],
				'TXT_SELECTION_FILE'        => $_ARRAYLANG['TXT_SELECTION_FILE'],
				'TXT_START_IMPORT'        => $_ARRAYLANG['TXT_START_IMPORT'],
				'TXT_CHOOSE_PROJECT'        => $_ARRAYLANG['TXT_CHOOSE_PROJECT']
			));
			
			
			
			if ($_POST['startImport']) {
				
				$projectname = $this->getProjectName($projectid);
				if ($this->checkIfCountryExists($country, $projectname)) {
					$deleteQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_" . $projectname . " WHERE country = '" . $country . "';";
					$objDatabase->Execute($deleteQuery);
				}
				
				if ($this->uploadFile($file)) {
					
					$dataArray = $this->createDataArray($file);
					
					$columns = $this->getColumnsFromProject($projectid); //for sql query (a, b, c)
					$columnsArray = $this->getColumnsArrayFromProject($projectid); //zum vergleichen mit in_array funktion ([0] = a, [1] = b, [2] = c)
				
					$projectname = $this->getProjectName($projectid);
					
										
					foreach ($dataArray as $row) {
						$list = array();
						foreach ($row as $columnName => $value) {
							if (in_array($columnName, $columnsArray)) {
									$value = addslashes($value);
									$value = htmlentities($value);
									$list[] = $value;
								}
							}
						$valuesArray[] = "'".join("', '", $list)."'";
					}

					
					foreach ($valuesArray as $value) {
						$insertDataQuery = "INSERT INTO ".DBPREFIX."module_dataviewer_" . $projectname . " (" . $columns . ", country) VALUES (" . $value . ", '" . $country . "');";
						if($objDatabase->Execute($insertDataQuery)) {
							$this->_objTpl->setVariable(array(
								'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Daten wurden erfolgreich importiert."
							));
						} else {
							$this->_objTpl->setVariable(array(
								'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Fehler beim importieren der Daten!" . $insertDataQuery
							));
						}
					}
				}

			}
			
		
	}
	
	/**
	* checks if for the current project allready entries for the country exists
	* 
	* @param int $country
	* @param int $projectname
	* @return boolean
	*/
	function checkIfCountryExists($country, $projectname) {
		global $objDatabase;
		$query = "SELECT country FROM ".DBPREFIX."module_dataviewer_" . $projectname . " WHERE country = '" . $country . "';";
		$objResult = $objDatabase->Execute($query);
		if($objResult->RecordCount() > 0) {
			return true;
		} else {
			return false;	
		}
	}
	
	/**
	* returns selected project columns 
	* @param int $projectid
	* @return var $columns
	*/	
	function getColumnsFromProject($projectid) {
		global $objDatabase;
		$query = "SELECT projectfields FROM ".DBPREFIX."module_dataviewer_projects WHERE projectid = '" . $projectid . "';";
		$objResult = $objDatabase->Execute($query);
		$columns = str_replace(";", ", ", $objResult->fields['projectfields']);
		
		return $columns;
	}
	
	
	/**
	* returns selected project columns 
	* @param int $projectid
	* @return var $columns
	*/	
	function getColumnsArrayFromProject($projectid) {
		global $objDatabase;
		$query = "SELECT projectfields FROM ".DBPREFIX."module_dataviewer_projects WHERE projectid = '" . $projectid . "';";
		$objResult = $objDatabase->Execute($query);
		$columnsArray = explode(";", $objResult->fields['projectfields']);
		
		return $columnsArray;
	}
	
	/**
	* get projectname by projectid
	* @param int $projectid
	* @return var $projectname
	*/
	function getProjectName($projectid) {
		global $objDatabase;
		$query = "SELECT projectname FROM ".DBPREFIX."module_dataviewer_projects WHERE projectid = '" . $projectid . "';";
		$objResult = $objDatabase->Execute($query);
		$projectname= $objResult->fields['projectname'];
		
		return $projectname;
	}
	
	
	/**
	* uploads selected CSV file
	* @param file $file
	* @return boolean
	*/
	function uploadFile($file) {
		$tempPath = ASCMS_DATAVIEWER_TEMP_PATH;
		if ($file['type'] == "text/comma-separated-values") {
			if (move_uploaded_file($file['tmp_name'], $tempPath . $file['name'])) {
				return true;
			} else {
				return false;
			}
		} else {
			$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Falsches Dateiformat!"
			));
			return false;
		}					
	}

		
	/**
	* creates array with all fields and values in CSV file.
	* @param int $projectid
	* @param int $status
	* @return array $combinedArray
	*/
	function createDataArray($file) {
			$rows = 1;
			$handler = fopen(ASCMS_DATAVIEWER_TEMP_PATH . $file['name'], "r");
			
			//get columns and fieldvalues
			while(($csvData = fgetcsv($handler, 1000, ";")) !== FALSE) {
				$numberOfFields = count($csvData);
								
				if ($rows == 1) {
					for($i = 0; $i < $numberOfFields; $i++) {
						if($csvData[$i] !== "") {					//sometimes there a just blank fields inside the csv file
							$columnsArray[] = $csvData[$i];
							$csvData[$i];
						}
					}	
				} else {
					for($i = 0; $i < $numberOfFields; $i++) {
						$valuesArray[] = $csvData[$i];
						$csvData[$i];
					}
				}
				
				$rows++;
				
			}
			fclose($handler);
			
			
			//create an array, colums are the keys and fields are the values
			//returns array like this = $combinedArray[0] = array("spalte1" => value, "spalte2" => value, "spalte3" => value);
			//							$combinedArray[1] = array("spalte1" => value2, "spalte2" => value2, "spalte3" => value2);
			$numberOfColumns = count($columnsArray);
			$numberOfValues = count($valuesArray);
			$numberOfRuns = $numberOfValues / $numberOfColumns;
			$runs = 1;
			
			while ($runs <= $numberOfRuns) {
				$i = 0;	
	
				while ($i < $numberOfColumns) {
					$dataArray[$columnsArray[$i]] = $valuesArray[0];
					array_shift($valuesArray);
					$i++;
					if ($i == $numberOfColumns) {
						break;
					}
				}			
				
				$combinedArray[$runs] = $dataArray;
				$runs++;
			}
			
			return $combinedArray;
		}
				
	
	/**
	* set the filter fields for the choosen project (displays just the fields who got selected in the create project step)
	* @param int $projectid
	* @return no return
	*/
	function setFilter($projectid) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_set_filter.html');
		$projectname = $this->getProjectName($projectid);
		$_POST['columns'] = !empty($_POST['columns']) ? $_POST['columns'] : "";
		$_POST['saveFilter'] = !empty($_POST['saveFilter']) ? $_POST['saveFilter'] : "";
		$filterfields = "";
		
		if (!empty($_POST['columns'])) {
			$filterfields = implode(";", $_POST['columns']);	
		}
		
					
		if ($_POST['saveFilter']) {
			$selectQuery = "SELECT projectid FROM ".DBPREFIX."module_dataviewer_filters WHERE projectid = '" . $projectid . "';";
			$objResult = $objDatabase->Execute($selectQuery);
			
			if($objResult->RecordCount() > 0) {
				$updateQuery = "UPDATE ".DBPREFIX."module_dataviewer_filters SET fields = '" . $filterfields . "' WHERE projectid = '" . $projectid . "';";	
				
				if($objDatabase->Execute($updateQuery)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Filter wurde erfolgreich geändert."
					));		
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Filter konnte nicht geändert werden!"
					));		
				}
			} else {
				$insertQuery = "INSERT INTO ".DBPREFIX."module_dataviewer_filters (projectid, fields) VALUES ('" . $projectid . "', '" . $filterfields . "');";	
				
				if($objDatabase->Execute($insertQuery)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Filter wurde erfolgreich erstellt."
					));		
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Filter konnte nicht erstellt werden!"
					));		
				}
			}		
		}
		
		$this->_objTpl->setVariable(array(
			'TXT_SAVE_FILTER'        => $_ARRAYLANG['TXT_SAVE_FILTER'],
			'TXT_CREATE_FILTER_FOR'        => $_ARRAYLANG['TXT_CREATE_FILTER_FOR'],
			'PROJECTNAME'        => $projectname,
			'PROJECTID'        => $projectid
		));		
		
		
		$columnsArray = $this->getColumnsArrayFromProject($projectid);
		
		foreach ($columnsArray as $column) {
			$this->_objTpl->setVariable(array(
				'COLUMN_NAME'	=> $column
			));
			$this->_objTpl->parse('columnOption');
		}
	}
		



}

?>
