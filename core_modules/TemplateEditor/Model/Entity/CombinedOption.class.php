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
     * Array with the types of the single options
     *
     * @var array
     */
    protected $options;

    /**
     * Array with values for each single options
     *
     * @var array
     */
    protected $elements;

    /**
     * @param String $name          Name of the option
     * @param array  $translations  Array with translations for option.
     * @param array  $data          the specific data for this option
     * @param String $type          the type of the option
     * @param Group  $group         the group of the option
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
        $this->options = $data['options'];
        $this->elements = $data['elements'];
    }

    /**
     * Render the option field in the backend.
     *
     * @return Sigma    the backend template
     */
    public function renderOptionField()
    {
        $elements = array();
        // is used to storage the parsed template of the single options
        $template = '';
        // parse each single option, using its own renderOptionField method
        foreach($this->elements as $id => $element) {
            // we need to instantiate each single option, because otherwise we
            // can not use the renderOptionField method
            $singleOptionType = $this->options[$id]['type'];
            $option = new $singleOptionType (
                $this->name . '_combinedId' .$id,
                '',
                $element,
                $this->options[$id]['type'],
                $this->group,
                false
            );
            $template .= $option->renderOptionField()->get();
        }
        $elements['elements'][$this->name]['COMBINED_ELEMENT'] = $template;
        return parent::renderOptionField(
            array(),
            array(),
            $elements
        );
    }

    /**
     * Render the option in the frontend
     *
     * Pattern for placeholder name is:
     * TEMPLATE_EDITOR_{NAME_OF_COMBINED_OPTION}_{ID_OF_OF_SINGLE_OPTION}
     * ID_OF_OF_SINGLE_OPTION = the numeric array key in field $this->options
     *
     * @param Sigma $template the frontend template
     */
    public function renderTheme($template)
    {
        foreach($this->elements as $key => $element) {
            $template->setVariable(
                'TEMPLATE_EDITOR_' . strtoupper($this->name) . '_' . $key,
                current($element)
            );
        }
    }

    /**
     * Handle a change of the option.
     *
     * @param  array  $data  data from frontend javascript
     * @return array         new specific values
     */
    public function handleChange($data)
    {
        $optionData = $this->elements[$data['id']];
        $optionClass = $this->options[$data['id']]['type'];
        $option = new $optionClass(
            '',
            array(),
            $optionData,
            $this->type
        );
        // parse the new value using the single option class
        $newValue = $option->handleChange($data['value']);
        if ($newValue) {
            $this->elements[$data['id']][key($optionData)] =
                $newValue[key($optionData)];
            return array(
                'options' => $this->options,
                'elements' => $this->elements,
            );
        }


    }

    /**
     * Gets the current value of the option.
     *
     * @return array    array with the elements
     */
    public function getValue()
    {
        return array(
            'elements' => $this->elements
        );
    }
}