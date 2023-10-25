
/**
 * This will handle ps support plugin
 * 
 * @author PS Team
 */
define('ps_support', ['ps_model', 'ps_helper', 'ps_store', 'ps_websocket', 'jquery', 'ps_localstorage'], function () {

    var ps_model        = arguments[0];
    var ps_helper       = arguments[1];
    var ps_store        = arguments[2];
    var ps_websocket    = arguments[3];
    var $               = arguments[4];
    var ps_localstorage = arguments[5];

    var globals   = { 
                        debug    : false, 
                        store    : new ps_store('ps_support', {
                                    bank: { is_show: ps_localstorage.get('support_bank') },
                                    chat: { is_show: ps_localstorage.get('support_chat') }
                                }),
                        storage_events: { bank: false, chat: false }
                    };

    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will render support template base on its type
         * @param  string   type    
         * @return void
         */
        render_support: function(type) {
            ps_localstorage.watch('support_' + type, function(value) {
                globals.store.store_update(type, 'is_show', value);
            });

            if ($.isFunction(callables[type+'_ws_subscription'])) {
                callables[type+'_ws_subscription']();
            }

            return {
                data    : function() {
                            return {
                                type             : type,
                                support          : { list:[] },
                                store            : globals.store.store_fetch(type),
                                is_loading       : true,
                                activate_animate : false,
                                is_clickable     : true
                            };
                        },
                computed: {
                            support_length  : function() {
                                                var vm = this;
                                                
                                                if ($.isPlainObject(vm.support) && $.isArray(vm.support.list)) {
                                                    return vm.support.list.length;
                                                } else {
                                                    return 0;
                                                }
                                            },
                            show            : function() {
                                                return (this.is_loading == false && this.support_length > 0);
                                            },
                            no_item         : function() {
                                                return (this.is_loading == false && this.support_length  <= 0);
                                            },
                            bank_group      : function() {
                                                var banks = [];
                                                var col   = 4;
                                                var bank  = 0;
                                                for(var i = 0; i < this.support_length / 4; i++) {
                                                    var temp = [];
                                                    for(bank; bank < col; bank++) {
                                                        if(this.support.list[bank] === undefined) break;
                                                        temp.push(this.support.list[bank]);
                                                    }
                                                    col += 4;
                                                    banks.push(temp);
                                                }
                                                return banks;
                                            }
                        },
                mounted : function() {
                            var vm = this;

                            ps_model.plugin({
                                success: function(response) { 
                                            vm.support    = response;
                                            vm.is_loading = false;
                                        }

                            }, type + ' operational');


                            $(vm.$el).find('.ps_js-support_toggle').on('click', function() {
                                if(vm.is_clickable) {
                                    vm.is_clickable = false;
                                    vm.activate_animate = true;
                                    setTimeout(function() {
                                        vm.activate_animate = false;
                                        vm.is_clickable = true;
                                    }, 2000);
                                    //vm.animating = false;
                                    callables.toggle_is_show(type);
                                }
                            });
                        }
            };  
        },

        /**
         * Websocket subscriptions for chat operational
         * @return void
         */
        chat_ws_subscription: function() {
            require(['ps_websocket'], function(ps_websocket) {
                ps_websocket.subscribe('chat_app', function(message) {
                    ps_model.update_chat_operational(message);
                });
            });
        },

        /**
         * This will toggle support is_show status
         * @param  string type
         * @return void
         */
        toggle_is_show: function(type) {
            var support_store = globals.store.store_fetch(type);
            var toggle_to     = support_store.is_show ? false : true;
            globals.store.store_update(type, 'is_show', toggle_to);
            ps_localstorage.set('support_' + type, toggle_to);
        }
    };

    return {
        /**
         * support bank
         * @return void
         */
        support_bank: function() {
            return callables.render_support('bank');
        },


        /**
         * support chat
         * @return void
         */
        support_chat: function() {
            return callables.render_support('chat');
        }
    }
});
