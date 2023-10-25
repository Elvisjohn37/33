/**
 * (Deffered) This will handle all types of notification
 * Available: Modal, Toast
 *
 * @author PS Team
 */
define('ps_popup', ['ps_view', 'ps_helper', 'ps_store', 'ps_language', 'jquery', 'bootstrap','ps_model'], function() {

	'use strict';

    var ps_view     = arguments[0];
    var ps_helper   = arguments[1];
    var ps_store    = arguments[2];
    var ps_language = arguments[3];
    var $           = arguments[4];
    var bootstrap   = arguments[5];
    var ps_model    = arguments[6];

	var globals   = { 
						store: new ps_store('ps_popup'),
						toast: { timeout: 10000, default_id: 'default'},
						modal: { 
								onrender        : {},
								defaults        : {
													keyboard     : true,
													backdrop     : true,
													close_button : true,
													modal_class  : ''
												},
								prev_focused    : null,
								default_category: 'default',
								tab_captured    : false
							}
					};

	var callables = {
		/**
         |-------------------------------------------------------------------------------------------------------------- 
         | Toast methods
         |-------------------------------------------------------------------------------------------------------------- 
         */
		toast: {

			/**
			 * This will open a toast
			 * @param  string content Toast content
			 * @param  object options [
			 *                        	title(Default) = ucwords of 'type' attribute,
			 *                        	type (Default) ='alert', 
			 *                        	id             = If not emoty this wil create another toast instance,
			 *                        	auto           = If toast will auto close, default is false
			 *                        ]
			 * @return void
			 */
			open: function(content, options) {
				options = options || {};
				
				// set default and presets
				options.id        = ps_helper.set_default(options.id,   globals.toast.default_id);
				options.key       = ps_helper.uniqid();
				options.type      = ps_helper.set_default(options.type, 'alert');
				options.title     = ps_helper.set_default(options.title, ps_language.get('language.error'));
				options.auto      = ps_helper.set_default(options.auto, false);
				options.content   = content;
				options.is_active = true;

				
				if (!globals.store.store_exists('toast')) {

					// create toast
					globals.store.store_update('toast', {
						list_ids    : [options.id],
						list        : [options]
					});

					callables.toast.render();

				} else {

					// update only
					var toast_store = globals.store.store_fetch('toast');

					if (ps_helper.in_array(options.id, toast_store.list_ids)) {

						globals.store.store_list_delete('toast', function(value) {
							return value.id === options.id;
						});

					} else {

						globals.store.store_list_push('toast', 'list_ids', options.id);

					}

					globals.store.store_list_unshift('toast', options);

				}

				// auto close
				if (options.auto) {
					callables.toast.auto_close(options.id)
				}
			},

			/**
			 * close button click event
			 * unlike normal close this will force the modal to closed even if its still focused and in auto mode
			 * @return void
			 */
			close_button: function(e) {
				var target = $(this).data('target');
				globals.store.store_list_update('toast', function(value) {
					return value.id === target;
				}, { is_active: false, auto: false });
			},

			/**
			 * This will bind dom events to newly created toasts
			 * @return void
			 */
			events: function() {
				var vm = this;
				
				// DOM events
				$(vm.$el).find('.ps_js-toast_close')
						.off('click', callables.toast.close_button)
						.on( 'click', callables.toast.close_button);

				// DOM events
				$(vm.$el).find('.ps_js-toast_item')
						.off(ps_helper.animation_end_events(), callables.toast.animation_end)
						.on( ps_helper.animation_end_events(), callables.toast.animation_end);
			},

			/**
			 * Toast rendering
			 * @return void
			 */
			render: function() {
				ps_view.render($('body'), 'toast', { 
					data   : { 
								toast         : globals.store.store_fetch('toast'),
								max_visibility: false
							},
					computed: {
								is_active: function() {
									var vm = this;

									var list_length = vm.toast.list.length;
									for (var index=0; index <list_length; index++) {
										var toast_item = vm.toast.list[index];

										if (toast_item.is_active === true) {
											return true;
										}
									}

									return false;	
								}
							},
					mounted : callables.toast.events, 
					updated : callables.toast.events
				});
			},

			/**
			 * This will close the toast
			 * @return void
			 */
			close: function(id) {
				if (ps_helper.empty(id)) {
					id = globals.toast.default_id;
				} 

				if (globals.store.store_exists('toast')) {
					globals.store.store_list_update('toast', function(value) {
						return value.id === id;
					}, { is_active: false });
				}
			},

			/**
			 * This will close the toast within a period of time if it set to auto
			 * @param  string    id 
			 * @return function
			 */
			auto_close: function(id) {
				setTimeout(function() { 
					var toast_id_value = globals.store.store_list_fetch('toast', function(value) {
											return value.id === id;
										});

					toast_id_value.forEach(function(toast){
						if (toast.auto) {
							callables.toast.close(toast.id); 
						}
					});

				}, globals.toast.timeout);
			},

			/**
			 * Execute after animation end of a toast item
			 * @return void
			 */
			animation_end: function() {
				if (!$(this).hasClass('ps_js-toast_active')) {
					$(this).addClass('ps_js-toast_out')
				}
			}

		},

		/**
         |-------------------------------------------------------------------------------------------------------------- 
         | Modal methods
         |-------------------------------------------------------------------------------------------------------------- 
         */
		modal: {
			/**
			 * This will open our modal
			 * Unlike toast modal depends ost on bootstrap events instead of ps_view data
			 * @param  string id 
			 * @param  object options  { onrender: function after modal content dom is rendered }
			 * @param  string category default='default', assign another category to create another independent modal 
			 * @return void
			 */
			open: function(id, options, category) {
				category            = category || globals.modal.default_category;
				var modal_store_key = callables.modal.store_key(category);
				var modal_store 	= globals.store.store_fetch(modal_store_key);

				// modal store is existing
				if (modal_store !== false) {
					var previous_active = modal_store.list_active;

					if (modal_store.category_is_active) {
						var modal_element = $('#ps_js-'+modal_store_key);

						if (previous_active != id) {
							var original_context   = this;
							var original_arguments = arguments; 

							modal_element.one('hidden.bs.modal', function() {
								callables.modal.open.apply(original_context,original_arguments);
							});

							modal_element.modal('hide');
						}

						return false;
					}
				}

				// render modal first before doing the rest
				callables.modal.render(category, function() {
					
					// refetch modal store
					if (modal_store == false) {
						modal_store = globals.store.store_fetch(modal_store_key);
					}

					// process current options
					options = options || {};

					if (options.closable === false) {
						options.keyboard     = false;
						options.backdrop     = 'static';
						options.close_button = false; 
					} 

					options 			  = $.extend({}, globals.modal.defaults, options);
					var is_content_recent = (!ps_helper.in_array(id, modal_store.list_ids));
					var new_modal_element = $('#ps_js-'+modal_store_key);

					// activate new content
					globals.store.store_update(modal_store_key, { 
						list_active     : id
					});

					// prepare onrender callback
					if (is_content_recent) {
						// the very first render callback will handle the creation of content parts
						var original_onrender = options.onrender;
						options.onrender      = function (content_dom, is_content_recent) {
													callables.modal.render_parts(content_dom, options);
													if ($.isFunction(original_onrender)) {
														original_onrender(content_dom, is_content_recent);
													}
												};

						if ($.isPlainObject(options.bind)) {
							$.each(options.bind, function(bind_event, handler) {
								if ($.isFunction(handler)) {
									new_modal_element.on(bind_event+'.bs.modal', function() {
										if (modal_store.list_active === id) {
											handler.apply(this, arguments);
										}
									});
								}
							});
						}
					}

					// upate modal settings and show
					var modal_options = new_modal_element.data('bs.modal');

					if (ps_helper.empty(modal_options)) {

						new_modal_element.modal({
							keyboard    : options.keyboard,
							backdrop    : options.backdrop
						});

					} else {

						ps_helper.assoc_merge(modal_options.options, {
							keyboard    : options.keyboard,
							backdrop    : options.backdrop
						});

						new_modal_element.modal('show');
					}

					// on render callback
					if ($.isFunction(options.onrender)) {
						var content_dom = callables.modal.get_id_content(id, category);

						// if element is not yet present add to onrender queue and execute on view update
						if (content_dom.length <= 0) {

							if (!$.isPlainObject(globals.modal.onrender[category])) {
								globals.modal.onrender[category] = {};
							}

							if (!$.isArray(globals.modal.onrender[category][id])) {
								globals.modal.onrender[category][id] = [];
							}

							globals.modal.onrender[category][id].push(options.onrender);

						} else {

							options.onrender(content_dom, false);

						}
					}

					// add new modal to store
					if (is_content_recent) {
						globals.store.store_update(modal_store_key, 'is_loading', true);
						var update_list = globals.store.store_list_push(modal_store_key, { 
											id        		 : id,
											has_header		 : options.hasOwnProperty('header'), 
											has_body  		 : options.hasOwnProperty('body'), 
											has_footer       : options.hasOwnProperty('footer'),
											is_sticky_header : options.sticky_header,
										});

						globals.store.store_list_push(modal_store_key, 'list_ids', id);

					} 

					// activate new content
					globals.store.store_update(modal_store_key, {
						close_button: options.close_button,
						modal_class : options.modal_class
					});
					
				});
			},

			/**
			 * This will create modal store key
			 * @param  string category 
			 * @return string
			 */
			store_key: function(category) {
				if (ps_helper.empty(category)) {
					category = globals.modal.default_category;
				}

				return category + '_modal';
			},

			/**
			 * This will render each parts of the modal
			 * @param  function content_dom 
			 * @param  object   options        
			 * @return void
			 */
			render_parts: function(content_dom, options) {
				// make sure dom is empty first
				var modal_parts = content_dom.find('.ps_js-modal_part');
				modal_parts.empty();

				modal_parts.each(function() {
					var part      = $(this);
					var part_type = part.data('part');

					if (options.hasOwnProperty(part_type)) {

						var part_option = options[part_type];

						if (ps_helper.is_dom(part_option)) {

							part.append(part_option);

						} else if ($.isFunction(part_option)) {

							part_option(part);

						} else {

							if (!ps_helper.empty(part_option)) {
								part.text(part_option);
							}

						}
					}
				});
			},

			/**
			 * This will get the dom of given id in modal
			 * @param   string id
			 * @param   string category
			 * @return  dom
			 */
			get_id_content: function(id, category) {
				var modal_store_key = callables.modal.store_key(category);
				return $('#ps_js-'+modal_store_key+' .ps_js-modal_'+id);
			},

			/**
			 * This will check if modal with id exists
			 * @param  string   id
			 * @param  string   category
			 * @return boolean
			 */
			exists: function(id, category) {
				var modal_store_key = callables.modal.store_key(category);
				if (globals.store.store_exists(modal_store_key)) {
					return ps_helper.in_array(id, globals.store.store_fetch(modal_store_key).list_ids);
				} else {
					return false;
				}
			},

			/**
			 * This will check if modal is currently active
			 * @param  string   id
			 * @param  string   category
			 * @return boolean
			 */
			is_active: function(id, category) {
				var modal_store_key = callables.modal.store_key(category);
				if (globals.store.store_exists(modal_store_key)) {
					var modal_store = globals.store.store_fetch(modal_store_key);
					return (modal_store.category_is_active && modal_store.list_active == id);
				} else {
					return false;
				}
			},

			/**
			 * Close modal
			 * @param  string id   if not empty the modal will only close if the current active ID is equal
			 * @param  string   category
			 * @param  function callback
			 * @return void
			 */
			close: function(id, category, callback) {
				var modal_store_key = callables.modal.store_key(category);
				var modal_store     = globals.store.store_fetch(modal_store_key);
				var modal_element   = $('#ps_js-'+modal_store_key);
				var is_active       = modal_store.category_is_active;

				if ((ps_helper.empty(id) || id === modal_store.list_active || id==='all') && is_active) {
					if ($.isFunction(callback)) {
						modal_element.one('hidden.bs.modal', function() {
							callback.call(this);
						});
					}

					modal_element.modal('hide');

				} else {

					if ($.isFunction(callback)) {
						callback.call(modal_element);
					}

				}
			},

			/**
			 * This will create or get existing modal global store
			 * @return void
			 */
			global_store: function() {
				if (!globals.store.store_exists('modal_global')) {
					globals.store.store_update('modal_global', {
						active_categories:[]
					});
				}
				return globals.store.store_fetch('modal_global');
			},

			/**
			 * This will render modal if not yet rendered else execute callback
			 * @param  string   category 
			 * @param  function callback 
			 * @return void
			 */
			render: function(category, callback) {
				var modal_store_key = callables.modal.store_key(category);
				// first time modal setup
				if (!globals.store.store_exists(modal_store_key)) {

					var initial_options = $.extend({}, globals.modal.defaults, {
											category_is_active: true,
											is_loading        : false,
											list_active       : null,
											list_ids          : [],
											list       		  : [],
											list_dom_length   : 0,
											is_shown          : false,
											stick_header      : false,
											back_top_button   : false
										});

					globals.store.store_update(modal_store_key, initial_options);

					ps_view.render($('body'),'modal',{
						data    : { 
									category : category || globals.modal.default_category,
									local    : globals.store.store_fetch(modal_store_key),
									store_key: modal_store_key,
									global   : callables.modal.global_store()
								},
						computed: {
									has_class:  function() {
												return (!ps_helper.empty(this.local.modal_class));
											},
									is_latest: function() {
												var active_categories = this.global.active_categories;
												return (active_categories[active_categories.length - 1] === category);
											},

									content_is_active: function() {
														var vm = this;

														var is_actives = [];
														vm.local.list_ids.forEach(function(id, index) {
				                                            is_actives.push(id == vm.local.list_active);
														});

				                                        return is_actives;
													}
								},
						mounted : function() {
									var vm = this;

									$(vm.$el).on('show.bs.modal', function() {
										callables.modal.capture_tab(category);
										globals.store.store_update(modal_store_key, 'category_is_active', true);

										// push to active category list
										globals.store.store_list_delete('modal_global', function(value) {
											return value==category;
										}, 'active_categories');
										globals.store.store_list_push('modal_global', 'active_categories', category);

										// add open modal in body
										$('body').addClass('ps_js-popup_modal_open');
									});
									
									$(vm.$el).on('hide.bs.modal', function() {
										globals.store.store_update(modal_store_key, { 
											category_is_active: false,
											stick_header      : false
										});

										// remove from active categoty list
										globals.store.store_list_delete('modal_global', function(value) {
											return value==category;
										}, 'active_categories');
										
										callables.modal.release_tab(category);
									});

									$(vm.$el).on('hidden.bs.modal', function() {
										// remove open modal class if there's no active modal
										if (globals.store.store_fetch('modal_global').active_categories.length<=0) {
											$('body').removeClass('ps_js-popup_modal_open');
										}
									});

									// bind additional elements event
									callables.modal.events(vm);

									if ($.isFunction(callback)) {
										callback();
									}
								},
						updated : function() {
									if (this.local.is_loading == true) {
										var cur_dom_length = $(this.$el).find('.ps_js-modal_content').length;
										if (this.local.list_dom_length != cur_dom_length) {

											globals.store.store_update(modal_store_key, {
												list_dom_length: cur_dom_length,
												is_loading     : false
											});

											// check all onrender callbacks and check if its element already present
											var category_onrender = globals.modal.onrender[category];
											for (var id in category_onrender) {
												var content_dom = callables.modal.get_id_content(id, category); 
												
												if (content_dom.length > 0) {
													var onrender_functions = category_onrender[id];
													delete category_onrender[id];

													onrender_functions.forEach(function(onrender) {
														if ($.isFunction(onrender)) {
															onrender(content_dom, true);
														}
													});
												}
											}
										}
									}
								}
					});

				} else {

					if ($.isFunction(callback)) {
						callback();
					}
				}	
			},

			/**
			 * This will trap the tab key inside modal only
			 * @param  object e
			 * @param  string category
			 * @return void
			 */
			tab: function(e) {
				var category        = e.data;
				var modal_store_key = callables.modal.store_key(category);
				var local_store     = globals.store.store_fetch(modal_store_key);
				var global_store    = callables.modal.global_store();
				var active_categories = global_store.active_categories;
				if (active_categories[active_categories.length - 1] === category) {
					var id              = local_store.list_active;
					var active_content  = callables.modal.get_id_content(id, category);
					var current_tabbed  = active_content.find(':focus');
					var tabbables       = $(ps_helper.tabbable('#ps_js-'+category+'_modal .ps_js-modal_'+id+' '))
											.filter(':visible');
					if (e.keyCode == 9 && e.shiftKey) {
			            e.preventDefault();
						if (current_tabbed.length > 0) {

							var prev_index = tabbables.index(current_tabbed) - 1;
							if (prev_index >= 0) {
								tabbables.eq(prev_index).trigger('focus');   
							} else {
								tabbables.last().trigger('focus');   
							}

						} else {

							tabbables.last().trigger('focus');   

						}

			        } else if (e.keyCode == 9) {

			            e.preventDefault();
						if (current_tabbed.length > 0) {
							var next_index = tabbables.index(current_tabbed) + 1;
							if (next_index < tabbables.length) {
								tabbables.eq(next_index).trigger('focus');   
							} else {
								tabbables.first().trigger('focus');   
							}

						} else {
							tabbables.first().trigger('focus');   
						}
						
			        }
				}
			},

			/**
			 * This will initiate tab trapper
			 * @param  string category
			 * @return void
			 */
			capture_tab: function(category) {
				globals.modal.prev_focused = $(':focus');
				if (globals.modal.prev_focused.length > 0) {
					globals.modal.prev_focused.blur();
				}

				$(document).on('keydown', null, category, callables.modal.tab);
			},

			/**
			 * This will turn off tab trapper
			 * @param  string category
			 * @return void
			 */
			release_tab: function(category) {
				if (ps_helper.is_dom(globals.modal.prev_focused) && globals.modal.prev_focused.length > 0) {
					globals.modal.prev_focused.trigger('focus');   
				}

				var global_store = callables.modal.global_store();
				if (global_store.active_categories.length <= 0 ) {
					$(document).off('keydown', callables.modal.tab);
				}
			},

			/**
			 * This will add event handlers for modal additional custom elements
			 * @param  object vm 
			 * @return void
			 */
			events: function(vm) {

				// events related to scrolling
				var scrolling_dom = $($(vm.$el).attr('data-scroller'));
				
				if (scrolling_dom.length > 0) {

					var back_to_top = $(vm.$el).find('.ps_js-modal_back_top');
					scrolling_dom.on('scroll', function() {
						var element = $(this);

						// back to top button
						if (back_to_top.length>0) {
               				globals.store.store_update(vm.store_key, 'back_top_button', (element.scrollTop() > 0));
						}
					});

					// back to top event
					back_to_top.on('click', function() {
	                    ps_helper.animate(scrolling_dom,{ scrollTop: 0 },'fast');
					});
					
				}
			}
		}
	};

	return {
		toast: callables.toast,
		modal: callables.modal,

        /**
         * ajax error and fail toast/modal callback
         * @param  string err_code 
         * @return void
         */
        ajax_error_notification: function(err_code) {
            if (!ps_helper.empty(err_code)) {

            	var is_error_handled = false;
                var error_message 	 = ps_language.error(err_code);
                var error_toast   	 = function() {
                                        callables.toast.open(error_message.content, {
                                            title: error_message.title,
                                            auto : true
                                        });
                                    };

                switch (err_code) {
                    case '-3':
                    case 'ERR_00061':
                    case 'ERR_00118':

		                ps_model.view_data({

			                success: function(view_data) {

			                	if (view_data.error_notif_config.timeout_user_action) {

			                		callables.modal.open('session_tmeout', {
			                			modal_class: 'session_timeout_root',
			                			header     : error_message.title,
			                			body       : error_message.content,
			                			closable   : false,
			                			footer     : function(modal_part) {
						                                ps_view.render(modal_part, 'session_timeout_footer', {
						                                    replace : false,
						                                    mounted : function() {
						                                    			var vm = this;
						                                    			$(vm.$el).find('.ps_js-refresh_page').on(
						                                    				'click',
						                                    				function(){
						                                    					callables.modal.close(
						                                    						'session_timeout'
						                                    					);
						                                    				}
						                                    			);
						                                    		}
						                                });
						                            }
			                    	});

		                    	} else {

                   					error_toast();

		                    	}
			                }

						}, ['error_notif_config']);

                        break;

                    case '-1':

                        error_toast();
                        window.location = '';
                        break;

                    default: 

                   		error_toast();
                }

            }
        },

        /**
         * ajax success toast/modal callback
         * @param  string message 
         * @return void
         */
        ajax_success_notification: function(message) {

            if (!ps_helper.empty(message)) {
                callables.toast.open(message[1], {
                    title: message[0],
                    type : 'done',
                    auto : true
                });
            }
            
        },

        /**
         * This will popup current client status via modal
         * @param  string  err_code 
         * @param  boolean is_active
         * @return void
         */
        client_status_notification: function(err_code, is_active) {

            if (!ps_helper.empty(err_code)) {
	            var error_message = ps_language.error(err_code);

                ps_model.view_data({

	                success: function(view_data) {

			                	if (view_data.error_notif_config.client_status_action) {

						            ps_popup.modal.open('client_status', {
						                modal_class: 'client_status_root',
						                header     : error_message.title,
						                body       : error_message.content,
						                closable   : false,
						                footer     : function(modal_part) {
						                                ps_view.render(modal_part, 'client_status_footer', {
						                                    replace : false,
						                                    mounted : function() {
						                                                var vm = this;
						                                                $(vm.$el).find('.ps_js-ok_button').on(
						                                                	'click',
						                                                	function(){
						                                                   		ps_popup.modal.close('client_status');
						                                                	}
						                                                );
						                                            }
						                                });
						                            }
						            });

						        } else {

                                    callables.toast.open(error_message.content, {
                                        title: error_message.title,
                                        auto : true
                                    });
                                    
						        }

	                		}

				}, ['error_notif_config']);
	        }

        }
	};

});