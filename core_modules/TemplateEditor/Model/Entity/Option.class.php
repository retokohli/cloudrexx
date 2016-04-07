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

use Cx\Core\Html\Sigma;
use Cx\Core_Modules\TemplateEditor\Model\YamlSerializable;

/**
 * Class OptionValueNotValidException
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @package contrexx
 * @subpackage  core_module_templateeditor
 */
class OptionValueNotValidException extends \Exception
{
}

/**
 * Class Option
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
abstract class Option extends \Cx\Model\Base\EntityBase
    implements YamlSerializable
{

    /**
     * The identifying name of the option.
     * @var void
     */
    protected $name;

    /**
     * Array with translations for all available languages.
     * The key of the array is the language id.
     *
     * @var array
     */
    protected $translations;

    /**
     * The translated name of the option. If the active language isn't available
     * english is used as a fallback. If this also isn't available the name of
     * the option is used.
     *
     * @var string
     */
    protected $humanName;

    /**
     * Defines if this type should be handled as series or a single field.
     * Standard is false and will be rendered as single field
     *
     * @var boolean
     */
    protected $series;

    /**
     * The type of the option. This must always be a subclass from options.
     * Should contain the whole namespace to the class.
     *
     * @var string
     */
    protected $type;

    /**
     * @param String $name Name of the option
     * @param array  $translations Array with translations for option.
     * @param array  $data
     * @param String $type         the type of the option
     * @param bool   $series       handle the elements as series if true
     */
    public function __construct(
        $name,
        $translations,
        $data,
        $type,
        $series = false
    ) {
        global $_LANGID;
        $this->name         = $name;
        $this->humanName    = isset($translations[$_LANGID])
            ? $translations[$_LANGID]
            : (isset($translations[2]) ? $translations[2] : $name);
        $this->translations = $translations;
        $this->type = $type;
        $this->series = $series;
    }

    /**
     * Render the option field in the backend.
     *
     * @param array $optionProperties  Values to parse into subTemplate
     * @param array $globalVariables   Variables which should be global
     * @param array $subTemplateBlocks subBlocks to parse. Example:
     *    array() {
     *      ["subBlockName1"]=> array() {
     *          [0]=> array() {
     *              ["PLACEHOLDER_1"] => "example"
     *              ["PLACEHOLDER_2"] => "example 2"
     *          }
     *          [1] => array(...)
     *      }
     *      ["subBlockName2"] => array(...)
     *   }
     * @return Sigma
     */
    public function renderOptionField(
        $optionProperties = array(),
        $globalVariables = array(),
        $subTemplateBlocks = array()
    ) {
        $subTemplate = new Sigma();
        // load subTemplate file for the given option.
        // pattern for html file is: {optionName}Option.html
        $optionName = end( // last value of the array is the class name
            explode('\\', get_class($this)) // get array for class namespace
        );
        $subTemplate->loadTemplateFile(
            $this->getDirectory() . '/View/Template/Backend/'
            .  $optionName . '.html'
        );

        // get all placeholders to replace in subTemplate into one array
        $subTemplateVariables = array_merge(
            array(
            'TEMPLATEEDITOR_OPTION_NAME'        => $this->name,
            'TEMPLATEEDITOR_OPTION_HUMAN_NAME'  => $this->humanName,
            ),
            $optionProperties
        );

        // render subBlocks of subTemplate
        foreach ($subTemplateBlocks as $blockName => $block) {
            foreach ($block as $variables) {
                $subTemplate->setVariable($variables);
                $subTemplate->parse($blockName);
            }
        }

        // render subTemplate
        $subTemplate->setVariable($subTemplateVariables);
        if (!empty($globalVariables)) {
            $subTemplate->setGlobalVariable($globalVariables);
        }
        return $subTemplate;
    }

    /**
     * Render the option in the frontend.
     *
     * @param Sigma $template The frontend template.
     */
    public abstract function renderTheme($template);

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
    public abstract function handleChange($data);

    /**
     * Get the name of the option.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name of the option.
     *
     * @param void $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get the type of the option.
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set the type of the option.
     *
     * @param void $type
     */
    public function setType($type) {
        $this->name = $type;
    }

    /**
     * Return true if element is in a series
     *
     * @return boolean
     */
    public function isSeries() {
        return $this->series;
    }

    /**
     * Set the series of the option.
     *
     * @param void $series
     */
    public function setSeries($series) {
        $this->name = $series;
    }

    /**
     * Get the translated name of the option
     *
     * @return string
     */
    public function getHumanName() {
        return $this->humanName;
    }

    /**
     * Set the human name.
     *
     * @param string $humanName
     */
    public function setHumanName($humanName) {
        $this->humanName = $humanName;
    }

    /**
     * Get the data in a serializable format.
     *
     * @return array
     */
    public function yamlSerialize() {
        return array(
            'name' => $this->name,
            'specific' => array(),
            'type' => get_called_class(),
            'translation' => $this->translations,
            'series' => $this->series
        );
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public abstract function getValue();

}