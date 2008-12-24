<?php
/**
 * Graph
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_banner
 * @todo        Edit PHP DocBlocks!
 */

error_reporting(0);

/**
 * Includes
 */
require_once dirname(__FILE__).'/../../config/configuration.php';
include ASCMS_LIBRARY_PATH.'/ykcee/ykcee.php';
require_once ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php';

$objDb = ADONewConnection($_DBCONFIG['dbType']); # eg 'mysql' or 'postgres'
$objDb->Connect($_DBCONFIG['host'],$_DBCONFIG['user'],$_DBCONFIG['password'],$_DBCONFIG['database']);

/**
 * Banner management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @access public
 * @version 1.0.0
 * @package     contrexx
 * @subpackage  core_module_banner
 */
class makeGraph
{
    var $stats = '';

    var $graphWidth = 200;
    var $graphHeight = 100;

    var $graphBackgroundColor = "white";

    var $graphChartType = "bars";
    var $graphChartBackgroundColor = "white";
    var $graphChartBorderColor = "white";
    var $graphChartTitle = "";
    var $graphChartTitleSize = 10;

    var $graphArrBarColor = array("blue","red");
    var $graphArrBarBorderColor = array("blue","red");

    var $graphArrLegendText = array();
    var $graphLegendBackgroundColor = "white";

    var $graphTitleAxisX = "";
    var $graphTitleAxisY = "";

    var $graphGridX = 10;
    var $graphGridY = 10;
    var $graphGridColor = "silver";

    var $graphAxisXMaxStringSize = 4;
    var $graphAxisFontSize = 7;
    var $graphAxisTitleFontSize = 6;

    var $graphArrData = array();

    var $graphScaleMax = 10;

    var $graphMarginLeft = 10;
    var $graphMarginRight = 10;
    var $graphMarginTop = 10;
    var $graphMarginBottom = 10;

    var $graphFrame = false;

    var $graphColor = "#c8d7ee";



    function makeGraph() {
        if (isset($_GET['banner_id']) && !empty($_GET['banner_id'])) {
            $this->_makeRequestsYearsGraph(intval($_GET['banner_id']));
        }
    }

    function _makeRequestsYearsGraph($banner_id) {
        global $objDb, $_ARRAYLANG;

        $arrBarPlot1 = array();
        $arrBarPlot2 = array();

        $arrBarPlot1Keys = array();
        $arrBarPlot2Keys = array();

        $arrData = array();

        // get statistics
        $query = 'SELECT id, views, clicks
                    FROM '.DBPREFIX.'module_banner_system
                    WHERE id='.$banner_id.' LIMIT 1 ';
        
        $result = $objDb->Execute($query);
        if ($result) {
            while ($arrResult = $result->FetchRow()) {
                $arrBarPlot1[1] = $arrResult['views'];
                $arrBarPlot2[1] = $arrResult['clicks'];
            }
        }

        reset($arrBarPlot2);

        $arrData[1] = array('', $arrBarPlot1[1], $arrBarPlot2[1]);
        
        $this->graphAxisXMaxStringSize = 1;
        $this->graphArrLegendText = array('Anzeigen', 'Klicks');
        $this->graphTitleAxisX = '';
        $this->graphChartTitle = 'Diagramm';
        $this->graphArrData = $arrData;

        $this->_generateGraph();
    }



    function _generateGraph() {
        global $_ARRAYLANG;

        $graph = new ykcee;

        $graph->SetImageSize($this->graphWidth, $this->graphHeight);
        $graph->SetTitleFont(ASCMS_LIBRARY_PATH.'/ykcee/VERDANA.TTF');
        $graph->SetFont(ASCMS_LIBRARY_PATH.'/ykcee/VERDANA.TTF');
        $graph->SetFileFormat("png");
        $graph->SetMaxStringSize($this->graphAxisXMaxStringSize);
        $graph->SetBackgroundColor($this->graphBackgroundColor);

        $graph->SetChartType($this->graphChartType);

        $graph->SetChartBackgroundColor($this->graphChartBackgroundColor);
        $graph->SetChartBorderColor($this->graphChartBorderColor);
        $graph->SetChartTitle($this->graphChartTitle);
        $graph->SetChartTitleSize($this->graphChartTitleSize);
        $graph->SetChartTitleColor("black");
        $graph->SetFontColor("black");

        $graph->SetBarColor($this->graphArrBarColor);
        $graph->SetBarBorderColor($this->graphArrBarBorderColor);

        $graph->SetLegend($this->graphArrLegendText);
        $graph->SetLegendPosition(2);
        $graph->SetLegendBackgroundColor($this->graphLegendBackgroundColor);

        $graph->SetTitleAxisX($this->graphTitleAxisX);
        $graph->SetTitleAxisY($this->graphTitleAxisY);
        $graph->SetAxisFontSize($this->graphAxisFontSize);
        $graph->SetAxisColor("black");
        $graph->SetAxisTitleSize($this->graphAxisTitleFontSize);
        $graph->SetTickLength(2);
        $graph->SetTickInterval(5);
        $graph->SetGridX($this->graphGridX);
        $graph->SetGridY($this->graphGridY);
        $graph->SetGridColor($this->graphGridColor);
        $graph->SetLineThickness(1);
        $graph->SetPointSize(2); //es werden dringend gerade Zahlen empfohlen
        $graph->SetPointShape("dots");
        $graph->SetShading(0);
        $graph->SetNoData($_ARRAYLANG['TXT_NO_DATA_AVAILABLE']);

        $graph->SetDataValues($this->graphArrData);
        $graph->DrawGraph();
    }
}

new makeGraph();
?>
