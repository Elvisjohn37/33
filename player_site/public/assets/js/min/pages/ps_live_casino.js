define("ps_live_casino",["ps_window","ps_model","ps_popup","ps_language","ps_view","ps_store","ps_helper"],function(){"use strict";var e,n=arguments[0],i=arguments[1],s=arguments[2],o=arguments[3],t=arguments[4],a=arguments[5],c=arguments[6],_={is_page_rendered:!1,store:new a("ps_live_casino"),is_page_init:!1};return e={page_info:function(){return _.store.store_exists("info")||_.store.store_update("info",{is_loading:!0}),_.store.store_fetch("info")},on_game_closed:function(){i.view_data({success:function(e){i.reset_websession(e.live_casino.gameID)}},["live_casino"])},events:function(n){$(function(){$(n.$el).find(".ps_js-play_button").off("click").on("click",function(){e.open_casino(n.hash_info,$(this).attr("index-item"),$(this).attr("index-version"),n)})})},open_casino:function(t,a,p,r){n.new_instance(function(n){n.on("close",function(){_.game_opened&&e.on_game_closed()}),n.open("","width="+screen.width+",height="+screen.height),s.toast.open(o.get("language."+t.id),{title:o.get("messages.opening_game"),type:"schedule",id:t.productID}),_.game_opened=!1,i.view_data({success:function(o){i.play(o.live_casino.gameID,t.productID,{success:function(i){n.is_open()||e.on_game_closed();var s=i.URL;r.live_casinos[a].engines[p].params.forEach(function(e){s=c.add_url_param(s,e)}),n.redirect(s),_.game_opened=!0},fail:function(e){n.close()},complete:function(){s.toast.close(t.productID)}})},fail:function(){n.close(),s.toast.close(t.productID)}},["live_casino"])})}},{activate:function(n,s){var o=e.page_info();_.is_page_rendered||(_.is_page_rendered=!0,t.render($(".ps_js-page_"+s.page),"live_casino",{replace:!1,data:{hash_info:s,page_info:o,live_casinos:[{description:"Live Casino",engines:[{version:"html5",params:["isHtml5=Y"]},{version:"flash",params:["isHtml5=N"]}]}]},mounted:function(){var n=this;e.events(n),i.update_page_rendered(n.hash_info.page),_.store.store_update("info",{is_loading:!1})}}))}}});