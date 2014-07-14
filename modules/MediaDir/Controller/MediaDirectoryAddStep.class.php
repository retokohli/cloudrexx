<?php

/**
 * Medi Directory Add Step Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Controller;
/**
 * Media Directory Add Step Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryAddStep extends MediaDirectoryLibrary
{
    var $arrSteps = array();

    /**
     * Constructor
     */
    function __construct($name)
    {
        parent::__construct('.', $name);
    }

    function addNewStep($strStepName) {
        $this->arrSteps[] = $strStepName;
    }

    function getStepNavigation($objTpl) {
        foreach ($this->arrSteps as $intStepId => $strStepName){
            $objTpl->setVariable(array(
                $this->moduleLangVar.'_ENTRY_ADDSTEP_NAME' => $strStepName,
                $this->moduleLangVar.'_ENTRY_ADDSTEP_HREF' => "javascript:selectAddStep('Step_".$intStepId."');",
                $this->moduleLangVar.'_ENTRY_ADDSTEP_ID' => $this->moduleNameLC."AddStep_Step_".$intStepId,
                $this->moduleLangVar.'_ENTRY_ADDSTEP_CLASS' => $intStepId == 0 ? "active" : "",
            ));

            $objTpl->parse($this->moduleNameLC.'EntryAddStepNavigationElement');
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
