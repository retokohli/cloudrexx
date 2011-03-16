<?php
/**
 * Calendar Class Event
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_calendar
 * @todo        Edit PHP DocBlocks!
 *
 * This Class was built in addition to module expansion..
 * its not used all over the module..
 * but if someone has to rewrite the module it might be quite useful
 */


class CalendarEvent {
    var $values = array();
    var $mandateLink;
    function __construct($mandate = '') {
    	$this->mandateLink = $mandate;
        $this->values = array(
            'id'            => 0,
            'catid'         => 0,
            'active'        => 0,
            'startdate'     => 0,
            'enddate'       => 0,
            'priority'      => 3,
            'access'        => 0,
            'name'          => '',
            'comment'       => '',
            'placeName'     => '',
            'link'          => '',
            'pic'           => '',
            'attachment'    => '',
            'placeStreet'   => '',
            'placeZip'      => '',
            'placeCity'     => '',
            'placeLink'     => '',
            'placeMap'      => '',
            'organizerName' => '',
            'organizerStreet'=>'',
            'organizerZip'  => '',
            'organizerPlace'=> '',
            'organizerMail' => '',
            'organizerLink' => '',
            'key'           => '',
            'num'           => 0,
            'mailTitle'     => '',
            'mailContent'   => '',
            'registration'  => 0,
            'groups'        => '',
            'all_groups'    => 0,
            'notification'  => 0,
            'notificationAddress' 		=> '',
            'registrationSubscriber' 	=> '',
            'public '       => '',
            
            'series_status' => 0,
            'series_type'   => 0,
            'series_pattern_count'      => 0,
            'series_pattern_weekday'    => '',
            'series_pattern_day'        => 0,
            'series_pattern_week'      => 0,
            'series_pattern_month'      => 0,
            'series_pattern_type'       => 0,
            'series_pattern_dourance_type' => '',
            'series_pattern_end'        => 0,
            'series_pattern_exceptions' => '',
        );
    }

    function set($values) {
        if(!$values) {
            return false;
        }
        foreach ($values as $key => $value) {
            if(isset($this->values[$key])) {
                $this->values[$key] = $value;
            }
        }
        return true;
    }

    function insert($values) {
        global $objDatabase;
        if(!$this->set($values)) {
            throw new Exception("could not insert event because of invalid arguments");
        }

        $v = $this->values;
        $query = "INSERT INTO ".DBPREFIX."module_calendar".$this->mandateLink." (
            active, catid, startdate, enddate,
            priority, access, name, comment,
            link, pic, attachment, placeName,
            placeStreet, placeZip, placeCity, placeLink,
            placeMap, organizerName, organizerStreet, organizerZip,
            organizerPlace, organizerMail, organizerLink, registration,
            groups, all_groups, public, mailTitle,
            mailContent, `key`, num, notification,
            notification_address,series_status, series_type, series_pattern_count,
            series_pattern_weekday, series_pattern_day,
            series_pattern_week, series_pattern_month,
            series_pattern_type, series_pattern_dourance_type,
            series_pattern_end, series_pattern_exceptions
        ) VALUES (
            '".$v['active']."',  '".$v['catid']."',  '".$v['startdate']."',  '".$v['enddate']."',
            '".$v['priority']."',  '".$v['access']."',  '".$v['name']."',  '".$v['comment']."',
            '".$v['link']."',  '".$v['pic']."',  '".$v['attachment']."',  '".$v['placeName']."',
            '".$v['placeStreet']."',  '".$v['placeZip']."',  '".$v['placeCity']."',  '".$v['placeLink']."',
            '".$v['placeMap']."',  '".$v['organizerName']."',  '".$v['organizerStreet']."',  '".$v['organizerZip']."',
            '".$v['organizerPlace']."',  '".$v['organizerMail']."',  '".$v['organizerLink']."',  '".$v['registration']."',
            '".$v['groups']."',  '".$v['all_groups']."',  '".$v['public']."',  '".$v['mailTitle']."',
            '".$v['mailContent']."',  '".$v['key']."',  '".$v['num']."',  '".$v['notification']."',
            '".$v['notificationAddress']."',
            '".$v['series_status']."', '".$v['series_type']."', '".$v['series_pattern_count']."',
            '".$v['series_pattern_weekday']."', '".$v['series_pattern_day']."',
            '".$v['series_pattern_week']."', '".$v['series_pattern_month']."',
            '".$v['series_pattern_type']."', '".$v['series_pattern_dourance_type']."',
            '".$v['series_pattern_end']."', '".$v['series_pattern_exceptions']."'
        )";

        $objResult = $objDatabase->Execute($query);
        if($objResult === false) {
            throw  new Exception("error inserting new event with $query");
        }

        return $objDatabase->Insert_ID();

    }

    /**
     * Sets a new Active state for an event
     *
     * @param boolean or integer $active
     * @param integer $eventid
     * @return true on success, false otherwise
     */
    function setActive($active, $eventid) {
        global $objDatabase;
        $active = intval($active);
        $eventid = intval($eventid);

        $query = "UPDATE ".DBPREFIX."module_calendar".$this->mandateLink." SET active=$active WHERE id=$eventid";
        $objRs = $objDatabase->Execute($query);
        if(!$objRs) {
            return false;
        }
        return true;
    }

    function get($id) {
        global $objDatabase;
        $id = intval($id);
        $query = "SELECT * FROM ".DBPREFIX."module_calendar".$this->mandateLink." WHERE id=$id";
        $objRs = $objDatabase->Execute($query);
        if(!$objRs) {
        	echo $query."<br>";
            return false;
        }

        return $this->set($objRs->fields);

    }

    function update($values) {
        $this->set($values);
    }

    function getActive() {
        return $this->values['active'];
    }


}
?>