/**
 * Account page module handler
 * 
 * @author PS Team
 */
define('ps_account', ['ps_view','ps_model','jquery','ps_store','ps_helper','ps_validator'], function() {
	
    var ps_view      = arguments[0];
    var ps_model     = arguments[1];
    var $            = arguments[2];
    var ps_store     = arguments[3];
    var ps_helper    = arguments[4];
    var ps_validator = arguments[5];

	var globals   = { is_page_rendered: false, store: new ps_store('ps_account') };
	var callables = {
		/**
		 * Get/Create account page store
		 * @return object
		 */
		account_page_info: function() {
			if (!globals.store.store_exists('info')) {
				globals.store.store_update('info', {
					loaded_hash                 : [],
					register_friend_loading     : false,
					register_friend_handled     : false,
					deposit_confirmation_loading: false,
					deposit_confirmation_handled: false,
					withdrawal_request_loading  : false,
					withdrawal_request_handled  : false,
					change_password_loading     : false,
					fund_transfer_loading       : false,
					fund_transfer_handled       : false,
					fund_transfer_to            : '',
					reward                      : []
				});
			}

			return globals.store.store_fetch('info');
		},

		/**
		 * This will get all needed data for account page to initiate
		 * Triggered in account page mounted event
		 * @return void
		 */
		page_init: function() {
			var vm = this;
			ps_model.view_data({
				success: function(response) {
							vm.view_data = response;

							// mark page as rendered
							ps_model.update_page_rendered(vm.hash_info.page);
							vm.is_init = true;
						}
			},['account', 'user','navigation','configs','wallets_dropdown', 'bank_account_config', 'reward']);
		},

		/**
		 * This will trigger when page active hash changed
		 * Triggered in account watch event
		 * @param  string new_hash
		 * @param  string old_hash
		 * @return void
		 */
		active_hash_change: function(new_hash, old_hash) {
			var vm           = this;
			var is_recent    = !ps_helper.in_array(new_hash, vm.page_info.loaded_hash);
			var init_handler = function() {
								// old deactivate
								var old_handlers_key = ps_helper.replace_all(old_hash,'#','');
								var old_handlers     = callables[old_handlers_key];
								if ($.isPlainObject(old_handlers)) {
									if ($.isFunction(old_handlers.deactivate)) {
										old_handlers.deactivate.call(vm);
									}
								}

								// new activate
								var new_handlers_key = ps_helper.replace_all(new_hash,'#','');
								var new_handlers     = callables[new_handlers_key];
								if ($.isPlainObject(new_handlers)) {
									if ($.isFunction(new_handlers.activate)) {
										new_handlers.activate.call(vm,is_recent);
									}
								}
							};

			if (is_recent) {
				globals.store.store_list_push('info','loaded_hash', new_hash);
				vm.$nextTick(function() {
					init_handler();
				});
			} else {
				init_handler();
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | PROFILE SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		profile: {
			/**
			 * This will trigger after profile subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				
				// bind events on first time load
				if (is_recent) {
					$(vm.$el).find('.ps_js-displayName .ps_js-button_text_trigger').on('click', function() {
						require(['ps_displayname'], function(ps_displayname) {
							ps_displayname.open();
						});
					});
				}
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | BALANCE SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		balance: {
			/**
			 * This will trigger after balance subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				
				if (!is_recent) {
					$(vm.$el).find('.ps_js-account_balance .ps_js-usedbalance_root').trigger('view_components_refresh');
				}
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | REGISTER A FRIEND SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		register_friend: {
			/**
			 * This will trigger after register friend subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				if (is_recent) {
					// when this change our view changes so we need to call activate again
					vm.$watch('view_data.user.derived_is_transactable', function() {
						globals.store.store_update('info',{ register_friend_handled: false });
						if (vm.active_main_hash === '#register_friend') {
							callables.register_friend.update_handlers(vm);
						}
					});
				}

				callables.register_friend.update_handlers(vm);
			},

			/**
			 * This will update register friend handlers accordingly
			 * @param  object vm
			 * @return void
			 */
			update_handlers: function(vm) {
				// bind events if not yet handled
				var form = $(vm.$el).find('.ps_js-register_friend_form');
				if (form.length > 0) {
					if (!vm.page_info.register_friend_handled) {

						globals.store.store_update('info',{ register_friend_handled: true });
		                // apply form validations
		                ps_validator.apply(form, {
		                    validations: callables.register_friend.validations(),
		                    success    : function() {
		                    				//  form submission
		                    				globals.store.store_update('info', { register_friend_loading: true });
							    			ps_model.register_friend(ps_helper.json_serialize(form), {
							                    ps_validator_form: form,
							    				success: function() {
													form.trigger('view_components_fullreset');
							    				},
							    				complete: function() {
		                    						globals.store.store_update('info',{ register_friend_loading:false});
							    				}
							    			});
		                     			}
		                });
						
						// buttons
						form.find('.ps_js-register_friend_reset').on('click', function() {
							form.trigger('view_components_fullreset');
						});
					}

					// reset frm first
					form.trigger('view_components_fullreset');
				}
			},

			/**
			 * This will pass validation object needed for register friend form validation
			 * @return object
			 */
			validations: function() {
				return {
	                '.ps_js-firstName'         : {
			                                        triggers: 'blur',
			                                        prevent : true,
			                                        validate: [
			                                        			{as:'required'},
			                                        			{as:'alpha_language'}
			                                        		]
			                                    },
	                '.ps_js-lastName'          : {
			                                        triggers: 'blur',
			                                        prevent : true,
			                                        validate: [
			                                        			{as:'required'},
			                                        			{as:'alpha_language'}
			                                        		]
			                                    },
	                '.ps_js-bank_select'       : {
			                                        triggers: 'blur',
			                                        validate: [
			                                                    {as: 'required'},
			                                                    {as: 'bank_dropdown'}
			                                                ]
			                                    },
	                '.ps_js-bank_input_segment': {
	                                                triggers: 'blur',
	                                                validate: [{
	                                                            as          : 'required', 
	                                                            type        : 'multiple',
	                                                            visible_only: true
	                                                        }]
	                                            },
	                '.ps_js-accountBankName'   : { triggers: 'blur', validate: [{as: 'required'}] },
	                '.ps_js-emailAddress'      : {
		                                            triggers: 'blur',
		                                            validate: [{as:'required'},{as:'email'}]
		                                        },
	                '.ps_js-mobileNo'          : {
		                                            triggers: 'blur',
		                                            prevent : true,
		                                            validate: [
		                                                        {
		                                                        	as: 'required',
		                                                        	exclude_triggers: ['prevent']
		                                                        },
		                                                        {as: 'mobile_number'},
		                                                        {as: 'max_length'    , type: 'mobile'},
		                                                        {
		                                                            as  : 'min_length', 
		                                                            type: 'mobile' ,
		                                                            exclude_triggers:['prevent']
		                                                        }
		                                                    ]
		                                        }
	            };
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | DEPOSIT CONFIRMATION SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		deposit_confirmation: {
			/**
			 * This will trigger after deposit confirmation subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				if (is_recent) {
					// when this change our view changes so we need to call activate again
					vm.$watch('view_data.user.derived_is_transactable', function() {
						globals.store.store_update('info',{ deposit_confirmation_handled: false });
						if (vm.active_main_hash === '#deposit_confirmation') {
							callables.deposit_confirmation.update_handlers(vm);
						}
					});
				}

				callables.deposit_confirmation.update_handlers(vm);
			},

			/**
			 * This will update deposit confirmation handlers accordingly
			 * @param  object vm
			 * @return void
			 */
			update_handlers: function(vm) {
				// bind events if not yet handled
				var form = $(vm.$el).find('.ps_js-deposit_confirmation_form');
				if (form.length > 0) {
					if (!vm.page_info.deposit_confirmation_handled) {

						globals.store.store_update('info',{ deposit_confirmation_handled: true });
		                // apply form validations
		                ps_validator.apply(form, {
		                    validations: callables.deposit_confirmation.validations(),
		                    success    : function() {
		                    				//  form submission
		                    				globals.store.store_update('info', { deposit_confirmation_loading: true });

		                    				var form_inputs = form.find(':visible');
							    			ps_model.deposit_confirmation(ps_helper.json_serialize(form_inputs), {
							                    ps_validator_form: form,
							    				success: function(response) {
															form.trigger('view_components_fullreset');
									    				},
							    				complete: function() {
				                    						globals.store.store_update('info',{ 
				                    							deposit_confirmation_loading: false
				                    						});
									    				}
							    			});
		                     			}
		                });
						
						// buttons
						form.find('.ps_js-deposit_confirmation_reset').on('click', function() {
							form.trigger('view_components_fullreset');
						});
					}

					// reset frm first
					form.trigger('view_components_fullreset');
				}
			},

			/**
			 * This will pass validation object needed for deposit confirmation form validation
			 * @return object
			 */
			validations: function() {
				return {
	                '.ps_js-bank_select'       : {
			                                        triggers: 'blur',
			                                        validate: [
			                                                    {as: 'required'},
			                                                    {as: 'bank_dropdown'}
			                                                ]
			                                    },
	                '.ps_js-bank_input_segment': {
	                                                triggers: 'blur',
	                                                validate: [{
	                                                            as          : 'required', 
	                                                            type        : 'multiple',
	                                                            visible_only: true
	                                                        }]
	                                            },
	                '.ps_js-accountBankName'    : { triggers: 'blur', validate: [{as: 'required'}] },
	                '.ps_js-money_input'        : { 
            										triggers: 'blur', 
            										prevent : true,
            										validate: [
            													{as: 'required', exclude_triggers:['prevent']}, 
            													{as: 'money'}
            												] 
            									},
	                '.ps_js-captcha_input'     : { triggers: 'blur', validate: [{as: 'required', visible_only: true}] }
	            };
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | WITHDRAWAL REQUEST SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		withdrawal_request: {
			/**
			 * This will trigger after withdrawal request subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				if (is_recent) {
					// when this change our view changes so we need to call activate again
					vm.$watch('view_data.user.derived_is_transactable', function() {
						globals.store.store_update('info',{ withdrawal_request_handled: false });
						if (vm.active_main_hash === '#withdrawal_request') {
							callables.withdrawal_request.update_handlers(vm);
						}
					});
				}

				callables.withdrawal_request.update_handlers(vm);
			},

			/**
			 * This will update withdrawal request handlers accordingly
			 * @param  object vm
			 * @return void
			 */
			update_handlers: function(vm) {
				// bind events if not yet handled
				var form = $(vm.$el).find('.ps_js-withdrawal_request_form');
				if (form.length > 0) {
					if (!vm.page_info.withdrawal_request_handled) {

						globals.store.store_update('info',{ withdrawal_request_handled: true });
		                // apply form validations
		                ps_validator.apply(form, {
		                    validations: callables.withdrawal_request.validations(),
		                    success    : function() {
		                    				//  form submission
		                    				globals.store.store_update('info', { withdrawal_request_loading: true });
		                    				var form_inputs = form.find(':visible');
							    			ps_model.withdrawal_request(ps_helper.json_serialize(form_inputs), {
							                    ps_validator_form: form,
							    				success: function(response) {
															form.trigger('view_components_fullreset');
									    				},
							    				complete: function() {
				                    						globals.store.store_update('info',{ 
				                    							withdrawal_request_loading: false
				                    						});
									    				}
							    			});
		                     			}
		                });
						
						// buttons
						form.find('.ps_js-withdrawal_request_reset').on('click', function() {
							form.trigger('view_components_fullreset');
						});
					}

					// reset frm first
					form.trigger('view_components_fullreset');
				}
			},

			/**
			 * This will pass validation object needed for withdrawal request form validation
			 * @return object
			 */
			validations: function() {
				return {
	                '.ps_js-password'                 : { triggers: 'blur', validate: [{as: 'required'}] },
	                '.ps_js-amount .ps_js-money_input': { 
	                										triggers: 'blur', 
	                										prevent : true,
	                										validate: [
	                													{as: 'required', exclude_triggers:['prevent']}, 
	                													{as: 'money'}
	                												] 
	                									},
	                '.ps_js-captcha_input'            : { 
	                										triggers: 'blur', 
	                										validate: [{as: 'required',visible_only: true}] 
	                									}
	            };
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | CHANGE PASSWORD SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		change_password: {
			/**
			 * This will trigger after  change password subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm   = this;
				var form = $(vm.$el).find('#ps_js-change_password');

				if (is_recent) {
					// bind events and form validations
	                ps_validator.apply(form, {
	                    validations: callables.change_password.validations(vm),
	                    success    : function() {
	                    				//  form submission
	                    				globals.store.store_update('info', { change_password_loading: true });
						    			ps_model.change_password(ps_helper.json_serialize(form), {
						                    ps_validator_form: form,
						    				success: function(response) {
														form.trigger('view_components_fullreset');
								    				},
						    				complete: function() {
			                    						globals.store.store_update('info',{ 
			                    							change_password_loading: false
			                    						});
								    				}
						    			});
	                     			}
	                });

					// buttons
					form.find('.ps_js-change_password_reset').on('click', function() {
						form.trigger('view_components_fullreset');
					});
				}

				// reset frm first
				form.trigger('view_components_fullreset');
			},

			/**
			 * This will pass validation object needed for change password form validation
			 * @param  object vm
			 * @return object
			 */
			validations: function(vm) {
				var password_selector = '#ps_js-change_password .ps_js-password_field';

				return {
	                '.ps_js-current_password' : { 
	                								triggers: 'blur', 
		                                            prevent : true,
	                								validate: [
	                											{
	                												as: 'required',
		                                                            exclude_triggers: [ 'prevent']
	                											},
		                                                        {
		                                                            as              : 'max_length',
		                                                            type            : 'password'
		                                                        }
	                										] 
	                							},
	                '.ps_js-password_field'   : {
		                                            triggers: 'blur focus',
		                                            prevent : true,
		                                            validate: [
		                                                        {
		                                                            as              : 'required',
		                                                            exclude_triggers: ['focus', 'prevent']
		                                                        },
		                                                        {
		                                                            as              : 'not_contain',
		                                                            type            : 'password',
		                                                            values          : [
		                                                            					vm.view_data.user.firstName,
		                                                            					vm.view_data.user.lastName,
		                                                            					vm.view_data.user.loginName
		                                                            				],
		                                                            exclude_triggers: ['focus', 'prevent']
		                                                        },
		                                                        {
		                                                            as              : 'max_length',
		                                                            type            : 'password',
		                                                            exclude_triggers: ['focus']
		                                                        },
		                                                        {
		                                                            as              : 'alpha_num_symbol',
		                                                            type            : 'password',
		                                                            exclude_triggers: ['focus', 'prevent']
		                                                        },
		                                                    ]
		                                        },
	                '.ps_js-confirm_new_password': {
		                                            triggers: 'blur focus',
		                                            prevent : true,
		                                            validate: [
		                                                        {
		                                                            as              : 'prerequisite',
		                                                            fields          : [password_selector],
		                                                            is_focus        : true,
		                                                            exclude_triggers: ['prevent']
		                                                        },
		                                                        {
		                                                            as              : 'required',
		                                                            exclude_triggers: ['focus', 'prevent']
		                                                        },
		                                                        {
		                                                            as              : 'same',
		                                                            type            : 'password',
		                                                            field           : password_selector,
		                                                            exclude_triggers: ['focus', 'prevent']
		                                                        },
		                                                        {
		                                                            as              : 'max_length',
		                                                            type            : 'password',
		                                                            exclude_triggers: ['focus']
		                                                        }
		                                                    ]
		                                        },
	            };
			}
		},

        /**
         |--------------------------------------------------------------------------------------------------------------
         | FUND TRANSFER SUBPAGE HANDLERS
         |--------------------------------------------------------------------------------------------------------------
         */
		fund_transfer: {
			/**
			 * This will trigger after fund transfer subpage has been activate
			 * @param  boolean is_recent 
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;

				if (is_recent) {
					// when this change our view changes so we need to call activate again
					vm.$watch('view_data.user.derived_is_transactable', function() {
						globals.store.store_update('info',{ fund_transfer_handled: false });
						if (vm.active_main_hash === '#fund_transfer') {
							callables.fund_transfer.update_handlers(vm);
						}
					});
				}

				callables.fund_transfer.update_handlers(vm);
			},

			/**
			 * This will update fund transfer handlers accordingly
			 * @param  object vm
			 * @return void
			 */
			update_handlers: function(vm) {
				// bind events if not yet handled
				var form = $(vm.$el).find('.ps_js-fund_transfer_form');

				if (form.length > 0) {

					if (!vm.page_info.fund_transfer_handled) {

						globals.store.store_update('info',{ fund_transfer_handled: true });

		                // apply form validations
		                ps_validator.apply(form, {
		                    validations: callables.fund_transfer.validations(),
		                    success    : function() {
		                    				callables.fund_transfer.submit(vm, form);
		                    			}
		                });
						
						// buttons
						form.find('.ps_js-fund_transfer_reset').on('click', function() {
							form.trigger('view_components_fullreset');
						});

						// dropdowns
						var fund_to_dropdown = form.find('.ps_js-fund_to_wallet .ps_js-wallet_select');
						fund_to_dropdown.on('change', function() {
		            		globals.store.store_update('info', { fund_transfer_to: $(this).val() });
						});

						// default wallet
						form.on('view_components_postreset', function() {
							fund_to_dropdown.val(vm.default_fund_to);
							fund_to_dropdown.trigger('change');
						});

					} 

					// reset frm first
					form.trigger('view_components_fullreset');
					// refresh table 
				}
			},

			/**
			 * This will submit fund transfer form
			 * @param  object vm   
			 * @param  dom    form 
			 * @return void
			 */
			submit: function(vm,form) {
				globals.store.store_update('info', { fund_transfer_loading: true });
				var form_inputs = form.find(':visible');
				ps_model.fund_transfer(ps_helper.json_serialize(form_inputs), {
	                ps_validator_form: form,
					success: function(response) {
								form.trigger('view_components_fullreset');

								// get the selected external wallet
								if (vm.page_info.fund_transfer_to == 'house') {
									var selected = form.find('.ps_js-fund_from_wallet .ps_js-wallet_select').val();
								} else {
									var selected = form.find('.ps_js-fund_to_wallet .ps_js-wallet_select').val();
								}

								var wallets = $(vm.$el).find('.ps_js-account_fund_transfer .ps_js-usedbalance_wallets');
								wallets.trigger('view_components_refresh',selected);
		    				},
					complete: function() {
	    						globals.store.store_update('info',{ fund_transfer_loading: false });
		    				}
				});
			},


			/**
			 * This will pass validation object needed for fund transfer form validation
			 * @return object
			 */
			validations: function() {

				return {
					'.ps_js-wallet_select': {
	                                            triggers: 'blur',
	                                            validate: [
	                                                        {
	                                                            as              : 'required',
	                                                            exclude_triggers: ['focus']
	                                                        }
	                                                    ]
	                                        },
	                '.ps_js-money_input'  : { 
        										triggers: 'blur', 
        										prevent : true,
        										validate: [
        													{as: 'required', exclude_triggers:['prevent']}, 
        													{as: 'money'}
        												] 
        									},
	                '.ps_js-captcha_input': { triggers: 'blur', validate: [{as: 'required', visible_only: true}] }
				};
			}
		},
		reward: {
			activate:function(is_recent) {

			var vm = this;

				if (is_recent) {
					globals.store.store_update('info',{ reward: vm.view_data.reward });
				}
				// console.log()
			}

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

			var page_info = callables.account_page_info();

			if (!globals.is_page_rendered) {
				globals.is_page_rendered = true;
				
				ps_view.render($('.ps_js-page_'+hash_info.page), 'account', {
					replace : false,
					computed: {
								page_navigation_data: function() {
														return this.view_data.navigation.pages[this.hash_info.page];
													},
								active_main_hash    : function() {
														var vm = this;
														if (!vm.is_init) {
															return '';
														} else {
															return vm.page_navigation_data.active_main_hash
														}
													},
								displayName_editable: function() {
														var vm = this;
														if (!vm.is_init) {
															return false;
														} else {
															return vm.view_data.user.displayNameStatus!=1;
														}
													},
								fund_from_type      : function() {
														if (this.page_info.fund_transfer_to == 'house') {
															return 'nonhouse';
														} else {
															return 'house';
														}
													},
								default_fund_to     : function() {
														var vm          = this;
														var wallet_keys = Object.keys(vm.view_data.wallets_dropdown);
														if (wallet_keys.length > 1) {
															return wallet_keys[1];
														} else {
															return null;
														}
													},
								bank_account_config : function() {
														return this.view_data.bank_account_config || {};
								},
								bank_account_info : function() {
														return this.bank_account_config.bank_account_info || {};
								}
							},
					watch   : { active_main_hash: callables.active_hash_change }, 
					data    : { hash_info:hash_info, page_info:page_info, view_data:{}, is_init:false },
					mounted : callables.page_init
				});

			}
		}
	};
});