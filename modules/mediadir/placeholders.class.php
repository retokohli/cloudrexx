<?php

/**
 * Media  Directory
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/category.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/level.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';

class mediaDirectoryPlaceholders extends mediaDirectoryLibrary
{
    private $strPlaceholder;          
    
    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct('.');
        parent::getSettings();
    }
    
    function getNavigationPlacholder()
    {                 
        $this->strPlaceholder = null;
        
        if($this->arrSettings['settingsShowLevels'] == 1) {
        	$objLevels = new mediaDirectoryLevel();
	        $intLevelId = isset($_GET['lid']) ? intval($_GET['lid']) : null;
	        
	        $this->strPlaceholder = $objLevels->listLevels($this->_objTpl, 6, $intLevelId);
        } else {
        	$objCategories = new mediaDirectoryCategory();
            $intCategoryId = isset($_GET['cid']) ? intval($_GET['cid']) : null;
        
            $this->strPlaceholder = $objCategories->listCategories($this->_objTpl, 6, $intCategoryId, null, null, null, 1);
        }
        
        return '<ul id="'.$this->moduleName.'NavigationPlacholder">'.$this->strPlaceholder.'</ul>';
    }
    
    function getLatestPlacholder()
    {                     
        $this->strPlaceholder = null;
        
        $intLimitEnd = intval($this->arrSettings['settingsLatestNumOverview']); 
        
        $objEntries = new mediaDirectoryEntry(); 
        $objEntries->getEntries(null,null,null,null,true,null,1,null,$intLimitEnd);  
        
        foreach($objEntries->arrEntries as $intEntryId => $arrEntry) {
            if($objEntries->checkPageCmd('detail'.intval($arrEntry['entryFormId']))) {
                $strDetailCmd = 'detail'.intval($arrEntry['entryFormId']);
            } else {
                $strDetailCmd = 'detail';
            }                                                                            
            
            $strDetailUrl = 'index.php?section='.$this->moduleName.'&amp;cmd='.$strDetailCmd.'&amp;eid='.$arrEntry['entryId'];
        
            $this->strPlaceholder .= '<li><a href="'.$strDetailUrl.'">'.$arrEntry['entryFields'][0].'</a></li>';    
        } 
        
        return '<ul id="'.$this->moduleName.'LatestPlacholder">'.$this->strPlaceholder.'</ul>'; 
    }
}
?>
