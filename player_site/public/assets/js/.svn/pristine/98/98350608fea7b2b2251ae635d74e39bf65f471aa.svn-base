
/**
 * This will handle ps latest_transactions plugin
 * @author PS Team
 */
define('ps_latest_transactions', ['ps_model','ps_helper','jquery'], function (ps_model,ps_helper,$) {

    var globals   = {   
                        contents : {
                                    winners   : ['product2', 'product3'],
                                    deposit   : ['deposit'],
                                    withdrawal: ['withdrawal']
                                }, 
                    };
    var callables = {};

    return {
        /**
         * Latest transaction main custom tag
         * @return void
         */
        latest_transactions_main: function() {
            // Realtime updates
            require(['ps_websocket'], function(ps_websocket) {
                ps_websocket.subscribe('winner',   function(message) {
                    message.displayName = message.display_name;
                    message.amount      = ps_helper.money_format(parseInt(message.amount*1000));
                    ps_model.update_winners(message.product.toLowerCase(), message);
                });

                ps_websocket.subscribe('transfer', function(message) {  
                    message.displayName = message.display_name;
                    message.amount      = ps_helper.money_format(parseInt(message.amount*1000));
                    ps_model.update_transactions(message.type.toLowerCase(), message);
                });
            });

            return {
                data    : function() {
                            return  {
                                transactions: {},
                                is_loading  : true
                            };
                        },
                props   : ['content'],
                computed: {
                            final_content: function() {
                                var vm           = this;
                                var content_keys = vm.content || 'winners,deposit,withdrawal';
                                return content_keys.split(',');
                            }, 

                            // compute contents together with indicators count
                            contents: function() {
                                        var vm            = this;
                                        var content_items = [];

                                        if (vm.transactions.display == true) {

                                            vm.final_content.forEach(function(key) {

                                                if ($.isArray(globals.contents[key])) {

                                                    globals.contents[key].forEach(function(product) {

                                                        if ($.isArray(vm.transactions[product])) {

                                                            content_items.push({
                                                                type            : key,
                                                                product_name_key: product,
                                                                no_item         : (vm.transactions[product].length<=0),
                                                                list            : vm.transactions[product]
                                                            });

                                                        }
                                                        
                                                    });

                                                }

                                            });
                                        }
                                        
                                        return content_items;
                                    }
                        },
                mounted : function() {
                            var vm = this;

                            ps_model.plugin({
                                success: function(response) { 
                                            vm.transactions = response;
                                            vm.is_loading   = false;
                                        }
                            }, 'transactions');
                        }
            };
        }
    };
});





