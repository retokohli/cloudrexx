var baseUrl = 'index.php?cmd=content';

//issues the request to revert a page to a certain version.
//called from links in history table.
revert = function(pageId, version) {
    jQuery.post(baseUrl+'&act=actAjaxRevert', {
        pageId: pageId,
        version: version
    }, function() {
        loadHistory();
    });
}

loadHistory = function() {
    var pageId = jQuery('#pageId').val();
    
    if(pageId == 'new')
        return;
    
    jQuery('#historyContainer').empty();
    jQuery('#historyContainer').load(baseUrl+'&act=actAjaxGetHistoryTable&pageId='+pageId);
}

jQuery(function() {
    var notification = new NotificationBar(jQuery('#notificationLayer'));
    notification.loading.start();
    jQuery("#site-tree")
            .jstree({


                // List of active plugins
                "plugins" : [
                    "themes","json_data","ui","crrm","cookies","dnd","search","types","hotkeys", "languages"
                ],

                "languages" : ["de", "fr", "en"],

                // I usually configure the plugin that handles the data first
                // This example uses JSON as it is most common
                "json_data" : {
                    // This tree is ajax enabled - as this is most common, and maybe a bit more complex
                    // All the options are almost the same as jQuery's AJAX (read the docs)
                    "ajax" : {
                        // the URL to fetch the data
                        "url" : "?cmd=jsondata",
                        // the `data` function is executed in the instance's scope
                        // the parameter is the node being loaded
                        // (may be -1, 0, or undefined when loading the root nodes)
                        "data" : function (n) {
                            // the result is fed to the AJAX request `data` option
                            return {
                                "operation" : "get_children",
                                "id" : n.attr ? n.attr("id").replace("node_", "") : 1
                            };
                        }
                    }
                },
                // Configuring the search plugin
                "search" : {
                    // As this has been a common question - async search
                    // Same as above - the `ajax` config option is actually jQuery's AJAX object
                    "ajax" : {
                        "url" : "server.php",
                        // You get the search string as a parameter
                        "data" : function (str) {
                            return {
                                "operation" : "search",
                                "search_str" : str
                            };
                        }
                    }
                },
                // Using types - most of the time this is an overkill
                // read the docs carefully to decide whether you need types
                "types" : {
                    // I set both options to -2, as I do not need depth and children count checking
                    // Those two checks may slow jstree a lot, so use only when needed
                    "max_depth" : -2,
                    "max_children" : -2,
                    // I want only `drive` nodes to be root nodes
                    // This will prevent moving or creating any other type as a root node
                    //"valid_children" : [ "drive" ],
                    "types" : {
                        // The default type
                        "default" : {
                            // I want this type to have no children (so only leaf nodes)
                            // In my case - those are files
                            "valid_children" : "files",
                            // If we specify an icon for the default type it WILL OVERRIDE the theme icons
                            "icon" : {
                                "image" : "../lib/javascript/jquery/ui/images/file.png"
                            }
                        },
                        // The `folder` type
                        "folder" : {
                            // can have files and other folders inside of it, but NOT `drive` nodes
                            "valid_children" : [ "default", "folder" ],
                            "icon" : {
                                "image" : "../lib/javascript/jquery/ui/images/folder.png"
                            }
                        },
                        // The `drive` nodes
                        "drive" : {
                            // can have files and folders inside, but NOT other `drive` nodes
                            "valid_children" : [ "default", "folder" ],
                            "icon" : {
                                "image" : "../lib/javascript/jquery/ui/images/root.png"
                            },
                            // those prevent the functions with the same name to be used on `drive` nodes
                            // internally the `before` event is used
                            "start_drag" : false,
                            "move_node" : false,
                            "delete_node" : false,
                            "remove" : false
                        }
                    }
                },
                // UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

                // the UI plugin - it handles selecting/deselecting/hovering nodes
                "ui" : {
                    // this makes the node with ID node_4 selected onload
                    //"initially_select" : [ "node_4" ]
                },
                // the core plugin - not many options here
                "core" : {
                    // just open those two nodes up
                    // as this is an AJAX enabled tree, both will be downloaded from the server
                    //"initially_open" : [ "node_2" , "node_3" ]
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
            .bind("rename.jstree", function (e, data) {
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
            })
            .bind("move_node.jstree", function (e, data) {
                
                data.rslt.o.each(function (i) {
                    
                    jQuery.ajax({
                        async : false,
                        type: 'POST',
                        url: "?cmd=jsondata&operation=move_node",
                        data : {
                            "operation" : "move_node",
                            "id" : jQuery(this).attr("id").replace("node_", ""),
                            "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_", ""),
                            "position" : data.rslt.cp + i,
                            "title" : data.rslt.name,
                            "copy" : data.rslt.cy ? 1 : 0
                        },
                        success : function (r) { return true;
                            if (!r.status) { 
                                jQuery.jstree.rollback(data.rlbk);
                            }
                            else { 
                                jQuery(data.rslt.oc).attr("id", "node_" + r.id);
                                if (data.rslt.cy && jQuery(data.rslt.oc).children("UL").length) {
                                    data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                                }
                            }
                            jQuery("#analyze").click();
                        }
                    });
                });
            })
	        .bind("loaded.jstree", function (event, data) {
                var jst = jQuery.jstree._reference('#site-tree');
                var langs = jst.get_settings().languages;

                //generate the 'untranslated' links.
 		        jQuery('#site-tree li').each(function(index, node) {
                    //(I) decide which link we copy for untranslated pages
                    var referenceLink = null;
                    for(var i = 0; i < langs.length; i++) {
                        var result = jQuery(node).children('a.'+langs[i]);
                        if(result.length > 0) {
                            referenceLink = jQuery(result[0]);
                            break;
                        }                            
                    }
                    if(!referenceLink)
                        return; //empty node, shouldn't happen :S

                    //(II) find untranslated pages, assign link
                    for(var i = 0; i < langs.length; i++) {
                        var lang = langs[i];
                        var result = jQuery(node).children('a.'+lang);
                        if(result.length == 0) {
                            var newLink = jQuery(referenceLink.clone());
                            newLink.removeAttr('id');
                            newLink.removeAttr('class');
                            newLink.addClass(lang);
                            newLink.css({color: 'gray'});                          
                            newLink.insertAfter(referenceLink);
                        }                            
                    }
                });

 		        jQuery('#site-tree a').each(function(index, leaf) {
			        jQuery(leaf).click(function() {
				        jQuery.ajax({url : '?cmd=jsondata&class=page&action=get&id='+this.id, complete : function(response) { 
                            page = jQuery.parseJSON(response.responseText);
					        cx.cm.loadPage(page);
				        } });
			        });
		        });

                //add the active/hidden/inactive dropdowns to all nodes
		        jQuery('#site-tree li').each(function(index, node) {
                    var sel = jQuery('<select name="'+node.id+'_status"></select');
                    jQuery('<option value="active">Active</option>').appendTo(sel);
                    jQuery('<option value="hidden">Hidden</option>').appendTo(sel);
                    jQuery('<option value="inactive">Disabled</option>').appendTo(sel);
                    var ul = jQuery(node).find('ul:first');
                    if(ul.length > 0)
                        sel.insertBefore(ul);
                    else
                        sel.appendTo(node);
                    sel.change(function() {
                        var len = 'node_'.length;
                        var nodeId = parseInt(sel.parent().attr('id').substr(len));
                        var value = sel.val();

                        jQuery.ajax({url: '?cmd=jsondata&class=node&action=update&id='+nodeId,
                                     complete: function(response) {
                                        alert(response.responseText);
                                     },
                                     data: 'status='+value,
                                     type: 'post'});
                    });
//TODO: chosen
                    //sel.chosen();
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
        jQuery("input.date").datepicker(jQuery.datepicker.regional[str]);
    });
});

cx.cm = function(target) {
    //TODO: the 'de' below is a hack.
    jQuery("input.date").datepicker(jQuery.datepicker.regional['de']);
    //jQuery("input.date").datepicker({ dateFormat: 'dd.mm.yy',showOn: "button", buttonImage: "template/ascms/images/calender.png",buttonImageOnly: true },jQuery.datepicker.regional['']);

    var config = {
        toolbar:
                [
                    ['Source'],
                    ['Format'],
                    ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Image', 'Link', 'Unlink'],
                    ['UIColor'],
                    ['Maximize']
                ],
        height: 380,
        resize_dir: 'vertical'
    };

    // Initialize the editor.
    // Callback function can be passed and executed after full instance creation.
    jQuery('#cm_ckeditor').ckeditor(config);

    if (jQuery("#site")) {
        jQuery("#site").tabs().css('display', 'block');
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
       jQuery(this).css('color','#CCCCCC');
    });

    jQuery('#save').click(function() {
        jQuery.post('index.php?cmd=jsondata&object=page&action=update', jQuery('#cm_form').serialize(), function(response){alert(response);} );
    });

    jQuery('#site').bind('tabsselect', function(event, ui) {
        var historyTabIndex = 4;
        if(ui.index == historyTabIndex) {
            loadHistory();
        }
    });
};

cx.cm.loadPage = function(page) {
    // Page Content
    jQuery('#pageId').val(page.id);
    jQuery('#title').val(page.title);
    CKEDITOR.instances.cm_ckeditor.setData(page.content);
    jQuery('#start').val(page.start);
    jQuery('#end').val(page.end);
    jQuery('#metatitle').val(page.metatitle);
    jQuery('#metakeys').val(page.metakeys);
    jQuery('#metadesc').val(page.metadesc);
    jQuery('#metarobots').val(page.metarobots);

    // Page Settings
    jQuery('#module').val(page.module);
    jQuery('#cm_cmd').val(page.cmd);
    jQuery('#skin').val(page.skin);
    jQuery('#cssName').val(page.cssName);
    jQuery('#customContent').val(page.customContent);
    jQuery('#target').val(page.target);
    jQuery('#caching').val(page.caching);
    jQuery('#slug').val(page.slug);


/*                'editingStatus' =>  $page->getEditingStatus(),
                'display'       =>  $page->getDisplay(),
                'active'        =>  $page->getActive(),*/

//                'type'          =>  $page->getType(),

//                'protection'    =>  $page->getProtection(),
};
