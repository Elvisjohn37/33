<?php

namespace Backend\models;

use DB;

/**
 * Transfer amount is based on onyx balance
 */
class Mtransfer extends Basemodel {

	protected $table      = 'transfer';
	protected $primaryKey = 'transferID';
	protected $hidden     = array(
								'transactionID', 
								'adminID', 
								'accountBankName',
								'accountBankNo', 
								'bankName',
								'description', 
								'notificationStatusID',
								'derived_currency_amount'
							);
	public  $timestamps = false;

	/**
	 * This will give amount base on players currency 
	 * for now this supports IDR only
	 * amount is in onyx balance
	 * @param  object $query 
	 * @return object
	 */
	public function scopeCurrency_amount_field($query)
    {
		return $query->addSelect(DB::raw('amount*1000 as derived_currency_amount'));
	}

	/**
	 * Filter all amount within the given minimum amount
	 * @param  object  $query         
	 * @param  numeric $minimum_amount 
	 * @return object      
	 */
	public function scopeMinimum_amount($query,$minimum_amount)
	{

		return $query->where('transfer.amount', '>=' ,$minimum_amount);

	}

	/**
	 * This will filter all transfer with players of given whiteLabelID
	 * @param  object  $query             
	 * @param  string  $whiteLabelID      
	 * @param  boolean $with_isTestPlayer default = true, set to false if testplayers should be excluded
	 * @return object
	 */
	public function scopewhiteLabelID($query, $whiteLabelID, $with_isTestPlayer = true)
	{
		if ($with_isTestPlayer) {
			
			return $query->where(function($query) use ($whiteLabelID) {

					$query->where('client.whiteLabelID','=',$whiteLabelID)
						->orWhere('client.isTestPlayer','=',1);

				});


		} else {

			return $query->where('client.whiteLabelID','=',$whiteLabelID);

		}
	}

	/**
	 * This will filter all transfer for player accounts only
	 * @param  object $query 
	 * @return object
	 */
	public function scopeJoin_player($query)
	{
		return $query->Join("client","client.clientID","=","transfer.fromToClientID")
			->where('client.clientTypeID', '=', 4);
	}

	/**
	 * This will filter all transfer valid for plugin display
	 * @param  object $query         
	 * @param  int    $minimum_amount 
	 * @return object                 
	 */
	public function scopePlugin($query, $plugin_filter)
	{
			
		return $query
			->join_player()
			->where('transfer.type', '=',$plugin_filter['type'])
			->whiteLabelID($plugin_filter['whiteLabelID'],$plugin_filter['with_isTestPlayer'])
			->Bapproved_transfer()
			->minimum_amount($plugin_filter['minimum_amount']);

	}

	/**
	 * Count latest transactions for specific whiteLabelID only, no test players
	 * @param  string $type                
	 * @param  array  $site_information    [start_date,whiteLabelID]
	 * @param  array  $minimum_ammount		
	 * @return object                    
	 */
	public function count_latest($type, $site_information, $minimum_ammount)
	{
		return $this->plugin(
						array( 
							'type'				=> $type,
							'minimum_amount'   	=> $minimum_ammount,
							'whiteLabelID'	    => $site_information['whiteLabelID'],
							'with_isTestPlayer' => false
						)
					)
					->where('transfer.datetime', '>=' , $site_information['start_date'])
					->count('transfer.transferID');
	}

	/**
	 * This will get all latest transfers including test players
	 * @param  string $type            transfer type
	 * @param  string $whiteLabelID    whiteLabelID
	 * @param  array  $transfer_config specific configurations for filtering the result
	 * @return object
	 */
	public function get_latest($type, $whiteLabelID, $transfer_config) 
	{	
		return $this->select('client.displayName','transfer.type')
					->currency_amount_field()
					->plugin(
						array( 
							'type'				=> $type,
							'minimum_amount'   	=> $transfer_config['minimum_amount'],
							'whiteLabelID'	    => $whiteLabelID,
							'with_isTestPlayer' => true
						)
					)
					->orderBy('transfer.transferID', 'desc')
					->take($transfer_config['limit'])
					->get();
	}

	/**
	 * This will get all active transfer of a given transactionID
	 * @param  object $query        
	 * @param  int    $transactionID
	 * @return object
	 */
	public function scopeActive_transactionID($query, $transactionID)
	{
		return $query->Bactive_transfer()->where('transfer.transactionID','=',$transactionID);
	}

	/**
	 * This will get transactionID transfer details
	 * @param  int   $transactionID 
	 * @param  array $limits       
	 * @return object
	 */
	public function get_active_details($transactionID, $limits)
	{
		return $this->select(
						'transfer.dateTime',
						'transfer.type',
						'transfer.amount',
						'transfer.actualCashBalance',
						'transfer.actualAvailableCredit',
						'transfer.actualPlayableBalance',
						'transfer.notificationStatusID'
					)
					->active_transactionID($transactionID)
					->orderBy('transfer.dateTime')
					->offset($limits['offset'])
					->limit($limits['limit'])
					->get();
	}

	/**
	 * This will count transactionID transfer details
	 * @param  int   $transactionID 
	 * @return object
	 */
	public function count_active_details($transactionID)
	{
		return $this->active_transactionID($transactionID)->count('transfer.transferID');
	}

	/**
	 * This will save date to transfer table and return transferID
	 * @param  array  $transfer transfer data
	 * @return int
	 */
	public function insert_transfer($transfer)
	{
		return $this->Binsert($transfer);
	}
}
