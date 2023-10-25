/**
 * This will handle PS banner display
 *
 * @author PS Team
 */
define('ps_banner', ['ps_model','ps_view','jquery','ps_helper','ps_store','ps_popup'], function() {

    var ps_model  = arguments[0];
    var ps_view   = arguments[1];
    var $         = arguments[2];
    var ps_helper = arguments[3];
    var ps_store  = arguments[4];
    var ps_popup  = arguments[5];

	globals   = { is_page_rendered: false, store: new ps_store('ps_banner') };
	callables = {
		/**
		 * 	This will get formatted banner object
		 * @return object
		 */
		format_banner: function() {
			var vm            = this;
			var banners       = {};
			var served_orders = {};

			var banner_pusher = function(product, details) {
									banners[product]       = banners[product] || [];
									served_orders[product] = served_orders[product] || [];

									if (!ps_helper.in_array(details.order,served_orders[product])) {
										served_orders[product].push(details.order);
										banners[product].push(details);
									}
								};

			if (!vm.banner.is_loading) {
				vm.banner.list.forEach(function(detail) {
					banner_pusher(detail.promo_for, detail);

					// display products banner to home
					if (detail.promo_for!='home' && vm.configs.banner.products_to_home) {
						banner_pusher('home', detail);
					}
				});
			}
			return banners;
		},

		/**
		 * This will update banner status from the store
		 * @param  string hash_info
		 * @return object
		 */
		update_banner_status: function(hash_info) {
			if (globals.store.store_exists('banner_status')) {
				var banner_status = globals.store.store_fetch('banner_status');

				if (!ps_helper.in_array(hash_info.id, banner_status.loaded)) {
					globals.store.store_list_push('banner_status', 'loaded', hash_info.id);
				}
				
				globals.store.store_update('banner_status','active',hash_info.id);

			} else {
				globals.store.store_update('banner_status',{ loaded:[hash_info.id], active: hash_info.id });
			}

			return globals.store.store_fetch('banner_status');
		},

		/**
		 * display banner texts in modal
		 * @param  object vm    
		 * @param  int    index
		 * @return void
		 */
		read_more: function(vm, index) {
			ps_popup.modal.open('banner' + index, {
                header      : vm.banner.list[index].title,
                body        : function(modal_part) {

                                ps_view.render(modal_part, 'banner_modal_body', {
                                    replace : false,
                                    data    : { item: vm.banner.list[index] }
                                });

                            },
                modal_class : 'banner'
            });
		}
	};

	return {
		/**
		 * Activate page
		 * @param  string hash     
		 * @param  object hash_info 
		 * @return void
		 */
		activate: function(hash, hash_info) {
			var banner_status = callables.update_banner_status(hash_info);

			if (!globals.is_page_rendered) {
				globals.is_page_rendered = true;
				var banner_page = $('.ps_js-page_'+hash_info.page); 
				ps_view.render(banner_page, 'banner', {
					replace: false,
					data   : { 
								banner     : { list:[], is_loading: true, status: banner_status}, 
								side_banner: { list:[], is_loading: true },
								configs    : {}
							},
					computed: {
								formatted_banner: callables.format_banner,
								banner_length   : function() {
													var vm            = this;
													var banner_length = {};

													vm.banner.status.loaded.forEach(function(promo_for) {
														var cur_banner           = vm.formatted_banner[promo_for];
														if ($.isArray(cur_banner)) {
															banner_length[promo_for] = cur_banner.length;
														} else {
															banner_length[promo_for] = 0;
														}
													});

													return banner_length;
												},

								is_banner_active: function() {
													var vm            = this;
													var active_status = {};
													vm.banner.status.loaded.forEach(function(promo_for) {
														active_status[promo_for] = promo_for == vm.banner.status.active;
													});

													return active_status;
												},

								side_banner_length: function() {
													var vm 			= this;
													var side_banner = vm.side_banner;
									   				if ($.isPlainObject(side_banner) && $.isArray(side_banner.list)) {
														return side_banner.list.length;
									   				} else {
									   					return 0;
									   				}
												},
							},
					mounted: function() {
								var vm          = this;
								var plugin_data = [];

								ps_model.view_data({
									success: function(response) {
												vm.configs = response.configs;

												// mark page as rendered
												ps_model.update_page_rendered(hash_info.page);
												
												// banner data
												if ($(vm.$el).find('.ps_js-main').length > 0) {
													ps_model.plugin({
														success: function(response) {
																	vm.banner.list   = response.list;
																	vm.banner.is_loading = false;
																}
													}, 'banner');
												}

												// side banner data
												if ($(vm.$el).find('.ps_js-side').length > 0) {
													ps_model.plugin({
														success: function(response) {
																	vm.side_banner.list       = response.list;
																	vm.side_banner.is_loading = false;
																}
													}, 'side banner');
												}

												// read more button
												$(vm.$el).on('click','.ps_js-banner_read_more',function() {
													callables.read_more(vm, $(this).attr('data-index'));
												});
											}
								}, ['configs']);
							}
				});
			}

		}
	};
});