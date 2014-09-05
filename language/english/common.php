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
define("_CO_STRAYLIGHT_CLIENT_AUTHORISED_DSC", "Enable or disable remote administrative privileges for this client device. A disabled client has no rights and is completely non-functional.");
define("_CO_STRAYLIGHT_CLIENT_REQUEST_COUNTER", "Request counter");
define("_CO_STRAYLIGHT_CLIENT_REQUEST_COUNTER_DSC", "Tracks the number of requests submitted by a client to prevent replay attacks. Client requests include an incremental counter value, if it is lower than the value stored in the database the request is rejected.");
define("_CO_STRAYLIGHT_CLIENT_SHARED_HMAC_KEY", "Shared HMAC key");
define("_CO_STRAYLIGHT_CLIENT_SHARED_HMAC_KEY_DSC", "Use a 256 character key for optimum security. Used to check the authenticity and integrity of data submitted by the client device by SHA256 HMAC. Requests that are not accompanied by a valid HMAC will be discarded.");

// Admin table
define("_CO_STRAYLIGHT_USER_NAME", "User name");
define("_CO_STRAYLIGHT_AUTHORISATION_ENABLED", "Authorisation enabled");
define("_CO_STRAYLIGHT_AUTHORISATION_DISABLED", "Authorisation disabled");
define("_CO_STRAYLIGHT_AUTHORISATION_ENABLE", "Enable authorisation");
define("_CO_STRAYLIGHT_AUTHORISATION_DISABLE", "Disable authorisation");