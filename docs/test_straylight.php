<?php

/*
 * Script for testing the operation of the straylight module without a remote device.
 * 
 * Usage: Install the Straylight module, add an authorised client and generate a 256 byte pre-
 * shared key from a high quality random source (eg. www.grc.com/passwords). Register the key in
 * the authorised client and use it to set the value of the $pre_shared_key variable below. Set
 * the $client_id to match that of the authorised you created in the module as well. You should 
 * then be able to open this script in your browser and use the menu to securely send commands to
 * control your ImpressCMS website without logging in.
 * 
 * Note that 'securely' in this instance means that your request is authenticated via HMAC. So 
 * while an adversary can observe the plaintext requests that you send - they cannot forge a valid 
 * request themselves without the key (which you have inserted into this script, so don't leave it 
 * laying around).
 * 
 * Note that you can safely disable this script by de-athorising the matching client in the
 * Straylight module admin interface.
 * 
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL V2 or any later version
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

// Please edit these variables or the script cannot function
$client_id = '3'; // The ID of the authorised straylight client you created in the module
$pre_shared_key = 'JOfUS03S9CDtPyHnNH0DbihTDauaEYDidiSzFB1pqOzij9heJZanLC4m3762mAdqnYkc8SaSc7Nfg7648t72pPVvYjfWC8XvLkeIe7fZYh4lum2DhH7HCNqCjHuPZs9A0lgD6BTN860PHjHP1H6myshaWzgvH6tzA83qXj7jUDtMqsorjRLrdRACqITBr1tUQAfkCbMhHlAUgOpcGMooGIl5RQWnY1Yi1Rncu8JF5Lx378NrVfe9m5RlWMYRG1jI'; // The same 256 character random key you registered in the authorised client
$url = 'http://192.168.1.99/dev'; // Enter the base URL of your site (eg. http://www.mysite.com or http://localhost etc).
$counter = 60; // Need to increment this each run of the script. Sorry!

// Initialising other variables (no need to edit)
$command = trim($_POST['command'], FILTER_SANITISE_STRING);
$timestamp = time();
$random = generateRandomString();
$path = $url . '/modules/straylight/target.php';
$data = '';
$my_hmac = '';

// For convenience (testing) sake, but this is NOT an acceptable (secure) way to generate random strings
// Also, don't forget that the total length of all parameters sent in a Straylight request should be
// > 256 characters in order to maintain the full security of the HMAC.
function generateRandomString($length = 62) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

echo '<h1>Straylight test script</h1>';

switch ($_POST['action']) {
	case "send":
		// Must retain EXACTLY the same order and parameters as in target.php
		$data = $client_id
			. $command 
			. $counter
			. $timestamp
			. $random;
		$my_hmac = hash_hmac('sha256', $data, $pre_shared_key, FALSE);
		$command_parameters = array(
			'client_id' => $client_id,
			'command' => $command,
			'counter' => $counter,
			'timestamp' => $timestamp,
			'random' => $random,
			'hmac' => $my_hmac);
		
		// Post data to target.php for execution
		foreach($command_parameters as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_POST, count($command_parameters));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
		
		break;
	
	default: // Show the command menu
		echo '<p>This script emulates a remote device sending commands to the Straylight module. 
			To determine if a command has been successful or not, you will need to observer your 
			website in a separate browser tab, as the Straylight module does not provide feedback 
			to client devices.</p>';

		// Check that required variables have been set
		if ($client_id == '') {
			echo '<p>Please create an authorised straylight client within the Straylight module (obviously, 
				after you have installed it) and note it\'s ID number. Set the $client_id variable in the 
				script to be the same.</p>';
		}
		if ($pre_shared_key == '') {
			echo '<p>Please generate a RANDOM 256 character key and use it to set the $pre_shared_key 
				variable in the script. The same key must be registered in an authorised straylight client 
				within the admin interface of the module.</p>';
		}
		if ($url == '') {
			echo '<p>Please enter your site URL in the $url variable of the script.</p>';
		}

		// Command menu
		$form = '<form name="input" action="' . $_SERVER['PHP_SELF'] . '" method="post">
			<input type="radio" name="command" value="checkPulse">Check pulse<br />
			<input type="radio" name="command" value="checkStatus">Check status<br />
			<input type="radio" name="command" value="openSite">Open site<br />
			<input type="radio" name="command" value="closeSite">Close site<br />
			<input type="radio" name="command" value="clearCache">Clear cache<br />
			<input type="radio" name="command" value="debugOn">Debug on<br />
			<input type="radio" name="command" value="debugOff">Debug off<br />
			<input type="radio" name="command" value="lockDown">Lock down<br />
			<input type="hidden" name="action" value="send">
			<input type="submit" value="Submit">
		</form>';
		echo $form;
		break;
}