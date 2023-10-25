
/**
 * Sports page
 * 
 * @author PS Team
 */
define('ps_sports', ['ps_view', 'ps_model', 'ps_store', 'ps_window', 'ps_popup', 'ps_language'], function() {

    'use strict';

    var ps_view     = arguments[0];
    var ps_model    = arguments[1];
    var ps_store    = arguments[2];
    var ps_window   = arguments[3];
    var ps_popup    = arguments[4];
    var ps_language = arguments[5];

    var globals   = { 
                        store      : new ps_store('ps_sports'), 
                        game_opened: false,
                        hash_info  : []
                     };
    var callables;

    // wrap all callables into 1 windowq instance
    ps_window.new_instance(function(window_instance) {

        window_instance.on('close', function() {
            if (globals.game_opened) {
                callables.on_game_closed();
            }
        });

        callables = {
            /**
             * This will open sports window
             * @param  string productID
             * @param  string id
             * @return void
             */
            open_sports: function(productID, id) {
                if(id !== 'sports') {
                    ps_model.view_data({
                        success: function(response) {
                                    if (response.user.is_auth) {
                                        // SPORTS ASI
                                       callables.sports_after_login(response, productID, id); 

                                    } else {
                                        // SPORTS BSI
                                       callables.sports_before_login(response, productID, response[id]); 

                                    }
                                }

                    }, ['user', id]);
                }
            },

            /** 
             * This will load sports after login
             * @param  object view_data 
             * @param  string productID
             * @param  string id
             * @return void
             */
            sports_after_login: function(view_data, productID, id) {
                globals.store.store_update('info', { is_loading: true });
                ps_model.play(view_data[id].gameID, productID, {
                    success: function(response) {
                                // if window is not open then update websession
                                if (!window_instance.is_open()) {
                                    callables.on_game_closed();
                                }

                                window_instance.redirect(response.URL);
                                globals.game_opened = true;
                            },
                    fail   : function(response) {
                                window_instance.close();

                            },
                    complete: function() {
                                ps_popup.toast.close(productID);
                            }
                });
            },

            /**
             * This will load sports before login
             * @param  object view_data 
             * @param  object sports
             * @return void
             */
            sports_before_login: function(view_data, productID, sports) {
                var complete = window_instance.redirect(sports.bsi_src);
                globals.game_opened = true;
                if(complete) {
                    ps_popup.toast.close(productID);
                }
            },
            /**
             * This will activate Sports page
             * @param  string hash 
             * @param  object hash_info 
             * @return void
             */
            activate: function(hash, hash_info) {
                window_instance.open('','width=' + screen.width +',height=' + screen.height);
                ps_popup.toast.open(ps_language.get('language.' + hash_info.id), {
                    title: ps_language.get('messages.opening_game'),
                    type : 'schedule',
                    id   : hash_info.productID
                });

                globals.game_opened = false;
                globals.hash_info = hash_info
                callables.open_sports(hash_info.productID, hash_info.id);

            },

            /**
             * Triggers when game was closed
             * @return void
             */
            on_game_closed: function() {

                ps_model.view_data({
                    success: function(response) {
                                if(response.user.is_auth) {
                                    ps_model.reset_websession(response[globals.hash_info.id].gameID);
                                }
                            }
                }, [globals.hash_info.id, 'user']);

            }
        };

    });

    return {
        activate: callables.activate
    };
});

    
