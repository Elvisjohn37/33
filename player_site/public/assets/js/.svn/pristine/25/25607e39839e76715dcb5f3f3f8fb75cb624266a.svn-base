
/**
 * Game window page
 * 
 * @author PS Team
 */
define('ps_error_page', ['ps_view','ps_model','ps_language','ps_helper','jquery'], function() {
    var ps_view       = arguments[0];
    var ps_model      = arguments[1];
    var ps_language   = arguments[2];
    var ps_helper     = arguments[3];


    var globals   = { is_page_rendered: false };
    var callables = {};

    return {
    	activate: function(hash, hash_info) {
            var page = $('.ps_js-page_'+hash_info.page);
            if (globals.is_page_rendered == false) {
                globals.is_page_rendered = true;

                ps_model.view_data({
                    success: function(view_data) {
                                ps_view.render(page, 'error_page_view', {
                                    replace : false,
                                    data    : { 
                                                hash_info     : hash_info, 
                                                last_game_open: null,
                                                view_data     : view_data
                                            },
                                    computed: {
                                                err_code: function() {
                                                            var view_data = this.view_data;

                                                            if (!ps_helper.empty(view_data.error_page.err_code)) {
                                                                return view_data.error_page.err_code;
                                                            } else {
                                                                return ps_language.unknown_err_code;
                                                            }
                                                        }
                                            },
                                    mounted : function() {
                                                var vm = this;
                                                ps_model.update_page_rendered(vm.hash_info.page);
                                            }
                                });
                            }

                }, ['error_page']);
            }
    	},


        /**
         * error page full custom tag
         * @return object
         */
        error_page_full: function() {
            return {
                props: ['code']
            };
        }
    };
});