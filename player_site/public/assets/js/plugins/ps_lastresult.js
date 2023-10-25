
/**
 * This will handle ps lastresult plugin
 * 
 * @author PS Team
 */
define('ps_lastresult', ['ps_model', 'ps_helper'], function () {

    var ps_model  = arguments[0];
    var ps_helper = arguments[1];
    var globals   = {};
    var callables = {};

    return {
        /**
         * Lastresult main tag
         * @return void
         */
        lastresult_main: function() {
            return {
                data    : function() {
                            return {
                                lastresult : { list:[] },
                                is_loading : true
                            };
                        },
                computed: {
                            show    : function() {
                                        return (this.is_loading == false && this.lastresult.list.length > 0);
                                    },
                            no_item : function() {
                                        return (this.is_loading == false && this.lastresult.list.length <= 0);
                                    }
                        },
                mounted : function() {
                            var vm = this;

                            ps_model.plugin({
                                success: function(response) { 
                                            vm.lastresult = response;
                                            vm.is_loading = false;
                                        }
                            }, 'lastresult');
                        }
            };
        }
    };
});
