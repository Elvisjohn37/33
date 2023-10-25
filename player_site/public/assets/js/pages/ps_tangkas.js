
/**
 * Sports page
 * 
 * @author PS Team
 */
define('ps_tangkas', ['ps_view', 'ps_model', 'ps_store'], function(ps_view, ps_model, ps_store) {

    'use strict';

    var globals   = { is_page_renderend: false, store: new ps_store('ps_tangkas') };
    var callables = {
        /**
         * Get/Create page store
         * @return object
         */
        page_info: function() {
            if (!globals.store.store_exists('info')) {
                globals.store.store_update('info', {
                    is_loading  : true,
                    src         : null,
                    is_success  : false,
                    err_code    : null,
                    dcode       : null,
                    lobby_height: null
                });
            }

            return globals.store.store_fetch('info');
        },

        /**
         * Open tangkas lobby
         * @param  string productID
         * @return object
         */
        open_tangkas: function(productID) {
            
            globals.store.store_update('info', { 
                is_loading: true,
                err_code  : null,
                dcode     : null 
            });

            ps_model.view_data({
                success: function(response) {

                            ps_model.play(response.tangkas.gameID, productID, {
                                success: function(response) {
                                            globals.store.store_update('info', { 
                                                src       : response.URL,
                                                is_success: true,
                                                err_code  : null
                                            });
                                        },
                                fail   : function(response) {
                                            var err_code = null;
                                            var dcode    = null;

                                            if ($.isPlainObject(response)) {
                                                err_code = response.err_code;
                                                dcode    = response.dcode;

                                                switch (response.dcode) {
                                                    case 'DNR': callables.open_display_name(); break;
                                                }
                                            }

                                            globals.store.store_update('info', { 
                                                is_success: false,
                                                err_code  : err_code,
                                                dcode     : dcode
                                            });

                                        },
                                complete: function() {
                                            globals.store.store_update('info', { is_loading: false });
                                        }
                            });

                        }
            }, ['tangkas']);

        },

        /**
         * Subscribe all tangkas WS
         * @return void
         */
        tangkas_ws: function() {
            require(['ps_websocket'], function(ps_websocket) {
                ps_websocket.subscribe('resize_tangkas_lobby',   function(message) {
                    globals.store.store_update('info', { lobby_height: message + 'px' });
                });
            });
        },

        /**
         * This will open display name modal
         * @return void
         */
        open_display_name: function() {
            require(['ps_displayname'], function(ps_displayname) {

                ps_displayname.open(function() {
                    callables.open_tangkas();
                });

            });
        }
    };

    return {
        /**
         * Activate sports page
         * @param  string hash    
         * @param  object hash_info 
         * @return void
         */
        activate: function(hash, hash_info) {
            var page_info = callables.page_info();

            if (!globals.is_page_rendered) {

                globals.is_page_rendered = true;

                callables.tangkas_ws();

                ps_view.render($('.ps_js-page_'+hash_info.page), 'tangkas', {
                    replace: false, 
                    data   : {
                                hash_info : hash_info,
                                page_info : page_info
                            },
                    mounted: function() {
                                var vm = this;
                                ps_model.update_page_rendered(vm.hash_info.page);
                                callables.open_tangkas(vm.hash_info.productID);

                                $(vm.$el).on('click', '.ps_js-set_display_name', callables.open_display_name);
                            }
                });

            } else {
                callables.open_tangkas(hash_info.productID);
            }
        }

    };
});
