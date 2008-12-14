<?php
/**
 * Class Dataviewer
 * @author Ben Fischer
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
            							"<a href='index.php?cmd=dataviewer'>".$_ARRAYLANG['TXT_DATAVIEWER_OVERVIEW']."</a>
            							<a href='index.php?cmd=dataviewer&amp;act=new'>".$_ARRAYLANG['TXT_DATAVIEWER_NEW_PROJECT']."</a>");
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
		
		switch ($_GET['act']) {
			case "edit":
				$this->editProject($_GET['id']);
				break;
			case "new":
				$this->newProject();
				break;
			case "filter":
				$this->filter($_GET['id']);
				break;
			case "placeholder":
				$this->placeholder($_GET['id']);
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
    
	   
	/**
	 * handles the overview view and actions
	 *
	 */
    function overview() {
        global $_ARRAYLANG, $objDatabase;
        $this->_objTpl->loadTemplateFile('module_dataviewer_overview.html');
        $this->_pageTitle = $_ARRAYLANG['TXT_DATAVIEWER_ADMINISTRATION'];
        
        $_GET['setstatus'] = !empty($_GET['setstatus']) ? $_GET['setstatus'] : "";
        $_GET['delete']    = !empty($_GET['delete']) ? $_GET['delete'] : "";
        
        //set status
        if ($_GET['setstatus']) {
        	$_GET['setstatus'] = $_GET['setstatus'] == "y" ? 1 : 0;
        	$this->changeStatus($_GET['id'], $_GET['setstatus']);
        }
        
        //delete project
        if ($_GET['delete']) {
        	if($this->deleteProject($_GET['delete'])) {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strOkMessage = "Projekt wurde erfolgreich gelöscht."
				));	
        	} else {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strErrMessage = "Projekt konnte nicht gelöscht werden!"
				));	
        	}
        }
        
        
        $queryProjects     = "SELECT * FROM ".DBPREFIX."module_dataviewer_projects";
		$objResultProjects = $objDatabase->Execute($queryProjects);
		
		$i = 0;	
		while (!$objResultProjects->EOF) {
			$this->_objTpl->setVariable(array(
				'ID'			=> $objResultProjects->fields['id'],
				'STATUS'		=> $objResultProjects->fields['status'] == 1 ? '<a href="index.php?cmd=dataviewer&amp;id=' . $objResultProjects->fields['id']. '&amp;setstatus=n"><img border="0" src="../cadmin/images/icons/led_green.gif" /></a>' : '<a href="index.php?cmd=dataviewer&amp;id=' . $objResultProjects->fields['id']. '&amp;setstatus=y"><img border="0" src="../cadmin/images/icons/led_red.gif" /></a>',
				'NAME'			=> $objResultProjects->fields['name'],
				'DESCRIPTION' 	=> $objResultProjects->fields['description'],
				'LANGUAGE' 		=> $objResultProjects->fields['language'],
				'COUNTRYBASED' 	=> $objResultProjects->fields['countrybased'] == 1 ? '<img src="../images/modules/dataviewer/thumb_up.gif" alt="countrybasedY">' : '<img src="../images/modules/dataviewer/thumb_down.gif" alt="countrybasedN">',
				'FILTER' 		=> $objResultProjects->fields['filters'],
				'PREVIEW' 		=> $this->createPreview($objResultProjects->fields['name']),
				'ROWCLASS' 		=> ($i % 2 == 0) ? 'row1' : 'row2'
			));
			
			$i++;
			$this->_objTpl->parse('projectRow');
			$objResultProjects->MoveNext();
		}

        $this->_objTpl->setVariable(array(
            'TXT_PROJECTNAME'        	=> $_ARRAYLANG['TXT_PROJECTNAME'],
            'TXT_PROJECT_DESCRIPTION' 	=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
            'TXT_FUNCTIONS'        		=> $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_PROJECTSTATUS'        	=> $_ARRAYLANG['TXT_PROJECTSTATUS'],
        ));
	}

	
	/**
	 * creates the preview in the overview mask
	 *
	 * @param string $name
	 * @return string $xhtml
	 */
	function createPreview($name) {
		global $objDatabase;
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$name);
		
		$xhtml = '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>';
		foreach ($columns as $column) {
			if ($column !== "country") {
				$xhtml .= '<td><b>' . $column . '</b></td>';	
				$blindText .= '<td>Lorem ipsum dolor</td>';	
			}
		}
		$xhtml .= '<tr>' . $blindText . '</tr>';
		$xhtml .= '</tr></table>';
	
		return $xhtml;
	}


	/**
	* handles the view and actions for a new project
	* @param no param
	* @return no return
	*/
	function newProject() {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_new_project.html');
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
		
		//SAVE
		if ($_POST['save']) {
			$name 			= !empty($_POST['name']) ? $_POST['name'] : "";
			$language 		= !empty($_POST['language']) ? $_POST['language'] : "";
			$description 	= !empty($_POST['description']) ? $_POST['description'] : "";
			
			$countryBased 	= !empty($_POST['countryBased']) ? $_POST['countryBased'] : "";
			$countryBased 	= $countryBased == "y" ? 1 : 0;
			
			$status 		= !empty($_POST['status']) ? $_POST['status'] : "";
			$status 		= $status == "y" ? 1 : 0;
			
			$columns 		= !empty($_POST['column']) ? $_POST['column'] : "";
						
			//input check
			$error = false;
			$errorMessage = "";
			
			if ($name == "") {
				$errorMessage = "Name, ";
				$error = true;
			}
			
			if ($language == "") {
				$errorMessage .= "Frontend Sprache, ";
				$error = true;
			}
			
			if ($description == "") {
				$errorMessage .= "Beschreibung, ";
				$error = true;
			}
			
						
			
			//delete last ","
			$errorMessage = substr($errorMessage, 0, strlen($errorMessage)-2);
			
			if ($error) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 			=> $this->strErrMessage = "Bitte kontrollieren Sie folgende Felder:<br />" . $errorMessage,
					'POST_NAME' 						=> $name,
					'POST_DESCRIPTION' 					=> $description,
					'POST_DESCRIPTION_COUNTRYBASED_Y' 	=> $countryBased == 1 ? 'checked="checked"' : "",
					'POST_DESCRIPTION_COUNTRYBASED_N' 	=> $countryBased == 0 ? 'checked="checked"' : "",
					'POST_DESCRIPTION_STATUS_Y' 		=> $status == 1 ? 'checked="checked"' : "",
					'POST_DESCRIPTION_STATUS_N' 		=> $status == 0 ? 'checked="checked"' : ""
				));	
			}
			
			if (!$error) {
				//create projects query
				$insertProjectQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_projects 
											(name, 
											description, 
											status, 
											language,
											countrybased)
										VALUES
											('" . $name . "',
											'" . $description . "',
											'" . $status . "',
											'1',
											'" . $countryBased  . "');";
				
				//create CREATE query
				
				$createProjectQuery = "CREATE TABLE ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($name) ." (";
				
				$columnsString = "";
				foreach ($columns as $column) {
					$columnsString .= $column . " VARCHAR(250), ";
				}
				
				if ($countryBased == 1) {
					$columnsString .= "country VARCHAR(10), ";
				}
				
				//delete last ","
				$columnsString       = substr($columnsString, 0, strlen($columnsString)-2);
				$createProjectQuery .= $columnsString . ")";
				
				if($objDatabase->Execute($insertProjectQuery) && $objDatabase->Execute($createProjectQuery)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Projekt wurde erfolgreich erstellt."
					));	
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Projekt konnte nicht erstellt werden!<br />" . $insertProjectQuery . "<br />" . $createProjectQuery
					));	
				}
			}
		}
	}
	
	
	/**
	 * replaces whitspaces and formats to lowercase
	 *
	 * @param string $input
	 * @return string $input
	 */
	function makeInputDBvalid($input) {
		$input = str_replace(" ", "_", $input);
		$input = strtolower($input);
		return $input;
	}

	
	/**
	 * handles the edit view and edit actions
	 *
	 * @param int $id
	 */
	function editProject($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_edit_project.html');
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
		
		
		//EDIT
		$query 		= "SELECT * FROM " .DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult 	= $objDatabase->Execute($query);
		
		$this->_objTpl->setVariable(array(
				'POST_NAME' 						=> $objResult->fields['name'],
				'POST_DESCRIPTION' 					=> $objResult->fields['description'],
				'POST_DESCRIPTION_COUNTRYBASED_Y' 	=> $objResult->fields['countrybased'] == 1 ? 'checked="checked"' : "",
				'POST_DESCRIPTION_COUNTRYBASED_N' 	=> $objResult->fields['countrybased'] == 0 ? 'checked="checked"' : "",
				'POST_DESCRIPTION_STATUS_Y' 		=> $objResult->fields['status'] == 1 ? 'checked="checked"' : "",
				'POST_DESCRIPTION_STATUS_N' 		=> $objResult->fields['status'] == 0 ? 'checked="checked"' : "",
				'POST_ID' 							=> $objResult->fields['id']
			));	
		
		
		//UPDATE
		if ($_POST['save']) {
			$name 			= !empty($_POST['name']) ? $_POST['name'] : "";
			$nameDBvalid	= $this->makeInputDBvalid($name);
			
			$language 		= !empty($_POST['language']) ? $_POST['language'] : "";
			$description 	= !empty($_POST['description']) ? $_POST['description'] : "";
			$id			 	= !empty($_POST['id']) ? $_POST['id'] : "";
			
			$countryBased 	= !empty($_POST['countryBased']) ? $_POST['countryBased'] : "";
			$countryBased 	= $countryBased == "y" ? 1 : 0;
			
			$status 		= !empty($_POST['status']) ? $_POST['status'] : "";
			$status 		= $status == "y" ? 1 : 0;
			
			$columns 		= !empty($_POST['column']) ? $_POST['column'] : "";
						
			//input check
			$error = false;
			$errorMessage = "";
			
			if ($name == "") {
				$errorMessage = "Name, ";
				$error = true;
			}
			
			if ($language == "") {
				$errorMessage .= "Frontend Sprache, ";
				$error = true;
			}
			
			if ($description == "") {
				$errorMessage .= "Beschreibung, ";
				$error = true;
			}
						
			
			//delete last ","
			$errorMessage = substr($errorMessage, 0, strlen($errorMessage)-2);
			
			if ($error) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 			=> $this->strErrMessage = "Bitte kontrollieren Sie folgende Felder:<br />" . $errorMessage,
					'POST_NAME' 						=> $name,
					'POST_DESCRIPTION' 					=> $description,
					'POST_DESCRIPTION_COUNTRYBASED_Y' 	=> $countryBased == 1 ? 'checked="checked"' : "",
					'POST_DESCRIPTION_COUNTRYBASED_N' 	=> $countryBased == 0 ? 'checked="checked"' : "",
					'POST_DESCRIPTION_STATUS_Y' 		=> $status == 1 ? 'checked="checked"' : "",
					'POST_DESCRIPTION_STATUS_N' 		=> $status == 0 ? 'checked="checked"' : "",
					'POST_ID' 							=> $id
				));	
			}
			
			if (!$error) {
				//ERASE EXISTINGS PROJECT
				$dropTableQuery = "DROP TABLE ".DBPREFIX."module_dataviewer_". $this->makeInputDBvalid($this->getProjectName($id));
								
				//DATAVIEWER_PROJECTS RECORD
				$updateProjectsQuery = "	UPDATE ".DBPREFIX."module_dataviewer_projects
										SET
											name = '" . $name . "',
											description = '" . $description . "',
											status = '" . $status . "',
											language = '1',
											countrybased = '" . $countryBased  . "'
										WHERE
											id = '" . $id . "'";
								
				//CREATE NEW CREATE TABLE QUERY
				$createProjectQuery = "CREATE TABLE ".DBPREFIX."module_dataviewer_".$nameDBvalid ." (";
				
				$columnsString = "";
				foreach ($columns as $column) {
					$columnsString .= $column . " VARCHAR(250), ";
				}
				
				if ($countryBased == 1) {
					$columnsString .= "country VARCHAR(10), ";
				}
				
				//delete last ","
				$columnsString       = substr($columnsString, 0, strlen($columnsString)-2);
				$createProjectQuery .= $columnsString . ")";
				
				if($objDatabase->Execute($dropTableQuery) && $objDatabase->Execute($updateProjectsQuery) && $objDatabase->Execute($createProjectQuery)) {
						$this->_objTpl->setVariable(array(
							'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Projekt wurde erfolgreich geupdatet."
						));	
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Projekt konnte nicht geupdatet werden!<br />" . $dropTableQuery . "<br />" . $updateProjectsQuery . "<br />" . $createProjectQuery
					));	
				}					
			}
		}
	}
	
	
	/**
	 * sets the status to 1 or 0 (visible or hidden)
	 * 
	 * @param int $id
	 * @param int $status
	 * @return no return
	 */
	function changeStatus($id, $status) {
		global $objDatabase;
		$query = "UPDATE ".DBPREFIX."module_dataviewer_projects SET status = '" . $status . "' WHERE id = '" . $id . "'";
		$objDatabase->Execute($query);
	}
	
	
	/**
	 * gets the projectname by projectid
	 * 
	 * @param int $id
	 * @return var $projectname
	 */
	function getProjectName($id) {
		global $objDatabase;
		$query     = "SELECT name FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "';";
		$objResult = $objDatabase->Execute($query);
		
		return $objResult->fields['name'];
	}
	
	
	/**
	 * handles the filter view and filter actions
	 * 
	 * @param int $id
	 * @return no return
	 */
	function filter($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_set_filter.html');
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
				
		//UPDATE
		if ($_POST['save']) {
			$filters = !empty($_POST['filters']) ? $_POST['filters'] : "";
			$id = !empty($_GET['id']) ? $_GET['id'] : "";
			
			$filtersString = implode(";", $filters);
			$filtersString = str_replace("noColumn", "", $filtersString);
			
			$updateFiltersQuery = "UPDATE ".DBPREFIX."module_dataviewer_projects SET filters = '" . $filtersString . "' WHERE id = '" . $id . "'";				
			
			if($objDatabase->Execute($updateFiltersQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Filter wurden erfolgreich geupdatet."
				));
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Filter konnten nicht geupdatet werden!<br />" . $updateFiltersQuery
				));
			}	
		} 
		
		
		//get all columns
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
		$numberOfColumns = count($columns);
		
		$currentFilterQuery = "SELECT filters FROM " . DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult = $objDatabase->Execute($currentFilterQuery);
		$filters = explode(";", $objResult->fields['filters']);
		
		$i = 0;
		while ($numberOfColumns > $i) {
			foreach ($columns as $column) {
				$this->_objTpl->setVariable(array(
					'COLUMN' 	=> $column,
					'SELECTED' 	=> $column == $filters[$i] ? 'selected="selected"' : ""
				));		
				$this->_objTpl->parse('columnsRow');
			}
		
			$this->_objTpl->setVariable(array(
				'FILTER'	=> "Filter " . ($i == 0 ? "1" : $i+1),
				'ROWCLASS' 		=> ($i % 2 == 0) ? 'row1' : 'row2'
			));		
			$this->_objTpl->parse('filterRow');
			$i++;
		}	
		
		$this->_objTpl->setVariable(array(
				'ID'	=> $id
			));		
	}
		
	
	/**
	 * handles the placeholder view and placeholder actions
	 * 
	 * @param int $id
	 * @return no return
	 */
	function placeholder($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_placeholders.html');
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
				
		//UPDATE
		if ($_POST['save']) {
			$placeholders = !empty($_POST['placeholders']) ? $_POST['placeholders'] : "";
			$id = !empty($_GET['id']) ? $_GET['id'] : "";
			
			
			foreach ($placeholders as $key => $placeholder) {
				$updatePlaceholdersQuery[] = "UPDATE ".DBPREFIX."module_dataviewer_placeholders SET `column` = '" . $placeholder . "' WHERE `id` = " . $key . " AND `projectid` = " . $id . "";	
			}
			
			$error = false;
			$queryForDisplaying = "";
			foreach ($updatePlaceholdersQuery as $query) {
				if (!$objDatabase->Execute($query)) {
					$error = true;
				}
				$queryForDisplaying .= $query . "<br />";
			}
			
			if ($error) {
				$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Platzhalter konnten nicht geupdatet werden!<br />" . $queryForDisplaying
					));	
			} else {
				$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Platzhalter wurde erfolgreich geupdatet."
					));	
			}
		}
			
			
		//get all columns
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
				
		$getPlaceholdersQuery = "SELECT * FROM " . DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" . $id . "'";
		$objPlaceholdersResult = $objDatabase->Execute($getPlaceholdersQuery);
		
		$i = 0;
		while (!$objPlaceholdersResult->EOF) {
			foreach ($columns as $column) {
				$this->_objTpl->setVariable(array(
					'COLUMN' 	=> $column,
					'SELECTED' 	=>  $column == $objPlaceholdersResult->fields['column'] ? 'selected="selected"' : ""
				));
				$this->_objTpl->parse('columnsRow');
			}
			
			$objPlaceholdersResult->MoveNext();
			
			$this->_objTpl->setVariable(array(
				'PLACEHOLDER'	=> "Platzhalter " . ($i == 0 ? "1" : $i+1),
				'ROWCLASS' 		=> ($i % 2 == 0) ? 'row1' : 'row2'
			));		
			$this->_objTpl->parse('placeholderRow');
			$i++;
		}	
			
		$this->_objTpl->setVariable(array(
			'ID'	=> $id
		));		
	}
	
	
	/**
	 * prepares string  for db insert.
	 * deletes the last delemiters
	 *
	 * @param string $filtersString
	 * @return string $filtersString
	 */
	function deleteDelemiter($filtersString) {
		echo "INPUT ==> " . $filtersString . "<br>";
		if (substr($filtersString, strlen($filtersString)-1) !== ";") {
		echo "RETURN";
		return "fiker";
		} else {
		$filtersString = substr($filtersString, 0, strlen($filtersString)-1);
		echo "-->" . $filtersString . "<--<br/>";
		$this->deleteDelemiter($filtersString);
		}
	}
	
		
	/**
	* deletes the record in dataviewer_projects and the project table
	* 
	* @param int $id
	* @return boolean
	*/
	function deleteProject($id) {
		global $objDatabase;
		
		$deleteProjectTableQuery = "DROP TABLE ".DBPREFIX."module_dataviewer_" . $this->getProjectName($id);
		$deleteProjectRecordQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		
		if ($objDatabase->Execute($deleteProjectTableQuery) && $objDatabase->Execute($deleteProjectRecordQuery)) {
			return true;
		} else {
			return false;
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
