<?php   
class CalendarWebserviceClient
{        
    private $SOAPClient;
    
    public function __construct($location, $uri) {  
        $options = array(
            'location' => $location,
            'uri'      => $uri
        );

        $this->SOAPClient = new SoapClient(null, $options); 
    }       
    
    public function verifyHost($myHost,$foreignKey) { 
        return $this->SOAPClient->verifyHost($myHost,$foreignKey);
    }
    
    function getEventList($start_date, $end_date, $auth, $term, $langId, $foreignHostId, $myHostId, $showEventsOnlyInActiveLanguage) {
       return $this->SOAPClient->getEventList($start_date, $end_date, $auth, $term, $langId, $foreignHostId, $myHostId, $showEventsOnlyInActiveLanguage);        
    } 
}