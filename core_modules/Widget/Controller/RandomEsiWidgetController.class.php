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
 * JsonAdapter Controller to handle EsiWidgets
 *
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */

namespace Cx\Core_Modules\Widget\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */
abstract class RandomEsiWidgetController extends EsiWidgetController {

    /**
     * Parses a widget
     * @todo Add logic to limit randomized ESI widgets and set a timeout
     * @param \Cx\Core_Modules\Widget\Model\Entity\Widget $widget The Widget
     * @param array $params Params passed by ESI (/API) request
     * @return array Content in an associative array
     */
    protected function internalParseWidget($widget, $params) {
        if ($widget instanceof \Cx\Core_Modules\Widget\Model\Entity\RandomEsiWidget) {
            $esiContent = $this->getComponent('Cache')->getRandomizedEsiContent(
                $this->getRandomEsiWidgetContentInfos($widget)
            ); 
            return array(
                'content' => $esiContent,
            );
        }
        return parent::internalParseWidget($widget, $params);
    }

    /**
     * Returns a list of ESI request infos that are to be randomized
     *
     * Each returned entry consists of an array like:
     * array(
     *     <adapterName>,
     *     <adapterMethod>,
     *     <params>,
     * )
     * @param \Cx\Core_Modules\Widget\Model\Entity\Widget $widget The RandomEsiWidget
     * @return array List of URLs
     */
    protected abstract function getRandomEsiWidgetContentInfos($widgetName);
}
