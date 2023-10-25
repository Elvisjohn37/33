define("ps_sports",["ps_view","ps_model","ps_store","ps_window","ps_popup","ps_language"],function(){"use strict";var e,s=(arguments[0],arguments[1]),o=arguments[2],n=arguments[3],t=arguments[4],i=arguments[5],a={store:new o("ps_sports"),game_opened:!1,hash_info:[]};return n.new_instance(function(o){o.on("close",function(){a.game_opened&&e.on_game_closed()}),e={open_sports:function(o,n){"sports"!==n&&s.view_data({success:function(s){s.user.is_auth?e.sports_after_login(s,o,n):e.sports_before_login(s,o,s[n])}},["user",n])},sports_after_login:function(n,i,_){a.store.store_update("info",{is_loading:!0}),s.play(n[_].gameID,i,{success:function(s){o.is_open()||e.on_game_closed(),o.redirect(s.URL),a.game_opened=!0},fail:function(e){o.close()},complete:function(){t.toast.close(i)}})},sports_before_login:function(e,s,n){var i=o.redirect(n.bsi_src);a.game_opened=!0,i&&t.toast.close(s)},activate:function(s,n){o.open("","width="+screen.width+",height="+screen.height),t.toast.open(i.get("language."+n.id),{title:i.get("messages.opening_game"),type:"schedule",id:n.productID}),a.game_opened=!1,a.hash_info=n,e.open_sports(n.productID,n.id)},on_game_closed:function(){s.view_data({success:function(e){e.user.is_auth&&s.reset_websession(e[a.hash_info.id].gameID)}},[a.hash_info.id,"user"])}}}),{activate:e.activate}});