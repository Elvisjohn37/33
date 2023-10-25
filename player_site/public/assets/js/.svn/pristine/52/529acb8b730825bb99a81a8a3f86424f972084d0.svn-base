
/**
 * PS google analytics handler
 * 
 * @author PS Team
 */
define('ps_google_analytics', ['ps_helper', 'ps_model'], function() {

    var ps_helper = arguments[0];
    var ps_model  = arguments[1];

    var globals   = { debug: true };
    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),
    };

    return {
        /**
         * Initialize google analytics for this site
         * @return void
         */
        init: function() {
            ps_model.view_data({
                success: function(view_data) {

                            if (!ps_helper.empty(view_data.google_analytics.id)) {

                                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                                })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
                                ga('create', view_data.google_analytics.id, 'auto');
                                ga('send', 'pageview');

                            }

                        }
            },['google_analytics']);
        },
        init_tagmanager: function() {
            ps_model.view_data({
                success: function(view_data) {

                            if (!ps_helper.empty(view_data.google_tagmanager.id)) {

                                require(['https://www.googletagmanager.com/gtag/js?id='+view_data.google_tagmanager.id]);

                                window.dataLayer = window.dataLayer || [];
                                function gtag(){dataLayer.push(arguments);}
                                gtag('js', new Date());
                                gtag('config', view_data.google_tagmanager.id);

                            }
                        }
            },['google_tagmanager']);
        }
    };
});