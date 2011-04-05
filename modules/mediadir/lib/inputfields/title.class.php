<?php
/**
 * Media  Directory Inputfield Text Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfields/inputfield.interface.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/placeholder.class.php';

class mediaDirectoryInputfieldTitle extends mediaDirectoryLibrary implements inputfield
{
    public $arrPlaceholders = array('TXT_MEDIADIR_INPUTFIELD_NAME');



    /**
     * Constructor
     */
    function __construct()
    {
        parent::getFrontendLanguages();
        parent::getSettings();
    }



    function getInputfield($intView, $arrInputfield, $intEntryId=null)
    {
        global $objDatabase, $_LANGID, $objInit, $_ARRAYLANG; 
        
        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                if($objInit->mode == 'backend') {
                    return null;
                } else { 
                    return "<br />";
                }   
                
                break;
            case 2:
                //search View         
                break;
        }
    }



    function saveInputfield($intInputfieldId, $strValue)
    {
        //$strValue = contrexx_addslashes(contrexx_strip_tags($strValue));
        return null;
    }


    function deleteContent($intEntryId, $intIputfieldId)
    {                                                
        return true; 
    }



    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus)
    {     
        $arrContent['TXT_'.$this->moduleLangVar.'_INPUTFIELD_NAME'] = '<h2>'.htmlspecialchars($arrInputfield['name'][0], ENT_QUOTES, CONTREXX_CHARSET).'</h2>';
        $arrContent[$this->moduleLangVar.'_INPUTFIELD_VALUE'] = '';
        
        return $arrContent;
    }


    function getJavascriptCheck()
    {             
        return null;
    }
    
    
    function getFormOnSubmit($intInputfieldId)
    {
        return null;
    }
}
