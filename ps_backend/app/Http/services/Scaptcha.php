<?php

namespace Backend\services;

use Auth;
use File;
use Input;
use Config;


/**
* Captcha
*/
class Scaptcha extends Baseservice {

    private $validated_transaction_captcha = array();

    /**
     * check captcha if input and session are same
     * @param  string   $captcha_input 
     * @param  string   $field_name    name of captcha field, 
     *                                 use also to identify if validation error will toast or not
     * @param  function $captcha_input Callback once validation failed
     * @return array
     */
    public function check($captcha_input, $field_name = '' , $callback = null)
    {

        $captcha_session = $this->service('Ssession')->get('captcha_value');

        $this->get();
        $toast = TRUE;

        ($field_name == '') ? $field_name = 'captcha' : $toast = FALSE;

        return $this->service('Svalidate')->validate(array(

                $field_name => array(
                                'value' => array(
                                            'captcha_input'   => $captcha_input,
                                            'captcha_session' => $captcha_session,
                                        ),
                                'validator' => 'captcha',
                                'callback'  => $callback
                            )

            ), $toast);
    }

    /**
	 * get captcha
	 * @return array 
	 */
	public function get()
	{

        $captha_assets_path = $this->service('Ssiteconfig')->self_hosted_asset('images/captcha/');
        $fontname = $this->service('Ssiteconfig')->self_hosted_asset('fonts/Oxygen-Regular.ttf');
        
        //string
        $captcha  = str_random(6);
        
        if (Auth::check()) {

            $username = Auth::user()->loginName.str_random(6);

        } else {

            $username = $this->service('Ssession')->get('guestName').str_random(6);

        }

        $file     =  "{$captha_assets_path}{$username}.jpg";
        $filePath =  $this->service('Ssiteconfig')->self_hosted_asset("images/captcha/{$username}.jpg", true);

        // Path of the base image of CAPTCHA. This is for preset background and border
        $image    = imagecreatefromjpeg($captha_assets_path.'c_base.jpg');

        //captcha text color
        $captcha_color = imagecolorallocate($image, 55, 189, 102);

        // Image quality
        $quality = 90;
        // Vertical start position of text
        $y = 40;
        // Center the text inside the image
        $image_width = 185;
        $dimensions  = imagettfbbox(26, 0, $fontname, $captcha);
        $x = ceil(($image_width - $dimensions[4]) / 2);

        // Create text inside the CAPTCHA image
        imagettftext($image, 26, 0, $x, $y, $captcha_color, $fontname,$captcha);
        // Create the CAPTCHA image.
        imagejpeg($image, $file, $quality);

        File::delete($this->service('Ssession')->get('captcha'));
        $encryptedValue='';

        //put captcha session
        $this->service('Ssession')->put_captcha(array(
            'captcha'           => $file,
            'captcha_encrypted' => $encryptedValue,
            'captcha_value'     => $captcha,
            'captcha_path'      => $filePath
        ));

        $sessionID = $this->service('Ssession')->sessionID();

        $this->service('Ssocket')->push(array(
            'session_id' => $sessionID, 
            'event'      => 'UPDATE_CAPTCHA', 
            'message'    => array('image' => $filePath, 'encryptedValue' => $encryptedValue)
        ));
   
        return array('path' => $filePath, 'value' => $encryptedValue);
    
	}

    /**
     * Process transaction request and required captcha if needed 
     * Session of captcha not yet save
     * @param  string $captcha_field       
     * @param  string $transaction_session 
     * @return int                      
     */
    public function transaction_captcha($captcha_field, $type)
    {
        $session_key       = "{$type}_captcha"; 
        $time_interval     = Config::get('settings.transactions.time_interval');
        $max_count         = Config::get('settings.transactions.max_count');
        $request_session   = $this->service('Ssession')->get($session_key);

        if (empty($request_session['has_captcha'])) {
           
            $request_time = date('Y-m-d H:i:s');
            $a = substract_dates($request_time,$request_session['time'], 'Minutes');

            if ($a >= $time_interval || is_null($request_session['time'])) {

                $request_session['time']        = $request_time;
                $request_session['count']       = 1;
                $request_session['has_captcha'] = 0;

            } else {

                $request_session['count']++;

                if ($request_session['count'] == $max_count) {

                    $request_session['has_captcha'] = 1;

                }
            }
 
        } else {

            // if captcha field is present then validate normally,
            // else notify frontend that it needs capctha input
            if (Input::has($captcha_field)) {

                $this->check(Input::get($captcha_field), $captcha_field);

            } else {

                $this->check(Input::get($captcha_field), '', function(&$validation_result) {
                    $validation_result['has_captcha'] = 1;
                });
                
            }

            $request_session['has_captcha'] = 0;
            $request_session['count']       = 0;

        }

        $this->validated_transaction_captcha[$session_key] = $request_session;
        return $request_session['has_captcha'];

    }

    /**
     * save validated captcha after all validation passed
     * @return null
     */
    public function save_transaction_captcha()
    {
        
        foreach ($this->validated_transaction_captcha as $session_key => $session_value) {

            $this->service('Ssession')->put($session_key,$session_value);

        }

    }


}