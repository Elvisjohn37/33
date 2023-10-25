<?php
    $config = array(
				# Other js, css, and meta file links are in global.static_assets
				'from' => 'player',

				# Other constant plugins are already declared in plugins.loader
				'plug'=> array('avatar'),

				# menu = array(<text/icon>,<text/icon name>,<page to show>,id extension)
				'menu' => array(
					'primary' => array(
						0 => array(
							'text'		=>'',
							'page'		=>'banner',
							'id'		=>'home',
						),
						5 => array(
							'text'		=> '{{@lang.language.sports}}',
							'page'		=> 'sports',
							'id'		=> 's_sports',
							'floating'       => true,
		                    'direct_trigger' => true,
					    	'featured'  => true
						),
						6 => array(
					    	'text'		     => '{{@lang.language.live_casino}}',
					    	'page'		     => 'live_casino',
					    	'id'		     => 'live_casino',
					    	'featured'       => array('soon' => true)
						),
						2 => array(
					    	'text'		=> '{{@lang.language.skill_games}}',
					    	'page'		=> 'skill_games',
					    	'id'		=> 'skill_games',
					    	'has_badge'	=> true,
					    	'featured'  => array('soon' => false)
					    ),
						3 => array(
					    	'text'      =>'{{@lang.language.tangkas}}',
					    	'page'      =>'tangkas',
					    	'id'        =>'tangkas',
					    	'featured'  => array('soon' => false)
						),
						1 => array(
					    	'icon'      => '',
					    	'text'      => '{{@lang.language.games}}',
					    	'page'      => 'games',
					    	'id'        => 'games',
					    	'has_badge' => true,
					    	'featured'  => array('soon' => false)
						),
						4 => array(
					    	'text'      => '{{@lang.language.live_togel}}',
					    	'page'      => 'live_togel',
					    	'id'        => 'live_togel',
					    	'featured'  => array('soon' => false)
						),
						7 => array(
		                	'text'     =>'{{@lang.language.multiplayer}}',
		                	'page'     =>'multiplayer',
		                	'id'       =>'multiplayer',
		                	'has_badge' => true,
		                    'featured' => array('soon' => false)
						),
						101 => array(
					    	'text'      => '{{@lang.language.promotions}}',
					    	'page'      => 'promo',
					    	'id'        => 'promo',
		               		'has_badge' => true,
					    	'featured'  => array('soon' => false)
						),
						102 => array(
							'text' => '{{@lang.language.tournament}}',
							'page' => 'tournament',
							'id'   => 'tournament'
						)
					),	

					'secondary'=> array(
						103 => array(
							'icon'=> '',
							'text'=> '{{@lang.language.account}}',
							'page'=> 'account',
							'id'  => 'account'
						),
						104 => array(
							'icon'=>'',
							'text'=>'{{@lang.language.report}}',
							'page'=>'report',
							'id'  =>'report'
						),
						105 => array(
							'text'=>'{{@lang.language.announcement}}',
							'page'=>'announcement',
							'id'  =>'announcement',
						),
						106 => array(
							'text'=>'{{@lang.language.help}}',
							'page'=>'help',
							'id'=>'help',
		            		'floating' => true,
		            		'first_sidebar' => false
						)
					),
				
					'hidden' => array(
									107 => array(
											'text'     => 'Ingame Balance',
											'page'     => 'ingame_balance',
											'id'       => 'ingame_balance',
			                    			'floating' => true
										),
			                        111 => array(
			                                'text'     => 'Error',
			                                'page'     => 'error_page',
			                                'id'       => 'error_page',
			                                'floating' => true
			                            )
								)
				),

		    /*
		    |--------------------------------------------------------------------------
		    | Sidebars
		    |--------------------------------------------------------------------------
		    |
		    | Set sidebars for each pages
		    | This sidebars will still being filtered by Ssiteconfig service.
		    | This means even if you put sidebar on this list, it might still be 
		    | removed by the service.
		    | <page> => array(<sidebars>)
		    | 
		    */
		    'sidebars' => array(
		                    103 => array(

			                        array(
			                            'text' => '{{@lang.language.profile}}',
			                            'id'   => 'profile'
			                        ),

			                        array(
			                            'text' => '{{@lang.language.balance}}',
			                            'id'   => 'balance'
			                        ),

			                        array(
			                            'text' => '{{@lang.language.register_friend}}',
			                            'id'   => 'register_friend'
			                        ),

			                        array(
			                            'text' => '{{@lang.language.deposit_confirmation}}',
			                            'id'   => 'deposit_confirmation'
			                        ),

			                        array(
			                            'text' => '{{@lang.language.withdrawal_request}}',
			                            'id'   => 'withdrawal_request'
			                        ),

			                        array(
			                            'text' => '{{@lang.language.change_password}}',
			                            'id'   => 'change_password'
			                        ),

			                        array(
			                            'text' => '{{@lang.language.fund_transfer}}',
			                            'id'   => 'fund_transfer'
			                        ),
			                    ),

		                    104 => array(

		                            array(
		                                'text'     => '{{@lang.language.statement}}',
		                                'id'       => 'statement',
		                                'active'   => true,
		                                'children' => array(
						                                array(
						                                	'id'   => 'betting_details',
						                                	'text' => '{{@lang.language.player_bet_list}}'
						                                ),
						                                array(
						                                	'id'   => 'credit_details',
						                                	'text' => '{{@lang.language.credit}}'
						                                ),
						                                array(
						                                	'id'   => 'transfer_details', 
						                                	'text' => '{{@lang.language.transfer}}'
						                                )
		                                            )

		                            ),

		                            array(
		                                'text' => '{{@lang.language.running_bets}}',
		                                'id'   => 'running_bets'
		                            ),

		                            array(
		                                'text' => '{{@lang.language.transaction_logs}}',
		                                'id'   => 'transaction_logs'
		                            )

		                        ),

		                    106 => array(

		                            array(
		                                'text'     => '{{@lang.language.faq}}',
		                                'id'       => 'faq',
		                                'children' => array(
		                                                array(
		                                                    'id'        => 'general', 
		                                                    'productID' => 0
		                                                )
		                                            )
		                            ),

		                            array(
		                                'text'     => '{{@lang.language.gaming_rules}}',
		                                'id'       => 'gaming_rules',
		                                'children' => array(
		                                                array(
		                                                    'id'        => 'general', 
		                                                    'productID' => 0
		                                                )
		                                            )
		                            ),

		                            array(
		                                'text' => '{{@lang.language.game_guide}}',
		                                'id'   => 'game_guide'
		                            ),

		                            array(
		                                'text' => '{{@lang.language.terms_and_conditions}}',
		                                'id'   => 'terms_and_conditions'
		                            ),

		                            array(
		                                'text' => '{{@lang.language.contact_us}}',
		                                'id'   => 'contact_us'
		                            )

		                        )
		                ),

				'detect_timeout'=>true,
				
				/*
			    |--------------------------------------------------------------------------
			    | plugin_cache
			    |--------------------------------------------------------------------------
			    |
			    | List which plugins should be cacheable and non_cacheable
			    |
			    */
				'plugin_cache' 	 => array(
					'enable'  => array(),
					'disable' => array('news','transactions','jackpot','lastresult','badge','banner')
				),

			    /*
			    |--------------------------------------------------------------------------
			    | chat
			    |--------------------------------------------------------------------------
			    |
			    | chatbox settings
			    |
			    */
				'chat' =>  array('type' => 'native')
			);