<?php

namespace Backend\models;

use DB;

class Mtransactiondetail extends Basemodel  {
	
	protected $table      = 'transactiondetail';
	protected $primaryKey = 'transactionDetID';

	/**
	 * This will get settled bets only
	 * @param  int 	  $transactionID
	 * @param  array  $limits       
	 * @return object   
	 */
	public function get_settled_bets($transactionID, $limits)
	{
		return $this->select(
						'transactiondetail.transactionDetID',
						'transactiondetail.result',
						'transactiondetail.message',
						'transactiondetail.event',
						'transactiondetail.txnID',
						'transactiondetail.txnDetID',
						'transactiondetail.actualCashBalance',
						'transactiondetail.actualAvailableCredit',
						'transactiondetail.actualPlayableBalance',
						'transactiondetail.endDateTime',

						'product.productID',
						'product.productName',

						'game.gameID',
						'game.gameName',
						'game.serverID'
					)
					->transaction_join()
					->transactiondetail_dateTime()
					->Baggregate_fields('SUM', array(
						'transactiondetail.turnover',
						'transactiondetail.stake',
						'transactiondetail.grossRake',
						'transactiondetail.turnover',
						'transactiondetail.netWin',
						'transactiondetail.totalWin',
						'winlose.membercomm'
					))
					->Btotal_transactions_field()
					->join('winlose','transactiondetail.transactionDetID','=','winlose.transactionDetID')
					// we have promotion record with gameID 0 so we use left join
					->leftJoin('game', 'game.gameID','=','transactiondetail.gameID')
					->leftJoin('product', 'product.productID','=','game.productID')
					->where('transactiondetail.transactionID','=',$transactionID)
					->Bsettled_bet()
					->Bbet_grouping_field('groupBy')
					->orderBy('dateTime')
					->offset($limits['offset'])
					->limit($limits['limit'])
					->get();
	}

	/**	
	 * This will count settled bets only
	 * @param  int $transactionID 
	 * @return int
	 */
	public function count_settled_bets($transactionID)
	{
		return $this->where('transactiondetail.transactionID','=',$transactionID)
					->Bsettled_bet()
					->Bbet_grouping_field('count')
					->value('derived_bet_grouping');
	}

	/**
	 * get transactiondetails of betting transaction
	 * @param  int $transactionDetID 
	 * @param  int $clientID         
	 * @return mixed transaction detail row or null
	 */
	public function get_details($transactionDetID, $clientID)
	{

		return $this->select(
			'transaction.productID',
			'transactiondetail.result',
			'transactiondetail.txnID',
			'transactiondetail.transactionDetID',
			'transactiondetail.txnDetID',
			'game.serverID',
			'game.gameID'
			)
		->leftjoin('transaction','transactiondetail.transactionID','=','transaction.transactionID')
		->leftJoin('game','transactiondetail.gameID','=','game.gameID')
		->where('transactionDetID','=', $transactionDetID)
		->where('clientID', '=', $clientID)
		->first();
	}

	/**
	 * scope to add condtion if enddatetime or startdatetime to use for dateTime
	 * @param  object $query 
	 * @return object        
	 */
	public function scopetransactiondetail_dateTime($query)
	{
		return $query->addSelect(DB::raw($this->Bas_transactiondetail_dateTime));
	}

	/**
	 * Scope to join transaction table
	 * @param  object $query 
	 * @return object
	 */
	public function scopetransaction_join($query)
	{
		return $query->join('transaction','transaction.transactionID','=','transactiondetail.transactionID');
	}
}
