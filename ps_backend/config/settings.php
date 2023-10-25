<?php

$config = array(
        
      
    /*
	|--------------------------------------------------------------------------
	| PROMO_NEW_DATE
	|--------------------------------------------------------------------------
	|
	| Set this number of days for the promotion that can be tagged
	| as "New".
    | 0 - Tagging is being done manually by admin.
	|
	*/
        
    'PROMO_NEW_DATE' => 14,
        
    /*
	|--------------------------------------------------------------------------
	| GAME_NEW_DATE
	|--------------------------------------------------------------------------
	|
	| Set this number of days for the game that can be tagged
	| as "New".
                | 0 - Tagging is being done manually by admin.
	|
	*/
        
    'GAME_NEW_DATE' => 0,
        
    /*
	|--------------------------------------------------------------------------
	| TANGKAS_LOBBY_URL
	|--------------------------------------------------------------------------
	|
	| Base URL of Tangkas or MMPoker Game
	|
	*/
        
    'TANGKAS_LOBBY_URL' => 'https://tangkas.'.HOST_TLD.'/',
        
    /*
	|--------------------------------------------------------------------------
	| BASE_CURRENCY
	|--------------------------------------------------------------------------
	|
	| The base currency that will be use by the app
	|
	*/
        
    'BASE_CURRENCY' => 15,  // Set this base from the table 'Currency'
        
    /*
	|--------------------------------------------------------------------------
	| PROMO_MAX_ROW_PER_PAGE
	|--------------------------------------------------------------------------
	|
	| Number of promotion rows to be display in promotion section
	|
	*/
        
    'PROMO_MAX_ROW_PER_PAGE' => 3,
        
        /*
	|--------------------------------------------------------------------------
	| GAMES_MAX_ROW_PER_PAGE
	|--------------------------------------------------------------------------
	|
	| Number of game rows to be display in games section
	|
	*/
        
    'GAMES_MAX_ROW_PER_PAGE' => 3,

    /*
	|--------------------------------------------------------------------------
	| EXPIRED_PASSWORD
	|--------------------------------------------------------------------------
	|
	| Allow the application to check and force the player to change 
    | the password every specific number of months
	|
	*/
        
    'EXPIRED_PASSWORD' => true,
        
    /*
	|--------------------------------------------------------------------------
	| Web Socket
	|--------------------------------------------------------------------------
	|
	| WS_PORT      : Port that will be use to connect to WebSocket
	| WS_TCP       : Use in back-end (ZMQ+Ratchet) connection
	|
	*/
    
    'WS_PORT'       => 8443,
    'WS_TCP'        => 'tcp://10.22.3.1:5555', 
    'WS_NAMESPACE'  => 'onyx', 
    
    /*
	|--------------------------------------------------------------------------
	| SEND_MAIL
	|--------------------------------------------------------------------------
	|
	| Allow system to send e-mail to real recipient. False, send to
    | specific e-mail only.
	|
	*/
        
    'SEND_MAIL' => false,
        
    /*
	|--------------------------------------------------------------------------
	| Player Site Mode
	|--------------------------------------------------------------------------
	|
	| __NORMAL      :    Allow players to use the application
	| __MAINTENANCE :   Prohibit the players from logging in to their account
	| __TEST        :    Allow only test accounts to use the application
	|
	*/
        
    '__NORMAL' 		 => 0,

    '__MAINTENANCE'  => array(1,3),

    '__TEST' 		 => 2,

    'TANGKAS_GAMEID' => 2,

    /*
	|-------------------------------------
	|Email EXPIRATION
	|-------------------------------------
	|
	|
	*/
	'EMAIL_EXPIRATION' => '1 month',
        
    /*
	|--------------------------------------------------------------------------
	| DEFAULT_LANGUAGE
	|--------------------------------------------------------------------------
	|
	| Default language that will be use upon registration
	|
	*/
	'language' => array(

		'base_languageID' => 4
	),
	
    'DEFAULT_LANGUAGE'  => 'id',
    
    'BET_DETAILS_URL'   => 'https://ogs01lab.'.HOST_TLD.'/lke_header/creport?payload={payload}',

    'BET_DETAILS_URL2'  => 'https://ogs01lab.'.HOST_TLD.'/lke_header/cmmreport?payload={payload}',

    'JOIN_TANGKAS_URL'  => 'https://tangkas.'.HOST_TLD.'/continuegame?payload={payload}',

    'BANNERS_ALLOWED'       => array(
                                "home"          => 5,
                                "games"         => 1,
                                "skill_games"   => 1,
                                "tangkas"       => 1, 
                                "live_togel"    => 1, 
                                "sports"        => 1, 
                                "live_casino"   => 1
                            ),
    'BANNERS_BEFORE_LOGIN'  => array(
                                "home"          => 0,
                                "games"         => 1,
                                "skill_games"   => 2,
                                "tangkas"       => 3, 
                                "live_togel"    => 4, 
                                "sports"        => 5, 
                                "live_casino"   => 6
                            ),

    'DEPOSIT_PROFILE_BANKINFO' => 1,

    # put game IDs here that needed token from API/Also need to register on first login
    'TOKEN_ON_API'          => array(4,8),

    'AGENT_WHITELIST'       => false,
    'COMMISION_PLCOMRAKE'   => 0,
    'COMMISION_AGENTPT'     => 0,

    # ~ GAMES play() settings ~
    # game IDs that requires display name
    'GAMES_REQ_DISPLAYNAME' => array(1, 2, 5, 6),
    
    # game IDs that has no test mode
	'GAMES_NO_TEST' => array(0),
    
    # game IDs that has lobby type
    'GAMES_LOBBY_TYPE'      => array(2,3),
    
    # product IDs that should start websession on PS side
    'GAMES_START_SESSION'   => array(5),
    
    # game IDs that should have encoded url as token
    'GAMES_URLENCODED_TOKEN'=> array(2),

    # Put all productID with games that cant be played in multiple window
    'PRODUCT_SINGLE_WINDOW' => array(1),

    # Menu Badges
    'BADGES_BEFORE_LOGIN'   => array("promo"),
    'BADGES_AFTER_LOGIN'    => array("games","promo","skill_games"),
		
	# Set to 1 if all "plugin/" ajax will be at once, 0 if by chunk of data only
	'PLUGIN_PRESET'		  => 1,
	'PLUGIN_EXTRA_PARAM'  => array(
								"badge" 		=> array(
													"gameCount"  => array( "games","skill_games"),
													"promoCount" => true 
												),
								"transactions"  => array(
													"winner" => array(2,3),
													"trans"=> array("Deposit","Withdrawal")
												)
							),

	'PLUGIN_UNCACHE'	  => array("bank operational"),
    'FT_ALLOW_WITHDRAWAL' => true,
        
    /*
	|--------------------------------------------------------------------------
	| log
	|--------------------------------------------------------------------------
	|
	| contains settings for our logger
	|
	*/
	'log' => array(

		'storage' => '/var/log/338a/player_site/'

	),

    /*
    |--------------------------------------------------------------------------
    | plugin
    |--------------------------------------------------------------------------
    |
    | contains config for getting data of some plugins
    |
    | ~ IMPORTANT NOTES ~
    | * plugin_cache configuration is located in theme_* configuration
    |  	because it depends if config is for guest or player 
    | * badge plugin has configuration in theme_*.menu.has_badge & theme_*.submenu.has_badge
    |
    */
    'plugin' => array(

        'news' => array(
            'count' => 15,
            'preview_characters' => 200,
            'date_format'        => 'M d Y'
        ),

		'transactions' => array(

			'deposit' => array(

				'minimum_amount' => 50,
				'display_qouta'  => 5,
				'limit' 	     => 5

			),

			'withdrawal' => array(

				'minimum_amount' => 50,
				'display_qouta'  => 5,
				'limit' 		 => 5

			),

			'winner' => array(

				'minimum_amount' => 1,
				'display_qouta'  => 10,
				'limit' 		 => 5,
				'productIDs' 	 => array(3),
				'key_prefix'     => 'product'

			)
		),

		// productID => banner count
		'banner' => array(

			'limit_per_productID' => array(
										0 => 5,
										1 => 1,
										2 => 1,
										3 => 1, 
										4 => 1,
										5 => 1,
										6 => 1,
										7 => 1
									),
			
			// set to true if other product banners should be displayed to home also
			'products_to_home'   => false

		),

		'sidebanner' => array('limit' => 10),
		'disabled_productIDs'   => array(
			'jackpots' => array(2),
			'winner'   => array(2)
		) 
    ),
    
    /*
	|--------------------------------------------------------------------------
	| transactions
	|--------------------------------------------------------------------------
	|
	| Transactions related configurations
	|
	*/

	'transactions' => array(

		'accounting_time' => '08:00:00',

		'fund_transfer'   => array(
			'allow_withdrawal' => true
		),
		
		'time_interval' => 1,

		'max_count'     => 3

	),

    /*
	|--------------------------------------------------------------------------
	| currency
	|--------------------------------------------------------------------------
	|
	| contains all items config for getting data in plugin route
	|
	*/
	'currency' => array(

		'base_currencyID' => 15

	),    

	/*
	|--------------------------------------------------------------------------
	| report
	|--------------------------------------------------------------------------
	|
	| contains all report configurations
	|
	*/
	'report' => array(

		'statement_months' 		 => 3,
		'transaction_logs_range' => '1 hour',
		'row_per_page' 	   	     => 20,
		'disabled_bet_details'   => array('GPI')

	),

	'report_window' => array(
		'default'     => array("width" => 800,"height"=> 717),
		'live_casino' => array("width" => 815,"height" => 660)
	),
	
	/*
	|--------------------------------------------------------------------------
	| user
	|--------------------------------------------------------------------------
	|
	| contains all user configurations
	|
	*/
	'user' => array(
		'displayName_prefix'  		 => array('HEART', 'SPADE', 'DIAMOND', 'CLOVER'),
		'displayName_max_try' 		 => 3,
		'displayName_length'         => array('min' => 6, 'max'=> 15),
		'new_player_isWalkIn'  		 => 1,
		'max_login_attempt'   		 => 3,
		'max_wrongPassword_attempt'  => 6,
		'sessionID_max_try'          => 3,
		'marketingToken_max_try'     => 3,
		'max_day_registration'		 => 7,
		'password_expiration_months' => 3,
		'loginName_length'           => array('min' => 6, 'max'=> 15),
		'registration_expiration'    => 3,
		'max_field'					 => array(
											'firstName'       => 50,
											'lastName'        => 50,
											'email'           => 100,
											'yourAnswer'      => 100,
											'accountBankName' => 100
										),
		'usedBalanace_display'       => FALSE
	),

	/*
	|--------------------------------------------------------------------------
	| avatar
	|--------------------------------------------------------------------------
	|
	| contains all avatar configurations
	|
	*/

	'avatar' => array(
		'mime_type'    => array('jpg', 'png', 'jpeg'),
		'max_filesize' => 204800,
		'max_count'    => 3,
		'default_image' => 'default.jpg',
	),

	/*
	|--------------------------------------------------------------------------
	| products
	|--------------------------------------------------------------------------
	|
	| contains configuration for our products and games 
	| 
	*/
	'products' => array(

		'thumbnails_assets' 	 => array(
									'thumbnail_path'  => '/images/product/',
									'preview_path'    => '/images/product_preview/',
									'slide_path'      => '/images/product_slide/'
								 ),

		'retrieve_list'		     => array(
									'unsorted_productIDs' => array(2)
								 ),

		'house_walletID'  	 	 => 'house',

		'transferable_walletIDs' => array(6)
		
	),

    /*
    |--------------------------------------------------------------------------
    | help
    |--------------------------------------------------------------------------
    |
    | contains configuration for our help page
    */
    'help' => array(

        // list of productIDs with FAQ
        'faq_productIDs'          => array(2,3,1),
        
        // list of productIDs with game rules
        'gaming_rules_productIDs' => array(3,1,4),
        
        // list of gameIDs disabled for game guide
        'disabled_game_guides'    => array(),

		'serverID_without_GG'     => array('SBO','SBO_RNG','GPI','SVY','BND','CEM','OFC','PQQ','RPS')

    ),

    /*
    |--------------------------------------------------------------------------
    | websocket
    |--------------------------------------------------------------------------
    |
    | websocket configs
    */
    'websocket' => array(
    	'global_topic' => 'player_site'
    ),

    /*
    |--------------------------------------------------------------------------
    | promotion
    |--------------------------------------------------------------------------
    |
    | All promotion system settings
    */
    'promotion' => array(
    	'new_member_enabled'     => true,
    	'new_member_promotionID' => 1,
    	'new_member_dropdown'    => array('.3','.1'),
    	'row_per_page'           => null
    ),

    /*
    |--------------------------------------------------------------------------
    | live casino
    |--------------------------------------------------------------------------
    |
    */
    'live_casino' => array(
    	'gameID' => 8
    ),

    /*
    |--------------------------------------------------------------------------
    | sports
    |--------------------------------------------------------------------------
    |
    */
    'sports' => array(
    	'gameID'    => 4,
    	'productID' => 5,
    	'bsi_src'   => 'https://sportsbook.gobandarq.com/web-root/restricted/default.aspx?theme=ocean&oddstyle=id'
    ),

    /*
    |--------------------------------------------------------------------------
    | tangkas
    |--------------------------------------------------------------------------
    |
    */
    'tangkas' => array(
    	'gameID' => 2
    ),


    /*
    |--------------------------------------------------------------------------
    | view_data
    |--------------------------------------------------------------------------
    |
    */
    'view_data' => array(
    	'init_via_ajax' => true
    ),

    /*
     |--------------------------------------------------------------------------
     | Live Togel
     |--------------------------------------------------------------------------
     |
     */
    'live_togel' => array (
    	'gameID' => 3
    ),

	//configs that same value in all Player site and all env
	'POKER_GAMEID' => 	1,

	// No. of days to expire unverified account
	'REGISTRATION_EXPIRATION'	=>	'P3D',

	'COMPANY_SETTINGS'			=>	array(
									'OPEN'		=>	0,
									'SUSPENDED'	=>	1,
									'CLOSED'	=>	2
								),
	// Member status
	'MEMBER_STATUS'			=>	array(
								'UNVERIFY'	=>	0,
								'OPEN'		=>	1,
								'SUSPENDED'	=>	2,
								'CLOSED'	=>	3,
								'LOCKED'	=>	4,
								'DELETED'	=>	5
							),

	//200kb max size of avatar bytes
	'avatar_filesize_max'       =>  204800,

	//Toggle availability of registration
	'ALLOW_REGISTRATION'		=>	true,

	//Dynamic URL settings
	'DYNAMIC_URL'			=>	true,

	'EMAIL_RECEIVER'        => 'lp_dev@leekie.com',

	'MOBILE_READY'      => 1,
	
	// Disable items  on menu per environment by product ID assigned to them
	'MENU_DISABLED'=> array(102),

    // 3 for row and 6 promo per row
    'promotions' => array(
		'row_per_page' => 18
	),

	//announcement 30days ago
	'VALID_ANNC_DAYS' => 30,

	//Show live games tab = 1, hide = 0
	'LIVEGAMES'             => 1,

	// Use Livechat plugin if set to 1
	'LIVECHAT_PLUGIN'       => 0,
	
	'FORCE_HTTPS' => true,

	// Disable games per environment / application
	'GG_DISABLED_GAMES' => array(0),

	// Bonus new member Promotion enable/disable
    'BONUS_NEW_MEMBER'      => 1,

	/*
	|--------------------------------------------------------------------------
	| guest
	|--------------------------------------------------------------------------
	|
	| contains all guest only configurations
	|
	*/
	'guest' => array(

		'create_max_try' => 3

	),

	/*
	|--------------------------------------------------------------------------
	| game_advance_preview
	|--------------------------------------------------------------------------
	|
	| set if we will going to show game thumbnail and youtube preview
	| 
	*/
	'game_advance_preview' => array('ASI' => 1, 'BSI' => 0),

    /*
	|--------------------------------------------------------------------------
	| rso (old)
	|--------------------------------------------------------------------------
	|
	| contains settings for our logger
	|
	*/
	'assets' => array(
				"compress"                => 0,
				"autoversion"             => 0,
				"rso_url"                 => 'https://localhost'.$_SERVER['REQUEST_URI'],
				"path"                    => "/assets/",
				"wl_path"                 => "/assets/",
				"common_path"             => "/assets/",
				"detect_path"             => "images/favicon/favicon32.png",
				"fallback_path"           => 'https://localhost'.$_SERVER['REQUEST_URI'].'/assets/',
				"wl_fallback_path"        => 'https://localhost'.$_SERVER['REQUEST_URI'].'/assets/',
				"common_fallback_path"    => 'https://localhost'.$_SERVER['REQUEST_URI'].'/assets/',
				"system_assets_dirname"   => "assets_sys",
				'banners_promotions_path' => '/banners_promotions/'
			),

	/*
	|--------------------------------------------------------------------------
	| rso
	|--------------------------------------------------------------------------
	|
	| PS own assets settings
	|
	*/
	'rso' => array(
						'frontend_url'   => '',
						'backend_url'	 => '/var/www/html/rso',
						'self_hosted_url'=> 'assets_sys',
						'compress' 		 => 0,
						'autoversion'	 => 0,
						
						'frontend'    => array(),
						'backend'     => array(),
						'all'     	  => array(
											'assets' 			 => '/',
											'wl_assets' 		 => '/',
											'common_assets' 	 => '/',
											'bannerspromotions'	 => '/banners_promotions/',
											'gameguide'          => '/game_guide/',
											'avatar_ext'         => '/avatar/'
										),
						'self_hosted'  => array(
											'assets' => '/'
										),
						'fallback'    => array(
											'assets_frontend'    		 => 'https://localhost'.$_SERVER['REQUEST_URI'].'/assets/',
											'wl_assets_frontend' 		 => 'https://localhost'.$_SERVER['REQUEST_URI'].'/assets/',
											'common_assets_frontend' 	 => 'https://localhost'.$_SERVER['REQUEST_URI'].'/assets/',
											'bannerspromotions_frontend' => 'https://rso.DOMAIN.com/banners_promotions/',
											'gameguide_frontend'		 => 'https://rso.DOMAIN.com/game_guide/',
											'avatar_ext_frontend'        => 'https://rso.DOMAIN.com/avatar/'
										),	
						'script_paths' => array(
											'js'       		  => 'js/',
											'js_minified'     => 'js/min/',
											'css'      		  => 'css/',
											'css_minified'    => 'css/'
										),
						'view_css'     => array(
											'default'     => 'ps_core.css', 
											'game_window' => 'ps_game_window.css', 
											'error_window'=> 'ps_error_window.css'
										)
					),
	/*
	|--------------------------------------------------------------------------
	| Chat
	|--------------------------------------------------------------------------
	|
	*/
	'chat' => array(
		'message_range' => 0,
		'display_time'  => 20,
		'limit_per_retrieve' =>10,
		'max_per_send' => 10,
		'time_span' => 30,
		'delay_time' => 20 
	),

	'request_url_blocklist' => array('get', 'play', 'process'),

	/*
	|--------------------------------------------------------------------------
	| announcement
	|--------------------------------------------------------------------------
	|
	*/
	'announcement' => array(
						'display_days'  => 30,
						'date_format'   => 'm/d/Y h:i:s A',
					),
	/**
	 |-------------------------------------------------------------------------
	 | Games global configs
	 |-------------------------------------------------------------------------
	 | This config is not meant for 'Games' productName only,
	 | this config is for all games handling by PS
	 |
	 */
	'games' => array(
				'unique_key'         => array(200021 => '2'),
				'ps_window_serverID' => array('GPI'),

				//GPI games config
				'GPI'                => array(
											'operator' => 'GOBETX',
											'language' => array(
															'en' => 'en-us',
															'th' => 'th-th'
														)
										)	
			),

	'input_fields' => array(
		'bank_account_info' => 'default'
	),
	'curl_timeout' => 20,

	'hashids' => array(
		'salt' => 'ONYX_AGENT',
		'padding' => 5
	),

	'marketing_reward_src' => ''

); 

// connect to frontend root config
require dirname(base_path()).'/'.PROJECT_DIR.'/config/'.basename(__FILE__);

// connect to per environment config
require dirname(__DIR__).'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);

// return
return $config;