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
     */
    public function renderOptionField()
    {
        global $_ARRAYLANG;

        $images = array();
        $entryHtml = "";
        foreach ($this->elements as $id => $elm) {
            $optionReflection = new \ReflectionClass($this->type);
            if ($optionReflection->isSubclassOf('Cx\Core_Modules\TemplateEditor\Model\Entity\Option')
            ) {
                $instance = $optionReflection->newInstance(
                    $this->name.'_seriesId'.$id,
                    "",
                    $elm, // $option['specific'],
                    $this->type
                );
                $entryHtml .= $instance->renderOptionField()->get();
            }
        }

        $mediaBrowser   = new MediaBrowser();
        $mediaBrowserId = $this->name . '_mediabrowser';
        $mediaBrowser->setOptions(
            array(
                'id' => $mediaBrowserId
            )
        );
        $mediaBrowser->setOptions(
            array(
                'views' => 'uploader,filebrowser',
                'startview' => 'filebrowser',
            )
        );
        $mediaBrowser->setCallback('callback_' . $this->name);

        //Get last key
        end($this->elements);
        $key = key($this->elements);
        return parent::renderOptionField(
            array(
                'MEDIABROWSER_BUTTON' =>
                    $mediaBrowser->getXHtml(
                        $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_ADD_ELEMENT']
                    ),
                'MEDIABROWSER_ID'       => $mediaBrowserId,
                'TEMPLATEEDITOR_LASTID' => $key != null ? $key : '0',
                'TEMPLATEEDITOR_SERIE_CONTENT' => $entryHtml,
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
            if (isset($data['value']['action']) && $data['value']['action'] == 'remove') {
                unset($this->elements[intval($data['id'])]);
            } else {
                throw new OptionValueNotValidException(
                    sprintf(
                        $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_VALUE_EMPTY']
                    )
                );
            }
        } else {
            /*$newValue = parse_url($data['value']);
            if (!isset($newValue['host'])) {
                if (!file_exists(
                    $this->cx->getWebsitePath() . $newValue['path']
                )
                ) {
                    if (!file_exists(
                        $this->cx->getCodeBasePath() . $newValue['path']
                    )
                    ) {
                        throw new OptionValueNotValidException(
                            sprintf(
                                $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_IMAGE_FILE_NOT_FOUND'],
                                $newValue['path']
                            )
                        );
                    }
                }
            }*/
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
}