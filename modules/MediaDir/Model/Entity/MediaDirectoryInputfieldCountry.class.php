<?php

/**
 * Marketplace Modul Inputfield Country Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */
namespace Cx\Modules\MediaDir\Model\Entity;
/**
 * Marketplace Modul Inputfield Country Class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 */
class MediaDirectoryInputfieldCountry extends \Cx\Modules\MediaDir\Controller\MediaDirectoryLibrary implements Inputfield
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
        global $objDatabase, $_LANGID, $objInit, $_ARRAYLANG;

        $intId = intval($arrInputfield['id']);

        switch ($intView) {
            default:
            case 1:
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
                    $strValue = intval($objInputfieldValue->fields['value']);
                } else {
                    $strValue = null;
                }
                
                if(empty($strValue)) {
                    if(substr($arrInputfield['default_value'][0],0,2) == '[[') {
                        $objPlaceholder = new \Cx\Modules\MediaDir\Controller\MediaDirectoryPlaceholder($this->moduleName);
                        $strValue = $objPlaceholder->getPlaceholder($arrInputfield['default_value'][0]);
                    } else {
                        $strValue = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];
                    }
                }
                
                if(!empty($arrInputfield['info'][0])){
                    $strInfoValue = empty($arrInputfield['info'][$_LANGID]) ? 'title="'.$arrInputfield['info'][0].'"' : 'title="'.$arrInputfield['info'][$_LANGID].'"';
                    $strInfoClass = 'mediadirInputfieldHint';
                } else {
                    $strInfoValue = null;
                    $strInfoClass = '';
                }

                $objInputfieldValue = $objDatabase->Execute("
                        SELECT
                            `id`, `name`
                        FROM
                            ".DBPREFIX."lib_country
                        ORDER BY
                        	`name`
                    ");
                
                if($objInit->mode == 'backend') {
                	$strInputfield = '<select name="'.$this->moduleNameLC.'Inputfield['.$intId.']" id="'.$this->moduleNameLC.'Inputfield_'.$intId.'" class="'.$this->moduleNameLC.'InputfieldDropdown" style="width: 302px">';
                } else {
                	$strInputfield = '<select name="'.$this->moduleNameLC.'Inputfield['.$intId.']" id="'.$this->moduleNameLC.'Inputfield_'.$intId.'" class="'.$this->moduleNameLC.'InputfieldDropdown '.$strInfoClass.'" '.$strInfoValue.'>';
                }

                $strInputfieldOptions = '';
                while(!$objInputfieldValue->EOF) {
					$strOptionSelected = ($strValue == $objInputfieldValue->fields['id']) ? 'selected="selected"' : '';

                	$strInputfieldOptions .= '<option value="'.$objInputfieldValue->fields['id'].'" '.$strOptionSelected.'>'.$objInputfieldValue->fields['name'].'</option>';
                	$objInputfieldValue->MoveNext();
                }

                $strInputfield .= $strInputfieldOptions.'</select>';

                return $strInputfield;

                break;
            case 2:
                //search View
                $objInputfieldValue = $objDatabase->Execute("
                        SELECT
                            `id`, `name`
                        FROM
                            ".DBPREFIX."lib_country
                        ORDER BY
                        	`name`
                    ");

                while(!$objInputfieldValue->EOF) {
                	$strInputfieldOptions .= '<option value="'.$objInputfieldValue->fields['id'].'">'.$objInputfieldValue->fields['name'].'</option>';
                	$objInputfieldValue->MoveNext();
                }

                $strInputfield = '<select name="'.$intId.'" class="'.$this->moduleName.'InputfieldSearch">';
                $strInputfield .= '<option  value="">'.$_ARRAYLANG['TXT_MEDIADIR_PLEASE_CHOOSE'].'</option>';

                $strInputfield .= $strInputfieldOptions.'</select>';

                return $strInputfield;

                break;
        }
    }



    function saveInputfield($intInputfieldId, $intValue)
    {
        $intValue = intval($intValue);
        return $intValue;
    }


    function deleteContent($intEntryId, $intIputfieldId)
    {
        global $objDatabase;

        $objDeleteInputfield = $objDatabase->Execute("DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields WHERE `entry_id`='".intval($intEntryId)."' AND  `field_id`='".intval($intIputfieldId)."'");

        if($objDeleteEntry !== false) {
            return true;
        } else {
            return false;
        }
    }



    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus)
    {
        global $objDatabase, $_ARRAYLANG;

		$intId = intval($arrInputfield['id']);
        $objInputfieldValue = $objDatabase->Execute("
            SELECT
                `name`
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
            LEFT JOIN
            	".DBPREFIX."lib_country
            ON
            	".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields.value = ".DBPREFIX."lib_country.id
            WHERE
                field_id=".$intId."
            AND
                entry_id=".$intEntryId."
            LIMIT 1
        ");


        $strValue = strip_tags(htmlspecialchars($objInputfieldValue->fields['name'], ENT_QUOTES, CONTREXX_CHARSET));

        if(!empty($strValue)) {
            $arrContent['TXT_'.$this->moduleLangVar.'_INPUTFIELD_NAME'] = $_ARRAYLANG['TXT_'.$this->moduleLangVar.'_INPUTFIELD_TYPE_COUNTRY'];
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_VALUE'] = $strValue;
        } else {
            $arrContent = null;
        }

        return $arrContent;
    }


    function getJavascriptCheck()
    {
        $strJavascriptCheck = <<<EOF
EOF;
        return $strJavascriptCheck;
    }


    function getFormOnSubmit($intInputfieldId)
    {
        return null;
    }
}
