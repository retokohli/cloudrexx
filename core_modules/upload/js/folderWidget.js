var FolderWidget = function(options) {
    var container = options.container; //the DOM container where we populate the list into
    var id = options.id; //the unique widget id
    var refreshUrl = options.refreshUrl; //the url to get fresh folder data from
    var deleteUrl = options.deleteUrl; //the url used to send a delete request for a file
    var files = []; //the files in target folder
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
                files = json;
                $.each(files, function(index, file) {
                    var li = $J('<li></li>').html(file);
                    var del = $J(' &nbsp; <a></a>').html('x');
                    del.attr('href','');
                    del.bind('click', function() {
                        var fileElement = li;
                        var fileName = file;
                        $.get(deleteUrl,
                            {
                                file: file,
                                folderWidgetId: id
                            },
                            function() {                               
                                fileElement.remove(); //remove the li elem representing the file
                                //remove the file from our local files array
                                //this makes sure isEmpty() doesn't lie 
                                for(index in files) {
                                    var file = files[index];
                                    if(file == fileName)
                                        files.splice(index, 1);
                                }
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

    var isEmpty = function() {
        return files.length == 0;
    };

    return {
        refresh: refresh,
        isEmpty: isEmpty
    };
};