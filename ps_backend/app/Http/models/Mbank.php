<?php

namespace Backend\models;

class Mbank extends Basemodel {

	protected $table        = 'bank';
	protected $primaryKey   = 'bankID';
	protected $hidden       = array('bankID');

	/**
	 * This will filter bank by whiteLabelID
     * 
	 * @param  object $query        
	 * @param  string $whiteLabelID 
	 * @return object               
	 */
	public function scopewhiteLabelID_join($query, $whiteLabelID)
	{
        if ($whiteLabelID=='') {
            
            $query->where('bank.isHouse', '=', 1);
            
        } else {
            
            $query->join('whitelabelbank','whitelabelbank.bankID', '=', 'bank.bankID')
                ->where('isWhiteLabel', '=', 1)
                ->where( 'whitelabelbank.whiteLabelID', '=', $whiteLabelID);
        }
        
        return $query->where('isEnabled','=',1);
	}

	/**
	 * This will raw bank support data from DB
         * 
	 * @param  string  $whiteLabelID   
	 * @param  boolean $is_get_details
	 * @return object
	 */
	public function get_bank_support($whiteLabelID, $is_get_details)
	{
        $query = $this->select(
                    'bank.bankName',
                    'whitelabelbankaccount.whiteLabelBankAccountID',
                    'whitelabelbank.isOnline',
                    'whitelabelbank.forceValidityDate',
                    'whitelabelbankschedule.whiteLabelBankScheduleID',
                    'whitelabelbankschedule.startDay',
                    'whitelabelbankschedule.startTime',
                    'whitelabelbankschedule.endTime',
                    'whitelabelbankschedule.endDay'
                )
                ->whiteLabelID_join($whiteLabelID)
                ->leftJoin(
                    'whitelabelbankaccount',
                    'whitelabelbankaccount.whiteLabelBankID','=','whitelabelbank.whiteLabelBankID'
                )
                ->leftJoin(
                    'whitelabelbankschedule',
                    'whitelabelbankschedule.whiteLabelBankID','=','whitelabelbank.whiteLabelBankID'
                )
                ->where('whitelabelbank.isShow','=',1);

        if ($is_get_details) {

            return $query->addSelect(
                    'whitelabelbank.contentText',
                    'whitelabelbankaccount.accountName',
                    'whitelabelbankaccount.accountNumber'
                )
                ->get();

        } else {

            return $query->get();

        }
	}
    
    /**
     * This will get bank names of whitelabel
     * @param string $whiteLabelID
     * @return obj
     */
    public function get_wl_banks($whiteLabelID)
    {
        
        $base_query = $this->select(
                        'bank.bankName',
                        'bank.accountNoPattern',
                        'bank.accountBankNo'
                    )->whiteLabelID_join($whiteLabelID);

        if ($whiteLabelID != '') {
            
            $base_query->join(
                'whitelabelbankaccount',
                'whitelabelbank.whiteLabelBankID','=','whitelabelbankaccount.whiteLabelBankID'
            )
            ->groupBy('bank.bankID');

        }
        
        return $base_query->get();
    }

    /**
     * This filter by bankName
     * @param  object $query 
     * @param  string $bankName 
     * @return object
     */
    public function scopebankName($query, $bankName)
    {
        return $query->where('bankName', '=', $bankName);
    }
        
    /**
     * get bank number
     * 
     * @param type $bank_name
     * @return type
     */
    public function get_bank_number($bankName) 
    {

        return $this->select('accountBankNo','accountNoPattern')->bankName($bankName)->first();
        
    }

    /** 
     * This will add minDepositLimit field depending on whiteLabelID given
     * @param  object $query       
     * @param  string $whiteLabelID 
     * @param  string $add_as       This will tel s cope which eloqeutn method should we add the field
     * @return object
     */
    public function scopeminDepositLimit_field($query, $whiteLabelID)
    {
        if ($whiteLabelID=='') {
            
            $this->addSelect('bank.minDepositLimit');

        } else {

            $this->addSelect('whitelabelbank.minDepositLimit');

        }
    }

    /**
     * get bank number
     * 
     * @param type $bank_name
     * @return type
     */
    public function get_minDepositLimit($bankName, $whiteLabelID) 
    {
        
        return $this->minDepositLimit_field($whiteLabelID)
                    ->whiteLabelID_join($whiteLabelID)
                    ->bankName($bankName)
                    ->first();
        
    }

}