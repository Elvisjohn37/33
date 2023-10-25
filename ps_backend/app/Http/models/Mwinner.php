<?php

namespace Backend\models;

use DB;

/**
 * Winner amount is based on onyx balance
 */
class Mwinner extends Basemodel {

	protected $table      = 'winner';
	protected $hidden     = array('winnerID', 'loginname', 'description', 'derived_currency_amount');
	public    $timestamps = false;

	/**
	 * This will filter all winners within given productIDs
	 * @param  object $query     
	 * @param  mixed  $productIDs array or string productID
	 * @return object
	 */
	public function scopeproductIDs($query, $productIDs)
	{
		$query->join('product','product.productID','=','winner.productID');

		if (is_array($productIDs)) {
			
			return $query->whereIn('product.productID',$productIDs);

		} else {

			return $query->where('product.productID','=',$productIDs);

		}

	}

	/**
	 * This will filter all winners base on given minimum amount
	 * @param  object  $query          
	 * @param  numeric $minimum_amount 
	 * @return object
	 */
	public function scopeMinimum_amount($query, $minimum_amount)
	{

		return $query->where("winner.amount", ">=" , $minimum_amount);

	}

	/**
	 * This will filter winners base on whiteLabelID
	 * @param  object $query       
	 * @param  string $whiteLabelID 
	 * @return object
	 */
	public function scopewhiteLabelID($query,$whiteLabelID)
	{

		return $query->join('client','client.username','=','winner.username')
			->where('client.whiteLabelID','=',$whiteLabelID);

	}

	/**
	 * This will filter all winners valid for plugin display
	 * @param  object $query 		
	 * @param  array  $plugin_filter [minimum_amount,productIDs]
	 * @return object
	 */
	public function scopePlugin($query,$plugin_filter)
	{

		return $query->productIDs($plugin_filter['productIDs'])
			->minimum_amount($plugin_filter['minimum_amount']);

	}

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
	 * This will count all latest winners of the games starting from given start date
	 * @param  array  $site_information [start_date, whiteLabelID]
	 * @param  array  $winners_config   [minimum_amount, productIDs]
	 * @return int
	 */
	public function count_latest($site_information, $winners_config)
	{
		$start_date=date('Y-m-d', strtotime($site_information['start_date']));

		return $this->plugin(array(
						'minimum_amount' => $winners_config['minimum_amount'],
						'productIDs'	 => $winners_config['productIDs']
					))
					->whiteLabelID($site_information['whiteLabelID'])
					->where('winner.date','>=',$start_date)
					->count('winner.winnerID');

	}

	/**
	 * get latest winners list
	 * @param  string $productID      
	 * @param  array $winners_config  [minimum_amount, limit]
	 * @return object
	 */
	public function get_latest($productID, $winners_config)
	{

		return $this->select('winner.displayName','product.productName as product')
					->currency_amount_field()
					->plugin(array(
						'minimum_amount' => $winners_config['minimum_amount'],
						'productIDs'	 => $productID
					))
					->orderBy('winner.winnerID', 'desc')
					->take($winners_config["limit"])
					->get();

	}

}
