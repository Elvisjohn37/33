<?php

namespace Backend\models;

/**
 * jackpot amount is based on game balance
 */
class Mjackpot extends Basemodel {

	protected $table  = 'jackpot';
	protected $hidden = array('gameID','gameMultiplier');
	
	/**
	 * This will get all jackpots
	 * array $productID 
	 * @return object
	 */
	public function get_jackpots($productIDs)
	{
		return $this->select('game.gameID','game.gameName as productName', 'jackpot.jackpot')
					->join('game','jackpot.gameID','=','game.gameID')
					->where('jackpot.jackpot', '>', 0)
					->whereNotIn('game.productID',$productIDs)
					->get();
	}

	/**
	 * This will get all lastResult of the games
	 * @return object
	 */
	public function get_lastResults()
	{
		return $this->select('lastResult','gameName')
					->join('game','jackpot.gameID','=','game.gameID')
					->whereNotNull('lastResult')
					->where('lastResult','!=','')
					->get();

	}
}
