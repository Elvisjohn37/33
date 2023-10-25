<?php

namespace Backend\models;

class Mplayerblacklist extends Basemodel {

	protected $table      = 'playerblacklist';

	/**
	 * This will count player blocklist
	 * @return  int
	 */
	public function count_player_blocklist($clientID)
	{
        return $this->where('clientID','=',$clientID)->count('clientID');
		
	}
}