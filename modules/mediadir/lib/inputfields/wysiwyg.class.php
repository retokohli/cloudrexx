<?php
/**
 * Media  Directory Inputfield Textarea Class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_CORE_PATH . '/wysiwyg.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfields/inputfield.interface.php';

class mediaDirectoryInputfieldWysiwyg extends mediaDirectoryLibrary implements inputfield
{
    public $arrPlaceholders = array('TXT_MEDIADIR_INPUTFIELD_NAME','MEDIADIR_INPUTFIELD_VALUE');


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

        $wysiwygEditor = "FCKeditor";
        $FCKeditorBasePath = "/editor/fckeditor/";

        switch ($intView) {
            default:
            case 1:
                //modify (add/edit) View
                $intId = intval($arrInputfield['id']);

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
                    ");

                    if ($objInputfieldValue !== false) {
                        while (!$objInputfieldValue->EOF) {
                            $arrValue[intval($objInputfieldValue->fields['lang_id'])] = $objInputfieldValue->fields['value'];
                            $objInputfieldValue->MoveNext();
                        }
                    }
                } else {
                    $arrValue = null;
                }

                if(empty($strValue)) {
                    $arrValue = empty($arrInputfield['default_value'][$_LANGID]) ? $arrInputfield['default_value'][0] : $arrInputfield['default_value'][$_LANGID];
                }

                if($objInit->mode == 'backend') {
                    $strInputfield = '<div id="'.$this->moduleName.'Inputfield_'.$intId.'_Minimized" style="display: block;">'.get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.'][0]', $arrValue[0], 'mediadir_admin').'&nbsp;<a href="javascript:ExpandMinimize(\''.$intId.'\');">'.$_ARRAYLANG['TXT_MEDIADIR_MORE'].'&nbsp;&raquo;</a></div>';

                    $strInputfield .= '<div id="'.$this->moduleName.'Inputfield_'.$intId.'_Expanded" style="display: none;">';
                    foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                        $intLangId = $arrLang['id'];

                        if(($key+1) == count($this->arrFrontendLanguages)) {
                            $minimize = "&nbsp;<a href=\"javascript:ExpandMinimize('".$intId."');\">&laquo;&nbsp;".$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE']."</a>";
                        } else {
                            $minimize = "";
                        }

                        $strInputfield .= get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.']['.$intLangId.']', $arrValue[$intLangId], 'mediadir_admin').'&nbsp;'.$arrLang['name'].'<a href="javascript:ExpandMinimize(\''.$intId.'\');">&nbsp;'.$minimize.'</a><br />';
                    }
                    $strInputfield .= '</div>';
                } else {
                    //$strInputfield =  get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.']', $strValue, 'news');
                    $strInputfield =  get_wysiwyg_editor($this->moduleName.'Inputfield['.$intId.'][0]', $arrValue[0], 'mediadir');
                }


                return $strInputfield;
                break;
            case 2:
                //search View
                break;
        }
    }



    function saveInputfield($intInputfieldId, $strValue)
    {
        global $objInit;

        if($objInit->mode == 'backend') {
            $strValue = contrexx_addslashes($strValue);
        } else {
            $strValue = $this->BBCodeToHTML(contrexx_stripslashes($strValue));
        }


        return $strValue;
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



    function getContent($intEntryId, $arrInputfield)
    {
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

        $strValue = $objInputfieldValue->fields['value'];

        if(!empty($strValue)) {
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
}