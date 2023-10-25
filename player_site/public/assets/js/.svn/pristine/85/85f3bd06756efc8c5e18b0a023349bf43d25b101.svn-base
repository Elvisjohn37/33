/**
 * Promo page handler
 * 
 * @author PS Team
 */
define('ps_promo', ['ps_view', 'ps_model', 'ps_language', 'ps_popup', 'ps_store'], function() {

    'use strict';
    var ps_view     = arguments[0];
    var ps_model    = arguments[1];
    var ps_language = arguments[2];
    var ps_popup    = arguments[3];
    var ps_store    = arguments[4];

	var globals     = { is_page_rendered: false, store: new ps_store('ps_promo') };
	var callables   = {
		/**
		 * This will load our promootions
		 * @param  object vm
		 * @return void
		 */
		load: function(vm) {
           	vm.is_loading = true;

            ps_model.get_promo({
            	success: function(response) {
            				vm.promo_raw  = response;
            				vm.is_success = true; 
            			},
            	fail   : function(response) {
            				vm.promo_raw  = response;
            				vm.is_success = false;
            			},
            	error  : function(response) {
            				vm.promo_raw  = response;
            				vm.is_success = false;
            			},
            	complete: function() {
            				vm.is_loading = false;
            				if (vm.is_success) {
                                vm.$nextTick(function() {
            						callables.form_events(vm);
            					});
            				}
            			}
            });
		},

		/**
		 * Attach form events for promo filters
		 * @param  object vm
		 * @return void
		 */
		form_events: function(vm) {
			var filter_elem = $(vm.$el).find('.ps_js-promo_switch');
			vm.filter       = filter_elem.find('.ps_js-switch_value').val();

			filter_elem.off('view_components_change').on('view_components_change', function(e, value) {
		        vm.filter = value;
		    });

			var form = $(vm.$el).find('.ps_js-promo_form');
			form.off('submit').on('submit', function(e) {
		        e.preventDefault();
		        vm.search = form.find('.ps_js-search_promo').val().toLowerCase();
		    });        

			$(vm.$el).find('.ps_js-promo_back').off('click').on('click', function() {
		        form.find('.ps_js-search_promo').val(vm.last_search);
		        form.trigger('submit');
		    }); 

			$(vm.$el).find('.ps_js-promo_trigger').off('click').on('click', function() {
				callables.promo_modal($(this).attr('data-index'),vm.promo.rows[$(this).attr('data-index')]);
		    }); 
		},

		/**
		 * This will open promotion modal
		 * @param  int    index 
		 * @param  object promo_object 
		 * @return void
		 */
		promo_modal: function(index, promo_object) {
			var modal_name   = 'promo_'+index;
			var modal_info   = callables.modal_info(modal_name);
			var custom_class =  'ps_js-'+ modal_name;
			ps_popup.modal.open(modal_name, {
                modal_class: 'promo_root ' + custom_class,
				header     : promo_object.title,
				body       : function(modal_part) {
								ps_view.render(modal_part, 'promo_modal', {
                                    replace : false,
									data    : { 
												promo          : promo_object, 
												modal_info     : modal_info, 
												is_sticky_image: false,
												custom_class   : custom_class
											},
                                    computed: {
                                                has_video   :  function() {    
                                                                var vm = this;
                                                                return !ps_helper.empty(vm.promo.videoSource);
                                                            },
                                                is_loaded   :  function() {
                                                                var vm          = this;
                                                                var is_shown    =  vm.modal_info.is_shown;
                                                                var is_rendered =  vm.modal_info.is_rendered;
                                                                var is_mounted  =  vm.modal_info.is_mounted;
                                                                return (is_shown && is_rendered && is_mounted);
                                                            },
                                                load_video   : function() {
                                                                var vm = this;
                                                                return (vm.has_video && vm.is_loaded);
                                                			},
                                                is_video_stop: function() {
                                                                var vm = this;
                                                                return (vm.modal_info.is_hide && vm.has_video);
                                                            }
                                    		},
                                    watch   : {
                                                is_video_stop: function(is_video_stop) {
                                                                var vm    = this;
                                                                var video = $(vm.$el).find('.ps_js-media_youtube');
                                                                
                                                                if (is_video_stop) {
                                                                    video.trigger('media_stop');
                                                                }
                                                            }
                                            }, 
                                    mounted : function() {
                                                var vm = this;
                                                globals.store.store_update(modal_name, { is_mounted: true });
                                            }
								});
							},
                bind       :  {
                                shown:  function() {
                                            globals.store.store_update(modal_name, {
                                                is_shown: true,
                                                is_hide : false,
                                            });
                                        },
                                hide  :  function() {
                                            globals.store.store_update(modal_name, { is_hide : true });
                                        }
                            },
                onrender   : function () {
                                globals.store.store_update(modal_name, { is_rendered: true });
                            }
			});
		},

		/**
		 * This will get store of specific promo modal
		 * @param  string store_key
		 * @return object
		 */
		modal_info: function(store_key) {
			if (!globals.store.store_exists(store_key)) {
                globals.store.store_update(store_key, {
                    is_shown   : false,
                    is_rendered: false,
                    is_mounted : false,
                    is_hide    : false
                });
            }
            return globals.store.store_fetch(store_key);
		}
	};

	return {
		activate: function(hash, hash_info) {
			var page = $('.ps_js-page_'+hash_info.page);

			if (globals.is_page_rendered == false) {
                globals.is_page_rendered = true;

                ps_view.render(page, 'promo', {
                    replace: false,
                    data   : { 
                    			hash_info   : hash_info, 
                                filter      : null,
                                search      : '',
                                last_search : '',
                    			is_loading  : true,
                    			is_success  : false,
                    			err_code    : null,
                    			promo_raw   : { rows:[], total:0 },
                    		},
                    computed: {
	                            promo       : function() {
	                                            var vm = this;
	                                            if ($.isPlainObject(vm.promo_raw) && $.isArray(vm.promo_raw.rows)) {
	                                                return vm.promo_raw;
	                                            } else {
	                                                return { 
	                                                    rows      :[], 
	                                                    is_success: false,  
	                                                    err_code  : ps_language.net_err_code 
	                                                };
	                                            }
	                                        },
	                            is_searched : function() {
	                                            var vm          = this;
	                                            var is_searched = [];

	                                            vm.promo.rows.forEach(function(value, index) {
	                                                if (ps_helper.empty(vm.search)) {
	                                                    is_searched[index] = true;
	                                                } else {
	                                                    is_searched[index] =  ps_helper.is_contain(
	                                                                            vm.search, 
	                                                                            value.title.toLowerCase()
	                                                                        );
	                                                }
	                                            });
	                                            return is_searched;
	                                        },
                            	is_filtered : function() {
	                                            var vm          = this;
	                                            var is_filtered = [];

	                                            vm.promo.rows.forEach(function(value, index) {
	                                                if (vm.filter == 'new') {
	                                                    is_filtered[index] = (value.derived_isNew == 1);
	                                                } else {
	                                                    is_filtered[index] = true;
	                                                }
	                                            });

	                                            return is_filtered;
	                                        },
	                            total        : function() {
	                                            var vm = this;

	                                            if (vm.is_loading === false) {

	                                                var final_total = 0;

	                                                vm.promo.rows.forEach(function(value, index) {
	                                                    if (vm.is_searched[index]) {
	                                                        final_total++;
	                                                    }
	                                                });

	                                                return final_total;

	                                            } else {

	                                                return null;

	                                            }
	                                        },


	                            is_display_result: function() {
	                                                return this.total !== null;
	                                            },
                    		},

                	watch   : {
	                            search: function() {
	                                        var  vm = this;
	                                        if (vm.total > 0) {
	                                            vm.last_search = vm.search;
	                                        }
	                                    }
	                        },
                    mounted : function() {
                                var vm = this;
                                ps_model.update_page_rendered(vm.hash_info.page);

                                // load for the first time
                                callables.load(vm);

                                // add reload as dom event
	                            $(vm.$el).on('promo_template_reload', function() {
	                                if (vm.is_loading == false && vm.is_success == false) {
	                                    callables.load(vm);
	                                } else {
	                                    $(vm.$el).find('.ps_js-promo_form').trigger('view_components_fullreset');
	                                    vm.search = '';
	                                }
	                            });
                            }
                });

            } else {

            	page.find('.ps_js-promo').trigger('promo_template_reload');

            }

		}
	};
});