<?php

namespace Backend\models;


/**
* 
*/
class Mtournament extends Basemodel {

	protected $table = 'tournament';
	protected $primaryKey = 'tournamentID';
	public $timestamps = false;
	
	/**
	 * get all phases of tournament
	 * @return object 
	 */
	public function tournaments_details()
	{
		return $this->select(
						'tournamentID',
						'phaseNo',
						'startDateTime',
						'isActive', 
						'claimDeadline'
					)
					->orderBy('phaseNo', 'asc')->get();
	}

	/**
	 * get active tournament
	 * @return mixed
	 */
	public function get_active()
	{

		return $this->select('*')
					->where('isActive','=',1)
					->first();
					
	}
}