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

namespace Cx\Core_Modules\Widget\Model\Entity;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */
abstract class EsiWidgetController extends \Cx\Core\Core\Model\Entity\Controller implements \Cx\Core\Json\JsonAdapter {

    /**
     * Returns the internal name used as identifier for this adapter
     * @see \Cx\Core\Json\JsonAdapter::getName()
     * @return string Name of this adapter
     */
    public function getName() {
        return parent::getName();
    }

    /**
     * Returns all messages as string
     * @see \Cx\Core\Json\JsonAdapter::getMessagesAsString()
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return '';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @see \Cx\Core\Json\JsonAdapter::getMessagesAsString()
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array(
            'getWidget',
        );
    }

    /**
     * Returns default permission as object
     * @see \Cx\Core\Json\JsonAdapter::getMessagesAsString()
     * @return Cx\Core_Modules\Access\Model\Entity\Permission Required permission
     */
    public function getDefaultPermissions() {
        return new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false);
    }

    /**
     * Returns the content of a widget
     * @param array $params JsonAdapter parameters
     * @return array Content in an associative array
     */
    public function getWidget($params) {
        $requiredParams = array(
            'theme',
            'page',
            'lang',
            'name',
            'targetComponent',
            'targetEntity',
            'targetId',
        );
        $params['get'] = contrexx_input2raw($params['get']);
        foreach ($requiredParams as $requiredParam) {
            if (!isset($params['get']) || !isset($params['get'][$requiredParam])) {
                throw new \InvalidArgumentException('Param "' . $requiredParam . '" not set');
            }
        }

        // resolve widget template
        $widgetTemplate = $this->getComponentController()->getWidgetTemplate(
            $params['get']['name'],
            $params['get']['theme'],
            $params['get']['page'],
            $params['get']['targetComponent'],
            $params['get']['targetEntity'],
            $params['get']['targetId']
        );

        return array(
            'content' => $this->parseWidget(
                $params['get']['name'],
                $widgetTemplate,
                $params['get']['lang']
            )
        );
    }

    /**
     * Parses a widget
     * @param string $name Widget name
     * @param \Cx\Core\Html\Sigma Widget template
     * @param string $locale RFC 3066 locale identifier
     */
    public abstract function parseWidget($name, $template, $locale);
}
