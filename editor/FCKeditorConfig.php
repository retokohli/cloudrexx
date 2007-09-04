<?php require_once('../config/configuration.php') ?>
/*
 * FCKeditor config file
 */

FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/default/' ;

FCKConfig.EnterMode = 'br';
FCKConfig.ShiftEnterMode = 'p';

FCKConfig.ToolbarSets["Default"] = [
	['Source','DocProps','-','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak'],
	'/',
	['FontFormat','FontName','FontSize'],
	['TextColor','BGColor'],
	['FitWindow']
];
FCKConfig.ToolbarSets["News"] = [
	['NewPage','Preview'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Table','Rule','Smiley','SpecialChar'],
	['FitWindow']
] ;

FCKConfig.LinkBrowserURL = FCKConfig.BasePath + '../../..<?php echo ASCMS_BACKEND_PATH; ?>/index.php?cmd=fileBrowser&standalone=true&type=webpages' ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + '../../..<?php echo ASCMS_BACKEND_PATH; ?>/index.php?cmd=fileBrowser&standalone=true';
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + '../../..<?php echo ASCMS_BACKEND_PATH; ?>/index.php?cmd=fileBrowser&standalone=true';
FCKConfig.LinkUploadURL = FCKConfig.BasePath + '../../..<?php echo ASCMS_BACKEND_PATH; ?>/index.php?cmd=fileBrowser&act=FCKEditorUpload&standalone=true&type=webpages';
FCKConfig.ImageUploadURL = FCKConfig.BasePath + '../../..<?php echo ASCMS_BACKEND_PATH; ?>/index.php?cmd=fileBrowser&act=FCKEditorUpload&standalone=true';
FCKConfig.FlashUploadURL = FCKConfig.BasePath + '../../..<?php echo ASCMS_BACKEND_PATH; ?>/index.php?cmd=fileBrowser&act=FCKEditorUpload&standalone=true';

