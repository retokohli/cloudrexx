var baseUrl = 'index.php?cmd=content';

//called from links in history table.
loadHistoryVersion = function(pageId, version) {   
    cx.cm.loadPage(pageId, 0, version);
};

loadHistory = function() {
    var pageId = jQuery('#pageId').val();
    
    if(pageId == 'new')
        return;
    
    jQuery('#historyContainer').empty();
    jQuery('#historyContainer').load(baseUrl+'&act=actAjaxGetHistoryTable&pageId='+pageId);
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

reloadCustomContentTemplates = function() {
    var skinId = jQuery('#page_skin').val();
    var module = jQuery('#page_application').val();
   
    var select = jQuery('#page_custom_content');
    var lastChoice = select.data('sel');
    select.empty();

    if(!skinId)
        return;

    jQuery.get(baseUrl+'&act=actAjaxGetCustomContentTemplates&themeId='+skinId+'&module='+module,
        function(data){
            select.empty();

            var o = jQuery('<option></option>');
            o.attr('value', 0);
            o.html('(Standard)');
            o.appendTo(select);

            for(var i = 0; i < data.length; i++) {
                var name = data[i];

                o = jQuery('<option></option>');
                o.attr('value', name);
                o.html(name);
                
                if(name == lastChoice)
                    o.attr('selected', 'selected');

                o.appendTo(select);
            }
        },
        'json'
    );
};

cx.ready(function() {
    jQuery('#targetBrowseButton').click(function() {
        url = '?cmd=fileBrowser&{CSRF_PARAM}&standalone=true&type=webpages';
        opts = 'width=800,height=600,resizable=yes,status=no,scrollbars=yes';
        window.open(url, 'target', opts).focus();
        return false;
    });
    window.SetUrl = function(url) {
        var matches = /\[\[NODE_(\d+)_(\d+)\]\]/.exec(url);
        var node = matches[1];
        var lang = matches[2];
        jQuery('#page_target').val(node+'-'+lang+'|');
    }
    
    var notification = new NotificationBar(jQuery('#notificationLayer'));
    notification.loading.start();

    jQuery("#site-tree")
            .jstree({
                // List of active plugins
                "plugins" : [
                    "themes","json_data","ui","crrm","cookies","dnd","types", "languages", "checkbox"
                ], // TODO: hotkeys, search?

                "languages" : languages,

                "json_data" : {
                    "ajax" : {
                        "url" : "?cmd=jsondata&object=node&act=getTree",
                        // the `data` function is executed in the instance's scope
                        // the parameter is the node being loaded
                        // (may be -1, 0, or undefined when loading the root nodes)
                        /*"data" : function (n) {
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
                            "valid_children" : "default",
                        },
                        /*// sites - i.e. manage multiple sites in one contrexx install
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
                "ui" : {
                    // this makes the node with ID node_4 selected onload
                    //"initially_select" : [ "node_4" ]
                },
                // the core plugin - not many options here
                "core" : {
                    // just open those two nodes up
                    //"initially_open" : [ "node_2" , "node_3" ]
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
                        success : function (r) { return true;
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
	        .bind("loaded.jstree", function (event, data) {
                var jst = jQuery.jstree._reference('#site-tree');
                var langs = jst.get_settings().languages;

                // load pages on click
 		        jQuery('#site-tree a').each(function(index, leaf) {
			        jQuery(leaf).click(function(event) {
                        // don't load a page if the user only meant to select/unselect its checkbox
                        if (!jQuery(event.target).hasClass('jstree-checkbox')) {
                          cx.cm.loadPage(this.id, jQuery(this).closest('li').attr("id").split("_")[1]);
                        }
			        });
		        });

                // add a wrapper div for the horizontal lines
		        jQuery('#site-tree li > ins.jstree-icon').each(function(index, node) {
                    var actions = jQuery('<div class="actions">Actions</div>').click(function() {
                        var lang = jQuery("#site-tree").jstree("get_lang");
                        jQuery(this).after('<div class="actions-expanded" />').siblings('.actions-expanded').load('index.php?cmd=content&act=actions&node='+jQuery(this).closest('li').attr('id').split("_")[1]+'&lang='+lang);
                    });
                    var wrapper = jQuery(actions).wrap('<div class="jstree-wrapper" />').parent();
                    jQuery(node).before(wrapper);
		        });
                jQuery('div.actions-expanded').live('mouseleave', function(event) {
                    jQuery(event.target).closest('.actions-expanded').remove();
                });
                jQuery('div.actions-expanded li.action-item').live('click', function(event) {
                    var url = jQuery(event.target).attr('data-href');
                    var action = url.match(/act=([^&]+)/)[1];
                    var type = action == 'delete' ? 'POST' : 'GET';
                    var nodeId = jQuery(event.target).parent().closest('li').attr('id').match(/node_(\d+)/)[1];
                    jQuery.ajax({url: url, dataType: "json", type: type, data: {"id": nodeId}, success: function(json) {
                        if (action == "delete") {
                            return;
                        }
                        if (json) {
                            if (json.action == 'new') {
                                alert('A new page would now be created. Not implemented yet.');
                                return;
                            }
                            var selector = '#node_' + json.nodeId + ' a.' + json.lang + ' ins.jstree-icon';
                            if (json.action == 'publish' || json.action == 'unpublish') {
                                jQuery(selector + '.publishing').toggleClass('published').toggleClass('unpublished');
                            }
                            if (json.action == 'visible' || json.action == 'hidden') {
                                jQuery(selector + '.page').toggleClass('active').toggleClass('hidden');
                            }
                            jQuery(event.target).closest('.actions-expanded').remove();
                        }
                    }});
                });
                // publishing and visibility icons
                jQuery('#site-tree li a ins.jstree-icon').each(function(index, node) {
                    publishing = jQuery(node).closest('li').data(jQuery(node).parent().attr('id')).publishing;
                    visibility = jQuery(node).closest('li').data(jQuery(node).parent().attr('id')).visibility;

                    jQuery(node).before('<ins class="jstree-icon publishing '+publishing+'">&nbsp;</ins>');
                    jQuery(node).addClass("page " + visibility);
                });
	        })
            .ajaxStart(function(){
                notification.loading.start();
            })
            .ajaxStop(function(){
                notification.loading.stop();
            });

    jQuery(".chzn-select").chosen().change(function(){ 
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
        }
        else {
            jQuery('.hidable_nofallback').hide();
        }

        if (pageId) {
            cx.cm.loadPage(pageId, node);
        }
        else {
            jQuery('#page input[name="source_page"]').val(jQuery('#page input[name="page[id]"]').val());
            jQuery('#page input[name="page[id]"]').val("new");
            jQuery('#page input[name="page[lang]"]').val(str);
            jQuery('#page input[name="page[node]"]').val(node);
        }
    });
    jQuery(".chzn-select").trigger('change');

    //add callback to reload custom content templates available as soon as template or module changes
    jQuery('#skin').bind('change', function() {
        reloadCustomContentTemplates();
    });

    jQuery('#module').bind('blur', function() {
        reloadCustomContentTemplates();
    });

    // react to get ?loadpage=
    if (jQuery.getUrlVar('loadPage')) {
        cx.cm.loadPage(jQuery.getUrlVar('loadPage'));
    }

});

cx.cm = function(target) {
    var dpOptions = {
        dateFormat: 'dd.mm.yy',
        timeFormat: 'hh:mm',
        buttonImage: "template/ascms/images/calender.png",
        buttonImageOnly: true
    };
    jQuery("input.date").datetimepicker(dpOptions);

    var config = {
        customConfig : cx.variables.get('basePath', 'contrexx') + 'editor/ckeditor/config.contrexx.js',
        toolbar: 'Default',
        skin: 'kama'
    };

    // Initialize the editor.
    // Callback function can be passed and executed after full instance creation.
    jQuery('#cm_ckeditor').ckeditor(config);

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

    jQuery('#buttons button').click(function(event) {
        event.preventDefault(true);
    });

    var inputs = jQuery('.additionalInfo input');
    inputs.focus(function(){
       jQuery(this).css('color','#000000');
    });
    inputs.blur(function(){
       jQuery(this).css('color','#000000');
    });

    jQuery('#publish').click(function() {
        jQuery.post('index.php?cmd=jsondata&_object=page&action=update', jQuery('#cm_page').serialize(), function(response){if(response=="new"){window.location.reload();}} );
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
        if(ui.index == historyTabIndex) {
            loadHistory();
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
    });

    // make headings look nice
    jQuery('.heading').after('<hr class="heading_separator" />');
    // togglers
    jQuery('.toggle').before('<div class="toggle_indicator toggle_open"></div>').click(function() {
        jQuery(this).closest('a').prev().toggleClass('toggle_closed').nextAll('div').first().toggle(400);
    }).prev().click(function() {
        jQuery(this).toggleClass('toggle_closed').nextAll('div').first().toggle(400);
    });
    // toggling checkboxes
    jQuery('.toggle_checkbox').each(function(index, element) {
        jQuery(element).wrapInner('<label for="checkbox_'+jQuery(element).attr("id")+'"></label>')
           .before(jQuery('<input type="checkbox" class="toggle_indicator" />')
             .attr("id", "checkbox_"+jQuery(element).attr("id")).attr("name", jQuery(element).attr("id"))
             .change(function() {
                 jQuery(this).nextAll('div').first().toggle(jQuery(this).prop('checked'));
             })
           );
    });

};

cx.cm.loadPage = function(pageId, nodeId, historyId) {
    var url = '?cmd=jsondata&object=page&act=get&page='+pageId+'&node='+nodeId+'&lang='+jQuery("#site-tree").jstree("get_lang")+'&userFrontendLangId='+jQuery("#site-tree").jstree("get_lang");
    if(historyId)
        url += '&history=' + historyId;
    jQuery.ajax({url : url, complete : function(response) {
        var page = jQuery.parseJSON(response.responseText);
        cx.cm.pageLoaded(page);
    } });
},
cx.cm.pageLoaded = function(page) {
    // Page Content
    jQuery('#page input[name="page[id]"]').val(page.id);
    jQuery('#page input[name="page[lang]"]').val(page.lang);
    jQuery('#page input[name="page[node]"]').val(page.node);
    jQuery('#page input[name="page[name]"]').val(page.name);
    jQuery('#page input[name="page[title]"]').val(page.title);
    jQuery('#page input[name="page[contentTitle]"]').val(page.contentTitle);
    CKEDITOR.instances.cm_ckeditor.setData(page.content);
    jQuery('#page input[name="page[start]"]').val(page.start);
    jQuery('#page input[name="page[end]"]').val(page.end);
    jQuery('#page input[name="page[metatitle]"]').val(page.metatitle);
    jQuery('#page textarea[name="page[metakeys]"]').val(page.metakeys);
    jQuery('#page textarea[name="page[metadesc]"]').val(page.metadesc);
    jQuery('#page input[name="page[metarobots]"]').prop('checked', page.metarobots);

    // Page Settings
    jQuery('#page select[name="page[application]"]').val(page.module);
    jQuery('#page input[name="page[area]"]').val(page.cm_cmd);
    jQuery('#page select[name="page[skin]"]').val(page.skin);
    jQuery('#page input[name="page[cssName]"]').val(page.cssName);
    jQuery('#page select[name="page[customContent]"]').data('sel', page.customContent);
    jQuery('#page select[name="page[customContent]"]').val(page.customContent);
    jQuery('#page input[name="page[target]"]').val(page.target);
    jQuery('#page input[name="page[caching]"]').prop('checked', page.caching);
    jQuery('#page input[name="page[slug]"]').val(page.slug);

    reloadCustomContentTemplates();

    jQuery('#page input[name="page[type]"][value="'+page.type+'"]').click();

    cx.cm.loadAccess(page.accessData);

    // .change doesn't fire if a checkbox is changed through .prop. This is a workaround.
    jQuery(':checkbox').trigger('change');


/*                'editingStatus' =>  $page->getEditingStatus(),
                'display'       =>  $page->getDisplay(),
                'active'        =>  $page->getActive(),*/

//                'protection'    =>  $page->getProtection(),
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
            var selected = arrayContains(accessData.frontend.assignedGroups, id);
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
