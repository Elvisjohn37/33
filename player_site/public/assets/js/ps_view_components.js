/**
 * Renders small details with special function to our site and needed consistency on its UI.
 * This module is an extension of ps_view for template rendering
 * NOTE: 1. Be carefull loading component to another component.
 *       2. All items added to ajax, vue, etc. that is used only for this module should have 'psc_',
 *          This is to avoid conflict when we accept any setup outside this module.
 *       
 * @author PS Team
 */

define('ps_view_components', [
    'jquery',
    'ps_helper',
    'ps_model',
    'ps_view',
    'ps_store',
    'ps_language',
    'ps_popup',
    'ps_date',
    'ps_window'
], function() {

    'use strict';

    var $            = arguments[0];
    var ps_helper    = arguments[1];
    var ps_model     = arguments[2];
    var ps_view      = arguments[3];
    var ps_store     = arguments[4];
    var ps_language  = arguments[5];
    var ps_popup     = arguments[6];
    var ps_date      = arguments[7];
    var ps_window    = arguments[8];

    var globals   = { 
                        debug: false, 

                        // custom data for all same components
                        store            : new ps_store('ps_view_components', {
                                            refresh_balance: { is_refreshing: false } 
                                        }),
                        update_captcha_Ws: false
                    };
    var callables = {

        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will get balance from model and update the refresh balance button states
         * @return void
         */
        refresh_balance: function() {
            globals.store.store_update('refresh_balance', 'is_refreshing', true);
            
            ps_model.get_balance({
                complete: function(resposnse) {
                            globals.store.store_update('refresh_balance', 'is_refreshing', false);
                        }
            });
        },

        /**
         * This will get join event handler function
         * @param  string tid 
         * @param  string gid 
         * @param  string tableName 
         * @return function
         */
        join_event_function: (function() {
            var existing_handlers = {};

            return function(gid, tid, tableName) {

                if (!existing_handlers.hasOwnProperty(tableName)) {
                    // create a handler for this tableName
                    existing_handlers[tableName] =  ps_window.new_instance(function(window_instance) {
                                                        return function() {
                                                            ps_popup.toast.open(
                                                                ps_language.get('messages.loading_message'), 
                                                                {
                                                                    title: ps_language.get('messages.opening_game'),
                                                                    type : 'schedule',
                                                                    id   : tableName
                                                                }
                                                            );

                                                            window_instance.open('', 'width=800, height=717');

                                                            ps_model.continue_game(gid, tid, {
                                                                success: function(response) {
                                                                            window_instance.redirect(response.URL);
                                                                        },
                                                                fail    : function() {
                                                                            window_instance.close();
                                                                        },
                                                                error   : function() {
                                                                            window_instance.close();
                                                                        },
                                                                complete: function() {
                                                                            ps_popup.toast.close(tableName);
                                                                        }
                                                            });
                                                        };
                                                    },tableName);

                } 

                return existing_handlers[tableName];
            };

        }()),

        /**
         * Add css file to head
         * @param  string    href     If URL is not absolute, this will use the global config rso settings
         * @param  object    settings [vue + onload, onerror]
         * @return 
         */
        css: function(href, settings) {
            settings           = settings      || {};
            settings.data      = settings.data || {};
            settings.data.href = '';

            // extend mounted event
            var actual_href   = ps_helper.is_absolute_url(href) ? href : ps_model.rso_css(href);

            var css_callbacks = ps_helper.object_only(settings, ['onerror', 'onload']);
            delete settings.onload;
            delete settings.onerror;

            settings.mounted = (function(mounted_callback, css_callbacks, actual_href) {

                                        return function() {  

                                            // onload function
                                            if ($.isFunction(css_callbacks.onload)) {
                                                $(this.$el).on('load',css_callbacks.onload);
                                            }

                                            // error function
                                            if ($.isFunction(css_callbacks.onerror)) {
                                                $(this.$el).on('error',css_callbacks.onerror);
                                            }

                                            if ($.isFunction(mounted_callback)) {
                                                mounted_callback.apply(this, arguments);
                                            }

                                            this.href = actual_href;

                                        };

                                    }(settings.mounted, css_callbacks, actual_href));

            ps_view.render($('head'), 'css', settings);
        },

        /**
         * This will render tooltip/popover
         * @param  string type  tooltip/popover
         * @param  object vm 
         * @return void
         */
        render_pop: function(type, vm) {
            var element = $(vm.$el);
            if (!vm.has_pop_event) {
                vm.has_pop_event = true;
                require(['bootstrap'], function(bootstrap) {
                    element.on('show.bs.'+type, function() {
                        // check for templates
                        callables.pop_template(vm);
                        vm.is_shown = true;
                    });

                    element.on('hide.bs.'+type, function() {
                        vm.is_shown = false;
                    });
                    
                    // rerender on window resize
                    var delay_id = ps_helper.uniqid();
                    $(window).on('resize', function() {
                        ps_helper.event_delay(function() {

                            if (vm.is_shown === true) {
                                element[type]('show');
                            }
                            
                        }, 100, delay_id);
                    });

                    element[type]();
                });

            } else {

                if (vm.is_shown === true) {
                    require(['bootstrap'], function(bootstrap) {
                        element[type]('show');
                    });
                }

            }
        },

        /**
         * This will destroy tooltip/popover
         * @param  string type  tooltip/popover
         * @param  object vm 
         * @return void
         */
        destroy_pop: function(type, vm) {
            var element = $(vm.$el);
            if (vm.has_pop_event) {
                require(['bootstrap'], function(bootstrap) {
                    element[type]('destroy');
                });
            } 
        },

        /**
         * This will get the template DOM of tooltip/popover and assign it to content_attr
         * @param  string element 
         * @return void
         */
        pop_template: function(vm) {
            var element = $(vm.$el);
            if (vm.has_template || vm.has_selector) {
                if (vm.has_template) {
                    var template = element.find('.ps_js-pop_template');
                } else {
                    var template = $(vm.template);
                }

                if (template.length > 0) {
                    vm.content_attr = (ps_helper.dom_stringify(template.clone()))
                }
            } else {
                vm.content_attr = vm.content;
            }
        },

        /**
         * Custom tag tooltip and popover properties
         * @param  string type
         * @return object
         */
        pop_properties: function(type) {
            return {
                data    : function() { return { has_pop_event: false, is_shown: false, content_attr:' ' }; },
                props   : ['template', 'content', 'dataTrigger', 'tabindex'],
                computed: {
                            has_template: function() { return !!this.$slots.template; },
                            has_selector: function() {
                                            return ($.type(this.template)=='string' && !ps_helper.empty(this.template));
                                        },
                            is_html     : function() {
                                            return (
                                                !!this.$slots.template 
                                                || $.type(this.template)=='string' && !ps_helper.empty(this.template)
                                            );
                                        },
                            tabindex_attr: function() {
                                            if ($.type(this.tabindex) !== 'undefined') {
                                                return this.tabindex;
                                            } else if (this.dataTrigger == 'focus') {
                                                return -1;
                                            } else {
                                                return null;
                                            }
                                        }
                        },
                mounted      : function() { callables.render_pop(type, this); },
                updated      : function() { callables.render_pop(type, this); },
                beforeDestroy: function() { callables.destroy_pop(type, this); },
            };
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | Group of methods that will add properties to a custom tag vue instance
         |--------------------------------------------------------------------------------------------------------------
         | always return an object
         */
        tag_properties: {
            /**
             * form root tag
             * @return object
             */
            form_root: function() {
                return {
                    mounted: function() {
                                $(this.$el).on('view_components_fullreset', function() {
                                    $(this).trigger('view_components_prereset');
                                    $(this).trigger('reset');
                                    $(this).trigger('view_components_postreset');
                                });
                                
                                $(this.$el).on('submit', function(e) {
                                    e.preventDefault();
                                });
                            }
                };
            },

            /**
             * form root tag
             * @return object
             */
            form_button_file: function() {
                return {
                    props  : ['name','accept','disabled'],
                    mounted: function() {
                                var vm = this;

                                $(vm.$el).find('.ps_js-button_file_tigger').on('click', function(e) {
                                    e.preventDefault();
                                    $(vm.$el).find('.ps_js-button_file_target').click();
                                });
                            }
                };
            },

            /**
             * form root tag
             * @return object
             */
            form_button: function() {
                return {
                    props   : ['type'],
                    computed: {
                                final_type: function() {
                                            return this.type || 'button';
                                        }
                            }
                };
            },

            /**
             * form root tag
             * @return object
             */
            form_text_button: function() {
                return {
                    props   : ['active']
                };
            },
            
            
            /** 
             * captcha custom tag
             * @return object
             */
            form_captcha: function() {
                return {
                    data   : function() {
                                return  {
                                    is_loading   : true,
                                    captcha      : {},
                                    is_initiated : false,
                                    is_mounted   : false
                                };
                            },
                    computed: {
                                final_active: function() {
                                                if (this.is_mounted && this.active) {
                                                    return true;
                                                } else {
                                                    return false;
                                                }
                                            }
                            },
                    watch   : {
                                final_active: function(is_active, old_isactive) {
                                                var vm = this;
                                                if (is_active !== false) {
                                                    // initiate captcha
                                                    ps_model.get_captcha({
                                                        success: function(response) {
                                                                    if (!vm.is_initiated) {
                                                                        vm.is_initiated = true;
                                                                        vm.captcha      = response;
                                                                    }

                                                                    vm.is_loading = false;
                                                                }
                                                    });
                                                }
                                            }
                            },
                    props  : { name: {}, active: { default: true }},
                    mounted: function() { 
                                var vm        = this;

                                if (globals.update_captcha_Ws == false) {
                                    globals.update_captcha_Ws = true;

                                    // WS subscription
                                    require(['ps_websocket'], function(ps_websocket) {
                                        ps_websocket.subscribe('update_captcha', function(message) {
                                            ps_model.update_captcha_image(message.image);
                                        });
                                    });
                                }
                                
                                vm.is_mounted = true;

                                // refresh link
                                $(vm.$el).find('.ps_js-captcha_refresh').on('click', function() {
                                    if (!vm.is_loading) {
                                        vm.is_loading = true;
                                        ps_model.get_captcha({
                                            success: function(response) {
                                                        vm.is_loading = false;
                                                    }
                                        });
                                    }
                                });
                            }
                    };
            },

            /** 
             * captcha custom tag
             * @return object
             */
            form_password_meter: function() {
                return {
                    data    : function() {
                                return { 
                                    score:0
                                };
                            },
                    computed: {
                                cur_score_description: function() {
                                                        return this.descriptions[this.score];
                                                    }
                            },
                    props   : ['name','placeholder','criteriaFields','criteriaValues'],
                    mounted : function() { 
                                var vm             = this;
                                var password_field = $(vm.$el).find('.ps_js-password_field');
                                password_field.on('keyup change paste input', function() {
                                    var value         = $(this).val();
                                    var current_score = 0;

                                    if (!ps_helper.empty(value)) {
                                        // if password bigger than 6 give 1 point
                                        if (value.length > 6) {
                                            current_score++;
                                        }

                                        // if password has both lower and uppercase characters give 1 point  
                                        if (ps_helper.has_lowercase(value) && ps_helper.has_uppercase(value)) {
                                            current_score++;
                                        }

                                        // if password has at least one number give 1 point
                                        if (ps_helper.has_number(value)) {
                                            current_score++;
                                        }

                                        // if password has at least one special character give 1 point
                                        if (ps_helper.has_special_chars(value)) {
                                            current_score++;
                                        }

                                        // if password bigger than 10 give another 1 point
                                        if (value.length > 8) {
                                            current_score++;
                                        }

                                        // if password doesnt contain field/value criterias  give another 1 point
                                        var is_failed = false;

                                        // criteria fields
                                        if ($.isArray(vm.criteriaFields)) {
                                            var fields = $([]);
                                            vm.criteriaFields.forEach(function(field_selector) {
                                                fields = fields.add($(field_selector));
                                            });

                                            fields.each(function() {
                                                var field_value = $(this).val();
                                                var is_empty    = ps_helper.empty(field_value);
                                                if (is_empty || ps_helper.is_contain(field_value,value)) {
                                                    is_failed = true;
                                                    return false;
                                                }
                                            });
                                        }

                                        // criteria values
                                        if (!is_failed && $.isArray(vm.criteriaValues)) {
                                            var criteria_values_length = vm.criteriaValues.length;
                                            for (var i = 0; i <= criteria_values_length; i++) {
                                                if (ps_helper.is_contain(vm.criteriaValues[i],value)) {
                                                    is_failed = true;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if (!is_failed) {
                                            current_score++;
                                        }
                                    }
                                    vm.score = current_score;
                                });

                                // on form reset
                                $(vm.$el).closest('form').on('view_components_postreset', function() {
                                    password_field.trigger('change');
                                });
                            }
                    };
            },

            /**
             * elastic textarea
             * @return void
             */
            form_elastic_textarea: function() {
                return {
                    props  : ['adjustParent'],
                    mounted: function() {
                                var vm = this;
                                $(this.$el).on('keydown blur change paste',function() {
                                    var element = this;
                                    setTimeout(function(){
                                        $(element).css('height', 'auto');
                                        var calc_height = ($(element).val() === '') ? '' : element.scrollHeight + 'px';
                                        $(element).css('height', calc_height).css('overflow', '');

                                        if (!ps_helper.empty(vm.adjustParent)) {

                                            var parent = $(element).closest(vm.adjustParent);
                                            if (parent.length > 0) {
                                                parent.css('height', 'auto');
                                                var parent_height = calc_height === '' ? '' : parent.innerHeight();
                                                parent.css('height', parent_height);
                                            }
                                        }

                                        $(element).scrollTop(element.scrollHeight)
                                    }, 0);
                                });  
                            }
                };
            },

            /**
             * form select tag
             * @return object
             */
            form_select: function() {
                return {
                    props  : { 
                                options        : {}, 
                                placeholder    : {}, 
                                pair           : {}, 
                                selected       : {}, 
                                value          : {}, 
                                description    : {},
                                htmlDescription: { default: false }
                             },
                    computed: {
                                is_pair: function() {
                                            if (typeof(this.pair) === "boolean") {
                                                return this.pair;
                                            } else {
                                                return $.isPlainObject(this.options);
                                            }
                                        },
                                final_options: function() {
                                                var vm           = this;
                                                var final_options=[];
                                                $.each(vm.options, function(index, value) {

                                                    if ($.isPlainObject(value)) {

                                                        final_options.push({
                                                            value      : value[vm.value],
                                                            description: value[vm.description],
                                                            selected   : (vm.selected == value[vm.value])
                                                        });

                                                    } else {
                                                        var final_value = vm.is_pair ? index:value;
                                                        final_options.push({
                                                            value      : final_value,
                                                            description: value,
                                                            selected   : (vm.selected == final_value)
                                                        });
                                                    }

                                                });
                                                return final_options;
                                            }
                            }
                };
            },

            /**
             * form bank select tag
             * @return object
             */
            form_bank_select: function() {
                return {
                    data   : function() {
                                return  {
                                    bank_dropdown:{}
                                };
                           },
                    computed: {
                                banks: function() {
                                        return Object.keys(this.bank_dropdown);
                                    }
                            },
                    props   : ['name', 'selected'],
                    mounted : function() {
                                var vm = this;
                                ps_model.view_data({ 
                                    success: function(response) {
                                                vm.bank_dropdown = response.bank_dropdown;
                                            }
                                },['bank_dropdown']);
                            }
                };
            },

            /**
             * form wallets select tag
             * @return object
             */
            form_wallet_select: function() {
                return {
                    data   : function() {
                                return  {
                                    wallets_dropdown:{}
                                };
                            },
                    computed: {
                                final_wallets: function() {
                                                var vm = this;
                                                switch (vm.type) {
                                                    case 'house':
                                                        var final_wallets = {};
                                                        $.each(vm.wallets_dropdown, function(index, wallet) {
                                                            if (index==='house') {
                                                                final_wallets[index] = wallet;
                                                            }
                                                        });
                                                        return final_wallets;

                                                    case 'nonhouse':
                                                        var final_wallets = {};
                                                        $.each(vm.wallets_dropdown, function(index, wallet) {
                                                            if (index!=='house') {
                                                                final_wallets[index] = wallet;
                                                            }
                                                        });
                                                        return final_wallets;
                                                    
                                                    default:
                                                        return vm.wallets_dropdown;
                                                }
                                            }
                            },
                    props   : ['name', 'selected','placeholder','type'],
                    mounted : function() {
                                var vm = this;
                                ps_model.view_data({ 
                                    success: function(response) {
                                                vm.wallets_dropdown = response.wallets_dropdown;
                                                vm.$nextTick(function() {
                                                    if (!ps_helper.empty(vm.selected)) {
                                                        $(vm.$el).find('.ps_js-wallet_select').val(vm.selected);
                                                    }
                                                });
                                            }
                                },['wallets_dropdown']);
                            }
                };
            },

            /**
             * form bank input tag
             * @return object
             */
            form_bank_input: function () {
                return {
                    data   : function() {
                                return { selected:'', banks:{} };
                            },
                    computed: {
                                segments    : function() {
                                                var vm = this;
                                                if (vm.banks.hasOwnProperty(vm.selected)) {
                                                    return vm.banks[vm.selected].segment_count;
                                                } else {
                                                    return 0;
                                                }
                                            },
                                width        : function() {
                                                return 100/this.segments + '%';
                                            },
                                selected_info: function () {
                                                var vm = this;
                                                if (vm.banks.hasOwnProperty(vm.selected)) {
                                                    return vm.banks[vm.selected];
                                                } 
                                            }
                            },
                    props   : ['name', 'bankSource'],
                    mounted : function() {
                                var vm     = this;
                                var inputs = $(vm.$el).find('.ps_js-bank_input_segment');

                                // prevent invalid values
                                inputs.on('keydown', function(e) {

                                    var value        = $(this).val();
                                    var value_length = value.length;
                                    if (!ps_helper.empty(value)) {   

                                        var input_index    = $(this).attr('data-index'); 
                                        var segment_regex  = new RegExp(vm.selected_info.segment_regex[input_index]);
                                        var segment_length = vm.selected_info.segment_length[input_index];

                                        if (segment_regex.test(value) && value_length <= segment_length) {
                                            $(this).data('bank_input_value', value);
                                        } else {
                                            e.preventDefault();
                                        }

                                    } else {

                                        $(this).data('bank_input_value','');

                                    }

                                }); 

                                inputs.on('keyup input', function(e) {
                                    var value          = $(this).val();
                                    var value_length   = value.length;
                                    var input_index    = $(this).attr('data-index'); 
                                    var segment_regex  = new RegExp(vm.selected_info.segment_regex[input_index]);
                                    var segment_length = vm.selected_info.segment_length[input_index];

                                    if (!ps_helper.empty(value)) {
                                        if (!segment_regex.test(value) || value_length > segment_length) {
                                            $(this).val($(this).data('bank_input_value'));
                                        }
                                    }

                                    var new_value_length = $(this).val().length;
                                    var scope_class      = '.ps_js-bank_input_segment:visible';

                                    // next/prev focus handler
                                    if (e.keyCode == 8) { 

                                        // backspace, prevoous
                                        if (new_value_length <= 0) {
                                            var previous = $(this).prevAll(scope_class)[0];
                                            if (!ps_helper.empty(previous) && $(this)[0] !== previous) {
                                                $(previous).trigger('focus');   
                                            }
                                        }

                                    } else if (!(e.shiftKey && e.keyCode == 9))  {

                                        // next
                                        if (new_value_length >= segment_length) {
                                            var next = $(this).nextAll(scope_class)[0];
                                            if (!ps_helper.empty(next) && $(this)[0] !== next) {
                                                $(next).trigger('focus');   
                                            }
                                        }

                                    } else {

                                        $(this).val('');

                                    }
                                });
                                
                                // don't allow another input to be focused without completing the previous first
                                inputs.on('focus', function(e) {
                                    var previous        = $(this).prev('.ps_js-bank_input_segment:visible');
                                    if (previous.length > 0) {
                                        var previous_value  = previous.val(); 
                                        var value_length    = previous_value.length; 
                                        var prev_index      = previous.attr('data-index'); 
                                        var previous_regex  = new RegExp(vm.selected_info.segment_regex[prev_index]);
                                        var previous_length = vm.selected_info.segment_length[prev_index];
                                        if ($(this)[0] !== previous[0]) {
                                            if (!previous_regex.test(previous_value) || value_length<previous_length) {
                                                previous.trigger('focus');   
                                            }
                                        }
                                    }
                                });

                                ps_model.view_data({ 
                                    success: function(response) {
                                                vm.$nextTick(function() {
                                                    vm.banks = response.bank_dropdown;
                                                    var source       = $(vm.bankSource);
                                                    var source_event = function() {
                                                                        vm.selected = source.val();
                                                                        inputs.val('');

                                                                        vm.$nextTick(function() {
                                                                            if (!ps_helper.empty(vm.selected)) {
                                                                                inputs.filter(':visible')
                                                                                      .first()
                                                                                      .trigger('focus');   
                                                                            }   
                                                                        });
                                                                    };

                                                    source.on('change', source_event);
                                                    source_event();

                                                    // on form reset
                                                    $(vm.$el).closest('form').on('view_components_postreset', source_event);
                                                });
                                            }
                                },['bank_dropdown']);
                            }
                };
            },

            /**
             * form select tag
             * @return object
             */
            form_currency_select: function() {
                return {
                    data   : function() {
                                return  {
                                    currency:{ enabled: {}, base: null }
                                };
                           },
                    computed: {
                                final_selected: function() {
                                                    var vm = this;
                                                    if (ps_helper.empty(vm.selected)) {
                                                        if (ps_helper.empty(vm.placeholder)) {
                                                            return vm.currency.base;
                                                        }
                                                    } else {
                                                        return vm.selected;
                                                    }
                                                }
                            },
                    props    : ['name', 'selected', 'placeholder'],
                    mounted  : function() {
                                var vm = this;
                                ps_model.view_data({ 
                                    success: function(response) {
                                                vm.currency = response.currency;
                                            }
                                },['currency']);
                            }
                };
            },

            /**
             * form select tag
             * @return object
             */
            form_securityquestion_select: function() {
                return {
                    data   : function() {
                                return  {
                                    securityQuestions:{ list:[] }
                                };
                           },
                    props   : ['name', 'selected'],
                    mounted : function() {
                                var vm = this;
                                ps_model.view_data({ 
                                    success: function(response) {
                                                vm.securityQuestions = response.securityQuestions;
                                            }
                                },['securityQuestions']);
                            }
                };
            },

            /**
             * form money input tag
             * @return object
             */
            form_money_input: function() {
                return {
                    props  : ['name', 'placeholder'],
                    mounted: function() {
                                var vm            = this;
                                var input_element = $(vm.$el).find('.ps_js-money_input');
                                input_element.on('blur change keyup paste', function() {
                                    ps_helper.event_delay(function() {
                                        var value          = input_element.val();
                                        var caret_position = input_element[0].selectionStart;
                                        var value_length   = value.length;
                                        var set_caret      = caret_position === input_element.val().length ? 0 : 1;

                                        if (value !== '') {
                                            input_element.val(ps_helper.money_format(value,0));
                                        }

                                        if (set_caret === 1) {
                                            var caret_indent = value_length - input_element.val().length;
                                            ps_helper.set_caret_position(caret_position - caret_indent, input_element);
                                        }
                                    },600,'form_money_input');
                                });
                            }
                };
            },

            /**
             * form input range
             * @return object
             */
            form_input_range: function() {
                return {
                    props:['min','max','name','value']
                };
            },

            /**
             * form date picker
             * @return object
             */
            form_date_picker: function() {
                var present_date_object = new Date();

                return {
                    data   : function() { 
                                return { 
                                    is_active         : false, 
                                    selected_day      : null, 
                                    selected_month    : null, 
                                    selected_year     : null, 
                                    selected_hour     : null, 
                                    selected_minute   : null, 
                                    selected_second   : null, 
                                    active_picker     : 'day',
                                    time_parts        : ['hour','minute','second'],
                                    time_picker_active: false
                                };
                             },
                    props  : {
                                name          : {},
                                // this format works well with IE and other browsers
                                initialDate  : { default: ps_date.format_date(
                                                            'MM dd,yy HHH:iii:sss',
                                                            present_date_object
                                                        )
                                                },
                                format       : { default: 'yy/mm/dd hhh:iii:sss AA'},
                                monthFormat  : { default: 'M'   },
                                yearFormat   : { default: 'yy'  },
                                dayFormat    : { default: 'dd'  },
                                timeFormat   : { default: 'hhh:iii:sss AA'  },
                                showYears    : { default: 9     },
                                monthsPerRow : { default: 3     },
                                yearsPerRow  : { default: 3     },
                                enableYears  : {},
                                enableMonths : {},
                                enableHour   : { default: true  },
                                enableMinute : { default: true  },
                                enableSecond : { default: true  },
                                enableFuture : { default: true  },
                                submitOnSet  : { default: false }
                            },
                    computed: {
                                value_object   : function() {
                                                    var vm          = this;
                                                    var date_object = new Date(vm.initialDate);

                                                    if (vm.selected_year!==null) {
                                                        var selected_year = vm.selected_year;
                                                    } else {
                                                        var selected_year = date_object.getFullYear();
                                                    }

                                                    if (vm.selected_month!==null) {
                                                        var selected_month = vm.selected_month;
                                                    } else {
                                                        var selected_month = date_object.getMonth() + 1;
                                                    }

                                                    if (vm.selected_day!==null) {
                                                        var selected_day = vm.selected_day;
                                                    } else {
                                                        var selected_day = date_object.getDate();
                                                    }

                                                    if (vm.selected_hour!==null) {
                                                        var selected_hour = vm.selected_hour;
                                                    } else {
                                                        var selected_hour = date_object.getHours();
                                                    }

                                                    if (vm.selected_minute!==null) {
                                                        var selected_minute = vm.selected_minute;
                                                    } else {
                                                        var selected_minute = date_object.getMinutes();
                                                    }

                                                    if (vm.selected_second!==null) {
                                                        var selected_second = vm.selected_second;
                                                    } else {
                                                        var selected_second = date_object.getSeconds();
                                                    }

                                                    return  { 
                                                        year  : parseInt(selected_year),
                                                        month : parseInt(selected_month),
                                                        day   : parseInt(selected_day),
                                                        hour  : parseInt(selected_hour),
                                                        minute: parseInt(selected_minute),
                                                        second: parseInt(selected_second)
                                                    };
                                                },
                                // value base on selected and initialDate
                                value          : function() {
                                                    return ps_date.format_date(this.format,this.selected_date_object()); 
                                                },
                                // formatted value month             
                                final_month     : function() { 
                                                    return ps_date.format_date(
                                                        this.monthFormat, 
                                                        this.selected_date_object()
                                                    );     
                                                },
                                // formatted value year
                                final_year     : function() { 
                                                    return ps_date.format_date(
                                                        this.yearFormat, 
                                                        this.selected_date_object()
                                                    );    
                                                },
                                // formatted value day
                                final_day      : function() { 
                                                    return ps_date.format_date(
                                                        this.dayFormat, 
                                                        this.selected_date_object()
                                                    );
                                                },       
                                // time values
                                final_hour     : function() { 
                                                    return this.value_object.hour;
                                                },    
                                final_minute    : function() { 
                                                    return this.value_object.minute;
                                                },    
                                final_second    : function() { 
                                                    return this.value_object.second;     
                                                },
                                final_time      : function() { 
                                                    return ps_date.format_date(
                                                        this.timeFormat,
                                                        this.selected_date_object()
                                                    );
                                                },
                                // next and prev year
                                nav_year      : function() { 
                                                    var vm            = this;
                                                    var selected_year = vm.value_object.year;  

                                                    /**
                                                     |------------------------------------------------------------------
                                                     | Calculate next year
                                                     |------------------------------------------------------------------
                                                     */
                                                    var calculated_next = selected_year + 1;
                                                    if (!vm.is_year_enabled(calculated_next)) {
                                                        calculated_next = false;
                                                    }

                                                    /**
                                                     |------------------------------------------------------------------
                                                     | Calculate next year
                                                     |------------------------------------------------------------------
                                                     */
                                                    var calculated_prev = selected_year - 1;
                                                    if (!vm.is_year_enabled(calculated_prev)) {
                                                        calculated_prev = false;
                                                    }

                                                    return { 
                                                        next:{ number: calculated_next }, 
                                                        prev:{ number: calculated_prev }
                                                    };
                                                },
                                // next and prev month
                                nav_month     : function() { 
                                                    var vm        = this;
                                                    var nav_month = { 
                                                                        next: { number: null, affected: null},
                                                                        prev: { number: null, affected: null}
                                                                    };
                                                    var selected_month= vm.value_object.month;
                                                    var selected_year = vm.value_object.year;

                                                    /**
                                                     |------------------------------------------------------------------
                                                     | Calculate next month
                                                     |------------------------------------------------------------------
                                                     */
                                                    var calculated_next  = selected_month + 1;
                                                    if (calculated_next > 12) {
                                                        // month number exceeded the max month number per year
                                                        if (vm.nav_year.next.number!==false) {
                                                            if (vm.is_month_enabled(1,vm.nav_year.next.number)) {
                                                                nav_month.next.number   = 1;
                                                                nav_month.next.affected = 'year';
                                                            } else {
                                                                nav_month.next.number   = false;
                                                            }
                                                        } else {
                                                            nav_month.next.number = false;
                                                        }
                                                    } else {
                                                        // normal next
                                                        if (vm.is_month_enabled(calculated_next,selected_year)) {
                                                            nav_month.next.number = calculated_next;
                                                        } else {
                                                            nav_month.next.number = false;
                                                        }
                                                    }
                                                    
                                                    /**
                                                     |------------------------------------------------------------------
                                                     | Calculate previous month
                                                     |------------------------------------------------------------------
                                                     */
                                                    var calculated_prev  = selected_month - 1;
                                                    if (calculated_prev < 1) {
                                                        // month number exceeded the min month number per year
                                                        if (vm.nav_year.prev.number!==false) {
                                                            if (vm.is_month_enabled(12,vm.nav_year.prev.number)) {
                                                                nav_month.prev.number   = 12;
                                                                nav_month.prev.affected = 'year';
                                                            } else {
                                                                nav_month.prev.number   = false;
                                                            }
                                                        } else {
                                                            nav_month.prev.number = false;
                                                        }
                                                    } else {
                                                        // normal prev
                                                        if (vm.is_month_enabled(calculated_prev,selected_year)) {
                                                            nav_month.prev.number = calculated_prev;
                                                        } else {
                                                            nav_month.prev.number = false;
                                                        }
                                                    }

                                                    return nav_month;
                                                },
                                // how many days in current selected month
                                month_days    : function() {
                                                    return ps_date.month_days(this.selected_date_object());
                                                },
                                // next and prev day
                                nav_day       : function() {
                                                    var vm      = this;
                                                    var nav_day = { 
                                                                    next: { number: null, affected: null},
                                                                    prev: { number: null, affected: null}
                                                                };

                                                    var selected_day  = vm.value_object.day;
                                                    var selected_month= vm.value_object.month;
                                                    var selected_year = vm.value_object.year;
                                                    /**
                                                     |------------------------------------------------------------------
                                                     | Calculate next day
                                                     |------------------------------------------------------------------
                                                     */
                                                    var calculated_next  = selected_day + 1;
                                                    if (calculated_next > vm.month_days) {

                                                        // day number exceeded the max day number per month
                                                        if (vm.nav_month.next.number!==false) {
                                                            if (vm.nav_month.next.number <= 1) {
                                                                var next_year = selected_year + 1;
                                                            } else {
                                                                var next_year = selected_year;
                                                            }
                                                            var next_day_enabled = vm.is_day_enabled(
                                                                                    1,
                                                                                    vm.nav_month.next.number,
                                                                                    next_year
                                                                                );

                                                            if (next_day_enabled) {
                                                                nav_day.next.affected = 'month';
                                                                nav_day.next.number   = 1;
                                                            } else {
                                                                nav_day.next.number = false;
                                                            }
                                                        } else {
                                                            nav_day.next.number = false;
                                                        }

                                                    } else {

                                                        // normal next day
                                                       var next_day_enabled = vm.is_day_enabled(
                                                                                calculated_next,
                                                                                selected_month,
                                                                                selected_year
                                                                            );
                                                        if (next_day_enabled) {
                                                            nav_day.next.number = calculated_next;
                                                        } else {
                                                            nav_day.next.number = false;
                                                        }
                                                    }
                                                    
                                                    /**
                                                     |------------------------------------------------------------------
                                                     | Calculate previous day
                                                     |------------------------------------------------------------------
                                                     */
                                                    var calculated_prev  = selected_day - 1;
                                                    if (calculated_prev < 1) {

                                                        // day number exceeded the min day number per month
                                                        if (vm.nav_month.prev.number!==false) {
                                                            var date_object = new Date(
                                                                                selected_year,
                                                                                // date object starts month in zero
                                                                                vm.nav_month.prev.number - 1,
                                                                                1
                                                                            );
                                                            var prev_month_days =  ps_date.month_days(date_object);

                                                            if (vm.nav_month.prev.number >= 12) {
                                                                var prev_year = selected_year - 1;
                                                            } else {
                                                                var prev_year = selected_year;
                                                            }

                                                            var prev_day_enabled = vm.is_day_enabled(
                                                                                    prev_month_days,
                                                                                    vm.nav_month.prev.number,
                                                                                    prev_year
                                                                                );

                                                             if (prev_day_enabled) {
                                                                nav_day.prev.affected = 'month';
                                                                nav_day.prev.number   = prev_month_days;
                                                            } else {
                                                                nav_day.prev.number = false;
                                                            }
                                                        } else {
                                                            nav_day.prev.number = false;
                                                        }

                                                    } else {

                                                        // normal prev day
                                                       var prev_day_enabled = vm.is_day_enabled(
                                                                                calculated_prev,
                                                                                selected_month,
                                                                                selected_year
                                                                            );
                                                        if (prev_day_enabled) {
                                                            nav_day.prev.number = calculated_prev;
                                                        } else {
                                                            nav_day.prev.number = false;
                                                        }

                                                    }

                                                    return nav_day;
                                                },
                                // what weekday the current month starts
                                start_weekday  : function() { 
                                                    return ps_date.month_start_weekday(this.selected_date_object()); 
                                                },
                                // days to be displayed in calendar by row
                                days           : function() { 
                                                    var vm                 = this;
                                                    var days               = [];
                                                    var month_days         = vm.month_days;
                                                    var last_day_inserted  = 0;
                                                    var is_weekday_started = false;
                                                    var row                = 0;
                                                    var selected_year      = vm.value_object.year;
                                                    var selected_month     = vm.value_object.month;

                                                    while (last_day_inserted < month_days) {
                                                        days[row] = [];

                                                        for (var i = 0; i < 7; i++) {
                                                            if (is_weekday_started == false) {
                                                                is_weekday_started = vm.start_weekday == i;
                                                            }

                                                            if (is_weekday_started && last_day_inserted < month_days) {
                                                                last_day_inserted++;

                                                                var date_object= new Date(
                                                                                    selected_year,
                                                                                    // date object starts month in zero
                                                                                    selected_month - 1,
                                                                                    last_day_inserted
                                                                                ); 

                                                                days[row][i]   =  {
                                                                                    name    : ps_date.format_date(
                                                                                                vm.dayFormat,
                                                                                                date_object
                                                                                            ),
                                                                                    value   : last_day_inserted,
                                                                                    enabled : vm.is_day_enabled(
                                                                                                last_day_inserted,
                                                                                                selected_month,
                                                                                                selected_year
                                                                                            )
                                                                                };
                                                            } else {
                                                                days[row][i] = '';
                                                            }
                                                        }

                                                        row++;
                                                    }     

                                                    return days;   
                                                },
                                // months to be displayed in calendar by row
                                months         : function() {
                                                    var vm                  = this;
                                                    var months_row          = [];
                                                    var row                 = 0;
                                                    var last_month_inserted = 0;
                                                    var selected_year       = vm.value_object.year;

                                                    while (last_month_inserted < 12) {
                                                        months_row[row] = [];

                                                        for (var i = 0; i < vm.monthsPerRow; i++) {
                                                            last_month_inserted++;

                                                            var date_object  = new Date(
                                                                                selected_year,
                                                                                // date object starts month in zero
                                                                                last_month_inserted - 1,
                                                                                1
                                                                            ); 

                                                            var formatted = ps_date.format_date(
                                                                                vm.monthFormat,
                                                                                date_object
                                                                            );

                                                            months_row[row][i] = {
                                                                                    name    : formatted,
                                                                                    value   : last_month_inserted,
                                                                                    enabled : vm.is_month_enabled(
                                                                                                last_month_inserted,
                                                                                                selected_year
                                                                                            )
                                                                                }
                                                        }

                                                        row++;
                                                    }

                                                    return months_row;
                                                },
                                // years to be displayed in calendar by row
                                years: function() {
                                        var vm                 = this; 
                                        var years_row          = [];
                                        var selected_year      = vm.value_object.year;
                                        var lowest_year        = selected_year - Math.ceil((vm.showYears/2));
                                        var highest_year       = selected_year + Math.ceil((vm.showYears/2)-1);
                                        var last_year_inserted = lowest_year;
                                        var row                = 0;

                                        while (last_year_inserted < highest_year) {
                                            years_row[row] = [];

                                            for (var i = 0; i < vm.yearsPerRow; i++) {
                                                last_year_inserted++;
                                                years_row[row][i] = {
                                                                        name: ps_date.format_date(
                                                                                vm.yearFormat,
                                                                                last_year_inserted
                                                                                +'-01-01'
                                                                            ),
                                                                        value   : last_year_inserted,
                                                                        enabled : vm.is_year_enabled(last_year_inserted)
                                                                    };
                                            }

                                            row++;
                                        }

                                        return years_row;
                                    }
                            },

                    mounted : function() {
                                var vm         = this;
                                var input      = $(vm.$el).find('.ps_js-date_picker_input');
                                var time_parts = vm.time_parts;

                                // initialize time ranges
                                time_parts.forEach(function(time_part) {
                                    var time_dom = $(vm.$el).find(
                                                        '.ps_js-calendar_'
                                                        +time_part
                                                        +' .ps_js-input_range_element'
                                                    );

                                    if (time_dom.length>0) {
                                        time_dom.val(vm['final_'+time_part]);

                                        vm.$watch('final_'+time_part, function() {
                                            time_dom.val(vm['final_'+time_part]);
                                        });

                                        time_dom.on('change',function() {
                                            vm.goto_number(time_part, $(this).val());
                                        });

                                        time_dom.on('mousedown',function() {
                                            $(this).on('mousemove touchmove', function() {
                                                $(this).trigger('change');
                                            });
                                            $(this).one('mouseup', function() {
                                                $(this).off('mousemove touchmove');
                                            });
                                        });

                                        vm.trigger_availability(time_part);
                                    }
                                });
                                
                                // initialize date picker
                                vm.grid_picker();
                                vm.trigger_availability('year');
                                vm.trigger_availability('month');
                                vm.trigger_availability('day');

                                // events
                                var previous = {};
                                var save_previous = function() {
                                                    previous = {
                                                                selected_day    : vm.selected_day, 
                                                                selected_month  : vm.selected_month, 
                                                                selected_year   : vm.selected_year, 
                                                                selected_hour   : vm.selected_hour, 
                                                                selected_minute : vm.selected_minute, 
                                                                selected_second : vm.selected_second
                                                            };
                                                };

                                save_previous();

                                $(vm.$el).on('focus', function() {
                                    vm.is_active = true;
                                    save_previous();
                                });
                                
                                var close = function() {
                                                vm.is_active          = false;
                                                vm.active_picker      = 'day';
                                                vm.time_picker_active = false;
                                            };

                                $(vm.$el).on('focusout', function(e) {
                                    if (!$.contains(this, e.relatedTarget) && e.relatedTarget!=this) {
                                        close();
                                    }
                                });

                                $(vm.$el).find('.ps_js-calendar_cancel').on('click', function() {
                                    vm.selected_day    = previous.selected_day;
                                    vm.selected_month  = previous.selected_month;
                                    vm.selected_year   = previous.selected_year;
                                    vm.selected_hour   = previous.selected_hour;
                                    vm.selected_minute = previous.selected_minute;
                                    vm.selected_second = previous.selected_second;
                                    $(vm.$el).trigger('blur');
                                });

                                if (vm.submitOnSet) {
                                    var form = $(vm.$el).closest('form');

                                    if (form.length > 0) {
                                        $(vm.$el).on('view_components_set', function() {
                                            form.trigger('submit');
                                        });
                                    }
                                }

                                $(vm.$el).find('.ps_js-calendar_set').on('click', function() {
                                    close();
                                    $(vm.$el).trigger('view_components_set');
                                });

                                $(vm.$el).on('view_components_previous', function(e, type) {
                                    if (ps_helper.in_array(type, time_parts)) {
                                        vm.navigate_time(type, 'prev');
                                    } else {
                                        vm.navigate(type, 'prev');
                                    }
                                });

                                $(vm.$el).on('view_components_availability', function(e, type) {
                                    vm.trigger_availability(type);
                                });

                                $(vm.$el).on('view_components_next', function(e, type) {
                                    if (ps_helper.in_array(type, time_parts)) {
                                        vm.navigate_time(type, 'next');
                                    } else {
                                        vm.navigate(type, 'next');
                                    }
                                });
                                
                                $(vm.$el).find('.ps_js-edit_time').on('click', function() {
                                    vm.time_picker_active = true;
                                });
                                
                                $(vm.$el).find('.ps_js-close_time').on('click', function() {
                                    vm.time_picker_active = false;
                                });
                                
                                $(vm.$el).find('.ps_js-edit_day').on('click', function() {
                                    vm.active_picker = 'day';
                                });
                                
                                $(vm.$el).find('.ps_js-edit_month').on('click',  function() {
                                    vm.active_picker = 'month';
                                });
                                
                                $(vm.$el).find('.ps_js-edit_year').on('click',  function() {
                                    vm.active_picker = 'year';
                                });

                                $(vm.$el).find('.ps_js-calendar_arrow').on('click',  function() {
                                    var select_type             = $(this).data('type');
                                    var select_arrow            = $(this).data('arrow');
                                    vm.navigate(select_type, select_arrow);
                                    vm.active_picker            = select_type;
                                });

                                $(vm.$el).find('.ps_js-calendar_now').on('click',  function() {
                                    vm.goto_now();
                                });

                                // on form reset
                                $(vm.$el).closest('form').on('view_components_postreset', function() {
                                    vm.goto_now();
                                });
                            },
                    methods: {
                                navigate   : function(type, arrow) {
                                                var vm          = this;
                                                var navigate_to = vm['nav_'+type][arrow];

                                                if (navigate_to.number !== false) {
                                                    var affected = navigate_to.affected;
                                                    if (!ps_helper.empty(affected)) {
                                                        vm.navigate(navigate_to.affected, arrow);
                                                    }
                                                    vm.goto_number(type, navigate_to.number);

                                                } else {
                                                    callables.debug(arrow+' '+type+' is disabled.');
                                                }
                                            },
                                navigate_time: function(type, arrow) {
                                                var vm            = this;
                                                var current_value = vm.value_object[type];
                                                if (vm.is_time_enabled(type, arrow)) {
                                                    if (arrow == 'next') {
                                                        vm.goto_number(type, current_value + 1);
                                                    } else if(arrow == 'prev') {
                                                        vm.goto_number(type, current_value - 1);
                                                    }
                                                } else {
                                                    callables.debug('Cannot adjust '+type+' anymore.');
                                                }
                                            },
                                trigger_availability: function(type) {
                                                        var vm = this;
                                                        if (ps_helper.in_array(type, vm.time_parts)) { 
                                                            // next of this action if still enabled
                                                            $(vm.$el).trigger('view_components_available', {
                                                                type     : type,
                                                                arrow    : 'next',
                                                                available: vm.is_time_enabled(type, 'next')
                                                            });

                                                            $(vm.$el).trigger('view_components_available', {
                                                                type     : type,
                                                                arrow    : 'prev',
                                                                available: vm.is_time_enabled(type, 'prev')
                                                            });
                                                        } else {
                                                            // next of this action if still enabled
                                                            $(vm.$el).trigger('view_components_available', {
                                                                type     : type,
                                                                arrow    : 'next',
                                                                available: (vm['nav_'+type]['next'].number !== false)
                                                            });

                                                            $(vm.$el).trigger('view_components_available', {
                                                                type     : type,
                                                                arrow    : 'prev',
                                                                available: (vm['nav_'+type]['prev'].number !== false)
                                                            });
                                                        }
                                                    },
                                is_time_enabled: function(type, arrow) {
                                                    var vm            = this;
                                                    var current_value = vm.value_object[type];
                                                    switch (type) {
                                                        case 'hour':
                                                            if (arrow == 'prev' && current_value<=0) {
                                                                return false;
                                                            }

                                                            if (arrow == 'next' && current_value>=23) {
                                                                return false;
                                                            }

                                                            return true;
                                                        case 'second':
                                                        case 'minute':
                                                            if (arrow == 'prev' && current_value<=1) {
                                                                return false;
                                                            }

                                                            if (arrow == 'next' && current_value>=59) {
                                                                return false;
                                                            }

                                                            return true;
                                                    }
                                                },
                                goto_number: function(type, number) {
                                                var vm               = this;
                                                var previous         = vm.value_object[type];

                                                switch (type) {
                                                    case 'year':

                                                        if (vm.is_year_enabled(number)) {
                                                            vm['selected_'+type] = number;

                                                            if (number < previous) {
                                                                vm.goto_number('month', 12);
                                                            } else {
                                                                vm.goto_number('month', 1);
                                                            }
                                                            
                                                            vm.active_picker  = 'month';

                                                        } else {

                                                            callables.debug(
                                                                'Selected year disabled.'
                                                                + 'No changes applied' 
                                                            );
                                                            return false;

                                                        }

                                                        break;

                                                    case 'month':

                                                        var select_month = false;
                                                        if (vm.is_month_enabled(number, vm.selected_year)) {

                                                            select_month = number;

                                                        } else {

                                                            // we'll select the first enabled month in selected year
                                                            var enabled = false;
                                                            var ctr     = 1;
                                                            while (enabled === false && ctr < 12) {

                                                                if (vm.is_month_enabled(ctr, vm.selected_year)) {
                                                                    enabled = ctr;
                                                                }

                                                                ctr++;
                                                            }

                                                            if (enabled !== false) {
                                                                select_month = enabled;
                                                            }
                                                        }

                                                        if (select_month !== false) {

                                                            vm['selected_'+type] = select_month;
                                                            vm.goto_number('day', 1);
                                                            vm.active_picker    = 'day';

                                                        } else {

                                                            callables.debug('No enabled month this year.');
                                                            return false;

                                                        }

                                                        break;

                                                    case 'day':

                                                        var select_day = false;
                                                        var is_enabled = vm.is_day_enabled(
                                                                            number, 
                                                                            vm.selected_month, 
                                                                            vm.selected_year
                                                                        );
                                                        if (is_enabled) {

                                                            select_day = number;

                                                        } else {

                                                            // we'll select the first enabled day in selected month
                                                            var enabled = false;
                                                            var ctr     = 1;
                                                            while (enabled === false && ctr < vm.month_days) {

                                                                var is_enabled = vm.is_day_enabled(
                                                                                    ctr, 
                                                                                    vm.selected_month, 
                                                                                    vm.selected_year
                                                                                );

                                                                if (is_enabled) {
                                                                    enabled = ctr;
                                                                }

                                                                ctr++;
                                                            }

                                                            if (enabled !== false) {
                                                                select_day = enabled;
                                                            }
                                                        }

                                                        if (select_day !== false) {

                                                            vm['selected_'+type] = number;

                                                        } else {

                                                            callables.debug('No enabled day this month.');
                                                            return false;

                                                        }

                                                        break;

                                                    default:
                                                        vm['selected_'+type] = number;
                                                }

                                                // if not part of time then the grid changes for sure
                                                if (!ps_helper.in_array(type, vm.time_parts)) {
                                                    vm.grid_picker();
                                                }

                                                // check next and prev of this type if avialable
                                                vm.trigger_availability(type);

                                                // calendr value changes trigger custom change event
                                                vm.$nextTick(function() {
                                                    $(vm.$el).trigger('view_components_change', vm.value);
                                                });
                                            },
                                goto_now    : function() {
                                                var vm = this;
                                                var this_moment    = new Date();
                                                vm.goto_number('year'   , this_moment.getFullYear());
                                                // +1 because selected months start at 1
                                                vm.goto_number('month'  , this_moment.getMonth() + 1);
                                                vm.goto_number('day'    , this_moment.getDate());
                                                vm.goto_number('hour'   , this_moment.getHours());
                                                vm.goto_number('minute' , this_moment.getMinutes());
                                                vm.goto_number('second' , this_moment.getSeconds());
                                                vm.active_picker = 'day';
                                            },
                                grid_picker : function() {
                                                var vm = this;
                                                $(vm.$el).find('.ps_js-calendar_select')
                                                    .off('click')
                                                    .on('click', function() {
                                                        if (!$(this).hasClass('ps_js-disabled')) {
                                                            var select_type  = $(this).attr('data-type');
                                                            var select_value = $(this).attr('data-value');
                                                            if (!ps_helper.empty(select_value)) {
                                                                vm.goto_number(select_type, parseInt(select_value));
                                                            }
                                                        }
                                                    });
                                            },
                                is_year_enabled: function(year) {
                                                    var vm = this;

                                                    if (!vm.enableFuture) {
                                                        var cur_year  = present_date_object.getFullYear();
                                                        if (year > cur_year) {
                                                            return false;
                                                        }
                                                    }

                                                    if (!ps_helper.empty(vm.enableYears)) {
                                                        // flat array
                                                        if ($.isArray(vm.enableYears)) {
                                                            return ps_helper.in_array(year, vm.enableYears);
                                                        }

                                                        // direct all except
                                                        if ($.isPlainObject(vm.enableYears)) {
                                                            if ($.isArray(vm.enableYears.all_except)) {
                                                                return !ps_helper.in_array(
                                                                    year, 
                                                                    vm.enableYears.all_except
                                                                );
                                                            }
                                                        }
                                                    }

                                                    return true;
                                                },
                                is_month_enabled: function(month, year) {
                                                    var vm = this;

                                                    if (!vm.enableFuture) {
                                                        // date object month starts at 0
                                                        var cur_month = present_date_object.getMonth() + 1;
                                                        var cur_year  = present_date_object.getFullYear();
                                                        if (month > cur_month && year >= cur_year) {
                                                            return false;
                                                        }
                                                    }

                                                    if (!ps_helper.empty(vm.enableMonths)) {

                                                        // flat array
                                                        if ($.isArray(vm.enableMonths)) {
                                                            return ps_helper.in_array(month, vm.enableMonths);
                                                        }

                                                        if ($.isPlainObject(vm.enableMonths)) {

                                                            // per year flat array
                                                            if ($.isArray(vm.enableMonths[year])) {
                                                                return ps_helper.in_array(month, vm.enableMonths[year]);
                                                            }

                                                            // per year all except
                                                            if ($.isPlainObject(vm.enableMonths[year])) {
                                                                if ($.isArray(vm.enableMonths[year].all_except)) {
                                                                    return !ps_helper.in_array(
                                                                        number, 
                                                                        vm.enableMonths[year].all_except
                                                                    );
                                                                }
                                                            }

                                                            // direct all except
                                                            if ($.isArray(vm.enableMonths.all_except)) {
                                                                return !ps_helper.in_array(
                                                                    number, 
                                                                    vm.enableMonths.all_except
                                                                );
                                                            }
                                                        }
                                                    }

                                                    return true;
                                                },
                                is_day_enabled  : function(day, month, year) {
                                                    var vm = this;
                                                    if (!vm.enableFuture) {
                                                        var cur_day   = present_date_object.getDate();
                                                        // date object month starts at 0
                                                        var cur_month = present_date_object.getMonth() + 1;
                                                        var cur_year  = present_date_object.getFullYear();

                                                        if (day > cur_day && month >= cur_month && year >= cur_year) {
                                                            return false;
                                                        }
                                                    }
                                                    return true;
                                                },
                                // Date object of selected date              
                                selected_date_object: function() {
                                                        var vm = this;
                                                        return new Date(
                                                            vm.value_object.year,
                                                            // date object starts month in zero
                                                            vm.value_object.month - 1,
                                                            vm.value_object.day,
                                                            vm.value_object.hour,
                                                            vm.value_object.minute,
                                                            vm.value_object.second
                                                        );
                                                    }
                            }
                };
            },

            /**
             * form switch
             * @return object
             */
            form_switch: function() {
                return {
                    data    : function() {
                                return { selected: null };
                            },
                    props   : { 
                                options       : { default: [] }, 
                                default_option: { default: null },
                                name          : { default: null },
                                // when set to true, this will foloow assign value only
                                readonly      : { default: false },
                                assign        : { default: '' },
                             },
                    computed: {
                                options_length: function() {
                                                    return this.options.length;
                                                },
                                value         : function() {
                                                    if (this.readonly) {

                                                        return this.assign;

                                                    } else {

                                                        if (ps_helper.empty(this.selected)) {

                                                            if (ps_helper.empty(this.default_option)) {
                                                                if (this.options.length > 0) {
                                                                    return this.options[0];
                                                                } else {
                                                                    return null;
                                                                }
                                                            } else {
                                                                return this.default_option;
                                                            }

                                                        } else {

                                                            return this.selected;
                                                            
                                                        }

                                                    }
                                                },
                                selected_index  : function() {
                                                    return this.options.indexOf(this.value);
                                                }   
                            },
                    mounted: function() {
                                var vm = this;
                                $(vm.$el).find('.ps_js-form_switch_option').on('click', function() {
                                    vm.selected = vm.options[parseInt($(this).attr('data-index'))];
                                    $(vm.$el).trigger('view_components_change', vm.value);
                                });

                                // on form reset
                                $(vm.$el).closest('form').on('view_components_postreset', function() {
                                    vm.selected = null;
                                    $(vm.$el).trigger('view_components_change', vm.value);
                                });
                            }
                };
            },

            /**
             * If popup this will be close window button else this will be go to main page
             * @return object
             */
            form_close_goto: function() {
                return {
                    data    : function() {
                                return  { is_popup: ps_helper.is_popup() };
                            },
                    mounted : function() {
                                var vm = this;

                                $(vm.$el).on('click','.ps_js-close_goto_button', function() {
                                    ps_model.view_data({
                                        success: function(view_data) {
                                                    if (ps_helper.is_popup()) {
                                                        window.close();
                                                    } else {
                                                        window.location = view_data.site.base_url;
                                                    }
                                                }

                                    }, ['site']);
                                });
                            }
                };
            },

            /**
             * small-notice tag
             * @return void
             */
            indicator_small_notice: function() {
                return {
                    data   : function() {  return {  final_type:'info'  };  },
                    props  :['type'],
                    mounted: function() {
                                if (!ps_helper.empty(this.type)) {
                                    this.final_type = this.type;
                                }
                            }
                };
            },

            /**
             * Image lazy loading-bar tag
             * @return void
             */
            indicator_loading_bar: function() {
                return {
                    props :['percent']
                };
            },

            /**
             * This will render nontransactable
             * @return void
             */
            indicator_nontransactable: function() {
                return {
                    data   : function() {
                                return {
                                    view_data : {},
                                    is_loading: true
                                };
                            },
                    computed: {
                                err_code: function(){
                                            var vm = this;
                                            if (!vm.is_loading) {
                                                var user_status = vm.view_data.user.derived_status_id;
                                                return vm.view_data.configs.status_codes_list[user_status].err_code;
                                            }
                                        },
                                has_err_code: function() {
                                                return !ps_helper.empty(this.err_code);
                                            }
                            },
                    mounted : function() {
                                var vm = this;

                                ps_model.view_data({
                                    success: function(response) {
                                                vm.view_data  = response;
                                                vm.is_loading = false;
                                            }
                                },['user','configs']);
                            }
                };
            },

            /**
             * indicator onpage error custom tag
             * @return object
             */
            indicator_onpage_error: function() {
                return {
                    props   : { code: { default: ps_language.net_err_code } },
                    computed: {
                                error_message: function() { return ps_language.error(this.code); }
                            },
                };
            },
            /**
             * indicator that compute time for countdown
             * @return object 
             */
            indicator_countdown_timer: function() {
                return {
                    data    : function() {

                            return {
                                computed_time  : {},
                                prev_time_info : {},
                                is_running     : false
                            }

                    },
                    props   : {
                        timeInfo: { default: null },
                        format  : { default: null },
                        retrieve: { default: function() {
                                                return [
                                                    'formatted_hours',
                                                    'formatted_minutes',
                                                    'formatted_seconds',
                                                ]
                                            }
                                }
                    },
                    computed: {
                        has_time: function() {

                               return this.timeInfo != null ;                         
                        }
                        
                    },
                    mounted: function() {

                        this.start_timer();
                    },
                    methods: {
                        start_timer: function() {

                            var vm = this;
                            var stop_timer = function(timer_id){
                                                window.clearInterval(timer_id);
                                                vm.is_running = false;
                                                $(vm.$el).trigger('view_components_stopped');
                                            };

                            var timer_id = window.setInterval(function() {

                                vm.is_running = true;

                                if (vm.has_time) {
                                    if (vm.format === null) {
                                        vm.computed_time =  ps_date.diff_date(vm.timeInfo, new Date(), vm.retrieve);
                                    } else {
                                        vm.computed_time =  ps_date.diff_date(
                                                                vm.timeInfo, 
                                                                ps_date.get_current_date(vm.format), 
                                                                vm.retrieve
                                                            );

                                    }

                                    if (vm.computed_time.difference <= 0) { 
                                        stop_timer(timer_id);
                                    } 

                                } else {
                                    stop_timer(timer_id);
                                    
                                }

                            }.bind(this), 1000);

                        }
                    },
                    watch: {
                        timeInfo: function() {
                            var vm = this;
                            if (!vm.is_running) {
                                vm.start_timer();
                            }
                        }
                    } 
                }

            },


            /**
             * Popover component
             * @return object
             */
            pop_popover: function() {
                return callables.pop_properties('popover');
            },

            /**
             * Tooltip component
             * @return object
             */
            pop_tooltip: function() {
                return callables.pop_properties('tooltip');
            },

            /**
             * Custom tag refresh balance
             * @return void
             */
            options_refresh_balance: function() {
                return {
                    data   : function() { return globals.store.store_fetch('refresh_balance'); },
                    mounted: function() { $(this.$el).on('click', callables.refresh_balance); }
                };
            },

            /**
             * Custom tag logout
             * @return void
             */
            options_logout: function() {
                return {
                    props  : { confirm: { default: false }},
                    mounted: function() {

                        var vm = this;
                        $(vm.$el).on('click', function() {
                            var logout_process = function() {
                                                    ps_popup.toast.open(ps_language.get('messages.logging_out'), {
                                                        title: ps_language.get('language.logout'),
                                                        type : 'exit_to_app'
                                                    });

                                                    ps_model.logout({
                                                        success: function() {
                                                                    window.location = '';
                                                                }
                                                    });
                                                };

                            if (vm.confirm) {
                                
                                ps_popup.modal.open('logout', {
                                    modal_class: 'logout_modal',
                                    closable: false,
                                    header  : ps_language.get('language.logout'),
                                    body    : ps_language.get('messages.want_logout'),
                                    footer  : function(modal_part) {
                                                ps_view.render(modal_part, 'logout_modal_footer', {
                                                    replace : false,
                                                    mounted : function() {
                                                                var vm = this;
                                                                $(vm.$el).find('.ps_js-yes').on('click', function() {
                                                                    ps_popup.modal.close('logout');
                                                                    logout_process();
                                                                });
                                                                $(vm.$el).find('.ps_js-no').on('click', function() {
                                                                    ps_popup.modal.close('logout');
                                                                });
                                                            }
                                                });
                                            }
                                });

                            } else {

                                logout_process();
                            
                            }
                        }); 
                    }
                };
            },

            /**
             * Custom tag usebalance root
             * @return void
             */
            usedbalance_root: function() {
                return {
                    data   : function() {
                                return {
                                    usedBalance   : { list:[] },
                                    joinable_class: 'ps_js-usedbalance_joinable',
                                    view_data     : {},
                                    is_loading    : true
                                };
                            },
                    computed: {
                                length: function() {
                                            if ($.isArray(this.usedBalance.list)  && this.usedBalance.display) {
                                                return this.usedBalance.list.length;
                                            } else {
                                                return 0;
                                            }
                                        },
                                joinable: function() {
                                            var vm        = this;
                                            var length    = vm.length;
                                            var user      = vm.view_data.user;
                                            var joinables = [];

                                            for (var i = 0; i < length; i++) {
                                                var joinable_wallet = vm.usedBalance.list[i].derived_joinable_wallet;
                                                joinables[i] = (joinable_wallet && user.derived_is_transactable);
                                            }

                                            return joinables;
                                        }
                            },
                    mounted: function() {
                                var vm = this;

                                $(vm.$el).on('view_components_refresh', function() {
                                    vm.is_loading = true;
                                    ps_model.view_data({ 
                                        success: function(response) {
                                                    vm.view_data = response;
                                                    ps_model.get_balance({
                                                        success: function(response) {
                                                                    vm.usedBalance = response.usedBalance;
                                                                    vm.usedBalance.display = vm.view_data.configs.usedBalance_display;
                                                                    vm.is_loading  = false;
                                                                }
                                                    });
                                                }
                                    },['user','configs']);
                                });

                                $(vm.$el).trigger('view_components_refresh');
                            },
                    updated: function() {
                                // join buttons
                                var vm = this;
                                $(vm.$el).find('.'+vm.joinable_class).each(function() {
                                    var buttons   = $(this).find('.ps_js-button');

                                    buttons.off('click').on('click', callables.join_event_function(
                                        $(this).attr('data-gid'),
                                        $(this).attr('data-tid'),
                                        $(this).attr('data-tableName')
                                    ));
                                });
                            }
                };
            },

            /**
             * Custom tag usebalance api
             * @return void
             */
            usedbalance_wallets: function() {
                return {
                    data   : function() {
                                return {
                                    wallets_balance : {},
                                    is_loading      : true
                                };
                            },
                    computed: {
                                length: function() {
                                            return Object.keys(this.wallets_balance).length;
                                        }
                            },
                    mounted : function() {
                                var vm = this;

                                $(vm.$el).on('view_components_refresh', function(e, companies) {
                                    vm.is_loading  = true;
                                    ps_model.get_api_balance({
                                        success: function(response) {
                                                    vm.wallets_balance = response.api_balance;
                                                    vm.is_loading      = false;
                                                }
                                    }, companies);
                                });

                                $(vm.$el).trigger('view_components_refresh');
                            }
                };
            },

            /**
             * Table root
             * @return void
             */
            table_root: function() {
                return {
                    data    : function() { return { is_recent: true, show_paging: false }; },
                    props   : {
                                rows              :{}, 
                                columns           :{}, 
                                footerRows        :{}, 
                                footerColumns     :{},
                                loading           :{ default: false },
                                columnAttributes  :{},
                                pageRows          :{},
                                page              :{ default: 1 },
                                total             :{},
                                maxPagingButtons  :{ default:5  },
                                scrollUp          :{},
                                scroller          :{},
                                scrollTransition  :{ default: 'fast' },
                                stickyHeader      :{ default: false  },
                                horizontalScroller:{}
                            },
                    computed: {
                                final_footerColumns     : function() {
                                                            return this.footerColumns || this.columns;
                                                        },
                                recent_loading          : function() {
                                                            return this.is_recent && this.loading;
                                                        },
                                non_recent_loading      : function() {
                                                            return !this.is_recent && this.loading;
                                                        },
                                final_column_attributes : function() {
                                                            return this.columnAttributes || {};
                                                        },
                                final_rows              : function() {
                                                            var vm = this;
                                                            if (!ps_helper.empty(vm.pageRows) && vm.rows>vm.pageRows) {
                                                                return vm.pageRows;
                                                            } else {
                                                                return vm.rows;
                                                            }
                                                        },
                                page_count              : function() {
                                                            var vm = this;
                                                            if (!ps_helper.empty(vm.pageRows)) {
                                                                return Math.ceil(vm.total/vm.pageRows);
                                                            } else {
                                                                return 1;
                                                            }
                                                        },
                                page_first             : function() {
                                                            if (this.page!=1) {
                                                                return 1;
                                                            } else {
                                                                return false;
                                                            }
                                                        }, 
                                page_previous           : function() {
                                                            if (this.page_count) {

                                                                var previous = this.page - 1;

                                                                if (previous < 1) {
                                                                    return false;
                                                                } else {
                                                                    return previous;
                                                                }

                                                            } else {
                                                                return false;
                                                            }
                                                        }, 
                                page_last               : function() {
                                                            if (this.page<this.page_count) {
                                                                return this.page_count;
                                                            } else {
                                                                return false;
                                                            }
                                                        }, 
                                page_next               : function() {
                                                            if (this.page_count) {

                                                                var next = this.page + 1;
                                                                if (next > this.page_count) {
                                                                    return false;
                                                                } else {
                                                                    return next;
                                                                }

                                                            } else {
                                                                return false;
                                                            }
                                                        }, 
                                page_buttons            : function() {
                                                            var vm          = this;
                                                            var max_buttons = vm.maxPagingButtons;

                                                            if (vm.page_count > max_buttons) {

                                                                // place current page at middle if possible
                                                                var max_buttons_divided = (max_buttons/2);
                                                                if ((max_buttons%2) == 0) {
                                                                    // -1 for the current page space
                                                                    var lower_padding = max_buttons_divided - 1;  
                                                                } else {
                                                                    var lower_padding = Math.floor(max_buttons_divided);   
                                                                }
                                                                // +1 for the current page space
                                                                var upper_padding = max_buttons - (lower_padding + 1);

                                                                var start = vm.page - lower_padding;
                                                                var end   = vm.page + upper_padding;

                                                                // start number cannot be less than 0
                                                                // else set it to 1 and add the missing count to end 
                                                                if (start < 1) {
                                                                    var remaining  =  1 - start;
                                                                    start  =  1;
                                                                    end   += remaining;
                                                                }

                                                                // end cannot be higher than page count 
                                                                // if its higher then set to maximum only
                                                                // add remaining to start
                                                                if (end > vm.page_count) {
                                                                    var remaining  = end - vm.page_count;
                                                                    end            = vm.page_count;
                                                                    start          = start - remaining;

                                                                    if (start < 1) {
                                                                        start = 1;
                                                                    }
                                                                }

                                                                var button_range = { start: start, end: end };

                                                            } else {

                                                                var button_range = { start: 1, end: vm.page_count };

                                                            }

                                                            var page_buttons = [];
                                                            for (var i=button_range.start;i <= button_range.end; i++) {
                                                                page_buttons.push(i);
                                                            }

                                                            return page_buttons;
                                                        }
                            },
                    watch   : {
                                loading     : function(is_loading) {
                                                var vm = this;
                                                if (is_loading === false) {
                                                    vm.is_recent = false;
                                                    vm.components_loaded();
                                                } else {

                                                    vm.$nextTick(function() {

                                                        if (vm.scrollUp == true) {

                                                            var table = $(vm.$el).find('.ps_js-table_main_element');

                                                            if (ps_helper.empty(vm.scroller)) {

                                                                var scroller = ps_helper.scrollable_parent(table);

                                                            } else {

                                                                var scroller = ps_helper.scrollable_mend(
                                                                                $(vm.scroller)
                                                                            );
                                                            }        

                                                            ps_helper.animate(
                                                                scroller,
                                                                { scrollTop: 0 },
                                                                vm.scrollTransition
                                                            );
                                                        }

                                                        vm.components_loading();
                                                    });
                                                    
                                                }
                                            }
                            },

                    mounted : function() {
                                var vm = this;

                                if (vm.loading === false) {
                                    vm.components_loaded();
                                }
                                
                                // paging button
                                $(vm.$el).on('click', '.ps_js-paging_button', function() {
                                    var number =  parseInt($(this).attr('data-number'));
                                    $(vm.$el).trigger('view_components_paging', number);
                                });


                                // sticky header
                                if (vm.stickyHeader) {
                                    var selector = vm.horizontalScroller;

                                    if (ps_helper.empty(vm.horizontalScroller)) {
                                        selector = $(vm.$el).attr('data-horizontal-scroller');
                                    }

                                    var table = $(vm.$el).find('.ps_js-table_main_element');
                                    if (ps_helper.empty(selector)) {

                                        var horizontal_scroller = ps_helper.scrollable_parent(table);

                                    } else {


                                        if (selector=='document' || selector=='window') {
                                            var horizontal_scroller = $(window);
                                        } else {
                                            var horizontal_scroller = table.closest(selector);
                                        }
                                    }
                                    
                                    var sticky_header = $(vm.$el).find('.ps_js-table_sticky_header');
                                    horizontal_scroller.on('scroll', function() {
                                        sticky_header.offset({ left:-1*$(this).scrollLeft() });
                                    });
                                }
                            },
                    methods  : {
                                components_loading: function() {
                                    var vm = this;
                                    vm.show_paging = false;
                                    $(vm.$el).trigger('view_components_loading');
                                },

                                components_loaded: function() {
                                    var vm = this;
                                    vm.$nextTick(function() {
                                        vm.show_paging = (vm.page_count>1);
                                        $(vm.$el).trigger('view_components_loaded');
                                    });
                                }
                            }
                }
            },

            /**
             * Custom tag list collapsible
             * @return void
             */
            list_collapsible: function() {
                return {
                    data   : function() {
                                return { is_active: false, collapsible_id: ps_helper.uniqid() };
                            },
                    mounted : function() {
                                var vm      = this;
                                var trigger = $(vm.$el).find('.ps_js-components_trigger.ps_js-'+vm.collapsible_id);

                                trigger.on('click', function(e) {
                                    e.stopPropagation();
                                    var is_active = vm.is_active ? false : true;
                                    vm.trigger(is_active);
                                });

                                $(vm.$el).on('view_components_hide', function(e) {
                                    e.stopPropagation();
                                    vm.trigger(false);
                                });

                                $(vm.$el).on('view_components_collapse', function(e) {
                                    e.stopPropagation();
                                    vm.trigger(true);
                                });
                            },
                    methods : {
                                trigger: function(to_status) {
                                            var vm = this;
                                            if (vm.is_active != to_status) {
                                                vm.is_active = to_status;
                                                $(vm.$el).trigger('view_components_change', to_status);
                                            }
                                        }   
                            }
                };
            },

            /**
             * Sticky element 
             * 
             * @return void
             */
            position_sticky: function() {
                return {
                    data   : function() {
                                return { is_sticky: false, scroll_direction: null };
                            },
                    props  : { stickOn: {}, scroller: {}},
                    mounted: function() {
                                var vm = this;

                                $(vm.$el).on('view_components_direction', function(e, direction) {
                                    vm.scroll_direction = direction;
                                });

                                if (ps_helper.empty(vm.stickOn)) {
                                    var element = $(vm.$el);
                                } else {
                                    var element = $(vm.$el).find(vm.stickOn);
                                }

                                if (ps_helper.empty(vm.scroller)) {
                                    var scroller = ps_helper.scrollable_parent(element);
                                } else {
                                    var scroller = ps_helper.scrollable_mend($(vm.scroller));
                                }        
                                
                                var last_scroll_top = 0;
                                scroller.on('scroll', function(e, change_direction) {
                                    var scroll_top  = scroller.scrollTop();

                                    // check if needs to change direction
                                    if (change_direction!==false) {
                                        vm.scroll_direction = (scroll_top > last_scroll_top) ? 'down' : 'up';
                                    }

                                    last_scroll_top     = scroll_top;

                                    if (scroller[0] === window) {
                                        var offset = element.offset().top - scroll_top;
                                    } else {
                                        var offset = element.offset().top - scroller.offset().top;
                                    }
                                    
                                    vm.is_sticky = (offset < 0 && scroll_top > 0);
                                });
                                
                                var delay_id = ps_helper.uniqid();
                                $(window).on('resize', function() {
                                    ps_helper.event_delay(function() {
                                        scroller.trigger('scroll', false);
                                    }, 100, delay_id);
                                });

                                scroller.trigger('scroll');
                            }
                }
            },
            
            /**
             * Iframe that can rescale to minimum resolution
             * @return object 
             */
            iframe_rescale: function() {
                return {
                    data    : function() {
                                return {
                                    scale_adjustment    : null,
                                    height_adjustment   : null,
                                    width_adjustment    : null,
                                    parent_height       : null,
                                    parent_width        : null,
                                    parent_margin_bottom: null,
                                    parent_margin_right : null
                                };
                            },
                    props   : { 
                                scrolling : { default:'yes' }, 
                                src       : {}, 
                                viewWidth : {}, 
                                viewHeight: {}
                            },
                    computed: {
                                rescale_property: function() {
                                                    var vm = this;

                                                    if (!ps_helper.empty(vm.viewWidth)) {
                                                        return 'width';
                                                    } else if (!ps_helper.empty(vm.viewHeight)) {
                                                        return 'height';
                                                    } else {
                                                        return null;
                                                    }
                                                }
                            },
                    mounted : function() {
                                var vm = this;

                                var delay_id = ps_helper.uniqid();
                                $(window).on('resize', function() {
                                    ps_helper.event_delay(function() {
                                        vm.rescale();
                                    }, 100, delay_id);
                                });

                                vm.rescale();

                                $(vm.$el).on('view_components_rescale', vm.rescale);
                            },
                    methods : {
                                reset   : function() {
                                            var vm = this;

                                            vm.scale_adjustment    = null;
                                            vm.height_adjustment   = null;
                                            vm.width_adjustment    = null;
                                            vm.parent_height       = null;
                                            vm.parent_width        = null;
                                            vm.parent_margin_bottom= null;
                                            vm.parent_margin_right = null;
                                        },

                                rescale: function() {
                                            var vm = this;

                                            vm.reset();

                                            vm.$nextTick(function() {

                                                if (vm.rescale_property!==null) {
                                                    if (vm.rescale_property === 'width') {
                                                        vm.rescale_width();
                                                    } else {
                                                        vm.rescale_height();
                                                    }
                                                } 

                                            });
                                        },

                                rescale_width: function() {
                                            var vm = this;

                                            var iframe          = $(vm.$el).find('.ps_js-iframe_element');
                                            var iframe_width    = parseFloat(iframe.outerWidth());
                                            var iframe_height   = parseFloat(iframe.outerHeight());

                                            if (iframe_width < vm.viewWidth) {


                                                var width_percentage = (iframe_width / vm.viewWidth);

                                                // get the aspect ratio
                                                var height_size = vm.viewWidth * iframe_height / iframe_width;
                                                var height_diff = height_size  - iframe_height;
                                                var width_diff  = vm.viewWidth - iframe_width;

                                                // for iframe
                                                vm.scale_adjustment  = width_percentage;
                                                vm.height_adjustment = height_size;
                                                vm.width_adjustment  = vm.viewWidth;


                                                // for parent
                                                var iframe_bounding_size = iframe[0].getBoundingClientRect();
                                                vm.parent_height         = height_diff + iframe_bounding_size.height;
                                                vm.parent_width          = width_diff  + iframe_bounding_size.width;
                                                vm.parent_margin_bottom  = 0-height_diff;
                                                vm.parent_margin_right   = 0-width_diff;

                                            } else {

                                                vm.reset();

                                            }
                                        },


                                rescale_height: function() {
                                            var vm = this;

                                            var iframe          = $(vm.$el).find('.ps_js-iframe_element');
                                            var iframe_width    = parseFloat(iframe.outerWidth());
                                            var iframe_height   = parseFloat(iframe.outerHeight());
                                            
                                            if (iframe_height < vm.viewHeight) {

                                                var height_percentage = (iframe_height / vm.viewHeight);

                                                // get the aspect ratio
                                                var width_size  = vm.viewHeight * iframe_width / iframe_height;
                                                var height_diff = vm.viewHeight - iframe_height;
                                                var width_diff  = width_size    - iframe_width;

                                                // for iframe
                                                vm.scale_adjustment  = height_percentage;
                                                vm.height_adjustment = vm.viewHeight;
                                                vm.width_adjustment  = width_size;


                                                // for parent
                                                var iframe_bounding_size = iframe[0].getBoundingClientRect();
                                                vm.parent_height         = height_diff + iframe_bounding_size.height;
                                                vm.parent_width          = width_diff  + iframe_bounding_size.width;
                                                vm.parent_margin_bottom  = 0-height_diff;
                                                vm.parent_margin_right   = 0-width_diff;

                                            } else {

                                                vm.reset();

                                            }
                                        }
                            }
                }

            }
        }
    }; 

    return {
        css           : callables.css,
        tag_properties: callables.tag_properties
    };

});
