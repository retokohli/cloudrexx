/**
 * Frontend Editing
 * @author: Ueli Kramer <ueli.kramer@comvation.com>
 * @version: 1.0
 * @package: contrexx
 * @subpackage: core_modules_frontendediting
 */

/**
 * Set CKEDITOR configuration, so the inline editing will not start automatically
 * @type {boolean}
 */
CKEDITOR.disableAutoInline = true;

/**
 * Run the frontend editing when DOM is ready
 */
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
    cx.fe.toolbar.hideAnchors();
};

/**
 * Init the ckeditor for the content and title element
 */
cx.fe.contentEditor = function () {
    cx.fe.editMode = true;

    // add border around the editable contents
    cx.jQuery('#fe_content,#fe_title').attr('contenteditable', true).css('outline', '1px #0873bb dashed');

    // check for publish permission and add publish button
    var extraPlugins = ['save'];
    if (cx.variables.get('hasPublishPermission', 'frontendEditing')) {
        extraPlugins.push('publish');
    }

    // init the editors
    if (!CKEDITOR.instances.fe_title) {
        CKEDITOR.inline('fe_title', {
            customConfig: CKEDITOR.getUrl(cx.variables.get('configPath', 'frontendEditing')),
            toolbar: 'FrontendEditingTitle',
            forcePasteAsPlainText: true,
            extraPlugins: extraPlugins.join(','),
            entities: false
        });
    }
    if (!CKEDITOR.instances.fe_content) {
        CKEDITOR.inline('fe_content', {
            customConfig: CKEDITOR.getUrl(cx.variables.get('configPath', 'frontendEditing')),
            toolbar: 'FrontendEditingContent',
            extraPlugins: extraPlugins.join(',')
        });
    }
    return false;
};

/**
 * stop the edit mode
 */
cx.fe.contentEditor.stop = function () {
    cx.fe.editMode = false;

    // load last published content if the page in the current editor is a draft
    if (cx.jQuery('#fe_title').html() != cx.fe.publishedPage.title
        || cx.jQuery('#fe_content').html() != cx.fe.publishedPage.content) {
        if (confirm(cx.fe.langVars.TXT_FRONTEND_EDITING_SAVE_CURRENT_STATE)) {
            cx.fe.savePage();
        }
        cx.jQuery('#fe_title').html(cx.fe.publishedPage.title);
        cx.jQuery('#fe_content').html(cx.fe.publishedPage.content);
    }

    // destroy ckeditor instances
    if (CKEDITOR.instances.fe_content != undefined) {
        CKEDITOR.instances.fe_content.destroy();
    }
    if (CKEDITOR.instances.fe_title != undefined) {
        CKEDITOR.instances.fe_title.destroy();
    }

    // remove some css
    cx.jQuery('#fe_content,#fe_title').attr('contenteditable', false).css('outline', '');

    // remove status message
    cx.jQuery.fn.cxDestroyDialogs();

    // remove history and options
    cx.fe.toolbar.hideAnchors();
    return false;
};

/**
 * Init the toolbar
 * Show the toolbar if the cookie for the toolbar is set what means that it was opened
 * in the last session
 */
cx.fe.toolbar = function () {
    // is toolbar already opened from last session
    cx.fe.toolbar_opened = (cx.jQuery.cookie('fe_toolbar') == 'true');

    // if it was opened the last time, open now or init and hide
    if (cx.fe.toolbar_opened == true) {
        cx.fe.toolbar.show();
    } else {
        cx.fe.toolbar.show();
        cx.fe.toolbar.hide();
    }

    // add click handler for toolbar tab
    cx.jQuery('#fe_toolbar_tab').click(function () {
        if (cx.fe.toolbar_opened == true) {
            cx.fe.toolbar.hide();
        } else {
            cx.fe.toolbar.show();
        }
    });

    // save published content and title
    cx.fe.publishedPage = {
        title: cx.jQuery('#fe_title').html(),
        content: cx.jQuery('#fe_content').html()
    };

    // start / stop edit mode button
    cx.jQuery('#fe_toolbar_startEditMode').html(cx.fe.langVars.TXT_FRONTEND_EDITING_EDIT).click(function () {
        if (cx.fe.editMode == true) {
            // if the edit mode was active, stop the editor
            cx.jQuery(this).html(cx.fe.langVars.TXT_FRONTEND_EDITING_EDIT);
            cx.fe.contentEditor.stop();
        } else {
            // if the edit mode was not active, start the editor
            cx.jQuery(this).html(cx.fe.langVars.TXT_FRONTEND_EDITING_STOP_EDIT);

            // load newest version, draft or published and refresh the editor's content
            cx.fe.loadPageData(null, true, function () {
                cx.fe.history();
                cx.fe.options();
                cx.fe.toolbar.showAnchors();
            });

            // init the ckeditor
            cx.fe.contentEditor();
        }
        return false;
    });
};

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

/**
 * hide history and options anchors
 */
cx.fe.toolbar.hideAnchors = function () {
    cx.jQuery('#fe_history_box_anchor,#fe_options_box_anchor').hide();
};

/**
 * show history and options anchors
 */
cx.fe.toolbar.showAnchors = function () {
    cx.jQuery('#fe_history_box_anchor,#fe_options_box_anchor').show();
};

/**
 * prepare Page for sending
 *
 * replace true and false statements to "on" and "off"
 */
cx.fe.preparePageToSend = function () {
    cx.fe.page.title = CKEDITOR.instances.fe_title.getData();
    cx.fe.page.content = CKEDITOR.instances.fe_content.getData();
    cx.fe.page.application = cx.fe.page.module;
    cx.fe.page.skin = cx.jQuery('#fe_options_box select[name="page[skin]"]').val();
    cx.fe.page.customContent = cx.jQuery('#fe_options_box select[name="page[customContent]"]').val();
    cx.fe.page.cssName = cx.jQuery('#fe_options_box input[name="page[cssName]"]').val();

    // rewrite true and false to on and off
    cx.fe.page.scheduled_publishing = (cx.fe.page.scheduled_publishing === true ? 'on' : 'off');
    cx.fe.page.protection_backend = (cx.fe.page.protection_backend === true ? 'on' : 'off');
    cx.fe.page.protection_frontend = (cx.fe.page.protection_frontend === true ? 'on' : 'off');
    cx.fe.page.caching = (cx.fe.page.caching === true ? 'on' : 'off');
    cx.fe.page.sourceMode = (cx.fe.page.sourceMode === true ? 'on' : 'off');
    cx.fe.page.metarobots = (cx.fe.page.metarobots === true ? 'on' : 'off');
};

/**
 * load the page data
 * @param historyId
 * @param putTheData
 * @param callback
 */
cx.fe.loadPageData = function (historyId, putTheData, callback) {
    console.log('called: loadPageData(' + historyId + ',' + putTheData + ')');
    var url = cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=get&page=' + cx.variables.get('pageId', 'frontendEditing') + '&lang=' + cx.jQuery.cookie('langId') + '&userFrontendLangId=' + cx.jQuery.cookie('langId');
    if (historyId) {
        url += '&history=' + historyId;
    }
    console.log('call request: ' + url);
    jQuery.ajax({
        url: url,
        complete: function (response) {
            // get the page json data response
            cx.fe.page = jQuery.parseJSON(response.responseText).data;

            if (putTheData) {
                // put the new data of page into the html and start editor if the user is in edit mode
                cx.jQuery('#fe_title').html(cx.fe.page.title);
                cx.jQuery('#fe_content').html(cx.fe.page.content);
                if (cx.fe.editMode == true) {
                    cx.fe.contentEditor();
                }
            }

            cx.fe.history.loadedVersion = (historyId ? historyId : (cx.fe.pageIsADraft() ? cx.fe.page.historyId - 1 : cx.fe.page.historyId));
            cx.fe.history.updateHighlighting();

            // if it is a draft tell the user that he is editing a draft
            if (cx.fe.pageIsADraft() && cx.fe.editMode == true
                && ((historyId && historyId == cx.fe.page.historyId - 1) || !historyId)) {
                if (cx.fe.dialogTimeout > 0) {
                    setTimeout(function () {
                        cx.jQuery('<div class="warning">' + cx.fe.langVars.TXT_FRONTEND_EDITING_THE_DRAFT + '</div>').cxNotice();
                        clearTimeout(cx.fe.dialogTimeout);
                    }, 5000);
                } else {
                    cx.jQuery('<div class="warning">' + cx.fe.langVars.TXT_FRONTEND_EDITING_THE_DRAFT + '</div>').cxNotice();
                }
            } else {
                cx.jQuery.fn.cxDestroyDialogs();
            }
            if (callback) {
                callback();
            }
            cx.fe.history.load();
            cx.fe.options.load();
        }
    });
};

/**
 * Returns true when the page is a draft, false if not
 * @returns {boolean}
 */
cx.fe.pageIsADraft = function () {
    if (cx.fe.page.editingStatus == 'hasDraft'
        || cx.fe.page.editingStatus == 'hasDraftWaiting') {
        return true;
    }
    return false;
};

/**
 * Does a request to publish the new contents
 */
cx.fe.publishPage = function () {
    cx.fe.preparePageToSend();

    jQuery.post(cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=set', {
        'action': 'publish',
        'page': cx.fe.page
    }, function (response) {
        if (response.data != null) {
            var className = '';
            if (response.status != 'success') {
                className = ' class="error"';
            }
            cx.jQuery('<div' + className + '>' + response.message + '</div>').cxNotice();
            cx.jQuery.fn.cxDestroyDialogs(5000);
        }
        cx.fe.publishedPage = {
            title: cx.jQuery('#fe_title').html(),
            content: cx.jQuery('#fe_content').html()
        };
        // load new page data, but don't reload and don't put data into content
        cx.fe.loadPageData(null, false);
    });
};

/**
 * Save a page
 * Does a request to the page jsonadapter to put the new values into the database
 */
cx.fe.savePage = function () {
    cx.fe.preparePageToSend();

    jQuery.post(cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=set', {
        'page': cx.fe.page
    }, function (response) {
        if (response.data != null) {
            var className = '';
            if (response.status != 'success') {
                className = ' class="error"';
            }
            cx.jQuery('<div' + className + '>' + response.message + '</div>').cxNotice();
            cx.jQuery.fn.cxDestroyDialogs(5000);
        }
        // load new page data, but don't reload and don't put data into content
        cx.fe.loadPageData(null, false);
    });
};

/**
 * Init the history. show the box when clicking on label.
 * Hide the history box after 2 seconds
 */
cx.fe.history = function () {
    cx.fe.history.hide();

    cx.jQuery('#fe_history_box_anchor').click(function () {
        if (cx.jQuery('#fe_history_box').css('display') == 'none') {
            cx.fe.options.hide();
            cx.fe.history.show();
            cx.fe.history.load();
        }
        return false;
    }).hover(function () {
            clearTimeout(cx.fe.history.displayTimeout);
        }, function () {
            cx.fe.history.displayTimeout = setTimeout(function () {
                cx.fe.history.hide();
            }, 2000);
        });
};

/**
 * show history anchor
 */
cx.fe.history.show = function () {
    cx.jQuery('#fe_history_arrow,#fe_history_box').show();
};

/**
 * hide the history anchor
 */
cx.fe.history.hide = function () {
    cx.jQuery('#fe_history_arrow,#fe_history_box').hide();
};

/**
 * Load history and put the history into the correct container
 * @param pos
 */
cx.fe.history.load = function (pos) {
    if (!pos) {
        pos = 0;
    }

    jQuery("#fe_history_box").html("<div class=\"historyInit\"><img src=\"" + cx.variables.get('basePath', 'contrexx') + "/lib/javascript/jquery/jstree/themes/default/throbber.gif\" alt=\"Loading...\" /></div>");
    jQuery('#fe_history_box').load(cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=getHistoryTable&page=' + cx.fe.page.id + '&pos=' + pos + '&limit=10', function () {
        jQuery("#history_paging").find("a").each(function (index, el) {
            el = jQuery(el);
            var pos;
            if (el.attr("class") == "pagingFirst") {
                pos = 0;
            } else {
                pos = el.attr("href").match(/pos=(\d*)/)[1];
            }
            el.data("pos", pos);
        }).attr("href", "#").click(function () {
                cx.fe.history.load(cx.jQuery(this).data("pos"));
            });
        cx.fe.history.updateHighlighting();
    });
};

/**
 * Remove functions for active history version
 */
cx.fe.history.updateHighlighting = function () {
    cx.jQuery('.historyLoad, .historyPreview').each(function () {
        if ((cx.jQuery(this).attr('id') == 'load_' + cx.fe.history.loadedVersion) || (cx.jQuery(this).attr('id') == 'preview_' + cx.fe.history.loadedVersion)) {
            cx.jQuery(this).css('display', 'none');
        } else {
            cx.jQuery(this).css('display', 'block');
        }
    });
}

/**
 * Init the options. show the box when clicking on label.
 * Hide the options box after 2 seconds
 */
cx.fe.options = function () {
    // hide options
    cx.fe.options.hide();
    cx.jQuery('#fe_options_box select[name="page[skin]"]').val(cx.fe.page.skin);
    cx.fe.options.reloadCustomContentTemplates();
    cx.jQuery('#fe_options_box select[name="page[customContent]"]').val(cx.fe.page.customContent);
    cx.jQuery('#fe_options_box input[name="page[cssName]"]').val(cx.fe.page.cssName);

    cx.jQuery('#fe_options_box_anchor').click(function () {
        if (cx.jQuery('#fe_options_box').css('display') == 'none') {
            cx.fe.history.hide();
            cx.fe.options.show();
            cx.fe.options.load();
        }
        return false;
    }).hover(function () {
            clearTimeout(cx.fe.options.displayTimeout);
        }, function () {
            cx.fe.options.displayTimeout = setTimeout(function () {
                cx.fe.options.hide();
            }, 2000);
        });

    cx.jQuery('#fe_options_box select[name="page[skin]"]').bind('change', function () {
        cx.fe.options.reloadCustomContentTemplates();
    });
};

/**
 * reload the custom content templates
 */
cx.fe.options.reloadCustomContentTemplates = function () {
    var skinId = jQuery('#fe_options_box select[name="page[skin]"]').val();
    var module = jQuery('#fe_options_box select[name="page[application]"]').val();
    var select = jQuery('#fe_options_box select[name="page[customContent]"]');
    select.empty();
    select.append(jQuery("<option value=\"\" selected=\"selected\">(Default)</option>"));

    // Default skin
    if (skinId == 0) {
        skinId = cx.variables.get('defaultTemplate', 'frontendEditing');
    }

    var templates = cx.variables.get('contentTemplates', "frontendEditing");
    if (templates[skinId] == undefined) {
        return;
    }

    for (var i = 0; i < templates[skinId].length; i++) {
        var isHome = /^home_/.exec(templates[skinId][i]);
        if ((isHome && module == "home") || !isHome && module != "home") {
            select.append(jQuery('<option>', {
                value: templates[skinId][i]
            }).text(templates[skinId][i]));
        }
    }
};

/**
 * show options anchor
 */
cx.fe.options.show = function () {
    cx.jQuery('#fe_options_arrow,#fe_options_box').show();
};

/**
 * hide the options anchor
 */
cx.fe.options.hide = function () {
    cx.jQuery('#fe_options_arrow,#fe_options_box').hide();
};

/**
 * load the options into the options container
 */
cx.fe.options.load = function () {
    cx.fe.options.reloadCustomContentTemplates();
};

/**
 * function which is called when the user clicks on "load" in history box
 * @param version
 */
loadHistoryVersion = function (version) {
    cx.fe.loadPageData(version, true);
};

/**
 * Dialogs
 */
(function ($) {
    var defaultOpts = {
        draggable: false,
        resizable: false,
        minWidth: 100,
        minHeight: 28,
        dialogClass: 'cxDialog noTitle'
    };

    $.fn.cxNotice = function (options) {
        $.fn.cxDestroyDialogs();

        var dialogOptions = {
            position: ['center', 'top']
        };
        var applicableOptions = $.extend({}, defaultOpts, dialogOptions, options);

        this.dialog(applicableOptions);

        return this;
    };

    $.fn.cxDestroyDialogs = function (delay) {
        if (delay !== undefined && delay > 0) {
            cx.fe.dialogTimeout = setTimeout(function () {
                cx.jQuery('.cxDialog .ui-dialog-content').dialog('destroy');
                clearTimeout(cx.fe.dialogTimeout);
            }, delay);
        } else {
            $('.cxDialog .ui-dialog-content').dialog('destroy');
        }
    };
})(cx.jQuery);
