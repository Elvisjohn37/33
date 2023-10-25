<?php

namespace Backend\models;


/**
* 
*/
class Mtournamentdetail extends Basemodel {

	protected $table = 'tournamentdetail';
	protected $primaryKey = 'tournamentDetID';
	public $timestamps = false;
	
	/**
	 * get player rank
	 * @param  int $clientID     
	 * @param  int $tournamentID 
	 * @return mixed               
	 */
	public function tournament_rank($clientID, $tournamentID)
	{
		return $this->select('displayName','turnover','isClaimed')
					->join_tournamentprice_tournamentID($tournamentID)
					->where('tournamentdetail.clientID', '=', $clientID)
					->where('turnover', '>', 0)
					->where('isClaimed', '=', 0)
					->first();
	}

	/**
	 * get details of ranking per phase
	 * @param  int $tournamentID 
	 * @return object               
	 */
	public function phase_ranking($tournamentID)
	{
		return $this->select(
						'tournamentdetail.clientID',
						'tournamentdetail.displayName',
						'tournamentdetail.turnover',
						'tournamentdetail.netwin',
						'tournamentprize.tournamentPrizeID',
						'tournamentprize.amount'
					)
					->join_tournamentprice_tournamentID($tournamentID)
					->orderBy('turnover', 'desc')
					->orderBy('netwin', 'desc')
					->get();

	}

	/** 
	 * Join tournamentPrize and where tournamentID
	 * @param  object $query        
	 * @param  int $tournamentID 
	 * @return object               
	 */
	public function scopejoin_tournamentprice_tournamentID($query, $tournamentID)
	{
		return $query->leftjoin('tournamentprize','tournamentdetail.tournamentPrizeID', '=', 'tournamentprize.tournamentPrizeID')
					 ->where('tournamentdetail.tournamentID', '=', $tournamentID);
	}

	/**
	 * Set client prize to claimed
	 * @param  int $clientID      
	 * @param  int $tournamentID  
	 * @param  array $update_fields 
	 * @return int                
	 */
	public function claim_prize($clientID, $tournamentID, $update_fields)
	{
		return $this->where('clientID','=', $clientID)
					->where('tournamentID','=', $tournamentID)
					->where('isClaimed','=',0)
					->update($update_fields);
	}

	/**
	 * get prize detials
	 * @param  int $clientID     
	 * @param  int $tournamentID 
	 * @return mix               (object or null)
	 */
	public function prize_details($clientID, $tournamentID)
	{
		return $this->select('rank', 'amount')
					->join_tournamentprice_tournamentID($tournamentID)
					->where('clientID','=', $clientID)
					->first();
	}
}