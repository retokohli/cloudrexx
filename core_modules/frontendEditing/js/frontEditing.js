var fe_feSection                = 'frontendEditing';

var fe_appearanceDuration		= 0.5;
var fe_backgroundOpacity		= 0.5;
var fe_loaderDivName 			= '#fe_Loader';
var fe_containerDivName 		= '#fe_Container';
var fe_backgroundDivName		= '#fe_Background';

var fe_loginDivName				= '#fe_Login';
var fe_loginTypeFrontend		= '#fe_LoginTypeFrontend';

var fe_disallowedDivName		= '#fe_Disallowed';
var fe_disallowedOpacity		= 0.9;

var fe_toolbarIsLoaded			= false;
var fe_toolbarIsVisible			= false;
var fe_toolbarDivName			= '#fe_Toolbar';

var fe_selectionDivName			= '#fe_Selection';

var fe_editorIsLoaded			= false;
var fe_editorIsVisible 			= false;
var fe_editorDivName 			= '#fe_Editor';
var fe_editorFormDivName 		= '#fe_EditorForm';
var fe_editorFormTitleName 		= '#fe_FormTitle';
var fe_editorFormContentName 	= 'fe_FormContent';
var fe_editorFormOldSuffix 		= '_Old';
var fe_editorHighlightColor 	= '#dff1ff';
var fe_editorWindowHeight 		= 0;
var fe_editorWindowWidth		= 0;

var fe_previewTitleName 		= '#fe_PreviewTitle';
var fe_previewContentName 		= '#fe_PreviewContent';
var fe_previewSaveIcon			= '#fe_saveIcon';
var fe_previewSaveIconIsVisible	= false;

jQuery(document).ready(function(){
    fe_fileForIndex = cx.variables.get('path','contrexx');
    fe_checkForLogin();
});

function fe_checkForLogin() {
    if(fe_userIsLoggedIn) {
        fe_loadToolbar(false);
    }
}

function fe_loadToolbar(showEditorAfterLoading) {
    if (!fe_toolbarIsLoaded) {
        console.log('nocht nicht geladen');
        fe_startLoading();
        jQuery.ajax({
            url: fe_fileForIndex,
            data: {	act: 'getToolbar', 
                    section: fe_feSection,
                    page: fe_pageId
            },
            success: function(transport){
                fe_loadToolbarResponse(transport, showEditorAfterLoading);
            },
            complete : fe_stopLoading
        });
    } else {
        console.log('geladen');
        fe_showToolbar();

        if (showEditorAfterLoading) {
            fe_loadEditor(true)
        }
    }
}

function fe_loadToolbarResponse(responseText, showEditorAfterLoading) {
    arrResponse = responseText.split(';;;', 2);

    jQuery(fe_containerDivName).html(arrResponse[1]);
    jQuery(fe_containerDivName).show();
                        
    switch (arrResponse[0]) {
        case 'login':
            fe_showLogin();
            break;
        case 'admin':
            jQuery(fe_containerDivName).hide();
            window.location = arrResponse[1];
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
    jQuery(fe_backgroundDivName).fadeIn();
    jQuery(fe_loginDivName).fadeIn();
}

function fe_closeLogin() {
    jQuery(fe_backgroundDivName).effect('fade');
    jQuery(fe_loginDivName).effect('fade');
}

function fe_doLogin() {
    fe_closeLogin();
    fe_startLoading();
    
    postdata = {
        section: fe_feSection,
        doLogin: 'true',
    };

    var loginType = jQuery(fe_loginTypeFrontend).is(':checked') ? 'frontend' : 'backend';
    if (loginType == 'frontend') {
        postdata['act'] = 'getToolbar';
        showEditorAfterLoading = true;
    } else {
        showEditorAfterLoading = false;
        postdata['act'] = 'getAdmin';
    }

    jQuery.map(jQuery('#fe_LoginForm').serializeArray(), function(n, i){
        postdata[n['name']] = n['value'];
    });

    jQuery.ajax({
        type: 'POST',
        url: fe_fileForIndex,
        data: postdata,
        success: function(transport){
            fe_loadToolbarResponse(transport, showEditorAfterLoading);
        },
        complete : fe_stopLoading
    });
}

function fe_showDisallowed() {
    jQuery(fe_disallowedDivName).fadeIn();
    jQuery(fe_disallowedDivName).effect('fade');
}

function fe_showToolbar() {
    if (!fe_toolbarIsLoaded) {
        toolbarDiv = jQuery(fe_toolbarDivName).detach();
        jQuery(toolbarDiv).prependTo(jQuery(document.body));
        fe_toolbarIsLoaded = true;
    }
    
    if (!fe_toolbarIsVisible && fe_userWantsToolbar) {	
        jQuery(fe_toolbarDivName).fadeIn();
        fe_toolbarIsVisible = true;
    }
}

function fe_closeToolbar() {
    jQuery(fe_toolbarDivName).effect('fade');
    fe_toolbarIsVisible = false;
    
    if (fe_editorIsVisible) {
        fe_makeEditorInvisible();
    }
    
    fe_setToolbarVisibility(false);
}

function fe_setToolbarVisibility(newStatus) {
    fe_userWantsToolbar = newStatus;
        
    fe_startLoading();
    jQuery.ajax({
        url: fe_fileForIndex,
        data: {	act: 'setToolbarVisibility',
                section: fe_feSection,
                status: ((fe_userWantsToolbar == true) ? '1' : '0')
        },
        success: function(transport){},
        complete : fe_stopLoading
    });
}

function fe_doLogout() {
    if (fe_editorIsVisible) {
        fe_makeEditorInvisible();
    }
    
    fe_startLoading();
    jQuery.ajax({
        url: fe_fileForIndex,
        data: {	section: 'logout', standalone: 'true' },
        success: function(transport){
            fe_toolbarIsLoaded 	= false;
            fe_toolbarIsVisible = false;
            jQuery(fe_toolbarDivName).remove();

            fe_editorIsLoaded	= false;
            fe_editorIsVisible	= false;
            fe_loadDefault();
        },
        complete : fe_stopLoading
    });
}

function fe_closeSelection() {
    jQuery(fe_backgroundDivName).effect('fade');
    jQuery(fe_selectionDivName).effect('fade');
}

function fe_showSelection() {
    jQuery(fe_backgroundDivName).fadeIn();
    jQuery(fe_selectionDivName).fadeIn();
}

function fe_loadEditor(showSelectionIfNeeded) {
    if (!fe_editorIsLoaded) {
        fe_startLoading();
        jQuery.ajax({
            url: fe_fileForIndex,
            data: {	act: 'getEditor', 
                    section: fe_feSection,
                    page: fe_pageId,
                    selection: showSelectionIfNeeded
            },
            success: function(transport){
                fe_loadEditorResponse(transport);
            },
            complete : fe_stopLoading()
        });
    } else {
        fe_showEditor();
    }
}

function fe_loadEditorResponse(responseText) {
    var arrResponse = responseText.split(';;;', 2);
                                                                                                                                                          
    jQuery(fe_containerDivName).html(arrResponse[1]);
    jQuery(fe_containerDivName).show();
                        
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
        CKEDITOR.replace(fe_editorFormContentName, {
            width: '100%',
            height: 300,
            toolbar: 'Default',
            customConfig: CKEDITOR.getUrl('config.contrexx.js.php')
        });

        CKEDITOR.on("instanceReady", function(event){
            jQuery("#cke_message").css({marginLeft:"0px"});
        });

        editorDiv = jQuery(fe_editorDivName).detach();
        editorBackgroundDiv = jQuery(fe_backgroundDivName).detach();
        
        jQuery(editorDiv).insertAfter(jQuery(fe_toolbarDivName));
        jQuery(editorBackgroundDiv).insertAfter(jQuery(editorDiv));
        
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
    jQuery(fe_backgroundDivName).fadeIn();
    jQuery(fe_editorDivName).fadeIn();
     fe_editorIsVisible = true;
}

function fe_makeEditorInvisible() {
    jQuery(fe_backgroundDivName).effect('fade');
     jQuery(fe_editorDivName).effect('fade');
     fe_editorIsVisible = false;
}

function fe_loadDefault() {
    fe_startLoading();
    jQuery.ajax({
        data: {	frontEditing: '1' },
        success: function(transport){
            fe_restoreDefault(transport);
        },
        complete : fe_stopLoading()
    });
}

function fe_restoreDefault(defaultContent) {
    var fe_title = jQuery(fe_previewTitleName);
    if (fe_title) {
        fe_title.html(jQuery(fe_editorFormTitleName + fe_editorFormOldSuffix).val());
        jQuery(fe_previewTitleName).effect('highlight');
    }
    jQuery(fe_previewContentName).html(defaultContent);
    jQuery(fe_previewContentName).effect('highlight');
}

function fe_loadPreview(previewMode) {
    fe_makeEditorInvisible();
    var editorContent = CKEDITOR.instances[fe_editorFormContentName].getData()
    fe_startLoading();
    jQuery.ajax({
        type: 'POST',
        data: {	frontEditing: '1',
                previewContent: editorContent
        },
        success: function(transport){
            fe_showPreview(transport);

            if (previewMode) {
                fe_showSaveIcon();
            }
        },
        complete : fe_stopLoading()
    });
}

function fe_showPreview(previewContent) {
    var fe_title = jQuery(fe_previewTitleName);
    if (fe_title) {
        fe_title.html(jQuery(fe_editorFormTitleName).val());
        fe_title.effect('highlight');
    }

     jQuery(fe_previewContentName).html(previewContent);	 	
     jQuery(fe_previewContentName).effect('highlight');
}

function fe_showSaveIcon() {
    jQuery(fe_previewSaveIcon).fadeIn();
    fe_previewSaveIconIsVisible = true;
}

function fe_hideSaveIcon() {
    if (fe_previewSaveIconIsVisible) {
        jQuery(fe_previewSaveIcon).effect('fade');
        fe_previewSaveIconIsVisible = false;
    }
}

function fe_updatePage() {
    fe_makeEditorInvisible();
    fe_startLoading();
    jQuery.ajax({
        url: fe_fileForIndex,
        type: 'POST',
        data: {	act: 'doUpdate', 
                section: fe_feSection,
                page: fe_pageId,
                title: jQuery(fe_editorFormTitleName).val(),
                content: CKEDITOR.instances[fe_editorFormContentName].getData()
        },
        success: function(transport){
            fe_loadPreview(false);
            fe_hideSaveIcon();
        },
        complete : fe_stopLoading()
    });
}

function fe_startLoading() {
    jQuery(fe_loaderDivName).show();
}

function fe_stopLoading() {
    jQuery(fe_loaderDivName).hide();
}
