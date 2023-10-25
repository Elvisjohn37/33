/**
 * PS date handler
 *
 * Author PS Team
 */
define('ps_date', ['jquery','ps_helper','jquery_ui'], function($,ps_helper) {

    'use strict';

	var globals   = { debug: false };
	var callables = {
        /**
         * Extend helper debug using local configuration
         */
        debug: ps_helper.debug(globals.debug),

        /**
         * 12 hours format conversion
         * @param  int hours 0-23
         * @return int
         */
        twelve_hour_format: function(hours) {
            if (hours===0) {

                return 12;

            } else if (hours > 12) {

                return hours - 12;

            } else {

                return hours;

            }
        },

        /**
         * This will get if hours is AM/PM
         * @param  int hours 0-23
         * @return int
         */
        get_meridiem: function(hours) {
            if (hours >= 12) {
                return 'PM';
            } else {
                return 'AM';
            }
        },

        /**
         * This will convert given date string into specified format
         * Using $.datepicker.formatDate API for date, added custom time formatter
         * @param  string        format    Jquery.datetimepicker plus the ff:
         *                                 hhh : 12-hour format with leading zero
         *                                 hh  : 12-hour format without leading zero
         *                                 HHH : 24-hour format with leading zero
         *                                 HH  : 24-hour format without leading zero
         *                                 iii : minutes with leading zero
         *                                 ii  : minutes without leading zero
         *                                 sss : seconds with leading zero
         *                                 ss  : seconds without leading zero
         *                                 aa  : merediem lowercase
         *                                 AA  : merediem uppercase
         * @param  string/object date    
         * @param  object        options
         * @return string
         */
        format_date: function(format, date, options) {
            format            = format || 'yy-mm-dd';
            options           = options || {}; 
            
            // 2 letters minimum to prevent conflict with dateFormat of jquery
            var time_formats  = ['hhh','hh','HHH','HH','iii','ii','sss','ss','aa','AA','gg'];

            if (!(date instanceof Date)) {
                date = new Date(date);
            }

            var formatted_date = $.datepicker.formatDate(format, date, options);

            time_formats.forEach(function(time_format) {
                if (ps_helper.is_contain(time_format, formatted_date)) {
                    switch (time_format) {
                        case 'HHH': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                ps_helper.str_pad_left(date.getHours(),2, 0)
                                            );
                            break;

                        case 'HH': 
                            formatted_date = ps_helper.replace_all(formatted_date,time_format, date.getHours());
                            break;

                        case 'hhh': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                ps_helper.str_pad_left(
                                                    callables.twelve_hour_format(date.getHours()),
                                                    2,
                                                    '0'
                                                )
                                            );
                            break;

                        case 'hh': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                callables.twelve_hour_format(date.getHours())
                                            );
                            break;


                        case 'iii': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                ps_helper.str_pad_left(date.getMinutes(),2,0)
                                            );
                            break;

                        case 'ii': 
                            formatted_date = ps_helper.replace_all(formatted_date,time_format,date.getMinutes());
                            break;


                        case 'sss': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                ps_helper.str_pad_left(date.getSeconds(),2,0)
                                            );
                            break;

                        case 'ss': 
                            formatted_date = ps_helper.replace_all(formatted_date,time_format,date.getSeconds());
                            break;



                        case 'AA': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                callables.get_meridiem(date.getHours()).toUpperCase()
                                            );
                            break;

                        case 'aa': 
                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                callables.get_meridiem(date.getHours()).toLowerCase()
                                            );
                            break;

                        case 'gg':     

                            var gmt = date.getTimezoneOffset();

                            if (gmt < 0 ) {
                                var gmt_string = 'GMT +' + (-(gmt/60));
                            } else { 
                                var gmt_string = 'GMT -' + ((gmt/60));
                            }

                            formatted_date = ps_helper.replace_all(
                                                formatted_date,
                                                time_format,
                                                gmt_string
                                            );
                            break;
                    }
                }
            });

            return formatted_date;
        },

        /**
         * This will get the current date in given format
         * @param  string format
         * @param  object options
         * @return string
         */
        get_current_date: function(format, options) {
            format  = format || 'yy-mm-dd hh:ii:ss';
            options = options || {};
            return callables.format_date(format, new Date(), options);
        },

        /**
         * This will get how many days in a month
         * @param  string/object date  
         * @return int
         */
        month_days: function(date) {
            if (!( date instanceof Date )) {
                date = new Date(date);
            }

            return new Date(date.getFullYear(), date.getMonth()+1, 0).getDate();
        },

        /**
         * This will get what weekday the month starts
         * Starts with 0(Sunday)
         * @param  string/object date  
         * @return int
         */
        month_start_weekday: function(date) {
            if (!( date instanceof Date )) {
                date = new Date(date);
            }

            return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
        },

        /**
         * This will compute the difference of two dates
         * @param  string/object date first_date  
         * @param  string/object date second_date 
         * @param  array formats     this will format the return computed difference of two dates
         * @return array             
         */
        diff_date: function(first_date, second_date, formats) {
            formats = formats || [];
            
            var formatted_date = {};

            if (!( first_date instanceof Date )) {
                first_date = new Date(first_date);
            }
            if (!( second_date instanceof Date )) {
                second_date = new Date(second_date);
            }
            //miliseconds
            formatted_date.difference = first_date.getTime() - second_date.getTime();

            formats.forEach(function(format) {

                switch (format) {
                    case 'weeks':
                        // 1000 * 60 * 60 * 24*7 = 604800000 milisec_per_week
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / 86400000),2,0
                                                );
                        break;
                    case 'days': 
                        // 1000 * 60 * 60 * 24 = 86400000 milisec_per_day
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / 86400000),2,0
                                                );
                        break;
                    case 'formatted_days':
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / (86400000) % 7),2,0
                                                );
                        break;
                    case 'hours': 
                        // 1000 * 60 * 60  = 3600000 milisec_per_hour
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / 3600000),2,0
                                                ); 
                        break;
                    case 'formatted_hours':
                        // 1000 * 60 * 60  = 3600000 milisec_per_hour
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / (3600000) % 24),2,0
                                                );

                        break;
                    case 'minutes':
                        // 1000 * 60 = 60000 milisec_per_minute
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / 60000),2,0
                                                );
                        break;
                    case 'formatted_minutes': 
                        // 1000 * 60 = 60000 milisec_per_minute
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / (60000) % 60),2,0
                                                );
                        break;
                    case 'seconds': 
                        // 1000 milisec_per_minute
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / 1000),2,0
                                                );
                        break;
                    case 'formatted_seconds':
                        // 1000 milisec_per_minute
                        formatted_date[format] = ps_helper.str_pad_left(
                                                    Math.floor(formatted_date.difference / (1000) % 60),2,0
                                                );
                        break;
                }
            });

            formatted_date.difference =  Math.floor(formatted_date.difference/1000); 
           
            return formatted_date;
        },

        /**
         * This will get the date after adding seconds
         * @param  string/object date    
         * @param  int           seconds 
         * @param  string        format
         * @return string
         */
        add_seconds: function (date, seconds, format) {

            format = format || 'yy-mm-dd hh:ii:ss';

            if (!( date instanceof Date )) {
                date = new Date(date);
            }

            return callables.format_date(format, new Date(date.getTime() + (seconds * 1000)));
        }
    };

	return {
        format_date        : callables.format_date,
        get_current_date   : callables.get_current_date,
        month_days         : callables.month_days,
        month_start_weekday: callables.month_start_weekday,
        diff_date          : callables.diff_date,
        add_seconds        : callables.add_seconds
	};
});