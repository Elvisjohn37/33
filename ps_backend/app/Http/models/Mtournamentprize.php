<?php

namespace Backend\models;


/**
* 
*/
class Mtournamentprize extends Basemodel {

	protected $table = 'tournamentprize';
	protected $primaryKey = 'trank';
	public $timestamps = false;
	
	/**
	 * get all phases of tournament
	 * @return object 
	 */
	public function prize_amount($tournamentID)
	{

		return $this->select(
						'amount',
						'rank'
					)
					->where('tournamentID', '=', $tournamentID)
					->whereIN('rank', [1,2,3,4])
					->get()
					->keyBy('rank');

	}

}