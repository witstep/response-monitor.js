<?php
/**
 * WARNING: Demo code! Not safe for use in a production server.
 *
 * Simulates lengthy processing by sleeping for some seconds before returning
 * the result of the division of 2 numbers.
 *
 */
$cookiePrefix = 'response-monitor'; //should match the one used in the client options
$tokenValue = $_GET[$cookiePrefix]; //the cookie is always received using GET
$delay = 5;// 5 seconds is enough to pretend something is being done

//operation arguments can be passed by POST or GET
$dividend = intval($_REQUEST['dividend']);
$divisor = intval($_REQUEST['divisor']);

/*
The cookie name is not constant to allow simultaneous monitoring of
multiple requests. 
The value of the cookie is used to pass a result code
*/
$cookieName = $cookiePrefix.'_'.$tokenValue;

try {
	if($divisor == 0)//simulate server-side failure
		throw new Exception('Division by zero.');
	$result = $dividend/$divisor;
	//simulate processing delay
	sleep($delay);
	//the client code will be looking for the cookie
	setCookieToken($cookieName, 1, false);//in this example, "1" means success
} catch (Exception $e) {
	setCookieToken($cookieName, 0, false);//in this example, "0" means failure
	echo 'error:', $e->getMessage();
	die();
}

header('Content-Type: text/plain');
header("Content-Disposition: attachment; filename=\"$cookieName.txt\"");
echo "$dividend / $divisor = $result\r\n";
print_r($_REQUEST);

function setCookieToken($cookieName, $cookieValue, $httpOnly = true, $secure = false ) {
	setcookie(
		$cookieName,
		$cookieValue,
		time()+300,            // expire in 5 mintes
		"/",                   // your path
		$_SERVER["HTTP_HOST"], // your domain
		$secure,               // Use true over HTTPS
		$httpOnly              // Set true for authentication cookies (XSS)
	);
}
