<?php
/*
|--------------------------------------------------------------------------
| helper functions
|--------------------------------------------------------------------------
| 
| Keep all functions in this area simple
| This will contain all generic functions that has no dependency with our framework
| 
*/

/**
 * This will mimic PHP ip2long and will convert it to non negative(unsigned)
 * Please read ip2long documentation, pending: add support to IPv6
 * @param  string $ip
 * @return number
 */
function unsigned_ip2long($ip) {

    return sprintf('%u', ip2long($ip));

}

/**
 * This will check if given IP address is in range
 * Implements unsigned_ip2long helper
 * @param  string $ip      IP to be searched
 * @param  string $min_ip 
 * @param  string $max_ip 
 * @return boolean
 */
function ip_in_range($ip, $min_ip, $max_ip) {

    $ip     = unsigned_ip2long($ip);
    $min_ip = unsigned_ip2long($min_ip);
    $max_ip = unsigned_ip2long($max_ip);

    return ($ip >= $min_ip && $ip <= $max_ip); 

}

/**
 * This will check if given IP address is in range list
 * Implements ip_in_range helper
 * @param  string $ip      IP to be searched
 * @param  array  $ip_list [fromIP, toIP]
 * @return boolean
 */
function ip_range_list($ip, $ip_list) {

    foreach ($ip_list as $ip_range) {

        if (ip_in_range($ip,  $ip_range['fromIP'], $ip_range['toIP'])) {
                
            return true;

        }
    }

    return false;

}

/**
 * extending php parse_url with additional process
 * @param  string $url URL
 * @return array       Parsed URL
 */
function custom_parse_url($url) {

    $parsed_url = parse_url($url);

    // set default scheme
    if (!isset($parsed_url["scheme"])) {

        $url        = "http://" . $url;
        $parsed_url = custom_parse_url($url);
    }

    return $parsed_url;
}

/**
 * remove subdomain
 * @param  string $host 
 * @return string       
 */
function remove_subdomain($host) {

    $parsed_url = parse_url($host);

    if (isset($parsed_url['scheme'])) {

        $host = str_replace($parsed_url['scheme'].'://', '', $host);

    }

    $host_segments        = explode('.', $host);
    $host_segments_length = sizeof($host_segments);

    if ($host_segments_length >= 3) {

        array_splice($host_segments, 0, 1);

    }

    return implode('.', $host_segments);
}

/**
 * remove subdomain
 * @param  string $host 
 * @return string       
 */
function replace_subdomain($url, $subdomain) {

    $parsed_url = parse_url($url);

    $url = $subdomain.'.'.remove_subdomain($url);

    if (isset($parsed_url['scheme'])) {
        
        return $parsed_url['scheme'].'://'.$url;

    } else {

        return $url;

    }

}

/**
 * This will add url get params
 * @param  string $url      
 * @param  string $url_params
 * @return string
 */
function url_add_query($url, $url_params) {

    if (strpos($url, '?') !== false) {
        
        return $url.'&'.http_build_query($url_params); 

    } else {

        return $url.'?'.http_build_query($url_params); 

    }

}

/**
 * Merge associative array by foreach loop instead of PHP native array_merge
 * This is not replacement for array_merge, this works for some set of purposes
 * Note: this will not add non array arguments
 * @param  array   &$main_array      
 * @param  array   &...$merge_array accepts unlimited arguments, arrays to be merged
 * @return array
 */
function assoc_array_merge(&$main_array, ...$merge_arrays) {

    // if PHP array_merge is optimized please rewrite this function
    foreach ($merge_arrays as $merge_array) {

        if (is_array($merge_array)) {

            foreach($merge_array as $key => $value) {

                $main_array[$key] = $value;
            }

        }

    }

    return $main_array;
}

/**
 * Merge sequential array by foreach loop instead of PHP native array_merge
 * This is not replacement for array_merge, this works for some set of purposes
 * Note: this will not add non array arguments
 * @param  array   &$main_array      
 * @param  array   &...$merge_array accepts unlimited arguments, arrays to be merged
 * @return array
 */
function seq_array_merge(&$main_array, ...$merge_arrays) {

    // if PHP array_merge is optimized please rewrite this function
    foreach ($merge_arrays as $merge_array) {

        if (is_array($merge_array)) {

            foreach($merge_array as $value) {

                $main_array[] = $value;
            }

        }
    }

    return $main_array;
}

/**
 * For formating money. Outputs two decimal places without rounding off
 * 
 * @param  int   $number
 * @param  mixed $fallback_value  This will be the value if the given $number is not a number
 * @param  int   $options         [ 
 *                                    'fallback'                = (default) 0,
 *                                    'decimal_places'          = (default) 2, 
 *                                    'original_decimal_places' = (default) none
 *                                                                best to put a value if decimal places is more than 6
 *                                ]
 * @return int
 */
function custom_money_format($number, $options = array()) {

    set_default($options, 'decimal_places',          2);
    set_default($options, 'decimal_delimeter',       '.');
    set_default($options, 'original_decimal_places', 6);

    if (!is_numeric($number)) {

        $number = set_default($options, 'fallback', 0);

    }

    // handle scientific notations
    if (isset($options['original_decimal_places']) && is_numeric($options['original_decimal_places'])) {

        // convert scientific notations with precise decimal places
        $number = sprintf('%.'.$options['original_decimal_places'].'f', (string)$number);

    }

    if (is_numeric($number)) {

        $number_split = explode($options['decimal_delimeter'], $number);

        if (isset($number_split[1]) ) {

            $decimal = substr($number_split[1], 0, $options['decimal_places']);

        } else {

            $decimal = str_pad('',$options['decimal_places'],'0');

        }

        return number_format($number_split[0].$options['decimal_delimeter'].$decimal, $options['decimal_places']);

    } else {

        return $number;

    }

}

/**
 * Just like json_decode but will return the original value if it cannot be decoded
 * @param  string   $value 
 * @param  boolean  $to_array 
 * @return mixed
 */
function custom_json_decode($value, $to_array = false) {

    if (is_string($value)) {

        $decoded_value = json_decode($value, $to_array);

        if (json_last_error() == JSON_ERROR_NONE) {

            $value = $decoded_value;

        }
    }

    return $value;

}

/**
 * Checks if value can be json_encoded without errors
 * This will json_encode and json_decode the actual value
 * @param  mixed   $value 
 * @return boolean        
 */
function is_json_encodable($value) {

    if (json_encode($value) !== NULL) {
        
        return true;

    } else {

        return false;

    }

}

/**
 * This will translate strings special characters and new lines
 * @param  string $string 
 * @return string
 */
function escape_string($string) {
    $string = str_replace('\n', "\n", $string);
    $string = str_replace('\r', "\r", $string);
    
    return nl2br(htmlspecialchars($string));
    
}

/** 
 * This will remove non letters and numbers character including spaces
 * @param  string $string 
 * @return string
 */
function alphanum_only($string) {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $string);
}

/**
 * Check if datetime is at 
 * @param  string  $date  
 * @param  string $format 
 * @return boolean        
 */
function is_date_format($date, $format = 'Y-m-d H:i:s') {

    $formatted = DateTime::createFromFormat($format, $date);
    return $formatted && $formatted->format($format) == $date;

}

/**
 * This will transform date to given format
 * @param  string $date  
 * @param  string $format 
 * @return object
 */
function custom_date_format($format, $date) {

    return date($format, strtotime($date));

}

/**
 * This will get the latest last day of the month
 * If the month is not yet finish then this will get the current day
 * @param  string $date  
 * @return object
 */
function month_last_day($date) {
    $current_month = date('M');
    $date_month    = date('M', strtotime($date));

    if ($current_month == $date_month) {
        return date('d');
    } else {
        return date('t', strtotime($date));
    }
}

/**
 * This will calculate and give new date according to formula
 * @param  string $date   
 * @param  string $formula 
 * @param  string $format  
 * @return string
 */
function calculate_date_time($date, $formula, $format = 'Y-m-d H:i:s') {

    return date($format, strtotime($formula, strtotime($date)));
    
}

/**
 * This will remove all space in a string
 * @param  string $string [description]
 * @return string
 */
function remove_space($string) {

    return str_replace(' ','',$string);

}

/**
 * This will remove all new lines
 * @return string
 */
function remove_newlines($string) {

    return str_replace(PHP_EOL,'',$string);
    
}

/**
 * This will convert string to snake case
 * @param  string $string 
 * @return string
 */
function to_snake_case($string, $is_lowercase = true) {

    if ($is_lowercase) {

        $string = strtolower($string);

    }
    
    return str_replace(' ','_',$string);

}

/**
 * This will convert snake cased strings to space
 * @param  string $string 
 * @return string
 */
function snake_to_space($string) {

    return str_replace('_',' ',$string);

}

/**
 * This will convert camel cased strings to space
 * @param  string $string 
 * @return string
 */
function camel_to_space($string) {

    $regex  = '/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x';
    return strtolower(preg_replace($regex,' $1',$string));

}

/**
 * generate a token
 * @param  int $length 
 * @return string
 */
function generate_token($length) {

    return substr(uniqid(mt_rand(),true), 0, $length);

}

/**
 * This is like array_walk but can nhandle multiple arrays
 * @param  array    $arrays   array or arrays :D
 * @param  callable $callback [description]
 * @param  mixed    $userdata [description]
 * @return boolean
 */
function multi_array_walk($callback,&...$arrays) {

    foreach ($arrays as $array) {
        
       foreach ($array as $key => $value) {
        
            $callback($value,$key);

       }

    }

    return true;

}

/**
 * Add new line to paragraph
 * @param  string $string 
 * @return 
 */
function nl2p($string) {

    $paragraphs = '';

    foreach (explode(PHP_EOL, $string) as $line) {

        $paragraphs .= '<p>' . $line . '</p>';

    }

    return $paragraphs;

}

/**
 * This will remove namespace of given class
 * @param  string  $class_name 
 * @return string     
 */
function remove_namespace($class_name) {

    return substr($class_name, strrpos($class_name, '\\') + 1);

}

/**
 * This is like array_splice but will preserve the key values
 * @param  array  &$orignal_array [description]
 * @param  int    $position      
 * @param  array  $insert_array   
 * @return array
 */
function assoc_array_splice(&$orignal_array, $position, $insert_array) {

    return $orignal_array = array_slice($orignal_array, 0, $position, true) 
                            + $insert_array
                            + array_slice($orignal_array, $position, NULL, true);

}

/**
 * Generate random number
 * 
 * @param type $length
 * @return string
 */
function random_number( $length = 1 ) {
    
    $characters = '0123456789';
    $RETURN = '';
    for ($i = 0; $i < $length; $i++) {
            $RETURN .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $RETURN; 
}

/**
 * This will split the string by given delimiter 
 * and get the last segments depending on the segment count needed
 * @param  string  $string        
 * @param  string  $delimiter     
 * @param  integer $segment_count 
 * @return string
 */
function get_last_segment($string, $delimiter = '.', $segment_count = 1) {

    $array = explode($delimiter, trim($string));

    $segments = array();

    for ($i=0; $i<$segment_count; $i++) {
        
        if (count($array)>0) {

            $segments[]=array_pop($array);

        } else {

            break;

        }

    }

    return implode($delimiter,array_reverse($segments));
}

/**
 * This will split the string by given delimiter 
 * and get the first segments depending on the segment count needed
 * @param  string  $string        
 * @param  string  $delimiter     
 * @param  integer $segment_count 
 * @return string
 */
function get_first_segment($string, $delimiter = '.', $segment_count = 1) {

    $array = explode($delimiter, trim($string));

    $segments = array();

    for ($i=0; $i<$segment_count; $i++) {
        
        if (count($array)>0) {

            $segments[]=array_shift($array);

        } else {

            break;

        }

    }
    return implode($delimiter,$segments);
}

/**
 * 
 * @param  type $custom_string
 * @return type
 */
function generate_verification_code($custom_string) {
    $salt   =   str_random(32);
    $date   =   date("Y-m-d H:i:s");
    
    return MD5($custom_string.$salt.$date);
}

/**
 * this will add newline to string if its not empty
 * @param  string $string
 * @return string
 */
function new_line($string) {

    if ($string != '') {

        return '<br/>'.$string;

    }

}

/**
 * Clean value
 * 
 * @param type $value
 * @return type
 */
function sanitize_value($value) {
    return preg_replace( '/\s+/', '', $value );
}

/**
 * This will turn money formatted amounts to real float value
 * @param  string $amount 
 * @param  string $delimete
 * @return float
 */
function non_money_format($amount, $delimeter = '.') {

    $replaced_delimeter = (preg_replace('/([^0-9\\'.$delimeter.'])/i', "", $amount));
    return floatval(str_replace($delimeter, '.', $replaced_delimeter));

}

/**
 * This will check if number is a whole number
 * @param  numeric $number 
 * @return boolean        
 */
function is_whole_number($number) {

    return ($number % 1 == 0);

}

/**
 * This will give timestamp with microsecond
 * @return stirng Y-m-d H:i:s.u
 */
function timestamp_microsecond() {

    $current_microtime = microtime(true);
    $padded_microtime  = sprintf("%06d", ($current_microtime - floor($current_microtime)) * 1000000);
        
    return date('Y-m-d H:i:s.' . $padded_microtime, $current_microtime);
    
}

/**
 * This will set the default value of the object or array if index was not set
 * @param array/object &$subject      The object or array
 * @param string       $index         The index to be set
 * @param mixed        $default_value Default value
 */
function set_default(&$subject, $index, $default_value) {
    
    if (!isset($subject[$index])) {
        
        $subject[$index] = is_callable($default_value) ? $default_value() : $default_value;

    } 
    
    return $subject[$index];
}

/** 
 * This will get all first character of the string after the delimeter
 * @param  string $string    
 * @param  string $delimiter default = [space]
 * @return string
 */
function get_first_chars($string, $delimiter=' ') {

    $tokenized_string =  strtok($string, $delimiter);
    $first_chars      = '';

    while ($tokenized_string !== false) {

        $first_chars      .= $tokenized_string[0];
        $tokenized_string = strtok($delimiter);
    }

    return $first_chars;

}

/**
 * This will transform the string to proper case
 * @param  string $string 
 * @return string
 */
function custom_ucwords($string) {

    return ucwords(strtolower($string));
    
}

/**
 * This will help us encrypt passwords
 * @param  string $password 
 * @param  string $salt    
 * @return string
 */
function encrypt_password($password, $salt) {

    return MD5($password.$salt);

}

/**
 * This will json_encode non string
 * @param  mixed   value
 * @return string
 */
function to_string($value) {

    if (!is_string($value)) {
        
        return (string)json_encode($value);

    } else {

        return $value;

    }

}

/**
 * This will check if string ends with certain characters
 * @param  string $string
 * @param  string $needle
 * @return boolean
 */
function str_contains_last($string, $needle) {

    $length = strlen($needle);

    if ($length == 0) {
        return true;
    }

    return (substr($string, -$length) === $needle);
}

/**
 * This will remove list of array keys from array
 * @param  array $array 
 * @param  array $keys  
 * @return array
 */
function array_remove_keys(&$array, $keys) {

    return $array = array_diff_key($array,array_flip($keys));

}

/**
 * This will filter array by passing callable 2nd argument and return the key
 * @param  array    &$array   
 * @param  callable $filter 
 * @return mixed
 */
function array_where_first(&$array, callable $filter, $is_key_only = false) {

    $filtered_array = array_filter($array, $filter, ARRAY_FILTER_USE_BOTH);
    
    if (is_array($filtered_array) && count($filtered_array) > 0) {

        $first_key = array_keys($filtered_array)[0];

        if ($is_key_only) {

            return  $first_key;

        } else {
            
            return array('key' => $first_key, 'value' => $filtered_array[$first_key]);

        }

    } else {

        if ($is_key_only) {

            return  null;

        } else {
            
            return array('key' => null, 'value' => null);

        }

    }
}

/**
 * This will check if given date is on weekly schedule
 * @param  array  $schedules [startDay, startTime, endDay, endTime]
 *                           startDay/endDay   = from 0(Sunday) - 6(Saturday)
 *                           startTime/endTime = H:i:s 24 hour format
 * @param  string $date      Optional(Default = today), Date to be checked Y-m-d H:i:s
 * @return boolean
 */
function on_weekly_schedule($schedules, $date = false) {
    if ($date === false) {

        $date = date('Y-m-d H:i:s');

    }

    $date_week_day = custom_date_format('w', $date);
    $date_time     = custom_date_format('H:i:s', $date);

    // check first if startDay is not empty
    $schedules['startTime'] = custom_date_format('H:i:s', $schedules['startTime']);
    $schedules['endTime']   = custom_date_format('H:i:s', $schedules['endTime']);

    // check if endDay is next week or startDay is last week
    if ($schedules['startDay'] > $schedules['endDay']) {

        // adjust endDay +7 to next week, because start day already passed
        if ($date_week_day > $schedules['startDay']) {

            $schedules['endDay']+=7;

        }

        // adjust startDay to last week because it did not passed yet
        if ($date_week_day < $schedules['startDay']) {

            $schedules['startDay']-=7;

        }

    }

    // only if current day is between or equal 2 offline days
    if ($schedules['startDay'] <= $date_week_day && $date_week_day <= $schedules['endDay']) {

        // bank offline schedule will start and end today
        if($date_week_day == $schedules['startDay'] && $date_week_day == $schedules['endDay']) {

            // we need to detect the time if its between offline time already
            if($date_time >= $schedules['startTime'] && $date_time <= $schedules['endTime']) {

                return true;

            }

        }

        // if offline sched started but will not end yet today
        if ($schedules['startDay'] == $date_week_day && $schedules['endDay'] != $date_week_day) {

            // we need to check if offline time started already
            if ($date_time >= $schedules['startTime']) {

                return true;

            }

        }

        // today is between the two offline day, no need to detect time
        if ($date_week_day != $schedules['startDay'] && $date_week_day != $schedules['endDay']) {

            return true;

        }


        // bank offline sched ends today
        if ($schedules['endDay'] == $date_week_day && $schedules['startDay'] != $date_week_day) {

            // detect if the time to offline did not end yet
            if ($date_time <= $schedules['endTime']) {

                return true;

            }

        }
    }

    return false;
}

/**
 * This will add prefix to all values of array
 * @param  array  &$array 
 * @param  string $prefix 
 * @return array
 */
function array_prefix(&$array, $prefix) {

    return $array = preg_filter('/^/', $prefix, $array);

}

/**
 * This will add sufix to all values of array
 * @param  array  &$array 
 * @param  string $sufix 
 * @return array
 */
function array_sufix(&$array, $sufix) {

    return $array = preg_filter('/$/', $sufix, $array);

}

/**
 * Get the first key of the array
 * @param  array  &$array 
 * @return array
 */
function array_first_key($array) {

    return array_keys($array)[0];

}

/**
 * This will concat array index to its value
 * @param  array  $array     [description]
 * @param  string $delimeter [description]
 * @return array
 */
function array_parameterize($array, $delimeter = ' as ') {

    foreach ($array as $key => &$value) {
        
        if (is_string($key)) {
            
            $value=$key.$delimeter.$value;

        }

    }

    return $array;
}

/**
 * This will insert value to array at certain position
 * NOT reliable
 * @param  array  &$array   
 * @param  int    $position starts at 0
 * @param  mixed  $value    
 * @return array
 */
function array_insert(&$array, $position, $value){

    // slice into 2
    $first_half = array_slice($array, 0, $position);
    
    if (is_array($value)) {     

        assoc_array_merge($first_half, $value);

    } else {

        array_push($first_half, $value);

    }

    $second_half = array_slice($array, $position);

    // combine again with the new item being inserted
    return $array = assoc_array_merge($first_half, $second_half);

}

/**
 * This will check if all  required keys are existing in array
 * @param  array  $array         
 * @param  array  $required_keys 
 * @return boolean
 */
function array_contains_all($array, $required_keys) {

    return !array_diff_key(array_flip($required_keys), $array);

}

/**
 * This will move the value of an array to another position
 * @param  array  &$array   
 * @param  mixed  &$value    
 * @param  int    $position starts at 0
 * @return array
 */
function array_move_value(&$array, &$value, $position) {

    array_insert($array, $value, $position);

    unset($value);

    return $array;

}

/**
 * Get what is the current environment
 * @return  string
 */
function get_host_env() {

    $gethostname = trim(gethostname());

    if ( preg_match("/^SD-\d{1,}$/", $gethostname) ) {

        return 'local';

    } elseif ( preg_match("/^.*([lab|uat]{3})/", $gethostname, $out) ) {

        if (count($out) && $out[1] == "lab") {

            return 'staging';

        } elseif (count($out) && $out[1] == "uat") {

            return 'uat';
        }

    } else {

        return 'production';
    }
}

/**
 * check if value has no symbol
 * @param  string $value 
 * @return bool        
 */
function no_symbol($value) {

    $value=str_replace(" ","",$value);
        
    return preg_match('/(?=.*[\`~!@#$%^&*=()\/\[\]\-_+{}|:;\'"<>\?\,]).*$/', $value) ? TRUE : FALSE ;

}
/**
 * check if string has no space
 * @param  string $value 
 * @return bool        
 */
function no_space($value) {
    
        return preg_match('/\s/', $value) ? FALSE : TRUE ;

}
/**
 * this will remove special character then remove all space
 * @param  string $string 
 * @return string         
 */
function alphanum_remove_space($string){

    return strtolower(remove_space(alphanum_only($string)));
}

/**
 * this will checkk if string has no integer value
 * @param  string $value 
 * @return bool        
 */
function no_number($value) {
    return preg_match('/\d.*$/', $value, $match) ? TRUE : FALSE ;

}

/**
 * This will check if value is valid json
 * @param   mix  $string   
 * @return  boolean            
 */
function is_json($value) {

    return (is_string($value) && is_array(json_decode($value, TRUE))) ? true : false;

}
/**
 * this will check if string is containing three (numbers letter and symbol)
 * @param  string  $value 
 * @return boolean
 */
function alpha_num_symbol($value) {

    return (preg_match('/\d+/', $value) && preg_match('/[A-Za-z]+/', $value) && preg_match('/[\W_]+/', $value, $match)) ? TRUE : FALSE;
    
}

/**
 * Check value if alphabet of any language
 * @param  string $value 
 * @return bool        
 */
function alpha_language($value)
{

    return preg_match('/^[A-z\p{Han} ]+$/u', $value) ?  TRUE : FALSE;

}