<?php
/**
 * Media  Directory Inputfield Relation Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfields/inputfield.interface.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';

class mediaDirectoryInputfieldRelation_group extends mediaDirectoryLibrary implements inputfield
{
    public $arrPlaceholders = array('TXT_MEDIADIR_INPUTFIELD_NAME','MEDIADIR_INPUTFIELD_VALUE');



    /**
     * Constructor
     */
    function __construct()
    {
        parent::getFrontendLanguages();
        parent::getSettings();
    }



    function getInputfield($intView, $arrInputfield, $intEntryId=null)
    {
        global $objDatabase, $_LANGID, $objInit, $_ARRAYLANG;

        $intId = intval($arrInputfield['id']);


        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                $this->arrFormOnSubmit['asdasd'] = $this->moduleName.'Inputfield['.$intId.'][]';
                
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
                    $strValue = htmlspecialchars($objInputfieldValue->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
                } else {
                    $strValue = null;
                }
                
                $strFormType = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];
                $arrSelectorOptions = array();
                $arrValue = explode(",",$strValue);
                
                $objEntries = new mediaDirectoryEntry();
                $objEntries->getEntries();
                
                foreach($objEntries->arrEntries as $intKey => $arrEntry) {
                	if(in_array($intKey,$arrValue)) {
                		$arrSelectorOptions['selected'][] = '<option  value="'.$arrEntry['entryId'].'">'.$arrEntry['entryFields'][0].'</option>';
                    } else {
                        $arrSelectorOptions['not_selected'][] = '<option  value="'.$arrEntry['entryId'].'">'.$arrEntry['entryFields'][0].'</option>';
                	}
                }
                
                asort($arrSelectorOptions['selected']);
                asort($arrSelectorOptions['not_selected']);
                
                $strSelectorSelected = join("", $arrSelectorOptions['selected']);
                $strSelectorNotSelected = join("", $arrSelectorOptions['not_selected']);
                
                $strInputfield .= <<<EOF
<script language="JavaScript" type="text/javascript">
/* <![CDATA[ */

function searchElement(elementId, term){
    elmSelector = document.getElementById(elementId);

    var pattern = term.toLowerCase()
    var reg = new RegExp(pattern);
    
    for (i = 0; i < elmSelector.length; ++i) {
        var text = elmSelector.options[i].text.toLowerCase()
    
        if (text.match(reg)) {
            elmSelector.options[i].selected = true;
        } else {
            elmSelector.options[i].selected = false;
        }
    }
}

/* ]]> */
</script>
EOF;
                
                $strInputfield .= '<div class="'.$this->moduleName.'Selector" style="float: left; height: auto !important;">';
                $strInputfield .= '<div class="'.$this->moduleName.'SelectorLeft" style="float: left; height: auto !important;"><select id="'.$this->moduleName.'Inputfield_deselected_'.$intId.'" name="'.$this->moduleName.'Inputfield[deselected_'.$intId.'][]" size="12" multiple="multiple" style="width: 180px;">';
                $strInputfield .= $strSelectorNotSelected;
                $strInputfield .= '</select><br /><input class="'.$this->moduleName.'SelectorSearch" type="text" onclick="this.value=\'\';" onkeyup="searchElement(\''.$this->moduleName.'Inputfield_deselected_'.$intId.'\', this.value);" value="Suchbegriff..."  style="width: 178px;"/></div>';
                $strInputfield .= '<div class="'.$this->moduleName.'SelectorCenter" style="float: left; height: 100px; padding: 60px 10px 0px 10px;">';
                $strInputfield .= '<input style="width: 40px; min-width: 40px;" value=" &gt;&gt; " name="addElement" onclick="moveElement(document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_deselected_'.$intId.'\'],document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_'.$intId.'\'],addElement,removeElement);" type="button">';
                $strInputfield .= '<br />';
                $strInputfield .= '<input style="width: 40px; min-width: 40px;" value=" &lt;&lt; " name="removeElement" onclick="moveElement(document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_'.$intId.'\'],document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_deselected_'.$intId.'\'],removeElement,addElement);" type="button">';
                $strInputfield .= '</div>';
                $strInputfield .= '<div class="'.$this->moduleName.'SelectorRight" style="float: left; height: auto !important;"><select id="'.$this->moduleName.'Inputfield_'.$intId.'" name="'.$this->moduleName.'Inputfield['.$intId.'][]" size="12" multiple="multiple" style="width: 180px;">';
                $strInputfield .= $strSelectorSelected;
                $strInputfield .= '</select><br /><input class="'.$this->moduleName.'SelectorNew" style="float: right;" type="button" value="'.$_ARRAYLANG['TXT_MEDIADIR_ADD_ENTRY'].'"/></div>';
                $strInputfield .= '</div>';
                
                return $strInputfield;

                break;
            case 2:
                //search View
                
               return $strInputfield;

               break;
        }
    }



    function saveInputfield($intInputfieldId, $intValue)
    {
        $intValue = intval($intValue);
        
        $intValue = null;
        
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
        global $objDatabase, $_LANGID;

        $intId = intval($arrInputfield['id']);
        $objEntryDefaultLang = $objDatabase->Execute("SELECT `lang_id` FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_entries WHERE id=".intval($intEntryId)." LIMIT 1");
        $intEntryDefaultLang = intval($objEntryDefaultLang->fields['lang_id']);

        if($this->arrSettings['settingsTranslationStatus'] == 1) {
	        if(in_array($_LANGID, $arrTranslationStatus)) {
	        	$intLangId = $_LANGID;
	        } else {
	        	$intLangId = $intEntryDefaultLang;
	        }
        } else {
        	$intLangId = $_LANGID;
        }

       $objInputfield = $objDatabase->Execute("
          SELECT
             `value`
          FROM
             ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
          WHERE
             ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields.lang_id = ".$_LANGID."
          AND
             ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields.field_id = '".$intId."'");

       	$intEntryId = intval($objInputfield->fields['value']);

		$objEntry = new mediaDirectoryEntry;
		$objEntry->getEntries($intEntryId);
		$strEntryValue = $objEntry->arrEntries[$intEntryId]['entryFields'][0];

        if(!empty($strEntryValue)) {
            $arrContent['TXT_'.$this->moduleLangVar.'_INPUTFIELD_NAME'] = htmlspecialchars($arrInputfield['name'][0], ENT_QUOTES, CONTREXX_CHARSET);
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_VALUE'] = '<a href="index.php?section='.$this->moduleName.'&cmd=detail&amp;eid='.$intEntryId.'">'.$strEntryValue.'</a>';

        } else {
            $arrContent = null;
        }

        return $arrContent;
    }


    function getJavascriptCheck()
    {
        return NULL;
    }
}