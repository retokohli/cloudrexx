<?php
/**
 * Class Dataviewer
 */

class  Dataviewer 
{
    var $_objTpl;
    

    /**
     * Constructor
     * @param  string  $pageContent
     */
    function __construct($pageContent) {
        global $_ARRAYLANG;
        $this->pageContent = $pageContent;
        $this->_objTpl = new HTML_Template_Sigma('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
    }
    

    function getPage() {
    	$projectname = !empty($_GET['cmd']) ? $_GET['cmd'] : "";
    	
    	if ($projectname == "") {
    		$this->showOverview();
		} else {
			$this->show($projectname);
    	}
        return $this->_objTpl->get();
    }
    
    
    function showOverview() {
    	global $objDatabase, $_ARRAYLANG;
    	$this->_objTpl->setTemplate($this->pageContent);
    }
    
    
	function show($projectname) {
		global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent);
		
		if (!$this->isActive($projectname)) {
			header("Location: index.php");
		}			
		
		//prepare $GET filter
		$selectedFilters = !empty($_GET['filter']) ? $_GET['filter'] : "";
		$where           = "";
		if ($selectedFilters !== "") {
			$selectedFiltersEx = explode(",", $selectedFilters);
			
			foreach ($selectedFiltersEx as $value) {
				$explode[] = explode("=", $value);
			}
			$selectedFilters = "";
			foreach ($explode as $value) {
				$selectedFilters[$value[0]] = $value[1];
			}	
			
			//build WHERE clause for select query
			$where = " WHERE ";
			foreach ($selectedFilters as $name => $value) {
				$where .= $name . " = '" . $value . "' AND "; 
			}
			
			$where = substr($where, 0, strlen($where)-5);
		}

		
		//get all placeholders for current project
		$placeholdersQuery     = "SELECT * FROM ".DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" .  $this->getProjectID($projectname) . "'";
		$objPlaceholdersResult = $objDatabase->Execute($placeholdersQuery);
		
		//JUST FOR DIAMIR fixed distributor 
		//later we have to fix this by settings from projecttable
//		$orderBy = " ORDER BY distributor DESC, name ASC";
		$orderBy = "";
		
		//get all records for current project
		$selectRecordsQuery = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $projectname . $where . $orderBy;
		$objRecordsResult   = $objDatabase->Execute($selectRecordsQuery);
		
		//create placeholders array 
		//=> [placeholder id][column which placeholder displays]
		while (!$objPlaceholdersResult->EOF) {
				$placeholders[$objPlaceholdersResult->fields['id']] = $objPlaceholdersResult->fields['column'];
				$objPlaceholdersResult->MoveNext();	
		}	
		
		
		//set template variables
		$firstRun = true; //diamir
		while (!$objRecordsResult->EOF) {			
			foreach ($placeholders as $id => $placeholder) {
				$id++;	//because we dont want placeholder_0
				$this->_objTpl->setVariable(array(
					'DATAVIEWER_PLACEHOLDER_' . $id => htmlspecialchars($objRecordsResult->fields[$placeholder])
//					'DATAVIEWER_PLACEHOLDER_' . $id => $firstRun == true && $objRecordsResult->fields['distributor'] == 1 ? "<b>".$objRecordsResult->fields[$placeholder]."</b>" : $objRecordsResult->fields[$placeholder] //diamir
				));	
			}
			
			$this->_objTpl->parse('dataviewer_row');		
			$objRecordsResult->MoveNext();
			$firstRun = false;//diamir
		}	
				
		
		//create drop down menue for filtering
		if ($this->hasFilters($projectname)) {
			$mainFilter = $this->getFilterDropDown($projectname, "main");
			$subFilters = $this->getFilterDropDown($projectname, "");
			$this->_objTpl->setVariable(array(
				'DATAVIEWER_FILTER' => $mainFilter . $subFilters
			));		
		}
	}
	
	
	function hasFilters($projectname) {
		global $objDatabase;
		//get all records for current project
		$query = "SELECT filters FROM ".DBPREFIX."module_dataviewer_projects WHERE name ='" . $projectname . "'";
		$objResult   = $objDatabase->Execute($query);
		if ($objResult->fields['filters'] == "") {
			return false;	
		} else {
			return true;
		}
	}
	
	
	
	/**
	 * gets projectid by projectname
	 * 
	 * @param  string $projectname
	 * @return int $id
	 */
	function getProjectID($projectname) {
		global $objDatabase;
		$query     = "SELECT id FROM ".DBPREFIX."module_dataviewer_projects WHERE name = '" . $projectname . "';";
		$objResult = $objDatabase->Execute($query);
		
		if($objResult->_numOfRows > 0) {
			return $objResult->fields['id'];	
		} else {
			return 00;
		}
	}
	
	
	function isActive($projectname) {
		global $objDatabase;
		$query     = "SELECT status FROM ".DBPREFIX."module_dataviewer_projects WHERE name = '" . $projectname . "';";
		$objResult = $objDatabase->Execute($query);
		
		if($objResult->fields['status'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	
	
	/**
	 * creates filter dropdown
	 * 
	 * @param  string $projectname
	 * @return string $xhtml
	 */
	function getFilterDropDown($projectname, $mode) {
		global $objDatabase;
		
		$selectedFilters = !empty($_GET['filter']) ? $_GET['filter'] : "";
			
		//prepare $GET filter
		if ($selectedFilters !== "") {
			$selectedFiltersEx = explode(",", $selectedFilters);
			
			foreach ($selectedFiltersEx as $value) {
				$explode[] = explode("=", $value);
			}
			$selectedFilters = "";
			foreach ($explode as $value) {
				$selectedFilters[$value[0]] = $value[1];
			}	
		}
		
	
						
		//get filters string from projecttable
		$queryFilters = "SELECT filters FROM " . DBPREFIX . "module_dataviewer_projects WHERE name = '" . $projectname . "'";
		$objResult    = $objDatabase->Execute($queryFilters);
		$filters      = $objResult->fields['filters'];
				
		//explode string to array
		$filtersArray = explode(";", $filters);
		$xhtml = "";
				
		/******************************
		* build main filter drop down
		*******************************/
		if ($mode == "main") {
			//create menue for each filter
			$valuesArray = "";
			
			//select all values in column		
			$query     = "SELECT DISTINCT " . $filtersArray[0] . " FROM " . DBPREFIX . "module_dataviewer_" . $projectname;
			$objResult = $objDatabase->Execute($query);
			
			//create array with values from filters
			while (!$objResult->EOF) {
				$valuesArray[] = $objResult->fields[$filtersArray[0]];
				$objResult->MoveNext();
			}
			
			
			foreach ($selectedFilters as $first) {
				$firstFilter = $first;
				break;
			}
						
			//create xhtml dropdown
			$xhtml .= '<select size="1" name="placeholders[]" id="' . $filtersArray[0] . '" style="float:left;" onchange="location.href=\'index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;filter=' . $filtersArray[0] . '=\' + this.value + \'\'">
					<option value="0">' . $filtersArray[0] . '</option>';
			
			foreach ($valuesArray as $content) {
				if ($content == $firstFilter) {
					$selected = 'selected="selected"';
				} else {
					$selected = "";
				}
				$content = htmlspecialchars($content);
				$xhtml .= '<option value="' . $content . '" ' . $selected . '>' . $content . '</option>';	
			}
			$xhtml .= "</select>";	
			
			return $xhtml;
		} else {
			//delete main filter from array (this is allways the first one)
			unset($filtersArray[0]);
			
			//create menue for each filter
			$xhtml = "";
			
			$i = 0;
			foreach ($filtersArray as $filter) {
				if ($filter !== "") {
					$valuesArray = "";
					
					
					//build WHERE clause for $query
					$where = " WHERE ";
					$whereForDropDown = "";
					
					$z = 0;
					foreach ($selectedFilters as $name => $value) {
						$where .= $name . " = '" . $value . "' AND "; 	
						$whereForDropDown .= $name . "=" . $value .",";
						if ($z == $i) {
							break;
						}
						$z++;
					}
					
					//delete last "AND"
					$where = substr($where, 0, strlen($where)-5);
					if($where == " W") {
						$where = "";
					}
					
					//select all values in column		
					$query     = "SELECT DISTINCT " . $filter . " FROM " . DBPREFIX . "module_dataviewer_" . $projectname . $where . " ORDER BY " . $filter . " ASC";
					$objResult = $objDatabase->Execute($query);
					
					//create array with values from filters
					while (!$objResult->EOF) {
						$valuesArray[] = $objResult->fields[$filter];
						$objResult->MoveNext();
					}
					
					//create xhtml dropdown
					$xhtml .= '<select size="1" name="placeholders[]" id="dd_'.$filter.'" style="float:left; margin-left:10px;" onchange="location.href=\'index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;filter=' . $whereForDropDown . $filter . '=\' + this.value + \'\'">
							<option value="0">' . $filter . '</option>';
										
									
					foreach ($valuesArray as $content) {
						//make selected
						if (str_replace(" ", "", $content) == str_replace(" ", "", $selectedFilters[$filter])) {
							$selected = 'selected="selected"';
						} else {
							$selected = "";
						}
						$content = htmlspecialchars($content);					
						$xhtml .= '<option value="' . $content . '" ' . $selected . '>' . $content . '</option>';	
					}
					$xhtml .= "</select>";	
					$i++;
				}
			}
			
		return $xhtml;
		}
	
	}
}


?>
