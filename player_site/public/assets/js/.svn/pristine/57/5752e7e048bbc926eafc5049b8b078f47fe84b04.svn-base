/**
 * Announcement page
 *
 * @author PS Team
 */


define('ps_announcement', ['jquery','ps_store','ps_view','ps_model','ps_helper'], function(){

	'use strict';
	
	var $         = arguments[0]; 
	var ps_store  = arguments[1];
	var ps_view   = arguments[2];
	var ps_model  = arguments[3];
	var ps_helper = arguments[4];

	var globals   = {
		is_page_rendered: false,
		store : new ps_store('ps_announcement')
	};
	var callables = {

		/**
		 * Get storage for announcement page
		 * @return object
		 */
		announcement_page_info: function() {
			if (!globals.store.store_exists('info')) {
				globals.store.store_update('info', {
					announcement_loading   : true,    
					announcement_page      : null
				});

			return globals.store.store_fetch('info');

			}
		},

		/**
		 * Load the data needed for announcement page
		 * @return  void
		 */
		load_now: function() {
			var vm = this;
			ps_model.view_data({
				success: function() {
							
						callables.announcement.activate(
							function(response) {
								vm.announcement = response;
							}
						);

						// mark page as rendered
						ps_model.update_page_rendered(vm.hash_info.page);
						vm.is_init = true;
					}
			});
		},

		/**
         |--------------------------------------------------------------------------------------------------------------
         | ANNOUNCEMENT SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		announcement: {
			/**
			 *  Get data and update announcement object 
			 * @return void
			 */
			activate: function(callback) {

				var vm = this;
				globals.store.store_update('info',{ announcement_loading : true});

				ps_model.get_announcement({
					success: callback,
					complete: function() {
						globals.store.store_update('info',{ announcement_loading: false });
					}
				});
			}

		}

	};

	return {

		/**
		 * activate annoncemnet page
		 * @param  string hash      
		 * @param  object hash_info 
		 * @return object           
		 */
		activate: function(hash, hash_info){
			var page_info = callables.announcement_page_info();

			if (!globals.is_page_rendered) {

				globals.is_page_rendered = true;
				var announcement_page    = $('.ps_js-page_'+hash_info.page); 

				ps_view.render(announcement_page, 'announcement',{
					replace  : false,
					data     : {
									hash_info    : hash_info,
									page_info    : page_info,
									announcement : { rows:[], total:0 },
									is_init      : false
					},
					computed : {
						announcement_length:  function() {

							return this.announcement.rows.length;
						}

					},
					mounted: callables.load_now
				});

			} else {
				callables.announcement.activate();
			}


		},
	};
});