
/**
 * Register page
 * 
 * @author PS Team
 */
define('ps_register', [
	'ps_view',
	'ps_model',
	'ps_popup',
	'ps_language',
	'ps_store',
	'ps_helper',
	'jquery',
    'ps_validator'
], function () {

    var ps_view      = arguments[0];
    var ps_model     = arguments[1];
    var ps_popup     = arguments[2];
    var ps_language  = arguments[3];
    var ps_store     = arguments[4];
    var ps_helper    = arguments[5];
    var $            = arguments[6];
    var ps_validator = arguments[7];

    var globals   = {	
    					store : new ps_store('ps_register'),
                        form_id: 0 
   					};
    var callables = {
    	/**
    	 * reset register
    	 * @return void
    	 */
    	register_reset: function() {
    		// turn everything back to beginning
            $('.ps_js-register_form').trigger('view_components_fullreset');
            
			if (globals.store.store_exists('info')) {
				globals.store.store_update('info', { request_num: globals.store.store_fetch('info').request_num + 1 });
			} else {
				globals.store.store_update('info', { request_num: 0 });
			}
			globals.store.store_update('info', { loading : false, validation_id: null });

            $('.ps_js-register_form .ps_js-register_first').trigger('focus');
    	},

    	/**
    	 * This will get info store if existing else initialiaze it
    	 * @return object
    	 */
    	register_info: function() {
    		callables.register_reset();

    		return globals.store.store_fetch('info');
    	},

    	/**
    	 * This will submit all register credential
    	 * @param  dom form 
    	 * @return void
    	 */
    	register_submit: function(form) {
    		var info_store = globals.store.store_fetch('info');
    		if (!info_store.loading) {
    			var used_req_num = info_store.request_num + 1;

    			globals.store.store_update('info', { 
    				loading    : true,
    				request_num: used_req_num
    			});

    			ps_model.register(ps_helper.json_serialize(form), {
                    ps_validator_form: form,
    				success: function() {
						// check if this is still the request we're waiting
    					if (used_req_num == info_store.request_num) {
    						ps_popup.modal.close('register','floating_page');
    					}
    				},
    				complete: function() {
						// check if this is still the request we're waiting
						if (used_req_num == info_store.request_num) {
			    			globals.store.store_update('info', { loading: false });
						}
    				}
    			});
    		}
    	},

        /**
         * Generate register form validation
         * @param  dom    form
         * @return object
         */
        register_validation: function(form, vm) {
            var form_id               = form.attr('id');

            var password_dependencies = [
                                        '#'+form_id+' .ps_js-firstName',
                                        '#'+form_id+' .ps_js-lastName',
                                        '#'+form_id+' .ps_js-loginName',
                                    ];
            var password_selector     = '#'+form_id+' .ps_js-password_field';


            return {
                '.ps_js-firstName': {
                                        triggers: 'blur',
                                        prevent : true,
                                        validate: [
                                                    {as:'required'},
                                                    {as:'alpha_language'}
                                                ]
                                    },
                '.ps_js-lastName' : {
                                        triggers: 'blur',
                                        prevent : true,
                                        validate: [
                                                    {as:'required'},
                                                    {as:'alpha_language'}
                                                ]
                                    },
                '.ps_js-loginName' : {
                                        triggers: 'blur',
                                        prevent : true,
                                        validate: [
                                                    {as:'required',    exclude_triggers: ['prevent']},
                                                    {as:'loginName',   exclude_triggers: ['prevent']},
                                                    {as: 'max_length', type            : 'loginName'}
                                                ]
                                    },
                '.ps_js-password_field' : {
                                            triggers: 'blur focus',
                                            prevent : true,
                                            validate: [
                                                        {
                                                            as              : 'prerequisite',
                                                            fields          : password_dependencies,
                                                            is_focus        :true,
                                                            exclude_triggers: ['prevent']
                                                        },
                                                        {
                                                            as              : 'required',
                                                            exclude_triggers: ['focus', 'prevent']
                                                        },
                                                        {
                                                            as              : 'not_contain',
                                                            type            : 'password',
                                                            fields          : password_dependencies,
                                                            exclude_triggers: ['focus', 'prevent']
                                                        },
                                                        {
                                                            as              : 'max_length',
                                                            type            : 'password',
                                                            exclude_triggers: ['focus']
                                                        },
                                                        {
                                                            as              : 'min_length',
                                                            type            : 'password',
                                                            exclude_triggers: ['focus','prevent']
                                                        },
                                                        {
                                                            as              : 'alpha_num_symbol',
                                                            type            : 'password',
                                                            fields          : password_dependencies,
                                                            exclude_triggers: ['focus', 'prevent']
                                                        },
                                                        {as:'no_space'}
                                                    ]
                                        },
                '.ps_js-retypePassword': {
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
                                        },
                '.ps_js-emailAddress' : {
                                            triggers: 'blur',
                                            validate: [{as:'required'},{as:'email'}]
                                        },
                '.ps_js-confirmEmailAddress': {
                                                triggers: 'blur focus',
                                                validate: [
                                                            {
                                                                as      : 'prerequisite',
                                                                fields  : ['#'+form_id+' .ps_js-emailAddress'],
                                                                is_focus:true
                                                            },
                                                            {
                                                                as              : 'required',
                                                                exclude_triggers: ['focus']
                                                            },
                                                            {
                                                                as              : 'same',
                                                                type            : 'email',
                                                                field           : '#'+form_id+' .ps_js-emailAddress',
                                                                exclude_triggers: ['focus']
                                                            }
                                                        ]
                                            },
                '.ps_js-mobileNo': {
                                            triggers: 'blur',
                                            prevent : true,
                                            validate: [
                                                        {as: 'required'      , exclude_triggers: ['prevent']},
                                                        {as: 'mobile_number'},
                                                        {as: 'max_length'    , type: 'mobile'},
                                                        {
                                                            as  : 'min_length', 
                                                            type: 'mobile' ,
                                                            exclude_triggers:['prevent']
                                                        }
                                                    ]
                                        },
                '.ps_js-bank_select': {
                                        triggers: 'blur',
                                        validate: [
                                                    {
                                                        as: vm.bank_account_info == 'remove' ?  'null' : 'required',
                                                        visible_only: true
                                                    },
                                                    {as: 'bank_dropdown'}
                                                ]
                                    },
                '.ps_js-bank_input_segment': {
                                                triggers: 'blur',
                                                validate: [{
                                                            as          : vm.bank_account_info == 'remove' ?  
                                                                                                    'null' : 
                                                                                                    'required', 
                                                            type        : 'multiple',
                                                            visible_only: true
                                                        }]
                                            },
                '.ps_js-accountBankName': { triggers: 'blur', validate: [{
                                                                            as: vm.bank_account_info == 'remove' ?  
                                                                                                          'null' : 
                                                                                                          'required'
                                                                        }] 
                                        },
                '.ps_js-currency_select': { 
                                            triggers:'blur', 
                                            validate:[
                                                        {as:'required'},
                                                        {as:'currency_dropdown'}
                                                    ]
                                        },
                '.ps_js-promotion': { triggers: 'blur', validate: [{as: 'required'}] },
                '.ps_js-securityQuestion_select': { 
                                                    triggers: 'blur', 
                                                    validate: [
                                                                {as:'required'},
                                                                {as:'securityQuestion'}
                                                            ] 
                                                },
                '.ps_js-yourAnswer': { 
                                        triggers: 'blur', 
                                        prevent : true,
                                        validate: [
                                                    {as:'required'  , exclude_triggers: ['prevent']},
                                                    {as:'max_length', type: 'yourAnswer'}
                                                ] 
                                    },
                '.ps_js-captcha_input': { triggers: 'blur', validate: [{as:'required' }] }
            };
        },

        /**
         * This will initialize all view data needed for register module to cache it and save http requests
         * @param  function callback 
         * @return void
         */
        register_view_data: function(callback) {
            ps_model.view_data({ 
                success: callback 
            },['currency','bank_dropdown','bonus_new_member','securityQuestions', 'bank_account_config']);
        }
    };

    return {
    	/**
    	 * This will trigger on register page activation
    	 * @param  string hash 
    	 * @return void
    	 */
    	activate: function(hash) {
            var register_info_store = callables.register_info();

            // open modal and generate form
            var form_id = globals.form_id;
            globals.form_id++;
            
    		ps_popup.modal.open('register', {
                modal_class: 'register_root',
    			header: ps_language.get('language.account_registration'),
    			body  : function(modal_part) {
                            ps_view.render(modal_part, 'register', {
                                replace: false,
                                data   : {
                                            info      : callables.register_info(),
                                            view_data : {},
                                            form_id   : form_id,
                                            is_loading: true
                                        },
                                computed : {
                                            bank_account_config : function() {
                                                return this.view_data.bank_account_config || {};
                                            },
                                            bank_account_info : function() {
                                                return this.bank_account_config.bank_account_info || {};
                                            }
                                },
                                mounted: function() {   
                                            var vm = this;
                                            callables.register_view_data(function(response) {
                                                vm.view_data  = response;
                                                vm.is_loading = false;
                                                vm.$nextTick(function() {
                                                    var form = $(vm.$el).find('.ps_js-register_form');

                                                    // apply form validations
                                                    ps_validator.apply(form, {
                                                        validations: callables.register_validation(form, vm),
                                                        success    : function() { callables.register_submit(form); }
                                                    });
                                                });
                                            });
                                        }
                            });   
	    				},
                bind    : {
                            hide: function() {
                                    window.location = ps_model.active_main_hash();
                                },
                            shown: function() {
                                    ps_helper.ready('.ps_js-register_form .ps_js-register_first:visible', function() {
                                        $(this).trigger('focus');       
                                    }, $(this));
                                }
                        }
    		}, 'floating_page');
    	},

    	/**
    	 * This will trigger on register page deactivation
    	 * @return void
    	 */
    	deactivate: function(hash) {
    		ps_popup.modal.close('register', 'floating_page');
    	}
    };
});
