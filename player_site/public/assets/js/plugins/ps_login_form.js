
/**
 * This will handle ps login form plugin
 * 
 * @author PS Team
 */
define('ps_login_form', [
    'ps_model',
    'ps_view', 
    'ps_helper', 
    'ps_popup', 
    'ps_language', 
    'ps_store',
    'ps_validator',
    'jquery',
    'ps_localstorage',
    'ps_window'
], function () {

    var ps_model        = arguments[0];
    var ps_view         = arguments[1];
    var ps_helper       = arguments[2];
    var ps_popup        = arguments[3];
    var ps_language     = arguments[4];
    var ps_store        = arguments[5];
    var ps_validator    = arguments[6];
    var $               = arguments[7];
    var ps_localstorage = arguments[8];
    var ps_window       = arguments[9];

    var globals   = { store: new ps_store('ps_login_form') };
    var callables = {
        /**
         * This will handle login form submission
         * @param  dom      form
         * @param  boolean  is_remember
         * @return void
         */
        login_form_submit: function(form, is_remember) {
            if (!globals.store.store_fetch('login_form').loading) {
                globals.store.store_update('login_form', { loading : true });
                ps_popup.toast.open(ps_language.get('messages.authenticating'), {
                    title: ps_language.get('language.login'),
                    type : 'lock'
                });

                ps_model.login(ps_helper.json_serialize(form), ps_window.id, {
                        success: function(response) {

                                    if (is_remember) {

                                        ps_localstorage.set('login_form_remember', {
                                            username: form.find('.ps_js-username').val(),
                                            password: form.find('.ps_js-password').val()
                                        }, true);

                                    } else {

                                        ps_localstorage.remove('login_form_remember');

                                    }

                                    window.location = '';
                                },

                        fail: function(response) {
                                if (response.has_captcha) {

                                    callables.login_captcha();

                                } else if (response.resend_email) {

                                    callables.resend_email_modal(response);

                                } 

                                callables.login_form_reset();
                            }
                });
            }
        },

        /**
         * This will get current login form store
         * @return object
         */
        login_form_info: function() {
            if (!globals.store.store_exists('login_form')) {
                callables.login_form_reset();
            } 

            return globals.store.store_fetch('login_form');
        },

        /**
         * reset login form
         * @return void
         */
        login_form_reset: function() {
            $('.ps_js-login_form').trigger('reset');

            globals.store.store_update('login_form', { 
                loading : false,
                remember: ps_localstorage.get('login_form_remember')
            });
        },

        /**
         * This will open resend email modal
         * @param  object response 
         * @return void
         */
        resend_email_modal: function(response) {
            ps_popup.toast.close();

            // resend email modal
            var error_message = ps_language.error(response.err_details.err_code);
            ps_popup.modal.open('resend_email', {
                modal_class : 'resend_email_root',
                header      : error_message.title,
                body        : error_message.content,
                footer      : function(modal_part) {
                                ps_view.render(modal_part,'resend_email_footer', {
                                    replace: false,
                                    mounted: function() {
                                                $(this.$el).find('[name=ps_js-resend_email]')
                                                        .on('click', function() {
                                                            ps_popup.modal.close('resend_email');

                                                            var resend_msg = ps_language.get('messages.sending_email');
                                                            ps_popup.toast.open(resend_msg, {
                                                                title: error_message.title,
                                                                type : 'mail'
                                                            });

                                                            ps_model.resend_verification_email();
                                                        });
                                            },
                                });
                            }
            });
        },

        /**
         * Login captcha validation object
         * @return object
         */
        login_captcha_validation: function() {
            return {
                '.ps_js-captcha_input': { 
                                            validate:[{as:'required', type:'captcha'}] 
                                        }
            };
        },

        /**
         * This will open captcha modal for login
         * @return void
         */
        login_captcha: function() {
            ps_popup.modal.open('login_captcha', {
                header      : ps_language.get('language.warning'),
                body        : function(modal_part) {
                                ps_view.render(modal_part,'login_captcha', {
                                    replace      : false,
                                    data         : { info: callables.login_captcha_info() },
                                    mounted      : function() {
                                                    var vm   = this;
                                                    var form = $(vm.$el).find('.ps_js-login_captcha_form');

                                                    // submit after validation
                                                    ps_validator.apply(form, {
                                                        success    : function() { 
                                                                        callables.login_captcha_submit(form); 
                                                                    },
                                                        validations: callables.login_captcha_validation()
                                                    });
                                                }
                                });
                            },
                footer      : function(modal_part) {
                                ps_view.render(modal_part,'login_captcha_footer', {
                                    replace: false,
                                    data   : { info: callables.login_captcha_info() },
                                    mounted: function() {
                                                $(this.$el).find('[name=ps_js-login_captcha_submit]')
                                                        .on('click', function() {
                                                            $('.ps_js-login_captcha_form').submit();
                                                        });
                                            },
                                });
                            },
                modal_class : 'login_captcha_root',
                bind        : {
                                hide: function() {
                                        ps_popup.toast.close();
                                    }
                            },
                onrender    : function() {
                                ps_helper.ready('.ps_js-login_captcha_root .ps_js-captcha_input', function() {
                                    $(this).filter(':visible').trigger('focus');       
                                });
                            },
                closable    : false
            });
        },

        /**
         * This will submit login captcha form
         * @param  dom form 
         * @return void
         */
        login_captcha_submit: function(form) {
            if (!globals.store.store_fetch('login_captcha').loading) {
                globals.store.store_update('login_captcha', { loading : true });
                ps_model.login_captcha_submit(ps_helper.json_serialize(form), {
                    success : function() {
                                ps_popup.modal.close('login_captcha');
                            },
                    complete: function() {
                                callables.login_captcha_reset();
                            }
                });
            }
        },

        /**
         * This will get current login captcha store
         * @return object
         */
        login_captcha_info: function() {
            if (!globals.store.store_exists('login_captcha')) {
                callables.login_captcha_reset();
            } 

            return globals.store.store_fetch('login_captcha');
        },

        /**
         * reset login captcha form
         * @return void
         */
        login_captcha_reset: function() {
            $('.ps_js-login_captcha_form').trigger('view_components_fullreset');

            globals.store.store_update('login_captcha', { 
                loading : false
            });

            $('.ps_js-login_captcha_form input:visible').first().trigger('focus');   
        }
    };

    return {
        /**
         * Login form main custom tag
         * @return void
         */
        login_form_main: function() {
            return {
                data    : { info: callables.login_form_info() },
                computed: {
                            remember: function() {
                                        var vm = this;
                                        if ($.isPlainObject(vm.info.remember)) {

                                            return {
                                                enabled    : true,
                                                credentials: vm.info.remember
                                            };

                                        } else {

                                            return {
                                                enabled    : false,
                                                credentials: { username: '', password:'' }
                                            };

                                        }
                                    }
                        },  
                props   : ['rememberCheckbox'],
                mounted : function() {
                            var vm   = this;

                            ps_helper.ready('.ps_js-login_form', function() {
                                var form = $(vm.$el).find('.ps_js-login_form');
                                
                                $(vm.$el).find('input').on('focus', function() {
                                    ps_popup.toast.close();
                                });

                                setTimeout(function() {
                                    $('input').first().focus();
                                }, 1);

                                var remember_me_checkbox = $(vm.$el).find('.ps_js-remember_me_checkbox .ps_js-checkbox');
                                // submit after validation
                                ps_validator.apply(form, {
                                    success           : function() { 
                                                            callables.login_form_submit(
                                                                form,
                                                                remember_me_checkbox.is(':checked')
                                                            ); 
                                                        },
                                    terminate_on_error: true,
                                    validations       : {
                                                            '.ps_js-username': { 
                                                                                    validate:[{
                                                                                        as  :'required', 
                                                                                        type:'login'
                                                                                    }] 
                                                                                },
                                                            '.ps_js-password': { 
                                                                                    validate:[{
                                                                                        as  :'required', 
                                                                                        type:'login'
                                                                                    }] 
                                                                                },
                                                        }
                                });

                            }, $(vm.$el));
                        }
            }
        }
    };
});
