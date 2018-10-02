<?php declare(strict_types=1);

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */

namespace Cx\Modules\CHDIRTravelLog\Controller;

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_chdirtravellog
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController {

    /**
     * Use this to parse your frontend page
     *
     * You will get a template based on the content of the resolved page
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template containing content of resolved page
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd) {
        // this class inherits from Controller, therefore you can get access to
        // Cx like this:
        $this->cx;

        // Controller routes all calls to undeclared methods to your
        // ComponentController. So you can do things like
        $this->getName();
    }

// TODO from index (class TravelLog)
    /**
     * Travel Log (Nemesis)
     */
    public $_objTpl;
    public $pageContent;
    public $moduleName = "travellog";

    private $dataRoot;
    private $dataWebRoot;

    private $projectName = array();

    private $csvSeparator = ';';

    /**
     * Constructor
     */
    function __construct($pageContent)
    {
        global $_ARRAYLANG, $_CORELANG;

   // DBG::activate();

        $this->dataRoot = ASCMS_DOCUMENT_ROOT.'/travellog/';
        $this->dataWebRoot = ASCMS_PATH_OFFSET.'/travellog/';

        $this->loadConfiguration();
        $this->syncData();

        $this->_objTpl = new \Cx\Core\Html\Sigma('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_objTpl->setGlobalVariable(array(
            'MODULE_NAME' =>  $this->moduleName,
            'CSRF' =>  'csrf='.CSRF::code(),
        ));

        $this->pageContent = $pageContent;
    }


    /**
     * get page
     *
     * Reads the act and selects the right action
     *
     * @access   public
     * @return   string  parsed content
     */
    function getPage()
    {
        global $_CONFIG;

        JS::activate('jquery');


        switch ($_REQUEST['cmd']) {
            default:
                $this->showSearch();
        }

        return $this->_objTpl->get();
    }

    function showSearch()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_CONFIG;

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        if($_GET['type'] == 'datasheet_nr') {
            $selectConnectionNr = '';
            $selectPaperNr = 'selected="selected"';
        } else {
            $selectPaperNr = '';
            $selectConnectionNr = 'selected="selected"';
        }

        if(isset($_GET['number'])) {
            $number = $_GET['number'];
        }

        if(isset($_GET['search'])) {
            $searchTerm = $_GET['number'];
            $pos = intval($_GET['pos']);

            switch($_GET['type']) {
                case 'datasheet_nr':
                    $query = "SELECT STR_TO_DATE(j.`REISEDAT`,'%d.%m.%Y') AS resedat, j.`REISEDAT`, j.`VERBNR`, j.`RBN`, j.`REISEN` FROM ".DBPREFIX."module_".$this->moduleName."_journey AS j WHERE (`ATT` NOT LIKE '111') AND (`D` NOT LIKE 'X') AND (`RBN` = '".stripslashes($searchTerm)."') ORDER BY `resedat` ASC";

                    $objNumDatasheets = $objDatabase->Execute($query);
                    $objDatasheets = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
                    $numDatasheets = intval($objNumDatasheets->RecordCount());

                    if($numDatasheets > $_CONFIG['corePagingLimit']) {
                        $paging = getPaging($numDatasheets, $pos, null, "<b>".$_ARRAYLANG['TXT_MEDIADIR_ENTRIES']."</b>", true, $_CONFIG['corePagingLimit']);
                    } else {
                        $paging = null;
                    }

                    if ($objDatasheets !== false) {
                        while (!$objDatasheets->EOF) {
                            $connectionNr = floor($objDatasheets->fields['VERBNR']);
                            $query2 = "SELECT `Verbindungsstring` FROM ".DBPREFIX."module_".$this->moduleName."_connection WHERE (`Verbindungsnummer` = '".$connectionNr."') LIMIT 1";
                            $objConnection = $objDatabase->Execute($query2);

                            if ($objConnection !== false) {
                                $connectionName = $objConnection->fields['Verbindungsstring'];
                            }

                            $this->_objTpl->setVariable(array(
                                'TRAVELLOG_CONNECTION_RBN_LINK'             => $this->getFileLink($searchTerm),
                                'TRAVELLOG_CONNECTION_RBN'                  => $objDatasheets->fields['RBN'],
                                'TRAVELLOG_CONNECTION_DATE'                 => $objDatasheets->fields['REISEDAT'],
                                'TRAVELLOG_CONNECTION_NUM_OF_CONNECTION'    => $objDatasheets->fields['REISEN'],
                                'TRAVELLOG_CONNECTION_CONNECTION_NR'        => $objDatasheets->fields['VERBNR'],
                                'TRAVELLOG_CONNECTION_NAME'                 => $connectionName,
                            ));

                            $this->_objTpl->parse('travellog_connection_results');

                            $objDatasheets->MoveNext();
                        }
                    }

                    $this->_objTpl->setVariable(array(
                        'TRAVELLOG_SELECTED_DATASHEET'      => '<a target="_blank" href="'.$this->dataWebRoot.'pdf/'.$this->projectName.'_'.$searchTerm.'.pdf">'.$searchTerm.'</a>',
                        'TRAVELLOG_NUM_CONNECTIONS'         => $numDatasheets,
                        'TRAVELLOG_PAGING'                  => $paging,
                        'TRAVELLOG_EXPORT_RESULTS'         => '<a href="'.$_SERVER['REQUEST_URI'].'&csv=1"><img src="images/modules/downloads/xls.gif" style="height: 20px; width: auto; margin: 0 0 15px 0 !important; float: right;" alt="'.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_ALT'].'" title="'.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_TITLE'].'" /></a>',
                    ));

                    $this->_objTpl->parse('travellog_connections');
                    $this->_objTpl->hideBlock('travellog_datasheets');
                    break;
                case 'connection_nr':
                    $arrConnection = explode(".", $searchTerm);
                    $query = "SELECT `Verbindungsstring` FROM ".DBPREFIX."module_".$this->moduleName."_connection WHERE `Verbindungsnummer` = '".intval($arrConnection[0])."'";

                    $objConnection = $objDatabase->SelectLimit($query, 1, 0);

                    if ($objConnection !== false) {
                        $connectionName = $objConnection->fields['Verbindungsstring'];
                    }

                    if(!empty($arrConnection[1])) {
                        $regex = "LIKE '".stripslashes($searchTerm)."'";
                    } else {
                        $regex = "REGEXP '^".stripslashes($arrConnection[0])."[.]'";
                    }

                    $query = "SELECT STR_TO_DATE(`REISEDAT`,'%d.%m.%Y') AS resedat, `REISEDAT`, `VERBNR`, `RBN`, `REISEN` FROM ".DBPREFIX."module_".$this->moduleName."_journey WHERE `ATT` NOT LIKE '111' AND `D` NOT LIKE 'X' AND `VERBNR` ".$regex." ORDER BY `RBN` ASC, `resedat` ASC";

                    $objNumDatasheets = $objDatabase->Execute($query);
                    $objDatasheets = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);
                    $numDatasheets = intval($objNumDatasheets->RecordCount());

                    if($numDatasheets > $_CONFIG['corePagingLimit']) {
                        $paging = getPaging($numDatasheets, $pos, null, "<b>".$_ARRAYLANG['TXT_MEDIADIR_ENTRIES']."</b>", true, $_CONFIG['corePagingLimit']);
                    } else {
                        $paging = null;
                    }

                    if ($objDatasheets !== false) {
                        while (!$objDatasheets->EOF) {

                            $this->_objTpl->setVariable(array(
                                'TRAVELLOG_DATASHEET_RNB_LINK'              => $this->getFileLink($objDatasheets->fields['RBN']),
                                'TRAVELLOG_DATASHEET_RNB'                   => $objDatasheets->fields['RBN'],
                                'TRAVELLOG_DATASHEET_DATE'                  => $objDatasheets->fields['REISEDAT'],
                                'TRAVELLOG_DATASHEET_NUM_OF_CONNECTION'     => $objDatasheets->fields['REISEN'],
                                'TRAVELLOG_DATASHEET_CONNECTION_NR'         => $objDatasheets->fields['VERBNR'],
                                'TRAVELLOG_DATASHEET_CONNECTION_NAME'       => $connectionName,
                            ));

                            $this->_objTpl->parse('travellog_datasheet_results');

                            $objDatasheets->MoveNext();
                        }
                    }

                    $this->_objTpl->setVariable(array(
                        'TRAVELLOG_SELECTED_CONNECTION'     => $searchTerm." ".$connectionName,
                        'TRAVELLOG_PAGING'                  => $paging,
                        'TRAVELLOG_NUM_DATASHEETS'          => $numDatasheets,
                        'TRAVELLOG_EXPORT_RESULTS'         => '<a href="'.$_SERVER['REQUEST_URI'].'&csv=1"><img src="images/modules/downloads/xls.gif" style="height: 20px; width: auto; margin: 0 0 15px 0 !important; float: right;" alt="'.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_ALT'].'" title="'.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_TITLE'].'" /></a>',
                    ));

                    $this->_objTpl->parse('travellog_datasheets');
                    $this->_objTpl->hideBlock('travellog_connections');
                    break;
            }

            if(intval($_GET['csv']) == 1) {
                $this->getCsvExport($_GET['type']."_".$_GET['number'], $query);
            }

            if(intval($objDatasheets->RecordCount()) == 0) {
                $this->_objTpl->touchBlock('travellog_no_results');
                $this->_objTpl->hideBlock('travellog_datasheets');
                $this->_objTpl->hideBlock('travellog_connections');
            }
        } else {
            $this->_objTpl->hideBlock('travellog_datasheets');
            $this->_objTpl->hideBlock('travellog_connections');
        }

        $this->_objTpl->setVariable(array(
            'TXT_SEARCH'                        => $_CORELANG['TXT_SEARCH'],
            'TRAVELLOG_NUMBER'                  => $searchTerm,
            'TRAVELLOG_SELECTED_CONNECTION_NR'  => $selectConnectionNr,
            'TRAVELLOG_SELECTED_PAPER_NR'       => $selectPaperNr,
        ));
    }

    function syncData()
    {
        $lastSyncFile = $this->dataRoot."lastsync.txt";
        $connectionsFile = $this->dataRoot.$this->projectName."_Verbindungen.csv";
        $journeysFile = $this->dataRoot.$this->projectName."_FAHRT.csv";

        if (file_exists($lastSyncFile)) {
            $lastSyncFile = fopen($lastSyncFile, "r");
            while(!feof($lastSyncFile)) {
                $lastSync = trim(fgets($lastSyncFile));
            }
            fclose($lastSyncFile);

            //$lastSync=0;

            if(filemtime($connectionsFile) > $lastSync || filemtime($journeysFile) > $lastSync) {
                $this->arrConnections = $this->csv2db('connection', $connectionsFile);
                $this->arrJourneys = $this->csv2db('journey', $journeysFile);

                $handle = fopen($this->dataRoot."lastsync.txt", 'w') or die('Cannot open file:  '.$lastSyncFile);
                fwrite($handle, mktime());
                fclose($handle);
            }
        } else {
            die("Unable to open configfile (".$this->dataRoot."lastsync.txt)!");
        }
    }

    function loadConfiguration()
    {
        $configFilename = fopen($this->dataRoot."filename.txt", "r") or die("Unable to open configfile (".$this->dataRoot."filename.txt)!");
        while(!feof($configFilename)) {
            $this->projectName = trim(fgets($configFilename));
        }
        fclose($configFilename);
    }

    function csv2db($tablename='', $filename='')
    {
        global $objDatabase;

        if(!file_exists($filename) || !is_readable($filename)) {
             die("Unable to open file (".$filename.")!");
        }

        $header = null;
        $data = array();

        $objDatabase->Execute("TRUNCATE TABLE ".DBPREFIX."module_".$this->moduleName."_".$tablename);

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $this->csvSeparator)) !== false) {
                if(!$header) {
                    $header = $row;
                    if($tablename == 'journey') {
                        array_pop($header);
                    }
                    if($tablename == 'connection') {
                        unset($header[1]);
                    }

                }else {
                    if($tablename == 'journey') {
                        array_pop($row);
                    }
                    if($tablename == 'connection') {
                        unset($row[1]);
                    }

                    $objDatabase->Execute("INSERT INTO ".DBPREFIX."module_".$this->moduleName."_".$tablename." (`".join('`, `', $header)."`) VALUES ('".utf8_encode(join('\', \'', $row))."')");
                }
            }
            fclose($handle);
        }
    }

    function getFileLink($datasheetNr, $urlOnly=null)
    {
        global $_ARRAYLANG;

        $datasheetLink1 = $this->dataWebRoot.'pdf/'.$this->projectName.'_'.$datasheetNr.'.pdf';
        $datasheetPath1 = $this->dataRoot.'pdf/'.$this->projectName.'_'.$datasheetNr.'.pdf';
        $datasheetLink2 = $this->dataWebRoot.'pdf/'.$this->projectName.'_'.$datasheetNr.'_F.pdf';
        $datasheetPath2 = $this->dataRoot.'pdf/'.$this->projectName.'_'.$datasheetNr.'_F.pdf';

        $dataSheetIcon = '<img src="images/modules/downloads/pdf.gif" style="height: 20px; width: auto; margin: 0 !important;" alt="'.$_ARRAYLANG['TXT_TRAVELLOG_DOWNLOAD_ICON_ALT'].'" title="'.$_ARRAYLANG['TXT_TRAVELLOG_DOWNLOAD_ICON_TITLE'].'" />';

        if(file_exists($datasheetPath1)) {
            if($urlOnly) {
                return 'http://www.voev.ch'.$datasheetLink1;
            } else {
                return '<a target="_blank" href="'.$datasheetLink1.'" >'.$dataSheetIcon.'</a>';
            }
        }

        if(file_exists($datasheetPath2)) {
            if($urlOnly) {
                return 'http://www.voev.ch'.$datasheetLink2;
            } else {
                return '<a target="_blank" href="'.$datasheetLink2.'" >'.$dataSheetIcon.'</a>';
            }
        }

        if($urlOnly) {
            return null;
        } else {
            return '<img src="images/modules/downloads/_blank.gif" style="margin: 0 !important;" alt="'.$_ARRAYLANG['TXT_TRAVELLOG_NO_DOWNLOAD_FOUND'].'" title="'.$_ARRAYLANG['TXT_TRAVELLOG_NO_DOWNLOAD_FOUND'].'" />';
        }
    }

    function getCsvExport($filename, $query)
    {
        global $_ARRAYLANG, $objDatabase;

        $filename = $this->escapeCsvValue($filename).".csv";

        header("Content-Type: text/comma-separated-values; charset=".CONTREXX_CHARSET, true);
        header("Content-Disposition: attachment; filename=\"$filename\"", true);

        print($_ARRAYLANG['TXT_TRAVELLOG_EXPORT_DATE'].$this->csvSeparator.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_CONNECTION'].$this->csvSeparator.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_NAME'].$this->csvSeparator.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_NUM'].$this->csvSeparator.$_ARRAYLANG['TXT_TRAVELLOG_EXPORT_DATASHEET']);

        print ("\r\n");

        $objExport = $objDatabase->Execute($query);

        if ($objExport !== false) {
            while (!$objExport->EOF) {
                $connectionNr = floor($objExport->fields['VERBNR']);
                $query2 = "SELECT `Verbindungsstring` FROM ".DBPREFIX."module_".$this->moduleName."_connection WHERE (`Verbindungsnummer` = '".$connectionNr."') LIMIT 1";
                $objConnection = $objDatabase->Execute($query2);

                if ($objConnection !== false) {
                    $connectionName = $objConnection->fields['Verbindungsstring'];
                }

                print($this->escapeCsvValue($objExport->fields['REISEDAT']).$this->csvSeparator);
                print($this->escapeCsvValue($objExport->fields['VERBNR']).$this->csvSeparator);
                print($this->escapeCsvValue($connectionName).$this->csvSeparator);
                print($this->escapeCsvValue($objExport->fields['REISEN']).$this->csvSeparator);
                print($this->escapeCsvValue($this->getFileLink($objExport->fields['RBN'], true)));

                print ("\r\n");

                $objExport->MoveNext();
            }
        }

        exit();
    }

    function escapeCsvValue(&$value)
    {
        $value = preg_replace('/\r\n/', "\n", $value);
        $valueModified = str_replace('"', '""', $value);

        if ($valueModified != $value || preg_match('/['.$this->_csvSeparator.'\n]+/', $value)) {
            $value = '"'.$valueModified.'"';
        }
        return strtolower(CONTREXX_CHARSET) == 'utf-8' ? utf8_decode($value) : $value;
    }

}
