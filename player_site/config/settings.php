<?php

    /*
    |--------------------------------------------------------------------------
    | Session
    |--------------------------------------------------------------------------
    |
    */

    $config['SESSION_MAX_LIFE'] = '30 minutes';


    /*
    |--------------------------------------------------------------------------
    | Site info 
    |--------------------------------------------------------------------------
    |
    */

    $config['WEBTYPE_NAME'] = 'Player Site';
    $config['PRODUCT_NAME'] = '338A';


    /*
    |--------------------------------------------------------------------------
    | Banner
    |--------------------------------------------------------------------------
    |
    */

    $config['BANNERS_ALLOWED']['live_casino']      = 0;
    $config['BANNERS_ALLOWED']['home']             = 2;
    $config['BANNERS_BEFORE_LOGIN']['skill_games'] = 1;
    $config['BANNERS_BEFORE_LOGIN']['sports']      = 0;
    $config['BANNERS_BEFORE_LOGIN']['live_casino'] = 0;
    $config['BANNERS_AFTER_LOGIN']                 = array('home' => 0);
    $config['PRODUCT_ADMIN_FILTER']                = 1;

    /*
    |--------------------------------------------------------------------------
    | Deposit Limit
    |--------------------------------------------------------------------------
    |
    */
    $config['DEPOSIT_LIMIT_CHECKDB'] = 1;

    /*
    |--------------------------------------------------------------------------
    | Badges
    |--------------------------------------------------------------------------
    |
    */
    $config['BADGES_AFTER_LOGIN']    = array('games','promo','skill_games',1,2);

    /*
    |--------------------------------------------------------------------------
    | Plugin data
    |--------------------------------------------------------------------------
    |
    */
    $config['PLUGIN_DATA'] = array (
								'news',
								'transactions',
								'jackpot',
								'badge',
								'banner'
							);
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    */
    $config['CACHE_PREFIX'] = '338a';

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
    $config['plugin']['news']['date_format'] = 'M d Y';
    $config['plugin']['banner']              = array(
												'limit_per_productID' => array(
																			0 => 2,
																			1 => 1,
																			2 => 1,
																			3 => 1, 
																			4 => 1,
																			5 => 1,
                                                                            7 => 1
																		),
												'products_to_home'  => true
											);


	/*
	|--------------------------------------------------------------------------
	| sports
	|--------------------------------------------------------------------------
	|
	*/
    $config['sports']['theme']  ='black';
    $config['sports']['bsi_src']='https://sportsbook.338a.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';


    /*
    |--------------------------------------------------------------------------
    | RSO
    |-------------------------------------------------------------------------
    | rso_folder: asstes folder name in rso
    */
    $config['rso']['rso_folder']      = basename(dirname(__DIR__));
    $config['rso']['game_rso_folder'] = 'player_site';


    $config['serverIDs_disabled']  = array('GPI');
