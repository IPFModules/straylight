<?php
/**
 * Classes responsible for managing Straylight client objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

class mod_straylight_ClientHandler extends icms_ipf_Handler {
	
	// Initialise variables
	private $authenticated = FALSE; // Set to TRUE if all validation and authentication tests pass
	private $clean_client_id = ''; // ID of the client device
	private $clean_command = ''; // Command requested by client device
	private $clean_counter = ''; // Counter value incremented by client device on each request
	private $clean_hmac = ''; // Client-generated HMAC of request parameters using pre-shared key
	private $clean_timestamp = ''; // Client-generated timestamp of request
	private $clean_random = ''; // Random data generated by client to mash up the request HMAC signature
	private $my_hmac = ''; // Server-generated HMAC of request parameters using pre-shared key
	private $my_data = '';
	private $valid_command = FALSE; // Set to TRUE if the request passes validation tests
	private $good_timestamp = FALSE; // Set to TRUE if the timestamp falls within an acceptable range
	private $straylight_authorised_client = FALSE; // Set to TRUE if the client device ID is known and authorised
	private $good_counter = FALSE; // Set to TRUE if the counter is > than the stored value for the last request
	private $good_hmac = FALSE; // Set to TRUE if the client-generated HMAC matches the server-generated HMAC
	private $parameters_supplied = FALSE;
	private $straylight_client = ''; // Holds a straylight client object

	// List of commands accepted by the Straylight module
	private $valid_commands = array(
		'checkPulse',
		'closeSite',
		'openSite',
		'clearCache',
		'debugOn',
		'debugOff',
		'lockDown');
		
	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db database connection object
	 */
	public function __construct(&$db) {
		parent::__construct($db, "client", "client_id", "uid", "publickey", "straylight");
	}
	
	/**
	 * Extracts commands from remote devices, authenticates and executes them.
	 * 
	 * Pass in the $_POST from target.php or equivalent. Sanitisation and authentication are 
	 * handled internally.
	 * 
	 * @param array $dirty_data
	 */
	public function authenticate_remote_command($dirty_data) {
		$this->check_required_parameters_supplied($dirty_data);
		$this->check_and_sanitise_dirty_data($dirty_data);
		$this->check_valid_command();
		$this->check_timestamp();
		$this->check_client_authorised();
		$this->check_counter();
		$this->check_hmac();
		$this->authenticated = $this->check_sanity();
		if ($this->authenticated == TRUE) {
			$this->execute_remote_command();
		}
	}
		
	/**
	 * Checks that necessary parameters have been supplied in a remote command (pass in $_POST)
	 * 
	 * @param array $post_data 
	 */
	private function check_required_parameters_supplied ($dirty_data) {
		if (empty($dirty_data['client_id']) 
			|| empty($dirty_data['command']) 
			|| empty($dirty_data['counter']) 
			|| empty($dirty_data['timestamp']) 
			|| empty($dirty_data['random']) 
			|| empty($dirty_data['hmac'])) {
				$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_MISSING_REQUIRED_PARAMETER);
		} else {
			$this->parameters_supplied = TRUE;
		}
	}
	
	/**
	 * Checks the data type and sanitises parameters supplied in the remote command
	 * 
	 * @param mixed $data 
	 */	
	private function check_and_sanitise_dirty_data($dirty_data) {
		$this->clean_client_id = ctype_digit($dirty_data['client_id']) ? 
			(int)($dirty_data['client_id']) : $this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_CLIENT_ID_NOT_DECIMAL);
		$this->clean_counter = ctype_digit($dirty_data['counter']) ?
			(int)($dirty_data['counter']) : $this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_COUNTER_NOT_DECIMAL);
		$this->clean_command = ctype_alpha($dirty_data['command']) ?
			trim($dirty_data['command']) : $this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_COMMAND_NOT_ALPHANUMERIC);
		$this->clean_timestamp = ctype_digit($dirty_data['timestamp']) ?
			(int)($dirty_data['timestamp']) : $this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_TIMESTAMP_NOT_DECIMAL);
		$this->clean_random = ctype_alnum($dirty_data['random']) ?
			trim($dirty_data['random']) : $this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_RANDOM_NOT_ALPHANUMERIC);
		$this->clean_hmac = ctype_alnum($dirty_data['hmac']) ?
			trim($dirty_data['hmac']) : $this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_HMAC_NOT_ALPHANUMERIC);
	}
	
	/**
	 * Checks the command issued by the remote device against a whitelist of acceptable values
	 * 
	 * @param string $command
	 */
	private function check_valid_command($clean_command, $valid_commands) {
		if (in_array($this->clean_command, $this->valid_commands)) {
			$this->valid_command = TRUE;
		} else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_INVALID_COMMAND);
		}
	}
	
	/**
	 * Checks that the timestamp issued with the remote command falls in an acceptable range defined
	 * in module preferences
	 * 
	 * Timestamps that have expired (too old) or that precede current server time will be rejected.
	 * This helps guard (in part) against replay attacks.
	 */
	private function check_timestamp() {
		$time = time();
		$timestamp_differential = $time - $this->clean_timestamp;
		if ($this->clean_timestamp <= $time
				&& $timestamp_differential < icms_getConfig('timestamp_tolerance', 'straylight')) {
			$this->good_timestamp = TRUE;
		} else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_BAD_TIMESTAMP);
		}
	}
	
	/**
	 * Retrieves a Straylight client object and checks if it is authorised to issue remote commands
	 */
	private function check_client_authorised() {
		$straylight_client = $this->get($this->clean_client_id);
		if ($straylight_client && ($straylight_client->getVar('authorised', 'e') == TRUE)) {
			$this->straylight_client = $straylight_client;
			$this->straylight_authorised_client = TRUE;
		} else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_CLIENT_NOT_AUTHORISED);
		}
	}
	
	/**
	 * Checks that the counter issued with the remote command is valid
	 * 
	 * Straylight increments a counter each time a client device issues a remote command. If a 
	 * remote command is received with a counter value less than or equal to the stored value, it
	 * is discarded in order to guard against replay attacks.
	 */
	private function check_counter() {
		if ($this->clean_counter > $this->straylight_client->getVar('request_counter', 'e')) {
			$this->good_counter = TRUE;
			$this->update_request_counter($this->straylight_client, $this->clean_counter);
		} else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_BAD_COUNTER);
		}
	}
	
	/**
	 * Checks if the remote command HMAC matches that calculated by Straylight using  pre-shared key
	 * 
	 * The remote device supplies a HMAC of the command parameters calculated using a pre-shared key.
	 * Straylight also calculates a HMAC of the command parameters using its copy of the pre-shared
	 * key. If the HMACs match, the integrity and authenticity of the remote command is validated 
	 * and Straylight will execute it. If the HMACs do not match, then the command is discarded as 
	 * invalid.
	 * 
	 * Note that in order for the HMACs to match, the remote client must use EXACTLY the same data 
	 * in exactly the same order as the Straylight module when calculating the HMAC. You can get the
	 * order from the function below.
	 */
	private function check_hmac() {
		$key = $this->straylight_client->getVar('shared_hmac_key', 'e');
		$this->my_data = $this->clean_client_id
				. $this->clean_command 
				. $this->clean_counter
				. $this->clean_timestamp
				. $this->clean_random;
		if (!empty($key)) {
			$this->my_hmac = substr(hash_hmac('sha256', $this->my_data, $key, FALSE), 0, 32);
		} else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_NO_PRESHARED_KEY);
		}
		if ($this->my_hmac == $this->clean_hmac) // HMAC verified, authenticity and integrity has been established. 
		{
			$this->good_hmac = TRUE;
		}
		else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_BAD_HMAC); // Bad HMAC, discard
		}
	}
	
	/**
	 * Explicitly checks that all Straylight tests for authentication have been passed
	 */
	private function check_sanity() {
		if ($this->parameters_supplied
				&& $this->valid_command
				&& $this->good_timestamp
				&& $this->straylight_authorised_client
				&& $this->good_counter
				&& $this->good_hmac) {
			return TRUE;	
		} else {
			$this->report_error(_CO_STRAYLIGHT_CLIENT_ERROR_SANITY_CHECK_FAILED);
		}
	}
	
	/**
	 * Executes a remote command once authentication has been establised
	 */
	private function execute_remote_command() {
		if ($this->authenticated == TRUE) // Just checking one more time, consider it a killswitch on the method
		{
			switch ($this->clean_command)
			{
				// Returns an 'ok' code if site up.
				case "checkPulse":
					http_response_code(200); // Requires PHP 5.4+
					break;

				// Closes the site
				case "closeSite":
					$this->straylight_update_config('closesite', 1);
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
						'cache' => ICMS_CACHE_PATH . "/"));
					break;

				// Turns inline debugging on
				case "debugOn":
					$this->straylight_update_config('debug_mode', 1);		
					break;

				// Turns inline debugging off
				case "debugOff":
					$this->straylight_update_config('debug_mode', 0);
					break;

				// Prepares the site to resist casual abuse by idiots
				case "lockDown":
					$this->straylight_update_config('activation_type', 2); // New user registrations require admin approval
					$this->straylight_update_config('activation_group', 1); // Registration activation requests sent to admins
					$this->straylight_update_config('minpass', 13); // Sets minimum password length to 13 characters
					$this->straylight_update_config('pass_level', 80); // Sets minimum password security level to 'strong'
					$this->straylight_update_config('minuname', 5); // Set minimum username length to 5 characters
					$this->straylight_update_config('multi-login', 0); // Multiple login of same user disallowed
					$this->straylight_update_config('remember_me', 0); // Remember me login feature disabled
					$this->straylight_update_config('self-delete', 0); // Prevent users deleting their own account
					$this->straylight_update_config('enable_badips', 1); // IP bans are enabled
					$this->straylight_update_config('com_rule', 0); // Comments are closed for all modules
					$this->straylight_update_config('use_captcha', 1); // CAPTCHA enabled on forms, including registration
					$this->straylight_update_config('use_captchaf', 1); // CAPTCHA enabled on forms (general pref)
					$this->straylight_update_config('keyword_min', 5); // Minimum search keyword at least 5 chars
					$this->straylight_update_config('gzip_compression', 0); // Disable GZIP compression (reduces processer load)
					$this->straylight_update_config('allow_chgmail', 0); // User change of email address disallowed
					$this->straylight_update_config('allow_chguname', 0); // User change of display name disallowed
					$this->straylight_update_config('allow_htsig', 0); // Display of external images and html in signatures disallowed
					$this->straylight_update_config('avatar_allow_upload', 0); // Upload of custom avatar images disallowed
				break;
			}
		}
		else {
			exit;
		}
	}
	
	/**
	 * Updates system/module configuration values
	 * 
	 * Retrieves configuration objects based on their 'conf_name' field. This is usually a single
	 * object but may be multiple in some cases (eg. com_rule is used by each module). Alters the 
	 * value of the 'conf_value' field for each.
	 * 
	 * @param string $conf_name
	 * @param mixed $conf_value 
	 */
	private function straylight_update_config($conf_name, $conf_value) {
		$criteria = icms_buildCriteria(array('conf_name' => $conf_name));
		$config_handler = icms::handler('icms_config');
		$configs = $config_handler->getConfigs($criteria);
		foreach ($configs as $config) {
			$config->setVar('conf_value', $conf_value);
			$config_handler->insertConfig($config);
		}
	}
	
	/**
	 * Updates the client request counter
	 */
	private function update_request_counter($clientObj, $counter){
		$clientObj->setVar('request_counter', $counter);
		$this->insert($clientObj, TRUE);
	}
	
	/**
	 * Toggles Straylight authentication online or offline
	 *
	 * @param int $token_id
	 * @param str $field
	 * @return int $visibility
	 */
	private function changeAuthorisationStatus($client_id, $field) {

		$authorised = $clientObj = '';

		$clientObj = $this->get($client_id);
		if ($clientObj->getVar($field, 'e') == TRUE) {
			$clientObj->setVar($field, 0);
			$authorised = 0;
		} else {
			$clientObj->setVar($field, 1);
			$authorised = 1;
		}
		$this->insert($clientObj, TRUE);

		return $authorised;
	}
	
	/**
	 * Adjust data before saving or updating
	 * @param object $obj 
	 */
	protected function beforeSave(& $obj) {		
		// Require key length to be 64 characters. Todo: Check all are in hexadecimal range.
		$key = trim($obj->getVar('shared_hmac_key', 'e'));
		if (strlen($key)==64) {
			$obj->setVar('shared_hmac_key', $key); // Trim any whitespace user entered in box
			return TRUE;
		} else {
			$obj->setErrors(_CO_STRAYLIGHT_CLIENT_KEY_LENGTH_ERROR);
			return FALSE;
		}
		return TRUE;
		
	}
	
	/**
	 * Echoes an error message and exits.
	 * 
	 * For internal testing use only. Keep the echo line commented out on public-facing sites.
	 * 
	 * @param string $error
	 */
	private function report_error($error) {
		// IMPORTANT TEST USE ONLY; Ensure the error message is commented OUT on production sites
		echo _CO_STRAYLIGHT_CLIENT_ERROR . $error;
		exit;
	}
}