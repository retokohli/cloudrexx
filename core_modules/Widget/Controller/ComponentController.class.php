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
 * Main controller for Widget handler
 *
 * @author Michael Ritter <michael.ritter@cloadrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */

namespace Cx\Core_Modules\Widget\Controller;

/**
 * Main controller for Widget handler
 *
 * @author Michael Ritter <michael.ritter@cloadrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * List of widgets
     *
     * @var array
     */
    protected $widgets = array();

    /**
     * get controller classes
     *
     * @return array
     */
    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Registers a Widget
     *
     * @param \Cx\Core_Modules\Widget\Model\Entity\Widget $widget Widget to add
     */
    public function registerWidget($widget) {
        $this->widgets[] = $widget;
    }

    /**
     * Parses the widget on a template
     *
     * @param \HTML_Template_Sigma $template Template to parse widgets into
     */
    public function parseWidgets($template) {
        foreach ($this->widgets as $widget) {
            $widget->parse($template);
        }
    }

    /**
     * Returns the list of registered widgets
     * @return array List of \Cx\Core_Modules\Widget\Entity\Widget objects
     */
    public function getWidgets() {
        return $this->widgets;
    }
}
