/**
 * PS displayname modal handler
 *
 * Author PS Team
 */
define('ps_displayname', [
    'jquery',
    'ps_helper',
    'ps_popup',
    'ps_language',
    'ps_view',
    'ps_validator',
    'ps_model',
    'ps_store'
], function() {
	$             = arguments[0];
	ps_helper     = arguments[1];
    ps_popup      = arguments[2];
    ps_language   = arguments[3];
    ps_view       = arguments[4];
    ps_validator  = arguments[5];
    ps_model      = arguments[6];
    ps_store      = arguments[7];

	var globals   = { debug: true, store: new ps_store('ps_displayname'), onset_hide_function: null };
	var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * Fetch or create displayName modal store
         * @return object
         */
        modal_store: function() {
            if (!globals.store.store_exists('modal_info')) {
                globals.store.store_update('modal_info', {
                    status : 'start',
                    loading: false,
                    isset  : false
                });
            } 

            return globals.store.store_fetch('modal_info');
        },


        /**
         * This will initiate newly mounted displayname modal
         * @return void
         */
        modal_init: function() {
            var vm    = this;
            var form  = $(vm.$el).find('.ps_js-displayname_form');
            var input = form.find('.ps_js-displayname_input');
            ps_validator.apply(form, {
                validations: {
                                '.ps_js-displayname_input': {
                                                            triggers: 'blur change keyup paste',
                                                            prevent : true,
                                                            validate: [
                                                                        {
                                                                            as              :'displayName',
                                                                            exclude_triggers:['prevent']
                                                                        },
                                                                        {
                                                                            as  :'alpha_num',
                                                                            type:'displayName'
                                                                        },
                                                                        {
                                                                            as  :'max_length',
                                                                            type:'displayName'
                                                                        }
                                                                    ]
                                                        }
                            },
                success: function() {
                            callables.set(form);
                        },
                failed : function() {
                            globals.store.store_update('modal_info', { status: 'error'});
                        }
            });
            
            form.on('reset', function() {
                globals.store.store_update('modal_info', { status: 'start'});
            });
            
            // perform the DB checking
            form.find('.ps_js-displayname_input').on('blur change keyup paste', function() {
                var element = $(this);
                ps_helper.event_delay(function() {
                    if (ps_helper.empty(element.val())) {
                        globals.store.store_update('modal_info', { status: 'start'});
                    } else {
                        if (element.hasClass('ps_js-input_error')) {
                            globals.store.store_update('modal_info', { status: 'error'});
                        } else {
                            // confirm to backend if really success
                            ps_model.validate_displayName(element.val(), {
                                success: function() {
                                            globals.store.store_update('modal_info', { status: 'success'});
                                        },
                                fail   : function() {
                                            globals.store.store_update('modal_info', { status: 'error'});
                                        }
                            });
                        }
                    }
                });
            });
        },

        /**
         * This will bind events t displayName modal buttons
         * Triggered in modal footer mounted event
         * @return void
         */
        modal_button_events: function() {
            var vm = this;
            $(vm.$el).find('.ps_js-displayname_ignore').on('click', function() {
                callables.ignore();
            });

            $(vm.$el).find('.ps_js-displayname_submit').on('click', function() {
                if (!vm.disable_submit) {
                    $(this).closest('.ps_js-modal_displayname').find('.ps_js-displayname_form').trigger('submit');
                }
            });
        },

        /**
         * Modal footer computed properties
         * @return void
         */
        modal_footer_computed: function() {
            return {
                disable_submit: function() {
                                    return this.info.status!='success' || this.info.loading;
                                },
                can_ignore    : function() {
                                    return this.view_data.user.displayNameStatus == 0;
                                }                                               
            };
        },

        /**
         * Ignore button handler
         * @return void
         */
        ignore: function() {
            globals.store.store_update('modal_info', { loading: true });
            ps_model.generate_displayName({
                success: function() {
                            globals.store.store_update('modal_info', {
                                isset: true
                            });

                            ps_popup.modal.close('displayname');
                        },
                complete: function() {
                            globals.store.store_update('modal_info', { loading: false });
                        }   
            });
        },

        /**
         * Submit button handler
         * @param  dom   form form where the displayName was inputted
         * @return void
         */
        set: function(form) {
            globals.store.store_update('modal_info', { loading: true });
            ps_model.change_displayName(ps_helper.json_serialize(form), {
                success: function() {
                            globals.store.store_update('modal_info', {
                                isset: true
                            });

                            ps_popup.modal.close('displayname');

                        },
                complete: function() {
                            globals.store.store_update('modal_info', { loading: false });
                        }   
            });
        }
    };

	return {
        /**
         * This will open display name setter modal
         * @param  function onset_hide_function  This callback function will trigger if modal was hidden 
         *                                       and displayName was set previously, if display name was not set
         *                                       this callback will be discarded
         * @return void
         */
        open: function(onset_hide_function) {
            var modal_store             = callables.modal_store();
            globals.onset_hide_function = onset_hide_function;

            ps_popup.modal.open('displayname', {
                modal_class: 'displayname_modal_root',
                header: ps_language.get('language.set_display_name'),
                body  : function(modal_part) {
                            ps_view.render(modal_part,'displayname_modal', {
                                replace : false,
                                data    : modal_store,
                                mounted : callables.modal_init
                            });
                        },
                footer: function(modal_part) {
                            ps_model.view_data({
                                success: function(response) {
                                            ps_view.render(modal_part,'displayname_modal_footer', {
                                                data    : { info: modal_store, view_data: response },
                                                computed: callables.modal_footer_computed(),
                                                replace: false,
                                                mounted: callables.modal_button_events
                                            });
                                        }
                            },['user']);
                        },
                bind  : {
                            shown: function() {
                                    ps_helper.ready('.ps_js-displayname_form', function() {
                                        $(this).trigger('view_components_fullreset');       
                                    }, $(this));

                                    ps_helper.ready('.ps_js-displayname_input:visible', function() {
                                        $(this).trigger('focus');       
                                    }, $(this));
                                },
                            hide: function () {
                                    // if modal is hiding and displayName was set 
                                    if (modal_store.isset && $.isFunction(globals.onset_hide_function)) {
                                        globals.onset_hide_function();
                                    }

                                    globals.onset_hide_function = null;
                                }
                        }
            });
        }
	};
});