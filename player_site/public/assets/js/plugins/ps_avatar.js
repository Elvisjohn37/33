
/**
 * PS avatar
 * 
 * @author PS Team
 */
define('ps_avatar', ['jquery','ps_popup','ps_view','ps_model','ps_helper','ps_store','ps_validator'], function() {

    var $              = arguments[0];
    var ps_popup       = arguments[1];
    var ps_view        = arguments[2];
    var ps_model       = arguments[3];
    var ps_helper      = arguments[4];
    var ps_store       = arguments[5];
    var ps_validator   = arguments[6];


    var globals   = { 
                        debug                 : true, 
                        is_modal_set          : false, 
                        store                 : new ps_store('ps_avatar'),
                        secondary_action_modal: 'avatar_secondary_action',
                        websocket_subscribed  : false
                    };
    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will open avatar modal
         * @return void
         */
        open_avatar_modal: function() {
            if (ps_popup.modal.is_active('avatar', 'avatar')) {
                callables.debug('Avatar modal is already open!');
                return false;
            }

            var avatar_modal_store = callables.avatar_store_reset();

            ps_popup.modal.open('avatar', {
                modal_class: 'avatar_modal_root',
                body       : function(modal_part) {
                                ps_view.render(modal_part, 'avatar_modal', {
                                    replace: false,
                                    data    : { 
                                                info           : avatar_modal_store,
                                                avatars        : { list:[] },
                                                events_handled : false
                                            },
                                    computed: {
                                                formatted_list: callables.avatar_formatted_list,
                                                selected      : function() {
                                                                    var vm = this;
                                                                    if (vm.info.active_imgOrder) {
                                                                        return vm.info.active_imgOrder;
                                                                    } else {
                                                                        var selected = 1;

                                                                        // set default active imgOrder
                                                                        $.each(vm.avatars.list, function(index, avatar){
                                                                            if (avatar.isActive) {
                                                                                selected = avatar.imgOrder;
                                                                                return false;
                                                                            }
                                                                        });

                                                                        return selected;
                                                                    }
                                                                },
                                                is_loading    : function() {
                                                                    var vm         = this;
                                                                    var no_active = vm.selected===null;
                                                                    var no_list   = ps_helper.empty(vm.formatted_list);
                                                                    return (no_active || no_list);
                                                                }
                                            },
                                    mounted : function() {
                                                var vm = this;

                                                // inital avatar data
                                                ps_model.avatars({
                                                    success: function(response) {
                                                                vm.avatars = response;
                                                            }
                                                });
                                            },
                                    updated : function() {
                                                var vm = this;
                                                if (!vm.events_handled) {
                                                    vm.events_handled = true;
                                                    callables.avatar_modal_events(vm);
                                                }
                                            }
                                });
                            },
                bind       : {
                                show: function() {
                                        globals.store.store_update('avatar_modal', { is_open: true });
                                    },
                                hide: function() {
                                        globals.store.store_update('avatar_modal', { is_open: false });
                                        ps_popup.modal.close('avatar_crop'  , globals.avatar_secondary_action);
                                        ps_popup.modal.close('avatar_webcam', globals.avatar_secondary_action);
                                    }
                            },
                onrender   : function() {
                                // update avatar if not the first time modal was open
                                if (globals.is_modal_set) {
                                    ps_model.avatars();
                                } else {
                                    globals.is_modal_set = true;
                                }
                            }
            },'avatar');
            
            // subscribe to websocket
            if (!globals.websocket_subscribed) {
                globals.websocket_subscribed = true;
                require(['ps_websocket'], function(ps_websocket) {
                    ps_websocket.subscribe('open_avatar', function(message) {
                        if (parseInt(message) === 1) {
                            // update avatar list only
                            ps_model.avatars();
                        }
                    });
                });
            }
        },

        /**
         * This will create or reset the current avatar modal store
         * @return object
         */
        avatar_store_reset: function() {
            globals.store.store_update('avatar_modal', {
                active_imgOrder: null,
                is_open        : false
            });

            return globals.store.store_fetch('avatar_modal');
        },

        /**
         * Hanlder of avatar modal formatted_list computed property
         * @return object
         */
        avatar_formatted_list: function() {
            var vm = this;

            var avatar_formatted_list = {};

            // set default active imgeOrder
            vm.avatars.list.forEach(function(avatar, index) {
                avatar_formatted_list[avatar.imgOrder] = { raw: avatar };

                // status
                if (avatar.status == 3) {
                    avatar_formatted_list[avatar.imgOrder].status_text = 'rejected';
                } else if (avatar.status == 2) {
                    avatar_formatted_list[avatar.imgOrder].status_text = 'pending';
                } else if (avatar.isActive) {
                    avatar_formatted_list[avatar.imgOrder].status_text = 'active';
                } else {
                    avatar_formatted_list[avatar.imgOrder].status_text = 'approved';
                }

                // can upload
                if ((avatar.isActive == 0) || (avatar.isActive == 1 && avatar.status == 0)) {
                    avatar_formatted_list[avatar.imgOrder].can_upload = true;
                } else {
                    avatar_formatted_list[avatar.imgOrder].can_upload = false;
                }

                // can set as primary
                if (avatar.isActive == 0 && ps_helper.in_array(avatar.status,[0,1])) {
                    avatar_formatted_list[avatar.imgOrder].can_set_primary = true; 
                } else {
                    avatar_formatted_list[avatar.imgOrder].can_set_primary = false; 
                }
            });

            return avatar_formatted_list;
        },

        /**
         * Bind all avatar modal events
         * @param  object vm 
         * @return void
         */
        avatar_modal_events: function (vm) {
            var modal = $(vm.$el);

            // images click
            modal.find('.ps_js-avatar_image').on('click', function() {
                globals.store.store_update('avatar_modal', {
                    active_imgOrder:$(this).attr('data-order')
                });
            });

            // upload button
            modal.find('[name=ps_js-avatar_upload]').on('change', function() {
                if (!ps_helper.empty($(this).val())) {

                    // open cropping modal
                    callables.avatar_crop_modal(vm);

                    // start reading the file
                    ps_helper.read_files($(this), { 
                        progress: function(file) { 
                                    // only the first file will be accepted
                                    if (file.id == 0) {

                                        if (file.percent === 100) {
                                            globals.store.store_update('crop_modal', {
                                                file_base64: file.result,
                                            });
                                        }

                                        globals.store.store_update('crop_modal', {
                                            percent    : file.percent,
                                            file_name  : file.info.name
                                        });
                                    }
                                },

                        error   : function(file) { 
                                    // only the first file will be accepted
                                    if (file.id == 0) {
                                    }
                                }
                    });

                }
            });

            // webcam button
            modal.find('.ps_js-avatar_open_webcam').on('click', function() {
                callables.avatar_open_webcam(vm);
            });
            
            // set as primary button
            modal.find('.ps_js-avatar_set_profile').on('click', function() {
                ps_model.avatar_set_primary(vm.selected);
            });

            // set as primary button
            modal.find('.ps_js-avatar_modal_close').on('click', function() {
                ps_popup.modal.close('avatar','avatar');
            });
        },

        /**
         * This will open the modal where our avatar will be croped and uploaded
         * @param  int     main_modal_vm     object
         * @param  boolean is_camera         if set to true this means its from camera
         * @return void
         */
        avatar_crop_modal: function(main_modal_vm, is_camera) {
            var crop_modal_store = callables.crop_store_reset();
            globals.store.store_update('crop_modal', {
                imgOrder : main_modal_vm.selected,
                is_camera: is_camera || false 
            });

            ps_popup.modal.open('avatar_crop', {
                modal_class: 'avatar_crop_root',
                body: function(modal_part) {
                        ps_view.render(modal_part, 'avatar_crop', {
                            replace : false,
                            data    : crop_modal_store,
                            computed: {
                                        final_percent: function() {
                                                        return this.is_rendered ? this.percent : 0;
                                                    }
                                    },
                            mounted: function() {
                                        var vm = this;
                                        callables.crop_modal_events(vm, main_modal_vm);
                                    }
                        });
                    },
                bind    : {
                            hide: function() {
                                    // reset file upload
                                    $('[name=ps_js-avatar_upload]').each(function() {
                                        $(this).val('');
                                    });

                                    $(this).find('.ps_js-image_crop').trigger('image_remove_crop');
                                },

                            shown: function() {
                                    setTimeout(function(){
                                        globals.store.store_update('crop_modal', { is_rendered: true });
                                    },100);
                                }
                        }
            }, globals.avatar_secondary_action);
        },

        /**
         * This will create or reset the current crop modal store
         * @return object
         */
        crop_store_reset: function() {
            globals.store.store_update('crop_modal', {
                percent    : 0,
                file_name  : '',
                file_base64: '',
                is_rendered: false,
                imgOrder   : 0,
                uploading  : 0,
                is_camera  : false
            });

            return globals.store.store_fetch('crop_modal');
        },

        /**
         * Bind crop modal events
         * @param  object vm           
         * @param  object main_modal_vm 
         * @return void
         */
        crop_modal_events: function(vm, main_modal_vm) {
            var element = $(vm.$el);

            // crop image render error
            element.on('image_error_crop', '.ps_js-image_cropper', function(e,err_code) {
                // close the modal
                ps_popup.modal.close('avatar_crop', globals.avatar_secondary_action);
            });

            // cancel button
            element.on('click', '.ps_js-crop_cancel', function() {
                ps_popup.modal.close('avatar_crop',globals.avatar_secondary_action);
            });

            // retake button
            element.on('click','.ps_js-crop_retake', function() {
                callables.avatar_open_webcam();
            });

            // rotate button
            element.on('click','.ps_js-crop_rotate', function() {
                element.find('.ps_js-image_cropper').trigger('image_rotate');
            });

            // form
            ps_helper.ready('.ps_js-avatar_crop_form', function() {
                var form = element.find('.ps_js-avatar_crop_form');
                ps_validator.apply(form, {
                    validations: {
                                    '.ps_js-imgOrder': {
                                        validate: [{as:'imgOrder'}]
                                    }, 
                                    '.ps_js-image_save_base64': {
                                        validate: [{as:'required'}]
                                    }
                                },
                    success    : function() {
                                    ps_model.avatar_upload(vm.imgOrder, form.find('.ps_js-image_save_base64').val(),{
                                        progress: function(percent) {
                                                    globals.store.store_update('crop_modal', { uploading  : percent });
                                                },
                                        complete: function() {
                                                    ps_popup.modal.close('avatar_crop', globals.avatar_secondary_action);
                                                }
                                    });
                                }
                });
            }, element);
        },

        /**
         * This will open avatar webcam
         * @param  int   main_modal_vm  pass the vm instance of parent modal
         * @return void
         */
        avatar_open_webcam: function(main_modal_vm) {
            var webcam_store = callables.webcam_store_reset();

            if (ps_popup.modal.exists('avatar_webcam', globals.avatar_secondary_action)) {
                globals.store.store_update('webcam_modal', { is_active: true });
            }

            ps_popup.modal.open('avatar_webcam', {
                modal_class: 'avatar_webcam_root',
                body: function(modal_part) {
                        ps_view.render(modal_part, 'avatar_webcam', {
                            replace : false,
                            data    : webcam_store,
                            mounted : function() { 
                                callables.webcam_modal_events(this, main_modal_vm); 
                                globals.store.store_update('webcam_modal', { is_active: true });
                            }
                        });
                    },
                bind: {
                        hide: function() {
                                globals.store.store_update('webcam_modal', { is_active: false });
                            }
                    }
            }, globals.avatar_secondary_action);
        },

        /**
         * This will create or reset the current webcam modal store
         * @return object
         */
        webcam_store_reset: function() {
            globals.store.store_update('webcam_modal', { is_active:false, has_captured: false });
            return globals.store.store_fetch('webcam_modal');
        },

        /**
         * Bind events for webcam modal elements
         * @param  object vm     
         * @param  object main_modal_vm 
         * @return void
         */
        webcam_modal_events: function(vm, main_modal_vm) {
            $(vm.$el).find('.ps_js-media_webcam').on('media_webcam_error', function(){
                ps_popup.modal.close('avatar_webcam', globals.avatar_secondary_action);
            });

            // capture
            $(vm.$el).find('.ps_js-avatar_capture').on('click', function() {
                var modal_content = $(vm.$el).closest('.ps_js-modal_avatar_webcam');
                var modal_webcam  = modal_content.find('.ps_js-media_webcam');
                
                if (modal_webcam.length>0) {

                    modal_webcam.trigger('media_webcam_capture', function(base64) {
                        // open cropping modal
                        callables.avatar_crop_modal(main_modal_vm, true);

                        globals.store.store_update('crop_modal', {
                            percent    : 100,
                            file_base64: base64,
                            file_name  : 'video'
                        });
                    });

                    globals.store.store_update('webcam_modal', { has_captured: true });

                } else {

                    callables.debug('Failed to capture, webcam is not yet ready');

                }
            });

            // cancel
            $(vm.$el).find('.ps_js-avatar_webcam_cancel').on('click', function() {
                ps_popup.modal.close('avatar_webcam', globals.avatar_secondary_action);
            });
        }
    };

    return {
        open_avatar_modal: callables.open_avatar_modal,
        /**
         * Avatar primary edit custom tag
         * @return object
         */
        avatar_primary_edit: function() {
            return {
                mounted: function() {
                            var vm = this;
                            $(vm.$el).find('.ps_js-avatar_edit').on('click', callables.open_avatar_modal);            
                        }
            };
        },
        
        /**
         * Avatar selector 
         * This tag is just to select active avatar image in the list
         * @return object
         */
        avatar_selector: function() {
            return {
                props: ['active'],
                watch: {
                        active: function(active) {
                                    globals.store.store_update('avatar_modal', {
                                        active_imgOrder: active
                                    });
                                }
                    }
            };
        }
    };
});