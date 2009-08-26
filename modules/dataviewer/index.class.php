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

        if (!$this->isActive($projectname) || !in_array($_SESSION['userFrontendLangId'], $this->getLangID($projectname))) {
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
                if($name == "country"){$country = $value;}            //DIAMIR
            }

            $where = substr($where, 0, strlen($where)-5);
        }


        //get order by
        $orderByQuery        = "SELECT order_by FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" .  $this->getProjectID($projectname) . "'";
        $objOrderByResult = $objDatabase->Execute($orderByQuery);
        $orderBy = !empty($objOrderByResult->fields['order_by']) ? " ORDER BY " . $objOrderByResult->fields['order_by'] . " ASC": "";

        //get all placeholders for current project
        $placeholdersQuery     = "SELECT * FROM ".DBPREFIX."module_dataviewer_placeholders WHERE projectid = '" .  $this->getProjectID($projectname) . "'";
        $objPlaceholdersResult = $objDatabase->Execute($placeholdersQuery);

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

        if (!$this->hasFilters($projectname)) {
            $selectRecordsQuery = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($projectname) . $orderBy;
        }


        //*****************DIAMIR
        $this->_objTpl->setVariable(array('DISPLAY_STYLE' => "none"));

        if (in_array("Distributor", $objDatabase->MetaColumnNames(DBPREFIX."module_dataviewer_".$projectname)) && $selectedFilters['country'] !== "" && (count($selectedFilters) == 1)) {
            $selectRecordsQueryDistributor = "SELECT * FROM ".DBPREFIX."module_dataviewer_" . $this->makeInputDBvalid($projectname) . $where . " AND Distributor = '1' ORDER BY dealer ASC";
            $objRecordsResultDistributor   = $objDatabase->Execute($selectRecordsQueryDistributor);

            while (!$objRecordsResultDistributor->EOF) {
                foreach ($placeholders as $id => $placeholder) {
                    $id++;    //because we dont want placeholder_0
                    $this->_objTpl->setVariable(array(
                        'DATAVIEWER_PLACEHOLDER_' . $id => htmlspecialchars($objRecordsResultDistributor->fields[$placeholder])
                    ));
                }


                $this->_objTpl->parse('dataviewer_distributor_row');
                $objRecordsResultDistributor->MoveNext();
            }

            $this->_objTpl->setVariable(array('DISPLAY_STYLE' => "block"));
        }
        //*****************DIAMIR

        $objRecordsResult  = $objDatabase->Execute($selectRecordsQuery);

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


        if(count($filtersArray) == count($selectedFiltersEx)) {
            //set template variables
            while (!$objRecordsResult->EOF) {
                foreach ($placeholders as $id => $placeholder) {
                    $id++;    //because we dont want placeholder_0
                    $this->_objTpl->setVariable(array(
                        'DATAVIEWER_PLACEHOLDER_' . $id => htmlspecialchars($objRecordsResult->fields[$placeholder])
                    ));
                }

                $this->_objTpl->parse('dataviewer_row');
                $objRecordsResult->MoveNext();
            }
        }

        if (!$this->hasFilters($projectname)) {
            //set template variables
            while (!$objRecordsResult->EOF) {
                foreach ($placeholders as $id => $placeholder) {
                    $id++;    //because we dont want placeholder_0
                    $this->_objTpl->setVariable(array(
                        'DATAVIEWER_PLACEHOLDER_' . $id => htmlspecialchars($objRecordsResult->fields[$placeholder])
                    ));
                }

                $this->_objTpl->parse('dataviewer_row');
                $objRecordsResult->MoveNext();
            }
        }








        //create drop down menue for filtering
        if ($this->hasFilters($projectname)) {
            $this->parseFilterRow($projectname);
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
        $query           = "SELECT countrybased FROM ".DBPREFIX."module_dataviewer_projects WHERE id = '" . $id . "'";
        $objResult    = $objDatabase->Execute($query);

        return $objResult->fields['countrybased'] == 1 ? true : false;
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
        $query     = "SELECT status FROM ".DBPREFIX."module_dataviewer_projects WHERE name = '" . $this->makeInputDBvalid($projectname) . "';";
        $objResult = $objDatabase->Execute($query);

        if($objResult->fields['status'] == "1") {
            return true;
        } else {
            return false;
        }
    }

    function getLangID($projectname) {
        global $objDatabase;
        $query     = "SELECT language FROM ".DBPREFIX."module_dataviewer_projects WHERE name = '" . $this->makeInputDBvalid($projectname) . "';";
        $objResult = $objDatabase->Execute($query);
        $arrLanguage = explode(";", $objResult->fields['language']);

        return $arrLanguage;
    }


    /**
     * creates filter dropdown
     *
     * @param  string $projectname
     * @return string $xhtml
     */
    function parseFilterRow($projectname) {
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
        $filtersArrayNotManipulated = explode(";", $filters);
        $lastFilter[] = $filtersArray[count($filtersArray)-1];

        $where = "";
        if ($selectedFilters !== "") {
            //delete filters already in URL from array
            $x = 0;
            while ($x < count($selectedFilters)) {
                unset($filtersArray[$x]);
                $x++;
            };

            $filtersArray = array_values($filtersArray);

            //build WHERE clause for $query
            $where = " WHERE ";
            foreach ($selectedFilters as $name => $value) {
                $where .= $name . " = '" . $value . "' AND ";
            }

            //delete last "AND"
            $where = substr($where, 0, strlen($where)-5);
            if($where == " W") {
                $where = "";
            }
        }


        if (count($filtersArray) == 0) {
            $filtersArray = $lastFilter;
        }

        //select all values in column
        $query     = "SELECT DISTINCT " . $filtersArray[0] . " FROM " . DBPREFIX . "module_dataviewer_" . $this->makeInputDBvalid($projectname) . $where . " ORDER BY " . $filtersArray[0] . " ASC";
        $objResult = $objDatabase->Execute($query);

        //create array with values from filters
        while (!$objResult->EOF) {
            $valuesArray[] = $objResult->fields[$filtersArray[0]];
            $objResult->MoveNext();
        }

        //special to have multilingual words for "country"
        if ($filtersArray[0] == "country") {
            $filterTemp = $filtersArray[0];
            $filterX = $_ARRAYLANG['TXT_COUNTRY'] = "Land";
        } else {
            $filterX = $filtersArray[0];
        }

        $_GET['filter'] = !empty($_GET['filter']) ? $_GET['filter']."," : "";


        //display the filters just if it the last before records view
        if(count($filtersArrayNotManipulated) !== count($selectedFilters)) {
            //create xhtml output
            foreach ($valuesArray as $content) {
                $xhtml = '<a href="index.php?section=dataviewer&amp;cmd=' . $projectname . '&amp;filter=' . $_GET['filter'] . $filtersArray[0] . '=';
                //makes iso_code_2 to Countryname
                if ($filtersArray[0] == "country") {
                    $contentX = $this->getLanguageName($content);
                } else {
                    $contentX = $content;
                }

                $xhtml .= htmlspecialchars($content) . '">' . $contentX . '</a>';

                $this->_objTpl->setVariable(array(
                    'DATAVIEWER_FILTER' => $xhtml
                ));
                $this->_objTpl->parse('dataviewer_filter_row');
            }
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
//        $input = str_replace(" ", "_",  $input);
//        $input = str_replace("Ä", "ae", $input);
//        $input = str_replace("ä", "ae", $input);
//        $input = str_replace("Ö", "oe", $input);
//        $input = str_replace("ö", "oe", $input);
//        $input = str_replace("Ü", "ue", $input);
//        $input = str_replace("ü", "ue", $input);
        $arrPattern["/[\+\/\(\)=,;%&]+/"] = "_"; // interpunction etc.
        $arrPattern['/[\'<>\\\~$!\"]+/']  =  "'_'";           // quotes and other special characters
        $arrPattern['/Ä/']                   = "ae";
        $arrPattern['/Ö/']                   = "oe";
        $arrPattern['/Ü/']                   = "ue";
        $arrPattern['/ä/']                   = "ae";
        $arrPattern['/ö/']                   = "oe";
        $arrPattern['/ü/']                   = "ue";
        $arrPattern['/à/']                   = "a";
        $arrPattern['/ç/']                   = "c";
        $arrPattern['/\s/']               = "_";
        $arrPattern['/[èé]/']               = "e";

        // Fallback for everything we didn't catch by now
        $arrPattern['/[^\sa-z_-]+/i']       = "_";
        $arrPattern['/[_-]{2,}/']         = "_";
        $arrPattern['/^[_\.\/\-]+/']       = "_";

        foreach ($arrPattern as $pattern => $replacement) {
            $input = preg_replace($pattern, $replacement, $input);
        }

        return strtolower($input);
    }
}


?>
