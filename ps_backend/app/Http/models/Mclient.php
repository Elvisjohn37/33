<?php

namespace Backend\models;

use DB;

class Mclient extends Basemodel {
        
    protected $table      = 'client';
    protected $hidden     = array('password');
    protected $primaryKey = 'clientID';
    protected $guarded    = array('clientID');
    public    $timestamps = false;
    protected $fillable   = array(
                                'clientID','firstName','lastName',           
                                'email','bankName','accountBankNo',         
                                'accountBankName','signupDate','mobile',            
                                'clientTypeID','currencyID','languageID',          
                                'isWalkIn','isFirstLogin','isCompany',         
                                'isTestPlayer','isCashFlow','isTermsAccepted',       
                                'memberStatusID','parentID','username',  
                                'loginName','salt','password', 
                                'referralID','securityQuestion','yourAnswer',
                                'isBot','whiteLabelID',
                                'jurisdictionID'            
                            );

    /**
     * This will give us the status text and unified status id of client
     * fields : status, status_id, is_view, is_active
     * Read DB comments for negative statuses(status that can limit client transactions to the site)
     * 
     * Logic: company negative statuses is priority than agent negative statuses
     *
     * status_id was made to create one final client status base on 2 fields(memberStatusID, companySettingID)
     * 
     * Negative status from companySettingID    = com_<companySettingID>
     * Negative status from for memberStatusID  = mem_<memberStatusID>
     * @param  object $query
     * @return object
     */
    public function scopeAccount_statuses_field($query)
    {
        return $query->addSelect(

            DB::raw($this->Bclient_status_query." as derived_status_id"),
            DB::raw($this->Bclient_transactable_query." as derived_is_transactable"),
            DB::raw($this->Bclient_active_query." as derived_is_active")
            
        );
    }

    /**
     * This will get all information of client joining all tables that has client prefix
     * 
     * @param  int    $clientID 
     * @return object
     */
    public function get_full_information($clientID) 
    {
        return $this->select('client.*','clientsession.*','clientbalance.*','clientproduct.*')
                    ->account_statuses_field()
                    ->clientsession_join()
                    ->join("clientbalance", "clientbalance.clientID", "=", "client.clientID")
                    ->join("clientproduct", "clientproduct.clientID", "=", "client.clientID")
                    ->where('client.clientID', '=', $clientID)
                    ->first();
    }

    /**
     * This will join clientsession table
     * @param  object $query
     * @return object
     */
    public function scopeclientsession_join($query)
    {
        return $query->join('clientsession', 'clientsession.clientID', '=', 'client.clientID');
    }

    /**
     * This will give an information from client table only no extra joins
     * @param  int      $clientID clientID of the client in client table
     * @param  string   $to_select use for switch case to limit the select column
     * @return object   eloquent object of client information
     */
    public function get_by_clientID($clientID, $to_select = ' ') 
    {
        switch (strtoupper($to_select)) {
            
            case 'ACTIVATE_REGISTRATION':

                $query = $this->select('client.memberStatusID', 'client.companySettingID');
                break;

            default:

                $query = $this->select('client.clientID', 'client.username');
                break;
        }

        return $query->where('client.clientID', '=', $clientID)->first();
    }

    /**
     * This will get client by sessionID
     * @param  string $sessionID 
     * @param  string $to_select 
     * @return object
     */
    public function client_by_sessionID($sessionID, $to_select = ' ') 
    {
        $query = $this->select('client.clientID')->clientsession_join();

        switch (strtoupper($to_select)) {
            default:
                $query = $query->account_statuses_field();
                break;
        }

        return $query->where('clientsession.sessionID', '=', $sessionID)->first();
    }

    /**
     * Get basic information of client
     * @param  int $clientID 
     * @return object         
     */
    public function get_basic_information($clientID)
    {
        return $this->select('client.clientID', 'client.username')->clientID($clientID)->first();
    }

    /**
     * This get information of client's parent use to activate account
     * @param  int $clientID 
     * @return object           
     */
    public function for_activate_account($clientID)
    {

        return $this->select('client.memberStatusID', 'client.companySettingID')->clientID($clientID)->first();

    }

    /**
     * Get parentinformation for register a friend
     * @param  int $clientID 
     * @return object           
     */
    public function get_parent_information($clientID)
    {

        return $this->select('currencyID', 'languageID', 'isWalkIn', 'isBot', 'whiteLabelID')->clientID($clientID)->first();
    }

    /**
     * Use to get information of one client
     * @param  eloquent $query    
     * @param  int $clientID 
     * @return object           
     */
    public function scopeclientID($query, $clientID)
    {
        return $query->where('clientID', '=', $clientID);
    }

    /**
     * This will get the clientID of given displayName
     * 
     * @param  string  $displayName display name of client
     * @return mixed                client row or null
     */
    public function clientID_by_displayName($displayName) 
    {
        return $this->select('clientID')->where('displayName', '=', $displayName)->first();
    }

    /**
     * Get clientID and parentID using verification code
     * 
     * @param  string $code verfication code use to retrieve clientID and parentID
     * @return object       eloquent object clientID and parentID
     */
    public function ids_by_verificationCode($code) 
    {
        return $this->select('client.clientID', 'client.parentID')
                    ->join('playerregistration', 'playerregistration.clientID', '=', 'client.clientID')
                    ->where('playerregistration.verificationCode', '=', $code)
                    ->first();
    }

    /**
     * Update registration to 
     * 
     * @param  int   $clientID       clientID use to update playerregistration 
     * @param  array $update_fields  value needed for updating player registartion
     * @return int   				 number of rows affected on update    
     */
    public function update_registration($clientID,$update_fields) 
    {
        return $this->join('playerregistration', 'playerregistration.clientID', '=', 'client.clientID')
                    ->where('client.clientID', '=', $clientID)
                    ->update($update_fields);
    }

    /**
     * Get client row by lostPasswordCode
     * @param  String $code 
     * @return object
     */
    public function get_by_lostPasswordCode($code) 
    {
        return $this->select('clientID','username', 'loginName', 'firstName', 'lastName', 'password')
                    ->where('lostPasswordCode', '=', $code)
                    ->first();
    }

    /**
     * update lostPassword
     * 
     * @param  string $code          
     * @param  array $update_fields 
     * @return int
     */
    public function update_lostPasswordCode( $code, $update_fields ) 
    {
        return $this->where('lostPasswordCode', '=', $code)->update($update_fields);
    }

    /**
     * Retrieve security question
     * 
     * @param  string $loginName
     * @param  string $email
     * @param  string $whiteLabelID
     * @return type
     */
    public function get_securityQuestion( $loginName, $email, $whiteLabelID ) 
    {
        return $this->select('securityQuestion')
                    ->retrieve_by_credentials(array(
                                                'loginName' => $loginName,
                                                'email'     => $email
                                            ) , $whiteLabelID)
                    ->where('isWalkIn', '=', 1)
                    ->value('securityQuestion');
    }

    /**
     * This will count existing loginName
     * @param  string $loginName   
     * @param  string $whiteLabelID
     * @return int
     */
    public function count_loginName($loginName, $whiteLabelID)
    {
       return $this->loginName(trim($loginName))->wl_or_testplayer($whiteLabelID)->count();
    }
    
    /**
     * This will forbid displayName update base on player current displayNameStatus
     * @param  object $query             
     * @param  int    $displayNameStatus
     * @return object
     */
    public function scopeForbid_update_displayName($query, $displayNameStatus)
    {   
        // if auto generate status should be 0
        switch ($displayNameStatus) {
            case 1: return $query->where('displayNameStatus', '!=', $displayNameStatus);
            case 2: return $query->where('displayNameStatus', '=', 0);
        }
    }

    /**
     * Update unique display name 
     * 
     * @param  int    $clientID
     * @param  string $displayName
     * @param  int    $displayNameStatus
     * @return type
     */
    public function update_displayName($clientID, $displayName, $displayNameStatus)
    {
        return $this->where('clientID', '=', $clientID)
                    ->forbid_update_displayName($displayNameStatus)
                    ->update(array(
                        'displayName'        => $displayName, 
                        'displayNameStatus'  => $displayNameStatus
                    ));
    }
    
    /**
     * 
     * @param type $email
     * @param type $whiteLabelID
     * @return type
     */
    public function email_in_use($email, $whiteLabelID) 
    {  
        return $this->select(
                'client.clientID',
                'client.email',
                'client.signupDate',
                'client.memberStatusID',
                'playerregistration.verificationCode'
            )
            ->playerregistration_leftJoin()
            ->wl_email($email,$whiteLabelID)
            ->first();
    }

    /**
     * This will soft delete client in DB
     * @param  type $clientID
     * @return int
     */
    public function client_soft_delete( $clientID )
    {
        return $this->playerregistration_leftJoin()
                    ->where('client.clientID', '=', $clientID )
                    ->update(array(
                        'memberStatusID'                        => 5,
                        'loginName'                             => DB::raw('client.username'),
                        'email'                                 => DB::raw('client.username'),
                        'playerregistration.verificationCode'   => NULL
                    ));
    }
    

    public function scopeplayerregistration_leftJoin($query)
    {
        return $query->leftJoin('playerregistration','playerregistration.clientID','=','client.clientID');
    }
    /**
     * Count the users thats use a username
     * 
     * @param  string $username
     * @return int
     */
    public function count_username($username)
    {
        return $this->where('username', '=', $username)->count();
    }

    /**
     * This will fillter client by agent type only
     * @param  object $query 
     * @return object
     */
    public function scopeAgent($query)
    {
        return $query->where('clientTypeID', '=', 3);
    }

    /**
     * This will fillter client by agent type only
     * @param  object $query 
     * @return object
     */
    public function scopePlayer($query)
    {
        return $query->where('clientTypeID', '=', 4);
    }

    /**
     * SCOPE for WL ID and check if test player
     * 
     * @param  object $query
     * @param  string $whiteLabelID
     * @param  array $test_agent_whitelist
     * @return object
     */
    public function scopeWl_or_testplayer($query, $whiteLabelID, $test_agent_whitelist = false) 
    {

        return $query->where(function($where) use($whiteLabelID, $test_agent_whitelist) {

            $where->where('whiteLabelID', '=', $whiteLabelID);

            if (is_array($test_agent_whitelist)) {

                $where->orWhere(function($orWhere) use($test_agent_whitelist) {

                    $orWhere->whereIn('parentID',$test_agent_whitelist)->where('isTestPlayer', '=', 1);

                });

            } else {

                $where->orWhere('isTestPlayer', '=', 1);

            }

        });
    }
    
    /**
     * Add new player info
     * 
     * @param  type $data
     * @return int
     */
    public function insert_new_player( $data )
    {
        return $this->Binsert($data);
    }

    /**
     * This will get all client info that will be used for transfer
     * @param  int $clientID
     * @return object
     */
    public function get_transfer_information($clientID)
    {
        return $this->select(
                        'client.clientID',
                        'client.parentID',
                        'client.firstName',
                        'client.lastName',
                        'client.bankName',
                        'client.accountBankName',
                        'client.accountBankNo',
                        'clientbalance.playableBalance',
                        'clientbalance.availableBalance',
                        'clientbalance.cashBalance',
                        'clientbalance.totalBalance'
                    )
                    ->join('clientbalance','clientbalance.clientID','=','client.clientID')
                    ->where('client.clientID','=',$clientID)
                    ->first();
    }

    /**
     * This will update basic information only of player those on client table only
     * @param  int   $clientID    
     * @param  array $update_data 
     * @return int
     */
    public function update_basic_information($clientID, $update_data)
    {
        return $this->where('client.clientID', '=', $clientID)->update($update_data);
    }

    /**
     * Query client data if not login using credentials
     * @param  object $query        
     * @param  array $client_data  loginName, email credentials use to retrieve client
     * @param  string $whiteLabelID 
     * @return eloquent               
     */
    public function scopeRetrieve_by_credentials($query,$client_data,$whiteLabelID)
    {
        return $query->loginName($client_data['loginName'])
                    ->wl_or_testplayer($whiteLabelID)
                    ->where('email', '=', $client_data['email']);
    }

    /**
     * set lostPasswordCode of client
     * @param array $client_data      loginName, email, yourAnswer
     * @param string $whiteLabelID     
     * @param sting $lostPasswordCode 
     */
    public function set_lostPasswordCode($client_data, $whiteLabelID, $lostPasswordCode)
    {

        return $this->where(array(
                            array('loginName', '=', $client_data['loginName']),
                            array('yourAnswer', '=', $client_data['yourAnswer'])
                        ))
                    ->retrieve_by_credentials($client_data, $whiteLabelID)
                    ->update($lostPasswordCode);

    }

    /**
     * Get firstName, lastName of client use for email
     * @param  string $loginName 
     * @return object            
     */
    public function name_by_loginName($loginName, $whiteLabelID)
    {

        return $this->select('firstName', 'lastName', 'lostPasswordCode')
                    ->loginName($loginName)
                    ->where('whiteLabelID','=', $whiteLabelID)
                    ->first();
    }

    /**
     * Get username of client
     * @param  int $clientID 
     * @return mixed           
     */
    public function get_username($clientID)
    {

        return $this->select('username')
                    ->where('clientID', '=', $clientID)
                    ->value('username');

    }

    /**
     * get client name and verification code by email
     * @param   string $email          
     * @param   string $whiteLabelID   
     * @return  ojbect                 
     */
    public function get_by_email($email,$whiteLabelID)
    {

        return $this->select(
                    'client.clientID',
                    'client.lastName',
                    'client.firstName',
                    'playerregistration.verificationCode'
                )
                ->join('playerregistration','playerregistration.clientID', '=', 'client.clientID')
                ->wl_email($email,$whiteLabelID)
                ->first();      
    }

    /**
     * This will filter user that uses the email in specific whitelabel
     * @param  object $query        
     * @param  string $email        
     * @param  string $whiteLabelID 
     * @return object
     */
    public function scopeWl_email($query, $email, $whiteLabelID)
    {
        return $query->where('client.email', '=', trim($email))->wl_or_testplayer($whiteLabelID);
    }

    /**
     * update isTermsAccepted
     * @param  int $clientID      
     * @param  array $update_fields 
     * @return int                
     */
    public function update_isTermsAccepted($clientID, $update_fields)
    {
        return $this->where('clientID', '=', $clientID)
                ->update($update_fields);
    }

    /**
     * This will filter client by loginName
     * @param  object $query 
     * @param  string $loginName
     * @return object
     */
    public function scopeloginName($query,$loginName)
    {
        return $query->where('client.loginName','=',$loginName);
    }

    /**
     * This will get specific WL player including test players info by username
     * @param  string $loginName     
     * @param  string $whiteLabelID 
     * @return object
     */
    public function for_login_verification($loginName, $whiteLabelID, $test_agent_whitelist)
    {
        return $this->select(
                        'client.loginName',
                        'client.password',
                        'client.salt',
                        'client.username',
                        'client.companySettingID',
                        'client.memberStatusID',
                        'client.email',
                        'client.isTestPlayer',
                        'client.whiteLabelID',
                        'client.parentID',
                        'client.clientID',
                        'client.wrongPassword',
                        'client.isPasswordReset',
                        'clientsession.sessionID',
                        'clientsession.isLogin',
                        'clientsession.lastActivity'
                    )
                    ->account_statuses_field()
                    ->clientsession_join()
                    ->player()
                    ->wl_or_testplayer($whiteLabelID, $test_agent_whitelist)
                    ->loginName($loginName)
                    ->first();
    }

    /**
     * This will update client and clientsession table using clientID
     * @param  array $clientID    
     * @param  array $update_data 
     * @param  array $sessionID 
     * @return int
     */
    public function update_login_information($clientIDs, $update_data, $sessionID = null)
    {
        $query = $this->clientsession_join()
                    ->whereIn('client.clientID', $clientIDs);
                       
        if (!is_null($sessionID)) {

            $query->where('clientsession.sessionID', '=', $sessionID);

       }
            return $query->update($update_data);
                      
    }

    /**
     * This will get login client in clientID list
     * @param  array $clientIDs
     * @return object
     */
    public function get_isLogin_clients($clientIDs)
    {
        return $this->select(
                        'client.loginName',
                        'client.username',
                        'clientsession.sessionID',
                        'client.clientID', 
                        'client.parentID'
                    )->clientsession_join()
                    ->whereIn('client.clientID',$clientIDs)
                    ->where('clientsession.isLogin','=',1)
                    ->get();
    }
    
    /**
     * This will add fields that is needed for walkin agents before login info
     * @param  object $query 
     * @return object
     */
    public function  scopeWalkin_agent_fields($query)
    {
        return $query->addSelect(
                        'client.clientID',
                        'client.username',
                        'client.languageID',
                        'client.isCompany',
                        'client.isTestPlayer',
                        'client.isCashFlow',
                        'client.isBot',
                        'client.whiteLabelID',
                        'client.jurisdictionID'
                    );
    }

    /**
     * This will get the agent of assigned currencyID
     * no need to check if walkin because we will have a set of agent ID 
     * 
     * @param int    $currencyID
     * @param array $registration_parents
     */
    public function walkin_currencyID_agent($currencyID, $registration_parents = NULL)
    {
        return $this->walkin_agent_fields()
                    ->agent()
                    ->where('currencyID', '=', $currencyID)
                    ->whereIn('clientID', $registration_parents)
                    ->first();
    }

    /**
     * This will get whitelabel assigned agent
     * @param  string $whiteLabelID 
     * @return object
     */
    public function wl_agent($whiteLabelID)
    {
        return $this->walkin_agent_fields()
                    ->join('whitelabel','whitelabel.clientID','=','client.clientID')
                    ->agent()
                    ->where('whitelabel.whiteLabelID','=',$whiteLabelID)
                    ->first();
    }

    /**
     * get all unverified account
     * @param  string $prev_signupDate 
     * @return array                  
     */
    public function get_unverified($prev_signupDate)
    {
        return $this->select(
                        'client.clientID',
                        'client.username',
                        'client.loginName',
                        'client.email',
                        'client.firstName',
                        'client.lastName'
                    )
                    ->join('playerregistration', 'playerregistration.clientID', '=', 'client.clientID')
                    ->where('playerregistration.isActivated', '=', 0)
                    ->where('client.signupDate', '<=', $prev_signupDate)
                    ->where('client.clientTypeID', '=', 4)
                    ->where('client.memberStatusID', '=', 0)
                    ->get();
    }

    /**
     * get client lastactivity and information
     * @param  int $clientID 
     * @return mixed           
     */
    public function player_lastActivity($clientID)
    {
        return $this->select(
                        'client.clientID',
                        'client.username',
                        'client.loginName',
                        'client.memberStatusID',
                        'clientsession.sessionID',
                        'clientsession.lastLoginIP',
                        'clientsession.lastActivity'

                    )
                    ->clientsession_join()
                    ->where('client.clientID', '=', $clientID)
                    ->first();
    }

    /**
     * get all unregistered clients game
     * @return array 
     */
    public function unregistered()
    {
        return $this->select(
                        'client.clientID',
                        'client.loginName',
                        'client.parentID'
                    )
                    ->leftJoin('companyclient','companyclient.clientID','=','client.clientID')
                    ->join('companyclient as cc', 'cc.clientID','=','client.parentID')
                    ->where('client.isTermsAccepted','=', 1)
                    ->whereNull('companyclient.companyID')
                    ->get();
    }

    /**
     * Get status if test player or not
     * @param  string  $loginName            
     * @param  string  $whiteLabelID         
     * @param  array  $test_agent_whitelist 
     * @return array                       
     */
    public function isTestPlayer($loginName, $whiteLabelID, $test_agent_whitelist = false)
    {
        return $this->select('isTestPlayer')
                ->wl_or_testplayer($whiteLabelID, $test_agent_whitelist)
                ->loginName($loginName)
                ->first();
    }
}
