<?php

/**
 * Media Directory Inputfield Label Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfields/inputfield.interface.php';

/**
 * Media Directory Inputfield Label Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */
class mediaDirectoryInputfieldLabel implements inputfield
{
    public $arrPlaceholders = array('MEDIADIR_INPUTFIELD_VALUE');



    /**
     * Constructor
     */
    function __construct()
    {
    }



    function getInputfield($intView, $arrInputfield, $intEntryId=null)
    {
        global $objDatabase, $_LANGID, $objInit;

        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                $strValue = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];

                return $strValue;

                break;
            case 2:
                //search View
                break;
        }
    }



    function saveInputfield($strValue)
    {
        return true;
    }


    function deleteContent($intEntryId, $intIputfieldId)
    {
        return true;
    }



    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus)
    {
        return null;
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
