<?php
    /*
	|--------------------------------------------------------------------------
	| Live Chat
	|-------------------------------------------------------------------------
	|
	*/
	$config['LIVECHAT_PLUGIN'] = 1;

    /*
	|--------------------------------------------------------------------------
	| Live Chat
	|-------------------------------------------------------------------------
	|
	*/
	$config['assets']['rso_url']     = 'https://onyx.628c.com';
	$config['assets']['path']        = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['wl_path']     = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['common_path'] = '/ps/'.basename(dirname(dirname(__DIR__))).'/';

    /*
	|--------------------------------------------------------------------------
	| SPORTS
	|-------------------------------------------------------------------------
	|
	*/
	$config['SPORTS_LOBBY_URL'] ='https://sportsbook.DOMAIN.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';
	$config['sports']['bsi_src']='https://sportsbook.DOMAIN.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';

    /*
	|--------------------------------------------------------------------------
	| WHITE LABEL
	|-------------------------------------------------------------------------
	|
	*/
	$config['WL_CODE']        = '';
	$config['WL_IS_ORIGINAL'] = 1;

    /*
	|--------------------------------------------------------------------------
	| EMAIL USERS
	|-------------------------------------------------------------------------
	|
	*/
	$config['EMAIL_SENDER']   = 'support@338a.com';
	$config['EMAIL_RECEIVER'] = 'goc@leekie.com';

    /*
	|--------------------------------------------------------------------------
	| REGISTRATION
	|-------------------------------------------------------------------------
	|
	*/
	$config['BONUS_NEW_MEMBER']     = 0;
	$config['REGISTRATION_PARENTS'] =  array(3);

    /*
	|--------------------------------------------------------------------------
	| rso
	|--------------------------------------------------------------------------
	|
	| RSO assets settings
	|
	*/
	$config['rso']['all']['assets']                          = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['all']['wl_assets']                       = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['fallback']['wl_assets_frontend']         = 'https://www.DOMAIN.com/assets/';

    /*
	|--------------------------------------------------------------------------
	| google analytics license
	|--------------------------------------------------------------------------
	|
	*/
	$config['google_analytics'] = 'UA-12769516-4';
	
    /*
    |--------------------------------------------------------------------------
    | promotion
    |--------------------------------------------------------------------------
    */
	$config['promotion']['new_member_enabled'] = false;