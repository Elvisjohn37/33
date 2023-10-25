<?php

namespace Backend\models;

class Mipblacklist extends Basemodel {

	protected $table      = 'ipblacklist';
	public    $timestamps = false;
	protected $primaryKey = 'ID';

	/**
	 * This will get all blacklisted IPs
	 * @return  object
	 */
	public function get_all()
	{
		return $this->select('fromIP', 'toIP')->where('isEnabled','=',1)->get();
	}
}