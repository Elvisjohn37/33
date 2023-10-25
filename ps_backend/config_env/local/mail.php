<?php

	/*
	|--------------------------------------------------------------------------
	| Mail Driver
	|--------------------------------------------------------------------------
	|
	| Laravel supports both SMTP and PHP's "mail" function as drivers for the
	| sending of e-mail. You may specify which one you're using throughout
	| your application here. By default, Laravel is setup for SMTP mail.
	|
	| Supported: "smtp", "mail", "sendmail"
	|
	*/

	$config['driver'] = 'smtp';

	/*
	|--------------------------------------------------------------------------
	| SMTP Host Port
	|--------------------------------------------------------------------------
	|
	| This is the SMTP port used by your application to delivery e-mails to
	| users of your application. Like the host we have set this value to
	| stay compatible with the Mailgun e-mail applications by default.
	|
	*/

	$config['port'] = 465;
	
	/*
	|--------------------------------------------------------------------------
	| E-Mail Encryption Protocol
	|--------------------------------------------------------------------------
	|
	| Here you may specify the encryption protocol that should be used when
	| the application send e-mail messages. A sensible default using the
	| transport layer security protocol should provide great security.
	|
	*/

	$config['encryption'] = 'ssl';

	/*
	|--------------------------------------------------------------------------
	| SMTP Server Username
	|--------------------------------------------------------------------------
	|
	| If your SMTP server requires a username for authentication, you should
	| set it here. This will get used to authenticate with your server on
	| connection. You may also set the "password" value below this one.
	|
	*/

	$config['username'] = 'rola.dummy00@gmail.com';

	/*
	|--------------------------------------------------------------------------
	| SMTP Server Password
	|--------------------------------------------------------------------------
	|
	| Here you may set the password required by your SMTP server to send out
	| messages from your application. This will be given to the server on
	| connection so that the application will be able to send messages.
	|
	*/


	$config['password'] = 'asdf1234*';
	
	// connect to frontend per environment config
	require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);

