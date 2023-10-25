<?php

namespace Backend\models;
use DB;

class Mcurrency extends Basemodel {

    protected $table       = 'currency';
    public    $timestamps  = false;
    
    /**
     * count webSignUpEnabled of currencyID
     * @param  int $currencyID 
     * @return int
     */
    public function count_webSignupEnabled($currencyID)
    {
        return $this->where('currencyID','=',$currencyID)
	                ->where('webSignupEnabled','=',1)
	                ->count('currencyID');
    }
    
    /**
     * get all webSignupEnabled currencies
     * @return int
     */
    public function get_webSignupEnabled_currency($registration_parents)
    {
        return $this->select('currency.currencyID','currency.code','currency.description')
            ->join('client', 'client.currencyID','=','currency.currencyID')
            ->whereIN('clientID',$registration_parents)
            ->where('webSignupEnabled','=',1)
            ->get();
    }

    /**
     * Get agent currency info
     * @param  string $whitelabelID 
     * @return array
     */
    public function get_agentSignup_currency($whitelabelID)
    {
        return $this->select(DB::raw('DISTINCT currency.currencyID'), 'currency.code', 'currency.description')
            ->join('client','client.currencyID' ,'=','currency.currencyID')
            ->where('client.clientTypeID', '=', 3)
            ->where('whitelabelID','=', $whitelabelID)
            ->where('webSignupEnabled','=',1)
            ->get();
    }
}
