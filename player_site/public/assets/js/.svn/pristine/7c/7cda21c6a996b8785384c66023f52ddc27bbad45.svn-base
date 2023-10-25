/**
 * This will handle ps login form plugin
 * 
 * @author PS Team
 */
define('ps_validator', ['jquery', 'ps_helper', 'ps_model', 'ps_popup', 'ps_language','ps_view'], function () {

	'use strict';

    var $            = arguments[0];
    var ps_helper    = arguments[1];
    var ps_model     = arguments[2];
    var ps_popup     = arguments[3];
    var ps_language  = arguments[4];
    var ps_view      = arguments[5];

	var globals   = {configs: {}, form_ajax_subscribed: false};
	var callables = {
	    /**
	     |--------------------------------------------------------------------------------------------------------------
	     | List of validations
	     |--------------------------------------------------------------------------------------------------------------
	     */
		validations: {
            /**
             * check if value has no symbol
             * @param  string $value 
             * @return bool        
             */
            no_symbol: function(value) {
                if(ps_helper.has_no_symbol(value)) {
                    return { result: false, err_code: 'ERR_00109' }; 
                }
                return { result: true };
            },

            /**
             * this will checkk if string has no integer value
             * @param  string value 
             * @return bool        
             */
            no_number: function(value) {
                if(ps_helper.has_no_number(value)) {
                    return { result: false, err_code: 'ERR_00109' }; 
                }
                return { result: true };
            },

            /**
             * Check if value is not empty
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
			required: function(value, val_object) {
				if (ps_helper.empty(value)) {
					switch (val_object.type) {
						case 'login'   : return { result: false, err_code: 'ERR_00038' }; 
                        case 'multiple': return { result: false, err_code: 'ERR_00068' }; 
                        case 'captcha' : return { result: false, err_code: 'ERR_00065' }; 
						default        : return { result: false, err_code: 'ERR_00002' }; 
					}
				} else {
					return { result: true }; 
				}
			},

            /**
             * Check if value is an email
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            email: function(value, val_object) {
                if (!ps_helper.is_email(value)) {
                    return { result: false, err_code: 'ERR_00013' }; 
                } else {
                    return { result: true }; 
                }
            },

            /**
             * Check if value letters and space only
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            alphabetic: function(value, val_object) {
                if (!ps_helper.is_alpha_space(value)) {
                    return { result: false, err_code: 'ERR_00004' }; 
                } else {
                    return { result: true }; 
                }
            },

            /**
             * Check if value is valid loginName
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            loginName: function(value, val_object) {
                // login name length
                var length_config = globals.configs.configs.loginName_length;
                if (!ps_helper.is_between(value.length,length_config.min,length_config.max)) {
                    return { result: false, err_code: 'ERR_00006' }; 
                } 

                // login name character composition
                if (!ps_helper.is_alpha_num(value)) {
                    return { result: false, err_code: 'ERR_00006' }; 
                }

                // login name first character
                if (!ps_helper.is_alpha_only(value.substring(0, 1))) {
                    return { result: false, err_code: 'ERR_00006' }; 
                }
                
                // login name must have mixed letters and numbers
                if (!ps_helper.mixed_alpha_num(value)) {
                    return { result: false, err_code: 'ERR_00006' }; 
                }

                return { result: true }; 
            },

            /**
             * Check if all prerequisite fields is not empty
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            prerequisite: function(value, val_object) {
                var fields = $(val_object.fields.join(','));

                if (fields.length > 0) {
                    var empty_field = null;
                    fields.each(function(){
                        if (ps_helper.empty($(this).val())) {
                            empty_field = $(this);
                            return false;
                        }
                    });

                    if (empty_field!==null) {
                        return { result: false, err_code: 'ERR_00003', field:empty_field}; 
                    }
                }
                return { result: true }; 
            },

            /**
             * Check if value contains one of the other fields value
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            not_contain: function(value, val_object) {
                var has_match = false;

                if ($.isArray(val_object.fields)) {
                    var fields = $(val_object.fields.join(','));
                    fields.each(function(){
                        if (ps_helper.is_contain($(this).val(), value)) {
                            has_match = true;
                            return false;
                        }
                    });
                }

                if (has_match === false && $.isArray(val_object.values)) {
                    var values_length = val_object.values.length;
                    for (var i=0; i<values_length; i++) {
                        if (ps_helper.is_contain(val_object.values[i], value)) {
                            has_match = true;
                            break;
                        }
                    }
                }

                if (has_match) {
                    switch (val_object.type) {
                        case 'password': return { result: false, err_code: 'ERR_00010' };
                        default        : return { result: false, err_code: 'ERR_00001' };
                    }
                } else {
                    return { result: true }; 
                }
            },

            /**
             * Check if value is within max length
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            max_length: function(value, val_object) {

                switch (val_object.type) {
                    case 'mobile'     : var max_length = 20;   break;
                    case 'password'   : var max_length = 15;   break;
                    case 'yourAnswer' : var max_length = 100;  break;
                    case 'loginName'  : var max_length = globals.configs.configs.loginName_length.max; break;
                    case 'displayName': var max_length = globals.configs.configs.displayName_length.max; break;
                    default           : var max_length = false;
                }

                if (max_length!==false && value.length > max_length) {
                    return { result: false };
                } else {
                    return { result: true }; 
                }
            },

            /**
             * Check if value is within min length
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            min_length: function(value, val_object) {
                switch (val_object.type) {
                    case 'mobile'   : var min_length = 5;     break;
                    case 'password' : var min_length = 8;     break;
                    default         : var min_length = false;
                }

                if (min_length!==false && value.length < min_length) {
                    switch (val_object.type) {
                        case 'mobile'   : return { result: false,  err_code: 'ERR_00017'};
                        case 'password' : return { result: false,  err_code: 'ERR_00010'};
                        default         : return { result: false };
                    }
                } else {
                    return { result: true }; 
                }
            },

            /**
             * Check if value is equal to related field
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            same: function(value, val_object) {
                var same_value = $(val_object.field).val();
                if (value !== same_value) {
                    switch (val_object.type) {
                        case 'password': return { result: false, err_code: 'ERR_00011' };
                        case 'email'   : return { result: false, err_code: 'ERR_00016' };
                        default        : return { result: false, err_code: 'ERR_00001' };
                    }
                } else {
                    return { result: true }; 
                }
            },

            /**
             * Check if value has letters, numbers and symbols
             * @param  string value    
             * @return object
             */
            alpha_num_symbol: function(value) {
                return !ps_helper.alpha_num_symbol(value) ? { result: false, err_code: 'ERR_00010' } : { result: true };
            },

            /**
             * Check if value is number only
             * @param  string value    
             * @param  object val_object 
             * @return object
             */
            mobile_number: function(value, val_object) {
                if (ps_helper.is_number_only(value)) {
                    return { result: true }; 
                } else {
                    return { result: false}; 
                }
            },

            /**
             * Check if value is in bank dropdown
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            bank_dropdown: function(value, val_object) {
                if (globals.configs.bank_dropdown.hasOwnProperty(value)) {
                    return { result: true }; 
                } else {
                    return { result: false, err_code: 'ERR_00080' };
                }
            },

            /**
             * Check if value is in currency dropdown
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            currency_dropdown: function(value, val_object) {
                if (globals.configs.currency.enabled.hasOwnProperty(value)) {
                    return { result: true }; 
                } else {
                    return { result: false, err_code: 'ERR_00024' };
                }
            },

            /**
             * Check if value is in securityQuestion dropdown
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            securityQuestion: function(value, val_object) {
                if (ps_helper.in_array(value, globals.configs.securityQuestions.list)) {
                    return { result: true }; 
                } else {
                    return { result: false, err_code: 'ERR_00026' };
                }
            },

            /**
             * This will check if avatar imgOrder is correct
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            imgOrder: function(value, val_object) {
                if (value >= 1 && value <= globals.configs.configs.avatar.max_count) {
                    return { result: true }; 
                } else {
                    return { result: false, err_code: 'ERR_00001' };
                }
            },

            /**
             * This will check if value is valid displayName
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            displayName: function(value, val_object) {
                var length_config = globals.configs.configs.displayName_length;

                // display name length
                if (!ps_helper.is_between(value.length,length_config.min,length_config.max)) {
                    return { result: false }; 
                }

                // cannot contain username and loginName
                if (ps_helper.is_contain(globals.configs.user.username, value)) {
                    return { result: false }; 
                }

                if (ps_helper.is_contain(globals.configs.user.loginName, value)) {
                    return { result: false }; 
                }
                
                return { result: true }; 
            },

            /**
             * This will check if value is alpha numeric only
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            alpha_num: function(value, val_object) {
                if (!ps_helper.is_alpha_num(value)) {
                    switch (val_object.type) {
                        case 'displayName': return { result: false };
                        default           : return { result: false, err_code: 'ERR_00001' };
                    } 
                } else {
                    return true;
                }
            },

            /**
             * This will check if value is numeric without the money format, and digits after decimal is not allowed
             * @param  string value    
             * @param  object val_object
             * @return object
             */
            money: function(value, val_object) {

                val_object.decimal_delimeter  = val_object.decimal_delimeter  || '.';
                val_object.thousand_delimeter = val_object.thousand_delimeter || ',';
                val_object.digits             = val_object.digits || 7;
                var to_number                 = ps_helper.replace_all(value,  val_object.decimal_delimeter, '');
                to_number                     = ps_helper.replace_all(value,  val_object.thousand_delimeter, '');

                // no decimal
                if (ps_helper.is_contain(val_object.decimal_delimeter, value)) {
                    return { result: false };
                }

                // must be numbers only
                if (!ps_helper.is_number_only(to_number)) {
                    return { result: false };
                }

                // must be fixed digits count only
                if (to_number.length > val_object.digits) {
                    return { result: false };
                }

                //no 0 number on first digit
                if(value.charAt(0) == 0) {
                    return { result: false };
                }

                return { result: true };
            },

            /**
             * check if value has no space
             * @param  string $value 
             * @return bool        
             */
            no_space: function(value) {
                console.log(ps_helper.has_no_space(value));
                if(!ps_helper.has_no_space(value)) {
                    return { result: false, err_code: 'ERR_00010' }; 
                }
                return { result: true };
            },

            /**
             * check if value is alphabet of nay language
             * @param  string $value 
             * @return bool        
             */
            alpha_language : function(value) {

                if(!ps_helper.alpha_language(value)) {
                    return { result: false, err_code: 'ERR_00109' }; 
                }
                return { result: true };
            },
		},

        /**
         * this will decide wether to continue specific validation on a field
         * @param  dom     field 
         * @param  object  val_object [description]
         * @param  string  event_type [description]
         * @return boolean
         */
        is_continue_validation: function(field, val_object, event_type) {
            if (val_object.as === null) {
                return false;
            }

            if (val_object.visible_only && !field.is(':visible')) {
                return false;
            }

            if (ps_helper.in_array(event_type, val_object.exclude_triggers)) {
                return false;
            }

            return true;
        },

        /**
         * This will perform validation on fields
         * @param  dom     fields 
         * @param  boolean terminate_on_error 
         * @param  string   event_type 
         * @return object
         */
        validate: function(fields, terminate_on_error, event_type) {
            var error = [];
            fields.each(function() {
                var validate = $(this).data('validate');
                var value    = $(this).val();

                if ($.isArray(validate)) {
                    var validate_length = validate.length;

                    for (var ctr = 0; ctr < validate_length; ctr++) {

                        var val_object = validate[ctr];
                        if (callables.is_continue_validation($(this), val_object, event_type)) {
                            var execute_validation = callables.validations[val_object.as](value, val_object);
                            if (execute_validation.result === false) {
                                // set current element as default field
                                execute_validation.field   = execute_validation.field || $(this);
                                execute_validation.details = val_object;
                                error.push(execute_validation);
                                break;
                            }

                        } 
                    }
                }

                // break on error
                if (terminate_on_error && error.length > 0) {
                    return false;
                }
            });

            if (error.length > 0) {
                return {result: false, error: error};
            } else {
                return {result: true};
            }
        },

        /**
         * This will add new config item to globals.configs if not yet existing
         * @param  string   key 
         * @param  mixed    value 
         * @return void
         */
        new_config: function(key, value) {
            if (!globals.configs.hasOwnProperty(key)) {
                globals.configs[key] = value;
            }
        },

        /**
         * This will load deffered data first then execute callback
         * NTE: not only view_data can be loaded here, all dependency should be loaded here
         * @param  object    validation_setup 
         * @param  function  callback 
         * @return boolean
         */
        load_requirements: function(validation_setup, callback) {

            // subscribe to ajax fail for error handling
            if (!globals.form_ajax_subscribed) {
                globals.form_ajax_subscribed = true;
                ps_model.subscribe('fail', function(response, status, jqXHR, ajax_setup) {
                    if (ajax_setup.hasOwnProperty('ps_validator_form')) {
                        ajax_setup.ps_validator_form.trigger('validator_display_errors', response);
                    }
                });
            }

            /**
             |----------------------------------------------------------------------------------------------------------
             | view_data requirements
             |----------------------------------------------------------------------------------------------------------
             | Load other dependencies above this notice
             */
            var view_datas     = [];

            var view_data_push = function(items) {
                items.forEach(function(item) {
                    if (!ps_helper.in_array(item, view_datas)) {
                        view_datas.push(item);
                    }
                });
            }

            $.each(validation_setup.validations, function(field, validation) {
                validation.validate.forEach(function(val_object) {
                    switch (val_object.as) {
                        case 'bank_dropdown'    : view_data_push(['bank_dropdown']);     break;
                        case 'imgOrder'         : view_data_push(['configs']);           break;
                        case 'displayName'      : view_data_push(['user']);  
                        case 'max_length'       :
                        case 'loginName'        : view_data_push(['configs']);           break;
                        case 'currency_dropdown': view_data_push(['currency']);          break;
                        case 'securityQuestion' : view_data_push(['securityQuestions']); break;
                    }
                });
            });

            if (view_datas.length > 0) {
                ps_model.view_data({
                    success: function(response) {
                                $.each(response, function(key, value) {
                                    callables.new_config(key, value);
                                });
                                callback();
                            }
                }, view_datas);
            } else {
                callback();
            }
        },

        /**
         * This will get error container of an input if there's any
         * @param  string input 
         * @return dom/void
         */
        form_error_container: function(input) {
            var input_container = input.closest('.ps_js-input_wrap');

            if (input_container.length > 0) {
                var error_container = input_container.find('.ps_js-input_error_container');
                if (error_container.length > 0) {
                    return error_container;
                }
            }
        },

        /**
         * This will handle input error display
         * @param  string input 
         * @param  dom    form    Uses form.find(field_name) combination if result is an object from backend
         * @return dom/void
         */
        form_handle_errors: function(result, form) {
            var displaying_handler = function(error_obj) {
                                        error_obj.field.addClass('ps_js-input_error');

                                        if (error_obj.hasOwnProperty('err_code')) {
                                            var error_container = callables.form_error_container(error_obj.field);
                                            var message = ps_language.error(error_obj.err_code);
                                            if (ps_helper.empty(error_container)) {

                                                ps_popup.toast.open(message.content, {
                                                    title: message.title,
                                                    auto : true
                                                });

                                            } else {

                                                error_container.addClass('ps_js-form_error').html(message.content);
                                                
                                            }   

                                            if ($.isPlainObject(error_obj.details) && error_obj.details.is_focus) {
                                                error_obj.field.trigger('focus');   
                                            } 
                                        }
                                        
                                        var element = $('.ps_js-render').removeClass();
                                        for (var i = 0; i < element.length; i++) {

                                            switch($(element[i]).data('type')) {
                                                case 'resend': 

                                                    ps_view.render($(element[i]), 'resend_link',{
                                                        mounted: function(){
                                                            var vm = this; 

                                                            $(vm.$el).on('click', function () {
                                                                var resend_msg = ps_language.get('messages.sending_email');

                                                                ps_popup.toast.open(resend_msg, {
                                                                    title: message.title,
                                                                    type : 'mail'
                                                                });

                                                                ps_model.resend_verification_email();
                                                            });

                                                        }
                                                    });
                                                    break;

                                            }
                                        }
                                    };

            if ($.isArray(result.error)) {

                result.error.forEach(displaying_handler);

            } else if ($.isPlainObject(result.error)) {

                for (var field_name in result.error) {
                    displaying_handler({
                        field   : form.find('[name='+field_name+']'),
                        err_code: result.error[field_name],  
                    });
                }

            }
        },

        /**
         * Remove errors in inputs
         * @param  string inputs 
         * @return dom/void
         */
        form_remove_errors: function(inputs) {
            inputs.each(function() {
                $(this).removeClass('ps_js-input_error');
                var error_container = callables.form_error_container($(this));
                if (!ps_helper.empty(error_container)) {
                    error_container.removeClass('ps_js-form_error');
                }
            });
        }
	};

	return {
        /**
         * Thisw ill apply validation to a form
         * @param  dom    form    
         * @param  object validation_setup
         * @return void
         */
        apply: function(form, validation_setup) {
            callables.load_requirements(validation_setup, function() {
                var validation_fields  = $([]);

                for (var field_selector in validation_setup.validations) {
                    var cur_validation = validation_setup.validations[field_selector];
                    var field          = form.find(field_selector).data('validate', cur_validation.validate);

                    // all fields to be validated
                    validation_fields = validation_fields.add(field);
                    
                    // validation on assigned triggers
                    if (!ps_helper.empty(cur_validation.triggers)) {
                        field.on(cur_validation.triggers, function(event) {
                            var validation = callables.validate($(this), false, event.type);
                            callables.form_handle_errors(validation,form);
                        });
                
                        // remove errors on focus
                        field.on('keydown change', function() {
                            callables.form_remove_errors($(this));
                        });
                    }

                    // prevent invalid values by reverting to old value
                    if (cur_validation.prevent) {
                        // prevent invalid values
                        field.on('keydown', function(e) {
                            if (!ps_helper.empty($(this).val())) {    
                                var validation = callables.validate($(this), false, 'prevent');
                                if (validation.result) {
                                    $(this).data('last_valid_value',$(this).val());
                                } else {
                                    e.preventDefault();
                                }
                            } else {
                                $(this).data('last_valid_value','');
                            }
                        }); 
                        field.on('keyup input', function(e) {
                            var validation = callables.validate($(this), false, 'prevent');
                            if (!ps_helper.empty($(this).val()) && !validation.result) {
                                $(this).val($(this).data('last_valid_value'));
                            }
                        });
                    }

                }

                // remove errors on reset
                form.on('reset',  function() {
                    validation_fields.each(function() {
                        callables.form_remove_errors($(this));
                    });
                });

                // create event to display form error
                form.on('validator_display_errors', function(e, validation) {
                    callables.form_handle_errors(validation,form);

                    form.trigger('validation_failed', validation);

                    if ($.isFunction(validation_setup.failed)) {
                        validation_setup.failed.call(this, validation);
                    }

                    if ($(this).data('focusError')!==false) {
                        var error_input = $(this).find('.ps_js-input_error').first();
                        if (error_input.length > 0) {
                            error_input.trigger('focus');   
                        }
                    }
                });

                // validate all on form submit
                form.on('submit', function(e) {
                    e.preventDefault();
                    var validation = callables.validate(validation_fields,validation_setup.terminate_on_error,'submit');
                    if (validation.result) {
                        form.trigger('validation_success', validation);
                        
                        if ($.isFunction(validation_setup.success)) {
                            validation_setup.success.call(this);
                        }
                    } else {

                        form.trigger('validator_display_errors', validation);
                    }
                });
            });
        }
	};
});