if (typeof jQuery == 'undefined') {
	throw new Error('jQuery is required.');
}

+function ($) {

	var popup = undefined;


	// Mobile navigation menu toggle
	$('.mobile-nav').on('click', function (e) {

		e.preventDefault();

		$('.navigation').stop().slideToggle(300);

	});

	// Option links toggle
	$('.option-link').on('click', function (e) {

		e.preventDefault();

	    var url = $(this).data('url');

	    if (typeof url !== 'undefined' && url) {

	    	function __popup_open(link) {
	    		return window.open(
		            link, 
		            "same_window",
		            "width = 940, height = 620"
		        );
	    	}


	    	if (typeof popup == 'undefined' || popup.closed) {

		        popup = __popup_open(url);

	    	} else {

	    		popup.close();
	    		popup = __popup_open(url);
	    	}

	        popup.focus();
	    }

	});

	// Balance refresh animation
	window.__fn_animate_refresh = function () {
		var refresh_icon = $('.icon-refresh');

		if (typeof refresh_icon != 'undefined') {

			refresh_icon.addClass('full-spin').promise().done(function () {
		        setTimeout(function () {
		            refresh_icon.removeClass('full-spin')
		        }, 200);
		    });
		}
	};

	// Menu Translation
	var translation = {
		en: [
			'statement',
			'balance',
			'game guide',
			'help'
		],
		id: [
			'laporan',
			'saldo',
			'panduan permainan',
			'bantuan'
		],
		zh: [
			'声明',
			'平衡',
			'游戏指南',
			'救命'
		]
	};

	var check_language = $('#ann');

	if (check_language.length) {

		var get_language = check_language.attr('language');

		if (typeof get_language != 'undefined') {

			$('.option-link').each(function (i, v) {

				if (typeof translation[get_language] != 'undefined') {

					var get_translation = translation[get_language],
						get_wordings = (get_translation[i]).toUpperCase();

					$(v).html(get_wordings.toString());

				}
			});
		}
	}


	/**
	 * Popup window Date Time
	 */
	$.fn.clock = function () {

		return this.each(function (i, dom) {

			var obj = $(dom);

			var clock_time = new Date();

	        var clock_day = clock_time.getDate();
	        var clock_month = clock_time.getMonth();
	        var clock_year = clock_time.getFullYear();
	        var clock_hours = clock_time.getHours();
	        var clock_minutes = clock_time.getMinutes();
	        var clock_seconds = clock_time.getSeconds();
	        var clock_GMT = clock_time.getTimezoneOffset();
	        var clock_suffix = "";

	        if (clock_hours<10) {
	            clock_hours = '0' + clock_hours;	
	        }

	        if (clock_minutes<10) {
	            clock_minutes = '0' + clock_minutes;
	        }

	        if (clock_seconds<10) {
	            clock_seconds = '0' + clock_seconds;
	        }

	        if (clock_GMT<0 ) {
	            var sign = '+'; 
	            var gmt = -(clock_GMT/60);
	        } else { 
	            var sign = '-'; 
	            var gmt = (clock_GMT/60);
	        }

	        $(dom).html(clock_month+1 + "/" + clock_day + "/" + clock_year + " " + clock_hours + ":" + clock_minutes+' GMT '+ sign + gmt);

		});
	}

	$('#datetime').clock();

}(jQuery);