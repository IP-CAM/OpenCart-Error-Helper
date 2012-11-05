<?php

// Handlers for errors and script shutdown
set_error_handler('error_handler');
register_shutdown_function('fatal_error_handler');

function error_handler($errno, $errstr, $errfile, $errline) {

	global $log, $config;

	// If this error is not in error reporting
    if (!(error_reporting() & $errno)) {
        return;
    }

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	// If this is a production enviroment
	if ($_SERVER['HTTP_HOST']=='www.yoursite.com'){

		// Send email to devlopers
		$message =  $error . "\n\n";
		$message .=  $errstr . ' in ' . $errfile . ' on line ' . $errline. "\n";

		$mail = new Mail();
		$mail->protocol = $config->get('config_mail_protocol');
		$mail->parameter = $config->get('config_mail_parameter');
		$mail->hostname = $config->get('config_smtp_host');
		$mail->username = $config->get('config_smtp_username');
		$mail->password = $config->get('config_smtp_password');
		$mail->port = $config->get('config_smtp_port');
		$mail->timeout = $config->get('config_smtp_timeout');
		// Add your email here				
		$mail->setTo('you@youremail.com');
  		$mail->setFrom('error@yoursite.com');
  		$mail->setSender('Error');
  		$mail->setSubject('Error on Yoursite');
  		$mail->setText($message);
  		$mail->send();

  		// Send the 500 error headers
  		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
  		
  		// Include custom error page here
  		ob_start();
		include('error.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		echo $buffer;

		exit();

	} else {
		
		// Display or log errors depending on opencart config
		if ($config->get('config_error_display')) {
			echo '<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
		}
		
		if ($config->get('config_error_log')) {
			$log->write('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
		}

	}

	return true;

}

function fatal_error_handler() {

	// If script dies check it was not an error. If it was trigger the error handler function
    $last_error = error_get_last();
    if($last_error['type'] === E_ERROR || $last_error['type'] === E_PARSE) {
        error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
	} else {
		return true;
	}

}
