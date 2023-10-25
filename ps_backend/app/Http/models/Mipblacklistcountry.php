<?php

namespace Backend\models;

/**
* 
*/
class Mipblacklistcountry extends Basemodel {
	
	protected $table      = 'ipblacklistcountry';
	public    $timestamps = false;
	protected $primaryKey = 'ID';

	public function country_ip_range($ip)
	{
		return $this->join('ip', 'ip.countryName','=', 'ipblacklistcountry.country')
                    ->where('range1','<=', $ip)
                    ->where('range2','>=', $ip)
                    ->where('isEnabled','=',1)
                    ->first();

	}
}