
/**
 * Game window page
 * 
 * @author PS Team
 */
define('ps_game_window', ['ps_view','ps_model','ps_date','ps_websocket','ps_helper','jquery'], function() {
    var ps_view       = arguments[0];
    var ps_model      = arguments[1];
    var ps_date       = arguments[2];
    var ps_websocket  = arguments[3];
    var ps_helper     = arguments[4];

    var globals   = { is_page_rendered: false };
    var callables = {   

        /**
         * This will close game window or redirect it if its not a popup
         * @param  object view_data 
         * @return void
         */
        window_close: function(view_data) {
            if (ps_helper.is_popup()) {
                window.close();
            } else {
                window.location = view_data.site.base_url;
            }
        }
    };

    return {
    	activate: function(hash, hash_info) {
            // disable zooming and right click
            ps_helper.disable_window_manipulation();

            var page = $('.ps_js-page_'+hash_info.page);
            if (globals.is_page_rendered == false) {
                globals.is_page_rendered = true;

                ps_model.view_data({
                    success: function(view_data) {
                                window.resizeTo(screen.availWidth, screen.availHeight);
                        
                                // window data
                                if (view_data.game_window.hasOwnProperty('game_data')) {
                                    document.title = view_data.game_window.game_data.gameName,
                                    $(window).on('beforeunload', function() {
                                        if (view_data.game_window.result === true) {
                                            ps_model.game_window_closed(
                                                {
                                                    game_token: view_data.game_window.token,
                                                    gameID    : view_data.game_window.game_data.gameID,
                                                    cid       : view_data.user.id
                                                }
                                            );
                                        }
                                    });
                                }

                                ps_websocket.subscribe('ps_game_close', function(message) {  
                                    if(message === view_data.game_window.token) {
                                        callables.window_close(view_data);
                                    }
                                });
                                
                                ps_view.render(page, 'game_window', {
                                    replace: false,
                                    data   : { 
                                                hash_info     : hash_info, 
                                                last_game_open: null,
                                                view_data     : view_data,
                                                timestamp     : ps_date.get_current_date('mm/dd/yy HHH:iii gg')
                                            },
                                    mounted: function() {
                                                var vm = this;

                                                $(vm.$el).on('click','.ps_js-games_window_back', function() {
                                                    callables.window_close(view_data);
                                                });

                                                ps_model.update_page_rendered(vm.hash_info.page);
                                            }
                                });

                            }

                }, ['game_window','site','user']);
            }
    	},

        deactivate: function() {
            // enable zooming and right click
            ps_helper.disable_window_manipulation();
        }
    };
});