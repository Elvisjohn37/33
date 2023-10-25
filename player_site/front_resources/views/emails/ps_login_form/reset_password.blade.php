<!DOCTYPE html>
<html lang="{{{ Session::get('langID') }}}">
	<head>
		<meta charset="utf-8">
	</head>
	<body style="font-family: Tahoma">
		<div class="ps_modal in" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23); background-color: rgb(28,29,32); border: 1px solid rgb(43,46,46);">
			<div class="row">
				@if(Session::get('langID') == 'en')
					<div class="col-lg-12">
						<div class="ps_container" style="padding: 0 50px; font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">
							<p class="ps_header" style="font-size: 18px; color: rgb(135, 144, 147); letter-spacing: 2px; padding-bottom: 10px; text-transform: uppercase;">DEAR  {{{ $firstName.' '.$lastName}}}, </p>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">We have received a request to reset your {{{ Config::get("settings.PRODUCT_NAME") }}} login password.</p>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Please click the link below to proceed with your reset password request.</p>
							<div class="ps_toggle" style="margin: 12px 0px; border-radius: 1px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24); transition: all .4s;">
								<a href="{{{ URL::to('reset').'/'.$code }}}" class="ps_btn_activation_link ps_toggle_header" style="text-decoration: none; color: #DDDDDD !important; font-size: 12px; padding: 8px 15px; color: #888; background-color: #1E1F24; display: block; letter-spacing: 2px; letter-spacing: 1px;">{{{ URL::to('reset').'/'.$code }}}</a>
							</div>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Please note that you will be prompted to change your password upon clicking the link above.</p>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">If you have received this email without requesting to reset your {{{ Config::get("settings.PRODUCT_NAME") }}} login password, please contact our dedicated {{{ Config::get("settings.PRODUCT_NAME") }}} Support Team at {!! Config::get("settings.EMAIL_SENDER") !!}.</p><br />
						</div>
						<div class="ps_title_bar" style="background-color: rgb(23, 23, 25); color: #BBB; font-size: 13px; padding: 10px 50px;">Best Regards, <br />{{{ Config::get("settings.PRODUCT_NAME") }}} Support Team</div>
					</div>
				@else
					<div class="col-lg-12">
						<div class="ps_container" style="padding: 0 50px; font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">
							<p class="ps_header" style="font-size: 18px; color: rgb(135, 144, 147); font-family: Tahoma; letter-spacing: 2px; padding-bottom: 10px; text-transform: uppercase;">KEPADA  {{{ $firstName.' '.$lastName}}}, </p>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Kami telah menerima pengajuan pengaturan ulang kata sandi {{{ Config::get("settings.PRODUCT_NAME") }}} Anda.</p>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Harap mengklik URL di bawah ini untuk memproses pengajuan pengaturan ulang kata sandi Anda.</p>
							<div class="ps_toggle" style="margin: 12px 0px; border-radius: 1px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24); transition: all .4s;">
								<a href="{{{ URL::to('reset').'/'.$code }}}" class="ps_btn_activation_link ps_toggle_header" style="text-decoration: none; color: #DDDDDD !important; font-size: 12px; padding: 8px 15px; color: #888; background-color: #1E1F24; display: block; letter-spacing: 2px; letter-spacing: 1px;">{{{ URL::to('reset').'/'.$code }}}</a>
							</div>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Harap diketahui, bahwa Anda akan diminta untuk mengganti kata sandi setelah mengklik URL di atas.</p>
							<p style="font-size: 13px; color: rgb(124,133,135); letter-spacing: .5px;">Bilamana Anda tidak merasa mengajukan permohonan pengaturan ulang kata sandi ini, harap menghubungi layanan pelanggan {{{ Config::get("settings.PRODUCT_NAME") }}} di {!! Config::get("settings.EMAIL_SENDER") !!}.</p><br />
						</div>
						<div class="ps_title_bar" style="background-color: rgb(23, 23, 25); color: #BBB; font-size: 13px; padding: 10px 50px;">Salam Hangat,<br />Layanan Pelanggan {{{ Config::get("settings.PRODUCT_NAME") }}}</div>
					</div>
				@endif
			</div>
		</div>
	</body>
</html>
