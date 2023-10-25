/**
 * This will handle PS navigation tools
 * Available: Menu, Sidebar, Pages
 *
 * ps_navigation module is a core and also serve as plugin
 * - Our core JS must run ps_navigation.run_hash_manager() to handle the site pages.
 * - While menu and sidebars is rendered using plugin format.
 * 
 * @author PS Team
 */
define('ps_navigation', ['jquery','ps_helper', 'ps_model', 'ps_view', 'ps_language'], function () {

    'use strict';

    var $           = arguments[0];
    var ps_helper   = arguments[1];
    var ps_model    = arguments[2];
    var ps_view     = arguments[3];
    var ps_language = arguments[4];

    var globals   = { debug: false, is_hash_handled: false, subscriptions: {}, default_location: false, is_first_load: true };
    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will get the default location hash base on 'navigation' and 'route' view_data
         * @param  object view_data 
         * @return string
         */
        default_location: function(view_data) {

            if (globals.default_location == false) {

                if (view_data.navigation.hashes.hasOwnProperty(view_data.route.default_location)) {

                    globals.default_location = view_data.route.default_location;

                } else {

                    if (!ps_helper.empty(view_data.navigation.menu.primary)) {

                        globals.default_location = view_data.navigation.menu.primary[0].hash;

                    } else if(!ps_helper.empty(view_data.navigation.menu.secondary)) {

                        globals.default_location = view_data.navigation.menu.secondary[0].hash;

                    } else if(!ps_helper.empty(view_data.navigation.menu.hidden)) {

                        globals.default_location = view_data.navigation.menu.hidden[0].hash;

                    } else {

                        globals.default_location = Object.keys(view_data.navigation.hashes)[0];

                    }

                }

            } 

            return globals.default_location;
        },

        /**
         * This will add hashchange event to our window;
         * @param   view_data
         * @return  void
         */
        run_hash_handler: function(view_data, callbacks) {

            if (globals.is_hash_handled) {
                callables.debug("Hash already handled no need to run ps_navigation.run_hash_handler more than once.");
                return false;
            }

            globals.is_hash_handled = true;

            var pages = $('#ps_js-navigation-pages');

            if (pages.length <= 0) {

                callables.debug('No pages container found, please create html element with id ps_js-navigation-pages');

                if ($.isFunction(callbacks.onerror)) {
                    callbacks.onerror();
                }

            }

            ps_view.render(pages, 'page', {

                data    : {
                            actives  : view_data.navigation.actives,
                            page_list: view_data.navigation.pages,
                            scroller         : pages.attr('data-scroller')          || null,
                            scroll_transition: pages.attr('data-scroll-transition') || 0,
                            is_first_load: true
                        },

                computed: {
                            is_active:  function() {
                                            var is_actives = {};

                                            for (var page in this.page_list) {
                                                is_actives[page] = (page == this.actives.page);
                                            }

                                            return is_actives;
                                        },
                            active_index: function() {  
                                            var index = 0; 
                                            for (var page in this.page_list) {

                                                if (page == this.actives.page) {
                                                    return index;
                                                }

                                                index++;
                                            }

                                            return index;
                                        }
                        },

                    watch: {
                        is_first_load: function() {
                            // this will trigger only if hash is home
                            if (callables.default_location(view_data) == '#home') {
                                $(window).trigger('hashchange');
                            }
                        }
                    },

                mounted: function() {
                            var vm = this;

                            // incase page is inside carousel
                            // NOTE: when using carousel as page container 
                            //       it is recommended to create 1  menu hash only per page.   
                            //       Example of multiple menu hash per page is the banner,
                            //       banner page setup should be avoided if carousel is used to animate page
                            $(vm.$el).find('.ps_js-page_carousel_root').on('carousel_moved', function(e, index) {
                                var page = vm.page_list[Object.keys(vm.page_list)[index]];
                                if ($.isPlainObject(page) && ps_helper.empty(page.active_main_hash)) {
                                    window.location = page.menu_hashes[0];
                                }
                            });

                            if (ps_helper.empty(vm.scroller)) {

                                var scroller = ps_helper.scrollable_parent($(vm.$el));

                            } else {

                                var scroller = ps_helper.scrollable_mend($(vm.scroller));
                                
                            }        

                            // hash change event
                            $(window).on('hashchange', function() {
                                var default_location = callables.default_location(view_data);

                                if (ps_helper.empty(window.location.hash) && !vm.is_first_load) {

                                    var hash_info        = view_data.navigation.hashes[default_location];
                                } else {
                                    var hash_info        = view_data.navigation.hashes[window.location.hash];

                                }
                                
                                // hash not found in directory
                                if (vm.is_first_load) {
                                    
                                    if (ps_helper.empty(hash_info)) {
                                        if (ps_helper.empty(view_data.navigation.actives.menu)) {
                                            callables.debug(
                                                window.location.hash 
                                                + ' Hash not found in directory, redirecting to ' 
                                                + default_location
                                            );
                                            if (default_location !== '#home') {
                                                window.location = default_location;
                                            }
                                        } else {
                                            callables.debug(
                                                window.location.hash 
                                                + ' Hash not found in directory, previous menu will still be active ' 
                                                + view_data.navigation.actives.menu
                                            );
                                        }
                                        vm.is_first_load = false;
                                        return false;
                                    }
                                }
                                //  if (ps_helper.empty(hash_info)) {
                                //     if (ps_helper.empty(view_data.navigation.actives.menu)) {
                                //         callables.debug(
                                //             window.location.hash 
                                //             + ' Hash not found in directory, redirecting to ' 
                                //             + default_location
                                //         );
                                //         window.location = default_location;
                                //     } else {
                                //         callables.debug(
                                //             window.location.hash 
                                //             + ' Hash not found in directory, previous menu will still be active ' 
                                //             + view_data.navigation.actives.menu
                                //         );
                                //     }
                                //     return false;
                                // }


                                ps_helper.animate(scroller, { scrollTop: 0 }, vm.scroll_transition);
                                callables.activate_hash(hash_info, view_data);
                            });
    
                            // trigger hashchange for the first time
                            $(window).trigger('hashchange'); //triggered game_guide


                            if ($.isFunction(callbacks.onload)) {
                                callbacks.onload();
                            }
                        }
            });
        },

        /** 
         * This will activate hash compnents
         * @param  object   hash_info 
         * @param  object   nav_data
         * @param  string   hash_opener  
         * @return void
         */
        activate_hash: function(hash_info, view_data, hash_opener) {
            var current_actives = $.extend(true, {}, view_data.navigation.actives);

            switch (hash_info.type) {
                case 'floating':

                    if (ps_helper.empty(current_actives.menu)) {

                        var default_location = callables.default_location(view_data);

                        // activate default menu
                        callables.debug('Activating floating menu while no active main menu,' 
                            +' activating '+default_location+' first.');

                        var default_page = view_data.navigation.hashes[default_location];

                        callables.activate_hash(default_page, view_data);
                    }

                    // deactivate current floating
                    if (!ps_helper.empty(current_actives.floating)) {
                        ps_model.update_floating_sidebar(null);
                        callables.deactivate_page(view_data.navigation.hashes[current_actives.floating]);
                    }

                    ps_model.update_nav_floating(hash_info.hash);
                    callables.trigger_page_activation(hash_info, hash_opener);

                    break;

                case 'menu' :

                    // deactivate if there's floating
                    var current_active_floating = current_actives.floating;
                    if (!ps_helper.empty(current_active_floating)) {

                        // deactivate only current floating if have 
                        // It  means there's a floating page previously and then came back only to this 
                        ps_model.update_floating_sidebar(null);
                        ps_model.update_nav_floating(null);
                        callables.deactivate_page(view_data.navigation.hashes[current_actives.floating]);

                    } 

                    var hash_final_form = ps_model.hash_final_form(hash_info.hash);
                    if (ps_model.active_main_hash() !== hash_final_form || ps_helper.empty(current_active_floating)) {
                        
                        // deactivate current page
                        if (!ps_helper.empty(current_actives.menu)) {
                            ps_model.update_nav_sidebar(null);
                            callables.deactivate_page(view_data.navigation.hashes[current_actives.menu]);
                        }

                        ps_model.update_nav_page(hash_info.page);
                        ps_model.update_nav_menu(hash_info.hash);
                        callables.trigger_page_activation(hash_info, hash_opener);

                    }

                    break;

                case 'floating sidebar': 
                case 'sidebar': 
                    callables.activate_hash(view_data.navigation.hashes[hash_info.menu_hash],view_data,hash_info.hash);
                    break;
            }
        },

        /**
         * This will trigger activate_page with proper parameters
         * @param  object hash_info  
         * @param  string hash_opener 
         * @return void
         */
        trigger_page_activation: function(hash_info, hash_opener) {
            if (ps_helper.empty(hash_info.sidebars) || hash_info.first_sidebar === false) {

                callables.activate_page(hash_info, hash_info.hash);
                ps_model.update_main_hash(hash_info.page,hash_info.hash);

            } else {

                if (ps_helper.empty(hash_opener)) {
                    hash_opener = hash_info.sidebars[0]
                }

                if (hash_info.floating) {
                    ps_model.update_floating_sidebar(hash_opener);
                } else {
                    ps_model.update_nav_sidebar(hash_opener);
                }

                callables.activate_page(hash_info, hash_opener);
                ps_model.update_main_hash(hash_info.page,hash_opener);
            }
        },

        /**
         * This will call the activate method of a page
         * @param  object   hash_info   
         * @param  string   hash_opener the hash that triggers the page to open
         * @return void
         */
        activate_page: function(hash_info, hash_opener) {
            require(['ps_' + hash_info.page], function(page) {
                if (!ps_helper.empty(page) && $.isFunction(page.activate)) {
                    page.activate(hash_opener, hash_info);
                    callables.debug('activate ' + hash_info.page);
                }
            });

            callables.fire_subscriptions('activate', hash_info);
        },

        /**
         * This is like activate_hash that is triggered via hashchange 
         * except the activation will be binded to the menu DOM itself
         * @param  dom      menu_item
         * @param  object   hash_info   
         * @param  string   hash_opener the hash that triggers the page to open
         * @return void
         */
        direct_trigger_activation: function(menu_item, hash_info, hash_opener) {
            require(['ps_' + hash_info.page], function(page) {
                if (!ps_helper.empty(page) && $.isFunction(page.activate)) {
                    var direct_trigger = function() {
                                            // check callables.activate_page
                                            page.activate(hash_opener, hash_info);
                                            callables.debug('activate ' + hash_info.page);
                                            callables.fire_subscriptions('activate', hash_info);
                                        };

                    menu_item.off('click', direct_trigger).on('click', direct_trigger);
                }
            });

        },

        /**
         * Event when indirect trigger menu was clicked
         * Indirect menu means the real activation wasn't on click event, instead its on hashchange
         * @param  dom   menu_item
         * @return void
         */
        hash_trigger_activation: function(menu_item) {
            var hash_trigger =  function() {
                                    if (window.location.hash == $(this).attr('href')) {
                                        console.log('indirect trigger');
                                        $(window).trigger('hashchange');
                                    }
                                };

            menu_item.off('click', hash_trigger).on('click', hash_trigger);
        },

        /**
         * This will call the deactivate method of a page
         * @param  object   hash_info   
         * @return void
         */
        deactivate_page: function(hash_info) {
            ps_model.update_main_hash(hash_info.page, null);

            require(['ps_' + hash_info.page], function(page) {
                if (!ps_helper.empty(page) && $.isFunction(page.deactivate)) {
                    page.deactivate(hash_info);
                    callables.debug('deactivate ' + hash_info.page);
                }
            });

            callables.fire_subscriptions('deactivate', hash_info);
        },

        /**
         * This will fire event subscriptions
         * @param  string event_name 
         * @param  object hash_info 
         * @return void
         */
        fire_subscriptions: function(event_name, hash_info) {
            if (!ps_helper.empty(globals.subscriptions[event_name])) {
                for (var id in globals.subscriptions[event_name]) {
                    if ($.isFunction(globals.subscriptions[event_name][id])) {
                        globals.subscriptions[event_name][id](hash_info);
                    }
                }

                callables.debug('navigation '+event_name+' event subscriptions fired!');
            }
        },

        /** 
         * This will open menu
         * @return void
         */
        open_side_menu: function() {
            ps_popup.modal.open('side_menu', {
                modal_class: 'side_menu_root',
                closable   : false,
                header     : function(modal_part) {
                                ps_view.render(modal_part, 'side_menu_header', {
                                    replace: false,
                                    mounted: function() {
                                                var vm = this;
                                                $(vm.$el).find('.ps_js-close_menu').on('click', function() {
                                                    ps_popup.modal.close('side_menu', 'side_menu');
                                                });
                                            }
                                })
                            },
                body       : function(modal_part) {
                                ps_view.render(modal_part, 'side_menu_body', {
                                    replace: false,
                                    data   : { view_data : {}, is_loading: true },
                                    mounted: function() {
                                                var vm = this;
                                                ps_model.view_data({
                                                    success: function(response) {
                                                        vm.view_data  = response;
                                                        vm.is_loading = false;

                                                        vm.$nextTick(function() {
                                                            $(vm.$el).find('.ps_js-menu_item').on('click', function() {
                                                                ps_popup.modal.close('side_menu', 'side_menu');
                                                            });
                                                        });
                                                    }
                                                },['navigation']);
                                            }
                                })
                            },
                    bind   : {
                                shown: function() {
                                        var modal_body = $(this).find('.ps_js-side_menu_body');
                                        
                                        if (modal_body.length > 0) {
                                            ps_helper.animate(
                                                ps_helper.scrollable_parent(modal_body),
                                                { scrollTop: 0 },
                                                'fast'
                                            );
                                        }
                                    }
                            }
            }, 'side_menu');
        }
    };

    return {
        run_hash_handler: callables.run_hash_handler,

        /**
         * Subscribe to page navigation events
         * @param  string   event_name [description]
         * @param  function callback   [description]
         * @param  object   settings   [description]
         * @return void
         */
        subscribe: function(event_name, callback, settings) {
            if ($.isFunction(callback)) {

                if (ps_helper.empty(globals.subscriptions[event_name])) {
                    globals.subscriptions[event_name] = {};
                }

                settings    = settings    || {};
                settings.id = settings.id || ps_helper.uniqid();
                globals.subscriptions[event_name][settings.id] = callback;

            } else {
                callables.debug('ps_navigation.subscribe 2nd argument must be a function');
            }
        },

        /**
         * This will unsubscribe event callback with given id
         * @param  string event_name   
         * @param  string id 
         * @return void
         */
        unsubscribe: function(event_name, id) {
            if (!ps_helper.empty(globals.subscriptions[event_name])) { 
                delete globals.subscriptions[event_name][id];
            }
        },

        /**
         * Custom tag navigation menu
         * @return void
         */
        navigation_menu: function() {
            return {
                data    : function() {
                            return {
                                is_loading : true,
                                view_data  : {}
                            };
                        },
                computed: {
                            navigation: function() {
                                            return this.view_data.navigation || {};
                                        },
                            navigation_menu: function() {
                                            return this.navigation.menu || {};
                                        },
                            menu_list : function() {
                                        return this.navigation_menu[this.item] || [];
                                    },
                            actives  : function() {
                                        return this.navigation.actives || {};
                                    },
                            is_active: function() {
                                        var vm = this;
                                        return this.menu_list.map(function(menu) {
                                            if(menu['hash'] === undefined) {
                                                return false;
                                            }
                                            return (menu.hash == vm.actives.menu) 
                                                || (menu.hash == vm.actives.floating);
                                        });
                                    },
                            has_item : function() {
                                        return (this.menu_list.length > 0);
                                    },
                            products : function(){
                                  return this.navigation_menu.products || [];
                                },
                            is_submenu_active : function() {
                                        var vm = this;
                                        var result = false;
                                        vm.products.forEach(function(e) {
                                            result = e.hash == vm.actives.menu ? true : false;
                                        });
                                        return result;
                            }
                        },
                props   :{ item:{} , expectedCount:{ default:0 } },
                mounted : function() { 
                                var vm = this;

                                ps_model.view_data({
                                    success: function (view_data) {
                                                vm.view_data  = view_data;
                                                vm.is_loading = false;

                                                // For main menu
                                                vm.$nextTick(function() {
                                                    vm.menu_list.forEach(function(menu) {
                                                        var menu_item = $(vm.$el).find('.ps_js-menu_'+menu.id)

                                                        if (menu.direct_trigger) {
                                                            
                                                            callables.direct_trigger_activation(
                                                                menu_item,
                                                                view_data.navigation.hashes[menu.hash],
                                                                menu.hash
                                                            );

                                                        } else {

                                                            callables.hash_trigger_activation(menu_item);

                                                        }
                                                    });

                                                    // For sub menu
                                                    vm.products.forEach(function(menu) {
                                                        var menu_item = $(vm.$el).find('.ps_js-menu_'+menu.id)

                                                        if (menu.direct_trigger) {
                                                            
                                                            callables.direct_trigger_activation(
                                                                menu_item,
                                                                view_data.navigation.hashes[menu.hash],
                                                                menu.hash
                                                            );

                                                        } else {

                                                            callables.hash_trigger_activation(menu_item);

                                                        }
                                                    });
                                                });
                                            }
                                }, ['navigation']);
                            }

            };
        },

        /**
         * Custom tag navigation page
         * NOTE: this doesn't handle floating hashes
         * @return void
         */
        navigation_page: function() {
            return {
                data    : function() { return { is_loading: true, navigation: {} }; },
                computed: {
                            page_data       : function() {
                                                var vm = this;
                                                return vm.is_loading ? {} : vm.navigation.pages[vm.page];
                                            },
                            hash_has_sidebar: function() {
                                                var vm = this;
                                                if (vm.page_data.hasOwnProperty('menu_hashes')) {
                                                    var hash_has_sidebar = {};
                                                    vm.page_data['menu_hashes'].forEach(function(hash, index) {
                                                        var sidebars = vm.navigation.hashes[hash].sidebars;
                                                        if ($.isArray(sidebars)) {
                                                            hash_has_sidebar[hash] = sidebars.length>0;
                                                        } else {
                                                            hash_has_sidebar[hash] = false;
                                                        }
                                                    });
                                                    return hash_has_sidebar;
                                                } else {
                                                    return {};
                                                }
                                            },
                            sidebar_indexes: function() {
                                                var vm = this;
                                                if (vm.page_data.hasOwnProperty('menu_hashes')) {
                                                    var sidebar_indexes = {};
                                                    vm.page_data['menu_hashes'].forEach(function(hash, index) {
                                                        sidebar_indexes[hash] = {};

                                                        var sidebars = vm.navigation.hashes[hash].sidebars;
                                                        sidebars.forEach(function(sidebar, index) {
                                                            sidebar_indexes[hash][sidebar] = index;
                                                        });
                                                    });

                                                    return sidebar_indexes;
                                                } else {
                                                    return {};
                                                }
                                            },
                            routes: function () {
                                        var vm          = this;
                                        var route_array = [];

                                        if (vm.page == vm.navigation.actives.page) {

                                            // root
                                            var root = vm.navigation.hashes[vm.navigation.actives.menu];
                                            route_array.push(root);

                                            // sidebar/children 
                                            if (!ps_helper.empty(vm.navigation.actives.sidebar)) {
                                                var sidebar = vm.navigation.hashes[vm.navigation.actives.sidebar];
                                                var menu    = vm.navigation.hashes[sidebar.menu_hash];

                                                // check if sidebar really belongs to this page
                                                if (menu.page === vm.page) {

                                                    var last_parent_hash = root.hash;

                                                    // if sidebar has parents
                                                    sidebar.parents.forEach(function(parent) {
                                                        route_array.push(vm.navigation.hashes[parent.hash]);
                                                    });

                                                    // finally the sidebar
                                                    route_array.push(sidebar);
                                                }
                                            }
                                        }

                                        return route_array;
                                    },

                            is_loaded: function() {
                                        var vm = this;
                                        return !vm.is_loading && !vm.navigation.pages[vm.page].is_rendering;
                                    },
                            is_active: function() {
                                        var vm = this;

                                        if (!vm.is_loading) {
                                            var actives   = vm.navigation.actives;
                                            var page_data = vm.page_data;
                                            return (ps_helper.in_array(actives.menu, page_data.menu_hashes) 
                                                || ps_helper.in_array(actives.floating, page_data.menu_hashes));

                                        } else {

                                            return false;

                                        }
                                    }
                        },
                props   : ['page'],
                mounted : function() {
                            var vm = this;

                            ps_model.view_data({
                                success: function(response) {
                                            vm.navigation = response.navigation;
                                            vm.is_loading = false;
                                        }
                            }, ['navigation'])

                        }
            };
        },

        /**
         * Custom tag navigation sidebar
         * @return void
         */
        navigation_sidebar: function() {
            return {
                data    : function() { return { is_loading: true, sidebars:{}, view_data:{} }; },
                props   : { hash:{}, collapsible: { default: true }},
                mounted : function() {
                            var vm = this;
                            ps_model.view_data({
                                success: function(response) {
                                            vm.view_data  = response;

                                            var productID = response.navigation.hashes[vm.hash].productID;
                                            vm.sidebars   = response.navigation.sidebars[productID];
                                            vm.is_loading = false;

                                            // sidebar collapsible toggles
                                            vm.$nextTick(function() {
                                                var collapsibles = $(vm.$el).find('.ps_js-collapsible');
                                                collapsibles.on('view_components_change', function(e, is_active) {
                                                    e.stopPropagation();

                                                    if (is_active) {
                                                        var active = this;
                                                        var parent = $(this).closest('.ps_js-components_sidebar_tree');
                                                        
                                                        parent.find('.ps_js-collapsible').each(function() {
                                                            if (!ps_helper.in_dom_scope(active, this)) {
                                                                $(this).trigger('view_components_hide');
                                                            }
                                                        });
                                                    }
                                                });
                                            });
                                        }
                            }, ['navigation'])
                        }
            };
        },

        /**
         * Custom tag navigation sidebar tree
         * @return void
         */
        navigation_sidebar_tree: function() {
            return {
                props   : { sidebars:{}, parent: {}, collapsible: { default: true }, actives: {}},
                computed: {
                            prefix      : function() {
                                            if (ps_helper.empty(this.parent)) {
                                                return '';
                                            } else {
                                                return this.parent + '_';
                                            }
                                        },

                            active_index: function() {
                                            var vm             = this; 
                                            var index          = 0;
                                            var sidebar_length = vm.sidebars.length;
                                            var main           = vm.actives.sidebar;
                                            var floating       = vm.actives.floating_sidebar;

                                            for (var i = 0; i < sidebar_length; i++) {

                                                var cur_hash = vm.sidebars[i].hash;
                                                if (cur_hash == main || cur_hash == floating) {
                                                    return i;
                                                }

                                            }

                                            return index;
                                        }
                        }
            };
        },

        /**
         * Custom tag navigation sidebar tree
         * @return object
         */
        navigation_sidebar_text: function() {
            return {
                props   : ['sidebar']
            };
        },

        /**
         * Custom tag navigation badge
         * @return object
         */
        navigation_badge: function() {
            return {
                data    : function() {
                            return { badges: {}, is_loading: true };
                        },

                computed: {
                            badge        : function() {
                                            var vm = this;
                                            return ps_helper.empty(vm.badges[vm.id]) ? 0 : parseInt(vm.badges[vm.id]);
                                        },
                            display_badge: function() {
                                            return !this.is_loading && this.badge > 0;
                                        }
                        }, 
                props   : ['id'],
                mounted : function() {
                            var vm = this;
                            ps_model.plugin({
                                success: function(response) { 
                                            vm.badges     = response;
                                            vm.is_loading = false;
                                        }
                            }, 'badge');
                        }
            };
        },

        /**
         * Sticky header 
         * @return void
         */
        navigation_sticky_header: function() {
            return {
                data   : function() {
                            return { is_expand: true };
                        },
                props  : {stickOn: {}, scroller: {} },
                mounted: function() {
                            var vm=this;
                            $(vm.$el).find('.ps_js-navigation_hide').on('click', function() {
                                vm.is_expand = false;
                            });
                            $(vm.$el).find('.ps_js-navigation_expand').on('click', function() {
                                vm.is_expand = true;
                                $(vm.$el).find('.ps_js-navigation_sticky').trigger('view_components_direction', 'up');
                            });
                        }
            }
        },

        /** 
         * scrollable menu
         * @return object
         */
        navigation_scrollable_menu: function() {
            return {
                data    : function() {
                            return {
                                is_loading : true,
                                view_data  : {}
                            };
                        },
                props   : ['item', 'expectedCount'],
                computed: {
                            active_index: function() {
                                            var vm=this;
                                            if (!vm.is_loading && $.isArray(vm.view_data.navigation.menu[vm.item])) {
                                                var menu_length = vm.view_data.navigation.menu[vm.item].length;

                                                for (var i = 0; i < menu_length; i++) {
                                                    var menu = vm.view_data.navigation.menu[vm.item][i];
                                                    if (menu.hash == vm.view_data.navigation.actives.menu) {
                                                        return i;
                                                    }

                                                    if (menu.hash == vm.view_data.navigation.actives.floating) {
                                                        return i;
                                                    }
                                                }

                                                return 0;

                                            } else {

                                                return 0;

                                            }
                                        }  
                        },
                mounted : function() {
                            var vm = this;

                            ps_model.view_data({
                                success: function (view_data) {
                                            vm.view_data  = view_data;
                                            vm.is_loading = false;
                                        }

                            }, ['navigation']);
                        }
            }
        },

        /**
         * Custom tag menu opener
         * @return object
         */
        navigation_side_menu: function() {
            return {
                mounted: function() {
                            var vm = this;

                            $(vm.$el).on('click', function() {
                                callables.open_side_menu();
                            });
                        }   
            };
        },

        /**
         * Custom tag info
         * This will only be use to get navigation object via scope
         * We did not put this into model shared data because its too big and not always needed
         * @return object
         */
        navigation_info: function() {
            return {
                data   : function() {
                            return {
                                is_loading: true,
                                navigation: {}
                            };
                        },
                mounted: function() {
                            var vm = this;

                            ps_model.view_data({
                                success: function(response) {
                                            vm.navigation = response.navigation;
                                            vm.is_loading = false;
                                        }
                            }, ['navigation'])
                        }   
            };
        }
    }
});
