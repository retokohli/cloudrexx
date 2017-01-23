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
 * Represents a template widget that is handled by ESI
 *
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */

namespace Cx\Core_Modules\Widget\Model\Entity;

/**
 * Represents a template widget that is handled by ESI
 * Usage:
 * ```php
 * $this->getComponent('Widget')->registerWidget(
 *     new \Cx\Core_Modules\Widget\Model\Entity\EsiWidget(
 *         $this->getSystemComponentController(),
 *         'FOO'
 *     )
 * );
 * ```
 * The above example replaces Sigma placeholder "FOO" by return value of
 * JsonAdapter method "getWidget" of JsonAdapter named after $this->getName()
 * @author Michael Ritter <michael.ritter@cloudrexx.com>
 * @package cloudrexx
 * @subpackage coremodules_widget
 */
class EsiWidget extends Widget {

    /**
     * Instanciates a new widget
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $component Component registering this widget
     * @param string $name Name of this widget
     * @param boolean $hasContent (optional) Wheter this widget has content or not
     * @param string $jsonAdapterName (optional) Name of the JsonAdapter to call. If not specified, $component->getName() is used
     * @param string $jsonMethodName (optional) Name of the JsonAdapter method to call. If not specified, "getWidget" is used
     * @param array $jsonParams (optional) Params to pass on JsonAdapter call. If not specified, a default list is used, see getEsiParams()
     */
    public function __construct($component, $name, $hasContent = false, $jsonAdapterName = '', $jsonMethodName = '', $jsonParams = array()) {
        parent::__construct($component, $name, $hasContent);
        $this->jsonAdapterName = $jsonAdapterName;
        $this->jsonMethodName = $jsonMethodName;
        $this->jsonParams = $jsonParams;
    }

    /**
     * Returns the name of the JsonAdapter to call
     * @return string JsonAdapter name
     */
    public function getJsonAdapterName() {
        if (empty($this->jsonAdapterName)) {
            return $this->getRegisteringComponent()->getName();
        }
        return $this->jsonAdapterName;
    }

    /**
     * Returns the name of the JsonAdapter method to call
     * @return string JsonAdapter method name
     */
    public function getJsonMethodName() {
        if (empty($this->jsonMethodName)) {
            return 'getWidget';
        }
        return $this->jsonMethodName;
    }

    /*
     * Really parses this widget into $template
     * If this Widget has no content, the replacement can simply be returned
     * as string. Otherwise the replacement must be done in $template.
     * @param \HTML_Template_Sigma $template Template to parse this widget into
     * @param \Cx\Core\Routing\Model\Entity\Reponse $response Current response object
     * @param string $targetComponent Parse target component name
     * @param string $targetEntity Parse target entity name
     * @param string $targetId Parse target entity ID
     * @return string Replacement for widgets without content, NULL otherwise
     */
    public function internalParse($template, $response, $targetComponent, $targetEntity, $targetId) {
        return $this->getComponent('Cache')->getEsiContent(
            $this->getJsonAdapterName(),
            $this->getJsonMethodName(),
            $this->getEsiParams($targetComponent, $targetEntity, $targetId)
        );
        if (!$this->hasContent()) {
            return $esiContent;
        }
        $template->replaceBlock($this->getName(), $esiContent);
        $template->touchBlock($this->getName());
    }

    /**
     * Returns the params for the JsonAdapter call
     * @param string $targetComponent Parse target component name
     * @param string $targetEntity Parse target entity name
     * @param string $targetId Parse target entity ID
     * @return array List of params
     */
    protected function getEsiParams($targetComponent, $targetEntity, $targetId) {
        if (empty($this->jsonParams)) {
            return array(
                'name' => $this->getName(),
                'theme' => \Env::get('init')->getCurrentThemeId(),
                'page' => $this->cx->getPage()->getId(),
                'targetComponent' => $targetComponent,
                'targetEntity' => $targetEntity,
                'targetId' => $targetId,
            );
        }
        return $this->jsonParams;
    }
}
