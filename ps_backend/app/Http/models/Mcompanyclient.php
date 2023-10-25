<?php

namespace Backend\models;

class Mcompanyclient extends Basemodel {

	protected $table  = 'companyclient';
	protected $hidden = array('companyID','clientID'); 

	/**
	 * Count client record
	 * @param  int $companyID
	 * @param  int $clientID 
	 * @return int
	 */
	public function count_record($companyID, $clientID)
	{

		return $this->where('companyID','=',$companyID)->where('clientID','=',$clientID)->count('clientID');

	}

}
