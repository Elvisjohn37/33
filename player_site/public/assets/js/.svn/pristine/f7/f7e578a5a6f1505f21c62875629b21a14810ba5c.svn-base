
/**
 * Handler for savvy plugins like announcement
 * 
 * @author PS Team
 */
define('ps_savvy', ['ps_helper', 'ps_model', 'ps_view_components'], function() {

    var ps_helper           = arguments[0];
    var ps_model            = arguments[1];
    var ps_view_components  = arguments[2];

    var globals   = { debug: true, announcement_assets_rendered: false };
    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),
    };

    return {
        savvy_announcement: function() {
            return {
                props  : ['client', 'language', 'game'],
                mounted: function() {
                            ps_model.view_data({
                                success: function(view_data) {
                                            if (!globals.announcement_assets_rendered) {
                                                globals.announcement_assets_rendered = true;
                                                ps_view_components.css(view_data.savvy_announcement.css,{  
                                                    onload : function() {
                                                                /** 
                                                                 * Announcement JS file consists socketio
                                                                 * and directly invoked anonymous function
                                                                 * to run the announcement.
                                                                 * This has conflict with modular pattern
                                                                 * because socketio creates a module only.
                                                                 * on first require we only get socketio
                                                                 * on second require we let the announcement run
                                                                 */
                                                                var no_ext = ps_helper.remove_extension(
                                                                                view_data.savvy_announcement.js
                                                                            );
                                                                require.config({
                                                                    paths: { io_temp: no_ext },
                                                                    shim : { io_temp: { exports: 'io' } }
                                                                });

                                                                require(['io_temp',], function(io_temp) {
                                                                    window.io = io_temp;
                                                                    require.undef('io_temp');
                                                                    require(['io_temp']);
                                                                }); 
                                                            }
                                                });
                                            }
                                        }
                            },['savvy_announcement']);
                        }
            };
        }
    };
});