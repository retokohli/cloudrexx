/**
 * Frontend Editing
 * @author: Ueli Kramer <ueli.kramer@comvation.com>
 * @version: 1.0
 * @package: contrexx
 * @subpackage: core_modules_frontend_editing
 */
CKEDITOR.disableAutoInline = true;

cx.ready(function () {
    cx.fe();
});

/**
 * Init the editor
 * Do some configurations and start the editor
 * Open the toolbar if it was active already
 */
cx.fe = function () {
    cx.fe.langVars = cx.variables.get('langVars', 'frontendEditing');

    cx.fe.addCustomPlugins();
    cx.fe.toolbar();
};

/**
 * Init the ckeditor for the content and title element
 */
cx.fe.contentEditor = function () {
    cx.fe.loadPageData();

    cx.jQuery('#fe_content,#fe_title').attr('contenteditable', true).css('outline', '1px #0873bb dashed');

    // @todo only show publish button if the user has the permission to publish
    CKEDITOR.inline('fe_content', {
        customConfig: CKEDITOR.getUrl(cx.variables.get('configPath', 'frontendEditing')),
        toolbar: 'FrontendEditingContent',
        removePlugins: 'bbcode',
        extraPlugins: 'publish,save'
    });
    CKEDITOR.inline('fe_title', {
        customConfig: CKEDITOR.getUrl(cx.variables.get('configPath', 'frontendEditing')),
        toolbar: 'FrontendEditingTitle',
        forcePasteAsPlainText: true,
        extraPlugins: 'publish,save'
    });

    cx.fe.editMode = true;
    return false;
};

/**
 * stop the edit mode
 */
cx.fe.contentEditor.stop = function() {
    // @todo ask to save as draft, load newest published content
    CKEDITOR.instances.fe_content.destroy();
    CKEDITOR.instances.fe_title.destroy();
    cx.jQuery('#fe_content,#fe_title').attr('contenteditable', false).css('outline', '');
    cx.fe.editMode = false;
    return false;
}

/**
 * Init the toolbar
 * Show the toolbar if the cookie for the toolbar is set what means that it was opened
 * in the last session
 */
cx.fe.toolbar = function() {
    cx.fe.toolbar_opened = (cx.jQuery.cookie('fe_toolbar') ? true : false);
    if (cx.fe.toolbar_opened) {
        cx.fe.toolbar.show();
    } else {
        cx.fe.toolbar.hide();
    }
    cx.jQuery('#fe_toolbar_tab').click(function () {
        if (cx.fe.toolbar_opened) {
            cx.fe.toolbar.hide();
        } else {
            cx.fe.toolbar.show();
        }
    });
    cx.jQuery('#fe_toolbar_startEditMode').html(cx.fe.langVars.TXT_FRONTEND_EDITING_EDIT).click(function() {
        if (cx.fe.editMode == true) {
            cx.jQuery(this).html(cx.fe.langVars.TXT_FRONTEND_EDITING_EDIT);
            cx.fe.contentEditor.stop();
        } else {
            cx.jQuery(this).html(cx.fe.langVars.TXT_FRONTEND_EDITING_STOP_EDIT);
            cx.fe.contentEditor();
        }
        return false;
    });
}

/**
 * Hide the toolbar
 */
cx.fe.toolbar.hide = function () {
    // do the css
    cx.jQuery('#fe_toolbar').css('top', '-' + cx.jQuery('#fe_toolbar').height() + 'px');
    cx.jQuery('body').css('padding-top', '0px');

    // do the html
    cx.jQuery('#fe_toolbar_tab').html(cx.fe.langVars.TXT_FRONTEND_EDITING_SHOW_TOOLBAR);

    // save the status
    cx.fe.toolbar_opened = false;
    cx.jQuery.cookie('fe_toolbar', cx.fe.toolbar_opened);
};

/**
 * Show the toolbar
 */
cx.fe.toolbar.show = function () {
    // do the css
    cx.jQuery('#fe_toolbar').css({
        top: '0px',
        display: 'block'
    });
    cx.jQuery('body').css('padding-top', cx.jQuery('#fe_toolbar').height() + 'px');

    // do the html
    cx.jQuery('#fe_toolbar_tab').html(cx.fe.langVars.TXT_FRONTEND_EDITING_HIDE_TOOLBAR);

    // save the status
    cx.fe.toolbar_opened = true;
    cx.jQuery.cookie('fe_toolbar', cx.fe.toolbar_opened);
};


cx.fe.loadPageData = function (historyId) {
    var url = cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=get&page=' + cx.variables.get('pageId', 'frontendEditing') + '&lang=' + cx.jQuery.cookie('langId') + '&userFrontendLangId=' + cx.jQuery.cookie('langId');
    if (historyId) {
        url += '&history=' + historyId;
    }
    jQuery.ajax({
        url : url,
        complete : function(response) {
            cx.fe.page = jQuery.parseJSON(response.responseText).data;

            if (   cx.fe.page.editingStatus == 'hasDraft'
                || cx.fe.page.editingStatus == 'hasDraftWaiting') {
                cx.jQuery('#fe_toolbar_status_message').html(cx.fe.langVars.TXT_FRONTEND_EDITING_HAS_DRAFT).attr('class', 'warningbox');

                cx.jQuery('#fe_toolbar_load_draft').click(function() {
                    // @todo: test
                    cx.fe.loadPageData(cx.fe.page.historyId - 1);
                });
            } else {
                cx.jQuery('#fe_toolbar_status_message').html('').attr('class', '');
            }
        }
    });
}
