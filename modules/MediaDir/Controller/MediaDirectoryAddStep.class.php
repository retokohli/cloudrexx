<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Medi Directory Add Step Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Controller;
/**
 * Media Directory Add Step Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
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
