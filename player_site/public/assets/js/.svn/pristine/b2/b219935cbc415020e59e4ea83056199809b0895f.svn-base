
/**
 * Ingame balance page
 * 
 * @author PS Team
 */
define('ps_ingame_balance', ['ps_view','ps_popup','ps_model','ps_language'], function(ps_view) {
    var ps_view      = arguments[0];
    var ps_popup     = arguments[1];
    var ps_model     = arguments[2];
    var ps_language  = arguments[3];
    var globals      = {};
    var callables    = {};

    return {
		/**
    	 * This will trigger on register page activation
    	 * @param  string hash 
    	 * @return void
    	 */
    	activate: function(hash) {
            ps_model.view_data({
                success: function(view_data) {
                            ps_popup.modal.open('ingame_balance', {
                                modal_class: 'ingame_balance_root',
                                closable   : (view_data.route.view_type!='ingame'),
                                header     : ps_language.get('language.balance'),
                                body       : function(modal_part) {
                                                ps_view.render(modal_part, 'ingame_balance', {
                                                    replace: false,
                                                    mounted: function() {
                                                                var vm     = this;

                                                                if (view_data.route.view_type == 'ingame') {
                                                                    var width  = $(vm.$el).attr('data-ingame-width'); 
                                                                    var height = $(vm.$el).attr('data-ingame-height'); 
                                                                    window.resizeTo(width, height);
                                                                }
                                                            }
                                                });
                                            },
                                bind        : {
                                                hide: function() {
                                                        window.location = ps_model.active_main_hash();
                                                    }
                                            }
                            }, 'floating_page');
                        }
            },['route']);

            // refresh usedbalance
            $('.ps_js-ingame_balance_root .ps_js-usedbalance_root').trigger('view_components_refresh');
    	},

    	/**
    	 * This will trigger on register page deactivation
    	 * @return void
    	 */
    	deactivate: function(hash) {
    		ps_popup.modal.close('ingame_balance', 'floating_page');
    	}
    };
});