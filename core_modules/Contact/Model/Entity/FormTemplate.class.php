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
 * Class FormTemplate
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_contact
 */

namespace Cx\Core_Modules\Contact\Model\Entity;

/**
 * Class FormTemplate
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_contact
 */
class FormTemplate extends \Cx\Core_Modules\Contact\Controller\ContactLib {
    /**
     * @var Cx\Core\ContentManager\Model\Entity\Page
     */
    protected $page;

    /**
     * @var Cx\Core_Modules\Contact\Model\Entity\Form
     */
    protected $form;

    /**
     * @var Cx\Core\View\Model\Entity\Theme
     */
    protected $theme;

    /**
     * @var Cx\Core\Html\Sigma
     */
    protected $template;

    /**
     * @var Cx\Core\Core\Controller\Cx
     */
    protected $cx;

    /**
     * @var integer
     */
    protected $langId;

    /**
     * @var array
     */
    protected $fieldTemplates = array();

    /**
     * @var boolean
     */
    protected $preview;

    /**
     * @var boolean
     */
    protected $hasFileField = false;

    /**
     * @var string
     */
    protected $uploaderCode = '';

    /**
     * @var string
     */
    protected $blockPrefix = 'contact_form_field_';

    /**
     * Constructor
     *
     * @param Form                                      $form  Form instance
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page  Resolved page instance
     * @param \Cx\Core\View\Model\Entity\Theme          $theme Theme instance
     */
    public function __construct(
        Form $form,
        \Cx\Core\ContentManager\Model\Entity\Page $page,
        \Cx\Core\View\Model\Entity\Theme $theme = null,
        $preview = false
    ) {
        //Initialize the class variables, Contact form and Form template
        $this->form  = $form;
        $this->page  = $page;
        $this->theme = $theme;
        $this->cx    = \Cx\Core\Core\Controller\Cx::instanciate();
        $this->setPreview($preview);
        $this->setLangId(LANG_ID);
        $this->initContactForms($this->form->getId());
        $this->initTemplate();
    }

    /**
     * Initialize the submission form and form field templates
     */
    public function initTemplate()
    {
        $this->template = new \Cx\Core\Html\Sigma(
            $this->cx->getCodeBaseCoreModulePath() . '/Contact/View/Template/Frontend'
        );
        $this->template->setErrorHandling(PEAR_ERROR_DIE);
        $this->template->setTemplate($this->page->getContent());

        if ($this->template->placeholderExists('APPLICATION_DATA')) {
            //Load the Form template from 'theme specific form template' for eg:
            //(/themes/<theme>/core_modules/Contact/Template/Frontend/Form.html)
            //or 'default form template' for eg:
            //(/core_modules/Contact/View/Template/Frontend/Form.html)
            $formTemplate = $this->getTemplateContent('Form');
            \LinkGenerator::parseTemplate($formTemplate);
            $this->template->addBlock(
                'APPLICATION_DATA',
                'application_data',
                $formTemplate
            );
        }

        //Check if the loaded form has form fields otherwise return.
        $formFields = $this->getFormFields($this->form->getId());
        if (!$formFields) {
            return;
        }

        //Load form fields template content
        foreach ($formFields as $arrField) {
            $fieldType = $arrField['type'];
            if ($fieldType == 'special') {
                $fieldType = $arrField['special_type'];
            }
            $fieldTemplateContent = $this->getFormFieldContent(
                $arrField['type'],
                $arrField['special_type']
            );
            $fieldTemplate = new FormFieldTemplate();
            $fieldTemplate->setContent($fieldTemplateContent);
            $this->fieldTemplates[$fieldType] = $fieldTemplate;
        }
    }

    /**
     * Get Form field Content by using template-block contact_form_field_<TYPE> or
     * 'theme specific form field template' or 'default form field template'
     *
     * @param string $fieldType   Form Field type
     * @param string $specialType Form Field special type
     *
     * @return string Content of formfield
     */
    protected function getFormFieldContent($fieldType, $specialType)
    {
        $customBlock = $this->blockPrefix . $fieldType;
        if ($fieldType == 'special') {
            $customBlock = $this->blockPrefix . $specialType;
        }

        //Check if the template block 'contact_form_field_<TYPE>' exists
        if ($this->template->blockExists($customBlock)) {
            return $this->template->getUnparsedBlock($customBlock);
        }

        $fileName = 'Field' . ucfirst($fieldType);
        if ($fieldType == 'multi_file') {
            $fileName = 'FieldMultiFile';
        }

        //Get form field content from theme specific form field template
        // if the form field template exists in theme
        //(/themes/<theme>/core_modules/Contact/Template/Frontend/FieldText.html),
        //otherwise from default form field template(core_modules/Contact/View/Template/Frontend/)
        $fieldTemplateContent = $this->getTemplateContent($fileName);
        if ($fieldType != 'special') {
            return $fieldTemplateContent;
        }

        //The special form field template(FieldSpecial.html) have two blocks:
        //'contact_form_special_field_select' and 'contact_form_special_field_input'
        //The block 'contact_form_special_field_select' content for the following
        //special fields: 'access_title', 'access_gender', 'access_country'.
        //The remaining special fields using the block
        //'contact_form_special_field_input' content.
        $specialBlock = $this->blockPrefix . 'special_input';
        if (
            in_array(
                $specialType,
                array('access_title', 'access_gender', 'access_country')
            )
        ) {
            $specialBlock = $this->blockPrefix . 'special_select';
        }

        $matches = array();
        preg_match(
            '@(<!--\s*BEGIN\s+(' . $specialBlock . ')\s*-->(.*?)<!--\s*END\s+\2\s*-->)@s',
            $fieldTemplateContent,
            $matches
        );
        if (isset($matches[3])) {
            return $matches[3];
        }

        return '';
    }

    /**
     * Get Form/Form Field template content
     *
     * @param string $fileName Name of the file
     *
     * @return string Template content
     */
    protected function getTemplateContent($fileName)
    {
        if (empty($fileName)) {
            return '';
        }

        //Check and return file content if the file exists in the Contact Component.
        $defaultPath = $this->cx->getClassLoader()->getFilePath(
            $this->cx->getCodeBaseCoreModulePath() . '/Contact/View/Template/Frontend/'
            . $fileName . '.html'
        );
        if ($this->hasPreview() || !$this->theme) {
            return file_get_contents($defaultPath);
        }

        //Check and return file content if the file exists in the theme.
        $themePath = $this->cx->getClassLoader()->getFilePath(
            $this->cx->getWebsiteThemesPath() . '/' . $this->theme->getFoldername() .
            '/' . $this->cx->getCoreModuleFolderName() . '/Contact/Template/Frontend/'
            . $fileName . '.html'
        );
        if ($themePath) {
            return file_get_contents($themePath);
        }

        return file_get_contents($defaultPath);
    }

    /**
     * To hide the block formText
     */
    public function hideFormText()
    {
        if ($this->template->blockExists('contact_form_text')) {
            $this->template->hideBlock('contact_form_text');
        }
    }

    /**
     * To show the block formText
     */
    public function showFormText()
    {
        if ($this->template->blockExists('contact_form_text')) {
            $this->template->touchBlock('contact_form_text');
        }
    }

    /**
     * To hide the submission form
     */
    public function hideForm()
    {
        if ($this->template->blockExists('contact_form')) {
            $this->template->hideBlock('contact_form');
        }
    }

    /**
     * Set Captcha content
     *
     * @param boolean $useCaptcha If it is true then captcha will be shown
     *                            otherwise not
     */
    public function setCaptcha($useCaptcha)
    {
        global $_CORELANG;

        if (!$this->template->blockExists('contact_form_captcha')) {
            return;
        }

        if (!$useCaptcha) {
            $this->template->hideBlock('contact_form_captcha');
            return;
        }
        $this->template->setVariable(array(
            'TXT_CONTACT_CAPTCHA'  => $_CORELANG['TXT_CORE_CAPTCHA'],
            'CONTACT_CAPTCHA_CODE' =>
            \Cx\Core_Modules\Captcha\Controller\Captcha::getInstance()->getCode(),
        ));
        $this->template->parse('contact_form_captcha');
    }

    /**
     * Get the submission form Content
     *
     * @param boolean $show If it is true then source code of form will be return
     *                      otherwise form content will be return
     *
     * @return string
     */
    public function getHtml($show = false)
    {
        if ($show) {
            return preg_replace(
                '/\{([A-Z0-9_-]+)\}/',
                '[[\\1]]',
                $this->getSourceCode()
            );
        }
        return $this->template->get();
    }

    /**
     * Parse Submission form and its form field values
     */
    public function parseFormTemplate()
    {
        global $_ARRAYLANG;

        \JS::activate('cx');
        $formId      = $this->form->getId();
        $formFields  = $this->getFormFields($formId);
        $profileData = $this->getProfileData();
        $this->handleUniqueId();

        //Check if the loaded form has form fields and
        //the template block 'contact_form' is exists otherwise return empty.
        if (!$formFields || !$this->template->blockExists('contact_form')) {
            return;
        }

        //Parse Form related values
        $formName = $this->arrForms[$formId]['lang'][$this->langId]['name'];
        $formText = $this->arrForms[$formId]['lang'][$this->langId]['text'];
        $actionUrl = \Cx\Core\Routing\Url::fromModuleAndCmd(
            'Contact',
            $formId,
            $this->langId
        );
        $customStyleId = '';
        if ($this->arrForms[$formId]['useCustomStyle'] > 0) {
            $customStyleId = '_' . $formId;
        }
        $this->template->setGlobalVariable($profileData);
        $this->template->setVariable(array(
            'CONTACT_FORM_NAME'   => contrexx_raw2xhtml($formName),
            'CONTACT_FORM_TEXT'   => $formText,
            'CONTACT_FORM_ACTION' => $actionUrl->toString(),
            'CONTACT_FORM_CUSTOM_STYLE_ID' => $customStyleId
        ));

        //Parse FormField related values
        foreach ($formFields as $fieldId => $arrField) {
            //Set values for special field types
            $this->setSpecialFieldValue($arrField, $fieldId);

            $fieldValue = preg_replace(
                '/\[\[([A-Z0-9_]+)\]\]/',
                '{$1}',
                $arrField['lang'][$this->langId]['value']
            );
            $fieldLabel = '&nbsp;';
            if (!empty($arrField['lang'][$this->langId]['name'])) {
                $fieldLabel = contrexx_raw2xhtml(
                    $arrField['lang'][$this->langId]['name']
                );
            }
            $customBlockName = $this->blockPrefix . $fieldId;
            //Check if the placeholder {<ID>_VALUE} or {<ID>_LABEL} exists,
            //if so, parse its content directly
            if (
                $this->template->placeholderExists($fieldId . '_VALUE') ||
                $this->template->placeholderExists($fieldId . '_LABEL')
            ) {
                $this->parseInputFieldValue(
                    $this->template,
                    $fieldId,
                    $fieldValue,
                    $fieldId . '_VALUE'
                );
                $this->template->setVariable(array(
                    $fieldId . '_LABEL' => contrexx_raw2xhtml($fieldLabel)
                ));
                $content = '';
            } elseif ($this->template->blockExists($customBlockName)) {
                //check if the template-block contact_form_field_<ID> exists
                //if so, parse that block content
                $content = $this->template->getUnparsedBlock($customBlockName);
            } else {
                //Use the content of the associated object of
                //$this->fieldTemplates for parsing
                $fieldType = $arrField['type'];
                if ($fieldType == 'special') {
                    $fieldType = $arrField['special_type'];
                }
                $content = $this->fieldTemplates[$fieldType]->getContent();
            }
            if ($content) {
                $this->template->setVariable(
                    'CONTACT_FORM_FIELD',
                    $this->parseFormField(
                        $content,
                        $fieldId,
                        $arrField,
                        $profileData
                    )
                );
                $this->template->parse($this->blockPrefix . 'list');
            }
        }

        //Use stylesheet 'form.css' if the form is loaded for the preview
        if ($this->preview) {
            $this->template->setVariable(
                'CONTACT_FORM_CSS_HREF',
                $this->cx->getCodeBaseCoreModuleWebPath() .
                '/Contact/View/Style/form.css'
            );
            $this->template->parse('contact_form_css_link');
        } else {
            $this->template->hideBlock('contact_form_css_link');
        }

        //Parse language text and JS source code for form validation, uploader code.
        $this->template->setVariable(array(
            'CONTACT_FORM_JAVASCRIPT' => $this->_getJsSourceCode($formId, $formFields),
            'CONTACT_FORM_UPLOADER'   => $this->uploaderCode,
            'TXT_NEW_ENTRY_ERORR'     => $_ARRAYLANG['TXT_NEW_ENTRY_ERORR'],
            'TXT_CONTACT_SUBMIT'      => $_ARRAYLANG['TXT_CONTACT_SUBMIT'],
            'TXT_CONTACT_RESET'       => $_ARRAYLANG['TXT_CONTACT_RESET']
        ));
    }

    /**
     * Generates the HTML Source code of the Submission form designed in backend
     *
     * @return string Source code of the submission form
     */
    protected function getSourceCode()
    {
        $formContent = $this->template->getUnparsedBlock('contact_form_section');
        $formFieldContent = array();
        $formFieldContent[] =
            "<!-- BEGIN " . $this->blockPrefix . "list -->
            [[CONTACT_FORM_FIELD]]
            <!-- END " . $this->blockPrefix . "list -->
            ";
        foreach ($this->fieldTemplates as $type => $formField) {
            $content = $formField->getContent();
            foreach (
                array(
                    $this->blockPrefix . 'required',
                    $this->blockPrefix . 'options'
                ) as $block
            ) {
                $content = str_replace($block, $block . '_' . $type, $content);
            }
            $formFieldContent[] =
                "<!-- BEGIN " . $this->blockPrefix . $type . " -->\n" . $content .
                "\n<!-- END " . $this->blockPrefix . $type . " -->\n";
        }

        return preg_replace(
            '@(<!--\s*BEGIN\s+(' . $this->blockPrefix . 'list)\s*-->.*?<!--\s*END\s+\2\s*-->)@s',
            implode("\n", $formFieldContent),
            $formContent
        );
    }

    /**
     * Set values for special field types if the user is authenticated and
     * there is no value set through GET and POST
     *
     * @param array   $arrField Array of field values
     * @param integer $fieldId  Field ID
     */
    protected function setSpecialFieldValue(&$arrField, $fieldId)
    {
        if (
            !\FWUser::getFWUserObject()->objUser->login() ||
            $arrField['type'] != 'special' ||
            !empty($_GET[$fieldId]) ||
            !empty($_POST['contactFormField_' . $fieldId])
        ) {
            return;
        }

        if ($arrField['special_type'] == 'access_email') {
            $arrField['lang'][$this->langId]['value'] = '[[ACCESS_USER_EMAIL]]';
        } else {
            $value = str_replace('access_', '', $arrField['special_type']);
            $arrField['lang'][$this->langId]['value'] =
                '[[ACCESS_PROFILE_ATTRIBUTE_' . strtoupper($value) . ']]';
        }
    }

    /**
     * Parse Form Field
     *
     * @param string  $fieldContent Content of the FormField
     * @param integer $fieldId      FormField ID
     * @param array   $arrField     Array of FormField values
     * @param array   $profileData  Array of User Profile data
     *
     * @return string Parsed content of Form field
     */
    protected function parseFormField(
        $fieldContent,
        $fieldId,
        $arrField,
        $profileData
    ) {
        global $_ARRAYLANG;
        if (empty($fieldContent)) {
            return '';
        }

        $template = new \Cx\Core\Html\Sigma('.');
        $template->setErrorHandling(PEAR_ERROR_DIE);
        $template->setTemplate($fieldContent);
        $template->setGlobalVariable($profileData);

        $regex = '/\[\[([A-Z0-9_]+)\]\]/';
        $fieldValue = preg_replace(
            $regex,
            '{$1}',
            $arrField['lang'][$this->langId]['value']
        );
        $fieldLabel = '&nbsp;';
        if (!empty($arrField['lang'][$this->langId]['name'])) {
            $fieldLabel = contrexx_raw2xhtml($arrField['lang'][$this->langId]['name']);
        }
        $fieldType = $arrField['type'];
        if ($fieldType == 'special') {
            $fieldType = $arrField['special_type'];
        }

        // Check if the template have block like any one of the following formats:
        //'contact_form_field_required' or 'contact_form_field_required_<Type>'
        // or 'contact_form_field_required_<ID> for required'
        $requiedBlock = $this->blockPrefix . 'required';
        if ($template->blockExists($requiedBlock . '_' . $fieldId)) {
            $requiedBlockName = $requiedBlock . '_' . $fieldId;
        } elseif ($template->blockExists($requiedBlock . '_' . $fieldType)) {
            $requiedBlockName = $requiedBlock . '_' . $fieldType;
        } else {
            $requiedBlockName = $requiedBlock;
        }

        if ($arrField['is_required']) {
            $template->touchBlock($requiedBlockName);
        } else {
            $template->hideBlock($requiedBlockName);
        }

        // Parse Form field Id and Label values.
        $template->setVariable(array(
            'CONTACT_FORM_FIELD_ID'    => $fieldId,
            'CONTACT_FORM_FIELD_LABEL' => $fieldLabel
        ));
        // Parse form field value based on its type.
        switch ($fieldType) {
            case 'checkbox':
                $checkboxSelected = '';
                if (
                    $fieldValue == 1 ||
                    !empty($_POST['contactFormField_' . $fieldId])
                ) {
                    $checkboxSelected = 'checked="checked"';
                }
                $template->setVariable(array(
                    'CONTACT_FORM_FIELD_CHECKBOX_SELECTED' => $checkboxSelected
                ));
                break;
            case 'checkboxGroup':
            case 'radio':
                $this->parseFormFieldValue($template, $fieldId, $fieldType, $fieldValue);
                break;
            case 'access_title':
            case 'access_gender':
                // Collect user attribute options
                $arrOptions   = array();
                $objUser      = \FWUser::getFWUserObject()->objUser;
                $accessAttrId = str_replace('access_', '', $fieldType);
                $objAttribute = $objUser->objAttribute->getById($accessAttrId);

                // get options
                $arrAttribute = $objAttribute->getChildren();
                foreach ($arrAttribute as $attributeId) {
                    //In case the selection of the field is mandatory,
                    // we shall skip the unknown option of the user profile attribute
                    if (
                        $arrField['is_required'] &&
                        strpos($attributeId, '_undefined')
                    ) {
                        continue;
                    }
                    $objAttribute = $objUser->objAttribute->getById($attributeId);
                    $arrOptions[] = $objAttribute->getName($this->langId);
                }
                //Options will be used for select input generation
                $fieldValue = implode(',', $arrOptions);
            case 'select':
                $options = explode(',', $fieldValue);
                if ($arrField['is_required']) {
                    $options = array_merge(
                        array($_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT']),
                        $options
                    );
                }
                $this->parseFormFieldValue($template, $fieldId, $fieldType, $options);
                break;
            case 'recipient':
                $recipients = $this->getRecipients($this->form->getId());
                $options    = array();
                foreach ($recipients as $index => $recipient) {
                    $options[$index] = preg_replace($regex, '{$1}', $recipient['lang'][$this->langId]);
                }
                $this->parseFormFieldValue($template, $fieldId, $fieldType, $options);
                break;
            case 'access_country':
            case 'country':
                $matches = array();
                if (preg_match('/\{([A-Z_]+)\}/', $fieldValue, $matches)) {
                    $fieldValue = $template->_globalVariables[$matches[1]];
                }
                $arrCountry = \Cx\Core\Country\Controller\Country::getNameArray(
                    true,
                    $this->langId
                );
                $defaultOption = $_ARRAYLANG['TXT_CONTACT_NOT_SPECIFIED'];
                if ($arrField['is_required']) {
                    $defaultOption = $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'];
                }
                $options = array_merge(array($defaultOption), $arrCountry);
                $this->parseFormFieldValue(
                    $template,
                    $fieldId,
                    $fieldType,
                    $options,
                    $fieldValue
                );
                break;
            case 'file':
                $this->hasFileField  = true;
                $this->uploaderCode .= $this->initUploader($template, $fieldId);
                break;
            case 'multi_file':
                $this->hasFileField  = true;
                $this->uploaderCode .= $this->initUploader($template, $fieldId, false);
                break;
            default:
                $this->parseInputFieldValue(
                    $template,
                    $fieldId,
                    $fieldValue,
                    'CONTACT_FORM_FIELD_VALUE'
                );
                break;
        }

        return $template->get();
    }

    /**
     * Parse FormField's value
     *
     * @param \Cx\Core\Html\Sigma $template   Template object
     * @param integer             $fieldId    Field ID
     * @param string              $fieldType  Field type
     * @param array               $options    Field option values
     * @param string              $fieldValue Field value
     */
    protected function parseFormFieldValue(
        \Cx\Core\Html\Sigma $template,
        $fieldId,
        $fieldType,
        $options,
        $fieldValue = ''
    ) {
        if (empty($options)) {
            return;
        }

        $isSpecialType = in_array($fieldType, array('access_title', 'access_gender'));
        $accessAttrId  = '';
        if ($isSpecialType) {
            $accessAttrId  =
                'ACCESS_PROFILE_ATTRIBUTE_' . str_replace('access_', '', $fieldType);
        }

        $selectedText = 'selected = "selected"';
        if (in_array($fieldType, array('radio', 'checkboxGroup'))) {
            $selectedText = 'checked = "checked"';
        }

        // Check if the template have block like any one of the following formats:
        //'contact_form_field_options' or 'contact_form_field_options_<Type>'
        // or 'contact_form_field_options_<ID> for parsing option values'
        $blockName = $this->blockPrefix . 'options';
        if ($template->blockExists($blockName . '_' . $fieldId)) {
            $optionBlockName = $blockName . '_' . $fieldId;
        } elseif ($template->blockExists($blockName . '_' . $fieldType)) {
            $optionBlockName = $blockName . '_' . $fieldType;
        } else {
            $optionBlockName = $blockName;
        }

        if (!is_array($options)) {
            $options = explode(',', $options);
        }
        foreach ($options as $index => $option) {
            // Parse form field value
            if (preg_match('/\{([A-Z_]+)\}/', $option)) {
                // Set form field value through User profile attribute
                $valuePlaceholderBlock =
                    'contact_value_placeholder_block_' . $fieldId . '_' . $index;
                $template->addBlock(
                    'CONTACT_FORM_FIELD_VALUE',
                    $valuePlaceholderBlock,
                    contrexx_raw2xhtml($option)
                );
                $template->touchBlock($valuePlaceholderBlock);
            } else {
                $template->setVariable(
                    'CONTACT_FORM_FIELD_VALUE',
                    contrexx_raw2xhtml($option)
                );
            }

            $template->setVariable(array(
                'CONTACT_FORM_FIELD_KEY' => $index,
                'CONTACT_FORM_BLOCK_FIELD_ID' => $fieldId
            ));
            // Set selected or checked attribute to the form field based on
            // post, get and default value of that form field
            $valueFromPost = '';
            if (isset($_POST['contactFormField_' . $fieldId])) {
                $valueFromPost = contrexx_input2raw(
                    $_POST['contactFormField_' . $fieldId]
                );
            }
            $valueFromGet = '';
            if (isset($_GET[$fieldId])) {
                $valueFromGet = contrexx_input2raw($_GET[$fieldId]);
            }

            $isOptionInPost =
                !empty($valueFromPost) &&
                (
                    in_array($option, $valueFromPost) ||
                    $option == $valueFromPost ||
                    strcasecmp($option, $valueFromPost) == 0
                );
            $isOptionInGet =
                !empty($valueFromGet) &&
                (
                    $option == $valueFromGet ||
                    strcasecmp($option, $valueFromGet) == 0
                );
            $isOptionInAccessAttr =
                $isSpecialType &&
                isset($template->_globalVariables[strtoupper($accessAttrId)]) &&
                $option == $template->_globalVariables[strtoupper($accessAttrId)];
            if (
                $isOptionInPost ||
                $isOptionInGet ||
                $isOptionInAccessAttr ||
                $option == $fieldValue
            ) {
                $template->setVariable(
                    'CONTACT_FORM_FIELD_SELECTED',
                    $selectedText
                );
            }
            $template->parse($optionBlockName);
        }
    }

    /**
     * Parse Input Field's value
     *
     * @param \Cx\Core\Html\Sigma $template         Template object
     * @param integer             $fieldId          Form Field ID
     * @param string              $fieldValue       Form Field value
     * @param string              $valuePlaceholder Value placeholder
     */
    protected function parseInputFieldValue(
        \Cx\Core\Html\Sigma $template,
        $fieldId,
        $fieldValue,
        $valuePlaceholder
    ) {
        //Set default field value through User profile attribute
        if (preg_match('/\{([A-Z_]+)\}/', $fieldValue)) {
            $valuePlaceholderBlock =
                'contact_value_placeholder_block_' . $fieldId;
            $template->addBlock(
                $valuePlaceholder,
                $valuePlaceholderBlock,
                contrexx_raw2xhtml($fieldValue)
            );
            $template->touchBlock($valuePlaceholderBlock);
            return;
        }

        if (!empty($_POST['contactFormField_' . $fieldId])) {
            $fieldValue = $_POST['contactFormField_' . $fieldId];
        } elseif (!empty($_GET[$fieldId])) {
            $fieldValue = $_GET[$fieldId];
        }
        $template->setVariable(
            $valuePlaceholder,
            contrexx_raw2xhtml($fieldValue)
        );
    }

    /**
     * Get Profile data
     *
     * @return array Array of Profile data
     */
    protected function getProfileData()
    {
        if (!\FWUser::getFWUserObject()->objUser->login()) {
            return array();
        }

        $objUser     = \FWUser::getFWUserObject()->objUser;
        $profileData = array(
            'ACCESS_USER_EMAIL' => contrexx_raw2xhtml($objUser->getEmail())
        );

        $objUser->objAttribute->reset();
        while (!$objUser->objAttribute->EOF) {
            $value = '';
            $objAttribute = $objUser->objAttribute->getById(
                $objUser->objAttribute->getId()
            );

            switch ($objAttribute->getType()) {
                case 'menu':
                    if ($objAttribute->isCoreAttribute()) {
                        foreach ($objAttribute->getChildren() as $childAttributeId) {
                            $objChildAtrribute = $objAttribute->getById($childAttributeId);
                            if (!$objChildAtrribute->getId()) {
                                continue;
                            }
                            $profileAttribute = $objUser->getProfileAttribute(
                                $objAttribute->getId()
                            );
                            if ($objChildAtrribute->getMenuOptionValue() == $profileAttribute) {
                                $value = $objChildAtrribute->getName();
                                break;
                            }
                        }
                    } else {
                        if (!$objUser->getProfileAttribute($objAttribute->getId())) {
                            break;
                        }
                        $objSelectedAttribute = $objAttribute->getById(
                            $objUser->getProfileAttribute($objAttribute->getId())
                        );
                        if (!$objSelectedAttribute->getId()) {
                            break;
                        }
                        $value = $objSelectedAttribute->getName();
                    }
                    break;

                case 'date':
                    $value = $objUser->getProfileAttribute($objAttribute->getId());
                    if ($value !== false && $value !== '') {
                        $value = date(ASCMS_DATE_FORMAT_DATE, intval($value));
                    } else {
                        $value = '';
                    }
                    break;

                default:
                    $value = $objUser->getProfileAttribute($objAttribute->getId());
                    break;
            }

            $attrPlaceholder =
                'ACCESS_PROFILE_ATTRIBUTE_' . strtoupper($objAttribute->getId());
            $profileData[$attrPlaceholder] = $value;
            $objUser->objAttribute->next();
        }

        return $profileData;
    }

    /**
     * generates an unique id for each form and user.
     */
    protected function handleUniqueId()
    {
        $this->cx->getComponent('Session')->getSession();

        $id = 0;
        if (isset($_REQUEST['unique_id'])) {
            //an id is specified - we're handling a page reload
            $id = intval($_REQUEST['unique_id']);
        } else { //generate a new id
            if (!isset($_SESSION['contact_last_id'])) {
                $_SESSION['contact_last_id'] = 1;
            } else {
                $_SESSION['contact_last_id'] += 1;
            }

            $id = $_SESSION['contact_last_id'];
        }
        $this->template->setVariable('CONTACT_FORM_UNIQUE_ID', $id);
    }

    /**
     * Initialize the Uploader
     *
     * @param \Cx\Core\Html\Sigma $template                  Template object
     * @param integer             $fieldId                   Field ID
     * @param boolean             $restrictUpload2SingleFile If true Uploader accept only SingleFile
     *                                                       otherwise Uploader handle Multiple Files
     * @return string
     */
    protected function initUploader(
        \Cx\Core\Html\Sigma $template,
        $fieldId,
        $restrictUpload2SingleFile = true
    ) {
        try {
            $this->cx->getComponent('Session')->getSession();

            $uploader = new \Cx\Core_Modules\Uploader\Model\Entity\Uploader();
            //set instance name so we are able to catch the instance with js
            $uploader->setCallback('contactFormUploader_' . $fieldId);

            //specifies the function to call when upload is finished.
            // must be a static function
            $uploader->setFinishedCallback(array(
                $this->cx->getCodeBaseCoreModulePath() .
                '/Contact/Controller/Contact.class.php',
                '\Cx\Core_Modules\Contact\Controller\Contact',
                'uploadFinished'
            ));

            if ($restrictUpload2SingleFile) {
                $uploader->setData(array(
                    'singleFile'   => $restrictUpload2SingleFile
                ));
                $uploader->setUploadLimit(1);
            }
            $uploaderId = $uploader->getId();
            $uploader->setOptions(array(
                'id'     => 'contactUploader_' . $uploaderId,
                'style'  => 'display: none'
            ));

            //initialize the widget displaying the folder contents
            $folderWidget = new \Cx\Core_Modules\MediaBrowser\Model\Entity\FolderWidget(
                $_SESSION->getTempPath() . '/'. $uploaderId
            );
            $template->setVariable(array(
                'CONTACT_UPLOADER_FOLDER_WIDGET' => $folderWidget->getXhtml(),
                'CONTACT_FORM_FIELD_VALUE'       => $uploaderId
            ));

            $folderWidgetId = $folderWidget->getId();
            return $uploader->getXHtml() . <<<CODE
            <script type="text/javascript">
            cx.ready(function() {
                    jQuery('#contactFormFieldId_$fieldId').bind('click', function() {
                        jQuery('#contactUploader_$uploaderId').trigger('click');
                        return false;
                    }).removeAttr('disabled');
            });

            //uploader javascript callback function
            function contactFormUploader_$fieldId(callback) {
                    angular.element('#mediaBrowserfolderWidget_$folderWidgetId').scope().refreshBrowser();
            }
            </script>
CODE;
        } catch (\Exception $e) {
            return '<!-- failed initializing uploader, exception '. get_class($e)
                . ' with message "' . $e->getMessage() . '" -->';
        }
    }

    /**
     * Get Template object
     *
     * @return \Cx\Core\Html\Sigma
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * hasFileField
     *
     * @return boolean
     */
    public function hasFileField()
    {
        return $this->hasFileField;
    }

    /**
     * Set LangId
     *
     * @param integer $langId Language ID
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    /**
     * Get LangId
     *
     * @return integer
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * Set preview status
     *
     * @param boolean $preview
     */
    public function setPreview($preview)
    {
        if ($preview) {
            $this->setLangId(FRONTEND_LANG_ID);
        }
        $this->preview = $preview;
    }

    /**
     * Get Preview status
     *
     * @return boolean
     */
    public function hasPreview()
    {
        return $this->preview;
    }
}
