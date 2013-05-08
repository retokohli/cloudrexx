<?php
if (strpos(dirname(__FILE__), 'customizing') === false) {
    $contrexx_config_path = dirname(dirname(dirname(__FILE__))).'/config/';
} else {
    // this files resides within the customizing directory, therefore we'll have to strip
    // out one directory more than usually
    $contrexx_config_path = dirname(dirname(dirname(dirname(__FILE__)))).'/config/';
}
require_once($contrexx_config_path.'settings.php');
require_once($contrexx_config_path.'configuration.php');
require_once(ASCMS_CORE_PATH.'/ClassLoader/ClassLoader.class.php');

$customizing = null;
if (isset($_CONFIG['useCustomizings']) && $_CONFIG['useCustomizings'] == 'on') {
// TODO: webinstaller check: has ASCMS_CUSTOMIZING_PATH already been defined in the installation process?
    $customizing = ASCMS_CUSTOMIZING_PATH;
}

$cl = new \Cx\Core\ClassLoader\ClassLoader(ASCMS_DOCUMENT_ROOT, true, $customizing);
/**
 * Environment repository
 */
$cl->loadFile(ASCMS_CORE_PATH.'/Env.class.php');
\Env::set('ClassLoader', $cl);
\Env::set('ftpConfig', $_FTPCONFIG);

require_once(ASCMS_FRAMEWORK_PATH.'/DBG/DBG.php');
require_once(ASCMS_CORE_PATH.'/settings.class.php');
$cl->loadFile(ASCMS_CORE_PATH.'/API.php');
require_once(ASCMS_CORE_PATH.'/validator.inc.php');
require_once(ASCMS_LIBRARY_PATH.'/CSRF.php');
require_once(ASCMS_CORE_PATH.'/Html.class.php');

$db = new \Cx\Core\Db\Db();
$objDatabase = $db->getAdoDb();
\Env::set('db', $objDatabase);
$objSettings = new settingsManager();
$objInit = new InitCMS('backend');
$sessionObj = new cmsSession();
$sessionObj->cmsSessionStatusUpdate('backend');
$CSRF = '&'.CSRF::key().'='.CSRF::code();


$langId = !empty($_GET['langId']) ? $_GET['langId'] : null;
$absoluteURIs = !empty($_GET['absoluteURIs']) ? $_GET['absoluteURIs'] : null;

//'&' must not be htmlentities, used in javascript
$defaultBrowser   = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=fileBrowser&standalone=true&langId='.$langId
                   .'&absoluteURIs='.$absoluteURIs.$CSRF;
$linkBrowser      = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=fileBrowser&standalone=true&langId='.$langId
                   .'&absoluteURIs='.$absoluteURIs.'&type=webpages'.$CSRF;

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

    config.tabSpaces = 4;

    config.filebrowserBrowseUrl      = CKEDITOR.getUrl('<?php echo $linkBrowser; ?>');
    config.filebrowserImageBrowseUrl = CKEDITOR.getUrl('<?php echo $defaultBrowser; ?>');
    config.filebrowserFlashBrowseUrl = CKEDITOR.getUrl('<?php echo $defaultBrowser; ?>');

    config.templates_files = [ '<?php echo $defaultTemplateFilePath; ?>' ];

    config.toolbar_Full = [
        ['Source','-','Templates'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Scayt'],
        ['Undo','Redo','-','Replace','-','SelectAll','RemoveFormat'],
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

    config.toolbar_Small = [
        ['Preview'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','Scayt'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
        ['OrderedList','UnorderedList','-','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Table','HorizontalRule','Smiley','SpecialChar']
    ];

    config.toolbar_BBCode = [
        ['Source'],
        ['Bold','Italic','Underline','StrikeThrough','-','Link','Unlink', 'SpecialChar'],
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
