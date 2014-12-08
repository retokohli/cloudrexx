<?php
header("content-type: application/javascript");
if (strpos(dirname(__FILE__), 'customizing') === false) {
    $contrexx_path = dirname(dirname(dirname(__FILE__)));
} else {
    // this files resides within the customizing directory, therefore we'll have to strip
    // out one directory more than usually
    $contrexx_path = dirname(dirname(dirname(dirname(__FILE__))));
}

require_once($contrexx_path . '/core/Core/init.php');
init('minimal');

$sessionObj = \cmsSession::getInstance();
$_SESSION->cmsSessionStatusUpdate('backend');
$CSRF = '&'.\Cx\Core\Csrf\Controller\Csrf::key().'='.\Cx\Core\Csrf\Controller\Csrf::code();


$langId = !empty($_GET['langId']) ? $_GET['langId'] : null;

//'&' must not be htmlentities, used in javascript
$defaultBrowser   = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=FileBrowser&standalone=true&langId='.$langId.$CSRF;
$linkBrowser      = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=FileBrowser&standalone=true&langId='.$langId.'&type=webpages'.$CSRF;

$defaultTemplateFilePath = substr(\Env::get('ClassLoader')->getFilePath('/lib/ckeditor/plugins/templates/templates/default.js'), strlen(ASCMS_PATH));


?>
CKEDITOR.scriptLoader.load( '/core_modules/MediaBrowser/View/Script/ckeditor-mediabrowser.js' );
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
    
    config.ignoreEmptyParagraph = false;
    config.protectedSource.push(/<i[^>]*><\/i>/g);
    config.protectedSource.push(/<span[^>]*><\/span>/g);
    config.protectedSource.push(/<a[^>]*><\/a>/g);

    config.ignoreEmptyParagraph = false;
    config.protectedSource.push(/<i[^>]*><\/i>/g);
    config.protectedSource.push(/<span[^>]*><\/span>/g);
    config.protectedSource.push(/<a[^>]*><\/a>/g);

    config.tabSpaces = 4;
    config.baseHref = 'http://<?php echo $_CONFIG['domainUrl'] . ASCMS_PATH_OFFSET; ?>/';

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
