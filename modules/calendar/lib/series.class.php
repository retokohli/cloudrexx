<?php

/**
 * Calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 */
class seriesManager
{
    public $eventList = array();
    public $eventList_maxsize;
    public $eventList_startdate;
    public $eventList_enddate;
    public $eventList_auth;
    public $eventList_term;
    public $eventList_category;
    public $eventList_callback = true;
    public $eventList_active;

    /**
     * PHP 5 Constructor
     */
    function __construct()
    {
    }


    function getEventList($startDate, $endDate=0, $maxSize=null, $auth, $term=null, $category=null, $onlyActive = false)
    {
        $this->eventList_maxsize    = $maxSize;
        $this->eventList_startdate  = $startDate;
        $this->eventList_enddate    = $endDate;
        $this->eventList_auth       = $auth;
        $this->eventList_term       = $term;
        $this->eventList_category   = $category;
        $this->eventList_active     = $onlyActive;
        /*echo "start: ".date("D d.m.Y H:i:s", $this->eventList_startdate)." - ".$this->eventList_startdate."<br >";
        echo "ende: ".date("D d.m.Y H:i:s", $this->eventList_enddate)." - ".$endDate."<br >";
        echo "size: ".$this->eventList_maxsize."<br >";
        echo "cat: ".$this->eventList_category."<br >";
        echo "term: ".$this->eventList_term."<br >";*/
        $this->_getMainEvents();
        $this->_checkEventList();
        /*print_r("<pre>");
        print_r($this->eventList);
        print_r("</pre>");*/
        if (isset($this->eventList_maxsize)) {
            $this->eventList = array_slice($this->eventList, 0, $this->eventList_maxsize);
        }
        return $this->eventList;
    }


    function updateMainEvent($id)
    {
        global $objDatabase;

        foreach ($this->eventList as $array) {
            if (array_search($id, $array)) {
                $new_startdate  = $array['startdate'];
                $new_enddate    = $array['enddate'];
                $new_patern_end = $array['series_pattern_end'];
                break;
            }
        }
        $query = "UPDATE ".DBPREFIX."module_calendar".MODULE_INDEX." SET
                        active = '1',
                        startdate = '".$new_startdate."',
                        enddate = '".$new_enddate."',
                        series_pattern_begin = '".$new_patern_end."'
                WHERE   id = '".$id."'";
        $objDatabase->Execute($query);
    }


    function _getMainEvents()
    {
        global $objDatabase, $_LANGID;

        if ($this->eventList_auth == true) {
            $auth_where = "";
        } else {
            $auth_where = " AND access='0' ";
        }
        $active_where = ($this->eventList_active == true ? ' AND active=1' : '');
        if (isset($this->eventList_enddate) && $this->eventList_enddate != 0) {
            $date_where = '((
                ((cal.enddate <= '.$this->eventList_enddate.') AND (cal.enddate >= '.$this->eventList_startdate.')) OR
                ((cal.startdate <= '.$this->eventList_enddate.') AND (cal.startdate >= '.$this->eventList_startdate.')) OR
                ((cal.startdate >= '.$this->eventList_startdate.') AND (cal.enddate <= '.$this->eventList_enddate.'))
            ) OR (
                (cal.series_status = 1) AND (cal.startdate <= '.$this->eventList_enddate.')
            ))';
        } else {
            $date_where = '((
                ((cal.enddate >= '.$this->eventList_startdate.') AND (cal.startdate <= '.$this->eventList_startdate.')) OR
                ((cal.startdate >= '.$this->eventList_startdate.') AND (cal.enddate >= '.$this->eventList_startdate.'))
            ) OR (
                (cal.series_status = 1)
            ))';
        }
        if (!empty($this->eventList_term)) {
            $term_where =   ", MATCH (cal.name,cal.comment,cal.placeName) AGAINST ('%$this->eventList_term%') AS score
                            FROM ".DBPREFIX."module_calendar".MODULE_INDEX." as cal
                            LEFT JOIN ".DBPREFIX."module_calendar".MODULE_INDEX."_categories AS cat ON (cat.id = cal.catid)
                            WHERE
                            cat.lang = $_LANGID AND (cal.`name` LIKE '%$this->eventList_term%' OR
                            cal.`comment` LIKE '%$this->eventList_term%' OR
                            cal.`placeName` LIKE '%$this->eventList_term%') AND ";
        } else {
            $term_where =   "FROM ".DBPREFIX."module_calendar".MODULE_INDEX." AS cal
                            LEFT JOIN ".DBPREFIX."module_calendar".MODULE_INDEX."_categories AS cat ON (cat.id = cal.catid)
                            WHERE (cat.lang = '".$_LANGID."') AND ";
        }
        if (isset($this->eventList_category) && $this->eventList_category != 0) {
            $cat_where = " AND cal.catid='$this->eventList_category' ";
        } else {
            $cat_where = "";
        }
        $query = "
            SELECT cal.id, cal.catid, cal.name, cal.comment, cal.pic,
                   cal.startdate, cal.priority, cal.enddate, cal.placeName,
                   cal.access, cal.series_status, cal.series_type,
                   cal.series_pattern_count, cal.series_pattern_weekday,
                   cal.series_pattern_day, cal.series_pattern_week,
                   cal.series_pattern_month, cal.series_pattern_type,
                   cal.series_pattern_dourance_type,
                   cal.series_pattern_end, cal.series_pattern_begin
                   $term_where $date_where $auth_where
                   $cat_where $active_where
                   ORDER BY cal.startdate
        ";
        $objResult = $objDatabase->Execute($query);
        if ($objResult !== false) {
            $count = $objResult->RecordCount();
            $i = 0;
            if ($count >= 1) {
                while (!$objResult->EOF) {
                    $tmpArray = array();
                    $tmpArray['id']             = $objResult->fields['id'];
                    $tmpArray['name']           = $objResult->fields['name'];
                    $tmpArray['pic']            = $objResult->fields['pic'];
                    $tmpArray['comment']        = $objResult->fields['comment'];
                    $tmpArray['priority']       = $objResult->fields['priority'];
                    $tmpArray['placeName']      = $objResult->fields['placeName'];
                    $tmpArray['startdate']      = $objResult->fields['startdate'];
                    $tmpArray['enddate']        = $objResult->fields['enddate'];
                    $tmpArray['access']         = $objResult->fields['access'];
                    $tmpArray['series_status']  = $objResult->fields['series_status'];
                    if ($objResult->fields['series_status'] == 1 ) {
                        $tmpArray['series_type']                    = $objResult->fields['series_type'];
                        $tmpArray['series_pattern_count']           = $objResult->fields['series_pattern_count'];
                        $tmpArray['series_pattern_weekday']         = $objResult->fields['series_pattern_weekday'];
                        $tmpArray['series_pattern_day']             = $objResult->fields['series_pattern_day'];
                        $tmpArray['series_pattern_week']            = $objResult->fields['series_pattern_week'];
                        $tmpArray['series_pattern_month']           = $objResult->fields['series_pattern_month'];
                        $tmpArray['series_pattern_type']            = $objResult->fields['series_pattern_type'];
                        $tmpArray['series_pattern_dourance_type']   = $objResult->fields['series_pattern_dourance_type'];
                        $tmpArray['series_pattern_end']             = $objResult->fields['series_pattern_end'];
                        $tmpArray['series_pattern_begin']           = $objResult->fields['series_pattern_begin'];
                    }
                    $this->eventList[] = $tmpArray;
                    $i++;
                    $objResult->MoveNext();
                }
            }
        }
        /*print_r("<pre>");
        print_r($this->eventList);
        print_r("</pre>");*/
    }


    function _checkEventList()
    {
        $this->_sortEventList();
        $key = 0;
        while ($this->eventList_callback) {
            if (!empty($this->eventList[$key]['series_status'])) {
                $this->eventList[$key]['series_status'] = 0;
                $this->_getNextSeriesEvent($key);
                $this->_cleanupEventList($key);
                $this->_checkEventList();
                $this->_checkCallback(null, null, true);
            }
            $this->_cleanupEventList($key);
            $this->_checkCallback($key, null, null);
            $key++;
        }
    }


    function _checkCallback($count=null, $date=null, $abort=null) {
        if (isset($abort)) {
            $this->eventList_callback = false;
        }
        if (isset($count)) {
            if($count == $this->eventList_maxsize) {
                $this->eventList_callback = false;
            }
        }
        /*echo "count cb: ".$count."<br>";
        echo "date cb: ".$date."<br>";
        echo "abort cb: ".$abort."<br>";*/
        if (isset($date) && isset($this->eventList_enddate) && $this->eventList_enddate != 0) {
            //echo $date." - ".$this->eventList_enddate."<br>";
            if($date > $this->eventList_enddate) {
                //echo "weg<br>";
                $this->eventList_callback = false;
            }
        }
    }


    function _sortEventList()
    {
        usort($this->eventList, array(__CLASS__, "cmp"));
    }


    function cmp($a, $b)
    {
        if ($a['startdate'] == $b['startdate']) {
            return 0;
        }
        return ($a['startdate'] < $b['startdate']) ? -1 : 1;
    }


    function _cleanupEventList($key)
    {
        if (isset($key) && isset($this->eventList_startdate) && $this->eventList_startdate != 0) {
            if (   isset($this->eventList[$key]['startdate'])
                && $this->eventList[$key]['startdate'] < $this->eventList_startdate) {
                unset($this->eventList[$key]);
            }
        }
    }


    function _getNextSeriesEvent($key)
    {
        $old_startdate      = $this->eventList[$key]['startdate'];
        $old_enddate        = $this->eventList[$key]['enddate'];
        switch ($this->eventList[$key]['series_type']) {
            case 1:
                //daily
                if ($this->eventList[$key]['series_pattern_type'] == 1) {
                    $hour       = date("H", $old_startdate);
                    $minutes    = date("i", $old_startdate);
                    $seconds    = date("s", $old_startdate);
                    $day        = date("d", $old_startdate)+$this->eventList[$key]['series_pattern_day'];
                    $month      = date("m", $old_startdate);
                    $year       = date("Y", $old_startdate);

                    $new_startdate = mktime($hour, $minutes, $seconds, $month, $day, $year);

                    $hour       = date("H", $old_enddate);
                    $minutes    = date("i", $old_enddate);
                    $seconds    = date("s", $old_enddate);
                    $day        = date("d", $old_enddate)+$this->eventList[$key]['series_pattern_day'];
                    $month      = date("m", $old_enddate);
                    $year       = date("Y", $old_enddate);

                    $new_enddate = mktime($hour, $minutes, $seconds, $month, $day, $year);
                } else {
                    $old_weekday = date("w", $old_startdate);

                    if ($old_weekday == 5) {
                        $add_days = 3;
                    } else {
                        $add_days = 1;
                    }

                    $hour       = date("H", $old_startdate);
                    $minutes    = date("i", $old_startdate);
                    $seconds    = date("s", $old_startdate);
                    $day        = date("d", $old_startdate)+$add_days;
                    $month      = date("m", $old_startdate);
                    $year       = date("Y", $old_startdate);

                    $new_startdate = mktime($hour, $minutes, $seconds, $month, $day, $year);

                    $hour       = date("H", $old_enddate);
                    $minutes    = date("i", $old_enddate);
                    $seconds    = date("s", $old_enddate);
                    $day        = date("d", $old_enddate)+$add_days;
                    $month      = date("m", $old_enddate);
                    $year       = date("Y", $old_enddate);

                    $new_enddate = mktime($hour, $minutes, $seconds, $month, $day, $year);
                }
            break;
            case 2:
                //weekly
                $old_weekday        = date("w", $old_startdate);
                $weekday_pattern    = $this->eventList[$key]['series_pattern_weekday'];
                $match              = false;
                $i                  = 0;
                $old_kw             = date("W", $old_startdate);

                while (!$match) {
                    $i++;

                    if(substr($weekday_pattern, $old_weekday, 1) == 1) {
                        $add_days = $i;
                        $match = true;
                    } else {
                        $old_weekday++;
                    }
                    if ($old_weekday > 6) {
                        $old_weekday = 0;
                    }
                }
                $hour    = date("H", $old_startdate);
                $minutes = date("i", $old_startdate);
                $seconds = date("s", $old_startdate);
                $day     = date("d", $old_startdate)+$add_days;
                $month   = date("m", $old_startdate);
                $year    = date("Y", $old_startdate);
                $new_kw  = date("W", mktime($hour, $minutes, $seconds, $month, $day, $year));

                if ($this->eventList[$key]['series_pattern_week'] > 1) {
                    if ($old_kw < $new_kw) {
                        $add_weeks = ($this->eventList[$key]['series_pattern_week']-1)*7;
                    }
                }

                $new_startdate = mktime($hour, $minutes, $seconds, $month, $day+$add_weeks, $year);

                $hour       = date("H", $old_enddate);
                $minutes    = date("i", $old_enddate);
                $seconds    = date("s", $old_enddate);
                $day        = date("d", $old_enddate)+$add_days+$add_weeks;
                $month      = date("m", $old_enddate);
                $year       = date("Y", $old_enddate);

                $new_enddate = mktime($hour, $minutes, $seconds, $month, $day, $year);
                break;
            case 3:
                //monthly
                if ($this->eventList[$key]['series_pattern_type'] == 1) {
                    $month_days = 0;
                    $hour       = date("H", $old_startdate);
                    $minutes    = date("i", $old_startdate);
                    $seconds    = date("s", $old_startdate);
                    $day        = date("d", $old_startdate);
                    $month      = date("m", $old_startdate);
                    $year       = date("Y", $old_startdate);
                    $month_days = date("t", $old_startdate);
                    $add_days   = $month_days-$day+$this->eventList[$key]['series_pattern_day'];
                    $new_startdate = mktime($hour, $minutes, $seconds, $month, $day+$add_days, $year);
                    $hour       = date("H", $old_enddate);
                    $minutes    = date("i", $old_enddate);
                    $seconds    = date("s", $old_enddate);
                    $day        = date("d", $old_enddate);
                    $month      = date("m", $old_enddate);
                    $year       = date("Y", $old_enddate);
                    $new_enddate = mktime($hour, $minutes, $seconds, $month, $day+$add_days, $year);
                } else {
                    $hour       = date("H", $old_startdate);
                    $minutes    = date("i", $old_startdate);
                    $seconds    = date("s", $old_startdate);
                    $day        = date("d", $old_startdate);
                    $month      = date("m", $old_startdate);
                    $year       = date("Y", $old_startdate);
                    $weekday_pattern    = $this->eventList[$key]['series_pattern_weekday'];
                    $count_pattern      = $this->eventList[$key]['series_pattern_count'];
                    $month_pattern      = $this->eventList[$key]['series_pattern_month'];
                    $next_month         = $month + $month_pattern;
                    $match  = false;
                    $i      = 0;
                    while (!$match) {
                        if(substr($weekday_pattern, $i, 1) == 1) {
                            $weekday = $i;
                            $match = true;
                        } else {
                            $i++;
                        }
                    }
                    if ($weekday > 6) {
                        $weekday = 0;
                    }
                    $match  = false;
                    $d      = 0;
                    while (!$match) {
                        $check_date     = mktime($hour, $minutes, $seconds, $next_month, $d, $year);
                        $check_day      = date("w", $check_date);
                        if ($check_day == $weekday) {
                            $match = true;
                        } else {
                            $d++;
                        }
                    }
                    if($count_pattern > 1) {
                        $count_pattern = 7*($count_pattern-1);
                    }
                    $add_days   = ($count_pattern+$d)-$day;
                    $add_month  = $next_month-$month;
                    $new_startdate = mktime($hour, $minutes, $seconds, $month+$add_month, $day+$add_days, $year);
                    $hour       = date("H", $old_enddate);
                    $minutes    = date("i", $old_enddate);
                    $seconds    = date("s", $old_enddate);
                    $day        = date("d", $old_enddate);
                    $month      = date("m", $old_enddate);
                    $year       = date("Y", $old_enddate);
                    $new_enddate = mktime($hour, $minutes, $seconds, $month+$add_month, $day+$add_days, $year);
                }
            break;
        }
        $this->_checkCallback(null, $new_startdate, null);
        if ($this->eventList_callback) {
            switch($this->eventList[$key]['series_pattern_dourance_type']) {
                case 1:
                    $status = 1;
                    $end    = $this->eventList[$key]['series_pattern_end'];
                    $this->_addEventToEventList($key, $new_startdate, $new_enddate, $end, $status);
                break;
                case 2:
                    if ($this->eventList[$key]['series_pattern_end'] >= 2) {
                        $status = 1;
                        $end    = $this->eventList[$key]['series_pattern_end']-1;
                        $this->_addEventToEventList($key, $new_startdate, $new_enddate, $end, $status);
                    }
                break;
                case 3:
                    $end = $this->eventList[$key]['series_pattern_end'];
                    if($new_startdate <= $this->eventList[$key]['series_pattern_end']) {
                        $status = 1;
                        $this->_addEventToEventList($key, $new_startdate, $new_enddate, $end, $status);
                    }
                break;
            }
        }
        $this->eventList_callback = true;
    }


    function _addEventToEventList($key, $startdate, $enddate, $end, $status)
    {
        $tmpArray = array();
        $tmpArray['id']                 = $this->eventList[$key]['id'];
        $tmpArray['startdate']          = $startdate;
        $tmpArray['enddate']            = $enddate;
        $tmpArray['series_status']      = $status;
        $tmpArray['priority']           = $this->eventList[$key]['priority'];
        $tmpArray['placeName']          = $this->eventList[$key]['placeName'];
        $tmpArray['name']               = $this->eventList[$key]['name'];
        $tmpArray['comment']            = $this->eventList[$key]['comment'];
        $tmpArray['pic']                = $this->eventList[$key]['pic'];
        $tmpArray['access']             = $this->eventList[$key]['access'];
        if ($tmpArray['series_status'] == 1 ) {
            $tmpArray['series_type']                    = $this->eventList[$key]['series_type'];
            $tmpArray['series_pattern_count']           = $this->eventList[$key]['series_pattern_count'];
            $tmpArray['series_pattern_weekday']         = $this->eventList[$key]['series_pattern_weekday'];
            $tmpArray['series_pattern_day']             = $this->eventList[$key]['series_pattern_day'];
            $tmpArray['series_pattern_week']            = $this->eventList[$key]['series_pattern_week'];
            $tmpArray['series_pattern_month']           = $this->eventList[$key]['series_pattern_month'];
            $tmpArray['series_pattern_type']            = $this->eventList[$key]['series_pattern_type'];
            $tmpArray['series_pattern_dourance_type']   = $this->eventList[$key]['series_pattern_dourance_type'];
            $tmpArray['series_pattern_end']             = $end;
            $tmpArray['series_pattern_begin']           = $this->eventList[$key]['series_pattern_begin'];
        }
        $this->eventList[] = $tmpArray;
    }

}

?>
