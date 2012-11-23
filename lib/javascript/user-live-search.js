cx.ready(function() {
    var replaceInput   = function(element) {
        var objElement = $J(element);
        return objElement.clone().attr({type: "hidden", class: objElement.attr("class") + "-replaced"}).prop("outerHTML");
    }
    
    $J(".live-search-user-id").next(".live-search-user-name").replaceWith(function() {
        return replaceInput(this);
    });
    
    $J(".live-search-user-id").replaceWith(function() {
        userTitle = $J(this).next(".live-search-user-name").val();
        if (userTitle == "" || userTitle == undefined || userTitle == null) {
            $J.ajax({
                url:      "index.php?cmd=jsondata&object=user&act=getUserById",
                data:     {id: $J(this).val()},
                dataType: "json",
                async:    false,
                success:  function(response) {
                    $J(this).val(response.data.id);
                    userTitle = response.data.title;
                }
            });
        }
        var replace = 
            replaceInput(this) +
            "<img class=\"live-search-user-icon\" src=\"images/icons/icon-user.png\" alt=\"User icon\" />" +
            "<span class=\"live-search-user-title\">" + userTitle + "</span>" +
            "<a class=\"live-search-user-edit\" href=\"#\">" +
                "<img src=\"images/icons/icon-user-edit.png\" alt=\"Edit user\" />" +
            "</a>"
        ;
        return replace;
    });
    
    var userSearchDialog = cx.ui.dialog({
        width:       500,
        height:      170,
        modal:       true,
        autoOpen:    false,
        dialogClass: "ui-dialog-live-search-user",
        title:       cx.variables.get("txtUserSearch", "user/live-search"),
        content:     "<div class=\"live-search-user-info\">" + cx.variables.get("txtUserSearchInfo", "user/live-search") + "</div>" +
                     "<input class=\"live-search-user-input ui-corner-all\" placeholder=\"" + cx.variables.get("txtUserSearch", "user/live-search") + "...\" />",
        buttons:     {
            "OK": function() {
                var input = $J(this).children(".live-search-user-input");
                var value = $J.trim(input.val());
                if (value.length >= cx.variables.get("userMinLength", "user/live-search")) {
                    $J("input[name=" + input.attr("name").split("-")[0] + "]").val(value).prev().prev(".live-search-user-title").text(value).prev().prev(".live-search-user-id-replaced").val(0);
                    $J(this).dialog("close");
                } else {
                    input.css("border", "1px solid #FF0000")
                }
            }
        }
    });
    
    userSearchDialog.bind("open", function() {
        $J(".ui-dialog-live-search-user").children(".ui-dialog-content").css("height", "70px");
    });
    
    userSearchDialog.bind("close", function() {
        $J(".live-search-user-input").val("").removeAttr('name').removeAttr('style');
    });
        
    $J(".live-search-user-edit").click(function(event) {
        event.preventDefault();
        
        var userEdit         = $J(this);
        var userName         = userEdit.next(".live-search-user-name-replaced");
        var dialog           = $J(".ui-dialog-live-search-user");
        var dialogButtonpane = dialog.children(".ui-dialog-buttonpane");
        
        if (userName.length) {
            dialogButtonpane.show();
            $J(".live-search-user-input").attr("name", userName.attr("name") + "-search");
        } else {
            dialogButtonpane.hide();
        }
        
        userSearchDialog.open();
        
        $J(".live-search-user-input").autocomplete({
            source: function(request, response) {
                $J.getJSON("index.php?cmd=jsondata&object=user&act=getUsers", {
                    term: request.term
                },
                function(data) {
                    var users = new Array();
                    for (id in data.data) {
                        var user = {
                            id: id,
                            value: data.data[id]
                        };
                        users.push(user);
                    }
                    response(users);
                });
            },
            search: function() {
                if ($J.trim(this.value).length < cx.variables.get("userMinLength", "user/live-search")) {
                    return false;
                }
            },
            select: function(event, ui) {
                userEdit.prev(".live-search-user-title").text(ui.item.value).prev().prev(".live-search-user-id-replaced").val(ui.item.id);
                userSearchDialog.close();
                // Must be reset additionally
                ui.item.value = "";
            }
        });
    });
});