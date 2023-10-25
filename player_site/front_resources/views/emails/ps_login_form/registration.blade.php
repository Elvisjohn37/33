<!DOCTYPE html>
<html lang="{{{ Session::get('langID') }}}">
	<head>
		<meta charset="utf-8">
	</head>
	<body style="font-family: Tahoma">
		<div class="ps_modal in" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23); background-color: rgb(28,29,32); border: 1px solid rgb(43,46,46);">
			<div class="row">
				<div class="col-lg-12">
					<div class="ps_container" style="padding: 0 50px; font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">
						<p class="ps_header" style="font-size: 18px; color: rgb(135, 144, 147); letter-spacing: 2px; padding-bottom: 10px; text-transform: uppercase;">Selamat datang di {{{ Config::get("settings.PRODUCT_NAME") }}}.</p>
						<p class="ps_header" style="font-size: 18px; color: rgb(135, 144, 147); letter-spacing: 2px; padding-bottom: 10px; text-transform: uppercase;">KEPADA {{{ $firstName.' '.$lastName}}}, </p>
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Terima kasih atas kepercayaan Anda bergabung bersama kami di {{{ Config::get("settings.PRODUCT_NAME") }}}. </p>
					</div>
					
					{{--  Check if need to display username and password --}}
					@if(isset($isDisplayCredential) && $isDisplayCredential)
						<div class="ps_title_bar" style="background-color: rgb(23, 23, 25); color: #BBB; font-size: 13px; padding: 10px 50px;">Berikut kami lampirkan data account Anda di {{{ Config::get("settings.PRODUCT_NAME") }}} sebagai berikut dibawah ini :</div>
						<div class="ps_container" style="padding: 0 50px; font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">
							<ul>
								<li style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Username: {{{ $loginName }}}</li>
								<li style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Password: {{{ $password }}}</li>
							</ul>
						</div>
					@endif
					
					<div class="ps_container" style="padding: 0 50px; font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Jangan lupa mengubah password Anda setelah berhasil login untuk pertama kali.</p>
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Untuk dapat menggunakan platform {{{ Config::get("settings.PRODUCT_NAME") }}}, silahkan mengaktifkan account Anda dengan mengklik link dibawah ini. Bilamana browser internet Anda tidak dapat merespon secara langsung, silahkan copy dan paste URL dibawah ini kedalam halaman browser internet Anda.</p>
						<div class="ps_toggle" style="margin: 12px 0px; border-radius: 1px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24); transition: all .4s;">
							<a href="{{{ URL::to('activate').'/'.$code }}}" class="ps_btn_activation_link ps_toggle_header" style="text-decoration: none; color: #DDDDDD !important; font-size: 12px; padding: 8px 15px; color: #888; background-color: #1E1F24; display: block; letter-spacing: 2px; letter-spacing: 1px;">{{{ URL::to('activate').'/'.$code }}}</a>
						</div>
					</div>
					<div class="ps_container" style="padding: 0 50px; font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Untuk keterangan mengenai cara deposit & withdrawal, silahkan menghubungi customer service kami via Live Chat di website {{{ URL::to('/') }}}</p><br />
					</div>
					<div class="ps_title_bar" style="background-color: rgb(23, 23, 25); color: #BBB; font-size: 13px; padding: 10px 50px;">
						<p style="font-size: 13px; color: #BBB; letter-spacing: .5px;">Salam {{{ Config::get("settings.PRODUCT_NAME") }}},</p> <br /><br />
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Email: support@338A.com</p>
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">No. Hp: +63 977 320 1971</p>
						<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Live Chat: {{{ URL::to('/') }}}</p>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
