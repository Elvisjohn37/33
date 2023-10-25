<?php

namespace Backend\models;

use DB;

class Mwebsession extends Basemodel {

	protected $table 	  = 'websession';
	protected $primaryKey = 'websessionID';
	public    $timestamps = false;

	private   $lobby_gameType = 'Lobby';

	/**
     * This will delete websessionIDs
     * Its always better to select primary key first before deleting it
     * To avoid deadlocks
     * @param  array $websessionIDs
     * @return boolean/null
	 */
	public function delete_websessionIDs($websessionIDs)
	{
		return $this->whereIn('websessionID',$websessionIDs)->delete();
	}

	/**
     * This will delete the non lobby websession of specific gameID recorded for client
     * @param  int $gameID   
     * @param  int $clientID
     * @return boolean/null
	 */
	public function get_gameID_websessionIDs($gameID, $clientID)
	{
		return $this->select('websessionID')
					->where('gameID','=',$gameID)
					->where('clientID','=',$clientID)
					->not_lobby()
					->pluck('websessionID');
	}

	/**
	 * Delete all lobby websession and given productIDs
	 * @param  array $clientIDs  
	 * @param  array $productIDs 
	 * @return object
	 */
	public function get_force_delete($clientIDs, $productIDs)
	{
		return $this->select('websessionID')
					->join('game','game.gameID','=','websession.gameID')
					->whereIn('websession.clientID',$clientIDs)
					->where(function($query) use($productIDs) {
			  			$query->lobby();
			  			$query->orWhereIn('game.productID', $productIDs);
					})
					->pluck('websessionID');
	}

	/**
	 * Check if productIDs has no coin in then delete
	 * @param  array $clientIDs  
	 * @param  array $productIDs 
	 * @return object
	 */
	public function get_without_coinin($clientIDs, $productIDs)
	{
		return $this->select('websessionID')
					->join('game','game.gameID','=','websession.gameID')
					->whereIn('websession.clientID',$clientIDs)
					->leftJoin('usedbalance', function($join) {
						$join->on('websession.clientID','=','usedbalance.clientID');
						$join->on('game.walletID', '=', 'usedbalance.walletID');
					}) 
					->whereIn('game.productID', $productIDs)
					->whereNull('usedbalance.usedBalanceID')
					->pluck('websessionID');
	}

	/**
	 * Check if productIDs that is not on the given list has no running then delete
	 * @param  array $clientIDs       
	 * @param  array $except_productIDs 
	 * @return object
	 */
	public function get_without_running($clientIDs, $except_productIDs)
	{
		$transaction_subquery = DB::table('transaction')
									->select(
										'transaction.transactionID',
										'transaction.clientID',
										'transactiondetail.gameID'
									)
									->join(
										'transactiondetail',
										'transactiondetail.transactionID','=','transaction.transactionID'
									)
									->where('transaction.transactionType','=','Bet')
									->where('transactiondetail.event','=','R')
									->whereIn('transaction.clientID',$clientIDs);

		return $this->select('websessionID')
					->join('game','game.gameID','=','websession.gameID')
					->leftJoin(DB::raw('('.$transaction_subquery->toSql().') as transaction'), function($join) {

						$join->on('transaction.gameID','=','websession.gameID')
							->on('transaction.clientID','=','websession.clientID');

					})
					->addBinding($transaction_subquery->getBindings(),'join')
					->whereIn('websession.clientID', $clientIDs)
					->whereNull('transaction.transactionID')
					->whereNotIn('game.productID', $except_productIDs)
					->where('websession.gameType', '!=', $this->lobby_gameType)
					->pluck('websessionID');
	}

	/**
	 * Filter lobby websessions
	 * @param  object $query    
	 * @return object
	 */
	public function scopeNot_lobby($query)
	{
		return $query->where('gameType','!=',$this->lobby_gameType);
	}

	/**
	 * Filter lobby websessions
	 * @param  object $query    
	 * @return object
	 */
	public function scopeLobby($query)
	{
		return $query->where('websession.gameType', '=', $this->lobby_gameType);
	}

	/**
	 * Search cient websession by gameID
	 * @param  object  $query    
	 * @param  int     $gameID   
	 * @param  int     $clientID
	 * @return object
	 */
	public function scopeBy_game($query, $gameID, $clientID)
	{

		return $query->where('websession.gameID',  '=', $gameID)
					->where('websession.clientID', '=', $clientID);

	}

	/**
	 * Search cient websession by productID
	 * @param  object  $query     
	 * @param  int     $productID
	 * @param  int     $clientID  
	 * @return object
	 */
	public function scopeBy_product($query, $productID, $clientID)
	{

		$query->join('game', 'websession.gameID', '=', 'game.gameID')
			->where('websession.clientID', '=', $clientID)
			->where('game.productID', '=', $productID);

	}

	/**
	 * Get the first lobby websession of the game
	 * @param  int     $gameID  
	 * @param  int     $clientID 
	 * @return object
	 */
	public function lobby_by_game($gameID, $clientID)
	{
		return $this->select('webSessionID','websession.token')
					->by_game($gameID, $clientID)
					->lobby()
					->first();
	}

	/**
	 * Get the first lobby websession of the product
	 * @param  int     $productID  
	 * @param  int     $clientID 
	 * @return object
	 */
	public function lobby_by_product($productID, $clientID)
	{
		return $this->select('webSessionID','websession.token')
					->by_product($productID, $clientID)
					->lobby()
					->first();
	}

	/**
	 * This will filter by gameType
	 * There are games that can create multiple websession on their own but have different gameType only  
	 * Sample: Tangkas, using tableName as gameType
	 * @param  object  $query    
	 * @param  string  $gameType if set to false then this will not filter gameType
	 * @return object
	 */
	public function scopeBy_gameType($query, $search_gameType)
	{
		if ($search_gameType) {
			
			return $query->where('gameType', '=', $search_gameType);

		} else {

			return $query->where('gameType', '!=', $this->lobby_gameType);

		}
	}

	/**
	 * Get the first actual gameType websession of gameID
	 * @param  int    $gameID   
	 * @param  int    $clientID 
	 * @param  string $gameType 
	 * @return array
	 */
	public function actual_by_game($gameID, $clientID, $search_gameType)
	{
		return $this->select('webSessionID')
					->by_game($gameID, $clientID)
					->by_gameType($search_gameType)
					->first();
	}

	/**
	 * Get the first actual websession of productID
	 * @param  int    $productID
	 * @param  int    $clientID  
	 * @param  string $gameType
	 * @return array
	 */
	public function actual_by_product($productID, $clientID, $search_gameType)
	{
		return $this->select('webSessionID')
					->by_product($productID, $clientID)
					->by_gameType($search_gameType)
					->first();
	}

	/**
	 * This will create new lobby game websession
	 * @param  array $websession_data
	 * @return int                    primaryKey
	 */
	public function create_lobby($websession_data)
	{
		assoc_array_merge($websession_data, array('gameType' => $this->lobby_gameType));

		return $this->insert_websession($websession_data);
	}

	/**
	 * This will update websession by webSessionID
	 * @param  array $websession_data
	 * @return int                    affected
	 */
	public function update_websession($webSessionID, $websession_data)
	{
		return $this->where('webSessionID','=',$webSessionID)->update($websession_data);
	}

	/**
	 * This will insert websession
	 * @param  array $websession_data
	 * @return int                    primaryKey
	 */
	public function insert_websession($websession_data)
	{
		return $this->Binsert($websession_data);
	}

    /** 
     * This will get not lobby websession by token, gameID and clientID
     * @param  string $token    
     * @param  string $gameID   
     * @param  string $clientID 
     * @return int
     */
	public function get_by_token($token, $gameID, $clientID) 
	{
		return $this->select('sessionID','gameType','token')
					->where('gameID', $gameID)
					->where('clientID',$clientID)
					->where('token', $token)
					->not_lobby()
					->first();
	}
}
