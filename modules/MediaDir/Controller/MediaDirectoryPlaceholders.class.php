<?php

/**
 * Media Directory Placeholders
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Controller;
/**
 * Media Directory Placeholders
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryPlaceholders extends MediaDirectoryLibrary
{
    private $strPlaceholder;          
    
    /**
     * Constructor
     */
    function __construct($name)
    {
        
        parent::__construct('.', $name);
        parent::getSettings();
    }
    
    function getNavigationPlacholder()
    {
        $this->strPlaceholder = null;
        
        if($this->arrSettings['settingsShowLevels'] == 1) {
        	$objLevels = new MediaDirectoryLevel(null, null, 0, $this->moduleName);
	        $intLevelId = isset($_GET['lid']) ? intval($_GET['lid']) : null;
	        
	        $this->strPlaceholder = $objLevels->listLevels($this->_objTpl, 6, $intLevelId);
        } else {
        	$objCategories = new MediaDirectoryCategory(null, null, 0, $this->moduleName);
            $intCategoryId = isset($_GET['cid']) ? intval($_GET['cid']) : null;
        
            $this->strPlaceholder = $objCategories->listCategories($this->_objTpl, 6, $intCategoryId, null, null, null, 1);
        }
        
        return '<ul id="'.$this->moduleNameLC.'NavigationPlacholder">'.$this->strPlaceholder.'</ul>';
    }
    
    function getLatestPlacholder()
    {
        $this->strPlaceholder = null;
        
        $intLimitEnd = intval($this->arrSettings['settingsLatestNumOverview']); 
        
        $objEntries = new MediaDirectoryEntry($this->moduleName); 
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
        
        return '<ul id="'.$this->moduleNameLC.'LatestPlacholder">'.$this->strPlaceholder.'</ul>'; 
    }
}
?>
