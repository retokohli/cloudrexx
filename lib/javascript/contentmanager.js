tipMessageStyle = ["","","","","",,"black","#ffffe1","","","",,,,1,"#000000",2,21,0.3,,2,"gray",1,,15,-5];

var baseUrl = 'index.php?cmd=content';

//called from links in history table.
loadHistoryVersion = function(version) {
    pageId = parseInt(jQuery('#pageId').val());
    if (isNaN(pageId)) {
        return;
    }
    
    cx.cm.loadPage(pageId, 0, version, "content", false);
    
    jQuery('.historyLoad, .historyPreview').each(function () {
        if ((jQuery(this).attr('id') == 'load_' + version) || (jQuery(this).attr('id') == 'preview_' + version)) {
            jQuery(this).css('display', 'none');
        } else {
            jQuery(this).css('display', 'block');
        }
    });
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
        return jQuery.getUrlVars()[name];
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

reloadCustomContentTemplates = function() {
    var skinId = jQuery('#page select[name="page[skin]"]').val();
    var module = jQuery('#page select[name="page[application]"]').val();
    var select = jQuery('#page select[name="page[customContent]"]');
    var lastChoice = select.data('sel');
    select.empty();
    
    var templates = cx.variables.get('contentTemplates', "contentmanager");
    if (templates[skinId] == undefined) {
        return;
    }
    
    select.empty();
    for (var i = 0; i < templates[skinId].length; i++) {
        var isHome = /^home_/.exec(templates[skinId][i]);
        if ((isHome && module == "home") || !isHome && module != "home") {
            select.append(jQuery('<option>', {
                value : i
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
    
    jQuery('#targetBrowseButton').click(function() {
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
                    jQuery("#site-tree").jstree("destroy");
                    cx.cm.createJsTree(jQuery("#site-tree"), response.data.tree, true);
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
        jQuery('#site-tree ul li.jstree-checked').each(function() {
            nodeId = jQuery(this).attr('id').match(/\d+$/)[0];
            data.nodes.push(nodeId);
        });
        data.currentNodeId = jQuery('input#pageNode').val();
        if ((data.action != '') && (data.lang != '') && (data.nodes.length > 0)) {
            object = (data.action == 'delete') ? 'node'   : 'page';
            act    = (data.action == 'delete') ? 'Delete' : 'Set';
            if (data.action == 'delete') {
                if (!cx.cm.confirmDeleteNode()) return;
            }
            jQuery.ajax({
                type: 'POST',
                url:  'index.php?cmd=jsondata&object='+object+'&act=multiple'+act,
                data: data,
                success: function(json) {
                    jQuery('#multiple-actions-select').val(0);
                    jQuery('#site-tree').jstree("refresh");
                    if (data.action == 'delete') {
                        if (json.data.deletedCurrentPage) {
                            cx.cm.closePage();
                        }
                    } else {
                        if (json.data.id > 0) {
                            cx.cm.loadPage(json.data.id, undefined, undefined, undefined, false);
                        }
                    }
                }
            });
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
    cx.cm.createJsTree(jQuery("#site-tree"), data.data.tree);

    jQuery(".chzn-select").chosen().change(function() {
        var str = "";
        jQuery("select.chzn-select option:selected").each(function () {
            str += jQuery(this).attr('value');
        });
        jQuery("#site-tree").jstree("set_lang", str);
        var dpOptions = {
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

        jQuery('#site-tree li .jstree-wrapper').each(function() {
            jsTreeLang = jQuery('#site-tree').jstree('get_lang');
            jQuery(this).children('.module.show, .preview.show, .lastupdate.show').removeClass('show').addClass('hide');
            jQuery(this).children('.module.'+jsTreeLang + ', .preview.'+jsTreeLang + ', .lastupdate.' + jsTreeLang).toggleClass('show hide');
        });
    });
    jQuery(".chzn-select").trigger('change');

    jQuery('div.actions-expanded li.action-item').live('click', function(event) {
        var url = jQuery(event.target).attr('data-href');
        var type = 'POST';
        if (!url) {
            return;
        }
        var action = url.match(/act=([^&]+)/)[1];
        if (action == 'delete') {
            if (!cx.cm.confirmDeleteNode()) return;
        }
        jQuery.ajax({
            url: url, 
            dataType: "json", 
            type: type, 
            data: url, 
            success: function(json) {
                jQuery('#site-tree').jstree("refresh");
                jQuery(event.target).closest('.actions-expanded').remove();
                if (action == 'delete') {
                    nodeId = url.match(/id=(\d+)/)[1];
                    if (nodeId == jQuery('input#pageNode').val()) {
                        cx.cm.closePage();
                    }
                } else {
                    if ((json.data.node == jQuery('input#pageNode').val()) && (json.data.lang == jQuery('input#pageLang').val())) {
                        if (json.data.id > 0) {
                            cx.cm.loadPage(json.data.id, undefined, undefined, undefined, false);
                        }
                    }
                }
            }
        });
    });

    //add callback to reload custom content templates available as soon as template or module changes
    jQuery('#page select[name="page[skin]"]').bind('change', function() {
        reloadCustomContentTemplates();
    });

    jQuery('#page_skin_view, #page_skin_edit').click(function(event) {
        if (jQuery('#page_skin').val() != '') {
            themeId = jQuery('#page_skin').val();
            themeName = jQuery('#page_skin option:selected').text();
        } else {
            themeId = cx.variables.get('themeId', 'contentmanager/theme');
            themeName = cx.variables.get('themeName', 'contentmanager/theme');
        }

        if (jQuery(event.currentTarget).is('#page_skin_view')) {
            window.open('../index.php?preview='+themeId);
        } else {
            window.open('index.php?cmd=skins&act=templates&themes='+themeName+'&csrf='+cx.variables.get('csrf', 'contrexx'));
        }
    });

    jQuery('#page select[name="page[application]"]').bind('blur', function() {
        reloadCustomContentTemplates();
    });

    // react to get ?loadpage=
    /*if (jQuery.getUrlVar('loadPage')) {
        cx.cm.loadPage(jQuery.getUrlVar('loadPage'));
    }*/
    if (jQuery.getUrlVar('page')) {
        cx.cm.loadPage(jQuery.getUrlVar('page'), undefined, undefined, jQuery.getUrlVar('tab'));
    }

    cx.cm();
});

jQuery.fn.saveToggleStatuses = function() {
        var toggleStatuses = {
            tabContent: {
                toggleTitles: jQuery('#titles_container').css('display'), 
                toggleType: jQuery('#type_container').css('display')
            },
            tabSettings: {
                toggleThemes: jQuery('#themes_container').css('display'), 
                toggleNavigation: jQuery('#navigation_container').css('display')
            },
            sidebar: jQuery('#content-manager .left').css('display')
        };
        cx.variables.set('toggleTitles', toggleStatuses['tabContent']['toggleTitles'], 'contentmanager/toggle');
        cx.variables.set('toggleType', toggleStatuses['tabContent']['toggleType'], 'contentmanager/toggle');
        cx.variables.set('toggleThemes', toggleStatuses['tabSettings']['toggleThemes'], 'contentmanager/toggle');
        cx.variables.set('toggleNavigation', toggleStatuses['tabSettings']['toggleNavigation'], 'contentmanager/toggle');
        cx.variables.set('sidebar', toggleStatuses['sidebar'], 'contentmanager/toggle');
        jQuery.post('index.php?cmd=jsondata&object=cm&act=saveToggleStatuses', toggleStatuses);
};

cx.cm = function(target) {
    cx.cm.initHistory();
    
    var dpOptions = {
        dateFormat: 'dd.mm.yy',
        timeFormat: 'hh:mm',
        buttonImage: "template/ascms/images/calender.png",
        buttonImageOnly: true
    };
    jQuery("input.date").datetimepicker(dpOptions);

    $J('#page input[name="page[slug]"]').keyup(function() {
        $J('#liveSlug').text($J('#page input[name="page[slug]"]').val());
    });

    cx.cm.loadAccess(cx.variables.get('cleanAccessData', 'contentmanager'));

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

    jQuery('#publish, #release').click(function() {
        if (!cx.cm.validateFields()) {
            return false;
        }
        jQuery.post('index.php?cmd=jsondata&object=page&act=set', 'action=publish&'+jQuery('#cm_page').serialize(), function(response) {
            jQuery('#site-tree').jstree("refresh");
            if (response.data != null) {
                if (jQuery('#historyConatiner').html() != '') {
                    cx.cm.loadHistory();
                }
                if (((jQuery('#pageId').val() == 'new') || jQuery('#pageId').val() == 0) && jQuery('a[href="#page_history"]').parent().hasClass('ui-state-active')) {
                    cx.cm.loadHistory(response.data.id);
                }
                if (response.data.reload) {
                    cx.cm.loadPage(response.data.id, undefined, undefined, undefined, false);
                }
            }
            jQuery.fn.cxDestroyDialogs(10000);
        });
    });

    jQuery('#save, #refuse').click(function() {
        if (!cx.cm.validateFields()) {
            return false;
        }
        jQuery.post('index.php?cmd=jsondata&object=page&act=set', jQuery('#cm_page').serialize(), function(response) {
            jQuery('#site-tree').jstree("refresh");
            if (response.data != null) {
                if (jQuery('#historyConatiner').html() != '') {
                    cx.cm.loadHistory();
                }
                if (((jQuery('#pageId').val() == 'new') || jQuery('#pageId').val() == 0) && jQuery('a[href="#page_history"]').parent().hasClass('ui-state-active')) {
                    cx.cm.loadHistory(response.data.id);
                }
                if (response.data.reload) {
                    cx.cm.loadPage(response.data.id, undefined, undefined, undefined, false);
                }
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
        var historyTabIndex = 4;
        if (ui.index == historyTabIndex) {
            if (jQuery('#historyContainer').html() == '') {
                cx.cm.loadHistory();
            }
        }
    });

    jQuery('#page').bind('tabsshow', function(event, ui) {
        if (!cx.cm.historyAdjusting) {
            cx.cm.pushHistory('tab');
        }
    });

    //lock together the title and content title.
    var contentTitle = jQuery('#contentTitle');
    var navTitle = jQuery('#title');
    var headerTitlesLock = new Lock(jQuery('#headerTitlesLock'), function(isClosed) {
        if(isClosed) {
            contentTitle.attr('disabled', 'true');
            contentTitle.val(navTitle.val());
        }
        else {
            contentTitle.removeAttr('disabled');
        }
    });
    navTitle.bind('change', function() {
        if(headerTitlesLock.isLocked())
            contentTitle.val(navTitle.val());
    });

    // show/hide elemnts when a page's type is changed
    jQuery('input[name="page[type]"]').click(function(event) {
        jQuery('.type_hidable').hide();
        jQuery('.type_'+jQuery(event.target).val()).show();
        jQuery('#type_toggle label').text(jQuery(this).next().text());
        if (jQuery(this).val() == 'redirect') {
            jQuery('#preview').hide();
        } else {
            jQuery('#preview').show();
        }
    });
    jQuery('input[name="page[type]"]:checked').trigger('click');

    // togglers
    jQuery('#content-manager #sidebar_toggle').click(function() {
        jQuery('#content-manager .left').toggle();
        jQuery('#content-manager #sidebar_toggle').toggleClass('show hide');
        if (jQuery('#pageId').val() !== 'new') {
            jQuery.fn.saveToggleStatuses();
        }
    });

    jQuery('.toggle').click(function() {
        jQuery(this).toggleClass('open closed').nextAll('.container').first().animate({height: 'toggle'}, 400, function() {
            if (jQuery('#pageId').val() !== 'new') {
                jQuery.fn.saveToggleStatuses();
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
        }
    });

    // toggle ckedit when sourceMode is toggled
    jQuery('#page input[name="page[sourceMode]"]').change(function() {
        if (jQuery(this).attr('checked')) {
            if (typeof(CKEDITOR.instances.cm_ckeditor) != "undefined") {
                try {
                    CKEDITOR.instances.cm_ckeditor.destroy();
                } catch (e) {
                    // this is a bug in CKEDITOR. Until we apply the patch we
                    // just catch it. See http://dev.ckeditor.com/ticket/6203
                }
            }
        } else {
            var config = {
                customConfig : cx.variables.get('basePath', 'contrexx') + 'editor/ckeditor/config.contrexx.js.php',
                toolbar: 'Default',
                skin: 'kama'
            };

            // Initialize the editor.
            // Callback function can be passed and executed after full instance creation.
            jQuery('#cm_ckeditor').ckeditor(config);
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

    cx.cm.resetEditor();

    // load ckeditor if it's a new page
    if (jQuery('#pageId').val() == 'new') {
        if (typeof(CKEDITOR.instances.cm_ckeditor) == 'undefined') {
            var config = {
                customConfig : cx.variables.get('basePath', 'contrexx') + 'editor/ckeditor/config.contrexx.js.php',
                toolbar: 'Default',
                skin: 'kama'
            };
            jQuery('#cm_ckeditor').ckeditor(config);
        }
    }
};

cx.cm.createJsTree = function(target, data, open_all) {
    target.jstree({
        // List of active plugins
        "plugins" : [
            "themes","json_data","ui","crrm","cookies","dnd","types", "languages", "checkbox"
        ], // TODO: hotkeys, search?
        "languages" : languages,
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
                }
                else {
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
                    return true;
                    // TODO: response/reporting/refresh
                    if (!r.status) { 
                        jQuery.jstree.rollback(data.rlbk);
                    }
                    else { 
                        jQuery(data.rslt.oc).attr("id", "node_" + r.id);
                        if (data.rslt.cy && jQuery(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                }
            });
        });
    })
    .bind("load_node.jstree", function (event, data) {
        var jst = jQuery.jstree._reference('#site-tree');
        var langs = jst.get_settings().languages;

        // load pages on click
        jQuery('#site-tree a').each(function(index, leaf) {
            jQuery(leaf).click(function(event) {
                // don't load a page if the user only meant to select/unselect its checkbox
                if (!jQuery(event.target).hasClass('jstree-checkbox') && !jQuery(this).hasClass('broken')) {
                    var module = jQuery.trim(jQuery.parseJSON(jQuery(leaf).attr("data-href")).module);
                    if (module != "" && module != "home") {
                        cx.cm.showEditModeWindow(module, this.id, jQuery(this).closest('li').attr("id").split("_")[1]);
                    } else {
                        cx.cm.loadPage(this.id, jQuery(this).closest('li').attr("id").split("_")[1], null, "content");
                    }
                }
            });
        });

        // highlight active page
        jQuery('#' + jQuery('#pageId').val()).siblings('.jstree-wrapper').addClass('active');
        jQuery('#site-tree li a').each(function() {
            jQuery(this).click(function() {
                jQuery('#site-tree li .jstree-wrapper.active').removeClass('active');
                jQuery(this).siblings('.jstree-wrapper').addClass('active');
            });

            jQuery(this).hover(function() {
                jQuery(this).siblings('.jstree-wrapper').addClass('hover');
            }, function() {
                jQuery(this).siblings('.jstree-wrapper').removeClass('hover');
            });
        });

        // add a wrapper div for the horizontal lines
        jQuery('#site-tree li > ins.jstree-icon').each(function(index, node) {
            jQuery(this).hover(function() {
                jQuery(this).siblings('.jstree-wrapper').addClass('hover');
            }, function() {
                jQuery(this).siblings('.jstree-wrapper').removeClass('hover');
            });

            if (jQuery(node).prev().is(".jstree-wrapper")) {
                return;
            }

            var actions = jQuery('<div class="actions"><div class="label">' + cx.variables.get('TXT_CORE_CM_ACTIONS', 'contentmanager') + '</div><div class="arrow" /></div>').click(function() {
                if (jQuery(this).next().is('.actions-expanded')) {
                    return;
                }
                jQuery(this).parent('.jstree-wrapper').css('z-index', 10);
                jQuery(this).parent().siblings('a, .jstree-icon').css('z-index', 11);
                var lang = jQuery("#site-tree").jstree("get_lang");
                jQuery(this).after('<div class="actions-expanded" />').siblings('.actions-expanded').html(cx.cm.actions[lang][jQuery(this).closest('li').attr('id').split("_")[1]]);//.load('index.php?cmd=jsondata&act=actions&node='+jQuery(this).closest('li').attr('id').split("_")[1]+'&lang='+lang, function() {jQuery('div.actions-expanded ul li:first').addClass("first");});
            });
            var wrapper = jQuery(actions).wrap('<div class="jstree-wrapper" />').parent();
            jQuery(node).before(wrapper);
        });
        
        jQuery('#site-tree li .jstree-checkbox').each(function() {
            try {
                var info = jQuery.parseJSON(jQuery(this).parent().attr('data-href'));
                if (info !== null) {
                    jQuery(this).css('left', '-' + ((info.level * 18) + 20) + 'px');
                }
            }
            catch (e) {
            }

        });

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
                jQuery(e).append($J('<span class="module ' + lang + ' ' + display + '" /><a class="preview ' + lang + ' ' + display + '" target="_blank">' + cx.variables.get('TXT_CORE_CM_VIEW') + '</a><span class="lastupdate ' + lang + ' ' + display + '" />'));
                var info = jQuery.parseJSON(jQuery(e).siblings('a[data-href].' + lang).attr('data-href'));
                try {
                    if (info != null) {
                        var user = info.user != '' ? ', ' + info.user : '';
                        jQuery(e).children('span.module.' + lang).text(info.module);
                        jQuery(e).children('a.preview.' + lang).attr('href', '../' + lang + '/' + info.slug + '?pagePreview=1');
                        jQuery(e).children('span.lastupdate.' + lang).text(info.lastupdate + user);
                    }
                } catch (ex) {
                    jQuery(e).children('a.preview.' + lang).css('display', 'none');
                }
            });
        });

        jQuery('.jstree li').live('mouseleave', function(event) {
            if (!jQuery(event.target).is('li.action-item') && jQuery('.actions-expanded').length > 0) {
                jQuery('.actions-expanded').each(function() {
                    jQuery(this).parent().parent().children().css('z-index', 'auto');
                    jQuery(this).remove();
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

        jQuery('#site-tree li a .jstree-icon.publishing.unpublished').each(function() {
            jQuery(this).parent().addClass('unpublished');
        });
    })
    .bind("loaded.jstree", function(event, data) {
        if (open_all) {
            jQuery("#site-tree").jstree("open_all");
        }
    })
    .bind("refresh.jstree", function(event, data) {
        jQuery(event.target).jstree('loaded');
    })
    .ajaxStart(function(){
        $J('#loading').cxNotice();
    })
    .ajaxError(function(event, request, settings) {
        })
    .ajaxStop(function(event, request, settings){
        $J('#loading').dialog('destroy');
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

cx.cm.showEditor = function() {
    jQuery("#content-manager").addClass("edit_page");
    cx.cm.resetEditor();
}

cx.cm.resetEditor = function() {
    // reset all input fields
    jQuery("form#cm_page input").each(function(index, el) {
        el = jQuery(el);
        var type = el.attr("type");
        var id = el.attr("id");
        if (type != "hidden") {
            if (id == undefined) {
                // this only happens if we have an error in the template
                // (input field without id --> label won't work!)
                alert(el.attr("name"));
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
    // update ckeditor state by firing change event
    jQuery("input#page_sourceMode").change();
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
    // remove unused classes
    jQuery(".warning").removeClass("warning");
    // switch to content tab
    cx.cm.selectTab("content", false);
}

cx.cm.hideEditor = function() {
    jQuery("#content-manager").removeClass("edit_page");
    cx.cm.resetEditor();
}

cx.cm.showEditModeWindow = function(cmdName, pageId) {
    var dialog = cx.variables.get("editmodedialog", 'contentmanager');
    if (dialog) {
        return;
    }
    var csrf = cx.variables.get("csrf", "contrexx");
    var title = cx.variables.get("editmodetitle", "contentmanager");
    var content = cx.variables.get("editmodecontent", "contentmanager");

    var editModeLayoutLink = "cx.cm.hideEditModeWindow(); cx.cm.loadPage(" + pageId + "); return false;";
    var editModeModuleLink = "index.php?cmd=" + cmdName + "&csrf=" + csrf;
    
    content = content.replace(/\%1/g, editModeLayoutLink);
    content = content.replace(/\%2/g, editModeModuleLink);
    
    dialog = cx.ui.dialog({
        title: title,
        width: 450,
        content: content,
        autoOpen: true
    });
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
    
    jQuery('#historyContainer').load('index.php?cmd=jsondata&object=page&act=getHistoryTable&page='+pageId);
};

cx.cm.loadPage = function(pageId, nodeId, historyId, selectTab, reloadHistory) {
    cx.cm.resetEditor();
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
                cx.cm.pageLoaded(page.data, selectTab, reloadHistory);
            }
            if (page.status == "error" && page.message)  {
                $J('<div class="error">'+page.message+'</div>').cxNotice();
            }
            else if (page.message) {
                $J('<div>'+page.message+'</div>').cxNotice();
            }
        }
    });
};
cx.cm.pageLoaded = function(page, selectTab, reloadHistory) {
    cx.cm.showEditor();
    jQuery('#multiple-actions-strike').hide();//yttodo
    jQuery('.jstree .actions').each(function() {
        jQuery(this).empty();
    });

    if (page.id == 0) {
        jQuery('.tab.page_history').hide();
    } else {
        jQuery('.tab.page_history').show();
    }

    if (jQuery('#page input[name="page[lang]"]').val() != page.lang) {
        // lang has changed, preselect correct entry in lang select an reload tree
        jQuery("#site-tree").jstree("set_lang", page.lang);
        jQuery('.chzn-select').val(page.lang);
        jQuery('.chzn-select').trigger('liszt:updated');
    }

    // set toggle statuses
    var toggleElements = new Array(['toggleTitles', '#titles_container'], ['toggleType', '#type_container'], ['toggleThemes', '#themes_container'], ['toggleNavigation', '#navigation_container']);
    jQuery.each(toggleElements, function() {
        if (jQuery(this[1]).css('display') !== cx.variables.get(this[0], "contentmanager/toggle")) {
            jQuery(this[1]).css('display', cx.variables.get(this[0], "contentmanager/toggle"));
            jQuery(this[1]).prevAll('.toggle').first().toggleClass('open closed');
        }
    });

    // set sidebar status
    if (jQuery('#content-manager .left').css('display') !== cx.variables.get('sidebar', "contentmanager/toggle")) {
        jQuery('#content-manager .left').css('display', cx.variables.get('sidebar', "contentmanager/toggle"));
        jQuery('#content-manager #sidebar_toggle').toggleClass('show hide');
    }

    // tab content
    jQuery('#page input[name="page[id]"]').val(page.id);
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
        jQuery('#page input[name="page[metarobots]"]').parent().nextAll('.container').first().show();
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
    jQuery('#page select[name="page[customContent]"]').data('sel', page.customContent);
    jQuery('#page select[name="page[customContent]"]').val(page.customContent);
    jQuery('#page input[name="page[cssName]"]').val(page.cssName);

    jQuery('#page input[name="page[caching]"]').prop('checked', page.caching);

    jQuery('#page input[name="page[link_target]"]').val(page.linkTarget);
    jQuery('#page input[name="page[slug]"]').val(page.slug);
    jQuery('#page input[name="page[cssNavName]"]').val(page.cssNavName);
    
    jQuery("#page span#page_slug_pre_url").html(page.parentPath);

    jQuery('#page input[name="page[sourceMode]"]').prop('checked', page.sourceMode);
    if (page.sourceMode) {
        if (typeof(CKEDITOR.instances.cm_ckeditor) !== 'undefined') {
            try {
                CKEDITOR.instances.cm_ckeditor.destroy();
            } catch (e) {
                // this is a bug in CKEDITOR. Until we apply the patch we
                // just catch it. See http://dev.ckeditor.com/ticket/6203
            }
        }
        jQuery('#page textarea[name="page[content]"]').val(page.content);
    } else {
        if (typeof(CKEDITOR.instances.cm_ckeditor) == 'undefined') {
            var config = {
                customConfig : cx.variables.get('basePath', 'contrexx') + 'editor/ckeditor/config.contrexx.js.php',
                toolbar: 'Default',
                skin: 'kama'
            };
            jQuery('#cm_ckeditor').ckeditor(config);
        }
    
        CKEDITOR.instances.cm_ckeditor.setData(page.content);
    }

    // .change doesn't fire if a checkbox is changed through .prop. This is a workaround.
    jQuery(':checkbox').trigger('change');

    reloadCustomContentTemplates();

    if (reloadHistory) {
        jQuery('#historyContainer').empty();
    }
    
    if (page.editingStatus == 'hasDraftWaiting') {
        jQuery('#page input#refuse').show();
    } else {
        jQuery('#page input#refuse').hide();
    }
    
    if (page.type == 'redirect') {
        jQuery('#preview').hide();
    }
    jQuery('#page #preview').attr('href', cx.variables.get('basePath', 'contrexx') + page.lang + '/' + page.slug + '?pagePreview=1');
    
    cx.cm.loadAccess(page.accessData);

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
};

cx.cm.closePage = function() {
    cx.cm.hideEditor();
    jQuery('#multiple-actions-strike').show();
    jQuery('.jstree .actions').each(function() {
        jQuery(this).html(cx.variables.get('TXT_CORE_CM_ACTIONS', "contentmanager") + '<div />');
    });
};

cx.cm.loadAccess = function(accessData) {
    var arrayContains = function(array, value) {
        for(var i = 0; i < array.length; i++) {
            if(array[i] == value)

                return true;
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
            if(selected)
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

    jQuery('.ms2side__div').remove();
    fillSelect(jQuery('#frontendAccessGroups'), accessData.frontend);
    fillSelect(jQuery('#backendAccessGroups'), accessData.backend);
}

cx.cm.confirmDeleteNode = function() {
    return confirm(cx.variables.get('confirmDeleteQuestion', "contentmanager"));
}

cx.cm.historyPushes = 0;

cx.cm.historyAdjusting = false;

cx.cm.pushHistory = function(source) {
    // pushHistory("tab") is always called last, so we wait for that
    if (source != "tab" || cx.cm.historyAdjusting) {
        return;
    }
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
        return;
    }
    
    // push state
    if (activePageId == 'new') {
        History.pushState({
            state:cx.cm.historyPushes
        }, "", "?cmd=content&act=new&userFrontendLangId=" + activeLanguageId + "&tab=" + activeTabName)
    } else {
        History.pushState({
            state:cx.cm.historyPushes
        }, "", "?cmd=content&page=" + activePageId + "&tab=" + activeTabName)
    }
    cx.cm.historyPushes++;
}

cx.cm.hashChangeEvent = function(pageId, lang, activeTab) {
    // do not push history during change
    cx.cm.historyAdjusting = true;
    
    // load leaf if necessary
    if (pageId != undefined) {
        if (pageId != jQuery("#pageId").val()) {
            cx.cm.loadPage(pageId, undefined, undefined, activeTab);
        }
    } else {
        // load empty editor
        cx.cm.resetEditor();
        cx.cm.showEditor();
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
        cx.cm.hashChangeEvent(pageId, lang, activeTab);
    });
}
