<?php

namespace Backend\controllers;

use Config;
use Input;
use Auth;
use Lang;
use Layer;
use URL;

/**
 * Playing and getting games data
 * 	
 * @author PS Team
 */
class Cproducts extends Basecontroller {
    
    /**
     * main entry point for products module
     * 
     * @return mix
     */
    public function get_products()
    {

        $type = strtolower(Input::get('g'));
        
        $this->service('Svalidate')->validate(array(
            'required_param' => array( 
                                    'value' => array(
                                                'type'      => 'hidden',
                                                'input'     => $type
                                            )
                                )
        ), TRUE);
        
        switch ($type) {
            case 'ps_games': return $this->retrieve_games();
            case 'ps_lobby': return $this->get_lobby();
        }
        
    }
    
    /**
     * main entry point for proccessing product
     * 
     * @return mix
     */
    public function process_products()
    {
        $type = strtolower(Input::get('ps_form-process'));
        
        $this->service('Svalidate')->validate(array(
            'required_param' => array( 
                                'value' => array(
                                            'type'      => 'hidden',
                                            'input'     => $type
                                        )
                            )
        ), TRUE);
        
        switch ($type) {
            case 'reset_websession': return $this->reset_websession();
        }

    }
    
    /**
     * Decrypts and validates the gameID
     * 
     * @param  type $encrypted_gameID
     * @return type
     */
    private function gameID_decrypt($encrypted_gameID)
    {       

        $decrypted_gameID = $this->service('Scrypt')->crypt_decrypt($encrypted_gameID);

        $this->service('Svalidate')->validate(array(

            'gameID'            => array(
                                    'value'     => array(
                                                    'input' => $encrypted_gameID,
                                                    'type'  => 'hidden'
                                                ),
                                    'validator' => 'required_param' 
                                )

        ), TRUE);

        return $decrypted_gameID;   
    }

    /**
     * This will reset gameIDs websession
     * This supports only per gameID websession deletion for now
     * Next enhancement: per gameType if necessary
     * @return array
     */
    private function reset_websession()
    {   
        $gameID = $this->gameID_decrypt(Input::get('_GID'));

        $this->service('Svalidate')->validate(array(

            'websession_access' => array('value' => array('gameID' => $gameID))

        ), TRUE);
        
        $gameID_session_left = $this->service('Ssession')->gameID_open($gameID, false);

        if (!$gameID_session_left) {
            
            $this->repository('Rproducts')->delete_websession($gameID, Auth::user()->clientID);

        }

        return array('result' => true, 'count' => $gameID_session_left);
    }

    /**
     * This will get the game guide data from rso
     * @return html
     */
    public function get_gameguide()
    {
        $page           = Input::get('gpage');
        $gameName       = alphanum_only(Input::get('gname'));
        $productID      = $this->service('Scrypt')->crypt_decrypt(Input::get('pID'));
        $language       = $this->service('Ssiteconfig')->get_lang_id();

        $path_extension = $language.'/'.to_snake_case("{$productID} {$gameName}").str_pad($page,2,0,STR_PAD_LEFT).'.html';
        $full_path      = $this->service('Ssiteconfig')->rso('gameguide','backend',$path_extension)['original'];
        
        return $this->service('Sserver')->file_get_contents($full_path);
    }

    /**
     * Retrieve all game details
     * 
     * @return type
     */
    private function retrieve_games( ) 
    {
        $filter     = Input::get('filter');
        $category   = Input::get("category");
        $game_name  = Input::get('q');
        $productID  = $this->service('Scrypt')->crypt_decrypt($category);
        $user       = Auth::user();

        $this->service('Svalidate')->validate(array(
            
            'filter'             => array( 
                                        'value'     => array(
                                                        'input'     => $filter,
                                                        'type'      => 'hidden'
                                                    ), 
                                        'validator' => 'required_param'
                                    ),
            
            'category'           => array( 
                                        'value'     => array(
                                                        'input'     => $category,
                                                        'type'      => 'hidden'
                                                    ), 
                                        'validator' => 'required_param' 
                                    ),
            
            'site_access'        => array(
                                        'value'     => array()
                                    ),
            
            'transaction_access' => array(
                                        'value'     => array(
                                                        'auth_user' => $user
                                                    )
                                    ),
            
            'product_access'     => array(
                                        'value'     => array(
                                                        'productID' => $productID,
                                                        'auth_user' => $user
                                                    )
                                    )

        ), TRUE );
        
        $is_mobile_platform = $this->service('Ssiteconfig')->is_mobile_platform();

        $product = array('is_mobile_platform' => $is_mobile_platform,  'productID' => $productID);
        
        $options = array(
            'isTestPlayer'       => $user->isTestPlayer,
            'game_name'          => $game_name,
            'filter'             => $filter,
            'serverIDs_disabled' => $this->service('Ssiteconfig')->serverIDs_disabled()
         );

        assoc_array_merge($options, Config::get('settings.products.retrieve_list'));

        return $this->repository('Rproducts')->retrieve_games(
            $options,
            $product,
            function(&$game){
                $game['game_key'] = $this->service('Ssiteconfig')
                                        ->gameName_unique_key($game['gameID'], $game['gameName']);
                $game['gameID']   = $this->service('Scrypt')->crypt_encrypt($game['gameID']);

            }
        );
    }

    /**
     * This will get specific game info with standard validations
     * @param  stirng   $encrypted_gameID gameID encrypted via laravel Cypt::encrypt() method
     * @param  platform $platform         default desktop
     * @return array                      Game Informations
     */
    private function validated_game_data($encrypted_gameID, $platform = 'desktop')
    {
        $this->service('Svalidate')->validate(array(

            'site_access'        => array(
                                        'value' => array()
                                    ),
            
            'transaction_access' => array(
                                        'value' => array(
                                                    'auth_user' => Auth::user()
                                                )
                                    )
        ), TRUE);

        // decrypt gameID
        $gameID = $this->gameID_decrypt($encrypted_gameID);

        // get game details
        $game   = $this->repository('Rproducts')->game_data_url($gameID, $platform);

        // check if game ID and detail exsist
        $this->service('Svalidate')->validate(array(
            
            'game'           => array(
                                    'value'     => array('input' => $game), 
                                    'validator' => 'truthy'
                                ),

            'product_access' => array(
                                    'value'     => array(
                                                    'auth_user'  => Auth::user(),
                                                    'productID'  => $game['productID'],

                                                    // we also got the product detail in $game variable
                                                    'product'    => $game
                                                )
                                ),
            
            'game_access'    => array(
                                    'value'     => array(
                                                    'auth_user'        => Auth::user(),
                                                    'encrypted_gameID' => $encrypted_gameID,
                                                    'gameID'           => $gameID,
                                                    'game'             => $game
                                                )
                                )
            
        ), TRUE); 

        return $game;
    }

    /**
     * Used for getting informations for PS created game lobby
     * For now this supports Togel only
     * @return array
     */
    private function get_lobby()
    {
        $game = $this->validated_game_data(Input::get('_GID'));

        // this process is for togel only
        $data = $this->service('Ssiteconfig')->get_url();
        
        return $this->service('Sapi')->request('togel_lobby_information', array(
            'url' => $data
        ));
    }

    /**
     * This will create and get necessary data for playing games
     * @return array
     */
    public function play()
    {   
        $is_mobile = Input::has('is_mobile') ? Input::get('is_mobile') : 0;
        $platform  = $is_mobile ? 'mobile' : 'desktop';
        $game      = $this->validated_game_data(Input::get('_GID'), $platform);
        
        // create game token
        $token = $this->request_game_token($game);

        // build URL
        $url_params  =  false;
        $is_rso      =  Input::get('is_rso');
        $language_id =  $this->service('Ssiteconfig')->game_lang_format(array_only($game,array(
                            'gameID',
                            'serverID',
                            'productID'
                        )));

        // determine url params via serverID (for now this is our first filter attempting to get url params)
        switch ($game['serverID']) {

            case 'GPI': 

                $url_params = array(
                                    'token'    => $token,
                                    'lang'     => $language_id,
                                    'op'       => Config::get('settings.games.GPI.operator'),
                                    'fun'      => 0,
                                    'lobbyURL' => URL::to('close_window')
                                );

                break;
                
        }

        // determine url params via products (last attempt to get url params)
        if ($url_params == false) {

            switch ($game['productID']) {

                case 3: 

                    $url_params = array(
                                    'token' => $this->service('Scrypt')->aes_encrypt(http_build_query(array(
                                                'token'     => $token,
                                                'timestamp' => date('Y-m-d H:i:s'),
                                                'lang'      => $language_id,
                                                'rso'       => $this->service('Ssiteconfig')->rso_folder()
                                            )))
                                );

                    break;

                case 2:
                case 4:
                case 7:

                    $url_params = array(
                                    'token'  => $token,
                                    'gameID' => $game['gameID'],
                                    'lang'   => $language_id
                                );

                    break;

                case 6: 

                    $url_params = array(
                                    'token'  => $token,
                                    'lang'   => $language_id,
                                    'device' => substr($platform,0,1)
                                );

                    break;

                case 5: 

                    $url_params = array(
                                    'token'  => $token,
                                    'lang'   => $language_id
                                );

                    if (Config::has('settings.sports.theme')) {
                        $url_params['theme'] = Config::get('settings.sports.theme');
                    }

                    if ($this->service('Ssiteconfig')->is_mobile_platform()) {
                        $url_params['device'] = 2;
                    }

                    break;

                default:

                    $url_params = array(
                                    'token'  => $token,
                                    'gameID' => $game['gameID'],
                                    'lang'   => $language_id,
                                    'rso'    => $this->service('Ssiteconfig')->rso_folder()
                                );

                    break;
            }

        }
        
        
        $game_url = url_add_query($this->service('Ssiteconfig')->get_dynamic_domain($game['url']),$url_params);

        // build response
        return array(
            'result' => true, 
            'URL'    => $this->service('Ssession')->game_window_session(
                            array(
                                'game'     => $game, 
                                'token'    => $token, 
                                'game_url' => $game_url
                            ),
                            $platform
                        ),
            'token'  => $token, 
            'lang'   => $language_id
        );
    }

    /**
     * This will create and get necessary data for continuing unfinished games
     * @return array
     */
    public function continue_game()
    {
        $game      = $this->validated_game_data(Input::get('_GID'));
        $tableID   = $this->service('Scrypt')->crypt_decrypt(Input::get('_TID'));

        $join_info = $this->repository('Rproducts')->continue_game_info(
                        Auth::user()->clientID,
                        $game['gameID'], 
                        $tableID
                    );

        $this->service('Svalidate')->validate(array(

            'join_access' => array(
                                'value' => array(
                                            'user'      => Auth::user(),
                                            'join_info' => $join_info
                                        )
                            )

        ), TRUE);

        // add tableName
        $game['tableName'] = $join_info['tableName'];

        // create game token
        $create_config = $this->service('Ssiteconfig')->websession_products()['create'];
        // we need to force create websession so we can continue the game
        $create_config['force_create'] = true; 
        $token = $this->request_game_token($game, $create_config);
        
        // build URL
        $is_rso      = Input::get('is_rso');
        $language_id = $this->service('Ssiteconfig')->game_lang_format(array_only($game,array(
                            'gameID',
                            'serverID',
                            'productID'
                        )));

        switch ($game['productID']) {

            case 3:  // Tangkas

                $url_params = array(
                                'payload' => $this->service('Scrypt')->aes_encrypt(http_build_query(array(
                                            'token'     => $token,
                                            't_id'      => $tableID,
                                            'stamp'     => microtime(true),
                                            'clientID'  => Auth::user()->clientID,
                                            'sessionID' => Auth::user()->sessionID,
                                            'rso'       => $this->service('Ssiteconfig')->rso_folder()
                                        )))
                            );

                // additional to game path
                $game['url'].='/continuegame';

                break;
        }

        // response
        return array(
            'result' => true, 
            'URL'    => url_add_query($this->service('Ssiteconfig')->get_dynamic_domain($game['url']), $url_params)
        );
    }

    /**
     * This will serve as centralized process when creating token, if it will come from API or websession
     * @param  array  $game         
     * @param  array  $create_config default = Ssiteconfig > websession_products
     *                               This will be use as config in creating token and websession
     *                                   
     * @return array
     */
    private function request_game_token($game, $create_config = false)
    {
        $user = Auth::user();

        if ($create_config === false) {

            $create_config = $this->service('Ssiteconfig')->websession_products()['create'];

        }

        // games that needs token from API
        if (in_array($game['gameID'],Config::get("settings.TOKEN_ON_API"))) {  

            $api_token = $this->service('Sapi')->login_third_party($user, $game);

        }

        // websession
        $websession = $this->repository('Rproducts')->update_create_websession($game, $user, $create_config);
        
        // update game open window count
        if (in_array($game['productID'], $create_config['ps_managed'])) {

            $this->service('Ssession')->gameID_open($game['gameID']);
            
        }

        // set the actual token to be used for logging in to the game
        if (isset($api_token)) {

            return $api_token;

        } else {

            return $websession['token'];

        }
    }

    /**
     * get tournament details
     * @return array 
     */
    public function tournament_details()
    {   

        if (Auth::check()) {

            $phases = $this->repository('Rproducts')->player_tournament_rank(Auth::user()->clientID,Config::get('app.client_timezone'));

        } else {

            $phases = $this->repository('Rproducts')->guest_tournaments();
        
        }

        return array('phases' => $phases);
 
    }

    /**
     * get ranks of phase 
     * @return array 
     */
    public function get_phase_ranks()
    {

        $phase_ranks['phase'] = Input::get('phase');
        $phase_ranks['ranks'] = $this->repository('Rproducts')->phase_ranks($phase_ranks['phase']);

        return $phase_ranks; 
    }

    /**
     * get top rank of tournament
     * @param  int $stop_broadcast 
     * @return array                
     */
    public function get_top_rank($stop_broadcast)
    {

        $active_tournament = $this->repository('Rproducts')->get_active_tournament();

        if ($active_tournament) {

            $top_rank['phase'] = $active_tournament['phaseNo'];
            $top_rank['ranks'] = $this->repository('Rproducts')->get_top_rank();

            if ($stop_broadcast != 1) {

                $this->service('Ssocket')->broadcast('toprank', json_encode($top_rank));
                $this->service('Ssocket')->broadcast(
                    'toprank',
                    json_encode($top_rank), 
                    strtolower(Config::get('settings.WL_CODE')).'_guest'
                );
            }

        }

        return $top_rank;
    }

    /**
     * claim client tournamentprize
     * @return array 
     */
    public function claim_tournamentprize()
    {

        $this->service('Svalidate')->validate(array(
            'isTestPlayer' => array(
                                'value'     => array(
                                                    'input' => Auth::user()->isTestPlayer,
                                                    'type'  => 'claim_prize'
                                                ),
                                'validator' => 'falsy'
                            )
        ), true);

        $claim_inputs = array(
            'bankName'           =>  Input::get('ps_claim_bank'),
            'bankAccountName'    =>  Input::get('ps_claim_bank_name'),
            'bankAccountNumber'  =>  Input::get('ps_claim_bank_number'),
            'phoneNumber'        =>  Input::get('ps_claim_phone_number')    
        );

        $this->service('Svalidate')->validate(array(
 
            'ps_claim_bank'         => array(
                                           'value'     => array('input' => $claim_inputs['bankName']),
                                           'validator' => 'required_param'
                                    ),
            'ps_claim_bank_name'    => array(
                                            'value'     => $claim_inputs['bankAccountName'],
                                            'validator' => 'no_symbol'
                                    ),
            'ps_claim_bank_number'  => array(
                                            'value'     => array('input' => $claim_inputs['bankAccountNumber']),
                                            'validator' => 'required_param'
                                    ),
            'ps_claim_phone_number' => array(
                                            'value'     => $claim_inputs['phoneNumber'],
                                            'validator' => 'mobile_number'
                                    )
        ));
        
        $process      = Input::get('ps_form-process');
        $tournamentID = Input::get('ps_ctid');
        $clientID     = Auth::user()->clientID;

        $claim_inputs['isClaimed']   = 1;
        $claim_inputs['dateClaimed'] = date('Y-m-d H:i:s');

        $isClaimed = $this->repository('Rproducts')->claim_prize($clientID, $tournamentID, $claim_inputs);

        $this->service('Svalidate')->validate(array(
            'isClaimed' => array(
                                'value'     => array('input' => $isClaimed),
                                'validator' => 'truthy'
                            )
        ), true);

        $prize_details            = $this->repository('Rproducts')->get_prize_details($clientID, $tournamentID);

        $claim_inputs['to']       = Config::get('settings.TOURNAMENT_SENDER');
        $claim_inputs['view']     = 'emails.ps_tournament.claim_notification';
        
        $claim_inputs['username'] = Auth::user()->username;
        $claim_inputs['rank']     = $prize_details['rank'];
        $claim_inputs['prize']    = custom_money_format($prize_details['amount']);
        $claim_inputs['subject']  = "Tangkas Tournament Phase {$tournamentID} winner";

        Config::set('mail.from.address', 'info@338a.com');
        Config::set('mail.username', 'info@338a.com');

        $this->service('Semails')->send($claim_inputs);

        return $claim_inputs;

    }

    /**
     * This will do backend actions when game window was closed:
     * Delete websession via API
     * @return array
     */
    public function game_window_closed()
    {
        // this is used in game window closing
        // make sure the request will continue even after client abort
        ignore_user_abort(true);

        $game_token         = Input::get('game_token');
        $gameID             = Input::get('_GID');
        $encrypted_clientID = Input::get('_CID');
        $clientID           = $this->service('Scrypt')->aes_decrypt($encrypted_clientID,0,true);



        $this->service('Svalidate')->validate(array(
            'game_token' => array(
                               'value'     => array('input' => $game_token,'type'=>'game_window'),
                               'validator' => 'required_param'
                        ),
            'gameID'    => array(
                               'value'     => array('input' => $gameID,'type'=>'game_window'),
                               'validator' => 'required_param'
                        ),
            'clientID'  => array(
                               'value'     => array('input' => $clientID,'type'=>'game_window'),
                               'validator' => 'required_param'
                        ),
        ), TRUE);


        $websession = $this->repository('Rproducts')->websession_by_token(
                        $game_token,
                        $gameID,
                        $clientID
                    );

        $this->service('Svalidate')->validate(array(
            'websession' => array(
                               'value'     => array('input' => $websession,'type'=> 'game_window'),
                               'validator' => 'truthy'
                            )
        ), TRUE);

        $this->service('Sapi')->status_player_false(
            $websession,
            $gameID,
            $encrypted_clientID
        );

        return array('result' => true);
    }

}
