<?php
/**
 * Generic interface for handling administration commands from remote devices (Android, Arduino). 
 * 
 * Commands are authenticated via calculation of SHA256 HMAC with preshared, per-device keys. 
 * This allows the integrity and authenticity of the request to be tested, but the request is not
 * encrypted (ie. it is not private). Replay attacks are guarded against by inclusion of a counter
 * (tracked by the server), timestamp and random string in the the request.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

include_once 'header.php';
include_once ICMS_ROOT_PATH . '/header.php';

// Initialise
$clean_client_id = $clean_command = $clean_counter = $clean_timestamp = $clean_random = $clean_hmac = '';
$valid = $authenticated = $straylight_client = FALSE;

// Check that required parameters have been supplied. Exit if anything missing.
if (
		empty($_GET['client_id']) ||
		empty($_GET['command']) || 
		empty($_GET['counter']) ||
		empty($_GET['timestamp']) ||
		empty($_GET['random']) ||
		empty($_GET['hmac'])
		)
{
	echo 'Error: Missing required parameter';
	exit;
}

// Sanitise input, exit if any parameter is rendered empty as a result.
$clean_client_id = isset($_GET['client_id']) ? (int)($_GET['client_id']) : 0;
$clean_command = icms_core_DataFilter::checkVar($_GET['command'], 'str');
$clean_timestamp = isset($_GET['timestamp']) ? (int)($_GET['timestamp']) : 0;
$clean_random = icms_core_DataFilter::checkVar($_GET['random'], 'str');
$clean_hmac = icms_core_DataFilter::checkVar($_GET['hmac'], 'str');

if (empty($clean_uid) || empty($clean_command) || empty($clean_signature))
{
	echo 'Error: Sanitising killed a parameter';
	exit;
}

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

if (!in_array($clean_command, $valid_commands))
{
	echo 'Error: Invalid command';
	exit;
}

// Validate input:
// 1. The concatenated request parameters must be authenticated by SHA256 HMAC
// 2. The counter must be > than the previously stored value
// 3. The timestamp must fall within an acceptable range (TODO: Add to module preferences)
// 4. The device must be known and authorised for remote Straylight administration.

// 1. Authenticate message via SHA256 HMAC and preshared key

// 2. Check for replay attack (counter must be > than previously stored value)

// 3. Check timestamp falls in acceptable range (defined in module preferences, default 10 minutes)

// 4. Check the device is currently Straylight authorised.
$straylight_client_handler = icms_getModuleHandler('client', basename(dirname(__FILE__)), 'straylight');

$straylight_client = $straylight_client_handler->get($clean_uid);
if (!$straylight_client || ($straylight_client->getVar('authorised', 'e') == FALSE))
{
	echo 'Error: Client not Straylight authorised';
	$straylight_client = FALSE;
	exit;
}

//////////////////////////////////////////////////////////
////////// PASSED VALIDATION AND AUTHENTICATION //////////
//////////////////////////////////////////////////////////

// If execution got this far, request has been validated and authenticated, proceed to process
if ($valid && $authenticated && $straylight_client)
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
			echo "Shift moved to war footing";
			
			break;
	}
}
else {
	exit;
}

echo '<br />Everything pass the test';

include_once 'footer.php';