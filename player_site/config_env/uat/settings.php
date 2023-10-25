<?php
    /*
	|--------------------------------------------------------------------------
	| Web Socket
	|--------------------------------------------------------------------------
	|
	| WS_CONNECTION: URL use in frontend to subscribe to WS
	|
	*/
	$config['WS_CONNECTION'] = replace_subdomain($_SERVER['HTTP_HOST'], 'wss://ws');

    /*
	|--------------------------------------------------------------------------
	| Live Chat
	|--------------------------------------------------------------------------
	|
	*/
	$config['LIVECHAT_PLUGIN'] =1;

    /*
	|--------------------------------------------------------------------------
	| RSO (old)
	|--------------------------------------------------------------------------
	|
	*/
	$config['assets']['rso_url']              = 'https://www.tryrso.com';
	$config['assets']['path']                 = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['wl_path']              = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['common_path']          = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['wl_fallback_path']     = 'https://www.DOMAIN.com/assets/';
	$config['assets']['common_fallback_path'] = 'https://www.try338a.com/assets/';
	$config['assets']['rso_folder']           = basename(dirname(dirname(__DIR__)));

    /*
	|--------------------------------------------------------------------------
	| SPORTS
	|--------------------------------------------------------------------------
	|
	*/
	$config['SPORTS_LOBBY_URL'] ='https://sportsbook.338a.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';
	$config['sports']['bsi_src']='https://sportsbook.338a.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';

    /*
	|--------------------------------------------------------------------------
	| WHITE LABEL
	|--------------------------------------------------------------------------
	|
	*/
	$config['WL_CODE']        = '';
	$config['WL_IS_ORIGINAL'] = 1; 

    /*
	|--------------------------------------------------------------------------
	| WHITE LABEL
	|--------------------------------------------------------------------------
	|
	*/
	$config['WL_CODE']        = '';
	$config['WL_IS_ORIGINAL'] = 1; 

    /*
	|--------------------------------------------------------------------------
	| EMAIL USERS
	|--------------------------------------------------------------------------
	|
	*/
	$config['EMAIL_SENDER']   = 'support@338a.com';
	$config['EMAIL_RECEIVER'] = 'sc@leekie.com';

    /*
	|--------------------------------------------------------------------------
	| REGISTRATION
	|--------------------------------------------------------------------------
	|
	*/
	$config['BONUS_NEW_MEMBER']     = 0;
	$config['REGISTRATION_PARENTS'] = array(15);

    /*
	|--------------------------------------------------------------------------
	| LAUNCH
	|--------------------------------------------------------------------------
	|
	*/
	$config['launch'] = array(
				        	"start"=>"2017-04-26",
				        	"end"=>"2017-04-28"
				        );


    /*
	|--------------------------------------------------------------------------
	| rso
	|--------------------------------------------------------------------------
	|
	| RSO assets settings
	|
	*/
	$config['rso']['all']['assets']                 = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['all']['wl_assets']              = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['fallback']['wl_assets_frontend']= 'https://www.DOMAIN.com/assets/';


    /*
    |--------------------------------------------------------------------------
    | promotion
    |--------------------------------------------------------------------------
    */
	$config['promotion']['new_member_enabled'] = false;