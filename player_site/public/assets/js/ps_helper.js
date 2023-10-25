/**
 * This will hold all simple PS helper
 * NOTE: Take a look first on other libraries that is being used,
 *       before making own helper function.
 *
 * @author PS Team
 */
define('ps_helper', ['jquery','jquery_ui','jquery_raf'], function($) {

    'use strict';
    
    var callables = {
        /**
         * check if value has no symbol
         * @param  string value 
         * @return bool        
         */
        has_no_symbol: function(value) {
            var replace = value.replace("");
            return /(?=.*[\`~!@#$%^&*=()\/\[\]\-_+{}|:;\'"<>\?\,]).*$/.test(replace) ? true : false ;
        },

        /**
         * this will check if string has no integer value
         * @param  string value 
         * @return bool        
         */
        has_no_number: function(value) {
            return /\d.*$/.test(value) ? true : false ;
        },

        /**
         * This will check if value is empty, almost equivalent to PHP empty
         * types  empty values : null, undefined
         * string empty values: ''
         * array  empty values: length = 0
         * object empty values: No properties
         * @return object
         */
        empty: function(value, test) {

            // undefined and null
            if (typeof value == 'undefined' || value == null) {
                return true;
            }

            // string
            if (typeof value == 'string') {
                return (value == '');
            }

            // array
            if (Array.isArray(value)) {
                return (value.length <= 0);
            }


            // object
            if ($.isPlainObject(value)) {
                for (var prop in value) {
                    if (value.hasOwnProperty(prop)) {
                        return false;
                    }
                }
                return true
            }

            return false;

        },

        /**
         * This is a customized console logging function
         * WARNING: Do not overuse, This will not be disabled even in debug false config
         * @return function
         */
        custom_console: function() {

            var console_func = console.log.bind(console);

            return function(value, type){
                        
                type = callables.set_default(type,'info');
                                        
                function default_log(value, settings) {
                    
                    var style = [ 
                                    'background: ' + settings.background
                                    , 'color: ' + settings.color
                                    , 'text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3)'

                                ].join(';');
                    
                    console_func('%c '+ value + ' ', style);
                }
                
                switch ( type ) {
                                        
                    case 'site_version': 
                        
                        var style = [ 
                                        'background: linear-gradient(#D33106, #571402)'
                                        , 'border: 1px solid #3E0E02'
                                        , 'color: #ffe239'
                                        , 'text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3)'
                                        , 'line-height: 30px'
                                        , 'font-weight: 900'

                                    ].join(';');
                        
                        console_func('%c' + value, style);
                        
                        break;
                        
                    case 'error':
                        
                        default_log(value, {
                            background: '#ac2925',
                            color:      '#fff'
                        });
                        
                        break;
                        
                    case 'warning':
                        
                        default_log(value, {
                            background: '#ec971f',
                            color:      '#fff'
                        });
                        
                        break;
                        
                    case 'success':
                        
                        default_log(value, {
                            background: '#4cae4c',
                            color:      '#fff'
                        });
                        
                        break;
                                         
                    default:
                            
                        default_log(value, {
                            background: '#2e6da4',
                            color:      '#fff'
                        });
                }
            };
        },

        /**
         * This will set default value if original is not acceptable
         * @param  mixed value         
         * @param  mixed default_value 
         * @return mixed
         */
        set_default: function(value, default_value) {

            if (value !== undefined && value !== null) {
                
                if (value.trim !='' ) {
                    
                    return value;
                }
            }
            
            return default_value; 

        },
           
        /**
         * This will check if array 2 is exacly the same as array 1
         * @param  array   array_1         
         * @param  array   array_2 
         * @return boolean
         */                 
        array_same:  function(array_1, array_2) {

            if (array_1.length != array_2.length) { 

                return false;

            }

            for (var i = 0; i < array_2.length; i++) {

                if (array_1[i].compare) { 

                    if (!array_1[i].compare(array_2[i])) { 
                        return false; 
                    }

                } else if (array_1[i] !== array_2[i]) { 

                    return false;

                }
            }

            return true;
        },

        /**
         * This will convert a dom to string representation
         * @param  dom    dom
         * @return string
         */
        dom_stringify: function(dom) {
            var temporary_node = $('<div></div>').append(dom);
            var node_string    = temporary_node[0].innerHTML;
            temporary_node     = null; // IE memory leak
            return node_string;
        },
        
        /**
         * This will merge objects to main object.
         * This takes infinite arguments and the first argument should be the main object.
         * @return object
         */
        assoc_merge: function() {
            return $.extend.apply(this, arguments);
        },

        /**
         * This will search value in array
         * @param  mixed needle  
         * @param  array haystack
         * @return boolean
         */
        in_array: function(needle, haystack) {
            return $.inArray(needle, haystack) > -1;
        },

        /**
         * This will replace all occurence of a string in another string
         * @param  string   string     
         * @param  string   needle    
         * @param  string   replacement 
         * @return string
         */
        replace_all: function (string, needle, replacement) {
            
            if ( callables.empty(string) !== true && callables.empty(needle) !== true) {

                /* covert all first to string */
                string      = string.toString();
                needle      = needle.toString();
                replacement = (! callables.empty(replacement)) ? replacement.toString() : "";

                return string.split(needle).join(replacement);
            }
            
            return string;

        },

        /**
         * This will wait until element is ready in DOM
         * NOTE: please use only when you are sure that element DOM will be present later
         * @param  string    selector
         * @param  function  handler 
         * @param  dom       scope 
         * @param  mixed     params    
         * @return void
         */
        ready: function (selector, handler, scope, params, iteration) {

            if (callables.empty(scope)) {
                var element = $(selector);
            } else {
                var element = scope.find(selector);
            }
            
            if (element.length > 0) {
                handler.call(element, params); 
            } else {
                // set max iteration, discard handler if reached
                iteration = iteration || 1;
                if (iteration <= 60) {
                    iteration++;
                    setTimeout(function () {
                        callables.ready(selector, handler, scope, params, iteration)
                    }, 100);
                } else {
                    console.warn('ps_helper.ready has reached timeout on selector: ' + selector);
                }
            }
        },

        /**
         * This will set element current caret position
         * @param  int position 
         * @param  dom element
         * @return void
         */
        set_caret_position: function(position, element) {
            var caret_position = element[0];

            if (caret_position.createTextRange) {

                var caretMove = caret_position.createTextRange();
                caretMove.move('character', position);
                caretMove.select();

            } else {

                if (caret_position.selectionStart) {
                    caret_position.focus();
                    caret_position.setSelectionRange(position, position);
                } else {
                    caret_position.focus();
                }

            }
        },

        /**
         * This will add padding to left
         * @param  string value 
         * @param  int    length 
         * @param  mixed  padding
         * @return string
         */
        str_pad_left: function(value, length, padding) {
            value              = String(value);
            var cur_length     = value.length;
            var missing_digits = length - cur_length;

            if (missing_digits > 0) {
                for (var i = 0; i<missing_digits; i++) {
                    value = padding + value;
                }
            }

            return value;
        },

        /**
         * This will add padding to right
         * @param  string value 
         * @param  int    length 
         * @param  mixed  padding
         * @return string
         */
        str_pad_right: function(value, length, padding) {
            value              = String(value);
            var cur_length     = value.length;
            var missing_digits = length - cur_length;

            if (missing_digits > 0) {
                for (var i = 0; i<missing_digits; i++) {
                    value = value + padding;
                }
            }

            return value;
        },

        /**
         * Check if a string contains substring
         * @param  string  needle 
         * @param  string  string 
         * @return boolean
         */
        is_contain: function(needle, string) {
            return string.indexOf(needle) > -1;
        },

        /**
         * This will replace all substring with {{@keyword}} wrapper with actual value from object
         * Use this for strings
         * @param  object   object     
         * @param  string   string 
         * @param  array    whitelist
         * @return string
         */
        render_directives: function (object, target, whitelist) {
            if ($.type(object) == 'object') {
                switch (($.type(target))) {
                    case 'string' :
                        var regex            = new RegExp('{{@([^}}]+)}}', 'gm');
                        var formatted_target = target;

                        if ($.isArray(whitelist) && whitelist.length > 0) {
                            var whitelist_regex = callables.contains_first_regex(whitelist);
                        } else {
                            var whitelist_regex = false;
                        }

                        var match;

                        while (match = regex.exec(target)) {

                            // check whitelisted first
                            if (whitelist_regex != false) {

                                var is_whitelisted = false;
                                if (whitelist_regex.test(match[1])) {
                                    var is_whitelisted = true;
                                }

                            } else {

                                var is_whitelisted = true;

                            }

                            if (is_whitelisted) {
                                var value = callables.get_property(object, match[1]);
                                if (!callables.empty(value) && !$.isFunction(value)) {
                                    formatted_target = callables.replace_all(formatted_target, match[0], value);
                                }
                            }

                        }

                        return formatted_target;

                    case 'object': case 'array': 

                        if (!callables.empty(target)) {

                            return JSON.parse(callables.render_directives(
                                object,
                                JSON.stringify(target),
                                whitelist
                            ));

                        } else {

                            return target;

                        }

                    default:
                        console.warn('Second parameter must be either string, object, array');
                        return target;

                }

            } else {

                 console.warn('Second parameter must be either string, object, array');
                return target;

            }
        },

        /**
         * This will get a property of an object based on string index
         * @param  object   object    
         * @param  string   index    
         * @return mixed
         */
        get_property: function (object, index) {
            
            var delimeter = callables.set_default(delimeter,'.');
            return index.split(delimeter).reduce(function (object, index) { 

                if (!$.isPlainObject(object) || $.type(object[index]) === 'undefined') {

                    return null;

                } else {

                    return object[index]; 

                }

            }, object);

        },

        /**
         * Extract youtube video ID from a URL
         * @param  void url
         * @return string/false
         */
        extract_youtube_id: function(url) {
            var regex  = /^.*(youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/;
            var parsed = url.match(regex);

            if (parsed && parsed[2]) {
                return parsed[2];
            } else {
                return false;
            }
        },

        /**
         * This will fix some doms that are not scrollable and will select the right DOM instead
         * @param  dom scrollable 
         * @return dom
         */
        scrollable_mend: function(scrollable) {
            if (scrollable.is($('body'))) {
                return $(window);
            } else if(scrollable.is($(document))) {
                return $(window);
            } else {
                return scrollable;
            }
        },

        /**
         * request animation frame with fallback
         * @return string
         */
        requestAnimationFrame: function() {
            var request_animation = window.requestAnimationFrame ||
                                    window.mozRequestAnimationFrame ||
                                    window.webkitRequestAnimationFrame ||
                                    function (fn) {
                                        window.setTimeout(fn, 15)
                                    };

            return request_animation.apply(window, arguments);
        },

        /**
         * request animation frame with fallback
         * @return void
         */
        cancelAnimationFrame: function() {
            var cancel_animation = window.cancelAnimationFrame ||
                                    window.mozCancelAnimationFrame ||
                                    window.mozCancelRequestAnimationFrame ||
                                    window.webkitCancelAnimationFrame ||
                                    window.webkitCancelRequestAnimationFrame ||
                                    function (fn) {
                                        window.clearTimeout(id)
                                    };

            return cancel_animation.apply(window, arguments);
        },

        /**
         * check if a string contains number
         * @param  string string 
         * @return boolean
         */
        is_number_only: function(string) {
            return /^[0-9]+$/.test(string);
        },

        /**
         * This will return window real width
         * If the elemenis not window this will use simple .width() of jquery
         * @param  dom element   
         * @return int
         */
        viewport_width: function(element) {
            if (element[0] === window) {
                return window.innerWidth || element.width();
            } else {
                element.width();
            }
        },

        /**
         * Event to disable zooming
         * @return void
         */
        disbale_zoom_callback: function(event) {
            // 107 Num Key  +
            // 109 Num Key  -
            // 173 Min Key  hyphen/underscor Hey
            // 61 Plus key  +/=
            var zoom_keys = ['61','107','173','109','187','189'];
            if (event.ctrlKey == true && callables.in_array(event.which, zoom_keys)) {
                event.preventDefault();
            }
        },

        /**
         * Event to disable zooming
         * @return void
         */
        disbale_zoom_keys: function(event) {
            // 107 Num Key  +
            // 109 Num Key  -
            // 173 Min Key  hyphen/underscor Hey
            // 61 Plus key  +/=
            var zoom_keys = [61,107,173,109,187,189];
            if (event.ctrlKey == true && callables.in_array(event.which, zoom_keys)) {
                event.preventDefault();
            }
        },

        /**
         * Event to disable zooming via mousewheel
         * @return void
         */
        disbale_zoom_mousewheel: function(event) {
            console.log('mousewheel');
            if (event.ctrlKey == true) {
                event.preventDefault();
            }
        },

        /**
         * Prevent default to whatever event was triggered
         * @return void
         */
        prevent_default: function(event) {
            event.preventDefault();
            return false;
        },

        /**
         * This will check fot IOS device
         * @returns boolean
         */
        is_ios: function() {
            return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        }
    };

    /**
     |------------------------------------------------------------------------------------------------------------------
     | Exports
     |------------------------------------------------------------------------------------------------------------------
     */
    return {
        console              : callables.custom_console(),
        empty                : callables.empty,
        set_default          : callables.set_default,
        array_same           : callables.array_same,
        dom_stringify        : callables.dom_stringify,
        assoc_merge          : callables.assoc_merge,
        in_array             : callables.in_array,
        replace_all          : callables.replace_all,
        ready                : callables.ready,
        set_caret_position   : callables.set_caret_position,
        is_contain           : callables.is_contain,
        str_pad_left         : callables.str_pad_left,
        render_directives    : callables.render_directives,
        get_property         : callables.get_property,
        extract_youtube_id   : callables.extract_youtube_id,
        scrollable_mend      : callables.scrollable_mend,
        is_number_only       : callables.is_number_only,
        requestAnimationFrame: callables.requestAnimationFrame,
        viewport_width       : callables.viewport_width,
        has_no_symbol        : callables.has_no_symbol,
        has_no_number        : callables.has_no_number,
        is_ios               : callables.is_ios,
        
        /**
         * add a wrapper for console.log function
         * @param  Boolean is_debug_enabled
         * @return function
         */
        debug: function(is_debug_enabled) {
            return function(message) {
                if (is_debug_enabled) {
                    console.warn(message);
                }
            }
        },
                    
        /**
         * This will get cookie by name
         * @param  string name 
         * @return string
         */
        get_cookie: function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length >= 2) return parts.pop().split(";").shift();
        },
        
        /**
         * Get Node element from parsed HTML
         * @param {array} parsed HTML template
         * @param {string} node ID or Class of the element
         * @returns {object}
         */
        get_template: function (template, node) {
            var elem;
            $.each(template, function (index, element) {
                if ($(element).attr('id') == node || $(element).hasClass(node)) {
                    elem = $(element);
                }
            });
            return elem;
        },
        
        /**
         * Check if user agent is mobile 
         * @returns {Boolean}
         */
        detect_mobile:  function(){
            return (/Android|webOS|iPhone|iPad|iPod|Mobile|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
        },
        /**
         * 
         * @param {type} haystack
         * @param {type} needle
         * @returns {Array}
         */
        unset:  function(haystack, needle) {

            return $.grep( haystack, function( val ) {

                return val != needle;

            });

        },
                  
        /**
         * generate unique ID
         * @param  string delimeter default = '', unqid suppose to be using '-' as default delimeter 
         *                          but using hypen causes issue when we assign it as element ID or object ID
         * @return string
         */
        uniqid: function(delimeter) {

            var delimeter = delimeter || '-';

            var _padLeft = function (paddingString, width, replacementChar) {

                            if (paddingString.length >= width) {

                                return paddingString;

                            } else {

                                return _padLeft(replacementChar + paddingString, width, replacementChar || ' ');

                            }

                        };

            var _s4 = function (number) {
                        var hexadecimalResult = number.toString(16);
                        return _padLeft(hexadecimalResult, 4, '0');
                    };

            var _cryptoGuid = function (delimeter) {

                                var buffer = new window.Uint16Array(8);
                                window.crypto.getRandomValues(buffer);

                                return [

                                    _s4(buffer[0]) + _s4(buffer[1]), 
                                    _s4(buffer[2]), _s4(buffer[3]),
                                    _s4(buffer[4]), _s4(buffer[5]) + _s4(buffer[6]) + _s4(buffer[7])

                                ].join(delimeter);
                            };

            var _guid = function (delimeter) {
                            var currentDateMilliseconds = new Date().getTime();
                            var pattern =            'xxxxxxxx'
                                          +delimeter +'xxxx'
                                          +delimeter +'4xxx'
                                          +delimeter +'yxxx'
                                          +delimeter +'xxxxxxxxxxxx';

                            return pattern.replace(
                                /[xy]/g, 
                                function (currentChar) {
                                    var randomChar = (currentDateMilliseconds + Math.random() * 16) % 16 | 0;
                                    currentDateMilliseconds = Math.floor(currentDateMilliseconds / 16);
                                    return (currentChar === 'x' ? randomChar : (randomChar & 0x7 | 0x8))
                                        .toString(16);
                                }
                            );
                        };

            var create = function (delimeter) {

                            if (!callables.empty(window.crypto)) {

                                if (!callables.empty(window.crypto.getRandomValues)) {
                                    return _cryptoGuid(delimeter);
                                }

                            }

                            return _guid(delimeter);
                        };

            return create(delimeter);

        },
        
        /**
         * This will remove characters if its on the beggining of stirng 
         * @param   string prefix
         * @param   string value
         * @returns string
         */
        remove_prefix: function(prefix, value) {

            if (value.substr(0, prefix.length) == prefix) {
                
                return value.slice(prefix.length);
                
            } else {
                
                return value;
            }

        },
                        
        /**
         * This will convert object to array
         * @param   object  value
         * @returns array
         */    
        to_array: function (value) {
            return $.map( value, function (value, index) {
                return [value];
            });
        },
        
        /**
         * This will get percentage of a number from another number
         * @param   int total
         * @param   int count
         * @returns int
         */       
        get_percentage: function(total, count) {
            return Math.floor((count / total) * 100);
        },
        
        /**
         * This will get percentage of a number from another number
         * @param   int total
         * @param   int percent
         * @returns int
         */       
        get_percent_value: function(total, percent) {
            return Math.floor((percent / 100) * total);
        },
        
        /**
         * This will get average of percentage listed in an object
         * If the object has element with value less than 0, then that value will over rule
         * @param  object/array
         * @return int
         */
        get_percent_average: function(list) {

            var sum        = 0;
            var item_count = 0;
            for (var item in list) {

                if (list[item] < 0) {
                    return list[item]
                } else {
                    sum += list[item];
                    item_count++;
                }
            }

            return Math.floor(sum / item_count);
        },
        
        /**
         * This will get average of percentage listed in an object
         * If the object has element with value less than 0, then that value will over rule
         * @param  object/array
         * @return int
         */
        is_percent_loaded: function(percent) {
            return callables.in_array(percent, [100, -1]);
        },

        /**
         * This will generate contains first regex
         * @param  array/string needles 
         * @return string
         */
        contains_first_regex: function( needles ) {
            var regex = $.isArray(needles) ? needles.join('|^') : needles;
            return new RegExp('^'+regex,'m');
        },
         

        /**
         * This will set a property of an object based on string index
         * NOTE: this will auto create the missing keys
         * @param  object   object    
         * @param  string   index    
         * @param  mixed    value
         * @return mixed
         */
        set_property: function (object, property, value) {
            var original_reference = object;
            if (callables.empty(property)) {

                if ($.isPlainObject(value)) {
                    callables.assoc_merge(object, value);
                } else {
                    object = value;
                }

            } else {
                var delimeter      = callables.set_default(delimeter,'.');
                var indexes        = property.split(delimeter);
                var indexes_length = indexes.length - 1;

                for(var index = 0; index < indexes_length; index++) {
                    // auto create missing
                    object[indexes[index]] = object[indexes[index]] ||  {};
                    object                 = object[indexes[index]];

                }

                if ($.isPlainObject(value)) {

                    object[indexes[indexes_length]] = object[indexes[indexes_length]] || {};
                    callables.assoc_merge(object[indexes[indexes_length]], value);

                } else {
                    object[indexes[indexes_length]] = value;
                }

            }
            
            return original_reference;
        },
              
        /**
         * This will check if all required properties of an object exists
         * @param  array    required 
         * @param  object   object  
         * @return boolean
         */
        required_prop: function(required, object) {

            var properties = Object.keys(object);

            if (callables.array_same(required, properties)){

                return true;

            } else {

                var missing = required.filter(function( val ) {
                    
                    return properties.indexOf(val) == -1;

                });

                return false;

            }
        },

        /**
         * This will get a certain node from string DOM
         * @param  string  dom_string 
         * @param  string  selector      
         * @return string
         */
        get_node: function(dom_string, selector, before_stringify) {

            var node_dom = $('<div></div>').append(dom_string).children(selector);

            if ($.isFunction(before_stringify)) {

                before_stringify(node_dom);

            }
            
            return { string: callables.dom_stringify(node_dom), dom: node_dom };
            
        },

        /**
         * Capitalize first letter of each words
         * @param  string  string   
         * @return string
         */
        ucwords: function(string) {

            string = string.toLowerCase();
            return string.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function(string){
                return string.toUpperCase();
            });

        },

        /**
         * convert words to snake case
         * @param  string  string   
         * @return string
         */
        snake_case: function(string) {

            return string.replace(/ /g,'')
                        .replace(/\.?([A-Z]+)/g, function (x,y){ return "_" + y.toLowerCase() })
                        .replace(/^_/, "");

        },

        /**
         * This will check if URL is absolute
         * @param  string url 
         * @return boolean
         */
        is_absolute_url: function(url) {
            return new RegExp('^([a-z]+://|//)', 'i').test(url);
        },

        /**
         * This will filter object to return only some of its properties
         * @param  object   object
         * @param  array    list  
         * @return array
         */
        object_only: function(object, list) {
            var new_object = {};
            list.forEach(function(item) {
                if (!callables.empty(object[item])) {
                    new_object[item] = object[item];
                }
            });
            return new_object;
        },

        /**
         * This will filter object to return only some of its properties
         * @param  object   object
         * @param  array    list  
         * @return array
         */
        object_except: function(object, list) {
            var new_object = {};

            for (var key in object) {
                if (!callables.in_array(key, list)) {
                    new_object[key] = object[key];
                }
            }

            return new_object;
        },

        /**
         * This will check if DOM node is still present in html
         * @param  dom  node jquery wrapped DOM node
         * @return boolean
         */
        is_in_html: function(node) {
            var root = node.parents('html')[0];
            return (node.length > 0 && !callables.empty(root) && root === document.documentElement);
        },

        /**
         * This will check if variable is a number type
         * @param  mixed   number
         * @return boolean
         */
        is_number_type: function(number) {
            return typeof number == 'number';
        },

        /**
         * This will clone array but only immediate childrens
         * Dont use this for nested array
         * @param  array  array 
         * @return return array
         */
        array_shallow_clone: function(array) {
            return array.slice(0);
        },

        /**
         * This will check if element is jquery/dom 
         * @return boolean
         */
        is_dom: function(element) {
            return (element instanceof jQuery || element instanceof Element);
        },

        /** 
         * all tabbable element
         * @param  string  prefix
         * @return string
         */
        tabbable: function(prefix) {
            prefix = prefix || '';
            var selectors = [
                                'a[href]', 
                                'area[href]', 
                                'input:not([disabled])', 
                                'select:not([disabled])', 
                                'textarea:not([disabled])',
                                'button:not([disabled])',
                                'iframe',
                                'object',
                                'embed',
                                '*[tabindex]',
                                '*[contenteditable]'
                            ];
            return prefix + selectors.join(', '+prefix);            
        },

        /**
         * This will load image src
         * @param  string src 
         * @param  object callbacks 
         * @return void
         */
        load_image: function(src, callbacks) {

            var image_preload = new Image();

            $(image_preload).on('load', function (response, status, xhr) {
                callbacks.success(src);
            }).on('error', function (response, status, xhr) {
                callbacks.error(src);
            });

            $(image_preload).attr({
                src: src
            });
            
            if (image_preload.complete || image_preload.readyState === 4) {
                callbacks.success(src);
            }
        },

        /**
         * This will return all events that can be bind for animation end
         * @return string
         */
        animation_end_events: function() {
            return 'webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend'
                  +' webkitAnimationEnd oanimationend msAnimationEnd animationend';
        },

        /**
         * This will repalce < and >
         * @param  string string
         * @return string
         */
        no_tag: function(string) {
            string = callables.replace_all(string, '>', '&#62;');
            string = callables.replace_all(string, '<', '&#60;');

            return string;
        },

        /**
         * This will repalce < and >
         * @param  string string
         * @return string
         */
        nl2br: function(string, is_xhtml) {
            var break_tag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
            return (string + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + break_tag + '$2');
        },

        /**
         * Get all attributes in an object form
         * @param  dom element
         * @return object
         */
        get_all_attributes: function(element) {
            var attr_obj = {};
            $.each(element[0].attributes, function() {
                if(this.specified) {
                    attr_obj[this.name] = this.value;
                }
            });
            return attr_obj;
        },

        /**
         * This will set all element attributes
         * @param  dom    element    
         * @param  object attributes 
         * @return void
         */
        set_attributes: function(element, attributes) {
            $.each(attributes, function(name, value) {
                // Ensure that class names are not copied but rather added
                if (name== "class") {
                    element.addClass(value);
                } else {
                    element.attr(name, value);
                }
            });
        },

        /**
         * This will serialize form fields into JSON format
         * @param  dom form
         * @return object
         */
        json_serialize: function(form) {
            var array_serialized = form.serializeArray();
            var json_serialized  = {};

            array_serialized.forEach(function(field_info) {
                if (json_serialized[field_info.name] !== undefined) {
                    if (!json_serialized[field_info.name].push) {
                        json_serialized[field_info.name] = [json_serialized[field_info.name]];
                    }
                    json_serialized[field_info.name].push(field_info.value || '');
                } else {
                    json_serialized[field_info.name] = field_info.value || '';
                }
            });

            return json_serialized;
        },

        /**
         * Check if string is valid email
         * @param  string  string 
         * @return boolean
         */
        is_email: function(string) {
            var regex = new RegExp([
                            '^(([^<>()[\\]\\\.,;:\\s@\"]+(\\.[^<>()\\[\\]\\\.,;:\\s@\"]+)*)',
                            '|(".+"))@((\\[[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.',
                            '[0-9]{1,3}\])|(([a-zA-Z\\-0-9]+\\.)+',
                            '[a-zA-Z]{2,}))$'
                        ].join(''));
            return regex.test(string);
        },

        /**
         * This will check if string is alphabetic
         * @param  string  string 
         * @return boolean
         */
        is_alpha_space: function(string) {
            return /^[A-Za-z\s]+$/.test(string);
        },

        /** 
         * Check if number is between 2 numbers
         * @param  number  number 
         * @param  number  min   
         * @param  number  max    
         * @return boolean
         */
        is_between: function(number, min, max) {
            return number >= min && number<=max;
        },

        /**
         * Check if string is alpha numeric only
         * @param  string  string void
         * @return boolean
         */
        is_alpha_num: function(string) {
            return /^[a-zA-Z0-9]+$/.test(string);
        },

        /**
         * Check if string is alpha numeric only
         * @param  string  string void
         * @return boolean
         */
        is_alpha_only: function(string) {
            return /^[a-zA-Z]+$/.test(string);
        },

        /**
         * check if a string contains lower case letter
         * @param  string string 
         * @return boolean
         */
        has_lowercase: function(string) {
            return string.match(/[a-z]/);
        },

        /**
         * check if a string contains upper case letter
         * @param  string string 
         * @return boolean
         */
        has_uppercase: function(string) {
            return string.match(/[A-Z]/);
        },

        /**
         * check if a string contains number
         * @param  string string 
         * @return boolean
         */
        has_number: function(string) {
            return string.match(/\d+/);
        },

        /**
         * check if a string contains number
         * @param  string string 
         * @return boolean
         */
        has_special_chars: function(string) {
            return !(/^[a-zA-Z0-9]+$/.test(string));
        },

        /**
         * This will read file being selected in file input
         * @param  dom    file_input 
         * @param  object callbacks 
         * @param  string read_as 
         * @return void
         */
        read_files: function(file_input, callbacks, read_as) {
            read_as         = read_as || 'DataURL';
            var files       = file_input[0].files;
            var files_count = files.length;
            var read_files  = [];

            var done_callback = function() {
                                    if ($.isFunction(callbacks.done) && index >= (files_count-1)) {
                                        var done = callbacks.done;
                                        delete callbacks.done;
                                        done.call(file_input,read_files);
                                    }
                                };

            if (typeof (FileReader) !== "undefined") {

                for (var index = 0; index < files_count; index++) {
                    (function(file, index) {

                        // compile info
                        var final_info = {
                                            id     : index,
                                            result : null,
                                            info   : file,
                                            percent: 0
                                        };

                        read_files.push(final_info);

                        var reader        = new FileReader();

                        reader.onprogress = function (e) {
                                                if (e.lengthComputable) {
                                                    final_info.percent = Math.round((e.loaded * 100) / e.total);

                                                    // 100% must be executed on onload event only
                                                    if (final_info.percent == 100) {
                                                        final_info.percent = 99;
                                                    }
                                                }  

                                                if ($.isFunction(callbacks.progress)) {
                                                    callbacks.progress.call(file_input,final_info);
                                                }
                                            };

                        reader.onload = function (e) {
                                            final_info.percent = 100;
                                            final_info.result  = e.target.result;

                                            if ($.isFunction(callbacks.progress)) {
                                                callbacks.progress.call(file_input,final_info);
                                            }

                                            if ($.isFunction(callbacks.load)) {
                                                callbacks.load.call(file_input,final_info);
                                            }

                                            done_callback();
                                        };

                        reader.onerror = function (e) {
                                            final_info.percent = -1;
                                            final_info.error   = e.target.error;

                                            if ($.isFunction(callbacks.progress)) {
                                                callbacks.progress.call(file_input,final_info);
                                            }

                                            if ($.isFunction(callbacks.error)) {
                                                callbacks.error.call(file_input,final_info);
                                            }

                                            done_callback();
                                        };
                        

                        reader['readAs'+read_as](file);

                    }(files[index], index));
                };


            } else {

                var final_info = {
                                    id     : 0,
                                    result : null,
                                    info   : null,
                                    percent: -1,
                                    error  : { code: 'NO_FILEREADER_SUPPORT' }
                                };

                 read_files.push(final_info);

                if ($.isFunction(callbacks.progress)) {
                    callbacks.progress.call(file_input,final_info);
                }

                if ($.isFunction(callbacks.error)) {
                    callbacks.error.call(file_input,final_info);
                }

                done_callback();

                console.warn('Error: This browser does not support FileReader.');
            }

        },

        /**
         * This will convert base64 data to blob
         * @param  string base64_data
         * @param  string content_type 
         * @param  int    slice_size   
         * @return blob
         */
        base64_to_blob: function (base64_data, content_type, slice_size) {
            var 
                byte_characters  = atob(base64_data.split(',')[1]),
                byte_arrays      = [];
                content_type    = content_type || '';
                slice_size      = slice_size || 512;


            for (var offset = 0; offset < byte_characters.length; offset += slice_size) {

                var 
                    slice           = byte_characters.slice(offset, offset + slice_size),
                    byteNumbers     = new Array(slice.length);

                for (var i = 0; i < slice.length; i++) {

                    byteNumbers[i] = slice.charCodeAt(i);
                }

                var byteArray = new Uint8Array(byteNumbers);

                byte_arrays.push(byteArray);
            }
            
            var blob;
            
            try {

                blob = new Blob(byte_arrays, {type: content_type});

            } catch (e) {
                // TypeError old chrome and FF
                window.BlobBuilder = window.BlobBuilder ||
                        window.WebKitBlobBuilder ||
                        window.MozBlobBuilder ||
                        window.MSBlobBuilder;
                if (e.name === 'TypeError' && window.BlobBuilder) {
                    var bb = new BlobBuilder();
                    bb.append(byte_arrays);
                    blob = bb.getBlob(content_type);
                }
                else if (e.name === "InvalidStateError") {
                    // InvalidStateError (tested on FF13 WinXP)
                    blob = new Blob(byte_arrays, {type: content_type});
                }
                else {
                    console.warn('ERROR: Blob constructor unsupported entirely.');
                }
            }

            return blob;
        },

        /**
         * Convert object into formData object
         * @param  object form_data_object 
         * @return object
         */
        form_data: function(form_data_object) {
            var form_data = new FormData();

            for (var data_key in form_data_object) {
                form_data.append(data_key, form_data_object[data_key]);
            }

            return form_data;
        },

        /**
         * This will stream video to video tag using webcam
         * @param  object   options 
         * @param  object   callbacks [description]
         * @return void
         */
        video_stream: function(callbacks, options, video) {
            // compose video object
            var constraints = {video:{}};
            callables.assoc_merge(constraints.video, options);

            var success_handler = function(stream) {
                                    var load_to_video = function(video) {
                                                        // Older browsers may not have srcObject
                                                        if ("srcObject" in video) {
                                                            video.srcObject = stream;
                                                        } else {
                                                            // Avoid using this in new browsers, as it is going away.
                                                            video.src = window.URL.createObjectURL(stream);
                                                        }
                                                    };

                                    if ($.isFunction(callbacks.load)) {
                                        callbacks.load.call(this, stream, load_to_video);
                                    }
                                };

            var error_handler   = function() {
                                    if ($.isFunction(callbacks.error)) {
                                        callbacks.error.apply(this, arguments);
                                    }
                                };  

            // we need to use old getUserMedia in old devices
            if (navigator.mediaDevices !== undefined && navigator.mediaDevices.getUserMedia !== undefined) {
                navigator.mediaDevices.getUserMedia(constraints).then(success_handler).catch(error_handler);
            } else {
                var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
                // there's nothing we can do if some browsers doesn't iplement this
                if (!getUserMedia) {

                    error_handler(new Error('getUserMedia is not implemented in this browser'), true);
                    
                } else {

                    try {

                        getUserMedia.call(navigator, constraints, success_handler, error_handler);

                    } catch(error) {

                        error_handler(error);

                    }

                }
            }
        },

        /**
         * This will convert current video frame to image
         * @param  dom video 
         * @return string
         */
        video_to_base64: function(video) {
            // temporary canvas for video only
            var video_canvas           = document.createElement('canvas');
            var video_canvas_context   = video_canvas.getContext('2d');    
            video_canvas.width         = video.videoWidth  || video.width;
            video_canvas.height        = video.videoHeight || video.height;
            video_canvas_context.drawImage(video, 0, 0, video_canvas.width, video_canvas.height);
            
            return video_canvas.toDataURL("image/png");
        },

        /** 
         * Used to have delay for events execution and prevent spamming
         * Identical to debounce
         * @param  int milliseconds
         * @return function
         */
        event_delay: function() {
            var timers = {};
            return function (callback, latency_ms, id) {
                var id = id || 'default';
                var ms = ms || 300;

                if (timers[id]) {
                    clearTimeout(timers[id]);
                }

                timers[id] = setTimeout(callback, latency_ms);
            };
        }(),

        /**
         * This will convert number into money format
         * @param  number number  
         * @param  int    decimals
         * @param  object options  
         * @return string
         */
        money_format: function(number, decimals, options) {
            decimals = decimals === undefined ? 2 : decimals;
            options  = options  || {};
            options.decimal_delimeter  = options.decimal_delimeter  || '.';
            options.thousand_delimeter = options.thousand_delimeter || ',';

            number            = String(callables.replace_all(number, ',', ''));
            var parsed_number = parseFloat(number);

            if (isNaN(parsed_number) || parsed_number == 0 || !callables.is_number_only(number)) {
                number = '0';
            }

            var split_parts    = number.split(options.decimal_delimeter);

            // whole number formatitng
            var whole_number = split_parts[0];
            var digits_array = [];
            var ctr          = 0;
            while (whole_number.length > 3) {
                ctr++;
                digits_array.splice(ctr, 0, options.thousand_delimeter + whole_number.slice(-3));
                whole_number = whole_number.slice(0, -3);
            }
            var formatted_whole_number = (parsed_number < 0 ? '-' : '')+whole_number+digits_array.reverse().join('');

            // decimal number formatitng
            var decimal_number = split_parts[1] || '';
            if (decimals <= 0) {

                decimal_number = '';

            } else {

                var decimal_length = decimal_number.length;
                if (decimal_number.length > decimals) {
                    decimal_number = decimal_number.substring(0,decimals);
                }

                if (decimal_number.length < decimals) {
                    decimal_number = callables.str_pad_right(decimal_number,decimals,'0');
                }

            }

            if (decimal_number.length > 0) {
                var formatted_decimal_number = '.' + decimal_number;
            } else {
                var formatted_decimal_number = '';
            }


            return formatted_whole_number + formatted_decimal_number;
        },

        /**
         * This will get the value of array last item
         * @param  array array 
         * @return mixed
         */
        array_last: function(array) {
            if ($.isArray(array)) {
                return array[array.length-1];
            }
        },

        /**
         * Check wether the element is inside or equal the scope
         * @param  dom  scope       non jquery dom
         * @param  dom  element     non jquery dom
         * @return boolean
         */
        in_dom_scope: function(scope, element) {
            return ($(scope).is($(element)) || $.contains(scope, $(element)));
        },

        /**
         * Check for scrollable parent
         * @param  dom child_dom 
         * @return dom
         */
        scrollable_parent: function(child_dom) {
            return callables.scrollable_mend(child_dom.scrollParent());
        },

        /**
         * Check if value is a string
         * @param  mixed   value 
         * @return boolean
         */
        is_string: function(value) {
            return $.type(value) === 'string';
        },

        /**
         * Create unique array
         * @param  array   value 
         * @return array
         */
        array_unique: function(array) {
            var new_array = [];
            array.forEach(function(value) {
                if (!callables.in_array(value, new_array)) {
                    new_array.push(value);
                }
            });

            return value;
        },

        /**
         * Check if audio is playing
         * @param  object audio 
         * @return boolean
         */
        is_audio_playing: function(audio) {
            return audio
                && audio.currentTime > 0
                && !audio.paused
                && !audio.ended
                && audio.readyState > 2;
        },

        /**
         * This will get first item of an object
         * @param  object obj 
         * @return mixed
         */
        obj_get_first: function(obj) {
            var keys = Object.keys(obj);
            if (keys.length > 0) {
                return obj[keys[0]];
            } else {
                return null;
            }
        },

        /**
         * This will get right offset of the element
         * @param  dom element 
         * @return int
         */
        offset_right: function(element) {
            var offsets = element[0].getBoundingClientRect();
            return callables.viewport_width($(window)) - (offsets.left + offsets.width);
        },
        
        /**
         * Jquery animate wrapper with additional autofix for window/document
         * @param  dom            element   
         * @param  object         properties
         * @param  number/string  duration   
         * @param  string         easing     
         * @param  function       complete   
         * @return object
         */
        animate: function(element, properties, duration, easing , complete) {
            if (element.is($(document)) || element.is($(window))) {
                element = $('html, body');
            }

            return element.animate(properties, duration, easing , complete);
        },

        /**
         *  Remove extension from absolute path
         * @param  string path
         * @return string
         */
        remove_extension: function(path) {
            return path.replace(/\.[^/.]+$/, "");
        },

        /** 
         * This will detect if current device supports touch gesture
         * @return boolean
         */
        is_touch_device: function() {
            return 'ontouchstart' in window || navigator.msMaxTouchPoints;
        },

        /**
         * Same as event delay, but better used if event callback is timing sensitive
         * @param  function callback  
         * @param  int      latency_ms 
         * @param  string   id         
         * @return void 
         */
        event_delay_animation: function() {
            var timers = {};

            /**
             * [description]
             */
            return function(callback, latency_ms, id) {
                if (!callables.empty(timers[id])) {
                    callables.cancelAnimationFrame(timers[id]);
                }

                var start_time = Date.now();

                function step() {
                    var now = Date.now();
                    var elapsed = now - start_time;

                    if (elapsed < latency_ms) {
                        callables.cancelAnimationFrame(timers[id]);
                        timers[id] = callables.requestAnimationFrame(step);
                    } else {
                        callback();
                    }
                }

                timers[id] = callables.requestAnimationFrame(step);
            };
        }(),

        /**
         * This will remove non letters and numbers character
         * @param  string string 
         * @return string
         */
        to_alpha_num: function(string) {
            return string.replace(/[^A-Za-z0-9 ]/g,'');
        },

        /**
         * This will return if current window is a popup
         * @return boolean
         */
        is_popup: function() {
            return (window.opener && window.opener !== window);
        },

        /**
         * This will return window real height 
         * If the elemenis not window this will use simple .height() of jquery
         * @param  dom element   
         * @return int
         */
        viewport_height: function(element) {
            if (element[0] === window) {
                return window.innerHeight || element.height();
            } else {
                element.height();
            }
        },

        /**
         * This will disable zoom and right click
         * @return void
         */
        disable_window_manipulation: function() {
            $(document).on('keydown', callables.disbale_zoom_keys)
                        .on('contextmenu', callables.prevent_default)
                        .on('mousewheel DOMMouseScroll', callables.disbale_zoom_mousewheel);
        },

        /**
         * This will enable zoom and right click
         * @return void
         */
        enable_window_manipulation: function() {
            $(document).off('keydown', callables.disbale_zoom_keys)
                        .off('contextmenu', callables.prevent_default)
                        .off('mousewheel DOMMouseScroll', callables.disbale_zoom_mousewheel);
        },

        /**
         * This will check if string contains letters and numbers
         * @param  string string
         * @return boolean
         */
        mixed_alpha_num: function(string) {
            return /^[A-Za-z].*[0-9]/.test(string);
        },

        /**
         * This will check if string contains letters, numbers and symbols
         * @param  string string
         * @return boolean
         */
        alpha_num_symbol: function(string) {
            return /[A-Za-z]/.test(string) && /[0-9]/.test(string) && /[\W-_]/.test(string);
        },

        /**
         * This will add parameter to url
         * @param  string string
         * @return string
         */
        add_url_param: function(url, key_val) {
            var params = new URL(url).searchParams;
            return url.concat(params == "" ? "?" : "&").concat(key_val);
        },

        /**
         * This will check if string has no space
         * @param  string string 
         * @return boolean
         */
        has_no_space:  function(value) {

            return /\s/.test(value) ? false : true ;

        },

        /**
         * This will check if string is alphabet of any language 
         * @param  {string} value 
         * @return {bool}       
         */
        alpha_language: function(value) {
            
            return /^[A-z\u3400-\u9FBF ]+$/.test(value) ? true : false ;

        }
    };
});