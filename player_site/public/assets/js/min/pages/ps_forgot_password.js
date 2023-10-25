define("ps_forgot_password",["ps_view","ps_model","ps_popup","ps_language","ps_store","ps_helper","jquery","ps_validator"],function(){var s=arguments[0],e=arguments[1],o=arguments[2],r=arguments[3],t=arguments[4],i=arguments[5],a=arguments[6],n=arguments[7],_={store:new t("ps_forgot_password"),form_id:0},d={forgot_password_reset:function(){a(".ps_js-forgot_password_form").trigger("view_components_fullreset"),_.store.store_exists("info")?_.store.store_update("info",{request_num:_.store.store_fetch("info").request_num+1}):_.store.store_update("info",{request_num:0}),_.store.store_update("info",{step:1,securityQuestion:"",loading:!1}),a(".ps_js-forgot_password_form .ps_js-forgot_password_first").trigger("focus")},forgot_password_info:function(){return _.store.store_exists("info")||d.forgot_password_reset(),_.store.store_fetch("info")},forgot_password_next:function(s){var o=_.store.store_fetch("info");if(!o.loading){var r=o.request_num+1;_.store.store_update("info",{loading:!0,request_num:r}),e.get_securityQuestion(i.json_serialize(s.find(":visible")),{ps_validator_form:s,success:function(s){r==o.request_num&&(_.store.store_update("info",{step:2,securityQuestion:s.securityQuestion}),i.ready(".ps_js-forgot_password_form .ps_js-yourAnswer:visible",function(){a(this).trigger("focus")},a(this)))},fail:function(s,e,t,i){r!=o.request_num&&delete i.ps_validator_form},complete:function(s){r==o.request_num&&_.store.store_update("info","loading",!1)}})}},forgot_password_submit:function(s){var r=_.store.store_fetch("info");if(!r.loading){var t=r.request_num+1;_.store.store_update("info",{loading:!0,request_num:t}),e.forgot_password_submit(i.json_serialize(s),{success:function(){t==r.request_num&&o.modal.close("forgot_password","floating_page")},fail:function(){t==r.request_num&&d.forgot_password_reset()}})}},forgot_password_validation:function(){return{".ps_js-loginName":{triggers:"blur",validate:[{as:"required"}]},".ps_js-email":{triggers:"blur",validate:[{as:"required"},{as:"email"}]},".ps_js-captcha_input":{triggers:"blur",validate:[{as:"required",type:"captcha"}]},".ps_js-yourAnswer":{triggers:"blur",validate:[{as:"required",visible_only:!0}]}}},reset_password_info:function(){if(!_.store.store_exists("reset_password_info")){var s=_.form_id;_.form_id++,_.store.store_update("reset_password_info",{loading:!1,form_id:s,request_num:0})}return _.store.store_fetch("reset_password_info")},reset_password_validation:function(s){var e=s.attr("id"),o="#"+e+" .ps_js-password_field";return{".ps_js-password_field":{triggers:"blur focus",prevent:!0,validate:[{as:"required",exclude_triggers:["focus","prevent"]},{as:"max_length",type:"password",exclude_triggers:["focus"]},{as:"alpha_num_symbol",type:"password",exclude_triggers:["focus","prevent"]}]},".ps_js-confirm_new_password":{triggers:"blur focus",prevent:!0,validate:[{as:"prerequisite",fields:[o],is_focus:!0,exclude_triggers:["prevent"]},{as:"required",exclude_triggers:["focus","prevent"]},{as:"same",type:"password",field:o,exclude_triggers:["focus","prevent"]},{as:"max_length",type:"password",exclude_triggers:["focus"]}]}}},reset_password_submit:function(s,r){var t=d.reset_password_info();if(!t.loading){var a=t.request_num+1;_.store.store_update("reset_password_info",{loading:!0,request_num:a}),e.new_password(s,i.json_serialize(r),{ps_validator_form:r,success:function(){a==t.request_num&&o.modal.close("forgot_password_reset")},complete:function(){a==t.request_num&&_.store.store_update("reset_password_info",{loading:!1})}})}}};return{activate:function(t){var _=d.forgot_password_info();o.modal.open("forgot_password",{modal_class:"forgot_password_root",header:r.get("language.lost_password"),body:function(e){s.render(e,"forgot_password",{replace:!1,data:{info:_},mounted:function(){var s=this,e=a(s.$el).find(".ps_js-forgot_password_form");n.apply(e,{validations:d.forgot_password_validation(),success:function(){1==s.info.step?d.forgot_password_next(e):d.forgot_password_submit(e)}})}})},footer:function(e){s.render(e,"forgot_password_footer",{replace:!1,data:{info:_},mounted:function(){a(this.$el).find(".ps_js-forgot_password_submit").on("click",function(){a(".ps_js-forgot_password_form").trigger("submit")})}})},bind:{hide:function(){window.location=e.active_main_hash()},shown:function(){i.ready(".ps_js-forgot_password_form .ps_js-forgot_password_first:visible",function(){a(this).trigger("focus")},a(this))}},onrender:function(){d.forgot_password_reset()}},"floating_page")},deactivate:function(){o.modal.close("forgot_password","floating_page")},activate_reset_password:function(e){var t=d.reset_password_info();o.modal.open("forgot_password_reset",{modal_class:"forgot_password_reset",closable:!1,header:r.get("language.create_new_password"),body:function(o){s.render(o,"reset_password",{replace:!1,data:{info:t},mounted:function(){var s=this,o=a(s.$el).find(".ps_js-reset_password_form");n.apply(o,{validations:d.reset_password_validation(o),success:function(){d.reset_password_submit(e,o)}})}})},footer:function(e){s.render(e,"reset_password_footer",{replace:!1,data:{info:t},mounted:function(){var s=this;a(s.$el).find(".ps_js-reset_password_submit").on("click",function(){a("#"+s.info.form_id).trigger("submit")})}})},bind:{shown:function(){i.ready("#"+t.form_id+" .ps_js-password_field:visible",function(){a(this).trigger("focus")},a(this))}}})}}});