/**
 * PS audio and video handler
 *
 * Author PS Team
 */
define('ps_media', ['jquery','ps_helper','ps_popup','ps_language','ps_store','ps_localstorage','ps_model'], function() {
    $               = arguments[0];
    ps_helper       = arguments[1];
    ps_popup        = arguments[2];
    ps_language     = arguments[3];
    ps_store        = arguments[4];
    ps_localstorage = arguments[5];
    ps_model        = arguments[6];

    var globals   = { 
                        debug : true,
                        store : new ps_store('ps_media'),
                        sounds: {}
                    };

    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * Get current sound settings
         * @return object
         */
        sound_settings: function() {
            if (!globals.store.store_exists('sound_settings')) {

                globals.store.store_update('sound_settings', {
                    is_on: ps_localstorage.get('media_sound_status')
                });

                ps_localstorage.watch('media_sound_status', function(value) {
                    globals.store.store_update('sound_settings', {
                        is_on: value
                    });
                });
            }

            return globals.store.store_fetch('sound_settings');
        }
    };

    return {
        /**
         * image-webcam custom tag
         * @return object
         */
        media_webcam: function() {
            return {
                data   : function() { return { is_loading: true }; },
                watch  : {
                            active: function(is_active) {
                                        var vm = this;
                                        if (is_active) {
                                            $(vm.$el).trigger('media_webcam_play');
                                        } else {
                                            $(vm.$el).trigger('media_webcam_stop');
                                        }
                                    }
                        },
                props  : { active:{} ,height:{}, width:{} },
                mounted: function() {
                            var vm = this;
                            
                            // add custom events
                            $(vm.$el).on('media_webcam_error', function() {
                                // open a toast
                                var message = ps_language.error(ps_language.media_webcam_error);
                                ps_popup.toast.open(message.content, {
                                    title: message.title,
                                    auto : true
                                });
                            });

                            $(vm.$el).on('media_webcam_play', function() {
                                vm.is_loading = true;

                                ps_helper.video_stream({
                                    load: function(stream, load_to_video) {
                                            vm.is_loading = false;

                                            vm.$nextTick(function() {
                                                var video_raw = $(vm.$el).find('.ps_js-media_webcam_video')[0];

                                                $(vm.$el).off('media_webcam_pause').on('media_webcam_pause',function() {
                                                    video_raw.pause();
                                                });

                                                $(vm.$el).off('media_webcam_capture')
                                                    .on('media_webcam_capture',function(e, capture_handler) {
                                                        $(vm.$el).trigger('media_webcam_pause');

                                                        if ($.isFunction(capture_handler)) {
                                                            capture_handler.call(
                                                                video_raw,
                                                                ps_helper.video_to_base64(video_raw)
                                                            );
                                                        }
                                                    });

                                                // replay can only be used to a webcam that only been paused
                                                $(vm.$el).off('media_webcam_replay')
                                                    .on('media_webcam_replay', function() {
                                                        video_raw.play();
                                                    });

                                                $(vm.$el).off('media_webcam_stop')
                                                    .on('media_webcam_stop', function() {
                                                        $(vm.$el).trigger('media_webcam_pause');
                                                        stream.getTracks()[0].stop();

                                                        // remove previous custom events
                                                        $(vm.$el).off('media_webcam_pause');
                                                        $(vm.$el).off('media_webcam_replay');
                                                        $(vm.$el).off('media_webcam_stop');
                                                    });

                                                // load video
                                                load_to_video(video_raw);
                                            });
                                        },

                                    error: function(error, is_unsupported) {
                                            // trigger custom events
                                            $(vm.$el).trigger('media_webcam_error', arguments);

                                            if (is_unsupported) {
                                                ps_model.browser_not_recommended();
                                            }
                                        }

                                },{ height: vm.height, width: vm.width });
                            });
                            
                            // play for first time
                            if (vm.is_active) {
                                $(vm.$el).trigger('media_webcam_play');
                            }
                        }
            };
        },

        /**
         * media youtube custom tag for embedding youtube videos
         * @return object
         */
        media_youtube: function() {
            return {
                data   : function() {
                            return { 
                                is_loading: true,
                                is_error  : false, 
                                is_playing: false,
                                id        : ps_helper.uniqid()
                            };
                        },
                // this is the options PS currently using
                // you can see and add other available parameters here 
                // https://developers.google.com/youtube/player_parameters
                // NOTE: we'll only use loadVideoByUrl
                props  : { 
                            src           : {}, 
                            width         : { default: '690' }, 
                            height        : { default: '414' },
                            autoplay      : { default: 0     },
                            autohide      : { default: 1     },
                            rel           : { default: 0     },
                            showinfo      : { default: 0     },
                            disablekb     : { default: 0     },
                            iv_load_policy: { default: 3     },
                            controls      : { default: 1     },
                            fs            : { default: 0     },
                            modestbranding: { default: 0     },
                        },
                mounted: function() {
                            var vm = this;
                            if (!ps_helper.empty(vm.src)) {

                                // triggers when called and media_loaded fires
                                $(vm.$el).on('media_is_loaded', function(e, callback) {
                                    if (!vm.is_loading && !vm.is_error) {

                                        if ($.isFunction(callback)) {
                                            callback.apply(this);
                                        }

                                    } else {

                                        $(vm.$el).on('media_loaded', function() {
                                            if ($.isFunction(callback)) {
                                                callback.apply(this);
                                            }
                                        });
                                    }
                                });

                                // triggers when called and media_error fires
                                $(vm.$el).on('media_is_error', function(e, callback) {
                                    if (!vm.is_loading && vm.is_error) {

                                        if ($.isFunction(callback)) {
                                            callback.apply(this);
                                        }
                                        
                                    } else {

                                        $(vm.$el).on('media_error', function() {
                                            if ($.isFunction(callback)) {
                                                callback.apply(this);
                                            }
                                        });

                                    }
                                });

                                // triggers when playing and stopped playing
                                $(vm.$el).on('media_is_playing', function(e, callback) {
                                    if ($.isFunction(callback)) {
                                        callback.call(this, vm.is_playing);
                                    }
                                    
                                    $(vm.$el).on('media_playing', function() {
                                        vm.is_playing = true;
                                        if ($.isFunction(callback)) {
                                            callback.call(this, vm.is_playing);
                                        }
                                    });

                                    $(vm.$el).on('media_unstarted media_ended media_paused media_stopped', function() {
                                        vm.is_playing = false;
                                        if ($.isFunction(callback)) {
                                            callback.call(this, vm.is_playing);
                                        }
                                    });
                                });
                                
                                // load youtube video
                                require(['youtube'], function() {
                                    if (YT.loaded) {
                                        vm.load_video();
                                    } else {
                                        window.onYouTubeIframeAPIReady = vm.load_video;
                                    }
                                }, vm.error);

                            } else {

                                vm.error();
                                callables.debug('Youtube player needs a SRC URL.');

                            }
                        },
                methods : {
                            error      : function(error) {

                                            var vm = this;
                                            vm.is_error   = true;
                                            vm.is_loading = false;
                                            $(vm.$el).trigger('media_error');

                                        },
                            ready       : function(player) {
                                            var vm = this;
                                            
                                            var youtube_id = ps_helper.extract_youtube_id(vm.src);
                                            if (youtube_id !== false) {
                                                if (vm.autoplay) {
                                                    player.loadVideoById({ videoId: youtube_id });
                                                } else {
                                                    player.cueVideoById({ videoId: youtube_id });
                                                }

                                            } else {

                                                if (vm.autoplay) {
                                                    player.loadVideoByUrl({ mediaContentUrl: vm.src });
                                                } else {
                                                    player.cueVideoByUrl({ mediaContentUrl: vm.src });
                                                }

                                            }

                                            $(vm.$el).trigger('media_loaded');

                                            // additional events
                                            $(vm.$el).on('media_stop', function() {

                                                try {

                                                    player.stopVideo();
                                                    $(vm.$el).trigger('media_stopped'); 

                                                } catch(error) {

                                                    callables.debug(error.message);

                                                }
                                            });

                                            $(vm.$el).on('media_pause', function() {
                                                try {

                                                    player.pauseVideo();

                                                } catch(error) {

                                                    callables.debug(error.message);

                                                }
                                            });

                                            $(vm.$el).on('media_play', function() {
                                                try {
                                                    
                                                    player.playVideo();

                                                } catch(error) {

                                                    callables.debug(error.message);

                                                }
                                            });

                                            $(vm.$el).on('replay', function() {
                                                try {
                                                    
                                                    player.seekTo(0);
                                                    $(vm.$el).trigger('media_play');

                                                } catch(error) {

                                                    callables.debug(error.message);

                                                }
                                            });

                                            vm.is_loading = false; 

                                        },
                            state_change: function(e) {
                                            var vm = this;
                                            switch (e.data) {
                                                case -1: $(vm.$el).trigger('media_unstarted'); break;
                                                case  0: $(vm.$el).trigger('media_ended');     break;
                                                case  1: $(vm.$el).trigger('media_playing');   break;
                                                case  2: $(vm.$el).trigger('media_paused');    break;
                                                case  3: $(vm.$el).trigger('media_buffering'); break;
                                                case  5: $(vm.$el).trigger('media_cued');      break;
                                            }
                                        },
                            load_video  : function() {
                                            var vm     = this;
                                            var player = new YT.Player(vm.id, {
                                                            height        : vm.height,
                                                            width         : vm.width,
                                                            playerVars    : {
                                                                                enablejsapi   : 1,
                                                                                autoplay      : 0,
                                                                                autohide      : vm.autohide,
                                                                                rel           : vm.rel,
                                                                                showinfo      : vm.showinfo,
                                                                                disablekb     : vm.disablekb,
                                                                                iv_load_policy: vm.iv_load_policy,
                                                                                controls      : vm.controls,
                                                                                fs            : vm.fs,
                                                                                modestbranding: vm.modestbranding,
                                                                            },
                                                            events        : {
                                                                                onReady      : function() {
                                                                                                vm.ready(player);
                                                                                            },
                                                                                onStateChange: function(e) {
                                                                                                vm.state_change(e);
                                                                                            },
                                                                                onError     : function() {
                                                                                                vm.error();
                                                                                            }
                                                                            }
                                                        });
                                        },
                        }
            };
        },

        /**
         * Custom tag sound-manager
         * @return void
         */
        media_sound_manager: function() {
            return {
                data    : function() { return { info: callables.sound_settings() }; },
                computed: {
                            is_on: function() {
                                    var vm = this;
                                    return vm.info.is_on === true;
                                }
                        },
                mounted : function() {
                            var vm = this;

                            $(vm.$el).on('click', function() {
                                var new_value = vm.is_on ? false : true;

                                globals.store.store_update('sound_settings', {
                                    is_on: new_value
                                });

                                ps_localstorage.set('media_sound_status', new_value);
                            }); 
                        }
            };
        },

        /**
         * Custom tag sound
         * This can be used to wrap element play sound with set events
         * @return void
         */
        media_sound: function() {
            return {
                 data   : function() {
                            return { 
                                info      : callables.sound_settings(),
                                id        : ps_helper.uniqid(),
                                play_count: 0
                            }; 
                        },
                computed: {
                            is_on:  function() {
                                        var vm = this;
                                        return vm.info.is_on === true;
                                    },
                            triggers: function() {
                                        var vm       = this;
                                        var triggers = {};

                                        if (!ps_helper.empty(vm.playTrigger)) {
                                            triggers[vm.playTrigger] = triggers[vm.playTrigger] || [];
                                            triggers[vm.playTrigger].push('play');
                                        }

                                        if (!ps_helper.empty(vm.stopTrigger)) {
                                            triggers[vm.stopTrigger] = triggers[vm.stopTrigger] || [];
                                            triggers[vm.stopTrigger].push('stop');
                                        }

                                        if (!ps_helper.empty(vm.pauseTrigger)) {
                                            triggers[vm.pauseTrigger] = triggers[vm.pauseTrigger] || [];
                                            triggers[vm.pauseTrigger].push('pause');
                                        }

                                        return triggers;
                                    }
                        },
                props   : {
                            file         : {},
                            playTrigger  : { default: 'media_immediate' },
                            stopTrigger  : { default: null              },
                            pauseTrigger : { default: null              },
                            loop         : { default: 1                 },
                            volume       : { default: 1                 }
                        },
                watch   : {
                            is_on: function(is_on) {
                                    var vm = this;
                                    vm.adjust({ 
                                        volume: vm.is_on ? vm.volume : 0 
                                    });
                                }
                        },
                mounted : function() {
                            var vm = this;
                            
                            if (!ps_helper.empty(vm.file)) {

                                globals.sounds[vm.id] = new Audio(vm.file);
                                vm.adjust({ 
                                    volume: vm.is_on ? vm.volume : 0 
                                });

                                // loop
                                $(globals.sounds[vm.id]).on('ended', function() {

                                    if (vm.loop == 'infinite' || vm.play_count < vm.loop) {
                                        globals.sounds[vm.id].currentTime = 0
                                        this.play();
                                        vm.play_count++;
                                    }

                                });

                                for (var trigger in vm.triggers) {
                                    vm.add_event(trigger, vm.triggers[trigger]);
                                }

                                // built-in custom event
                                vm.event_target().trigger('media_immediate');
                            }
                        },
                methods: {
                            event_target   : function() {
                                                var vm = this;
                                                if (vm.$el.hasChildNodes()) {
                                                    return $(vm.$el);
                                                } else {
                                                    return $(window);
                                                }
                                            },
                            adjust         : function(properties) {
                                                var vm = this;
                                                if (globals.sounds.hasOwnProperty(vm.id)) {
                                                    for (var property in properties) {
                                                        globals.sounds[vm.id][property] = properties[property];
                                                    }
                                                }
                                            },
                            play           : function() {
                                                var vm = this;
                                                if (globals.sounds[vm.id].ended) {
                                                    globals.sounds[vm.id].currentTime = 0
                                                }

                                                vm.play_count = 1;
                                                globals.sounds[vm.id].play();
                                            },
                            pause          : function() {
                                                var vm = this;
                                                globals.sounds[vm.id].pause();
                                            },
                            stop           : function() {
                                                var vm = this;
                                                vm.play_count                     = 1;
                                                globals.sounds[vm.id].currentTime = 0;
                                                globals.sounds[vm.id].pause();
                                            },


                            add_event: function(trigger, actions) {
                                        var vm = this;
                                        vm.event_target().on(trigger, function() {
                                            if (actions.length > 1) {
                                                
                                                var is_playing = ps_helper.is_audio_playing(globals.sounds[vm.id]);
                                                if (ps_helper.in_array('play',actions) && !is_playing) {

                                                    vm['play']();

                                                }  else {

                                                    var has_pause = ps_helper.in_array('pause',actions);
                                                    var has_stop  = ps_helper.in_array('stop',actions);

                                                    if (has_pause && has_stop)  {
                                                        callbles.debug(
                                                            'You cant put pause and stop in same trigger'
                                                            + ', triggering pause instead'
                                                        );

                                                        vm['pause']();

                                                    } else if (has_pause) {

                                                        vm['pause']();

                                                    } else if (has_stop) {

                                                        vm['stop']();

                                                    }

                                                }

                                            } else {

                                                vm[actions[0]]();
                                            
                                            }

                                        });
                                    }
                        }         
            };
        }
    };
});