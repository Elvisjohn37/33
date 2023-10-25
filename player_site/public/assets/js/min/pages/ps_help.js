define("ps_help",["jquery","ps_view","ps_model","ps_popup","ps_language","ps_store","ps_helper"],function(){var e=arguments[0],a=arguments[1],i=arguments[2],t=arguments[3],n=arguments[4],o=arguments[5],s=arguments[6],_={store:new o("ps_help")},d={help_page_info:function(){return _.store.store_exists("info")||_.store.store_update("info",{open_sidebar:null,previous_sidebar:null}),_.store.store_fetch("info")},page_init:function(){var e=this;i.view_data({success:function(a){e.view_data=a,i.update_page_rendered(e.hash_info.page),e.is_loading=!1}},["navigation","route"])},sidebar_init:function(a,i){var n=i.view_data.navigation.hashes[a];if(n.parents.length>0)var o=n.parents[0].id;else var o=n.id;t.modal.close("all","help",function(){e.isPlainObject(d[o])&&e.isFunction(d[o].activate)&&(a=s.replace_all(a,"#",""),d[o].activate(a,n,i))})},faq:{activate:function(n,o,s){t.modal.open(n,{modal_class:"faq_root",sticky_header:!0,header:function(i){a.render(i,"faq_header",{replace:!1,data:{modal_info:d.faq.modal_info(n),text:o.text},mounted:function(){var a=this;e(a.$el).closest(".ps_js-faq_root");e(a.$el).find(".ps_js-faq_toggle_answers").on("click",function(){var e=a.modal_info.is_expand_all;_.store.store_update(n,{is_expand_all:!e})})}})},body:function(t){a.render(t,"faq_body",{replace:!1,data:{modal_info:d.faq.modal_info(n),is_loading:!0,faq:{list:[]}},computed:{is_expand_all:function(){return this.modal_info.is_expand_all}},watch:{is_expand_all:function(a){var i=this;a?e(i.$el).find(".ps_js-collapsible").trigger("view_components_collapse"):e(i.$el).find(".ps_js-collapsible").trigger("view_components_hide")}},mounted:function(){var e=this;i.get_faqs(o.productID,{success:function(a){e.faq=a},complete:function(){e.is_loading=!1,e.$nextTick(d.faq.events)}})}})},bind:{hide:function(){window.location.hash===o.hash&&(window.location=o.menu_hash),_.store.store_update(n,{is_expand_all:!1}),e(".ps_js-help_faq_body .ps_js-collapsible").trigger("view_components_hide")}}},"help")},modal_info:function(e){return _.store.store_exists(e)||_.store.store_update(e,{is_expand_all:!1}),_.store.store_fetch(e)},events:function(){var a=this,i=e(a.$el).find(".ps_js-collapsible");i.on("view_components_change",function(t,n){if(t.stopPropagation(),n&&0==a.modal_info.is_expand_all){var o=this;i.each(function(){s.in_dom_scope(o,this)||e(this).trigger("view_components_hide")})}}),a.modal_info.is_expand_all&&i.trigger("view_components_collapse")}},gaming_rules:{activate:function(n,o,s){t.modal.open(n,{modal_class:"gaming_rules_root",closable:"ingame"!=s.view_data.route.view_type,header:function(e){a.render(e,"gaming_rules_header",{replace:!1,data:{text:o.text}})},body:function(t){a.render(t,"gaming_rules_body",{replace:!1,data:{is_loading:!0,gaming_rules:{description:"",items:[],title:""}},mounted:function(){var a=this;if("ingame"==s.view_data.route.view_type){var t=e(a.$el).attr("data-ingame-width"),n=e(a.$el).attr("data-ingame-height");window.resizeTo(t,n)}i.get_gaming_rules(o.productID,{success:function(e){a.gaming_rules=e},complete:function(){a.is_loading=!1,a.$nextTick(function(){d.gaming_rules.events(a,s)})}})}})},bind:{hide:function(){window.location.hash===o.hash&&(window.location=o.menu_hash)}}},"help")},events:function(i,t){a.render(e(i.$el).find(".ps_js-game_guide_menu"),"game_guide_tree",{data:{hash_info:t.hash_info,view_data:t.view_data},computed:{game_guide:function(){return this.view_data.navigation.sidebars[this.hash_info.productID].filter(function(e){return"game_guide"==e.id})[0].children}}})}},game_guide:{activate:function(e,i,n){var o=d.game_guide.modal_info(e),s=d.help_page_info();t.modal.open(e,{modal_class:"game_guide_root",closable:"ingame"!=n.view_data.route.view_type,sticky_header:!0,header:function(e){a.render(e,"game_guide_header",{replace:!1,data:{text:i.text,modal_info:o,sidebar_hash_info:i,page_info:s},mounted:function(){d.game_guide.load_header.call(this,n)}})},body:function(e){a.render(e,"game_guide_body",{replace:!1,data:{modal_info:o,sidebar_hash_info:i,page_info:s},computed:{is_header_ready:function(){return this.modal_info.is_header_ready},active_page:function(){return this.modal_info.active_page}},watch:{is_header_ready:function(){d.game_guide.load_body.call(this,n)},active_page:function(){d.game_guide.load_body.call(this,n)}},mounted:function(){d.game_guide.load_body.call(this,n)}})},bind:{hide:function(){window.location.hash===i.hash&&(window.location=i.menu_hash)}}},"help"),_.store.store_update(e,{active_page:0})},modal_info:function(e){return _.store.store_exists(e)||_.store.store_update(e,{has_back_button:!1,is_header_ready:!1,header_title:"",page_count:0,header_options:{},active_page:0,page_infos:[],under_construction:!1,category:e}),_.store.store_fetch(e)},load_header:function(a){var t=this,n=t.sidebar_hash_info,o=t.modal_info.category;i.get_game_guide({gpage:0,gname:n.gameName_original,pID:t.sidebar_hash_info.productID},{success:function(a){var i=e("<p></p>").append(a),t=i.find(".ps_gg-select-page").text(),n=i.find(".ps_game_guide_selector");if(n.length>0){var s={};n.find("option").each(function(a){s[a]=e(this).html(),_.store.store_list_push(o,"page_infos",{initiated:!1,loaded:!1,success:null,content:""})}),_.store.store_update(o,{header_title:t,page_count:Object.keys(s).length,header_options:s})}},complete:function(){var a=t.modal_info,i=a.page_count;_.store.store_update(o,{is_header_ready:!0,under_construction:i<=0}),t.$nextTick(function(){e(t.$el).find(".ps_js-game_guide_select").on("change",function(){_.store.store_update(o,{active_page:e(this).val()})})})}})},load_body:function(a){var t=this;if("ingame"==a.view_data.route.view_type){var n=e(t.$el).attr("data-ingame-width"),o=e(t.$el).attr("data-ingame-height");window.resizeTo(n,o)}if(t.is_header_ready){var r=parseInt(t.modal_info.active_page),c=t.modal_info.page_infos[r],l=t.modal_info.category;e.isPlainObject(c)&&0==c.initiated&&(_.store.store_update(l,"page_infos."+r+".initiated",!0),i.get_game_guide({gpage:r+1,gname:t.sidebar_hash_info.gameName_original,pID:t.sidebar_hash_info.productID},{success:function(e){_.store.store_update(l,"page_infos."+r,{loaded:!0,success:!0,content:e}),t.$nextTick(function(){d.game_guide.load_images(t,r)})},error:function(){_.store.store_update(l,"page_infos."+r,{loaded:!0,success:!1})},fail:function(){_.store.store_update(l,"page_infos."+r,{loaded:!0,success:!1})}})),s.animate(s.scrollable_parent(e(t.$el)),{scrollTop:0},"fast")}},load_images:function(i,t){e(i.$el).find(".ps_js-game_guide_"+t+" img").each(function(){a.render(e(this),"game_guide_images",{data:{attributes:s.get_all_attributes(e(this)),is_loading:!0},mounted:function(){var a=this;e(a.$el).find(".ps_js-gg_image_selector").trigger("image_loaded",function(){s.set_attributes(e(this).find(".ps_js-image"),a.attributes),a.is_loading=!1})}})})}},terms_and_conditions:{activate:function(e,o,s){t.modal.open(e,{modal_class:"terms_conditions_root",header:n.get("language.terms_and_conditions"),body:function(e){a.render(e,"terms_conditions_body",{replace:!1,data:{is_loading:!0,content:""},mounted:function(){var e=this;i.terms_and_conditions({success:function(a){e.content=a,e.is_loading=!1}})}})},bind:{hide:function(){window.location.hash===o.hash&&(window.location=o.menu_hash)}}},"help")}},contact_us:{activate:function(e,o,s){t.modal.open(e,{modal_class:"contact_us_root",header:n.get("language.contact_us"),body:function(e){a.render(e,"contact_us_body",{replace:!1,data:{is_loading:!0,content:""},mounted:function(){var e=this;i.contact_us({success:function(a){e.content=a,e.is_loading=!1}})}})},bind:{hide:function(){window.location.hash===o.hash&&(window.location=o.menu_hash)}}},"help")}}};return{activate:function(o,r){var c=d.help_page_info();t.modal.open("help_menu",{modal_class:"help_menu_root",header:n.get("language.help"),body:function(e){a.render(e,"help_menu",{replace:!1,data:{hash_info:r,page_info:c,view_data:{},is_loading:!0},computed:{active_sidebar:function(){var e=this;return e.is_loading?null:e.page_info.open_sidebar}},watch:{active_sidebar:function(){var e=this;null!==e.active_sidebar&&d.sidebar_init(e.active_sidebar,e)}},mounted:d.page_init})},bind:{hide:function(){window.location=i.active_main_hash(),e(".ps_js-help_menu_root .ps_js-collapsible").trigger("view_components_hide"),t.modal.close("all","help")}}},"floating_page");var l=window.location.hash;s.in_array(l,r.sidebars)?_.store.store_update("info",{open_sidebar:l,previous_sidebar:c.open_sidebar}):_.store.store_update("info",{open_sidebar:null,previous_sidebar:c.open_sidebar})},deactivate:function(e){i.is_hash_related(e.hash,window.location.hash)||t.modal.close("help_menu","floating_page")}}});