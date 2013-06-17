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
 * Init the frontend editing when DOM is loaded and ready
 */
cx.ready(function() {
    cx.fe();
});

/**
 * Init the editor
 * Do some configurations and start the editor
 * Open the toolbar if it was active already
 */
cx.fe = function() {
    /**
     * Lang vars which are used for the template
     * write language variables from php to javascript variable
     * @type {Array}
     */
    cx.fe.langVars = cx.variables.get("langVars", "FrontendEditing");

    /**
     * Used as flag
     * The editor is not active by default
     * @type {boolean}
     */
    cx.fe.editMode = false;

    /**
     * Object for the page state which is published
     * Used as a temporary storage for page data
     * @type {{}}
     */
    cx.fe.publishedPage = {};

    /**
     * The state of the toolbar
     * Default: closed
     * @type {boolean}
     */
    cx.fe.toolbar_opened = false;

    // add the ckeditor plugins
    // not used at the moment, perhaps later when it will be possible to edit blocks
//    cx.fe.addCustomPlugins();

    // add the toolbar and hide the anchors
    cx.fe.toolbar();
    cx.fe.toolbar.hideAnchors();

    cx.jQuery("#fe_toolbar").show();
};

/**
 * Init the ckeditor for the content and title element
 * this line is necessary for the start and end method of contentEditor
 */
cx.fe.contentEditor = function() {
};

/**
 * Start content editor
 */
cx.fe.contentEditor.start = function() {
    // set a flag to true
    cx.fe.editMode = true;

    // add border around the editable contents
    cx.jQuery("#fe_content,#fe_title").attr("contenteditable", true).addClass('fe_outline');

    // check for publish permission and add publish button
    // not used at the moment, perhaps later if it is possible to edit blocks
    var extraPlugins = [];
//    var extraPlugins = ["save"];
//    if (cx.variables.get("hasPublishPermission", "FrontendEditing")) {
//        extraPlugins.push("publish");
//    }

    // init empty js object for storing the data
    cx.fe.publishedPage = {};

    // init the editors if the editor is not already initialized
    if (!CKEDITOR.instances.fe_title) {
        CKEDITOR.inline("fe_title", {
            customConfig: CKEDITOR.getUrl(cx.variables.get("configPath", "FrontendEditing")),
            toolbar: "FrontendEditingTitle",
            forcePasteAsPlainText: true,
            extraPlugins: extraPlugins.join(","),
            basicEntities: false,
            entities: false,
            entities_latin: false,
            entities_greek: false,
            on: {
                instanceReady: function() {
                    cx.fe.publishedPage.title = CKEDITOR.instances.fe_title.getData()
                },
                blur: function() {
                    if (cx.fe.pageHasBeenModified() && cx.fe.confirmSaveAsDraft()) {
                        cx.fe.savePage();
                    }
                }
            }
        });
    }
    if (!CKEDITOR.instances.fe_content) {
        CKEDITOR.inline("fe_content", {
            customConfig: CKEDITOR.getUrl(cx.variables.get("configPath", "FrontendEditing")),
            toolbar: "FrontendEditingContent",
            extraPlugins: extraPlugins.join(","),
            startupOutlineBlocks: false,
            on: {
                instanceReady: function() {
                    cx.fe.publishedPage.content = CKEDITOR.instances.fe_content.getData()
                },
                blur: function() {
                    if (cx.fe.pageHasBeenModified() && cx.fe.confirmSaveAsDraft()) {
                        cx.fe.savePage();
                    }
                }
            }
        });
    }
};

/**
 * stop the edit mode
 */
cx.fe.contentEditor.stop = function() {
    // set flag to false
    cx.fe.editMode = false;

    // if whether the CKEDITORs exist
    // why? they don"t exist if the page is an application page
    if (CKEDITOR.instances.fe_content != undefined && CKEDITOR.instances.fe_title != undefined) {
        // load last published content if the page in the current editor is a draft
        if (cx.fe.pageHasBeenModified()) {
            if (cx.fe.confirmSaveAsDraft()) {
                cx.fe.savePage();
            }
            cx.jQuery("#fe_title").html(cx.fe.publishedPage.title);
            cx.jQuery("#fe_content").html(cx.fe.publishedPage.content);
        }

        // destroy and remove the inline editor
        CKEDITOR.instances.fe_content.destroy();
        CKEDITOR.instances.fe_title.destroy();
    }

    // remove outline of editable content divs
    cx.jQuery("#fe_content,#fe_title").attr("contenteditable", false).removeClass('fe_outline');

    // remove status message
    cx.jQuery.fn.cxDestroyDialogs();

    // remove history and options
    cx.fe.toolbar.hideAnchors();

    // hide publish button
    cx.jQuery("#fe_toolbar_publishPage").hide();
};

cx.fe.pageHasBeenModified = function() {
    return CKEDITOR.instances.fe_title.getData() != cx.fe.publishedPage.title ||
        CKEDITOR.instances.fe_content.getData() != cx.fe.publishedPage.content ||
        cx.jQuery("#fe_options .fe_box select[name=\"page[skin]\"]").val() != cx.fe.page.skin ||
        cx.jQuery("#fe_options .fe_box select[name=\"page[customContent]\"]").val() != cx.fe.page.customContent ||
        cx.jQuery("#fe_options .fe_box input[name=\"page[cssName]\"]").val() != cx.fe.page.cssName;
};

/**
 * Ask to save as draft if the content or the options has been edited
 * @returns {*}
 */
cx.fe.confirmSaveAsDraft = function() {
    return confirm(cx.fe.langVars.TXT_FRONTEND_EDITING_SAVE_CURRENT_STATE);
};

/**
 * Init the toolbar
 * Show the toolbar if the cookie for the toolbar is set what means that it was opened
 * in the last session
 */
cx.fe.toolbar = function() {
    // is toolbar already opened from last session
    cx.fe.toolbar_opened = cx.jQuery.cookie("fe_toolbar") == "true";

    // if it was opened the last time, open now or hide
    if (cx.fe.toolbar_opened) {
        cx.fe.toolbar.show();
    } else {
        cx.fe.toolbar.hide();
    }

    // add click handler for toolbar tab
    cx.jQuery("#fe_toolbar_tab").click(function() {
        if (cx.fe.toolbar_opened) {
            cx.fe.toolbar.hide();
        } else {
            cx.fe.toolbar.show();
        }
    });

    // start / stop edit mode button
    cx.jQuery("#fe_toolbar_startEditMode").html(cx.fe.langVars.TXT_FRONTEND_EDITING_EDIT).click(function() {
        if (cx.fe.editMode) {
            // if the edit mode was active, stop the editor
            cx.jQuery(this).html(cx.fe.langVars.TXT_FRONTEND_EDITING_EDIT);
            cx.fe.contentEditor.stop();
        } else {
            // if the edit mode was not active, start the editor
            cx.jQuery(this).html(cx.fe.langVars.TXT_FRONTEND_EDITING_STOP_EDIT);

            // load newest version, draft or published and refresh the editor's content
            cx.fe.loadPageData(null, true, function() {
                cx.fe.history();
                cx.fe.options();

                // check whether the content is editable or not
                // don't show inline editor for module pages, except home
                if (cx.fe.page.type == "content" || (cx.fe.page.type == "application" && cx.fe.page.module == "home")) {
                    // init the inline ckeditor
                    cx.fe.contentEditor.start();
                    cx.fe.toolbar.showAnchors(true, true); // show both anchors, history and options
                } else {
                    cx.fe.editMode = true;
                    cx.jQuery("<div class=\"info\">" + cx.fe.langVars.TXT_FRONTEND_EDITING_MODULE_PAGE + "</div>").cxNotice();
                    cx.fe.toolbar.showAnchors(false, true); // only show option anchor, hide history anchor
                }
            });
            // show publish button
            cx.jQuery("#fe_toolbar_publishPage").show();
        }
        return false;
    });

    // init publish button and hide it
    cx.jQuery("#fe_toolbar_publishPage")
        .html(
            cx.variables.get("hasPublishPermission", "FrontendEditing") ?
                cx.fe.langVars.TXT_FRONTEND_EDITING_PUBLISH :
                cx.fe.langVars.TXT_FRONTEND_EDITING_SUBMIT_FOR_RELEASE
        ).click(function() {
            if (!cx.fe.editMode) {
                return false;
            }
            cx.fe.publishPage();
            return false;
        }).hide();

    // init the admin menu anchor and box
    // not used for contrexx 3.1
//    cx.fe.adminmenu();
};

/**
 * Hide the toolbar
 */
cx.fe.toolbar.hide = function() {
    // hide anchor boxes
    cx.fe.toolbar.hideBoxes();

    // do the css
    cx.jQuery("#fe_toolbar").css("top", "-" + cx.jQuery("#fe_toolbar").height() + "px");
    cx.jQuery("body").css("padding-top", "0px");

    // do the html
    cx.jQuery("#fe_toolbar_tab").html(cx.fe.langVars.TXT_FRONTEND_EDITING_SHOW_TOOLBAR);

    // save the status
    cx.fe.toolbar_opened = false;
    cx.jQuery.cookie("fe_toolbar", cx.fe.toolbar_opened);
};

/**
 * Show the toolbar
 */
cx.fe.toolbar.show = function() {
    // do the css
    cx.jQuery("body").css("padding-top", cx.jQuery("#fe_toolbar").height() + "px");
    cx.jQuery("#fe_toolbar").css({
        top: "0px"
    });

    // do the html
    cx.jQuery("#fe_toolbar_tab").html(cx.fe.langVars.TXT_FRONTEND_EDITING_HIDE_TOOLBAR);

    // save the status
    cx.fe.toolbar_opened = true;
    cx.jQuery.cookie("fe_toolbar", cx.fe.toolbar_opened);
};


/**
 * Init the administration menu
 */
cx.fe.adminmenu = function() {
    cx.jQuery("#fe_adminmenu .fe_box").find("a").each(function(index, el) {
        cx.jQuery(el).click(function() {
            // ajax to backend link, show in cx.ui.dialog
            cx.ui.dialog({
                title: cx.jQuery(this).text(),
                modal: true,
                content: "<iframe style=\"height: 100%; width: 100%;\" src=\"" + cx.jQuery(this).attr("href").replace(/de\/index.php/, "cadmin/index.php") + "\" />"
            });
            // replace all link and form targets by this javascript handler
            return false;
        });
    });

    cx.jQuery("#fe_adminmenu").click(function() {
        if (cx.jQuery("#fe_adminmenu .fe_box").css("display") == "none") {
            cx.fe.toolbar.hideBoxes();
            cx.fe.adminmenu.show();
        }
        return false;
    }).hover(
        function() {
            clearTimeout(cx.fe.adminmenu.displayTimeout);
        },
        function() {
            cx.fe.adminmenu.displayTimeout = setTimeout(function() {
                cx.fe.adminmenu.hide();
            }, 2000);
        }
    );
};

/**
 * Show admin menu box
 */
cx.fe.adminmenu.show = function() {
    cx.jQuery("#fe_adminmenu .fe_toggle").show();
};

/**
 * Hide admin menu box
 */
cx.fe.adminmenu.hide = function() {
    cx.jQuery("#fe_adminmenu .fe_toggle").hide();
};

/**
 * hide history and options anchors
 */
cx.fe.toolbar.hideAnchors = function() {
    cx.jQuery("#fe_metanavigation .fe_anchor.fe_toggle").hide();
    cx.fe.toolbar.hideBoxes();
};

/**
 * hide boxes of anchors
 */
cx.fe.toolbar.hideBoxes = function() {
    cx.jQuery("#fe_metanavigation .fe_anchor .fe_toggle").hide();
};

/**
 * show history and options anchors
 * @param history
 * @param options
 */
cx.fe.toolbar.showAnchors = function(history, options) {
    if (history) {
        cx.jQuery("#fe_history").show();
    }
    if (options) {
        cx.jQuery("#fe_options").show();
    }
};

/**
 * prepare Page for sending
 *
 * replace true and false statements to "on" and "off"
 */
cx.fe.preparePageToSend = function() {
    // load data from options box and write to page object
    cx.fe.page.title = CKEDITOR.instances.fe_title.getData();
    cx.fe.page.content = CKEDITOR.instances.fe_content.getData();
    cx.fe.page.application = cx.fe.page.module;
    cx.fe.page.skin = cx.jQuery("#fe_options .fe_box select[name=\"page[skin]\"]").val();
    cx.fe.page.customContent = cx.jQuery("#fe_options .fe_box select[name=\"page[customContent]\"]").val();
    cx.fe.page.cssName = cx.jQuery("#fe_options .fe_box input[name=\"page[cssName]\"]").val();

    // rewrite true and false to on and off
    cx.fe.page.scheduled_publishing = (cx.fe.page.scheduled_publishing === true ? "on" : "off");
    cx.fe.page.protection_backend = (cx.fe.page.protection_backend === true ? "on" : "off");
    cx.fe.page.protection_frontend = (cx.fe.page.protection_frontend === true ? "on" : "off");
    cx.fe.page.caching = (cx.fe.page.caching === true ? "on" : "off");
    cx.fe.page.sourceMode = (cx.fe.page.sourceMode === true ? "on" : "off");
    cx.fe.page.metarobots = (cx.fe.page.metarobots === true ? "on" : "off");
};

/**
 * load the page data
 * @param historyId
 * @param putTheData
 * @param callback
 */
cx.fe.loadPageData = function(historyId, putTheData, callback) {
    var url = cx.variables.get("basePath", "contrexx") + "cadmin/index.php?cmd=jsondata&object=page&act=get&page=" + cx.variables.get("pageId", "FrontendEditing") + "&lang=" + cx.jQuery.cookie("langId") + "&userFrontendLangId=" + cx.jQuery.cookie("langId");
    if (historyId) {
        url += "&history=" + historyId;
    }
    jQuery.ajax({
        url: url,
        complete: function(response) {
            // get the page json data response
            cx.fe.page = jQuery.parseJSON(response.responseText).data;

            // check whether the page is a content page or a home page
            // the application pages do not allow to update title and content
            if (putTheData && (cx.fe.page.type != "application" || cx.fe.page.module == "home")) {
                // put the new data of page into the html and start editor if the user is in edit mode
                cx.jQuery("#fe_title").html(cx.fe.page.title);
                cx.jQuery("#fe_content").html(cx.fe.page.content);
                // when the editor is in the edit mode, restart the content editor
                if (cx.fe.editMode) {
                    cx.fe.contentEditor.start();
                }
            }

            // a specific history is requested
            if (historyId) {
                cx.fe.history.loadedVersion = historyId;
            } else {
                // no specific history requested
                // check if the current page is a draft
                // if it is a draft, load the previous history
                if (cx.fe.pageIsADraft()) {
                    cx.fe.history.loadedVersion = cx.fe.page.historyId - 1;
                } else {
                    // load the current history
                    cx.fe.history.loadedVersion = cx.fe.page.historyId;
                }
            }

            // update the history highlighting in history box
            cx.fe.history.updateHighlighting();

            // call the callback function after loading the content from db
            if (callback) {
                callback();
            }

            // if it is a draft tell the user that he is editing a draft
            if (cx.fe.pageIsADraft() &&
                cx.fe.editMode &&
                (
                    (historyId && historyId == cx.fe.page.historyId - 1) || !historyId
                    )
                ) {
                if (cx.fe.dialogTimeout > 0) {
                    setTimeout(function() {
                        cx.jQuery("<div class=\"warning\">" + cx.fe.langVars.TXT_FRONTEND_EDITING_THE_DRAFT + "</div>").cxNotice();
                        clearTimeout(cx.fe.dialogTimeout);
                    }, 5000);
                } else {
                    cx.jQuery("<div class=\"warning\">" + cx.fe.langVars.TXT_FRONTEND_EDITING_THE_DRAFT + "</div>").cxNotice();
                }
            }

            // reload the boxes
            cx.fe.history.load();
            cx.fe.options.load();
        }
    });
};

/**
 * Returns true when the page is a draft, false if not
 * @returns {boolean}
 */
cx.fe.pageIsADraft = function() {
    if (cx.fe.page.editingStatus == "hasDraft" ||
        cx.fe.page.editingStatus == "hasDraftWaiting"
        ) {
        return true;
    }
    return false;
};

/**
 * Does a request to publish the new contents
 */
cx.fe.publishPage = function() {
    cx.fe.preparePageToSend();

    jQuery.post(
        cx.variables.get("basePath", "contrexx") + "cadmin/index.php?cmd=jsondata&object=page&act=set",
        {
            action: "publish",
            page: cx.fe.page
        },
        function(response) {
            var className = "success";
            if (response.status != "success") {
                className = "error";
            }

            // remove all dialogs
            cx.jQuery.fn.cxDestroyDialogs();
            cx.jQuery("<div class=\"" + className + "\">" + response.message + "</div>").cxNotice();
            cx.jQuery.fn.cxDestroyDialogs(5000);

            cx.fe.publishedPage = {
                title: CKEDITOR.instances.fe_title.getData(),
                content: CKEDITOR.instances.fe_content.getData()
            };
            // load new page data, but don't reload and don't put data into content
            cx.fe.loadPageData(null, false);
        }
    );
};

/**
 * Save a page
 * Does a request to the page jsonadapter to put the new values into the database
 */
cx.fe.savePage = function() {
    cx.fe.preparePageToSend();

    jQuery.post(
        cx.variables.get("basePath", "contrexx") + "cadmin/index.php?cmd=jsondata&object=page&act=set",
        {
            page: cx.fe.page
        },
        function(response) {
            if (response.data != null) {
                var className = "success";
                if (response.status != "success") {
                    className = "error";
                }
                cx.jQuery("<div class=\"" + className + "\">" + response.message + "</div>").cxNotice();
                cx.jQuery.fn.cxDestroyDialogs(5000);
            }
            // load new page data, but don't reload and don't put data into content
            cx.fe.loadPageData(null, false);
        }
    );
};

/**
 * Init the history. Show the box when clicking on label.
 * Hide the history box after 2 seconds
 */
cx.fe.history = function() {
    cx.jQuery("#fe_history").click(function() {
        if (cx.jQuery("#fe_history .fe_box").css("display") != "none") {
            return false;
        }
        cx.fe.toolbar.hideBoxes();
        cx.fe.history.show();
        cx.fe.history.load();
    }).hover(
        function() {
            clearTimeout(cx.fe.history.displayTimeout);
        },
        function() {
            cx.fe.history.displayTimeout = setTimeout(function() {
                cx.fe.history.hide();
            }, 2000);
        }
    );
};

/**
 * show history anchor
 */
cx.fe.history.show = function() {
    cx.jQuery("#fe_history .fe_toggle").show();
};

/**
 * hide the history anchor
 */
cx.fe.history.hide = function() {
    cx.jQuery("#fe_history .fe_toggle").hide();
};

/**
 * Load history and put the history into the correct container
 * @param pos
 */
cx.fe.history.load = function(pos) {
    if (!pos) {
        pos = 0;
    }

    jQuery("#fe_history .fe_box").html("<div class=\"historyInit\"><img src=\"" + cx.variables.get("basePath", "contrexx") + "/lib/javascript/jquery/jstree/themes/default/throbber.gif\" alt=\"Loading...\" /></div>");
    jQuery("#fe_history .fe_box").load(
        cx.variables.get("basePath", "contrexx") + "cadmin/index.php?cmd=jsondata&object=page&act=getHistoryTable&page=" + cx.fe.page.id + "&pos=" + pos + "&limit=10",
        function() {
            jQuery("#history_paging").find("a").each(function(index, el) {
                el = jQuery(el);
                var pos;
                if (el.attr("class") == "pagingFirst") {
                    pos = 0;
                } else {
                    pos = el.attr("href").match(/pos=(\d*)/)[1];
                }
                el.data("pos", pos);
            }).attr("href", "#").click(function() {
                    cx.fe.history.load(cx.jQuery(this).data("pos"));
                });
            cx.fe.history.updateHighlighting();
        }
    );
};

/**
 * Remove functions for active history version
 */
cx.fe.history.updateHighlighting = function() {
    cx.jQuery(".historyLoad, .historyPreview").each(function() {
        if (cx.jQuery(this).attr("id") == "load_" + cx.fe.history.loadedVersion ||
            cx.jQuery(this).attr("id") == "preview_" + cx.fe.history.loadedVersion) {
            cx.jQuery(this).css("display", "none");
        } else {
            cx.jQuery(this).css("display", "block");
        }
    });
}

/**
 * Init the options. show the box when clicking on label.
 * Hide the options box after 2 seconds
 */
cx.fe.options = function() {
    // hide options
    cx.jQuery("#fe_options .fe_box select[name=\"page[skin]\"]").val(cx.fe.page.skin);
    cx.fe.options.reloadCustomContentTemplates();
    cx.jQuery("#fe_options .fe_box select[name=\"page[customContent]\"]").val(cx.fe.page.customContent);
    cx.jQuery("#fe_options .fe_box input[name=\"page[cssName]\"]").val(cx.fe.page.cssName);

    cx.jQuery("#fe_options").click(function() {
        if (cx.jQuery("#fe_options .fe_box").css("display") == "none") {
            cx.fe.toolbar.hideBoxes();
            cx.fe.options.show();
            cx.fe.options.load();
        }
        return false;
    }).hover(
        function() {
            clearTimeout(cx.fe.options.displayTimeout);
        },
        function() {
            cx.fe.options.displayTimeout = setTimeout(function() {
                cx.fe.options.hide();
            }, 2000);
        }
    );

    cx.jQuery("#fe_options .fe_box select[name=\"page[skin]\"]").bind("change", function() {
        cx.fe.options.reloadCustomContentTemplates();
    });
};

/**
 * reload the custom content templates
 */
cx.fe.options.reloadCustomContentTemplates = function() {
    var skinId = cx.jQuery("#fe_options .fe_box select[name=\"page[skin]\"]").val();
    var application = cx.jQuery("#fe_options .fe_box select[name=\"page[application]\"]").val();
    var select = cx.jQuery("#fe_options .fe_box select[name=\"page[customContent]\"]");
    select.empty();
    select.append(cx.jQuery("<option value=\"\" selected=\"selected\">(Default)</option>"));

    // Default skin
    if (skinId == 0) {
        skinId = cx.variables.get("defaultTemplate", "FrontendEditing");
    }

    var templates = cx.variables.get("contentTemplates", "FrontendEditing");
    if (templates[skinId] == undefined) {
        return;
    }

    for (var i = 0; i < templates[skinId].length; i++) {
        var isHome = /^home_/.exec(templates[skinId][i]);
        if ((isHome && application == "home") || !isHome && application != "home") {
            select.append(cx.jQuery("<option>", {
                value: templates[skinId][i]
            }).text(templates[skinId][i]));
        }
    }
};

/**
 * show options anchor
 */
cx.fe.options.show = function() {
    cx.jQuery("#fe_options .fe_toggle").show();
};

/**
 * hide the options anchor
 */
cx.fe.options.hide = function() {
    cx.jQuery("#fe_options .fe_toggle").hide();
};

/**
 * load the options into the options container
 */
cx.fe.options.load = function() {
    cx.fe.options.reloadCustomContentTemplates();
};

/**
 * function which is called when the user clicks on "load" in history box
 * @param version
 */
loadHistoryVersion = function(version) {
    cx.fe.loadPageData(version, true);
};

/**
 * Dialogs
 */
(function($) {
    var defaultOpts = {
        draggable: false,
        resizable: false,
        minWidth: 100,
        minHeight: 28,
        dialogClass: "cxDialog noTitle"
    };

    $.fn.cxNotice = function(options) {
        $.fn.cxDestroyDialogs();

        var dialogOptions = {
            position: ["center", "top"]
        };
        var applicableOptions = $.extend({}, defaultOpts, dialogOptions, options);

        this.dialog(applicableOptions);

        return this;
    };

    $.fn.cxDestroyDialogs = function(delay) {
        if (delay !== undefined && delay > 0) {
            cx.fe.dialogTimeout = setTimeout(function() {
                cx.jQuery(".cxDialog .ui-dialog-content").dialog("destroy");
                clearTimeout(cx.fe.dialogTimeout);
            }, delay);
        } else {
            $(".cxDialog .ui-dialog-content").dialog("destroy");
        }
    };
})(cx.jQuery);
