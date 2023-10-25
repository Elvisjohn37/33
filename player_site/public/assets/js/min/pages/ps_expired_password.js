define("ps_expired_password",["ps_view","ps_model","ps_validator","ps_store"],function(){"use strict";var e=arguments[0],s=arguments[1],r=arguments[2],t=arguments[3],i={is_page_renderend:!1,form_id:0,store:new t("ps_expired_password")},a={page_info:function(){return i.store.store_exists("info")||i.store.store_update("info",{loading:!1,request_num:0}),i.store.store_fetch("info")},view_data:function(e){s.view_data({success:e},["user"])},validations:function(e,s){var r=e.attr("id"),t="#"+r+" .ps_js-password_field";return{".ps_js-password_field":{triggers:"blur focus",prevent:!0,validate:[{as:"required",exclude_triggers:["focus","prevent"]},{as:"not_contain",type:"password",values:[s.user.firstName,s.user.lastName,s.user.loginName],exclude_triggers:["focus","prevent"]},{as:"max_length",type:"password",exclude_triggers:["focus"]},{as:"alpha_num_symbol",type:"password",exclude_triggers:["focus","prevent"]}]},".ps_js-confirm_new_password":{triggers:"blur focus",prevent:!0,validate:[{as:"prerequisite",fields:[t],is_focus:!0,exclude_triggers:["prevent"]},{as:"required",exclude_triggers:["focus","prevent"]},{as:"same",type:"password",field:t,exclude_triggers:["focus","prevent"]},{as:"max_length",type:"password",exclude_triggers:["focus"]}]}}},form_submit:function(e){var r=i.store.store_fetch("info");if(!r.loading){var t=r.request_num+1;i.store.store_update("info",{loading:!0,request_num:t}),s.expired_password(ps_helper.json_serialize(e),{ps_validator_form:e,success:function(){t==r.request_num&&(window.location="")},complete:function(){t==r.request_num&&i.store.store_update("info",{loading:!1})}})}}};return{activate:function(t,o){var n=a.page_info();if(!i.is_page_rendered){i.is_page_rendered=!0;var _=i.form_id;i.form_id++,e.render($(".ps_js-page_"+o.page),"expired_password",{replace:!1,data:{hash_info:o,page_info:n,view_data:{},form_id:_},mounted:function(){var e=this;a.view_data(function(t){e.view_data=t,s.update_page_rendered(e.hash_info.page),e.$nextTick(function(){var s=$(e.$el).find(".ps_js-expired_password_form");r.apply(s,{validations:a.validations(s,e.view_data),success:function(){a.form_submit(s)}}),s.find(".ps_js-expired_password_reset").on("click",function(){s.trigger("view_components_fullreset")})})})}})}}}});