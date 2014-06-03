<?php
/**
 * Calendar 
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */


/**
 * Calendar Class WebserviceClient
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */
class CalendarWebserviceClient
{        
    /**
     * SOAP Client object
     *
     * @access private
     * @var object
     */
    private $SOAPClient;
    
    /**
     * CalendarWebserviceClient Constructor
     * 
     * @param string $location the URL of the SOAP server to send the request
     * @param string $uri      the target namespace of the SOAP service          
     */
    public function __construct($location, $uri) {  
        $options = array(
            'location' => $location,
            'uri'      => $uri
        );

        $this->SOAPClient = new SoapClient(null, $options); 
    }       
    
    /**
     * verify the host name, if its exists returns the host data
     * 
     * @param string $myHost     host name
     * @param string $foreignKey reference Key
     * 
     * @return mixed host details on success, false otherwise
     */
    public function verifyHost($myHost,$foreignKey) { 
        return $this->SOAPClient->verifyHost($myHost,$foreignKey);
    }
    
    /**
     * Get the event list
     * 
     * @param integer $start_date                     Start date
     * @param integer $end_date                       End date
     * @param boolean $auth                           Authorization
     * @param string  $term                           search term
     * @param integer $langId                         Language id
     * @param integer $foreignHostId                  Foreign Host id
     * @param integer $myHostId                       Host id
     * @param boolean $showEventsOnlyInActiveLanguage get event only active 
     *                                                frontend language
     * 
     * @return array Event list object
     */
    function getEventList($start_date, $end_date, $auth, $term, $langId, $foreignHostId, $myHostId, $showEventsOnlyInActiveLanguage) {
       return $this->SOAPClient->getEventList($start_date, $end_date, $auth, $term, $langId, $foreignHostId, $myHostId, $showEventsOnlyInActiveLanguage);        
    } 
}