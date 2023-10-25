
/**
 * Skill Games page
 * 
 * @author PS Team
 */
define('ps_skill_games', ['ps_view','ps_model','ps_language','ps_store'], function() {

    var ps_view     = arguments[0];
    var ps_model    = arguments[1];
    var ps_language = arguments[2];
    var ps_store    = arguments[3];

    var globals   = { is_page_rendered: false, store: new ps_store('ps_skill_games') };
    var callables = {   
        /**
         * Get/Create skill games page store
         * @return object
         */
        page_info: function() {
            if (!globals.store.store_exists('info')) {
                globals.store.store_update('info', {
                   is_lobby: false
                });
            }

            return globals.store.store_fetch('info');
        },

        /**
         * This will bind events for games 
         * @param  object vm 
         * @return void
         */
        events: function(vm) {
            var games_display = $(vm.$el).find('.ps_js-skill_games_display');

            // play button clicked
            games_display.on('games_template_clicked', function(e, gameID) {
                vm.last_game_open   = gameID;
                vm.is_lobby_loading = true;
                globals.store.store_update('info', {
                   is_lobby: true
                });
            });

            // play button success
            games_display.on('games_template_success', function(e, gameID, response) {
                if (gameID === vm.last_game_open) {
                    vm.is_lobby_loading = false;
                    vm.response         = response;
                    vm.$nextTick(function() {
                        $(vm.$el).find('.ps_js-skill_games_form').trigger('submit');
                    });
                }
            });

            // play button fail
            games_display.on('games_template_error', function(e, gameID, response) {
                if (gameID === vm.last_game_open) {
                    globals.store.store_update('info', {
                       is_lobby: false
                    });
                }
            });

            // back button
            $(vm.$el).find('.ps_js-skill_games_back').on('click', function() {
                globals.store.store_update('info', {
                   is_lobby: false
                });
            });
        },
    };

    return {
        activate: function(hash, hash_info) {
            var page_info = callables.page_info();
            var page      = $('.ps_js-page_'+hash_info.page);

            if (globals.is_page_rendered == false) {

                globals.is_page_rendered = true;
                ps_view.render(page, 'skill_games', {
                    replace: false,
                    data   : { 
                                hash_info       : hash_info, 
                                last_game_open  : null, 
                                page_info       : page_info,
                                response        : { URL:'', token:'', lang:'' },
                                is_lobby_loading: true
                            },
                    mounted: function() {
                                var vm = this;
                                ps_model.update_page_rendered(vm.hash_info.page);
                                callables.events(vm);
                            }
                });

            } else {

                page.find('.ps_js-skill_games_display').trigger('games_template_reload');
                globals.store.store_update('info', {
                   is_lobby: false
                });
            }
        },

        deactivate: function() {
            
        }
    };
});