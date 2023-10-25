<?php

namespace Backend\repositories;

use Layer;

/**
 * Repositories for all transaction involving money
 */
class Rtransactions extends Baserepository {

    public $models = array(
                        'Musedbalance',
                        'Mtransfer',
                        'Mwinner',
                        'Mcurrency',
                        'Mtransaction',
                        'Mtransactiondetail',
                        'Mcredit',
                        'Mtransferlog'
                    );

    public $event_descriptions = array(
                                    'D' => 'Denied', 
                                    'R' => 'Running', 
                                    'L' => 'Lose', 
                                    'W' => 'Won', 
                                    'V' => 'Voided',
                                    'P' => '--',
                                    'A' => '--',
                                    'X' => 'Rejected',
                                    'O' => 'Draw', 
                                    'B' => 'Aborted', 
                                );

    private $no_auto_event = array('V','B','A','R');

    private $statement_details_formatter = array(
                                                'Promotion' => 'format_bet_details', 
                                                'Betting'   => 'format_bet_details',
                                                'Transfer'  => 'format_transfer_details',
                                                'Credit'    => 'format_credit_details'
                                        );

    private $amount_decimal_places = 6;

    /**
     * This will get all usedBalance of clientID
     * @param int $clientID
     * @return array
     */
    public function get_usedbalance($clientID, $derived_status_id, $encrypt) {

        $usedBalances = $this->model('Musedbalance')->get_usedbalance($clientID);

        foreach ($usedBalances as $usedBalance) {
            if (is_callable($encrypt)) {
                $encrypt($usedBalance);
            }

            $usedBalance->_TNAME      = $usedBalance->tableName;
            
            $usedBalance->derived_currency_amount = custom_money_format($usedBalance->derived_currency_amount);

        }

        return $usedBalances->toArray();
    }

    /**
     * This will check if plugin data is sufficient for new WL to display       
     * @param  array   $transactions_config 
     * @param  string  $site_information    [whiteLabelID, start_date]  
     * @return boolean                     
     */
    private function is_plugin_qouta($transactions_config, $site_information)
    {

        foreach ($transactions_config as $transaction_type => $transaction_config) {

            switch ($transaction_type) {

                case 'withdrawal':
                case 'deposit':

                    $transaction_count = $this->model('Mtransfer')->count_latest(
                                            $transaction_type,
                                            $site_information,
                                            $transactions_config[$transaction_type]['minimum_amount']
                                        );

                    break;

                case 'winner':

                    $transaction_count = $this->model('Mwinner')->count_latest(
                                            $site_information,
                                            $transactions_config[$transaction_type]
                                        );
                    
                    break;
                    
            }

            if ($transaction_count >= $transactions_config[$transaction_type]['display_qouta']) {
                
                return true;

            }

        }

        return false;

    }

    /**
     * Get transaction data for plugin display
     * @param  array   $transactions_config      
     * @param  array   $site_information  [is_check_qouta, start_date, whiteLabelID]
     * @return array    
     */
    public function get_plugin_data($transactions_config, $site_information) 
    {   
        
        // check existing data sufficiency
        if ($site_information['is_check_qouta']) {

            $is_display_plugin = $this->is_plugin_qouta($transactions_config,$site_information);

            if ($is_display_plugin == false) {
                
                // data is insufficient, cannot display 
                return array('display' => false);

            }

        }

        // get data
        $transactions=array('display' => true);
        foreach ($transactions_config as $type => $sub_types) {

            switch ($type) {

                case 'deposit':
                case 'withdrawal':

                    $transactions[$type] = $this->format_plugin_data(

                                            $this->model('Mtransfer')->get_latest(
                                                $type,
                                                $site_information['whiteLabelID'],
                                                $transactions_config[$type]
                                            ),

                                            $type
                                        );

                    break;

                case 'winner':

                    foreach ($transactions_config[$type]['productIDs'] as $productID) {
                        
                        $winner_key                = $transactions_config[$type]['key_prefix'].$productID;
                        $transactions[$winner_key] = $this->format_plugin_data(

                                                        $this->model('Mwinner')->get_latest(
                                                            $productID,
                                                            $transactions_config[$type]
                                                        ),

                                                        $type
                                                    );
                    }

                    break;
            }

        }

       return $transactions;
    }

    /**
     * This will format raw transactions data from DB for display
     * @param  object      &$ref_content     reference transaction data object
     * @param  string      $type             transaction type
     * @return none
     */
    private function format_plugin_data($transactions, $type) 
    {   

        foreach ($transactions as $transaction) {

            // format displayName for display
            if ((strlen($transaction->displayName) >= 6) && (strlen($transaction->displayName) <= 8)) {

                $transaction->displayName = substr($transaction->displayName, 0, 4) . '****';

            } else if (strlen($transaction->displayName) >= 9) {

                $transaction->displayName = substr($transaction->displayName, 0, 6) . '****';

            } else {

                $transaction->displayName = 'newplayer';
            }
            
            // cut and sanitize displayName
            $transaction->displayName = escape_string($transaction->displayName);

            $transaction->amount      = custom_money_format($transaction->derived_currency_amount);

            if (!isset($transaction->product)) {

                $transaction->product = $type;
                
            }

        }

        // convert to array
        return $transactions->toArray();

    }

    /**
     * This will check if transactionID belongs to clientID
     * @param  int $transactionID
     * @param  int $clientID     
     * @return boolean         
     */
    public function is_players_transaction($transactionID, $clientID)
    {

        $record_found = $this->model('Mtransaction')->count_client_transactionID($transactionID, $clientID);

        return $record_found > 0;

    }

    /**
     * This will tell if productID can have multibet, multiplayer, or single transaction only
     * @param  int    $productID 
     * @return string
     */
    public function get_bet_type($productID)
    {
        switch ($productID) {
            case 4  : return 'multibet';
            case 2  : return 'multiplayer';
            default : return 'singlebet';
        }
    }

    /**
     * This will get the final event
     * @param  object $transaction 
     * @return string
     */
    private function get_bet_event(&$transaction)
    {
        switch ($this->get_bet_type($transaction->productID)) {

            case 'multibet':

                if (!in_array($transaction->event, $this->no_auto_event)) {
                   
                    if($transaction->totalWin > 0) {

                        return 'W';

                    } else {

                        return 'L';

                    }
                }

            default     : return $transaction->event;
        }
    }

    /**
     * This will get the product name of transaction record to be displayed in reports
     * @param  object  transaction 
     * @return string
     */
    private function get_bet_productName(&$transaction)
    {
        switch ($transaction->transactionType) {
            case 'Promotion': return 'Promotion';
            default         : 

                if ($transaction->productName == null) {
                    
                    return $transaction->productName = '';

                } else {

                    return $transaction->productName;

                }
        }
    }

    /**
     * This will get the table name of the current transaction detail
     * @param  object $transaction 
     * @return string
     */
    private function get_bet_tableName(&$transaction)
    {
        // get tableName in result column first
        if (is_object($transaction->result) && isset($transaction->result->table)) {

            $bet_result_tableName = $transaction->result->table;

        } else {

            $bet_result_tableName = $transaction->result;

        }

        switch ($transaction->event) {

            case 'P': return 'Promotion';
            case 'A':

                $tableName=$transaction->productName;

                if($transaction->result) {

                    return $bet_result_tableName;

                } else {

                    return $tableName;

                }

            default:
                switch ($transaction->productID) {
                    case 7 :
                    case 2 : 
                            if (is_string($bet_result_tableName)) {
                                return '<b>'.$transaction->gameName.'</b>'
                                         .new_line($bet_result_tableName);
                            }
                                return'<b>'.$transaction->gameName.'</b>';
                    case 6 :
                    case 3 : return $bet_result_tableName;
                    default: return $transaction->gameName;
                }

        }

    }

    /**
     * This will get the roundID
     * @param  object $transaction 
     * @return string
     */
    private function get_bet_roundID(&$transaction)
    {

        switch ($transaction->event) {

            case 'P': return '';
            case 'A': return 'Netwin Adjustment';
            default:

                $padded_gameID = sprintf('%05d',$transaction->gameID);

                switch ($transaction->productID) {
                    case 4 : return $padded_gameID.$transaction->txnID;
                    default: return $padded_gameID.$transaction->txnDetID;
                }
        }

    }

    /**
     * This will get the betLink
     * @param  object $transaction 
     * @return string
     */
    private function get_bet_betLink(&$transaction)
    {

        if (in_array($transaction->serverID, $transaction->disabled_bet_details)) {
            return '';
        }
        switch ($transaction->event) {

            case 'A': case 'V': case 'X': case 'B': case 'P': return '';
            case 'R':

                if (!in_array($transaction->gameID, array(3))) {
                    
                    return '';

                }

            default:

                switch ($transaction->productID) {
                    case 5 : return '';
                }
        }

        return '<span id="'.$transaction->betLinkID.'"></span>';

    }

    /**
     * This will get the betLink
     * @param  object $transaction 
     * @return string
     */
    private function get_bet_reason(&$transaction)
    {   

        if (is_object($transaction->result) && isset($transaction->result->resettled)) {
            
            if (is_object($transaction->result->resettled) && isset($transaction->result->resettled->reason)) {
                return $transaction->result->resettled->reason;
            }
            
        } else {

            switch ($transaction->event) {
                case 'A': case 'P': return 'Reason: '.$transaction->message;
            }

        }

        // default
        return '';

    }

    /**
     * This will get the betLink
     * @param  object $transaction 
     * @return string
     */
    private function get_bet_description(&$transaction)
    {

        switch ($transaction->event) {
            case 'A': case 'P': return '--';

            default:

                switch ($transaction->productID) {

                    case 4:

                        return 'Total Bet Amount: '.custom_money_format($transaction->stake)
                            .new_line('Number of Transactions: '.$transaction->derived_total_transactions);

                    case 5:

                        // sports
                        if (is_object($transaction->result)) {
                            
                            if (count($transaction->result->betDetails) > 1) {

                                $sports_description='Mix Parlay<br/><br/>';

                            } else {

                                $sports_description='';

                            }

                            foreach ($transaction->result->betDetails as $key => $sports_betDetail) {
                                
                                if ($key > 1) {

                                    $sports_description.='<br/>';

                                }

                                // live
                                if (isset($sports_betDetail->live) && $sports_betDetail->live!='') {
                                    
                                    $sports_description.=$sports_betDetail->betOption
                                                       .' @Live Odds '
                                                       .$sports_betDetail->odds
                                                       ." <span class='ps_special_font'>"
                                                       .$sports_betDetail->oddType
                                                       .'</span> @'
                                                       .$sports_betDetail->live;

                                } else {

                                    $sports_description.=$sports_betDetail->betOption
                                                       .' Odds '
                                                       .$sports_betDetail->odds
                                                       ." <span class='ps_special_font'>"
                                                       .$sports_betDetail->oddType
                                                       .'</span>';
                                }

                                $sports_description.=new_line($sports_betDetail->sport
                                                   .' - '
                                                   .snake_to_space($sports_betDetail->betType))
                                                   .new_line($sports_betDetail->match)
                                                   .new_line($sports_betDetail->league
                                                   .' @ '
                                                   .custom_date_format('m/d/Y',$sports_betDetail->date))
                                                   .new_line('HT: '.$sports_betDetail->score->ht)
                                                   .new_line(' / FT: '.$sports_betDetail->score->ft);

                            }

                            return $sports_description;

                        } else {

                            return '';

                        }
                    
                    default:
                    
                        //bet list of GPI's transaction ID
                        if ($transaction->serverID == 'GPI') {
                            $bet = $transaction->result;

                            $gpi_description = '';
                            if (isset($transaction->result->bet_detail)) {
                                $gpi_description = implode(',', $transaction->result->bet_detail);   
                            }
                           
                            return $gpi_description;

                        }

                        // bet amount
                        if(isset($transaction->result->main_bet_amount)) {

                            $bet_amount=$transaction->result->main_bet_amount;

                        } else {

                            $bet_amount=$transaction->stake;

                        }

                        $default_description='Bet amount: '.custom_money_format($bet_amount);

                        // side bet amount
                        if(isset($transaction->result->side_bet_amount) && $transaction->result->side_bet_amount != 0) {

                            $default_description.=new_line('Side Bet Amount: '
                                                .custom_money_format($transaction->result->side_bet_amount));

                        }

                        // stake per point
                        if(isset($transaction->result->stake_per_point) && $transaction->result->stake_per_point != 0) {

                            $default_description.=new_line('Stake per point: '
                                                .custom_money_format($transaction->result->stake_per_point));

                        }

                        // no of bets
                        if(isset($transaction->result->n)) {

                            $default_description.=new_line('No. of bets: '.$transaction->result->n);

                        } else {

                            // if running the number of bets should be --
                            
                            if($transaction->event=='R') {

                                $default_description.=new_line('No. of bets: --');

                            }

                        }

                        // winning lines
                        if(isset($transaction->result->Slot))
                        {
                            if($transaction->result->Slot==0) {

                                $winning_line="--";

                            } else {

                                // count numbers
                                $winning_line=count(explode('|', $transaction->result->Slot));

                            }
                            $default_description.=new_line('Winning Lines: '.$winning_line);
                        }

                        // gamble
                        if(isset($transaction->result->gamble) && $transaction->result->gamble) {

                            $default_description.=new_line('Gamble');

                        }

                        return $default_description;
                }
        }

    }

    /**
     * This will get the transfer description
     * @param  object &$transaction 
     * @return object
     */
    private function get_transfer_description(&$transaction)
    {
        switch(strtolower($transaction->type)) {

            case 'deposit'   :

                switch($transaction->notificationStatusID)
                {
                    case 0: return '{{@lang.language.deposit_request}} {{@user.parent.username}}';
                    case 1: return '{{@lang.language.deposit_approved}} {{@user.parent.username}}';
                    case 4: return '{{@lang.messages.transfer_description}} {{@user.parent.username}}';
                }

            case 'withdrawal':

                switch($transaction->notificationStatusID)
                {
                    case 0 : return '{{@lang.language.withdrawal_request_to}} {{@user.parent.username}}';
                    default: return '{{@lang.language.withdrawal_approved}} {{@user.parent.username}}';

                }

            case 'adjustment': return '{{@lang.messages.transfer_adjustment}}';
            case 'cutoff'    : return '{{@lang.messages.cutoff_transfer}} {{@user.parent.username}}';
            case 'fund'      : return '{{@lang.messages.fund_transfer}} {{@user.parent.username}}';
        }
    }

    /**
     * This will format each columns of betting type transactions
     * @param  object $transaction 
     * @param  array  $get_columns 
     * @return array
     */
    private function format_bet_details(&$transaction, $get_columns, $encrypt)
    {

        // we will initialize all fields that will become dependency of more than one columns
        $transaction->result           = custom_json_decode($transaction->result);
        $transaction->event            = $this->get_bet_event($transaction);
        $transaction->betLinkID        = $transaction->rowNumber.uniqid();
        $transaction->betLink          = $this->get_bet_betLink($transaction);

        $columns = array();

        foreach ($get_columns as $column_name) {
            
            switch ($column_name) {

                case 'rowNumber': 
                    $columns[$column_name] = $transaction->rowNumber; 
                    break;

                case 'dateTime' : 
                    $columns[$column_name] = custom_date_format('m/d/Y h:i:s A', $transaction->dateTime);
                    break;

                case 'typeProduct':
                    $columns[$column_name] = $transaction->transactionType
                                            .new_line($this->get_bet_productName($transaction));
                    break;

                case 'gameDetails':
                    $columns[$column_name] = $this->get_bet_tableName($transaction)
                                            .new_line($this->get_bet_roundID($transaction))
                                            .new_line($transaction->betLink)
                                            .new_line($this->get_bet_reason($transaction))
                                            .new_line($this->get_bet_endDateTime($transaction));
                    break;

                case 'gameResult':
                    $columns[$column_name] = $this->event_descriptions[$transaction->event];
                    break;

                case 'description':
                    $columns[$column_name] = $this->get_bet_description($transaction);
                    break;


                case 'amount':
                    $columns[$column_name] = '--';
                    break;

                case 'stakeTurnover':
                    $columns[$column_name] = custom_money_format($transaction->stake)
                                            .new_line(custom_money_format($transaction->turnover));
                    break;

                case 'grossRake':
                    $columns[$column_name] = custom_money_format($transaction->grossRake);
                    break;

                case 'membersWLCommision':
                    $columns[$column_name] = custom_money_format($transaction->netWin)
                                            .new_line(custom_money_format($transaction->membercomm));
                    break;

                case 'cashCreditPlayable':
                    $columns[$column_name] = custom_money_format($transaction->actualCashBalance)
                                            .new_line(custom_money_format($transaction->actualAvailableCredit))
                                            .new_line(custom_money_format($transaction->actualPlayableBalance));
                    break;

                case 'hasBetLink':
                     $columns[$column_name] = ($transaction->betLink != '');
                     break;

                case 'betLinkID':
                     $columns[$column_name] = $transaction->betLinkID;
                     break;

                case 'transDetID':
                     if (is_callable($encrypt)) {
                        $columns[$column_name] = $encrypt($transaction->transactionDetID);
                     }
                     break;

            }

        }
        return $columns;

    }

    /**
     * This will format each columns of transfer type transactions
     * @param  object $transaction 
     * @param  array  $get_columns 
     * @return array
     */
    private function format_transfer_details(&$transaction, $get_columns) 
    {
        $columns = array();

        foreach ($get_columns as $column_name) {
            
            switch ($column_name) {

                case 'rowNumber': 
                    $columns[$column_name] = $transaction->rowNumber; 
                    break;

                case 'dateTime' : 
                    $columns[$column_name] = custom_date_format('m/d/Y h:i:s A', $transaction->dateTime);
                    break;

                case 'typeProduct' :
                    $columns[$column_name] = $transaction->transactionType;
                    break;

                case 'description':
                    $columns[$column_name] = $this->get_transfer_description($transaction);
                    break;

                case 'amount':
                    $columns[$column_name] = custom_money_format($transaction->amount);
                    break;

                case 'translatedAmount':

                    if ($transaction->notificationStatusID == 0) {

                        $columns[$column_name] = '';

                    } else {

                        switch (strtolower($transaction->type)) {

                            case 'withdrawal':
                                $columns[$column_name] = '-'.custom_money_format($transaction->amount);
                                break;
                            
                            default:
                                $columns[$column_name] = custom_money_format($transaction->amount);
                                break;
                        }

                    }

                    break;

                case 'outstandingBalance':

                    if ($transaction->notificationStatusID == 0) {

                         switch (strtolower($transaction->type)) {

                            case 'withdrawal':
                                $columns[$column_name] = '-'.custom_money_format($transaction->amount);
                                break;
                            
                            default:
                                $columns[$column_name] = custom_money_format($transaction->amount);
                                break;
                        }

                    } else {

                        $columns[$column_name] = '';

                    }

                    break;

                case 'playableBalance':
                    $columns[$column_name] = custom_money_format($transaction->actualPlayableBalance);
                    break;

                case 'cashCreditPlayable':
                    $columns[$column_name] = custom_money_format($transaction->actualCashBalance)
                                            .new_line(custom_money_format($transaction->actualAvailableCredit))
                                            .new_line(custom_money_format($transaction->actualPlayableBalance));
                    break;

                case 'hasBetLink':
                    $columns[$column_name] = false;
                    break;

                case 'transDetID':
                case 'betLinkID':
                case 'membersWLCommision':
                case 'grossRake':
                case 'stakeTurnover':
                case 'gameResult':
                case 'gameDetails': 
                    $columns[$column_name] = '';
                    break;

            }

        }

        return $columns;
    }

    /**
     * This will format each columns of transfer type transactions
     * @param  object $transaction 
     * @param  array  $get_columns 
     * @return array
     */
    private function format_credit_details(&$transaction, $get_columns) 
    {
        $columns = array();

        foreach ($get_columns as $column_name) {
            switch ($column_name) {

                case 'rowNumber': 
                    $columns[$column_name] = $transaction->rowNumber; 
                    break;

                case 'dateTime':
                    $columns[$column_name] = custom_date_format('m/d/Y h:i:s A', $transaction->dateTime);
                    break;

                case 'typeProduct':
                    $columns[$column_name] = $transaction->transactionType;
                    break;

                case 'description':
                    $columns[$column_name] = '{{@lang.language.credit_description}} {{@user.parent.username}}';
                    break;

                case 'amount':
                    $columns[$column_name] = custom_money_format($transaction->amount);
                    break;

                case 'creditLimit':
                    $columns[$column_name] = custom_money_format($transaction->newCreditLimit);
                    break;

                case 'playerTotalBalance':
                    $columns[$column_name] = custom_money_format($transaction->actualTotalBalance);
                    break;

                case 'playableBalance':
                    $columns[$column_name] = custom_money_format($transaction->actualPlayableBalance);
                    break;

                case 'cashCreditPlayable':
                    $columns[$column_name] = custom_money_format($transaction->actualCashBalance)
                                            .new_line(custom_money_format($transaction->actualAvailableCredit))
                                            .new_line(custom_money_format($transaction->actualPlayableBalance));
                    break;

                case 'hasBetLink':
                    $columns[$column_name] = false;
                    break;

                case 'transDetID':
                case 'betLinkID':
                case 'membersWLCommision':
                case 'grossRake':
                case 'stakeTurnover':
                case 'gameResult':
                case 'gameDetails': 
                    $columns[$column_name] = '';
                    break;
            }
        }

        return $columns;
    }


    /**
     * This will format date for transaction tables query
     * Used for selects only
     * @param  string $start_date
     * @param  string $range 
     * @return array
     */
    private function transaction_date_format($start_date, $range = '1 day', $accounting_time)
    {

        // make sure start date has correct format
        $start_date = custom_date_format('Y-m-d H:i:s',$start_date);

        // compute end date
        $end_date   = calculate_date_time($start_date, '-1 minute +59 second +'.$range);

        return array(
            'start_date' => $start_date.'.000000',
            'end_date'   => $end_date.'.999999',
            'date_only'  => $this->get_accounting_date(
                                $accounting_time,
                                $start_date
                            )
        );

    }

    /**
     * This will get all statement of clientID for the given range
     *     NOTES:  accounting_time parameter removed 
     *     where clause date already save based on accounting time
     * @param  int    $clientID  
     * @param  int    $month_number     1 = current, 2 = previous 1 mo, 3 = previous 2 mo
     * @return array
     */
    public function get_statement($clientID, $month_number, callable $encrypt)
    {

        $statement_date  = array(
                                'start_date' => date('Y-m-01', strtotime(date('Y-m').' -'.($month_number-1).' month')), 
                                'end_date' => date('Y-m-t', strtotime(date('Y-m').' -'.($month_number-1).' month'))
                            );

        $transactions    = $this->model('Mtransaction')->get_statement($clientID, $statement_date);

        $rows   = array();
        $total  = 0;
        $footer = array(
                    'grossRake'   => 0,
                    'turnover'    => 0,
                    'commission'  => 0,
                    'cashBalance' => 0,
                    'credit'      => 0
                );

        foreach ($transactions as $key => $transaction) {
            
            # initialization
            $rows[$key]['rowNumber']      = ++$total;
            $rows[$key]['date']           = custom_date_format('m/d/Y', $transaction->date);
            if (is_callable($encrypt)) {
                $rows[$key]['transactionID']  = $encrypt($transaction->transactionID);
            }

            $rows[$key]['product']        = $this->get_bet_productName($transaction);

            $rows[$key]['type']           = $transaction->transactionType;

            foreach ($footer as $field => $value) {
                
                if (is_numeric($transaction->$field)) {
                
                    $footer[$field] += $transaction->$field;

                }

            }

            $rows[$key]['turnover']      =  custom_money_format($transaction->turnover,   array('fallback' => ''));
            $rows[$key]['grossRake']     =  custom_money_format($transaction->grossRake,  array('fallback' => ''));
            $rows[$key]['commission']    =  custom_money_format($transaction->commission, array('fallback' => ''));
            $rows[$key]['cashBalance']   =  custom_money_format($transaction->cashBalance);
            $rows[$key]['credit']        =  custom_money_format($transaction->credit);

        }

        foreach ($footer as $field => $value) {
                
            $footer[$field] = custom_money_format($value);

        }

        return compact('rows', 'footer', 'total');
    }

    /**
     * This will get details of a specific transaction column
     * @param  array  $transactionID 
     * @param  array  $transactionType 
     * @param  array  $page_data             [
     *                                           limits => [offset, limit],
     *                                           disabled_bet_details
     *                                       ]
     * @return array
     */
    public function get_statement_details($transaction_data ,$page_data , callable $encrypt)
    {

        $total  = 0;
        $limits = $page_data['limits'];
        switch ($transaction_data['transactionType']) {

            case 'Promotion':
            case 'Betting'  :

                $transactions = $this->model('Mtransactiondetail')->get_settled_bets(
                                                                        $transaction_data['transactionID'],
                                                                        $limits
                                                                    );

                if (count($transactions)) {
                    
                    $total = $this->model('Mtransactiondetail')->count_settled_bets($transaction_data['transactionID']);

                }

                $get_columns  = array(
                                    'rowNumber',
                                    'dateTime',
                                    'description',
                                    'gameDetails',
                                    'gameResult',
                                    'grossRake',
                                    'hasBetLink',
                                    'membersWLCommision',
                                    'stakeTurnover',
                                    'transDetID',
                                    'betLinkID'
                                );

                break;

            case 'Transfer':

                $transactions = $this->model('Mtransfer')->get_active_details(
                                                                $transaction_data['transactionID'],
                                                                $limits
                                                            );

                if (count($transactions)) {
                    
                    $total = $this->model('Mtransfer')->count_active_details($transaction_data['transactionID']);

                }

                $get_columns  = array(
                                    'rowNumber',
                                    'dateTime',
                                    'description',
                                    'translatedAmount',
                                    'outstandingBalance',
                                    'playableBalance'
                                );
                break;

            case 'Credit':

                $transactions = $this->model('Mcredit')->get_active_details(
                                                            $transaction_data['transactionID'],
                                                            $limits
                                                        );
                
                if (count($transactions)) {
                    
                    $total = $this->model('Mcredit')->count_active_details($transaction_data['transactionID']);

                }

                $get_columns  = array(
                                    'rowNumber',
                                    'dateTime',
                                    'description',
                                    'creditLimit',
                                    'playerTotalBalance',
                                    'playableBalance'
                                );
                break;

        }

        $rows = array();

        $formatter = $this->statement_details_formatter[$transaction_data['transactionType']];

        foreach ($transactions as $key => $transaction) {

            $transaction->transactionType = $transaction_data['transactionType'];
            $transaction->rowNumber       = ($key+1) + $limits['offset'];

            if ($formatter == 'format_bet_details') {
                $transaction->disabled_bet_details = $page_data['disabled_bet_details'];
                $rows[$key] = $this->$formatter($transaction, $get_columns, $encrypt);
            } else {
                $rows[$key] = $this->$formatter($transaction, $get_columns);
            }

        }

        return compact('rows','total');
    }

    /**
     * This will get the transaction logs of the player
     * @param  array  $statement_info [start_date_time, range, limits, disabled_bet_details]
     * @param  int    $clientID       
     * @param  array  $limits              [offset, limit]
     * @return array
     */
    public function get_transaction_logs($statement_info, $clientID, callable $encrypt)
    {
        // format statememnt_date to start-date and end_date 
        $statement_date = $this->transaction_date_format(
                            $statement_info['start_date_time'], 
                            $statement_info['range'],
                            $statement_info['accounting_time']
                        );
        $limits         = $statement_info['limits'];

        $transactions   = $this->model('Mtransaction')->get_transaction_logs($statement_date, $clientID, $limits);

        if (count($transactions)>0) {

            $total = $this->model('Mtransaction')->count_transaction_logs($statement_date, $clientID);

        } else {

            $total = 0;

        }
        

        $get_columns = array(
                        'rowNumber',
                        'dateTime',
                        'typeProduct',
                        'gameDetails',
                        'gameResult',
                        'description',
                        'amount',
                        'stakeTurnover',
                        'grossRake',
                        'membersWLCommision',
                        'betLinkID',
                        'hasBetLink',
                        'transDetID',
                        'cashCreditPlayable'
                    );


        $rows = array();

        foreach ($transactions as $key => $transaction) {

            $transaction->rowNumber = ($key+1) + $limits['offset'];

            $formatter  = $this->statement_details_formatter[$transaction->transactionType];

            if ($formatter == 'format_bet_details') {
                $transaction->disabled_bet_details = $statement_info['disabled_bet_details'];
                $rows[$key] = $this->$formatter($transaction, $get_columns, $encrypt);
            } else {
                $rows[$key] = $this->$formatter($transaction, $get_columns);
            }

        }

        $hasNext = $this->has_next_transactions(
                    $statement_date['start_date'],
                    array('range'=> $statement_info['range'], 'accounting_time' => $statement_info['accounting_time']),
                    $clientID
                );

        return compact('rows','total','hasNext');
    }

    /**
     * This will check if transaction logs has next transactions for this
     * @param  string  $previous_date_time
      * @param  string  $range             
     * @param  int     $clientID           
     * @return boolean
     */
    private function has_next_transactions($previous_date_time,$date_config,$clientID)
    {
        $range = $date_config['range'];

        $next_date_time = calculate_date_time($previous_date_time,$range,'Y-m-d H:i:s.u');

        $next_date_only        = custom_date_format('Y-m-d',$next_date_time);
        $previous_date_only    = custom_date_format('Y-m-d',$previous_date_time);

        // check if next date_time is still same date
        if ($previous_date_only == $next_date_only) {

            // calculate next statement time until end of todate
            $todate_end_time         = custom_date_format('Y-m-d H:i:s.u', $next_date_only.' 24:00:00');
            $todate_remaining_hours  = substract_dates($next_date_time, $todate_end_time, 'hours');
            $statement_date          = $this->transaction_date_format(
                                            $next_date_time,
                                            $todate_remaining_hours.' hours',
                                            $date_config['accounting_time']
                                        );
            $count_next_transactions = $this->model('Mtransaction')->count_transaction_logs($statement_date, $clientID);
            
            return ($count_next_transactions > 0);

        } else {

            return false;

        }

    }

    /**
     * This will get all running bets of given clientID
     * @param  int    $clientID [description]
     * @param  array  $page_data             [
     *                                           limits => [offset, limit],
     *                                           disabled_bet_details
     *                                       ]
     * @return array
     */
    public function get_running_bets($clientID, $page_data, callable $encrypt)
    {
        $limits       = $page_data['limits'];
        $transactions = $transactions = $this->model('Mtransaction')->get_running_bets($clientID, $limits);

        if (count($transactions)>0) {

            $total             = $this->model('Mtransaction')->count_running_bets($clientID);
            $total_running_bet = $this->model('Mtransaction')->total_running_bets($clientID);

        } else {

            $total             = 0;
            $total_running_bet = 0;

        }


        $get_columns = array(
                        'rowNumber',
                        'dateTime',
                        'gameDetails',
                        'gameResult',
                        'description',
                        'stakeTurnover',
                        'betLinkID',
                        'hasBetLink',
                        'transDetID'
                    );

        $rows = array();

        foreach ($transactions as $key => $transaction) {

            $transaction->transactionType ='Betting';
            $transaction->rowNumber = ($key+1) + $limits['offset'];
            $transaction->disabled_bet_details = $page_data['disabled_bet_details'];
            $rows[$key] = $this->format_bet_details($transaction, $get_columns, $encrypt);

        }

        
        $total_running_bet = custom_money_format($total_running_bet);

        return compact('rows','total','total_running_bet');
    }

    /**
     * This will give the current accounting date based on given accounting time
     * @param  string $accounting_time      H:i:s
     * @param  string $date                 format = Y-m-d H:i:s, default = today
     * @return string
     */
    private function get_accounting_date($accounting_time, $date = false)
    {

        // set today as default
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }

        if (custom_date_format('H:i:s', $date) < custom_date_format('H:i:s', $accounting_time)) {
            
            $date = calculate_date_time($date, '-1 day');

        }

        return custom_date_format('Y-m-d',$date);
    }

    /**
     * This will save all transfer
     * @param  string $type                 deposit or withdrawal
     * @param  array  $client_transfer_info [client, parent] objects contains client info needed for transfer
     * @param  array  $transfer             note: if bankName, accountBankName, accountBankNo is empty 
     *                                      this will automatically get from client info
     * @return int                          parent transferID       
     */
    public function save_transfer($type, $client_transfer_info, $transfer)
    {
        $accounting_date   = $this->get_accounting_date($transfer['accounting_time']);
        $formatted_amount  = non_money_format($transfer['amount']);
        $transfer_datetime = timestamp_microsecond();

        // if this wasn't set get from client info
        set_default($transfer,'bankName',       $client_transfer_info['client']->bankName);
        set_default($transfer,'accountBankName',$client_transfer_info['client']->accountBankName);
        set_default($transfer,'accountBankNo',  $client_transfer_info['client']->accountBankNo);

        // transfer record for player
        $client_transactionID = $this->model('Mtransaction')->get_create_transactionID(array(
                                    'clientID'        => $client_transfer_info['client']->clientID,
                                    'transactionType' => 'Transfer',
                                    'date'            => $accounting_date
                                ))->id;

        $client_transferID    = $this->model('Mtransfer')->insert_transfer(array(
                                    'transactionID'          => $client_transactionID,
                                    'fromToclientID'         => $client_transfer_info['parent']->clientID,
                                    'type'                   => $type,
                                    'amount'                 => $formatted_amount,
                                    'actualAvailableBalance' => $client_transfer_info['client']->availableBalance,
                                    'actualPlayableBalance'  => $client_transfer_info['client']->playableBalance,
                                    //totalBalance use because clientbalance.cashBalance is not use for players 
                                    'actualCashBalance'      => $client_transfer_info['client']->totalBalance,
                                    'firstName'              => $client_transfer_info['client']->firstName,
                                    'lastName'               => $client_transfer_info['client']->lastName,
                                    'bankName'               => $transfer['bankName'],
                                    'accountBankName'        => $transfer['accountBankName'],
                                    'accountBankNo'          => $transfer['accountBankNo'],
                                    'dateTime'               => $transfer_datetime,
                                    'notificationStatusID'   => 0
                                ));


        // transfer record for parent
        $parent_transactionID  = $this->model('Mtransaction')->get_create_transactionID(array(
                                    'clientID'        => $client_transfer_info['parent']->clientID,
                                    'transactionType' => 'Transfer',
                                    'date'            => $accounting_date
                                ))->id;

        $parent_transferID    = $this->model('Mtransfer')->insert_transfer(array(
                                    'transactionID'          => $parent_transactionID,
                                    'fromToclientID'         => $client_transfer_info['client']->clientID,
                                    'type'                   => $type,
                                    'amount'                 => $formatted_amount,
                                    'actualAvailableBalance' => $client_transfer_info['parent']->availableBalance,
                                    'actualPlayableBalance'  => $client_transfer_info['parent']->playableBalance,
                                    'actualCashBalance'      => $client_transfer_info['parent']->cashBalance,
                                    'firstName'              => $client_transfer_info['parent']->firstName,
                                    'lastName'               => $client_transfer_info['parent']->lastName,
                                    'bankName'               => $client_transfer_info['parent']->bankName,
                                    'accountBankName'        => $client_transfer_info['parent']->accountBankName,
                                    'accountBankNo'          => $client_transfer_info['parent']->accountBankNo,
                                    'dateTime'               => $transfer_datetime,
                                    'notificationStatusID'   => 0,
                                    'referenceID'            => $client_transferID
                                ));

        

        return compact('parent_transferID','client_transferID');
    }

    /**
     * This will insert transfer informations to transferlog table
     * @param  array $log_data  data to be logged
     * @return void
     */
    public function insert_transferlog($log_data) 
    {
        
        switch($log_data['transferType']) {
            case 'Withdrawal': $amount = -$log_data['amount']; break;
            default          : $amount =  $log_data['amount'];       
        }

        $formatted_log_data = array(
                                'transferID' => $log_data['transferID'],
                                'username'   => $log_data['username'],
                                'from'       => to_string(array(
                                                    'Playable  Bal' => $log_data['from']['playableBalance'],
                                                    'Available Bal' => $log_data['from']['availableBalance'],
                                                    'Cash Bal'      => $log_data['from']['cashBalance'],
                                                    'Total Bal'     => $log_data['from']['totalBalance']
                                                )),
                                'to'         => to_string(array(
                                                    'Amount'        => $amount,
                                                    'Playable  Bal' => $log_data['to']['playableBalance'],
                                                    'Available Bal' => $log_data['to']['availableBalance'],
                                                    'Cash Bal'      => $log_data['to']['cashBalance'],
                                                    'Total Bal'     => $log_data['to']['totalBalance']
                                                )),
                                'description' => $log_data['transferType'],
                                'remark'      => to_string(set_default($log_data,'remark',  'Pending')),
                                'createdOn'   => $log_data['createdOn'],
                                'createdBy'   => $log_data['createdBy'],
                                'createdFrom' => $log_data['createdFrom'],
                                'ipAddress'   => $log_data['ipAddress']
                            );

        $this->model('Mtransferlog')->insert_transferlog($formatted_log_data);
    }

    /**
     * This will check if any game under productID has running transaction
     * @param  int    $clientID    
     * @param  int    $productID  
     * @param  array  $except_gameID
     * @return boolean
     */
    public function productID_running_first($clientID, $productID, $except_gameID = array())
    {
        $get_running = $this->model('Mtransaction')->productID_running_first($clientID, $productID, $except_gameID);

        if ($get_running) {
            
            return $get_running;

        } else {

            return false;

        }
    }

    /**
     * get transaction details
     * @param  int $transactionDetID 
     * @param  int $clientID         
     * @return mixed                   transaction row or null
     */
    public function get_transactionDetail($transactionDetID, $clientID)
    {
        return $this->model('Mtransactiondetail')->get_details($transactionDetID, $clientID);
    
    }

    /**
     * This will set dateTime  
     * @param  object &$transaction 
     * @return string               
     */
    public function get_bet_endDateTime(&$transaction)
    {   

        switch ($transaction->transactionType) {
            case 'Promotion':
                
                return '';
            
            default:

                if ($transaction->event == 'R') {
                    return '';
                }
                
                return custom_date_format('m/d/Y h:i:s A', $transaction->endDateTime);
        }
        
    }
}
