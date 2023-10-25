<?php

namespace Backend\services;

use Config;
use Auth;
use Exception;
use Layer;

/**
 * This will help us get and set items in our cache
 * 
 * @author PS Team
 */
class Sapi extends Baseservice {

	private $log_file = 'ps_api';

	/**
	 * This will curl to API
	 * @param  string $process_name process we're calling on API
	 * @param  mixed  $params      
	 * @return array
	 */
	public function request($process_name, $params)
	{
		$url 			  = Config::get('settings.API_URL').$process_name;
		$params_formatted = array('param' => json_encode($params));
		$api_curl		  = $this->service('Sserver')->curl($url,$params_formatted);

		// validate API response
		$this->service('Svalidate')->validate(array(
			'api_response' => array(

								'value'    => $api_curl, 

								'callback' => function($error) use($process_name, $url, $params_formatted, $api_curl) {

												$this->service('Slogger')->file(

										    		array(
										        		'request'  => array(
													        			'url'    => $url,
													        			'params' => $params_formatted
													        		),
										                'response' => $api_curl
										    		),
										    		$process_name,
										    		$this->log_file

										    	);

											}
							)
		), true);

		return $api_curl;
	}

    /**
     * This will register player to companyID via API
     * FOR NOW THIS IS FOR SBO ONLY
     * @param  int    $clientID  
     * @param  int    $serverID
     * @return boolean
     */
    public function register_not_exist($clientID, $parentID, $serverID)
    {
		$companyID = $this->repository('Rproducts')->get_companyID($serverID);
    	
    	// validate if parent is registered
    	$this->service('Svalidate')->validate(array(
    		'company_registration' => array(
						    			'value'    => array('clientID' => $parentID, 'companyID' => $companyID),
						    			'callback' => function($error) {

									    				$this->service('Slogger')->file(
												    		array('error' => 'Agent not registered'),
												    		'registration',
												    		$this->log_file
												    	);

									    			}
						    		)
    	), true);

    	// register if not existing
    	$is_player_registered = $this->repository('Rproducts')->is_company_registered($companyID, $clientID);

        if ($is_player_registered == false) {
        	
	    	$this->service('Svalidate')->validate(array('serverID_access' => array('value' => $serverID)), true);
        	$this->request('register_player',array('client_id' => $clientID, 'server_id' => $serverID));

        }

        return array("result" => true);
    }

	/**
	 * This will get walletID existing balance via API to 3rd party company API
	 * This is used mostly for games that was developed by other companies
	 * @param  array/string  $walletIDs 
	 * @param  array         $clientIDs    
	 * @param  boolean       $is_encrypted   default = false
	 * @return array
	 */
	public function get_wallet_balance($walletIDs, $clientIDs, $is_encrypted = false)
	{
		if (!is_array($walletIDs)) {
			$walletIDs = array($walletIDs);
		}

		$balance = array();
		foreach ($walletIDs as $walletID) {

			if ($walletID != Config::get('settings.products.house_walletID')) {

				if ($is_encrypted) {
					$walletID =  $this->service('Scrypt')->crypt_decrypt($walletID);
				}

				$wallet_balance_information = $this->wallet_balance_information($walletID, $clientIDs, true );

				$balance[$wallet_balance_information['description']] = array(
					'amount'       => custom_money_format($wallet_balance_information['balance']),
					'is_connected' => $wallet_balance_information['is_connected'] 
				);

			} 
		}
		
		return array('result' => true, 'balance' => $balance);
	}

	/**
	 * This will get wallet balance and informatiomation of balance
	 * @param  int $walletID  
	 * @param  array $clientIDs 
	 * @param  bool $try_catch this will determine if requesting to api should put inside tre try catch
	 * @return array            
	 */
	public function wallet_balance_information($walletID, $clientIDs, $try_catch)
	{
		$wallet       = $this->repository('Rproducts')->get_walletID_information($walletID);
		// make sure client is registered
		$this->register_not_exist($clientIDs['clientID'], $clientIDs['parentID'],  $wallet['serverID']);
		
		$get_balance = function() use ($clientIDs, $walletID) {

			return $this->request(
						'get_player_balance', 
						array(
							'client_id' => $clientIDs['clientID'], 
							'wallet_id' => $walletID
						)
					);

		};	

		if ($try_catch) {
			
			try {
				
				$get_player_balance     = $get_balance();
				$wallet['balance']      = $get_player_balance['balance'];
				$wallet['is_connected'] = true;
				
			} catch (Exception $e) {

				$wallet['balance']      = 0;
				$wallet['is_connected'] = false;

			}

		} else {
		
			$get_player_balance = $get_balance();
			$wallet['balance']  = $get_player_balance['balance'];


		}

		return $wallet;
	}

	/**
	 * This will do the actual fund transfer through API
	 * @param  string $process        deposit/withdraw
	 * @param  int    $transfer_info  [walletID, amount]
	 * @param  int    $clientIDs      [clientID, parentID]
	 * @return array
	 */
	public function fund_transfer($process, $transfer_info, $clientIDs)
	{
		$wallet = $this->repository('Rproducts')->get_walletID_information($transfer_info['walletID']);

		$this->register_not_exist($clientIDs['clientID'], $clientIDs['parentID'],  $wallet['serverID']);

		$this->request($process,array(
			'client_id' => $clientIDs['clientID'], 
			'amount'    => $transfer_info['amount']
		));

        return array( 
			'result'  => true, 
			'message' => array('{{@lang.language.fund_transfer}}','{{@lang.messages.fund_transfer_success}}'
		));
	}

	/**
	 * This will login client to third party games
	 * For now this supports sbo login only
	 * @param  array  $clientIDs [clientID, parentID]
	 * @param  array  $game_info   [gameID, productID]
	 * @return string               token
	 */
	public function login_third_party($clientIDs, $game_info)
	{
		$this->register_not_exist($clientIDs['clientID'], $clientIDs['parentID'],  $game_info['serverID']);

		return $this->request('sbo_login', array(
			'client_id'  => $clientIDs['clientID'], 
			'server_id'  => $game_info['serverID'], 
			'game_id'    => $game_info['gameID'], 
			'product_id' => $game_info['productID']
		))['token'];

	}

	/**
	 * This will logout list of clientIDs to other third party sites
	 * For now this supports sbo logout only
	 * @param  array  $clientIDs  single or list of clientIDs
	 * @param  array  $serverIDs  if no argument passed this will logout all third parties
	 * @return array
	 */
	public function logout_third_parties($clientIDs, $serverIDs = false)
	{
		if (!is_array($clientIDs)) {

    		$clientIDs = array($clientIDs);
    		
    	}	

    	if ($serverIDs == false) {
    		
    		// get serverIDs first
    		$serverIDs = $this->repository('Rproducts')->get_unique_serverIDs(Config::get('settings.TOKEN_ON_API'));
    		
    	}

    	$logged_out = array();

    	// logout each clientID to all serverID list
    	foreach ($clientIDs as $clientID) {
    		
    		foreach ($serverIDs as $serverID) {
    			
    			// its not necessary to return error here, this process should not affect any operation
    			// because this is only to ensure that client is logged out if ever he is registered in any 3rd party
    			try {

    				$this->request('sbo_logout', array('client_id' => $clientID, 'server_id' => $serverID));

    				if (!isset($logged_out[$clientID])) {
    					
    					$logged_out[$clientID] = array();

    				}

    				$logged_out[$clientID][] = $serverID;

    			} catch(Exception $e) {

    				// nothing to do here, error is already logged by Sapi.php

    			}

    		}

    	}

    	return $logged_out;
	}

	/**
	 * get payload by api request
	 * @param  int $transactionDetID 
	 * @param  string $serverID         
	 * @param  int $productID        
	 * @return string                   
	 */
	public function bet_details_payload($transactionDetID, $serverID, $productID)
	{

		switch ($productID) {
			case 8:
			case 6:
				$payload_destination = 'casino_payload';
				$payload_param       = array(
											'transactiondetid' => $transactionDetID,
											'server_id'        => $serverID
									   );
				break;
			
		}

		$api_payload = $this->request($payload_destination, $payload_param);

		return $api_payload['payload'];
	}

	/**
	 * Send API request to notify system about closed game window
	 * @param  array  $websession 
	 * @param  string $gameID    
	 * @param  string $clientID   
	 * @return void
	 */
	public function status_player_false($websession, $gameID, $clientID)
	{
		$this->request('post_status_player', array(
			'server_id' => $this->repository('Rproducts')->get_serverID($gameID),
			'details'   => array(
							'game_id'    => $gameID,
							'token'      => $websession['token'],
							'client_id'  => $clientID,
							'session_id' => $websession['sessionID'],
							'game_type'  => $websession['gameType'],
							'ip'         => get_ip()
						),
			'is_active' => false
		));
	}
}