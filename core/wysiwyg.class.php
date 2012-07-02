<?php
/**
 * WYSIWYG editor interface
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Daeppen <thomas.daeppen@comvation.com>
 * @version     2.0.0
 * @package     contrexx
 * @subpackage  core
 */
//Security-Check
if (preg_match("#".$_SERVER['PHP_SELF']."#", __FILE__)) {
    Header("Location: ../index.php");
    die();
}


/**
 * WYSIWYG editor
 *
 * Gets the wysiwyg editor as a string
 * @version   1.0        initial version
 * @param string $name
 * @param string $value
 * @param string $mode
 * @return string
 */
function get_wysiwyg_editor($name, $value = '', $mode = '', $languageId = null, $absoluteURIs = false)
{
    if ($mode != 'html') {
        JS::activate('ckeditor');
        JS::activate('jquery');

        $loadBBCodePlugin = $mode == 'forum' ? 1 : 0;
        $arrCKEditorOptions = array(
            "customConfig: CKEDITOR.getUrl('config.contrexx.js.php?langId=".$languageId."&absoluteURIs=".$absoluteURIs."&bbcode=".$loadBBCodePlugin."')",
        );
        $onReady = array("
            CKEDITOR.replace('".$name."', {
                %s
            });
        ");

        switch ($mode) {
            case 'forum':
                $arrCKEditorOptions[] = "width: '96%'";
                $arrCKEditorOptions[] = "height: 200";
                $arrCKEditorOptions[] = "extraPlugins: 'bbcode'";
                $arrCKEditorOptions[] = "toolbar: 'BBCode'";
                $arrCKEditorOptions[] = "resize_minWidth: '96%'";
                $arrCKEditorOptions[] = "resize_maxWidth: '96%'";
                $onReady[] = '
                    CKEDITOR.on("instanceReady", function(event){
                        $J("#cke_message").css({marginLeft:"0px"});
                    });
                ';
                break;

            case 'shop':
                $arrCKEditorOptions[] = "width: '100%'";
                $arrCKEditorOptions[] = "height: 200";
                $arrCKEditorOptions[] = "toolbar: 'Default'";
                break;

            case 'news':
                $arrCKEditorOptions[] = "width: '100%'";
                $arrCKEditorOptions[] = "height: 350";
                $arrCKEditorOptions[] = "toolbar: 'News'";
                break;

            case 'teaser':
                $arrCKEditorOptions[] = "width: '100%'";
                $arrCKEditorOptions[] = "height: 100";
                $arrCKEditorOptions[] = "toolbar: 'News'";
                break;

            case 'fullpage':
                $arrCKEditorOptions[] = "width: '100%'";
                $arrCKEditorOptions[] = "height: 450";
                $arrCKEditorOptions[] = "toolbar: 'Default'";
                $arrCKEditorOptions[] = "fullPage: true";
                break;

            case 'frontendEditing':
                $arrCKEditorOptions[] = "width: '100%'";
                $arrCKEditorOptions[] = "height: 400";
                $arrCKEditorOptions[] = "toolbar: 'Default'";
                break;

            default:
                $arrCKEditorOptions[] = "width: '100%'";
                $arrCKEditorOptions[] = "height: 450";
                $arrCKEditorOptions[] = "toolbar: 'Default'";
                break;
        }

        $onReady[0] = sprintf($onReady[0], implode(",\n", $arrCKEditorOptions));

        JS::registerCode('
            $J(function(){
                '.implode("\n", $onReady).'
            });
        ');
    }

    return '<textarea name="'.$name.'" style="width: 100%; height: 450px;">'.$value.'</textarea>';
}
?>
