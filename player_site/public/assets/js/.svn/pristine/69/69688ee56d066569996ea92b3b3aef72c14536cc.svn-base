
/**
 * Extend bootstrap carousel + PS own carousel
 * 
 * @author PS Team
 */
define('ps_carousel', ['ps_helper','ps_view', 'ps_store','jquery','ps_window'], function() {

    'use strict';

    var ps_helper = arguments[0];
    var ps_view   = arguments[1];
    var ps_store  = arguments[2];
    var $         = arguments[3];
    var ps_window = arguments[4];

    var globals   = { 
                        store: new ps_store('ps_carousel'), 
                        debug: true,
                        dom  : {
                                hqueue:{}
                            }
                    };
    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         |--------------------------------------------------------------------------------------------------------------
         | Carousel with bootstrap as base handler
         |--------------------------------------------------------------------------------------------------------------
         */
        
        /**
         * Bootstrap carousel initiator
         * @param  dom dom 
         * @param  int     active_index  First index to be active
         * @return void
         */
        bootstrap_carousel: function(dom, active_index) {
            // carousel required attributes
            if (ps_helper.empty(dom.attr('id'))) {
                dom.attr('id', ps_helper.uniqid());
            }

            if (ps_helper.empty(dom.attr('data-transition'))) {
                dom.attr('data-transition', 'slide');
            }

            require(['bootstrap'], function(bootstrap) {
                var options = dom.data();
                
                // default active items
                var items = dom.find('.ps_js-item');
                if (items.filter('.active').length <= 0) {
                    if (!ps_helper.empty(active_index)) {
                        $(items[active_index]).addClass('active');
                    } 
                }
                var items = dom.find('.ps_js-item');
                if (items.filter('.active').length <= 0) {
                    items.first().addClass('active');
                }

                // indicators
                if (dom.find('.ps_js-carousel_nav.active').length <= 0) {
                    dom.find('.ps_js-carousel_nav').first().addClass('active');
                }
                
                if (options['ps_carousel_bootstrap'] != true) {

                    // additional attribute controls
                    callables.bootstrap_attribute_controls(dom, options);
                    callables.bootstrap_detect_animating(dom, options);

                    if (options.hasOwnProperty('preventTabshow')) {
                        callables.bootstrap_prevent_tabshow(dom, options);
                    }

                    // add transition handler
                    if ($.isFunction(callables.bootstrap_transitions[options.transition])) {
                        callables.bootstrap_transitions[options.transition](dom);
                    }

                    // touch
                    if (options.hasOwnProperty('touch')) {
                        callables.bootstrap_touch(dom, options);
                    }

                    // global carousel event
                    if (!ps_helper.empty(options.indexClass)) {
                        callables.bootstrap_index_class(options.indexClass, dom, options);
                    }

                    // add transition handler
                    if ($.isFunction(callables.bootstrap_transitions[options.transition])) {
                    }


                    dom.data('ps_carousel_bootstrap', true).carousel();
                    dom.triggerHandler('carousel_rolled');
                }
            });
        },

        /**
         * Additional attributes that can control the carousel behaviour
         * @param  dom    dom   
         * @param  object options
         * @return void
         */
        bootstrap_attribute_controls: function(dom, options) {

            // pause
            var controlls = dom.find('.ps_js-carousel_nav, .ps_js-carousel_control_next, .ps_js-carousel_control_prev');
            controlls.on('click', function (e) {
                dom.data('carousel_user_interact',1);
            });

            dom.on('carousel_touched', function (e) {
                dom.data('carousel_user_interact',1);
            });

            dom.on('slide.bs.carousel', function (e) {

                if (parseInt(dom.attr('data-pause')) == 1 && dom.data('carousel_user_interact')!==1) {

                    e.preventDefault();
                    e.stopImmediatePropagation();

                } else {

                    dom.triggerHandler('carousel_moved');
                    
                }

                dom.data('carousel_user_interact', 0);
            });
        },

        /**
         * This will disable all navigation if carousel is still animating
         * @return void
         */
        bootstrap_detect_animating: function(dom, options) {
            dom.on('slide.bs.carousel', function (e) {

                if (dom.data('ps_carousel_animating') === true) {
                    
                    if (options.hasOwnProperty('preventAnimationConflict')) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                    }

                } else {

                    dom.data('ps_carousel_animating', true).addClass('ps_js-animating', true);
                    $(e.relatedTarget).one(ps_helper.animation_end_events(), function(e) {
                        dom.data('ps_carousel_animating', false).removeClass('ps_js-animating', true);
                        $(this).off(ps_helper.animation_end_events());
                    });

                }
            });
        },

        /** 
         * Prevent tabbed element to be shown
         * @param  dom   dom    
         * @param  array options
         * @return void
         */
        bootstrap_prevent_tabshow: function(dom, options) {

            if ($(dom).hasClass('ps_js-carousel_root')) {
                var root_element = dom;
            } else {
                var root_element = dom.find('.ps_js-carousel_root');
            }

            root_element.on('focusin', function() {
                $(this).scrollLeft(0);
                $(this).scrollTop(0);
            });

        },

        /**
         * Add touch handler to bootstrap carousel
         * @param dom     dom    
         * @param options options 
         */
        bootstrap_touch: function(dom, options) {
            if (!ps_helper.is_number_type(options.touch) || options.touch <= 0) {
                var sensitivity = 5;
            } else {
                var sensitivity = options.touch;
            }

            dom.on('touchstart', function(event) {
                var start_point       = event.originalEvent.touches[0].pageX;
                var last_direction    = null; 
                var touch_handler = function(event) {
                                            var current_point = event.originalEvent.touches[0].pageX;
                                            var distance      = Math.floor(start_point - current_point);

                                            if (distance > sensitivity && last_direction != 'next') {
                                                dom.triggerHandler('carousel_touched');
                                                dom.carousel('next');
                                                last_direction = 'next';
                                                start_point    = current_point;
                                            } else if (distance < -(sensitivity) && last_direction != 'prev') {
                                                dom.triggerHandler('carousel_touched');
                                                dom.carousel('prev');
                                                last_direction = 'prev';
                                                start_point    = current_point;
                                            }
                                        };

                dom.on('touchmove', touch_handler);
                dom.on('touchend', function() {
                    dom.off('touchmove', touch_handler);
                    last_direction = null; 
                });
            });
        },

        bootstrap_transitions: {
            /**
             * The default and most basic carousel transition
             * @param  dom  dom 
             * @return void
             */
            slide: function(dom) {
                dom.on('slide.bs.carousel', function (e) {
                    dom.removeClass('ps_js-carousel_left').removeClass('ps_js-carousel_right');
                    dom.addClass('ps_js-carousel_' + e.direction);
                    dom.find('.item.ps_js-carousel_previous').removeClass('ps_js-carousel_previous');
                    dom.find('.item.active').addClass('ps_js-carousel_previous');
                });
            }
        },

        /**
         * This will add index as class to carousel
         * ps_js-carousel_index_<number>
         * @param  mixed  index_class    
         * @param  dom    dom    
         * @param  object options
         * @return void
         */
        bootstrap_index_class: function(index_class, dom, options) {
            switch (index_class) {
                case 'reverse':
                    var index_length = dom.find('.item').length - 1;
                    dom.addClass('ps_js-carousel_index_' + index_length);
                    dom.data('ps_carousel_index', index_length);

                    dom.on('slide.bs.carousel', function (e) {
                        var index_length   = dom.find('.item').length  - 1;
                        var current_active = (index_length - $(e.relatedTarget).index());
                        dom.removeClass('ps_js-carousel_index_' + dom.data('ps_carousel_index'))
                           .addClass('ps_js-carousel_index_' + current_active);
                        dom.data('ps_carousel_index', current_active);
                    });

                    break;

                default:

                    dom.addClass('ps_js-carousel_index_0');
                    dom.data('ps_carousel_index', 0);

                    dom.on('slide.bs.carousel', function (e) {
                        var current_active = $(e.relatedTarget).index();
                        dom.removeClass('ps_js-carousel_index_' + dom.data('ps_carousel_index'))
                           .addClass('ps_js-carousel_index_' + current_active);
                        dom.data('ps_carousel_index', current_active);
                    });
                    break;
            }
        },
        
        /**
         * Move carousel to specific number
         * @param  dom dom    
         * @param  int number 
         * @return void
         */
        bootstrap_move: function(dom, number) {
            // wait if carousel not yet rolled
            if (!dom.data('ps_carousel_bootstrap')) {
                dom.on('carousel_rolled', function() {
                    dom.carousel(number);
                });
            } else {
                dom.carousel(number);
            }
        },

        /**
         |--------------------------------------------------------------------------------------------------------------
         | PS made carousel
         |--------------------------------------------------------------------------------------------------------------
         */
        
        /**
         * PS vertical-queue
         * @param  dom    dom 
         * @param  object vm 
         * @return void
         */
        ps_hqueue: function(dom, vm) {

            if (!vm.is_init) {

                vm.is_init      = true;
                vm.final_active = vm.active;
                
                // prepare events
                $(vm.$el).on('carousel_move', function(e, number) {
                    callables.ps_hqueue_move(vm, number);
                });

                var auto_timer = null;
                var auto_init  = false; 
                $(vm.$el).on('carousel_destroy_auto', function(e) {
                    if (auto_timer!==null) {
                        clearInterval(auto_timer);
                        auto_timer = null;
                    }
                });

                $(vm.$el).on('carousel_auto', function(e) {
                    // first time setup
                    if (!auto_init) {
                        auto_init = true;

                        if (vm.autoPause) {
                            $(vm.$el).on('mouseover touchstart',function() {
                                vm.paused = true;
                            });

                            $(vm.$el).on('mouseout touchend',function() {
                                vm.paused = false;
                            });
                        }
                    }

                    $(vm.$el).triggerHandler('carousel_destroy_auto');
                    if (ps_helper.is_number_type(vm.interval)) {
                        auto_timer = setInterval(function() {
                                        if (parseInt(dom.attr('data-pause')) != 1  && !vm.paused) {
                                            var next = vm.final_active + 1;

                                            if (next < vm.length) {
                                                callables.ps_hqueue_move(vm, next);
                                            } else {
                                                callables.ps_hqueue_move(vm, 0);
                                            }
                                        }
                                    }, vm.interval);
                    }
                });
        
                // roll carousel
                var is_rolled = callables.ps_hqueue_roll($(vm.$el), vm);
                if (!is_rolled || vm.liveUpdate) {
                    if (window.MutationObserver) {

                        var observer = new MutationObserver(function(mutations) {
                                        callables.ps_hqueue_dom($(vm.$el), vm);
                                        callables.ps_hqueue_roll($(vm.$el), vm);

                                        if (!vm.liveUpdate && vm.is_rolled) {
                                            $(vm.$el).triggerHandler('carousel_disconnect_liveupdate');
                                        }
                                    });

                        $(vm.$el).on('carousel_disconnect_liveupdate', function() {
                            observer.disconnect();
                        });

                        // Setup the observer
                        observer.observe(
                            globals.dom.hqueue[vm.id].scroller[0],
                            { childList: true, subtree: true }
                        ); 

                    } else {

                        if (!is_rolled) {

                            ps_helper.ready(vm.items, function() {
                                callables.ps_hqueue_roll($(vm.$el), vm);
                            }, $(vm.$el));

                        }

                        callables.debug(
                            'ps_carousel.ps_hqueue live-update feature will be disabled'
                            + ' MutationObserver unsupported'
                        );

                        ps_model.browser_not_recommended();

                    }
                }

            } else {

                callables.ps_hqueue_roll(dom, vm);

            }

        },

        /**
         * This will start the carousel to roll
         * @param  dom    dom 
         * @param  object vm  
         * @return void
         */
        ps_hqueue_roll: function(dom, vm) {
            var items = globals.dom.hqueue[vm.id].items;
            vm.length = items.length;

            // first time setup
            if (!vm.is_rolled && items.length) {

                callables.ps_hqueue_overflow(dom,vm);

                // snap points initial calculation
                vm.$nextTick(function() {
                    callables.ps_hqueue_calculate(vm, function() {
                        callables.ps_hqueue_scrolling(dom,vm);
                    });

                    callables.ps_hqueue_visibles(dom,vm);

                    if (vm.arrows) {
                        callables.ps_hqueue_arrows(dom,vm);
                    }

                    vm.$nextTick(function() {
                        callables.ps_hqueue_move(vm, vm.active);
                        dom.triggerHandler('carousel_auto');
                    });
                });



                ps_window.subscribe('resize_width', function() {
                    globals.dom.hqueue[vm.id].scroller.stop();

                    vm.resize_adjusting     = true;
                    vm.calculating_overflow = true;

                    vm.$nextTick(function() {
                        callables.ps_hqueue_overflow(dom,vm);

                        vm.$nextTick(function() {
                            vm.calculating_overflow = false;

                            vm.$nextTick(function() {
                                // recalculate snap points on resize
                                callables.ps_hqueue_calculate(vm);

                                // if move returns true, the resize_adjusting will be on animate callback
                                if (!callables.ps_hqueue_move(vm, vm.active)) {
                                    vm.resize_adjusting = false;
                                }

                                callables.ps_hqueue_visibles(dom,vm);
                            });
                        });

                    });
                });

                vm.is_rolled = true;
                dom.triggerHandler('carousel_rolled');
            }

            callables.ps_custom_indicators(dom);

            return vm.is_rolled;
        },

        /**
         * This will calculate snap points of our carousel
         * @param  object   vm    
         * @param  function callback 
         * @return void
         */
        ps_hqueue_calculate: function(vm, callback) {
            var scroller              = globals.dom.hqueue[vm.id].scroller;
            var scroller_width        = scroller.outerWidth(true);
            var scroller_offsets      = scroller.offset();
            var scroller_padding_left = parseFloat(scroller.css('padding-left'));
            var scroller_padding_right= parseFloat(scroller.css('padding-right'));
            globals.dom.hqueue[vm.id].scroller_scroll_area  = scroller.get(0).scrollWidth - scroller_width;
            globals.dom.hqueue[vm.id].scroller_offset_left  = scroller_offsets.left+scroller_padding_left;
            globals.dom.hqueue[vm.id].scroller_offset_right = ps_helper.offset_right(scroller)+scroller_padding_right;

            // snap points
            var items           =  globals.dom.hqueue[vm.id].items;
            var scroll_position = 0;
            globals.dom.hqueue[vm.id].snap_points = [];
            items.each(function() {
                var width = $(this).outerWidth();
                globals.dom.hqueue[vm.id].snap_points.push({
                    width       : width,
                    scroll_left : scroll_position,
                    scroll_right: scroll_position - (scroller.width() - width)
                });

                scroll_position += width;
            });

            globals.dom.hqueue[vm.id].snap_length = globals.dom.hqueue[vm.id].snap_points.length;

            if ($.isFunction(callback)) {
                callback();
            }
        },

        /**
         * This will check and update is_overflow if scrollbar is possible for the scroller
         * @param  dom      dom 
         * @param  object   vm  
         * @return void
         */
        ps_hqueue_overflow: function(dom, vm) {
            var scroller = globals.dom.hqueue[vm.id].scroller;

            if (scroller.length > 0 ) {
                vm.is_overflow = scroller.get(0).scrollWidth > scroller.outerWidth();
            } 
        },

        /**
         * This will check if index is currently exposed and is candidate as next snap point
         * @param  object vm      
         * @param  object options [scroll_direction, type, snap_point, exposed_area, index]
         * @return boolean/int     
         */
        ps_hqueue_exposed: function(vm, options) {
            switch(options.type) {
                case 'arrow':

                    if (options.exposed_area >= 0) {
                        return options.index;
                    } else {
                        return false;
                    }

                    break;

                default:

                    if (options.exposed_area > 0) {

                       if (vm.selectExposed == 'auto') {

                            var select_exposed = ps_helper.get_percent_value(
                                                    options.snap_point.width,
                                                    vm.exposedPercentage
                                                );

                        } else {

                            var select_exposed = vm.select_exposed;

                        }

                        if (select_exposed <= options.exposed_area) {

                            return options.index;

                        } else {

                            if (options.scroll_direction == 'left') {
                                return options.index - 1;
                            } else {
                                return options.index + 1;
                            }

                        }

                    } else {

                        return false;

                    }
            }
        },

        /**
         * This will get the next snap element index
         * @param  object vm               
         * @param  string scroll_direction 
         * @param  string type               [auto(default),arrow]     
         * @return void
         */
        ps_hqueue_nextindex: function(vm, scroll_direction, type) {
            var hqueue_props = globals.dom.hqueue[vm.id];
            var scroll_left  = globals.dom.hqueue[vm.id].scroller.scrollLeft();
            type             = type || 'auto';

            if (scroll_direction == 'left') {

                var scroll_right = hqueue_props.scroller_scroll_area - scroll_left;
                for (var i = hqueue_props.snap_length - 1; i >= 0; i--) {
                    var snap_point     = hqueue_props.snap_points[i];
                    scroll_right      -= snap_point.width;

                    var activate_point = callables.ps_hqueue_exposed(vm, {
                                            scroll_direction: scroll_direction,
                                            type            : type,
                                            exposed_area    : scroll_right*-1,
                                            snap_point      : snap_point,
                                            index           : i
                                        });

                    if (activate_point!==false) {
                        break;
                    }
                }

            } else {

                for (var i = 0; i < hqueue_props.snap_length; i++) {
                    var snap_point   = hqueue_props.snap_points[i];
                    scroll_left     -= snap_point.width;
                    
                    var activate_point = callables.ps_hqueue_exposed(vm, {
                                            scroll_direction: scroll_direction,
                                            type            : type,
                                            exposed_area    : scroll_left*-1,
                                            snap_point      : snap_point,
                                            index           : i
                                        });

                    if (activate_point!==false) {
                        break;
                    }
                }

            }

            return activate_point;
        },

        /**
         * This will get the next scroll position 
         * @param  object vm        
         * @param  string direction 
         * @param  int    index   
         * @return void
         */
        ps_hqueue_nextpoint: function(vm, scroll_direction, index) {
            var hqueue_props = globals.dom.hqueue[vm.id];
            if (scroll_direction == 'left') {
                return hqueue_props.snap_points[index].scroll_right;
            } else {
                return hqueue_props.snap_points[index].scroll_left;
            }
        },

        /**
         * Bind scrolling handler for hqueue carousel
         * @param  dom    dom 
         * @param  object vm  
         * @return void
         */
        ps_hqueue_scrolling: function(dom, vm) {
            var hqueue_props       = globals.dom.hqueue[vm.id];
            var scroller           = hqueue_props.scroller;
            var last_scroll_left   = null;

            function adjust() {
                var scroll_left = scroller.scrollLeft();
                if (vm.scrolling == false && vm.dragging == false && scroller.scrollLeft() != last_scroll_left) {
                    var scroll_direction = (scroll_left > last_scroll_left)? 'left':'right';
                    last_scroll_left     = null;
                    callables.ps_hqueue_adjust(dom,vm, {
                        direction: scroll_direction,
                        type     : 'auto'
                    });
                }
            }

            function scroll_end() {
                vm.scrolling = false;
                adjust();
            }

            scroller.on('scroll', function() {
                vm.scrolling = true;
                callables.ps_hqueue_visibles(dom,vm);

                var is_scroll_animated = scroller.is(':animated');

                if (!is_scroll_animated && last_scroll_left === null) {
                    last_scroll_left = scroller.scrollLeft();  
                }

                if (!is_scroll_animated) {
                    ps_helper.event_delay_animation(scroll_end, 100, vm.id);
                }
            });

            scroller.on('touchstart', function(e) {
                vm.dragging = true;
            });

            scroller.on('touchend', function() {
                vm.dragging = false;
                adjust();
            });
        },

        /**  
         * hqueue left/right arrow handler
         * @param  void   dom 
         * @param  object vm        
         * @return void
         */
        ps_hqueue_arrows: function(dom, vm) {
            dom.find('.ps_js-hqueue_left').on('click', function() {
                callables.ps_hqueue_adjust(dom, vm, { direction: 'right', type: 'arrow' });
            });

            dom.find('.ps_js-hqueue_right').on('click', function() {
                callables.ps_hqueue_adjust(dom, vm, { direction: 'left', type: 'arrow' });
            });
        },

        /**
         * This will move item with index into view
         * @param  object vm   
         * @param  int    index
         * @return void
         */
        ps_hqueue_move: function(vm, index) {
            var hqueue_props   = globals.dom.hqueue[vm.id];
            var dom            = $(vm.$el);
            var items          = globals.dom.hqueue[vm.id].items;
            var active_item    = items.get(index);
            var has_adjustment = false;
            if (!ps_helper.empty(active_item)) {
                var scroller         = globals.dom.hqueue[vm.id].scroller;
                var item_left_offset = $(active_item).offset().left-hqueue_props.scroller_offset_left;
                if (item_left_offset < 0) {
                    callables.ps_hqueue_adjust(dom, vm, { direction: 'right', index: index });
                    has_adjustment = true;
                } else {
                    var item_right_offset = ps_helper.offset_right($(active_item))-hqueue_props.scroller_offset_right;
                    if (item_right_offset < 0) {
                        callables.ps_hqueue_adjust(dom, vm, { direction: 'left', index: index });
                        has_adjustment = true;
                    }
                }

                vm.final_active = index;
                return has_adjustment;
            }
        },

        /**
         * This will handle auto adjust of carousel scroll
         * @param  dom    dom    
         * @param  object vm     
         * @param  object options custom options. { activate_page, direction, is_arrow }
         * @return void
         */
        ps_hqueue_adjust: function(dom, vm,  options) {
            
            if (vm.is_overflow) {

                if (ps_helper.empty(options.index) && ps_helper.empty(options.type)) {
                    throw 'atleast one on options.index or options.type should have value.';
                }

                vm.adjusting = true;
                var scroller = globals.dom.hqueue[vm.id].scroller;
                var items    = globals.dom.hqueue[vm.id].items;

                if (ps_helper.empty(options.index)) {
                    // must provide type 'auto or arrow' if index is not present
                    var index = callables.ps_hqueue_nextindex(vm, options.direction, options.type);
                } else {
                    var index = options.index;
                }

                var scroll_to = callables.ps_hqueue_nextpoint(vm, options.direction, index);

                scroller.stop().animate({ scrollLeft: scroll_to }, vm.scrollTransition, function() {
                    vm.final_active     = index;
                    vm.resize_adjusting = false;
                    vm.adjusting        = false;
                });

            }
        },

        /**
         * This will update hqueue carousel cached doms
         * @param  dom    dom 
         * @param  object vm  
         * @return void
         */
        ps_hqueue_dom: function(dom, vm) {
            if (!globals.dom.hqueue.hasOwnProperty(vm.id)) {
                globals.dom.hqueue[vm.id] = {
                                                scroller: dom.find('.ps_js-carousel_scroller_' + vm.id),
                                                items   : dom.find(vm.items)
                                            };
            } else {
                globals.dom.hqueue[vm.id].items = dom.find(vm.items);
            }
        },

        /**
         * This will get all visible item
         * @return void
         */
        ps_hqueue_visibles: function(dom, vm) {
            // get all visible items 
            if (vm.getVisible && vm.is_overflow) {
                var detect = function() {
                                var visibility            = {};
                                var scroller              = globals.dom.hqueue[vm.id].scroller;
                                var items                 = globals.dom.hqueue[vm.id].items;
                                var left_padding          = parseFloat(scroller.css('padding-left'));
                                var scroller_left_offset  = scroller.offset().left + left_padding;
                                var right_padding         = parseFloat(scroller.css('padding-right'));
                                var scroller_right_offset = ps_helper.offset_right(scroller) + right_padding;

                                items.each(function(index) {
                                    var item_width          = $(this).outerWidth();
                                    var exposed_percent     = ps_helper.get_percent_value(
                                                                item_width, 
                                                                vm.visibleExposed
                                                            );

                                    var item_left_offset    = $(this).offset().left - scroller_left_offset;
                                    var exposed_area_left   = item_width + item_left_offset;
                                    if (exposed_area_left <= exposed_percent) {

                                        visibility[index] = false;

                                    } else {

                                        var item_right_offset  = ps_helper.offset_right($(this))-scroller_right_offset;
                                        var exposed_area_right = item_width + item_right_offset;

                                        if (exposed_area_right <= exposed_percent) {
                                            visibility[index] = false;
                                        } else {
                                            visibility[index] = true;
                                        }
                                    }
                                });
                                
                                vm.visibility = visibility;

                            };
                if (vm.visibilityDelay==false) {
                    detect();
                } else {
                    ps_helper.event_delay(detect, vm.visibilityDelay, 'carousel_hqueue_visible' + vm.id);
                }

            } else {

                vm.visibility = {};

            }
        },

        /**
         * This will bind carousel custom indicators event
         * @param  dom    dom 
         * @return void
         */
        ps_custom_indicators: function(dom) {
            dom.find('.ps_js-carousel_nav').off('click').on('click', function() {
                dom.triggerHandler('carousel_move', parseInt($(this).attr('data-move-to')));
            });
        }
    };

    return {
        /**
         * Bootstrap carousel template
         * @return void
         */
        carousel_bootstrap: function() {
            return {
                props   : ['items','indicators','arrows','id','active'],
                watch   : {
                            active: function(new_value) {
                                        var vm = this;
                                        if (!ps_helper.empty(new_value)) {
                                            callables.bootstrap_move($(vm.$el), new_value);
                                        }
                                    }
                        },
                computed: {
                            final_id: function() {
                                return this.id || ps_helper.uniqid()
                            },
                            items_length: function() {
                                if ($.isArray(this.items)) {
                                    return this.items.length;
                                } else if($.isPlainObject(this.items)) {
                                    return Object.keys(this.items).length;
                                }
                            }
                        },
                mounted : function() {
                            var vm = this;
                            callables.bootstrap_carousel($(vm.$el), vm.active);
                        },
                updated : function() {
                            var vm = this;
                            callables.bootstrap_carousel($(vm.$el), vm.active);
                        }
            };
        },

        /**
         * Bootstrap carousel indicator
         * @return void
         */
        carousel_bootstrap_indicators: function() {
            return {
                props   : ['items','target'],
                computed: {
                            items_length: function() {
                                if ($.isArray(this.items)) {
                                    return this.items.length;
                                } else if($.isPlainObject(this.items)) {
                                    return Object.keys(this.items).length;
                                }
                            }
                        }
            };
        },

        /**
         * carousel v-queue
         * @return object
         */
        carousel_hqueue: function() {
            return {
                data    : function() {
                            return {
                                is_overflow          : true,
                                calculating_overflow : false,
                                final_active         : 0,
                                is_rolled            : false,
                                is_init              : false,
                                length               : 0,
                                paused               : false,
                                resize_adjusting     : false,
                                scrolling            : false,
                                adjusting            : false,
                                dragging             : false,
                                id                   : ps_helper.uniqid(),
                                visibility           : {}
                            };
                        },
                props   : { 
                            items            : {},
                            indicators       : { default: false  },
                            arrows           : { default: true   },
                            active           : { default: 0      },
                            selectExposed    : { default: 'auto' },
                            liveUpdate       : { default: false  },
                            interval         : { default: false  },
                            autoPause        : { default: true   },
                            exposedPercentage: { default: 25     }, 
                            scrollTransition : { default: 'fast' }, 
                            visibleExposed   : { default: 75     },
                            getVisible       : { default: false  },
                            visibilityDelay  : { default: false  },
                        },
                watch   : {
                            active     : function(active) {
                                            callables.ps_hqueue_move(this, active);
                                        },
                            interval    : function() {
                                            var vm = this;
                                            $(vm.$el).triggerHandler('carousel_auto');
                                        },
                            final_active: function(final_active) {
                                            var vm = this;
                                            $(vm.$el).triggerHandler('carousel_moved', final_active);
                                        }
                        },

                computed: {
                            busy            : function() {
                                                var vm = this;
                                                return (vm.scrolling || vm.dragging || vm.adjusting);
                                            },
                            final_visibility: function() {
                                                var vm = this;

                                                if (Object.keys(vm.visibility).length > 0) {
                                                    return vm.visibility;
                                                } else {
                                                    var visibility = {};

                                                    for (var i = 0; i < vm.length; i++) {
                                                        visibility[0] = true;
                                                    }

                                                    return visibility;
                                                }
                                            },
                            items_length    : function() {
                                                if ($.isArray(this.items)) {
                                                    return this.items.length;
                                                } else if($.isPlainObject(this.items)) {
                                                    return Object.keys(this.items).length;
                                                }
                                            }
                        },

                mounted : function() {
                            var vm = this;
                            callables.ps_hqueue_dom($(vm.$el), vm);
                            callables.ps_hqueue($(vm.$el), vm);
                        },

                updated : function() {
                            var vm = this;
                            callables.ps_hqueue_dom($(vm.$el), vm);
                            callables.ps_hqueue($(vm.$el), vm);
                        },

                beforeDestroy: function() {
                    var vm = this;
                    $(vm.$el).triggerHandler('carousel_disconnect_liveupdate');
                    $(vm.$el).triggerHandler('carousel_destroy_auto');
                }
            };
        },

        /**
         * PS custom carousel indicator
         * @return void
         */
        carousel_custom_indicators: function() {
            return {
                props  : ['length','active']
            };
        },
    };
});