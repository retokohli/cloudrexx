var baseUrl = 'index.php?cmd=content';

var mouseIsUp = true;
jQuery(document).bind('mouseup.global', function() {
    mouseIsUp = true;
}).bind('mousedown.global', function() {
    mouseIsUp = false;
});

//called from links in history table.
loadHistoryVersion = function(version) {
    pageId = parseInt(jQuery('#pageId').val());
    if (isNaN(pageId)) {
        return;
    }
    
    cx.cm.loadPage(pageId, 0, version, "content", false);
};

jQuery.extend({
    getUrlVars: function(){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    },
    getUrlVar: function(name){
        try {
            return jQuery.getUrlVars()[name];
        } catch (ex) {
            return undefined;
        }
    },
    ucfirst: function(string) {
        return (string.charAt(0).toUpperCase() + string.substr(1));
    },
    lcfirst: function(string) {
        return (string.charAt(0).toLowerCase() + string.substr(1));
    }
});

jQuery.fn.equals = function(compareTo) {
    if (!compareTo || !compareTo.length || this.length!=compareTo.length) {
        return false;
    }
    for (var i=0; i<this.length; i++) {
        if (this[i]!==compareTo[i]) {
            return false;
        }
    }
    return true;
}

var arrayContains = function(array, value) {
    if (array == null) return false;
    for (var i = 0; i < array.length; i++) {
        if (array[i] == value) return true;
    }
    return false;
};

var fillSelect = function(select, data) {
    select.empty();
    jQuery.each(data.groups, function(group, id) {
        var selected = arrayContains(data.assignedGroups, group);
        var option = jQuery('<option></option>');
        option.html(id);
        option.val(group);
        if (selected)
            option.attr('selected','selected');

        option.appendTo(select);
    });

    select.multiselect2side({
        selectedPosition: 'right',
        moveOptions: false,
        labelsx: '',
        labeldx: '',
        autoSort: true,
        autoSortAvailable: true
    });
};

reloadCustomContentTemplates = function() {
    var skinId = jQuery('#page select[name="page[skin]"]').val();
    var module = jQuery('#page select[name="page[application]"]').val();
    var select = jQuery('#page select[name="page[customContent]"]');
    var lastChoice = select.data('sel');
    select.empty();
    select.append(jQuery("<option value=\"\" selected=\"selected\">(Default)</option>"));
    
    // Default skin
    if (skinId == 0) {
        skinId = cx.variables.get('defaultTemplates', 'contentmanager/themes')[cx.cm.getCurrentLang()];
    }
    
    var templates = cx.variables.get('contentTemplates', "contentmanager");
    if (templates[skinId] == undefined) {
        return;
    }
    
    for (var i = 0; i < templates[skinId].length; i++) {
        var isHome = /^home_/.exec(templates[skinId][i]);
        if ((isHome && module == "home") || !isHome && module != "home") {
            select.append(jQuery('<option>', {
                value : templates[skinId][i]
            }).text(templates[skinId][i]));
        }
    }
};

/* Dialogs */
(function($) {
    defaultOpts = {
        draggable: false,
        resizable: false,
        minWidth: 100,
        minHeight: 28,
        dialogClass: 'cxDialog noTitle'
    };

    $.fn.cxDialog = function(options) {
        $.fn.cxDestroyDialogs();

        dialogOptions = {};
        applicableOptions = $.extend({}, defaultOpts, dialogOptions, options); 

        this.dialog(applicableOptions);

        return this;
    }

    $.fn.cxNotice = function(options) {
        $.fn.cxDestroyDialogs();

        dialogOptions = {
            position: ['center', 'top']
        };
        applicableOptions = $.extend({}, defaultOpts, dialogOptions, options);

        this.dialog(applicableOptions);

        return this;
    }

    $.fn.cxDestroyDialogs = function(delay) {
        if (delay !== undefined && delay > 0) {
            setTimeout("jQuery('.cxDialog .ui-dialog-content').dialog('destroy')", 10000);
        } else {
            jQuery('.cxDialog .ui-dialog-content').dialog('destroy');
        }
    }
})(jQuery);

cx.ready(function() {
    // wheter expand all was once called on the tree
    cx.cm.all_opened = false;
    // are we opening all nodes at the moment?
    cx.cm.is_opening = false;
    
    jQuery('#page_target_browse').click(function() {
        url = '?cmd=fileBrowser&csrf='+cx.variables.get('csrf', 'contrexx')+'&standalone=true&type=webpages';
        opts = 'width=800,height=600,resizable=yes,status=no,scrollbars=yes';
        window.open(url, 'target', opts).focus();
        return false;
    });
    window.SetUrl = function(url) {
        jQuery('#page_target').val(url);
    }
    
    jQuery('.jstree-action').click(function(event) {
        event.preventDefault();
        action = jQuery(this).attr('class').split(' ')[1];
        if (action == "open" && !cx.cm.all_opened) {
            // no need to get the whole tree twice
            cx.cm.all_opened = true;
            cx.cm.is_opening = true;
            jQuery('#loading').cxNotice();
            jQuery("#site-tree").hide();
            // get complete tree
            jQuery.ajax({
                url: "?cmd=jsondata&object=node&act=getTree&recursive=true",
                dataType: 'json',
                success: function(response) {
                    if (!response.data) {
                        return;
                    }
                    if (cx.cm.actions == undefined) {
                        cx.cm.actions = response.data.actions;
                    } else {
                        jQuery(languages).each(function(index, lang) {
                            jQuery.extend(cx.cm.actions[lang], response.data.actions[lang]);
                        });
                    }
                    // add tree data to jstree (replace tree by new one) and open all nodes
                    cx.cm.createJsTree(jQuery("#site-tree"), response.data.tree, response.data.nodeLevels, true);
                }
            });
        } else {
            jQuery('#site-tree').jstree(action+'_all');
        }
    });
    
    jQuery('#multiple-actions-select').change(function() {
        data = new Object();
        data.action = jQuery(this).children('option:selected').val();
        if (data.action == '0') {
            return false;
        }
        data.lang  = jQuery('#site-tree').jstree('get_lang');
        data.nodes = new Array();
        jQuery('#site-tree ul li.jstree-checked').not(".action-item").each(function() {
            nodeId = jQuery(this).attr('id').match(/\d+$/)[0];
            data.nodes.push(nodeId);
        });
        data.currentNodeId = jQuery('input#pageNode').val();
        if ((data.action != '') && (data.lang != '') && (data.nodes.length > 0)) {
            object = (data.action == 'delete') ? 'node'   : 'page';
            act    = (data.action == 'delete') ? 'Delete' : 'Set';
            var recursive = false;
            if (data.action == 'delete') {
                if (!cx.cm.confirmDeleteNode()) return;
            } else {
                recursive = cx.cm.askRecursive();
            }
            if (recursive) {
                recursive = "&recursive=true";
            } else {
                recursive = "";
            }
            var multipleActionAjaxRequest = function(offset) {
                if (offset) {
                    offset = "&offset=" + offset;
                } else {
                    offset = "";
                }
                jQuery.ajax({
                    type: 'POST',
                    url:  'index.php?cmd=jsondata&object='+object+'&act=multiple'+act+recursive+offset,
                    data: data,
                    success: function(json) {
                        if (json.state && json.state == 'timeout') {
                            multipleActionAjaxRequest(json.offset);
                            return;
                        }
                        jQuery('#multiple-actions-select').val(0);
                        cx.cm.createJsTree(jQuery("#site-tree"), json.data.tree, json.data.nodeLevels, false);
                        if (data.action == 'delete') {
                            if (json.data.deletedCurrentPage) {
                                cx.cm.hideEditView();
                            }
                        } else {
                            if (json.data.id > 0) {
                                cx.cm.loadPage(json.data.id, undefined, undefined, undefined, false);
                            }
                        }
                    }
                });
            };
            multipleActionAjaxRequest();
        } else {
            jQuery('#multiple-actions-select').val(0);
        }
    });
    
    // aliases:
    if (!publishAllowed) {
        jQuery("div.page_alias").each(function (index, field) {
            field = jQuery(field);
            field.removeClass("empty");
            if (field.children("span.noedit").html() == "") {
                field.addClass("empty");
            }
        });
        jQuery(".empty").hide();
    }
    
    // alias input fields
    jQuery("div.page_alias input").keyup(function() {
        var me = jQuery(this).closest("div.page_alias");
        jQuery("div.page_alias input.warning").removeClass("warning");
        
        var text = jQuery(this).val();
        text = text.replace(/\s/, '-');
        text = text.replace(/[^a-zA-Z0-9-_]/, '');
        jQuery(this).val(text);
        // remove unused alias input fields
        // do not remove the last input field
        if (jQuery(this).val() == "") {
            var emptyCount = 0;
            jQuery("div.page_alias").each(function(index, el) {
                if (jQuery(el).children("input").val() == "") {
                    emptyCount++;
                }
            });
            if (emptyCount > 1) {
                jQuery(this).closest("div.page_alias").remove();
            }
        }
        // highlight same text
        jQuery("div.page_alias").each(function(index, el) {
            var me = jQuery(el);
            if (me.children("input").val() != "") {
                me.children("input").attr("id", "page_alias_" + index);
            }
            jQuery("div.page_alias").each(function(index, el) {
                var it = jQuery(el);
                if (me.get(0) == it.get(0)) {
                    return true;
                }
                if (me.children("input").val() == it.children("input").val()) {
                    me.children("input").addClass("warning");
                    it.children("input").addClass("warning");
                    return false;
                }
            });
        });
        // add new alias input fields
        if (jQuery(this).val() != "") {
            var hasEmpty = false;
            jQuery("div.page_alias").each(function(index, el) {
                if (jQuery(el).children("input").val() == "") {
                    // there is already a empty field
                    hasEmpty = true;
                    return;
                }
            });
            if (!hasEmpty) {
                var parent = jQuery(this).parent("div.page_alias");
                var clone = parent.clone(true);
                clone.children("input").val("");
                clone.children("input").attr("id", "page_alias");
                clone.insertAfter(parent);
            }
        }
    });
    
    var data = jQuery.parseJSON(cx.variables.get("tree-data", "contentmanager/tree"));
    cx.cm.actions = data.data.actions;
    cx.cm.createJsTree(jQuery("#site-tree"), data.data.tree, data.data.nodeLevels);

    jQuery(".chzn-select").chosen().change(function() {
        var str = "";
        jQuery("select.chzn-select option:selected").each(function () {
            str += jQuery(this).attr('value');
        });
        cx.cm.setCurrentLang(str);
        var dpOptions = {
            showSecond: false,
            dateFormat: 'dd.mm.yy',
            timeFormat: 'hh:mm' 
        };
        jQuery("input.date").datetimepicker(dpOptions);

        node = jQuery('#page input[name="page[node]"]').val();
        //pageId = jQuery('#node_'+node+" a."+str).attr("id");
        pageId = jQuery('#pageId').val();
        
        // get translated page id (page->getNode()->getPage(lang)->getId())
        if (pageId) {
            pageId = jQuery('li#node_'+node).children('.'+str).attr('id');
        }
        
        if (fallbacks[str]) {
            jQuery('.hidable_nofallback').show();
            jQuery('#fallback').text(language_labels[fallbacks[str]]);
        } else {
            jQuery('.hidable_nofallback').hide();
        }
        if (pageId && pageId != "new") {
            cx.cm.loadPage(pageId, node);
        } else {
            jQuery('#page input[name="source_page"]').val(jQuery('#page input[name="page[id]"]').val());
            jQuery('#page input[name="page[id]"]').val("new");
            jQuery('#page input[name="page[lang]"]').val(str);
            jQuery('#page input[name="page[node]"]').val(node);
            jQuery('#page #preview').attr('href', cx.variables.get('basePath', 'contrexx') + str + '/index.php?pagePreview=1');
        }

        jQuery('#site-tree>ul li .jstree-wrapper').each(function() {
            jsTreeLang = jQuery('#site-tree').jstree('get_lang');
            jQuery(this).children('.module.show, .preview.show, .lastupdate.show').removeClass('show').addClass('hide');
            jQuery(this).children('.module.'+jsTreeLang + ', .preview.'+jsTreeLang + ', .lastupdate.' + jsTreeLang).toggleClass('show hide');
        });
    });
    jQuery(".chzn-select").trigger('change');

    jQuery('div.actions-expanded li.action-item').live('click', function(event) {
        var classes =  jQuery(event.target).attr("class").split(/\s+/);
        var url = jQuery(event.target).attr('data-href');
        var lang = jQuery('#site-tree').jstree('get_lang');
        
        var action = classes[1];
        var pageId = jQuery(event.target).closest(".jstree-wrapper").nextAll("a." + lang).attr("id");
        var nodeId = jQuery(event.target).closest(".jstree-wrapper").parent().attr("id").split("_")[1];
        
        cx.cm.performAction(action, pageId, nodeId);
        
        jQuery(event.target).closest('.actions-expanded').hide();
    });

    //add callback to reload custom content templates available as soon as template or module changes
    jQuery('#page select[name="page[skin]"]').bind('change', function() {
        reloadCustomContentTemplates();
    });

    jQuery('#page_skin_view, #page_skin_edit').click(function(event) {
        var themeId = 0;
        var themeName = "";
        if (jQuery('#page_skin').val() != '') {
            themeId = jQuery('#page_skin').val();
            themeName = jQuery('#page_skin option:selected').text();
        } else {
            themeId = cx.variables.get('themeId', 'contentmanager/theme');
            themeName = cx.variables.get('themeName', 'contentmanager/theme');
        }
        
        if (themeId == 0) {
            themeId = cx.variables.get('defaultTemplates', 'contentmanager/themes')[cx.cm.getCurrentLang()];
        }

        if (jQuery(event.currentTarget).is('#page_skin_view')) {
            window.open('../index.php?preview='+themeId);
        } else {
            window.open('index.php?cmd=skins&act=templates&themes='+cx.variables.get("templateFolders", "contentmanager/themes")[themeId]+'&csrf='+cx.variables.get('csrf', 'contrexx'));
        }
    });

    jQuery('#page select[name="page[application]"]').bind('blur', function() {
        reloadCustomContentTemplates();
    });

    // react to get ?loadpage=
    /*if (jQuery.getUrlVar('loadPage')) {
        cx.cm.loadPage(jQuery.getUrlVar('loadPage'));
    }*/
    if (jQuery.getUrlVar("page") || jQuery.getUrlVar("node")) {
        cx.cm.loadPage(jQuery.getUrlVar("page"), jQuery.getUrlVar("node"), jQuery.getUrlVar("version"), jQuery.getUrlVar("tab"));
    }

    cx.cm();
});

cx.cm = function(target) {
    cx.cm.initHistory();
    
    var dpOptions = {
        showSecond: false,
        dateFormat: 'dd.mm.yy',
        timeFormat: 'hh:mm',
        buttonImage: "template/ascms/images/calender.png",
        buttonImageOnly: true
    };
    jQuery("input.date").datetimepicker(dpOptions);

    $J('#page input[name="page[slug]"]').keyup(function() {
        $J('#liveSlug').text($J('#page input[name="page[slug]"]').val());
    });

    if (jQuery("#page")) {
        jQuery("#page").tabs().css('display', 'block');
    }

    if (jQuery('#showHideInfo')) {
        jQuery('#showHideInfo').toggle(function() {
            jQuery('#additionalInfo').slideDown();
        }, function() {
            jQuery('#additionalInfo').slideUp();
        });
    }

    jQuery('#buttons input').click(function(event) {
        event.preventDefault();
    });

    var inputs = jQuery('.additionalInfo input');
    inputs.focus(function(){
        jQuery(this).css('color','#000000');
    });
    inputs.blur(function(){
        jQuery(this).css('color','#000000');
    });
    
    jQuery("#cancel").click(function() {
        cx.cm.hideEditView();
    });

    jQuery('#publish, #release').unbind("click").click(function() {
        if (!cx.cm.validateFields()) {
            return false;
        }
        jQuery.post('index.php?cmd=jsondata&object=page&act=set', 'action=publish&'+jQuery('#cm_page').serialize(), function(response) {
            if (response.data != null) {
                if (jQuery('#historyConatiner').html() != '') {
                    cx.cm.loadHistory();
                }
                var newName = jQuery('#page_name').val();
                if (jQuery('#pageId').val() == 'new' || jQuery('#pageId').val() == 0) {
                    cx.cm.loadPage(response.data.id);
                    if (jQuery('a[href="#page_history"]').parent().hasClass('ui-state-active')) {
                        cx.cm.loadHistory(response.data.id);
                    }
                }
                var page = cx.cm.getPageStatus(cx.cm.getNodeId(jQuery("#pageId").val()), cx.cm.getCurrentLang());
                if (publishAllowed) {
                    page.publishing.published = true;
                    page.publishing.hasDraft = "no";
                } else {
                    page.publishing.hasDraft = "waiting";
                }
                switch (jQuery("[name=\"page[type]\"]:checked").attr("value")) {
                    case "content":
                        page.visibility.type = "standard";
                        page.visibility.fallback = false;
                        break;
                    case "redirect":
                        page.visibility.type = "redirection";
                        page.visibility.fallback = false;
                        break;
                    case "application":
                        var module = jQuery("[name=\"page[application]\"").val();
                        if (module != "home") {
                            page.visibility.type = "application";
                        } else {
                            page.visibility.type = "home";
                        }
                        page.visibility.fallback = false;
                        break;
                    case "fallback":
                        if (!page.visibility.fallback) {
                            cx.cm.createJsTree();
                            return;
                        }
                        break;
                }
                page.publishing.locked = jQuery("#page_protection_backend").is(":checked");
                page.visibility.protected = jQuery("#page_protection_frontend").is(":checked");
                page.name = newName;
                cx.cm.updateTreeEntry(page);
            }
            jQuery.fn.cxDestroyDialogs(10000);
        });
    });

    jQuery('#save, #refuse').unbind("click").click(function() {
        if (!cx.cm.validateFields()) {
            return false;
        }
        jQuery.post('index.php?cmd=jsondata&object=page&act=set', jQuery('#cm_page').serialize(), function(response) {
            if (response.data != null) {
                if (jQuery('#historyConatiner').html() != '') {
                    cx.cm.loadHistory();
                }
                var newName = jQuery('#page_name').val();
                if (jQuery('#pageId').val() == 'new' || jQuery('#pageId').val() == 0) {
                    cx.cm.loadPage(response.data.id);
                    if (jQuery('a[href="#page_history"]').parent().hasClass('ui-state-active')) {
                        cx.cm.loadHistory(response.data.id);
                    }
                }
                var page = cx.cm.getPageStatus(cx.cm.getNodeId(jQuery("#pageId").val()), cx.cm.getCurrentLang());
                page.publishing.hasDraft = "yes";
                switch (jQuery("[name=\"page[type]\"]:checked").attr("value")) {
                    case "content":
                        page.visibility.type = "standard";
                        page.visibility.fallback = false;
                        break;
                    case "redirect":
                        page.visibility.type = "redirection";
                        page.visibility.fallback = false;
                        break;
                    case "application":
                        var module = jQuery("[name=\"page[application]\"").val();
                        if (module != "home") {
                            page.visibility.type = "application";
                        } else {
                            page.visibility.type = "home";
                        }
                        page.visibility.fallback = false;
                        break;
                    case "fallback":
                        if (!page.visibility.fallback) {
                            cx.cm.createJsTree();
                            return;
                        }
                        break;
                }
                page.publishing.locked = jQuery("#page_protection_backend").is(":checked");
                page.visibility.protected = jQuery("#page_protection_frontend").is(":checked");
                page.name = newName;
                cx.cm.updateTreeEntry(page);
            }
        });
    });

    jQuery('#preview').click(function(event) {
        jQuery.ajax({
            type: 'post',
            url:  'index.php?cmd=jsondata&object=page&act=setPagePreview',
            data:  jQuery('#cm_page').serialize(),
            async: false,
            error: function() {
                event.preventDefault();
            }
        });
    });

    jQuery('div.wrapper').click(function(event) {
        jQuery(event.target).find('input[name="page[type]"]:radio').click();
    });

    jQuery('div.wrapper input[name="page[type]"]:radio').click(function(event) {
        jQuery('div.activeType').removeClass('activeType');
        jQuery(event.target).parentsUntil('div.type').addClass('activeType');
    });
    
    jQuery('#page').bind('tabsselect', function(event, ui) {
        if (ui.index == 5) {
            if (jQuery('#page_history').html() == '') {
                cx.cm.loadHistory();
            }
        }
    });

    jQuery('#page').bind('tabsshow', function(event, ui) {
        if (ui.index == 0) {
            cx.cm.resizeEditorHeight();
        }
        cx.cm.pushHistory('tab');
    });

    //lock together the title and content title.
    var contentTitle = jQuery('#contentTitle');
    var navTitle = jQuery('#title');
    var headerTitlesLock = new Lock(jQuery('#headerTitlesLock'), function(isClosed) {
        if (isClosed) {
            contentTitle.attr('disabled', 'true');
            contentTitle.val(navTitle.val());
        } else {
            contentTitle.removeAttr('disabled');
        }
    });
    navTitle.bind('change', function() {
        if (headerTitlesLock.isLocked())
            contentTitle.val(navTitle.val());
    });

    // show/hide elemnts when a page's type is changed
    jQuery('input[name="page[type]"]').click(function(event) {
        jQuery('#page .type_hidable').hide();
        jQuery('#page .type_'+jQuery(event.target).val()).show();
        jQuery('#page #type_toggle label').text(jQuery(this).next().text());
        if (jQuery(this).val() == 'application') {
            jQuery('#page #application_toggle label').text(jQuery(this).next().text());
        }
        if (jQuery(this).val() == 'redirect') {
            jQuery('#page #preview').hide();
        } else {
            jQuery('#page #preview').show();
        }
        if (jQuery(this).val() == 'fallback') {
            jQuery("#type_toggle").hide();
        } else {
            jQuery("#type_toggle").show();
        }
        cx.cm.resizeEditorHeight();
    });
    jQuery('input[name="page[type]"]:checked').trigger('click');

    // togglers
    jQuery('#content-manager #sidebar_toggle').click(function() {
        cx.cm.toggleSidebar();
        if (jQuery('#pageId').val() !== 'new') {
            cx.cm.saveToggleStatuses();
        }
    });

    jQuery('.toggle').click(function(objEvent) {
        jQuery(this).toggleClass('open closed');
        if (jQuery(objEvent.currentTarget).is('#titles_toggle')) {
            cx.cm.resizeEditorHeight();
        }
        jQuery(this).nextAll('.container').first().animate({height: 'toggle'}, 400, function() {
            if (jQuery('#pageId').val() !== 'new') {
                cx.cm.saveToggleStatuses();
            }
        });
    });

    jQuery('.checkbox').click(function(event) {
        var indicator = jQuery(this).children('.indicator');
        var container = jQuery(this).nextAll('.container').first();

        if (!jQuery(event.target).is('.indicator') ) {
            indicator.prop('checked', !indicator.prop('checked'));
            indicator.trigger('change');
        }
        if (!jQuery(this).hasClass('no_toggle')) {
            container.animate({height: 'toggle'}, 400);
        }
    });
    
    jQuery("#page_name").blur(function() {
        var val = jQuery(this).val();
        if (val != "") {
            var fields = [
                "page_title",
                "page_metatitle",
                "page_metadesc",
                "page_metakeys",
                "page_slug"
            ];
            jQuery.each(fields, function(index, el) {
                var element = jQuery("#" + el);
                if (element.val() == "") {
                    element.val(val);
                }
            });
            var previewTarget = cx.variables.get("basePath", "contrexx") + jQuery("#page_slug_breadcrumb").text() + val;
            jQuery("#preview").attr("href", previewTarget + "?pagePreview=1");
        }
    });

    var homeCheck = function() {
        var module = jQuery("select#page_application");
        var cmd = jQuery("input#page_application_area");
        var home = jQuery("ins.jstree-icon.page.home");
        
        module.removeClass("warning");
        cmd.removeClass("warning");
        
        if (!home.length) {
            return;
        }
        
        // there is a content with module home and no cmd
        // is it us?
        var pageId = jQuery("#pageId").val();
        var homeId = home.parent().attr("id");
        if (pageId == homeId) {
            return;
        }
        
        if (module.val() == "home") {
            if (cmd.val() == "") {
                module.addClass("warning");
                cmd.addClass("warning");
            }
        }
    }
    jQuery("select#page_application").change(homeCheck);
    jQuery("input#page_application_area").keyup(homeCheck);
    // prevent enter key from opening fileBrowser
    jQuery("#content-manager input").keydown(function(event) {
        if (event.keyCode == 13) {
            return false;
        }
    });
    
    cx.bind("pageStatusUpdate", cx.cm.updatePageIcons, "contentmanager");
    cx.bind("pageStatusUpdate", cx.cm.updateTranslationIcons, "contentmanager");
    cx.bind("pageStatusUpdate", cx.cm.updateActionMenu, "contentmanager");
    cx.bind("pagesStatusUpdate", cx.cm.updatePagesIcons, "contentmanager");
    cx.bind("pagesStatusUpdate", cx.cm.updateTranslationsIcons, "contentmanager");
    cx.bind("pagesStatusUpdate", cx.cm.updateActionMenus, "contentmanager");

    cx.cm.resetEditView();

    // toggle ckeditor when sourceMode is toggled
    jQuery('#page input[name="page[sourceMode]"]').change(function() {
        cx.cm.toggleEditor();
    });

    jQuery(document).ready(function() {
        if (jQuery.getUrlVar('act') == 'new') {
            // make sure history tab is hidden
            jQuery('.tab.page_history').hide();
            // load selected tab
            cx.cm.selectTab(jQuery.getUrlVar('tab'), false);
            // load ckeditor if it's a new page
            cx.cm.createEditor();
        }
    });
};

cx.cm.createJsTree = function(target, data, nodeLevels, open_all) {
    var langPreset;
    try {
        langPreset = cx.cm.getCurrentLang();
    } catch (ex) {}
    if (!target) {
        target = cx.cm.getTree();
    }
    try {
        target.jstree("destroy");
    } catch (ex) {}
    
    var eventAdded = false;
    
    target.jstree({
        // List of active plugins
        "plugins" : [
            "themes","json_data","ui","crrm","cookies","dnd","types", "languages", "checkbox"
        ], // TODO: hotkeys, search?
        "languages" : languages,
        "checkbox": {
            // We want to select every single node separately
            "two_state" : true
        },
        "json_data" : {
            "data" : data,
            "ajax" : {
                "url" : function (nodeId) {
                    if (nodeId == -1) {
                        nodeId = "";
                    } else {
                        nodeId = jQuery(nodeId).closest('li').attr('id').split("_")[1];
                        nodeId = "&nodeid=" + nodeId;
                    }
                    return "?cmd=jsondata&object=node&act=getTree" + nodeId;
                },
                "progressive_render" : true,
                "success" : function(response) {
                    if (!response.data) {
                        return null;
                    }
                    if (cx.cm.actions == undefined) {
                        cx.cm.actions = response.data.actions;
                    } else {
                        jQuery(languages).each(function(index, lang) {
                            jQuery.extend(cx.cm.actions[lang], response.data.actions[lang]);
                        });
                    }
                    nodeLevels = [];
                    for (nodeId in response.data.nodeLevels) {
                        nodeLevels[nodeId] = response.data.nodeLevels[nodeId];
                    }
                    return response.data.tree;
                }/*,
                                   // the `data` function is executed in the instance's scope
                                   // the parameter is the node being loaded
                                   // (may be -1, 0, or undefined when loading the root nodes)
                                   "data" : function (n) {
                                   return {
                                   "operation" : "get_children",
                                   "id" : n.attr ? n.attr("id").replace("node_", "") : 1
                                   };
                                   }*/
            }
        },
        "types" : {
            // I set both options to -2, as I do not need depth and children count checking
            // Those two checks may slow jstree a lot, so use only when needed
            "max_depth" : -2,
            "max_children" : -2,
            // next ln will be neede as soon as we want to manage multiple sites in one contrexx install
            //"valid_children" : [ "site" ],
            "types" : {
                // The default type
                "default" : {
                    "valid_children" : "default"
                }/*,
                     // sites - i.e. manage multiple sites in one contrexx install
                     "site" : {
                     // can have pages in them
                     "valid_children" : [ "default" ],
                     // those prevent the functions with the same name to be used on site nodes
                     "start_drag" : false,
                     "move_node" : false,
                     "delete_node" : false,
                     "remove" : false
                     }*/
            }
        },
        "cookies" : {
            'save_selected' : false
        }
    })
    .bind("before.jstree", function(e, data) {
        if (!eventAdded) {
            jQuery('#site-tree').delegate('a', 'mouseup', function() {
                mouseIsUp = true;
            }).delegate('a', 'mousedown', function() {
                mouseIsUp = false;
            });
        }
        eventAdded = true;;
    })
    .bind("create.jstree", function (e, data) {
        jQuery.post(
            "server.php",
            {
                "operation" : "create_node",
                "id" : data.rslt.parent.attr("id").replace("node_", ""),
                "position" : data.rslt.position,
                "title" : data.rslt.name,
                "type" : data.rslt.obj.attr("rel")
            },
            function (r) {
                if (r.status) {
                    jQuery(data.rslt.obj).attr("id", "node_" + r.id);
                } else {
                    jQuery.jstree.rollback(data.rlbk);
                }
            }
            );
    })
    .bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
            jQuery.ajax({
                async : false,
                type: 'POST',
                url: "server.php",
                data : {
                    "operation" : "remove_node",
                    "id" : this.id.replace("node_", "")
                },
                success : function (r) {
                    if (!r.status) {
                        data.inst.refresh();
                    }
                }
            });
        });
    })
    /*.bind("rename.jstree", function (e, data) {
      jQuery.post(
      "server.php",
      {
      "operation" : "rename_node",
      "id" : data.rslt.obj.attr("id").replace("node_", ""),
      "title" : data.rslt.new_name
      },
      function (r) {
      if (!r.status) {
      jQuery.jstree.rollback(data.rlbk);
      }
      }
      );
      })*/
    .bind("move_node.jstree", function (e, data) {
        data.rslt.o.each(function (i) {
            jQuery.ajax({
                async : false,
                type: 'POST',
                url: "?cmd=jsondata&object=node&act=move",
                data : {
                    "operation" : "move_node",
                    "id" : jQuery(this).attr("id").replace("node_", ""),
                    "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_", ""),
                    "position" : data.rslt.cp + i,
                    "title" : data.rslt.name,
                    "copy" : data.rslt.cy ? 1 : 0
                },
                success : function (r) {
                    for (nodeId in r.data.nodeLevels) {
                        nodeLevels[nodeId] = r.data.nodeLevels[nodeId];
                    }
                    for (nodeId in r.data.nodeLevels) {
                        jQuery('#node_' + nodeId).children('a').children('.jstree-checkbox').css('left', '-' + ((r.data.nodeLevels[nodeId] * 18) + 20) + 'px');
                    }
                    return true;
                    // TODO: response/reporting/refresh
                    if (!r.status) { 
                        jQuery.jstree.rollback(data.rlbk);
                    } else { 
                        jQuery(data.rslt.oc).attr("id", "node_" + r.id);
                        if (data.rslt.cy && jQuery(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                }
            });
        });
    })
    .bind("load_node.jstree", function (event, siteTreeData) {
        var jst = jQuery.jstree._reference('#site-tree');
        var langs = jst.get_settings().languages;

        for (nodeId in nodeLevels) {
            jQuery('#node_' + nodeId).children('a').children('.jstree-checkbox').css('left', '-' + ((nodeLevels[nodeId] * 18) + 20) + 'px');
        }

        jQuery('#site-tree ul li').not(".actions-expanded li").each(function() {
            jQuery(this).children('a:last').after(function() {
                if (!jQuery(this).hasClass('jstree-move') && jQuery(this).siblings('.jstree-move').length == 0) {
                    return '<a class="jstree-move" href="#"></a>';
                }
            });
        });

        // load pages on click
        jQuery('#site-tree a').each(function(index, leaf) {
            jQuery(leaf).not('.jstree-move').click(function(event) {
                var action;
                // don't load a page if the user only meant to select/unselect its checkbox
                if (!jQuery(event.target).hasClass('jstree-checkbox') 
                    && !jQuery(this).hasClass('broken')
                    && !jQuery(event.target).is('ins')) {
                    var module = "";
                    try {
                        module = jQuery.trim(jQuery.parseJSON(jQuery(leaf).attr("data-href")).module);
                        module = module.split(" ")[0];
                    } catch (ex) {}
                    if (jQuery.inArray(module, ["", "home", "login", "imprint", "ids", "error"]) == -1) {
                        cx.cm.showEditModeWindow(module, this.id, jQuery(this).closest('li').attr("id").split("_")[1]);
                    } else {
                        cx.cm.loadPage(this.id, jQuery(this).closest('li').attr("id").split("_")[1], null, "content");
                    }
                } else if (jQuery(event.target).is('ins.page') ||
                        jQuery(event.target).is('ins.publishing')) {
                    if (jQuery(event.target).is('ins.page')) {
                        action = "hide";
                        if (jQuery(event.target).hasClass('invisible')) {
                            action = "show";
                        }
                    } else {
                        action = "deactivate";
                        if (jQuery(event.target).hasClass('unpublished')) {
                            action = "activate";
                        }
                    }
                    var nodeId = jQuery(event.target).closest("li").attr("id").split("_")[1];
                    cx.cm.performAction(action, this.id, nodeId);
                }
            });
            
            jQuery(this).hover(
                function() {
                    if (mouseIsUp) {
                        jQuery(this).siblings('.jstree-wrapper').addClass('hover');
                        jQuery(this).parent().children('.jstree-move').css('display', 'inline-block');
                    } else {
                        jQuery(this).mouseup(function() {
                            jQuery(this).siblings('.jstree-wrapper').addClass('hover');
                            jQuery(this).parent().children('.jstree-move').css('display', 'inline-block');
                            jQuery(this).unbind('mouseup');
                        });
                    }
                },
                function(e) {
                    if (mouseIsUp) {
                        jQuery(this).siblings('.jstree-wrapper').removeClass('hover');
                        jQuery(this).parent().children('.jstree-move').css('display', 'none');
                    } else {
                        jQuery(document).bind('mouseup.link', function() {
                            jQuery(e.currentTarget).siblings('.jstree-wrapper').removeClass('hover');
                            jQuery(e.currentTarget).parent().children('.jstree-move').css('display', 'none');
                            jQuery(document).unbind('mouseup.link');
                        });
                    }
                }
            );
        });

        // highlight active page
        jQuery('#' + jQuery('#pageId').val()).siblings('.jstree-wrapper').addClass('active');

        // add a wrapper div for the horizontal lines
        jQuery('#site-tree li > ins.jstree-icon').each(function(index, node) {
            jQuery(this).hover(
                function() {
                    if (mouseIsUp) {
                        jQuery(this).siblings('.jstree-wrapper').addClass('hover');
                        jQuery(this).siblings('.jstree-move').css('display', 'inline-block');
                    } else if (!mouseIsUp) {
                        jQuery(this).mouseup(function() {
                            jQuery(this).siblings('.jstree-wrapper').addClass('hover');
                            jQuery(this).siblings('.jstree-move').css('display', 'inline-block');
                            jQuery(this).unbind('mouseup');
                        });
                    }
                },
                function(e) {
                    if (mouseIsUp) {
                        jQuery(this).siblings('.jstree-wrapper').removeClass('hover');
                        jQuery(this).siblings('.jstree-move').css('display', 'none');
                    } else {
                        jQuery(document).bind('mouseup.ins', function() {
                            jQuery(e.currentTarget).siblings('.jstree-wrapper').removeClass('hover');
                            jQuery(e.currentTarget).siblings('.jstree-move').css('display', 'none');
                            jQuery(document).unbind('mouseup.ins');
                        });
                    }
                }
            );

            if (jQuery(node).prev().is(".jstree-wrapper")) {
                return;
            }

            var translations = jQuery("<div class=\"translations\" />");
            var nodeIds = [];
            jQuery(this).parent().children("a").each(function(index, el) {
                if (!jQuery(el).is(".jstree-move")) {
                    var lang = jQuery(el).attr("class");
                    var node = jQuery(el).parent("li");
                    nodeIds[lang] = node.attr("id").substr(5);
                }
            });
            jQuery.each(jQuery("select.chzn-select option"), function(index, el) {
                var lang = jQuery(el).val();
                var langEl = jQuery("<div class=\"translation " + lang + "\" />");
                langEl.text(lang);
                langEl.click(function() {
                    var page = cx.cm.getPageStatus(nodeIds[lang], lang);
                    if (page.existing) {
                        cx.cm.loadPage(page.id, null, null, "content");
                    } else {
                        cx.cm.setCurrentLang(lang);
                        cx.cm.loadPage(undefined, nodeIds[lang], null, "content");
                    }
                });
                translations.append(langEl);
            });
            var actions = jQuery('<div class="actions"><div class="label">' + cx.variables.get('TXT_CORE_CM_ACTIONS', 'contentmanager/lang') + '</div><div class="arrow" /></div>')
                            .append("<div class=\"actions-expanded\" style=\"display: none;\"><ul></ul></div>")
                            .click(function() {
                                jQuery(this).children(".actions-expanded").toggle();
                            });
            var wrapper = jQuery(actions).wrap('<div class="jstree-wrapper" />').parent();
            wrapper.prepend(translations);
            jQuery(node).before(wrapper);
        });

        jQuery('.jstree-wrapper').hover(
            function(e) {
                if (mouseIsUp) {
                    jQuery(this).addClass('hover');
                    jQuery(this).siblings('.jstree-move').css('display', 'inline-block');
                } else {
                    jQuery(this).mouseup(function() {
                        jQuery(this).addClass('hover');
                        jQuery(this).siblings('.jstree-move').css('display', 'inline-block');
                        jQuery(this).unbind('mouseup');
                    });
                }
            },
            function (e) {
                if (mouseIsUp) {
                    jQuery(this).removeClass('hover');
                    jQuery(this).siblings('.jstree-move').css('display', 'none');
                } else {
                    jQuery(document).bind('mouseup.jstree-wrapper', function() {
                        jQuery(e.currentTarget).removeClass('hover');
                        jQuery(e.currentTarget).siblings('.jstree-move').css('display', 'none');
                        jQuery(document).unbind('mouseup.jstree-wrapper');
                    });
                }
            }
        );

        // prepare the expanded table
        jQuery(langs).each(function(a, lang) {
            $J('div#site-tree li .jstree-wrapper').each(function(b, e) {
                if (jQuery(e).children('span.module.' + lang).length > 0) {
                    return;
                }
                if (lang == jQuery('#site-tree').jstree('get_lang')) {
                    display = 'show';
                } else {
                    display = 'hide';
                }
                jQuery(e).append($J('<span class="module ' + lang + ' ' + display + '" /><a class="preview ' + lang + ' ' + display + '" target="_blank">' + cx.variables.get('TXT_CORE_CM_VIEW', 'contentmanager/lang') + '</a><span class="lastupdate ' + lang + ' ' + display + '" />'));
                var info = jQuery.parseJSON(jQuery(e).siblings('a[data-href].' + lang).attr('data-href'));
                try {
                    if (info != null) {
                        var user = info.user != '' ? ', ' + info.user : '';
                        jQuery(e).children('span.module.' + lang).text(info.module);
                        jQuery(e).children('a.preview.' + lang).attr('href', '../' + lang + info.path + '?pagePreview=1');
                        jQuery(e).children('span.lastupdate.' + lang).text(info.lastupdate + user);
                    }
                } catch (ex) {
                    jQuery(e).children('a.preview.' + lang).css('display', 'none');
                }
            });
        });

        jQuery('.jstree li, .actions-expanded').live('mouseleave', function(event) {
            if (!jQuery(event.target).is('li.action-item') && jQuery('.actions-expanded').length > 0) {
                jQuery('.actions-expanded').each(function() {
                    jQuery(this).parent().parent().children().css('z-index', 'auto');
                    jQuery(this).hide();
                });
            }
        });
        
        // publishing and visibility icons
        jQuery('#site-tree li a ins.jstree-icon').each(function(index, node) {
            if (jQuery(node).hasClass("publishing") || jQuery(node).hasClass("page")) {
                return;
            }
            publishing = jQuery(node).closest('li').data(jQuery(node).parent().attr('id')).publishing;
            visibility = jQuery(node).closest('li').data(jQuery(node).parent().attr('id')).visibility;

            jQuery(node).before('<ins class="jstree-icon publishing '+publishing+'">&nbsp;</ins>');
            jQuery(node).addClass("page " + visibility);
        });
        
        jQuery("#site-tree ul li > a").each(function(index, element) {
            var pageId = jQuery(element).attr("id");
            var nodeId = jQuery(element).parent("li").attr("id").substr(5);
            var lang = jQuery(element).attr("class").split(" ")[0];
            // theres an error here, we'll fix it later:
            if (!jQuery(element).children(".name").length) {
                var pageName = jQuery.trim(jQuery(element).text());
                jQuery(element).html(jQuery(element).html().replace(pageName.replace("&", "&amp;"), " "));
                jQuery(element).append("<div class=\"name\">" + pageName + "</div>");
            }
            if (pageId) {
                cx.cm.updateTreeEntry(cx.cm.getPageStatus(nodeId, lang));
            }
        });
        
        var checkSiteTree = setInterval(function() {
            if (jQuery('#site-tree li').length) {
                jQuery('.jstree-move').empty();
                clearInterval(checkSiteTree);
            }
        }, 100);

        jQuery('#site-tree .publishing, #site-tree .page, #site-tree .jstree-move, #site-tree .translation, #site-tree .preview, #site-tree .name').tooltip({
            tip: '#tooltip_message',
            offset: [-130,-231],
            predelay: 700,
            onBeforeShow: function(objEvent) {
                var objTrigger = this.getTrigger();
                var objTip = this.getTip();
                objTip.html('');
                var arrCssClasses = jQuery.trim(objTrigger.attr('class')).split(' ');

                if (objTrigger.hasClass('publishing')) {

                    var arrStatuses = new Array();
                    var arrTipMessage = new Array();
                    
                    if (objTrigger.hasClass('unpublished')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_UNPUBLISHED', 'contentmanager/lang/tooltip'));
                    } else {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_PUBLISHED', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('draft') && !objTrigger.hasClass('waiting')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_DRAFT', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('draft') && objTrigger.hasClass('waiting')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_DRAFT_WAITING', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('locked')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_LOCKED', 'contentmanager/lang/tooltip'));
                    }

                    if (arrStatuses.length > 0) {
                        arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_INFO_STATUSES', 'contentmanager/lang/tooltip')+jQuery.ucfirst(arrStatuses.join(', ')));
                    }
                    if (!objTrigger.hasClass('inexistent')) {
                        if (objTrigger.hasClass('unpublished')) {
                            arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_INFO_ACTION_ACTIVATE', 'contentmanager/lang/tooltip'));
                        } else {
                            arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_INFO_ACTION_DEACTIVATE', 'contentmanager/lang/tooltip'));
                        }
                    }

                    objTip.html(arrTipMessage.join('<br />'));

                } else if (objTrigger.hasClass('page')) {

                    var arrStatuses = new Array();
                    var arrTypes = new Array();
                    var arrTipMessage = new Array();

                    if (objTrigger.hasClass('broken')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PAGE_STATUS_BROKEN', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('invisible')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PAGE_STATUS_INVISIBLE', 'contentmanager/lang/tooltip'));
                    } else {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PAGE_STATUS_VISIBLE', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('protected')) {
                        arrStatuses.push(cx.variables.get('TXT_CORE_CM_PAGE_STATUS_PROTECTED', 'contentmanager/lang/tooltip'));
                    }

                    if (!objTrigger.hasClass('home') && !objTrigger.hasClass('application') && !objTrigger.hasClass('redirection')) {
                        arrTypes.push(cx.variables.get('TXT_CORE_CM_PAGE_TYPE_CONTENT_SITE', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('application')) {
                        arrTypes.push(cx.variables.get('TXT_CORE_CM_PAGE_TYPE_APPLICATION', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('redirection')) {
                        arrTypes.push(cx.variables.get('TXT_CORE_CM_PAGE_TYPE_REDIRECTION', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('home')) {
                        arrTypes.push(cx.variables.get('TXT_CORE_CM_PAGE_TYPE_HOME', 'contentmanager/lang/tooltip'));
                    }
                    if (objTrigger.hasClass('fallback')) {
                        arrTypes.push(cx.variables.get('TXT_CORE_CM_PAGE_TYPE_FALLBACK', 'contentmanager/lang/tooltip'));
                    }

                    if (arrStatuses.length > 0) {
                        arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PAGE_INFO_STATUSES', 'contentmanager/lang/tooltip')+jQuery.ucfirst(arrStatuses.join(', ')));
                    }
                    if (arrTypes.length > 0) {
                        arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PUBLISHING_INFO_TYPES', 'contentmanager/lang/tooltip')+jQuery.ucfirst(arrTypes.join(', ')));
                    }
                    if (!objTrigger.parent().hasClass('inexistent')) {
                        if (objTrigger.hasClass('invisible')) {
                            arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PAGE_INFO_ACTION_SHOW', 'contentmanager/lang/tooltip'));
                        } else {
                            arrTipMessage.push(cx.variables.get('TXT_CORE_CM_PAGE_INFO_ACTION_HIDE', 'contentmanager/lang/tooltip'));
                        }
                    }

                    objTip.html(arrTipMessage.join('<br />'));

                } else if (objTrigger.hasClass('jstree-move')) {
                    objTip.html(cx.variables.get('TXT_CORE_CM_PAGE_MOVE_INFO', 'contentmanager/lang/tooltip'));
                } else if (objTrigger.hasClass('translation')) {
                    objTip.html(cx.variables.get('TXT_CORE_CM_TRANSLATION_INFO', 'contentmanager/lang/tooltip'));
                } else if (objTrigger.hasClass('preview')) {
                    objTip.html(cx.variables.get('TXT_CORE_CM_PREVIEW_INFO', 'contentmanager/lang/tooltip'));
                } else if (objTrigger.hasClass('name')) {
                    objTip.html(objTrigger.text());
                }

                if (objTip.html() === '') {
                    return false;
                }
            }
        });
        
        if (jQuery.browser.msie  && parseInt(jQuery.browser.version, 10) === 7) {
            zIndex = jQuery('#site-tree li').length * 10;
            jQuery('#site-tree li').each(function() {
                jQuery(this).children('.jstree-wrapper').css('zIndex', zIndex);
                jQuery(this).children('a, ins').css('zIndex', zIndex + 1);
                zIndex -= 10;
            });
        }
    })
    .bind("loaded.jstree", function(event, data) {
        if (open_all) {
            jQuery("#site-tree").jstree("open_all");
        }
        cx.cm.is_opening = false;
        jQuery("#site-tree").show();
        jQuery('#loading').dialog('destroy');
        
        var setPageTitlesWidth = setInterval(function() {
            if (jQuery('#content-manager').hasClass('edit_view') && jQuery('#site-tree .name').length) {
                jQuery('#site-tree .name').each(function() {
                    width    = jQuery(this).width();
                    var data = jQuery(this).parent().attr('data-href');
                    if (data == null) {
                        clearInterval(setPageTitlesWidth);
                        return;
                    }
                    level    = jQuery.parseJSON(data).level;
                    maxWidth = 228 - ((level - 1) * 18) - 26;
                    if (width >= maxWidth) {
                        jQuery(this).css('width', maxWidth + 'px');
                    }
                });
                clearInterval(setPageTitlesWidth);
            }
        }, 100);
    })
    .bind("refresh.jstree", function(event, data) {
        jQuery(event.target).jstree('loaded');
    })
    .bind("set_lang.jstree", function(event, data) {
        document.cookie = "userFrontendLangId=" + data.rslt;
    })
    .ajaxStart(function(){
        if (!cx.cm.is_opening) {
            $J('#loading').cxNotice();
        }
    })
    .ajaxError(function(event, request, settings) {
    })
    .ajaxStop(function(event, request, settings){
        if (!cx.cm.is_opening) {
            $J('#loading').dialog('destroy');
        }
    })
    .ajaxSuccess(function(event, request, settings) {
        try {
            response = $J.parseJSON(request.responseText);
            if (response.message) {
                $J('<div>'+response.message+'</div>').cxNotice();
            }
        }
        catch (e) {}
    });
    if (typeof(langPreset) == 'string' && langPreset.length == 2) {
        cx.cm.setCurrentLang(langPreset);
    }
};

cx.cm.saveToggleStatuses = function() {
    var toggleStatuses = {
        toggleTitles: jQuery('#titles_container').css('display'),
        toggleType: jQuery('#type_container').css('display'),
        toggleNavigation: jQuery('#navigation_container').css('display'),
        toggleBlocks: jQuery('#blocks_container').css('display'),
        toggleThemes: jQuery('#themes_container').css('display'),
        toggleApplication: jQuery('#application_container').css('display'),
        sidebar: jQuery('#content-manager #cm-left').css('display')
    };
    cx.variables.set('toggleTitles', toggleStatuses['toggleTitles'], 'contentmanager/toggle');
    cx.variables.set('toggleType', toggleStatuses['toggleType'], 'contentmanager/toggle');
    cx.variables.set('toggleNavigation', toggleStatuses['toggleNavigation'], 'contentmanager/toggle');
    cx.variables.set('toggleBlocks', toggleStatuses['toggleBlocks'], 'contentmanager/toggle');
    cx.variables.set('toggleThemes', toggleStatuses['toggleThemes'], 'contentmanager/toggle');
    cx.variables.set('toggleApplication', toggleStatuses['toggleApplication'], 'contentmanager/toggle');
    cx.variables.set('sidebar', toggleStatuses['sidebar'], 'contentmanager/toggle');
    jQuery.post('index.php?cmd=jsondata&object=cm&act=saveToggleStatuses', toggleStatuses);
};

CKEDITOR.on('instanceReady', function() {
    cx.cm.resizeEditorHeight();
});

jQuery(window).resize(function() {
    if (cx.cm.isEditView()) {
        if (this.resizeTimeout) {
            clearTimeout(this.resizeTimeout);
        }
        this.resizeTimeout = setTimeout(function() {
            cx.cm.resizeEditorHeight();
        }, 250);
    }
});

cx.cm.resizeEditorHeight = function() {
    var windowHeight = jQuery(window).height();
    var contentHeightWithoutEditor = 
        jQuery('#header').outerHeight(true) +
        parseInt(jQuery('#content').css('padding-top')) +
        jQuery('.breadcrumb').outerHeight(true) +
        jQuery('#cm-tabs').outerHeight(true) +
        jQuery('#titles_toggle').outerHeight(true) +
        (jQuery('#titles_toggle').hasClass('closed') ? 0 : jQuery('#titles_container').outerHeight(true)) +
        jQuery('#type_toggle').outerHeight(true) +
        (jQuery('#type_container .container').is(':visible') ? jQuery('#type_container .container').outerHeight() : 0) +
        jQuery('#buttons').outerHeight(true) +
        parseInt(jQuery('#content-manager').css('padding-bottom')) +
        parseInt(jQuery('#content').css('padding-bottom')) +
        jQuery('#footer').outerHeight(true)
    ;
    var restHeight = windowHeight-contentHeightWithoutEditor;

    if (cx.cm.editorInUse() && CKEDITOR.status == 'basic_ready') {//resize ckeditor
        var ckeditorSpacing =
            parseInt(jQuery('.cke_wrapper').css('padding-top')) +
            jQuery('#cke_top_cm_ckeditor').outerHeight(true) +
            jQuery('#cke_bottom_cm_ckeditor').outerHeight(true) +
            parseInt(jQuery('.cke_wrapper').css('padding-bottom'))
        ;
        if (restHeight > 400) {
            ckeditorHeight = restHeight - ckeditorSpacing;
            if (jQuery.browser.msie && jQuery.browser.version == 9 ) {
                ckeditorHeight = ckeditorHeight - 1;
            }
        } else if (restHeight < 400) {
            ckeditorHeight = 400 - ckeditorSpacing;
        }
        jQuery('#cke_contents_cm_ckeditor').css('height', ckeditorHeight + 'px');
    } else {//resize textarea
        textareaSpacing = (jQuery('#cm_ckeditor').outerHeight(true) - jQuery('#cm_ckeditor').height());
        if (restHeight > 400) {
            textareaHeight = restHeight - textareaSpacing;
        } else if (restHeight < 400) {
            textareaHeight = 400 - textareaSpacing;
        }
        jQuery('#cm_ckeditor').css('height', textareaHeight + 'px');
    }
};

cx.cm.validateFields = function() {
    var error = false;
    var fields = [jQuery("#page_name"), jQuery("#page_title")];
    jQuery.each(fields, function(index, el) {
        el.removeClass("warning");
        if (el.val() == "") {
            error = true;
            el.addClass("warning");
        }
    });
    if (error) {
        $J('<div></div>').cxNotice();
    }
    return !error;
}

cx.cm.performAction = function(action, pageId, nodeId) {
    var pageElement = jQuery("a#" + pageId);
    var pageLang = pageElement.attr("class").split(" ")[0];
    var page = cx.cm.getPageStatus(nodeId, pageLang);
    var url = "index.php?cmd=jsondata&object=page&act=set&action=" + action + "&pageId=" + pageId;
    switch (action) {
        case "new":
            cx.cm.showEditView(true);
            jQuery('.tab.page_history').hide();
            jQuery("#parent_node").val(nodeId);
            cx.cm.createEditor();
            return;
        case "activate":
        case "deactivate":
            // do not toggle activity for drafts
            if (page.publishing.hasDraft != "no") {
                return
            }
            break;
        case "show":
        case "hide":
        case "publish":
            // nothing to do yet
            break;
        case "delete":
            if (!cx.cm.confirmDeleteNode()) {
                return;
            }
            url = "index.php?cmd=jsondata&object=node&act=delete&action=" + action + "&id=" + nodeId;
            break;
        default:
            // do not perform unknown actions
            alert("Unknown action \"" + action + "\"");
            return;
    }
    jQuery.ajax({
        url: url,
        dataType: "json",
        type: "POST",
        data: url,
        success: function(json) {
            switch (action) {
                case "show":
                    page.visibility.visible = true;
                    break;
                case "hide":
                    page.visibility.visible = false;
                    break;
                case "publish":
                    if (publishAllowed) {
                        page.publishing.published = true;
                    } else {
                        page.publishing.hasDraft = "waiting";
                    }
                    break;
                case "activate":
                    page.publishing.published = true;
                    break;
                case "deactivate":
                    page.publishing.published = false;
                    break;
                case "delete":
                    page.deleted = true;
                    break;
                default:
                    // do not perform unknown actions
                    alert("Unknown action \"" + action + "\"");
                    return;
            }
            cx.cm.updateTreeEntry(page);
        }
    });
}

cx.cm.updatePageIcons = function(args) {
    var node = jQuery("#node_" + args.page.nodeId);
    var page = node.children("a." + args.page.lang);
    
    if (!args.page.existing) {
        page.addClass("inexistent");
    } else {
        page.removeClass("inexistent");
    }
    
    // reload the editor values
    if (args.page.id == jQuery('input#pageId').val()) {
        cx.cm.loadPage(args.page.id, undefined, undefined, undefined, false);
    }
}

cx.cm.updatePagesIcons = function(args) {
    for (var i = 0; i < args.pages.length; i++) {
        var pageId = args.pages[i].id;
        var arg = {page: args.pages[pageId]};
        cx.cm.updatePageIcons(arg);
    }
}

cx.cm.updateTranslationIcons = function(args) {
    var node = jQuery("#node_" + args.page.nodeId);
    var page = node.children("a." + args.page.lang);
    var translationIcon = page.siblings(".jstree-wrapper").children(".translations").children(".translation." + args.page.lang);
    
    // reset classes
    translationIcon.attr("class", "translation " + args.page.lang);
    // set new status
    if (args.page.deleted || !args.page.publishing.published) {
        translationIcon.addClass("unpublished");
    }
    if (jQuery.inArray(args.page.publishing.hasDraft, ["yes", "waiting"]) >= 0) {
        translationIcon.addClass("draft");
    }
    if (!args.page.existing) {
        translationIcon.addClass("inexistent");
    }
}

cx.cm.updateTranslationsIcons = function(args) {
    for (var i = 0; i < args.pages.length; i++) {
        var pageId = args.pages[i].id;
        var arg = {page: args.pages[pageId]};
        cx.cm.updateTranslationIcons(arg);
    }
}

cx.cm.updateActionMenu = function(args) {
    var node = jQuery("#node_" + args.page.nodeId);
    var menu = node.children(".jstree-wrapper").children(".actions").children(".actions-expanded").children("ul");
    
    // reset menu
    menu.html("");
    
    // add actions
    menu.append(jQuery("<li class=\"action-item\">").addClass("new").text(cx.variables.get("new", "contentmanager/lang/actions")));
    if (!args.page.publishing.locked) {
        if (args.page.publishing.hasDraft == "no") {
            if (args.page.publishing.published) {
                menu.append(jQuery("<li class=\"action-item\">").addClass("deactivate").text(cx.variables.get("deactivate", "contentmanager/lang/actions")));
            } else {
                menu.append(jQuery("<li class=\"action-item\">").addClass("activate").text(cx.variables.get("activate", "contentmanager/lang/actions")));
            }
        } else {
            menu.append(jQuery("<li class=\"action-item\">").addClass("publish").text(cx.variables.get("publish", "contentmanager/lang/actions")));
        }
        if (args.page.visibility.visible) {
            menu.append(jQuery("<li class=\"action-item\">").addClass("hide").text(cx.variables.get("hide", "contentmanager/lang/actions")));
        } else {
            menu.append(jQuery("<li class=\"action-item\">").addClass("show").text(cx.variables.get("show", "contentmanager/lang/actions")));
        }
        menu.append(jQuery("<li class=\"action-item\">").addClass("delete").text(cx.variables.get("delete", "contentmanager/lang/actions")));
    }
}

cx.cm.updateActionMenus = function(args) {
    for (var i = 0; i < args.pages.length; i++) {
        var pageId = args.pages[i].id;
        var arg = {page: args.pages[pageId]};
        cx.cm.updateActionMenu(arg);
    }
}

/**
 * Updates the publishing and visibility status of a page
 * Base structure for a status is
 * page: {
 *     id: {id},
 *     lang: {lang},
 *     nodeId: {id},
 *     existing: true|false,
 *     deleted: true|false,
 *     publishing: {
 *         locked: true|false,
 *         published: true|false,
 *         hasDraft: no|yes|waiting
 *     },
 *     visibility: {
 *         visible: true|false,
 *         broken: true|false,
 *         protected: true|false,
 *         fallback: true|false,
 *         type: standard|application|home|redirection
 *     }
 * }
 * id is optional, nodeId and lang are not!
 * publishing.locked and visibility.protected are both optional, default is the current value
 * @param int pageId ID of the page to update
 * @param object newStatus New status as array, see method description
 * @return boolean True on success, false otherwise
 */
cx.cm.updateTreeEntry = function(newStatus) {
    // get things we won't change
    var node = jQuery("#node_" + newStatus.nodeId);
    if (!node.length) {
        // we don't have such a node, so our data must be outdated --> reload()
        cx.cm.createJsTree();
        // no need to trigger any event, createJsTree will do that on load
        return true;
    }
    var page = node.children("a." + newStatus.lang);
    var pageId = page.attr("id");
    var nodeId = newStatus.nodeId;
    var pageLang = page.attr("class").split(" ")[0];
    var publishing = page.children("ins.publishing");
    var visibility = page.children("ins.page");
    
    // get temporary helpers
    var tmpPublishingStatus = publishing.attr("class");
    var tmpVisibilityStatus = visibility.attr("class");
    
    // get things we will change
    var lockingStatus = publishing.hasClass("locked");
    var protectionStatus = visibility.hasClass("protected");
    
    // handle special cases
    if (!newStatus.existing) {}
    if (newStatus.deleted) {
        /** we don't care for now, we just reload the tree */
        cx.cm.createJsTree();
        // no need to trigger any event, createJsTree will do that on load
        return true;
    }
    
    if (newStatus.publishing.locked == undefined) {
        newStatus.publishing.locked = lockingStatus;
    }
    if (newStatus.publishing.published == undefined) {
        // Illegal publishing state
        return false;
    }
    if (jQuery.inArray(newStatus.publishing.hasDraft, ["no", "yes", "waiting"]) < 0) {
        // Illegal draft state
        return false;
    }
    if (newStatus.visibility.protected == undefined) {
        newStatus.visibility.protected = protectionStatus;
    }
    if (newStatus.visibility.visible == undefined) {
        // Illegal visibility state
        return false;
    }
    if (newStatus.visibility.broken == undefined) {
        // Illegal broken state
        return false;
    }
    if (newStatus.visibility.fallback == undefined) {
        // Illegal fallback state
        return false;
    }
    if (jQuery.inArray(newStatus.visibility.type, ["standard", "application", "home", "redirection"]) < 0) {
        // Illegal type
        return false;
    }
    if (newStatus.name != "") {
        page.children(".name").text(newStatus.name);
    }
    
    // set css classes
    publishing.attr("class", "");
    publishing.addClass("jstree-icon");
    publishing.addClass("publishing");
    visibility.attr("class", "");
    visibility.addClass("jstree-icon");
    visibility.addClass("page");
    if (newStatus.publishing.locked) {
        publishing.addClass("locked");
    }
    if (!newStatus.publishing.published) {
        publishing.addClass("unpublished")
    }
    switch (newStatus.publishing.hasDraft) {
        case "waiting":
            publishing.addClass("waiting");
        case "yes":
            publishing.addClass("draft");
            break;
        default:
            break;
    }
    if (!newStatus.existing) {
        publishing.addClass("inexistent");
    }
    if (!newStatus.visibility.visible) {
        visibility.addClass("invisible");
    }
    if (newStatus.visibility.broken) {
        visibility.addClass("broken");
    }
    if (newStatus.visibility.fallback) {
        visibility.addClass("fallback");
    }
    if (newStatus.visibility.protected) {
        visibility.addClass("protected");
    }
    switch (newStatus.visibility.type) {
        case "application":
        case "home":
        case "redirection":
            visibility.addClass(newStatus.visibility.type);
        default:
            break;
    }
    
    // make sure IDs are correct
    newStatus.id = pageId;
    newStatus.lang = pageLang;
    newStatus.nodeId = nodeId;
    
    cx.trigger("pageStatusUpdate", "contentmanager", {page: newStatus});
    
    // return
    return true;
}

/**
 * @see cx.cm.updateTreeEntry()
 * @param array pageIds List of page IDs
 * @param array newStatuses List of new statuses ({pageId}=>{status})
 */
cx.cm.updateTreeEntries = function(newStatuses) {
    /** we don't care for now, we just reload the tree */
    cx.cm.createJsTree();
    cx.trigger("pagesStatusUpdate", "contentmanager", {pages: newStatuses});
}

/**
 * Reads the status of a page
 * @param int pageId ID of the page you wan't the state of
 * @return object page: {
 *     id: {id},
 *     existing: true|false,
 *     deleted: false,
 *     publishing: {
 *         locked: true|false,
 *         published: true|false,
 *         hasDraft: no|yes|waiting
 *     },
 *     visibility: {
 *         visible: true|false,
 *         broken: true|false,
 *         protected: true|false,
 *         fallback: true|false,
 *         type: standard|application|home|redirection
 *     }
 * }
 * deleted is true if no page with that ID could be found or the ID is 0
 * if an error occurs, null is returned
 */
cx.cm.getPageStatus = function(nodeId, lang) {
    var node = jQuery("#node_" + nodeId);
    var page = node.children("a." + lang);
    var pageId = page.attr("id");
    if (!page || !page.length || pageId == 0) {
        return {
            id: 0,
            lang: lang,
            name: "",
            nodeId: nodeId,
            existing: false,
            deleted: false,
            publishing: {
                locked: false,
                published: false,
                hasDraft: "no"
            },
            visibility: {
                visible: false,
                broken: true,
                protected: false,
                fallback: false,
                type: "standard"
            }
        };
    }
    
    var publishing = page.children("ins.publishing");
    var visibility = page.children("ins.page");
    
    if (!publishing || !visibility) {
        // ins elements do not exists, state unknown, abort!
        return null;
    }
    
    var hasDraft = "no";
    if (publishing.hasClass("draft")) {
        if (publishing.hasClass("waiting")) {
            hasDraft = "waiting";
        } else {
            hasDraft = "yes";
        }
    }
    
    var type = "standard";
    if (visibility.hasClass("application")) {
        type = "application";
    } else if (visibility.hasClass("home")) {
        type = "home";
    } else if (visibility.hasClass("redirection")) {
        type = "redirection";
    }
    
    var name = page.children(".name").text();
    
    return {
        id: pageId,
        lang: lang,
        name: name,
        nodeId: nodeId,
        existing: true,
        deleted: false,
        publishing: {
            locked: publishing.hasClass("locked"),
            published: !publishing.hasClass("unpublished"),
            hasDraft: hasDraft
        },
        visibility: {
            visible: !visibility.hasClass("invisible") && !visibility.hasClass("inactive"),
            broken: visibility.hasClass("broken"),
            protected: visibility.hasClass("protected"),
            fallback: visibility.hasClass("fallback"),
            type: type
        }
    };
}

/**
 * Returns the nodeId for the given pageId
 * @param int pageId ID of a page
 * @return int nodeId or null
 */
cx.cm.getNodeId = function(pageId) {
    if (!pageId || pageId == 0) {
        return null;
    }
    var page = jQuery("a#" + pageId);
    if (!page || !page.length) {
        return null;
    }
    var node = page.parent("li");
    // if pageId is something like "new" we won't find a node
    if (!node || !node.length) {
        return null;
    }
    return node.attr("id").substr(5);
}

/**
 * Returns the element on which we use .jstree
 * @return object jQuery object
 */
cx.cm.getTree = function() {
    return jQuery("#site-tree");
}

/**
 * Returns the current lang selected in contentmanager
 * @return string Language in the form "en"
 */
cx.cm.getCurrentLang = function() {
    return cx.cm.getTree().jstree("get_lang");
}

/**
 * Sets the current lang to the specified one
 * @param string newLang New language in format "de"
 */
cx.cm.setCurrentLang = function(newLang) {
    cx.cm.getTree().jstree("set_lang", newLang);
}

/**
 * Selects an editor tab
 * @param string tab Tab identifier
 * @param boolean push (optional) Wheter to push this to browser history or not, default is true
 */
cx.cm.selectTab = function(tab, push) {
    if (push == undefined) {
        push = true;
    }
    var tabElement = jQuery(".tab.page_" + tab);
    if (tabElement) {
        var adjusting = cx.cm.historyAdjusting;
        cx.cm.historyAdjusting = true;
        tabElement.click();
        cx.cm.historyAdjusting = adjusting;
    }
    if (push) {
        cx.cm.pushHistory("tab", false);
    }
}

cx.cm.isEditView = function() {
    return jQuery("#content-manager").hasClass("edit_view");
}

cx.cm.showEditView = function(forceReset) {
    jQuery(".jstree-wrapper.active").removeClass("active");
    if (!cx.cm.isEditView()) {
        jQuery("#content-manager").addClass("edit_view");
        cx.cm.resetEditView();
    } else if (forceReset) {
        cx.cm.resetEditView();
    }
    jQuery('#multiple-actions-strike').hide();
    jQuery('.jstree .actions .label, .jstree .actions .arrow').hide();
    
    jQuery('#site-tree .name').each(function() {
        width    = jQuery(this).width();
        data     = jQuery(this).parent().attr('data-href');
        if (data == null) {
            return;
        }
        level    = jQuery.parseJSON(data).level;
        maxWidth = 228 - ((level - 1) * 18) - 26;
        if (width >= maxWidth) {
            jQuery(this).css('width', maxWidth + 'px');
        }
    });
}

cx.cm.resetEditView = function() {
    // reset all input fields
    jQuery("form#cm_page input").not('#buttons input').each(function(index, el) {
        el = jQuery(el);
        var type = el.attr("type");
        var id = el.attr("id");
        if (type != "hidden") {
            if (id == undefined) {
                // this only happens if we have an error in the template
                // (input field without id --> label won't work!)
                //alert(el.attr("name"));
            }
            if (type == "checkbox") {
                // uncheck all checkboxes
                el.attr("checked", false);
            } else if (type == "radio") {
                // do not clear val of radio buttons
            } else {
                // empty all text inputs
                el.val("");
            }
        }
    });
    // empty all textareas
    jQuery("form#cm_page textarea").each(function(index, el) {
        el = jQuery(el);
        el.val("");
    });
    // reset hidden fields
    jQuery("input#pageId").val("new");
    jQuery("input#pageLang").val(jQuery('.chzn-select').val());
    jQuery("input#pageNode").val("");
    jQuery("input#source_page").val("new");
    jQuery("input#parent_node").val("");
    jQuery("input#page[type]").val("off");
    // reset page type
    jQuery("input#type_content").click();
    // remove application
    jQuery("select#page_application").val("");
    // show seo details
    jQuery("#page_metarobots").attr("checked", true);
    jQuery("#metarobots_container").show();
    // same for scheduled publishing
    jQuery("#scheduled_publishing_container").hide();
    // reset theme
    jQuery("select#page_skin").val("");
    // reset multiselects
    var options = jQuery("select#frontendGroupsms2side__dx").html();
    jQuery("select#frontendGroupsms2side__sx").html(options);
    jQuery("select#frontendGroupsms2side__dx").html("");
    options = jQuery("select#backendGroupsms2side__dx").html();
    jQuery("select#backendGroupsms2side__sx").html(options);
    jQuery("select#backendGroupsms2side__dx").html("");
    // (re-)load access data into multiselect
    cx.cm.loadAccess(jQuery.parseJSON(cx.variables.get('cleanAccessData', 'contentmanager')).data);
    // (re-)load block data into multiselect
    var data = {"groups": jQuery.parseJSON(cx.variables.get('availableBlocks', 'contentmanager')).data,"assignedGroups": []};
    fillSelect(jQuery('#pageBlocks'), data);
    // hide refuse button by default
    jQuery('#page input#refuse').hide();

    // remove unused classes
    jQuery(".warning").removeClass("warning");
    
    // switch to content tab
    cx.cm.selectTab("content", false);
    
    // remove or show language dropdown
    if (cx.cm.isEditView()) {
        jQuery("#site-language .chzn-container").show();
    } else {
        if (jQuery(".chzn-select").chosen().children("option").length == 1) {
            jQuery("#site-language .chzn-container").hide();
        }
    }
}

cx.cm.hideEditView = function() {
    if (jQuery('#content-manager').hasClass('sidebar-show')) {
        cx.cm.toggleSidebar();
    }
    jQuery("#content-manager").removeClass("edit_view");
    jQuery('#multiple-actions-strike').show();
    jQuery('.jstree .actions .label, .jstree .actions .arrow').show();
    cx.cm.resetEditView();
    cx.cm.pushHistory("tab");

    jQuery('#site-tree .name').each(function() {
        jQuery(this).css('width', 'auto');
    });
};

cx.cm.toggleSidebar = function() {
    jQuery('#content-manager #cm-left').toggle();
    jQuery('#content-manager').toggleClass('sidebar-show sidebar-hide');
};

cx.cm.toggleEditor = function() {
    if (jQuery('#page_sourceMode').prop('checked')) {
        cx.cm.destroyEditor();
    } else {
        cx.cm.createEditor();
    }
};

cx.cm.editorInUse = function() {
    if (typeof(CKEDITOR.instances.cm_ckeditor) == 'undefined') {
        return false;
    } else {
        return true;
    }
};

cx.cm.createEditor = function() {
    if (!cx.cm.editorInUse()) {
        var config = {
            customConfig: cx.variables.get('basePath', 'contrexx') + cx.variables.get('ckeditorconfigpath', 'contentmanager'),
            toolbar: 'Default',
            skin: 'kama'
        };
        jQuery('#cm_ckeditor').ckeditor(config);
        
        cx.cm.resizeEditorHeight();
    }
};

cx.cm.destroyEditor = function() {
    if (cx.cm.editorInUse()) {
        try {
            CKEDITOR.instances.cm_ckeditor.destroy();
        } catch (e) {
            // this is a bug in CKEDITOR. Until we apply the patch we just catch it. See http://dev.ckeditor.com/ticket/6203
        }
    }
};

cx.cm.setEditorData = function(pageContent) {
    jQuery(document).ready(function() {
        if (!jQuery('#page_sourceMode').prop('checked') && cx.cm.editorInUse()) {
            CKEDITOR.instances.cm_ckeditor.setData(pageContent);
        } else {
            jQuery('#page textarea[name="page[content]"]').val(pageContent);
        }
    });
};

cx.cm.showEditModeWindow = function(cmdName, pageId) {
    var dialog = cx.variables.get("editmodedialog", 'contentmanager');
    if (dialog) {
        return;
    }
    var csrf = cx.variables.get("csrf", "contrexx");
    var title = cx.variables.get("editmodetitle", "contentmanager");
    var content = cx.variables.get("editmodecontent", "contentmanager");

    var editModeLayoutLink = "cx.cm.hideEditModeWindow(); cx.cm.loadPage(" + pageId + ", null, null, 'content'); return false;";
    var editModeModuleLink = "index.php?cmd=" + cmdName + "&csrf=" + csrf;
    
    content = content.replace(/\%1/g, editModeLayoutLink);
    content = content.replace(/\%2/g, editModeModuleLink);
    
    dialog = cx.ui.dialog({
        dialogClass: 'edit-mode',
        title: title,
        width: 400,
        content: content,
        autoOpen: true,
        modal: true
    });
    jQuery('.ui-dialog #edit_mode a').blur();
    
    dialog.bind("close", function() {
        cx.variables.set("editmodedialog", null, "contentmanager");
    });
    cx.variables.set("editmodedialog", dialog, "contentmanager");
}

cx.cm.hideEditModeWindow = function() {
    var dialog = cx.variables.get("editmodedialog", "contentmanager");
    if (!dialog) {
        return;
    }
    dialog.close();
}

cx.cm.loadHistory = function(id) {
    pageId = (id != undefined) ? parseInt(id) : parseInt(jQuery('#pageId').val());
    if (isNaN(pageId) || (pageId == 0)) {
        return;
    }
    
    jQuery('#page_history').load('index.php?cmd=jsondata&object=page&act=getHistoryTable&page='+pageId, function() {
        cx.cm.updateHistoryTableHighlighting();
    });
};

cx.cm.loadPage = function(pageId, nodeId, historyId, selectTab, reloadHistory) {
    cx.cm.resetEditView();
    var url = '?cmd=jsondata&object=page&act=get&page='+pageId+'&node='+nodeId+'&lang='+jQuery("#site-tree").jstree("get_lang")+'&userFrontendLangId='+jQuery("#site-tree").jstree("get_lang");
    if (historyId) {
        url += '&history=' + historyId;
    }
    
    if (reloadHistory == undefined) {
        reloadHistory = true;
    }
    
    jQuery.ajax({
        url : url,
        complete : function(response) {
            var page = jQuery.parseJSON(response.responseText);
            if (page.status == "success") {
                cx.cm.pageLoaded(page.data, selectTab, reloadHistory, historyId);
            }
            if (page.status == "error" && page.message)  {
                $J('<div class="error">'+page.message+'</div>').cxNotice();
            } else if (page.message) {
                $J('<div>'+page.message+'</div>').cxNotice();
            }
            cx.cm.updateHistoryTableHighlighting();
            jQuery.fn.cxDestroyDialogs(10000);
        }
    });
};
cx.cm.pageLoaded = function(page, selectTab, reloadHistory, historyId) {
    cx.cm.showEditView();
    
    // make sure history tab is shown
    jQuery('.tab.page_history').show();
    
    if (jQuery('#page input[name="page[lang]"]').val() != page.lang) {
        // lang has changed, preselect correct entry in lang select an reload tree
        jQuery("#site-tree").jstree("set_lang", page.lang);
        jQuery('.chzn-select').val(page.lang);
        jQuery('#language_chzn').remove();
        jQuery('#language').val(page.lang).change().removeClass('chzn-done').chosen();
    }
    var str = "";
    jQuery("select.chzn-select option:selected").each(function () {
        str += jQuery(this).attr('value');
    });
    if (fallbacks[str]) {
        jQuery('.hidable_nofallback').show();
        jQuery('#fallback').text(language_labels[fallbacks[str]]);
    } else {
        jQuery('.hidable_nofallback').hide();
    }
    
    // set toggle statuses
    var toggleElements = new Array(
        ['toggleTitles', '#titles_container'],
        ['toggleType', '#type_container'],
        ['toggleNavigation', '#navigation_container'], 
        ['toggleBlocks', '#blocks_container'],
        ['toggleApplication', '#application_container'],
        ['toggleThemes', '#themes_container']
    );
    jQuery.each(toggleElements, function() {
        if (jQuery(this[1]).css('display') !== cx.variables.get(this[0], 'contentmanager/toggle')) {
            jQuery(this[1]).css('display', cx.variables.get(this[0], 'contentmanager/toggle'));
            jQuery(this[1]).prevAll('.toggle').first().toggleClass('open closed');
        }
    });

    // set sidebar status
    if (jQuery('#content-manager #cm-left').css('display') !== cx.variables.get('sidebar', "contentmanager/toggle")) {
        jQuery('#content-manager #cm-left').css('display', cx.variables.get('sidebar', "contentmanager/toggle"));
        jQuery('#content-manager').toggleClass('sidebar-show sidebar-hide');
    }

    // tab content
    jQuery('#page input[name="page[id]"]').val(page.id);
    jQuery('#page input[name="page[historyId]"]').val(historyId);
    jQuery('#page input[name="page[lang]"]').val(page.lang);
    jQuery('#page input[name="page[node]"]').val(page.node);
    jQuery('#page input[name="page[name]"]').val(page.name);
    jQuery('#page input[name="page[title]"]').val(page.title);
    jQuery('#page input[name="page[contentTitle]"]').val(page.contentTitle);

    jQuery('#page input[name="page[type]"][value="'+page.type+'"]').trigger('click');
    jQuery('#page input[name="page[target]"]').val(page.target);
    jQuery('#page select[name="page[application]"]').val(page.module);
    jQuery('#page input[name="page[area]"]').val(page.area);

    // tab seo
    jQuery('#page input[name="page[metarobots]"]').prop('checked', page.metarobots);
    if (page.metarobots) {
        jQuery("#metarobots_container").show();
    } else {
        jQuery("#metarobots_container").hide();
    }
    jQuery('#page input[name="page[metatitle]"]').val(page.metatitle);
    jQuery('#page textarea[name="page[metadesc]"]').val(page.metadesc);
    jQuery('#page textarea[name="page[metakeys]"]').val(page.metakeys);

    // tab access protection
    jQuery('#page input[name="page[protection_frontend]"]').prop('checked', page.frontend_protection);
    jQuery('#page input[name="page[protection_backend]"]').prop('checked', page.backend_protection);

    // tab settings
    jQuery('#page input[name="page[scheduled_publishing]"]').prop('checked', page.scheduled_publishing);
    if (page.scheduled_publishing) {
        jQuery('#page input[name="page[scheduled_publishing]"]').parent().nextAll('.container').first().show();
    }
    jQuery('#page input[name="page[start]"]').val(page.start);
    jQuery('#page input[name="page[end]"]').val(page.end);

    jQuery('#page select[name="page[skin]"]').val(page.skin);
    reloadCustomContentTemplates();
    jQuery('#page select[name="page[customContent]"]').val(page.customContent);
    jQuery('#page input[name="page[cssName]"]').val(page.cssName);

    jQuery('#page input[name="page[caching]"]').prop('checked', page.caching);

    jQuery('#page input[name="page[link_target]"]').val(page.linkTarget);
    jQuery('#page input[name="page[slug]"]').val(page.slug);
    jQuery('#page input[name="page[cssNavName]"]').val(page.cssNavName);
    
    jQuery("#page span#page_slug_breadcrumb").html(jQuery("#site-tree").jstree("get_lang") + '/' + page.parentPath);

    jQuery('#page input[name="page[sourceMode]"]').prop('checked', page.sourceMode);
    cx.cm.toggleEditor();
    cx.cm.setEditorData(page.content);
    cx.cm.resizeEditorHeight();

    // .change doesn't fire if a checkbox is changed through .prop. This is a workaround.
    jQuery(':checkbox').trigger('change');

    if (reloadHistory) {
        jQuery('#page_history').empty();
    }
    
    if (page.editingStatus == 'hasDraftWaiting') {
        jQuery('#page input#refuse').show();
    } else {
        jQuery('#page input#refuse').hide();
    }
    
    if (page.type == 'redirect') {
        jQuery('#preview').hide();
    }
    jQuery('#page #preview').attr('href', cx.variables.get('basePath', 'contrexx') + page.lang + '/' + page.parentPath + page.slug + '?pagePreview=1');
    
    cx.cm.loadAccess(page.accessData);

    var data = {"groups": jQuery.parseJSON(cx.variables.get('availableBlocks', 'contentmanager')).data,"assignedGroups": page.assignedBlocks};
    fillSelect(jQuery('#pageBlocks'), data);

    /*                'editingStatus' =>  $page->getEditingStatus(),
                'display'       =>  $page->getDisplay(),
                'active'        =>  $page->getActive(),*/
    
    var container = jQuery("div.page_alias").first().parent();
    var field = jQuery("div.page_alias").first();
    if (jQuery("div.page_alias").length > 1) {
        // remove all alias fields
        field.children("input").val('');
        field.children(".noedit").html('');
        jQuery("div.page_alias").remove();
        container.append(field);
    }
    jQuery(page.aliases).each(function(index, alias) {
        // add a new field
        var myField = field.clone(true);
        myField.children("input").val(alias);
        myField.children("input").attr("id", "page_alias_" + index);
        myField.children("span.noedit").html(alias);
        field.before(myField);
    });
    if (!publishAllowed) {
        jQuery("div.page_alias").each(function (index, field) {
            field = jQuery(field);
            field.removeClass("empty");
            if (field.children("span.noedit").html() == "") {
                field.addClass("empty");
            }
        });
        jQuery(".empty").hide();
    }
    
    if (selectTab != undefined) {
        cx.cm.selectTab(selectTab);
    } else {
        // will be done by selectTab too
        cx.cm.pushHistory('leaf');
    }
    jQuery("#node_" + page.node).children(".jstree-wrapper").addClass("active");
    jQuery('html, body').animate({scrollTop:0}, 'slow');
};

cx.cm.loadAccess = function(accessData) {
    jQuery('.ms2side__div').remove();

    fillSelect(jQuery('#frontendAccessGroups'), accessData.frontend);
    fillSelect(jQuery('#backendAccessGroups'), accessData.backend);
}

cx.cm.confirmDeleteNode = function() {
    return confirm(cx.variables.get('confirmDeleteQuestion', "contentmanager/lang"));
}

cx.cm.askRecursive = function() {
    return confirm(cx.variables.get("recursiveQuestion", "contentmanager/lang/actions"));
}

cx.cm.historyPushes = 0;

cx.cm.historyAdjusting = false;

cx.cm.pushHistory = function(source) {
    // pushHistory("tab") is always called last, so we wait for that
    if (source != "tab" || cx.cm.historyAdjusting) {
        return;
    }
    cx.cm.historyAdjusting = true;
    var History = window.History;
    
    // get state
    var activeTabName = jQuery("#cm-tabs li.ui-tabs-selected").children('a').attr('href');
    activeTabName = activeTabName.split("_")[1];
    var activePageId = jQuery('#pageId').val();
    var activeLanguageId = jQuery("#site-tree").jstree("get_lang");
    var oldPageId = undefined;
    try {
        oldPageId = /[?&]page=(\d+)/.exec(window.location)[1];
    } catch (e) {}
    var oldTabName = undefined;
    try {
        oldTabName = /[?&]tab=([^&]*)/.exec(window.location)[1];
    } catch (e) {}
    
    // prevent state from being written twice
    if (activeTabName == oldTabName && oldPageId == activePageId) {
        cx.cm.historyAdjusting = false;
        return;
    }
    
    // push state
    if (!cx.cm.isEditView()) {
        History.pushState({
            state:cx.cm.historyPushes
        }, document.title, "?cmd=content" + "&userFrontendLangId=" + activeLanguageId + "&csrf=" + cx.variables.get("csrf", "contrexx"));
    } else if (activePageId == "new" || activePageId == "" || activePageId == "0") {
        var node = "";
        var act = "&act=new";
        if (jQuery("#parent_node").val() != "") {
            node = "&node=" + jQuery("#parent_node").val();
        } else if (jQuery("#pageNode").val() != "") {
            act = "";
            node = "&node=" + jQuery("#pageNode").val();
        }
        History.pushState({
            state:cx.cm.historyPushes
        }, document.title, "?cmd=content" + act + "&userFrontendLangId=" + activeLanguageId + node + "&tab=" + activeTabName + "&csrf=" + cx.variables.get("csrf", "contrexx"));
    } else {
        var version = "";
        if (jQuery("#historyId").val() != "") {
            version = "&version=" + jQuery("#historyId").val();
        }
        History.pushState({
            state:cx.cm.historyPushes
        }, document.title, "?cmd=content&page=" + activePageId + version + "&tab=" + activeTabName + "&csrf=" + cx.variables.get("csrf", "contrexx"));
    }
    cx.cm.historyPushes++;
}

cx.cm.hashChangeEvent = function(pageId, nodeId, lang, version, activeTab) {
    // do not push history during change
    if (cx.cm.historyAdjusting) {
        cx.cm.historyAdjusting = false;
        return;
    }

    cx.cm.historyAdjusting = true;
    
    if (lang != undefined) {
        jQuery("#site-tree").jstree("set_lang", lang);
    }
    
    // load leaf if necessary
    if (pageId != undefined) {
        if (pageId != jQuery("#pageId").val()) {
            cx.cm.loadPage(pageId, undefined, version, activeTab);
        }
    } else if (nodeId != undefined && pageId != undefined && lang != undefined) {
        cx.cm.loadPage(undefined, node, version, activeTab);
    } else if (jQuery.getUrlVar("act") == "new") {
        // make sure history tab is hidden
        jQuery('.tab.page_history').hide();
        // load empty editor
        cx.cm.showEditView();
    } else {
        cx.cm.hideEditView();
    }
    cx.cm.selectTab(activeTab, false);
    
    cx.cm.historyAdjusting = false;
}

cx.cm.initHistory = function() {
    var History = window.History;
    History.Adapter.bind(window, "statechange", function() {
        var state = History.getState();
        var url = state.url;
        var urlParams = url.split("?")[1].split("&");
        var params = [];
        jQuery.each(urlParams, function(index, el) {
            el = el.split("=");
            params[el[0]] = el[1];
        });
        var pageId = params["page"];
        var lang = params["userFrontendLangId"];
        var activeTab = params["tab"];
        var version = params["version"];
        var nodeId = params["node"];
        cx.cm.hashChangeEvent(pageId, nodeId, lang, version, activeTab);
    });
}

cx.cm.updateHistoryTableHighlighting = function() {
    var version = jQuery("#historyId").val();
    if (version == "") {
        jQuery('.historyLoad, .historyPreview').first().hide();
    }
    jQuery('.historyLoad, .historyPreview').each(function () {
        if ((jQuery(this).attr('id') == 'load_' + version) || (jQuery(this).attr('id') == 'preview_' + version)) {
            jQuery(this).css('display', 'none');
        } else {
            jQuery(this).css('display', 'block');
        }
    });
}
