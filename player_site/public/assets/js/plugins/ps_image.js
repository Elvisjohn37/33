/**
 * PS image handler and also wrapper of cropper
 *
 * Author PS Team
 */
define('ps_image', ['jquery','cropper','ps_helper', 'ps_language', 'ps_popup'], function() {
    $             = arguments[0];
    cropper       = arguments[1];
    ps_helper     = arguments[2];
    ps_language   = arguments[3];
    ps_popup      = arguments[4];
    
    var globals   = { debug: false };

    var callables = {
            /**
             * Extend helper debug using local configuration
             */
            debug: ps_helper.debug(globals.debug),

            /**
             * This will render our lazy loaded image 
             * @param  object vm
             * @return void
             */
            lazy_render_image: function(vm) {
                var rendered_data = $(vm.$el).data();
                $(vm.$el).attr('data-default', '');
                $(vm.$el).on('image_loaded', function(e, loaded_function) {
                    if (vm.dom_loaded === true) {

                        loaded_function.apply(this);

                    } else {

                        $(vm.$el).on('image_real_loaded', function(e) {
                            loaded_function.apply(this);
                        });

                    }
                });

                // final resort
                var default_img = function() {
                                    ps_helper.load_image(rendered_data.default, {
                                        success: function(src) {
                                                    vm.final_src  = src;
                                                    vm.is_loading = false;
                                                    vm.$nextTick(function() {
                                                        vm.dom_loaded  = true;
                                                        var image      = $(vm.$el).find('.ps_js-image');
                                                        vm.is_portrait = image.height()>image.width();
                                                        $(vm.$el).trigger('image_real_loaded');
                                                    });
                                                },
                                        error   : function(src) {
                                                    callables.debug(
                                                        'Please setup a proper fallback image, '
                                                        + src +' does not exists!'
                                                    );
                                                }
                                    });
                                };

                // first error callback attempt               
                var image_error = function() {
                                    vm.is_broken = true;

                                    if (vm.default) {
                                        ps_helper.load_image(vm.default, {
                                            success: function(src) {
                                                        vm.final_src  = src;
                                                        vm.is_loading = false;
                                                        vm.$nextTick(function() {
                                                            vm.dom_loaded  = true;
                                                            var image      = $(vm.$el).find('.ps_js-image');
                                                            vm.is_portrait = image.height()>image.width();
                                                            $(vm.$el).trigger('image_real_loaded');
                                                        });
                                                    },
                                            error   : function() {
                                                        default_img();
                                                    }
                                        });
                                    } else {
                                        default_img();
                                    }
                                };

                // load real image
                if (!ps_helper.empty(vm.src)) {
                    ps_helper.load_image(vm.src, {
                        success: function(src) {
                                    vm.final_src  = src;
                                    vm.is_loading = false;
                                    vm.$nextTick(function() {
                                        vm.dom_loaded  = true;
                                        var image      = $(vm.$el).find('.ps_js-image');
                                        vm.is_portrait = image.height()>image.width();
                                        $(vm.$el).trigger('image_real_loaded');
                                    });
                                },
                        error   : function() {
                                    image_error();
                                }
                    });
                } else {
                    image_error();
                }
            },

            /** 
             * This will render image crop
             * @param  object vm
             * @return void
             */
            crop_render_image: function(vm) {
                // remove crop intance first if there's any
                $(vm.$el).trigger('image_remove_crop');

                $(vm.$el).on('image_error_crop', function() {
                    vm.is_error   = true;
                    vm.is_loading = false;

                    // open a toast
                    var message=ps_language.error(ps_language.image_crop_error);
                    ps_popup.toast.open(message.content, {
                        title: message.title,
                        auto : true
                    });
                });

                // load real image
                if (!ps_helper.empty(vm.src)) {

                    ps_helper.load_image(vm.src, {
                        success: function(src) {
                                    vm.is_error   = false;
                                    vm.is_loading = false;
                                    vm.$nextTick(function() {
                                        var image      = $(vm.$el).find('.ps_js-image_crop');
                                        vm.is_portrait = image.height()>image.width();
                                        $(vm.$el).trigger('image_loaded_crop', vm.is_portrait);
                                        
                                        vm.$nextTick(function() {
                                            callables.cropping($(vm.$el), vm.is_portrait);
                                        });
                                    });
                                },

                        error   : function() {
                                    $(vm.$el).trigger('image_error_crop');
                                }
                    });

                } else {
                    $(vm.$el).trigger('image_error_crop');
                }
            },

            /**
             * Initialize cropping plugin - cropper
             * @param  dom      element 
             * @param  boolean  is_portrait 
             * @return void
             */
            cropping: function(element, is_portrait) {
                var options                 = element.data()       || {};
                options.viewMode            = options.viewMode     || 1;
                options.dragMode            = options.dragMode     || 'none';
                options.movable             = options.movable      || false;
                options.rotatable           = options.rotatable    || true;
                options.scalable            = options.scalable     || false;
                options.zoomable            = options.zoomable     || false;
                options.zoomOnTouch         = options.zoomOnTouch  || false;
                options.zoomOnWheel         = options.zoomOnWheel  || false;
                options.square              = options.square       || true;
                options.minimum             = options.minimum      || 50;
                options.outputWidth         = options.outputWidth  || 180;
                options.outputHeight        = options.outputHeight || 180;
                options.strict              = options.strict       || false;
                var image                   = element.find('.ps_js-image_crop');
                var mandatory_options       = {};

                // add minimum height and width
                if (options.hasOwnProperty('minimum')) {
                    mandatory_options.minCropBoxWidth  = options.minimum;
                    mandatory_options.minCropBoxHeight = options.minimum;
                }

                // maintain square aspect ratio
                if (options.square) {
                    mandatory_options.aspectRatio  = 1/1;
                }

                // update base64
                var conversion_delay_id = 'imgae_crop_conversion' + ps_helper.uniqid();
                image.on('crop', function() {

                    ps_helper.event_delay(function() {
                        
                        var base64 = image.cropper('getCroppedCanvas', {
                                        width : options.outputWidth, 
                                        height: options.outputHeight}
                                    ).toDataURL();

                        element.find('.ps_js-image_save_base64').val(base64);

                    }, 300, conversion_delay_id);

                });

                image.cropper(ps_helper.assoc_merge(options, mandatory_options));

                // custom events
                element.on('image_remove_crop', function() {
                    image.cropper('destroy');
                });

                element.on('image_rotate', function(e, number) {
                    number = number || 90;
                    image.cropper('rotate', number);
                });

                // reset on window resize
                var delay_id = 'imgae_crop_resize' + ps_helper.uniqid();
                $(window).on('resize', function() {
                    ps_helper.event_delay(function() {
                        image.cropper('destroy');
                        image.cropper(ps_helper.assoc_merge(options, mandatory_options));
                    }, 300, delay_id);
                });
            }
        };

    return {
        /**
         * Image lazy loader tag
         * @return void
         */
        image_lazy: function() {
            return {
                data: function() {
                        return {
                            is_loading : true,
                            final_src  : '',
                            dom_loaded : false,
                            is_broken  : false,
                            is_portrait: false
                        };
                    },
                watch  : {
                            src: function() { callables.lazy_render_image(this); }
                        },
                props  : ['src','alt','default','background'],
                mounted: function() { callables.lazy_render_image(this); }
            };
        },

        /**
         * This will render image and add cropping functionality
         * @return void
         */
        image_crop: function() {
            return {
                data    : function() { return { is_loading: true, is_error: false, is_portrait: false }; },
                watch  : {
                            src: function() { callables.crop_render_image(this); },
                        },
                props   : ['src','outputWidth','outputHeight','name'],
                mounted : function() { callables.crop_render_image(this); },
                beforeDestroy: function() {
                                // auto remove when element is destroyed
                                $(this.$el).trigger('image_remove_crop');
                            }

            };
        }
    };
});