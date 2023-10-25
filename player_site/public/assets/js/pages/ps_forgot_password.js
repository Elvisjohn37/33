
/**
 * Forgot password page
 * 
 * @author PS Team
 */
define('ps_forgot_password', [
	'ps_view',
	'ps_model',
	'ps_popup',
	'ps_language',
	'ps_store',
	'ps_helper',
	'jquery',
    'ps_validator'
], function() {

    var ps_view      = arguments[0];
    var ps_model     = arguments[1];
    var ps_popup     = arguments[2];
    var ps_language  = arguments[3];
    var ps_store     = arguments[4];
    var ps_helper    = arguments[5];
    var $            = arguments[6];
    var ps_validator = arguments[7];

    var globals   = { 
    					store  : new ps_store('ps_forgot_password'),
                        form_id: 0 
    				};

    var callables = {
    	/**
    	 * reset forgot password
    	 * @return void
    	 */
    	forgot_password_reset: function() {
    		// turn everything back to beginning
            $('.ps_js-forgot_password_form').trigger('view_components_fullreset');
            

			if (globals.store.store_exists('info')) {
				globals.store.store_update('info', { request_num: globals.store.store_fetch('info').request_num + 1 });
			} else {
				globals.store.store_update('info', { request_num: 0 });
			}

			globals.store.store_update('info', { 
				step            : 1, 
				securityQuestion: '' ,
				loading         : false
			});
            
            $('.ps_js-forgot_password_form .ps_js-forgot_password_first').trigger('focus');
    	},

    	/**
    	 * This will get info store if existing else initialiaze it
    	 * @return object
    	 */
    	forgot_password_info: function() {
    		if (!globals.store.store_exists('info')) {
    			callables.forgot_password_reset();
    		} 

    		return globals.store.store_fetch('info');
    	},

    	/**
    	 * This will perform ajax and get securityQuestion before navigating to next step
    	 * @param  dom form 
    	 * @return void
    	 */
    	forgot_password_next: function(form) {
    		var info_store = globals.store.store_fetch('info');
    		if (!info_store.loading) {
    			var used_req_num = info_store.request_num + 1;
    			globals.store.store_update('info', { 
    				loading    : true ,
    				request_num: used_req_num
    			});
    			ps_model.get_securityQuestion(ps_helper.json_serialize(form.find(':visible')), {
    				ps_validator_form: form,
    				success: function(response) {
    							// check if this is still the request we're waiting
    							if (used_req_num == info_store.request_num) {
	    							globals.store.store_update('info', {
	    								step: 2,
	    								securityQuestion: response.securityQuestion
	    							});

                                    ps_helper.ready(
                                        '.ps_js-forgot_password_form .ps_js-yourAnswer:visible',
                                        function() { $(this).trigger('focus'); },
                                        $(this)
                                    );
    							}
    						},
    				fail    : function(response, status, jqXHR, ajax_setup) {
    							// check if this is still the request we're waiting, if not:
    							// remove form as request component so that error will not be displayed
    							if (used_req_num != info_store.request_num) {
    								delete ajax_setup.ps_validator_form;
    							}
    						},
    				complete: function(response) {
    							// check if this is still the request we're waiting
    							if (used_req_num == info_store.request_num) {
    								globals.store.store_update('info', 'loading', false);
    							}
    						}
    			});
    		}
    	},

    	/**
    	 * This will submit all forgot password credential
    	 * @param  dom form 
    	 * @return void
    	 */
    	forgot_password_submit: function(form) {
    		var info_store = globals.store.store_fetch('info');
    		if (!info_store.loading) {
    			var used_req_num = info_store.request_num + 1;
    			globals.store.store_update('info', { 
    				loading    : true,
    				request_num: used_req_num
    			});

    			ps_model.forgot_password_submit(ps_helper.json_serialize(form), {
    				success: function() {
						// check if this is still the request we're waiting
    					if (used_req_num == info_store.request_num) {
    						ps_popup.modal.close('forgot_password','floating_page');
    					}
    				},
    				fail   : function() {
						// check if this is still the request we're waiting
						if (used_req_num == info_store.request_num) {
			    			callables.forgot_password_reset();
						}
    				}
    			});
    		}
    	},

        /**
         * This will validate forgot password
         * @return object
         */
        forgot_password_validation: function() {
            return {
                '.ps_js-loginName'    : {
                                            triggers: 'blur',
                                            validate: [{as:'required'}]
                                        },
                '.ps_js-email'        : {
                                            triggers: 'blur',
                                            validate: [{as:'required'},{as:'email'}]
                                        },
                '.ps_js-captcha_input': {
                                            triggers: 'blur',
                                            validate: [{as:'required',type:'captcha'}]
                                        },
                '.ps_js-yourAnswer'   : {
                                            triggers: 'blur',
                                            validate: [{as:'required',visible_only:true}]
                                        }
            };
        },

        /**
         * This will get reset password info store if existing else initialiaze it
         * @return object
         */
        reset_password_info: function() {
            if (!globals.store.store_exists('reset_password_info')) {

                // open modal and generate form
                var form_id = globals.form_id;
                globals.form_id++;

                globals.store.store_update('reset_password_info', {
                    loading    : false,
                    form_id    : form_id,
                    request_num: 0
                });
            } 

            return globals.store.store_fetch('reset_password_info');
        },       

        /**
         * Reset password validations
         * @param  dom    form 
         * @return object
         */
        reset_password_validation: function(form) {
            var form_id           = form.attr('id');
            var password_selector = '#'+form_id+' .ps_js-password_field';

            return {
                '.ps_js-password_field'   : {
                                                triggers: 'blur focus',
                                                prevent : true,
                                                validate: [
                                                            {
                                                                as              : 'required',
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
                                                                exclude_triggers: ['focus','prevent']
                                                            },
                                                            {
                                                                as              : 'same',
                                                                type            : 'password',
                                                                field           : password_selector,
                                                                exclude_triggers: ['focus','prevent']
                                                            },
                                                            {
                                                                as              : 'max_length',
                                                                type            : 'password',
                                                                exclude_triggers: ['focus']
                                                            }
                                                        ]
                                            }
            };
        },

        /**
         * This will submit reset password form
         * @param  string code
         * @param  dom    form 
         * @return void
         */
        reset_password_submit: function(code, form) {
            var reset_password_info = callables.reset_password_info();

            if (!reset_password_info.loading) {

                var used_req_num = reset_password_info.request_num + 1;

                globals.store.store_update('reset_password_info', { 
                    loading    : true,
                    request_num: used_req_num
                });

                ps_model.new_password(code, ps_helper.json_serialize(form), {
                    ps_validator_form: form,
                    success: function() {
                        // check if this is still the request we're waiting
                        if (used_req_num == reset_password_info.request_num) {
                            ps_popup.modal.close('forgot_password_reset');
                        }
                    },
                    complete: function() {
                        // check if this is still the request we're waiting
                        if (used_req_num == reset_password_info.request_num) {
                            globals.store.store_update('reset_password_info', { loading: false });
                        }
                    }
                });
            }
        },
    };

    return {
    	/**
    	 * This will trigger on forgot password page activation
    	 * @param  string hash 
    	 * @return void
    	 */
    	activate: function(hash) {

            var forgot_password_info = callables.forgot_password_info();

    		ps_popup.modal.open('forgot_password', {
                modal_class: 'forgot_password_root',
    			header: ps_language.get('language.lost_password'),
    			body  : function(modal_part) {
	    					ps_view.render(modal_part, 'forgot_password', {
	    						replace: false,
	    						data   : { info: forgot_password_info },
	    						mounted: function() {	
	    									var vm   = this;
                                            var form = $(vm.$el).find('.ps_js-forgot_password_form');

                                            // apply form validations
                                            ps_validator.apply(form, {
                                                validations: callables.forgot_password_validation(),
                                                success    : function() {   
                                                                if (vm.info.step == 1) {
                                                                    callables.forgot_password_next(form);
                                                                } else {
                                                                    callables.forgot_password_submit(form);
                                                                }
                                                            }
                                            });
	    								} 
	    					});
	    				},
                footer: function(modal_part) {
                            ps_view.render(modal_part,'forgot_password_footer', {
                                replace : false,
	    						data    : { info: forgot_password_info },
                                mounted : function() {
                                			var vm   = this;

                                			// submit
                                			$(vm.$el).find('.ps_js-forgot_password_submit').on('click', function() {
                                				$('.ps_js-forgot_password_form').trigger('submit');
                                			});
                                        }
                            });
                        },
                bind    : {
                            hide: function() {
                                    window.location = ps_model.active_main_hash();
                                },
                            shown: function() {
                                    ps_helper.ready(
                                        '.ps_js-forgot_password_form .ps_js-forgot_password_first:visible',
                                        function() { $(this).trigger('focus'); },
                                        $(this)
                                    );
                                }
                        },
				onrender: function() {
                            callables.forgot_password_reset();
		                }
    		}, 'floating_page');
    	},

    	/**
    	 * This will trigger on forgot password page deactivation
    	 * @return void
    	 */
    	deactivate: function() {
    		ps_popup.modal.close('forgot_password', 'floating_page');
    	},

        /**
         * This will open reset password modal, which user can finally reset their password
         * This is being triggered by flash sessions in ps_core
         * @param  string code
         * @return void
         */
        activate_reset_password: function(code) {
            var reset_password_info = callables.reset_password_info();

            ps_popup.modal.open('forgot_password_reset', {
                modal_class: 'forgot_password_reset',
                closable: false,
                header  : ps_language.get('language.create_new_password'),
                body    : function(modal_part) {

                            ps_view.render(modal_part, 'reset_password', {
                                replace: false,
                                data   : { info: reset_password_info },
                                mounted: function() {   
                                            var vm = this;

                                            var form = $(vm.$el).find('.ps_js-reset_password_form');

                                            // apply form validations
                                            ps_validator.apply(form, {
                                                validations: callables.reset_password_validation(form),
                                                success    : function() {   
                                                                callables.reset_password_submit(code, form);
                                                            }
                                            });
                                        } 
                            });

                        },
                footer  : function(modal_part) {

                            ps_view.render(modal_part, 'reset_password_footer', {
                                replace : false,
                                data    : { info: reset_password_info },
                                mounted : function() {
                                            var vm = this;

                                            // submit
                                            $(vm.$el).find('.ps_js-reset_password_submit').on('click', function() {
                                                $('#'+vm.info.form_id).trigger('submit');
                                            });
                                        }
                            });

                        },

                bind    : {
                            shown: function() {
                                    ps_helper.ready(
                                        '#'+ reset_password_info.form_id + ' .ps_js-password_field:visible',
                                        function() { $(this).trigger('focus'); },
                                        $(this)
                                    );
                                }
                        }
            });
        }
    };
});
