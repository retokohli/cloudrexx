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
 

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

/**
 * Class CombinedOption
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Adrian Berger <adrian.berger@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class CombinedOption extends Option
{
    /**
     * Array with option types for the combinedOption
     *
     * @var Option[]
     */
    protected $options;

    /**
     * Array with values for the options
     *
     * @var array
     */
    protected $elements;

    /**
     * @param String $name Name of the option
     * @param array  $translations Array with translations for option.
     * @param array  $data
     * @param String $type          the type of the option
     * @param Group  $group        the group of the option
     * @param bool   $series        handle the elements as series if true
     */
    public function __construct(
        $name,
        $translations,
        $data,
        $type,
        $group,
        $series = false
    ) {
        parent::__construct($name, $translations, $data, $type, $group, $series);
        foreach ($data['elements'] as $key => $elm) {
            if (!empty($elm)) {
                $this->elements[$key] = $elm;
            }
        }
        foreach($data['options'] as $options) {
            $this->options[] = $options['type'];
        }
        foreach($data['elements'] as $key => $element) {
            $this->elements[$key] = $element;
        }
    }

    /**
     * Render the option field in the backend.
     *
     * @return Sigma    the template
     */
    public function renderOptionField()
    {
        global $_ARRAYLANG;
        $elements = array();
        $template ='';
        foreach($this->elements as $id => $element) {
            $optionName = $this->options[$id - 1];
            $option = new $optionName (
                $this->name . '_combinedId' .$id,
                '',
                $element,
                $this->options[$id],
                $this->group,
                false
            );
            $template .= $option->renderOptionField()->get();

        }
        $elements['elements'][$this->name . $id]['COMBINED_ELEMENT'] =
            $template;
        return parent::renderOptionField(
            array(),
            array(),
            $elements
        );
    }

    /**
     * Render the option in the frontend.
     *
     * @param Sigma $template
     */
    public function renderTheme($template)
    {
    }

    /**
     * Handle a change of the option.
     *
     * @param array $data Data from frontend javascript
     *
     * @return array Changed data for the frontend javascript
     */
    public function handleChange($data)
    {
        $element = $this->elements[$data['id']];
        $element[key($element)] = $data['value'];
        $this->elements[$data['id']] = $element;
        return array(
            'options' => $this->options,
            'elements' => $this->elements,
        );
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public function getValue()
    {
        return array(
            'options' => $this->options,
            'elements' => $this->elements,
        );
    }
}