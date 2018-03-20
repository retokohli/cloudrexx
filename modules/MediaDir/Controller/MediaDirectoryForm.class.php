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
 * Media  Directory Form Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Controller;
/**
 * Media Directory Form Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryForm extends MediaDirectoryLibrary
{
    public $intFormId;

    public $arrForms = array();

    /**
     * Constructor
     */
    function __construct($intFormId=null, $name)
    {
        $this->intFormId = intval($intFormId);

        parent::__construct('.', $name);
        parent::getSettings();
        parent::getFrontendLanguages();
        $this->arrForms = self::getForms($this->intFormId);
    }

    function getForms($intFormId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $objInit;

        $arrForms = array();

        $langId = static::getOutputLocale()->getId();

        if(!empty($intFormId)) {
            $whereFormId = "form.id='".$intFormId."' AND";
        } else {
        $whereFormId = '';
        }

        $strSlugField = '';
        $strJoinSlugField = '';

        if ($this->arrSettings['usePrettyUrls']) {
            $strSlugField = ",
                slug_field.`id` as `slug_field_id`
            ";
            $strJoinSlugField = "
                LEFT JOIN
                    ".DBPREFIX."module_".$this->moduleTablePrefix."_inputfields AS slug_field
                ON
                    slug_field.`form` = form.`id`
                    AND slug_field.`context_type` = 'slug'
            ";
        }

        $objFormsRS = $objDatabase->Execute("
            SELECT form.`id`, form.`order`, form.`picture`, form.`cmd`,
                form.`use_category`, form.`use_level`,
                form.`use_ready_to_confirm`, form.`use_associated_entries`,
                form.`entries_per_page`, form.`active`,
                form_names.`form_name` AS `name`,
                form_names.`form_description` AS `description`
                ".$strSlugField."
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_forms AS form
            INNER JOIN ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names AS form_names
                ON form_names.form_id=form.id
                ".$strJoinSlugField."
            WHERE
                $whereFormId
                form_names.lang_id='".$langId."'
            ORDER BY
                `order` ASC
            ");

        if ($objFormsRS !== false) {
            while (!$objFormsRS->EOF) {

            $arrForm = array();
            $arrFormName = array();
            $arrFormDesc = array();

                //get lang attributes
            $arrFormName[0] = $objFormsRS->fields['name'];
            $arrFormDesc[0] = $objFormsRS->fields['description'];

                $objFormAttributesRS = $objDatabase->Execute("
                    SELECT
                        `lang_id` AS `lang_id`,
                        `form_name` AS `name`,
                    `form_description` AS `description`
                    FROM
                        ".DBPREFIX."module_".$this->moduleTablePrefix."_form_names
                    WHERE
                        form_id=".$objFormsRS->fields['id']."
                ");

                if ($objFormAttributesRS !== false) {
                    while (!$objFormAttributesRS->EOF) {
                        $arrFormName[$objFormAttributesRS->fields['lang_id']] = htmlspecialchars($objFormAttributesRS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                        $arrFormDesc[$objFormAttributesRS->fields['lang_id']] = htmlspecialchars($objFormAttributesRS->fields['description'], ENT_QUOTES, CONTREXX_CHARSET);

                        $objFormAttributesRS->MoveNext();
                    }
                }

                $arrForm['formId']                = intval($objFormsRS->fields['id']);
                $arrForm['formOrder']             = intval($objFormsRS->fields['order']);
                    $arrForm['formPicture']           = htmlspecialchars($objFormsRS->fields['picture'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrForm['formName']              = $arrFormName;
                $arrForm['formDescription']       = $arrFormDesc;
                $arrForm['formActive']            = intval($objFormsRS->fields['active']);
                    $arrForm['formCmd']               = htmlspecialchars($objFormsRS->fields['cmd'], ENT_QUOTES, CONTREXX_CHARSET);
                    $arrForm['formUseCategory']       = intval($objFormsRS->fields['use_category']);
                    $arrForm['formUseLevel']          = intval($objFormsRS->fields['use_level']);
                    $arrForm['formUseReadyToConfirm'] = intval($objFormsRS->fields['use_ready_to_confirm']);
                    $arrForm['formEntriesPerPage']    = $objFormsRS->fields['entries_per_page'];
                    $arrForm['slug_field_id']         = $this->arrSettings['usePrettyUrls'] ? $objFormsRS->fields['slug_field_id'] : 0;
                $arrForm['use_associated_entries'] =
                    intval($objFormsRS->fields['use_associated_entries']);
                if ($arrForm['use_associated_entries']) {
                    $arrForm['target_form_ids'] =
                        $this->getAssociatedFormIdsByFormId($arrForm['formId']);
                }
                $arrForms[$objFormsRS->fields['id']] = $arrForm;
                $objFormsRS->MoveNext();
            }
        }

        return $arrForms;
    }



    function listForms($objTpl, $intView, $intFormId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $i = 0;

        switch ($intView) {
            case 1:
                //settings overview
                if(!empty($this->arrForms)){
                    foreach ($this->arrForms as $key => $arrForm) {
                        //get status
                        if($arrForm['formActive'] == 1) {
                            $strStatus = '../core/Core/View/Media/icons/status_green.gif';
                            $intStatus = 0;
                        } else {
                            $strStatus = '../core/Core/View/Media/icons/status_red.gif';
                            $intStatus = 1;
                        }

                        //parse data variables
                        $objTpl->setVariable(array(
                            $this->moduleLangVar.'_FORM_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                            $this->moduleLangVar.'_FORM_ID' => $arrForm['formId'],
                            $this->moduleLangVar.'_FORM_TITLE' => contrexx_raw2xhtml($arrForm['formName'][0]),
                            $this->moduleLangVar.'_FORM_DESCRIPTION' => contrexx_raw2xhtml($arrForm['formDescription'][0]),
                            $this->moduleLangVar.'_FORM_ORDER' => $arrForm['formOrder'],
                            $this->moduleLangVar.'_FORM_STATUS' => $strStatus,
                            $this->moduleLangVar.'_FORM_SWITCH_STATUS' => $intStatus,
                        ));

                        $i++;
                        $objTpl->parse($this->moduleNameLC.'FormTemplateList');
                        $objTpl->hideBlock($this->moduleNameLC.'FormTemplateNoEntries');
                        $objTpl->clearVariables();
                    }
                } else {
                    $objTpl->setGlobalVariable(array(
                        'TXT_'.$this->moduleLangVar.'_NO_ENTRIES_FOUND' => $_ARRAYLANG['TXT_MEDIADIR_NO_ENTRIES_FOUND']
                    ));

                    $objTpl->touchBlock($this->moduleNameLC.'FormTemplateNoEntries');
                    $objTpl->clearVariables();
                }
                break;
            case 2:
                //form selector backend (add entry view)
                $arrDropdownOptions[0] = $_ARRAYLANG['TXT_MEDIADIR_CHOOSE'];

                foreach ($this->arrForms as $key => $arrForm) {
                    if($arrForm['formActive'] == 1) {
                        $arrDropdownOptions[$arrForm['formId']] = $arrForm['formName'][0];
                    }
                }

                $strDropdown = $this->buildDropdownmenu($arrDropdownOptions, null);

                //parse data variables
                $objTpl->setVariable(array(
                    'TXT_'.$this->moduleLangVar.'_CHOOSE_FORM' => $_ARRAYLANG['TXT_MEDIADIR_CHOOSE_FORM'],
                    $this->moduleLangVar.'_FORM_LIST' => '<select onchange="document.entryModfyForm.submit();" name="selectedFormId" style="width: 302px">'.$strDropdown."</select>",
                ));

                $objTpl->parse($this->moduleNameLC.'FormList');
                $objTpl->clearVariables();

                break;
            case 3:
                //form selector frontend (add entry view)

                foreach ($this->arrForms as $key => $arrForm) {
                    if($arrForm['formActive'] == 1) {

                        $hasPicture = $arrForm['formPicture'] != '';
                        $thumbImage = $this->getThumbImage($arrForm['formPicture']);
                        //parse data variables
                        $objTpl->setVariable(array(
                            $this->moduleLangVar.'_FORM_ROW_CLASS' => $i%2==0 ? 'row1' : 'row2',
                            'TXT_'.$this->moduleLangVar.'_FORM_ID' => $arrForm['formId'],
                            'TXT_'.$this->moduleLangVar.'_FORM_TITLE' => contrexx_raw2xhtml($arrForm['formName'][0]),
                            'TXT_'.$this->moduleLangVar.'_FORM_DESCRIPTION' => nl2br(contrexx_raw2xhtml($arrForm['formDescription'][0])),
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE' => $hasPicture ? '<img src="'.$arrForm['formPicture'].'" alt="'.contrexx_raw2xhtml($arrForm['formName'][0]).'" />' : '',
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE_SRC' => $arrForm['formPicture'],
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE_SRC_THUMB' => $thumbImage,
                            'TXT_'.$this->moduleLangVar.'_FORM_IMAGE_THUMB' => $hasPicture ?'<img src="'. $thumbImage .'" alt="'.contrexx_raw2xhtml($arrForm['formName'][0]).'" />' : '',
                        ));

                        $i++;
                        $objTpl->parse($this->moduleNameLC.'FormList');
                        $objTpl->clearVariables();
                    }
                }

                $objTpl->parse($this->moduleNameLC.'Forms');
                break;

            case 4:
                //Dropdown Menu
                $strDropdownOptions = '';
                foreach ($this->arrForms  as $key => $arrForm) {
                    if($arrForm['formActive'] == 1) {
                        if($arrForm['formId'] == $intFormId) {
                            $strSelected = 'selected="selected"';
                        } else {
                            $strSelected = '';
                        }

                        $strDropdownOptions .= '<option value="'.$arrForm['formId'].'" '.$strSelected.' >'.contrexx_raw2xhtml($arrForm['formName'][0]).'</option>';
                    }
                }

                return $strDropdownOptions;
                break;
        }
    }

    /**
     * Update the form values
     *
     * @param array   $arrName        Form names array, The array key is refered as the language id
     * @param array   $arrDescription Form description array, The array key is refered as the language id
     * @param integer $intFormId      Form id
     *
     * @return boolean true | false
     */
    public function updateFormLocale($arrName, $arrDescription, $intFormId)
    {
        global $objDatabase;

        if (empty($intFormId)) {
            return false;
        }

        $objDefaultLang = $objDatabase->Execute('
            SELECT
                `form_name` AS `name`,
                `form_description` AS `description`
            FROM
                '.DBPREFIX.'module_'.$this->moduleTablePrefix.'_form_names
            WHERE
                lang_id='.static::getOutputLocale()->getId().'
                AND `form_id` = "'.$intFormId.'"
            LIMIT
                1
        ');

        $strOldDefaultName        = '';
        $strOldDefaultDescription = '';

        if ($objDefaultLang !== false) {
            $strOldDefaultName        = $objDefaultLang->fields['name'];
            $strOldDefaultDescription = $objDefaultLang->fields['description'];
        }

        foreach ($this->arrFrontendLanguages as $lang) {
            $activeLang[] = $lang['id'];
        }
        // Before updating the form names Remove the corresponding existing form names from db.
        $objDatabase->Execute('DELETE FROM ' . DBPREFIX . 'module_' . $this->moduleTablePrefix . '_form_names WHERE form_id="' . $intFormId . '" AND lang_id IN("'.  implode('","', $activeLang).'")');

        foreach ($this->arrFrontendLanguages as $arrLang) {
            $strName        = $arrName[$arrLang['id']];
            $strDescription = $arrDescription[$arrLang['id']];

            if ($arrLang['id'] == static::getOutputLocale()->getId()) {
                if ($arrName[0] != $strOldDefaultName) {
                    $strName = $arrName[0];
                }
                if ($arrName[$arrLang['id']] != $strOldDefaultName) {
                    $strName = $arrName[$arrLang['id']];
                }
                if ($arrDescription[0] != $strOldDefaultDescription) {
                    $strDescription = $arrDescription[0];
                }
                if ($arrDescription[$arrLang['id']] != $strOldDefaultDescription) {
                    $strDescription = $arrDescription[$arrLang['id']];
                }
            }

            if (empty($strName)) {
                $strName = $arrName[0];
            }
            if (empty($strDescription)) {
                $strDescription = $arrDescription[0];
            }
            $objInsertNames = $objDatabase->Execute('
                        INSERT INTO
                            ' . DBPREFIX . 'module_' . $this->moduleTablePrefix . '_form_names
                        SET
                            `lang_id`="' . intval($arrLang['id']) . '",
                            `form_id`="' . intval($intFormId) . '",
                            `form_name`="' . contrexx_input2db($strName) . '",
                            `form_description`="' . contrexx_input2db($strDescription) . '"
                    ');
        }

        return $objInsertNames;
    }

    function saveForm($arrData, $intFormId=null)
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $intId = intval($intFormId);
        $strPicture = contrexx_strip_tags($arrData['formImage']);
        $arrName = $arrData['formName'];
        $arrDescription = $arrData['formDescription'];
        $strCmd = strtolower(contrexx_strip_tags($arrData['formCmd']));
        $intUseCategory = intval($arrData['formUseCategory']);
        $intUseLevel = isset($arrData['formUseLevel']) ? contrexx_input2int($arrData['formUseLevel']) : 0;
        $intUseReadyToConfirm = isset($arrData['formUseReadyToConfirm']) ? contrexx_input2int($arrData['formUseReadyToConfirm']) : 0;
        $intEntriesPerPage = isset($arrData['formEntriesPerPage']) ? contrexx_input2int($arrData['formEntriesPerPage']) : 0;
        $use_associated_entries = !empty($arrData['use_associated_entries']);
        $target_form_ids =
            !$use_associated_entries || empty($arrData['target_form_ids'])
                ? [] : $arrData['target_form_ids'];
        if (empty($intId)) {
            if (!$objDatabase->Execute('
                INSERT INTO ' . DBPREFIX . 'module_'
                . $this->moduleTablePrefix . '_forms
                SET `order`=?, `picture`=?, `cmd`=?,
                    `use_category`=?, `use_level`=?,
                    `use_ready_to_confirm`=?, `use_associated_entries`=?,
                    `entries_per_page`=?, `active`=?',
                [99, $strPicture, $strCmd,
                $intUseCategory, $intUseLevel,
                $intUseReadyToConfirm, $use_associated_entries,
                $intEntriesPerPage, 0])) {
                return false;
            }
            $intId = $objDatabase->Insert_ID();

                foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                    if(empty($arrName[0])) $arrName[0] = "";

                $strName = $arrName[$arrLang['id']];
                $strDescription = $arrDescription[$arrLang['id']];
                if (empty($strName)) {
                    $strName = $arrName[0];
                }
                if (empty($strDescription)) {
                    $strDescription = $arrDescription[0];
                }
                if (!$objDatabase->Execute('
                    INSERT INTO ' . DBPREFIX . 'module_'
                    . $this->moduleTablePrefix . '_form_names
                    SET `lang_id`=?, `form_id`=?,
                        `form_name`=?,
                        `form_description`=?',
                    [$arrLang['id'], $intId,
                    contrexx_input2raw($strName),
                    contrexx_input2raw($strDescription)])) {
                    return false;
                }
            }
            if (!$objDatabase->Execute('
                INSERT INTO ' . DBPREFIX . 'module_'
                . $this->moduleTablePrefix . '_order_rel_forms_selectors
                SET `selector_id`=?, `form_id`=?,
                    `selector_order`=?, `exp_search`=?',
                [9, $intId, 0, 1])) {
                return false;
            }
            if (!$objDatabase->Execute('
                INSERT INTO ' . DBPREFIX . 'module_'
                . $this->moduleTablePrefix . '_order_rel_forms_selectors
                SET `selector_id`=?, `form_id`=?,
                    `selector_order`=?, `exp_search`=?',
                [10, $intId, 1, 1])) {
                return false;
            }
            parent::getCommunityGroups();
            foreach (array_keys($this->arrCommunityGroups) as $intGroupId) {
                if (!$objDatabase->Execute('
                    INSERT INTO ' . DBPREFIX . 'module_'
                    . $this->moduleTablePrefix . '_settings_perm_group_forms
                    SET `group_id`=?, `form_id`=?, `status_group`=?',
                    [$intGroupId, $intId, 1])) {
                    return false;
                }
            }
        } else {
            if (!$objDatabase->Execute('
                UPDATE ' . DBPREFIX . 'module_'
                . $this->moduleTablePrefix . '_forms
                SET `picture`=?, `cmd`=?,
                    `use_category`=?, `use_level`=?,
                    `use_ready_to_confirm`=?, `use_associated_entries`=?,
                    `entries_per_page`=?
                WHERE `id`=?',
                [$strPicture, $strCmd,
                $intUseCategory, $intUseLevel,
                $intUseReadyToConfirm, $use_associated_entries,
                $intEntriesPerPage, $intId])) {
                return false;
            }
            if (!$objDatabase->Execute('
                DELETE FROM ' . DBPREFIX . 'module_'
                . $this->moduleTablePrefix . '_settings_perm_group_forms
                WHERE form_id=?',
                [$intId])) {
                return false;
            }
            $settingsPermissionGroupForm =
                isset($arrData['settingsPermGroupForm'][$intId])
                    ? $arrData['settingsPermGroupForm'][$intId] : array();
            foreach ($settingsPermissionGroupForm as $intGroupId => $intGroupStatus) {
                if (!$objDatabase->Execute('
                    INSERT INTO ' . DBPREFIX . 'module_'
                    . $this->moduleTablePrefix . '_settings_perm_group_forms
                    SET `group_id`=?, `form_id`=?, `status_group`=?',
                    [$intGroupId, $intId, $intGroupStatus])) {
                    return false;
                }
            }
            if (!$this->updateFormLocale($arrName, $arrDescription, $intId)) {
                return false;
            }
        }
        return $this->storeAssociatedFormIds($intId, $target_form_ids);
    }

    function deleteForm($intFormId)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute('
            SELECT `id`
            FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_entries
            WHERE `form_id`=?', [$intFormId]);
        if (!$objResult) {
            return false;
        }
        while (!$objResult->EOF) {
            if (!MediaDirectoryEntry::deleteById($objResult->fields['id'])) {
                return false;
            }
            $objResult->MoveNext();
        }
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_form_associated_form
            WHERE `source_form_id`=?
            OR `target_form_id`=?', [$intFormId, $intFormId])) {
            return false;
        }
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_order_rel_forms_selectors
            WHERE `form_id`=?', [$intFormId])) {
            return false;
        }
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_inputfields
            WHERE `form`=?', [$intFormId])) {
            return false;
        }
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_inputfield_names
            WHERE `form_id`=?', [$intFormId])) {
            return false;
        }
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_settings_perm_group_forms
            WHERE `form_id`=?', [$intFormId])) {
            return false;
        }
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_forms
            WHERE `id`=?', [$intFormId])) {
            return false;
        }
        return true;
    }

    function saveOrder($arrData) {
        global $objDatabase;

        foreach($arrData['formsOrder'] as $intFormId => $intFormOrder) {
            $objRSFormOrder = $objDatabase->Execute("UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_forms SET `order`='".intval($intFormOrder)."' WHERE `id`='".intval($intFormId)."'");

            if ($objRSFormOrder === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return HTML options for associated forms
     *
     * Forms associated to the one with the given ID have their "selected"
     * attribute set.
     * @param   integer $form_id
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getAssociatedFormsOptions($form_id)
    {
        $forms = $this->getForms();
        $options = $selected =
            array_flip($this->getAssociatedFormIdsByFormId($form_id));
        foreach ($forms as $form) {
            $id = $form['formId'];
            // Update values for existing selected keys, append others
            $options[$id]= $form['formName'][0];
        }
        $option_string = \Html::getOptions($options, $selected);
        return $option_string;
    }

    /**
     * Return an array of form IDs associated to the given Form ID
     *
     * Returns false on error.
     * Mind that the returned array may be empty.
     * @global  ADOConnection   $objDatabase
     * @param   integer         $form_id
     * @return  array|boolean                   The ID array on success,
     *                                          false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getAssociatedFormIdsByFormId($form_id)
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute('
            SELECT `target_form_id`
            FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_form_associated_form
            WHERE `source_form_id`=?
            ORDER BY `ord` ASC',
            [$form_id]);
        if (!$objResult) {
            return false;
        }
        $form_ids = [];
        while (!$objResult->EOF) {
            $form_ids[] = intval($objResult->fields['target_form_id']);
            $objResult->MoveNext();
        }
        return $form_ids;
    }

    /**
     * Store an array of target Form IDs associated to the given source Form ID
     *
     * Returns false on error.
     * Note that associations with Entries of Forms whose ID is not present in
     * $target_form_ids anymore are not deleted.
     * @global  ADOConnection   $objDatabase
     * @param   integer         $source_form_id
     * @param   array           $target_form_ids
     * @return  array|boolean                   True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function storeAssociatedFormIds(
        $source_form_id, array $target_form_ids)
    {
        global $objDatabase;
        if (!$objDatabase->Execute('
            DELETE FROM ' . DBPREFIX . 'module_'
            . $this->moduleTablePrefix . '_form_associated_form
            WHERE `source_form_id`=?',
            [$source_form_id])) {
            return false;
        }
        foreach ($target_form_ids as $ord => $target_form_id) {
            if (!$objDatabase->Execute('
                INSERT INTO ' . DBPREFIX . 'module_'
                . $this->moduleTablePrefix . '_form_associated_form (
                    `source_form_id`, `target_form_id`, `ord`
                ) VALUES (
                    ?, ?, ?
                )',
                [$source_form_id, $target_form_id, $ord])) {
                return false;
            }
        }
        return true;
    }

}
