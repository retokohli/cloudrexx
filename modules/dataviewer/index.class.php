<?php
/**
 * Class Dataviewer
 */

class  Dataviewer {
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
				if($name == "country"){$country = $value;}			//DIAMIR
			}
			
			$where = substr($where, 0, strlen($where)-5);
		}

		
		//get all placeholders for current project
		$placeholdersQuery     = "SELECT * FROM ".DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" .  $this->getProjectID($projectname) . "'";
		$objPlaceholdersResult = $objDatabase->Execute($placeholdersQuery);
		
		
		//later we have to fix this by settings from projecttable
//		$orderBy = " ORDER BY distributor DESC, name ASC"; //==> diamir
		$orderBy = "";
		
		
		//create placeholders array 
		//=> [placeholder id][column which placeholder displays]
		while (!$objPlaceholdersResult->EOF) {
			$placeholders[$objPlaceholdersResult->fields['id']] = $objPlaceholdersResult->fields['column'];
			$objPlaceholdersResult->MoveNext();	
		}
		
		
		//get all records for current selection project
		$selectRecordsQuery = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($projectname) . $where . $orderBy;
		if($selectedFilters == "") {
				$selectRecordsQuery = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($projectname) . "  LIMIT 0";
		}
		$objRecordsResult   = $objDatabase->Execute($selectRecordsQuery);
		
		
		
		//*****************DIAMIR
		if (in_array("Distributor", $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$projectname))) {
			if ($selectedFilters['country'] !== "" && (count($selectedFilters) == 1)) {
				$selectRecordsQuery 		   = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($projectname) . " WHERE country = '9999'";	//we dont wanna display all records => at beginning just the distributors
				$selectRecordsQueryDistributor = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($projectname) . $where . " AND Distributor = '1' ORDER BY name ASC";
				$objRecordsResultDistributor   = $objDatabase->Execute($selectRecordsQueryDistributor);
				$objRecordsResult  			   = $objDatabase->Execute($selectRecordsQuery);
				
				while (!$objRecordsResultDistributor->EOF) {			
					foreach ($placeholders as $id => $placeholder) {
						$id++;	//because we dont want placeholder_0
						$this->_objTpl->setVariable(array(
							'DATAVIEWER_PLACEHOLDER_' . $id => htmlspecialchars($objRecordsResultDistributor->fields[$placeholder])
						));	
					}
					
					$this->_objTpl->parse('dataviewer_distributor_row');		
					$objRecordsResultDistributor->MoveNext();
				}	
			}	
		}
		//*****************DIAMIR
		
		
		
		//set template variables
		while (!$objRecordsResult->EOF) {			
			foreach ($placeholders as $id => $placeholder) {
				$id++;	//because we dont want placeholder_0
				$this->_objTpl->setVariable(array(
					'DATAVIEWER_PLACEHOLDER_' . $id => htmlspecialchars($objRecordsResult->fields[$placeholder])
				));	
			}
			
			$this->_objTpl->parse('dataviewer_row');		
			$objRecordsResult->MoveNext();
		}	
		
		
		//create drop down menue for filtering
		if ($this->hasFilters($projectname)) {
			$mainFilter = $this->getFilterDropDown($projectname, "main");
			$subFilters = $this->getFilterDropDown($projectname, "");
			$this->_objTpl->setVariable(array(
				'DATAVIEWER_JS' 	=> $this->buildJS($projectname),
				'DATAVIEWER_FILTER' => $mainFilter . $subFilters
			));		
		}
	}
	
	
	
	/**
	 * checks if project is countrybased
	 *
	 * @param  int $id
	 * @return boolean
	 */
	function isCountryBased($id) {
		global $objDatabase;			
		$query 		  = "SELECT countrybased FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
		$objResult    = $objDatabase->Execute($query);
		
		return $objResult->fields['countrybased'] == 1 ? true : false;
	}
	
	
	
	/**
	 * creates javascript for hide & show the dropdown menues
	 *
	 * @param  string $projectname
	 * @return string $JS
	 */
	function buildJS($projectname) {
		global $objDatabase;
		//get filters string from projecttable
		$queryFilters = "SELECT filters FROM " . DBPREFIX . "module_dataviewer_projects WHERE name = '" . $projectname . "'";
		$objResult    = $objDatabase->Execute($queryFilters);
		$filters      = $objResult->fields['filters'];
		$countFilters = count($filters);
		
		$JS = '
				<script type="text/javascript">
					var url      = document.URL.split("&")[2];
					var url      = url.replace("filter=", "");
					var splitAll = url.split(",");
						
					var filtersSelected = new Array();
					for (i=0; i<splitAll.length; i++) {
						filtersSelected.push(splitAll[i].split("=")[0]);
					}
					
					var filters      = "'.$filters.'";
					var filtersSplit = filters.split(";");
					var filtersTotal = new Array();
					for (i=0; i<filtersSplit.length; i++) {
						filtersTotal.push(filtersSplit[i]);
					}
					
					currentFiltersCount = filtersSelected.length;
					filtersTotalCount   = filtersTotal.length;
					
					for (i=0; i<=currentFiltersCount; i++) {
						document.getElementById("dd_"+filtersTotal[i]).style.display = "block";	
					}
				</script>
		';
		
		return $JS;
	}
	
	
	/**
	 * checks if project has filters defined
	 *
	 * @param  string $projectname
	 * @return boolean
	 */
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
	
	
	/**
	 * gets status of project
	 *
	 * @param  string $projectname
	 * @return boolean
	 */
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
		global $objDatabase, $_ARRAYLANG;
		
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
			$query     = "SELECT DISTINCT " . $filtersArray[0] . " FROM " . DBPREFIX . "module_dataviewer_" . $this->makeInputDBvalid($projectname);
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
			
			//special to have multilingual words for "country"
			if ($filtersArray[0] == "country") {
				$filterTemp = $filtersArray[0];
				$filterX = $_ARRAYLANG['TXT_COUNTRY'] = "Land";
			} else {
				$filterX = $filtersArray[0];
			}
						
			//create xhtml dropdown
			$xhtml .= '<div id="dd_'.$filtersArray[0].'" class="dataviewer_Select_First">
							<select size="1" onchange="location.href=\'index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;filter=' . $filtersArray[0] . '=\' + this.value + \'\'">
					<option value="0">' . $filterX . '</option>';
			
			foreach ($valuesArray as $content) {
				//makes iso_code_2 to Countryname
				if ($filtersArray[0] == "country") {
					$contentX = $this->getLanguageName($content);
				} else {
					$contentX = $content;
				}
				
				if ($content == $firstFilter) {
					$selected = 'selected="selected"';
				} else {
					$selected = "";
				}
				$content = htmlspecialchars($content);
				$xhtml .= '<option value="' . $content . '" ' . $selected . '>' . $contentX . '</option>';	
			}
			$xhtml .= "</select></div>";	
			
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
					$query     = "SELECT DISTINCT " . $filter . " FROM " . DBPREFIX . "module_dataviewer_" . $this->makeInputDBvalid($projectname) . $where . " ORDER BY " . $filter . " ASC";
					$objResult = $objDatabase->Execute($query);
					
					//create array with values from filters
					while (!$objResult->EOF) {
						$valuesArray[] = $objResult->fields[$filter];
						$objResult->MoveNext();
					}
					
					
					//special to have multilingual words for "country"
					if ($filter == "country") {
						$filterX = $_ARRAYLANG['TXT_COUNTRY'] = "Land";
					} else {
						$filterX = $filter;
					}
					//create xhtml dropdown
					$xhtml .= '
							<div id="dd_'.$filter.'" class="dataviewer_Select">
								<select size="1" onchange="location.href=\'index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;filter=' . $whereForDropDown . $filter . '=\' + this.value + \'\'">
								<option value="0">' . $filterX . '</option>';
										
									
					foreach ($valuesArray as $content) {
						//makes iso_code_2 to Countryname
						if ($filter == "country") {
							$contentX = $this->getLanguageName($content);
						} else {
							$contentX = $content;
						}
						
						//make selected
						if (str_replace(" ", "", $content) == str_replace(" ", "", $selectedFilters[$filter])) {
							$selected = 'selected="selected"';
						} else {
							$selected = "";
						}
						$content = htmlspecialchars($content);					
						$xhtml .= '<option value="' . $content . '" ' . $selected . '>' . $contentX . '</option>';	
					}
					$xhtml .= "</select></div>";	
					$i++;
				}
			}
			
		return $xhtml;
		}
	
	}
	
	
	/**
	 * gets language name from countrycodes
	 *
	 * @param  string $id;
	 * @return string name;
	 */
	function getLanguageName($id) {
		global $objDatabase;
		$query     = "SELECT name FROM ".DBPREFIX."lib_country WHERE iso_code_2 = '".$id."'";
		$objResult = $objDatabase->Execute($query);
		return $objResult->fields['name'];
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
}


?>
