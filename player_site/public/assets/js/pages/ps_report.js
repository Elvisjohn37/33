/**
 * Report page module handler
 * 
 * @author PS Team
 */
define('ps_report', ['ps_store','ps_view','ps_model','ps_helper','ps_window','ps_popup'], function() {

    var ps_store  = arguments[0];
    var ps_view   = arguments[1];
    var ps_model  = arguments[2];
    var ps_helper = arguments[3];
    var ps_window = arguments[4];
    var ps_popup  = arguments[5];

	var globals   = { is_page_rendered: false, store: new ps_store('ps_report') };
	var callables = {
		/**
		 * Get/Create report page store
		 * @return object
		 */
		report_page_info: function() {
			if (!globals.store.store_exists('info')) {
				globals.store.store_update('info', {
					loaded_hash             : [],

					statement_selected     : null,
					statement_loading      : true,
					statement_row          : null,
					statement_refresh      : true,

					transfer_loading       : true,
					transfer_page          : null,
					transfer_subtype       : null, // no subtype

					betting_loading        : true,
					betting_page           : null,
					betting_subtype        : null, // [Promotion]

					credit_loading         : true,
					credit_page            : null,
					credit_subtype         : null, // no subtype

					running_bets_loading   : true,
					running_bets_page      : null,

					transaction_logs_loading: true,
					transaction_logs_page   : null,
					transaction_logs_form   : {},
					transaction_logs_prev   : true,
					transaction_logs_next   : true
				});
			}

			return globals.store.store_fetch('info');
		},

		/**
		 * This will get all needed data for report page to initiate
		 * Triggered in report page mounted event
		 * @return void
		 */
		page_init: function() {
			var vm = this;

			ps_model.view_data({
				success: function(response) {

							// resize
							if (response.route.view_type == 'ingame') {
					            var width  = $(vm.$el).attr('data-ingame-width'); 
					            var height = $(vm.$el).attr('data-ingame-height'); 
					            window.resizeTo(width, height);
							}

							vm.view_data = response;

							// mark page as rendered
							ps_model.update_page_rendered(vm.hash_info.page);
							vm.is_init = true;
						}
			},['navigation','report','route']);
		},

		/**
		 * This will trigger when page active hash changed
		 * Triggered in report watch event
		 * @param  string new_hash
		 * @param  string old_hash
		 * @return void
		 */
		active_hash_change: function(new_hash, old_hash) {
			var vm           = this;
			var is_recent    = !ps_helper.in_array(new_hash, vm.page_info.loaded_hash);
			var init_handler = function() {
								// old deactivate
								var old_handlers_key = ps_helper.replace_all(old_hash,'#','');
								var old_handlers     = callables[old_handlers_key];
								if ($.isPlainObject(old_handlers)) {
									if ($.isFunction(old_handlers.deactivate)) {
										old_handlers.deactivate.call(vm);
									}
								}

								// new activate
								var new_handlers_key = ps_helper.replace_all(new_hash,'#','');
								var new_handlers     = callables[new_handlers_key];
								if ($.isPlainObject(new_handlers)) {
									if ($.isFunction(new_handlers.activate)) {
										new_handlers.activate.call(vm,is_recent);
									}
								}
							};

			if (is_recent) {
				globals.store.store_list_push('info','loaded_hash', new_hash);
				vm.$nextTick(function() {
					init_handler();
				});
			} else {
				init_handler();
			}
		},

		/**
         |--------------------------------------------------------------------------------------------------------------
         | STATEMENT
         |--------------------------------------------------------------------------------------------------------------
         */
		statement: {
			/**
			 * Activate statement page
			 * @param  boolean is_recent
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;

				if (is_recent) {
					// statement date change options
					// There are 2 ways to implement date dropdown in statement page
					// 1. Use 'ps_js-statement_dates' to any element and assign 'data-number' attribute, 
					// this will trigger onChange
					// 2. Use 'ps_js-statement_input' to any element with onchange event supported e.g. dropdown.
					$(vm.$el).on('click', '.ps_js-statement_dates' , function() {
	    				callables.statement.load(vm, parseInt($(this).attr('data-number')));
					});
					$(vm.$el).on('change', '.ps_js-statement_input' , function() {
	    				callables.statement.load(vm, parseInt($(this).val()));
					});

					$(vm.$el).on('click', '.ps_js-statement_drill_down' , function() {
						// statement date link
						globals.store.store_update('info', 'statement_row', parseInt($(this).attr('data-row')));
					});
				}

				if (vm.page_info.statement_refresh) {
					callables.statement.load(vm, 1);
				} else {
					globals.store.store_update('info', 'statement_refresh', true);
				}
			},

			/**
			 * this will load statement report data
			 * @param  object vm          
			 * @param  int    month_number  
			 * @return void
			 */
			load: function(vm, month_number) {
				globals.store.store_update('info',{ statement_selected: month_number, statement_loading : true});

				ps_model.get_statement(month_number, {
					success : function(response) {
								vm.statement = response;
							},
					complete: function() {
								globals.store.store_update('info',{ statement_loading: false });
							}
				});
			}
		},
		
		/**
         |--------------------------------------------------------------------------------------------------------------
         | Transfer details
         |--------------------------------------------------------------------------------------------------------------
         */
		statement_transfer_details: {
			/**
			 * Activate transfer details page
			 * @param  boolean is_recent
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				if (is_recent) {
					
					// paging
					$(vm.$el).on('view_components_paging', '.ps_js-transfer_table', function(e, number) {
						globals.store.store_update('info', 'transfer_page', number);
						callables.statement_details(vm, 'Transfer');
					});

					// refresh
					$(vm.$el).find('.ps_js-transfer_refresh').on('click', function() {
						globals.store.store_update('info', 'transfer_page', 1);
						callables.statement_details(vm, 'Transfer');
					});

					// back
					var parent = ps_helper.array_last(vm.view_data.navigation.hashes[vm.active_main_hash].parents);
					$(vm.$el).find('.ps_js-statement_back').on('click', function() {
						globals.store.store_update('info', 'statement_refresh', false);
						window.location = parent.hash;
					});
				}

				if (!ps_helper.empty(vm.page_info.statement_row)) {
					var row_details = vm.statement.rows[vm.page_info.statement_row];

					if (row_details.type==='Transfer') {
						globals.store.store_update('info', {
							transfer_transactionID: row_details.transactionID,
							transfer_date         : row_details.date,
							// Transfer has no subtype, therefore this is always Transfer
							transfer_subtype      : row_details.type,
							transfer_page         : 1
						});

						callables.statement_details(vm, row_details.type);
					}
				}

				// if no transactionID fetched then redirect back to parent hash
				if (ps_helper.empty(vm.page_info.transfer_transactionID)) {
					var parent      = ps_helper.array_last(vm.view_data.navigation.hashes[vm.active_main_hash].parents);
					window.location = parent.hash;
				}
			}
		},

		
		/**
         |--------------------------------------------------------------------------------------------------------------
         | Credit details
         |--------------------------------------------------------------------------------------------------------------
         */
		statement_credit_details: {
			/**
			 * Activate credit details page
			 * @param  boolean is_recent
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				if (is_recent) {
					// paging
					$(vm.$el).on('view_components_paging', '.ps_js-credit_table', function(e, number) {
						globals.store.store_update('info', 'credit_page', number);
						callables.statement_details(vm, 'Credit');
					});

					// refresh
					$(vm.$el).find('.ps_js-credit_refresh').on('click', function() {
						globals.store.store_update('info', 'credit_page', 1);
						callables.statement_details(vm, 'Credit');
					});

					// back
					var parent = ps_helper.array_last(vm.view_data.navigation.hashes[vm.active_main_hash].parents);
					$(vm.$el).find('.ps_js-statement_back').on('click', function() {
						globals.store.store_update('info', 'statement_refresh', false);
						window.location = parent.hash;
					});
				}

				if (!ps_helper.empty(vm.page_info.statement_row)) {
					var row_details = vm.statement.rows[vm.page_info.statement_row];

					if (row_details.type==='Credit') {
						globals.store.store_update('info', {
							credit_transactionID: row_details.transactionID,
							credit_date         : row_details.date,
							// Credit has no subtype, therefore this is always Credit
							credit_subtype      : row_details.type,
							credit_page         : 1
						});

						callables.statement_details(vm, row_details.type);
					}
				}

				// if no transactionID fetched then redirect back to parent hash
				if (ps_helper.empty(vm.page_info.credit_transactionID)) {
					var parent      = ps_helper.array_last(vm.view_data.navigation.hashes[vm.active_main_hash].parents);
					window.location = parent.hash;
				}
			}
		},
		
		/**
         |--------------------------------------------------------------------------------------------------------------
         | betting details
         |--------------------------------------------------------------------------------------------------------------
         */
		statement_betting_details: {
			/**
			 * Activate betting details page
			 * Other types that are not Transfer and Creadits are considered as betting type (e.g Promotions)
			 * @param  boolean is_recent
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;
				if (is_recent) {

					// paging
					$(vm.$el).on('view_components_paging', '.ps_js-betting_table', function(e, number) {
						globals.store.store_update('info', 'betting_page', number);
						callables.statement_details(vm, 'Betting');
					});

					// betting links
					$(vm.$el).on('view_components_loaded', '.ps_js-betting_table' , function() {
						callables.render_bet_links(vm.betting.rows);
					});

					// refresh
					$(vm.$el).find('.ps_js-betting_refresh').on('click', function() {
						globals.store.store_update('info', 'betting_page', 1);
						callables.statement_details(vm, 'Betting');
					});

					// back
					var parent = ps_helper.array_last(vm.view_data.navigation.hashes[vm.active_main_hash].parents);
					$(vm.$el).find('.ps_js-statement_back').on('click', function() {
						globals.store.store_update('info', 'statement_refresh', false);
						window.location = parent.hash;
					});
				}

				if (!ps_helper.empty(vm.page_info.statement_row)) {
					var row_details = vm.statement.rows[vm.page_info.statement_row];

					// Betting has sub type like Promotion thats why we dont filter as Betting only
					if (row_details.type!='Transfer' && row_details.type!='Credit') {
						globals.store.store_update('info', {
							betting_transactionID: row_details.transactionID,
							betting_subtype      : row_details.type,
							betting_product      : row_details.product,
							betting_page         : 1
						});

						callables.statement_details(vm, 'Betting');
					}
				}

				// if no transactionID fetched then redirect back to parent hash
				if (ps_helper.empty(vm.page_info.betting_transactionID)) {
					var parent      = ps_helper.array_last(vm.view_data.navigation.hashes[vm.active_main_hash].parents);
					window.location = parent.hash;
				}
			}
		},

		/**
         |--------------------------------------------------------------------------------------------------------------
         | RUNNING BETS
         |--------------------------------------------------------------------------------------------------------------
         */
		running_bets: {
			/**
			 * Activate running bets page
			 * @param  boolean is_recent
			 * @return void
			 */
			activate: function(is_recent) {
				var vm = this;

				if (is_recent) {
					// paging
					$(vm.$el).on('view_components_paging', '.ps_js-running_bets_table', function(e, number) {
						callables.running_bets.load(vm, number);
					});

					// betting links
					$(vm.$el).on('view_components_loaded', '.ps_js-running_bets_table', function() {
						callables.render_bet_links(vm.running_bets.rows);
					});

					// refresh
					$(vm.$el).find('.ps_js-running_bets_refresh').on('click', function() {
						callables.running_bets.load(vm, 1);
					});
				}

				callables.running_bets.load(vm, 1);
			},

			/**
			 * This will load running bets data
			 * @param  object vm   
			 * @param  int    page 
			 * @return void
			 */
			load: function(vm, page) {
				globals.store.store_update('info',{ running_bets_page: page, running_bets_loading : true});
				ps_model.get_running_bets(page, {
					success: function(response) {
								vm.running_bets = response;
							},
					complete: function() {
								globals.store.store_update('info',{ running_bets_loading: false });
							}
				});
			}
		},

		/**
		 * This will assume the table is already rendered the it will search all betLinkIDs and render bet link dom
		 * @param  array rows 
		 * @return void
		 */
		render_bet_links: (function() {
			var last_transactionID = null;

            return ps_window.new_instance(function(window_instance) {
            	
				return function(rows) {
					rows.forEach(function(row) {
						if (row.hasBetLink) {
							ps_view.render($('#'+row.betLinkID), 'report_bet_link', {
								data   : {  transDetID: row.transDetID  },
								mounted: function() {
											var vm = this;

											$(vm.$el).on('click', function() {

		                                        ps_popup.toast.open(ps_language.get('messages.loading_message'), {
		                                            title: ps_language.get('messages.opening_bet_details'),
		                                            type : 'schedule',
		                                            id   : 'bet_details'
		                                        });

                    							window_instance.open('', 'width=800, height=717');
                    							last_transactionID = vm.transDetID;

												ps_model.bet_details(vm.transDetID, {
													success: function(response) {
																if (vm.transDetID === last_transactionID) {

																	if ($.isPlainObject(response.window_size)) {
																		window_instance.resize(
																			response.window_size.width,
																			response.window_size.height
																		);
																	}

                        											window_instance.redirect(response.url);

																}
															},
													fail    : function() {
																if (vm.transDetID === last_transactionID) {
                        											window_instance.close();
																}
															},
													error   : function() {
																if (vm.transDetID === last_transactionID) {
                        											window_instance.close();
																}
															},
		                                            complete: function() {
																if (vm.transDetID === last_transactionID) {
		                                                        	ps_popup.toast.close('bet_details');
		                                                        }
		                                                    }
												});
											});
										}
							});
						}
					});
				};

			});

		}()),

		/**
		 * Used for getting data of statement details child pages
		 * @param  object vm       
		 * @param  string type 
		 * @return void
		 */
		statement_details: function(vm, type) {
			var formatted_type = type.toLowerCase();
			globals.store.store_update('info', formatted_type + '_loading', true);
			ps_model.get_statement_details(
				type, 
				{ 
					transactionID: vm.page_info[formatted_type+'_transactionID'], 
					page         : vm.page_info[formatted_type+'_page'], 
					subtype      : vm.page_info[formatted_type+'_subtype']
				}, 
				{
					success: function(response) {
								vm[formatted_type] = response;
							},
					complete: function() {
								globals.store.store_update('info', formatted_type + '_loading', false);
							}
				}
			);
		},

		/**
         |--------------------------------------------------------------------------------------------------------------
         | TRANSACTION LOGS
         |--------------------------------------------------------------------------------------------------------------
         */
		transaction_logs: {
			/**
			 * Activate transaction logs page
			 * @param  boolean is_recent
			 * @return void
			 */
			activate: function(is_recent) {
				var vm                    = this;
				var transaction_logs_form = $(vm.$el).find('.ps_js-transaction_logs_form');
				var transaction_logs_date = $(vm.$el).find('.ps_js-transaction_logs_date');

				if (is_recent) {
					var transaction_logs_table = $(vm.$el).find('.ps_js-transaction_logs_table');

					// paging
					transaction_logs_table.on('view_components_paging', function(e, number) {
						transaction_logs_form.trigger('submit', number);
					});

					// betting links
					transaction_logs_table.on('view_components_loaded', function() {
						callables.render_bet_links(vm.transaction_logs.rows);
					});

					transaction_logs_form.on('submit', function(e, page) {
						e.preventDefault();
						page = page || 1;
						globals.store.store_update('info',{ 
							transaction_logs_form: ps_helper.json_serialize(transaction_logs_form)
						});
						callables.transaction_logs.load(vm, page);
					});

					$(vm.$el).find('.ps_js-transaction_logs_previous').on('click', function() {
						transaction_logs_date.one('view_components_change', function(e, date) {
							transaction_logs_form.trigger('submit');
						});
						transaction_logs_date.trigger('view_components_previous','hour');
					});
					$(vm.$el).find('.ps_js-transaction_logs_next').on('click', function() {
						transaction_logs_date.one('view_components_change', function(e, date) {
							transaction_logs_form.trigger('submit');
						});
						transaction_logs_date.trigger('view_components_next','hour');
					});

					transaction_logs_date.on('view_components_available', function(e, details) {
						if (details.type == 'hour' && details.arrow == 'prev') {
							globals.store.store_update('info','transaction_logs_prev', details.available);
						}

						if (details.type == 'hour' && details.arrow == 'next') {
							globals.store.store_update('info','transaction_logs_next', details.available);
						}
					});

					transaction_logs_date.trigger('view_components_availability','hour');
				}

				// reset form and submit
				transaction_logs_form.trigger('view_components_fullreset');
				transaction_logs_date.one('view_components_change', function(e, date) {
					transaction_logs_form.trigger('submit');
				});
			},

			/**
			 * This will load transaction logs data
			 * @param  object vm   
			 * @param  int    page 
			 * @return void
			 */
			load: function(vm, page) {
				globals.store.store_update('info',{ transaction_logs_page: page, transaction_logs_loading : true});
				ps_model.get_transaction_logs(vm.page_info.transaction_logs_form, page, {
					success: function(response) {
								vm.transaction_logs = response;
							},
					complete: function() {
								globals.store.store_update('info','transaction_logs_loading', false);
							}
				});
			}
		},
	};

	return {
		/**
		 * Activate page
		 * @param  string hash     
		 * @param  object hash_info 
		 * @return void
		 */
		activate: function(hash, hash_info) {
			var page_info = callables.report_page_info();

			if (!globals.is_page_rendered) {

				globals.is_page_rendered = true;
				ps_view.render($('.ps_js-page_'+hash_info.page), 'report', {
					replace : false,
					data    : { 
								hash_info       : hash_info, 
								page_info       : page_info, 
								view_data       : {}, 
								is_init         : false,
								statement       : { rows:[], footer:{}, total:0 },
								transfer        : { rows:[], total :0 },
								betting         : { rows:[], total :0 },
								credit          : { rows:[], total :0 },
								running_bets    : { rows:[], total :0, total_running_bet: null },
								transaction_logs: { rows:[], total: 0, hasNext: false          }
							},
					computed: {
								page_navigation_data: function() {
														return this.view_data.navigation.pages[this.hash_info.page];
													},
								active_main_hash    : function() {
														var vm = this;
														if (!vm.is_init) {
															return '';
														} else {
															return vm.page_navigation_data.active_main_hash
														}
													},
	                            statement_length    : function() {
		                                                return this.statement.rows.length;
		                                            },
	                            transfer_length     : function() {
		                                                return this.transfer.rows.length;
		                                            },
	                            betting_length      : function() {
		                                                return this.betting.rows.length;
		                                            },
	                            credit_length       : function() {
		                                                return this.credit.rows.length;
		                                            }
		                                            ,
	                            running_bets_length : function() {
		                                                return this.running_bets.rows.length;
		                                            },
	                            transaction_logs_length : function() {
			                                                return this.transaction_logs.rows.length;
			                                            },
			                    transaction_final_prev  : function() {
			                    							var vm            = this;
			                    							var previous_hour = vm.page_info.transaction_logs_prev;
			                    							var is_loading    = vm.page_info.transaction_logs_loading;
			                    							return previous_hour && !is_loading;
			                    						},
			                    transaction_final_next  : function() {
			                    							var vm         = this;
			                    							var next_hour  = vm.page_info.transaction_logs_next;
			                    							var is_loading = vm.page_info.transaction_logs_loading;
			                    							return next_hour && !is_loading;
			                    						},
		                        enabled_dates      : function() {
		                        						var vm = this;

		                        						if ($.isPlainObject(vm.view_data.report)) {
		                        							var enabled_months  = {};
		                        							var enabled_years   = [];
		                        							var statement_dates = vm.view_data.report.statement_dates;
		                        							statement_dates.forEach(function(statement_date) {
		                        								var year  = parseInt(statement_date.year);
		                        								var month = parseInt(statement_date.month);

		                        								if (!$.isArray(enabled_months[year])) {
		                        									enabled_months[year] = [];
		                        								}

		                        								var is_month_pushed = ps_helper.in_array(
		                        														month,
		                        														enabled_months[year]
		                        													);
		                        								if (!is_month_pushed) {
		                        									enabled_months[year].push(month);
		                        								}

		                        								var is_year_pushed = ps_helper.in_array(
		                        														year,
		                        														enabled_years
		                        													);
		                        								if (!is_year_pushed) {
		                        									enabled_years.push(year);
		                        								}
		                        							});
															
															return { months:enabled_months, years:enabled_years };
		                        						}

		                        						return { months: null, years: null };
		                        					}
							},
					watch   : { active_main_hash: callables.active_hash_change }, 
					mounted : callables.page_init
				});
			}
		}
	};
});