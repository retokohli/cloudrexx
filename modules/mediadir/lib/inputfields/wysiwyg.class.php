<?php

/**
 * Media  Directory Inputfield Textarea Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_PATH . '/wysiwyg.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfields/inputfield.interface.php';

/**
 * Media  Directory Inputfield Textarea Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_marketplace
 * @todo        Edit PHP DocBlocks!
 */
class mediaDirectoryInputfieldWysiwyg extends mediaDirectoryLibrary implements inputfield
{
    public $arrPlaceholders = array('TXT_MARKETPLACE_INPUTFIELD_NAME','MARKETPLACE_INPUTFIELD_VALUE');


    /**
     * Constructor
     */
    function __construct()
    {
        parent::getFrontendLanguages();
    }

    
    function getInputfield($intView, $arrInputfield, $intEntryId=null)
    {
        global $objDatabase, $_LANGID, $objInit, $wysiwygEditor, $FCKeditorBasePath, $_ARRAYLANG;;      

        $intId = intval($arrInputfield['id']);
        $wysiwygEditor = "FCKeditor";
        $FCKeditorBasePath = "/editor/fckeditor/";

        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                if (isset($intEntryId) && $intEntryId != 0) {
                    $objInputfieldValue = $objDatabase->Execute("
                        SELECT
                            `value`,
                            `lang_id`
                        FROM
                            ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
                        WHERE
                            field_id=".$intId."
                        AND
                            entry_id=".$intEntryId."
                    ");

                    if ($objInputfieldValue !== false) {
                        while (!$objInputfieldValue->EOF) {
                            $arrValue[intval($objInputfieldValue->fields['lang_id'])] = $objInputfieldValue->fields['value'];
                            $objInputfieldValue->MoveNext();
                        }
// TODO: What if the current language value is missing?
// The empty string is an inconvenient default!
//                        $arrValue[0] = $arrValue[$_LANGID];
                        $arrValue[0] = (isset($arrValue[$_LANGID]) ? $arrValue[$_LANGID] : '');
                    }
                } else {
                    $arrValue = null;
                }

                if (empty($arrValue)) {
// TODO: Bogus line
//                    $strDefaultValue = empty($strDefaultValue) ? $arrInputfield['default_value'][0] : $strDefaultValue;
                    foreach ($arrInputfield['default_value'] as $intLangKey => $strDefaultValue) {
                       if (substr($strDefaultValue,0,2) == '[[') {
                            $objPlaceholder = new mediaDirectoryPlaceholder();
                            $arrValue[$intLangKey] = $objPlaceholder->getPlaceholder($strDefaultValue);
                        } else {
                            $arrValue[$intLangKey] = $strDefaultValue;
                        }
                    }
                }

                /*$arrInfoValue = array();
                if (!empty($arrInputfield['info'][0])){
                    $arrInfoValue[0] = 'title="'.$arrInputfield['info'][0].'"';
                    foreach ($arrInputfield['info'] as $intLangKey => $strInfoValue) {
                        $strInfoClass = 'mediadirInputfieldHint';
                        $arrInfoValue[$intLangKey] = empty($strInfoValue) ? 'title="'.$arrInputfield['info'][0].'"' : 'title="'.$strInfoValue.'"';
                    }
                } else {
                    $arrInfoValue = null;
                    $strInfoClass = '';
                }*/

                if ($objInit->mode == 'backend') {
                    //$strInputfield = '<span id="'.$this->moduleName.'Inputfield_'.$intId.'_Minimized" style="display: block;"><textarea name="'.$this->moduleName.'Inputfield['.$intId.'][0]" id="'.$this->moduleName.'Inputfield_'.$intId.'_0" style="width: 300px; height: 60px;" onfocus="this.select();" />'.$arrValue[0].'</textarea>&nbsp;<a href="javascript:ExpandMinimize(\''.$intId.'\');">'.$_ARRAYLANG['TXT_MEDIADIR_MORE'].'&nbsp;&raquo;</a></span>';
                    $strInputfield = '<span id="'.$this->moduleName.'Inputfield_'.$intId.'_Minimized" style="display: block;">'.get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.'][0]', $arrValue[0], 'mediadir').'&nbsp;<a href="javascript:ExpandMinimize(\''.$intId.'\');">'.$_ARRAYLANG['TXT_MEDIADIR_MORE'].'&nbsp;&raquo;</a></span>';

                    $strInputfield .= '<span id="'.$this->moduleName.'Inputfield_'.$intId.'_Expanded" style="display: none;">';
                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                        $intLangId = $arrLang['id'];

                        if (($key+1) == count($this->arrFrontendLanguages)) {
                            $minimize = "&nbsp;<a href=\"javascript:ExpandMinimize('".$intId."');\">&laquo;&nbsp;".$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE']."</a>";
                        } else {
                            $minimize = "";
                        }  
                        $strInputfield .=  get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.']['.$intLangId.']', $arrValue[$intLangId], 'mediadir').'&nbsp;'.$arrLang['name'].'<a href="javascript:ExpandMinimize(\''.$intId.'\');">&nbsp;'.$minimize.'</a><br />';  
                    }
                    $strInputfield .= '<textarea name="'.$this->moduleName.'Inputfield['.$intId.'][old]" style="display: none;" onfocus="this.select();" />'.$arrValue[0].'</textarea>';
                    $strInputfield .= '</span>';
                } else {
                    //$strInputfield = '<textarea name="'.$this->moduleName.'Inputfield['.$intId.'][0]" id="'.$this->moduleName.'Inputfield_'.$intId.'_0" class="'.$this->moduleName.'InputfieldTextarea" onfocus="this.select();" />'.$arrValue[0].'</textarea>';
                    //$strInputfield = '<span id="'.$this->moduleName.'Inputfield_'.$intId.'_Minimized" style="display: block; float: left;" class="'.$this->moduleName.'GroupMultilang"><textarea name="'.$this->moduleName.'Inputfield['.$intId.'][0]" id="'.$this->moduleName.'Inputfield_'.$intId.'_0" class="'.$this->moduleName.'InputfieldTextarea '.$strInfoClass.'" '.$arrInfoValue[0].' onfocus="this.select();" />'.$arrValue[0].'</textarea>&nbsp;<a href="javascript:ExpandMinimize(\''.$intId.'\');">'.$_ARRAYLANG['TXT_MEDIADIR_MORE'].'&nbsp;&raquo;</a></span>';
                    $strInputfield = '<span class="editorFix"><span id="'.$this->moduleName.'Inputfield_'.$intId.'_Minimized" style="display: block;" class="'.$this->moduleName.'GroupMultilang">'.get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.'][0]', $arrValue[0], 'mediadir').'&nbsp;<a href="javascript:ExpandMinimize(\''.$intId.'\');">'.$_ARRAYLANG['TXT_MEDIADIR_MORE'].'&nbsp;&raquo;</a></span>';

                    $strInputfield .= '<span id="'.$this->moduleName.'Inputfield_'.$intId.'_Expanded" style="display: none;" class="'.$this->moduleName.'GroupMultilang">';
                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                        $intLangId = $arrLang['id'];

                        if (($key+1) == count($this->arrFrontendLanguages)) {
                            $minimize = "&nbsp;<a href=\"javascript:ExpandMinimize('".$intId."');\">&laquo;&nbsp;".$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE']."</a>";
                        } else {
                            $minimize = "";
                        }

                        //$strInputfield .= '<textarea name="'.$this->moduleName.'Inputfield['.$intId.']['.$intLangId.']" id="'.$this->moduleName.'Inputfield_'.$intId.'_'.$intLangId.'" class="'.$this->moduleName.'InputfieldTextarea '.$strInfoClass.'" '.$arrInfoValue[$intLangId].' onfocus="this.select();" />'.$arrValue[$intLangId].'</textarea>&nbsp;'.$arrLang['name'].'<a href="javascript:ExpandMinimize(\''.$intId.'\');">&nbsp;'.$minimize.'</a><br />';
                        
                        $strInputfield .=  get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.']['.$intLangId.']', $arrValue[$intLangId], 'mediadir').'&nbsp;'.$arrLang['name'].'<a href="javascript:ExpandMinimize(\''.$intId.'\');">&nbsp;'.$minimize.'</a><br />';
                    }
                    $strInputfield .= '<textarea name="'.$this->moduleName.'Inputfield['.$intId.'][old]" style="display: none;" onfocus="this.select();" />'.$arrValue[0].'</textarea>';
                    $strInputfield .= '</span></span>';
                }


                return $strInputfield;
                break;
            case 2:
                //search View
                $strValue = $_GET[$intId];
                $strInputfield = '<input type="text" name="'.$intId.'" " class="'.$this->moduleName.'InputfieldSearch" value="'.$strValue.'" />';

                return $strInputfield;

                break;
        }
    }    
    

    function saveInputfield($intInputfieldId, $strValue)
    {
        global $objInit;

        /*if ($objInit->mode == 'backend') {
            $strValue = contrexx_addslashes($strValue);
        } else {
            //$strValue = strip_tags($strValue, )
            
            $strValue = $this->BBCodeToHTML(contrexx_stripslashes($strValue));
        }   */                                             
        $strValue = strip_tags(contrexx_input2raw($strValue), '<b><strong><em><u><br><ul><li><ol>');

        return $strValue;
    }


    function deleteContent($intEntryId, $intIputfieldId)
    {
        global $objDatabase;

        return (boolean)$objDatabase->Execute("
            DELETE FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
             WHERE `entry_id`='".intval($intEntryId)."'
               AND  `field_id`='".intval($intIputfieldId)."'");
    }



    function getContent($intEntryId, $arrInputfield, $arrTranslationStatus=array())
    {
        global $objDatabase, $_LANGID;

        $intId = intval($arrInputfield['id']);
        $objEntryDefaultLang = $objDatabase->Execute("SELECT `lang_id` FROM ".DBPREFIX."module_".$this->moduleTablePrefix."_entries WHERE id=".intval($intEntryId)." LIMIT 1");
        $intEntryDefaultLang = intval($objEntryDefaultLang->fields['lang_id']);

        if ($this->arrSettings['settingsTranslationStatus'] == 1) {
            if (in_array($_LANGID, $arrTranslationStatus)) {
                $intLangId = $_LANGID;
            } else {
                $intLangId = $intEntryDefaultLang;
            }
        } else {
            $intLangId = $_LANGID;
        }

        $objInputfieldValue = $objDatabase->Execute("
            SELECT
                `value`
            FROM
                ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
            WHERE
                field_id=".$intId."
            AND
                entry_id=".intval($intEntryId)."
            AND
                lang_id=".$intLangId."
            LIMIT 1
        ");

        if (empty($objInputfieldValue->fields['value'])) {
            $objInputfieldValue = $objDatabase->Execute("
                SELECT
                    `value`
                FROM
                    ".DBPREFIX."module_".$this->moduleTablePrefix."_rel_entry_inputfields
                WHERE
                    field_id=".$intId."
                AND
                    entry_id=".intval($intEntryId)."
                AND
                    lang_id=".intval($intEntryDefaultLang)."
                LIMIT 1
            ");
        }

        /*$intId = intval($arrInputfield['id']);
        $objInputfieldValue = $objDatabase->Execute("
            SELECT
                `value`
            FROM
                ".DBPREFIX."module_".self::$moduleTablePrefix."_rel_entry_inputfields
            WHERE
                field_id=".$intId."
            AND
                entry_id=".$intEntryId."
            LIMIT 1
        "); */

        $strValue = $objInputfieldValue->fields['value'];

        if (!empty($strValue)) {
            $arrContent['TXT_'.$this->moduleLangVar.'_INPUTFIELD_NAME'] = htmlspecialchars($arrInputfield['name'][0], ENT_QUOTES, CONTREXX_CHARSET);
            $arrContent[$this->moduleLangVar.'_INPUTFIELD_VALUE'] = $strValue;
        } else {
            $arrContent = null;
        }

        return $arrContent;
    }



    function getJavascriptCheck()
    {
        $strJavascriptCheck = <<<EOF

            case 'wysiwyg':
                break;
EOF;
        return $strJavascriptCheck;
    }



    function BBCodeToHTML($content){
        global $_ARRAYLANG;
        require_once ASCMS_LIBRARY_PATH.'/bbcode/stringparser_bbcode.class.php';
        $objBBCode = new StringParser_BBCode();
        $objBBCode->addFilter(STRINGPARSER_FILTER_PRE, array(&$this, 'convertlinebreaks')); //unify all linebreak variants from different systems
        $objBBCode->addFilter(STRINGPARSER_FILTER_PRE, array(&$this, 'convertlinks'));
//      $objBBCode->addFilter(STRINGPARSER_FILTER_POST, array(&$this, 'stripBBtags'));
        $objBBCode->addFilter(STRINGPARSER_FILTER_POST, array(&$this, 'removeDoubleEscapes'));
        $objBBCode->addParser(array ('block', 'inline', 'link', 'listitem'), 'htmlspecialchars');
        $objBBCode->addParser(array ('block', 'inline', 'link', 'listitem'), 'nl2br');
        $objBBCode->addParser('list', array(&$this, 'bbcode_stripcontents'));
        $objBBCode->addCode('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'), 'inline', array ('block', 'inline'), array ());
        $objBBCode->addCode('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'), 'inline', array ('block', 'inline'), array ());
        $objBBCode->addCode('u', 'simple_replace', null, array ('start_tag' => '<u>', 'end_tag' => '</u>'), 'inline', array ('block', 'inline'), array ());
        $objBBCode->addCode('s', 'simple_replace', null, array ('start_tag' => '<strike>', 'end_tag' => '</strike>'), 'inline', array ('block', 'inline'), array ());
        $objBBCode->addCode('url', 'usecontent?', array(&$this, 'do_bbcode_url'), array ('usecontent_param' => 'default'), 'inline', array ('listitem', 'block', 'inline'), array ('link'));
        $objBBCode->addCode('img', 'usecontent', array(&$this, 'do_bbcode_img'), array ('usecontent_param' => array('w', 'h')), 'image', array ('listitem', 'block', 'inline', 'link'), array ());
        $objBBCode->addCode('quote', 'callback_replace', array(&$this, 'do_bbcode_quote'), array('usecontent_param' => 'default'), 'block', array('block', 'inline'), array('list', 'listitem'));
        $objBBCode->addCode('code', 'usecontent', array(&$this, 'do_bbcode_code'), array('usecontent_param' => 'default'), 'block', array('block', 'inline'), array('list', 'listitem'));
        $objBBCode->addCode('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'), 'list', array ('block', 'listitem'), array ());
        $objBBCode->addCode('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'), 'listitem', array ('list'), array ());
        $objBBCode->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
        $objBBCode->setCodeFlag('*', 'paragraphs', true);
        $objBBCode->setCodeFlag('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
        $objBBCode->setCodeFlag('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
        $objBBCode->setCodeFlag('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

        $objBBCode->setOccurrenceType('img', 'image');
        $objBBCode->setMaxOccurrences('image', 5);

        $objBBCode->setRootParagraphHandling(false); //do not convert new lines to paragraphs, see stringparser_bbcode::setParagraphHandlingParameters();
        $content = $objBBCode->parse($content);

        return $content;
    }


    function getFormOnSubmit($intInputfieldId)
    {
        return null;
    }
}
