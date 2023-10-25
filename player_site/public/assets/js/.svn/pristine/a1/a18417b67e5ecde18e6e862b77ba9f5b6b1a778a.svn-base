
/**
 * This will handle ps chatbox plugin
 * 
 * @author PS Team
 */
define('ps_chatbox', ['ps_model','ps_helper', 'ps_window', 'jquery', 'ps_localstorage', 'ps_date'], function () {

    var ps_model        = arguments[0];
    var ps_helper       = arguments[1];
    var ps_window       = arguments[2];
    var $               = arguments[3];
    var ps_localstorage = arguments[4];
    var ps_date         = arguments[5];

    var globals   = { native_ws_subscribed: false };
    var callables = {

        init: {
            /**
             * Initialize ative app chatbox
             * @param  object vm 
             * @return void
             */
            native: function(vm) {

                // get chat status
                ps_model.verify_parent({
                    success: function(chat_status) {
                        
                                ps_localstorage.watch('chatbox_is_active', function(value) {
                                    vm.raw_data.is_active = value;
                                });

                                vm.raw_data = {
                                                chat_id             : ps_helper.uniqid(),
                                                status              : chat_status,
                                                unsorted_messages   : { list: [] },
                                                is_active           : ps_localstorage.get('chatbox_is_active'),
                                                is_initiated        : false,
                                                is_scrolled_botom   : true,
                                                chat_block_time     : ps_model.chat_block_time,

                                                // before updated length will count message on vue beforeUpdate
                                                // updated length will  count message on vue update
                                                before_update_length: 0,
                                                udpated_length      : 0
                                            };

                                vm.is_loading = false;

                                // get history immediately if is_active flag is set
                                if (vm.raw_data.is_active) {
                                    // lock or unlock body scroll
                                    vm.chatbox_methods().update_scroll_lock();

                                    callables.native_get_history(vm);
                                }

                                // chatbox event handler
                                vm.$nextTick(function() {

                                    // opener & message fetcher
                                    $(vm.$el).find('.ps_js-chatbox_opener').on('click', function() {
                                        vm.raw_data.is_active = vm.raw_data.is_active ? false : true;
                                        ps_localstorage.set('chatbox_is_active', vm.raw_data.is_active);
                                        
                                        // lock or unlock body scroll
                                        vm.chatbox_methods().update_scroll_lock();

                                        callables.native_get_history(vm);
                                    });

                                    // retry button
                                    $(vm.$el).on('click', '.ps_js-chatbox_retry', function(e) {
                                        var send_id = $(this).data('id');
                                        ps_model.retry_message(send_id);
                                    });

                                    // send
                                    $(vm.$el).find('.ps_js-chatbox_send_form').on('submit', function(e) {
                                        e.preventDefault();

                                        var message = $(this).find('.ps_js-composed').val();
                                        $(this).find('.ps_js-composed').val('').trigger('change');

                                        ps_model.send_message(message);

                                        // scroll to bottom when user send message
                                        var chatbox_body  = $(vm.$el).find('.ps_js-chat_body');
                                        chatbox_body.scrollTop(chatbox_body.prop('scrollHeight'));
                                    });

                                    // seen messages on window state
                                    ps_window.subscribe('focus', function() {
                                        vm.chatbox_methods().seen();
                                    });

                                    // submit on enter if input is textarea type
                                    $(vm.$el).find('.ps_js-composed').not('input').on('keydown', function(e) {
                                        if (!e.shiftKey && e.keyCode == 13) {
                                            e.preventDefault();
                                            $(this).closest('.ps_js-chatbox_send_form').trigger('submit');
                                        }
                                    });

                                    // block timer event
                                    var block_timer = $('.ps_js-chatbox_block_timer');
                                    if (block_timer.length > 0) {
                                        block_timer.on('view_components_stopped',vm.chatbox_methods().unblock);

                                    } else {

                                        vm.$watch('raw_data.status.block_until', function(new_value) {
                                            if (new_value !== null) {

                                                var date_diff = ps_date.diff_date(
                                                                    new_value,
                                                                    ps_date.get_current_date(ps_model.chat_block_time),
                                                                    ['seconds']
                                                                );

                                                setTimeout(
                                                    vm.chatbox_methods().unblock, 
                                                    parseFloat(date_diff.seconds)*1000
                                                );
                                            }
                                        });

                                    }    

                                    // window notification on unload with message still sending
                                    ps_model.view_data({
                                        success: function(view_data) {
                                                    $(window).on('beforeunload', function(e) {
                                                        var messages = vm.raw_data.unsorted_messages.list;

                                                        var sending  = messages.filter(function(value) {
                                                                        return value.sending || value.blocked;
                                                                    });
                                                        if (sending.length > 0) {
                                                            return view_data.lang.messages.confirm_have_unsent;
                                                        }
                                                    });   
                                                }
                                    }, ['lang']);                           
                                });
                callables.native_ws_subscriptions(vm);
                            
                            }   
                });


            },

            /**
             * Initialize live chat inc
             * API documentation: https://docs.livechatinc.com/js-api/
             * @param  object vm 
             * @return void
             */
            livechatinc: function(vm) {
                if (!ps_helper.empty(vm.chat_config.license)) {
                    window.__lc = window.__lc || {};
                    window.__lc.license             = vm.chat_config.license;
                    window.__lc.chat_between_groups = false;

                    require(['livechatinc'], function() {
                        LC_API.on_before_load = function() {
                                                    LC_API.hide_chat_window();
                                                };
                        LC_API.on_after_load  =  function() {
                                                    var status    =  LC_API.agents_are_available() ? 'online':'offline';
                                                    vm.raw_data   = {
                                                                        status: { status: status }
                                                                    };
                                                    vm.is_loading = false;

                                                    vm.$nextTick(function() {

                                                        // opener & message fetcher
                                                        var opener = $(vm.$el).find('.ps_js-chatbox_opener');

                                                        if (opener.length > 0) {

                                                            LC_API.on_chat_window_minimized = LC_API.hide_chat_window;
                                                            
                                                            opener.on('click', function() {
                                                                LC_API.open_chat_window();
                                                            });

                                                        } else {

                                                            LC_API.minimize_chat_window();
                                                            
                                                        }

                                                    });
                                                };
                    });
                }
            },

            /**
             * Initialize Snap engage
             * @param  object vm 
             * @return void
             */
            snapengage: function(vm) {
                if (!ps_helper.empty(vm.chat_config.license)) {

                    require(
                        ['//storage.googleapis.com/code.snapengage.com/js/' + vm.chat_config.license + '.js'],
                        function() {

                            vm.raw_data   = {
                                                status: { status: true }
                                            };

                            SnapEngage.getAgentStatusAsync(function (online) {
                                vm.raw_data.status.status = online ? 'online' : 'offline';
                                vm.is_loading = false;

                                vm.$nextTick(function() {

                                    // opener & message fetcher
                                    var opener = $(vm.$el).find('.ps_js-chatbox_opener');

                                    if (opener.length > 0) {
                                        opener.on('click', function() {
                                            SnapEngage.startChat();
                                        });
                                    }
                                });
                            });
                        }
                    );
                }
            }
        },

        /**
         * This will generate data base on raw_data being set from 'init' method of chatbox
         * @type object
         */
        computed_chatbox_data: {
            /**
             * Computed data for native chatbox
             * @param  object vm
             * @return object
             */
            native: function(vm) {
                var message_length = vm.raw_data.unsorted_messages.list.length;

                return {
                    is_loading      : (vm.raw_data.is_initiated && vm.raw_data.status.is_loading),
                    messages        : vm.raw_data.unsorted_messages.list.sort(function(msg_1, msg_2) {
                                        // order by date first
                                        if (msg_1.dateTime == false && msg_2.dateTime == false) {
                                            var by_date = 0;
                                        } else if(msg_1.dateTime == false) {
                                            var by_date = 1;
                                        } else if(msg_2.dateTime == false) {
                                            var by_date = -1;
                                        } else {
                                            var by_date = new Date(msg_1.dateTime)-new Date(msg_2.dateTime);
                                        }

                                        if (by_date!==0) {
                                            return by_date;
                                        }

                                        // order by .order if date is just the same
                                        var order_1 = msg_1.order;
                                        var order_2 = msg_2.order;
                                        if (!ps_helper.empty(order_1) && !ps_helper.empty(order_2)) {
                                            var by_order = order_1 - order_2;
                                        } else {
                                            var by_order = 0;
                                        }

                                        return by_order;
                                    }),
                    message_length  : message_length,
                    notification    : (!vm.raw_data.status.can_send || message_length <= 0),
                    can_send_final  : (vm.raw_data.status.can_send && vm.raw_data.status.block_until === null)
                };
            }
        },

        /**
         * When data is updated but not the dom yet
         * @type object
         */
        chatbox_before_update: {
            /**
             * Native chatbox data updated but not dom yet
             * @param  object vm
             * @return void
             */
            native: function(vm) {

                // If there's new message only
                var cur_length = vm.raw_data.unsorted_messages.list.length; 
                if (cur_length != vm.raw_data.before_update_length) {
                    vm.raw_data.before_update_length = cur_length;

                    var chatbox_body = $(vm.$el).find('.ps_js-chat_body');
                    if (chatbox_body.length > 0) {
                        var scroll_height             = chatbox_body.prop('scrollHeight');
                        var offset_height             = chatbox_body.prop('offsetHeight');
                        var scrolled_area             = scroll_height - offset_height;
                        vm.raw_data.is_scrolled_botom = (chatbox_body.scrollTop() == scrolled_area)
                    }
                }
            }
        },

        /**
         * When dom is updated
         * @type object
         */
        chatbox_updated: {
            /**
             * Native chatbox dom updated
             * @param  object vm
             * @return void
             */
            native: function(vm) {

                // If there's new message only
                var cur_length =  vm.raw_data.unsorted_messages.list.length; 
                if (vm.raw_data.is_scrolled_botom && cur_length != vm.raw_data.updated_length) {
                    vm.raw_data.updated_length = cur_length;
                    $(vm.$el).find('.ps_js-chat_body').each(function() {
                        $(this).scrollTop(this.scrollHeight);
                    });
                }

                // seen messages on message arrived
                if (vm.raw_data.status.unread > 0 && ps_window.is_focused()) {
                    vm.chatbox_methods().seen();
                }
            }
        },

        /**
         * methods that needs vm instance
         * @type object
         */
        chatbox_methods: {
            /**
             * Native chatbox methods
             * @param  object vm
             * @return object
             */
            native: function(vm) {
                return {
                    seen    : function() {
                                var data = vm.raw_data;
                                if (data.status.unread > 0 && data.is_active && data.status.chatStatus == 'show') {
                                    ps_model.seen_message();
                                }
                            },

                    unblock : function() {

                                ps_model.retry_blocked_message();

                            },

                    update_scroll_lock : function() {

                                var data = vm.raw_data;

                                if (vm.scrollLock) {

                                    if (data.is_active) {
                                        ps_helper.body_scroll('chatbox', false, 'ps_js-chatbox_open');

                                    } else {
                                        ps_helper.body_scroll('chatbox', true);
                                    }

                                }
                            }     
                };
            }
        },

        /**
         * This will handle real time updates of ps native chatbox
         * @return void
         */
        native_ws_subscriptions: function(vm) {

            // real time updates
            if (globals.native_ws_subscribed === false) {

                globals.native_ws_subscribed = true;

                require(['ps_websocket'], function(ps_websocket) {
                    // new messages
                    ps_websocket.subscribe('chat', function(message) {
                        ps_model.add_message({
                            dateTime: message.dateTime,
                            messages: message.messages,
                            sender  : message.f,
                            send_id : message.send_id,
                            is_you  : (message.player == 'yes')
                       });
                    });

                    // changed status
                    ps_websocket.subscribe('chat_seen', function() {
                        ps_model.update_unread_messages();
                    });
                    ps_websocket.subscribe('online', function() {

                        if (vm.user.is_auth) {
                            if (vm.user.isWalkIn ) {
                                var can_send = vm.raw_data.status.chatStatus == 'show';
                                
                            } else {
                                var can_send = true;
                            }
                        }
                                    
                        ps_model.update_chat_status( { can_send : can_send, 'status' : 'online' } );

                    });
                    ps_websocket.subscribe('offline', function() {

                        if (vm.user.is_auth) {
                            if (vm.user.isWalkIn ) {
                                var can_send = false;
                                
                            } else {
                                var can_send = true;
                            }
                        }
                                    
                        ps_model.update_chat_status( { can_send : can_send, 'status' : 'offline' } );

                    });
                    ps_websocket.subscribe('show', function() {
                        ps_model.update_chat_visibility('show')
                    });
                    ps_websocket.subscribe('hide', function() {
                        ps_model.update_chat_visibility('hide')
                    });
                });
            }
        },

        /** 
         * This will get native chatbox history from backend
         * @param  object vm 
         * @return void
         */
        native_get_history: function(vm) {

            if (!vm.raw_data.is_initiated) {

                vm.raw_data.is_initiated = true;

                ps_model.chat_history({
                    success: function(store_messages) {
                                var raw_data = vm.raw_data;
                                raw_data.unsorted_messages = store_messages;


                                vm.$nextTick(function() {

                                    // after initiation, we will listen to scroll event to get next batch
                                    var delay_id        = ps_helper.uniqid();
                                    var chatbox_body    = $(vm.$el).find('.ps_js-chat_body');
                                    var lazy_loader_dom = $(vm.$el).find('.ps_js-lazy_loader');
                                    
                                    if (lazy_loader_dom.length > 0) {
                                        var adjust_scroll = lazy_loader_dom.outerHeight();
                                        var active_scroll = lazy_loader_dom.outerHeight();
                                    } else {
                                        var adjust_scroll = 30;
                                        var active_scroll = 0;
                                    }

                                    function lazy_load() {
                                        ps_helper.event_delay(function() {

                                            if (chatbox_body.scrollTop() < active_scroll) {

                                                ps_model.chat_history({
                                                    success: function() {   

                                                                if (raw_data.status.has_message) {

                                                                    if (chatbox_body.scrollTop() < active_scroll) {
                                                                        // scroll a little to give user
                                                                        // option to scroll on their own
                                                                        chatbox_body.scrollTop(
                                                                            adjust_scroll
                                                                        );
                                                                    }  

                                                                }

                                                                lazy_load();
                                                            }
                                                });
                                            }

                                        }, 600, delay_id);
                                    }

                                    chatbox_body.on('scroll', lazy_load);
                                    lazy_load();
                                    
                                });
                            }
                }); 

            }
        }
    };

    return {
        chatbox_main: function() {
            return {
                data    : function() {
                            return {
                                user           : {},
                                view_data      : {},
                                raw_data       : {},
                                is_loading     : true,
                                init_chatbox   : [],
                                is_mobile_agent: ps_helper.detect_mobile()
                            };
                        },
                props   : { multiline: { default: false }, scrollLock: { default: false } },
                computed: {
                            chat_config : function() {
                                            var vm = this;
                                            if (ps_helper.empty(vm.view_data.chat)) {
                                                return {};
                                            } else {
                                                return vm.view_data.chat;
                                            }
                                        },
                            type        :  function() {
                                            var vm = this;
                                            if (ps_helper.empty(vm.chat_config.type)) {
                                                return null;
                                            } else {
                                                return vm.chat_config.type;
                                            }
                                        },

                            chatbox_data: function() {
                                            var vm = this;
                                            if (!vm.is_loading) {
                                                var computed_method = callables.computed_chatbox_data[vm.type];
                                                if ($.isFunction(computed_method)) {
                                                    return computed_method(vm);
                                                }
                                            }

                                            return null;
                                        }
                        },
                watch   : {
                            // If 'type' value it changes app will initialize chatbox accordingly
                            type: function(new_type) {
                                    var vm = this;
                                    if (new_type!==null && !ps_helper.in_array(new_type,vm.init_chatbox)) {
                                        vm.init_chatbox.push(new_type);
                                        var init_method = callables.init[new_type];
                                        if ($.isFunction(init_method)) {
                                            init_method(vm);
                                        }
                                    }
                                }
                        },
                mounted : function() {
                            var vm = this;

                            // get the view_data of chatbox plugin
                            ps_model.view_data({
                                success: function(view_data) {
                                    
                                            vm.view_data = view_data.configs;
                                            vm.user      = view_data.user;

                                        }
                            }, ['configs','user']);
                        },
                beforeUpdate: function() {
                                var vm = this;

                                if (!vm.is_loading) {
                                    var before_update_method = callables.chatbox_before_update[vm.type];
                                    if ($.isFunction(before_update_method)) {
                                        before_update_method(vm);
                                    }
                                }
                            },
                updated : function() {
                            var vm = this;

                            if (!vm.is_loading) {
                                var updated_method = callables.chatbox_updated[vm.type];
                                if ($.isFunction(updated_method)) {
                                    updated_method(vm);
                                }
                            }
                        },
                methods: {
                            chatbox_methods: function() {
                                                var vm = this;

                                                if (!vm.is_loading) {
                                                    var chatbox_methods = callables.chatbox_methods[vm.type];
                                                    if ($.isFunction(chatbox_methods)) {
                                                        return chatbox_methods(vm);
                                                    }
                                                }
                                            }
                        }
            };
        }
    };
});
