<?php 

namespace Backend\services;

use Backend\exceptions\Svalidateexception;
use Exception;
use Config;


/**
 * This will handle all PS web socket transactions
 * 
 * @author PS Team
 */
class Ssocket extends Baseservice {

	/**
	 * Web socket operations
	 * @param  array  $custom_params   
	 * @return mixed                 boolean or curl response
	 */
	private function send($custom_params,$reason = '') 
	{	
		
		if (!empty($custom_params['event'])) {
			
			$params = array(
						'namespace' => Config::get('settings.WS_NAMESPACE'),
						'message'   => '',
						'room'      => 'all'
					);

			// merge custom params
			assoc_array_merge($params, $custom_params);

			// curl to socket server
			$curl_reponse = $this->service('Sserver')->curl(

				Config::get('settings.WS_HOST').'/send',

				$params, 

				array (
					CURLOPT_HTTPHEADER => array(
											'X-Request-Origin: '.$_SERVER['HTTP_HOST'],
											'X-Client-IP: '.get_ip()
										)
				)

			);

			try {
				
				$this->service('Svalidate')->validate(array(
					'ws_send' => array(
									'value' => array(
										'input' => $curl_reponse['success']
									),
									'validator' => 'truthy',
									'callback'  => function() use ($curl_reponse, $params){
										$this->service('Slogger')->file(
																		array(
																			'ws_validation' => $curl_reponse,
																			'send_params'   => $params
																		),
																		'SOCKET_ERROR'
																	);
									}
								)
					),true);

			} catch (Exception $e) {
				if ($reason == 'login') {
					return array(
						'success'  => true,
						'message' => 'Error socket connection'
					);
				}

				throw new Svalidateexception(json_decode($e->getMessage(), true));
			}
				
				return $curl_reponse;

		} else {

			throw new Exception("Ssocket::send expecting argument 1 to be array with required 'event'");

		}

	}

	/**
	 * Send to websocket using list of sessionIDs
	 * @param  mixed  $sessionIDs string or array
	 * @param  array  $params [room, event,message]  
	 * @return mixed
	 */
	public function push($params, $reason = '') 
	{	

		if (!empty($params['session_id'])) {

			// convert string sessionID to array
			if (!is_array($params['session_id'])) {

				$params['session_id'] = array($params['session_id']);

			}

			$ws_send = $this->send($params, $reason);

			if ($ws_send['success']) {
				$ws_send['ws_message'] = $ws_send['message'];
				unset($ws_send['message']);
			}

			return $ws_send;
		}
	}

	/**
	 * Send per WL or to all player site  
	 * @param  string $event 
	 * @param  mixed  $message 
	 * @param  string $room   optional(default = settings.websocket.global_topic)
	 * @return mixed
	 */
	public function broadcast($event, $message, $room = '') 
	{
	
		if (trim($room) == '') {
			$room = Config::get('settings.websocket.global_topic');
		}

		return $this->send(array(
			'room' 	  	      => $room,
			'event'			  => $event,
			'message'	 	  => $message

		));

	}

	/**
	 * validate wesbsocket and send request  
	 * @param  array $data
	 * @return array
	 */
	public function validation($params)
	{

		if (!empty($params['rooms'])) {

			$ws_validation = $this->service('Sserver')->curl(

				Config::get('settings.WS_HOST').'/validate', 

				$params, 

				array (
					CURLOPT_HTTPHEADER => array(
											'X-Request-Origin: '.$_SERVER['HTTP_HOST'],
											'X-Client-IP: '.get_ip()
										)
				)

			);

			$this->service('Svalidate')->validate(array(
					'ws_validation' => array(
											'value' => array(
												'input' => $ws_validation['success']
											),
											'validator' => 'truthy',
											'callback'  => function() use ($ws_validation, $params) {

												$this->service('Slogger')->file(
																	array(
																		'ws_validation' => $ws_validation,
																		'send_params'   => $params
																	),
																	'SOCKET_ERROR'
																);
											}
										)
					),true);

			$ws_validation['ws_message'] = $ws_validation['message'];
			unset($ws_validation['message']);
			return $ws_validation;

		} else {

			throw new Exception("Ssocket::validate expecting argument 1 to be array with required 'rooms'.");

		}
	
	}

	/**
	 * Send to websocket ps_main_window tpic using list of sessionIDs
	 * @param  mixed  $sessionIDs string or array
	 * @param  string $event   
	 * @param  string $message    
	 * @return mixed
	 */
	public function push_main($sessionIDs, $custom_params, $reason = '') 
	{
		if (!empty($sessionIDs)) {

			// convert string sessionID to array
			if (!is_array($sessionIDs)) {

				$sessionIDs = array($sessionIDs);

			}
			
			$params = array(
				'room' => 'ps_main_window',
				'session_id' => $sessionIDs
			);

			assoc_array_merge($params, $custom_params);
			
			return $this->send($params, $reason);

		}
	}

	/**
	 * Send to websocket ps_game_window tpic using list of sessionIDs
	 * @param  mixed  $sessionIDs string or array
	 * @param  string $event   
	 * @param  string $message    
	 * @return mixed
	 */
	public function push_game($sessionIDs, $event, $message='') 
	{
		if (!empty($sessionIDs)) {

			// convert string sessionID to array
			if (!is_array($sessionIDs)) {

				$sessionIDs = array($sessionIDs);

			}

			return $this->send(array(
				'room' 	  	 => 'ps_game_window',
				'session_id' => $sessionIDs,
				'event'   	 => $event,
				'message'	 => $message
			));

		}
	}

	/**
	 * This will send PS_lo for bulk logout of client
	 * @param  array $clients 
	 * @return           void
	 */
	public function send_PS_lo($clients, $reason = '')
	{	

		$sessionIDs_by_parentIDs = array();
		foreach ($clients as $client) {

			if (isset($sessionIDs_by_parentIDs[$client['parentID']])) {
				
				$this->send(array(
					'session_id' => $sessionIDs_by_parentIDs[$client['parentID']],
					'room'       => 'agent_site',
					'event'      => 'PS_lo',
					'message'    => array('username' => $client['username'])
				), $reason);

			} else {

				$sessionIDs_by_parentIDs[$client['parentID']] = $this->repository('Rplayer')->get_agent_sessionIDs(
																								$client['parentID']
																							);
				$this->send(array(
					'session_id' => $sessionIDs_by_parentIDs[$client['parentID']],
					'room'       => 'agent_site',
					'event'      => 'PS_lo',
					'message'    => array('username' => $client['username'])
				), $reason);
			}
		}
		        
	}
}