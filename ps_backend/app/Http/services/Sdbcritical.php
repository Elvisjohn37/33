<?php

namespace Backend\services;

use DB;
use Request;
use Config;
use Input;
use Exception;

/**
 * This service will hold all critical DB/Repository transactions.
 * To isolate them and to give serious attention when enhancing/adding/removing process
 * @author PS Team
 */
class Sdbcritical extends Baseservice {

	/**
	 * All queries for inserting newly registered client
	 * @param  array  $client_data 
	 * @return array 
	 */
	public function registration($client_data) 
	{

		DB::beginTransaction();
		try {

			$clientID = $this->repository('Rplayer')->insert_new_player($client_data);
			
			// Add default pokerlimit for all direct registration in Player site
	        $this->repository('Rplayer')->insert_clientbalance(
	            array('clientID'=> $clientID, 'agentID' =>  $client_data['parentID']),
	            Config::get('settings.user.skill_games_limit')
	        );

            // verifiation code
			$verificationCode = $this->repository('Rplayer')->insert_playerregistration(array(
			                'clientID'          => $clientID,
			                'userAgent'         => Request::header('User-Agent'),
			                'referrerUrl'       => $this->service('Ssession')->get_referrer()
			            )); 

            // Bonus New member
			if (!empty(Input::get('ps_promotion')) && $this->service('Ssiteconfig')->new_bonus_enabled()) {
            
	            $this->repository('Rplayer')->insert_clientpromotion(array(
	                'clientID'      => $clientID, 
                    'promotionID'   => Config::get('settings.promotion.new_member_promotionID'),  
	                'value'         => Input::get('ps_promotion')
	            ));

	        }        
        
	        //get products from rpoducts and pass to Rplayer
	        $products = $this->repository('Rproducts')->get_products();
	        
	        $commissioneffective = $this->repository('Rplayer')->player_commissioneffective(
	                                array(
	                                    'playerID' => $clientID,
	                                    'agentID'  => $client_data['parentID']
	                                ),

	                                array(
	                                    'commission_agent_pt'   => Config::get('settings.COMMISION_AGENTPT'),
	                                    'commission_pl_comrake' => Config::get('settings.COMMISION_PLCOMRAKE'),
	                                    'system_time'           => $this->repository('Rdbconfig')
	                                                                    ->set_system_time(date('Y-m-d H:i:s'))
	                                ),

	                                $products 
	                            );
	        
	        $clientproduct = $this->repository('Rplayer')->insert_clientproduct($client_data['parentID'], $clientID);
			
			DB::commit();
			return array(
				'result'              => true,
				'clientID'			  => $clientID,
				'verificationCode'    => $verificationCode,
				'commissioneffective' => $commissioneffective,
				'clientproduct'       => $clientproduct
			);

		} catch (Exception $e) {

			$this->service('Slogger')->file(
				array(
					'error_message' => $e->getMessage()
				),
				'ERROR'
			);

			DB::rollBack();

			return array('result' => false);

		}
	}

	/**
	 * Queries for register a friend
	 * @param  @array $friend_data 
	 * @return array              
	 */
	public function register_friend($friend_data)
	{

		DB::beginTransaction();
		try {
			
			$friend_id = $this->repository('Rplayer')->insert_new_player($friend_data);

			DB::commit();
			return array('success' => true, 'friend_id' => $friend_id);

		} catch (Exception $e) {
			
			$this->service('Slogger')->file(
				array(
					'error_message' => $e->getMessage()
				),
				'ERROR'
			);

			DB::rollBack();
			return array('success' => false);
			
		}

	}
}