<?php

namespace Backend\services;

use Session;
use Config;
use Exception;
use Request;
use Aes;
use Auth;
use layer;
use Cookie;

/**
 * All process that has something to do with Sessions
 * By wrapping it to service we can have additional conditions 
 * when getting and setting sessions
 * 
 * @author PS Team
 */
class Ssession extends Baseservice {

	/**
	 * Alias can be use to shorten the real name of session key 
	 * and minimize the session size somehow
	 * @var array
	 */
	private $alias = array();

	// sessions that will retain its values on login and logout
	private $auth_persistent = array('url_referrer', 'ps_token');

	// variable used in watching created sessions
	private $watch_session = false;
	private $watched_sessions;
	private $sent_message = array();

	/**
	 * This will hold old sessions after flush is called
	 * for restoring
	 * @var null
	 */
	private $old_sessions = null;

	public function __construct() 
	{

		// check if current route is under 'web' middleware 
		$is_session_start = $this->service('Ssiteconfig')->get_start_session();

		if (!$is_session_start) {

			throw new Exception("Route must be under 'web' middleware to use laravel Session manager");
			
		}

	}

	/**
	 * Get the real key being used in Session::
	 * this allows our app to enable to use both alias and real key
	 * @param  string $session_key 
	 * @return string
	 */
	private function get_real_key($session_key) 
	{

		if (array_key_exists($session_key , $this->alias)) {

			return $this->alias[$session_key];

		} else {
				
			return $session_key;

		}

	}

	/**
	 * Get session value via key
	 * @param  string $session_key 
	 * @return mixed
	 */
	public function get($session_key) 
	{

		$session_key = $this->get_real_key($session_key);

		if (Session::has($session_key)) {
			
			return Session::get($session_key);

		} else {

			return null;

		}

	}

	/**
	 * Set session value via key
	 * @param  string  $session_key   
	 * @param  mix  $session_value 
	 * @param  boolean $save_session  this will save session immediate
	 * 								  to prevent inconsistent session when rapid request occur 
	 * @return mixed
	 */
	public function put($session_key, $session_value, $save_session = false) 
	{
		$session_key = $this->get_real_key($session_key);

		if ($this->watch_session) {
			
			if (!is_array($this->watched_sessions)) {
				
				$this->watched_sessions = array();

			}

			$this->watched_sessions[] = $session_key;

		}

		$session = Session::put($session_key,$session_value);
		
		if ($save_session) {
			Session::save();
		}

		return $session;
	}

	/**
	 * This will record all sessions being created after this method call
	 * @return void
	 */
	public function start_watch()
	{
		$this->watch_session = true;
	}

	/**
	 * This will stop watching the session being created and return previously watched sessions
	 * @return array/null
	 */
	public function stop_watch()
	{
		$this->watch_session    = false;
		$watched_sessions 	    = $this->bulk_get($this->watched_sessions);
		$this->watched_sessions = null;
		return $watched_sessions;
	}

	/**
	 * Set session value by passing array
	 * @param  array $session_array 
	 * @return mixed
	 */
	public function bulk_put($session_array)
	{
		foreach ($session_array as $key => $session) {
			
			$this->put($key, $session);

		}

		Session::save();
	}

	/**
	 * Get session value by passing array
	 * @param  array $session_keys 
	 * @return mixed
	 */
	public function bulk_get($session_keys)
	{
		if (is_array($session_keys)) {

			$sessions = array();
			foreach ($session_keys as $key) {
				
				$sessions[$key] = $this->get($key);

			}

			return $sessions;

		} else {

			return null;

		}
	}

	/**
	 * Forget session
	 * @param  string $session_key 
	 * @return mixed
	 */
	public function forget($session_key)
	{
		
		$session_key = $this->get_real_key($session_key);
		
		return Session::forget($session_key);

	}

	/**
	 * retrieve and delete session
	 * @param  	string $session_key 
	 * @return 	mixed              
	 */
	public function pull($session_key)
	{

		$session_key = $this->get_real_key($session_key);

		return Session::pull($session_key);

	}

	/**
	 * flush all session but it wil be stored in local variable for revert
	 * @param   array  $except  List of session key to be restored right after flush
	 * @return 	mixed              
	 */
	public function flush($except = array())
	{
		$this->old_sessions = Session::all();
		$flush = Session::flush();

		// restore excempted
		if (is_array($except) && count($except) > 0) {
			
			$restore = array_only($this->old_sessions, $except);

			if (count($restore) > 0) {

				$this->bulk_put($restore);
				
			}

		}

		return Session::all();
	}

	/**
	 * This will flush current session and will restore value of last flushed session
	 * @return 	mixed              
	 */
	public function revert()
	{
		$old_sessions = $this->old_sessions;
		$this->flush();

		if ($old_sessions != null) {

			$this->bulk_put($old_sessions);

		}
	}

	/**
	 * set session for captcha
	 * @param  array $captcha_session  key and value for session
	 * @return void                  
	 */
	public function put_captcha($captcha_session)
	{

		foreach ($captcha_session as $session_key => $session_value) {

			$this->put($session_key, $session_value);

		}

	}

    /**
     * get referrer from session
     * 
     * @return type
     */
    public function get_referrer()
    {
        $referrer_url = $this->get('url_referrer');
        
        return is_null($referrer_url) ? Request::server('HTTP_HOST') : $referrer_url;
    }

    /**
     * set referrer to session
     * @return void
     */
    public function set_referrer()
    {
		$referrer = referrer_url();

		if (!$this->get('url_referrer') || $referrer['is_host'] == false) {

        	$this->put('url_referrer', $referrer['referrer']);

		}
    }

    /**
     * This will increase gameID_open saved in session by 1
     * @param  int     $gameID
     * @param  boolean $is_increase
     * @return int
     */
    public function gameID_open($gameID, $is_increase = true)
    {

    	$session_key    = $gameID.'_open';
    	$current_count  = $this->get($session_key);

    	if (!$current_count || $current_count < 0) {
    		
    		$current_count = 0;

    	}
    	
		if ($is_increase) {
			
			$current_count+=1;

		} else {

			// 0 is the minimum count
			if ($current_count > 0) {

				$current_count-=1;

			}

		}

    	$this->put($session_key,$current_count, TRUE);

    	return $current_count;
    }

	/**
	 * Set session of client for reset_password
	 * @param  array $client client details that we will store in session
	 * @return void
	 */
	public function put_lost_password($client)
	{
		$this->put('lostPasswordCode', $client['lostPasswordCode']);
		$this->put('reset_clientID',   $client['clientID']);
		$this->put('username', 	       $client['username']);
		$this->put('loginName',        $client['loginName']);
		$this->put('firstName',        $client['firstName']);
		$this->put('lastName',         $client['lastName']);
		$this->put('password',         $client['password']);

		Session::save();
	}

    /**
     * This will get all lost password sessions
     * @return array
     */
    public function get_lost_password()
    {
        return  array(
        	'lostPasswordCode'  => $this->get('lostPasswordCode'),
        	'clientID' 			=> $this->get('reset_clientID'),
        	'username'          => $this->get('username'),
        	'loginName'         => $this->get('loginName'),
        	'firstName'         => $this->get('firstName'),
        	'lastName'          => $this->get('lastName'),
        	'password'          => $this->get('password')
        );
    }

    /**
     * This will forget all lost password sessions
     * @return void
     */
    public function forget_lost_password()
    {
        $this->forget('lostPasswordCode');
        $this->forget('reset_clientID');
        $this->forget('username');
        $this->forget('loginName');
        $this->forget('firstName');
        $this->forget('lastName');
        $this->forget('password');
    }

    /**
     * This will get current login attempt sessions
     * has_captcha & login_attempt
     * @return array
     */
    public function get_login_attempts()
    {
    	$has_captcha   = $this->get('has_captcha');
    	$login_attempt = $this->get('login_attempt');

    	if (!$login_attempt) {

    		$login_attempt = 0;

    	}

    	return compact('has_captcha','login_attempt');
    }

    /**
     * This will add current login attempts
     * has_captcha & login_attempt
     * @return array
     */
    public function add_login_attempts()
    {

    	$prev_login_attempts = $this->get_login_attempts();

    	$login_attempt = ++$prev_login_attempts['login_attempt'];
    	$this->put('login_attempt', $login_attempt);

    	$has_captcha  = ($login_attempt >= Config::get('settings.user.max_login_attempt'));
    	$this->put('has_captcha',  $has_captcha);

    	return compact('has_captcha', 'login_attempt');
    }

    /**
     * This will forget all login attempts session
     * @return void
     */
    public function forget_login_attempts()
    {
        $this->forget('has_captcha');
        $this->forget('login_attempt');
    }

    /**
     * This will set proper sessions for login players
     * @param array $client 
     * @return void
     */
    public function set_login_sessions($client)
    {	
    	// generate sessionID first 
    	$sessionID = $this->repository('Rplayer')->generate_sessionID(Config::get('settings.user.sessionID_max_try'));

    	$this->service('Svalidate')->validate(array(

            'truthy' => array(
                            'value' => array(
                                        'input' => $sessionID,
                                        'type'  => 'sessionID_generation'
                                    )
                        )

        ) ,true);

    	// remove all sessions
    	$this->flush($this->auth_persistent);

		$this->put('SESSION_ID',$sessionID, TRUE);
    	$this->put('encrypted_clientID', $this->service('Scrypt')->aes_encrypt($client['clientID'], true));
    	$this->put('clientID', $client['clientID']);
    	$this->put('parentID', $client['parentID']);
    	$this->put('checkPassReset', $client['isPasswordReset']);

        Auth::loginUsingId($client['clientID']);

        return $sessionID;
    }

    /**
     * This will set proper sessions when logging out
     * @param  int/array $clientIDs single or list of clientIDs 
     * @return void
     */
    public function set_logout_sessions($clientIDs)
    {
    
    	if (!is_array($clientIDs)) {

    		$clientIDs = array($clientIDs);

    	}

    	if (Auth::check() && in_array(Auth::user()->clientID, $clientIDs)) {

    		Auth::logout();
    		$this->flush($this->auth_persistent);
           
    	}

    }

    /**
     * This will create guests sessions
     * @return void
     */
    public function guest_sessions()
    {	
    	if (!$this->get('TSESSION_ID')) {

			// get the assigned parent
			$parent = $this->service('Ssiteconfig')->get_site_agent();

			if ($parent) {

				$this->put('parentID',        $parent['clientID']);
				$this->put('parent_username', $parent['username']);

			}

			// create guests session with details, if chatbox is native and we already have assigned agent 
			if ($this->service('Ssiteconfig')->is_native_chatbox() && $parent) {

				$guest = $this->repository('Rguest')->create(
							$parent['clientID'],
							Config::get('settings.guest.create_max_try')
						);

				$guest_sessionID = $guest['sessionID'];
				$guest_name      = $guest['name'];


			} else {

				$guest_sessionID = $this->repository('Rguest')
										->generate_sessionID(Config::get('settings.guest.create_max_try'));

				$guest_name		 = $this->repository('Rguest')
										->generate_name(Config::get('settings.guest.create_max_try'));

			}

			$this->put('TSESSION_ID', $guest_sessionID);
			$this->put('guestName',   $guest_name);

	    	// set the URL referrer
			$this->set_referrer();
		} 

		return $this->bulk_get(array('parentID','parent_username','TSESSION_ID','guestName','url_referrer'));
    }

    /**
     * This will get guest sessionID
     * @return void
     */
    public function guest_sessionID()
    {	
    	return $this->get('TSESSION_ID');
    }

    /**
     * This will validate and create new csrf token
     * @param  string $csrf 
     * @return void
     */
    public function validate_csrf($csrf) 
    {
    	
        $decrypted_token = $this->service('Scrypt')->aes_decrypt($csrf, 2, TRUE);
		$token_key 		 = $decrypted_token['id'];

    	$current_csrf	 = $this->get("multiple_token.{$token_key}");
    	$encrypted_token = $this->generate_csrf($token_key);

    	try {
    		
		$this->service('Svalidate')->validate(array(

    		'csrf' => array(
	    			'value' => array(
				    			'input'   => $decrypted_token['value'],
				    			'session' => $current_csrf['token']
				    		)
			    	)

    	), true);	

    	} catch (Exception $e) {    		
    		return array('error' => $e->getMessage(), 'token'=> $encrypted_token);
    	}    	

	return array('token'=>$encrypted_token);

    }

    /**
     * This will generate new csrf
     * @param  int $token_key index of token
     * @return string            
     */
    public function generate_csrf($token_key = null)
    {
    	$token         = str_random(40);
    	$last_update   = date('Y-m-d H:i:s');

    	if (is_null($token_key)) {

	    	$token_key = random_number(3);

	    	$this->put(
	    		"multiple_token.{$token_key}",
	    		array('token' => $token, 'last_update' => $last_update),
	    		TRUE
	    	);
	    	$this->delete_expired_token();

    	} else {

	    	$this->put(
	    		"multiple_token.{$token_key}",
	    		array('token' => $token, 'last_update' => $last_update),
	    		TRUE
	    	);

    	}
    	$encrypted_token = $this->service('Scrypt')->aes_encrypt(http_build_query(array(
    		'id' => $token_key,
    		'value' => $token
    	)), TRUE);

    	return $encrypted_token;
    }

    /**
     * This will generate new csrf
     * @return void
     */
    public function get_csrf()
    {	
    	$csrf = $this->get('ps_token');

    	if (empty($csrf)) {

    		return $this->generate_csrf('ps_token');

    	} else {

    		return $csrf;

    	}
    }

	/**
	 * This will get current chat sender username from session
	 * @return string 
	 */
	public function chat_sender()
	{
		if (Auth::check()) {
			return Auth::user()->username;
		} else {
			return $this->get('guestName');
		}
	}

	/**
	 * This will get current chat sender username from session
	 * @return string 
	 */
	public function chat_receiver()
	{
		if (Auth::check()) {
			return Auth::user()->parent_username;
		} else {
			return $this->get('parent_username');
		}
	}

	/**
	 * This will get sessionID to be used depends if user is authenticated or not
	 * @return string
	 */
	public function sessionID() 
	{
		if (Auth::check()) {
			return Auth::user()->sessionID;
		} else {
			return $this->get('TSESSION_ID');
		}
	}

	/**
	 * This will get all flashed session and delete it
	 * @return array  [error, success]
	 */
	public function get_flash() 
	{
		$notifications = $this->pull('flash_sessions');
		
		return is_array($notifications) ? $notifications : array();
	}

	/**
	 * This is different from flashing session which creates session for next request
	 * This methods creates session and will be available until it's was fetch via get_flash
	 * @return array  [error, success]
	 */
	public function add_flash($content, $type = 'error') 
	{
		$notifications = $this->get('flash_sessions');

		if (!is_array($notifications)) {
			$notifications = array();
		}

		$notifications[$type] = $content;

		$this->put('flash_sessions', $notifications, TRUE);
	}

	/**
	 * update lastActivity of player
	 * @param  int $clientID 
	 * @return null
	 */
	public function update_lastActivity($clientID)
	{

		$this->repository('Rplayer')->update_lastActivity($clientID);
		
	}

	/**
	 * We will use this game window key for all game window session
	 * @param  array $game 
	 * @return string
	 */
	public function game_window_key($game)
	{
		if ($game['isMultipleInstance']) {

			return 'ps_gamewindow_'.$this->service('Ssiteconfig')
										->gameName_unique_key($game['gameID'],$game['gameName']);

		} else {
			return 'ps_gamewindow_'.$this->service('Ssiteconfig')
										->productName_formatter($game['productName']); 
		}
	}

	/**
	 * This will get game window is used key
	 * @param  string $game_window_key [description]
	 * @param  string $token           [description]
	 * @return string
	 */
	public function game_key_used($game_window_key, $token) 
	{
		return $game_window_key.$token.'is_used';
	}

	/**
	 * This will create game window session
	 * @param  array  $session_data  [game, token, game_url]
	 * @param  string $platform
	 * @return string                Game Window URL
	 */
	public function game_window_session($session_data, $platform) 
	{
		$game_window_key = $this->game_window_key($session_data['game'], $platform);

		// close first existing window via WS
		$game_session = $this->get($game_window_key);
		if (is_array($game_session)) {
			$this->service('Ssocket')->push_game(
				$this->service('Ssession')->sessionID(), 
				'ps_game_close', 
				$game_session['token']
			);
		}

		// create new session for game window
		if (in_array($session_data['game']['serverID'], Config::get('settings.games.ps_window_serverID'))) {
			
			$this->put($game_window_key, array(
				'token'    => $session_data['token'],
				'game_url' => $session_data['game_url'],
				'gameID'   => $session_data['game']['gameID'],
				'ws_topic' => 'ps_gw_'.generate_token(16)
			));

			$this->put($this->game_key_used($game_window_key,$session_data['token']), false);

			// return url_add_query(
			// 	\URL::to('/game_window'),
			// 	array(
			// 		'payload'  => $this->service('Scrypt')->aes_encrypt(http_build_query(array(
			// 						'token'      => $session_data['token'],
			// 						'gameID'     => $session_data['game']['gameID'],
			// 						'window_key' => $game_window_key
			// 					)), true)
			// 	)
			// );

			return url_add_query(
				$this->service('Ssiteconfig')->get_site_domains()[$platform].'/game_window',
				array(
					'payload'  => $this->service('Scrypt')->aes_encrypt(http_build_query(array(
									'token'      => $session_data['token'],
									'gameID'     => $session_data['game']['gameID'],
									'window_key' => $game_window_key
								)), true)
				)
			);

		} else {

			return $session_data['game_url'];

		}

	}

	/**
	 * This will get game session via token
	 * @param  string $token 
	 * @return array
	 */
	public function validated_game_session($payload)
	{	
		$result = array();

		/*
		|---------------------------------------------------------------------------------------------------------------
		| decrypt game payload and user session
		|---------------------------------------------------------------------------------------------------------------
		 */
		try {

			$decrypted_game_payload = $this->service('Scrypt')->aes_decrypt($payload, 3, true);

		} catch(Exception $e) {

			$decrypted_game_payload = false;

		}

		try { 

	        $this->service('Svalidate')->validate(array(
	            'game_payload' => array(
	                        		'value' => $decrypted_game_payload
	                    		)

	        ), TRUE);

	    } catch(Exception $e) {

			return json_decode($e->getMessage(), true);

		}

		/*
		|---------------------------------------------------------------------------------------------------------------
		| get and validate game session
		|---------------------------------------------------------------------------------------------------------------
		 */
		$game_session = $this->get($decrypted_game_payload['window_key']);
		$is_used_key  = $this->game_key_used(
							$decrypted_game_payload['window_key'],
							$decrypted_game_payload['token']
						);
		try {

	        $this->service('Svalidate')->validate(array(

	            'game_session' => array(
	                        		'value' => array(
	                        					'payload' => $decrypted_game_payload,
	                        					'session' => $game_session,
	                        					'is_used' => $this->get($is_used_key)
	                        				)
	                    		)

	        ), TRUE);

	    } catch(Exception $e) {

			return json_decode($e->getMessage(), true);

		}

		// game session used
		if (Session::has($is_used_key)) {
			Session::forget($is_used_key);
		}

		/*
		|---------------------------------------------------------------------------------------------------------------
		| get and validate game data
		|---------------------------------------------------------------------------------------------------------------
		 */
		$game_data=$this->repository('Rproducts')->game_window_data($game_session['gameID'], Auth::user()->currencyID);

		try {

	        $this->service('Svalidate')->validate(array(
	            
	            'game'           => array(
	                                    'value'     => array('input' => $game_data), 
	                                    'validator' => 'truthy'
	                                ),

	            'product_access' => array(
	                                    'value'     => array(
	                                                    'auth_user'  => Auth::user(),
	                                                    'productID'  => $game_data['productID'],

	                                                    // we also got the product detail in $game variable
	                                                    'product'    => $game_data
	                                                )
	                                ),
	            
	            'game_access'    => array(
	                                    'value'     => array(
	                                                    'auth_user'        => Auth::user(),
	                                                    'encrypted_gameID' => $this->service('Scrypt')
	                                                    						->crypt_encrypt($game_data),
	                                                    'gameID'           => $game_data['gameID'],
	                                                    'game'             => $game_data
	                                                )
	                                )
	            
	        ), TRUE); 

	    } catch(Exception $e) {

			return json_decode($e->getMessage(), true);

		}

		return array(
			'result'      => true,
			'URL'         => $game_session['game_url'],
			'ws_topic'    => $game_session['ws_topic'],
			'token'       => $game_session['token'],
			'game_data'   => $game_data
		);
	}

	/**
	 * This count the sent message for specific time and save to session
	 * @return  bool
	 */
    public function check_sent_message()
    {
        $max_sent        = Config::get('settings.chat.max_per_send');
        $time_span       = Config::get('settings.chat.time_span');
        $delay_time      = Config::get('settings.chat.delay_time');
        $message_session = $this->service('Ssession')->get('sent_message'); 

        if (empty($message_session['block_sending'])) {
            $sent_time  = date('Y-m-d H:i:s');
            $remaining  = substract_dates($sent_time, $message_session['time'], 'Seconds');

            if ($remaining >= $time_span || is_null($message_session['time'])) {

                $message_session['time']          = $sent_time;
                $message_session['count']         = 1;
                $message_session['block_sending'] = false;

            } else {
                $message_session['count']++;

                if($message_session['count'] == $max_sent) {
                    $message_session['block_sending'] = true;
                    $message_session['open_sending']  = date('Y-m-d H:i:s', time() + $delay_time);
                }
            }

        } else { 

            $is_open = compare_dates($message_session['open_sending'],date('Y-m-d H:i:s'), 'lt');
        

            $this->service('Svalidate')->validate(array(
                'message_open' => array(
                                        'value'     => array(
                                                        'input' => $is_open,
                                                        'type'  => 'send_message'
                                                    ),
                                        'validator' => 'truthy',
                                        'callback'  => function(&$error) use ($message_session){
                                        				$remaining = substract_dates(
                                        					date('Y-m-d H:i:s'),
                                        					$message_session['open_sending'],
                                        					'Seconds');
		                                                $error['remaining'] = $remaining;
                                        }
                                    )
            ), true);

            $message_session['count'] = 0;
            $message_session['block_sending'] = false;

        }


        $this->sent_message = $message_session;
        return $message_session['block_sending'];
    }
    /**
     * save session of sent message after process og sending message
     * @return  void
     */
    public function save_sent_message()
    {	

        $this->service('Ssession')->put('sent_message',$this->sent_message);

    }
    /**
     * delete expired token if last update exceed to lifespan
     * @return null
     */
    public function delete_expired_token()
    {
    	$token_lifespan = Config::get('session.token_lifespan');
    	$multiple_token = $this->get('multiple_token');

		foreach ($multiple_token as $key => $token_value) {

			$last_update = previous_date(date('Y-m-d H:i:s'), $token_lifespan, 'Minutes');

			if (compare_dates($last_update, $token_value['last_update'], 'gte')) {

				Session::forget("multiple_token.{$key}");

			}

		}

    }
}