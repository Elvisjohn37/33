<?php 

namespace Backend\services;

use Exception;
use Config;

/**
 * This will handle all PS server request :3
 * 
 * @author PS Team
 */
class Sserver extends Baseservice {

	/**
	 * Start a curl request
	 * @param  string $url               
	 * @param  array  $params           
	 * @param  array  $curl_setopt_array 
	 * @return mixed  response content or false if error
	 */
	public function curl($url, $params=array(), $curl_setopt_array =  array()) 
    {
        
        $ch = curl_init();

		// default options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::get('settings.curl_timeout'));

        // add/overwrite default
        if (is_array($curl_setopt_array) && count($curl_setopt_array)>0) {
        	
        	curl_setopt_array($ch, $curl_setopt_array);

        }
        
        $response      = curl_exec($ch);
        $error_number  = curl_errno($ch);
        $error_message = curl_error($ch);

        curl_close($ch);

        if ($error_number == 0) {
        
	        // parse response
	        $content = custom_json_decode($response, true);

	        if ($content === NULL) {
	        	
	        	$content = $response;

	        }

	        return $content;

        } else {

        	$this->service('Slogger')->file(

        		array(
	        		'url'           => $url,
	        		'error_number'  => $error_number,
	        		'error_message' => $error_message
        		),

        		'CURL_ERROR'

        	);

        	return false;

        }
	}

    /**
     * This will get file contents base on relative or absolute server url
     * @param  string  $path 
     * @param  boolean $use_include_path 
     * @param  array   $custom_options    customized options for get contents
     * @return mixed
     */
    public function file_get_contents($path, $use_include_path = false, $custom_options = array())
    {
        try {

            $stream_context_create = array(
                                        'ssl' => array(
                                                    'verify_peer'       =>  false,
                                                    'verify_peer_name'  =>  false
                                                )
                                    );

            assoc_array_merge($stream_context_create, $custom_options);

            $content = file_get_contents($path, $use_include_path, stream_context_create($stream_context_create));

        } catch(Exception $e) {

            $content = false;

            $this->service('Slogger')->file(

                array(
                    'path'          => $path,
                    'error_message' => $e->getMessage(),
                    'error_code'    => $e->getCode()
                ),

                'SERVER_ERROR'
            );

        }

        $this->service('Svalidate')->validate(array('firewall_response' =>  array('value' => $content)), true);

        return $content;

    }
}