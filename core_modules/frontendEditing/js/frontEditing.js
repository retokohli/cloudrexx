var fe_pathToFrontEditing 		= 'core_modules/frontendEditing/';
var fe_fileForFrontEditing		= 'frontendEditing.class.php';
var fe_fileForIndex				= 'index.php';
var fe_pathToBackend			= 'cadmin/index.php';

var fe_appearanceDuration		= 0.5;
var fe_backgroundOpacity		= 0.5;
var fe_loaderDivName 			= 'fe_Loader';
var fe_containerDivName 		= 'fe_Container';
var fe_backgroundDivName		= 'fe_Background';

var fe_loginDivName				= 'fe_Login';
var fe_loginUsername			= 'fe_LoginUsername';
var fe_loginPassword			= 'fe_LoginPassword';
var fe_loginSecurityKey			= 'fe_LoginSecurityKey';
var fe_loginTypeFrontend		= 'fe_LoginTypeFrontend';
var fe_loginTypeBackend			= 'fe_LoginTypeBackend';
var fe_loginPageId				= 'fe_LoginPageId';
var fe_loginPageSection			= 'fe_LoginPageSection';
var fe_loginPageCmd				= 'fe_LoginPageCmd';

var fe_disallowedDivName		= 'fe_Disallowed';
var fe_disallowedOpacity		= 0.9;

var fe_toolbarIsLoaded			= false;
var fe_toolbarIsVisible			= false;
var fe_toolbarDivName			= 'fe_Toolbar';

var fe_selectionDivName			= 'fe_Selection';

var fe_editorIsLoaded			= false;
var fe_editorIsVisible 			= false;
var fe_editorDivName 			= 'fe_Editor';
var fe_editorFormDivName 		= 'fe_EditorForm';
var fe_editorFormTitleName 		= 'fe_FormTitle';
var fe_editorFormContentName 	= 'fe_FormContent';
var fe_editorFormOldSuffix 		= '_Old';
var fe_editorHighlightColor 	= '#dff1ff';
var fe_editorWindowHeight 		= 0;
var fe_editorWindowWidth		= 0;

var fe_previewTitleName 		= 'fe_PreviewTitle';
var fe_previewContentName 		= 'fe_PreviewContent';
var fe_previewSaveIcon			= 'fe_saveIcon';
var fe_previewSaveIconIsVisible	= false;

function fe_checkForLogin() {
	if(fe_userIsLoggedIn) {
		fe_loadToolbar(false);
	}
}
var oldOnLoad = window.onload;
window.onload = function() { if (oldOnLoad) { oldOnLoad(); } fe_checkForLogin(); };

function fe_loadToolbar(showEditorAfterLoading) {
	if (!fe_toolbarIsLoaded) {
	    fe_startLoading();
		new Ajax.Request(	fe_pathToFrontEditing + fe_fileForFrontEditing, 
							{	method: 'get',
								parameters: {	act: 'getToolbar', 
												page: fe_pageId,
												section: fe_pageSection,
												cmd: fe_pageCommand
											},
		  						onSuccess: function(transport) {
		  							fe_loadToolbarResponse(transport.responseText, showEditorAfterLoading);
		  						},
		  						onComplete : fe_stopLoading
							}
						);				
	} else {
		fe_showToolbar();
	}
}

function fe_loadToolbarResponse(responseText, showEditorAfterLoading) {
	arrResponse = responseText.split(';;;', 2);

	$(fe_containerDivName).update(arrResponse[1]);
	$(fe_containerDivName).show();
						
	switch (arrResponse[0]) {
		case 'login':
			fe_showLogin();
			break;
		case 'admin':
			$(fe_containerDivName).hide();
			window.location = fe_pathToBackend;
		break;
		default:
			fe_showToolbar();
			
			if (showEditorAfterLoading) {
				fe_loadEditor(true)
			}
	}
	
	return;
}

function fe_showLogin() {
	Effect.Appear(fe_backgroundDivName, {duration: fe_appearanceDuration, to: fe_backgroundOpacity});
	Effect.Appear(fe_loginDivName, {duration: fe_appearanceDuration});
}

function fe_closeLogin() {
	Effect.Fade(fe_backgroundDivName, {duration: fe_appearanceDuration, from: fe_backgroundOpacity});
	Effect.Fade(fe_loginDivName, {duration: fe_appearanceDuration, queue:{scope:'myscope', position:'end'}});
}

function fe_doLogin() {
	fe_closeLogin();
		
	pageId 	= $F(fe_loginPageId);
	section = $F(fe_loginPageSection);
	cmd 	= $F(fe_loginPageCmd);
	
	var loginType = ($(fe_loginTypeFrontend).checked == true) ? 'frontend' : 'backend';

	if (loginType == 'frontend') {
	    fe_startLoading();
		new Ajax.Request(	fe_pathToFrontEditing + fe_fileForFrontEditing, 
							{	method: 'post',
								parameters: {	act: 'getToolbar',
												page: pageId,
												section: section,
												cmd: cmd,
												doLogin: 'true',
												username: $F(fe_loginUsername),
												password: $F(fe_loginPassword),
												seckey: $F(fe_loginSecurityKey),
												type: loginType
											},
		  						onSuccess: function(transport) {
									fe_loadToolbarResponse(transport.responseText, true);
		  						},
		  						onComplete: fe_stopLoading()
							}
						);
	} else {
	    fe_startLoading();
		new Ajax.Request(	fe_pathToFrontEditing + fe_fileForFrontEditing, 
							{	method: 'post',
								parameters: {	act: 'getAdmin',
												doLogin: 'true',
												username: $F(fe_loginUsername),
												password: $F(fe_loginPassword),
												seckey: $F(fe_loginSecurityKey),
												type: loginType
											},
		  						onSuccess: function(transport) {
		  							fe_loadToolbarResponse(transport.responseText, false);
		  						},
		  						onComplete: fe_stopLoading()
							}
						);
	}
}

function fe_showDisallowed() {
	Effect.Appear(fe_disallowedDivName, {duration: fe_appearanceDuration, to: fe_disallowedOpacity, queue:{scope:'myscope', position:'end'}});
	Effect.Fade(fe_disallowedDivName, {duration: fe_appearanceDuration, from: fe_disallowedOpacity, delay: 1.0, queue:{scope:'myscope', position:'end'}});
}

function fe_showToolbar() {
	if (!fe_toolbarIsLoaded) {
		toolbarDiv = $(fe_toolbarDivName).remove();
		body = document.getElementsByTagName("body")[0];
		new Insertion.Top(body, toolbarDiv);
		fe_toolbarIsLoaded = true;
	}
	
	if (!fe_toolbarIsVisible && fe_userWantsToolbar) {	
		Effect.Appear(fe_toolbarDivName, {duration: fe_appearanceDuration, from: 0.0, to: 1.0});
		fe_toolbarIsVisible = true;
	}
}

function fe_closeToolbar() {
	Effect.Fade(fe_toolbarDivName, {duration: fe_appearanceDuration, to: 0.0});
	fe_toolbarIsVisible = false;
	
	if (fe_editorIsVisible) {
		fe_makeEditorInvisible();
	}
	
	fe_setToolbarVisibility(false);
}

function fe_setToolbarVisibility(newStatus) {
	fe_userWantsToolbar = newStatus;
		
	fe_startLoading();
	new Ajax.Request(	fe_pathToFrontEditing + fe_fileForFrontEditing, 
					{	method: 'get',
						parameters: {	act: 'setToolbarVisibility',
										status: ((fe_userWantsToolbar == true) ? '1' : '0')
									},
  						onSuccess: function(transport) {},
  						onComplete: fe_stopLoading()
					}
				);
}

function fe_doLogout() {
	if (fe_editorIsVisible) {
		fe_makeEditorInvisible();
	}
	
	fe_startLoading();
	new Ajax.Request(	fe_fileForIndex, 
					{	method: 'get',
						parameters: {	section: 'logout' },
  						onSuccess: function(transport) {
  							fe_toolbarIsLoaded 	= false;
  							fe_toolbarIsVisible = false;
  							$(fe_toolbarDivName).remove();
  							
  							fe_editorIsLoaded	= false;
  							fe_editorIsVisible	= false;
  							$(fe_containerDivName).update();
  						},
  						onComplete: fe_stopLoading()
					}
				);
}

function fe_closeSelection() {
	Effect.Fade(fe_backgroundDivName, {duration: fe_appearanceDuration, from: fe_backgroundOpacity});
	Effect.Fade(fe_selectionDivName, {duration: fe_appearanceDuration, queue:{scope:'myscope', position:'end'}});
}

function fe_showSelection() {
	Effect.Appear(fe_backgroundDivName, {duration: fe_appearanceDuration, to: fe_backgroundOpacity});
	Effect.Appear(fe_selectionDivName, {duration: fe_appearanceDuration});
}

function fe_loadEditor(showSelectionIfNeeded) {
	if (!fe_editorIsLoaded) {
	    fe_startLoading();
		new Ajax.Request(	fe_pathToFrontEditing + fe_fileForFrontEditing, 
						{	method: 'get',
							parameters: {	act: 'getEditor', 
											page: fe_pageId,
											section: fe_pageSection,
											cmd: fe_pageCommand,
											selection: showSelectionIfNeeded
										},
	  						onSuccess: function(transport) {
	  							fe_loadEditorResponse(transport.responseText);
	  						},
	  						onComplete: function() {
                                CKEDITOR.replace(fe_editorFormContentName, {
                                    width: '100%',
                                    height: 400,
                                    toolbar: 'Default',
                                    customConfig: CKEDITOR.getUrl('config.contrexx.js.php')
                                });
                                CKEDITOR.on("instanceReady", function(event){
                                    $J("#cke_message").css({marginLeft:"0px"});
                                });
                                fe_stopLoading();
                            }
						}
					);		
	} else {
		fe_showEditor();
	}
}

function fe_loadEditorResponse(responseText) {
	var arrResponse = responseText.split(';;;', 2);
	  							  									  					  							  							
	$(fe_containerDivName).update(arrResponse[1]);
	$(fe_containerDivName).show();
						
	switch (arrResponse[0]) {
		case 'login':
			fe_showLogin();
			break;
		case 'disallowed':
			fe_showDisallowed();
			break;
		case 'selection':
			fe_showSelection();
			break;
		default:
			fe_showEditor();
	}
	
	return;
}

function fe_showEditor() {
	if (!fe_editorIsLoaded) {
		editorDiv = $(fe_editorDivName).remove();
		editorBackgroundDiv = $(fe_backgroundDivName).remove();
		
		new Insertion.After(toolbarDiv, editorDiv);
		new Insertion.After(editorDiv, editorBackgroundDiv);
		
		fe_editorIsLoaded = true;
	}
	
	fe_switchEditorVisibility();
	fe_hideSaveIcon();
}

function fe_switchEditorVisibility() {
	if (fe_editorIsVisible == true) {
 		fe_makeEditorInvisible()
 	} else {
 		fe_makeEditorVisible();
 	}
}

function fe_makeEditorVisible() {
	Effect.Appear(fe_backgroundDivName, {duration: fe_appearanceDuration, to: fe_backgroundOpacity});
	Effect.Appear(fe_editorDivName, {duration: fe_appearanceDuration});
 	fe_editorIsVisible = true;
}

function fe_makeEditorInvisible() {
	Effect.Fade(fe_backgroundDivName, {duration: fe_appearanceDuration, from: fe_backgroundOpacity});
 	Effect.Fade(fe_editorDivName, {duration: fe_appearanceDuration});
 	fe_editorIsVisible = false;
}

function fe_loadDefault() {
    fe_startLoading();
	new Ajax.Request(	fe_fileForIndex, 
						{	method: 'get',
							parameters: {	frontEditing: '1', 
											page: fe_pageId,
											section: fe_pageSection,
											cmd: fe_pageCommand
										},
	  						onSuccess: function(transport) {
	  							fe_restoreDefault(transport.responseText);
	  						},
	  						onComplete: fe_stopLoading()
						}
					);	
}

function fe_restoreDefault(defaultContent) {
	var fe_title = $(fe_previewTitleName);
	if (fe_title) {
		fe_title.update($F(fe_editorFormTitleName + fe_editorFormOldSuffix));
		new Effect.Highlight(fe_previewTitleName, {startcolor: fe_editorHighlightColor, duration: fe_appearanceDuration});	
	}
	$(fe_previewContentName).update(defaultContent);
	new Effect.Highlight(fe_previewContentName, {startcolor: fe_editorHighlightColor, duration: fe_appearanceDuration});
}

function fe_loadPreview(previewMode) {
	fe_makeEditorInvisible();
	var editorContent = FCKeditorAPI.GetInstance(fe_editorFormContentName).GetData();	
	fe_startLoading();
	new Ajax.Request(	fe_fileForIndex, 
						{	method: 'get',
							parameters: {	frontEditing: '1',
											page: fe_pageId,
											section: fe_pageSection,
											cmd: fe_pageCommand,
											previewContent: editorContent
										},
	  						onSuccess: function(transport) {
	  							fe_showPreview(transport.responseText);
	  							
	  							if (previewMode) {
	  								fe_showSaveIcon();
	  							}
	  						},
	  						onComplete: fe_stopLoading()
						}
					);	
}

function fe_showPreview(previewContent) {
	var fe_title = $(fe_previewTitleName);
	if (fe_title) {
		fe_title.update($F(fe_editorFormTitleName));
		new Effect.Highlight(fe_previewTitleName, {startcolor: fe_editorHighlightColor, duration: fe_appearanceDuration, delay: 0.2});
	}

 	$(fe_previewContentName).update(previewContent);	 	
 	new Effect.Highlight(fe_previewContentName, {startcolor: fe_editorHighlightColor, duration: fe_appearanceDuration, delay: 0.2});
}

function fe_showSaveIcon() {
	Effect.Appear(fe_previewSaveIcon, {duration: 0.2});
	fe_previewSaveIconIsVisible = true;
}

function fe_hideSaveIcon() {
	if (fe_previewSaveIconIsVisible) {
		Effect.Fade(fe_previewSaveIcon, {duration: 0.2});
		fe_previewSaveIconIsVisible = false;
	}
}

function fe_updatePage() {
	fe_makeEditorInvisible();
	fe_startLoading();
	new Ajax.Request(	fe_pathToFrontEditing + fe_fileForFrontEditing,
						{	method: 'post',
							parameters: {	act: 'doUpdate', 
											page: fe_pageId,
											section: fe_pageSection,
											cmd: fe_pageCommand,
											title: $F(fe_editorFormTitleName),
											content: CKEDITOR.instances[fe_editorFormContentName].getData()
										},
	  						onSuccess: function(transport) {
	  							fe_loadPreview(false);
	  							fe_hideSaveIcon();
	  						},
	  						onComplete: fe_stopLoading()
						}
					);	
}

function fe_startLoading() {
	$(fe_loaderDivName).show();
}

function fe_stopLoading() {
	$(fe_loaderDivName).hide();
}
