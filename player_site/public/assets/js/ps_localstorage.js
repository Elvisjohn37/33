/**
 * PS localStorage management
 * NOTE: uset localstorage only for enhancing UI, don't rely on it too much
 * 
 * @author PS Team
 */
define('ps_localstorage', ['jquery', 'ps_helper', 'ps_gconfig'], function ($, ps_helper, ps_gconfig) {

    'use strict';

    var globals   = { 
                        key_prefix: 'ps_' + ps_helper.snake_case(ps_gconfig.site_signature), 
                        debug     : true, 
                        signature :  ps_helper.snake_case(ps_gconfig.site_signature)
                    };
    var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * This will get real key for localstorage item
         * @param  string raw_key 
         * @return string
         */
        get_key: function(raw_key) {
            return globals.key_prefix + raw_key;
        },

        /**
         * This will get localstorage item, full details including signatures
         * @param  string key 
         * @return mixed
         */
        get_full: function(key) {
            var real_key = callables.get_key(key);
            var item     = window.localStorage.getItem(real_key);

            if (!ps_helper.empty(item)) {

                try {

                    item = JSON.parse(item);

                } catch(e) {

                    callables.debug('localstorage with key '+key+' could not be parsed, it might be an external data.');

                }

            }

            return item;
        },

        /**
         * This will check if localstorage item is not set by our app
         * @param  string  key void
         * @return boolean
         */
        external: function(key) {
            var value    = callables.get_full(key);
            var real_key = callables.get_key(key);

            if (value !== null||window.localStorage.hasOwnProperty(real_key)||window.localStorage[real_key]!=null) {

                if (!$.isPlainObject(value)) {

                    return true;

                } else if (value.signature != globals.signature) {

                    return true;

                }

            }

            return false;
        },

        /**
         * Set localstorage item
         * @param  string  key 
         * @param  mixed   value
         * @param  boolean is_persistent setting this to true
         *                               will make localstorage value persistent when using remove_all
         * @return void
         */
        set: function(key, value, is_persistent) {
            if (!callables.external(key)) {
                var real_key = callables.get_key(key);
                var content  = JSON.stringify({ value: value, signature: globals.signature, persistent:is_persistent });
                
                try {

                    window.localStorage.setItem(real_key, content);

                } catch (e) {

                    callables.debug('localstorage with key '+key+' was not saved, it maybe too big or invalid data.');

                }

            } else {

                callables.debug('localstorage with key '+key+' was not saved, it is used by external plugins.');

            }
        },

        /**
         * This will delete specific key from localstorage
         * @param  string key
         * @return void
         */
        remove: function(key) {
            if (!callables.external(key)) {
                callables.set(key, null);
                
                var real_key = callables.get_key(key);
                window.localStorage.removeItem(real_key);
                callables.debug('localstorage with key '+key+' removed.');

            } else {

                callables.debug('localstorage with key '+key+' cannot be removed, it is used by external plugins.');

            }
        },

        /**
         * This will get localstorage item, value only
         * @param  string key
         * @return mixed
         */
        get: function(key) {
            var full_value = callables.get_full(key);

            if ($.isPlainObject(full_value) && full_value.signature == globals.signature) {
                return full_value.value;
            } else {
                return full_value;
            }
        }
    };

    return {
        remove: callables.remove,
        get   : callables.get,
        set   : callables.set,

        /**
         * This will listen to specific localstorage key changes then trigger the callback event
         * @param  string   key    
         * @param  string   callback 
         * @return void
         */
        watch: function(key, callback) {
            $(window).bind('storage', function (e) {
                if (e.originalEvent.key === callables.get_key(key)) {
                    callback(callables.get(key));
                }
            });
        },

        /**
         * This will remove all localstorage item of PS only
         * @return void
         */
        remove_all: function() {
            setTimeout(function() {
                for (var item in window.localStorage) {
                    var key   = ps_helper.remove_prefix(globals.key_prefix, item);
                    var value = callables.get_full(key);

                    if (value != null && $.isPlainObject(value) && !value.persistent) {
                        callables.remove(key);
                    }
                }
            },0);
        }
    };
});