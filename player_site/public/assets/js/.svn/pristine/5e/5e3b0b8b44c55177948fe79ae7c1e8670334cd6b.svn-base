/**
 * This will start the our App, by doing the ff:
 * - Commit requireJS configs
 * - Load all PS core JS
 * - Load Ajax notifications
 * - Ajax initial site data
 * - Load layouts
 * - Load contents (components, plugins)
 *
 * @author PS Team
 */

/**
 |----------------------------------------------------------------------------------------------------------------------
 | Load config, located in ps_init.js
 |----------------------------------------------------------------------------------------------------------------------
 */
window.requireJS_config();

/**
 |----------------------------------------------------------------------------------------------------------------------
 | Load PS
 |----------------------------------------------------------------------------------------------------------------------
 */
require([
    'jquery',
    'ps_helper',
    'ps_model',
    'ps_view',
    'ps_popup',
    'ps_gconfig',
    'ps_navigation',
    'ps_language',
    'ps_view_components',
    'ps_window',
    'ps_google_analytics',
    'ps_localstorage'
], function() {

    'use strict';

    var $                   = arguments[0];
    var ps_helper           = arguments[1];
    var ps_model            = arguments[2];
    var ps_view             = arguments[3];
    var ps_popup            = arguments[4];
    var ps_gconfig          = arguments[5];
    var ps_navigation       = arguments[6];
    var ps_language         = arguments[7];
    var ps_view_components  = arguments[8];
    var ps_window           = arguments[9];
    var ps_google_analytics = arguments[10];
    var ps_localstorage     = arguments[11];
    
    var globals   = { 
                        main_deps: { 
                                        'core_dependencies': 0,
                                        'configurations'   : 0,
                                        'subscriptions'    : 0,
                                        'initial_data'     : 0,
                                        'layout'           : 0,
                                        'navigation'       : 0,
                                        'content'          : 0,
                                        'sockets'          : 0,
                                        'analytics'        : 0,
                                        'localstorage'     : 0       
                                    }
                    };
    var callables = {

        /**
         * The callback everytime a dependcy part is loaded
         * @param  string dependency 
         * @param  int    percent   
         * @return void
         */
        dependency_loaded: function(dependency, percent_loaded) {

            // if percent is less than 0, it means there an error
            if (percent_loaded < 0) {

                $('body').addClass('ps_js-body-loaded').addClass('ps_js-body-error');
                $(window).trigger('core_error');

                // toast an error
                ps_popup.toast.open('There is an error loading this dependency: ' + dependency);

            }

            globals.main_deps[dependency] = ps_helper.set_default(percent_loaded, 100);
            var percent                   = ps_helper.get_percent_average(globals.main_deps);

            ps_helper.console('Loading ' + dependency + ': ' + globals.main_deps[dependency] + '%');

            if (percent == 100) {

                // unload screen here
                $('body').addClass('ps_js-body-loaded').addClass('ps_js-body-success');
                $(window).trigger('core_success');
                ps_helper.console(' Site has loaded! ', 'success');
                
            }
        },

        /**
         * This will iterate over flashed session data and perform an action
         * @param  object flash
         * @return void
         */
        flashed_sessions: function(flash) {
            if ($.isPlainObject(flash)) {
                for (var type in flash) {
                    var flash_item = flash[type];

                    switch (type) {
                        case 'success'       : ps_popup.ajax_success_notification(flash_item.message); break;
                        case 'error'         : ps_popup.ajax_error_notification(flash_item.err_code);  break;
                        case 'reset_password': 

                            require(['ps_forgot_password'], function(ps_forgot_password) {
                                ps_forgot_password.activate_reset_password(flash_item);
                            });      

                            break;
                    }

                }
            }
        }
    };

    callables.dependency_loaded('core_dependencies');
    
    /**
     |------------------------------------------------------------------------------------------------------------------
     | Execute Configurations
     |------------------------------------------------------------------------------------------------------------------
     */
    /**
     * disable console log if debugging is false
     * NOTE: always include "jquery" in dependency if its needed
     * WARNING: this can still be enabled through "man in the middle" attack
     */
    if (ps_gconfig.debug != true) {
        window.console.log =  function(){};
    }

    callables.dependency_loaded('configurations');

    /**
     |------------------------------------------------------------------------------------------------------------------
     | Initial data and contents
     |------------------------------------------------------------------------------------------------------------------
     */
    ps_model.view_data({
        progress: function(percent_loaded) { callables.dependency_loaded('initial_data', percent_loaded); },
        success : function (view_data) {
                    var is_mobile = view_data.site.is_mobile ? ' Mobile' : '';
                    ps_helper.console(
                        '  '
                        + view_data.site.name
                        + is_mobile
                        + ' v'
                        + view_data.site.version
                        + '  '
                    , 'site_version');

                    var is_mobile_device =  ps_helper.detect_mobile();

                    if (view_data.site.mobile_redirect == true && is_mobile_device == true) {

                        window.location = view_data.site.domains.mobile;

                    } else {

                        /**
                         |----------------------------------------------------------------------------------------------
                         | Global event subscriptions to different modules
                         |----------------------------------------------------------------------------------------------
                         */
                        ps_model.subscribe('fail', function(response) { 
                            ps_popup.ajax_error_notification(response.err_code); 
                        });
                        ps_model.subscribe('error', function(response) { 
                            ps_popup.ajax_error_notification(ps_language.net_err_code); 
                        });
                        ps_model.subscribe('success', function(response) { 
                            ps_popup.ajax_success_notification(response.message); 
                        });

                        ps_navigation.subscribe('deactivate', function(response) {
                            // remove the default toast
                            ps_popup.toast.close();
                        });

                        callables.dependency_loaded('subscriptions');

                        /**
                         |----------------------------------------------------------------------------------------------
                         | Main CSS
                         |----------------------------------------------------------------------------------------------
                         */
                        ps_view_components.css(view_data.view_css.main, {  
                            onload : function() { callables.dependency_loaded('layout', 100); },
                            onerror: function() { callables.dependency_loaded('layout', -1);  }
                        });


                        /**
                         |----------------------------------------------------------------------------------------------
                         | Localstorage
                         |----------------------------------------------------------------------------------------------
                         | Clear localstorage when auth changed
                         */
                        var has_user_data = $.isPlainObject(view_data.user);
                        if (has_user_data && ps_localstorage.get('core_auth') != view_data.user.is_auth) {
                            ps_localstorage.remove_all();
                            ps_localstorage.set('core_auth', view_data.user.is_auth, true);
                        }
                        callables.dependency_loaded('localstorage');

                        /**
                         |----------------------------------------------------------------------------------------------
                         | Site monitoring like google analytics
                         |----------------------------------------------------------------------------------------------
                         */
                        if ($.isPlainObject(view_data.google_analytics)) {
                            ps_google_analytics.init();
                        }

                        if ($.isPlainObject(view_data.google_tagmanager)) {
                            ps_google_analytics.init_tagmanager();
                        }

                        callables.dependency_loaded('analytics');

                        /**
                         |----------------------------------------------------------------------------------------------
                         | Navigation and Content
                         |----------------------------------------------------------------------------------------------
                         */
                        
                        // add view type class
                        $('body').addClass('ps_js-core_view_'+view_data.route.view_type);

                        // add mobile device class
                        if (is_mobile_device) {
                            $('body').addClass('ps_js-is_mobile');
                        }

                        // navigation and initial module
                        ps_navigation.run_hash_handler(view_data, {

                            onload  : function() {

                                        callables.dependency_loaded('navigation');

                                        // load body content
                                        ps_view.fill($('.ps_js-render').removeClass('ps_js-render'), {
                                            onprogress:function(percent_loaded) {
                                                callables.dependency_loaded('content', percent_loaded);
                                            }
                                        });

                                    },

                            onerror : function() { callables.dependency_loaded('navigation', -1); }

                        });

                        /**
                         |----------------------------------------------------------------------------------------------
                         | Sockets
                         |----------------------------------------------------------------------------------------------
                         */
                        
                        if (view_data.websocket) {
                            require(['ps_websocket'], function(ps_websocket) {
                                ps_websocket.global_subscriptions();
                            });  
                        }

                        callables.dependency_loaded('sockets');


                        /**
                         |----------------------------------------------------------------------------------------------
                         | Process flashed sessions
                         |----------------------------------------------------------------------------------------------
                         */
                        callables.flashed_sessions(view_data.flash);
                    }
                }
    });
});

