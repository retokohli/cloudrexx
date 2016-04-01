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
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class SeriesOption extends Option
{

    /**
     * Array with values for serie
     *
     * @var array
     */
    protected $elements;

    /**
     * @param String $name Name of the option
     * @param array  $translations Array with translations for option.
     * @param array  $data
     * @param String $type          the type of the option
     * @param bool   $series        handel the elements as series if true
     */
    public function __construct(
        $name,
        $translations,
        $data,
        $type,
        $series = false
    ) {
        parent::__construct($name, $translations, $data, $type, $series);
        foreach ($data['elements'] as $key => $elm) {
            if (!empty($elm)) {
                $this->elements[$key] = $elm;
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

        $images = array();
        $entryHtml = "";
        // load the rendered template form the option foreach element in the
        // series, so there is only one template needed per option and we do not
        // need to write a {Option}SeriesOption.html foreach option
        foreach ($this->elements as $id => $elm) {
            $entryHtml .= $this->getElementHtmlTemplate($id, $elm);
        }

        return parent::renderOptionField(
            array(
                'TEMPLATEEDITOR_SERIE_CONTENT' => $entryHtml,
                'TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_ELEMENT' =>
                    $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_ELEMENT'],
            ),
            $_ARRAYLANG,
            $images
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
        if ($template->blockExists($blockName)) {
            foreach ($this->elements as $id => $elm) {
                foreach($elm as $val){
                    $template->setVariable(
                        strtoupper('TEMPLATE_EDITOR_' . $this->name),
                        contrexx_raw2xhtml($val)
                    );
                    $template->parse($blockName);
                }
            }
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
        if (empty($data['id']) && $data['id'] != 0) {
            throw new OptionValueNotValidException("Needs a id to work");
        }
        if ($data['value']['elm'] === '') {
            if (isset($data['value']['action'])) {
                switch ($data['value']['action']) {
                    case 'remove':
                        unset($this->elements[intval($data['id'])]);
                        break;
                    case 'add':
                        end($this->elements);
                        $key = key($this->elements);
                        $this->elements[] =
                            array_fill_keys(
                                array_keys($this->elements[$key]),
                                ""
                            );
                        return
                            array(
                                'elements' => $this->elements,
                                'html' => $this->getElementHtmlTemplate(
                                        $key + 1,
                                        array()
                                    ),
                            );
                        break;
                    default:
                        throw new OptionValueNotValidException(
                            sprintf(
                                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_ACTION_UNKNOWN']
                            )
                        );
                }
            } else {
                throw new OptionValueNotValidException(
                    sprintf(
                        $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_EMPTY']
                    )
                );
            }
        } else {
            // create new instance for this single element, so the option rendering
            // can be done directly over the type of the field and we do not need to
            // implement rendering for all types in the series itself
            $optionReflection = new \ReflectionClass($this->type);
            if ($optionReflection->isSubclassOf('Cx\Core_Modules\TemplateEditor\Model\Entity\Option')
            ) {
                $seriesElement
                    = $optionReflection->newInstance(
                    '',
                    array(),
                    array(),
                    $this->type
                );
            }
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
        return array(
            'elements' => $this->elements
        );
    }

    /**
     * Get the rendered html template for an element
     *
     * @param   int     $id         the seriesId of the element
     * @param   array   $specific   the specific values of the element
     * @return  String              the rendered html template
     */
    protected function getElementHtmlTemplate ($id, $specific) {
        $optionReflection = new \ReflectionClass($this->type);
        if ($optionReflection->isSubclassOf('Cx\Core_Modules\TemplateEditor\Model\Entity\Option')
        ) {
            $instance = $optionReflection->newInstance(
                $this->name.'_seriesId'.$id,
                "",
                $specific,
                $this->type
            );
            return $instance->renderOptionField()->get();
        }
    }
}