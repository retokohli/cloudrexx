/**
 * @param theConfig Object {
 *   div: <advancedFileUploader-div>
 *   uploaders: [
 *     { 
 *       type: 'uploader_type',
 *       description: 'uploader_name' 
 *     } 
 *   ],
 *   uploadId: upload_id,
 *   switchUrl: 'switch_url',
 *   otherUploadersCaption: 'captionstring'
 * }
 */
var ComboUploader = function(theConfig) {
    var $ = $J; //we want jquery at $ internally

    var config = theConfig;
    var uploaders = theConfig.uploaders;
    var div = $(config.div);

    var curType = 'form';

    //loads code of another uploader via ajax
    var switchUploader = function(type) {
        $.post(
            config.switchUrl,
            {
                uploadType: type,
                uploadId: config.uploadId
            },
            function(data) {
                var uploaderDiv = div.find('.uploader:first');
                uploaderDiv.html(data);
                curType = type;
            },
            'html' //we specify html here to make sure embedded js is executed
        );
    };

    if(uploaders.length > 1) { //multiple uploaders to choose from, load functionality to switch
        //check what is supported by the browser
        var checkRuntimes = function() {
            var flashSupported = swfobject.getFlashPlayerVersion().major >= 10;

            var javaSupported = false;
            $(deployJava.getJREs()).each(function(index,jre) { //look for an installed jre > 1.4 (jumploader minimum)
                if(parseFloat(jre.substring(0,3)) > 1.4)
                    javaSupported = true;
            });
            
            return { 'flash': flashSupported, 'java': javaSupported, 'form': true};
        };
        //remember the browser's capabilities
        var browserRuntimes = checkRuntimes();
        //uploader type => runtime relation
        var typeRuntimes = {
            'pl': 'flash',
            'jump' : 'java',
            'form' : 'form'
        };

        //makes a click-handler for the switch-links
        var switchLinkClicked = function(type)
        {
            return function() {
                switchUploader(type);
                return false;
            };
        };

        //initialize 'advanced'-menu
        $(uploaders).each(function(index,uploader) {
            //can the browser display the runtime?
            if(browserRuntimes[typeRuntimes[uploader.type]]) {
                var link = $('<a></a>').attr('href','');
                link.bind('click', switchLinkClicked(uploader.type));
                link.html(uploader.description);
                link.appendTo(div.find('.uploaderLinks:first'));
            }
        });

        //initialize 'advanced'-link
        div.find('.advancedLink:first').bind('click',function() {
            var advancedLink = div.find('.advancedLink:first');
            if(!advancedLink.hasClass('expanded')) {
                div.find('.uploaderLinks:first').fadeIn();
                advancedLink.addClass('expanded');
                advancedLink.html(config.otherUploadersCaption + ' &lt;&lt;');
            }
            else {
                div.find('.uploaderLinks:first').fadeOut();
                advancedLink.removeClass('expanded');
                advancedLink.html(config.otherUploadersCaption + ' &gt;&gt;');
            }
            return false;
        });

        //checks if a player is enabled
        var playerEnabled = function(type) {
            var found = false;
            $.each(uploaders, function(index, uploader) {
                if(uploader.type == type)
                    found = true;
                });
            return found;
        };

        //initialize correct player
        if(playerEnabled('pl') && browserRuntimes.flash) //flashy enough for pluploader
            switchUploader('pl');
        else if(playerEnabled('jump') && browserRuntimes.java)//try java if not
        switchUploader('jump');
    }
    else { //only a single player, most likely advanced uploading was disabled
        div.find('.advancedLink:first').remove(); //remove advanced link
    }

    return {
        refresh: function() {
            switchUploader(curType);
        }
    };
};