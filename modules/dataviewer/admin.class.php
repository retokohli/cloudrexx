<?php
/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <ben@comvation.com>
 * @version		1.0.1
 * @package     contrexx
 * @subpackage  module_dataviewer
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
		$_GET['id']  = !empty($_GET['id']) ? $_GET['id'] : "";
		
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
			case "import":
				$this->import($_GET['id']);
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
    	
    	$this->createContentpage("laenderbasiert");
    	
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
					'CONTENT_STATUS_MESSAGE' 	=> $this->strOkMessage = $_ARRAYLANG['TXT_PROJECT_DELETED']
				));	
        	} else {
        		$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' 	=> $this->strErrMessage = $_ARRAYLANG['TXT_PROJECT_COULDN_BE_DELETED']
				));	
        	}
        }
        
        
        //select all projects
        $queryProjects     = "SELECT * FROM ".DBPREFIX."module_dataviewer_projects";
		$objResultProjects = $objDatabase->Execute($queryProjects);
		
		$i = 0;	
		while (!$objResultProjects->EOF) {
			//display choosen filter nicer
			$filtersString = "";
			foreach (explode(";", $objResultProjects->fields['filters']) as $filter) {
				if ($filter !== "") {
					$filtersString .= $filter . "<br />";	
				}
			}
						
			$this->_objTpl->setVariable(array(
				'ID'				=> $objResultProjects->fields['id'],
				'STATUS'			=> $objResultProjects->fields['status'] == 1 ? '<a href="index.php?cmd=dataviewer&amp;id=' . $objResultProjects->fields['id']. '&amp;setstatus=n"><img border="0" src="images/icons/led_green.gif" /></a>' : '<a href="index.php?cmd=dataviewer&amp;id=' . $objResultProjects->fields['id']. '&amp;setstatus=y"><img border="0" src="images/icons/led_red.gif" /></a>',
				'NAME'				=> $objResultProjects->fields['name'],
				'DESCRIPTION' 		=> $objResultProjects->fields['description'],
				'LANGUAGE' 			=> $objResultProjects->fields['language'],
				'COUNTRYBASED' 		=> $objResultProjects->fields['countrybased'] == 1 ? '<img src="../images/modules/dataviewer/thumb_up.gif" alt="countrybasedY">' : '<img src="../images/modules/dataviewer/thumb_down.gif" alt="countrybasedN">',
				'FILTER' 			=> $filtersString,
				'PREVIEW' 			=> $this->createPreview($this->makeInputDBvalid($objResultProjects->fields['name'])),
				'ROWCLASS' 			=> ($i % 2 == 0) ? 'row1' : 'row2',
				'TXT_SHOW_PREVIEW'  => $_ARRAYLANG['TXT_SHOW_PREVIEW']
			));
			
			$i++;
			$this->_objTpl->parse('projectRow');
			$objResultProjects->MoveNext();
		}

        $this->_objTpl->setVariable(array(
            'TXT_PROJECTNAME'        	=> $_ARRAYLANG['TXT_PROJECTNAME'],
            'TXT_PROJECTDESCRIPTION' 	=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
            'TXT_FUNCTIONS'        		=> $_ARRAYLANG['TXT_FUNCTIONS'],
            'TXT_PROJECTSTATUS'        	=> $_ARRAYLANG['TXT_PROJECTSTATUS'],
            'TXT_ADMINISTRATE_PROJECTS' => $_ARRAYLANG['TXT_ADMINISTRATE_PROJECTS'],
            'TXT_LANGUAGE'        		=> $_ARRAYLANG['TXT_LANGUAGE'],
            'TXT_COUNTRY_BASED'        	=> $_ARRAYLANG['TXT_COUNTRY_BASED'],
            'TXT_FILTER'        		=> $_ARRAYLANG['TXT_FILTER'],
            'TXT_PREVIEW'        		=> $_ARRAYLANG['TXT_PREVIEW'],
            'TXT_SAVE'        			=> $_ARRAYLANG['TXT_SAVE'],
            'TXT_DELETE_CONFIRMATION'   => $_ARRAYLANG['TXT_DELETE_CONFIRMATION']
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
		
		//get all columns in table
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$name);
		
		//select first record in table
		$query     = "SELECT * FROM " . DBPREFIX."module_dataviewer_".$name . " LIMIT 1";
		$objResult = $objDatabase->Execute($query);
		
		//create xhtml
		$xhtml       = '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>';
		$firstRecord = "";
		
		foreach ($columns as $column) {
			if ($column !== "country") {
				$xhtml       .= '<td><b>' . $column . '</b></td>';	
				$firstRecord .= '<td>' . $objResult->fields[$column] . '</td>';	
			}
		}
		
		$xhtml .= '<tr>' . $firstRecord . '</tr>';
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
			$languageString = implode(";", $language);
			
			$description 	= !empty($_POST['description']) ? $_POST['description'] : "";
			
			$countryBased 	= !empty($_POST['countryBased']) ? $_POST['countryBased'] : "";
			$countryBased   = $countryBased == "y" ? 1 : 0;
						
			$status 		= !empty($_POST['status']) ? $_POST['status'] : "";
			$status 		= $status == "y" ? 1 : 0;
			
			$columns 		= !empty($_POST['column']) ? $_POST['column'] : "";
			array_pop($columns); //deletes last element, this one is empty because of the JS used to create dynamics input fields
			
			//add field country to columns
			if ($countryBased == 1) {
				$columns[] = "country";
			}
			$columnsString = implode(";", $columns);
						
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
				//create query for dataviewer_projects
				$insertProjectQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_projects 
											(name, 
											description, 
											status, 
											language,
											countrybased,
											filters)
										VALUES
											('" . $name . "',
											'" . $description . "',
											'" . $status . "',
											'" . $languageString . "',
											'" . $countryBased  . "',
											'" . $columnsString  . "');";
				
				
				//insert record in dataviewer_projects
				if($objDatabase->Execute($insertProjectQuery)) {
					$insertProjectQueryOK = true;
				}
							
				//create query for dataviewer_projects_placeholders
				$insertPlaceholderQuery = "	INSERT INTO ".DBPREFIX."module_dataviewer_placeholders 
												(`id`, 
												`projectid`, 
												`column`)
											VALUES ";
				
				$valuesString = "";
				foreach ($columns as $key => $column) {
					$valuesString .= 	"('$key', '" . ($objDatabase->Insert_ID(DBPREFIX."module_dataviewer_projects", "id")) . "', '$column'), ";
				}
								
				//delete last ","
				$valuesString       = substr($valuesString, 0, strlen($valuesString)-2);
				$insertPlaceholderQuery = $insertPlaceholderQuery . $valuesString;
				
				
				//create query for dataviewer_projects_$name
				$createProjectQuery = "CREATE TABLE ".DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($name) ." (";
				
				$columnsString = "";
				foreach ($columns as $column) {
					$columnsString .= $column . " VARCHAR(250), ";
				}
				
				//delete last ","
				$columnsString       = substr($columnsString, 0, strlen($columnsString)-2);
				$createProjectQuery .= $columnsString . ")";
				
				
				//execute queries
				if($insertProjectQueryOK && $objDatabase->Execute($insertPlaceholderQuery) && $objDatabase->Execute($createProjectQuery)) {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = "Projekt wurde erfolgreich erstellt."
					));	
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = "Projekt konnte nicht erstellt werden!<br />" . $insertProjectQuery . "<br />" . $createProjectQuery . "<br />" . $insertPlaceholderQuery
					));	
				}
			}
		}
			
		$this->_objTpl->setVariable(array(
			'AVAILABLE_FRONTENT_LANGUAGES'	=> $this->getFrontentLangCheckboxes(),
			'TXT_FRONTEND_LANGUAGE' 		=> $_ARRAYLANG['TXT_FRONTEND_LANGUAGE'],
			'TXT_YES' 						=> $_ARRAYLANG['TXT_YES'],
			'TXT_NO' 						=> $_ARRAYLANG['TXT_NO'],
			'TXT_ACTIVE' 					=> $_ARRAYLANG['TXT_ACTIVE'],
			'TXT_INACTIVE' 					=> $_ARRAYLANG['TXT_INACTIVE'],
			'TXT_ADD_COLUMN' 				=> $_ARRAYLANG['TXT_ADD_COLUMN'],
			'TXT_PROJECT_COLUMNS' 			=> $_ARRAYLANG['TXT_PROJECT_COLUMNS'],
			'TXT_COUNTRY_BASED' 			=> $_ARRAYLANG['TXT_COUNTRY_BASED'],
			'TXT_PROJECTNAME' 				=> $_ARRAYLANG['TXT_PROJECTNAME'],
			'TXT_PROJECTDESCRIPTION'		=> $_ARRAYLANG['TXT_PROJECTDESCRIPTION'],
			'TXT_PROJECTSTATUS' 			=> $_ARRAYLANG['TXT_PROJECTSTATUS'],
			'TXT_PREVIEW' 					=> $_ARRAYLANG['TXT_PREVIEW'],
			'TXT_SAVE' 						=> $_ARRAYLANG['TXT_SAVE'],	
			'TXT_ONE_COLUMN_OR_MORE' 		=> $_ARRAYLANG['TXT_ONE_COLUMN_OR_MORE'],
			'TXT_COLUMNS_MUSTNT_BE_EMPTY' 	=> $_ARRAYLANG['TXT_COLUMNS_MUSTNT_BE_EMPTY'],
			'TXT_ENTER_A_NAME' 				=> $_ARRAYLANG['TXT_ENTER_A_NAME'],
			'TXT_CHOOSE_FRONTENT_LANG' 		=> $_ARRAYLANG['TXT_CHOOSE_FRONTENT_LANG'],
			'TXT_ENTER_DESCRIPTION' 		=> $_ARRAYLANG['TXT_ENTER_DESCRIPTION'],
			'TXT_CHOOSE_COUNTRYBASED' 		=> $_ARRAYLANG['TXT_CHOOSE_COUNTRYBASED'],
			'TXT_CHOOSE_STATUS' 			=> $_ARRAYLANG['TXT_CHOOSE_STATUS'],
			'TIP_PROJECTNAME'				=> $_ARRAYLANG['TIP_PROJECTNAME'],
			'TIP_FRONTEND_LANG' 			=> $_ARRAYLANG['TIP_FRONTEND_LANG'],
			'TIP_COUNTRYBASED' 				=> $_ARRAYLANG['TIP_COUNTRYBASED'],
			'TIP_DESCRIPTION' 				=> $_ARRAYLANG['TIP_DESCRIPTION'],
			'TIP_STATUS' 					=> $_ARRAYLANG['TIP_STATUS'],
			'TIP_COLUMN_NAME' 				=> $_ARRAYLANG['TIP_COLUMN_NAME']
		));	
	}
	
	
	/**
	 * creates the current activated frontend languages as checkboxes
	 * 
	 * @return string $xhtml
	 */
	function getFrontentLangCheckboxes() {
		global $objDatabase;
		//select current frontend languages
		$query     = "SELECT * FROM " . DBPREFIX . "languages WHERE frontend = '1'";
		$objResult = $objDatabase->Execute($query);
		
		//create xhtml
		$xhtml = "";
		while (!$objResult->EOF) {
			$xhtml .= '<input type="checkbox" name="language[]" value="' . $objResult->fields['id'] . '" /> ' . $objResult->fields['name'];
			$objResult->MoveNext();
		}
		
		return $xhtml;
	}
	
	
	/**
	 * creates languages dropdown based on "lib_country"
	 * 
	 * @param  int $countryBased
	 * @return string $xhtml
	 */
	function getLanguagesDropDown($countryBased) {
		global $objDatabase;
		
		if ($countryBased == 1) {
			$query     = "SELECT * FROM " . DBPREFIX . "lib_country";
			$objResult = $objDatabase->Execute($query);
			
			$xhtml = '<option value="0">'.$_ARRAYLANG['TXT_CHOOSE_COUNTRY'].'</option>';
			while (!$objResult->EOF) {
				$xhtml .= '<option value="' . $objResult->fields['iso_code_2'] . '">' . $objResult->fields['name'] . '</option>';
				$objResult->MoveNext();
			}	
		} else {
			$xhtml = '<option value="0">'.$_ARRAYLANG['TXT_PROJECT_IS_NOT_COUNTRYBASED'].'</option>';
		}
		
		return $xhtml;
	}
	
	
	/**
	 * replaces whitspaces, umlaute and formats to lowercase
	 *
	 * @param  string $input
	 * @return string $input
	 */
	function makeInputDBvalid($input) {
		$input = str_replace(" ", "_", $input);
		$input = str_replace("Ä", "ae", $input);
		$input = str_replace("ä", "ae", $input);
		$input = str_replace("Ö", "oe", $input);
		$input = str_replace("ö", "oe", $input);
		$input = str_replace("Ü", "ue", $input);
		$input = str_replace("ü", "ue", $input);
		$input = strtolower($input);

		return $input;
	}
	
			
	/**
	 * sets the status (visible or hidden)
	 * 
	 * @param  int $id
	 * @param  int $status
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
	 * @param  int $id
	 * @return string $projectname
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
	 * @param  int $id
	 * @return no return
	 */
	function filter($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_filters.html');
		
		$_POST['save'] = !empty($_POST['save']) ? $_POST['save'] : "";
				
		//UPDATE
		if ($_POST['save']) {
			$filters = !empty($_POST['filters']) ? $_POST['filters'] : "";
			$id = !empty($_GET['id']) ? $_GET['id'] : "";
			
			
			$orderChanged = false;
			foreach ($filters as $key => $filter) {
				if ($filter == "noColumn") {
					unset($filters[$key]);
					$orderChanged = true;
				}
			}
			
			
			//create filter string
			$filtersString = implode(";", $filters);
			$filtersString = str_replace("noColumn", "", $filtersString);
			
			if(strlen($filtersString) <= count($filters)) {
				$filtersString = "";
			}
						
			$updateFiltersQuery = "UPDATE ".DBPREFIX."module_dataviewer_projects SET filters = '" . $filtersString . "' WHERE id = '" . $id . "'";				
			
			if($objDatabase->Execute($updateFiltersQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_FILTER_UPDATED'] . ($orderChanged == true ? $_ARRAYLANG['TXT_ORDER_HAS_BEEN_CHANGED'] = "<br />Die Reihenfolge wurde angepasst." : "")
				));
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_FILTER_COULDNT_BE_UPDATED'] . $updateFiltersQuery
				));
			}	
		}		
		
		//get all columns
		$columns         = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
		$numberOfColumns = count($columns);
		
		//select current filters
		$currentFilterQuery = "SELECT filters FROM " . DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult = $objDatabase->Execute($currentFilterQuery);
		$filters   = explode(";", $objResult->fields['filters']);
		
		//set template variables
		$i = 0;
		while ($i < $numberOfColumns) {
			foreach ($columns as $column) {
				$this->_objTpl->setVariable(array(
					'COLUMN' 		=> $column,
					'SELECTED' 		=> $column == $filters[$i] ? 'selected="selected"' : "",
					'TXT_NO_FILTER'	=> $_ARRAYLANG['TXT_NO_FILTER']
				));		
				$this->_objTpl->parse('columnsRow');
			}
		
			$this->_objTpl->setVariable(array(
				'FILTER'	=> "Filter " . ($i == 0 ? "1" : $i+1),
				'ROWCLASS' 	=> ($i % 2 == 0) ? 'row1' : 'row2'
			));		
			$this->_objTpl->parse('filterRow');
			$i++;
		}	
		
		$this->_objTpl->setVariable(array(
			'ID'						=> $id,
			'TXT_FILTER'				=> $_ARRAYLANG['TXT_FILTER'],
			'TXT_SAVE'					=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_NO_DOUBLE_ASSIGNMENTS'	=> $_ARRAYLANG['TXT_NO_DOUBLE_ASSIGNMENTS'],
			'TIP_EVERY_FILTER'			=> $_ARRAYLANG['TIP_EVERY_FILTER']
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
			
			//create querys
			foreach ($placeholders as $key => $placeholder) {
				$updatePlaceholdersQuery[] = "UPDATE ".DBPREFIX."module_dataviewer_placeholders SET `column` = '" . $placeholder . "' WHERE `id` = " . $key . " AND `projectid` = " . $id . "";	
			}
			
			$error = false;
			$queryForErrorReport = "";
			foreach ($updatePlaceholdersQuery as $query) {
				if (!$objDatabase->Execute($query)) {
					$error = true;
				}
				$queryForErrorReport .= $query . "<br />";
			}
			
			if ($error) {
				$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_PLACEHOLDER_COULDNT_BE_UPDATED']  . $queryForErrorReport
					));	
			} else {
				$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_PLACEHOLDER_UPDATED']
					));	
			}
		}
			
			
		//get all columns
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->makeInputDBvalid($this->getProjectName($id)));
				
		//select all placeholder from project
		$getPlaceholdersQuery  = "SELECT * FROM " . DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" . $id . "' ORDER BY id ASC";
		$objPlaceholdersResult = $objDatabase->Execute($getPlaceholdersQuery);
		
		//set template variables
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
				'PLACEHOLDER'		=> $_ARRAYLANG['TXT_PLACEHOLDER'] . " " . ($i == 0 ? "1" : $i+1),
				'ROWCLASS' 			=> ($i % 2 == 0) ? 'row1' : 'row2',
				'TXT_NO_ASSIGNMENT'	=> $_ARRAYLANG['TXT_NO_ASSIGNMENT']
			));		
			$this->_objTpl->parse('placeholderRow');
			$i++;
		}	
			
		$this->_objTpl->setVariable(array(
			'ID'				=> $id,
			'TXT_PLACEHOLDER'	=> $_ARRAYLANG['TXT_PLACEHOLDER'],
			'TIP_PLACEHOLDER'	=> $_ARRAYLANG['TIP_PLACEHOLDER'],
			'TXT_SAVE'			=> $_ARRAYLANG['TXT_SAVE']
		));		
	}
			
		
	/**
	* deletes the record in dataviewer_projects and the project table
	* 
	* @param int $id
	* @return boolean
	*/
	function deleteProject($id) {
		global $objDatabase;
		
		$deleteProjectTableQuery       = "DROP TABLE ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($this->getProjectName($id));
		$deleteProjectRecordQuery      = "DELETE FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$deletePlaceholdersRecordQuery = "DELETE FROM ".DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" . $id . "'";
		
		if ($objDatabase->Execute($deleteProjectTableQuery) && $objDatabase->Execute($deleteProjectRecordQuery) && $objDatabase->Execute($deletePlaceholdersRecordQuery)) {
			return true;
		} else {
			return false;
		}
	}
	
	
		
	function isCountryBased($id) {
		global $objDatabase;			
		$query 		  = "SELECT countrybased FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult    = $objDatabase->Execute($query);
		$countrybased = $objResult->fields['countrybased'] == 1 ? true : false;
		
		return $countrybased;
	}
	
	
	/**
	* handles the view and actions for importing data to a project
	* @param int $id
	*/
	function import($id) {
		global $_ARRAYLANG, $objDatabase;			
		$this->_objTpl->loadTemplateFile('module_dataviewer_import.html');
		require_once("lib/CSVHandler.class.php");
		
		$_POST['continue'] = !empty($_POST['continue']) ? $_POST['continue'] : "";
		$_POST['import']   = !empty($_POST['import']) ? $_POST['import'] : "";

		if($this->isCountryBased($id)) {
			//generate dropdown for country list
			$query     = "SELECT * FROM " . DBPREFIX . "lib_country";
			$objResult = $objDatabase->Execute($query);
			
			while (!$objResult->EOF) {
				$this->_objTpl->setVariable(array(
					'COUNTRY' 	  => $objResult->fields['name'],
					'COUNTRYCODE' => $objResult->fields['iso_code_2'],
				));
				$this->_objTpl->parse('countryRow');
				$objResult->MoveNext();
			}
			
		
		}

		$this->_objTpl->setVariable(array(
			'ID' => $id,
			'COUNTRY_STATEMENT' => $this->isCountryBased($id) ? $_ARRAYLANG['TXT_CHOOSE_COUNTRY'] : $_ARRAYLANG['TXT_PROJECT_IS_NOT_COUNTRYBASED']
		));	
		
		
		
			
		
		//CONTINUE button
		if ($_POST['continue']) {
			$file     		 = $_FILES['csvFile'];
			$selectedCountry = $_POST['country'];
			
			if ($selectedCountry == "null" && $this->isCountryBased($id)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_CHOOSE_COUNTRY_WHICH_DATA_FOR']
				));	
			}
			
			$this->_objTpl->setVariable(array(
				'FILENAME' 				 => $file['name']
			));	
			
			//generate dropdown for country list
			//SELECTED == selected
			$query     = "SELECT * FROM " . DBPREFIX . "lib_country";
			$objResult = $objDatabase->Execute($query);
			$selected  = "";
			
			while (!$objResult->EOF) {
				$selected = $objResult->fields['iso_code_2'] == $selectedCountry ? "selected" : "";
				$this->_objTpl->setVariable(array(
					'COUNTRY' 	  => $objResult->fields['name'],
					'COUNTRYCODE' => $objResult->fields['iso_code_2'],
					'SELECTED'    => $selected
				));
				$this->_objTpl->parse('countryRow');
				$objResult->MoveNext();
			}
			
			//generate selection stuff
			//filetype check		
			if ($file['type'] == "text/comma-separated-values") {
				//upload file
				if($this->uploadFile($file)) {
					$file 	= ASCMS_DATAVIEWER_TEMP_PATH . $file['name'];
					$objCSV = new CSVHandler($file, ";", "");
			
					//get all columns
					$columnsDB  = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$this->getProjectName($id));
					$columnsCSV = $objCSV->ListAllHeader();
					
					//dont display country column
					if ($this->isCountryBased($id)) {
						unset($columnsDB["COUNTRY"]); //in db field "country" no imports are alowed
					}
					
					//create drop down menu with columns from csv file
					$xhtml = '<option value="0">'.$_ARRAYLANG['TXT_NO_ASSIGNMENT'].'</option>';
					foreach ($columnsCSV as $column) {
						$xhtml .= '<option value="' . $column . '">' . $column . '</option>';
					}
					
					//ouput	
					$i = 0;
					foreach ($columnsDB as $column) {
						$this->_objTpl->setVariable(array(
							'COLUMNS_CSV_DROPDOWN' => $xhtml,
							'COLUMN_NAME_DB' 	   => $column,
							'ROWCLASS' 			   => ($i % 2 == 0) ? 'row1' : 'row2'
						));
						
						$i++;
						$this->_objTpl->parse('columnRow');
					}
				} else {
					$this->_objTpl->setVariable(array(
						'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_FILE_COULDNT_BE_UPLOADED']
					));	
				}
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_WRONG_FILETYPE']
				));	
			}
		}
		
		//IMPORT
		if ($_POST['import']) {
			$columnsDB  	= $_POST['dbColumn'];
			$columnsCSV 	= $_POST['csvColumn'];
			$hiddenFilename = $_POST['fileName'];	
			$country 		= $_POST['country'];	
			
			if ($this->isCountryBased($id)) {
				//if records for country allready exists,
				//delete it
				if($this->countryExistsInDB($id, $country)) {
					$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_dataviewer_" . $this->getProjectName($id) . " WHERE country = '" . $country . "'");
				}	
			}
			
			$objCSV = new CSVHandler(ASCMS_DATAVIEWER_TEMP_PATH . $hiddenFilename, ";", "");

			//create query for insert
			$insertDataQuery = "INSERT INTO ".DBPREFIX."module_dataviewer_" . $this->getProjectName($id) . " (";
		
			//add columns manually, if project is countrybased
			if ($this->isCountryBased($id)) {
				$columnsDB[] = "country";
				$columnsCSV[] = "country";
			}
			
			//fields
			$columnsNameString = "";
			foreach ($columnsDB as $column) {
				$columnsNameString .= "`" . $column . "`,";
			}
			
			//delete last ","
			$columnsNameString  = substr($columnsNameString, 0, strlen($columnsNameString)-1);
			$columnsNameString .= ") VALUES ";

			//values
			$csvData 			 = $objCSV->ReadCSV();
			$columnsValuesString = "";
			
			//write all values to query string
			foreach ($csvData as $row) {
				if ($this->isCountryBased($id)) {
					$row['country'] = $country;
				}	
				
				$columnsValuesString .= "(";
				foreach ($columnsCSV as $column) {
					$columnsValuesString .= "'" . $row[$column] . "',";
				}
				$columnsValuesString  = substr($columnsValuesString, 0, strlen($columnsValuesString)-1);
				$columnsValuesString .= "), ";
			}
			
			$columnsValuesString  = substr($columnsValuesString, 0, strlen($columnsValuesString)-3);			
			$columnsValuesString .= ")";
			$insertDataQuery = $insertDataQuery . $columnsNameString . $columnsValuesString;
			
			if ($objDatabase->Execute($insertDataQuery)) {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strOkMessage = $_ARRAYLANG['TXT_DATA_IMPORTED']
				));	
			} else {
				$this->_objTpl->setVariable(array(
					'CONTENT_STATUS_MESSAGE' => $this->strErrMessage = $_ARRAYLANG['TXT_ERROR_AT_IMPORT'] . $insertDataQuery
				));	
			}
		}
	}
	
	
	/**
	 * uploads the csv file
	 *
	 */
	function uploadFile($file) {
		$tempFilePath = ASCMS_DATAVIEWER_TEMP_PATH;
		
		if ($file['type'] == "text/comma-separated-values") {
				if (move_uploaded_file($file['tmp_name'], $tempFilePath . $file['name'])) {
					return true;
				} else {
					return false;
				}
			}
	}
		
	
	function countryExistsInDB($id, $country) {
		global $objDatabase;			
		$query 		  = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->getProjectName($id) . " WHERE country = '" . $country . "'";
		$objResult    = $objDatabase->Execute($query);
		
		if($objResult->_numOfRows > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	
	
	
	/**
	 * creates content page for project
	 */
	function createContentpage($projectname) {
		global $objDatabase;
		
		$columns = $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$projectname);
		
		$tableHeadlines = "";
		$tableContent   = "";
		$i = 1;
		foreach ($columns as $column) {
			$tableHeadlines .= '<td><strong>' . $column  . '</strong></td>';
			$tableContent   .= '<td>[[DATAVIEWER_PLACEHOLDER_' . $i . ']]</td>';
			$i++;
		}
		
		$tableHeadlines = "<tr>" . $tableHeadlines . "</tr>" ;
		$tableContent   = "<tr>" . $tableContent . "</tr>" ;
		$tableStart     = '<table border="1" width="100">';
		$tableEnd       = '</table>';
		
		$xhtml = $tableStart . $tableHeadlines . $tableContent;
	}
}
?>
