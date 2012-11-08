<?php
namespace Cx\Core\Wysiwyg;

/**
 * Wysiqyg class
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Daeppen <thomas.daeppen@comvation.com>
 * @author      Michael Räss <michael.raess@comvation.com>
 * @version     2.0.0
 * @package     contrexx
 * @subpackage  core
 */

class Wysiwyg
{
    private $name;
    private $value;
    private $mode;
    private $languageId;
    private $absoluteUris;
    
    public function __construct($name, $value = '', $mode = '', $languageId = null, $absoluteUris = false)
    {
        $this->name         = $name;
        $this->value        = $value;
        $this->mode         = $mode;
        $this->languageId   = $languageId;
        $this->absoluteUris = $absoluteUris;
    }
    
    public function __toString()
    {
        if ($this->mode != 'html') {
            \JS::activate('ckeditor');
            \JS::activate('jquery');
    
            $loadBBCodePlugin = $this->mode == 'forum' ? 1 : 0;
            $configPath = ASCMS_PATH_OFFSET.substr(\Env::get('ClassLoader')->getFilePath(ASCMS_CORE_PATH.'/Wysiwyg/ckeditor.config.js.php'), strlen(ASCMS_DOCUMENT_ROOT));
            $arrCKEditorOptions = array(
                "customConfig: CKEDITOR.getUrl('".$configPath."?langId=".$this->languageId."&absoluteURIs=".$this->absoluteUris."&bbcode=".$loadBBCodePlugin."')",
            );
            $onReady = array("
                CKEDITOR.replace('".$this->name."', {
                    %s
                });
            ");
    
            switch ($this->mode) {
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
    
            \JS::registerCode('
                $J(function(){
                    '.implode("\n", $onReady).'
                });
            ');
        }
    
        return '<textarea name="'.$this->name.'" style="width: 100%; height: 450px;">'.$this->value.'</textarea>';
    }
}
