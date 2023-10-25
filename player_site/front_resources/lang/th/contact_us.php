<?php  
	ob_start();
?>
<p>Jika Anda memiliki pertanyaan, masalah atau saran tentang bagaimana kita bisa menjadi lebih baik maka silakan hubungi kami. Email dan telepon dukungan 338A buka 24/7.</p>
<p><b>Layanan Telepon</b><br>Informasi &amp; Layanan Pelanggan : +63 977 320 1971<br>Afiliasi (untuk daerah Poipet) : +855 719 986 555, +66 86 343 2820</p>
<p><b>Layanan Email</b><br>Silahkan kirim email ke <a href="mailto:<?php echo Config::get("settings.EMAIL_SENDER")  ?>"><?php echo Config::get("settings.EMAIL_SENDER")  ?></a>. Jika Anda memiliki pertanyaan terkait dengan transaksi rekening, atau Anda ingin membuat perubahan ke account 338A Anda, silahkan login sebelum mengirim permintaan Anda. Ini adalah untuk tujuan keamanan dan juga memastikan bahwa permintaan Anda ditangani secara efektif dan segera.</p>
<?php
	return array("content"=>ob_get_clean());
?>