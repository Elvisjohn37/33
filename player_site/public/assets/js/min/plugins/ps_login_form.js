define("ps_login_form",["ps_model","ps_view","ps_helper","ps_popup","ps_language","ps_store","ps_validator","jquery","ps_localstorage","ps_window"],function(){var e=arguments[0],o=arguments[1],n=arguments[2],t=arguments[3],i=arguments[4],a=arguments[5],s=arguments[6],r=arguments[7],c=arguments[8],_=arguments[9],l={store:new a("ps_login_form")},p={login_form_submit:function(o,a){l.store.store_fetch("login_form").loading||(l.store.store_update("login_form",{loading:!0}),t.toast.open(i.get("messages.authenticating"),{title:i.get("language.login"),type:"lock"}),e.login(n.json_serialize(o),_.id,{success:function(e){a?c.set("login_form_remember",{username:o.find(".ps_js-username").val(),password:o.find(".ps_js-password").val()},!0):c.remove("login_form_remember"),window.location=""},fail:function(e){e.has_captcha?p.login_captcha():e.resend_email&&p.resend_email_modal(e),p.login_form_reset()}}))},login_form_info:function(){return l.store.store_exists("login_form")||p.login_form_reset(),l.store.store_fetch("login_form")},login_form_reset:function(){r(".ps_js-login_form").trigger("reset"),l.store.store_update("login_form",{loading:!1,remember:c.get("login_form_remember")})},resend_email_modal:function(n){t.toast.close();var a=i.error(n.err_details.err_code);t.modal.open("resend_email",{modal_class:"resend_email_root",header:a.title,body:a.content,footer:function(n){o.render(n,"resend_email_footer",{replace:!1,mounted:function(){r(this.$el).find("[name=ps_js-resend_email]").on("click",function(){t.modal.close("resend_email");var o=i.get("messages.sending_email");t.toast.open(o,{title:a.title,type:"mail"}),e.resend_verification_email()})}})}})},login_captcha_validation:function(){return{".ps_js-captcha_input":{validate:[{as:"required",type:"captcha"}]}}},login_captcha:function(){t.modal.open("login_captcha",{header:i.get("language.warning"),body:function(e){o.render(e,"login_captcha",{replace:!1,data:{info:p.login_captcha_info()},mounted:function(){var e=this,o=r(e.$el).find(".ps_js-login_captcha_form");s.apply(o,{success:function(){p.login_captcha_submit(o)},validations:p.login_captcha_validation()})}})},footer:function(e){o.render(e,"login_captcha_footer",{replace:!1,data:{info:p.login_captcha_info()},mounted:function(){r(this.$el).find("[name=ps_js-login_captcha_submit]").on("click",function(){r(".ps_js-login_captcha_form").submit()})}})},modal_class:"login_captcha_root",bind:{hide:function(){t.toast.close()}},onrender:function(){n.ready(".ps_js-login_captcha_root .ps_js-captcha_input",function(){r(this).filter(":visible").trigger("focus")})},closable:!1})},login_captcha_submit:function(o){l.store.store_fetch("login_captcha").loading||(l.store.store_update("login_captcha",{loading:!0}),e.login_captcha_submit(n.json_serialize(o),{success:function(){t.modal.close("login_captcha")},complete:function(){p.login_captcha_reset()}}))},login_captcha_info:function(){return l.store.store_exists("login_captcha")||p.login_captcha_reset(),l.store.store_fetch("login_captcha")},login_captcha_reset:function(){r(".ps_js-login_captcha_form").trigger("view_components_fullreset"),l.store.store_update("login_captcha",{loading:!1}),r(".ps_js-login_captcha_form input:visible").first().trigger("focus")}};return{login_form_main:function(){return{data:{info:p.login_form_info()},computed:{remember:function(){var e=this;return r.isPlainObject(e.info.remember)?{enabled:!0,credentials:e.info.remember}:{enabled:!1,credentials:{username:"",password:""}}}},props:["rememberCheckbox"],mounted:function(){var e=this;n.ready(".ps_js-login_form",function(){var o=r(e.$el).find(".ps_js-login_form");r(e.$el).find("input").on("focus",function(){t.toast.close()}),setTimeout(function(){r("input").first().focus()},1);var n=r(e.$el).find(".ps_js-remember_me_checkbox .ps_js-checkbox");s.apply(o,{success:function(){p.login_form_submit(o,n.is(":checked"))},terminate_on_error:!0,validations:{".ps_js-username":{validate:[{as:"required",type:"login"}]},".ps_js-password":{validate:[{as:"required",type:"login"}]}}})},r(e.$el))}}}}});