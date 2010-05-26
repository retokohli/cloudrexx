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
                    $strValue = intval($objInputfieldValue->fields['value']);
                } else {
                    $strValue = null;
                }
                
                $strFormType = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];
                $arrSelectorOptions = array();
                
                for($i=0;$i <= 20; $i++) {
                    $arrSelectorOptions['not_selected'] .= '<option  value="'.$i.'">Test '.$i.'</option>';
                }
                
                $strInputfield .= <<<EOF
<script language="JavaScript" type="text/javascript">
/* <![CDATA[ */

function sayHallo(){
    searchElement("Hallo Chrigu");
}

function searchElement(term){
    alert(term);
}

/* ]]> */
</script>
EOF;
                
                $strInputfield .= '<div class="'.$this->moduleName.'Selector" style="float: left; height: auto !important;">';
                $strInputfield .= '<div class="'.$this->moduleName.'SelectorLeft" style="float: left; height: auto !important;"><select id="'.$this->moduleName.'Inputfield_deselected_'.$intId.'" name="'.$this->moduleName.'Inputfield[deselected_'.$intId.'][]" size="12" multiple="multiple" style="width: 180px;">';
                $strInputfield .= $arrSelectorOptions['not_selected'];
                $strInputfield .= '</select><br /><input class="'.$this->moduleName.'SelectorSearch" type="text" onclick="this.value=\'\';" onkeyup="searchElement(this.value);" value="Suchbegriff..."  style="width: 178px;"/></div>';
                $strInputfield .= '<div class="'.$this->moduleName.'SelectorCenter" style="float: left; height: 100px; padding: 60px 10px 0px 10px;">';
                $strInputfield .= '<input style="width: 40px; min-width: 40px;" value=" &gt;&gt; " name="addElement" onclick="moveElement(document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_deselected_'.$intId.'\'],document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_'.$intId.'\'],addElement,removeElement);" type="button">';
                $strInputfield .= '<br />';
                $strInputfield .= '<input style="width: 40px; min-width: 40px;" value=" &lt;&lt; " name="removeElement" onclick="moveElement(document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_'.$intId.'\'],document.entryModfyForm.elements[\''.$this->moduleName.'Inputfield_deselected_'.$intId.'\'],removeElement,addElement);" type="button">';
                $strInputfield .= '</div>';
                $strInputfield .= '<div class="'.$this->moduleName.'SelectorRight" style="float: left; height: auto !important;"><select id="'.$this->moduleName.'Inputfield_'.$intId.'" name="'.$this->moduleName.'Inputfield['.$intId.'][]" size="12" multiple="multiple" style="width: 180px;">';
                $strInputfield .= $arrSelectorOptions['selected'];
                $strInputfield .= '</select></div>';
                $strInputfield .= '</div>';
                
                $strInputfield .= <<<EOF
<script language="JavaScript" type="text/javascript">
/* <![CDATA[ */

sayHallo();

/* ]]> */
</script>
EOF;
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