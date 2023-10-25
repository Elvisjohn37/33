<?php

namespace Backend\models;

class Mwallet extends Basemodel {
	
	protected $table      = 'wallet';
	protected $primaryKey = 'walletID';
	public    $timestamps = false;

	/**
	 * This will add serverID field by connecting to any game with walletID
	 * @param  object $query 
	 * @return object
	 */
	public function scopeserverID_field($query)
	{

		return $query->addSelect('game.serverID')->join('game','game.walletID','=','wallet.walletID');

	}

	/**
	 * This will get wallet information by walletID
	 * @param  int    $walletID 
	 * @return object
	 */
	public function get_by_walletID($walletID)
	{
		return $this->select('wallet.description')
					->serverID_field()
					->where('wallet.walletID','=',$walletID)
					->first();
	}

	/**
	 * This will get wallet list informations
	 * @param  array  $walletIDs
	 * @param  array  $productIDs
	 * @return object
	 */
	public function get_product_wallets($walletIDs, $productIDs)
	{
		return $this->select('wallet.walletID', 'wallet.description')
			->join('game','game.walletID', '=', 'wallet.walletID')
			->join('product','product.productID', '=', 'game.productID')
			->whereIn('wallet.walletID',$walletIDs)
			->whereIn('product.productID',$productIDs)
			->groupBy('wallet.walletID')
			->get();
	}
}