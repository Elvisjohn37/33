define("ps_avatar",["jquery","ps_popup","ps_view","ps_model","ps_helper","ps_store","ps_validator"],function(){var a=arguments[0],e=arguments[1],t=arguments[2],r=arguments[3],o=arguments[4],s=arguments[5],_=arguments[6],n={debug:!0,is_modal_set:!1,store:new s("ps_avatar"),secondary_action_modal:"avatar_secondary_action",websocket_subscribed:!1},c={debug:o.debug(n.debug),open_avatar_modal:function(){if(e.modal.is_active("avatar","avatar"))return c.debug("Avatar modal is already open!"),!1;var s=c.avatar_store_reset();e.modal.open("avatar",{modal_class:"avatar_modal_root",body:function(e){t.render(e,"avatar_modal",{replace:!1,data:{info:s,avatars:{list:[]},events_handled:!1},computed:{formatted_list:c.avatar_formatted_list,selected:function(){var e=this;if(e.info.active_imgOrder)return e.info.active_imgOrder;var t=1;return a.each(e.avatars.list,function(a,e){if(e.isActive)return t=e.imgOrder,!1}),t},is_loading:function(){var a=this,e=null===a.selected,t=o.empty(a.formatted_list);return e||t}},mounted:function(){var a=this;r.avatars({success:function(e){a.avatars=e}})},updated:function(){var a=this;a.events_handled||(a.events_handled=!0,c.avatar_modal_events(a))}})},bind:{show:function(){n.store.store_update("avatar_modal",{is_open:!0})},hide:function(){n.store.store_update("avatar_modal",{is_open:!1}),e.modal.close("avatar_crop",n.avatar_secondary_action),e.modal.close("avatar_webcam",n.avatar_secondary_action)}},onrender:function(){n.is_modal_set?r.avatars():n.is_modal_set=!0}},"avatar"),n.websocket_subscribed||(n.websocket_subscribed=!0,require(["ps_websocket"],function(a){a.subscribe("open_avatar",function(a){1===parseInt(a)&&r.avatars()})}))},avatar_store_reset:function(){return n.store.store_update("avatar_modal",{active_imgOrder:null,is_open:!1}),n.store.store_fetch("avatar_modal")},avatar_formatted_list:function(){var a=this,e={};return a.avatars.list.forEach(function(a,t){e[a.imgOrder]={raw:a},3==a.status?e[a.imgOrder].status_text="rejected":2==a.status?e[a.imgOrder].status_text="pending":a.isActive?e[a.imgOrder].status_text="active":e[a.imgOrder].status_text="approved",0==a.isActive||1==a.isActive&&0==a.status?e[a.imgOrder].can_upload=!0:e[a.imgOrder].can_upload=!1,0==a.isActive&&o.in_array(a.status,[0,1])?e[a.imgOrder].can_set_primary=!0:e[a.imgOrder].can_set_primary=!1}),e},avatar_modal_events:function(t){var s=a(t.$el);s.find(".ps_js-avatar_image").on("click",function(){n.store.store_update("avatar_modal",{active_imgOrder:a(this).attr("data-order")})}),s.find("[name=ps_js-avatar_upload]").on("change",function(){o.empty(a(this).val())||(c.avatar_crop_modal(t),o.read_files(a(this),{progress:function(a){0==a.id&&(100===a.percent&&n.store.store_update("crop_modal",{file_base64:a.result}),n.store.store_update("crop_modal",{percent:a.percent,file_name:a.info.name}))},error:function(a){a.id}}))}),s.find(".ps_js-avatar_open_webcam").on("click",function(){c.avatar_open_webcam(t)}),s.find(".ps_js-avatar_set_profile").on("click",function(){r.avatar_set_primary(t.selected)}),s.find(".ps_js-avatar_modal_close").on("click",function(){e.modal.close("avatar","avatar")})},avatar_crop_modal:function(r,o){var s=c.crop_store_reset();n.store.store_update("crop_modal",{imgOrder:r.selected,is_camera:o||!1}),e.modal.open("avatar_crop",{modal_class:"avatar_crop_root",body:function(a){t.render(a,"avatar_crop",{replace:!1,data:s,computed:{final_percent:function(){return this.is_rendered?this.percent:0}},mounted:function(){var a=this;c.crop_modal_events(a,r)}})},bind:{hide:function(){a("[name=ps_js-avatar_upload]").each(function(){a(this).val("")}),a(this).find(".ps_js-image_crop").trigger("image_remove_crop")},shown:function(){setTimeout(function(){n.store.store_update("crop_modal",{is_rendered:!0})},100)}}},n.avatar_secondary_action)},crop_store_reset:function(){return n.store.store_update("crop_modal",{percent:0,file_name:"",file_base64:"",is_rendered:!1,imgOrder:0,uploading:0,is_camera:!1}),n.store.store_fetch("crop_modal")},crop_modal_events:function(t,s){var i=a(t.$el);i.on("image_error_crop",".ps_js-image_cropper",function(a,t){e.modal.close("avatar_crop",n.avatar_secondary_action)}),i.on("click",".ps_js-crop_cancel",function(){e.modal.close("avatar_crop",n.avatar_secondary_action)}),i.on("click",".ps_js-crop_retake",function(){c.avatar_open_webcam()}),i.on("click",".ps_js-crop_rotate",function(){i.find(".ps_js-image_cropper").trigger("image_rotate")}),o.ready(".ps_js-avatar_crop_form",function(){var a=i.find(".ps_js-avatar_crop_form");_.apply(a,{validations:{".ps_js-imgOrder":{validate:[{as:"imgOrder"}]},".ps_js-image_save_base64":{validate:[{as:"required"}]}},success:function(){r.avatar_upload(t.imgOrder,a.find(".ps_js-image_save_base64").val(),{progress:function(a){n.store.store_update("crop_modal",{uploading:a})},complete:function(){e.modal.close("avatar_crop",n.avatar_secondary_action)}})}})},i)},avatar_open_webcam:function(a){var r=c.webcam_store_reset();e.modal.exists("avatar_webcam",n.avatar_secondary_action)&&n.store.store_update("webcam_modal",{is_active:!0}),e.modal.open("avatar_webcam",{modal_class:"avatar_webcam_root",body:function(e){t.render(e,"avatar_webcam",{replace:!1,data:r,mounted:function(){c.webcam_modal_events(this,a),n.store.store_update("webcam_modal",{is_active:!0})}})},bind:{hide:function(){n.store.store_update("webcam_modal",{is_active:!1})}}},n.avatar_secondary_action)},webcam_store_reset:function(){return n.store.store_update("webcam_modal",{is_active:!1,has_captured:!1}),n.store.store_fetch("webcam_modal")},webcam_modal_events:function(t,r){a(t.$el).find(".ps_js-media_webcam").on("media_webcam_error",function(){e.modal.close("avatar_webcam",n.avatar_secondary_action)}),a(t.$el).find(".ps_js-avatar_capture").on("click",function(){var e=a(t.$el).closest(".ps_js-modal_avatar_webcam"),o=e.find(".ps_js-media_webcam");o.length>0?(o.trigger("media_webcam_capture",function(a){c.avatar_crop_modal(r,!0),n.store.store_update("crop_modal",{percent:100,file_base64:a,file_name:"video"})}),n.store.store_update("webcam_modal",{has_captured:!0})):c.debug("Failed to capture, webcam is not yet ready")}),a(t.$el).find(".ps_js-avatar_webcam_cancel").on("click",function(){e.modal.close("avatar_webcam",n.avatar_secondary_action)})}};return{open_avatar_modal:c.open_avatar_modal,avatar_primary_edit:function(){return{mounted:function(){a(this.$el).find(".ps_js-avatar_edit").on("click",c.open_avatar_modal)}}},avatar_selector:function(){return{props:["active"],watch:{active:function(a){n.store.store_update("avatar_modal",{active_imgOrder:a})}}}}}});