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
    protected $preview = false;

    /**
     * @var boolean
     */
    protected $hasFileField = false;

    /**
     * @var string
     */
    protected $uploaderCode = '';

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
        \Cx\Core\View\Model\Entity\Theme $theme
    ) {
        global $_LANGID;

        $this->form  = $form;
        $this->page  = $page;
        $this->theme = $theme;
        $this->cx    = \Cx\Core\Core\Controller\Cx::instanciate();
        $this->setLangId($_LANGID);
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
            //Load the Form template from 'theme specific form template' or 'default form template'
            $formTemplate = $this->getTemplateContent('Form');
            \LinkGenerator::parseTemplate($formTemplate);
            $this->template->addBlock(
                'APPLICATION_DATA',
                'application_data',
                $formTemplate
            );
        }

        $formFields = $this->getFormFields($this->form->getId());
        if (!$formFields) {
            return;
        }

        //Load Form Field template by using template-block contact_form_field_<TYPE> or
        //theme specific form field template' or 'default form field template'
        foreach ($formFields as $arrField) {
            $fieldTemplate = new FormFieldTemplate();
            $fieldType     = $arrField['type'];
            if ($this->template->blockExists('contact_form_field_' . $arrField['type'])) {
                $fieldTemplateContent = $this->template->getUnparsedBlock(
                    'contact_form_field_' . $arrField['type']
                );
            } else {
                $fileName = 'Field' . ucfirst($fieldType);
                if ($fieldType == 'multi_file') {
                    $fileName = 'FieldMultiFile';
                }
                $fieldTemplateContent = $this->getTemplateContent($fileName);
                if ($fieldType == 'special') {
                    $fieldType = $arrField['special_type'];
                    if (
                        in_array(
                            $arrField['special_type'],
                            array('access_title', 'access_gender', 'access_country')
                        )
                    ) {
                        $specialBlock = 'contact_form_special_field_select';
                    } else {
                        $specialBlock = 'contact_form_special_field_input';
                    }
                    $matches = array();
                    preg_match(
                        '@(<!--\s*BEGIN\s+(' . $specialBlock . ')\s*-->.*?<!--\s*END\s+\2\s*-->)@s',
                        $fieldTemplateContent,
                        $matches
                    );
                    if (isset($matches[0])) {
                        $fieldTemplateContent = $matches[0];
                    }
                }
            }
            $fieldTemplate->setContent($fieldTemplateContent);
            $this->fieldTemplates[$fieldType] = $fieldTemplate;
        }
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

        $themePath = $this->cx->getClassLoader()->getFilePath(
            $this->cx->getWebsiteThemesPath() . '/' . $this->theme->getFoldername() .
            '/' . $this->cx->getCoreModuleFolderName() . '/Contact/Template/Frontend/'
            . $fileName . '.html'
        );
        if ($themePath) {
            return file_get_contents($themePath);
        }

        $defaultPath = $this->cx->getClassLoader()->getFilePath(
            $this->cx->getCodeBaseCoreModulePath() . '/Contact/View/Template/Frontend/'
            . $fileName . '.html'
        );
        return file_get_contents($defaultPath);
    }

    /**
     * To hide the block formText
     */
    public function hideFormText()
    {
        if ($this->template->blockExists('formText')) {
            $this->template->hideBlock('formText');
        }
    }

    /**
     * To hide the block formText
     */
    public function showFormText()
    {
        if ($this->template->blockExists('formText')) {
            $this->template->touchBlock('formText');
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
     * Set Captcha
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
     * @param boolean $show
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

        if (!$formFields || !$this->template->blockExists('contact_form')) {
            return;
        }

        //Parse Form related values
        $formName = $this->arrForms[$formId]['lang'][$this->langId]['name'];
        $formText = $this->arrForms[$formId]['lang'][$this->langId]['text'];
        $url = \Cx\Core\Routing\Url::fromModuleAndCmd(
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
            'CONTACT_FORM_ACTION' => $url->toString(),
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
            $customBlockName = 'contact_form_field_' . $fieldId;
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
            } else if ($this->template->blockExists($customBlockName)) {
                $this->template->replaceBlock(
                    $customBlockName,
                    $this->parseFormField(
                        $this->template->getUnparsedBlock($customBlockName),
                        $fieldId,
                        $arrField,
                        $profileData
                    )
                );
            } else {
                $fieldType = $arrField['type'];
                if ($fieldType == 'special') {
                    $fieldType = $arrField['special_type'];
                }
                $this->template->setVariable(array(
                    'CONTACT_FORM_FIELD' =>
                    $this->parseFormField(
                        $this->fieldTemplates[$fieldType]->getContent(),
                        $fieldId,
                        $arrField,
                        $profileData
                    )
                ));
                $this->template->parse('contact_form_field_list');
            }
        }
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
     * @return string
     */
    protected function getSourceCode()
    {
        $formFields  = $this->getFormFields($this->form->getId());
        $formContent = $this->template->getUnparsedBlock('contact_form_section');
        $formFieldContent = array();
        foreach ($formFields as $fieldId => $arrField) {
            $customBlockName = 'contact_form_field_' . $fieldId;
            if (
                $this->template->placeholderExists($fieldId . '_VALUE') ||
                $this->template->placeholderExists($fieldId . '_LABEL') ||
                $this->template->blockExists($customBlockName)
            ) {
                continue;
            }

            $formFieldContent[] =
                "<!-- BEGIN contact_form_field_" . $fieldId . " -->\n" .
                $this->fieldTemplates[$arrField['type']]->getContent() .
                "\n<!-- END contact_form_field_" . $fieldId . " -->\n";
        }

        return preg_replace(
            '@(<!--\s*BEGIN\s+(contact_form_field_list)\s*-->.*?<!--\s*END\s+\2\s*-->)@s',
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
     * @param string $fieldContent Content of the FormField
     * @param string $fieldId      FormField ID
     * @param array  $arrField     Array of FormField values
     * @param array  $profileData  Array of User Profile data
     *
     * @return string
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

        $fieldValue = preg_replace(
            '/\[\[([A-Z0-9_]+)\]\]/',
            '{$1}',
            $arrField['lang'][$this->langId]['value']
        );
        $fieldLabel = '&nbsp;';
        if (!empty($arrField['lang'][$this->langId]['name'])) {
            $fieldLabel = contrexx_raw2xhtml($arrField['lang'][$this->langId]['name']);
        }
        if ($arrField['is_required']) {
            $template->touchBlock('contact_form_field_required');
        }
        $fieldType = $arrField['type'];
        if ($fieldType == 'special') {
            $fieldType = $arrField['special_type'];
        }
        $template->setVariable(array(
            'CONTACT_FORM_FIELD_ID'    => $fieldId,
            'CONTACT_FORM_FIELD_LABEL' => $fieldLabel
        ));
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
                // collect user attribute options
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
                    $options[$index] = preg_replace(
                        '/\[\[([A-Z0-9_]+)\]\]/', '{$1}',
                        $recipient['lang'][$this->langId]
                    );
                }
                $this->parseFormFieldValue($template, $fieldId, $fieldType, $options);
                break;
            
            case 'access_country':
            case 'country':
                if (preg_match($userProfileRegExp, $fieldValue)) {
                    $fieldValue = $template->_globalVariables[trim($fieldValue, '{}')];
                }
                $country = \Cx\Core\Country\Controller\Country::getNameArray(
                    true,
                    $fieldValue
                );
                foreach ($country as $id => $name) {
                    $template->setVariable('CONTACT_FORM_FIELD_VALUE', $name);

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
                        strcasecmp($name, $valueFromPost) == 0;
                    if (
                        $isOptionInPost ||
                        (
                         !empty($valueFromGet) &&
                         strcasecmp($name, $valueFromGet) == 0
                        ) ||
                        $name == $fieldValue
                    ) {
                        $template->setVariable(
                            'CONTACT_FORM_FIELD_SELECTED',
                            'selected = "selected"'
                        );
                    }
                    $template->parse('contact_form_field_options');
                }
                $template->setVariable(array(
                    'TXT_CONTACT_PLEASE_SELECT' => $_ARRAYLANG['TXT_CONTACT_PLEASE_SELECT'],
                    'TXT_CONTACT_NOT_SPECIFIED' => $_ARRAYLANG['TXT_CONTACT_NOT_SPECIFIED']
                ));
                break;
            
            case 'file':
                $this->hasFileField  = true;
                $this->uploaderCode .= $this->initUploader($template, $fieldId, true);
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
     * @param string              $fieldValue Field value
     */
    protected function parseFormFieldValue(
        \Cx\Core\Html\Sigma $template,
        $fieldId,
        $fieldType,
        $fieldValue
    ) {
        if (empty($fieldValue)) {
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
        if (!is_array($fieldValue)) {
            $fieldValue = explode(',', $fieldValue);
        }
        foreach ($fieldValue as $index => $option) {
            if (preg_match('/\{([A-Z_]+)\}/', $option)) {
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
                'CONTACT_FORM_FIELD_VALUE_ID' => $index,
                'CONTACT_FORM_BLOCK_FIELD_ID' => $fieldId
            ));
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
                (   in_array($option, $valueFromPost) ||
                    $option == $valueFromPost ||
                    $index == array_search($valueFromPost, $options)
                );
            $isOptionInGet =
                !empty($valueFromGet) &&
                (   $option == $valueFromGet ||
                    $index == array_search($valueFromGet, $options)
                );
            $isOptionInAccessAttr =
                $isSpecialType &&
                isset($template->_globalVariables[strtoupper($accessAttrId)]) &&
                $option == $template->_globalVariables[strtoupper($accessAttrId)];
            if ($isOptionInPost || $isOptionInGet || $isOptionInAccessAttr) {
                $template->setVariable(
                    'CONTACT_FORM_FIELD_SELECTED',
                    $selectedText
                );
            }
            $template->parse('contact_form_field_options');
        }
    }

    /**
     * Parse Input Field's value
     *
     * @param \Cx\Core\Html\Sigma $template         Template object
     * @param string              $fieldId          Form Field ID
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
            $valuePlaceholderBlock = 'contact_value_placeholder_block_' . $fieldId;
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
        } else if (!empty($_GET[$fieldId])) {
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

            switch ($objAttribute->getType())
            {
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
            $profileData[$attrPlaceholder] = contrexx_raw2xhtml($value);
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
        $this->template->setVariable('CONTACT_UNIQUE_ID', $id);
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
            $uploader->setCallback('contactFormUploader_'.$fieldId);

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
                'id'     => 'contactUploader_'.$uploaderId,
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
            $strInputfield  = $uploader->getXHtml();
            $strInputfield .= <<<CODE
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
            return $strInputfield;
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
    public function getPreview()
    {
        return $this->preview;
    }
}