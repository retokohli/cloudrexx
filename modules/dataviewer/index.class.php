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
    	$_GET['cmd'] = !empty($_GET['cmd']) ? $_GET['cmd'] : "";
    	
    	switch ($_GET['cmd']) {
    		case "":
    			$this->showList();
    			break;
    		default:
    			$this->show();
    	}
        return $this->_objTpl->get();
    }
    
    
	function show() {
		global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent);
		
		require 'lib/class.eyemysqladap.inc.php';
		require 'lib/class.eyedatagrid.inc.php';
		
		$projectname    = !empty($_GET['cmd']) ? strtolower($_GET['cmd']) : "";
		$_GET['filter'] = !empty($_GET['filter']) ? $_GET['filter'] : "";
		
		
		//create output table
		// Load the database adapter
		$db = new EyeMySQLAdap('localhost', 'root', '', 'svntrunk');
		// Load the datagrid class
		$x = new EyeDataGrid($db);
		// Set the query
		
		//diesplay all fields EXECPT the country field
		$x->setQuery($this->getColumnsFromProject($this->getProjectID($projectname)), "contrexx_module_dataviewer_" . $projectname);
		$output = $x->printTable($projectname);
						
		//create filters drop down
		$queryProjects     = "SELECT fields FROM ".DBPREFIX."module_dataviewer_filters WHERE projectid = '" . $this->getProjectID($projectname) . "'";
		$objResultProjects = $objDatabase->Execute($queryProjects);
		
		$fieldExplode = explode(";", $objResultProjects->fields['fields']);
		
		
		$filtersDropDown = '<form method="post" action="">';	

		//standard COUNTRY select option
		$filtersDropDown .= '
								<select name="filter[country]" onchange="window.location=\'index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;pageDV=&amp;order=&amp;filter=country:\' + this.value">									
								<option value="">Land</option>
								';
		$queryCountryValues = "SELECT DISTINCT country, ".DBPREFIX."lib_country.name FROM ".DBPREFIX."module_dataviewer_" . $projectname . " LEFT JOIN " .DBPREFIX."lib_country ON ".DBPREFIX."module_dataviewer_" . $projectname . ".country = ".DBPREFIX."lib_country.iso_code_2";
		$objResultCountryValues = $objDatabase->Execute($queryCountryValues);	
			
		while (!$objResultCountryValues->EOF) {
			$filtersDropDown .= '<option value="' . $objResultCountryValues->fields['country'] . '">' . $objResultCountryValues->fields['name'] . '</option>
			';
			$objResultCountryValues->MoveNext();
		}
		
		$filtersDropDown .= "</select>";
		
			
		foreach ($fieldExplode as $singleField) {
			$filtersDropDown .= '
								<select name="filter[' . $singleField . ']" onchange="window.location=\'index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;pageDV=&amp;order=&amp;filter=' . $singleField . ':\' + this.value + \'\'">
								<option value="">' . $singleField . '</option>
								';

			//set filter for select options
			$arrFilter = explode(':', contrexx_addslashes($_GET['filter']));
			$where = '';
			if(!empty($arrFilter[0]) && $arrFilter[0] != $singleField){
				$where = " WHERE `".$arrFilter[0]."` =  '".$arrFilter[1]."' ";
			}
			
			//get select options
			$queryFieldValues = "SELECT DISTINCT " . $singleField . " FROM ".DBPREFIX."module_dataviewer_" . $projectname . $where . " ORDER BY " . $singleField . " ASC";
			$objResultFieldValues = $objDatabase->Execute($queryFieldValues);	

			while (!$objResultFieldValues->EOF) {
				//set selected="selected"
				$selected = "";
				if ($objResultFieldValues->fields[$singleField] == $arrFilter[1]) {
					$selected = 'selected="selected"';	
				}
				
				$filtersDropDown .= '<option value="' . $objResultFieldValues->fields[$singleField] . '"' . $selected . '>' . $objResultFieldValues->fields[$singleField] . '</option>
				';
				$objResultFieldValues->MoveNext();
			}
				
			$filtersDropDown .= '</select>';
		}
		
		$filtersDropDown .= '</form>';
		
		$this->_objTpl->setVariable(array(
			'FILTERS_DROPDOWN' 	=> $filtersDropDown,
			'OUTPUT_VIEW' 		=> $output,
			'PROJECTNAME' 		=> "name"
		));
		
	}
	
	
	function showList() {
		global $objDatabase, $_ARRAYLANG;
		$this->_objTpl->setTemplate($this->pageContent);
		
		//create filters drop down
		$queryAllProjects     = "SELECT * FROM ".DBPREFIX."module_dataviewer_projects WHERE projectstatus = '1'";
		$objResultAllProjects = $objDatabase->Execute($queryAllProjects);
		
		while (!$objResultAllProjects->EOF) {
			$objResultAllProjects->fields['projectname'];
			$this->_objTpl->setVariable(array(
				'PROJECT_NAME'			=> $objResultAllProjects->fields['projectname'],
				'PROJECT_DESCRIPTION'	=> $objResultAllProjects->fields['projectdescription'],
				'PROJECT_URL' 			=> 'index.php?section=dataviewer&amp;cmd=' . $objResultAllProjects->fields['projectname']
			));
			$this->_objTpl->parse('projectRow');
			$objResultAllProjects->MoveNext();
		}
		
	
	}
	
	/**
	 * get projectid by projectname
	 * @param int $projectname
	 * @return var $projectid
	 */
	function getProjectID($projectname) {
		global $objDatabase;
		$query     = "SELECT projectid FROM ".DBPREFIX."module_dataviewer_projects WHERE projectname = '" . $projectname . "';";
		$objResult = $objDatabase->Execute($query);
		$projectid = $objResult->fields['projectid'];
		
		return $projectid;
	}
	
	
	/**
	 * returns selected project columns 
	 * @param int $projectid
	 * @return var $columns
	 */	
	function getColumnsFromProject($projectid) {
		global $objDatabase;
		$query     = "SELECT projectfields FROM ".DBPREFIX."module_dataviewer_projects WHERE projectid = '" . $projectid . "';";
		$objResult = $objDatabase->Execute($query);
		$columns   = str_replace(";", ", ", $objResult->fields['projectfields']);
		
		return $columns;
	}
}
?>
