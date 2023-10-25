
/**
 * Live casino page
 * 
 * @author PS Team
 */
define('ps_live_casino', ['ps_window','ps_model','ps_popup','ps_language', 'ps_view', 'ps_store', 'ps_helper'], function () {

    'use strict';

    var ps_window   = arguments[0];
    var ps_model    = arguments[1];
    var ps_popup    = arguments[2];
    var ps_language = arguments[3];
    var ps_view     = arguments[4];
    var ps_store    = arguments[5];
    var ps_helper   = arguments[6];

    var globals   = { 
                        is_page_rendered: false,
                        store           : new ps_store('ps_live_casino'),
                        is_page_init    : false,
                    };
    var callables;

    callables = {

        /**
         * Get or store data for live casino page
         * @return object 
         */
        page_info: function() {
            if (!globals.store.store_exists('info')) {
                globals.store.store_update('info',{
                    is_loading: true
                });
            }
            return globals.store.store_fetch('info');
        },
        /**
         * Triggers when game was closed
         * @return void
         */
        on_game_closed: function() {

            ps_model.view_data({
                success: function(response) {
                            ps_model.reset_websession(response.live_casino.gameID);
                        }
            }, ['live_casino']);

        },

        events       : function(vm) {
            $(function() {
                $(vm.$el).find('.ps_js-play_button').off('click').on('click', function() {
                    callables.open_casino(vm.hash_info, $(this).attr('index-item'), $(this).attr('index-version'), vm);
                });
            });
            
        },
        /**
         * This will open casino window
         * @return void
         */
        open_casino  : function(hash_info, index_item, index_version, vm) {
            // wrap all callables into 1 windowq instance
            ps_window.new_instance(function(window_instance) {

                window_instance.on('close', function() {
                    if (globals.game_opened) {
                        callables.on_game_closed();
                    }
                });

                window_instance.open('','width=' + screen.width +',height=' + screen.height);

                ps_popup.toast.open(ps_language.get('language.' + hash_info.id), {
                    title: ps_language.get('messages.opening_game'),
                    type : 'schedule',
                    id   : hash_info.productID
                });

                globals.game_opened = false;

                ps_model.view_data({

                    success: function(response) {
                                ps_model.play(response.live_casino.gameID, hash_info.productID, {
                                    success: function(response) {
                                                // if window is not open then update websession
                                                if (!window_instance.is_open()) {
                                                    callables.on_game_closed();
                                                }

                                                var url = response.URL;
                                                var engine = vm.live_casinos[index_item].engines[index_version];
                                                // if(engine.version === "html5") {
                                                    var params = engine.params;

                                                    params.forEach(function(param) {
                                                        url = ps_helper.add_url_param(url, param);
                                                    });
                                                // }
                                                window_instance.redirect(url);
                                                globals.game_opened = true;
                                            },

                                    fail   : function(response) {
                                                window_instance.close();
                                            },

                                    complete: function() {
                                                ps_popup.toast.close(hash_info.productID);
                                            }
                                });
                            },

                    fail   : function() {
                                window_instance.close();
                                ps_popup.toast.close(hash_info.productID);
                            }

                }, ['live_casino']);
            });
        }
    };

    

    return {

        activate: function(hash, hash_info) {
            var page_info = callables.page_info();

            if (!globals.is_page_rendered) {
                globals.is_page_rendered = true;
                ps_view.render($('.ps_js-page_'+hash_info.page), 'live_casino', {
                    replace: false, 
                    data   : { 
                                hash_info    : hash_info,
                                page_info    : page_info,
                                live_casinos : [{
                                                    description : 'Live Casino',
                                                    engines     : [
                                                                    { version : 'html5', params : ['isHtml5=Y'] },
                                                                    { version : 'flash', params : ['isHtml5=N'] } 
                                                                ]
                                                }]
                             },
                    mounted: function() {
                                var vm = this;
                                callables.events(vm);
                                ps_model.update_page_rendered(vm.hash_info.page);
                                globals.store.store_update('info', {is_loading : false });
                            }
                });
            }

        }
    };
});
