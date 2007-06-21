<?php
/**
 * Make Graph
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Comvation Development Team <info@comvation.com>
 * @version 1.0
 * @package     contrexx
 * @subpackage  core_module_stats
 * @todo        Edit PHP DocBlocks!
 */
class makeGraph
{
	var $stats = '';

	var $graphWidth = 600;
	var $graphHeight = 250;

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
	var $graphGridY = 0;
	var $graphGridColor = "silver";

	var $graphAxisXMaxStringSize = 4;
	var $graphAxisFontSize = 7;
	var $graphAxisTitleFontSize = 10;

	var $graphArrData = array();

	var $graphScaleMax = 10;

	var $graphMarginLeft = 20;
	var $graphMarginRight = 20;
	var $graphMarginTop = 20;
	var $graphMarginBottom = 20;

	var $graphFrame = false;

	var $graphColor = "#c8d7ee";



	function makeGraph() {
		if (isset($_GET['stats']) && !empty($_GET['stats'])) {
			$this->stats = $_GET['stats'];
		}

		switch ($this->stats) {
			case 'requests_today':
				$this->_makeRequestsHoursGraph();
				break;
			case 'requests_days':
				$this->_makeRequestsDaysGraph();
				break;
			case 'requests_months':
				$this->_makeRequestsMonthsGraph();
				break;
			case 'requests_years':
				$this->_makeRequestsYearsGraph();
				break;
			case 'referers_spider':
				$this->_makeReferersSpiderGraph();
				break;
			case 'clients_browser':
				$this->_makeClientsBrowserGraph();
				break;
			case 'clients_os':
				$this->_makeClientsOSGraph();
				break;
		}
	}


	function _makeRequestsHoursGraph() {
		global $objDatabase, $_ARRAYLANG;

		$arrBarPlot1 = array();
		$arrBarPlot2 = array();

		// get statistics
		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%H' ) AS `hour` , `count`
			FROM `".DBPREFIX."stats_visitors_summary`
			WHERE FROM_UNIXTIME( `timestamp` , '%d-%m-%Y' ) = '".date('d-m-Y')."' AND `type` = 'hour' AND `count` > 0";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot1[$arrResult['hour']] = $arrResult['count'];
			}
		}
		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%H' ) AS `hour` , `count`
			FROM `".DBPREFIX."stats_requests_summary`
			WHERE FROM_UNIXTIME( `timestamp` , '%d-%m-%Y' ) = '".date('d-m-Y')."' AND `type` = 'hour' AND `count` > 0";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot2[$arrResult['hour']] = $arrResult['count'];
			}
		}

		// generate arrays for the bars
		for ($hour=1;$hour<=24;$hour++) {
			if (!isset($arrBarPlot1[sprintf("%02s",$hour)])){
				$arrBarPlot1[sprintf("%02s",$hour)] = 0;
			}
			if (!isset($arrBarPlot2[sprintf("%02s",$hour)])){
				$arrBarPlot2[sprintf("%02s",$hour)] = 0;
			}
			$arrData[$hour] = array($hour, $arrBarPlot1[sprintf("%02s",$hour)], $arrBarPlot2[sprintf("%02s",$hour)]);
		}

		$this->graphChartTitle = date('j').'. '.date('M');
		$this->graphArrLegendText = array($_ARRAYLANG['TXT_VISITORS'], $_ARRAYLANG['TXT_PAGE_VIEWS']);
		$this->graphTitleAxisX = $_ARRAYLANG['TXT_HOUR'];
		$this->graphArrData = $arrData;
		$this->_generateGraph();
	}



	function _makeRequestsDaysGraph() {
		global $objDatabase, $_ARRAYLANG;

		$arrBarPlot1 = array();
		$arrBarPlot2 = array();
		$arrData = array();

		// get statistics
		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%d' ) AS `day` , `count`
			FROM `".DBPREFIX."stats_visitors_summary`
			WHERE `type` = 'day' AND `count` > 0 AND FROM_UNIXTIME( `timestamp` , '%m-%Y' ) = '".date('m-Y')."'";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot1[$arrResult['day']] = $arrResult['count'];
			}
		}

		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%d' ) AS `day` , `count`
			FROM `".DBPREFIX."stats_requests_summary`
			WHERE `type` = 'day' AND `count` > 0 AND FROM_UNIXTIME( `timestamp` , '%m-%Y' ) = '".date('m-Y')."'";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot2[$arrResult['day']] = $arrResult['count'];
			}
		}

		// generate arrays for the bars
		for ($day=1;$day<=date('t');$day++) {
			if (!isset($arrBarPlot1[sprintf("%02s",$day)])){
				$arrBarPlot1[sprintf("%02s",$day)] = 0;
			}
			if (!isset($arrBarPlot2[sprintf("%02s",$day)])){
				$arrBarPlot2[sprintf("%02s",$day)] = 0;
			}
			$arrData[$day] = array(sprintf("%02s",$day), $arrBarPlot1[sprintf("%02s",$day)], $arrBarPlot2[sprintf("%02s",$day)]);
		}

		$arrMonth = explode(',',$_ARRAYLANG['TXT_MONTH_ARRAY']);
		$this->graphChartTitle = $arrMonth[date('n')-1];
		$this->graphArrLegendText = array($_ARRAYLANG['TXT_VISITORS'], $_ARRAYLANG['TXT_PAGE_VIEWS']);
		$this->graphTitleAxisX = $_ARRAYLANG['TXT_DAY'];
		$this->graphArrData = $arrData;
		$this->_generateGraph();
	}



	function _makeRequestsMonthsGraph() {
		global $objDatabase, $_ARRAYLANG;

		$arrBarPlot1 = array();
		$arrBarPlot2 = array();
		$arrData = array();

		// get statistics
		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%m' ) AS `month` , `count`
			FROM `".DBPREFIX."stats_visitors_summary`
			WHERE `type` = 'month' AND `count` > 0 AND FROM_UNIXTIME( `timestamp` , '%Y' ) = '".date('Y')."'";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot1[$arrResult['month']] = $arrResult['count'];
			}
		}

		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%m' ) AS `month` , `count`
			FROM `".DBPREFIX."stats_requests_summary`
			WHERE `type` = 'month' AND `count` > 0 AND FROM_UNIXTIME( `timestamp` , '%Y' ) = '".date('Y')."'";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot2[$arrResult['month']] = $arrResult['count'];
			}
		}

		// generate arrays for the bars
		for ($month=1;$month<=12;$month++) {
			if (!isset($arrBarPlot1[sprintf("%02s",$month)])){
				$arrBarPlot1[sprintf("%02s",$month)] = 0;
			}
			if (!isset($arrBarPlot2[sprintf("%02s",$month)])){
				$arrBarPlot2[sprintf("%02s",$month)] = 0;
			}
			$arrData[$month] = array(' '.date('M',mktime(0,0,0,$month,1,date('Y'))), $arrBarPlot1[sprintf("%02s",$month)], $arrBarPlot2[sprintf("%02s",$month)]);
		}

		$this->graphAxisXMaxStringSize = 5;
		$this->graphArrLegendText = array($_ARRAYLANG['TXT_VISITORS'], $_ARRAYLANG['TXT_PAGE_VIEWS']);
		$this->graphChartTitle = date('Y');
		$this->graphTitleAxisX = $_ARRAYLANG['TXT_MONTH'];
		$this->graphArrData = $arrData;

		$this->_generateGraph();
	}



	function _makeRequestsYearsGraph() {
		global $objDatabase, $_ARRAYLANG;

		$arrBarPlot1 = array();
		$arrBarPlot2 = array();

		$arrBarPlot1Keys = array();
		$arrBarPlot2Keys = array();

		$arrData = array();

		// get statistics
		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%Y' ) AS `year` , `count`
			FROM `".DBPREFIX."stats_visitors_summary`
			WHERE `type` = 'year'
			ORDER BY `year`";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot1[$arrResult['year']] = $arrResult['count'];
			}
		}

		$query = "SELECT FROM_UNIXTIME(`timestamp`, '%Y' ) AS `year` , `count`
			FROM `".DBPREFIX."stats_requests_summary`
			WHERE `type` = 'year'
			ORDER BY `year`";
		$result = $objDatabase->Execute($query);
		if ($result) {
			while ($arrResult = $result->FetchRow()) {
				$arrBarPlot2[$arrResult['year']] = $arrResult['count'];
			}
		}

		reset($arrBarPlot2);

		$startYear = key($arrBarPlot2);
		$endYear = date('Y');

		// generate arrays for the bars
		for ($year=$startYear;$year<=$endYear;$year++) {
			if (!isset($arrBarPlot1[$year])){
				$arrBarPlot1[$year] = 0;
			}
			if (!isset($arrBarPlot2[$year])){
				$arrBarPlot2[$year] = 0;
			}
			$arrData[$year] = array($year, $arrBarPlot1[$year], $arrBarPlot2[$year]);
		}

		$this->graphAxisXMaxStringSize = 5;
		$this->graphArrLegendText = array($_ARRAYLANG['TXT_VISITORS'], $_ARRAYLANG['TXT_PAGE_VIEWS']);
		$this->graphTitleAxisX = $_ARRAYLANG['TXT_YEAR'];
		$this->graphChartTitle = $startYear.' - '. $endYear;
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

error_reporting(0);

/**
 * Includes
 */
require_once dirname(__FILE__).'/../../config/configuration.php';
include ASCMS_LIBRARY_PATH.'/ykcee/ykcee.php';

$adminPage = true;
require_once ASCMS_CORE_PATH.'/API.php';

$errorMsg = '';
$objDatabase = getDatabaseObject($errorMsg);

$objInit= new InitCMS($mode="backend");

$sessionObj= &new cmsSession();
$sessionObj->cmsSessionStatusUpdate($status="backend");

$objPerm =&new Permission($type='backend');
$objPerm->checkAccess(19, 'static');

$objInit->_initBackendLanguage();
$objInit->getUserFrontendLangId();

$_ARRAYLANG = $objInit->loadLanguageData('stats');

new makeGraph();
?>
