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

class mediaDirectoryAddStep extends mediaDirectoryLibrary
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
                $this->moduleLangVar.'_ENTRY_ADDSTEP_NAME' => $strStepName,
                $this->moduleLangVar.'_ENTRY_ADDSTEP_HREF' => "javascript:selectAddStep('Step_".$intStepId."');",
                $this->moduleLangVar.'_ENTRY_ADDSTEP_ID' => $this->moduleName."AddStep_Step_".$intStepId,
                $this->moduleLangVar.'_ENTRY_ADDSTEP_CLASS' => $intStepId == 0 ? "active" : "",
            ));

            $objTpl->parse($this->moduleName.'EntryAddStepNavigationElement');
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
