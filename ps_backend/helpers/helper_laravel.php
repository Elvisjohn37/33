<?php
/*
|--------------------------------------------------------------------------
| helper_laravel functions
|--------------------------------------------------------------------------
| 
| Keep all functions in this area simple
|
| This helper functions can still be coded in a simple way without any dependency,
| however we still used some laravel helper class to make it easier and to maximize the framework
| 
*/


/*
|--------------------------------------------------------------------------
| Request class
|--------------------------------------------------------------------------
| 
*/

/**
 * This will get latest page referrer if its different than the current host
 * @return array  
 */
function referrer_url() {

    $current_host = Request::server("HTTP_HOST");
    $referrer     = Request::header('referer');

    if ($referrer != "") {

        $parse_referer = custom_parse_url($referrer);

        if ($parse_referer['host'] != $current_host) {

            return array('is_host' => false, 'referrer' => $parse_referer['host']);
        }
    }

    return array('is_host' => true, 'referrer' => $current_host);
}

/**
 * get client IP
 * @return string
 */
function get_ip() {

    $ip = Request::header('x-forwarded-for');

    if (empty($ip)) {
        $ip = Request::getClientIP();
    }

    return $ip;
}

/**
 * This will get the current user agent
 * @return string
 */
function get_user_agent() {

    $agent = Request::header('User-Agent');
    
    if (empty($agent)) {
        $agent = Request::server('HTTP_USER_AGENT');
    }

    return $agent;
}

/**
 * get the device the user is using
 * 
 * @return string
 */
function get_user_device() {
    
    $tablet_browser    = 0;
    $mobile_browser    = 0;
    $is_tablet         = preg_match(
                            '/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', 
                            strtolower(Request::server('HTTP_USER_AGENT'))
                        );
    if ($is_tablet) {
        
        $tablet_browser++;
    }
    
    $is_mobile_browser = preg_match(
                            '/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', 
                            strtolower(Request::server('HTTP_USER_AGENT'))
                        );

    if ($is_mobile_browser) {
        
        $mobile_browser++;
    }
        
    if ((strpos(strtolower(Request::server('HTTP_ACCEPT')), 'application/vnd.wap.xhtml+xml') > 0) 
            or ( (Request::server('HTTP_X_WAP_PROFILE') or Request::server('HTTP_PROFILE') ))){
        
        $mobile_browser++;
    }
        
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
    
    $mobile_agents = array(
        'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
        'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
        'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
        'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
        'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
        'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
        'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
        'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
        'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

    if (in_array($mobile_ua, $mobile_agents)){
        
        $mobile_browser++;
    }
            
    if (strpos(strtolower(Request::server('HTTP_USER_AGENT')), 'opera mini') > 0) {
        
        $mobile_browser++;

        $stock_ua = '';
        
        if ( strtolower( Request::server('HTTP_X_OPERAMINI_PHONE_UA') ) ) {
            
            $stock_ua = Request::server('HTTP_X_OPERAMINI_PHONE_UA');
            
        }else {
            
            if( Request::server('HTTP_DEVICE_STOCK_UA') ){
                
                $stock_ua = Request::server('HTTP_DEVICE_STOCK_UA');
            } 
        }
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
            
            $tablet_browser++;
        }
    }
    
    if ($tablet_browser > 0) {
        
        return 'TABLET';
        
    } else if ($mobile_browser > 0) {
        
        return 'MOBILE';
        
    } else {
        
        return 'DESKTOP';
    }
}

/*
|--------------------------------------------------------------------------
| Carbon class
|--------------------------------------------------------------------------
| 
*/

/**
 * This will substract two dates and will return value according to given 
 * @param  string        $first_date        
 * @param  string        $second_date       
 * @param  array/string  $get_difference_in if array given this will return array else this will return string
 * @return array/string                          
 */
function substract_dates($first_date, $second_date, $get_difference_in = 'days') {

    $first_date  = Carbon::parse($first_date);
    $second_date = Carbon::parse($second_date);

    $diff_getter = function ($get_difference_in) use($first_date,$second_date) {

        $carbon_method = 'diffIn'.ucwords(strtolower($get_difference_in));

        return $first_date->$carbon_method($second_date);

    };

    if (is_array($get_difference_in)) {
        
        $diff_list = array();

        foreach ($get_difference_in as $value) {
            
            $diff_list[strtolower($value)] = $diff_getter($value);

        }

        return $diff_list;

    } else {


        return $diff_getter($get_difference_in);

    }

}

/*
|--------------------------------------------------------------------------
| Laravel helper functions
|--------------------------------------------------------------------------
| 
*/

/**
 * This will group by array
 * @param  array   $array       [description]
 * @param  array   $levels      define set of indexes from top level to lowest
 * @param  array   $options     [
 *                                  'collapse_single': Replace single item with its children
 *                                  'move_up_single' : Move the order of single items into the beggining of array,
 *                                  'stop_last_level': Set last level as lowest value
 *                              ]
 * @return array
 */
function array_group_by($array, $levels, $options = array())
{
    set_default($options, 'collapse_single' , false);
    set_default($options, 'move_up_single'  , false);
    set_default($options, 'add_remaining'   , true);

    $current_level        = array_shift($levels);
    $remaining_levels     = $levels;
    $current_level_values = array();

    // process current level grouping
    foreach ($array as &$value) {

        if (!isset($current_level_values[$value[$current_level]])) {
            
            $current_level_values[$value[$current_level]] = array(

                                                            'id'       => $value[$current_level],
                                                            'children' => array()

                                                        );

        }

        $child_value = array_except($value, array($current_level));

        if (count($remaining_levels) > 0) { 

            $current_level_values[$value[$current_level]]['children'][] = $child_value;

        } else {

            // last level dont have children, add the remaining values to current level instead
            if ($options['add_remaining'] == true) {

                assoc_array_merge($current_level_values[$value[$current_level]],$child_value);

            }

            unset($current_level_values[$value[$current_level]]['children']);

        }

    }

    // process childrens and ordering if its not the last level yet
    if (count($remaining_levels) > 0) {

        $no_children = array();
        foreach ($current_level_values as $index => &$current_level_value) {

            if (isset($current_level_value['children'])) {

                $current_level_value['children'] = array_group_by(
                                                    $current_level_value['children'],
                                                    $remaining_levels, 
                                                    $options
                                                );


                if (count($current_level_value['children']) == 1 && $options['collapse_single']) {
                    
                    $single_child =  $current_level_value['children'][0];

                    // if have children adopt children else adopt the parent :)
                    if (isset($single_child['children'])) {

                        $current_level_value['children'] = $single_child['children'];
                        
                    } else {

                        $current_level_value = $single_child;

                    }
                    
                }

            }

            // collect single values we will prepend this later
            if (!isset($current_level_value['children']) && $options['move_up_single']) {

                $no_children[] = $current_level_value;
                unset($current_level_values[$index]);

            }

        }

        if ($options['move_up_single']) {

            $no_children = array_reverse($no_children);

            foreach ($no_children as $no_children_item) {
                
                array_unshift($current_level_values, $no_children_item);

            }

        }
    } 

    return array_values($current_level_values);
    
}

/**
 * format datetime string to client timezone then format
 * @param  string $datetime datetime format
 * @param  mixed $timezone  dateTimezone instance or string timezone
 * @param  string $format   
 * @return string           
 */
function timezone_format($datetime, $timezone, $format)
{

    return Carbon::createFromFormat('Y-m-d H:i:s', $datetime, $timezone)->format($format);

}
/**
 * compare two date, 
 * @param   string $datetime_now    
 * @param   string $datetime_msg   
 * @param   string $compare use to compare   
 * @return  bool                
 */
function compare_dates($datetime_now, $datetime_compare,$compare)
{

    $datetime_now     = Carbon::parse($datetime_now);
    $datetime_compare = Carbon::parse($datetime_compare);
    return $datetime_now->$compare($datetime_compare);
}

/**
 * Subtract date by given number
 * @param  string $date 
 * @param  int $number 
 * @return string       date subracted
 */
function previous_date($date, $number, $subtrac_in = 'days')
{

    $date = Carbon::parse($date);

    $carbon_method = 'sub'.ucwords(strtolower($subtrac_in));

    return $date->$carbon_method($number)->toDateTimeString();
   
}