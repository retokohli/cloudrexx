(function(){ //autoexec-closure

//first dependencies: logic
//find correct dialog css
var jqueryUiSkin  = cx.variables.get('jQueryUiCss', 'contrexx-ui');
var lang = cx.variables.get('language', 'contrexx');
var requiredFiles = [
    'lib/javascript/jquery/ui/jquery-ui-1.8.7.custom.min.js',
    'lib/javascript/jquery/ui/jquery-ui-timepicker-addon.js',
    jqueryUiSkin,
    'lib/javascript/jquery/tools/jquery.tools.min.js'
];
//second dependencies: i18n
var datepickerI18n = cx.variables.get('datePickerI18nFile', 'jQueryUi');
if (datepickerI18n) {
    requiredFiles.push(datepickerI18n);
}

//load the css and jquery ui plugin
cx.internal.dependencyInclude(
    requiredFiles,
    function() {

        /**
         * Contrexx JS API: User Interface extension
         */
        var UI = function() {
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
                var classname = options.dialogClass ? options.dialogClass : 0;
                var buttons = options.buttons ? options.buttons : {};
                var openHandler = options.open ? options.open : function () {};
                var closeHandler = options.close ? options.close : function () {};

                var position = options.position;

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
                        dialogClass:classname,
                        modal:modal,
                        open: function (event, ui) {
                            opened = true;
                            $J(event.target).parent().css('top', '30%');
                            openHandler(event, ui);
                        },
                        close: function (event, ui) {
                            opened = false;
                            closeHandler(event, ui);
                        },
                        autoOpen:autoOpen,
                        position:position,
                        buttons:buttons
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
                    getElement: function() {
                        return dialogDiv;
                    },
                    isOpen: function() {
                        return opened;
                    },
                    bind: function(event, handler) {
                        events.bind(event, handler);
                    }
                };        
            };

            /**
             * A contrexx tooltip.
             */
            var Tooltip = function(element) {
                if (typeof(element) == 'undefined') {
                    $('.tooltip-trigger').tooltip({relative: true, position: 'center right', offset: [0, 10], predelay: 250});
                    $('.tooltip-trigger .no-relative').tooltip({relative: false, position: 'center right', offset: [0, 10], predelay: 250});
                } else {
                    if ($(element).hasClass('no-relative')) {
                        $(element).tooltip({relative: false, position: 'center right', offset: [0, 10], predelay: 250});
                    } else {
                        $(element).tooltip({relative: true, position: 'center right', offset: [0, 10], predelay: 250});
                    }
                }
            };

            //public properties of UI
            return {
                dialog: function(options)
                {
                    return new Dialog(options);
                },
                tooltip: function(element)
                {
                    return new Tooltip(element);
                }
            };
        };

        //add the functionality to the global cx object
        cx.ui = new UI();

        //initialize tooltips
        cx.ui.tooltip()

    }, //end of dependencyInclude: callback
    true //dependencyInclude: chain
);


//end of autoexec-closure
})();