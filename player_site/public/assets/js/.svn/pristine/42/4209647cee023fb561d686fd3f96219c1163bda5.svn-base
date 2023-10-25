/**
 * Tournament page
 *
 * @author PS Team
 */

define('ps_tournament', ['ps_view', 'ps_store', 'ps_model'], function(ps_view, ps_store, ps_model){

	var globals   = {is_page_rendered: false, store: new ps_store('ps_tournament')};
	var callables = {
		page_info: function() {
			if (!globals.store.store_exists('info')) {
				globals.store.store_update('info', {
					is_loading: true,
					phase_loading: true,
					phase_requested: {}
				});
			}
			return globals.store.store_fetch('info');
		},

		load_pagess: function() {
			var vm = this;
			ps_model.view_data({
				success: function() {

						//stop here create tournament_details ajax 
						//check if data success

						ps_model.tournament_details({
							success: function(response) {

								vm.tournament = response;

								for (var i = 0; i < response.phases.length; i++) { 
									var phase = response.phases[i];

									if (phase.isActive) {



									}
								}
							},
							complete: function() {

								
								vm.$nextTick(function(){
									
									$(vm.$el).find('.ps_tournament-phase_no').on('click', function(){

										callables.phase_rank($(this).attr('data-pid'));

									});
								
								});
								

							}
						});

					// mark page as rendered
					ps_model.update_page_rendered(vm.hash_info.page);
					vm.is_init = true;
				}
			});
		},

		get_phase_ranks: function(phaseNo,callback ) {

			ps_model.get_phase_ranks (phaseNo,{
				success: callback
			});


		}
	};

	return {
		activate: function(hash, hash_info) {
			var page_info = callables.page_info();
			if (!globals.is_page_rendered) {
				
				globals.is_page_rendered = true;
				ps_view.render($('.ps_js-page_'+hash_info.page), 'tournament',{
					replace : false,
					data    : {
								hash_info   : hash_info,
								page_info   : page_info,
								is_init     : false,
								tournament  : {},
								phase_rank  : {},
								active_phase: null,
								selected_phase: null,
								pending_rank: null,
								phases_lengths: 0
					},
					computed: {
						phases_length: function() {
							var vm = this;

							return vm.tournament[vm.selected_phase].data.length;
						},
						pending_phase: function() {
							var vm = this;
							if ( vm.pending_rank && vm.selected_phase ) {

								return vm.pending_rank['phaseNo'] != vm.selected_phase;
							}
							return true;
						},
						display_date: function() {
							var vm = this;
							
							if (vm.is_init) {
								return vm.tournament[vm.selected_phase].displayDate;
							}
						},
						phase_status: function() {
							return 'phase_status';
						}
					},
					watch: {
						selected_phase: function() {
							var vm = this;
							//active/pending/disabled
							callables.get_phase_ranks(vm.selected_phase);

						}
					},
					mounted: function() {
						var vm =  this;

						ps_model.view_data({
							success: function() {

									//stop here create tournament_details ajax 
									//check if data success
									ps_model.tournament_details({
										success: function(response) {

											vm.tournament = response;
											for (var i = 1; i <= response.length; i++) { 
												var phase = response[i];
												// phase.disabled = false;
												if (phase.isActive == 1) {
													if (vm.selected_phase ==  null) {
														vm.selected_phase = phase.phaseNo;

														callables.get_phase_ranks(
															phase.phaseNo,
															function(response){

																// response
																// vm.phase_rank = response[vm.selected_phase].data;

															}
														);
													} else {
														vm.pending_rank = phase;
													}
												}

												if (phase.isActive == 2) {
													if (vm.pending_rank == null) {
														vm.pending_rank = phase;
													} else {
														phase.disabled = true;
													} 
												}
											}
											
											ps_model.update_page_rendered(vm.hash_info.page);
											vm.is_init = true;

											vm.$nextTick(function(){
			
												$(vm.$el).find('.ps_tournament-phase_no').on('click', function(){
													vm.selected_phase = $(this).attr('data-pid');
												});
											});
										}
									});

								// mark page as rendered
							}
						}, ['user']);
					}

				});
			}
		}
	};
});