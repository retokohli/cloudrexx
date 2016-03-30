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
 * Calendar Class Headlines
 * 
 * @package    contrexx
 * @subpackage module_calendar
 * @author     Comvation <info@comvation.com>
 * @copyright  CONTREXX CMS - COMVATION AG
 * @version    1.00
 */
class CalendarHeadlines extends CalendarLibrary
{    
    /**
     * Event manager object
     *
     * @access public
     * @var object 
     */
    private $objEventManager;
    
    /**
     * Headlines constructor
     * 
     * @param string $pageContent Template content
     */
    function __construct($pageContent) {
        parent::__construct('.');   
        parent::getSettings();   
        
        $this->pageContent = $pageContent;    
        
        CSRF::add_placeholder($this->_objTpl);
    }
    
    /**
     * Load the event manager
     * 
     * @return null
     */
    function loadEventManager()
    {
        if($this->arrSettings['headlinesStatus'] == 1 && $this->_objTpl->blockExists('calendar_headlines_row')) {                        
            $startDate = new \DateTime();
            $startDate->setTime(0, 0, 0);
            $endDate = new \DateTime();
            $endDate->setTime(23, 59, 59);
            $endDate->modify('+10 years');
            $categoryId = intval($this->arrSettings['headlinesCategory']) != 0 ? intval($this->arrSettings['headlinesCategory']) : null;

            $startPos = 0;
            $endPos = $this->arrSettings['headlinesNum'];

            $this->objEventManager = new CalendarEventManager($startDate,$endDate,$categoryId,$searchTerm,true,$needAuth,true,$startPos,$endPos);
            $this->objEventManager->getEventList();
        }
    }
    
    /**
     * Return's headlines
     *      
     * @return string parsed template content
     */
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
