<?php
/**
 * Media  Directory Inputfield Textarea Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */

class mediaDirectoryInputfieldAdd_step extends mediaDirectoryLibrary
{
    public $arrPlaceholders = array('TXT_MEDIADIR_INPUTFIELD_NAME');


    /**
     * Constructor
     */
    function __construct()
    {
    }


    function getInputfield($intView, $arrInputfield, $intEntryId=null, $objAddStep)
    {
        global $objDatabase, $_LANGID, $objInit;

        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                if($objInit->mode == 'backend') {
                    return null;
                } else {
                    $arrStepInfos = $objAddStep->getLastStepInformations();

                    $strValue = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];


                    if($arrStepInfos['first'] == true) {
                        $strNotFirst = '';
                        $strDisplay = 'block';
                    } else {
                        $strNotFirst = '</div>';
                        $strDisplay = 'none';
                    }

                    return $strNotFirst.'<div id="Step_'.$arrStepInfos['id'].'" class="'.$this->moduleName.'AddStep" style="display: '.$strDisplay.'; float: left; width: 100%; height: auto !important;"><p class="'.$this->moduleName.'AddStepText">'.$strValue.'</p>';
                }

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
}
