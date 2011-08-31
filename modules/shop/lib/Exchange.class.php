<?php

/**
 * OBSOLETE -- For a long time already
 * Exports and Imports CSV data
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author       Thomas Daeppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @version      0.1
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Exports and Imports CSV data
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author       Thomas Daeppen <thomas.daeppen@comvation.com>
 * @package     contrexx
 * @version      0.1
 * @subpackage  module_shop
 * @todo        Edit PHP DocBlocks!
 */
class Exchange
{
    /**
    * The template system object
    * @access private
    * @var object
    * @see __construct(), selectExchangeContent(), export(), import(), selectPage(), checkStep()
    */
    public $_objTpl;

    /**
    * The exchange method
    * @access private
    * @var string
    * @see selectExchangeContent(), selectPage()
    */
    public $strMethod;

    /**
    * Path to the export script
    * @access private
    * @var string
    * @see __construct(), export()
    */
    public $strExportLink;

    /**
    * Path of the temporary directory for the upload files
    * @access private
    * @var string
    * @see __construct(), import()
    */
    public $strImportPath;

    /**
    * Contains the step of each exchange method
    * @access private
    * @var array
    * @see __construct(), selectExchangeContent(), selectPage(), checkStep()
    */
    public $arrExchangeStep = array();

    /**
    * Contains the predefined steps of each exchange method
    * @access private
    * @var array
    * @see __construct(), selectPage(), checkStep()
    */
    public $arrExchangeSteps = array(
        'export'    => array(
            'selectTable',
            'selectCategories',
            'selectCols',
            'download',
        ),
        'import'    => array(
            'selectTable',
            'selectFiletype',
            'importOptions',
            'importRecords',
        ),
    );


    /**
    * The supported Filetypes
    * @access private
    * @var array
    * @see import()
    */
    public $arrFileTypes = array(
        'csv'    => array(
            "text/comma-separated-values",
            "application/vnd.ms-excel",
        ),
        'xml'    => array("text/xml"),
    );


    function __construct()
    {
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/shop/template/');
        $this->_objTpl->loadTemplateFile('module_shop_exchange.html');
        $this->arrExchangeStep = array(
            'export' => $this->arrExchangeSteps['export'][0],
            'import' => $this->arrExchangeSteps['import'][0]
        );
        $this->strExportLink = "/modules/shop/export.php";
        $this->strImportPath = ASCMS_PATH.'/modules/shop/tmp/';
    }


    /**
    * Select exchange content
    *
    * Selects the exchange content and returns it
    *
    * @access public
    * @return    object    Exchange template
    * @see selectPage(), export(), import()
    */
    function selectExchangeContent($method = "export", $step = "selectTable")
    {
        global $_ARRAYLANG;

           $this->strMethod = $method;
        $this->arrExchangeStep[$this->strMethod] = $step;

/*  TODO: This seems obsolete.  As does the whole class.
        // Check if we are in the right step
        if ($this->arrExchangeStep['import'] != $this->arrExchangeSteps['import'][0]) {
//$this->checkStep('import');
        }
        if (!$this->arrExchangeStep['export'] != $this->arrExchangeSteps['export'][0]) {
//$this->checkStep('export');
        }
*/

        // Selects the export page and executes the export function
        $this->selectPage('export',$this->arrExchangeStep['export']);
        $this->export($this->arrExchangeStep['export']);

        // Selects the import page and executes the import function
        $this->selectPage('import',$this->arrExchangeStep['import']);
        $this->import($this->arrExchangeStep['import']);

        //set language variables
         $this->_objTpl->setVariable(array(
            'TXT_EXPORT'            => $_ARRAYLANG['TXT_EXPORT'],
            'TXT_IMPORT'            => $_ARRAYLANG['TXT_IMPORT'],
            'TXT_NEXT'                => $_ARRAYLANG['TXT_NEXT']
        ));
        print "<br><br>";
        print_r($_SESSION);
        return $this->_objTpl->get();
    }

    /**
    * Export data
    *
    * Exports the selected data
    *
    * @access private
    * @see checkStep()
    */
    function export_DEV($step)
    {
        global $objDatabase, $_ARRAYLANG;

//        print "EXPORT";
//        $this->selectPage('export',$this->arrExchangeStep['export']);
        switch ($step) {
            case 'none':
                $this->arrExchangeStep['export'] = $this->arrExchangeSteps['export'][0];
                break;

            case 'selectTable': // Generate the first page with the table selection
                unset($_SESSION['shop_exchange_export']);
                print "HALLO";
                $_SESSION['shop_exchange_export']['tables'] = array(
                    array(
                        'name'    => DBPREFIX.'module_shop_products',
                        'text'    => "Produkte"
                        ),
                    array(
                        'name'    => DBPREFIX.'module_shop_customers',
                        'text'    => "Kunden"
                        ));
                $this->_objTpl->setCurrentBlock('tablesExport');
                for ($i=0;$i<count($_SESSION['shop_exchange_export']['tables']);$i++) {
                    $this->_objTpl->setVariable(array(
                            'TABLE_NAME'        => $_SESSION['shop_exchange_export']['tables'][$i]['name'],
                            'TXT_TABLE_NAME'    => $_SESSION['shop_exchange_export']['tables'][$i]['text']
                            ));
                    $this->_objTpl->parseCurrentBlock();
                }

                $_SESSION['shop_exchange_export']['step_old'] = $step;
                break;

            case 'selectCategories':
                // Check if we are in the right step
//                if ($this->checkStep('export')) {break;}

                // Gets the selected table
                for ($i=0;$i<count($_SESSION['shop_exchange_export']['tables']);$i++) {
                    if (in_array($_POST['table'],$_SESSION['shop_exchange_export']['tables'][$i])) {
                        $_SESSION['shop_exchange_export']['table'] = array(
                                'name'    => $_SESSION['shop_exchange_export']['tables'][$i]['name'],
                                'text'    => $_SESSION['shop_exchange_export']['tables'][$i]['text']
                                );
                        break;
                    }
                }
                unset($_SESSION['shop_exchange_export']['tables']);

                switch ($_SESSION['shop_exchange_export']['table']['name'])
                {
                    case DBPREFIX."module_shop".MODULE_INDEX."_products":
                        // Gets the product selection list
                        $query = "SELECT catid, parent_id, catname
                                  FROM ".DBPREFIX."module_shop".MODULE_INDEX."_categories";
                        if (!($objResult = $objDatabase->Execute($query))) {
                            $i=0;
                            while (!$objResult->EOF) {
                                $arrCategorie[$i] = array(
                                                        'catid'        => $objResult->fields['catid'],
                                                        'parent_id'    => $objResult->fields['parent_id'],
                                                        'catname'    => $objResult->fields['catname']
                                                          );
                                $i++;
                                $objResult->MoveNext();
                            }

                            // Generates the categorie lists
                            for ($i=0;$i<count($arrCategorie);$i++) {
                                if ($arrCategorie[$i]['parent_id'] != 0) {
                                    $arrCategorie[$i]['catname'] = $arrCategorie[$arrCategorie[$i]['parent_id']]['catname']."_".$arrCategorie[$i]['catname'];
                                }
                                $this->_objTpl->setCurrentBlock('categorieList_selectCategories');
                                $this->_objTpl->setVariable(array(
                                        'CATEGORIE_ID'        => $arrCategorie[$i]['catid'],
                                        'CATEGORIE_NAME'    => $arrCategorie[$i]['catname']
                                        ));
                                $this->_objTpl->parseCurrentBlock();
                            }
                        }

                        // set language variables
                        $this->_objTpl->setVariable(array(
                                'TXT_SELECT_CATEGORIES_TO_EXPORT' => $_ARRAYLANG['TXT_SELECT_CATEGORIES_TO_EXPORT']
                                ));
                        break;

                    default:
                        // Go to the next page
                        $_SESSION['shop_exchange_export']['step_old'] = $step;
                        $this->selectExchangeContent("export","selectCols");
                        break 2;
                }

                $_SESSION['shop_exchange_export']['step_old'] = $step;
                break;

            case 'selectCols':
                // Check if we are in the right step
//                if ($this->checkStep('export')) {break;}

                $this->_objTpl->setVariable(array(
                        'FILENAME' => $_SESSION['shop_exchange_export']['table']['text']
                        ));

                switch ($_SESSION['shop_exchange_export']['table']['name']) {
                    case DBPREFIX."module_shop".MODULE_INDEX."_products":
                        $this->_objTpl->setCurrentBlock('categorieList_selectCols');
                        for ($i=0;$i<count($_REQUEST['categories']);$i++) {
                            $_SESSION['shop_exchange_export']['products']['categories'][$i] = array(
                                    'id' => $_REQUEST['categories'][$i]
                                    );
                        }
                        break;
                    default:
                        break;
                }

                // Gets the cols of the selected table
                $query = "SELECT * FROM ".$_SESSION['shop_exchange_export']['table']['name'];
                $objDatabase->Execute($query);

                // FIXME
                // Replace $objDatabase->metadata() with $objDatabase-> and the corresponding method
                $arrMetadata = $objDatabase->metadata();

                $this->_objTpl->setCurrentBlock('Cols');
                for ($i=0;$i<count($arrMetadata);$i++) {
                    $this->_objTpl->setVariable(array(
                            'COL' => $arrMetadata[$i]['name']
                            ));
                    $this->_objTpl->parseCurrentBlock();
                }

                $_SESSION['shop_exchange_export']['step_old'] = $step;
                break;

            case 'download':
                // Check if we are in the right step
//                if ($this->checkStep('export')) {break;}

                $_SESSION['shop_exchange_export']['file']['type'] = $_POST['type'];
                $_SESSION['shop_exchange_export']['cols'] = $_POST['cols'];

                // Generates the download link
                switch ($_SESSION['shop_exchange_export']['file']['type']) {
                    case "csv":
                        $link = $this->strExportLink.'?content=SELECT ';
                        for ($i=0;$i<count($_SESSION['shop_exchange_export']['cols']);$i++) {
                            if ($i>0) {
                                $link .= ",";
                            }
                            $link .= $_SESSION['shop_exchange_export']['cols'][$i];
                        }
                        switch ($_SESSION['shop_exchange_export']['table']['name']) {
                            case DBPREFIX."module_shop".MODULE_INDEX."_products":
                                $link .=" FROM ".$_SESSION['shop_exchange_export']['table']['name']." WHERE ";
                                $first = true;
                                for ($i=0;$i<count($_SESSION['shop_exchange_export']['products']['categories']);$i++) {
                                        if ($first) {
                                            $link .= "catid=";
                                            $first = false;
                                        } else {
                                            $link .= " OR catid=";
                                        }
                                        $link .= $_SESSION['shop_exchange_export']['products']['categories'][$i]['id'];
                                }
                                break;
                            default:
                                $link .=" FROM ".$_SESSION['shop_exchange_export']['table']['name']." ";
                                break;
                        }
                        $link .="&amp;name=".$_POST['filename'].".".$_SESSION['shop_exchange_export']['file']['type']."&amp;type=sql";
                        break;
                    case "xml":
                        $link = "xml";
                        break;
                }
                $this->_objTpl->setVariable(array(
                        'LINK' => addslashes($link),
                        'LINK_NAME' => $_POST['filename'].".".$_SESSION['shop_exchange_export']['file']['type']));
                break;
        }
    }

    /**
    * Import
    *
    * Imports data from a file
    *
    * @access private
    * @see checkStep()
    */
    function import_DEV($step)
    {
        global $objDatabase, $_ARRAYLANG;
        print "IMPORT";
//        $this->selectPage('import',$this->arrExchangeStep['import']);
        switch ($step) {
            case 'none':
                $this->arrExchangeStep['import'] = $this->arrExchangeSteps['import'][0];
                break;

            case 'selectTable': // Generate the first page with the table selection
                unset($_SESSION['shop_exchange_import']);
                print "ADE";
                $_SESSION['shop_exchange_import']['tables'] = array(
                        array(
                                'name'    => DBPREFIX.'module_shop_products',
                                'text'    => "Produkte",
                                'id'    => "id"
                                ),
                        array(
                                'name'    => DBPREFIX.'module_shop_customers',
                                'text'    => "Kunden",
                                'id'    => "customerid"
                                ));

                $this->_objTpl->setCurrentBlock('tablesImport');
                for ($i=0;$i<count($_SESSION['shop_exchange_import']['tables']);$i++) {
                    $this->_objTpl->setVariable(array(
                            'TABLE_NAME'        => $_SESSION['shop_exchange_import']['tables'][$i]['name'],
                            'TXT_TABLE_NAME'    => $_SESSION['shop_exchange_import']['tables'][$i]['text']
                            ));
                    $this->_objTpl->parseCurrentBlock();
                }

                $_SESSION['shop_exchange_import']['step_old'] = $step;
                break;

            case 'selectFiletype': // Gets the file type of the file to import and displays some file type specifies option
                // Check if we are in the right step
//                if ($this->checkStep('import')) {break;}

                $_SESSION['shop_exchange_import']['file'] = $_FILES['file'];
                $this->_objTpl->setVariable(array(
                        'FILENAME'    => $_SESSION['shop_exchange_import']['file']['name']
                        ));

                // Gets the selected table
                for ($i=0;$i<count($_SESSION['shop_exchange_import']['tables']);$i++) {
                    if (array_search($_POST['table'],$_SESSION['shop_exchange_import']['tables'][$i])) {
                        $_SESSION['shop_exchange_import']['table'] = array(
                                'name'    => $_SESSION['shop_exchange_import']['tables'][$i]['name'],
                                'text'    => $_SESSION['shop_exchange_import']['tables'][$i]['text'],
                                'id'    => $_SESSION['shop_exchange_import']['tables'][$i]['id']
                                );
                        break;
                    }
                }
                unset($_SESSION['shop_exchange_import']['tables']);

                // Generates the file type dropdown list
                $this->_objTpl->setCurrentBlock('importTypes');
                foreach ($this->arrFileTypes as $type => $description) {
                    $this->_objTpl->setVariable(array(
                            'TYPE'            => $type,
                            'DESCRIPTION'    => $description[0]
                            ));
                    if (in_array($_SESSION['shop_exchange_import']['file']['type'],$description)) {
                        if (move_uploaded_file($_SESSION['shop_exchange_import']['file']['tmp_name'], $this->strImportPath.$_SESSION['shop_exchange_import']['file']['name'])) {
                            $fileType = $type;
                            $this->_objTpl->setVariable(array(
                                    'SELECTED' => "selected"
                                    ));
                        } else {
                            // Display the import page with a status message
                            $this->_objTpl->setVariable(array(
                                    'CONTENT_STATUS' => "TXT_FILE_COULDNT_BE_UPLOADED"
                                    ));
                            $this->selectExchangeContent("import","selectTable");
                            break;
                        }
                    }
                    $this->_objTpl->parseCurrentBlock();
                }

                // Displays file type specifies options
                switch ($fileType)
                {
                    case "csv":
                        $this->_objTpl->touchBlock('csvOptions');
                        break;

                    case "xml":
                        break;

                    default:
                        $this->_objTpl->setVariable(array(
                                'TYPE'            => "none",
                                'DESCRIPTION'    => "TXT_FILETYPE_NOT_KNOWN",
                                'SELECTED'        => "selected"
                                ));
                        $this->_objTpl->parseCurrentBlock();
                }

                $_SESSION['shop_exchange_import']['step_old'] = $step;
                break;

            case 'importOptions': // Displays the col assignment and the import options
                // Check if we are in the right step
//                if ($this->checkStep('import')) {break;}

                $arrImportCols = array();

                $_SESSION['shop_exchange_import']['file']['type'] = $_POST['filetype'];
                $_SESSION['shop_exchange_import']['file']['separator'] = $_POST['separator'];

                // Gets the col names of the selected table
                $query = "SELECT *
                                 FROM ".$_SESSION['shop_exchange_import']['table']['name'];
                $objResult = $objDatabase->Execute($query);

                // FIXME
                // Replace $objDatabase->metadata() with $objDatabase-> and the corresponding method
                $arrMetadata = $objDatabase->metadata();

                // Gets the content of the file to import
                $fileContent = file_get_contents($this->strImportPath.$_SESSION['shop_exchange_import']['file']['name']);

                // Put the cols and the cells from the content of the file into arrays
                switch ($_SESSION['shop_exchange_import']['file']['type']) {
                    case "csv":
                        $strStart = 0;
                        for ($i=0;$i<strlen($fileContent);$i++) {
                            // If the end of line is reached, continue to read the cells
                            if (ord(substr($fileContent,$i,1)) == 13 && ord(substr($fileContent,$i+1,1)) == 10) {
                                array_push($arrImportCols,substr($fileContent,$strStart,$i-$strStart));
                                $strStart = $i+2;
                                $col=0;
                                $row=0;
                                for ($j=$strStart;$j<strlen($fileContent);$j++) {
                                    if (ord(substr($fileContent,$j,1)) == 13 && ord(substr($fileContent,$j+1,1)) == 10)
                                    {
                                        $arrImportRows[$row][$arrImportCols[$col]] = substr($fileContent,$strStart,$j-($strStart));
                                        $strStart = $j+2;
                                        $col=0;
                                        $row++;
                                    }
                                    if (substr($fileContent,$j,1) == $_SESSION['shop_exchange_import']['file']['separator']) {
                                        $arrImportRows[$row][$arrImportCols[$col]] = substr($fileContent,$strStart,$j-$strStart);
                                        $strStart = $j+1;
                                        $col++;
                                    }
                                }
                                break;
                            }
                            if (substr($fileContent,$i,1) == $_SESSION['shop_exchange_import']['file']['separator']) {
                                array_push($arrImportCols,substr($fileContent,$strStart,$i-$strStart));
                                $strStart = $i+1;
                            }
                        }
                        break;

                    case "xml":
                        break;

                    default:
                        // Display the import page with a status message
                        $this->_objTpl->setVariable(array(
                                'CONTENT_STATUS' => "TXT_THIS_FILETYPE_IS_NOT_SUPPORTED"
                                ));
                        $this->selectExchangeContent("import","selectTable");
                }

                $this->_objTpl->setVariable(array(
                        'FILENAME' => $_SESSION['shop_exchange_import']['file']['name']
                        ));

                $_SESSION['shop_exchange_import']['importCols'] = $arrImportCols;
                $_SESSION['shop_exchange_import']['importRows'] = $arrImportRows;

                // Generates the col assignment list
                foreach ($arrImportCols as $colName) {
                    $isSelected = false;
                    $this->_objTpl->setCurrentBlock('colList');
                    for ($i=0;$i<count($arrMetadata);$i++) {
                        $this->_objTpl->setVariable(array(
                                'COL_DB' => $arrMetadata[$i]['name'],
                                'COL_DB_TEXT' => $arrMetadata[$i]['name']
                                ));
                        if ($arrMetadata[$i]['name'] == $colName) {
                            $this->_objTpl->setVariable(array(
                                    'SELECTED' => "selected"
                                    ));
                            $isSelected = true;
                        }
                        $this->_objTpl->parseCurrentBlock('colList');
                    }
                    $this->_objTpl->setVariable(array(
                            'COL_DB' => "",
                            'COL_DB_TEXT' => "TXT_NOT_ASSIGNED"
                            ));
                    if (!$isSelected) {
                        $this->_objTpl->setVariable(array(
                                'SELECTED' => "selected"
                                ));
                    }
                    $this->_objTpl->parseCurrentBlock('colList');
                    $this->_objTpl->setCurrentBlock('colAssignment');
                    $this->_objTpl->setVariable(array(
                            'COL_IMPORT' => $colName
                            ));
                    $this->_objTpl->parseCurrentBlock('colAssignment');
                }

                $_SESSION['shop_exchange_import']['step_old'] = $step;
                break;

            case 'importRecords': // Import the records into the database table
                // Check if we are in the right step
//                if ($this->checkStep('import')) {break;}

                $arrIds = array();
                $arrKeepIds = array();
                $arrProducts = array();

                // Assign the cols of the file to the cols of the database table
                foreach ($_SESSION['shop_exchange_import']['importCols'] as $key) {
                    $arrImportCols[$key] = $_POST[$key];
                }
                $_SESSION['shop_exchange_import']['importCols'] = $arrImportCols;

                // Delete the ID col assignment if requested
                if ($_POST['optionId'] == "dontImport") {
                    $_SESSION['shop_exchange_import']['importCols'][$_SESSION['shop_exchange_import']['table']['id']] = "";
                }

                // Get the existing IDs from the database table
                $query = "SELECT ".$_SESSION['shop_exchange_import']['table']['id']." FROM ".$_SESSION['shop_exchange_import']['table']['name'];
                $objResult = $objDatabase->Execute($query);
                while (!$objResult->EOF) {
                    array_push($arrIds,$objResult->fields[$_SESSION['shop_exchange_import']['table']['id']]);
                    $objResult->MoveNext();
                }

                // Clear the table
                if ($_POST['optionImport'] == "del") {
                    switch ($_SESSION['shop_exchange_import']['table']['name']) {
                        case DBPREFIX.'module_shop_products':
                            // Check if there are products that are still in use by an ordering
                            $query = "SELECT productid FROM ".DBPREFIX.'module_shop_order_items';
                            $objResult = $objDatabase->Execute($query);
                            while (!$objResult->EOF) {
                                if (in_array($objResult->fields['productid'],$arrIds)) {
                                    array_push($arrProducts,$objResult->fields['productid']);
                                }
                                $objResult->MoveNext();
                            }
                            // Generate a list of data records to keep in the table
                            for ($i=0;$i<count($arrIds);$i++) {
                                if (in_array($arrIds[$i],$arrProducts)) {
                                    array_push($arrKeepIds,$arrIds[$i]);
                                } else {
                                    $arrIds[array_search($arrIds[$i],$arrIds)] = "";
                                }
                            }
                            break;
                    }

                    // Generate a string of data records to keep in the table
                    $first = true;
                    foreach ($arrKeepIds as $id) {
                        if ($first) {
                            $strIds .= " WHERE ";
                            $first = false;
                        }
                        $strIds .= "id != '".$id."' OR ";
                    }

                    // Finally delete the data records which we dont have to keep
                    $query = "DELETE FROM ".$_SESSION['shop_exchange_import']['table']['name'].substr($strIds,0,strlen($strIds)-4);
                    $objDatabase->Execute($query);
                }

                // Prepare the SQL-INSERT queries
                $i = 0;
                foreach ($_SESSION['shop_exchange_import']['importRows'] as $row) {
                    $strCols = "";
                    $strValues = "";
                    foreach ($row as $key => $value) {
                        if ($_SESSION['shop_exchange_import']['importCols'][$key])
                        {
                            // Delete the ID-col value if it exists already in the database table
                            if ($_SESSION['shop_exchange_import']['importCols'][$key] == $_SESSION['shop_exchange_import']['table']['id']) {
                                if (in_array($value,$arrIds)) {
                                    $value = "";
                                } else {
                                    array_push($arrIds,$value);
                                }
                            }
                            $strCols .= $_SESSION['shop_exchange_import']['importCols'][$key].",";
                            $strValues .= "'".$value."',";
                        }
                    }
                    $arrQuery[$i] = "INSERT INTO ".$_SESSION['shop_exchange_import']['table']['name']." (".substr($strCols,0,strlen($strCols)-1).") VALUES (".substr($strValues,0,strlen($strValues)-1).")";
                    $i++;
                }

                // Execute the SQL-INSERT queries
                foreach ($arrQuery as $query) {
                    $objDatabase->Execute($query);
                }

                // Display the import page with a status message
                $this->_objTpl->setVariable(array(
                        'CONTENT_STATUS' => "Die Datei ".$_SESSION['shop_exchange_import']['file']['name']." wurde importiert."
                        ));
                $this->selectExchangeContent("import","selectTable");
                break;
        }
    }

    /**
    * Select page content
    *
    * Selects which pages should be shown
    *
    * @access private
    */
    function selectPage($method,$step)
    {
        for ($i=0;$i<count($this->arrExchangeSteps[$method]);$i++) {
            if (($this->arrExchangeSteps[$method][$i] == $step and $this->strMethod == $method) or ($i == 0 and $this->strMethod != $method)) {
                $this->_objTpl->touchBlock($method.'_'.$this->arrExchangeSteps[$method][$i]);
            } else {
                $this->_objTpl->hideBlock($method.'_'.$this->arrExchangeSteps[$method][$i]);
            }
        }

        // Selects the page for putting in foreground
        $this->_objTpl->setVariable(array(
                'EXPORT_STYLE' => "display:none;"
                ));
        $this->_objTpl->setVariable(array(
                'IMPORT_STYLE' => "display:none;"
                ));
        $this->_objTpl->setVariable(array(
                strtoupper($this->strMethod).'_STYLE' => "display:block;"
                ));

    }

    /**
    * Check exchange step
    *
    * Checks if the script is doing the right step
    *
    * @access private
    * @return    boolean    If we can proceed with the exchange functions
    * @see selectExchangeContent()
    */
    function checkStep($method)
    {
        if ($_SESSION['shop_exchange_'.$method]['step_old'] != $this->arrExchangeSteps[$method][array_search($this->arrExchangeStep[$method],$this->arrExchangeSteps[$method])-1]) {
            //Display the import page with a status message
            $this->_objTpl->setVariable(array(
                    'CONTENT_STATUS' => "TXT_".strtoupper($method)."_PROCEDURE_WAS_NOT_VALID_ANY_LONGER"
                    ));
            //$this->arrExchangeStep['export'] = "none";
            //$this->arrExchangeStep['import'] = "none";
            $this->selectExchangeContent($method,"selectTable");
            return true;
        } else {
            return false;
        }
    }
}
?>
