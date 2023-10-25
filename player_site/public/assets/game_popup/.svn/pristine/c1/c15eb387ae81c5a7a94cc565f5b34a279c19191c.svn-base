if (typeof jQuery == 'undefined') {
	throw new Error('jQuery is required.');
}

+function ($) {
	'use strict';

	var report_dom = {
		title: 	$('.report-title'),
		main: $('.main-container'),
		sidebar: $('.left-container'),
		toggle: $('.toggle-info')
	};


	report_dom.main.addClass('main-container-mobile');
	
	var dom_toggle = [
		'<button class="toggle-info" id="toggle_info">',
			'>> Details',
		'</button>',

		report_dom.title.html()
	].join(' ');

	report_dom.title.addClass('report-title-mobile').html(dom_toggle);
	report_dom.sidebar.addClass('left-container-mobile');


	var toggle_dom = $('#toggle_info');

	toggle_dom.on('click', function (e) {
		e.preventDefault();

		$(this).toggleClass('button-active');
		
		report_dom.main.toggleClass('sidebar-active');
		report_dom.sidebar.toggleClass('toggle-active');

		if (report_dom.sidebar.hasClass('toggle-active')) {

			$(this).html('<< Details');
		
		} else {

			$(this).html('>> Details');
		}

	});


	// Show report sidebar toggle only
	// on mobile devices.
	if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		
		toggle_dom.hide();
	}
	
	toggle_dom.trigger('click');

}(jQuery);
