<?php

namespace Backend\repositories;

use DateTime;
use Lang;
use Auth;
use Layer;

/**
 * Repository for product hierarchy
 * - Company
 *     - Wallets
 *         - Products
 *             - Games
 */
class Rproducts extends Baserepository {

    public $models = array(
                    'Mproduct',
                    'Mgame',
                    'Mjackpot',
                    'Mproduct',
                    'Mwallet',
                    'Mcompanyclient',
                    'Mwebsession',
                    'Mtournament',
                    'Mtournamentdetail',
                    'Mtournamenttoprank',
                    'Mtournamentprize'
                );

    /**
     * This will get all jackpot of the games
     * @return array
     */
    public function get_jackpots($disabled_productIDs)
    {
        $jackpots = $this->model('Mjackpot')->get_jackpots($disabled_productIDs);

        foreach ($jackpots as $jackpot) {

            $jackpot->jackpot = custom_money_format($jackpot->jackpot, array('decimal_places' => 0, 'fallback' => 0));
        }

        return $jackpots->toArray();
    }
    
    /**
     * Get games details
     * 
     * @param  string $options ['filter', 'game_name', 'serverIDs_disabled', 'isTestPlayer']
     * @param  array  $product ['is_mobile_platform', 'productID']
     * @param  string $game_unique_key
     * @return array 
     */
    public function retrieve_games($options, $product, $game_unique_key) 
    {
        $is_mobile_platform = $product['is_mobile_platform'];
        $productID          = $product['productID'];
        $is_order           = (!in_array($productID, $options['unsorted_productIDs'])) ? TRUE : FALSE;      
        
        switch ($options['filter']) {

            case 'NEW':
                $rows = $this->model('Mgame')->get_new($productID, $is_order, $options['serverIDs_disabled']);
                break;

            case 'SEARCH':
                $rows = $this->model('Mgame')->search_game($options, $productID, $is_order);
                break;
            
            default: // ALL
                $rows = $this->model('Mgame')->get_all($productID, $is_order, $options['serverIDs_disabled']);
        }
        
        $ctr   = 0;
        $rows  = $rows->makeVisible('isTestModeEnabled')->toArray();
        $total = count($rows);

        foreach ($rows as &$game) {
            //add here key for same name
         
            $game['description'] = escape_string($game['description']);
            if (is_callable($game_unique_key)) {

                $game_unique_key($game);

            }
            $game['playable'] = 1;

            if ($game['isTestModeEnabled'] == 1 and $options['isTestPlayer'] == 0) {

                $game['playable'] = 0;
            }
            
            unset($game['isTestModeEnabled']);

            # Game not playable in mobile
            if ($is_mobile_platform and $game['mobile'] == 0) {
                
                $game['playable'] = 0;
            }
            
            $ctr++;
        }

        return compact('rows');
    }

    /**
     * This will get all games last results
     * 
     * @return array
     */
    public function get_games_lastResults()
    {   
        $lastResults = $this->model('Mjackpot')->get_lastResults()->toArray();

        foreach ($lastResults as &$lastResult) {
            $lastResult['lastResult'] = explode(',', $lastResult['lastResult']);
        }
        
        return $lastResults;
    }
    
    /**
     * This will get how many new games are in  productID
     * 
     * @param  int    $productID 
     * @return int           
     */
    public function count_new_games($productID, $gameIDs_disabled)
    {

        return $this->model('Mgame')->count_new($productID, $gameIDs_disabled);
    }

    /**
     * This will get all productIDs that's missing in present_productIDs list
     * 
     * @param  array  $present_productIDs 
     * @return array
     */
    public function get_missing_productIDs($present_productIDs)
    {

        return $this->model('Mproduct')->get_missing_productIDs($present_productIDs)->toArray();

    }

    /**
     * This will get wallet information base on walletID
     * @param  int   $walletID 
     * @return array
     */
    public function get_walletID_information($walletID)
    {

        $wallet = $this->model('Mwallet')->get_by_walletID($walletID);

        return ($wallet) ? $wallet->toArray() : $wallet;
    }
    
    /**
     * This will get companyID using serverID
     * @param  string $serverID [description]
     * @return int
     */
    public function get_companyID($serverID)
    {
        switch ($serverID) {

            case 'SBO':

                return 1;

                break;
        }
    }

    /**
     * This will check if clientID are registered to company
     * @param  int     $companyID 
     * @param  int     $clientID  
     * @return boolean            
     */
    public function is_company_registered($companyID, $clientID)
    {
        $record_count = $this->model('Mcompanyclient')->count_record($companyID, $clientID);

        return ($record_count > 0);
    }

    /**
     * This will get  game details
     * @param  int $gameID 
     * @return array
     */
    public function get_game_data($gameID)
    {
        $game_data = $this->model('Mgame')->get_game_data($gameID);
        
        return ($game_data) ? $game_data->makeVisible(array('productID','serverID'))->toArray() : $game_data;
    }

    /**
     * This will get game details and its url according to platform
     * @param  int    $gameID 
     * @param  string $platform 
     * @return array
     */
    public function game_data_url($gameID, $platform)
    {
        $game_data = $this->model('Mgame')->game_data_url($gameID, $platform);
        
        return ($game_data) ? $game_data->makeVisible(array('productID','serverID'))->toArray() : $game_data;
    }

    /**
     * This will get info of tableID for continuing the game through usedbalance
     * @param  int $clientID 
     * @param  int $gameID   
     * @param  int $tableID 
     * @return array
     */
    public function continue_game_info($clientID, $gameID, $tableID)
    {

        $continue_game_info = $this->model('Mgame')->continue_game_info($clientID, $gameID, $tableID);
        
        return ($continue_game_info) ? $continue_game_info->toArray() : $continue_game_info;

    }

    /**
     *  This will get all product details
     * @param  int $productID 
     * @return array
     */
    public function get_product_data($productID)
    {
        $get_product_data = $this->model('Mproduct')->get_product_data($productID)->makeVisible('productID');

        return ($get_product_data) ? $get_product_data->toArray() : $get_product_data;
    }

    /**
     * This will get game name only
     * @param  int $gameID 
     * @return array
     */
    public function get_gameName($gameID)
    {
        $game_details = $this->model('Mgame')->get_gameName($gameID)->makeVisible('productID')->toArray();
        return empty($game_details) ? array('gameName' => '' , 'productName' => '') : $game_details;


    }

    /**
     * Get productID and isCommRake of all products
     * @return array of object 
     */
    public function get_products()
    {

        $products = $this->model('Mproduct')->get_products();
        
        foreach ($products as $product_key => $product_value) {

            $products[$product_key] = (object) array(
                                        'productID'  => $product_value->productID,
                                        'isCommRake' => $product_value->isCommRake
                                    );

        }
       
       return $products;

    }

    /**
     * This will get the productID of a given productName
     * @param  string $productName 
     * @return int
     */
    public function get_productID($productName)
    {
        return $this->model('Mproduct')->get_productID($productName);
    }

    /**
     * This will get the productName of a given productID
     * Specific
     * @param  string $productName 
     * @return int
     */
    public function get_productName_key($productID, callable $productName_formatter)
    {
        $product = $this->model('Mproduct')->get_product_data($productID);

        if ($product) {
            
            if (is_callable($productName_formatter)) {

                return $productName_formatter($product->productName);
            }
            
        } else {

            return $productID;

        }
    }

    /**
     * This will get productIDs data in DB and transform it into array of arrays that has menu/sidebar like structure
     * @param  array $productIDs        
     * @param  array $existing_menu If same id will be found in new details the two will be merged
     *                              Items that has no same id with new details will be prepended to the root
     * @return void
     */
    public function product_as_menu($productIDs, $existing_menu, $service_function)
    { 
        $list     = array();
        $products = $this->model('Mproduct')->get_products_data($productIDs)->keyBy('productID')->toArray();

        
        if (count($products) > 0) {   

            foreach ($productIDs as $productID) {
                
                if (isset($products[$productID])) {
                    
                    $snake_productName  = $this->productName_as_key($products[$productID]['productName']);
                    
                    if (is_callable($service_function)) {

                        $new_details = $service_function($products[$productID]['productName'], $productID);
                    }

                    $new_details['text'] = '{{@lang.language.'.$snake_productName.'}}';

                    $existing_details   = array_where_first(
                                            $existing_menu, 
                                            function($value) use ($new_details) {
                                                return $value['id'] === $new_details['id'];
                                            }
                                        );

                    if ($existing_details['key']!==null) {
                        assoc_array_merge($new_details, $existing_details['value']);
                        unset($existing_menu[$existing_details['key']]);
                    }

                    $list[] = $new_details;

                }

            }

        }
        
        $existing_menu = array_values($existing_menu);
        return seq_array_merge($existing_menu, $list);
    }

    /**
     * This will format productName before using it as an ID or key
     * current format: snake_case
     * @param  string $productName [description]
     * @return string
     */
    private function productName_as_key($productName)
    {
        return to_snake_case(alphanum_only($productName));
    }

    /**
     * serverID is testmode if all games and product under it is set to testmode
     * @param  string $serverID 
     * @return boolean
     */
    public function serverID_testmode($serverID)
    {
        $normal_mode_games =  $this->model('Mgame')->count_enabled_serverID($serverID);

        return ($normal_mode_games <= 0);

    }

    /**
     * get games
     * @param  array $gameIDs 
     * @return array
     */
    public function get_games($gameIDs)
    {
        $games = $this->model('Mgame')->games_data($gameIDs);

        return $games ? $games->makeVisible(array('serverID'))->toArray() : array();
    }

    /**
     * This will delete websession of specific gameID recorded for client
     * @param  int $gameID
     * @param  int $clientID
     * @return boolean/null
     */
    public function delete_websession($gameID, $clientID)
    {
        $game_websessionIDs = $this->model('Mwebsession')->get_gameID_websessionIDs($gameID, $clientID);
        
        if (count($game_websessionIDs) > 0) {

            return $this->model('Mwebsession')->delete_websessionIDs($game_websessionIDs);

        } else {

            return null;

        }

    }

    /**
     *  This will remove all inactive websessions from list of clientIDs
     * @param  array $clientIDs        
     * @param  array $productIDs_config 
     * @return void
     */
    public function delete_inactive_websessions($clientIDs, $productIDs_config)
    {
        $delete_websessionIDs = array();

        $force_delete    = $this->model('Mwebsession')
                                ->get_force_delete($clientIDs,$productIDs_config['force_delete'])->toArray();

        $check_coinin    = $this->model('Mwebsession')
                                ->get_without_coinin($clientIDs, $productIDs_config['check_coinin'])->toArray();

        $without_running = $this->model('Mwebsession')
                                ->get_without_running($clientIDs, array_flatten($productIDs_config))->toArray();

        seq_array_merge($delete_websessionIDs,$force_delete,$check_coinin,$without_running);

        if (count($delete_websessionIDs) > 0) {

            return $this->model('Mwebsession')->delete_websessionIDs($delete_websessionIDs);

        } else {

            return null;

        }
    }

    /**
     * This will get serverIDs of gameID list
     * @param  array $gameIDs 
     * @return array
     */
    public function get_unique_serverIDs($gameIDs)
    {
        return $this->model('Mgame')->get_unique_serverIDs($gameIDs)->toArray();
    }

    /**
     * This will create or update websession of a game for specific client
     * LOGIC:
     * 
     *     First Layer Category (per game, per product)
     *         For products that can open different multiple games at same time they will have different
     *         websession per gameID else they will share per productID.
     *         
     *     Second Layer Category (gameType), applicable only for actual websession(not the lobby)
     *         1. There are products that games can create websession per gameType(usually using tableName)
     *         2. If the product games cannot create per gameType then follow the first category rule only
     *         
     * @param  array   $game            game detials needed for creating websession
     * @param  int     $client          [clientID, sessionID]
     * @param  array   $create_config   settings for creating websession
     * @return array
     */
    public function update_create_websession($game, $client, $create_config)
    {
        // First Category
        if ($game['isMultipleInstance']) {

            $search_method  = 'by_game';
            $search_subject = $game['gameID'];

                     
        } else {

            $search_method  = 'by_product';
            $search_subject = $game['productID']; 

        }

        // PS is the starting point of all websession so we will always update websession info
        $websession_data = array(
                            'gameID'     => $game['gameID'],
                            'clientID'   => $client['clientID'],
                            'sessionID'  => $client['sessionID'],
                            'token'      => generate_token(32),
                            'userAgent'  => get_user_agent(),
                            'userDevice' => get_user_device(),
                            'ip'         => get_ip()
                        );

        // lobby websession
        $lobby_search_method = 'lobby_'.$search_method;
        $lobby_websession    = $this->model('Mwebsession')->$lobby_search_method($search_subject, $client['clientID']);

        if ($lobby_websession) {
            
            $this->model('Mwebsession')->update_websession($lobby_websession['webSessionID'], $websession_data);

        } else {

            $this->model('Mwebsession')->create_lobby($websession_data);

        }

        // actual websession
        if (in_array($game['productID'], $create_config['ps_managed']) || $create_config['force_create']) {
            
            // additional data for actual websession
            assoc_array_merge($websession_data, array(
                'gameType'     => isset($game['tableName']) ? $game['tableName'] :  $game['gameName'],
                'startSession' => date('Y-m-d H:i:s')
            ));

            // Second Category
            if (in_array($game['productID'], $create_config['per_gameType'])) {

                $search_gameType = $websession_data['gameType'];

            } else {

                $search_gameType = false;

            }

            // create actual game websession
            $actual_search_method = 'actual_'.$search_method;
            $actual_websession    = $this->model('Mwebsession')->$actual_search_method(
                                        $search_subject, 
                                        $client['clientID'], 
                                        $search_gameType
                                    );

            if ($actual_websession) {

                // update
                $this->model('Mwebsession')->update_websession($actual_websession['webSessionID'], $websession_data);
                
            } else {

                // insert
                $this->model('Mwebsession')->insert_websession($websession_data);

            }

        }

        return $websession_data;
    }

    /**
     * This will get all fund transferable wallets
     * @return array
     */
    public function get_product_wallets($walletIDs, $productIDs, callable $encrypt)
    {
        $wallets = $this->model('Mwallet')->get_product_wallets($walletIDs, $productIDs)->toArray();

        $encrypted_walletIDs = array();

        foreach ($wallets as &$wallet) {
                if (is_callable($encrypt)) {
                    $encrypted_walletIDs[$encrypt($wallet['walletID'])] = $wallet['description'];
                }

        }

        return $encrypted_walletIDs;
    }

    /**
     * This will get all games which is not in the given gameID and productID
     * @param  array $disabled_IDs       array of disabled_gameIDs, disabled_productIDs, disabled_serverIDs
     * @param  array $existing_menu      If gameName and existing id is equal the existing detials will be merged
     *                                   Items that has no same gameNames will be prepended to the root
     * @return array
     */
    public function game_guide_menu($disbaled_IDs, $existing_menu, $gameName_formatter)
    {
        $game_guide_hierarchy = $this->model('Mproduct')->get_hierarchy(
                                                            $disbaled_IDs)
                                                        ->toArray();

        foreach ($game_guide_hierarchy as &$game) {
            // text and gameName_original has different purpose on frontend, it may become different in the future
            $game['gameName_original']    = $game['gameName'];
            $game['text']                 = $game['gameName'];

            if (is_callable($gameName_formatter)) {
                $gameName_formatter($game);
            }

            $existing_details   = array_where_first(
                                    $existing_menu, 
                                    function($value) use ($game) {
                                        return $value['id'] === $game['gameName'];
                                    }
                                );

            if ($existing_details['key']!==null) {
                assoc_array_merge($game, $existing_details['value']);
                unset($existing_menu[$existing_details['key']]);
            }

            $game['type']         = to_snake_case(alphanum_only($game['type']));

        }
        $existing_menu = array_values($existing_menu);
        return seq_array_merge($existing_menu, array_group_by(
            $game_guide_hierarchy, 
            array('productName','type','gameName'),
            array('collapse_single' => true, 'move_up_single' => true)
        ));
    }

    /**
     * Get gameID's that required displayName
     * @return array 
     */
    public function gameIDs_required_displayName()
    {
        return $this->model('Mgame')->gameIDs_required_displayName()->toArray();
    }

    /**
     * get tournament details
     * @return array 
     */
    public function guest_tournaments()
    {
        $phases = $this->model('Mtournament')->tournaments_details();

        foreach ($phases as $phase) {

           $this->format_tournament($phase);
           
        }
        return $phases ? $phases->toArray() : array();
    }

    /**
     * get tournament details of player
     * @param  int $clientID 
     * @param  string $timezone 
     * @return array           
     */
    public function player_tournament_rank($clientID,$timezone)
    {
        $phases   = $this->model('Mtournament')->tournaments_details();

        foreach ($phases as $phase) {

            $this->format_tournament($phase);
            
            if ($phase->isActive != 1) {
                
                $phase->claimButton = false;
                $current_date       = date('Y-m-d H:i:s');
                $tournamentdetail   = $this->model('Mtournamentdetail')->tournament_rank($clientID, $phase->tournamentID);

                if ($tournamentdetail && $phase->claimDeadline > $current_date && Auth::user()->isTestPlayer == 0) {

                    date_default_timezone_set($timezone);
                    $phase->claimButton  = true;
                    $current_date        = new DateTime(date('Y-m-d H:i:s'));
                    $claimDeadline       = new DateTime(date('Y-m-d H:i:s',strtotime($phase->claimDeadline)));
                    $time_interval       = $claimDeadline->diff($current_date);
                    $phase->claimDays    = $time_interval->format('%d');
                    $phase->claimHours   = $time_interval->format('%H');
                    $phase->claimMinutes = $time_interval->format('%i');
                    $phase->claimSeconds = $time_interval->format('%s');

                }
            }
            
        }
        
        return $phases ? $phases->toArray() : array();

    }

    /**
     * format and translate language of date
     * @param  object &$phase 
     * @return object      
     */
    private function format_tournament(&$phase)
    {

        $date = new DateTime($phase->startDateTime);
        $phase->displayDate = $phase->startDateTime == '0000-00-00 00:00:00' ? 
                                'Not Set' : 
                                $date->format('d').' '.Lang::get('language.'.$date->format('F')).' '.$date->format('Y');
       
    }

    /**
     * get ranks of phase
     * @param  int $tournamentID 
     * @return object               
     */
    public function phase_ranks($tournamentID)
    {

        return $this->model('Mtournamentdetail')->phase_ranking($tournamentID);

    }

    /**
     * get all active tournaments
     * @return array
     */
    public function get_active_tournament()
    {

        $active_tournament = $this->model('Mtournament')->get_active();

        return $active_tournament ? $active_tournament->toArray() : $active_tournament;

    }

    /**
     * get details of top rank of tournament
     * @return array 
     */
    public function get_top_rank()
    {

        $top_rank = $this->model('Mtournamenttoprank')->top_rank();

        if (count($top_rank)) {

            $rank_prize = $this->model('Mtournamentprize')->prize_amount($top_rank[0]->tournamentID)->toArray();

            foreach ($top_rank as $key => $rank) {

                $rank->rank = ++$key;

                if($key < 4) {

                $rank->amount = isset($rank_prize[$key]) ? $rank_prize[$key]['amount'] : '0.000000';
            
                } else {
                    $rank->amount = isset($rank_prize[4]) ? $rank_prize[4]['amount'] : '0.000000';
                }

            }

        }

        return $top_rank->toArray();
        

    }

    /**
     * Claim client prize for tournament
     * @param  int $clientID      
     * @param  int $tournamentID  
     * @param  array $update_fields fields to update
     * @return bool                
     */
    public function claim_prize($clientID, $tournamentID, $update_fields)
    {
        
        $row = $this->model('Mtournamentdetail')->claim_prize($clientID, $tournamentID, $update_fields);        
        return $row == 0 ? false : true;

    }

    /**
     * get details of client prize
     * @param  int $clientID     
     * @param  int $tournamentID 
     * @return array               
     */
    public function get_prize_details($clientID, $tournamentID)
    {

        $prize_details = $this->model('Mtournamentdetail')->prize_details($clientID, $tournamentID);

        return $prize_details ? $prize_details->toArray() : array('rank' => 0, 'amount' => 0);
        
    }

    /**
     * This will get game max payout for specific currency
     * @param  string $gameID     
     * @param  int    $currencyID 
     * @return string
     */
    public function game_window_data($gameID, $currencyID)
    {
        $game_data = $this->model('Mgame')->game_window_data($gameID, $currencyID);

        if($game_data) {

            $game_data = $game_data->makeVisible(array('productID','serverID'))->toArray();

            if (is_numeric($game_data['maxpayout']) && (float)$game_data['maxpayout'] > 0) {
                $game_data['maxpayout'] = custom_money_format($game_data['maxpayout']);
            } else {
                $game_data['maxpayout'] = false;
            }

            return $game_data;

        } else {

            return $game_data;

        }
    }

    /** 
     * This will count token record of specific game and player on websession
     * @param  string $token    
     * @param  string $gameID   
     * @param  string $clientID 
     * @return int
     */
    public function websession_token_count($token, $gameID, $clientID) 
    {
        return $this->model('Mwebsession')->websession_token_count($token, $gameID, $clientID);
    }


    /** 
     * This will get not lobby websession by token, gameID and clientID
     * @param  string $token    
     * @param  string $gameID   
     * @param  string $clientID 
     * @return int
     */
    public function websession_by_token($token, $gameID, $clientID) 
    {
        $websession = $this->model('Mwebsession')->get_by_token($token, $gameID, $clientID);
        return ($websession) ? $websession->toArray() : $websession;
    }

    /**
     * This will get game serverID
     * @param  string $gameID 
     * @return string
     */
    public function get_serverID($gameID) 
    {
        return $this->model('Mgame')->get_serverID($gameID);
    }

}
