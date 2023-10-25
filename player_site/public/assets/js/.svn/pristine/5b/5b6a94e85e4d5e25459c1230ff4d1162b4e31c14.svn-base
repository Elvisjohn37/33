/**
 * Live Togel page
 *
 * @author PS Team
 */

define('ps_live_togel', ['ps_view', 'ps_model', 'ps_store','ps_window','ps_popup'], function(){
	
	'use strict';

	var ps_view   = arguments[0];
	var ps_model  = arguments[1];
	var ps_store  = arguments[2];
	var ps_window = arguments[3];
	var ps_popup  = arguments[4];

	var globals   = { 
						is_page_rendered: false,
						store 			: new ps_store('ps_live_togel'),
						is_page_init    : false,
					};
	var callables;

	ps_window.new_instance(function(window_instance) {

		callables = { 
			
			/**
			 * Get or store data for live togel page
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
			 * get history for lobby
			 * @param  object ajax_setup 
			 * @return void            
			 */
			history: function(ajax_setup) {
				
				ps_model.view_data({
					success: function(response){
						
						ps_model.get_lobby(response.live_togel.gameID,ajax_setup);

					}
				}, ['live_togel']);
				
			},
			/**
			 * subscribe websocket of togel lobbby
			 * @return void
			 */
			live_togel_ws: function() {

				require(['ps_websocket'], function(ps_websocket) {
	                ps_websocket.subscribe('togel_lobby',   function(message) {
						callables.history();
	                });
	            });
			},
			/**
			 * instance of window and open live togel 
			 * @param  object hash_info 
			 * @return void           
			 */
			instance_new_window: function(hash_info) {

				window_instance.open('','width=' + screen.width +',height=' + screen.height);

				ps_popup.toast.open(ps_language.get('language.' + hash_info.id), {
                    title: ps_language.get('messages.opening_game'),
                    type : 'schedule',
                    id   : hash_info.productID
                }); 

                ps_model.view_data({
					success: function(response){
						ps_model.play(response.live_togel.gameID,hash_info.productID,{
							success: function(response) {

								window_instance.redirect(response.URL);

							},
							fail: function (data) {

								window_instance.close();

							},
							complete: function(data) {

                                ps_popup.toast.close(hash_info.productID);
								
							}
						});

					},
				    fail: function() {
                        window_instance.close();
                        ps_popup.toast.close(hash_info.productID);
                    }
				}, ['live_togel']);
			}
		}; 

	});

	return {
		activate: function(hash, hash_info) {
			var page_info = callables.page_info();

			if (!globals.is_page_rendered) {
				
				ps_view.render($('.ps_js-page_'+hash_info.page), 'live_togel', {
					replace  : false,
					data     : {
								hash_info  : hash_info,
								page_info  : page_info,
								is_init    : false,
								lobby      : { histories:{}, dealer: {} , is_success: false},
					},
					computed : {
						history_length: function() {
							return this.lobby.histories.length;
						},
						remaining_time: function() {
							if (this.lobby.run != null) {
								
								return this.lobby.run.remainingTime;
							}
							return null;
						}

					},
					mounted  : function() {
                        var vm = this;
                        
                        

						callables.history({
						success: function(response) {
							globals.store.store_update('info', {is_success: true });
							vm.lobby = response;

						},
						fail   : function(response) {

							vm.lobby = response;// check if right object key 

							var err_code = null;

							if ($.isPlainObject(response)) {
							    err_code = response.err_code;
							}

							globals.store.store_update('info', { 
							    is_success: false,
							    err_code  : err_code
							});

						},
						complete: function() {

							globals.store.store_update('info', {is_loading : false });

							ps_model.update_page_rendered(vm.hash_info.page);
							vm.is_init = true;
							callables.live_togel_ws();

							vm.$nextTick(function(){

								$(vm.$el).find('.ps_js-play_togel').on('click', function(){
									callables.instance_new_window(hash_info);
									// vm.lobby.status = false;

								});
							});

						}
					});

					}


				});
				globals.is_page_rendered = true;


			}
			else {

				callables.history();

			}

		}
	}
});