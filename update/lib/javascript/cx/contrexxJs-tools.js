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