<!DOCTYPE html>
<html>
<head>
	<title>{!! Config::get("settings.PRODUCT_NAME") !!} Landing Page</title>

	<link rel="shortcut icon" href="<?php echo asset('assets/images/favicon/favicon32.png'); ?>">
	<link rel="apple-touch-icon image_src" href="<?php echo asset('assets/images/favicon/favicon64.png'); ?>">

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">

	<link rel="stylesheet" type="text/css" href="<?php echo asset('assets/css/landing.css') ?>">
	<script type="text/javascript" src="<?php echo asset('assets/js/jquery-1.11.0.min.js') ?>"></script>
	<script type="text/javascript">
	$(function() {
		var currentDate = new Date();
		
		// For current date
		Date.prototype.today = function () { 
			return ((this.getDate() < 10)?"0":"") + this.getDate() +"/"+(((this.getMonth()+1) < 10)?"0":"") + (this.getMonth()+1) +"/"+ this.getFullYear();
		}
		// For current time
		Date.prototype.timeNow = function () {
			return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
		}

		var dateInst = new Date();
		var current = dateInst.today() + " " + dateInst.timeNow();
		var ended = new Date('2015-08-15 12:00:00');

		var delta = Math.abs(ended.getTime() - dateInst.getTime()) / 1000;

		// calculate (and subtract) whole days
		var days = Math.floor(delta / 86400);
		delta -= days * 86400;

		// calculate (and subtract) whole hours
		var hours = Math.floor(delta / 3600) % 24;
		delta -= hours * 3600;

		// calculate (and subtract) whole minutes
		var minutes = Math.floor(delta / 60) % 60;
		delta -= minutes * 60;

		// what's left is seconds
		var seconds = Math.floor(delta) % 60;

		days = leading_zero(days);
		hours = leading_zero(hours);
		minutes = leading_zero(minutes);
		seconds = leading_zero(seconds);

		console.log(days+':'+hours+':'+minutes+':'+seconds);
		for(i=parseInt(seconds.substring(0,1));i>=0;i--) {
			$('.ps_cards.sec.first[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(seconds.substring(1,2));i>=0;i--) {
			$('.ps_cards.sec.second[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(minutes.substring(0,1));i>=0;i--) {
			$('.ps_cards.min.first[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(minutes.substring(1,2));i>=0;i--) {
			$('.ps_cards.min.second[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(hours.substring(0,1));i>=0;i--) {
			$('.ps_cards.hr.first[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(hours.substring(1,2));i>=0;i--) {
			$('.ps_cards.hr.second[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(days.substring(0,1));i>=0;i--) {
			$('.ps_cards.day.first[data-number="'+i+'"]').removeClass('out');
		}
		for(i=parseInt(days.substring(1,2));i>=0;i--) {
			$('.ps_cards.day.second[data-number="'+i+'"]').removeClass('out');
		}


		$('.ps_cards.sec.first[data-number="'+seconds.substring(0,1)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.sec.second[data-number="'+seconds.substring(1,2)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.min.first[data-number="'+minutes.substring(0,1)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.min.second[data-number="'+minutes.substring(1,2)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.hr.first[data-number="'+hours.substring(0,1)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.hr.second[data-number="'+hours.substring(1,2)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.day.first[data-number="'+days.substring(0,1)+'"]').removeClass('out').addClass('front');
		$('.ps_cards.day.second[data-number="'+days.substring(1,2)+'"]').removeClass('out').addClass('front');


		function leading_zero(num)
		{
			if(parseInt(num) < 10) {
				return '0'+num;
			}
			return ''+num;
		}


		var cs1n = $('.ps_cards.sec.first.front').attr('data-number');
		var cs2n = $('.ps_cards.sec.second.front').attr('data-number');

		var cm1n = $('.ps_cards.min.first.front').attr('data-number');
		var cm2n = $('.ps_cards.min.second.front').attr('data-number');

		var ch1n = $('.ps_cards.hr.first.front').attr('data-number');
		var ch2n = $('.ps_cards.hr.second.front').attr('data-number');

		var cd1n = $('.ps_cards.day.first.front').attr('data-number');
		var cd2n = $('.ps_cards.day.second.front').attr('data-number');

		window.setInterval(function() {
			console.log(cd1n+''+cd2n+' : '+ch1n+''+ch2n+' : '+cm1n+''+cm2n+' : '+cs1n+''+cs2n);
			$('.ps_cards.sec.second[data-number="'+cs2n+'"]').addClass('out');
			cs2n = cs2n - 1;
			if(cs2n < 0) {
				$('.ps_cards.sec.second').removeClass('out');
				cs2n = 9;
				$('.ps_cards.sec.first[data-number="'+cs1n+'"]').addClass('out');
				cs1n = cs1n - 1;

				if(cs1n < 0) {
					$('.ps_cards.sec.first').removeClass('out');
					cs1n = 5;

					$('.ps_cards.min.second[data-number="'+cm2n+'"]').addClass('out');
					cm2n = cm2n - 1;

					if(cm2n < 0) {
						$('.ps_cards.min.second').removeClass('out');
						cm2n = 9;
						$('.ps_cards.min.first[data-number="'+cm1n+'"]').addClass('out');
						cm1n = cm1n - 1;

						if(cm1n < 0) {
							$('.ps_cards.min.first').removeClass('out');
							cm1n = 5;

							$('.ps_cards.hr.second[data-number="'+ch2n+'"]').addClass('out');
							ch2n = ch2n - 1;

							if(ch2n < 0) {
								$('.ps_cards.hr.second').removeClass('out');
								ch2n = 9;
								$('.ps_cards.hr.first[data-number="'+ch1n+'"]').addClass('out');
								ch1n = ch1n - 1;

								if(ch1n < 0) { 
									$('.ps_cards.day.second[data-number="'+cd2n+'"]').addClass('out');
									cd2n = cd2n - 1;

									ch1n = 2;
									ch2n = 3;

									$('.ps_cards.hr.first').addClass('out');
									$('.ps_cards.hr.second').addClass('out');

									$('.ps_cards.hr.first[data-number="'+ch1n+'"]').removeClass('out');
									$('.ps_cards.hr.second[data-number="'+ch2n+'"]').removeClass('out');
								}
							}

						}
					}
				}
			}
		}, 1000);

		$('.ps_more, .ps_showcase_backdrop').on('click', function() {
			$('.ps_container').toggleClass('inactive');
			$('.ps_showcase').toggleClass('active');
		});
		$('.ps_nav').on('click', function() {
			$('.ps_nav').removeClass('active');
			$(this).addClass('active');
			var dir = $(this).attr('data-direction');
			$('.ps_showcase_container').removeClass('left center right');
			$('.ps_showcase_container').addClass(dir);
		});
	});
	</script>
	<!--script type="text/javascript">
		var __lc = {};
		__lc.license = 4721851;
		__lc.group = 5;

		(function() {
		 var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
		 lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
		 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
		})();
	</script-->
</head>
<body class="ps_landing">
	<div class="ps_red_bg">
		<div class="ps_shadow_overlay">
			<div class="ps_container">
				<p class="ps_rebirth">REBIRTH OF A LEGEND</p>
				<img src="<?php echo asset('assets/images/logo.png') ?>" />
				
				<div class="ps_timer">
				<?php
					$e_date = strtotime('2015-07-28 15:17:00');

					$c_date = new DateTime(date('Y-m-d H:i:s'));
					$e_date = new DateTime(date('Y-m-d H:I:s', $e_date));

					$days = $e_date->diff($c_date)->format("%d");
					$hours =  $e_date->diff($c_date)->format("%H");
					$minutes =  $e_date->diff($c_date)->format("%i");
					$seconds =  $e_date->diff($c_date)->format("%s");

					$d1 = substr($days, 0, 1);
					$d2 = substr($days, 1, 1);

					$h1 = substr($hours, 0, 1);
					$h2 = substr($hours, 1, 1);

					$m1 = substr($minutes, 0, 1);
					$m2 = substr($minutes, 1, 1);

					$s1 = substr($seconds, 0, 1);
					$s2 = substr($seconds, 1, 1);

					$time_config = array(
								'day' => array(
										'digit1' => 3,
										'digit2' => 9,
										'current1' => $d1,
										'current2' => $d2
									),
								'hr' => array(
										'digit1' => 2,
										'digit2' => 9,
										'current1' => $h1,
										'current2' => $h2
									),
								'min' => array(
										'digit1' => 5,
										'digit2' => 9,
										'current1' => $m1,
										'current2' => $m2
									),
								'sec' => array(
										'digit1' => 5,
										'digit2' => 9,
										'current1' => $s1,
										'current2' => $s2
									),
							);

					foreach($time_config as $key => $value) {
						echo '<div class="ps_digit_container">';
						for($i = 0; $i <=$value['digit1']; $i++) {
							echo '<span id="" class="ps_cards '.$key.' first back out" data-number="'.$i.'"></span>';
						}
						for($i = 0; $i <=$value['digit2']; $i++) {
							echo '<span id="" class="ps_cards '.$key.' second back out" data-number="'.$i.'"></span>';
						}
						echo '</div>';
					}

				?>
				</div>
				<div class="ps_countdown_line"></div>
				<div class="ps_countdown_legend">
					<div class="ps_legend">
						<span class="ps_label">Days</span>
						<span class="ps_indicator"></span>
					</div>
					<div class="ps_legend">
						<span class="ps_label">Hours</span>
						<span class="ps_indicator"></span>
					</div>
					<div class="ps_legend">
						<span class="ps_label">Minutes</span>
						<span class="ps_indicator"></span>
					</div>
					<div class="ps_legend">
						<span class="ps_label">Seconds</span>
						<span class="ps_indicator"></span>
					</div>
				</div>
				<a class="ps_more">FIND OUT MORE</a>
			</div>
		</div>
		<a class="ps_sbo_logo" href="https://games1.asapez.com" target="_blank"><img src="<?php echo asset('assets/images/SBO_a_1-01.png') ?>" /></a>
	</div>
	<div class="ps_showcase">
		<div class="ps_showcase_backdrop"></div>
		<div class="ps_showcase_container">
			<div class="ps_item">
				<div class="ps_item_container">
					<img src="<?php echo asset('assets/images/skill_games.jpg') ?>" />
					<p class="ps_item_title">skill_games</p>
					<p class="ps_item_description">skill_games {!! Config::get("settings.PRODUCT_NAME") !!} offers modern and interactive user interface allow you to play Texas Holdem skill_games in multiple table and allow you to win unlimited royal jackpot prizes.</p>
				</div>
			</div>
			<div class="ps_item">
				<div class="ps_item_container">
					<img src="<?php echo asset('assets/images/tangkas.jpg') ?>" />
					<p class="ps_item_title">Tangkas</p>
					<p class="ps_item_description">With intuitive user interface and gaming experience, TANGKAS {!! Config::get("settings.PRODUCT_NAME") !!} allows you to play at anywhere and anytime in any device.</p>
				</div>
			</div>
			<div class="ps_item">
				<div class="ps_item_container">
					<img src="<?php echo asset('assets/images/games.jpg') ?>" />
					<p class="ps_item_title">Games</p>
					<p class="ps_item_description">Superior graphics, animations and sound all contribute to the excitement of {!! Config::get("settings.PRODUCT_NAME") !!} GAMES. </p>
				</div>
			</div>
		</div>
		<div class="ps_nav_indicator">
			<a class="ps_nav" data-direction="left"></a>
			<a class="ps_nav active" data-direction="center"></a>
			<a class="ps_nav" data-direction="right"></a>
		</div>
	</div>
</body>
</html>