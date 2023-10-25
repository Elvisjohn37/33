
/**
 * Accept Terms and Condition page
 * 
 * @author PS Team
 */
define('ps_accept_terms', ['ps_view', 'ps_model', 'ps_popup', 'ps_language'], function() {

    'use strict';

    var ps_view     = arguments[0];
    var ps_model    = arguments[1];
    var ps_popup    = arguments[2];
    var ps_language = arguments[3];

    var globals   = { is_page_renderend: false };
    var callables = {
        /**
         * This will accept terms and condition
         * @param  function callback
         * @return void
         */
        accept_terms_condition: function(callback) {
            ps_popup.toast.open(ps_language.get('messages.loading_message'), {
                title: ps_language.get('messages.accept_tac'),
                id   : 'accept_terms',
                type : 'schedule'
            });

            ps_model.accept_terms_conditions({
                success: function() {
                            if ($.isFunction(callback)) {
                                callback(true);
                            }

                            window.location = '';
                        },
                fail    : function() {
                            if ($.isFunction(callback)) {
                                callback(false);
                            }
                        },
                error   : function() {
                            if ($.isFunction(callback)) {
                                callback(false);
                            }
                        },
                complete: function() {
                            ps_popup.toast.close('accept_terms');
                        }
            });
        }
    };

    return {
        /**
         * Activate page
         * @param  string hash    
         * @param  object hash_info 
         * @return void
         */
        activate: function(hash, hash_info) {
            if (!globals.is_page_rendered) {
                globals.is_page_rendered = true;
                ps_view.render($('.ps_js-page_'+hash_info.page), 'accept_terms_page', {
                    replace: false, 
                    data   : { hash_info: hash_info, is_accepting: false },
                    mounted: function() {
                                var vm = this;
                                ps_model.update_page_rendered(vm.hash_info.page);
                                $(vm.$el).on('click', '.ps_js-accept_terms_button', function() {
                                    vm.is_accepting = true;
                                    callables.accept_terms_condition(function(is_success) {
                                        vm.is_accepting = false;
                                    });
                                });
                            }
                });
            }
        },

        /**
         * Accept terms content custon tag
         * @return object
         */
        accept_terms_content: function() {
            return {
                data   : function() {
                            return { is_loading: true, content:'', content_loaded: false };
                        },
                mounted: function() {
                            var vm = this;
                            ps_model.terms_and_conditions({
                                success: function(response) {
                                            vm.content    = response;
                                            vm.is_loading = false;
                                            vm.$nextTick(function() {
                                                vm.content_loaded = true;
                                            });
                                        }
                            });
                        } 
            };
        }
    };
});
