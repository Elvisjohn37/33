<?php

namespace Backend\models;

class Mcredit extends Basemodel {

	protected $table      = 'credit';
	protected $primaryKey = 'creditID';
	protected $hidden     = array('transactionID');
	public    $timestamps = false;

	/**
	 * This will get transactionID transfer details
	 * @param  int   $transactionID 
	 * @param  array $limits       
	 * @return object
	 */
	public function get_active_details($transactionID, $limits)
	{
		return $this->select('dateTime','newCreditLimit','actualTotalBalance','actualPlayableBalance')
					->where('credit.transactionID','=',$transactionID)
					->orderBy('credit.dateTime')
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
		return $this->where('credit.transactionID','=',$transactionID)->count('credit.creditID');
	}

}
