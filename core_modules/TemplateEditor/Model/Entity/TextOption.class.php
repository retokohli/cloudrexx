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
 * Class TextOption
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @author      Adrian Berger <adrian.berger@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class TextOption extends Option
{
    /**
     * The text values of the option.
     *
     * @var array
     */
    protected $values = array();

    /**
     * Regex which the string has to match
     *
     * @var String
     */
    protected $regex = null;

    /**
     * The text values of the option.
     *
     * @var String
     */
    protected $defaultValue = '';

    /**
     * @var integer
     */
    protected $frontendLangId;

    /**
     * Error message which is shown if the regex doesn't match.
     *
     * @var string
     */
    protected $regexError = "";

    /**
     * @var bool
     */
    protected $html = false;

    /**
     * @param String $name         Name of the option
     * @param array  $translations Array with translations for option.
     * @param array  $data         the specific data for this option
     * @param String $type         the type of the option
     * @param Group  $group        the group of the option
     * @param bool   $series       handle the values as series if true
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
        $this->values     = isset($data['values']) ? $data['values'] : '';
        $this->regex      = isset($data['regex']) ? $data['regex'] : null;
        $this->html       = isset($data['html']) ? $data['html'] : false;
        $this->regexError = isset($data['regexError']) ? $data['regexError']
            : '';

        $objFWUser = \FWUser::getFWUserObject();
        $this->frontendLangId = $objFWUser->objUser->getFrontendLanguage();
        $this->defaultValue   =  isset($this->values[$this->frontendLangId])
                        ? $this->values[$this->frontendLangId]
                        : current($this->values);
    }

    /**
     * Get the activated langugage
     */
    public function getMultiLanguageOptions()
    {
        $languages = array();
        foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
            $languageValue =  isset($this->values[$lang['id']])
                        ? $this->values[$lang['id']]
                        : $this->defaultValue;
            $languages[] = array(
                'TEXTFIELDS_LANGUAGE_ID'    => $lang['id'],
                'TEXTFIELDS_LANGUAGE_NAME'  => $lang['name'],
                'TEXTFIELDS_LANGUAGE_VALUE' => $this->parseValue($languageValue),
            );
        }
        return array('language' => $languages);
    }

    /**
     * Render the option field in the backend.
     *
     * @return \Cx\Core\Html\Sigma    the template
     */
    public function renderOptionField()
    {
        global $_ARRAYLANG;
        return parent::renderOptionField(
            array(
                'TEMPLATEEDITOR_OPTION_VALUE'  => $this->defaultValue,
                'TEMPLATEEDITOR_FRONTEND_ID'   => $this->frontendLangId,
                'TXT_TEMPLATEEDITOR_MINIMIZED' => $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_MINIMIZED'],
                'TXT_TEMPLATEEDITOR_EXTENDED'  => $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_EXTENDED']
            ),
            array(),
            $this->getMultiLanguageOptions()
        );
    }

    /**
     * Render the option in the frontend.
     *
     * @param \Cx\Core\Html\Sigma $template
     */
    public function renderTheme($template)
    {
        $template->setVariable(
            'TEMPLATE_EDITOR_' . strtoupper($this->name),
            $this->parseValue($this->defaultValue)
        );
    }

    /**
     * Parse the value string
     *
     * @param string $value String value
     *
     * @return string
     */
    public function parseValue($value) {
        return $this->html ? $value : contrexx_raw2xhtml($value);
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
        global $_ARRAYLANG, $_LANGID;
        foreach ($data as $key => $value) {
            if ($this->regex && !preg_match($this->regex, $value)) {
                if (!empty($this->regexError[$_LANGID])) {
                    return array(
                        'status'  => 'error',
                        'message' => array(
                            'errorContent' => $this->regexError[$_LANGID],
                            'inputValue'   => $value,
                            'id'           => $key,
                        )
                    );
                } elseif (!empty($this->regexError[2])) {
                    return array(
                        'status'  => 'error',
                        'message' => array(
                            'errorContent' => $this->regexError[2],
                            'inputValue'   => $value,
                            'id'           => $key,
                        )
                    );
                }
                return array(
                    'status'  => 'error',
                    'message' => array(
                        'errorContent' => $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_TEXT_WRONG_FORMAT'],
                        'inputValue' => $value,
                        'id' => $key,
                    )
                );
            }
        }
        $this->values = $data;
        return array('status' => 'success', 'values' => $this->values, 'message' => array());
    }

    /**
     * Get the data in a serializable format.
     *
     * @return array
     */
    public function yamlSerialize()
    {
        $option             = parent::yamlSerialize();
        $option['specific'] = array(
            'regex' => $this->regex,
            'regexError' => $this->regexError,
            'html' => $this->html
        );
        return $option;
    }

    /**
     * Get array with values
     *
     * @return Option[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the array values
     *
     * @param Option[] $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * Get the regex
     *
     * @return String
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Set the regex
     *
     * @param String $regex
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    /**
     * Get the regexError
     *
     * @return String
     */
    public function getRegexError()
    {
        return $this->regexError;
    }

    /**
     * Set the regex
     *
     * @param String $regexError
     */
    public function setRegexError($regexError)
    {
        $this->regexError = $regexError;
    }

    /**
     * Return if is html
     *
     * @return boolean
     */
    public function isHtml()
    {
        return $this->html;
    }

    /**
     * Set if is html
     *
     * @param boolean $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public function getValue()
    {
        return array('values' => $this->values);
    }
}
