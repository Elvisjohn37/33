<?php

namespace Backend\repositories;

use DateTime;
use DateTimeZone;

/**
 * Consolidate and validate all setting 
 * for the application in DB
 */
class Rdbconfig extends Baserepository {
    
    public $models = array(
                    'Mwebtype',
                    'Mcurrency',
                    'Mplatformconfig',
                    'Mipblacklist',
                    'Mipwhitelist',
                    'Mserverconfiguration',
                    'Mpromotion',
                    'Mipblacklistcountry'
                );
    
    /**
     * Get and validate application settings
     * @param  string $webTypeName
     * @return boolean 
     */
    public function get_app_mode($webTypeName) 
    { 
        
        $webtype = $this->model('Mwebtype')->get_app_mode($webTypeName);

        if ($webtype) {
            
            if ($webtype->mode != 0 && $webtype->mode != 2) {

                return array('mode' => $webtype->mode, 'app_mode' => false);

            }

        }
        
        return array('mode' => $webtype->mode, 'app_mode' => true);

    }
    
    /**
     * set system time from DB
     * @param  string $date_time
     * @return string
     */
    public function set_system_time($date_time)
    {
        if (strtotime($date_time)) {
            
            $timezone = $this->model('Mplatformconfig')->get_system_time();

            $format   = 'Y-m-d H:i:s';
            $ndate    = new DateTime($date_time);
            $ndate->setTimezone(new DateTimeZone($timezone));
            return $ndate->format($format);
                
        } else { 
            
            return $date_time; 
        }
    }

    /**
     * This will lookup our DB if given IP is allowed
     * @param  string  $ip 
     * @return boolean     
     */
    public function is_ip_allowed($ip)
    {

        if ($this->is_ip_blacklisted($ip)) {
            
            return $this->is_ip_whitelisted($ip);

        } elseif ($this->ip_country_blacklisted($ip)) {
            
            return $this->is_ip_whitelisted($ip);

        } 

        return true;

    }

    /**
     * This will check if IP is blacklisted in DB
     * @param  string  $ip 
     * @return boolean     
     */
    public function is_ip_blacklisted($ip)
    {
        $blacklisted_ips = $this->model('Mipblacklist')->get_all()->toArray();

        return ip_range_list($ip, $blacklisted_ips);

    }

    /**
     * check if country IP is blacklisted
     * @param  string $ip 
     * @return boolean TRUE if IP is blacklisted, False if IP not blacklisted
     */
    public function ip_country_blacklisted($ip)
    {   
        $ip = unsigned_ip2long($ip);
        $country_ip_range = $this->model('Mipblacklistcountry')->country_ip_range($ip);

        return is_null($country_ip_range) ? false : true;


    }

    /**
     * This will check if IP is whitelisted in DB
     * @param  string  $ip 
     * @return boolean    
     */
    public function is_ip_whitelisted($ip)
    {

        $whitelisted_ips = $this->model('Mipwhitelist')->get_all()->toArray();

        return ip_range_list($ip, $whitelisted_ips);

    }    

    /**
     * Get webTypeID
     * @param  string $webTypeName
     * @return int 
     */
    public function get_webTypeID($webTypeName) 
    { 
        
        return $this->model('Mwebtype')->get_webTypeID($webTypeName);
        
    }

    /**
     * get url by serverID and configName
     * @param  string $serverID   
     * @param  string $configName 
     * @return string            
     */
    public function get_server_url($serverID, $configName)
    {
        
        $server_url = $this->model('Mserverconfiguration')->get_config_url($serverID, $configName);
        
        return is_null($server_url) ? ' ' : $server_url;
    }

    /**
     * check if a promotion is enabled
     * @param  string $promotionID   
     * @return string            
     */
    public function is_promotion_enabled($promotionID)
    {
        return $this->model('Mpromotion')->count_promotion_enabled($promotionID);
    }

    /**
     * get system time from DB 
     * @return string 
     */
    public function get_system_time()
    {

        return $this->model('Mplatformconfig')->get_system_time();
    }
    
}