
/**
 * This will handle PS window states and events
 * 
 * @author PS Team
 */
define('ps_window', ['jquery','ps_helper', 'ps_popup','ps_language'], function () {
    'use strict';

    var $           = arguments[0];
    var ps_helper   = arguments[1];
    var ps_popup    = arguments[2];
    var ps_language = arguments[3];

    var globals   = { 
                        debug           : true,
                        is_focused      : true,
                        last_executed   : true,
                        visibility_id   : null,
                        execution_delay : 1000,
                        visibility_apis : {  
                                            hidden      : 'visibilitychange',
                                            mozHidden   : 'mozvisibilitychange',
                                            webkitHidden: 'webkitvisibilitychange',
                                            msHidden    : 'msvisibilitychange',
                                            oHidden     : 'ovisibilitychange'
                                        },
                        subscriptions   : {},
                        events_initiated: []
                    };

    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will fire event subscriptions
         * @param  string event_name 
         * @return void
         */
        fire_subscriptions: function(event_name) {
            if (!ps_helper.empty(globals.subscriptions[event_name])) {
                for (var id in globals.subscriptions[event_name]) {
                    if ($.isFunction(globals.subscriptions[event_name][id])) {
                        globals.subscriptions[event_name][id]();
                    }
                }

                callables.debug('window '+event_name+' event subscriptions fired!');
            }
        },

        /**
         * This will initiate event handlers
         * @param  string event_name 
         * @return void
         */
        initiate_event: function(event_name) {
            switch(event_name)   {
                case 'focus': 
                case 'blur':
                    if (!ps_helper.in_array('visibilitychange', globals.events_initiated)) {
                        globals.events_initiated.push('visibilitychange');
                        callables.visibilitychange_event();
                    }

                case 'resize_width': // resized width only
                case 'resize_height': // resized height only
                case 'resize_both': // must resized both 
                case 'resize': // resized any of the two

                    if (!ps_helper.in_array('resize', globals.events_initiated)) {
                        globals.events_initiated.push('resize');
                        callables.resize_event();
                    }
            }
        },

        /**
         * resize_width, resize_height, resize initiator
         * @return {[type]} [description]
         */
        resize_event: function() {
            globals.window_width  = ps_helper.viewport_width($(window));
            globals.window_height = ps_helper.viewport_height($(window));
            var delay_id = ps_helper.uniqid();

            $(window).on('resize', function() {
                ps_helper.event_delay_animation(function() {
                    var current_width  = ps_helper.viewport_width($(window));
                    var current_height = ps_helper.viewport_height($(window));
                    
                    if (current_width!=globals.window_width) {
                        callables.fire_subscriptions('resize_width');
                    }

                    if (current_height!=globals.window_height) {
                        callables.fire_subscriptions('resize_height');
                    }

                    if (current_width!=globals.window_width && current_height!=globals.window_height) {
                        callables.fire_subscriptions('resize_both');
                    }

                    if (current_width!=globals.window_width || current_height!=globals.window_height) {
                        callables.fire_subscriptions('resize');
                    }

                    globals.window_width  = current_width;
                    globals.window_height = current_height;

                }, 300, delay_id);
            });
        },

        /**
         * visibility change handler
         * @return string             event name used
         */
        visibilitychange_event: function() {

            // is_focused new default using hasfocus
            if (typeof document.hasFocus !== 'undefined') {
                globals.is_focused    = document.hasFocus();
                globals.last_executed = globals.is_focused;
            }

            // check first if visibility API is supported
            for (var property in globals.visibility_apis) {
                if (typeof document[property] !== 'undefined') {

                    // is_focused new default using visibility api
                    globals.is_focused    = (!document[property]);
                    globals.last_executed = globals.is_focused;

                    (function(property) {

                        $(document).on(globals.visibility_apis[property], function() {
                            var is_focused = (!document[property]);
                            callables.visibility_changed(is_focused);
                        });

                    }(property));

                }
            }

            $('window').on('focus', function() {
                callables.visibility_changed(true);
            });

            $('window').on('blur', function() {
                callables.visibility_changed(false);
            });

        },

        /**
         * Event fired when visibility changed
         * @param  boolean status
         * @return void
         */
        visibility_changed: function(status) {
            if (globals.is_focused != status) {
                globals.is_focused    = status; 
                var visibility_id     = ps_helper.uniqid();
                globals.visibility_id = visibility_id;

                // delay
                setTimeout(function() {
                    // check first if the ID we passed is still the latest
                    if (visibility_id == globals.visibility_id && globals.last_executed != globals.is_focused) {
                        globals.last_executed = globals.is_focused;
                        var event_name = globals.is_focused ? 'focus' : 'blur';
                        callables.fire_subscriptions(event_name);
                    }
                }, globals.execution_delay);
            }
        }
    };

    return {
        // return generated ID for this window
        id: ps_helper.uniqid(),

        /**
         * this will return current window focused state
         * @return Boolean
         */
        is_focused: function() {
            return globals.is_focused;
        },

        /**
         * subscribe to ficus/blur window event
         * @param  string   event_name   
         * @param  function callback 
         * @param  object   settings
         * @return void
         */
        subscribe: function(event_name, callback, settings) {
            if ($.isFunction(callback)) {

                if (ps_helper.empty(globals.subscriptions[event_name])) {
                    globals.subscriptions[event_name] = {};
                    callables.initiate_event(event_name);
                }

                settings    = settings    || {};
                settings.id = settings.id || ps_helper.uniqid();
                globals.subscriptions[event_name][settings.id] = callback;

            } else {
                callables.debug('ps_window.subscribe 2nd argument must be a function');
            }
        },

        /**
         * This will unsubscribe event callback with given id
         * @param  string event_name   
         * @param  string id 
         * @return void
         */
        unsubscribe: function(event_name, id) {
            if (!ps_helper.empty(globals.subscriptions[event_name])) { 
                delete globals.subscriptions[event_name][id];
            }
        },

        /**
         * Create new instance of window
         * @param  callback
         * @return object
         */
        new_instance: function(callback, name) {
            name = name || ps_helper.uniqid();

            if (!$.isFunction(callback)) {
                callables.debug('ps_window.new_instance requires first argument as function');
                return false;
            }

            var window_object     = null;
            var is_open           = false;
            var is_watching_close = false;
            var instance_events   = {};

            var methods = {
                /**
                 * Window open wrapper
                 * @param  string  URL    
                 * @param  string  name   
                 * @param  string  specs   
                 * @param  boolean replace 
                 * @return void
                 */
                open    : function(URL, specs, replace) {
                            methods.close();

                            window_object = window.open(URL, name, specs, replace);

                            if (window_object && typeof window_object.closed != 'undefined') {

                                is_open = true;
                                if (!is_watching_close && !ps_helper.empty(instance_events.close)) {
                                    methods.watch_close();
                                }

                            } else {

                                callables.debug('A popup was blocked.');
                                var message = ps_language.error(ps_language.popup_blocked_error);
                                ps_popup.toast.open(message.content, {
                                    title: message.title,
                                    type : 'alert',
                                    auto : true
                                });
                                
                            }

                        },

                /**
                 * Redirect if window is open
                 * NOTE: IE Loses window instance when redirecting URL.
                 * @param  string  URL    
                 * @return void
                 */
                redirect: function(URL) {
                            if (is_open && window_object && typeof window_object.closed != 'undefined') {
                                window_object.location.href = URL;
                                return true;
                            }
                            return false;
                        },

                /**
                 * Resize a window
                 * @param  int width 
                 * @param  int height 
                 * @return void
                 */
                resize: function(width, height) {
                            if (is_open && window_object && typeof window_object.closed != 'undefined') {
                                window_object.resizeTo(width, height);
                            }
                        },

                /**
                 * Close if window is open  
                 * @return void
                 */
                close   : function() {
                            if (is_open && window_object && typeof window_object.closed != 'undefined') {
                                window_object.close();
                            }

                            is_open = false;
                        },

                /**
                 * Check if window is open
                 * @return boolean
                 */
                is_open : function() {
                            return is_open && window_object && typeof window_object.closed != 'undefined';
                        },

                /**
                 * This will keep triggering every 1 sec while window is open
                 * @return void
                 */
                watch_close: function() {
                                if (!window_object || window_object.closed) {

                                    if ($.isPlainObject(instance_events.close)) {
                                        for (var id in instance_events.close) {
                                            instance_events.close[id]();
                                        }
                                    }

                                    is_watching_close = false;

                                } else {

                                    is_watching_close = true;
                                    setTimeout(methods.watch_close, 1000);

                                }
                            },

                /**
                 * Bind events
                 * Available events: close
                 * @param  string   event   
                 * @param  function callback
                 * @param  string   id    
                 * @return void
                 */
                on      : function(event, callback, id) {

                            if ($.isFunction(callback)) {
                                if (!instance_events.hasOwnProperty[event]) {
                                    instance_events[event] = {};
                                }

                                id = id || ps_helper.uniqid();
                                instance_events[event][id] = callback;

                                switch (event) {
                                    case 'close': 

                                        if (!is_watching_close && is_open) {
                                            methods.watch_close(); 
                                        }

                                        break;
                                }
                            }
                        },

                /**
                 * Unbind event
                 * if no id this will delete all callback under the event given
                 * @param  string event 
                 * @param  string id    
                 * @return void
                 */
                off     : function(event, id) {
                            if ($.isPlainObject(instance_events[event])) {
                                if (ps_helper.empty(id)) {
                                    // per event
                                    delete instance_events[event];
                                } else {
                                    // per id
                                    delete instance_events[event][id];
                                    if (ps_helper.empty(instance_events[event])) {
                                        delete instance_events[event];
                                    }
                                }
                            }
                        }
            };

            return callback(methods);
        }
    };
});
