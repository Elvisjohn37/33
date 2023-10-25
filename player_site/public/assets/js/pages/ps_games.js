
/**
 * Games page
 * 
 * @author PS Team
 */
define('ps_games', ['ps_view','ps_model','ps_window', 'ps_language'], function() {

    var ps_view     = arguments[0];
    var ps_model    = arguments[1];
    var ps_window   = arguments[2];
    var ps_language = arguments[3];

    var globals   = { is_page_rendered: false };
    var callables = {   
        /**
         * This will bind events for games 
         * @param  object vm 
         * @return void
         */
        events: function(vm) {
            // game window instance
            ps_window.new_instance(function(window_instance) {

                var games_display = $(vm.$el).find('.ps_js-games_display');

                // play button clicked
                games_display.on('games_template_clicked', function(e, gameID) {
                    vm.last_game_open = gameID;
                    window_instance.open('', 'width=800, height=717');
                });

                // play button success
                games_display.on('games_template_success', function(e, gameID, response) {
                    vm.last_game_open = gameID;
                    if (gameID === vm.last_game_open) {
                        window_instance.redirect(response.URL);
                    }
                });

                // play button fail
                games_display.on('games_template_error', function(e, gameID, response) {
                    if (gameID === vm.last_game_open) {
                        window_instance.close();
                    }
                });

                // on click of play button
                games_display.on('games_template_play', function(e, gameID) {
                    ps_model.play(gameID, vm.hash_info.productID, {
                        success: function(response) {
                                    if (gameID === vm.last_game_open) {
                                        window_instance.redirect(response.URL);
                                    }
                                },
                        fail   : function(response) {
                                    if (gameID === vm.last_game_open) {
                                        window_instance.close();
                                        games_display.trigger('games_template_error', gameID, response);
                                    }
                                }
                    });
                });
            });
        },
    };

    return {
    	activate: function(hash, hash_info) {

            var page = $('.ps_js-page_'+hash_info.page);

            if (globals.is_page_rendered == false) {

                globals.is_page_rendered = true;

                ps_view.render(page, 'games', {
                    replace: false,
                    data   : { hash_info: hash_info, last_game_open: null },
                    mounted: function() {
                                var vm = this;
                                ps_model.update_page_rendered(vm.hash_info.page);
                                callables.events(vm);
                            }
                });

            } else {

                page.find('.ps_js-games_display').trigger('games_template_reload');

            }

    	}
    };
});