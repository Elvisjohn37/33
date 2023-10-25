
/**
 * This will handle PS Real time updates via websocket
 * 
 * @author PS Team
 */
define('ps_websocket', [
    'jquery',
    'ps_helper',
    'ps_model',
    'ps_popup',
    'ps_language',
    'ps_view',
    'socketio'
], function() {

    var $               = arguments[0];
    var ps_helper       = arguments[1];
    var ps_model        = arguments[2];
    var ps_popup        = arguments[3];
    var ps_language     = arguments[4];
    var ps_view         = arguments[5];
    var socketio        = arguments[6];

    var globals   = { 
                        debug         : true,
                        topics        : [],
                        ping_interval : false,
                        dc_whitelist  : [

                                        ],
                        ping_delay    : 50000,
                        subscriptions : {},
                        connected     : false,
                        reconnect_attempt : 0,
                        reconnect_limit : 6,
                        
                        connection_settings: {
                            //reconnectionAttemps set to 60 instead of 10 fllowup to leo
                                                maxRetries: 60,
                                                retryDelay: 2000
                                            },
                        socket        : null,
                        socket_options: {
                                            path: "/ws", // default: /socket.io

                                            // reconnection: true, // default, true

                                            reconnectionAttempts: 6, // number of reconnection attempts before giving up

                                            // how long to initially wait before attempting a new reconnection (1000).
                                            // Affected by +/- randomizationFactor, for example the default initial 
                                            // delay will be between 500 to 1500ms.
                                            //set to 
                                            reconnectionDelay: 5000,

                                            // maximum amount of time to wait between reconnections
                                            reconnectionDelayMax: 5000, 

                                            randomizationFactor: 0, // 0 <= randomizationFactor <= 1

                                            // by setting this false, you have to manually connect via .connect() call
                                            autoConnect: false,

                                            // engine.IO options
                                            // WARNING: in this case, there is no fallback to long-polling
                                            transports: ["websocket"],

                                            // If true and if the previous websocket connection to the server succeeded,
                                            // the connection attempt will bypass the normal upgrade process and will 
                                            // initially try websocket.
                                            rememberUpgrade: true
                                        }
                    };

    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will fire event subscriptions
         * @param  string event_name 
         * @param  mixed  message 
         * @return void
         */
        fire_subscriptions: function(event, message) {

            if (!ps_helper.empty(globals.subscriptions[event])) {
                for (var id in globals.subscriptions[event]) {
                    if ($.isFunction(globals.subscriptions[event][id])) {
                        globals.subscriptions[event][id](message);
                    }
                }

                callables.debug('websocket '+event+' event subscriptions fired!');
            }
        },        

        /**
         * Disconnection handler of WS
         * @return void
         */
        ondisconnect: function(reason) {
            
            if (globals.reconnect_limit > globals.reconnect_attempt) {

                globals.socket.connect();
                globals.reconnect_attempt++;
                return;
            }
            
            // dc notification
            var disconnection_error = ps_language.error('ERR_00095');

            ps_popup.toast.open(disconnection_error.content, {
                id   : 'websocket',
                title: disconnection_error.title,
                auto : false
            });
        },

        /**
         * This will fire when socketIO is connected then validate socketID
         * @param  object data 
         * @return       
         */
        onconnected: function(data) {
            if (data.success == false) return;

            var data = callables.validate_ws(globals.socket.id);
        },

        onerror: function(error) {
        //what will do if socket error
        },

        /**
         * Validate socketID to WS server
         * @param  {string} socket_id 
         * @return 
         */
        validate_ws: function(socket_id) {


            ps_model.validate_ws(socket_id);
        },
        /**
         * This will fire websocket received message
         * @param  {object} data 
         * @return 
         */
        onmessage: function(data) {

            var event   = data.event.toLowerCase();
            var message = data.message;

            callables.fire_subscriptions(event, message);

        },

        /**
         * websocket on validate will check if validation success
         * @param  {object} data 
         * @return 
         */
        onvalidated: function(data) {

            if (data.success == false) return;

            globals.reconnect_attempt = 0;

        },

        /**
         * bind custom for socket events
         * @return 
         */
        bind_socket_events: function() {


            globals.socket.on("connected", callables.onconnected);
            
            globals.socket.on("disconnect", callables.ondisconnect);

            globals.socket.on("message", callables.onmessage);

            globals.socket.on("validated", callables.onvalidated);


            // globals.socket.on("error", function(error) {
            //     console.log(error);
            // });

            // globals.socket.on("connect_error", function(error) {
            //     console.log(error);
            // });

            // globals.socket.on("connect_timeout", function(timeout) {
            //     console.log(timeout);
            // });

            // globals.socket.on("reconnect_error", function(error) {
            //     console.log(error);
            // });

            // globals.socket.on("reconnect", function(message) {
            //     console.log(message);
            // });

            globals.socket.on("reconnect_failed", function() {
                console.log('reconnection failed');
            });



        },

        /**
         * This will start our websocket by pointing to correct handlers
         * @return void
         */
        init: function() {
            // get websocket configurations
            ps_model.view_data({
                success: function(view_data) {
                        globals.socket = socketio(view_data.websocket.url, globals.socket_options);

                        callables.bind_socket_events();
                        globals.socket.connect();
                    
                    

                        globals.topics = view_data.websocket.topics;
                        }
            },['websocket']);
        },

        /**
         * subscribe to ficus/blur window event
         * @param  string   category   
         * @param  function callback 
         * @param  object   settings
         * @return void
         */
        subscribe: function(category, callback, settings) {

            if ($.isFunction(callback)) {
                
                // initiate WS on first subscription
                if (ps_helper.empty(globals.subscriptions)) {
                    callables.init();
                }

                if (ps_helper.empty(globals.subscriptions[category])) {
                    globals.subscriptions[category] = {};
                }

                settings    = settings    || {};
                settings.id = settings.id || ps_helper.uniqid();
                globals.subscriptions[category][settings.id] = callback;

            } else {
                callables.debug('ps_window.subscribe 2nd argument must be a function');
            }
        }
    };

    return {
        subscribe: callables.subscribe,

        /**
         * This will unsubscribe event callback with given id
         * @param  string category   
         * @param  string id 
         * @return void
         */
        unsubscribe: function(category, id) {

            if (!ps_helper.empty(globals.subscriptions[category])) { 
                delete globals.subscriptions[category][id];
            }
        },

        /**
         * All Global WS category that can initiate any module, or execute any function
         * @return void
         */
        global_subscriptions: function() {

            ps_model.view_data({

                success: function(view_data) {
                            var global_subs_length = view_data.websocket.global_subs.length;

                            for (var i = 0; i < global_subs_length; i++) {
                                var category = view_data.websocket.global_subs[i];

                                switch(category) {
                                    
                                    /**
                                     |----------------------------------------------------------------------------------
                                     | OPEN AVATAR
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'open_avatar': 

                                        callables.subscribe('open_avatar', function(message) {
                                            require(['ps_window'], function(ps_window) {
                                                if (ps_helper.empty(message) && ps_window.is_focused()) {
                                                    require(['ps_avatar'], function(ps_avatar) {
                                                        ps_avatar.open_avatar_modal();
                                                    });
                                                }
                                            });
                                        });

                                        break;

                                    /**
                                     |----------------------------------------------------------------------------------
                                     | UPDATE BALANCE
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'balance': 
                                    
                                        callables.subscribe('balance', function(message) {
                                            var delay = ps_helper.empty(message.delay) ? 0 : parseInt(message.delay);

                                            if (delay > 0) {
                                                setTimeout(ps_model.get_balance, delay);
                                            } else {
                                                ps_model.get_balance();
                                            }
                                        });

                                        break;

                                    /**
                                     |----------------------------------------------------------------------------------
                                     | SESSION TIME OUT
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'timeout': 
                                    
                                        callables.subscribe('timeout', function(message) {
                                            ps_model.verify_auth();
                                        });

                                        break;

                                    /**
                                     |----------------------------------------------------------------------------------
                                     | CLIENT STATUS
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'client_status': 

                                        callables.subscribe('client_status', function(message) {
                                            ps_model.update_member_status(message);
                                            
                                            if (!ps_helper.empty(message.status_error)) {
                                                ps_popup.client_status_notification(
                                                    message.status_error, 
                                                    message.is_active
                                                );
                                            }
                                        });

                                        break;

                                    /**
                                     |----------------------------------------------------------------------------------
                                     | VALIDATED
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'validated': 

                                        callables.subscribe('validated', function(message) {
                                            callables.debug(message);
                                        });

                                        break;
                                    
                                    /**
                                     |----------------------------------------------------------------------------------
                                     | REFRESH
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'refresh': 
                                    
                                        callables.subscribe('refresh', function(message) {
                                            require(['ps_window'], function(ps_window) {
                                                if (ps_helper.empty(message) || message!=ps_window.id) {
                                                    window.location = '';
                                                }
                                            });
                                        });

                                        break;
                                    
                                    /**
                                     |----------------------------------------------------------------------------------
                                     | CLEAR LOCAL STORAGE
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'clear_storage': 

                                        callables.subscribe('clear_storage', function(message) {
                                            require(['ps_localstorage'], function(ps_localstorage) {

                                                if (ps_helper.empty(message.id)) {
                                                    ps_localstorage.remove_all();
                                                } else {
                                                    ps_localstorage.remove(message.id);
                                                }

                                            });
                                        });

                                        break;
                                    
                                    /**
                                     |----------------------------------------------------------------------------------
                                     | LOG OUT
                                     |----------------------------------------------------------------------------------
                                     */
                                    case 'lo': 

                                        callables.subscribe('lo', function(message) {
                                            ps_model.logout({
                                                success: function() {
                                                            window.location = '';
                                                        }
                                            });
                                        });

                                        break;
                                }
                            }
                        }

            },['websocket']);
        }
    };
});
