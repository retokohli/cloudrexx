<?php

/**
 * Calendar
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation 
 * @package     contrexx                                
 * @todo        Edit PHP DocBlocks!
 */
class CalendarHeadlines extends CalendarLibrary
{    
    private $objEventManager;
    
    function __construct($pageContent) {
        parent::__construct('.');   
        parent::getSettings();   
        
        $this->pageContent = $pageContent;    
        
        CSRF::add_placeholder($this->_objTpl);
    }
    
    function loadEventManager()
    {
        if($this->arrSettings['headlinesStatus'] == 1 && $this->_objTpl->blockExists('calendar_headlines_row')) {                        
            $startDate = mktime(0, 0, 0, date("m", mktime()), date("d", mktime()), date("Y", mktime()));                                   
            $enddate = mktime(23, 59, 59, date("m", mktime()), date("d", mktime()), date("Y", mktime())+10);       
            $categoryId = intval($this->arrSettings['headlinesCategory']) != 0 ? intval($this->arrSettings['headlinesCategory']) : null;        
            
            $startPos = 0;   
            $endPos = $this->arrSettings['headlinesNum'];             

            $this->objEventManager = new CalendarEventManager($startDate,$endDate,$categoryId,$searchTerm,true,$needAuth,true,$startPos,$endPos);
            $this->objEventManager->getEventList();
        }
    }
    
    function getHeadlines()
    {                        
        global $_CONFIG;
        
        $this->_objTpl->setTemplate($this->pageContent,true,true);  
        
        if($this->arrSettings['headlinesStatus'] == 1) {   
            if($this->_objTpl->blockExists('calendar_headlines_row')) {                  
                self::loadEventManager();  
                if (!empty($this->objEventManager->eventList)) {              
                    $this->objEventManager->showEventList($this->_objTpl); 
                }   
            }                                               
        } else {
            if($this->_objTpl->blockExists('calendar_headlines_row')) { 
                $this->_objTpl->hideBlock('calendar_headlines_row');
            }
        }  
        
        
        return $this->_objTpl->get();
    }      
}