<?php
/**
 * Generic interface for handling administration commands from remote devices (eg. Android, Arduino). 
 * 
 * Commands are authenticated via calculation of SHA256 HMAC with preshared, per-device keys. 
 * This allows the integrity and authenticity of the request to be validated, but the request is not
 * encrypted (ie. it is not private). Replay attacks are guarded against by inclusion of a counter
 * (tracked by the server), timestamp and a random string of crap (RSOC) in the the request, which 
 * ensures that each request has a unique HMAC fingerprint.
 * 
 * However, some devices (eg. Arduino) may not be capable of supplying all of these credentials due
 * to resource limitations, so the challenge tests are arranged in a semi-independent manner; 
 * you can comment out particular tests if your remote device can't handle them. Of course, this 
 * reduces security. As a minimum: Use the client_id, HMAC and counter or timestamp. The random 
 * text is highly desirable. If your client device can't generate adequate randomness, consider 
 * pre-generating a large chunk of it it on a more capable device and storing it on the client (in
 * this case, you MUST discard random data as you use it, you must NEVER re-use it).
 * 
 * Key length: Use a 256 byte key for optimum security. Shorter keys weaken security. There is 
 * no evidence that longer keys improve it. 256 is what the algorithm uses internally.
 * 
 * Randomness: Get your key from a high quality random source, such as GRC's perfect passwords page.
 * Be aware that random number generation on small devices (notably Arduino) can be extremely bad 
 * (that is to say, UNACCEPTABLE for security purposes) https://www.grc.com/passwords.htm
 * 
 * Data length: The overall length of device-supplied data used to calculate the HMAC should be at 
 * least 256 characters for security reasons. So you may want to adjust the length of your RSOC
 * accordingly.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL V2 or any later version
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

include_once 'header.php';
include_once ICMS_ROOT_PATH . '/header.php';

// Initialise
$staylight_client_handler = icms_getModuleHandler('client', basename(dirname(__FILE__)), 'straylight');

// Pass request to Straylight for athentication and execution. Sanitisation is handled internally.
$straylight_client_handler->authenticate_remote_command($_POST);


// 1. Check that required parameters have been supplied. Exit if anything missing.
if (empty($_POST['client_id']) 
		|| empty($_POST['command']) 
		|| empty($_POST['counter']) 
		|| empty($_POST['timestamp']) 
		|| empty($_POST['random']) 
		|| empty($_POST['hmac'])) {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_MISSING_REQUIRED_PARAMETER);
}

// 2. Check that required parameters are of expected type and sanitise. Exit if encounter bad data.
$clean_client_id = ctype_digit($_POST['client_id']) ? 
	(int)($_POST['client_id']) : straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_CLIENT_ID_NOT_DECIMAL);
$clean_counter = ctype_digit($_POST['counter']) ?
	(int)($_POST['counter']) : straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_COUNTER_NOT_DECIMAL);
$clean_command = ctype_alnum($_POST['command']) ?
	trim($_POST['command']) : straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_COMMAND_NOT_ALPHANUMERIC);
$clean_timestamp = ctype_digit($_POST['timestamp']) ?
	(int)($_POST['timestamp']) : straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_TIMESTAMP_NOT_DECIMAL);
$clean_random = ctype_alnum($_POST['random']) ?
	trim($_POST['random']) : straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_RANDOM_NOT_ALPHANUMERIC);
$clean_hmac = ctype_alnum($_POST['hmac']) ?
	trim($_POST['hmac']) : straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_HMAC_NOT_ALPHANUMERIC);

// 2. Check command against vocabulary whitelist. Exit if command invalid. Alphabetical only.
$valid_commands = array(
	'checkPulse',
	'closeSite',
	'openSite',
	'clearCache',
	'debugOn',
	'debugOff',
	'lockDown');

if (in_array($clean_command, $valid_commands)) {
	$valid_command = TRUE;
} else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_INVALID_COMMAND);
}

/**
 * 3. Validate input:
 * a. The timestamp must fall within an acceptable range
 * b. The device must be known and authorised for remote Straylight administration.
 * c. The counter must be > than the previously stored value to protect against replay attacks
 * d. The concatenated request parameters must be authenticated by SHA256 HMAC with per-device key
 */

// a. Check timestamp falls in acceptable range (defined in module preferences, default 10 minutes)
$time = time();
$timestamp_differential = $time - $clean_timestamp;
if ($clean_timestamp <= $time && $timestamp_differential < icms_getConfig('timestamp_tolerance', 'straylight')) {
	$good_timestamp = TRUE;
} else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_BAD_TIMESTAMP);
}

// b. Check the device is currently Straylight authorised.
$straylight_client_handler = icms_getModuleHandler('client', basename(dirname(__FILE__)), 'straylight');
$straylight_client = $straylight_client_handler->get($clean_client_id);
if ($straylight_client && ($straylight_client->getVar('authorised', 'e') == TRUE)) {
	$straylight_authorised_client = TRUE;
} else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_CLIENT_NOT_AUTHORISED);
}

// c. Check request counter exceeds the stored value (guard against replay attacks)
if ($clean_counter > $straylight_client->getVar('request_counter', 'e')) {
	$good_counter = TRUE;
	$straylight_client_handler->update_request_counter($straylight_client, $clean_counter);
} else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_BAD_COUNTER);
}

/**
 * d. Authenticate message via SHA256 HMAC and preshared key.
 * 
 * Note that client devices must also concatenate these fields in the same order when doing their
 * own HMAC calculation. To protect against tampering, all request fields must be included in the 
 * calculation (except for the HMAC itself).
 * 
 * Note: Special characters included in the POST request from your device need to be url encoded or 
 * serialised, and then decoded here prior to calculating the HMAC to ensure a reproduceable result.
 * Some characters, especially spaces and entities such as '&' can also mess up your parameters if
 * not encoded. If your device can't urlencode or is too limited to do it manually, then avoid use 
 * of such characters. 
 * 
 * CONVENTION: Only use alphanumeric characters in Straylight request parameters. We can't be sure
 * that all client devices will have a way to urlencode, or perhaps it will require including 
 * libraries that eat up limited memory space (in Arduino for example, this is a huge issue).
 */

$key = $straylight_client->getVar('shared_hmac_key', 'e');

// Comment out according to the parameters in use
$data = $clean_client_id . $clean_command 
		. $clean_counter
		. $clean_timestamp
		. $clean_random;
if (!empty($key)) {
	$my_hmac = hash_hmac('sha256', $data, $key, FALSE);
} else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_NO_PRESHARED_KEY);
}
if ($my_hmac == $clean_hmac) // HMAC verified, authenticity and integrity has been established. 
{
	$good_hmac = TRUE;
}
else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_BAD_HMAC);
}

// Final sanity check. Explicitly check that all necessary tests have been passed
if ($valid_command
		&& $good_timestamp
		&& $straylight_authorised_client
		&& $good_counter
		&& $good_hmac) {
	$authenticated = TRUE;	
} else {
	straylight_report_error(_CO_STRAYLIGHT_CLIENT_ERROR_SANITY_CHECK_FAILED);
}

//////////////////////////////////////////////////////////////////////
////////// REQUEST HAS PASSED VALIDATION AND AUTHENTICATION //////////
//////////////////////////////////////////////////////////////////////

/* Proceed to process the command
 * 
 * To extend the list of commands, simply i) add it to the command vocabulary array (see
 * comment 2. above), and ii) add a matching case statement below with the logic for implementing
 * it. The authentication mechanism is independent and will prevent unauthorised commands from 
 * being executed, so you can safely add things to the list. Commands must be alphabetical characters
 * only - no underscores, hyphens or numbers.
 */
if ($authenticated == TRUE)
{
	switch ($clean_command)
	{
		// Returns an 'ok' code if site up.
		case "checkPulse":
			http_response_code(200); // Requires PHP 5.4+
			break;

		// Closes the site
		case "closeSite":
			straylight_update_config('closesite', 1);
			break;
		
		// Not implemented. Access to relevant files requires having permissions to view the site
		// when it is closed. Could probably be accomplished using raw SQL but that has security 
		// implications (DB password) that I have not yet fully contemplated. So for the moment, 
		// it's out.
		case "openSite":
			break;

		// Clears the /cache and /templates_c folders
		case "clearCache":
			icms_core_Filesystem::cleanFolders(array('templates_c' => ICMS_COMPILE_PATH . "/",
				'cache' => ICMS_CACHE_PATH . "/"), $remove_admin_cache);
			break;

		// Turns inline debugging on
		case "debugOn":
			straylight_update_config('debug_mode', 1);		
			break;

		// Turns inline debugging off
		case "debugOff":
			straylight_update_config('debug_mode', 0);
			break;

		// Prepares the site to resist casual abuse by idiots
		case "lockDown":
			straylight_update_config('activation_type', 2); // New user registrations require admin approval
			straylight_update_config('activation_group', 1); // Registration activation requests sent to admins
			straylight_update_config('minpass', 13); // Sets minimum password length to 13 characters
			straylight_update_config('pass_level', 80); // Sets minimum password security level to 'strong'
			straylight_update_config('minuname', 5); // Set minimum username length to 5 characters
			straylight_update_config('multi-login', 0); // Multiple login of same user disallowed
			straylight_update_config('remember_me', 0); // Remember me login feature disabled
			straylight_update_config('self-delete', 0); // Prevent users deleting their own account
			straylight_update_config('enable_badips', 1); // IP bans are enabled
			straylight_update_config('com_rule', 0); // Comments are closed for all modules
			straylight_update_config('use_captcha', 1); // CAPTCHA enabled on forms, including registration
			straylight_update_config('use_captchaf', 1); // CAPTCHA enabled on forms (general pref)
			straylight_update_config('keyword_min', 5); // Minimum search keyword at least 5 chars
			straylight_update_config('gzip_compression', 0); // Disable GZIP compression (reduces processer load)
			straylight_update_config('allow_chgmail', 0); // User change of email address disallowed
			straylight_update_config('allow_chguname', 0); // User change of display name disallowed
			straylight_update_config('allow_htsig', 0); // Display of external images and html in signatures disallowed
			straylight_update_config('avatar_allow_upload', 0); // Upload of custom avatar images disallowed
		break;
	}
}
else {
	exit;
}