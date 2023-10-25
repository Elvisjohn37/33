/**
 * This contains requireJS initialization.
 * With the help of requireJS we will load our core JS.
 * This will also add 'ps_is_rso' cookie.
 *
 * FF modules are loaded here:
 * - ps_gconfig
 * - all modules starting with ps_wm_ via requireJS_noncompatibles() function
 * 
 * "We will start a spark that will ignite a Raging Inferno!"
 * @author PS Team
 */
window.onload = (function(onload) {

    // Get loader data
    var init_js        = document.getElementById('ps_js-initjs');
    var window_modules = {};
    var wm_prefix      = 'ps_wm_';

    // rso paths
    var rso_url        = window.ps_global_config.rso.urls.original.assets 
                       + window.ps_global_config.rso.script_paths.js;
    var fallback_url   = window.ps_global_config.rso.urls.fallback.assets 
                       + window.ps_global_config.rso.script_paths.js;
    var is_use_fallback= false; 

    // set require default congfigs that should be here before loading the data-main
    window.require     = {waitSeconds: 0};

    // requireJS config setter (This will be triggered on requireJS onload)
    window.requireJS_config = function () {
        
        // check if we will use fallback
        if (is_use_fallback) {
            var html_root = window.ps_global_config.rso.urls.fallback.assets+'template/';
        } else {
            var html_root =  window.ps_global_config.rso.urls.original.assets+'template/';
        }

        // check if we will use minified
        if (window.ps_global_config.rso.compress) {
            html_root += 'min/';
        }

        var config = {
                        paths      : { 
                                        ps_login_form_html          : html_root + 'ps_login_form.html',
                                        ps_forgot_password_html     : html_root + 'ps_forgot_password.html',
                                        ps_register_html            : html_root + 'ps_register.html',
                                        ps_banner_html              : html_root + 'ps_banner.html',
                                        ps_account_html             : html_root + 'ps_account.html',
                                        ps_avatar_html              : html_root + 'ps_avatar.html',
                                        ps_displayname_html         : html_root + 'ps_displayname.html',
                                        ps_report_html              : html_root + 'ps_report.html',
                                        ps_help_html                : html_root + 'ps_help.html',
                                        ps_announcement_html        : html_root + 'ps_announcement.html',
                                        ps_ingame_balance_html      : html_root + 'ps_ingame_balance.html',
                                        ps_games_html               : html_root + 'ps_games.html',
                                        ps_games_template_html      : html_root + 'ps_games_template.html',
                                        ps_skill_games_html         : html_root + 'ps_skill_games.html',
                                        ps_sports_html              : html_root + 'ps_sports.html',
                                        ps_tangkas_html             : html_root + 'ps_tangkas.html',
                                        ps_promo_html               : html_root + 'ps_promo.html',
                                        ps_accept_terms_html        : html_root + 'ps_accept_terms.html',
                                        ps_change_credentials_html  : html_root + 'ps_change_credentials.html',
                                        ps_expired_password_html    : html_root + 'ps_expired_password.html',
                                        ps_live_togel_html          : html_root + 'ps_live_togel.html',
                                        ps_master_html              : html_root + 'ps_masterhtml.html',
                                        ps_tournament_html          : html_root + 'ps_tournament.html',
                                        ps_game_window_html         : html_root + 'ps_game_window.html',
                                        ps_error_page_html          : html_root + 'ps_error_page.html',
                                        ps_news_tab_html            : html_root + 'ps_news_tab.html', 
                                        ps_multiplayer_html         : html_root + 'ps_multiplayer.html',
                                        ps_news_tab_html            : html_root + 'ps_news_tab.html',
                                        ps_live_casino_html         : html_root + 'ps_live_casino.html'
                                    },
                        bundles    : {},
                        shim       : { 
                                        'bootstrap' : { 
                                                        deps   : ['jquery'] 
                                                    },
                                        'cropper'   : { 
                                                        deps   : ['jquery'] 
                                                    },
                                        'jquery_ui': {
                                                        deps   : ['jquery'] 
                                                    },
                                        'jquery_raf': {
                                                        deps   : ['jquery'] 
                                                    },
                                        'socketio': {
                                                        exports: 'io'
                                                    }
                                    },

                        config      : {
                                        // This config is needed for allowing require-text cross domain origin
                                        // Need to allow in 'Access-Control-Allow-Origin' header
                                        template: {       
                                                    useXhr: function (url, protocol, hostname, port) {
                                                        return true;
                                                    }
                                                }
                                    }
                    };

        if (window.ps_global_config.rso.compress) {
            
            // js bundles
            config.bundles.ps_master = [
                                        'ps_helper','ps_model','ps_window','ps_view','ps_view_components',
                                        'ps_websocket','ps_date', 'ps_validator','ps_google_analytics',
                                        'ps_localstorage', 'ps_store',

                                        // libraries
                                        'bootstrap','jquery','jquery_raf','jquery_ui','vue','template',
                                        'cropper','ps_carousel','ps_image','ps_media','ps_chatbox',
                                        'ps_language','ps_popup','socketio',

                                        // soon this will be on plugin.js
                                        'ps_jackpot','ps_lastresult','ps_latest_transactions',
                                        'ps_news','ps_products','ps_support','ps_navigation','ps_savvy'
                                    ];

            // html bundle, this is handled by ps_view.js
            window.ps_global_config.html_bundle = {
                                                    ps_popup_html              : 'ps_master_html',
                                                    ps_navigation_html         : 'ps_master_html',
                                                    ps_components_html         : 'ps_master_html',
                                                    ps_news_html               : 'ps_master_html',
                                                    ps_jackpot_html            : 'ps_master_html',
                                                    ps_latest_transactions_html: 'ps_master_html',
                                                    ps_lastresult_html         : 'ps_master_html',
                                                    ps_products_html           : 'ps_master_html',
                                                    ps_carousel_html           : 'ps_master_html',
                                                    ps_support_html            : 'ps_master_html',
                                                    ps_chatbox_html            : 'ps_master_html',
                                                    ps_language_html           : 'ps_master_html',
                                                    ps_image_html              : 'ps_master_html',
                                                    ps_media_html              : 'ps_master_html',
                                                    ps_websocket_html          : 'ps_master_html',
                                                    ps_savvy_html              : 'ps_master_html'
                                                };

        } else {
            
            // js owned libraries
            config.paths.ps_helper           = 'ps_helper';
            config.paths.ps_model            = 'ps_model';
            config.paths.ps_window           = 'ps_window';
            config.paths.ps_view             = 'ps_view';
            config.paths.ps_view_components  = 'ps_view_components';
            config.paths.ps_websocket        = 'ps_websocket';
            config.paths.ps_date             = 'ps_date';
            config.paths.ps_google_analytics = 'ps_google_analytics';
            config.paths.ps_localstorage     = 'ps_localstorage';
            config.paths.ps_validator        = 'ps_validator';
            config.paths.ps_store            = 'ps_store';

            // js 3rd party libraries
            config.paths.bootstrap     = 'libraries/bootstrap';
            config.paths.jquery        = 'libraries/jquery';
            config.paths.jquery_ui     = 'libraries/jquery-ui.min';
            config.paths.jquery_raf    = 'libraries/jquery-requestAnimationFrame';
            config.paths.vue           = 'libraries/vue';
            config.paths.template      = 'libraries/require-text';
            config.paths.cropper       = 'libraries/cropper.min';
            config.paths.socketio      = 'libraries/socket.io';

            // js plugins
            config.paths.ps_carousel            = 'plugins/ps_carousel';
            config.paths.ps_image               = 'plugins/ps_image';
            config.paths.ps_media               = 'plugins/ps_media';
            config.paths.ps_chatbox             = 'plugins/ps_chatbox';
            config.paths.ps_language            = 'plugins/ps_language';
            config.paths.ps_popup               = 'plugins/ps_popup'
            config.paths.ps_jackpot             = 'plugins/ps_jackpot';
            config.paths.ps_lastresult          = 'plugins/ps_lastresult';
            config.paths.ps_latest_transactions = 'plugins/ps_latest_transactions';
            config.paths.ps_news                = 'plugins/ps_news';
            config.paths.ps_products            = 'plugins/ps_products';
            config.paths.ps_support             = 'plugins/ps_support';
            config.paths.ps_navigation          = 'plugins/ps_navigation';
            config.paths.ps_savvy               = 'plugins/ps_savvy';

            // html files
            config.paths.ps_popup_html                = html_root + 'ps_popup.html';
            config.paths.ps_navigation_html           = html_root + 'ps_navigation.html';
            config.paths.ps_components_html           = html_root + 'ps_components.html';
            config.paths.ps_news_html                 = html_root + 'ps_news.html';
            config.paths.ps_jackpot_html              = html_root + 'ps_jackpot.html';
            config.paths.ps_latest_transactions_html  = html_root + 'ps_latest_transactions.html';
            config.paths.ps_lastresult_html           = html_root + 'ps_lastresult.html';
            config.paths.ps_products_html             = html_root + 'ps_products.html';
            config.paths.ps_carousel_html             = html_root + 'ps_carousel.html';
            config.paths.ps_support_html              = html_root + 'ps_support.html';
            config.paths.ps_chatbox_html              = html_root + 'ps_chatbox.html';
            config.paths.ps_language_html             = html_root + 'ps_language.html';
            config.paths.ps_image_html                = html_root + 'ps_image.html';
            config.paths.ps_media_html                = html_root + 'ps_media.html';
            config.paths.ps_websocket_html            = html_root + 'ps_websocket.html';
            config.paths.ps_savvy_html                = html_root + 'ps_savvy.html';
            config.paths.ps_multiplayer_html          = html_root + 'ps_multiplayer.html';
        }

        // 3rd party Libraries
        config.paths.youtube     = '//www.youtube.com/iframe_api?noext';
        config.paths.livechatinc = 'https://cdn.livechatinc.com/tracking';

        // PS plugins
        config.paths.ps_login_form     = 'plugins/ps_login_form';
        config.paths.ps_avatar         = 'plugins/ps_avatar';
        config.paths.ps_displayname    = 'plugins/ps_displayname';
        config.paths.ps_games_template = 'plugins/ps_games_template';

        // PS pages
        config.paths.ps_register          = 'pages/ps_register';
        config.paths.ps_forgot_password   = 'pages/ps_forgot_password';
        config.paths.ps_banner            = 'pages/ps_banner';
        config.paths.ps_account           = 'pages/ps_account';
        config.paths.ps_report            = 'pages/ps_report';
        config.paths.ps_help              = 'pages/ps_help';
        config.paths.ps_ingame_balance    = 'pages/ps_ingame_balance';
        config.paths.ps_announcement      = 'pages/ps_announcement';
        config.paths.ps_games             = 'pages/ps_games';
        config.paths.ps_skill_games       = 'pages/ps_skill_games';
        config.paths.ps_live_casino       = 'pages/ps_live_casino';
        config.paths.ps_sports            = 'pages/ps_sports';
        config.paths.ps_tangkas           = 'pages/ps_tangkas';
        config.paths.ps_promo             = 'pages/ps_promo';
        config.paths.ps_accept_terms      = 'pages/ps_accept_terms';
        config.paths.ps_change_credentials= 'pages/ps_change_credentials';
        config.paths.ps_expired_password  = 'pages/ps_expired_password';
        config.paths.ps_live_togel        = 'pages/ps_live_togel';
        config.paths.ps_tournament        = 'pages/ps_tournament';
        config.paths.ps_game_window       = 'pages/ps_game_window';
        config.paths.ps_error_page        = 'pages/ps_error_page';
        config.paths.ps_news_tab          = 'pages/ps_news_tab';
        config.paths.ps_multiplayer       = 'pages/ps_multiplayer';

        require.config(config);

        window.ps_global_config.require_modules = Object.keys(config.paths);
        for (var bundle in config.bundles) {
            window.ps_global_config.require_modules = window.ps_global_config
                                                            .require_modules
                                                            .concat(config.bundles[bundle]);
        }

        // Define global_config module as our main config source
        // So data are used for moduke initialization only
        define('ps_gconfig', window.ps_global_config);

        // Store require config
        define('requireJS_config', config);

        window.ps_global_config = undefined;
        delete window.ps_global_config;

        // modules that are temporarily saved in window
        for (var module_name in window_modules) {
            var window_var = window_modules[module_name];

            if (window.hasOwnProperty(window_var)) {
                // add prefix ps_wm_ to mark this as from window_modules
                define(wm_prefix + module_name, window[window_var]);

                window[window_var] = undefined;
                delete window[window_var];
            }
        };
        
        window.requireJS_config = undefined;
        delete window.requireJS_config;
    };

    /**
     * Sometimes there are some libraries that are not compatible with requireJS and needs to be loaded first
     * Just like autobahn.js, all exports on that module will be assigned to define() via requireJS
     * NOTE: modules listed here must be temporary only, find a good solutions keep this list empty.
     * @param  function callback after all non compatible js was loaded
     * @return void
     */
    function requireJS_noncompatibles(callback) {
    
        var non_compatibles = null;

        if (non_compatibles == null) {
            callback();

        } else {
            for (var module in non_compatibles) {
                var module_info = non_compatibles[module];
                // Create script tag
                var script_tag = document.createElement('script');
                script_tag.setAttribute('type',  'text/javascript');
                script_tag.setAttribute('async', true);

                (function(module_info, module) {

                    var onload_callback_key = wm_prefix + module;

                    function loaded(is_loaded) {

                        // module export
                        if (is_loaded && module_info.hasOwnProperty('module_export')) {
                            // delete from queue and callback
                            delete non_compatibles[module];
                            delete window[onload_callback_key];

                            window_modules[module] = module_info.module_export;
                        }

                        // callback
                        if (Object.keys(non_compatibles).length <= 0 && typeof callback === "function") {
                            var callback_function = callback;
                                         callback = undefined;

                            callback_function();
                        }
                        
                    };

                    // instead of having onload, this should be called at the end of script file being loaded.
                    window[onload_callback_key] = loaded;

                    // prepare fallback if ever original rso fails
                    script_tag.onerror = function() { 
                                            this.parentNode.removeChild(this);

                                            var fallback_tag = document.createElement('script');
                                            fallback_tag.setAttribute('type',  'text/javascript');
                                            fallback_tag.setAttribute('async', true);

                                            fallback_tag.onerror = loaded(false);

                                            fallback_tag.setAttribute('src', fallback_url + module_info.file);

                                            document.getElementsByTagName('head')[0].appendChild(fallback_tag);
                                        };
                                        
                }(module_info, module));

                script_tag.setAttribute('src', rso_url + module_info.file);
                document.getElementsByTagName('head')[0].appendChild(script_tag);
            }
        }
    };
    
    // Window onload function
    return function(event) {
        
        onload && onload(event);

        // load all js that has no requireJS compatibility first before loading and starting PS
        requireJS_noncompatibles(function() {
            // requireJS and main JS filename
            var require_js   = 'libraries/require.js';
            var core_js      = window.ps_global_config.rso.compress ? 'ps_master.js' : 'ps_core.js';

            // Create script tag
            var script_tag = document.createElement('script');
            script_tag.setAttribute('type',  'text/javascript');
            script_tag.setAttribute('async', true);

            // prepare fallback if ever original rso fails
            script_tag.onerror = function() { 
                                    document.cookie = 'ps_is_rso=0;';
                                    this.parentNode.removeChild(this);

                                    var fallback_tag = document.createElement('script');
                                    fallback_tag.setAttribute('type',  'text/javascript');
                                    fallback_tag.setAttribute('async', true);

                                    fallback_tag.setAttribute('data-main', fallback_url + core_js);
                                    fallback_tag.setAttribute('src',       fallback_url + require_js);
                                    document.getElementsByTagName('head')[0].appendChild(fallback_tag);
                                    is_use_fallback = true;
                                };

            // try to load original rso 
            document.cookie = 'ps_is_rso=1;';
            script_tag.setAttribute('data-main', rso_url + core_js);
            script_tag.setAttribute('src',       rso_url + require_js);

            document.getElementsByTagName('head')[0].appendChild(script_tag);

            if (window.ps_global_config.rso.compress) {
                // Think if this is really necessary
                init_js.parentNode.removeChild(init_js);
            }
        });
    };
}(window.onload));