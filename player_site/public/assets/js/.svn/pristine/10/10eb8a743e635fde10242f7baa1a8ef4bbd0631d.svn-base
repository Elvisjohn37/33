
/**
 * This will handle ps products plugin
 * 
 * @author PS Team
 */
define('ps_products', ['ps_model','ps_view', 'ps_helper'], function () {

    var ps_model        = arguments[0];
    var ps_view         = arguments[1];
    var ps_helper       = arguments[2];

    var globals   = {};
    var callables = {};

    return {
        /**
         * Produtcs main custom tag
         * @return void
         */
        products_main: function() {
            return {
                data    : function() {
                            return {
                                menu      : {},
                                is_loading: true,
                            };
                        },
                computed: {
                            featured: function() {
                                        var vm       = this;
                                        var featured = [];


                                        for (var menu_type in vm.menu) {
                                            vm.menu[menu_type].forEach(function(menu_item) {
                                                if (menu_item.hasOwnProperty('featured') == true) {
                                                    featured.push(menu_item);
                                                }
                                            });
                                        }

                                        return featured;
                                    },
                            show    : function() {
                                        return (this.is_loading == false && this.featured.length > 0);
                                    },
                            no_item : function() {
                                        return (this.is_loading == false && this.featured.length <= 0);
                                    },
                                    
                            featured_length: function() {
                                                return  this.featured.length;
                                            }
                        },
                mounted : function() {
                            var vm = this;

                            ps_model.view_data({
                                success: function (view_data) {
                                            vm.menu       = view_data.navigation.menu || {};
                                            vm.is_loading = false;
                                        }
                            }, ['navigation']);
                        }
            };
        }
    }
});
