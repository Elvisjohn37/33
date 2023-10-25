<?php
Route::group(['middleware' => ['web']], function () {
    
    // Main landing page
    Route::get('/', array('uses' => 'Cmaster@view'))->name('/');

    // Error page
    Route::get('error/{code?}', array('uses' => 'Cmaster@view'))->name('error_window');
    
    // Game window closed
    Route::post('game_window_closed', array('uses' => 'Cproducts@game_window_closed'));

    // Main landing page
    Route::get('desktop', array('uses' => 'Cmaster@view'))->name('desktop');
    
    // Validate specific field
    //Route::post('validate', array('uses' => 'AppCore@validate')); // DEPRECATED 
    Route::post('validate', array('uses' => 'Cuser@validate'));

    // Account activation for newly registered account
    Route::get('activate/{code}', array('uses' => 'Cuser@activate_account'));

    // Language setting
    Route::post('language', array('uses' => 'Ccomponents@set_language'));

    // checker if player is still login
    Route::post('verify_auth', array('uses' => 'Cuser@is_login'));

    # checker if parent is still login
    // Route::post('verify_parent', array('uses' => 'AppCore@isParent_online'));
    Route::post('verify_parent', array('uses' => 'Ccomponents@check_chatStatus'));

    # Account password reset
    // Route::get('reset/{code}', array('uses' => 'AppCore@reset_password'));
    Route::get('reset/{code}', array('uses' => 'Cuser@reset_password'));

    // Get plugin data
    Route::post('plugin', array('before' => '', 'uses' => 'Ccomponents@get_plugin'));
            
    // Getting initial data for landing pages
    Route::post('view_data', array('uses' => 'Cmaster@view_data'));

    /*
      |-----------------------------------------------------------------
      |	Routes with csrf token checking
      |-----------------------------------------------------------------
     */
    Route::group(['middleware' => ['csrf']], function () {
        
        # Guest data retrieval
        // Route::post('v_get', array('uses' => 'AppCore@vget_process')); // DEPRECATED
        
        Route::post('v_get', function(){
            
            $G = Input::get('g');
   
            switch($G){
                
                // GET USER
                case 'security_question': 
                     return Layer::controller('Cuser')->get_user();

                // GET COMPONENT
                case 'promotions':
                case 'captcha':  
                        return Layer::controller('Ccomponents')->get_components();
            }
            
        });

        # Form actions
        // Route::post('process', array('uses' => 'AppCore@process')); // DEPRECATED
        
        Route::post('process', function(){

            $process = Input::get('ps_form-process');

            switch ( strtoupper($process) ) {
 
                // PROCESS COMPONENTS
                case 'CAPTCHA': 
                        return Layer::controller('Ccomponents')->process_components();
                           
                // PROCESS USER
                case 'PS_CHANGE_DISPLAY_NAME': 
                case 'GENERATE_DISPLAY_NAME':
                case 'PLAYER_REGISTRATION':
                case 'CHANGE_LOGIN_PASSWORD': 
                case 'REGISTER_FRIEND': 
                case 'CHANGE_PASSWORD':
                case 'EXPIRED_PASSWORD':
                case 'NEW_PASSWORD':
                case 'LOST_PASSWORD':
                    return Layer::controller('Cuser')->process_user();

                case 'RESET_WEBSESSION':
                    return Layer::controller('Cproducts')->process_products();

                case 'FUND_TRANSFER': 
                case 'WITHDRAWAL_REQUEST':
                case 'DEPOSIT_CONFIRMATION':
                    return Layer::controller('Ctransactions')->process_transactions();
                    
            }
            
        });

        # Read message received by speicifc player or guest
        // Route::post('seen', array('uses' => 'AppChat@seen'));
        Route::post('seen', array('uses' => 'Ccomponents@seen_message'));

        # Chat send message
        // Route::post('send_msg', array('uses' => 'AppChat@send_msg'));
        Route::post('send_msg', array('uses' => 'Ccomponents@send_message'));

        # Chat history
        // Route::post('chat_history', array('uses' => 'AppChat@getMessages')); //DEPRECATED
        Route::post('chat_history', array('uses' => 'Ccomponents@get_messages'));

        # Get tangkas tournament details
        // Route::post('tournament_details', array('uses' => 'AppTournament@getDetails'));
        Route::post('tournament_details', array('uses' => 'Cproducts@tournament_details'));

        # Get Tangkas tournament top players on specific phase
        // Route::post('phase_top', array('uses' => 'AppTournament@getPhaseTop'));
        Route::post('phase_top', array('uses' => 'Cproducts@get_phase_ranks'));

        # get game guide
        Route::post('game_guide', array('uses' => 'Cproducts@get_gameguide'));

        # Get page content
        Route::post('page', array('uses' => 'Ccomponents@get_language_page'));

        # Account verification e-mail resend
        // Route::post('resend_email', array('uses' => 'AppMailer@resend_registration'));
        Route::post('resend_email', array('uses' => 'Cuser@resend_email_registration'));
        
    });

    /*
      |-----------------------------------------------------------------
      |	Routes that needs to be logged in
      |-----------------------------------------------------------------
     */

    Route::group(['middleware' => ['player']], function () {

        // Player logout processing
        Route::post('logout', array('uses' => 'Cuser@logout'));

        # Terms and conditions acceptance
        // Route::post('accept_terms', array('uses' => 'AppCore@accept_terms'));
        Route::post('accept_terms', array('uses' => 'Cuser@accept_terms_condition'));

        # In game window
        Route::get('statement', array('uses' => 'Cmaster@view'))->name('statement');
        Route::get('balance', array('uses' => 'Cmaster@view'))->name('balance');
        Route::get('help/{productID}', array('uses' => 'Cmaster@view'))->name('game_rules');
        Route::get('gaming_rules/{gameID}', array('uses' => 'Cmaster@view'))->name('game_guide');

        # Game window
        Route::get('game_window', array('uses' => 'Cmaster@view'))->name('game_window');

        # Avatar
        // Route::post('avatars', array('uses' => 'GetController@avatars'));
        Route::post('avatars', array('uses' => 'Cuser@get_avatars'));


        /*
          |-----------------------------------------------------------------
          |	Routes that needs to be logged in and csrf
          |-----------------------------------------------------------------
         */
        Route::group(['middleware' => ['csrf']], function () {
            
            // Route::post('up_av', array('uses' => 'AppProcess@uploadAvatar'));
            Route::post('up_av', array('uses' => 'Cuser@upload_avatar'));
            
            // Route::post('set_av', array('uses' => 'AppProcess@setPrimary'));
            Route::post('set_av', array('uses' => 'Cuser@set_primary_avatar'));

            # Player data retrieval
            // Route::post('get', array('uses' =
            // > 'AppCore@get_process')); // DEPRECATED
            
            Route::post('get',function(){
                
                $G = Input::get('g');

                switch($G){

                    // GET TRANSACTIONS (get_transactions)  	
                        
                    case 'running_bets' :       
                    case 'transaction_logs':    
                    case 'statementdetails':
                    case 'statement':
                    case 'api_balance':
                    case 'balance': 
                        return Layer::controller('Ctransactions')->get_transactions();
                        
                    // GET GAMES
                    // case 'payload' : // unused
                    case 'ps_games':
                    case 'ps_lobby':
                        return Layer::controller('Cproducts')->get_products();

                    // GET COMPONENTS
                    case 'promotions':     
                    case 'announcement':
                    case 'captcha':         
                        return Layer::controller('Ccomponents')->get_components();
                }
            });

            # Play games
            Route::post('play', array('uses' => 'Cproducts@play'));

            # Continue playing tangkas
            Route::post('continuegame', array('uses' => 'Cproducts@continue_game'));
                
            # Bet details
            // Route::post('bet_details', array('uses' => 'AppCore@bet_details'));
            Route::post('bet_details', array('uses' => 'Ctransactions@bet_details'));

            # Player Account Status
            // Route::post('check_status', array('uses' => 'AppCore@check_status'));
            Route::post('check_status', array('uses' => 'Cuser@check_status'));

            # Claim tangkas tournament price
            // Route::post('claim', array('uses' => 'AppTournament@process_claim'));
            Route::post('claim', array('uses' => 'Cproducts@claim_tournamentprize'));
            
        });
    });

    /*
      |-----------------------------------------------------------------
      |	Routes for guests only (not logged in)
      |-----------------------------------------------------------------
     */

    Route::group(['middleware' => ['guest']], function () {

        # Authentication process of player
        //Route::post('authenticate', array('uses' => 'AppCore@authenticate'));
        Route::post('authenticate', array('uses' => 'Cuser@login'));

    });

    /*
      |----------------------------------------------------
      | Web Socket Routes
      |----------------------------------------------------
      |
      | Routes that are being called and used by web socket.
      | Process also web socket related modules.
      |
     */

    # Validating WebSocket connection
    // Route::post('sc', array('uses' => 'AppRealtime@ws_validation'));
    Route::post('validate_ws', array('uses' => 'Ccomponents@ws_validation'));

    # Deleting some sessions
    // Route::post('clear', array('uses' => 'AppCore@clearStorage'));
    Route::post('clear', array('uses' => 'Ccomponents@clear_storage'));

    /*
      |----------------------------------------------------
      | Extra routes (for devs only)
      |----------------------------------------------------
      |
      | Routes used for testinging, kindly remove if done using or else :>
      |
     */

    Route::get('flush', function() {
        Session::flush();
    });

    Route::get('_env', function() {
        return App::environment();
    });


    Route::get('myIP', function() {

        $is_ip_allowed = Layer::repository('Rdbconfig')->is_ip_allowed(Input::get('ip'));
        echo 'is IP allowed: ';
        var_dump($is_ip_allowed);

        echo " <br />WITHOUT USING WAF: <br />";
        echo Response::json(Request::header());
        echo "<br /><b>IP: " . Request::ip() . "</b>";
        echo '<br /><br />';
        echo "USING WAF: <br />";
        Request::setTrustedProxies(array('10.38.1.100'));
        echo Response::json(Request::header());
        echo "<br /><b>Final IP: " . get_ip() . "</b>";

           });


    Route::get('openAva', function() {
        Socket::push(array(Auth::user()->sessionID), 'OPEN_AVATAR');
    });

    Route::get('sessionID', function() {
        // echo Session::get('SESSION_ID');
    });

    Route::get('lobby', function() {

        #Socket::broadcast('TOGEL_LOBBY', 'refresh');
    });
    
});

/*
  |----------------------------------------------------
  | STAND ALONE ROUTES
  |----------------------------------------------------
 */

# Broadcaster
// Route::post('broadcast', array('uses' => 'AppRealtime@ws_broadcast'));
Route::post('broadcast', array('uses' => 'Ccomponents@ws_broadcast'));

# Inactive players auto logout
// Route::get('activity', array('uses' => 'AppRealtime@ws_activity'));
Route::get('activity', array('uses' => 'Cuser@inactive_players'));

# Unverified accounts deletion
// Route::get('delete_unverified', array('uses' => 'AppRealtime@ws_unverified'));
Route::get('delete_unverified', array('uses' => 'Cuser@update_unverified'));

# Logging method use by web socket on close and on open functions
// Route::post('WS_LOGIN', array('uses' => 'AppRealtime@ws_login'));
Route::post('WS_LOGIN', array('uses' => 'Cuser@ws_login'));
// Route::get('WS_LOGOUT', array('uses' => 'AppRealtime@ws_logout'));
Route::post('WS_LOGOUT', array('uses' => 'Cuser@ws_logout'));
// Route::post('WS_NOTIFY', array('uses' => 'AppRealtime@ws_notify'));
Route::post('WS_NOTIFY', array('uses' => 'Cuser@ws_notify'));

Route::post('API_LOGOUT_PLAYER', array('uses' => 'Cuser@api_logout_player'));

# Show PS avatar modal
// Route::post('ch_av', array('uses' => 'AppRealtime@ws_showAvatar'));
Route::post('ch_av', array('uses' => 'Cuser@ws_show_Avatar'));

# Organizing top rank of tangkas tournament
// Route::get('top/{stop_broadcast}', array('uses' => 'GetController@getTopRank'));
Route::get('top/{stop_broadcast}', array('uses' => 'Cproducts@get_top_rank'));

# Chat support status update
// Route::post('check_support', array('uses' => 'AppSupport@check_support'));
Route::post('check_support', array('uses' => 'Ccomponents@check_support'));
// Route::get('check_support_cron', array('uses' => 'AppSupport@check_support'));
Route::get('check_support_cron', array('uses' => 'Ccomponents@check_support'));

# Players that missed companyclient registration during accept T&C
// Route::get('companyclient_register', array('uses' => 'AppAPI@missedRegistration'));
Route::get('companyclient_register', array('uses' => 'Cuser@missed_registration'));

# This route will close window via JS
# This is usually being use as reference route to game window back button to close game window like GPI
Route::get('close_window', function() {
    return Response::view('close_window');
});

# This route will used by SA to check if player site is accessible
Route::get('healthcheck', function(){
    DB::connection()->getPdo();
    echo "ONLINE";
});

# this will catch all the missing routes and throw 404 page
Route::group(['middleware' => ['web']], function () {
    Route::any('{catchall}', function ($page) {
       throw new Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    })->where('catchall', '(.*)');
});
