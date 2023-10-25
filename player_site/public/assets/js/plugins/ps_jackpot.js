
/**
 * This will handle ps jackpot plugin
 * 
 * @author PS Team
 */
define('ps_jackpot', ['ps_model', 'ps_helper'], function () {

    var ps_model  = arguments[0];
    var ps_helper = arguments[1];
    var globals   = {};
    var callables = {};

    return {
        /**
         * Jackpot main custom tag
         * @return void
         */
        jackpot_main: function() {
            return {
                data    : function() {
                            return {
                                jackpot      : { list:[] },
                                is_loading   : true,
                            };
                        },
                computed: {
                            show    : function() {
                                        return (this.is_loading == false && this.jackpot.list.length > 0);
                                    },
                            no_item : function() {
                                        return (this.is_loading == false && this.jackpot.list.length <= 0);
                                    }
                        },
                mounted : function() {
                            var vm = this;
                            ps_model.plugin({
                                success: function(response) { 
                                            vm.jackpot    = response;
                                            vm.is_loading = false;
                                        }
                            }, 'jackpot');
                        }
            };
        }
    }
});
