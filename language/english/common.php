<?php
/**
 * English language constants commonly used in the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson).
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		straylight
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

// Client device
define("_CO_STRAYLIGHT_CLIENT_TITLE", "Remote client name");
define("_CO_STRAYLIGHT_CLIENT_AUTHORISED", "Authorised?");
define("_CO_STRAYLIGHT_CLIENT_AUTHORISED_DSC", "Enable or disable remote administrative privileges 
	for this client device. A disabled client has no rights and is completely non-functional.");
define("_CO_STRAYLIGHT_CLIENT_REQUEST_COUNTER", "Request counter");
define("_CO_STRAYLIGHT_CLIENT_REQUEST_COUNTER_DSC", "Tracks the number of requests submitted by a 
	client to prevent replay attacks. Client requests include an incremental counter value, if it 
	is lower than the value stored in the database the request is rejected.");
define("_CO_STRAYLIGHT_CLIENT_SHARED_HMAC_KEY", "Shared HMAC key");
define("_CO_STRAYLIGHT_CLIENT_SHARED_HMAC_KEY_DSC", "Use a 256 character key for optimum security. 
	Used to check the authenticity and integrity of data submitted by the client device by SHA256 
	HMAC. Requests that are not accompanied by a valid HMAC will be discarded.");

// Error messages - only displayed when the output of report_error() is enabled. As a rule, 
// Straylight does not provide feedback to clients about error conditions for security reasons.
// Remote commands that contain unexpected data are regarded as hostile.
define("_CO_STRAYLIGHT_CLIENT_ERROR", "Error: ");
define("_CO_STRAYLIGHT_CLIENT_ERROR_MISSING_REQUIRED_PARAMETER", "Missing required parameter.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_CLIENT_ID_NOT_DECIMAL", "Client ID not in decimal format.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_COUNTER_NOT_DECIMAL", "Counter not in decimal format.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_TIMESTAMP_NOT_DECIMAL", "Timestamp not in decimal format.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_RANDOM_NOT_ALPHANUMERIC", "Random factor not in alphanumeric 
	format");
define("_CO_STRAYLIGHT_CLIENT_ERROR_HMAC_NOT_ALPHANUMERIC", "HMAC not in alphanumeric format.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_BAD_TIMESTAMP", "Bad timestamp. Check the clock on your client 
	is accurate.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_CLIENT_NOT_AUTHORISED", "Client not Straylight authorised.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_BAD_COUNTER", "Bad counter. This is not the most recent request 
	from the client device.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_NO_PRESHARED_KEY", "No pre-shared key.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_BAD_HMAC", "Bad HMAC. Failed to confirm authenticity and 
	integrity of message. Discarding.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_SANITY_CHECK_FAILED", "Sanity check failed, request not 
	authenticated.");
define("_CO_STRAYLIGHT_CLIENT_ERROR_", "");
define("_CO_STRAYLIGHT_CLIENT_ERROR_", "");

// Admin table
define("_CO_STRAYLIGHT_USER_NAME", "User name");
define("_CO_STRAYLIGHT_AUTHORISATION_ENABLED", "Authorisation enabled");
define("_CO_STRAYLIGHT_AUTHORISATION_DISABLED", "Authorisation disabled");
define("_CO_STRAYLIGHT_AUTHORISATION_ENABLE", "Enable authorisation");
define("_CO_STRAYLIGHT_AUTHORISATION_DISABLE", "Disable authorisation");