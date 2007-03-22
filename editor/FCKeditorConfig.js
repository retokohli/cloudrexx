/*
 * FCKeditor config file
 */

FCKConfig.DocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ;

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

FCKConfig.LinkBrowserURL = FCKConfig.BasePath + '../../../admin/index.php?cmd=fileBrowser&standalone=true&type=webpages' ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + '../../../admin/index.php?cmd=fileBrowser&standalone=true';
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + '../../../admin/index.php?cmd=fileBrowser&standalone=true';
FCKConfig.LinkUploadURL = FCKConfig.BasePath + '../../../admin/index.php?cmd=fileBrowser&act=FCKEditorUpload&standalone=true&type=webpages';
FCKConfig.ImageUploadURL = FCKConfig.BasePath + '../../../admin/index.php?cmd=fileBrowser&act=FCKEditorUpload&standalone=true';
FCKConfig.FlashUploadURL = FCKConfig.BasePath + '../../../admin/index.php?cmd=fileBrowser&act=FCKEditorUpload&standalone=true';