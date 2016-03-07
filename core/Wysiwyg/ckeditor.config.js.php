<?php
$contrexx_config_path = dirname(dirname(dirname(__FILE__))).'/config/';

require_once($contrexx_config_path.'settings.php');
require_once($contrexx_config_path.'configuration.php');


require_once ASCMS_LIBRARY_PATH.'/DBG.php';
require_once ASCMS_CORE_PATH.'/settings.class.php';
require_once ASCMS_CORE_PATH.'/API.php';
require_once ASCMS_CORE_PATH.'/validator.inc.php';
require_once ASCMS_LIBRARY_PATH.'/CSRF.php';
require_once ASCMS_CORE_PATH.'/Html.class.php';

$strErrMessage = '';
$objDatabase = getDatabaseObject($strErrMessage);
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

$defaultTemplateFilePath = substr( ASCMS_LIBRARY_PATH .'/ckeditor/plugins/templates/templates/default.js', strlen(ASCMS_PATH));


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

    config.filebrowserBrowseUrl      = CKEDITOR.getUrl('<?php echo $linkBrowser; ?>');
    config.filebrowserImageBrowseUrl = CKEDITOR.getUrl('<?php echo $defaultBrowser; ?>');
    config.filebrowserFlashBrowseUrl = CKEDITOR.getUrl('<?php echo $defaultBrowser; ?>');

    config.templates_files = [ '<?php echo $defaultTemplateFilePath; ?>' ];

    config.toolbar_Default = [
        ['Source','-','NewPage','Preview','-','Templates'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker'],
        ['Undo','Redo','-','Replace','-','SelectAll','RemoveFormat'],
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent', 'Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Anchor'],
        ['Image','Flash','Table','HorizontalRule','SpecialChar'],
        ['Format'],
        ['TextColor','BGColor'],
        ['ShowBlocks'],
        ['Maximize']
    ]

    config.toolbar_News = [
        ['NewPage','Preview'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker'],
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
    config.extraPlugins = 'codemirror';
};

if (<?php
        if (\FWUser::getFWUserObject()->objUser->login()) {
            if (\FWUser::getFWUserObject()->objUser->getAdminStatus()) {
                echo 0;
            } else {
                $arrAssociatedGroupIds = \FWUser::getFWUserObject()->objUser->getAssociatedGroupIds();
                foreach ($arrAssociatedGroupIds as $groupId) {
                    $objGroup = \FWUser::getFWUserObject()->objGroup->getGroup($groupId);
                    if ($objGroup) {
                        if ($objGroup->getType() == 'backend') {
                            $isBackendGroup = true;
                            break;
                        }
                    }
                }
                if ($isBackendGroup) {
                    echo 0;
                } else {
                    echo 1;
                }
            }
        } else {
            echo 1;
        }
    ?>) {
    CKEDITOR.on('dialogDefinition', function(ev) {
        var dialogName       = ev.data.name;
        var dialogDefinition = ev.data.definition;

        if (dialogName == 'link') {
            dialogDefinition.getContents('info').remove('browse');
        }

        if (dialogName == 'image') {
            dialogDefinition.getContents('info').remove('browse');
            dialogDefinition.getContents('Link').remove('browse');
        }

        if (dialogName == 'flash') {
            dialogDefinition.getContents('info').remove('browse');
        }
    });
}