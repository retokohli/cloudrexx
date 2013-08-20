var FolderWidget = function(options) {
    var container = options.container; //the DOM container where we populate the list into
    var id = options.id; //the unique widget id
    var refreshUrl = options.refreshUrl; //the url to get fresh folder data from
    var deleteUrl = options.deleteUrl; //the url used to send a delete request for a file
    var files = []; //the files in target folder
    var fieldId = options.fieldId;
    var $ = jQuery; //jquery at $ locally

    var refresh = function() {
        $.getJSON(
            refreshUrl,
            {folderWidgetId: id },
            function(json) {
                setFiles(json);
                list();
            }
        );
    };

    var setFiles = function(passedFiles) {
        files = passedFiles;
    }

    var list = function() {
        // clear folder widget
        container.empty();

        // folder is empty -> stop
        if (isEmpty()) {
            return;
        }

        // list files
        var ul = $('<ul></ul>').appendTo(container);
        $.each(files, function(i, file) {
            var li = $('<li></li>');
            var span = $('<span></span>').html(file);
            var del = $(' &nbsp; <a></a>');
            del.addClass('deleteIcon');
            del.attr('href','');
            del.bind('click', function() {
                if (deleteUrl) {
                    $.get(deleteUrl, {
                            file: file,
                            folderWidgetId: id
                        },
                        function() {                               
                            li.remove();
                        }
                    );
                } else {
                    $('<input type="hidden" name="deleteMedia['+fieldId+'][]" value="'+file+'" />').appendTo($(container.parents('form')));
                    li.remove();
                }
                return false;
            });

            span.appendTo(li);
            del.appendTo(li);
            li.appendTo(ul);
        });
    }

    //load the files
    refresh();

    var isEmpty = function() {
        return files.length == 0;
    };

    return {
        refresh: refresh,
        isEmpty: isEmpty,
        setFiles: setFiles,
        list:    list
    };
};
