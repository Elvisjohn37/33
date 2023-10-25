<?php

namespace Backend\services;

use Backend\exceptions\Svalidateexception;
use Validator;
use Exception;
use Config;
use DateTime;
use Lang;
use Crypt;
use Auth;
use Layer;

/**
 * All custom validations
 * Special error codes:
 *     -1: reload
 *     -2: redirect with 'url' index
 *     -3: session timeout
 *     -4: retry
 * @author PS Team
 */
class Svalidate extends Baseservice {

    private $validation_levels = array('simple', 'db');

    /**
     * This will validate all fields and return array of errors
     * @param array $validate_fields        array( field => [value, validator, callback] )
     * 
     *    field     = field name, index of the arrayMSP
     *    validator = name of method that will perform the field validation, 
     *                if this is not set the 'field' will be considered as the validator method name
     *    value     = 'validator' method second argument
     *    callback  = function to be perform if error has been detected
     *    
     * @param  boolean $terminate_on_error   if set to true this method will immediately 
     *                                       throw error with last error result reported
     *     
     * @return array
     */
    public function validate($validate_fields, $terminate_on_error = false) 
    {

        $validation_progress = array(
                                    'nonvalidated_fields' => array(),
                                    'validated_fields'    => array(),

                                    // array to be return if the terminate_on_error is set to false
                                    // [error, details]
                                    'list'                => array(
                                                                'result'  => false,
                                                                'error'   => array(),
                                                                'details' => array()
                                                            ),

                                    // the last error response from validate_<method>
                                    'last_error'          => null
                                );

        if (is_array($validate_fields) && count($validate_fields) > 0) {

            // validations per validation level
            foreach ($this->validation_levels as $validation_level) {

                foreach ($validate_fields as $field => $options) {

                    // execute validation
                    $this->execute_validation(
                        array('validation_level' => $validation_level, 'field' => $field),
                        $validation_progress,
                        $options
                    );

                    // immediate throw if terminate on error is set to true
                    if (!empty($validation_progress['last_error']) && $terminate_on_error) {

                        throw new Svalidateexception($validation_progress['last_error']);
                    }
                }
            }
            
            // finalize result
            if (count($validation_progress['nonvalidated_fields']) <= 0) {

                if (count($validation_progress['list']['error'])) {

                    throw new Svalidateexception($validation_progress['list']);
                    
                } else {

                    return array('result' => true);
                }

            } else {

                throw new Exception('Invalid field names: ' 
                                    . json_encode(array_values($validation_progress['nonvalidated_fields']))
                                    .' , Declared Fields validator might not exists or does not have any return.');
            }

        } else {

            throw new Exception('Svalidate::validate() argument 1 must be an array');

        }
    }

    /**
     *  This will help validate() call the method that will validate all our fields base on current validation level
     *  
     *  Error reporting: If validation result has 'err_code' 
     *                   it will only put the 'err_code' to $validation_progress['list']['error'][<field>]
     *                   else it will put the full result 
     *                   
     * @param  string $inprogress            [validation_level, field]
     * @param  string $validation_progress   reference
     * @param  array  $options                  
     * @return void
     */
    private function execute_validation($inprogress, &$validation_progress, $options) 
    {
        // check if field already has error before then no need to validate again
        if (!array_key_exists($inprogress['field'], $validation_progress['list']['error'])) {

            $validator   = isset($options['validator']) ? $options['validator'] : $inprogress['field'];
            $method_name = 'validate_' . $validator;

            $validation_result = $this->$method_name($inprogress['validation_level'], $options['value']);
  
            if (!empty($validation_result)) {

                if ($validation_result['result'] == false) {

                    // dcode from err_code
                    if (!empty($validation_result['err_code'])) {

                        $parsed_err_code = explode(':', $validation_result['err_code']);

                        if (count($parsed_err_code) > 1) {

                            $validation_result['err_code'] = $parsed_err_code[0];
                            $validation_result['dcode']    = $parsed_err_code[1];

                        }

                    }

                    // check if there's callback, execute first
                    if (isset($options['callback']) && is_callable($options['callback'])) {
                        
                        $options['callback']($validation_result);

                    }

                    // add to errors list
                    $validation_progress['last_error'] = $validation_result;
                    
                    if (!empty($validation_result['err_code'])) {

                        $validation_progress['list']['error'][$inprogress['field']] = $validation_result['err_code'];

                        // add other `details` if have
                        $error_details = array_except($validation_result, array('result','err_code'));

                        if (count($error_details) > 0) {
                            
                            $validation_progress['list']['details'][$inprogress['field']] = $error_details;

                        }

                    } else {

                        $validation_progress['list']['error'][$inprogress['field']] = $validation_result; 

                    }
                }

                // add to validated list and remove from nonvalidated list
                $validation_progress['validated_fields'][$inprogress['field']] = $inprogress['field'];
                unset($validation_progress['nonvalidated_fields'][$inprogress['field']]);
                
            } else {

                // validation type does not exists
                if (!in_array($inprogress['field'], $validation_progress['validated_fields'])) {

                    $validation_progress['nonvalidated_fields'][$inprogress['field']] = $inprogress['field'];
                }

            }
        }
    }

    /**
     * This will execute simple validation rules using laravel Validate service facade
     * @param  array  $validator_args
     * @return array
     */
    private function execute_simple_validation($validator_args) 
    {
        // add bail to all rules, because we only need the first failure
        array_prefix($validator_args['rules'],'bail|');

        $validation = Validator::make($validator_args['values'], $validator_args['rules'], $validator_args['messages']);

        // check failure 
        if ($validation->fails()) {

            return array(
                'result'    => false,
                'err_code'  => $validation->messages()->all()[0]
            );
            
        } else {

            return array(
                'result' => true
            );
        }
    }
    
    /**
     * START CUSTOM VALIDATIONS
     */
    
    /**
     * display name validation
     * 
     * @param  string $level
     * @param  array  $value display name and client data
     * @return array
     */
    private function validate_displayName($level, $value) 
    {
        
        switch ($level) {

            case 'simple':

                if ($value['client']->displayNameStatus == 1) {
                    return array('result' => false, 'err_code' => 'ERR_00031');
                }

                if ($this->service('Ssiteconfig')->is_fould_word($value['displayName'])) {
                    
                    return array('result' => false, 'err_code'=> 'ERR_00055');

                }

                if (no_symbol($value['displayName'])) {

                    return array('result' => false, 'err_code' => 'ERR_00030');

                }

                if (!no_space($value['displayName'])) {

                    return array('result' => false, 'err_code' => 'ERR_00001');

                }
                $minumum = Config::get('settings.user.displayName_length.min');
                $maximum = Config::get('settings.user.displayName_length.max');

                return $this->execute_simple_validation(array(
                    'values'   => array(
                                    'displayName' => $value['displayName']
                                ),
                    'rules'    => array(
                                    'displayName' => 'required|min:'.$minumum.'|max:'.$maximum.
                                    '|not_in:' . $value['client']->username . ',' . $value['client']->loginName
                                ),
                    'messages' => array(

                                    'min'           => 'ERR_00030',
                                    'max'           => 'ERR_00030',  
                                    'required'      => 'ERR_00002',
                                    'in'            => 'ERR_00005',
                                    'not_in'        => 'ERR_00032'
                                )
                ));

            case 'db':

                // check if display name already exists
                if ($this->repository('Rplayer')->displayName_exists($value['displayName'])) {

                    return array(
                        'result'    => false, 
                        'err_code'  => 'ERR_00029'
                    );

                } else {

                    return array('result' => true);

                }

                break;
        }
    }

    /**
     * validate process 
     * 
     * IF the required field is a hidden parameter 
     * or a non user input from ajax, $value should be an array['is_param','value']
     * is_param = flag that the value is a hidden parameter 
     * value    = value of the parameter that is required
     * ELSE the $value should have an absolute value and used usually for required fields
     * ex. $value = 'hello'
     * 
     * @param  string $level 
     * @param  string $value process to validate
     * @return array        
     */
    private function validate_required_param( $level, $value ) 
    {

        switch ($level) {
            
            case 'simple':

                set_default($value, 'type' , NULL);

                switch ($value['type']) {
                    case 'hidden'     : $message = 'ERR_00001'; break;
                    case 'multiple'   : $message = 'ERR_00068'; break;
                    case 'login'      : $message = 'ERR_00038'; break;
                    case 'game_window': $message = 'ERR_00001:RWP'; break;
                    default           : $message = 'ERR_00002'; break;
                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'required_input' => $value['input']
                                ),

                    'rules'    => array(
                                    'required_input' => 'required'
                                ),

                    'messages' => array(
                                    'required' => $message
                                )
                ));
        }
    }
        
    /**
     * validate capthcha if same value to session
     * 
     * @param  string $level 
     * @param  array $value captcha input and session
     * @return array
     */
    private function validate_captcha($level, $value) 
    {
        
        switch ($level) {
            
            case 'simple':

               return $this->execute_simple_validation(array(
                    
                    'values' => array(
                                'captcha_session' => $value['captcha_session'],
                                'captcha'         => $value['captcha_input']
                            ),
                    
                    'rules' => array(
                                'captcha' => 'required|same:captcha_session'
                            ),
                    
                    'messages' => array(
                                'same'     => 'ERR_00037',
                                'required' => 'ERR_00065'
                            )
                ));
        }
        
    }
    
    /**
     * Access Layers
     */
    
    /**
     * This will validate if site is accessibe by player right now
     * @param type $level
     * @param type $value
     * @return array
     */
    private function validate_site_access($level, $value)
    {
        
        switch ($level) {

            case 'db':

                $app_mode  = $this->service('Ssiteconfig')->get_app_mode();

                if ($app_mode['app_mode']) {
                    if ($app_mode['mode'] == 2) {
                        
                        $err_code  = Auth::check() ? 'ERR_00061' : 'ERR_00118';
                        
                        $isTestPlayer = $this->repository('Rplayer')->isTestPlayer(
                                $value,
                                Config::get('settings.WL_CODE'),
                                $this->service('Ssiteconfig')->test_agents_whitelist()
                            );

                        if (!$isTestPlayer) {
                            return array(
                                'result'  => FALSE,
                                'enabled' => FALSE,
                                'err_code'=> $err_code,
                                '_am'     => $app_mode
                            ); 
                        }
                    }

                return array('result' => TRUE);

                }
                else {
                    return array(
                        'result'  => FALSE,
                        'enabled' => FALSE,
                        'err_code'=> 'ERR_00061',
                        '_am'     => $app_mode
                    ); 
                }
                
        }
    }

    /**
     * This will give array of status codes with their corresponding error message
     * @return array
     */
    public function status_codes_list() 
    {
        return  array(
            'mem_0' => array('err_code' => 'ERR_00040', 'dcode'=> 'MSP'), 
            'mem_2' => array('err_code' => 'ERR_00041', 'dcode'=> 'MSS'), 
            'mem_3' => array('err_code' => 'ERR_00042', 'dcode'=> 'MSC'), 
            'mem_4' => array('err_code' => 'ERR_00043', 'dcode'=> 'MSL'),
            'mem_5' => array('err_code' => 'ERR_00044', 'dcode'=> 'MSD'), 
            'com_1' => array('err_code' => 'ERR_00046', 'dcode'=> 'CSS'), 
            'com_2' => array('err_code' => 'ERR_00048', 'dcode'=> 'CSC'),
        );
    }

    /**
     * This will get the proper err code for players err_code
     * @param  string  $status_id
     * @param  boolean $err_code_only default = false
     * @return string
     */
    public function status_err_codes($derived_status_id, $err_code_only = false)
    {
        $err_codes = $this->status_codes_list();

        if (isset($err_codes[$derived_status_id])) {
            if ($err_code_only) {
                return $err_codes[$derived_status_id]['err_code'];
            } else {
                return $err_codes[$derived_status_id]['err_code'].':'.$err_codes[$derived_status_id]['dcode'];
            }
        } else {

            return null;

        }
    }

    /**
     * This will validate if user should be allowed for login status
     * @param  string $level 
     * @param  array  $value 
     * @return array
     */
    private function validate_login_access($level, $value)
    {
        switch ($level) {
            
            case 'simple':

                if (!$value['derived_is_active']) {     

                    return array(
                        'result'    => FALSE,
                        'enabled'   => FALSE,
                        'err_code'  => $this->status_err_codes($value['derived_status_id']),
                        '_msid'     => $value['memberStatusID'],
                        '_csid'     => $value['companySettingID']
                    );

                }

                // add IP checking
                if (!$this->repository('Rdbconfig')->is_ip_allowed(get_ip())) {
                    
                    return array(
                        'result'    => FALSE,
                        'err_code'  => 'ERR_00051:BIP',
                    );

                }

                return array('result' => TRUE);
        }
    }

    /**
     * This will get the proper err code for players err_code
     * @param  string $pre_requisite_page
     * @return string
     */
    private function prerequisite_err_codes($pre_requisite_page)
    {
        $err_codes = array(
                        'change_credentials' => 'ERR_00091:PRC', 
                        'accept_terms'       => 'ERR_00091:PRA',
                        'expired_password'   => 'ERR_00091:PRP',
                    );

        if (isset($err_codes[$pre_requisite_page])) {
            
            return $err_codes[$pre_requisite_page];

        } else {

            return null;

        }
    }

    /**
     * This will validate if user can access any transaction on the site
     * Playing games, Transfer, Account changes
     * @param  type $level
     * @param  type $value
     * @return array
     */
    private function validate_transaction_access($level, $value)
    {
        switch ($level) {
            
            case 'simple':

                set_default($value, 'page', '');

                $pre_requisite_page = $this->service('Ssiteconfig')->get_prerequisite_page($value['auth_user']);

                if ($pre_requisite_page && $value['page']  != $pre_requisite_page) {

                    return array(
                        'result'    => FALSE,
                        'err_code'  => $this->prerequisite_err_codes($pre_requisite_page)
                    );

                }

                if (!$value['auth_user']['derived_is_transactable']) {     

                    return array(
                        'result'    => FALSE,
                        'enabled'   => FALSE,
                        'err_code'  => $this->status_err_codes($value['auth_user']['derived_status_id']),
                        '_msid'     => $value['auth_user']['memberStatusID'],
                        '_csid'     => $value['auth_user']['companySettingID']
                    );

                }

                return array('result' => TRUE);
        }
    }

    /**
     * Check if the product is accessible
     * 
     * @param  type $level
     * @param  type $value [auth_user, productID, product(optional)]
     * @return type
     */
    private function validate_product_access($level, $value) 
    {
        
        $user = $value['auth_user'];

        switch ($level) {

            case 'simple':

                if (!in_array($value['productID'], $user->productID)) {

                    return array(
                        'result'    => FALSE,
                        'enabled'   => FALSE,
                        'err_code'  => 'ERR_00056:IPA',
                        '_ipa'      => FALSE
                    );

                }

                return array('result' => TRUE);

            case 'db':
                
                // set default product data
                set_default($value, 'product', function() use($value) {

                    return $this->repository('Rproducts')->get_product_data($value['productID']);

                });

                if (($value['product']['product_isTestModeEnabled'] and !$user->isTestPlayer)) {

                    return array(
                        'result'    => FALSE,
                        'enabled'   => FALSE,
                        'err_code'  => 'ERR_00059:PTM', 
                        '_ptm'      => $value['product']['product_isTestModeEnabled']
                    );

                }
                
                return array('result' => TRUE);
        }
    }

    /**
     * Check if serverID products are all in testmode
     * 
     * @param  type $level
     * @param  type $value
     * @return type
     */
    private function validate_serverID_access($level, $value) 
    {
        switch ($level) {

            case 'db':
                
                $serverID_testmode = $this->repository('Rproducts')->serverID_testmode($value); 

                if ($serverID_testmode) {

                    return array(
                        'result'    => FALSE,
                        'enabled'   => FALSE,
                        'err_code'  => 'ERR_00001:SIA'
                    );

                }
                
                return array('result' => TRUE);
        }
    }
            
    /**
     * This will validate if player can access game
     * @param   string $level
     * @param   array  $value [auth_user, gameID, game(optional)]
     * @return  array
     */
    private function validate_game_access($level, $value)
    {      

        $user = $value['auth_user'];

        switch ($level) {

            case 'simple':

                $displayName_required = $this->repository('Rproducts')->gameIDs_required_displayName();
                // $displayName_required = Config::get("settings.GAMES_REQ_DISPLAYNAME");

                if (in_array($value['gameID'], $displayName_required) && $user->displayNameStatus == 0 ) { 

                    return array( 
                        'result'   => FALSE,
                        'dcode'    => 'DNR', // display name required
                        'err_code' => 'ERR_00108'
                    );

                }

                return array('result' => TRUE);
                
            case 'db':

                // set default game data
                set_default($value, 'game', function() use($value) {

                    return $this->repository('Rproducts')->get_game_data($value['gameID']);

                });

                $is_mobile_platform = $this->service('Ssiteconfig')->is_mobile_platform();
                
                // Check if platform is mobile and game is playable in mobile
                if ($is_mobile_platform && $value['game']['isMobileReady'] == 0 ) {

                    return array(
                        'result'   => FALSE, 
                        'err_code' => 'ERR_00078'
                    );

                }
                
                // check if games is testmode
                if ($value['game']['game_isTestModeEnabled']) {
                    
                    $no_isTestPlayer = Config::get("settings.GAMES_NO_TEST");
                    
                    // check if player is not test player OR check if game does not have test mode
                    if (!$user->isTestPlayer || in_array($value['gameID'], $no_isTestPlayer)) {

                        return array( 
                            'result'   => FALSE,
                            'err_code' => 'ERR_00059:GTM', 
                            '_gtm'     => $value['game']['game_isTestModeEnabled']
                        ); 

                    }

                }

                // check if this game is under non multiple instance and has another game running under same productID
                if (!$value['game']['isMultipleInstance']) {

                    $other_gameID_running = $this->repository('Rtransactions')->productID_running_first(
                                                $user['clientID'],
                                                $value['game']['productID'],
                                                array($value['game']['gameID'])
                                            );

                    if ($other_gameID_running) {
                        // no need to return err_code
                        // frontend will handle UI response
                        return array(
                            'result'           => false,
                            'dcode'            => 'HRG', 
                            '_hrg'             => 1, 
                            'runningGame'      => $other_gameID_running['gameName'],
                            'running_game_key' => $this->service('Ssiteconfig')->gameName_unique_key(
                                                                            $other_gameID_running['gameID'],
                                                                            $other_gameID_running['gameName']
                                                                        ),
                            '_GID'             => $this->service('Scrypt')->crypt_encrypt($other_gameID_running['gameID']),
                            'gameName'         => $value['game']['gameName'],
                            'aborted'          => $value['encrypted_gameID']
                        );
                    }

                }

                //check if serverID of game is disabled
                if (in_array($value['game']['serverID'], $this->service('Ssiteconfig')->serverIDs_disabled())) {
                    return array(
                        'result'   => FALSE,
                        'err_code' =>'ERR_00001:SD'
                    );
                }

                return array('result' => TRUE);           
        }

    }


    /**
     * This will validate if site is the manager of game websession
     * @param   string $level
     * @param   array  $value [auth_user, gameID, game(optional)]
     * @return  array
     */
    private function validate_websession_access($level, $value)
    {     
        switch ($level) {
            
            case 'db':

                // set default game data
                set_default($value, 'game', function() use($value) {

                    return $this->repository('Rproducts')->get_game_data($value['gameID']);

                });

                $ps_managed_websessions = $this->service('Ssiteconfig')->websession_products()['create']['ps_managed'];

                if (!in_array($value['game']['productID'], $ps_managed_websessions)) {
                    
                    return array( 
                        'result'   => FALSE,
                        'err_code' => 'ERR_00005'
                    ); 

                }

                return array('result' => TRUE);           
        }

    } 

    /**
     * This will validate user access to withdrawal request module
     * @param  string $level 
     * @param  object $value
     * @return array
     */
    private function validate_withdrawal_access($level, $value)
    {
        switch ($level) {

            case 'simple':

                // only walkin can withdraw
                if (!$value->isWalkIn) {

                    return array('result'=>false, 'err_code' => 'ERR_00005:WDA');

                }

                return array('result'=>true);

                break;

        }
    }

    /**
     * This will validate user access to fund transfer module
     * @param  string $level 
     * @param  object $value
     * @return array
     */
    private function validate_fund_access($level, $value)
    {
        switch ($level) {

            case 'db':

                // fund transfer is for users only that can use products 
                $fund_transfer_walletIDs = $this->service('Ssiteconfig')->get_wallets($value->productID);

                // Notice that this use less than 2 istead of 1, 
                // because wallet list always has house wallet in it.
                if (count($fund_transfer_walletIDs) < 2) {

                    return array('result' => false, 'err_code' => 'ERR_00005:FTA');

                }

                return array('result' => true);

                break;

        }
    }

    /**
     * This will validate user access to withdrawal request module
     * @param  string $level 
     * @param  object $value
     * @return array
     */
    private function validate_join_access($level, $value)
    {
        switch ($level) {

            case 'simple':

                // only walkin can withdraw
                if (!$value['join_info'] || !$value['join_info']['derived_joinable']) {

                    return array('result' => false, 'err_code' => 'ERR_00005');

                }

                return array('result'=>true);

                break;

        }
    }

    /**
     * This will validate API reponse
     * @param  string $level 
     * @param  mixed  $value API response
     * @return array
     */
    private function validate_api_response($level, $value)
    {
        switch ($level) {

            case 'simple':

                if (is_array($value)) {

                    if (isset($value['error']) ) {
                        
                        if (is_array($value['error'])) {
                            
                            if (isset($value['error']['code']) && $value['error']['code'] == 0) {
                              
                                return array('result' => true);
                            }

                        } elseif ($value['error'] == 0) {

                            return array('result' => TRUE);
                        }
                    }
                }

                return array(
                    'result'    => FALSE, 
                    'err_code'  => 'ERR_00001:API'
                );
        }
    }

    /**
     * This will validate if clientID is registered to a company
     * @param type $level
     * @param type $value
     * @return type
     */
    private function validate_company_registration($level, $value)
    {

        switch ($level) {

            case 'db':
            
                $is_company_registered = $this->repository('Rproducts')
                                        ->is_company_registered($value['companyID'], $value['clientID']);

                if ($is_company_registered == false) {
                    
                    return array(
                        'result'    => false, 
                        'err_code'  => 'ERR_00001:CRE'
                    );

                }

                return array('result' => true);

        }

    }

    /**
     * This will validate if statement month number is for 3 mos only
     * @param  string $level 
     * @param  int    $value 
     * @return array
     */
    private function validate_statement_month($level, $value)
    {
        switch ($level) {
            
            case 'simple':

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'statement_month'   => $value,
                                ),
                    
                    'rules'    => array(
                                    'statement_month'   => 'required|numeric'
                                                          .'|between:0,'.Config::get('settings.report.statement_months')
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00033',
                                    'numeric'   => 'ERR_00033',
                                    'between'   => 'ERR_00033',
                                )
                ));

        }
    }
    
    /**
     * check if valid email format
     * 
     * @param string $level
     * @param string/array $value
     */
    private function validate_email_address ( $level, $value ) // tested
    {
        $email_address = (is_array($value) && isset($value['email_address'])) ?  $value['email_address'] : $value;
        $err_codes     = array();
        
        switch ($level) {
            
            case 'simple':

                //Add validation for max_value
                $result = $this->validate_max_value('simple', array(
                                                                'input'     => $value['email_address'],
                                                                'err_type'      => 'email',
                                                                'max_value' => $value['max_value']
                                                            ));
                if (!$result['result']) {
                    return $result;
                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'email_address' => $email_address
                                ),
                    
                    'rules'    => array(
                                    'email_address' => 'required|email'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002',
                                    'email'     => 'ERR_00014'
                                )
                ));
            
            case 'db':

                set_default($value, 'type' , NULL);
                
                switch($value['type']){
                    
                    case 'register':        
                        $err_codes['register'] = array(
                                                        'for_activation'       => 'ERR_00015', //with span 
                                                        'for_agent_activation' => 'ERR_00110' //without span
                                                    );
                    case 'register_friend': 

                        $err_codes['register_friend'] = array(
                                                        'for_activation'       => 'ERR_00111', //with spaout span 
                                                        'for_agent_activation' => 'ERR_00112' //without span
                                                    );
                        
                        set_default($value, 'whiteLabelID' , Config::get('settings.WL_CODE'));

                        $result_key = $this->repository('Rplayer')->check_email(
                            $email_address,
                            $value['whiteLabelID'],
                            Config::get('settings.user.max_day_registration')
                        );

                      // Email has been used before.
                        switch ($result_key) {
                            case 'used_confirmed':
                                return array(
                                    'result'    => FALSE,
                                    'err_code'  => 'ERR_00014'
                                );

                            case 'for_activation' :
                            case 'for_agent_activation' :
                                return array(
                                        'result'    => FALSE,
                                        'err_code'  => $err_codes[$value['type']][$result_key]
                                    );

                            case 'unused':
                            case 'soft_delete_reuse':
                                
                                return array( 'result' => TRUE );

                        }
                    
                        
                        break;
                }
                                
                return array( 'result' => TRUE );
        }
    }

    /**
     * This will validate if transactionID belongs to clientID
     * @param  string $level
     * @param  array  $value [transactionID, clientID]
     * @return array
     */
    private function validate_transactionID($level, $value)
    {
        switch ($level) {

            case 'simple':

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'transactionID' => $value['transactionID']
                                ),

                    'rules'    => array(
                                    'transactionID' => 'required'
                                ),

                    'messages' => array(
                                    'required' => 'ERR_00001:TIR'
                                )

                ));

            case 'db':

                $is_players_transaction = $this->repository('Rtransactions')
                                               ->is_players_transaction($value['transactionID'],$value['clientID']);

                if ($is_players_transaction) {
                    
                    return array('result' => true);

                } else {

                    return array('result' => false, 'err_code'=>'ERR_00001:TIU');

                }
        }

    }

    /**
     * Ths will validate if value is truthy (1, true), if not it will return error base on $value['type']
     * @param  string $level
     * @param  string $value [input, type]
     * @return array
     */
    private function validate_truthy($level, $value)
    {
        switch ($level) {

            case 'simple':

                set_default($value, 'type' , NULL);

                switch ($value['type']) {
                    case 'commissioneffective'         : $err_code = 'ERR_00072';     break;
                    case 'clientproduct'               : $err_code = 'ERR_00073';     break;
                    case 'withdrawal_availableBalance' : $err_code = 'ERR_00052';     break;
                    case 'chat_sender'                 : $err_code = 'ERR_00028';     break;
                    case 'securityQuestion'            : $err_code = 'ERR_00064';     break;
                    case 'login'                       : $err_code = 'ERR_00038';     break;
                    case 'was_created'                 : $err_code = 'ERR_00087';     break;
                    case 'set_primary'                 : $err_code = 'ERR_00090';     break;
                    case 'walkin_agent'                : $err_code = 'ERR_00054';     break;
                    case 'game_transactionDetail'      : $err_code = 'ERR_00092';     break;
                    case 'email_verification'          : $err_code = 'ERR_00001:EV';  break;
                    case 'activate_account'            : $err_code = 'ERR_00106:AA';  break;
                    case 'send_message'                : $err_code = 'ERR_00115';     break;
                    case 'game_window'                 : $err_code = 'ERR_00115:WNE'; break;
                    case 'registered_client'           : $err_code = 'ERR_00001:CRR'; break;
                        # code...
                        break;
                    default                            : $err_code = 'ERR_00001:TRU'; break;
                }

                // There's an error when using (!$value['input']) with laravel file->move() operation
                return ($value['input']) ? array('result' => true) : array('result' => false, 'err_code' => $err_code);
        }
    }

    /**
     * Ths will validate if value is falsy (null,0,false), if not it will return error base on $value['type']
     * @param  string $level 
     * @param  string $value [input, type]
     * @return array
     */
    private function validate_falsy($level, $value)
    {
        switch ($level) {

            case 'simple':

                set_default($value, 'type' , NULL);

                switch ($value['type']) {

                    case 'has_captcha'       : $err_code = 'ERR_00053';    break;
                    case 'claim_prize'       : $err_code = 'ERR_00005';    break;
                    default                  : $err_code = 'ERR_00001';    break;

                }

                if ($value['input']) {
                    
                    return array(
                        'result'    => FALSE,
                        'err_code'  => $err_code
                    );
                }
                
                return array( 'result' => TRUE );
        }
    }
    
    /**
     * Check token if its the same with session token
     * 
     * @param type $level
     * @param type $value
     * @return type
     */
    private function validate_csrf( $level, $value )
    {
        switch ($level) {
            
            case 'simple':

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'token'   => $value['input'],
                                    'session' => $value['session']
                                ),
                    
                    'rules'    => array(
                                    'token'   => 'required|same:session'
                                ),
                    
                    'messages' => array(
                                    'required'=> '-4:CSRF',
                                    'same'    => '-4:CSRF'
                                )
                    
                ));

        }
    }

    /**
     * This will validate transaction logs date format
     * @param  string $level
     * @param  string $value
     * @return array
     */
    private function validate_transactionlogs_date($level, $value)
    {

        switch ($level) {
            
            case 'simple':
                
                if (!is_date_format($value,'n/j/Y g:i A')) {
                    
                    return array('result'=>false, 'err_code'  => 'ERR_00001');

                }
                
                return array('result' => true);
        }

    }
    
    /**
     * allow only alphabetical characters 
     * 
     * @param string $level
     * @param string $value
     * @return array
     */
    private function validate_alphabetic( $level, $value ) // tested
    {
        switch ($level) {
            
            case 'simple':
                
                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'field' => remove_space( $value )
                                ),
                            
                    'rules'    => array(
                                    'field' => 'required|alpha'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002',  
                                    'alpha'     => 'ERR_00004'
                                )
                    
                ));
        }
    }
    /**
     * This will validate if input value is alphabetical and not more than max value
     * @param  string $level 
     * @param  array  $value 
     * @return array  
     */
    private function validate_name( $level, $value )
    {
        $value['err_type'] = 'name';

        switch ($level) {
            case 'simple':

                    $result = $this->validate_max_value('simple', $value);
                    if (!$result['result']) {

                        return $result;

                    }

                    return $this->validate_no_symbol('simple', $value['input']);                    
                break;
            
        }
    }
    /**
     * This will validate if input is not more than max value
     * @param  string $level 
     * @param  array  $value 
     * @return array      
     */
    private function validate_max_value($level, $value)
    {
        switch ($level) {
            
            case 'simple':
                switch ($value['err_type']) {

                    case 'name'           : $err_code = 'ERR_00116'; break;
                    case 'email'          :
                    case 'yourAnswer'     : 
                    case 'accountBankName': $err_code = 'ERR_00117'; break;

                }
                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'field' => remove_space( $value['input'] )
                                ),
                            
                    'rules'    => array(
                                    'field' => 'max:'.$value['max_value']
                                ),
                    
                    'messages' => array(
                                    'max'       => $err_code
                                )
                    
                ));
        }
    }
    
    /**
     * Login name rules
     * 
     * - The Login name must be between 6 and 15 characters contains only letters (a-z) and numbers (0-9), 
     * and start with a letter. The letters are not case-sensitive.
     *  - If the Login name has been used by the player or does not fulfilled the requirement, 
     * the login name cannot be used. (The warning sentence will appear beside the login name column)
     * 
     * @param type $level
     * @param type $value
     * @return type
     */
    private function validate_loginName( $level, $value ) // tested
    {
        switch ($level) {
            
            case 'simple':
                
                return $this->execute_simple_validation(array(
                    'values'   => array(
                                    'loginName'    => $value['loginName'],
                                    'is_character' => substr($value['loginName'],0, 1)
                                ),
                    
                    'rules'    => array(
                                    'loginName'    => 'required'
                                                    .'|between:'
                                                    .implode(',',Config::get('settings.user.loginName_length'))
                                                    .'|alpha_num'
                                                    .'|regex:/^[A-Za-z].*[0-9]/',
                                    'is_character' => 'alpha'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002', 
                                    'alpha'     => 'ERR_00006',
                                    'alpha_num' => 'ERR_00006',
                                    'between'   => 'ERR_00006',
                                    'regex'     => 'ERR_00006',
                                )
                ));
                
            case 'db':
                
                $is_exist = $this->repository('Rplayer')->is_loginName_exist(
                                $value['loginName'],
                                $value['user']['whiteLabelID']
                            );
                
                if ($is_exist){
                    
                    return array( 
                        'result'    => FALSE,
                        'err_code'  => 'ERR_00008'
                    );
                }
                
                return array( 'result' => TRUE );
        }
    }
    
    /**
     * validate mobile number
     * rules:
     * 1. Digits should be between 5 to 20 characters
     * 2. Numeric digits only are allowed
     * 
     * @param string $level
     * @param int $value
     * @return array
     */
    private function validate_mobile_number( $level, $value ) // tested 
    {
        switch ($level) {
            
            case 'simple':
                
                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'mobile_number' => $value
                                ),
                    
                    'rules'    => array(
                                    'mobile_number' => 'required|numeric|digits_between:5,20'
                                ),
                    
                    'messages' => array(
                                    'required'      => 'ERR_00002', 
                                    'digits_between'=> 'ERR_00019', 
                                    'numeric'       => 'ERR_00017'
                                )
                ));
        }
    }
    
    /**
     * check if bank name exist in DB
     * 
     * @param string $level
     * @param string $value
     * @return array
     */
    private function validate_bankName( $level, $value ) // tested 
    {
        switch ($level){
            
            case 'simple':
                
                if (no_symbol($value)) {

                    return array('result' => false, 'err_code' => 'ERR_00020');

                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'bank_name' => $value
                                ),
                    
                    'rules'    => array(
                                    'bank_name' => 'required'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002'
                                )
                ));
                
            case 'db':
            
                $bank_dropdown = $this->service('Ssiteconfig')->bank_dropdown();
                
                if (!isset($bank_dropdown[$value])) {
                    
                    return array(
                        'result'    => FALSE,
                        'err_code'  => 'ERR_00020'
                    );
                }
                
                return array( 'result' => TRUE );
        }
    }
    
    /**
     * Use to check accountNumber user input 
     * 
     * @todo get concatenated accountNumber and info to $value,
     * if get_accountNoPattern_info = FALSE, return error
     * @see Rwhitelabel::format_bank_number for bank concatenation
     * @see Rwhitelabel::get_accountNoPattern_info for getting bank number requirements
     * @param string $level
     * @param array $value
     * @return boolean
     */
    private function validate_bank_input( $level, $value ) // tested
    {
        switch ($level) {
            
            case 'simple':
                
                if ($value != FALSE) {
                    
                    $accountNumber      = $value['accountNumber'];
                    $accountNoPattern   = $value['bank_pattern_info']['accountNoPattern'];

                    return $this->execute_simple_validation(array(

                        'values'   => array(
                                        'bank_input' => $accountNumber
                                    ),

                        'rules'    => array(
                                        'bank_input' => 'required|regex:'.$accountNoPattern
                                    ),

                        'messages' => array(
                                        'required'  => 'ERR_00022', 
                                        'regex'     => 'ERR_00021'
                                    )
                    ));
                }
                
                return array(
                    'result'    => FALSE,
                    'err_code'  => 'ERR_00021'
                );
        }
    }
    
    /**
     * Allow only alpha and numeric characters.
     * No special characters allowed
     * 
     * @param string $level
     * @param string $value
     * @return array
     */
    private function validate_no_symbol( $level, $value ) // tested
    {
        switch ($level) {
            
            case 'simple':

                if (no_symbol($value)) {

                    return array('result' => false, 'err_code' => 'ERR_00109');

                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'value' => $value
                                ),
                    
                    'rules'    => array(
                                    'value' => 'required'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002', 
                                )
                ));
        }
    }

    /**
     * this will validate accountBankName upon registration
     * @param  string $level 
     * @param  array $value  input to validate and max_value
     * @return array        
     */
    private function validate_accountBankName( $level, $value ) // tested
    {

        $value['err_type'] = 'accountBankName';

        switch ($level) {
            
            case 'simple':
                
                if (no_symbol($value['input'])) {

                    return array('result' => false, 'err_code' => 'ERR_00109');

                }

                $result = $this->validate_max_value('simple', $value);
                if (!$result['result']) {
                    return $result;
                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'value' => $value['input']
                                ),
                    
                    'rules'    => array(
                                    'value' => 'required'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002'
                                )
                ));
        }
    }
    
    /**
     * Check if currency exist
     * 
     * @param string $level
     * @param int $value value is currencyID
     * @return array
     */
    private function validate_currency( $level, $value ) // tested
    {
        switch ($level) {
            case 'simple':
                
                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'currency' => $value
                                ),
                    
                    'rules'    => array(
                                    'currency' => 'required|numeric'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002',
                                    'numeric'   => 'ERR_00024'
                                )
                ));
                
            case 'db':
                
                $check_webSignupEnabled = $this->repository('Rwhitelabel')->check_webSignupEnabled( $value );
                
                if (  ! $check_webSignupEnabled ){
                    
                    return array(
                        'result'    => FALSE,
                        'message'   => 'ERR_00024'
                    );
                }
                
                return array( 'result' => TRUE );
        }
    }
    
    /**
     * Check if security question exist
     * Note: this is hard coded in PS from language file
     * 
     * @param string $level
     * @param string $value
     * @return array
     */
    private function validate_securityQuestion_exist( $level, $value )
    {
        switch ($level) {
            case 'simple':
                
                $questions = implode(',', $this->service('Ssiteconfig')->securityQuestions());

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'security_question' => $value
                                ),
                    
                    'rules'    => array(
                                    'security_question' => 'required|in:'.$questions
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002',
                                    'in'        => 'ERR_00026'
                                )
                )); 
        }
    }
    
    /**
     * Check if value is the same
     * 
     * @param type $level
     * @param type $value array( value_1, value_2, mismatch_message )
     * @return type
     */
    private function validate_same_value( $level, $value ) // tested
    {
        $required = 'ERR_00002';

        switch ($level) {

            case 'simple':
                
                set_default($value, 'type' , NULL);

                switch ($value['type']) {
                
                    case 'captcha'    : $message  = '';          break;
                    case 'password'   : $message  = 'ERR_00011'; break;
                    case 'email'      : $message  = 'ERR_00016'; break;
                    case 'reset_code' : $message  = 'ERR_00037'; 
                                        $required = 'ERR_00001'; break;
                }
                
                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'value_1'   => $value['value_1'],
                                    'value_2'   => $value['value_2']
                                ),
                    
                    'rules'    => array(
                                    'value_1' => 'required|same:value_2'
                                ),
                    
                    'messages' => array(
                                    'required'  => $required,
                                    'same'      => $message
                                )
                ));     
        }
    }
    
    /**
     * This is a generic validation for all transfer amount
     * @param  string  $level
     * @param  numeric $value
     * @return array
     */
    private function validate_transfer_amount($level, $value)
    {
        switch ($level) {

            case 'simple':
                $amount = non_money_format($value);

                if (!is_whole_number($amount)) {

                    return array('result' => false, 'err_code' => 'ERR_00083');

                }
                if (no_symbol($amount)) {

                    return array('result' => false, 'err_code' => 'ERR_00049');

                }
                return $this->execute_simple_validation(array(
                    'values'   => array('original_amount' => $value, 'amount' => $amount),
                    'rules'    => array('original_amount' => 'required', 'amount' => 'numeric|min:1'),
                    'messages' => array(
                                    'required' => 'ERR_00002',
                                    'numeric'  => 'ERR_00049',
                                    'min'      => 'ERR_00060'
                                )
                ));
        }
    }

    /**
     * This will validate amount for deposit
     * @param  string $level
     * @param  array  $value [amount, bankName]
     * @return array
     */
    private function validate_deposit_amount($level, $value)
    {
        switch ($level) {

            case 'simple':

                return $this->validate_transfer_amount($level, $value['amount']);

            case 'db':

                if (Config::get('settings.DEPOSIT_LIMIT_CHECKDB')) {

                    set_default($value, 'whiteLabelID',Config::get('settings.WL_CODE'));

                    $amount          = non_money_format($value['amount']);
                    $minDepositLimit = $this->repository('Rwhitelabel')->get_bank_minDepositLimit(
                                        $value['bankName'],
                                        $value['whiteLabelID']
                                    );

                    if ($amount < $minDepositLimit) {

                        return array('result' => false, 'err_code' => 'ERR_00070');

                    }

                }

                return array('result' => true);
        }
    }

    /**
     * Password and confirm password matching validation
     * 
     * 1. must contain 8-15 characters.
     * 2. must include a combination of alphabetic characters (uppercase or lowercase letters) numbers and symbols.
     * 3. must not contain username, first and last name.
     * 4. must not contain any blank space.
     * 
     * @param type $level
     * @param array $value ['password','not_contain']
     * @return type
     */
    private function validate_new_password( $level, $value ) // tested
    {        
        switch ($level) {
            case 'simple':  

                $no_user_data = array('loginName', 'firstName', 'lastName');

                foreach ($no_user_data as $user_data_key ) {

                    $user_data = $value['user'][$user_data_key];
                    $strpos = strpos($value['new_password'], $user_data );

                    if ($strpos !== FALSE) {

                        return array(
                            'result'    => FALSE,
                            'err_code'  => 'ERR_00010'
                        );
                    }

                }

                // set to blank so if no old_password being passed the 'different' validation will be bypassed
                $old_password   = set_default($value, 'old_password', '');
                $old_salt       = set_default($value, 'old_salt',     '');
                
                if (!alpha_num_symbol($value['new_password'])) {
                    return array(
                            'result'    => FALSE,
                            'err_code'  => 'ERR_00010'
                        );

                }

                if (!no_space($value['new_password'])) {

                   return array(
                            'result'    => FALSE,
                            'err_code'  => 'ERR_00010'
                        );

                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'new_password'           => $value['new_password'],
                                    'encrypted_new_password' => encrypt_password($value['new_password'], $old_salt),
                                    'old_password'           => $old_password
                                ),
                    
                    'rules'    => array(
                                    'new_password'           => 'required|between:8,15|',
                                    'encrypted_new_password' => 'different:old_password'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002',
                                    'between'   => 'ERR_00010',
                                    'different' => 'ERR_00012'
                                )
                ));
        }
    }

    /**
     * This will compare input password to current encrypted password
     * @param  string $level 
     * @param  array  $value [input_password, password, salt]
     * @return array
     */
    private function validate_current_password($level, $value)
    {
        switch ($level) {

            case 'simple':

                $encrypt_input_password = encrypt_password($value['input_password'],$value['salt']);

                set_default($value, 'type' , NULL);

                switch ($value['type']) {

                    case 'login': $err_code = 'ERR_00038'; break;
                    default     : $err_code = 'ERR_00084'; break;

                }

                return $this->execute_simple_validation(array(

                    'values'   => array(
                                    'input_password' => $encrypt_input_password, 
                                    'password'       => $value['password'],
                                    'salt'           => $value['salt']
                                ),

                    'rules'    => array(
                                    'password'       => 'required',
                                    'salt'           => 'required',
                                    'input_password' => 'required|same:password'
                                ),

                    'messages' => array(
                                    'required' => $err_code,
                                    'same'     => $err_code
                                )
                ));
        }
    }

    /**
     * This will validate the available amount of the wallet where the value to be deducted from
     * @param  string  $level 
     * @param  array   $value [amount, from, availableBalance]
     * @return array
     */
    private function validate_fund_amount($level, $value)
    {
        switch ($level) {

            case 'simple': return $this->validate_transfer_amount($level, $value['amount']);
            case 'db'  :

                if ($value['process'] == 'deposit') {
                    
                    $walletID_balance = $value['client']['availableBalance'];

                } else {

                    $get_wallet_balance = $this->service('Sapi')->wallet_balance_information(

                        $value['walletID'],

                        array(
                            'clientID' => $value['client']['clientID'],
                            'parentID' => $value['client']['parentID']
                        ),
                        false

                    );

                    $walletID_balance   = non_money_format($get_wallet_balance['balance']);

                }

                if (non_money_format($value['amount'])>$walletID_balance) {

                    return array('result' => false , 'err_code' => 'ERR_00067');

                }

                return array('result' => true);
        }
    }

    /**
     * This will validate the source of our money for fund transfer
     * @param  string  $level 
     * @param  array   $value [process, from]
     * @return array
     */
    private function validate_fund_from($level, $value)
    {   
        $house_walletID          = Config::get('settings.products.house_walletID');
        $fund_transfer_walletIDs = $this->service('Ssiteconfig')->transferable_walletIDs();

        switch ($level) {

            case 'simple':

                // check if allowed in process
                switch($value['process']) {
                    
                    case 'withdraw':

                        // check if withdrawal is allowed
                        if (!$this->service('Ssiteconfig')->ft_allow_withdrawal()) {

                            return array('result' => false, 'err_code'=>'ERR_00005:FTW');

                        }   

                        // check if walletID is transferable
                        if (!in_array($value['from'], $fund_transfer_walletIDs)) {

                            return array('result' => false, 'err_code'=>'ERR_00005:FTT');

                        }

                        break;

                }

                return $this->execute_simple_validation(array(

                    'values'   => array(
                                    'from' => $value['from'],
                                ),

                    'rules'    => array(
                                    'from' => 'required'
                                ),

                    'messages' => array(
                                    'required' => 'ERR_00002',
                                )

                ));
        }
    }

    /**
     * This will validate the destination of our money for fund transfer
     * @param  string  $level 
     * @param  array   $value [from, to]
     * @return array
     */
    private function validate_fund_to($level, $value)
    {

        $house_walletID          = Config::get('settings.products.house_walletID');
        $fund_transfer_walletIDs = $this->service('Ssiteconfig')->transferable_walletIDs();

        switch ($level) {

            case 'simple':

                switch($value['process']) {
                    
                    case 'deposit':

                        // check if walletID is transferable
                        if (!in_array($value['to'], $fund_transfer_walletIDs)) {

                            return array('result' => false, 'err_code'=>'ERR_00005:FTD');

                        }

                        break;

                }

                return $this->execute_simple_validation(array(

                    'values'   => array(
                                    'house_walletID' => $house_walletID,
                                    'from'           => $value['from'],
                                    'to'             => $value['to'],
                                    'from_and_to'    => array($value['from'],$value['to'])
                                ),

                    'rules'    => array(
                                    'to'             => 'required|different:from',
                                    'house_walletID' => 'in_array:from_and_to.*'
                                ),

                    'messages' => array(
                                    'required'  => 'ERR_00002',
                                    'different' => 'ERR_00069',
                                    'in_array'  => 'ERR_00085'
                                )

                ));
        }
    }

    /**
     * Some response was cloacked by firewall or webserver we need to validate it
     * @param  string  $level 
     * @param  array   $value The reponse body
     * @return array
     */
    private function validate_firewall_response($level, $value)
    {
        switch ($level) {

            case 'simple':
            
                if (str_contains(strtolower(remove_space($value)),'pagenotfound') || $value === false) {
                   
                    return array('result' => false, 'err_code' => 'ERR_00001:FR');

                }
                
                return array('result' => true);
        }
    }

    /**
     * This will validate if the given language key exists
     * @param  string  $level 
     * @param  array   $value language key
     * @return array
     */
    private function validate_language_key($level, $value)
    {
        switch ($level) {

            case 'simple':
            
                if (!Lang::has($value)) {
                    
                    return array('result' => false, 'err_code' => 'ERR_00001:LK');

                }

                return array('result' => true);
        }
    }

    /**
     * validate input file for uploading
     * @param  string $level 
     * @param  array $value mime type, max_size of file, input_name
     * @return array
     */
    private function validate_input_file($level, $value)
    {

        $mime_type = implode(',', $value['mime_type']);
        $file      = request()->file($value['input_name']);

        switch ($level) {
            case 'simple':
                if (request()->hasFile($value['input_name']) && $file->isvalid()) {

                    $result = $this->execute_simple_validation(array(
                        'values'   => array('file'  => $file),
                        'rules'    => array('file'  => "mimes:{$mime_type}|max:{$value['max_filesize']}"),
                        'messages' => array(
                                        'mimes' => 'ERR_00088',
                                        'max'   => 'ERR_00089:MF'
                                    )
                    ));

                    return $result;

                } else {
                    return array(
                        'result'   => false,
                        'err_code' => 'ERR_00001:IF'
                    );
                }

        return $result;

        }
    }

    /**
     * validate imgOrder of available for uploading
     * @param  string $level 
     * @param  array $value avatar_count, imgOrder, clientID
     * @return array 
     */
    private function validate_imgOrder_availability($level, $value)
    {

        switch ($level) {
            case 'simple':

                return $this->execute_simple_validation(array(
                        'values'   => array('imgOrder' => $value['imgOrder']),
                        'rules'    => array('imgOrder' => 'regex:/^[1-'.Config::get('settings.avatar.max_count').']{1}$/'),
                        'messages' => array('regex'    => 'ERR_00001:IA')
                    ));

            case 'db':

                return $this->validate_truthy('simple', array(
                        'input' => $this->repository('Rplayer')
                            ->check_availability($value['clientID'], $value['imgOrder'], $value['filter'])
                    ));

        }
    }

    /**
     * validate if value needed to show avatar is exist
     * @param  string $level 
     * @param  string $value 
     * @return array        
     */
    public function validate_show_avatar($level, $value)
    {

        switch ($level) {
            case 'simple':
                
                if (is_array($value) && !empty($value['sessionID'])) {
                    
                    return array('result' => true);

                }

                return array('result' => false);
        }
    }

    /**
     * validate imgOrder of available for uploading
     * @param  string $level 
     * @param  array $value avatar_count, imgOrder, clientID
     * @return array 
     */
    private function validate_view_payload($level, $value)
    {

        switch ($level) {

            case 'simple':

                // check if invalid payload
                if (empty($value['payload']) || !is_array($value['payload'])) {
                    
                    return array('result' => false, 'err_code' => 'ERR_00001:NVP');

                }

                // If route can access session
                // check if Authentication setting is still the same
                if ($value['payload']['has_session']) {
                    if (!isset($value['payload']['auth']) || $value['payload']['auth'] != Auth::check()) {

                        return array('result' => false, 'err_code' => '-1:NPA');

                    }
                }

                // check if valid view information
                if (empty($value['view_info']) || !is_array($value['view_info'])) {
                    
                    return array('result' => false, 'err_code' => 'ERR_00001:NVI');

                }

                // view info required data
                $required_view_informations = array('view_type','default_location','include_menu');
                if (!array_contains_all($value['view_info'], $required_view_informations)) {

                    return array('result' => false, 'err_code' => 'ERR_00001:VMK');

                }

                return array('result' => true);

        }
    }

    /**
     * This will validate if current chat message can be send or not
     * @param  string $level 
     * @param  array  $value 
     * @return array 
     */
    private function validate_chat_access($level, $value)
    {
        switch ($level) {

            case 'simple':

                return $this->execute_simple_validation(array(
                    'values'   => $value['input'],
                    'rules'    => array(
                                    'messages' => 'required',
                                    'sender'   => 'required',
                                    'receiver' => 'required',
                                ),
                    'messages' => array('required' => 'ERR_00001:RCA')
                ));

            case 'db':
            
                $chatStatus = $this->service('Ssiteconfig')->chatStatus($value['parentID']);

                if ($chatStatus['can_send']) {
                    return array('result' => true);
                } else {
                    return array('result' => true, 'required' => 'ERR_00001:CCS');
                }
        }
    }

    /**
     * This will validate if user is a guest only
     * @param  string $level 
     * @param  array  $value 
     * @return array 
     */
    private function validate_guest_access($level, $value)
    {
        switch ($level) {

            case 'simple':

                if (Auth::check()) {

                    return array('result' => false, 'err_code'=> 'ERR_00105');

                } else {

                    return array('result' => true);
                }
        }
    }

    /**
     * This will validate if user is logged in
     * @param  string $level 
     * @param  array  $value 
     * @return array 
     */
    private function validate_is_login($level, $value)
    {
        switch ($level) {

            case 'simple':

                if (Auth::check()) {

                    return array('result' => true);

                } else {

                    return array('result' => false, 'err_code' => '-3');
                }
        }
    }

    /**
     * validate if sessionID correct value
     * @param  string $level 
     * @param  array $value 
     * @return array        
     */
    private function validate_sessionID($level, $value)
    {

        switch ($level) {

            case 'simple':

                $db_sessionID = $value['db_sessionID'];
                if ($db_sessionID=='' || $db_sessionID===0 || $db_sessionID==='0') {
                    return array ('result' => false , 'err_code' => '-3:SID');
                }

                return $this->execute_simple_validation(array(
                    
                    'values' => array(
                                'db_sessionID' => $db_sessionID,
                                'sessionID'    => $value['sessionID'],
                            ),
                    
                    'rules' => array(
                                'db_sessionID' => 'same:sessionID'
                            ),
                    
                    'messages' => array(
                                'same' => '-3',
                            )
                ));

        }
    }

    /**
     * validate if lastActivity is still valid
     * @param  string $level 
     * @param  array $value 
     * @return array        
     */
    private function validate_lastActivity($level, $value)
    {

        switch ($level) {

            case 'simple':

                // Last Activity
                $last_timestamp = Layer::service('Ssiteconfig')->last_timestamp();
                if (compare_dates($last_timestamp, $value['lastActivity'], 'gt')) {
                    return array ('result' => false , 'err_code' => '-3');
                } else {
                    return array ('result' => true);
                }

        }
    }

    /**
     * validate if ip is still same
     * @param  string $level 
     * @param  array $value 
     * @return array        
     */
    private function validate_ip($level, $value)
    {

        switch ($level) {

            case 'simple':

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'current_ip'   => get_ip(),
                                    'login_ip'     => $value['lastLoginIP']
                                ),
                    
                    'rules'    => array('current_ip' => 'same:login_ip'),
                    
                    'messages' => array('same' => '-3',)
                ));

        }
    }

    /**
     * validate the request route if block 
     * @param  string $level 
     * @param  array $value 
     * @return array        
     */
    private function validate_block_request($level, $value)
    {   

        switch ($level) {

            case 'simple':

                if (Config::has('settings.CHECK_BLOCKLIST') && Config::get('settings.CHECK_BLOCKLIST')) {

                    $request_url_blocklist = Config::get('settings.request_url_blocklist');
                    if (in_array($value['request_url'], $request_url_blocklist)) {
                        
                        $player_has_blocklist = $this->repository('Rplayer')->count_player_blocklist(
                                                    $value['client']['clientID']
                                                );

                        if ($player_has_blocklist) {
                            return array('result' => false, 'err_code' => '-3');
                        }

                    }
                }

                return array('result' => true);
        }
    }

    /**
     * Validate game payload 
     * @param  string $level 
     * @param  mixed  $value 
     * @return array        
     */
    private function validate_game_payload($level, $value)
    {   

        switch ($level) {

            case 'simple':
                // payload should not be false
                if ($value === false) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'FPL');
                }

                //payload should be an array
                if (!is_array($value)) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'APL');
                }

                //payload should contain token and gameID
                if (!isset($value['token']) || !isset($value['gameID']) || !isset($value['window_key'])) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'LPL');
                }

                return array('result' => true);
        }
    }

    /**
     * Validate game payload 
     * @param  string $level 
     * @param  mixed  $value 
     * @return array        
     */
    private function validate_game_session($level, $value)
    {   

        switch ($level) {

            case 'simple':

                // session should be array
                if (!is_array($value['session'])) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'AGS');
                }

                // session should not been used yet
                if (!isset($value['is_used']) || $value['is_used']===true) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'UGS');
                }

                // verify session token
                if (!isset($value['session']['token']) || $value['session']['token']!=$value['payload']['token']) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'TGS');
                }

                // verify gameID
                if (!isset($value['session']['gameID']) || $value['session']['gameID']!=$value['payload']['gameID']) {
                    return array('result' => false, 'err_code' => 'ERR_00113', 'dcode' => 'IGS');
                }

                return array('result' => true);
        }
    }

    /**
     * This will validate lang id if valid
     * @param  string $level 
     * @param  mixed  $value 
     * @return array        
     */
    private function validate_lang_id($level, $value)
    {   

        switch ($level) {

            case 'simple':

                $languages = $this->service('Ssiteconfig')->theme('languages');

                if (empty($value) || !is_array($languages) || !array_key_exists($value, $languages)) {
                    return array('result' => false, 'err_code' => 'ERR_00114');
                } else {
                    return array('result' => true);
                }
        }
    }

    /**
     * Validation for yourAnswer this will validate maximum and required input
     * @param  string $level 
     * @param  array $value 
     * @return array
     */
    private function validate_yourAnswer($level, $value)
    {

        $value['err_type'] = 'yourAnswer';

        switch ($level) {
            case 'simple':

                    $result = $this->validate_max_value('simple', $value);
                    if (!$result['result']) {

                        return $result;

                    }

                    return $this->validate_required_param('simple', $value);
                    
                break;
            
        }
    }

    /**
     * Validation for string that contain alphabet only of any languages.
     * @param  string $level 
     * @param  string $value 
     * @return array        
     */
    private function validate_alpha_language($level, $value)
    {
        switch ($level) {
            
            case 'simple':
                if (!alpha_chinese($value)) {

                    return array('result' => false, 'err_code' => 'ERR_00109');

                }

                return $this->execute_simple_validation(array(
                    
                    'values'   => array(
                                    'value' => $value
                                ),
                    
                    'rules'    => array(
                                    'value' => 'required'
                                ),
                    
                    'messages' => array(
                                    'required'  => 'ERR_00002', 
                                )
                ));
        }
    }
} 
