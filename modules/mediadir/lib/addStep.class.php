<?php
/**
 * Media  Directory Inputfield Textarea Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */

class mediaDirectoryAddStep
{
    var $arrSteps = array();

    /**
     * Constructor
     */
    function __construct()
    {
    }

    function addNewStep($strStepName) {
        $this->arrSteps[] = $strStepName;
    }

    function getStepNavigation($objTpl) {
        foreach ($this->arrSteps as $intStepId => $strStepName){
            $objTpl->setVariable(array(
                'MEDIADIR_ENTRY_ADDSTEP_NAME' => $strStepName,
                'MEDIADIR_ENTRY_ADDSTEP_HREF' => "javascript:selectAddStep('Step_".$intStepId."');",
                'MEDIADIR_ENTRY_ADDSTEP_ID' => "mediadirAddStep_Step_".$intStepId,
                'MEDIADIR_ENTRY_ADDSTEP_CLASS' => $intStepId == 0 ? "active" : "",
            ));

            $objTpl->parse('mediadirEntryAddStepNavigationElement');
        }
    }

    function getLastStepInformations() {
        $arrStepInfos['name'] = end($this->arrSteps);
        $arrStepInfos['id'] = current(array_keys($this->arrSteps, $arrStepInfos['name']));;
        $arrStepInfos['position'] = count($this->arrSteps);
        $arrStepInfos['first'] = $arrStepInfos['position'] == 1 ? true : false;

        return $arrStepInfos;
    }
}
