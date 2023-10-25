
/**
 * This will handle ps news plugin
 * 
 * @author PS Team
 */
define('ps_news', ['ps_model','ps_helper', 'ps_popup','jquery', 'ps_view'], function () {

    var ps_model  = arguments[0];
    var ps_helper = arguments[1];
    var ps_popup  = arguments[2];
    var $         = arguments[3];
    var ps_view   = arguments[4];

    var globals   = {};

    var callables = {
        /**
         * Handler when user click a news item
         * @return void
         */
        news_click: function() {
            var news_item       = $(this).closest('.ps_js-news_item');
            var news_item_info  = news_item.data();

            // make it start on 1 temporarily instead of 0
            var current_chunk   = news_item_info.chunk + 1;
            var current_index   = news_item_info.news  + 1;

            var prev_chunk      = (current_chunk - 1);
            var prev_chunk_last = (prev_chunk * (news_item_info.rows));

            // substract 1 to revert it to original start from 0
            var news_overall_index = (prev_chunk_last + current_index) - 1; 
            var news_content       = ps_model.get_news(news_overall_index);

            ps_popup.modal.open('news' + news_overall_index, {
                header      : function(modal_part) {

                                ps_view.render(modal_part, 'news_title', {
                                    replace : false,
                                    data    : news_content
                                });

                            },
                body        : function(modal_part) {

                                ps_view.render(modal_part, 'news_body', {
                                    replace : false,
                                    data    : news_content
                                });

                            },
                modal_class : 'news'
            });
        }
    };

    return {
        /**
         * News main custom tag
         * @return void
         */
        news_main: function() {
            return {
                data    : function() {
                            return {
                                news         : { list:[] },
                                is_loading   : true,
                                chunk_length : 0
                            };
                        },
                props   : ['rows'],
                computed: {
                            final_rows: function() {
                                            return this.rows || 5;
                                        },
                            chunks : function() {
                                        var vm     = this;
                                        var chunks = [];
                                        var length = vm.news.list.length;

                                        for (var item_number = 0; item_number<length; item_number += vm.final_rows) {
                                            chunks.push(vm.news.list.slice(item_number, item_number + vm.final_rows));
                                        }

                                        return chunks;
                                    },
                            show    : function() {
                                        return (this.is_loading == false && this.news.list.length > 0);
                                    },
                            no_item : function() {
                                        return (this.is_loading == false && this.news.list.length <= 0);
                                    },
                                    
                            chunks_length: function() {
                                            return this.chunks.length;
                                        }
                        },
                mounted : function() {
                            var vm = this;

                            ps_model.plugin({
                                success: function(response) { 
                                            vm.news       = response;
                                            vm.is_loading = false; 
                                        }
                            }, 'news');
                        },
                updated : function() {
                            // bind events
                            $(this.$el).find('.ps_js-read_more')
                                       .off('click', callables.news_click)
                                       .on( 'click', callables.news_click);
                        }
            };
        }
    }
});
