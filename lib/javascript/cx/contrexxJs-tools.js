/**
 * This holds common patterns used throughout the Contrexx JS API and is intended to minimize future coding efforts.
 */
cx.tools = {};

/**
 * A class implementing Event handling based on a pimped Observer-Pattern
 */
cx.tools.Events = function() {
    //an object holding all events known
    var events = {};

    //a function called as soon as a so far unknown event is bound
    var newEventHandler = null;

    //public properties of Events
    return {
        //call callback on event
        bind: function(name, callback) {
            if(!events[name]) { //event unbound so far
                events[name] = []; //create array for callbacks
                if(newEventHandler) //if a handler is set, call it
                    newEventHandler(name);
            }
            //assign the callback
            events[name].push(callback);
        },
        //sets a function eh(name) called before a so far unboud event is getting bound
        newBehaviour: function(callback) {
            newEventHandler = callback;
        },
        //calls all callbacks for event 'name' with the data provided as argument
        notify: function(name,data) {
            if(events[name]) {
                cx.jQuery.each(events[name],function(index,callback) {
                    callback(data);
                });
            }
        }
    };
};

/**
 * StatusMessage function to show status messages
 * currently used in content manager and frontend editing
 *
 * @param options
 * @returns {{showMessage: Function, removeAllDialogs: Function}}
 * @constructor
 */
cx.tools.StatusMessage = function(options) {
    var timeout = null;

    /**
     * Messages to show (used as wait list)
     * @type {Array}
     */
    var messages = [];

    /**
     * The default options for the dialog
     * @type {{draggable: boolean, resizable: boolean, minWidth: number, minHeight: number, dialogClass: string, position: Array}}
     */
    var defaultOptions = {
        draggable: false,
        resizable: false,
        minWidth: 100,
        minHeight: 28,
        dialogClass: "cxDialog noTitle",
        position: ["center", "top"]
    };

    /**
     * Merged options with default options
     * @type {*}
     */
    var options = cx.jQuery.extend({}, defaultOptions, options);

    /**
     * Shows a new message in dialog
     *
     * If a message is currently displayed, put the message into the wait list
     *
     * @param message Message to show
     * @param cssClass Additional css class (for example 'warning' or 'error')
     * @param showTime After the amount of seconds, the dialog will be destroyed and the next message shown
     * @param callbackAfterDestroy This function is called after the showTime
     */
    var showMessage = function(message, cssClass, showTime, callbackAfterDestroy) {
        removeAllDialogs();
        messages.push({
            message: message,
            cssClass: cssClass,
            showTime: showTime,
            callbackAfterDestroy: callbackAfterDestroy
        });
        if (!cx.tools.StatusMessage.timeout) {
            displayMessage();
        }
    };

    /**
     * Display the first message from the messages array
     */
    var displayMessage = function() {
        if (messages.length == 0) return;
        var message = cx.jQuery(messages).first()[0];

        cx.jQuery("<div class=\"" + message.cssClass + "\">" + message.message + "</div>").dialog(options);
        if (message.showTime) {
            timeout = setTimeout(
                function() {
                    removeAllDialogs();

                    if (message.callbackAfterDestroy) {
                        message.callbackAfterDestroy();
                    }

                    // remove first element from messages array
                    messages.splice(0, 1);
                    displayMessage();
                }, message.showTime);
        }
    };

    /**
     * Remove all dialogs from screen and clear the messages wait list array
     */
    var removeAllDialogs = function() {
        clearTimeout(timeout);
        messages = [];
        cx.jQuery(".cxDialog .ui-dialog-content").dialog("destroy");
    };

    // return the public functions, so they can be used by controllers
    return {
        showMessage: showMessage,
        removeAllDialogs: removeAllDialogs
    };
};
