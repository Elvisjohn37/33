<?php

namespace Backend\controllers;

use Redirect;
use Request;
use Config;
use Input;
use Auth;
use Lang;
use Exception;
use Layer;

/**
 * Cuser for getting user data, and authentication
 * 	
 * @author PS Team
 */
class Cuser extends Basecontroller {

    /**
     * Entry point of getting user data
     * 
     * @return mixed
     */
    public function get_user()
    {

        $type = strtolower(Input::get('g'));

        $this->service('Svalidate')->validate(array(

            'required_param' => array(
                                    'value' => array(
                                                'type'  => 'hidden',
                                                'input' => $type
                                            )
                                )

        ), true);

        switch ($type) {

            case 'security_question': return $this->get_securityQuestion();

        }
        
    }

    /**
     * Entry point for processing form data and user data
     * 
     * @return mixed
     */
    public function process_user() 
    {
        $process = strtolower(Input::get('ps_form-process'));
        
        $this->service('Svalidate')->validate(array(
            
            'required_param' => array(
                                    'value' =>  array(
                                                    'type'  => 'hidden',
                                                    'input' => $process
                                                ),
                                )
        ), true);
        
        switch ($process) {
            
            case 'generate_display_name'  : return $this->generate_displayName();
            case 'player_registration'    : return $this->registration();
            case 'ps_change_display_name' : return $this->user_update_displayName();
            case 'register_friend'        : return $this->register_friend();
            case 'change_login_password'  : return $this->change_isFirstLogin_credentials();
            case 'expired_password'       : return $this->expired_reset_password();
            case 'change_password'        : return $this->change_account_password();
            case 'new_password'           : 

                if (Auth::check()) {
                
                    return $this->expired_reset_password();

                } else {

                    return $this->change_lostPasswordCode_password();
                }

            case 'lost_password'          : return $this->lost_password();
        }
        
    }

    /**
     * Validate user specific field via ajax
     * Route: validate 
     * 
     * @return array
     */
    public function validate() 
    {
        
        $field = Input::get('field');
        $value = Input::get('value');

        // compose validator arguments
        switch ($field) {

            case 'ps_change_display_name':

                $validator_name = 'displayName';
                $value          = array(
                                    'displayName'   => $value,
                                    'client'        => Auth::user()
                                );

                break;
        }

        return $this->service('Svalidate')->validate(array(
            $field => array(
                        'value'         => $value, 
                        'validator'     => $validator_name
                    )
        ));
    }

    /**
     * activate_account activate player account 
     * Inherit parent betsetting and clienttablelimit
     * 
     * @param  string   $code verification code use to activate account
     * @return redirect the result
     */
    public function activate_account($code) 
    {
        $client = $this->repository('Rplayer')->ids_by_verificationCode($code);

        if (count($client) > 0 && $this->repository('Rplayer')->activate_account($client->clientID,$client->parentID)) {

            $this->repository('Rplayer')->inherit_betsetting($client->clientID, $client->parentID);

            $this->repository('Rplayer')->inherit_clienttablelimit($client->clientID, $client->parentID);

            $this->service('Ssession')->add_flash(array(
                'result'  => true,
                'message' => array(
                                '{{@lang.language.registration_complete_header}}',
                                '{{@lang.messages.registration_complete_msg}}'
                            )
            ), 'success');

        } else {
            // Security: Guests should not have any idea if player actually exists
            // so we will set verified as default response
            try {
                // automatic false, we're just getting the error code from validate
                $this->service('Svalidate')->validate(array(
                    'truthy' => array('value'=> array('input' => false, 'type' => 'activate_account'))
                ), true);
            } catch(Exception $e) {
                $this->service('Ssession')->add_flash(json_decode($e->getMessage()));
            };
        }

        return Redirect::to('/');
    }

    /**
     * Check if current user is logged in
     * Route: verify_auth
     * 
     * @return array
     */
    public function is_login() 
    {   

        $this->service('Svalidate')->validate(array(
            'site_access'        => array(
                                        'value' => array()
                                    ),
            
            'is_login' => array('value'=> array())
        ),true);
        
        return array('result' => true);
    }


    /**
     * reset lost password
     * 
     * @param  string $code lostPasswordCode for reset
     * @return redirect
     */
    public function reset_password($code)
    {
        try {

            $this->service('Svalidate')->validate(array('guest_access' => array('value'=> array())), true);

        } catch(Exception $e) {

            $this->service('Ssession')->add_flash(json_decode($e->getMessage()));
            return  Redirect::to('/');

        }

        $client = $this->repository('Rplayer')->reset_lostPasswordCode($code);

        if ($client) {
            $this->service('Ssession')->put_lost_password($client);
            $this->service('Ssession')->add_flash($code, 'reset_password');
        }

        return  Redirect::to('/');
    }
    
    /**
     * retrieve security question of client before login
     * used mainly for forget password
     * 
     * @return array
     */
    private function get_securityQuestion() 
    {
        $captcha_input_feild = 'ps_lost_captchainput';
        
        // validate captcha
        $this->service('Scaptcha')->check(Input::get($captcha_input_feild), $captcha_input_feild );
        
        // get security question
        $loginName  = Input::get('ps_lost_loginName');
        $email      = Input::get('ps_lost_email');
        
        $this->service('Svalidate')->validate(array(
            
            'ps_lost_loginName' => array(
                                    'value'     => array(
                                                    'input' => $loginName, 
                                                    'type'  => 'single'
                                                ),
                                    'validator' => 'required_param'
                                ),
            
            'ps_lost_email'     => array(
                                    'value'     => array(
                                                    'input' => $email, 
                                                    'type'  => 'single'
                                                ),
                                    'validator' => 'required_param'
                                )
            
        ));
        
        $whiteLabelID = Config::get("settings.WL_CODE");
         
        $securityQuestion = $this->repository('Rplayer')->get_securityQuestion( $loginName, $email, $whiteLabelID );

        $this->service('Svalidate')->validate(array(

            'securityQuestion' => array(
                                    'value' => array(
                                                    'input' => $securityQuestion,
                                                    'type'  => 'securityQuestion'
                                            ),
                                    'validator' =>'truthy'
                                )

        ), true);

        return array(
            'result'            => TRUE, 
            'securityQuestion'  => $securityQuestion
        );
    }
    
    /**
     * Assign a unique display name 
     *  
     * @return array
     */    
    private function generate_displayName() 
    {
        $displayName = $this->repository('Rplayer')->generate_displayName(
                        Auth::user()->clientID, 
                        Config::get('settings.user.displayName_prefix'),
                        Config::get('settings.user.displayName_max_try')
                    );

        $this->service('Svalidate')->validate(array(

            'truthy' => array(

                            'value'    => array(
                                            'input' => $displayName['result'],
                                            'type'  => 'displayName'
                                        ),

                            'callback' => function() use($displayName) {

                                            $this->service('Slogger')->file(array(
                                                'message' => 'Failed display name generation',
                                                'status'  => Auth::user()->displayNameStatus,
                                                'return'  => $displayName
                                            ), 'ERROR', 'display_name');

                                        }
                        )

        ), true);

        return $this->update_displayName_success($displayName);
    }

    /**
     * This will process the udpating of display name base on user input
     * @return array
     */
    private function user_update_displayName()
    {
        $displayName = Input::get('ps_player_display_name');

        $this->service('Svalidate')->validate(array(

            'displayName' => array(

                                'value' => array(
                                            'displayName'   => $displayName,
                                            'client'        => Auth::user()
                                        )
                            )

        ));

        $displayName_update = $this->repository('Rplayer')->update_displayName(Auth::user()->clientID,$displayName);

        $this->service('Svalidate')->validate(array(

            'truthy' => array(

                            'value'    => array(
                                            'input' => $displayName_update['result'],
                                            'type'  => 'displayName'
                                        ),

                            'callback' => function() use($displayName_update) {

                                            $this->service('Slogger')->file(array(
                                                'message' => 'Failed display name user input change',
                                                'status'  => Auth::user()->displayNameStatus,
                                                'return'  => $displayName_update
                                            ), 'ERROR', 'display_name');

                                        }
                        )

        ), true);

        return $this->update_displayName_success($displayName_update);

    }

    /**
     * This will execute common process when updateing of display name was successful
     * @param  array  $displayName  result from updating displayName
     * @return array
     */
    private function update_displayName_success($displayName)
    {

        // log
        $this->service('Slogger')->client_changes($displayName);

        // respond
        return array(
            'result'        => TRUE, 
            'displayName'   => $displayName['displayName'],
            'message'       => array(
                                '{{@lang.language.set_display_name}}',
                                '{{@lang.messages.generated_display_name}} '.$displayName['displayName']
                            )
        );

    }
    
    /**
     * Register new walk in player
     * 
     * @return array
     */
    private function registration() 
    {
        // validate captcha
        $this->service('Scaptcha')->check( Input::get('ps_reg_captchainput'), 'ps_reg_captchainput' );

        // Client basic data from input
        $client_data = array(
            'firstName'        => Input::get('ps_reg_firstName'),
            'lastName'         => Input::get('ps_reg_lastName'),
            'email'            => Input::get('ps_reg_emailAddress'),
            'bankName'         => Input::get('ps_reg_bankName'),
            'accountBankName'  => Input::get('ps_reg_accountBankName'),
            'mobile'           => Input::get('ps_reg_mobileNo'),
            'currencyID'       => Input::get('ps_reg_currency'),
            'isWalkIn'         => Config::get('settings.user.new_player_isWalkIn'),
            'loginName'        => strtolower(Input::get('ps_reg_loginName')),
            'securityQuestion' => Input::get('ps_reg_securityQuestion'),
            'yourAnswer'       => Input::get('ps_reg_yourAnswer'),
            'password'         => Input::get('ps_reg_password'),
            'whiteLabelID'     => Config::get('settings.WL_CODE')
        );

        $max_field_value  = config::get('settings.user.max_field');
        $bankAccount_info = config::get('settings.input_fields.bank_account_info'); 

        $this->service('Svalidate')->validate(array(
            'ps_reg_firstName'           => array( 
                                                'value'     => array(
                                                                'input'     => $client_data['firstName'],
                                                                'max_value' => $max_field_value['firstName']
                                                            ),
                                                'validator' => 'name'
                                            ),
            'ps_reg_lastName'            => array( 
                                                'value'     => array(
                                                                'input'     => $client_data['lastName'],
                                                                'max_value' => $max_field_value['lastName']
                                                            ),
                                                'validator' => 'name'
                                            ),
            'ps_reg_loginName'           => array(
                                                'value'     => array(
                                                                'loginName' => $client_data['loginName'],
                                                                'user'      => $client_data
                                                            ),
                                                'validator' => 'loginName'
                                            ),
            'ps_reg_password'            => array(
                                                'value'     => array(
                                                                'new_password' => $client_data['password'],
                                                                'user'         => $client_data
                                                            ),
                                                'validator' => 'new_password'
                                            ),
            'ps_reg_retypePassword'      => array(
                                                'value'     => array(
                                                                'value_1' => $client_data['password'],
                                                                'value_2' => Input::get('ps_reg_retypePassword'),
                                                                'type'    => 'password'
                                                            ),
                                                'validator' => 'same_value'
                                            ), 
            'ps_reg_emailAddress'        => array(
                                                'value'     => array(
                                                                'email_address' => $client_data['email'],
                                                                'max_value'     => $max_field_value['email'],
                                                                'type'          => 'register'
                                                            ),
                                                'validator' => 'email_address',
                                                'callback'  => function($validation_result) use ($client_data){

                                                                $this->service('Ssession')->put(
                                                                    'resend_email', $client_data['email']
                                                                );
                                                }
                                            ),
            'ps_reg_confirmEmailAddress' => array(
                                                'value'     => array(
                                                                'value_1' => $client_data['email'],
                                                                'value_2' => Input::get('ps_reg_confirmEmailAddress'),
                                                                'type'    => 'email'
                                                            ),
                                                'validator' => 'same_value'
                                            ),
            'ps_reg_mobileNo'            => array(
                                                'value'     => $client_data['mobile'],
                                                'validator' => 'mobile_number'
                                            ),
            
            'ps_reg_currency'            => array(
                                                'value'     => $client_data['currencyID'],
                                                'validator' => 'currency'
                                            ),
            'ps_reg_securityQuestion'    => array(
                                                'value'     => $client_data['securityQuestion'],
                                                'validator' => 'securityQuestion_exist'
                                            ),            
            'ps_reg_yourAnswer'          => array(
                                                'value'     =>  array(
                                                                    'input' =>  $client_data['yourAnswer'],
                                                                    'max_value'     => $max_field_value['yourAnswer'],
                                                                    'type'  => 'single'
                                                                ),
                                                'validator' => 'yourAnswer'
                                            ),
        ));
        
        
        switch ($bankAccount_info) {
            case 'remove':
                

                $client_data['bankName']        = '';
                $client_data['accountBankNo']   = '';
                $client_data['accountBankName'] = '';

                break;

            default:
                $this->service('Svalidate')->validate(array(
                    'ps_reg_bankName'            => array(
                                                        'value'     => $client_data['bankName'],
                                                        'validator' => 'bankName'
                                                    ),
                    'ps_reg_accountBankName'     => array(
                                                        'value'     => array(
                                                                        'input'     => $client_data['accountBankName'],
                                                                        'max_value' => $max_field_value['accountBankName']
                                                                    ),
                                                        'validator' => 'accountBankName'
                                                    )
                ));
                // Client accountBankNo
                $bank_number_info = $this->repository('Rwhitelabel')->format_bank_number(

                                        array(
                                            Input::get('ps_reg_bankName_BankInput1'),
                                            Input::get('ps_reg_bankName_BankInput2'),
                                            Input::get('ps_reg_bankName_BankInput3'),
                                            Input::get('ps_reg_bankName_BankInput4'),
                                            Input::get('ps_reg_bankName_BankInput5')
                                        ), 

                                        $client_data['bankName']

                                    );

                $this->service('Svalidate')->validate(array(

                    'ps_reg_bankName_BankInput1' => array(
                                                        'value'     => $bank_number_info,
                                                        'validator' => 'bank_input'
                                                    )
                )); 

                $client_data['accountBankNo'] = $bank_number_info['accountNumber'];
                break;
        }
        



        // Client data from assigned agent
        $agent = $this->service('Ssiteconfig')->get_site_agent($client_data['currencyID']);

        // validate agent
        $this->service('Svalidate')->validate(array(
            'walkin_agent' => array(
                                'value'     => array(
                                                'input' => $agent,
                                                'type'  => 'walkin_agent'
                                            ),
                                'validator' => 'truthy'
                            )
        ), true);

        $client_data['parentID']       = $agent['clientID']; 
        $client_data['languageID']     = $agent['languageID'];
        $client_data['isCompany']      = $agent['isCompany'];
        $client_data['isTestPlayer']   = $agent['isTestPlayer'];
        $client_data['isCashFlow']     = $agent['isCashFlow'];
        $client_data['isBot']          = $agent['isBot'];
        $client_data['jurisdictionID'] = $agent['jurisdictionID']; 
        
        //all critical insert to DB that needed to be rollback if not successfully
        $registered_client = $this->service('Sdbcritical')->registration($client_data);

        $this->service('Svalidate')->validate(array(
            'registered_client' => array(
                                        'value'     => array (

                                                            'input' => $registered_client['result'],
                                                            'type'  => 'registered_client'
                                                        ),

                                        'validator' => 'truthy'

                                    )
        ), TRUE);

        // soft delete the new generated client
        $soft_delete = function($error) use ($registered_client) {
                        $this->repository('Rplayer')->client_soft_delete($registered_client['clientID']);
                    };

        // validate again new player 
        $this->service('Svalidate')->validate(array(
            
            // validate commission effective
            'commision_effective' => array(
                                        'value'     => array (

                                                            'input' => $registered_client['commissioneffective'],
                                                            'type'  => 'commissioneffective'
                                                        ),

                                        'validator' => 'truthy',
                                        'callback'  => $soft_delete

                                    ),
            
            // check if there are applicable products
            'client_product'      => array(
                                        'value'     => array (

                                                            'input' => $registered_client['clientproduct'],
                                                            'type'  => 'clientproduct'
                                                        ),
                                        
                                        'validator' => 'truthy',
                                        'callback'  => $soft_delete
                                    )
            
        ), TRUE);
        
        // send email to user
       $data = array(
                'subject'               => Lang::get("custom.email.register_subject"),
                'view'                  => 'emails.ps_login_form.registration',
                'to'                    => $client_data['email'],
                'lastName'              => $client_data['lastName'],
                'firstName'             => $client_data['firstName'],
                'loginName'             => $client_data['loginName'],
                'password'              => $client_data['password'],
                'isDisplayCredential'   => TRUE,
                'code'                  => $registered_client['verificationCode'],
                'expiration'            => Config::get('settings.EMAIL_EXPIRATION')
            );

       // Send account verification e-mail.
       
       $email_status = $this->service('Semails')->send($data);

       $this->service('Svalidate')->validate(array( 

           'email_sender' => array(
                                'value'     => array('input' => $email_status),
                                'validator' => 'truthy',
                                'callback'  => $soft_delete
                            )

       ), TRUE);

        return array(
            'result'    =>  TRUE,
            'message'   =>  array('{{@lang.language.account_registration}}','{{@lang.messages.registration_done}}')
        );
    }

    /**
     * Register friend
     * @return array          
     */
    private function register_friend()
    {

        $this->service('Svalidate')->validate(array(
            'site_access'        => array('value' => array()),
            'transaction_access' => array('value' => array('auth_user' => Auth::user()))
        ), true);

        $max_field_value = config::get('settings.user.max_field');
        $bankAccount_info = config::get('settings.input_fields.bank_account_info'); 

        $friend_data = array(
                        'firstName'        => Input::get('ps_regf_firstName'),
                        'lastName'         => Input::get('ps_regf_lastName'),
                        'email'            => Input::get('ps_regf_emailAddress'),
                        'bankName'         => Input::get('ps_regf_bankName'),
                        'accountBankName'  => Input::get('ps_regf_accountBankName'),
                        'mobile'           => Input::get('ps_regf_mobileNo'),
                        'parentID'         => Auth::user()->parentID,
                        'whiteLabelID'     => Auth::user()->whiteLabelID
                    );

        $this->service('Svalidate')->validate(array(
            'ps_regf_firstName'       => array(
                                            'value'     => array(
                                                            'input'     => $friend_data['firstName'],
                                                            'max_value' => $max_field_value['firstName']
                                                        ),
                                            'validator' => 'name'
                                        ),

            'ps_regf_lastName'        => array(
                                            'value'     => array(
                                                'input'     => $friend_data['lastName'],
                                                'max_value' => $max_field_value['lastName']),
                                            'validator' => 'name'
                                        ),

            
            'ps_regf_emailAddress'    => array(
                                            'value'     => array(
                                                'email_address' => $friend_data['email'],
                                                'max_value'     => $max_field_value['email'],
                                                'type'          => 'register_friend',
                                                'whiteLabelID'  => $friend_data['whiteLabelID']
                                            ),
                                            'validator' => 'email_address',
                                            'callback'  => function() use ($friend_data){

                                                            $this->service('Ssession')->put(
                                                                'resend_email', $friend_data['email']
                                                            );
                                            }
                                        ),
            'ps_regf_mobileNo'        => array(
                                            'value'     => $friend_data['mobile'],
                                            'validator' => 'mobile_number'
                                        ),

        ));

        switch ($bankAccount_info) {
            case 'remove':

                $friend_data['bankName']        = '';
                $friend_data['accountBankNo']   = '';
                $friend_data['accountBankName'] = '';

                break;
            
            default:

                $this->service('Svalidate')->validate(array(
                    'ps_regf_bankName'        => array(
                                                    'value'     => $friend_data['bankName'],
                                                    'validator' => 'bankName'
                                                 ),     
                    'ps_regf_accountBankName' => array(
                                                    'value'     => array(
                                                                        'input'     => $friend_data['accountBankName'],
                                                                        'max_value' => $max_field_value['accountBankName']
                                                    ),
                                                    'validator' => 'accountBankName'
                                                )
                ));
                $bank_number_info = $this->repository('Rwhitelabel')->format_bank_number(

                                        array(
                                            Input::get('ps_regf_bankName_BankInput1'),
                                            Input::get('ps_regf_bankName_BankInput2'),
                                            Input::get('ps_regf_bankName_BankInput3'),
                                            Input::get('ps_regf_bankName_BankInput4'),
                                            Input::get('ps_regf_bankName_BankInput5')
                                        ), 

                                        $friend_data['bankName']

                                    );

                $this->service('Svalidate')->validate(array(

                    'ps_regf_bankName_BankInput1' => array(
                                                        'value'     => $bank_number_info,
                                                        'validator' => 'bank_input'
                                                    )
                    
                ));

                $friend_data['accountBankNo'] = $bank_number_info['accountNumber'];  

                break;
        }
        

        $client_parent = $this->repository('Rplayer')->get_parent_information($friend_data['parentID']);

        if (count($client_parent) > 0) {

            $currencyID = $client_parent->currencyID;
            $languageID = $client_parent->languageID;

        } else {

            $currencyID = Config::get('settings.currency.base_currencyID');
            $languageID = Config::get('settings.currency.base_languageID');

        }


        $friend_data['referralID'] = Auth::user()->clientID;
        $friend_data['currencyID'] = $currencyID;
        $friend_data['languageID'] = $languageID;
        $friend_data['isWalkIn']   = $client_parent->isWalkIn;
        $friend_data['isBot']      = $client_parent->isBot;

        $friend_id = $this->repository('Rplayer')->insert_new_player($friend_data);
        
        // Add default pokerlimit for all direct registration in Player site
        $this->repository('Rplayer')->insert_clientbalance(
            array('clientID'=> $friend_id)
        );
        

        $this->service('Ssocket')->push(array(
            'session_id' => $this->repository('Rplayer')->get_agent_sessionIDs($friend_data['parentID']),
            'room'       => 'agent_site',
            'event'      => 'PS_'.strtoupper(get_first_chars( __FUNCTION__ ,'_')),
            'message'    => $this->service('Scrypt')->crypt_encrypt($friend_id)
        ));

        return array (
            'result'  => true,
            'message' => array('{{@lang.language.register_friend}}','{{@lang.messages.friend_registered}}')
        );
    }
    
    /**
     * This will let first login users change credential
     * @return array
     */
    private function change_isFirstLogin_credentials()
    {
        $this->service('Svalidate')->validate(array(
            'site_access'        => array('value' => array()),
            'transaction_access' => array('value' => array('auth_user' => Auth::user(), 'page' => 'change_credentials')),
            'isFistLogin'        => array(
                                        'value'     => array('input' => Auth::user()->isFirstLogin),
                                        'validator' => 'truthy'
                                    )
        ), true);

        $this->change_credentials_procedure(

            array(
                'loginName' => array(
                                'input_name'       => 'ps_change_loginName',
                            ),
                'password'  => array(
                                'input_name'       => 'ps_change_new_password',
                                'current_password' => 'ps_change_current_password',
                                'confirm_password' => 'ps_change_new_retype_password',
                                'can_reuse_old'    => false
                            )
            ),

            Auth::user(),

            'First Login'

        );
        
        $this->service('Ssocket')->push_main(
            $this->service('Ssession')->sessionID(),
                array('event' => 'REFRESH')
            );
        return array(
            'result'  => true,
            'message' => array(
                            '{{@lang.language.change_credentials}}',
                            '{{@lang.messages.change_credentials_success}}'
                        )
        );
    }

    /**
     * This is for account > change password
     * @return array
     */
    private function change_account_password()
    {
        $this->service('Svalidate')->validate(array(
            'site_access'        => array('value' => array()),
            'transaction_access' => array('value' => array('auth_user' => Auth::user()))
        ), true);

        return $this->change_credentials_procedure(

            array(

                'password' => array(
                                'input_name'       => 'ps_changePass_new_password',
                                'current_password' => 'ps_changePass_current_password',
                                'confirm_password' => 'ps_changePass_new_retype_password',
                                'can_reuse_old'    => false
                            )
            ),

            Auth::user(),

            'Account Page'

        );
    }

    /**
     * change password if expired of password reset by agent
     * @return array 
     */
    private function expired_reset_password()
    {
        $this->service('Svalidate')->validate(array(
            'site_access'        => array('value' => array()),
            'transaction_access' => array('value' => array(
                                                        'auth_user' => Auth::user(),
                                                        'page'      => 'expired_password'
                                                    )
                                    )
        ), true);
        
        if($this->service('Ssession')->get('checkPassReset')) {
            $remarks[] = 'Reset';
        }

        if ($this->service('Ssiteconfig')->is_password_expired(Auth::user()->lastChangePassword)) {
           $remarks[] = 'Expired';
        }

        $remarks         = implode(' & ', $remarks);
        $change_password = $this->change_credentials_procedure(
                            array(
                                'password' => array(
                                                'input_name'       => 'ps_changePass_new_password',
                                                'confirm_password' => 'ps_changePass_new_retype_password',
                                                'can_reuse_old'    => false
                                            )
                            ),
                            Auth::user(),
                            $remarks
                        );
        
        if (!is_null($this->service('Ssession')->get('checkPassReset'))) {
            $this->service('Ssession')->put('checkPassReset', 0);
        }

        $this->service('Ssocket')->push_main(
            $this->service('Ssession')->sessionID(),
            array('event' => 'REFRESH')
        );
        return $change_password;
    }

    /**
     * Change new password if password lost 
     * @return array 
     */
    private function change_lostPasswordCode_password()
    {
        $lost_password_sessions = $this->service('Ssession')->get_lost_password();
        $this->service('Svalidate')->validate(array(
            'code' => array(
                        'value'     => array(
                                        'value_1' => Input::get('code'),
                                        'value_2' => $lost_password_sessions['lostPasswordCode'],
                                        'type'    => 'reset_code'
                                    ),
                        'validator' => 'same_value'
                    )
        ), true);

       $change_password_process = $this->change_credentials_procedure(

                                    array(

                                        'password' => array(
                                                        'input_name'       => 'change_password',
                                                        'confirm_password' => 'change_confirmPassword',
                                                        'can_reuse_old'    => true
                                                    )
                                    ),

                                    $lost_password_sessions, // Auth::user()

                                    'Lost'
                                );

        $this->service('Ssession')->forget_lost_password();
        return $change_password_process;
    }

    /** 
     * This will handle the common procedure for changing user credentials (loginName and password)
     * @param  array  $input_names   [
     *                                   <credential name> => [
     *                                                             'input_name' => <credential input name>,
     *                                                             additional credential informations
     *                                                        ]
     *                               ]
     *                               e.g.
     *                               [
     *                                    'password' => [
     *                                                       'input_name'       => 'ps_changePass_new_password',
     *                                                       'current_password' => 'ps_changePass_current_password',
     *                                                       'confirm_password' => 'ps_changePass_new_retype_password',
     *                                                       'can_reuse_old'    => false
     *                                                  ]
     *                               ]
     * @param  array  $user_data
     * @param  string $process_name   
     * @return array                success message
     */
    private function change_credentials_procedure($input_names, $user_data, $process_name)
    {
        // Get the credentials that will be updated
        $new_creds         = array();
        $input_validations = array(); 

        /**
         * Input processing 
         * This will get the credentials that will need to be validated and to be updated
         */
        foreach ($input_names as $cred_name => $input_details) {

            $cred_input_name      = $input_details['input_name'];
            $new_cred[$cred_name] = Input::get($cred_input_name);

            switch ($cred_name) {

                case 'loginName':

                    $input_validations[$cred_input_name] = array(
                                                            'value'     => array(
                                                                            'loginName' => $new_cred[$cred_name],
                                                                            'user'      => $user_data
                                                                        ),
                                                            'validator' => 'loginName'
                                                        );

                    break;
                
                case 'password' :

                    /**
                     * This is optional 
                     * if the parent method passed current password then this will validate it 
                     */
                    if (isset($input_details['current_password'])) {

                        $curr_pass = $input_details['current_password'];

                        $input_validations[$curr_pass] = array(

                                                            'value'     => array(
                                                                            'input_password' => Input::get($curr_pass), 
                                                                            'password'       => $user_data['password'], 
                                                                            'salt'           => $user_data['salt']
                                                                        ), 
                                                            'validator' => 'current_password'
                                                        );
                    }

                    // new password validation
                    $input_validations[$cred_input_name] = array(
                                                            'value'     => array(
                                                                            'new_password' => $new_cred[$cred_name],
                                                                            'user'         => $user_data
                                                                        ),
                                                            'validator' => 'new_password'
                                                        );

                    // if not allowed reuse old password, then pass current user password & salt to validate it
                    if (!$input_details['can_reuse_old']) {
                        
                        $input_validations[$cred_input_name]['value']['old_password'] = $user_data['password'];
                        $input_validations[$cred_input_name]['value']['old_salt']     = $user_data['salt'];

                    }

                    // confirm password validation
                    $confirm_pass = $input_details['confirm_password'];

                    $input_validations[$confirm_pass] = array(
                                                        'value'     => array(
                                                                        'value_1' => Input::get($confirm_pass),
                                                                        'value_2' => $new_cred[$cred_name],
                                                                        'type'    => 'password'
                                                                    ),
                                                        'validator' => 'same_value'
                                                    );
                    break;
            }
        }

        // validate fields
        $this->service('Svalidate')->validate($input_validations);

        // update DB for new credentials
        $updated_credentials = $this->repository('Rplayer')->update_credentials($user_data['clientID'],$new_cred);

        // log 
        $this->service('Slogger')->client_changes($updated_credentials,$user_data,$process_name);
        
        // response - this might be ignored by the main method and set its own response.
        return array(
            'result'  => true,
            'message' => array('{{@lang.language.change_password}}','{{@lang.messages.new_password_success}}')
        );

    }

    /**
     * Reset lost password 
     * @return array 
     */
    private function lost_password()
    {   
        $client_data = array(
                        'loginName'  => Input::get('ps_lost_loginName'),
                        'email'      => Input::get('ps_lost_email'),
                        'yourAnswer' => Input::get('ps_lost_yourAnswer')
                    );

        $this->service('Svalidate')->validate(array(
            'ps_lost_loginName'  => array(
                                        'value'     => array(
                                                        'input' => $client_data['loginName'], 
                                                        'type'  => 'multiple'
                                                    ),
                                        'validator' => 'required_param'
                                    ),

            'ps_lost_email'      => array(
                                        'value'     => array(
                                                        'input' => $client_data['email'], 
                                                        'type'  => 'multiple'
                                                    ),
                                        'validator'  => 'required_param'
                                    ),

            'ps_lost_yourAnswer' => array(
                                        'value'     => array(
                                                        'input' => $client_data['yourAnswer'], 
                                                        'type'  => 'multiple'
                                                    ),
                                        'validator' => 'required_param'
                                    )
        ), true);

        $client = $this->repository('Rplayer')->set_lostPasswordCode($client_data, Config::get('settings.WL_CODE'));

        $this->service('Svalidate')->validate(array(
            'lostPasswordCode' => array(
                                    'value'     => array(
                                                    'input' => $client,
                                                    'type'  => 'securityQuestion'
                                                ),
                                    'validator' => 'truthy'
                                )
        ), true);

        // send email
        $this->service('Semails')->send(array(
            'subject'   => Lang::get("custom.email.reset_password_subject"),
            'view'      => 'emails.ps_login_form.reset_password',
            'to'        => $client_data['email'],
            'loginName' => $client_data['loginName'],
            'code'      => $client->lostPasswordCode,
            'firstName' => $client->firstName,
            'lastName'  => $client->lastName
        ));

        return array(
            'result'    => true, 
            'message'   => array('{{@lang.language.lost_password}}','{{@lang.messages.lost_password_success}}')
        );

    }

    /**
     * resend email registration
     * @return array   
     */
    public function resend_email_registration()
    {
        
        $email  = $this->service('Ssession')->pull('resend_email');

        $client = $this->repository('Rplayer')->client_by_email($email,Config::get('settings.WL_CODE'));


        //send email
        $email_status = $this->service('Semails')->send(array(
                        'subject'    => Lang::get('custom.email.register_subject'),
                        'view'       => 'emails.ps_login_form.registration',
                        'to'         => $email, 
                        'lastName'   => $client->lastName,
                        'firstName'  => $client->firstName,
                        'code'       => $client->verificationCode,
                        'expiration' =>  Config::get('settings.EMAIL_EXPIRATION')     
                    ));

        $this->service('Svalidate')->validate(array(
            'email_sender' => array(
                                'value'     => array('input' => $email_status),
                                'validator' => 'truthy'
                            )      
        ),TRUE);

        return array(
            'result'  => true,
            'message' => array('{{@lang.language.account_verification}}','{{@lang.messages.registration_done}}')
        );

    }

    /**
     * Accept Terms and conditions
     * @return array 
     */
    public function accept_terms_condition()
    {
        
        $gameIDs     = Config::get('settings.TOKEN_ON_API');
        $error_games = array();
        $clientID    = Auth::user()->clientID;
        $games       = $this->repository('Rproducts')->get_games($gameIDs);

        foreach ($games as $game) {
            try {
                $this->service('Sapi')->register_not_exist($clientID, Auth::user()->parentID, $game['serverID']);
            } catch (Exception $e) {
                $error_games[] = $game['gameName'];
            }   
        }
        
        if (count($error_games) > 0) {

            $this->service('Slogger')->file(array(
                'loginName'   => Auth::user()->loginName,
                'sessionID'   => Auth::user()->sessionID,
                'error_games' => $error_games
            ), 'failed_api_reg', 'process');

        }
        
        $this->repository('Rplayer')->accept_terms_condition($clientID, Auth::user()->isWalkIn);
        $this->service('Ssocket')->push_main(
            $this->service('Ssession')->sessionID(),
            array('event' => 'REFRESH')
        );

        return array('result' => true);

    }

    /**
     * This will authenticate loginName and password
     * If passed: This will assign porper sessions and redirect to correct page
     * @return array
     */
    public function login()
    {

        // inputs
        $login_inputs = array(
                            'loginName'   => Input::get('username'), 
                            'password'    => Input::get('password')
                        );
        $window_id    = Input::get('window_id', '');

        // prepare callback 
        $invalid_cred_callback = function(&$validation_result) { 

                                    $new_login_attempts = $this->service('Ssession')->add_login_attempts(); 

                                    // we will reference $validation_result to inject validation attempts
                                    assoc_array_merge($validation_result, $new_login_attempts);

                                };

        // validations that dont need user data
        $this->service('Svalidate')->validate(array(

            'site_access' => array(
                                'value'     => array('from_login' => true,'loginName' => $login_inputs['loginName'])
                            ),

            'has_captcha' => array(

                                'value'     => array(

                                                'input' => $this->service('Ssession')
                                                                ->get_login_attempts()['has_captcha'],

                                                'type'  => 'has_captcha'

                                            ),

                                'callback'  => $invalid_cred_callback,

                                'validator' => 'falsy'
                            ),

            'loginName'   => array(

                                'value'     => array(

                                                'input' => $login_inputs['loginName'],
                                                'type'  => 'login'

                                            ),

                                'callback'  => $invalid_cred_callback,

                                'validator' => 'required_param'
                            ),

            'password'    => array(

                                'value'     => array(

                                                'input' => $login_inputs['password'],
                                                'type'  => 'login'

                                            ),

                                'callback'  => $invalid_cred_callback,

                                'validator' => 'required_param'
                            ),

        ), true);
        
        // get user data
        $player = $this->repository('Rplayer')->for_login_verification(
                    $login_inputs['loginName'], 
                    Config::get('settings.WL_CODE'),
                    $this->service('Ssiteconfig')->test_agents_whitelist()
                );

        // validations that requires user data
        $this->service('Svalidate')->validate(array(

            'truthy'           => array(

                                    'value'    => array(
                                                    'input' => $player,
                                                    'type'  => 'login'
                                                ),
                                    'callback' => $invalid_cred_callback

                                ),

            'current_password' => array(

                                    'value'    => array(
                                                    'input_password' => $login_inputs['password'],
                                                    'password'       => $player['password'],
                                                    'salt'           => $player['salt'],
                                                    'type'           => 'login'
                                                ),

                                    'callback' => function(&$validation_result) use($invalid_cred_callback, $player) {

                                                    // call default login faied callback
                                                    $invalid_cred_callback($validation_result);

                                                    // update wrongPassword field
                                                    $max_wrong = Config::get('settings.user.max_wrongPassword_attempt');

                                                    $updated_data = $this->repository('Rplayer')->update_wrongPassword(
                                                                        $player,
                                                                        $max_wrong
                                                                    );

                                                    $this->service('Slogger')->client_changes($updated_data,$player);

                                                }
                                ),

            'login_access'     => array(

                                    'value'    => $player,
                                    'callback' => function(&$validation_result) use($player) {
                                                    switch (set_default($validation_result,'dcode',false)) {

                                                        case 'MSP':

                                                            $this->service('Ssession')->put(
                                                                'resend_email', $player['email']
                                                            );

                                                            // overwrite validation result
                                                            // command frontend to perform resend email
                                                            $validation_result = array(
                                                                                    'result'       => false,
                                                                                    'resend_email' => true,
                                                                                    'err_details'  => $validation_result
                                                                                );

                                                            break;
                                                        
                                                        case 'BIP':
                                                            
                                                            $this->service('Slogger')->file(array(
                                                                'username'  => $player['username'],
                                                                'loginName' => $player['loginName'],
                                                            ), 'BLOCKED_IP');

                                                            break;
                                                    }
                                                }

                                )

        ), true);
        
        // Make sure to logout first incase there's a login instance from other machine
        $this->logout($player['clientID'], 'login');
        

        $guest_sessionID = $this->service('Ssession')->guest_sessionID();
        // laravel Auth is checking if DB already has sessionID then session login will fail
        $sessionID       = $this->service('Ssession')->set_login_sessions($player);

        // login to onyx DB
        $webTypeID      = $this->repository('Rdbconfig')->get_webTypeID(Config::get('settings.WEBTYPE_NAME'));
        $login_instance = $this->repository('Rplayer')->db_login($player, $sessionID, $webTypeID);

        $this->service('Svalidate')->validate(array(
            'truthy' => array(
                            'value'    => array(
                                            'input' => $login_instance,
                                            'type'  => 'login_instance'
                                        ),

                            'callback' => function() use($player) {

                                // login aborted, revert old sessions
                                $this->service('Ssession')->revert();

                            }
                        )
        ) ,true);

        // notify agent
        $agent_sessionIDs = $this->repository('Rplayer')->get_agent_sessionIDs($player['parentID']);
        $this->service('Ssocket')->push(array(
            'room'       => 'agent_site',
            'session_id' => $agent_sessionIDs,
            'event'      => 'PS_li',
            'message'    => array('username' => $player['username'])
        ),'login');
        
        // notify other players window except the one requested
        $this->service('Ssocket')->push_main(
            $guest_sessionID,
            array('event' => 'REFRESH', 'message' => $window_id),
            'login'
        );

        // log file
        $this->service('Slogger')->file(
            array_only($login_instance, array('loginName','username','sessionID','clientID')), 
            'login', 
            'auth'
        );

        return array(
            'result'  => true, 
            'message' => array(
                '{{@lang.language.login}}',
                '{{@lang.messages.redirecting}}'
            )
        );
    }

    /**
     * This will logout clientID
     * @param  int/array $clientIDs if empty this will get from Auth::user()->clientID, 
     *                              this supports array of clientID
     * @param  string    $from      Default = normal, This keyword will tell logout where/what process called it
     *                              This is useful for our file logging
     * 
     * @return array
     */
    public function logout($clientIDs = false, $from = 'normal', $sessionID = null)
    {   
        // check if no clientIDs and is logged in
        if (!$clientIDs && Auth::check()) {
            $clientIDs = array(Auth::user()->clientID);
        }

        $logout_count = 0;
        // redirect only if no clientIDs, the client is fully logged out already
        if ($clientIDs) {

            // force clientIDs to be array
            if (!is_array($clientIDs)) {
                $clientIDs = array($clientIDs);
            }

            // logout from onyx DB
            $logout_instances = $this->repository('Rplayer')->db_logout(
                $clientIDs,
                Input::get('lastPage', false),
                $sessionID
            );

            // If false it means all clientIDs already been logged out previously from DB
            // and we only need to logout from site session
            if ($logout_instances) {

                $logout_count = count($logout_instances['clientIDs']);

                // $this->service('Sapi')->logout_third_parties($logout_instances['clientIDs']);

                $this->repository('Rproducts')->delete_inactive_websessions(
                    $logout_instances['clientIDs'], 
                    $this->service('Ssiteconfig')->websession_products()['delete']
                );

                if ($from != 'ws_notify') {
                    $this->service('Ssocket')->push_main(
                        $logout_instances['sessionIDs'],
                        array('event' => 'timeout'),
                        $from
                    );

                    // tangkas timeout
                    $this->service('Ssocket')->push_main(
                        $logout_instances['sessionIDs'],
                        array('event' => 'timeout','namespace' =>'games','room' => 'tangkas'),
                        $from
                    );
                }

                $this->service('Slogger')->file($logout_instances['clients'],'logout_'.$from,'auth');

                $this->service('Ssocket')->send_PS_lo($logout_instances['clients'], $from);

            }

            // Inactive and ws_notify is session less
            if (!in_array($from, array('inactive','ws_notify','ws_logout', 'api_logout'))) {
                $this->service('Ssession')->set_logout_sessions($clientIDs);
                //ps_timeout_session

            }

        }

        return array(
            'result'  => true, 
            'count'   => $logout_count, 
            'message' => array(
                '{{@lang.language.logout}}',
                '{{@lang.messages.redirecting}}'
            )
        ); 
    }

    /**
     * get avatars of client 
     * @return array 
     */
    public function get_avatars()
    {
        $clientID           = Auth::user()->clientID;
        $avatar_max_count   = Config::get('settings.avatar.max_count');
        $avatars            = $this->repository('Rplayer')->get_avatars($clientID);
        $generated_imgOrder = array();

        for ($avatar_max_count; $avatar_max_count >= 1; $avatar_max_count--) {

            if (!(array_key_exists($avatar_max_count, $avatars))) {

                $avatars[$avatar_max_count] = $this->repository('Rplayer')->generate_avatar($clientID,array(
                                                'imgOrder' => $avatar_max_count,
                                                'filename' => Config::get('settings.avatar.default_image')
                                            ));

                $generated_imgOrder[] = $avatar_max_count;

            }

        }

        if (count($generated_imgOrder) > 0) {

            $this->service('Slogger')->file(array(
                'message'            => 'Create default avatar',
                'generated_imgOrder' => $generated_imgOrder
            ), 'AVATAR');

        }

        return array_values($avatars);
    }

    /**
     * Upload avatar of client
     * @return array 
     */
    public function upload_avatar()
    {

        $imgOrder    = Input::get('ps_img_order');
        $clientID    = Auth::user()->clientID;
        $input_name  = 'ps_img_base64';
        $filename    = "{$clientID}_{$imgOrder}".date('ymdHis').".png";
        $imageBase64 = Input::file($input_name);
        $filesize    = filesize($imageBase64); 

            
        $this->service('Svalidate')->validate(array(

            'input_file'   => array(
                                'value'     => array(
                                                'input_name'   => $input_name,
                                                'mime_type'    => Config::get('settings.avatar.mime_type'),
                                                'max_filesize' => Config::get('settings.avatar.max_filesize')
                                            ),
                                'callback'  => function($error) use($filename, $filesize){

                                                if (set_default($error,'dcode',false) == 'MF') {

                                                    $this->service('Slogger')->file(
                                                        array(
                                                            'username' => Auth::user()->username,
                                                            'filename' => $filename,
                                                            'filesize' => "{$filesize} bytes"
                                                        ),
                                                        'AVATAR',
                                                        'ps_graphics'
                                                    );
                                                }
                                            }
                            ),

            'ps_img_order' => array(
                                'value'     => array(
                                                'imgOrder' => $imgOrder,
                                                'clientID' => $clientID,
                                                'filter'   => 'upload_avatar'
                                            ),
                                'validator' => 'imgOrder_availability'
                            )
        ),true);
        
        $move_file = $imageBase64->move(
                        $this->service('Ssiteconfig')->rso('avatar_ext','backend')['original'],
                        $filename
                    );

        $this->service('Svalidate')->validate(array(
            'upload_avatar' => array(
                                'value'     => array(
                                                'input' => $move_file
                                            ),
                                'validator' => 'truthy'
                            )
        ));
            
        $this->repository('Rplayer')->set_uploaded_avatar($clientID, $imgOrder, $filename);

        return array(
            'result'    => TRUE,
            'imgOrder'  => $imgOrder,
            'filename'  => $filename
        );

    }

    /**
     * set profile avatar
     */
    public function set_primary_avatar()
    { 
        $clientID = Auth::user()->clientID;
        $avatars  = $this->repository('Rplayer')->get_all_avatars($clientID);
        $imgOrder = Input::get('imgOrder'); 

        $this->service('Svalidate')->validate(array(
            'set_primary' => array(
                                'value'     => array(
                                                'imgOrder' => $imgOrder,
                                                'clientID' => $clientID,
                                                'filter'   => 'set_primary' 
                                            ),
                                 'validator' => 'imgOrder_availability'
                            )
        ), true);

        $this->service('Svalidate')->validate(array(
            'set_avatar' => array(
                                'value'     => array(
                                                'input' => $this->repository('Rplayer')
                                                                ->set_primary_avatar($clientID, $imgOrder),
                                                'type'  => 'set_primary'
                                            ),
                                'validator' => 'truthy',
                                'callback'  => function() use($clientID, $imgOrder) {

                                                $this->service('Slogger')->file( array(
                                                    'clientID' => $clientID,
                                                    'imgOrder' => $imgOrder
                                                ),'FAILED_SET_AVATAR' );

                                            }
                            )
        ), true);

        return array('result' => true);

    }

    /**
     * get memberstatusID and companysettingID
     * @return array 
     */
    public function check_status()
    {
        
        return array(
            '_msid' => Auth::user()->memberStatusID,
            '_csid' => Auth::user()->companySettingID
        );

    }

    /**
     * this will show avatar requested by websocket
     * @return array 
     */
    public function ws_show_Avatar()
    {
        $param = (array)json_decode(Input::get('param'));
     
            $this->service('Svalidate')->validate(array(
                'sessionID' => array(
                                    'value'     => $param,
                                    'validator' => 'show_avatar'
                                )
            ));

            $this->service('Ssocket')->push_main(
                $param['sessionID'],
                array('event' => 'OPEN_AVATAR')
            );

            return array('result' => true);


    }

    /**
     * get all inactive players and logout players
     * @return array 
     */
    public function inactive_players()
    {

        $last_timestamp = $this->service('Ssiteconfig')->last_timestamp(date('Y-m-d H:i:s'));

        $inactive_clientIDs = $this->repository('Rplayer')->get_inactive_clientIDs($last_timestamp);

        if (count($inactive_clientIDs) == 0) return array('result' => true, 'msg'=>'No inactive players');

        $this->logout($inactive_clientIDs, 'inactive');

        return $inactive_clientIDs;

    }

    /**
     * update playerregistration that not verified within 3 days from signupdate
     * @return array 
     */
    public function update_unverified()
    {
        
        $prev_signupDate    = previous_date(date('Y-m-d H:i:s'), Config::get('settings.user.registration_expiration'));
        
        $unverified_account = $this->repository('Rplayer')->update_unverified_account($prev_signupDate);

        $this->service('Slogger')->file($unverified_account, 'unverified_account', 'ps_cron');
        
        return $unverified_account;
    }

    /**
     * notfiy the player about his latest account status
     * @return array 
     */
    public function ws_notify()
    {   

        $return = array(
                        'result'         => false,
                        'sessionID'      => Input::get('sessionId'),
                        'adminSessionID' => Input::get('adminSessionId')
                );

        $isLogin = $this->repository('Radmin')->isLogin($return['adminSessionID']);

        if ($isLogin) {
            $return['result'] = $isLogin;

            $client    = $this->repository('Rplayer')->client_by_sessionID(Input::get('sessionId')); 
            $status_id = $client['derived_status_id'];
            $is_active = $client['derived_is_active'];

            if (!$is_active) {
                $this->logout($client['clientID'],'ws_notify');
            }

            $this->service('Ssocket')->push_main(
                $return['sessionID'], 
                array(
                    'event' => 'client_status',
                    'message' => array(
                                'derived_status_id'       => $status_id,
                                'derived_is_transactable' => $client['derived_is_transactable'],
                                'status_error'            => $this->service('Svalidate')->status_err_codes($status_id, true),
                                'is_active'               => $is_active
                            )
                )
            );

            $this->service('Slogger')->file($return, 'ws_notify', 'debug');
        }
        
        return $return;

    }


    /**
     * logout client requested by admin
     * @return array 
     */
    public function ws_logout()
    {
        $data     = array(
                        'isWL'        => Input::get('isWL'),
                        'webTypeName' => Input::get('wlCode'),
                        'wlMode'      => Input::get('wlMode'),
                        'clientIDs'   => Input::get('clientID'),
        ); 
        $clientIDs = array();
        if ($data['isWL'] == 1 && $data['webTypeName']) {

            $clientIDs = $this->repository('Rplayer')->get_clientIDs($data['webTypeName'], $data['wlMode']);
           
        }

        if (!is_array($data['clientIDs'])) {
            
            $data['clientIDs'] = (array)$data['clientIDs'];
        }
        
        $clientIDs = seq_array_merge($clientIDs, $data['clientIDs']);

        try {
            
            $return = $this->service('Svalidate')->validate(
                        array(
                            'ws_logout' => array(
                                                'value'     => array('input' => $clientIDs),
                                                'validator' => 'truthy'
                                            )
                        ), TRUE
                    );
            $this->logout($clientIDs, 'ws_logout');

        } catch (Exception $e) {
            $return = json_decode($e->getMessage());
            $return->error = 'Please check request parameters.'; 
            $return->id    = Input::all();
            unset($return->err_code);            

            $return = json_encode($return);
        }
        return $return;
    }

    /**
     * Revive clients session to database
     * requested by admin
     * @return none 
     */
    public function ws_login()
    {
        $sessionID = Input::get('sessionId');
        $clientID  = Input::get('clientId');
        $client    = $this->repository('Rplayer')->get_player_lastActivity($clientID);
  
        $this->service('Slogger')->file(
            array(
                'login_name' => $client['loginName'],
                'session_id' => $sessionID,
                'old_session_id' => $client['sessionID'],
                'member_status_ID' => $client['memberStatusID'],
                'last_activity' => $client['lastActivity']
            ),
            'ws_login',
            'auth'
        );

        $webTypeID = $this->repository('Rdbconfig')->get_webTypeID(Config::get('settings.WEBTYPE_NAME'));

        $this->repository('Rplayer')->db_login($client, $sessionID, $webTypeID);
     
    }

    /**
     * register client game if missed in registration
     * @return none
     */     
    public function missed_registration()
    {

        $unregistered = $this->repository('Rplayer')->get_unregistered();


        if(count($unregistered) > 0){

            $games = $this->repository('Rproducts')->get_games(Config::get('settings.TOKEN_ON_API'));

            foreach ($games as $game) {

                foreach ($unregistered as $client) {

                    try {

                        $registered = $this->service('Sapi')->register_not_exist(
                                        $client['clientID'],
                                        $client['parentID'],
                                        $game['serverID']
                                     );
                        
                    } catch (Exception $e) {

                    }


                    $response = isset($registered['result']) ? 'success_api_reg' : 'failed_api_reg';

                    $this->service('Slogger')->file(
                        array(
                            'login_name'  => $client['loginName'],
                            'error_games' => array($game['gameName'])
                        ),
                        $response,
                        'ps_api'
                    );
                }
            }
        }
    }

    /**
     * this will logout player requested by API
     * @return  array
     */
    public function api_logout_player()
    {
        $param = (array)json_decode(Input::get('param'));

        $this->service('Svalidate')->validate(array(
            'clientID' => array(
                            'value'     => array(
                                                'type'     => 'hidden',
                                                'input'    => $param['clientID']
                                            ),
                            'validator' => 'required_param'
            ),
            'sessionID' => array(
                            'value'     => array(
                                                'type'     => 'hidden',
                                                'input'    => $param['sessionID']
                                            ),
                            'validator' => 'required_param'
            )
        ));
    
        return $this->logout($param['clientID'], 'api_logout', $param['sessionID']);

    }

} 