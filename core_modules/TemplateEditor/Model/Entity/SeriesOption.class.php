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

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Html\Sigma;
use Cx\Core_Modules\MediaBrowser\Model\Entity\MediaBrowser;

/**
 * Class SeriesOption
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @author      Adrian Berger <adrian.berger@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class SeriesOption extends Option
{

    /**
     * Array with values for series
     *
     * @var array
     */
    protected $elements;

    /**
     * @param String $name         Name of the option
     * @param array  $translations Array with translations for option.
     * @param array  $data         the specific data for this option
     * @param String $type         the type of the option
     * @param Group  $group        the group of the option
     * @param bool   $series       handle the elements as series if true
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
        foreach ($data['elements'] as $key => $element) {
            if (
                $type ==
                    'Cx\Core_Modules\TemplateEditor\Model\Entity\CombinedOption'
            ) {
                // drop index 'elements' if it already exists. This is the case
                // if the data are loaded from the session
                if (isset($element['elements'])) {
                    $element = $element['elements'];
                }
                $this->elements[$key]['elements'] = $element;
                // we also need to store the options for each element,
                // otherwise we don't now the type of the elements
                $this->elements[$key]['options'] = $data['options'];
            } else {
                $this->elements[$key] = $element;
            }
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
        // load the rendered template form the option foreach element in the
        // series, so there is only one template needed per option and we do not
        // need to write a {Option}SeriesOption.html foreach option
        foreach ($this->elements as $id => $elm) {
            $elements['elements'][$id]['SERIES_ELEMENT'] =
                $this->getElementHtmlTemplate($id, $elm)->get();
        }
        return parent::renderOptionField(
            array(
                'TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_ELEMENT' =>
                    $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_ELEMENT'],
            ),
            $_ARRAYLANG,
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
        $blockName = strtolower('TEMPLATE_EDITOR_' . $this->name);
        if (!$template->blockExists($blockName)) {
            return;
        }
        foreach ($this->elements as $element) {
            if (
                $this->type ==
                    'Cx\Core_Modules\TemplateEditor\Model\Entity\CombinedOption'
            ) {
                // for the combinedOptions, we need to parse each single option
                foreach($element["elements"] as $optionId => $option){
                    $template->setVariable(
                        strtoupper(
                            'TEMPLATE_EDITOR_' . $this->name . '_' . $optionId
                        ),
                        contrexx_raw2xhtml(current($option))
                    );
                }
            } else {
                $template->setVariable(
                    strtoupper('TEMPLATE_EDITOR_' . $this->name),
                    contrexx_raw2xhtml(current($element))
                );

            }
            $template->parse($blockName);
        }
    }

    /**
     * Handle a change of the option.
     *
     * @param array $data Data from frontend javascript
     *
     * @return array Changed data for the frontend javascript
     *
     * @throws OptionValueNotValidException If the data which the option should
     *                                      handle is invalid this exception
     *                                      will be thrown.
     *                                      It gets caught by the JsonData
     *                                      class and gets handled by the
     *                                      javascript callback in the frontend.
     */
    public function handleChange($data)
    {
        global $_ARRAYLANG;

        if (
            !is_a(
                $this->type,
                'Cx\Core_Modules\TemplateEditor\Model\Entity\Option',
                true
            )
        ) {
            throw new OptionValueNotValidException(
                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_NO_OPTION']
            );
        }
        if (empty($data['id']) && $data['id'] != 0) {
            throw new OptionValueNotValidException(
                $_ARRAYLANG["TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_WITHOUT_ID"]
            );
        }
        if ($data['value']['elm'] === '' && !isset($data['value']['action'])) {
            throw new OptionValueNotValidException(
                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_EMPTY']
            );
        }
        if ($data['value']['elm'] === '') {
            switch ($data['value']['action']) {
                case 'remove':
                    unset($this->elements[intval($data['id'])]);
                    break;
                case 'add':
                    // to add a new element, we copy the last element and delete
                    // the values in copy, so the array structure is the same
                    end($this->elements);
                    $key = key($this->elements);
                    if (
                        $this->type ==
                            'Cx\Core_Modules\TemplateEditor\Model\Entity\CombinedOption'
                    ) {
                        $emptyElement = $this->elements[$key];
                        $elements = $this->elements[$key]['elements'];
                        // for the combinedOption we need to delete each field
                        // of each single option
                        foreach($elements as $id  => $element) {
                            $emptyElement['elements'][$id] = array_fill_keys(
                                array_keys($element),
                                ""
                            );
                        }
                    } else {
                        $emptyElement = array_fill_keys(
                            array_keys($this->elements[$key]),
                            ""
                        );
                    }
                    $this->elements[] = $emptyElement;
                    return
                        array(
                            'elements' => $this->elements,
                            'html' => $this->getElementHtmlTemplate(
                                    $key + 1,
                                    $emptyElement
                                )->get(),
                        );
                    break;
                default:
                    throw new OptionValueNotValidException(
                        $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_ACTION_UNKNOWN']
                    );
            }
        } else {
            $optionClass = $this->type;
            // create an new instance for this single element, so the option
            // rendering can be done directly over the type of the field and we
            // don't need to implement it for all types in the series itself
            $seriesElement = new $optionClass(
                '',
                array(),
                $this->elements[$data['id']],
                $this->type
            );
            $this->elements[$data['id']] =
                $seriesElement->handleChange($data['value']);
        }
        return array('elements' => $this->elements);
    }

    /**
     * Get array with elements
     *
     * @return Option[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param Option[] $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public function getValue()
    {
        if (
            $this->type ==
                'Cx\Core_Modules\TemplateEditor\Model\Entity\CombinedOption'
        ) {
            // combinedOptions already have the index 'elements' in array
            return $this->elements;
        }
        return array(
            'elements' => $this->elements
        );
    }

    /**
     * Get the sigma template for an element
     *
     * @param   int     $id                   the seriesId of the element
     * @param   array   $specific             the specific values of the element
     * @return  Sigma                         the html template
     * @throws  OptionValueNotValidException  If the data which the option should
     *                                        handle is invalid this exception
     *                                        will be thrown..
     */
    protected function getElementHtmlTemplate ($id, $specific) {
        global $_ARRAYLANG;
        if (
            !is_a(
                $this->type,
                'Cx\Core_Modules\TemplateEditor\Model\Entity\Option',
                true
            )
        ) {
            throw new OptionValueNotValidException(
                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_NO_OPTION']
            );
        }
        $optionClass = $this->type;
        $instance = new $optionClass(
            $this->name.'_seriesId' . $id,
            "",
            $specific,
            $this->type
        );
        return $instance->renderOptionField();

    }
}