<?php

namespace Backend\models;


/**
* 
*/
class Mtournamenttoprank extends Basemodel {

	protected $table = 'tournamenttopRank';
	protected $primaryKey = 'trank';
	public $timestamps = false;
	
	/**
	 * get all phases of tournament
	 * @return object 
	 */
	public function top_rank()
	{
		return $this->select(
						'trank',
						'clientID',
						'username',
						'displayName',
						'productID',
						'gameID',
						'tournamentID',
						'turnover',
						'netwin',
						'startDateTime',
						'endDateTime'
					)->get();
	}

}