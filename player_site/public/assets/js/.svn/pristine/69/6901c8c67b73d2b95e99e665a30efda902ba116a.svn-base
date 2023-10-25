
/**
 * This will handle ps language plugin
 * @author PS Team
 */
define('ps_language', ['ps_model', 'ps_helper', 'jquery', 'ps_window'], function () {

    var ps_model  = arguments[0];
    var ps_helper = arguments[1];
    var $         = arguments[2];
    var ps_window = arguments[3];

    var globals = { 
                    // default messages
                    messages : {

                                error      : {
                                                title  : 'Error',
                                                content: 'Something went wrong with your request.' 
                                                        +' Please refresh this page or try again later.',
                                            },

                                ERR_00093  : {
                                                title  : 'Network error',
                                                content: 'Please refresh this page or try again later.' 
                                            },

                                '-1'       : {
                                                title  : 'Processing',
                                                content: 'Refreshing page...'
                                            },

                                '-2'       : {
                                                title  : 'Processing',
                                                content: 'Redirecting page...'
                                            },

                                '-3'       : {
                                                title  : 'Session Timeout',
                                                content: 'Your session has been expired. Please re-login to continue.'
                                            }

                            },

                    net_err_code    : 'ERR_00093',
                    running_err_code: 'ERR_00075',
                    unknown_err_code: 'ERR_00001',
                    ps_model_lang   : {}
                };

    var callables = {
        /**
         * This will get lang store object from model
         * NOTE: this will only get the current state of lang store from model and will not invoke ajax
         * @return void
         */
        lang_store: function() {
            if (ps_helper.empty(globals.ps_model_lang)) {
                globals.ps_model_lang = ps_model.lang_store() || {};
            }
        },

        /**
         * This will get text base from language key given
         * @param  string key 
         * @return string
         */
        get_handler: function(key) {
            if ($.isPlainObject(globals.ps_model_lang)) {
                var text = ps_helper.get_property(globals.ps_model_lang, key);

                if (!ps_helper.empty(text)) {
                    return text;
                }
            }

            return key;
        },

        /**
         * This will check if language key exists
         * @param  string key 
         * @return string
         */
        exists_handler: function(key) {
            if ($.isPlainObject(globals.ps_model_lang)) {
                var text = ps_helper.get_property(globals.ps_model_lang, key);

                return (!ps_helper.empty(text));
                
            } else {
                return false;
            }
        },

        /**
         * Get error message base on err_code
         * @param  string err_code 
         * @return string
         */
        error_handler: function(err_code) {
            if (!ps_helper.empty(globals.ps_model_lang)) {
                var lang_file_message = globals.ps_model_lang.error[err_code]
            } else {
                var lang_file_message = null;
            }

            var global_default_message = ps_helper.get_property(globals.messages, err_code) || globals.messages.error;

            // from lang file
            if (ps_helper.empty(lang_file_message)) {

                return global_default_message;

            } else {

                if ($.isArray(lang_file_message)) {

                    if (lang_file_message.length > 1) {
                        return { title:lang_file_message[0], content: lang_file_message[1] };
                    } else {
                        return { title:global_default_message[0], content: lang_file_message[0] };
                    }

                } else {

                    return { title:global_default_message[0], content: lang_file_message };

                }
            }
        },

        /**
         * This will get default notice text
         * @return string
         */
        get_default_notice: function() {
            return callables.get('error.ERR_00094');
        },

        /**
         * callables.error wrapper
         * @param  string key 
         * @return string
         */
        error: function(err_code) {
            callables.lang_store();
            return callables.error_handler.apply(this, arguments);
        },

    };

    return {
        get_default_notice: callables.get_default_notice,
        image_crop_error   : 'ERR_00096',
        media_webcam_error : 'ERR_00098',
        popup_blocked_error: 'ERR_00102',
        net_err_code       : globals.net_err_code,
        unknown_err_code   : globals.unknown_err_code,
        error              : callables.error,

        /**
         * Gets the error
         * @return {Boolean} [description]
         */
        has_running_error: function() {
            return callables.error(globals.running_err_code);
        },

        /**
         * callables.get wrapper
         * @param  string key 
         * @return string
         */
        get: function(key) {
            callables.lang_store();
            return callables.get_handler.apply(this, arguments);
        },

        /**
         * callables.exists wrapper
         * @param  string key 
         * @return string
         */
        exists: function(key) {
            callables.lang_store();
            return callables.exists_handler.apply(this, arguments);
        },

        /**
         * language selector custom tag
         * @return void
         */
        language_selector: function() {
            return {
                data    : function() {
                            return { language: { list:[] }, is_loading: true, is_selecting: false };
                        },
                computed: {
                            active: function() {
                                        var languages_length = this.language.list.length;

                                        for (var i = 0; i < languages_length; i++) {
                                            var language_obj = this.language.list[i];
                                            if (language_obj.is_active) {
                                                return language_obj;
                                            }
                                        }

                                        return {};
                                    }
                        },
                mounted : function() {
                            var vm = this;

                            $(vm.$el).on('click','.ps_js-language_item', function() {
                                var language    = $(this).attr('data-language');
                                vm.is_selecting = true;
                                ps_model.set_language(language, ps_window.id, {
                                    success: function() {
                                                window.location = '';
                                                vm.is_selecting = false;
                                            }
                                });
                            });

                            ps_model.view_data({
                                success: function(response) { 
                                            vm.language   = response.lang_config;
                                            vm.is_loading = false;
                                        }
                            }, ['lang_config']);
                        }
            };
        }
    }
});
