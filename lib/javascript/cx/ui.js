(function(){ //autoexec-closure

//find correct dialog css
var jqueryUiSkin  = cx.variables.get('jQueryUiCss', 'contrexx-ui');

//load the css and jquery ui plugin
cx.internal.dependencyInclude(
    [
        'lib/javascript/jquery/ui/jquery-ui-1.8.7.custom.min.js',
        jqueryUiSkin
    ],
function(){
   
/**
 * Contrexx JS API: User Interface extension
 */
var UI = function(){
    //we want jQuery at $ locally
    var $ = cx.jQuery;
    /**
     * A contrexx dialog.
     * 
     * @param array options {
     *     [ title: 'the title', ]
     *     content: <content-div> | 'html code',
     *     modal: <boolean>,
     *     autoOpen: <boolean> (default true)
     * }
     */
    var Dialog = function(options) {
        var opened = false; //is the dialog opened?

        //option handling
        var title = options.title;
        var content = options.content;

        var autoOpen;
        if(typeof(options.autoOpen) != "undefined")
            autoOpen = options.autoOpen;
        else
            autoOpen = true;
        
        var modal;
        if(typeof(options.modal) != "undefined") 
            modal = options.modal;
        else
            modal = false;

        var height = options.height ? options.height : 0;
        var width = options.width ? options.width : 0;

        //events the user specified handlers for
        var requestedEvents = options.events ? options.events : null;
        
        var dialogDiv;

        //event handling
        var events = new cx.tools.Events();

        //create bind to new event on the dialog for each bind request of user
        events.newBehaviour(function(name){
            dialogDiv.dialog().bind('dialog'+name, function(){
                events.notify(name);
            });
        });

        //event handling
        var closeHandler = function() {
            opened = false;            
        };
        var openHandler = function() {
            opened = true;  
        };

        var createDialogDiv = function() {            

            if(typeof(content) != 'string') { //content is a div
                dialogDiv = $(content);
            }
            else { //content is a html string
                //create a hidden div...
                dialogDiv = $('<div></div>').css({display:'none'});
                //...set the content and append it to the body
                dialogDiv.html(content).appendTo('body:first');
            }

            if(title) //set title if specified (user could also set it in html)
                dialogDiv.attr('title',title);

            //remove all script tags; jquery fires DOM ready-event on dialog creation
            //scripts have already been parsed once at this point and would be parsed
            //twice if they're in a "jQuery(function(){...})"-statement
            var scripts = dialogDiv.find("script").remove();

            //the options that we pass to the jquery ui dialog constructor
            var dialogOptions = {
                modal:modal,
                open:openHandler,
                close:closeHandler,
                autoOpen:autoOpen             
            };          
            //handle height and width if set
            if(height > 0)
                dialogOptions.height = height;

            if(width > 0)
                dialogOptions.width = width;
            
            //init jquery ui dialog
            dialogDiv.dialog(dialogOptions);

            //bind all requested events
            if(requestedEvents) {
                $.each(requestedEvents, function(event, handler){
                    events.bind(event, handler);
                });
            }
        };

        createDialogDiv();
       
        //public properties of Dialog
        return {
            close: function() {
                dialogDiv.dialog('close');
            },
            open: function() {
                dialogDiv.dialog('open');
            },
            isOpen: function() {
                return opened;
            },
            bind: function(event, handler) {
                events.bind(event, handler);
            }
        };        
    };

    //public properties of UI
    return {
        dialog:function(options)
        {
            return new Dialog(options);
        }
    };
};

//add the functionality to the global cx object
cx.ui = new UI();

//end of dependencyInclude
});
//end of autoexec-closure
})();