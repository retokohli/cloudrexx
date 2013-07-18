<?php
if (strpos(dirname(__FILE__), 'customizing') === false) {
    $contrexx_path = dirname(dirname(dirname(__FILE__)));
} else {
    // this files resides within the customizing directory, therefore we'll have to strip
    // out one directory more than usually
    $contrexx_path = dirname(dirname(dirname(dirname(__FILE__))));
}

require_once($contrexx_path . '/init.php');
init('minimal');

$sessionObj = new cmsSession();
$sessionObj->cmsSessionStatusUpdate('backend');
$CSRF = '&'.CSRF::key().'='.CSRF::code();


$langId = !empty($_GET['langId']) ? $_GET['langId'] : null;

//'&' must not be htmlentities, used in javascript
$defaultBrowser   = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=fileBrowser&standalone=true&langId='.$langId.$CSRF;
$linkBrowser      = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=fileBrowser&standalone=true&langId='.$langId.'&type=webpages'.$CSRF;

$defaultTemplateFilePath = substr(\Env::get('ClassLoader')->getFilePath('/lib/ckeditor/plugins/templates/templates/default.js'), strlen(ASCMS_PATH));


?>
CKEDITOR.editorConfig = function( config )
{
    config.skin = 'moono';

    config.height = 307;
    config.uiColor = '#ececec';

    config.forcePasteAsPlainText = false;
    config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;
    config.startupOutlineBlocks = true;
    config.allowedContent = true;

    config.tabSpaces = 4;

    config.filebrowserBrowseUrl      = CKEDITOR.getUrl('<?php echo $linkBrowser; ?>');
    config.filebrowserImageBrowseUrl = CKEDITOR.getUrl('<?php echo $defaultBrowser; ?>');
    config.filebrowserFlashBrowseUrl = CKEDITOR.getUrl('<?php echo $defaultBrowser; ?>');

    config.templates_files = [ '<?php echo $defaultTemplateFilePath; ?>' ];

    config.toolbar_Full = config.toolbar_Small = [
        ['Source','-','NewPage','Templates'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent', 'Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Anchor'],
        ['Image','Flash','Table','HorizontalRule','SpecialChar'],
        ['Format'],
        ['TextColor','BGColor'],
        ['ShowBlocks'],
        ['Maximize'],
        ['Div','CreateDiv']
    ];

    config.toolbar_BBCode = [
        ['Source','-','NewPage'],
        ['Undo','Redo','-','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','Link','Unlink','SpecialChar'],
    ];

    config.toolbar_FrontendEditingContent = [
        ['Publish','Save'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'],
        ['Undo','Redo','-','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent', 'Blockquote'],
        '/',
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Anchor'],
        ['Image','Flash','Table','HorizontalRule','SpecialChar'],
        ['Format'],
        ['TextColor','BGColor'],
        ['ShowBlocks']
    ];

    config.toolbar_FrontendEditingTitle = [
        ['Publish','Save'],
        ['Cut','Copy','Paste','-','Scayt'],
        ['Undo','Redo']
    ];
};
