<?php

namespace Backend\services;

use Route;
use Input;
use Auth;
use View;
use URL;
use Config;
use Response;
use Lang;

/**
 * Use for creating PS landng page views and managing view datas 
 * 
 * @author PS Team
 */
class Sview extends Baseservice {

	private $data = array();

	/**	
	 * This will creare view for specific route
	 * @param  array  $override [route,param,input], if item wasn't set this will get the current values
	 * @return object
	 */
	public function create($override = array())
	{
		$route = isset($override['route']) ? $override['route']:Route::currentRouteName();
		$param = isset($override['param']) ? $override['param']:Route::getCurrentRoute()->parameters();
		$input = isset($override['input']) ? $override['input']:Input::all();

		$payload = $this->service('Scrypt')->crypt_encrypt(array(
					'route'       => $route,
					'param'       => $param,
					'input'       => $input,
					'auth'        => Auth::check(),
					'has_session' => $this->service('Ssiteconfig')->get_start_session()
				));

		// get intial view_data if set to not ajax
		if (!Config::get('settings.view_data.init_via_ajax')) {
			View::share('view_data', $this->view_data($payload));
		}

		View::share('payload'  , $payload);
		//Add token variable to share in view
		View::share('token', $this->service('Ssession')->generate_csrf());

		View::share('view_type', $this->route_view_type($route));
		
		return Response::view('master');
	}

	/**
	 * This will get all necessary data for view, parse view payload
	 * Give it a valid key and this will get it
	 * If $items is empty this will get the preset items
	 * @param  string/boolean $payload  If set to false this will get Input payload as payload 
	 * @return 
	 */
	public function view_data($payload = false, $items = null)
	{	
		// get and validate view info and payload
		if (!$payload) {
			$payload = Input::get('payload');
		}

		$decrypted_payload = $this->service('Scrypt')->crypt_decrypt($payload); 
		$view_info  	   = $this->parse_view_payload($decrypted_payload);
		
		$this->service('Svalidate')->validate(array(
			'view_payload' => array(
								'value' => array(
											'payload' 	=> $decrypted_payload,
											'view_info' => $view_info
										)
							)
		), true);

		// fetch items
		$get_items = custom_json_decode($items);

		if (!is_array($get_items) || empty($get_items)) {
			$get_items = $view_info['initial_view_data'];
		}

		$getter = function($item) use($view_info, $decrypted_payload) {
					switch ($item) {
						case 'navigation': return $this->get_navigation(
											$view_info['include_menu'],
											$view_info['exclude_menu']
										);
						case 'site'      : return array(
											'version'         => APP_VERSION,
											'mode'            => $this->service('Ssiteconfig')->get_app_mode(),
											'name'            => Config::get("settings.PRODUCT_NAME"),
											'whiteLabelID'    => Config::get('settings.WL_CODE'),
											'domains'         => $this->service('Ssiteconfig')->get_site_domains(),
											'mobile_redirect' => (
																   !$this->service('Ssiteconfig')->is_mobile_platform()
																&&  $view_info['mobile_redirect']
																&& !set_default($decrypted_payload['input'],'desktop',0) 
																&& $this->service('Ssiteconfig')->is_mobile_ready()
																),
											'is_mobile'       => $this->service('Ssiteconfig')->is_mobile_platform(),
											'is_mobile_ready' => $this->service('Ssiteconfig')->is_mobile_ready(),
											'base_url'        => URL::to('/')
										);
						case 'lang' 	 : return array(
											'language' 			 => Lang::get('language'),
											'messages' 			 => Lang::get('messages'),
											'error'   			 => Lang::get('error'),
											'custom'  			 => Lang::get('custom')
										);

						case 'lang_config': return array(
											'list'   => $this->service('Ssiteconfig')->theme_lang_array(),
											'active' => $this->service('Ssiteconfig')->get_lang_id()
										);

						case 'user' 	 : return $this->get_user();
						case 'route'     : return array(
											'view_type'       => $view_info['view_type'],
											'default_location'=> $this->service('Ssiteconfig')
													 				  ->get_default_location(
														 				 	$view_info['default_location']
													 				  	)
										);
						case 'websocket' : return array(
											'url'         => Config::get('settings.WS_CONNECTION')
													      	.':'
													      	.Config::get('settings.WS_PORT')
													      	.'/'
													      	.Config::get('settings.WS_NAMESPACE'),
											'topics'	  => $this->service('Ssiteconfig')->websocket_topics(
																$decrypted_payload
															),
											'global_subs' => $this->ws_global_categories($decrypted_payload)
										);
						case 'configs'      : return $this->get_frontend_configs();
						case 'bank_dropdown': return $this->service('Ssiteconfig')->bank_dropdown();
						case 'currency'     : return array(
												'enabled' => $this->service('Ssiteconfig')->currency_dropdown(),
												'base'    => $this->service('Ssiteconfig')->currencyID()
											);
						case 'bonus_new_member': return array(
													'isEnabled'=> $this->service('Ssiteconfig')->new_bonus_enabled(),
													'dropdown' => $this->service('Ssiteconfig')->new_bonus_dropdown(),
												);
						case 'securityQuestions'  : return $this->service('Ssiteconfig')->securityQuestions();
						case 'wallets_dropdown'   : return $this->service('Ssiteconfig')->get_wallets();
						case 'account'            : return $this->account_view();
						case 'report'             : return $this->report_view();
						case 'live_casino'        : return $this->live_casino_view();
						case 's_sports'           : return $this->sports_view();
						case 'tangkas'            : return $this->tangkas_view();
						case 'flash'              : return $this->service('Ssession')->get_flash();
						case 'live_togel'         : return $this->live_togel_view();
						case 'view_css'           : return $this->service('Ssiteconfig')->view_css(
														$decrypted_payload['route']
													);
						case 'game_window'        : return $this->game_window_view($decrypted_payload);
						case 'savvy_announcement' : return $this->service('Ssiteconfig')->savvy_announcement_assets();
						case 'google_analytics'   : return array('id' => Config::get('settings.google_analytics'));
						case 'error_page'         : return $this->error_page_view($decrypted_payload);
						case 'error_notif_config' : return $this->error_notif_config($decrypted_payload);
						case 'news_tab'           : return $this->news_view();
						case 'bank_account_config': return config::get('settings.input_fields');
						case 'google_tagmanager'  : return array('id' => Config::get('settings.google_tagmanager'));
						case 'reward'             : return $this->reward_view();
					}
				};

		foreach ($get_items as $item) {
			if (!isset($this->data[$item])) {

				$item_content = $getter($item);

				// assign only if the getter returns not null
				if ($item_content !== null) {
					$this->data[$item] = $item_content;
				}

			}
		}

		return $this->data;
	}

	/**
	 * This will parse the payload being passed then assign data base on the parsed payload
	 * @param  $payload The encrypted payload
	 * @return void
	 */
	private function parse_view_payload($payload)
	{
		// additional view information base on payload route
		switch ($payload['route']) {

			case '/':

				return array( 
					'default_location'  => false, 
					'view_type'  	    => $this->route_view_type($payload['route']), 
					'mobile_redirect'   => true,
					'include_menu' 	    => false,
					'exclude_menu' 	    => array(110),
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'user',
											'route',
											'websocket',
											'lang_config',
											'configs',
											'flash',
											'view_css',
											'savvy_announcement',
											'google_analytics',
											'error_notif_config',
											'google_tagmanager'
										)
				);

			case 'balance': 

				return array(
					'default_location'  => '#ingame_balance', 
					'view_type'  	    => $this->route_view_type($payload['route']), 
					'mobile_redirect'   => false,
					'include_menu'      => array(107),
					'exclude_menu' 	    => false,
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'user',
											'route',
											'websocket',
											'lang_config',
											'view_css',
											'error_notif_config'
										)
				);

			case 'statement': 

				return array(
					'default_location'  => '#statement', 
					'view_type'  	    => $this->route_view_type($payload['route']), 
					'mobile_redirect'   => false,
					'include_menu'      => array(104),
					'exclude_menu' 	    => false,
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'user',
											'route',
											'websocket',
											'lang_config',
											'view_css',
											'error_notif_config'
										)
				);

			case 'game_guide':

				$game_details = $this->repository('Rproducts')->get_gameName($payload['param']['gameID']);
				$game_key = $this->service('Ssiteconfig')->gameName_unique_key(
																			$payload['param']['gameID'],
																			$game_details['gameName'],
																			$game_details['productName']
																		);
				return array(
					'default_location'  => '#game_guide_'.$game_key, 
					'view_type'  	    => $this->route_view_type($payload['route']), 
					'mobile_redirect'   => false,
					'include_menu'      => array(106),
					'exclude_menu' 	    => false,
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'user',
											'route',
											'websocket',
											'lang_config',
											'view_css',
											'error_notif_config'
										)
				);

			case 'game_rules':

				$productName   = $this->repository('Rproducts')->get_productName_key(
																	$payload['param']['productID'],
																	array(
																		$this->service('Ssiteconfig'),
																		'productName_formatter'
																	)
																);

				return array(
					'default_location'  => '#gaming_rules_'.$productName, 
					'view_type'  	    => $this->route_view_type($payload['route']), 
					'mobile_redirect'   => false,
					'include_menu'      => array(106),
					'exclude_menu' 	    => false,
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'user',
											'route',
											'websocket',
											'lang_config',
											'view_css',
											'error_notif_config'
										)
				);

			case 'game_window':

				return array( 
					'default_location'  => '#game_window', 
					'view_type'  	    => $this->route_view_type($payload['route']), 
					'mobile_redirect'   => false,
					'include_menu'      => array(110),
					'exclude_menu' 	    => false,
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'user',
											'route',
											'websocket',
											'lang_config',
											'view_css',
											'savvy_announcement',
											// make sure this will come after websocket data
											// so game window topic can be created
											'game_window',
											'error_notif_config'
										)
				);

			case 'error_window':
				return array( 
					'default_location'  => '#error_page', 
					'view_type'  	    => $this->route_view_type($payload['route']),
					'mobile_redirect'   => false, 
					'include_menu'      => array(111),
					'exclude_menu' 	    => false,
					'initial_view_data' => array(
											'navigation',
											'site',
											'lang',
											'route',
											'view_css',
											'error_page',
											'error_notif_config'
										)
				);


			default: return null;
		}
	}

	/**
	 * Get route view type
	 * @param  string  $route [description]
	 * @return boolean        [description]
	 */
	private function route_view_type($route) 
	{	
		switch ($route) {
			case 'balance'     : 
			case 'statement'   : 
			case 'game_guide'  :
			case 'game_rules'  : return 'ingame';
			case 'game_window' : return 'game_window';
			case 'error_window': return 'error_window';
			default            : return 'main';
		}
	}

	/**
	 * Set user data that is sharable to view
	 * @return array 
	 */
	private function get_user()
	{
		if (Auth::check()) {

			$status_id = Auth::user()->derived_status_id;

			return array(
				'id'                      => $this->service('Ssession')->get('encrypted_clientID'),
				'is_auth'                 => 1,
				'firstName'			      => Auth::user()->firstName,
				'lastName'			      => Auth::user()->lastName,
				'loginName' 	   		  => Auth::user()->loginName,
				'lastLogin'    			  => custom_date_format('m/d/Y h:i:s A', Auth::user()->lastLogin),
				'username' 	   		      => Auth::user()->username,
				'displayName'  		      => Auth::user()->displayName,
				'displayNameStatus'    	  => Auth::user()->displayNameStatus,
				'email'        		   	  => Auth::user()->email == Auth::user()->username ? ' ' : Auth::user()->email,
				'mobile'  				  => Auth::user()->mobile,
				'securityQuestion'  	  => Auth::user()->securityQuestion,
				'referralLink'  		  => Auth::user()->referralLink,
				'bankName' 	  			  => Auth::user()->bankName,
				'accountBankName'  	      => Auth::user()->accountBankName,
				'accountBankNo'  		  => Auth::user()->accountBankNo,
				'derived_is_transactable' => Auth::user()->derived_is_transactable,
				'derived_status_id'   	  => $status_id,
				'status_error'   	      => $this->service('Svalidate')->status_err_codes($status_id, true),
				'isWalkIn'   		      => Auth::user()->isWalkIn,
				'playableBalance' 	   	  => custom_money_format(Auth::user()->playableBalance),
				'availableBalance'   	  => custom_money_format(Auth::user()->availableBalance),
				'currency_code' 	   	  => Auth::user()->currency_code,
				'currency_description' 	  => Auth::user()->currency_description,
				'avatar_filename'   	  => Auth::user()->avatar_filename,
				'pokerAvailableLimit'     => custom_money_format(Auth::user()->pokerAvailableLimit),
				'parent'      		  	  => array('username' => Auth::user()->parent_username)
			);

		} else {

			$guest_sessions = array_only(
								$this->service('Ssession')->guest_sessions(), 
								array('parent_username', 'guestName', 'url_referrer')
							);
			return array(
				'id'           => '',
				'is_auth'      => 0,
				'username'     => $guest_sessions['guestName'],
				'url_referrer' => $guest_sessions['url_referrer'],
				'parent'       => array('username' => $guest_sessions['parent_username'])
			);
		}
	}

	/**
	 * This will get all the data for view navigations
	 * @param  array/boolean $include_menu  	[productIDs] If non empty array, all productIDs listed here will be the
	 * 											only included in the menu
	 * @param  array/boolean $exclude_menu  	[productIDs] If non empty array, all productIDs listed here will be the
	 * 											excluded in the menu, this will be filtered last, so if productID
	 * 											exists in included_menu it will be overwrited by this   
	 * @return array  			   [menu, sidebars]
	 */
	private function get_navigation($include_menu, $exclude_menu)
	{
		$navigation_data 	= array();
		$enabled_menus 		= $this->service('Ssiteconfig')->get_enabled_menu(false, $include_menu, $exclude_menu);
		$pre_requisite_page = $this->service('Ssiteconfig')->get_prerequisite_page();

		// Derived data from getting the menu
		$navigation_data['sidebars'] = array();

		foreach ($enabled_menus as $menu_type => &$menu) {

			foreach ($menu as $menu_productID => &$menu_attributes) {

				// encrypt menu index as productID
				$menu_attributes['productID'] = $this->service('Scrypt')->crypt_encrypt($menu_productID);

				if ($pre_requisite_page) {
					
					$menu_attributes['page'] = $pre_requisite_page;

				}
				
				if (!isset($navigation_data['sidebars'][$menu_attributes['page']])) {

					$page_sidebars = $this->service('Ssiteconfig')->get_sidebar($menu_productID);

					if ($page_sidebars && count($page_sidebars) > 0) {

						$navigation_data['sidebars'][$menu_attributes['productID']] = $page_sidebars;

					}

				}

			}
			
			// remove productID as keys, to prevent showing it in frontend
			$menu = array_values($menu);
		}

		// put the finalized menu items to navigation data
		return assoc_array_merge($navigation_data, array('menu' => $enabled_menus));
	}

	/**
	 * This will return all useful configurations for frontend components/plugins
	 * @return array
	 */
	private function get_frontend_configs() 
	{		
		$configs = array();

		assoc_array_merge($configs, array(
			'banner' => array('products_to_home' => Config::get('settings.plugin.banner.products_to_home')),
			'chat'   => $this->service('Ssiteconfig')->theme('chat')
		));

		if (Auth::check()) {

			assoc_array_merge($configs, array(
				'avatar'			 => array('max_count' => Config::get('settings.avatar.max_count')),
				'displayName_length' => Config::get('settings.user.displayName_length'),
				'status_codes_list'  => $this->service('Svalidate')->status_codes_list(),
				'loginName_length'   => Config::get('settings.user.loginName_length'),
				'usedBalance_display'=> Config::get('settings.user.usedBalanace_display')
			));

		} else {

			assoc_array_merge($configs, array(
				'loginName_length' => Config::get('settings.user.loginName_length')
			));

		}

		return $configs;
	}

	/**
	 * This will return all view data for account
	 * @return array
	 */
	private function account_view() 
	{		
		$deposit_captcha    = $this->service('Ssession')->get('deposit_captcha');
		$withdrawal_captcha = $this->service('Ssession')->get('withdrawal_captcha');
		$fund_captcha       = $this->service('Ssession')->get('fund_transfer_captcha');

		return array(
			'deposit_bank_noneditable' => Config::get('settings.DEPOSIT_PROFILE_BANKINFO'),
			'deposit_has_captcha'      => is_array($deposit_captcha) && $deposit_captcha['has_captcha'],
			'withdrawal_has_captcha'   => is_array($withdrawal_captcha) && $withdrawal_captcha['has_captcha'],
			'fund_has_captcha'         => is_array($fund_captcha) && $fund_captcha['has_captcha']
		);
	}

	/**
	 * This will return all view data for report
	 * @return array
	 */
	private function report_view() 
	{		
		return array(
			'statement_dates' => $this->service('Ssiteconfig')->statement_dates(),
			'row_per_page'    => Config::get('settings.report.row_per_page')
		);
	}
	
	/**
	 * This will return all view data for live_casino
	 * @return array
	 */
	private function live_casino_view() 
	{		
		return array(
			'gameID' => $this->service('Scrypt')->crypt_encrypt(Config::get('settings.live_casino.gameID'))
		);
	}

	/**
	 * This will return all view data for sports
	 * @return array
	 */
	private function sports_view() 
	{		
		if (Auth::check()) {
			return array(
				'gameID' => $this->service('Scrypt')->crypt_encrypt(Config::get('settings.sports.gameID'))
			);
		} else {

			$additional_params = array(
									'lang' => $this->service('Ssiteconfig')->game_lang_format(
												Config::get('settings.sports.productID')
											)
								);

			return array(
				'bsi_src' => url_add_query($this->service('Ssiteconfig')->get_dynamic_domain(Config::get('settings.sports.bsi_src')), $additional_params)
			);
		}
	}
	
	/**
	 * This will return all view data for tangkas
	 * @return array
	 */
	private function tangkas_view() 
	{		
		return array(
			'gameID' => $this->service('Scrypt')->crypt_encrypt(Config::get('settings.tangkas.gameID'))
		);
	}

	/**
	 * Get gameID of live togel
	 * @return  array
	 */
	private function live_togel_view() 
	{

		return array(
			'gameID' => $this->service('Scrypt')->crypt_encrypt(Config::get('settings.live_togel.gameID'))
		);

	}


	/**
	 * This will get game data saved in session for game window view
	 * @param  array $decrypted_payload 
	 * @return array
	 */
	private function game_window_view($decrypted_payload) 
	{
		$game_session = $this->service('Ssession')->validated_game_session($decrypted_payload['input']['payload']);


		// additional WS topics
		if ($game_session['result'] == true && isset($this->data['websocket'])) {
			array_push($this->data['websocket']['topics'],$game_session['ws_topic']);
		} else {
			return $game_session;
		}

		// flag if game have game guide
		$game_session['has_game_guide'] = $this->service('Ssiteconfig')->has_game_guide($game_session['game_data']); 


		// filter only game data that should be accessed in frontend
		$game_session['game_data'] = array_only(
										$game_session['game_data'], 
										array('maxpayout','gameID','productID','gameName')
									);

		return $game_session;
	}	

	/**
	 * This will get game data saved in session for game window view
	 * @param  array $decrypted_payload 
	 * @return array
	 */
	private function error_page_view($decrypted_payload)
	{
		return $decrypted_payload['input'];
	}

	/**	
	 * This lost of WS global categories to be subscribed per route
	 * @param  array $decrypted_payload 
	 * @return array
	 */
	private function ws_global_Categories($decrypted_payload) 
	{
		switch($decrypted_payload['route']) {
			case 'error_window': return array();
			case 'game_window' : return array('balance','validated','clear_storage');
			default            : return array(
				            				'open_avatar',
				            				'balance',
				            				'timeout',
				            				'client_status',
				            				'validated',
				            				'refresh',
				            				'clear_storage',
				            				'lo'
										);
		}
	}

	/**	
	 * This will return view configuration for handling error response 
	 * @param  array $decrypted_payload 
	 * @return array
	 */
	private function error_notif_config($decrypted_payload) 
	{
		switch($decrypted_payload['route']) {
			case 'error_window':
			case 'game_window' :
				
				return array(
					'timeout_user_action' => false,
					'client_status_action'=> false
				);

			default:

				return array(
					/**
					 * -------------------------------------------------------------------------------------------------
					 *  true : open notification with close/redirect page button.
					 *  false: open notification only
					 * -------------------------------------------------------------------------------------------------
					 */
					'timeout_user_action' => Auth::check(),
					'client_status_action'=> Auth::check()
				);
		}
	}

	/**
	 * get url of embeded news
	 * @return array 
	 */
	private function news_view()
	{
		return array(
				'news_src' => $this->service('Ssiteconfig')->get_dynamic_domain(Config::get('settings.news_url'))
			);
	}

	
	private function reward_view()
	{
		$marketingToken = $this->repository('Rplayer')->generate_marketing_token(
			Config::get('settings.user.sessionID_max_try'),
			Auth::user()->clientID
		);

		if ($marketingToken) {
			$payload = $this->service('Scrypt')->aes_encrypt(http_build_query(array(
                                                'token' => $marketingToken,
                                                'test'  => 'this is not a drill'
                                               
                                            )));
		$marketing_url = $this->service('Ssiteconfig')->get_dynamic_domain(config::get('settings.marketing_reward_src'));
			return array(
				'result'     => true,
				'reward_src' => url_add_query($marketing_url,array('payload' => $payload)),
				'payload'    => $payload,
			);
		}

        
		return array(
			'result'     => false

		);
	}
}