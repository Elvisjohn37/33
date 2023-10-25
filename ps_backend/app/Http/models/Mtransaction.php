<?php

namespace Backend\models;

use DB;

/**
 * Transfer amount is based on onyx balance
 */
class Mtransaction extends Basemodel  {
	
	protected $table      = 'transaction';
	protected $primaryKey = 'transactionID';
	protected $fillable   = array('transactionID','clientID','transactionType','date');
	public    $timestamps = false;

	/**
	 * This will add final transactionType field
	 * @param  object $query 
	 * @return object
	 */
	public function scopetransactionType_field($query)
	{

		return $query->addSelect(DB::raw(
				"CASE WHEN(transaction.transactionType='Bet')
					THEN 'Betting'
					ELSE transaction.transactionType
				END as transactionType"
			));

	}

	/**
	 * Collection of join scope to get each transaction details
	 * @param  object $query
	 * @param  array  $statement_date DEFAULT NULL
	 *                                removed where every join if join is for statement
	 *                                Will have where every join if for transaction logs
	 * @return object
	 */
	public function scopeStatement_details_join($query, $statement_date = NUll)
	{		

		return $query

			// betting
			->leftjoin(DB::raw('transactiondetail FORCE INDEX FOR JOIN (idx_transactiondetail_transactionID)'),
				function($join) use($statement_date) {

					$join->on('transactiondetail.transactionID','=','transaction.transactionID');
						if (!is_null($statement_date)) {
							$join->where('transactiondetail.endDateTime','>=', $statement_date['start_date'])
								->where('transactiondetail.endDateTime','<=', $statement_date['end_date']);
						}
					$join = $this->scopeBsettled_bet($join);

			})
			->leftjoin('winlose','transactiondetail.transactionDetID','=','winlose.transactionDetID')

			// transfer
			->leftJoin(DB::raw('transfer FORCE INDEX FOR JOIN (fk_idx_transfer_transactionID)'),
				function($join) use($statement_date) {

					$join->on('transfer.transactionID','=','transaction.transactionID');
					$join = $this->scopeBactive_transfer($join);
					if (!is_null($statement_date)) {
						$join->where('transfer.dateTime','>=', $statement_date['start_date'])
							->where('transfer.dateTime','<=', $statement_date['end_date']);
					}

			})

			// credit
			->leftjoin(DB::raw('credit FORCE INDEX FOR JOIN (fk_idx_credit_transactionID)'),
				function($join) use($statement_date) {

					$join->on('credit.transactionID','=','transaction.transactionID');
						if (!is_null($statement_date)) {
							$join->where('credit.dateTime','>=', $statement_date['start_date'])
							->where('credit.dateTime','<=', $statement_date['end_date']);
						}

			})

			->where(function($query) {

				$query->whereNotNull('transfer.transferID')
					->orWhereNotNull('credit.creditID')
					->orWhereNotNull('transactiondetail.transactionDetID');

			});
	}

	/**
	 * This will get transactions by given date
	 * @param  int    $clientID   
	 * @param  array  $statement_date [start_date, end_date] Y-m-d
	 *                                Add where between clause
	 * @param  string $end_date  
	 * @return object
	 */
	public function get_statement($clientID, $statement_date)
	{
		return $this->select(
						'transaction.transactionID',
						'transaction.date',
						DB::raw('COALESCE(SUM(transactiondetail.netWin), transaction.cashBalance) as cashBalance'),
						'product.productName'
					)
					->transactionType_field()
					->Baggregate_fields('SUM', array(
						'transactiondetail.turnover',
						'transactiondetail.grossRake',
						'transactiondetail.turnover',
						'commission' => 'winlose.membercomm',
						'credit' 	 => 'credit.amount'
					))
					->where('transaction.clientID','=', $clientID)
					->whereBetween('transaction.date', array($statement_date['start_date'], $statement_date['end_date']))
					->statement_details_join()
					->leftJoin('product', 'product.productID','=','transaction.productID')
					->groupBy('transaction.transactionID')
					->get();
	}

	/**
	 * This will count transactionID of the clientID
	 * @param  int $transactionID
	 * @param  int $clientID      
	 * @return int
	 */
	public function count_client_transactionID($transactionID, $clientID)
	{
		return $this->where('transaction.transactionID','=',$transactionID)
					->where('transaction.clientID','=',$clientID)
					->count('transactionID');
	}

	/**
	 * This will compose a CASE WHEN clause for 3 related tables of transaction
	 * Do not use this for queries that has values from user
	 * @param  array $table_query_string  ['table'=>'field value']
	 * @return string                    
	 */
	private function related_table_field($table_query_string, $as_column = NUll)
	{

		if (is_null($as_column)) {
			$additional_case = $table_query_string['transactiondetail'];
		} else {
			$additional_case = 'CASE WHEN(transaction.transactionType="Bet")
						THEN transactiondetail.startDateTime
						ELSE transactiondetail.endDateTime
						END';
		}

		return 'CASE WHEN transfer.transferID IS NOT NULL 
				THEN 
					'.$table_query_string['transfer'].'
				ELSE
					CASE WHEN credit.creditID IS NOT NULL 
					THEN 
						'.$table_query_string['credit'].'
					ELSE
						'.$additional_case.'
					END
				END';
	}

	/**
	 * Transaction logs group by
	 * @param  object $query 
	 * @param  object $used_for 
	 * @return object
	 */
	public function scopeLogs_grouping_field($query, $used_for = '')
	{

		$group_query_string = $this->related_table_field(array(
								'transfer' 			=> 'transfer.transferID',
								'credit'   			=> 'credit.creditID',
								'transactiondetail' => $this->Btransactiondetail_grouping
							));

		switch($used_for) {

			case 'count': 

				return $query->Baggregate_fields('COUNT', 
						array('derived_logs_grouping' => Db::raw('DISTINCT '.$group_query_string))
					);

			default:

				$query->addSelect(
					Db::raw($group_query_string.' as derived_logs_grouping')
				);

				if ($used_for!='') {

					return $query->$used_for('derived_logs_grouping');

				} else {

					return $query;

				}

		}
	}

	/**
	 * Partial query of getting transaction logs
	 * @param  object $query          
	 * @param  array  $statement_date 
	 * @param  int    $clientID     
	 * @return object
	 */
	public function scopeTransaction_logs($query , $statement_date, $clientID)
	{ 

		return $query->statement_details_join($statement_date)
				->where('transaction.date','=', $statement_date['date_only'])
				->where('transaction.clientID','=', $clientID);
	}

	/**
	 * This will get players transaction logs
	 * @param  array  $statement_date [start_date, end_date, date_ony] Y-m-d H:i:s.u
	 *                                date_only (Y-m-d) this will use for filtering the records
	 *                                to lessen the search data 
	 * @param  int    $clientID       
	 * @param  array  $limits         [offset, limit]
	 * @return object
	 */
	public function get_transaction_logs($statement_date, $clientID, $limits)
	{

		return $this->select(
						'transfer.type',
						'transfer.notificationStatusID',
						
						'transactiondetail.transactionDetID',
						'transactiondetail.result',
						'transactiondetail.event',
						'transactiondetail.txnID',
						'transactiondetail.txnDetID',
						'transactiondetail.message',
						'transactiondetail.endDateTime',

						'product.productID',
						'product.productName',

						'game.gameID',
						'game.gameName',
						'game.serverID',

					  	DB::raw($this->related_table_field(array(
							'transfer' 			=> 'transfer.dateTime',
							'credit'   			=> 'credit.dateTime',
						), 'dateTime').' as dateTime'),

					  	DB::raw($this->related_table_field(array(
							'transfer' 			=> 'transfer.amount',
							'credit'   			=> 'credit.amount',
							'transactiondetail' => 'NULL'
						)).' as amount'),

					  	DB::raw($this->related_table_field(array(
							'transfer' 			=> 'transfer.actualCashBalance',
							'credit'   			=> 'credit.actualCashBalance',
							'transactiondetail' => 'transactiondetail.actualCashBalance'
						)).' as actualCashBalance'),

					  	DB::raw($this->related_table_field(array(
							'transfer' 			=> 'transfer.actualAvailableCredit',
							'credit'   			=> 'credit.actualAvailableCredit',
							'transactiondetail' => 'transactiondetail.actualAvailableCredit'
						)).' as actualAvailableCredit'),

					  	DB::raw($this->related_table_field(array(
							'transfer' 			=> 'transfer.actualPlayableBalance',
							'credit'   			=> 'credit.actualPlayableBalance',
							'transactiondetail' => 'transactiondetail.actualPlayableBalance'
						)).' as actualPlayableBalance')

					)
					->Baggregate_fields('SUM', array(
						'transactiondetail.totalWin',
						'transactiondetail.stake',
						'transactiondetail.turnover',
						'transactiondetail.grossRake',
						'transactiondetail.netWin',
						'winlose.membercomm'
					))
					->Btotal_transactions_field()
					->transactionType_field()
					->transaction_logs($statement_date, $clientID)
					->leftJoin('game', 'game.gameID','=','transactiondetail.gameID')
					->leftJoin('product', 'product.productID','=','game.productID')
					->logs_grouping_field('groupBy')
					->orderBy('dateTime')
					->offset($limits['offset'])
					->limit($limits['limit'])
					->get();
	}

	/**
	 * This will count players transaction logs
	 * @param  array  $statement_date [start_date, end_date] Y-m-d H:i:s.u
	 * @param  int    $clientID       
	 * @return object
	 */
	public function count_transaction_logs($statement_date, $clientID)
	{
		return $this->transaction_logs($statement_date, $clientID)
					->logs_grouping_field('count')
					->value('derived_logs_grouping');
	}

	/**
	 * This will get exisitng transactionID for given clientID and date
	 * @param  array $transaction  transaction info to be inserted, required fiels[clientID, transactionType, date]
	 * @return int
	 */
	public function get_create_transactionID($transaction)
	{
		return $this->BfirstOrCreate($transaction);
	}

	/**
	 * This will get all unsettled bets details
	 * @param  int 	 $clientID
	 * @param  array $limits   [offset, limit]
	 * @return object
	 */
	public function get_running_bets($clientID, $limits)
	{
		return $this->select(
				'transactiondetail.transactionDetID',
				'transactiondetail.result',
				'transactiondetail.message',
				'transactiondetail.event',
				'transactiondetail.txnID',
				'transactiondetail.txnDetID',

				'product.productID',
				'product.productName',
				
				'game.gameID',
				'game.gameName',
				'game.serverID'
			)
			->transactiondetail_dateTime()		
			->Baggregate_fields('SUM', array(
				'transactiondetail.turnover',
				'transactiondetail.stake'
			))
			->Btotal_transactions_field()
			->join('transactiondetail','transactiondetail.transactionID','=','transaction.transactionID')
			->join('game', 'game.gameID','=','transactiondetail.gameID')
			->join('product', 'product.productID','=','game.productID')
			->Bbet_grouping_field('groupBy')
			->where('transaction.clientID','=',$clientID)
			->Bunsettled_bet()
			->orderBy('dateTime')
			->offset($limits['offset'])
			->limit($limits['limit'])
			->get();
	}

	/**
	 * This will count unsettled bets
	 * @param  int $clientID 
	 * @return int
	 */
	public function count_running_bets($clientID)
	{
		return $this->join('transactiondetail','transactiondetail.transactionID','=','transaction.transactionID')
					->Bbet_grouping_field('count')
					->where('transaction.clientID','=',$clientID)
					->Bunsettled_bet()
					->value('derived_bet_grouping');
	}

	/**
	 * This will get total stake of all unsettled bets
	 * @param  int $clientID 
	 * @return int
	 */
	public function total_running_bets($clientID)
	{
		return $this->join('transactiondetail','transactiondetail.transactionID','=','transaction.transactionID')
					->Baggregate_fields('SUM', array('transactiondetail.stake'))
					->where('transaction.clientID','=',$clientID)
					->Bunsettled_bet()
					->value('stake');
	}

	/**
	 * This will get one record of any game under productID has running transaction
     * @param  int    $clientID    
     * @param  int    $productID  
     * @param  array  $except_gameID
     * @return int
	 */
	public function productID_running_first($clientID, $productID, $except_gameID)
	{
		return $this->select('game.gameID','game.gameName')
					->join('transactiondetail','transactiondetail.transactionID','=','transaction.transactionID')
					->join('game', 'game.gameID','=','transactiondetail.gameID')
					->where('transaction.clientID','=',$clientID)
					->where('game.productID','=',$productID)
					->whereNotIn('game.gameID',$except_gameID)
					->Bunsettled_bet()
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
}
