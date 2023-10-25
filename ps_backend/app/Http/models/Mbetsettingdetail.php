<?php

namespace Backend\models;

class Mbetsettingdetail extends Basemodel {

	protected $table      = 'betsettingdetail';
	protected $primaryKey = 'betSettingDetailID';
	public    $timestamps = false;
	Protected $fillable   = array('betSettingID', 'description', 'value');

	/**
	 * getSetting get the betsettingdetail of parent 
	 * @param  array $betSettingIDs betsettingID's of parent from betsetting table
	 * @return object      eloquent objetc of betsettingdetail               
	 */
	public function get_by_betSettingIDs($betSettingIDs)
	{

		return $this->select('betSettingID','description','value')
					->whereIn('betSettingID',$betSettingIDs)
					->get();

	}

	/**
	 * inherit the betsettingdetail of parent
	 * @param  int $child_betSettingID betBettingID of client's parent
	 *         from betsetting table use to retrieve betsettingdetail of parent
	 * @param  array $parent_value        description and value of parentd betsettingdetails
	 * @return none
	 */
	public function insert_betsettingdetail($betsettingdetail)
	{
		return $this->Binsert($betsettingdetail);
	}	
}
