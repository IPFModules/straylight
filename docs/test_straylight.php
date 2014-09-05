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
$path = $url . '/modules/straylight/target.php';
$action = '';

echo '<h1>Straylight test script</h1>';

switch ($action) {
	case "send":
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
		$form = '<form name="input" action="' . $path . '" method="get">
			<input type="radio" name="check_pulse" value="check_pulse">Check pulse<br />
			<input type="radio" name="check_status" value="check_status">Check status<br />
			<input type="radio" name="open_site" value="open_site">Open site<br />
			<input type="radio" name="close_site" value="close_site">Close site<br />
			<input type="radio" name="clear_cache" value="clear_cache">Clear cache<br />
			<input type="radio" name="debug_on" value="debug_on">Debug on<br />
			<input type="radio" name="debug_off" value="debug_off">Debug off<br />
			<input type="radio" name="war_footing" value="war_footing">War footing<br />
			<input type="submit" value="Submit">
		</form>';
		echo $form;
		break;
}