

var baseUrl = 'index.php?cmd=content';

//called from links in history table.
loadHistoryVersion = function(version) {
    pageId = parseInt(jQuery('#pageId').val());
    if (isNaN(pageId)) {
        return;
    }
    
    cx.cm.loadPage(pageId, 0, version, true, false);
    
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
    
    var templates = cx.variables.get('contentTemplates');
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
    jQuery('#targetBrowseButton').click(function() {
        url = '?cmd=fileBrowser&{CSRF_PARAM}&standalone=true&type=webpages';
        opts = 'width=800,height=600,resizable=yes,status=no,scrollbars=yes';
        window.open(url, 'target', opts).focus();
        return false;
    });
    window.SetUrl = function(url) {
        jQuery('#page_target').val(url);
    }
    
    jQuery('.multiple-actions-marking').click(function(event) {
        event.preventDefault(true);
        if (jQuery(this).hasClass('checked')) {
            add = 'checked';
            remove = 'unchecked';
        } else {
            add = 'unchecked';
            remove = 'checked';
        }
        jQuery('#site-tree ul li').each(function() {
            jQuery(this).removeClass('jstree-'+remove).addClass('jstree-'+add);
        });
    });
    
    jQuery('#select-multiple-actions').change(function() {
        data = new Object();
        data.action = jQuery(this).children('option:selected').val();
        if (data.action == '0') {
            return false;
        }
        data.lang  = jQuery('.chzn-select option:selected').val();
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
                    jQuery('#site-tree').jstree("refresh");
                    if (data.action == 'delete') {
                        if (json.data.deletedCurrentPage) {
                            cx.cm.closePage();
                        }
                    } else {
                        if (json.data.id > 0) {
                            cx.cm.loadPage(json.data.id, undefined, undefined, false, false);
                        }
                    }
                }
            });
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
                clone.insertAfter(parent);
            }
        }
    });
    
    jQuery("#site-tree").jstree({
        // List of active plugins
        "plugins" : [
            "themes","json_data","ui","crrm","cookies","dnd","types", "languages", "checkbox"
        ], // TODO: hotkeys, search?
        "languages" : languages,

        "json_data" : {
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
                if (!jQuery(event.target).hasClass('jstree-checkbox')) {
                    cx.cm.loadPage(this.id, jQuery(this).closest('li').attr("id").split("_")[1], null, true);
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

            var actions = jQuery('<div class="actions">' + cx.variables.get('TXT_CORE_CM_ACTIONS') + '<div /></div>').click(function() {
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
                jQuery(this).css('left', '-' + ((info.level * 18) + 20) + 'px');
            }
            catch (e) {
            }

        });

        jQuery('#site-tree li div.actions').each(function(index, node) {
            if (jQuery(node).prev().is(".preview")) {
                return
            }
            jQuery(node).before('<a class="preview" href="#">' + cx.variables.get('TXT_CORE_CM_VIEW') + '</a>');
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
                jQuery(e).append($J('<span class="module ' + lang + ' ' + display + '" /><span class="lastupdate ' + lang + ' ' + display + '" />'));
                var info = jQuery.parseJSON(jQuery(e).siblings('a[data-href].' + lang).attr('data-href'));
                try {
                    var user = info.user !== null ? ', ' + info.user : '';
                    jQuery(e).children('span.module.' + lang).text(info.module);
                    jQuery(e).children('span.lastupdate.' + lang).text(info.lastupdate + user);
                }
                catch (e) {
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

        if (jQuery('#content-manager').hasClass('shrunk')) {
            jQuery('.jstree li').each(function() {
                jQuery(this).children('.jstree-wrapper').children('.actions').empty();
            });
        }

        jQuery('#site-tree li a .jstree-icon.publishing.unpublished').each(function() {
            jQuery(this).parent().addClass('unpublished');
        });
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
        pageId = jQuery('#node_'+node+" a."+str).attr("id");

        if (fallbacks[str]) {
            jQuery('.hidable_nofallback').show();
            jQuery('#fallback').text(language_labels[fallbacks[str]]);
        } else {
            jQuery('.hidable_nofallback').hide();
        }

        if (pageId) {
            cx.cm.loadPage(pageId, node);
        } else {
            jQuery('#page input[name="source_page"]').val(jQuery('#page input[name="page[id]"]').val());
            jQuery('#page input[name="page[id]"]').val("new");
            jQuery('#page input[name="page[lang]"]').val(str);
            jQuery('#page input[name="page[node]"]').val(node);
            jQuery('#page #preview').attr('href', cx.variables.get('basePath', 'contrexx') + str + '/index.php?pagePreview=1');
        }

        jQuery('#site-tree li .jstree-wrapper').each(function() {
            jQuery(this).children('span.show').removeClass('show').addClass('hide');
            jQuery(this).children('.module.' + jQuery('#site-tree').jstree('get_lang')).toggleClass('show hide');
            jQuery(this).children('.lastupdate.' + jQuery('#site-tree').jstree('get_lang')).toggleClass('show hide');
        });
    });
    jQuery(".chzn-select").trigger('change');

    jQuery('div.actions-expanded li.action-item').live('click', function(event) {
        var url = jQuery(event.target).attr('data-href');
        var type = 'POST';
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
                            cx.cm.loadPage(json.data.id, undefined, undefined, false, false);
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

    jQuery('#page select[name="page[application]"]').bind('blur', function() {
        reloadCustomContentTemplates();
    });

    // react to get ?loadpage=
    if (jQuery.getUrlVar('loadPage')) {
        cx.cm.loadPage(jQuery.getUrlVar('loadPage'));
    }

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
        cx.variables.set('toggleTitles', toggleStatuses['tabContent']['toggleTitles']);
        cx.variables.set('toggleType', toggleStatuses['tabContent']['toggleType']);
        cx.variables.set('toggleThemes', toggleStatuses['tabSettings']['toggleThemes']);
        cx.variables.set('toggleNavigation', toggleStatuses['tabSettings']['toggleNavigation']);
        cx.variables.set('sidebar', toggleStatuses['sidebar']);
        jQuery.post('index.php?cmd=jsondata&object=cm&act=saveToggleStatuses', toggleStatuses);
};

cx.cm = function(target) {
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

    cx.cm.loadAccess(cx.variables.get('cleanAccessData'));

    if (jQuery("#page")) {
        var visible = jQuery("#page").is(":visible");
        jQuery("#page").tabs().css('display', 'block');
        if (!visible) {
            jQuery("#page").hide();
        }
    }

    if (jQuery('#showHideInfo')) {
        jQuery('#showHideInfo').toggle(function() {
            jQuery('#additionalInfo').slideDown();
        }, function() {
            jQuery('#additionalInfo').slideUp();
        });
    }

    jQuery('#buttons input').click(function(event) {
        event.preventDefault(true);
    });

    var inputs = jQuery('.additionalInfo input');
    inputs.focus(function(){
        jQuery(this).css('color','#000000');
    });
    inputs.blur(function(){
        jQuery(this).css('color','#000000');
    });

    jQuery('#publish, #release').click(function() {
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
                    cx.cm.loadPage(response.data.id, undefined, undefined, false, false);
                }
            }
            jQuery.fn.cxDestroyDialogs(10000);
        });
    });

    jQuery('#save, #refuse').click(function() {
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
                    cx.cm.loadPage(response.data.id, undefined, undefined, false, false);
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
        cx.cm.pushHistory('tab');
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
        jQuery('#content-manager .left_wrapper').toggle();
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

    // toggle ckedit when sourceMode is toggled
    jQuery('#page input[name="page[sourceMode]"]').change(function() {
        if (jQuery(this).attr('checked')) {
            if (typeof(CKEDITOR.instances.cm_ckeditor) != "undefined") {
                CKEDITOR.instances.cm_ckeditor.destroy();
            }
        } else {
            var config = {
                customConfig : cx.variables.get('basePath', 'contrexx') + 'editor/ckeditor/config.contrexx.js',
                toolbar: 'Default',
                skin: 'kama'
            };

            // Initialize the editor.
            // Callback function can be passed and executed after full instance creation.
            jQuery('#cm_ckeditor').ckeditor(config);
        }
    });

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

cx.cm.loadHistory = function(id) {
    pageId = (id != undefined) ? parseInt(id) : parseInt(jQuery('#pageId').val());
    if (isNaN(pageId) || (pageId == 0)) {
        return;
    }
    
    jQuery('#historyContainer').load('index.php?cmd=jsondata&object=page&act=getHistoryTable&page='+pageId);
};

cx.cm.loadPage = function(pageId, nodeId, historyId, selectFirstTab, reloadHistory) {
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
                cx.cm.pageLoaded(page.data, selectFirstTab, reloadHistory);
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
cx.cm.pageLoaded = function(page, selectFirstTab, reloadHistory) {
    jQuery('#page').show();
    jQuery('#content-manager').addClass('shrunk');
    jQuery('#multiple-actions-marking').hide();
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
        if (jQuery(this[1]).css('display') !== cx.variables.get(this[0])) {
            jQuery(this[1]).css('display', cx.variables.get(this[0]));
            jQuery(this[1]).prevAll('.toggle').first().toggleClass('open closed');
        }
    });

    // set sidebar status
    if (jQuery('#content-manager .left').css('display') !== cx.variables.get('sidebar')) {
        jQuery('#content-manager .left').css('display', cx.variables.get('sidebar'));
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

    jQuery('#page input[name="page[sourceMode]"]').prop('checked', page.sourceMode);
    if (page.sourceMode) {
        if (typeof(CKEDITOR.instances.cm_ckeditor) !== 'undefined') {
            CKEDITOR.instances.cm_ckeditor.destroy();
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
    var field = jQuery("div.page_alias").first().clone(true);
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
        myField.children("span.noedit").html(alias);
        container.prepend(myField);
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
    
    // get active tab from anchor
    var anchor = window.location.hash.substring(1);
    var activeTab = 'page_content';
    if (anchor != '' && page.id > 0) {
        activeTab = anchor;
    }
    if (selectFirstTab != undefined && selectFirstTab) {
        activeTab = 'page_content';
    }
    jQuery('.tab.' + activeTab).click();
    cx.cm.pushHistory('leaf');
};

cx.cm.closePage = function() {
    jQuery('#page').hide();
    jQuery('#content-manager').removeClass('shrunk');
    jQuery('#multiple-actions-marking').show();
    jQuery('.jstree .actions').each(function() {
        jQuery(this).html(cx.variables.get('TXT_CORE_CM_ACTIONS') + '<div />');
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
    return confirm(cx.variables.get('confirmDeleteQuestion'));
}

cx.cm.historyPushes = 0;

cx.cm.pushHistory = function(source) {
    var History = window.History;
    var activeTabName = jQuery("#cm-tabs li.ui-tabs-selected").children('a').attr('href');
    var activePageId = jQuery('#pageId').val();
    var activeLanguageId = jQuery("#site-tree").jstree("get_lang");
    var oldPageId = undefined;
    try {
        oldPageId = /loadPage=(\d+)/.exec(window.location)[1];
    } catch (e) {}
    //alert(source+": "+source);
    // prevent state from being written twice
    if (activeTabName == '#' + History.getHash().split("-")[0] && oldPageId == activePageId) {
        return;
    }
    if (activePageId == 'new') {
        History.pushState({
            state:cx.cm.historyPushes
        }, "", "?cmd=content&act=new&userFrontendLangId="+activeLanguageId+activeTabName+"-"+activePageId)
    } else {
        History.pushState({
            state:cx.cm.historyPushes
        }, "", "?cmd=content"+activeTabName+"-"+activePageId)
    }
    cx.cm.historyPushes++;
}

cx.cm.hashChangeEvent = function(pageId, lang, activeTab) {
    // load leaf if necessary
    if (pageId != undefined) {
        if (pageId != jQuery("#pageId").val()) {
            cx.cm.loadPage(pageId, jQuery(this).closest('li').attr("id").split("_")[1], null, true);
        }
    } else {
    // load new page in lang
    }
    // switch tab
    jQuery('.tab.' + activeTab).click();
}

jQuery(window).bind("popstate", function(e) {
    // the html5 way
    //alert('asdf');
    //cx.cm.hashChangeEvent();
    });

window.onhashchange = function() {
    var page = null;
    var lang = null;
    try {
        page = /#-(\d+)$/.exec(History.getHash())[1];
    } catch (e) {}
    try {
        lang = /userFrontentLangId=(\d+)/.exec(window.location)[1];
    } catch (e) {}
    var tab = History.getHash().split("-")[0];
    cx.cm.hashChangeEvent(page, lang, tab);
//alert("LKJ");
}
