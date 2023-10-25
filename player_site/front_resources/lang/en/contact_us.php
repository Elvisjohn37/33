<?php  
	ob_start();
?>
<p>If you have any questions, problems or suggestions on how we can get better then please get in touch with us. 338A's email and telephone support is open 24/7.</p>
<p><b>Telephone Support</b><br>Inquiries &amp; Customer Support : +63 977 320 1971<br>Affiliates (for Poipet region) : +855 719 986 555, +66 86 343 2820</p>
<p><b>Mail Support</b><br>Please send us an email to <a href="mailto:<?php echo Config::get("settings.EMAIL_SENDER")  ?>"><?php echo Config::get("settings.EMAIL_SENDER")  ?></a>. If you have an inquiry related to any account transactions, or you wish to make any amendments to your 338A account, please log in before sending your request. This is for security purposes and also ensures that your query is dealt with effectively and promptly.</p>
<?php
	return array("content"=>ob_get_clean());
?>