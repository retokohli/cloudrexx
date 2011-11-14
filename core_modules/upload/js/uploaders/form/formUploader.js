var FormUploader = function(uploaderDiv) {
    var div = $J(uploaderDiv);

    var bindDelete = function(fileDiv) {
        fileDiv.find('.delete:first').bind('click',function(event) {
            //only delete if it's not the last entry
            if(div.find('.file').length > 1) {
                fileDiv.remove();
            }
            return false; //do not follow link
        });        
    };

    var add = function() {
        //take first file entry to make a copy
        var file = uploaderDiv.find('.file:first').clone();
        //clear selected file
        file.find('input[type=file]:first').attr('value','');
        //append the div
        file = file.appendTo(div.find('.files:first'));
        //set delete handler
        bindDelete(file);
    };

    div.find('.add:first').bind('click',function() {
        add();
        return false; //do not follow link
    });
    bindDelete(div.find('.file:first'));

    return {
        add:add
    };
};