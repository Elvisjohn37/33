<?php

namespace Backend\models;

class Mmemberstatus extends Basemodel {

	protected $table      = 'memberstatus';
	protected $primaryKey = 'memberStatusID';
	public    $timestamps = false;

	/**
	 * This will get memberStatusNames from DB
	 * @param  array $memberStatusIDs 
	 * @return object
	 */
	public function get_names($memberStatusIDs)
	{
		return $this->select('memberStatusID','memberStatusName')
					->whereIn('memberStatusID',$memberStatusIDs)
					->pluck('memberStatusName','memberStatusID');
	}
}
