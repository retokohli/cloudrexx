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
            							<a href='index.php?cmd=dataviewer&act=new'>".$_ARRAYLANG['TXT_DATAVIEWER_NEW_PROJECT']."</a>
            							<a href='index.php?cmd=dataviewer&act=settings'>".$_ARRAYLANG['TXT_DATAVIEWER_SETTINGS']."</a>");
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
        $_POST['createProject'] = !empty($_POST['createProject']) ? $_POST['createProject'] : "";
        $projectname = !empty($_POST['projectname']) ? $_POST['projectname'] : "";

        
        $file = !empty($_FILES['file']) ? $_FILES['file'] : "";
        
        
        
//        if ($_POST['import']) {
//        	$this->startImport($file);
//        }
        
        switch ($_GET['act']) {
            case "settings":
                $this->createDataArray();
                break;
			case "new":
				if ($_POST['createProject']) {$this->createProject($projectname);}
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
			
			while (!$objResultProjects->EOF) {
				$status = $objResultProjects->fields['projectstatus'] == 1 ? "led_green" : "led_red";
				$statusInt = $objResultProjects->fields['projectstatus'] == 1 ? "I" : "A";
				$this->_objTpl->setVariable(array(
					'PROJECTID'	=> $objResultProjects->fields['projectid'],
					'PROJECTNAME'	=> $objResultProjects->fields['projectname'],
					'PROJECTSTATUS'	=> $status,
					'PROJECTSTATUSINT' => $statusInt 
				));
				$this->_objTpl->parse('projectRow');
				$objResultProjects->MoveNext();
			}

        $this->_objTpl->setVariable(array(
            'TXT_PROJECTNAME'        => $_ARRAYLANG['TXT_PROJECTNAME'],
            'TXT_FUNCTIONS'        => $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_PROJECTSTATUS'        => $_ARRAYLANG['TXT_PROJECTSTATUS'],
        ));

	}
	
	
	function changeStatus($projectid, $status) {
		global $objDatabase;
		$status = $status == "A" ? 1 : 0;
		$query = "UPDATE ".DBPREFIX."module_dataviewer_projects SET projectstatus = '" . $status . "' WHERE projectid = '" . $projectid . "'";
		$objDatabase->Execute($query);
	}
	
	
	//deletes JUST the record in dataviewer_projects table
	function deleteProject($projectid) {
		global $objDatabase;
		$query = "DELETE FROM ".DBPREFIX."module_dataviewer_projects WHERE projectid = '" . $projectid . "'";
		$objDatabase->Execute($query);
	}
	
	
	//displays the mask for a new project
	function newProject() {
			global $_ARRAYLANG, $objDatabase;
			
			$this->_objTpl->loadTemplateFile('module_dataviewer_new_project.html');
			$this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_ADMINISTRATION'];
			
			$query = "	SELECT * FROM ".DBPREFIX."lib_country";
			$objResultCountries = $objDatabase->Execute($query);
			
			while (!$objResultCountries->EOF) {
				$this->_objTpl->setVariable(array(
					'COUNTRY_CODE'	=> $objResultCountries->fields['iso_code_2'],
					'COUNTRY_NAME'	=> $objResultCountries->fields['name']
				));
				$this->_objTpl->parse('selectOption');
				$objResultCountries->MoveNext();
			}
			
			$this->_objTpl->setVariable(array(
				'TXT_SELECTION_FILE'        => $_ARRAYLANG['TXT_SELECTION_FILE'],
				'TXT_SELECTION_COUNTRY'        => $_ARRAYLANG['TXT_SELECTION_COUNTRY'],
				'TXT_CONTINUE'        => $_ARRAYLANG['TXT_CONTINUE'],
				'TXT_START_IMPORT'        => $_ARRAYLANG['TXT_START_IMPORT'],
				'TXT_CREATE_PROJECT'        => $_ARRAYLANG['TXT_CREATE_PROJECT'],
				'TXT_PROJECTNAME'        => $_ARRAYLANG['TXT_PROJECTNAME']
				
			));
			
		}

	
	function startImport($file) {
			if ($file !== "") {
        		if($this->uploadFile($file) == true) {
        			$this->createTable($file['name']);	
        		}
        	}
		}	
		
	
	//uploads the selected file
	function uploadFile($file) {
			$tempPath = ASCMS_DATAVIEWER_TEMP_PATH;
			if (move_uploaded_file($file['tmp_name'], $tempPath . $file['name'])) {
				return true;
			} else {
				return false;
			}			
		}

		
	//creates dataviewer_projects record and dataviewer_projectname table
	function createProject($projectname) {
			global $objDatabase;
			$createTableQueryOK = false;
			$insertProjectQueryOK = false;
			$createTableQuery = "CREATE TABLE ".DBPREFIX."module_dataviewer_".$projectname ." (test VARCHAR( 100 ));";
			
			if ($objDatabase->Execute($createTableQuery)) {
				$createTableQueryOK = true;
			}
			
			if ($createTableQueryOK) {
				$insertProjectQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_projects (projectname, projectstatus) VALUES ('" . $projectname . "', '1');";
				if ($objDatabase->Execute($insertProjectQuery)) {
					$insertProjectQueryOK = true;
				}
			}
			
			if ($createTableQuery == true && $insertProjectQueryOK == true) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Projekt wurde erfolgreich angelegt. Datenbanktabelle wurde erzeugt."
				));
			} else {
				$objDatabase->Execute("DROP TABLE contrexx_module_dataviewer_" . $projectname . "");
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Fehler beim anlegen des Projektes. Bitte wiederholen!"
				));	
			}	
		}
		
		
		
	//returns array like this = $combinedArray[0] = array("spalte1" => value, "spalte2" => value, "spalte3" => value);
	//							$combinedArray[1] = array("spalte1" => value2, "spalte2" => value2, "spalte3" => value2);
	function createDataArray() {
			$rows = 1;
			$handler = fopen(ASCMS_DATAVIEWER_TEMP_PATH . "test.csv", "r");
			
			//get columns and fieldvalues
			while(($csvData = fgetcsv($handler, 1000, ";")) !== FALSE) {
				$numberOfFields = count($csvData);
				
				if ($rows == 1) {
					for($i = 0; $i < $numberOfFields; $i++) {
						$columnsArray[] = $csvData[$i];
					}	
				} else {
					for($i = 0; $i < $numberOfFields; $i++) {
						$valuesArray[] = $csvData[$i];
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
		
	
	//get columns for selection (return array)
	function getColumns($file) {
		$rows = 1;
		$handler = fopen(ASCMS_DATAVIEWER_TEMP_PATH . "test.csv", "r");
		
		//get columns and fieldvalues
		while(($csvData = fgetcsv($handler, 1000, ";")) !== FALSE) {
			$numberOfFields = count($csvData);
			
			if ($rows == 1) {
				for($i = 0; $i < $numberOfFields; $i++) {
					$columnsArray[] = $csvData[$i];
				}	
			} 
			
			$rows++;
		}
		fclose($handler);
		return $columnsArray;
	}
		

	
	
	
	
	
	
	
	function createTable($file) {
			$tempPath = ASCMS_MODULE_PATH.'/dataviewer/temp/';
			
			$row = 1;
			$handler = fopen($tempPath . $file, "r");
			
			while(($data = fgetcsv($handler, 1000, ";")) !== FALSE) {
				$numberOfFields = count($data);
				
				if ($row == 1) {
					$i = 0;
					while ($i < $numberOfFields) {
						$tableFieldsArray[$i] = $data[$i];
			//			$tableFieldsArray[$data[$i]] = "";
						$i++;
					}	
				} 	
				
				$row++;
			}
			
			fclose($handler);
			
			$query = "CREATE TABLE contrexx_dataviewer_tes2222t (";
			foreach ($tableFieldsArray as $field) {
				$query .= $field . " VARCHAR( 100 ),";				
			}
			
			echo $query = substr($query, 0, strlen($query)-1) . ");";

			print "<pre>";
			print_r($tableFieldsArray);
			}




















}

?>
