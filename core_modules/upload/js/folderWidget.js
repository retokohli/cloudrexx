var FolderWidget = function(options) {
    var container = options.container; //the DOM container where we populate the list into
    var id = options.id; //the unique widget id
    var refreshUrl = options.refreshUrl; //the url to get fresh folder data from
    var deleteUrl = options.deleteUrl; //the url used to send a delete request for a file
    var $ = $J; //jquery at $ locally

    var refresh = function() {
        $.getJSON(
            refreshUrl,
            {
                folderWidgetId: id
            },
            function(json) {
                container.empty();
                var ul = $J('<ul></ul>');
                $.each(json, function(index, file) {
                    var li = $J('<li></li>').html(file);
                    var del = $J('<a></a>').html('x');
                    del.attr('href','');
                    del.bind('click', function() {
                        var theFile = li;
                        $J.get(deleteUrl,
                            {
                                file: file,
                                folderWidgetId: id
                            },
                            function() {
                                theFile.remove(); //remove the li elem representing the file
                            });
                        return false;
                    });
                    del.appendTo(li);
                    li.appendTo(ul);
                });
                ul.appendTo(container);
            }
        );
    };

    //load the files
    refresh();

    return {
        refresh: refresh
    };
};