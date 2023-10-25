
/**
 * PS help page
 * 
 * @author PS Team
 */
define('ps_help',['jquery','ps_view','ps_model','ps_popup','ps_language','ps_store','ps_helper'], function() {

    var $           = arguments[0];
    var ps_view     = arguments[1];
    var ps_model    = arguments[2];
    var ps_popup    = arguments[3];
    var ps_language = arguments[4];
    var ps_store    = arguments[5];
    var ps_helper   = arguments[6];

    var globals   = { store: new ps_store('ps_help') };
    var callables = {
        /**
         * Get/Create help page store
         * @return object
         */
        help_page_info: function() {
            if (!globals.store.store_exists('info')) {
                globals.store.store_update('info', {
                    open_sidebar    : null,
                    previous_sidebar: null
                });
            }

            return globals.store.store_fetch('info');
        },

        /**
         * This will get all needed data for page to initiate
         * Triggered in account page mounted event
         * @return void
         */
        page_init: function() {
            var vm = this;
            ps_model.view_data({
                success: function(response) {
                            vm.view_data = response;
                            // mark page as rendered
                            ps_model.update_page_rendered(vm.hash_info.page);
                            vm.is_loading = false;
                        }
            },['navigation','route']);
        },

        /**
         * This will initiate help sidebar pages
         * @param  string  vm 
         * @param  object vm 
         * @return void
         */
        sidebar_init: function(active_sidebar, vm) {
            var sidebar_hash_info = vm.view_data.navigation.hashes[active_sidebar];

            if (sidebar_hash_info.parents.length > 0) {
                var sidebar_root = sidebar_hash_info.parents[0].id;
            } else {
                var sidebar_root = sidebar_hash_info.id;
            }

            // close current open first & open new
            ps_popup.modal.close('all', 'help', function() {
                if ($.isPlainObject(callables[sidebar_root])) {
                    if ($.isFunction(callables[sidebar_root].activate)) {
                        active_sidebar = ps_helper.replace_all(active_sidebar,'#','');
                        callables[sidebar_root].activate(active_sidebar, sidebar_hash_info, vm);
                    }
                }
            });
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | FAQS
         |--------------------------------------------------------------------------------------------------------------
         */
        faq: {
            /**
             * Activate faq
             * @param  string active_sidebar
             * @param  object sidebar_hash_info
             * @param  object vm          
             * @return void
             */
            activate: function(active_sidebar, sidebar_hash_info, vm) {
                ps_popup.modal.open(active_sidebar, {
                    modal_class  : 'faq_root',
                    sticky_header: true,
                    header       : function(modal_part) {
                                    ps_view.render(modal_part, 'faq_header', {
                                        replace: false,
                                        data   : {
                                                    modal_info: callables.faq.modal_info(active_sidebar),
                                                    text      : sidebar_hash_info.text
                                                },
                                        mounted: function() {
                                                    var child_vm  = this;
                                                    var modal_root = $(child_vm.$el).closest('.ps_js-faq_root');

                                                    // toggle
                                                    $(child_vm.$el).find('.ps_js-faq_toggle_answers')
                                                        .on('click', function() {
                                                            var is_expand_all = child_vm.modal_info.is_expand_all;
                                                            globals.store.store_update(active_sidebar, {
                                                                is_expand_all: is_expand_all ? false:true
                                                            });
                                                        });
                                                }
                                    });
                                },
                    body         : function(modal_part) {
                                    ps_view.render(modal_part, 'faq_body', {
                                        replace: false,
                                        data   : {
                                                    modal_info: callables.faq.modal_info(active_sidebar),
                                                    is_loading: true,
                                                    faq       : { list:[] }
                                                },
                                        computed: {
                                                    is_expand_all: function() {
                                                                    return this.modal_info.is_expand_all;
                                                                }
                                                },
                                        watch   : {
                                                    is_expand_all: function(is_expand_all) {
                                                                    var child_vm = this;
                                                                    if (is_expand_all) {
                                                                        $(child_vm.$el).find('.ps_js-collapsible')
                                                                            .trigger('view_components_collapse');
                                                                    } else {
                                                                        $(child_vm.$el).find('.ps_js-collapsible')
                                                                            .trigger('view_components_hide');
                                                                    }
                                                                }
                                                },
                                        mounted: function() {
                                                    var child_vm = this;
                                                    ps_model.get_faqs(sidebar_hash_info.productID, {
                                                        success: function(response) {
                                                                    child_vm.faq = response;
                                                                },
                                                        complete: function() {
                                                                    child_vm.is_loading = false;
                                                                    child_vm.$nextTick(callables.faq.events);
                                                                }
                                                    });
                                                }
                                    });
                                },
                    bind         : {
                                    hide : function() {
                                            if (window.location.hash === sidebar_hash_info.hash) {
                                                window.location = sidebar_hash_info.menu_hash;
                                            }

                                            // reset
                                            globals.store.store_update(active_sidebar, { is_expand_all: false });
                                            $('.ps_js-help_faq_body .ps_js-collapsible')
                                                .trigger('view_components_hide');
                                        }
                                }
                },'help');
            },

            /**
             * This will create or get modal info of specified faq category
             * @param  string category 
             * @return void
             */
            modal_info: function(category) {
                if (!globals.store.store_exists(category)) {
                    globals.store.store_update(category, {
                        is_expand_all: false
                    });
                }

                return globals.store.store_fetch(category);
            },

            /**
             * FAQ page events
             * @return void
             */
            events: function() {
                var child_vm     = this;
                var collapsibles = $(child_vm.$el).find('.ps_js-collapsible');
                collapsibles.on('view_components_change', function(e, is_active) {
                    e.stopPropagation();

                    if (is_active && child_vm.modal_info.is_expand_all == false) {
                        var active = this;
                        collapsibles.each(function() {
                            if (!ps_helper.in_dom_scope(active, this)) {
                                $(this).trigger('view_components_hide');
                            }
                        });
                    }
                });

                if (child_vm.modal_info.is_expand_all) {
                    collapsibles.trigger('view_components_collapse');
                }
            }
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | GAMING RULES
         |--------------------------------------------------------------------------------------------------------------
         */
        gaming_rules: {
            /**
             * Activate game rules
             * @param  string active_sidebar
             * @param  object sidebar_hash_info
             * @param  object vm          
             * @return void
             */
            activate: function(active_sidebar, sidebar_hash_info, vm) {
                ps_popup.modal.open(active_sidebar, {
                    modal_class: 'gaming_rules_root',
                    closable   : (vm.view_data.route.view_type!='ingame'),
                    header     : function(modal_part) {
                                    ps_view.render(modal_part, 'gaming_rules_header', {
                                        replace : false,
                                        data    : { text: sidebar_hash_info.text }
                                    });
                                },
                    body       : function(modal_part) {
                                    ps_view.render(modal_part, 'gaming_rules_body', {
                                        replace: false,
                                        data   : {
                                                    is_loading  : true,
                                                    gaming_rules: { description:'', items:[], title:'' }
                                                },
                                        mounted: function() {
                                                    var child_vm = this;

                                                    if (vm.view_data.route.view_type == 'ingame') {
                                                        var width  = $(child_vm.$el).attr('data-ingame-width'); 
                                                        var height = $(child_vm.$el).attr('data-ingame-height'); 
                                                        window.resizeTo(width, height);
                                                    }

                                                    ps_model.get_gaming_rules(sidebar_hash_info.productID, {
                                                        success: function(response) {
                                                                    child_vm.gaming_rules = response;
                                                                },
                                                        complete: function() {
                                                                    child_vm.is_loading = false;
                                                                    child_vm.$nextTick(function() {
                                                                        callables.gaming_rules.events(child_vm, vm);
                                                                    });
                                                                }
                                                    });
                                                }
                                    });
                                },
                    bind       : {
                                    hide : function() {
                                            if (window.location.hash === sidebar_hash_info.hash) {
                                                window.location = sidebar_hash_info.menu_hash;
                                            }
                                        }
                                }
                },'help');
            },

            /**
             * Additional game rules element and events
             * @param  object child_vm game rules modal vm instance
             * @param  object vm       help sidebar vm instance
             * @return void
             */
            events: function(child_vm, vm) {
                ps_view.render($(child_vm.$el).find('.ps_js-game_guide_menu'), 'game_guide_tree', {
                    data   : {
                                hash_info   : vm.hash_info,
                                view_data   : vm.view_data
                            },
                    computed: {
                                game_guide: function() {
                                                var sidebars   = this.view_data.navigation.sidebars;
                                                var productID  = this.hash_info.productID;
                                                return sidebars[productID].filter(function(value) {
                                                    return value.id == 'game_guide'
                                                })[0].children;
                                            }
                            }
                });
            }
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | GAME GUIDE
         |--------------------------------------------------------------------------------------------------------------
         */
        game_guide: {
            /**
             * Activate game guide
             * @param  string active_sidebar
             * @param  object sidebar_hash_info
             * @param  object vm          
             * @return void
             */
            activate: function(active_sidebar, sidebar_hash_info, vm) {
                var modal_info = callables.game_guide.modal_info(active_sidebar);
                var page_info  = callables.help_page_info();

                ps_popup.modal.open(active_sidebar, {
                    modal_class  : 'game_guide_root',
                    closable     : (vm.view_data.route.view_type!='ingame'),
                    sticky_header: true,
                    header       : function(modal_part) {
                                    ps_view.render(modal_part, 'game_guide_header', {
                                        replace : false,
                                        data    : { 
                                                    text             : sidebar_hash_info.text,
                                                    modal_info       : modal_info,
                                                    sidebar_hash_info: sidebar_hash_info,
                                                    page_info        : page_info
                                                },
                                        mounted : function() {
                                                    callables.game_guide.load_header.call(this, vm);
                                                }
                                    });
                                },
                    body         : function(modal_part) {
                                    ps_view.render(modal_part, 'game_guide_body', {
                                        replace: false,
                                        data    : {
                                                    modal_info       : modal_info,
                                                    sidebar_hash_info: sidebar_hash_info,
                                                    page_info        : page_info
                                                },
                                        computed: {
                                                    is_header_ready: function() {
                                                                        return this.modal_info.is_header_ready;
                                                                    },

                                                    active_page     : function() {
                                                                        return this.modal_info.active_page;
                                                                    }
                                                },
                                        watch   : {
                                                    is_header_ready:  function() {
                                                                        callables.game_guide.load_body.call(this, vm);
                                                                    },
                                                    active_page    :  function() {
                                                                        callables.game_guide.load_body.call(this, vm);
                                                                    }
                                                },
                                        mounted: function() {
                                                    callables.game_guide.load_body.call(this, vm);
                                                }
                                    });
                                },
                    bind         : {
                                    hide : function() {
                                            if (window.location.hash === sidebar_hash_info.hash) {
                                                window.location = sidebar_hash_info.menu_hash;
                                            }
                                        }
                                }
                },'help');

                globals.store.store_update(active_sidebar, {
                    active_page: 0
                });
            },

            /**
             * This will create or get modal info of specified game guide category
             * @param  string category 
             * @return void
             */
            modal_info: function(category) {
                if (!globals.store.store_exists(category)) {
                    globals.store.store_update(category, {
                        has_back_button   : false,
                        is_header_ready   : false,
                        header_title      : '',
                        page_count        : 0,
                        header_options    : {},
                        active_page       : 0,
                        page_infos        : [],
                        under_construction: false,
                        category          : category
                    });
                }

                return globals.store.store_fetch(category);
            },

            /**
             * This will intialize our game guide header
             * @param  object  vm
             * @return void
             */
            load_header: function(vm) {
                var child_vm          = this;
                var sidebar_hash_info = child_vm.sidebar_hash_info;
                var category         = child_vm.modal_info.category;
                
                ps_model.get_game_guide({
                                            gpage : 0,
                                            gname : sidebar_hash_info.gameName_original,
                                            pID   : child_vm.sidebar_hash_info.productID
                                        },
                                         {
                                            success : function(response) {
                                                        var header = $('<p></p>').append(response);
                                                        var title  = header.find('.ps_gg-select-page').text();
                                                        var select = header.find('.ps_game_guide_selector');


                                                        if (select.length>0) {
                                                            var options = {};

                                                            select.find('option').each(function(key) {
                                                                options[key] = $(this).html();
                                                                globals.store.store_list_push(category,'page_infos', {
                                                                    initiated: false,
                                                                    loaded   : false,
                                                                    success  : null,
                                                                    content  : ''
                                                                });
                                                            });
                                                            
                                                            globals.store.store_update(category, {
                                                                header_title   : title,
                                                                page_count     : Object.keys(options).length,
                                                                header_options : options
                                                            });
                                                        }
                                                    },
                                            complete: function() {
                                                        var modal_info   = child_vm.modal_info;
                                                        var page_count   = modal_info.page_count;
                                                        globals.store.store_update(category, {
                                                            is_header_ready   : true,
                                                            under_construction: page_count<=0
                                                        });

                                                        child_vm.$nextTick(function() {
                                                            $(child_vm.$el).find('.ps_js-game_guide_select')
                                                                           .on('change', function() {
                                                                globals.store.store_update(category, {
                                                                    active_page: $(this).val()
                                                                });
                                                            });
                                                        });
                                                    }
                                        });
            },

            /**
             * This will intialize our game guide body
             * @param  object vm
             * @return void
             */
            load_body: function(vm) {
                var child_vm = this;

                if (vm.view_data.route.view_type == 'ingame') {
                    var width  = $(child_vm.$el).attr('data-ingame-width'); 
                    var height = $(child_vm.$el).attr('data-ingame-height'); 
                    window.resizeTo(width, height);
                }

                if (child_vm.is_header_ready) {
                    var active_page = parseInt(child_vm.modal_info.active_page);
                    var page_info   = child_vm.modal_info.page_infos[active_page];
                    var category    = child_vm.modal_info.category;
                    if ($.isPlainObject(page_info) && page_info.initiated == false) {
                        globals.store.store_update(category, 'page_infos.'+active_page+'.initiated', true);
                        ps_model.get_game_guide({
                                                    gpage : active_page + 1,
                                                    gname : child_vm.sidebar_hash_info.gameName_original,
                                                    pID   : child_vm.sidebar_hash_info.productID
                                                },
                                                 {
                                                    success : function(response) {
                                                                globals
                                                                .store
                                                                .store_update(category, 'page_infos.'+active_page, {
                                                                    loaded : true,
                                                                    success: true,
                                                                    content: response
                                                                });

                                                                child_vm.$nextTick(function() {
                                                                    callables
                                                                    .game_guide
                                                                    .load_images(child_vm, active_page);
                                                                });

                                                            },
                                                    error   : function() {
                                                                globals
                                                                .store
                                                                .store_update(category, 'page_infos.'+active_page, {
                                                                    loaded : true,
                                                                    success: false
                                                                });
                                                            },
                                                    fail   : function() {
                                                                globals
                                                                .store
                                                                .store_update(category, 'page_infos.'+active_page, {
                                                                    loaded : true,
                                                                    success: false
                                                                });
                                                            }
                                                });
                    } 

                    ps_helper.animate(ps_helper.scrollable_parent($(child_vm.$el)),{scrollTop: 0},'fast');
                }
            },

            /**
             * This will load game guide images
             * @param  object child_vm    
             * @param  int    active_page 
             * @return void
             */
            load_images: function(child_vm, page) {
                $(child_vm.$el).find('.ps_js-game_guide_'+page+ ' img').each(function() {
                    ps_view.render($(this),'game_guide_images',{
                        data   : { attributes: ps_helper.get_all_attributes($(this)), is_loading: true },
                        mounted: function() {
                                    var image_vm       = this;
                                    var image_selector = $(image_vm.$el).find('.ps_js-gg_image_selector');
                                    image_selector.trigger('image_loaded', function() {
                                        ps_helper.set_attributes($(this).find('.ps_js-image'), image_vm.attributes);
                                        image_vm.is_loading = false;
                                    });
                                }
                    });
                });
            }
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | TERMS AND CONDITION
         |--------------------------------------------------------------------------------------------------------------
         */
        terms_and_conditions: {
            /**
             * Activate terms and condition
             * @param  string active_sidebar
             * @param  object sidebar_hash_info
             * @param  object vm          
             * @return void
             */
            activate: function(active_sidebar, sidebar_hash_info, vm) {
                 ps_popup.modal.open(active_sidebar, {
                    modal_class: 'terms_conditions_root',
                    header     : ps_language.get('language.terms_and_conditions'),
                    body       : function(modal_part) {
                                    ps_view.render(modal_part, 'terms_conditions_body', {
                                        replace: false,
                                        data   : { is_loading: true, content: '' },
                                        mounted: function() {
                                                    var child_vm = this;
                                                    ps_model.terms_and_conditions({
                                                        success: function(response) {
                                                                    child_vm.content    = response;
                                                                    child_vm.is_loading = false;
                                                                }
                                                    });
                                                }
                                    });
                                },
                    bind        : {
                                    hide : function() {
                                            if (window.location.hash === sidebar_hash_info.hash) {
                                                window.location = sidebar_hash_info.menu_hash;
                                            }
                                        }
                                }
                },'help');
            }
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | CONTACT US
         |--------------------------------------------------------------------------------------------------------------
         */
        contact_us: {
            /**
             * Activate terms and condition
             * @param  string active_sidebar
             * @param  object sidebar_hash_info
             * @param  object vm          
             * @return void
             */
            activate: function(active_sidebar, sidebar_hash_info, vm) {
                 ps_popup.modal.open(active_sidebar, {
                    modal_class: 'contact_us_root',
                    header     : ps_language.get('language.contact_us'),
                    body       : function(modal_part) {
                                    ps_view.render(modal_part, 'contact_us_body', {
                                        replace : false,
                                        data    : { is_loading: true , content: '' },
                                        mounted : function() {
                                                    var child_vm = this;
                                                    ps_model.contact_us({
                                                        success: function(response) {
                                                                    child_vm.content    = response;
                                                                    child_vm.is_loading = false;
                                                                }
                                                    });
                                                }
                                    });
                                },
                    bind        : {
                                    hide : function() {
                                            if (window.location.hash === sidebar_hash_info.hash) {
                                                window.location = sidebar_hash_info.menu_hash;
                                            }
                                        }
                                }
                },'help');
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
            var page_info = callables.help_page_info();
            ps_popup.modal.open('help_menu', {
                modal_class: 'help_menu_root',
                header     : ps_language.get('language.help'),
                body       : function(modal_part) {
                                ps_view.render(modal_part, 'help_menu', {
                                    replace: false,
                                    data   : {
                                                hash_info : hash_info,
                                                page_info : page_info,
                                                view_data : {},
                                                is_loading: true
                                            },
                                    computed: {
                                                active_sidebar: function() {
                                                                    var vm = this;
                                                                    return vm.is_loading?null:vm.page_info.open_sidebar;    
                                                                }
                                            },
                                    watch   : {
                                                active_sidebar: function() {
                                                                    var vm = this;
                                                                    if (vm.active_sidebar!==null) {
                                                                        callables.sidebar_init(vm.active_sidebar,vm);
                                                                    }
                                                                }
                                            },
                                    mounted : callables.page_init
                                });   
                            },
                bind       : {
                                hide : function() {
                                        window.location = ps_model.active_main_hash();

                                        $('.ps_js-help_menu_root .ps_js-collapsible').trigger('view_components_hide');
                                        ps_popup.modal.close('all', 'help');
                                    }
                            }

            }, 'floating_page');
            
            // update active sidebar
            var window_hash = window.location.hash;
            if (ps_helper.in_array(window_hash, hash_info.sidebars)) {
                globals.store.store_update('info', { 
                    open_sidebar    : window_hash,
                    previous_sidebar: page_info.open_sidebar 
                });
            }  else {
                globals.store.store_update('info', { 
                    open_sidebar    : null,
                    previous_sidebar: page_info.open_sidebar 
                });
            }
        },

        /**
         * This will trigger on help page deactivation
         * @return void
         */
        deactivate: function(hash_info) {
            if (!ps_model.is_hash_related(hash_info.hash, window.location.hash)) {
                ps_popup.modal.close('help_menu', 'floating_page');
            }
        }
    };
});