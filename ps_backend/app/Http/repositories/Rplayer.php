<?php

namespace Backend\repositories;

use Exception;
use Auth;

/**
* Repositories for all table connected to client data
*/
class Rplayer extends Baserepository {
    
    public $models = array(
                        'Mclient',
                        'Mbetsetting',
                        'Mbetsettingdetail',
                        'Mavatar',
                        'Mclienttablelimit',
                        'Mclientsession',
                        'Malias',
                        'Mplayerregistration',
                        'Mclientbalance',
                        'Mclientpromotion',
                        'Mhistorycommissioneffective',
                        'Mcommissioneffective',
                        'Mhistorycommission',
                        'Mcommissiondefault',
                        'Mclientproduct',
                        'Mhistoryrake',
                        'Mprofilelog',
                        'Maccountlog',
                        'Mloginlog',
                        'Mmemberstatus',
                        'Mplayerblacklist'
                    );

    private $displayNameStatus   = array('not_set' => 0, 'user_input' => 1, 'auto_generated' => 2);

    /**
     * This will get player information from client table only
     * @param  int    $clientID 
     * @return object
     */
    public function get_basic_information($clientID)
    {
       $get_client_data = $this->model('Mclient')->get_basic_information($clientID);

        return ($get_client_data) ? $get_client_data->toArray() : $get_client_data;
    }

    /**
     * This will get player and its agent information that's needed for transfer
     * @param  int   $clientID 
     * @return array           [client,parent] elloquent objects of client and parent transfer
     */
    public function get_transfer_information($clientID)
    {
        $client = $this->model('Mclient')->get_transfer_information($clientID);
        $parent = $this->model('Mclient')->get_transfer_information($client->parentID);

        return compact('client','parent');
    }

    /**
     * Get clientID and parentID of client using code
     * 
     * @param  string $code verification code use to retrieve id's
     * @return mixed        eloquent object of client table or empty array
     */
    public function ids_by_verificationCode( $code ) 
    {
        $client = $this->model('Mclient')->ids_by_verificationCode($code);

        return $client ? $client : array();
    }

    /**
     * activate client registration 
     * 
     * @param  int $clientID id of client use to activate account
     * @param  int $parentID use to get parentinformation
     * @return boolean
     */
    public function activate_account( $clientID, $parentID ) 
    {
        $parent = $this->model('Mclient')->for_activate_account($parentID);

        $rows   = $this->model('Mclient')->update_registration($clientID, array(
                    'playerregistration.verificationCode'   => null,
                    'playerregistration.dateActivated'      => date('Y-m-d H:i:s'),
                    'playerregistration.isActivated'        => 1,
                    'memberStatusID'                        => $parent->memberStatusID,
                    'companySettingID'                      => $parent->companySettingID,
                    'notificationStatusID'                  => 1
                ));

        return ($rows > 0);
    }

    /**
     * inherit betsetting of parent
     * @param  int $clientID use for insert
     * @param  int $parentID use to get parent betsetting
     * @return void
     */
    public function inherit_betsetting( $clientID, $parentID ) 
    {
        $parent_betsettings = $this->model('Mbetsetting')->get_by_clientID($parentID);

        if (count($parent_betsettings) > 0) {

            $child_betSettingIDs = array();

            //insert for betsetting of client
            foreach ($parent_betsettings as $parent_betsetting) {

                $child_betSettingID = $this->model('Mbetsetting')->insert_betsetting(array(
                                        'clientID'  => $clientID,
                                        'gameID'    => $parent_betsetting->gameID
                                    ));


                $child_betSettingIDs[$parent_betsetting->betSettingID] = $child_betSettingID;
            }

            //insert betsettingdetails
            $parent_betsettingdetails = $this->model('Mbetsettingdetail')
                                            ->get_by_betSettingIDs(array_keys($child_betSettingIDs));

            foreach ($parent_betsettingdetails as $parent_betsettingdetail) {

                $this->model('Mbetsettingdetail')->insert_betsettingdetail(array(
                    'betSettingID'  => $child_betSettingIDs[$parent_betsettingdetail->betSettingID],
                    'description'   => $parent_betsettingdetail->description,
                    'value'         => $parent_betsettingdetail->value
                ));

            }
        }
    }

    /**
     * inherit the clienttablelimit of client's parent
     * 
     * @param  object $clientID 
     * @param  object $parentID 
     * @return void         
     */
    public function inherit_clienttablelimit( $clientID, $parentID ) 
    {
        $parent_clienttablelimits = $this->model('Mclienttablelimit')->get_by_clientID($parentID);

        foreach ($parent_clienttablelimits as $parent_clienttablelimit) {

            $this->model('Mclienttablelimit')->insert_clienttablelimit(array(
                'clientID'      => $clientID,
                'tableLimitID'  => $parent_clienttablelimit->tableLimitID
            ));

        }
    }

    /**
     * This will check if display name already exists in our DB
     * 
     * @param  string $displayName
     * @return boolean
     */
    public function displayName_exists( $displayName ) 
    {
        $client = $this->model('Mclient')->clientID_by_displayName($displayName);

        return ($client) ? TRUE : FALSE;
    }

    /**
     * Get chabox status by clientID in clientsession
     * 
     * @param  string $clientID 
     * @return array
     */
    public function chatStatus_by_clientID($clientID) 
    {
        $status     = 'offline';
        $chatStatus = 'show';
        
        $parent = $this->model('Mclientsession')->get_by_clientID($clientID);

        if (is_null($parent)) {
            return compact('status', 'chatStatus');
        }
        // check if offline/online status
        if ($parent->isOnline == 1) {

            if ($parent->isLogin == 1) {

                $status = 'online';

            } else {

                $count_online = $this->model('Malias')->count_online($parent->clientID);

                if ($count_online > 0) {

                    $status = 'online';
                }
            }
        }

        // check if hide/show chatbox
        if ($parent->chatStatus == 0) {

            $chatStatus = 'hide';
        }

        return compact('status', 'chatStatus');
    }

    /**
     * Set lostPasswordCode of client for reset password
     * @param array $client_data  loginName, email, yourAnswer
     * @param string $whiteLabelID 
     */
    public function set_lostPasswordCode($client_data, $whiteLabelID)
    {
        
        $code   = array('lostPasswordCode' => generate_verification_code($client_data['loginName']));

        $row    = $this->model('Mclient')->set_lostPasswordCode($client_data, $whiteLabelID, $code);
        $client = FALSE;

        if ($row > 0) {
            $client  = $this->model('Mclient')->name_by_loginName($client_data['loginName'], $whiteLabelID);

        }
        
        return $client;

    }

    /**
     * get client row for session and remove lostPasswordCode
     * 
     * @param  string $code 
     * @return mixed
     */
    public function reset_lostPasswordCode($code) 
    {
        $client = $this->model('Mclient')->get_by_lostPasswordCode($code);

        $affected_rows = 0;

        if ($client) {

            $affected_rows = $this->model('Mclient')->update_lostPasswordCode($code, array('lostPasswordCode' => ''));
            
        }

        if ($affected_rows > 0) {

            // get the clientID primaryKey first because it will be removed if we use toArray in eloquent
            $clientID = $client->clientID;
            $client->toArray();
            $client['clientID']         = $clientID;
            $client['lostPasswordCode'] = $code;

            return $client;

        } else {

            return false;

        }

    }

    /**
     * Get security question for player
     * 
     * @param  string $loginName   
     * @param  string $email          
     * @param  string $whiteLabelID 
     * @return string/boolean           security question or FALSE
     */
    public function get_securityQuestion( $loginName, $email, $whiteLabelID ) 
    {
        return $this->model('Mclient')->get_securityQuestion($loginName, $email, $whiteLabelID);
    }

    /**
     * update and validate display name
     * 
     * @param int    $clientID 
     * @param string $displayName
     * @param int    $displayNameStatus
     * @return array ['affectedRows', 'displayName']
     */
    public function update_displayName($clientID, $displayName, $displayNameStatus = 1) 
    {
        $affectedRows = NULL;
        
        try {

            $affectedRows = $this->model('Mclient')->update_displayName($clientID,$displayName,$displayNameStatus);
            
        } catch ( Exception $e ) {

            $affectedRows   = 0;
        }

        $result = ($affectedRows>0);
        
        return compact('result', 'displayName', 'displayNameStatus');
    }

    /**
     * This will generate unique displayName
     * @param  int   $clientID             
     * @param  array $displayName_prefixes 
     * @param  int   $max_try             
     * @return array                       
     */
    public function generate_displayName($clientID, $displayName_prefixes, $max_try)
    {
        $displayName_tried = array();

        for ($i = 0; $i < $max_try; $i++) {
            
            $names_count = count($displayName_prefixes) - 1;
            $displayName =  $displayName_prefixes[rand(0, $names_count)].random_number(5);
            $update      = $this->update_displayName($clientID, $displayName, 2);

            if ($update['result']) {

                // return on successful update
                return $update;

            } else {

                $displayName_tried[] = $displayName;

            }

        }

        // return as failed, no successful update
        return array('result' => false, 'displayNames'=> $displayName_tried);

    }
    
    /**
     * check if email already exist and can reuse
     * 
     * @param type $email
     * @param type $whiteLabelID
     * @return string
     */
    public function check_email($email,$whiteLabelID, $max_day) 
    {

        $used_email = $this->model('Mclient')->email_in_use(trim($email), $whiteLabelID);
        $result_key = 'unused';

        if ( !is_null($used_email) ) {

            $days_diff = substract_dates($used_email['signupDate'], date('Y-m-d H:i:s'));

            if ($used_email['memberStatusID'] == 0) {

                if ($days_diff >= $max_day ) {

                    $this->model('Mclient')->client_soft_delete( $used_email['clientID'] );
                    $result_key = 'soft_delete_reuse';

                } else {

                    $result_key = is_null($used_email['verificationCode']) ? 'for_agent_activation' : 'for_activation';

                }

            } else {

                $result_key     = 'used_confirmed';   

            }

        }
        return $result_key;

    }
    
    /**
     * Soft delete client 
     * 
     * @param  int $clientID
     * @return void
     */
    public function client_soft_delete($clientID)
    {
        $this->model('Mclient')->client_soft_delete($clientID);
    }
    
    /**
     * 
     * @param type $loginName
     * @param type $whiteLabelID
     * @return type
     */
    public function is_loginName_exist($loginName, $whiteLabelID)
    {
        $exist = $this->model('Mclient')->count_loginName($loginName, $whiteLabelID);
        
        return ($exist > 0) ? TRUE : FALSE;
    }
    
    
    /**
     * New Player registration
     */
    
    /**
     * Check if username exists
     * 
     * @param type $username
     * @return type
     */
    public function generate_random_username( )
    {
        $username = '';
        
        while ( $username == '' ) {

            $username = random_number(10);
        
            $count = $this->model('Mclient')->count_username($username);
            
            if ( $count > 0 ) {
                
                $username = '';
            }
        }
        
        return $username;
    }
    
    /**
     * This will insert players basic info
     * @param array $player_data
     * @return int clientID
     */
    public function insert_new_player($player_data)
    {

        // auto generated and default infos
        set_default($player_data,'username',              $this->generate_random_username());
        set_default($player_data,'loginName',             $player_data['username']);
        set_default($player_data,'isFirstLogin',          1);
        set_default($player_data,'isTermsAccepted',       0);
        set_default($player_data,'memberStatusID',        0);
        set_default($player_data,'clientTypeID',          4);
        set_default($player_data,'signupDate',            date('Y-m-d H:i:s'));

        // hash password
        if (isset($player_data['password'])) {
            $salted_password = $this->get_salted_password($player_data['password']);
            assoc_array_merge($player_data, $salted_password);
        }

        $clientID = $this->model('Mclient')->insert_new_player($player_data);

        // add client session 
        $this->model('Mclientsession')->add_clientsession($clientID);
        

        return $clientID;

    }

    /**
     * Insert registration for walkin player only 
     * @param  array $playerregistration_data
     * @return  boolean
     */
    public function insert_playerregistration($playerregistration_data)
    {
        
        $playerregistration_data['verificationCode'] = generate_verification_code($playerregistration_data['clientID']);
        $playerregistration_data['userDevice']       = get_user_device();
        
        $this->model('Mplayerregistration')->insert_playerregistration( $playerregistration_data );

        return $playerregistration_data['verificationCode'];

    }

    /**
     * Insert promotion for client
     * @param  array $clientpromotion_data 
     * @return void                      
     */
    public function insert_clientpromotion($clientpromotion_data)
    {

        $this->model('Mclientpromotion')->insert_client_promotion($clientpromotion_data);
    
    }
    
    /**
     * 
     * @param type $playerID
     * @param type $agentID
     * @return boolean
     */
    public function player_commissioneffective( $clientID, $config, $products )
    {

        $comm_default_model = $this->model('Mcommissiondefault');
        
        foreach ($products as $product) {
            
            $agentCommDef = array(
                                'clientID'          => $clientID['agentID'],
                                'childClientID'     => $clientID['playerID'],
                                'productID'         => $product->productID,
                                'commissionRake'    => 0,
                                'minPT'             => 0,
                                'forcedPT'          => 0,
                                'takeRemaining'     => 0
                            );

            $comm_default_model->insert_commission_default( $agentCommDef );

            $history_data = array(
                                'clientID'      => $clientID['playerID'],
                                'productID'     => $product->productID,
                                'datetime'      => $config['system_time']
                            );

            if ($product->isCommRake == 1) {
                
                $history_data['commission'] = 0;
                $this->model('Mhistorycommission')->insert_history_commission($history_data);
                
            } else {
                
                $history_data['rake'] = 0;
                $this->model('Mhistoryrake')->insert_history_rake($history_data);

            }
            
            // Create Commission Effective Record
            // Get Master Commission Default
            $masterCommDef = $comm_default_model->get_commission_default($clientID['agentID'], $product->productID);
            
            if (count($masterCommDef) === 0) {

                return FALSE; 
            }

            // Get SMA Commission Default
            $smaCommDef = $comm_default_model->get_commission_default($masterCommDef->clientID, $product->productID);
            
            if (count($smaCommDef) === 0) {

                return FALSE;
            }

            // Get House Commission Default
            $houseCommDef = $comm_default_model->get_commission_default($smaCommDef->clientID, $product->productID);
            
            if (count($houseCommDef) === 0) {

                return FALSE;
            }

            $agentPT        = $config['commission_agent_pt'];
            $masterForcedPt = $masterCommDef->forcedPT * 100;
            $smaForcedPt    = $smaCommDef->forcedPT * 100;
            $houseForcedPt  = $houseCommDef->forcedPT * 100;

            $remaining = 100;
            if (($masterForcedPt / 100) > $agentPT) {
                $agentPT = $masterCommDef->forcedPT;
            }
            $agentPT = $agentPT * 100;
            $remaining -= $agentPT;

            // master
            $masterPT = $masterCommDef->minPT; // 30
            $masterPT = $masterPT * 100; // 30 + 5
            if ($smaForcedPt > ($masterPT + $agentPT)) { // if smaForcePT has been meet by agent and master effectivePT
                $masterPT = $smaForcedPt - ($agentPT); // add lacking values to masterPT
            }
            if ($remaining < $masterPT) { // check if effective PT is higher than whats remaining on 100
                $masterPT = $remaining; // take remaining
            }
            $remaining -= $masterPT; // deduct official masterPT to remaining
            
            // sma
            $smaPT = $smaCommDef->minPT;
            $smaPT = $smaPT * 100;
            if ($houseForcedPt > ($smaPT + $masterPT + $agentPT)) {
                $smaPT = $houseForcedPt - ($masterPT + $agentPT);
            }
            if ($remaining < $smaPT) {
                $smaPT = $remaining;
            }
            $remaining -= $smaPT;

            // house
            $housePT = $houseCommDef->minPT;
            $housePT = $housePT * 100;
            if ($remaining < $housePT) {
                $housePT = $remaining;
            }
            $remaining -= $housePT;

            if ($remaining > 0) {
                if ($masterCommDef->takeRemaining == 1) {
                    $masterPT += $remaining;
                } else {
                    if ($smaCommDef->takeRemaining == 1) {
                        $smaPT += $remaining;
                    }
                }
            }

            # Create Effective Data
            $effectiveData = array(
                                "productID"             => $product->productID,
                                "smaID"                 => $smaCommDef->clientID,
                                "masterID"              => $masterCommDef->clientID,
                                "agentID"               => $clientID['agentID'],
                                "playerID"              => $clientID['playerID'],
                                "smaPT"                 => $smaPT / 100,
                                "masterPT"              => $masterPT / 100,
                                "agentPT"               => $agentPT / 100,
                                "smaMinPT"              => $smaCommDef->minPT,
                                "smaForcedPT"           => $smaCommDef->forcedPT,
                                "smaTR"                 => $smaCommDef->takeRemaining,
                                "masterMinPT"           => $masterCommDef->minPT,
                                "masterForcedPT"        => $masterCommDef->forcedPT,
                                "masterTR"              => $masterCommDef->takeRemaining,
                                "houseMinPT"            => $houseCommDef->minPT,
                                "houseForcedPT"         => $houseCommDef->forcedPT,
                                "houseTR"               => $houseCommDef->takeRemaining,
                                "smaCommissionRake"     => $houseCommDef->commissionRake,
                                "masterCommissionRake"  => $smaCommDef->commissionRake,
                                "agentCommissionRake"   => $masterCommDef->commissionRake,
                                "playerCommissionRake"  => $config['commission_pl_comrake']
                            );

            $this->model('Mcommissioneffective')->insert_commission_effective( $effectiveData );

            // Create History Commission Effective Record

            $historyEffectiveData = array(
                                        "productID"         => $product->productID,
                                        "clientID"          => $clientID['playerID'],
                                        "smaPT"             => $smaPT / 100,
                                        "masterPT"          => $masterPT / 100,
                                        "agentPT"           => $agentPT / 100,
                                        "smaMinPT"          => $smaCommDef->minPT,
                                        "masterMinPT"       => $masterCommDef->minPT,
                                        "smaForcedPT"       => $smaCommDef->forcedPT,
                                        "masterForcedPT"    => $masterCommDef->forcedPT,
                                        "smaTR"             => $smaCommDef->takeRemaining,
                                        "masterTR"          => $masterCommDef->takeRemaining,
                                        "datetime"          => $config['system_time']
                                    );
            
            $this->model('Mhistorycommissioneffective')->insert_history_commEffective( $historyEffectiveData );
        }
        
        return TRUE;
    }

    /**
     * Inherit clientproduct of agent 
     * @param  int $agentID  
     * @param  int $clientID 
     * @return mixed
     */
    public function insert_clientproduct($agentID, $clientID)
    {
        $clientproduct = $this->model('Mclientproduct')->get_applicable_products($agentID);

        if (count($clientproduct) == 0) {
            
            $clientproduct = FALSE;
            
        } else {
            
            $client_product_data = array(
                                    'clientID'  => $clientID,
                                    'productID' => $clientproduct->productID
                                );
            
            $this->model('Mclientproduct')->insert_client_product($client_product_data);
        }

        return $clientproduct;

    }

    /**
     * This will get agent and its aliases sessionIDs 
     * @param  int   $clientID  Agents clientID
     * @return array
     */
    public function get_agent_sessionIDs($clientID) 
    {

        $sessions = array();

        // get agents sessionID
        $parent_sessionID = $this->model('Mclientsession')->get_sessionID($clientID)->toArray();

        foreach ($parent_sessionID as $key => $parent_sessionID) {

            $sessions[] = $parent_sessionID['sessionID'];
            
        }

        $aliases_sessionID = $this->model('Malias')->get_sessionID($clientID)->toArray();

        return seq_array_merge($sessions, $aliases_sessionID);
    }

    /**
     * This will deduct player availableBalance only if it will not result to negative balance
     * @param  int    $clientID 
     * @param  int    $amount   
     * @return boolean
     */
    public function deduct_availableBalance($clientID, $amount)
    {
        $affected_availableBalance = $this->model('Mclientbalance')->deduct_availableBalance(
                                        $clientID, 
                                        non_money_format($amount)
                                    );

        return ($affected_availableBalance > 0);

    }

    /**
     *  used to update password or loginName
     * @param  int   $clientID           
     * @param  array $change_credentials [loginName,password] both is optional
     * @return boolean
     */
    public function update_credentials($clientID, $change_credentials)
    {
        $client_data = array();

        // set loginName
        if (isset($change_credentials['loginName'])) {
            
            $client_data['loginName']    = $change_credentials['loginName'];

            // only in first login the loginName can be reset
            $client_data['isFirstLogin'] = 0;

        }

        // set password
        if (isset($change_credentials['password'])) {
            
            $salted_password = $this->get_salted_password($change_credentials['password']);
            assoc_array_merge($client_data, $salted_password);

            $client_data['lastChangePassword'] = date('Y-m-d');
            $client_data['isPasswordReset']    = 0;
        }

        if ($client_data > 0) {

            $update_credentials = $this->model('Mclient')->update_basic_information($clientID, $client_data);

            if ($update_credentials) {
                
                return $client_data;

            } else {

                return false;
                
            }

        } else {

            return false;

        }
        

    }

    /**
     * This will generate salt for password
     * @param  string $password  non encrypted password
     * @return array
     */
    private function get_salted_password($password)
    {
        $salt = str_random(32);
        return array('salt' => $salt, 'password' =>  encrypt_password($password,$salt));
    }

    /**
     * Get parent information
     * @param  int $clientID 
     * @return object           
     */
    public function get_parent_information($clientID)
    {
       
        return $this->model('Mclient')->get_parent_information($clientID);

    }

    /**
     * This will log all profile changes
     * @param  array $log_data  data to be logged
     * @return void
     */
    public function insert_profilelog($log_data)
    {
        $formatted_log_data = array(
                                'username'    => $log_data['username'],
                                'from'        => to_string(set_default($log_data,'from',array())),
                                'to'          => to_string(set_default($log_data,'to',  array())),
                                'description' => to_string($log_data['description']),
                                'remark'      => to_string(set_default($log_data,'remark', '')),
                                'createdOn'   => $log_data['createdOn'],
                                'createdBy'   => $log_data['createdBy'],
                                'createdFrom' => $log_data['createdFrom'],
                                'ipAddress'   => $log_data['ipAddress']
                            );

        $this->model('Mprofilelog')->insert_profilelog($formatted_log_data);
    }

    /**
     * This will log all account changes
     * @param  array $log_data  data to be logged
     * @return void
     */
    public function insert_accountlog($log_data)
    {
        $formatted_log_data = array(
                                'username'    => $log_data['username'],
                                'from'        => to_string(set_default($log_data,'from',array())),
                                'to'          => to_string(set_default($log_data,'to',  array())),
                                'description' => to_string($log_data['description']),
                                'remark'      => to_string(set_default($log_data,'remark', '')),
                                'createdOn'   => $log_data['createdOn'],
                                'createdBy'   => $log_data['createdBy'],
                                'createdFrom' => $log_data['createdFrom'],
                                'ipAddress'   => $log_data['ipAddress']
                            );

        $this->model('Maccountlog')->insert_accountlog($formatted_log_data);
    }

    /**
     * Set client online
     * @param int $clientID 
     */
    public function set_as_online($clientID)
    {

        return $this->model('Mclientsession')->set_by_clientID($clientID, array('isOnline' => 1));

    }

    /**
     * get username of client
     * @param  int $clientID 
     * @return mixed           
     */
    public function get_username($clientID)
    {

        return $this->model('Mclient')->get_userName($clientID);

    }

    /**
     * Get client information by email
     * @param   string $email          
     * @param   string $whiteLabelID   
     * @return  mixed                  
     */
    public function client_by_email($email, $whiteLabelID)
    {

        $client = $this->model('Mclient')->get_by_email($email,$whiteLabelID);

        if (is_null($client->verificationCode)) {

            $client->verificationCode = generate_verification_code($client->clientID);

            $this->model('Mplayerregistration')->regenerate_verificationCode(
                $client->clientID,
                array('verificationCode' => $client->verificationCode)
            );
        
        }
        
        return $client;
    }

    /**
     * set fields to update in terms and condition
     * @param  int $clientID 
     * @param  int $isWalkIn 
     * @return void           
     */
    public function accept_terms_condition($clientID, $isWalkIn)
    {

        $update_fields = array( 'isTermsAccepted'=>1 );

        if ($isWalkIn) {

            $update_fields['isFirstLogin'] = 0;

        }

        $this->model('Mclient')->update_isTermsAccepted($clientID, $update_fields);

    }

    /**
     * This will get specific WL player including test players info by loginName
     * @param  string $loginName
     * @param  string $whiteLabelID
     * @return array
     */
    public function for_login_verification($loginName, $whiteLabelID, $test_agent_whitelist)
    {
        $client = $this->model('Mclient')->for_login_verification($loginName, $whiteLabelID, $test_agent_whitelist);

        if ($client) {

            $client = $client->makeVisible('password')->toArray();

        } 

        return $client;
    }

    /**
     * This will get status name for list of memberStatusID
     * @param   array $memberStatusIDs
     * @return 
     */
    public function get_memberStatusNames($memberStatusIDs)
    {
        return $this->model('Mmemberstatus')->get_names($memberStatusIDs)->toArray();
    }

    /**
     * This will add wrong password count
     * This will automatically lock the account if wrongPassword reach its limit
     * @param  int $client     client informations
     * @return array/boolean   This will return false if no rows are affected
     */
    public function update_wrongPassword($client, $max_wrongPassword)
    {
        $client['wrongPassword']++;
        $update_data = array('wrongPassword' => $client['wrongPassword']);

        // lock account if status is allowed for login only
        if ($client['wrongPassword'] >= $max_wrongPassword && $client['derived_is_active']) {

            $update_data['memberStatusID'] = 4;

        }

        $update_wrongPassword = $this->model('Mclient')->update_basic_information($client['clientID'],$update_data);

        if ($update_wrongPassword) {

            return $update_data;

        } else {

            return false;

        }
    }

    /**
     * count avatar of client
     * @param  int $clientID 
     * @return int           
     */
    public function count_avatar($clientID)
    {

        return $this->model('Mavatar')->count_avatars($clientID);
    
    }

    /**
     * generate default avatar for client
     * @param  int $clientID 
     * @param  array $fields   $imgOrder and default filename 
     * @return array           
     */
    public function generate_avatar($clientID, $fields)
    {

        $this->model('Mavatar')->insert_avatar(array(
            'clientID'  => $clientID,
            'imgOrder'  => $fields['imgOrder'],
            'filename'  => $fields['filename'],
            'updatedOn' => date('Y-m-d H:i:s'),
            'adminID'   => 0,
            'status'    => 0,
            'isActive'  => 0
        ));
        
        return $this->format_avatar(array(
                'filename' => $fields['filename'],
                'imgOrder' => $fields['imgOrder'],
                'status'   => 0,
                'isActive' => 0
            ));

    }

    /**
     * get avatars of client
     * @param  int $clientID     
     * @param  int $avatar_count not of max avatar
     * @return array
     */
    public function get_avatars($clientID)
    {
        $avatars_arr = $this->get_all_avatars($clientID);
        $avatars     = array();
        foreach ($avatars_arr as $avatar) {

            $avatars[$avatar->imgOrder]  = $this->format_avatar($avatar);

        }

        return $avatars;
    }

    /**
     * This will format array of avatar for front end
     * @param  array $avatar filename,imgOrder,status, isActive
     * @return array         
     */
    private function format_avatar($avatar)
    {

        return array(
                'filename' => $avatar['filename'],
                'imgOrder' => $avatar['imgOrder'],
                'status'   => $avatar['status'],
                'isActive' => $avatar['isActive']
            );
    }

    /**
     * This will generate player sessionID
     * @return string
     */
    public function generate_sessionID($max_try)
    {
        $try = 0;

        while ($try <= $max_try) {

            $sesssionID      = str_random(32);
            $count_sessionID = $this->model('Mclientsession')->count_sessionID($sesssionID);

            if ($count_sessionID <= 0) {

                return $sesssionID;

            }

            $try++;

        }

        return false;
    }

    /**
     * This will login player to DB
     * @param  int    $client         client informations
     * @param  string $new_sessionID
     * @param  string $webTypeID
     * @return array/boolean          This will return false if no rows are affected
     */
    public function db_login($client, $new_sessionID, $webTypeID)
    {
        $today = date('Y-m-d H:i:s');

        $login_data =  array(
                        'isLogin'       => 1,
                        'isOnline'      => 1,
                        'sessionID'     => $new_sessionID,
                        'lastLoginIP'   => get_ip(),
                        'lastLogin'     => $today,
                        'lastActivity'  => $today,
                        'webTypeID'     => $webTypeID,
                        'wrongPassword' => 0
                    );

        $update_client = $this->model('Mclient')->update_login_information(array($client['clientID']), $login_data);

        if ($update_client > 0) {
            
            // merge updates
            assoc_array_merge($client, $login_data);

            // login log
            $this->model('Mloginlog')->insert_loginlog(array(
                'username'    => $client['username'],
                'sessionID'   => $client['sessionID'],
                'loginDate'   => $today,
                'createdOn'   => $today,
                'createdFrom' => 'Player Site',
                'ipAddress'   => $client['lastLoginIP'],
            ));
            
            return $client;

        } else {

            return false;

        }

    }

    /**
     * This will logout players in DB
     * @param  array $clientIDs list of clientIDs, because our site supports batch logout
     * @param  int   $lastPage 
     * @return array/boolean    [sessionIDs, clientIDs], This will return false if no rows are affected
     */
    public function db_logout($clientIDs, $lastPage, $sessionID)
    {
        $clients     = $this->model('Mclient')->get_isLogin_clients($clientIDs)->toArray();
        $sessionIDs  = array_pluck($clients,'sessionID');
        $clientIDs   = array_pluck($clients,'clientID');
        $logout_data = array(
                        'isLogin'   => 0,
                        'isOnline'  => 0,
                        'sessionID' => '',
                        'webTypeID' => NULL,
                    );

        // some logout dont need to set the lastPage, sample is from Admin site WS_LOGOUT request
        if ($lastPage !== false) {

            $logout_data['lastPage'] = ($lastPage >= 0 && $lastPage <= 3) ? $lastPage : 0;

        }

        $update_client = $this->model('Mclient')->update_login_information($clientIDs, $logout_data, $sessionID);

        if ($update_client > 0) {

            // login log
            $this->model('Mloginlog')->update_loginlog($sessionIDs, array('logoutDate' => date('Y-m-d H:i:s')));

            return compact('sessionIDs','clientIDs','clients');

        } else {

            return false;

        }
    }

    /**
     * check if image order of client's avatar is available for uploading or setting as profile avatar
     * @param  int $clientID 
     * @param  int $imgOrder 
     * @return bool           
     */
    public function check_availability($clientID, $imgOrder, $filter)
    {
        $avatar = null;

        switch ($filter) {
            case 'upload_avatar':
                
                $avatar = $this->model('Mavatar')->check_available_upload($clientID, $imgOrder); break;
            
            case 'set_primary':

                $avatar = $this->model('Mavatar')->check_set_primary($clientID, $imgOrder);break;
        }

        return !(is_null($avatar));

    }

    /**
     * Update image of client and set for pending 
     * @param int $clientID 
     * @param int $imgOrder 
     * @param string $filename name of file 
     */
    public function set_uploaded_avatar($clientID, $imgOrder, $filename)
    {

        return $this->model('Mavatar')->set_avatar($clientID,$imgOrder,array(
            'filename' => $filename, 
            'status'   => 2, 
            'isActive' => 0 
            ));
    }

    /**
     * get all avatars of client
     * @param  int $clientID 
     * @return obejct           
     */ 
    public function get_all_avatars($clientID)
    {
       
        return $this->model('Mavatar')->get_info($clientID);

    }

    /**
     * Reset active avatar then set a new profile avatar
     * @param int $clientID 
     * @param int $imgOrder 
     * @return  boolean 
     */
    public function set_primary_avatar($clientID, $imgOrder)
    {

        $this->model('Mavatar')->reset_active($clientID,$imgOrder);

        $set_primary = $this->model('Mavatar')->set_primary($clientID, $imgOrder);

        return $set_primary > 0;

    }
    
    /**
     * This will get site assigned agent
     * @param  string $whiteLabelID        
     * @param  int    $currencyID         
     * @param  array  $registration_parents 
     * @return array
     */
    public function get_assigned_agent($whiteLabelID, $currencyID, $registration_parents)
    {
        if ($whiteLabelID == '') {

            $parent = $this->model('Mclient')->walkin_currencyID_agent($currencyID, $registration_parents);

        } else {

            $parent = $this->model('Mclient')->wl_agent($whiteLabelID);

        }

        return ($parent) ? $parent->toArray() : $parent;
    }

    /**
     * get client sessionID
     * @param  int $clientID 
     * @return string           
     */
    public function get_sessionID($clientID)
    {

        $sessionIDs = $this->model('Mclientsession')->get_sessionID($clientID)->toArray();
        $clientIDs_sessionID = array();

        foreach ($sessionIDs as $sessionID) {

            $clientIDs_sessionID[] = $sessionID['sessionID'];
        }

        return $clientIDs_sessionID;
    }

    /**
     * get inactive clientIDs
     * @param  string $last_timestamp 
     * @return array                 
     */
    public function get_inactive_clientIDs($last_timestamp)
    {

        $inactive_clientIDs = $this->model('Mclientsession')->get_inactive_clientIDs($last_timestamp);

        $clientIDs = array();
        foreach ($inactive_clientIDs as $inactive_clientID) {
            $clientIDs[] = $inactive_clientID->clientID;
        }

        return $clientIDs;

    }

    /**
     * update unverified account
     * @param  string $prev_signupDate 
     * @return array                  
     */
    public function update_unverified_account($prev_signupDate)
    {
        $unverified_accounts = $this->model('Mclient')->get_unverified($prev_signupDate);
        $unverified_players  = array();
        foreach ($unverified_accounts as $account) {

            $verificationCode     = generate_verification_code(random_number(12));
            $unverified_players[] = $account->toArray();

            $this->model('Mclient')->update_registration(
                $account->clientID,
                array(
                    'playerregistration.verificationCode' => $verificationCode,
                    'signupDate'                          => date("Y-m-d H:i:s")
                )
            );

        }
        return array(
            'deleted'            =>count($unverified_players),
            'unverified_players' => $unverified_players
        );
        
    }

    /**
     * This will get client via sessionID
     * @param  string $sessionID 
     * @return array
     */
    public function client_by_sessionID($sessionID) 
    {
        return $this->model('Mclient')->client_by_sessionID($sessionID)->toArray();
    }

    /**
     * get clientIDs
     * @param  string $webTypeName 
     * @return array              
     */
    public function get_clientIDs($webTypeName, $wlMode)
    {      
        switch ($wlMode) {
            case '2':
                $clientIDs_array = $this->model('Mclientsession')->clientIDs_not_TestPlayer($webTypeName);
                break;
            
            default:
                $clientIDs_array = $this->model('Mclientsession')->clientIDs_by_webTypeName($webTypeName);
                
                break;
        }

        $clientIDs       = array();

        foreach ($clientIDs_array as $clientID) {
            $clientIDs[] = $clientID->clientID;
        }

        return $clientIDs;

    }

    /**
     * get last activity of clientsession and player's info
     * @param  int $clientID 
     * @return array           
     */
    public function get_player_lastActivity($clientID)
    {

        $client = $this->model('Mclient')->player_lastActivity($clientID);

        return $client ? $client->toArray() : array();

    }

    /**
     * get unregistered client
     * @return array 
     */
    public function get_unregistered()
    {
        
        $unregistered = $this->model('Mclient')->unregistered();

        return $unregistered ? $unregistered->toArray() : array();

    }

    /**
     * update last activity of player
     * @param  int $clientID 
     * @return int           
     */
    public function update_lastActivity($clientID)
    {

        return $this->model('Mclientsession')->update_lastActivity($clientID, array('lastActivity' => date('Y-m-d H:i:s')));

    }

    /**
     * Count player blocklist
     * @param  int $clientID 
     * @return int           
     */
    public function count_player_blocklist($clientID)
    {

        return $this->model('Mplayerblacklist')->count_player_blocklist($clientID);
      
    }

    /**
     * insert clientbalance
     * @param   array $clientIDs   agentID and clientID
     * @param   int $pokerlimit     default pokerlimit every registration
     * @return void            
     */
    public function insert_clientbalance($clientIDs, $pokerlimit = null)
    {
        $clientbalance = array('clientID' => $clientIDs['clientID']);
        if (!is_null($pokerlimit)) {

            $is_deducted = $this->model('Mclientbalance')->deduct_pokerAvailableLimit($clientIDs['agentID'], $pokerlimit);

                if (!$is_deducted) {
                    $pokerlimit = $this->model('Mclientbalance')
                        ->remaining_pokerAvailableLimit($clientIDs['agentID'], $pokerlimit);

                    if (is_null($pokerlimit)) {
                        $pokerlimit = 0;
                    } else {
                        $this->model('Mclientbalance')->deduct_pokerAvailableLimit($clientIDs['agentID'], $pokerlimit);
                    }
                }

            $clientbalance['pokerLimit']          = $pokerlimit;
            $clientbalance['pokerAvailableLimit'] = $pokerlimit;

           }
        
        $this->model('Mclientbalance')->insert_clientbalance($clientbalance);

    }

    public function generate_marketing_token($max_try, $clientID)
    {
        $try = 0;

        while ($try <= $max_try) {

            $marketingToken       = str_random(32);
            $count_marketingToken = $this->model('Mclientsession')->count_marketingToken($marketingToken);

            if ($count_marketingToken <= 0) {
                $this->model('Mclientsession')->set_by_clientID($clientID, array('marketingToken' => $marketingToken));
                //insert marketing token in DB
                return $marketingToken;

            }

            $try++;

        }

        return false;
    }

    /**
     * Check if player is test player
     * @param  array  $user                 
     * @param  string  $whiteLabelID         
     * @param  array  $test_agent_whitelist 
     * @return boolean                       
     */
    public function isTestPlayer($user, $whiteLabelID, $test_agent_whitelist)
    {
        if (Auth::check()) {
            return Auth::user()->isTestPlayer;
        } elseif (isset($user['loginName'])){
            return $this->model('Mclient')->isTestPlayer($user['loginName'], $whiteLabelID, $test_agent_whitelist)['isTestPlayer'];
        }

        return false;
    }

}