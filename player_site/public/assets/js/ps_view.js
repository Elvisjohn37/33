/**
 * This will manage all the template rendering of our site
 *
 * NOTE: 1. All items added to ajax, vue, etc. that is used only for this module should have 'psv_',
 *          This is to avoid conflict when we accept any setup outside this module.
 *       2. custom tag are dynamically registered in globals.template_config.
 *       3. custom tags pattern 'ps' + type + data-tag (e.g. data-tag = 'input' for type 'form': '<ps-form-input>').
 *       4. Use multiple v-bind:class intead of one and separating it in new line which gives error in IE.
 * @author PS Team
 */
define('ps_view', ['jquery','ps_helper','vue','ps_model','ps_gconfig'], function() {

    'use strict';

    var $          = arguments[0];
    var ps_helper  = arguments[1];
    var vue        = arguments[2];
    var ps_model   = arguments[3];
    var ps_gconfig = arguments[4];

    var globals   = {   
                        debug            : false,
                        template_config  : { 
                                            toast      : { html:'ps_popup_html'       , selector:'.ps_js-toast'       },
                                            modal      : { html:'ps_popup_html'       , selector:'.ps_js-modal'       },
                                            menu       : { html:'ps_navigation_html'  , selector:'.ps_js-menu'        },
                                            page       : { html:'ps_navigation_html'  , selector:'.ps_js-page'        },
                                            css        : { html:'ps_components_html'  , selector:"[rel='stylesheet']" },
                                            loader     : { html:'ps_components_html'  , selector:'.ps_js-loader'      },
                                            logout     : { html:'ps_components_html'  , selector:'.ps_js-logout'      },
                                            news       : { html:'ps_news_html'        , selector:'.ps_js-news'        },
                                            jackpot    : { html:'ps_jackpot_html'     , selector:'.ps_js-jackpot'     },
                                            products   : { html:'ps_products_html'    , selector:'.ps_js-products'    },
                                            support    : { html:'ps_support_html'     , selector:'.ps_js-support'     },
                                            chatbox    : { html:'ps_chatbox_html'     , selector:'.ps_js-chatbox'     },
                                            language   : { html:'ps_language_html'    , selector:'.ps_js-language'    },
                                            banner     : { html:'ps_banner_html'      , selector:'.ps_js-banner'      },
                                            account    : { html:'ps_account_html'     , selector:'.ps_js-account'     },
                                            report     : { html:'ps_report_html'      , selector:'.ps_js-report'      },
                                            navigation : { html:'ps_navigation_html'  , selector:'.ps_js-navigation'  },
                                            carousel   : { html:'ps_carousel_html'    , selector:'.ps_js-carousel'    },
                                            avatar     : { html:'ps_avatar_html'      , selector:'.ps_js-avatar'      },
                                            image      : { html:'ps_image_html'       , selector:'.ps_js-image'       },
                                            media      : { html:'ps_media_html'       , selector:'.ps_js-media'       },
                                            help_menu  : { html:'ps_help_html'        , selector:'.ps_js-help_menu'   },
                                            games      : { html:'ps_games_html'       , selector:'.ps_js-games'       },
                                            sports     : { html:'ps_sports_html'      , selector:'.ps_js-sports'      },
                                            tangkas    : { html:'ps_tangkas_html'     , selector:'.ps_js-tangkas'     },
                                            promo      : { html:'ps_promo_html'       , selector:'.ps_js-promo'       },
                                            savvy      : { html:'ps_savvy_html'       , selector:'.ps_js-savvy'       },
                                            live_togel : { html:'ps_live_togel_html'  , selector:'.ps_js-live_togel'  },
                                            tournament : { html:'ps_tournament_html'  , selector:'.ps_js-tournament'  },
                                            game_window: { html:'ps_game_window_html' , selector:'.ps_js-game_window' },
                                            news_tab   : { html:'ps_news_tab_html'    , selector:'.ps_js-news_tab'    },
                                            multiplayer: { html:'ps_multiplayer_html' , selector:'.ps_js-multiplayer' },
                                            live_casino: { html:'ps_live_casino_html' , selector:'.ps_js-live_casino' },

                                            error_page_view : { 
                                                                html    :'ps_error_page_html', 
                                                                selector:'.ps_js-error_page_view'
                                                            },
                                            error_page      : { 
                                                                html    :'ps_error_page_html',
                                                                selector:'.ps_js-error_page' 
                                                            },

                                            news_title: {
                                                            html    : 'ps_news_html', 
                                                            selector: '.ps_js-news_title'        
                                                        },

                                            news_body: {
                                                            html    : 'ps_news_html', 
                                                            selector: '.ps_js-news_body'        
                                                        },

                                            logout_modal_footer: { 
                                                                    html    : 'ps_components_html', 
                                                                    selector:  '.ps_js-logout_modal_footer'      
                                                                },


                                            sound_manager: { 
                                                            html    : 'ps_media_html', 
                                                            selector: '.ps_js-sound_manager'       
                                                        },

                                            promo_modal : { 
                                                            html    : 'ps_promo_html', 
                                                            selector: '.ps_js-promo_modal'      
                                                        },

                                            skill_games: { 
                                                            html    : 'ps_skill_games_html', 
                                                            selector: '.ps_js-skill_games'       
                                                        },
                                            
                                            announcement : { 
                                                                html    : 'ps_announcement_html', 
                                                                selector: '.ps_js-announcement'      
                                                        },

                                            running_modal_footer : { 
                                                                    html    :'ps_games_template_html', 
                                                                    selector:'.ps_js-running_modal_footer'       
                                                                },

                                            games_template : { 
                                                                html    :'ps_games_template_html', 
                                                                selector:'.ps_js-games_template'       
                                                            },

                                            game_preview_modal : { 
                                                                    html    :'ps_games_template_html', 
                                                                    selector:'.ps_js-game_preview_modal'       
                                                                },


                                            faq_header : { 
                                                            html    :'ps_help_html', 
                                                            selector:'.ps_js-faq_header'   
                                                        },

                                            faq_body   : { 
                                                            html    :'ps_help_html', 
                                                            selector:'.ps_js-faq_body'   
                                                        },

                                            gaming_rules_header : { 
                                                                    html    :'ps_help_html', 
                                                                    selector:'.ps_js-gaming_rules_header'   
                                                                },

                                            gaming_rules_body   : { 
                                                                    html    :'ps_help_html', 
                                                                    selector:'.ps_js-gaming_rules_body'   
                                                                },

                                            game_guide_header : { 
                                                                    html    :'ps_help_html', 
                                                                    selector:'.ps_js-game_guide_header'   
                                                                },

                                            game_guide_body   : { 
                                                                    html    :'ps_help_html', 
                                                                    selector:'.ps_js-game_guide_body'   
                                                                },

                                            game_guide_images : { 
                                                                    html    :'ps_help_html', 
                                                                    selector:'.ps_js-game_guide_images'   
                                                                },

                                            game_guide_tree : { 
                                                                html    :'ps_help_html', 
                                                                selector:'.ps_js-game_guide_tree'   
                                                            },

                                            terms_conditions_body: { 
                                                                    html    :'ps_help_html', 
                                                                    selector:'.ps_js-terms_conditions_body'   
                                                                },

                                            contact_us_body: { 
                                                                html    :'ps_help_html', 
                                                                selector:'.ps_js-contact_us_body'   
                                                            },

                                            report_bet_link: {
                                                                html    :'ps_report_html', 
                                                                selector:'.ps_js-report_bet_link'      
                                                            },

                                            displayname_modal: { 
                                                                html    :'ps_displayname_html', 
                                                                selector:'.ps_js-displayname_modal'      
                                                            },

                                            displayname_modal_footer: { 
                                                                        html    :'ps_displayname_html', 
                                                                        selector:'.ps_js-displayname_modal_footer'      
                                                                    },

                                            avatar_modal: { 
                                                            html    :'ps_avatar_html', 
                                                            selector:'.ps_js-avatar_modal'      
                                                        },

                                            avatar_crop: { 
                                                            html    :'ps_avatar_html',
                                                            selector:'.ps_js-avatar_crop_modal'      
                                                        },

                                            avatar_webcam: { 
                                                            html    :'ps_avatar_html', 
                                                            selector:'.ps_js-avatar_webcam_modal'      
                                                        },

                                            avatar_webcam_footer: { 
                                                            html    :'ps_avatar_html', 
                                                            selector:'.ps_js-avatar_webcam_footer'      
                                                        },
 
                                            lastresult: { 
                                                            html    :'ps_lastresult_html', 
                                                            selector:'.ps_js-lastresult'
                                                        },

                                            latest_transactions: { 
                                                                    html    :'ps_latest_transactions_html', 
                                                                    selector:'.ps_js-latest_transactions'
                                                                },

                                            refresh_balance : { 
                                                                html    :'ps_components_html', 
                                                                selector:'.ps_js-refresh_balance'
                                                            },

                                            carousel_circle: { 
                                                                html    :'ps_carousel_html', 
                                                                selector:'.ps_js-carousel_circle'
                                                            },


                                            carousel_prev: { 
                                                            html    :'ps_carousel_html', 
                                                            selector:'.ps_js-carousel_control_prev'
                                                        },

                                            carousel_next: { 
                                                            html    :'ps_carousel_html', 
                                                            selector:'.ps_js-carousel_control_next'
                                                        },

                                            small_notice: { 
                                                            html    :'ps_components_html', 
                                                            selector:'.ps_js-small_notice'
                                                        },

                                            login_form  : { 
                                                            html    :'ps_login_form_html', 
                                                            selector:'.ps_js-login'
                                                        },

                                            login_captcha: { 
                                                            html    :'ps_login_form_html', 
                                                            selector:'.ps_js-login_captcha'
                                                        },


                                            login_captcha_footer: { 
                                                                    html    :'ps_login_form_html', 
                                                                    selector:'.ps_js-login_captcha_footer'
                                                                },

                                            resend_email_footer: { 
                                                                    html    :'ps_login_form_html', 
                                                                    selector:'.ps_js-resend_email_footer'
                                                                },

                                            captcha: { 
                                                        html    :'ps_components_html', 
                                                        selector:'.ps_js-captcha'
                                                    },

                                            forgot_password: { 
                                                                html    :'ps_forgot_password_html', 
                                                                selector:'.ps_js-forgot_password'
                                                            },

                                            forgot_password_footer: { 
                                                                        html    :'ps_forgot_password_html', 
                                                                        selector:'.ps_js-forgot_password_footer'
                                                                    },

                                            register: { 
                                                        html    :'ps_register_html', 
                                                        selector:'.ps_js-register'
                                                    },

                                            register_footer: { 
                                                                html    :'ps_register_html', 
                                                                selector:'.ps_js-register_footer'
                                                            },

                                            ingame_balance: { 
                                                                html    :'ps_ingame_balance_html', 
                                                                selector:'.ps_js-ingame_balance'
                                                            },

                                            accept_terms_page: { 
                                                                    html    :'ps_accept_terms_html', 
                                                                    selector:'.ps_js-accept_terms_page'       
                                                                },

                                            accept_terms: { 
                                                            html    :'ps_accept_terms_html', 
                                                            selector:'.ps_js-accept_terms_tags'       
                                                        },

                                            change_credentials: { 
                                                                    html    :'ps_change_credentials_html', 
                                                                    selector:'.ps_js-change_credentials'       
                                                                },

                                            expired_password: { 
                                                                html    :'ps_expired_password_html', 
                                                                selector:'.ps_js-expired_password'       
                                                            },
                                                            
                                            reset_password: {
                                                                html    :'ps_forgot_password_html', 
                                                                selector:'.ps_js-reset_password' 
                                                            },

                                            reset_password_footer: {
                                                                    html    :'ps_forgot_password_html', 
                                                                    selector:'.ps_js-reset_password_footer' 
                                                                },

                                            session_timeout_footer: {
                                                                        html    :'ps_popup_html', 
                                                                        selector:'.ps_js-session_timeout_footer' 
                                                                    },

                                            client_status_footer: {
                                                                    html    :'ps_popup_html', 
                                                                    selector:'.ps_js-client_status_footer' 
                                                                },

                                            banner_modal_body: { 
                                                                html     : 'ps_banner_html', 
                                                                selector : '.ps_js-banner_modal_body'
                                                            },
                                            resend_link: {
                                                                html    :'ps_account_html', 
                                                                selector:'.ps_js-resend_email_link'      
                                                            }
                                        },

                        template_cache       : {},
                        registered_custom_tag: {},
                        custom_tag_pending   : {},
                        html_bundle          : null
                    };

    var callables = {
        
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will get all bundled html keys
         * @return array
         */
        html_bundle: function() {
            if (globals.html_bundle === null) {
                globals.html_bundle = $.isPlainObject(ps_gconfig.html_bundle)? Object.keys(ps_gconfig.html_bundle) : [];
            } 

            return globals.html_bundle;
        },

        /**
         * This will get template string using template key
         * @param  string/object  template_object_key 
         * @param  function       callback   
         * @return void
         */
        get_template: function(template_object_key, callback) {
            if ($.isFunction(callback)) {

                if ($.isPlainObject(template_object_key)) {
                    if (!globals.template_config.hasOwnProperty(template_object_key.template_key)) {
                        globals.template_config[template_object_key.template_key] = template_object_key;
                    }
                    var template_key = template_object_key.template_key;
                } else {
                    var template_key = template_object_key;
                }

                // check if template_key is in bundled_html
                var html_path = globals.template_config[template_key].html;
                if (ps_helper.in_array(html_path, callables.html_bundle())) {
                    html_path = ps_gconfig.html_bundle[html_path];
                }
                
                require(['template!' + html_path], function (template) {
                    // build template
                    if (ps_helper.empty(globals.template_cache[template_key])) {
                        var selector = globals.template_config[template_key].selector;
                        globals.template_cache[template_key] = ps_helper.get_node(template, selector);
                    }

                    var template = globals.template_cache[template_key];
                    callback(template);
                });
            }
        },

        /**
         * This will render templates to our view
         * @param  dom      container    
         * @param  string   template_key 
         * @param  object   options      
         * @return void
         */
        render: function (container, template_key, options) {
            options = options || {};

            callables.get_template(template_key, function(template) {
                // preset options, can't be altered 
                ps_helper.assoc_merge(options, { template: template.string });

                var fill = function() {
                            // initial rendering, with template and exclusive data
                            container.each(function() {
                                if (options.replace == false || container.is('body, head, html')) {
                                    callables.fill($('<p></p>').appendTo(this), options);
                                } else {
                                    callables.fill($(this), options);
                                }
                            });
                        };

                    // custom elements
                    callables.custom_tags($(template.dom), fill);

            });
        },

        /**
         * This will fill {{ keyword }} text in a doms with sharable data from model
         * Almost like render but does not force template_key
         * @param  dom      scope
         * @param  object   options [vue + onprogress]
         * @return void
         */
        fill: function(container, options) {

            // this will be triggered everytime container template's mounted
            var update_progress = function(percent) {
                                    if ($.isFunction(options.onprogress)) {
                                        options.onprogress(percent);
                                    }

                                    // make sure 100% progress will only be called once
                                    if (ps_helper.is_percent_loaded(percent)) {
                                        delete options.onprogress;
                                        if ($.isFunction(options.onload)) {
                                            options.onload(options, container, percent);
                                            delete options.onload;
                                        }
                                    }
                                };

            if (container.length > 0) {

                var view_data = ps_model.shared();
                options.data = options.data || {};

                if (!ps_helper.empty(view_data)) {
                    if (!ps_helper.empty(options.data.shared)) {
                        callables.debug('Dont use options.data.shared in rendering, it will be overwritten!');
                    }
                    options.data.shared = view_data;
                }

                var fill_info = {container: container, count: container.length, class: ps_helper.uniqid()};
                // temporary element for couting remaining unrendered elements
                $('<p></p>').appendTo(container).addClass(fill_info.class);

                var orig_mounted = options.mounted

                options.mounted  = function() {
                                    if ($.isFunction(orig_mounted)) {
                                        orig_mounted.apply(this, arguments);
                                    }

                                    $(this.$el).find('.' + fill_info.class).remove();
                                    var remaining = $('.' + fill_info.class).length;
                                    var processed = fill_info.count - remaining;
                                    var percent   = ps_helper.get_percentage(fill_info.count, processed);
                                    update_progress(percent);
                                };

                // initial rendering, with template and exclusive data
                container.each(function() { 
                    (function (element) {
                        callables.custom_tags($(element), function() { 
                            options.el = element;
                            new vue(options); 
                        });
                    }(this));
                });

            } else {

                // if no container found then just update as done
                update_progress(100);

            }

        },

        /**
         * This will register vue custom component if not yet existing
         * @param  string   type     
         * @param  function callback 
         * @return void
         */
        load_custom_tag: function(type, callback) {
            if (globals.registered_custom_tag[type] !== true) {

                if (ps_helper.empty(globals.custom_tag_pending[type])) {
                    globals.custom_tag_pending[type] = [];
                    globals.custom_tag_pending[type].push(callback);
                    
                    // some big components was separated in different module located in 'plugins/' js
                    var component_module_name = 'ps_'+ type;
                    if (!ps_helper.in_array(component_module_name, ps_gconfig.require_modules)) {
                        var component_module_name ='ps_view_components';
                    }
                    
                    require([component_module_name], function(component_module) {

                        // Register default template if not existing
                        if (globals.template_config.hasOwnProperty(type)) {
                            var template_config = type;
                        } else {
                            var template_key    = type + '_custom_tags';
                            var template_config = {
                                                    template_key: template_key,
                                                    html        : 'ps_components_html',
                                                    selector    : '.ps_js-' + template_key
                                                };
                        }

                        callables.get_template(template_config, function(template) {

                            /**
                             |------------------------------------------------------------------------------------------
                             | TAG REGISTRATION FUNCTION
                             |------------------------------------------------------------------------------------------
                             */
                            var register_tags = function() {
                                // Get all available children
                                $(template.dom).children().each(function() {
                                    var tag = $(this).data('tag');
                                    $(this).attr('data-tag', null);

                                    // get custom poperties
                                    if (!ps_helper.empty(tag)) {

                                        var properties = {};

                                        // tag_method sample: 
                                        // <navigation type><sidebar-tree tag> = navigation_sidebar_tree
                                        var tag_method_name =  type+'_'+ps_helper.replace_all(tag, '-', '_');
                                        if (component_module_name == 'ps_view_components') {
                                            var tag_method = component_module.tag_properties[tag_method_name];
                                        } else {
                                            var tag_method = component_module[tag_method_name];
                                        }
                                        
                                        if ($.isFunction(tag_method)) {
                                            var tag_properties = tag_method();

                                            if ($.isPlainObject(tag_properties)) {
                                                properties = tag_properties;
                                            } else {
                                                callables.debug(
                                                    component_module+'.tag_properties.'+tag_method
                                                    +'should return an object!'
                                                );
                                            } 
                                        }

                                        // add template to properties
                                        ps_helper.assoc_merge(properties,  {
                                            template: ps_helper.dom_stringify($(this))
                                        });

                                        // fixed data properties, cannot be overwritten
                                        var orig_data   = properties.data;
                                        properties.data = function () {
                                                            if ($.isFunction(orig_data)) {
                                                                var orig_properties = orig_data();
                                                            } else if($.isPlainObject(orig_data)) {
                                                                var orig_properties = orig_data;
                                                            } else {
                                                                var orig_properties = {};
                                                            }

                                                            return ps_helper.assoc_merge(orig_properties, {
                                                                shared: ps_model.shared()
                                                            });
                                                        };
                                        vue.component('ps-'+ps_helper.replace_all(type,'_', '-')+'-'+tag,properties);
                                    }
                                });

                                globals.registered_custom_tag[type] = true;
                                globals.custom_tag_pending[type].forEach(function(pending_callback) {
                                    if ($.isFunction(pending_callback)) {
                                        pending_callback(type);
                                    }
                                });

                                delete globals.custom_tag_pending[type];
                            }; 

                            /**
                             |------------------------------------------------------------------------------------------
                             | NESTED TAG DETECTION BEFORE REGISTRATION
                             |------------------------------------------------------------------------------------------
                             */
                            // remove same type to prevent exceeding call stack size
                            callables.custom_tags($(template.dom), register_tags, [type]);
                        });
                    });

                } else {

                    globals.custom_tag_pending[type].push(callback);

                }
            } else {

                if ($.isFunction(callback)) {
                   callback(type);
                }
            }
        },

        /**
         * This will register multiple vue custom component if not yet existing
         * @param  dom      element  
         * @param  function callback
         * @param  array    exclude custom tags to be excluded
         * @return void
         */
        custom_tags: function(element, callback, exclude) {

            // get tags array
            var custom_tags_elements = element.find('[data-custom-tags]').addBack('[data-custom-tags]');
            var custom_tags_array    = [];
            custom_tags_elements.each(function() {
                var custom_tags_string = $(this).attr('data-custom-tags');
                if (!ps_helper.empty(custom_tags_string)) {
                    custom_tags_array = $.merge(custom_tags_array, custom_tags_string.split(','));
                }
            });
            
            // remove excludecluded tags
            if ($.isArray(exclude) && exclude.length>0) {
                custom_tags_array = $.grep(custom_tags_array, function(value) {
                                        return !ps_helper.in_array(value, exclude);
                                    });
            }

            if (custom_tags_array.length > 0 && $.isFunction(callback)) {
                // get all elements to be loaded
                var types_obj = {};
                custom_tags_array.forEach(function(value) {
                    types_obj[value] = 0;
                });

                custom_tags_array.forEach(function(value) {
                    callables.load_custom_tag(value, function(type) {
                        types_obj[type]    = 100;
                        var percent_loaded = ps_helper.get_percent_average(types_obj);

                        if (percent_loaded === 100 && $.isFunction(callback)) {
                            var loaded_callback  = callback;
                                        callback = undefined;

                            loaded_callback();
                        }
                    });
                });

            } else {

                if ($.isFunction(callback)) {
                    callback();
                }

            }
        }
    };

    /**
     |------------------------------------------------------------------------------------------------------------------
     | Exports
     |------------------------------------------------------------------------------------------------------------------
     */
    return {  
        fill        : callables.fill,
        render      : callables.render,
        get_template: callables.get_template
    };

});




