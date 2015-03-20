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
$cx = init('minimal');

$sessionObj = \cmsSession::getInstance();
$_SESSION->cmsSessionStatusUpdate('backend');
$CSRF = '&'.\Cx\Core\Csrf\Controller\Csrf::key().'='.\Cx\Core\Csrf\Controller\Csrf::code();


$langId = !empty($_GET['langId']) ? $_GET['langId'] : null;
$pageId = !empty($_GET['pageId']) ? $_GET['pageId'] : null;

//'&' must not be htmlentities, used in javascript
$defaultBrowser   = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=FileBrowser&standalone=true&langId='.$langId.$CSRF;
$linkBrowser      = ASCMS_PATH_OFFSET . ASCMS_BACKEND_PATH.'/'.CONTREXX_DIRECTORY_INDEX
                   .'?cmd=FileBrowser&standalone=true&langId='.$langId.'&type=webpages'.$CSRF;

//$defaultTemplateFilePath = substr(\Env::get('ClassLoader')->getFilePath('/lib/ckeditor/plugins/templates/templates/default.js'), strlen(ASCMS_PATH));
//\DBG::activate();

//find the right css files and put it into the wysiwyg
$em = $cx->getDb()->getEntityManager();
$componentRepo = $em->getRepository('Cx\Core\Core\Model\Entity\SystemComponent');
$wysiwyg = $componentRepo->findOneBy(array('name'=>'Wysiwyg'));
$themeRepo   = new \Cx\Core\View\Model\Repository\ThemeRepository();
$pageRepo   = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
\Cx\Core\Setting\Controller\Setting::init('Config', 'wysiwyg', 'Yaml');

$skin = $themeRepo->getDefaultTheme()->getFoldername();
if(\Cx\Core\Setting\Controller\Setting::getValue('specificStylesheet','Config') && !empty($pageId) && $pageRepo->find($pageId)->getSkin()>0){
    $skin = $themeRepo->findById($pageRepo->find($pageId)->getSkin())->getFoldername();
}
//getThemeFileContent
$filePath = $skin.'/index.html';
$content = '';

if (file_exists(\Env::get('cx')->getWebsiteThemesPath().'/'.$filePath)) {
    $content = file_get_contents(\Env::get('cx')->getWebsiteThemesPath().'/'.$filePath);
} elseif (file_exists(\Env::get('cx')->getCodeBaseThemesPath().'/'.$filePath)) {
    $content = file_get_contents(\Env::get('cx')->getCodeBaseThemesPath().'/'.$filePath);
}

$cssArr = \JS::findCSS($content);

?>
//if the wysiwyg css not defined in the session, then load the css files and put it into the session
if(!cx.variables.get('wysiwygCss', 'wysiwyg')) {
    cx.variables.set('wysiwygCss', [<?php echo '\'' . implode($cssArr, '\',\'') . '\'' ?>], 'wysiwyg')
}

CKEDITOR.scriptLoader.load( '<?php echo $cx->getCodeBaseCoreModuleWebPath().'/MediaBrowser/View/Script/ckeditor-mediabrowser.js'   ?>' );
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
    
    config.templates_replaceContent = <?php echo \Cx\Core\Setting\Controller\Setting::getValue('replaceActualContents','Config')? 'true' : 'false' ?>;

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
    
    //Set the CSS
    config.contentsCss = cx.variables.get('wysiwygCss', 'wysiwyg');
};

//loading the templates
CKEDITOR.on('instanceReady',function(){
    var loadingTemplates = <?php echo $wysiwyg->getWysiwygTempaltes();?>;
    for(var instanceName in CKEDITOR.instances) {
        //console.log( CKEDITOR.instances[instanceName] );
        loadingTemplates.button = CKEDITOR.instances[instanceName].getCommand("templates") //Reference to Template-Button
        
        // Define Standard-Path
        //var path = CKEDITOR.plugins.getPath('templates')
        //var defaultPath = path.split("lib/ckeditor/")[0]+"customizing/lib/ckeditor"+path.split("lib/ckeditor")[1]+"templates/"
        //var defaultPath = path.split("lib/ckeditor")[0] //Path to Templates-Folder
        //var defaultPath = "/"
        loadingTemplates.load = (function(){
            //this.defaultPath = defaultPath;
            if (typeof this.button != 'undefined') {
                this.button.setState(CKEDITOR.TRISTATE_DISABLED) // Disable "Template"-Button
            }
            for(var i=0;i<this.length;i++){
                (function(item){
                    CKEDITOR.addTemplates('default',{
                        imagesPath: "../../",//CKEDITOR.getUrl(defaultPath),
                        templates: this
                    });
                }).bind(this)(this[i])
            }
            if (typeof this.button != 'undefined') {
                this.button.setState(CKEDITOR.TRISTATE_ENABLE) // Enable "Template"-Button
            }
        }).bind(loadingTemplates)();
    
    
    }
});

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

//this script will not be executed at the first round (first wysiwyg call)
cx.bind("loadingEnd", function(myArgs) {
    if(myArgs.hasOwnProperty('data')) {
        var data = myArgs['data'];
        if(data.hasOwnProperty('wysiwygCssReload') && (data.wysiwygCssReload).hasOwnProperty('wysiwygCss')) {
            for(var instanceName in CKEDITOR.instances) {
                //CKEDITOR.instances[instanceName].config.contentsCss =  data.wysiwygCssReload.wysiwygCss;
                var is_same = cx.variables.get('wysiwygCss', 'wysiwyg').length == (data.wysiwygCssReload.wysiwygCss).length && cx.variables.get('wysiwygCss', 'wysiwyg').every(function(element, index) {
                    return element === data.wysiwygCssReload.wysiwygCss[index]; 
                });
                if(!is_same){
                    //cant set the css on the run, so you must destroy the wysiwyg and recreate it
                    CKEDITOR.instances[instanceName].destroy();
                    cx.variables.set('wysiwygCss', data.wysiwygCssReload.wysiwygCss, 'wysiwyg')
                    var config = {
                        customConfig: cx.variables.get('basePath', 'contrexx') + cx.variables.get('ckeditorconfigpath', 'contentmanager'),
                        toolbar: 'Full',
                        skin: 'moono',
                    };
                    CKEDITOR.replace('page[content]', config);
                }
            }
        }
    }
}, "contentmanager");
