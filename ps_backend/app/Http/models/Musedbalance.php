<?php

namespace Backend\models;
use DB;

/**
 * amount is in game balance
 */
class Musedbalance extends Basemodel {
    
    protected $table        = 'usedbalance';
    protected $primaryKey   = 'usedBalanceID';
    protected $guarded      = array('usedBalanceID');
    protected $hidden       = array('walletID','tableID','gameID');
    public    $timestamps      = false;

    /**
     * This will add ammountType in field
     * @param  object $query 
     * @return object
     */
    public function scopeAmmount_type_field($query)
    {

        return $query->addSelect(
                DB::raw(
                    'CASE 
                        WHEN usedbalance.walletID IN(1,3,5) THEN ?
                        WHEN usedbalance.walletID = 2       THEN ?
                        ELSE ?
                    END as derived_amount_type'
                )
            )
            ->addBinding(array('Chips','Coins',''),'select');

    }

    /**
     * This will get the amount base on player currency
     * For now this supports IDR only
     * @param  object $query 
     * @return object       
     */
    public function scopeCurrency_amount_field($query)
    {
        return $query->addSelect(
                DB::raw(
                    'CASE 
                        WHEN usedbalance.walletID IN(1,3,5) THEN amount*1000
                        WHEN usedbalance.walletID = 2       THEN amount*10
                        ELSE amount
                    END as derived_currency_amount'
                )
            );
    }
    
    /**
     * This will get used balance record of player
     * @param  int $clientID
     * @return object
     */
    public function get_usedbalance($clientID)
    {
        return $this->select('usedbalance.walletID','tableName','tableID','game.gameID')
                    ->ammount_type_field()
                    ->currency_amount_field()
                    // we will use the joinable_walletID_field because we dont need to check user status
                    // Frontend will handle the member status checking, because it has live source
                    ->Bjoinable_walletID_field()
                    ->join('wallet','wallet.walletID','=','usedbalance.walletID')
                    ->join('client', 'client.clientID', '=','usedbalance.clientID')
                    ->join('game','game.walletID',    '=','wallet.walletID')
                    ->where('client.clientID', '=', $clientID)
                    ->where('usedbalance.amount', '>', 0)
                    ->groupBy('usedbalance.usedBalanceID')
                    ->get();
    }
    
}
