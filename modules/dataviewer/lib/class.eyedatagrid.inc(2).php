<?php
/**
 * EyeDataGrid
 * Provides datagrid control features
 *
 * LICENSE: This source file is subject to the BSD license
 * that is available through the world-wide-web at the following URI:
 * http://www.eyesis.ca/license.txt.  If you did not receive a copy of
 * the BSD License and are unable to obtain it through the web, please
 * send a note to mike@eyesis.ca so I can send you a copy immediately.
 *
 * @author     Micheal Frank <mike@eyesis.ca>
 * @copyright  2008 Eyesis
 * @license    http://www.eyesis.ca/license.txt  BSD License
 * @version    v1.1.6 12/3/2008 10:04:44 AM
 * @link       http://www.eyesis.ca/projects/datagrid.html
 */

class EyeDataGrid {
	private $results_per_page = 10;
	private $column_count = 0; // Num of columns
	private $row_count = 0; // Number of rows
	private $hide_header = false; // Header visibility
	private $hide_footer = false; // Footer visibility
	private $hide_order = false; // Show ordering option
	private $show_checkboxes = false; // Show checkboxes
	private $allow_filters = true; // Allow filters or not
	private $row_select = false; // Enable row selection
	private $create_button = false; // Show create button
	private $reset_button = false; // Show reset grid button
	private $show_row_number = false; // Show row numbers
	private $hide_page_list = false; // Hide page list
	private $page = 1; // Current page
	private $primary = ''; // Tables primary key column
	private $query; // SQL query
	private $hidden = array(); // Hidden columns
	private $header = array(); // Header titles
	private $type = array(); // Column types
	private $controls = array(); // Row controls, std or custom
	private $order = false; // Current order
	private $filter = false; // Current filter
	private $limit = false; // Current limit
	private $_db, $result; // Database related
	private $select_fields = ''; // Field used to select
	private $select_where = ''; // Where clause
	private $select_table = ''; // Table to read
	private $image_path = ''; // Path to images

	// Filename of required images
	public $img_edit = 'edit.png';
	public $img_delete = 'delete.png';
	public $img_create = 'create.png';
	public $img_reset = 'reset.png';

	// Configuration constants
	const CUSCTRL_TEXT = 1;
	const CUSCTRL_IMAGE = 2;
	const STDCTRL_EDIT = 3;
	const STDCTRL_DELETE = 4;
	const TYPE_DATE = 1;
	const TYPE_IMAGE = 2;
	const TYPE_ONCLICK = 3;
	const TYPE_ARRAY = 4;
	const TYPE_DOLLAR = 5;
	const TYPE_HREF = 6;
	const TYPE_CHECK = 7;
	const TYPE_PERCENT = 8;
	const TYPE_CUSTOM = 9;
	const TYPE_FUNCTION = 10;
	const ORDER_DESC = 'DESC';
	const ORDER_ASC = 'ASC';

	// Default text
	const TXT_RESET = 'Reset Table';
	const TXT_NORESULTS = 'Keine Datensätze gefunden!';
	
	/**
	* Constructor
	*
	* @param EyeMySQLAdap $_db The Eyesis MySQL Adapter class
	* @param string $image_path The path to datagrid images
	*/
	public function __construct(EyeMySQLAdap $_db, $image_path = '') {
		$this->_db = $_db;

		if (empty($image_path))
			$this->image_path = 'images/modules/dataviewer/';
		else
			$this->image_path = $image_path;

		$page = (isset($_GET['pageDV'])) ? (int) $_GET['pageDV'] : 0; // Page number
		$order = (isset($_GET['order'])) ? $_GET['order'] : ''; // Order clause
		$filter = (isset($_GET['filter'])) ? $_GET['filter'] : ''; // Filter clause

		// Set the limit
		if (empty($page) or $page <= 0)
			$this->setLimit(0, $this->results_per_page);
		else
			$this->page = $page;

		// Set the order
		if ($order)
		{
			list($column, $order) = $this->parseInputCond($order);
			$this->setOrder($column, $order);
		}

		// Set the filter
		if ($filter)
		{
			list($column, $value) = $this->parseInputCond($filter);
			$this->setFilter($column, $value);
		}
	}

	
	/**
	* Hides page drop down selection and replaces it with text
	*
	* @param $hide Show or hide the page drop down
	*/
	public function hidePageSelectList($hide = true)
	{
		$this->hide_page_list = $hide;
	}

	
	/**
	 * Allow filters
	 *
	 * @param boolean $allow
	 */
	public function allowFilters($allow = true)
	{
		$this->allow_filters = $allow;
	}

	
	/**
	 * Hide order functionality
	 *
	 * @param boolean $hide
	 */
	public function hideOrder($hide = true)
	{
		$this->hide_order = $hide;
	}

	
	/**
	 * Show checkboxes on each row
	 *
	 * @param boolean $show
	 */
	public function showCheckboxes($show = true)
	{
		$this->show_checkboxes = $show;
	}

	
	/**
	 * Hide header row
	 *
	 * @param boolean $hide
	 */
	public function hideHeader($hide = true)
	{
		$this->hide_header = $hide;
	}

	
	/**
	 * Hide footer row
	 *
	 * @param boolean $hide
	 */
	public function hideFooter($hide = true)
	{
		$this->hide_footer = $hide;
	}

	
	/**
	 * Show reset control
	 *
	 * @param string $text Display caption
	 */
	public function showReset($text = self::TXT_RESET)
	{
		$this->reset_button = $text;
	}

	
	/**
	 * Show row numbers
	 *
	 * @param boolean $show
	 */
	public function showRowNumber($show = true)
	{
		$this->show_row_number = $show;
	}

	
	/**
	 * Set the SELECT query
	 *
	 * @param string $fields Feilds to fetch from table. * for all columns
	 * @param string $table Table to select from
	 * @param string $primay Optional primary key column
	 * @param string $where Optional where condition
	 */
	public function setQuery($fields, $table, $primary = '', $where = '')
	{
		$this->primary = $primary;

		$this->select_fields = $fields;
		$this->select_table = $table;
		$this->select_where = $where;
	}

	
	/**
	 * Set filter
	 *
	 * @param string $column Column to apply filter clause on
	 * @param string $value Value to compare to
	 */
	private function setFilter($column, $value)
	{
		$this->filter = array('Column' => $column,
							'Value' => $value);
	}

	
	/**
	 * Set order
	 *
	 * @param string $column Column to apply order clause on
	 * @param string $order Direction, use ORDER_* const
	 */
	private function setOrder($column, $order = self::ORDER_DESC)
	{
		$order = ($order == self::ORDER_DESC)
					? self::ORDER_DESC
					: self::ORDER_ASC;

		$this->order = array('Column' => $column,
							'Order' => $order);
	}

	
	/**
	* Hides a column
	*
	* @param string $column The column to be hidden
	*/
	public function hideColumn($column)
	{
		$this->hidden[] = $column;
	}

	
	/**
	* Change column header caption
	*
	* @param string $column The column name
	* @param string $header The new header caption
	*/
	public function setColumnHeader($column, $header)
	{
		$this->header[$column] = $header;
	}

	
	/**
	* Set a column type
	*
	* @param string $column The column to apply the type to
	* @param integer $type The type of column, use TYPE_* const
	* @param mixed $criteria Specific value to each column type
	* @param mixed $criteria_2 Second specific value to each column type
	*/
	public function setColumnType($column, $type, $criteria = '', $criteria_2 = '')
	{
		$this->type[$column] = array($type, $criteria, $criteria_2);
	}

	
	/**
	* Sets the maximum amount of rows per page
	*
	* @param integer $num Amount of rows per page
	*/
	public function setResultsPerPage($num)
	{
		$this->results_per_page = (int) $num;
		$this->setLimit(0, (int) $num);
	}

	
	/**
	* Adds a standard control to a row
	*
	* @param integer $type The type of standard control, use STDCTRL_* const
	* @param string $action The action of the control (onclick code or href link)
	* @param integer $action_type The type of action, use TYPE_ONCLICK or TYPE_HREF
	*/
	public function addStandardControl($type, $action, $action_type = self::TYPE_ONCLICK)
	{
		$action = $this->parseLinkAction($action, $action_type);

		switch ($type)
		{
			case self::STDCTRL_EDIT:
				$this->controls[] = '<a ' . $action . '><img src="' . $this->image_path . $this->img_edit . '" alt="Edit" title="Edit"></a>';
				break;
			case self::STDCTRL_DELETE:
				$this->controls[] = '<a ' . $action . '><img src="' . $this->image_path . $this->img_delete . '" alt="Delete" title="Delete"></a>';
				break;
			default:
				// Invalid standard control
				break;
		}
	}

	
	/**
	* Adds a custom control to a row
	*
	* @param integer $type The type of custom control, use CUSCTRL_* const
	* @param string $action The action of the control (onclick code or href link)
	* @param integer $action_type The type of action, use TYPE_ONCLICK or TYPE_HREF
	* @param string $text The textual description of the control
	* @param string $image_path The location of the image if type is CUSCTRL_IMAGE
	*/
	public function addCustomControl($type = self::CUSCTRL_TEXT, $action, $action_type = self::TYPE_ONCLICK, $text, $image_src = '')
	{
		$action = $this->parseLinkAction($action, $action_type);

		switch ($type)
		{
			case self::CUSCTRL_IMAGE:
				$this->controls[] = '<a ' . $action . '><img src="' . $image_src . '" alt="' . $text . '" title="' . $text . '"></a>';
				break;
			default: // Default to text
				$this->controls[] = '<a ' . $action . '>' . $text . '</a>';
				break;
		}
	}

	
	/**
	* Adds a create control above the table
	*
	* @param string $action The action associated to the create (onclick code or href link)
	* @param integer $action_type The type of action, use TYPE_ONCLICK or TYPE_HREF
	* @param string $text The textual description of the create
	*/
	public function showCreateButton($action, $action_type = self::TYPE_ONCLICK, $text = 'New Record')
	{
		$action = $this->parseLinkAction($action, $action_type);

		$this->create_button = array('Action' => $action,
										'Text' => $text);
	}

	
	/**
	* Adds ability to select a entire row
	*
	* @param string $onclick The JS function to call when a row is clicked
	*/
	public function addRowSelect($onclick)
	{
		$this->row_select = $onclick;
	}

	
	/**
	* Data sanitization and control for filters and ordering
	*
	* @param string $in The value to be sanitized and parsed
	*/
	private function parseInputCond($in)
	{
		return explode(':', ereg_replace("[\'\"\<\>\\]", '%', $in), 2);
	}

	
	/**
	* Replaces our variables place holders with values
	*
	* @param array $row The row associated array
	* @param string $act The string containing place holders to replace
	* @return string
	*/
	private function parseVariables(array $row, $act)
	{
		// The only way we get an array for $act is for parameters from a column type of function
		if (is_array($act))
		{
			// Loop through each passed param and replace variables where necessary
			foreach ($act as $key => $value)
				$act[$key] = $this->parseVariables($row, $value);

			return $act;
		}

		// %_P% is an alias for the primary key, replace it with the primary key
		if ($this->primary)
			$act = str_replace('%_P%', '%' . $this->primary . '%', $act);

		preg_match_all("/%([A-Za-z0-9_ \-]*)%/", $act, $vars);

		foreach($vars[0] as $v)
			$act = str_replace($v, $row[str_replace('%', '', $v)], $act);

		return $act;
	}

	
	/**
	* Builds a link action
	*
	* @param string $action The action
	* @param integer $action_type The type of actions (onclick code or href link)
	* @return string
	*/
	private function parseLinkAction($action, $action_type)
	{
		if ($action_type == self::TYPE_ONCLICK)
			$action = 'href="javascript:;" onclick="' . $action . '"';
		else
			$action = 'href="' . $action . '"';

		return $action;
	}

	
	/**
	* Sets the limit by clause
	*
	* @param integer $low The minimum row number
	* @param integer $high The maximum row number
	*/
	private function setLimit($low, $high)
	{
		$this->limit = array('Low' => $low,
							'High' => $high);
	}


	/**
	* Creates the table header
	*
	*/
	private function buildHeader()
	{
		// If entire header is hidden, skip all together
		if ($this->hide_header)
			return;

		$xhtml = "";
		$xhtml .=  '<thead><tr>';

		// Get field names of result
		$headers = $this->_db->fieldNameArray($this->result);
		$this->column_count = count($headers);

		// Add a blank column if the row number is to be shown
		if ($this->show_row_number)
		{
			$this->column_count++;
			$xhtml .=  '<td>&nbsp;</td>';
		}

		// Show checkboxes
		if ($this->show_checkboxes)
		{
			$this->column_count++;
			$xhtml .=  '<td"><input type="checkbox" name="checkall" onclick="tblToggleCheckAll()"></td>';
		}

		// Loop through each header and output it
		foreach ($headers as $t)
		{
			// Skip column if hidden
			if (in_array($t, $this->hidden))
			{
				$this->column_count--;
				continue;
			}

			// Check for header caption overrides
			if (array_key_exists($t, $this->header))
				$header = $this->header[$t];
			else
				$header = $t;

			if ($this->hide_order)
				$xhtml .=  '<td>' . $header; // Prevent the user from changing order
			else {
				if ($this->order and $this->order['Column'] == $t)
					$order = ($this->order['Order'] == self::ORDER_ASC)
										? self::ORDER_DESC
										: self::ORDER_ASC;
				else
					$order = self::ORDER_ASC;

				$xhtml .=  '<td><a href="javascript:;" onclick="tblSetOrder(\'' . $t . '\', \'' . $order . '\')">' . $header . "</a>";

				// Show the user the order image if set
				if ($this->order and $this->order['Column'] == $t)
					$xhtml .=  '&nbsp;<img src="' . $this->image_path . 'sort_' . strtolower($this->order['Order']) . '.gif">';
			}

			// Add filters if allowed and only if the column type is not "special"
			if ($this->allow_filters and
				!in_array($this->type[$t][0], array(
									self::TYPE_ARRAY,
									self::TYPE_IMAGE,
									self::TYPE_FUNCTION,
									self::TYPE_DATE,
									self::TYPE_CHECK,
									self::TYPE_CUSTOM,
									self::TYPE_PERCENT
									)))
			{
				if ($this->filter['Column'] == $t and !empty($this->filter['Value']))
				{
					$filter_display = 'block';
					$filter_value = $this->filter['Value'];
				} else {
					$filter_display = 'none';
					$filter_value = '';
				}
			}

			$xhtml .=  '</td>';
		}

		// If we have controls, add a blank column
		if (count($this->controls) > 0)
		{
			$this->column_count++;
			$xhtml .=  '<td>&nbsp;</td>';
		}

		$xhtml .=  '</tr></thead>';
		
		return $xhtml;
	}

	
	/**
	* Creates the table footer
	*
	* @param integer $shown The amounts of rows being shown in the current page
	* @param integer $first The row number of the first row
	* @param integer $last The row number of the last row
	*/
	private function buildFooter($shown, $first = 0, $last = 0)
	{
		// Skip adding the footer if it is hidden
		if ($this->hide_footer)
			return;
			
		$xhtml = "";

		$pages = ceil($this->row_count / $this->results_per_page); // Total number of pages

		$xhtml .=  '<tfoot><tr><td colspan="' . $this->column_count . '"><table width="100%"><tr><td width="33%">Found <em>' . $this->row_count . '</em> results';

		if ($this->row_count > 0)
			$xhtml .=  ', showing <em>' . $first . '</em> to <em>' . $last . '</em>';

		$xhtml .=  '</td><td width="33%">';

		// Handle results that span multiple pages
		if ($this->row_count > $this->results_per_page)
		{
			if ($this->page > 1)
				$xhtml .=  '<a href="javascript:;" onclick="tblSetPage(1)"><img src="' . $this->image_path . 'arrow_first.gif" alt="&lt;&lt;" title="First Page"></a><a href="javascript:;" onclick="tblSetPage(' . ($this->page - 1) . ')"><img src="' . $this->image_path . 'arrow_left.gif" alt="&lt;" title="Previous Page"></a>';
			else
				$xhtml .=  '<img src="' . $this->image_path . 'arrow_first_disabled.gif" alt="arrow_first_disabled" title="First Page" /><img src="' . $this->image_path . 'arrow_left_disabled.gif" class="tbl-arrows" alt="previousPage" title="Previous Page" />';

			// Special thanks to ionut for this next few lines
			$startpage = ($this->page > 10)
										? $this->page - 10
										: 1;
			
			$endpage = (($pages - 10) > $this->page)
										? $this->page + 10
										: $pages;

      // Only display a portion of the selectable pages
      for ($i = $startpage; $i <= $endpage; $i++)
			{
				if ($i == $this->page)
					$xhtml .=  '&nbsp;<span class="page-selected">' . $i . '</span>&nbsp;';
				else
					$xhtml .=  '&nbsp;<a href="javascript:;" onclick="tblSetPage(' . $i . ')">' . $i . '</a>&nbsp;';
			}

			if ($this->page < $pages)
				$xhtml .=  '<a href="javascript:;" onclick="tblSetPage(' . ($this->page + 1) . ')"><img src="' . $this->image_path . 'arrow_right.gif" class="tbl-arrows" alt="arrowRight" title="Next Page"></a><a href="javascript:;" onclick="tblSetPage(' . $pages . ')"><img src="' . $this->image_path . 'arrow_last.gif" class="tbl-arrows" alt="arrowlast" title="Last Page"></a>';
			else
				$xhtml .=  '<img src="' . $this->image_path . 'arrow_right_disabled.gif" class="tbl-arrows" alt="nextPAge" title="Next Page"><img src="' . $this->image_path . 'arrow_last_disabled.gif" class="tbl-arrows" alt="&gt;&gt;" title="Last Page">';
		}

		$xhtml .=  '</td><td width="33%" class="tbl-page">';

		// Only show page section if we have more than one page
		if ($pages > 0)
		{
			$xhtml .=  'Seite ';
			if (!$this->hide_page_list and $pages > 1)
			{
				// Create a selectable drop down list for pages
				$xhtml .=  '<select name="tbl-page" onchange="tblSetPage(this.options[this.selectedIndex].value)">';
				for ($x = 1; $x <= $pages; $x++)
				{
					$xhtml .=  '<option value="' . $x . '"';
					if ($x == $this->page)
						$xhtml .=  ' selected="selected"';
					$xhtml .=  '>' . $x . '</option>';
				}
				$xhtml .=  '</select>';
			} else
				$xhtml .=  $this->page; // Just write the page number, nothing to fancy

			$xhtml .=  ' von ' . $pages;
		}

		$xhtml .=  '</td></tr></table></td></tr></tfoot>';
		
		return $xhtml;
	}

	
	/**
	* Builds row controls
	*
	* @param array $row The row associated array
	*/
	private function buildControls(array $row)
	{
			$xhtml = "";
			
			// Add controls as needed
			if (count($this->controls) > 0)
			{
				$xhtml .= '<td class="tbl-controls">';
				foreach ($this->controls as $ctl)
					$xhtml .=  $this->parseVariables($row, $ctl);
				$xhtml .= '</td>';
			}
	}

	
	/**
	* Outputs the datagrid to the screen
	*
	*/
	public function printTable($projectname)
	{
		// Set the limit
		$this->setLimit(($this->page - 1) * $this->results_per_page, $this->results_per_page);
		
		$xhtml = "";

		// FILTER
		$filter_query = '';
		if ($this->select_where)
			$filter_query .= "(" . $this->select_where . ")";

		if ($this->allow_filters and $this->filter)
		{
			if (!strstr($this->filter['Value'], '%'))
				$filter_value = '%' . $this->filter['Value'] . '%';
			else
				$filter_value = $this->filter['Value'];

			if ($this->select_where)
				$filter_query = $filter_query . " AND ";

			$filter_query .= "(`" . $this->filter['Column'] . "` LIKE '" . $filter_value . "')";
		}
		
		if ($filter_query)
			$filter = 'WHERE ' . $filter_query;

		// ORDER
		if ($this->order)
			$order = "ORDER BY `" . $this->order['Column'] . "` " . $this->order['Order'];
		else
			$order = '';

		// LIMIT
		if ($this->limit)
			$limit = "LIMIT " . $this->limit['Low'] . ", " . $this->limit['High'];
		else
			$limit = '';

		$query = 'SELECT ' . $this->select_fields . ' FROM ' . $this->select_table . ' ' . $filter;

		// Inform the user of any errors. Commonly caused when a column is specified in the filter or order clause that does not exist
		
//		echo $query . ' ' . $order . ' ' . $limit;
		
		$this->result = $this->_db->query($query . ' ' . $order . ' ' . $limit, false);
		if (!$this->result)
		{
			$xhtml .=  '<div style="color: red; font-weight: bold; border: 2px solid red; padding: 10px;">Oops! We ran into a problem while trying to output the table. <a href="javascript:;" onclick="tblReset()">Click here</a> to reset the table or <a href="javascript:;" onclick="alert(\'' . ereg_replace('[\'"]', '', $this->_db->error()) . '\')">here</a> to review the error.</div>';
			return;
		}

		// Count the number of rows without the limit clause
		$this->row_count = (int) $this->_db->selectOneValue('COUNT(*)', $this->select_table, $filter_query); // Old code which does not support large data sets: $this->_db->countRows($query);

		$xhtml .=  '<form action="#" name="dg" id="dg">';

		// Output the create button
		if ($this->create_button)
			$xhtml .=  '<span class="tbl-create"><a ' . $this->create_button['Action'] . ' title="' . $this->create_button['Text'] . '"><img src="' . $this->image_path . $this->img_create . '" class="tbl-create-image">' . $this->create_button['Text'] . '</a></span>';

		// Output the reset button
		if ($this->reset_button)
			$xhtml .=  '<span class="tbl-reset"><a href="javascript:;" onclick="tblReset()" title="' . $this->reset_button .'"><img src="' . $this->image_path . $this->img_reset . '" class="tbl-reset-image">' . $this->reset_button .'</a></span>';

		$xhtml .=  '<table class="tbl">';

		$xhtml .= $this->buildHeader();

		$xhtml .=  '<tbody>';

		if ($this->row_count == 0)
			$xhtml .=  '<tr><td colspan="' . $this->column_count . '" class="tbl-noresults">' . self::TXT_NORESULTS . '</td></tr>';
		else {
			$i = 0; $first = 0; $last = 0;

			while ($row = $this->_db->fetchAssoc($this->result))
			{
				$xhtml .=  '<tr class="tbl-row tbl-row-' . (($i % 2) ? 'odd' : 'even'); // Switch up the bgcolors on each row

				// Handle row selects
				if ($this->row_select)
					$xhtml .=  ' tbl-row-highlight" onclick="' . $this->parseVariables($row, $this->row_select);

				$xhtml .=  '">';

				$line = ($this->page == 1)
							? $i + 1
							: $i + 1 + (($this->page - 1) * $this->results_per_page);

				$last = $line; // Last line
				if ($first == 0)
					$first = $line; // First line

				if ($this->show_row_number)
					$xhtml .=  '<td class="tbl-row-num">' . $line . '</td>';

				if ($this->show_checkboxes)
					$xhtml .=  '<td align="center"><input type="checkbox" class="tbl-checkbox" id="checkbox" name="tbl-checkbox" value="' . $row[$this->primary] . '"></td>';

				foreach ($row as $key => $value)
				{
					// Skip if column is hidden
					if (in_array($key, $this->hidden))
						continue;

					// Apply a column type to the value
					if (array_key_exists($key, $this->type))
					{
						list($type, $criteria, $criteria_2) = $this->type[$key];

						switch ($type)
						{
							case self::TYPE_ONCLICK:
								if ($value)
									$value = '<a href="javascript:;" onclick="' . $this->parseVariables($row, $criteria) . '">' . $value . '</a>';
								break;

							case self::TYPE_HREF:
								if ($value)
									$value = '<a href="' . $this->parseVariables($row, $criteria) . '">' . $value . '</a>';
								break;

							case self::TYPE_DATE:
								if ($criteria_2 == true)
									$value = date($criteria, strtotime($value));
								else
									$value = date($criteria, $value);
								break;

							case self::TYPE_IMAGE:
								$value = '<img src="' . $this->parseVariables($row, $criteria) . '" id="' . $key . '-' . $i . '">';
								break;

							case self::TYPE_ARRAY:
								$value = $criteria[$value];
								break;

							case self::TYPE_CHECK:
								if ($value == '1' or $value == 'yes' or $value == 'true' or ($criteria != '' and $value == $criteria))
									$value = '<img src="' . $this->image_path . 'check.gif">';
								break;

							case self::TYPE_PERCENT:
								if ($criteria == true)
									$value *= 100; // Value is in decimal format

								$value = round($value); // Round to the nearest decimal

								$value .= '%';

								// Apply a bar if an array is supplied via criteria_2
								if (is_array($criteria_2))
									$value = '<div style="background: ' . $criteria_2['Back'] . '; width: ' . $value . '; color: ' . $criteria_2['Fore'] . ';">' . $value . '</div>';
								break;

							case self::TYPE_DOLLAR:
								$value = '$' . number_format($value, 2);
								break;

							case self::TYPE_CUSTOM:
								$value = $this->parseVariables($row, $criteria);
								break;

							case self::TYPE_FUNCTION:
								if (is_array($criteria_2))
									$value = call_user_func_array($criteria, $this->parseVariables($row, $criteria_2));
								else
									$value = call_user_func($criteria, $this->parseVariables($row, $criteria_2));
								break;

							default:
								// Invalid column type
								break;
							}
					}

					$xhtml .=  '<td class="tbl-cell">' . $value . '</td>';
				}

				$this->buildControls($row);

				$xhtml .=  '</tr>';

				$i++;
			}
		}

		$xhtml .=  '</tbody>';

		$xhtml .= $this->buildFooter($i, $first, $last);

		$xhtml .=  '</table></form>';
		
		$xhtml .= $this->printJavascript($projectname);
		
		return $xhtml;
	}

	
	/**
	* Prints the required JS functions
	*
	*/
	public function printJavascript($projectname) {
		$javaScript = "";
		if ($this)
		{
			$page = $this->page;
			$order = (($this->order) ? implode(':', $this->order) : '');
			$filter = (($this->filter) ? implode(':', $this->filter) : '');
		}

		$javaScript .= "\n<script type=\"text/javascript\">\n";
		$javaScript .= "/* <![CDATA[ */\n";
		$javaScript .= "var params = ''; var tblpage = '" . $page . "'; var tblorder = '" . $order . "'; var tblfilter = '" . $filter . "';\n";
		$javaScript .= "function tblSetPage(page) { tblpage = page; params = 'section=dataviewer&cmd=" . $projectname . "&pageDV=' + page + '&order=' + tblorder + '&filter=' + tblfilter; updateTable(); }\n";
		$javaScript .= "function tblSetOrder(column, order) { tblorder = column + ':' + order; params = 'section=dataviewer&cmd=" . $projectname . "&pageDV=' + tblpage + '&order=' + tblorder + '&filter=' + tblfilter; updateTable(); }\n";
		$javaScript .= "function tblSetFilter(column) { val = document.getElementById('filter-value-' + column).value; tblfilter = column + ':' + val; tblpage = 1; params = 'section=dataviewer&cmd=" . $projectname . "&pageDV=1&order=' + tblorder + '&filter=' + tblfilter; updateTable(); }\n";
		$javaScript .= "function tblClearFilter() { tblfilter = ''; params = 'section=dataviewer&cmd=" . $projectname . "&pageDV=1&order=' + tblorder + '&filter='; updateTable(); }\n";
		$javaScript .= "function tblToggleCheckAll() { for (i = 0; i < document.dg.checkbox.length; i++) { document.dg.checkbox[i].checked = !document.dg.checkbox[i].checked; } }\n";
		$javaScript .= "function tblShowHideFilter(column) { var o = document.getElementById('filter-' + column); if (o.style.display == 'block') { tblClearFilter(); } else {	o.style.display = 'block'; } }\n";
		$javaScript .= "function tblReset() { params = 'section=dataviewer&cmd=" . $projectname . "&pageDV=1'; updateTable(); }\n";
		$javaScript .= 'function updateTable() { window.location = "?" + params;}';
		$javaScript .= "/* ]]> */\n";
		$javaScript .= "</script>\n";
		
		return $javaScript;
	}
}