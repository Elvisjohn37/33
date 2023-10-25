
/**
 * Not to be confused with real game pages, 
 * this plugin contains components that multiple game pages share in common
 * 
 * @author PS Team
 */
define('ps_games_template', ['ps_view','ps_model','ps_language','ps_popup','ps_store'], function() {

    var ps_view     = arguments[0];
    var ps_model    = arguments[1];
    var ps_language = arguments[2];
    var ps_popup    = arguments[3];
    var ps_store    = arguments[4];

    var globals     = { store: new ps_store('ps_games_template') };
    var callables   = {

        /**
         * This will convert gameName into gameKey to be used as classname and IDs
         * @param  string gameName 
         * @return string
         */
        gameName_as_key: function(gameName) {
            return ps_helper.replace_all(ps_helper.to_alpha_num(gameName.toLowerCase()),' ','');
        },

        /**
         * This will give current modal information of game preview
         * @param  string store_key
         * @return object
         */
        game_preview_info: function(store_key) {
            if (!globals.store.store_exists(store_key)) {
                globals.store.store_update(store_key, {
                    is_shown   : false,
                    is_rendered: false,
                    is_mounted : false,
                    is_hide    : false
                });
            }
            return globals.store.store_fetch(store_key);
        },

        /**
         * This will open game preview modal
         * @param  object   game_details   
         * @param  string   game_key 
         * @param  function play_button_callback
         * @return void
         */
        game_preview_modal: function(game_details, game_key, play_button_callback) {
            var modal_name = 'game_preview_' + game_key;
            var modal_info = callables.game_preview_info(modal_name);
            ps_popup.modal.open(modal_name, {
                modal_class: 'game_preview_root',
                body        : function(modal_part) {
                                ps_view.render(modal_part, 'game_preview_modal', {
                                    replace : false,
                                    data    : { 
                                                game_key        : game_key,
                                                game            : game_details, 
                                                modal_info      : modal_info,
                                                is_video_ended  : false,
                                                is_video_playing: false
                                            },
                                    computed: {
                                                has_video:  function() {    
                                                                var vm = this;
                                                                return !ps_helper.empty(vm.game.videoReference);
                                                            },
                                                is_loaded:  function() {
                                                                var vm          = this;
                                                                var is_shown    =  vm.modal_info.is_shown;
                                                                var is_rendered =  vm.modal_info.is_rendered;
                                                                var is_mounted  =  vm.modal_info.is_mounted;
                                                                return (is_shown && is_rendered && is_mounted);
                                                            },
                                                items       :  function() {
                                                                var vm    = this;
                                                                var items = [];

                                                                if (vm.has_video) {
                                                                    items.push({ 
                                                                        type: 'video', 
                                                                        src : vm.game.videoReference 
                                                                    });
                                                                }

                                                                for (var i = 1; i <= 3; i++) {
                                                                    items.push({ 
                                                                        type: 'image', 
                                                                        src : vm.game_key+'_'+i
                                                                    });
                                                                } 

                                                                return items;
                                                            },
                                                carousel_pause: function() {
                                                                    var vm = this;

                                                                    // condition order is important
                                                                    if (!vm.is_loaded) {
                                                                        return 0;
                                                                    }

                                                                    if (vm.modal_info.is_hide) {
                                                                        return 0;
                                                                    }

                                                                    if (!vm.has_video) {
                                                                        return 0;
                                                                    }

                                                                    if (vm.is_video_ended && !vm.is_video_playing) {
                                                                        return 0;
                                                                    }

                                                                    return 1;
                                                                },
                                                is_video_stop: function() {
                                                                var vm = this;
                                                                return (vm.modal_info.is_hide && vm.has_video);
                                                            }
                                            },
                                    watch   : {
                                                is_loaded    : function(is_loaded) {
                                                                var vm = this;
                                                                if (is_loaded) {
                                                                    vm.$nextTick(vm.events);
                                                                }
                                                            },

                                                is_video_stop: function(is_video_stop) {
                                                                var vm    = this;
                                                                var video = $(vm.$el).find('.ps_js-media_youtube');
                                                                
                                                                if (is_video_stop) {
                                                                    video.trigger('media_stop');
                                                                }
                                                            }
                                            },  
                                    mounted : function() {
                                                var vm = this;
                                                
                                                $(vm.$el).on('click', '.ps_js-play_button', function () {
                                                    if ($.isFunction(play_button_callback)) {
                                                        play_button_callback();
                                                    }

                                                    if (parseInt($(vm.$el).attr('data-auto-close')) !== 0) {
                                                        ps_popup.modal.close(modal_name);
                                                    }
                                                });

                                                globals.store.store_update(modal_name, {
                                                    is_mounted: true
                                                });
                                            },
                                    methods : {
                                               events: function() {
                                                        var vm = this;
                                                        if (vm.has_video) {
                                                            var video = $(vm.$el).find('.ps_js-media_youtube');

                                                            video.trigger('media_is_playing', function(is_playing) {
                                                                vm.is_video_playing = is_playing;
                                                            });

                                                            video.on(
                                                                'media_ended media_stopped media_error',
                                                                function(e) {
                                                                    vm.is_video_ended = 1;
                                                                }
                                                            );

                                                            var carousel = $(vm.$el).find('.ps_js-carousel_root');
                                                            carousel.on('carousel_moved', function() {
                                                                video.trigger('media_stop');
                                                            });
                                                        }
                                                    }  
                                            }
                                });
                            },
                bind       :  {
                                show:  function() {
                                            globals.store.store_update(modal_name, {
                                                slide_active: 0
                                            });
                                        },
                                shown:  function() {
                                            globals.store.store_update(modal_name, {
                                                is_shown    : true,
                                                is_hide     : false,
                                            });
                                        },
                                hide  :  function() {
                                            globals.store.store_update(modal_name, {
                                                is_hide     : true,
                                                slide_active: 1
                                            });
                                        },
                                hidden :  function() {
                                            globals.store.store_update(modal_name, {
                                                is_hide     : true,
                                                slide_active: 1
                                            });
                                        }
                            },
                onrender    : function () {
                                globals.store.store_update(modal_name, {
                                    is_rendered: true
                                });
                            }
            });
        },

        /**
         * This will popup error that there's still running game to be continued
         * @param  object   error_details   
         * @param  function play_button_callback
         * @return void
         */
        running_game_modal: function(error_details, play_button_callback) {
            var error_message  = ps_language.has_running_error();
            var modal_name     = 'running_game' 
                                + callables.gameName_as_key(error_details.runningGame)
                                + callables.gameName_as_key(error_details.gameName);

            ps_popup.modal.open(modal_name, {
                modal_class: 'running_notice_root',
                header     : error_message.title,
                body       : ps_helper.render_directives(error_details, error_message.content),
                footer     : function(modal_part) {
                                ps_view.render(modal_part, 'running_modal_footer', {
                                    replace: false,
                                    mounted: function() {
                                                var child_vm = this;
                                                var element  = $(child_vm.$el);
                                                // continue
                                                element.find('.ps_js-running_game_continue').on('click', function() {
                                                    if ($.isFunction(play_button_callback)) {
                                                        play_button_callback();
                                                    }
                                                    ps_popup.modal.close(modal_name);
                                                });
                                                
                                                // cancel
                                                element.find('.ps_js-running_game_cancel').on('click', function() {
                                                    ps_popup.modal.close(modal_name);
                                                });
                                            }
                                });
                            }
            });
        }
    };

    return {
        /**
         * custom tag games template thumbnails
         * @return object
         */
        games_template_thumbnail: function() {
            return {
                props   : { productId: {}, liveSearch: { default: false }, gameTypeFilter: { default: false }},
                data    : function() {
                            return {  
                                is_loading        : true, 
                                is_success        : false, 
                                filter            : null,
                                search            : '',
                                last_filters      : {},
                                hovered_images    : [],
                                selected_game_type: '',
                                games_raw         : { rows:[], is_success: false,  err_code: null } 
                            };
                        },
                computed: {
                            games       : function() {
                                            var vm = this;
                                            if ($.isPlainObject(vm.games_raw) && $.isArray(vm.games_raw.rows)) {

                                                var games = { total: vm.games_raw.rows.length  };
                                                return ps_helper.assoc_merge(games, vm.games_raw);

                                            } else {

                                                return { 
                                                    rows      :[], 
                                                    is_success: false,  
                                                    err_code  : ps_language.net_err_code,
                                                    total     : 0
                                                };
                                                
                                            }
                                        },
                            game_keys   : function() {
                                            var vm        = this;
                                            var game_keys = [];

                                            vm.games.rows.forEach(function(value, index) {
                                                game_keys.push(callables.gameName_as_key(value.gameName));
                                            });

                                            return game_keys;
                                        },
                            is_searched : function() {
                                            var vm          = this;
                                            var is_searched = [];

                                            vm.games.rows.forEach(function(value, index) {
                                                is_searched[index] = vm.is_searched_filter(value);
                                            });

                                            return is_searched;
                                        },
                            is_filtered: function() {
                                            var vm          = this;
                                            var is_filtered = [];

                                            vm.games.rows.forEach(function(value, index) {
                                                if (vm.filter == 'new') {
                                                    is_filtered[index] = (value.isNew == 1);
                                                } else {
                                                    is_filtered[index] = true;
                                                }
                                            });

                                            return is_filtered;
                                        },
                            is_hovered: function() {
                                            var vm          = this;
                                            var is_hovered = [];

                                            vm.games.rows.forEach(function(value, index) {
                                                is_hovered[index] = ps_helper.in_array(index, vm.hovered_images);
                                            });

                                            return is_hovered;
                                        },
                            total      : function() {
                                            var vm          = this;
                                            if (vm.is_loading === false) {
                                                var final_total = 0;

                                                vm.games.rows.forEach(function(value, index) {
                                                    if (vm.is_searched[index]) {
                                                        final_total++;
                                                    }
                                                });

                                                return final_total;

                                            } else {

                                                return null;

                                            }
                                        },

                            is_display_result: function() {
                                                return this.total !== null;
                                            },

                            games_by_gameID : function() {
                                                var vm          = this;
                                                var game_object = {};

                                                vm.games.rows.forEach(function(value) {
                                                    game_object[value.gameID] = value;
                                                });

                                                return game_object;
                                            },

                            game_type_filters : function() {
                                                var vm        = this;
                                                var game_types = [];

                                                if (vm.gameTypeFilter == true) {

                                                    vm.games.rows.forEach(function(value) {

                                                        if (!ps_helper.in_array(value.type, game_types)) {
                                                            game_types.push(value.type);
                                                        }

                                                    });

                                                }

                                                return game_types;
                                            },

                            display_game_type : function() {
                                                return this.game_type_filters.length > 1;
                                            },

                            filters           : function() {
                                                var vm = this;
                                                return {
                                                    search            : vm.search,
                                                    selected_game_type: vm.selected_game_type
                                                };
                                            }
                        },
                watch   : {
                            filters: function() {
                                        var  vm = this;
                                        if (vm.total > 0) {
                                            vm.last_filters = vm.filters;
                                        }
                                    }
                        },
                mounted : function() {
                            var vm = this;
                            vm.load();
                            $(vm.$el).on('games_template_reload', function() {
                                if (vm.is_loading == false && vm.games.is_success == false) {
                                    vm.load();
                                } else {
                                    $(vm.$el).find('.ps_js-games_template_form').trigger('view_components_fullreset');
                                    vm.search             = '';
                                    vm.selected_game_type = '';
                                }
                            });

                            $(vm.$el).on('click', '.ps_js-game_type_item', function() {

                                if (vm.display_game_type) {
                                    var game_type = $(this).attr('data-game-type');

                                    if (vm.selected_game_type == game_type) {
                                        vm.selected_game_type = '';
                                    } else {
                                        vm.selected_game_type = game_type;
                                    }
                                }   

                            });
                        },
                methods: {

                            is_searched_filter: function(game_object) {
                                                var vm = this;

                                                // search box
                                                if (!ps_helper.empty(vm.search)) {

                                                    var is_name_contained =  ps_helper.is_contain(
                                                                                vm.search.toLowerCase(), 
                                                                                game_object.gameName.toLowerCase()
                                                                            );
                                                    
                                                    if (!is_name_contained) {
                                                        return false;
                                                    }

                                                } 

                                                // game type
                                                if (!ps_helper.empty(vm.selected_game_type)) {
                                                    
                                                    if (game_object.type != vm.selected_game_type) {
                                                        return false;
                                                    }

                                                } 


                                                return true;
                                            },

                            load     : function() {
                                        var vm        = this;
                                        vm.is_loading = true;
                                        ps_model.get_games(vm.productId, {
                                            success:function(response) {
                                                       vm.games_raw = response;
                                                    },

                                            fail:   function(response) {
                                                        vm.games_raw = response;
                                                    },

                                            error  : function(response) {
                                                        vm.games_raw = response;
                                                    },

                                            complete: function() {
                                                        vm.is_loading = false;
                                                        if (vm.games.is_success) {
                                                            vm.$nextTick(function() {
                                                                vm.form_init();
                                                            });
                                                        }
                                                    }
                                        });
                                    },
                            onerror  : function(gameID, error_details) {
                                        var vm = this;
                                        switch (error_details.dcode) {
                                            case 'HRG': 

                                                callables.running_game_modal(error_details, function() {
                                                    vm.play(error_details._GID, error_details.runningGame);
                                                });

                                                break;

                                            case 'DNR': 

                                                require(['ps_displayname'], function(ps_displayname) {

                                                    ps_displayname.open(function() {
                                                        vm.play(gameID);
                                                    });

                                                });

                                                break;
                                        }
                                    },
                            form_init: function() {
                                        var vm          = this;
                                        var filter_elem = $(vm.$el).find('.ps_js-games_template_switch');
                                        vm.filter       = filter_elem.find('.ps_js-switch_value').val();

                                        filter_elem.off('view_components_change')
                                            .on('view_components_change', function(e, value) {
                                                vm.filter = value;
                                            });

                                        var form = $(vm.$el).find('.ps_js-games_template_form');
                                        form.off('submit')
                                            .on('submit', function(e) {
                                                e.preventDefault();
                                                vm.search = form.find('.ps_js-search_game').val();
                                            });            

                                        $(vm.$el).find('.ps_js-games_template_back').off('click')
                                            .on('click', function() {
                                                form.find('.ps_js-search_game').val(vm.last_filters.search);
                                                form.trigger('submit');

                                                vm.selected_game_type = vm.last_filters.selected_game_type;
                                            });    

                                        $(vm.$el).find('.ps_js-game_item').off('mouseover touchstart')
                                            .on('mouseover touchstart', function() {
                                                var index = parseInt($(this).attr('data-index'));
                                                if (!ps_helper.in_array(index, vm.hovered_images)) {
                                                    vm.hovered_images.push(index);
                                                }
                                            });     

                                        // play button
                                        $(vm.$el).find('.ps_js-play_button').off('click').on('click', function() {
                                            vm.play($(this).attr('data-gid'));
                                        });                       

                                        // modal description modal
                                        $(vm.$el).find('.ps_js-game_modal_trigger').off('click')
                                            .on('click', function() {
                                                var index = parseInt($(this).attr('data-index'));
                                                var game  = vm.games.rows[index];
                                                callables.game_preview_modal(game, vm.game_keys[index], function() {
                                                    vm.play(game.gameID);
                                                });
                                            });     

                                        // live seacrh
                                        if (vm.liveSearch) {
                                            $(vm.$el).find('.ps_js-search_game').off('keyup').on('keyup', function() {
                                                ps_helper.event_delay(function() {
                                                    form.trigger('submit');
                                                }, 600, ps_helper.uniqid());
                                            });
                                        }
                                    },
                            play    : function(gameID, gameName) {
                                        var vm = this;

                                        if (ps_helper.empty(gameName)) {
                                            gameName = vm.games_by_gameID[gameID].gameName;
                                        }

                                        if(ps_helper.is_ios() === false) {
                                            ps_popup.toast.open(gameName, {
                                                title: ps_language.get('messages.opening_game'),
                                                type : 'schedule',
                                                id   : vm.productId
                                            });
                                        }

                                        $(vm.$el).trigger('games_template_clicked', gameID);
                                        ps_model.play(gameID, vm.productId, {
                                            success: function(response) {
                                                        $(vm.$el).trigger('games_template_success',[gameID, response]);
                                                    },
                                            fail   : function(response) {
                                                        $(vm.$el).trigger('games_template_error',[gameID, response]);
                                                        vm.onerror(gameID, response);
                                                    },
                                            complete: function() {
                                                        if(ps_helper.is_ios() == false) {
                                                            ps_popup.toast.close(vm.productId);
                                                        }
                                                    }
                                        });
                                    }
                        }
            };
        },

        /**
         * game template error custom tag
         * @return void
         */
        games_template_error: function() {
            return {
                props:['code']
            };
        },

        /**
         * game template play custom tag
         * @return void
         */
        games_template_play: function() {
            return {
                props:['game']
            };
        }
    };
});