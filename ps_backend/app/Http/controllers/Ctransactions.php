<?php

namespace Backend\controllers;

use Response;
use Auth;
use Input;
use Config;
use Layer;

/**
 * Controller for all transactions that involves money
 * 	
 * @author PS Team
 */
class Ctransactions extends Basecontroller {

    /**
     * Main entry point for getting transactions
     * @return mixed
     */
    public function get_transactions()
    {
        $type = strtolower(Input::get('g'));
        
        $this->service('Svalidate')->validate(array(
            'required_param' => array('value' => array('input' => $type, 'type' => 'hidden'))
        ), true);
                
        switch ($type) {
            case 'balance'          : return $this->get_balance();
            case 'api_balance'      : return $this->get_api_balance();
            case 'statement'        : return $this->get_statement();
            case 'statementdetails' : return $this->get_statement_details();
            case 'transaction_logs' : return $this->get_transaction_logs();
            case 'running_bets'     : return $this->get_running_bets();
        }
    }

    /**
     * Main entry point for processing transactions
     * @return mixed
     */
    public function process_transactions()
    {
        $type = strtolower(Input::get('ps_form-process'));
        
        $this->service('Svalidate')->validate(array(
            'required_param' => array('value' => array('input' => $type, 'type' => 'hidden'))
        ), true);

        switch ($type) {
            case 'deposit_confirmation': return $this->process_deposit_confirmation();
            case 'withdrawal_request'  : return $this->process_withdrawal_request();
            case 'fund_transfer'       : return $this->process_fund_transfer();
        }
    }
   
    /**
     * Balance Report
     * Get balance report to be display in:
     * • Balance Table
     * • Account > Balance
     * 
     * @return array
     */
    private function get_balance() 
    {        
        return array(
            'result'           => true,
            'playableBalance'  => custom_money_format(Auth::user()->playableBalance),
            'availableBalance' => custom_money_format(Auth::user()->availableBalance),
            'usedBalance'      => $this->repository('Rtransactions')->get_usedbalance(
                                    Auth::user()->clientID,
                                    Auth::user()->derived_status_id,
                                    function(&$usedBalance) {
                                        $usedBalance->_TID = $this->service('Scrypt')->crypt_encrypt($usedBalance->tableID);
                                        $usedBalance->_GID = $this->service('Scrypt')->crypt_encrypt($usedBalance->gameID);
                                    }
                                )
        );
    }

    /**
     * This will get wallet balances in API
     * @return array
     */
    private function get_api_balance()
    {
        if (Input::has('company')) {
            $companies    = Input::get('company');
            $is_encrypted = true;
        } else {
            $companies    = $this->service('Ssiteconfig')->transferable_walletIDs();
            $is_encrypted = false;
        }

        $wallet_balances = $this->service('Sapi')->get_wallet_balance(
                            $companies, 
                            array('clientID' => Auth::user()->clientID, 'parentID' =>  Auth::user()->parentID),
                            $is_encrypted
                        );

        return array(
            'result'           => true, 
            'balance'          => $wallet_balances['balance'], 
            'availableBalance' => custom_money_format(Auth::user()->availableBalance),
            'playableBalance'  => custom_money_format(Auth::user()->playableBalance)
        );
    }

    /**
     * This will get statement of current player for the given month
     * @return array
     */
    private function get_statement()
    {
        $month_number = Input::get('no');

        $this->service('Svalidate')->validate(array('statement_month' => array('value' => $month_number)), true);

        return $this->repository('Rtransactions')->get_statement(
            Auth::user()->clientID,
            $month_number,
            array($this->service('Scrypt'),'crypt_encrypt')
        );

    }

    /**
     * This will get the details of transactionID filtered by type and page number 
     * @return object
     */
    private function  get_statement_details()
    {
        $transactionID   = $this->service('Scrypt')->crypt_decrypt(Input::get('trans'));
        $transactionType = Input::get('type');

        $this->service('Svalidate')->validate(array(

            'transactionID'  => array(
                                    'value' => array(
                                                'transactionID'=> $transactionID, 
                                                'clientID'     => Auth::user()->clientID
                                            )
                                ),

            'required_param' => array('value' => array('input' => $transactionType, 'type' => 'hidden')),

        ), true);

        return $this->repository('Rtransactions')->get_statement_details(
            array('transactionID' => $transactionID, 'transactionType' => $transactionType),
            array(
                'limits'               => $this->service('Ssiteconfig')->paging_offset_limit(Input::get('p')),
                'disabled_bet_details' => Config::get('settings.report.disabled_bet_details'),
            ),
            array($this->service('Scrypt'), 'crypt_encrypt')
        );
    }
    
    /**
     * this will get transaction logs report
     * @return array  
     */
    private function get_transaction_logs()
    {
        $start_date_time = Input::get('s_d'); // n/j/Y g:i A

        $this->service('Svalidate')->validate(array('transactionlogs_date' => array('value' => $start_date_time)), true);
        return $transactions = $this->repository('Rtransactions')->get_transaction_logs(
            
            array(
                'start_date_time' => $start_date_time,
                'range'           => Config::get('settings.report.transaction_logs_range'),
                'limits'          => $this->service('Ssiteconfig')->paging_offset_limit(Input::get('p')),
                'disabled_bet_details' => Config::get('settings.report.disabled_bet_details'),
                'accounting_time' => $this->service('Ssiteconfig')->accounting_time()

            ),

            Auth::user()->clientID,

            array($this->service('Scrypt'), 'crypt_encrypt')
            
        );

        return $transactions;
    }

    /**
     * This will get all running bets of player
     * @return array
     */
    private function get_running_bets()
    {
        return $this->repository('Rtransactions')->get_running_bets(
            Auth::user()->clientID,
            array(
                'limits'               => $this->service('Ssiteconfig')->paging_offset_limit(Input::get('p')),
                'disabled_bet_details' => Config::get('settings.report.disabled_bet_details'),
            ),
            array($this->service('Scrypt'),'crypt_encrypt')
        );
    }

    /**
     * This will process deposit request
     * @return array
     */
    private function process_deposit_confirmation()
    {
        // General validation
        $this->service('Svalidate')->validate(array(
            'site_access'        => array('value' => array()),
            'transaction_access' => array('value' => array('auth_user' => Auth::user()))
        ), true);


        //enchancement start here

        $has_captcha = $this->service('Scaptcha')->transaction_captcha('deposit_captchainput','deposit');

        //ends here

        $input_validations = array();

        // bank account informations
        if (Config::get("settings.DEPOSIT_PROFILE_BANKINFO")) {

            $bankName        = Auth::user()->bankName;
            $accountBankName = Auth::user()->accountBankName;
            $accountBankNo   = Auth::user()->accountBankNo;

            // validate bank account info from DB
            $this->service('Svalidate')->validate(array(

                'ps_dep_bankName'           => array(
                                                'value' => array('input' => $bankName, 'type' => 'multiple'),        
                                                'validator' => 'required_param'
                                            ),

                'ps_dep_accountBankName'    => array(
                                                'value' => array('input' => $accountBankName, 'type' => 'multiple'), 
                                                'validator' => 'required_param'
                                            ),
                
                'ps_dep_bankName_BankInput1'=> array(
                                                'value' => array('input' => $accountBankNo,'type' => 'multiple'),   
                                                'validator' => 'required_param'
                                            )
            ), true);

        } else {

            $bankName            = Input::get('ps_dep_bankName');
            $accountBankName     = Input::get('ps_dep_accountBankName');
            $accountBankNo_info  = $this->repository('Rwhitelabel')->format_bank_number(
                                    array(
                                        Input::get('ps_dep_bankName_BankInput1'),
                                        Input::get('ps_dep_bankName_BankInput2'),
                                        Input::get('ps_dep_bankName_BankInput3'),
                                        Input::get('ps_dep_bankName_BankInput4'),
                                        Input::get('ps_dep_bankName_BankInput5'),
                                    ),
                                    $bankName
                                );
            $accountBankNo       = $accountBankNo_info['accountNumber'];

            // validate bank account info from user input
            $input_validations['ps_dep_bankName']            = array(
                                                                   'value' => $bankName, 
                                                                   'validator' => 'bankName'
                                                               );

            $input_validations['ps_dep_accountBankName']     = array(
                                                                   'value' => $accountBankName, 
                                                                   'validator' => 'no_symbol'
                                                               );

            $input_validations['ps_dep_bankName_BankInput1'] = array(
                                                                   'value' => $accountBankNo_info, 
                                                                   'validator' => 'bank_input'
                                                               );

        }

        // amount
        $amount = Input::get('ps_dep_Amount');

        $input_validations['ps_dep_Amount'] = array(
                                                'value'     => array('amount' => $amount, 'bankName' =>  $bankName), 
                                                'validator' => 'deposit_amount'
                                            );

        $this->service('Svalidate')->validate($input_validations);

        $this->service('Scaptcha')->save_transaction_captcha();
        
        $previous_balance = array(
                                'playableBalance'  => Auth::user()->playableBalance,
                                'availableBalance' => Auth::user()->availableBalance,
                                'cashBalance'      => Auth::user()->cashBalance,
                                'totalBalance'     => Auth::user()->totalBalance
                            );

        return $this->standard_transfer_procedure('deposit_confirmation', array(
            'amount'          => $amount,
            'bankName'        => $bankName,
            'accountBankName' => $accountBankName,
            'accountBankNo'   => $accountBankNo,
            'has_captcha'     => $has_captcha
        ), $previous_balance ); 
    }

    /**
     * This will process withdrawal requests
     * @return array
     */
    private function process_withdrawal_request()
    {
        // General validation
        $this->service('Svalidate')->validate(array(
            'site_access'        => array('value' => array()),
            'transaction_access' => array('value' => array('auth_user' => Auth::user())),
            'withdrawal_access'  => array('value' => Auth::user())
        ), true);

        $has_captcha = $this->service('Scaptcha')->transaction_captcha('withdrawal_captchainput','withdrawal');

        $amount = Input::get('ps_with_Amount');
 
        $this->service('Svalidate')->validate(array(

            'ps_with_password' => array(

                                    'value'     => array(
                                                    'input_password'   => Input::get('ps_with_password'), 
                                                    'password'         => Auth::user()->password, 
                                                    'salt'             => Auth::user()->salt
                                                ), 

                                    'validator' => 'current_password'

                                ),

            'ps_with_Amount'   => array(
                                    'value'     => $amount, 
                                    'validator' => 'transfer_amount'
                                )

        ));

        $previous_balance = array(
                                'playableBalance'  => Auth::user()->playableBalance,
                                'availableBalance' => Auth::user()->availableBalance,
                                'cashBalance'      => Auth::user()->cashBalance,
                                'totalBalance'     => Auth::user()->totalBalance
                            );

        $deduct_player_availablebalance = $this->repository('Rplayer')->deduct_availableBalance(
                                            Auth::user()->clientID,
                                            $amount
                                        );

        $this->service('Svalidate')->validate(array(

            'withdrawal_availableBalance' => array(
                                                'value'     => array(
                                                                'input' => $deduct_player_availablebalance,
                                                                'type'  => 'withdrawal_availableBalance'
                                                            ),

                                                'validator' => 'truthy'

                                            )

        ), true);

        $this->service('Scaptcha')->save_transaction_captcha();

        $return = $this->standard_transfer_procedure('withdrawal_request', array(
                    'amount'      => $amount, 
                    'has_captcha' => $has_captcha
                ), $previous_balance);
        
        $this->service('Ssocket')->push(array(
            'session_id' => Auth::user()->sessionID, 
            'event'      => 'BALANCE', 
            'message'    => array('balance' => non_money_format($return['_ab']))
        ));

        // Add socket call for balance going to tangkas
        $this->service('Ssocket')->push(array(
            'namespace'  => 'games',
            'session_id' => Auth::user()->sessionID, 
            'room'       => 'tangkas', 
            'event'      => 'balance', 
            'message'    => array('balance' => non_money_format($return['_ab']))
        ));

        return $return;
    }

    /**
     * This will cover the standard transfer procedure, just right after input validations
     * @param  string $process          The transfer process from Input 'g', e.g. withdrawal_request, deposit_confirmation
     * @param  array  $transfer         [amount, bankName, accountBankName, accountBankNo]
     * @param  array  $previous_balance 
     * @return array
     */
    private function standard_transfer_procedure($process, $transfer, $previous_balance)
    {
        // additional transfer informations
        $client_transfer_info        = $this->repository('Rplayer')->get_transfer_information(Auth::user()->clientID);
        $transfer['accounting_time'] = $this->service('Ssiteconfig')->accounting_time();

        switch ($process) {
            case 'withdrawal_request':

                    $transferType_category = 'Withdrawal';
                    $transferType_code     = 'WR';

                break;
            
            case 'deposit_confirmation':

                $transferType_category = 'Deposit';
                $transferType_code     = 'DC';

                break;
        }

        $transferIDs = $this->repository('Rtransactions')->save_transfer($transferType_category,$client_transfer_info,$transfer);

        // log
        $this->service('Slogger')->db(array(
            'transferID'   => $transferIDs['client_transferID'],
            'transferType' => $transferType_category,
            'amount'       => $transfer['amount'],
            'from'         => $previous_balance,
            'to'           => array(
                                'playableBalance'  => $client_transfer_info['client']->playableBalance,
                                'availableBalance' => $client_transfer_info['client']->availableBalance,
                                'cashBalance'      => $client_transfer_info['client']->cashBalance,
                                'totalBalance'     => $client_transfer_info['client']->totalBalance
                            )

        ),'transfer');

        // send socket agent for withdrawl and deposit
        $this->service('Ssocket')->push(array(
            'session_id' => $this->repository('Rplayer')->get_agent_sessionIDs(Auth::user()->parentID),
            'room'       => 'agent_site',
            'event'      => "PS_{$transferType_category}",
            'message'    => array('transferID' => $transferIDs['client_transferID'], 'code' => $transferType_code)
        ));
        
        // response 
        return array(
            'result'      => true,
            'has_captcha' => $transfer['has_captcha'],
            'message'     => array(
                            '{{@lang.language.'.$process.'}}',
                            '{{@lang.messages.'.strtolower($transferType_category).'_success}}'
                        ), 
            '_ab'         => custom_money_format($client_transfer_info['client']->availableBalance)
        );
    }

    /**
     * This will process fund transfers to other platforms
     * @return array
     */
    private function process_fund_transfer()
    {
        // General validation
        $this->service('Svalidate')->validate(array(
            'site_access'          => array('value' => array()),
            'transaction_access'   => array('value' => array('auth_user' => Auth::user())),
            'fund_access'          => array('value' => Auth::user())
        ), true);

        //enchancement start here

        $has_captcha = $this->service('Scaptcha')->transaction_captcha('fund_transfer_captchainput','fund_transfer');

        //ends here

        $amount           = non_money_format(Input::get('ps_tf_amount'));
        $house_walletID   = Config::get('settings.products.house_walletID');
        $decypt_non_house = function($value) use($house_walletID) {

                                if ($value == $house_walletID) {

                                   return $value;

                                } else {

                                    return $this->service('Scrypt')->crypt_decrypt($value);

                                }

                            };

        $from    = $decypt_non_house(Input::get('ps_tf_from'));
        $to      = $decypt_non_house(Input::get('ps_tf_to'));
        $process = ($from == $house_walletID) ? 'deposit' : 'withdraw';

        // per field validations
        $this->service('Svalidate')->validate(array(
            'ps_tf_amount' => array(
                                'value'     => array(
                                                'amount'    => $amount, 
                                                'process'   => $process,
                                                'walletID'  => $from,
                                                'client'    => array(
                                                                'availableBalance' => Auth::user()->availableBalance,
                                                                'clientID'         => Auth::user()->clientID,
                                                                'parentID'         => Auth::user()->parentID
                                                            )
                                                
                                            ),
                                'validator' => 'fund_amount'
                            ),
            'ps_tf_from'   => array(
                                'value'     => array(
                                                'process' => $process,
                                                'from'    => $from,
                                                'to'      => $to,
                                            ),
                                'validator' => 'fund_from'
                            ),
            'ps_tf_to'     => array(
                                'value'     => array(
                                                'process' => $process,
                                                'from'    => $from,
                                                'to'      => $to,
                                            ),
                                'validator' => 'fund_to'
                            )
        ));
        
        $non_house_walletID = ($from != $house_walletID)? $from : $to;

        $this->service('Scaptcha')->save_transaction_captcha();

        $return = $this->service('Sapi')->fund_transfer(
                    $process,
                    array('walletID' => $non_house_walletID,    'amount'   => $amount),
                    array('clientID' => Auth::user()->clientID, 'parentID' => Auth::user()->parentID)
                );

        $return['has_captcha'] = $has_captcha;
        
        $return['balance']          = $this->get_api_balance()['balance'];
        $return['availableBalance'] = $this->get_balance()['availableBalance'];

        return $return;
    }

    /**
     * This will get games bet details URL
     * @return array
     */
    public function bet_details()
    {
        $transactionDetID  = $this->service('Scrypt')->crypt_decrypt(Input::get('_BID'));
        $clientID          = Auth::user()->clientID;
        $is_rso            = Input::get('is_rso');
        $transactiondetail = $this->repository('Rtransactions')
                                  ->get_transactionDetail(
                                        $this->service('Scrypt')->crypt_decrypt(Input::get('_BID')), 
                                        $clientID
                                    );
        $language_id       = $this->service('Ssiteconfig')->game_lang_format(array(
                                'gameID'    => $transactiondetail->gameID,
                                'serverID'  => $transactiondetail->serverID,
                                'productID' => $transactiondetail->productID
                            ));

        $this->service('Svalidate')->validate(array(
            'game_transactionDetail' => array(
                                            'value'     => array(
                                                            'input' => $transactiondetail,
                                                            'type'  => 'game_transactionDetail'
                                                        ),
                                            'validator' => 'truthy'
                                        )
        ), true);

        switch ($transactiondetail->productID) {

            //skill games
            case 2:

                $result        = json_decode($transactiondetail->result);
                $build_payload = http_build_query(array(
                                    'bet_id'     => $transactiondetail->txnID,
                                    'lang'       => $language_id,
                                    'table_name' => $result->table,
                                    'client_id'  => $this->service('Scrypt')->aes_encrypt($clientID,true),
                                    'isAdmin'    => 0,
                                    'rso'        => $this->service('Ssiteconfig')->rso_folder()
                                ));
                // encrypt final param
                $url_param     = array(
                                    'payload' => urlencode($this->service('Scrypt')->aes_encrypt(urldecode($build_payload)))
                                ); 
                $window_size   = Config::get('settings.report_window.default');

                break;

            //live games
            case 4:

                $build_payload = http_build_query(array(
                                    'bet_id'    => $transactiondetail->txnID,
                                    'lang'      => $language_id,
                                    'client_id' => $this->service('Scrypt')->aes_encrypt($clientID,true),
                                    'rso'       => $this->service('Ssiteconfig')->rso_folder()
                                ));
                $url_param     = array(
                                    'payload' => $this->service('Scrypt')->aes_encrypt(urldecode($build_payload))
                               ); 

                $window_size = Config::get('settings.report_window.default');

                break;

            case 8:
            case 6:
                $build_payload = $this->service('Sapi')->bet_details_payload(
                                                $transactiondetail->transactionDetID,
                                                $transactiondetail->serverID, 
                                                $transactiondetail->productID
                                            );
                $url_param   = array(
                                'payload' => urldecode($build_payload) 
                            );

                $window_size = Config::get('settings.report_window.live_casino');

                break;

            //multiplayer
            case 7:

                $result        = json_decode($transactiondetail->result);
                $build_payload = http_build_query(array(
                                    'bet_id'     => $transactiondetail->txnDetID,
                                    'lang'       => $language_id,
                                    'client_id'  => $this->service('Scrypt')->aes_encrypt($clientID,true),
                                    'rso'        => $this->service('Ssiteconfig')->rso_folder()
                                ));
                // encrypt final param
                $url_param     = array(
                                    'payload' => urlencode($this->service('Scrypt')->aes_encrypt(urldecode($build_payload)))
                                ); 
                $window_size   = Config::get('settings.report_window.default');

                break;
            
            default:

                switch ($transactiondetail->gameID) {

                    case 20001:
                        
                        $url_param   = array(
                                        'payload' => $this->service('Scrypt')->aes_encrypt(http_build_query(array(
                                                        'bet_id' => $transactiondetail->txnDetID,
                                                        'lang'   => $language_id,
                                                        'rso'    => $this->service('Ssiteconfig')
                                                                         ->rso_folder()
                                                    )))
                                    ); 

                        $window_size = Config::get('settings.report_window.default');

                        break;
                    
                    default:
                        
                        $url_param   = array(
                                        'payload' => $this->service('Scrypt')->aes_encrypt(http_build_query(array(
                                                        'bet_id' => $transactiondetail->txnID,
                                                        'lang'   => $language_id,
                                                        'rso'    => $this->service('Ssiteconfig')
                                                                         ->rso_folder()
                                                    )))
                                    ); 

                        $window_size = Config::get('settings.report_window.default');
                }
        }

        return array(
            'result'      => true,
            'payload'     => $url_param['payload'],
            'url'         => url_add_query($this->service('Ssiteconfig')->get_dynamic_domain(
                                $this->repository('Rdbconfig')->get_server_url($transactiondetail->serverID,'report')
                            ),$url_param),
            'window_size' => $window_size
        );
    }

}