<?php
/**
 * Generic interface for handling administration commands from remote devices (eg. Android, Arduino). 
 * 
 * Commands are authenticated via calculation of SHA256 HMAC with preshared, per-device keys. 
 * This allows the integrity and authenticity of the request to be validated, but the request is not
 * encrypted (ie. it is not private). Replay attacks are guarded against by inclusion of a counter
 * (tracked by the server), timestamp and random string of crap (RSOC) in the the request, which 
 * ensure that each request has a unique fingerprint.
 * 
 * However, some devices (eg. Arduino) may not be capable of supplying all of these credentials due
 * to resource limitations, so the challenge tests are arranged in a semi-independent manner; 
 * you can comment out particular tests if your remote device can't handle them. Of course, this 
 * reduces security. As a minimum: Use the client_id, HMAC and (counter or timestamp). The random 
 * text is highly desirable.
 * 
 * Key length: Use a 256 byte key for optimum security. Shorter keys weaken security. There is 
 * no evidence that longer keys improve it. 256 is what the algorithm uses internally.
 * 
 * Randomness: Get your key from a high quality random source, such as GRC's perfect passwords page.
 * Be aware that random number generation on small devices (notably Arduino) can be extremely bad.
 * https://www.grc.com/passwords.htm
 * 
 * Data length: The overall length of device supplied data used to calculate the HMAC should be at 
 * least 256 characters for security reasons. So you may want to adjust the length of your RSOC 
 * accordingly.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

function report_error($error) {
	echo 'Error: ' . $error;
	exit;
}

include_once 'header.php';
include_once ICMS_ROOT_PATH . '/header.php';

// Initialise
$clean_client_id = $clean_command = $clean_counter = $clean_timestamp = $clean_random = 
$clean_hmac = $my_hmac = $data = '';
$valid_request = $authenticated = $straylight_authorised_client = FALSE;

// Check that required parameters have been supplied. Exit if anything missing.
if (empty($_GET['client_id']) 
		|| empty($_GET['command']) 
		|| empty($_GET['counter']) 
		|| empty($_GET['timestamp']) 
		|| empty($_GET['random']) 
		|| empty($_GET['hmac'])) {
	report_error('Missing required parameter');
}

// Sanitise input, exit if any parameter is rendered empty as a result.
$clean_client_id = isset($_GET['client_id']) ? (int)($_GET['client_id']) : 0;
$clean_counter = isset($_GET['counter']) ? (int)($_GET['counter']) : 0;
$clean_command = icms_core_DataFilter::checkVar($_GET['command'], 'str');
$clean_timestamp = isset($_GET['timestamp']) ? (int)($_GET['timestamp']) : 0;
$clean_random = icms_core_DataFilter::checkVar($_GET['random'], 'str');
$clean_hmac = icms_core_DataFilter::checkVar($_GET['hmac'], 'str');

// Check command is valid part of command vocabulary. Exit if command invalid.
$valid_commands = array(
	'check_pulse',
	'check_status',
	'open_site',
	'close_site',
	'clear_cache',
	'debug_on',
	'debug_off',
	'war_footing');

if (in_array($clean_command, $valid_commands))
{
	$valid_request = TRUE;
} else {
	report_error('Invalid command');
}

/**
 * Validate input:
 *
 * 1. The device must be known and authorised for remote Straylight administration.
 * 2. The concatenated request parameters must be authenticated by SHA256 HMAC with per-device key
 * 3. The timestamp must fall within an acceptable range
 * 4. The counter must be > than the previously stored value to protect against replay attacks
 */

// 1. Check the device is currently Straylight authorised.
$straylight_client_handler = icms_getModuleHandler('client', basename(dirname(__FILE__)), 'straylight');

$straylight_client = $straylight_client_handler->get($clean_client_id);
if ($straylight_client && ($straylight_client->getVar('authorised', 'e') == TRUE)) {
	$straylight_authorised_client = TRUE;
} else {
	report_error('Client not Straylight authorised');
}

/**
 * 2. Authenticate message via SHA256 HMAC and preshared key.
 * 
 * Note that client devices must also concatenate these fields in the same order when doing their
 * own HMAC calculation. To protect against tampering, all request fields must be included in the 
 * calculation (except for the HMAC itself).
 * 
 * Note: Special characters included in the GET request from your device need to be url encoded or 
 * serialised, and then decoded here prior to calculating the HMAC to ensure a reproduceable result.
 * Some characters, especially spaces and entities such as '&' can also mess up your parameters if
 * not encoded. If your device can't urlencode or is too limited to do it manually, then avoid use 
 * of such characters.
 */

$key = $straylight_client->getVar('shared_hmac_key', 'e');

// Comment out according to the parameters in use
$data = $clean_client_id . $clean_command 
		. $clean_counter
		. $clean_timestamp
		. $clean_random
		;
if (!empty($key)) {
	$my_hmac = hash_hmac('sha256', $data, $key, FALSE);
}
if ($my_hmac == $clean_hmac) // HMAC verified, authenticity and integrity has been established. 
{
	// 3. Check timestamp falls in acceptable range (defined in module preferences, default 10 minutes)
	$time = time();
	$timestamp_differential = $time - $clean_timestamp;
	if ($clean_timestamp > $time || $timestamp_differential > icms_getConfig('timestamp_tolerance', $straylight)) {
		report_error('Bad timestamp. Command has expired. Check the clock of your device is accurate.');
	}
	
	// 4. Check request counter exceeds the stored value (guard against replay attacks)
	if ($clean_counter > $straylight_client->getVar('request_counter')) {
		$straylight_client_handler->update_request_counter($straylight_client, $clean_counter);
	} else {
		report_error('This is not the most recent request from your device. Discarded');
	}
		
	// Passed all checks, request is good
	$authenticated = TRUE;
	echo 'Good HMAC';
	echo $clean_random;
}
else {
	report_error('Bad HMAC. Failed to confirm authenticity and integrity of message. Discarding.');
}

//////////////////////////////////////////////////////////
////////// PASSED VALIDATION AND AUTHENTICATION //////////
//////////////////////////////////////////////////////////

// If execution got this far, request has been validated and authenticated, proceed to process
if ($straylight_authorised_client && $valid_request && $authenticated)
{
	switch ($clean_command)
	{
		// Returns a heartbeat, if client receives no response site will be presumed dead
		case "check_pulse":
			echo "Check pulse";
			
			break;

		// Returns a readout of Straylight's current settings
		case "check_status":
			echo "Check status";
			
			break;

		// Opens the site
		case "open_site":
			echo "Open site";
			$icmsConfig['closesite'] = 0;
			break;

		// Closes the site
		case "close_site":
			echo "Close site";
			$icmsConfig['closesite'] = 1;
			$config_handler = icms::handler('icms_config');
			$config = $config_handler->getConfigs();
			foreach ($config as $item) 
			{
				echo $item->getVar('conf_name') . '<br /><br />';
			}
			$config_handler->insertConfig($config);
			break;

		// Clears the /cache and /templates_c folders
		case "clear_cache":
			echo "Clear cache";
			
			break;

		// Turns inline debugging on
		case "debug_on":
			echo "Debug on";
			
			break;

		// Turns inline debugging off
		case "debug_off":
			echo "Debug off";
			
			break;

		// Prepares the site to resist casual abuse by idiots. Protective measures include:
		// New user registrations are closed.
		// Multiple login of same user disallowed.
		// IP bans are enabled
		// Comments are closed for all modules.
		// CAPTCHA enabled on forms
		// HTML purifier is enabled
		// Minimum search string set to at least 5 characters.
		// GZIP compression is disabled (reduces processer load at cost of increased bandwidth)
		// Minimum password length set to 8 characters.
		// Minimum security level set to strong.
		// User change of email address disallowed.
		// User change of display name disallowed.
		// Display of external images and html in signatures disallowed.
		// Upload of custom avatar images disallowed.
		
		case "war_footing":
			echo "Shift site to war footing";
			
			break;
	}
}
else {
	exit;
}

echo '<br />Everything pass the test';