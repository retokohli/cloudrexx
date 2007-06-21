<?PHP
/**
 * Calendar headline news
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Headline news
 *
 * Gets all the calendar headlines
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 */
class calHeadlines
{
	var $_pageContent;
	var $_objTemplate;

	/**
	 * Constructor php5
	 */
	function __construct($pageContent) {
		$this->calHeadlines($pageContent);
	}

	/**
	 * Constructor php4
	 */
    function calHeadlines($pageContent) {
	    $this->_pageContent = $pageContent;
	    $this->_objTemplate = &new HTML_Template_Sigma('.');
	}

	function getHeadlines()
	{
		global $_CONFIG, $objDatabase, $_LANGID;

		$this->_objTemplate->setTemplate($this->_pageContent,true,true);

		$category_name = '';
		if ($_CONFIG['calendarheadlinescat'] != 0) {
			$query = "SELECT name FROM ".DBPREFIX."module_calendar_categories
					  WHERE id = {$_CONFIG['calendarheadlinescat']}";
			$objResult = $objDatabase->SelectLimit($query, 1);
			$category_name = $objResult->fields['name'];
		}

		$this->_objTemplate->setVariable(array("CALENDAR_EVENT_CATEGORY" => $category_name));

		$this->_objTemplate->setCurrentBlock('calendar_headlines_row');

		if ($_CONFIG['calendarheadlines']) {
			$today = time();

			if ($_CONFIG['calendarheadlinescat'] == "0") {
				$query = '	SELECT 	id
							FROM	'.DBPREFIX.'module_calendar_categories
							WHERE	lang='.intval($_LANGID);
				$objResult = $objDatabase->Execute($query);
				if ($objResult->RecordCount() > 0) {
					$strWhere = ' AND ( ';
					while (!$objResult->EOF) {
						$strWhere .= 'catid='.$objResult->fields['id'].' OR ';
						$objResult->MoveNext();
					}
					$strWhere = substr($strWhere,0,strlen($strWhere)-4);
					$strWhere .= ' ) ';

					$query = "SELECT id, catid, startdate, enddate, name FROM ".DBPREFIX."module_calendar
							  WHERE enddate > $today  AND
							  active = 1
							  ".$strWhere."
							  ORDER BY startdate ASC
						  	LIMIT 0,".$_CONFIG['calendarheadlinescount'];
				}
			} else {
				$query = "SELECT id, catid, startdate, enddate, name FROM ".DBPREFIX."module_calendar
				  WHERE catid = {$_CONFIG['calendarheadlinescat']}
				  AND enddate > $today AND
				  active = 1
				  ORDER BY startdate ASC
			  	LIMIT 0,".$_CONFIG['calendarheadlinescount'];
			}

			$objResult = $objDatabase->Execute($query);

			if ($objResult !== false && $objResult->RecordCount()>=0) {
				while (!$objResult->EOF) {
					$this->_objTemplate->setVariable(array(
						"CALENDAR_EVENT_ENDTIME"		=> date("H:i", $objResult->fields['enddate']),
						"CALENDAR_EVENT_ENDDATE"		=> date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['enddate']),
						"CALENDAR_EVENT_STARTTIME"		=> date("H:i", $objResult->fields['startdate']),
						"CALENDAR_EVENT_STARTDATE"		=> date(ASCMS_DATE_SHORT_FORMAT, $objResult->fields['startdate']),
						"CALENDAR_EVENT_NAME"			=> stripslashes(htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)),
						"CALENDAR_EVENT_ID"				=> $objResult->fields['id'],
					));

					$this->_objTemplate->parseCurrentBlock();
					$objResult->MoveNext();
				}
				return $this->_objTemplate->get();
			}
		}
		$this->_objTemplate->hideBlock('calendar_headlines_row');
	}
}

?>
