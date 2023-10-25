
/**
 * Accept Terms and Condition page
 * 
 * @author PS Team
 */
define('ps_change_credentials', ['ps_view', 'ps_model','ps_validator', 'ps_store'], function() {

    'use strict';

    var ps_view      = arguments[0];
    var ps_model     = arguments[1];
    var ps_validator = arguments[2];
    var ps_store     = arguments[3];

    var globals   = { is_page_renderend:false, form_id:0, store: new ps_store('ps_change_credentials') };
    var callables = {
        /**
         * Get/Create page store
         * @return object
         */
        page_info: function() {
            if (!globals.store.store_exists('info')) {
                globals.store.store_update('info', {
                    loading    : false,
                    request_num: 0
                });
            }

            return globals.store.store_fetch('info');
        },

        /**
         * validation rules
         * @param  dom     form
         * @param  object  view_data
         * @return object
         */
        validations: function(form, view_data) {
            var form_id               = form.attr('id');
            var password_dependencies = ['#'+form_id+' .ps_js-loginName'];
            var password_selector     = '#'+form_id+' .ps_js-password_field';

            return {
                '.ps_js-loginName'           : {
                                                triggers: 'blur',
                                                prevent : true,
                                                validate: [
                                                            {as: 'required',    exclude_triggers: ['prevent']},
                                                            {as: 'loginName',   exclude_triggers: ['prevent']},
                                                            {as: 'max_length',  type            : 'loginName'}
                                                        ]
                                            },
               '.ps_js-current_password'     : { 
                                                    triggers: 'blur', 
                                                    prevent : true,
                                                    validate: [
                                                                {
                                                                    as              : 'required',
                                                                    exclude_triggers: ['prevent']
                                                                },
                                                                {
                                                                    as              : 'max_length',
                                                                    type            : 'password'
                                                                }
                                                            ] 
                                                },
                '.ps_js-password_field'      : {
                                                triggers: 'blur focus',
                                                prevent : true,
                                                validate: [
                                                            {
                                                                as              : 'prerequisite',
                                                                fields          : password_dependencies,
                                                                is_focus        : true,
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
                                                                values          : [
                                                                                    view_data.user.firstName,
                                                                                    view_data.user.lastName
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
                                                                is_focus        :  true,
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
                                            }
            }
        },

        /**
         * This will initialize all view data needed for this page
         * @param  function callback 
         * @return void
         */
        view_data: function(callback) {
            ps_model.view_data({ success: callback },['user']);
        },

        /**
         * This will submit form
         * @param  dom form 
         * @return void
         */
        form_submit: function(form) {
            var info_store = globals.store.store_fetch('info');
            if (!info_store.loading) {
                var used_req_num = info_store.request_num + 1;

                globals.store.store_update('info', { 
                    loading    : true,
                    request_num: used_req_num
                });

                ps_model.change_credentials(ps_helper.json_serialize(form), {
                    ps_validator_form: form,
                    success: function() {
                        // check if this is still the request we're waiting
                        if (used_req_num == info_store.request_num) {
                            window.location = '';
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
            var page_info = callables.page_info();

            if (!globals.is_page_rendered) {

                globals.is_page_rendered = true;
                var form_id              = globals.form_id;
                globals.form_id++;

                ps_view.render($('.ps_js-page_'+hash_info.page), 'change_credentials', {
                    replace: false, 
                    data   : { 
                                hash_info: hash_info, 
                                page_info:page_info, 
                                view_data: {}, 
                                form_id  : form_id
                            },
                    mounted: function() {
                                var vm = this;

                                callables.view_data(function(response) {
                                    vm.view_data = response;
                                    ps_model.update_page_rendered(vm.hash_info.page);

                                    vm.$nextTick(function() {
                                        var form = $(vm.$el).find('.ps_js-change_credentials_form');

                                        // apply form validations
                                        ps_validator.apply(form, {
                                            validations: callables.validations(form, vm.view_data),
                                            success    : function() { 
                                                            callables.form_submit(form);
                                                        }
                                        });

                                        // buttons
                                        form.find('.ps_js-change_credentials_reset').on('click', function() {
                                            form.trigger('view_components_fullreset');
                                        });
                                    });
                                });

                            }
                });
            }
        }
    };
});
