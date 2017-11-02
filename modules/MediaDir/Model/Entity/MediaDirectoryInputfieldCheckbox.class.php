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
 * Media  Directory Inputfield Checkbox Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Model\Entity;
/**
 * Media  Directory Inputfield Checkbox Class
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryInputfieldCheckbox extends \Cx\Modules\MediaDir\Controller\MediaDirectoryLibrary implements Inputfield
{
    public $arrPlaceholders = array('TXT_MEDIADIR_INPUTFIELD_NAME','MEDIADIR_INPUTFIELD_VALUE');



    /**
     * Constructor
     */
    function __construct($name)
    {
        parent::__construct('.', $name);
    }



    function getInputfield($intView, $arrInputfield, $intEntryId=null)
    {
        global $objDatabase, $_LANGID, $objInit;

        $intId = intval($arrInputfield['id']);

        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                if(isset($intEntryId) && $intEntryId != 0) {
                    $objInputfieldValue = $objDatabase->Execute("
                        SELECT
                            `value`
                        FROM
                            ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
                        WHERE
                            field_id=".$intId."
                        AND
                            entry_id=".$intEntryId."
                        LIMIT 1
                    ");
                    if(!empty($objInputfieldValue->fields['value'])) {
                        $arrValue = explode(",",$objInputfieldValue->fields['value']);
                    } else {
                        $arrValue = null;
                    }
                } else {
                    $arrValue = null;
                }

                $strOptions = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];
                $arrOptions = explode(",", $strOptions);

                if(!empty($arrInputfield['info'][0])){
                    $strInfoValue = empty($arrInputfield['info'][$_LANGID]) ? 'title="'.$arrInputfield['info'][0].'"' : 'title="'.$arrInputfield['info'][$_LANGID].'"';
                    $strInfoClass = 'mediadirInputfieldHint';
                } else {
                    $strInfoValue = null;
                    $strInfoClass = '';
                }

                if($objInit->mode == 'backend') {
                    $strInputfield = '<span id="'.$this->moduleNameLC.'Inputfield_'.$intId.'_list" style="display: block;">';
                    foreach($arrOptions as $intKey => $strDefaultValue) {
                        $intKey++;
                        if(in_array($intKey, $arrValue)) {
                            $strChecked = 'checked="checked"';
                        } else {
                            $strChecked = '';
                        }

                        $strInputfield .= '<input type="checkbox" name="'.$this->moduleNameLC.'Inputfield['.$intId.'][]" id="'.$this->moduleNameLC.'Inputfield_'.$intId.'_'.$intKey.'" value="'.$intKey.'" '.$strChecked.' />&nbsp;' . $strDefaultValue . '<br />';
                    }

                    $strInputfield .= '</span>';
                } else {
                    $strInputfield = '<span id="'.$this->moduleNameLC.'Inputfield_'.$intId.'_list" style="display: block;">';

                    foreach($arrOptions as $intKey => $strDefaultValue) {
                        $intKey++;
                        if(in_array($intKey, $arrValue)) {
                            $strChecked = 'checked="checked"';
                        } else {
                            $strChecked = '';
                        }

                        $strInputfield .= '<input class="'.$this->moduleNameLC.'InputfieldCheckbox '.$strInfoClass.'" '.$strInfoValue.' type="checkbox" name="'.$this->moduleNameLC.'Inputfield['.$intId.'][]" id="'.$this->moduleNameLC.'Inputfield_'.$intId.'_'.$intKey.'" value="'.$intKey.'" '.$strChecked.' />&nbsp;' . contrexx_raw2xhtml($strDefaultValue) . '<br />';
                    }


                    $strInputfield .= '</span>';
                }


                return $strInputfield;

                break;
            case 2:
                //search View
                $strOptions = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];
                $arrOptions = explode(",", $strOptions);

                $arrSelected = isset($_GET[$intId]) ? $_GET[$intId] : array();
                $strChecked = '';

                $strInputfield = '<div class="checkboxes_' . $intId . '">';
                foreach($arrOptions as $intKey => $strDefaultValue) {
                    $intKey++;
                    $strChecked = in_array($intKey, $arrSelected) ? 'checked="checked"' : '';

                    $strInputfield .= '<input type="checkbox" name="'.$intId.'[]" class="'.$this->moduleNameLC.'InputfieldSearch" id="'.$this->moduleNameLC.'InputfieldSearch_'. $intId .'_'. $intKey .'" value="'. $intKey.'" '. $strChecked .' /><label for="'.$this->moduleNameLC.'InputfieldSearch_'. $intId .'_'. $intKey .'">'. contrexx_raw2xhtml($strDefaultValue) .'</label>';
                }
                $strInputfield .= '</div>';
                return $strInputfield;
                break;
        }
    }



    function saveInputfield($intInputfieldId, $strValue, $langId = 0)
    {
        $arrValue = $strValue;

        foreach($arrValue as $intKey => $strValue) {
            $arrValue[$intKey] = $strValue = contrexx_strip_tags(contrexx_input2raw($strValue));
        }

        $strValue = join(",",$arrValue);

        return $strValue;
    }


    function deleteContent($intEntryId, $intIputfieldId)
    {
        global $objDatabase;

        $objDeleteInputfield = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields WHERE `entry_id`='".intval($intEntryId)."' AND  `field_id`='".intval($intIputfieldId)."'");

        if($objDeleteInputfield !== false) {
            return true;
        } else {
            return false;
        }
    }



    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus)
    {
        $arrValues = explode(",", $arrInputfield['default_value'][0]);
        $strValue = strip_tags(htmlspecialchars(static::getDataFromDb($intEntryId, $arrInputfield), ENT_QUOTES, CONTREXX_CHARSET));

        //explode elements
        $arrElements = explode(",", $strValue);

        //open <ul> list
        $strValue = '<ul class="'.$this->moduleNameLC.'InputfieldCheckbox">';

        //make element list
        foreach ($arrElements as $intKey => $strElement) {
            $strElement = $strElement-1;
            $strValue .= '<li>'.$arrValues[$strElement].'</li>';
        }

        //close </ul> list
        $strValue .= '</ul>';

        if($arrElements[0] != null) {
            $arrContent['TXT_'.$this->moduleLangVar.'_INPUTFIELD_NAME'] = htmlspecialchars($arrInputfield['name'][0], ENT_QUOTES, CONTREXX_CHARSET);
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_VALUE'] = $strValue;
        } else {
            $arrContent = null;
        }

        return $arrContent;
    }

    function getRawData($intEntryId, $arrInputfield, $arrTranslationStatus) {
        $strValue = static::getDataFromDb($intEntryId, $arrInputfield);

        //explode elements
        $arrElements = explode(",", $strValue);

        $arrValues = explode(",", $arrInputfield['default_value'][0]);
        $strValues = array();
        foreach ($arrElements as $strElement) {
            $strValues[] = $arrValues[$strElement - 1];
        }

        return implode(',', $strValues);
    }

    function getDataFromDb($intEntryId, $arrInputfield) {
        global $objDatabase;

        $intId = intval($arrInputfield['id']);
        $objInputfieldValue = $objDatabase->Execute("
            SELECT
                `value`
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
            WHERE
                field_id=".$intId."
            AND
                entry_id=".$intEntryId."
            LIMIT 1
        ");

        return $objInputfieldValue->fields['value'];
    }


    function getJavascriptCheck()
    {
        $fieldName = $this->moduleNameLC."Inputfield_";
        $fieldName2 = $this->moduleNameLC."Inputfield[";
        $strJavascriptCheck = <<<EOF

            case 'checkbox':
                if (isRequiredGlobal(inputFields[field][1], value)) {
                    var boxes = document.getElementsByName('$fieldName2' + field + '][]');
                    var checked = false;

                    for (var i = 0; i < boxes.length; i++) {
                        if (boxes[i].checked) {
                            checked = true;
                        }
                    }

                    if (!checked) {
                        document.getElementById('$fieldName' + field + '_list').style.border = "#ff0000 1px solid";
                        isOk = false;
                    } else {
                        document.getElementById('$fieldName' + field + '_list').style.border = "#ff0000 0px solid";
                    }
                }
                break;

EOF;
        return $strJavascriptCheck;
    }


    function getFormOnSubmit($intInputfieldId)
    {
        return null;
    }
}
